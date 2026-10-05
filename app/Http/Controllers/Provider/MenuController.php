<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\MenuCategory;
use App\Models\MenuItem;
use App\Models\Provider;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(
        private readonly ImageUploadSecurityService $imageUpload,
    ) {}

    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;
        abort_unless($rootSlug === 'restaurants', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Restaurants & Gastronomie.');

        return $provider;
    }

    /** Page d'accueil de l'espace dédié Restaurant. */
    public function dashboard(): View
    {
        $provider = $this->getProvider();

        $menuCounts = [
            'available' => MenuItem::where('provider_id', $provider->id)->where('is_available', true)->count(),
            'unavailable' => MenuItem::where('provider_id', $provider->id)->where('is_available', false)->count(),
        ];

        return view('provider.menu.dashboard', compact('menuCounts'));
    }

    public function index(): View
    {
        $provider = $this->getProvider();

        $menuItems = MenuItem::where('provider_id', $provider->id)
            ->with('category')
            ->orderByDesc('id')
            ->paginate(15);

        return view('provider.menu.index', compact('menuItems'));
    }

    public function create(): View
    {
        $this->getProvider();

        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.menu.form', ['menuItem' => new MenuItem(), 'categories' => $categories]);
    }

    public function store(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();

        $validated = $this->validated($request);

        $images = [];
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'menu/'.$provider->id, 'plat');
        }

        MenuItem::create([
            ...$validated,
            'provider_id' => $provider->id,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('provider.menu.index')->with('success', 'Plat ajouté à la carte.');
    }

    public function edit(MenuItem $menuItem): View
    {
        $provider = $this->getProvider();
        abort_unless((int) $menuItem->provider_id === (int) $provider->id, 403);

        $categories = MenuCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('provider.menu.form', ['menuItem' => $menuItem, 'categories' => $categories]);
    }

    public function update(Request $request, MenuItem $menuItem): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $menuItem->provider_id === (int) $provider->id, 403);

        $validated = $this->validated($request);

        $images = $this->removeMarkedImages($request, $menuItem->images ?? []);
        foreach ($this->uploadedImages($request) as $file) {
            $images[] = $this->imageUpload->store($file, 'menu/'.$provider->id, 'plat');
        }

        $menuItem->update([
            ...$validated,
            'images' => $images,
            'is_available' => $request->boolean('is_available', true),
        ]);

        return redirect()->route('provider.menu.index')->with('success', 'Plat mis à jour.');
    }

    public function destroy(MenuItem $menuItem): RedirectResponse
    {
        $provider = $this->getProvider();
        abort_unless((int) $menuItem->provider_id === (int) $provider->id, 403);

        $menuItem->delete();

        return redirect()->route('provider.menu.index')->with('success', 'Plat retiré de la carte.');
    }

    /** @return array<string, mixed> */
    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'integer', 'exists:menu_categories,id'],
            'description' => ['nullable', 'string', 'max:2000'],
            'price_xof' => ['required', 'integer', 'min:0'],
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

}
