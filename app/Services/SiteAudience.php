<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Audience du site, sans cookie et sans donnée personnelle conservée (méthode de Plausible Analytics) :
 * un visiteur = empreinte SHA-256 de (sel du jour + IP + navigateur). Le sel est aléatoire, change chaque
 * jour et n'est pas conservé au-delà de 48 h : impossible de retrouver l'IP ni de suivre quelqu'un d'un jour
 * à l'autre. Les « visites » d'un mois ou d'une année sont donc la somme des visiteurs uniques de chaque jour.
 */
class SiteAudience
{
    /** Délai pendant lequel un visiteur est compté « en ligne » après sa dernière page vue. */
    public const ONLINE_MINUTES = 5;

    /** Conservation des lignes journalières (graphique sur 12 mois + marge). */
    public const RETENTION_DAYS = 400;

    private const MOIS = ['Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Jun', 'Jul', 'Aoû', 'Sep', 'Oct', 'Nov', 'Déc'];

    private const JOURS = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];

    /** Enregistre une page vue (au plus une écriture par visiteur et par minute). */
    public function record(Request $request): void
    {
        $now = now();
        $date = $now->toDateString();
        $salt = Cache::remember('site_audience:salt:'.$date, now()->addHours(48), fn () => Str::random(40));
        $hash = hash('sha256', $salt.'|'.$request->ip().'|'.$request->userAgent().'|'.$request->getHost());

        if (! Cache::add('site_audience:seen:'.$hash, 1, 60)) {
            return;
        }

        DB::table('site_visitors')->upsert(
            [[
                'date' => $date, 'visitor_hash' => $hash, 'user_id' => $request->user()?->getKey(),
                'hits' => 1, 'first_seen_at' => $now, 'last_seen_at' => $now,
            ]],
            ['date', 'visitor_hash'],
            ['last_seen_at' => $now, 'hits' => DB::raw('hits + 1')]
        );
    }

    public function online(): int
    {
        return DB::table('site_visitors')->where('last_seen_at', '>=', now()->subMinutes(self::ONLINE_MINUTES))->count();
    }

    /** Chiffres clés + séries des graphiques (aujourd'hui par heure, 7 jours, 30 jours, 12 mois). */
    public function summary(): array
    {
        $today = Carbon::today();
        $perDay = DB::table('site_visitors')
            ->where('date', '>=', $today->copy()->subMonths(11)->startOfMonth()->toDateString())
            ->selectRaw('date, count(*) as total')
            ->groupBy('date')
            ->pluck('total', 'date')
            ->mapWithKeys(fn ($n, $d) => [Carbon::parse($d)->toDateString() => (int) $n]);

        $sumBetween = fn (Carbon $from, Carbon $to) => $perDay
            ->filter(fn ($n, $d) => $d >= $from->toDateString() && $d <= $to->toDateString())->sum();

        // Aujourd'hui : nouveaux visiteurs par heure d'arrivée.
        $hours = array_fill(0, 24, 0);
        DB::table('site_visitors')->where('date', $today->toDateString())->pluck('first_seen_at')
            ->each(function ($t) use (&$hours) { $hours[(int) Carbon::parse($t)->format('G')]++; });

        $days = fn (int $n) => collect(range($n - 1, 0))->map(fn ($i) => $today->copy()->subDays($i));
        $week = $days(7);
        $month = $days(30);
        $months = collect(range(11, 0))->map(fn ($i) => $today->copy()->startOfMonth()->subMonths($i));

        return [
            'online' => $this->online(),
            'today' => $perDay[$today->toDateString()] ?? 0,
            'week' => $sumBetween($today->copy()->subDays(6), $today),
            'month' => $sumBetween($today->copy()->startOfMonth(), $today),
            'year' => $sumBetween($today->copy()->startOfYear(), $today),
            'series' => [
                'day' => ['labels' => array_map(fn ($h) => sprintf('%02dh', $h), range(0, 23)), 'values' => $hours],
                'week' => [
                    'labels' => $week->map(fn ($d) => self::JOURS[$d->dayOfWeek].' '.$d->format('d'))->all(),
                    'values' => $week->map(fn ($d) => $perDay[$d->toDateString()] ?? 0)->all(),
                ],
                'month' => [
                    'labels' => $month->map(fn ($d) => $d->format('d/m'))->all(),
                    'values' => $month->map(fn ($d) => $perDay[$d->toDateString()] ?? 0)->all(),
                ],
                'year' => [
                    'labels' => $months->map(fn ($m) => self::MOIS[$m->month - 1].' '.$m->format('y'))->all(),
                    'values' => $months->map(fn ($m) => $sumBetween($m, $m->copy()->endOfMonth()))->all(),
                ],
            ],
        ];
    }

    public function prune(): int
    {
        return DB::table('site_visitors')->where('date', '<', now()->subDays(self::RETENTION_DAYS)->toDateString())->delete();
    }
}
