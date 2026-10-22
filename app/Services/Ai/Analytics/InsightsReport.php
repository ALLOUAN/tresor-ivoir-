<?php

namespace App\Services\Ai\Analytics;

/**
 * Assemble le rapport « analyse avancée » d'un prestataire selon son forfait :
 *  - Bronze (stats « basic ») : complétude de la fiche seulement, le reste est verrouillé ;
 *  - Silver (« advanced ») : prévision 30 jours, comparaison à la catégorie, 3 suggestions ;
 *  - Gold (« full ») : horizon 90 jours, demande de réservation, toutes les suggestions, export.
 */
class InsightsReport
{
    public function __construct(
        private readonly PlanAccess $plans,
        private readonly ProviderProfileMetrics $metrics,
        private readonly ProviderForecaster $forecaster,
        private readonly CategoryBenchmark $benchmark,
        private readonly ImprovementAdvisor $advisor,
    ) {}

    /** @return array<string,mixed> */
    public function build(object $provider, ?int $forceRank = null): array
    {
        $plan = $this->plans->for((int) $provider->id);
        $rank = $forceRank ?? $plan['rank'];
        $m = $this->metrics->for($provider);

        $report = ['provider' => $provider, 'plan' => $plan, 'rank' => $rank, 'metrics' => $m, 'forecast' => null, 'bookings' => null, 'benchmark' => null, 'advice' => [], 'advice_total' => 0];
        if ($rank < 2) {
            return $report;
        }

        $horizon = $rank >= 3 ? 90 : 30;
        $forecast = $this->forecaster->forecast((int) $provider->id, $horizon);
        $bench = $this->benchmark->compare($provider, $m);
        $advice = $this->advisor->advise($provider, $m, $bench, $forecast, $plan);

        $report['forecast'] = $forecast;
        $report['benchmark'] = $bench;
        $report['advice_total'] = count($advice);
        $report['advice'] = $rank >= 3 ? $advice : array_slice($advice, 0, 3);
        if ($rank >= 3 && (int) $provider->category_id && $this->isLodging($provider)) {
            $report['bookings'] = $this->forecaster->bookings((int) $provider->id);
        }
        // Historique récent pour le graphique (60 derniers jours)
        $report['recent'] = array_slice($this->forecaster->series((int) $provider->id), -60, null, true);

        return $report;
    }

    private function isLodging(object $provider): bool
    {
        return (bool) \Illuminate\Support\Facades\DB::table('reservations')->where('provider_id', $provider->id)->exists()
            || (bool) \Illuminate\Support\Facades\DB::table('accommodations')->where('provider_id', $provider->id)->exists();
    }
}
