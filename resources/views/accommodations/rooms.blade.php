<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Chambres') }} — {{ $accommodation->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        /* Page « Chambres » : en-tête de l'hôtel, comparatif, cartes de chambres avec carrousel.
           Aplats uniquement, ombre au survol seulement. */
        .ti-rooms-back { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 999px; background: #ffffff; border: 1px solid #ece5d6; color: #9a4308; font-size: 13px; font-weight: 700; transition: border-color .15s ease; }
        .ti-rooms-back:hover { border-color: #ef7c2b; color: #9a4308; }
        .ti-rooms-back i { font-size: 11px; }

        /* En-tête */
        .rh { display: grid; grid-template-columns: auto minmax(0, 1fr); gap: 22px; align-items: center; margin: 20px 0 26px; }
        .rh__img { width: 112px; height: 112px; border-radius: 26px; object-fit: cover; background: #f3eee4; }
        .rh__img.is-icon[hidden] { display: none; }
        .rh__img.is-icon { display: inline-flex; align-items: center; justify-content: center; color: #b9500a; font-size: 34px; background: #fdf4ec; }
        .rh__kicker { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #b9500a; }
        .rh__kicker span { color: #c98a0b; letter-spacing: 0; }
        .rh__title { margin: 4px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(30px, 2.4vw + 18px, 46px); font-weight: 700; line-height: 1.08; letter-spacing: -.01em; color: #1d1a16; }
        .rh__facts { display: flex; flex-wrap: wrap; gap: 6px 18px; margin: 12px 0 0; padding: 0; list-style: none; font-size: 14px; color: #4a443a; }
        .rh__facts li { display: inline-flex; align-items: center; gap: 7px; max-width: 100%; }
        .rh__facts li > i { flex: none; }
        .rh__facts i { font-size: 13px; color: #8a7f6b; }
        .rh__facts b { color: #1d1a16; }
        .rh__facts .is-ok { color: #1b6b3a; font-weight: 600; } .rh__facts .is-ok i { color: #1b6b3a; }

        /* Comparatif */
        .rc { margin: 0 0 28px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; overflow: hidden; }
        .rc__title { margin: 0; padding: 16px 20px; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #6b6458; border-bottom: 1px solid #f0eadf; }
        .rc table { width: 100%; border-collapse: collapse; font-size: 14px; }
        .rc th { padding: 10px 20px; font-size: 11.5px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: #8a7f6b; text-align: left; background: #faf7f1; }
        .rc td { padding: 14px 20px; border-top: 1px solid #f0eadf; color: #3a352c; }
        .rc td:first-child a { font-weight: 700; color: #1d1a16; text-decoration: none; }
        .rc td:first-child a:hover { color: #9a4308; text-decoration: underline; text-underline-offset: 3px; }
        .rc td.is-price { font-weight: 800; color: #1d1a16; white-space: nowrap; text-align: right; }
        .rc th.is-price { text-align: right; }
        .rc tr.is-best td.is-price { color: #1b6b3a; }

        /* Chambres */
        .ti-rooms-list { display: flex; flex-direction: column; gap: 22px; }
        .rr { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); background: #ffffff; border: 1px solid #ece5d6; border-radius: 26px; overflow: hidden; scroll-margin-top: 100px; transition: border-color .2s ease, box-shadow .25s ease; }
        .rr:hover { border-color: #d8cdb8; box-shadow: 0 26px 50px -36px rgba(29,26,22,.5); }
        .rr__media { position: relative; min-height: 340px; background: #f3eee4; }
        .rr__track { position: absolute; inset: 0; display: flex; overflow-x: auto; scroll-snap-type: x mandatory; scrollbar-width: none; }
        .rr__track::-webkit-scrollbar { display: none; }
        .rr__slide { flex: 0 0 100%; scroll-snap-align: start; position: relative; padding: 0; border: 0; background: none; cursor: zoom-in; }
        .rr__slide img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .rr__nav { position: absolute; top: 50%; z-index: 2; width: 40px; height: 40px; margin-top: -20px; border: 0; border-radius: 999px; background: #fff; color: #1d1a16; font-size: 13px; cursor: pointer; opacity: 0; transition: opacity .2s ease, background .15s ease; }
        .rr__nav.is-prev { left: 14px; } .rr__nav.is-next { right: 14px; }
        .rr:hover .rr__nav, .rr__nav:focus-visible { opacity: 1; }
        .rr__nav:hover { background: #1d1a16; color: #fff; }
        .rr__dots { position: absolute; left: 0; right: 0; bottom: 14px; z-index: 2; display: flex; justify-content: center; gap: 6px; pointer-events: none; }
        .rr__dots span { width: 7px; height: 7px; border-radius: 999px; background: rgba(255,255,255,.55); transition: width .2s ease, background .2s ease; }
        .rr__dots span.is-on { width: 20px; background: #fff; }
        .rr__count { position: absolute; left: 14px; top: 14px; z-index: 2; display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgba(29,26,22,.72); color: #fff; font-size: 12px; font-weight: 700; }
        .rr__empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; color: #8a7f6b; font-size: 13px; }
        .rr__empty i { width: 60px; height: 60px; border-radius: 999px; background: #fff; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 22px; }

        .rr__body { display: flex; flex-direction: column; gap: 14px; padding: 24px 26px 22px; min-width: 0; }
        .rr__head { display: flex; flex-wrap: wrap; align-items: flex-start; justify-content: space-between; gap: 8px 12px; }
        .rr__name { margin: 0; font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; line-height: 1.15; color: #1d1a16; }
        .rr__badges { display: flex; flex-wrap: wrap; gap: 6px; }
        .rr__badges span { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; background: #fff3d6; color: #7a5300; font-size: 12px; font-weight: 700; }
        .rr__specs { display: flex; flex-wrap: wrap; gap: 4px 18px; margin: 0; padding: 0; list-style: none; font-size: 14px; color: #3a352c; }
        .rr__specs li { display: inline-flex; align-items: center; gap: 8px; }
        .rr__specs i { color: #8a7f6b; font-size: 13px; }
        .rr__text { margin: 0; font-size: 14.5px; line-height: 1.65; color: #4a443a; }
        .rr__amen { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 16px; margin: 0; padding: 0; list-style: none; }
        .rr__amen li { display: flex; align-items: center; gap: 10px; font-size: 13.5px; color: #3a352c; min-width: 0; }
        .rr__amen i { flex: none; width: 30px; height: 30px; border-radius: 9px; background: #f6f3ec; color: #1d1a16; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; }
        .rr__cond { display: flex; gap: 8px; margin: 0; padding: 10px 12px; border-radius: 14px; background: #faf6ee; color: #6b6458; font-size: 12.5px; line-height: 1.5; }
        .rr__cond i { margin-top: 2px; color: #8a6a1f; }
        .rr__foot { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 14px; margin-top: auto; padding-top: 16px; border-top: 1px solid #ece5d6; }
        .rr__price { margin: 0; line-height: 1.15; }
        .rr__price small { display: block; margin-bottom: 4px; font-size: 12px; color: #6b6458; }
        .rr__price strong { font-size: 26px; font-weight: 800; color: #1d1a16; letter-spacing: -.01em; }
        .rr__price em { font-style: normal; font-size: 13px; color: #6b6458; }
        .rr__weekend { display: block; margin-top: 5px; font-size: 12.5px; color: #6b6458; }
        .rr__free { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 12.5px; font-weight: 600; color: #1b6b3a; }
        .rr__cta { display: inline-flex; align-items: center; gap: 10px; height: 50px; padding: 0 8px 0 22px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 14.5px; font-weight: 700; text-decoration: none; transition: background .15s ease; }
        .rr__cta:hover { background: #9a4308; color: #fff; }
        .rr__cta span { width: 36px; height: 36px; border-radius: 999px; background: #fff; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }
        .rr__cta.is-line { background: #fff; color: #1d1a16; border: 1.5px solid #e3dccb; }
        .rr__cta.is-line span { background: #f6f3ec; color: #1d1a16; }
        .rr__cta.is-line:hover { border-color: #b9500a; color: #9a4308; }
        .rr button:focus-visible, .rr a:focus-visible, .rc a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        .ti-room-empty { padding: 56px 20px; text-align: center; background: #ffffff; border: 1px dashed #d8cdb8; border-radius: 30px; color: #6b6458; }
        .ti-room-empty span { width: 64px; height: 64px; margin: 0 auto 12px; border-radius: 999px; background: #fdf4ec; color: #b9500a; display: flex; align-items: center; justify-content: center; font-size: 24px; }

        /* Visionneuse */
        .rl__nav { position: absolute; top: 50%; width: 48px; height: 48px; margin-top: -24px; border: 0; border-radius: 999px; background: rgba(255,255,255,.14); color: #fff; font-size: 16px; cursor: pointer; }
        .rl__nav:hover { background: #b9500a; }
        .rl__nav.is-prev { left: 16px; } .rl__nav.is-next { right: 16px; }
        .rl__count { position: absolute; bottom: 18px; left: 50%; transform: translateX(-50%); padding: 6px 12px; border-radius: 999px; background: rgba(255,255,255,.14); color: #fff; font-size: 13px; font-weight: 700; }

        /* Icônes Solar « Bold Duotone » (x-icon) : couleur de marque, second ton à 50 %. */
        .ti-rooms-back i { font-size: 15px; }
        .rh__img.is-icon i { font-size: 40px; }
        .rh__facts i { font-size: 16px; color: #b9500a; }
        .rr__specs i { font-size: 18px; color: #b9500a; }
        .rr__amen i { width: 38px; height: 38px; border-radius: 12px; background: #fdf4ec; color: #b9500a; font-size: 18px; }
        .rr__badges i { font-size: 13px; }
        .rr__count i { font-size: 15px; }
        .rr__nav { font-size: 17px; }
        .rr__empty i { font-size: 28px; }
        .rr__cond i { font-size: 16px; margin-top: 1px; }
        .rr__free i { font-size: 16px; }
        .rr__cta span { font-size: 17px; }
        .rl__nav { font-size: 22px; }
        #lightbox > button i { font-size: 20px; }
        @media (hover: none) { .rr__nav { opacity: 1; } }
        @media (max-width: 900px) {
            .rr { grid-template-columns: 1fr; }
            .rr__media { min-height: 0; aspect-ratio: 16 / 10; }
            .rr__body { padding: 20px 18px 18px; }
        }
        @media (max-width: 639px) {
            .rh { grid-template-columns: 72px minmax(0, 1fr); gap: 14px; }
            .rh__img { width: 72px; height: 72px; border-radius: 18px; align-self: start; }
            .rc th:nth-child(3), .rc td:nth-child(3) { display: none; }
            .rc th, .rc td { padding-left: 14px; padding-right: 14px; }
            .rr__amen { grid-template-columns: 1fr; }
            .rr__foot { flex-direction: column; align-items: stretch; }
            .rr__cta { justify-content: space-between; }
        }
        @media (prefers-reduced-motion: reduce) { .rr, .rr__dots span { transition: none; } .rr__track { scroll-behavior: auto; } }
    
        .rr__nav:hover { background: #b9500a; color: #fff; }
        .rr__cta.is-line { border-color: #b9500a; color: #9a4308; } .rr__cta.is-line span { background: #b9500a; color: #fff; } .rr__cta.is-line:hover { background: #fdf4ec; }
</style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('accommodations.show', $accommodation->slug) }}" class="ti-rooms-back">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ $accommodation->name }}
        </a>

        @php
            $rooms = collect($accommodation->room_types ?? []);
            $roomPrices = $rooms->pluck('price_xof')->filter(fn ($p) => (int) $p > 0);
            $hotelPhoto = $accommodation->cover_image ?: $accommodation->thumbnail;
            $freeDays = (new \App\Models\Reservation(['accommodation_id' => $accommodation->id]))->setRelation('accommodation', $accommodation)->freeCancellationDays();
            $hotelResponse = $accommodation->provider?->responseBadge();
            $rates = app(\App\Services\RoomRates::class);
            $where = collect([$accommodation->quartier, $accommodation->city?->name])->filter()->join(', ');
            $fmt = fn ($v) => number_format((int) $v, 0, ',', ' ');
            $roomId = fn ($r, $i) => 'chambre-'.(\Illuminate\Support\Str::slug($r['name'] ?? '') ?: $i);
            // Icône d'un équipement d'après son libellé (présentation seulement).
            $amenIcon = function (string $label): string {
                $l = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($label));
                foreach ([
                    'clim' => 'snowflake', 'wifi' => 'wi-fi-router', 'wi-fi' => 'wi-fi-router', 'tv' => 'tv', 'televi' => 'tv',
                    'minibar' => 'wineglass', 'coffre' => 'safe-square', 'bureau' => 'case', 'vue' => 'city',
                    'baignoire' => 'bath', 'douche' => 'bath', 'nespresso' => 'cup-hot', 'cafe' => 'cup-hot', 'bouilloire' => 'cup-hot',
                    'balcon' => 'window-frame', 'terrasse' => 'umbrella', 'piscine' => 'swimming', 'cuisine' => 'chef-hat',
                    'parking' => 'map-point', 'petit-dej' => 'cup-hot', 'petit dej' => 'cup-hot', 'lit' => 'bed', 'salon' => 'sofa',
                    'peignoir' => 'hanger', 'fer' => 'hanger', 'seche' => 'snowflake',
                ] as $k => $icon) {
                    if (str_contains($l, $k)) {
                        return $icon;
                    }
                }

                return 'check-circle';
            };
        @endphp

        {{-- En-tête : l'établissement --}}
        <header class="rh">
            @if($hotelPhoto)
                <img src="{{ $hotelPhoto }}" alt="" class="rh__img" decoding="async" onerror="this.nextElementSibling.hidden = false; this.remove()">
                <span class="rh__img is-icon" aria-hidden="true" hidden><x-icon name="buildings" /></span>
            @else
                <span class="rh__img is-icon" aria-hidden="true"><x-icon name="buildings" /></span>
            @endif
            <div class="min-w-0">
                <p class="rh__kicker">{{ __('Chambres & logements') }} · {{ $accommodation->type_label }}@if((int) $accommodation->stars > 0) <span>{{ str_repeat('★', (int) $accommodation->stars) }}</span>@endif</p>
                <h1 class="rh__title">{{ $accommodation->name }}</h1>
                <ul class="rh__facts">
                    @if($where)<li><x-icon name="map-point" />{{ $where }}</li>@endif
                    @if($rooms->isNotEmpty())<li><x-icon name="bed" />{{ trans_choice(':count type de chambre|:count types de chambres', $rooms->count()) }}</li>@endif
                    @if($roomPrices->isNotEmpty())<li><x-icon name="tag-price" />{{ __('Dès') }}&nbsp;<b>{{ $fmt($roomPrices->min()) }} {{ __('FCFA') }}</b>&nbsp;{{ __('/ nuit') }}</li>@endif
                    @if($roomPrices->isNotEmpty() && $freeDays > 0)<li class="is-ok"><x-icon name="restart" />{{ trans_choice('Annulation gratuite jusqu\'à :n jour avant|Annulation gratuite jusqu\'à :n jours avant', $freeDays, ['n' => $freeDays]) }}</li>@endif
                    @if($hotelResponse)<li class="is-ok"><x-icon name="bolt" />{{ $hotelResponse }}</li>@endif
                </ul>
            </div>
        </header>

        @if($rooms->isNotEmpty())
            {{-- Comparatif rapide --}}
            @if($rooms->count() > 1)
                <div class="rc">
                    <p class="rc__title">{{ __('Comparer les chambres') }}</p>
                    <table>
                        <thead><tr><th scope="col">{{ __('Chambre') }}</th><th scope="col">{{ __('Voyageurs') }}</th><th scope="col">{{ __('Surface') }}</th><th scope="col" class="is-price">{{ __('Prix / nuit') }}</th></tr></thead>
                        <tbody>
                            @foreach($rooms as $i => $room)
                                <tr class="{{ (int) ($room['price_xof'] ?? 0) > 0 && (int) $room['price_xof'] === (int) $roomPrices->min() ? 'is-best' : '' }}">
                                    <td><a href="#{{ $roomId($room, $i) }}">{{ $room['name'] ?? __('Chambre') }}</a></td>
                                    <td>{{ $room['max_adults'] ?? 2 }}</td>
                                    <td>{{ ! empty($room['area_m2']) ? $room['area_m2'].' m²' : '—' }}</td>
                                    <td class="is-price">{{ ! empty($room['price_xof']) ? $fmt($room['price_xof']).' '.__('FCFA') : __('Sur demande') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif

            <div class="ti-rooms-list">
                @foreach($rooms as $i => $room)
                    @php
                        $roomPhotos = array_values(array_filter((array) ($room['photos'] ?? [])));
                        $roomName = $room['name'] ?? __('Chambre');
                        $roomBadges = $rates->badges($room);
                        $roomAmenities = is_array($room['amenities'] ?? null) ? $room['amenities'] : array_filter([$room['amenities'] ?? null]);
                    @endphp
                    <article class="rr" id="{{ $roomId($room, $i) }}">
                        <div class="rr__media" data-rr-photos='@json($roomPhotos)'>
                            @if($roomPhotos)
                                <div class="rr__track" data-rr-track>
                                    @foreach($roomPhotos as $p => $photo)
                                        <button type="button" class="rr__slide" data-rr-zoom="{{ $p }}" aria-label="{{ __('Agrandir la photo :n de :name', ['n' => $p + 1, 'name' => $roomName]) }}">
                                            <img src="{{ $photo }}" alt="{{ $p === 0 ? $roomName : '' }}" loading="lazy" decoding="async">
                                        </button>
                                    @endforeach
                                </div>
                                <span class="rr__count"><x-icon name="gallery" />{{ trans_choice(':count photo|:count photos', count($roomPhotos)) }}</span>
                                @if(count($roomPhotos) > 1)
                                    <button type="button" class="rr__nav is-prev" data-rr-step="-1" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                                    <button type="button" class="rr__nav is-next" data-rr-step="1" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                                    <div class="rr__dots" aria-hidden="true">@foreach($roomPhotos as $p => $photo)<span class="{{ $p === 0 ? 'is-on' : '' }}"></span>@endforeach</div>
                                @endif
                            @else
                                <div class="rr__empty"><x-icon name="bed" />{{ __('Photos à venir') }}</div>
                            @endif
                        </div>

                        <div class="rr__body">
                            <div class="rr__head">
                                <h2 class="rr__name">{{ $roomName }}</h2>
                                @if($roomBadges)
                                    <span class="rr__badges">@foreach($roomBadges as $badge)<span><x-icon name="tag-price" />{{ $badge }}</span>@endforeach</span>
                                @endif
                            </div>
                            <ul class="rr__specs">
                                <li><x-icon name="users-group-rounded" />{{ $room['max_adults'] ?? 2 }} {{ __('pers. max') }}</li>
                                @if(!empty($room['beds']))<li><x-icon name="bed" />{{ $room['beds'] }}</li>@endif
                                @if(!empty($room['area_m2']))<li><x-icon name="ruler-angular" />{{ $room['area_m2'] }} m²</li>@endif
                            </ul>

                            @if(!empty($room['description']))
                                <p class="rr__text">{{ $room['description'] }}</p>
                            @endif

                            @if($roomAmenities)
                                <ul class="rr__amen" aria-label="{{ __('Équipements') }}">
                                    @foreach($roomAmenities as $amenity)<li><x-icon :name="$amenIcon((string) $amenity)" />{{ $amenity }}</li>@endforeach
                                </ul>
                            @endif

                            @if(!empty($room['conditions']))
                                <p class="rr__cond"><x-icon name="info-circle" />{{ $room['conditions'] }}</p>
                            @endif

                            <div class="rr__foot">
                                @if(!empty($room['price_xof']))
                                    <p class="rr__price">
                                        <small>{{ __('À partir de') }}</small>
                                        <strong>{{ $fmt($room['price_xof']) }} {{ __('FCFA') }}</strong> <em>{{ __('/ nuit') }}</em>
                                        @if(!empty($room['weekend_price_xof']) && (int) $room['weekend_price_xof'] !== (int) $room['price_xof'])
                                            <span class="rr__weekend">{{ __('Week-end :') }} {{ $fmt($room['weekend_price_xof']) }} {{ __('FCFA') }}</span>
                                        @endif
                                        @if($freeDays > 0)
                                            <span class="rr__free"><x-icon name="verified-check" />{{ trans_choice('Annulation gratuite jusqu\'à :n jour avant|Annulation gratuite jusqu\'à :n jours avant', $freeDays, ['n' => $freeDays]) }}</span>
                                        @endif
                                    </p>
                                    <a href="{{ route('accommodations.show', $accommodation->slug) }}?room={{ urlencode($roomName) }}#booking-module" class="rr__cta">
                                        {{ __('Réserver cette chambre') }}<span><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                                    </a>
                                @else
                                    <p class="rr__price"><small>{{ __('Tarif') }}</small><strong>{{ __('Sur demande') }}</strong></p>
                                    <a href="{{ route('accommodations.show', $accommodation->slug) }}" class="rr__cta is-line">
                                        {{ __('Contacter l\'établissement') }}<span><i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="ti-room-empty">
                <span aria-hidden="true"><x-icon name="bed" /></span>
                <p>{{ __('Aucune chambre renseignée pour cet établissement pour le moment.') }}</p>
            </div>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()" aria-label="{{ __('Fermer') }}">
            <i class="fas fa-xmark"></i>
        </button>
        <button type="button" id="lightbox-prev" class="rl__nav is-prev hidden" onclick="event.stopPropagation(); lightboxStep(-1)" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
        <button type="button" id="lightbox-next" class="rl__nav is-next hidden" onclick="event.stopPropagation(); lightboxStep(1)" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
        <span id="lightbox-count" class="rl__count hidden"></span>
    </div>

@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
let lbPhotos = [], lbIndex = 0;
function renderLightbox() {
    document.getElementById('lightbox-img').src = lbPhotos[lbIndex];
    const many = lbPhotos.length > 1;
    ['lightbox-prev', 'lightbox-next', 'lightbox-count'].forEach(id => document.getElementById(id).classList.toggle('hidden', !many));
    document.getElementById('lightbox-count').textContent = (lbIndex + 1) + ' / ' + lbPhotos.length;
}
// openLightbox(url) ou openLightbox([urls], index)
function openLightbox(urls, index) {
    lbPhotos = Array.isArray(urls) ? urls : [urls]; lbIndex = index || 0;
    renderLightbox();
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function lightboxStep(d) { if (lbPhotos.length < 2) return; lbIndex = (lbIndex + d + lbPhotos.length) % lbPhotos.length; renderLightbox(); }
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => {
    if (document.getElementById('lightbox').classList.contains('hidden')) return;
    if (e.key === 'Escape') closeLightbox();
    if (e.key === 'ArrowLeft') lightboxStep(-1);
    if (e.key === 'ArrowRight') lightboxStep(1);
});

// Carrousel de chaque chambre : flèches, points, glisser au doigt, clic = visionneuse.
document.querySelectorAll('.rr__media[data-rr-photos]').forEach(media => {
    const track = media.querySelector('[data-rr-track]');
    if (!track) return;
    const photos = JSON.parse(media.dataset.rrPhotos || '[]');
    const dots = media.querySelectorAll('.rr__dots span');
    const current = () => Math.round(track.scrollLeft / track.clientWidth);
    const go = i => track.scrollTo({ left: ((i + photos.length) % photos.length) * track.clientWidth, behavior: 'smooth' });
    media.querySelectorAll('[data-rr-step]').forEach(b => b.addEventListener('click', () => go(current() + Number(b.dataset.rrStep))));
    track.addEventListener('scroll', () => { const c = current(); dots.forEach((d, i) => d.classList.toggle('is-on', i === c)); }, { passive: true });
    track.addEventListener('click', e => { const s = e.target.closest('[data-rr-zoom]'); if (s) openLightbox(photos, Number(s.dataset.rrZoom)); });
});
</script>
</body>
</html>
