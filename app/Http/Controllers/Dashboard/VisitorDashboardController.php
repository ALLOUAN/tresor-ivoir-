<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Event;
use App\Models\NewsletterSubscriber;
use App\Models\Review;
use Illuminate\Support\Facades\Auth;

class VisitorDashboardController extends Controller
{
    public function index()
    {
        $featured_articles = Article::where('status', 'published')
            ->where('is_featured', true)
            ->with('category', 'author')
            ->latest('published_at')
            ->take(4)
            ->get();

        $upcoming_events = Event::where('status', 'published')
            ->where('starts_at', '>', now())
            ->with('category')
            ->orderBy('starts_at')
            ->take(4)
            ->get();

        $user = Auth::user();
        $newsletter = NewsletterSubscriber::query()
            ->where('status', 'active')
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhereRaw('LOWER(email) = ?', [mb_strtolower((string) $user->email)]);
            })
            ->first();

        $my_reviews = Review::where('user_id', Auth::id())
            ->with('provider')
            ->latest()
            ->take(3)
            ->get();

        return view('dashboards.visitor', compact(
            'featured_articles',
            'upcoming_events',
            'newsletter',
            'my_reviews',
        ));
    }
}
