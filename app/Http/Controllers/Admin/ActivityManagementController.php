<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Activity;
use App\Models\ActivityCategory;
use App\Models\Provider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ActivityManagementController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->get('q');
        $category = $request->get('category');
        $availability = $request->get('availability');

        $query = Activity::query()
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

        $activities = $query->paginate(20)->withQueryString();
        $categories = ActivityCategory::query()->orderBy('sort_order')->get();

        $counts = [
            'all' => Activity::count(),
            'available' => Activity::where('is_available', true)->count(),
            'unavailable' => Activity::where('is_available', false)->count(),
        ];

        return view('admin.activities.index', compact('activities', 'categories', 'counts', 'search', 'category', 'availability'));
    }

    public function create(): View
    {
        return view('admin.activities.form', [
            'activity' => new Activity(),
            'categories' => ActivityCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'operators' => $this->leisureProviders(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->storeUploadedFile($file, 'activities/admin', 'activite');
        }

        Activity::create([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('admin.activities.index')->with('success', 'Activité créée.');
    }

    public function edit(Activity $activity): View
    {
        return view('admin.activities.form', [
            'activity' => $activity,
            'categories' => ActivityCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'operators' => $this->leisureProviders(),
        ]);
    }

    public function update(Request $request, Activity $activity): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $activity->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->storeUploadedFile($file, 'activities/admin', 'activite');
        }

        $activity->update([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('admin.activities.index')->with('success', 'Activité mise à jour.');
    }

    public function toggle(Activity $activity): RedirectResponse
    {
        $activity->update(['is_available' => ! $activity->is_available]);

        return back()->with('success', 'Disponibilité mise à jour.');
    }

    public function destroy(Activity $activity): RedirectResponse
    {
        $activity->delete();

        return back()->with('success', 'Activité supprimée.');
    }

    /** Prestataires éligibles (famille de catégories Loisirs & Culture). */
    private function leisureProviders()
    {
        return Provider::query()
            ->whereHas('category', function ($query) {
                $query->where('slug', 'loisirs-culture')
                    ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->where('slug', 'loisirs-culture'));
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
            'category_id' => ['required', 'integer', 'exists:activity_categories,id'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_xof' => ['required', 'integer', 'min:0'],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:10080'],
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

    // ── CATÉGORIES D'ACTIVITÉS ───────────────────────────────────────────────

    public function categories(): View
    {
        $categories = ActivityCategory::withCount('activities')
            ->orderBy('sort_order')
            ->orderBy('name_fr')
            ->get();

        return view('admin.activities.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validateActivityCategory($request);
        $data['slug'] = $this->uniqueCategorySlug($data['name_fr']);
        $data['is_active'] = $request->boolean('is_active', true);

        ActivityCategory::create($data);

        return back()->with('success', "Catégorie « {$data['name_fr']} » créée.");
    }

    public function updateCategory(Request $request, ActivityCategory $activityCategory): RedirectResponse
    {
        $data = $this->validateActivityCategory($request);
        $data['is_active'] = $request->boolean('is_active');

        $activityCategory->update($data);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(ActivityCategory $activityCategory): RedirectResponse
    {
        if ($activityCategory->activities()->exists()) {
            return back()->with('error', 'Impossible de supprimer : des activités sont rattachées à cette catégorie.');
        }

        $activityCategory->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    private function validateActivityCategory(Request $request): array
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

        while (ActivityCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
