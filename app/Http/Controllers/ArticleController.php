<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\ArticleComment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class ArticleController extends Controller
{
    public function index()
    {
        $category_slug = request('categorie');
        $search = request('q');
        $tag_slug = request('tag');
        $articleWith = ['category', 'author', 'tags'];
        if (Schema::hasTable('article_uploader')) {
            $articleWith[] = 'uploaders';
        }

        $query = Article::where('status', 'published')
            ->where('published_at', '<=', now())
            ->with($articleWith)
            ->latest('published_at');

        if ($category_slug) {
            $query->whereHas('category', fn ($q) => $q->where('slug', $category_slug));
        }

        if ($tag_slug) {
            $query->whereHas('tags', fn ($q) => $q->where('slug', $tag_slug));
        }

        if ($search) {
            $query->where(fn ($q) => $q
                ->where('title_fr', 'like', "%{$search}%")
                ->orWhere('excerpt_fr', 'like', "%{$search}%")
            );
        }

        $articles = $query->paginate(12)->withQueryString();
        $categories = ArticleCategory::where('is_active', true)->orderBy('sort_order')->get();
        $featured = Article::where('status', 'published')
            ->where('is_featured', true)
            ->with(array_values(array_intersect($articleWith, ['category', 'author', 'uploaders'])))
            ->latest('published_at')
            ->take(3)
            ->get();

        $active_category = $category_slug
            ? $categories->firstWhere('slug', $category_slug)
            : null;

        return view('articles.index', compact(
            'articles', 'categories', 'featured', 'active_category', 'search', 'tag_slug'
        ));
    }

    public function show(string $slug)
    {
        $articleWith = ['category', 'author', 'tags', 'media'];
        if (Schema::hasTable('article_uploader')) {
            $articleWith[] = 'uploaders';
        }

        $article = Article::where('slug_fr', $slug)
            ->where('status', 'published')
            ->with($articleWith)
            ->firstOrFail();

        $article->increment('views_count');

        $related = Article::where('status', 'published')
            ->where('category_id', $article->category_id)
            ->where('id', '!=', $article->id)
            ->with(array_values(array_intersect($articleWith, ['category', 'author', 'uploaders'])))
            ->latest('published_at')
            ->take(3)
            ->get();

        $isFavorited = Auth::check() && Auth::user()->role === 'visitor'
            ? Auth::user()->favorites()
                ->where('favoritable_type', Article::class)
                ->where('favoritable_id', $article->id)
                ->exists()
            : false;

        $comments = $article->comments()
            ->where('is_approved', true)
            ->with('user')
            ->latest()
            ->get();

        return view('articles.show', compact('article', 'related', 'isFavorited', 'comments'));
    }
}
