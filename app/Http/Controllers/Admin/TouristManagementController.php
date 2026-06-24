<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Models\TouristSite;
use App\Models\TouristSiteMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TouristManagementController extends Controller
{
    // ── CITIES ────────────────────────────────────────────────────────────────

    public function cities(Request $request)
    {
        $search = $request->get('q');
        $query  = TouristCity::withCount('sites');
        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }
        $cities = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        $counts = [
            'total'    => TouristCity::count(),
            'active'   => TouristCity::where('is_active', 1)->count(),
            'featured' => TouristCity::where('is_featured', 1)->count(),
        ];
        return view('admin.tourist.cities', compact('cities', 'counts', 'search'));
    }

    public function storeCity(Request $request)
    {
        $data = $this->validateCity($request);
        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active', true);

        unset($data['thumbnail_file'], $data['cover_image_file']);

        $coverFile = $request->file('cover_image_file');
        if ($coverFile && $coverFile->isValid()) {
            $data['cover_image'] = $this->storeCityImage($coverFile, 'cover');
        }
        $thumbFile = $request->file('thumbnail_file');
        if ($thumbFile && $thumbFile->isValid()) {
            $data['thumbnail'] = $this->storeCityImage($thumbFile, 'thumb');
        }

        TouristCity::create($data);
        return back()->with('success', "Ville « {$data['name']} » créée.");
    }

    public function updateCity(Request $request, TouristCity $city)
    {
        $data = $this->validateCity($request);
        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');

        unset($data['thumbnail_file'], $data['cover_image_file']);

        $coverFile = $request->file('cover_image_file');
        if ($coverFile && $coverFile->isValid()) {
            $this->deleteCityImage($city->cover_image);
            $data['cover_image'] = $this->storeCityImage($coverFile, 'cover');
        }
        $thumbFile = $request->file('thumbnail_file');
        if ($thumbFile && $thumbFile->isValid()) {
            $this->deleteCityImage($city->thumbnail);
            $data['thumbnail'] = $this->storeCityImage($thumbFile, 'thumb');
        }

        $city->update($data);
        return back()->with('success', "Ville « {$city->name} » mise à jour.");
    }

    public function destroyCity(TouristCity $city)
    {
        $city->delete();
        return back()->with('success', 'Ville supprimée.');
    }

    public function toggleCityActive(TouristCity $city)
    {
        $city->update(['is_active' => !$city->is_active]);
        return back()->with('success', $city->is_active ? 'Ville activée.' : 'Ville désactivée.');
    }

    public function toggleCityFeatured(TouristCity $city)
    {
        $city->update(['is_featured' => !$city->is_featured]);
        return back()->with('success', $city->is_featured ? 'Mise en vedette.' : 'Retirée de la vedette.');
    }

    // ── CATEGORIES ────────────────────────────────────────────────────────────

    public function categories()
    {
        $categories = TouristCategory::withCount('sites')->orderBy('sort_order')->get();
        return view('admin.tourist.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateCategory($request);
        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        TouristCategory::create($data);
        return back()->with('success', "Catégorie « {$data['name']} » créée.");
    }

    public function updateCategory(Request $request, TouristCategory $category)
    {
        $data = $this->validateCategory($request);
        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);
        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(TouristCategory $category)
    {
        $category->delete();
        return back()->with('success', 'Catégorie supprimée.');
    }

    // ── SITES ─────────────────────────────────────────────────────────────────

    public function sites(Request $request)
    {
        $search = $request->get('q');
        $cityId = $request->get('city');
        $catId  = $request->get('category');
        $active = $request->get('active');

        $query = TouristSite::with(['city', 'category'])->latest();
        if ($search) $query->where('name', 'like', "%{$search}%");
        if ($cityId) $query->where('city_id', $cityId);
        if ($catId)  $query->where('category_id', $catId);
        if ($active !== null) $query->where('is_active', $active);

        $sites      = $query->paginate(20)->withQueryString();
        $cities     = TouristCity::orderBy('name')->get();
        $categories = TouristCategory::orderBy('sort_order')->get();
        $counts     = [
            'total'    => TouristSite::count(),
            'active'   => TouristSite::where('is_active', 1)->count(),
            'featured' => TouristSite::where('is_featured', 1)->count(),
        ];

        return view('admin.tourist.sites.index', compact('sites', 'cities', 'categories', 'counts', 'search', 'cityId', 'catId', 'active'));
    }

    public function createSite()
    {
        $cities     = TouristCity::where('is_active', 1)->orderBy('name')->get();
        $categories = TouristCategory::where('is_active', 1)->orderBy('sort_order')->get();
        return view('admin.tourist.sites.form', compact('cities', 'categories'));
    }

    public function storeSite(Request $request)
    {
        $data = $this->validateSite($request);
        $data['slug']           = Str::slug($data['name']);
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_active']      = $request->boolean('is_active', true);
        $data['schedules']      = $this->parseSchedules($request);
        $data['practical_info'] = $this->parsePracticalInfo($request);

        // Retirer les champs fichiers — non persistés directement en base
        unset($data['thumbnail_file'], $data['media_files']);

        $thumbFile = $request->file('thumbnail_file');
        if ($thumbFile && $thumbFile->isValid()) {
            $data['thumbnail'] = $this->storeSiteImage($thumbFile, 'thumb');
        }

        $site = TouristSite::create($data);
        $this->storeInlineMedia($request, $site);
        $this->storeUploadedMediaFiles($request, $site);

        return redirect()->route('admin.tourist.sites.index')
            ->with('success', "Site « {$site->name} » créé avec succès.");
    }

    public function editSite(TouristSite $site)
    {
        $site->load('media');
        $cities     = TouristCity::where('is_active', 1)->orderBy('name')->get();
        $categories = TouristCategory::where('is_active', 1)->orderBy('sort_order')->get();
        return view('admin.tourist.sites.form', compact('site', 'cities', 'categories'));
    }

    public function updateSite(Request $request, TouristSite $site)
    {
        $data = $this->validateSite($request);
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_active']      = $request->boolean('is_active');
        $data['schedules']      = $this->parseSchedules($request);
        $data['practical_info'] = $this->parsePracticalInfo($request);

        // Retirer les champs fichiers — non persistés directement en base
        unset($data['thumbnail_file'], $data['media_files']);

        $thumbFile = $request->file('thumbnail_file');
        if ($thumbFile && $thumbFile->isValid()) {
            $this->deleteSiteImage($site->thumbnail);
            $data['thumbnail'] = $this->storeSiteImage($thumbFile, 'thumb');
        }

        $site->update($data);
        $this->storeInlineMedia($request, $site);
        $this->storeUploadedMediaFiles($request, $site);

        return redirect()
            ->route('admin.tourist.sites.edit', $site)
            ->with('success', "Site « {$site->name} » mis à jour.");
    }

    public function destroySite(TouristSite $site)
    {
        $site->delete();
        return redirect()->route('admin.tourist.sites.index')->with('success', 'Site supprimé.');
    }

    public function toggleSiteActive(TouristSite $site)
    {
        $site->update(['is_active' => !$site->is_active]);
        return back()->with('success', $site->is_active ? 'Site activé.' : 'Site désactivé.');
    }

    public function toggleSiteFeatured(TouristSite $site)
    {
        $site->update(['is_featured' => !$site->is_featured]);
        return back()->with('success', $site->is_featured ? 'Site mis en vedette.' : 'Site retiré de la vedette.');
    }

    // ── MEDIA ─────────────────────────────────────────────────────────────────

    public function storeMedia(Request $request, TouristSite $site)
    {
        $request->validate([
            'type'          => 'required|in:photo,video',
            'url'           => 'required|string|max:500',
            'thumbnail_url' => 'nullable|string|max:500',
            'caption'       => 'nullable|string|max:200',
            'alt_text'      => 'nullable|string|max:200',
        ]);
        $site->media()->create($request->only(['type', 'url', 'thumbnail_url', 'caption', 'alt_text']));
        return back()->with('success', 'Média ajouté.');
    }

    public function destroyMedia(TouristSiteMedia $media)
    {
        $media->delete();
        return back()->with('success', 'Média supprimé.');
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function validateCity(Request $request): array
    {
        return $request->validate([
            'name'                  => 'required|string|max:100',
            'district'              => 'nullable|string|max:100',
            'region_administrative' => 'nullable|string|max:100',
            'description'           => 'nullable|string',
            'thumbnail'             => 'nullable|url|max:500',
            'thumbnail_file'        => 'nullable|file|image|max:5120',
            'cover_image'           => 'nullable|url|max:500',
            'cover_image_file'      => 'nullable|file|image|max:5120',
            'website'               => 'nullable|url|max:300',
            'latitude'              => 'nullable|numeric|between:-90,90',
            'longitude'             => 'nullable|numeric|between:-180,180',
            'sort_order'            => 'nullable|integer|min:0',
        ]);
    }

    private function storeSiteImage(\Illuminate\Http\UploadedFile $file, string $type): string
    {
        $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = 'tourist/sites/' . $type . '_' . Str::random(32) . '.' . $ext;
        Storage::disk('public')->put($filename, fopen($file->getPathname(), 'r'));
        return '/storage/' . $filename;
    }

    private function deleteSiteImage(?string $url): void
    {
        if (!$url || !str_starts_with($url, '/storage/')) return;
        $relative = ltrim(substr($url, strlen('/storage/')), '/');
        if ($relative) Storage::disk('public')->delete($relative);
    }

    private function storeUploadedMediaFiles(Request $request, TouristSite $site): void
    {
        if (!$request->hasFile('media_files')) return;
        $order = $site->media()->max('sort_order') ?? 0;
        foreach ($request->file('media_files') as $file) {
            if (!$file->isValid()) continue;
            $url = $this->storeSiteImage($file, 'media');
            $site->media()->create([
                'type'       => 'photo',
                'url'        => $url,
                'alt_text'   => $file->getClientOriginalName(),
                'sort_order' => ++$order,
            ]);
        }
    }

    private function storeCityImage(\Illuminate\Http\UploadedFile $file, string $type): string
    {
        $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
        $filename = 'tourist/cities/' . $type . '_' . Str::random(32) . '.' . $ext;

        Storage::disk('public')->put($filename, fopen($file->getPathname(), 'r'));

        return '/storage/' . $filename;
    }

    private function deleteCityImage(?string $url): void
    {
        if (!$url || !str_starts_with($url, '/storage/')) {
            return;
        }

        $relative = ltrim(substr($url, strlen('/storage/')), '/');
        if ($relative) {
            Storage::disk('public')->delete($relative);
        }
    }

    private function validateCategory(Request $request): array
    {
        return $request->validate([
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:80',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
    }

    private function validateSite(Request $request): array
    {
        return $request->validate([
            'city_id'            => 'required|exists:tourist_cities,id',
            'category_id'        => 'required|exists:tourist_categories,id',
            'name'               => 'required|string|max:150',
            'short_description'  => 'nullable|string|max:300',
            'description'        => 'nullable|string',
            'thumbnail'          => 'nullable|url|max:500',
            'thumbnail_file'     => 'nullable|file|image|max:5120',
            'media_files'        => 'nullable|array',
            'media_files.*'      => 'nullable|file|image|max:5120',
            'entrance_fee'       => 'nullable|string|max:100',
            'website'            => 'nullable|url|max:300',
            'phone'              => 'nullable|string|max:30',
            'email'              => 'nullable|email|max:150',
            'latitude'           => 'nullable|numeric|between:-90,90',
            'longitude'          => 'nullable|numeric|between:-180,180',
            'departement'        => 'nullable|string|max:100',
            'sous_prefecture'    => 'nullable|string|max:100',
            'localite'           => 'nullable|string|max:150',
            'altitude_m'         => 'nullable|integer',
            'superficie_ha'      => 'nullable|numeric|min:0',
            'distance_centre_km' => 'nullable|numeric|min:0',
            'point_repere'       => 'nullable|string|max:250',
            'acces_description'  => 'nullable|string',
            'map_embed_url'      => 'nullable|string|max:2000',
            'sort_order'         => 'nullable|integer|min:0',
        ]);
    }

    private function parseSchedules(Request $request): ?array
    {
        $days   = $request->input('schedule_day', []);
        $opens  = $request->input('schedule_opens', []);
        $closes = $request->input('schedule_closes', []);
        $closed = $request->input('schedule_closed', []);

        $result = [];
        foreach ($days as $i => $day) {
            if (!$day) continue;
            $result[] = [
                'day'    => $day,
                'opens'  => $opens[$i] ?? null,
                'closes' => $closes[$i] ?? null,
                'closed' => isset($closed[$i]),
            ];
        }
        return $result ?: null;
    }

    private function parsePracticalInfo(Request $request): ?array
    {
        $labels = $request->input('info_label', []);
        $values = $request->input('info_value', []);
        $icons  = $request->input('info_icon', []);

        $result = [];
        foreach ($labels as $i => $label) {
            if (!$label) continue;
            $result[] = [
                'label' => $label,
                'value' => $values[$i] ?? '',
                'icon'  => $icons[$i] ?? null,
            ];
        }
        return $result ?: null;
    }

    private function storeInlineMedia(Request $request, TouristSite $site): void
    {
        $urls          = $request->input('media_url', []);
        $types         = $request->input('media_type', []);
        $thumbnailUrls = $request->input('media_thumbnail_url', []);
        $captions      = $request->input('media_caption', []);
        $altTexts      = $request->input('media_alt_text', []);

        foreach ($urls as $i => $url) {
            if (!$url) continue;
            $site->media()->create([
                'type'          => $types[$i] ?? 'photo',
                'url'           => $url,
                'thumbnail_url' => $thumbnailUrls[$i] ?: null,
                'caption'       => $captions[$i] ?: null,
                'alt_text'      => $altTexts[$i] ?: null,
            ]);
        }
    }
}
