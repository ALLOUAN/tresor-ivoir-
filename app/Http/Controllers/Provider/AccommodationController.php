<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\AccommodationMedia;
use App\Models\Provider;
use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Services\AccommodationContentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AccommodationController extends Controller
{
    public function __construct(private readonly AccommodationContentService $content) {}

    private function getProvider(): Provider
    {
        $provider = Provider::query()->where('user_id', Auth::id())->first();
        if (! $provider) {
            abort(404, 'Aucune fiche prestataire trouvée.');
        }

        return $provider;
    }

    /** Un hébergement ne peut être géré que par un prestataire de la catégorie "hôtels". */
    private function assertAccommodationCategory(Provider $provider): void
    {
        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

        abort_unless($rootSlug === 'hotels', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Hôtels & Hébergements.');
    }

    /** Sans fiche établissement, on renvoie vers « Ma fiche » avec un message clair plutôt qu'un 404. */
    private function ficheRequiredRedirect(string $action): RedirectResponse
    {
        return redirect()->route('provider.accommodation.profile.edit')
            ->withErrors(['fiche' => "Complétez d'abord la fiche établissement {$action}."]);
    }

    /* ── Vue d'ensemble ──────────────────────────────────────────────────── */

    public function dashboard(): View
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->with('media')->first();
        $roomCount = count($accommodation->room_types ?? []);
        $photoCount = $accommodation ? $accommodation->media->count() : 0;

        $checklist = [
            'description' => ! empty($accommodation?->description),
            'cover_image' => ! empty($accommodation?->cover_image),
            'rooms' => $roomCount > 0,
            'booking_links' => ! empty($accommodation?->booking_links),
        ];

        return view('provider.accommodation.dashboard', compact('accommodation', 'roomCount', 'photoCount', 'checklist'));
    }

    /* ── Fiche établissement ─────────────────────────────────────────────── */

    public function editProfile(): View
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        $cities = TouristCity::orderBy('name')->get();
        $categories = TouristCategory::orderBy('sort_order')->get();

        return view('provider.accommodation.profile', compact('provider', 'accommodation', 'cities', 'categories'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();

        $data = $this->validateAccommodation($request);
        $data['provider_id'] = $provider->id;
        $data['slug'] = $this->content->uniqueSlug($data['name'], $accommodation?->id);
        // is_featured reste un levier éditorial réservé à l'administration.
        $data['is_active'] = $request->boolean('is_active', true);
        $data['category_ids'] = $request->input('category_ids')
            ? array_map('intval', $request->input('category_ids')) : null;
        $data['amenities'] = $this->content->parseKeyLabel($request, 'amenity_icons', 'amenity_labels');
        $data['booking_links'] = $this->content->parseBookingLinks($request);

        unset($data['cover_image_file'], $data['thumbnail_file']);

        if ($f = $request->file('cover_image_file')) {
            $this->content->deleteImage($accommodation?->cover_image);
            $data['cover_image'] = $this->content->storeImage($f, 'accommodations', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $this->content->deleteImage($accommodation?->thumbnail);
            $data['thumbnail'] = $this->content->storeImage($f, 'accommodations', 'thumb');
        }

        if ($accommodation) {
            $accommodation->update($data);
        } else {
            $data['is_featured'] = false;
            $accommodation = Accommodation::create($data);
        }

        return redirect()->route('provider.accommodation.profile.edit')
            ->with('success', 'Fiche établissement mise à jour avec succès.');
    }

    /* ── Chambres & tarifs ───────────────────────────────────────────────── */

    public function roomTypes(): View
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        $rooms = $accommodation ? $this->ensureRoomIds($accommodation) : [];

        return view('provider.accommodation.rooms.index', compact('accommodation', 'rooms'));
    }

    public function createRoomType(): View|RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        if (! $accommodation) {
            return $this->ficheRequiredRedirect("avant d'ajouter une chambre");
        }

        return view('provider.accommodation.rooms.form', ['accommodation' => $accommodation, 'room' => null]);
    }

    public function storeRoomType(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        if (! $accommodation) {
            return $this->ficheRequiredRedirect("avant d'ajouter une chambre");
        }

        $this->validateRoomType($request);

        $room = $this->content->parseSingleRoomType($request);
        $room = $this->content->uploadSingleRoomPhotos($request, $room);

        $rooms = $accommodation->room_types ?? [];
        $rooms[] = $room;
        $accommodation->update(['room_types' => $rooms]);

        return redirect()->route('provider.accommodation.rooms.index')->with('success', 'Chambre ajoutée.');
    }

    public function editRoomType(string $room): View
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        $roomData = $accommodation ? collect($this->ensureRoomIds($accommodation))->firstWhere('id', $room) : null;
        abort_unless($roomData, 404);

        return view('provider.accommodation.rooms.form', ['accommodation' => $accommodation, 'room' => $roomData]);
    }

    public function updateRoomType(Request $request, string $room): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        $rooms = $accommodation ? $this->ensureRoomIds($accommodation) : [];
        $index = collect($rooms)->search(fn ($r) => ($r['id'] ?? null) === $room);
        abort_if($index === false, 404);

        $this->validateRoomType($request);

        $updated = $this->content->parseSingleRoomType($request, $room);
        $updated = $this->content->uploadSingleRoomPhotos($request, $updated);

        $rooms[$index] = $updated;
        $accommodation->update(['room_types' => array_values($rooms)]);

        return redirect()->route('provider.accommodation.rooms.index')->with('success', 'Chambre mise à jour.');
    }

    public function destroyRoomType(string $room): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        $rooms = $accommodation ? $this->ensureRoomIds($accommodation) : [];
        $index = collect($rooms)->search(fn ($r) => ($r['id'] ?? null) === $room);
        abort_if($index === false, 404);

        foreach ($rooms[$index]['photos'] ?? [] as $url) {
            $this->content->deleteImage($url);
        }
        unset($rooms[$index]);
        $accommodation->update(['room_types' => array_values($rooms)]);

        return redirect()->route('provider.accommodation.rooms.index')->with('success', 'Chambre supprimée.');
    }

    /* ── Galerie photos ──────────────────────────────────────────────────── */

    public function gallery(): View
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->with(['media', 'photos', 'videos'])->first();

        return view('provider.accommodation.gallery', compact('accommodation'));
    }

    public function storeGalleryMedia(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->first();
        if (! $accommodation) {
            return $this->ficheRequiredRedirect("avant d'ajouter des photos");
        }

        $request->validate([
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
            'video_links' => 'nullable|array',
            'video_links.*' => 'nullable|url|max:500',
        ]);

        $this->content->storeUploadedMedia($request, $accommodation);
        $this->content->storeVideoLinks($request, $accommodation);

        return redirect()->route('provider.accommodation.gallery.index')->with('success', 'Galerie mise à jour.');
    }

    public function destroyMedia(AccommodationMedia $media): RedirectResponse
    {
        $provider = $this->getProvider();
        $accommodation = $provider->accommodation()->first();

        if (! $accommodation || (int) $media->accommodation_id !== (int) $accommodation->id) {
            abort(403);
        }

        $this->content->deleteImage($media->url);
        $media->delete();

        return back()->with('success', 'Média supprimé.');
    }

    /**
     * Rétro-remplit un `id` (uuid) pour les chambres créées avant l'introduction de ce champ
     * (ancien formulaire en bloc, sans identifiant stable par chambre) — persiste immédiatement
     * si un id a dû être généré, pour que les liens Modifier/Supprimer restent stables ensuite.
     *
     * @return array<int, array<string, mixed>>
     */
    private function ensureRoomIds(Accommodation $accommodation): array
    {
        $rooms = $accommodation->room_types ?? [];
        $changed = false;

        foreach ($rooms as &$room) {
            if (empty($room['id'])) {
                $room['id'] = (string) Str::uuid();
                $changed = true;
            }
        }
        unset($room);

        if ($changed) {
            $accommodation->update(['room_types' => $rooms]);
        }

        return $rooms;
    }

    /* ── Validation ──────────────────────────────────────────────────────── */

    private function validateAccommodation(Request $request): array
    {
        return $request->validate([
            'city_id' => 'required|exists:tourist_cities,id',
            'name' => 'required|string|max:150',
            'type' => 'required|in:hotel,residence,resort,guesthouse,hostel,auberge,villa,eco_lodge',
            'stars' => 'nullable|integer|min:0|max:5',
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            'cancellation_policy' => 'nullable|string',
            'adresse' => 'nullable|string|max:255',
            'quartier' => 'nullable|string|max:100',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'phone' => 'nullable|string|max:30',
            'email' => 'nullable|email|max:150',
            'website' => 'nullable|url|max:300',
            'cover_image_file' => 'nullable|file|image|max:5120',
            'thumbnail_file' => 'nullable|file|image|max:5120',
            'check_in_time' => 'nullable|string|max:5',
            'check_out_time' => 'nullable|string|max:5',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:tourist_categories,id',
        ]);
    }

    private function validateRoomType(Request $request): void
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'max_adults' => 'nullable|integer|min:1|max:10',
            'max_children' => 'nullable|integer|min:0|max:10',
            'area_m2' => 'nullable|numeric|min:0',
            // Sans prix, la chambre n'est pas réservable (ni bouton « Réserver », ni tarif affiché).
            'price_xof' => 'required|integer|min:1',
            'price_eur' => 'nullable|numeric|min:0',
            'description' => 'nullable|string|max:2000',
            'beds' => 'nullable|string|max:150',
            'conditions' => 'nullable|string|max:2000',
            'room_photo_files' => 'nullable|array',
            'room_photo_files.*' => 'nullable|file|image|max:5120',
        ]);
    }
}
