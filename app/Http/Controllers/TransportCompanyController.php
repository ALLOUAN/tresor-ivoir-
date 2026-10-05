<?php

namespace App\Http\Controllers;

use App\Models\TouristCity;
use App\Models\TransportCompany;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Fiche riche « Transports & Mobilité » — même patron que LeisureVenueController,
 * sans réservation/paiement : fiche de présentation + les offres du prestataire
 * (via TransportOffer), avec contact direct plutôt qu'un paiement en ligne.
 */
class TransportCompanyController extends Controller
{
    public function index(Request $request): View
    {
        $cityId = $request->get('ville');
        $sort = $request->get('tri', 'pertinence');

        $query = TransportCompany::active()->with(['city', 'provider']);

        if ($cityId) {
            $query->forCity((int) $cityId);
        }

        $companies = (match ($sort) {
            'recent' => $query->orderByDesc('created_at'),
            'popularite' => $query->orderByDesc('views_count'),
            default => $query->orderByDesc('is_featured'),
        })->get();

        $cities = $cityId
            ? TouristCity::where('id', $cityId)->get()
            : TouristCity::active()->orderBy('name')->get();

        return view('transport-companies.index', [
            'companies' => $companies,
            'cities' => $cities,
            'cityId' => $cityId,
            'sort' => $sort,
        ]);
    }

    public function show(string $slug): View
    {
        $company = TransportCompany::active()
            ->where('slug', $slug)
            ->with(['city', 'provider', 'media'])
            ->firstOrFail();

        $company->incrementViews();

        $offersByCategory = collect();
        if ($company->provider) {
            $offersByCategory = $company->provider->transportOffers()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($offer) => $offer->category?->name_fr ?? 'Autres');
        }

        $reviews = $company->provider
            ? $company->provider->approvedReviews()->latest()->take(5)->get()
            : collect();

        $related = TransportCompany::active()
            ->where('id', '!=', $company->id)
            ->where('city_id', $company->city_id)
            ->limit(3)
            ->get();

        return view('transport-companies.show', compact('company', 'offersByCategory', 'reviews', 'related'));
    }

    /**
     * Page dédiée listant toutes les offres de transport — seule source d'affichage de la
     * liste illustrée, afin de ne pas dupliquer ce design sur la fiche générale.
     */
    public function offers(string $slug): View
    {
        $company = TransportCompany::active()
            ->where('slug', $slug)
            ->firstOrFail();

        $offersByCategory = collect();
        if ($company->provider) {
            $offersByCategory = $company->provider->transportOffers()
                ->where('is_available', true)
                ->with('category')
                ->orderBy('sort_order')
                ->get()
                ->groupBy(fn ($offer) => $offer->category?->name_fr ?? 'Autres');
        }

        return view('transport-companies.offers', compact('company', 'offersByCategory'));
    }
}
