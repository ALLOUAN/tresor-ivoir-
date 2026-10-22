<?php

namespace App\Console\Commands;

use App\Services\Ai\Catalog;
use App\Services\Ai\Taxonomy;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Génère un historique de consultations SIMULÉ (signalé is_simulated = 1) pour démontrer et
 * évaluer le filtrage collaboratif tant que le site manque d'usage réel. Chaque profil type
 * consulte surtout des fiches de ses centres d'intérêt, avec un peu de bruit.
 */
class AiSeedSimulated extends Command
{
    protected $signature = 'ai:seed-simulated {--actors=60 : nombre de visiteurs simulés} {--fresh : supprime d\'abord les données simulées existantes} {--seed=42}';

    protected $description = 'Génère des consultations simulées (marquées comme telles) pour le module de recommandation';

    private const PERSONAS = [
        ['culture', 'artisanat'],
        ['plage', 'sorties'],
        ['gastronomie', 'culture'],
        ['nature', 'aventure'],
        ['plage', 'gastronomie'],
        ['culture', 'nature'],
    ];

    public function handle(Catalog $catalog): int
    {
        if ($this->option('fresh')) {
            DB::table('ai_interactions')->where('is_simulated', true)->delete();
        }
        mt_srand((int) $this->option('seed'));
        $items = $catalog->items();
        $rows = [];
        $n = (int) $this->option('actors');

        for ($a = 0; $a < $n; $a++) {
            $persona = self::PERSONAS[$a % count(self::PERSONAS)];
            $pool = array_values(array_filter($items, fn ($i) => array_intersect($i->tags, $persona)));
            $others = array_values(array_filter($items, fn ($i) => ! array_intersect($i->tags, $persona)));
            $views = mt_rand(4, 9);
            $picked = [];
            for ($j = 0; $j < $views; $j++) {
                $useNoise = mt_rand(1, 100) <= 20 || ! $pool;
                $src = $useNoise ? $others : $pool;
                if (! $src) {
                    continue;
                }
                $item = $src[mt_rand(0, count($src) - 1)];
                $picked[$item->key] = $item;
            }
            foreach ($picked as $item) {
                $rows[] = [
                    'user_id' => null, 'session_key' => 'sim'.str_pad((string) $a, 5, '0', STR_PAD_LEFT),
                    'item_type' => $item->type, 'item_id' => $item->id, 'action' => 'view',
                    'is_simulated' => true, 'created_at' => now()->subDays(mt_rand(0, 40)),
                ];
            }
        }
        foreach (array_chunk($rows, 200) as $chunk) {
            DB::table('ai_interactions')->insert($chunk);
        }
        Cache::forget('ai.matrix');
        $this->info(count($rows).' consultations simulées pour '.$n.' visiteurs (marquées is_simulated).');

        return self::SUCCESS;
    }
}
