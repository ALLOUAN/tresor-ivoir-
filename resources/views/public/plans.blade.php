<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Nos offres') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="Choisissez le plan qui correspond à votre activité et boostez votre visibilité sur {{ $siteBrand['site_name'] }}.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1d1a16; }
        /* Page Abonnements : parcours en 2 étapes (secteur puis forfait), charte du site et couleurs du logo.
           Aplats, ombre au survol seulement. Classes conservées pour le script : .category-card / .cat-panel /
           .price-monthly / .price-yearly / #btn-monthly / #btn-yearly / .toggle-btn.active. */
        .lc { --c: 217,101,15; }
        .lc.c2 { --c: 74,138,42; } .lc.c3 { --c: 31,128,121; } .lc.c4 { --c: 201,138,12; } .lc.c5 { --c: 44,110,143; } .lc.c6 { --c: 176,134,28; } .lc.c7 { --c: 122,74,42; }
        .ab-wrap { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
        .ab-flash { padding: 10px 16px; text-align: center; font-size: 14px; }
        .ab-flash.is-info { background: #e7f3ec; color: #1b6b3a; }
        .ab-flash.is-error { background: #fdecea; color: #b42318; }

        /* Héros */
        .ab-hero { position: relative; overflow: hidden; background: #f3ead8; border-bottom: 1px solid #e3d5bb; }
        .ab-hero .ivm-g { opacity: .16 !important; }
        .ab-hero__in { position: relative; z-index: 1; max-width: 860px; margin: 0 auto; padding: 44px 20px 56px; text-align: center; }
        .ab-hero__card { padding: 30px 32px; border-radius: 30px; background: #f6efe1; border: 1px solid #e3d5bb; }
        .ab-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #fff; border: 1px solid #e3d5bb; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #9a4308; }
        .ab-kicker .ico { font-size: 15px; }
        .ab-title { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(34px, 2.6vw + 18px, 56px); font-weight: 700; line-height: 1.05; letter-spacing: -.015em; color: #1d1a16; }
        .ab-title em { font-style: italic; color: #b9500a; }
        .ab-lead { margin: 14px auto 0; max-width: 620px; font-size: 16.5px; line-height: 1.65; color: #3a2f22; }
        .ab-trust { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px 20px; margin: 18px 0 0; padding: 0; list-style: none; font-size: 13.5px; color: #3a2f22; }
        .ab-trust li { display: inline-flex; align-items: center; gap: 7px; }
        .ab-trust .ico { color: #1b6b3a; font-size: 17px; }

        /* Bascule mensuel / annuel */
        .ab-billing { display: inline-flex; align-items: center; gap: 4px; margin-top: 22px; padding: 5px; border-radius: 999px; background: #fff; border: 1px solid #e3d5bb; }
        .toggle-btn { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 20px; border: 0; border-radius: 999px; background: transparent; color: #4a443a; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s ease, color .15s ease; }
        .toggle-btn.active { background: #1b6b3a; color: #fff !important; }
        .toggle-btn em { font-style: normal; padding: 3px 8px; border-radius: 999px; background: #fdf0e1; color: #b9500a; font-size: 11px; font-weight: 800; }
        .toggle-btn.active em { background: #f6c38f; color: #5a2a06; }
        .toggle-btn:focus-visible, .category-card:focus-visible, .pl-cta:focus-visible, .ab-faq summary:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        /* Étapes */
        .ab-step { display: flex; align-items: center; gap: 12px; margin: 0 0 18px; }
        .ab-step b { flex: none; width: 36px; height: 36px; border-radius: 999px; background: #1b6b3a; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; }
        .ab-step h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(22px, 1vw + 18px, 30px); color: #1d1a16; }
        .ab-step span { margin-left: auto; font-size: 13.5px; color: #6b6458; }

        /* Secteurs */
        .ab-cats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .category-card { position: relative; display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 20px; background: #fff; border: 1.5px solid #ece5d6; text-align: left; cursor: pointer; transition: border-color .15s ease, background .15s ease, box-shadow .2s ease; }
        .category-card:hover { border-color: rgba(var(--c), .5); box-shadow: 0 16px 30px -26px rgba(29,26,22,.5); }
        .category-card .ab-cat__ic { flex: none; width: 46px; height: 46px; border-radius: 15px; background: rgba(var(--c), .12); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-size: 19px; transition: background .15s ease, color .15s ease; }
        .category-card b { display: block; font-size: 14.5px; line-height: 1.3; color: #1d1a16; }
        .category-card small { display: block; margin-top: 2px; font-size: 12.5px; color: #6b6458; }
        .category-card .ab-check { position: absolute; top: 10px; right: 10px; width: 22px; height: 22px; border-radius: 999px; background: rgb(var(--c)); color: #fff; font-size: 12px; font-weight: 800; display: none; align-items: center; justify-content: center; }
        .category-card.is-selected { border-color: rgb(var(--c)); background: rgba(var(--c), .06); }
        .category-card.is-selected .ab-cat__ic { background: rgb(var(--c)); color: #fff; }
        .category-card.is-selected .ab-check { display: flex; }

        /* Forfaits */
        .ab-plans { position: relative; padding: 46px 0 20px; }
        .plans-bg { background-size: cover; background-position: center; background-repeat: no-repeat; }
        .plans-bg[style*="--plans-bg-image"] { background-image: var(--plans-bg-image, none); }
        .cat-panel { animation: cat-panel-in .3s ease; }
        @keyframes cat-panel-in { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
        .ab-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; align-items: start; }
        .ab-empty { padding: 34px; border-radius: 24px; background: #fff; border: 1px dashed #d8cdb8; text-align: center; color: #6b6458; }

        .pl-card { position: relative; display: flex; flex-direction: column; padding: 26px 24px 24px; border-radius: 28px; background: #fff; border: 1.5px solid #ece5d6; transition: border-color .15s ease, box-shadow .2s ease; }
        .pl-card:hover { border-color: rgba(var(--c), .45); box-shadow: 0 22px 40px -30px rgba(29,26,22,.55); }
        .pl-card.is-popular { border-color: #1b6b3a; border-width: 2px; padding-top: 44px; }
        .pl-card__ribbon { position: absolute; left: -2px; right: -2px; top: -2px; margin: 0; display: flex; align-items: center; justify-content: center; gap: 7px; height: 34px; border-radius: 28px 28px 0 0; background: #1b6b3a; color: #fff; font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .pl-card__ribbon .ico { color: #f6c38f; }
        .pl-card__ic { display: flex; width: 50px; height: 50px; border-radius: 16px; background: rgba(var(--c), .12); color: rgb(var(--c)); align-items: center; justify-content: center; font-size: 22px; }
        .pl-card__head h3 { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: 24px; color: #1d1a16; }
        .pl-card__head p { margin: 6px 0 0; font-size: 14px; line-height: 1.55; color: #4a443a; min-height: 44px; }
        .pl-card__price { margin-top: 18px; padding: 16px 18px; border-radius: 18px; background: rgba(var(--c), .07); }
        .pl-amount { margin: 0; display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px; }
        .pl-amount b { font-family: 'Playfair Display', serif; font-size: 38px; line-height: 1; color: #1d1a16; }
        .pl-amount span { font-size: 13.5px; font-weight: 600; color: #6b6458; }
        .pl-note { margin: 6px 0 0; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 12.5px; color: #6b6458; }
        .pl-save { font-style: normal; padding: 2px 8px; border-radius: 999px; background: #e7f3ec; color: #1b6b3a; font-size: 11.5px; font-weight: 800; }
        .pl-cta { display: flex; align-items: center; justify-content: center; gap: 8px; height: 50px; margin-top: 18px; border-radius: 999px; background: #fff; border: 1.5px solid #b9500a; color: #9a4308; font-size: 15px; font-weight: 800; text-decoration: none; transition: background .15s ease, color .15s ease; }
        .pl-cta:hover { background: #fdf4ec; color: #9a4308; }
        .pl-card.is-popular .pl-cta { background: #b9500a; color: #fff; border-color: #b9500a; }
        .pl-card.is-popular .pl-cta:hover { background: #9a4308; }
        .pl-inc { margin: 18px 0 10px; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #8a7f6b; }
        .pl-feats { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 9px; }
        .pl-feats li { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; line-height: 1.4; color: #1d1a16; }
        .pl-feats li.is-no { color: #a39885; }
        .pl-tick { flex: none; width: 22px; height: 22px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; background: #e7f3ec; color: #1b6b3a; }
        .pl-feats li.is-no .pl-tick { background: #f1ece2; color: #b1a690; }

        /* Garanties */
        .ab-guar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 40px; }
        .ab-g { display: flex; align-items: center; gap: 14px; padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .ab-g > span { flex: none; width: 46px; height: 46px; border-radius: 15px; background: rgba(var(--c), .12); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .ab-g b { display: block; font-size: 15px; color: #1d1a16; }
        .ab-g small { display: block; margin-top: 2px; font-size: 13px; color: #6b6458; }

        /* FAQ */
        .ab-faq { margin-top: 56px; display: grid; grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr); gap: 28px; align-items: start; }
        .ab-faq h2 { margin: 6px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1vw + 18px, 32px); color: #1d1a16; }
        .ab-faq p.ab-sub { margin: 10px 0 0; font-size: 15px; line-height: 1.6; color: #4a443a; }
        .ab-label { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .ab-faq details { border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
        .ab-faq details + details { margin-top: 10px; }
        .ab-faq summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; font-size: 15px; font-weight: 700; color: #1d1a16; cursor: pointer; list-style: none; border-radius: 18px; }
        .ab-faq summary::-webkit-details-marker { display: none; }
        .ab-faq summary::after { content: '+'; flex: none; width: 28px; height: 28px; border-radius: 999px; background: #fdf4ec; color: #b9500a; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; }
        .ab-faq details[open] summary::after { content: '–'; }
        .ab-faq details p { margin: 0; padding: 0 18px 16px; font-size: 14.5px; line-height: 1.65; color: #4a443a; }

        /* Appel final */
        .ab-cta { position: relative; overflow: hidden; margin: 56px 0 72px; padding: 36px 36px; border-radius: 30px; background: #123a25; color: #fff; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 20px; align-items: center; }
        .ab-cta .ivm-g { opacity: .06 !important; }
        .ab-cta > :not(.ivm) { position: relative; z-index: 1; }
        .ab-cta h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.2vw + 18px, 34px); line-height: 1.15; }
        .ab-cta p { margin: 8px 0 0; font-size: 15px; color: #cfe6d7; }
        .ab-cta__btns { display: flex; flex-wrap: wrap; gap: 10px; }
        .ab-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 50px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; }
        .ab-btn .ico { font-size: 18px; }
        .ab-btn.is-white { background: #fff; color: #123a25; }
        .ab-btn.is-white:hover { background: #fdf4ec; }
        .ab-btn.is-ghost { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.5); }
        .ab-btn.is-ghost:hover { border-color: #fff; }

        @media (max-width: 1023px) {
            .ab-cats { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ab-grid { grid-template-columns: minmax(0, 1fr); max-width: 520px; margin: 0 auto; }
            .ab-faq, .ab-cta { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 640px) {
            .ab-wrap { padding: 0 16px; }
            /* Mobile : en-tête compact, bande de motifs visible autour de la carte */
            .ab-hero .ivm-g { opacity: .3 !important; }
            .ab-hero__in { padding: 30px 14px 30px; }
            .ab-hero__card { padding: 14px 14px 12px; border-radius: 18px; }
            .ab-kicker { font-size: 10.5px; padding: 5px 10px; }
            .ab-title { margin-top: 8px; font-size: clamp(22px, 6.4vw, 26px); line-height: 1.1; }
            .ab-lead { margin-top: 6px; font-size: 13px; line-height: 1.45; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .ab-billing { margin-top: 16px; }
            .ab-trust { margin-top: 10px; gap: 5px; }
            .ab-trust li { gap: 5px; padding: 4px 9px 4px 4px; border-radius: 999px; background: #fff; border: 1px solid #e3d5bb; font-size: 11px; font-weight: 600; }
            .ab-trust li > .ico:first-child { width: 22px !important; height: 22px !important; margin: 0 !important; font-size: 11px !important; }
            .ab-trust li > .ico:first-child > svg { width: 12px !important; height: 12px !important; }
            .toggle-btn { padding: 0 14px; font-size: 13.5px; }
            .ab-cats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .category-card { flex-direction: column; align-items: flex-start; gap: 10px; padding: 12px; }
            .ab-step span { display: none; }
            .ab-guar { grid-template-columns: minmax(0, 1fr); }
            .ab-cta { padding: 26px 20px; border-radius: 24px; }
            .ab-cta__btns .ab-btn { flex: 1 1 100%; }
        }
        @media (prefers-reduced-motion: reduce) { .cat-panel { animation: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-a2d53ae5.css') }}?v={{ @filemtime(public_path('css/tw-a2d53ae5.css')) }}">
</head>
<body class="antialiased">
    @include('partials.page-background')
@include('partials.public-top-nav')

@if(session('info'))<div class="ab-flash is-info" role="status">{{ session('info') }}</div>@endif
@if(session('error'))<div class="ab-flash is-error" role="alert">{{ session('error') }}</div>@endif

@php
    $sectorIcons = ['hotels' => 'fa-bed', 'restaurants' => 'fa-utensils', 'sites-touristiques' => 'fa-mountain-sun', 'agences-voyages' => 'fa-plane-departure', 'loisirs-culture' => 'fa-masks-theater', 'transports' => 'fa-van-shuttle', 'art-creations' => 'fa-palette'];
@endphp

{{-- ══ HÉROS ══ --}}
<section class="ab-hero">
    @include('partials.ivorian-motifs', ['culture' => 'national'])
    <div class="ab-hero__in">
        <div class="ab-hero__card">
            <span class="ab-kicker"><x-icon name="star-shine" />{{ __('Visibilité & croissance') }}</span>
            <h1 class="ab-title">{{ __('Choisissez votre offre') }} <em>{{ __('et faites-vous connaître') }}</em></h1>
            <p class="ab-lead">{{ __('Référencez votre activité sur le premier magazine culturel et touristique de Côte d\'Ivoire et touchez des milliers de visiteurs qualifiés.') }}</p>

            @if($showMonthly || $showYearly)
                <div class="ab-billing" role="group" aria-label="{{ __('Période de facturation') }}">
                    @if($showMonthly)
                        <button id="btn-monthly" type="button" onclick="setBilling('monthly')" class="toggle-btn active">{{ __('Mensuel') }}</button>
                    @endif
                    @if($showYearly)
                        <button id="btn-yearly" type="button" onclick="setBilling('yearly')" class="toggle-btn {{ ! $showMonthly ? 'active' : '' }}">
                            {{ __('Annuel') }} @if($yearlySavingsLabel)<em>{{ $yearlySavingsLabel }}</em>@endif
                        </button>
                    @endif
                </div>
            @endif

            <ul class="ab-trust">
                <li><x-icon name="shield-check" />{{ __('Paiement sécurisé CinetPay') }}</li>
                <li><x-icon name="restart" />{{ __('Mensuel sans engagement') }}</li>
                <li><x-icon name="chat-round-dots" />{{ __('Accompagnement dédié') }}</li>
            </ul>
        </div>
    </div>
</section>

@php
    $plansImage = \Illuminate\Support\Facades\Schema::hasTable('plans_section_images')
        ? \App\Models\PlansSectionImage::query()->find(1)
        : null;
@endphp

<main class="ab-wrap">
    {{-- ══ ÉTAPE 1 : SECTEUR ══ --}}
    <section style="padding-top: 40px" aria-labelledby="ab-step1">
        <div class="ab-step"><b>1</b><h2 id="ab-step1">{{ __('Votre secteur d\'activité') }}</h2><span>{{ __('Les forfaits s\'adaptent à votre métier') }}</span></div>
        <div class="ab-cats" id="category-cards">
            @foreach($rootCategories as $cat)
                @php $planCount = ($plansByCategory[$cat->slug] ?? collect())->count(); @endphp
                <button type="button" class="category-card lc c{{ ($loop->index % 7) + 1 }} {{ $selectedCategorySlug === $cat->slug ? 'is-selected' : '' }}" data-category-slug="{{ $cat->slug }}" aria-pressed="{{ $selectedCategorySlug === $cat->slug ? 'true' : 'false' }}">
                    <span class="ab-cat__ic"><i class="fas {{ $cat->icon ?: ($sectorIcons[$cat->slug] ?? 'fa-tag') }}" aria-hidden="true"></i></span>
                    <span class="min-w-0"><b>{{ $cat->localized('name') }}</b><small>{{ $planCount }} {{ $planCount > 1 ? __('forfaits disponibles') : __('forfait disponible') }}</small></span>
                    <span class="ab-check" aria-hidden="true">✓</span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- ══ ÉTAPE 2 : FORFAIT ══ --}}
    <section class="ab-plans {{ ($plansImage && $plansImage->isVisible()) ? 'plans-bg' : '' }}"
        @if($plansImage && $plansImage->isVisible()) style="--plans-bg-image: url('{{ $plansImage->image_url }}');" @endif
        id="plans-section">
        <p id="no-category-hint" class="ab-empty {{ $selectedCategorySlug ? 'hidden' : '' }}">{{ __('Sélectionnez une catégorie ci-dessus pour découvrir les forfaits qui lui sont dédiés.') }}</p>

        @foreach($rootCategories as $cat)
            @php $catPlans = $plansByCategory[$cat->slug] ?? collect(); @endphp
            <div class="cat-panel {{ $selectedCategorySlug === $cat->slug ? '' : 'hidden' }}" data-category-panel="{{ $cat->slug }}">
                <div class="ab-step"><b>2</b><h2>{{ __('Votre forfait') }} · {{ $cat->localized('name') }}</h2></div>
                @if($catPlans->isEmpty())
                    <p class="ab-empty">{{ __('Aucun forfait actif pour cette catégorie pour le moment.') }}</p>
                @else
                    <div class="ab-grid">
                        @foreach($catPlans as $i => $plan)
                            @include('public.partials._plan-card', ['plan' => $plan, 'i' => $i, 'count' => $catPlans->count(), 'category' => $cat])
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        {{-- Garanties --}}
        <div class="ab-guar">
            <div class="ab-g lc c2"><span><x-icon name="shield-check" /></span><div><b>{{ __('Paiement sécurisé') }}</b><small>{{ __('CinetPay, Mobile Money, carte bancaire') }}</small></div></div>
            <div class="ab-g lc c1"><span><x-icon name="restart" /></span><div><b>{{ __('Résiliation libre') }}</b><small>{{ __('Sans engagement pour les offres mensuelles') }}</small></div></div>
            <div class="ab-g lc c3"><span><x-icon name="chat-round-dots" /></span><div><b>{{ __('Support dédié') }}</b><small>{{ __('Une équipe disponible pour vous accompagner') }}</small></div></div>
        </div>
    </section>

    {{-- ══ FAQ ══ --}}
    <section class="ab-faq" aria-labelledby="ab-faq-t">
        <div>
            <p class="ab-label">{{ __('Questions fréquentes') }}</p>
            <h2 id="ab-faq-t">{{ __('Tout savoir avant de vous abonner') }}</h2>
            <p class="ab-sub">{{ __('Une autre question ? Notre équipe vous répond.') }}</p>
        </div>
        <div>
            <details open>
                <summary>{{ __('Comment se passe le paiement ?') }}</summary>
                <p>{{ __('Le paiement est sécurisé par CinetPay : Mobile Money ou carte bancaire. Le montant et la période choisie sont rappelés avant validation.') }}</p>
            </details>
            <details>
                <summary>{{ __('Puis-je arrêter mon abonnement ?') }}</summary>
                <p>{{ __('Oui : les offres mensuelles sont sans engagement et résiliables à tout moment.') }}</p>
            </details>
            <details>
                <summary>{{ __('Mensuel ou annuel : quelle différence ?') }}</summary>
                <p>{{ __('Le même forfait, payé en une fois pour l\'année à un prix réduit. Le prix mensuel équivalent et l\'économie réalisée sont indiqués sur chaque forfait.') }}</p>
            </details>
            <details>
                <summary>{{ __('Je n\'ai pas encore de compte prestataire') }}</summary>
                <p>{{ __('Choisissez simplement votre forfait : vous serez guidé pour créer votre compte prestataire, puis vers le paiement. Votre fiche est rattachée au secteur choisi.') }}</p>
            </details>
        </div>
    </section>

    {{-- ══ APPEL FINAL ══ --}}
    <section class="ab-cta">
        @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
        <div>
            <h2>{{ __('Besoin d\'un conseil pour choisir ?') }}</h2>
            <p>{{ __('Dites-nous ce que vous proposez : nous vous orientons vers la formule la plus adaptée.') }}</p>
        </div>
        <div class="ab-cta__btns">
            <button type="button" class="ab-btn is-white" onclick="window.openContactModal && window.openContactModal()"><x-icon name="chat-round-dots" />{{ __('Contactez-nous') }}</button>
            <a href="{{ route('register', ['role' => 'provider']) }}" class="ab-btn is-ghost"><x-icon name="shop" />{{ __('Créer un compte prestataire') }}</a>
        </div>
    </section>
</main>

<script>
    const SHOW_MONTHLY = @json((bool) $showMonthly);
    const SHOW_YEARLY  = @json((bool) $showYearly);
    let billing = SHOW_MONTHLY ? 'monthly' : 'yearly';

    function setBilling(type) {
        billing = type;
        const btnM = document.getElementById('btn-monthly');
        const btnY = document.getElementById('btn-yearly');
        if (btnM) { btnM.classList.toggle('active', type === 'monthly'); btnM.setAttribute('aria-pressed', type === 'monthly' ? 'true' : 'false'); }
        if (btnY) { btnY.classList.toggle('active', type === 'yearly'); btnY.setAttribute('aria-pressed', type === 'yearly' ? 'true' : 'false'); }
        document.querySelectorAll('.price-monthly').forEach(el => el.classList.toggle('hidden', type !== 'monthly'));
        document.querySelectorAll('.price-yearly').forEach(el => el.classList.toggle('hidden', type !== 'yearly'));
    }

    document.addEventListener('DOMContentLoaded', function () {
        setBilling(!SHOW_MONTHLY && SHOW_YEARLY ? 'yearly' : 'monthly');
    });

    // Sélection du secteur : révèle ses forfaits, sans rechargement de page.
    (function () {
        const cards = document.querySelectorAll('.category-card');
        const panels = document.querySelectorAll('.cat-panel');
        const hint = document.getElementById('no-category-hint');
        function selectCategory(slug, scroll) {
            cards.forEach(c => { const on = c.dataset.categorySlug === slug; c.classList.toggle('is-selected', on); c.setAttribute('aria-pressed', on ? 'true' : 'false'); });
            panels.forEach(p => p.classList.toggle('hidden', p.dataset.categoryPanel !== slug));
            if (hint) hint.classList.add('hidden');
            const url = new URL(window.location.href);
            url.searchParams.set('categorie', slug);
            window.history.replaceState({}, '', url);
            if (scroll) document.getElementById('plans-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        cards.forEach(card => card.addEventListener('click', () => selectCategory(card.dataset.categorySlug, true)));
    })();
</script>

@include('partials.homepage-footer')
</body>
</html>
