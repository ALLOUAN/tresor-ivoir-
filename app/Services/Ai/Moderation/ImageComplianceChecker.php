<?php

namespace App\Services\Ai\Moderation;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Contrôle de conformité du CONTENU d'une photo (nudité, violence, contenu sans rapport avec le
 * tourisme, texte promotionnel…) par un modèle de vision externe.
 *
 * Désactivé par défaut (AI_VISION_DRIVER=none) : sans modèle, aucun jugement n'est porté sur le
 * contenu et le rapport l'indique (« non effectué »), au lieu d'une fausse assurance.
 */
class ImageComplianceChecker
{
    public function enabled(): bool
    {
        return config('ai.moderation.vision.driver') === 'anthropic' && (bool) config('ai.llm.api_key');
    }

    /** @return array{status:string,reasons:string[]}  status : ok | refused | not_done | error */
    public function check(string $path): array
    {
        if (! $this->enabled()) {
            return ['status' => 'not_done', 'reasons' => []];
        }
        $img = @imagecreatefromstring((string) @file_get_contents($path));
        if (! $img) {
            return ['status' => 'error', 'reasons' => ['image illisible']];
        }
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1.0, 1024 / max($w, $h));
        $small = imagecreatetruecolor((int) round($w * $scale), (int) round($h * $scale));
        imagecopyresampled($small, $img, 0, 0, 0, 0, imagesx($small), imagesy($small), $w, $h);
        ob_start();
        imagejpeg($small, null, 80);
        $jpeg = (string) ob_get_clean();

        $prompt = "Tu contrôles les photos d'un site de tourisme en Côte d'Ivoire. Réponds uniquement par un objet JSON "
            .'{"conforme": true|false, "motifs": ["..."]}. Une photo est non conforme si elle montre de la nudité ou du contenu sexuel, '
            .'de la violence explicite, des armes brandies, un contenu haineux, ou si elle est manifestement sans rapport avec un lieu, '
            .'un plat, un événement ou une création artisanale (capture d\'écran, document, publicité, selfie hors contexte). Sinon : conforme.';

        try {
            $res = Http::withHeaders(['x-api-key' => config('ai.llm.api_key'), 'anthropic-version' => '2023-06-01'])
                ->timeout(30)->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('ai.moderation.vision.model'), 'max_tokens' => 300,
                    'messages' => [['role' => 'user', 'content' => [
                        ['type' => 'image', 'source' => ['type' => 'base64', 'media_type' => 'image/jpeg', 'data' => base64_encode($jpeg)]],
                        ['type' => 'text', 'text' => $prompt],
                    ]]],
                ]);
            if (! $res->successful()) {
                Log::warning('AI vision: HTTP '.$res->status());

                return ['status' => 'error', 'reasons' => ['service indisponible']];
            }
            $text = (string) data_get($res->json(), 'content.0.text', '');
            if (preg_match('/\{.*\}/s', $text, $m)) {
                $text = $m[0];
            }
            $data = json_decode($text, true);
        } catch (\Throwable $e) {
            Log::warning('AI vision failed: '.$e->getMessage());

            return ['status' => 'error', 'reasons' => ['service indisponible']];
        }
        if (! is_array($data) || ! array_key_exists('conforme', $data)) {
            return ['status' => 'error', 'reasons' => ['réponse invalide']];
        }

        return $data['conforme']
            ? ['status' => 'ok', 'reasons' => []]
            : ['status' => 'refused', 'reasons' => array_slice(array_map(fn ($x) => strip_tags((string) $x), (array) ($data['motifs'] ?? [])), 0, 3)];
    }
}
