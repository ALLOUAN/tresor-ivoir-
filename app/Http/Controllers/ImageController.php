<?php

namespace App\Http\Controllers;

use App\Services\ImageResizer;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ImageController extends Controller
{
    public function show(ImageResizer $resizer, int $width, string $path): BinaryFileResponse
    {
        $file = $resizer->variant($path, $width);
        abort_if($file === null, 404);

        $mime = str_ends_with($file, '.webp') ? 'image/webp' : (mime_content_type($file) ?: 'application/octet-stream');

        // L'URL porte la date de modification du fichier (?v=) : cache navigateur d'un an.
        return response()->file($file, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }

    /** Image de partage sur les réseaux sociaux (JPEG 1200 × 630). */
    public function share(ImageResizer $resizer, string $path): BinaryFileResponse
    {
        // L'URL se termine par « .jpg » (certains réseaux déduisent le format de l'extension) : on retire ce suffixe.
        $file = $resizer->shareImage(preg_replace('/\.jpg$/', '', $path));
        abort_if($file === null, 404);

        return response()->file($file, [
            'Content-Type' => 'image/jpeg',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
