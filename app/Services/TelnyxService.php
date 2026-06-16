<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Contact;
use App\Models\CallLog;
use App\Models\CallSession;
use App\Models\OfferPrompt;

class TelnyxService
{
    protected string $apiKey;
    protected string $connectionId;
    protected string $fromNumber;
    protected string $baseUrl = 'https://api.telnyx.com/v2';

    public function __construct()
    {
        $this->apiKey       = config('services.telnyx.api_key');
        $this->connectionId = config('services.telnyx.connection_id');
        $this->fromNumber   = config('services.telnyx.phone_number');
    }

    /**
     * En-têtes communs pour les appels API Telnyx
     */
    protected function headers(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-Type'  => 'application/json',
            'Accept'        => 'application/json',
        ];
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
            $response = Http::withHeaders($this->headers())
                ->timeout(15)
                ->post("{$this->baseUrl}/calls", [
                    'connection_id' => $this->connectionId,
                    'to'            => $contact->phone,
                    'from'          => $this->fromNumber,
                    'webhook_url'   => route('telnyx.webhook'),
                    'webhook_url_method' => 'POST',
                    'answering_machine_detection' => 'premium',
                    'answering_machine_detection_config' => [
                        'after_greeting_silence_millis' => 800,
                        'between_words_silence_millis'  => 150,
                        'greeting_duration_millis'       => 3500,
                        'initial_silence_millis'         => 4000,
                        'maximum_number_of_words'        => 5,
                        'maximum_word_length_millis'     => 3000,
                        'silence_threshold'              => 256,
                        'greeting_total_analysis_period_millis' => 5000,
                    ],
                ]);

            if (!$response->successful()) {
                $error = $response->json('errors.0.detail', $response->body());

                CallLog::create([
                    'contact_id' => $contact->id,
                    'result'     => 'failed',
                    'notes'      => $error,
                    'called_at'  => now(),
                ]);
                $contact->update(['status' => 'failed']);

                return ['success' => false, 'error' => $error];
            }

            $callControlId = $response->json('data.call_control_id');

            // Créer un log d'appel — on stocke le call_control_id dans call_sid
            CallLog::create([
                'contact_id' => $contact->id,
                'call_sid'   => $callControlId,
                'result'     => 'no_answer',
                'called_at'  => now(),
            ]);

            // Préparer la session de conversation (sera complétée à call.answered)
            CallSession::updateOrCreate(
                ['call_sid' => $callControlId],
                [
                    'contact_id'           => $contact->id,
                    'conversation_history' => [],
                    'turn_count'           => 0,
                ]
            );

            $contact->update(['status' => 'calling']);

            return ['success' => true, 'call_sid' => $callControlId];
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
     * Faire parler l'IA (TTS) sur l'appel
     */
    public function speak(string $callControlId, string $text): bool
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->baseUrl}/calls/{$callControlId}/actions/speak", [
                'payload'  => $text,
                'voice'    => 'Polly.Lea-Neural',
                'language' => 'fr-FR',
            ]);

        return $response->successful();
    }

    /**
     * Démarrer l'écoute (transcription) après avoir parlé
     */
    public function startTranscription(string $callControlId): bool
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->baseUrl}/calls/{$callControlId}/actions/transcription_start", [
                'language'              => 'fr-FR',
                'transcription_engine'  => 'B',
                'interim_results'       => false,
            ]);

        return $response->successful();
    }

    /**
     * Arrêter la transcription
     */
    public function stopTranscription(string $callControlId): bool
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->baseUrl}/calls/{$callControlId}/actions/transcription_stop");

        return $response->successful();
    }

    /**
     * Raccrocher l'appel
     */
    public function hangup(string $callControlId): bool
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->baseUrl}/calls/{$callControlId}/actions/hangup");

        return $response->successful();
    }

    /**
     * Répondre à un appel entrant (non utilisé pour les sortants, mais requis par l'API dans certains flux)
     */
    public function answer(string $callControlId): bool
    {
        $response = Http::withHeaders($this->headers())
            ->timeout(10)
            ->post("{$this->baseUrl}/calls/{$callControlId}/actions/answer", [
                'webhook_url' => route('telnyx.webhook'),
            ]);

        return $response->successful();
    }

    /**
     * Construire le message d'ouverture pour un contact (remplace les variables)
     */
    public function buildOpeningMessage(Contact $contact): string
    {
        $prompt = OfferPrompt::getActive();

        $openingText = $prompt->opening_message;
        $openingText = str_replace(
            ['{name}', '{company}'],
            [$contact->name ?? 'Monsieur/Madame', $contact->company ?? ''],
            $openingText
        );

        return $openingText;
    }

    /**
     * Message de messagerie vocale
     */
    public function voicemailMessage(): string
    {
        return "Bonjour, nous avons essayé de vous joindre concernant une offre spéciale. "
            . "Nous vous rappellerons prochainement. Merci et bonne journée.";
    }
}