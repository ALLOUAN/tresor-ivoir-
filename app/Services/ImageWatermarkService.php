<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class ImageWatermarkService
{
    private const TEXT = "\xA9 Tresors Ivoire";

    /**
     * Apply watermark in-place (keeps original format).
     */
    public function apply(string $relativeStoragePath): void
    {
        $abs = Storage::disk('public')->path($relativeStoragePath);

        if (! is_file($abs)) {
            return;
        }

        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return;
        }

        $img = $this->load($abs, $ext);
        if ($img === null) {
            return;
        }

        $this->stamp($img);
        $this->save($img, $abs, $ext);
    }

    /**
     * Apply watermark then convert to WebP for better compression.
     * Deletes the original file if conversion succeeds.
     * Returns the new relative storage path (extension changed to .webp).
     */
    public function optimize(string $relativeStoragePath): string
    {
        $abs = Storage::disk('public')->path($relativeStoragePath);

        if (! is_file($abs)) {
            return $relativeStoragePath;
        }

        $ext = strtolower(pathinfo($abs, PATHINFO_EXTENSION));
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return $relativeStoragePath;
        }

        $img = $this->load($abs, $ext);
        if ($img === null) {
            return $relativeStoragePath;
        }

        $this->stamp($img);

        if ($ext === 'webp') {
            $this->save($img, $abs, 'webp');
                return $relativeStoragePath;
        }

        $webpRelative = preg_replace('/\.[^.]+$/', '.webp', $relativeStoragePath);
        $webpAbs      = Storage::disk('public')->path($webpRelative);

        imagewebp($img, $webpAbs, 82);

        if (is_file($webpAbs) && filesize($webpAbs) > 0) {
            @unlink($abs);
            return $webpRelative;
        }

        // WebP write failed: fall back to watermark-only on original
        $img2 = $this->load($abs, $ext);
        if ($img2) {
            $this->stamp($img2);
            $this->save($img2, $abs, $ext);
            imagedestroy($img2);
        }

        return $relativeStoragePath;
    }

    private function load(string $path, string $ext): ?\GdImage
    {
        $res = match ($ext) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($path),
            'png'         => @imagecreatefrompng($path),
            'webp'        => @imagecreatefromwebp($path),
            default       => false,
        };

        return ($res instanceof \GdImage) ? $res : null;
    }

    private function stamp(\GdImage $img): void
    {
        $w   = imagesx($img);
        $h   = imagesy($img);
        $font = 5;
        $cw  = imagefontwidth($font);
        $ch  = imagefontheight($font);
        $pad = max(8, (int) ($w * 0.018));

        $x = $w - ($cw * strlen(self::TEXT)) - $pad;
        $y = $h - $ch - $pad;

        imagealphablending($img, true);

        $shadow = imagecolorallocatealpha($img, 0,   0,   0,   50);
        $white  = imagecolorallocatealpha($img, 255, 255, 255, 50);

        imagestring($img, $font, $x + 1, $y + 1, self::TEXT, $shadow);
        imagestring($img, $font, $x,     $y,     self::TEXT, $white);
    }

    private function save(\GdImage $img, string $path, string $ext): void
    {
        if ($ext === 'png') {
            imagesavealpha($img, true);
            imagepng($img, $path, 6);
        } elseif ($ext === 'webp') {
            imagewebp($img, $path, 90);
        } else {
            imagejpeg($img, $path, 90);
        }
    }
}
