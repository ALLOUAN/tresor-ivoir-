<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ $siteBrand['site_name'] }} — {{ __('Magazine Culturel & Touristique Premium') }}</title>
    <meta name="description" content="{{ $siteBrand['site_description'] ?: __('Découvrez la Côte d\'Ivoire à travers ses richesses culturelles, touristiques et patrimoniales. Grand reportage, adresses choisies, art de vivre ivoirien.') }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
        <style>
        * { box-sizing: border-box; }

        /* ── Typography ─────────────────────────────────────── */
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }

        /* ── Hero background ─────────────────────────────────── */
        .hero-bg {
            background-image:
                linear-gradient(rgba(10, 9, 6, 0.74), rgba(10, 9, 6, 0.74)),
                url('/images/hero-bg.jpg');
            background-size: cover;
            background-position: center 30%;
        }
        /* Fallback when no image */
        .hero-bg-fallback {
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .hero-bg-fallback::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(transparent, transparent),
                        linear-gradient(transparent, transparent);
            pointer-events: none;
        }
        .hero-bg-fallback::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(242, 121, 15, 0.01), rgba(242, 121, 15, 0.01)),
                linear-gradient(rgba(242, 121, 15, 0.01), rgba(242, 121, 15, 0.01));
            pointer-events: none;
        }
        .hero-viewport { min-height: auto; }
        #hero.hero-bg-fallback,
        #hero .hero-viewport {
            min-height: auto !important;
        }
        #hero > .hero-viewport {
            display: block;
        }
        #hero-bg-carousel.hero-viewport {
            position: relative;
            inset: auto;
            width: 100%;
            height: auto;
            min-height: 0;
            aspect-ratio: 16 / 8.5;
        }
        #hero-bg-carousel .hero-bg-layer,
        #hero-bg-carousel picture,
        #hero-bg-carousel img,
        #hero-bg-carousel video {
            height: 100%;
            object-fit: cover;
        }
        #hero-bg-carousel img   { object-position: 52% 42%; }
        #hero-bg-carousel video { object-position: center center; }
        #hero-bg-carousel .hero-bg-layer { background:#e9e5d9; }
        #hero .hero-editorial-content {
            display: block;
            flex: none;
            align-items: flex-start;
        }
        #hero .hero-editorial-copy {
            max-width: 100%;
            padding-top: 1.25rem;
            padding-bottom: 2.25rem;
        }
        #hero .hero-bg-controls { bottom: 0.9rem; }
        #hero .hero-scroll-indicator { display: none; }

        @media (max-width: 639px) {
            #hero-bg-carousel.hero-viewport { aspect-ratio: 16 / 8.5; }
        }
        @media (min-width: 640px) and (max-width: 1023px) {
            #hero-bg-carousel.hero-viewport { aspect-ratio: 16 / 8; }
            #hero-bg-carousel img { object-position: 54% 40%; }
            #hero .hero-editorial-copy {
                padding-top: 1.75rem;
                padding-bottom: 2.75rem;
                max-width: min(92%, 44rem);
            }
            #hero .hero-bg-controls { bottom: 1rem; }
        }
        @media (min-width: 1024px) and (max-width: 1439px) {
            #hero-bg-carousel.hero-viewport { aspect-ratio: 16 / 7; }
            #hero-bg-carousel img { object-position: 50% 42%; }
            #hero .hero-editorial-copy {
                max-width: min(92vw, 48rem);
                padding-top: 2rem;
                padding-bottom: 3.25rem;
            }
            #hero .hero-bg-controls { bottom: 1.25rem; }
        }
        @media (min-width: 1440px) {
            #hero-bg-carousel.hero-viewport { aspect-ratio: 16 / 6.4; }
            #hero-bg-carousel img { object-position: center center; }
            #hero .hero-editorial-copy {
                max-width: 52rem;
                padding-top: 2.25rem;
                padding-bottom: 3.5rem;
            }
            #hero .hero-bg-controls { bottom: 1.5rem; }
        }
        /* Téléphone/petite tablette tenus en paysage : la fenêtre est courte
           en hauteur, donc un ratio pensé pour du portrait (16/8.5 etc.)
           donne un hero bien trop haut par rapport à l'écran visible. On
           réduit le ratio spécifiquement dans ce cas (après les règles par
           largeur ci-dessus, pour les surclasser à égalité de spécificité
           quand les deux s'appliquent). */
        @media (max-height: 500px) and (orientation: landscape) {
            #hero-bg-carousel.hero-viewport { aspect-ratio: 16 / 5; }
        }

        /* ── Scrollbar ───────────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background:#e9e5d9; }
        ::-webkit-scrollbar-thumb { background: #f2790f; border-radius: 3px; }

        /* ── Gold line decoration ────────────────────────────── */
        .gold-line::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: #f68a26;
            margin-top: 12px;
        }
        .gold-line-center::after { margin-left: auto; margin-right: auto; }

        /* ── Card hover effect ───────────────────────────────── */
        .article-card:hover .article-img { transform: scale(1.05); }
        .article-img { transition: transform .6s ease; }
        /* « À la une » : les cartes ont un cadre blanc (ui-kit) — marge intérieure pour que
           le texte ne touche pas la bordure, image arrondie à l'intérieur du cadre. */
        #articles .article-card { padding: 12px 12px 14px; border-radius: 16px; }
        #articles .article-card > .relative:first-child { border-radius: 10px; }

        /* ── Animate on scroll ───────────────────────────────── */
        .reveal { opacity: 0; transform: translateY(28px); transition: opacity .7s ease, transform .7s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }

        /* ── Mobile menu ─────────────────────────────────────── */
        #mobile-menu { display: none; }
        #mobile-menu.open { display: block; animation: slideDown .3s ease; }

        /* ── Partners horizontal carousel ────────────────────── */
        .partners-marquee {
            position: relative;
            overflow: hidden;
            padding-block: 10px 18px; /* place pour le soulèvement et l'ombre des cartes au survol */
        }
        .partners-track {
            display: flex;
            width: max-content;
            gap: 1rem;
            animation: partnersScroll 28s linear infinite;
            will-change: transform;
        }
        .partners-marquee:hover .partners-track {
            animation-play-state: paused;
        }
        .ti-partner { width: 240px; flex-shrink: 0; }
        @keyframes partnersScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 640px) {
            .partners-track { animation-duration: 16s; gap: 10px; }
            /* Section et cartes plus compactes sur mobile (elles prenaient presque tout l'écran). */
            #partenaires { padding-top: 28px; padding-bottom: 28px; }
            #partenaires .text-center.mb-12 { margin-bottom: 14px; }
            #partenaires .text-center h2 { font-size: 22px; margin-bottom: 6px; }
            #partenaires .text-center p.text-xl { font-size: 14px; line-height: 1.4; }
            #partenaires .text-center p.mb-3 { margin-bottom: 4px; font-size: 11px; }
            #partenaires .ti-partner { width: 128px; gap: 6px; padding: 10px 10px 9px; border-radius: 14px; }
            #partenaires .ti-partner__mono { width: 30px; height: 30px; border-radius: 9px; font-size: 11px; }
            #partenaires .ti-partner__logo { height: 32px; }
            #partenaires .ti-partner__name { min-height: 0; font-size: 12px; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            #partenaires .ti-partner__type { height: 18px; padding: 0 6px; gap: 4px; font-size: 8.5px; }
            #partenaires .ti-partner__type i { font-size: 8px; }
            #partenaires .ti-partner__visit { font-size: 10.5px; gap: 4px; }
            #partenaires .ti-partner__visit i { font-size: 9px; }
        }

        /* ── Featured section title (special design) ───────── */
        .featured-title-wrap {
            position: relative;
            display: flex;
            align-items: center;
            gap: 0.9rem;
        }
        .featured-title-line {
            flex: 1;
            height: 1px;
            background: rgba(242, 121, 15, 0.23);
        }
        .featured-title-line.reverse {
            background: rgba(242, 121, 15, 0.23);
        }
        .featured-title-badge {
            position: relative;
            display: inline-flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.55rem 1.2rem;
            border-radius: 9999px;
            border: 1px solid rgba(242, 121, 15,0.38);
            background:
                linear-gradient(rgba(249, 188, 135, 0.11), rgba(249, 188, 135, 0.11)),
                rgba(255, 255, 255,0.84);
            box-shadow: 0 10px 24px rgba(0,0,0,0.26), inset 0 1px 0 rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }
        .featured-title-dot {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 9999px;
            background: #f68a26;
            box-shadow: 0 0 10px rgba(242, 121, 15,0.65);
        }
        .featured-title-text {
            font-family: 'Cormorant Garamond', Georgia, serif;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.25em;
            text-transform: uppercase;
            color: #fdbe7b;
        }

        /* Section Annuaire : onglets « secteur » (le pont de thème clair réécrit les
           couleurs de texte, l'état actif est donc fixé explicitement ici). */
        [data-annuaire-panel][hidden] { display: none !important; }
        .annuaire-tab { width: 100%; cursor: pointer; }
        .annuaire-tab.is-active { background: #f3eee4; }
        .annuaire-tab.is-active .annuaire-tab-icon { border-color: #d8cdb8; background: #ffffff; }
        .annuaire-tab.is-active .annuaire-tab-label { color: #1d1a16 !important; }
        .annuaire-tab.is-active .annuaire-tab-chevron { opacity: 1; transform: none; color: #b9500a; }
        /* Secteurs aux couleurs du logo (coucher de soleil, soleil, palmier, lagune, anneau doré, mer, brun du texte) */
        .annuaire-tab { --c: #7a4a2a; --t: #f5ede3; }
        .annuaire-tab[data-annuaire-tab="hotels"] { --c: #d9650f; --t: #fdf0e1; }
        .annuaire-tab[data-annuaire-tab="restaurants"] { --c: #c98a0c; --t: #fdf5dc; }
        .annuaire-tab[data-annuaire-tab="sites-touristiques"] { --c: #4a8a2a; --t: #eef5e4; }
        .annuaire-tab[data-annuaire-tab="agences-voyages"] { --c: #1f8079; --t: #e3f3f1; }
        .annuaire-tab[data-annuaire-tab="loisirs-culture"] { --c: #b0861c; --t: #fbf3dc; }
        .annuaire-tab[data-annuaire-tab="transports"] { --c: #2c6e8f; --t: #e6f0f5; }
        #annuaire-tabs .annuaire-tab .annuaire-tab-icon { width: 36px; height: 36px; border-radius: 12px; background: var(--t) !important; border: 1px solid color-mix(in srgb, var(--c) 22%, transparent) !important; transition: background .2s ease, border-color .2s ease; }
        #annuaire-tabs .annuaire-tab .annuaire-tab-icon i { color: var(--c) !important; font-size: 14px; }
        #annuaire-tabs .annuaire-tab:hover { background: var(--t) !important; }
        #annuaire-tabs .annuaire-tab:hover .annuaire-tab-label { color: #1d1a16 !important; }
        #annuaire-tabs .annuaire-tab:hover .annuaire-tab-chevron { color: var(--c) !important; }
        #annuaire-tabs .annuaire-tab.is-active { background: var(--t) !important; box-shadow: inset 3px 0 0 var(--c); }
        #annuaire-tabs .annuaire-tab.is-active .annuaire-tab-icon { background: var(--c) !important; border-color: var(--c) !important; }
        #annuaire-tabs .annuaire-tab.is-active .annuaire-tab-icon i { color: #fff !important; }
        #annuaire-tabs .annuaire-tab.is-active .annuaire-tab-chevron { color: var(--c) !important; }
        #annuaire-tabs .annuaire-tab-label { color: #3a2f22 !important; }
        #annuaireSearchInput { background: #fff !important; border: 1.5px solid #e6cf98 !important; color: #1d1a16 !important; }
        #annuaireSearchInput:focus { border-color: #d9650f !important; box-shadow: 0 0 0 4px rgba(217,101,15,.12); }
        #annuaireSearchWrap .fa-magnifying-glass { color: #d9650f !important; }

        @media (prefers-reduced-motion: reduce) {
            .social-icon-ultra,
            .social-icon-ultra::before,
            .social-icon-ultra i,
            .footer-logo-ring,
            .footer-logo-ring::before,
            .home-provider-card-bg {
                transition: none;
                animation: none;
            }
        }

        /* ── Light mode: body & textes ──────────────────── */
        html:not(.dark) body           { background-color:#e9e5d9!important; color:#1c1915!important; }
        html:not(.dark) .text-white    { color:#1c1915!important; }
        html:not(.dark) .text-gray-100 { color:#1c1915!important; }
        html:not(.dark) .text-gray-200 { color:#2d2a23!important; }
        html:not(.dark) .text-gray-300 { color:#44413a!important; }
        html:not(.dark) .text-gray-400 { color:#544f47!important; }
        html:not(.dark) .text-gray-500 { color:#665f52!important; }
        html:not(.dark) .text-slate-400 { color:#544f47!important; }
        html:not(.dark) .text-slate-500 { color:#665f52!important; }

        /* ── Light mode: custom dark palette ────────────── */
        html:not(.dark) .bg-dark-900  { background-color:#e9e5d9!important; }
        html:not(.dark) .bg-dark-800  { background-color:#f0ece4!important; }
        html:not(.dark) .bg-dark-700  { background-color:#d6cfba!important; }

        /* ── Light mode: borders ────────────────────────── */
        html:not(.dark) .border-white\/5           { border-color:rgba(0,0,0,0.05)!important; }
        html:not(.dark) .border-white\/10          { border-color:rgba(0,0,0,0.08)!important; }
        html:not(.dark) .border-white\/20          { border-color:rgba(0,0,0,0.12)!important; }
        html:not(.dark) .border-white\/\[0\.07\]   { border-color:rgba(0,0,0,0.07)!important; }
        html:not(.dark) .bg-white\/\[0\.04\]       { background-color:rgba(0,0,0,0.04)!important; }

        /* ── Light mode: cartes & sections ─────────────── */
        html:not(.dark) .bg-green-900    { background-color:#e9e5d9!important; }
        html:not(.dark) .bg-slate-800    { background-color:#e9e5d9!important; }
        html:not(.dark) .border-slate-800 { border-color:#d6cfba!important; }
        html:not(.dark) .hero-bg-fallback {
            background: #f8f4ec;
        }
        html:not(.dark) .featured-title-line {
            background: rgba(194, 94, 10, 0.2);
        }
        html:not(.dark) .featured-title-line.reverse {
            background: rgba(194, 94, 10, 0.2);
        }
        html:not(.dark) .featured-title-badge {
            border-color: rgba(194, 94, 10,0.35);
            background:
                linear-gradient(rgba(249, 188, 135, 0.5), rgba(249, 188, 135, 0.5)),
                rgba(255,255,255,0.92);
            box-shadow: 0 10px 20px rgba(194, 94, 10,0.12), inset 0 1px 0 rgba(255,255,255,0.95);
        }
        html:not(.dark) .featured-title-text { color:#a3450a; }
        html:not(.dark) .featured-title-dot { box-shadow: 0 0 8px rgba(194, 94, 10,0.45); }

        .event-price-badge {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(242, 121, 15,0.35);
            background: rgba(249, 188, 135, 0.13);
            box-shadow: 0 8px 20px rgba(0,0,0,0.22), inset 0 1px 0 rgba(255,255,255,0.12);
            animation: eventPricePulse 2.8s ease-in-out infinite;
        }
        .event-price-badge::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-120%);
            background: rgba(255, 255, 255, 0.12);
            animation: eventPriceShine 2.4s ease-in-out infinite;
            pointer-events: none;
        }
        .group:hover .event-price-badge {
            transform: translateY(-1px) scale(1.02);
            box-shadow: 0 12px 24px rgba(242, 121, 15,0.22), inset 0 1px 0 rgba(255,255,255,0.16);
        }
        @keyframes eventPricePulse {
            0%, 100% { filter: brightness(1); }
            50% { filter: brightness(1.08); }
        }
        @keyframes eventPriceShine {
            0%, 20% { transform: translateX(-120%); }
            60%, 100% { transform: translateX(130%); }
        }
        html:not(.dark) .event-price-badge {
            border-color: rgba(194, 94, 10,0.32);
            background: rgba(249, 188, 135, 0.57);
            color: #7a3c08;
        }

        .event-cta-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.6rem;
            margin-top: 0.85rem;
            padding: 0.38rem;
            border-radius: 0.9rem;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(10px);
            animation: ctaRowIn .55s cubic-bezier(.2,.8,.2,1) both;
        }
        .event-price-badge-modern {
            display: inline-flex;
            align-items: center;
            gap: 0.38rem;
            min-height: 2rem;
            padding: 0.38rem 0.7rem;
            border-radius: 0.7rem;
            border: 1px solid rgba(242, 121, 15,0.35);
            background: rgba(242, 121, 15,0.16);
            color: #f8d79a;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.02em;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 8px 18px rgba(0,0,0,0.25);
            animation: pricePulseSoft 2.7s ease-in-out infinite;
        }
        .event-price-badge-modern i {
            font-size: 10px;
            opacity: 0.95;
        }
        .event-ticket-btn-modern {
            position: relative;
            overflow: hidden;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 2rem;
            padding: 0.38rem 0.78rem;
            border-radius: 0.65rem;
            font-size: 11px;
            font-weight: 800;
            color: #1b1408;
            background: #f2790f;
            box-shadow: 0 10px 20px rgba(242, 121, 15,0.28);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .event-ticket-btn-modern:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 14px 24px rgba(242, 121, 15,0.35);
        }
        .group:hover .event-cta-row {
            border-color: rgba(242, 121, 15,0.35);
            box-shadow: 0 10px 22px rgba(0,0,0,0.24), inset 0 1px 0 rgba(255,255,255,0.08);
            transform: translateY(-1px);
        }
        .event-cta-ghost {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            min-height: 2rem;
            padding: 0.38rem 0.78rem;
            border-radius: 0.65rem;
            font-size: 11px;
            font-weight: 700;
            color: #f3f4f6;
            border: 1px solid rgba(255,255,255,0.14);
            background: rgba(233, 229, 217, 0.05);
            transition: background-color .2s ease, border-color .2s ease, transform .2s ease;
        }
        .event-cta-ghost:hover {
            transform: translateY(-1px);
            border-color: rgba(242, 121, 15,0.45);
            background: rgba(242, 121, 15,0.14);
        }
        @keyframes ctaRowIn {
            0% {
                opacity: 0;
                transform: translateY(8px) scale(0.985);
            }
            100% {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        @keyframes pricePulseSoft {
            0%, 100% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.16), 0 8px 18px rgba(0,0,0,0.25); }
            50% { box-shadow: inset 0 1px 0 rgba(255,255,255,0.22), 0 10px 22px rgba(242, 121, 15,0.22); }
        }
        html:not(.dark) .event-cta-row {
            border-color: rgba(0,0,0,0.08);
            background: rgba(252, 250, 246, 0.94);
        }
        html:not(.dark) .event-price-badge-modern {
            border-color: rgba(194, 94, 10,0.28);
            background: rgba(255,255,255,0.92);
            color: #7a3c08;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.95), 0 8px 16px rgba(194, 94, 10,0.14);
        }
        html:not(.dark) .event-cta-ghost {
            color: #1f2937;
            border-color: rgba(0,0,0,0.12);
            background: rgba(233, 229, 217, 0.85);
        }

        /* ── Categories bar (chips) — repris du modèle de /articles ──────── */
        .category-chip {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(203, 213, 225, 0.9);
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .category-chip::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(-130%);
            transition: transform .45s ease;
            pointer-events: none;
        }
        .category-chip::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            border-radius: 2px;
            background: #eb7d1c;
            transition: width .24s ease;
            box-shadow: 0 0 10px rgba(242, 121, 15,0.45);
        }
        .category-chip:hover {
            transform: translateY(-1px);
            color: #ffffff;
            border-color: rgba(242, 121, 15,0.3);
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 14px rgba(242, 121, 15,0.14);
        }
        .category-chip:hover::before {
            transform: translateX(130%);
        }
        .category-chip:hover::after {
            width: 72%;
        }
        .category-chip.is-active {
            border-color: rgba(242, 121, 15,0.52);
            background: #f2790f;
            color: #090705;
            box-shadow: 0 8px 20px rgba(242, 121, 15,0.3);
        }
        .category-chip.is-active::after {
            width: 76%;
            background: rgba(9,7,5,0.8);
            box-shadow: none;
        }
        .categories-strip {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 9999px;
            background: rgba(8,8,7,0.38);
            backdrop-filter: blur(10px) saturate(120%);
            padding: 0.45rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .categories-strip::-webkit-scrollbar {
            display: none;
        }
        .categories-strip {
            -webkit-mask-image: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65));
                    mask-image: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65));
        }
        .categories-nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            width: 34px; height: 34px;
            border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(28,25,21,0.9);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: 0 8px 18px rgba(0,0,0,0.4);
            cursor: pointer;
            font-size: 12px;
            transition: background .2s ease, transform .2s ease, opacity .2s ease;
        }
        .categories-nav-arrow:hover { background: #f2790f; transform: translateY(-50%) scale(1.06); }
        .categories-nav-arrow--left { left: -4px; }
        .categories-nav-arrow--right { right: -4px; }
        .categories-nav-arrow[hidden] { display: none; }
        html:not(.dark) .categories-strip {
            border-color: rgba(0,0,0,0.1);
            background: rgba(233, 229, 217, 0.92);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        html:not(.dark) .categories-nav-arrow {
            background: rgba(255,255,255,0.96);
            color: #1c1915;
            border-color: rgba(0,0,0,0.1);
            box-shadow: 0 8px 18px rgba(0,0,0,0.12);
        }
        html:not(.dark) .categories-nav-arrow:hover { background: #f2790f; color: #fff; }
        html:not(.dark) .category-chip {
            border-color: rgba(0,0,0,0.12);
            background: rgba(233, 229, 217, 0.9);
            color: #2d2a23;
        }
        html:not(.dark) .category-chip:hover {
            color: #1c1915;
            border-color: rgba(194, 94, 10,0.35);
            background: rgba(242, 121, 15,0.10);
            box-shadow: 0 0 14px rgba(194, 94, 10,0.12);
        }
        html:not(.dark) .category-chip.is-active {
            border-color: rgba(194, 94, 10,0.45);
            background: #f2790f;
            color: #1c1915;
            box-shadow: 0 8px 18px rgba(194, 94, 10,0.2);
        }

    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-2a1bc571.css') }}?v={{ @filemtime(public_path('css/tw-2a1bc571.css')) }}">
</head>
<body class="relative bg-dark-900 text-white antialiased font-sans">
    @include('partials.page-background')
@php
    $topContact = $siteBrand['contact'] ?? [];
    $topPhoneDisplay = !empty($topContact['phone_1']) ? $topContact['phone_1'] : '+225 27 22 48 36 90';
    $topPhoneHref = 'tel:'.preg_replace('/[^\d+]/', '', $topPhoneDisplay);
    $infoPages = $informationPages ?? collect();
    $infoGuide = $infoPages->firstWhere('slug', 'user-guide');
    $infoFaq = $infoPages->firstWhere('slug', 'faq');
    $infoLegal = $infoPages->firstWhere('slug', 'legal-notice');
@endphp

@include('partials.public-top-nav')

{{-- ══════════════════════════════════════════════════════════
     HERO
══════════════════════════════════════════════════════════ --}}
<section class="hero-bg-fallback hero-viewport relative flex flex-col overflow-hidden" id="hero">

    {{-- Decorative orbs --}}

    {{-- Content : carousel plein écran (slides responsives) --}}
    <div class="hero-viewport relative z-10 flex w-full flex-1 flex-col sm:min-h-[min(100svh,900px)]">
        @if($heroSlides->isNotEmpty())
            <div id="hero-bg-carousel" class="hero-viewport pointer-events-none absolute inset-0 z-0 h-full w-full overflow-hidden">
                @foreach($heroSlides as $idx => $slide)
                    @if($slide->isVideo())
                        @php
                            $vDesktop = trim((string) $slide->video_desktop_url);
                            $vTablet  = trim((string) ($slide->video_tablet_url  ?: $slide->video_desktop_url));
                            $vMobile  = trim((string) ($slide->video_mobile_url  ?: $slide->video_tablet_url ?: $slide->video_desktop_url));
                        @endphp
                        @if($vDesktop !== '')
                            <div class="hero-bg-layer absolute inset-0 bg-green-950 transition-opacity duration-700 ease-out {{ $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}"
                                 data-hero-bg-layer="{{ $idx }}"
                                 data-slide-type="video">
                                <video
                                    class="absolute inset-0 h-full w-full object-cover object-center"
                                    autoplay muted playsinline
                                    preload="{{ $idx === 0 ? 'auto' : 'none' }}"
                                    @if($idx === 0) src="{{ $vDesktop }}" @endif
                                    data-hero-video
                                    data-src-desktop="{{ $vDesktop }}"
                                    data-src-tablet="{{ $vTablet }}"
                                    data-src-mobile="{{ $vMobile }}"
                                ></video>
                            </div>
                        @endif
                    @else
                        @php
                            $desktop = trim((string) $slide->desktop_image_url);
                            $tablet  = trim((string) ($slide->tablet_image_url  ?: $slide->desktop_image_url));
                            $mobile  = trim((string) ($slide->mobile_image_url  ?: $slide->tablet_image_url ?: $slide->desktop_image_url));
                            $src     = $mobile !== '' ? $mobile : ($tablet !== '' ? $tablet : $desktop);
                        @endphp
                        @if($src !== '')
                            <div class="hero-bg-layer absolute inset-0 bg-green-950 transition-opacity duration-700 ease-out {{ $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}"
                                 data-hero-bg-layer="{{ $idx }}"
                                 data-slide-type="image">
                                <picture class="absolute inset-0 block h-full w-full">
                                    @if($desktop !== '')
                                        <source media="(min-width: 1024px)" srcset="{{ $desktop }}">
                                    @endif
                                    @if($tablet !== '')
                                        <source media="(min-width: 640px)" srcset="{{ $tablet }}">
                                    @endif
                                    <img
                                        src="{{ $src }}"
                                        alt=""
                                        @if($idx > 0) loading="lazy" @endif
                                        @if($idx === 0) fetchpriority="high" @endif
                                        decoding="async"
                                        class="h-full w-full object-cover object-center sm:object-[58%_center] lg:object-center"
                                    />
                                </picture>
                            </div>
                        @endif
                    @endif
                @endforeach

                @if($heroSlides->count() > 1)
                    {{-- Flèches latérales (desktop & tablette) --}}
                    <button type="button" id="hero-bg-prev"
                            class="hero-bg-arrow hero-bg-arrow-prev pointer-events-auto absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex items-center justify-center w-11 h-11 rounded-full bg-green-950/45 border border-white/20 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition shadow-lg shadow-green-950/40 backdrop-blur-sm"
                            aria-label="{{ __('Slide précédent') }}">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>
                    <button type="button" id="hero-bg-next"
                            class="hero-bg-arrow hero-bg-arrow-next pointer-events-auto absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex items-center justify-center w-11 h-11 rounded-full bg-green-950/45 border border-white/20 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition shadow-lg shadow-green-950/40 backdrop-blur-sm"
                            aria-label="{{ __('Slide suivant') }}">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>

                    {{-- Barre de contrôles (mobile + dots desktop) --}}
                    <div class="hero-bg-controls absolute inset-x-0 bottom-3 sm:bottom-4 z-30 flex items-center justify-center gap-2 sm:gap-3 pointer-events-none px-4">
                        <button type="button" id="hero-bg-prev-mobile"
                                class="pointer-events-auto sm:hidden w-9 h-9 rounded-full bg-green-950/55 border border-white/25 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center backdrop-blur-sm"
                                aria-label="{{ __('Slide précédent') }}">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <div class="flex items-center gap-2 pointer-events-auto rounded-full bg-green-950/35 border border-white/10 px-3 py-1.5 backdrop-blur-sm" id="hero-bg-dots" role="tablist" aria-label="{{ __('Choisir un slide') }}">
                            @foreach($heroSlides as $idx => $slide)
                                <button type="button"
                                        class="hero-bg-dot h-2 rounded-full transition-all {{ $idx === 0 ? 'w-6 bg-gold-400' : 'w-2 bg-white/35 hover:bg-white/60' }}"
                                        data-hero-bg-dot="{{ $idx }}"
                                        aria-label="Slide {{ $idx + 1 }}"
                                        aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"></button>
                            @endforeach
                        </div>
                        <button type="button" id="hero-bg-next-mobile"
                                class="pointer-events-auto sm:hidden w-9 h-9 rounded-full bg-green-950/55 border border-white/25 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center backdrop-blur-sm"
                                aria-label="{{ __('Slide suivant') }}">
                            <i class="fas fa-chevron-right text-xs"></i>
                        </button>
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- Categories bar --}}
    <div class="relative z-10 border-t border-white/8 bg-dark-900/60 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3">
            <div class="categories-nav relative">
                <button type="button" class="categories-nav-arrow categories-nav-arrow--left" data-dir="-1" aria-label="{{ __('Catégories précédentes') }}" hidden>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="categories-strip flex items-center gap-2.5 overflow-x-auto scrollbar-none">
                    <a href="{{ route('articles.index') }}"
                       class="category-chip {{ !request('categorie') && request()->routeIs('home') ? 'is-active' : '' }} shrink-0 px-5 py-2 rounded-full text-sm font-semibold">
                        {{ __('Tous') }}
                    </a>
                    @foreach($homeCategories as $cat)
                    <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
                       class="category-chip shrink-0 px-5 py-2 rounded-full text-sm font-semibold">
                        {{ $cat->localized('name') }}
                        @if($cat->articles_count > 0)
                        <span class="ml-1 opacity-70">({{ $cat->articles_count }})</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                <button type="button" class="categories-nav-arrow categories-nav-arrow--right" data-dir="1" aria-label="{{ __('Catégories suivantes') }}">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <a href="#articles"
       class="hero-scroll-indicator absolute bottom-28 md:bottom-36 right-6 sm:right-10 w-12 h-12 rounded-full border border-gold-400/30 bg-dark-800/60 backdrop-blur flex items-center justify-center text-gold-400 hover:bg-gold-500 hover:text-dark-900 hover:border-gold-500 transition-all duration-300 animate-bounce z-10">
        <i class="fas fa-chevron-down text-sm"></i>
    </a>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION: À LA UNE
══════════════════════════════════════════════════════════ --}}
<section id="articles" class="py-16 sm:py-20 bg-dark-900 relative overflow-hidden"
    @if($articlesImage && $articlesImage->isVisible())
        style="--articles-bg-image: url('{{ $articlesImage->image_url }}');"
    @endif
>
    <div class="section-watermark-bg"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">

        {{-- ── En-tête centré style presse ── --}}
        <div class="featured-title-wrap mb-10 sm:mb-12 reveal">
            <div class="featured-title-line"></div>
            <div class="featured-title-badge shrink-0">
                <span class="featured-title-dot"></span>
                <span class="featured-title-text">{{ __('À la une') }}</span>
                <span class="featured-title-dot"></span>
            </div>
            <div class="featured-title-line reverse"></div>
        </div>

        @if(($homeArticles ?? collect())->isNotEmpty())
        @php
            $mainArt  = $homeArticles->first();
            $listArts = $homeArticles->slice(1, 5)->values();
            // Pictogramme de rubrique (aussi utilisé quand l'article n'a pas d'image).
            $artIcon = fn ($art) => match (true) {
                ! $art->category => 'fa-newspaper',
                str_contains($art->category->slug, 'nature') => 'fa-leaf',
                str_contains($art->category->slug, 'gastro') => 'fa-utensils',
                str_contains($art->category->slug, 'destination') => 'fa-location-dot',
                str_contains($art->category->slug, 'portrait') => 'fa-user',
                str_contains($art->category->slug, 'art') => 'fa-shirt',
                str_contains($art->category->slug, 'culture') => 'fa-landmark',
                default => 'fa-newspaper',
            };
        @endphp

        <div class="grid grid-cols-1 lg:grid-cols-[1.55fr_1fr] gap-6 lg:gap-8 items-start">

            {{-- Article à la une --}}
            @if($mainArt)
            @php
                // Comme sur la page article : le compte « Super Admin » n'est jamais affiché publiquement,
                // et l'auteur n'est pas répété parmi les contributeurs.
                $isSuperAdmin = fn ($u) => strcasecmp(trim((string) ($u?->full_name ?? '')), 'Super Admin') === 0;
                $mainPeople = collect([$mainArt->author])->merge($mainArt->display_uploaders)
                    ->filter()->reject($isSuperAdmin)->unique('id')->values();
                $mainAuthor = $mainPeople->first();
                $mainContributors = $mainPeople->slice(1);
            @endphp
            <article class="ti-feat group reveal">
                <div class="ti-feat__media">
                    @if($mainArt->cover_url)
                        <img src="{{ $mainArt->cover_url }}" alt="{{ $mainArt->cover_alt ?: $mainArt->localized('title') }}" decoding="async">
                    @else
                        <span class="ti-feat__placeholder" aria-hidden="true"><i class="fas {{ $artIcon($mainArt) }}"></i></span>
                    @endif
                    <div class="ti-feat__top">
                        <span class="ti-feat__hot"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('Dernier paru') }}</span>
                        <div class="ti-feat__actions">
                            @include('partials.place-card-fav', ['providerId' => $mainArt->id, 'name' => $mainArt->localized('title'), 'favType' => 'article', 'favIcon' => 'bookmark', 'favClass' => 'ti-round'])
                            <button type="button" class="ti-round" data-place-share="{{ route('articles.show', $mainArt->slug_fr) }}" data-place-title="{{ $mainArt->localized('title') }}" aria-label="{{ __('Partager :name', ['name' => $mainArt->localized('title')]) }}" title="{{ __('Partager') }}"><i class="fas fa-share-nodes" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    @if($mainArt->reading_time)
                        <span class="ti-feat__time"><i class="far fa-clock" aria-hidden="true"></i>{{ trans_choice(':count min de lecture|:count min de lecture', $mainArt->reading_time) }}</span>
                    @endif
                </div>
                <div class="ti-feat__body">
                    <p class="ti-feat__meta">
                        @if($mainArt->category)
                            <span class="ti-feat__cat"><i class="fas {{ $artIcon($mainArt) }}" aria-hidden="true"></i>{{ $mainArt->category->localized('name') }}</span>
                        @endif
                        @if($mainArt->published_at)
                            <span><i class="far fa-calendar" aria-hidden="true"></i>{{ $mainArt->published_at->locale(app()->getLocale())->translatedFormat('j M Y') }}</span>
                        @endif
                        @if($mainArt->views_count)
                            <span><i class="far fa-eye" aria-hidden="true"></i>{{ trans_choice(':count lecture|:count lectures', $mainArt->views_count) }}</span>
                        @endif
                    </p>
                    <h2 class="ti-feat__title"><a href="{{ route('articles.show', $mainArt->slug_fr) }}" class="ti-feat__link">{{ $mainArt->localized('title') }}</a></h2>
                    @if($mainArt->excerpt_fr)
                        <p class="ti-feat__excerpt">{{ $mainArt->localized('excerpt') }}</p>
                    @endif
                </div>
                <div class="ti-feat__foot">
                    @if($mainAuthor)
                        <span class="ti-feat__author">
                            <span class="ti-feat__avatar" aria-hidden="true">{{ $mainAuthor->initials }}</span>
                            <span>{{ $mainAuthor->full_name }}
                                @if($mainContributors->isNotEmpty())<small>{{ __('avec') }} {{ $mainContributors->take(2)->pluck('first_name')->join(', ') }}</small>@endif
                            </span>
                        </span>
                    @else
                        <span></span>
                    @endif
                    <span class="ti-cta" aria-hidden="true">{{ __('Lire l\'article') }}<i class="fas fa-arrow-right"></i></span>
                </div>
            </article>
            @endif

            {{-- À lire aussi --}}
            <aside class="ti-readlist reveal" aria-labelledby="ti-readlist-title">
                <div class="ti-readlist__head">
                    <h3 id="ti-readlist-title"><i class="fas fa-book-open" aria-hidden="true"></i>{{ __('À lire aussi') }}</h3>
                </div>
                @forelse($listArts as $i => $art)
                    <a href="{{ route('articles.show', $art->slug_fr) }}" class="ti-readlist__item group">
                        <span class="ti-readlist__num" aria-hidden="true">{{ str_pad($i + 1, 2, '0', STR_PAD_LEFT) }}</span>
                        <span class="min-w-0">
                            @if($art->category)<span class="ti-readlist__cat">{{ $art->category->localized('name') }}</span>@endif
                            <span class="ti-readlist__title">{{ $art->localized('title') }}</span>
                            <span class="ti-readlist__meta">
                                @if($art->reading_time){{ $art->reading_time }} {{ __('min') }} · @endif{{ $art->published_at?->locale(app()->getLocale())->translatedFormat('j M') }}
                            </span>
                        </span>
                        <span class="ti-readlist__thumb">
                            @if($art->cover_url)
                                <img src="{{ $art->cover_url }}" alt="" loading="lazy" decoding="async">
                            @else
                                <i class="fas {{ $artIcon($art) }}" aria-hidden="true"></i>
                            @endif
                        </span>
                    </a>
                @empty
                    <p class="text-[#6b6458] text-sm py-4">{{ __('Aucun article supplémentaire.') }}</p>
                @endforelse
                <a href="{{ route('articles.index') }}" class="ti-outline-btn">
                    {{ __('Voir tous les articles') }} <i class="fas fa-arrow-right" aria-hidden="true"></i>
                </a>
            </aside>
        </div>
        @include('partials.place-card-script')

        @else
        {{-- Fallback : aucun article --}}
        <div class="text-center py-16 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
            <i class="fas fa-newspaper text-dark-600 text-5xl mb-4"></i>
            <p class="text-gray-500 font-elegant text-lg mb-4">{{ __('Aucun article publié pour le moment.') }}</p>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl btn-primary font-bold text-sm">
                <i class="fas fa-plus text-xs"></i> {{ __('Publier le premier article') }}
            </a>
        </div>
        @endif

    </div>
</section>

{{-- Recommandations personnalisées (module IA 1) : section masquée si l'IA est coupée --}}
@include('partials.ai-reco-strip', ['limit' => 4])

{{-- ══════════════════════════════════════════════════════════
     SECTION : ANNUAIRE PRESTATAIRES
══════════════════════════════════════════════════════════ --}}
<section id="annuaire" class="py-16 sm:py-24 bg-dark-800 relative overflow-clip"
    @if($annuaireImage && $annuaireImage->isVisible())
        style="--annuaire-bg-image: url('{{ $annuaireImage->image_url }}');"
    @endif
>
    <div class="section-watermark-bg"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">
            <aside class="lg:col-span-5 reveal self-start lg:sticky lg:top-28 rounded-2xl border border-[#e8e1d3] bg-white shadow-[0_1px_2px_rgba(29,26,22,.06)] overflow-hidden">

                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-[#fdf4ec] flex items-center justify-center">
                            <i class="fas fa-compass" style="color:#b9500a"></i>
                        </div>
                        <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant">{{ __('Annuaire des prestataires') }}</p>
                    </div>

                    <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4 leading-snug">
                        {{ __('Les meilleures adresses') }}<br>{{ __('de Côte d\'Ivoire') }}
                    </h2>
                    <p class="text-[#1c1915] font-elegant text-xl font-light leading-relaxed mb-7">
                        {{ __('Hôtels, restaurants, guides touristiques, artisans… Découvrez notre sélection premium d\'établissements vérifiés et notés par notre équipe.') }}
                    </p>

                    {{-- Recherche d'adresses / établissements --}}
                    <div class="relative mb-6" id="annuaireSearchWrap">
                        <form method="GET" action="{{ route('providers.index') }}" role="search">
                            <div class="relative">
                                <i class="fas fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gold-400/70 text-sm"></i>
                                <input type="text" name="q" id="annuaireSearchInput" autocomplete="off"
                                       placeholder="{{ __('Rechercher une adresse, un établissement, une ville…') }}"
                                       class="w-full bg-dark-700/70 border border-white/10 focus:border-gold-400/50 rounded-xl pl-11 pr-4 py-3.5 text-sm text-white placeholder:text-gray-500 outline-none transition">
                            </div>
                        </form>
                        <div id="annuaireSearchResults"
                             class="hidden absolute z-30 top-full left-0 right-0 mt-2 rounded-xl border border-white/10 bg-dark-800 shadow-2xl shadow-green-950/40 overflow-hidden max-h-80 overflow-y-auto"></div>
                    </div>

                    {{-- Secteurs — onglets : la grille de droite affiche sur place les prestataires du secteur choisi --}}
                    <nav class="flex flex-col gap-1.5 mb-7 border-t border-white/10 pt-5" id="annuaire-tabs" @if(($homeProviderCategories ?? collect())->isNotEmpty()) role="tablist" aria-label="{{ __('Secteurs de l\'annuaire') }}" @endif>
                        @php
                            $sidebarCatIcons = ['hotels' => 'fa-bed', 'restaurants' => 'fa-utensils', 'sites-touristiques' => 'fa-mountain-sun', 'agences-voyages' => 'fa-plane-departure', 'loisirs-culture' => 'fa-masks-theater', 'transports' => 'fa-van-shuttle'];
                        @endphp
                        @if(($homeProviderCategories ?? collect())->isNotEmpty())
                            <button type="button" role="tab" id="annuaire-tab-all" aria-selected="true" aria-controls="annuaire-panel-all" data-annuaire-tab="all"
                                    class="annuaire-tab is-active group flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gold-500/10 transition-all duration-300 text-left">
                                <span class="annuaire-tab-icon w-8 h-8 shrink-0 rounded-lg border border-white/10 bg-dark-700/70 flex items-center justify-center group-hover:border-gold-400/45 group-hover:bg-gold-500/15 transition-all duration-300">
                                    <i class="fas fa-border-all text-gold-400/80 text-xs group-hover:text-gold-300 transition"></i>
                                </span>
                                <span class="annuaire-tab-label flex-1 text-gray-300 text-sm font-semibold tracking-wide group-hover:text-gold-300 transition-colors duration-300">{{ __('Tous') }}</span>
                                <i class="annuaire-tab-chevron fas fa-chevron-right text-[10px] text-gray-500 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-gold-400 transition-all duration-300"></i>
                            </button>
                            @foreach($homeProviderCategories as $pc)
                            <button type="button" role="tab" id="annuaire-tab-{{ $pc->slug }}" aria-selected="false" aria-controls="annuaire-panel-{{ $pc->slug }}" data-annuaire-tab="{{ $pc->slug }}"
                                    class="annuaire-tab group flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gold-500/10 transition-all duration-300 text-left">
                                <span class="annuaire-tab-icon w-8 h-8 shrink-0 rounded-lg border border-white/10 bg-dark-700/70 flex items-center justify-center group-hover:border-gold-400/45 group-hover:bg-gold-500/15 transition-all duration-300">
                                    <i class="fas {{ $sidebarCatIcons[$pc->slug] ?? 'fa-store' }} text-gold-400/80 text-xs group-hover:text-gold-300 transition"></i>
                                </span>
                                <span class="annuaire-tab-label flex-1 text-gray-300 text-sm font-semibold tracking-wide group-hover:text-gold-300 transition-colors duration-300">{{ $pc->localized('name') }}</span>
                                <i class="annuaire-tab-chevron fas fa-chevron-right text-[10px] text-gray-500 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-gold-400 transition-all duration-300"></i>
                            </button>
                            @endforeach
                        @else
                            @foreach(['Hôtellerie' => 'fa-bed', 'Gastronomie' => 'fa-utensils', 'Guides' => 'fa-mountain-sun', 'Artisanat' => 'fa-plane-departure', 'Loisirs' => 'fa-masks-theater', 'Bien-être' => 'fa-van-shuttle'] as $c => $icon)
                            <a href="{{ route('providers.index') }}"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gold-500/10 transition-all duration-300">
                                <span class="w-8 h-8 shrink-0 rounded-lg border border-white/10 bg-dark-700/70 flex items-center justify-center group-hover:border-gold-400/45 group-hover:bg-gold-500/15 transition-all duration-300">
                                    <i class="fas {{ $icon }} text-gold-400/80 text-xs group-hover:text-gold-300 transition"></i>
                                </span>
                                <span class="flex-1 text-gray-300 text-sm font-semibold tracking-wide group-hover:text-gold-300 transition-colors duration-300">{{ $c }}</span>
                                <i class="fas fa-chevron-right text-[10px] text-gray-500 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-gold-400 transition-all duration-300"></i>
                            </a>
                            @endforeach
                        @endif
                    </nav>

                    <a href="{{ route('providers.index') }}"
                       class="flex items-center justify-center gap-2.5 w-full px-6 py-3.5 rounded-xl btn-primary font-bold text-sm">
                        <i class="fas fa-compass"></i> {{ __('Explorer l\'annuaire') }}
                    </a>
                </div>
            </aside>

            <div class="lg:col-span-7 reveal scroll-mt-24" id="annuaire-panels">
                {{-- Panneau « Tous » : l'établissement mis en avant de chaque secteur (mêmes données que les pages /residences-hotels, /restaurants…) --}}
                <div id="annuaire-panel-all" role="tabpanel" aria-labelledby="annuaire-tab-all" data-annuaire-panel="all">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if(($homeSectorFeatured ?? collect())->isNotEmpty())
                            @foreach($homeSectorFeatured as $it)
                                @include('partials.home-sector-card', ['it' => $it, 'showSector' => true])
                            @endforeach
                        @else
                            @forelse(($homeProviders ?? collect()) as $p)
                                @include('partials.home-provider-card', ['p' => $p])
                            @empty
                            <div class="sm:col-span-2 text-center text-gray-500 text-sm py-10 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
                                {{ __('Aucun prestataire actif pour le moment.') }}
                            </div>
                            @endforelse
                        @endif
                    </div>
                    <a href="{{ route('establishments.index') }}"
                       class="mt-5 inline-flex items-center gap-2 text-[#b9500a] hover:text-[#9a4308] text-sm font-semibold transition-colors">
                        {{ __('Voir tous nos établissements') }} <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>

                {{-- Un panneau par secteur (masqués tant que l'onglet n'est pas choisi) --}}
                @foreach(($homeProviderCategories ?? collect()) as $pc)
                <div id="annuaire-panel-{{ $pc->slug }}" role="tabpanel" aria-labelledby="annuaire-tab-{{ $pc->slug }}" data-annuaire-panel="{{ $pc->slug }}" hidden>
                    @php $showcase = $homeSectorShowcase[$pc->slug] ?? null; @endphp
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        @if($showcase)
                            @forelse($showcase['items'] as $it)
                                @include('partials.home-sector-card', ['it' => $it])
                            @empty
                            <div class="sm:col-span-2 text-center text-gray-500 text-sm py-10 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
                                {{ __('Aucun établissement dans «') }} {{ $pc->localized('name') }} {{ __('» pour le moment.') }}
                            </div>
                            @endforelse
                        @else
                            @forelse(($homeProvidersBySector[$pc->slug] ?? collect()) as $p)
                                @include('partials.home-provider-card', ['p' => $p])
                            @empty
                            <div class="sm:col-span-2 text-center text-gray-500 text-sm py-10 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
                                {{ __('Aucun prestataire actif dans «') }} {{ $pc->localized('name') }} {{ __('» pour le moment.') }}
                            </div>
                            @endforelse
                        @endif
                    </div>
                    <a href="{{ $showcase['index_url'] ?? route('providers.index', ['categorie' => $pc->slug]) }}"
                       class="mt-4 inline-flex items-center gap-2 text-gold-400 hover:text-gold-300 text-sm font-semibold transition-colors">
                        {{ __('Voir tout :') }} {{ $pc->localized('name') }} <i class="fas fa-arrow-right text-xs"></i>
                    </a>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const tabs = document.querySelectorAll('[data-annuaire-tab]');
    const panels = document.querySelectorAll('[data-annuaire-panel]');
    const wrap = document.getElementById('annuaire-panels');
    if (!tabs.length || !panels.length) return;

    function select(key, scroll) {
        tabs.forEach(function (t) {
            const on = t.getAttribute('data-annuaire-tab') === key;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-selected', on ? 'true' : 'false');
        });
        panels.forEach(function (p) {
            p.hidden = p.getAttribute('data-annuaire-panel') !== key;
        });
        // Sur mobile / tablette la grille est sous le menu : on l'amène à l'écran.
        if (scroll && wrap && window.innerWidth < 1024) {
            wrap.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    tabs.forEach(function (t) {
        t.addEventListener('click', function () { select(t.getAttribute('data-annuaire-tab'), true); });
    });
})();
</script>

<script>
(function () {
    const input = document.getElementById('annuaireSearchInput');
    const results = document.getElementById('annuaireSearchResults');
    if (!input || !results) return;

    const suggestionsUrl = @json(route('search.suggestions'));
    let debounceTimer = null;
    let controller = null;

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, (c) => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
        }[c]));
    }

    function renderResults(items) {
        if (!items.length) {
            results.innerHTML = '<p class="px-4 py-3.5 text-gray-500 text-sm">Aucun résultat.</p>';
            results.classList.remove('hidden');
            return;
        }
        results.innerHTML = items.map((item) => `
            <a href="${item.url}" class="flex items-center gap-3 px-4 py-3 hover:bg-white/5 transition border-b border-white/5 last:border-0">
                <span class="w-8 h-8 shrink-0 rounded-lg bg-gold-500/10 border border-gold-500/20 flex items-center justify-center">
                    <i class="fas fa-location-dot text-gold-400/80 text-xs"></i>
                </span>
                <span class="min-w-0">
                    <span class="block text-white text-sm font-semibold truncate">${escapeHtml(item.name)}</span>
                    <span class="block text-gray-500 text-xs truncate">${escapeHtml([item.category, item.city].filter(Boolean).join(' · ') || {{ \Illuminate\Support\Js::from(__('Établissement')) }})}</span>
                </span>
            </a>
        `).join('');
        results.classList.remove('hidden');
    }

    function search(q) {
        if (controller) controller.abort();
        controller = new AbortController();

        fetch(`${suggestionsUrl}?q=${encodeURIComponent(q)}`, { signal: controller.signal, headers: { 'Accept': 'application/json' } })
            .then((r) => r.json())
            .then((data) => renderResults(data.results || []))
            .catch((e) => { if (e.name !== 'AbortError') results.classList.add('hidden'); });
    }

    input.addEventListener('input', () => {
        const q = input.value.trim();
        clearTimeout(debounceTimer);
        if (q.length < 2) {
            results.classList.add('hidden');
            return;
        }
        debounceTimer = setTimeout(() => search(q), 250);
    });

    input.addEventListener('focus', () => {
        if (input.value.trim().length >= 2 && results.innerHTML) results.classList.remove('hidden');
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('#annuaireSearchWrap')) results.classList.add('hidden');
    });
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') results.classList.add('hidden');
    });
})();
</script>

{{-- ══════════════════════════════════════════════════════════
     BANNER : DÉCOUVERTES
══════════════════════════════════════════════════════════ --}}
{{-- [COMMENTÉ] section #decouvertes --}}
{{-- <section id="decouvertes" class="py-16 sm:py-24 bg-dark-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-color: rgba(242, 121, 15, 0.5); background-size: 20px 20px;"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16 reveal">
            <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Explorer par thème</p>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4">Nos rubriques</h2>
            <p class="text-[#1c1915] font-elegant text-xl font-light">Plongez dans la richesse et la diversité de la Côte d'Ivoire à travers nos sélections thématiques.</p>
        </div>

        @php
            $defaultCatIcons = ['fa-landmark','fa-palette','fa-leaf','fa-utensils','fa-map-location-dot','fa-gem','fa-camera','fa-music','fa-heart','fa-star'];
            $defaultCatColors = [
                'from-orange-900/40 to-orange-800/10 border-orange-700/20',
                'from-rose-900/30 to-rose-800/10 border-rose-700/20',
                'from-green-900/40 to-green-800/10 border-green-700/20',
                'from-orange-900/30 to-orange-800/10 border-orange-700/20',
                'from-green-900/30 to-green-800/10 border-green-700/20',
                'from-green-900/30 to-green-800/10 border-green-700/20',
                'from-green-900/30 to-green-800/10 border-green-700/20',
                'from-green-900/30 to-green-800/10 border-green-700/20',
            ];
        @endphp

        @if(($homeCategories ?? collect())->isNotEmpty())
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach($homeCategories->take(6) as $i => $cat)
            @php
                $catIcon  = $cat->icon ?: ($defaultCatIcons[$i % count($defaultCatIcons)]);
                $catColor = $defaultCatColors[$i % count($defaultCatColors)];
            @endphp
            <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
               class="group bg-linear-to-b {{ $catColor }} border rounded-2xl p-5 text-center hover:scale-105 transition-all duration-300 reveal">
                <div class="w-12 h-12 rounded-xl bg-white/5 group-hover:bg-gold-500/15 flex items-center justify-center mx-auto mb-3 transition">
                    <i class="fas {{ $catIcon }} text-gold-400 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <p class="text-white text-sm font-semibold font-serif">{{ $cat->localized('name') }}</p>
                <p class="text-gray-500 text-xs mt-1">
                    {{ $cat->articles_count > 0 ? $cat->articles_count.' article'.($cat->articles_count > 1 ? 's' : '') : __('Bientôt') }}
                </p>
            </a>
            @endforeach
        </div>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach([__('Patrimoine'),__('Art & Culture'),__('Nature'),__('Gastronomie'),__('Destinations'),__('Art de vivre')] as $i => $label)
            @php $colors = ['from-orange-900/40 to-orange-800/10 border-orange-700/20','from-rose-900/30 to-rose-800/10 border-rose-700/20','from-green-900/40 to-green-800/10 border-green-700/20','from-orange-900/30 to-orange-800/10 border-orange-700/20','from-green-900/30 to-green-800/10 border-green-700/20','from-green-900/30 to-green-800/10 border-green-700/20']; $icons = ['fa-landmark','fa-palette','fa-leaf','fa-utensils','fa-map-location-dot','fa-gem']; @endphp
            <a href="{{ route('articles.index') }}" class="group bg-linear-to-b {{ $colors[$i] }} border rounded-2xl p-5 text-center hover:scale-105 transition-all duration-300 reveal">
                <div class="w-12 h-12 rounded-xl bg-white/5 group-hover:bg-gold-500/15 flex items-center justify-center mx-auto mb-3 transition">
                    <i class="fas {{ $icons[$i] }} text-gold-400 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <p class="text-white text-sm font-semibold font-serif">{{ $label }}</p>
                <p class="text-gray-500 text-xs mt-1">Bientôt</p>
            </a>
            @endforeach
        </div>
        @endif
    </div>
</section> --}}

{{-- ══════════════════════════════════════════════════════════
     SECTION : RÉGIONS TOURISTIQUES
══════════════════════════════════════════════════════════ --}}
@if(($homeTouristCities ?? collect())->isNotEmpty())
<style>
/* ════════════════════════════════════════════════════════════
   RÉGIONS TOURISTIQUES — Design asymétrique cinématique
   ════════════════════════════════════════════════════════════ */

/* Entrée hero (gauche) */
@@keyframes rt2-hero-in {
    from { opacity:0; transform: translateX(-50px) scale(.97); }
    to   { opacity:1; transform: translateX(0)     scale(1);   }
}
/* Entrée cards droite (stagger) */
@@keyframes rt2-side-in {
    from { opacity:0; transform: translateX(40px); }
    to   { opacity:1; transform: translateX(0);    }
}
/* Entrée strip bas */
@@keyframes rt2-strip-in {
    from { opacity:0; transform: translateY(30px); }
    to   { opacity:1; transform: translateY(0);    }
}
/* Shimmer sweep */
@@keyframes rt2-sweep {
    from { left: -60%; }
    to   { left: 130%; }
}
/* Pulsation dorée badge */
@@keyframes rt2-pulse {
    0%,100% { box-shadow: 0 0 0 0 rgba(242, 121, 15,.55); }
    60%     { box-shadow: 0 0 0 8px rgba(242, 121, 15,0); }
}
/* Ligne dorée animée (hero) */
@@keyframes rt2-line {
    from { width: 0; opacity:0; }
    to   { width: 3rem; opacity:1; }
}
/* Scroll indicator bounce */
@@keyframes rt2-bounce-x {
    0%,100% { transform: translateX(0); }
    50%     { transform: translateX(5px); }
}

/* ── Images de fond configurables (back-office) des 6 sections d'accueil ──
   Rôle visuel commun aux 6 : simple filigrane texturé derrière le contenu,
   jamais un visuel « héros » à cadrer précisément — d'où le voile crème à
   55% qui les recouvre systématiquement, quel que soit l'écran.
   `background-size: cover` : remplit toute la section sans aucune bande
   crème résiduelle en haut/bas (le souci d'espace entre sections observé
   en production avec `contain`). Contrepartie assumée (demande explicite) :
   sur les sections plus hautes que larges, `cover` recadre les bords
   gauche/droite de l'image — les montages qui ont leur sujet sur les bords
   (ex. collage régions touristiques) perdent alors ce sujet, ne laissant
   que le filigrane central quasi invisible. */
.section-watermark-bg {
    position: absolute;
    inset: 0;
    background-repeat: no-repeat;
    /* Largeur fixée à 1920px (comme la largeur native des images) sur toutes
       les sections : avec `cover` seul, une section dont la hauteur diffère
       lègèrement de l'image (ex. Articles, 864px de haut réel vs ~883px de
       section) force un zoom horizontal légèrement différent des autres
       sections, ce qui décale les motifs de bordure (zigzags, rayures) d'une
       section à l'autre. Fixer la largeur à 1920px garde le même recadrage
       partout ; seule la hauteur continue de s'adapter à la section. */
    background-size: max(100%, 1920px) 100%, max(100%, 1920px) 100% !important;
    background-position: center center;
    pointer-events: none;
}
#regions-touristiques .section-watermark-bg {
    background-image:
        linear-gradient(rgba(250, 248, 243, 0.86), rgba(250, 248, 243, 0.86)), var(--regions-bg-image, none);
}
#annuaire .section-watermark-bg {
    background-image:
        linear-gradient(rgba(250, 248, 243, 0.86), rgba(250, 248, 243, 0.86)), var(--annuaire-bg-image, none);
}
#cultures-ivoiriennes .section-watermark-bg {
    background-image:
        linear-gradient(rgba(250, 248, 243, 0.86), rgba(250, 248, 243, 0.86)), var(--cultures-bg-image, none);
}
/* Titre des cartes ethnies : posé sur un dégradé réellement sombre
   (from-green-950/85), non affecté par le pont de thème clair — couleur
   fixée en dur (text-white y était réécrit en noir par le pont, invisible). */
#cultures-ivoiriennes .cti-title { color: #ffffff !important; }
#cultures-ivoiriennes a.group:hover .cti-title { color: #fde3b8 !important; }
#evenements .section-watermark-bg {
    background-image:
        linear-gradient(rgba(250, 248, 243, 0.86), rgba(250, 248, 243, 0.86)), var(--evenements-bg-image, none);
}
#partenaires .section-watermark-bg {
    background-image:
        linear-gradient(rgba(250, 248, 243, 0.86), rgba(250, 248, 243, 0.86)), var(--partenaires-bg-image, none);
}
#articles .section-watermark-bg {
    background-image:
        linear-gradient(rgba(250, 248, 243, 0.86), rgba(250, 248, 243, 0.86)), var(--articles-bg-image, none);
}

/* ── Régions touristiques ───────────────────────────────
   Contenu toujours visible (pas d'animation d'entrée qui le masque), aplat
   vert forêt sous chaque photo : une image absente ou cassée reste lisible.
   Les règles inline min-height des cartes restent la référence de hauteur. */
.rt2-hero,
.rt2-side,
.rt2-strip-card {
    position: relative;
    overflow: hidden;
    background: #173f2a;
    border: 1px solid rgba(29,26,22,.08);
    transition: box-shadow .2s ease, border-color .2s ease;
}
.rt2-hero {
    border-radius: 16px;
    min-height: 480px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
}
.rt2-side {
    border-radius: 14px;
    display: flex;
    align-items: flex-end;
    min-height: 148px;
    flex: 1;
}
.rt2-hero:hover,
.rt2-side:hover,
.rt2-strip-card:hover {
    box-shadow: 0 12px 28px rgba(29,26,22,.16);
}
.rt2-img {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .6s ease;
}
.rt2-hero:hover .rt2-img,
.rt2-side:hover .rt2-img,
.rt2-strip-card:hover .rt2-img { transform: scale(1.03); }
/* Filet sous le titre de la destination vedette. */
.rt2-line {
    display: block;
    width: 40px;
    height: 2px;
    margin-bottom: .75rem;
    background: #f2790f;
}

/* ── Bandeau « Autres destinations » (défilement horizontal) ── */
.rt2-strip-wrap {
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.rt2-strip-wrap::-webkit-scrollbar { display: none; }
.rt2-strip-card {
    scroll-snap-align: start;
    flex-shrink: 0;
    width: 220px;
    height: 160px;
    border-radius: 12px;
}
/* Sur mobile, les hauteurs fixes pensées pour un écran large (style inline
   min-height:480px / 148px) sont réduites ; !important pour passer devant l'inline. */
@media (max-width: 639px) {
    .rt2-hero { min-height: 340px !important; }
    .rt2-side { min-height: 120px !important; }
}

.rt2-nav-btn {
    width: 38px; height: 38px;
    border-radius: 50%;
    background: #ffffff;
    border: 1px solid #d8cdb8;
    display: flex; align-items: center; justify-content: center;
    color: #1d1a16;
    cursor: pointer;
    flex-shrink: 0;
    transition: border-color .15s ease, color .15s ease;
}
.rt2-nav-btn:hover { border-color: #b9500a; color: #b9500a; }

.rt2-progress {
    height: 2px;
    background: #e8e1d3;
    border-radius: 999px;
    overflow: hidden;
    margin-top: 12px;
}
.rt2-progress-bar {
    height: 100%;
    background: #b9500a;
    border-radius: 999px;
    transition: width .25s ease;
    width: 0%;
}

/* Pastille « Destination vedette » / « Vedette » sur photo. */
.rt2-badge {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 4px 10px;
    border-radius: 999px;
    background: #f6ecd5;
    color: #8a6a1f;
    font-size: 11px; font-weight: 600;
}
.rt2-badge i { font-size: 9px; }

/* ── Pastilles « Explorer par catégorie » ── */
.rt2-pill {
    --pc: 163,69,10;
    display: inline-flex; align-items: center; gap: 9px;
    flex-shrink: 0;
    padding: 6px 16px 6px 6px;
    border-radius: 999px;
    border: 1px solid #e8e1d3;
    background: #ffffff;
    font-size: 14px; font-weight: 500;
    color: #1d1a16;
    transition: border-color .15s ease, color .15s ease;
    text-decoration: none;
    white-space: nowrap;
}
/* Sur mobile, seules les 4 premières pilules sont visibles au départ ; le
   bouton « Voir plus » déplie le reste. À partir de sm:, toutes sont visibles. */
.rt2-pill-extra { display: none; }
#rt2-pills-row.is-expanded .rt2-pill-extra { display: inline-flex; }
@media (min-width: 640px) {
    .rt2-pill-extra { display: inline-flex; }
}
/* Grille 2 colonnes sur mobile (grid-cols-2 sur #rt2-pills-row) : le texte
   passe à la ligne au lieu de déborder. */
@media (max-width: 639px) {
    #rt2-pills-row { align-items: stretch; }
    #rt2-pills-row .rt2-pill {
        white-space: normal; width: 100%; text-align: left;
        min-height: 52px; box-sizing: border-box;
    }
}
.rt2-pills-toggle {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 13px; font-weight: 600;
    color: #b9500a;
    background: none; border: none; cursor: pointer;
    padding: 6px 4px;
}
.rt2-pills-toggle:hover { color: #9a4308; }
.rt2-pills-toggle i { transition: transform .2s ease; }
.rt2-pills-toggle.is-expanded i { transform: rotate(180deg); }
/* Même logique « Voir plus » pour les pilules de domaines culturels. */
.domain-pill-extra { display: none; }
#domain-pills-row.is-expanded .domain-pill-extra { display: inline-flex; }
@media (min-width: 640px) {
    .domain-pill-extra { display: inline-flex; }
}
@media (max-width: 639px) {
    #domain-pills-row { align-items: stretch; }
    #domain-pills-row .domain-pill {
        white-space: normal; width: 100%; text-align: left;
        min-height: 52px; box-sizing: border-box;
    }
}
/* Mobile : « Explorer par catégorie » et « Explorer par domaine » deviennent une rangée défilante au doigt (une seule ligne,
   toutes les pastilles accessibles, aucun texte coupé sur 3 lignes) ; les boutons « Voir plus » ne sont alors plus utiles. */
@media (max-width: 639px) {
    #rt2-pills-row, #domain-pills-row {
        display: flex !important; flex-wrap: nowrap !important; justify-content: flex-start !important; align-items: center;
        gap: .6rem; overflow-x: auto; overscroll-behavior-x: contain; -webkit-overflow-scrolling: touch; scrollbar-width: none;
        scroll-snap-type: x proximity; margin-inline: calc(50% - 50vw); padding: .3rem 1.5rem .8rem;
    }
    #rt2-pills-row::-webkit-scrollbar, #domain-pills-row::-webkit-scrollbar { display: none; }
    #rt2-pills-row .rt2-pill, #domain-pills-row .domain-pill {
        flex: none; width: auto; min-height: 0; white-space: nowrap; scroll-snap-align: start; padding-top: .55rem; padding-bottom: .55rem;
        background: #ffffff !important; border: 1px solid #e8e1d3 !important; box-shadow: 0 1px 2px rgba(31, 27, 22, .05);
    }
    #rt2-pills-row .rt2-pill-extra, #domain-pills-row .domain-pill-extra { display: inline-flex !important; }
    div:has(> #rt2-pills-toggle), div:has(> #domain-pills-toggle) { display: none !important; }
}
.rt2-pill-icon {
    width: 26px; height: 26px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: rgba(var(--pc),.12);
    color: rgb(var(--pc));
    font-size: 11px;
}
.rt2-pill:hover { border-color: rgba(var(--pc),.55); color: #1d1a16; }

/* Couleurs du logo en alternance (coucher de soleil, soleil, palmier, lagune, anneau doré, mer, brun du texte)
   sur les pastilles « Explorer par catégorie » et « Explorer par domaine ». */
#rt2-pills-row > :nth-child(7n+1), #domain-pills-row > :nth-child(7n+1) { --pc: 217,101,15 !important; }
#rt2-pills-row > :nth-child(7n+2), #domain-pills-row > :nth-child(7n+2) { --pc: 74,138,42 !important; }
#rt2-pills-row > :nth-child(7n+3), #domain-pills-row > :nth-child(7n+3) { --pc: 31,128,121 !important; }
#rt2-pills-row > :nth-child(7n+4), #domain-pills-row > :nth-child(7n+4) { --pc: 201,138,12 !important; }
#rt2-pills-row > :nth-child(7n+5), #domain-pills-row > :nth-child(7n+5) { --pc: 44,110,143 !important; }
#rt2-pills-row > :nth-child(7n+6), #domain-pills-row > :nth-child(7n+6) { --pc: 176,134,28 !important; }
#rt2-pills-row > :nth-child(7n+7), #domain-pills-row > :nth-child(7n+7) { --pc: 122,74,42 !important; }
#rt2-pills-row .rt2-pill-icon { width: 28px; height: 28px; background: rgba(var(--pc),.14); border: 1px solid rgba(var(--pc),.25); color: rgb(var(--pc)); font-size: 12px; }
#rt2-pills-row .rt2-pill:hover { background: rgba(var(--pc),.07); }
#domain-pills-row .domain-pill { padding: 6px 16px 6px 6px !important; gap: 9px !important; background: #fff !important; border: 1px solid #e8e1d3 !important; }
#domain-pills-row .domain-pill > i:first-child {
    flex: none; width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center;
    border-radius: 999px; background: rgba(var(--pc),.14); border: 1px solid rgba(var(--pc),.25);
    color: rgb(var(--pc)) !important; font-size: 12px !important;
}
#domain-pills-row .domain-pill:hover { border-color: rgba(var(--pc),.55) !important; background: rgba(var(--pc),.07) !important; }
#domain-pills-row .domain-pill:hover span { color: #1d1a16 !important; }

/* Voile sombre commun sous le texte des photos. */
.rt2-overlay-b {
    position: absolute; inset: 0;
    background: rgba(10, 16, 12, 0.39);
    z-index: 2;
}

/* Content z-index */
.rt2-content { position: relative; z-index: 3; }

/* Texte superposé sur photo : couleurs fixées en CSS pur (et non via classes
   Tailwind text-white, text-gray-x ou text-gold-x), que le pont de thème
   clair réécrit en teintes sombres pour le reste de la page — invisibles
   ici sur fond de photo assombri par .rt2-overlay-b. */
.rt2-content .rt2-title { color: #ffffff !important; }
.rt2-content .rt2-district { color: rgba(253, 190, 123, 0.85) !important; }
.rt2-content .rt2-meta { color: #d1d5db !important; }
.rt2-content .rt2-meta-light { color: #e2e8f0 !important; }
.rt2-content .rt2-meta-icon { color: rgba(242, 121, 15, 0.6) !important; }
.rt2-content .rt2-meta-icon-soft { color: rgba(253, 190, 123, 0.9) !important; }
.rt2-content .rt2-cta { color: #fa9a3c !important; }
.rt2-content .rt2-arrow-btn { color: #fa9a3c !important; }
</style>

<section id="regions-touristiques" class="py-16 sm:py-24 bg-dark-900 relative overflow-hidden"
    @if($regionsImage && $regionsImage->isVisible())
        style="--regions-bg-image: url('{{ $regionsImage->image_url }}');"
    @endif
>
    <div class="section-watermark-bg"></div>


    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative" id="rt2-section">

        {{-- ── En-tête ─────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-10 sm:mb-14 reveal">
            <div>
                <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">{{ __('Voyage en Côte d\'Ivoire') }}</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">{{ __('Régions Touristiques') }}</h2>
                <p class="text-[#1c1915] font-elegant text-xl font-light mt-4 max-w-lg">
                    {{ __('Plages, parcs nationaux, monuments historiques… Explorez les villes et leurs merveilles.') }}
                </p>
            </div>
            <a href="{{ route('tourist.cities') }}"
               class="shrink-0 self-start sm:self-auto inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#d8cdb8] bg-white text-sm font-semibold text-[#1d1a16] hover:border-[#b9500a] hover:text-[#b9500a] transition-colors">
                {{ __('Explorer toutes les villes') }} <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        @php
            $rtMain  = $homeTouristCities->take(4);   /* Hero + 3 side */
            $rtStrip = $homeTouristCities->skip(4);   /* Strip bas */
            $rtHero  = $rtMain->first();
            $rtSides = $rtMain->skip(1);
        @endphp

        {{-- ── Destinations : la vedette en pleine largeur (cadre à la forme de son image, infos dessous),
             puis 3 cartes 16:9 (format des visuels) : aucune image rognée ni texte intégré coupé. ── --}}
        @php
            $destSites = fn ($city, bool $long = false) => $city->sites_count > 0
                ? trans_choice($long ? ':count site à explorer|:count sites à explorer' : ':count site|:count sites', $city->sites_count)
                : __('Sites à venir');
        @endphp
        <div class="rg reveal">
            @if($rtHero)
            <a href="{{ route('tourist.city', $rtHero->slug) }}" class="rg-hero group">
                <span class="rg-media rg-hero__media" data-rg-fit>
                    <span class="rg-media__fallback" aria-hidden="true">{{ mb_substr($rtHero->name, 0, 1) }}</span>
                    @if($rtHero->thumbnail || $rtHero->cover_image)
                        <img src="{{ $rtHero->thumbnail ?: $rtHero->cover_image }}" alt="{{ $rtHero->name }}" loading="lazy" decoding="async" onerror="this.remove()">
                    @endif
                    @if($rtHero->is_featured)
                        <span class="rg-badge is-star"><i class="fas fa-star" aria-hidden="true"></i>{{ __('Destination vedette') }}</span>
                    @endif
                </span>
                <span class="rg-hero__body">
                    <span class="min-w-0">
                        <span class="rg-hero__name">{{ $rtHero->name }}</span>
                        @if($rtHero->district || $rtHero->region_administrative)
                            <span class="rg-meta"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ collect([$rtHero->district, $rtHero->region_administrative])->filter()->join(' · ') }}</span>
                        @endif
                        @if($rtHero->description)
                            <span class="rg-hero__desc">{{ \Illuminate\Support\Str::limit(strip_tags($rtHero->description), 170) }}</span>
                        @endif
                        <span class="rg-chip"><i class="far fa-compass" aria-hidden="true"></i>{{ $destSites($rtHero, true) }}</span>
                    </span>
                    <span class="rg-go">{{ __('Découvrir :city', ['city' => $rtHero->name]) }} <i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                </span>
            </a>
            @endif

            @if($rtSides->isNotEmpty())
            <div class="rg-row">
                @foreach($rtSides as $city)
                <a href="{{ route('tourist.city', $city->slug) }}" class="rg-card group">
                    <span class="rg-media rg-card__media">
                        <span class="rg-media__fallback" aria-hidden="true">{{ mb_substr($city->name, 0, 1) }}</span>
                        @if($city->thumbnail || $city->cover_image)
                            <img src="{{ $city->thumbnail ?: $city->cover_image }}" alt="{{ $city->name }}" loading="lazy" decoding="async" onerror="this.remove()">
                        @endif
                        @if($city->is_featured)
                            <span class="rg-badge"><i class="fas fa-star" aria-hidden="true"></i>{{ __('Vedette') }}</span>
                        @endif
                    </span>
                    <span class="rg-card__body">
                        <span class="min-w-0">
                            <span class="rg-card__name">{{ $city->name }}</span>
                            <span class="rg-meta"><i class="fas fa-location-dot" aria-hidden="true"></i>{{ collect([$city->region_administrative ?: $city->district, $destSites($city)])->filter()->join(' · ') }}</span>
                        </span>
                        <span class="rg-arrow" aria-hidden="true"><i class="fas fa-arrow-right"></i></span>
                    </span>
                </a>
                @endforeach
            </div>
            @endif
        </div>
        <style>
            .rg { display: grid; gap: 20px; }
            .rg-hero, .rg-card { display: flex; flex-direction: column; overflow: hidden; background: #ffffff; border: 1px solid #ece5d6; color: #1d1a16; transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease; }
            .rg-hero { border-radius: 24px; }
            .rg-card { border-radius: 20px; }
            .rg-hero:hover, .rg-card:hover { transform: translateY(-3px); border-color: #e0d4bd; box-shadow: 0 24px 48px -30px rgb(29 26 22 / .45); color: #1d1a16; }
            .rg-hero:focus-visible, .rg-card:focus-visible { outline: 3px solid #b9500a; outline-offset: 3px; }

            .rg-media { position: relative; display: block; overflow: hidden; background: #1f5a3c; }
            .rg-media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
            .group:hover .rg-media img { transform: scale(1.03); }
            .rg-media__fallback { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-family: var(--font-serif, Georgia, serif); font-size: 64px; font-weight: 700; color: rgb(255 255 255 / .85); }
            /* La vedette prend la forme de son visuel (fixée en JS, entre 16:9 et 3:1) ; 21:9 en attendant. */
            .rg-hero__media { aspect-ratio: 21 / 9; }
            .rg-card__media { aspect-ratio: 16 / 9; }

            .rg-badge { position: absolute; top: 14px; left: 14px; display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: #ffffff; color: #1d1a16; font-size: 12px; font-weight: 600; }
            .rg-badge i { font-size: 10px; color: #b9500a; }
            .rg-badge.is-star { background: #febf48; color: #1f1a0e; }
            .rg-badge.is-star i { color: #1f1a0e; }

            .rg-hero__body { display: flex; align-items: center; justify-content: space-between; gap: 24px; padding: 22px 26px 24px; }
            .rg-hero__name { display: block; font-family: var(--font-serif, Georgia, serif); font-size: clamp(26px, 1.6vw + 14px, 34px); font-weight: 700; line-height: 1.1; }
            .rg-hero__desc { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; max-width: 68ch; margin-top: 10px; font-size: 15px; line-height: 1.55; color: #5b5346; }
            .rg-meta { display: flex; align-items: center; gap: 6px; margin-top: 6px; font-size: 13px; color: #5b5346; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .rg-meta i { font-size: 11px; color: #1b6b3a; }
            .rg-chip { display: inline-flex; align-items: center; gap: 6px; height: 26px; margin-top: 12px; padding: 0 10px; border-radius: 999px; background: #eef5ef; color: #1b4d31; font-size: 12px; font-weight: 600; }
            .rg-go { flex-shrink: 0; display: inline-flex; align-items: center; gap: 10px; height: 48px; padding: 0 22px; border-radius: 999px; background: #b9500a; color: #ffffff; font-size: 14px; font-weight: 600; white-space: nowrap; transition: background .2s ease; }
            .rg-hero:hover .rg-go { background: #9a4308; }
            .rg-go i { font-size: 12px; transition: transform .2s ease; }
            .rg-hero:hover .rg-go i { transform: translateX(3px); }

            .rg-row { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 20px; }
            .rg-card__body { flex: 1; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 16px 16px 18px 20px; }
            .rg-card__name { display: block; font-family: var(--font-serif, Georgia, serif); font-size: 21px; font-weight: 700; line-height: 1.15; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
            .rg-arrow { width: 40px; height: 40px; flex-shrink: 0; border-radius: 999px; background: #b9500a; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: transform .2s ease, background .2s ease; }
            .rg-card:hover .rg-arrow { transform: rotate(-45deg); background: #9a4308; }

            /* Tablette et mobile : les 3 villes défilent à l'horizontale (2 cartes et un bout visibles sur
               tablette, une et un bout sur mobile) au lieu de 3 colonnes trop étroites. */
            @media (max-width: 1023px) {
                .rg-row { display: flex; gap: 16px; overflow-x: auto; scroll-snap-type: x mandatory; scroll-padding-inline: 24px; margin: 0 -24px; padding: 0 24px 6px; scrollbar-width: none; }
                .rg-row::-webkit-scrollbar { display: none; }
                .rg-card { flex: 0 0 44%; scroll-snap-align: start; }
            }
            @media (max-width: 767px) {
                .rg { gap: 16px; }
                .rg-hero__body { flex-direction: column; align-items: flex-start; gap: 16px; padding: 18px 18px 20px; }
                .rg-go { width: 100%; justify-content: center; }
                .rg-row { gap: 14px; scroll-padding-inline: 16px; margin: 0 -16px; padding: 0 16px 6px; }
                .rg-card { flex-basis: 78%; }
            }
            @media (prefers-reduced-motion: reduce) {
                .rg-hero, .rg-card, .rg-media img, .rg-go i, .rg-arrow { transition: none; }
                .rg-hero:hover, .rg-card:hover { transform: none; }
            }
        </style>
        <script>
        /* Le cadre de la vedette épouse le format de son visuel (borné entre 16:9 et 3:1) : une bannière
           large avec texte intégré s'affiche en entier, sans recadrage. */
        (function () {
            var media = document.querySelector('[data-rg-fit]');
            var img = media && media.querySelector('img');
            if (!img) return;
            function fit() {
                if (!img.naturalWidth) return;
                var r = Math.min(3, Math.max(16 / 9, img.naturalWidth / img.naturalHeight));
                media.style.aspectRatio = r.toFixed(3);
            }
            if (img.complete) fit(); else img.addEventListener('load', fit);
        })();
        </script>

        {{-- ── Autres destinations : pastilles ── --}}
        @if($rtStrip->isNotEmpty())
        <nav class="ti-dest-strip reveal" aria-label="{{ __('Autres destinations') }}">
            <span class="ti-dest-strip__label">{{ __('Autres destinations') }}</span>
            @foreach($rtStrip as $m => $city)
                @php $destColors = ['#327661', '#1b6b3a', '#b9500a', '#8a6a1f', '#497318']; @endphp
                <a href="{{ route('tourist.city', $city->slug) }}" class="ti-dest-chip">
                    <span class="ti-dest-chip__avatar" style="background: {{ $destColors[$m % count($destColors)] }}">
                        @if($city->thumbnail || $city->cover_image)
                            <img src="{{ $city->thumbnail ?? $city->cover_image }}" alt="" loading="lazy" decoding="async" onerror="this.remove()">
                        @endif
                        <span aria-hidden="true">{{ mb_substr($city->name, 0, 1) }}</span>
                    </span>
                    <span>{{ $city->name }}<small>{{ $destSites($city) }}</small></span>
                </a>
            @endforeach
        </nav>
        @endif

        {{-- ── Catégories ─────────────────────────────────────────────── --}}
        @php
            $touristCats = \App\Models\TouristCategory::where('is_active', 1)->orderBy('sort_order')->limit(8)->get();
        @endphp
        @if($touristCats->isNotEmpty())
        <div class="mt-12 pt-10 border-t border-white/5" id="rt2-pills">
            <div class="flex items-center justify-center gap-3 mb-6">
                <span class="hidden sm:block w-8 h-px" style="background:rgba(242, 121, 15, 0.18)"></span>
                <p class="text-center text-[#1c1915] text-sm tracking-[.22em] uppercase font-elegant flex items-center gap-2">
                    <i class="fas fa-compass text-sm" style="color:#f2790f"></i>
                    {{ __('Explorer par catégorie') }}
                </p>
                <span class="hidden sm:block w-8 h-px" style="background:rgba(242, 121, 15, 0.18)"></span>
            </div>
            <div class="grid grid-cols-2 gap-2.5 sm:flex sm:flex-wrap sm:justify-center" id="rt2-pills-row">
                @foreach($touristCats as $j => $cat)
                @php
                    $catColor = $cat->color ?: '#a3450a';
                    $catRgb = sscanf(ltrim($catColor, '#'), '%02x%02x%02x') ?: [163, 69, 10];
                @endphp
                <a href="{{ route('tourist.cities', ['categorie' => $cat->slug]) }}"
                   class="rt2-pill {{ $j >= 4 ? 'rt2-pill-extra' : '' }}"
                   style="animation-delay: {{ $j * 45 }}ms; --pc: {{ $catRgb[0] }},{{ $catRgb[1] }},{{ $catRgb[2] }};">
                    <span class="rt2-pill-icon overflow-hidden">
                        @if($cat->icon_image_url)
                            <img src="{{ $cat->icon_image_url }}" alt="" class="w-full h-full object-cover rounded-full">
                        @else
                            <i class="{{ $cat->icon ?: 'fas fa-tag' }}"></i>
                        @endif
                    </span>
                    {{ $cat->name }}
                </a>
                @endforeach
            </div>
            @if($touristCats->count() > 4)
            <div class="mt-3 text-center sm:hidden">
                <button type="button" class="rt2-pills-toggle" id="rt2-pills-toggle" aria-expanded="false" aria-controls="rt2-pills-row">
                    <span id="rt2-pills-toggle-label">{{ __('Voir plus de catégories') }}</span>
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
            </div>
            @endif
        </div>
        @endif

    </div>
</section>

<script>
(function () {
    /* ── IntersectionObserver : déclenche toutes les animations ── */
    const io = new IntersectionObserver(entries => {
        entries.forEach(e => {
            if (!e.isIntersecting) return;
            const id = e.target.id;
            if (id === 'rt2-main') {
                e.target.querySelector('.rt2-hero')?.style.setProperty('animation-play-state', 'running');
                e.target.querySelectorAll('.rt2-side').forEach(c => c.style.animationPlayState = 'running');
                e.target.querySelector('.rt2-line')?.style.setProperty('animation-play-state', 'running');
            }
            if (id === 'rt2-strip-section') {
                e.target.querySelectorAll('.rt2-strip-card').forEach(c => c.style.animationPlayState = 'running');
            }
            if (id === 'rt2-pills') {
                e.target.querySelectorAll('.rt2-pill').forEach(c => c.style.animationPlayState = 'running');
            }
            io.unobserve(e.target);
        });
    }, { threshold: 0.1 });

    /* Pause tout dès le départ */
    document.querySelectorAll('.rt2-hero, .rt2-side, .rt2-strip-card, .rt2-line, .rt2-pill').forEach(el => {
        el.style.animationPlayState = 'paused';
    });

    ['rt2-main','rt2-strip-section','rt2-pills'].forEach(id => {
        const el = document.getElementById(id);
        if (el) io.observe(el);
    });

    /* ── Pilules catégorie : « Voir plus » sur mobile ── */
    const pillsToggle = document.getElementById('rt2-pills-toggle');
    const pillsRow = document.getElementById('rt2-pills-row');
    const pillsToggleLabel = document.getElementById('rt2-pills-toggle-label');
    if (pillsToggle && pillsRow) {
        pillsToggle.addEventListener('click', () => {
            const expanded = pillsRow.classList.toggle('is-expanded');
            pillsToggle.classList.toggle('is-expanded', expanded);
            pillsToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            pillsToggleLabel.textContent = expanded ? 'Voir moins' : {{ \Illuminate\Support\Js::from(__('Voir plus de catégories')) }};
        });
    }

    /* ── Pilules domaine culturel : « Voir plus » sur mobile ── */
    const domainToggle = document.getElementById('domain-pills-toggle');
    const domainRow = document.getElementById('domain-pills-row');
    const domainToggleLabel = document.getElementById('domain-pills-toggle-label');
    if (domainToggle && domainRow) {
        domainToggle.addEventListener('click', () => {
            const expanded = domainRow.classList.toggle('is-expanded');
            domainToggle.classList.toggle('is-expanded', expanded);
            domainToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            domainToggleLabel.textContent = expanded ? 'Voir moins' : {{ \Illuminate\Support\Js::from(__('Voir plus de domaines')) }};
        });
    }

    /* ── Navigation strip ── */
    const strip = document.getElementById('rt2-strip');
    const prog  = document.getElementById('rt2-prog');
    const prev  = document.getElementById('rt2-prev');
    const next  = document.getElementById('rt2-next');
    const STEP  = 240;

    function updateProg() {
        if (!strip || !prog) return;
        const max = strip.scrollWidth - strip.clientWidth;
        prog.style.width = max > 0 ? (strip.scrollLeft / max * 100) + '%' : '0%';
    }
    if (strip) {
        strip.addEventListener('scroll', updateProg, { passive: true });
        updateProg();
    }
    prev?.addEventListener('click', () => strip?.scrollBy({ left: -STEP, behavior: 'smooth' }));
    next?.addEventListener('click', () => strip?.scrollBy({ left:  STEP, behavior: 'smooth' }));

    /* Drag-to-scroll */
    if (strip) {
        let isDown = false, startX, scrollLeft;
        strip.addEventListener('mousedown', e => { isDown = true; startX = e.pageX - strip.offsetLeft; scrollLeft = strip.scrollLeft; });
        strip.addEventListener('mouseleave', () => isDown = false);
        strip.addEventListener('mouseup', () => isDown = false);
        strip.addEventListener('mousemove', e => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - strip.offsetLeft;
            strip.scrollLeft = scrollLeft - (x - startX) * 1.2;
        });
    }

    /* ── Tilt 3D sur le hero ── */
    const hero = document.querySelector('.rt2-hero');
    if (hero) {
        hero.addEventListener('mousemove', e => {
            const r = hero.getBoundingClientRect();
            const x = (e.clientX - r.left) / r.width  - .5;
            const y = (e.clientY - r.top)  / r.height - .5;
            hero.style.transform = `perspective(900px) rotateY(${x * 3.5}deg) rotateX(${-y * 2.5}deg)`;
        });
        hero.addEventListener('mouseleave', () => {
            hero.style.transform = '';
        });
    }
})();
</script>
@endif

{{-- ══════════════════════════════════════════════════════════
     SECTION : CULTURES IVOIRIENNES
══════════════════════════════════════════════════════════ --}}
@if(($homeCulturalPeoples ?? collect())->isNotEmpty())
<section id="cultures-ivoiriennes" class="py-16 sm:py-24 bg-dark-800 relative overflow-hidden"
    @if($culturesImage && $culturesImage->isVisible())
        style="--cultures-bg-image: url('{{ $culturesImage->image_url }}');"
    @endif
>
    <div class="section-watermark-bg"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">

        {{-- En-tête --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-12 sm:mb-16">
            <div class="reveal">
                <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-3">{{ __('Patrimoine vivant') }}</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">{{ __('Cultures Ivoiriennes') }}</h2>
                <p class="text-[#1c1915] font-elegant text-xl font-light mt-4 max-w-lg">
                    {{ __('Peuples, traditions, masques, gastronomie… Plongez dans la richesse des 60 ethnies de Côte d\'Ivoire.') }}
                </p>
            </div>
            <a href="{{ route('cultural.peoples') }}" class="shrink-0 inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#d8cdb8] bg-white text-sm font-semibold text-[#1d1a16] hover:border-[#b9500a] hover:text-[#b9500a] transition-colors">
                <span>{{ __('Explorer toutes les cultures') }}</span>
                <i class="fas fa-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1"></i>
            </a>
        </div>

        {{-- Grille des peuples --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($homeCulturalPeoples as $people)
                <div class="reveal">@include('partials.people-card', ['people' => $people, 'headingTag' => 'h3'])</div>
            @endforeach
        </div>

        {{-- Domaines culturels --}}
        @if(($homeCulturalDomains ?? collect())->isNotEmpty())
        <div class="mt-12 pt-10 border-t border-white/5">
            <p class="text-center text-[#1c1915] text-sm tracking-[.2em] uppercase font-elegant mb-6">{{ __('Explorer par domaine') }}</p>
            <div class="grid grid-cols-2 gap-3 sm:flex sm:flex-wrap sm:justify-center" id="domain-pills-row">
                @foreach($homeCulturalDomains as $j => $domain)
                <a href="{{ route('cultural.peoples', ['domaine' => $domain->slug]) }}" class="domain-pill group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 hover:border-gold-500/40 hover:bg-gold-500/10 transition-all duration-200 {{ $j >= 4 ? 'domain-pill-extra' : '' }}">
                    <i class="{{ $domain->icon }} text-sm" style="color: {{ $domain->color }}"></i>
                    <span class="text-[#1c1915] group-hover:text-[#a54a0b] text-sm font-medium transition-colors">{{ $domain->name }}</span>
                </a>
                @endforeach
            </div>
            @if($homeCulturalDomains->count() > 4)
            <div class="mt-3 text-center sm:hidden">
                <button type="button" class="rt2-pills-toggle" id="domain-pills-toggle" aria-expanded="false" aria-controls="domain-pills-row">
                    <span id="domain-pills-toggle-label">{{ __('Voir plus de domaines') }}</span>
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </button>
            </div>
            @endif
        </div>
        @endif

    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     SECTION : ÉVÉNEMENTS
══════════════════════════════════════════════════════════ --}}
<section id="evenements" class="py-16 sm:py-24 bg-dark-900 relative overflow-hidden"
    @if($evenementsImage && $evenementsImage->isVisible())
        style="--evenements-bg-image: url('{{ $evenementsImage->image_url }}');"
    @endif
>
    <div class="section-watermark-bg"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="flex items-end justify-between mb-10 sm:mb-14">
            <div class="reveal">
                <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-2">{{ __('Agenda culturel') }}</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">{{ __('Événements à venir') }}</h2>
            </div>
            <a href="{{ route('events.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#d8cdb8] bg-white text-sm font-semibold text-[#1d1a16] hover:border-[#b9500a] hover:text-[#b9500a] transition-colors">
                {{ __('Voir l\'agenda complet') }} <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse(($homeEvents ?? collect()) as $ev)
                <div class="reveal">@include('partials.event-card', ['event' => $ev, 'headingTag' => 'h3'])</div>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-[#d8cdb8] px-6 py-10 text-center text-[#6b6458]">{{ __('Aucun événement à venir pour le moment.') }}</p>
            @endforelse
            {{-- Moins de 3 événements : une tuile mène à l'agenda complet au lieu de laisser la grille vide. --}}
            @if(($homeEvents ?? collect())->isNotEmpty() && ($homeEvents ?? collect())->count() < 3)
                <a href="{{ route('events.index') }}" class="ti-event-more reveal">
                    <span class="ti-event-more__icon" aria-hidden="true"><i class="far fa-calendar-days"></i></span>
                    <span class="ti-event-more__title">{{ __('Tout l\'agenda culturel') }}</span>
                    <span class="ti-event-more__text">{{ __('Festivals, cérémonies, concerts et expositions à travers la Côte d\'Ivoire.') }}</span>
                    <span class="ti-event-more__link">{{ __('Voir tous les événements') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION : ART & CRÉATIONS
══════════════════════════════════════════════════════════ --}}
@php
    // Image de fond choisie dans l'administration (Apparence → Art & Créations).
    $artBg = \Illuminate\Support\Facades\Schema::hasTable('art_section_images') ? \App\Models\ArtSectionImage::query()->find(1) : null;
    $artBgUrl = $artBg && $artBg->isVisible() ? $artBg->image_url : null;
@endphp
@if($artBgUrl)
<style>
    /* !important : le thème clair force « background » sur cette section. */
    #art-creations.has-bg { isolation: isolate; background-image: var(--art-bg) !important; background-size: cover !important; background-position: center !important; background-repeat: no-repeat !important; }
    /* Voile uni (clair en thème clair, sombre en thème sombre) : garde le titre et les cartes lisibles. */
    #art-creations.has-bg::before { content: ''; position: absolute; inset: 0; z-index: -1; background: rgba(13,12,10,.78); }
    html:not(.dark) #art-creations.has-bg::before { background: rgba(233,229,217,.8); }
</style>
@endif
<section id="art-creations" class="py-16 sm:py-24 bg-dark-900 border-t border-white/5 relative overflow-hidden {{ $artBgUrl ? 'has-bg' : '' }}" @if($artBgUrl) style="--art-bg: url('{{ $artBgUrl }}');" @endif>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="flex items-end justify-between mb-10 sm:mb-14">
            <div class="reveal">
                <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-2">{{ __('Galerie d\'artistes') }}</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">{{ __('Art & Créations') }}</h2>
            </div>
            <a href="{{ route('art.index') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-[#d8cdb8] bg-white text-sm font-semibold text-[#1d1a16] hover:border-[#b9500a] hover:text-[#b9500a] transition-colors">
                {{ __('Voir toutes les créations') }} <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse(($homeArtworks ?? collect()) as $art)
                <div class="reveal">@include('partials.artwork-card', ['artwork' => $art, 'headingTag' => 'h3'])</div>
            @empty
                <p class="col-span-full rounded-2xl border border-dashed border-[#d8cdb8] px-6 py-10 text-center text-[#6b6458]">{{ __('Aucune œuvre disponible pour le moment.') }}</p>
            @endforelse
            {{-- Moins de 3 œuvres : une tuile mène à la galerie complète au lieu de laisser la grille vide. --}}
            @if(($homeArtworks ?? collect())->isNotEmpty() && ($homeArtworks ?? collect())->count() < 3)
                @if(($homeArtworks ?? collect())->count() < 2)
                    {{-- Commande sur mesure : on la met en avant tant que la galerie est peu fournie. --}}
                    <a href="{{ route('art.index') }}" class="ti-event-more is-forest reveal">
                        <span class="ti-event-more__icon" aria-hidden="true"><i class="fas fa-pen-nib"></i></span>
                        <span class="ti-event-more__title">{{ __('Commandez une œuvre sur mesure') }}</span>
                        <span class="ti-event-more__text">{{ __('Décrivez votre projet à un artiste ivoirien : sujet, format, technique. Il vous répond avec un devis, payable en ligne.') }}</span>
                        <span class="ti-event-more__link">{{ __('Trouver un artiste') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                    </a>
                @endif
                <a href="{{ route('art.index') }}" class="ti-event-more reveal">
                    <span class="ti-event-more__icon" aria-hidden="true"><i class="fas fa-table-cells-large"></i></span>
                    <span class="ti-event-more__title">{{ __('Toute la galerie') }}</span>
                    <span class="ti-event-more__text">{{ __('Peintures, sculptures et créations d\'artistes ivoiriens, à découvrir et à acquérir.') }}</span>
                    <span class="ti-event-more__link">{{ __('Voir toutes les créations') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></span>
                </a>
            @endif
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION : PARTENAIRES
══════════════════════════════════════════════════════════ --}}
<section id="partenaires" class="py-16 sm:py-24 bg-dark-900 border-t border-white/5 relative overflow-hidden"
    @if($partenairesImage && $partenairesImage->isVisible())
        style="--partenaires-bg-image: url('{{ $partenairesImage->image_url }}');"
    @endif
>
    <div class="section-watermark-bg"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14 reveal">
            <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-3">{{ __('Ils nous font confiance') }}</p>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4 leading-snug text-white">
                {{ __('Nos partenaires') }}
            </h2>
            <p class="text-[#1c1915] font-elegant text-xl font-light leading-relaxed">
                {{ __('Institutions, entreprises et organisations qui soutiennent la mise en valeur du patrimoine et du tourisme ivoirien.') }}
            </p>
        </div>

        @if(($homePartners ?? collect())->isNotEmpty())
            <div class="partners-marquee reveal py-1">
                <div class="partners-track">
                    @foreach([1, 2] as $loopIndex)
                        @foreach(($homePartners ?? collect()) as $partner)
                            @php $ptype = $partner->typeEnum(); @endphp
                            @php
                                $ptStyle = match ($ptype?->value) {
                                    'ngo' => ['#1b6b3a', '#eef6f0', '#14532d', 'fa-heart'],
                                    'government' => ['#b9500a', '#fdf4ec', '#9a4308', 'fa-landmark'],
                                    'educational' => ['#327661', '#dcece6', '#23584a', 'fa-graduation-cap'],
                                    'technology' => ['#1f1a0e', '#f6f3ec', '#3a352c', 'fa-laptop'],
                                    default => ['#8a6a1f', '#f6ecd5', '#6f5416', 'fa-circle-plus'],
                                };
                                $ptInitials = collect(preg_split('/[\s\-’\']+/u', $partner->name))
                                    ->reject(fn ($w) => mb_strlen($w) < 3 || in_array(mb_strtolower($w), ['des', 'les', 'aux', 'pour', 'and', 'the'], true))
                                    ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->join('');
                            @endphp
                            <article class="ti-partner group" @if($loopIndex === 2) aria-hidden="true" @endif>
                                @if($partner->logo_url)
                                    <span class="ti-partner__logo"><img src="{{ $partner->logo_url }}" alt="{{ $loopIndex === 1 ? $partner->name : '' }}" loading="lazy" decoding="async"></span>
                                @else
                                    <span class="ti-partner__mono" style="background: {{ $ptStyle[0] }}" aria-hidden="true">{{ $ptInitials ?: mb_strtoupper(mb_substr($partner->name, 0, 2)) }}</span>
                                @endif
                                <h3 class="ti-partner__name">{{ $partner->name }}</h3>
                                @if($ptype)
                                    <span class="ti-partner__type" style="background: {{ $ptStyle[1] }}; color: {{ $ptStyle[2] }}"><i class="fas {{ $ptStyle[3] }}" aria-hidden="true"></i>{{ $ptype->label() }}</span>
                                @endif
                                @if($partner->website_url)
                                    <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer" class="ti-partner__visit" @if($loopIndex === 2) tabindex="-1" @endif>
                                        {{ __('Visiter le site') }}<i class="fas fa-arrow-up-right-from-square" aria-hidden="true"></i>
                                    </a>
                                @else
                                    <span class="ti-partner__visit is-off">{{ __('Site non renseigné') }}</span>
                                @endif
                            </article>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @else
            <div class="col-span-full text-center text-gray-500 text-sm py-8 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
                {{ __('Les logos de nos partenaires seront affichés ici dès leur publication dans l’administration.') }}
            </div>
        @endif
    </div>
</section>

@include('partials.homepage-footer')

{{-- ══════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════ --}}
<script>
    // Reveal on scroll
    const reveals = document.querySelectorAll('.reveal');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 80);
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });
    reveals.forEach(el => observer.observe(el));

    // Close mobile menu on nav click
    document.querySelectorAll('#mobile-menu a').forEach(a => {
        a.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.remove('open');
        });
    });

    // Categories strip: scroll arrows + molette horizontale (la liste dépasse la largeur visible)
    document.querySelectorAll('.categories-nav').forEach((nav) => {
        const strip = nav.querySelector('.categories-strip');
        const prev = nav.querySelector('.categories-nav-arrow--left');
        const next = nav.querySelector('.categories-nav-arrow--right');
        if (!strip || !prev || !next) return;

        const updateArrows = () => {
            const max = strip.scrollWidth - strip.clientWidth;
            prev.hidden = strip.scrollLeft <= 4;
            next.hidden = max <= 4 || strip.scrollLeft >= max - 4;
        };
        const scrollByStep = (dir) => {
            strip.scrollBy({ left: dir * Math.round(strip.clientWidth * 0.7), behavior: 'smooth' });
        };

        prev.addEventListener('click', () => scrollByStep(-1));
        next.addEventListener('click', () => scrollByStep(1));
        strip.addEventListener('scroll', updateArrows, { passive: true });
        strip.addEventListener('wheel', (e) => {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                strip.scrollLeft += e.deltaY;
                e.preventDefault();
            }
        }, { passive: false });
        window.addEventListener('resize', updateArrows);
        updateArrows();
    });

    // Hero background carousel (images + vidéos)
    (function () {
        const layers = document.querySelectorAll('[data-hero-bg-layer]');
        if (!layers.length) return;

        // ── Source responsive selon la largeur d'écran ────────────────────
        function resolveVideoSrc(video) {
            const w = window.innerWidth;
            if (w >= 1024) return video.dataset.srcDesktop || '';
            if (w >= 640)  return video.dataset.srcTablet  || video.dataset.srcDesktop || '';
            return              video.dataset.srcMobile    || video.dataset.srcTablet  || video.dataset.srcDesktop || '';
        }

        // ── Initialise/corrige la src de chaque vidéo ─────────────────────
        document.querySelectorAll('[data-hero-video]').forEach(function (video) {
            const src = resolveVideoSrc(video);
            if (src && video.getAttribute('src') !== src) {
                video.src = src;
                video.load();
            }
        });

        // ── Play robuste : retry sur canplay si la vidéo n'est pas prête ──
        function tryPlay(video) {
            if (!video.src) return;
            var p = video.play();
            if (p !== undefined) {
                p.catch(function () {
                    video.addEventListener('canplay', function handler() {
                        video.removeEventListener('canplay', handler);
                        video.play().catch(function () {});
                    }, { once: true });
                });
            }
        }

        function getVideo(layer) {
            return layer.querySelector('[data-hero-video]');
        }

        // Slide unique : juste démarrer la vidéo (avec ended → retour au début)
        if (layers.length < 2) {
            var v = getVideo(layers[0]);
            if (v) {
                tryPlay(v);
                v.addEventListener('ended', function () { v.currentTime = 0; tryPlay(v); });
            }
            return;
        }

        const dots   = document.querySelectorAll('[data-hero-bg-dot]');
        const prevBtns = [
            document.getElementById('hero-bg-prev'),
            document.getElementById('hero-bg-prev-mobile'),
        ].filter(Boolean);
        const nextBtns = [
            document.getElementById('hero-bg-next'),
            document.getElementById('hero-bg-next-mobile'),
        ].filter(Boolean);

        let index = 0;
        let timer = null;

        // ── Démarre le timer fixe (pour les slides image) ─────────────────
        function start() { stop(); timer = setInterval(function () { next(); }, 7000); }
        function stop()  { if (timer) { clearInterval(timer); timer = null; } }

        // ── Adapte l'avancement au type du slide actif ────────────────────
        function scheduleAfterCurrent() {
            var layer = layers[index];
            var video = getVideo(layer);
            if (video) {
                // Slide vidéo : le timer est suspendu, c'est ended qui avance
                stop();
                video.addEventListener('ended', onVideoEnded);
            } else {
                // Slide image : timer fixe de 7 s
                start();
            }
        }

        function onVideoEnded() {
            this.removeEventListener('ended', onVideoEnded);
            next();
        }

        // ── Rendu d'un slide ──────────────────────────────────────────────
        function render() {
            layers.forEach(function (el, i) {
                const active = i === index;
                el.classList.toggle('opacity-100', active);
                el.classList.toggle('z-10',        active);
                el.classList.toggle('opacity-0',   !active);
                el.classList.toggle('z-0',         !active);

                const video = getVideo(el);
                if (video) {
                    // Nettoyer l'écouteur ended au cas où
                    video.removeEventListener('ended', onVideoEnded);
                    if (active) {
                        if (!video.src) {
                            const src = resolveVideoSrc(video);
                            if (src) { video.src = src; video.load(); }
                        }
                        video.currentTime = 0;
                        tryPlay(video);
                    } else {
                        video.pause();
                        video.currentTime = 0;
                    }
                }
            });

            dots.forEach(function (dot, i) {
                const active = i === index;
                dot.classList.toggle('w-6',               active);
                dot.classList.toggle('bg-gold-400',       active);
                dot.classList.toggle('w-2',               !active);
                dot.classList.toggle('bg-white/35',       !active);
                dot.classList.toggle('hover:bg-white/60', !active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });

            scheduleAfterCurrent();
        }

        function next() { index = (index + 1) % layers.length; render(); }
        function prev() { index = (index - 1 + layers.length) % layers.length; render(); }

        render();

        const root = document.getElementById('hero-bg-carousel');
        if (root) {
            // Survol : pause sur image, pause vidéo sur vidéo
            root.addEventListener('mouseenter', function () {
                stop();
                var v = getVideo(layers[index]);
                if (v) { v.removeEventListener('ended', onVideoEnded); v.pause(); }
            });
            root.addEventListener('mouseleave', function () {
                var v = getVideo(layers[index]);
                if (v) { tryPlay(v); v.addEventListener('ended', onVideoEnded); }
                else   { start(); }
            });
        }

        prevBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = getVideo(layers[index]);
                if (v) v.removeEventListener('ended', onVideoEnded);
                prev();
            });
        });
        nextBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var v = getVideo(layers[index]);
                if (v) v.removeEventListener('ended', onVideoEnded);
                next();
            });
        });
        dots.forEach(function (dot, i) {
            dot.addEventListener('click', function () {
                var v = getVideo(layers[index]);
                if (v) v.removeEventListener('ended', onVideoEnded);
                index = i; render();
            });
        });

        document.addEventListener('visibilitychange', function () {
            if (document.hidden) {
                stop();
                document.querySelectorAll('[data-hero-video]').forEach(function (v) {
                    v.removeEventListener('ended', onVideoEnded);
                    v.pause();
                });
            } else {
                var activeVideo = getVideo(layers[index]);
                if (activeVideo) {
                    tryPlay(activeVideo);
                    activeVideo.addEventListener('ended', onVideoEnded);
                } else {
                    start();
                }
            }
        });
    })();

    // Dropdown "Mon espace" : géré par partials/public-top-nav.blade.php (inclus plus haut sur cette page).
</script>

@include('partials.image-protection')
</body>
</html>
