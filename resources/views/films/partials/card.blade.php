{{-- Carte affiche d'un film (grille et rangées « Cinéma d'Ivoire »). --}}
<a href="{{ route('films.show', $f) }}" class="ci-card">
    <span class="ci-card__poster">
        @if($f->posterUrl())
            <img src="{{ $f->posterUrl() }}" alt="" loading="lazy" decoding="async">
        @else
            <span class="ci-card__ph"><x-icon name="{{ $kindIcons[$f->kind] ?? 'clapperboard-play' }}" />{{ $f->title }}</span>
        @endif
        <span class="ci-card__badges">
            <span class="ci-badge is-{{ $f->kind }}"><x-icon name="{{ $kindIcons[$f->kind] ?? 'clapperboard-play' }}" />{{ $f->kindLabel() }}</span>
            @if($f->isNew())<span class="ci-badge is-new">{{ __('Nouveau') }}</span>@endif
        </span>
        <span class="ci-card__play"><x-icon name="play" />{{ $f->trailer() ? __('Bande-annonce') : __('Voir la fiche') }}</span>
    </span>
    <h3>{{ $f->title }}</h3>
    <p class="ci-card__meta">{{ collect([$f->year, $f->genre, $f->durationLabel()])->filter()->implode(' · ') }}</p>
    @if($f->director)<p class="ci-card__dir">{{ __('de :n', ['n' => $f->director]) }}</p>@endif
</a>
