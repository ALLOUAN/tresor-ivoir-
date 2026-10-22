<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bundle;
use App\Models\Provider;
use App\Models\TouristCity;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

/** Composition des offres groupées par l'administration. */
class BundleManagementController extends Controller
{
    public function index(): View
    {
        $bundles = Bundle::with(['provider', 'city'])->orderByDesc('is_featured')->orderBy('sort_order')->latest()->paginate(20);

        return view('admin.bundles.index', compact('bundles'));
    }

    public function create(): View
    {
        return view('admin.bundles.form', ['bundle' => new Bundle(['duration_days' => 2, 'min_people' => 1, 'is_active' => true])] + $this->options());
    }

    public function edit(Bundle $bundle): View
    {
        return view('admin.bundles.form', compact('bundle') + $this->options());
    }

    public function store(Request $request): RedirectResponse
    {
        $bundle = Bundle::create($this->data($request));

        return redirect()->route('admin.bundles.index')->with('success', 'Offre « '.$bundle->title.' » créée.');
    }

    public function update(Request $request, Bundle $bundle): RedirectResponse
    {
        $bundle->update($this->data($request, $bundle));

        return redirect()->route('admin.bundles.index')->with('success', 'Offre mise à jour.');
    }

    public function toggle(Bundle $bundle): RedirectResponse
    {
        $bundle->update(['is_active' => ! $bundle->is_active]);

        return back()->with('success', $bundle->is_active ? 'Offre publiée.' : 'Offre masquée.');
    }

    public function destroy(Bundle $bundle): RedirectResponse
    {
        $bundle->delete();

        return back()->with('success', 'Offre supprimée.');
    }

    private function options(): array
    {
        $catalog = [];
        foreach (Bundle::INCLUDABLE as $type => [$class, $label]) {
            $catalog[$type] = ['label' => $label, 'items' => $class::query()->where('is_active', true)->orderBy('name')->get(['id', 'name'])];
        }

        return [
            'providers' => Provider::where('status', 'active')->orderBy('name')->get(['id', 'name']),
            'cities' => TouristCity::orderBy('name')->get(['id', 'name']),
            'catalog' => $catalog,
        ];
    }

    private function data(Request $request, ?Bundle $bundle = null): array
    {
        $validated = $request->validate([
            'provider_id' => ['required', 'exists:providers,id'],
            'city_id' => ['nullable', 'exists:tourist_cities,id'],
            'title' => ['required', 'string', 'max:180'],
            'title_en' => ['nullable', 'string', 'max:180'],
            'summary' => ['nullable', 'string', 'max:300'],
            'summary_en' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:10000'],
            'description_en' => ['nullable', 'string', 'max:10000'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:60'],
            'price_xof' => ['required', 'integer', 'min:5'],
            'min_people' => ['required', 'integer', 'min:1', 'max:200'],
            'max_people' => ['nullable', 'integer', 'gte:min_people', 'max:500'],
            'includes' => ['nullable', 'array'],
            'includes.*' => ['nullable', 'array'],
            'includes.*.*' => ['integer'],
            'highlights' => ['nullable', 'string', 'max:3000'],
            'cover_image_file' => ['nullable', 'file', 'image', 'max:5120'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ], [], ['provider_id' => 'prestataire porteur', 'price_xof' => 'prix par personne', 'title' => 'titre']);

        $includes = [];
        foreach ((array) ($validated['includes'] ?? []) as $type => $ids) {
            if (! isset(Bundle::INCLUDABLE[$type])) continue;
            foreach (array_filter((array) $ids) as $id) {
                $includes[] = ['type' => $type, 'id' => (int) $id];
            }
        }

        $data = collect($validated)->except(['includes', 'highlights', 'cover_image_file'])->all();
        $data['includes'] = $includes;
        $data['highlights'] = array_values(array_filter(array_map('trim', preg_split('/\R/', (string) ($validated['highlights'] ?? '')))));
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort_order'] = (int) ($validated['sort_order'] ?? 0);
        $data['slug'] = $this->slug($validated['title'], $bundle?->id);

        if ($request->hasFile('cover_image_file')) {
            $data['cover_image'] = $request->file('cover_image_file')->store('bundles', 'public');
        }

        return $data;
    }

    private function slug(string $title, ?int $ignoreId): string
    {
        $base = Str::slug($title) ?: 'offre';
        $slug = $base;
        for ($i = 2; Bundle::where('slug', $slug)->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))->exists(); $i++) {
            $slug = $base.'-'.$i;
        }

        return $slug;
    }
}
