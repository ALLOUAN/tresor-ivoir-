<?php

namespace App\Http\Controllers;

use App\Models\Bundle;
use App\Models\TouristCity;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Offres groupées : liste publique et fiche détaillée (réservation via les demandes). */
class BundleController extends Controller
{
    public function index(Request $request): View
    {
        $city = (string) $request->query('ville', '');

        $bundles = Bundle::active()
            ->with('city')
            ->when($city !== '', fn ($q) => $q->whereHas('city', fn ($c) => $c->where('slug', $city)))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('price_xof')
            ->get();

        $cities = TouristCity::whereIn('id', Bundle::active()->whereNotNull('city_id')->pluck('city_id'))->orderBy('name')->get();

        return view('bundles.index', compact('bundles', 'cities', 'city'));
    }

    public function show(Bundle $bundle): View
    {
        abort_unless($bundle->is_active, 404);
        $bundle->load(['city', 'provider']);
        $items = $bundle->includedItems();
        $others = Bundle::active()->whereKeyNot($bundle->id)->orderByDesc('is_featured')->limit(3)->get();

        return view('bundles.show', compact('bundle', 'items', 'others'));
    }
}
