<?php

namespace App\Http\Controllers\Provider;

use App\Http\Controllers\Controller;
use App\Models\Provider;
use App\Models\ProviderAnalytic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProviderAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $period = $request->integer('period', 30);
        $period = in_array($period, [7, 30, 90]) ? $period : 30;

        $provider = Provider::where('user_id', Auth::id())->first();

        $totals      = $this->emptyTotals();
        $prevTotals  = $this->emptyTotals();
        $chartDates  = [];
        $chartViews  = [];
        $chartClicks = [];

        if ($provider) {
            $rows = ProviderAnalytic::where('provider_id', $provider->id)
                ->where('date', '>=', now()->subDays($period - 1)->toDateString())
                ->orderBy('date')
                ->get();

            $totals = [
                'views'              => $rows->sum('views_count'),
                'clicks_phone'       => $rows->sum('clicks_phone'),
                'clicks_website'     => $rows->sum('clicks_website'),
                'clicks_direction'   => $rows->sum('clicks_direction'),
                'new_reviews'        => $rows->sum('new_reviews'),
                'search_appearances' => $rows->sum('search_appearances'),
            ];

            $prevRows = ProviderAnalytic::where('provider_id', $provider->id)
                ->where('date', '>=', now()->subDays($period * 2 - 1)->toDateString())
                ->where('date', '<', now()->subDays($period - 1)->toDateString())
                ->get();

            $prevTotals = [
                'views'          => $prevRows->sum('views_count'),
                'clicks_phone'   => $prevRows->sum('clicks_phone'),
                'clicks_website' => $prevRows->sum('clicks_website'),
            ];

            $chartDates  = $rows->pluck('date')->map(fn ($d) => $d->format('d/m'))->values()->all();
            $chartViews  = $rows->pluck('views_count')->values()->all();
            $chartClicks = $rows->map(fn ($r) => $r->clicks_phone + $r->clicks_website + $r->clicks_direction)->values()->all();
        }

        return view('provider.analytics.index', compact(
            'provider', 'totals', 'prevTotals', 'chartDates', 'chartViews', 'chartClicks', 'period'
        ));
    }

    private function emptyTotals(): array
    {
        return [
            'views' => 0, 'clicks_phone' => 0, 'clicks_website' => 0,
            'clicks_direction' => 0, 'new_reviews' => 0, 'search_appearances' => 0,
        ];
    }
}
