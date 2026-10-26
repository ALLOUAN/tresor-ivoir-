<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>@yield('title', 'Tableau de bord') — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            color: #8AA99B;
            font-size: 0.875rem;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .sidebar-link:hover { background:#D8E9E0; color: #fff; }
        .sidebar-link.active {
            background: rgba(235,110,0, 0.12);
            color: #EB6E00;
            font-weight: 500;
        }
        /* ——— Sidebar administrateur ——— */
        .admin-sidebar {
            width: 18rem;
            background:#D8E9E0;
            border-right: 1px solid rgba(235,110,0, 0.12);
            box-shadow: 4px 0 24px rgba(14,59,42, 0.35);
        }
        .ti-side-logo { display: flex; align-items: center; justify-content: center; padding: 10px 14px; border-radius: 14px; background: #fff; }
        .ti-side-logo img { display: block; width: 100%; max-width: 190px; height: auto; }
        .admin-sidebar-brand {
            background: rgba(235,110,0, 0.04);
            border-bottom: 1px solid rgba(138,169,155, 0.12);
        }
        .admin-sidebar .nav-section-title {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #557466;
            padding: 1rem 0.75rem 0.4rem;
            margin-top: 0.15rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        .admin-sidebar .nav-section-title::after {
            content: '';
            flex: 1;
            height: 1px;
            order: 1;
            background: rgba(85,116,102, 0.23);
        }
        /* Groupes repliables : le titre est cliquable, le chevron tourne à l'ouverture. */
        .admin-sidebar .nav-group > summary { list-style: none; cursor: pointer; user-select: none; border-radius: .6rem; transition: color .15s ease; }
        .admin-sidebar .nav-group > summary::-webkit-details-marker { display: none; }
        .admin-sidebar .nav-group > summary:hover { color: #EB6E00; }
        .admin-sidebar .nav-group > summary:focus-visible { outline: 2px solid #EB6E00; outline-offset: 1px; }
        .admin-sidebar .nav-chevron { order: 2; font-size: .62rem; transition: transform .2s ease; transform: rotate(-90deg); }
        .admin-sidebar .nav-group[open] > summary .nav-chevron { transform: rotate(0deg); }
        .admin-sidebar .nav-group-body { display: grid; gap: .125rem; padding-bottom: .25rem; }
        @media (prefers-reduced-motion: reduce) { .admin-sidebar .nav-chevron { transition: none; } }
        .admin-sidebar .nav-row {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.45rem 0.65rem 0.45rem 0.5rem;
            border-radius: 0.75rem;
            color: #8AA99B;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
            margin-bottom: 1px;
        }
        .admin-sidebar .nav-row:hover {
            background: rgba(216,233,224, 0.04);
            color: #F3F8F5;
        }
        .admin-sidebar .nav-row.is-active {
            font-weight: 600;
            background: rgba(235,110,0, 0.08);
            color: #F7C396;
            box-shadow: inset 3px 0 0 #EB6E00;
        }
        .admin-sidebar .nav-row-icon {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.55rem;
            background: rgba(216,233,224, 0.85);
            border: 1px solid rgba(36,80,61, 0.35);
            flex-shrink: 0;
            font-size: 0.8rem;
            transition: inherit;
        }
        .admin-sidebar .nav-row:hover .nav-row-icon {
            background: rgba(36,80,61, 0.95);
            border-color: rgba(85,116,102, 0.45);
            color: #e2e8f0;
        }
        .admin-sidebar .nav-row.is-active .nav-row-icon {
            background: rgba(235,110,0, 0.18);
            border-color: rgba(235,110,0, 0.35);
            color: #EB6E00;
        }
        .admin-sidebar .nav-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(235,110,0, 0.35) transparent;
        }
        .admin-sidebar .role-pill {
            background: rgba(184,86,0, 0.3);
            border: 1px solid rgba(235,110,0, 0.25);
            box-shadow: 0 0 20px rgba(235,110,0, 0.12);
        }
        .admin-sidebar-footer {
            background: rgba(255, 255, 255, 0.45);
            border-top: 1px solid rgba(138,169,155, 0.12);
        }
        .admin-sidebar .user-card {
            border-radius: 0.75rem;
            padding: 0.65rem 0.75rem;
            background: rgba(216,233,224, 0.55);
            border: 1px solid rgba(36,80,61, 0.35);
        }
        .admin-sidebar .user-avatar {
            box-shadow: 0 0 0 2px rgba(235,110,0, 0.35), 0 4px 12px rgba(14,59,42, 0.25);
        }
        /* ── Light mode: body & fond ──────────────────── */
        html:not(.dark) body                    { background-color:#D8E9E0!important; color:#0E3B2A!important; }
        html:not(.dark) .text-white             { color:#0E3B2A!important; }
        html:not(.dark) .text-slate-100         { color:#0E3B2A!important; }
        html:not(.dark) .text-slate-200         { color:#0E3B2A!important; }
        html:not(.dark) .text-slate-300         { color:#24503D!important; }
        html:not(.dark) .text-slate-400         { color:#24503D!important; }
        html:not(.dark) .text-slate-500         { color:#557466!important; }
        html:not(.dark) .text-slate-600         { color:#24503D!important; }
        html:not(.dark) .bg-green-950           { background-color:#D8E9E0!important; }
        html:not(.dark) .bg-green-900           { background-color:#D8E9E0!important; }
        html:not(.dark) .bg-slate-800           { background-color:#F3F8F5!important; }
        html:not(.dark) .bg-slate-700           { background-color:#D8E9E0!important; }
        html:not(.dark) .border-slate-800       { border-color:#8AA99B!important; }
        html:not(.dark) .border-slate-700       { border-color:#8AA99B!important; }
        html:not(.dark) .border-slate-700\/70   { border-color:rgba(14,59,42,0.1)!important; }
        html:not(.dark) .bg-green-900\/80       { background-color:rgba(216,233,224, 0.97)!important; }
        html:not(.dark) .bg-slate-800\/70       { background-color:rgba(14,59,42,0.05)!important; }
        html:not(.dark) .bg-slate-700\/70       { background-color:rgba(14,59,42,0.06)!important; }
        html:not(.dark) .hover\:bg-slate-700\/70:hover { background-color:rgba(14,59,42,0.08)!important; }
        /* ── Light mode: sidebar non-admin ──────────── */
        html:not(.dark) .sidebar-link              { color:#24503D; }
        html:not(.dark) .sidebar-link:hover        { background:rgba(14,59,42,0.04); color:#0E3B2A; }
        html:not(.dark) .sidebar-link.active       { background:rgba(235,110,0,0.1); color:#B85600; }
        /* ── Light mode: sidebar admin ───────────────── */
        html:not(.dark) .admin-sidebar {
            background:#D8E9E0;
            border-right-color:#8AA99B;
            box-shadow:4px 0 24px rgba(14,59,42,0.05);
        }
        html:not(.dark) .admin-sidebar-brand {
            background:rgba(235,110,0, 0.03);
            border-bottom-color:rgba(14,59,42,0.07);
        }
        html:not(.dark) .admin-sidebar .nav-section-title { color:#557466; }
        html:not(.dark) .admin-sidebar .nav-section-title::after {
            background:rgba(14,59,42, 0.07);
        }
        html:not(.dark) .admin-sidebar .nav-row                   { color:#24503D; }
        html:not(.dark) .admin-sidebar .nav-row:hover             { background:rgba(14,59,42,0.04); color:#0E3B2A; }
        html:not(.dark) .admin-sidebar .nav-row.is-active         { background:rgba(235,110,0, 0.07); color:#B85600; box-shadow:inset 3px 0 0 #EB6E00; }
        html:not(.dark) .admin-sidebar .nav-row-icon              { background:rgba(14,59,42,0.05); border-color:rgba(14,59,42,0.1); color:#24503D; }
        html:not(.dark) .admin-sidebar .nav-row:hover .nav-row-icon { background:rgba(14,59,42,0.08); border-color:rgba(14,59,42,0.14); color:#0E3B2A; }
        html:not(.dark) .admin-sidebar .nav-row.is-active .nav-row-icon { background:rgba(235,110,0,0.14); border-color:rgba(235,110,0,0.3); color:#B85600; }
        html:not(.dark) .admin-sidebar-footer { background:rgba(255, 255, 255, 0.45); border-top-color:rgba(14,59,42,0.07); }
        html:not(.dark) .admin-sidebar .user-card { background:rgba(14,59,42,0.04); border-color:rgba(14,59,42,0.1); }
        html:not(.dark) .admin-sidebar .user-avatar { box-shadow:0 0 0 2px rgba(235,110,0,0.3),0 4px 12px rgba(14,59,42,0.08); }
        html:not(.dark) .admin-sidebar .role-pill { background:rgba(184,86,0, 0.09); border-color:rgba(235,110,0,0.2); box-shadow:none; }
        html:not(.dark) .admin-sidebar .nav-scroll { scrollbar-color:rgba(235,110,0,0.2) transparent; }
        /* ── Light mode: top bar ─────────────────────── */
        html:not(.dark) header.sticky { border-bottom-color:#8AA99B!important; }
        /* ── Light mode: content cards ───────────────── */
        html:not(.dark) .bg-white\/5  { background-color:rgba(14,59,42,0.04)!important; }
        html:not(.dark) .border-white\/10 { border-color:rgba(14,59,42,0.1)!important; }
        html:not(.dark) .divide-slate-800>*+* { border-color:#8AA99B!important; }
        /* ── Light mode: toast messages ──────────────── */
        html:not(.dark) .bg-emerald-500\/15 { background-color:rgba(216,233,224,0.6)!important; }
        html:not(.dark) .border-emerald-400\/40 { border-color:rgba(0,135,79,0.4)!important; }
        html:not(.dark) .bg-red-900\/40 { background-color:rgba(255,228,228,0.6)!important; }

        /* Menu utilisateur (barre du haut) */
        /* Aux couleurs du site : vert forêt, orange ivoirien, ambre, crème. */
        .um { --um-forest: #00874F; --um-deep: #00874F; --um-teal: #00874F; --um-orange: #EB6E00; --um-amber: #EB6E00; --um-ink: #0E3B2A; --um-soft: #24503D; --um-line: #D8E9E0; --um-cream: #F3F8F5; position: relative; }
        .um-trigger { display: flex; align-items: center; gap: 10px; height: 46px; padding: 0 14px 0 4px; border: 1px solid var(--um-line); border-radius: 999px; background: #fff; color: var(--um-ink); cursor: pointer; transition: border-color .2s ease, box-shadow .2s ease, background .2s ease; }
        .um-trigger:hover, .um.is-open .um-trigger { background: var(--um-cream); border-color: #F7C396; box-shadow: 0 10px 24px -16px rgba(0,135,79,.55); }
        .um-trigger:focus-visible { outline: 3px solid rgba(235,110,0,.45); outline-offset: 2px; }
        .um-av { position: relative; width: 38px; height: 38px; flex: none; padding: 2px; border-radius: 999px; background: var(--um-orange); }
        .um-av > span { width: 100%; height: 100%; border-radius: inherit; border: 2px solid #fff; background: var(--um-forest); color: #fff; display: flex; align-items: center; justify-content: center; overflow: hidden; font-size: 12.5px; font-weight: 800; letter-spacing: .04em; }
        .um-av img { width: 100%; height: 100%; object-fit: cover; }
        .um-av::after { content: ''; position: absolute; right: 0; bottom: 0; width: 10px; height: 10px; border-radius: 999px; background: #00874F; border: 2px solid #fff; }
        .um-av.is-lg { width: 58px; height: 58px; }
        .um-av.is-lg > span { font-size: 19px; border-color: rgba(255,255,255,.9); }
        .um-av.is-lg::after { width: 13px; height: 13px; border-color: var(--um-deep); }
        .um-who { display: flex; flex-direction: column; align-items: flex-start; line-height: 1.15; text-align: left; max-width: 170px; }
        .um-who b { max-width: 100%; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 13.5px; font-weight: 800; color: var(--um-ink); }
        .um-who small { margin-top: 2px; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; color: var(--um-orange); }
        .um-chev { width: 24px; height: 24px; border-radius: 999px; background: #F3F8F5; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; color: var(--um-soft); transition: transform .25s ease, background .2s ease; }
        .um.is-open .um-chev { transform: rotate(180deg); background: var(--um-amber); color: var(--um-ink); }

        .um-panel { position: absolute; right: 0; top: calc(100% + 10px); z-index: 60; width: 320px; max-width: calc(100vw - 24px); padding: 8px; border: 1px solid var(--um-line); border-radius: 24px; background: rgba(255,255,255,.96); backdrop-filter: blur(14px); box-shadow: 0 30px 60px -28px rgba(14,59,42,.45), 0 2px 6px rgba(14,59,42,.05); transform-origin: top right; animation: um-in .22s cubic-bezier(.2,.9,.3,1.2); }
        .um-panel[hidden] { display: none; }
        @keyframes um-in { from { opacity: 0; transform: translateY(-6px) scale(.96); } to { opacity: 1; transform: none; } }
        /* En-tête : aplat vert forêt + motifs ivoiriens au trait (partials.ivorian-motifs), lisière kita en bas. */
        .um-head { position: relative; overflow: hidden; display: flex; align-items: center; gap: 14px; padding: 18px 16px 26px; border-radius: 18px; color: #fff; background: #00874F; }
        .um-head .ivm-g { opacity: .2 !important; }
        .um-head .ivm-g > * { stroke: #F7C396 !important; }
        .um-head .ivm-band { height: 8px; }
        .um-head > .um-av, .um-head > .um-head__txt { position: relative; z-index: 2; }
        .um-head__txt { min-width: 0; }
        .um-head__txt b { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 16px; font-weight: 800; letter-spacing: -.01em; color: #fff; }
        .um-head__txt small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px; font-size: 12.5px; color: rgba(255,255,255,.75); }
        .um-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 9px; padding: 4px 10px; border-radius: 999px; background: var(--um-amber); color: var(--um-ink); font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
        .um-badge i { font-size: 10px; }
        .um-list { display: grid; gap: 2px; padding: 8px 0 4px; }
        .um-sep { height: 1px; margin: 4px 10px; background: var(--um-line); }
        .um-item { --t: 0,135,79; display: flex; align-items: center; gap: 12px; width: 100%; padding: 8px 10px; border: 0; border-radius: 14px; background: none; color: var(--um-ink); text-align: left; text-decoration: none; cursor: pointer; transition: background .18s ease; }
        .um-ic { width: 38px; height: 38px; flex: none; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: rgba(var(--t), .1); color: rgb(var(--t)); font-size: 15px; transition: background .18s ease, color .18s ease, transform .18s ease; }
        .um-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.2; }
        .um-txt b { font-size: 14px; font-weight: 700; }
        .um-txt small { margin-top: 2px; font-size: 12px; color: var(--um-soft); }
        .um-go { font-size: 11px; color: rgb(var(--t)); opacity: 0; transform: translateX(-6px); transition: opacity .18s ease, transform .18s ease; }
        .um-item:hover, .um-item:focus-visible { background: rgba(var(--t), .07); outline: none; }
        .um-item:hover .um-ic, .um-item:focus-visible .um-ic { background: rgb(var(--t)); color: #fff; transform: rotate(-6deg); }
        .um-item:hover .um-go, .um-item:focus-visible .um-go { opacity: 1; transform: none; }
        .um-out { display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 44px; margin-top: 6px; border: 1px solid #f5c9cf; border-radius: 14px; background: #FFFFFF; color: #be123c; font-size: 14px; font-weight: 800; cursor: pointer; transition: background .18s ease, color .18s ease, border-color .18s ease; }
        .um-out:hover, .um-out:focus-visible { background: #e11d48; border-color: #e11d48; color: #fff; outline: none; }
        body.adm header.sticky .um-out { color: #be123c !important; }
        body.adm header.sticky .um-out:hover, body.adm header.sticky .um-out:focus-visible { color: #fff !important; }

        html.dark .um { --um-ink: #F3F8F5; --um-soft: #8AA99B; --um-line: #00874F; --um-cream: #00874F; }
        html.dark .um-trigger, html.dark .um-panel { background: #006B3F; }
        html.dark .um-chev { background: rgba(255,255,255,.08); }
        html.dark .um-av > span, html.dark .um-av::after { border-color: #006B3F; }
        html.dark .um-out { background: rgba(225,29,72,.12); border-color: rgba(225,29,72,.35); color: #fda4af; }
        @media (max-width: 640px) {
            .um-trigger { padding: 0 4px; gap: 0; border-color: transparent; background: transparent; }
            .um-who, .um-chev { display: none; }
            .um-panel { position: fixed; top: 64px; right: 12px; left: 12px; width: auto; max-width: none; }
        }
        @media (prefers-reduced-motion: reduce) { .um-panel { animation: none; } .um-ic, .um-go, .um-chev { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
    @php $admShell = in_array(auth()->user()?->role, ['admin', 'provider'], true); @endphp
    @if($admShell)
        @include('partials.admin-theme')
    @endif
</head>
<body class="h-full bg-green-950 text-white flex {{ $admShell ? 'adm' : '' }}{{ auth()->user()?->role === 'provider' ? ' adm-provider' : '' }}">
    @php
        $role = auth()->user()->role;
        $sidebarMessagingUnreadCount = 0;
        $sidebarClientMessagingUnreadCount = 0;
        $sidebarConversationReportsPendingCount = 0;
        if ($role === 'admin') {
            $sidebarMessagingUnreadCount = \App\Models\ProviderConversation::query()
                ->whereHas('messages', fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', auth()->id()))
                ->count();
            $sidebarConversationReportsPendingCount = \App\Models\ConversationReport::where('status', 'pending')->count();
        } elseif ($role === 'provider') {
            /** @var \App\Models\User $authUser */
            $authUser = auth()->user();
            $provider = \App\Models\Provider::forUserQuery($authUser->id)->with('category.parent')->first();
            if ($provider) {
                $sidebarMessagingUnreadCount = \App\Models\ProviderConversation::query()
                    ->where('provider_id', $provider->id)
                    ->whereHas('messages', fn ($q) => $q
                        ->whereNull('read_at')
                        ->where('sender_id', '!=', auth()->id()))
                    ->count();

                $sidebarClientMessagingUnreadCount = \App\Models\Conversation::query()
                    ->where('provider_id', $provider->id)
                    ->whereHas('messages', fn ($q) => $q
                        ->whereNull('read_at')
                        ->where('sender_id', '!=', auth()->id()))
                    ->count();

                $providerCategoryRootSlug = $provider->category?->parent_id
                    ? $provider->category?->parent?->slug
                    : $provider->category?->slug;
                // Prestataire multi-secteurs : chaque secteur exercé ouvre son espace dans le menu.
                $providerSectorSlugs = $provider->sectorSlugs();
                $providerIsAccommodationCategory = in_array('hotels', $providerSectorSlugs, true);
                $providerIsArtCategory = in_array('art-creations', $providerSectorSlugs, true);
                $providerIsRestaurantCategory = in_array('restaurants', $providerSectorSlugs, true);
                $providerIsTravelAgencyCategory = in_array('agences-voyages', $providerSectorSlugs, true);
                $providerIsLeisureCategory = (bool) array_intersect(['loisirs-culture', 'sites-touristiques'], $providerSectorSlugs);
                $providerIsLeisureVenueCategory = in_array('loisirs-culture', $providerSectorSlugs, true);
                $providerIsTouristExperienceCategory = in_array('sites-touristiques', $providerSectorSlugs, true);
                $providerIsTransportCategory = in_array('transports', $providerSectorSlugs, true);
            }
        } elseif ($role === 'visitor') {
            $sidebarClientMessagingUnreadCount = \App\Models\Conversation::query()
                ->where('client_id', auth()->id())
                ->whereHas('messages', fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', auth()->id()))
                ->count();
        }
    @endphp
    @if(session('success'))
        <div id="success-toast"
             class="fixed top-4 right-4 z-[80] max-w-sm w-[calc(100%-2rem)] sm:w-auto px-4 py-3 rounded-lg border border-emerald-400/40 bg-emerald-500/15 text-emerald-200 shadow-2xl shadow-emerald-900/30 flex items-start gap-2 transition-opacity duration-300">
            <i class="fas fa-circle-check mt-0.5"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- ══════════════ SIDEBAR ══════════════ --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300
            {{ $admShell ? 'admin-sidebar' : 'w-64 bg-green-900 border-r border-slate-800' }}">
        @if($admShell)
            @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
        @endif

        {{-- Brand --}}
        {{-- Logo officiel sur bandeau blanc (charte : sur fond coloré non uni, logo dans un bandeau blanc ; jamais recomposé). --}}
        <div class="px-4 py-4 border-b border-slate-800 {{ $admShell ? 'admin-sidebar-brand !border-b-0' : '' }}">
            <a href="{{ route('home') }}" class="ti-side-logo" aria-label="{{ $siteBrand['site_name'] }}">
                <img src="{{ $siteBrand['logo_compact_url'] ?? $siteBrand['logo_url'] }}" alt="{{ $siteBrand['site_name'] }}" width="1200" height="369">
            </a>
        </div>

        {{-- Role badge --}}
        <div class="px-5 py-3 border-b border-slate-800 {{ $admShell ? '!border-slate-700/50' : '' }}">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                {{ $admShell ? 'role-pill text-orange-100' :
                  ($role === 'editor'   ? 'bg-green-900/50 text-green-300'    :
                  ($role === 'provider' ? 'bg-orange-900/50 text-orange-300' :
                                         'bg-emerald-900/50 text-emerald-300')) }}">
                <i class="fas
                    {{ $role === 'admin'    ? 'fa-shield-halved' :
                      ($role === 'editor'   ? 'fa-pen-nib'       :
                      ($role === 'provider' ? 'fa-store'         :
                                             'fa-user')) }}
                    text-[10px]"></i>
                {{ ucfirst($role === 'provider' ? 'prestataire' : ($role === 'editor' ? 'éditeur' : $role)) }}
            </span>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5 {{ $admShell ? 'nav-scroll' : '' }}">

            @if($role === 'admin')
                <details class="nav-group" data-nav-group="pilotage" open>
                <summary class="nav-section-title"><span>Pilotage</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-row {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-gauge-high"></i></span>
                    <span>Tableau de bord</span>
                </a>
                <a href="{{ route('admin.analytics.index') }}"
                   class="nav-row {{ request()->routeIs('admin.analytics.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-chart-line"></i></span>
                    <span>Analytics</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="communaute" open>
                <summary class="nav-section-title"><span>Communauté</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.users.index') }}"
                   class="nav-row {{ request()->routeIs('admin.users.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-users"></i></span>
                    <span>Utilisateurs</span>
                </a>
                <a href="{{ route('admin.providers.index') }}"
                   class="nav-row {{ request()->routeIs('admin.providers.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-store"></i></span>
                    <span>Prestataires</span>
                </a>
                <a href="{{ route('admin.provider-verifications.index') }}"
                   class="nav-row {{ request()->routeIs('admin.provider-verifications.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-id-card"></i></span>
                    <span>Vérifications</span>
                </a>
                <a href="{{ route('admin.reviews.index') }}"
                   class="nav-row {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-star"></i></span>
                    <span>Avis</span>
                </a>
                <a href="{{ route('admin.permissions') }}"
                   class="nav-row {{ request()->routeIs('admin.permissions') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-key"></i></span>
                    <span>Rôles &amp; permissions</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="echanges" open>
                <summary class="nav-section-title"><span>Échanges</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.conversations.index') }}"
                   class="nav-row {{ request()->routeIs('admin.conversations.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-comments"></i></span>
                    <span class="flex-1">Messagerie</span>
                    @if($sidebarMessagingUnreadCount > 0)
                        <span class="inline-flex min-w-[1.35rem] h-[1.35rem] px-1.5 items-center justify-center rounded-full bg-orange-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarMessagingUnreadCount > 99 ? '99+' : $sidebarMessagingUnreadCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.conversation-reports.index') }}"
                   class="nav-row {{ request()->routeIs('admin.conversation-reports.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-flag"></i></span>
                    <span class="flex-1">Signalements messagerie</span>
                    @if($sidebarConversationReportsPendingCount > 0)
                        <span class="inline-flex min-w-[1.35rem] h-[1.35rem] px-1.5 items-center justify-center rounded-full bg-orange-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarConversationReportsPendingCount > 99 ? '99+' : $sidebarConversationReportsPendingCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('admin.administration.contact-messages.index') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.contacts*', 'admin.administration.contact-messages*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-address-book"></i></span>
                    <span>Contacts</span>
                </a>
                <a href="{{ route('admin.newsletter.index') }}"
                   class="nav-row {{ request()->routeIs('admin.newsletter.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-envelope-open-text"></i></span>
                    <span>Newsletter</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="magazine-medias" open>
                <summary class="nav-section-title"><span>Magazine &amp; médias</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.articles.index') }}"
                   class="nav-row {{ request()->routeIs('admin.articles.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-newspaper"></i></span>
                    <span>Articles</span>
                </a>
                <a href="{{ route('admin.events.index') }}"
                   class="nav-row {{ request()->routeIs('admin.events.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-calendar-days"></i></span>
                    <span>Événements</span>
                </a>
                <a href="{{ route('admin.categories.articles') }}"
                   class="nav-row {{ request()->routeIs('admin.categories.articles*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-tags"></i></span>
                    <span>Catégories &amp; tags</span>
                </a>
                <a href="{{ route('admin.administration.media') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.media*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-images"></i></span>
                    <span>Galerie photo</span>
                </a>
                <a href="{{ route('admin.videos.index') }}"
                   class="nav-row {{ request()->routeIs('admin.videos.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-film"></i></span>
                    <span>Vidéos</span>
                    @php $__pendingVideos = \Illuminate\Support\Facades\Schema::hasTable('videos') ? \App\Models\Video::where('status', 'pending')->count() : 0; @endphp
                    @if($__pendingVideos)<span class="ml-auto px-1.5 min-w-5 h-5 rounded-full bg-orange-500 text-black text-[11px] font-bold inline-flex items-center justify-center">{{ $__pendingVideos }}</span>@endif
                </a>
                <a href="{{ route('admin.films.index') }}"
                   class="nav-row {{ request()->routeIs('admin.films.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-clapperboard"></i></span>
                    <span>Cinéma d'Ivoire</span>
                    @php $__pendingFilms = \Illuminate\Support\Facades\Schema::hasTable('films') ? \App\Models\Film::where('status', 'pending')->count() : 0; @endphp
                    @if($__pendingFilms)<span class="ml-auto px-1.5 min-w-5 h-5 rounded-full bg-orange-500 text-black text-[11px] font-bold inline-flex items-center justify-center">{{ $__pendingFilms }}</span>@endif
                </a>
                <a href="{{ route('admin.comics.index') }}"
                   class="nav-row {{ request()->routeIs('admin.comics.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-book-open"></i></span>
                    <span>Astuces de Mian (BD)</span>
                </a>
                <a href="{{ route('admin.home-banners.index') }}"
                   class="nav-row {{ request()->routeIs('admin.home-banners.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-rectangle-ad"></i></span>
                    <span>Bannières publicitaires</span>
                </a>
                <a href="{{ route('admin.homepage-bubbles.index') }}"
                   class="nav-row {{ request()->routeIs('admin.homepage-bubbles.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-circle-dot"></i></span>
                    <span>Bulles interactives</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="destinations-culture" open>
                <summary class="nav-section-title"><span>Destinations &amp; culture</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.tourist.cities.index') }}"
                   class="nav-row {{ request()->routeIs('admin.tourist.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-map-location-dot"></i></span>
                    <span>Régions Touristiques</span>
                </a>
                <a href="{{ route('admin.tourist-visits.index') }}"
                   class="nav-row {{ request()->routeIs('admin.tourist-visits.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-binoculars"></i></span>
                    <span>Visites touristiques</span>
                </a>
                <a href="{{ route('admin.cultural.peoples.index') }}"
                   class="nav-row {{ request()->routeIs('admin.cultural.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-masks-theater"></i></span>
                    <span>Cultures Ivoiriennes</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="offres-services" open>
                <summary class="nav-section-title"><span>Offres &amp; services</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.accommodations.index') }}"
                   class="nav-row {{ request()->routeIs('admin.accommodations.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-hotel"></i></span>
                    <span>Hébergements</span>
                </a>
                <a href="{{ route('admin.menu.index') }}"
                   class="nav-row {{ request()->routeIs('admin.menu.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-utensils"></i></span>
                    <span>Restaurants</span>
                </a>
                <a href="{{ route('admin.activities.index') }}"
                   class="nav-row {{ request()->routeIs('admin.activities.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-person-hiking"></i></span>
                    <span>Loisirs &amp; Culture</span>
                </a>
                <a href="{{ route('admin.transport.index') }}"
                   class="nav-row {{ request()->routeIs('admin.transport.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-car-side"></i></span>
                    <span>Transports</span>
                </a>
                <a href="{{ route('admin.tours.index') }}"
                   class="nav-row {{ request()->routeIs('admin.tours.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-route"></i></span>
                    <span>Agences de Voyages</span>
                </a>
                <a href="{{ route('admin.prestations.index') }}"
                   class="nav-row {{ request()->routeIs('admin.prestations.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-concierge-bell"></i></span>
                    <span>Nos Prestations</span>
                </a>
                <a href="{{ route('admin.artworks.index') }}"
                   class="nav-row {{ request()->routeIs('admin.artworks.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-palette"></i></span>
                    <span>Art &amp; Créations</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="reservations-ventes" open>
                <summary class="nav-section-title"><span>Réservations &amp; ventes</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.reservations.index') }}"
                   class="nav-row {{ request()->routeIs('admin.reservations.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-calendar-check"></i></span>
                    <span>Réservations</span>
                </a>
                <a href="{{ route('admin.booking-requests.index') }}"
                   class="nav-row {{ request()->routeIs('admin.booking-requests.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-utensils"></i></span>
                    <span>Tables &amp; activités</span>
                </a>
                <a href="{{ route('admin.bundles.index') }}"
                   class="nav-row {{ request()->routeIs('admin.bundles.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-layer-group"></i></span>
                    <span>Offres groupées</span>
                </a>
                <a href="{{ route('admin.disputes.index') }}"
                   class="nav-row {{ request()->routeIs('admin.disputes.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-scale-balanced"></i></span>
                    <span>Litiges</span>
                    @php $openDisputes = \App\Models\Dispute::where('status', 'open')->count(); @endphp
                    @if($openDisputes > 0)<span class="ml-auto text-[10px] font-bold bg-orange-500 text-black rounded-full px-1.5">{{ $openDisputes }}</span>@endif
                </a>
                <a href="{{ route('admin.art-orders.index') }}"
                   class="nav-row {{ request()->routeIs('admin.art-orders.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-receipt"></i></span>
                    <span>Commandes Art</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="finance" open>
                <summary class="nav-section-title"><span>Finance</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.payments.index') }}"
                   class="nav-row {{ request()->routeIs('admin.payments.index', 'admin.payments.show') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-credit-card"></i></span>
                    <span>Paiements</span>
                </a>
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="nav-row {{ request()->routeIs('admin.subscriptions.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-file-invoice"></i></span>
                    <span>Abonnements</span>
                </a>
                <a href="{{ route('admin.plans.index') }}"
                   class="nav-row {{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.promo-codes.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-gem"></i></span>
                    <span>Forfaits</span>
                </a>
                <a href="{{ route('admin.wallet.index') }}"
                   class="nav-row {{ request()->routeIs('admin.wallet.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-wallet"></i></span>
                    <span>Portefeuilles</span>
                </a>
                <a href="{{ route('admin.payments.settings') }}"
                   class="nav-row {{ request()->routeIs('admin.payments.settings*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-sliders"></i></span>
                    <span>Config paiement</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="intelligence-artificielle" open>
                <summary class="nav-section-title"><span>Intelligence artificielle</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.ai.index') }}"
                   class="nav-row {{ request()->routeIs('admin.ai.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-wand-magic-sparkles"></i></span>
                    <span>Intelligence artificielle</span>
                </a>
                <a href="{{ route('admin.ai-moderation.index') }}"
                   class="nav-row {{ request()->routeIs('admin.ai-moderation.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-shield-halved"></i></span>
                    <span>Modération assistée</span>
                </a>
                </div>
                </details>
                <details class="nav-group" data-nav-group="site-securite" open>
                <summary class="nav-section-title"><span>Site &amp; sécurité</span><i class="fas fa-chevron-down nav-chevron" aria-hidden="true"></i></summary>
                <div class="nav-group-body">
                <a href="{{ route('admin.administration.appearance') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.appearance', 'admin.administration.flash-info', 'admin.administration.social', 'admin.administration.media') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-paintbrush"></i></span>
                    <span>Apparence</span>
                </a>
                <a href="{{ route('admin.administration.partners') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.partners*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-handshake"></i></span>
                    <span>Partenaires</span>
                </a>
                <a href="{{ route('admin.administration.info-center') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.info-center*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-circle-info"></i></span>
                    <span>Centre d'information</span>
                </a>
                <a href="{{ route('admin.administration.settings') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.settings*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-gear"></i></span>
                    <span>Paramètres</span>
                </a>
                <a href="{{ route('admin.administration.maintenance') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.maintenance') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-screwdriver-wrench"></i></span>
                    <span>Maintenance</span>
                </a>
                <a href="{{ route('admin.audit.index') }}"
                   class="nav-row {{ request()->routeIs('admin.audit.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span>Journal d'audit</span>
                </a>
                </div>
                </details>

            @elseif($role === 'editor')
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Rédaction</p>
                <a href="{{ route('editor.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('editor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                </a>
                <a href="{{ route('editor.articles.index') }}"
                   class="sidebar-link {{ request()->routeIs('editor.articles.index') ? 'active' : '' }}">
                    <i class="fas fa-pen-nib w-4 text-center"></i> Mes articles
                </a>
                <a href="{{ route('editor.articles.create') }}"
                   class="sidebar-link {{ request()->routeIs('editor.articles.create') ? 'active' : '' }}">
                    <i class="fas fa-circle-plus w-4 text-center"></i> Nouvel article
                </a>
                <a href="{{ route('editor.articles.index', ['status' => 'review']) }}"
                   class="sidebar-link {{ request()->routeIs('editor.articles.index') && request('status') === 'review' ? 'active' : '' }}">
                    <i class="fas fa-clock-rotate-left w-4 text-center"></i> En révision
                </a>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-calendar-days w-4 text-center"></i> Événements
                </a>

            @elseif($role === 'provider')
                @if(\App\Http\Middleware\ProviderStaffGate::isStaff(auth()->id()))
                    {{-- Membre de l'équipe : uniquement les opérations du quotidien. --}}
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">{{ $provider?->name ?? 'Mon équipe' }}</p>
                    <a href="{{ route('provider.agenda') }}" class="sidebar-link {{ request()->routeIs('provider.agenda') ? 'active' : '' }}"><i class="fas fa-calendar-day w-4 text-center"></i> Planning du jour</a>
                    <a href="{{ route('provider.booking-requests.index') }}" class="sidebar-link {{ request()->routeIs('provider.booking-requests.*') ? 'active' : '' }}"><i class="fas fa-calendar-plus w-4 text-center"></i> Demandes de réservation</a>
                    <a href="{{ route('provider.reservations.index') }}" class="sidebar-link {{ request()->routeIs('provider.reservations.*') ? 'active' : '' }}"><i class="fas fa-calendar-check w-4 text-center"></i> Réservations</a>
                    <a href="{{ route('provider.tourist-visits.index') }}" class="sidebar-link {{ request()->routeIs('provider.tourist-visits.*') ? 'active' : '' }}"><i class="fas fa-person-hiking w-4 text-center"></i> Visites</a>
                    <a href="{{ route('provider.tickets.index') }}" class="sidebar-link {{ request()->routeIs('provider.tickets.*') ? 'active' : '' }}"><i class="fas fa-qrcode w-4 text-center"></i> Contrôle des billets</a>
                    <a href="{{ route('provider.restaurant.tables.edit') }}" class="sidebar-link {{ request()->routeIs('provider.restaurant.tables.*') ? 'active' : '' }}"><i class="fas fa-chair w-4 text-center"></i> Tables du jour</a>
                    <a href="{{ route('provider.client-conversations.index') }}" class="sidebar-link {{ request()->routeIs('provider.client-conversations.*') ? 'active' : '' }}"><i class="fas fa-comments w-4 text-center"></i> Messages clients</a>
                    <a href="{{ route('provider.gift-cards.index') }}" class="sidebar-link {{ request()->routeIs('provider.gift-cards.*') ? 'active' : '' }}"><i class="fas fa-gift w-4 text-center"></i> Bons cadeaux</a>
                    <a href="{{ route('provider.notifications.index') }}" class="sidebar-link {{ request()->routeIs('provider.notifications.*') ? 'active' : '' }}"><i class="fas fa-bell w-4 text-center"></i> Notifications</a>
                @else
                @php
                    /*
                     * Menu prestataire organisé par tâche (et non par module) : pilotage, réservations, offre du secteur,
                     * clients, visibilité, finances, compte. Chaque lien n'apparaît qu'une fois ; l'offre propre au secteur
                     * (hébergement, restaurant, agence, activités, site, transport, atelier) est regroupée sous un seul titre.
                     * Entrée : [route, motifs routeIs actifs, icône Font Awesome, libellé].
                     */
                    $pvGroups = []; $pvBook = []; $pvOfferTitles = []; $pvWallet = false;
                    if ($providerIsAccommodationCategory ?? false) {
                        $pvOfferTitles[] = 'Mon hébergement'; $pvGroups['Mon hébergement'] = []; $pvWallet = true;
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.accommodation.dashboard', ['provider.accommodation.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.accommodation.profile.edit', ['provider.accommodation.profile.*'], 'fa-hotel', 'Fiche établissement'],
                            ['provider.accommodation.rooms.index', ['provider.accommodation.rooms.*'], 'fa-bed', 'Chambres & tarifs'],
                            ['provider.accommodation.gallery.index', ['provider.accommodation.gallery.*'], 'fa-photo-film', 'Galerie photos']);
                        array_push($pvBook,
                            ['provider.reservations.index', ['provider.reservations.*'], 'fa-calendar-check', 'Réservations'],
                            ['provider.tourist-visits.index', ['provider.tourist-visits.*'], 'fa-person-hiking', 'Visites'],
                            ['provider.tickets.index', ['provider.tickets.*'], 'fa-qrcode', 'Contrôle des billets']);
                    }
                    if ($providerIsRestaurantCategory ?? false) {
                        $pvOfferTitles[] = 'Mon restaurant'; $pvGroups['Mon restaurant'] = []; $pvWallet = true;
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.menu.dashboard', ['provider.menu.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.restaurant.profile.edit', ['provider.restaurant.profile.*'], 'fa-utensils', 'Fiche établissement'],
                            ['provider.menu.index', ['provider.menu.index', 'provider.menu.create', 'provider.menu.edit'], 'fa-book-open', 'Ma carte'],
                            ['provider.restaurant.gallery.index', ['provider.restaurant.gallery.*'], 'fa-photo-film', 'Galerie photos']);
                        $pvBook[] = ['provider.restaurant.tables.edit', ['provider.restaurant.tables.*'], 'fa-chair', 'Tables & plat du jour'];
                    }
                    if ($providerIsTravelAgencyCategory ?? false) {
                        $pvOfferTitles[] = 'Mon agence'; $pvGroups['Mon agence'] = []; $pvWallet = true;
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.tours.dashboard', ['provider.tours.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.travel-agency.profile.edit', ['provider.travel-agency.profile.*'], 'fa-plane', 'Fiche établissement'],
                            ['provider.tours.index', ['provider.tours.index', 'provider.tours.create', 'provider.tours.edit'], 'fa-route', 'Mes circuits'],
                            ['provider.travel-agency.gallery.index', ['provider.travel-agency.gallery.*'], 'fa-photo-film', 'Galerie photos']);
                    }
                    if ($providerIsTouristExperienceCategory ?? false) {
                        $pvOfferTitles[] = 'Mon site & mes visites'; $pvGroups['Mon site & mes visites'] = [];
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.tourist-experience.dashboard', ['provider.tourist-experience.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.tourist-experience.profile.edit', ['provider.tourist-experience.profile.*'], 'fa-landmark', 'Fiche établissement'],
                            ['provider.tourist-experience.visit-types.index', ['provider.tourist-experience.visit-types.*'], 'fa-map-signs', 'Types de visite'],
                            ['provider.tourist-experience.sessions.index', ['provider.tourist-experience.sessions.*'], 'fa-clock', 'Créneaux de visite'],
                            ['provider.tourist-experience.gallery.index', ['provider.tourist-experience.gallery.*'], 'fa-photo-film', 'Galerie photos']);
                        $pvBook[] = ['provider.tourist-visits.index', ['provider.tourist-visits.*'], 'fa-person-hiking', 'Visites réservées'];
                    }
                    if ($providerIsLeisureVenueCategory ?? false) {
                        $pvOfferTitles[] = 'Mon établissement'; $pvGroups['Mon établissement'] = [];
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.leisure-venue.dashboard', ['provider.leisure-venue.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.leisure-venue.profile.edit', ['provider.leisure-venue.profile.*'], 'fa-masks-theater', 'Fiche établissement'],
                            ['provider.leisure-venue.gallery.index', ['provider.leisure-venue.gallery.*'], 'fa-photo-film', 'Galerie photos']);
                    }
                    if ($providerIsLeisureCategory ?? false) {
                        $pvOfferTitles[] = 'Mes activités'; $pvGroups['Mes activités'] = []; $pvWallet = true;
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.activities.dashboard', ['provider.activities.dashboard'], 'fa-chart-simple', 'Bilan des activités'],
                            ['provider.activities.index', ['provider.activities.index', 'provider.activities.create', 'provider.activities.edit'], 'fa-person-hiking', 'Mes activités']);
                        array_push($pvBook,
                            ['provider.activity-bookings.index', ['provider.activity-bookings.*', 'provider.leisure-resources.*', 'provider.leisure-packages.*', 'provider.leisure-passes.*'], 'fa-ticket', 'Réservations & formules'],
                            ['provider.tickets.index', ['provider.tickets.*'], 'fa-qrcode', 'Scanner les billets']);
                    }
                    if ($providerIsTransportCategory ?? false) {
                        $pvOfferTitles[] = 'Ma compagnie'; $pvGroups['Ma compagnie'] = []; $pvWallet = true;
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.transport.dashboard', ['provider.transport.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.transport-company.profile.edit', ['provider.transport-company.profile.*'], 'fa-car', 'Fiche établissement'],
                            ['provider.transport.index', ['provider.transport.index', 'provider.transport.create', 'provider.transport.edit'], 'fa-car-side', 'Mes offres'],
                            ['provider.transport-company.gallery.index', ['provider.transport-company.gallery.*'], 'fa-photo-film', 'Galerie photos']);
                        $pvBook[] = ['provider.transport-bookings.index', ['provider.transport-bookings.*', 'provider.transport-fleet.*', 'provider.transport-trips.*'], 'fa-calendar-check', 'Planning, flotte, lignes'];
                    }
                    if ($providerIsArtCategory ?? false) {
                        $pvOfferTitles[] = 'Mon atelier'; $pvGroups['Mon atelier'] = []; $pvWallet = true;
                        array_push($pvGroups[end($pvOfferTitles)],
                            ['provider.artworks.dashboard', ['provider.artworks.dashboard'], 'fa-house-chimney', 'Vue d\'ensemble'],
                            ['provider.artworks.index', ['provider.artworks.index', 'provider.artworks.create', 'provider.artworks.edit'], 'fa-palette', 'Mes œuvres'],
                            ['provider.artist-profile.edit', ['provider.artist-profile.*', 'provider.art-offers.*', 'provider.art-commissions.*'], 'fa-id-badge', 'Page artiste, offres, sur mesure']);
                        $pvBook[] = ['provider.art-orders.index', ['provider.art-orders.*'], 'fa-box-open', 'Commandes d\'art'];
                    }
                    // Un même lien (ex. contrôle des billets) n'apparaît qu'une fois.
                    $pvBook = collect($pvBook)->unique(fn ($i) => $i[0])->values()->all();
                    $pendingBookings = \App\Models\BookingRequest::whereIn('provider_id', \App\Models\Provider::forUserQuery(auth()->id())->select('id'))->where('status', 'new')->count();
                    $pvBadge = 'ml-auto inline-flex min-w-[1.2rem] h-[1.2rem] px-1 items-center justify-center rounded-full bg-orange-500 text-black text-[10px] font-bold leading-none';
                @endphp

                {{-- Pilotage --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Pilotage</p>
                <a href="{{ route('provider.dashboard') }}" class="sidebar-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                </a>
                <a href="{{ route('provider.agenda') }}" class="sidebar-link {{ request()->routeIs('provider.agenda') ? 'active' : '' }}">
                    <i class="fas fa-calendar-day w-4 text-center"></i> Planning du jour
                </a>
                <a href="{{ route('provider.analytics') }}" class="sidebar-link {{ request()->routeIs('provider.analytics*') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-4 text-center"></i> Statistiques
                </a>
                @if(config('ai.enabled', true))
                    <a href="{{ route('provider.insights') }}" class="sidebar-link {{ request()->routeIs('provider.insights*') ? 'active' : '' }}">
                        <i class="fas fa-chart-area w-4 text-center"></i> Analyse avancée
                    </a>
                @endif

                {{-- Réservations et commandes --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Réservations</p>
                <a href="{{ route('provider.booking-requests.index') }}" class="sidebar-link {{ request()->routeIs('provider.booking-requests.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus w-4 text-center"></i> <span class="flex-1">Demandes de réservation</span>
                    @if($pendingBookings > 0)<span class="{{ $pvBadge }}">{{ $pendingBookings > 99 ? '99+' : $pendingBookings }}</span>@endif
                </a>
                @foreach($pvBook as [$pvRoute, $pvActive, $pvIcon, $pvLabel])
                    <a href="{{ route($pvRoute) }}" class="sidebar-link {{ request()->routeIs(...$pvActive) ? 'active' : '' }}">
                        <i class="fas {{ $pvIcon }} w-4 text-center"></i> {{ $pvLabel }}
                    </a>
                @endforeach

                {{-- Offre propre au secteur --}}
                @foreach($pvGroups as $pvOfferTitle => $pvOffer)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">{{ $pvOfferTitle }}</p>
                    @foreach($pvOffer as [$pvRoute, $pvActive, $pvIcon, $pvLabel])
                        <a href="{{ route($pvRoute) }}" class="sidebar-link {{ request()->routeIs(...$pvActive) ? 'active' : '' }}">
                            <i class="fas {{ $pvIcon }} w-4 text-center"></i> {{ $pvLabel }}
                        </a>
                    @endforeach
                @endforeach

                {{-- Clients --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Clients</p>
                <a href="{{ route('provider.client-conversations.index') }}" class="sidebar-link {{ request()->routeIs('provider.client-conversations.*') ? 'active' : '' }}">
                    <i class="fas fa-message w-4 text-center"></i> <span class="flex-1">Messages clients</span>
                    @if($sidebarClientMessagingUnreadCount > 0)<span class="{{ $pvBadge }}">{{ $sidebarClientMessagingUnreadCount > 99 ? '99+' : $sidebarClientMessagingUnreadCount }}</span>@endif
                </a>
                <a href="{{ route('provider.reviews.index') }}" class="sidebar-link {{ request()->routeIs('provider.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star w-4 text-center"></i> Avis clients
                </a>
                <a href="{{ route('provider.disputes.index') }}" class="sidebar-link {{ request()->routeIs('provider.disputes.*') ? 'active' : '' }}">
                    <i class="fas fa-scale-balanced w-4 text-center"></i> Litiges
                </a>

                {{-- Visibilité et ventes --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Visibilité &amp; ventes</p>
                <a href="{{ route('provider.promo-codes.index') }}" class="sidebar-link {{ request()->routeIs('provider.promo-codes.*') ? 'active' : '' }}">
                    <i class="fas fa-tag w-4 text-center"></i> Codes promo
                </a>
                <a href="{{ route('provider.gift-cards.index') }}" class="sidebar-link {{ request()->routeIs('provider.gift-cards.*') ? 'active' : '' }}">
                    <i class="fas fa-gift w-4 text-center"></i> Bons cadeaux
                </a>
                <a href="{{ route('provider.media.index') }}" class="sidebar-link {{ request()->routeIs('provider.media.*') ? 'active' : '' }}">
                    <i class="fas fa-images w-4 text-center"></i> Publications
                </a>
                <a href="{{ route('provider.videos.index') }}" class="sidebar-link {{ request()->routeIs('provider.videos.*') ? 'active' : '' }}">
                    <i class="fas fa-film w-4 text-center"></i> Mes vidéos
                </a>

                {{-- Finances --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Finances</p>
                @if($pvWallet)
                    <a href="{{ route('provider.wallet.index') }}" class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                <a href="{{ route('provider.billing.plans') }}" class="sidebar-link {{ request()->routeIs('provider.billing.plans') || request()->routeIs('provider.billing.checkout') ? 'active' : '' }}">
                    <i class="fas fa-gem w-4 text-center"></i> Mon forfait
                </a>
                <a href="{{ route('provider.billing.invoices') }}" class="sidebar-link {{ request()->routeIs('provider.billing.invoices') || request()->routeIs('provider.billing.confirmation') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-4 text-center"></i> Factures
                </a>

                {{-- Compte --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Compte</p>
                <a href="{{ route('provider.profile.edit') }}" class="sidebar-link {{ request()->routeIs('provider.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-store w-4 text-center"></i> Profil &amp; horaires
                </a>
                <a href="{{ route('provider.verification.index') }}" class="sidebar-link {{ request()->routeIs('provider.verification.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card w-4 text-center"></i> Vérification du compte
                </a>
                <a href="{{ route('provider.team.index') }}" class="sidebar-link {{ request()->routeIs('provider.team.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-4 text-center"></i> Mon équipe
                </a>
                <a href="{{ route('provider.conversations.index') }}" class="sidebar-link {{ request()->routeIs('provider.conversations.*') ? 'active' : '' }}">
                    <i class="fas fa-headset w-4 text-center"></i> <span class="flex-1">Support Trésors d'Ivoire</span>
                    @if($sidebarMessagingUnreadCount > 0)<span class="{{ $pvBadge }}">{{ $sidebarMessagingUnreadCount > 99 ? '99+' : $sidebarMessagingUnreadCount }}</span>@endif
                </a>
                <a href="{{ route('provider.notifications.index') }}" class="sidebar-link {{ request()->routeIs('provider.notifications.*') ? 'active' : '' }}">
                    <i class="fas fa-bell w-4 text-center"></i> Notifications
                </a>
                @endif
            @else {{-- visitor --}}
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Espace visiteur</p>
                <a href="{{ route('visitor.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('visitor.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                </a>
                <a href="{{ route('visitor.profile.edit') }}"
                   class="sidebar-link {{ request()->routeIs('visitor.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-user-pen w-4 text-center"></i> Mon profil
                </a>
                <a href="{{ route('visitor.favorites.index') }}"
                   class="sidebar-link {{ request()->routeIs('visitor.favorites.*') ? 'active' : '' }}">
                    <i class="fas fa-heart w-4 text-center"></i> Mes favoris
                </a>
                <a href="{{ route('visitor.notifications.index') }}"
                   class="sidebar-link {{ request()->routeIs('visitor.notifications.*') ? 'active' : '' }}">
                    <i class="fas fa-bell w-4 text-center"></i> Notifications
                </a>
                <a href="{{ route('visitor.conversations.index') }}"
                   class="sidebar-link {{ request()->routeIs('visitor.conversations.*') ? 'active' : '' }}">
                    <i class="fas fa-message w-4 text-center"></i>
                    <span class="flex-1">Messages</span>
                    @if($sidebarClientMessagingUnreadCount > 0)
                        <span class="inline-flex min-w-[1.2rem] h-[1.2rem] px-1 items-center justify-center rounded-full bg-orange-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarClientMessagingUnreadCount > 99 ? '99+' : $sidebarClientMessagingUnreadCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('articles.index') }}" class="sidebar-link">
                    <i class="fas fa-newspaper w-4 text-center"></i> Articles
                </a>
                <a href="{{ route('events.index') }}" class="sidebar-link">
                    <i class="fas fa-calendar-days w-4 text-center"></i> Événements
                </a>
                <a href="{{ route('providers.index') }}" class="sidebar-link">
                    <i class="fas fa-compass w-4 text-center"></i> Annuaire
                </a>
            @endif
        </nav>
        <script>
            (function () {
                // Groupes du menu admin : celui de la page courante est ouvert ; les autres reprennent l'état mémorisé (« Pilotage » ouvert, les autres fermés au premier passage).
                var groups = document.querySelectorAll('[data-nav-group]');
                if (!groups.length) return;
                var KEY = 'ti-admin-nav', saved = {};
                try { saved = JSON.parse(localStorage.getItem(KEY) || '{}') || {}; } catch (e) {}
                groups.forEach(function (g) {
                    var id = g.getAttribute('data-nav-group');
                    g.open = !!g.querySelector('.nav-row.is-active') || (id in saved ? saved[id] === true : id === 'pilotage');
                    g.addEventListener('toggle', function () {
                        saved[id] = g.open;
                        try { localStorage.setItem(KEY, JSON.stringify(saved)); } catch (e) {}
                    });
                });
            })();
        </script>

        {{-- User footer --}}
        <div class="border-t border-slate-800 p-4 {{ $admShell ? 'admin-sidebar-footer' : '' }}">
            <div class="flex items-center gap-3 {{ $admShell ? 'user-card' : '' }}">
                <div class="w-8 h-8 rounded-full bg-orange-400 flex items-center justify-center text-white text-xs font-bold shrink-0 {{ $admShell ? 'user-avatar' : '' }}">
                    {{ auth()->user()->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-red-500/15 hover:text-red-400 transition text-sm {{ $admShell ? 'border border-slate-700/60 hover:border-red-500/30' : '' }}">
                        <i class="fas fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay"
        class="fixed inset-0 z-40 bg-green-950/60 lg:hidden hidden"
        onclick="toggleSidebar()"></div>

    {{-- ══════════════ MAIN ══════════════ --}}
    <div class="flex-1 flex flex-col min-h-screen {{ $admShell ? 'lg:ml-[18rem]' : 'lg:ml-64' }}">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 bg-green-900/80 backdrop-blur border-b border-slate-800 flex items-center justify-between px-4 sm:px-6 h-14">
            <div class="flex items-center gap-4">
                <button onclick="toggleSidebar()" class="lg:hidden text-slate-400 hover:text-white">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="text-white font-semibold text-sm sm:text-base">@yield('page-title', 'Tableau de bord')</h1>
            </div>
            <div class="flex items-center gap-3">
                @yield('header-actions')
                @php
                    $notificationRouteByRole = [
                        'visitor' => 'visitor.notifications.index',
                        'editor' => 'editor.notifications.index',
                        'provider' => 'provider.notifications.index',
                        'admin' => 'admin.notifications.index',
                    ];
                    $notificationRoute = $notificationRouteByRole[$role] ?? null;
                @endphp
                @if($notificationRoute && \Illuminate\Support\Facades\Route::has($notificationRoute))
                    @php $globalUnreadNotifications = auth()->user()->unreadNotifications()->count(); @endphp
                    <a href="{{ route($notificationRoute) }}"
                       class="relative inline-flex h-9 w-9 items-center justify-center rounded-lg border border-slate-700/70 bg-slate-800/70 text-slate-300 hover:text-white hover:bg-slate-700/70 transition"
                       title="Notifications">
                        <i class="fas fa-bell text-sm"></i>
                        @if($globalUnreadNotifications > 0)
                            <span class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-orange-500 text-black text-[10px] font-bold leading-[1.1rem] text-center">
                                {{ $globalUnreadNotifications > 99 ? '99+' : $globalUnreadNotifications }}
                            </span>
                        @endif
                    </a>
                @endif
                @if($role === 'admin' && !empty($siteBrand['maintenance_mode']))
                    <a href="{{ route('admin.administration.maintenance') }}"
                       class="inline-flex items-center gap-1.5 rounded-full border border-rose-500/50 bg-rose-600/20 px-2.5 py-1 text-[10px] sm:text-[11px] font-semibold uppercase tracking-wide text-rose-200 hover:bg-rose-600/30 transition shrink-0 max-w-[9rem] sm:max-w-none truncate">
                        <span class="h-1.5 w-1.5 rounded-full bg-rose-400 animate-pulse"></span>
                        Maintenance active
                    </a>
                @endif
                <span class="text-slate-500 text-xs hidden sm:block">
                    {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                </span>

                {{-- Menu utilisateur --}}
                @php
                    $umUser = auth()->user();
                    $umRoleLabel = ['admin' => 'Admin', 'editor' => 'Éditeur', 'provider' => 'Prestataire', 'visitor' => 'Visiteur'][$role] ?? ucfirst($role);
                    $umProfile = $role === 'visitor' ? route('visitor.profile.edit') : route('account.edit');
                    $umPassword = $role === 'visitor' ? route('visitor.profile.edit') : route('account.edit').'#mot-de-passe';
                    $umSettings = match ($role) {
                        'admin' => route('admin.administration.settings'),
                        'provider' => route('provider.profile.edit'),
                        default => null,
                    };
                @endphp
                <div class="um" id="user-menu">
                    <button type="button" class="um-trigger" aria-haspopup="menu" aria-expanded="false" aria-controls="user-menu-panel">
                        <span class="um-av"><span>@if($umUser->avatar_url)<img src="{{ $umUser->avatar_url }}" alt="">@else{{ $umUser->initials }}@endif</span></span>
                        <span class="um-who">
                            <b>{{ $umUser->full_name }}</b>
                            <small>{{ $umRoleLabel }}</small>
                        </span>
                        <i class="fas fa-chevron-down um-chev" aria-hidden="true"></i>
                    </button>
                    <div class="um-panel" id="user-menu-panel" role="menu" hidden>
                        <div class="um-head">
                            @include('partials.ivorian-motifs', ['culture' => 'national'])
                            <span class="um-av is-lg"><span>@if($umUser->avatar_url)<img src="{{ $umUser->avatar_url }}" alt="">@else{{ $umUser->initials }}@endif</span></span>
                            <div class="um-head__txt">
                                <b>{{ $umUser->full_name }}</b>
                                <small>{{ $umUser->email }}</small>
                                <span class="um-badge"><i class="fas fa-shield-halved"></i>{{ $umRoleLabel }}</span>
                            </div>
                        </div>
                        <div class="um-list">
                            <a href="{{ $umProfile }}" class="um-item" role="menuitem" style="--t: 49,98,28">
                                <span class="um-ic"><i class="fas fa-user"></i></span>
                                <span class="um-txt"><b>Mon profil</b><small>Identité et coordonnées</small></span>
                                <i class="fas fa-arrow-right um-go" aria-hidden="true"></i>
                            </a>
                            <a href="{{ $umPassword }}" class="um-item" role="menuitem" style="--t: 201,138,12">
                                <span class="um-ic"><i class="fas fa-key"></i></span>
                                <span class="um-txt"><b>Modifier mot de passe</b><small>Sécurité du compte</small></span>
                                <i class="fas fa-arrow-right um-go" aria-hidden="true"></i>
                            </a>
                            @if($umSettings)
                                <a href="{{ $umSettings }}" class="um-item" role="menuitem" style="--t: 50,118,97">
                                    <span class="um-ic"><i class="fas fa-sliders"></i></span>
                                    <span class="um-txt"><b>Paramètres</b><small>{{ $role === 'admin' ? 'Réglages de la plateforme' : 'Fiche de votre activité' }}</small></span>
                                    <i class="fas fa-arrow-right um-go" aria-hidden="true"></i>
                                </a>
                            @endif
                            <div class="um-sep"></div>
                            <a href="{{ route('home') }}" class="um-item" role="menuitem" style="--t: 239,124,43">
                                <span class="um-ic"><i class="fas fa-house"></i></span>
                                <span class="um-txt"><b>Retour au site</b><small>Trésors d'Ivoire</small></span>
                                <i class="fas fa-arrow-right um-go" aria-hidden="true"></i>
                            </a>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="um-out" role="menuitem"><i class="fas fa-power-off"></i>Déconnexion</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            {{-- Espace prestataire : fond de page à motifs ivoiriens très discrets (comme le tableau de bord admin). --}}
            @if($role === 'provider')
                <div class="pv-bg" aria-hidden="true">@include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])</div>
            @endif
            @if($role === 'admin')
                @php
                    $schedulerLastRun = \Illuminate\Support\Facades\Cache::get('scheduler:last_run');
                    $schedulerStale = ! $schedulerLastRun || \Illuminate\Support\Carbon::parse($schedulerLastRun)->lt(now()->subMinutes(15));
                @endphp
                @if($schedulerStale)
                    <div class="mb-4 p-3 rounded-lg border text-sm flex items-start gap-2" style="background:#F3F8F5;border-color:#F7C396;color:#B85600;">
                        <i class="fas fa-triangle-exclamation mt-0.5"></i>
                        <span><strong>Tâches planifiées arrêtées.</strong> Le cron <code>php artisan schedule:run</code> ne tourne pas sur le serveur
                        {{ $schedulerLastRun ? '(dernière exécution '.\Illuminate\Support\Carbon::parse($schedulerLastRun)->locale('fr')->diffForHumans().')' : '(aucune exécution enregistrée)' }} :
                        les réservations non payées ne sont plus libérées, les fonds des prestataires ne sont plus débloqués et les sauvegardes ne se font plus.</span>
                    </div>
                @endif
            @endif
            @if(session('error'))
                <div class="mb-4 p-3 bg-red-900/40 border border-red-700 rounded-lg text-red-300 text-sm flex items-center gap-2">
                    <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
                </div>
            @endif

            @yield('content')
        </main>

    </div>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        }

        (function () {
            const menu = document.getElementById('user-menu');
            if (!menu) return;
            const trigger = menu.querySelector('.um-trigger');
            const panel = menu.querySelector('.um-panel');
            const setOpen = (open) => {
                panel.hidden = !open;
                trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
                menu.classList.toggle('is-open', open);
            };
            trigger.addEventListener('click', (e) => { e.stopPropagation(); setOpen(panel.hidden); });
            document.addEventListener('click', (e) => { if (!menu.contains(e.target)) setOpen(false); });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && !panel.hidden) { setOpen(false); trigger.focus(); } });
        })();

        const successToast = document.getElementById('success-toast');
        if (successToast) {
            setTimeout(() => {
                successToast.classList.add('opacity-0');
                setTimeout(() => successToast.remove(), 300);
            }, 3500);
        }
    </script>
    @include('partials.image-protection')
    @stack('scripts')
</body>
</html>
