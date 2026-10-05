<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Provider;
use App\Models\TouristCity;
use App\Models\TravelAgency;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TravelAgencyController extends Controller
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

    /** Une agence de voyages ne peut être gérée que par un prestataire de la catégorie "agences-voyages". */
    private function assertTravelAgencyCategory(Provider $provider): void
    {
        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

        abort_unless($rootSlug === 'agences-voyages', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Agences de Voyages & Tours.');
    }

    /* ── Vue d'ensemble ──────────────────────────────────────────────────── */

    public function dashboard(): View
    {
        $provider = $this->getProvider();
        $this->assertTravelAgencyCategory($provider);

        $agency = $provider->travelAgency()->with('media')->first();
        $tourPackageCount = $provider->tourPackages()->count();
        $photoCount = $agency ? $agency->photos->count() : 0;

        $checklist = [
            'description' => ! empty($agency?->description),
            'cover_image' => ! empty($agency?->cover_image),
            'tours' => $tourPackageCount > 0,
            'contact' => ! empty($agency?->phone) || ! empty($agency?->email),
        ];

        return view('provider.travel-agency.dashboard', compact('agency', 'tourPackageCount', 'photoCount', 'checklist'));
    }

    /* ── Fiche établissement ─────────────────────────────────────────────── */

    public function editProfile(): View
    {
        $provider = $this->getProvider();
        $this->assertTravelAgencyCategory($provider);

        $agency = $provider->travelAgency()->first();
        $cities = TouristCity::orderBy('name')->get();

        return view('provider.travel-agency.profile', compact('provider', 'agency', 'cities'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertTravelAgencyCategory($provider);

        $agency = $provider->travelAgency()->first();

        $data = $this->validateAgency($request);
        $data['provider_id'] = $provider->id;
        $data['slug'] = $this->uniqueSlug($data['name'], $agency?->id);
        // is_featured reste un levier éditorial réservé à l'administration.
        $data['is_active'] = $request->boolean('is_active', true);
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'travel-agencies', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'travel-agencies', 'thumb');
        }

        if ($agency) {
            $agency->update($data);
        } else {
            $data['is_featured'] = false;
            $agency = TravelAgency::create($data);
        }

        return redirect()->route('provider.travel-agency.profile.edit')
            ->with('success', 'Fiche établissement mise à jour avec succès.');
    }

    /* ── Galerie photos ──────────────────────────────────────────────────── */

    public function gallery(): View
    {
        $provider = $this->getProvider();
        $this->assertTravelAgencyCategory($provider);

        $agency = $provider->travelAgency()->with('media')->first();

        return view('provider.travel-agency.gallery', compact('agency'));
    }

    public function storeGalleryMedia(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertTravelAgencyCategory($provider);

        $agency = $provider->travelAgency()->first();
        abort_unless($agency, 404, "Complétez d'abord la fiche établissement avant d'ajouter des photos.");

        $request->validate([
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
        ]);

        $sort = (int) ($agency->media()->max('sort_order') ?? 0);
        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $url = $this->imageUpload->store($file, 'travel-agencies/media', 'photo');
            $agency->media()->create([
                'mediable_type' => TravelAgency::class,
                'mediable_id' => $agency->id,
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

        return redirect()->route('provider.travel-agency.gallery.index')->with('success', 'Galerie mise à jour.');
    }

    public function destroyMedia(Media $media): RedirectResponse
    {
        $provider = $this->getProvider();
        $agency = $provider->travelAgency()->first();

        if (! $agency || $media->mediable_type !== TravelAgency::class || (int) $media->mediable_id !== (int) $agency->id) {
            abort(403);
        }

        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /* ── Validation & helpers ──────────────────────────────────────────────── */

    private function validateAgency(Request $request): array
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
        $base = Str::slug($name) ?: 'agence';
        $slug = $base;
        $i = 1;
        while (
            TravelAgency::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
