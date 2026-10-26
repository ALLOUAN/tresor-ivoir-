@extends('layouts.visitor-public')

@section('title', __('Mon Espace'))

@section('content')
@php
    $vdUser = auth()->user();
    $vdHour = (int) now('Africa/Abidjan')->format('G');
    $vdHello = $vdHour < 5 || $vdHour >= 18 ? __('Bonsoir') : __('Bonjour');
    // Prochain séjour : la réservation à venir (ou en cours) la plus proche.
    $vdNext = ($my_reservations ?? collect())
        ->filter(fn ($r) => in_array($r->computed_status, [\App\Models\Reservation::COMPUTED_STATUS_UPCOMING, \App\Models\Reservation::COMPUTED_STATUS_ONGOING], true))
        ->sortBy(fn ($r) => $r->check_in)
        ->first();
    $vdNextImg = $vdNext?->accommodation?->cover_image ?? $vdNext?->accommodation?->thumbnail;
    $vdDays = $vdNext ? (int) now('Africa/Abidjan')->startOfDay()->diffInDays($vdNext->check_in->copy()->startOfDay(), false) : null;
    $vdStatusTone = [
        \App\Models\Reservation::COMPUTED_STATUS_UPCOMING => 'lagoon',
        \App\Models\Reservation::COMPUTED_STATUS_ONGOING => 'palm',
        \App\Models\Reservation::COMPUTED_STATUS_COMPLETED => 'sun',
        \App\Models\Reservation::COMPUTED_STATUS_CANCELLED => 'rose',
    ];
@endphp
<style>
    /* Espace client : bandeau d'accueil vert à motifs + prochain séjour, chiffres clés aux couleurs du logo,
       raccourcis, réservations, inspirations. Aplats ; ombre au survol seulement. */
    .vd { --sunset: 235,110,0; --palm: 0,135,79; --lagoon: 0,135,79; --sun: 235,110,0; --rose: 192,54,44; color: #0E3B2A; font-family: 'Montserrat', sans-serif; }
    .vd a { text-decoration: none; }
    .vd-alert { display: flex; align-items: center; gap: 10px; margin: 0 0 16px; padding: 12px 14px; border-radius: 14px; font-size: 14px; }
    .vd-alert.is-ok { background: #F3F8F5; border: 1px solid #D8E9E0; color: #00874F; }
    .vd-alert.is-info { background: #F3F8F5; border: 1px solid #D8E9E0; color: #B85600; }
    .vd-alert.is-err { background: #fdeceb; border: 1px solid #f5c2bd; color: #8a241c; }

    .vd-hero { position: relative; overflow: hidden; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 380px); gap: 24px; align-items: center; padding: 28px; border-radius: 28px; background: #00874F; color: #fff; }
    .vd-hero .ivm-g { opacity: .09 !important; }
    .vd-hero .ivm-g > * { stroke: #F7C396 !important; }
    .vd-hero .ivm-band { height: 6px; }
    .vd-hero > :not(.ivm):not(.ivm-band) { position: relative; z-index: 1; }
    .vd-me { display: flex; align-items: center; gap: 16px; }
    .vd-avatar { flex: none; width: 64px; height: 64px; border-radius: 20px; display: flex; align-items: center; justify-content: center; background: #EB6E00; color: #fff; font-size: 22px; font-weight: 800; letter-spacing: .02em; }
    .vd-hello { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #F7C396; }
    .vd-name { margin: 4px 0 0; font-family: 'Montserrat', serif; font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.1; color: #fff; }
    .vd-lead { margin: 12px 0 0; max-width: 46ch; font-size: 15px; line-height: 1.55; color: #D8E9E0; }
    .vd-hero-actions { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 18px; }
    .vd-chip { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 14px; border: 0; border-radius: 999px; background: rgba(255,255,255,.12); color: #fff; font: inherit; font-size: 13.5px; font-weight: 700; cursor: pointer; }
    .vd-chip:hover { background: rgba(255,255,255,.2); }
    .vd-chip .ico { width: 16px; height: 16px; color: #F7C396; }
    .vd-chip.is-on { background: rgba(0,135,79,.14); color: #F3F8F5; cursor: default; }

    .vd-next { display: block; overflow: hidden; border-radius: 22px; background: #fff; color: #0E3B2A; transition: box-shadow .2s ease, transform .2s ease; }
    .vd-next:hover { transform: translateY(-2px); box-shadow: 0 24px 40px -30px rgba(14,59,42,.6); }
    .vd-next__img { position: relative; height: 130px; background: #D8E9E0; }
    .vd-next__img img { width: 100%; height: 100%; object-fit: cover; }
    .vd-next__img .ico { position: absolute; inset: 0; margin: auto; width: 40px; height: 40px; color: #EB6E00; }
    .vd-next__count { position: absolute; left: 12px; top: 12px; display: inline-flex; align-items: center; height: 30px; padding: 0 12px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 12.5px; font-weight: 800; }
    .vd-next__body { padding: 14px 16px 16px; }
    .vd-next__body small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #00874F; }
    .vd-next__body b { display: block; margin-top: 4px; font-family: 'Montserrat', serif; font-size: 19px; line-height: 1.2; }
    .vd-next__body p { display: flex; align-items: center; gap: 6px; margin: 8px 0 0; font-size: 13.5px; color: #24503D; }
    .vd-next__body p .ico { width: 15px; height: 15px; color: #EB6E00; }
    .vd-next.is-empty { padding: 20px; }
    .vd-next.is-empty b { margin-top: 0; }
    .vd-next.is-empty p { display: block; margin: 6px 0 14px; font-size: 14px; line-height: 1.5; color: #24503D; }
    .vd-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; height: 44px; padding: 0 18px; border-radius: 999px; background: #EB6E00; color: #fff !important; font-size: 14px; font-weight: 800; }
    .vd-btn:hover { background: #B85600; }
    .vd-btn .ico { width: 16px; height: 16px; }

    .vd-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: 16px; }
    .vd-stat { --c: var(--sunset); display: flex; flex-direction: column; gap: 10px; padding: 16px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; color: #0E3B2A; transition: border-color .15s ease, box-shadow .2s ease; }
    .vd-stat:hover { border-color: rgba(var(--c), .5); box-shadow: 0 18px 30px -26px rgba(14,59,42,.5); }
    .vd-stat.is-palm { --c: var(--palm); } .vd-stat.is-lagoon { --c: var(--lagoon); } .vd-stat.is-sun { --c: var(--sun); }
    .vd-stat__ico { width: 40px; height: 40px; border-radius: 13px; display: flex; align-items: center; justify-content: center; background: rgba(var(--c), .12); color: rgb(var(--c)); }
    .vd-stat__ico .ico { width: 20px; height: 20px; }
    .vd-stat b { font-size: 26px; font-weight: 800; line-height: 1; font-variant-numeric: tabular-nums; }
    .vd-stat span { font-size: 13.5px; font-weight: 600; color: #24503D; }
    .vd-stat small { font-size: 12px; color: #557466; }

    .vd-quick { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-top: 12px; }
    .vd-quick a { display: flex; align-items: center; gap: 10px; padding: 12px 14px; border-radius: 16px; background: #FFFFFF; border: 1px solid #D8E9E0; color: #0E3B2A; font-size: 14px; font-weight: 700; }
    .vd-quick a:hover { border-color: #EB6E00; color: #B85600; background: #fff; }
    .vd-quick .ico { flex: none; width: 20px; height: 20px; color: #EB6E00; }

    .vd-sec { margin-top: 30px; }
    .vd-sec__head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 12px; }
    .vd-h2 { margin: 0; font-family: 'Montserrat', serif; font-size: 22px; font-weight: 700; color: #0E3B2A; }
    .vd-more { display: inline-flex; align-items: center; gap: 4px; font-size: 13.5px; font-weight: 700; color: #B85600; }
    .vd-more:hover { text-decoration: underline; text-underline-offset: 3px; }
    .vd-more .ico { width: 14px; height: 14px; }
    .vd-cols { display: grid; grid-template-columns: minmax(0, 1.7fr) minmax(0, 1fr); gap: 20px; }
    .vd-panel { border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; overflow: hidden; }

    .vd-row { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-top: 1px solid #F3F8F5; color: #0E3B2A; }
    .vd-row:first-child { border-top: 0; }
    a.vd-row:hover { background: #FFFFFF; }
    .vd-row__ico { flex: none; width: 44px; height: 44px; border-radius: 14px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #F3F8F5; color: #EB6E00; }
    .vd-row__ico img { width: 100%; height: 100%; object-fit: cover; }
    .vd-row__ico .ico { width: 20px; height: 20px; }
    .vd-row__txt { flex: 1; min-width: 0; }
    .vd-row__txt b { display: block; font-size: 14.5px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vd-row__txt small { display: block; margin-top: 3px; font-size: 12.5px; color: #557466; }
    .vd-pill { --c: var(--lagoon); flex: none; display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 999px; background: rgba(var(--c), .12); color: rgb(var(--c)); font-size: 12px; font-weight: 800; }
    .vd-pill.is-palm { --c: var(--palm); } .vd-pill.is-sun { --c: var(--sun); } .vd-pill.is-rose { --c: var(--rose); }
    .vd-go { flex: none; width: 34px; height: 34px; border-radius: 999px; display: flex; align-items: center; justify-content: center; background: #F3F8F5; color: #557466; }
    .vd-go .ico { width: 15px; height: 15px; }
    .vd-empty { padding: 28px 18px; text-align: center; font-size: 14px; color: #557466; }
    .vd-empty .ico { width: 34px; height: 34px; color: #8AA99B; }
    .vd-empty p { margin: 8px 0 14px; }

    .vd-buys { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
    .vd-buy { display: block; color: #0E3B2A; }
    .vd-buy span { display: block; aspect-ratio: 4 / 3; overflow: hidden; border-radius: 16px; background: #D8E9E0; border: 1px solid #D8E9E0; }
    .vd-buy img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s ease; }
    .vd-buy:hover img { transform: scale(1.04); }
    .vd-buy b { display: block; margin-top: 8px; font-size: 13.5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .vd-buy small { font-size: 12px; color: #557466; }

    .vd-arts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
    .vd-art { display: flex; flex-direction: column; overflow: hidden; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; color: #0E3B2A; transition: border-color .15s ease, box-shadow .2s ease; }
    .vd-art:hover { border-color: #8AA99B; box-shadow: 0 18px 30px -26px rgba(14,59,42,.5); }
    .vd-art__img { position: relative; aspect-ratio: 16 / 9; background: #D8E9E0; display: flex; align-items: center; justify-content: center; color: #EB6E00; }
    .vd-art__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .vd-art__img .ico { width: 32px; height: 32px; }
    .vd-art__body { padding: 12px 14px 14px; }
    .vd-art__body small { font-size: 11.5px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #EB6E00; }
    .vd-art__body b { display: -webkit-box; margin-top: 4px; font-size: 15px; line-height: 1.35; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .vd-art__body p { margin: 6px 0 0; font-size: 12.5px; color: #557466; }

    .vd-ev { display: flex; align-items: center; gap: 12px; padding: 12px 14px; border-top: 1px solid #F3F8F5; color: #0E3B2A; }
    .vd-ev:first-child { border-top: 0; }
    .vd-ev:hover { background: #FFFFFF; }
    .vd-date { flex: none; width: 52px; padding: 6px 0; border-radius: 14px; background: #00874F; color: #fff; text-align: center; }
    .vd-date small { display: block; font-size: 10.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #F7C396; }
    .vd-date b { display: block; font-size: 20px; line-height: 1.1; }
    .vd-ev__txt { min-width: 0; flex: 1; }
    .vd-ev__txt b { display: -webkit-box; font-size: 14px; line-height: 1.35; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .vd-ev__txt small { display: block; margin-top: 3px; font-size: 12.5px; color: #557466; }

    .vd-rev { padding: 14px 16px; border-top: 1px solid #F3F8F5; }
    .vd-rev:first-child { border-top: 0; }
    .vd-rev__top { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; }
    .vd-rev__top b { font-size: 14.5px; }
    .vd-stars { display: inline-flex; gap: 2px; color: #EB6E00; font-size: 13px; letter-spacing: 1px; }
    .vd-stars i { font-style: normal; color: #D8E9E0; }
    .vd-rev p { margin: 6px 0 0; font-size: 13.5px; line-height: 1.5; color: #24503D; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .vd-rev small { font-size: 12px; color: #557466; }
    .vd a:focus-visible, .vd button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

    @media (max-width: 900px) {
        .vd-hero { grid-template-columns: minmax(0, 1fr); padding: 22px; }
        .vd-cols { grid-template-columns: minmax(0, 1fr); }
        .vd-stats, .vd-quick { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 560px) {
        /* Mobile : carte d'accueil compacte (avatar, titre et boutons réduits, texte sur 2 lignes, encart serré). */
        .vd-hero { padding: 14px 14px 18px; border-radius: 20px; gap: 12px; }
        .vd-me { gap: 10px; }
        .vd-avatar { width: 40px; height: 40px; border-radius: 12px; font-size: 14px; }
        .vd-hello { font-size: 10.5px; }
        .vd-name { margin-top: 2px; font-size: 19px; }
        .vd-lead { font-size: 13px; line-height: 1.45; margin-top: 6px; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .vd-hero-actions { flex-wrap: nowrap; gap: 6px; margin-top: 10px; overflow-x: auto; scrollbar-width: none; }
        .vd-hero-actions::-webkit-scrollbar { display: none; }
        .vd-chip { flex: none; height: 32px; padding: 0 11px; font-size: 12px; gap: 6px; }
        .vd-chip .ico { width: 14px; height: 14px; }
        .vd-next { border-radius: 16px; }
        .vd-next.is-empty { padding: 12px 14px; }
        .vd-next.is-empty b { font-size: 15px; }
        .vd-next.is-empty p { margin: 4px 0 10px; font-size: 12.5px; line-height: 1.4; }
        .vd-next.is-empty .vd-btn { height: 36px; padding: 0 14px; font-size: 12.5px; }
        .vd-next__img { height: 96px; }
        .vd-next__body { padding: 10px 12px 12px; }
        .vd-next__body b { font-size: 16px; }
        .vd-stat { padding: 12px; gap: 8px; border-radius: 18px; }
        .vd-stat b { font-size: 22px; }
        .vd-quick a { padding: 10px 12px; font-size: 13px; }
        .vd-arts { grid-template-columns: minmax(0, 1fr); }
        .vd-buys { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .vd-h2 { font-size: 20px; }
    }
    @media (prefers-reduced-motion: reduce) { .vd-next, .vd-buy img { transition: none; } }
</style>

<div class="vd">
@include('partials.visitor-account-nav')

@if(session('newsletter_success'))<div class="vd-alert is-ok" role="status"><x-icon name="check-circle" />{{ session('newsletter_success') }}</div>@endif
@if(session('newsletter_info'))<div class="vd-alert is-info" role="status"><x-icon name="info-circle" />{{ session('newsletter_info') }}</div>@endif
@if(session('newsletter_error'))<div class="vd-alert is-err" role="alert"><x-icon name="danger-circle" />{{ session('newsletter_error') }}</div>@endif
@if($errors->has('newsletter_email'))<div class="vd-alert is-err" role="alert"><x-icon name="danger-circle" />{{ $errors->first('newsletter_email') }}</div>@endif

{{-- ── Accueil + prochain séjour ── --}}
<section class="vd-hero" aria-labelledby="vd-name">
    @include('partials.ivorian-motifs', ['culture' => 'national'])
    <div>
        <div class="vd-me">
            <span class="vd-avatar" aria-hidden="true">{{ $vdUser->initials }}</span>
            <div>
                <p class="vd-hello">{{ $vdHello }}</p>
                <h1 class="vd-name" id="vd-name">{{ $vdUser->first_name }}</h1>
            </div>
        </div>
        <p class="vd-lead">{{ __('Votre espace voyage : réservations, reçus, favoris et messages, réunis au même endroit.') }}</p>
        <div class="vd-hero-actions">
            @if($newsletter)
                <span class="vd-chip is-on"><x-icon name="check-circle" />{{ __('Abonné à la newsletter') }}</span>
            @else
                <form method="post" action="{{ route('newsletter.subscribe') }}">
                    @csrf
                    <input type="hidden" name="newsletter_email" value="{{ old('newsletter_email', $vdUser->email) }}">
                    <input type="hidden" name="redirect_to" value="dashboard">
                    <button type="submit" class="vd-chip"><x-icon name="star-shine" />{{ __('S’abonner à la newsletter') }}</button>
                </form>
            @endif
            <a href="{{ route('visitor.profile.edit') }}" class="vd-chip"><x-icon name="user-rounded" />{{ __('Mon profil') }}</a>
        </div>
    </div>

    @if($vdNext)
        <a href="{{ route('visitor.reservations.show', $vdNext) }}" class="vd-next">
            <span class="vd-next__img">
                @if($vdNextImg)<img src="{{ $vdNextImg }}" alt="" loading="lazy" decoding="async">@else<x-icon name="bed" />@endif
                <span class="vd-next__count">
                    @if($vdNext->computed_status === \App\Models\Reservation::COMPUTED_STATUS_ONGOING)
                        {{ __('En cours') }}
                    @elseif($vdDays === 0)
                        {{ __('Aujourd\'hui') }}
                    @elseif($vdDays === 1)
                        {{ __('Demain') }}
                    @else
                        {{ __('Dans :n jours', ['n' => $vdDays]) }}
                    @endif
                </span>
            </span>
            <span class="vd-next__body">
                <small>{{ __('Votre prochain séjour') }}</small>
                <b>{{ $vdNext->accommodation_name }}</b>
                <p><x-icon name="calendar" />{{ $vdNext->check_in->translatedFormat('d M') }} → {{ $vdNext->check_out->translatedFormat('d M Y') }}</p>
            </span>
        </a>
    @else
        <div class="vd-next is-empty">
            <b>{{ __('Où partez-vous ensuite ?') }}</b>
            <p>{{ __('Hôtels, résidences et expériences dans tout le pays, à réserver en quelques clics.') }}</p>
            <a href="{{ route('accommodations.index') }}" class="vd-btn"><x-icon name="bed" />{{ __('Trouver un hébergement') }}</a>
        </div>
    @endif
</section>

{{-- ── Chiffres clés ── --}}
<div class="vd-stats">
    <a href="{{ route('visitor.reservations.index') }}" class="vd-stat">
        <span class="vd-stat__ico"><x-icon name="bed" /></span>
        <b>{{ number_format($reservation_stats['total'] ?? 0) }}</b>
        <span>{{ __('Réservations') }}</span>
        <small>{{ trans_choice(':count à venir|:count à venir', $reservation_stats['upcoming'] ?? 0, ['count' => $reservation_stats['upcoming'] ?? 0]) }}</small>
    </a>
    <a href="{{ route('visitor.favorites.index') }}" class="vd-stat is-palm">
        <span class="vd-stat__ico"><x-icon name="heart" /></span>
        <b>{{ number_format($favorites_count ?? 0) }}</b>
        <span>{{ __('Favoris') }}</span>
        <small>{{ __('Vos adresses enregistrées') }}</small>
    </a>
    <a href="{{ route('visitor.notifications.index') }}" class="vd-stat is-lagoon">
        <span class="vd-stat__ico"><x-icon name="alarm" /></span>
        <b>{{ number_format($unread_notifications ?? 0) }}</b>
        <span>{{ __('Notifications') }}</span>
        <small>{{ __('non lues') }}</small>
    </a>
    <a href="{{ route('visitor.purchases.index') }}" class="vd-stat is-sun">
        <span class="vd-stat__ico"><x-icon name="gallery" /></span>
        <b>{{ number_format($purchases_count ?? 0) }}</b>
        <span>{{ __('Achats') }}</span>
        <small>{{ __('Photos achetées') }}</small>
    </a>
</div>

{{-- ── Raccourcis ── --}}
<nav class="vd-quick" aria-label="{{ __('Raccourcis') }}">
    <a href="{{ route('accommodations.index') }}"><x-icon name="buildings" />{{ __('Réserver un séjour') }}</a>
    <a href="{{ route('tourist.cities') }}"><x-icon name="map-point" />{{ __('Explorer les régions') }}</a>
    <a href="{{ route('visitor.conversations.index') }}"><x-icon name="chat-round-dots" />{{ __('Mes messages') }}</a>
    <a href="{{ route('visitor.wallet.index') }}"><x-icon name="wallet-money" />{{ __('Mon portefeuille') }}</a>
</nav>

{{-- ── Réservations + reçus ── --}}
<div class="vd-sec vd-cols">
    <section aria-labelledby="vd-res">
        <div class="vd-sec__head">
            <h2 class="vd-h2" id="vd-res">{{ __('Mes réservations') }}</h2>
            @if(($my_reservations ?? collect())->isNotEmpty())<a href="{{ route('visitor.reservations.index') }}" class="vd-more">{{ __('Tout voir') }}<x-icon name="alt-arrow-right" /></a>@endif
        </div>
        <div class="vd-panel">
            @forelse($my_reservations ?? [] as $reservation)
                @php $rImg = $reservation->accommodation?->cover_image ?? $reservation->accommodation?->thumbnail; @endphp
                <a href="{{ route('visitor.reservations.show', $reservation) }}" class="vd-row">
                    <span class="vd-row__ico">@if($rImg)<img src="{{ $rImg }}" alt="" loading="lazy" decoding="async">@else<x-icon name="bed" />@endif</span>
                    <span class="vd-row__txt">
                        <b>{{ $reservation->accommodation_name }}</b>
                        <small>{{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }} · {{ $reservation->reference }}</small>
                    </span>
                    <span class="vd-pill is-{{ $vdStatusTone[$reservation->computed_status] ?? 'lagoon' }}">{{ $reservation->labelForComputedStatus() }}</span>
                </a>
            @empty
                <div class="vd-empty">
                    <x-icon name="suitcase" />
                    <p>{{ __('Aucune réservation pour le moment.') }}</p>
                    <a href="{{ route('accommodations.index') }}" class="vd-btn"><x-icon name="bed" />{{ __('Trouver un hébergement') }}</a>
                </div>
            @endforelse
        </div>
    </section>

    <section aria-labelledby="vd-rec">
        <div class="vd-sec__head">
            <h2 class="vd-h2" id="vd-rec">{{ __('Derniers reçus') }}</h2>
            @if(($latest_receipts ?? collect())->isNotEmpty())<a href="{{ route('visitor.receipts.index') }}" class="vd-more">{{ __('Tout voir') }}<x-icon name="alt-arrow-right" /></a>@endif
        </div>
        <div class="vd-panel">
            @forelse($latest_receipts ?? [] as $receipt)
                <a href="{{ $receipt->receiptUrl() }}" class="vd-row">
                    <span class="vd-row__ico"><x-icon name="clipboard-list" /></span>
                    <span class="vd-row__txt"><b>{{ $receipt->accommodation_name }}</b><small>{{ $receipt->reference }}</small></span>
                    <span class="vd-go"><x-icon name="alt-arrow-right" /></span>
                </a>
            @empty
                <div class="vd-empty"><x-icon name="clipboard-list" /><p style="margin-bottom:0">{{ __('Aucun reçu pour le moment.') }}</p></div>
            @endforelse
        </div>
    </section>
</div>

{{-- ── Achats récents ── --}}
@if(($purchases_count ?? 0) > 0 && ($recent_purchases ?? collect())->isNotEmpty())
<section class="vd-sec" aria-labelledby="vd-buys">
    <div class="vd-sec__head">
        <h2 class="vd-h2" id="vd-buys">{{ __('Mes achats récents') }}</h2>
        <a href="{{ route('visitor.purchases.index') }}" class="vd-more">{{ __('Tout voir') }}<x-icon name="alt-arrow-right" /></a>
    </div>
    <div class="vd-buys">
        @foreach($recent_purchases as $purchase)
            @php $media = $purchase->media; @endphp
            @if($media)
                <a href="{{ route('gallery.public.show', $media->uuid) }}" class="vd-buy">
                    <span>@if($media->url)<img src="{{ $media->url }}" alt="{{ $media->alt_text ?? $media->title }}" loading="lazy" decoding="async">@endif</span>
                    <b>{{ $media->title ?? $media->original_name }}</b>
                    <small>{{ $purchase->paid_at?->format('d/m/Y') }} · {{ number_format((float) $purchase->amount, 0, ',', ' ') }} {{ __('FCFA') }}</small>
                </a>
            @endif
        @endforeach
    </div>
</section>
@endif

{{-- ── Inspirations : articles + événements ── --}}
<div class="vd-sec vd-cols">
    <section aria-labelledby="vd-arts">
        <div class="vd-sec__head">
            <h2 class="vd-h2" id="vd-arts">{{ __('À lire pour votre prochain voyage') }}</h2>
            <a href="{{ route('articles.index') }}" class="vd-more">{{ __('Magazine') }}<x-icon name="alt-arrow-right" /></a>
        </div>
        @if($featured_articles->isEmpty())
            <div class="vd-panel vd-empty"><x-icon name="book" /><p style="margin-bottom:0">{{ __('Aucun article à la une pour le moment.') }}</p></div>
        @else
            <div class="vd-arts">
                @foreach($featured_articles as $article)
                    @php $aTitle = $article->localized('title') ?: $article->title_fr; @endphp
                    <a href="{{ route('articles.show', $article->slug_fr) }}" class="vd-art">
                        <span class="vd-art__img">
                            <x-icon name="book" />
                            @if($article->cover_url)<img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?: '' }}" loading="lazy" decoding="async" onerror="this.remove()">@endif
                        </span>
                        <span class="vd-art__body">
                            <small>{{ $article->category?->localized('name') ?: __('Magazine') }}</small>
                            <b>{{ $aTitle }}</b>
                            <p>{{ $article->published_at?->translatedFormat('d M Y') }}@if($article->reading_time) · {{ __(':n min', ['n' => $article->reading_time]) }}@endif</p>
                        </span>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    <section aria-labelledby="vd-evs">
        <div class="vd-sec__head">
            <h2 class="vd-h2" id="vd-evs">{{ __('Événements à venir') }}</h2>
            <a href="{{ route('events.index') }}" class="vd-more">{{ __('Agenda') }}<x-icon name="alt-arrow-right" /></a>
        </div>
        <div class="vd-panel">
            @forelse($upcoming_events as $event)
                <a href="{{ route('events.show', $event->slug) }}" class="vd-ev">
                    <span class="vd-date"><small>{{ $event->starts_at->translatedFormat('M') }}</small><b>{{ $event->starts_at->format('d') }}</b></span>
                    <span class="vd-ev__txt">
                        <b>{{ $event->localized('title') ?: $event->title_fr }}</b>
                        <small>{{ $event->city }}@if($event->category?->localized('name')) · {{ $event->category->localized('name') }}@endif</small>
                    </span>
                </a>
            @empty
                <div class="vd-empty"><x-icon name="calendar" /><p style="margin-bottom:0">{{ __('Aucun événement à venir.') }}</p></div>
            @endforelse
        </div>
    </section>
</div>

{{-- ── Mes avis ── --}}
<section class="vd-sec" aria-labelledby="vd-revs">
    <div class="vd-sec__head"><h2 class="vd-h2" id="vd-revs">{{ __('Mes derniers avis') }}</h2></div>
    <div class="vd-panel">
        @forelse($my_reviews as $review)
            @php
                $rstatus = ['approved' => ['palm', __('Publié')], 'pending' => ['sun', __('En attente')], 'rejected' => ['rose', __('Refusé')]];
                [$rTone, $rLabel] = $rstatus[$review->status] ?? ['lagoon', $review->status];
            @endphp
            <div class="vd-rev">
                <div class="vd-rev__top">
                    <b>{{ $review->provider->name ?? __('Établissement') }}</b>
                    <span class="vd-stars" aria-label="{{ __(':n sur 5', ['n' => $review->rating]) }}">{{ str_repeat('★', (int) $review->rating) }}<i>{{ str_repeat('★', 5 - (int) $review->rating) }}</i></span>
                    <span class="vd-pill is-{{ $rTone }}">{{ $rLabel }}</span>
                    <small>{{ $review->created_at->diffForHumans() }}</small>
                </div>
                @if($review->comment)<p>{{ $review->comment }}</p>@endif
            </div>
        @empty
            <div class="vd-empty">
                <x-icon name="star" />
                <p>{{ __('Vous n\'avez pas encore rédigé d\'avis.') }}</p>
                <a href="{{ route('providers.index') }}" class="vd-btn"><x-icon name="compass" />{{ __('Explorer les prestataires') }}</a>
            </div>
        @endforelse
    </div>
</section>
</div>
@endsection
