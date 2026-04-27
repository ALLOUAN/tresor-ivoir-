<?php

namespace App\Http\Controllers\Editor;

use App\Http\Controllers\Controller;
use App\Http\Requests\AutosaveArticleRequest;
use App\Http\Requests\StoreArticleRequest;
use App\Models\Article;
use App\Models\ArticleCategory;
use App\Models\Media;
use App\Models\Provider;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
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
        $uploaders = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'editor', 'provider'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'role']);

        return view('editor.articles.create', compact('categories', 'tags', 'sponsors', 'uploaders'));
    }

    public function store(StoreArticleRequest $request)
    {
        $data = $request->validated();
        $tags = $data['tags'] ?? [];
        $uploaderIds = $data['uploader_ids'] ?? [];
        $articleImages = $request->file('article_images', []);
        unset($data['cover_image']);
        unset($data['article_images']);
        unset($data['publication_mode']);
        unset($data['tags']);
        unset($data['uploader_ids']);

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
        $this->syncUploaders($article, $uploaderIds);
        if (is_array($articleImages) && $articleImages !== []) {
            $this->storeArticleGalleryImages($article, $articleImages);
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
        $uploaders = User::query()
            ->where('is_active', true)
            ->whereIn('role', ['admin', 'editor', 'provider'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'role']);
        $selectedUploaderIds = Schema::hasTable('article_uploader')
            ? $article->uploaders()->pluck('users.id')->toArray()
            : [];
        $galleryImages = $article->media()
            ->where('type', 'image')
            ->where('collection', 'article-gallery')
            ->orderBy('sort_order')
            ->orderByDesc('id')
            ->get();

        return view('editor.articles.create', compact('article', 'categories', 'tags', 'selectedTags', 'sponsors', 'uploaders', 'selectedUploaderIds', 'galleryImages'));
    }

    public function update(StoreArticleRequest $request, Article $article)
    {
        $this->authorizeEdit($article);

        $data = $request->validated();
        $tags = $data['tags'] ?? [];
        $uploaderIds = $data['uploader_ids'] ?? [];
        $removeMediaIds = $data['remove_media_ids'] ?? [];
        $articleImages = $request->file('article_images', []);
        unset($data['cover_image']);
        unset($data['article_images']);
        unset($data['publication_mode']);
        unset($data['tags']);
        unset($data['uploader_ids']);
        unset($data['remove_media_ids']);

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
        $this->syncUploaders($article, $uploaderIds);
        $this->deleteArticleGalleryImages($article, $removeMediaIds);
        if (is_array($articleImages) && $articleImages !== []) {
            $this->storeArticleGalleryImages($article, $articleImages);
        }

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

    /**
     * @param array<int, UploadedFile> $images
     */
    private function storeArticleGalleryImages(Article $article, array $images): void
    {
        $existingSortOrder = (int) $article->media()->max('sort_order');
        $sort = max(0, $existingSortOrder);

        foreach ($images as $image) {
            if (! $image instanceof UploadedFile || ! str_starts_with((string) $image->getMimeType(), 'image/')) {
                continue;
            }

            $path = $image->store('articles/gallery', 'public');
            $absolutePath = Storage::disk('public')->path($path);
            $width = null;
            $height = null;
            $dimensions = @getimagesize($absolutePath);
            if (is_array($dimensions)) {
                $width = isset($dimensions[0]) ? (int) $dimensions[0] : null;
                $height = isset($dimensions[1]) ? (int) $dimensions[1] : null;
            }

            $sort++;
            $article->media()->create([
                'collection' => 'article-gallery',
                'type' => 'image',
                'mime_type' => (string) $image->getMimeType(),
                'original_name' => (string) $image->getClientOriginalName(),
                'file_path' => $path,
                'url' => '/storage/'.$path,
                'thumb_url' => null,
                'size_bytes' => (int) $image->getSize(),
                'width' => $width,
                'height' => $height,
                'duration_sec' => null,
                'alt_text' => $article->title_fr,
                'caption' => null,
                'sort_order' => $sort,
                'uploaded_by' => (int) Auth::id(),
            ]);
        }
    }

    /**
     * @param array<int, int|string> $mediaIds
     */
    private function deleteArticleGalleryImages(Article $article, array $mediaIds): void
    {
        $ids = collect($mediaIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($ids->isEmpty()) {
            return;
        }

        $mediaItems = $article->media()
            ->where('collection', 'article-gallery')
            ->where('type', 'image')
            ->whereIn('id', $ids->all())
            ->get();

        foreach ($mediaItems as $media) {
            if (! empty($media->url) && str_starts_with((string) $media->url, '/storage/')) {
                $this->deleteStoredPublicFile((string) $media->url);
            }

            $media->delete();
        }
    }

    /**
     * @param array<int, int|string> $uploaderIds
     */
    private function syncUploaders(Article $article, array $uploaderIds): void
    {
        if (! Schema::hasTable('article_uploader')) {
            return;
        }

        $ids = collect($uploaderIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if (! $ids->contains((int) $article->author_id)) {
            $ids->push((int) $article->author_id);
        }

        $article->uploaders()->sync($ids->all());
    }
}
