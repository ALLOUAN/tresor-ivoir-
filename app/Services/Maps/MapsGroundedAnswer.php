<?php

namespace App\Services\Maps;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Réponse « ancrée sur Google Maps » (outil googleMaps de l'API Gemini) : utilisée seulement
 * quand le contenu du site ne suffit pas. Les adresses de la plateforme fournies en contexte
 * sont à privilégier ; les lieux Google sont cités avec leur lien Maps.
 * Activée par AI_MAPS_GROUNDING=true et GEMINI_API_KEY.
 *
 * @see https://ai.google.dev/gemini-api/docs/maps-grounding
 */
class MapsGroundedAnswer
{
    private const ENDPOINT = 'https://generativelanguage.googleapis.com/v1beta/models/%s:generateContent';

    /** Centre d'Abidjan : position par défaut quand le visiteur ne partage pas la sienne. */
    public const DEFAULT_POSITION = [5.345, -4.024];

    public function enabled(): bool
    {
        return (bool) config('ai.maps_grounding.enabled') && (string) config('ai.maps_grounding.api_key') !== '';
    }

    /**
     * @param  array<int, array{title: string, url: string, text: string}>  $siteContext  fiches du site à privilégier
     * @return array{answer: string, places: array<int, array{title: string, url: string}>, widget_token: ?string}|null
     */
    public function answer(string $question, string $lang, array $siteContext = [], ?array $position = null): ?array
    {
        if (! $this->enabled()) {
            return null;
        }

        [$lat, $lng] = $position ?? self::DEFAULT_POSITION;
        $cacheKey = 'maps-grounded:'.md5(json_encode([$question, $lang, round($lat, 2), round($lng, 2), array_column($siteContext, 'url')]));
        if (($cached = Cache::get($cacheKey)) !== null) {
            return $cached;
        }

        $context = collect($siteContext)->take(5)->map(fn ($s) => "- {$s['title']} ({$s['url']}) : ".mb_substr($s['text'], 0, 300))->implode("\n");
        $system = ($lang === 'en'
            ? 'You are the travel assistant of Trésors d\'Ivoire, a culture and tourism platform for Côte d\'Ivoire. Answer briefly (max 120 words) in English. '
              .'When the platform listings below match the request, recommend them first with their link; otherwise use Google Maps places. Never invent prices, opening hours or availability.'
            : 'Tu es l\'assistant voyage de Trésors d\'Ivoire, plateforme de culture et de tourisme en Côte d\'Ivoire. Réponds brièvement (120 mots maximum) en français. '
              .'Quand les adresses de la plateforme ci-dessous correspondent à la demande, recommande-les en premier avec leur lien ; sinon appuie-toi sur les lieux Google Maps. N\'invente jamais de prix, d\'horaires ni de disponibilités.')
            .($context !== '' ? "\n\nAdresses de la plateforme :\n".$context : '');

        try {
            $response = Http::timeout(25)
                ->withHeaders(['x-goog-api-key' => config('ai.maps_grounding.api_key')])
                ->post(sprintf(self::ENDPOINT, config('ai.maps_grounding.model')), [
                    'systemInstruction' => ['parts' => [['text' => $system]]],
                    'contents' => [['role' => 'user', 'parts' => [['text' => $question]]]],
                    'tools' => [['googleMaps' => ['enableWidget' => true]]],
                    'toolConfig' => ['retrievalConfig' => ['latLng' => ['latitude' => $lat, 'longitude' => $lng], 'languageCode' => $lang]],
                    'generationConfig' => ['maxOutputTokens' => 600, 'temperature' => 0.3],
                ]);
        } catch (\Throwable $e) {
            Log::warning('Gemini (Google Maps) injoignable', ['error' => $e->getMessage()]);

            return null;
        }

        if ($response->failed()) {
            Log::warning('Gemini (Google Maps) en erreur', ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 300)]);

            return null;
        }

        $candidate = $response->json('candidates.0') ?? [];
        $text = trim(collect($candidate['content']['parts'] ?? [])->pluck('text')->filter()->implode("\n"));
        // Le widget affiche du texte brut : on retire le gras et les puces markdown.
        $text = preg_replace(['/\*\*(.+?)\*\*/s', '/^\s*[*\-]\s+/m'], ['$1', '• '], $text);
        if ($text === '') {
            return null;
        }

        $meta = $candidate['groundingMetadata'] ?? [];
        $places = collect($meta['groundingChunks'] ?? [])
            ->map(fn ($c) => $c['maps'] ?? null)
            ->filter(fn ($m) => ! empty($m['uri']) && ! empty($m['title']))
            ->unique('uri')
            ->map(fn ($m) => ['title' => (string) $m['title'], 'url' => (string) $m['uri']])
            ->take(6)
            ->values()
            ->all();

        $result = [
            'answer' => $text,
            'places' => $places,
            'widget_token' => $meta['googleMapsWidgetContextToken'] ?? null,
        ];
        Cache::put($cacheKey, $result, now()->addHours(6));

        return $result;
    }
}
