@php
    // Réservations par type : regroupées dans un seul menu (13 onglets ne tenaient pas sur une ligne).
    $vanBookings = [
        ['label' => __('Hébergements'), 'icon' => 'fa-bed', 'route' => 'visitor.reservations.index', 'active' => request()->routeIs('visitor.reservations.*')],
        ['label' => __('Tables & activités'), 'icon' => 'fa-utensils', 'route' => 'visitor.booking-requests.index', 'active' => request()->routeIs('visitor.booking-requests.*')],
        ['label' => __('Mes visites'), 'icon' => 'fa-ticket', 'route' => 'visitor.tourist-visits.index', 'active' => request()->routeIs('visitor.tourist-visits.*')],
        ['label' => __('Mes voyages'), 'icon' => 'fa-route', 'route' => 'visitor.tour-bookings.index', 'active' => request()->routeIs('visitor.tour-bookings.*')],
        ['label' => __('Mes trajets'), 'icon' => 'fa-car-side', 'route' => 'visitor.transport-bookings.index', 'active' => request()->routeIs('visitor.transport-bookings.*')],
        ['label' => __('Mes activités'), 'icon' => 'fa-masks-theater', 'route' => 'visitor.activity-bookings.index', 'active' => request()->routeIs('visitor.activity-bookings.*')],
    ];
    $vanAccount = [
        ['label' => __('Mon profil'), 'icon' => 'fa-user-pen', 'route' => 'visitor.profile.edit', 'active' => request()->routeIs('visitor.profile.edit') && ! request()->query('securite')],
        ['label' => __('Sécurité'), 'icon' => 'fa-shield-halved', 'route' => 'visitor.profile.edit', 'params' => ['securite' => 1], 'active' => request()->routeIs('visitor.profile.edit') && request()->query('securite')],
    ];
    $vanBookingOn = collect($vanBookings)->firstWhere('active', true);
    $vanAccountOn = collect($vanAccount)->contains('active', true);
    $accountNavUnreadMessages = \App\Models\Conversation::where('client_id', auth()->id())
        ->whereHas('messages', fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', auth()->id()))
        ->count();
@endphp
<style>
    /* Onglets de l'espace client : 6 entrées qui tiennent sur une ligne ; les réservations par type
       et le compte sont dans des menus déroulants. Sur mobile : grille de 3 colonnes, sans défilement caché. */
    .van { position: relative; z-index: 30; display: flex; flex-wrap: wrap; align-items: center; gap: 6px; margin: 0 0 20px; padding: 6px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; font-family: 'Inter', sans-serif; }
    .van-item, .van-dd > summary { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 42px; padding: 0 16px; border-radius: 16px; color: #3a2f22 !important; font-size: 14px; font-weight: 600; white-space: nowrap; text-decoration: none; cursor: pointer; list-style: none; transition: background .15s ease, color .15s ease; }
    .van-dd > summary::-webkit-details-marker { display: none; }
    .van-item i, .van-dd > summary i { font-size: 13px; color: #b9500a; }
    .van-item:hover, .van-dd > summary:hover { background: #fdf4ec; color: #9a4308 !important; }
    .van-item.is-on, .van-dd.is-on > summary { background: #b9500a; color: #fff !important; }
    .van-item.is-on i, .van-dd.is-on > summary i { color: #fff; }
    .van-caret { font-size: 10px !important; transition: transform .2s ease; }
    .van-dd[open] .van-caret { transform: rotate(180deg); }
    .van-badge { display: inline-flex; align-items: center; justify-content: center; min-width: 19px; height: 19px; padding: 0 5px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 10.5px; font-weight: 800; line-height: 1; }
    .van-item.is-on .van-badge { background: rgba(255,255,255,.25); }
    .van-dd { position: relative; }
    .van-menu { position: absolute; left: 0; top: calc(100% + 8px); min-width: 240px; padding: 6px; border-radius: 18px; background: #fff; border: 1px solid #e6dccb; box-shadow: 0 24px 50px -28px rgba(29,26,22,.45); }
    .van-dd.is-end .van-menu { left: auto; right: 0; }
    .van-menu a, .van-menu button { display: flex; align-items: center; gap: 12px; width: 100%; padding: 10px 12px; border: 0; border-radius: 12px; background: none; color: #2f2a22 !important; font: inherit; font-size: 14px; font-weight: 600; text-align: left; text-decoration: none; cursor: pointer; }
    .van-menu a i, .van-menu button i { width: 30px; height: 30px; flex: none; border-radius: 10px; display: inline-flex; align-items: center; justify-content: center; background: #fdf4ec; color: #b9500a; font-size: 13px; }
    .van-menu a:hover { background: #fdf4ec; color: #9a4308 !important; }
    .van-menu a.is-on { background: #f6f3ec; color: #9a4308 !important; }
    .van-menu a.is-on::after { content: '✓'; margin-left: auto; font-weight: 800; color: #b9500a; }
    .van-menu hr { margin: 6px 4px; border: 0; border-top: 1px solid #f0eadf; }
    .van-menu button { color: #8a241c !important; }
    .van-menu button i { background: #fdeceb; color: #c0362c; }
    .van-menu button:hover { background: #fdeceb; }
    .van a:focus-visible, .van summary:focus-visible, .van button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
    .van-box { container-type: inline-size; margin: 0 0 20px; }
    .van-box > .van { margin: 0; }
    @container (min-width: 601px) and (max-width: 940px) {
        .van { flex-wrap: nowrap; justify-content: space-between; gap: 2px; }
        .van-item, .van-dd > summary { gap: 6px; padding: 0 10px; font-size: 13px; }
        .van-item > i, .van-dd > summary > i:first-child { display: none; }
    }
    @container (max-width: 600px) {
        .van { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 4px; padding: 5px; border-radius: 20px; }
        .van-item, .van-dd > summary { flex-direction: column; gap: 4px; height: 60px; padding: 0 4px; border-radius: 14px; font-size: 12px; white-space: normal; text-align: center; line-height: 1.15; }
        .van-item i, .van-dd > summary i { font-size: 15px; }
        .van-caret { display: none; }
        .van-badge { position: absolute; margin: -34px 0 0 30px; }
        .van-item { position: relative; }
        .van-dd { position: static; }
        .van-menu { left: 5px; right: 5px; min-width: 0; }
        .van-dd.is-end .van-menu { left: 5px; right: 5px; }
    }
    @media (prefers-reduced-motion: reduce) { .van-caret { transition: none; } }
</style>
<div class="van-box">
<nav class="van" aria-label="{{ __('Mon espace') }}" data-van>
    <a href="{{ route('visitor.dashboard') }}" class="van-item {{ request()->routeIs('visitor.dashboard') ? 'is-on' : '' }}" @if(request()->routeIs('visitor.dashboard')) aria-current="page" @endif>
        <i class="fas fa-gauge-high" aria-hidden="true"></i>{{ __('Tableau de bord') }}
    </a>

    <details class="van-dd {{ $vanBookingOn ? 'is-on' : '' }}" data-van-dd>
        <summary aria-label="{{ __('Mes réservations') }}{{ $vanBookingOn ? ' — '.$vanBookingOn['label'] : '' }}">
            <i class="fas {{ $vanBookingOn['icon'] ?? 'fa-suitcase-rolling' }}" aria-hidden="true"></i>{{ __('Mes réservations') }}<i class="fas fa-chevron-down van-caret" aria-hidden="true"></i>
        </summary>
        <div class="van-menu">
            @foreach($vanBookings as $item)
                <a href="{{ route($item['route']) }}" class="{{ $item['active'] ? 'is-on' : '' }}" @if($item['active']) aria-current="page" @endif><i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}</a>
            @endforeach
        </div>
    </details>

    <a href="{{ route('visitor.conversations.index') }}" class="van-item {{ request()->routeIs('visitor.conversations.*') ? 'is-on' : '' }}" @if(request()->routeIs('visitor.conversations.*')) aria-current="page" @endif>
        <i class="fas fa-message" aria-hidden="true"></i>{{ __('Messages') }}
        @if($accountNavUnreadMessages > 0)<span class="van-badge" aria-label="{{ __(':n non lus', ['n' => $accountNavUnreadMessages]) }}">{{ $accountNavUnreadMessages > 99 ? '99+' : $accountNavUnreadMessages }}</span>@endif
    </a>
    <a href="{{ route('visitor.receipts.index') }}" class="van-item {{ request()->routeIs('visitor.receipts.*') ? 'is-on' : '' }}" @if(request()->routeIs('visitor.receipts.*')) aria-current="page" @endif>
        <i class="fas fa-receipt" aria-hidden="true"></i>{{ __('Mes reçus') }}
    </a>
    <a href="{{ route('visitor.wallet.index') }}" class="van-item {{ request()->routeIs('visitor.wallet.*') ? 'is-on' : '' }}" @if(request()->routeIs('visitor.wallet.*')) aria-current="page" @endif>
        <i class="fas fa-wallet" aria-hidden="true"></i>{{ __('Portefeuille') }}
    </a>

    <details class="van-dd is-end {{ $vanAccountOn ? 'is-on' : '' }}" data-van-dd>
        <summary><i class="fas fa-user" aria-hidden="true"></i>{{ __('Mon compte') }}<i class="fas fa-chevron-down van-caret" aria-hidden="true"></i></summary>
        <div class="van-menu">
            @foreach($vanAccount as $item)
                <a href="{{ route($item['route'], $item['params'] ?? []) }}" class="{{ $item['active'] ? 'is-on' : '' }}" @if($item['active']) aria-current="page" @endif><i class="fas {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}</a>
            @endforeach
            <a href="{{ route('visitor.favorites.index') }}"><i class="fas fa-heart" aria-hidden="true"></i>{{ __('Mes favoris') }}</a>
            <a href="{{ route('visitor.notifications.index') }}"><i class="fas fa-bell" aria-hidden="true"></i>{{ __('Notifications') }}</a>
            <hr>
            <form method="POST" action="{{ route('logout') }}" style="margin:0">
                @csrf
                <button type="submit"><i class="fas fa-arrow-right-from-bracket" aria-hidden="true"></i>{{ __('Déconnexion') }}</button>
            </form>
        </div>
    </details>
</nav>
</div>
<script>
(function () {
    // Un seul menu ouvert à la fois ; fermeture au clic dehors et avec Échap.
    var dds = document.querySelectorAll('[data-van-dd]');
    dds.forEach(function (d) { d.addEventListener('toggle', function () { if (d.open) dds.forEach(function (o) { if (o !== d) o.open = false; }); }); });
    document.addEventListener('click', function (e) { dds.forEach(function (d) { if (d.open && !d.contains(e.target)) d.open = false; }); });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') dds.forEach(function (d) { if (d.open) { d.open = false; d.querySelector('summary').focus(); } }); });
})();
</script>
