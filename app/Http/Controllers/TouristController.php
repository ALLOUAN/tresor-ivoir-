<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\TouristCategory;
use App\Models\TouristCity;
use App\Models\TouristSite;

class TouristController extends Controller
{
    public function cities()
    {
        $cities = TouristCity::withCount(['sites' => fn ($q) => $q->where('is_active', 1)])
            ->where('is_active', 1)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('tourist.cities', compact('cities'));
    }

    public function city(string $slug)
    {
        $city = TouristCity::where('slug', $slug)->where('is_active', 1)->firstOrFail();

        // Catégories ayant au moins un site touristique
        $siteCatIds = TouristSite::where('city_id', $city->id)
            ->where('is_active', 1)
            ->pluck('category_id')
            ->unique();

        // Catégories ayant au moins un hébergement
        $accomCatIds = Accommodation::where('city_id', $city->id)
            ->where('is_active', 1)
            ->get(['category_ids'])
            ->flatMap(fn ($a) => $a->category_ids ?? [])
            ->unique();

        $allCatIds = $siteCatIds->merge($accomCatIds)->unique();

        $categories = TouristCategory::whereIn('id', $allCatIds)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get()
            ->each(function ($cat) use ($city) {
                $cat->sites_count = TouristSite::where('city_id', $city->id)
                    ->where('category_id', $cat->id)
                    ->where('is_active', 1)
                    ->count();
                $cat->accoms_count = Accommodation::where('city_id', $city->id)
                    ->whereJsonContains('category_ids', $cat->id)
                    ->where('is_active', 1)
                    ->count();
            });

        return view('tourist.city', compact('city', 'categories'));
    }

    public function category(string $citySlug, string $categorySlug)
    {
        $city     = TouristCity::where('slug', $citySlug)->where('is_active', 1)->firstOrFail();
        $category = TouristCategory::where('slug', $categorySlug)->where('is_active', 1)->firstOrFail();

        $sites = TouristSite::with(['media' => fn ($q) => $q->where('type', 'photo')->orderBy('sort_order')->limit(1)])
            ->where('city_id', $city->id)
            ->where('category_id', $category->id)
            ->where('is_active', 1)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $accommodations = Accommodation::with(['media' => fn ($q) => $q->where('type', 'photo')->orderBy('sort_order')->limit(1)])
            ->where('city_id', $city->id)
            ->whereJsonContains('category_ids', $category->id)
            ->where('is_active', 1)
            ->orderBy('is_featured', 'desc')
            ->orderBy('stars', 'desc')
            ->orderBy('sort_order')
            ->get();

        return view('tourist.category', compact('city', 'category', 'sites', 'accommodations'));
    }

    public function site(string $slug)
    {
        $site = TouristSite::with(['city', 'category', 'photos', 'videos'])
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        $site->incrementViews();

        $related = TouristSite::with(['media' => fn ($q) => $q->where('type', 'photo')->limit(1)])
            ->where('city_id', $site->city_id)
            ->where('category_id', $site->category_id)
            ->where('id', '!=', $site->id)
            ->where('is_active', 1)
            ->limit(4)
            ->get();

        return view('tourist.site', compact('site', 'related'));
    }
}
