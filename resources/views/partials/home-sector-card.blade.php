{{--
    Carte établissement d'un secteur (accueil « Les meilleures adresses » et /nos-etablissements).
    Paramètres : $it (tableau préparé par App\Support\HomeSectorShowcase), $showSector (bool).
    Toute la carte est cliquable (lien étendu du titre) ; favori, itinéraire et partage restent de vrais boutons.
    Styles : .ti-place-card dans partials.ui-kit ; comportements : partials.place-card-script.
--}}
@php
    $pcRating = $it['rating'] ?? null;
    $pcAmenities = collect($it['amenities'] ?? []);
    // Accueil (sections mélangées) : le secteur en tête ; dans une section : le lieu (le type est déjà sur la photo).
    $pcShowSector = !empty($showSector) && !empty($it['sector']);
@endphp
<article class="ti-place-card group">
    <div class="ti-place-card__media">
        @if($it['image'])
            <img src="{{ $it['image'] }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
            @if(!empty($it['hover_image']))
                <img src="{{ $it['hover_image'] }}" alt="" class="ti-place-card__hover" loading="lazy" decoding="async" onerror="this.remove()">
            @endif
        @endif
        <span class="ti-place-card__icon" aria-hidden="true"><i class="fas {{ $it['icon'] }}"></i></span>

        <div class="ti-place-card__top">
            <div class="ti-place-card__pills">
                @if($it['badge'] || !empty($it['stars']))
                    <span class="ti-place-card__pill">
                        <i class="fas {{ $it['icon'] }}" aria-hidden="true"></i>{{ $it['badge'] ?? $it['sector'] }}@if(!empty($it['stars'])) {{ $it['stars'] }}★@endif
                    </span>
                @endif
                @if($it['verified'])
                    <span class="ti-place-card__pill is-ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Vérifié') }}</span>
                @endif
                @if(!empty($it['available']))
                    <span class="ti-place-card__pill is-ok"><i class="fas fa-calendar-check" aria-hidden="true"></i>{{ __('Disponible') }}</span>
                @endif
            </div>
            @include('partials.place-card-fav', ['providerId' => $it['provider_id'] ?? null, 'name' => $it['name']])
        </div>

        @if(($it['photo_count'] ?? 0) > 1)
            <div class="ti-place-card__bottom">
                <span class="ti-place-card__dots" aria-hidden="true">
                    @for($d = 0; $d < min(5, $it['photo_count']); $d++)<span @class(['is-on' => $d === 0])></span>@endfor
                </span>
                <span class="ti-place-card__count"><i class="fas fa-images" aria-hidden="true"></i>{{ $it['photo_count'] }}<span class="sr-only"> {{ __('photos') }}</span></span>
            </div>
        @endif

        <div class="ti-place-card__quick">
            <a href="{{ $it['map_url'] }}" target="_blank" rel="noopener" class="ti-place-card__qbtn" aria-label="{{ __('Itinéraire vers :name', ['name' => $it['name']]) }}" title="{{ __('Itinéraire') }}"><i class="fas fa-diamond-turn-right" aria-hidden="true"></i></a>
            <button type="button" class="ti-place-card__qbtn" data-place-share="{{ $it['url'] }}" data-place-title="{{ $it['name'] }}" aria-label="{{ __('Partager :name', ['name' => $it['name']]) }}" title="{{ __('Partager') }}"><i class="fas fa-share-nodes" aria-hidden="true"></i></button>
        </div>
    </div>

    <div class="ti-place-card__body">
        <div class="ti-place-card__row">
            @if($pcShowSector)
                <p class="ti-place-card__sector"><i class="fas {{ $it['icon'] }}" aria-hidden="true"></i>{{ $it['sector'] }}</p>
            @elseif($it['location'] !== '')
                <p class="ti-place-card__location"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $it['location'] }}</p>
            @else
                <span></span>
            @endif
            @if($pcRating)
                <span class="ti-place-card__rate"><i class="fas fa-star" aria-hidden="true"></i>{{ number_format($pcRating, 1, ',', ' ') }}@if(($it['reviews'] ?? 0) > 0)<small>({{ $it['reviews'] }})</small>@endif</span>
            @else
                <span class="ti-place-card__rate is-new"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('Nouveau') }}</span>
            @endif
        </div>
        <h3 class="ti-place-card__name"><a href="{{ $it['url'] }}" class="ti-place-card__link">{{ $it['name'] }}</a></h3>
        @if($pcShowSector && $it['location'] !== '')
            <p class="ti-place-card__location"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $it['location'] }}</p>
        @endif
        @if($pcAmenities->isNotEmpty())
            <ul class="ti-place-card__amenities" aria-label="{{ __('Équipements') }}">
                @foreach($pcAmenities->take(3) as $am)
                    <li><i class="{{ $am['icon'] ?? 'fas fa-check' }}" aria-hidden="true"></i>{{ \Illuminate\Support\Str::limit($am['label'], 18) }}</li>
                @endforeach
                @if($pcAmenities->count() > 3)
                    <li class="is-more">+{{ $pcAmenities->count() - 3 }}</li>
                @endif
            </ul>
        @elseif($it['description'])
            <p class="ti-place-card__text">{{ $it['description'] }}</p>
        @endif
        @if(!empty($it['free_cancellation']) || !empty($it['response_badge']))
            <p class="ti-place-card__trust">
                @if(!empty($it['free_cancellation']))<span><i class="fas fa-rotate-left" aria-hidden="true"></i>{{ __('Annulation gratuite') }}</span>@endif
                @if(!empty($it['response_badge']))<span><i class="fas fa-bolt" aria-hidden="true"></i>{{ $it['response_badge'] }}</span>@endif
            </p>
        @endif
    </div>

    <div class="ti-place-card__footer">
        @if($it['price'])
            <span class="ti-place-card__price">
                <small>{{ __('À partir de') }}</small>
                <strong>{{ number_format($it['price'], 0, ',', ' ') }} {{ __('FCFA') }}</strong><em> {{ __('/ nuit') }}</em>
            </span>
        @else
            <span></span>
        @endif
        <span class="ti-place-card__more" aria-hidden="true">{{ $it['cta'] ?? __('Découvrir') }}<i class="fas fa-arrow-right"></i></span>
    </div>
</article>
@include('partials.place-card-script')
