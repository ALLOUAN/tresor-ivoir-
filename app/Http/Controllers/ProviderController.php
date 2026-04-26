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
        if ($catSlug) {
            $activeCategory = ProviderCategory::where('slug', $catSlug)->first();
            if ($activeCategory) {
                $query->where('category_id', $activeCategory->id);
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
        $featured = Provider::where('status', 'active')->where('is_featured', true)->limit(3)->get();

        return view('providers.index', compact('providers', 'categories', 'activeCategory', 'cities', 'search', 'city', 'price', 'sort', 'featured'));
    }

    public function show(string $slug)
    {
        $provider = Provider::with([
            'category', 'tags', 'hours',
            'approvedReviews.user', 'approvedReviews.reply',
            'media',
        ])
            ->where('slug', $slug)
            ->where('status', 'active')
            ->firstOrFail();

        $provider->increment('views_count');

        $related = Provider::with('category')
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

        return view('providers.show', compact('provider', 'related', 'canReview', 'ratingBreakdown', 'ratingBreakdownCounts', 'isFavorited'));
    }
}
