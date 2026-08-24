<!DOCTYPE html>
<html lang="fr" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ $siteBrand['site_name'] }} — Magazine Culturel & Touristique Premium</title>
    <meta name="description" content="{{ $siteBrand['site_description'] ?: 'Découvrez la Côte d\'Ivoire à travers ses richesses culturelles, touristiques et patrimoniales. Grand reportage, adresses choisies, art de vivre ivoirien.' }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@300;400;500;600&family=Plus+Jakarta+Sans:ital,wght@0,400;0,500;0,600;0,700;1,500&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif:  ['Playfair Display', 'Georgia', 'serif'],
                        elegant:['Cormorant Garamond', 'Georgia', 'serif'],
                        sans:   ['Inter', 'system-ui', 'sans-serif'],
                    },
                    colors: {
                        gold: {
                            300: '#fdbe7b',
                            400: '#fa9a3c',
                            500: '#f2790f',
                            600: '#d4630a',
                        },
                        ivory: {
                            50:  '#fdfaf3',
                            100: '#faf3e0',
                        },
                        dark: {
                            900: '#e9e5d9',
                            800: '#e9e5d9',
                            700: '#e9e5d9',
                            600: '#e9e5d9',
                            500: '#e9e5d9',
                            400: '#e9e5d9',
                        }
                    },
                    animation: {
                        'fade-up':    'fadeUp .8s ease forwards',
                        'fade-in':    'fadeIn .6s ease forwards',
                        'slide-down': 'slideDown .4s ease forwards',
                    },
                    keyframes: {
                        fadeUp:    { '0%': {opacity:'0', transform:'translateY(30px)'}, '100%': {opacity:'1', transform:'translateY(0)'} },
                        fadeIn:    { '0%': {opacity:'0'}, '100%': {opacity:'1'} },
                        slideDown: { '0%': {opacity:'0', transform:'translateY(-10px)'}, '100%': {opacity:'1', transform:'translateY(0)'} },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }

        /* ── Typography ─────────────────────────────────────── */
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }

        /* ── Hero background ─────────────────────────────────── */
        .hero-bg {
            background-image:
                linear-gradient(to right, rgba(10,9,6,.92) 45%, rgba(10,9,6,.55) 100%),
                url('/images/hero-bg.jpg');
            background-size: cover;
            background-position: center 30%;
        }
        /* Fallback gradient when no image */
        .hero-bg-fallback {
            background: linear-gradient(135deg,
                #ffffff 0%,
                #ffffff 25%,
                #ffffff 50%,
                #ffffff 75%,
                #ffffff 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-bg-fallback::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 70% 50%, rgba(242, 121, 15,.12) 0%, transparent 60%),
                        radial-gradient(ellipse at 20% 80%, rgba(34,85,34,.08) 0%, transparent 50%);
            pointer-events: none;
        }
        .hero-bg-fallback::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 80px, rgba(242, 121, 15,.015) 80px, rgba(242, 121, 15,.015) 81px),
                repeating-linear-gradient(90deg, transparent, transparent 80px, rgba(242, 121, 15,.015) 80px, rgba(242, 121, 15,.015) 81px);
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
            background: linear-gradient(90deg, #f2790f, #fa9a3c);
            margin-top: 12px;
        }
        .gold-line-center::after { margin-left: auto; margin-right: auto; }

        /* ── Card hover effect ───────────────────────────────── */
        .article-card:hover .article-img { transform: scale(1.05); }
        .article-img { transition: transform .6s ease; }

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
            mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
            -webkit-mask-image: linear-gradient(to right, transparent 0%, black 8%, black 92%, transparent 100%);
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
        .partner-card {
            width: 240px;
            min-height: 220px;
            flex-shrink: 0;
        }
        @keyframes partnersScroll {
            from { transform: translateX(0); }
            to { transform: translateX(-50%); }
        }
        @media (max-width: 640px) {
            .partners-track { animation-duration: 20s; }
            .partner-card { width: 210px; min-height: 210px; }
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
            background: linear-gradient(90deg, transparent, rgba(242, 121, 15,0.45));
        }
        .featured-title-line.reverse {
            background: linear-gradient(90deg, rgba(242, 121, 15,0.45), transparent);
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
                linear-gradient(135deg, rgba(242, 121, 15,0.18), rgba(255,255,255,0.04)),
                rgba(255, 255, 255,0.84);
            box-shadow: 0 10px 24px rgba(0,0,0,0.26), inset 0 1px 0 rgba(255,255,255,0.08);
            backdrop-filter: blur(8px);
        }
        .featured-title-dot {
            width: 0.42rem;
            height: 0.42rem;
            border-radius: 9999px;
            background: linear-gradient(135deg, #fa9a3c, #f2790f);
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
        @media (prefers-reduced-motion: reduce) {
            .social-icon-ultra,
            .social-icon-ultra::before,
            .social-icon-ultra i,
            .footer-logo-ring,
            .footer-logo-ring::before {
                transition: none;
                animation: none;
            }
        }

        /* ── CSS variables dropdown ────────────────────── */
        :root {
            --dd-bg: rgba(8,10,14,0.96);
            --dd-border: rgba(255,255,255,0.07);
            --dd-shadow: 0 32px 64px -8px rgba(0,0,0,0.9), 0 0 0 1px rgba(255,255,255,0.03), inset 0 1px 0 rgba(255,255,255,0.06);
            --dd-divider: rgba(255,255,255,0.06);
            --dd-head-gradient: rgba(242, 121, 15,0.07);
        }
        html:not(.dark) {
            --dd-bg: rgba(255,253,248,0.99);
            --dd-border: rgba(0,0,0,0.09);
            --dd-shadow: 0 24px 48px -8px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,0.9);
            --dd-divider: rgba(0,0,0,0.07);
            --dd-head-gradient: rgba(242, 121, 15,0.05);
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
            background: linear-gradient(135deg, #f8f4ec 0%, #fefcf8 50%, #f5efe4 100%);
        }
        html:not(.dark) .featured-title-line {
            background: linear-gradient(90deg, transparent, rgba(194, 94, 10,0.4));
        }
        html:not(.dark) .featured-title-line.reverse {
            background: linear-gradient(90deg, rgba(194, 94, 10,0.4), transparent);
        }
        html:not(.dark) .featured-title-badge {
            border-color: rgba(194, 94, 10,0.35);
            background:
                linear-gradient(135deg, rgba(242, 121, 15,0.14), rgba(255,255,255,0.85)),
                rgba(255,255,255,0.92);
            box-shadow: 0 10px 20px rgba(194, 94, 10,0.12), inset 0 1px 0 rgba(255,255,255,0.95);
        }
        html:not(.dark) .featured-title-text { color:#a3450a; }
        html:not(.dark) .featured-title-dot { box-shadow: 0 0 8px rgba(194, 94, 10,0.45); }

        .event-price-badge {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(242, 121, 15,0.35);
            background: linear-gradient(135deg, rgba(242, 121, 15,0.2), rgba(255,255,255,0.06));
            box-shadow: 0 8px 20px rgba(0,0,0,0.22), inset 0 1px 0 rgba(255,255,255,0.12);
            animation: eventPricePulse 2.8s ease-in-out infinite;
        }
        .event-price-badge::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-120%);
            background: linear-gradient(100deg, transparent 15%, rgba(255,255,255,0.35) 45%, transparent 75%);
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
            background: linear-gradient(135deg, rgba(242, 121, 15,0.2), rgba(255,255,255,0.95));
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
            background: linear-gradient(130deg, rgba(255,255,255,0.04), rgba(255,255,255,0.01));
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
            background: linear-gradient(145deg, rgba(242, 121, 15,0.22), rgba(255,255,255,0.08));
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
            background: linear-gradient(135deg, #f4c65a 0%, #f2790f 65%, #cb8517 100%);
            box-shadow: 0 10px 20px rgba(242, 121, 15,0.28);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
        }
        .event-ticket-btn-modern::after {
            content: '';
            position: absolute;
            inset: 0;
            transform: translateX(-130%);
            background: linear-gradient(100deg, transparent 20%, rgba(255,255,255,0.38) 50%, transparent 80%);
            transition: transform .55s ease;
            pointer-events: none;
        }
        .event-ticket-btn-modern:hover {
            transform: translateY(-1px);
            filter: brightness(1.03);
            box-shadow: 0 14px 24px rgba(242, 121, 15,0.35);
        }
        .event-ticket-btn-modern:hover::after {
            transform: translateX(130%);
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
            background: linear-gradient(130deg, rgba(255,255,255,0.95), rgba(248,244,236,0.92));
        }
        html:not(.dark) .event-price-badge-modern {
            border-color: rgba(194, 94, 10,0.28);
            background: linear-gradient(145deg, rgba(242, 121, 15,0.24), rgba(255,255,255,0.95));
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
            background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
            color: rgba(203, 213, 225, 0.9);
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .category-chip::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.16), transparent);
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
            background: linear-gradient(90deg, #d4630a, #fa9a3c, #f2790f);
            transition: width .24s ease;
            box-shadow: 0 0 10px rgba(242, 121, 15,0.45);
        }
        .category-chip:hover {
            transform: translateY(-1px);
            color: #ffffff;
            border-color: rgba(242, 121, 15,0.3);
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.03));
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
            background: linear-gradient(135deg, #fa9a3c, #f2790f);
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
            -webkit-mask-image: linear-gradient(90deg, transparent, #000 28px, #000 calc(100% - 28px), transparent);
                    mask-image: linear-gradient(90deg, transparent, #000 28px, #000 calc(100% - 28px), transparent);
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
            background: linear-gradient(135deg, #fa9a3c, #f2790f);
            color: #1c1915;
            box-shadow: 0 8px 18px rgba(194, 94, 10,0.2);
        }

    </style>
</head>
<body class="relative bg-dark-900 text-white antialiased font-sans">
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
<section class="hero-bg-fallback hero-viewport relative flex flex-col" id="hero">

    {{-- Decorative orbs --}}
    <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-gold-500/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/3 left-1/6 w-64 h-64 bg-green-900/10 rounded-full blur-3xl pointer-events-none"></div>

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
                <div class="hero-overlay-primary absolute inset-0 z-20 bg-linear-to-r from-green-950/92 via-green-950/65 to-green-950/35 sm:from-green-950/88 sm:via-green-950/55 sm:to-green-950/25" aria-hidden="true"></div>
                <div class="hero-overlay-secondary absolute inset-0 z-20 bg-linear-to-t from-green-950/80 via-green-950/15 to-green-950/50" aria-hidden="true"></div>

                @if($heroSlides->count() > 1)
                    {{-- Flèches latérales (desktop & tablette) --}}
                    <button type="button" id="hero-bg-prev"
                            class="hero-bg-arrow hero-bg-arrow-prev pointer-events-auto absolute left-3 sm:left-5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex items-center justify-center w-11 h-11 rounded-full bg-green-950/45 border border-white/20 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition shadow-lg shadow-green-950/40 backdrop-blur-sm"
                            aria-label="Slide précédent">
                        <i class="fas fa-chevron-left text-sm"></i>
                    </button>
                    <button type="button" id="hero-bg-next"
                            class="hero-bg-arrow hero-bg-arrow-next pointer-events-auto absolute right-3 sm:right-5 top-1/2 -translate-y-1/2 z-30 hidden sm:flex items-center justify-center w-11 h-11 rounded-full bg-green-950/45 border border-white/20 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition shadow-lg shadow-green-950/40 backdrop-blur-sm"
                            aria-label="Slide suivant">
                        <i class="fas fa-chevron-right text-sm"></i>
                    </button>

                    {{-- Barre de contrôles (mobile + dots desktop) --}}
                    <div class="hero-bg-controls absolute inset-x-0 bottom-3 sm:bottom-4 z-30 flex items-center justify-center gap-2 sm:gap-3 pointer-events-none px-4">
                        <button type="button" id="hero-bg-prev-mobile"
                                class="pointer-events-auto sm:hidden w-9 h-9 rounded-full bg-green-950/55 border border-white/25 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center backdrop-blur-sm"
                                aria-label="Slide précédent">
                            <i class="fas fa-chevron-left text-xs"></i>
                        </button>
                        <div class="flex items-center gap-2 pointer-events-auto rounded-full bg-green-950/35 border border-white/10 px-3 py-1.5 backdrop-blur-sm" id="hero-bg-dots" role="tablist" aria-label="Choisir un slide">
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
                                aria-label="Slide suivant">
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
                <button type="button" class="categories-nav-arrow categories-nav-arrow--left" data-dir="-1" aria-label="Catégories précédentes" hidden>
                    <i class="fas fa-chevron-left"></i>
                </button>
                <div class="categories-strip flex items-center gap-2.5 overflow-x-auto scrollbar-none">
                    <a href="{{ route('articles.index') }}"
                       class="category-chip {{ !request('categorie') && request()->routeIs('home') ? 'is-active' : '' }} shrink-0 px-5 py-2 rounded-full text-sm font-semibold">
                        Tous
                    </a>
                    @foreach($homeCategories as $cat)
                    <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
                       class="category-chip shrink-0 px-5 py-2 rounded-full text-sm font-semibold">
                        {{ $cat->name_fr }}
                        @if($cat->articles_count > 0)
                        <span class="ml-1 opacity-70">({{ $cat->articles_count }})</span>
                        @endif
                    </a>
                    @endforeach
                </div>
                <button type="button" class="categories-nav-arrow categories-nav-arrow--right" data-dir="1" aria-label="Catégories suivantes">
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
<section id="articles" class="py-16 sm:py-20 bg-dark-900"
    @if($articlesImage && $articlesImage->isVisible())
        style="--articles-bg-image: url('{{ $articlesImage->image_url }}');"
    @endif
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        {{-- ── En-tête centré style presse ── --}}
        <div class="featured-title-wrap mb-10 sm:mb-12 reveal">
            <div class="featured-title-line"></div>
            <div class="featured-title-badge shrink-0">
                <span class="featured-title-dot"></span>
                <span class="featured-title-text">À la une</span>
                <span class="featured-title-dot"></span>
            </div>
            <div class="featured-title-line reverse"></div>
        </div>

        @if(($homeArticles ?? collect())->isNotEmpty())
        @php
            $mainArt   = $homeArticles->first();
            $leftArts  = $homeArticles->slice(1, 2)->values();
            $rightArts = $homeArticles->slice(3, 4)->values();
        @endphp

        {{-- ── Grille 3 colonnes ── --}}
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 lg:gap-8">

            {{-- ──────────────────────────────────────────
                 COLONNE GAUCHE (25%) — 2 articles empilés
            ────────────────────────────────────────── --}}
            <div class="lg:col-span-1 flex flex-col gap-6">
                @forelse($leftArts as $art)
                @php $contributors = $art->display_uploaders; @endphp
                <a href="{{ route('articles.show', $art->slug_fr) }}"
                   class="article-card group block reveal">
                    {{-- Image --}}
                    <div class="relative overflow-hidden rounded-xl h-40 bg-dark-700 mb-3">
                        @if($art->cover_url)
                            <img src="{{ $art->cover_url }}" alt="{{ $art->title_fr }}"
                                 class="article-img absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="article-img absolute inset-0 bg-gradient-to-br from-dark-700 to-dark-600 flex items-center justify-center">
                                <i class="fas fa-image text-dark-500 text-2xl"></i>
                            </div>
                        @endif
                    </div>
                    {{-- Catégorie --}}
                    @if($art->category)
                    <div class="flex flex-wrap gap-1.5 mb-2">
                        <span class="text-gold-400 text-[10px] font-semibold uppercase tracking-wider font-elegant">{{ $art->category->name_fr }}</span>
                    </div>
                    @endif
                    {{-- Titre --}}
                    <h3 class="font-serif text-sm font-bold leading-snug line-clamp-3 group-hover:text-gold-300 transition mb-2">
                        {{ $art->title_fr }}
                    </h3>
                    {{-- Auteur + date --}}
                    <div class="flex items-center gap-1.5 text-[#1c1915] font-elegant text-xl font-light flex-wrap">
                        @if($art->author)
                        <span class="font-semibold uppercase">{{ $art->author->full_name }}</span>
                        <span>·</span>
                        @endif
                        <span>{{ $art->published_at?->translatedFormat('d M Y') }}</span>
                    </div>
                    @if($contributors->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($contributors->take(2) as $contributor)
                        <span class="inline-flex items-center rounded-full border border-orange-500/25 bg-orange-500/10 px-1.5 py-0.5 text-[9px] font-medium text-orange-200">
                            {{ $contributor->first_name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </a>
                @empty
                <div class="text-gray-700 text-sm py-4">—</div>
                @endforelse
            </div>

            {{-- ──────────────────────────────────────────
                 COLONNE CENTRALE (50%) — article principal
            ────────────────────────────────────────── --}}
            <div class="lg:col-span-2 reveal">
                @if($mainArt)
                @php $mainContributors = $mainArt->display_uploaders; @endphp
                <a href="{{ route('articles.show', $mainArt->slug_fr) }}" class="article-card group block">
                    {{-- Grande image --}}
                    <div class="relative overflow-hidden rounded-2xl h-64 sm:h-80 bg-dark-700 mb-4">
                        @if($mainArt->cover_url)
                            <img src="{{ $mainArt->cover_url }}" alt="{{ $mainArt->title_fr }}"
                                 class="article-img absolute inset-0 w-full h-full object-cover">
                        @else
                            <div class="article-img absolute inset-0 bg-gradient-to-br from-dark-700 via-dark-800 to-dark-600 flex items-center justify-center">
                                <i class="fas fa-image text-dark-500 text-4xl"></i>
                            </div>
                        @endif
                        {{-- Overlay dégradé bas --}}
                        <div class="absolute inset-0 bg-gradient-to-t from-dark-900/60 via-transparent to-transparent pointer-events-none"></div>
                        {{-- Badge dernier paru --}}
                        <span class="absolute top-4 left-4 bg-gold-500 text-dark-900 text-[10px] font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                            <i class="fas fa-star text-[9px] mr-1"></i>Dernier paru
                        </span>
                    </div>
                    {{-- Catégorie --}}
                    @if($mainArt->category)
                    <div class="flex flex-wrap gap-2 mb-2">
                        <span class="text-gold-400 text-[11px] font-semibold uppercase tracking-wider font-elegant">{{ $mainArt->category->name_fr }}</span>
                    </div>
                    @endif
                    {{-- Grand titre --}}
                    <h2 class="font-serif text-xl sm:text-2xl lg:text-[1.6rem] font-bold leading-snug group-hover:text-gold-300 transition mb-3">
                        {{ $mainArt->title_fr }}
                    </h2>
                    {{-- Extrait --}}
                    @if($mainArt->excerpt_fr)
                    <p class="text-[#1c1915] leading-relaxed line-clamp-3 mb-4 font-elegant text-xl font-light">
                        {{ $mainArt->excerpt_fr }}
                    </p>
                    @endif
                    {{-- Auteur + date + lecture --}}
                    <div class="flex items-center gap-2 text-xs text-gray-500 pt-3 border-t border-white/5 flex-wrap">
                        @if($mainArt->author)
                        <span class="font-semibold text-gray-300 uppercase text-[11px]">{{ $mainArt->author->full_name }}</span>
                        <span class="text-gray-600">·</span>
                        @endif
                        <span>{{ $mainArt->published_at?->translatedFormat('d M Y') }}</span>
                        @if($mainArt->reading_time)
                        <span class="text-gray-600">·</span>
                        <span><i class="fas fa-clock mr-1 text-gold-500/50"></i>{{ $mainArt->reading_time }} min</span>
                        @endif
                    </div>
                    @if($mainContributors->isNotEmpty())
                    <div class="mt-3 flex flex-wrap gap-1.5">
                        @foreach($mainContributors->take(4) as $contributor)
                        <span class="inline-flex items-center rounded-full border border-orange-500/25 bg-orange-500/10 px-2.5 py-1 text-[10px] font-medium text-orange-200">
                            {{ $contributor->full_name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </a>
                @endif
            </div>

            {{-- ──────────────────────────────────────────
                 COLONNE DROITE (25%) — liste mini-cards
            ────────────────────────────────────────── --}}
            <div class="lg:col-span-1">
                <div class="flex flex-col divide-y divide-white/5">
                    @forelse($rightArts as $art)
                    @php $contributors = $art->display_uploaders; @endphp
                    <a href="{{ route('articles.show', $art->slug_fr) }}"
                       class="group flex items-start gap-3 py-3 first:pt-0 hover:bg-dark-800/50 -mx-2 px-2 rounded-lg transition-all duration-200 reveal">
                        {{-- Texte --}}
                        <div class="flex-1 min-w-0">
                            @if($art->category)
                            <span class="text-gold-400/60 text-[9px] uppercase tracking-wider font-elegant block mb-0.5">{{ $art->category->name_fr }}</span>
                            @endif
                            <h4 class="font-serif text-[11px] sm:text-xs font-semibold line-clamp-2 group-hover:text-gold-300 transition leading-snug">
                                {{ $art->title_fr }}
                            </h4>
                            <p class="text-[#1c1915] font-elegant text-xl font-light mt-1">{{ $art->published_at?->translatedFormat('d M Y') }}</p>
                            @if($contributors->isNotEmpty())
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($contributors->take(2) as $contributor)
                                <span class="inline-flex items-center rounded-full border border-orange-500/25 bg-orange-500/10 px-1.5 py-0.5 text-[9px] font-medium text-orange-200">
                                    {{ $contributor->first_name }}
                                </span>
                                @endforeach
                            </div>
                            @endif
                        </div>
                        {{-- Miniature --}}
                        <div class="w-16 h-14 shrink-0 rounded-lg overflow-hidden bg-dark-700 relative">
                            @if($art->cover_url)
                                <img src="{{ $art->cover_url }}" alt="{{ $art->title_fr }}"
                                     class="article-img absolute inset-0 w-full h-full object-cover">
                            @else
                                <div class="article-img absolute inset-0 bg-gradient-to-br from-dark-700 to-dark-600 flex items-center justify-center">
                                    <i class="fas fa-image text-dark-500 text-[10px]"></i>
                                </div>
                            @endif
                        </div>
                    </a>
                    @empty
                    <p class="text-gray-700 text-sm py-4">Aucun article supplémentaire.</p>
                    @endforelse
                </div>

                {{-- Lien "Voir tous" mobile --}}
                <a href="{{ route('articles.index') }}"
                   class="mt-5 flex items-center justify-center gap-2 w-full py-2.5 rounded-xl border border-gold-500/20 text-gold-400 text-xs font-semibold hover:bg-gold-500/5 hover:border-gold-500/40 transition">
                    Voir tous les articles <i class="fas fa-arrow-right text-[10px]"></i>
                </a>
            </div>

        </div>{{-- /grid --}}

        @else
        {{-- Fallback : aucun article --}}
        <div class="text-center py-16 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
            <i class="fas fa-newspaper text-dark-600 text-5xl mb-4"></i>
            <p class="text-gray-500 font-elegant text-lg mb-4">Aucun article publié pour le moment.</p>
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm transition">
                <i class="fas fa-plus text-xs"></i> Publier le premier article
            </a>
        </div>
        @endif

    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION : ANNUAIRE PRESTATAIRES
══════════════════════════════════════════════════════════ --}}
<section id="annuaire" class="py-16 sm:py-24 bg-dark-800 relative overflow-hidden"
    @if($annuaireImage && $annuaireImage->isVisible())
        style="--annuaire-bg-image: url('{{ $annuaireImage->image_url }}');"
    @endif
>
    <div class="absolute inset-0 pointer-events-none opacity-40">
        <div class="absolute -top-32 -left-24 w-80 h-80 rounded-full bg-gold-500/10 blur-3xl"></div>
        <div class="absolute -bottom-20 right-0 w-72 h-72 rounded-full bg-orange-400/10 blur-3xl"></div>
    </div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">
            <aside class="lg:col-span-5 reveal lg:sticky lg:top-28 self-start rounded-3xl border border-white/10 bg-gradient-to-br from-white/[0.06] via-white/[0.02] to-transparent backdrop-blur-xl shadow-2xl shadow-green-950/25 overflow-hidden">
                {{-- Decorative accent bar --}}
                <div class="h-1.5 bg-gradient-to-r from-gold-400 via-gold-500 to-orange-500"></div>

                <div class="p-6 sm:p-8">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-gold-400 via-gold-500 to-orange-500 flex items-center justify-center shadow-lg shadow-gold-500/25">
                            <i class="fas fa-compass text-dark-900"></i>
                        </div>
                        <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant">Annuaire des prestataires</p>
                    </div>

                    <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4 leading-snug">
                        Les meilleures adresses<br>de Côte d'Ivoire
                    </h2>
                    <p class="text-[#1c1915] font-elegant text-xl font-light leading-relaxed mb-7">
                        Hôtels, restaurants, guides touristiques, artisans… Découvrez notre sélection premium d'établissements vérifiés et notés par notre équipe.
                    </p>

                    {{-- Category list — sidebar-style vertical menu --}}
                    <nav class="flex flex-col gap-1.5 mb-7 border-t border-white/10 pt-5">
                        @php
                            $sidebarCatIcons = ['hotels' => 'fa-bed', 'restaurants' => 'fa-utensils', 'sites-touristiques' => 'fa-mountain-sun', 'agences-voyages' => 'fa-plane-departure', 'loisirs-culture' => 'fa-masks-theater', 'transports' => 'fa-van-shuttle'];
                        @endphp
                        @if(($homeProviderCategories ?? collect())->isNotEmpty())
                            @foreach($homeProviderCategories as $pc)
                            <a href="{{ route('providers.index', ['categorie' => $pc->slug]) }}"
                               class="group flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-gold-500/10 transition-all duration-300">
                                <span class="w-8 h-8 shrink-0 rounded-lg border border-white/10 bg-dark-700/70 flex items-center justify-center group-hover:border-gold-400/45 group-hover:bg-gold-500/15 transition-all duration-300">
                                    <i class="fas {{ $sidebarCatIcons[$pc->slug] ?? 'fa-store' }} text-gold-400/80 text-xs group-hover:text-gold-300 transition"></i>
                                </span>
                                <span class="flex-1 text-gray-300 text-sm font-semibold tracking-wide group-hover:text-gold-300 transition-colors duration-300">{{ $pc->name_fr }}</span>
                                <i class="fas fa-chevron-right text-[10px] text-gray-500 opacity-0 -translate-x-1 group-hover:opacity-100 group-hover:translate-x-0 group-hover:text-gold-400 transition-all duration-300"></i>
                            </a>
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
                       class="flex items-center justify-center gap-2.5 w-full px-6 py-3.5 rounded-xl text-dark-900 font-bold text-sm bg-gradient-to-r from-gold-400 via-gold-500 to-orange-500 hover:from-gold-300 hover:to-orange-400 transition-all duration-300 shadow-lg shadow-gold-500/25 hover:-translate-y-0.5">
                        <i class="fas fa-compass"></i> Explorer l'annuaire
                    </a>
                </div>
            </aside>

            <div class="lg:col-span-7 grid grid-cols-1 sm:grid-cols-2 gap-4 reveal">
                @forelse(($homeProviders ?? collect()) as $p)
                <a href="{{ route('providers.show', $p->slug) }}" class="group rounded-2xl border border-white/10 bg-gradient-to-br from-dark-700/80 via-dark-700/60 to-dark-800/70 p-4.5 sm:p-5 hover:border-gold-500/35 hover:-translate-y-1 transition-all duration-300 shadow-lg shadow-green-950/20">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-11 h-11 rounded-xl border border-white/10 bg-dark-600/80 flex items-center justify-center group-hover:border-gold-500/35 group-hover:bg-gold-500/10 transition">
                            <i class="fas fa-store text-gold-400/70 group-hover:text-gold-300 transition"></i>
                        </div>
                        @if($p->is_verified)
                        <span class="inline-flex items-center bg-emerald-500/15 text-emerald-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-400/35">
                            <i class="fas fa-badge-check mr-1 text-[9px]"></i>Vérifié
                        </span>
                        @endif
                    </div>
                    <p class="text-white text-sm sm:text-[15px] font-semibold font-serif leading-snug line-clamp-2">{{ $p->name }}</p>
                    <p class="text-[#1c1915] font-elegant text-xl font-light mt-1">{{ $p->category->name_fr ?? 'Prestataire' }}</p>
                    <div class="mt-3 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                            <i class="fas fa-star text-[10px] {{ $i <= round((float) ($p->rating_avg ?? 0)) ? 'text-gold-400' : 'text-dark-500' }}"></i>
                            @endfor
                        </div>
                        <span class="text-[#1c1915] font-elegant text-xl font-light">{{ number_format((float) ($p->rating_avg ?? 0), 1) }} ({{ (int) ($p->rating_count ?? 0) }})</span>
                    </div>
                </a>
                @empty
                <div class="sm:col-span-2 text-center text-gray-500 text-sm py-10 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
                    Aucun prestataire actif pour le moment.
                </div>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     BANNER : DÉCOUVERTES
══════════════════════════════════════════════════════════ --}}
{{-- [COMMENTÉ] section #decouvertes --}}
{{-- <section id="decouvertes" class="py-16 sm:py-24 bg-dark-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(45deg, #f2790f 0, #f2790f 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>
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
                <p class="text-white text-sm font-semibold font-serif">{{ $cat->name_fr }}</p>
                <p class="text-gray-500 text-xs mt-1">
                    {{ $cat->articles_count > 0 ? $cat->articles_count.' article'.($cat->articles_count > 1 ? 's' : '') : 'Bientôt' }}
                </p>
            </a>
            @endforeach
        </div>
        @else
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @foreach(['Patrimoine','Art & Culture','Nature','Gastronomie','Destinations','Art de vivre'] as $i => $label)
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

/* Image de fond configurable (back-office) de la section Régions Touristiques :
   superposée d'un voile crème à forte opacité pour ne rien changer à la lisibilité
   existante — l'image n'apparaît qu'en filigrane, comme pour le footer. */
#regions-touristiques {
    background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--regions-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
/* Image de fond configurable (back-office) de la section Annuaire — même
   traitement que la section Régions Touristiques. */
#annuaire {
    background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--annuaire-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
/* Image de fond configurable (back-office) de la section Cultures Ivoiriennes —
   même traitement que les sections Régions Touristiques et Annuaire. */
#cultures-ivoiriennes {
    background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--cultures-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
/* Titre des cartes ethnies : posé sur un dégradé réellement sombre
   (from-green-950/85), non affecté par le pont de thème clair — couleur
   fixée en dur (text-white y était réécrit en noir par le pont, invisible). */
#cultures-ivoiriennes .cti-title { color: #ffffff !important; }
#cultures-ivoiriennes a.group:hover .cti-title { color: #fde3b8 !important; }
/* Image de fond configurable (back-office) de la section Événements — même
   traitement que les autres sections d'accueil. */
#evenements {
    background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--evenements-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
/* Image de fond configurable (back-office) de la section Partenaires — même
   traitement que les autres sections d'accueil. */
#partenaires {
    background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--partenaires-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}
/* Image de fond configurable (back-office) de la section Articles (« À la une ») —
   même traitement que les autres sections d'accueil. */
#articles {
    background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55)), var(--articles-bg-image, none);
    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
}

/* ── Hero card ─────────────────────── */
.rt2-hero {
    opacity: 0;
    animation: rt2-hero-in .8s cubic-bezier(.22,1,.36,1) forwards;
    animation-play-state: paused;
    position: relative;
    overflow: hidden;
    border-radius: 20px;
    border: 1px solid rgba(255,255,255,.06);
    min-height: 480px;
    display: flex;
    flex-direction: column;
    justify-content: flex-end;
    transition: border-color .35s ease, box-shadow .35s ease;
}
.rt2-hero:hover {
    border-color: rgba(242, 121, 15,.35);
    box-shadow: 0 0 40px rgba(242, 121, 15,.10), inset 0 0 60px rgba(0,0,0,.1);
}
.rt2-hero .rt2-img {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover;
    transform-origin: center;
    transition: transform 1s cubic-bezier(.22,1,.36,1), filter .5s ease;
    will-change: transform;
}
.rt2-hero:hover .rt2-img {
    transform: scale(1.06) translateY(-8px);
    filter: brightness(1.06) saturate(1.1);
}
/* Sweep shimmer */
.rt2-hero::after {
    content: '';
    position: absolute; inset: 0; top: 0; height: 100%;
    width: 40%;
    background: linear-gradient(105deg, transparent 20%, rgba(255,255,255,.06) 50%, transparent 80%);
    left: -60%;
    pointer-events: none;
    z-index: 6;
}
.rt2-hero:hover::after {
    animation: rt2-sweep .8s ease forwards;
}
/* Ligne de titre animée */
.rt2-line {
    display: block;
    height: 2px;
    background: linear-gradient(to right, #f2790f, transparent);
    margin-bottom: .75rem;
    animation: rt2-line .6s .5s ease forwards;
    width: 0; opacity: 0;
}

/* ── Side cards ────────────────────── */
.rt2-side {
    opacity: 0;
    animation: rt2-side-in .6s cubic-bezier(.22,1,.36,1) forwards;
    animation-play-state: paused;
    position: relative;
    overflow: hidden;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.06);
    display: flex;
    align-items: flex-end;
    min-height: 148px;
    transition: border-color .3s ease, transform .3s cubic-bezier(.34,1.56,.64,1), box-shadow .3s ease;
    flex: 1;
}
.rt2-side:hover {
    transform: translateX(5px) translateY(-2px);
    border-color: rgba(242, 121, 15,.35);
    box-shadow: 0 8px 32px rgba(0,0,0,.4), 0 0 20px rgba(242, 121, 15,.08);
}
.rt2-side .rt2-img {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .7s cubic-bezier(.22,1,.36,1), filter .4s ease;
    will-change: transform;
}
.rt2-side:hover .rt2-img {
    transform: scale(1.08);
    filter: brightness(1.07);
}
/* Indicateur latéral doré */
.rt2-side::before {
    content: '';
    position: absolute;
    left: 0; top: 20%; bottom: 20%;
    width: 2px;
    background: linear-gradient(to bottom, transparent, #f2790f, transparent);
    z-index: 5;
    opacity: 0;
    transition: opacity .3s ease;
}
.rt2-side:hover::before { opacity: 1; }

/* ── Strip (bas, scroll horizontal) ── */
.rt2-strip-wrap {
    overflow-x: auto;
    scroll-snap-type: x mandatory;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    cursor: grab;
}
.rt2-strip-wrap::-webkit-scrollbar { display: none; }
.rt2-strip-wrap:active { cursor: grabbing; }

.rt2-strip-card {
    scroll-snap-align: start;
    flex-shrink: 0;
    width: 220px;
    height: 160px;
    border-radius: 14px;
    overflow: hidden;
    position: relative;
    border: 1px solid rgba(255,255,255,.06);
    opacity: 0;
    animation: rt2-strip-in .5s cubic-bezier(.22,1,.36,1) forwards;
    animation-play-state: paused;
    transition: border-color .3s ease, transform .3s ease;
}
.rt2-strip-card:hover {
    border-color: rgba(242, 121, 15,.4);
    transform: translateY(-4px) scale(1.02);
}
.rt2-strip-card .rt2-img {
    position: absolute; inset: 0;
    width: 100%; height: 100%; object-fit: cover;
    transition: transform .6s cubic-bezier(.22,1,.36,1);
}
.rt2-strip-card:hover .rt2-img { transform: scale(1.08); }

/* Scroll indicator arrows */
.rt2-nav-btn {
    width: 40px; height: 40px;
    border-radius: 50%;
    background: rgba(255,255,255,.9);
    border: 1px solid rgba(242, 121, 15,.3);
    display: flex; align-items: center; justify-content: center;
    color: #a3450a;
    cursor: pointer;
    transition: background .2s, border-color .2s, transform .2s;
    backdrop-filter: blur(8px);
    flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(0,0,0,.08);
}
.rt2-nav-btn:hover {
    background: rgba(242, 121, 15,.15);
    border-color: rgba(242, 121, 15,.5);
    transform: scale(1.1);
}
.rt2-nav-btn i { animation: rt2-bounce-x 1.4s ease-in-out infinite; }
.rt2-nav-btn.rt2-prev i { animation: none; }

/* Progress bar */
.rt2-progress {
    height: 2px;
    background: rgba(233, 229, 217, .06);
    border-radius: 999px;
    overflow: hidden;
    margin-top: 10px;
}
.rt2-progress-bar {
    height: 100%;
    background: linear-gradient(to right, #f2790f, #f5c842);
    border-radius: 999px;
    transition: width .25s ease;
    width: 0%;
}

/* Badge pulse */
.rt2-badge {
    animation: rt2-pulse 2.2s ease-in-out infinite;
}

/* Pill categories */
@@keyframes rt2-pill-in {
    from { opacity:0; transform: translateY(10px) scale(.92); }
    to   { opacity:1; transform: translateY(0)    scale(1);   }
}
.rt2-pill {
    --pc: 163,69,10;
    opacity: 0;
    animation: rt2-pill-in .45s cubic-bezier(.22,1,.36,1) both;
    animation-play-state: paused;
    display: inline-flex; align-items: center; gap: 9px;
    padding: 6px 17px 6px 6px;
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.08);
    background: rgba(255,255,255,.9);
    box-shadow: 0 1px 3px rgba(28,25,21,.05);
    font-size: 14px; font-weight: 600;
    color: #1c1915;
    transition: background .25s ease, border-color .25s ease, color .25s ease, transform .25s ease, box-shadow .25s ease;
    text-decoration: none;
    white-space: nowrap;
}
.rt2-pill-icon {
    width: 24px; height: 24px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: rgba(var(--pc),.13);
    color: rgb(var(--pc));
    font-size: 10px;
    transition: background .25s ease, color .25s ease, transform .3s cubic-bezier(.22,1,.36,1);
}
.rt2-pill:hover {
    border-color: rgba(var(--pc),.4);
    color: rgb(var(--pc));
    transform: translateY(-3px);
    box-shadow: 0 12px 22px -10px rgba(var(--pc),.55);
}
.rt2-pill:hover .rt2-pill-icon {
    background: rgb(var(--pc));
    color: #fff;
    transform: scale(1.12) rotate(-8deg);
}

/* Overlay gradient commun */
.rt2-overlay-b {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(0,0,0,.88) 0%, rgba(0,0,0,.22) 50%, transparent 100%);
    transition: background .4s ease;
    z-index: 2;
}
.rt2-hero:hover .rt2-overlay-b   { background: linear-gradient(to top, rgba(0,0,0,.80) 0%, rgba(0,0,0,.10) 50%, transparent 100%); }
.rt2-side:hover .rt2-overlay-b   { background: linear-gradient(to top, rgba(0,0,0,.82) 0%, rgba(0,0,0,.1) 50%, transparent 100%); }
.rt2-strip-card:hover .rt2-overlay-b { background: linear-gradient(to top, rgba(0,0,0,.82) 0%, transparent 70%); }

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

    {{-- Fond décoratif subtil --}}
    <div class="absolute inset-0 pointer-events-none opacity-[0.025]"
         style="background-image: radial-gradient(circle, #f2790f 1px, transparent 1px); background-size: 28px 28px;"></div>
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold-500/20 to-transparent"></div>
    {{-- Halo ambiance --}}
    <div class="absolute -top-60 -left-40 w-[700px] h-[700px] rounded-full pointer-events-none"
         style="background: radial-gradient(circle, rgba(242, 121, 15,.06) 0%, transparent 65%);"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative" id="rt2-section">

        {{-- ── En-tête ─────────────────────────────────────────────── --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-10 sm:mb-14 reveal">
            <div>
                <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Voyage en Côte d'Ivoire</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">Régions Touristiques</h2>
                <p class="text-[#1c1915] font-elegant text-xl font-light mt-4 max-w-lg">
                    Plages, parcs nationaux, monuments historiques… Explorez les villes et leurs merveilles.
                </p>
            </div>
            <a href="{{ route('tourist.cities') }}"
               class="shrink-0 inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl border border-gold-500/25 bg-dark-800/70 text-sm text-gold-300 hover:text-gold-200 hover:border-gold-400/50 hover:bg-dark-700/80 transition-all duration-300 font-semibold tracking-wide group hover:-translate-y-0.5 self-start sm:self-auto">
                <span>Explorer toutes les villes</span>
                <i class="fas fa-arrow-right text-xs group-hover:translate-x-1 transition-transform duration-300"></i>
            </a>
        </div>

        @php
            $rtMain  = $homeTouristCities->take(4);   /* Hero + 3 side */
            $rtStrip = $homeTouristCities->skip(4);   /* Strip bas */
            $rtHero  = $rtMain->first();
            $rtSides = $rtMain->skip(1);
        @endphp

        {{-- ── Layout principal (hero gauche + colonne droite) ───────── --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-4 mb-4" id="rt2-main">

            {{-- Hero (grande carte gauche, 3 colonnes sur 5) --}}
            @if($rtHero)
            <a href="{{ route('tourist.city', $rtHero->slug) }}"
               class="rt2-hero lg:col-span-3" style="min-height:480px;">

                @if($rtHero->cover_image || $rtHero->thumbnail)
                <img src="{{ $rtHero->cover_image ?? $rtHero->thumbnail }}" alt="{{ $rtHero->name }}"
                     class="rt2-img" loading="lazy">
                @else
                <div class="absolute inset-0 bg-gradient-to-br from-orange-900/70 to-green-900"></div>
                @endif

                <div class="rt2-overlay-b"></div>

                {{-- Badge --}}
                @if($rtHero->is_featured)
                <div class="absolute top-4 left-4 z-10">
                    <span class="rt2-badge inline-flex items-center gap-1.5 px-3 py-1.5 bg-gold-500/90 text-black text-[11px] font-bold rounded-full backdrop-blur-sm">
                        <i class="fas fa-star text-[9px]"></i> Destination vedette
                    </span>
                </div>
                @endif

                {{-- Contenu bas --}}
                <div class="rt2-content p-7">
                    <span class="rt2-line"></span>
                    <h3 class="rt2-title font-serif text-4xl sm:text-5xl font-bold mb-2 leading-tight">
                        {{ $rtHero->name }}
                    </h3>
                    @if($rtHero->district)
                    <p class="rt2-district text-base font-elegant mb-4">
                        <i class="fas fa-map-marker-alt mr-1.5 text-sm"></i>{{ $rtHero->district }}
                    </p>
                    @endif
                    <div class="flex items-center gap-4">
                        <span class="rt2-meta text-base">
                            <i class="fas fa-map-pin rt2-meta-icon mr-1.5 text-sm"></i>
                            {{ $rtHero->sites_count }} site{{ $rtHero->sites_count > 1 ? 's' : '' }} à explorer
                        </span>
                        <span class="rt2-cta ml-auto flex items-center gap-2 text-base font-semibold group-hover:gap-3 transition-all duration-300">
                            Explorer
                            <span class="w-9 h-9 rounded-full bg-gold-500/15 border border-gold-500/30 flex items-center justify-center group-hover:bg-gold-500 group-hover:text-black group-hover:border-gold-500 transition-all duration-300">
                                <i class="fas fa-arrow-right text-sm"></i>
                            </span>
                        </span>
                    </div>
                </div>
            </a>
            @endif

            {{-- Colonne droite : 3 cards empilées (2 colonnes sur 5) --}}
            <div class="lg:col-span-2 flex flex-col gap-4">
                @foreach($rtSides as $k => $city)
                <a href="{{ route('tourist.city', $city->slug) }}"
                   class="rt2-side" style="animation-delay: {{ ($k + 1) * 120 }}ms; flex:1; min-height:148px;">

                    @if($city->cover_image || $city->thumbnail)
                    <img src="{{ $city->cover_image ?? $city->thumbnail }}" alt="{{ $city->name }}"
                         class="rt2-img" loading="lazy">
                    @else
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-900/60 to-green-900"></div>
                    @endif

                    <div class="rt2-overlay-b"></div>

                    @if($city->is_featured)
                    <div class="absolute top-2.5 right-2.5 z-10">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-gold-500/80 text-black text-[9px] font-bold rounded-full">
                            <i class="fas fa-star text-[7px]"></i>
                        </span>
                    </div>
                    @endif

                    <div class="rt2-content px-4 pb-4 pt-2 w-full">
                        <div class="flex items-end justify-between">
                            <div class="flex-1 min-w-0">
                                <h3 class="rt2-title font-serif text-lg font-bold truncate group-hover:text-gold-200 transition-colors">{{ $city->name }}</h3>
                                @if($city->district)
                                <p class="rt2-district text-xs truncate mt-0.5">{{ $city->district }}</p>
                                @endif
                            </div>
                            <span class="rt2-arrow-btn ml-3 w-7 h-7 rounded-full bg-gold-500/10 border border-gold-500/20 flex items-center justify-center shrink-0 group-hover:bg-gold-500 group-hover:text-black transition-all duration-300">
                                <i class="fas fa-arrow-right text-[9px]"></i>
                            </span>
                        </div>
                        <p class="rt2-meta-light text-xs mt-1.5">
                            <i class="fas fa-map-pin mr-1 text-[9px]"></i>{{ $city->sites_count }} site{{ $city->sites_count > 1 ? 's' : '' }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>

        </div>

        {{-- ── Strip horizontal (villes restantes) ───────────────────── --}}
        @if($rtStrip->isNotEmpty())
        <div class="mt-2" id="rt2-strip-section">
            <div class="flex items-center gap-3 mb-3">
                <span class="text-[#1c1915] text-[10px] tracking-[.2em] uppercase font-elegant flex-1">Autres destinations</span>
                <button class="rt2-nav-btn rt2-prev" id="rt2-prev" aria-label="Précédent">
                    <i class="fas fa-arrow-left text-xs"></i>
                </button>
                <button class="rt2-nav-btn" id="rt2-next" aria-label="Suivant">
                    <i class="fas fa-arrow-right text-xs"></i>
                </button>
            </div>

            <div class="rt2-strip-wrap flex gap-3" id="rt2-strip">
                @foreach($rtStrip as $m => $city)
                <a href="{{ route('tourist.city', $city->slug) }}"
                   class="rt2-strip-card group" style="animation-delay: {{ $m * 80 }}ms;">

                    @if($city->cover_image || $city->thumbnail)
                    <img src="{{ $city->cover_image ?? $city->thumbnail }}" alt="{{ $city->name }}"
                         class="rt2-img" loading="lazy">
                    @else
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-900/60 to-green-900"></div>
                    @endif

                    <div class="rt2-overlay-b"></div>

                    <div class="rt2-content absolute bottom-0 left-0 right-0 p-3.5">
                        <h3 class="rt2-title font-serif text-base font-bold truncate group-hover:text-gold-200 transition-colors">{{ $city->name }}</h3>
                        <p class="rt2-meta-light text-xs mt-0.5">
                            <i class="fas fa-map-pin rt2-meta-icon-soft mr-1 text-[9px]"></i>
                            {{ $city->sites_count }} site{{ $city->sites_count > 1 ? 's' : '' }}
                        </p>
                    </div>
                </a>
                @endforeach
            </div>

            {{-- Barre de progression --}}
            <div class="rt2-progress">
                <div class="rt2-progress-bar" id="rt2-prog"></div>
            </div>
        </div>
        @endif

        {{-- ── Catégories ─────────────────────────────────────────────── --}}
        @php
            $touristCats = \App\Models\TouristCategory::where('is_active', 1)->orderBy('sort_order')->limit(8)->get();
        @endphp
        @if($touristCats->isNotEmpty())
        <div class="mt-12 pt-10 border-t border-white/5" id="rt2-pills">
            <div class="flex items-center justify-center gap-3 mb-6">
                <span class="hidden sm:block w-8 h-px" style="background:linear-gradient(to right, transparent, rgba(242,121,15,.35))"></span>
                <p class="text-center text-[#1c1915] text-sm tracking-[.22em] uppercase font-elegant flex items-center gap-2">
                    <i class="fas fa-compass text-sm" style="color:#f2790f"></i>
                    Explorer par catégorie
                </p>
                <span class="hidden sm:block w-8 h-px" style="background:linear-gradient(to left, transparent, rgba(242,121,15,.35))"></span>
            </div>
            <div class="flex flex-wrap justify-center gap-2.5">
                @foreach($touristCats as $j => $cat)
                @php
                    $catColor = $cat->color ?: '#a3450a';
                    $catRgb = sscanf(ltrim($catColor, '#'), '%02x%02x%02x') ?: [163, 69, 10];
                @endphp
                <a href="{{ route('tourist.cities') }}#{{ $cat->slug }}"
                   class="rt2-pill"
                   style="animation-delay: {{ $j * 45 }}ms; --pc: {{ $catRgb[0] }},{{ $catRgb[1] }},{{ $catRgb[2] }};">
                    <span class="rt2-pill-icon">
                        <i class="{{ $cat->icon ?: 'fas fa-tag' }}"></i>
                    </span>
                    {{ $cat->name }}
                </a>
                @endforeach
            </div>
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

    <div class="absolute inset-0 pointer-events-none opacity-[0.03]" style="background-image: radial-gradient(circle, #f2790f 1px, transparent 1px); background-size: 32px 32px;"></div>
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-gold-500/20 to-transparent"></div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">

        {{-- En-tête --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6 mb-12 sm:mb-16">
            <div class="reveal">
                <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-3">Patrimoine vivant</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">Cultures Ivoiriennes</h2>
                <p class="text-[#1c1915] font-elegant text-xl font-light mt-4 max-w-lg">
                    Peuples, traditions, masques, gastronomie… Plongez dans la richesse des 60 ethnies de Côte d'Ivoire.
                </p>
            </div>
            <a href="{{ route('cultural.peoples') }}" class="shrink-0 inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl border border-gold-500/25 bg-dark-800/70 text-sm text-gold-300 hover:text-gold-200 hover:border-gold-400/50 hover:bg-dark-700/80 transition-all duration-300 font-semibold tracking-wide group hover:-translate-y-0.5 self-start sm:self-auto">
                <span>Explorer toutes les cultures</span>
                <i class="fas fa-arrow-right text-xs transition-transform duration-300 group-hover:translate-x-1"></i>
            </a>
        </div>

        {{-- Grille des peuples --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach($homeCulturalPeoples as $people)
            <a href="{{ route('cultural.people', $people->slug) }}" class="group relative rounded-2xl overflow-hidden border border-white/5 hover:border-gold-500/30 transition-all duration-300 hover:-translate-y-1 reveal" style="min-height: 220px;">

                {{-- Image --}}
                @if($people->cover_image)
                <img src="{{ $people->cover_image }}" alt="{{ $people->name }}"
                    class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                @else
                <div class="absolute inset-0 flex items-center justify-center"
                    style="background: linear-gradient(135deg, #1a1a12 0%, #2a2510 100%);">
                    <i class="fas fa-people-group text-gold-500/20 text-6xl"></i>
                </div>
                @endif

                <div class="absolute inset-0 bg-gradient-to-t from-green-950/85 via-green-950/30 to-transparent group-hover:from-green-950/75 transition-all duration-300"></div>

                {{-- Badge vedette --}}
                @if($people->is_featured)
                <div class="absolute top-3 left-3">
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gold-500/90 text-black text-[10px] font-bold rounded-full backdrop-blur-sm">
                        <i class="fas fa-star text-[8px]"></i> À la une
                    </span>
                </div>
                @endif

                {{-- Badge zone --}}
                @if($people->zone_geographique)
                <div class="absolute top-3 right-3">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-950/50 backdrop-blur-sm text-slate-300 text-[10px] rounded-full border border-white/10">
                        <i class="fas fa-map-location-dot text-gold-400/60 text-[8px]"></i>
                        {{ $people->zone_geographique }}
                    </span>
                </div>
                @endif

                <div class="absolute bottom-0 left-0 right-0 p-5">
                    <h3 class="cti-title font-serif text-xl font-bold mb-1 transition-colors">
                        {{ $people->name }}
                    </h3>
                    @if($people->famille_linguistique)
                    <p class="text-gold-400/80 text-xs font-elegant mb-2 truncate">
                        <i class="fas fa-language text-[10px] mr-1"></i>{{ $people->famille_linguistique }}
                    </p>
                    @endif
                    <div class="flex items-center justify-between">
                        @if($people->capitale_culturelle)
                        <span class="text-[#eae6da] text-xs">
                            <i class="fas fa-map-pin text-gold-500/60 mr-1 text-[10px]"></i>
                            {{ $people->capitale_culturelle }}
                        </span>
                        @endif
                        <span class="w-7 h-7 rounded-full bg-gold-500/10 border border-gold-500/20 flex items-center justify-center text-gold-400 group-hover:bg-gold-500/20 group-hover:translate-x-0.5 transition-all ml-auto">
                            <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Domaines culturels --}}
        @if(($homeCulturalDomains ?? collect())->isNotEmpty())
        <div class="mt-12 pt-10 border-t border-white/5">
            <p class="text-center text-[#1c1915] text-sm tracking-[.2em] uppercase font-elegant mb-6">Explorer par domaine</p>
            <div class="flex flex-wrap justify-center gap-3">
                @foreach($homeCulturalDomains as $domain)
                <a href="{{ route('cultural.peoples', ['domaine' => $domain->slug]) }}" class="group inline-flex items-center gap-2 px-4 py-2 rounded-full border border-white/10 bg-white/5 hover:border-gold-500/40 hover:bg-gold-500/10 transition-all duration-200">
                    <i class="{{ $domain->icon }} text-sm" style="color: {{ $domain->color }}"></i>
                    <span class="text-[#1c1915] group-hover:text-[#a54a0b] text-sm font-medium transition-colors">{{ $domain->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</section>
@endif

{{-- ══════════════════════════════════════════════════════════
     SECTION : ÉVÉNEMENTS
══════════════════════════════════════════════════════════ --}}
<section id="evenements" class="py-16 sm:py-24 bg-dark-900"
    @if($evenementsImage && $evenementsImage->isVisible())
        style="--evenements-bg-image: url('{{ $evenementsImage->image_url }}');"
    @endif
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-10 sm:mb-14">
            <div class="reveal">
                <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-2">Agenda culturel</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">Événements à venir</h2>
            </div>
            <a href="{{ route('events.index') }}"
               class="hidden sm:inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl border border-gold-500/25 bg-dark-800/70 text-base text-gold-300 hover:text-gold-200 hover:border-gold-400/50 hover:bg-dark-700/80 shadow-lg shadow-green-950/20 hover:shadow-gold-500/10 transition-all duration-300 font-semibold tracking-wide group hover:-translate-y-0.5">
                <span>Voir l'agenda complet</span>
                <i class="fas fa-arrow-right text-sm transition-transform duration-300 group-hover:translate-x-1 group-hover:scale-110"></i>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse(($homeEvents ?? collect()) as $ev)
            @php
                $daysUntil = $ev->starts_at ? now()->startOfDay()->diffInDays($ev->starts_at->startOfDay(), false) : null;
                $urgencyLabel = $daysUntil === null
                    ? null
                    : ($daysUntil <= 0 ? "Aujourd'hui" : "Dans {$daysUntil} jour" . ($daysUntil > 1 ? 's' : ''));
            @endphp
            <article class="group bg-orange-900/20 border border-orange-700/20 rounded-2xl overflow-hidden hover:border-gold-500/30 transition-all duration-300 reveal">
                <a href="{{ route('events.show', $ev->slug) }}" class="relative block h-40 bg-dark-700">
                    @if($ev->cover_url)
                        <img src="{{ $ev->cover_url }}" alt="{{ $ev->title_fr }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-linear-to-br from-dark-700 to-dark-600">
                            <i class="fas fa-calendar-days text-dark-500 text-3xl"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-green-950/70 via-green-950/20 to-transparent"></div>
                    @if($urgencyLabel)
                        <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-rose-500/90 text-white">
                            <i class="fas fa-bolt mr-1 text-[9px]"></i>{{ $urgencyLabel }}
                        </span>
                    @endif
                    <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $ev->is_free ? 'bg-emerald-500/90 text-white' : 'bg-orange-500/90 text-dark-900' }}">
                        {{ $ev->is_free ? 'Gratuit' : 'Payant' }}
                    </span>
                </a>

                <div class="p-5 flex gap-4">
                    <div class="shrink-0 w-16 text-center">
                        <p class="text-gold-400 text-[11px] font-bold uppercase tracking-widest">{{ $ev->starts_at?->translatedFormat('M') }}</p>
                        <p class="font-serif text-4xl font-bold text-white leading-none mt-1">{{ $ev->starts_at?->format('d') }}</p>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="inline-block bg-white/8 text-gold-300 text-[10px] uppercase tracking-wider px-2 py-0.5 rounded-full mb-2">{{ $ev->category->name_fr ?? 'Événement' }}</span>
                        <h3 class="font-serif text-sm font-semibold group-hover:text-gold-300 transition leading-snug line-clamp-2">{{ $ev->title_fr }}</h3>
                        <p class="text-[#1c1915] text-sm mt-2">
                            <i class="fas fa-location-dot mr-1 text-gold-500/60"></i>{{ $ev->city ?: 'Côte d\'Ivoire' }}
                        </p>
                        <div class="event-cta-row">
                            <span class="event-price-badge-modern">
                                <i class="fas fa-ticket"></i>
                                @if($ev->is_free || (float) ($ev->price ?? 0) <= 0)
                                    Gratuit
                                @else
                                    {{ number_format((float) $ev->price, 0, ',', ' ') }} FCFA
                                @endif
                            </span>
                            @if($ev->ticket_url)
                                <a href="{{ $ev->ticket_url }}" target="_blank" class="event-ticket-btn-modern">
                                    <i class="fas fa-ticket-simple text-[10px]"></i> Réserver
                                </a>
                            @else
                                <a href="{{ route('events.show', $ev->slug) }}" class="event-cta-ghost">
                                    <i class="fas fa-arrow-right text-[10px]"></i> Voir détails
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </article>
            @empty
            <div class="col-span-3 text-center text-gray-500 py-8">Aucun événement à venir pour le moment.</div>
            @endforelse
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION : PARTENAIRES
══════════════════════════════════════════════════════════ --}}
<section id="partenaires" class="py-16 sm:py-24 bg-dark-900 border-t border-white/5"
    @if($partenairesImage && $partenairesImage->isVisible())
        style="--partenaires-bg-image: url('{{ $partenairesImage->image_url }}');"
    @endif
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14 reveal">
            <p class="text-gold-400 text-sm tracking-[.25em] uppercase font-elegant mb-3">Ils nous font confiance</p>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4 leading-snug text-white">
                Nos partenaires
            </h2>
            <p class="text-[#1c1915] font-elegant text-xl font-light leading-relaxed">
                Institutions, entreprises et organisations qui soutiennent la mise en valeur du patrimoine et du tourisme ivoirien.
            </p>
        </div>

        @if(($homePartners ?? collect())->isNotEmpty())
            <div class="partners-marquee reveal py-1">
                <div class="partners-track">
                    @foreach([1, 2] as $loopIndex)
                        @foreach(($homePartners ?? collect()) as $partner)
                            @php $ptype = $partner->typeEnum(); @endphp
                            <article class="partner-card bg-dark-800/80 border border-white/8 rounded-2xl p-5 sm:p-6 flex flex-col items-center text-center hover:border-gold-500/25 hover:bg-dark-800 transition-all duration-300 group">
                                @if($partner->logo_url)
                                    <div class="w-full max-w-[140px] h-20 sm:h-24 mb-4 flex items-center justify-center mx-auto">
                                        @if($partner->website_url)
                                            <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer" class="flex h-full w-full items-center justify-center">
                                                <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" class="max-h-full max-w-full object-contain opacity-90 group-hover:opacity-100 transition">
                                            </a>
                                        @else
                                            <img src="{{ $partner->logo_url }}" alt="{{ $partner->name }}" class="max-h-full max-w-full object-contain opacity-90 group-hover:opacity-100 transition">
                                        @endif
                                    </div>
                                @else
                                    <div class="w-14 h-14 rounded-xl bg-dark-700 border border-white/10 flex items-center justify-center mb-4 group-hover:border-gold-500/30 transition">
                                        <i class="fas fa-handshake text-gold-400/70 text-xl"></i>
                                    </div>
                                @endif
                                <h3 class="font-serif text-sm sm:text-base font-semibold text-white leading-snug line-clamp-2 mb-1">{{ $partner->name }}</h3>
                                @if($ptype)
                                    <span class="text-[10px] sm:text-xs text-gold-400/80 uppercase tracking-wider mb-3">{{ $ptype->label() }}</span>
                                @endif
                                @if($partner->website_url)
                                    <a href="{{ $partner->website_url }}" target="_blank" rel="noopener noreferrer"
                                        class="mt-auto inline-flex items-center gap-1.5 text-xs text-gold-400 hover:text-gold-300 font-medium transition">
                                        <span>Visiter le site</span>
                                        <i class="fas fa-arrow-up-right-from-square text-[10px]"></i>
                                    </a>
                                @endif
                            </article>
                        @endforeach
                    @endforeach
                </div>
            </div>
        @else
            <div class="col-span-full text-center text-gray-500 text-sm py-8 rounded-2xl border border-dashed border-white/10 bg-dark-800/40">
                Les logos de nos partenaires seront affichés ici dès leur publication dans l’administration.
            </div>
        @endif
    </div>
</section>

@include('partials.homepage-footer')

@include('partials.homepage-bubbles')

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

    // ── Dropdown "Mon espace" ─────────────────────────────────────────────
    (function () {
        const btn      = document.getElementById('nav-user-dropdown-btn');
        const dropdown = document.getElementById('nav-user-dropdown');
        const chevron  = document.getElementById('nav-dd-chevron');
        const wrap     = document.getElementById('nav-user-dropdown-wrap');
        if (!btn || !dropdown) return;

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = !dropdown.classList.contains('hidden');
            dropdown.classList.toggle('hidden', isOpen);
            chevron?.classList.toggle('rotate-180', !isOpen);
        });
        document.addEventListener('click', (e) => {
            if (!wrap?.contains(e.target)) {
                dropdown.classList.add('hidden');
                chevron?.classList.remove('rotate-180');
            }
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); }
        });
    })();

</script>

@include('partials.image-protection')
</body>
</html>
