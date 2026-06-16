<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\CallSession;
use App\Models\CallLog;
use App\Models\OfferPrompt;

class ClaudeService
{
    protected string $apiKey;
    protected string $model = 'claude-sonnet-4-6';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.key');
    }

    /**
     * Générer une réponse de l'IA basée sur l'historique de conversation
     * Retourne [response_text, should_hangup, result]
     */
    public function generateResponse(CallSession $session, string $userSpeech): array
    {
        $prompt = OfferPrompt::getActive();
        if (!$prompt) {
            return ['Je suis désolé, une erreur est survenue. Au revoir.', true, 'failed'];
        }

        $history = $session->conversation_history ?? [];
        $history[] = ['role' => 'user', 'content' => $userSpeech];

        $systemPrompt = $prompt->system_prompt . "\n\n"
            . "INSTRUCTIONS IMPORTANTES :\n"
            . "- Tu réponds uniquement à l'oral, phrases courtes et naturelles.\n"
            . "- Si la personne est intéressée, dis-lui que tu vas la recontacter et conclus l'appel.\n"
            . "- Si la personne n'est pas intéressée ou veut raccrocher, remercie-la poliment et raccroche.\n"
            . "- Maximum 3 échanges si peu d'intérêt montré.\n"
            . "- À la fin de ta réponse, ajoute sur une nouvelle ligne UNIQUEMENT l'un de ces tags :\n"
            . "  [CONTINUE] si la conversation continue\n"
            . "  [INTERESTED] si la personne est intéressée\n"
            . "  [NOT_INTERESTED] si la personne refuse\n"
            . "  [HANGUP] pour terminer poliment\n"
            . "- Tour actuel : " . ($session->turn_count + 1) . "/5\n";

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(10)->post('https://api.anthropic.com/v1/messages', [
                'model'      => $this->model,
                'max_tokens' => 300,
                'system'     => $systemPrompt,
                'messages'   => $history,
            ]);

            $content = $response->json('content.0.text', '');

            $tag = 'CONTINUE';
            $tags = ['[CONTINUE]', '[INTERESTED]', '[NOT_INTERESTED]', '[HANGUP]'];
            foreach ($tags as $t) {
                if (str_contains($content, $t)) {
                    $tag = trim($t, '[]');
                    $content = trim(str_replace($t, '', $content));
                    break;
                }
            }

            if ($session->turn_count >= 4 && $tag === 'CONTINUE') {
                $tag = 'HANGUP';
                $content = "Je vous remercie pour votre temps. N'hésitez pas à nous contacter si vous souhaitez plus d'informations. Bonne journée !";
            }

            $history[] = ['role' => 'assistant', 'content' => $content];
            $session->update([
                'conversation_history' => $history,
                'turn_count'           => $session->turn_count + 1,
            ]);

            // Mettre à jour le transcript du call_log en temps réel
            $callLog = CallLog::where('call_sid', $session->call_sid)->first();
            if ($callLog) {
                $callLog->update([
                    'transcript' => $this->buildTranscript($history),
                ]);
            }

            $shouldHangup = in_array($tag, ['INTERESTED', 'NOT_INTERESTED', 'HANGUP']);
            $result = match($tag) {
                'INTERESTED'     => 'interested',
                'NOT_INTERESTED' => 'not_interested',
                'HANGUP'         => 'answered',
                default          => null,
            };

            return [$content, $shouldHangup, $result];
        } catch (\Exception $e) {
            return [
                'Je suis désolé, une difficulté technique est survenue. Je vous rappellerai. Bonne journée !',
                true,
                'failed'
            ];
        }
    }

    /**
     * Construire une transcription lisible depuis l'historique
     */
    public function buildTranscript(array $history): string
    {
        return collect($history)->map(function ($msg) {
            $role = $msg['role'] === 'user' ? '👤 Prospect' : '🤖 Agent';
            return "$role : {$msg['content']}";
        })->implode("\n");
    }
}