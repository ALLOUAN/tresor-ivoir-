<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Provider;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MenuManagementController extends Controller
{
    public function __construct(private readonly ImageUploadSecurityService $imageUpload) {}

    public function index(Request $request): View
    {
        $search = $request->get('q');
        $category = $request->get('category');
        $availability = $request->get('availability');

        $query = MenuItem::query()
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

        $menuItems = $query->paginate(20)->withQueryString();
        $categories = MenuCategory::query()->orderBy('sort_order')->get();

        $counts = [
            'all' => MenuItem::count(),
            'available' => MenuItem::where('is_available', true)->count(),
            'unavailable' => MenuItem::where('is_available', false)->count(),
        ];

        return view('admin.menu.index', compact('menuItems', 'categories', 'counts', 'search', 'category', 'availability'));
    }

    public function create(): View
    {
        return view('admin.menu.form', [
            'menuItem' => new MenuItem(),
            'categories' => MenuCategory::where('is_active', true)->orderBy('sort_order')->get(),
            'restaurants' => $this->restaurantProviders(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'menu/'.$validated['provider_id'], 'plat');
        }

        MenuItem::create([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Plat créé.');
    }

    public function edit(MenuItem $menuItem): View
    {
        return view('admin.menu.form', [
            'menuItem' => $menuItem,
            'categories' => MenuCategory::where('is_active', true)->orWhere('id', $menuItem->category_id)->orderBy('sort_order')->get(),
            'restaurants' => $this->restaurantProviders(),
        ]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $validated = $this->validated($request, $menuItem);

        $images = $this->removeMarkedImages($request, $menuItem->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'menu/'.$validated['provider_id'], 'plat');
        }

        $menuItem->update([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('admin.menu.index')->with('success', 'Plat mis à jour.');
    }

    public function toggle(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->update(['is_available' => ! $menuItem->is_available]);

        return back()->with('success', 'Disponibilité mise à jour.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $menuItem->delete();

        return back()->with('success', 'Plat supprimé.');
    }

    /** Prestataires éligibles (famille de catégories Restaurants & Gastronomie). */
    private function restaurantProviders()
    {
        return Provider::query()
            ->whereHas('category', function ($query) {
                $query->where('slug', 'restaurants')
                    ->orWhereHas('parent', fn ($parentQuery) => $parentQuery->where('slug', 'restaurants'));
            })
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * Le plat appartient forcément à un prestataire « Restaurants & Gastronomie », dans une catégorie
     * active (ou sa catégorie actuelle) ; les photos sont de vraies images de 5 Mo au plus.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?MenuItem $current = null): array
    {
        return $request->validate([
            'provider_id' => ['required', 'integer', Rule::in($this->restaurantProviders()->pluck('id')->all())],
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', Rule::exists('menu_categories', 'id')->where(fn ($q) => $q->where(fn ($w) => $w->where('is_active', true)
                ->when($current, fn ($w2) => $w2->orWhere('id', $current->category_id))))],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_xof' => ['required', 'integer', 'min:0'],
            'images' => ['nullable', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'max:5120'],
        ], ['provider_id.in' => __('Choisissez un prestataire de la catégorie Restaurants & Gastronomie.')]);
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

    // ── CATÉGORIES DE MENU ──────────────────────────────────────────────────

    public function categories(): View
    {
        $categories = MenuCategory::withCount('menuItems')
            ->orderBy('sort_order')
            ->orderBy('name_fr')
            ->get();

        return view('admin.menu.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $this->validateMenuCategory($request);
        $data['slug'] = $this->uniqueCategorySlug($data['name_fr']);
        $data['is_active'] = $request->boolean('is_active', true);

        MenuCategory::create($data);

        return back()->with('success', "Catégorie « {$data['name_fr']} » créée.");
    }

    public function updateCategory(Request $request, MenuCategory $menuCategory): RedirectResponse
    {
        $data = $this->validateMenuCategory($request);
        $data['is_active'] = $request->boolean('is_active');

        $menuCategory->update($data);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(MenuCategory $menuCategory): RedirectResponse
    {
        if ($menuCategory->menuItems()->exists()) {
            return back()->with('error', 'Impossible de supprimer : des plats sont rattachés à cette catégorie.');
        }
        if ($menuCategory->menuItems()->onlyTrashed()->exists()) {
            return back()->with('error', 'Impossible de supprimer : des plats supprimés y sont encore rattachés. Désactivez plutôt la catégorie.');
        }

        $menuCategory->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    private function validateMenuCategory(Request $request): array
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

        while (MenuCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
