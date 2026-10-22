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
        $provider = Provider::forUserQuery(Auth::id())->first();
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
        // Adresse stable : fixée à la création, pour ne pas casser les menus QR imprimés ni les liens partagés.
        $data['slug'] = $restaurant?->slug ?: $this->uniqueSlug($data['name']);
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

    /* ── Réservations de tables, plat du jour, menu QR ─────────────────────── */

    public function editTables(): View|RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);
        $restaurant = $provider->restaurant()->first();
        if (! $restaurant) {
            return redirect()->route('provider.restaurant.profile.edit')->with('error', 'Créez d\'abord la fiche de votre restaurant.');
        }
        $today = \App\Models\BookingRequest::with('restaurantTable')->where('bookable_type', 'restaurant')->where('bookable_id', $restaurant->id)
            ->whereDate('booking_date', now()->toDateString())->whereIn('status', ['new', 'confirmed'])->orderBy('booking_time')->get();

        $tables = $restaurant->tables()->get();

        return view('provider.restaurant.tables', compact('restaurant', 'today', 'tables'));
    }

    public function updateTables(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);
        $restaurant = $provider->restaurant()->firstOrFail();
        $data = $request->validate([
            'table_capacity' => 'nullable|integer|min:1|max:2000',
            'slot_minutes' => 'required|integer|in:15,30,45,60,90,120',
            'table_duration_minutes' => 'nullable|integer|in:60,90,120,150,180',
            'max_party_size' => 'nullable|integer|min:1|max:500',
            'daily_special' => 'nullable|string|max:200',
            'daily_special_price_xof' => 'nullable|integer|min:0',
        ], [], ['table_capacity' => 'couverts par créneau', 'daily_special' => 'plat du jour']);
        $data['auto_confirm'] = $request->boolean('auto_confirm') && ! empty($data['table_capacity']);
        // Commandes de plats : à emporter, et livraison seulement si les commandes sont ouvertes.
        $data['accepts_orders'] = $request->boolean('accepts_orders');
        $data['offers_delivery'] = $data['accepts_orders'] && $request->boolean('offers_delivery');
        // Le plat du jour vaut pour aujourd'hui : il disparaît tout seul le lendemain.
        $data['daily_special_date'] = ! empty($data['daily_special']) ? now('Africa/Abidjan')->toDateString() : null;
        $restaurant->update($data);

        return back()->with('success', 'Réglages des réservations enregistrés.');
    }

    /* ── Plan de salle : tables ──────────────────────────────────────────── */

    public function storeTable(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);
        $restaurant = $provider->restaurant()->firstOrFail();
        $data = $request->validate([
            'label' => ['required', 'string', 'max:30', \Illuminate\Validation\Rule::unique('restaurant_tables', 'label')->where('restaurant_id', $restaurant->id)],
            'seats' => 'required|integer|min:1|max:50',
            'zone' => 'required|string|max:40',
            'shape' => 'required|in:'.implode(',', array_keys(\App\Models\RestaurantTable::SHAPES)),
            'count' => 'nullable|integer|min:1|max:30',
        ], ['label.unique' => 'Une table porte déjà ce numéro.'], ['label' => 'numéro de table', 'seats' => 'places']);

        // Ajout en série : « T » × 5 crée T1 à T5 (en sautant les numéros déjà pris).
        $count = (int) ($data['count'] ?? 1);
        $created = 0;
        $sort = (int) $restaurant->tables()->max('sort_order');
        for ($i = 1; $created < $count && $i <= 200; $i++) {
            $label = $count > 1 ? $data['label'].$i : $data['label'];
            if ($restaurant->tables()->where('label', $label)->exists()) {
                if ($count === 1) break;
                continue;
            }
            $restaurant->tables()->create(['label' => $label, 'seats' => $data['seats'], 'zone' => trim($data['zone']), 'shape' => $data['shape'], 'sort_order' => ++$sort]);
            $created++;
        }

        return back()->with('success', $created > 1 ? $created.' tables ajoutées au plan de salle.' : 'Table ajoutée au plan de salle.');
    }

    public function toggleTable(\App\Models\RestaurantTable $table): RedirectResponse
    {
        $this->ownTable($table);
        $table->update(['is_active' => ! $table->is_active]);

        return back()->with('success', $table->is_active ? 'Table remise en service.' : 'Table retirée du plan (hors service).');
    }

    public function destroyTable(\App\Models\RestaurantTable $table): RedirectResponse
    {
        $this->ownTable($table);
        // Les réservations passées gardent le nom de la table (item_name) ; le lien est simplement retiré.
        $table->delete();

        return back()->with('success', 'Table supprimée du plan de salle.');
    }

    private function ownTable(\App\Models\RestaurantTable $table): void
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);
        abort_unless((int) $table->restaurant_id === (int) $provider->restaurant()->value('id'), 403);
    }

    public function menuQr(): View
    {
        $provider = $this->getProvider();
        $this->assertRestaurantCategory($provider);
        $restaurant = $provider->restaurant()->firstOrFail();
        $url = route('restaurant.menu', $restaurant->slug).'?utm_source=qr_table';

        return view('provider.restaurant.menu-qr', ['restaurant' => $restaurant, 'url' => $url, 'qr' => \App\Support\Qr::dataUri($url, 520)]);
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
