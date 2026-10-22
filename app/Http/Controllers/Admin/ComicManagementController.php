<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Comic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** « Les Astuces de Mian » — gestion des bandes dessinées (couverture, planches ordonnées, publication). */
class ComicManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.comics.index', [
            'comics' => Comic::ordered()->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.comics.form', ['comic' => new Comic(['category' => 'educatif', 'is_published' => true])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $comic = new Comic;
        $this->fill($comic, $request);

        return redirect()->route('admin.comics.index')->with('success', __('Bande dessinée ajoutée.'));
    }

    public function edit(Comic $comic): View
    {
        return view('admin.comics.form', ['comic' => $comic]);
    }

    public function update(Request $request, Comic $comic): RedirectResponse
    {
        $this->fill($comic, $request);

        return redirect()->route('admin.comics.index')->with('success', __('Bande dessinée enregistrée.'));
    }

    public function destroy(Comic $comic): RedirectResponse
    {
        $comic->delete();
        Storage::disk('public')->deleteDirectory('comics/'.$comic->id);
        Cache::forget('mian_welcome_v2');

        return back()->with('success', __('Bande dessinée supprimée.'));
    }

    private function fill(Comic $comic, Request $request): void
    {
        $image = ['image', 'mimes:'.implode(',', Comic::IMAGE_MIMES), 'max:'.Comic::IMAGE_MAX_KB];
        $current = array_values(array_filter((array) $comic->pages));

        $data = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'summary' => ['nullable', 'string', 'max:2000'],
            'category' => ['required', Rule::in(array_keys(Comic::CATEGORIES))],
            'age_label' => ['nullable', 'string', 'max:40'],
            'sort_order' => ['nullable', 'integer', 'min:-9999', 'max:9999'],
            'cover' => ['nullable', ...$image],
            'remove_cover' => ['nullable', 'boolean'],
            'pages_keep' => ['nullable', 'array'],
            'pages_keep.*' => ['string', Rule::in($current)],
            'new_pages' => ['nullable', 'array', 'max:'.Comic::MAX_PAGES],
            'new_pages.*' => $image,
        ], [], [
            'title' => 'titre', 'cover' => 'couverture', 'new_pages.*' => 'planche', 'new_pages' => 'planches',
        ]);

        $wasPublished = $comic->exists && $comic->is_published;
        $comic->fill([
            'title' => $data['title'],
            'summary' => $data['summary'] ?? null,
            'category' => $data['category'],
            'age_label' => $data['age_label'] ?? null,
            'sort_order' => (int) ($data['sort_order'] ?? 0),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ]);
        if ($comic->exists && $comic->isDirty('title')) {
            $comic->slug = Comic::uniqueSlug($comic->title, $comic->id);
        }
        if ($comic->is_published && ! $wasPublished && ! $comic->published_at) {
            $comic->published_at = now();
        }
        $comic->save(); // l'identifiant sert de dossier de stockage

        $disk = Storage::disk('public');
        $dir = 'comics/'.$comic->id;

        if ($request->boolean('remove_cover') && $comic->cover_path) {
            $disk->delete($comic->cover_path);
            $comic->cover_path = null;
        }
        if ($request->hasFile('cover')) {
            if ($comic->cover_path) {
                $disk->delete($comic->cover_path);
            }
            $comic->cover_path = $request->file('cover')->store($dir, 'public');
        }

        // Planches gardées dans l'ordre choisi ; les autres sont supprimées du disque.
        $keep = array_values(array_unique((array) ($data['pages_keep'] ?? [])));
        foreach (array_diff($current, $keep) as $gone) {
            $disk->delete($gone);
        }
        foreach ((array) $request->file('new_pages', []) as $file) {
            $keep[] = $file->store($dir, 'public');
        }
        $comic->pages = array_slice($keep, 0, Comic::MAX_PAGES);
        $comic->save();
        Cache::forget('mian_welcome_v2'); // fenêtre de première visite
    }
}
