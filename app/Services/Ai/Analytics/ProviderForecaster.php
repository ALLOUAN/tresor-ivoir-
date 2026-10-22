<?php

namespace App\Services\Ai\Analytics;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Prévision de la fréquentation d'une fiche (vues par jour).
 *
 * Trois modèles sont mis en concurrence sur l'historique du prestataire par rétro-test glissant
 * (prévoir 7 jours, comparer au réel, décaler, recommencer) ; le plus précis est retenu :
 *   - moyenne  : moyenne des 7 derniers jours ;
 *   - semaine  : même jour de la semaine précédente ;
 *   - tendance : niveau récent × profil des jours de la semaine, avec tendance amortie.
 * Au-delà du changement de mois, la prévision est ajustée par un indice saisonnier : hypothèse
 * configurée au départ, saisonnalité apprise sur les données dès 12 mois d'historique.
 * Les intervalles à 80 % viennent des erreurs observées au rétro-test.
 */
class ProviderForecaster
{
    /** @return array<string,int> date => vues, sans trou entre la première mesure et hier */
    public function series(int $providerId, bool $withSimulated = true): array
    {
        if (! \App\Support\SchemaCache::hasTable('provider_analytics')) {
            return [];
        }
        $q = DB::table('provider_analytics')->where('provider_id', $providerId)->whereDate('date', '<', now()->toDateString());
        if (! $withSimulated && Schema::hasColumn('provider_analytics', 'is_simulated')) {
            $q->where('is_simulated', false);
        }
        $rows = $q->orderBy('date')->get(['date', 'views_count'])->groupBy(fn ($r) => substr((string) $r->date, 0, 10))->map(fn ($g) => (int) $g->sum('views_count'));
        if ($rows->isEmpty()) {
            return [];
        }
        $out = [];
        $d = Carbon::parse($rows->keys()->first());
        $end = now()->subDay()->startOfDay();
        while ($d->lte($end)) {
            $out[$d->toDateString()] = (int) ($rows[$d->toDateString()] ?? 0);
            $d->addDay();
        }

        return $out;
    }

    public function simulatedShare(int $providerId): float
    {
        if (! \App\Support\SchemaCache::hasTable('provider_analytics') || ! Schema::hasColumn('provider_analytics', 'is_simulated')) {
            return 0.0;
        }
        $total = DB::table('provider_analytics')->where('provider_id', $providerId)->count();

        return $total ? round(DB::table('provider_analytics')->where('provider_id', $providerId)->where('is_simulated', true)->count() / $total, 2) : 0.0;
    }

    /**
     * @return array{status:string,confidence:string,message:?string,history_days:int,model:?string,forecast:array,
     *               weeks:array,totals:array,backtest:?array,weekday:array,seasonal_source:string,simulated_share:float}
     */
    public function forecast(int $providerId, int $horizon = 30, ?array $series = null): array
    {
        $series ??= $this->series($providerId);
        $n = count($series);
        $min = (int) config('ai.analytics.min_days', 14);
        $base = ['history_days' => $n, 'model' => null, 'forecast' => [], 'weeks' => [], 'totals' => [], 'backtest' => null, 'weekday' => [], 'seasonal_source' => 'aucune', 'simulated_share' => $this->simulatedShare($providerId)];

        if ($n < $min) {
            return $base + ['status' => 'insufficient', 'confidence' => 'insuffisante',
                'message' => "Historique trop court ($n jour".($n > 1 ? 's' : '')." de mesures ; $min minimum). Les vues de votre fiche sont comptées chaque jour : la prévision apparaîtra dans ".max(1, $min - $n).' jours.'];
        }

        $y = array_values($series);
        $dates = array_keys($series);
        $last = Carbon::parse(end($dates));

        // Rétro-test et choix du modèle
        $bt = null;
        $model = 'tendance';
        $minBt = (int) config('ai.analytics.backtest_days', 28);
        if ($n >= $minBt) {
            $bt = $this->backtest($y, $dates);
            $model = $bt['best'];
        } elseif ($n < 21) {
            $model = 'moyenne';
        }

        // Prévision brute (avant effet de saison)
        $raw = $this->predict($model, $y, $dates, $horizon);
        $sigma = $bt ? $bt['models'][$model]['sigma'] : max(1.0, $this->std(array_slice($y, -14)));

        [$index, $source] = $this->monthlyIndex($y, $dates);
        $baseMonth = (int) $last->format('n');
        $forecast = [];
        for ($h = 1; $h <= $horizon; $h++) {
            $date = $last->copy()->addDays($h);
            $adj = ($index[(int) $date->format('n')] ?? 1.0) / max(0.1, $index[$baseMonth] ?? 1.0);
            $yhat = max(0.0, $raw[$h - 1] * $adj);
            $band = 1.28 * $sigma * (1 + 0.02 * $h);
            $forecast[] = ['date' => $date->toDateString(), 'yhat' => round($yhat, 1), 'lo' => round(max(0.0, $yhat - $band), 1), 'hi' => round($yhat + $band, 1)];
        }

        // Totaux : prévision vs réel de la période équivalente précédente
        $span = min($horizon, 30);
        $prev = array_sum(array_slice($y, -$span));
        $next = array_sum(array_map(fn ($f) => $f['yhat'], array_slice($forecast, 0, $span)));
        $lo = array_sum(array_map(fn ($f) => $f['lo'], array_slice($forecast, 0, $span)));
        $hi = array_sum(array_map(fn ($f) => $f['hi'], array_slice($forecast, 0, $span)));

        $weeks = [];
        foreach (array_chunk($forecast, 7) as $i => $chunk) {
            $weeks[] = ['from' => $chunk[0]['date'], 'to' => end($chunk)['date'], 'yhat' => round(array_sum(array_column($chunk, 'yhat'))), 'lo' => round(array_sum(array_column($chunk, 'lo'))), 'hi' => round(array_sum(array_column($chunk, 'hi')))];
        }

        $confidence = $n >= 90 && $bt && $bt['models'][$model]['wape'] <= 0.35 ? 'bonne' : ($n >= 28 ? 'moyenne' : 'faible');

        return array_merge($base, [
            'status' => 'ok', 'confidence' => $confidence, 'message' => null, 'model' => $model, 'forecast' => $forecast, 'weeks' => $weeks,
            'totals' => ['previous' => (int) $prev, 'next' => (int) round($next), 'lo' => (int) round($lo), 'hi' => (int) round($hi), 'span' => $span,
                'change' => $prev > 0 ? round(($next - $prev) / $prev * 100, 1) : null],
            'backtest' => $bt, 'weekday' => $this->weekdayProfile($y, $dates), 'seasonal_source' => $source,
        ]);
    }

    // ── Modèles ─────────────────────────────────────────────────────────────

    /** @param int[] $y @param string[] $dates @return float[] */
    public function predict(string $model, array $y, array $dates, int $h): array
    {
        $n = count($y);
        $out = [];
        if ($model === 'moyenne') {
            $m = array_sum(array_slice($y, -7)) / max(1, min(7, $n));

            return array_fill(0, $h, $m);
        }
        if ($model === 'semaine') {
            for ($i = 1; $i <= $h; $i++) {
                $idx = $n - 7 + (($i - 1) % 7);
                $out[] = (float) ($y[max(0, $idx)] ?? 0);
            }

            return $out;
        }
        // tendance : profil hebdomadaire + niveau désaisonnalisé + tendance amortie
        $win = min($n, 56);
        $recent = array_slice($y, -$win);
        $rd = array_slice($dates, -$win);
        $mean = max(0.001, array_sum($recent) / $win);
        $dow = array_fill(0, 7, [0.0, 0]);
        foreach ($recent as $i => $v) {
            $d = (int) Carbon::parse($rd[$i])->dayOfWeek;
            $dow[$d][0] += $v;
            $dow[$d][1]++;
        }
        $factor = [];
        for ($d = 0; $d < 7; $d++) {
            $factor[$d] = $dow[$d][1] ? ($dow[$d][0] / $dow[$d][1]) / $mean : 1.0;
        }
        $des = [];
        foreach ($recent as $i => $v) {
            $f = $factor[(int) Carbon::parse($rd[$i])->dayOfWeek];
            $des[] = $f > 0.05 ? $v / $f : $v;
        }
        $k = min(14, count($des));
        $level = array_sum(array_slice($des, -7)) / min(7, count($des));
        $prevLevel = count($des) >= 14 ? array_sum(array_slice($des, -14, 7)) / 7 : $level;
        $trend = ($level - $prevLevel) / 7;
        $phi = 0.9;
        $last = Carbon::parse(end($dates));
        $acc = 0.0;
        $damp = 1.0;
        for ($i = 1; $i <= $h; $i++) {
            $damp *= $phi;
            $acc += $damp;
            $f = $factor[(int) $last->copy()->addDays($i)->dayOfWeek];
            $out[] = max(0.0, ($level + $trend * $acc) * $f);
        }
        unset($k);

        return $out;
    }

    /**
     * Rétro-test glissant : prévoir 7 jours à partir de chaque origine, comparer au réel.
     *
     * @return array{best:string,origins:int,models:array<string,array{mae:float,mape:?float,wape:float,sigma:float}>}
     */
    public function backtest(array $y, array $dates, int $horizon = 7): array
    {
        $n = count($y);
        $origins = [];
        for ($o = $n - $horizon; $o >= 21; $o -= 7) {
            $origins[] = $o;
        }
        if (! $origins) {
            $origins = [max(14, $n - $horizon)];
        }
        $stats = [];
        foreach (['moyenne', 'semaine', 'tendance'] as $m) {
            $abs = 0.0;
            $act = 0.0;
            $pct = [];
            $errs = [];
            foreach ($origins as $o) {
                $pred = $this->predict($m, array_slice($y, 0, $o), array_slice($dates, 0, $o), $horizon);
                for ($i = 0; $i < $horizon && $o + $i < $n; $i++) {
                    $a = $y[$o + $i];
                    $e = $a - $pred[$i];
                    $abs += abs($e);
                    $act += $a;
                    $errs[] = $e;
                    if ($a > 0) {
                        $pct[] = abs($e) / $a;
                    }
                }
            }
            $cnt = max(1, count($errs));
            $stats[$m] = [
                'mae' => round($abs / $cnt, 2), 'mape' => $pct ? round(array_sum($pct) / count($pct) * 100, 1) : null,
                'wape' => $act > 0 ? round($abs / $act, 3) : 1.0, 'sigma' => round($this->std($errs), 2),
            ];
        }
        $best = array_keys($stats)[0];
        foreach ($stats as $m => $s) {
            if ($s['wape'] < $stats[$best]['wape'] - 0.005) {
                $best = $m;
            }
        }

        return ['best' => $best, 'origins' => count($origins), 'models' => $stats];
    }

    /** Indice mensuel : appris dès 12 mois, sinon hypothèse configurée. @return array{0:array<int,float>,1:string} */
    private function monthlyIndex(array $y, array $dates): array
    {
        if (count($y) >= 365) {
            $sum = [];
            $cnt = [];
            foreach ($y as $i => $v) {
                $m = (int) substr($dates[$i], 5, 2);
                $sum[$m] = ($sum[$m] ?? 0) + $v;
                $cnt[$m] = ($cnt[$m] ?? 0) + 1;
            }
            $all = array_sum($y) / count($y);
            if ($all > 0 && count($sum) === 12) {
                return [array_map(fn ($m) => ($sum[$m] / $cnt[$m]) / $all, array_combine(range(1, 12), range(1, 12))), 'apprise'];
            }
        }

        return [config('ai.analytics.seasonal_prior'), 'hypothèse'];
    }

    /** @return array<int,array{day:string,share:float}> part de la fréquentation hebdomadaire par jour */
    private function weekdayProfile(array $y, array $dates): array
    {
        $names = ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'];
        $win = min(count($y), 56);
        $sum = array_fill(0, 7, 0.0);
        foreach (array_slice($y, -$win) as $i => $v) {
            $sum[(int) Carbon::parse(array_slice($dates, -$win)[$i])->dayOfWeek] += $v;
        }
        $tot = array_sum($sum) ?: 1;
        $out = [];
        foreach ([1, 2, 3, 4, 5, 6, 0] as $d) {
            $out[] = ['day' => $names[$d], 'share' => round($sum[$d] / $tot * 100, 1)];
        }

        return $out;
    }

    private function std(array $v): float
    {
        $n = count($v);
        if ($n < 2) {
            return 0.0;
        }
        $m = array_sum($v) / $n;

        return sqrt(array_sum(array_map(fn ($x) => ($x - $m) ** 2, $v)) / ($n - 1));
    }

    // ── Demande de réservation (hébergements) ───────────────────────────────

    /**
     * Demandes de réservation par semaine (table reservations) et prévision des 4 prochaines semaines.
     *
     * @return array{status:string,weeks:array,forecast:array,message:?string}
     */
    public function bookings(int $providerId): array
    {
        if (! \App\Support\SchemaCache::hasTable('reservations')) {
            return ['status' => 'none', 'weeks' => [], 'forecast' => [], 'message' => null];
        }
        $rows = DB::table('reservations')->where('provider_id', $providerId)->whereNotIn('status', ['cancelled', 'canceled'])->get(['created_at']);
        if ($rows->count() < 3) {
            return ['status' => 'insufficient', 'weeks' => [], 'forecast' => [], 'message' => 'Pas encore assez de réservations pour estimer la demande ('.$rows->count().' enregistrée'.($rows->count() > 1 ? 's' : '').').'];
        }
        $byWeek = $rows->groupBy(fn ($r) => Carbon::parse($r->created_at)->startOfWeek()->toDateString())->map->count();
        $start = Carbon::parse($byWeek->keys()->min());
        $end = now()->startOfWeek()->subWeek();
        $weeks = [];
        for ($d = $start->copy(); $d->lte($end); $d->addWeek()) {
            $weeks[] = ['from' => $d->toDateString(), 'count' => (int) ($byWeek[$d->toDateString()] ?? 0)];
        }
        if (count($weeks) < 6) {
            return ['status' => 'insufficient', 'weeks' => $weeks, 'forecast' => [], 'message' => 'Moins de 6 semaines d\'historique de réservations : la prévision de demande n\'est pas encore fiable.'];
        }
        $counts = array_column($weeks, 'count');
        $avg = array_sum(array_slice($counts, -4)) / 4;
        $sd = $this->std(array_slice($counts, -8));
        $forecast = [];
        for ($i = 1; $i <= 4; $i++) {
            $from = $end->copy()->addWeeks($i);
            $adj = (config('ai.analytics.seasonal_prior')[(int) $from->format('n')] ?? 1.0) / (config('ai.analytics.seasonal_prior')[(int) $end->format('n')] ?? 1.0);
            $y = $avg * $adj;
            $forecast[] = ['from' => $from->toDateString(), 'yhat' => round($y, 1), 'lo' => round(max(0, $y - 1.28 * $sd), 1), 'hi' => round($y + 1.28 * $sd, 1)];
        }

        return ['status' => 'ok', 'weeks' => $weeks, 'forecast' => $forecast, 'message' => null];
    }
}
