<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $people->name }} — {{ __('Cultures Ivoiriennes') }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.share-meta', ['title' => $people->name.' — Cultures ivoiriennes', 'description' => $people->description, 'image' => $people->cover_images ?: $people->thumbnail])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; } .font-serif { font-family: 'Playfair Display', serif; } html { scroll-behavior: smooth; } @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }</style>
    @include('partials.offer-page-theme')
    <style>
        /* Fiche d'un peuple, charte du site et couleurs du logo (coucher de soleil, palmier, lagune, soleil, mer, or, brun).
           Photo entière sans texte posé dessus, carte d'identité, onglets qui suivent la lecture, patrimoine filtrable. */
        .cp { max-width: 1180px; margin: 0 auto; padding: 22px 20px 64px; }
        .cp-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: #6b6458; }
        .cp-crumb li:first-child a { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #9a4308; font-weight: 700; text-decoration: none; }
        .cp-crumb li:first-child a:hover { border-color: #b9500a; }
        .cp-crumb .sep { color: #c2b89e; }
        .cp-crumb [aria-current] { font-weight: 700; color: #1d1a16; }

        /* Héros */
        .cp-hero-wrap { position: relative; overflow: hidden; background: #f3ead8; border-bottom: 1px solid #e3d5bb; }
        .cp-hero-wrap .ivm-g { opacity: .16 !important; }
        .cp-hero { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); gap: 34px; align-items: center; max-width: 1180px; margin: 0 auto; padding: 18px 20px 52px; }
        .cp-hero__txt { padding: 26px 28px; border-radius: 28px; background: #f6efe1; border: 1px solid #e3d5bb; }
        .cp-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .cp-pill { display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 999px; font-size: 11.5px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; background: #fff; border: 1px solid #e3d5bb; color: #9a4308; }
        .cp-pill .ico { font-size: 14px; }
        .cp-pill.is-green { color: #1b6b3a; }
        .cp-title { margin: 14px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(40px, 3.4vw + 18px, 72px); font-weight: 700; line-height: 1; letter-spacing: -.02em; color: #1d1a16; }
        .cp-lead { margin: 14px 0 0; font-size: 16.5px; line-height: 1.7; color: #3a2f22; }
        .cp-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        .cp-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 48px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; transition: background .15s ease, border-color .15s ease; }
        .cp-btn .ico { font-size: 18px; }
        .cp-btn.is-main { background: #b9500a; color: #fff; }
        .cp-btn.is-main:hover { background: #9a4308; color: #fff; }
        .cp-btn.is-line { background: #fff; color: #9a4308; border: 1.5px solid #b9500a; }
        .cp-btn.is-line:hover { background: #fdf4ec; }
        .cp-btn.is-icon { width: 48px; padding: 0; background: #fff; color: #9a4308; border: 1.5px solid #e6dccb; }
        .cp-btn:focus-visible, .cp-chip:focus-visible, .cp-el:focus-visible, .cp-other:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .cp-credit { margin: 14px 0 0; font-size: 11.5px; font-style: italic; color: #8a7f6b; }
        .cp-credit b { font-style: normal; color: #9a4308; }

        .cp-cover { position: relative; aspect-ratio: 4 / 3; border-radius: 28px; overflow: hidden; background: #e9e1d2; border: 1px solid #e3d5bb; }
        .people-hero-layer { position: absolute; inset: 0; transition: opacity .7s ease; }
        .people-hero-layer img { width: 100%; height: 100%; object-fit: cover; }
        .cp-cover__empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 64px; color: #b9500a; background: #fdf4ec; }
        .cp-cover__ui { position: absolute; left: 14px; right: 14px; bottom: 14px; z-index: 30; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        #people-hero-dots { display: flex; gap: 5px; padding: 8px 10px; border-radius: 999px; background: #fff; }
        .people-hero-dot { width: 7px; height: 7px; padding: 0; border: 0; border-radius: 999px; background: #e3d5bb; cursor: pointer; transition: width .25s ease, background .25s ease; }
        .people-hero-dot[aria-selected="true"] { width: 20px; background: #b9500a; }
        .cp-arrows { display: flex; gap: 6px; }
        .cp-arrows button { width: 38px; height: 38px; border-radius: 999px; border: 0; background: #fff; color: #9a4308; font-size: 19px; font-weight: 700; cursor: pointer; }
        .cp-arrows button:hover { background: #fdf4ec; }
        .cp-count { position: absolute; top: 14px; right: 14px; z-index: 30; display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 11px; border-radius: 999px; background: #fff; font-size: 12px; font-weight: 800; color: #3a2f22; }

        /* Sections */
        .cp-main { display: flex; flex-direction: column; gap: 56px; margin-top: 30px; }
        .cp-sec { scroll-margin-top: 150px; }
        .cp-label { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .cp-h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.3vw + 18px, 34px); font-weight: 700; color: #1d1a16; }
        .cp-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px 24px; margin-bottom: 20px; }

        /* Palette du logo, en rotation */
        .lc { --c: 217,101,15; }
        .lc.c2 { --c: 74,138,42; } .lc.c3 { --c: 31,128,121; } .lc.c4 { --c: 201,138,12; } .lc.c5 { --c: 44,110,143; } .lc.c6 { --c: 176,134,28; } .lc.c7 { --c: 122,74,42; }

        /* Présentation : domaines */
        .cp-intro { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 24px; align-items: start; }
        .cp-intro p.cp-text { margin: 0; font-size: 16.5px; line-height: 1.8; color: #3a2f22; }
        .cp-domains { padding: 22px; border-radius: 24px; background: #fff; border: 1px solid #ece5d6; }
        .cp-domains h3 { margin: 0 0 14px; font-size: 13px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #6b6458; }
        .cp-dlist { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; }
        .cp-dom { display: flex; align-items: center; gap: 10px; padding: 8px 10px; border-radius: 14px; background: rgba(var(--c), .07); border: 1px solid rgba(var(--c), .16); color: #1d1a16; font-size: 13.5px; font-weight: 600; text-decoration: none; cursor: pointer; text-align: left; transition: background .15s ease; }
        .cp-dom:hover { background: rgba(var(--c), .13); }
        .cp-dom i { flex: none; width: 30px; height: 30px; border-radius: 10px; background: rgb(var(--c)); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }
        .cp-dom em { margin-left: auto; font-style: normal; font-size: 12px; font-weight: 800; color: rgb(var(--c)); }

        /* Patrimoine */
        .cp-chips { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; padding-bottom: 2px; }
        .cp-chips::-webkit-scrollbar { display: none; }
        .cp-chip { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px 0 6px; border-radius: 999px; border: 1px solid #e8e1d3; background: #fff; color: #3a2f22; font-size: 13.5px; font-weight: 600; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease; }
        .cp-chip i { width: 28px; height: 28px; border-radius: 999px; background: rgba(var(--c), .14); color: rgb(var(--c)); display: inline-flex; align-items: center; justify-content: center; font-size: 12px; }
        .cp-chip:hover { border-color: rgba(var(--c), .55); }
        .cp-chip.is-on { background: rgb(var(--c)); border-color: rgb(var(--c)); color: #fff; }
        .cp-chip.is-on i { background: rgba(255,255,255,.22); color: #fff; }
        .cp-chip em { font-style: normal; font-size: 12px; opacity: .75; }
        .cp-els { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; margin-top: 18px; }
        .cp-el { position: relative; display: flex; flex-direction: column; border-radius: 24px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .cp-el[hidden] { display: none; }
        .cp-el:hover { border-color: #d8cdb8; box-shadow: 0 18px 34px -26px rgba(29,26,22,.55); }
        .cp-el__img { position: relative; aspect-ratio: 16 / 10; background: rgba(var(--c), .1); display: flex; align-items: center; justify-content: center; color: rgb(var(--c)); font-size: 40px; overflow: hidden; }
        .cp-el__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .cp-el:hover .cp-el__img img { transform: scale(1.04); }
        .cp-el__risk { position: absolute; top: 12px; left: 12px; display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; background: #fff; font-size: 11px; font-weight: 800; }
        .cp-el__risk .dot { width: 7px; height: 7px; border-radius: 999px; background: currentColor; }
        .cp-el__risk.is-vulnerable { color: #b45309; } .cp-el__risk.is-en_danger { color: #b42318; } .cp-el__risk.is-disparu { color: #57534e; }
        .cp-el__star { position: absolute; top: 12px; right: 12px; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; background: #fff; color: #9a4308; font-size: 11px; font-weight: 800; }
        .cp-el__body { flex: 1; display: flex; flex-direction: column; padding: 16px 18px 18px; }
        .cp-el__body small { display: flex; align-items: center; gap: 6px; font-size: 11.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: rgb(var(--c)); }
        .cp-el__body b { margin-top: 6px; font-family: 'Playfair Display', serif; font-size: 19px; line-height: 1.25; color: #1d1a16; }
        .cp-el__body p { margin: 6px 0 0; font-size: 13.5px; line-height: 1.55; color: #4a443a; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cp-el__go { margin-top: auto; padding-top: 12px; font-size: 13.5px; font-weight: 700; color: #9a4308; }
        .cp-empty { padding: 34px; border-radius: 24px; background: #fff; border: 1px dashed #d8cdb8; text-align: center; color: #6b6458; }

        /* Symboles */
        .cp-syms { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .cp-sym { display: flex; gap: 14px; padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .cp-sym > span { flex: none; width: 46px; height: 46px; border-radius: 15px; background: rgba(var(--c), .12); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-size: 20px; font-weight: 700; }
        .cp-sym small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8a7f6b; }
        .cp-sym b { display: block; margin-top: 4px; font-size: 15.5px; line-height: 1.4; color: #1d1a16; }

        /* Histoire */
        .cp-story { display: grid; grid-template-columns: minmax(0, 1.4fr) minmax(0, 1fr); gap: 24px; align-items: start; }
        .cp-story__text { padding: 28px 30px; border-radius: 26px; background: #fff; border: 1px solid #ece5d6; }
        .cp-story__text p { margin: 0; font-size: 16.5px; line-height: 1.85; color: #3a2f22; white-space: pre-line; }
        .cp-story__text p::first-letter { float: left; margin: 6px 10px 0 0; font-family: 'Playfair Display', serif; font-size: 58px; line-height: .8; color: #b9500a; }
        .cp-sources { padding: 22px; border-radius: 24px; background: #123a25; color: #fff; }
        .cp-sources h3 { margin: 0 0 12px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #f6c38f; }
        .cp-sources ol { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; counter-reset: s; }
        .cp-sources li { counter-increment: s; display: grid; grid-template-columns: 26px minmax(0, 1fr); gap: 10px; font-size: 13.5px; line-height: 1.45; }
        .cp-sources li::before { content: counter(s); width: 26px; height: 26px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f6c38f; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
        .cp-sources a { color: #fff; text-decoration: underline; text-decoration-color: rgba(255,255,255,.35); text-underline-offset: 3px; overflow-wrap: anywhere; }
        .cp-sources a:hover { text-decoration-color: #f6c38f; }
        .cp-sources p { margin: 0; font-size: 13.5px; color: #cfe6d7; }

        /* Autres peuples */
        .cp-others { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .cp-other { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .cp-other:hover { border-color: #d8cdb8; box-shadow: 0 16px 30px -24px rgba(29,26,22,.5); }
        .cp-other__img { aspect-ratio: 4 / 3; background: rgba(var(--c), .1); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-size: 30px; overflow: hidden; position: relative; }
        .cp-other__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .cp-other__body { padding: 12px 14px 14px; }
        .cp-other__body b { display: block; font-family: 'Playfair Display', serif; font-size: 17px; color: #1d1a16; }
        .cp-other__body span { display: block; margin-top: 3px; font-size: 12.5px; color: #6b6458; }

        @media (max-width: 1023px) {
            .cp-hero { grid-template-columns: minmax(0, 1fr); gap: 18px; }
            .cp-cover { order: -1; }
            .cp-intro, .cp-story { grid-template-columns: minmax(0, 1fr); }
            .cp-els, .cp-syms { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cp-others { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .cp { padding: 16px 16px 48px; }
            .cp-hero { padding: 14px 16px 44px; }
            .cp-hero__txt { padding: 18px 16px; border-radius: 22px; }
            .cp-title { font-size: clamp(34px, 10vw, 44px); }
            .cp-lead { font-size: 15px; }
            .cp-actions .cp-btn { flex: 1 1 auto; }
            .cp-actions .cp-btn.is-icon { flex: none; }
            .cp-cover { border-radius: 22px; }
            .cp-dlist, .cp-els, .cp-syms { grid-template-columns: minmax(0, 1fr); }
            .cp-story__text { padding: 20px; }
        }
        @media (prefers-reduced-motion: reduce) { .people-hero-layer, .cp-el__img img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="min-h-screen">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $coverImages = $people->cover_images ?? [];
    $culture = \App\Support\IvorianCulture::forPeople($people->slug);
    $riskLabels = ['vulnerable' => __('Vulnérable'), 'en_danger' => __('En danger'), 'disparu' => __('Disparu')];
    // Couleur du logo attribuée à chaque domaine, stable sur toute la page
    $domainColor = $domains->values()->mapWithKeys(fn ($d, $i) => [$d->id => 'c'.(($i % 7) + 1)]);
    $domainCount = $elements->groupBy('domain_id')->map->count();
    $symbols = $people->symboles ?? [];
    $sources = $people->sources ?? [];
@endphp

<section class="cp-hero-wrap">
    @include('partials.ivorian-motifs', ['culture' => $culture['key']])
    <div style="position:relative; z-index:1; max-width:1180px; margin:0 auto; padding:20px 20px 0">
        <ol class="cp-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
            <li><a href="{{ route('cultural.peoples') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Cultures') }}</a></li>
            <li class="sep" aria-hidden="true">/</li>
            <li aria-current="page">{{ $people->name }}</li>
        </ol>
    </div>
    <header class="cp-hero">
        <div class="cp-hero__txt">
            <div class="cp-pills">
                <span class="cp-pill"><x-icon name="map-point" />{{ $culture['label'] }}</span>
                @if($people->zone_geographique)<span class="cp-pill">{{ $people->zone_geographique }}</span>@endif
                @if($people->is_featured)<span class="cp-pill is-green"><x-icon name="star" />{{ __('Peuple vedette') }}</span>@endif
            </div>
            <h1 class="cp-title">{{ $people->name }}</h1>
            @if($people->description)<p class="cp-lead">{{ $people->localized('description') }}</p>@endif
            <div class="cp-actions">
                <a href="#patrimoine" class="cp-btn is-main"><x-icon name="masks" />{{ __('Explorer le patrimoine') }}</a>
                @if($people->histoire)<a href="#histoire" class="cp-btn is-line"><x-icon name="book" />{{ __('Son histoire') }}</a>@endif
                <button type="button" class="cp-btn is-icon" data-cp-share data-done="{{ __('Lien copié') }}" aria-label="{{ __('Partager') }}" title="{{ __('Partager') }}"><x-icon name="share" /></button>
            </div>
            <p class="cp-credit"><b>{{ $culture['label'] }}</b> · {{ $culture['credit'] }}</p>
        </div>

        <div class="cp-cover hero-wrap">
            @if(! empty($coverImages))
                @foreach($coverImages as $i => $img)
                    <div class="people-hero-layer {{ $i === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" data-people-hero-layer="{{ $i }}">
                        <img src="{{ $img }}" alt="{{ __(':p — photo :n', ['p' => $people->name, 'n' => $i + 1]) }}" @if($i > 0) loading="lazy" @endif decoding="async">
                    </div>
                @endforeach
                @if(count($coverImages) > 1)
                    <span class="cp-count"><x-icon name="gallery" />{{ count($coverImages) }}</span>
                    <div class="cp-cover__ui">
                        <div id="people-hero-dots" role="tablist" aria-label="{{ __('Choisir une photo') }}">
                            @foreach($coverImages as $i => $img)
                                <button type="button" class="people-hero-dot" data-people-hero-dot="{{ $i }}" aria-label="{{ __('Photo :n', ['n' => $i + 1]) }}" aria-selected="{{ $i === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <div class="cp-arrows">
                            <button type="button" id="people-hero-prev" aria-label="{{ __('Photo précédente') }}">‹</button>
                            <button type="button" id="people-hero-next" aria-label="{{ __('Photo suivante') }}">›</button>
                        </div>
                    </div>
                @endif
            @elseif($people->thumbnail)
                <img src="{{ $people->thumbnail }}" alt="{{ $people->name }}" style="width:100%;height:100%;object-fit:cover">
            @else
                <span class="cp-cover__empty"><x-icon name="users-group-rounded" /></span>
            @endif
        </div>
    </header>
</section>

<main class="cp">
    {{-- ══ CARTE D'IDENTITÉ ══ --}}
    <ul class="ox-keys">
        @if($people->langue_principale)<li><x-icon name="chat-round-dots" /><span><small>{{ __('Langue') }}</small><b>{{ $people->langue_principale }}</b></span></li>@endif
        @if($people->famille_linguistique)<li><x-icon name="global" /><span><small>{{ __('Famille linguistique') }}</small><b>{{ $people->famille_linguistique }}</b></span></li>@endif
        @if($people->capitale_culturelle)<li><x-icon name="buildings" /><span><small>{{ __('Capitale culturelle') }}</small><b>{{ $people->capitale_culturelle }}</b></span></li>@endif
        @if($people->population_estimee)<li><x-icon name="users-group-rounded" /><span><small>{{ __('Population estimée') }}</small><b>≈ {{ number_format($people->population_estimee, 0, ',', ' ') }}</b></span></li>@endif
        <li class="is-ok"><x-icon name="masks" /><span><small>{{ __('Patrimoine') }}</small><b>{{ $elements->count() }} {{ $elements->count() > 1 ? __('éléments') : __('élément') }}</b></span></li>
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        <a href="#presentation">{{ __('Présentation') }}</a>
        <a href="#patrimoine">{{ __('Patrimoine') }} ({{ $elements->count() }})</a>
        @if(! empty($symbols))<a href="#symboles">{{ __('Symboles') }}</a>@endif
        <a href="#histoire">{{ __('Histoire & sources') }}</a>
        @if($others->isNotEmpty())<a href="#autres">{{ __('Autres peuples') }}</a>@endif
    </nav>

    <div class="cp-main">
        {{-- ══ PRÉSENTATION ══ --}}
        <section class="ox-sec cp-sec" id="presentation">
            <div class="cp-head"><div><p class="cp-label">{{ __('Présentation') }}</p><h2 class="cp-h2">{{ __('Qui sont les :p ?', ['p' => $people->name]) }}</h2></div></div>
            <div class="cp-intro">
                <p class="cp-text">{{ $people->localized('description') ?: __('La présentation de ce peuple sera bientôt documentée.') }}</p>
                @if($domains->isNotEmpty())
                    <div class="cp-domains">
                        <h3>{{ __('Domaines culturels') }}</h3>
                        <div class="cp-dlist">
                            @foreach($domains as $d)
                                <button type="button" class="cp-dom lc {{ $domainColor[$d->id] }}" data-cp-goto="{{ $d->id }}">
                                    <i class="{{ $d->icon ?: 'fas fa-star' }}" aria-hidden="true"></i>{{ $d->name }}<em>{{ $domainCount[$d->id] ?? 0 }}</em>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </section>

        {{-- ══ PATRIMOINE ══ --}}
        <section class="ox-sec cp-sec" id="patrimoine">
            <div class="cp-head"><div><p class="cp-label">{{ __('Patrimoine') }}</p><h2 class="cp-h2">{{ __('Le patrimoine culturel :p', ['p' => $people->name]) }}</h2></div></div>
            @if($elements->isEmpty())
                <p class="cp-empty">{{ __('Aucun élément culturel enregistré pour l\'instant.') }}</p>
            @else
                @if($domains->count() > 1)
                    <div class="cp-chips" role="group" aria-label="{{ __('Filtrer par domaine') }}">
                        <button type="button" class="cp-chip lc c7 is-on" data-cp-filter="all" aria-pressed="true"><i class="fas fa-border-all" aria-hidden="true"></i>{{ __('Tout') }} <em>{{ $elements->count() }}</em></button>
                        @foreach($domains as $d)
                            <button type="button" class="cp-chip lc {{ $domainColor[$d->id] }}" data-cp-filter="{{ $d->id }}" aria-pressed="false"><i class="{{ $d->icon ?: 'fas fa-star' }}" aria-hidden="true"></i>{{ $d->name }} <em>{{ $domainCount[$d->id] ?? 0 }}</em></button>
                        @endforeach
                    </div>
                @endif
                <div class="cp-els">
                    @foreach($elements as $el)
                        @php $cc = $el->domain ? ($domainColor[$el->domain->id] ?? 'c1') : 'c7'; @endphp
                        <a href="{{ route('cultural.element', $el->slug) }}" class="cp-el lc {{ $cc }}" data-cp-domain="{{ $el->domain_id }}">
                            <span class="cp-el__img">
                                <i class="{{ $el->domain?->icon ?: 'fas fa-masks-theater' }}" aria-hidden="true"></i>
                                @if($el->thumbnail)<img src="{{ $el->thumbnail }}" alt="{{ $el->name }}" loading="lazy" decoding="async" onerror="this.remove()">@endif
                                @if($el->niveau_risque && $el->niveau_risque !== 'stable' && isset($riskLabels[$el->niveau_risque]))
                                    <span class="cp-el__risk is-{{ $el->niveau_risque }}"><span class="dot" aria-hidden="true"></span>{{ $riskLabels[$el->niveau_risque] }}</span>
                                @endif
                                @if($el->is_featured)<span class="cp-el__star"><x-icon name="star" />{{ __('À la une') }}</span>@endif
                            </span>
                            <span class="cp-el__body">
                                @if($el->domain)<small>{{ $el->domain->name }}</small>@endif
                                <b>{{ $el->name }}</b>
                                @if($el->short_description)<p>{{ $el->localized('short_description') }}</p>@endif
                                <span class="cp-el__go">{{ __('Découvrir') }} →</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ══ SYMBOLES ══ --}}
        @if(! empty($symbols))
            <section class="ox-sec cp-sec" id="symboles">
                <div class="cp-head"><div><p class="cp-label">{{ __('Repères') }}</p><h2 class="cp-h2">{{ __('Symboles et repères :p', ['p' => $people->name]) }}</h2></div></div>
                <div class="cp-syms">
                    @foreach($symbols as $s)
                        <div class="cp-sym lc c{{ ($loop->index % 7) + 1 }}">
                            <span aria-hidden="true">{{ mb_strtoupper(mb_substr((string) ($s['label'] ?? '•'), 0, 1)) }}</span>
                            <div class="min-w-0"><small>{{ $s['label'] ?? '' }}</small><b>{{ $s['valeur'] ?? '' }}</b></div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- ══ HISTOIRE & SOURCES ══ --}}
        <section class="ox-sec cp-sec" id="histoire">
            <div class="cp-head"><div><p class="cp-label">{{ __('Histoire') }}</p><h2 class="cp-h2">{{ __('L\'histoire du peuple :p', ['p' => $people->name]) }}</h2></div></div>
            <div class="cp-story">
                <div class="cp-story__text"><p>{{ $people->histoire ?: __('L\'histoire de ce peuple sera bientôt documentée.') }}</p></div>
                <aside class="cp-sources" aria-label="{{ __('Sources') }}">
                    <h3><x-icon name="book" />{{ __('Sources') }}</h3>
                    @if(! empty($sources))
                        <ol>
                            @foreach($sources as $src)
                                <li><span>@if(! empty($src['url']))<a href="{{ $src['url'] }}" target="_blank" rel="noopener noreferrer">{{ $src['label'] ?? $src['url'] }}</a>@else{{ $src['label'] ?? '' }}@endif</span></li>
                            @endforeach
                        </ol>
                    @else
                        <p>{{ __('Sources en cours de documentation.') }}</p>
                    @endif
                </aside>
            </div>
        </section>

        {{-- ══ AUTRES PEUPLES ══ --}}
        @if($others->isNotEmpty())
            <section class="ox-sec cp-sec" id="autres">
                <div class="cp-head">
                    <div><p class="cp-label">{{ __('Continuer') }}</p><h2 class="cp-h2">{{ __('D\'autres peuples à découvrir') }}</h2></div>
                    <a href="{{ route('cultural.peoples') }}" class="cp-btn is-line">{{ __('Tous les peuples') }} →</a>
                </div>
                <div class="cp-others">
                    @foreach($others as $o)
                        @php $oImg = ($o->cover_images[0] ?? null) ?: $o->thumbnail; @endphp
                        <a href="{{ route('cultural.people', $o->slug) }}" class="cp-other lc c{{ ($loop->index % 7) + 1 }}">
                            <span class="cp-other__img"><x-icon name="users-group-rounded" />@if($oImg)<img src="{{ $oImg }}" alt="{{ $o->name }}" loading="lazy" decoding="async" onerror="this.remove()">@endif</span>
                            <span class="cp-other__body"><b>{{ $o->name }}</b><span>{{ collect([$o->zone_geographique, $o->famille_linguistique])->filter()->implode(' · ') }}</span></span>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>

@include('partials.homepage-footer')

<script>
// ── Filtre du patrimoine par domaine ──
(function () {
    const chips = document.querySelectorAll('[data-cp-filter]');
    const cards = document.querySelectorAll('[data-cp-domain]');
    function apply(key) {
        chips.forEach(c => { const on = c.dataset.cpFilter === String(key); c.classList.toggle('is-on', on); c.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        cards.forEach(card => { card.hidden = key !== 'all' && card.dataset.cpDomain !== String(key); });
    }
    chips.forEach(c => c.addEventListener('click', () => apply(c.dataset.cpFilter)));
    // Les domaines de la présentation mènent au patrimoine filtré
    document.querySelectorAll('[data-cp-goto]').forEach(b => b.addEventListener('click', () => {
        if (chips.length) apply(b.dataset.cpGoto);
        document.getElementById('patrimoine')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }));
})();

// ── Partage ──
document.querySelector('[data-cp-share]')?.addEventListener('click', async e => {
    const b = e.currentTarget, data = { title: document.title, url: location.href.split('#')[0] };
    try {
        if (navigator.share) { await navigator.share(data); return; }
        await navigator.clipboard.writeText(data.url);
        b.title = b.dataset.done;
    } catch (err) {}
});

// ── Carrousel des photos (fondu, flèches, points, pause à l'interaction) ──
(function () {
    const layers = document.querySelectorAll('.people-hero-layer');
    if (layers.length < 2) return;
    const root = document.querySelector('.hero-wrap');
    const dots = document.querySelectorAll('.people-hero-dot');
    const prevBtn = document.getElementById('people-hero-prev');
    const nextBtn = document.getElementById('people-hero-next');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const DELAY = 4000;
    let index = 0, timer = null;
    function start() { stop(); if (!reduceMotion) timer = setInterval(next, DELAY); }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function render() {
        layers.forEach((el, i) => {
            const active = i === index;
            el.classList.toggle('opacity-100', active); el.classList.toggle('z-10', active);
            el.classList.toggle('opacity-0', !active); el.classList.toggle('z-0', !active);
        });
        dots.forEach((dot, i) => dot.setAttribute('aria-selected', i === index ? 'true' : 'false'));
    }
    function goTo(i) { index = (i + layers.length) % layers.length; render(); start(); }
    function next() { goTo(index + 1); }
    dots.forEach((dot, i) => dot.addEventListener('click', () => goTo(i)));
    prevBtn?.addEventListener('click', () => goTo(index - 1));
    nextBtn?.addEventListener('click', next);
    if (root) {
        root.addEventListener('mouseenter', stop); root.addEventListener('mouseleave', start);
        root.addEventListener('focusin', stop); root.addEventListener('focusout', start);
        root.addEventListener('touchstart', stop, { passive: true });
    }
    document.addEventListener('visibilitychange', () => { if (document.hidden) stop(); else start(); });
    render(); start();
})();
</script>
</body>
</html>
