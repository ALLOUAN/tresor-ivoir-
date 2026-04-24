<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->get('status');

        $query = Article::with(['category'])
            ->latest();

        if (! $user->isAdmin()) {
            $query->where('author_id', $user->id);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $articles = $query->paginate(15)->withQueryString();

        $counts = [
            'all' => $this->baseQuery()->count(),
            'draft' => $this->baseQuery()->where('status', 'draft')->count(),
            'review' => $this->baseQuery()->where('status', 'review')->count(),
            'published' => $this->baseQuery()->where('status', 'published')->count(),
            'archived' => $this->baseQuery()->where('status', 'archived')->count(),
        ];

        return view('editor.articles.index', compact('articles', 'counts', 'status'));
    }

    public function create()
    {
        $categories = ArticleCategory::where('is_active', true)->orderBy('sort_order')->get();
        $tags = Tag::whereIn('type', ['article', 'shared'])->orderBy('name_fr')->get();

        return view('editor.articles.create', compact('categories', 'tags'));
    }

    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data['author_id'] = Auth::id();
        $data['word_count'] = $data['content_fr'] ? str_word_count(strip_tags($data['content_fr'])) : 0;
        if (empty($data['reading_time']) && $data['word_count']) {
            $data['reading_time'] = max(1, (int) round($data['word_count'] / 200));
        }

        $article = Article::create($data);

        if ($tags) {
            $article->tags()->sync($tags);
        }

        $msg = match ($article->status) {
            'review' => 'Article soumis pour révision.',
            'published' => 'Article publié avec succès.',
            default => 'Brouillon enregistré.',
        };

        return redirect()->route('editor.articles.index')
            ->with('success', $msg);
    }

    public function edit(Article $article)
    {
        $this->authorizeEdit($article);

        $categories = ArticleCategory::where('is_active', true)->orderBy('sort_order')->get();
        $tags = Tag::whereIn('type', ['article', 'shared'])->orderBy('name_fr')->get();
        $selectedTags = $article->tags->pluck('id')->toArray();

        return view('editor.articles.edit', compact('article', 'categories', 'tags', 'selectedTags'));
    }

    public function update(StoreArticleRequest $request, Article $article)
    {
        $this->authorizeEdit($article);

        $data = $request->validated();
        $tags = $data['tags'] ?? [];
        unset($data['tags']);

        $data['word_count'] = $data['content_fr'] ? str_word_count(strip_tags($data['content_fr'])) : 0;
        if (empty($data['reading_time']) && $data['word_count']) {
            $data['reading_time'] = max(1, (int) round($data['word_count'] / 200));
        }

        $article->update($data);
        $article->tags()->sync($tags);

        return redirect()->route('editor.articles.index')
            ->with('success', 'Article mis à jour.');
    }

    public function destroy(Article $article)
    {
        $this->authorizeEdit($article);
        $article->delete();

        return back()->with('success', 'Article supprimé.');
    }

    public function updateStatus(Request $request, Article $article)
    {
        $request->validate(['status' => 'required|in:draft,review,published,archived']);
        $this->authorizeEdit($article);

        $data = ['status' => $request->status];

        if ($request->status === 'published' && ! $article->published_at) {
            $data['published_at'] = now();
        }

        $article->update($data);

        return back()->with('success', 'Statut mis à jour.');
    }

    private function baseQuery()
    {
        $user = Auth::user();

        return $user->isAdmin()
            ? Article::query()
            : Article::where('author_id', $user->id);
    }

    private function authorizeEdit(Article $article): void
    {
        $user = Auth::user();
        if (! $user->isAdmin() && $article->author_id !== $user->id) {
            abort(403, 'Vous ne pouvez pas modifier cet article.');
        }
    }
}
