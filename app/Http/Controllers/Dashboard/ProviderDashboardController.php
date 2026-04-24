<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Provider;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class ProviderDashboardController extends Controller
{
    public function index()
    {
        $provider = Provider::where('user_id', Auth::id())
            ->with(['category'])
            ->first();

        $subscription = $provider
            ? $provider->subscriptions()
                ->with('plan')
                ->where('status', 'active')
                ->latest()
                ->first()
            : null;

        $stats = [
            'views' => $provider?->views_count ?? 0,
            'clicks_phone' => $provider?->clicks_phone ?? 0,
            'clicks_website' => $provider?->clicks_website ?? 0,
            'rating_avg' => $provider?->rating_avg ?? 0,
            'rating_count' => $provider?->rating_count ?? 0,
        ];

        $recent_reviews = $provider
            ? Review::where('provider_id', $provider->id)
                ->where('status', 'approved')
                ->latest()
                ->take(5)
                ->get()
            : collect();

        $pending_reviews = $provider
            ? Review::where('provider_id', $provider->id)
                ->where('status', 'pending')
                ->count()
            : 0;

        $invoices = $provider
            ? Invoice::where('provider_id', $provider->id)
                ->latest('issued_at')
                ->take(5)
                ->get()
            : collect();

        return view('dashboards.provider', compact(
            'provider',
            'subscription',
            'stats',
            'recent_reviews',
            'pending_reviews',
            'invoices',
        ));
    }
}
