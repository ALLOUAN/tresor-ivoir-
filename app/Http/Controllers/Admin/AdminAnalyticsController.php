<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleAnalytic;
use App\Models\Provider;
use App\Models\ProviderAnalytic;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request): View
    {
        $hasArticleAnalytics = Schema::hasTable('article_analytics');
        $hasProviderAnalytics = Schema::hasTable('provider_analytics');

        $to = Carbon::parse($request->query('to', now()->toDateString()))->endOfDay();
        $from = Carbon::parse($request->query('from', now()->subDays(30)->toDateString()))->startOfDay();

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $articleTotals = collect();
        $articleKpis = ['views' => 0, 'shares' => 0, 'visitors' => 0];
        if ($hasArticleAnalytics) {
            $articleTotals = ArticleAnalytic::query()
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('article_id, SUM(views_count) as total_views, SUM(shares_count) as total_shares, SUM(unique_visitors) as total_visitors')
                ->groupBy('article_id')
                ->orderByDesc('total_views')
                ->limit(20)
                ->get();

            $articleKpis = [
                'views' => (int) ArticleAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('views_count'),
                'shares' => (int) ArticleAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('shares_count'),
                'visitors' => (int) ArticleAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('unique_visitors'),
            ];
        }

        $articleTitles = collect();
        if ($articleTotals->isNotEmpty()) {
            $articleTitles = Article::query()
                ->whereIn('id', $articleTotals->pluck('article_id'))
                ->pluck('title_fr', 'id');
        }

        $providerTotals = collect();
        $providerKpis = [
            'views' => 0,
            'clicks_phone' => 0,
            'clicks_website' => 0,
            'search_appearances' => 0,
        ];
        if ($hasProviderAnalytics) {
            $providerTotals = ProviderAnalytic::query()
                ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                ->selectRaw('provider_id, SUM(views_count) as total_views, SUM(clicks_phone) as total_phone, SUM(clicks_website) as total_site, SUM(search_appearances) as total_search')
                ->groupBy('provider_id')
                ->orderByDesc('total_views')
                ->limit(20)
                ->get();

            $providerKpis = [
                'views' => (int) ProviderAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('views_count'),
                'clicks_phone' => (int) ProviderAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('clicks_phone'),
                'clicks_website' => (int) ProviderAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('clicks_website'),
                'search_appearances' => (int) ProviderAnalytic::query()
                    ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
                    ->sum('search_appearances'),
            ];
        }

        $providerNames = collect();
        if ($providerTotals->isNotEmpty()) {
            $providerNames = Provider::query()
                ->whereIn('id', $providerTotals->pluck('provider_id'))
                ->pluck('name', 'id');
        }

        return view('admin.analytics.index', compact(
            'from',
            'to',
            'hasArticleAnalytics',
            'hasProviderAnalytics',
            'articleTotals',
            'articleTitles',
            'articleKpis',
            'providerTotals',
            'providerNames',
            'providerKpis',
        ));
    }
}
