<?php

namespace App\Services;

use App\Models\Accommodation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Parsing / stockage des sous-contenus dynamiques d'un hébergement
 * (types de chambres, liens de réservation, équipements, médias).
 * Partagé entre la gestion admin et l'auto-gestion prestataire.
 */
class AccommodationContentService
{
    /** Parse les lignes dynamiques de types de chambres. */
    public function parseRoomTypes(Request $request): ?array
    {
        $names = $request->input('room_name', []);
        $adults = $request->input('room_max_adults', []);
        $children = $request->input('room_max_children', []);
        $areas = $request->input('room_area_m2', []);
        $pricesXof = $request->input('room_price_xof', []);
        $pricesEur = $request->input('room_price_eur', []);
        $amenities = $request->input('room_amenities', []);
        $photos = $request->input('room_photos', []);
        $descriptions = $request->input('room_description', []);
        $beds = $request->input('room_beds', []);
        $conditions = $request->input('room_conditions', []);

        $rooms = [];
        foreach ($names as $i => $name) {
            if (empty(trim($name))) {
                continue;
            }
            $rooms[] = [
                'name' => trim($name),
                'max_adults' => (int) ($adults[$i] ?? 2),
                'max_children' => (int) ($children[$i] ?? 0),
                'area_m2' => ! empty($areas[$i]) ? (float) $areas[$i] : null,
                'price_xof' => ! empty($pricesXof[$i]) ? (int) $pricesXof[$i] : null,
                'price_eur' => ! empty($pricesEur[$i]) ? (float) $pricesEur[$i] : null,
                'amenities' => ! empty($amenities[$i])
                    ? array_values(array_filter(array_map('trim', explode(',', $amenities[$i]))))
                    : [],
                'photos' => ! empty($photos[$i])
                    ? array_values(array_filter(array_map('trim', explode(',', $photos[$i]))))
                    : [],
                'description' => ! empty($descriptions[$i]) ? trim($descriptions[$i]) : null,
                'beds' => ! empty($beds[$i]) ? trim($beds[$i]) : null,
                'conditions' => ! empty($conditions[$i]) ? trim($conditions[$i]) : null,
            ];
        }

        return $rooms ?: null;
    }

    /** Parse les lignes dynamiques de liens de réservation. */
    public function parseBookingLinks(Request $request): ?array
    {
        $providers = $request->input('bl_provider', []);
        $urls = $request->input('bl_url', []);
        $logos = $request->input('bl_logo', []);
        $officials = $request->input('bl_official', []);
        $badges = $request->input('bl_badge', []);

        $links = [];
        foreach ($providers as $i => $provider) {
            if (empty(trim($provider))) {
                continue;
            }
            $links[] = [
                'provider_name' => trim($provider),
                'logo_url' => ! empty($logos[$i]) ? trim($logos[$i]) : null,
                'affiliate_url' => ! empty($urls[$i]) ? trim($urls[$i]) : '#',
                'is_official' => in_array((string) $i, (array) $officials),
                'badge_text' => ! empty($badges[$i]) ? trim($badges[$i]) : null,
                'sort_order' => $i,
            ];
        }

        return $links ?: null;
    }

    /** Parse des paires icon/label (commodités). */
    public function parseKeyLabel(Request $request, string $iconField, string $labelField): ?array
    {
        $icons = $request->input($iconField, []);
        $labels = $request->input($labelField, []);

        $result = [];
        foreach ($labels as $i => $label) {
            if (empty(trim($label))) {
                continue;
            }
            $result[] = [
                'icon' => trim($icons[$i] ?? 'fas fa-check'),
                'label' => trim($label),
            ];
        }

        return $result ?: null;
    }

    /**
     * Parse le formulaire "une seule chambre" (page dédiée prestataire) — contrairement à
     * parseRoomTypes() qui lit des tableaux parallèles pour N lignes soumises ensemble.
     * Attribue un id stable (uuid) permettant de retrouver cette chambre précise plus tard
     * dans le JSON room_types, sans dépendre de sa position dans le tableau.
     */
    public function parseSingleRoomType(Request $request, ?string $existingId = null): array
    {
        $photos = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('room_photos', '')))));
        $amenities = array_values(array_filter(array_map('trim', explode(',', (string) $request->input('amenities', '')))));

        return [
            'id' => $existingId ?? (string) Str::uuid(),
            'name' => trim((string) $request->input('name', '')),
            'max_adults' => (int) $request->input('max_adults', 2),
            'max_children' => (int) $request->input('max_children', 0),
            'area_m2' => $request->filled('area_m2') ? (float) $request->input('area_m2') : null,
            'price_xof' => $request->filled('price_xof') ? (int) $request->input('price_xof') : null,
            'price_eur' => $request->filled('price_eur') ? (float) $request->input('price_eur') : null,
            'amenities' => $amenities,
            'photos' => $photos,
            'description' => $request->filled('description') ? trim((string) $request->input('description')) : null,
            'beds' => $request->filled('beds') ? trim((string) $request->input('beds')) : null,
            'conditions' => $request->filled('conditions') ? trim((string) $request->input('conditions')) : null,
        ];
    }

    /** Upload les photos de la chambre courante (formulaire "une seule chambre") et les ajoute au tableau. */
    public function uploadSingleRoomPhotos(Request $request, array $room): array
    {
        foreach ((array) $request->file('room_photo_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $room['photos'][] = $this->storeImage($file, 'accommodations/rooms', 'room');
        }

        return $room;
    }

    /** Upload les photos de chambre et les ajoute dans le tableau room_types. */
    public function uploadRoomPhotos(Request $request, ?array $rooms): ?array
    {
        if (! $rooms) {
            return $rooms;
        }
        foreach ($rooms as $i => &$room) {
            $files = $request->file("room_photo_files.{$i}") ?? [];
            foreach ((array) $files as $file) {
                if (! $file || ! $file->isValid()) {
                    continue;
                }
                $room['photos'][] = $this->storeImage($file, 'accommodations/rooms', 'room');
            }
        }

        return $rooms;
    }

    public function storeUploadedMedia(Request $request, Accommodation $accommodation): void
    {
        $files = $request->file('media_files', []);
        foreach ($files as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $url = $this->storeImage($file, 'accommodations/media', 'photo');
            $accommodation->media()->create(['type' => 'photo', 'url' => $url]);
        }
    }

    /**
     * Enregistre des liens vidéo (YouTube, Vimeo, ou lien direct .mp4) plutôt
     * qu'un fichier téléversé — même table `accommodation_media`, juste un
     * type différent (`video`), pas de nouvelle logique de stockage à écrire.
     */
    public function storeVideoLinks(Request $request, Accommodation $accommodation): void
    {
        $links = array_filter((array) $request->input('video_links', []), fn ($url) => trim((string) $url) !== '');
        foreach ($links as $url) {
            $accommodation->media()->create(['type' => 'video', 'url' => trim($url)]);
        }
    }

    public function storeImage(\Illuminate\Http\UploadedFile $file, string $folder, string $prefix): string
    {
        $ext = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = $folder.'/'.$prefix.'_'.Str::random(32).'.'.$ext;
        Storage::disk('public')->put($filename, fopen($file->getPathname(), 'r'));

        return '/storage/'.$filename;
    }

    public function deleteImage(?string $url): void
    {
        if (! $url || ! str_starts_with($url, '/storage/')) {
            return;
        }
        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $url), '/'));
    }

    /** Génère un slug unique pour un hébergement, en excluant éventuellement son propre id. */
    public function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'hebergement';
        $slug = $base;
        $i = 1;
        while (
            Accommodation::withTrashed()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
