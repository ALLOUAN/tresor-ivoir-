<?php

namespace App\Console\Commands;

use App\Support\ImageCompressor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

/**
 * Allège une fois pour toutes les images déjà en ligne (storage/app/public) : côté le plus long
 * ramené à 2 000 px et recompression, même nom et même format (aucune URL ne change).
 * Les variantes /img/… se régénèrent d'elles-mêmes (leur cache dépend de la date du fichier).
 *
 *   php artisan images:shrink --dry-run      liste ce qui serait allégé
 *   php artisan images:shrink                 allège
 *   php artisan images:shrink --folder=cultural
 */
class ShrinkStoredImages extends Command
{
    protected $signature = 'images:shrink
        {--folder= : Sous-dossier de storage/app/public à traiter (tout par défaut)}
        {--dry-run : Afficher les images concernées sans les modifier}
        {--limit=0 : Nombre maximal d\'images à traiter (0 = toutes)}';

    protected $description = 'Allège les images déjà téléversées (2 000 px max, recompression, même format)';

    public function handle(): int
    {
        $disk = Storage::disk('public');
        $root = $disk->path('');
        $folder = trim((string) $this->option('folder'), '/');
        $dry = (bool) $this->option('dry-run');
        $limit = max(0, (int) $this->option('limit'));

        $files = collect($disk->allFiles($folder))
            ->filter(fn ($f) => preg_match('/\.(jpe?g|png|webp)$/i', $f))
            ->values();

        $done = 0;
        $saved = 0;
        $bar = $this->output->createProgressBar($files->count());
        foreach ($files as $relative) {
            $path = $root.$relative;
            $info = @getimagesize($path);
            $big = is_array($info) && max((int) $info[0], (int) $info[1]) > ImageCompressor::MAX_SIDE;
            if ($dry) {
                if ($big || filesize($path) > 350 * 1024) {
                    $this->line(sprintf("\n  %s  %s ko%s", $relative, number_format(filesize($path) / 1024, 0, ',', ' '), $big ? '  ('.$info[0].'×'.$info[1].')' : ''));
                    $done++;
                }
            } elseif (($gain = ImageCompressor::shrink($path)) !== null) {
                $saved += $gain;
                $done++;
            }
            $bar->advance();
            if ($limit && $done >= $limit) {
                break;
            }
        }
        $bar->finish();
        $this->newLine(2);

        $this->info($dry
            ? sprintf('%d image(s) seraient allégées sur %d.', $done, $files->count())
            : sprintf('%d image(s) allégée(s) sur %d — %s Mo gagnés.', $done, $files->count(), number_format($saved / 1048576, 1, ',', ' ')));

        return self::SUCCESS;
    }
}
