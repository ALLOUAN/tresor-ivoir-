<?php

namespace App\Http\Controllers;

use App\Models\Provider;
use App\Models\ProviderCategory;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('q');
        $catSlug = $request->get('categorie');
        $city = $request->get('ville');
        $price = $request->get('prix');
        $verified = $request->boolean('verifie');
        $sort = $request->get('tri', 'rating');

        $query = Provider::with('category')
            ->where('status', 'active');

        $activeCategory = null;
        $categoryIds = null;
        if ($catSlug) {
            $activeCategory = ProviderCategory::where('slug', $catSlug)->first();
            if ($activeCategory) {
                // Un secteur racine (ex: "Hôtels") doit englober ses sous-catégories
                // (ex: "Hôtels de luxe") — un clic depuis le mega-menu ne doit pas
                // exclure les prestataires classés plus finement.
                $categoryIds = $activeCategory->parent_id === null
                    ? ProviderCategory::where('id', $activeCategory->id)->orWhere('parent_id', $activeCategory->id)->pluck('id')
                    : collect([$activeCategory->id]);
                $query->whereIn('category_id', $categoryIds);
            }
        }

        if ($city) {
            $query->where('city', $city);
        }
        if ($price) {
            $query->where('price_range', $price);
        }
        if ($verified) {
            $query->where('is_verified', true);
        }

        if ($search) {
            $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%")
                ->orWhere('description_fr', 'like', "%{$search}%")
            );
        }

        $query = match ($sort) {
            'name' => $query->orderBy('name'),
            'newest' => $query->orderByDesc('created_at'),
            'views' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_featured')->orderByDesc('rating_avg'),
        };

        $providers = $query->paginate(12)->withQueryString();
        $categories = ProviderCategory::where('is_active', true)
            ->withCount(['providers' => fn ($q) => $q->where('status', 'active')])
            ->orderBy('sort_order')->get();
        $cities = Provider::where('status', 'active')->whereNotNull('city')->distinct()->orderBy('city')->pluck('city');

        return view('providers.index', compact('providers', 'categories', 'activeCategory', 'cities', 'search', 'city', 'price', 'sort'));
    }

    public function show(string $slug)
    {
        $provider = Provider::with([
            'category', 'tags', 'hours',
            'approvedReviews.user', 'approvedReviews.reply',
            'media', 'accommodation.media', 'accommodation.videos',
            'menuItems' => fn ($q) => $q->where('is_available', true)->with('category')->orderBy('sort_order'),
            'tourPackages' => fn ($q) => $q->where('is_available', true)->with('category')->orderBy('sort_order'),
            'activities' => fn ($q) => $q->where('is_available', true)->with('category')->orderBy('sort_order'),
            'transportOffers' => fn ($q) => $q->where('is_available', true)->with('category')->orderBy('sort_order'),
            'artworks' => fn ($q) => $q->published()->with('category')->latest(),
            'sponsoredArticles' => fn ($q) => $q->where('status', 'published')->where('published_at', '<=', now())->latest('published_at'),
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $accommodation = $provider->accommodation;
        $menuByCategory = $provider->menuItems->groupBy(fn ($item) => $item->category?->name_fr ?? 'Autres');
        $toursByCategory = $provider->tourPackages->groupBy(fn ($tour) => $tour->category?->name_fr ?? 'Autres');
        $activitiesByCategory = $provider->activities->groupBy(fn ($activity) => $activity->category?->name_fr ?? 'Autres');
        $transportByCategory = $provider->transportOffers->groupBy(fn ($offer) => $offer->category?->name_fr ?? 'Autres');
        $artworksByCategory = $provider->artworks->groupBy(fn ($artwork) => $artwork->category?->name_fr ?? 'Autres');

        $provider->increment('views_count');

        $related = Provider::with('category', 'media', 'accommodation')
            ->where('status', 'active')
            ->where('id', '!=', $provider->id)
            ->where(fn ($q) => $q
                ->where('category_id', $provider->category_id)
                ->orWhere('city', $provider->city)
            )
            ->limit(3)
            ->get();

        $canReview = Auth::check()
            && Auth::user()->role !== 'admin'
            && ! Review::where('provider_id', $provider->id)->where('user_id', Auth::id())->exists();
        $isFavorited = Auth::check() && Auth::user()->role === 'visitor'
            ? Auth::user()->favorites()
                ->where('favoritable_type', Provider::class)
                ->where('favoritable_id', $provider->id)
                ->exists()
            : false;

        $approvedReviews = $provider->approvedReviews;
        $ratingBreakdown = [
            'quality' => round((float) ($approvedReviews->avg('rating_quality') ?? 0), 1),
            'price' => round((float) ($approvedReviews->avg('rating_price') ?? 0), 1),
            'welcome' => round((float) ($approvedReviews->avg('rating_welcome') ?? 0), 1),
            'clean' => round((float) ($approvedReviews->avg('rating_clean') ?? 0), 1),
        ];

        $ratingBreakdownCounts = [
            'quality' => $approvedReviews->whereNotNull('rating_quality')->count(),
            'price' => $approvedReviews->whereNotNull('rating_price')->count(),
            'welcome' => $approvedReviews->whereNotNull('rating_welcome')->count(),
            'clean' => $approvedReviews->whereNotNull('rating_clean')->count(),
        ];

        return view('providers.show', compact('provider', 'accommodation', 'menuByCategory', 'toursByCategory', 'activitiesByCategory', 'transportByCategory', 'artworksByCategory', 'related', 'canReview', 'ratingBreakdown', 'ratingBreakdownCounts', 'isFavorited'));
    }
}
