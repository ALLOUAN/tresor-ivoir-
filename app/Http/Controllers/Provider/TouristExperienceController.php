<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Provider;
use App\Models\TouristCity;
use App\Models\TouristExperience;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TouristExperienceController extends Controller
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

    /** Un site touristique ne peut être géré que par un prestataire de la catégorie "sites-touristiques". */
    private function assertTouristExperienceCategory(Provider $provider): void
    {
        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

        abort_unless($rootSlug === 'sites-touristiques', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Sites Touristiques.');
    }

    /* ── Vue d'ensemble ──────────────────────────────────────────────────── */

    public function dashboard(): View
    {
        $provider = $this->getProvider();
        $this->assertTouristExperienceCategory($provider);

        $experience = $provider->touristExperience()->with('media')->first();
        $activityCount = $provider->activities()->count();
        $photoCount = $experience ? $experience->photos->count() : 0;

        $checklist = [
            'description' => ! empty($experience?->description),
            'cover_image' => ! empty($experience?->cover_image),
            'activities' => $activityCount > 0,
            'contact' => ! empty($experience?->phone) || ! empty($experience?->email),
        ];

        return view('provider.tourist-experience.dashboard', compact('experience', 'activityCount', 'photoCount', 'checklist'));
    }

    /* ── Fiche établissement ─────────────────────────────────────────────── */

    public function editProfile(): View
    {
        $provider = $this->getProvider();
        $this->assertTouristExperienceCategory($provider);

        $experience = $provider->touristExperience()->first();
        $cities = TouristCity::orderBy('name')->get();

        return view('provider.tourist-experience.profile', compact('provider', 'experience', 'cities'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertTouristExperienceCategory($provider);

        $experience = $provider->touristExperience()->first();

        $data = $this->validateExperience($request);
        $data['provider_id'] = $provider->id;
        $data['slug'] = $this->uniqueSlug($data['name'], $experience?->id);
        // is_featured reste un levier éditorial réservé à l'administration.
        $data['is_active'] = $request->boolean('is_active', true);
        $data['amenities'] = $this->parseAmenities($request);
        $data['visit_individual_enabled'] = $request->boolean('visit_individual_enabled');
        $data['visit_guided_enabled'] = $request->boolean('visit_guided_enabled');
        $data['visit_group_enabled'] = $request->boolean('visit_group_enabled');

        unset($data['cover_image_file'], $data['thumbnail_file']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'tourist-experiences', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'tourist-experiences', 'thumb');
        }

        if ($experience) {
            $experience->update($data);
        } else {
            $data['is_featured'] = false;
            $experience = TouristExperience::create($data);
        }

        return redirect()->route('provider.tourist-experience.profile.edit')
            ->with('success', 'Fiche établissement mise à jour avec succès.');
    }

    /* ── Galerie photos ──────────────────────────────────────────────────── */

    public function gallery(): View
    {
        $provider = $this->getProvider();
        $this->assertTouristExperienceCategory($provider);

        $experience = $provider->touristExperience()->with('media')->first();

        return view('provider.tourist-experience.gallery', compact('experience'));
    }

    public function storeGalleryMedia(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertTouristExperienceCategory($provider);

        $experience = $provider->touristExperience()->first();
        abort_unless($experience, 404, "Complétez d'abord la fiche établissement avant d'ajouter des photos.");

        $request->validate([
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
        ]);

        $sort = (int) ($experience->media()->max('sort_order') ?? 0);
        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $url = $this->imageUpload->store($file, 'tourist-experiences/media', 'photo');
            $experience->media()->create([
                'mediable_type' => TouristExperience::class,
                'mediable_id' => $experience->id,
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

        return redirect()->route('provider.tourist-experience.gallery.index')->with('success', 'Galerie mise à jour.');
    }

    public function destroyMedia(Media $media): RedirectResponse
    {
        $provider = $this->getProvider();
        $experience = $provider->touristExperience()->first();

        if (! $experience || $media->mediable_type !== TouristExperience::class || (int) $media->mediable_id !== (int) $experience->id) {
            abort(403);
        }

        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /* ── Validation & helpers ──────────────────────────────────────────────── */

    private function validateExperience(Request $request): array
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
            'visit_individual_price_xof' => 'nullable|integer|min:0',
            'visit_guide_supplement_xof' => 'nullable|integer|min:0',
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
        $base = Str::slug($name) ?: 'site';
        $slug = $base;
        $i = 1;
        while (
            TouristExperience::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
