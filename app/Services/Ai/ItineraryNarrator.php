<?php

namespace App\Services\Ai;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Rédaction du programme en français à partir du plan calculé.
 *
 *  - « template » : rédaction déterministe, sans service externe (par défaut).
 *  - « anthropic » : un modèle de langage reformule le plan ; sa réponse est vérifiée
 *    (JSON valide, un texte par jour, toutes les étapes citées) et, au moindre écart,
 *    le texte déterministe est utilisé à la place. Le modèle ne choisit jamais les étapes.
 */
class ItineraryNarrator
{
    /** @return array{engine:string,intro:string,days:string[]} */
    public function narrate(array $plan): array
    {
        $driver = config('ai.llm.driver');
        if ($driver === 'anthropic' && config('ai.llm.api_key')) {
            $llm = $this->viaAnthropic($plan);
            if ($llm !== null) {
                return $llm;
            }
        }

        return $this->template($plan);
    }

    public function template(array $plan): array
    {
        $s = $plan['summary'];
        $cities = implode(', ', $s['cities']);
        $intro = sprintf(
            'Votre séjour de %d jour%s à %s pour %d personne%s, avec un coût estimé à %s FCFA%s.',
            $s['days'], $s['days'] > 1 ? 's' : '', $cities, $s['travelers'], $s['travelers'] > 1 ? 's' : '',
            number_format($s['total_cost'], 0, ',', ' '),
            $s['budget'] > 0 ? ' pour un budget de '.number_format($s['budget'], 0, ',', ' ').' FCFA' : ''
        );

        $days = [];
        foreach ($plan['days'] as $d) {
            $parts = [];
            if ($d['transfer']) {
                $parts[] = 'Départ pour '.$d['transfer']['to'].' (environ '.$d['transfer']['km'].' km).';
            }
            $act = array_values(array_filter($d['stops'], fn ($x) => $x['kind'] === 'activity'));
            foreach ($act as $i => $a) {
                $lead = match (true) {
                    $a['slot'] === 'Soirée' => 'En soirée, ne manquez pas',
                    $i === 0 => 'Le matin, rendez-vous à',
                    default => 'Ensuite, découvrez',
                };
                $parts[] = $lead.' '.$a['title'].($a['cost'] > 0 ? ' ('.number_format($a['cost'], 0, ',', ' ').' FCFA pour le groupe)' : ' (gratuit ou prix non communiqué)').'.';
            }
            $meal = collect($d['stops'])->firstWhere('kind', 'meal');
            if ($meal) {
                $parts[] = 'Pour le déjeuner : '.$meal['title'].'.';
            } elseif (collect($d['stops'])->contains('kind', 'free_meal')) {
                $parts[] = 'Déjeuner libre.';
            }
            if (! $act) {
                $parts[] = 'Journée libre : aucune autre activité publiée à proximité ne correspond à vos critères.';
            }
            if ($d['lodging']) {
                $parts[] = 'Nuit à '.$d['lodging']['title'].' (chambre « '.$d['lodging']['room'].' »).';
            }
            $days[] = implode(' ', $parts);
        }

        return ['engine' => 'template', 'intro' => $intro, 'days' => $days];
    }

    private function viaAnthropic(array $plan): ?array
    {
        $facts = [
            'sejour' => $plan['summary'],
            'jours' => array_map(fn ($d) => [
                'jour' => $d['index'], 'date' => Carbon::parse($d['date'])->locale('fr')->translatedFormat('l j F'), 'ville' => $d['city'],
                'transfert' => $d['transfer'],
                'hebergement' => $d['lodging'] ? ['nom' => $d['lodging']['title'], 'chambre' => $d['lodging']['room']] : null,
                'etapes' => array_map(fn ($s) => ['moment' => $s['slot'], 'nom' => $s['title'], 'cout_groupe_fcfa' => $s['cost']], $d['stops']),
            ], $plan['days']),
        ];

        $system = "Tu rédiges en français le programme d'un séjour touristique en Côte d'Ivoire à partir de données JSON. "
            ."Règles strictes : n'utilise QUE les lieux, prix et chiffres fournis ; n'invente aucun lieu, horaire ni prix ; "
            ."cite chaque étape par son nom exact ; ton chaleureux et sobre ; pas de liste à puces. "
            .'Réponds uniquement par un objet JSON : {"intro": "...", "days": ["texte du jour 1", "texte du jour 2", ...]} '
            .'avec exactement un texte par jour (2 à 4 phrases).';

        try {
            $res = Http::withHeaders(['x-api-key' => config('ai.llm.api_key'), 'anthropic-version' => '2023-06-01'])
                ->timeout((int) config('ai.llm.timeout', 25))
                ->post('https://api.anthropic.com/v1/messages', [
                    'model' => config('ai.llm.model'), 'max_tokens' => 1800, 'system' => $system,
                    'messages' => [['role' => 'user', 'content' => json_encode($facts, JSON_UNESCAPED_UNICODE)]],
                ]);
            if (! $res->successful()) {
                Log::warning('AI narrator: HTTP '.$res->status());

                return null;
            }
            $text = (string) data_get($res->json(), 'content.0.text', '');
            if (preg_match('/\{.*\}/s', $text, $m)) {
                $text = $m[0];
            }
            $data = json_decode($text, true);
        } catch (\Throwable $e) {
            Log::warning('AI narrator failed: '.$e->getMessage());

            return null;
        }

        return $this->validated($plan, is_array($data) ? $data : []);
    }

    /** Refuse toute réponse qui ne cite pas toutes les étapes de chaque jour. */
    public function validated(array $plan, array $data): ?array
    {
        if (! isset($data['intro'], $data['days']) || ! is_array($data['days']) || count($data['days']) !== count($plan['days'])) {
            return null;
        }
        foreach ($plan['days'] as $i => $d) {
            $text = mb_strtolower(strip_tags((string) $data['days'][$i]));
            foreach ($d['stops'] as $s) {
                if ($s['kind'] !== 'free_meal' && ! str_contains($text, mb_strtolower($s['title']))) {
                    return null;
                }
            }
        }

        return [
            'engine' => 'anthropic',
            'intro' => trim(strip_tags((string) $data['intro'])),
            'days' => array_map(fn ($t) => trim(strip_tags((string) $t)), $data['days']),
        ];
    }
}
