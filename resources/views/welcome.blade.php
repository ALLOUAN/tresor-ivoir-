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
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;0,800;1,400;1,600&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@300;400;500;600&display=swap" rel="stylesheet">
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

        /* ── Scrollbar ───────────────────────────────────────── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #0d0d0b; }
        ::-webkit-scrollbar-thumb { background: #e8a020; border-radius: 3px; }

        /* ── Header scroll effect ────────────────────────────── */
        .header-scrolled {
            background: rgba(13,13,11,.97) !important;
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(232,160,32,.15) !important;
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

    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">
@php
    $topContact = $siteBrand['contact'] ?? [];
    $topPhoneDisplay = !empty($topContact['phone_1']) ? $topContact['phone_1'] : '+225 27 22 48 36 90';
    $topPhoneHref = 'tel:'.preg_replace('/[^\d+]/', '', $topPhoneDisplay);
@endphp

{{-- ══════════════════════════════════════════════════════════
     TOP BAR
══════════════════════════════════════════════════════════ --}}
<div id="home-top-bar" class="bg-dark-800 border-b border-white/5 text-xs text-gray-400 hidden md:block">
    <div class="max-w-7xl mx-auto px-6">
        @if(session('contact_success') || session('contact_error'))
            <div class="py-2 text-center text-[11px] font-medium border-b border-white/5 {{ session('contact_success') ? 'text-emerald-400/90' : 'text-red-400/90' }}">
                {{ session('contact_success') ?? session('contact_error') }}
            </div>
        @endif
        <div class="py-2 flex items-center justify-between">
        <span class="tracking-widest uppercase font-elegant text-gold-400/70 text-[11px]">
            {{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}
        </span>
        <div class="flex items-center gap-6">
            <a href="{{ $topPhoneHref }}" class="flex items-center gap-1.5 hover:text-gold-400 transition">
                <i class="fas fa-phone text-gold-500/60 text-[10px]"></i>
                {{ $topPhoneDisplay }}
            </a>
            <button type="button" onclick="openContactModal()" class="hover:text-gold-400 transition text-left">Contact</button>
            <a href="#" class="hover:text-gold-400 transition">Guide d'utilisation</a>
            <a href="#" class="hover:text-gold-400 transition">FAQ</a>
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
    class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 border-b border-transparent"
    style="top: 0"
    x-data>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 md:h-20 flex items-center justify-between gap-6">

        {{-- Logo --}}
        <a href="/" class="flex items-center gap-3 shrink-0">
            @if(!empty($siteBrand['logo_url']))
                <div class="w-9 h-9 md:w-11 md:h-11 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center shadow-lg overflow-hidden p-0.5">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="w-9 h-9 md:w-11 md:h-11 rounded-lg bg-gold-500 flex items-center justify-center shadow-lg">
                    <i class="fas fa-gem text-dark-900 text-sm md:text-base"></i>
                </div>
            @endif
            <div class="hidden sm:block min-w-0">
                <p class="text-gold-400 font-serif font-bold text-base md:text-lg leading-tight tracking-wide truncate">{{ $siteBrand['site_name'] }}</p>
                <p class="text-gray-500 text-[10px] tracking-widest uppercase truncate">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
            </div>
        </a>

        {{-- Nav desktop --}}
        <nav class="hidden lg:flex items-center gap-1">
            @php $navItems = [
                ['label' => 'Accueil',              'href' => '/'],
                ['label' => 'Articles',             'href' => '#articles'],
                ['label' => 'Découvertes',          'href' => '#decouvertes'],
                ['label' => 'Annuaire Prestataires','href' => route('providers.index')],
                ['label' => 'Événements',           'href' => '#evenements'],
            ]; @endphp
            @foreach($navItems as $item)
            <a href="{{ $item['href'] }}"
               class="px-3 xl:px-4 py-2 text-sm text-gray-300 hover:text-gold-400 transition font-medium tracking-wide relative group">
                {{ $item['label'] }}
                <span class="absolute bottom-0 left-1/2 -translate-x-1/2 h-px w-0 bg-gold-400 group-hover:w-4/5 transition-all duration-300"></span>
            </a>
            @endforeach
            <button type="button" onclick="openContactModal()"
                class="px-3 xl:px-4 py-2 text-sm text-gray-300 hover:text-gold-400 transition font-medium tracking-wide relative group">
                Contact
                <span class="absolute bottom-0 left-1/2 -translate-x-1/2 h-px w-0 bg-gold-400 group-hover:w-4/5 transition-all duration-300"></span>
            </button>
        </nav>

        {{-- Right actions --}}
        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            {{-- Language --}}
            <div class="hidden sm:flex items-center rounded-full border border-white/10 overflow-hidden text-xs font-semibold">
                <button class="px-3 py-1.5 bg-gold-500 text-dark-900">FR</button>
                <button class="px-3 py-1.5 text-gray-400 hover:text-white transition">EN</button>
            </div>

            @auth
            <a href="{{ route('dashboard') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 border border-gold-500/40 hover:border-gold-400 text-gold-400 text-sm font-medium rounded-lg transition">
                <i class="fas fa-gauge-high text-xs"></i> Dashboard
            </a>
            @else
            <a href="{{ route('login') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 border border-white/15 hover:border-gold-400/40 text-gray-300 hover:text-white text-sm font-medium rounded-lg transition">
                Connexion
            </a>
            <a href="#"
               class="inline-flex items-center gap-1.5 px-3 sm:px-5 py-2 bg-gold-500 hover:bg-gold-400 text-dark-900 text-sm font-semibold rounded-lg transition shadow-lg shadow-gold-500/20">
                <i class="fas fa-gem text-xs hidden sm:inline"></i> S'abonner
            </a>
            @endauth

            {{-- Mobile hamburger --}}
            <button id="menu-toggle" onclick="document.getElementById('mobile-menu').classList.toggle('open')"
                class="lg:hidden w-9 h-9 flex items-center justify-center border border-white/10 rounded-lg text-gray-300 hover:text-white transition">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="mobile-menu" class="lg:hidden bg-dark-800/98 backdrop-blur border-t border-white/5">
        <div class="px-4 py-4 space-y-1">
            @foreach($navItems as $item)
            <a href="{{ $item['href'] }}" class="block px-4 py-2.5 text-gray-300 hover:text-gold-400 hover:bg-white/5 rounded-lg text-sm transition">
                {{ $item['label'] }}
            </a>
            @endforeach
            <button type="button" onclick="openContactModal(); document.getElementById('mobile-menu').classList.remove('open')"
                class="w-full text-left block px-4 py-2.5 text-gray-300 hover:text-gold-400 hover:bg-white/5 rounded-lg text-sm transition">
                Contact
            </button>
            <div class="pt-3 border-t border-white/5 flex gap-2">
                <a href="{{ route('login') }}" class="flex-1 text-center py-2 border border-white/15 text-gray-300 text-sm rounded-lg">Connexion</a>
                <a href="#" class="flex-1 text-center py-2 bg-gold-500 text-dark-900 text-sm font-semibold rounded-lg">S'abonner</a>
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
<section class="hero-bg-fallback relative min-h-screen flex flex-col" id="hero">

    {{-- Decorative orbs --}}
    <div class="absolute top-1/4 right-1/4 w-96 h-96 bg-gold-500/5 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute bottom-1/3 left-1/6 w-64 h-64 bg-green-900/10 rounded-full blur-3xl pointer-events-none"></div>

    {{-- Content : accroche + carrousel slides (images responsives) --}}
    <div class="flex-1 flex items-center relative z-10 pt-32 md:pt-36 pb-24 md:pb-32">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 w-full">
            <div class="@if($heroSlides->isNotEmpty()) grid grid-cols-1 lg:grid-cols-12 gap-10 lg:gap-12 xl:gap-16 items-center @endif">
                <div class="@if($heroSlides->isNotEmpty()) lg:col-span-5 @else max-w-2xl xl:max-w-3xl @endif">

                    {{-- Brand tag --}}
                    <div class="inline-flex items-center gap-2 mb-5 animate-fade-in">
                        <div class="h-px w-8 bg-gold-400"></div>
                        <span class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant">{{ $siteBrand['site_name'] }}</span>
                    </div>

                    {{-- Main title --}}
                    <h1 class="font-serif text-4xl sm:text-5xl md:text-6xl xl:text-7xl font-bold leading-[1.1] mb-6 animate-fade-up" style="animation-delay:.1s">
                        Découvrez la magie<br>
                        <span class="text-transparent bg-clip-text bg-linear-to-r from-gold-400 via-gold-300 to-gold-500">
                            de la Côte d'Ivoire
                        </span>
                    </h1>

                    {{-- Subtitle --}}
                    <p class="font-elegant text-gray-300 text-lg sm:text-xl md:text-2xl leading-relaxed mb-8 max-w-xl animate-fade-up" style="animation-delay:.2s; font-weight:300">
                        Grand reportage, adresses choisies et récits d'exception pour explorer les paysages, les cultures et l'art de vivre ivoiriens.
                    </p>

                    {{-- CTAs --}}
                    <div class="flex flex-wrap items-center gap-3 sm:gap-4 animate-fade-up" style="animation-delay:.3s">
                        <a href="#articles"
                           class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold text-sm sm:text-base rounded-xl transition-all duration-300 shadow-xl shadow-gold-500/25 hover:shadow-gold-400/30 hover:-translate-y-0.5">
                            <i class="fas fa-book-open"></i>
                            Explorer le magazine
                        </a>
                        <a href="#decouvertes"
                           class="inline-flex items-center gap-2 px-6 sm:px-8 py-3 sm:py-4 border border-white/20 hover:border-gold-400/50 text-white hover:text-gold-300 font-semibold text-sm sm:text-base rounded-xl transition-all duration-300 backdrop-blur-sm bg-white/5 hover:bg-white/8">
                            <i class="fas fa-compass text-sm"></i>
                            Découvrir
                        </a>
                    </div>

                    {{-- Stats --}}
                    <div class="flex flex-wrap gap-6 sm:gap-10 mt-12 sm:mt-16 pt-8 border-t border-white/8 animate-fade-up" style="animation-delay:.4s">
                        @php $stats = [
                            ['n' => '350+',  'label' => 'Articles & reportages'],
                            ['n' => '120+',  'label' => 'Prestataires référencés'],
                            ['n' => '18',    'label' => 'Régions couvertes'],
                            ['n' => '45K+',  'label' => 'Lecteurs mensuels'],
                        ]; @endphp
                        @foreach($stats as $s)
                        <div>
                            <p class="font-serif text-2xl sm:text-3xl font-bold text-gold-400">{{ $s['n'] }}</p>
                            <p class="text-gray-500 text-xs mt-0.5 tracking-wide">{{ $s['label'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                @if($heroSlides->isNotEmpty())
                    <div class="lg:col-span-7 w-full max-w-xl mx-auto lg:max-w-none animate-fade-up" style="animation-delay:.25s">
                        <p class="text-gold-400/90 text-[10px] tracking-[.2em] uppercase font-elegant mb-3 text-center lg:text-left">
                            <i class="fas fa-images mr-1.5 opacity-70"></i> Images responsives
                        </p>
                        <div id="hero-slides-carousel" class="relative rounded-2xl overflow-hidden border border-white/10 shadow-2xl shadow-black/40 bg-dark-800 aspect-[16/10] max-h-[min(380px,42vh)] sm:max-h-[min(440px,48vh)] lg:max-h-[min(520px,58vh)]">
                            @foreach($heroSlides as $idx => $slide)
                                @php
                                    $tabletSrc = $slide->tablet_image_url ?: $slide->desktop_image_url;
                                    $mobileSrc = $slide->mobile_image_url ?: $tabletSrc;
                                @endphp
                                <div class="hero-slide-layer absolute inset-0 transition-opacity duration-700 ease-out {{ $idx === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0 pointer-events-none' }}"
                                     data-hero-slide="{{ $idx }}"
                                     aria-hidden="{{ $idx === 0 ? 'false' : 'true' }}">
                                    <picture class="block w-full h-full">
                                        <source media="(min-width: 1024px)" srcset="{{ $slide->desktop_image_url }}">
                                        <source media="(min-width: 640px)" srcset="{{ $tabletSrc }}">
                                        <img src="{{ $mobileSrc }}" alt="{{ $slide->title }}"
                                             class="w-full h-full object-cover"
                                             @if($idx > 0) loading="lazy" @endif>
                                    </picture>
                                    <div class="absolute inset-0 bg-linear-to-t from-black/85 via-black/20 to-transparent pointer-events-none"></div>
                                    @if($slide->title || $slide->subtitle || $slide->description)
                                        <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-5 z-20">
                                            @if($slide->title)
                                                <p class="font-serif text-lg sm:text-xl font-bold text-white leading-tight">{{ $slide->title }}</p>
                                            @endif
                                            @if($slide->subtitle)
                                                <p class="text-gold-300/95 text-sm mt-1 font-elegant font-light">{{ $slide->subtitle }}</p>
                                            @endif
                                            @if($slide->description)
                                                <p class="text-gray-300/90 text-xs sm:text-sm mt-2 line-clamp-2">{{ $slide->description }}</p>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                            @if($heroSlides->count() > 1)
                                <button type="button" id="hero-slide-prev" class="absolute left-2 top-1/2 -translate-y-1/2 z-30 w-9 h-9 rounded-full bg-black/50 border border-white/15 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center" aria-label="Slide précédent">
                                    <i class="fas fa-chevron-left text-xs"></i>
                                </button>
                                <button type="button" id="hero-slide-next" class="absolute right-2 top-1/2 -translate-y-1/2 z-30 w-9 h-9 rounded-full bg-black/50 border border-white/15 text-white hover:bg-gold-500/90 hover:text-dark-900 hover:border-gold-400 transition flex items-center justify-center" aria-label="Slide suivant">
                                    <i class="fas fa-chevron-right text-xs"></i>
                                </button>
                                <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-2 z-30" id="hero-slide-dots" role="tablist" aria-label="Choisir un slide">
                                    @foreach($heroSlides as $idx => $slide)
                                        <button type="button" class="hero-slide-dot h-2 rounded-full transition-all {{ $idx === 0 ? 'w-6 bg-gold-400' : 'w-2 bg-white/35 hover:bg-white/60' }}"
                                                data-hero-dot="{{ $idx }}" aria-label="Slide {{ $idx + 1 }}" aria-selected="{{ $idx === 0 ? 'true' : 'false' }}"></button>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Categories bar --}}
    <div class="relative z-10 border-t border-white/8 bg-dark-900/60 backdrop-blur-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="flex items-center overflow-x-auto gap-0 scrollbar-none py-1">
                @php $cats = [
                    'Magazine Culturel & Touristique',
                    'Patrimoine & Élégance',
                    'Regards Contemporains',
                    'Culture & Traditions',
                    'Destination du Mois',
                    'Art de Vivre',
                    'Gastronomie',
                ]; @endphp
                @foreach($cats as $cat)
                <a href="#" class="shrink-0 px-4 py-4 text-xs text-gray-400 hover:text-gold-400 tracking-wider uppercase font-medium transition border-b-2 border-transparent hover:border-gold-400/50 whitespace-nowrap">
                    {{ $cat }}
                </a>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Scroll indicator --}}
    <a href="#articles"
       class="absolute bottom-28 md:bottom-36 right-6 sm:right-10 w-12 h-12 rounded-full border border-gold-400/30 bg-dark-800/60 backdrop-blur flex items-center justify-center text-gold-400 hover:bg-gold-500 hover:text-dark-900 hover:border-gold-500 transition-all duration-300 animate-bounce z-10">
        <i class="fas fa-chevron-down text-sm"></i>
    </a>
</section>

{{-- ══════════════════════════════════════════════════════════
     SECTION: À LA UNE
══════════════════════════════════════════════════════════ --}}
<section id="articles" class="py-16 sm:py-24 bg-dark-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6">

        {{-- Section header --}}
        <div class="flex items-end justify-between mb-10 sm:mb-14">
            <div class="reveal">
                <p class="text-gold-400 text-xs tracking-[.25em] uppercase font-elegant mb-2">Sélection de la rédaction</p>
                <h2 class="font-serif text-3xl sm:text-4xl font-bold gold-line">Articles à la une</h2>
            </div>
            <a href="#" class="hidden sm:inline-flex items-center gap-2 text-sm text-gray-400 hover:text-gold-400 transition font-medium">
                Voir tous les articles <i class="fas fa-arrow-right text-xs"></i>
            </a>
        </div>

        {{-- Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">

            {{-- Featured article (large) --}}
            <div class="md:col-span-2 article-card group cursor-pointer reveal">
                <div class="relative overflow-hidden rounded-2xl aspect-video bg-dark-700">
                    <div class="article-img absolute inset-0 bg-linear-to-br from-green-900/40 via-dark-800 to-amber-900/30 flex items-center justify-center">
                        <div class="text-center">
                            <i class="fas fa-image text-5xl text-dark-600 mb-3"></i>
                            <p class="text-dark-500 text-sm">Remplacer avec votre image</p>
                        </div>
                    </div>
                    <div class="absolute inset-0 bg-linear-to-t from-black/90 via-black/30 to-transparent"></div>
                    <span class="absolute top-4 left-4 bg-gold-500 text-dark-900 text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide">
                        <i class="fas fa-star mr-1 text-[10px]"></i>À la une
                    </span>
                    <div class="absolute bottom-0 left-0 right-0 p-6">
                        <span class="text-gold-400 text-xs uppercase tracking-widest font-elegant">Patrimoine</span>
                        <h3 class="font-serif text-xl sm:text-2xl font-bold mt-1 mb-3 group-hover:text-gold-300 transition leading-snug">
                            Grand-Bassam, joyau colonial classé au patrimoine mondial
                        </h3>
                        <div class="flex items-center gap-3 text-xs text-gray-400">
                            <span><i class="fas fa-user-pen mr-1"></i>Marie Kouassi</span>
                            <span>·</span>
                            <span><i class="fas fa-calendar mr-1"></i>15 avril 2026</span>
                            <span>·</span>
                            <span><i class="fas fa-eye mr-1"></i>2 840 vues</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Side articles --}}
            <div class="flex flex-col gap-6">
                @php $sideArticles = [
                    ['title' => 'Les masques sacrés du peuple Wê, gardiens d\'une tradition millénaire', 'cat' => 'Culture', 'date' => '12 avr. 2026', 'views' => '1 240'],
                    ['title' => 'Parc National de Taï : dernier sanctuaire de la forêt primaire ouest-africaine', 'cat' => 'Nature', 'date' => '8 avr. 2026', 'views' => '980'],
                ]; @endphp
                @foreach($sideArticles as $art)
                <div class="article-card group cursor-pointer flex gap-4 reveal bg-dark-700/50 hover:bg-dark-700 border border-white/5 hover:border-gold-500/20 rounded-xl p-4 transition-all duration-300">
                    <div class="w-24 h-20 sm:w-28 rounded-xl bg-dark-600 overflow-hidden shrink-0 relative">
                        <div class="article-img absolute inset-0 bg-linear-to-br from-dark-700 to-dark-600 flex items-center justify-center">
                            <i class="fas fa-image text-dark-500"></i>
                        </div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <span class="text-gold-400/80 text-[11px] uppercase tracking-wider font-elegant">{{ $art['cat'] }}</span>
                        <h3 class="font-serif text-sm font-semibold mt-1 line-clamp-3 group-hover:text-gold-300 transition leading-snug">
                            {{ $art['title'] }}
                        </h3>
                        <p class="text-gray-500 text-xs mt-2">{{ $art['date'] }} · {{ $art['views'] }} vues</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- More articles row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6">
            @php $moreArts = [
                ['title' => 'Le Kédjénou, recette emblématique de la Côte d\'Ivoire', 'cat' => 'Gastronomie'],
                ['title' => 'Abidjan by night : les adresses incontournables', 'cat' => 'Art de vivre'],
                ['title' => 'Tissage de Korhogo, l\'art ancestral des tisserands sénoufo', 'cat' => 'Artisanat'],
                ['title' => 'Les lagunes d\'Assinie, perle du tourisme ivoirien', 'cat' => 'Destinations'],
            ]; @endphp
            @foreach($moreArts as $art)
            <a href="#" class="article-card group bg-dark-700/40 hover:bg-dark-700 border border-white/5 hover:border-gold-500/20 rounded-xl overflow-hidden transition-all duration-300 reveal">
                <div class="h-32 bg-dark-600 relative overflow-hidden">
                    <div class="article-img absolute inset-0 bg-linear-to-br from-dark-700 to-dark-500 flex items-center justify-center">
                        <i class="fas fa-image text-dark-400 text-lg"></i>
                    </div>
                </div>
                <div class="p-3">
                    <span class="text-gold-400/70 text-[10px] uppercase tracking-wider font-elegant">{{ $art['cat'] }}</span>
                    <h3 class="font-serif text-xs font-semibold mt-1 line-clamp-2 group-hover:text-gold-300 transition leading-snug">{{ $art['title'] }}</h3>
                </div>
            </a>
            @endforeach
        </div>
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

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
            @php $rubriques = [
                ['icon' => 'fa-landmark',       'label' => 'Patrimoine',     'count' => '48 articles',  'color' => 'from-amber-900/40 to-amber-800/10',     'border' => 'border-amber-700/20'],
                ['icon' => 'fa-palette',        'label' => 'Art & Culture',  'count' => '63 articles',  'color' => 'from-rose-900/30 to-rose-800/10',        'border' => 'border-rose-700/20'],
                ['icon' => 'fa-leaf',           'label' => 'Nature',         'count' => '37 articles',  'color' => 'from-green-900/40 to-green-800/10',      'border' => 'border-green-700/20'],
                ['icon' => 'fa-utensils',       'label' => 'Gastronomie',    'count' => '29 articles',  'color' => 'from-orange-900/30 to-orange-800/10',    'border' => 'border-orange-700/20'],
                ['icon' => 'fa-map-location-dot','label' => 'Destinations',  'count' => '52 articles',  'color' => 'from-blue-900/30 to-blue-800/10',        'border' => 'border-blue-700/20'],
                ['icon' => 'fa-gem',            'label' => 'Art de vivre',   'count' => '41 articles',  'color' => 'from-violet-900/30 to-violet-800/10',    'border' => 'border-violet-700/20'],
            ]; @endphp
            @foreach($rubriques as $r)
            <a href="#" class="group bg-linear-to-b {{ $r['color'] }} border {{ $r['border'] }} rounded-2xl p-5 text-center hover:scale-105 transition-all duration-300 reveal">
                <div class="w-12 h-12 rounded-xl bg-white/5 group-hover:bg-gold-500/15 flex items-center justify-center mx-auto mb-3 transition">
                    <i class="fas {{ $r['icon'] }} text-gold-400 text-lg group-hover:scale-110 transition-transform"></i>
                </div>
                <p class="text-white text-sm font-semibold font-serif">{{ $r['label'] }}</p>
                <p class="text-gray-500 text-xs mt-1">{{ $r['count'] }}</p>
            </a>
            @endforeach
        </div>
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
            <a href="{{ route('events.index') }}" class="hidden sm:inline-flex items-center gap-2 text-sm text-gray-400 hover:text-gold-400 transition">
                Voir l'agenda complet <i class="fas fa-arrow-right text-xs"></i>
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
                    @php $cats2 = ['Hôtellerie', 'Gastronomie', 'Guides', 'Artisanat', 'Loisirs', 'Bien-être']; @endphp
                    @foreach($cats2 as $c)
                    <span class="px-3 py-1.5 bg-dark-700 border border-white/8 text-gray-300 text-xs rounded-full hover:border-gold-400/40 hover:text-gold-400 cursor-pointer transition">{{ $c }}</span>
                    @endforeach
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
     FOOTER
══════════════════════════════════════════════════════════ --}}
<footer class="bg-dark-800 border-t border-white/5">
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
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16">
        {{-- Newsletter (pied de page) --}}
        <div id="newsletter-footer" class="mb-10 sm:mb-12 scroll-mt-28">
            <div class="rounded-2xl border border-gold-500/20 bg-linear-to-br from-dark-700/80 to-dark-800/80 p-6 sm:p-8 lg:flex lg:items-center lg:justify-between lg:gap-10 reveal">
                <div class="lg:max-w-md mb-6 lg:mb-0">
                    <div class="inline-flex items-center gap-2 mb-3">
                        <span class="w-10 h-10 rounded-xl bg-gold-500/15 border border-gold-500/25 flex items-center justify-center">
                            <i class="fas fa-envelope-open-text text-gold-400"></i>
                        </span>
                        <span class="text-gold-400/90 text-[10px] tracking-[.2em] uppercase font-elegant">Newsletter</span>
                    </div>
                    <h2 class="font-serif text-2xl sm:text-3xl font-bold text-white mb-2">Ne manquez rien de l’Ivoire</h2>
                    <p class="text-gray-400 text-sm sm:text-base font-elegant font-light leading-relaxed">
                        Articles, adresses et événements sélectionnés pour vous, directement dans votre boîte mail.
                    </p>
                </div>
                <div class="w-full lg:max-w-md shrink-0">
                    @if (session('newsletter_success'))
                        <div class="rounded-xl border border-emerald-500/35 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-200 mb-4">
                            {{ session('newsletter_success') }}
                        </div>
                    @endif
                    @if (session('newsletter_info'))
                        <div class="rounded-xl border border-amber-500/35 bg-amber-500/10 px-4 py-3 text-sm text-amber-100 mb-4">
                            {{ session('newsletter_info') }}
                        </div>
                    @endif
                    @if (session('newsletter_error'))
                        <div class="rounded-xl border border-rose-500/35 bg-rose-500/10 px-4 py-3 text-sm text-rose-200 mb-4">
                            {{ session('newsletter_error') }}
                        </div>
                    @endif
                    <form method="post" action="{{ route('newsletter.subscribe') }}" class="flex flex-col sm:flex-row gap-3">
                        @csrf
                        <label for="newsletter-email" class="sr-only">Adresse e-mail</label>
                        <input type="email" name="newsletter_email" id="newsletter-email" required maxlength="255"
                               value="{{ old('newsletter_email') }}"
                               placeholder="Votre adresse e-mail"
                               autocomplete="email"
                               class="flex-1 bg-dark-900 border border-white/10 focus:border-gold-400/50 focus:ring-1 focus:ring-gold-500/30 text-white placeholder-gray-600 rounded-xl px-4 py-3 text-sm outline-none transition">
                        <button type="submit"
                                class="shrink-0 inline-flex items-center justify-center gap-2 bg-gold-500 hover:bg-gold-400 text-dark-900 font-bold px-6 py-3 rounded-xl text-sm transition shadow-lg shadow-gold-500/20">
                            <i class="fas fa-paper-plane text-xs"></i>
                            S’abonner
                        </button>
                    </form>
                    @error('newsletter_email')
                        <p class="text-rose-400 text-xs mt-2">{{ $message }}</p>
                    @enderror
                    <p class="text-gray-600 text-[11px] mt-3">Aucun spam. Désabonnement possible à tout moment.</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">

            {{-- Brand + réseaux --}}
            <div class="lg:col-span-1">
                <div class="flex items-center gap-3 mb-4">
                    @if(!empty($siteBrand['logo_url']))
                        <div class="w-10 h-10 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden p-0.5 shrink-0">
                            <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                        </div>
                    @else
                        <div class="w-10 h-10 rounded-lg bg-gold-500 flex items-center justify-center shrink-0">
                            <i class="fas fa-gem text-dark-900"></i>
                        </div>
                    @endif
                    <div class="min-w-0">
                        <p class="text-gold-400 font-serif font-bold leading-tight truncate">{{ $siteBrand['site_name'] }}</p>
                        <p class="text-gray-500 text-[10px] tracking-widest uppercase truncate">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
                    </div>
                </div>
                <p class="text-gray-500 text-sm leading-relaxed mb-5">
                    {{ $footerBlurb }}
                </p>
                @if(count($socialRows) > 0 || $waHref)
                    <p class="text-gray-600 text-[10px] uppercase tracking-wider mb-2">Suivez-nous</p>
                    <div class="flex flex-wrap items-center gap-2">
                        @foreach($socialRows as $row)
                            <a href="{{ $row['url'] }}" target="_blank" rel="noopener noreferrer" title="{{ $row['label'] }}"
                               class="w-9 h-9 rounded-lg bg-dark-700 border border-white/8 flex items-center justify-center text-gray-400 hover:text-gold-400 hover:border-gold-500/30 transition text-xs">
                                <i class="fab {{ $row['icon'] }}"></i>
                            </a>
                        @endforeach
                        @if($waHref)
                            <a href="{{ $waHref }}" target="_blank" rel="noopener noreferrer" title="WhatsApp"
                               class="w-9 h-9 rounded-lg bg-dark-700 border border-white/8 flex items-center justify-center text-emerald-400/90 hover:text-emerald-300 hover:border-emerald-500/30 transition text-xs">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                    </div>
                @endif
            </div>

            {{-- Magazine --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Magazine</h4>
                <ul class="space-y-2.5 text-sm text-gray-500">
                    @foreach(['Articles', 'Découvertes', 'Événements', 'Destinations', 'Gastronomie', 'Artisanat'] as $l)
                    <li><a href="#" class="hover:text-gold-400 transition">{{ $l }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Annuaire --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Annuaire</h4>
                <ul class="space-y-2.5 text-sm text-gray-500">
                    @foreach(['Hôtellerie', 'Restaurants', 'Guides touristiques', 'Artisans', 'Bien-être', 'Devenir prestataire'] as $l)
                    <li><a href="#" class="hover:text-gold-400 transition">{{ $l }}</a></li>
                    @endforeach
                </ul>
            </div>

            {{-- Contact (admin) --}}
            <div>
                <h4 class="text-white font-semibold text-sm mb-4">Contact</h4>
                <ul class="space-y-2.5 text-sm text-gray-500 mb-5">
                    @foreach(['À propos', 'Guide d\'utilisation', 'FAQ', 'Mentions légales', 'Politique de confidentialité'] as $l)
                    <li><a href="#" class="hover:text-gold-400 transition">{{ $l }}</a></li>
                    @endforeach
                </ul>
                <div class="space-y-3 text-xs text-gray-500">
                    @if(!empty($c['phone_1']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-phone text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_1']) }}" class="hover:text-gold-400 transition break-all">{{ $c['phone_1'] }}</a>
                        </p>
                    @endif
                    @if(!empty($c['phone_2']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-phone-volume text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            <a href="tel:{{ preg_replace('/\s+/', '', $c['phone_2']) }}" class="hover:text-gold-400 transition break-all">{{ $c['phone_2'] }}</a>
                        </p>
                    @endif
                    @if(!empty($c['email_primary']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-envelope text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            <a href="mailto:{{ $c['email_primary'] }}" class="hover:text-gold-400 transition break-all">{{ $c['email_primary'] }}</a>
                        </p>
                    @endif
                    @if(!empty($c['email_secondary']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-envelope-open text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            <a href="mailto:{{ $c['email_secondary'] }}" class="hover:text-gold-400 transition break-all">{{ $c['email_secondary'] }}</a>
                        </p>
                    @endif
                    @if(!empty($c['contact_form_email']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-paper-plane text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            <span class="text-gray-600">Formulaire&nbsp;:</span>
                            <a href="mailto:{{ $c['contact_form_email'] }}" class="hover:text-gold-400 transition break-all">{{ $c['contact_form_email'] }}</a>
                        </p>
                    @endif
                    @if(!empty($c['address']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-location-dot text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            @if($mapHref)
                                <a href="{{ $mapHref }}" target="_blank" rel="noopener noreferrer" class="hover:text-gold-400 transition">{{ $c['address'] }}</a>
                            @else
                                <span>{{ $c['address'] }}</span>
                            @endif
                        </p>
                    @endif
                    @if(!empty($c['opening_hours']))
                        <p class="flex items-start gap-2">
                            <i class="fas fa-clock text-gold-500/50 w-4 mt-0.5 shrink-0"></i>
                            <span class="whitespace-pre-line text-gray-400">{{ $c['opening_hours'] }}</span>
                        </p>
                    @endif
                    @if(empty($c['phone_1']) && empty($c['phone_2']) && empty($c['email_primary']) && empty($c['email_secondary']) && empty($c['contact_form_email']) && empty($c['address']) && empty($c['opening_hours']))
                        <p class="text-gray-600 italic">Les coordonnées seront affichées ici une fois renseignées dans l’administration.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="border-t border-white/5 pt-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-gray-600">
            <p>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }} — Tous droits réservés</p>
            <div class="flex items-center gap-4">
                <a href="#" class="hover:text-gray-400 transition">Mentions légales</a>
                <a href="#" class="hover:text-gray-400 transition">CGU</a>
                <a href="{{ route('login') }}" class="hover:text-gold-400 transition flex items-center gap-1">
                    <i class="fas fa-lock text-[10px]"></i>Espace membres
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
        if (e.key === 'Escape') closeContactModal();
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

    // Hero : carrousel des slides (images responsives)
    (function () {
        const layers = document.querySelectorAll('.hero-slide-layer');
        if (!layers.length) return;

        let index = 0;
        const dots = document.querySelectorAll('.hero-slide-dot');
        const prev = document.getElementById('hero-slide-prev');
        const next = document.getElementById('hero-slide-next');

        function apply() {
            layers.forEach((el, j) => {
                const on = j === index;
                el.classList.toggle('opacity-100', on);
                el.classList.toggle('z-10', on);
                el.classList.toggle('opacity-0', !on);
                el.classList.toggle('z-0', !on);
                el.classList.toggle('pointer-events-none', !on);
                el.setAttribute('aria-hidden', on ? 'false' : 'true');
            });
            dots.forEach((d, j) => {
                const on = j === index;
                d.classList.toggle('w-6', on);
                d.classList.toggle('bg-gold-400', on);
                d.classList.toggle('w-2', !on);
                d.classList.toggle('bg-white/35', !on);
                d.classList.toggle('hover:bg-white/60', !on);
                d.setAttribute('aria-selected', on ? 'true' : 'false');
            });
        }

        function go(delta) {
            index = (index + delta + layers.length) % layers.length;
            apply();
        }

        if (prev) prev.addEventListener('click', () => go(-1));
        if (next) next.addEventListener('click', () => go(1));
        dots.forEach((d, j) => d.addEventListener('click', () => { index = j; apply(); }));

        let timer = null;
        function armAutoplay() {
            if (layers.length < 2) return;
            clearInterval(timer);
            timer = setInterval(() => go(1), 7000);
        }
        armAutoplay();
        const root = document.getElementById('hero-slides-carousel');
        if (root) {
            root.addEventListener('mouseenter', () => clearInterval(timer));
            root.addEventListener('mouseleave', armAutoplay);
        }
    })();
</script>

</body>
</html>
