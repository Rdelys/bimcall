<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Contact;
use App\Models\CallLog;
use App\Models\CallSession;
use App\Services\TelnyxService;
use App\Services\ClaudeService;

class TelnyxWebhookController extends Controller
{
    public function __construct(
        protected TelnyxService $telnyx,
        protected ClaudeService $claude
    ) {}

    /**
     * Point d'entrée unique pour tous les webhooks Telnyx
     */
    public function handle(Request $request)
    {
        $eventType     = $request->input('data.event_type');
        $payload       = $request->input('data.payload', []);
        $callControlId = $payload['call_control_id'] ?? null;

        if (!$callControlId) {
            return response()->json(['status' => 'ignored']);
        }

        match ($eventType) {
            'call.answered'                       => $this->onAnswered($callControlId, $payload),
            'call.machine.premium.detection.ended' => $this->onAmdResult($callControlId, $payload),
            'call.machine.detection.ended'         => $this->onAmdResult($callControlId, $payload),
            'call.speak.ended'                     => $this->onSpeakEnded($callControlId, $payload),
            'call.transcription'                   => $this->onTranscription($callControlId, $payload),
            'call.hangup'                          => $this->onHangup($callControlId, $payload),
            default                                => null,
        };

        return response()->json(['status' => 'ok']);
    }

    /**
     * L'appel est décroché — on attend le résultat AMD avant de parler
     * (le webhook AMD arrivera juste après si activé)
     */
    protected function onAnswered(string $callControlId, array $payload): void
    {
        $log = CallLog::where('call_sid', $callControlId)->first();
        if (!$log) return;

        // Si pas d'AMD configuré, on parle directement
        // (Avec AMD premium activé, on attend onAmdResult)
    }

    /**
     * Résultat de la détection messagerie / humain
     */
    protected function onAmdResult(string $callControlId, array $payload): void
    {
        $log = CallLog::where('call_sid', $callControlId)->first();
        if (!$log) return;

        $result = $payload['result'] ?? 'unknown'; // human, machine, not_sure, etc.

        if (in_array($result, ['machine', 'machine_end_beep', 'machine_end_silence', 'machine_end_other'])) {
            // Messagerie détectée — laisser un message court puis raccrocher
            $log->update(['result' => 'voicemail']);
            $log->contact->update(['status' => 'done']);

            $this->telnyx->speak($callControlId, $this->telnyx->voicemailMessage());

            // Programmer le hangup après le message (géré via call.speak.ended)
            \Cache::put("telnyx_hangup_after_speak_{$callControlId}", true, now()->addMinutes(2));
            return;
        }

        // Humain détecté (ou pas de détection concluante) — démarrer la conversation
        $contact = $log->contact;
        $session = CallSession::where('call_sid', $callControlId)->first();

        if (!$session) {
            $session = CallSession::create([
                'call_sid'             => $callControlId,
                'contact_id'           => $contact->id,
                'conversation_history' => [],
                'turn_count'           => 0,
            ]);
        }

        $opening = $this->telnyx->buildOpeningMessage($contact);

        // Sauvegarder le message d'ouverture dans l'historique
        $session->update([
            'conversation_history' => [
                ['role' => 'assistant', 'content' => $opening],
            ],
        ]);

        $this->telnyx->speak($callControlId, $opening);
    }

    /**
     * L'IA (ou le message vocal) a fini de parler
     */
    protected function onSpeakEnded(string $callControlId, array $payload): void
    {
        // Si on doit raccrocher après ce message (cas messagerie)
        if (\Cache::pull("telnyx_hangup_after_speak_{$callControlId}")) {
            $this->telnyx->hangup($callControlId);
            return;
        }

        // Sinon, démarrer/relancer l'écoute du prospect
        $this->telnyx->startTranscription($callControlId);
    }

    /**
     * Transcription reçue — texte dit par le prospect
     */
    protected function onTranscription(string $callControlId, array $payload): void
    {
        $transcriptionData = $payload['transcription_data'] ?? [];
        $isFinal           = $transcriptionData['is_final'] ?? false;
        $text              = trim($transcriptionData['transcript'] ?? '');

        // On ne traite que les transcriptions finales et non vides
        if (!$isFinal || $text === '') {
            return;
        }

        $session = CallSession::where('call_sid', $callControlId)->first();
        $log     = CallLog::where('call_sid', $callControlId)->first();

        if (!$session || !$log) return;

        // Arrêter l'écoute pendant que l'IA répond
        $this->telnyx->stopTranscription($callControlId);

        // Générer la réponse IA
        [$aiResponse, $shouldHangup, $result] = $this->claude->generateResponse($session, $text);

        if ($result) {
            $log->update(['result' => $result]);
        }

        if ($shouldHangup) {
            $log->contact->update(['status' => 'done']);
            if (!$result && $log->result === 'no_answer') {
                $log->update(['result' => 'answered']);
            }
            \Cache::put("telnyx_hangup_after_speak_{$callControlId}", true, now()->addMinutes(2));
        }

        // Faire parler l'IA — onSpeakEnded relancera l'écoute (ou raccrochera)
        $this->telnyx->speak($callControlId, $aiResponse);
    }

    /**
     * Fin de l'appel — mise à jour finale du log
     */
    protected function onHangup(string $callControlId, array $payload): void
    {
        $log = CallLog::where('call_sid', $callControlId)->first();
        if (!$log) return;

        $hangupCause = $payload['hangup_cause'] ?? null; // normal_clearing, call_rejected, etc.

        // Durée de l'appel
        if (isset($payload['start_time']) && isset($payload['end_time'])) {
            try {
                $start = \Carbon\Carbon::parse($payload['start_time']);
                $end   = \Carbon\Carbon::parse($payload['end_time']);
                $log->update(['duration' => $end->diffInSeconds($start)]);
            } catch (\Exception $e) {}
        }

        // Si aucun résultat n'a été défini pendant l'appel
        if ($log->result === 'no_answer') {
            $result = match($hangupCause) {
                'call_rejected'              => 'busy',
                'unallocated_number',
                'unspecified',
                'normal_temporary_failure'   => 'failed',
                default                      => 'no_answer',
            };
            $log->update(['result' => $result]);
        }

        $log->contact->update(['status' => 'done']);

        \Cache::forget("telnyx_hangup_after_speak_{$callControlId}");
    }
}