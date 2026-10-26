<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Film;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/** « Cinéma d'Ivoire » — gestion des fiches films, validation des propositions, mise en avant et contenus sponsorisés. */
class FilmManagementController extends Controller
{
    public function index(Request $request): View
    {
        $status = array_key_exists((string) $request->query('statut'), Film::STATUSES) ? (string) $request->query('statut') : null;
        $q = trim((string) $request->query('q', ''));

        return view('admin.films.index', [
            'films' => Film::query()
                ->when($status, fn ($w) => $w->where('status', $status))
                ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('title', 'like', "%{$q}%")->orWhere('director', 'like', "%{$q}%")))
                ->orderByRaw("status = 'pending' desc")->orderByDesc('updated_at')
                ->paginate(30)->withQueryString(),
            'counts' => Film::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status'),
            'status' => $status,
            'q' => $q,
        ]);
    }

    public function create(): View
    {
        return view('admin.films.form', ['film' => new Film(['kind' => 'documentaire', 'status' => 'draft', 'country' => "Côte d'Ivoire"])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $film = new Film;
        $this->fill($film, $request);

        return redirect()->route('admin.films.index')->with('success', __('Film « :t » enregistré.', ['t' => $film->title]));
    }

    public function edit(Film $film): View
    {
        return view('admin.films.form', ['film' => $film]);
    }

    public function update(Request $request, Film $film): RedirectResponse
    {
        $this->fill($film, $request);

        return redirect()->route('admin.films.index')->with('success', __('Film « :t » enregistré.', ['t' => $film->title]));
    }

    /** Validation rapide depuis la liste : publier ou refuser une proposition. */
    public function status(Request $request, Film $film): RedirectResponse
    {
        $data = $request->validate(['status' => ['required', Rule::in(['published', 'rejected', 'withdrawn', 'draft'])]]);
        if ($data['status'] === 'published' && ! $film->rights_confirmed) {
            return back()->with('error', __('Confirmez d\'abord les droits et autorisations dans la fiche avant de publier.'));
        }
        $film->status = $data['status'];
        if ($film->status === 'published' && ! $film->published_at) {
            $film->published_at = now();
        }
        $film->save();

        return back()->with('success', __('« :t » : :s.', ['t' => $film->title, 's' => mb_strtolower($film->statusLabel())]));
    }

    public function destroy(Film $film): RedirectResponse
    {
        Storage::disk('public')->deleteDirectory('films/'.$film->id);
        $film->delete();

        return back()->with('success', __('Film supprimé.'));
    }

    private function fill(Film $film, Request $request): void
    {
        $image = ['nullable', 'image', 'mimes:'.implode(',', Film::IMAGE_MIMES), 'max:'.Film::IMAGE_MAX_KB];
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'kind' => ['required', Rule::in(array_keys(Film::KINDS))],
            'genre' => ['nullable', 'string', 'max:60'],
            'tagline' => ['nullable', 'string', 'max:220'],
            'synopsis' => ['nullable', 'string', 'max:6000'],
            'director' => ['nullable', 'string', 'max:160'],
            'director_bio' => ['nullable', 'string', 'max:4000'],
            'cast' => ['nullable', 'string', 'max:3000'],
            'production' => ['nullable', 'string', 'max:220'],
            'country' => ['nullable', 'string', 'max:80'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 2)],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:900'],
            'language' => ['nullable', 'string', 'max:80'],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'watch_url' => ['nullable', 'url', 'max:500'],
            'watch_label' => ['nullable', 'string', 'max:80'],
            'distributor_contact' => ['nullable', 'string', 'max:220'],
            'awards' => ['nullable', 'string', 'max:3000'],
            'screenings_text' => ['nullable', 'string', 'max:5000'],
            'credits' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(array_keys(Film::STATUSES))],
            'review_note' => ['nullable', 'string', 'max:500'],
            'poster' => $image,
            'backdrop' => $image,
            'director_photo' => $image,
            'remove_poster' => ['nullable', 'boolean'],
            'remove_backdrop' => ['nullable', 'boolean'],
            'remove_director_photo' => ['nullable', 'boolean'],
        ], [], ['title' => 'titre', 'kind' => 'catégorie', 'poster' => 'affiche', 'backdrop' => 'image de fond', 'director_photo' => 'photo du réalisateur']);

        $rights = $request->boolean('rights_confirmed');
        if ($data['status'] === 'published' && ! $rights) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'rights_confirmed' => 'Un film ne peut être publié qu\'une fois les droits et autorisations confirmés.',
            ]);
        }

        $film->fill(collect($data)->except(['poster', 'backdrop', 'director_photo', 'remove_poster', 'remove_backdrop', 'remove_director_photo', 'screenings_text'])->all() + [
            'rights_confirmed' => $rights,
            'is_featured' => $request->boolean('is_featured'),
            'is_sponsored' => $request->boolean('is_sponsored'),
            'screenings' => $this->parseScreenings($data['screenings_text'] ?? ''),
        ]);
        if ($film->exists && $film->isDirty('title')) {
            $film->slug = Film::uniqueSlug($film->title, $film->id);
        }
        if ($film->status === 'published' && ! $film->published_at) {
            $film->published_at = now();
        }
        $film->save(); // l'identifiant sert de dossier de stockage

        $disk = Storage::disk('public');
        foreach (['poster' => 'poster_path', 'backdrop' => 'backdrop_path', 'director_photo' => 'director_photo_path'] as $field => $column) {
            if ($request->boolean('remove_'.$field) && $film->{$column}) {
                $disk->delete($film->{$column});
                $film->{$column} = null;
            }
            if ($request->hasFile($field)) {
                if ($film->{$column}) {
                    $disk->delete($film->{$column});
                }
                $film->{$column} = $request->file($field)->store('films/'.$film->id, 'public');
            }
        }
        $film->save();
    }

    /** Une projection par ligne : « 2026-11-15 19:00 | Lieu, ville | lien (facultatif) ». */
    private function parseScreenings(string $text): array
    {
        return collect(preg_split('/\R/', $text))
            ->map(fn ($line) => array_map('trim', explode('|', $line)))
            ->filter(fn ($p) => ($p[0] ?? '') !== '' && strtotime($p[0]) !== false)
            ->map(fn ($p) => [
                'date' => date('Y-m-d H:i', strtotime($p[0])),
                'place' => $p[1] ?? '',
                'url' => isset($p[2]) && filter_var($p[2], FILTER_VALIDATE_URL) ? $p[2] : null,
            ])
            ->sortBy('date')->values()->take(30)->all();
    }
}
