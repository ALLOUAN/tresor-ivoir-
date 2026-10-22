<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $city->name }} — {{ __('Régions Touristiques') }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.share-meta', ['title' => $city->name.' — Régions touristiques', 'description' => $city->description, 'image' => $city->cover_image ?: $city->thumbnail])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; } .font-serif { font-family: 'Playfair Display', serif; } html { scroll-behavior: smooth; } @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }</style>
    @include('partials.offer-page-theme')
    <style>
        /* Page destination, charte du site : crème, vert forêt, orange, Playfair. La bannière s'affiche entière
           (elle contient souvent son propre titre) : aucun texte n'est posé dessus. Aplats, ombre au survol seulement. */
        .tc { max-width: 1180px; margin: 0 auto; padding: 22px 20px 64px; }
        .tc-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: #6b6458; }
        .tc-crumb a { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #9a4308; font-weight: 700; text-decoration: none; }
        .tc-crumb a:hover { border-color: #b9500a; }
        .tc-crumb .sep { color: #c2b89e; }
        .tc-crumb [aria-current] { font-weight: 700; color: #1d1a16; }

        .tc-hero { display: grid; grid-template-columns: minmax(0, 1fr); gap: 26px; margin: 18px 0 26px; }
        .tc-hero > .min-w-0 { display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 8px 40px; align-items: end; }
        .tc-hero__id { grid-column: 1; }
        .tc-hero__txt { grid-column: 2; }
        .tc-hero__txt .tc-desc { margin-top: 0; }
        .tc-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .tc-pill { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 13px; border-radius: 999px; background: #fdf4ec; color: #9a4308; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .tc-pill .ico { font-size: 15px; }
        .tc-pill.is-green { background: #e7f3ec; color: #1b6b3a; }
        .tc-title { margin: 14px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(40px, 3.4vw + 20px, 72px); font-weight: 700; line-height: 1; letter-spacing: -.02em; color: #1d1a16; }
        .tc-region { margin: 10px 0 0; display: flex; align-items: center; gap: 8px; font-size: 15px; color: #4a443a; }
        .tc-region .ico { color: #b9500a; font-size: 18px; }
        .tc-desc { margin: 16px 0 0; font-size: 16.5px; line-height: 1.7; color: #3a352c; max-width: 560px; }
        .tc-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .tc-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; white-space: nowrap; transition: background .15s ease, border-color .15s ease; }
        .tc-btn .ico { font-size: 18px; }
        .tc-btn.is-main { background: #b9500a; color: #fff; }
        .tc-btn.is-main:hover { background: #9a4308; color: #fff; }
        .tc-btn.is-line { background: #fff; color: #9a4308; border: 1.5px solid #b9500a; }
        .tc-btn.is-line:hover { background: #fdf4ec; }
        .tc-btn:focus-visible, .tc-card:focus-visible, .tc-cat:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .tc-cover { order: -1; position: relative; border-radius: 28px; overflow: hidden; background: #e9e1d2; border: 1px solid #e6dccb; }
        .tc-cover img { display: block; width: 100%; height: clamp(300px, 34vw, 480px); object-fit: cover; object-position: left center; }
        .tc-cover__empty { height: clamp(300px, 30vw, 440px); display: flex; align-items: center; justify-content: center; background: #1b6b3a; color: #f6c38f; font-size: 48px; }
        .tc-cover__badge { position: absolute; top: 14px; right: 14px; display: inline-flex; align-items: center; gap: 6px; height: 32px; padding: 0 12px; border-radius: 999px; background: #fff; color: #9a4308; font-size: 12px; font-weight: 800; }
        .tc-cover__badge .ico { font-size: 15px; }

        .tc-main { display: flex; flex-direction: column; gap: 54px; margin-top: 30px; }
        .tc-sec { scroll-margin-top: 150px; }
        .tc-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 10px 20px; margin-bottom: 18px; }
        .tc-label { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .tc-h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.2vw + 18px, 34px); font-weight: 700; color: #1d1a16; }
        .tc-more { display: inline-flex; align-items: center; gap: 6px; font-size: 14px; font-weight: 700; color: #9a4308; text-decoration: none; }
        .tc-more:hover { text-decoration: underline; text-underline-offset: 3px; }

        /* Incontournables : 1 grande carte + les autres */
        .tc-sites { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); grid-auto-rows: 210px; gap: 14px; }
        .tc-card { position: relative; display: flex; flex-direction: column; justify-content: flex-end; border-radius: 24px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .tc-card:hover { border-color: #d8cdb8; box-shadow: 0 18px 34px -26px rgba(29,26,22,.55); }
        .tc-card.is-big { grid-column: span 2; grid-row: span 2; }
        .tc-card.is-wide { grid-column: span 2; }
        .tc-card__img { position: absolute; inset: 0; background: #f3eee4; }
        .tc-card__img img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .tc-card:hover .tc-card__img img { transform: scale(1.04); }
        .tc-card__ph { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; padding-bottom: 70px; font-size: 40px; }
        .tc-card__body { position: relative; margin: 10px; padding: 12px 14px; border-radius: 16px; background: #fff; }
        .tc-card__body small { display: block; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #b9500a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tc-card__body b { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 3px; font-family: 'Playfair Display', serif; font-size: 17px; line-height: 1.25; color: #1d1a16; }
        .tc-card.is-big .tc-card__body { margin: 14px; padding: 16px 18px; }
        .tc-card.is-big .tc-card__body b { font-size: 24px; }
        .tc-card__body p { margin: 6px 0 0; font-size: 13.5px; line-height: 1.5; color: #4a443a; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .tc-card__star { position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 5px; height: 28px; padding: 0 10px; border-radius: 999px; background: #fff; color: #9a4308; font-size: 11.5px; font-weight: 800; }
        .tc-card__star .ico { font-size: 14px; }

        /* Catégories */
        .tc-cats { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 14px; }
        .tc-cat { display: flex; align-items: center; gap: 16px; padding: 16px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .tc-cat:hover { border-color: #d8cdb8; box-shadow: 0 16px 30px -26px rgba(29,26,22,.5); }
        .tc-cat__ic { flex: none; width: 60px; height: 60px; border-radius: 18px; overflow: hidden; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .tc-cat__ic img { width: 100%; height: 100%; object-fit: cover; }
        .tc-cat__body { flex: 1; min-width: 0; }
        .tc-cat__body b { display: block; font-size: 15.5px; color: #1d1a16; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tc-cat__count { display: flex; flex-wrap: wrap; gap: 4px 12px; margin-top: 4px; font-size: 12.5px; font-weight: 600; color: #6b6458; }
        .tc-cat__count span { display: inline-flex; align-items: center; gap: 5px; }
        .tc-cat__count .ico { color: #b9500a; font-size: 14px; }
        .tc-cat__body p { margin: 4px 0 0; font-size: 12.5px; color: #8a7f6b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .tc-cat__go { flex: none; width: 36px; height: 36px; border-radius: 999px; background: #f6f3ed; color: #9a4308; display: flex; align-items: center; justify-content: center; transition: background .15s ease, color .15s ease; }
        .tc-cat:hover .tc-cat__go { background: #b9500a; color: #fff; }

        /* Où dormir */
        .tc-stays { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; align-items: stretch; }

        .tc-plan { display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 18px; align-items: center; padding: 28px 30px; border-radius: 28px; background: #1b6b3a; color: #fff; }
        .tc-plan h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(22px, 1vw + 18px, 30px); }
        .tc-plan p { margin: 6px 0 0; font-size: 15px; color: #cfe6d7; }
        .tc-plan .tc-btn.is-main { background: #fff; color: #1b6b3a; }
        .tc-plan .tc-btn.is-main:hover { background: #fdf4ec; color: #1b6b3a; }
        .tc-empty { padding: 28px; border-radius: 22px; background: #fff; border: 1px dashed #d8cdb8; text-align: center; color: #6b6458; font-size: 14px; }

        @media (max-width: 1023px) {
            .tc-hero { gap: 20px; }
            .tc-hero > .min-w-0 { grid-template-columns: minmax(0, 1fr); }
            .tc-hero__id, .tc-hero__txt { grid-column: 1; }
            .tc-hero__txt .tc-desc { margin-top: 10px; }
            .tc-sites { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: 200px; }
            .tc-cats, .tc-stays { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .tc { padding: 16px 16px 120px; }
            .tc-cover { border-radius: 22px; }
            .tc-cover img { height: auto; aspect-ratio: 16 / 10; }
            .tc-cover__empty { height: 240px; font-size: 40px; }
            .tc-desc { font-size: 15.5px; }
            .tc-actions .tc-btn { flex: 1 1 auto; }
            .tc-sites { grid-template-columns: minmax(0, 1fr); grid-auto-rows: auto; }
            .tc-card { min-height: 230px; }
            .tc-card.is-wide { grid-column: auto; }
            .tc-card.is-big { grid-column: auto; grid-row: span 1; }
            .tc-card.is-big { min-height: 320px; }
            .tc-card.is-big .tc-card__body b { font-size: 20px; }
            .tc-cats, .tc-stays { grid-template-columns: minmax(0, 1fr); }
            .tc-plan { grid-template-columns: minmax(0, 1fr); padding: 22px; border-radius: 22px; }
        }
        @media (prefers-reduced-motion: reduce) { .tc-card__img img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915] min-h-screen">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $cover = $city->cover_image ?: $city->thumbnail;
    $sitesCount = $sites->count();
    $accomsCount = \App\Models\Accommodation::where('city_id', $city->id)->where('is_active', 1)->count();
    $region = $city->district ?: $city->region_administrative;
    $sources = $city->sources ?? [];
@endphp
<main class="tc">
    <ol class="tc-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
        <li><a href="{{ route('tourist.cities') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Régions') }}</a></li>
        <li class="sep" aria-hidden="true">/</li>
        <li aria-current="page">{{ $city->name }}</li>
    </ol>

    {{-- ══ EN-TÊTE ══ --}}
    <header class="tc-hero">
        <div class="min-w-0">
            <div class="tc-hero__id"><div class="tc-pills">
                <span class="tc-pill"><x-icon name="map-point" />{{ __('Destination') }}</span>
                @if($city->is_featured)<span class="tc-pill is-green"><x-icon name="star" />{{ __('Destination vedette') }}</span>@endif
            </div>
            <h1 class="tc-title">{{ $city->name }}</h1>
            @if($region)<p class="tc-region"><x-icon name="map" />{{ $region }}@if($city->region_administrative && $city->district && mb_strtolower($city->region_administrative) !== mb_strtolower($city->district)) · {{ $city->region_administrative }}@endif</p>@endif</div>
            <div class="tc-hero__txt">@if($city->description)<p class="tc-desc">{{ $city->localized('description') }}</p>@endif
            <div class="tc-actions">
                <a href="#incontournables" class="tc-btn is-main"><x-icon name="compass" />{{ __('Explorer les sites') }}</a>
                @if($city->latitude && $city->longitude)
                    <a href="https://www.google.com/maps/search/?api=1&query={{ $city->latitude }},{{ $city->longitude }}" target="_blank" rel="noopener noreferrer" class="tc-btn is-line"><x-icon name="map" />{{ __('Voir sur la carte') }}</a>
                @endif
                @if($city->website)
                    <a href="{{ $city->website }}" target="_blank" rel="noopener" class="tc-btn is-line"><x-icon name="global" />{{ __('Site officiel') }}</a>
                @endif
            </div>
        </div>
        </div>
        <div class="tc-cover">
            @if($cover)
                <img src="{{ $cover }}" alt="{{ __('Bannière :c', ['c' => $city->name]) }}" decoding="async" fetchpriority="high">
            @else
                <div class="tc-cover__empty"><x-icon name="city" /></div>
            @endif
        </div>
    </header>

    {{-- ══ CHIFFRES CLÉS ══ --}}
    <ul class="ox-keys">
        <li><x-icon name="map-point" /><span><small>{{ __('Sites touristiques') }}</small><b>{{ $sitesCount }}</b></span></li>
        <li><x-icon name="bed" /><span><small>{{ __('Hébergements') }}</small><b>{{ $accomsCount }}</b></span></li>
        <li><x-icon name="widget" /><span><small>{{ __('Catégories') }}</small><b>{{ $categories->count() }}</b></span></li>
        @if($city->district)<li><x-icon name="flag" /><span><small>{{ __('District') }}</small><b>{{ $city->district }}</b></span></li>@endif
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        @if($sites->isNotEmpty())<a href="#incontournables">{{ __('Incontournables') }}</a>@endif
        <a href="#categories">{{ __('Catégories') }}</a>
        @if($accommodations->isNotEmpty())<a href="#ou-dormir">{{ __('Où dormir') }}</a>@endif
        <a href="#preparer">{{ __('Préparer') }}</a>
    </nav>

    <div class="tc-main">
        {{-- ══ INCONTOURNABLES ══ --}}
        @if($sites->isNotEmpty())
            <section class="ox-sec tc-sec" id="incontournables">
                <div class="tc-head"><div><p class="tc-label">{{ __('À voir') }}</p><h2 class="tc-h2">{{ __('Les incontournables de :c', ['c' => $city->name]) }}</h2></div></div>
                <div class="tc-sites">
                    @foreach($sites as $site)
                        @php
                            $img = $site->media->first()?->url ?: $site->thumbnail;
                            $col = $site->category?->color ?: '#b9500a';
                        @endphp
                        <a href="{{ route('tourist.site', $site->slug) }}" class="tc-card {{ $loop->first ? 'is-big' : '' }} {{ $loop->last && ! $loop->first && ($loop->count - 1) % 2 === 1 ? 'is-wide' : '' }}">
                            <span class="tc-card__img">
                                <span class="tc-card__ph" style="background: {{ $col }}1a; color: {{ $col }}"><i class="{{ $site->category?->icon ?: 'fas fa-landmark' }}" aria-hidden="true"></i></span>
                                @if($img)
                                    <img src="{{ $img }}" alt="{{ $site->name }}" loading="{{ $loop->first ? 'eager' : 'lazy' }}" decoding="async" onerror="this.remove()" style="position:relative">
                                @endif
                            </span>
                            @if($site->is_featured)<span class="tc-card__star"><x-icon name="star" />{{ __('Incontournable') }}</span>@endif
                            <span class="tc-card__body">
                                @if($site->category)<small>{{ $site->category->name }}</small>@endif
                                <b>{{ $site->name }}</b>
                                @if($loop->first && $site->short_description)<p>{{ $site->short_description }}</p>@endif
                            </span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ══ CATÉGORIES ══ --}}
        <section class="ox-sec tc-sec" id="categories">
            <div class="tc-head"><div><p class="tc-label">{{ __('Par thème') }}</p><h2 class="tc-h2">{{ __('Catégories touristiques') }}</h2></div></div>
            @if($categories->isEmpty())
                <p class="tc-empty">{{ __('Aucune catégorie touristique disponible pour') }} {{ $city->name }}.</p>
            @else
                <div class="tc-cats">
                    @foreach($categories as $cat)
                        @php $col = $cat->color ?: '#b9500a'; @endphp
                        <a href="{{ route('tourist.category', [$city->slug, $cat->slug]) }}" class="tc-cat">
                            <span class="tc-cat__ic" style="background: {{ $col }}1f; color: {{ $col }}">
                                @if($cat->icon_image_url)<img src="{{ $cat->icon_image_url }}" alt="">@else<i class="{{ $cat->icon ?: 'fas fa-tag' }}" aria-hidden="true"></i>@endif
                            </span>
                            <span class="tc-cat__body">
                                <b>{{ $cat->name }}</b>
                                <span class="tc-cat__count">
                                    @if($cat->sites_count > 0)<span><x-icon name="map-point" />{{ $cat->sites_count }} {{ $cat->sites_count > 1 ? __('sites') : __('site') }}</span>@endif
                                    @if(($cat->accoms_count ?? 0) > 0)<span><x-icon name="bed" />{{ $cat->accoms_count }} {{ $cat->accoms_count > 1 ? __('hébergements') : __('hébergement') }}</span>@endif
                                </span>
                                @if($cat->description)<p>{{ $cat->description }}</p>@endif
                            </span>
                            <span class="tc-cat__go" aria-hidden="true"><x-icon name="alt-arrow-right" /></span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ══ OÙ DORMIR ══ --}}
        @if($accommodations->isNotEmpty())
            <section class="ox-sec tc-sec" id="ou-dormir">
                <div class="tc-head">
                    <div><p class="tc-label">{{ __('Séjourner') }}</p><h2 class="tc-h2">{{ __('Où dormir à :c', ['c' => $city->name]) }}</h2></div>
                    @if($accomsCount > $accommodations->count())<a href="{{ route('accommodations.index') }}" class="tc-more">{{ __('Tout voir') }}<x-icon name="alt-arrow-right" /></a>@endif
                </div>
                <div class="tc-stays">
                    @foreach($accommodations as $acc)
                        @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($acc, 'hotels')])
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ══ PRÉPARER ══ --}}
        <section class="tc-plan tc-sec" id="preparer">
            <div>
                <h2>{{ __('Envie de découvrir :c ?', ['c' => $city->name]) }}</h2>
                <p>{{ __('Composez votre séjour jour par jour : sites, hébergements et budget.') }}</p>
            </div>
            <a href="{{ route('trip.create') }}" class="tc-btn is-main" id="reserver"><x-icon name="route" />{{ __('Planifier mon séjour') }}</a>
        </section>
    </div>
</main>

<div class="ox-bar" data-ox-bar>
    <span><small>{{ __('Destination') }}</small><b>{{ $city->name }}</b></span>
    <a href="{{ route('trip.create') }}"><x-icon name="route" />{{ __('Planifier') }}</a>
</div>

@include('partials.sources-list', ['sources' => $sources])
@include('partials.homepage-footer')
@include('partials.place-card-script')
</body>
</html>
