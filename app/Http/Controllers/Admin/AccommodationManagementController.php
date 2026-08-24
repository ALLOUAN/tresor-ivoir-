<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationMedia;
use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Services\AccommodationContentService;
use Illuminate\Http\Request;

class AccommodationManagementController extends Controller
{
    public function __construct(private readonly AccommodationContentService $content) {}

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
        $data['slug']        = $this->content->uniqueSlug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active', true);
        $data['category_ids']  = $request->input('category_ids')
            ? array_map('intval', $request->input('category_ids')) : null;
        $data['amenities']     = $this->content->parseKeyLabel($request, 'amenity_icons', 'amenity_labels');
        $data['room_types']    = $this->content->parseRoomTypes($request);
        $data['booking_links'] = $this->content->parseBookingLinks($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->content->storeImage($f, 'accommodations', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->content->storeImage($f, 'accommodations', 'thumb');
        }

        $data['room_types'] = $this->content->uploadRoomPhotos($request, $data['room_types'] ?? []);

        $accommodation = Accommodation::create($data);
        $this->content->storeUploadedMedia($request, $accommodation);

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
        $data['slug']        = $this->content->uniqueSlug($data['name'], $accommodation->id);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');
        $data['category_ids']  = $request->input('category_ids')
            ? array_map('intval', $request->input('category_ids')) : null;
        $data['amenities']     = $this->content->parseKeyLabel($request, 'amenity_icons', 'amenity_labels');
        $data['room_types']    = $this->content->parseRoomTypes($request);
        $data['booking_links'] = $this->content->parseBookingLinks($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $this->content->deleteImage($accommodation->cover_image);
            $data['cover_image'] = $this->content->storeImage($f, 'accommodations', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $this->content->deleteImage($accommodation->thumbnail);
            $data['thumbnail'] = $this->content->storeImage($f, 'accommodations', 'thumb');
        }

        $data['room_types'] = $this->content->uploadRoomPhotos($request, $data['room_types'] ?? []);

        $accommodation->update($data);
        $this->content->storeUploadedMedia($request, $accommodation);

        return redirect()->route('admin.accommodations.edit', $accommodation)
            ->with('success', "Hébergement « {$accommodation->name} » mis à jour.");
    }

    // ── DESTROY ──────────────────────────────────────────────────────────────

    public function destroy(Accommodation $accommodation)
    {
        $this->content->deleteImage($accommodation->cover_image);
        $this->content->deleteImage($accommodation->thumbnail);
        foreach ($accommodation->media as $m) {
            $this->content->deleteImage($m->url);
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
        $this->content->deleteImage($media->url);
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
}
