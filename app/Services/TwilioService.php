<?php

namespace App\Services;

use Twilio\Rest\Client;
use Twilio\TwiML\VoiceResponse;
use App\Models\Contact;
use App\Models\CallLog;
use App\Models\CallSession;
use App\Models\OfferPrompt;

class TwilioService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(
            config('services.twilio.sid'),
            config('services.twilio.auth_token')
        );
    }

    /**
     * Initier un appel sortant vers un contact
     */
    public function initiateCall(Contact $contact): array
    {
        $prompt = OfferPrompt::getActive();
        if (!$prompt) {
            return ['success' => false, 'error' => 'Aucune offre active configurée.'];
        }

        try {
            $call = $this->client->calls->create(
                $contact->phone,
                config('services.twilio.phone_number'),
                [
                    'url'        => route('twilio.voice.answer'),
                    'statusCallback' => route('twilio.voice.status'),
                    'statusCallbackMethod' => 'POST',
                    'statusCallbackEvent' => ['initiated', 'ringing', 'answered', 'completed'],
                    'machineDetection' => 'Enable', // Détection de messagerie vocale
                    'asyncAmd' => 'true',
                    'asyncAmdStatusCallback' => route('twilio.voice.amd'),
                ]
            );

            // Créer un log d'appel
            CallLog::create([
                'contact_id' => $contact->id,
                'call_sid'   => $call->sid,
                'result'     => 'no_answer',
                'called_at'  => now(),
            ]);

            $contact->update(['status' => 'calling']);

            return ['success' => true, 'call_sid' => $call->sid];
        } catch (\Exception $e) {
            CallLog::create([
                'contact_id' => $contact->id,
                'result'     => 'failed',
                'notes'      => $e->getMessage(),
                'called_at'  => now(),
            ]);
            $contact->update(['status' => 'failed']);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * TwiML pour répondre à l'appel — dit le message d'ouverture et écoute
     */
    public function buildAnswerTwiml(string $callSid, Contact $contact): string
    {
        $prompt = OfferPrompt::getActive();
        $response = new VoiceResponse();

        // Créer la session de conversation
        CallSession::updateOrCreate(
            ['call_sid' => $callSid],
            [
                'contact_id'           => $contact->id,
                'conversation_history' => [],
                'turn_count'           => 0,
            ]
        );

        // Message d'ouverture
        $openingText = $prompt->opening_message;
        // Remplacer les variables
        $openingText = str_replace(
            ['{name}', '{company}'],
            [$contact->name ?? 'Monsieur/Madame', $contact->company ?? ''],
            $openingText
        );

        $gather = $response->gather([
            'input'        => 'speech',
            'action'       => route('twilio.voice.respond'),
            'language'     => 'fr-FR',
            'speechTimeout' => 'auto',
            'timeout'      => 5,
        ]);
        $gather->say($openingText, ['language' => 'fr-FR', 'voice' => 'Polly.Léa-Neural']);

        // Si pas de réponse après gather
        $response->redirect(route('twilio.voice.respond'), ['method' => 'POST']);

        return (string) $response;
    }

    /**
     * TwiML quand messagerie détectée (AMD)
     */
    public function buildVoicemailTwiml(string $callSid): string
    {
        $response = new VoiceResponse();
        $prompt = OfferPrompt::getActive();

        // Laisser un message court
        $voicemailMsg = "Bonjour, nous avons essayé de vous joindre concernant une offre spéciale. "
            . "Nous vous rappellerons prochainement. Merci et bonne journée.";

        $response->say($voicemailMsg, ['language' => 'fr-FR', 'voice' => 'Polly.Lea']);
        $response->hangup();

        return (string) $response;
    }

    /**
     * TwiML de fin d'appel
     */
    public function buildHangupTwiml(string $message = ''): string
    {
        $response = new VoiceResponse();
        if ($message) {
            $response->say($message, ['language' => 'fr-FR', 'voice' => 'Polly.Lea']);
        }
        $response->hangup();
        return (string) $response;
    }
}