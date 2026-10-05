<?php

namespace App\Services;

use App\Models\GalleryLike;
use App\Models\SiteMediaItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Str;

/**
 * Likes de la galerie publique : fonctionnent sans compte (la galerie elle-même
 * est consultable sans authentification), un visiteur anonyme est identifié via
 * un cookie longue durée plutôt qu'obligé de se connecter juste pour aimer une
 * image. Un seul point d'entrée pour toute la logique liker_key/bascule/comptage,
 * réutilisé par le contrôleur d'action et par l'affichage (état déjà aimé ou non).
 */
class GalleryLikeService
{
    private const COOKIE_NAME = 'gallery_liker';

    private const COOKIE_MINUTES = 60 * 24 * 365;

    public function resolveLikerKey(Request $request): string
    {
        if (Auth::check()) {
            return 'user:'.Auth::id();
        }

        $token = $request->cookie(self::COOKIE_NAME);
        if (! $token) {
            $token = (string) Str::uuid();
            Cookie::queue(self::COOKIE_NAME, $token, self::COOKIE_MINUTES);
        }

        return 'guest:'.$token;
    }

    public function toggle(SiteMediaItem $media, string $likerKey): bool
    {
        $existing = GalleryLike::where('media_id', $media->id)->where('liker_key', $likerKey)->first();

        if ($existing) {
            $existing->delete();
            SiteMediaItem::whereKey($media->id)->decrement('likes_count');

            return false;
        }

        try {
            GalleryLike::create(['media_id' => $media->id, 'liker_key' => $likerKey]);
            SiteMediaItem::whereKey($media->id)->increment('likes_count');
        } catch (\Illuminate\Database\QueryException $e) {
            // Double-clic concurrent sur le même like : déjà créé par l'autre
            // requête, rien à faire de plus — on considère l'état comme "aimé".
            if (! $this->isDuplicateKeyViolation($e)) {
                throw $e;
            }
        }

        return true;
    }

    /**
     * @param  iterable<int>  $mediaIds
     * @return array<int, true>  ensemble (clé = media_id) des visuels déjà aimés par ce liker_key
     */
    public function likedMediaIds(string $likerKey, iterable $mediaIds): array
    {
        $ids = is_array($mediaIds) ? $mediaIds : iterator_to_array($mediaIds);
        if (empty($ids)) {
            return [];
        }

        return GalleryLike::where('liker_key', $likerKey)
            ->whereIn('media_id', $ids)
            ->pluck('media_id')
            ->flip()
            ->map(fn () => true)
            ->all();
    }

    private function isDuplicateKeyViolation(\Illuminate\Database\QueryException $e): bool
    {
        return (int) $e->getCode() === 23000;
    }
}
