<?php

namespace App\Http\Controllers;

use App\Models\CulturalDomain;
use App\Models\CulturalElement;
use App\Models\CulturalPeople;

class CulturalController extends Controller
{
    public function peoples()
    {
        $peoples = CulturalPeople::where('is_active', 1)
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $domains = CulturalDomain::whereNull('parent_id')
            ->where('is_active', 1)
            ->orderBy('sort_order')
            ->get();

        return view('cultural.peoples', compact('peoples', 'domains'));
    }

    public function people(string $slug)
    {
        $people = CulturalPeople::where('slug', $slug)->where('is_active', 1)->firstOrFail();

        $elements = CulturalElement::with('domain')
            ->where('is_active', 1)
            ->whereJsonContains('people_roles', ['people_id' => $people->id])
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();

        $domains = $elements->pluck('domain')->filter()->unique('id')->values();

        return view('cultural.people', compact('people', 'elements', 'domains'));
    }

    public function element(string $slug)
    {
        $element = CulturalElement::with(['domain', 'media'])
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->firstOrFail();

        $element->incrementViews();

        $peoples = $element->peoples;
        $cities  = $element->cities;

        $related = CulturalElement::with('domain')
            ->where('domain_id', $element->domain_id)
            ->where('id', '!=', $element->id)
            ->where('is_active', 1)
            ->limit(4)
            ->get();

        return view('cultural.element', compact('element', 'peoples', 'cities', 'related'));
    }
}
