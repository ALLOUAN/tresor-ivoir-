{{--
    Carte prestataire de l'annuaire générique (accueil, onglets sans fiche sectorielle dédiée).
    Paramètre : $p (App\Models\Provider). Même gabarit que home-sector-card (styles .ti-place-card dans partials.ui-kit).
--}}
@php
    $pPhotos = collect([$p->cover_url])
        ->merge($p->media->where('type', 'image')->sortBy('sort_order')->pluck('url'))
        ->push($p->accommodation?->cover_image)
        ->filter()->unique()->values();
    $pCoverImg = $pPhotos->get(0);
    $pHoverImg = $pPhotos->get(1);
    $pRating = (float) ($p->rating_avg ?? 0);
    $pReviews = (int) ($p->rating_count ?? 0);
    $pUrl = route('providers.show', $p->slug);
    $pResponse = $p->responseBadge();
@endphp
<article class="ti-place-card group">
    <div class="ti-place-card__media">
        @if($pCoverImg)
            <img src="{{ $pCoverImg }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
            @if($pHoverImg)
                <img src="{{ $pHoverImg }}" alt="" class="ti-place-card__hover" loading="lazy" decoding="async" onerror="this.remove()">
            @endif
        @endif
        <span class="ti-place-card__icon" aria-hidden="true"><i class="fas fa-store"></i></span>

        <div class="ti-place-card__top">
            <div class="ti-place-card__pills">
                @if($p->is_verified)
                    <span class="ti-place-card__pill is-ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Vérifié') }}</span>
                @endif
            </div>
            @include('partials.place-card-fav', ['providerId' => $p->id, 'name' => $p->name])
        </div>

        @if($pPhotos->count() > 1)
            <div class="ti-place-card__bottom">
                <span class="ti-place-card__dots" aria-hidden="true">
                    @for($d = 0; $d < min(5, $pPhotos->count()); $d++)<span @class(['is-on' => $d === 0])></span>@endfor
                </span>
                <span class="ti-place-card__count"><i class="fas fa-images" aria-hidden="true"></i>{{ $pPhotos->count() }}<span class="sr-only"> {{ __('photos') }}</span></span>
            </div>
        @endif

        <div class="ti-place-card__quick">
            <a href="https://www.google.com/maps/search/?api=1&query={{ rawurlencode(trim($p->name.' '.($p->city ?? '').' Côte d\'Ivoire')) }}" target="_blank" rel="noopener" class="ti-place-card__qbtn" aria-label="{{ __('Itinéraire vers :name', ['name' => $p->name]) }}" title="{{ __('Itinéraire') }}"><i class="fas fa-diamond-turn-right" aria-hidden="true"></i></a>
            <button type="button" class="ti-place-card__qbtn" data-place-share="{{ $pUrl }}" data-place-title="{{ $p->name }}" aria-label="{{ __('Partager :name', ['name' => $p->name]) }}" title="{{ __('Partager') }}"><i class="fas fa-share-nodes" aria-hidden="true"></i></button>
        </div>
    </div>

    <div class="ti-place-card__body">
        <div class="ti-place-card__row">
            <p class="ti-place-card__sector"><i class="fas fa-store" aria-hidden="true"></i>{{ $p->category?->localized('name') ?? __('Prestataire') }}</p>
            @if($pRating > 0)
                <span class="ti-place-card__rate"><i class="fas fa-star" aria-hidden="true"></i>{{ number_format($pRating, 1, ',', ' ') }}@if($pReviews > 0)<small>({{ $pReviews }})</small>@endif</span>
            @else
                <span class="ti-place-card__rate is-new"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('Nouveau') }}</span>
            @endif
        </div>
        <h3 class="ti-place-card__name"><a href="{{ $pUrl }}" class="ti-place-card__link">{{ $p->name }}</a></h3>
        @if($p->city)
            <p class="ti-place-card__location"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $p->city }}</p>
        @endif
        @if($pResponse)
            <p class="ti-place-card__trust"><span><i class="fas fa-bolt" aria-hidden="true"></i>{{ $pResponse }}</span></p>
        @endif
    </div>

    <div class="ti-place-card__footer">
        <span></span>
        <span class="ti-place-card__more" aria-hidden="true">{{ __('Voir la fiche') }}<i class="fas fa-arrow-right"></i></span>
    </div>
</article>
@include('partials.place-card-script')
