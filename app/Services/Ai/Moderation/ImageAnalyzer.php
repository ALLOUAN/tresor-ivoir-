<?php

namespace App\Services\Ai\Moderation;

use GdImage;

/**
 * Contrôle technique d'une photo : résolution, netteté (variance du laplacien), exposition,
 * contraste, format, poids et empreinte perceptuelle (pour repérer les doublons visuels).
 * Ne juge pas le contenu : voir ImageComplianceChecker.
 */
class ImageAnalyzer
{
    public const LABELS = [
        'low_resolution' => 'Résolution trop faible',
        'blurry' => 'Image floue',
        'too_dark' => 'Image trop sombre',
        'too_bright' => 'Image surexposée',
        'low_contrast' => 'Image très peu contrastée',
        'odd_format' => 'Format d\'image inhabituel',
        'heavy_file' => 'Fichier très lourd',
        'visual_duplicate' => 'Doublon visuel d\'une autre photo',
        'unreadable' => 'Fichier illisible ou corrompu',
        'non_compliant' => 'Contenu non conforme aux règles du site',
    ];

    /** @return array{flags:array,score:int,metrics:array,dhash:?string} */
    public function analyze(string $path): array
    {
        $cfg = config('ai.moderation.image');
        $flags = [];
        $add = function (string $code, int $weight, string $detail = '') use (&$flags) {
            if ($weight > ($flags[$code]['weight'] ?? 0)) {
                $flags[$code] = ['weight' => $weight, 'detail' => $detail];
            }
        };

        $size = @getimagesize($path);
        $img = $size ? $this->load($path, $size[2]) : null;
        if (! $size || ! $img) {
            $add('unreadable', 80, basename($path));

            return ['flags' => $flags, 'score' => TextAnalyzer::combine($flags), 'metrics' => [], 'dhash' => null];
        }
        [$w, $h] = $size;

        if ($w < 320 || $h < 200) {
            $add('low_resolution', 50, $w.'×'.$h.' px');
        } elseif ($w < $cfg['min_width'] || $h < $cfg['min_height']) {
            $add('low_resolution', 30, $w.'×'.$h.' px (minimum conseillé '.$cfg['min_width'].'×'.$cfg['min_height'].')');
        }

        $ratio = $w / max(1, $h);
        if ($ratio > 3.5 || $ratio < 0.28) {
            $add('odd_format', 10, round($ratio, 2).':1');
        }
        $bytes = (int) @filesize($path);
        if ($bytes > 8 * 1024 * 1024) {
            $add('heavy_file', 10, round($bytes / 1048576, 1).' Mo');
        }

        // Version réduite en niveaux de gris pour les mesures
        $small = $this->gray($img, 384);
        $metrics = $this->metrics($small);
        imagedestroy($small);

        // Netteté relative : variance du laplacien rapportée au contraste (une photo sombre ou très
        // claire n'est pas pour autant floue).
        $rel = $metrics['sharpness'] / max(1.0, $metrics['std'] ** 2);
        $metrics['relative_sharpness'] = $rel;
        if ($rel < $cfg['very_blur_relative']) {
            $add('blurry', 45, 'netteté relative '.round($rel, 3));
        } elseif ($rel < $cfg['blur_relative']) {
            $add('blurry', 30, 'netteté relative '.round($rel, 3));
        }
        if ($metrics['mean'] < $cfg['dark_mean']) {
            $add('too_dark', 25, 'luminosité moyenne '.round($metrics['mean']));
        } elseif ($metrics['mean'] > $cfg['bright_mean']) {
            $add('too_bright', 20, 'luminosité moyenne '.round($metrics['mean']));
        }
        if ($metrics['std'] < $cfg['low_contrast_std'] && ! isset($flags['blurry'])) {
            $add('low_contrast', 15, 'contraste '.round($metrics['std']));
        }

        $hash = $this->dhash($img);
        imagedestroy($img);
        $metrics += ['width' => $w, 'height' => $h, 'bytes' => $bytes];

        return ['flags' => $flags, 'score' => TextAnalyzer::combine($flags), 'metrics' => $metrics, 'dhash' => $hash];
    }

    /** Distance de Hamming entre deux empreintes hexadécimales de 64 bits. */
    public static function distance(string $a, string $b): int
    {
        $d = 0;
        for ($i = 0, $n = min(strlen($a), strlen($b)); $i < $n; $i++) {
            $d += substr_count(decbin(hexdec($a[$i]) ^ hexdec($b[$i])), '1');
        }

        return $d;
    }

    // ── Mesures ─────────────────────────────────────────────────────────────

    private function load(string $path, int $type): ?GdImage
    {
        $img = match ($type) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG => @imagecreatefrompng($path),
            IMAGETYPE_WEBP => @imagecreatefromwebp($path),
            IMAGETYPE_GIF => @imagecreatefromgif($path),
            default => false,
        };

        return $img instanceof GdImage ? $img : null;
    }

    /** Copie en niveaux de gris, largeur maximale $max. */
    private function gray(GdImage $img, int $max): GdImage
    {
        $w = imagesx($img);
        $h = imagesy($img);
        $scale = min(1.0, $max / max($w, $h));
        $nw = max(8, (int) round($w * $scale));
        $nh = max(8, (int) round($h * $scale));
        $dst = imagecreatetruecolor($nw, $nh);
        imagefill($dst, 0, 0, imagecolorallocate($dst, 255, 255, 255));   // fond blanc sous la transparence
        imagecopyresampled($dst, $img, 0, 0, 0, 0, $nw, $nh, $w, $h);
        imagefilter($dst, IMG_FILTER_GRAYSCALE);

        return $dst;
    }

    /** Luminosité moyenne, contraste (écart type) et netteté (variance du laplacien). */
    private function metrics(GdImage $g): array
    {
        $w = imagesx($g);
        $h = imagesy($g);
        $px = [];
        $sum = 0;
        for ($y = 0; $y < $h; $y++) {
            $row = [];
            for ($x = 0; $x < $w; $x++) {
                $v = imagecolorat($g, $x, $y) & 0xFF;
                $row[] = $v;
                $sum += $v;
            }
            $px[] = $row;
        }
        $n = $w * $h;
        $mean = $sum / $n;
        $var = 0.0;
        foreach ($px as $row) {
            foreach ($row as $v) {
                $var += ($v - $mean) ** 2;
            }
        }
        $lapSum = 0.0;
        $lapSq = 0.0;
        $cnt = 0;
        for ($y = 1; $y < $h - 1; $y++) {
            for ($x = 1; $x < $w - 1; $x++) {
                $l = $px[$y - 1][$x] + $px[$y + 1][$x] + $px[$y][$x - 1] + $px[$y][$x + 1] - 4 * $px[$y][$x];
                $lapSum += $l;
                $lapSq += $l * $l;
                $cnt++;
            }
        }
        $lapMean = $cnt ? $lapSum / $cnt : 0;
        $lapVar = $cnt ? $lapSq / $cnt - $lapMean ** 2 : 0;

        return ['mean' => $mean, 'std' => sqrt($var / $n), 'sharpness' => $lapVar];
    }

    /** dHash 64 bits : comparaison des pixels voisins d'une miniature 9×8. */
    private function dhash(GdImage $img): string
    {
        $t = imagecreatetruecolor(9, 8);
        imagefill($t, 0, 0, imagecolorallocate($t, 255, 255, 255));
        imagecopyresampled($t, $img, 0, 0, 0, 0, 9, 8, imagesx($img), imagesy($img));
        imagefilter($t, IMG_FILTER_GRAYSCALE);
        $bits = '';
        for ($y = 0; $y < 8; $y++) {
            for ($x = 0; $x < 8; $x++) {
                $bits .= ((imagecolorat($t, $x, $y) & 0xFF) > (imagecolorat($t, $x + 1, $y) & 0xFF)) ? '1' : '0';
            }
        }
        imagedestroy($t);
        $hex = '';
        foreach (str_split($bits, 4) as $nibble) {
            $hex .= dechex(bindec($nibble));
        }

        return $hex;
    }
}
