<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\TourCategory;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TourManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q');
        $category = $request->get('category');
        $availability = $request->get('availability');

        $query = TourPackage::query()
            ->withTrashed()
            ->with(['provider', 'category'])
            ->latest();

        if ($availability === 'available') {
            $query->where('is_available', true);
        } elseif ($availability === 'unavailable') {
            $query->where('is_available', false);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($search) {
            $query->where('name', 'like', "%{$search}%");
        }

        $tours = $query->paginate(20)->withQueryString();
        $categories = TourCategory::query()->orderBy('sort_order')->get();

        $counts = [
            'all' => TourPackage::count(),
            'available' => TourPackage::where('is_available', true)->count(),
            'unavailable' => TourPackage::where('is_available', false)->count(),
        ];

        return view('admin.tours.index', compact('tours', 'categories', 'counts', 'search', 'category', 'availability'));
    }

    public function create(): View
    {
        return view('admin.tours.form', [
            'tour' => new TourPackage(),
            'categories' => TourCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'agencies' => $this->travelAgencyProviders(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->storeUploadedFile($file, 'tours/admin', 'circuit');
        }

        TourPackage::create([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('admin.tours.index')->with('success', 'Circuit créé.');
    }

    public function edit(TourPackage $tour): View
    {
        return view('admin.tours.form', [
            'tour' => $tour,
            'categories' => TourCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'agencies' => $this->travelAgencyProviders(),
        ]);
    }

    public function update(Request $request, TourPackage $tour): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $tour->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->storeUploadedFile($file, 'tours/admin', 'circuit');
        }

        $tour->update([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('admin.tours.index')->with('success', 'Circuit mis à jour.');
    }

    public function toggle(TourPackage $tour): RedirectResponse
    {
        $tour->update(['is_available' => ! $tour->is_available]);

        return back()->with('success', 'Disponibilité mise à jour.');
    }

    public function destroy(TourPackage $tour): RedirectResponse
    {
        $tour->delete();

        return back()->with('success', 'Circuit supprimé.');
    }

    /** Prestataires éligibles (famille de catégories Agences de Voyages & Tours). */
    private function travelAgencyProviders()
    {
        return Provider::query()
            ->whereHas('category', function ($query) {
                $query->where('slug', 'agences-voyages')
                    ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->where('slug', 'agences-voyages'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:tour_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_xof' => ['required', 'integer', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1', 'max:365'],
            'max_participants' => ['nullable', 'integer', 'min:1', 'max:999'],
        ]);
    }

    /**
     * Retire de la liste les images cochées pour suppression (case "remove_images[]") et
     * supprime aussi les fichiers physiques correspondants du disque.
     *
     * @param  array<int, string>  $currentImages
     * @return array<int, string>
     */
    private function removeMarkedImages(Request $request, array $currentImages): array
    {
        $toRemove = array_filter((array) $request->input('remove_images', []));
        if (empty($toRemove)) {
            return $currentImages;
        }

        foreach ($toRemove as $url) {
            if (in_array($url, $currentImages, true)) {
                Storage::disk('public')->delete(ltrim(str_replace('/storage/', '', (string) $url), '/'));
            }
        }

        return array_values(array_diff($currentImages, $toRemove));
    }

    /** @return array<int, UploadedFile> */
    private function uploadedImages(Request $request): array
    {
        $files = $request->file('images', []);
        if (! is_array($files)) {
            return [];
        }

        return array_values(array_filter($files, function ($file) {
            if (! $file instanceof UploadedFile) {
                return false;
            }
            try {
                return $file->isValid() && $file->getError() === UPLOAD_ERR_OK;
            } catch (\ValueError) {
                return false;
            }
        }));
    }

    /**
     * Stockage via fopen() plutôt que UploadedFile::store() : sous Windows, store()
     * appelle realpath() en interne, qui peut échouer (ValueError) quand les noms
     * courts 8.3 sont désactivés sur le volume.
     */
    private function storeUploadedFile(UploadedFile $file, string $folder, string $prefix): string
    {
        $pathname = $file->getPathname();
        $handle = (is_string($pathname) && $pathname !== '') ? @fopen($pathname, 'r') : false;

        if (! is_resource($handle)) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        $extension = strtolower($file->getClientOriginalExtension()) ?: 'bin';
        $relativePath = $folder.'/'.$prefix.'_'.Str::random(40).'.'.$extension;

        try {
            $stored = Storage::disk('public')->put($relativePath, $handle);
        } finally {
            is_resource($handle) && fclose($handle);
        }

        if (! $stored) {
            abort(422, 'Le fichier téléversé est invalide. Veuillez le sélectionner à nouveau.');
        }

        return '/storage/'.$relativePath;
    }

    // ── CATÉGORIES DE CIRCUITS ──────────────────────────────────────────────

    public function categories(): View
    {
        $categories = TourCategory::withCount('tourPackages')
            ->orderBy('sort_order')
            ->orderBy('name_fr')
            ->get();

        return view('admin.tours.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validateTourCategory($request);
        $data['slug'] = $this->uniqueCategorySlug($data['name_fr']);
        $data['is_active'] = $request->boolean('is_active', true);

        TourCategory::create($data);

        return back()->with('success', "Catégorie « {$data['name_fr']} » créée.");
    }

    public function updateCategory(Request $request, TourCategory $tourCategory): RedirectResponse
    {
        $data = $this->validateTourCategory($request);
        $data['is_active'] = $request->boolean('is_active');

        $tourCategory->update($data);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(TourCategory $tourCategory): RedirectResponse
    {
        if ($tourCategory->tourPackages()->exists()) {
            return back()->with('error', 'Impossible de supprimer : des circuits sont rattachés à cette catégorie.');
        }

        $tourCategory->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    private function validateTourCategory(Request $request): array
    {
        return $request->validate([
            'name_fr' => 'required|string|max:150',
            'name_en' => 'required|string|max:150',
            'icon' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (TourCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
