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
        .sidebar-link { @apply flex items-center gap-3 px-3 py-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition text-sm; }
        .sidebar-link.active { @apply bg-amber-500/10 text-amber-400 font-medium; }
    </style>
</head>
<body class="h-full bg-slate-950 text-white flex">
    @if(session('success'))
        <div id="success-toast"
             class="fixed top-4 right-4 z-[80] max-w-sm w-[calc(100%-2rem)] sm:w-auto px-4 py-3 rounded-lg border border-emerald-400/40 bg-emerald-500/15 text-emerald-200 shadow-2xl shadow-emerald-900/30 flex items-start gap-2 transition-opacity duration-300">
            <i class="fas fa-circle-check mt-0.5"></i>
            <p class="text-sm font-medium">{{ session('success') }}</p>
        </div>
    @endif

    {{-- ══════════════ SIDEBAR ══════════════ --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-50 w-64 bg-slate-900 border-r border-slate-800 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300">

        {{-- Brand --}}
        <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
            @if(!empty($siteBrand['logo_url']))
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-white/5 border border-slate-700 shrink-0 overflow-hidden p-0.5">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-amber-500 shrink-0">
                    <i class="fas fa-gem text-white text-base"></i>
                </div>
            @endif
            <div class="min-w-0">
                <p class="text-amber-400 font-bold text-sm leading-tight truncate">{{ $siteBrand['site_name'] }}</p>
                <p class="text-slate-500 text-xs truncate">{{ $siteBrand['site_slogan'] ?: 'Magazine Premium' }}</p>
            </div>
        </div>

        {{-- Role badge --}}
        <div class="px-5 py-3 border-b border-slate-800">
            @php $role = auth()->user()->role; @endphp
            <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                {{ $role === 'admin'    ? 'bg-rose-900/50 text-rose-300'    :
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
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">

            @if($role === 'admin')
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Administration</p>
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                </a>
                <a href="{{ route('admin.users.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                    <i class="fas fa-users w-4 text-center"></i> Utilisateurs
                </a>
                <a href="{{ route('admin.providers.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.providers.*') ? 'active' : '' }}">
                    <i class="fas fa-store w-4 text-center"></i> Prestataires
                </a>
                <a href="{{ route('admin.articles.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.articles.*') ? 'active' : '' }}">
                    <i class="fas fa-newspaper w-4 text-center"></i> Articles
                </a>
                <a href="{{ route('admin.events.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.events.*') ? 'active' : '' }}">
                    <i class="fas fa-calendar-days w-4 text-center"></i> Événements
                </a>
                <a href="{{ route('admin.reviews.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.reviews.*') ? 'active' : '' }}">
                    <i class="fas fa-star w-4 text-center"></i> Avis
                </a>
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Bouton Administration</p>
                <a href="{{ route('admin.administration.maintenance') }}"
                   class="sidebar-link {{ request()->routeIs('admin.administration.maintenance') ? 'active' : '' }}">
                    <i class="fas fa-screwdriver-wrench w-4 text-center"></i> Maintenance
                </a>
                <a href="{{ route('admin.administration.appearance') }}"
                   class="sidebar-link {{ request()->routeIs('admin.administration.appearance', 'admin.administration.social', 'admin.administration.media') ? 'active' : '' }}">
                    <i class="fas fa-palette w-4 text-center"></i> Apparence
                </a>
                <a href="{{ route('admin.administration.contact-messages.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.administration.contacts*', 'admin.administration.contact-messages*') ? 'active' : '' }}">
                    <i class="fas fa-address-book w-4 text-center"></i> Contacts
                </a>
                <a href="{{ route('admin.administration.settings') }}"
                   class="sidebar-link {{ request()->routeIs('admin.administration.settings*') ? 'active' : '' }}">
                    <i class="fas fa-gear w-4 text-center"></i> Paramètres
                </a>
                <a href="{{ route('admin.administration.partners') }}"
                   class="sidebar-link {{ request()->routeIs('admin.administration.partners') ? 'active' : '' }}">
                    <i class="fas fa-handshake w-4 text-center"></i> Partenaires
                </a>
                <a href="{{ route('admin.administration.info-center') }}"
                   class="sidebar-link {{ request()->routeIs('admin.administration.info-center') ? 'active' : '' }}">
                    <i class="fas fa-circle-info w-4 text-center"></i> Centre d'information
                </a>
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Finance</p>
                <a href="{{ route('admin.plans.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.plans.*') || request()->routeIs('admin.promo-codes.*') ? 'active' : '' }}">
                    <i class="fas fa-gem w-4 text-center"></i> Forfaits
                </a>
                <a href="{{ route('admin.payments.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}">
                    <i class="fas fa-credit-card w-4 text-center"></i> Paiements
                </a>
                <a href="{{ route('admin.subscriptions.index') }}"
                   class="sidebar-link {{ request()->routeIs('admin.subscriptions.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice w-4 text-center"></i> Abonnements
                </a>
                <a href="{{ route('admin.payments.settings') }}"
                   class="sidebar-link {{ request()->routeIs('admin.payments.settings*') ? 'active' : '' }}">
                    <i class="fas fa-sliders w-4 text-center"></i> Config paiement
                </a>
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Contenu</p>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-envelope-open-text w-4 text-center"></i> Newsletter
                </a>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-tags w-4 text-center"></i> Catégories &amp; Tags
                </a>
                <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider mt-3">Sécurité</p>
                <a href="{{ route('admin.permissions') }}"
                   class="sidebar-link {{ request()->routeIs('admin.permissions') ? 'active' : '' }}">
                    <i class="fas fa-key w-4 text-center"></i> Rôles &amp; Permissions
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
                <a href="#" class="sidebar-link">
                    <i class="fas fa-images w-4 text-center"></i> Médias
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
                <a href="#" class="sidebar-link">
                    <i class="fas fa-newspaper w-4 text-center"></i> Articles
                </a>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-calendar-days w-4 text-center"></i> Événements
                </a>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-compass w-4 text-center"></i> Annuaire
                </a>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-star w-4 text-center"></i> Mes avis
                </a>
                <a href="#" class="sidebar-link">
                    <i class="fas fa-envelope w-4 text-center"></i> Newsletter
                </a>
            @endif
        </nav>

        {{-- User footer --}}
        <div class="border-t border-slate-800 p-4">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-amber-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ auth()->user()->initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ auth()->user()->full_name }}</p>
                    <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Déconnexion"
                        class="text-slate-500 hover:text-red-400 transition text-sm">
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
    <div class="flex-1 flex flex-col min-h-screen lg:ml-64">

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
