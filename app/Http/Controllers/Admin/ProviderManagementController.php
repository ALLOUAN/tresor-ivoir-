<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Accommodation;
use App\Models\Article;
use App\Models\Event;
use App\Models\Invoice;
use App\Models\LeisureVenue;
use App\Models\Media;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Restaurant;
use App\Models\ReviewReply;
use App\Models\TouristExperience;
use App\Models\TransportCompany;
use App\Models\TravelAgency;
use App\Models\User;
use App\Services\ImageUploadSecurityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ProviderManagementController extends Controller
{
    public function __construct(private readonly ImageUploadSecurityService $imageUpload) {}

    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('q');
        $category = $request->get('category');

        $query = Provider::query()
            ->with(['category', 'user', 'accommodation', 'leisureVenue', 'restaurant', 'touristExperience', 'travelAgency', 'transportCompany'])
            ->withCount(['sponsoredArticles', 'events', 'media'])
            ->latest();

        if ($status) {
            $query->where('status', $status);
        }

        if ($category) {
            $query->where('category_id', $category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('city', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $providers = $query->paginate(20)->withQueryString();
        $categories = ProviderCategory::query()->orderBy('name_fr')->get();
        $accommodations = Accommodation::query()->orderBy('name')->get(['id', 'name', 'provider_id']);
        $leisureVenues = LeisureVenue::query()->orderBy('name')->get(['id', 'name', 'provider_id']);
        $restaurants = Restaurant::query()->orderBy('name')->get(['id', 'name', 'provider_id']);
        $touristExperiences = TouristExperience::query()->orderBy('name')->get(['id', 'name', 'provider_id']);
        $travelAgencies = TravelAgency::query()->orderBy('name')->get(['id', 'name', 'provider_id']);
        $transportCompanies = TransportCompany::query()->orderBy('name')->get(['id', 'name', 'provider_id']);

        $counts = [
            'all' => Provider::count(),
            'active' => Provider::where('status', 'active')->count(),
            'pending' => Provider::where('status', 'pending')->count(),
            'suspended' => Provider::where('status', 'suspended')->count(),
            'featured' => Provider::where('is_featured', true)->count(),
        ];

        return view('admin.providers.index', compact('providers', 'categories', 'accommodations', 'leisureVenues', 'restaurants', 'touristExperiences', 'travelAgencies', 'transportCompanies', 'counts', 'status', 'search', 'category'));
    }

    // ── CATÉGORIES DE PRESTATAIRES ───────────────────────────────────────────

    public function categories()
    {
        $categories = ProviderCategory::withCount('providers')
            ->with('parent')
            ->orderBy('sort_order')
            ->orderBy('name_fr')
            ->get();

        $parentOptions = $categories->whereNull('parent_id');

        return view('admin.providers.categories', compact('categories', 'parentOptions'));
    }

    public function storeCategory(Request $request)
    {
        $data = $this->validateProviderCategory($request);
        $data['slug']      = $this->uniqueCategorySlug($data['name_fr']);
        $data['is_active'] = $request->boolean('is_active', true);

        ProviderCategory::create($data);

        return back()->with('success', "Catégorie « {$data['name_fr']} » créée.");
    }

    public function updateCategory(Request $request, ProviderCategory $providerCategory)
    {
        $data = $this->validateProviderCategory($request);
        $data['is_active'] = $request->boolean('is_active');

        if (! empty($data['parent_id']) && (int) $data['parent_id'] === $providerCategory->id) {
            return back()->withErrors(['parent_id' => "Une catégorie ne peut pas être sa propre catégorie parente."])->withInput();
        }

        $providerCategory->update($data);

        return back()->with('success', 'Catégorie mise à jour.');
    }

    public function destroyCategory(ProviderCategory $providerCategory)
    {
        if ($providerCategory->providers()->exists()) {
            return back()->with('error', 'Impossible de supprimer : des prestataires sont rattachés à cette catégorie.');
        }

        if ($providerCategory->children()->exists()) {
            return back()->with('error', 'Impossible de supprimer : cette catégorie a des sous-catégories.');
        }

        $providerCategory->delete();

        return back()->with('success', 'Catégorie supprimée.');
    }

    private function validateProviderCategory(Request $request): array
    {
        return $request->validate([
            'name_fr'        => 'required|string|max:150',
            'name_en'        => 'required|string|max:150',
            'icon'           => 'nullable|string|max:100',
            'color_hex'      => 'nullable|string|max:7',
            'description_fr' => 'nullable|string',
            'description_en' => 'nullable|string',
            'parent_id'      => 'nullable|exists:provider_categories,id',
            'sort_order'     => 'nullable|integer|min:0',
        ]);
    }

    private function uniqueCategorySlug(string $name): string
    {
        $base = Str::slug($name) ?: Str::lower(Str::random(8));
        $slug = $base;
        $i = 2;

        while (ProviderCategory::where('slug', $slug)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'user_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->whereNull('deleted_at')],
            'password' => 'required|string|min:8',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:provider_categories,id',
            'provider_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:500',
            'city' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:500',
            'description_fr' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
            'is_featured' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'accommodation_id' => 'nullable|exists:accommodations,id',
            'leisure_venue_id' => 'nullable|exists:leisure_venues,id',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'tourist_experience_id' => 'nullable|exists:tourist_experiences,id',
            'travel_agency_id' => 'nullable|exists:travel_agencies,id',
            'transport_company_id' => 'nullable|exists:transport_companies,id',
            'cover_image_file' => 'nullable|file|image|max:5120',
        ]);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_url'] = $this->imageUpload->store($f, 'providers/covers', 'cover');
        }

        DB::transaction(function () use ($data) {
            User::withTrashed()
                ->where('email', $data['user_email'])
                ->whereNotNull('deleted_at')
                ->update(['email' => 'deleted_' . time() . '_' . $data['user_email']]);

            $user = User::create([
                'email' => $data['user_email'],
                'password_hash' => $data['password'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'provider',
                'is_active' => true,
            ]);

            $baseSlug = Str::slug($data['name']);
            $slugRoot = $baseSlug ?: Str::lower(Str::random(8));
            $slug = $slugRoot;
            $i = 2;

            while (Provider::where('slug', $slug)->exists()) {
                $slug = "{$slugRoot}-{$i}";
                $i++;
            }

            $provider = Provider::create([
                'user_id' => $user->id,
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'slug' => $slug,
                'description_fr' => $data['description_fr'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'email' => $data['provider_email'] ?? $data['user_email'],
                'status' => $data['status'],
                'is_featured' => ! empty($data['is_featured']),
                'is_verified' => ! empty($data['is_verified']),
                'cover_url' => $data['cover_url'] ?? null,
            ]);

            $this->syncAccommodationLink($provider, isset($data['accommodation_id']) ? (int) $data['accommodation_id'] : null);
            $this->syncLeisureVenueLink($provider, isset($data['leisure_venue_id']) ? (int) $data['leisure_venue_id'] : null);
            $this->syncRestaurantLink($provider, isset($data['restaurant_id']) ? (int) $data['restaurant_id'] : null);
            $this->syncTouristExperienceLink($provider, isset($data['tourist_experience_id']) ? (int) $data['tourist_experience_id'] : null);
            $this->syncTravelAgencyLink($provider, isset($data['travel_agency_id']) ? (int) $data['travel_agency_id'] : null);
            $this->syncTransportCompanyLink($provider, isset($data['transport_company_id']) ? (int) $data['transport_company_id'] : null);
        });

        return redirect()->route('admin.providers.index')->with('success', 'Prestataire créé avec succès.');
    }

    public function update(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'user_email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($provider->user_id)],
            'password' => 'nullable|string|min:8',
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:provider_categories,id',
            'provider_email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'website' => 'nullable|url|max:500',
            'city' => 'nullable|string|max:150',
            'address' => 'nullable|string|max:500',
            'description_fr' => 'nullable|string',
            'status' => 'required|in:pending,active,suspended',
            'is_featured' => 'nullable|boolean',
            'is_verified' => 'nullable|boolean',
            'edit_provider_id' => 'nullable|integer',
            'accommodation_id' => 'nullable|exists:accommodations,id',
            'leisure_venue_id' => 'nullable|exists:leisure_venues,id',
            'restaurant_id' => 'nullable|exists:restaurants,id',
            'tourist_experience_id' => 'nullable|exists:tourist_experiences,id',
            'travel_agency_id' => 'nullable|exists:travel_agencies,id',
            'transport_company_id' => 'nullable|exists:transport_companies,id',
            'cover_image_file' => 'nullable|file|image|max:5120',
        ]);

        if ($f = $request->file('cover_image_file')) {
            $data['cover_url'] = $this->imageUpload->store($f, 'providers/covers', 'cover');
        }

        DB::transaction(function () use ($provider, $data) {
            $userPayload = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['user_email'],
                'role' => 'provider',
            ];

            if (! empty($data['password'])) {
                $userPayload['password_hash'] = $data['password'];
            }

            $provider->user->update($userPayload);

            $provider->update([
                'category_id' => $data['category_id'],
                'name' => $data['name'],
                'description_fr' => $data['description_fr'] ?? null,
                'address' => $data['address'] ?? null,
                'city' => $data['city'] ?? null,
                'phone' => $data['phone'] ?? null,
                'website' => $data['website'] ?? null,
                'email' => $data['provider_email'] ?? $data['user_email'],
                'status' => $data['status'],
                'is_featured' => ! empty($data['is_featured']),
                'is_verified' => ! empty($data['is_verified']),
                ...(isset($data['cover_url']) ? ['cover_url' => $data['cover_url']] : []),
            ]);

            $this->syncAccommodationLink($provider, isset($data['accommodation_id']) ? (int) $data['accommodation_id'] : null);
            $this->syncLeisureVenueLink($provider, isset($data['leisure_venue_id']) ? (int) $data['leisure_venue_id'] : null);
            $this->syncRestaurantLink($provider, isset($data['restaurant_id']) ? (int) $data['restaurant_id'] : null);
            $this->syncTouristExperienceLink($provider, isset($data['tourist_experience_id']) ? (int) $data['tourist_experience_id'] : null);
            $this->syncTravelAgencyLink($provider, isset($data['travel_agency_id']) ? (int) $data['travel_agency_id'] : null);
            $this->syncTransportCompanyLink($provider, isset($data['transport_company_id']) ? (int) $data['transport_company_id'] : null);
        });

        return redirect()->route('admin.providers.index')->with('success', 'Prestataire modifié avec succès.');
    }

    /**
     * Relie (ou délie) l'hébergement choisi à ce prestataire — un hébergement ne peut être
     * lié qu'à un seul prestataire à la fois (contrainte unique sur accommodations.provider_id).
     */
    private function syncAccommodationLink(Provider $provider, ?int $accommodationId): void
    {
        Accommodation::where('provider_id', $provider->id)->update(['provider_id' => null]);

        if ($accommodationId) {
            Accommodation::where('id', $accommodationId)->update(['provider_id' => $provider->id]);
        }
    }

    /**
     * Relie (ou délie) l'établissement Loisirs & Culture choisi à ce prestataire — un
     * établissement ne peut être lié qu'à un seul prestataire (contrainte unique sur
     * leisure_venues.provider_id).
     */
    private function syncLeisureVenueLink(Provider $provider, ?int $leisureVenueId): void
    {
        LeisureVenue::where('provider_id', $provider->id)->update(['provider_id' => null]);

        if ($leisureVenueId) {
            LeisureVenue::where('id', $leisureVenueId)->update(['provider_id' => $provider->id]);
        }
    }

    /**
     * Relie (ou délie) le restaurant choisi à ce prestataire — un restaurant ne peut
     * être lié qu'à un seul prestataire (contrainte unique sur restaurants.provider_id).
     */
    private function syncRestaurantLink(Provider $provider, ?int $restaurantId): void
    {
        Restaurant::where('provider_id', $provider->id)->update(['provider_id' => null]);

        if ($restaurantId) {
            Restaurant::where('id', $restaurantId)->update(['provider_id' => $provider->id]);
        }
    }

    /**
     * Relie (ou délie) le site touristique choisi à ce prestataire — un site ne peut
     * être lié qu'à un seul prestataire (contrainte unique sur tourist_experiences.provider_id).
     */
    private function syncTouristExperienceLink(Provider $provider, ?int $touristExperienceId): void
    {
        TouristExperience::where('provider_id', $provider->id)->update(['provider_id' => null]);

        if ($touristExperienceId) {
            TouristExperience::where('id', $touristExperienceId)->update(['provider_id' => $provider->id]);
        }
    }

    /**
     * Relie (ou délie) l'agence de voyages choisie à ce prestataire — une agence ne peut
     * être liée qu'à un seul prestataire (contrainte unique sur travel_agencies.provider_id).
     */
    private function syncTravelAgencyLink(Provider $provider, ?int $travelAgencyId): void
    {
        TravelAgency::where('provider_id', $provider->id)->update(['provider_id' => null]);

        if ($travelAgencyId) {
            TravelAgency::where('id', $travelAgencyId)->update(['provider_id' => $provider->id]);
        }
    }

    /**
     * Relie (ou délie) l'entreprise de transport choisie à ce prestataire — une entreprise
     * ne peut être liée qu'à un seul prestataire (contrainte unique sur transport_companies.provider_id).
     */
    private function syncTransportCompanyLink(Provider $provider, ?int $transportCompanyId): void
    {
        TransportCompany::where('provider_id', $provider->id)->update(['provider_id' => null]);

        if ($transportCompanyId) {
            TransportCompany::where('id', $transportCompanyId)->update(['provider_id' => $provider->id]);
        }
    }

    public function validateProvider(Provider $provider)
    {
        $provider->update(['status' => 'active']);

        return back()->with('success', 'Prestataire validé avec succès.');
    }

    public function suspend(Provider $provider)
    {
        $provider->update(['status' => 'suspended']);

        return back()->with('success', 'Prestataire suspendu avec succès.');
    }

    public function destroy(Provider $provider): RedirectResponse
    {
        DB::transaction(function () use ($provider): void {
            $provider->loadMissing('user');

            // Purge explicit FK dependents that are not configured with cascade.
            // Invoices référencent payments via payment_id → supprimer invoices en premier.
            Invoice::query()->where('provider_id', $provider->id)->delete();
            Payment::query()->where('provider_id', $provider->id)->delete();
            ReviewReply::query()->where('provider_id', $provider->id)->delete();

            $provider->forceDelete();

            if ($provider->user) {
                $provider->user->update([
                    'is_active' => false,
                ]);
            }
        });

        return back()->with('success', 'Compte prestataire supprimé avec succès.');
    }

    public function content(Provider $provider)
    {
        $provider->load(['category', 'user']);

        $articles = Article::query()
            ->where('sponsor_id', $provider->id)
            ->latest('published_at')
            ->latest('id')
            ->get();

        $events = Event::query()
            ->where('provider_id', $provider->id)
            ->latest('published_at')
            ->latest('id')
            ->get();

        $mediaItems = Media::query()
            ->where('mediable_type', Provider::class)
            ->where('mediable_id', $provider->id)
            ->latest('created_at')
            ->latest('id')
            ->get();

        $providers = Provider::query()
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('admin.providers.content', compact('provider', 'articles', 'events', 'mediaItems', 'providers'));
    }

    public function reassignArticle(Request $request, Provider $provider, Article $article)
    {
        if ((int) $article->sponsor_id !== (int) $provider->id) {
            abort(404);
        }

        $data = $request->validate([
            'target_provider_id' => ['required', 'exists:providers,id'],
        ]);

        $article->update(['sponsor_id' => (int) $data['target_provider_id']]);

        return back()->with('success', 'Article réattribué.');
    }

    public function reassignArticlesBulk(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'target_provider_id' => ['required', 'exists:providers,id'],
            'article_ids' => ['required', 'array', 'min:1'],
            'article_ids.*' => ['integer', 'exists:articles,id'],
        ]);

        Article::query()
            ->where('sponsor_id', $provider->id)
            ->whereIn('id', $data['article_ids'])
            ->update(['sponsor_id' => (int) $data['target_provider_id']]);

        return back()->with('success', 'Articles réattribués en masse.');
    }

    public function reassignEvent(Request $request, Provider $provider, Event $event)
    {
        if ((int) $event->provider_id !== (int) $provider->id) {
            abort(404);
        }

        $data = $request->validate([
            'target_provider_id' => ['required', 'exists:providers,id'],
        ]);

        $event->update(['provider_id' => (int) $data['target_provider_id']]);

        return back()->with('success', 'Événement réattribué.');
    }

    public function reassignEventsBulk(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'target_provider_id' => ['required', 'exists:providers,id'],
            'event_ids' => ['required', 'array', 'min:1'],
            'event_ids.*' => ['integer', 'exists:events,id'],
        ]);

        Event::query()
            ->where('provider_id', $provider->id)
            ->whereIn('id', $data['event_ids'])
            ->update(['provider_id' => (int) $data['target_provider_id']]);

        return back()->with('success', 'Événements réattribués en masse.');
    }

    public function storeMedia(Request $request, Provider $provider): RedirectResponse
    {
        $request->validate([
            'media_files'   => 'required|array|min:1',
            'media_files.*' => 'file|image|max:5120',
        ]);

        $sort = (int) ($provider->media()->max('sort_order') ?? 0);

        foreach ($request->file('media_files', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $sort++;
            $ext      = strtolower($file->getClientOriginalExtension()) ?: 'jpg';
            $filePath = 'providers/media/photo_' . Str::random(32) . '.' . $ext;
            Storage::disk('public')->put($filePath, fopen($file->getPathname(), 'r'));

            $provider->media()->create([
                'collection'    => 'gallery',
                'type'          => 'image',
                'mime_type'     => $file->getMimeType(),
                'original_name' => $file->getClientOriginalName(),
                'file_path'     => $filePath,
                'url'           => '/storage/' . $filePath,
                'size_bytes'    => $file->getSize(),
                'sort_order'    => $sort,
                'uploaded_by'   => auth()->id(),
            ]);
        }

        return back()->with('success', 'Photos ajoutées avec succès.');
    }

    public function destroyMedia(Provider $provider, Media $media): RedirectResponse
    {
        if ($media->mediable_type !== Provider::class || (int) $media->mediable_id !== (int) $provider->id) {
            abort(404);
        }
        if ($media->file_path) {
            Storage::disk('public')->delete($media->file_path);
        }
        $media->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    public function reassignMedia(Request $request, Provider $provider, Media $media)
    {
        if ($media->mediable_type !== Provider::class || (int) $media->mediable_id !== (int) $provider->id) {
            abort(404);
        }

        $data = $request->validate([
            'target_provider_id' => ['required', 'exists:providers,id'],
        ]);

        $media->update([
            'mediable_type' => Provider::class,
            'mediable_id' => (int) $data['target_provider_id'],
        ]);

        return back()->with('success', 'Photo/média réattribué.');
    }

    public function reassignMediaBulk(Request $request, Provider $provider)
    {
        $data = $request->validate([
            'target_provider_id' => ['required', 'exists:providers,id'],
            'media_ids' => ['required', 'array', 'min:1'],
            'media_ids.*' => ['integer', 'exists:media,id'],
        ]);

        Media::query()
            ->where('mediable_type', Provider::class)
            ->where('mediable_id', $provider->id)
            ->whereIn('id', $data['media_ids'])
            ->update([
                'mediable_type' => Provider::class,
                'mediable_id' => (int) $data['target_provider_id'],
            ]);

        return back()->with('success', 'Médias réattribués en masse.');
    }
}
