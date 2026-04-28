@php
    $topContact = $siteBrand['contact'] ?? [];
    $topPhoneDisplay = !empty($topContact['phone_1']) ? $topContact['phone_1'] : '+225 27 22 48 36 90';
    $topPhoneHref = 'tel:'.preg_replace('/[^\d+]/', '', $topPhoneDisplay);
    $publicInfoPages = \Illuminate\Support\Facades\Schema::hasTable('information_pages')
        ? \App\Models\InformationPage::query()->orderBy('sort_order')->orderBy('id')->get()
        : collect();
    $publicInfoGuide = $publicInfoPages->firstWhere('slug', 'user-guide');
    $publicInfoFaq = $publicInfoPages->firstWhere('slug', 'faq');
@endphp

<style>
    .font-plus { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
    .public-topbar {
        position: relative;
        isolation: isolate;
        background: linear-gradient(105deg, #0c0b09 0%, #12100c 42%, #15120e 100%);
        border-bottom: 1px solid rgba(232, 160, 32, 0.14);
    }
    .public-topbar::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background:
            radial-gradient(100% 180% at 0% 0%, rgba(232, 160, 32, 0.11), transparent 52%),
            radial-gradient(80% 120% at 100% 100%, rgba(120, 90, 40, 0.08), transparent 50%);
        pointer-events: none;
    }
    .public-topbar > * { position: relative; z-index: 1; }
    .public-slogan-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.35rem 0.85rem 0.35rem 0.65rem;
        border-radius: 9999px;
        border: 1px solid rgba(255,255,255,0.08);
        background: rgba(255,255,255,0.04);
        box-shadow: 0 0 0 1px rgba(232,160,32,0.06), 0 4px 24px rgba(0,0,0,0.25);
    }
    .public-pulse-dot {
        width: 6px; height: 6px; border-radius: 9999px;
        background: linear-gradient(135deg, #f5b942, #e8a020);
        box-shadow: 0 0 10px rgba(232,160,32,0.75);
        animation: publicPulse 2.2s ease-in-out infinite;
    }
    @keyframes publicPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.65; transform: scale(0.92); }
    }
    .public-topbar-action {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.32rem 0.85rem;
        border-radius: 9999px;
        font-size: 11px;
        font-weight: 500;
        color: rgba(226, 232, 240, 0.88);
        border: 1px solid rgba(255,255,255,0.07);
        background: rgba(255,255,255,0.035);
        transition: .2s ease;
    }
    .public-topbar-action:hover {
        color: #fde68a;
        border-color: rgba(232,160,32,0.35);
        background: rgba(232,160,32,0.08);
    }
    .public-header {
        background: linear-gradient(180deg, rgba(13,13,11,0.92) 0%, rgba(13,13,11,0.72) 55%, rgba(13,13,11,0.45) 100%);
        backdrop-filter: blur(16px) saturate(160%);
        border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .public-nav-pill {
        position: relative;
        padding: 0.5rem 1rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 500;
        letter-spacing: 0.03em;
        color: rgba(226, 232, 240, 0.82);
        transition: .2s ease;
    }
    .public-nav-pill:hover {
        color: #fef3c7;
        background: rgba(255,255,255,0.06);
    }
    .public-nav-pill.is-active {
        color: #fde68a;
        background: rgba(232,160,32,0.10);
        box-shadow: inset 0 0 0 1px rgba(232,160,32,0.28);
    }
    .logo-ring {
        position: relative;
        border-radius: 1rem;
        padding: 2px;
        background: linear-gradient(135deg, rgba(232,160,32,0.55), rgba(255,255,255,0.12), rgba(232,160,32,0.25));
        box-shadow: 0 8px 32px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.05) inset;
        transition: transform .35s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow .35s ease;
        animation: logoFloat 5.4s ease-in-out infinite;
    }
    .logo-ring::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: inherit;
        background: conic-gradient(from 180deg, rgba(232,160,32,0.0), rgba(232,160,32,0.5), rgba(255,255,255,0.08), rgba(232,160,32,0.0));
        opacity: .45;
        filter: blur(6px);
        animation: logoHaloSpin 8s linear infinite;
        pointer-events: none;
    }
    .logo-ring-inner {
        border-radius: calc(1rem - 2px);
        overflow: hidden;
        background: rgba(13,13,11,0.9);
        transition: transform .35s cubic-bezier(0.2, 0.8, 0.2, 1), filter .35s ease;
    }
    .group:hover .logo-ring {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 14px 34px rgba(0,0,0,0.42), 0 0 26px rgba(232,160,32,0.26), 0 0 0 1px rgba(255,255,255,0.08) inset;
    }
    .group:hover .logo-ring::before {
        opacity: .8;
    }
    .group:hover .logo-ring-inner {
        transform: scale(1.04);
        filter: brightness(1.08) saturate(1.08);
    }
    @keyframes logoFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-2px); }
    }
    @keyframes logoHaloSpin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    @media (prefers-reduced-motion: reduce) {
        .logo-ring,
        .logo-ring::before {
            animation: none;
        }
    }
</style>

<div class="public-topbar font-plus text-gray-300 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-2.5 flex flex-wrap items-center justify-between gap-y-2 gap-x-4 min-h-[2.5rem]">
            <div class="public-slogan-pill">
                <span class="public-pulse-dot shrink-0" aria-hidden="true"></span>
                <span class="text-[10px] sm:text-[11px] font-semibold tracking-[0.2em] uppercase text-amber-200/90">
                    {{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}
                </span>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2 sm:gap-2.5">
                <a href="{{ $topPhoneHref }}" class="public-topbar-action">
                    <i class="fas fa-phone-volume text-[10px] text-amber-400/90"></i>
                    <span>{{ $topPhoneDisplay }}</span>
                </a>
                @if($publicInfoGuide)
                <a href="{{ route('information.show', $publicInfoGuide) }}" class="public-topbar-action">
                    <i class="fas fa-book-open text-[10px] text-amber-400/90"></i>
                    Guide
                </a>
                @endif
                @if($publicInfoFaq)
                <a href="{{ route('information.show', $publicInfoFaq) }}" class="public-topbar-action">
                    <i class="fas fa-circle-question text-[10px] text-amber-400/90"></i>
                    FAQ
                </a>
                @endif
            </div>
        </div>
    </div>
</div>

<header class="public-header font-plus sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[4.25rem] md:h-[5.25rem] flex items-center justify-between gap-4 md:gap-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3 sm:gap-3.5 shrink-0 group">
            @if(!empty($siteBrand['logo_url']))
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-14 h-14 md:w-[4.25rem] md:h-[4.25rem] border border-white/10 bg-white/[0.04] flex items-center justify-center p-0.5">
                        <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                    </div>
                </div>
            @else
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-14 h-14 md:w-[4.25rem] md:h-[4.25rem] bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center">
                        <i class="fas fa-gem text-black text-base md:text-lg"></i>
                    </div>
                </div>
            @endif
            <div class="hidden sm:block min-w-0">
                <p class="text-transparent bg-clip-text bg-gradient-to-r from-amber-200 via-amber-400 to-amber-200 font-serif font-bold text-base md:text-lg leading-tight tracking-tight truncate">
                    {{ $siteBrand['site_name'] }}
                </p>
                <p class="text-gray-500 text-[9px] md:text-[10px] tracking-[0.22em] uppercase truncate font-plus font-medium mt-0.5">
                    {{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}
                </p>
            </div>
        </a>

        <nav class="hidden lg:flex items-center gap-1">
            <a href="{{ route('home') }}" class="public-nav-pill {{ request()->routeIs('home') ? 'is-active' : '' }}">Magazine Premium</a>
            <a href="{{ route('articles.index') }}" class="public-nav-pill {{ request()->routeIs('articles.*') ? 'is-active' : '' }}">Articles</a>
            <a href="{{ route('discoveries.index') }}" class="public-nav-pill {{ request()->routeIs('discoveries.*') ? 'is-active' : '' }}">Découvertes</a>
            <a href="{{ route('providers.index') }}" class="public-nav-pill {{ request()->routeIs('providers.*') ? 'is-active' : '' }}">Annuaire Prestataires</a>
            <a href="{{ route('events.index') }}" class="public-nav-pill {{ request()->routeIs('events.*') ? 'is-active' : '' }}">Événements</a>
            <a href="{{ route('gallery.public') }}" class="public-nav-pill {{ request()->routeIs('gallery.public') ? 'is-active' : '' }}">Galerie</a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            {{-- Recherche --}}
            <a href="{{ route('search') }}"
               class="w-9 h-9 flex items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-gray-400 hover:text-amber-300 hover:border-amber-500/30 hover:bg-amber-500/5 transition"
               title="Recherche">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </a>

            @auth
            <a href="{{ route('dashboard') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-200 rounded-full border border-white/12 bg-white/4 hover:border-amber-500/35 hover:bg-amber-500/8 hover:text-amber-100 transition">
                <i class="fas fa-gauge-high text-xs opacity-80"></i>
                <span>Dashboard</span>
            </a>
            @else
            <a href="{{ route('login') }}"
               class="inline-flex items-center gap-1.5 px-3.5 sm:px-5 py-2 rounded-full bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 text-black text-xs sm:text-sm font-bold transition">
                <span>Connexion</span>
            </a>
            @endauth

            <button id="public-menu-toggle" type="button"
                class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-gray-200 hover:text-white hover:border-amber-500/30 hover:bg-amber-500/5 transition">
                <i class="fas fa-bars-staggered text-sm"></i>
            </button>
        </div>
    </div>

    <div id="public-mobile-menu" class="lg:hidden hidden border-t border-white/10 bg-[#0d0d0b]/96">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 space-y-1">
            <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('home') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">Magazine Premium</a>
            <a href="{{ route('articles.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('articles.*') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">Articles</a>
            <a href="{{ route('discoveries.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('discoveries.*') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">Découvertes</a>
            <a href="{{ route('providers.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('providers.*') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">Annuaire Prestataires</a>
            <a href="{{ route('events.index') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('events.*') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">Événements</a>
            <a href="{{ route('gallery.public') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('gallery.public') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">Galerie Trésors d'Ivoire</a>
            <a href="{{ route('search') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('search') ? 'text-amber-300 bg-amber-500/10' : 'text-gray-300 hover:bg-white/5' }}">
                <i class="fas fa-magnifying-glass mr-2 text-xs"></i>Recherche
            </a>
        </div>
    </div>
</header>

<script>
    (function () {
        const toggle = document.getElementById('public-menu-toggle');
        const menu = document.getElementById('public-mobile-menu');
        if (!toggle || !menu) return;
        toggle.addEventListener('click', () => menu.classList.toggle('hidden'));
    })();
</script>
