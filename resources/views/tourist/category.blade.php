<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $category->name }} à {{ $city->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif { font-family: 'Montserrat', serif; }
        .site-card { transition: transform .2s ease, box-shadow .2s ease; }
        .site-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(14,59,42,.4); }
        html:not(.dark) body { background:#D8E9E0; color: #0E3B2A; }
        html:not(.dark) .site-card { background:#F3F8F5 !important; border-color: rgba(14,59,42,.12) !important; box-shadow: 0 8px 20px rgba(14,59,42,.05); }
        /* Image de fond configurable (back-office, par catégorie) du bandeau —
           même modèle que .cultural-hero / .tourist-hero. */
        /* Hébergements : la carte .ti-place-card porte le cadre ; l'enveloppe .accom-card (module séjour) reste invisible. */
        html:not(.dark) .accom-card.is-wrap, html:not(.dark) .accom-card.is-wrap:hover { border: 0 !important; background: transparent !important; box-shadow: none !important; }
        .category-hero { position: relative; }
        .category-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            background-image: var(--category-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        /* Mobile : la bannière est panoramique (~5:1) avec ses collages aux deux
           extrémités et un centre vide ; « cover » sur une section haute et étroite
           n'en garde que le centre vide. On l'affiche donc en pleine largeur, ancrée
           en haut ET en bas (les bandes de padding autour du cadre), pour que les
           deux collages restent visibles. */
        @media (max-width: 639px) {
            .category-hero::before {
                background-image: var(--category-hero-bg-image, none), var(--category-hero-bg-image, none);
                background-size: 100% auto, 100% auto;
                background-position: top center, bottom center;
                background-repeat: no-repeat, no-repeat;
            }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')

@include('partials.public-top-nav')

{{-- Hero --}}
{{-- Bandeau culturel : trame des toiles de Korhogo + lisière kita + « Le saviez-vous ? » (patrimoine ivoirien, UNESCO) --}}
@php
    $heritageFacts = [
        ['t' => __('Le Zaouli, danse masquée des Gouro du centre-ouest, est inscrit au patrimoine culturel immatériel de l\'UNESCO depuis 2017.'), 'u' => 'https://ich.unesco.org/fr/etat/cote-d-ivoire-CI', 'a' => ['gouro']],
        ['t' => __('Le Gbofe d\'Afounkaha, musique des trompes traversières des Tagbana, est inscrit au patrimoine immatériel de l\'UNESCO (2008).'), 'u' => 'https://ich.unesco.org/fr/etat/cote-d-ivoire-CI', 'a' => ['senoufo']],
        ['t' => __('Les pratiques liées au balafon des communautés sénoufo de Côte d\'Ivoire, du Mali et du Burkina Faso sont reconnues par l\'UNESCO (2012).'), 'u' => 'https://ich.unesco.org/fr/etat/cote-d-ivoire-CI', 'a' => ['senoufo']],
        ['t' => __('La ville historique de Grand-Bassam, première capitale coloniale, est inscrite au patrimoine mondial de l\'UNESCO depuis 2012.'), 'u' => 'https://whc.unesco.org/fr/etatsparties/ci', 'a' => ['lagunaire', 'krou-cote']],
        ['t' => __('Les mosquées de style soudanais du nord ivoirien (Kong, Kouto, Kaouara…) sont inscrites au patrimoine mondial depuis 2021.'), 'u' => 'https://whc.unesco.org/fr/etatsparties/ci', 'a' => ['mande']],
        ['t' => __('Le parc national de Taï, l\'une des dernières grandes forêts primaires d\'Afrique de l\'Ouest, est classé au patrimoine mondial depuis 1982.'), 'u' => 'https://whc.unesco.org/fr/etatsparties/ci', 'nature' => true, 'a' => ['krou', 'krou-cote']],
        ['t' => __('Le parc national de la Comoé, plus grande aire protégée du pays, est inscrit au patrimoine mondial depuis 1983.'), 'u' => 'https://whc.unesco.org/fr/etatsparties/ci', 'nature' => true, 'a' => ['lobi', 'akan']],
        ['t' => __('La réserve naturelle intégrale du mont Nimba, partagée avec la Guinée, est inscrite au patrimoine mondial depuis 1981.'), 'u' => 'https://whc.unesco.org/fr/etatsparties/ci', 'nature' => true, 'a' => ['dan']],
    ];
    // Famille du thème : libellé du bandeau et faits mis en avant en premier.
    $slug = (string) $category->slug;
    $family = match (true) {
        (bool) preg_match('/plage|lagune|parc|reserve|cascade|montagne|lac|cours-eau|nature|foret/', $slug) && ! str_contains($slug, 'loisir') => 'nature',
        (bool) preg_match('/gastronomie|maquis|cuisine/', $slug) => 'taste',
        (bool) preg_match('/sport|aventure|nocturne|divertissement|loisir|zoo/', $slug) => 'leisure',
        default => 'culture',
    };
    $cityCulture = \App\Support\IvorianCulture::forCity($city);
    $familyLabel = ['nature' => __('Nature & paysages'), 'taste' => __('Saveurs & traditions'), 'leisure' => __('Loisirs & découvertes'), 'culture' => __('Culture & traditions')][$family];
    $heritageFacts = collect($heritageFacts)->sortBy(fn ($f) => in_array($cityCulture['key'], $f['a'] ?? [], true) ? 0 : ($family === 'nature' ? (empty($f['nature']) ? 2 : 1) : (empty($f['nature']) ? 1 : 2)))->values()->all();
@endphp
<section class="cg-hero" aria-labelledby="cg-title">
    @include('partials.ivorian-motifs', ['culture' => $cityCulture['key']])
    @if($category->hero_image_url)
        <figure class="cg-banner"><img src="{{ $category->hero_image_url }}" alt="{{ $category->name }}" decoding="async" fetchpriority="high"></figure>
    @endif
    <div class="cg-hero__in">
        <nav class="cg-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
            <a href="{{ route('tourist.cities') }}">{{ __('Régions') }}</a><span aria-hidden="true">/</span>
            <a href="{{ route('tourist.city', $city->slug) }}">{{ $city->name }}</a><span aria-hidden="true">/</span>
            <span aria-current="page">{{ $category->name }}</span>
        </nav>
        <p class="cg-kicker">{{ $city->name }} · {{ $familyLabel }}</p>
        <h1 class="cg-title" id="cg-title">{{ $category->name }}</h1>
        <p class="cg-lead">
            @if($category->description)
                {{ $category->description }}
            @else
                {{ $sites->count() }} {{ $sites->count() > 1 ? __('sites touristiques') : __('site touristique') }}
                @if(isset($accommodations) && $accommodations->count() > 0)
                    · {{ $accommodations->count() }} {{ $accommodations->count() > 1 ? __('hébergements') : __('hébergement') }}
                @endif
            @endif
        </p>
        <div class="cg-fact" data-cg-facts aria-live="polite">
            <span class="cg-fact__tag">{{ __('Le saviez-vous ?') }}</span>
            @foreach($heritageFacts as $f)
                <p class="cg-fact__item" @if(! $loop->first) hidden @endif>
                    <span>{{ $f['t'] }}</span>
                    <a href="{{ $f['u'] }}" target="_blank" rel="noopener noreferrer">{{ __('Source : UNESCO') }}</a>
                </p>
            @endforeach
            <span class="cg-fact__nav">
                <button type="button" data-cg-prev aria-label="{{ __('Fait précédent') }}">‹</button>
                <button type="button" data-cg-next aria-label="{{ __('Fait suivant') }}">›</button>
            </span>
        </div>
        <p class="cg-credit"><b>{{ $cityCulture['label'] }}</b> · {{ $cityCulture['credit'] }}</p>
    </div>
</section>
<style>
    .cg-hero { position: relative; overflow: hidden; background: #D8E9E0; padding: 64px 20px 72px; border-bottom: 1px solid #D8E9E0; }
    .cg-banner { position: relative; z-index: 1; max-width: 1140px; margin: 0 auto 22px; border-radius: 28px; overflow: hidden; border: 1px solid #D8E9E0; background: #D8E9E0; }
    .cg-banner img { display: block; width: 100%; height: clamp(150px, 19vw, 270px); object-fit: cover; }
    @media (max-width: 640px) { .cg-banner { border-radius: 20px; margin-bottom: 14px; } .cg-banner img { height: auto; aspect-ratio: 16 / 9; } }
    .cg-hero__in { position: relative; z-index: 1; max-width: 900px; margin: 0 auto; padding: 30px 40px 26px; border-radius: 32px; background: #F3F8F5; border: 1px solid #D8E9E0; text-align: center; }
    .cg-crumb { display: inline-flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 6px 10px; padding: 8px 16px; border-radius: 999px; background: #FFFFFF; border: 1px solid #D8E9E0; font-size: 12.5px; color: #557466; }
    .cg-crumb a { color: #B85600; font-weight: 600; text-decoration: none; }
    .cg-crumb a:hover { text-decoration: underline; text-underline-offset: 3px; }
    .cg-crumb [aria-current] { font-weight: 700; color: #0E3B2A; }
    .cg-kicker { margin: 20px 0 0; font-size: 12.5px; font-weight: 800; letter-spacing: .16em; text-transform: uppercase; color: #EB6E00; }
    .cg-title { margin: 10px 0 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 3.4vw + 18px, 64px); font-weight: 700; line-height: 1.03; letter-spacing: -.015em; color: #0E3B2A; }
    .cg-lead { margin: 14px auto 0; max-width: 660px; font-size: 17px; line-height: 1.65; font-weight: 500; color: #0E3B2A; }
    .cg-fact { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 14px; margin: 24px auto 0; max-width: 760px; padding: 12px 12px 12px 14px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; text-align: left; }
    .cg-fact__tag { padding: 6px 11px; border-radius: 999px; background: #00874F; color: #fff; font-size: 11.5px; font-weight: 800; letter-spacing: .04em; white-space: nowrap; }
    .cg-fact__item { margin: 0; display: flex; flex-direction: column; gap: 3px; font-size: 14px; line-height: 1.5; color: #0E3B2A; }
    .cg-fact__item[hidden] { display: none; }
    .cg-fact__item a { align-self: flex-start; font-size: 12px; font-weight: 700; color: #B85600; text-decoration: underline; text-underline-offset: 3px; }
    .cg-fact__nav { display: flex; gap: 6px; }
    .cg-fact__nav button { width: 34px; height: 34px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; color: #B85600; font-size: 18px; font-weight: 700; line-height: 1; cursor: pointer; }
    .cg-fact__nav button:hover { border-color: #EB6E00; }
    .cg-fact__nav button:focus-visible, .cg-crumb a:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .cg-credit b { font-style: normal; font-weight: 800; color: #B85600; }
    .cg-credit { margin: 14px 0 0; font-size: 11.5px; font-style: italic; color: #24503D; }
    @media (max-width: 640px) {
        /* Mobile : une vraie bande de motifs visible au-dessus et au-dessous du panneau de lecture */
        .cg-hero { padding: 64px 16px 48px; }
        .cg-hero__in { padding: 12px 12px 10px; border-radius: 18px; }
        .cg-crumb { font-size: 11px; padding: 5px 10px; gap: 4px 6px; }
        .cg-kicker { margin-top: 10px; font-size: 10px; letter-spacing: .1em; }
        .cg-title { margin-top: 4px; font-size: clamp(24px, 6.6vw, 28px); }
        .cg-lead { margin-top: 6px; font-size: 13px; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .cg-fact { margin-top: 10px; padding: 9px 10px; gap: 6px 8px; border-radius: 14px; }
        .cg-fact__tag { padding: 4px 9px; font-size: 10.5px; }
        .cg-credit { margin-top: 8px; font-size: 10px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .cg-fact { grid-template-columns: minmax(0, 1fr) auto; gap: 10px; }
        .cg-fact__tag { grid-column: 1; grid-row: 1; justify-self: start; }
        .cg-fact__nav { grid-column: 2; grid-row: 1; }
        .cg-fact__item { grid-column: 1 / -1; grid-row: 2; }
        .cg-fact__nav button { width: 28px; height: 28px; font-size: 15px; }
        .cg-fact__item { font-size: 12.5px; line-height: 1.4; }
        .cg-fact__item a { font-size: 11px; }
    }
</style>
<script>
(function () {
    const box = document.querySelector('[data-cg-facts]');
    if (!box) return;
    const items = [...box.querySelectorAll('.cg-fact__item')];
    let i = 0, timer = null;
    const show = n => { i = (n + items.length) % items.length; items.forEach((el, k) => el.hidden = k !== i); };
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const start = () => { if (!reduce) timer = setInterval(() => show(i + 1), 7000); };
    const stop = () => clearInterval(timer);
    box.querySelector('[data-cg-prev]').addEventListener('click', () => { stop(); show(i - 1); });
    box.querySelector('[data-cg-next]').addEventListener('click', () => { stop(); show(i + 1); });
    box.addEventListener('mouseenter', stop); box.addEventListener('focusin', stop);
    start();
})();
</script>

@include('partials.hero-separator', ['class' => 'mb-8'])

{{-- Sites list --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    @if($sites->isEmpty())
    <div class="text-center py-16 text-slate-500 border border-slate-800 rounded-2xl">
        <i class="fas fa-map-pin text-4xl mb-3 block text-slate-700"></i>
        {{ __('Aucun site disponible dans cette catégorie pour le moment.') }}
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($sites as $site)
        @php
            $sitePhotos = $site->media->pluck('url')->push($site->thumbnail)->filter()->unique()->values();
            $siteIcon = \Illuminate\Support\Str::after((string) ($category->icon ?: 'fas fa-landmark'), 'fas ');
            $siteIt = [
                'name' => $site->name,
                'url' => route('tourist.site', $site->slug),
                'image' => $sitePhotos[0] ?? null,
                'hover_image' => $sitePhotos[1] ?? null,
                'photo_count' => $sitePhotos->count(),
                'stars' => null,
                'amenities' => collect(),
                'cta' => __('Découvrir'),
                'provider_id' => null,
                'reviews' => 0,
                'response_badge' => null,
                'free_cancellation' => false,
                'map_url' => $site->latitude && $site->longitude
                    ? 'https://www.google.com/maps/dir/?api=1&destination='.$site->latitude.','.$site->longitude
                    : 'https://www.google.com/maps/search/?api=1&query='.rawurlencode(trim($site->name.' '.$city->name.' Côte d\'Ivoire')),
                'location' => trim(collect([$site->localite ?? $site->departement, $city->name])->filter()->unique()->implode(' · ')),
                'badge' => $category->name,
                'description' => $site->localized('short_description'),
                'price' => null,
                'rating' => null,
                'verified' => false,
                'icon' => $siteIcon,
                'sector' => null,
                'available' => false,
            ];
        @endphp
        @include('partials.home-sector-card', ['it' => $siteIt])
        @endforeach
    </div>
    @endif
</section>

{{-- ── Hébergements ──────────────────────────────────────────────────────────── --}}
@if(isset($accommodations) && $accommodations->isNotEmpty())
<section class="max-w-6xl mx-auto px-6 pb-24" id="hebergements">

    {{-- Titre section --}}
    <div class="flex items-center gap-3 mb-6">
        <div class="w-1 h-6 rounded-full bg-orange-500 shrink-0"></div>
        <h2 class="font-serif text-2xl font-bold text-white">{{ __('Hébergements') }}</h2>
        <span class="text-slate-500 text-sm">{{ $accommodations->count() }} {{ $accommodations->count() > 1 ? 'disponibles' : 'disponible' }}</span>
        <span id="bk-avail-hint"
              class="hidden ml-auto text-xs text-emerald-400 items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse inline-block"></span>
            {{ __('Chambres disponibles affichées') }}
        </span>
    </div>

    {{-- ════════════════════════════════════════════════════════
         Widget de recherche / réservation
    ════════════════════════════════════════════════════════ --}}
    <div class="relative bg-[#ffffff] border border-orange-500/20 rounded-2xl p-5 mb-8 shadow-2xl overflow-hidden">


        <div class="relative">
            {{-- En-tête widget --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-7 h-7 rounded-lg bg-orange-500/15 flex items-center justify-center">
                        <i class="fas fa-calendar-check text-orange-400 text-xs"></i>
                    </div>
                    <span class="text-white font-semibold text-sm">{{ __('Votre séjour') }}</span>
                </div>
                <div id="bk-summary" class="hidden items-center gap-2">
                    <span id="bk-nights-label"
                          class="px-3 py-1 bg-orange-500/20 border border-orange-500/30 text-orange-300 text-xs font-bold rounded-full">
                    </span>
                    <span id="bk-guests-label"
                          class="px-3 py-1 bg-slate-800 border border-slate-700 text-slate-300 text-xs rounded-full">
                    </span>
                </div>
            </div>

            {{-- Champs --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                {{-- Arrivée --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-plane-arrival text-orange-400/60"></i> {{ __('Arrivée') }}
                    </label>
                    <div class="relative">
                        <input type="date" id="bk-checkin"
                               min="{{ date('Y-m-d') }}"
                               class="bk-date-input w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition cursor-pointer"
                               style="color-scheme:dark">
                    </div>
                </div>

                {{-- Départ --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-plane-departure text-orange-400/60"></i> {{ __('Départ') }}
                    </label>
                    <div class="relative">
                        <input type="date" id="bk-checkout"
                               min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                               class="bk-date-input w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition cursor-pointer"
                               style="color-scheme:dark">
                    </div>
                </div>

                {{-- Chambres --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-door-open text-orange-400/60"></i> {{ __('Chambres') }}
                    </label>
                    <div class="flex items-center gap-0 bg-green-900 border border-slate-700 rounded-xl overflow-hidden">
                        <button type="button" onclick="bkStep('rooms',-1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span id="bk-rooms-val"
                              class="flex-1 text-center text-white font-semibold text-sm">1</span>
                        <button type="button" onclick="bkStep('rooms',+1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>

                {{-- Personnes --}}
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1.5 flex items-center gap-1">
                        <i class="fas fa-user-group text-orange-400/60"></i> {{ __('Personnes') }}
                    </label>
                    <div class="flex items-center gap-0 bg-green-900 border border-slate-700 rounded-xl overflow-hidden">
                        <button type="button" onclick="bkStep('guests',-1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-minus text-xs"></i>
                        </button>
                        <span id="bk-guests-val"
                              class="flex-1 text-center text-white font-semibold text-sm">2</span>
                        <button type="button" onclick="bkStep('guests',+1)"
                                class="w-10 h-[42px] flex items-center justify-center text-slate-400 hover:text-orange-400 hover:bg-slate-800 transition text-base font-bold shrink-0">
                            <i class="fas fa-plus text-xs"></i>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Ligne du bas : horaires de la destination --}}
            @php $firstCheckIn = $accommodations->first()?->check_in_time; @endphp
            @if($firstCheckIn)
            <div class="mt-3 flex items-center gap-3 text-[11px] text-slate-600">
                <i class="fas fa-clock text-[9px]"></i>
                {{ __('Check-in généralement à partir de') }} {{ $firstCheckIn }}
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         Grille des cartes hébergements
    ════════════════════════════════════════════════════════ --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($accommodations as $acc)
        @php
            $photo  = $acc->media->first();
            $imgSrc = $photo?->url ?? $acc->cover_image ?? $acc->thumbnail;
            $links  = $acc->booking_links ?? [];
        @endphp
        <div class="accom-card is-wrap flex flex-col gap-2"
             data-acc-id="{{ $acc->id }}"
             data-acc-name="{{ $acc->name }}"
             data-acc-rooms='@json($acc->room_types ?? [])'
             data-acc-links='@json($acc->booking_links ?? [])'
             data-acc-cover="{{ $imgSrc ?? '' }}">
            @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($acc, 'hotels')])
            <div class="acc-stay-summary hidden px-3 py-2 rounded-xl text-xs" style="background:#F3F8F5;color:#B85600"></div>
            <div class="acc-rooms-panel hidden"></div>
            @if(count($links) > 0)
            <div class="acc-booking-links flex flex-wrap gap-2 px-1">
                @foreach(array_slice($links, 0, 3) as $bl)
                <a href="{{ $bl['affiliate_url'] ?? '#' }}"
                   data-base-url="{{ $bl['affiliate_url'] ?? '#' }}"
                   data-provider="{{ strtolower($bl['provider_name'] ?? '') }}"
                   target="_blank" rel="noopener nofollow"
                   class="bk-link inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-semibold transition border"
                   style="{{ ! empty($bl['is_official']) ? 'background:#EB6E00;border-color:#EB6E00;color:#fff' : 'background:#fff;border-color:#D8E9E0;color:#24503D' }}">
                    @if(!empty($bl['logo_url']))
                        <img src="{{ $bl['logo_url'] }}" alt="{{ $bl['provider_name'] }}" class="h-3.5 w-auto object-contain">
                    @else
                        <span>{{ $bl['provider_name'] }}</span>
                    @endif
                    @if(!empty($bl['badge_text']))<span class="opacity-70">· {{ $bl['badge_text'] }}</span>@endif
                </a>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    </div>
</section>

{{-- ════════════════════════════════════════════════════════
     Modal détail chambre
════════════════════════════════════════════════════════ --}}
<div id="room-modal"
     class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-green-950/85 backdrop-blur-sm" onclick="closeRoomModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-4xl max-h-[95vh] bg-[#ffffff] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- Bouton fermer --}}
        <button onclick="closeRoomModal()"
                class="absolute top-4 right-4 z-20 w-9 h-9 rounded-full bg-green-950/60 backdrop-blur-sm border border-white/10 text-white hover:bg-slate-700 flex items-center justify-center transition">
            <i class="fas fa-times text-sm"></i>
        </button>

        {{-- Hero photo --}}
        <div class="relative shrink-0 overflow-hidden bg-green-900" style="height:460px">
            <img id="rm-hero-img" src="" alt=""
                 class="w-full h-full object-cover transition duration-300">
            <div class="absolute inset-0 bg-green-950/40"></div>
            <div class="absolute bottom-4 left-5 right-14">
                <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-500/20 border border-emerald-500/30 text-emerald-400 text-[10px] font-bold rounded-full mb-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>{{ __('Disponible') }}
                </span>
                <h3 id="rm-name" class="font-serif text-2xl font-bold text-white leading-tight"></h3>
            </div>
        </div>

        {{-- Strip galerie photos --}}
        <div id="rm-gallery"
             class="hidden gap-2 px-4 py-3 bg-green-950/40 overflow-x-auto shrink-0"
             style="scrollbar-width:none;display:none">
        </div>

        {{-- Corps scrollable --}}
        <div class="overflow-y-auto flex-1">
            <div class="p-5 grid grid-cols-1 sm:grid-cols-5 gap-5">

                {{-- Colonne gauche : infos --}}
                <div class="sm:col-span-3 space-y-5">

                    {{-- Capacité + surface --}}
                    <div id="rm-meta" class="flex flex-wrap gap-4 text-sm text-slate-300"></div>

                    {{-- Équipements --}}
                    <div id="rm-amenities-section">
                        <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-2.5">{{ __('Équipements') }}</p>
                        <div id="rm-amenities" class="flex flex-wrap gap-2"></div>
                    </div>
                </div>

                {{-- Colonne droite : prix + réservation --}}
                <div class="sm:col-span-2">
                    <div class="bg-green-900 border border-slate-800 rounded-xl p-4 space-y-3 sticky top-4">
                        <div>
                            <div id="rm-price-ppn" class="text-xl font-bold text-white leading-tight"></div>
                            <div id="rm-price-total" class="text-orange-400 text-sm font-semibold mt-0.5"></div>
                            <div id="rm-stay-info" class="text-slate-500 text-xs mt-1 leading-relaxed"></div>
                        </div>
                        <a id="rm-book-btn" href="#" target="_blank" rel="noopener nofollow"
                           class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-500 hover:bg-orange-400 text-black font-bold rounded-xl transition text-sm">
                            <i class="fas fa-calendar-check"></i>{{ __('Réserver cette chambre') }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     Modal liste des chambres disponibles (par hébergement)
════════════════════════════════════════════════════════ --}}
<div id="rooms-list-modal"
     class="fixed inset-0 z-50 hidden items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-green-950/85 backdrop-blur-sm" onclick="closeRoomsListModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-6xl h-[95vh] sm:h-auto sm:max-h-[95vh] bg-[#ffffff] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- En-tête --}}
        <div class="flex items-center justify-between gap-3 px-6 py-5 border-b border-slate-800 shrink-0">
            <div class="min-w-0">
                <p class="text-xs text-slate-500 uppercase tracking-wider mb-1">{{ __('Chambres disponibles') }}</p>
                <h3 id="rlm-acc-name" class="text-white font-semibold text-lg truncate"></h3>
            </div>
            <button onclick="closeRoomsListModal()"
                    class="shrink-0 w-10 h-10 rounded-full bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 flex items-center justify-center transition">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>

        {{-- Corps scrollable --}}
        <div id="rlm-body" class="overflow-y-auto flex-1 p-6"></div>
    </div>
</div>

{{-- ════════════════════════════════════════════════════════
     Modal demande de réservation (envoi vers le back-office)
════════════════════════════════════════════════════════ --}}
<div id="reservation-modal"
     class="fixed inset-0 z-[60] hidden items-end sm:items-center justify-center p-0 sm:p-4"
     role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div class="absolute inset-0 bg-green-950/85 backdrop-blur-sm" onclick="closeReservationModal()"></div>

    {{-- Panneau --}}
    <div class="relative z-10 w-full sm:max-w-md max-h-[92vh] bg-[#ffffff] border border-slate-800 rounded-t-2xl sm:rounded-2xl overflow-hidden flex flex-col shadow-2xl">

        {{-- En-tête --}}
        <div class="flex items-center justify-between gap-3 px-5 py-4 border-b border-slate-800 shrink-0">
            <div class="min-w-0">
                <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-0.5">{{ __('Demande de réservation') }}</p>
                <h3 id="rsv-title" class="text-white font-semibold text-sm truncate"></h3>
            </div>
            <button onclick="closeReservationModal()"
                    class="shrink-0 w-9 h-9 rounded-full bg-slate-800 border border-slate-700 text-white hover:bg-slate-700 flex items-center justify-center transition">
                <i class="fas fa-times text-sm"></i>
            </button>
        </div>

        {{-- Corps --}}
        <div class="overflow-y-auto flex-1 p-5">

            {{-- Récap séjour --}}
            <div id="rsv-summary" class="mb-4 px-3 py-2.5 bg-orange-500/10 border border-orange-500/20 rounded-xl text-xs text-orange-300/90"></div>

            {{-- Formulaire --}}
            <form id="rsv-form" class="space-y-3">
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Nom complet') }}</label>
                    <input type="text" name="full_name" required maxlength="255"
                           class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-slate-500 mb-1">{{ __('E-mail') }}</label>
                        <input type="email" name="email" required maxlength="255"
                               class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-500 mb-1">{{ __('Téléphone') }}</label>
                        <input type="tel" name="phone" maxlength="50"
                               class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition">
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] text-slate-500 mb-1">{{ __('Message (optionnel)') }}</label>
                    <textarea name="message" rows="3" maxlength="2000"
                              class="w-full bg-green-900 border border-slate-700 focus:border-orange-500/50 rounded-xl px-3 py-2.5 text-sm text-slate-200 outline-none transition resize-none"
                              placeholder="{{ __('Précisions sur votre demande...') }}"></textarea>
                </div>

                <div id="rsv-error" class="hidden text-xs text-rose-400 bg-rose-500/10 border border-rose-500/25 rounded-lg px-3 py-2"></div>

                <button type="submit" id="rsv-submit-btn"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-orange-500 hover:bg-orange-400 text-black font-bold rounded-xl transition text-sm">
                    <i class="fas fa-paper-plane"></i>{{ __('Envoyer la demande') }}
                </button>
            </form>

            {{-- État succès --}}
            <div id="rsv-success" class="hidden text-center py-6">
                <div class="w-14 h-14 mx-auto rounded-full bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center mb-3">
                    <i class="fas fa-check text-emerald-400 text-xl"></i>
                </div>
                <p class="text-white font-semibold text-sm mb-1">{{ __('Demande envoyée') }}</p>
                <p id="rsv-success-msg" class="text-slate-400 text-xs"></p>
            </div>
        </div>
    </div>
</div>

<style>
/* Forcer le thème sombre sur le picker natif */
.bk-date-input::-webkit-calendar-picker-indicator { filter: invert(.7) sepia(1) hue-rotate(10deg) saturate(.8); cursor: pointer; }
.bk-date-input::-webkit-datetime-edit { color: #24503D; }
</style>

<script>
(function () {
    let bkRooms  = 1;
    let bkGuests = 2;

    const fmt     = n => new Intl.NumberFormat('fr-FR').format(n);
    const fmtDate = d => { const [y,m,j] = d.split('-'); return j+'/'+m+'/'+y; };

    // Registre des données de chambre pour le modal (clé → données)
    const _rooms = {};

    // Registre du HTML de la grille de chambres par hébergement (accId → html)
    const _roomsLists = {};

    // ── Steppers ──────────────────────────────────────────────────────────
    window.bkStep = function (field, delta) {
        if (field === 'rooms') {
            bkRooms = Math.max(1, Math.min(10, bkRooms + delta));
            document.getElementById('bk-rooms-val').textContent = bkRooms;
        } else {
            bkGuests = Math.max(1, Math.min(20, bkGuests + delta));
            document.getElementById('bk-guests-val').textContent = bkGuests;
        }
        bkUpdate();
    };

    // ── Construit l'URL d'un provider avec les paramètres de séjour ───────
    function buildBookUrl(base, providerName, ci, co) {
        if (!base || base === '#') return '#';
        const p  = new URLSearchParams();
        const pr = (providerName || '').toLowerCase();
        if (pr.includes('booking')) {
            p.set('checkin', ci);      p.set('checkout', co);
            p.set('group_adults', bkGuests); p.set('no_rooms', bkRooms);
        } else if (pr.includes('airbnb')) {
            p.set('check_in', ci);    p.set('check_out', co);
            p.set('adults', bkGuests);
        } else if (pr.includes('expedia') || pr.includes('hotels')) {
            p.set('startDate', ci);   p.set('endDate', co);
            p.set('adults', bkGuests); p.set('rooms', bkRooms);
        } else {
            p.set('checkin', ci);     p.set('checkout', co);
            p.set('adults', bkGuests); p.set('rooms', bkRooms);
        }
        return base + (base.includes('?') ? '&' : '?') + p.toString();
    }

    // ── Modal détail chambre ───────────────────────────────────────────────
    window.openRoomModal = function (key) {
        const d = _rooms[key];
        if (!d) return;
        const { r, bookUrl, nights, ci, co } = d;

        // Hero photo (première photo de la chambre ou couverture hébergement)
        let photos = Array.isArray(r.photos) ? r.photos.filter(Boolean) : [];
        if (!photos.length && r.thumbnail) photos = [r.thumbnail];
        const heroSrc = photos[0] || d.accCoverUrl || '';
        const heroImg = document.getElementById('rm-hero-img');
        heroImg.src = heroSrc;
        heroImg.alt = r.name || 'Chambre';

        // Nom
        document.getElementById('rm-name').textContent = r.name || 'Chambre';

        // Strip galerie (affichée seulement si > 1 photo)
        const gallery = document.getElementById('rm-gallery');
        if (photos.length > 1) {
            gallery.style.display = 'flex';
            gallery.innerHTML = photos.map(function (url, i) {
                return '<button onclick="document.getElementById(\'rm-hero-img\').src=\'' + url + '\'"'
                    + ' class="flex-shrink-0 w-20 h-14 rounded-lg overflow-hidden border-2 transition '
                    + (i === 0 ? 'border-orange-500' : 'border-transparent opacity-60 hover:opacity-100') + '">'
                    + '<img src="' + url + '" class="w-full h-full object-cover" loading="lazy">'
                    + '</button>';
            }).join('');
        } else {
            gallery.style.display = 'none';
            gallery.innerHTML = '';
        }

        // Méta (capacité + surface)
        const metaEl = document.getElementById('rm-meta');
        const meta   = [];
        if (r.max_adults)   meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-user text-orange-400/70"></i>' + r.max_adults   + ' adulte'  + (r.max_adults   > 1 ? 's' : '') + ' max</span>');
        if (r.max_children) meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-child text-orange-400/70"></i>' + r.max_children + ' enfant' + (r.max_children > 1 ? 's' : '') + ' max</span>');
        if (r.area_m2)      meta.push('<span class="flex items-center gap-1.5"><i class="fas fa-vector-square text-orange-400/70"></i>' + r.area_m2 + ' m²</span>');
        metaEl.innerHTML = meta.join('');

        // Équipements
        let ams = [];
        if (Array.isArray(r.amenities)) ams = r.amenities;
        else if (typeof r.amenities === 'string' && r.amenities)
            ams = r.amenities.split(',').map(function (s) { return s.trim(); }).filter(Boolean);

        const amsEl   = document.getElementById('rm-amenities');
        const amsSect = document.getElementById('rm-amenities-section');
        if (ams.length) {
            amsEl.innerHTML = ams.map(function (a) {
                return '<span class="inline-flex items-center gap-1.5 px-2.5 py-1.5 bg-slate-800 border border-slate-700 rounded-lg text-xs text-slate-300">'
                    + '<i class="fas fa-check text-orange-400/60 text-[8px]"></i>' + a + '</span>';
            }).join('');
            amsSect.style.display = '';
        } else {
            amsSect.style.display = 'none';
        }

        // Prix
        const ppn      = parseInt(r.price_xof || 0);
        const totalXof = ppn && nights > 0 ? ppn * nights * bkRooms : 0;
        const ppnEl    = document.getElementById('rm-price-ppn');
        const totEl    = document.getElementById('rm-price-total');
        const stayEl   = document.getElementById('rm-stay-info');

        if (ppn) {
            ppnEl.innerHTML    = fmt(ppn) + ' <span class="text-sm font-normal text-slate-500">FCFA / nuit</span>';
            totEl.textContent  = totalXof ? 'Total : ' + fmt(totalXof) + ' FCFA' : '';
            stayEl.textContent = nights > 0
                ? nights + ' nuit' + (nights > 1 ? 's' : '') + '  ·  '
                  + bkRooms + ' chambre' + (bkRooms > 1 ? 's' : '') + '  ·  '
                  + bkGuests + ' pers.'
                : '';
        } else {
            ppnEl.textContent  = 'Prix sur demande';
            totEl.textContent  = '';
            stayEl.textContent = '';
        }

        // Bouton réserver
        const btn = document.getElementById('rm-book-btn');
        btn.href                = bookUrl !== '#' ? bookUrl : '#';
        btn.style.opacity       = bookUrl !== '#' ? '' : '0.4';
        btn.style.pointerEvents = bookUrl !== '#' ? '' : 'none';

        // Affichage modal
        document.getElementById('room-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeRoomModal = function () {
        document.getElementById('room-modal').style.display = 'none';
        document.body.style.overflow = '';
    };

    // ── Modal liste des chambres disponibles ───────────────────────────────
    window.openRoomsListModal = function (accId) {
        const d = _roomsLists[accId];
        if (!d) return;
        document.getElementById('rlm-acc-name').textContent = d.name || '';
        document.getElementById('rlm-body').innerHTML = d.html || '';
        document.getElementById('rooms-list-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeRoomsListModal = function () {
        document.getElementById('rooms-list-modal').style.display = 'none';
        document.body.style.overflow = '';
    };

    // ── Modal demande de réservation ───────────────────────────────────────
    let _rsvCurrent = null;

    window.openReservationModal = function (key) {
        const d = _rooms[key];
        if (!d) return;
        _rsvCurrent = { key, d };

        const { r, nights, ci, co, accId, accName, rooms, guests } = d;

        document.getElementById('rsv-title').textContent = (accName || '') + ' — ' + (r.name || 'Chambre');

        const ppn = parseInt(r.price_xof || 0);
        const total = ppn ? ppn * nights * rooms : 0;
        document.getElementById('rsv-summary').innerHTML =
            '<i class="fas fa-calendar-check mr-1.5"></i>' + fmtDate(ci) + ' → ' + fmtDate(co)
            + '&nbsp;&nbsp;·&nbsp;&nbsp;' + nights + ' nuit' + (nights > 1 ? 's' : '')
            + '&nbsp;&nbsp;·&nbsp;&nbsp;' + rooms + ' ch. · ' + guests + ' pers.'
            + (total ? '<br><span class="text-white font-semibold">' + fmt(total) + ' FCFA</span> estimé' : '');

        const form = document.getElementById('rsv-form');
        form.reset();
        form.classList.remove('hidden');
        document.getElementById('rsv-error').classList.add('hidden');
        document.getElementById('rsv-success').classList.add('hidden');

        const btn = document.getElementById('rsv-submit-btn');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i>Envoyer la demande';

        document.getElementById('reservation-modal').style.display = 'flex';
        document.body.style.overflow = 'hidden';
    };

    window.closeReservationModal = function () {
        document.getElementById('reservation-modal').style.display = 'none';
        document.body.style.overflow = '';
        _rsvCurrent = null;
    };

    document.getElementById('rsv-form').addEventListener('submit', function (e) {
        e.preventDefault();
        if (!_rsvCurrent) return;

        const { d } = _rsvCurrent;
        const { r, nights, ci, co, accId, rooms } = d;
        const form = e.target;
        const errorEl = document.getElementById('rsv-error');
        const btn = document.getElementById('rsv-submit-btn');

        errorEl.classList.add('hidden');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>Envoi...';

        const payload = {
            accommodation_id: accId,
            room_name: r.name || 'Chambre',
            room_price_xof: r.price_xof || null,
            check_in: ci,
            check_out: co,
            rooms_count: rooms,
            guests_count: d.guests,
            full_name: form.full_name.value,
            email: form.email.value,
            phone: form.phone.value,
            message: form.message.value,
        };

        fetch('{{ route('reservations.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify(payload),
        })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok) {
                    const msg = result.data && result.data.errors
                        ? Object.values(result.data.errors).flat().join(' ')
                        : (result.data && result.data.message) || {{ \Illuminate\Support\Js::from(__('Une erreur est survenue. Veuillez réessayer.')) }};
                    throw new Error(msg);
                }
                form.classList.add('hidden');
                document.getElementById('rsv-success-msg').textContent = result.data.message || {{ \Illuminate\Support\Js::from(__('Votre demande a bien été envoyée.')) }};
                document.getElementById('rsv-success').classList.remove('hidden');
            })
            .catch(function (err) {
                errorEl.textContent = err.message || {{ \Illuminate\Support\Js::from(__('Une erreur est survenue. Veuillez réessayer.')) }};
                errorEl.classList.remove('hidden');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-paper-plane"></i>Envoyer la demande';
            });
    });

    document.addEventListener('keydown', function (e) {
        if (e.key !== 'Escape') return;
        window.closeRoomModal();
        window.closeRoomsListModal();
        window.closeReservationModal();
    });

    // ── Panneau chambres disponibles ──────────────────────────────────────
    function renderRoomsPanel(card, nights, ci, co) {
        const panel   = card.querySelector('.acc-rooms-panel');
        const linksEl = card.querySelector('.acc-booking-links');
        if (!panel) return;

        if (nights <= 0) {
            panel.classList.add('hidden');
            panel.innerHTML = '';
            if (linksEl) linksEl.classList.remove('hidden');
            return;
        }

        let rooms = [];
        try { rooms = JSON.parse(card.dataset.accRooms || '[]'); } catch(e) {}
        let links = [];
        try { links = JSON.parse(card.dataset.accLinks || '[]'); } catch(e) {}

        const accCoverUrl = card.dataset.accCover || '';

        // Masquer les boutons classiques, révéler le panneau
        if (linksEl) linksEl.classList.add('hidden');
        panel.classList.remove('hidden');

        if (!rooms.length) {
            panel.innerHTML =
                '<div class="mt-3 pt-3 border-t border-slate-800/80 text-center text-slate-600 text-xs py-3">'
                + '<i class="fas fa-info-circle mr-1"></i>Aucun type de chambre renseigné.</div>';
            return;
        }

        // Filtrer par capacité : max_adults doit couvrir ceil(bkGuests/bkRooms) par chambre
        const gPR   = Math.max(1, Math.ceil(bkGuests / bkRooms));
        const avail = rooms.filter(r => {
            const maxA = parseInt(r.max_adults || 0);
            return maxA === 0 || maxA >= gPR;
        });

        if (!avail.length) {
            panel.innerHTML =
                '<div class="mt-3 pt-3 border-t border-slate-800/80 text-center text-xs py-3">'
                + '<span class="text-orange-600/70"><i class="fas fa-exclamation-circle mr-1"></i>'
                + {{ \Illuminate\Support\Js::from(__('Aucune chambre pour ')) }} + gPR + ' adulte' + (gPR > 1 ? 's' : '') + '/chambre.</span><br>'
                + '<span class="text-slate-600 text-[10px]">Ajustez le nombre de personnes ou de chambres.</span></div>';
            return;
        }

        // Lien officiel ou premier lien disponible pour le bouton {{ \Illuminate\Support\Js::from(__('Réserver')) }}
        const bkLink = links.find(l => l.is_official) || links[0] || null;
        const accId   = card.dataset.accId || '';
        const accName = card.dataset.accName || '';

        let html = '<div class="flex flex-wrap gap-5">';

        avail.forEach(function (r) {
            const ppn      = parseInt(r.price_xof || 0);
            const totalXof = ppn ? ppn * nights * bkRooms : 0;

            let ams = [];
            if (Array.isArray(r.amenities)) {
                ams = r.amenities;
            } else if (typeof r.amenities === 'string' && r.amenities) {
                ams = r.amenities.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
            }

            const bookUrl = bkLink
                ? buildBookUrl(bkLink.affiliate_url, bkLink.provider_name, ci, co)
                : '#';

            // Enregistrement pour le modal
            const key = 'r' + Date.now().toString(36) + Math.random().toString(36).slice(2, 7);
            _rooms[key] = { r, bookUrl, nights, ci, co, accCoverUrl, accId, accName, rooms: bkRooms, guests: bkGuests };

            let photos = Array.isArray(r.photos) ? r.photos.filter(Boolean) : [];
            const thumbSrc = photos[0] || r.thumbnail || accCoverUrl || '';

            const metaItems = [];
            if (r.max_adults)   metaItems.push('<i class="fas fa-user text-[8px] mr-0.5"></i>' + r.max_adults);
            if (r.max_children) metaItems.push('<i class="fas fa-child text-[8px] mr-0.5"></i>' + r.max_children);
            if (r.area_m2)      metaItems.push('<i class="fas fa-vector-square text-[8px] mr-0.5"></i>' + r.area_m2 + 'm²');

            html +=
                '<div class="w-full sm:w-[300px] bg-slate-800/50 border border-slate-700/60 rounded-xl overflow-hidden flex flex-col">'
                +   '<button type="button" onclick="openRoomModal(\'' + key + '\')" class="relative h-44 w-full bg-green-900 block">'
                +     (thumbSrc
                        ? '<img src="' + thumbSrc + '" alt="' + (r.name || 'Chambre') + '" loading="lazy" class="w-full h-full object-cover">'
                        : '<div class="w-full h-full flex items-center justify-center"><i class="fas fa-bed text-slate-700 text-3xl"></i></div>')
                +     '<span class="absolute top-2.5 left-2.5 inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-500/90 text-black text-xs font-bold rounded-full">'
                +       '<span class="w-1.5 h-1.5 rounded-full bg-green-950/40 inline-block"></span>Dispo'
                +     '</span>'
                +   '</button>'
                +   '<div class="p-4 flex flex-col flex-1">'
                +     '<span class="text-white text-base font-semibold leading-tight line-clamp-1 mb-2">' + (r.name || 'Chambre') + '</span>'
                +     (metaItems.length
                        ? '<div class="flex flex-wrap gap-3 text-xs text-slate-500 mb-3">'
                          + metaItems.map(function(i){ return '<span>' + i + '</span>'; }).join('')
                          + '</div>'
                        : '')
                +     (ppn
                          ? '<div class="mb-3">'
                            + '<div class="text-white text-lg font-bold leading-tight">' + fmt(ppn) + '<span class="text-xs font-normal text-slate-500"> FCFA/nuit</span></div>'
                            + (totalXof ? '<div class="text-orange-400/80 text-xs leading-tight">' + fmt(totalXof) + ' FCFA total</div>' : '')
                            + '</div>'
                          : '')
                +     '<div class="mt-auto space-y-2">'
                +       (bookUrl !== '#'
                            ? '<a href="' + bookUrl + '" target="_blank" rel="noopener nofollow"'
                              + ' class="block text-center px-4 py-2.5 bg-orange-500 hover:bg-orange-400 text-black text-sm font-bold rounded-lg transition">'
                              + '<i class="fas fa-calendar-check text-xs mr-1.5"></i>Reserver'
                              + '</a>'
                            : '')
                +       '<button type="button" onclick="openReservationModal(\'' + key + '\')"'
                +         ' class="w-full text-center px-4 py-2.5 bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-medium rounded-lg transition">'
                +         '<i class="fas fa-paper-plane text-xs mr-1.5"></i>Demander cette chambre'
                +         '</button>'
                +     '</div>'
                +   '</div>'
                + '</div>';
        });

        html += '</div>';

        _roomsLists[accId] = { name: accName, html: html };

        panel.innerHTML =
            '<div class="mt-3 pt-3 border-t border-slate-800/80">'
            + '<button type="button" onclick="openRoomsListModal(\'' + accId + '\')"'
            + ' class="w-full flex items-center justify-between gap-2 px-3 py-2.5 bg-emerald-500/10 border border-emerald-500/25 rounded-xl text-left transition hover:bg-emerald-500/15">'
            +   '<span class="flex items-center gap-1.5 text-emerald-400 text-xs font-semibold">'
            +     '<span class="w-1.5 h-1.5 rounded-full bg-emerald-400 inline-block"></span>'
            +     avail.length + ' chambre' + (avail.length > 1 ? 's' : '') + ' disponible' + (avail.length > 1 ? 's' : '')
            +   '</span>'
            +   '<span class="text-slate-400 text-[10px] font-medium">Voir <i class="fas fa-chevron-right text-[8px] ml-0.5"></i></span>'
            + '</button>'
            + '</div>';
    }

    // ── Calcul nuits + mise à jour UI ─────────────────────────────────────
    function bkUpdate() {
        const ciEl = document.getElementById('bk-checkin');
        const coEl = document.getElementById('bk-checkout');
        const ci = ciEl.value, co = coEl.value;

        let nights = 0;
        if (ci && co && co > ci) {
            nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
            coEl.min = ci;
        }

        // Summary badge + indicateur de disponibilité
        const summaryEl = document.getElementById('bk-summary');
        const nightsEl  = document.getElementById('bk-nights-label');
        const guestsEl  = document.getElementById('bk-guests-label');
        const hintEl    = document.getElementById('bk-avail-hint');

        if (nights > 0) {
            nightsEl.textContent = nights + ' nuit' + (nights > 1 ? 's' : '');
            guestsEl.textContent = bkRooms + ' ch. · ' + bkGuests + ' pers.';
            summaryEl.classList.remove('hidden'); summaryEl.classList.add('flex');
            if (hintEl) { hintEl.classList.remove('hidden'); hintEl.classList.add('flex'); }
        } else {
            summaryEl.classList.add('hidden'); summaryEl.classList.remove('flex');
            if (hintEl) { hintEl.classList.add('hidden'); hintEl.classList.remove('flex'); }
        }

        document.querySelectorAll('.accom-card').forEach(function (card) {

            // ── Prix dynamique ──────────────────────────────────────────
            const priceSpan = card.querySelector('.acc-price-display');
            if (priceSpan) {
                const ppn = parseInt(priceSpan.dataset.priceXof || 0);
                if (nights > 0 && ppn) {
                    const total = ppn * nights * bkRooms;
                    priceSpan.innerHTML = fmt(total) + ' FCFA'
                        + '<span class="opacity-60"> · ' + nights + ' nuit' + (nights > 1 ? 's' : '') + '</span>';
                } else if (ppn) {
                    priceSpan.innerHTML = 'À partir de ' + fmt(ppn) + ' FCFA'
                        + '<span class="opacity-60">/nuit</span>';
                }
            }

            // ── Résumé séjour ───────────────────────────────────────────
            const stayEl = card.querySelector('.acc-stay-summary');
            if (stayEl) {
                if (nights > 0) {
                    stayEl.innerHTML =
                        '<i class="fas fa-calendar-check mr-1.5"></i>'
                        + fmtDate(ci) + ' → ' + fmtDate(co)
                        + '&nbsp;&nbsp;·&nbsp;&nbsp;<i class="fas fa-door-open mr-1"></i>'
                        + bkRooms + ' chambre' + (bkRooms > 1 ? 's' : '')
                        + '&nbsp;&nbsp;·&nbsp;&nbsp;<i class="fas fa-user-group mr-1"></i>'
                        + bkGuests + ' personne' + (bkGuests > 1 ? 's' : '');
                    stayEl.classList.remove('hidden');
                } else {
                    stayEl.classList.add('hidden');
                }
            }

            // ── Liens réservation classiques (URL dynamiques) ───────────
            card.querySelectorAll('.bk-link').forEach(function (a) {
                const base     = a.dataset.baseUrl || '#';
                const provider = a.dataset.provider || '';
                if (base === '#' || !ci || !co) { a.href = base; return; }
                a.href = buildBookUrl(base, provider, ci, co);
            });

            // ── Panneau chambres disponibles ────────────────────────────
            renderRoomsPanel(card, nights, ci, co);
        });
    }

    // ── Écoute des changements de date ────────────────────────────────────
    document.getElementById('bk-checkin').addEventListener('change', function () {
        const co = document.getElementById('bk-checkout');
        co.min = this.value;
        if (co.value && co.value <= this.value) co.value = '';
        bkUpdate();
    });
    document.getElementById('bk-checkout').addEventListener('change', bkUpdate);
})();
</script>
@endif

@include('partials.homepage-footer')
</body>
</html>
