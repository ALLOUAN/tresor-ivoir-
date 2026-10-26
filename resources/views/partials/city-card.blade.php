{{--
    Carte ville touristique (/tourisme). Paramètre : $city (App\Models\TouristCity avec sites_count).
    Même famille que les cartes de l'accueil : photo posée dans la carte (ou monogramme sur aplat
    avec motif quand la ville n'a pas de photo), pastilles, une info par zone, bouton rond.
    Styles : .ti-city dans partials.ui-kit.
--}}
@php
    $cityImage = $city->thumbnail ?: $city->cover_image;
    $cityPalette = [['#00874F', 'diamond'], ['#00874F', 'ring'], ['#EB6E00', 'cross'], ['#B85600', 'diamond'], ['#00874F', 'ring']];
    [$cityColor, $cityMotif] = $cityPalette[crc32($city->slug ?: $city->name) % count($cityPalette)];
    $cityPattern = 'cp-'.$city->id;
    $citySites = $city->sites_count > 0
        ? trans_choice(':count site touristique|:count sites touristiques', $city->sites_count)
        : __('Sites à venir');
@endphp
<a href="{{ route('tourist.city', $city->slug) }}" class="ti-city group">
    <div class="ti-city__media" style="background: {{ $cityColor }}">
        {{-- Monogramme de secours : reste visible si la photo manque ou ne se charge pas. --}}
        <svg class="ti-city__pattern" width="100%" height="100%" aria-hidden="true">
            <defs>
                <pattern id="{{ $cityPattern }}" width="28" height="28" patternUnits="userSpaceOnUse">
                    @if($cityMotif === 'diamond')<path d="M14 2 26 14 14 26 2 14Z" fill="none" stroke="#fff" stroke-width="2"/>
                    @elseif($cityMotif === 'cross')<path d="M0 14h28M14 0v28" fill="none" stroke="#fff" stroke-width="2"/>
                    @else<circle cx="14" cy="14" r="6" fill="none" stroke="#fff" stroke-width="2"/>@endif
                </pattern>
            </defs>
            <rect width="100%" height="100%" fill="url(#{{ $cityPattern }})"/>
        </svg>
        <span class="ti-city__initial" aria-hidden="true">{{ mb_substr($city->name, 0, 1) }}</span>
        @if($cityImage)
            {{-- Photo entière (bannières larges avec texte incrusté) sur un fond flouté de la même image. --}}
            <img class="ti-city__backdrop" src="{{ $cityImage }}" alt="" aria-hidden="true" loading="lazy" decoding="async" onerror="this.remove()">
            <img class="ti-city__photo" src="{{ $cityImage }}" alt="{{ $city->name }}" loading="lazy" decoding="async" onerror="this.remove()">
        @endif

        <span class="ti-city__top">
            @if($city->is_featured)
                <span class="ti-city__pill is-star"><i class="fas fa-star" aria-hidden="true"></i>{{ __('À la une') }}</span>
            @else
                <span></span>
            @endif
            <span class="ti-city__pill"><i class="far fa-compass" aria-hidden="true"></i>{{ $citySites }}</span>
        </span>
    </div>

    <div class="ti-city__body">
        <h2 class="ti-city__name">{{ $city->name }}</h2>
        <p class="ti-city__where">
            <i class="fas fa-location-dot" aria-hidden="true"></i>
            {{ collect([$city->district, $city->region_administrative])->filter()->unique()->join(' · ') ?: "Côte d'Ivoire" }}
        </p>
        @if($city->description)
            <p class="ti-city__text">{{ $city->localized('description') }}</p>
        @endif
    </div>

    <div class="ti-city__footer">
        <span>{{ __('Explorer la ville') }}</span>
        <span class="ti-city__go" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
    </div>
</a>
