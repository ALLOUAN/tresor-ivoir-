<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\CulturalElementMedia;
use App\Models\CulturalPeople;
use App\Models\TouristCity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CulturalManagementController extends Controller
{
    // ── PEOPLES ───────────────────────────────────────────────────────────────

    public function peoples(Request $request)
    {
        $search  = $request->get('q');
        $zone    = $request->get('zone');
        $query   = CulturalPeople::query();
        if ($search) $query->where('name', 'like', "%{$search}%");
        if ($zone)   $query->where('zone_geographique', $zone);
        $peoples = $query->orderBy('sort_order')->orderBy('name')->paginate(20)->withQueryString();
        $counts  = [
            'total'    => CulturalPeople::count(),
            'active'   => CulturalPeople::where('is_active', 1)->count(),
            'featured' => CulturalPeople::where('is_featured', 1)->count(),
        ];
        return view('admin.cultural.peoples', compact('peoples', 'counts', 'search', 'zone'));
    }

    public function storePeople(Request $request)
    {
        $data = $this->validatePeople($request);
        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active', true);
        $data['symboles']    = $this->parseSymboles($request);
        unset($data['thumbnail_file'], $data['cover_image_file']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->storeImage($f, 'cultural/peoples', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->storeImage($f, 'cultural/peoples', 'thumb');
        }

        CulturalPeople::create($data);
        return back()->with('success', "Peuple « {$data['name']} » créé.");
    }

    public function updatePeople(Request $request, CulturalPeople $people)
    {
        $data = $this->validatePeople($request);
        $data['slug']        = Str::slug($data['name']);
        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_active']   = $request->boolean('is_active');
        $data['symboles']    = $this->parseSymboles($request);
        unset($data['thumbnail_file'], $data['cover_image_file']);

        if ($f = $request->file('cover_image_file')) {
            $this->deleteImage($people->cover_image);
            $data['cover_image'] = $this->storeImage($f, 'cultural/peoples', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $this->deleteImage($people->thumbnail);
            $data['thumbnail'] = $this->storeImage($f, 'cultural/peoples', 'thumb');
        }

        $people->update($data);
        return back()->with('success', "Peuple « {$people->name} » mis à jour.");
    }

    public function destroyPeople(CulturalPeople $people)
    {
        $this->deleteImage($people->cover_image);
        $this->deleteImage($people->thumbnail);
        $people->delete();
        return back()->with('success', 'Peuple supprimé.');
    }

    public function togglePeopleActive(CulturalPeople $people)
    {
        $people->update(['is_active' => !$people->is_active]);
        return back()->with('success', $people->is_active ? 'Peuple activé.' : 'Peuple désactivé.');
    }

    public function togglePeopleFeatured(CulturalPeople $people)
    {
        $people->update(['is_featured' => !$people->is_featured]);
        return back()->with('success', $people->is_featured ? 'Mis en vedette.' : 'Retiré de la vedette.');
    }

    // ── DOMAINS ───────────────────────────────────────────────────────────────

    public function domains()
    {
        $roots = CulturalDomain::with(['children' => fn ($q) => $q->withCount('elements')])
            ->withCount('elements')
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->get();
        return view('admin.cultural.domains', compact('roots'));
    }

    public function storeDomain(Request $request)
    {
        $data = $this->validateDomain($request);
        $data['slug']      = Str::slug($data['name']);
        $data['is_active'] = $request->boolean('is_active', true);
        CulturalDomain::create($data);
        return back()->with('success', "Domaine « {$data['name']} » créé.");
    }

    public function updateDomain(Request $request, CulturalDomain $domain)
    {
        $data = $this->validateDomain($request);
        $data['is_active'] = $request->boolean('is_active');
        $domain->update($data);
        return back()->with('success', 'Domaine mis à jour.');
    }

    public function destroyDomain(CulturalDomain $domain)
    {
        $domain->delete();
        return back()->with('success', 'Domaine supprimé.');
    }

    // ── ELEMENTS ──────────────────────────────────────────────────────────────

    public function elements(Request $request)
    {
        $search   = $request->get('q');
        $domainId = $request->get('domain');
        $risk     = $request->get('risk');

        $query = CulturalElement::with('domain')->latest();
        if ($search)   $query->where('name', 'like', "%{$search}%");
        if ($domainId) $query->where('domain_id', $domainId);
        if ($risk)     $query->where('niveau_risque', $risk);

        $elements = $query->paginate(20)->withQueryString();
        $domains  = CulturalDomain::orderBy('sort_order')->get();
        $counts   = [
            'total'      => CulturalElement::count(),
            'active'     => CulturalElement::where('is_active', 1)->count(),
            'en_danger'  => CulturalElement::whereIn('niveau_risque', ['en_danger', 'disparu'])->count(),
        ];

        return view('admin.cultural.elements.index', compact('elements', 'domains', 'counts', 'search', 'domainId', 'risk'));
    }

    public function createElement()
    {
        $domains = CulturalDomain::where('is_active', 1)->orderBy('sort_order')->get();
        $peoples = CulturalPeople::where('is_active', 1)->orderBy('name')->get();
        $cities  = TouristCity::where('is_active', 1)->orderBy('name')->get();
        return view('admin.cultural.elements.form', compact('domains', 'peoples', 'cities'));
    }

    public function storeElement(Request $request)
    {
        $data = $this->validateElement($request);
        $data['slug']           = Str::slug($data['name']);
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_active']      = $request->boolean('is_active', true);
        $data['people_roles']   = $this->parsePeopleRoles($request);
        $data['city_ids']       = $request->input('city_ids') ? array_map('intval', $request->input('city_ids')) : null;
        $data['practical_info'] = $this->parsePracticalInfo($request);
        $data['meilleure_periode'] = $request->input('meilleure_periode') ?: null;
        unset($data['thumbnail_file'], $data['cover_image_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->storeImage($f, 'cultural/elements', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->storeImage($f, 'cultural/elements', 'thumb');
        }

        $element = CulturalElement::create($data);
        $this->storeUploadedMedia($request, $element);

        return redirect()->route('admin.cultural.elements.index')
            ->with('success', "Élément « {$element->name} » créé.");
    }

    public function editElement(CulturalElement $element)
    {
        $element->load('media');
        $domains = CulturalDomain::where('is_active', 1)->orderBy('sort_order')->get();
        $peoples = CulturalPeople::where('is_active', 1)->orderBy('name')->get();
        $cities  = TouristCity::where('is_active', 1)->orderBy('name')->get();
        return view('admin.cultural.elements.form', compact('element', 'domains', 'peoples', 'cities'));
    }

    public function updateElement(Request $request, CulturalElement $element)
    {
        $data = $this->validateElement($request);
        $data['is_featured']    = $request->boolean('is_featured');
        $data['is_active']      = $request->boolean('is_active');
        $data['people_roles']   = $this->parsePeopleRoles($request);
        $data['city_ids']       = $request->input('city_ids') ? array_map('intval', $request->input('city_ids')) : null;
        $data['practical_info'] = $this->parsePracticalInfo($request);
        $data['meilleure_periode'] = $request->input('meilleure_periode') ?: null;
        unset($data['thumbnail_file'], $data['cover_image_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $this->deleteImage($element->cover_image);
            $data['cover_image'] = $this->storeImage($f, 'cultural/elements', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $this->deleteImage($element->thumbnail);
            $data['thumbnail'] = $this->storeImage($f, 'cultural/elements', 'thumb');
        }

        $element->update($data);
        $this->storeUploadedMedia($request, $element);

        return redirect()->route('admin.cultural.elements.edit', $element)
            ->with('success', "Élément « {$element->name} » mis à jour.");
    }

    public function destroyElement(CulturalElement $element)
    {
        $element->delete();
        return redirect()->route('admin.cultural.elements.index')->with('success', 'Élément supprimé.');
    }

    public function toggleElementActive(CulturalElement $element)
    {
        $element->update(['is_active' => !$element->is_active]);
        return back()->with('success', $element->is_active ? 'Élément activé.' : 'Élément désactivé.');
    }

    public function toggleElementFeatured(CulturalElement $element)
    {
        $element->update(['is_featured' => !$element->is_featured]);
        return back()->with('success', $element->is_featured ? 'Mis en vedette.' : 'Retiré de la vedette.');
    }

    // ── MEDIA ─────────────────────────────────────────────────────────────────

    public function destroyMedia(CulturalElementMedia $media)
    {
        $this->deleteImage($media->url);
        $media->delete();
        return back()->with('success', 'Média supprimé.');
    }

    // ── HELPERS ───────────────────────────────────────────────────────────────

    private function validatePeople(Request $request): array
    {
        return $request->validate([
            'name'                 => 'required|string|max:100',
            'zone_geographique'    => 'nullable|in:Nord,Sud,Est,Ouest,Centre',
            'famille_linguistique' => 'nullable|string|max:100',
            'langue_principale'    => 'nullable|string|max:100',
            'population_estimee'   => 'nullable|integer|min:0',
            'capitale_culturelle'  => 'nullable|string|max:100',
            'description'          => 'nullable|string',
            'histoire'             => 'nullable|string',
            'thumbnail'            => 'nullable|url|max:500',
            'thumbnail_file'       => 'nullable|file|image|max:5120',
            'cover_image'          => 'nullable|url|max:500',
            'cover_image_file'     => 'nullable|file|image|max:5120',
            'sort_order'           => 'nullable|integer|min:0',
        ]);
    }

    private function validateDomain(Request $request): array
    {
        return $request->validate([
            'parent_id'   => 'nullable|exists:cultural_domains,id',
            'name'        => 'required|string|max:100',
            'icon'        => 'nullable|string|max:80',
            'color'       => 'nullable|string|max:20',
            'description' => 'nullable|string',
            'sort_order'  => 'nullable|integer|min:0',
        ]);
    }

    private function validateElement(Request $request): array
    {
        return $request->validate([
            'domain_id'          => 'required|exists:cultural_domains,id',
            'name'               => 'required|string|max:150',
            'short_description'  => 'nullable|string|max:300',
            'description'        => 'nullable|string',
            'origine_historique' => 'nullable|string',
            'thumbnail'          => 'nullable|url|max:500',
            'thumbnail_file'     => 'nullable|file|image|max:5120',
            'cover_image'        => 'nullable|url|max:500',
            'cover_image_file'   => 'nullable|file|image|max:5120',
            'media_files'        => 'nullable|array',
            'media_files.*'      => 'nullable|file|image|max:5120',
            'website'            => 'nullable|url|max:300',
            'niveau_risque'      => 'nullable|in:stable,vulnerable,en_danger,disparu',
            'unesco_status'      => 'nullable|string|max:100',
            'city_ids'           => 'nullable|array',
            'city_ids.*'         => 'nullable|integer|exists:tourist_cities,id',
            'sort_order'         => 'nullable|integer|min:0',
        ]);
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
        $relative = ltrim(substr($url, strlen('/storage/')), '/');
        if ($relative) Storage::disk('public')->delete($relative);
    }

    private function storeUploadedMedia(Request $request, CulturalElement $element): void
    {
        if (!$request->hasFile('media_files')) return;
        $order = $element->media()->max('sort_order') ?? 0;
        foreach ($request->file('media_files') as $file) {
            if (!$file->isValid()) continue;
            $url = $this->storeImage($file, 'cultural/elements', 'media');
            $element->media()->create([
                'type'       => 'photo',
                'url'        => $url,
                'alt_text'   => $file->getClientOriginalName(),
                'sort_order' => ++$order,
            ]);
        }
    }

    private function parseSymboles(Request $request): ?array
    {
        $labels  = $request->input('symbole_label', []);
        $valeurs = $request->input('symbole_valeur', []);
        $result  = [];
        foreach ($labels as $i => $label) {
            if (!$label) continue;
            $result[] = ['label' => $label, 'valeur' => $valeurs[$i] ?? ''];
        }
        return $result ?: null;
    }

    private function parsePeopleRoles(Request $request): ?array
    {
        $ids   = $request->input('people_id', []);
        $roles = $request->input('people_role', []);
        $result = [];
        foreach ($ids as $i => $id) {
            if (!$id) continue;
            $result[] = ['people_id' => (int) $id, 'role' => $roles[$i] ?? null];
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
            $result[] = ['label' => $label, 'value' => $values[$i] ?? '', 'icon' => $icons[$i] ?? null];
        }
        return $result ?: null;
    }
}
