<?php

namespace App\Services\Ai\Analytics;

use Illuminate\Support\Facades\DB;

/**
 * Compare une fiche à la médiane et au quart supérieur de sa catégorie (ou, si la catégorie compte
 * trop peu de prestataires, à l'ensemble de l'annuaire, en le disant).
 */
class CategoryBenchmark
{
    /** Indicateurs comparés : [clé, libellé, unité, plus = mieux]. */
    public const METRICS = [
        ['views_30d', 'Vues de la fiche (30 jours)', '', true],
        ['directions_30d', 'Clics « Itinéraire » (30 jours)', '', true],
        ['rating', 'Note moyenne', '/5', true],
        ['rating_count', 'Nombre d\'avis', '', true],
        ['photos', 'Photos', '', true],
        ['description_chars', 'Longueur de la description', ' car.', true],
        ['completeness', 'Complétude de la fiche', ' %', true],
    ];

    public function __construct(private readonly ProviderProfileMetrics $metrics) {}

    /** @return array{scope:string,scope_label:string,peers:int,rows:array<int,array<string,mixed>>} */
    public function compare(object $provider, ?array $mine = null): array
    {
        $mine ??= $this->metrics->for($provider);
        $min = (int) config('ai.analytics.min_peers', 3);

        $peers = DB::table('providers')->where('status', 'active')->whereNull('deleted_at')->where('id', '!=', $provider->id)->where('category_id', $provider->category_id)->get();
        $scope = 'category';
        $label = DB::table('provider_categories')->where('id', $provider->category_id)->value('name_fr') ?: 'votre catégorie';
        if ($peers->count() < $min) {
            $peers = DB::table('providers')->where('status', 'active')->whereNull('deleted_at')->where('id', '!=', $provider->id)->get();
            $scope = 'all';
            $label = 'l\'ensemble de l\'annuaire (votre catégorie compte trop peu de prestataires pour une comparaison fiable)';
        }
        if ($peers->count() < $min) {
            return ['scope' => 'none', 'scope_label' => '', 'peers' => $peers->count(), 'rows' => []];
        }

        $peerMetrics = $peers->map(fn ($p) => $this->metrics->for($p));
        $rows = [];
        foreach (self::METRICS as [$key, $name, $unit, $higher]) {
            $values = $peerMetrics->pluck($key)->map(fn ($v) => (float) $v)->sort()->values();
            $me = (float) $mine[$key];
            $median = $this->quantile($values->all(), 0.5);
            $p75 = $this->quantile($values->all(), 0.75);
            $below = $values->filter(fn ($v) => $v < $me)->count();
            $equal = $values->filter(fn ($v) => $v == $me)->count();
            $percentile = (int) round(($below + 0.5 * $equal) / max(1, $values->count()) * 100);
            $gap = $median > 0 ? ($me - $median) / $median * 100 : null;
            $rows[] = [
                'key' => $key, 'label' => $name, 'unit' => $unit, 'mine' => $me, 'median' => round($median, 1), 'top_quartile' => round($p75, 1),
                'percentile' => $percentile, 'gap' => $gap !== null ? round($gap) : null,
                'status' => $higher ? ($me >= $p75 && $p75 > $median ? 'top' : ($me >= $median ? 'ok' : 'low')) : 'ok',
            ];
        }

        return ['scope' => $scope, 'scope_label' => $label, 'peers' => $peers->count(), 'rows' => $rows];
    }

    /** @param float[] $sorted */
    private function quantile(array $sorted, float $q): float
    {
        $n = count($sorted);
        if ($n === 0) {
            return 0.0;
        }
        $pos = ($n - 1) * $q;
        $lo = (int) floor($pos);
        $hi = (int) ceil($pos);

        return $sorted[$lo] + ($sorted[$hi] - $sorted[$lo]) * ($pos - $lo);
    }
}
