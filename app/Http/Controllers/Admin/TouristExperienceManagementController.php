<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TouristExperienceManagementController extends Controller
{
    public function __construct(private readonly ImageUploadSecurityService $imageUpload) {}

    // ── INDEX ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->get('q');
        $cityId = $request->get('city_id');

        $query = TouristExperience::with('city')->withCount('media');

        if ($search) $query->where('name', 'like', "%{$search}%");
        if ($cityId) $query->where('city_id', $cityId);

        $experiences = $query->orderBy('city_id')->orderBy('sort_order')->orderBy('name')
            ->paginate(20)->withQueryString();

        $counts = [
            'total' => TouristExperience::count(),
            'active' => TouristExperience::where('is_active', 1)->count(),
            'featured' => TouristExperience::where('is_featured', 1)->count(),
        ];

        $cities = TouristCity::orderBy('name')->get();

        return view('admin.tourist-experience.index', compact('experiences', 'counts', 'cities', 'search', 'cityId'));
    }

    // ── CREATE / STORE ───────────────────────────────────────────────────────

    public function create()
    {
        $cities = TouristCity::orderBy('name')->get();

        return view('admin.tourist-experience.form', compact('cities'));
    }

    public function store(Request $request)
    {
        $data = $this->validateExperience($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'tourist-experiences', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'tourist-experiences', 'thumb');
        }

        $experience = TouristExperience::create($data);
        $this->storeUploadedMedia($request, $experience);

        return redirect()->route('admin.tourist-experiences.edit', $experience)
            ->with('success', "Site « {$experience->name} » créé.");
    }

    // ── EDIT / UPDATE ────────────────────────────────────────────────────────

    public function edit(TouristExperience $touristExperience)
    {
        $touristExperience->load('media');
        $cities = TouristCity::orderBy('name')->get();

        return view('admin.tourist-experience.form', ['experience' => $touristExperience, 'cities' => $cities]);
    }

    public function update(Request $request, TouristExperience $touristExperience)
    {
        $data = $this->validateExperience($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $touristExperience->id);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'tourist-experiences', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'tourist-experiences', 'thumb');
        }

        $touristExperience->update($data);
        $this->storeUploadedMedia($request, $touristExperience);

        return redirect()->route('admin.tourist-experiences.edit', $touristExperience)
            ->with('success', "Site « {$touristExperience->name} » mis à jour.");
    }

    // ── DESTROY ──────────────────────────────────────────────────────────────

    public function destroy(TouristExperience $touristExperience)
    {
        foreach ($touristExperience->media as $m) {
            $m->delete();
        }
        $touristExperience->delete();

        return redirect()->route('admin.tourist-experiences.index')
            ->with('success', "Site « {$touristExperience->name} » supprimé.");
    }

    // ── TOGGLES ──────────────────────────────────────────────────────────────

    public function toggleActive(TouristExperience $touristExperience)
    {
        $touristExperience->update(['is_active' => ! $touristExperience->is_active]);

        return back()->with('success', $touristExperience->is_active ? 'Activé.' : 'Désactivé.');
    }

    public function toggleFeatured(TouristExperience $touristExperience)
    {
        $touristExperience->update(['is_featured' => ! $touristExperience->is_featured]);

        return back()->with('success', $touristExperience->is_featured ? 'Mis en vedette.' : 'Retiré de la vedette.');
    }

    // ── MÉDIAS ───────────────────────────────────────────────────────────────

    public function destroyMedia(Media $media)
    {
        if ($media->mediable_type !== TouristExperience::class) {
            abort(404);
        }
        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    // ── HELPERS PRIVÉS ───────────────────────────────────────────────────────

    private function storeUploadedMedia(Request $request, TouristExperience $experience): void
    {
        $sort = (int) ($experience->media()->max('sort_order') ?? 0);

        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $url = $this->imageUpload->store($file, 'tourist-experiences/media', 'photo');
            $experience->media()->create([
                'mediable_type' => TouristExperience::class,
                'mediable_id' => $experience->id,
                'collection' => 'gallery',
                'type' => 'image',
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'file_path' => Str::after($url, '/storage/'),
                'url' => $url,
                'size_bytes' => $file->getSize(),
                'sort_order' => $sort,
                'uploaded_by' => auth()->id(),
            ]);
        }
    }

    private function parseAmenities(Request $request): ?array
    {
        $icons = $request->input('amenity_icons', []);
        $labels = $request->input('amenity_labels', []);
        $result = [];
        foreach ($labels as $i => $label) {
            if (empty(trim($label))) {
                continue;
            }
            $result[] = ['icon' => trim($icons[$i] ?? 'fas fa-check'), 'label' => trim($label)];
        }

        return $result ?: null;
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'site';
        $slug = $base;
        $i = 1;
        while (
            TouristExperience::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function validateExperience(Request $request): array
    {
        return $request->validate([
            'city_id' => 'required|exists:tourist_cities,id',
            'name' => 'required|string|max:150',
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'adresse' => 'nullable|string|max:255',
            'quartier' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|url|max:300',
            'cover_image_file' => 'nullable|file|image|max:5120',
            'thumbnail_file' => 'nullable|file|image|max:5120',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }
}
