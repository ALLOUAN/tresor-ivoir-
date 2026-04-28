<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use RuntimeException;

class ConversationAttachmentSecurityService
{
    /** @var array<string, string> */
    private array $mimeByExt = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xls' => 'application/vnd.ms-excel',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'txt' => 'text/plain',
    ];

    public function assertSafeUpload(UploadedFile $file): void
    {
        $realPath = $file->getRealPath();
        if ($realPath === false || $realPath === '') {
            throw new RuntimeException('Fichier invalide.');
        }

        $ext = strtolower((string) $file->getClientOriginalExtension());
        if (! array_key_exists($ext, $this->mimeByExt)) {
            throw new RuntimeException('Extension non autorisée.');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $finfoMime = $finfo ? (string) finfo_file($finfo, $realPath) : '';
        if ($finfo) {
            finfo_close($finfo);
        }
        if ($finfoMime === '') {
            throw new RuntimeException('Impossible de lire la signature MIME.');
        }

        $allowed = $this->allowedMimesForExtension($ext);
        if (! in_array($finfoMime, $allowed, true)) {
            throw new RuntimeException('Signature MIME invalide pour ce type de fichier.');
        }
    }

    public function runAntivirusHook(string $absolutePath): string
    {
        $template = (string) env('CONVERSATION_AV_SCAN_COMMAND', '');
        if ($template === '') {
            return 'skipped';
        }

        $cmd = str_replace('%path%', escapeshellarg($absolutePath), $template);
        $output = [];
        $exitCode = 1;
        @exec($cmd, $output, $exitCode);

        return $exitCode === 0 ? 'clean' : 'infected';
    }

    public function createImageThumbnailIfPossible(string $absolutePath, string $extension): ?string
    {
        $extension = strtolower($extension);
        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }
        if (! function_exists('imagecreatetruecolor')) {
            return null;
        }

        $src = match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($absolutePath),
            'png' => @imagecreatefrompng($absolutePath),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolutePath) : false,
            default => false,
        };
        if (! $src) {
            return null;
        }

        $srcW = (int) imagesx($src);
        $srcH = (int) imagesy($src);
        if ($srcW <= 0 || $srcH <= 0) {
            imagedestroy($src);
            return null;
        }

        $targetW = min(640, $srcW);
        $targetH = (int) max(1, round(($srcH / $srcW) * $targetW));

        $thumb = imagecreatetruecolor($targetW, $targetH);
        if (! $thumb) {
            imagedestroy($src);
            return null;
        }

        if ($extension === 'png' || $extension === 'webp') {
            imagealphablending($thumb, false);
            imagesavealpha($thumb, true);
            $transparent = imagecolorallocatealpha($thumb, 0, 0, 0, 127);
            imagefilledrectangle($thumb, 0, 0, $targetW, $targetH, $transparent);
        }

        imagecopyresampled($thumb, $src, 0, 0, 0, 0, $targetW, $targetH, $srcW, $srcH);
        imagedestroy($src);

        $thumbPath = dirname($absolutePath).DIRECTORY_SEPARATOR.'thumb_'.Str::random(16).'.jpg';
        $ok = imagejpeg($thumb, $thumbPath, 80);
        imagedestroy($thumb);

        return $ok ? $thumbPath : null;
    }

    /**
     * @return array<int, string>
     */
    private function allowedMimesForExtension(string $ext): array
    {
        return match ($ext) {
            'jpg', 'jpeg' => ['image/jpeg'],
            'png' => ['image/png'],
            'webp' => ['image/webp'],
            'pdf' => ['application/pdf'],
            'txt' => ['text/plain', 'application/octet-stream'],
            'doc' => ['application/msword', 'application/octet-stream'],
            'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/zip', 'application/octet-stream'],
            'xls' => ['application/vnd.ms-excel', 'application/octet-stream'],
            'xlsx' => ['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/zip', 'application/octet-stream'],
            default => [],
        };
    }
}

