<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\PrestationBanner;
use App\Models\PrestationItem;
use App\Models\PrestationSetting;
use App\Models\Provider;
use App\Models\TouristCity;
use App\Models\TouristSite;
use Illuminate\View\View;

class PrestationController extends Controller
{
    public function show(): View
    {
        $settings = PrestationSetting::singleton();
        $banners = PrestationBanner::query()->active()->ordered()->get();
        $items = PrestationItem::query()->active()->ordered()->get();

        // Affichage : chiffres réels de la plateforme (preuve de portée pour les professionnels).
        $stats = [
            'providers' => Provider::where('status', 'active')->count(),
            'cities' => TouristCity::where('is_active', 1)->count(),
            'sites' => TouristSite::where('is_active', 1)->count(),
            'articles' => Article::where('status', 'published')->where('published_at', '<=', now())->count(),
        ];

        return view('prestations.show', compact('settings', 'banners', 'items', 'stats'));
    }
}
