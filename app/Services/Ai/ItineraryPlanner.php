<?php

namespace App\Services\Ai;

use Illuminate\Support\Carbon;

/**
 * Planificateur d'itinéraires « Mon séjour ».
 *
 *  1. Sélection des candidats : hébergements, sites, expériences, loisirs, restaurants et
 *     événements situés dans la zone et aux dates du séjour (catalogue publié uniquement).
 *  2. Notation : affinité avec les centres d'intérêt (similarité TF-IDF + étiquettes), qualité,
 *     et un léger bonus d'équité pour les adresses peu connues.
 *  3. Optimisation sous contraintes : budget (hébergement plafonné, reste réparti par jour),
 *     distance quotidienne maximale selon le rythme, nombre d'étapes par jour. Les étapes d'une
 *     journée sont choisies par une heuristique gloutonne « score / détour », puis ordonnées par
 *     énumération exacte (au plus 4 étapes) pour minimiser la distance.
 *  4. Aucune fiche n'est inventée : tout provient du catalogue ; les prix absents sont signalés.
 */
class ItineraryPlanner
{
    private const DIRECTIONS_TYPE = [
        'restaurant' => 'restaurant', 'site' => 'site', 'event' => 'evenement', 'leisure' => 'loisir',
        'experience' => 'experience', 'agency' => 'agence', 'transport' => 'transport', 'provider' => 'annuaire',
    ];

    private const CITY_RADIUS_KM = 18.0;

    private array $items;

    private array $vectors;

    private array $cities;

    private float $medianViews;

    private TextVectorizer $vectorizer;

    private array $cfg;

    public function __construct(private readonly Catalog $catalog)
    {
        $snap = $catalog->snapshot();
        $this->items = $snap['items'];
        $this->vectors = $snap['vectors'];
        $this->cities = $snap['cities'];
        $this->medianViews = $snap['medianViews'];
        $this->vectorizer = $catalog->vectorizer();
        $this->cfg = config('ai.planner');
    }

    /** Valide et complète les paramètres de saisie. */
    public function normalize(array $in): array
    {
        $maxDays = (int) $this->cfg['max_days'];
        $cityIds = array_values(array_unique(array_filter(array_map('intval', (array) ($in['city_ids'] ?? [])), fn ($id) => isset($this->cities[$id]))));
        $days = max(1, min($maxDays, (int) ($in['days'] ?? 3)));
        $cityIds = array_slice($cityIds, 0, min((int) $this->cfg['max_cities'], $days));
        $pace = isset($this->cfg['pace'][$in['pace'] ?? '']) ? $in['pace'] : 'modere';
        $start = null;
        if (! empty($in['start_date'])) {
            try {
                $start = Carbon::parse($in['start_date'])->startOfDay();
            } catch (\Throwable) {
                $start = null;
            }
        }

        return [
            'city_ids' => $cityIds,
            'start_city_id' => isset($this->cities[(int) ($in['start_city_id'] ?? 0)]) ? (int) $in['start_city_id'] : null,
            'days' => $days,
            'start_date' => ($start && $start->gte(now()->startOfDay()) ? $start : now()->startOfDay())->toDateString(),
            'budget_xof' => max(0, (int) ($in['budget_xof'] ?? 0)),
            'travelers' => max(1, min(12, (int) ($in['travelers'] ?? 2))),
            'interests' => Taxonomy::cleanInterests($in['interests'] ?? []),
            'pace' => $pace,
        ];
    }

    public function plan(array $params): array
    {
        $p = $this->normalize($params);
        $warnings = [];
        if (! $p['city_ids']) {
            return ['params' => $p, 'summary' => [], 'days' => [], 'warnings' => ['Choisissez au moins une destination.'], 'ok' => false];
        }

        $query = $this->interestQuery($p['interests']);
        $blocks = $this->cityBlocks($p);
        $rooms = (int) ceil($p['travelers'] / 2);
        $nightsTotal = max(0, $p['days'] - 1);
        $budget = $p['budget_xof'];
        $used = [];          // fiches déjà placées dans le séjour
        $dayNo = 0;
        $days = [];
        $lodgingTotal = 0;

        // Hébergement par étape de ville (plafond proportionnel aux nuits)
        $lodgingCap = $budget > 0 ? $budget * $this->cfg['lodging_budget_share'] : PHP_INT_MAX;
        $lodgings = [];
        foreach ($blocks as $bi => $b) {
            $nights = $b['days'] - ($bi === array_key_last($blocks) ? 1 : 0);
            $b['nights'] = max(0, $nights);
            $blocks[$bi] = $b;
            if ($b['nights'] === 0) {
                continue;
            }
            $cap = $nightsTotal > 0 ? $lodgingCap * $b['nights'] / $nightsTotal : 0;
            $pick = $this->pickLodging($b['city_id'], $b['nights'], $rooms, $cap, $query, $p['interests']);
            if ($pick) {
                $lodgings[$bi] = $pick;
                $lodgingTotal += $pick['total'];
                if ($pick['over_cap']) {
                    $warnings[] = 'Votre budget est serré pour dormir à '.$this->cities[$b['city_id']]['name'].' : l\'hébergement le moins cher dépasse la part prévue.';
                }
            } else {
                $warnings[] = 'Aucun hébergement publié à '.$this->cities[$b['city_id']]['name'].' : prévoyez votre logement séparément.';
            }
        }

        $remaining = $budget > 0 ? max(0, $budget - $lodgingTotal) : PHP_INT_MAX;
        $dayCountLeft = $p['days'];
        $totalKm = 0.0;
        $unknown = 0;
        $prevPoint = $p['start_city_id'] ? $this->cityPoint($p['start_city_id']) : null;
        $date0 = Carbon::parse($p['start_date']);

        foreach ($blocks as $bi => $b) {
            $city = $this->cities[$b['city_id']];
            $cityPt = [$city['lat'], $city['lng']];
            $lodging = $lodgings[$bi] ?? null;
            $home = $lodging ? [$lodging['item']->lat, $lodging['item']->lng] : $cityPt;
            $activities = $this->activityPool($b['city_id'], $query, $p['interests']);
            $restaurants = $this->restaurantPool($b['city_id'], $p['interests']);

            for ($d = 0; $d < $b['days']; $d++) {
                $dayNo++;
                $date = $date0->copy()->addDays($dayNo - 1);
                $dayBudget = $remaining === PHP_INT_MAX ? PHP_INT_MAX : (int) floor($remaining / max(1, $dayCountLeft));
                $transfer = null;
                if ($d === 0 && $prevPoint && $prevPoint[0] !== null && $city['lat'] !== null) {
                    $km = Geo::roadKm($prevPoint[0], $prevPoint[1], $city['lat'], $city['lng']);
                    if ($km !== null && $km > 20) {
                        $transfer = ['from' => $bi === 0 ? 'Départ' : 'Étape précédente', 'to' => $city['name'], 'km' => round($km), 'minutes' => (int) round($km / 60 * 60)];
                        $totalKm += $km;
                    }
                }

                $spent = 0;
                $stops = [];

                // Événement du jour (priorité : date fixe)
                $fixed = [];
                foreach ($activities as $key => $a) {
                    $it = $a['item'];
                    if ($it->type === 'event' && $it->startsAt && $it->endsAt
                        && $date->copy()->endOfDay()->timestamp >= $it->startsAt && $date->copy()->startOfDay()->timestamp <= $it->endsAt && ! isset($used[$key])) {
                        $fixed[$key] = $a;
                        break;
                    }
                }

                $pace = $this->cfg['pace'][$p['pace']];
                $maxStops = $pace['activities'];
                $chosen = [];
                foreach ($fixed as $key => $a) {
                    $cost = $this->cost($a['item'], $p['travelers']);
                    if ($cost['known'] === false) {
                        $unknown++;
                    }
                    $chosen[$key] = $a + ['cost' => $cost['amount'], 'fixed' => true];
                    $spent += $cost['amount'];
                    $used[$key] = true;
                }

                // Sélection gloutonne : meilleur score / détour, sous contraintes de budget et de distance
                $pool = array_filter($activities, fn ($a, $k) => ! isset($used[$k]) && $a['item']->type !== 'event', ARRAY_FILTER_USE_BOTH);
                while (count($chosen) < $maxStops && $pool) {
                    $best = null;
                    $bestVal = -INF;
                    foreach ($pool as $key => $a) {
                        $cost = $this->cost($a['item'], $p['travelers']);
                        if ($dayBudget !== PHP_INT_MAX && $spent + $cost['amount'] + $this->mealCost($restaurants, $p['travelers']) > $dayBudget && $cost['amount'] > 0) {
                            continue;
                        }
                        $trial = array_merge(array_values(array_map(fn ($c) => $c['item'], $chosen)), [$a['item']]);
                        $km = $this->bestPath($home, $trial)['km'];
                        if ($km > $pace['max_km']) {
                            continue;
                        }
                        $val = $a['score'] / (1 + max(0, $km - 5) / 25);
                        if ($val > $bestVal) {
                            [$bestVal, $best] = [$val, $key];
                        }
                    }
                    if ($best === null) {
                        break;
                    }
                    $cost = $this->cost($pool[$best]['item'], $p['travelers']);
                    if ($cost['known'] === false) {
                        $unknown++;
                    }
                    $chosen[$best] = $pool[$best] + ['cost' => $cost['amount'], 'fixed' => false];
                    $spent += $cost['amount'];
                    $used[$best] = true;
                    unset($pool[$best]);
                }

                // Ordre optimal du parcours
                $ordered = $this->bestPath($home, array_values(array_map(fn ($c) => $c['item'], $chosen)));
                $byKey = [];
                foreach ($chosen as $c) {
                    $byKey[$c['item']->key] = $c;
                }

                // Déjeuner après la première étape
                $meal = $this->pickMeal($restaurants, $ordered['order'][0] ?? null, $used, $home, $p['travelers']);
                if ($meal) {
                    $used[$meal['item']->key] = true;
                    $spent += $meal['cost'];
                    if ($meal['estimated']) {
                        $unknown++;
                    }
                }

                // Assemblage chronologique
                $cursor = $home;
                $dayKm = 0.0;
                $slotNames = ['Matin', 'Après-midi', 'Fin de journée', 'Soirée'];
                $slotIdx = 0;
                foreach ($ordered['order'] as $idx => $it) {
                    $km = Geo::roadKm($cursor[0], $cursor[1], $it->lat, $it->lng) ?? 0.0;
                    $dayKm += $km;
                    $cursor = [$it->lat, $it->lng];
                    $c = $byKey[$it->key];
                    $stops[] = $this->stopRow($c['fixed'] ? 'Soirée' : $slotNames[min($slotIdx, 2)], 'activity', $it, $c['cost'], $km, $c['reasons'] ?? [], $p['travelers']);
                    $slotIdx = $c['fixed'] ? $slotIdx : $slotIdx + 1;
                    if ($idx === 0 && $meal) {
                        $mk = Geo::roadKm($cursor[0], $cursor[1], $meal['item']->lat, $meal['item']->lng) ?? 0.0;
                        $dayKm += $mk;
                        $cursor = [$meal['item']->lat ?? $cursor[0], $meal['item']->lng ?? $cursor[1]];
                        $stops[] = $this->stopRow('Déjeuner', 'meal', $meal['item'], $meal['cost'], $mk, $meal['reasons'], $p['travelers'], $meal['estimated']);
                    }
                }
                if (! $ordered['order'] && $meal) {
                    $mk = Geo::roadKm($home[0], $home[1], $meal['item']->lat, $meal['item']->lng) ?? 0.0;
                    $dayKm += $mk;
                    $stops[] = $this->stopRow('Déjeuner', 'meal', $meal['item'], $meal['cost'], $mk, $meal['reasons'], $p['travelers'], $meal['estimated']);
                } elseif (! $meal) {
                    $est = (int) $this->cfg['default_meal_xof'] * $p['travelers'];
                    $spent += $est;
                    $unknown++;
                    $stops[] = ['slot' => 'Déjeuner', 'kind' => 'free_meal', 'item' => null, 'title' => 'Repas libre', 'cost' => $est, 'estimated' => true, 'km' => 0.0, 'reasons' => ['Aucun restaurant publié dans cette zone : repas à votre convenance (coût estimé).']];
                }
                // retour à l'hébergement
                $dayKm += Geo::roadKm($cursor[0], $cursor[1], $home[0], $home[1]) ?? 0.0;
                $totalKm += $dayKm;
                if ($remaining !== PHP_INT_MAX) {
                    $remaining = max(0, $remaining - $spent);
                }
                $dayCountLeft--;

                $days[] = [
                    'index' => $dayNo, 'date' => $date->toDateString(), 'city' => $city['name'], 'city_id' => $b['city_id'],
                    'lodging' => ($lodging && ! ($bi === array_key_last($blocks) && $d === $b['days'] - 1)) ? $this->lodgingRow($lodging, $rooms) : null,
                    'transfer' => $transfer, 'stops' => $stops, 'km' => round($dayKm), 'cost' => $spent,
                ];
            }
            $prevPoint = $cityPt;
        }

        $activityCost = array_sum(array_column($days, 'cost'));
        $total = $activityCost + $lodgingTotal;
        if ($budget > 0 && $total > $budget) {
            $warnings[] = 'Le coût estimé dépasse votre budget de '.number_format($total - $budget, 0, ',', ' ').' FCFA : réduisez la durée, le rythme ou choisissez un hébergement plus économique.';
        }
        if ($unknown > 0) {
            $warnings[] = $unknown.' prix ne sont pas renseignés par les prestataires : ils sont estimés ou comptés à 0 FCFA.';
        }
        if (! array_filter(array_column($days, 'stops'), fn ($s) => collect($s)->contains(fn ($x) => $x['kind'] === 'activity'))) {
            $warnings[] = 'Peu d\'activités publiées dans la zone choisie : élargissez la destination.';
        }

        return [
            'ok' => true, 'params' => $p, 'warnings' => array_values(array_unique($warnings)), 'days' => $days,
            'summary' => [
                'days' => $p['days'], 'travelers' => $p['travelers'], 'budget' => $budget,
                'lodging_cost' => $lodgingTotal, 'activities_cost' => $activityCost, 'total_cost' => $total,
                'remaining' => $budget > 0 ? $budget - $total : null,
                'km_total' => round($totalKm), 'cities' => array_map(fn ($b) => $this->cities[$b['city_id']]['name'], $blocks),
                'unknown_prices' => $unknown,
            ],
        ];
    }

    // ── Sélection et notation ───────────────────────────────────────────────

    private function interestQuery(array $interests): array
    {
        $parts = [];
        foreach ($interests as $key) {
            $tf = [];
            foreach (Taxonomy::INTERESTS[$key]['keywords'] as $kw) {
                foreach (TextVectorizer::tokens($kw) as $t) {
                    $tf[$t] = 1.0;
                }
            }
            $parts[] = [$this->vectorizer->vector($tf), 1.0];
        }

        return $parts ? TextVectorizer::blend($parts) : [];
    }

    /** Distance à la ville (km), null si la fiche n'a pas de position. */
    private function distanceTo(CatalogItem $i, int $cityId): ?float
    {
        $c = $this->cities[$cityId];

        return $i->hasGeo() && $c['lat'] !== null ? Geo::km($c['lat'], $c['lng'], $i->lat, $i->lng) : null;
    }

    private function inCity(CatalogItem $i, int $cityId): bool
    {
        $c = $this->cities[$cityId];
        if ($i->hasGeo() && $c['lat'] !== null) {
            $km = Geo::km($c['lat'], $c['lng'], $i->lat, $i->lng) ?? INF;
            // Dans la ville, ou rattachée à la ville dans l'annuaire (garde-fou : pas à plus de 60 km).
            return $km <= self::CITY_RADIUS_KM || ($i->cityId === $cityId && $km <= 60);
        }

        return $i->cityId === $cityId;
    }

    private function affinity(CatalogItem $i, array $query, array $interests): float
    {
        $aff = $interests
            ? 0.6 * TextVectorizer::cosine($query, $this->vectors[$i->key]) + 0.4 * (count(array_intersect($interests, $i->tags)) / count($interests))
            : 0.35;
        $n = $i->ratingCount;
        $quality = $n > 0 ? ($i->rating * $n + 3.8 * 5) / ($n + 5) / 5 : 0.5;
        $fair = $i->views <= $this->medianViews ? 0.08 : 0.0;

        return 0.65 * $aff + 0.2 * $quality + 0.07 * ($i->featured ? 1 : 0) + $fair;
    }

    /** @return array<string,array{item:CatalogItem,score:float,reasons:string[]}> */
    private function activityPool(int $cityId, array $query, array $interests): array
    {
        $pool = [];
        foreach ($this->items as $key => $i) {
            if (! in_array($i->type, ['site', 'experience', 'leisure', 'event'], true) || ! $i->hasGeo()) {
                continue;
            }
            // Dans la ville, sinon en excursion (≤ 65 km) avec une légère pénalité.
            $local = $this->inCity($i, $cityId);
            $km = $this->distanceTo($i, $cityId);
            if (! $local && ($km === null || $km > 65)) {
                continue;
            }
            $score = $this->affinity($i, $query, $interests) * ($local ? 1.0 : 0.85);
            $reasons = $local ? [] : ['Excursion depuis '.$this->cities[$cityId]['name'].' (~'.round($km).' km)'];
            $shared = array_values(array_intersect($interests, $i->tags));
            if ($shared) {
                $reasons[] = 'Pour votre goût : '.Taxonomy::INTERESTS[$shared[0]]['label'];
            }
            if ($i->views <= $this->medianViews) {
                $reasons[] = 'Adresse encore peu connue';
            }
            if ($i->ratingCount > 0 && $i->rating >= 4.0) {
                $reasons[] = 'Bien noté ('.number_format($i->rating, 1, ',', '').'/5)';
            }
            $pool[$key] = ['item' => $i, 'score' => $score, 'reasons' => array_slice($reasons, 0, 2)];
        }
        uasort($pool, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $pool;
    }

    private function restaurantPool(int $cityId, array $interests): array
    {
        $pool = [];
        foreach ($this->items as $key => $i) {
            if ($i->type !== 'restaurant') {
                continue;
            }
            $local = $this->inCity($i, $cityId);
            $km = $this->distanceTo($i, $cityId);
            if (! $local && ($km === null || $km > 25)) {
                continue;
            }
            $n = $i->ratingCount;
            $quality = $n > 0 ? ($i->rating * $n + 3.8 * 5) / ($n + 5) / 5 : 0.5;
            $boost = in_array('gastronomie', $interests, true) ? 0.15 : 0.0;
            $pool[$key] = ['item' => $i, 'score' => ($quality + $boost + ($i->featured ? 0.05 : 0)) * ($local ? 1.0 : 0.85)];
        }
        uasort($pool, fn ($a, $b) => $b['score'] <=> $a['score']);

        return $pool;
    }

    private function pickLodging(int $cityId, int $nights, int $rooms, float $cap, array $query, array $interests): ?array
    {
        $best = null;
        $cheapest = null;
        foreach ($this->items as $i) {
            if ($i->type !== 'accommodation' || ! $this->inCity($i, $cityId) || empty($i->extra['rooms'])) {
                continue;
            }
            $room = collect($i->extra['rooms'])->sortBy('price')->first();
            $total = $room['price'] * $nights * $rooms;
            $row = ['item' => $i, 'room' => $room['name'], 'nightly' => $room['price'], 'total' => $total, 'nights' => $nights, 'over_cap' => false];
            if (! $cheapest || $total < $cheapest['total']) {
                $cheapest = $row;
            }
            if ($total > $cap) {
                continue;
            }
            $score = $this->affinity($i, $query, $interests);
            if (! $best || $score > $best['score']) {
                $best = $row + ['score' => $score];
            }
        }
        if ($best) {
            return $best;
        }

        return $cheapest ? $cheapest + ['over_cap' => true, 'score' => 0] : null;
    }

    // ── Calculs de coût et de parcours ──────────────────────────────────────

    /** @return array{amount:int,known:bool} coût d'une étape pour tout le groupe */
    private function cost(CatalogItem $i, int $travelers): array
    {
        if ($i->price === null) {
            return ['amount' => 0, 'known' => false];
        }

        return ['amount' => $i->price * $travelers, 'known' => true];
    }

    private function mealCost(array $restaurants, int $travelers): int
    {
        if (! $restaurants) {
            return (int) $this->cfg['default_meal_xof'] * $travelers;
        }
        $first = reset($restaurants)['item'];

        return ($first->price ?: (int) $this->cfg['default_meal_xof']) * $travelers;
    }

    private function pickMeal(array $restaurants, ?CatalogItem $near, array $used, array $home, int $travelers): ?array
    {
        $anchor = $near && $near->hasGeo() ? [$near->lat, $near->lng] : $home;
        $best = null;
        $bestVal = -INF;
        foreach ($restaurants as $key => $r) {
            if (isset($used[$key])) {
                continue;
            }
            $km = Geo::km($anchor[0], $anchor[1], $r['item']->lat, $r['item']->lng) ?? 10.0;
            $val = $r['score'] / (1 + $km / 10);
            if ($val > $bestVal) {
                [$bestVal, $best] = [$val, $r];
            }
        }
        if (! $best) {
            return null;
        }
        $price = $best['item']->price;
        $reasons = [$price ? 'Repas moyen estimé à '.number_format($price, 0, ',', ' ').' FCFA par personne' : 'Repas estimé (carte non renseignée)'];

        return [
            'item' => $best['item'], 'cost' => ($price ?: (int) $this->cfg['default_meal_xof']) * $travelers,
            'estimated' => ! $price, 'reasons' => $reasons,
        ];
    }

    /**
     * Ordre optimal par énumération exacte (≤ 4 étapes) : hébergement → étapes → hébergement.
     *
     * @param  CatalogItem[]  $stops
     * @return array{order:CatalogItem[],km:float}
     */
    private function bestPath(array $home, array $stops): array
    {
        $n = count($stops);
        if ($n === 0) {
            return ['order' => [], 'km' => 0.0];
        }
        $dist = fn ($a, $b) => Geo::roadKm($a[0], $a[1], $b[0], $b[1]) ?? 0.0;
        $perms = $this->permutations(range(0, $n - 1));
        $bestKm = INF;
        $bestOrder = [];
        foreach ($perms as $perm) {
            $km = 0.0;
            $cur = $home;
            foreach ($perm as $idx) {
                $pt = [$stops[$idx]->lat, $stops[$idx]->lng];
                $km += $dist($cur, $pt);
                $cur = $pt;
            }
            $km += $dist($cur, $home);
            if ($km < $bestKm) {
                [$bestKm, $bestOrder] = [$km, $perm];
            }
        }

        return ['order' => array_map(fn ($i) => $stops[$i], $bestOrder), 'km' => $bestKm];
    }

    private function permutations(array $items): array
    {
        if (count($items) <= 1) {
            return [$items];
        }
        $out = [];
        foreach ($items as $i => $item) {
            $rest = $items;
            unset($rest[$i]);
            foreach ($this->permutations(array_values($rest)) as $p) {
                $out[] = array_merge([$item], $p);
            }
        }

        return $out;
    }

    // ── Découpage du séjour et présentation ─────────────────────────────────

    /** Répartit les jours entre les villes ; ordre du plus proche au plus lointain depuis le départ. */
    private function cityBlocks(array $p): array
    {
        $ids = $p['city_ids'];
        $cursor = $p['start_city_id'] ? $this->cityPoint($p['start_city_id']) : null;
        $ordered = [];
        $left = $ids;
        while ($left) {
            $next = null;
            if ($cursor && $cursor[0] !== null) {
                $min = INF;
                foreach ($left as $id) {
                    $d = Geo::km($cursor[0], $cursor[1], $this->cities[$id]['lat'], $this->cities[$id]['lng']) ?? INF;
                    if ($d < $min) {
                        [$min, $next] = [$d, $id];
                    }
                }
            }
            $next ??= $left[0];
            $ordered[] = $next;
            $left = array_values(array_diff($left, [$next]));
            $cursor = $this->cityPoint($next);
        }

        $n = count($ordered);
        $base = intdiv($p['days'], $n);
        $extra = $p['days'] % $n;
        $blocks = [];
        foreach ($ordered as $i => $id) {
            $blocks[] = ['city_id' => $id, 'days' => $base + ($i < $extra ? 1 : 0)];
        }

        return $blocks;
    }

    private function cityPoint(int $id): array
    {
        return [$this->cities[$id]['lat'] ?? null, $this->cities[$id]['lng'] ?? null];
    }

    private function brief(CatalogItem $i): array
    {
        return [
            'key' => $i->key, 'type' => $i->type, 'type_label' => $i->typeLabel, 'title' => $i->title, 'url' => $i->url,
            'image' => $i->image, 'city' => $i->city, 'rating' => $i->rating, 'rating_count' => $i->ratingCount,
            'lat' => $i->lat, 'lng' => $i->lng,
            'directions' => isset(self::DIRECTIONS_TYPE[$i->type]) ? $this->directionsUrl($i) : null,
        ];
    }

    private function directionsUrl(CatalogItem $i): ?string
    {
        $slug = basename(parse_url($i->url, PHP_URL_PATH));

        return route('directions.go', ['type' => self::DIRECTIONS_TYPE[$i->type], 'slug' => $slug]);
    }

    private function stopRow(string $slot, string $kind, CatalogItem $i, int $cost, float $km, array $reasons, int $travelers, bool $estimated = false): array
    {
        return [
            'slot' => $slot, 'kind' => $kind, 'item' => $this->brief($i), 'title' => $i->title,
            'price_each' => $i->price, 'cost' => $cost, 'estimated' => $estimated || $i->price === null,
            'km' => round($km, 1), 'minutes' => (int) round($km / 40 * 60), 'reasons' => $reasons,
            'price_label' => $i->extra['fee_label'] ?? null,
        ];
    }

    private function lodgingRow(array $l, int $rooms): array
    {
        return [
            'item' => $this->brief($l['item']), 'title' => $l['item']->title, 'room' => $l['room'], 'nightly' => $l['nightly'],
            'nights' => $l['nights'], 'rooms' => $rooms, 'total' => $l['total'],
            'book_url' => $l['item']->url.'?room='.urlencode($l['room']).'#booking-module',
        ];
    }
}
