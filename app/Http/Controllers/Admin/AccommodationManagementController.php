<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationMedia;
use App\Models\TouristCategory;
use App\Models\TouristCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AccommodationManagementController extends Controller
{
    // ── INDEX ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->get('q');
        $cityId = $request->get('city_id');
        $type   = $request->get('type');

        $query = Accommodation::with('city')->withCount('media');

        if ($search)  $query->where('name', 'like', "%{$search}%");
        if ($cityId)  $query->where('city_id', $cityId);
        if ($type)    $query->where('type', $type);

        $accommodations = $query->orderBy('city_id')->orderBy('sort_order')->orderBy('name')
                                ->paginate(20)->withQueryString();

        $counts = [
            'total'    => Accommodation::count(),
            'active'   => Accommodation::where('is_active', 1)->count(),
            'featured' => Accommodation::where('is_featured', 1)->count(),
        ];

        $cities = TouristCity::orderBy('name')->get();

        return view('admin.accommodation.index', compact(
            'accommodations', 'counts', 'cities', 'search', 'cityId', 'type'
        ));
    }

    // ── CREATE / STORE ───────────────────────────────────────────────────────

    public function create()
    {
        $cities     = TouristCity::orderBy('name')->get();
        $categories = TouristCategory::orderBy('sort_order')->get();
        return view('admin.accommodation.form', compact('cities', 'categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAccommodation($request);
        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active', true);
        $data['category_ids']  = $request->input('category_ids')
            ? array_map('intval', $request->input('category_ids')) : null;
        $data['amenities']     = $this->parseKeyLabel($request, 'amenity_icons', 'amenity_labels');
        $data['room_types']    = $this->parseRoomTypes($request);
        $data['booking_links'] = $this->parseBookingLinks($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->storeImage($f, 'accommodations', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->storeImage($f, 'accommodations', 'thumb');
        }

        $data['room_types'] = $this->uploadRoomPhotos($request, $data['room_types'] ?? []);

        $accommodation = Accommodation::create($data);
        $this->storeUploadedMedia($request, $accommodation);

        return redirect()->route('admin.accommodations.edit', $accommodation)
            ->with('success', "Hébergement « {$accommodation->name} » créé.");
    }

    // ── EDIT / UPDATE ────────────────────────────────────────────────────────

    public function edit(Accommodation $accommodation)
    {
        $accommodation->load('media');
        $cities     = TouristCity::orderBy('name')->get();
        $categories = TouristCategory::orderBy('sort_order')->get();
        return view('admin.accommodation.form', compact('accommodation', 'cities', 'categories'));
    }

    public function update(Request $request, Accommodation $accommodation)
    {
        $data = $this->validateAccommodation($request);
        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');
        $data['category_ids']  = $request->input('category_ids')
            ? array_map('intval', $request->input('category_ids')) : null;
        $data['amenities']     = $this->parseKeyLabel($request, 'amenity_icons', 'amenity_labels');
        $data['room_types']    = $this->parseRoomTypes($request);
        $data['booking_links'] = $this->parseBookingLinks($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $this->deleteImage($accommodation->cover_image);
            $data['cover_image'] = $this->storeImage($f, 'accommodations', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $this->deleteImage($accommodation->thumbnail);
            $data['thumbnail'] = $this->storeImage($f, 'accommodations', 'thumb');
        }

        $data['room_types'] = $this->uploadRoomPhotos($request, $data['room_types'] ?? []);

        $accommodation->update($data);
        $this->storeUploadedMedia($request, $accommodation);

        return redirect()->route('admin.accommodations.edit', $accommodation)
            ->with('success', "Hébergement « {$accommodation->name} » mis à jour.");
    }

    // ── DESTROY ──────────────────────────────────────────────────────────────

    public function destroy(Accommodation $accommodation)
    {
        $this->deleteImage($accommodation->cover_image);
        $this->deleteImage($accommodation->thumbnail);
        foreach ($accommodation->media as $m) {
            $this->deleteImage($m->url);
            $m->delete();
        }
        $accommodation->delete();
        return redirect()->route('admin.accommodations.index')
            ->with('success', "Hébergement « {$accommodation->name} » supprimé.");
    }

    // ── TOGGLES ──────────────────────────────────────────────────────────────

    public function toggleActive(Accommodation $accommodation)
    {
        $accommodation->update(['is_active' => !$accommodation->is_active]);
        return back()->with('success', $accommodation->is_active ? 'Activé.' : 'Désactivé.');
    }

    public function toggleFeatured(Accommodation $accommodation)
    {
        $accommodation->update(['is_featured' => !$accommodation->is_featured]);
        return back()->with('success', $accommodation->is_featured ? 'Mis en vedette.' : 'Retiré de la vedette.');
    }

    // ── MÉDIAS ───────────────────────────────────────────────────────────────

    public function destroyMedia(AccommodationMedia $media)
    {
        $this->deleteImage($media->url);
        $media->delete();
        return back()->with('success', 'Média supprimé.');
    }

    // ── HELPERS PRIVÉS ───────────────────────────────────────────────────────

    private function validateAccommodation(Request $request): array
    {
        return $request->validate([
            'city_id'           => 'required|exists:tourist_cities,id',
            'name'              => 'required|string|max:150',
            'type'              => 'required|in:hotel,resort,guesthouse,hostel,auberge,villa,eco_lodge',
            'stars'             => 'nullable|integer|min:0|max:5',
            'short_description' => 'nullable|string|max:300',
            'description'       => 'nullable|string',
            'adresse'           => 'nullable|string|max:255',
            'quartier'          => 'nullable|string|max:100',
            'latitude'          => 'nullable|numeric|between:-90,90',
            'longitude'         => 'nullable|numeric|between:-180,180',
            'phone'             => 'nullable|string|max:30',
            'email'             => 'nullable|email|max:150',
            'website'           => 'nullable|url|max:300',
            'cover_image'       => 'nullable|string|max:500',
            'cover_image_file'  => 'nullable|file|image|max:5120',
            'thumbnail'         => 'nullable|string|max:500',
            'thumbnail_file'    => 'nullable|file|image|max:5120',
            'check_in_time'     => 'nullable|string|max:5',
            'check_out_time'    => 'nullable|string|max:5',
            'category_ids'      => 'nullable|array',
            'category_ids.*'    => 'integer|exists:tourist_categories,id',
            'media_files'           => 'nullable|array',
            'media_files.*'         => 'file|image|max:5120',
            'room_photo_files'      => 'nullable|array',
            'room_photo_files.*'    => 'nullable|array',
            'room_photo_files.*.*'  => 'nullable|file|image|max:5120',
            'sort_order'            => 'nullable|integer|min:0',
        ]);
    }

    /** Parse les lignes dynamiques de types de chambres. */
    private function parseRoomTypes(Request $request): ?array
    {
        $names     = $request->input('room_name', []);
        $adults    = $request->input('room_max_adults', []);
        $children  = $request->input('room_max_children', []);
        $areas     = $request->input('room_area_m2', []);
        $pricesXof = $request->input('room_price_xof', []);
        $pricesEur = $request->input('room_price_eur', []);
        $amenities = $request->input('room_amenities', []);
        $photos    = $request->input('room_photos', []);

        $rooms = [];
        foreach ($names as $i => $name) {
            if (empty(trim($name))) continue;
            $rooms[] = [
                'name'         => trim($name),
                'max_adults'   => (int) ($adults[$i]    ?? 2),
                'max_children' => (int) ($children[$i]  ?? 0),
                'area_m2'      => !empty($areas[$i])     ? (float) $areas[$i]     : null,
                'price_xof'    => !empty($pricesXof[$i]) ? (int)   $pricesXof[$i] : null,
                'price_eur'    => !empty($pricesEur[$i]) ? (float) $pricesEur[$i] : null,
                'amenities'    => !empty($amenities[$i])
                    ? array_values(array_filter(array_map('trim', explode(',', $amenities[$i]))))
                    : [],
                'photos'       => !empty($photos[$i])
                    ? array_values(array_filter(array_map('trim', explode(',', $photos[$i]))))
                    : [],
            ];
        }
        return $rooms ?: null;
    }

    /** Parse les lignes dynamiques de liens de réservation. */
    private function parseBookingLinks(Request $request): ?array
    {
        $providers = $request->input('bl_provider', []);
        $urls      = $request->input('bl_url', []);
        $logos     = $request->input('bl_logo', []);
        $officials = $request->input('bl_official', []);
        $badges    = $request->input('bl_badge', []);

        $links = [];
        foreach ($providers as $i => $provider) {
            if (empty(trim($provider))) continue;
            $links[] = [
                'provider_name' => trim($provider),
                'logo_url'      => !empty($logos[$i])     ? trim($logos[$i])    : null,
                'affiliate_url' => !empty($urls[$i])      ? trim($urls[$i])     : '#',
                'is_official'   => in_array((string)$i, (array)$officials),
                'badge_text'    => !empty($badges[$i])    ? trim($badges[$i])   : null,
                'sort_order'    => $i,
            ];
        }
        return $links ?: null;
    }

    /** Parse des paires icon/label (commodités). */
    private function parseKeyLabel(Request $request, string $iconField, string $labelField): ?array
    {
        $icons  = $request->input($iconField, []);
        $labels = $request->input($labelField, []);

        $result = [];
        foreach ($labels as $i => $label) {
            if (empty(trim($label))) continue;
            $result[] = [
                'icon'  => trim($icons[$i]  ?? 'fas fa-check'),
                'label' => trim($label),
            ];
        }
        return $result ?: null;
    }

    /** Upload les photos de chambre et les ajoute dans le tableau room_types. */
    private function uploadRoomPhotos(Request $request, ?array $rooms): ?array
    {
        if (!$rooms) return $rooms;
        foreach ($rooms as $i => &$room) {
            $files = $request->file("room_photo_files.{$i}") ?? [];
            foreach ((array) $files as $file) {
                if (!$file || !$file->isValid()) continue;
                $url = $this->storeImage($file, 'accommodations/rooms', 'room');
                $room['photos'][] = $url;
            }
        }
        return $rooms;
    }

    private function storeUploadedMedia(Request $request, Accommodation $accommodation): void
    {
        $files = $request->file('media_files', []);
        foreach ($files as $file) {
            if (!$file || !$file->isValid()) continue;
            $url = $this->storeImage($file, 'accommodations/media', 'photo');
            $accommodation->media()->create(['type' => 'photo', 'url' => $url]);
        }
    }

    private function storeImage(\Illuminate\Http\UploadedFile $file, string $folder, string $prefix): string
    {
        $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = $folder . '/' . $prefix . '_' . Str::random(32) . '.' . $ext;
        Storage::disk('public')->put($filename, fopen($file->getPathname(), 'r'));
        return '/storage/' . $filename;
    }

    private function deleteImage(?string $url): void
    {
        if (!$url || !str_starts_with($url, '/storage/')) return;
        Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', $url), '/'));
    }
}
