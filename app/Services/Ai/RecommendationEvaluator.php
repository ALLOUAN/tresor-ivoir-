<?php

namespace App\Services\Ai;

/**
 * Évaluation hors ligne de la recommandation (méthode « leave-one-out ») : pour chaque acteur
 * ayant au moins trois fiches dans son historique, on retire la dernière, on recommande à
 * partir du reste, et l'on regarde si la fiche retirée remonte dans les k premières.
 *
 * Trois systèmes sont comparés : tri par popularité (situation actuelle du site),
 * contenu + règles métier (sans collaboratif) et hybride complet.
 */
class RecommendationEvaluator
{
    public function __construct(private readonly Catalog $catalog, private readonly InteractionStore $interactions) {}

    /**
     * @return array{k:int, actors:int, simulated_share:float, methods:array<string,array<string,float>>, catalog:int}
     */
    public function run(int $k = 5): array
    {
        $matrix = $this->interactions->matrix();
        $items = $this->catalog->items();
        $snap = $this->catalog->snapshot();

        $cases = [];
        foreach ($matrix as $actor => $hist) {
            $hist = array_filter($hist, fn ($_, $key) => isset($items[$key]), ARRAY_FILTER_USE_BOTH);
            if (count($hist) < 3) {
                continue;
            }
            $keys = array_keys($hist);
            sort($keys);
            $held = end($keys);
            $cases[$actor] = $held;
        }

        $methods = ['popularite' => [], 'contenu_regles' => [], 'hybride' => []];
        $content = new HybridRecommender($this->catalog, []);
        $acc = [];
        foreach (array_keys($methods) as $m) {
            $acc[$m] = ['hit' => 0, 'ndcg' => 0.0, 'div' => 0.0, 'lists' => 0, 'long' => 0, 'slots' => 0, 'seen' => []];
        }

        foreach ($cases as $actor => $held) {
            $history = $matrix[$actor];
            unset($history[$held]);
            $history = array_filter($history, fn ($_, $key) => isset($items[$key]), ARRAY_FILTER_USE_BOTH);

            $m2 = $matrix;
            $m2[$actor] = $history;
            $hybrid = new HybridRecommender($this->catalog, $m2);
            $ctx = new RecoContext(history: $history);

            $lists = [
                'popularite' => $this->popularity($items, $history, $k),
                'contenu_regles' => array_map(fn ($r) => $r['item']->key, $content->recommend($ctx, $k)),
                'hybride' => array_map(fn ($r) => $r['item']->key, $hybrid->recommend($ctx, $k)),
            ];
            foreach ($lists as $name => $keys) {
                $a = &$acc[$name];
                $a['lists']++;
                $pos = array_search($held, $keys, true);
                if ($pos !== false) {
                    $a['hit']++;
                    $a['ndcg'] += 1 / log($pos + 2, 2);
                }
                $a['div'] += $this->diversity($keys, $snap['vectors']);
                foreach ($keys as $key) {
                    $a['seen'][$key] = true;
                    $a['slots']++;
                    if ($items[$key]->views <= $snap['medianViews']) {
                        $a['long']++;
                    }
                }
                unset($a);
            }
        }

        $n = max(1, count($cases));
        foreach ($acc as $name => $a) {
            $methods[$name] = [
                'precision' => round($a['hit'] / ($n * $k), 4),
                'rappel' => round($a['hit'] / $n, 4),
                'ndcg' => round($a['ndcg'] / $n, 4),
                'diversite' => round($a['div'] / $n, 4),
                'couverture' => round(count($a['seen']) / max(1, count($items)), 4),
                'longue_traine' => round($a['long'] / max(1, $a['slots']), 4),
            ];
        }

        $sim = 0;
        foreach (array_keys($cases) as $actor) {
            if (str_starts_with($actor, 's:sim')) {
                $sim++;
            }
        }

        return [
            'k' => $k, 'actors' => count($cases), 'simulated_share' => count($cases) ? round($sim / count($cases), 2) : 0.0,
            'methods' => $methods, 'catalog' => count($items),
        ];
    }

    /** @param array<string,CatalogItem> $items */
    private function popularity(array $items, array $history, int $k): array
    {
        $now = time();
        $pool = array_filter($items, fn ($i) => ! isset($history[$i->key]) && ($i->endsAt === null || $i->endsAt >= $now));
        uasort($pool, fn ($a, $b) => $b->views <=> $a->views);

        return array_slice(array_keys($pool), 0, $k);
    }

    /** 1 − similarité moyenne entre les fiches d'une même liste. */
    private function diversity(array $keys, array $vectors): float
    {
        $n = count($keys);
        if ($n < 2) {
            return 0.0;
        }
        $sum = 0.0;
        $pairs = 0;
        for ($i = 0; $i < $n; $i++) {
            for ($j = $i + 1; $j < $n; $j++) {
                $sum += TextVectorizer::cosine($vectors[$keys[$i]], $vectors[$keys[$j]]);
                $pairs++;
            }
        }

        return 1 - $sum / $pairs;
    }
}
