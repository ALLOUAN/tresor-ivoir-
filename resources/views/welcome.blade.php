<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ $siteBrand['site_name'] }} — Magazine Culturel & Touristique Premium</title>
    <meta name="description" content="{{ $siteBrand['site_description'] ?: 'Découvrez la Côte d\'Ivoire à travers ses richesses culturelles, touristiques et patrimoniales. Grand reportage, adresses choisies, art de vivre ivoirien.' }}">
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
                            300: '#fcd68a',
                            400: '#f5b942',
                            500: '#e8a020',
                            600: '#c4811a',
                        },
                        ivory: {
                            50:  '#fdfaf3',
                            100: '#faf3e0',
                        },
                        dark: {
                            900: '#0d0d0b',
                            800: '#141410',
                            700: '#1c1c16',
                            600: '#252520',
                            500: '#2e2e26',
                            400: '#3a3a30',
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
                #0d0d0b 0%,
                #1a1506 25%,
                #2a1f08 50%,
                #1c1408 75%,
                #0d0d0b 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-bg-fallback::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse at 70% 50%, rgba(232,160,32,.12) 0%, transparent 60%),
                        radial-gradient(ellipse at 20% 80%, rgba(34,85,34,.08) 0%, transparent 50%);
            pointer-events: none;
        }
        .hero-bg-fallback::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                repeating-linear-gradient(0deg, transparent, transparent 80px, rgba(232,160,32,.015) 80px, rgba(232,160,32,.015) 81px),
                repeating-linear-gradient(90deg, transparent, transparent 80px, rgba(232,160,32,.015) 80px, rgba(232,160,32,.015) 81px);
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
        #hero-bg-carousel img {
            height: 100%;
            object-fit: cover;
        }
        #hero-bg-carousel img { object-position: 52% 42%; }
        #hero-bg-carousel .hero-bg-layer { background: #000; }
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
            #hero { padding-top: 4.5rem; }
            #hero-bg-carousel.hero-viewport { aspect-ratio: 16 / 8.5; }
        }
        @media (min-width: 640px) and (max-width: 1023px) {
            #hero { padding-top: 8.25rem; }
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
            #hero { padding-top: 8.25rem; }
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
            #hero { padding-top: 8.25rem; }
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
        ::-webkit-scrollbar-track { background: #0d0d0b; }
        ::-webkit-scrollbar-thumb { background: #e8a020; border-radius: 3px; }

        /* ── Ultra-modern top bar & header (2025) ─────────────── */
        .font-plus { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        #home-top-bar.topbar-ultra {
            position: relative;
            isolation: isolate;
            background: linear-gradient(105deg, #0c0b09 0%, #12100c 42%, #15120e 100%);
            border-bottom: 1px solid rgba(232, 160, 32, 0.14);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }
        #home-top-bar.topbar-ultra::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            background:
                radial-gradient(100% 180% at 0% 0%, rgba(232, 160, 32, 0.11), transparent 52%),
                radial-gradient(80% 120% at 100% 100%, rgba(120, 90, 40, 0.08), transparent 50%);
            pointer-events: none;
        }
        #home-top-bar.topbar-ultra > * { position: relative; z-index: 1; }
        .topbar-slogan-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.85rem 0.35rem 0.65rem;
            border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(255,255,255,0.04);
            backdrop-filter: blur(8px);
            box-shadow: 0 0 0 1px rgba(232,160,32,0.06), 0 4px 24px rgba(0,0,0,0.25);
        }
        .topbar-slogan-pill .pulse-dot {
            width: 6px; height: 6px; border-radius: 9999px;
            background: linear-gradient(135deg, #f5b942, #e8a020);
            box-shadow: 0 0 10px rgba(232,160,32,0.75);
            animation: topbarPulse 2.2s ease-in-out infinite;
        }
        @keyframes topbarPulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.65; transform: scale(0.92); }
        }
        .topbar-action {
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            padding: 0.32rem 0.85rem;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.02em;
            color: rgba(226, 232, 240, 0.88);
            border: 1px solid rgba(255,255,255,0.07);
            background: rgba(255,255,255,0.035);
            backdrop-filter: blur(10px);
            transition: color .2s ease, border-color .2s ease, background .2s ease, transform .2s ease, box-shadow .2s ease;
        }
        .topbar-action:hover {
            color: #fde68a;
            border-color: rgba(232,160,32,0.35);
            background: rgba(232,160,32,0.08);
            box-shadow: 0 0 20px rgba(232,160,32,0.12);
            transform: translateY(-1px);
        }
        .topbar-action i { opacity: 0.75; }

        #main-header.main-header-ultra:not(.header-scrolled) {
            background: linear-gradient(180deg, rgba(13,13,11,0.92) 0%, rgba(13,13,11,0.72) 55%, rgba(13,13,11,0.45) 100%);
            backdrop-filter: blur(16px) saturate(160%);
            -webkit-backdrop-filter: blur(16px) saturate(160%);
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        #main-header.main-header-ultra .header-shell {
            position: relative;
        }
        #main-header.main-header-ultra .header-shell::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: min(72%, 640px);
            height: 1px;
            background: linear-gradient(90deg, transparent, rgba(232,160,32,0.25), transparent);
            pointer-events: none;
        }
        .nav-pill {
            position: relative;
            padding: 0.5rem 1rem;
            border-radius: 9999px;
            font-size: 0.8125rem;
            font-weight: 500;
            letter-spacing: 0.03em;
            color: rgba(226, 232, 240, 0.82);
            transition: color .2s ease, background .2s ease, box-shadow .2s ease;
        }
        .nav-pill:hover {
            color: #fef3c7;
            background: rgba(255,255,255,0.06);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .nav-pill-glow::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 2px;
            width: 0;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, #c4811a, #f5b942, #e8a020);
            transform: translateX(-50%);
            transition: width .28s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 0 12px rgba(232,160,32,0.45);
        }
        .nav-pill-glow:hover::after { width: 70%; }
        .logo-ring {
            position: relative;
            border-radius: 1rem;
            padding: 2px;
            background: linear-gradient(135deg, rgba(232,160,32,0.55), rgba(255,255,255,0.12), rgba(232,160,32,0.25));
            box-shadow: 0 8px 32px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.05) inset;
        }
        .logo-ring-inner {
            border-radius: calc(1rem - 2px);
            overflow: hidden;
            background: rgba(13,13,11,0.9);
        }
        .lang-switch-ultra {
            display: inline-flex;
            padding: 3px;
            border-radius: 9999px;
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
        }
        .lang-switch-ultra button {
            border-radius: 9999px;
            padding: 0.35rem 0.75rem;
            font-size: 11px;
            font-weight: 600;
            letter-spacing: 0.06em;
            transition: background .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .btn-ghost-header {
            border-radius: 9999px;
            border: 1px solid rgba(255,255,255,0.12);
            background: rgba(255,255,255,0.04);
            transition: border-color .2s ease, background .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .btn-ghost-header:hover {
            border-color: rgba(232,160,32,0.35);
            background: rgba(232,160,32,0.08);
            color: #fef3c7;
            box-shadow: 0 0 24px rgba(232,160,32,0.1);
        }
        .btn-gold-header {
            border-radius: 9999px;
            background: linear-gradient(135deg, #f5b942 0%, #e8a020 50%, #c4811a 100%);
            box-shadow: 0 4px 20px rgba(232,160,32,0.35), inset 0 1px 0 rgba(255,255,255,0.25);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        }
        .btn-gold-header:hover {
            transform: translateY(-1px);
            filter: brightness(1.05);
            box-shadow: 0 8px 28px rgba(232,160,32,0.45), inset 0 1px 0 rgba(255,255,255,0.3);
        }
        #mobile-menu.mobile-menu-ultra {
            background: rgba(10,10,9,0.96);
            backdrop-filter: blur(20px) saturate(180%);
            -webkit-backdrop-filter: blur(20px) saturate(180%);
            border-top: 1px solid rgba(232,160,32,0.12);
        }
        #mobile-menu.mobile-menu-ultra a,
        #mobile-menu.mobile-menu-ultra button {
            border-radius: 0.75rem;
            border: 1px solid transparent;
        }
        #mobile-menu.mobile-menu-ultra a:hover,
        #mobile-menu.mobile-menu-ultra button:hover {
            border-color: rgba(232,160,32,0.2);
            background: rgba(232,160,32,0.06);
        }

        /* ── Header scroll effect ────────────────────────────── */
        .header-scrolled {
            background: rgba(8,8,7,.94) !important;
            backdrop-filter: blur(28px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(28px) saturate(180%) !important;
            border-bottom: 1px solid rgba(232,160,32,.22) !important;
            box-shadow: 0 20px 50px rgba(0,0,0,0.45);
        }

        /* ── Gold line decoration ────────────────────────────── */
        .gold-line::after {
            content: '';
            display: block;
            width: 60px;
            height: 2px;
            background: linear-gradient(90deg, #e8a020, #f5b942);
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

        /* ── Footer ultra-moderne ─────────────────────────────── */
        .footer-ultra {
            position: relative;
            isolation: isolate;
            background-color: #060504;
            background-image:
                radial-gradient(ellipse 90% 50% at 50% -20%, rgba(232, 160, 32, 0.09), transparent 55%),
                radial-gradient(ellipse 50% 40% at 100% 100%, rgba(99, 102, 241, 0.05), transparent 45%);
        }
        .footer-ultra::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='fn'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23fn)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.9;
        }
        .footer-ultra-inner { position: relative; z-index: 1; }
        .footer-v2-link {
            display: block;
            padding: 0.32rem 0;
            font-size: 0.8125rem;
            color: rgba(163, 163, 163, 0.92);
            transition: color .18s ease, padding-left .18s ease;
        }
        .footer-v2-link:hover {
            color: #fde68a;
            padding-left: 0.35rem;
        }

    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">
@php
    $topContact = $siteBrand['contact'] ?? [];
    $topPhoneDisplay = !empty($topContact['phone_1']) ? $topContact['phone_1'] : '+225 27 22 48 36 90';
    $topPhoneHref = 'tel:'.preg_replace('/[^\d+]/', '', $topPhoneDisplay);
    $infoPages = $informationPages ?? collect();
    $infoGuide = $infoPages->firstWhere('slug', 'user-guide');
    $infoFaq = $infoPages->firstWhere('slug', 'faq');
    $infoLegal = $infoPages->firstWhere('slug', 'legal-notice');
@endphp

{{-- ══════════════════════════════════════════════════════════
     TOP BAR (ultra-modern)
══════════════════════════════════════════════════════════ --}}
<div id="home-top-bar" class="topbar-ultra font-plus text-gray-300 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('contact_success') || session('contact_error'))
            <div class="py-2.5 text-center text-[11px] font-semibold tracking-wide border-b border-white/[0.06] {{ session('contact_success') ? 'text-emerald-400/95' : 'text-red-400/95' }}">
                {{ session('contact_success') ?? session('contact_error') }}
            </div>
        @endif
        <div class="py-2.5 flex flex-wrap items-center justify-between gap-y-2 gap-x-4 min-h-[2.5rem]">
            <div class="topbar-slogan-pill">
                <span class="pulse-dot shrink-0" aria-hidden="true"></span>
                <span class="text-[10px] sm:text-[11px] font-semibold tracking-[0.2em] uppercase text-gold-200/90">
                    {{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}
                </span>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-2.5">
                <a href="{{ $topPhoneHref }}" class="topbar-action">
                    <i class="fas fa-phone-volume text-[10px] text-gold-400/90"></i>
                    <span>{{ $topPhoneDisplay }}</span>
                </a>
                <button type="button" onclick="openContactModal()" class="topbar-action cursor-pointer">
                    <i class="fas fa-message text-[10px] text-gold-400/90"></i>
                    Contact
                </button>
                @if($infoGuide)
                <a href="{{ route('information.show', $infoGuide) }}" class="topbar-action">
                    <i class="fas fa-book-open text-[10px] text-gold-400/90"></i>
                    Guide
                </a>
                @endif
                @if($infoFaq)
                <a href="{{ route('information.show', $infoFaq) }}" class="topbar-action">
                    <i class="fas fa-circle-question text-[10px] text-gold-400/90"></i>
                    FAQ
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@if(session('contact_success') || session('contact_error'))
    <div id="home-contact-flash-mobile" class="md:hidden bg-dark-800 border-b border-white/5 text-center text-[11px] py-2 px-4 font-medium {{ session('contact_success') ? 'text-emerald-400/90' : 'text-red-400/90' }}">
        {{ session('contact_success') ?? session('contact_error') }}
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════════ --}}
<header id="main-header"
    class="main-header-ultra font-plus fixed top-0 left-0 right-0 z-50 transition-all duration-500 ease-out"
    style="top: 0"
    x-data>
    <div class="header-shell max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[4.25rem] md:h-[5.25rem] flex items-center justify-between gap-4 md:gap-8">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-3 sm:gap-3.5 shrink-0 group">
            @if(!empty($siteBrand['logo_url']))
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-12 h-12 md:w-[4rem] md:h-[4rem] flex items-center justify-center">
                        <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain p-0.5">
                    </div>
                </div>
            @else
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-12 h-12 md:w-[4rem] md:h-[4rem] flex items-center justify-center bg-linear-to-br from-gold-400 to-gold-600">
                        <i class="fas fa-gem text-dark-900 text-sm md:text-base drop-shadow-sm"></i>
                    </div>
                </div>
            @endif
            <div class="hidden sm:block min-w-0">
                <p class="text-transparent bg-clip-text bg-linear-to-r from-gold-200 via-gold-400 to-amber-200 font-serif font-bold text-base md:text-lg leading-tight tracking-tight truncate group-hover:from-gold-100 group-hover:to-gold-300 transition-all duration-300">
                    {{ $siteBrand['site_name'] }}
                </p>
                <p class="text-gray-500 group-hover:text-gray-400 text-[9px] md:text-[10px] tracking-[0.22em] uppercase truncate font-plus font-medium mt-0.5 transition-colors">
                    {{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}
                </p>
            </div>
        </a>

        {{-- Nav desktop --}}
        <nav class="hidden lg:flex items-center gap-0.5 xl:gap-1">
            @php $navItems = [
                ['label' => 'Magazine Premium',     'href' => route('home')],
                ['label' => 'Articles',             'href' => route('articles.index')],
                ['label' => 'Découvertes',          'href' => route('discoveries.index')],
                ['label' => 'Annuaire Prestataires','href' => route('providers.index')],
                ['label' => 'Événements',           'href' => route('events.index')],
            ]; @endphp
            @foreach($navItems as $item)
            <a href="{{ $item['href'] }}"
               class="nav-pill nav-pill-glow whitespace-nowrap">
                {{ $item['label'] }}
            </a>
            @endforeach
            <a href="{{ route('gallery.public') }}"
               class="nav-pill nav-pill-glow whitespace-nowrap">
                Galerie Tresors d'Ivoire
            </a>
        </nav>

        {{-- Right actions --}}
        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <div class="lang-switch-ultra hidden sm:inline-flex">
                <a href="{{ route('lang.switch', 'fr') }}"
                   class="{{ session('locale', app()->getLocale()) === 'fr' ? 'bg-gold-500 text-dark-900 shadow-sm' : 'text-gray-400 hover:text-white' }}">FR</a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="{{ session('locale', app()->getLocale()) === 'en' ? 'bg-gold-500 text-dark-900 shadow-sm' : 'text-gray-400 hover:text-white' }}">EN</a>
            </div>

            @auth
            <a href="{{ route('dashboard') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gold-200 btn-ghost-header">
                <i class="fas fa-gauge-high text-xs opacity-80"></i>
                <span>Dashboard</span>
            </a>
            @else
            <a href="{{ route('login') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-200 btn-ghost-header">
                Connexion
            </a>
            <a href="{{ route('plans.public') }}"
               class="inline-flex items-center gap-1.5 px-3.5 sm:px-5 py-2 text-dark-900 text-xs sm:text-sm font-bold btn-gold-header">
                <i class="fas fa-star text-[10px] sm:text-xs opacity-90 hidden sm:inline"></i>
                <span>S’abonner</span>
            </a>
            @endauth

            <button id="menu-toggle" type="button" onclick="document.getElementById('mobile-menu').classList.toggle('open')"
                class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-gray-200 hover:text-white hover:border-gold-500/30 hover:bg-gold-500/5 transition">
                <i class="fas fa-bars-staggered text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu" class="mobile-menu-ultra lg:hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 space-y-1.5 font-plus">
            @foreach($navItems as $item)
            <a href="{{ $item['href'] }}" class="block px-4 py-3 text-gray-200 font-medium text-sm tracking-wide">
                {{ $item['label'] }}
            </a>
            @endforeach
            <a href="{{ route('gallery.public') }}"
               onclick="document.getElementById('mobile-menu')?.classList.remove('open')"
               class="w-full text-left block px-4 py-3 text-gray-200 font-medium text-sm tracking-wide">
                Galerie Tresors d'Ivoire
            </a>
            <div class="pt-4 mt-2 border-t border-white/10 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="text-center py-3 text-sm font-semibold text-gray-200 btn-ghost-header">Connexion</a>
                <a href="{{ route('plans.public') }}" class="text-center py-3 text-sm font-bold text-dark-900 btn-gold-header">S’abonner</a>
            </div>
        </div>
    </div>
</header>

{{-- Modal formulaire de contact (lien top bar / nav) --}}
<div id="contact-modal" class="hidden fixed inset-0 z-[60] p-4 bg-dark-900/80 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
    <div class="absolute inset-0" onclick="closeContactModal()" aria-hidden="true"></div>
    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl border border-white/10 bg-dark-800 shadow-2xl shadow-black/40">
        <div class="sticky top-0 flex items-center justify-between gap-4 px-5 py-4 border-b border-white/10 bg-dark-800/95 backdrop-blur">
            <div>
                <p id="contact-modal-title" class="font-serif text-lg text-white font-semibold flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-gold-400 text-sm"></i>
                    Envoyez-nous un message
                </p>
                <p class="text-gray-500 text-xs mt-0.5">Nous vous répondrons dans les plus brefs délais.</p>
            </div>
            <button type="button" onclick="closeContactModal()" class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-white/10 text-gray-400 hover:text-white hover:bg-white/5 transition" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="post" action="{{ route('contact.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label for="contact-name" class="block text-xs font-medium text-gray-400 mb-1.5">Nom complet</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-gold-500/50 text-xs"></i>
                    <input id="contact-name" name="name" type="text" required maxlength="255" value="{{ old('name') }}"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-dark-900 border border-white/10 text-sm text-white placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:border-gold-500/50"
                        placeholder="Jean Dupont" autocomplete="name">
                </div>
                @error('name')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-email" class="block text-xs font-medium text-gray-400 mb-1.5">E-mail</label>
                <div class="relative">
                    <i class="fas fa-at absolute left-3 top-1/2 -translate-y-1/2 text-gold-500/50 text-xs"></i>
                    <input id="contact-email" name="email" type="email" required maxlength="255" value="{{ old('email') }}"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-dark-900 border border-white/10 text-sm text-white placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:border-gold-500/50"
                        placeholder="vous@exemple.ci" autocomplete="email">
                </div>
                @error('email')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-subject" class="block text-xs font-medium text-gray-400 mb-1.5">Objet</label>
                <div class="relative">
                    <i class="fas fa-circle-notch absolute left-3 top-1/2 -translate-y-1/2 text-gold-500/50 text-[10px]"></i>
                    <input id="contact-subject" name="subject" type="text" required maxlength="255" value="{{ old('subject') }}"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-dark-900 border border-white/10 text-sm text-white placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:border-gold-500/50"
                        placeholder="Sujet de votre message">
                </div>
                @error('subject')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-message" class="block text-xs font-medium text-gray-400 mb-1.5">Message</label>
                <div class="relative">
                    <i class="fas fa-comment-dots absolute left-3 top-3 text-gold-500/50 text-xs"></i>
                    <textarea id="contact-message" name="message" rows="5" required maxlength="5000"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-dark-900 border border-white/10 text-sm text-white placeholder:text-gray-600 focus:outline-none focus:ring-2 focus:ring-gold-500/40 focus:border-gold-500/50 resize-y min-h-[120px]"
                        placeholder="Comment pouvons-nous vous aider ?">{{ old('message') }}</textarea>
                </div>
                @error('message')<p class="text-red-400 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm transition shadow-lg shadow-gold-500/20">
                <i class="fas fa-paper-plane text-xs"></i>
                Envoyer le message
            </button>
        </form>
    </div>
</div>
@if($errors->hasAny(['name', 'email', 'subject', 'message']))
    <span id="contact-modal-autoopen" class="hidden" aria-hidden="true"></span>
@endif

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
            <div id="hero-bg-carousel" class="hero-viewport pointer-events-none absolute inset-0 z-0 h-full w-full overflow-hidden" aria-hidden="true">
                @foreach($heroSlides as $idx => $slide)
                    @php
                        $desktop = trim((string) $slide->desktop_image_url);
                        $tablet = trim((string) ($slide->tablet_image_url ?: $slide->desktop_image_url));
                        $mobile = trim((string) ($slide->mobile_image_url ?: $slide->tablet_image_url ?: $slide->desktop_image_url));
                        $src = $mobile !== '' ? $mobile : ($tablet !== '' ? $tablet : $desktop);
                    @endphp
                    @if($src !== '')
                        <div class="hero-bg-layer absolute inset-0 bg-black transition-opacity duration-700 ease-out {{ $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0' }}" data-hero-bg-layer="{{ $idx }}">
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
                @endforeach
                <div class="hero-overlay-primary absolute inset-0 z-20 bg-linear-to-r from-black/92 via-black/65 to-black/35 sm:from-black/88 sm:via-black/55 sm:to-black/25"></div>
                <div class="hero-overlay-secondary absolute inset-0 z-20 bg-linear-to-t from-black/80 via-black/15 to-black/50"></div>
            </div>
            @if($heroSlides->count() > 1)
                <div class="hero-bg-controls absolute inset-x-0 bottom-16 sm:bottom-28 z-30 flex items-center justify-center gap-2 sm:gap-3 pointer-events-none px-4">
                    <button type="button" id="hero-bg-prev" class="pointer-events-auto w-9 h-9 rounded-full bg-black/45 border border-white/20 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center" aria-label="Slide précédent">
                        <i class="fas fa-chevron-left text-xs"></i>
                    </button>
                    <div class="flex items-center gap-2 pointer-events-auto" id="hero-bg-dots" role="tablist" aria-label="Choisir un slide">
                        @foreach($heroSlides as $idx => $slide)
                            <button type="button"
                                    class="hero-bg-dot h-2 rounded-full transition-all {{ $idx === 0 ? 'w-6 bg-gold-400' : 'w-2 bg-white/35 hover:bg-white/60' }}"
                                    data-hero-bg-dot="{{ $idx }}"
                                    aria-label="Slide {{ $idx + 1 }}"
                                    aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"></button>
                        @endforeach
                    </div>
                    <button type="button" id="hero-bg-next" class="pointer-events-auto w-9 h-9 rounded-full bg-black/45 border border-white/20 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center" aria-label="Slide suivant">
                        <i class="fas fa-chevron-right text-xs"></i>
                    </button>
                </div>
            @endif
        @endif

        {{-- Contenu éditorial hero --}}
        @php
            $heroArticle = ($hideHomeHeroArticle ?? false)
                ? null
                : ($homeDestinationArticle ?? (($homeArticles ?? collect())->where('is_featured', true)->first() ?? ($homeArticles ?? collect())->first()));
            $heroContributors = $heroArticle
                ? $heroArticle->display_uploaders
                : collect();
        @endphp
        <div class="hero-editorial-content relative z-10 flex w-full flex-1 items-center">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 w-full">
                <div class="hero-editorial-copy max-w-xl pt-28 sm:pt-32 pb-10">
                    @if($heroArticle)
                        <span class="inline-flex items-center gap-1.5 text-gold-400 text-xs uppercase tracking-[.2em] font-elegant mb-4 animate-fade-in">
                            <i class="fas fa-star text-[10px]"></i>
                            {{ $heroArticle->category?->name_fr ?? 'Sélection de la rédaction' }}
                        </span>
                        <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-4 text-white animate-fade-up">
                            {{ $heroArticle->title_fr }}
                        </h1>
                        @if($heroArticle->excerpt_fr)
                        <p class="text-gray-300 font-elegant text-lg font-light leading-relaxed mb-6 line-clamp-2 animate-fade-up">
                            {{ $heroArticle->excerpt_fr }}
                        </p>
                        @endif
                        <div class="flex flex-wrap items-center gap-3 text-xs text-gray-400 mb-8 animate-fade-in">
                            @if($heroArticle->author)
                            <span><i class="fas fa-user-pen mr-1 text-gold-500/60"></i>{{ $heroArticle->author->full_name }}</span>
                            <span class="text-gray-600">·</span>
                            @endif
                            @if($heroArticle->reading_time)
                            <span><i class="fas fa-clock mr-1 text-gold-500/60"></i>{{ $heroArticle->reading_time }} min de lecture</span>
                            <span class="text-gray-600">·</span>
                            @endif
                            <span><i class="fas fa-calendar mr-1 text-gold-500/60"></i>{{ $heroArticle->published_at?->translatedFormat('d M Y') }}</span>
                        </div>
                        @if($heroContributors->isNotEmpty())
                        <div class="mb-6 flex flex-wrap items-center gap-1.5">
                            @foreach($heroContributors->take(4) as $contributor)
                            <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-2.5 py-1 text-[10px] font-medium text-amber-200">
                                {{ $contributor->full_name }}
                            </span>
                            @endforeach
                        </div>
                        @endif
                        <a href="{{ route('articles.show', $heroArticle->slug_fr) }}"
                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm transition-all duration-300 shadow-xl shadow-gold-500/25 hover:shadow-gold-500/40 hover:-translate-y-0.5 animate-fade-up">
                            <i class="fas fa-book-open text-xs"></i>
                            Lire le reportage
                        </a>
                    @elseif(!($hideHomeHeroArticle ?? false))
                        <span class="inline-flex items-center gap-1.5 text-gold-400 text-xs uppercase tracking-[.2em] font-elegant mb-4">
                            <i class="fas fa-star text-[10px]"></i>Magazine Premium
                        </span>
                        <h1 class="font-serif text-3xl sm:text-4xl lg:text-5xl font-bold leading-tight mb-4 text-white">
                            Découvrez la Côte d'Ivoire<br class="hidden sm:block">à travers sa culture
                        </h1>
                        <p class="text-gray-300 font-elegant text-lg font-light leading-relaxed mb-8">
                            Patrimoine, art de vivre, gastronomie et destinations d'exception — le magazine de référence.
                        </p>
                        <a href="{{ route('articles.index') }}"
                           class="inline-flex items-center gap-2 px-6 py-3.5 rounded-xl bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm transition-all duration-300 shadow-xl shadow-gold-500/25 hover:shadow-gold-500/40 hover:-translate-y-0.5">
                            <i class="fas fa-newspaper text-xs"></i>
                            Explorer le magazine
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Categories bar --}}
    <div class="relative z-10 border-t border-white/8 bg-dark-900/60 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center overflow-x-auto gap-0 scrollbar-none py-1">
                <a href="{{ route('articles.index') }}"
                   class="shrink-0 px-4 py-4 text-xs {{ !request('categorie') && request()->routeIs('home') ? 'text-gold-400 border-gold-400/50' : 'text-gray-400 border-transparent' }} hover:text-gold-400 tracking-wider uppercase font-medium transition border-b-2 hover:border-gold-400/50 whitespace-nowrap">
                    Tout voir
                </a>
                @foreach($homeCategories as $cat)
                <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
                   class="shrink-0 px-4 py-4 text-xs text-gray-400 hover:text-gold-400 tracking-wider uppercase font-medium transition border-b-2 border-transparent hover:border-gold-400/50 whitespace-nowrap">
                    {{ $cat->name_fr }}
                    @if($cat->articles_count > 0)
                    <span class="ml-1 text-gray-600">({{ $cat->articles_count }})</span>
                    @endif
                </a>
                @endforeach
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
<section id="articles" class="py-16 sm:py-20 bg-dark-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        {{-- ── En-tête centré style presse ── --}}
        <div class="flex items-center gap-4 mb-10 sm:mb-12 reveal">
            <div class="flex-1 h-px bg-gradient-to-r from-transparent to-gold-500/40"></div>
            <div class="flex items-center gap-3 shrink-0">
                <span class="w-1.5 h-1.5 rounded-full bg-gold-400"></span>
                <span class="font-elegant text-xs sm:text-sm font-bold uppercase tracking-[0.28em] text-gold-300 border border-gold-500/30 px-5 py-2 rounded-full bg-dark-800/80">
                    À la une
                </span>
                <span class="w-1.5 h-1.5 rounded-full bg-gold-400"></span>
            </div>
            <div class="flex-1 h-px bg-gradient-to-l from-transparent to-gold-500/40"></div>
        </div>

        @if(($homeArticles ?? collect())->isNotEmpty())
        @php
            $mainArt   = $homeArticles->first();
            $leftArts  = $homeArticles->slice(1, 2)->values();
            $rightArts = $homeArticles->slice(3)->values();
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
                    <div class="flex items-center gap-1.5 text-[11px] text-gray-500 flex-wrap">
                        @if($art->author)
                        <span class="font-semibold text-gray-400 uppercase">{{ $art->author->full_name }}</span>
                        <span>·</span>
                        @endif
                        <span>{{ $art->published_at?->translatedFormat('d M Y') }}</span>
                    </div>
                    @if($contributors->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1">
                        @foreach($contributors->take(2) as $contributor)
                        <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-medium text-amber-200">
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
                    <p class="text-gray-400 text-sm leading-relaxed line-clamp-3 mb-4 font-elegant text-base">
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
                        <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-2.5 py-1 text-[10px] font-medium text-amber-200">
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
                            <p class="text-gray-600 text-[10px] mt-1">{{ $art->published_at?->translatedFormat('d M Y') }}</p>
                            @if($contributors->isNotEmpty())
                            <div class="mt-1 flex flex-wrap gap-1">
                                @foreach($contributors->take(2) as $contributor)
                                <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-medium text-amber-200">
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
     BANNER : DÉCOUVERTES
══════════════════════════════════════════════════════════ --}}
<section id="decouvertes" class="py-16 sm:py-24 bg-dark-800 relative overflow-hidden">
    <div class="absolute inset-0 opacity-5" style="background-image: repeating-linear-gradient(45deg, #e8a020 0, #e8a020 1px, transparent 0, transparent 50%); background-size: 20px 20px;"></div>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 relative">
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-16 reveal">
            <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Explorer par thème</p>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4">Nos rubriques</h2>
            <p class="text-gray-400 font-elegant text-lg font-light">Plongez dans la richesse et la diversité de la Côte d'Ivoire à travers nos sélections thématiques.</p>
        </div>

        @php
            $defaultCatIcons = ['fa-landmark','fa-palette','fa-leaf','fa-utensils','fa-map-location-dot','fa-gem','fa-camera','fa-music','fa-heart','fa-star'];
            $defaultCatColors = [
                'from-amber-900/40 to-amber-800/10 border-amber-700/20',
                'from-rose-900/30 to-rose-800/10 border-rose-700/20',
                'from-green-900/40 to-green-800/10 border-green-700/20',
                'from-orange-900/30 to-orange-800/10 border-orange-700/20',
                'from-blue-900/30 to-blue-800/10 border-blue-700/20',
                'from-violet-900/30 to-violet-800/10 border-violet-700/20',
                'from-teal-900/30 to-teal-800/10 border-teal-700/20',
                'from-pink-900/30 to-pink-800/10 border-pink-700/20',
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
            @php $colors = ['from-amber-900/40 to-amber-800/10 border-amber-700/20','from-rose-900/30 to-rose-800/10 border-rose-700/20','from-green-900/40 to-green-800/10 border-green-700/20','from-orange-900/30 to-orange-800/10 border-orange-700/20','from-blue-900/30 to-blue-800/10 border-blue-700/20','from-violet-900/30 to-violet-800/10 border-violet-700/20']; $icons = ['fa-landmark','fa-palette','fa-leaf','fa-utensils','fa-map-location-dot','fa-gem']; @endphp
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
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION : ÉVÉNEMENTS
══════════════════════════════════════════════════════════ --}}
<section id="evenements" class="py-16 sm:py-24 bg-dark-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="flex items-end justify-between mb-10 sm:mb-14">
            <div class="reveal">
                <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-2">Agenda culturel</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">Événements à venir</h2>
            </div>
            <a href="{{ route('events.index') }}"
               class="hidden sm:inline-flex items-center gap-2.5 px-5 py-2.5 rounded-xl border border-gold-500/25 bg-dark-800/70 text-base text-gold-300 hover:text-gold-200 hover:border-gold-400/50 hover:bg-dark-700/80 shadow-lg shadow-black/20 hover:shadow-gold-500/10 transition-all duration-300 font-semibold tracking-wide group hover:-translate-y-0.5">
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
            <article class="group bg-amber-900/20 border border-amber-700/20 rounded-2xl overflow-hidden hover:border-gold-500/30 transition-all duration-300 reveal">
                <a href="{{ route('events.show', $ev->slug) }}" class="relative block h-40 bg-dark-700">
                    @if($ev->cover_url)
                        <img src="{{ $ev->cover_url }}" alt="{{ $ev->title_fr }}" class="w-full h-full object-cover">
                    @else
                        <div class="w-full h-full flex items-center justify-center bg-linear-to-br from-dark-700 to-dark-600">
                            <i class="fas fa-calendar-days text-dark-500 text-3xl"></i>
                        </div>
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-black/70 via-black/20 to-transparent"></div>
                    @if($urgencyLabel)
                        <span class="absolute top-3 left-3 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide bg-rose-500/90 text-white">
                            <i class="fas fa-bolt mr-1 text-[9px]"></i>{{ $urgencyLabel }}
                        </span>
                    @endif
                    <span class="absolute top-3 right-3 inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $ev->is_free ? 'bg-emerald-500/90 text-white' : 'bg-amber-500/90 text-dark-900' }}">
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
                        <p class="text-gray-500 text-xs mt-2">
                            <i class="fas fa-location-dot mr-1 text-gold-500/60"></i>{{ $ev->city ?: 'Côte d\'Ivoire' }}
                        </p>

                        <div class="mt-3">
                            @if($ev->ticket_url)
                                <a href="{{ $ev->ticket_url }}" target="_blank" class="inline-flex items-center gap-1.5 bg-gold-500 hover:bg-gold-400 text-dark-900 text-xs font-semibold px-3 py-1.5 rounded-lg transition">
                                    <i class="fas fa-ticket-simple text-[10px]"></i> Réserver
                                </a>
                            @else
                                <a href="{{ route('events.show', $ev->slug) }}" class="inline-flex items-center gap-1.5 bg-white/10 hover:bg-white/15 text-white text-xs font-semibold px-3 py-1.5 rounded-lg transition">
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
     SECTION : ANNUAIRE PRESTATAIRES
══════════════════════════════════════════════════════════ --}}
<section id="annuaire" class="py-16 sm:py-24 bg-dark-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Annuaire des prestataires</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-5 leading-snug">
                    Les meilleures adresses<br>de Côte d'Ivoire
                </h2>
                <p class="text-gray-400 font-elegant text-lg font-light leading-relaxed mb-8">
                    Hôtels, restaurants, guides touristiques, artisans… Découvrez notre sélection premium d'établissements vérifiés et notés par notre équipe.
                </p>
                <div class="flex flex-wrap gap-3 mb-8">
                    @if(($homeProviderCategories ?? collect())->isNotEmpty())
                        @foreach($homeProviderCategories as $pc)
                        <a href="{{ route('providers.index', ['categorie' => $pc->slug]) }}"
                           class="px-3 py-1.5 bg-dark-700 border border-white/8 text-gray-300 text-xs rounded-full hover:border-gold-400/40 hover:text-gold-400 transition">
                            {{ $pc->name_fr }}
                        </a>
                        @endforeach
                    @else
                        @foreach(['Hôtellerie', 'Gastronomie', 'Guides', 'Artisanat', 'Loisirs', 'Bien-être'] as $c)
                        <a href="{{ route('providers.index') }}"
                           class="px-3 py-1.5 bg-dark-700 border border-white/8 text-gray-300 text-xs rounded-full hover:border-gold-400/40 hover:text-gold-400 transition">
                            {{ $c }}
                        </a>
                        @endforeach
                    @endif
                </div>
                <a href="{{ route('providers.index') }}"
                   class="inline-flex items-center gap-2 px-6 py-3 bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm rounded-xl transition shadow-lg shadow-gold-500/20">
                    <i class="fas fa-search"></i> Explorer l'annuaire
                </a>
            </div>
            <div class="grid grid-cols-2 gap-4 reveal">
                @forelse(($homeProviders ?? collect()) as $p)
                <a href="{{ route('providers.show', $p->slug) }}" class="bg-dark-700/50 border border-white/8 hover:border-gold-500/30 rounded-xl p-4 transition-all duration-300 group block">
                    <div class="flex items-start justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-dark-600 flex items-center justify-center group-hover:bg-gold-500/10 transition">
                            <i class="fas fa-store text-gold-400/60 group-hover:text-gold-400 transition"></i>
                        </div>
                        @if($p->is_verified)
                        <span class="bg-gold-500/15 text-gold-400 text-[10px] font-bold px-2 py-0.5 rounded-full border border-gold-500/30">
                            <i class="fas fa-check-circle mr-0.5 text-[9px]"></i>Vérifié
                        </span>
                        @endif
                    </div>
                    <p class="text-white text-sm font-semibold font-serif">{{ $p->name }}</p>
                    <p class="text-gray-500 text-xs">{{ $p->category->name_fr ?? 'Prestataire' }}</p>
                    <div class="flex items-center gap-1 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                        <i class="fas fa-star text-[10px] {{ $i <= round((float) ($p->rating_avg ?? 0)) ? 'text-gold-400' : 'text-dark-500' }}"></i>
                        @endfor
                        <span class="text-gray-500 text-xs ml-1">{{ number_format((float) ($p->rating_avg ?? 0), 1) }} ({{ (int) ($p->rating_count ?? 0) }})</span>
                    </div>
                </a>
                @empty
                <div class="col-span-2 text-gray-500 text-sm">Aucun prestataire actif pour le moment.</div>
                @endforelse
            </div>
        </div>
    </div>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION : PARTENAIRES
══════════════════════════════════════════════════════════ --}}
<section id="partenaires" class="py-16 sm:py-24 bg-dark-900 border-t border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">
        <div class="text-center max-w-2xl mx-auto mb-12 sm:mb-14 reveal">
            <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-3">Ils nous font confiance</p>
            <h2 class="font-serif text-3xl sm:text-4xl font-bold mb-4 leading-snug text-white">
                Nos partenaires
            </h2>
            <p class="text-gray-400 font-elegant text-lg font-light leading-relaxed">
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

{{-- ══════════════════════════════════════════════════════════
     FOOTER
══════════════════════════════════════════════════════════ --}}
<footer class="footer-ultra border-t border-white/[0.07]">
    @php
        $c = $siteBrand['contact'] ?? [];
        $s = $siteBrand['social'] ?? [];
        $socialRows = array_values(array_filter([
            ['url' => $s['facebook_url'] ?? null, 'icon' => 'fa-facebook-f', 'label' => 'Facebook'],
            ['url' => $s['instagram_url'] ?? null, 'icon' => 'fa-instagram', 'label' => 'Instagram'],
            ['url' => $s['twitter_url'] ?? null, 'icon' => 'fa-twitter', 'label' => 'Twitter / X'],
            ['url' => $s['linkedin_url'] ?? null, 'icon' => 'fa-linkedin-in', 'label' => 'LinkedIn'],
            ['url' => $s['youtube_url'] ?? null, 'icon' => 'fa-youtube', 'label' => 'YouTube'],
        ], fn ($row) => !empty($row['url'])));
        $waHref = \App\Models\SiteSetting::whatsappHref($s['whatsapp_phone'] ?? null);
        $mapHref = (!empty($c['latitude']) && !empty($c['longitude']))
            ? 'https://www.google.com/maps?q='.urlencode($c['latitude'].','.$c['longitude'])
            : null;
        $footerBlurb = !empty($siteBrand['site_description'])
            ? \Illuminate\Support\Str::limit(strip_tags($siteBrand['site_description']), 220)
            : 'Le magazine de référence pour explorer la culture, l\'art de vivre et le tourisme en Côte d\'Ivoire.';
    @endphp
    <div class="footer-ultra-inner max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        {{-- Newsletter : variante bento (deux panneaux + accent) --}}
        <div id="newsletter-footer" class="mb-12 sm:mb-14 scroll-mt-28">
            <div class="overflow-hidden rounded-2xl border border-white/[0.09] bg-[#080706] shadow-2xl shadow-black/50 reveal">
                <div class="grid lg:grid-cols-2 divide-y lg:divide-y-0 lg:divide-x divide-white/10">
                    <div class="relative p-6 sm:p-8 lg:p-10">
                        <div class="absolute left-0 top-8 bottom-8 w-1 rounded-full bg-gradient-to-b from-emerald-400/70 via-amber-400/50 to-amber-600/30 pointer-events-none" aria-hidden="true"></div>
                        <div class="pl-5 sm:pl-6">
                            <p class="text-[10px] font-plus font-bold uppercase tracking-[0.28em] text-emerald-400/85 mb-4">Inscription</p>
                            <h2 class="font-serif text-2xl sm:text-3xl font-semibold text-white mb-3 leading-tight tracking-tight">
                                Ne manquez rien de l’Ivoire
                            </h2>
                            <p class="text-gray-500 text-sm sm:text-[0.9375rem] font-plus leading-relaxed max-w-md">
                                Articles, adresses et événements sélectionnés pour vous, directement dans votre boîte mail.
                            </p>
                        </div>
                    </div>
                    <div class="p-6 sm:p-8 lg:p-10 bg-white/[0.02] flex flex-col justify-center">
                        @if (session('newsletter_success'))
                            <div class="rounded-lg border border-emerald-500/35 bg-emerald-500/[0.08] px-4 py-3 text-sm text-emerald-100 mb-4 font-plus">
                                {{ session('newsletter_success') }}
                            </div>
                        @endif
                        @if (session('newsletter_info'))
                            <div class="rounded-lg border border-amber-500/35 bg-amber-500/[0.08] px-4 py-3 text-sm text-amber-50 mb-4 font-plus">
                                {{ session('newsletter_info') }}
                            </div>
                        @endif
                        @if (session('newsletter_error'))
                            <div class="rounded-lg border border-rose-500/35 bg-rose-500/[0.08] px-4 py-3 text-sm text-rose-100 mb-4 font-plus">
                                {{ session('newsletter_error') }}
                            </div>
                        @endif
                        <form method="post" action="{{ route('newsletter.subscribe') }}" class="space-y-3">
                            @csrf
                            <label for="newsletter-email" class="sr-only">Adresse e-mail</label>
                            <input type="email" name="newsletter_email" id="newsletter-email" required maxlength="255"
                                   value="{{ old('newsletter_email') }}"
                                   placeholder="votre@email.com"
                                   autocomplete="email"
                                   class="w-full rounded-lg border border-white/12 bg-black/50 px-4 py-3.5 text-sm text-white placeholder:text-gray-600 outline-none transition focus:border-emerald-400/40 focus:ring-1 focus:ring-emerald-500/25 font-plus">
                            <button type="submit"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg bg-white px-6 py-3.5 text-sm font-bold text-black hover:bg-gray-100 transition font-plus">
                                <i class="fas fa-arrow-right text-xs"></i>
                                S’abonner
                            </button>
                        </form>
                        @error('newsletter_email')
                            <p class="text-rose-400 text-xs mt-2 font-plus">{{ $message }}</p>
                        @enderror
                        <p class="text-gray-600 text-[11px] mt-4 font-plus leading-relaxed">
                            Pas de spam — désinscription en un clic à tout moment.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Colonnes : séparateurs verticaux, style magazine --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-10 lg:gap-0 mb-12 pt-2 border-t border-white/5">

            <div class="lg:col-span-3 lg:pr-8 lg:border-r border-white/5">
                <div class="flex items-start gap-3 mb-4">
                    @if(!empty($siteBrand['logo_url']))
                        <div class="h-10 w-10 rounded-lg border border-white/10 bg-white/[0.04] flex items-center justify-center overflow-hidden p-0.5 shrink-0">
                            <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                        </div>
                    @else
                        <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-emerald-400 to-amber-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-gem text-black text-sm"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-white font-serif font-semibold leading-tight truncate">{{ $siteBrand['site_name'] }}</p>
                        <p class="text-gray-600 text-[10px] tracking-[0.16em] uppercase truncate font-plus mt-1">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
                    </div>
                </div>
                <p class="text-gray-500 text-xs leading-relaxed mb-5 font-plus">
                    {{ $footerBlurb }}
                </p>
                @if(count($socialRows) > 0 || $waHref)
                    <p class="text-gray-600 text-[9px] uppercase tracking-[0.18em] mb-2.5 font-plus font-semibold">Réseaux</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($socialRows as $row)
                            <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $row['label'] }}"
                               class="h-8 w-8 rounded-md border border-white/10 bg-white/[0.03] flex items-center justify-center text-gray-500 hover:text-white hover:border-white/25 hover:bg-white/[0.06] transition text-xs">
                                <i class="fab {{ $row['icon'] }}"></i>
                            </a>
                        @endforeach
                        @if($waHref)
                            <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" title="WhatsApp"
                               class="h-8 w-8 rounded-md border border-emerald-500/25 bg-emerald-500/[0.07] flex items-center justify-center text-emerald-400/90 hover:text-emerald-300 transition text-xs">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            <div class="lg:col-span-3 lg:px-8 lg:border-r border-white/5">
                <h4 class="text-white font-plus text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-400/90 mb-4">Magazine</h4>
                <ul class="space-y-0.5 font-plus">
                    <li><a href="{{ route('articles.index') }}" class="footer-v2-link">Tous les articles</a></li>
                    <li><a href="{{ route('discoveries.index') }}" class="footer-v2-link">Découvertes</a></li>
                    <li><a href="{{ route('events.index') }}" class="footer-v2-link">Événements</a></li>
                    @foreach($homeCategories->take(3) as $cat)
                    <li><a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}" class="footer-v2-link">{{ $cat->name_fr }}</a></li>
                    @endforeach
                </ul>
            </div>

            <div class="lg:col-span-3 lg:px-8 lg:border-r border-white/5">
                <h4 class="text-white font-plus text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-400/90 mb-4">Annuaire</h4>
                <ul class="space-y-0.5 font-plus">
                    <li><a href="{{ route('providers.index') }}" class="footer-v2-link">Tous les prestataires</a></li>
                    @foreach($homeProviderCategories->take(4) as $pc)
                    <li><a href="{{ route('providers.index', ['categorie' => $pc->slug]) }}" class="footer-v2-link">{{ $pc->name_fr }}</a></li>
                    @endforeach
                    <li><a href="{{ route('register') }}" class="footer-v2-link">Devenir prestataire</a></li>
                </ul>
            </div>

            <div class="lg:col-span-3 lg:pl-8">
                <h4 class="text-white font-plus text-[11px] font-bold uppercase tracking-[0.2em] text-emerald-400/90 mb-4">Contact</h4>
                <ul class="space-y-0.5 font-plus mb-5">
                    @forelse($infoPages as $infoPage)
                    <li><a href="{{ route('information.show', $infoPage) }}" class="footer-v2-link">{{ $infoPage->title_fr }}</a></li>
                    @empty
                    <li class="text-gray-600 italic text-xs py-1">Pages d’information à configurer.</li>
                    @endforelse
                </ul>
                <div class="space-y-2.5 text-[11px] text-gray-500 font-plus border-t border-white/5 pt-4">
                    @if(!empty($c['phone_1']))
                        <p><span class="text-gray-600">Tél.</span> <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_1']) }}" class="text-gray-300 hover:text-amber-300 transition break-all">{{ $c['phone_1'] }}</a></p>
                    @endif
                    @if(!empty($c['phone_2']))
                        <p><span class="text-gray-600">Tél. 2</span> <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_2']) }}" class="text-gray-300 hover:text-amber-300 transition break-all">{{ $c['phone_2'] }}</a></p>
                    @endif
                    @if(!empty($c['email_primary']))
                        <p><a href="mailto:{{ $c['email_primary'] }}" class="text-gray-300 hover:text-amber-300 transition break-all">{{ $c['email_primary'] }}</a></p>
                    @endif
                    @if(!empty($c['email_secondary']))
                        <p><a href="mailto:{{ $c['email_secondary'] }}" class="text-gray-300 hover:text-amber-300 transition break-all">{{ $c['email_secondary'] }}</a></p>
                    @endif
                    @if(!empty($c['contact_form_email']))
                        <p><span class="text-gray-600">Formulaire</span> <a href="mailto:{{ $c['contact_form_email'] }}" class="text-gray-300 hover:text-amber-300 transition break-all">{{ $c['contact_form_email'] }}</a></p>
                    @endif
                    @if(!empty($c['address']))
                        <p class="text-gray-400 leading-snug">
                            @if($mapHref)
                                <a href="{{ $mapHref }}" target="_blank" rel="noopener noreferrer" class="hover:text-amber-300 transition">{{ $c['address'] }}</a>
                            @else
                                {{ $c['address'] }}
                            @endif
                        </p>
                    @endif
                    @if(!empty($c['opening_hours']))
                        <p class="whitespace-pre-line text-gray-500 leading-relaxed">{{ $c['opening_hours'] }}</p>
                    @endif
                    @if(empty($c['phone_1']) && empty($c['phone_2']) && empty($c['email_primary']) && empty($c['email_secondary']) && empty($c['contact_form_email']) && empty($c['address']) && empty($c['opening_hours']))
                        <p class="text-gray-600 italic text-xs">Coordonnées à renseigner dans l’administration.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-white/10">
            <p class="text-[11px] text-gray-600 font-plus tracking-wide text-center sm:text-left">&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés</p>
            <div class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-[11px] font-plus">
                @if($infoLegal)
                <a href="{{ route('information.show', $infoLegal) }}" class="text-gray-500 hover:text-white transition underline-offset-4 hover:underline">Mentions légales</a>
                @endif
                @if($infoGuide)
                <a href="{{ route('information.show', $infoGuide) }}" class="text-gray-500 hover:text-white transition underline-offset-4 hover:underline">CGU</a>
                @endif
                <a href="{{ route('login') }}" class="text-amber-400/90 hover:text-amber-300 font-semibold transition">
                    Espace membres →
                </a>
            </div>
        </div>
    </div>
</footer>

{{-- ══════════════════════════════════════════════════════════
     SCRIPTS
══════════════════════════════════════════════════════════ --}}
<script>
    function openContactModal() {
        const el = document.getElementById('contact-modal');
        if (!el) return;
        el.classList.remove('hidden');
        el.classList.add('flex', 'items-center', 'justify-center');
        document.body.style.overflow = 'hidden';
    }

    function closeContactModal() {
        const el = document.getElementById('contact-modal');
        if (!el) return;
        el.classList.add('hidden');
        el.classList.remove('flex', 'items-center', 'justify-center');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            closeContactModal();
        }
    });

    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('contact-modal-autoopen')) {
            openContactModal();
        }
    });

    // Header scroll effect
    const header  = document.getElementById('main-header');
    function topBarHeight() {
        const topBar = document.getElementById('home-top-bar');
        if (topBar && topBar.offsetParent !== null) {
            return topBar.offsetHeight;
        }
        const flash = document.getElementById('home-contact-flash-mobile');

        return flash ? flash.offsetHeight : 0;
    }

    function syncHeaderTop() {
        if (!header) return;
        const scrolled = window.scrollY > 80;
        if (scrolled) {
            header.classList.add('header-scrolled');
            header.style.top = '0';
        } else {
            header.classList.remove('header-scrolled');
            header.style.top = topBarHeight() + 'px';
        }
    }

    window.addEventListener('scroll', syncHeaderTop);
    window.addEventListener('load', syncHeaderTop);
    syncHeaderTop();

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

    // Hero background carousel
    (function () {
        const layers = document.querySelectorAll('[data-hero-bg-layer]');
        if (!layers.length || layers.length < 2) return;
        const dots = document.querySelectorAll('[data-hero-bg-dot]');
        const prevBtn = document.getElementById('hero-bg-prev');
        const nextBtn = document.getElementById('hero-bg-next');

        let index = 0;
        let timer = null;

        function render() {
            layers.forEach((el, i) => {
                const active = i === index;
                el.classList.toggle('opacity-100', active);
                el.classList.toggle('z-10', active);
                el.classList.toggle('opacity-0', !active);
                el.classList.toggle('z-0', !active);
            });
            dots.forEach((dot, i) => {
                const active = i === index;
                dot.classList.toggle('w-6', active);
                dot.classList.toggle('bg-gold-400', active);
                dot.classList.toggle('w-2', !active);
                dot.classList.toggle('bg-white/35', !active);
                dot.classList.toggle('hover:bg-white/60', !active);
                dot.setAttribute('aria-selected', active ? 'true' : 'false');
            });
        }

        function next() {
            index = (index + 1) % layers.length;
            render();
        }
        function prev() {
            index = (index - 1 + layers.length) % layers.length;
            render();
        }

        function start() {
            stop();
            timer = setInterval(next, 7000);
        }

        function stop() {
            if (timer) {
                clearInterval(timer);
                timer = null;
            }
        }

        render();
        start();

        const root = document.getElementById('hero-bg-carousel');
        if (root) {
            root.addEventListener('mouseenter', stop);
            root.addEventListener('mouseleave', start);
        }
        if (prevBtn) prevBtn.addEventListener('click', () => { prev(); start(); });
        if (nextBtn) nextBtn.addEventListener('click', () => { next(); start(); });
        dots.forEach((dot, i) => dot.addEventListener('click', () => {
            index = i;
            render();
            start();
        }));

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                stop();
            } else {
                start();
            }
        });
    })();

</script>

</body>
</html>
