@extends('layouts.app')

@section('title', $film->exists ? 'Modifier le film' : 'Ajouter un film')
@section('page-title', $film->exists ? 'Modifier le film' : 'Ajouter un film')

@section('content')
@php
    $in = 'w-full bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2.5 text-white';
    $lb = 'block text-sm font-semibold text-slate-200 mb-1.5';
    $screeningsText = collect($film->screenings ?? [])->map(fn ($s) => trim(($s['date'] ?? '').' | '.($s['place'] ?? '').(! empty($s['url']) ? ' | '.$s['url'] : '')))->implode("\n");
@endphp
<a href="{{ route('admin.films.index') }}" class="inline-flex items-center gap-2 mb-4 text-sm text-slate-400 hover:text-orange-400"><i class="fas fa-arrow-left text-xs"></i> Tous les films</a>

@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-200 text-sm rounded-xl">
    <ul class="list-disc pl-5 space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
</div>
@endif

@if($film->exists && $film->submitter_email)
<div class="mb-5 px-4 py-3 bg-amber-900/20 border border-amber-800/60 text-amber-200 text-sm rounded-xl">
    <i class="fas fa-inbox"></i> Proposé par <b>{{ $film->submitter_name }}</b> — <a href="mailto:{{ $film->submitter_email }}" class="underline">{{ $film->submitter_email }}</a>@if($film->submitter_phone) · {{ $film->submitter_phone }}@endif @if($film->submitted_at) · le {{ $film->submitted_at->format('d/m/Y à H:i') }}@endif
</div>
@endif

<form method="POST" action="{{ $film->exists ? route('admin.films.update', $film) : route('admin.films.store') }}" enctype="multipart/form-data"
      class="bg-green-900 border border-slate-800 rounded-xl p-6 space-y-6">
    @csrf
    @if($film->exists) @method('PUT') @endif

    {{-- Identité du film --}}
    <div class="grid md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="title">Titre *</label>
            <input id="title" name="title" type="text" required maxlength="180" value="{{ old('title', $film->title) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="{{ $lb }}" for="kind">Catégorie *</label>
            <select id="kind" name="kind" class="{{ $in }}">
                @foreach(\App\Models\Film::KINDS as $key => $meta)
                    <option value="{{ $key }}" @selected(old('kind', $film->kind) === $key)>{{ $key === 'documentaire' ? 'Film documentaire' : 'Film fiction' }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $lb }}" for="genre">Genre</label>
            <input id="genre" name="genre" type="text" maxlength="60" list="genres" value="{{ old('genre', $film->genre) }}" class="{{ $in }}" placeholder="Ex. : Patrimoine & traditions, Drame…">
            <datalist id="genres">@foreach(collect(\App\Models\Film::GENRES)->flatten()->unique() as $g)<option value="{{ $g }}">@endforeach</datalist>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="tagline">Accroche</label>
            <input id="tagline" name="tagline" type="text" maxlength="220" value="{{ old('tagline', $film->tagline) }}" class="{{ $in }}" placeholder="Une phrase qui donne envie de voir le film">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="synopsis">Synopsis</label>
            <textarea id="synopsis" name="synopsis" rows="6" maxlength="6000" class="{{ $in }}">{{ old('synopsis', $film->synopsis) }}</textarea>
        </div>
    </div>

    {{-- Fiche technique --}}
    <div class="pt-5 border-t border-slate-800 grid md:grid-cols-3 gap-5">
        <p class="md:col-span-3 text-sm font-semibold text-slate-200 -mb-2">Fiche technique</p>
        <div>
            <label class="{{ $lb }}" for="director">Réalisateur / réalisatrice</label>
            <input id="director" name="director" type="text" maxlength="160" value="{{ old('director', $film->director) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="{{ $lb }}" for="year">Année</label>
            <input id="year" name="year" type="number" min="1900" max="{{ now()->year + 2 }}" value="{{ old('year', $film->year) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="{{ $lb }}" for="duration_minutes">Durée (minutes)</label>
            <input id="duration_minutes" name="duration_minutes" type="number" min="1" max="900" value="{{ old('duration_minutes', $film->duration_minutes) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="{{ $lb }}" for="country">Pays</label>
            <input id="country" name="country" type="text" maxlength="80" value="{{ old('country', $film->country) }}" class="{{ $in }}">
        </div>
        <div>
            <label class="{{ $lb }}" for="language">Langue(s)</label>
            <input id="language" name="language" type="text" maxlength="80" value="{{ old('language', $film->language) }}" class="{{ $in }}" placeholder="Français, baoulé, dioula…">
        </div>
        <div>
            <label class="{{ $lb }}" for="production">Production</label>
            <input id="production" name="production" type="text" maxlength="220" value="{{ old('production', $film->production) }}" class="{{ $in }}">
        </div>
        <div class="md:col-span-3">
            <label class="{{ $lb }}" for="director_bio">Biographie du réalisateur</label>
            <textarea id="director_bio" name="director_bio" rows="3" maxlength="4000" class="{{ $in }}">{{ old('director_bio', $film->director_bio) }}</textarea>
        </div>
        <div class="md:col-span-3 grid md:grid-cols-2 gap-5">
            <div>
                <label class="{{ $lb }}" for="cast">Distribution / intervenants</label>
                <textarea id="cast" name="cast" rows="4" maxlength="3000" class="{{ $in }}" placeholder="Un nom par ligne">{{ old('cast', $film->cast) }}</textarea>
            </div>
            <div>
                <label class="{{ $lb }}" for="awards">Prix et sélections</label>
                <textarea id="awards" name="awards" rows="4" maxlength="3000" class="{{ $in }}" placeholder="Un prix ou festival par ligne">{{ old('awards', $film->awards) }}</textarea>
            </div>
        </div>
    </div>

    {{-- Visuels --}}
    <div class="pt-5 border-t border-slate-800 grid md:grid-cols-2 gap-5">
        @foreach(['poster' => ['Affiche', 'Format portrait 2:3 (ex. 1000 × 1500 px).', 'poster_path', 'w-24 h-36'], 'backdrop' => ['Image de fond', 'Format paysage 16:9 (ex. 1920 × 1080 px). Sans image, l\'affiche est utilisée.', 'backdrop_path', 'w-48 h-28'], 'director_photo' => ['Photo du réalisateur', 'Portrait cadré sur le visage, format carré (ex. 600 × 600 px).', 'director_photo_path', 'w-24 h-24 rounded-full']] as $field => [$label, $hint, $col, $size])
            <div>
                <p class="text-sm font-semibold text-slate-200 mb-1">{{ $label }}</p>
                <p class="text-xs text-slate-400 mb-3">{{ $hint }} JPG, PNG ou WebP, 6 Mo max.</p>
                <div class="flex flex-wrap items-start gap-4">
                    @if($film->{$col})
                        <label class="block">
                            <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($film->{$col}) }}" alt="" class="{{ $size }} object-cover rounded-lg border border-slate-700">
                            <span class="mt-2 flex items-center gap-2 text-xs text-slate-300"><input type="checkbox" name="remove_{{ $field }}" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500"> Retirer</span>
                        </label>
                    @endif
                    <input type="file" name="{{ $field }}" accept="image/jpeg,image/png,image/webp" class="text-sm text-slate-300 file:mr-3 file:px-4 file:py-2 file:rounded-lg file:border-0 file:bg-orange-500 file:text-black file:font-semibold">
                </div>
            </div>
        @endforeach
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="credits">Crédits des visuels</label>
            <input id="credits" name="credits" type="text" maxlength="1000" value="{{ old('credits', $film->credits) }}" class="{{ $in }}" placeholder="© Société de production, photographe…">
        </div>
    </div>

    {{-- Regarder et projections --}}
    <div class="pt-5 border-t border-slate-800 grid md:grid-cols-2 gap-5">
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="trailer_url">Bande-annonce (lien YouTube ou Vimeo)</label>
            <input id="trailer_url" name="trailer_url" type="url" maxlength="500" value="{{ old('trailer_url', $film->trailer_url) }}" class="{{ $in }}" placeholder="https://www.youtube.com/watch?v=…">
            <p class="text-xs text-slate-400 mt-1">Intégrée en mode confidentialité renforcée et chargée seulement au clic.</p>
        </div>
        <div>
            <label class="{{ $lb }}" for="watch_url">Lien officiel pour regarder</label>
            <input id="watch_url" name="watch_url" type="url" maxlength="500" value="{{ old('watch_url', $film->watch_url) }}" class="{{ $in }}" placeholder="Plateforme, VOD, billetterie">
        </div>
        <div>
            <label class="{{ $lb }}" for="watch_label">Texte du bouton</label>
            <input id="watch_label" name="watch_label" type="text" maxlength="80" value="{{ old('watch_label', $film->watch_label) }}" class="{{ $in }}" placeholder="Regarder le film">
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="screenings_text">Projections</label>
            <textarea id="screenings_text" name="screenings_text" rows="3" maxlength="5000" class="{{ $in }} font-mono text-sm" placeholder="2026-11-15 19:00 | Institut français, Abidjan | https://billetterie…">{{ old('screenings_text', $screeningsText) }}</textarea>
            <p class="text-xs text-slate-400 mt-1">Une projection par ligne : date et heure | lieu | lien de réservation (facultatif). Seules les dates à venir s'affichent.</p>
        </div>
        <div class="md:col-span-2">
            <label class="{{ $lb }}" for="distributor_contact">Contact distribution (affiché sur la fiche)</label>
            <input id="distributor_contact" name="distributor_contact" type="text" maxlength="220" value="{{ old('distributor_contact', $film->distributor_contact) }}" class="{{ $in }}">
        </div>
    </div>

    {{-- Publication --}}
    <div class="pt-5 border-t border-slate-800 grid md:grid-cols-2 gap-5">
        <div>
            <label class="{{ $lb }}" for="status">Statut *</label>
            <select id="status" name="status" class="{{ $in }}">
                @foreach(\App\Models\Film::STATUSES as $key => $label)
                    <option value="{{ $key }}" @selected(old('status', $film->status) === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="{{ $lb }}" for="review_note">Note interne</label>
            <input id="review_note" name="review_note" type="text" maxlength="500" value="{{ old('review_note', $film->review_note) }}" class="{{ $in }}" placeholder="Visible uniquement par l'équipe">
        </div>
        <label class="md:col-span-2 flex items-start gap-3 p-3 rounded-lg border {{ $errors->has('rights_confirmed') ? 'border-red-700' : 'border-slate-700' }} text-sm text-slate-200">
            <input type="checkbox" name="rights_confirmed" value="1" @checked(old('rights_confirmed', $film->rights_confirmed)) class="mt-0.5 rounded border-slate-600 bg-slate-800 text-orange-500">
            <span><b>Droits et autorisations confirmés</b> — affiche, synopsis, bande-annonce et lien autorisés par les ayants droit. Obligatoire pour publier.</span>
        </label>
    </div>

    <div class="flex flex-wrap items-center gap-5 pt-4 border-t border-slate-800">
        <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $film->is_featured)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> À l'affiche</label>
        <label class="flex items-center gap-2 text-sm text-slate-200"><input type="checkbox" name="is_sponsored" value="1" @checked(old('is_sponsored', $film->is_sponsored)) class="rounded border-slate-600 bg-slate-800 text-orange-500"> Contenu sponsorisé <span class="text-xs text-slate-400">(mention affichée)</span></label>
        @if($film->exists)<span class="text-xs text-slate-400"><i class="fas fa-eye"></i> {{ $film->views_count }} vue(s) · <i class="fas fa-arrow-pointer"></i> {{ $film->clicks_count }} clic(s) « Regarder »</span>@endif
        <button type="submit" class="ml-auto px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl flex items-center gap-2"><i class="fas fa-save"></i> Enregistrer</button>
    </div>
</form>
@endsection
