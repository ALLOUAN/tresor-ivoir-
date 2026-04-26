<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Event;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SearchController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->get('q', ''));

        if (mb_strlen($q) < 2) {
            return view('search.index', [
                'q'         => $q,
                'articles'  => collect(),
                'events'    => collect(),
                'providers' => collect(),
                'total'     => 0,
            ]);
        }

        $like = "%{$q}%";

        $articles = Article::where('status', 'published')
            ->where('published_at', '<=', now())
            ->where(fn ($query) => $query
                ->where('title_fr', 'like', $like)
                ->orWhere('excerpt_fr', 'like', $like)
                ->orWhere('content_fr', 'like', $like))
            ->with(['category', 'author'])
            ->latest('published_at')
            ->limit(8)
            ->get();

        $events = Event::where('status', 'published')
            ->where(fn ($query) => $query
                ->where('title_fr', 'like', $like)
                ->orWhere('description_fr', 'like', $like)
                ->orWhere('city', 'like', $like))
            ->with('category')
            ->orderBy('starts_at')
            ->limit(6)
            ->get();

        $providers = Provider::where('status', 'active')
            ->where(fn ($query) => $query
                ->where('name', 'like', $like)
                ->orWhere('description_fr', 'like', $like)
                ->orWhere('city', 'like', $like))
            ->with('category')
            ->limit(6)
            ->get();

        $total = $articles->count() + $events->count() + $providers->count();

        return view('search.index', compact('q', 'articles', 'events', 'providers', 'total'));
    }
}
