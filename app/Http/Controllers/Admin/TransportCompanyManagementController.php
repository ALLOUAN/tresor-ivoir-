<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\TouristCity;
use App\Models\TransportCompany;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TransportCompanyManagementController extends Controller
{
    public function __construct(private readonly ImageUploadSecurityService $imageUpload) {}

    // ── INDEX ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $search = $request->get('q');
        $cityId = $request->get('city_id');

        $query = TransportCompany::with('city')->withCount('media');

        if ($search) $query->where('name', 'like', "%{$search}%");
        if ($cityId) $query->where('city_id', $cityId);

        $companies = $query->orderBy('city_id')->orderBy('sort_order')->orderBy('name')
            ->paginate(20)->withQueryString();

        $counts = [
            'total' => TransportCompany::count(),
            'active' => TransportCompany::where('is_active', 1)->count(),
            'featured' => TransportCompany::where('is_featured', 1)->count(),
        ];

        $cities = TouristCity::orderBy('name')->get();

        return view('admin.transport-company.index', compact('companies', 'counts', 'cities', 'search', 'cityId'));
    }

    // ── CREATE / STORE ───────────────────────────────────────────────────────

    public function create()
    {
        $cities = TouristCity::orderBy('name')->get();

        return view('admin.transport-company.form', compact('cities'));
    }

    public function store(Request $request)
    {
        $data = $this->validateCompany($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active', true);
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'transport-companies', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'transport-companies', 'thumb');
        }

        $company = TransportCompany::create($data);
        $this->storeUploadedMedia($request, $company);

        return redirect()->route('admin.transport-companies.edit', $company)
            ->with('success', "Entreprise « {$company->name} » créée.");
    }

    // ── EDIT / UPDATE ────────────────────────────────────────────────────────

    public function edit(TransportCompany $transportCompany)
    {
        $transportCompany->load('media');
        $cities = TouristCity::orderBy('name')->get();

        return view('admin.transport-company.form', ['company' => $transportCompany, 'cities' => $cities]);
    }

    public function update(Request $request, TransportCompany $transportCompany)
    {
        $data = $this->validateCompany($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $transportCompany->id);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active'] = $request->boolean('is_active');
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'transport-companies', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'transport-companies', 'thumb');
        }

        $transportCompany->update($data);
        $this->storeUploadedMedia($request, $transportCompany);

        return redirect()->route('admin.transport-companies.edit', $transportCompany)
            ->with('success', "Entreprise « {$transportCompany->name} » mise à jour.");
    }

    // ── DESTROY ──────────────────────────────────────────────────────────────

    public function destroy(TransportCompany $transportCompany)
    {
        foreach ($transportCompany->media as $m) {
            $m->delete();
        }
        $transportCompany->delete();

        return redirect()->route('admin.transport-companies.index')
            ->with('success', "Entreprise « {$transportCompany->name} » supprimée.");
    }

    // ── TOGGLES ──────────────────────────────────────────────────────────────

    public function toggleActive(TransportCompany $transportCompany)
    {
        $transportCompany->update(['is_active' => ! $transportCompany->is_active]);

        return back()->with('success', $transportCompany->is_active ? 'Activé.' : 'Désactivé.');
    }

    public function toggleFeatured(TransportCompany $transportCompany)
    {
        $transportCompany->update(['is_featured' => ! $transportCompany->is_featured]);

        return back()->with('success', $transportCompany->is_featured ? 'Mis en vedette.' : 'Retiré de la vedette.');
    }

    // ── MÉDIAS ───────────────────────────────────────────────────────────────

    public function destroyMedia(Media $media)
    {
        if ($media->mediable_type !== TransportCompany::class) {
            abort(404);
        }
        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    // ── HELPERS PRIVÉS ───────────────────────────────────────────────────────

    private function storeUploadedMedia(Request $request, TransportCompany $company): void
    {
        $sort = (int) ($company->media()->max('sort_order') ?? 0);

        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $url = $this->imageUpload->store($file, 'transport-companies/media', 'photo');
            $company->media()->create([
                'mediable_type' => TransportCompany::class,
                'mediable_id' => $company->id,
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
        $base = Str::slug($name) ?: 'entreprise';
        $slug = $base;
        $i = 1;
        while (
            TransportCompany::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }

    private function validateCompany(Request $request): array
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
