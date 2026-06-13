<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\CallLog;
use App\Models\CallSession;
use App\Services\TwilioService;
use App\Services\ClaudeService;
use Twilio\TwiML\VoiceResponse;

class TwilioWebhookController extends Controller
{
    public function __construct(
        protected TwilioService $twilio,
        protected ClaudeService $claude
    ) {}

    /**
     * Webhook : appel décroché — lancer le message d'ouverture
     */
    public function answer(Request $request)
    {
        $callSid = $request->input('CallSid');
        $to      = $request->input('To');

        $log     = CallLog::where('call_sid', $callSid)->first();
        $contact = $log ? $log->contact : Contact::where('phone', $to)->latest()->first();

        if (!$contact) {
            return response($this->twilio->buildHangupTwiml('Désolé, une erreur est survenue.'), 200)
                ->header('Content-Type', 'text/xml');
        }

        $twiml = $this->twilio->buildAnswerTwiml($callSid, $contact);

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Webhook : AMD — détection messagerie/humain
     */
    public function amd(Request $request)
    {
        $callSid    = $request->input('CallSid');
        $answeredBy = $request->input('AnsweredBy'); // human / machine_start / fax / unknown

        $log = CallLog::where('call_sid', $callSid)->first();

        if (in_array($answeredBy, ['machine_start', 'machine_end_beep', 'machine_end_silence', 'machine_end_other', 'fax'])) {
            // C'est une messagerie
            if ($log) {
                $log->update(['result' => 'voicemail']);
                $log->contact->update(['status' => 'done']);
            }

            // Rediriger vers la gestion messagerie
            try {
                $this->twilio->buildVoicemailTwiml($callSid);
            } catch (\Exception $e) {}
        }

        return response('', 204);
    }

    /**
     * Webhook : réponse du prospect — traitement IA
     */
    public function respond(Request $request)
    {
        $callSid    = $request->input('CallSid');
        $speechText = $request->input('SpeechResult', '');

        $session = CallSession::where('call_sid', $callSid)->first();
        $log     = CallLog::where('call_sid', $callSid)->first();

        if (!$session) {
            return response($this->twilio->buildHangupTwiml('Désolé, session introuvable.'), 200)
                ->header('Content-Type', 'text/xml');
        }

        // Si pas de parole détectée
        if (empty($speechText)) {
            $speechText = '[silence]';
        }

        // Générer la réponse IA
        [$aiResponse, $shouldHangup, $result] = $this->claude->generateResponse($session, $speechText);

        // Mettre à jour le log
        if ($log && $result) {
            $transcript = $this->claude->buildTranscript($session->fresh()->conversation_history ?? []);
            $log->update([
                'result'     => $result,
                'transcript' => $transcript,
            ]);
        }

        if ($shouldHangup) {
            // Fin de conversation
            if ($log) {
                $log->contact->update(['status' => 'done']);
                if (!$result && $log->result === 'no_answer') {
                    $log->update(['result' => 'answered']);
                }
            }
            $twiml = $this->twilio->buildHangupTwiml($aiResponse);
        } else {
            // Continuer la conversation
            $response = new VoiceResponse();
            $gather = $response->gather([
                'input'         => 'speech',
                'action'        => route('twilio.voice.respond'),
                'language'      => 'fr-FR',
                'speechTimeout' => 'auto',
                'timeout'       => 5,
            ]);
            $gather->say($aiResponse, ['language' => 'fr-FR', 'voice' => 'Polly.Léa-Neural']);

            // Si silence après le gather
            $response->say("Je suis toujours là. Avez-vous des questions ?", ['language' => 'fr-FR', 'voice' => 'Polly.Léa-Neural']);
            $response->redirect(route('twilio.voice.respond'), ['method' => 'POST']);
            $twiml = (string) $response;
        }

        return response($twiml, 200)->header('Content-Type', 'text/xml');
    }

    /**
     * Webhook : statut de l'appel (completed, no-answer, busy, failed)
     */
    public function status(Request $request)
    {
        $callSid    = $request->input('CallSid');
        $callStatus = $request->input('CallStatus');
        $duration   = (int) $request->input('CallDuration', 0);

        $log = CallLog::where('call_sid', $callSid)->first();
        if (!$log) {
            return response('', 204);
        }

        // Mettre à jour la durée
        $log->update(['duration' => $duration]);

        // Si statut terminal sans résultat défini
        if ($callStatus === 'no-answer' && $log->result === 'no_answer') {
            $log->contact->update(['status' => 'done']);
        } elseif ($callStatus === 'busy') {
            $log->update(['result' => 'busy']);
            $log->contact->update(['status' => 'done']);
        } elseif ($callStatus === 'failed') {
            $log->update(['result' => 'failed']);
            $log->contact->update(['status' => 'failed']);
        } elseif ($callStatus === 'completed') {
            $log->contact->update(['status' => 'done']);
        }

        return response('', 204);
    }
}