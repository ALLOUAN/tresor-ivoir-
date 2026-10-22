<?php

namespace App\Services\Ai;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/** Appel unique à l'API Messages d'Anthropic. Retourne null en cas de clé absente ou d'échec : l'appelant se replie alors sur le mode sans modèle. */
class LlmClient
{
    public function available(): bool
    {
        return (bool) config('ai.llm.api_key');
    }

    public function complete(string $system, string $user, int $maxTokens = 800, ?string $model = null, int $timeout = 30): ?string
    {
        if (! $this->available()) {
            return null;
        }
        try {
            $res = Http::withHeaders(['x-api-key' => config('ai.llm.api_key'), 'anthropic-version' => '2023-06-01'])
                ->timeout($timeout)->post('https://api.anthropic.com/v1/messages', [
                    'model' => $model ?: config('ai.llm.model'), 'max_tokens' => $maxTokens, 'system' => $system,
                    'messages' => [['role' => 'user', 'content' => $user]],
                ]);
            if (! $res->successful()) {
                Log::warning('LLM: HTTP '.$res->status());

                return null;
            }

            return (string) data_get($res->json(), 'content.0.text', '');
        } catch (\Throwable $e) {
            Log::warning('LLM: '.$e->getMessage());

            return null;
        }
    }

    /** Extrait un objet JSON d'une réponse (le modèle l'entoure parfois de texte). */
    public static function json(?string $text): ?array
    {
        if ($text === null || ! preg_match('/\{.*\}/s', $text, $m)) {
            return null;
        }
        $data = json_decode($m[0], true);

        return is_array($data) ? $data : null;
    }
}
