{{--
    Barre de l'espace client (layouts.visitor-public) : remplace la barre orange, le menu du site et le
    Flash info, qui ne concernent pas un client connecté dans son espace. Logo (accueil), retour au site,
    notifications, initiales et déconnexion. Styles propres (.vb-*).
--}}
@php
    $vbUser = auth()->user();
    $vbUnread = $vbUser ? $vbUser->unreadNotifications()->count() : 0;
@endphp
<header class="vb">
    <div class="vb__in">
        {{-- Tablette / mobile : ouvre la barre latérale (partials.visitor-sidebar). --}}
        <button type="button" class="vb-menu" data-vs-open aria-controls="vs-sidebar" aria-expanded="false">
            <svg class="vb-svg" viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h10"/></svg>
            <span>{{ __('Menu') }}</span>
        </button>
        <a href="{{ route('home') }}" class="vb-brand" aria-label="{{ $siteBrand['site_name'] }} — {{ __('Accueil') }}">
            @if(!empty($siteBrand['logo_url']))
                <span class="vb-logo"><img src="{{ $siteBrand['logo_url'] }}" alt=""></span>
            @endif
            <span class="vb-brand__txt">
                <strong>{{ $siteBrand['site_name'] }}</strong>
                <small>{{ __('Espace client') }}</small>
            </span>
        </a>

        <nav class="vb-actions" aria-label="{{ __('Mon compte') }}">
            <a href="{{ route('home') }}" class="vb-link">
                <svg class="vb-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5"/><path d="m11 6-6 6 6 6"/></svg><span>{{ __('Retour au site') }}</span>
            </a>
            @if($vbUser)
                <a href="{{ route('visitor.notifications.index') }}" class="vb-icon" aria-label="{{ __('Notifications') }}{{ $vbUnread ? ' ('.$vbUnread.')' : '' }}">
                    <svg class="vb-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9a6 6 0 1 1 12 0c0 4.5 1.5 6.5 2.5 7.5h-17C4.5 15.5 6 13.5 6 9Z"/><path d="M10 19.5a2.2 2.2 0 0 0 4 0"/></svg>
                    @if($vbUnread)<span class="vb-badge">{{ $vbUnread > 9 ? '9+' : $vbUnread }}</span>@endif
                </a>
                <a href="{{ route('visitor.dashboard') }}" class="vb-user" title="{{ __('Mon espace') }}">
                    <span class="vb-avatar" aria-hidden="true">{{ $vbUser->initials }}</span>
                    <span class="vb-user__name">{{ $vbUser->first_name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="vb-out-form">
                    @csrf
                    <button type="submit" class="vb-out"><svg class="vb-svg" viewBox="0 0 24 24" aria-hidden="true" focusable="false" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M14 4h3a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-3"/><path d="M10 16l-4-4 4-4"/><path d="M6 12h10"/></svg><span>{{ __('Déconnexion') }}</span></button>
                </form>
            @endif
        </nav>
    </div>
</header>
<style>
    .vb { position: sticky; top: 0; z-index: 40; background: #ffffff; border-bottom: 1px solid #D8E9E0; }
    .vb__in { max-width: 72rem; margin: 0 auto; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
    .vb-brand { display: inline-flex; align-items: center; gap: 12px; min-width: 0; color: #0E3B2A; text-decoration: none; }
    .vb-logo { width: 44px; height: 44px; flex-shrink: 0; border-radius: 999px; border: 1px solid #D8E9E0; background: #ffffff; display: inline-flex; align-items: center; justify-content: center; overflow: hidden; }
    .vb-logo img { max-width: 100%; max-height: 100%; object-fit: contain; padding: 3px; }
    .vb-brand__txt { display: flex; flex-direction: column; min-width: 0; line-height: 1.15; }
    .vb-brand__txt strong { font-family: var(--font-serif, Georgia, serif); font-size: 17px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vb-brand__txt small { margin-top: 2px; font-size: 11px; font-weight: 600; letter-spacing: .12em; text-transform: uppercase; color: #00874F; }
    .vb-actions { display: flex; align-items: center; gap: 8px; flex-shrink: 0; }
    .vb-link, .vb-out { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border-radius: 999px; border: 1px solid #D8E9E0; background: #ffffff; color: #0E3B2A; font-size: 13.5px; font-weight: 600; text-decoration: none; cursor: pointer; transition: border-color .15s ease, color .15s ease, background .15s ease; }
    .vb-svg { width: 17px; height: 17px; flex: none; display: block; }
    .vb-link:hover, .vb-out:hover { border-color: #EB6E00; color: #EB6E00; }
    .vb-out-form { margin: 0; }
    .vb-menu { display: none; align-items: center; gap: 8px; height: 40px; padding: 0 14px 0 12px; border: 0; border-radius: 999px; background: #EB6E00; color: #ffffff; font: inherit; font-size: 13.5px; font-weight: 700; cursor: pointer; flex-shrink: 0; transition: background .15s ease; }
    .vb-menu:hover { background: #B85600; }
    @media (max-width: 1023px) {
        .vb-menu { display: inline-flex; }
        .vb__in { justify-content: flex-start; }
        .vb-brand { flex: 1 1 auto; overflow: hidden; }
        .vb-actions { margin-left: auto; }
        .vb-link span, .vb-user__name { display: none; }
        .vb-link { width: 40px; padding: 0; justify-content: center; }
        .vb-user { padding: 0 4px; }
    }
    .vb-icon { position: relative; width: 40px; height: 40px; border-radius: 999px; border: 1px solid #D8E9E0; display: inline-flex; align-items: center; justify-content: center; color: #0E3B2A; transition: border-color .15s ease, color .15s ease; }
    .vb-icon:hover { border-color: #EB6E00; color: #EB6E00; }
    .vb-badge { position: absolute; top: -4px; right: -4px; min-width: 18px; height: 18px; padding: 0 5px; border-radius: 999px; background: #EB6E00; color: #ffffff; font-size: 10.5px; font-weight: 700; line-height: 18px; text-align: center; border: 2px solid #ffffff; }
    .vb-user { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 12px 0 4px; border-radius: 999px; background: #F3F8F5; color: #0E3B2A; font-size: 13.5px; font-weight: 600; text-decoration: none; }
    .vb-user:hover { color: #EB6E00; }
    .vb-avatar { width: 32px; height: 32px; border-radius: 999px; background: #00874F; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; letter-spacing: .02em; }
    @media (max-width: 767px) {
        .vb__in { padding: 8px 16px; gap: 10px; }
        .vb-logo { width: 38px; height: 38px; }
        .vb-brand__txt strong { font-size: 15px; }
        .vb-link span, .vb-out span, .vb-user__name { display: none; }
        .vb-link, .vb-out { width: 40px; padding: 0; justify-content: center; }
        .vb-user { padding: 0 4px; }
    }
    /* Mobile : « Retour au site » et « Déconnexion » passent en bas du panneau latéral (.vs-foot). */
    @media (max-width: 767px) { .vb-link, .vb-out-form { display: none !important; } }
    @media (max-width: 420px) { .vb-menu span { display: none; } .vb-menu { width: 40px; padding: 0; justify-content: center; } }
    @media (max-width: 380px) { .vb-brand__txt small { display: none; } }
</style>
