<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artwork;
use App\Models\ArtworkCategory;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ArtworkManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->get('status');
        $search = $request->get('q');
        $category = $request->get('category');

        $query = Artwork::query()
            ->withTrashed()
            ->with(['provider', 'category'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($search) {
            $query->where('title', 'like', "%{$search}%");
        }

        $artworks = $query->paginate(20)->withQueryString();
        $categories = ArtworkCategory::query()->orderBy('sort_order')->get();

        $counts = [
            'all' => Artwork::count(),
            'pending_review' => Artwork::where('status', Artwork::STATUS_PENDING_REVIEW)->count(),
            'published' => Artwork::where('status', Artwork::STATUS_PUBLISHED)->count(),
            'suspended' => Artwork::where('status', Artwork::STATUS_SUSPENDED)->count(),
            'sold' => Artwork::where('status', Artwork::STATUS_SOLD)->count(),
        ];

        return view('admin.artworks.index', compact('artworks', 'categories', 'counts', 'status', 'search', 'category'));
    }

    public function create(): View
    {
        return view('admin.artworks.form', [
            'artwork' => new Artwork(),
            'categories' => ArtworkCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'artists' => $this->artCreationsProviders(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->storeUploadedFile($file, 'artworks/admin', 'oeuvre');
        }

        if (empty($images)) {
            return back()->withErrors(['images' => 'Ajoutez au moins une photo de l\'œuvre.'])->withInput();
        }

        Artwork::create([
            ...$validated,
            'slug' => $this->uniqueSlug($validated['title']),
            'images' => $images,
        ]);

        return redirect()->route('admin.artworks.index')->with('success', 'Œuvre créée.');
    }

    public function edit(Artwork $artwork): View
    {
        return view('admin.artworks.form', [
            'artwork' => $artwork,
            'categories' => ArtworkCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'artists' => $this->artCreationsProviders(),
        ]);
    }

    public function update(Request $request, Artwork $artwork): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $artwork->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->storeUploadedFile($file, 'artworks/admin', 'oeuvre');
        }

        if (empty($images)) {
            return back()->withErrors(['images' => 'L\'œuvre doit conserver au moins une photo.'])->withInput();
        }

        $artwork->update([
            ...$validated,
            'images' => $images,
        ]);

        return redirect()->route('admin.artworks.index')->with('success', 'Œuvre mise à jour.');
    }

    public function approve(Artwork $artwork): RedirectResponse
    {
        $artwork->update(['status' => Artwork::STATUS_PUBLISHED]);

        return back()->with('success', 'Œuvre approuvée et publiée.');
    }

    public function reject(Artwork $artwork): RedirectResponse
    {
        $artwork->update(['status' => Artwork::STATUS_REJECTED]);

        return back()->with('success', 'Œuvre refusée.');
    }

    public function suspend(Artwork $artwork): RedirectResponse
    {
        $artwork->update(['status' => Artwork::STATUS_SUSPENDED]);

        return back()->with('success', 'Œuvre suspendue.');
    }

    public function destroy(Artwork $artwork): RedirectResponse
    {
        $artwork->delete();

        return back()->with('success', 'Œuvre supprimée.');
    }

    /** Prestataires éligibles comme artistes (famille de catégories Art & Créations). */
    private function artCreationsProviders()
    {
        return Provider::query()
            ->whereHas('category', function ($query) {
                $query->where('slug', 'art-creations')
                    ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->where('slug', 'art-creations'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'provider_id' => ['required', 'integer', 'exists:providers,id'],
            'title' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:artwork_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'medium' => ['nullable', 'string', 'max:255'],
            'dimensions' => ['nullable', 'string', 'max:255'],
            'year_created' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 1)],
            'price_xof' => ['required', 'integer', 'min:1000'],
            'stock_quantity' => ['required', 'integer', 'min:0', 'max:9999'],
            'status' => ['required', 'in:'.implode(',', array_keys(Artwork::STATUS_LABELS))],
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

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $i = 1;
        while (Artwork::where('slug', $slug)->exists()) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
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

    // ── CATÉGORIES D'ŒUVRES ──────────────────────────────────────────────────

    public function categories(): View
    {
        $categories = ArtworkCategory::withCount('artworks')
            ->orderBy('sort_order')
            ->orderBy('name_fr')
            ->get();

        return view('admin.artworks.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validateArtworkCategory($request);
        $data['slug'] = $this->uniqueCategorySlug($data['name_fr']);
        $data['is_active'] = $request->boolean('is_active', true);

        ArtworkCategory::create($data);

        return back()->with('success', "Catégorie « {$data['name_fr']} » créée.");
    }

    public function updateCategory(Request $request, ArtworkCategory $artworkCategory): RedirectResponse
    {
        $data = $this->validateArtworkCategory($request);
        $data['is_active'] = $request->boolean('is_active');

        $artworkCategory->update($data);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(ArtworkCategory $artworkCategory): RedirectResponse
    {
        if ($artworkCategory->artworks()->exists()) {
            return back()->with('error', 'Impossible de supprimer : des œuvres sont rattachées à cette catégorie.');
        }

        $artworkCategory->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    private function validateArtworkCategory(Request $request): array
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

        while (ArtworkCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
