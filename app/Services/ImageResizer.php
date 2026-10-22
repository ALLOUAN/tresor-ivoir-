<?php

namespace App\Services;

use GdImage;
use Illuminate\Support\Facades\Storage;

/**
 * Variantes redimensionnées (WebP) des images du disque public, générées à la demande
 * et conservées sur disque. Les fichiers d'origine ne sont jamais modifiés.
 */
class ImageResizer
{
    /** Largeurs autorisées (px) : limite le nombre de variantes générées. */
    public const WIDTHS = [400, 800, 1200, 1600, 1920];

    public const EXTENSIONS = ['png', 'jpg', 'jpeg', 'webp'];

    /** Chemin absolu de l'original, ou null s'il est absent / hors du disque public / non géré. */
    public function sourcePath(string $relative): ?string
    {
        $relative = ltrim(str_replace('\\', '/', $relative), '/');
        if ($relative === '' || str_contains($relative, '..') || str_contains($relative, "\0")) {
            return null;
        }
        if (! in_array(strtolower(pathinfo($relative, PATHINFO_EXTENSION)), self::EXTENSIONS, true)) {
            return null;
        }

        $root = realpath(Storage::disk('public')->path(''));
        $abs = realpath(Storage::disk('public')->path($relative));
        if ($root === false || $abs === false || ! is_file($abs) || ! str_starts_with($abs, $root)) {
            return null;
        }

        return $abs;
    }

    /**
     * Chemin absolu du fichier à servir pour cette largeur : la variante WebP,
     * ou l'original quand la variante ne ferait pas gagner de poids.
     */
    public function variant(string $relative, int $width): ?string
    {
        if (! in_array($width, self::WIDTHS, true)) {
            return null;
        }
        $source = $this->sourcePath($relative);
        if ($source === null) {
            return null;
        }

        $dir = storage_path('app/img-cache/'.$width);
        $cached = $dir.'/'.sha1($relative).'-'.filemtime($source).'.webp';
        if (is_file($cached)) {
            return filesize($cached) < filesize($source) ? $cached : $source;
        }

        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $img = $this->load($source);
        if ($img === null) {
            return $source;
        }

        if (imagesx($img) > $width) {
            $scaled = imagescale($img, $width, -1, IMG_BICUBIC);
            if ($scaled instanceof GdImage) {
                $img = $scaled;
            }
        }
        imagepalettetotruecolor($img);
        imagealphablending($img, false);
        imagesavealpha($img, true);

        $tmp = $cached.'.'.getmypid().'.tmp';
        $ok = @imagewebp($img, $tmp, 80);
        if (! $ok || ! is_file($tmp) || filesize($tmp) === 0) {
            @unlink($tmp);

            return $source;
        }
        @rename($tmp, $cached);

        return filesize($cached) < filesize($source) ? $cached : $source;
    }

    /**
     * Image de partage (aperçu Facebook, WhatsApp, LinkedIn, X…) : JPEG 1200 × 630 recadré au centre.
     * Le JPEG est le seul format lu partout (WhatsApp et certains réseaux ignorent le WebP des couvertures).
     */
    public function shareImage(string $relative): ?string
    {
        $source = $this->sourcePath($relative);
        if ($source === null) {
            return null;
        }

        $dir = storage_path('app/img-cache/og');
        $cached = $dir.'/'.sha1($relative).'-'.filemtime($source).'.jpg';
        if (is_file($cached)) {
            return $cached;
        }
        if (! is_dir($dir)) {
            @mkdir($dir, 0775, true);
        }

        $img = $this->load($source);
        if ($img === null) {
            return null;
        }

        [$w, $h] = [imagesx($img), imagesy($img)];
        [$tw, $th] = [1200, 630];
        $scale = max($tw / $w, $th / $h);
        $cropW = (int) round($tw / $scale);
        $cropH = (int) round($th / $scale);
        $srcX = (int) max(0, ($w - $cropW) / 2);
        $srcY = (int) max(0, ($h - $cropH) / 2);

        $out = imagecreatetruecolor($tw, $th);
        imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));   // fond blanc sous les zones transparentes (PNG)
        imagecopyresampled($out, $img, 0, 0, $srcX, $srcY, $tw, $th, $cropW, $cropH);

        $tmp = $cached.'.'.getmypid().'.tmp';
        $ok = @imagejpeg($out, $tmp, 85);
        if (! $ok || ! is_file($tmp) || filesize($tmp) === 0) {
            @unlink($tmp);

            return null;
        }
        @rename($tmp, $cached);

        return $cached;
    }

    private function load(string $path): ?GdImage
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $img = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => @imagecreatefromwebp($path),
            default => false,
        };
        if (! $img instanceof GdImage) {
            return null;
        }

        // Photos de téléphone : respecter l'orientation EXIF.
        if (in_array($ext, ['jpg', 'jpeg'], true) && function_exists('exif_read_data')) {
            $exif = @exif_read_data($path);
            $angle = match ((int) ($exif['Orientation'] ?? 1)) {
                3 => 180,
                6 => -90,
                8 => 90,
                default => 0,
            };
            if ($angle !== 0) {
                $rotated = imagerotate($img, $angle, 0);
                if ($rotated instanceof GdImage) {
                    $img = $rotated;
                }
            }
        }

        return $img;
    }
}
