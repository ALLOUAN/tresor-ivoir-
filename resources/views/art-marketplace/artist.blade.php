<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $profile->display_name }} — {{ __('Artiste') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($profile->tagline ?: strip_tags((string) $profile->bio), 155) }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; } .font-serif { font-family: 'Playfair Display', serif; } html { scroll-behavior: smooth; } @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }</style>
    @include('partials.offer-page-theme')
    <style>
        /* Page artiste façon portfolio, charte du site : crème, vert forêt, orange, Playfair. Aplats, ombre au survol seulement. */
        .ap { max-width: 1180px; margin: 0 auto; padding: 22px 20px 64px; }
        .ap-back { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #9a4308; font-size: 13px; font-weight: 700; text-decoration: none; }
        .ap-back:hover { border-color: #b9500a; }
        .ap-flash { margin-top: 14px; padding: 12px 16px; border-radius: 14px; background: #e7f3ec; border: 1px solid #c4e2cf; color: #1b6b3a; font-size: 14px; }

        .ap-hero { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr); gap: 20px; margin: 20px 0 24px; }
        .ap-id { display: flex; flex-direction: column; justify-content: space-between; gap: 22px; padding: 30px; border-radius: 28px; background: #fff; border: 1px solid #e6dccb; }
        .ap-id__top { display: flex; align-items: center; gap: 20px; }
        .ap-avatar { flex: none; width: 112px; height: 112px; border-radius: 999px; overflow: hidden; background: #1b6b3a; color: #fff; display: flex; align-items: center; justify-content: center; font-family: 'Playfair Display', serif; font-size: 40px; font-weight: 700; letter-spacing: .02em; border: 4px solid #fdf4ec; }
        .ap-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .ap-kicker { margin: 0; display: inline-flex; flex-wrap: wrap; align-items: center; gap: 6px 10px; font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #b9500a; }
        .ap-kicker .ico { font-size: 15px; }
        .ap-name { margin: 8px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(32px, 2.6vw + 18px, 54px); font-weight: 700; line-height: 1.04; letter-spacing: -.015em; color: #1d1a16; }
        .ap-tagline { margin: 10px 0 0; font-family: 'Playfair Display', serif; font-style: italic; font-size: 19px; color: #4a443a; }
        .ap-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); margin: 0; padding: 16px 0 0; list-style: none; border-top: 1px solid #f0eadf; }
        .ap-stats li { padding: 0 14px; border-left: 1px solid #f0eadf; }
        .ap-stats li:first-child { padding-left: 0; border-left: 0; }
        .ap-stats b { display: block; font-family: 'Playfair Display', serif; font-size: 28px; line-height: 1.1; color: #1d1a16; }
        .ap-stats small { display: block; margin-top: 2px; font-size: 12.5px; color: #6b6458; }
        .ap-actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .ap-actions form { display: contents; }
        .ap-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease; white-space: nowrap; }
        .ap-btn .ico { font-size: 18px; }
        .ap-btn.is-main { background: #b9500a; color: #fff; }
        .ap-btn.is-main:hover { background: #9a4308; color: #fff; }
        .ap-btn.is-line { background: #fff; color: #9a4308; border: 1.5px solid #b9500a; }
        .ap-btn.is-line:hover { background: #fdf4ec; }
        .ap-btn.is-on { background: #e7f3ec; color: #1b6b3a; border: 1.5px solid #c4e2cf; }
        .ap-btn.is-icon { width: 50px; padding: 0; background: #fff; color: #9a4308; border: 1.5px solid #e6dccb; }
        .ap-btn.is-icon:hover { border-color: #b9500a; }
        .ap-btn:focus-visible, .ap-work:focus-visible, .ap-chip:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        .ap-feature { position: relative; display: block; min-height: 100%; border-radius: 28px; overflow: hidden; background: #e9e1d2; color: #fff; text-decoration: none; }
        .ap-feature img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .ap-feature:hover img { transform: scale(1.03); }
        .ap-feature__cap { position: absolute; left: 14px; right: 14px; bottom: 14px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-radius: 18px; background: #fff; color: #1d1a16; }
        .ap-feature__cap small { display: block; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #b9500a; }
        .ap-feature__cap b { display: block; margin-top: 2px; font-family: 'Playfair Display', serif; font-size: 19px; line-height: 1.2; }
        .ap-feature__cap span { display: block; font-size: 13px; color: #6b6458; }
        .ap-feature__go { flex: none; width: 42px; height: 42px; border-radius: 999px; background: #b9500a; color: #fff; display: flex; align-items: center; justify-content: center; }
        .ap-feature.is-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 10px; min-height: 320px; background: #1b6b3a; color: #fff; text-align: center; padding: 24px; }
        .ap-feature.is-empty .ico { font-size: 44px; color: #f6c38f; }
        .ap-feature.is-empty p { margin: 0; font-family: 'Playfair Display', serif; font-size: 22px; }

        .ap-sec { scroll-margin-top: 150px; }
        .ap-label { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .ap-h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.2vw + 18px, 32px); font-weight: 700; color: #1d1a16; }
        .ap-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .ap-main { display: flex; flex-direction: column; gap: 52px; margin-top: 30px; }

        .ap-chips { display: flex; gap: 6px; padding: 4px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; }
        .ap-chip { height: 36px; padding: 0 14px; border-radius: 999px; border: 0; background: transparent; color: #4a443a; font-size: 13px; font-weight: 700; cursor: pointer; }
        .ap-chip.is-on { background: #1b6b3a; color: #fff; }
        .ap-works { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; }
        .ap-work { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .ap-work:hover { border-color: #d8cdb8; box-shadow: 0 18px 34px -26px rgba(29,26,22,.5); }
        .ap-work[hidden] { display: none; }
        .ap-work__img { position: relative; aspect-ratio: 4 / 5; background: #f3eee4; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #b9500a; font-size: 34px; }
        .ap-work__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .ap-work:hover .ap-work__img img { transform: scale(1.04); }
        .ap-work__tag { position: absolute; top: 10px; left: 10px; padding: 5px 10px; border-radius: 999px; background: #1d1a16; color: #fff; font-size: 11px; font-weight: 800; }
        .ap-work__tag.is-new { background: #fff; color: #9a4308; }
        .ap-work__body { padding: 14px 16px 16px; display: flex; flex-direction: column; gap: 4px; flex: 1; }
        .ap-work__body b { font-family: 'Playfair Display', serif; font-size: 18px; line-height: 1.25; color: #1d1a16; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ap-work__body small { font-size: 12.5px; color: #6b6458; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
        .ap-work__price { margin-top: auto; padding-top: 8px; display: flex; align-items: center; justify-content: space-between; font-size: 15px; font-weight: 800; color: #1d1a16; }
        .ap-work__price em { font-style: normal; font-size: 12.5px; font-weight: 600; color: #6b6458; }
        .ap-work__price .ico { color: #b9500a; }
        .ap-empty { padding: 28px; border-radius: 22px; background: #fff; border: 1px dashed #d8cdb8; text-align: center; color: #6b6458; font-size: 14px; }

        .ap-about { display: grid; grid-template-columns: minmax(0, 1.2fr) minmax(0, 1fr); gap: 28px; align-items: start; }
        .ap-bio { margin: 14px 0 0; font-size: 16px; line-height: 1.75; color: #3a352c; white-space: pre-line; }
        .ap-quote { position: relative; margin: 0; padding: 28px 28px 26px; border-radius: 24px; background: #1b6b3a; color: #fff; }
        .ap-quote::before { content: '“'; position: absolute; top: -6px; left: 20px; font-family: 'Playfair Display', serif; font-size: 90px; line-height: 1; color: #f6c38f; }
        .ap-quote p { margin: 22px 0 0; font-family: 'Playfair Display', serif; font-size: 20px; line-height: 1.5; white-space: pre-line; }
        .ap-quote footer { margin-top: 14px; font-size: 13px; font-weight: 700; color: #cfe6d7; }
        .ap-studio { margin-top: 16px; width: 100%; aspect-ratio: 4 / 3; object-fit: cover; border-radius: 22px; }

        .ap-path { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 20px; }
        .ap-box { padding: 22px 24px; border-radius: 24px; background: #fff; border: 1px solid #ece5d6; }
        .ap-box h3 { margin: 0 0 16px; display: flex; align-items: center; gap: 10px; font-size: 15px; font-weight: 800; color: #1d1a16; }
        .ap-box h3 .ico { width: 36px; height: 36px; border-radius: 12px; background: #fdf4ec; color: #b9500a; justify-content: center; font-size: 19px; }
        .ap-tl { margin: 0; padding: 0; list-style: none; position: relative; }
        .ap-tl::before { content: ''; position: absolute; left: 27px; top: 8px; bottom: 8px; width: 2px; background: #f0eadf; }
        .ap-tl li { position: relative; display: grid; grid-template-columns: 56px minmax(0, 1fr); gap: 14px; align-items: start; padding: 6px 0; }
        .ap-tl time { position: relative; z-index: 1; padding: 4px 0; border-radius: 999px; background: #b9500a; color: #fff; text-align: center; font-size: 12.5px; font-weight: 800; }
        .ap-tl time.is-n { background: #fdf4ec; color: #9a4308; }
        .ap-tl span { padding-top: 3px; font-size: 14.5px; line-height: 1.5; color: #3a352c; }
        .ap-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .ap-list li { display: flex; align-items: flex-start; gap: 10px; font-size: 14.5px; line-height: 1.5; color: #3a352c; }
        .ap-list .ico { flex: none; color: #b9500a; font-size: 18px; margin-top: 1px; }

        .ap-commission { display: grid; grid-template-columns: minmax(0, 1.3fr) minmax(0, 1fr); gap: 0; border-radius: 28px; overflow: hidden; background: #fff; border: 1px solid #e6dccb; scroll-margin-top: 150px; }
        .ap-commission__txt { padding: 30px; }
        .ap-commission__txt p.ap-lead { margin: 10px 0 0; font-size: 15.5px; line-height: 1.65; color: #4a443a; }
        .ap-steps { margin: 20px 0 0; padding: 0; list-style: none; display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; }
        .ap-steps li { padding: 14px; border-radius: 18px; background: #f6f3ed; font-size: 13.5px; line-height: 1.45; color: #3a352c; }
        .ap-steps li b { display: flex; width: 28px; height: 28px; margin-bottom: 8px; border-radius: 999px; background: #1b6b3a; color: #fff; align-items: center; justify-content: center; font-size: 13px; }
        .ap-commission__side { padding: 30px; background: #fdf4ec; display: flex; flex-direction: column; justify-content: center; gap: 12px; }
        .ap-commission__side small { font-size: 12px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #9a4308; }
        .ap-commission__side strong { font-family: 'Playfair Display', serif; font-size: 34px; line-height: 1.1; color: #1d1a16; }
        .ap-commission__side .ap-btn { width: 100%; margin-top: 6px; }
        .ap-commission__side ul { margin: 4px 0 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 8px; font-size: 13.5px; color: #4a443a; }
        .ap-commission__side li { display: flex; align-items: center; gap: 8px; }
        .ap-commission__side li .ico { color: #1b6b3a; }

        @media (max-width: 1023px) {
            .ap-hero { grid-template-columns: 1fr; }
            .ap-feature { min-height: 0; aspect-ratio: 16 / 10; }
            .ap-works { grid-template-columns: repeat(3, minmax(0, 1fr)); }
            .ap-about, .ap-path, .ap-commission { grid-template-columns: 1fr; }
        }
        @media (max-width: 767px) {
            .ap { padding: 16px 16px 120px; }
            .ap-id { padding: 20px; border-radius: 22px; }
            .ap-id__top { flex-direction: column; align-items: flex-start; gap: 14px; }
            .ap-avatar { width: 84px; height: 84px; font-size: 30px; }
            .ap-tagline { font-size: 17px; }
            .ap-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); row-gap: 14px; }
            .ap-stats li:nth-child(3) { padding-left: 0; border-left: 0; }
            .ap-stats b { font-size: 24px; }
            .ap-actions .ap-btn { flex: 1 1 auto; }
            .ap-actions .ap-btn.is-icon { flex: none; }
            .ap-feature { aspect-ratio: 4 / 5; border-radius: 22px; }
            .ap-works { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
            .ap-work__body { padding: 10px 12px 12px; }
            .ap-work__body b { font-size: 15.5px; }
            .ap-work__price { font-size: 13.5px; }
            .ap-commission__txt, .ap-commission__side { padding: 22px; }
            .ap-steps { grid-template-columns: 1fr; }
            .ap-quote p { font-size: 18px; }
        }
        .ox-bar a { white-space: nowrap; }
        @media (prefers-reduced-motion: reduce) { .ap-feature img, .ap-work__img img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="dx text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $fmt = fn (int $x) => number_format($x, 0, ',', ' ').' FCFA';
    $expos = \App\Models\ArtistProfile::lines($profile->exhibitions);
    $edu = \App\Models\ArtistProfile::lines($profile->education);
    $available = $works->where('status', \App\Models\Artwork::STATUS_PUBLISHED);
    $sold = $works->where('status', \App\Models\Artwork::STATUS_SOLD);
    $featured = $available->first() ?? $works->first();
    $initials = collect(preg_split('/\s+/u', trim((string) $profile->display_name)))
        ->reject(fn ($w) => in_array(mb_strtolower($w), ['atelier', 'studio', 'galerie', 'de', 'du', 'la', 'le', 'les'], true))
        ->take(2)->map(fn ($w) => mb_strtoupper(mb_substr($w, 0, 1)))->implode('') ?: mb_strtoupper(mb_substr((string) $profile->display_name, 0, 1));
    // « 2025 — Abidjan Art Week » → [année, texte] pour la frise.
    $exposTl = collect($expos)->map(fn ($e) => preg_match('/^\s*(\d{4})\s*[—–\-:]\s*(.+)$/u', $e, $m) ? [$m[1], $m[2]] : [null, $e]);
    $shareUrl = url()->current();
@endphp
<main class="ap">
    <a href="{{ route('art.index') }}" class="ap-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Art & Créations') }}</a>
    @if(session('success'))<p class="ap-flash" role="status">{{ session('success') }}</p>@endif

    {{-- ══ EN-TÊTE : identité + œuvre à la une ══ --}}
    <header class="ap-hero">
        <div class="ap-id">
            <div class="ap-id__top">
                <div class="ap-avatar">@if($profile->photo)<img src="{{ $profile->photo }}" alt="{{ $profile->display_name }}">@else<span aria-hidden="true">{{ $initials }}</span>@endif</div>
                <div class="min-w-0">
                    <p class="ap-kicker"><x-icon name="palette" />{{ __('Artiste') }}@if($profile->city)<span>· {{ $profile->city }}</span>@endif @if($profile->birth_year)<span>· {{ __('né(e) en :y', ['y' => $profile->birth_year]) }}</span>@endif</p>
                    <h1 class="ap-name">{{ $profile->display_name }}</h1>
                    @if($profile->tagline)<p class="ap-tagline">{{ $profile->tagline }}</p>@endif
                </div>
            </div>
            <ul class="ap-stats">
                <li><b>{{ $available->count() }}</b><small>{{ trans_choice('œuvre disponible|œuvres disponibles', $available->count()) }}</small></li>
                <li><b>{{ $sold->count() }}</b><small>{{ trans_choice('vendue|vendues', $sold->count()) }}</small></li>
                <li><b>{{ count($expos) }}</b><small>{{ trans_choice('exposition|expositions', count($expos)) }}</small></li>
                <li><b>{{ $followers }}</b><small>{{ trans_choice('abonné|abonnés', $followers) }}</small></li>
            </ul>
            <div class="ap-actions">
                @auth
                    <form method="POST" action="{{ route('artist.follow', $profile->slug) }}">@csrf
                        <button class="ap-btn {{ $following ? 'is-on' : 'is-main' }}"><x-icon name="{{ $following ? 'check-circle' : 'alarm' }}" />{{ $following ? __('Abonné') : __('Suivre l\'artiste') }}</button>
                    </form>
                @else
                    <a href="{{ route('login', ['redirect' => request()->getRequestUri()]) }}" class="ap-btn is-main"><x-icon name="alarm" />{{ __('Suivre l\'artiste') }}</a>
                @endauth
                @if($profile->accepts_commissions)
                    <a href="{{ route('artist.commission', $profile->slug) }}" class="ap-btn is-line"><x-icon name="palette" />{{ __('Commande sur mesure') }}</a>
                @endif
                <button type="button" class="ap-btn is-icon" data-ap-share title="{{ __('Partager') }}" aria-label="{{ __('Partager') }}"><x-icon name="share" /></button>
            </div>
        </div>

        @if($featured)
            <a href="{{ route('art.show', $featured->slug) }}" class="ap-feature">
                @if($featured->images[0] ?? null)<img src="{{ $featured->images[0] }}" alt="{{ $featured->title }}" decoding="async">@endif
                <span class="ap-feature__cap">
                    <span class="min-w-0">
                        <small>{{ __('Œuvre à la une') }}</small>
                        <b>{{ $featured->title }}</b>
                        <span>{{ collect([$featured->medium, $featured->dimensions, $featured->year_created])->filter()->implode(' · ') }}</span>
                    </span>
                    <span class="ap-feature__go" aria-hidden="true"><x-icon name="alt-arrow-right" /></span>
                </span>
            </a>
        @else
            <div class="ap-feature is-empty"><x-icon name="palette" /><p>{{ __('Nouvelles œuvres bientôt') }}</p></div>
        @endif
    </header>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        <a href="#oeuvres">{{ __('Œuvres') }}</a>
        @if($profile->bio || $profile->statement)<a href="#a-propos">{{ __('À propos') }}</a>@endif
        @if($expos || $edu)<a href="#parcours">{{ __('Parcours') }}</a>@endif
        @if($profile->accepts_commissions)<a href="#sur-mesure">{{ __('Sur mesure') }}</a>@endif
    </nav>

    <div class="ap-main">
        {{-- ══ ŒUVRES ══ --}}
        <section class="ox-sec ap-sec" id="oeuvres">
            <div class="ap-head">
                <div>
                    <p class="ap-label">{{ __('Œuvres') }}</p>
                    <h2 class="ap-h2">{{ __('Œuvres de :a', ['a' => $profile->display_name]) }}</h2>
                </div>
                @if($available->isNotEmpty() && $sold->isNotEmpty())
                    <div class="ap-chips" role="group" aria-label="{{ __('Filtrer les œuvres') }}">
                        <button type="button" class="ap-chip is-on" data-ap-filter="all" aria-pressed="true">{{ __('Toutes') }} ({{ $works->count() }})</button>
                        <button type="button" class="ap-chip" data-ap-filter="available" aria-pressed="false">{{ __('Disponibles') }} ({{ $available->count() }})</button>
                        <button type="button" class="ap-chip" data-ap-filter="sold" aria-pressed="false">{{ __('Vendues') }} ({{ $sold->count() }})</button>
                    </div>
                @endif
            </div>
            @if($works->isEmpty())
                <p class="ap-empty">{{ __('Aucune œuvre publiée pour le moment.') }}</p>
            @else
                <div class="ap-works">
                    @foreach($works as $w)
                        @php $isSold = $w->status === \App\Models\Artwork::STATUS_SOLD; @endphp
                        <a href="{{ route('art.show', $w->slug) }}" class="ap-work" data-ap-status="{{ $isSold ? 'sold' : 'available' }}">
                            <span class="ap-work__img">
                                @if($w->images[0] ?? null)<img src="{{ $w->images[0] }}" alt="{{ $w->title }}" loading="lazy" decoding="async">@else<x-icon name="palette" />@endif
                                @if($isSold)<span class="ap-work__tag">{{ __('Vendue') }}</span>@elseif($w->created_at && $w->created_at->gt(now()->subDays(30)))<span class="ap-work__tag is-new">{{ __('Nouveau') }}</span>@endif
                            </span>
                            <span class="ap-work__body">
                                <b>{{ $w->title }}</b>
                                <small>{{ collect([$w->medium, $w->dimensions, $w->year_created])->filter()->implode(' · ') ?: __('Œuvre originale') }}</small>
                                <span class="ap-work__price">
                                    @if($isSold)<em>{{ __('Collection privée') }}</em>@else{{ $fmt((int) $w->price_xof) }}@endif
                                    <x-icon name="alt-arrow-right" />
                                </span>
                            </span>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- ══ À PROPOS ══ --}}
        @if($profile->bio || $profile->statement)
            <section class="ox-sec ap-sec" id="a-propos">
                <div class="ap-about">
                    <div>
                        <p class="ap-label">{{ __('Biographie') }}</p>
                        <h2 class="ap-h2">{{ __('À propos de l\'artiste') }}</h2>
                        @if($profile->bio)<p class="ap-bio">{{ $profile->bio }}</p>@endif
                        @if($profile->studio_photo)<img src="{{ $profile->studio_photo }}" alt="{{ __('Atelier de :a', ['a' => $profile->display_name]) }}" class="ap-studio" loading="lazy">@endif
                    </div>
                    @if($profile->statement)
                        <blockquote class="ap-quote">
                            <p>{{ $profile->statement }}</p>
                            <footer>— {{ $profile->display_name }}, {{ __('Démarche') }}</footer>
                        </blockquote>
                    @endif
                </div>
            </section>
        @endif

        {{-- ══ PARCOURS ══ --}}
        @if($expos || $edu)
            <section class="ox-sec ap-sec" id="parcours">
                <div class="ap-head"><div><p class="ap-label">{{ __('Parcours') }}</p><h2 class="ap-h2">{{ __('Expositions & formation') }}</h2></div></div>
                <div class="ap-path">
                    @if($expos)
                        <div class="ap-box">
                            <h3><x-icon name="buildings" />{{ __('Expositions') }}</h3>
                            <ol class="ap-tl">
                                @foreach($exposTl as [$year, $label])
                                    <li><time class="{{ $year ? '' : 'is-n' }}">{{ $year ?? '•' }}</time><span>{{ $label }}</span></li>
                                @endforeach
                            </ol>
                        </div>
                    @endif
                    @if($edu)
                        <div class="ap-box">
                            <h3><x-icon name="book" />{{ __('Formation') }}</h3>
                            <ul class="ap-list">@foreach($edu as $e)<li><x-icon name="verified-check" />{{ $e }}</li>@endforeach</ul>
                        </div>
                    @endif
                </div>
            </section>
        @endif

        {{-- ══ SUR MESURE ══ --}}
        @if($profile->accepts_commissions)
            <section class="ap-commission" id="sur-mesure">
                <div class="ap-commission__txt">
                    <p class="ap-label">{{ __('Sur mesure') }}</p>
                    <h2 class="ap-h2">{{ __('Une œuvre pensée pour vous') }}</h2>
                    <p class="ap-lead">{{ __('Décrivez votre projet : format, couleurs, lieu d\'accrochage. :a vous répond avec une proposition et un devis.', ['a' => $profile->display_name]) }}</p>
                    <ol class="ap-steps">
                        <li><b>1</b>{{ __('Vous décrivez votre idée') }}</li>
                        <li><b>2</b>{{ __('L\'artiste propose un devis') }}</li>
                        <li><b>3</b>{{ __('Acompte, création, livraison') }}</li>
                    </ol>
                </div>
                <div class="ap-commission__side" id="reserver">
                    @if($profile->commission_from_xof)<small>{{ __('À partir de') }}</small><strong>{{ $fmt($profile->commission_from_xof) }}</strong>@else<small>{{ __('Commande sur mesure') }}</small><strong>{{ __('Sur devis') }}</strong>@endif
                    <ul>
                        @if($profile->commission_deposit_percent)<li><x-icon name="wallet-money" />{{ __('Acompte de :p %', ['p' => $profile->commission_deposit_percent]) }}</li>@endif
                        @if($profile->returns_accepted)<li><x-icon name="shield-check" />{{ __('Retours acceptés') }}</li>@endif
                        @if($profile->abidjan_delivery_xof !== null)<li><x-icon name="delivery" />{{ __('Livraison à Abidjan') }}</li>@endif
                    </ul>
                    <a href="{{ route('artist.commission', $profile->slug) }}" class="ap-btn is-main"><x-icon name="palette" />{{ __('Demander un devis') }}</a>
                </div>
            </section>
        @endif
    </div>
</main>

{{-- Barre mobile --}}
<div class="ox-bar" data-ox-bar>
    <span><small>{{ __('Artiste') }}</small><b>{{ \Illuminate\Support\Str::limit($profile->display_name, 22) }}</b></span>
    @if($profile->accepts_commissions)
        <a href="{{ route('artist.commission', $profile->slug) }}"><x-icon name="palette" />{{ __('Sur mesure') }}</a>
    @else
        <a href="#oeuvres"><x-icon name="gallery" />{{ __('Œuvres') }}</a>
    @endif
</div>

@include('partials.homepage-footer')
<script>
(function () {
    // Filtre Toutes / Disponibles / Vendues
    const chips = document.querySelectorAll('[data-ap-filter]');
    chips.forEach(chip => chip.addEventListener('click', () => {
        const f = chip.dataset.apFilter;
        chips.forEach(c => { const on = c === chip; c.classList.toggle('is-on', on); c.setAttribute('aria-pressed', on ? 'true' : 'false'); });
        document.querySelectorAll('[data-ap-status]').forEach(w => { w.hidden = f !== 'all' && w.dataset.apStatus !== f; });
    }));
    // Partage natif, sinon copie du lien
    const share = document.querySelector('[data-ap-share]');
    share?.addEventListener('click', async () => {
        const data = { title: @js($profile->display_name), url: @js($shareUrl) };
        try {
            if (navigator.share) { await navigator.share(data); return; }
            await navigator.clipboard.writeText(data.url);
            share.title = @js(__('Lien copié'));
            share.classList.add('is-on');
            setTimeout(() => share.classList.remove('is-on'), 1600);
        } catch (e) {}
    });
})();
</script>
</body>
</html>
