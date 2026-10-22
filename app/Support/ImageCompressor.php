<?php

namespace App\Support;

use GdImage;

/**
 * Allège une image sur place : côté le plus long ramené à MAX_SIDE pixels, recompression,
 * orientation EXIF des photos de téléphone appliquée. Le format (et donc le nom du fichier)
 * ne change pas : aucune URL existante n'est cassée. En cas de doute (format non géré,
 * image trop grande pour la mémoire, échec d'encodage), le fichier d'origine est conservé.
 */
final class ImageCompressor
{
    public const MAX_SIDE = 2000;

    /** En dessous de ce poids, une image déjà à la bonne taille n'est pas retouchée. */
    private const MIN_BYTES = 350 * 1024;

    /** Au-delà, l'image est trop lourde à décoder sur un hébergement mutualisé : on la laisse. */
    private const MAX_PIXELS = 50_000_000;

    private const QUALITY_JPEG = 82;

    private const QUALITY_WEBP = 82;

    /**
     * @param  string|null  $format  jpeg|png|webp ; déduit du contenu si absent (fichiers temporaires sans extension)
     * @return int|null octets gagnés, ou null si le fichier n'a pas été modifié
     */
    public static function shrink(string $path, ?string $format = null): ?int
    {
        if (! is_file($path) || ! is_writable($path) || ! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $info = @getimagesize($path);
        if (! is_array($info)) {
            return null;
        }
        $format ??= match ($info['mime'] ?? '') {
            'image/jpeg' => 'jpeg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => null,
        };
        if ($format === null || ($format === 'webp' && ! function_exists('imagewebp'))) {
            return null;
        }

        [$w, $h] = [(int) $info[0], (int) $info[1]];
        $before = (int) filesize($path);
        $needsResize = max($w, $h) > self::MAX_SIDE;
        if ($w < 1 || $h < 1 || $w * $h > self::MAX_PIXELS || (! $needsResize && $before < self::MIN_BYTES)) {
            return null;
        }

        self::ensureMemory($w * $h);

        $img = self::load($path, $format);
        if (! $img instanceof GdImage) {
            return null;
        }
        // L'orientation EXIF peut avoir inversé largeur et hauteur.
        [$w, $h] = [imagesx($img), imagesy($img)];

        if ($needsResize) {
            $ratio = self::MAX_SIDE / max($w, $h);
            $nw = max(1, (int) round($w * $ratio));
            $nh = max(1, (int) round($h * $ratio));
            $out = imagecreatetruecolor($nw, $nh);
            if ($format !== 'jpeg') {
                imagealphablending($out, false);
                imagesavealpha($out, true);
                imagefill($out, 0, 0, imagecolorallocatealpha($out, 0, 0, 0, 127));
            }
            imagecopyresampled($out, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
            imagedestroy($img);
            $img = $out;
        } elseif ($format !== 'jpeg') {
            imagealphablending($img, false);
            imagesavealpha($img, true);
        }

        $tmp = $path.'.'.getmypid().'.shrink';
        $ok = match ($format) {
            'jpeg' => (imageinterlace($img, true) !== null) && @imagejpeg($img, $tmp, self::QUALITY_JPEG),
            'png' => @imagepng($img, $tmp, 9),
            'webp' => @imagewebp($img, $tmp, self::QUALITY_WEBP),
        };
        imagedestroy($img);

        clearstatcache(true, $tmp);
        $after = $ok && is_file($tmp) ? (int) filesize($tmp) : 0;
        // On ne remplace que si l'image a été réduite ou si elle est nettement plus légère.
        if ($after <= 0 || (! $needsResize && $after >= $before * 0.95) || ($needsResize && $after >= $before)) {
            @unlink($tmp);

            return null;
        }

        // copy() plutôt que rename() : le fichier peut être un téléversement temporaire de PHP.
        if (! @copy($tmp, $path)) {
            @unlink($tmp);

            return null;
        }
        @unlink($tmp);
        clearstatcache(true, $path);

        return $before - $after;
    }

    private static function load(string $path, string $format): ?GdImage
    {
        $img = match ($format) {
            'jpeg' => @imagecreatefromjpeg($path),
            'png' => @imagecreatefrompng($path),
            'webp' => @imagecreatefromwebp($path),
        };
        if (! $img instanceof GdImage) {
            return null;
        }

        if ($format === 'jpeg' && function_exists('exif_read_data')) {
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
                    imagedestroy($img);
                    $img = $rotated;
                }
            }
        }

        return $img;
    }

    /** Relève la limite mémoire le temps du traitement si l'hébergement l'autorise (≈ 5 octets par pixel × 2 images). */
    private static function ensureMemory(int $pixels): void
    {
        $needed = (int) ($pixels * 10 + 48 * 1024 * 1024);
        $current = self::toBytes((string) ini_get('memory_limit'));
        if ($current > 0 && $current < $needed) {
            @ini_set('memory_limit', (string) min($needed, 768 * 1024 * 1024));
        }
    }

    private static function toBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '' || $value === '-1') {
            return -1;
        }
        $n = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $n * 1024 ** 3,
            'm' => $n * 1024 ** 2,
            'k' => $n * 1024,
            default => $n,
        };
    }
}
