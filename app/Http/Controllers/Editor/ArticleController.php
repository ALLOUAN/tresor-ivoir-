<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutosaveArticleRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Provider;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

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
        $sponsors = Provider::where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('editor.articles.create', compact('categories', 'tags', 'sponsors'));
    }

    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $tags = $data['tags'] ?? [];
        unset($data['cover_image']);
        unset($data['publication_mode']);
        unset($data['tags']);

        if ($request->hasFile('cover_image')) {
            $data['cover_url'] = $this->storeArticleCover($request->file('cover_image'));
        }

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
        $sponsors = Provider::where('status', 'active')->orderBy('name')->get(['id', 'name']);
        $selectedTags = $article->tags->pluck('id')->toArray();

        return view('editor.articles.edit', compact('article', 'categories', 'tags', 'selectedTags', 'sponsors'));
    }

    public function update(StoreArticleRequest $request, Article $article)
    {
        $this->authorizeEdit($article);

        $data = $request->validated();
        $tags = $data['tags'] ?? [];
        unset($data['cover_image']);
        unset($data['publication_mode']);
        unset($data['tags']);

        if ($request->hasFile('cover_image')) {
            $this->deleteStoredPublicFile($article->cover_url);
            $data['cover_url'] = $this->storeArticleCover($request->file('cover_image'));
        }

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

    public function preview(Article $article): View
    {
        $this->authorizeEdit($article);
        $article->load(['category', 'author', 'tags']);

        return view('editor.articles.preview', compact('article'));
    }

    public function autosave(AutosaveArticleRequest $request, Article $article): JsonResponse
    {
        $this->authorizeEdit($article);

        $validated = $request->validated();
        $tags = $validated['tags'] ?? null;
        unset($validated['tags']);

        $patch = [];
        foreach ($validated as $key => $value) {
            if (! $request->exists($key)) {
                continue;
            }
            if (in_array($key, ['title_fr', 'title_en'], true) && is_string($value) && trim($value) === '') {
                continue;
            }
            $patch[$key] = $value;
        }

        if (isset($patch['slug_fr']) && trim((string) $patch['slug_fr']) === '') {
            unset($patch['slug_fr']);
        }
        if (isset($patch['slug_en']) && trim((string) $patch['slug_en']) === '') {
            unset($patch['slug_en']);
        }

        if (isset($patch['slug_fr'])) {
            $patch['slug_fr'] = $this->ensureUniqueArticleSlug((string) $patch['slug_fr'], 'slug_fr', $article->id);
        }
        if (isset($patch['slug_en'])) {
            $patch['slug_en'] = $this->ensureUniqueArticleSlug((string) $patch['slug_en'], 'slug_en', $article->id);
        }

        if ($patch !== []) {
            $article->fill($patch);
        }

        $contentForCount = (string) ($article->content_fr ?? '');
        $article->word_count = $contentForCount !== '' ? str_word_count(strip_tags($contentForCount)) : 0;
        if (! $article->reading_time && $article->word_count > 0) {
            $article->reading_time = max(1, (int) round($article->word_count / 200));
        }

        $article->save();

        if (is_array($tags)) {
            $article->tags()->sync($tags);
        }

        return response()->json([
            'ok' => true,
            'saved_at' => $article->fresh()->updated_at?->toIso8601String(),
        ]);
    }

    private function ensureUniqueArticleSlug(string $slug, string $column, int $ignoreId): string
    {
        $slug = trim(Str::slug($slug));
        if ($slug === '') {
            $slug = 'article-'.Str::lower(Str::random(6));
        }

        $base = $slug;
        $n = 1;
        while (Article::query()->where($column, $slug)->where('id', '!=', $ignoreId)->exists()) {
            $slug = $base.'-'.$n++;
        }

        return $slug;
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

    private function storeArticleCover(UploadedFile $file): string
    {
        $path = $file->store('articles/covers', 'public');

        return '/storage/'.$path;
    }

    private function deleteStoredPublicFile(?string $storedUrl): void
    {
        if (! $storedUrl || ! str_starts_with($storedUrl, '/storage/')) {
            return;
        }

        $relative = ltrim(substr($storedUrl, strlen('/storage/')), '/');
        if ($relative !== '') {
            Storage::disk('public')->delete($relative);
        }
    }
}
