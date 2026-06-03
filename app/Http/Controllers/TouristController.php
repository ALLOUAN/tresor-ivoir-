<?php

namespace App\Http\Controllers;

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

        $categoryIds = TouristSite::where('city_id', $city->id)
            ->where('is_active', 1)
            ->pluck('category_id')
            ->unique();

        $categories = TouristCategory::whereIn('id', $categoryIds)
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get()
            ->each(function ($cat) use ($city) {
                $cat->sites_count = TouristSite::where('city_id', $city->id)
                    ->where('category_id', $cat->id)
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

        return view('tourist.category', compact('city', 'category', 'sites'));
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
