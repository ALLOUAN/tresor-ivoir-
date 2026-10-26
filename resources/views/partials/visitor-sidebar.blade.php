{{--
    Barre latérale de l'espace client (layouts.visitor-public). Remplace les onglets horizontaux
    (partials.visitor-account-nav) : toutes les rubriques visibles d'un coup d'œil, regroupées, avec icônes.
      - Ordinateur (≥ 1024 px) : colonne fixe à gauche, qui reste visible au défilement.
      - Tablette et mobile : panneau qui glisse depuis la gauche (bouton « Menu » de la barre client), avec voile.
    Carte d'identité en tête sur fond vert forêt avec motifs ivoiriens discrets. Pas de dégradé ; ombre au survol seulement.
--}}
@php
    $vsUser = auth()->user();
    $vsUnreadMessages = $vsUser ? \App\Models\Conversation::where('client_id', $vsUser->id)
        ->whereHas('messages', fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', $vsUser->id))
        ->count() : 0;
    $vsUnreadNotifs = $vsUser ? $vsUser->unreadNotifications()->count() : 0;
    $vsSecurity = request()->routeIs('visitor.profile.edit') && request()->query('securite');

    $vsGroups = [
        [null, [
            ['label' => __('Tableau de bord'), 'icon' => 'widget', 'route' => 'visitor.dashboard', 'on' => request()->routeIs('visitor.dashboard')],
            ['label' => __('Messages'), 'icon' => 'chat-round-dots', 'route' => 'visitor.conversations.index', 'on' => request()->routeIs('visitor.conversations.*'), 'badge' => $vsUnreadMessages],
            ['label' => __('Notifications'), 'icon' => 'bell', 'route' => 'visitor.notifications.index', 'on' => request()->routeIs('visitor.notifications.*'), 'badge' => $vsUnreadNotifs],
            ['label' => __('Mes favoris'), 'icon' => 'heart', 'route' => 'visitor.favorites.index', 'on' => request()->routeIs('visitor.favorites.*')],
        ]],
        [__('Mes réservations'), [
            ['label' => __('Hébergements'), 'icon' => 'bed', 'route' => 'visitor.reservations.index', 'on' => request()->routeIs('visitor.reservations.*')],
            ['label' => __('Tables & activités'), 'icon' => 'plate', 'route' => 'visitor.booking-requests.index', 'on' => request()->routeIs('visitor.booking-requests.*')],
            ['label' => __('Mes visites'), 'icon' => 'ticket', 'route' => 'visitor.tourist-visits.index', 'on' => request()->routeIs('visitor.tourist-visits.*')],
            ['label' => __('Mes voyages'), 'icon' => 'route', 'route' => 'visitor.tour-bookings.index', 'on' => request()->routeIs('visitor.tour-bookings.*')],
            ['label' => __('Mes trajets'), 'icon' => 'car', 'route' => 'visitor.transport-bookings.index', 'on' => request()->routeIs('visitor.transport-bookings.*')],
            ['label' => __('Mes activités'), 'icon' => 'masks', 'route' => 'visitor.activity-bookings.index', 'on' => request()->routeIs('visitor.activity-bookings.*')],
        ]],
        [__('Achats & paiements'), [
            ['label' => __('Mes reçus'), 'icon' => 'notes', 'route' => 'visitor.receipts.index', 'on' => request()->routeIs('visitor.receipts.*')],
            ['label' => __('Portefeuille'), 'icon' => 'wallet-money', 'route' => 'visitor.wallet.index', 'on' => request()->routeIs('visitor.wallet.*')],
            ['label' => __('Photos achetées'), 'icon' => 'camera', 'route' => 'visitor.purchases.index', 'on' => request()->routeIs('visitor.purchases.*')],
            ['label' => __('Commandes d\'art'), 'icon' => 'palette', 'route' => 'visitor.art-orders.index', 'on' => request()->routeIs('visitor.art-orders.*')],
            ['label' => __('Œuvres sur mesure'), 'icon' => 'ruler-angular', 'route' => 'visitor.art-commissions.index', 'on' => request()->routeIs('visitor.art-commissions.*')],
        ]],
        [__('Mon compte'), [
            ['label' => __('Mon profil'), 'icon' => 'user-rounded', 'route' => 'visitor.profile.edit', 'on' => request()->routeIs('visitor.profile.edit') && ! $vsSecurity],
            ['label' => __('Sécurité'), 'icon' => 'shield-check', 'route' => 'visitor.profile.edit', 'params' => ['securite' => 1], 'on' => (bool) $vsSecurity],
        ]],
    ];
@endphp

<div class="vs-scrim" data-vs-scrim hidden></div>
<aside class="vs" id="vs-sidebar" aria-label="{{ __('Mon espace') }}" data-vs>
    <div class="vs__scroll">
        @if($vsUser)
        <div class="vs-card">
            @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
            <div class="vs-card__in">
                <span class="vs-card__avatar" aria-hidden="true">{{ $vsUser->initials }}</span>
                <span class="vs-card__who">
                    <strong>{{ $vsUser->first_name }} {{ $vsUser->last_name }}</strong>
                    <small>{{ __('Membre depuis :date', ['date' => $vsUser->created_at?->translatedFormat('F Y')]) }}</small>
                </span>
            </div>
            <button type="button" class="vs-close" data-vs-close aria-label="{{ __('Fermer le menu') }}">
                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
            </button>
        </div>
        @endif

        <nav class="vs-nav">
            @foreach($vsGroups as [$vsTitle, $vsItems])
                <div class="vs-group">
                    @if($vsTitle)<p class="vs-group__title">{{ $vsTitle }}</p>@endif
                    <ul role="list">
                        @foreach($vsItems as $it)
                            <li>
                                <a href="{{ route($it['route'], $it['params'] ?? []) }}" class="vs-item {{ $it['on'] ? 'is-on' : '' }}" @if($it['on']) aria-current="page" @endif>
                                    <span class="vs-item__ico">
                                        @if($it['icon'] === 'bell')
                                            <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 1 1 12 0c0 4.5 1.5 6.5 2.5 7.5h-17C4.5 15.5 6 13.5 6 9Z"/><path d="M10 19.5a2.2 2.2 0 0 0 4 0"/></svg>
                                        @else
                                            <x-icon :name="$it['icon']" />
                                        @endif
                                    </span>
                                    <span class="vs-item__label">{{ $it['label'] }}</span>
                                    @if(! empty($it['badge']))
                                        <span class="vs-badge" aria-label="{{ __(':n non lus', ['n' => $it['badge']]) }}">{{ $it['badge'] > 99 ? '99+' : $it['badge'] }}</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </nav>

        <a href="{{ route('accommodations.index') }}" class="vs-cta">
            <span class="vs-cta__ico"><x-icon name="compass" /></span>
            <span>
                <strong>{{ __('Où partez-vous ensuite ?') }}</strong>
                <small>{{ __('Trouver un hébergement') }}</small>
            </span>
        </a>

        {{-- Mobile uniquement (masqués dans la barre client sur petit écran). --}}
        <div class="vs-foot">
            <a href="{{ route('home') }}" class="vs-foot__btn">
                <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg>{{ __('Retour au site') }}
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit" class="vs-foot__btn is-out">
                    <svg viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 16l-4-4 4-4"/><path d="M6 12h10"/></svg>{{ __('Déconnexion') }}
                </button>
            </form>
        </div>
    </div>
</aside>

<style>
    /* ── Barre latérale de l'espace client (.vs-*) ── */
    .vs { font-family: 'Montserrat', sans-serif; }
    .vs__scroll { display: flex; flex-direction: column; gap: 14px; }

    .vs-card { position: relative; overflow: hidden; border-radius: 22px; background: #00874F; color: #ffffff; padding: 22px 18px; }
    .vs-card > .ivm .ivm-g { opacity: .1 !important; }
    .vs-card > .ivm .ivm-g > * { stroke: #D8E9E0 !important; }
    .vs-card > .ivm .ivm-g > circle[fill] { fill: #EB6E00 !important; stroke: #EB6E00 !important; }
    .vs-card__in { position: relative; z-index: 1; display: flex; align-items: center; gap: 12px; }
    .vs-card__avatar { width: 46px; height: 46px; flex: none; border-radius: 14px; background: #EB6E00; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; font-weight: 800; letter-spacing: .02em; }
    .vs-card__who { display: flex; flex-direction: column; min-width: 0; line-height: 1.2; }
    .vs-card__who strong { font-family: 'Montserrat', serif; font-size: 17px; font-weight: 700; color: #ffffff; overflow: hidden; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; }
    .vs-card__who small { margin-top: 4px; font-size: 12px; color: rgb(255 255 255 / .72); }
    .vs-close { display: none; }

    .vs-nav { display: flex; flex-direction: column; gap: 6px; padding: 8px; border-radius: 22px; background: #ffffff; border: 1px solid #D8E9E0; }
    .vs-group + .vs-group { padding-top: 6px; border-top: 1px solid #F3F8F5; }
    .vs-group__title { margin: 8px 10px 4px; font-size: 10.5px; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; color: #557466; }
    .vs-group ul { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 2px; }
    .vs-item { position: relative; display: flex; align-items: center; gap: 11px; min-height: 42px; padding: 5px 10px 5px 6px; border-radius: 14px; color: #0E3B2A !important; font-size: 14px; font-weight: 600; text-decoration: none; transition: background .15s ease, color .15s ease; }
    .vs-item__ico { width: 32px; height: 32px; flex: none; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #F3F8F5; color: #EB6E00; font-size: 16px; transition: background .15s ease, color .15s ease; }
    .vs-item__ico svg { width: 18px; height: 18px; }
    .vs-item__ico .ico > svg { width: 18px; height: 18px; }
    .vs-item__label { flex: 1; min-width: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vs-item:hover { background: #F3F8F5; color: #B85600 !important; }
    .vs-item.is-on { background: #EB6E00; color: #ffffff !important; }
    .vs-item.is-on .vs-item__ico { background: rgb(255 255 255 / .16); color: #ffffff; }
    .vs-item:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
    .vs-badge { min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px; background: #00874F; color: #ffffff; font-size: 11px; font-weight: 800; line-height: 20px; text-align: center; }
    .vs-item.is-on .vs-badge { background: #ffffff; color: #EB6E00; }

    .vs-cta { display: flex; align-items: center; gap: 12px; padding: 14px; border-radius: 20px; background: #F3F8F5; border: 1px solid #D8E9E0; color: #0E3B2A !important; text-decoration: none; transition: border-color .15s ease, box-shadow .15s ease; }
    .vs-cta:hover { border-color: #EB6E00; box-shadow: 0 18px 36px -26px rgb(14 59 42 / .5); }
    .vs-cta__ico { width: 40px; height: 40px; flex: none; border-radius: 12px; background: #EB6E00; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
    .vs-cta strong { display: block; font-size: 14px; font-weight: 700; }
    .vs-cta small { display: block; margin-top: 2px; font-size: 12.5px; font-weight: 600; color: #EB6E00; }

    .vs-foot { display: none; }
    @media (max-width: 767px) {
        .vs-foot { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
        .vs-foot__btn { width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 44px; padding: 0 12px; border-radius: 14px; border: 1px solid #D8E9E0; background: #ffffff; color: #0E3B2A !important; font: inherit; font-size: 13.5px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .vs-foot__btn svg { width: 17px; height: 17px; flex: none; }
        .vs-foot__btn.is-out { color: #8a241c !important; border-color: #f2d3cf; background: #FFFFFF; }
    }

    /* Ordinateur : colonne collée sous la barre client, défilement propre si la liste dépasse. */
    @media (min-width: 1024px) {
        .vs { position: sticky; top: 84px; align-self: start; max-height: calc(100vh - 100px); overflow-y: auto; scrollbar-width: thin; padding-bottom: 4px; }
        .vs-scrim { display: none !important; }
    }
    /* Tablette / mobile : panneau coulissant à gauche avec voile (ouvert par [data-vs-open]). */
    @media (max-width: 1023px) {
        .vs { position: fixed; top: 0; bottom: 0; left: 0; z-index: 70; width: min(320px, 86vw); padding: 12px; background: #F3F8F5; overflow-y: auto; transform: translateX(-104%); transition: transform .25s ease; }
        .vs.is-open { transform: none; box-shadow: 0 0 60px rgb(14 59 42 / .25); }
        .vs-scrim { position: fixed; inset: 0; z-index: 69; background: rgb(14 59 42 / .45); }
        .vs-close { position: absolute; top: 12px; right: 12px; z-index: 2; display: inline-flex; align-items: center; justify-content: center; width: 34px; height: 34px; border: 0; border-radius: 999px; background: rgb(255 255 255 / .14); color: #ffffff; cursor: pointer; }
        .vs-close svg { width: 16px; height: 16px; }
        .vs-card__in { padding-right: 36px; }
        body.vs-lock { overflow: hidden; }
    }
    @media (prefers-reduced-motion: reduce) { .vs, .vs-item, .vs-item__ico, .vs-cta { transition: none; } }
</style>
<script>
(function () {
    var side = document.querySelector('[data-vs]');
    var scrim = document.querySelector('[data-vs-scrim]');
    if (!side) return;
    var opener = null;
    function open(btn) {
        opener = btn || null;
        side.classList.add('is-open');
        scrim.hidden = false;
        document.body.classList.add('vs-lock');
        document.querySelectorAll('[data-vs-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'true'); });
        var first = side.querySelector('.vs-item.is-on, .vs-item');
        if (first) first.focus();
    }
    function close() {
        side.classList.remove('is-open');
        scrim.hidden = true;
        document.body.classList.remove('vs-lock');
        document.querySelectorAll('[data-vs-open]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
        if (opener) opener.focus();
    }
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-vs-open]');
        if (btn) { e.preventDefault(); side.classList.contains('is-open') ? close() : open(btn); return; }
        if (e.target.closest('[data-vs-close]') || e.target === scrim) close();
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && side.classList.contains('is-open')) close(); });
    // Revenu en grand écran : on referme le panneau mobile.
    window.matchMedia('(min-width: 1024px)').addEventListener('change', function (m) { if (m.matches) close(); });
})();
</script>
