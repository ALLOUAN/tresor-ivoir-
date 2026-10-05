<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Provider;
use App\Models\Restaurant;
use App\Models\TouristCity;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RestaurantController extends Controller
{
    public function __construct(private readonly ImageUploadSecurityService $imageUpload) {}

    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    /** Un restaurant ne peut être géré que par un prestataire de la catégorie "restaurants". */
    private function assertRestaurantCategory(Provider $provider): void
    {
        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

        abort_unless($rootSlug === 'restaurants', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Restaurants & Gastronomie.');
    }

    /* ── Vue d'ensemble ──────────────────────────────────────────────────── */

    public function dashboard(): View
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);

        $restaurant = $provider->restaurant()->with('media')->first();
        $menuItemCount = $provider->menuItems()->count();
        $photoCount = $restaurant ? $restaurant->photos->count() : 0;

        $checklist = [
            'description' => ! empty($restaurant?->description),
            'cover_image' => ! empty($restaurant?->cover_image),
            'menu' => $menuItemCount > 0,
            'contact' => ! empty($restaurant?->phone) || ! empty($restaurant?->email),
        ];

        return view('provider.restaurant.dashboard', compact('restaurant', 'menuItemCount', 'photoCount', 'checklist'));
    }

    /* ── Fiche établissement ─────────────────────────────────────────────── */

    public function editProfile(): View
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);

        $restaurant = $provider->restaurant()->first();
        $cities = TouristCity::orderBy('name')->get();

        return view('provider.restaurant.profile', compact('provider', 'restaurant', 'cities'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);

        $restaurant = $provider->restaurant()->first();

        $data = $this->validateRestaurant($request);
        $data['provider_id'] = $provider->id;
        $data['slug'] = $this->uniqueSlug($data['name'], $restaurant?->id);
        // is_featured reste un levier éditorial réservé à l'administration.
        $data['is_active'] = $request->boolean('is_active', true);
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'restaurants', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'restaurants', 'thumb');
        }

        if ($restaurant) {
            $restaurant->update($data);
        } else {
            $data['is_featured'] = false;
            $restaurant = Restaurant::create($data);
        }

        return redirect()->route('provider.restaurant.profile.edit')
            ->with('success', 'Fiche établissement mise à jour avec succès.');
    }

    /* ── Galerie photos ──────────────────────────────────────────────────── */

    public function gallery(): View
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);

        $restaurant = $provider->restaurant()->with('media')->first();

        return view('provider.restaurant.gallery', compact('restaurant'));
    }

    public function storeGalleryMedia(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);

        $restaurant = $provider->restaurant()->first();
        abort_unless($restaurant, 404, "Complétez d'abord la fiche établissement avant d'ajouter des photos.");

        $request->validate([
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
        ]);

        $sort = (int) ($restaurant->media()->max('sort_order') ?? 0);
        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $url = $this->imageUpload->store($file, 'restaurants/media', 'photo');
            $restaurant->media()->create([
                'mediable_type' => Restaurant::class,
                'mediable_id' => $restaurant->id,
                'collection' => 'gallery',
                'type' => 'image',
                'mime_type' => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'file_path' => Str::after($url, '/storage/'),
                'url' => $url,
                'size_bytes' => $file->getSize(),
                'sort_order' => $sort,
                'uploaded_by' => Auth::id(),
            ]);
        }

        return redirect()->route('provider.restaurant.gallery.index')->with('success', 'Galerie mise à jour.');
    }

    public function destroyMedia(Media $media): RedirectResponse
    {
        $provider = $this->getProvider();
        $restaurant = $provider->restaurant()->first();

        if (! $restaurant || $media->mediable_type !== Restaurant::class || (int) $media->mediable_id !== (int) $restaurant->id) {
            abort(403);
        }

        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /* ── Validation & helpers ──────────────────────────────────────────────── */

    private function validateRestaurant(Request $request): array
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
        ]);
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
        $base = Str::slug($name) ?: 'restaurant';
        $slug = $base;
        $i = 1;
        while (
            Restaurant::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
