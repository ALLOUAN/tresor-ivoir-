@php
    $topContact = $siteBrand['contact'] ?? [];
    $topPhoneDisplay = !empty($topContact['phone_1']) ? $topContact['phone_1'] : '+225 27 22 48 36 90';
    $topPhoneHref = 'tel:'.preg_replace('/[^\d+]/', '', $topPhoneDisplay);
    $publicInfoPages = \Illuminate\Support\Facades\Schema::hasTable('information_pages')
        ? \App\Models\InformationPage::query()->orderBy('sort_order')->orderBy('id')->get()
        : collect();
    $publicInfoGuide = $publicInfoPages->firstWhere('slug', 'user-guide');
    $publicInfoFaq = $publicInfoPages->firstWhere('slug', 'faq');
    $publicNavItems = [
        ['label' => 'Accueil',               'icon' => 'fas fa-house',            'href' => route('home'),               'active' => request()->routeIs('home')],
        ['label' => 'Tourisme',              'icon' => 'fas fa-umbrella-beach',   'href' => route('tourist.cities'),     'active' => request()->routeIs('tourist.*')],
        ['label' => 'Cultures & Traditions', 'shortLabel' => 'Cultures', 'icon' => 'fas fa-masks-theater',    'href' => route('cultural.peoples'),   'active' => request()->routeIs('cultural.*')],
        ['label' => 'Annuaire',              'icon' => 'fas fa-address-book',     'href' => route('providers.index'),    'active' => request()->routeIs('providers.*')],
        ['label' => 'Magazine',              'icon' => 'fas fa-newspaper',        'href' => route('articles.index'),     'active' => request()->routeIs('articles.*') || request()->routeIs('discoveries.*')],
        ['label' => 'Événements',            'icon' => 'fas fa-calendar-days',    'href' => route('events.index'),       'active' => request()->routeIs('events.*')],
    ];
    $publicGalleryActive = request()->routeIs('gallery.public');
    $headerImage = $headerImage ?? (\Illuminate\Support\Facades\Schema::hasTable('header_images')
        ? \App\Models\HeaderImage::query()->find(1)
        : null);
    $activeFlashInfos = \Illuminate\Support\Facades\Schema::hasTable('flash_infos')
        ? \App\Models\FlashInfo::currentlyVisible()->orderBy('display_order')->orderByDesc('id')->get()
        : collect();
@endphp

<style>
    .font-plus { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }

    /* ── Top bar ─────────────────────────────────────────── */
    .public-topbar {
        position: relative;
        isolation: isolate;
        background: linear-gradient(105deg, #1f6b34 0%, #1c5f2e 50%, #1a5429 100%);
        border-bottom: 1px solid rgba(34,197,94, 0.14);
    }
    .public-topbar::before {
        content: '';
        position: absolute;
        inset: 0;
        z-index: 0;
        background:
            radial-gradient(100% 180% at 0% 0%, rgba(34,197,94, 0.14), transparent 52%),
            radial-gradient(80% 120% at 100% 100%, rgba(20, 70, 40, 0.18), transparent 50%);
        pointer-events: none;
    }
    .public-topbar > * { position: relative; z-index: 1; }
    .public-slogan-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.65rem;
        padding: 0.5rem 1.1rem 0.5rem 0.85rem;
        border-radius: 9999px;
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(233, 229, 217, 0.08);
        box-shadow: 0 0 0 1px rgba(255,255,255,0.06), 0 4px 24px rgba(0,0,0,0.2);
    }
    .public-pulse-dot {
        width: 9px; height: 9px; border-radius: 9999px;
        background: linear-gradient(135deg, #4ade80, #22c55e);
        box-shadow: 0 0 12px rgba(34,197,94,0.8);
        animation: publicPulse 2.2s ease-in-out infinite;
    }
    @keyframes publicPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.65; transform: scale(0.92); }
    }
    /* Couleurs fixées en CSS pur (et non via classes Tailwind text-green-*) :
       le pont de thème clair (theme-light-bridge) réécrit ces classes en vert
       très foncé pour les usages sur fond clair, ce qui les rendait quasi
       invisibles sur ce bandeau à fond vert foncé. */
    .public-slogan-text { color: #bbf7d0; }
    .public-topbar-action {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 0.4rem 0.85rem;
        border-radius: 9999px;
        font-size: 12px;
        font-weight: 500;
        color: rgba(234, 255, 241, 0.92);
        border: 1px solid rgba(255,255,255,0.14);
        background: rgba(233, 229, 217, 0.07);
        transition: .2s ease;
    }
    .public-topbar-action:hover {
        color: #ffffff;
        border-color: rgba(255,255,255,0.3);
        background: rgba(233, 229, 217, 0.16);
    }
    .public-topbar-action i { font-size: 12px; color: #4ade80; transition: color .2s ease; }
    .public-topbar-action:hover i { color: #86efac; }

    /* ── Flash info ticker ───────────────────────────────── */
    .flash-ticker-bg {
        background:
            radial-gradient(120% 260% at 0% 0%, rgba(242,121,15,.18), transparent 55%),
            radial-gradient(90% 220% at 100% 100%, rgba(34,197,94,.12), transparent 55%),
            linear-gradient(100deg, #14110d 0%, #211106 50%, #2a1408 100%);
    }
    .flash-ticker-sheen {
        background: linear-gradient(115deg, transparent 0%, rgba(255,255,255,.06) 45%, transparent 58%);
        background-size: 220% 100%;
        mix-blend-mode: overlay;
        animation: flash-ticker-sheen-sweep 7s ease-in-out infinite;
    }
    @keyframes flash-ticker-sheen-sweep {
        0%, 100% { background-position: 130% 0; }
        50%      { background-position: -30% 0; }
    }
    .flash-ticker-accent {
        background: linear-gradient(90deg, transparent, rgba(242,121,15,.85) 50%, transparent);
        background-size: 200% 100%;
        animation: flash-ticker-beam 5s ease-in-out infinite;
    }
    .flash-ticker-accent-bottom {
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.08) 50%, transparent);
    }
    @keyframes flash-ticker-beam {
        0%, 100% { background-position: 0% 0; }
        50%      { background-position: 100% 0; }
    }

    .flash-ticker-badge {
        position: relative;
        display: inline-flex; align-items: center; gap: 16px;
        padding: 10px 28px 10px 10px;
        border-radius: 999px;
        background: linear-gradient(135deg, rgba(255,255,255,.09), rgba(255,255,255,.02));
        border: 1px solid rgba(242,121,15,.35);
        box-shadow: 0 1px 0 rgba(255,255,255,.07) inset, 0 12px 30px rgba(0,0,0,.32);
    }
    .flash-ticker-badge-orb {
        position: relative;
        width: 46px; height: 46px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px;
        background: linear-gradient(135deg, #fb923c, #f2790f 65%, #c2410c);
        box-shadow: 0 0 0 1px rgba(255,255,255,.14) inset, 0 0 22px rgba(242,121,15,.7);
        flex-shrink: 0;
    }
    .flash-ticker-badge-ring {
        position: absolute; inset: -6px;
        border-radius: 999px;
        border: 1.5px solid rgba(242,121,15,.55);
        animation: flash-ticker-ripple 2.4s ease-out infinite;
    }
    .flash-ticker-badge-ring--2 { animation-delay: 1.2s; }
    @keyframes flash-ticker-ripple {
        0%   { opacity: .8; transform: scale(1); }
        100% { opacity: 0;  transform: scale(1.65); }
    }
    .flash-ticker-badge-icon {
        position: relative;
        font-size: 19px;
        color: #fff7ed;
    }
    .flash-ticker-badge-copy {
        position: relative;
        display: flex; align-items: baseline; gap: 8px;
        font-size: 18px; font-weight: 800; letter-spacing: .05em;
        white-space: nowrap;
    }
    .flash-ticker-badge-live {
        background: linear-gradient(90deg, #fed7aa, #fb923c);
        -webkit-background-clip: text; background-clip: text;
        color: transparent;
        text-transform: uppercase;
    }
    .flash-ticker-badge-title { color: rgba(255,255,255,.82); text-transform: uppercase; font-weight: 600; }

    .flash-ticker-track {
        -webkit-mask-image: linear-gradient(90deg, transparent, #000 6%, #000 94%, transparent);
                mask-image: linear-gradient(90deg, transparent, #000 6%, #000 94%, transparent);
    }

    @keyframes flash-ticker-marquee {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
    .flash-ticker-scroll {
        animation-name: flash-ticker-marquee;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
    }
    .flash-ticker-track:hover .flash-ticker-scroll { animation-play-state: paused; }

    .flash-ticker-item {
        display: inline-flex; align-items: center;
        padding: 9px 20px;
        border-radius: 999px;
        font-size: 19px; font-weight: 500;
        color: rgba(255,255,255,.92);
        text-decoration: none;
        transition: background .25s ease, color .25s ease, transform .25s ease;
    }
    a.flash-ticker-item:hover {
        color: #fff;
        background: rgba(255,255,255,.08);
        transform: translateY(-1px);
    }
    .flash-ticker-marker {
        width: 10px; height: 10px; border-radius: 999px;
        margin-right: 13px; flex-shrink: 0;
        background: #94a3b8;
        box-shadow: 0 0 0 5px rgba(148,163,184,.15);
    }
    .flash-ticker-item--info    .flash-ticker-marker { background: #38bdf8; box-shadow: 0 0 0 5px rgba(56,189,248,.18), 0 0 12px rgba(56,189,248,.55); }
    .flash-ticker-item--success .flash-ticker-marker { background: #4ade80; box-shadow: 0 0 0 5px rgba(74,222,128,.18), 0 0 12px rgba(74,222,128,.55); }
    .flash-ticker-item--warning .flash-ticker-marker { background: #fbbf24; box-shadow: 0 0 0 5px rgba(251,191,36,.18), 0 0 12px rgba(251,191,36,.55); }
    .flash-ticker-item--urgent  .flash-ticker-marker {
        background: #fb7185;
        box-shadow: 0 0 0 5px rgba(251,113,133,.18), 0 0 14px rgba(251,113,133,.75);
        animation: publicPulse 1.6s ease-in-out infinite;
    }
    .flash-ticker-sep {
        width: 2px; height: 32px;
        margin: 0 8px;
        flex-shrink: 0;
        display: inline-block;
        background: linear-gradient(180deg, transparent, rgba(255,255,255,.22), transparent);
    }

    .flash-ticker-close {
        width: 46px; height: 46px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px;
        background: rgba(255,255,255,.04);
        border: 1px solid rgba(255,255,255,.08);
        color: rgba(255,255,255,.55);
        transition: background .25s ease, color .25s ease, border-color .25s ease, transform .3s ease;
        font-size: 18px;
    }
    .flash-ticker-close:hover {
        background: rgba(242,121,15,.16);
        border-color: rgba(242,121,15,.45);
        color: #fdba74;
        transform: rotate(90deg);
    }

    @media (prefers-reduced-motion: reduce) {
        .flash-ticker-scroll,
        .flash-ticker-sheen,
        .flash-ticker-accent,
        .flash-ticker-badge-ring,
        .flash-ticker-item--urgent .flash-ticker-marker { animation: none; }
    }

    /* ── Header ──────────────────────────────────────────── */
    .public-header {
        background-image: linear-gradient(180deg, rgba(255, 255, 255,0.92) 0%, rgba(255, 255, 255,0.72) 55%, rgba(255, 255, 255,0.45) 100%), var(--header-bg-image, none);
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        backdrop-filter: blur(16px) saturate(160%);
        -webkit-backdrop-filter: blur(16px) saturate(160%);
        border-bottom: 2px solid rgba(34,197,94,0.55);
        transition: background .3s ease, border-color .3s ease, box-shadow .3s ease;
    }
    .public-header[style*="--header-bg-image"] {
        background-image: linear-gradient(180deg, rgba(255, 255, 255,0.6) 0%, rgba(255, 255, 255,0.45) 55%, rgba(255, 255, 255,0.3) 100%), var(--header-bg-image, none);
    }
    .public-header.header-scrolled {
        background-image: linear-gradient(180deg, rgba(233, 229, 217, 0.97), rgba(233, 229, 217, 0.97)), var(--header-bg-image, none) !important;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        box-shadow: 0 8px 24px rgba(0,0,0,0.06);
        border-bottom-color: rgba(34,197,94,0.65);
    }
    .public-header.header-scrolled[style*="--header-bg-image"] {
        background-image: linear-gradient(180deg, rgba(233, 229, 217, 0.65), rgba(233, 229, 217, 0.65)), var(--header-bg-image, none) !important;
    }
    .public-nav-pill {
        position: relative;
        padding: 0.5rem 0.7rem;
        border-radius: 9999px;
        font-size: 0.8125rem;
        font-weight: 500;
        letter-spacing: 0.03em;
        color: rgba(28, 25, 21, 0.72);
        transition: .2s ease;
    }
    @media (min-width: 1280px) {
        .public-nav-pill { padding: 0.5rem 0.75rem; }
    }
    .public-nav-pill:hover {
        color: #1c1915;
        background: rgba(0,0,0,0.05);
    }
    .public-nav-pill-glow::after {
        content: '';
        position: absolute;
        left: 50%;
        bottom: 2px;
        width: 0;
        height: 2px;
        border-radius: 2px;
        background: linear-gradient(90deg, #16a34a, #4ade80, #22c55e);
        transform: translateX(-50%);
        transition: width .28s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 0 12px rgba(34,197,94,0.45);
    }
    .public-nav-pill-glow:hover::after,
    .public-nav-pill-glow.is-active::after { width: 70%; }
    .public-nav-pill.is-active {
        color: #1a7038;
        background: rgba(34,197,94,0.10);
        box-shadow: inset 0 0 0 1px rgba(34,197,94,0.28);
    }
    .logo-ring {
        position: relative;
        border-radius: 1rem;
        padding: 2px;
        background: linear-gradient(135deg, rgba(34,197,94,0.55), rgba(255,255,255,0.12), rgba(34,197,94,0.25));
        box-shadow: 0 8px 32px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.05) inset;
        transition: transform .35s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow .35s ease;
        animation: logoFloat 5.4s ease-in-out infinite;
    }
    .logo-ring::before {
        content: '';
        position: absolute;
        inset: -4px;
        border-radius: inherit;
        background: conic-gradient(from 180deg, rgba(34,197,94,0.0), rgba(34,197,94,0.5), rgba(255,255,255,0.08), rgba(34,197,94,0.0));
        opacity: .45;
        filter: blur(6px);
        animation: logoHaloSpin 8s linear infinite;
        pointer-events: none;
    }
    .logo-ring-inner {
        border-radius: calc(1rem - 2px);
        overflow: hidden;
        background: rgba(233, 229, 217, 0.9);
        transition: transform .35s cubic-bezier(0.2, 0.8, 0.2, 1), filter .35s ease;
    }
    .group:hover .logo-ring {
        transform: translateY(-2px) scale(1.03);
        box-shadow: 0 14px 34px rgba(0,0,0,0.42), 0 0 26px rgba(34,197,94,0.26), 0 0 0 1px rgba(255,255,255,0.08) inset;
    }
    .group:hover .logo-ring::before { opacity: .8; }
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
        .logo-ring, .logo-ring::before { animation: none; }
    }
    .lang-switch-ultra {
        display: inline-flex;
        padding: 3px;
        border-radius: 9999px;
        background: rgba(233, 229, 217, 0.05);
        border: 1px solid rgba(255,255,255,0.08);
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.04);
    }
    .lang-switch-ultra a {
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
        background: rgba(233, 229, 217, 0.04);
        transition: border-color .2s ease, background .2s ease, color .2s ease, box-shadow .2s ease;
    }
    .btn-ghost-header:hover {
        border-color: rgba(34,197,94,0.35);
        background: rgba(34,197,94,0.08);
        color: #dcfce7;
        box-shadow: 0 0 24px rgba(34,197,94,0.1);
    }
    .btn-gold-header {
        border-radius: 9999px;
        background: linear-gradient(135deg, #4ade80 0%, #22c55e 50%, #16a34a 100%);
        box-shadow: 0 4px 20px rgba(34,197,94,0.35), inset 0 1px 0 rgba(255,255,255,0.25);
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
    }
    .btn-gold-header:hover {
        transform: translateY(-1px);
        filter: brightness(1.05);
        box-shadow: 0 8px 28px rgba(34,197,94,0.45), inset 0 1px 0 rgba(255,255,255,0.3);
    }
    #public-mobile-menu.mobile-menu-ultra {
        background: rgba(233, 229, 217, 0.98);
        backdrop-filter: blur(20px) saturate(180%);
        -webkit-backdrop-filter: blur(20px) saturate(180%);
        border-top: 1px solid rgba(34,197,94,0.12);
    }
    #public-mobile-menu.mobile-menu-ultra a {
        border-radius: 0.75rem;
        border: 1px solid transparent;
    }
    #public-mobile-menu.mobile-menu-ultra a:hover,
    #public-mobile-menu.mobile-menu-ultra a.is-active {
        border-color: rgba(34,197,94,0.2);
        background: rgba(34,197,94,0.06);
    }
    #public-mobile-menu.mobile-menu-ultra a.is-active { color: #1a7038; }

    /* ── Dropdown "Mon espace" ───────────────────────────── */
    :root {
        --dd-bg: rgba(255,253,248,0.99);
        --dd-border: rgba(0,0,0,0.09);
        --dd-shadow: 0 24px 48px -8px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,0.9);
        --dd-divider: rgba(0,0,0,0.07);
        --dd-head-gradient: rgba(242, 121, 15,0.05);
    }
    .nav-dd-lnk {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.45rem 0.625rem; border-radius: 0.625rem;
        font-size: 0.8125rem; font-weight: 500; color: #44413a;
        text-decoration: none; white-space: nowrap;
        background: none; border: none; cursor: pointer;
        transition: background .15s ease, color .15s ease;
        width: 100%; text-align: left;
    }
    .nav-dd-lnk:hover { background: rgba(0,0,0,0.04); color: #1c1915; }
    .nav-dd-lnk:hover .nav-dd-icon { background: rgba(242, 121, 15,0.14); color: #9f4709; }
    .nav-dd-icon {
        width: 1.875rem; height: 1.875rem; border-radius: 0.5rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.05); color: #544f47; font-size: 0.7rem;
        transition: background .15s ease, color .15s ease;
    }
    .nav-dd-danger { color: #dc2626; }
    .nav-dd-danger .nav-dd-icon { color: #dc2626; background: rgba(239,68,68,0.08); }
    .nav-dd-danger:hover { background: rgba(239,68,68,0.06); color: #b91c1c; }
</style>

{{-- ══════════════════════════════════════════════════════════
     TOP BAR
══════════════════════════════════════════════════════════ --}}
<div id="home-top-bar" class="public-topbar font-plus text-gray-300 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('contact_success') || session('contact_error'))
            <div class="py-2.5 text-center text-[11px] font-semibold tracking-wide border-b border-white/[0.06] {{ session('contact_success') ? 'text-emerald-400/95' : 'text-red-400/95' }}">
                {{ session('contact_success') ?? session('contact_error') }}
            </div>
        @endif
        <div class="py-3.5 flex flex-wrap items-center justify-between gap-y-3 gap-x-5 min-h-[3.25rem]">
            <div class="public-slogan-pill">
                <span class="public-pulse-dot shrink-0" aria-hidden="true"></span>
                <span class="public-slogan-text text-[11px] sm:text-xs font-semibold tracking-[0.14em] uppercase">
                    {{ $siteBrand['site_slogan'] ?: 'Magazine Culturel & Touristique Premium' }}
                </span>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2.5 sm:gap-3">
                <a href="{{ $topPhoneHref }}" class="public-topbar-action">
                    <i class="fas fa-phone-volume"></i>
                    <span>{{ $topPhoneDisplay }}</span>
                </a>
                <a href="{{ route('prestations.public') }}" class="public-topbar-action">
                    <i class="fas fa-concierge-bell"></i>
                    Nos prestations
                </a>
                <button type="button" onclick="openContactModal()" class="public-topbar-action cursor-pointer">
                    <i class="fas fa-message"></i>
                    Contact
                </button>
                @if($publicInfoGuide)
                <a href="{{ route('information.show', $publicInfoGuide) }}" class="public-topbar-action">
                    <i class="fas fa-book-open"></i>
                    Guide
                </a>
                @endif
                @if($publicInfoFaq)
                <a href="{{ route('information.show', $publicInfoFaq) }}" class="public-topbar-action">
                    <i class="fas fa-circle-question"></i>
                    FAQ
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@if(session('contact_success') || session('contact_error'))
    <div id="home-contact-flash-mobile" class="md:hidden bg-white border-b border-black/5 text-center text-[11px] py-2 px-4 font-medium {{ session('contact_success') ? 'text-emerald-600' : 'text-red-600' }}">
        {{ session('contact_success') ?? session('contact_error') }}
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════════ --}}
<header id="main-header" class="public-header font-plus sticky top-0 z-40"
    @if($headerImage && $headerImage->isVisible())
        style="--header-bg-image: url('{{ $headerImage->image_url }}');"
    @endif
>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-[4.25rem] md:h-[5.25rem] flex items-center justify-between gap-4 md:gap-8">
        <a href="{{ route('home') }}" class="flex items-center gap-3 sm:gap-3.5 shrink-0 group">
            @if(!empty($siteBrand['logo_url']))
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-12 h-12 md:w-[4rem] md:h-[4rem] flex items-center justify-center">
                        <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain p-0.5">
                    </div>
                </div>
            @else
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-12 h-12 md:w-[4rem] md:h-[4rem] flex items-center justify-center bg-linear-to-br from-green-400 to-green-600">
                        <i class="fas fa-gem text-dark-900 text-sm md:text-base drop-shadow-sm"></i>
                    </div>
                </div>
            @endif
            <div class="hidden sm:block min-w-0">
                <p class="text-transparent bg-clip-text bg-linear-to-r from-green-200 via-green-400 to-green-600 font-serif font-bold text-base md:text-lg leading-tight tracking-tight truncate group-hover:from-green-100 group-hover:to-green-300 transition-all duration-300">
                    {{ $siteBrand['site_name'] }}
                </p>
                <p class="text-gray-500 group-hover:text-gray-400 text-[9px] md:text-[10px] tracking-[0.22em] uppercase truncate font-plus font-medium mt-0.5 transition-colors">
                    {{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}
                </p>
            </div>
        </a>

        <nav class="hidden lg:flex items-center gap-0 xl:gap-0.5">
            @foreach($publicNavItems as $item)
            <a href="{{ $item['href'] }}"
               class="public-nav-pill public-nav-pill-glow whitespace-nowrap inline-flex items-center gap-1 xl:gap-1.5 {{ $item['active'] ? 'is-active' : '' }}">
                <i class="{{ $item['icon'] }} text-[11px] opacity-80 hidden 2xl:inline"></i>
                <span class="2xl:hidden">{{ $item['shortLabel'] ?? $item['label'] }}</span>
                <span class="hidden 2xl:inline">{{ $item['label'] }}</span>
            </a>
            @endforeach
            <a href="{{ route('gallery.public') }}"
               class="public-nav-pill public-nav-pill-glow whitespace-nowrap inline-flex items-center gap-1 xl:gap-1.5 {{ $publicGalleryActive ? 'is-active' : '' }}">
                <i class="fas fa-camera-retro text-[11px] opacity-80 hidden 2xl:inline"></i>
                <span class="2xl:hidden">Galerie</span>
                <span class="hidden 2xl:inline">Galerie Tresors d'Ivoire</span>
            </a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            <div class="lang-switch-ultra hidden sm:inline-flex">
                <a href="{{ route('lang.switch', 'fr') }}"
                   class="{{ session('locale', app()->getLocale()) === 'fr' ? 'bg-green-500 text-dark-900 shadow-sm' : 'text-gray-400 hover:text-white' }}">FR</a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="{{ session('locale', app()->getLocale()) === 'en' ? 'bg-green-500 text-dark-900 shadow-sm' : 'text-gray-400 hover:text-white' }}">EN</a>
            </div>

            {{-- Recherche --}}
            <a href="{{ route('search') }}"
               class="w-9 h-9 flex items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-gray-400 hover:text-green-300 hover:border-green-500/30 hover:bg-green-500/5 transition"
               title="Recherche">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </a>

            @auth
            @php $__initials = strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)); @endphp
            <div class="relative hidden sm:block" id="nav-user-dropdown-wrap">

                {{-- Trigger --}}
                <button type="button" id="nav-user-dropdown-btn"
                        class="group inline-flex items-center gap-2 pl-1.5 pr-2 2xl:pr-3.5 py-1.5 rounded-full border border-white/10 bg-white/4 hover:border-orange-500/30 hover:bg-orange-500/6 transition-all duration-200">
                    <span class="w-7 h-7 rounded-full bg-linear-to-br from-orange-400 to-orange-600 flex items-center justify-center text-[11px] font-bold text-white shadow-sm">
                        {{ $__initials }}
                    </span>
                    <span class="hidden 2xl:inline text-sm font-medium text-gray-300 group-hover:text-orange-100 transition-colors">Mon espace</span>
                    <i class="fas fa-chevron-down text-[9px] text-gray-500 group-hover:text-orange-400 transition-all duration-200" id="nav-dd-chevron"></i>
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
                                <div class="w-11 h-11 rounded-xl bg-linear-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shrink-0"
                                     style="box-shadow:0 8px 24px rgba(242, 121, 15,0.3)">
                                    {{ $__initials }}
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <p class="text-white text-sm font-semibold leading-tight truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                    <p class="text-slate-500 text-xs truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    <span class="mt-1.5 inline-flex items-center gap-1.5 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                        @if(auth()->user()->role==='admin') bg-orange-500/10 text-orange-300 border border-orange-500/20
                                        @elseif(auth()->user()->role==='editor') bg-green-500/10 text-green-300 border border-green-500/20
                                        @elseif(auth()->user()->role==='provider') bg-orange-500/10 text-orange-300 border border-orange-500/20
                                        @else bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            @if(auth()->user()->role==='admin') bg-orange-400
                                            @elseif(auth()->user()->role==='editor') bg-green-400
                                            @elseif(auth()->user()->role==='provider') bg-orange-400
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
                                    <span class="nav-dd-icon"><i class="fas fa-right-from-bracket"></i></span>
                                    Déconnexion
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
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

            <button id="public-menu-toggle" type="button"
                class="lg:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-gray-200 hover:text-white hover:border-green-500/30 hover:bg-green-500/5 transition">
                <i class="fas fa-bars-staggered text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="public-mobile-menu" class="mobile-menu-ultra lg:hidden hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 space-y-1.5 font-plus">
            @foreach($publicNavItems as $item)
            <a href="{{ $item['href'] }}" class="block px-4 py-3 text-gray-700 font-medium text-sm tracking-wide {{ $item['active'] ? 'is-active' : '' }}">
                {{ $item['label'] }}
            </a>
            @endforeach
            <a href="{{ route('gallery.public') }}"
               class="w-full text-left block px-4 py-3 text-gray-700 font-medium text-sm tracking-wide {{ $publicGalleryActive ? 'is-active' : '' }}">
                Galerie Tresors d'Ivoire
            </a>
            <div class="pt-4 mt-2 border-t border-black/10 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="text-center py-3 text-sm font-semibold text-gray-700 btn-ghost-header">Connexion</a>
                <a href="{{ route('plans.public') }}" class="text-center py-3 text-sm font-bold text-dark-900 btn-gold-header">S’abonner</a>
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════════
     FLASH INFO — bandeau défilant, géré depuis le back-office
     (Administration → Apparence → Flash info)
══════════════════════════════════════════════════════════ --}}
@if($activeFlashInfos->isNotEmpty())
@php
    $flashAllDismissible = $activeFlashInfos->every(fn ($f) => $f->is_dismissible);
    $flashIdsKey = $activeFlashInfos->pluck('id')->implode(',');
    $flashDuration = max(20, $activeFlashInfos->count() * 6);
@endphp
<div id="flash-ticker" class="flash-ticker relative z-10 overflow-hidden text-white"
     data-flash-ids="{{ $flashIdsKey }}" role="region" aria-label="Fil d'actualités">
    <div class="flash-ticker-bg absolute inset-0 pointer-events-none" aria-hidden="true"></div>
    <div class="flash-ticker-sheen absolute inset-0 pointer-events-none" aria-hidden="true"></div>
    <div class="flash-ticker-accent absolute inset-x-0 top-0 h-px pointer-events-none" aria-hidden="true"></div>
    <div class="flash-ticker-accent-bottom absolute inset-x-0 bottom-0 h-px pointer-events-none" aria-hidden="true"></div>

    <div class="relative mx-auto flex min-h-[4.5rem] max-w-[95rem] items-center gap-5 px-5 py-4 md:min-h-[5.75rem] md:gap-8 md:px-10">
        <div class="shrink-0">
            <span class="flash-ticker-badge">
                <span class="flash-ticker-badge-orb">
                    <span class="flash-ticker-badge-ring"></span>
                    <span class="flash-ticker-badge-ring flash-ticker-badge-ring--2"></span>
                    <i class="fas fa-tower-broadcast flash-ticker-badge-icon"></i>
                </span>
                <span class="flash-ticker-badge-copy">
                    <span class="flash-ticker-badge-live">Flash</span>
                    <span class="flash-ticker-badge-title">Info</span>
                </span>
            </span>
        </div>

        <div class="flash-ticker-track relative min-w-0 flex-1 overflow-hidden">
            <div class="flash-ticker-scroll flex w-max items-center whitespace-nowrap" style="animation-duration: {{ $flashDuration }}s">
                @for ($pass = 0; $pass < 2; $pass++)
                    @foreach($activeFlashInfos as $flash)
                        @if($flash->link_url)
                        <a href="{{ $flash->link_url }}" class="flash-ticker-item flash-ticker-item--{{ $flash->type }}">
                            <span class="flash-ticker-marker"></span>{{ $flash->message }}
                        </a>
                        @else
                        <span class="flash-ticker-item flash-ticker-item--{{ $flash->type }}">
                            <span class="flash-ticker-marker"></span>{{ $flash->message }}
                        </span>
                        @endif
                        <span class="flash-ticker-sep" aria-hidden="true"></span>
                    @endforeach
                @endfor
            </div>
        </div>

        @if($flashAllDismissible)
        <button type="button" class="flash-ticker-close shrink-0" onclick="dismissFlashTicker()" aria-label="Fermer le fil d'actualités">
            <i class="fas fa-xmark"></i>
        </button>
        @endif
    </div>
</div>
<script>
    (function () {
        var el = document.getElementById('flash-ticker');
        if (!el) return;
        var storageKey = 'flashTickerDismissed';
        var currentIds = el.dataset.flashIds || '';

        window.dismissFlashTicker = function () {
            el.remove();
            try { localStorage.setItem(storageKey, currentIds); } catch (e) { /* indisponible : ignoré */ }
        };

        // Exécuté immédiatement pour éviter tout flash si le visiteur avait déjà
        // fermé exactement ce même jeu de messages lors d'une visite précédente.
        try {
            if (localStorage.getItem(storageKey) === currentIds) el.remove();
        } catch (e) { /* localStorage indisponible : on ignore */ }
    })();
</script>
@endif

{{-- ══════════════════════════════════════════════════════════
     MODAL DE CONTACT
══════════════════════════════════════════════════════════ --}}
<div id="contact-modal" class="hidden fixed inset-0 z-[60] p-4 bg-black/40 backdrop-blur-sm" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
    <div class="absolute inset-0" onclick="closeContactModal()" aria-hidden="true"></div>
    <div class="relative w-full max-w-lg max-h-[90vh] overflow-y-auto rounded-2xl border border-black/10 bg-white shadow-2xl">
        <div class="sticky top-0 flex items-center justify-between gap-4 px-5 py-4 border-b border-black/10 bg-white/95 backdrop-blur">
            <div>
                <p id="contact-modal-title" class="font-serif text-lg text-gray-900 font-semibold flex items-center gap-2">
                    <i class="fas fa-pen-to-square text-green-500 text-sm"></i>
                    Envoyez-nous un message
                </p>
                <p class="text-gray-500 text-xs mt-0.5">Nous vous répondrons dans les plus brefs délais.</p>
            </div>
            <button type="button" onclick="closeContactModal()" class="shrink-0 w-9 h-9 flex items-center justify-center rounded-lg border border-black/10 text-gray-400 hover:text-gray-900 hover:bg-black/5 transition" aria-label="Fermer">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <form method="post" action="{{ route('contact.store') }}" class="p-5 space-y-4">
            @csrf
            <div>
                <label for="contact-name" class="block text-xs font-medium text-gray-500 mb-1.5">Nom complet</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3 top-1/2 -translate-y-1/2 text-green-500/60 text-xs"></i>
                    <input id="contact-name" name="name" type="text" required maxlength="255" value="{{ old('name') }}"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-white border border-black/10 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500/50"
                        placeholder="Jean Dupont" autocomplete="name">
                </div>
                @error('name')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-email" class="block text-xs font-medium text-gray-500 mb-1.5">E-mail</label>
                <div class="relative">
                    <i class="fas fa-at absolute left-3 top-1/2 -translate-y-1/2 text-green-500/60 text-xs"></i>
                    <input id="contact-email" name="email" type="email" required maxlength="255" value="{{ old('email') }}"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-white border border-black/10 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500/50"
                        placeholder="vous@exemple.ci" autocomplete="email">
                </div>
                @error('email')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-subject" class="block text-xs font-medium text-gray-500 mb-1.5">Objet</label>
                <div class="relative">
                    <i class="fas fa-circle-notch absolute left-3 top-1/2 -translate-y-1/2 text-green-500/60 text-[10px]"></i>
                    <input id="contact-subject" name="subject" type="text" required maxlength="255" value="{{ old('subject') }}"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-white border border-black/10 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500/50"
                        placeholder="Sujet de votre message">
                </div>
                @error('subject')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-message" class="block text-xs font-medium text-gray-500 mb-1.5">Message</label>
                <div class="relative">
                    <i class="fas fa-comment-dots absolute left-3 top-3 text-green-500/60 text-xs"></i>
                    <textarea id="contact-message" name="message" rows="5" required maxlength="5000"
                        class="w-full pl-9 pr-3 py-2.5 rounded-lg bg-white border border-black/10 text-sm text-gray-900 placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500/30 focus:border-green-500/50 resize-y min-h-[120px]"
                        placeholder="Comment pouvons-nous vous aider ?">{{ old('message') }}</textarea>
                </div>
                @error('message')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-green-500 hover:bg-green-400 text-white font-bold text-sm transition shadow-lg shadow-green-500/20">
                <i class="fas fa-paper-plane text-xs"></i>
                Envoyer le message
            </button>
        </form>
    </div>
</div>
@if($errors->hasAny(['name', 'email', 'subject', 'message']))
    <span id="contact-modal-autoopen" class="hidden" aria-hidden="true"></span>
@endif

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
        if (btn && dropdown) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !dropdown.classList.contains('hidden');
                dropdown.classList.toggle('hidden', isOpen);
                chevron?.classList.toggle('rotate-180', !isOpen);
            });
            document.addEventListener('click', (e) => { if (!wrap?.contains(e.target)) { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); } });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); } });
        }

        // Header scroll effect
        const header = document.getElementById('main-header');
        function syncHeaderScroll() {
            if (!header) return;
            if (window.scrollY > 40) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
        }
        window.addEventListener('scroll', syncHeaderScroll, { passive: true });
        syncHeaderScroll();

        // Contact modal
        window.openContactModal = function () {
            const el = document.getElementById('contact-modal');
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex', 'items-center', 'justify-center');
            document.body.style.overflow = 'hidden';
        };
        window.closeContactModal = function () {
            const el = document.getElementById('contact-modal');
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex', 'items-center', 'justify-center');
            document.body.style.overflow = '';
        };
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') window.closeContactModal(); });
        if (document.getElementById('contact-modal-autoopen')) window.openContactModal();
    })();
</script>
