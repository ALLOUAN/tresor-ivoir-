<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class EditorDashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $stats = [
            'my_total' => Article::where('author_id', $userId)->count(),
            'my_published' => Article::where('author_id', $userId)->where('status', 'published')->count(),
            'my_drafts' => Article::where('author_id', $userId)->where('status', 'draft')->count(),
            'global_review' => Article::where('status', 'review')->count(),
            'upcoming_events' => Event::where('status', 'published')->where('starts_at', '>', now())->count(),
            'total_views' => Article::where('author_id', $userId)->sum('views_count'),
        ];

        $my_articles = Article::where('author_id', $userId)
            ->with('category')
            ->latest()
            ->take(6)
            ->get();

        $pending_articles = Article::where('status', 'review')
            ->with('author', 'category')
            ->latest()
            ->take(5)
            ->get();

        $upcoming_events = Event::where('status', 'published')
            ->where('starts_at', '>', now())
            ->with('category')
            ->orderBy('starts_at')
            ->take(5)
            ->get();

        return view('dashboards.editor', compact(
            'stats',
            'my_articles',
            'pending_articles',
            'upcoming_events',
        ));
    }
}
