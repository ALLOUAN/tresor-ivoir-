<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>@yield('title', 'Tableau de bord') — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        .sidebar-link:hover { background: #1e293b; color: #fff; }
        .sidebar-link.active {
            background: rgba(245, 158, 11, 0.12);
            color: #fbbf24;
            font-weight: 500;
        }
        /* ——— Sidebar administrateur ——— */
        .admin-sidebar {
            width: 18rem;
            background: linear-gradient(165deg, #0f172a 0%, #020617 55%, #0c1222 100%);
            border-right: 1px solid rgba(245, 158, 11, 0.12);
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.35);
        }
        .admin-sidebar-brand {
            background: linear-gradient(135deg, rgba(245, 158, 11, 0.08) 0%, transparent 55%);
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
            background: linear-gradient(90deg, rgba(100, 116, 139, 0.45), transparent);
        }
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
            background: rgba(255, 255, 255, 0.04);
            color: #f1f5f9;
        }
        .admin-sidebar .nav-row.is-active {
            font-weight: 600;
            background: linear-gradient(90deg, rgba(245, 158, 11, 0.14) 0%, rgba(245, 158, 11, 0.02) 100%);
            color: #fde68a;
            box-shadow: inset 3px 0 0 #f59e0b;
        }
        .admin-sidebar .nav-row-icon {
            width: 2.25rem;
            height: 2.25rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.55rem;
            background: rgba(30, 41, 59, 0.85);
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
            background: rgba(245, 158, 11, 0.18);
            border-color: rgba(245, 158, 11, 0.35);
            color: #fbbf24;
        }
        .admin-sidebar .nav-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(245, 158, 11, 0.35) transparent;
        }
        .admin-sidebar .role-pill {
            background: linear-gradient(135deg, rgba(190, 18, 60, 0.35), rgba(136, 19, 55, 0.25));
            border: 1px solid rgba(251, 113, 133, 0.25);
            box-shadow: 0 0 20px rgba(244, 63, 94, 0.12);
        }
        .admin-sidebar-footer {
            background: linear-gradient(180deg, transparent, rgba(15, 23, 42, 0.9));
            border-top: 1px solid rgba(148, 163, 184, 0.12);
        }
        .admin-sidebar .user-card {
            border-radius: 0.75rem;
            padding: 0.65rem 0.75rem;
            background: rgba(30, 41, 59, 0.55);
            border: 1px solid rgba(71, 85, 105, 0.35);
        }
        .admin-sidebar .user-avatar {
            box-shadow: 0 0 0 2px rgba(245, 158, 11, 0.35), 0 4px 12px rgba(0, 0, 0, 0.25);
        }
    </style>
</head>
<body class="h-full bg-slate-950 text-white flex">
    @php
        $role = auth()->user()->role;
        $sidebarMessagingUnreadCount = 0;
        if ($role === 'admin') {
            $sidebarMessagingUnreadCount = \App\Models\ProviderConversation::query()
                ->whereHas('messages', fn ($q) => $q
                    ->whereNull('read_at')
                    ->where('sender_id', '!=', auth()->id()))
                ->count();
        } elseif ($role === 'provider') {
            /** @var \App\Models\User $authUser */
            $authUser = auth()->user();
            $provider = $authUser->providers()->first();
            if ($provider) {
                $sidebarMessagingUnreadCount = \App\Models\ProviderConversation::query()
                    ->where('provider_id', $provider->id)
                    ->whereHas('messages', fn ($q) => $q
                        ->whereNull('read_at')
                        ->where('sender_id', '!=', auth()->id()))
                    ->count();
            }
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
            {{ $role === 'admin' ? 'admin-sidebar' : 'w-64 bg-slate-900 border-r border-slate-800' }}">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800 {{ $role === 'admin' ? 'admin-sidebar-brand !border-b-0' : '' }}">
            @if(!empty($siteBrand['logo_url']))
                <div class="flex items-center justify-center {{ $role === 'admin' ? 'w-10 h-10 rounded-xl' : 'w-9 h-9 rounded-lg' }} bg-white/5 border border-slate-700 shrink-0 overflow-hidden p-0.5">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="flex items-center justify-center {{ $role === 'admin' ? 'w-10 h-10 rounded-xl shadow-lg shadow-amber-900/40' : 'w-9 h-9 rounded-lg' }} bg-gradient-to-br from-amber-400 to-amber-600 shrink-0">
                    <i class="fas fa-gem text-white {{ $role === 'admin' ? 'text-lg' : 'text-base' }}"></i>
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-amber-400 font-bold {{ $role === 'admin' ? 'text-[0.95rem] tracking-tight' : 'text-sm' }} leading-tight truncate">{{ $siteBrand['site_name'] }}</p>
                <p class="text-slate-500 {{ $role === 'admin' ? 'text-[11px]' : 'text-xs' }} truncate">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
            </div>
        </div>

        {{-- Role badge --}}
        <div class="px-5 py-3 border-b border-slate-800 {{ $role === 'admin' ? '!border-slate-700/50' : '' }}">
            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                {{ $role === 'admin'    ? 'role-pill text-rose-100' :
                  ($role === 'editor'   ? 'bg-blue-900/50 text-blue-300'    :
                  ($role === 'provider' ? 'bg-violet-900/50 text-violet-300' :
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
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5 {{ $role === 'admin' ? 'nav-scroll' : '' }}">

            @if($role === 'admin')
                <p class="nav-section-title"><span>Général</span></p>
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-row {{ request()->routeIs('admin.dashboard') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-gauge-high"></i></span>
                    <span>Tableau de bord</span>
                </a>
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
                <a href="{{ route('admin.conversations.index') }}"
                   class="nav-row {{ request()->routeIs('admin.conversations.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-comments"></i></span>
                    <span class="flex-1">Messagerie</span>
                    @if($sidebarMessagingUnreadCount > 0)
                        <span class="inline-flex min-w-[1.35rem] h-[1.35rem] px-1.5 items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarMessagingUnreadCount > 99 ? '99+' : $sidebarMessagingUnreadCount }}
                        </span>
                    @endif
                </a>
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
                <a href="{{ route('admin.administration.media') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.media*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-images"></i></span>
                    <span>Gestion des galerie photo</span>
                </a>
                <a href="{{ route('admin.reviews.index') }}"
                   class="nav-row {{ request()->routeIs('admin.reviews.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-star"></i></span>
                    <span>Avis</span>
                </a>
                <p class="nav-section-title"><span>Site &amp; maintenance</span></p>
                <a href="{{ route('admin.administration.maintenance') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.maintenance') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-screwdriver-wrench"></i></span>
                    <span>Maintenance</span>
                </a>
                <a href="{{ route('admin.administration.appearance') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.appearance', 'admin.administration.social', 'admin.administration.media') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-palette"></i></span>
                    <span>Apparence</span>
                </a>
                <a href="{{ route('admin.administration.contact-messages.index') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.contacts*', 'admin.administration.contact-messages*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-address-book"></i></span>
                    <span>Contacts</span>
                </a>
                <a href="{{ route('admin.administration.settings') }}"
                   class="nav-row {{ request()->routeIs('admin.administration.settings*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-gear"></i></span>
                    <span>Paramètres</span>
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
                <p class="nav-section-title"><span>Finance</span></p>
                <a href="{{ route('admin.plans.index') }}"
                   class="nav-row {{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.promo-codes.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-gem"></i></span>
                    <span>Forfaits</span>
                </a>
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
                <a href="{{ route('admin.payments.settings') }}"
                   class="nav-row {{ request()->routeIs('admin.payments.settings*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-sliders"></i></span>
                    <span>Config paiement</span>
                </a>
                <p class="nav-section-title"><span>Contenu</span></p>
                <a href="{{ route('admin.newsletter.index') }}"
                   class="nav-row {{ request()->routeIs('admin.newsletter.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-envelope-open-text"></i></span>
                    <span>Newsletter</span>
                </a>
                <a href="#" class="nav-row opacity-70 hover:opacity-100">
                    <span class="nav-row-icon"><i class="fas fa-tags"></i></span>
                    <span>Catégories &amp; tags</span>
                </a>
                <p class="nav-section-title"><span>Sécurité</span></p>
                <a href="{{ route('admin.analytics.index') }}"
                   class="nav-row {{ request()->routeIs('admin.analytics.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-chart-line"></i></span>
                    <span>Analytics</span>
                </a>
                <a href="{{ route('admin.audit.index') }}"
                   class="nav-row {{ request()->routeIs('admin.audit.*') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-clipboard-list"></i></span>
                    <span>Journal d'audit</span>
                </a>
                <a href="{{ route('admin.permissions') }}"
                   class="nav-row {{ request()->routeIs('admin.permissions') ? 'is-active' : '' }}">
                    <span class="nav-row-icon"><i class="fas fa-key"></i></span>
                    <span>Rôles &amp; permissions</span>
                </a>

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
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Mon établissement</p>
                <a href="{{ route('provider.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('provider.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                </a>
                <a href="{{ route('provider.profile.edit') }}"
                   class="sidebar-link {{ request()->routeIs('provider.profile.*') ? 'active' : '' }}">
                    <i class="fas fa-store w-4 text-center"></i> Ma fiche
                </a>
                <a href="{{ route('provider.reviews.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star w-4 text-center"></i> Mes avis
                </a>
                <a href="{{ route('provider.analytics') }}"
                   class="sidebar-link {{ request()->routeIs('provider.analytics') ? 'active' : '' }}">
                    <i class="fas fa-chart-line w-4 text-center"></i> Statistiques
                </a>
                <a href="{{ route('provider.media.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.media.*') ? 'active' : '' }}">
                    <i class="fas fa-images w-4 text-center"></i> Médias
                </a>
                <a href="{{ route('provider.conversations.index') }}"
                   class="sidebar-link {{ request()->routeIs('provider.conversations.*') ? 'active' : '' }}">
                    <i class="fas fa-comments w-4 text-center"></i>
                    <span class="flex-1">Messagerie admin</span>
                    @if($sidebarMessagingUnreadCount > 0)
                        <span class="inline-flex min-w-[1.2rem] h-[1.2rem] px-1 items-center justify-center rounded-full bg-amber-500 text-black text-[10px] font-bold leading-none">
                            {{ $sidebarMessagingUnreadCount > 99 ? '99+' : $sidebarMessagingUnreadCount }}
                        </span>
                    @endif
                </a>
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Abonnement</p>
                <a href="{{ route('provider.billing.plans') }}"
                   class="sidebar-link {{ request()->routeIs('provider.billing.plans') || request()->routeIs('provider.billing.checkout') ? 'active' : '' }}">
                    <i class="fas fa-gem w-4 text-center"></i> Mon forfait
                </a>
                <a href="{{ route('provider.billing.invoices') }}"
                   class="sidebar-link {{ request()->routeIs('provider.billing.invoices') || request()->routeIs('provider.billing.confirmation') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-4 text-center"></i> Factures
                </a>

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

        {{-- User footer --}}
        <div class="border-t border-slate-800 p-4 {{ $role === 'admin' ? 'admin-sidebar-footer' : '' }}">
            <div class="flex items-center gap-3 {{ $role === 'admin' ? 'user-card' : '' }}">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-xs font-bold shrink-0 {{ $role === 'admin' ? 'user-avatar' : '' }}">
                    {{ auth()->user()->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion"
                        class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-red-500/15 hover:text-red-400 transition text-sm {{ $role === 'admin' ? 'border border-slate-700/60 hover:border-red-500/30' : '' }}">
                        <i class="fas fa-right-from-bracket"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Overlay mobile --}}
    <div id="sidebar-overlay"
        class="fixed inset-0 z-40 bg-black/60 lg:hidden hidden"
        onclick="toggleSidebar()"></div>

    {{-- ══════════════ MAIN ══════════════ --}}
    <div class="flex-1 flex flex-col min-h-screen {{ $role === 'admin' ? 'lg:ml-[18rem]' : 'lg:ml-64' }}">

        {{-- Top bar --}}
        <header class="sticky top-0 z-30 bg-slate-900/80 backdrop-blur border-b border-slate-800 flex items-center justify-between px-4 sm:px-6 h-14">
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
                            <span class="absolute -top-1 -right-1 min-w-[1.1rem] h-[1.1rem] px-1 rounded-full bg-amber-500 text-black text-[10px] font-bold leading-[1.1rem] text-center">
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
                    {{ now()->isoFormat('dddd D MMMM YYYY') }}
                </span>
            </div>
        </header>

        {{-- Page content --}}
        <main class="flex-1 p-4 sm:p-6 lg:p-8">
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
    @stack('scripts')
</body>
</html>
