<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Génère un historique de fréquentation SIMULÉ (is_simulated = 1) pour démontrer les prévisions tant que
 * le site n'a pas accumulé de vraies mesures. Le modèle de simulation (profil hebdomadaire, tendance,
 * saison, bruit, pics ponctuels) est écrit par le développeur : il prouve que la chaîne fonctionne, pas
 * la précision que l'on obtiendrait sur de vrais visiteurs.
 */
class AiSeedAnalytics extends Command
{
    protected $signature = 'ai:seed-analytics {--days=180 : jours d\'historique} {--fresh : supprime d\'abord les mesures simulées} {--seed=7}';

    protected $description = 'Génère un historique de vues simulé pour les prestataires actifs (marqué is_simulated)';

    public function handle(): int
    {
        if ($this->option('fresh')) {
            DB::table('provider_analytics')->where('is_simulated', true)->delete();
        }
        mt_srand((int) $this->option('seed'));
        $days = max(14, (int) $this->option('days'));
        $prior = config('ai.analytics.seasonal_prior');
        $weekly = [0 => 1.25, 1 => 0.8, 2 => 0.85, 3 => 0.9, 4 => 1.0, 5 => 1.2, 6 => 1.4];   // dimanche … samedi
        $rows = [];

        foreach (DB::table('providers')->where('status', 'active')->whereNull('deleted_at')->get(['id', 'views_count']) as $p) {
            $base = max(4.0, min(60.0, $p->views_count / 8));
            $trend = (mt_rand(-30, 60) / 100) / $days;                    // dérive lente sur la période
            $existing = DB::table('provider_analytics')->where('provider_id', $p->id)->pluck('date')->map(fn ($d) => substr((string) $d, 0, 10))->flip();
            for ($i = $days; $i >= 1; $i--) {
                $date = now()->subDays($i)->startOfDay();
                if (isset($existing[$date->toDateString()])) {
                    continue;
                }
                $mu = $base * (1 + $trend * ($days - $i)) * $weekly[$date->dayOfWeek] * $prior[(int) $date->format('n')];
                $noise = 1 + (mt_rand(-250, 250) / 1000);
                $spike = mt_rand(1, 100) <= 3 ? mt_rand(18, 30) / 10 : 1.0;   // un événement, un article partagé…
                $views = (int) max(0, round($mu * $noise * $spike));
                $rows[] = [
                    'provider_id' => $p->id, 'date' => $date->toDateString(), 'views_count' => $views, 'clicks_phone' => 0, 'clicks_website' => 0,
                    'clicks_direction' => (int) round($views * mt_rand(1, 5) / 100), 'new_reviews' => 0, 'search_appearances' => 0,
                    'is_simulated' => true, 'created_at' => Carbon::now(),
                ];
            }
        }
        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('provider_analytics')->insert($chunk);
        }
        $this->info(count($rows).' mesures quotidiennes simulées (marquées is_simulated) sur '.$days.' jours.');

        return self::SUCCESS;
    }
}
