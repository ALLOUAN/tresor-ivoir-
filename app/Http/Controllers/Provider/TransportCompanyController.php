<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Media;
use App\Models\Provider;
use App\Models\TouristCity;
use App\Models\TransportCompany;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

class TransportCompanyController extends Controller
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

    /** Une entreprise de transport ne peut être gérée que par un prestataire de la catégorie "transports". */
    private function assertTransportCompanyCategory(Provider $provider): void
    {
        $category = $provider->category;
        $rootSlug = $category?->parent_id ? $category->parent?->slug : $category?->slug;

        abort_unless($rootSlug === 'transports', 403, 'Cette fonctionnalité est réservée aux prestataires de la catégorie Transports & Mobilité.');
    }

    /* ── Vue d'ensemble ──────────────────────────────────────────────────── */

    public function dashboard(): View
    {
        $provider = $this->getProvider();
        $this->assertTransportCompanyCategory($provider);

        $company = $provider->transportCompany()->with('media')->first();
        $transportOfferCount = $provider->transportOffers()->count();
        $photoCount = $company ? $company->photos->count() : 0;

        $checklist = [
            'description' => ! empty($company?->description),
            'cover_image' => ! empty($company?->cover_image),
            'offers' => $transportOfferCount > 0,
            'contact' => ! empty($company?->phone) || ! empty($company?->email),
        ];

        return view('provider.transport-company.dashboard', compact('company', 'transportOfferCount', 'photoCount', 'checklist'));
    }

    /* ── Fiche établissement ─────────────────────────────────────────────── */

    public function editProfile(): View
    {
        $provider = $this->getProvider();
        $this->assertTransportCompanyCategory($provider);

        $company = $provider->transportCompany()->first();
        $cities = TouristCity::orderBy('name')->get();

        return view('provider.transport-company.profile', compact('provider', 'company', 'cities'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertTransportCompanyCategory($provider);

        $company = $provider->transportCompany()->first();

        $data = $this->validateCompany($request);
        $data['provider_id'] = $provider->id;
        $data['slug'] = $this->uniqueSlug($data['name'], $company?->id);
        // is_featured reste un levier éditorial réservé à l'administration.
        $data['is_active'] = $request->boolean('is_active', true);
        $data['amenities'] = $this->parseAmenities($request);

        unset($data['cover_image_file'], $data['thumbnail_file']);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_image'] = $this->imageUpload->store($f, 'transport-companies', 'cover');
        }
        if ($f = $request->file('thumbnail_file')) {
            $data['thumbnail'] = $this->imageUpload->store($f, 'transport-companies', 'thumb');
        }

        if ($company) {
            $company->update($data);
        } else {
            $data['is_featured'] = false;
            $company = TransportCompany::create($data);
        }

        return redirect()->route('provider.transport-company.profile.edit')
            ->with('success', 'Fiche établissement mise à jour avec succès.');
    }

    /* ── Galerie photos ──────────────────────────────────────────────────── */

    public function gallery(): View
    {
        $provider = $this->getProvider();
        $this->assertTransportCompanyCategory($provider);

        $company = $provider->transportCompany()->with('media')->first();

        return view('provider.transport-company.gallery', compact('company'));
    }

    public function storeGalleryMedia(Request $request): RedirectResponse
    {
        $provider = $this->getProvider();
        $this->assertTransportCompanyCategory($provider);

        $company = $provider->transportCompany()->first();
        abort_unless($company, 404, "Complétez d'abord la fiche établissement avant d'ajouter des photos.");

        $request->validate([
            'media_files' => 'nullable|array',
            'media_files.*' => 'file|image|max:5120',
        ]);

        $sort = (int) ($company->media()->max('sort_order') ?? 0);
        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $url = $this->imageUpload->store($file, 'transport-companies/media', 'photo');
            $company->media()->create([
                'mediable_type' => TransportCompany::class,
                'mediable_id' => $company->id,
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

        return redirect()->route('provider.transport-company.gallery.index')->with('success', 'Galerie mise à jour.');
    }

    public function destroyMedia(Media $media): RedirectResponse
    {
        $provider = $this->getProvider();
        $company = $provider->transportCompany()->first();

        if (! $company || $media->mediable_type !== TransportCompany::class || (int) $media->mediable_id !== (int) $company->id) {
            abort(403);
        }

        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /* ── Validation & helpers ──────────────────────────────────────────────── */

    private function validateCompany(Request $request): array
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
        $base = Str::slug($name) ?: 'entreprise';
        $slug = $base;
        $i = 1;
        while (
            TransportCompany::withTrashed()->where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.(++$i);
        }

        return $slug;
    }
}
