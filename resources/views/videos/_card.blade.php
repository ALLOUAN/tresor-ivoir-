{{-- Carte éditoriale d'une vidéo (médiathèque) : vignette verticale, puis rubrique, titre, lieu et vues. --}}
<button type="button" class="vd-card" data-vd-open="{{ $v->slug }}" aria-label="{{ __('Regarder : :t', ['t' => $v->title]) }}">
    <span class="vd-card__media">
        @if($v->poster())
            <img src="{{ $v->poster() }}" alt="" loading="{{ $i < 6 ? 'eager' : 'lazy' }}" decoding="async">
        @else
            {{-- Sans couverture : vignette fixe (aucun octet de vidéo téléchargé avant la lecture). --}}
            <span class="vd-card__empty"><x-icon name="clapperboard-play" /></span>
        @endif
        @if($v->source === 'upload')<video data-preview data-src="{{ $v->video_url }}" muted loop playsinline preload="none"></video>@endif
        @if($v->links || $v->provider_id)<span class="vd-card__book"><x-icon name="bag" />{{ __('Réservable') }}</span>@endif
        @if($v->durationLabel())<span class="vd-card__dur"><x-icon name="play" />{{ $v->durationLabel() }}</span>@endif
        <span class="vd-card__play" aria-hidden="true"><x-icon name="play" /></span>
    </span>
    <span class="vd-card__body">
        <span class="vd-card__cat">{{ $v->categoryLabel() }}</span>
        <span class="vd-card__title">{{ $v->title }}</span>
        <span class="vd-card__meta">
            @if($v->city)<span><x-icon name="map-point" />{{ $v->city->name }}</span>@endif
            <span><x-icon name="eye" />{{ number_format($v->views_count, 0, ',', ' ') }} {{ __('vues') }}</span>
        </span>
    </span>
</button>
