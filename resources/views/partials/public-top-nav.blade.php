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

            {{-- Theme toggle --}}
            <button type="button" id="theme-toggle" class="theme-toggle" title="Changer le thème" aria-label="Basculer le thème">
                <i class="fas fa-sun icon-sun"></i>
                <i class="fas fa-moon icon-moon"></i>
            </button>

            @auth
            @php $__initials = strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)); @endphp
            <div class="relative hidden sm:block" id="nav-user-dropdown-wrap">

                {{-- Trigger --}}
                <button type="button" id="nav-user-dropdown-btn"
                        class="group inline-flex items-center gap-2.5 pl-1.5 pr-3.5 py-1.5 rounded-full border border-white/10 bg-white/4 hover:border-amber-500/30 hover:bg-amber-500/6 transition-all duration-200">
                    <span class="w-7 h-7 rounded-full bg-linear-to-br from-amber-400 to-amber-600 flex items-center justify-center text-[11px] font-bold text-white shadow-sm">
                        {{ $__initials }}
                    </span>
                    <span class="text-sm font-medium text-gray-300 group-hover:text-amber-100 transition-colors">Mon espace</span>
                    <i class="fas fa-chevron-down text-[9px] text-gray-500 group-hover:text-amber-400 transition-all duration-200" id="nav-dd-chevron"></i>
                </button>

                {{-- Dropdown --}}
                <div id="nav-user-dropdown"
                     class="hidden absolute right-0 top-full mt-3 w-72 z-50"
                     role="menu">
                    <div class="rounded-2xl overflow-hidden"
                         style="background:var(--dd-bg);border:1px solid var(--dd-border);box-shadow:var(--dd-shadow);backdrop-filter:blur(32px)">

                        {{-- En-tête utilisateur --}}
                        <div class="relative px-4 pt-4 pb-3.5 overflow-hidden">
                            <div class="absolute inset-0" style="background:radial-gradient(ellipse 120% 80% at 0% 0%,var(--dd-head-gradient),transparent 65%)"></div>
                            <div class="relative flex items-start gap-3">
                                <div class="w-11 h-11 rounded-xl bg-linear-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shrink-0"
                                     style="box-shadow:0 8px 24px rgba(245,158,11,0.3)">
                                    {{ $__initials }}
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <p class="text-white text-sm font-semibold leading-tight truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                    <p class="text-slate-500 text-xs truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    <span class="mt-1.5 inline-flex items-center gap-1.5 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                        @if(auth()->user()->role==='admin') bg-red-500/10 text-red-300 border border-red-500/20
                                        @elseif(auth()->user()->role==='editor') bg-blue-500/10 text-blue-300 border border-blue-500/20
                                        @elseif(auth()->user()->role==='provider') bg-purple-500/10 text-purple-300 border border-purple-500/20
                                        @else bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            @if(auth()->user()->role==='admin') bg-red-400
                                            @elseif(auth()->user()->role==='editor') bg-blue-400
                                            @elseif(auth()->user()->role==='provider') bg-purple-400
                                            @else bg-emerald-400 @endif"></span>
                                        {{ ucfirst(auth()->user()->role) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div style="height:1px;background:linear-gradient(90deg,transparent,var(--dd-divider) 30%,var(--dd-divider) 70%,transparent);margin:0 1rem"></div>

                        {{-- Navigation --}}
                        <div class="p-2">
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> Tableau de bord
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-users"></i></span> Utilisateurs
                                </a>
                                <a href="{{ route('admin.payments.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-chart-line"></i></span> Finance
                                </a>
                            @elseif(auth()->user()->role === 'editor')
                                <a href="{{ route('editor.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> Tableau de bord
                                </a>
                                <a href="{{ route('editor.articles.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-pen-nib"></i></span> Mes articles
                                </a>
                            @elseif(auth()->user()->role === 'provider')
                                <a href="{{ route('provider.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> Tableau de bord
                                </a>
                                <a href="{{ route('provider.profile.edit') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-store"></i></span> Ma fiche
                                </a>
                                <a href="{{ route('provider.billing.plans') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gem"></i></span> Mon forfait
                                </a>
                            @else
                                <a href="{{ route('visitor.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> Tableau de bord
                                </a>
                                <a href="{{ route('visitor.purchases.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-image"></i></span> Mes achats
                                </a>
                                <a href="{{ route('visitor.profile.edit') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-user-pen"></i></span> Mon profil
                                </a>
                                <a href="{{ route('visitor.favorites.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-heart"></i></span> Mes favoris
                                </a>
                                <a href="{{ route('visitor.notifications.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-bell"></i></span> Notifications
                                </a>
                            @endif
                        </div>

                        <div style="height:1px;background:linear-gradient(90deg,transparent,var(--dd-divider) 30%,var(--dd-divider) 70%,transparent);margin:0 1rem"></div>

                        {{-- Déconnexion --}}
                        <div class="p-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-dd-lnk nav-dd-danger w-full text-left">
                                    <span class="nav-dd-icon nav-dd-icon-danger"><i class="fas fa-right-from-bracket"></i></span>
                                    Déconnexion
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
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

<style>
    /* ── CSS variables (dark default / light override) ── */
    :root {
        --dd-bg: rgba(8,10,14,0.96);
        --dd-border: rgba(255,255,255,0.07);
        --dd-shadow: 0 32px 64px -8px rgba(0,0,0,0.9), 0 0 0 1px rgba(255,255,255,0.03), inset 0 1px 0 rgba(255,255,255,0.06);
        --dd-divider: rgba(255,255,255,0.06);
        --dd-head-gradient: rgba(245,158,11,0.07);
    }
    html:not(.dark) {
        --dd-bg: rgba(255,253,248,0.99);
        --dd-border: rgba(0,0,0,0.09);
        --dd-shadow: 0 24px 48px -8px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,0.9);
        --dd-divider: rgba(0,0,0,0.07);
        --dd-head-gradient: rgba(245,158,11,0.05);
    }
    /* ── Nav light mode ─────────────────────────────────── */
    html:not(.dark) .public-topbar {
        background: linear-gradient(105deg,#fefcf8 0%,#fdf9f2 42%,#fbf7eb 100%);
        border-bottom-color: rgba(200,160,60,0.22);
    }
    html:not(.dark) .public-topbar::before {
        background:
            radial-gradient(100% 180% at 0% 0%,rgba(232,160,32,0.05),transparent 52%),
            radial-gradient(80% 120% at 100% 100%,rgba(180,140,60,0.03),transparent 50%);
    }
    html:not(.dark) .public-slogan-pill {
        border-color:rgba(0,0,0,0.08); background:rgba(0,0,0,0.03);
        box-shadow:0 0 0 1px rgba(232,160,32,0.1),0 4px 24px rgba(0,0,0,0.05);
    }
    html:not(.dark) .public-topbar-action {
        color:rgba(28,25,21,0.7); border-color:rgba(0,0,0,0.1); background:rgba(0,0,0,0.03);
    }
    html:not(.dark) .public-topbar-action:hover {
        color:#92400e; border-color:rgba(232,160,32,0.4); background:rgba(232,160,32,0.08);
    }
    html:not(.dark) .public-header {
        background:linear-gradient(180deg,rgba(254,252,248,0.98) 0%,rgba(254,252,248,0.94) 55%,rgba(254,252,248,0.84) 100%);
        border-bottom-color:rgba(0,0,0,0.08);
    }
    html:not(.dark) .public-nav-pill { color:rgba(28,25,21,0.72); }
    html:not(.dark) .public-nav-pill:hover { color:#1c1915; background:rgba(0,0,0,0.05); }
    html:not(.dark) .public-nav-pill.is-active {
        color:#92400e; background:rgba(232,160,32,0.1); box-shadow:inset 0 0 0 1px rgba(232,160,32,0.32);
    }
    html:not(.dark) #public-mobile-menu {
        background:rgba(254,252,248,0.98); border-top-color:rgba(0,0,0,0.08);
    }
    /* ── Dropdown light mode ────────────────────────────── */
    html:not(.dark) .nav-dd-lnk { color:#44413a; }
    html:not(.dark) .nav-dd-lnk:hover { background:rgba(0,0,0,0.04); color:#1c1915; }
    html:not(.dark) .nav-dd-lnk:hover .nav-dd-icon { background:rgba(245,158,11,0.14); color:#d97706; }
    html:not(.dark) .nav-dd-icon { background:rgba(0,0,0,0.05); color:#6b6860; }
    html:not(.dark) .nav-dd-danger { color:#dc2626; }
    html:not(.dark) .nav-dd-danger .nav-dd-icon { color:#dc2626; background:rgba(239,68,68,0.08); }
    html:not(.dark) .nav-dd-danger:hover { background:rgba(239,68,68,0.06); color:#b91c1c; }
    /* ── Theme toggle ───────────────────────────────────── */
    .theme-toggle {
        width:2.25rem; height:2.25rem; border-radius:9999px;
        display:flex; align-items:center; justify-content:center;
        border:1px solid rgba(255,255,255,0.1); background:rgba(255,255,255,0.04);
        color:#94a3b8; cursor:pointer; transition:.2s ease; font-size:0.8rem;
        flex-shrink:0;
    }
    .theme-toggle:hover { color:#fde68a; border-color:rgba(232,160,32,0.35); background:rgba(232,160,32,0.08); }
    html:not(.dark) .theme-toggle { border-color:rgba(0,0,0,0.1); background:rgba(0,0,0,0.04); color:#6b6860; }
    html:not(.dark) .theme-toggle:hover { color:#d97706; border-color:rgba(232,160,32,0.35); background:rgba(232,160,32,0.08); }
    .theme-toggle .icon-sun  { display:block; }
    .theme-toggle .icon-moon { display:none; }
    html:not(.dark) .theme-toggle .icon-sun  { display:none; }
    html:not(.dark) .theme-toggle .icon-moon { display:block; }
    .nav-dd-lnk {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.45rem 0.625rem; border-radius: 0.625rem;
        font-size: 0.8125rem; font-weight: 500; color: #94a3b8;
        text-decoration: none; white-space: nowrap;
        background: none; border: none; cursor: pointer;
        transition: background .15s ease, color .15s ease;
        width: 100%; text-align: left;
    }
    .nav-dd-lnk:hover { background: rgba(255,255,255,0.05); color: #f1f5f9; }
    .nav-dd-lnk:hover .nav-dd-icon { background: rgba(245,158,11,0.18); color: #fbbf24; }
    .nav-dd-icon {
        width: 1.875rem; height: 1.875rem; border-radius: 0.5rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(255,255,255,0.05); color: #475569; font-size: 0.7rem;
        transition: background .15s ease, color .15s ease;
    }
    .nav-dd-danger { color: #f87171; }
    .nav-dd-danger .nav-dd-icon { color: #f87171; background: rgba(239,68,68,0.1); }
    .nav-dd-danger:hover { background: rgba(239,68,68,0.08); color: #fca5a5; }
    .nav-dd-danger:hover .nav-dd-icon { background: rgba(239,68,68,0.2); color: #fca5a5; }
</style>
@include('partials.theme-light-bridge')
<script>
    (function () {
        // Menu mobile
        const toggle = document.getElementById('public-menu-toggle');
        const menu   = document.getElementById('public-mobile-menu');
        if (toggle && menu) toggle.addEventListener('click', () => menu.classList.toggle('hidden'));

        // Dropdown "Mon espace"
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
        document.addEventListener('click', (e) => { if (!wrap?.contains(e.target)) { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); } });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); } });
    })();

    // ── Theme toggle ──────────────────────────────────────────────────────
    (function () {
        const btn = document.getElementById('theme-toggle');
        if (!btn) return;
        btn.addEventListener('click', () => {
            const html = document.documentElement;
            const going_light = html.classList.contains('dark');
            html.classList.toggle('dark', !going_light);
            localStorage.setItem('tiTheme', going_light ? 'light' : 'dark');
        });
    })();
</script>
