<?php

namespace App\Http\Controllers;

use App\Models\TouristCity;
use App\Models\TravelAgency;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fiche riche « Agences de Voyages & Tours » — même patron que LeisureVenueController,
 * sans réservation/paiement : fiche de présentation + les circuits du prestataire
 * (via TourPackage), avec contact direct plutôt qu'un paiement en ligne.
 */
class TravelAgencyController extends Controller
{
    public function index(Request $request): View
    {
        $cityId = $request->get('ville');
        $sort = $request->get('tri', 'pertinence');

        $query = TravelAgency::active()->with(['city', 'provider']);

        if ($cityId) {
            $query->forCity((int) $cityId);
        }

        $agencies = (match ($sort) {
            'recent' => $query->orderByDesc('created_at'),
            'popularite' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_featured'),
        })->get();

        $cities = $cityId
            ? TouristCity::where('id', $cityId)->get()
            : TouristCity::active()->orderBy('name')->get();

        return view('travel-agencies.index', [
            'agencies' => $agencies,
            'cities' => $cities,
            'cityId' => $cityId,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug): View
    {
        $agency = TravelAgency::active()
            ->where('slug', $slug)
            ->with(['city', 'provider', 'media'])
            ->firstOrFail();

        $agency->incrementViews();

        $toursByCategory = collect();
        if ($agency->provider) {
            $toursByCategory = $agency->provider->tourPackages()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($tour) => $tour->category?->name_fr ?? 'Autres');
        }

        $reviews = $agency->provider
            ? $agency->provider->approvedReviews()->latest()->take(5)->get()
            : collect();

        $related = TravelAgency::active()
            ->where('id', '!=', $agency->id)
            ->where('city_id', $agency->city_id)
            ->limit(3)
            ->get();

        return view('travel-agencies.show', compact('agency', 'toursByCategory', 'reviews', 'related'));
    }

    /**
     * Page dédiée listant tous les circuits proposés — seule source d'affichage de la
     * liste illustrée, afin de ne pas dupliquer ce design sur la fiche générale.
     */
    public function tours(string $slug): View
    {
        $agency = TravelAgency::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $toursByCategory = collect();
        if ($agency->provider) {
            $toursByCategory = $agency->provider->tourPackages()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($tour) => $tour->category?->name_fr ?? 'Autres');
        }

        return view('travel-agencies.tours', compact('agency', 'toursByCategory'));
    }
}
