<?php

namespace App\Http\Controllers;

use App\Models\PrestationBanner;
use App\Models\PrestationItem;
use App\Models\PrestationSetting;
use Illuminate\View\View;

class PrestationController extends Controller
{
    public function show(): View
    {
        $settings = PrestationSetting::singleton();
        $banners = PrestationBanner::query()->active()->ordered()->get();
        $items = PrestationItem::query()->active()->ordered()->get();

        return view('prestations.show', compact('settings', 'banners', 'items'));
    }
}
