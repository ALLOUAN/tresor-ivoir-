<?php

namespace App\Services\Ai\Analytics;

use Illuminate\Support\Facades\DB;

/**
 * Évaluation de la prévision de fréquentation : pour chaque prestataire ayant au moins 56 jours
 * d'historique, on masque les 14 derniers jours, on prévoit, puis on compare au réel (MAE, MAPE, WAPE),
 * avec deux repères naïfs (moyenne de la dernière semaine, même jour de la semaine précédente).
 * Sur des données SIMULÉES, ce test vérifie la chaîne de calcul, pas la précision future sur de vrais visiteurs.
 */
class AnalyticsEvaluator
{
    public function __construct(private readonly ProviderForecaster $forecaster) {}

    /** @return array<string,mixed> */
    public function run(int $holdout = 14): array
    {
        $acc = ['auto' => $this->blank(), 'moyenne' => $this->blank(), 'semaine' => $this->blank()];
        $inside = 0;
        $points = 0;
        $providers = 0;
        $simulated = 0.0;
        $perProvider = [];

        foreach (DB::table('providers')->where('status', 'active')->whereNull('deleted_at')->pluck('id') as $pid) {
            $series = $this->forecaster->series((int) $pid);
            if (count($series) < 56) {
                continue;
            }
            $dates = array_keys($series);
            $y = array_values($series);
            $n = count($y);
            $trainY = array_slice($y, 0, $n - $holdout);
            $trainD = array_slice($dates, 0, $n - $holdout);
            $test = array_slice($y, $n - $holdout);

            $providers++;
            $simulated += $this->forecaster->simulatedShare((int) $pid);

            $fc = $this->forecaster->forecast((int) $pid, $holdout, array_combine($trainD, $trainY));
            if ($fc['status'] !== 'ok') {
                continue;
            }
            $auto = array_column($fc['forecast'], 'yhat');
            $this->add($acc['auto'], $test, $auto);
            foreach ($fc['forecast'] as $i => $f) {
                $points++;
                $inside += ($test[$i] >= $f['lo'] && $test[$i] <= $f['hi']) ? 1 : 0;
            }
            foreach (['moyenne', 'semaine'] as $m) {
                $this->add($acc[$m], $test, $this->forecaster->predict($m, $trainY, $trainD, $holdout));
            }
            $perProvider[] = ['id' => (int) $pid, 'model' => $fc['model'], 'wape' => $this->wape($test, $auto)];
        }

        $metrics = [];
        foreach ($acc as $name => $a) {
            $metrics[$name] = [
                'mae' => $a['n'] ? round($a['abs'] / $a['n'], 2) : null,
                'mape' => $a['pn'] ? round($a['pct'] / $a['pn'] * 100, 1) : null,
                'wape' => $a['act'] > 0 ? round($a['abs'] / $a['act'], 3) : null,
            ];
        }
        $models = array_count_values(array_column($perProvider, 'model'));

        return [
            'providers' => $providers, 'holdout' => $holdout, 'simulated_share' => $providers ? round($simulated / $providers, 2) : 0.0,
            'metrics' => $metrics, 'interval_coverage' => $points ? round($inside / $points, 3) : null, 'models' => $models,
        ];
    }

    private function blank(): array
    {
        return ['abs' => 0.0, 'act' => 0.0, 'n' => 0, 'pct' => 0.0, 'pn' => 0];
    }

    private function add(array &$a, array $actual, array $pred): void
    {
        foreach ($actual as $i => $v) {
            $e = abs($v - ($pred[$i] ?? 0));
            $a['abs'] += $e;
            $a['act'] += $v;
            $a['n']++;
            if ($v > 0) {
                $a['pct'] += $e / $v;
                $a['pn']++;
            }
        }
    }

    private function wape(array $actual, array $pred): float
    {
        $abs = 0.0;
        foreach ($actual as $i => $v) {
            $abs += abs($v - ($pred[$i] ?? 0));
        }

        return array_sum($actual) > 0 ? round($abs / array_sum($actual), 3) : 0.0;
    }
}
