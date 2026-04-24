<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Event;
use App\Models\Payment;
use App\Models\Provider;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'users_today' => User::whereDate('created_at', today())->count(),
            'active_providers' => Provider::where('status', 'active')->count(),
            'pending_providers' => Provider::where('status', 'pending')->count(),
            'published_articles' => Article::where('status', 'published')->count(),
            'articles_review' => Article::where('status', 'review')->count(),
            'pending_reviews' => Review::where('status', 'pending')->count(),
            'active_subscriptions' => Subscription::where('status', 'active')->count(),
            'upcoming_events' => Event::where('status', 'published')->where('starts_at', '>', now())->count(),
            'monthly_revenue' => Payment::where('status', 'completed')
                ->whereNotNull('paid_at')
                ->whereMonth('paid_at', now()->month)
                ->whereYear('paid_at', now()->year)
                ->sum('amount'),
        ];

        $recent_users = User::latest()->take(6)->get();

        $pending_reviews = Review::with('provider', 'user')
            ->where('status', 'pending')
            ->latest()
            ->take(5)
            ->get();

        $providers_by_status = Provider::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $subscriptions_by_plan = Subscription::with('plan')
            ->where('status', 'active')
            ->get()
            ->groupBy('plan.code')
            ->map->count();

        return view('dashboards.admin', compact(
            'stats',
            'recent_users',
            'pending_reviews',
            'providers_by_status',
            'subscriptions_by_plan',
        ));
    }
}
