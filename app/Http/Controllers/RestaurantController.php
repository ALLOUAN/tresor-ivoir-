<?php

namespace App\Http\Controllers;

use App\Models\Restaurant;
use App\Models\TouristCity;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fiche riche « Restaurants & Gastronomie » — même patron que LeisureVenueController,
 * sans réservation/paiement : fiche de présentation + la carte du prestataire
 * (via MenuItem), avec contact direct plutôt qu'un paiement en ligne.
 */
class RestaurantController extends Controller
{
    public function index(Request $request): View
    {
        $cityId = $request->get('ville');
        $sort = $request->get('tri', 'pertinence');

        $query = Restaurant::active()->with(['city', 'provider']);

        if ($cityId) {
            $query->forCity((int) $cityId);
        }

        $restaurants = (match ($sort) {
            'recent' => $query->orderByDesc('created_at'),
            'popularite' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_featured'),
        })->get();

        $cities = $cityId
            ? TouristCity::where('id', $cityId)->get()
            : TouristCity::active()->orderBy('name')->get();

        return view('restaurants.index', [
            'restaurants' => $restaurants,
            'cities' => $cities,
            'cityId' => $cityId,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug): View
    {
        $restaurant = Restaurant::active()
            ->where('slug', $slug)
            ->with(['city', 'provider', 'media'])
            ->firstOrFail();

        $restaurant->incrementViews();

        $menuByCategory = collect();
        if ($restaurant->provider) {
            $menuByCategory = $restaurant->provider->menuItems()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($item) => $item->category?->name_fr ?? 'Autres');
        }

        $reviews = $restaurant->provider
            ? $restaurant->provider->approvedReviews()->latest()->take(5)->get()
            : collect();

        $related = Restaurant::active()
            ->where('id', '!=', $restaurant->id)
            ->where('city_id', $restaurant->city_id)
            ->limit(3)
            ->get();

        return view('restaurants.show', compact('restaurant', 'menuByCategory', 'reviews', 'related'));
    }

    /**
     * Page dédiée listant tous les plats de la carte — seule source d'affichage de la
     * liste illustrée, afin de ne pas dupliquer ce design sur la fiche générale.
     */
    public function menu(string $slug): View
    {
        $restaurant = Restaurant::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $menuByCategory = collect();
        if ($restaurant->provider) {
            $menuByCategory = $restaurant->provider->menuItems()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($item) => $item->category?->name_fr ?? 'Autres');
        }

        return view('restaurants.menu', compact('restaurant', 'menuByCategory'));
    }
}
