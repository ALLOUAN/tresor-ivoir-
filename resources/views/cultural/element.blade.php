<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $element->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($element->short_description ?: strip_tags((string) $element->description), 155) }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; } .font-serif { font-family: 'Playfair Display', serif; } html { scroll-behavior: smooth; } @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }</style>
    @include('partials.offer-page-theme')
    <style>
        /* Fiche d'un élément culturel, charte du site et couleurs du logo. Photo entière (jamais de voile ni de texte dessus),
           infos clés, onglets qui suivent la lecture, fiche « Patrimoine » collante, sources visibles. */
        .ce { max-width: 1180px; margin: 0 auto; padding: 22px 20px 64px; }
        .ce-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: #6b6458; }
        .ce-crumb a { color: #9a4308; font-weight: 600; text-decoration: none; }
        .ce-crumb a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .ce-crumb li:first-child a { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; font-weight: 700; }
        .ce-crumb li:first-child a:hover { border-color: #b9500a; text-decoration: none; }
        .ce-crumb .sep { color: #c2b89e; }
        .ce-crumb [aria-current] { font-weight: 700; color: #1d1a16; }

        .ce-hero-wrap { position: relative; overflow: hidden; background: #f3ead8; border-bottom: 1px solid #e3d5bb; }
        .ce-hero-wrap .ivm-g { opacity: .16 !important; }
        .ce-hero { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1fr); gap: 32px; align-items: center; max-width: 1180px; margin: 0 auto; padding: 18px 20px 52px; }
        .ce-hero__txt { padding: 26px 28px; border-radius: 28px; background: #f6efe1; border: 1px solid #e3d5bb; }
        .ce-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .ce-pill { display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 999px; font-size: 11.5px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; background: #fff; border: 1px solid #e3d5bb; color: #9a4308; }
        .ce-pill i, .ce-pill .ico { font-size: 13px; }
        .ce-pill.is-green { color: #1b6b3a; }
        .ce-pill.is-warn { color: #b45309; } .ce-pill.is-danger { color: #b42318; } .ce-pill.is-gone { color: #57534e; }
        .ce-title { margin: 14px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(34px, 2.8vw + 18px, 60px); font-weight: 700; line-height: 1.03; letter-spacing: -.015em; color: #1d1a16; }
        .ce-lead { margin: 14px 0 0; font-family: 'Playfair Display', serif; font-style: italic; font-size: 19px; line-height: 1.55; color: #3a2f22; }
        .ce-meta { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 14px; color: #3a2f22; }
        .ce-meta li { display: inline-flex; align-items: center; gap: 7px; }
        .ce-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 20px; }
        .ce-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 48px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; transition: background .15s ease, border-color .15s ease; }
        .ce-btn .ico { font-size: 18px; }
        .ce-btn.is-main { background: #b9500a; color: #fff; }
        .ce-btn.is-main:hover { background: #9a4308; color: #fff; }
        .ce-btn.is-line { background: #fff; color: #9a4308; border: 1.5px solid #b9500a; }
        .ce-btn.is-line:hover { background: #fdf4ec; }
        .ce-btn.is-icon { width: 48px; padding: 0; background: #fff; color: #9a4308; border: 1.5px solid #e6dccb; }
        .ce-btn:focus-visible, .ce-people a:focus-visible, .ce-rel:focus-visible, .ce-thumbs button:focus-visible, .ce-photos button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        .ce-cover { position: relative; aspect-ratio: 4 / 3; border-radius: 28px; overflow: hidden; background: #e9e1d2; border: 1px solid #e3d5bb; }
        .ce-cover > img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
        .ce-cover__empty { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 14px; background: rgba(var(--c), .1); color: rgb(var(--c)); text-align: center; padding: 24px; }
        .ce-cover__empty i { width: 96px; height: 96px; border-radius: 30px; background: rgb(var(--c)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 40px; }
        .ce-cover__empty b { font-family: 'Playfair Display', serif; font-size: 22px; color: #1d1a16; }
        .ce-cover__empty span { font-size: 13px; color: #6b6458; }
        .ce-thumbs { position: absolute; left: 12px; right: 12px; bottom: 12px; display: flex; gap: 8px; }
        .ce-thumbs button { width: 64px; height: 48px; padding: 0; border-radius: 12px; overflow: hidden; border: 2px solid #fff; background: #fff; opacity: .75; cursor: pointer; }
        .ce-thumbs button.is-on, .ce-thumbs button:hover { opacity: 1; border-color: #b9500a; }
        .ce-thumbs img { width: 100%; height: 100%; object-fit: cover; }

        /* Palette du logo */
        .lc { --c: 217,101,15; }
        .lc.c2 { --c: 74,138,42; } .lc.c3 { --c: 31,128,121; } .lc.c4 { --c: 201,138,12; } .lc.c5 { --c: 44,110,143; } .lc.c6 { --c: 176,134,28; } .lc.c7 { --c: 122,74,42; }

        .ce-grid { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 40px; align-items: start; margin-top: 28px; }
        .ce-main { display: flex; flex-direction: column; gap: 50px; min-width: 0; }
        .ce-sec { scroll-margin-top: 150px; }
        .ce-label { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .ce-h2 { margin: 0 0 16px; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.2vw + 18px, 32px); font-weight: 700; color: #1d1a16; }
        .ce-text { margin: 0; font-size: 16.5px; line-height: 1.85; color: #3a2f22; white-space: pre-line; }
        .ce-text::first-letter { float: left; margin: 6px 10px 0 0; font-family: 'Playfair Display', serif; font-size: 58px; line-height: .8; color: #b9500a; }
        .ce-origin { margin-top: 18px; padding: 20px 22px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .ce-origin b { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #9a4308; }
        .ce-origin p { margin: 8px 0 0; font-size: 15px; line-height: 1.7; color: #3a2f22; }
        .ce-periods { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 16px; }
        .ce-periods span { display: inline-flex; align-items: center; gap: 7px; height: 34px; padding: 0 14px; border-radius: 999px; background: #e7f3ec; color: #1b6b3a; font-size: 13.5px; font-weight: 700; }

        .ce-practical { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .ce-pr { display: flex; flex-direction: column; gap: 12px; padding: 20px; border-radius: 22px; background: rgba(var(--c), .07); border: 1px solid rgba(var(--c), .18); }
        .ce-pr > i { width: 44px; height: 44px; border-radius: 14px; background: rgb(var(--c)); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; }
        .ce-pr small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: rgb(var(--c)); }
        .ce-pr b { display: block; margin-top: 4px; font-size: 15.5px; line-height: 1.4; color: #1d1a16; }

        .ce-people { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .ce-people a { display: flex; align-items: center; gap: 14px; padding: 12px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .ce-people a:hover { border-color: #d8cdb8; box-shadow: 0 16px 30px -24px rgba(29,26,22,.5); }
        .ce-people__img { flex: none; width: 64px; height: 64px; border-radius: 18px; overflow: hidden; background: rgba(var(--c), .12); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-size: 24px; font-weight: 700; }
        .ce-people__img img { width: 100%; height: 100%; object-fit: cover; }
        .ce-people b { display: block; font-family: 'Playfair Display', serif; font-size: 18px; color: #1d1a16; }
        .ce-people em { display: inline-flex; margin-top: 4px; padding: 3px 10px; border-radius: 999px; background: rgba(var(--c), .12); color: rgb(var(--c)); font-style: normal; font-size: 12px; font-weight: 700; }
        .ce-cities { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 14px; }
        .ce-cities a { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #ece5d6; color: #3a2f22; font-size: 13.5px; font-weight: 600; text-decoration: none; }
        .ce-cities a:hover { border-color: #b9500a; color: #9a4308; }

        .ce-photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-auto-rows: 170px; gap: 10px; }
        .ce-photos button { padding: 0; border: 0; border-radius: 18px; overflow: hidden; background: #e9e1d2; cursor: zoom-in; }
        .ce-photos button:first-child { grid-column: span 2; grid-row: span 2; }
        .ce-photos img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .ce-photos button:hover img { transform: scale(1.04); }
        .ce-video { aspect-ratio: 16 / 9; border-radius: 20px; overflow: hidden; background: #000; margin-top: 14px; }
        .ce-video iframe { width: 100%; height: 100%; border: 0; }
        .ce-cap { margin: 6px 0 0; font-size: 12.5px; color: #6b6458; }
        .ce-audio { display: flex; flex-direction: column; gap: 12px; padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .ce-audio + .ce-audio { margin-top: 12px; }
        .ce-audio header { display: flex; align-items: center; gap: 12px; }
        .ce-audio header i { width: 44px; height: 44px; border-radius: 14px; background: #fdf4ec; color: #b9500a; display: flex; align-items: center; justify-content: center; }
        .ce-audio audio { width: 100%; }

        .ce-sources { padding: 22px; border-radius: 24px; background: #123a25; color: #fff; }
        .ce-sources h3 { margin: 0 0 12px; display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #f6c38f; }
        .ce-sources ol { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; counter-reset: s; }
        .ce-sources li { counter-increment: s; display: grid; grid-template-columns: 26px minmax(0, 1fr); gap: 10px; font-size: 13.5px; line-height: 1.45; }
        .ce-sources li::before { content: counter(s); width: 26px; height: 26px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f6c38f; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; }
        .ce-sources a { color: #fff; text-decoration: underline; text-decoration-color: rgba(255,255,255,.35); text-underline-offset: 3px; overflow-wrap: anywhere; }
        .ce-sources p { margin: 0; font-size: 13.5px; color: #cfe6d7; }

        /* Fiche collante */
        .ce-card { position: sticky; top: 140px; border-radius: 24px; background: #fff; border: 1px solid #e6dccb; overflow: hidden; }
        .ce-card__head { padding: 18px 20px 14px; border-bottom: 1px solid #f0eadf; }
        .ce-card__head small { display: block; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8a7f6b; }
        .ce-card__head b { display: block; margin-top: 4px; font-family: 'Playfair Display', serif; font-size: 22px; color: #1d1a16; }
        .ce-status { display: flex; align-items: center; gap: 12px; margin-top: 12px; padding: 12px 14px; border-radius: 16px; }
        .ce-status i { width: 36px; height: 36px; border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: center; }
        .ce-status small { display: block; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; opacity: .8; }
        .ce-status b { display: block; font-size: 15px; }
        .ce-status.is-stable { background: #e7f3ec; color: #1b6b3a; }
        .ce-status.is-vulnerable { background: #fdf0e1; color: #b45309; }
        .ce-status.is-en_danger { background: #fdecea; color: #b42318; }
        .ce-status.is-disparu { background: #efedea; color: #57534e; }
        .ce-gauge { display: grid; grid-template-columns: repeat(4, 1fr); gap: 4px; margin-top: 10px; }
        .ce-gauge span { height: 6px; border-radius: 999px; background: #ece5d6; }
        .ce-gauge span.is-on { background: currentColor; }
        .ce-card__body { padding: 16px 20px 20px; display: flex; flex-direction: column; gap: 10px; }
        .ce-card__body .ce-btn { width: 100%; }
        .ce-unesco { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 16px; background: #e3f3f1; color: #1f8079; font-size: 14px; font-weight: 700; }
        .ce-card__list { margin: 4px 0 0; padding: 12px 0 0; list-style: none; border-top: 1px solid #f0eadf; display: flex; flex-direction: column; gap: 8px; }
        .ce-card__list a { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 10px 12px; border-radius: 14px; background: #f6f3ed; color: #1d1a16; font-size: 14px; font-weight: 600; text-decoration: none; }
        .ce-card__list a:hover { background: #fdf4ec; color: #9a4308; }

        /* À découvrir aussi */
        .ce-related { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .ce-rel { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .ce-rel:hover { border-color: #d8cdb8; box-shadow: 0 16px 30px -24px rgba(29,26,22,.5); }
        .ce-rel__img { position: relative; aspect-ratio: 4 / 3; background: rgba(var(--c), .1); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-size: 30px; overflow: hidden; }
        .ce-rel__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ce-rel__body { padding: 12px 14px 14px; }
        .ce-rel__body small { display: block; font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: rgb(var(--c)); }
        .ce-rel__body b { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; margin-top: 4px; font-family: 'Playfair Display', serif; font-size: 16.5px; line-height: 1.25; color: #1d1a16; }

        #lightbox { background: rgba(14,12,9,.94) !important; }

        @media (max-width: 1023px) {
            .ce-hero { grid-template-columns: minmax(0, 1fr); gap: 18px; }
            .ce-cover { order: -1; }
            .ce-grid { grid-template-columns: minmax(0, 1fr); }
            .ce-card { position: static; }
            .ce-related { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 640px) {
            .ce { padding: 16px 16px 48px; }
            .ce-hero { padding: 14px 16px 44px; }
            .ce-hero__txt { padding: 18px 16px; border-radius: 22px; }
            .ce-lead { font-size: 17px; }
            .ce-actions .ce-btn { flex: 1 1 auto; }
            .ce-actions .ce-btn.is-icon { flex: none; }
            .ce-cover { border-radius: 22px; }
            .ce-practical, .ce-people { grid-template-columns: minmax(0, 1fr); }
            .ce-photos { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: 130px; }
        }
        @media (prefers-reduced-motion: reduce) { .ce-photos img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="min-h-screen">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $photos = $element->media->where('type', 'photo')->values();
    $videos = $element->media->where('type', 'video')->values();
    $audios = $element->media->where('type', 'audio')->values();
    $heroMedia = $photos->first();
    $heroUrl = $heroMedia?->url ?: ($element->cover_image ?: $element->thumbnail);
    $riskMap = [
        'stable' => ['label' => __('Stable'), 'pill' => 'is-green', 'lvl' => 1],
        'vulnerable' => ['label' => __('Vulnérable'), 'pill' => 'is-warn', 'lvl' => 2],
        'en_danger' => ['label' => __('En danger'), 'pill' => 'is-danger', 'lvl' => 3],
        'disparu' => ['label' => __('Disparu'), 'pill' => 'is-gone', 'lvl' => 4],
    ];
    $risk = $element->niveau_risque && isset($riskMap[$element->niveau_risque]) ? $element->niveau_risque : null;
    // Peuple d'origine en premier (rôle contenant « origine »), pour le fil d'Ariane et les motifs
    $originPeople = $peoples->first(fn ($p) => str_contains(mb_strtolower((string) $element->getRoleForPeople($p->id)), 'origine')) ?? $peoples->first();
    $culture = \App\Support\IvorianCulture::forPeople($originPeople?->slug);
    $sources = $element->sources ?? [];
@endphp

<section class="ce-hero-wrap">
    @include('partials.ivorian-motifs', ['culture' => $culture['key']])
    <div style="position:relative; z-index:1; max-width:1180px; margin:0 auto; padding:20px 20px 0">
        <ol class="ce-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
            <li><a href="{{ $originPeople ? route('cultural.people', $originPeople->slug) : route('cultural.peoples') }}"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ $originPeople?->name ?? __('Cultures') }}</a></li>
            <li class="sep" aria-hidden="true">/</li>
            <li><a href="{{ route('cultural.peoples') }}">{{ __('Cultures') }}</a></li>
            @if($element->domain)<li class="sep" aria-hidden="true">/</li><li>{{ $element->domain->name }}</li>@endif
            <li class="sep" aria-hidden="true">/</li>
            <li aria-current="page">{{ $element->name }}</li>
        </ol>
    </div>
    <header class="ce-hero">
        <div class="ce-hero__txt">
            <div class="ce-pills">
                @if($element->domain)<span class="ce-pill"><i class="{{ $element->domain->icon ?: 'fas fa-star' }}" aria-hidden="true"></i>{{ $element->domain->name }}</span>@endif
                @if($element->is_featured)<span class="ce-pill is-green"><x-icon name="star" />{{ __('À la une') }}</span>@endif
                @if($risk && $risk !== 'stable')<span class="ce-pill {{ $riskMap[$risk]['pill'] }}"><x-icon name="danger-circle" />{{ $riskMap[$risk]['label'] }}</span>@endif
                @if($element->unesco_status)<span class="ce-pill is-green"><x-icon name="global" />UNESCO</span>@endif
            </div>
            <h1 class="ce-title">{{ $element->name }}</h1>
            @if($element->short_description)<p class="ce-lead">{{ $element->localized('short_description') }}</p>@endif
            <ul class="ce-meta">
                @if($peoples->isNotEmpty())<li><x-icon name="users-group-rounded" />{{ $peoples->pluck('name')->implode(', ') }}</li>@endif
                @if($cities->isNotEmpty())<li><x-icon name="map-point" />{{ $cities->pluck('name')->implode(', ') }}</li>@endif
                <li><x-icon name="eye" />{{ number_format($element->views_count, 0, ',', ' ') }} {{ __('vues') }}</li>
            </ul>
            <div class="ce-actions">
                <a href="#presentation" class="ce-btn is-main"><x-icon name="book" />{{ __('Lire la présentation') }}</a>
                @if(! empty($element->practical_info))<a href="#pratique" class="ce-btn is-line"><x-icon name="info-circle" />{{ __('En pratique') }}</a>@endif
                <button type="button" class="ce-btn is-icon" data-ce-share data-done="{{ __('Lien copié') }}" aria-label="{{ __('Partager') }}" title="{{ __('Partager') }}"><x-icon name="share" /></button>
            </div>
        </div>
        <div class="ce-cover">
            @if($heroUrl)
                <img id="mainPhoto" src="{{ $heroUrl }}" alt="{{ $heroMedia?->alt_text ?: $element->name }}" decoding="async" fetchpriority="high" onclick="openLightbox(this.currentSrc || this.src, @js($element->name))">
                @if($photos->count() > 1)
                    <div class="ce-thumbs">
                        @foreach($photos->take(4) as $m)
                            <button type="button" onclick="changeHeroPhoto(@js($m->url))" data-photo="{{ $m->url }}" class="{{ $loop->first ? 'is-on' : '' }}" aria-label="{{ __('Photo :n', ['n' => $loop->iteration]) }}"><img src="{{ $m->url }}" alt=""></button>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="ce-cover__empty lc c1">
                    <i class="{{ $element->domain?->icon ?: 'fas fa-masks-theater' }}" aria-hidden="true"></i>
                    <b>{{ $element->domain?->name ?? __('Patrimoine culturel') }}</b>
                    <span>{{ __('Photo à venir') }}</span>
                </div>
            @endif
        </div>
    </header>
</section>

<main class="ce">
    {{-- ══ INFOS CLÉS ══ --}}
    <ul class="ox-keys">
        @if($element->domain)<li><x-icon name="widget" /><span><small>{{ __('Domaine') }}</small><b>{{ $element->domain->name }}</b></span></li>@endif
        @if($originPeople)<li><x-icon name="users-group-rounded" /><span><small>{{ __('Origine') }}</small><b>{{ $originPeople->name }}</b></span></li>@endif
        @if($risk)<li class="{{ $risk === 'stable' ? 'is-ok' : '' }}"><x-icon name="shield-check" /><span><small>{{ __('Préservation') }}</small><b>{{ $riskMap[$risk]['label'] }}</b></span></li>@endif
        @if($element->unesco_status)<li class="is-ok"><x-icon name="global" /><span><small>UNESCO</small><b>{{ $element->unesco_status }}</b></span></li>@endif
        <li><x-icon name="eye" /><span><small>{{ __('Consultations') }}</small><b>{{ number_format($element->views_count, 0, ',', ' ') }}</b></span></li>
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        <a href="#presentation">{{ __('Présentation') }}</a>
        @if(! empty($element->practical_info))<a href="#pratique">{{ __('En pratique') }}</a>@endif
        @if($photos->count() > 1 || $videos->isNotEmpty())<a href="#galerie">{{ __('Galerie') }}</a>@endif
        @if($audios->isNotEmpty())<a href="#audio">{{ __('Audio') }}</a>@endif
        @if($peoples->isNotEmpty() || $cities->isNotEmpty())<a href="#peuples">{{ __('Peuples & lieux') }}</a>@endif
        <a href="#sources">{{ __('Sources') }}</a>
        @if($related->isNotEmpty())<a href="#a-decouvrir">{{ __('À découvrir aussi') }}</a>@endif
    </nav>

    <div class="ce-grid">
        <div class="ce-main">
            {{-- Présentation --}}
            <section class="ox-sec ce-sec" id="presentation">
                <p class="ce-label">{{ __('Présentation') }}</p>
                <h2 class="ce-h2">{{ __('À propos de :e', ['e' => $element->name]) }}</h2>
                <p class="ce-text">{{ $element->localized('description') ?: ($element->localized('short_description') ?: __('La présentation de cet élément sera bientôt documentée.')) }}</p>
                @if($element->origine_historique)
                    <div class="ce-origin"><b><x-icon name="history" />{{ __('Origine historique') }}</b><p>{{ $element->origine_historique }}</p></div>
                @endif
                @if(! empty($element->meilleure_periode))
                    <div class="ce-periods" aria-label="{{ __('Meilleure période') }}">
                        @foreach($element->meilleure_periode as $periode)<span><x-icon name="calendar" />{{ $periode }}</span>@endforeach
                    </div>
                @endif
            </section>

            {{-- En pratique --}}
            @if(! empty($element->practical_info))
                <section class="ox-sec ce-sec" id="pratique">
                    <p class="ce-label">{{ __('En pratique') }}</p>
                    <h2 class="ce-h2">{{ __('Bon à savoir') }}</h2>
                    <div class="ce-practical">
                        @foreach($element->practical_info as $info)
                            <div class="ce-pr lc c{{ ($loop->index % 7) + 1 }}">
                                <i class="{{ $info['icon'] ?? 'fas fa-circle-info' }}" aria-hidden="true"></i>
                                <span><small>{{ $info['label'] ?? '' }}</small><b>{{ $info['value'] ?? '' }}</b></span>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            {{-- Galerie --}}
            @if($photos->count() > 1 || $videos->isNotEmpty())
                <section class="ox-sec ce-sec" id="galerie">
                    <p class="ce-label">{{ __('Galerie') }}</p>
                    <h2 class="ce-h2">{{ __('En images') }}</h2>
                    @if($photos->count() > 1)
                        <div class="ce-photos">
                            @foreach($photos as $photo)
                                <button type="button" onclick="openLightbox(@js($photo->url), @js($photo->caption ?? $element->name))" aria-label="{{ __('Agrandir la photo :n', ['n' => $loop->iteration]) }}"><img src="{{ $photo->url }}" alt="{{ $photo->alt_text ?: $element->name }}" loading="lazy"></button>
                            @endforeach
                        </div>
                    @endif
                    @foreach($videos as $video)
                        <div class="ce-video"><iframe src="{{ $video->url }}" allow="autoplay; encrypted-media" allowfullscreen loading="lazy" title="{{ $video->caption ?: $element->name }}"></iframe></div>
                        @if($video->caption)<p class="ce-cap">{{ $video->caption }}</p>@endif
                    @endforeach
                </section>
            @endif

            {{-- Audio --}}
            @if($audios->isNotEmpty())
                <section class="ox-sec ce-sec" id="audio">
                    <p class="ce-label">{{ __('Écouter') }}</p>
                    <h2 class="ce-h2">{{ __('Enregistrements audio') }}</h2>
                    @foreach($audios as $audio)
                        <div class="ce-audio">
                            <header><i class="fas fa-music" aria-hidden="true"></i><span><b class="block text-sm">{{ $audio->caption ?: $element->name }}</b>@if($audio->duree_secondes)<small class="text-xs" style="color:#6b6458">{{ gmdate('i:s', $audio->duree_secondes) }}</small>@endif</span></header>
                            <audio controls preload="none"><source src="{{ $audio->url }}"></audio>
                        </div>
                    @endforeach
                </section>
            @endif

            {{-- Peuples & lieux --}}
            @if($peoples->isNotEmpty() || $cities->isNotEmpty())
                <section class="ox-sec ce-sec" id="peuples">
                    <p class="ce-label">{{ __('Peuples & lieux') }}</p>
                    <h2 class="ce-h2">{{ __('Qui le pratique ?') }}</h2>
                    @if($peoples->isNotEmpty())
                        <div class="ce-people">
                            @foreach($peoples as $p)
                                @php $role = $element->getRoleForPeople($p->id); $pImg = $p->thumbnail ?: ($p->cover_images[0] ?? null); @endphp
                                <a href="{{ route('cultural.people', $p->slug) }}" class="lc c{{ ($loop->index % 7) + 1 }}">
                                    <span class="ce-people__img">@if($pImg)<img src="{{ $pImg }}" alt="{{ $p->name }}" loading="lazy">@else{{ mb_substr($p->name, 0, 1) }}@endif</span>
                                    <span class="min-w-0"><b>{{ $p->name }}</b>@if($role)<em>{{ ucfirst($role) }}</em>@endif</span>
                                </a>
                            @endforeach
                        </div>
                    @endif
                    @if($cities->isNotEmpty())
                        <div class="ce-cities">@foreach($cities as $city)<a href="{{ route('tourist.city', $city->slug) }}"><x-icon name="map-point" />{{ $city->name }}</a>@endforeach</div>
                    @endif
                </section>
            @endif

            {{-- Sources --}}
            <section class="ox-sec ce-sec" id="sources">
                <div class="ce-sources">
                    <h3><x-icon name="book" />{{ __('Sources') }}</h3>
                    @if(! empty($sources))
                        <ol>@foreach($sources as $src)<li><span>@if(! empty($src['url']))<a href="{{ $src['url'] }}" target="_blank" rel="noopener noreferrer">{{ $src['label'] ?? $src['url'] }}</a>@else{{ $src['label'] ?? '' }}@endif</span></li>@endforeach</ol>
                    @else
                        <p>{{ __('Sources en cours de documentation.') }}</p>
                    @endif
                </div>
            </section>
        </div>

        {{-- ══ FICHE PATRIMOINE ══ --}}
        <aside class="ce-card" aria-label="{{ __('Fiche patrimoine') }}">
            <div class="ce-card__head">
                <small>{{ __('Fiche patrimoine') }}</small>
                <b>{{ $element->domain?->name ?? __('Patrimoine culturel') }}</b>
                @if($risk)
                    <div class="ce-status is-{{ $risk }}">
                        <i><x-icon name="shield-check" /></i>
                        <span><small>{{ __('Niveau de préservation') }}</small><b>{{ $riskMap[$risk]['label'] }}</b></span>
                    </div>
                    <div class="ce-gauge is-{{ $risk }}" style="color: {{ ['stable' => '#1b6b3a', 'vulnerable' => '#d97706', 'en_danger' => '#b42318', 'disparu' => '#57534e'][$risk] }}" aria-hidden="true">
                        @for($g = 1; $g <= 4; $g++)<span class="{{ $g <= $riskMap[$risk]['lvl'] ? 'is-on' : '' }}"></span>@endfor
                    </div>
                @endif
            </div>
            <div class="ce-card__body">
                @if($element->unesco_status)<div class="ce-unesco"><x-icon name="global" />{{ $element->unesco_status }}</div>@endif
                @if($element->website)<a href="{{ $element->website }}" target="_blank" rel="noopener" class="ce-btn is-line"><x-icon name="global" />{{ __('Site de référence') }}</a>@endif
                @if($peoples->isNotEmpty())
                    <ul class="ce-card__list">
                        @foreach($peoples as $p)<li><a href="{{ route('cultural.people', $p->slug) }}"><span>{{ $p->name }}</span><span aria-hidden="true">›</span></a></li>@endforeach
                    </ul>
                @endif
                <a href="{{ route('cultural.peoples') }}" class="ce-btn is-main" style="height:44px"><x-icon name="users-group-rounded" />{{ __('Toutes les cultures') }}</a>
            </div>
        </aside>
    </div>

    {{-- ══ À DÉCOUVRIR AUSSI ══ --}}
    @if($related->isNotEmpty())
        <section class="ox-sec ce-sec" id="a-decouvrir" style="margin-top:56px">
            <p class="ce-label">{{ __('Dans le même domaine') }}</p>
            <h2 class="ce-h2">{{ __('À découvrir aussi') }}</h2>
            <div class="ce-related">
                @foreach($related as $r)
                    @php $rImg = $r->media->where('type', 'photo')->first()?->url ?: $r->thumbnail; @endphp
                    <a href="{{ route('cultural.element', $r->slug) }}" class="ce-rel lc c{{ ($loop->index % 7) + 1 }}">
                        <span class="ce-rel__img"><i class="{{ $r->domain?->icon ?: 'fas fa-masks-theater' }}" aria-hidden="true"></i>@if($rImg)<img src="{{ $rImg }}" alt="{{ $r->name }}" loading="lazy" onerror="this.remove()">@endif</span>
                        <span class="ce-rel__body">@if($r->domain)<small>{{ $r->domain->name }}</small>@endif<b>{{ $r->name }}</b></span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif
</main>

{{-- Visionneuse --}}
<div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center p-4" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[88vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    <p id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-sm text-center px-4"></p>
</div>

@include('partials.homepage-footer')

<script>
function changeHeroPhoto(url) {
    const img = document.getElementById('mainPhoto');
    if (img) img.src = url;
    document.querySelectorAll('[data-photo]').forEach(btn => btn.classList.toggle('is-on', btn.dataset.photo === url));
}
function openLightbox(url, caption) {
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-caption').textContent = caption || '';
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });
document.querySelector('[data-ce-share]')?.addEventListener('click', async e => {
    const b = e.currentTarget, data = { title: document.title, url: location.href.split('#')[0] };
    try {
        if (navigator.share) { await navigator.share(data); return; }
        await navigator.clipboard.writeText(data.url);
        b.title = b.dataset.done;
    } catch (err) {}
});
</script>
</body>
</html>
