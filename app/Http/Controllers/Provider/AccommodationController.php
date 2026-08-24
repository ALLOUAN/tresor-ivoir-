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

    public function edit(): View
    {
        $provider = $this->getProvider();
        $this->assertAccommodationCategory($provider);

        $accommodation = $provider->accommodation()->with('media')->first();
        $cities = TouristCity::orderBy('name')->get();
        $categories = TouristCategory::orderBy('sort_order')->get();

        return view('provider.accommodation.edit', compact('provider', 'accommodation', 'cities', 'categories'));
    }

    public function update(Request $request): RedirectResponse
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
        $data['room_types'] = $this->content->parseRoomTypes($request);
        $data['booking_links'] = $this->content->parseBookingLinks($request);

        unset($data['cover_image_file'], $data['thumbnail_file'], $data['media_files']);

        if ($f = $request->file('cover_image_file')) {
            $this->content->deleteImage($accommodation?->cover_image);
            $data['cover_image'] = $this->content->storeImage($f, 'accommodations', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $this->content->deleteImage($accommodation?->thumbnail);
            $data['thumbnail'] = $this->content->storeImage($f, 'accommodations', 'thumb');
        }

        $data['room_types'] = $this->content->uploadRoomPhotos($request, $data['room_types'] ?? []);

        if ($accommodation) {
            $accommodation->update($data);
        } else {
            $data['is_featured'] = false;
            $accommodation = Accommodation::create($data);
        }

        $this->content->storeUploadedMedia($request, $accommodation);

        return redirect()->route('provider.accommodation.edit')
            ->with('success', 'Fiche hébergement mise à jour avec succès.');
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

    private function validateAccommodation(Request $request): array
    {
        return $request->validate([
            'city_id' => 'required|exists:tourist_cities,id',
            'name' => 'required|string|max:150',
            'type' => 'required|in:hotel,resort,guesthouse,hostel,auberge,villa,eco_lodge',
            'stars' => 'nullable|integer|min:0|max:5',
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
            'check_in_time' => 'nullable|string|max:5',
            'check_out_time' => 'nullable|string|max:5',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'integer|exists:tourist_categories,id',
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
            'room_photo_files' => 'nullable|array',
            'room_photo_files.*' => 'nullable|array',
            'room_photo_files.*.*' => 'nullable|file|image|max:5120',
        ]);
    }
}
