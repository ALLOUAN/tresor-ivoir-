<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Validation + stockage sécurisé des photos uploadées par les prestataires (œuvres,
 * circuits, plats, activités, offres transport). Contrairement à un simple contrôle
 * d'extension déclarée par le client (trivialement falsifiable), le contenu réel du
 * fichier est vérifié via finfo avant tout stockage — même rigueur que
 * ConversationAttachmentSecurityService, appliquée ici aux photos de fiches produit.
 */
class ImageUploadSecurityService
{
    /** @var array<string, string> Extension autorisée => type MIME réel attendu. */
    private const ALLOWED = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    public function assertSafeImage(UploadedFile $file): void
    {
        // getPathname() plutôt que getRealPath() : sous Windows, getRealPath() appelle
        // realpath() en interne, qui renvoie false quand les noms courts 8.3 sont
        // désactivés sur le volume (constaté ici via un vrai upload multipart, alors
        // qu'un UploadedFile::fake() de test ne reproduit pas ce cas) — même précaution
        // que déjà documentée et appliquée dans store() ci-dessous.
        $pathname = $file->getPathname();
        if (! is_string($pathname) || $pathname === '' || ! is_file($pathname)) {
            abort(422, 'Fichier invalide.');
        }

        $extension = strtolower((string) $file->getClientOriginalExtension());
        if (! array_key_exists($extension, self::ALLOWED)) {
            abort(422, 'Format d\'image non autorisé (jpg, jpeg, png, webp uniquement).');
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = $finfo ? (string) finfo_file($finfo, $pathname) : '';
        if ($finfo) {
            finfo_close($finfo);
        }

        if ($mime !== self::ALLOWED[$extension]) {
            abort(422, 'Le contenu du fichier ne correspond pas à une image '.$extension.' valide.');
        }
    }

    /**
     * Valide puis stocke via fopen() plutôt que UploadedFile::store() : sous Windows,
     * store() appelle realpath() en interne, qui peut échouer (ValueError) quand les
     * noms courts 8.3 sont désactivés sur le volume.
     */
    public function store(UploadedFile $file, string $folder, string $prefix): string
    {
        $this->assertSafeImage($file);

        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $relativePath = $folder.'/'.$prefix.'_'.Str::random(40).'.'.$extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        return '/storage/'.$relativePath;
    }
}
