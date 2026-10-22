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
    public function parseRoomTypes(Request $request, ?array $existing = null): ?array
    {
        // Champs gérés ailleurs (périodes fermées saisies par le prestataire) : conservés tels quels.
        $existingById = collect($existing ?? [])->filter(fn ($r) => ! empty($r['id']))->keyBy('id');

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
        $units = $request->input('room_units', []);
        $ids = $request->input('room_id', []);

        $rooms = [];
        foreach ($names as $i => $name) {
            if (empty(trim($name))) {
                continue;
            }
            $rooms[] = [
                'id' => ! empty($ids[$i]) ? (string) $ids[$i] : (string) Str::uuid(),
                'name' => trim($name),
                'units' => ! empty($units[$i]) ? max(1, (int) $units[$i]) : null,
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
                'closed_ranges' => ! empty($ids[$i]) ? ($existingById->get($ids[$i])['closed_ranges'] ?? []) : [],
            ] + (! empty($ids[$i]) ? array_intersect_key((array) $existingById->get($ids[$i]), array_flip(['weekend_price_xof', 'rate_periods', 'long_stay_min_nights', 'long_stay_percent', 'last_minute_days', 'last_minute_percent', 'ical_url', 'external_blocks', 'ical_synced_at'])) : []);
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
            'units' => $request->filled('units') ? max(1, (int) $request->input('units')) : null,
            'closed_ranges' => $this->parseClosedRanges($request),
            'weekend_price_xof' => $request->filled('weekend_price_xof') ? max(0, (int) $request->input('weekend_price_xof')) : null,
            'rate_periods' => $this->parseRatePeriods($request),
            'long_stay_min_nights' => $request->filled('long_stay_min_nights') ? max(1, (int) $request->input('long_stay_min_nights')) : null,
            'long_stay_percent' => $request->filled('long_stay_percent') ? min(90, max(0, (float) $request->input('long_stay_percent'))) : null,
            'last_minute_days' => $request->filled('last_minute_days') ? max(1, (int) $request->input('last_minute_days')) : null,
            'last_minute_percent' => $request->filled('last_minute_percent') ? min(90, max(0, (float) $request->input('last_minute_percent'))) : null,
            'ical_url' => $request->filled('ical_url') ? trim((string) $request->input('ical_url')) : null,
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

    /**
     * Périodes fermées saisies par paires closed_from[] / closed_to[] (dates incluses).
     * Les lignes vides, inversées ou déjà passées sont ignorées.
     *
     * @return array<int, array{from: string, to: string}>
     */
    private function parseClosedRanges(Request $request): array
    {
        $froms = (array) $request->input('closed_from', []);
        $tos = (array) $request->input('closed_to', []);
        $today = now()->toDateString();
        $ranges = [];
        foreach ($froms as $i => $from) {
            $to = $tos[$i] ?? null;
            if (! $from || ! $to || $to < $from || $to < $today) {
                continue;
            }
            $ranges[] = ['from' => (string) $from, 'to' => (string) $to];
        }
        usort($ranges, fn ($a, $b) => strcmp($a['from'], $b['from']));

        return $ranges;
    }

    /**
     * Périodes tarifaires (haute saison, fêtes…) : rate_from[] / rate_to[] / rate_price[] / rate_label[].
     *
     * @return array<int, array{from: string, to: string, price_xof: int, label: ?string}>
     */
    private function parseRatePeriods(Request $request): array
    {
        $out = [];
        foreach ((array) $request->input('rate_from', []) as $i => $from) {
            $to = $request->input('rate_to.'.$i);
            $price = (int) $request->input('rate_price.'.$i);
            if (! $from || ! $to || $to < $from || $price <= 0) continue;
            $out[] = ['from' => (string) $from, 'to' => (string) $to, 'price_xof' => $price, 'label' => trim((string) $request->input('rate_label.'.$i)) ?: null];
        }
        usort($out, fn ($a, $b) => strcmp($a['from'], $b['from']));

        return $out;
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
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true) || ! \App\Support\ImageUploadGuard::isRealImage($file)) {
            abort(422, 'Image invalide : formats acceptés jpeg, png ou webp.');
        }
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
