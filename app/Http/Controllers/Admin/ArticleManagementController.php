<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleManagementController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $search = $request->get('q');
        $category = $request->get('category');

        $query = Article::with(['author', 'category'])->latest();

        if ($status) {
            $query->where('status', $status);
        }
        if ($category) {
            $query->where('category_id', $category);
        }
        if ($search) {
            $query->where(fn ($q) => $q
                ->where('title_fr', 'like', "%{$search}%")
                ->orWhereHas('author', fn ($q2) => $q2->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%"))
            );
        }

        $articles = $query->paginate(20)->withQueryString();
        $categories = ArticleCategory::orderBy('sort_order')->get();

        $counts = [
            'all' => Article::count(),
            'draft' => Article::where('status', 'draft')->count(),
            'review' => Article::where('status', 'review')->count(),
            'published' => Article::where('status', 'published')->count(),
            'archived' => Article::where('status', 'archived')->count(),
        ];

        return view('admin.articles.index', compact('articles', 'categories', 'counts', 'status', 'search', 'category'));
    }

    public function publish(Article $article)
    {
        $article->update([
            'status' => 'published',
            'published_at' => $article->published_at ?? now(),
        ]);

        return back()->with('success', "\"$article->title_fr\" est maintenant publié.");
    }

    public function update(Request $request, Article $article)
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:article_categories,id'],
            'title_fr' => ['required', 'string', 'max:255'],
            'excerpt_fr' => ['nullable', 'string', 'max:800'],
            'content_fr' => ['nullable', 'string'],
            'cover_url' => ['nullable', 'url', 'max:500'],
            'reading_time' => ['nullable', 'integer', 'min:1', 'max:240'],
            'status' => ['required', 'in:draft,review,published,archived'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['nullable', 'boolean'],
            'is_sponsored' => ['nullable', 'boolean'],
        ]);

        $data['is_featured'] = $request->boolean('is_featured');
        $data['is_sponsored'] = $request->boolean('is_sponsored');

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $article->published_at ?? now();
        }

        if ($data['status'] !== 'published' && $data['status'] !== 'archived') {
            $data['published_at'] = null;
        }

        $article->update($data);

        return back()->with('success', 'Article modifié avec succès.');
    }

    public function reject(Request $request, Article $article)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);
        $article->update(['status' => 'draft']);

        return back()->with('success', 'Article renvoyé en brouillon.');
    }

    public function archive(Article $article)
    {
        $article->update(['status' => 'archived']);

        return back()->with('success', 'Article archivé.');
    }

    public function destroy(Article $article)
    {
        $article->delete();

        return back()->with('success', 'Article supprimé.');
    }

    public function categories()
    {
        $categories = ArticleCategory::withCount('articles')->orderBy('sort_order')->get();

        return view('admin.articles.categories', compact('categories'));
    }

    public function storeCategory(Request $request)
    {
        $data = $request->validate([
            'name_fr' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'slug' => 'required|string|max:100|unique:article_categories,slug',
            'color_hex' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        ArticleCategory::create($data);

        return back()->with('success', 'Rubrique créée.');
    }

    public function updateCategory(Request $request, ArticleCategory $category)
    {
        $data = $request->validate([
            'name_fr' => 'required|string|max:100',
            'name_en' => 'nullable|string|max:100',
            'color_hex' => 'nullable|string|max:7',
            'icon' => 'nullable|string|max:50',
            'sort_order' => 'nullable|integer',
            'is_active' => 'boolean',
        ]);

        $data['is_active'] = $request->boolean('is_active');
        $category->update($data);

        return back()->with('success', 'Rubrique mise à jour.');
    }
}
