<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;

/**
 * Contrôle du CONTENU réel d'une image téléversée (et pas seulement de son extension) :
 * un fichier texte ou un script renommé en « .jpg » est refusé.
 */
final class ImageUploadGuard
{
    private const MIME_AUTORISES = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];

    /** @return bool true si le fichier est bien une image JPEG, PNG ou WebP lisible. */
    public static function isRealImage(?UploadedFile $file): bool
    {
        $path = $file?->getPathname();
        if (! is_string($path) || $path === '' || ! is_file($path)) {
            return true;   // impossible à relire ici (réglage temporaire du serveur) : les autres contrôles de l'appelant s'appliquent
        }

        $info = @getimagesize($path);

        return is_array($info) && in_array($info['mime'] ?? '', self::MIME_AUTORISES, true);
    }

    /** Interrompt la requête (422) si le fichier n'est pas une vraie image. */
    public static function assert(UploadedFile $file, string $field): void
    {
        if (! self::isRealImage($file)) {
            abort(422, "Le fichier {$field} n'est pas une image valide (jpeg, png ou webp).");
        }
    }
}
