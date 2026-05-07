@auth
{{-- ── Panneau dashboard glissant (public → dashboard sans changement de page) ── --}}
<div id="dash-panel" class="fixed inset-0 z-[200] hidden" role="dialog" aria-modal="true">

    {{-- Backdrop --}}
    <div id="dash-panel-backdrop" class="absolute inset-0 bg-black/60 backdrop-blur-sm"></div>

    {{-- Panel glissant depuis la droite --}}
    <div id="dash-panel-inner"
         class="absolute right-0 inset-y-0 flex w-full max-w-5xl shadow-2xl
                transform translate-x-full transition-transform duration-300 ease-out">

        {{-- ── Colonne sidebar (gauche) ────────────────────────────────── --}}
        <div class="w-64 shrink-0 bg-slate-900 border-r border-slate-800 flex flex-col overflow-y-auto">

            {{-- Marque --}}
            <div class="flex items-center gap-3 px-5 py-5 border-b border-slate-800">
                <div class="flex items-center justify-center w-9 h-9 rounded-lg bg-gradient-to-br from-amber-400 to-amber-600 shrink-0">
                    <i class="fas fa-gem text-white text-base"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-amber-400 font-bold text-sm leading-tight truncate">Trésors d'Ivoire</p>
                    <p class="text-slate-500 text-xs truncate">Magazine Premium</p>
                </div>
            </div>

            {{-- Rôle --}}
            <div class="px-5 py-3 border-b border-slate-800">
                <span class="inline-flex items-center gap-1.5 text-xs font-medium px-2.5 py-1 rounded-full
                    {{ auth()->user()->role === 'admin'    ? 'bg-red-900/50 text-red-300'     :
                      (auth()->user()->role === 'editor'   ? 'bg-blue-900/50 text-blue-300'   :
                      (auth()->user()->role === 'provider' ? 'bg-purple-900/50 text-purple-300' :
                       'bg-emerald-900/50 text-emerald-300')) }}">
                    <i class="fas fa-user text-[10px]"></i>
                    {{ ucfirst(auth()->user()->role) }}
                </span>
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-0.5">
                @if(auth()->user()->role === 'admin')
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Administration</p>
                    <button class="dp-nav-link" data-url="{{ route('admin.dashboard') }}" data-title="Tableau de bord">
                        <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('admin.users.index') }}" data-title="Utilisateurs">
                        <i class="fas fa-users w-4 text-center"></i> Utilisateurs
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('admin.providers.index') }}" data-title="Prestataires">
                        <i class="fas fa-store w-4 text-center"></i> Prestataires
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('admin.articles.index') }}" data-title="Articles">
                        <i class="fas fa-newspaper w-4 text-center"></i> Articles
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('admin.payments.index') }}" data-title="Finance">
                        <i class="fas fa-chart-line w-4 text-center"></i> Finance
                    </button>

                @elseif(auth()->user()->role === 'editor')
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Rédaction</p>
                    <button class="dp-nav-link" data-url="{{ route('editor.dashboard') }}" data-title="Tableau de bord">
                        <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('editor.articles.index') }}" data-title="Mes articles">
                        <i class="fas fa-pen-nib w-4 text-center"></i> Mes articles
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('editor.articles.create') }}" data-title="Nouvel article">
                        <i class="fas fa-circle-plus w-4 text-center"></i> Nouvel article
                    </button>

                @elseif(auth()->user()->role === 'provider')
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Mon établissement</p>
                    <button class="dp-nav-link" data-url="{{ route('provider.dashboard') }}" data-title="Tableau de bord">
                        <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('provider.profile.edit') }}" data-title="Ma fiche">
                        <i class="fas fa-store w-4 text-center"></i> Ma fiche
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('provider.reviews.index') }}" data-title="Mes avis">
                        <i class="fas fa-star w-4 text-center"></i> Mes avis
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('provider.analytics') }}" data-title="Statistiques">
                        <i class="fas fa-chart-line w-4 text-center"></i> Statistiques
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('provider.billing.plans') }}" data-title="Mon forfait">
                        <i class="fas fa-gem w-4 text-center"></i> Mon forfait
                    </button>

                @else {{-- visitor --}}
                    <p class="px-3 py-1.5 text-xs font-semibold text-slate-600 uppercase tracking-wider">Espace visiteur</p>
                    <button class="dp-nav-link" data-url="{{ route('visitor.dashboard') }}" data-title="Tableau de bord">
                        <i class="fas fa-gauge-high w-4 text-center"></i> Tableau de bord
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('visitor.purchases.index') }}" data-title="Mes achats">
                        <i class="fas fa-image w-4 text-center"></i> Mes achats
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('visitor.profile.edit') }}" data-title="Mon profil">
                        <i class="fas fa-user-pen w-4 text-center"></i> Mon profil
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('visitor.favorites.index') }}" data-title="Mes favoris">
                        <i class="fas fa-heart w-4 text-center"></i> Mes favoris
                    </button>
                    <button class="dp-nav-link" data-url="{{ route('visitor.notifications.index') }}" data-title="Notifications">
                        <i class="fas fa-bell w-4 text-center"></i> Notifications
                    </button>
                @endif
            </nav>

            {{-- Footer utilisateur --}}
            <div class="border-t border-slate-800 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-amber-600 flex items-center justify-center text-white text-xs font-bold shrink-0">
                        {{ strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                        <p class="text-slate-500 text-xs truncate">{{ auth()->user()->email }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Déconnexion"
                                class="flex h-9 w-9 items-center justify-center rounded-lg text-slate-500 hover:bg-red-500/15 hover:text-red-400 transition text-sm">
                            <i class="fas fa-right-from-bracket"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Zone de contenu (droite) ────────────────────────────────── --}}
        <div class="flex-1 bg-slate-950 flex flex-col overflow-hidden">

            {{-- Barre titre --}}
            <div class="flex items-center justify-between px-5 py-3.5 border-b border-slate-800 shrink-0">
                <p class="text-white font-semibold text-sm" id="dash-panel-title">Tableau de bord</p>
                <button type="button" id="dash-panel-close"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>

            {{-- Contenu AJAX --}}
            <div class="flex-1 overflow-y-auto p-4 sm:p-6 text-white" id="dash-panel-content">
                <div class="flex items-center justify-center h-48">
                    <i class="fas fa-spinner fa-spin text-amber-400 text-2xl"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .dp-nav-link {
        display: flex; align-items: center; gap: 0.625rem;
        width: 100%; padding: 0.5rem 0.75rem; border-radius: 0.5rem;
        font-size: 0.875rem; color: #94a3b8; text-align: left;
        transition: background .15s, color .15s; cursor: pointer; background: none; border: none;
    }
    .dp-nav-link:hover { background: #1e293b; color: #fff; }
    .dp-nav-link.dp-active { background: rgba(245,158,11,0.12); color: #fbbf24; font-weight: 500; }
</style>

<script>
(function () {
    const panel    = document.getElementById('dash-panel');
    const inner    = document.getElementById('dash-panel-inner');
    const backdrop = document.getElementById('dash-panel-backdrop');
    const closeBtn = document.getElementById('dash-panel-close');
    const content  = document.getElementById('dash-panel-content');
    const title    = document.getElementById('dash-panel-title');
    if (!panel) return;

    function openPanel(initialUrl, initialTitle) {
        panel.classList.remove('hidden');
        document.body.style.overflow = 'hidden';
        requestAnimationFrame(() => {
            requestAnimationFrame(() => inner.classList.remove('translate-x-full'));
        });
        if (initialUrl) loadContent(initialUrl, initialTitle);
    }

    function closePanel() {
        inner.classList.add('translate-x-full');
        document.body.style.overflow = '';
        setTimeout(() => panel.classList.add('hidden'), 310);
    }

    async function loadContent(url, pageTitle) {
        if (title && pageTitle) title.textContent = pageTitle;
        content.innerHTML = '<div class="flex items-center justify-center h-48"><i class="fas fa-spinner fa-spin text-amber-400 text-2xl"></i></div>';
        try {
            const res  = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' }, credentials: 'same-origin' });
            const html = await res.text();
            const doc  = new DOMParser().parseFromString(html, 'text/html');
            const main = doc.querySelector('main') || doc.querySelector('[role="main"]');
            content.innerHTML = main ? main.innerHTML : '<p class="text-slate-400">Contenu non disponible.</p>';
        } catch (e) {
            content.innerHTML = '<p class="text-red-400 p-4">Erreur de chargement.</p>';
        }
    }

    // Liens de navigation internes au panneau
    document.querySelectorAll('.dp-nav-link').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.dp-nav-link').forEach(b => b.classList.remove('dp-active'));
            btn.classList.add('dp-active');
            loadContent(btn.dataset.url, btn.dataset.title);
        });
    });

    closeBtn?.addEventListener('click', closePanel);
    backdrop?.addEventListener('click', closePanel);
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });

    // Exposer openPanel globalement pour les boutons Dashboard
    window.openDashPanel = openPanel;
})();
</script>
@endauth
