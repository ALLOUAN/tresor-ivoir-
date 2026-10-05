<?php

namespace App\Http\Controllers;

use App\Models\Artwork;
use App\Models\ArtworkCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ArtworkPublicController extends Controller
{
    private const BUDGET_BRACKETS = [
        'low' => [null, 50000],
        'mid' => [50000, 200000],
        'high' => [200000, 500000],
        'luxury' => [500000, null],
    ];

    public function index(Request $request): View
    {
        $search = trim((string) $request->get('q', ''));
        $categoryId = $request->get('categorie');
        $budget = trim((string) $request->get('budget', ''));
        $sort = $request->get('tri', 'pertinence');

        $query = Artwork::published()->with(['provider', 'category']);

        if ($search !== '') {
            $query->where('title', 'like', "%{$search}%");
        }

        if ($categoryId) {
            $query->where('category_id', (int) $categoryId);
        }

        if ($budget !== '' && isset(self::BUDGET_BRACKETS[$budget])) {
            [$min, $max] = self::BUDGET_BRACKETS[$budget];
            if ($min !== null) {
                $query->where('price_xof', '>=', $min);
            }
            if ($max !== null) {
                $query->where('price_xof', '<', $max);
            }
        }

        match ($sort) {
            'prix_asc' => $query->orderBy('price_xof'),
            'prix_desc' => $query->orderByDesc('price_xof'),
            'populaire' => $query->orderByDesc('views_count'),
            default => $query->latest(),
        };

        $artworks = $query->paginate(12)->withQueryString();
        $categories = ArtworkCategory::where('is_active', true)->orderBy('sort_order')->get();

        return view('art-marketplace.index', compact('artworks', 'categories', 'search', 'categoryId', 'budget', 'sort'));
    }

    public function show(Artwork $artwork): View
    {
        abort_unless($artwork->status === Artwork::STATUS_PUBLISHED || $artwork->status === Artwork::STATUS_SOLD, 404);

        $artwork->load(['provider', 'category']);
        $artwork->increment('views_count');

        return view('art-marketplace.show', compact('artwork'));
    }
}
