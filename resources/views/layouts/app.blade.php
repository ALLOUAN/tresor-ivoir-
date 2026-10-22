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
            color: #94a3b8;
            font-size: 0.875rem;
            transition: color 0.15s ease, background 0.15s ease;
        }
        .sidebar-link:hover { background:#e9e5d9; color: #fff; }
        .sidebar-link.active {
            background: rgba(242, 121, 15, 0.12);
            color: #fa9a3c;
            font-weight: 500;
        }
        /* ——— Sidebar administrateur ——— */
        .admin-sidebar {
            width: 18rem;
            background:#e9e5d9;
            border-right: 1px solid rgba(242, 121, 15, 0.12);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35);
        }
        .admin-sidebar-brand {
            background: rgba(242, 121, 15, 0.04);
            border-bottom: 1px solid rgba(148, 163, 184, 0.12);
        }
        .admin-sidebar .nav-section-title {
            font-size: 0.65rem;
            font-weight: 700;
            letter-spacing: 0.16em;
            text-transform: uppercase;
            color: #64748b;
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
            background: rgba(100, 116, 139, 0.23);
        }
        /* Groupes repliables : le titre est cliquable, le chevron tourne à l'ouverture. */
        .admin-sidebar .nav-group > summary { list-style: none; cursor: pointer; user-select: none; border-radius: .6rem; transition: color .15s ease; }
        .admin-sidebar .nav-group > summary::-webkit-details-marker { display: none; }
        .admin-sidebar .nav-group > summary:hover { color: #f2790f; }
        .admin-sidebar .nav-group > summary:focus-visible { outline: 2px solid #f2790f; outline-offset: 1px; }
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
            color: #94a3b8;
            font-size: 0.875rem;
            font-weight: 500;
            text-decoration: none;
            transition: background 0.18s ease, color 0.18s ease, box-shadow 0.18s ease;
            margin-bottom: 1px;
        }
        .admin-sidebar .nav-row:hover {
            background: rgba(233, 229, 217, 0.04);
            color: #f1f5f9;
        }
        .admin-sidebar .nav-row.is-active {
            font-weight: 600;
            background: rgba(242, 121, 15, 0.08);
            color: #fdbe7b;
            box-shadow: inset 3px 0 0 #f2790f;
        }
        .admin-sidebar .nav-row-icon {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.55rem;
            background: rgba(233, 229, 217, 0.85);
            border: 1px solid rgba(71, 85, 105, 0.35);
            flex-shrink: 0;
            font-size: 0.8rem;
            transition: inherit;
        }
        .admin-sidebar .nav-row:hover .nav-row-icon {
            background: rgba(51, 65, 85, 0.95);
            border-color: rgba(100, 116, 139, 0.45);
            color: #e2e8f0;
        }
        .admin-sidebar .nav-row.is-active .nav-row-icon {
            background: rgba(242, 121, 15, 0.18);
            border-color: rgba(242, 121, 15, 0.35);
            color: #fa9a3c;
        }
        .admin-sidebar .nav-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(242, 121, 15, 0.35) transparent;
        }
        .admin-sidebar .role-pill {
            background: rgba(167, 80, 9, 0.3);
            border: 1px solid rgba(250, 154, 60, 0.25);
            box-shadow: 0 0 20px rgba(242, 121, 15, 0.12);
        }
        .admin-sidebar-footer {
            background: rgba(255, 255, 255, 0.45);
            border-top: 1px solid rgba(148, 163, 184, 0.12);
        }
        .admin-sidebar .user-card {
            border-radius: 0.75rem;
            padding: 0.65rem 0.75rem;
            background: rgba(233, 229, 217, 0.55);
            border: 1px solid rgba(71, 85, 105, 0.35);
        }
        .admin-sidebar .user-avatar {
            box-shadow: 0 0 0 2px rgba(242, 121, 15, 0.35), 0 4px 12px rgba(0, 0, 0, 0.25);
        }
        /* ── Light mode: body & fond ──────────────────── */
        html:not(.dark) body                    { background-color:#e9e5d9!important; color:#1c1915!important; }
        html:not(.dark) .text-white             { color:#1c1915!important; }
        html:not(.dark) .text-slate-100         { color:#1c1915!important; }
        html:not(.dark) .text-slate-200         { color:#2d2a23!important; }
        html:not(.dark) .text-slate-300         { color:#44413a!important; }
        html:not(.dark) .text-slate-400         { color:#544f47!important; }
        html:not(.dark) .text-slate-500         { color:#665f52!important; }
        html:not(.dark) .text-slate-600         { color:#5c574e!important; }
        html:not(.dark) .bg-green-950           { background-color:#e9e5d9!important; }
        html:not(.dark) .bg-green-900           { background-color:#ddd7c7!important; }
        html:not(.dark) .bg-slate-800           { background-color:#f0ece1!important; }
        html:not(.dark) .bg-slate-700           { background-color:#ddd7c7!important; }
        html:not(.dark) .border-slate-800       { border-color:#d6cfba!important; }
        html:not(.dark) .border-slate-700       { border-color:#c2b89e!important; }
        html:not(.dark) .border-slate-700\/70   { border-color:rgba(0,0,0,0.1)!important; }
        html:not(.dark) .bg-green-900\/80       { background-color:rgba(233, 229, 217, 0.97)!important; }
        html:not(.dark) .bg-slate-800\/70       { background-color:rgba(0,0,0,0.05)!important; }
        html:not(.dark) .bg-slate-700\/70       { background-color:rgba(0,0,0,0.06)!important; }
        html:not(.dark) .hover\:bg-slate-700\/70:hover { background-color:rgba(0,0,0,0.08)!important; }
        /* ── Light mode: sidebar non-admin ──────────── */
        html:not(.dark) .sidebar-link              { color:#544f47; }
        html:not(.dark) .sidebar-link:hover        { background:rgba(0,0,0,0.04); color:#1c1915; }
        html:not(.dark) .sidebar-link.active       { background:rgba(242, 121, 15,0.1); color:#a3450a; }
        /* ── Light mode: sidebar admin ───────────────── */
        html:not(.dark) .admin-sidebar {
            background:#ddd7c7;
            border-right-color:#d6cfba;
            box-shadow:4px 0 24px rgba(0,0,0,0.05);
        }
        html:not(.dark) .admin-sidebar-brand {
            background:rgba(242, 121, 15, 0.03);
            border-bottom-color:rgba(0,0,0,0.07);
        }
        html:not(.dark) .admin-sidebar .nav-section-title { color:#665f52; }
        html:not(.dark) .admin-sidebar .nav-section-title::after {
            background:rgba(0, 0, 0, 0.07);
        }
        html:not(.dark) .admin-sidebar .nav-row                   { color:#544f47; }
        html:not(.dark) .admin-sidebar .nav-row:hover             { background:rgba(0,0,0,0.04); color:#1c1915; }
        html:not(.dark) .admin-sidebar .nav-row.is-active         { background:rgba(242, 121, 15, 0.07); color:#a3450a; box-shadow:inset 3px 0 0 #f2790f; }
        html:not(.dark) .admin-sidebar .nav-row-icon              { background:rgba(0,0,0,0.05); border-color:rgba(0,0,0,0.1); color:#544f47; }
        html:not(.dark) .admin-sidebar .nav-row:hover .nav-row-icon { background:rgba(0,0,0,0.08); border-color:rgba(0,0,0,0.14); color:#1c1915; }
        html:not(.dark) .admin-sidebar .nav-row.is-active .nav-row-icon { background:rgba(242, 121, 15,0.14); border-color:rgba(242, 121, 15,0.3); color:#9f4709; }
        html:not(.dark) .admin-sidebar-footer { background:rgba(255, 255, 255, 0.45); border-top-color:rgba(0,0,0,0.07); }
        html:not(.dark) .admin-sidebar .user-card { background:rgba(0,0,0,0.04); border-color:rgba(0,0,0,0.1); }
        html:not(.dark) .admin-sidebar .user-avatar { box-shadow:0 0 0 2px rgba(242, 121, 15,0.3),0 4px 12px rgba(0,0,0,0.08); }
        html:not(.dark) .admin-sidebar .role-pill { background:rgba(167, 80, 9, 0.09); border-color:rgba(250,154,60,0.2); box-shadow:none; }
        html:not(.dark) .admin-sidebar .nav-scroll { scrollbar-color:rgba(242, 121, 15,0.2) transparent; }
        /* ── Light mode: top bar ─────────────────────── */
        html:not(.dark) header.sticky { border-bottom-color:#d6cfba!important; }
        /* ── Light mode: content cards ───────────────── */
        html:not(.dark) .bg-white\/5  { background-color:rgba(0,0,0,0.04)!important; }
        html:not(.dark) .border-white\/10 { border-color:rgba(0,0,0,0.1)!important; }
        html:not(.dark) .divide-slate-800>*+* { border-color:#d6cfba!important; }
        /* ── Light mode: toast messages ──────────────── */
        html:not(.dark) .bg-emerald-500\/15 { background-color:rgba(209,250,229,0.6)!important; }
        html:not(.dark) .border-emerald-400\/40 { border-color:rgba(52,211,153,0.4)!important; }
        html:not(.dark) .bg-red-900\/40 { background-color:rgba(255,228,228,0.6)!important; }
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
                $providerIsAccommodationCategory = $providerCategoryRootSlug === 'hotels';
                $providerIsArtCategory = $providerCategoryRootSlug === 'art-creations';
                $providerIsRestaurantCategory = $providerCategoryRootSlug === 'restaurants';
                $providerIsTravelAgencyCategory = $providerCategoryRootSlug === 'agences-voyages';
                $providerIsLeisureCategory = in_array($providerCategoryRootSlug, ['loisirs-culture', 'sites-touristiques'], true);
                $providerIsLeisureVenueCategory = $providerCategoryRootSlug === 'loisirs-culture';
                $providerIsTouristExperienceCategory = $providerCategoryRootSlug === 'sites-touristiques';
                $providerIsTransportCategory = $providerCategoryRootSlug === 'transports';
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

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800 {{ $admShell ? 'admin-sidebar-brand !border-b-0' : '' }}">
            @if(!empty($siteBrand['logo_url']))
                <div class="flex items-center justify-center {{ $admShell ? 'w-10 h-10 rounded-xl' : 'w-9 h-9 rounded-lg' }} bg-white/5 border border-slate-700 shrink-0 overflow-hidden p-0.5">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="flex items-center justify-center {{ $admShell ? 'w-10 h-10 rounded-xl shadow-lg shadow-orange-900/40' : 'w-9 h-9 rounded-lg' }} bg-gradient-to-br from-orange-400 to-orange-600 shrink-0">
                    <i class="fas fa-gem text-white {{ $admShell ? 'text-lg' : 'text-base' }}"></i>
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-orange-400 font-bold {{ $admShell ? 'text-[0.95rem] tracking-tight' : 'text-sm' }} leading-tight truncate">{{ $siteBrand['site_name'] }}</p>
                <p class="text-slate-500 {{ $admShell ? 'text-[11px]' : 'text-xs' }} truncate">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
            </div>
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
                <a href="{{ route('admin.comics.index') }}"
                   class="nav-row {{ request()->routeIs('admin.comics.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-book-open"></i></span>
                    <span>Astuces de Mian (BD)</span>
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
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Mon établissement</p>
                <a href="{{ route('provider.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                </a>
                <a href="{{ route('provider.agenda') }}"
                   class="sidebar-link {{ request()->routeIs('provider.agenda') ? 'active' : '' }}">
                    <i class="fas fa-calendar-day w-4 text-center"></i> Planning du jour
                </a>
                <a href="{{ route('provider.profile.edit') }}"
                   class="sidebar-link {{ request()->routeIs('provider.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-store w-4 text-center"></i> Ma fiche
                </a>
                <a href="{{ route('provider.videos.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.videos.*') ? 'active' : '' }}">
                    <i class="fas fa-film w-4 text-center"></i> Mes vidéos
                </a>
                <a href="{{ route('provider.verification.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.verification.*') ? 'active' : '' }}">
                    <i class="fas fa-id-card w-4 text-center"></i> Vérification du compte
                </a>
                <a href="{{ route('provider.gift-cards.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.gift-cards.*') ? 'active' : '' }}">
                    <i class="fas fa-gift w-4 text-center"></i> Bons cadeaux
                </a>
                <a href="{{ route('provider.promo-codes.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.promo-codes.*') ? 'active' : '' }}">
                    <i class="fas fa-tag w-4 text-center"></i> Codes promo
                </a>
                <a href="{{ route('provider.team.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.team.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-4 text-center"></i> Mon équipe
                </a>
                <a href="{{ route('provider.booking-requests.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.booking-requests.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-plus w-4 text-center"></i> Demandes de réservation
                    @php $pendingBookings = \App\Models\BookingRequest::whereIn('provider_id', \App\Models\Provider::forUserQuery(auth()->id())->select('id'))->where('status', 'new')->count(); @endphp
                    @if($pendingBookings > 0)<span class="ml-auto text-[10px] font-bold bg-orange-500 text-black rounded-full px-1.5">{{ $pendingBookings }}</span>@endif
                </a>
                <a href="{{ route('provider.disputes.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.disputes.*') ? 'active' : '' }}">
                    <i class="fas fa-scale-balanced w-4 text-center"></i> Litiges
                </a>
                @if($providerIsAccommodationCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Mon hébergement</p>
                    <a href="{{ route('provider.accommodation.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.accommodation.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Vue d'ensemble
                    </a>
                    <a href="{{ route('provider.accommodation.profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.accommodation.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-hotel w-4 text-center"></i> Fiche établissement
                    </a>
                    <a href="{{ route('provider.accommodation.rooms.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.accommodation.rooms.*') ? 'active' : '' }}">
                        <i class="fas fa-bed w-4 text-center"></i> Chambres & tarifs
                    </a>
                    <a href="{{ route('provider.accommodation.gallery.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.accommodation.gallery.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-film w-4 text-center"></i> Galerie photos
                    </a>
                    <a href="{{ route('provider.reservations.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.reservations.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check w-4 text-center"></i> Réservations
                    </a>
                    <a href="{{ route('provider.tourist-visits.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tourist-visits.*') ? 'active' : '' }}">
                        <i class="fas fa-person-hiking w-4 text-center"></i> Visites
                    </a>
                    <a href="{{ route('provider.tickets.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-qrcode w-4 text-center"></i> Contrôle des billets
                    </a>
                    <a href="{{ route('provider.wallet.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                <a href="{{ route('provider.reviews.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star w-4 text-center"></i> Mes avis
                </a>
                @if(config('ai.enabled', true))
                <a href="{{ route('provider.insights') }}"
                   class="sidebar-link {{ request()->routeIs('provider.insights*') ? 'active' : '' }}">
                    <i class="fas fa-chart-area w-4 text-center"></i> Analyse avancée
                </a>
                @endif
                <a href="{{ route('provider.media.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.media.*') ? 'active' : '' }}">
                    <i class="fas fa-images w-4 text-center"></i> Médias
                </a>
                <a href="{{ route('provider.conversations.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.conversations.*') ? 'active' : '' }}">
                    <i class="fas fa-comments w-4 text-center"></i>
                    <span class="flex-1">Messagerie admin</span>
                    @if($sidebarMessagingUnreadCount > 0)
                        <span class="inline-flex min-w-[1.2rem] h-[1.2rem] px-1 items-center justify-center rounded-full bg-orange-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarMessagingUnreadCount > 99 ? '99+' : $sidebarMessagingUnreadCount }}
                        </span>
                    @endif
                </a>
                <a href="{{ route('provider.client-conversations.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.client-conversations.*') ? 'active' : '' }}">
                    <i class="fas fa-message w-4 text-center"></i>
                    <span class="flex-1">Messages clients</span>
                    @if($sidebarClientMessagingUnreadCount > 0)
                        <span class="inline-flex min-w-[1.2rem] h-[1.2rem] px-1 items-center justify-center rounded-full bg-orange-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarClientMessagingUnreadCount > 99 ? '99+' : $sidebarClientMessagingUnreadCount }}
                        </span>
                    @endif
                </a>
                @if($providerIsArtCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Espace Art &amp; Créations</p>
                    <a href="{{ route('provider.artworks.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.artworks.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.artworks.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.artworks.index') || request()->routeIs('provider.artworks.create') || request()->routeIs('provider.artworks.edit') ? 'active' : '' }}">
                        <i class="fas fa-palette w-4 text-center"></i> Mes œuvres
                    </a>
                    <a href="{{ route('provider.art-orders.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.art-orders.*') ? 'active' : '' }}">
                        <i class="fas fa-box-open w-4 text-center"></i> Commandes Art
                    </a>
                    <a href="{{ route('provider.artist-profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.artist-profile.*', 'provider.art-offers.*', 'provider.art-commissions.*') ? 'active' : '' }}">
                        <i class="fas fa-id-badge w-4 text-center"></i> Page artiste, offres, sur mesure
                    </a>
                    <a href="{{ route('provider.wallet.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                @if($providerIsRestaurantCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Espace Restaurant</p>
                    <a href="{{ route('provider.menu.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.menu.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.restaurant.profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.restaurant.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-utensils w-4 text-center"></i> Fiche établissement
                    </a>
                    <a href="{{ route('provider.menu.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.menu.index') || request()->routeIs('provider.menu.create') || request()->routeIs('provider.menu.edit') ? 'active' : '' }}">
                        <i class="fas fa-book-open w-4 text-center"></i> Ma carte
                    </a>
                    <a href="{{ route('provider.restaurant.tables.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.restaurant.tables.*') ? 'active' : '' }}">
                        <i class="fas fa-chair w-4 text-center"></i> Tables & plat du jour
                    </a>
                    <a href="{{ route('provider.restaurant.gallery.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.restaurant.gallery.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-film w-4 text-center"></i> Galerie photos
                    </a>
                    <a href="{{ route('provider.wallet.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                @if($providerIsTravelAgencyCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Espace Agence de Voyages</p>
                    <a href="{{ route('provider.tours.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tours.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.travel-agency.profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.travel-agency.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-plane w-4 text-center"></i> Fiche établissement
                    </a>
                    <a href="{{ route('provider.tours.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tours.index') || request()->routeIs('provider.tours.create') || request()->routeIs('provider.tours.edit') ? 'active' : '' }}">
                        <i class="fas fa-route w-4 text-center"></i> Mes circuits
                    </a>
                    <a href="{{ route('provider.travel-agency.gallery.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.travel-agency.gallery.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-film w-4 text-center"></i> Galerie photos
                    </a>
                    <a href="{{ route('provider.wallet.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                @if($providerIsLeisureCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Activités</p>
                    <a href="{{ route('provider.activities.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.activities.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.activities.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.activities.index') || request()->routeIs('provider.activities.create') || request()->routeIs('provider.activities.edit') ? 'active' : '' }}">
                        <i class="fas fa-person-hiking w-4 text-center"></i> Mes activités
                    </a>
                    <a href="{{ route('provider.activity-bookings.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.activity-bookings.*', 'provider.leisure-resources.*', 'provider.leisure-packages.*', 'provider.leisure-passes.*') ? 'active' : '' }}">
                        <i class="fas fa-ticket w-4 text-center"></i> Réservations, formules, cartes
                    </a>
                    <a href="{{ route('provider.tickets.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tickets.*') ? 'active' : '' }}">
                        <i class="fas fa-qrcode w-4 text-center"></i> Scanner les billets
                    </a>
                    <a href="{{ route('provider.wallet.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                @if($providerIsTouristExperienceCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Mon établissement</p>
                    <a href="{{ route('provider.tourist-experience.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tourist-experience.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.tourist-experience.profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tourist-experience.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-landmark w-4 text-center"></i> Fiche établissement
                    </a>
                    <a href="{{ route('provider.tourist-experience.gallery.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.tourist-experience.gallery.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-film w-4 text-center"></i> Galerie photos
                    </a>
                @endif
                @if($providerIsLeisureVenueCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Mon établissement</p>
                    <a href="{{ route('provider.leisure-venue.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.leisure-venue.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.leisure-venue.profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.leisure-venue.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-masks-theater w-4 text-center"></i> Fiche établissement
                    </a>
                    <a href="{{ route('provider.leisure-venue.gallery.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.leisure-venue.gallery.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-film w-4 text-center"></i> Galerie photos
                    </a>
                @endif
                @if($providerIsTransportCategory ?? false)
                    <p class="px-3 py-1.5 text-xs font-semibold text-orange-400/90 uppercase tracking-wider mt-3">Espace Transport</p>
                    <a href="{{ route('provider.transport.dashboard') }}"
                       class="sidebar-link {{ request()->routeIs('provider.transport.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-house-chimney w-4 text-center"></i> Tableau de bord
                    </a>
                    <a href="{{ route('provider.transport-company.profile.edit') }}"
                       class="sidebar-link {{ request()->routeIs('provider.transport-company.profile.*') ? 'active' : '' }}">
                        <i class="fas fa-car w-4 text-center"></i> Fiche établissement
                    </a>
                    <a href="{{ route('provider.transport.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.transport.index') || request()->routeIs('provider.transport.create') || request()->routeIs('provider.transport.edit') ? 'active' : '' }}">
                        <i class="fas fa-car-side w-4 text-center"></i> Mes offres
                    </a>
                    <a href="{{ route('provider.transport-bookings.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.transport-bookings.*', 'provider.transport-fleet.*', 'provider.transport-trips.*') ? 'active' : '' }}">
                        <i class="fas fa-calendar-check w-4 text-center"></i> Planning, flotte, lignes
                    </a>
                    <a href="{{ route('provider.transport-company.gallery.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.transport-company.gallery.*') ? 'active' : '' }}">
                        <i class="fas fa-photo-film w-4 text-center"></i> Galerie photos
                    </a>
                    <a href="{{ route('provider.wallet.index') }}"
                       class="sidebar-link {{ request()->routeIs('provider.wallet.*') ? 'active' : '' }}">
                        <i class="fas fa-wallet w-4 text-center"></i> Portefeuille
                    </a>
                @endif
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Abonnement</p>
                <a href="{{ route('provider.billing.plans') }}"
                   class="sidebar-link {{ request()->routeIs('provider.billing.plans') || request()->routeIs('provider.billing.checkout') ? 'active' : '' }}">
                    <i class="fas fa-gem w-4 text-center"></i> Mon forfait
                </a>
                <a href="{{ route('provider.billing.invoices') }}"
                   class="sidebar-link {{ request()->routeIs('provider.billing.invoices') || request()->routeIs('provider.billing.confirmation') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-4 text-center"></i> Factures
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
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-xs font-bold shrink-0 {{ $admShell ? 'user-avatar' : '' }}">
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
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
            @if($role === 'admin')
                @php
                    $schedulerLastRun = \Illuminate\Support\Facades\Cache::get('scheduler:last_run');
                    $schedulerStale = ! $schedulerLastRun || \Illuminate\Support\Carbon::parse($schedulerLastRun)->lt(now()->subMinutes(15));
                @endphp
                @if($schedulerStale)
                    <div class="mb-4 p-3 rounded-lg border text-sm flex items-start gap-2" style="background:#fff4e5;border-color:#f2b27a;color:#7a3204;">
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
