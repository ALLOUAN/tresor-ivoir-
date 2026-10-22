<?php

namespace App\Console\Commands;

use App\Services\Ai\Moderation\ModerationService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AiModerate extends Command
{
    protected $signature = 'ai:moderate {--no-images : ne pas analyser les photos} {--fresh : repart de zéro (supprime les rapports existants)}';

    protected $description = 'Analyse les contenus (articles, avis, fiches, événements, photos) et alimente la file de modération assistée';

    public function handle(ModerationService $service): int
    {
        if ($this->option('fresh')) {
            DB::table('moderation_reports')->delete();
        }
        $n = $service->scanAll(! $this->option('no-images'));
        $this->info(sprintf(
            'Analysés : %d articles, %d avis, %d prestataires, %d événements, %d photos (%d photos externes ignorées).',
            $n['article'], $n['review'], $n['provider'], $n['event'], $n['image'], $n['image_skipped']
        ));
        $rows = DB::table('moderation_reports')->select('verdict', DB::raw('COUNT(*) c'))->groupBy('verdict')->pluck('c', 'verdict');
        $this->line('Verdicts : ok='.($rows['ok'] ?? 0).'  à vérifier='.($rows['review'] ?? 0).'  problème probable='.($rows['likely'] ?? 0));

        return self::SUCCESS;
    }
}
