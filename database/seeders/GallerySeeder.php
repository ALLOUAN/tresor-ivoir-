<?php

namespace Database\Seeders;

use App\Models\SiteMediaItem;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Seeder de démonstration pour la galerie publique (/galerie-tresors-ivoire) :
 * génère 100 visuels réels (images JPEG produites via GD, pas de liens externes
 * ni d'URLs inventées) avec des dimensions variées pour exercer correctement la
 * grille en cascade (masonry). Non appelé depuis DatabaseSeeder — à lancer à la
 * demande : php artisan db:seed --class=GallerySeeder
 */
class GallerySeeder extends Seeder
{
    private const COUNT = 100;

    /** @var array<int, array{0:int,1:int,2:int}> Palette RVB, en rotation. */
    private const PALETTE = [
        [242, 121, 15], [22, 101, 52], [180, 83, 9], [30, 58, 138], [136, 19, 55],
        [15, 118, 110], [154, 52, 18], [55, 48, 163], [12, 74, 110], [120, 53, 15],
    ];

    /** @var array<int, array{0:int,1:int}> Largeur/hauteur, en rotation — variété de ratios pour le masonry. */
    private const DIMENSIONS = [
        [600, 800], [600, 900], [600, 600], [600, 420], [600, 750],
        [600, 1000], [600, 460], [600, 850], [600, 520], [600, 700],
    ];

    private const TITLES = [
        'Lumière du matin', 'Instant capturé', 'Regard sur la côte', 'Éclat de couleur',
        'Silhouette au crépuscule', 'Détail d\'artisanat', 'Scène de vie', 'Horizon partagé',
        'Motif traditionnel', 'Reflet du fleuve',
    ];

    public function run(): void
    {
        if (! extension_loaded('gd')) {
            $this->command?->warn('GallerySeeder ignoré : extension PHP GD indisponible (nécessaire pour générer les visuels).');

            return;
        }

        $uploader = User::query()->where('role', 'admin')->orderBy('id')->first()
            ?? User::query()->orderBy('id')->first();

        if (! $uploader) {
            $this->command?->warn('GallerySeeder ignoré : aucun utilisateur trouvé pour uploaded_by (lancez UserSeeder avant).');

            return;
        }

        $folder = 'site/media-library/seed';
        Storage::disk('public')->makeDirectory($folder);

        for ($i = 1; $i <= self::COUNT; $i++) {
            [$width, $height] = self::DIMENSIONS[$i % count(self::DIMENSIONS)];
            [$r, $g, $b] = self::PALETTE[$i % count(self::PALETTE)];
            $title = self::TITLES[$i % count(self::TITLES)].' '.$i;

            $filename = "gallery-seed-{$i}.jpg";
            $relativePath = "{$folder}/{$filename}";
            $absolutePath = Storage::disk('public')->path($relativePath);

            $this->generateImage($absolutePath, $width, $height, $r, $g, $b, (string) $i);

            SiteMediaItem::updateOrCreate(
                ['file_path' => $relativePath],
                [
                    'uuid' => (string) Str::uuid(),
                    'type' => 'image',
                    'mime_type' => 'image/jpeg',
                    'original_name' => $filename,
                    'title' => $title,
                    'alt_text' => $title,
                    'caption' => $i % 3 === 0 ? 'Visuel de démonstration généré pour la galerie publique.' : null,
                    'credit' => $i % 4 === 0 ? 'Studio Trésors d\'Ivoire' : null,
                    'section' => 'home_gallery',
                    'is_active' => true,
                    'is_featured' => $i % 10 === 0,
                    'display_order' => $i,
                    'published_at' => now(),
                    'url' => '/storage/'.$relativePath,
                    'size_bytes' => is_file($absolutePath) ? (filesize($absolutePath) ?: 0) : 0,
                    'uploaded_by' => $uploader->id,
                ]
            );
        }

        $this->command?->info('GallerySeeder exécuté : '.self::COUNT.' visuels générés pour la galerie publique.');
    }

    private function generateImage(string $path, int $width, int $height, int $r, int $g, int $b, string $label): void
    {
        $im = imagecreatetruecolor($width, $height);

        // Dégradé vertical simple (couleur pleine en haut, ton assombri en bas).
        for ($y = 0; $y < $height; $y++) {
            $ratio = $y / max(1, $height - 1);
            $lineColor = imagecolorallocate(
                $im,
                (int) ($r - $r * 0.5 * $ratio),
                (int) ($g - $g * 0.5 * $ratio),
                (int) ($b - $b * 0.5 * $ratio),
            );
            imageline($im, 0, $y, $width, $y, $lineColor);
        }

        $white = imagecolorallocate($im, 255, 255, 255);
        $font = 5;
        $text = '#'.$label;
        $textWidth = imagefontwidth($font) * strlen($text);
        $textHeight = imagefontheight($font);
        imagestring($im, $font, (int) (($width - $textWidth) / 2), (int) (($height - $textHeight) / 2), $text, $white);

        imagejpeg($im, $path, 85);
        imagedestroy($im);
    }
}
