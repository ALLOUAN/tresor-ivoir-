<?php

namespace App\Http\Controllers;

use App\Models\ProviderCategory;
use App\Support\HomeSectorShowcase;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

/**
 * Page « Tous nos établissements » : regroupe, secteur par secteur, les
 * établissements des 6 pages de listing (résidences-hôtels, restaurants,
 * expériences touristiques, agences de voyages, loisirs & culture, transports),
 * avec les mêmes cartes et les mêmes liens que sur chacune de ces pages.
 */
class EstablishmentController extends Controller
{
    public function index(): View
    {
        $sectors = Schema::hasTable('provider_categories')
            ? ProviderCategory::where('is_active', true)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get()
            : collect();

        $showcase = HomeSectorShowcase::build($sectors, null);
        $totalCount = collect($showcase)->sum(fn (array $s) => $s['items']->count());

        return view('establishments.index', compact('sectors', 'showcase', 'totalCount'));
    }
}
