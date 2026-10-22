<?php

namespace App\Http\Middleware;

use App\Support\ImageCompressor;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Symfony\Component\HttpFoundation\Response;

/**
 * Allège les photos téléversées AVANT que les contrôleurs ne les enregistrent : côté le plus long
 * ramené à 2 000 px et recompression, dans le même format. Un seul point d'entrée pour tous les
 * formulaires (administration, prestataires, éditeurs) : moins de disque, de bande passante et de
 * calcul côté serveur ensuite (variantes /img/…). Les fichiers non-images ne sont pas touchés.
 */
class ShrinkUploadedImages
{
    private const FORMATS = ['image/jpeg' => 'jpeg', 'image/png' => 'png', 'image/webp' => 'webp'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isMethodSafe() || ! $request->files->count()) {
            return $next($request);
        }

        foreach (Arr::flatten($request->allFiles()) as $file) {
            if (! $file instanceof UploadedFile || ! $file->isValid()) {
                continue;
            }
            $format = self::FORMATS[(string) $file->getMimeType()] ?? null;
            if ($format === null) {
                continue;
            }
            try {
                ImageCompressor::shrink($file->getPathname(), $format);
            } catch (\Throwable $e) {
                // Jamais bloquant : en cas d'erreur, l'image d'origine est enregistrée telle quelle.
                report($e);
            }
        }

        return $next($request);
    }
}
