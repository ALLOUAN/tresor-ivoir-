<?php

namespace App\Http\Controllers;

use App\Models\Comic;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** « Les Astuces de Mian » — liste des bandes dessinées et liseuse. */
class ComicController extends Controller
{
    public function index(Request $request): View
    {
        $category = $request->string('rubrique')->toString();
        $category = array_key_exists($category, Comic::CATEGORIES) ? $category : null;

        $all = Comic::published()->ordered()->get();
        $comics = $category ? $all->where('category', $category)->values() : $all;

        return view('comics.index', [
            'comics' => $comics,
            'featured' => $category ? null : ($all->firstWhere('is_featured', true) ?? $all->first()),
            'category' => $category,
            'counts' => $all->countBy('category'),
            'total' => $all->count(),
        ]);
    }

    public function show(Request $request, Comic $comic): View
    {
        abort_unless($comic->is_published, 404);

        // Lecture comptée une seule fois par session et par BD.
        $seen = (array) $request->session()->get('comics_seen', []);
        if (! in_array($comic->id, $seen, true)) {
            $comic->increment('views_count');
            $seen[] = $comic->id;
            $request->session()->put('comics_seen', array_slice($seen, -300));
        }

        $others = Comic::published()->whereKeyNot($comic->id)->ordered()->get();
        $next = $others->firstWhere('category', $comic->category) ?? $others->first();

        return view('comics.show', [
            'comic' => $comic,
            'next' => $next,
            'others' => $others->take(4),
        ]);
    }
}
