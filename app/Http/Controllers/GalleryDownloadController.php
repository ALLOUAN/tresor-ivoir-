<?php

namespace App\Http\Controllers;

use App\Models\SiteMediaItem;
use Illuminate\Http\JsonResponse;

class GalleryDownloadController extends Controller
{
    /**
     * Comptage simple des téléchargements (pas de déduplication par visiteur —
     * contrairement aux likes, chaque téléchargement réel doit être compté,
     * y compris répété par la même personne). Le fichier est déjà téléchargé
     * nativement par le navigateur via l'attribut `download` du lien ; cet appel
     * ne fait qu'incrémenter le compteur en parallèle, sans jamais bloquer ni
     * remplacer le téléchargement réel.
     */
    public function track(string $uuid): JsonResponse
    {
        $media = SiteMediaItem::query()
            ->forPublicHomeGallery()
            ->where('uuid', $uuid)
            ->firstOrFail();

        SiteMediaItem::whereKey($media->id)->increment('downloads_count');

        return response()->json([
            'downloads_count' => $media->fresh()->downloads_count,
        ]);
    }
}
