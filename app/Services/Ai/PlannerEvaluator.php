<?php

namespace App\Services\Ai;

/**
 * Cas de test scénarisés du planificateur : respect de la durée et du budget, distance par
 * jour, nombre d'étapes, couverture des centres d'intérêt demandés, place faite aux adresses
 * peu connues, absence de doublons.
 */
class PlannerEvaluator
{
    public function __construct(private readonly ItineraryPlanner $planner, private readonly Catalog $catalog) {}

    public static function scenarios(): array
    {
        return [
            ['name' => 'Abidjan, 2 j, culture + gastronomie, calme', 'city_ids' => [1], 'days' => 2, 'budget_xof' => 120000, 'travelers' => 2, 'interests' => ['culture', 'gastronomie'], 'pace' => 'calme'],
            ['name' => 'Bassam + Assinie, 3 j, plage + culture', 'city_ids' => [3, 8], 'start_city_id' => 1, 'days' => 3, 'budget_xof' => 250000, 'travelers' => 2, 'interests' => ['plage', 'culture'], 'pace' => 'modere'],
            ['name' => 'Yamoussoukro, 2 j, petit budget', 'city_ids' => [2], 'days' => 2, 'budget_xof' => 70000, 'travelers' => 1, 'interests' => ['culture'], 'pace' => 'modere'],
            ['name' => 'Man, 3 j, nature + aventure, soutenu', 'city_ids' => [4], 'start_city_id' => 1, 'days' => 3, 'budget_xof' => 200000, 'travelers' => 3, 'interests' => ['nature', 'aventure'], 'pace' => 'soutenu'],
            ['name' => 'Abidjan + Bassam, 5 j, tous centres', 'city_ids' => [1, 3], 'days' => 5, 'budget_xof' => 450000, 'travelers' => 2, 'interests' => ['culture', 'gastronomie', 'plage', 'nature'], 'pace' => 'modere'],
            ['name' => 'Abidjan, 1 j, budget très serré', 'city_ids' => [1], 'days' => 1, 'budget_xof' => 30000, 'travelers' => 2, 'interests' => ['gastronomie'], 'pace' => 'calme'],
        ];
    }

    /** @return array{scenarios:array<int,array<string,mixed>>} */
    public function run(): array
    {
        $items = $this->catalog->items();
        $median = $this->catalog->snapshot()['medianViews'];
        $out = [];

        foreach (self::scenarios() as $sc) {
            $name = $sc['name'];
            unset($sc['name']);
            $plan = $this->planner->plan($sc);
            $acts = [];
            $keys = [];
            $dup = false;
            foreach ($plan['days'] as $d) {
                foreach ($d['stops'] as $s) {
                    if ($s['kind'] === 'free_meal') {
                        continue;
                    }
                    if (isset($keys[$s['item']['key']])) {
                        $dup = true;
                    }
                    $keys[$s['item']['key']] = true;
                    if ($s['kind'] === 'activity') {
                        $acts[] = $items[$s['item']['key']];
                    }
                }
            }
            $covered = 0;
            foreach ($sc['interests'] as $int) {
                if (collect($acts)->contains(fn ($i) => in_array($int, $i->tags, true))) {
                    $covered++;
                }
            }
            $nDays = max(1, count($plan['days']));
            $out[] = [
                'name' => $name,
                'days_ok' => count($plan['days']) === $sc['days'],
                'budget_ok' => ($plan['summary']['remaining'] ?? -1) >= 0,
                'km_per_day' => round(array_sum(array_column($plan['days'], 'km')) / $nDays),
                'stops_per_day' => round(count($acts) / $nDays, 1),
                'interest_coverage' => $sc['interests'] ? $covered / count($sc['interests']) : 1.0,
                'long_tail' => $acts ? collect($acts)->filter(fn ($i) => $i->views <= $median)->count() / count($acts) : 0.0,
                'duplicates' => $dup,
                'total_cost' => $plan['summary']['total_cost'] ?? 0,
                'warnings' => $plan['warnings'],
            ];
        }

        return ['scenarios' => $out];
    }
}
