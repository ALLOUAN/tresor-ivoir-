<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $metaTitle }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => $metaTitle, 'description' => $metaDesc, 'image' => $event->cover_url, 'url' => $canonicalUrl, 'type' => 'article', 'alt' => $event->cover_alt])
    @include('partials.theme-init')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1d1a16; }
        .font-serif { font-family: 'Playfair Display', serif; }
        @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }
    </style>
    @include('partials.prose-clean')
    @include('partials.offer-page-theme')
    <style>
        /* Fiche événement, charte du site : crème, vert forêt, orange, Playfair. Affiche en entier (jamais de texte
           posé dessus), bande d'infos clés, onglets, module de réservation. Aplats, ombre au survol seulement. */
        .ev { max-width: 1180px; margin: 0 auto; padding: 22px 20px 64px; }
        .ev-back { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #9a4308; font-size: 13px; font-weight: 700; text-decoration: none; }
        .ev-back:hover { border-color: #b9500a; color: #9a4308; }
        .ev-back i { font-size: 15px; }

        .ev-hero { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(0, 1fr); gap: 34px; align-items: center; margin: 20px 0 24px; }
        .ev-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .ev-pill { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 13px; border-radius: 999px; background: #fdf4ec; color: #9a4308; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .ev-pill i { font-size: 15px; }
        .ev-pill.is-live { background: #e7f3ec; color: #1b6b3a; }
        .ev-pill.is-live .dot { width: 8px; height: 8px; border-radius: 999px; background: #1b6b3a; animation: ev-pulse 1.8s ease-out infinite; }
        .ev-pill.is-past { background: #f1ede5; color: #6b6458; }
        @keyframes ev-pulse { 0% { box-shadow: 0 0 0 0 rgba(27,107,58,.5); } 100% { box-shadow: 0 0 0 8px rgba(27,107,58,0); } }
        .ev-title { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(34px, 3vw + 18px, 58px); font-weight: 700; line-height: 1.03; letter-spacing: -.015em; color: #1d1a16; }
        .ev-sub { margin: 12px 0 0; font-family: 'Playfair Display', serif; font-style: italic; font-size: 19px; color: #4a443a; }
        .ev-meta { display: flex; flex-wrap: wrap; gap: 8px 22px; margin: 18px 0 0; padding: 0; list-style: none; font-size: 15px; color: #3a352c; }
        .ev-meta li { display: inline-flex; align-items: center; gap: 8px; }
        .ev-meta i { color: #b9500a; font-size: 18px; }
        .ev-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 24px; }
        .ev-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; transition: background .15s ease, border-color .15s ease, color .15s ease; }
        .ev-btn i { font-size: 18px; }
        .ev-btn.is-main { background: #b9500a; color: #fff; }
        .ev-btn.is-main:hover { background: #9a4308; color: #fff; }
        .ev-btn.is-line { background: #fff; color: #9a4308; border: 1.5px solid #b9500a; }
        .ev-btn.is-line:hover { background: #fdf4ec; }
        .ev-btn.is-icon { width: 50px; padding: 0; background: #fff; color: #9a4308; border: 1.5px solid #e6dccb; }
        .ev-btn.is-icon.is-on { background: #fdf4ec; border-color: #b9500a; }
        .ev-btn:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        .ev-cover { position: relative; aspect-ratio: 4 / 3; border-radius: 28px; overflow: hidden; background: #e9e1d2; border: 1px solid #e6dccb; }
        .ev-cover img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; }
        .ev-cover__empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #b9500a; font-size: 56px; background: #fdf4ec; }
        .ev-date { position: absolute; left: 18px; top: 18px; width: 78px; border-radius: 18px; overflow: hidden; background: #fff; text-align: center; box-shadow: 0 10px 24px -14px rgba(29,26,22,.5); }
        .ev-date small { display: block; padding: 5px 0; background: #b9500a; color: #fff; font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; }
        .ev-date b { display: block; padding-top: 4px; font-family: 'Playfair Display', serif; font-size: 32px; line-height: 1; color: #1d1a16; }
        .ev-date span { display: block; padding: 2px 0 8px; font-size: 11.5px; font-weight: 700; color: #6b6458; text-transform: capitalize; }

        /* Sections */
        .ev-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 40px; align-items: start; margin-top: 28px; }
        .ev-main { display: flex; flex-direction: column; gap: 48px; min-width: 0; }
        .ev-kicker { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .ev-h2 { margin: 0 0 16px; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.2vw + 18px, 32px); font-weight: 700; color: #1d1a16; }
        .ev-steps { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px; }
        .ev-step { display: grid; grid-template-columns: 74px minmax(0, 1fr); gap: 14px; align-items: start; }
        .ev-step__time { padding: 10px 0; border-radius: 14px; background: #1b6b3a; color: #fff; text-align: center; font-size: 14px; font-weight: 800; }
        .ev-step__time.is-n { background: #fdf4ec; color: #9a4308; }
        .ev-step__card { padding: 14px 16px; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
        .ev-step__card b { display: block; font-size: 15px; color: #1d1a16; }
        .ev-step__card p { margin: 5px 0 0; font-size: 14px; line-height: 1.55; color: #4a443a; }
        .ev-step__card small { display: flex; flex-wrap: wrap; gap: 4px 14px; margin-top: 8px; font-size: 12.5px; color: #6b6458; }
        .ev-step__card small span { display: inline-flex; align-items: center; gap: 5px; }
        .ev-step__card small i { color: #b9500a; font-size: 14px; }
        .ev-photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-auto-rows: 170px; gap: 10px; }
        .ev-photos button { position: relative; padding: 0; border: 0; border-radius: 18px; overflow: hidden; background: #e9e1d2; cursor: zoom-in; }
        .ev-photos button:first-child { grid-column: span 2; grid-row: span 2; }
        .ev-photos img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .ev-photos button:hover img { transform: scale(1.04); }
        .ev-photos button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .ev-video { aspect-ratio: 16 / 9; border-radius: 20px; overflow: hidden; background: #000; }
        .ev-video iframe { width: 100%; height: 100%; border: 0; }
        .ev-cap { margin: 6px 0 0; font-size: 12.5px; color: #6b6458; }
        .ev-where { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; margin-top: 14px; padding: 14px 16px; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; }
        .ev-where p { display: flex; align-items: center; gap: 10px; margin: 0; font-size: 14.5px; color: #3a352c; }
        .ev-where p i { color: #b9500a; font-size: 20px; }
        .ev-related { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 14px; }
        .ev-rel { position: relative; display: grid; grid-template-columns: 112px minmax(0, 1fr); gap: 14px; padding: 10px; border-radius: 20px; background: #fff; border: 1px solid #ece5d6; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .ev-rel:hover { border-color: #d8cdb8; box-shadow: 0 14px 30px -24px rgba(29,26,22,.45); }
        .ev-rel__img { position: relative; height: 112px; border-radius: 14px; overflow: hidden; background: #fdf4ec; color: #b9500a; display: flex; align-items: center; justify-content: center; font-size: 30px; }
        .ev-rel__img img { width: 100%; height: 100%; object-fit: cover; }
        .ev-rel__body { min-width: 0; display: flex; flex-direction: column; gap: 4px; padding: 4px 4px 4px 0; }
        .ev-rel__body small { font-size: 11px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #b9500a; }
        .ev-rel__body b { font-size: 15px; line-height: 1.3; color: #1d1a16; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .ev-rel__body span { display: inline-flex; align-items: center; gap: 6px; font-size: 12.5px; color: #6b6458; }
        .ev-rel__body span i { color: #b9500a; font-size: 14px; }
        .ev-share { display: flex; flex-wrap: wrap; gap: 8px; }
        .ev-share a, .ev-share button { display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #3a352c; font-size: 13.5px; font-weight: 600; text-decoration: none; cursor: pointer; }
        .ev-share a:hover, .ev-share button:hover { border-color: #b9500a; color: #9a4308; }
        .ev-share i { font-size: 15px; color: #b9500a; }

        /* Module de réservation */
        .ev-card { position: sticky; top: 140px; border-radius: 24px; background: #fff; border: 1px solid #e6dccb; overflow: hidden; scroll-margin-top: 140px; }
        .ev-card__head { padding: 20px 22px 16px; border-bottom: 1px solid #f0eadf; }
        .ev-card__head small { display: block; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #8a7f6b; }
        .ev-card__price { margin: 4px 0 0; font-family: 'Playfair Display', serif; font-size: 32px; font-weight: 700; color: #1d1a16; }
        .ev-card__price em { font-family: 'Inter', sans-serif; font-style: normal; font-size: 14px; font-weight: 700; color: #6b6458; }
        .ev-card__body { padding: 18px 22px 20px; display: flex; flex-direction: column; gap: 10px; }
        .ev-card__body .ev-btn { width: 100%; }
        .ev-card__list { margin: 8px 0 0; padding: 14px 0 0; list-style: none; border-top: 1px solid #f0eadf; display: flex; flex-direction: column; gap: 12px; font-size: 14px; color: #3a352c; }
        .ev-card__list li { display: flex; align-items: flex-start; gap: 10px; line-height: 1.45; }
        .ev-card__list i { flex: none; color: #b9500a; font-size: 18px; margin-top: 1px; }
        .ev-card__list small { display: block; font-size: 12px; color: #8a7f6b; }

        .ev .reveal { opacity: 1; transform: none; }
        #lightbox { background: rgba(14,12,9,.94) !important; }

        @media (max-width: 1023px) {
            .ev-hero { grid-template-columns: 1fr; gap: 20px; }
            .ev-cover { order: -1; }
            .ev-grid { grid-template-columns: 1fr; }
            .ev-card { position: static; }
        }
        @media (max-width: 767px) {
            .ev { padding: 16px 16px 120px; }
            .ev-hero { margin-top: 14px; }
            .ev-cover { border-radius: 22px; }
            .ev-date { left: 12px; top: 12px; width: 66px; border-radius: 15px; }
            .ev-date b { font-size: 26px; }
            .ev-meta { flex-direction: column; gap: 8px; font-size: 14.5px; }
            .ev-actions .ev-btn.is-main { flex: 1 1 100%; }
            .ev-actions .ev-btn.is-line { flex: 1 1 0; min-width: 0; padding: 0 12px; white-space: nowrap; font-size: 13.5px; }
            .ev-actions .ev-btn { height: 46px; }
            .ev-actions .ev-btn.is-icon { width: 46px; }
            .ev-photos { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: 130px; }
            .ev-related { grid-template-columns: 1fr; }
            .ev-rel { grid-template-columns: 92px minmax(0, 1fr); }
            .ev-rel__img { height: 92px; }
            .ev-step { grid-template-columns: 62px minmax(0, 1fr); gap: 10px; }
        }
        @media (prefers-reduced-motion: reduce) { .ev-pill.is-live .dot, .ev-photos img { animation: none; transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

@php
    $now = now();
    $eventEnd = $event->ends_at ?? $event->starts_at;
    $status = $event->starts_at && $now->lt($event->starts_at) ? 'soon' : ($now->lte($eventEnd) ? 'live' : 'past');
    $statusLabel = ['soon' => __('À venir'), 'live' => __('En cours'), 'past' => __('Terminé')][$status];
    $isFree = $event->is_free || (float) ($event->price ?? 0) <= 0;
    $priceLabel = $isFree ? __('Gratuit') : number_format((float) $event->price, 0, ',', ' ');
    $ctaUrl = $event->ticket_url ?: '#reserver';
    $ctaLabel = $event->ticket_url ? __('Réserver ma place') : __('En savoir plus');
    $where = $event->location_name ?: ($event->city ?: __('Côte d\'Ivoire'));
    $loc = app()->getLocale();
    $dateLong = $event->starts_at?->locale($loc)->isoFormat('dddd D MMMM YYYY [à] HH:mm');
    $categoryName = $event->category?->localized('name') ?? __('Événement');
@endphp

<main class="ev">
    <a href="{{ route('events.index') }}" class="ev-back"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ __('Agenda') }}</a>

    {{-- ══ EN-TÊTE : texte à gauche, affiche entière à droite ══ --}}
    <header class="ev-hero">
        <div class="min-w-0">
            <div class="ev-pills">
                <span class="ev-pill"><x-icon name="masks" />{{ $categoryName }}</span>
                @if($status === 'live')
                    <span class="ev-pill is-live"><span class="dot" aria-hidden="true"></span>{{ __('En cours') }}</span>
                @elseif($status === 'past')
                    <span class="ev-pill is-past"><x-icon name="history" />{{ __('Terminé') }}</span>
                @endif
                <span id="countdownChip" class="ev-pill is-live hidden"><span class="dot" aria-hidden="true"></span><span id="countdownText"></span></span>
            </div>
            <h1 class="ev-title">{{ $event->localized('title') }}</h1>
            @if($event->subtitle_fr)<p class="ev-sub">{{ $event->localized('subtitle') }}</p>@endif
            <ul class="ev-meta">
                @if($dateLong)<li><x-icon name="calendar" /><span class="first-letter:uppercase">{{ $dateLong }}</span></li>@endif
                <li><x-icon name="map-point" />{{ $where }}@if($event->location_name && $event->city), {{ $event->city }}@endif</li>
                @if($event->organizer_name)<li><x-icon name="user-rounded" />{{ $event->organizer_name }}</li>@endif
            </ul>
            <div class="ev-actions">
                <a href="{{ $ctaUrl }}" @if($event->ticket_url) target="_blank" rel="noopener noreferrer" @endif class="ev-btn is-main"><x-icon name="ticket" />{{ $ctaLabel }}</a>
                <a href="{{ route('events.ics', $event->slug) }}" class="ev-btn is-line"><x-icon name="calendar" />{{ __('Mon agenda') }}</a>
                <a href="#partager" class="ev-btn is-line"><x-icon name="share" />{{ __('Partager') }}</a>
                @auth
                    @if(auth()->user()->role === 'visitor')
                        @if(!($isFavorited ?? false))
                            <form method="POST" action="{{ route('visitor.favorites.store') }}" class="inline">
                                @csrf
                                <input type="hidden" name="type" value="event">
                                <input type="hidden" name="id" value="{{ $event->id }}">
                                <button type="submit" class="ev-btn is-icon" title="{{ __('Ajouter à ma wishlist') }}" aria-label="{{ __('Ajouter à ma wishlist') }}"><x-icon name="heart" /></button>
                            </form>
                        @else
                            <span class="ev-btn is-icon is-on" title="{{ __('Dans vos favoris') }}"><x-icon name="heart" /></span>
                        @endif
                    @endif
                @endauth
            </div>
        </div>
        <div class="ev-cover">
            @if(!empty($event->cover_url))
                <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->localized('title') }}" decoding="async" onclick="openLightbox(this.currentSrc || this.src, @js($event->localized('title')))">
            @else
                <span class="ev-cover__empty"><x-icon name="confetti" /></span>
            @endif
            @if($event->starts_at)
                <span class="ev-date" aria-hidden="true">
                    <small>{{ $event->starts_at->locale($loc)->isoFormat('MMM') }}</small>
                    <b>{{ $event->starts_at->format('d') }}</b>
                    <span>{{ $event->starts_at->locale($loc)->isoFormat('ddd') }}</span>
                </span>
            @endif
        </div>
    </header>

    {{-- ══ INFOS CLÉS ══ --}}
    <ul class="ox-keys" id="details">
        <li><x-icon name="calendar" /><span><small>{{ __('Date & heure') }}</small><b>{{ $event->starts_at?->format('d/m/Y H:i') }}@if($event->ends_at) → {{ $event->ends_at->format('d/m H:i') }}@endif</b></span></li>
        <li><x-icon name="map-point" /><span><small>{{ __('Lieu') }}</small><b>{{ $where }}</b></span></li>
        @if($event->audience)<li><x-icon name="users-group-rounded" /><span><small>{{ __('Public concerné') }}</small><b>{{ $event->audience }}</b></span></li>@endif
        <li><x-icon name="tag-price" /><span><small>{{ __('Tarif') }}</small><b>{{ $priceLabel }}@unless($isFree) {{ __('FCFA') }}@endunless</b></span></li>
        <li class="{{ $status === 'past' ? '' : 'is-ok' }}"><x-icon name="clock-circle" /><span><small>{{ __('Statut') }}</small><b>{{ $statusLabel }}</b></span></li>
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        @if($event->description_fr)<a href="#presentation">{{ __('Présentation') }}</a>@endif
        @if($event->hasProgram())<a href="#programme">{{ __('Programme') }}</a>@endif
        @if($photos->isNotEmpty() || $videos->isNotEmpty())<a href="#galerie">{{ __('Galerie') }}</a>@endif
        @if($event->latitude && $event->longitude)<a href="#acces">{{ __('Accès') }}</a>@endif
        @if($related->isNotEmpty())<a href="#a-suivre">{{ __('À suivre') }}</a>@endif
        <a href="#partager">{{ __('Partager') }}</a>
    </nav>

    <div class="ev-grid">
        <div class="ev-main">
            @if($event->description_fr)
                <section class="ox-sec" id="presentation">
                    <p class="ev-kicker">{{ __('Présentation') }}</p>
                    <h2 class="ev-h2">{{ __('À propos de cet événement') }}</h2>
                    <div class="prose-content max-w-none">{!! \App\Support\HtmlSanitizer::articleBody($event->localized('description')) !!}</div>
                </section>
            @endif

            @if($event->hasProgram())
                <section class="ox-sec" id="programme">
                    <p class="ev-kicker">{{ __('Déroulé') }}</p>
                    <h2 class="ev-h2">{{ __('Programme') }}</h2>
                    <ol class="ev-steps">
                        @foreach($event->program as $item)
                            <li class="ev-step">
                                <span class="ev-step__time {{ empty($item['time']) ? 'is-n' : '' }}">{{ $item['time'] ?? $loop->iteration }}</span>
                                <div class="ev-step__card">
                                    <b>{{ $item['title'] ?? '' }}</b>
                                    @if(!empty($item['description']))<p>{{ $item['description'] }}</p>@endif
                                    @if(!empty($item['speaker']) || !empty($item['location']))
                                        <small>
                                            @if(!empty($item['speaker']))<span><x-icon name="user-rounded" />{{ $item['speaker'] }}</span>@endif
                                            @if(!empty($item['location']))<span><x-icon name="map-point" />{{ $item['location'] }}</span>@endif
                                        </small>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </section>
            @endif

            @if($photos->isNotEmpty() || $videos->isNotEmpty())
                <section class="ox-sec" id="galerie">
                    <p class="ev-kicker">{{ __('Ambiance') }}</p>
                    <h2 class="ev-h2">{{ __('Galerie') }}</h2>
                    @if($photos->isNotEmpty())
                        <div class="ev-photos">
                            @foreach($photos as $photo)
                                <button type="button" onclick="openLightbox(@js($photo->url), @js($photo->caption ?? $event->localized('title')))" aria-label="{{ __('Agrandir la photo :n', ['n' => $loop->iteration]) }}">
                                    <img src="{{ $photo->url }}" alt="{{ $photo->alt_text ?: $event->localized('title') }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @foreach($videos as $video)
                        <div class="ev-video {{ $loop->first && $photos->isNotEmpty() ? 'mt-4' : '' }} {{ ! $loop->first ? 'mt-4' : '' }}"><iframe src="{{ $video->url }}" allow="autoplay; encrypted-media" allowfullscreen loading="lazy" title="{{ $video->caption ?: $event->localized('title') }}"></iframe></div>
                        @if($video->caption)<p class="ev-cap">{{ $video->caption }}</p>@endif
                    @endforeach
                </section>
            @endif

            @if($event->latitude && $event->longitude)
                <section class="ox-sec" id="acces">
                    <p class="ev-kicker">{{ __('Accès') }}</p>
                    <h2 class="ev-h2">{{ __('Où se déroule l\'événement ?') }}</h2>
                    @include('partials.route-map', [
                        'type' => 'evenement', 'slug' => $event->slug,
                        'lat' => $event->latitude, 'lng' => $event->longitude, 'name' => $event->title_fr,
                        'accent' => '#b9500a', 'height' => '340px', 'frameClass' => 'map-card rounded-2xl overflow-hidden',
                    ])
                    <div class="ev-where">
                        <p><x-icon name="map-point" />{{ $event->address ?: $event->location_name ?: $event->city }}</p>
                        <a href="{{ route('directions.go', ['evenement', $event->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer" class="ev-btn is-main" style="height:42px"><x-icon name="streets-navigation" />{{ __('Itinéraire') }}</a>
                    </div>
                </section>
            @endif

            @if($related->isNotEmpty())
                <section class="ox-sec" id="a-suivre">
                    <p class="ev-kicker">{{ __('À suivre aussi') }}</p>
                    <h2 class="ev-h2">{{ __('Découvrez également') }}</h2>
                    <div class="ev-related">
                        @foreach($related as $r)
                            <a href="{{ route('events.show', $r->slug) }}" class="ev-rel">
                                <span class="ev-rel__img">@if(!empty($r->cover_url))<img src="{{ $r->cover_url }}" alt="{{ $r->cover_alt ?: $r->localized('title') }}" loading="lazy" decoding="async">@else<x-icon name="confetti" />@endif</span>
                                <span class="ev-rel__body">
                                    <small>{{ $r->category?->localized('name') ?? __('Événement') }}</small>
                                    <b>{{ $r->localized('title') }}</b>
                                    <span><x-icon name="calendar" />{{ $r->starts_at?->format('d/m/Y H:i') }}</span>
                                    <span><x-icon name="map-point" />{{ $r->city ?: __('Côte d\'Ivoire') }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif

            <section class="ox-sec" id="partager">
                <p class="ev-kicker">{{ __('Diffuser') }}</p>
                <h2 class="ev-h2">{{ __('Partager cet événement') }}</h2>
                <div class="ev-share">
                    <a href="https://wa.me/?text={{ urlencode($event->localized('title') . ' - ' . $canonicalUrl) }}" target="_blank" rel="noopener"><i class="fab fa-whatsapp" aria-hidden="true"></i>WhatsApp</a>
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($canonicalUrl) }}" target="_blank" rel="noopener"><i class="fab fa-facebook-f" aria-hidden="true"></i>Facebook</a>
                    <a href="https://twitter.com/intent/tweet?url={{ urlencode($canonicalUrl) }}&text={{ urlencode($event->localized('title')) }}" target="_blank" rel="noopener"><i class="fab fa-x-twitter" aria-hidden="true"></i>X</a>
                    <button type="button" onclick="navigator.clipboard.writeText(window.location.href).then(() => { this.lastChild.textContent = @js(__('Lien copié')); })"><i class="fas fa-link" aria-hidden="true"></i><span>{{ __('Copier le lien') }}</span></button>
                </div>
            </section>
        </div>

        {{-- ══ MODULE DE RÉSERVATION ══ --}}
        <aside class="ev-card" id="reserver" aria-label="{{ __('Participer') }}">
            <div class="ev-card__head">
                <small>{{ __('Tarif') }}</small>
                <p class="ev-card__price">{{ $priceLabel }}@unless($isFree) <em>{{ __('FCFA') }}</em>@endunless</p>
                <div class="ox-card__badges">
                    @if($status !== 'past')<span><x-icon name="clock-circle" />{{ $statusLabel }}</span>@endif
                    @if($event->capacity)<span class="is-neutral"><x-icon name="users-group-rounded" />{{ __(':n places', ['n' => $event->capacity]) }}</span>@endif
                </div>
            </div>
            <div class="ev-card__body">
                @if($event->ticket_url)
                    <a href="{{ $event->ticket_url }}" target="_blank" rel="noopener noreferrer" class="ev-btn is-main"><x-icon name="ticket" />{{ __('Réserver ma place') }}</a>
                @endif
                <a href="{{ route('events.ics', $event->slug) }}" class="ev-btn {{ $event->ticket_url ? 'is-line' : 'is-main' }}"><x-icon name="calendar" />{{ __('Ajouter à mon agenda') }}</a>
                <ul class="ev-card__list">
                    <li><x-icon name="calendar" /><span>{{ $event->starts_at?->format('d/m/Y H:i') }}@if($event->ends_at)<small>{{ __('au') }} {{ $event->ends_at->format('d/m/Y H:i') }}</small>@endif</span></li>
                    <li><x-icon name="map-point" /><span>{{ $event->location_name ?: __('Non précisé') }}<small>{{ $event->city ?: __('Côte d\'Ivoire') }}</small></span></li>
                    @if($event->organizer_name)<li><x-icon name="user-rounded" /><span>{{ $event->organizer_name }}<small>{{ __('Organisateur') }}</small></span></li>@endif
                    @if($event->registration_deadline)<li><x-icon name="hourglass" /><span>{{ __('Inscriptions jusqu\'au :d', ['d' => \Illuminate\Support\Carbon::parse($event->registration_deadline)->format('d/m/Y')]) }}</span></li>@endif
                </ul>
            </div>
        </aside>
    </div>
</main>

{{-- Barre mobile : tarif + action principale (masquée quand le module est visible) --}}
<div class="ox-bar" data-ox-bar>
    <span><small>{{ __('Tarif') }}</small><b>{{ $priceLabel }}@unless($isFree) {{ __('FCFA') }}@endunless</b></span>
    <a href="{{ $event->ticket_url ?: route('events.ics', $event->slug) }}" @if($event->ticket_url) target="_blank" rel="noopener noreferrer" @endif>
        <x-icon name="{{ $event->ticket_url ? 'ticket' : 'calendar' }}" />{{ $event->ticket_url ? __('Réserver') : __('Mon agenda') }}
    </a>
</div>

{{-- Visionneuse --}}
<div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center p-4" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[86vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    <p id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-sm text-center px-4"></p>
</div>

@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
function openLightbox(url, caption) {
    document.getElementById('lightbox-img').src = url;
    document.getElementById('lightbox-caption').textContent = caption || '';
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Compte à rebours ──
(function () {
    const startsAt = @json($event->starts_at?->toIso8601String());
    if (!startsAt) return;
    const target = new Date(startsAt).getTime();
    const chip = document.getElementById('countdownChip');
    const text = document.getElementById('countdownText');
    if (!chip || !text) return;
    const T = @json(['d' => __('Débute dans :d j :h h'), 'h' => __('Débute dans :h h :m min'), 'm' => __('Débute dans :m min')]);
    function tick() {
        const diff = target - Date.now();
        if (diff <= 0) { chip.classList.add('hidden'); return; }
        const d = Math.floor(diff / 86400000), h = Math.floor((diff % 86400000) / 3600000), m = Math.floor((diff % 3600000) / 60000);
        text.textContent = (d > 0 ? T.d : (h > 0 ? T.h : T.m)).replace(':d', d).replace(':h', h).replace(':m', m);
        chip.classList.remove('hidden');
    }
    tick();
    setInterval(tick, 30000);
})();
</script>
</body>
</html>
