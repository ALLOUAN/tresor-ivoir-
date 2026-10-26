<?php

namespace App\Http\Controllers;

use App\Models\Film;
use App\Services\ClientAccountService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * « Cinéma d'Ivoire » (Médiathèque) — vitrine des films documentaires et de fiction :
 * accueil de rubrique avec filtres, fiche film, clic « Regarder » mesuré, proposition de film.
 */
class FilmController extends Controller
{
    public function index(Request $request, ?string $kindSlug = null): View
    {
        $kind = Film::kindFromSlug($kindSlug);
        abort_if($kindSlug !== null && $kind === null, 404);

        $published = Film::published();
        $base = (clone $published)->when($kind, fn ($q) => $q->where('kind', $kind));

        $q = trim((string) $request->query('q', ''));
        $genre = (string) $request->query('genre', '');
        $year = (int) $request->query('annee', 0);
        $country = (string) $request->query('pays', '');
        $director = (string) $request->query('realisateur', '');
        $sort = $request->query('tri') === 'populaires' ? 'populaires' : 'recents';

        $films = (clone $base)
            ->when($q !== '', fn ($w) => $w->where(fn ($s) => $s->where('title', 'like', "%{$q}%")
                ->orWhere('director', 'like', "%{$q}%")->orWhere('synopsis', 'like', "%{$q}%")))
            ->when($genre !== '', fn ($w) => $w->where('genre', $genre))
            ->when($year > 0, fn ($w) => $w->where('year', $year))
            ->when($country !== '', fn ($w) => $w->where('country', $country))
            ->when($director !== '', fn ($w) => $w->where('director', $director))
            ->when($sort === 'populaires', fn ($w) => $w->orderByDesc('views_count')->orderByDesc('clicks_count'), fn ($w) => $w->ordered())
            ->paginate(24)->withQueryString();

        $filtering = $q !== '' || $genre !== '' || $year > 0 || $country !== '' || $director !== '';
        $facets = fn (string $col) => (clone $base)->whereNotNull($col)->where($col, '!=', '')->distinct()->orderBy($col)->pluck($col);

        return view('films.index', [
            'films' => $films,
            'kind' => $kind,
            'featured' => $filtering ? collect() : (clone $base)->where('is_featured', true)->ordered()->take(6)->get(),
            'spotlight' => $filtering ? null : ((clone $base)->where('is_featured', true)->ordered()->first() ?? (clone $base)->ordered()->first()),
            'counts' => ['all' => (clone $published)->count()] + collect(Film::KINDS)->map(fn ($m, $k) => (clone $published)->where('kind', $k)->count())->all(),
            'genres' => $facets('genre'),
            'years' => (clone $base)->whereNotNull('year')->distinct()->orderByDesc('year')->pluck('year'),
            'countries' => $facets('country'),
            'directors' => $facets('director'),
            'filters' => compact('q', 'genre', 'year', 'country', 'director', 'sort'),
            'filtering' => $filtering,
        ]);
    }

    public function show(Request $request, Film $film): View
    {
        abort_unless($film->status === 'published', 404);

        // Vue comptée une fois par session et par film.
        $seen = (array) $request->session()->get('films_seen', []);
        if (! in_array($film->id, $seen, true)) {
            $film->increment('views_count');
            $seen[] = $film->id;
            $request->session()->put('films_seen', array_slice($seen, -300));
        }

        $related = Film::published()->whereKeyNot($film->id)->where('kind', $film->kind)->ordered()->take(4)->get();
        if ($related->count() < 4) {
            $related = $related->merge(Film::published()->whereKeyNot($film->id)->whereNotIn('id', $related->pluck('id'))->ordered()->take(4 - $related->count())->get());
        }

        return view('films.show', ['film' => $film, 'related' => $related]);
    }

    /** « Regarder le film » : clic compté, puis redirection vers le lien officiel. */
    public function watch(Film $film): RedirectResponse
    {
        abort_unless($film->status === 'published' && $film->watch_url, 404);
        $film->increment('clicks_count');

        return redirect()->away($film->watch_url);
    }

    public function create(): View
    {
        return view('films.submit');
    }

    /** Proposition d'un film par un professionnel : enregistrée « à valider », jamais publiée directement. */
    public function store(Request $request): RedirectResponse
    {
        $image = ['nullable', 'image', 'mimes:'.implode(',', Film::IMAGE_MIMES), 'max:'.Film::IMAGE_MAX_KB];
        $data = $request->validate([
            'title' => ['required', 'string', 'max:180'],
            'kind' => ['required', Rule::in(array_keys(Film::KINDS))],
            'genre' => ['nullable', 'string', 'max:60'],
            'director' => ['required', 'string', 'max:160'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:'.(now()->year + 2)],
            'duration_minutes' => ['nullable', 'integer', 'min:1', 'max:900'],
            'country' => ['nullable', 'string', 'max:80'],
            'synopsis' => ['required', 'string', 'min:40', 'max:4000'],
            'trailer_url' => ['nullable', 'url', 'max:500'],
            'watch_url' => ['nullable', 'url', 'max:500'],
            'poster' => $image,
            'director_photo' => $image,
            'submitter_name' => ['required', 'string', 'max:160'],
            'submitter_email' => ['required', 'email', 'max:190'],
            'submitter_phone' => ['nullable', 'string', 'max:40'],
            'rights_confirmed' => ['accepted'],
            ...ClientAccountService::honeypotRules(),
        ], [
            'rights_confirmed.accepted' => 'Merci de confirmer que vous détenez les droits ou les autorisations nécessaires.',
            'synopsis.min' => 'Le synopsis doit compter au moins 40 caractères.',
            'website.max' => 'Envoi refusé.',
        ], ['title' => 'titre', 'kind' => 'catégorie', 'director' => 'réalisateur', 'submitter_name' => 'nom', 'submitter_email' => 'e-mail']);

        $film = Film::create([
            'title' => $data['title'],
            'kind' => $data['kind'],
            'genre' => $data['genre'] ?? null,
            'director' => $data['director'],
            'year' => $data['year'] ?? null,
            'duration_minutes' => $data['duration_minutes'] ?? null,
            'country' => $data['country'] ?? null,
            'synopsis' => $data['synopsis'],
            'trailer_url' => $data['trailer_url'] ?? null,
            'watch_url' => $data['watch_url'] ?? null,
            'submitter_name' => $data['submitter_name'],
            'submitter_email' => $data['submitter_email'],
            'submitter_phone' => $data['submitter_phone'] ?? null,
            'rights_confirmed' => true,
            'status' => 'pending',
            'submitted_at' => now(),
        ]);
        foreach (['poster' => 'poster_path', 'director_photo' => 'director_photo_path'] as $field => $column) {
            if ($request->hasFile($field)) {
                $film->{$column} = $request->file($field)->store('films/'.$film->id, 'public');
            }
        }
        $film->save();

        return redirect()->route('films.submit')->with('submitted', $film->title);
    }
}
