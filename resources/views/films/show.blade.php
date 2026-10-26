@php
    $kindIcons = ['documentaire' => 'videocamera-record', 'fiction' => 'clapperboard-play'];
    $trailer = $film->trailer();
    $screenings = $film->upcomingScreenings();
    $cast = collect(preg_split('/\R|,/', (string) $film->cast))->map(fn ($s) => trim($s))->filter()->values();
    $awards = collect(preg_split('/\R/', (string) $film->awards))->map(fn ($s) => trim($s))->filter()->values();
    $facts = collect([
        ['calendar', __('Année'), $film->year],
        ['clock-circle', __('Durée'), $film->durationLabel()],
        ['tag-price', __('Genre'), $film->genre],
        ['global', __('Pays'), $film->country],
        ['chat-round-dots', __('Langue'), $film->language],
        ['buildings', __('Production'), $film->production],
    ])->filter(fn ($f) => filled($f[2]));
    // Balisage schema.org Movie (Google : name + image requis ; director, dateCreated, duration recommandés).
    $ld = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Movie',
        'name' => $film->title,
        'url' => route('films.show', $film),
        'image' => $film->posterUrl() ? url($film->posterUrl()) : ($film->backdropUrl() ? url($film->backdropUrl()) : null),
        'description' => $film->synopsis ? \Illuminate\Support\Str::limit(strip_tags($film->synopsis), 300) : null,
        'genre' => array_values(array_filter([$film->kindLabel(), $film->genre])),
        'director' => $film->director ? array_filter(['@type' => 'Person', 'name' => $film->director, 'image' => $film->directorPhotoUrl() ? url($film->directorPhotoUrl()) : null]) : null,
        'actor' => $cast->take(10)->map(fn ($n) => ['@type' => 'Person', 'name' => $n])->all() ?: null,
        'productionCompany' => $film->production ? ['@type' => 'Organization', 'name' => $film->production] : null,
        'countryOfOrigin' => $film->country ? ['@type' => 'Country', 'name' => $film->country] : null,
        'inLanguage' => $film->language,
        'dateCreated' => $film->year ? (string) $film->year : null,
        'duration' => $film->isoDuration(),
        'trailer' => $trailer ? array_filter(['@type' => 'VideoObject', 'name' => __('Bande-annonce').' — '.$film->title, 'embedUrl' => $trailer['embed'], 'thumbnailUrl' => $trailer['thumb'], 'description' => $film->tagline ?: $film->title, 'uploadDate' => optional($film->published_at)->toAtomString()]) : null,
    ]);
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $film->title }}{{ $film->year ? ' ('.$film->year.')' : '' }} — {{ __('Cinéma d\'Ivoire') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', [
        'title' => $film->title.($film->year ? ' ('.$film->year.')' : ''),
        'description' => $film->tagline ?: $film->synopsis,
        'image' => $film->backdropUrl(),
        'type' => 'video.movie',
        'url' => route('films.show', $film),
    ])
    <script type="application/ld+json">{!! json_encode($ld, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .cf { --night: #006B3F; --gold: #EB6E00; padding-bottom: 72px; color: #0E3B2A; }
        .cf-wrap { max-width: 1240px; margin: 0 auto; padding: 0 20px; }
        .cf .ico { flex: none; }
        .cf .ico > svg { width: 100%; height: 100%; }
        .cf span > .ico, .cf a > .ico, .cf button > .ico, .cf dt > .ico, .cf div > .ico { margin: 0; border: 0; border-radius: 0; background: none; }
        .cf a:focus-visible, .cf button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .cf-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 24px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; border: 0; cursor: pointer; transition: background .15s ease; white-space: nowrap; }
        .cf-btn:hover { background: #B85600; }
        .cf-btn .ico { width: 18px; height: 18px; }
        .cf-btn.is-ghost { background: rgba(255,255,255,.13); color: #fff; }
        .cf-btn.is-ghost:hover { background: rgba(255,255,255,.22); }

        /* Héros : fond paysage sous voile uni, affiche à gauche, informations clés à droite */
        .cf-hero { position: relative; overflow: hidden; background: var(--night); color: #fff; }
        .cf-hero .ivm-g { opacity: .07 !important; }
        .cf-hero .ivm-g > * { stroke: #F7C396 !important; }
        .cf-hero__bg { position: absolute; inset: 0; }
        .cf-hero__bg img { width: 100%; height: 100%; object-fit: cover; opacity: .3; }
        .cf-hero__bg::after { content: ""; position: absolute; inset: 0; background: rgba(0,107,63,.6); }
        .cf-hero__in { position: relative; z-index: 1; display: grid; grid-template-columns: 280px minmax(0, 1fr); gap: 40px; align-items: end; max-width: 1240px; margin: 0 auto; padding: 40px 20px 46px; }
        .cf-crumbs { display: flex; flex-wrap: wrap; gap: 6px; margin: 0 0 14px; padding: 0; list-style: none; font-size: 13px; color: #8AA99B; }
        .cf-crumbs a { color: #F3F8F5; text-decoration: none; }
        .cf-crumbs a:hover { text-decoration: underline; }
        .cf-crumbs li + li::before { content: "/"; margin-right: 6px; color: #8AA99B; }
        .cf-poster { aspect-ratio: 2 / 3; border-radius: 18px; overflow: hidden; background: #00874F; border: 4px solid rgba(255,255,255,.9); display: flex; align-items: center; justify-content: center; color: var(--gold); }
        .cf-poster img { width: 100%; height: 100%; object-fit: cover; }
        .cf-poster > .ico { width: 54px; height: 54px; }
        .cf-tags { display: flex; flex-wrap: wrap; gap: 8px; }
        .cf-tag { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: rgba(255,255,255,.12); color: var(--gold); font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .cf-tag .ico { width: 14px; height: 14px; }
        .cf-tag.is-sponsored { color: #fff; background: rgba(255,255,255,.08); border: 1px solid rgba(255,255,255,.25); text-transform: none; letter-spacing: 0; font-weight: 700; }
        .cf-h1 { margin: 14px 0 0; font-family: 'Montserrat', serif; font-size: clamp(32px, 4.6vw, 58px); font-weight: 700; line-height: 1.04; color: #fff; }
        .cf-tagline { margin: 10px 0 0; max-width: 60ch; font-size: 18px; line-height: 1.5; color: #F7C396; font-style: italic; }
        .cf-line { display: flex; flex-wrap: wrap; gap: 6px 18px; margin: 16px 0 0; padding: 0; list-style: none; font-size: 14.5px; color: #D8E9E0; }
        .cf-line li { display: inline-flex; align-items: center; gap: 7px; }
        /* Pastilles d'icônes (modèle du site) — variante fond sombre */
        .cf-line li > .ico { width: 28px; height: 28px; margin: 0; border-radius: 32%; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.18); color: #F7C396; }
        .cf-line li > .ico > svg { width: 15px; height: 15px; }
        .cf-line b { color: #fff; font-weight: 700; }
        .cf-cta { grid-column: 2; align-self: start; display: flex; flex-wrap: wrap; gap: 10px; margin-top: -16px; }
        .cf-poster { grid-row: 2 / span 2; }

        /* Corps */
        .cf-body { display: grid; grid-template-columns: minmax(0, 1fr) 340px; gap: 36px; padding-top: 36px; align-items: start; }
        .cf-sec + .cf-sec { margin-top: 34px; }
        .cf-h2 { display: flex; align-items: center; gap: 10px; margin: 0 0 14px; font-family: 'Montserrat', serif; font-size: clamp(21px, 2.2vw, 26px); font-weight: 700; color: #0E3B2A; }
        .cf-prose { font-size: 16.5px; line-height: 1.75; color: #24503D; white-space: pre-line; }

        /* Bande-annonce : façade légère, le lecteur n'est chargé qu'au clic (mode confidentialité renforcée) */
        .cf-trailer { position: relative; aspect-ratio: 16 / 9; border-radius: 22px; overflow: hidden; background: #006B3F; }
        .cf-trailer iframe { position: absolute; inset: 0; width: 100%; height: 100%; border: 0; }
        .cf-trailer__btn { position: absolute; inset: 0; width: 100%; padding: 0; border: 0; cursor: pointer; background: #006B3F; color: #fff; }
        .cf-trailer__btn img { width: 100%; height: 100%; object-fit: cover; opacity: .85; transition: opacity .2s ease; }
        .cf-trailer__btn:hover img { opacity: 1; }
        .cf-trailer__play { position: absolute; left: 50%; top: 50%; translate: -50% -50%; display: inline-flex; align-items: center; gap: 10px; height: 58px; padding: 0 26px 0 20px; border-radius: 999px; background: #EB6E00; font-size: 16px; font-weight: 800; transition: background .15s ease; }
        .cf-trailer__btn:hover .cf-trailer__play { background: #B85600; }
        .cf-trailer__play .ico { width: 24px; height: 24px; }
        .cf-trailer__note { margin: 8px 2px 0; font-size: 12.5px; color: #557466; }

        .cf-people { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 10px; margin: 0; padding: 0; list-style: none; }
        .cf-people li { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 14px; background: #fff; border: 1px solid #D8E9E0; font-size: 14.5px; font-weight: 600; color: #0E3B2A; }
        .cf-people .cf-av { width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: #F3F8F5; color: #EB6E00; font-size: 13px; font-weight: 800; flex: none; }
        .cf-dir { display: grid; grid-template-columns: 54px minmax(0, 1fr); gap: 14px; padding: 18px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; }
        .cf-dir .cf-av { width: 54px; height: 54px; font-size: 18px; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: #F3F8F5; color: #00874F; font-weight: 800; }
        .cf-dir:has(.cf-av--photo) { grid-template-columns: 96px minmax(0, 1fr); gap: 18px; align-items: center; }
        .cf-dir .cf-av--photo { width: 96px; height: 96px; border-radius: 999px; object-fit: cover; background: #D8E9E0; border: 3px solid #fff; box-shadow: 0 0 0 1px #D8E9E0; }
        .cf-dir h3 { margin: 0; font-size: 17px; color: #0E3B2A; }
        .cf-dir small { font-size: 12.5px; color: #557466; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; }
        .cf-dir p { margin: 8px 0 0; font-size: 15px; line-height: 1.65; color: #24503D; white-space: pre-line; }
        .cf-awards { margin: 0; padding: 0; list-style: none; display: grid; gap: 8px; }
        .cf-awards li { display: flex; gap: 12px; align-items: center; padding: 10px 14px 10px 10px; border-radius: 14px; background: #fff; border: 1px solid #D8E9E0; color: #0E3B2A; font-size: 14.5px; font-weight: 600; line-height: 1.45; }
        .cf-awards li > .ico { width: 32px; height: 32px; margin: 0; border-radius: 32%; background: var(--ti-ic-tint); border: 1px solid color-mix(in srgb, var(--ti-ic) 22%, transparent); color: var(--ti-ic); }
        .cf-awards li > .ico > svg { width: 17px; height: 17px; }

        /* Colonne : fiche technique, projections, contact */
        .cf-aside { position: sticky; top: 88px; display: grid; gap: 16px; }
        .cf-box { padding: 20px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
        .cf-box h2 { margin: 0 0 12px; font-size: 15px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; color: #557466; }
        .cf-facts { margin: 0; display: grid; gap: 12px; }
        .cf-facts div { display: grid; grid-template-columns: 34px minmax(0, 1fr); gap: 10px; align-items: center; }
        .cf-facts dt { grid-row: span 2; width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; }
        .cf-facts dt .ico { width: 17px; height: 17px; }
        .cf-facts dd { margin: 0; font-size: 14.5px; font-weight: 700; color: #0E3B2A; line-height: 1.3; }
        .cf-facts dd small { display: block; font-size: 12px; font-weight: 600; color: #557466; }
        .cf-scr { margin: 0; padding: 0; list-style: none; display: grid; gap: 10px; }
        .cf-scr li { display: grid; grid-template-columns: 52px minmax(0, 1fr); gap: 12px; align-items: center; }
        .cf-date { display: flex; flex-direction: column; align-items: center; justify-content: center; height: 56px; border-radius: 14px; background: #F3F8F5; color: #00874F; line-height: 1; }
        .cf-date b { font-size: 20px; }
        .cf-date small { margin-top: 3px; font-size: 11px; font-weight: 800; text-transform: uppercase; }
        .cf-scr p { margin: 0; font-size: 14px; font-weight: 700; color: #0E3B2A; line-height: 1.35; }
        .cf-scr p small { display: block; font-weight: 600; color: #557466; }
        .cf-scr a { font-size: 13px; font-weight: 700; color: #B85600; }
        .cf-watch { display: grid; gap: 10px; }
        .cf-watch .cf-btn { width: 100%; }
        .cf-watch p { margin: 0; font-size: 12.5px; line-height: 1.5; color: #557466; }
        .cf-share { display: flex; gap: 8px; }
        .cf-share a, .cf-share button { flex: 1 1 0; display: inline-flex; align-items: center; justify-content: center; gap: 6px; height: 40px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; color: #0E3B2A; font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .cf-share a:hover, .cf-share button:hover { border-color: #F7C396; background: #F3F8F5; }
        .cf-share .ico { width: 16px; height: 16px; }
        .cf-legal { font-size: 12.5px; line-height: 1.55; color: #557466; }
        .cf-legal p { margin: 0 0 6px; }
        .cf-legal a { color: #B85600; font-weight: 700; }

        /* À voir aussi */
        .cf-more { margin-top: 48px; }
        .cf-rel { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 20px; }
        .cf-rel a { display: flex; flex-direction: column; text-decoration: none; color: inherit; min-width: 0; }
        .cf-rel__p { aspect-ratio: 2 / 3; border-radius: 16px; overflow: hidden; background: #00874F; border: 1px solid #D8E9E0; display: flex; align-items: center; justify-content: center; color: var(--gold); transition: transform .2s ease, box-shadow .2s ease; }
        .cf-rel__p img { width: 100%; height: 100%; object-fit: cover; }
        .cf-rel__p .ico { width: 34px; height: 34px; }
        .cf-rel a:hover .cf-rel__p { transform: translateY(-4px); box-shadow: 0 22px 36px -24px rgba(0,107,63,.6); }
        .cf-rel h3 { margin: 10px 2px 0; font-size: 15px; font-weight: 700; line-height: 1.3; color: #0E3B2A; }
        .cf-rel small { margin: 3px 2px 0; font-size: 12.5px; color: #557466; }

        @media (max-width: 1023px) {
            .cf-hero__in { grid-template-columns: 200px minmax(0, 1fr); gap: 26px; }
            .cf-body { grid-template-columns: minmax(0, 1fr); }
            .cf-aside { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .cf-rel { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .cf-rel a:nth-child(4) { display: none; }
        }
        @media (max-width: 640px) {
            .cf-wrap { padding: 0 16px; }
            .cf-hero__in { grid-template-columns: 104px minmax(0, 1fr); gap: 14px; padding: 16px 16px 22px; align-items: start; }
            .cf-crumbs { grid-column: 1 / -1; margin: 0; font-size: 11.5px; }
            .cf-poster { grid-row: auto; border-width: 2px; border-radius: 12px; }
            .cf-tags { gap: 5px; }
            .cf-tag { height: 24px; padding: 0 9px; font-size: 10.5px; }
            .cf-h1 { margin-top: 8px; font-size: 24px; }
            .cf-tagline { font-size: 14px; margin-top: 6px; }
            .cf-line { gap: 4px 12px; margin-top: 10px; font-size: 12.5px; }
            .cf-dir:has(.cf-av--photo) { grid-template-columns: 72px minmax(0, 1fr); gap: 14px; }
            .cf-dir .cf-av--photo { width: 72px; height: 72px; }
            .cf-line li > .ico { width: 22px; height: 22px; }
            .cf-line li > .ico > svg { width: 12px; height: 12px; }
            .cf-cta { grid-column: 1 / -1; margin-top: 4px; gap: 8px; }
            .cf-cta .cf-btn { flex: 1 1 auto; height: 44px; padding: 0 16px; font-size: 14px; }
            .cf-body { padding-top: 24px; gap: 24px; }
            .cf-prose { font-size: 15.5px; line-height: 1.7; }
            .cf-trailer { border-radius: 16px; }
            .cf-trailer__play { height: 48px; padding: 0 20px 0 16px; font-size: 14.5px; }
            .cf-aside { grid-template-columns: minmax(0, 1fr); }
            .cf-rel { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
            .cf-rel a:nth-child(n+3) { display: none; }
        }
        @media (prefers-reduced-motion: reduce) { .cf-rel__p, .cf-trailer__btn img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.public-top-nav')

<main class="cf">
    <section class="cf-hero" aria-labelledby="cf-title">
        @if($film->backdropUrl())
            <div class="cf-hero__bg" aria-hidden="true"><img src="{{ $film->backdropUrl() }}" alt="" decoding="async" fetchpriority="high"></div>
        @else
            @include('partials.ivorian-motifs', ['culture' => 'baoule'])
        @endif
        <div class="cf-hero__in">
            <ol class="cf-crumbs" aria-label="{{ __('Fil d\'Ariane') }}" style="grid-column: 1 / -1">
                <li><a href="{{ route('films.index') }}">{{ __('Cinéma d\'Ivoire') }}</a></li>
                <li><a href="{{ route('films.kind', \App\Models\Film::KINDS[$film->kind]['slug'] ?? 'fictions') }}">{{ __(\App\Models\Film::KINDS[$film->kind]['plural'] ?? 'Films') }}</a></li>
            </ol>
            <div class="cf-poster">
                @if($film->posterUrl())<img src="{{ $film->posterUrl() }}" alt="{{ __('Affiche du film :t', ['t' => $film->title]) }}" decoding="async">@else<x-icon name="{{ $kindIcons[$film->kind] ?? 'clapperboard-play' }}" />@endif
            </div>
            <div>
                <div class="cf-tags">
                    <span class="cf-tag"><x-icon name="{{ $kindIcons[$film->kind] ?? 'clapperboard-play' }}" />{{ $film->kindLabel() }}</span>
                    @if($film->genre)<span class="cf-tag">{{ $film->genre }}</span>@endif
                    @if($film->is_sponsored)<span class="cf-tag is-sponsored">{{ __('Contenu sponsorisé') }}</span>@endif
                </div>
                <h1 class="cf-h1" id="cf-title">{{ $film->title }}</h1>
                @if($film->tagline)<p class="cf-tagline">{{ $film->tagline }}</p>@endif
                <ul class="cf-line">
                    @if($film->director)<li><x-icon name="videocamera-record" />{{ __('Réalisé par') }} <b>{{ $film->director }}</b></li>@endif
                    @if($film->year)<li><x-icon name="calendar" />{{ $film->year }}</li>@endif
                    @if($film->durationLabel())<li><x-icon name="clock-circle" />{{ $film->durationLabel() }}</li>@endif
                    @if($film->country)<li><x-icon name="global" />{{ $film->country }}</li>@endif
                </ul>
            </div>
            <div class="cf-cta">
                @if($trailer)<a href="#cf-trailer" class="cf-btn" data-cf-jump><x-icon name="play" />{{ __('Bande-annonce') }}</a>@endif
                @if($film->watch_url)<a href="{{ route('films.watch', $film) }}" class="cf-btn {{ $trailer ? 'is-ghost' : '' }}" target="_blank" rel="noopener nofollow"><x-icon name="tv" />{{ $film->watch_label ?: __('Regarder le film') }}</a>@endif
                <button type="button" class="cf-btn is-ghost" data-cf-share><x-icon name="share" /><span>{{ __('Partager') }}</span></button>
            </div>
        </div>
    </section>

    <div class="cf-wrap">
        <div class="cf-body">
            <div>
                @if($trailer)
                    <section class="cf-sec" id="cf-trailer" style="scroll-margin-top: 90px" aria-labelledby="cf-trailer-title">
                        <h2 class="cf-h2" id="cf-trailer-title">{{ __('Bande-annonce') }}</h2>
                        <div class="cf-trailer" data-cf-trailer data-embed="{{ $trailer['embed'] }}" data-title="{{ __('Bande-annonce') }} — {{ $film->title }}">
                            <button type="button" class="cf-trailer__btn" aria-label="{{ __('Lire la bande-annonce de :t', ['t' => $film->title]) }}">
                                @if($trailer['thumb'] ?? null)<img src="{{ $trailer['thumb'] }}" alt="" loading="lazy" decoding="async">@elseif($film->backdropUrl())<img src="{{ $film->backdropUrl() }}" alt="" loading="lazy" decoding="async">@endif
                                <span class="cf-trailer__play"><x-icon name="play" />{{ __('Lire la bande-annonce') }}</span>
                            </button>
                        </div>
                        <p class="cf-trailer__note">{{ $trailer['provider'] === 'youtube' ? __('Lecteur YouTube en mode confidentialité renforcée, chargé uniquement au clic.') : __('Lecteur Vimeo chargé uniquement au clic.') }}</p>
                    </section>
                @endif

                @if($film->synopsis)
                    <section class="cf-sec" aria-labelledby="cf-syn-title">
                        <h2 class="cf-h2" id="cf-syn-title">{{ __('Synopsis') }}</h2>
                        <div class="cf-prose">{{ $film->synopsis }}</div>
                    </section>
                @endif

                @if($film->director)
                    <section class="cf-sec" aria-labelledby="cf-dir-title">
                        <h2 class="cf-h2" id="cf-dir-title">{{ __('Réalisation') }}</h2>
                        <div class="cf-dir">
                            @if($film->directorPhotoUrl())
                                <img class="cf-av cf-av--photo" src="{{ $film->directorPhotoUrl() }}" alt="{{ __('Portrait de :n', ['n' => $film->director]) }}" loading="lazy" decoding="async">
                            @else
                                <span class="cf-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($film->director, 0, 1)) }}</span>
                            @endif
                            <div>
                                <small>{{ __('Réalisateur') }}</small>
                                <h3>{{ $film->director }}</h3>
                                @if($film->director_bio)<p>{{ $film->director_bio }}</p>@endif
                            </div>
                        </div>
                    </section>
                @endif

                @if($cast->isNotEmpty())
                    <section class="cf-sec" aria-labelledby="cf-cast-title">
                        <h2 class="cf-h2" id="cf-cast-title">{{ $film->kind === 'documentaire' ? __('Intervenants') : __('Distribution') }}</h2>
                        <ul class="cf-people">
                            @foreach($cast as $p)
                                <li><span class="cf-av" aria-hidden="true">{{ mb_strtoupper(mb_substr($p, 0, 1)) }}</span>{{ $p }}</li>
                            @endforeach
                        </ul>
                    </section>
                @endif

                @if($awards->isNotEmpty())
                    <section class="cf-sec" aria-labelledby="cf-aw-title">
                        <h2 class="cf-h2" id="cf-aw-title">{{ __('Prix et sélections') }}</h2>
                        <ul class="cf-awards">
                            @foreach($awards as $a)<li><x-icon name="star-shine" />{{ $a }}</li>@endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="cf-aside" aria-label="{{ __('Informations') }}">
                @if($film->watch_url)
                    <div class="cf-box cf-watch">
                        <h2>{{ __('Où regarder') }}</h2>
                        <a href="{{ route('films.watch', $film) }}" class="cf-btn" target="_blank" rel="noopener nofollow"><x-icon name="tv" />{{ $film->watch_label ?: __('Regarder le film') }}</a>
                        <p>{{ __('Lien officiel fourni par les ayants droit. Vous quittez :s.', ['s' => $siteBrand['site_name']]) }}</p>
                    </div>
                @endif

                @if($facts->isNotEmpty())
                    <div class="cf-box">
                        <h2>{{ __('Fiche technique') }}</h2>
                        <dl class="cf-facts">
                            @foreach($facts as [$icon, $label, $value])
                                <div><dt><x-icon name="{{ $icon }}" /></dt><dd><small>{{ $label }}</small>{{ $value }}</dd></div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                <div class="cf-box">
                    <h2>{{ __('Projections à venir') }}</h2>
                    @if($screenings)
                        <ul class="cf-scr">
                            @foreach($screenings as $s)
                                @php $d = \Illuminate\Support\Carbon::parse($s['date'])->locale(app()->getLocale()); @endphp
                                <li>
                                    <span class="cf-date"><b>{{ $d->format('d') }}</b><small>{{ $d->translatedFormat('M') }}</small></span>
                                    <p>{{ $s['place'] ?: __('Lieu à confirmer') }}<small>{{ $d->translatedFormat('l j F Y') }}{{ $d->format('H:i') !== '00:00' ? ' · '.$d->format('H\hi') : '' }}</small>
                                        @if(! empty($s['url']))<a href="{{ $s['url'] }}" target="_blank" rel="noopener nofollow">{{ __('Réserver') }}</a>@endif
                                    </p>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="cf-legal" style="margin:0">{{ __('Aucune projection annoncée pour le moment.') }}</p>
                    @endif
                </div>

                <div class="cf-box">
                    <h2>{{ __('Partager') }}</h2>
                    <div class="cf-share">
                        <a href="https://wa.me/?text={{ urlencode($film->title.' — '.route('films.show', $film)) }}" target="_blank" rel="noopener"><i class="fa-brands fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('films.show', $film)) }}" target="_blank" rel="noopener"><i class="fa-brands fa-facebook-f" aria-hidden="true"></i>Facebook</a>
                        <button type="button" data-cf-share><x-icon name="link" /><span>{{ __('Copier') }}</span></button>
                    </div>
                </div>

                <div class="cf-box cf-legal">
                    @if($film->credits)<p>{{ __('Visuels') }} : {{ $film->credits }}</p>@endif
                    @if($film->distributor_contact)<p>{{ __('Contact distribution') }} : {{ $film->distributor_contact }}</p>@endif
                    <p>{{ __('Vous êtes ayant droit et souhaitez modifier ou retirer cette fiche ?') }} <a href="{{ route('films.submit') }}#contact">{{ __('Contactez-nous') }}</a></p>
                </div>
            </aside>
        </div>

        @if($related->isNotEmpty())
            <section class="cf-more" aria-labelledby="cf-more-title">
                <h2 class="cf-h2" id="cf-more-title">{{ __('À voir aussi') }}</h2>
                <div class="cf-rel">
                    @foreach($related as $r)
                        <a href="{{ route('films.show', $r) }}">
                            <span class="cf-rel__p">@if($r->posterUrl())<img src="{{ $r->posterUrl() }}" alt="" loading="lazy" decoding="async">@else<x-icon name="{{ $kindIcons[$r->kind] ?? 'clapperboard-play' }}" />@endif</span>
                            <h3>{{ $r->title }}</h3>
                            <small>{{ collect([$r->kindLabel(), $r->year])->filter()->implode(' · ') }}</small>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</main>

@include('partials.homepage-footer')
<script>
(function () {
    var box = document.querySelector('[data-cf-trailer]');
    function play() {
        if (!box || box.querySelector('iframe')) return;
        var f = document.createElement('iframe');
        f.src = box.getAttribute('data-embed');
        f.title = box.getAttribute('data-title');
        f.allow = 'accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture; fullscreen';
        f.allowFullscreen = true;
        f.referrerPolicy = 'strict-origin-when-cross-origin';
        box.innerHTML = '';
        box.appendChild(f);
    }
    if (box) box.querySelector('button').addEventListener('click', play);
    document.querySelectorAll('[data-cf-jump]').forEach(function (a) {
        a.addEventListener('click', function () { setTimeout(play, 350); });
    });
    var data = { title: {{ \Illuminate\Support\Js::from($film->title) }}, url: {{ \Illuminate\Support\Js::from(route('films.show', $film)) }} };
    document.querySelectorAll('[data-cf-share]').forEach(function (b) {
        b.addEventListener('click', function () {
            if (navigator.share) { navigator.share(data).catch(function () {}); return; }
            if (navigator.clipboard) navigator.clipboard.writeText(data.url).then(function () {
                b.querySelector('span').textContent = {{ \Illuminate\Support\Js::from(__('Lien copié')) }};
            });
        });
    });
})();
</script>
</body>
</html>
