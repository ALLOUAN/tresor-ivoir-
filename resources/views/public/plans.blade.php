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
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        /* Page Abonnements : parcours en 2 étapes (secteur puis forfait), charte du site et couleurs du logo.
           Aplats, ombre au survol seulement. Classes conservées pour le script : .category-card / .cat-panel /
           .price-monthly / .price-yearly / #btn-monthly / #btn-yearly / .toggle-btn.active. */
        .lc { --c: 235,110,0; }
        .lc.c2 { --c: 0,135,79; } .lc.c3 { --c: 0,135,79; } .lc.c4 { --c: 235,110,0; } .lc.c5 { --c: 0,135,79; } .lc.c6 { --c: 235,110,0; } .lc.c7 { --c: 184,86,0; }
        .ab-wrap { max-width: 1120px; margin: 0 auto; padding: 0 20px; }
        .ab-flash { padding: 10px 16px; text-align: center; font-size: 14px; }
        .ab-flash.is-info { background: #F3F8F5; color: #00874F; }
        .ab-flash.is-error { background: #fdecea; color: #b42318; }

        /* Héros */
        .ab-hero { position: relative; overflow: hidden; background: #D8E9E0; border-bottom: 1px solid #D8E9E0; }
        .ab-hero .ivm-g { opacity: .16 !important; }
        .ab-hero__in { position: relative; z-index: 1; max-width: 860px; margin: 0 auto; padding: 44px 20px 56px; text-align: center; }
        .ab-hero__card { padding: 30px 32px; border-radius: 30px; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .ab-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #B85600; }
        .ab-kicker .ico { font-size: 15px; }
        .ab-title { margin: 16px 0 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 2.6vw + 18px, 56px); font-weight: 700; line-height: 1.05; letter-spacing: -.015em; color: #0E3B2A; }
        .ab-title em { font-style: italic; color: #EB6E00; }
        .ab-lead { margin: 14px auto 0; max-width: 620px; font-size: 16.5px; line-height: 1.65; color: #0E3B2A; }
        .ab-trust { display: flex; flex-wrap: wrap; justify-content: center; gap: 8px 20px; margin: 18px 0 0; padding: 0; list-style: none; font-size: 13.5px; color: #0E3B2A; }
        .ab-trust li { display: inline-flex; align-items: center; gap: 7px; }
        .ab-trust .ico { color: #00874F; font-size: 17px; }

        /* Bascule mensuel / annuel */
        .ab-billing { display: inline-flex; align-items: center; gap: 4px; margin-top: 22px; padding: 5px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; }
        .toggle-btn { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 20px; border: 0; border-radius: 999px; background: transparent; color: #24503D; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s ease, color .15s ease; }
        .toggle-btn.active { background: #00874F; color: #fff !important; }
        .toggle-btn em { font-style: normal; padding: 3px 8px; border-radius: 999px; background: #F3F8F5; color: #EB6E00; font-size: 11px; font-weight: 800; }
        .toggle-btn.active em { background: #F7C396; color: #0E3B2A; }
        .toggle-btn:focus-visible, .category-card:focus-visible, .pl-cta:focus-visible, .ab-faq summary:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

        /* Étapes */
        .ab-step { display: flex; align-items: center; gap: 12px; margin: 0 0 18px; }
        .ab-step b { flex: none; width: 36px; height: 36px; border-radius: 999px; background: #00874F; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 15px; }
        .ab-step h2 { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(22px, 1vw + 18px, 30px); color: #0E3B2A; }
        .ab-step span { margin-left: auto; font-size: 13.5px; color: #557466; }

        /* Secteurs */
        .ab-cats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
        .category-card { position: relative; display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 20px; background: #fff; border: 1.5px solid #D8E9E0; text-align: left; cursor: pointer; transition: border-color .15s ease, background .15s ease, box-shadow .2s ease; }
        .category-card:hover { border-color: rgba(var(--c), .5); box-shadow: 0 16px 30px -26px rgba(14,59,42,.5); }
        .category-card .ab-cat__ic { flex: none; width: 46px; height: 46px; border-radius: 15px; background: rgba(var(--c), .12); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-size: 19px; transition: background .15s ease, color .15s ease; }
        .category-card b { display: block; font-size: 14.5px; line-height: 1.3; color: #0E3B2A; }
        .category-card small { display: block; margin-top: 2px; font-size: 12.5px; color: #557466; }
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
        .ab-empty { padding: 34px; border-radius: 24px; background: #fff; border: 1px dashed #8AA99B; text-align: center; color: #557466; }

        .pl-card { position: relative; display: flex; flex-direction: column; padding: 26px 24px 24px; border-radius: 28px; background: #fff; border: 1.5px solid #D8E9E0; transition: border-color .15s ease, box-shadow .2s ease; }
        .pl-card:hover { border-color: rgba(var(--c), .45); box-shadow: 0 22px 40px -30px rgba(14,59,42,.55); }
        .pl-card.is-popular { border-color: #00874F; border-width: 2px; padding-top: 44px; }
        .pl-card__ribbon { position: absolute; left: -2px; right: -2px; top: -2px; margin: 0; display: flex; align-items: center; justify-content: center; gap: 7px; height: 34px; border-radius: 28px 28px 0 0; background: #00874F; color: #fff; font-size: 12.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
        .pl-card__ribbon .ico { color: #F7C396; }
        .pl-card__ic { display: flex; width: 50px; height: 50px; border-radius: 16px; background: rgba(var(--c), .12); color: rgb(var(--c)); align-items: center; justify-content: center; font-size: 22px; }
        .pl-card__head h3 { margin: 16px 0 0; font-family: 'Montserrat', serif; font-size: 24px; color: #0E3B2A; }
        .pl-card__head p { margin: 6px 0 0; font-size: 14px; line-height: 1.55; color: #24503D; min-height: 44px; }
        .pl-card__price { margin-top: 18px; padding: 16px 18px; border-radius: 18px; background: rgba(var(--c), .07); }
        .pl-amount { margin: 0; display: flex; align-items: baseline; flex-wrap: wrap; gap: 6px; }
        .pl-amount b { font-family: 'Montserrat', serif; font-size: 38px; line-height: 1; color: #0E3B2A; }
        .pl-amount span { font-size: 13.5px; font-weight: 600; color: #557466; }
        .pl-note { margin: 6px 0 0; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; font-size: 12.5px; color: #557466; }
        .pl-save { font-style: normal; padding: 2px 8px; border-radius: 999px; background: #F3F8F5; color: #00874F; font-size: 11.5px; font-weight: 800; }
        .pl-cta { display: flex; align-items: center; justify-content: center; gap: 8px; height: 50px; margin-top: 18px; border-radius: 999px; background: #fff; border: 1.5px solid #EB6E00; color: #B85600; font-size: 15px; font-weight: 800; text-decoration: none; transition: background .15s ease, color .15s ease; }
        .pl-cta:hover { background: #F3F8F5; color: #B85600; }
        .pl-card.is-popular .pl-cta { background: #EB6E00; color: #fff; border-color: #EB6E00; }
        .pl-card.is-popular .pl-cta:hover { background: #B85600; }
        .pl-inc { margin: 18px 0 10px; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .pl-feats { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 9px; }
        .pl-feats li { display: flex; align-items: flex-start; gap: 10px; font-size: 14px; line-height: 1.4; color: #0E3B2A; }
        .pl-feats li.is-no { color: #8AA99B; }
        .pl-tick { flex: none; width: 22px; height: 22px; border-radius: 999px; display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 800; background: #F3F8F5; color: #00874F; }
        .pl-feats li.is-no .pl-tick { background: #F3F8F5; color: #8AA99B; }

        /* Garanties */
        .ab-guar { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; margin-top: 40px; }
        .ab-g { display: flex; align-items: center; gap: 14px; padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
        .ab-g > span { flex: none; width: 46px; height: 46px; border-radius: 15px; background: rgba(var(--c), .12); color: rgb(var(--c)); display: flex; align-items: center; justify-content: center; font-size: 20px; }
        .ab-g b { display: block; font-size: 15px; color: #0E3B2A; }
        .ab-g small { display: block; margin-top: 2px; font-size: 13px; color: #557466; }

        /* FAQ */
        .ab-faq { margin-top: 56px; display: grid; grid-template-columns: minmax(0, .8fr) minmax(0, 1.2fr); gap: 28px; align-items: start; }
        .ab-faq h2 { margin: 6px 0 0; font-family: 'Montserrat', serif; font-size: clamp(24px, 1vw + 18px, 32px); color: #0E3B2A; }
        .ab-faq p.ab-sub { margin: 10px 0 0; font-size: 15px; line-height: 1.6; color: #24503D; }
        .ab-label { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #EB6E00; }
        .ab-faq details { border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; }
        .ab-faq details + details { margin-top: 10px; }
        .ab-faq summary { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 18px; font-size: 15px; font-weight: 700; color: #0E3B2A; cursor: pointer; list-style: none; border-radius: 18px; }
        .ab-faq summary::-webkit-details-marker { display: none; }
        .ab-faq summary::after { content: '+'; flex: none; width: 28px; height: 28px; border-radius: 999px; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: 700; }
        .ab-faq details[open] summary::after { content: '–'; }
        .ab-faq details p { margin: 0; padding: 0 18px 16px; font-size: 14.5px; line-height: 1.65; color: #24503D; }

        /* Appel final */
        .ab-cta { position: relative; overflow: hidden; margin: 56px 0 72px; padding: 36px 36px; border-radius: 30px; background: #00874F; color: #fff; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 20px; align-items: center; }
        .ab-cta .ivm-g { opacity: .06 !important; }
        .ab-cta > :not(.ivm) { position: relative; z-index: 1; }
        .ab-cta h2 { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(24px, 1.2vw + 18px, 34px); line-height: 1.15; }
        .ab-cta p { margin: 8px 0 0; font-size: 15px; color: #D8E9E0; }
        .ab-cta__btns { display: flex; flex-wrap: wrap; gap: 10px; }
        .ab-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 50px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; }
        .ab-btn .ico { font-size: 18px; }
        .ab-btn.is-white { background: #fff; color: #00874F; }
        .ab-btn.is-white:hover { background: #F3F8F5; }
        .ab-btn.is-ghost { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.5); }
        .ab-btn.is-ghost:hover { border-color: #fff; }

        /* Plusieurs secteurs : forfait choisi par secteur + barre récapitulative (total calculé en direct) */
        .cat-panel + .cat-panel { margin-top: 42px; }
        .pl-cta { width: 100%; cursor: pointer; font-family: inherit; }
        .pl-cta .pl-cta__on { display: none; }
        .pl-card.is-picked { border-color: #00874F; border-width: 2px; box-shadow: inset 0 0 0 1px #00874F; }
        .pl-card.is-picked .pl-cta { background: #00874F; border-color: #00874F; color: #fff; }
        .pl-card.is-picked .pl-cta .pl-cta__off { display: none; }
        .pl-card.is-picked .pl-cta .pl-cta__on { display: inline; }
        .ab-owned { display: inline-block; margin-top: 4px; padding: 2px 8px; border-radius: 999px; background: #F3F8F5; color: #00874F; font-size: 11px; font-weight: 800; }
        .ab-sum { position: fixed; left: 50%; bottom: 16px; z-index: 60; width: min(1080px, calc(100% - 24px)); transform: translate(-50%, 140%); display: grid; grid-template-columns: minmax(0, 1fr) auto auto; align-items: center; gap: 18px; padding: 12px 12px 12px 20px; border-radius: 22px; background: #00874F; color: #fff; box-shadow: 0 18px 40px -18px rgba(0,135,79,.6); transition: transform .3s ease; }
        .ab-sum.is-on { transform: translate(-50%, 0); }
        .ab-sum__items { display: flex; flex-wrap: wrap; gap: 6px; min-width: 0; }
        .ab-sum__item { display: inline-flex; align-items: center; gap: 6px; max-width: 100%; height: 30px; padding: 0 11px; border-radius: 999px; background: rgba(255,255,255,.12); font-size: 12.5px; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ab-sum__item.is-missing { background: rgba(247,195,150,.2); color: #F7C396; }
        .ab-sum__total { text-align: right; line-height: 1.15; }
        .ab-sum__total small { display: block; font-size: 11.5px; color: #D8E9E0; }
        .ab-sum__total b { font-family: 'Montserrat', serif; font-size: 24px; }
        .ab-sum__total span { font-size: 12.5px; color: #D8E9E0; }
        .ab-sum__go { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 50px; padding: 0 22px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; white-space: nowrap; transition: background .15s ease; }
        .ab-sum__go:hover { background: #B85600; }
        .ab-sum__go[aria-disabled="true"] { background: #557466; pointer-events: none; }
        .ab-sum__go:focus-visible { outline: 3px solid #F7C396; outline-offset: 2px; }
        body.has-sum { padding-bottom: 110px; }

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
            .ab-trust li { gap: 5px; padding: 4px 9px 4px 4px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; font-size: 11px; font-weight: 600; }
            .ab-trust li > .ico:first-child { width: 22px !important; height: 22px !important; margin: 0 !important; font-size: 11px !important; }
            .ab-trust li > .ico:first-child > svg { width: 12px !important; height: 12px !important; }
            .toggle-btn { padding: 0 14px; font-size: 13.5px; }
            .ab-cats { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
            .category-card { flex-direction: column; align-items: flex-start; gap: 10px; padding: 12px; }
            .ab-step span { display: none; }
            .ab-guar { grid-template-columns: minmax(0, 1fr); }
            .ab-cta { padding: 26px 20px; border-radius: 24px; }
            .ab-cta__btns .ab-btn { flex: 1 1 100%; }
            .ab-sum { bottom: 76px; grid-template-columns: minmax(0, 1fr) auto; gap: 10px; padding: 10px 10px 10px 14px; border-radius: 18px; }
            .ab-sum__items { grid-column: 1 / -1; }
            .ab-sum__item { height: 26px; font-size: 11.5px; }
            .ab-sum__total { text-align: left; white-space: nowrap; }
            .ab-sum__total small { display: inline; margin-right: 6px; }
            .ab-sum__total b { font-size: 20px; }
            .ab-sum__go { height: 44px; padding: 0 16px; font-size: 14px; }
            body.has-sum { padding-bottom: 210px; }
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
        <div class="ab-step"><b>1</b><h2 id="ab-step1">{{ __('Vos secteurs d\'activité') }}</h2><span>{{ __('Choisissez un ou plusieurs secteurs') }}</span></div>
        <div class="ab-cats" id="category-cards">
            @foreach($rootCategories as $cat)
                @php $planCount = ($plansByCategory[$cat->slug] ?? collect())->count(); $isSel = in_array($cat->slug, $selectedSlugs, true); @endphp
                <button type="button" class="category-card lc c{{ ($loop->index % 7) + 1 }} {{ $isSel ? 'is-selected' : '' }}" data-category-slug="{{ $cat->slug }}" data-category-name="{{ $cat->localized('name') }}" aria-pressed="{{ $isSel ? 'true' : 'false' }}">
                    <span class="ab-cat__ic"><i class="fas {{ $cat->icon ?: ($sectorIcons[$cat->slug] ?? 'fa-tag') }}" aria-hidden="true"></i></span>
                    <span class="min-w-0"><b>{{ $cat->localized('name') }}</b><small>{{ $planCount }} {{ $planCount > 1 ? __('forfaits disponibles') : __('forfait disponible') }}</small>
                        @if(in_array($cat->slug, $ownedSectors, true))<span class="ab-owned">{{ __('Votre secteur') }}</span>@endif</span>
                    <span class="ab-check" aria-hidden="true">✓</span>
                </button>
            @endforeach
        </div>
    </section>

    {{-- ══ ÉTAPE 2 : FORFAIT ══ --}}
    <section class="ab-plans {{ ($plansImage && $plansImage->isVisible()) ? 'plans-bg' : '' }}"
        @if($plansImage && $plansImage->isVisible()) style="--plans-bg-image: url('{{ $plansImage->image_url }}');" @endif
        id="plans-section">
        <p id="no-category-hint" class="ab-empty {{ $selectedSlugs ? 'hidden' : '' }}">{{ __('Sélectionnez un ou plusieurs secteurs ci-dessus pour découvrir leurs forfaits.') }}</p>

        @foreach($rootCategories as $cat)
            @php $catPlans = $plansByCategory[$cat->slug] ?? collect(); @endphp
            <div class="cat-panel {{ in_array($cat->slug, $selectedSlugs, true) ? '' : 'hidden' }}" data-category-panel="{{ $cat->slug }}">
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
                <summary>{{ __('J\'ai plusieurs activités : puis-je choisir plusieurs secteurs ?') }}</summary>
                <p>{{ __('Oui. Sélectionnez chaque secteur (par exemple Hôtel et Restaurant), puis un forfait pour chacun : le total est calculé automatiquement et réglé en un seul paiement. Votre tableau de bord regroupe ensuite tous vos secteurs.') }}</p>
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

{{-- Barre récapitulative : secteurs et forfaits choisis, total en direct, accès au paiement unique. --}}
<div class="ab-sum" id="ab-sum" role="region" aria-label="{{ __('Votre sélection') }}" aria-live="polite">
    <div class="ab-sum__items" id="ab-sum-items"></div>
    <p class="ab-sum__total"><small id="ab-sum-count"></small><b id="ab-sum-amount">0</b> <span id="ab-sum-period">{{ __('FCFA / mois') }}</span></p>
    <a href="#" class="ab-sum__go" id="ab-sum-go" aria-disabled="true">{{ __('Continuer') }} <span aria-hidden="true">→</span></a>
</div>

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
        if (window.abRenderSummary) window.abRenderSummary();
    }

    document.addEventListener('DOMContentLoaded', function () {
        setBilling(!SHOW_MONTHLY && SHOW_YEARLY ? 'yearly' : 'monthly');
    });

    // Plusieurs secteurs : chaque carte secteur s'active / se désactive ; un forfait choisi par secteur ;
    // la barre du bas affiche la sélection et le total (mensuel ou annuel), puis mène au paiement unique.
    (function () {
        const cards = Array.from(document.querySelectorAll('.category-card'));
        const panels = Array.from(document.querySelectorAll('.cat-panel'));
        const hint = document.getElementById('no-category-hint');
        const sum = document.getElementById('ab-sum');
        const itemsEl = document.getElementById('ab-sum-items');
        const amountEl = document.getElementById('ab-sum-amount');
        const periodEl = document.getElementById('ab-sum-period');
        const countEl = document.getElementById('ab-sum-count');
        const go = document.getElementById('ab-sum-go');
        const checkoutTpl = {{ \Illuminate\Support\Js::from(route('subscriptions.checkout', ['plan' => '__PLAN__'])) }};
        const T = {{ \Illuminate\Support\Js::from(['month' => __('FCFA / mois'), 'year' => __('FCFA / an'), 'pick' => __('forfait à choisir'), 'one' => __('1 secteur'), 'many' => __(':n secteurs'), 'go' => __('Continuer'), 'goMany' => __('Continuer avec :n secteurs')]) }};
        let selected = {{ \Illuminate\Support\Js::from($selectedSlugs) }};   // ordre de sélection des secteurs
        const picks = {};                         // secteur -> bouton du forfait choisi
        const fmt = n => Math.round(n).toLocaleString('fr-FR').replace(/ | /g, ' ');

        function render() {
            cards.forEach(c => { const on = selected.includes(c.dataset.categorySlug); c.classList.toggle('is-selected', on); c.setAttribute('aria-pressed', on ? 'true' : 'false'); });
            panels.forEach(p => p.classList.toggle('hidden', !selected.includes(p.dataset.categoryPanel)));
            // Les panneaux suivent l'ordre de sélection.
            const parent = panels[0]?.parentNode;
            selected.forEach(slug => { const p = panels.find(x => x.dataset.categoryPanel === slug); if (p && parent) parent.insertBefore(p, parent.querySelector('.ab-guar')); });
            if (hint) hint.classList.toggle('hidden', selected.length > 0);
            document.querySelectorAll('[data-pick-plan]').forEach(b => {
                const on = picks[b.dataset.sector] === b;
                b.setAttribute('aria-pressed', on ? 'true' : 'false');
                b.closest('.pl-card')?.classList.toggle('is-picked', on);
            });
            renderSummary();
            const url = new URL(window.location.href);
            url.searchParams.delete('categorie');
            selected.length ? url.searchParams.set('secteurs', selected.join(',')) : url.searchParams.delete('secteurs');
            window.history.replaceState({}, '', url);
        }

        function renderSummary() {
            const yearly = billing === 'yearly';
            let total = 0, missing = 0;
            itemsEl.innerHTML = '';
            selected.forEach(slug => {
                const card = cards.find(c => c.dataset.categorySlug === slug);
                const btn = picks[slug];
                const chip = document.createElement('span');
                chip.className = 'ab-sum__item' + (btn ? '' : ' is-missing');
                chip.textContent = (card?.dataset.categoryName || slug) + ' · ' + (btn ? btn.dataset.planName : T.pick);
                itemsEl.appendChild(chip);
                if (btn) total += Number(yearly ? btn.dataset.y : btn.dataset.m); else missing++;
            });
            amountEl.textContent = fmt(total);
            periodEl.textContent = yearly ? T.year : T.month;
            countEl.textContent = selected.length > 1 ? T.many.replace(':n', selected.length) : T.one;
            const ready = selected.length > 0 && missing === 0;
            go.setAttribute('aria-disabled', ready ? 'false' : 'true');
            go.firstChild.textContent = (selected.length > 1 ? T.goMany.replace(':n', selected.length) : T.go) + ' ';
            if (ready) {
                const [first, ...rest] = selected;
                const url = new URL(checkoutTpl.replace('__PLAN__', picks[first].dataset.pickPlan), window.location.origin);
                url.searchParams.set('categorie', first);
                if (rest.length) url.searchParams.set('avec', rest.map(s => s + ':' + picks[s].dataset.pickPlan).join(','));
                go.href = url.toString();
            } else {
                go.href = '#plans-section';
            }
            const show = selected.length > 0;
            sum.classList.toggle('is-on', show);
            document.body.classList.toggle('has-sum', show);
        }
        window.abRenderSummary = renderSummary;

        cards.forEach(card => card.addEventListener('click', () => {
            const slug = card.dataset.categorySlug;
            if (selected.includes(slug)) {
                selected = selected.filter(s => s !== slug);
                delete picks[slug];
            } else {
                selected.push(slug);
            }
            render();
            if (selected.includes(slug)) document.querySelector('[data-category-panel="' + slug + '"]')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }));
        document.querySelectorAll('[data-pick-plan]').forEach(btn => btn.addEventListener('click', () => {
            picks[btn.dataset.sector] = btn;
            render();
        }));
        render();
    })();
</script>

@include('partials.homepage-footer')
</body>
</html>
