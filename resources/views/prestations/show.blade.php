<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $settings->banner_title ?: __('Nos Prestations') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="description" content="{{ __('Visibilité éditoriale, référencement, réservation en ligne et vidéo : les prestations de :s pour les professionnels du tourisme et de la culture en Côte d\'Ivoire.', ['s' => $siteBrand['site_name']]) }}">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1d1a16; }
        .font-serif { font-family: 'Playfair Display', Georgia, serif; }
        /* Page « Nos prestations » (offre B2B), charte du site : crème, vert forêt, orange, Playfair.
           Aplats, ombre au survol seulement, motifs ivoiriens discrets. */
        .pr-wrap { max-width: 1180px; margin: 0 auto; padding: 0 20px; }
        .pr-label { margin: 0 0 8px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #b9500a; }
        .pr-h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(26px, 1.6vw + 18px, 40px); font-weight: 700; line-height: 1.1; color: #1d1a16; }
        .pr-sub { margin: 12px 0 0; max-width: 620px; font-size: 16px; line-height: 1.65; color: #4a443a; }
        .pr-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px 30px; margin-bottom: 30px; }
        .pr-sec { padding: 72px 0; }
        .pr-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 52px; padding: 0 22px; border-radius: 999px; font-size: 15px; font-weight: 700; text-decoration: none; border: 0; cursor: pointer; white-space: nowrap; transition: background .15s ease, border-color .15s ease, color .15s ease; }
        .pr-btn .ico { font-size: 19px; }
        .pr-btn.is-main { background: #b9500a; color: #fff; }
        .pr-btn.is-main:hover { background: #9a4308; color: #fff; }
        .pr-btn.is-line { background: #fff; color: #9a4308; border: 1.5px solid #b9500a; }
        .pr-btn.is-line:hover { background: #fdf4ec; }
        .pr-btn.is-white { background: #fff; color: #123a25; }
        .pr-btn.is-white:hover { background: #fdf4ec; color: #123a25; }
        .pr-btn.is-ghost { background: transparent; color: #fff; border: 1.5px solid rgba(255,255,255,.5); }
        .pr-btn.is-ghost:hover { border-color: #fff; background: rgba(255,255,255,.08); }
        .pr-btn:focus-visible, .pr-card:focus-within, .prestation-banner-dot:focus-visible, .prestation-banner-arrow:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }

        /* Héros */
        .pr-hero { position: relative; overflow: hidden; background: #f3ead8; border-bottom: 1px solid #e3d5bb; }
        .pr-hero__in { position: relative; z-index: 1; display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.05fr); gap: 40px; align-items: center; padding: 56px 20px 64px; max-width: 1180px; margin: 0 auto; }
        .pr-hero__txt { padding: 28px 30px; border-radius: 28px; background: #f6efe1; border: 1px solid #e3d5bb; }
        .pr-kicker { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #fff; border: 1px solid #e3d5bb; font-size: 12px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; color: #9a4308; }
        .pr-kicker .ico { font-size: 15px; }
        .pr-title { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(36px, 3vw + 18px, 62px); font-weight: 700; line-height: 1.03; letter-spacing: -.015em; color: #1d1a16; }
        .pr-title em { font-style: italic; color: #b9500a; }
        .pr-lead { margin: 16px 0 0; font-size: 17px; line-height: 1.65; color: #3a352c; }
        .pr-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 24px; }
        .pr-trust { display: flex; flex-wrap: wrap; gap: 8px 18px; margin: 20px 0 0; padding: 0; list-style: none; font-size: 13.5px; color: #4a443a; }
        .pr-trust li { display: inline-flex; align-items: center; gap: 7px; }
        .pr-trust .ico { color: #1b6b3a; font-size: 17px; }

        /* Visuel : bannières administrées (carrousel conservé) */
        .pr-visual { position: relative; }
        .prestation-cover-frame { position: relative; aspect-ratio: 4 / 3; border-radius: 28px; overflow: hidden; background: #1c1310; border: 1px solid #e3d5bb; }
        .prestation-banner-slide { position: absolute; inset: 0; }
        .prestation-banner-slide img { width: 100%; height: 100%; object-fit: cover; }
        .pr-slide-card { position: absolute; left: 14px; right: 14px; bottom: 14px; padding: 16px 18px; border-radius: 20px; background: #fff; color: #1d1a16; }
        .pr-slide-card small { display: block; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #b9500a; }
        .pr-slide-card b { display: block; margin-top: 4px; font-family: 'Playfair Display', serif; font-size: 21px; line-height: 1.2; }
        .pr-slide-card p { margin: 6px 0 0; font-size: 14px; line-height: 1.5; color: #4a443a; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .pr-slide-card a { display: inline-flex; align-items: center; gap: 6px; margin-top: 10px; font-size: 13.5px; font-weight: 700; color: #9a4308; text-decoration: none; }
        .pr-slide-card a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .pr-carousel-ui { position: absolute; top: 14px; right: 14px; display: flex; align-items: center; gap: 8px; z-index: 5; }
        .prestation-banner-dots { display: flex; gap: 6px; padding: 8px 10px; border-radius: 999px; background: #fff; }
        .prestation-banner-dot { width: 8px; height: 8px; padding: 0; border-radius: 999px; border: 0; background: #e3d5bb; cursor: pointer; transition: width .25s ease, background .25s ease; }
        .prestation-banner-dot.is-active { width: 22px; background: #b9500a; }
        .prestation-banner-arrow { width: 36px; height: 36px; border-radius: 999px; border: 0; background: #fff; color: #9a4308; font-size: 18px; font-weight: 700; cursor: pointer; }
        .prestation-banner-arrow:hover { background: #fdf4ec; }
        .pr-visual--empty { aspect-ratio: 4 / 3; border-radius: 28px; background: #123a25; color: #fff; display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding: 18px; }
        .pr-visual--empty div { border-radius: 20px; background: rgba(255,255,255,.08); padding: 18px; display: flex; flex-direction: column; justify-content: flex-end; }
        .pr-visual--empty .ico { font-size: 28px; color: #f6c38f; margin-bottom: auto; }
        .pr-visual--empty b { font-size: 16px; }

        /* Chiffres */
        .pr-stats { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; margin-top: -34px; position: relative; z-index: 2; }
        .pr-stat { padding: 20px 22px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .pr-stat b { display: block; font-family: 'Playfair Display', serif; font-size: 38px; line-height: 1; color: #1d1a16; }
        .pr-stat span { display: flex; align-items: center; gap: 7px; margin-top: 8px; font-size: 13.5px; color: #6b6458; }
        .pr-stat .ico { color: #b9500a; font-size: 16px; }

        /* Prestations */
        .pr-cards { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
        .pr-card { position: relative; display: flex; flex-direction: column; border-radius: 24px; overflow: hidden; background: #fff; border: 1px solid #ece5d6; transition: border-color .15s ease, box-shadow .2s ease; }
        .pr-card:hover { border-color: #d8cdb8; box-shadow: 0 20px 38px -28px rgba(29,26,22,.55); }
        .pr-card__img { aspect-ratio: 16 / 10; background: #f3eee4; overflow: hidden; }
        .pr-card__img img { width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .pr-card:hover .pr-card__img img { transform: scale(1.04); }
        .pr-card__body { flex: 1; display: flex; flex-direction: column; padding: 22px; }
        .pr-card__top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
        .pr-card__ic { width: 48px; height: 48px; border-radius: 16px; background: #fdf4ec; color: #b9500a; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .pr-card__num { font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; color: #e3d5bb; }
        .pr-card h3 { margin: 16px 0 0; font-family: 'Playfair Display', serif; font-size: 21px; line-height: 1.25; color: #1d1a16; }
        .pr-card p { margin: 8px 0 0; font-size: 14.5px; line-height: 1.6; color: #4a443a; }
        .pr-card__link { margin-top: auto; padding-top: 18px; display: inline-flex; align-items: center; gap: 7px; font-size: 14px; font-weight: 700; color: #9a4308; text-decoration: none; }
        .pr-card__link::after { content: ''; position: absolute; inset: 0; }
        .pr-card__link:hover { text-decoration: underline; text-underline-offset: 3px; }
        .pr-empty { padding: 34px; border-radius: 24px; background: #fff; border: 1px dashed #d8cdb8; text-align: center; color: #6b6458; }

        /* Méthode */
        .pr-steps { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; margin: 0; padding: 0; list-style: none; counter-reset: st; }
        .pr-step { position: relative; padding: 24px 22px; border-radius: 24px; background: #fff; border: 1px solid #ece5d6; }
        .pr-step b.n { display: flex; width: 40px; height: 40px; border-radius: 999px; background: #1b6b3a; color: #fff; align-items: center; justify-content: center; font-size: 15px; font-weight: 800; }
        .pr-step h3 { margin: 16px 0 0; font-size: 17px; font-weight: 800; color: #1d1a16; }
        .pr-step p { margin: 6px 0 0; font-size: 14px; line-height: 1.6; color: #4a443a; }
        .pr-step small { display: inline-flex; margin-top: 12px; padding: 4px 10px; border-radius: 999px; background: #f6f3ed; font-size: 12px; font-weight: 700; color: #6b6458; }

        /* Atouts */
        .pr-pillars { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr); gap: 16px; }
        .pr-pillar-main { padding: 32px; border-radius: 28px; background: #123a25; color: #fff; display: flex; flex-direction: column; justify-content: space-between; gap: 22px; }
        .pr-pillar-main h3 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.2vw + 18px, 32px); line-height: 1.15; }
        .pr-pillar-main p { margin: 10px 0 0; font-size: 15.5px; line-height: 1.65; color: #cfe6d7; }
        .pr-pillar-main ul { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 10px; font-size: 14.5px; }
        .pr-pillar-main li { display: flex; align-items: center; gap: 10px; }
        .pr-pillar-main li .ico { color: #f6c38f; font-size: 18px; }
        .pr-pillar-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .pr-pillar { padding: 22px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .pr-pillar .ico { width: 44px; height: 44px; border-radius: 14px; background: #fdf4ec; color: #b9500a; justify-content: center; font-size: 21px; }
        .pr-pillar b { display: block; margin-top: 14px; font-size: 16px; color: #1d1a16; }
        .pr-pillar span { display: block; margin-top: 5px; font-size: 13.5px; line-height: 1.55; color: #4a443a; }

        /* Vidéo & catalogue */
        .pr-video { border-radius: 28px; overflow: hidden; aspect-ratio: 16 / 9; background: #000; border: 1px solid #ece5d6; }
        .pr-video iframe, .pr-video video { width: 100%; height: 100%; border: 0; display: block; }
        .df-catalog-frame { border: 1px solid #ece5d6; border-radius: 24px; background: #fff; min-height: 420px; }
        .pr-catalog-actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin-top: 18px; }

        /* Appel final */
        .pr-cta { position: relative; overflow: hidden; border-radius: 32px; background: #123a25; color: #fff; padding: 44px 40px; display: grid; grid-template-columns: minmax(0, 1fr) auto; gap: 24px; align-items: center; }
        .pr-cta h2 { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(26px, 1.6vw + 18px, 40px); line-height: 1.12; }
        .pr-cta p { margin: 10px 0 0; max-width: 560px; font-size: 16px; line-height: 1.6; color: #cfe6d7; }
        .pr-cta__actions { display: flex; flex-wrap: wrap; gap: 10px; }
        .pr-cta__in { position: relative; z-index: 1; }

        /* Motifs ivoiriens plus discrets sur cette page (en-tête et appel final) */
        .pr-hero .ivm-g { opacity: .05 !important; }
        .pr-cta .ivm-g { opacity: .04 !important; }
        /* En-tête plus compact */
        .pr-hero__in { padding: 34px 20px 52px; gap: 32px; grid-template-columns: minmax(0, 1fr) minmax(0, .95fr); }
        .pr-hero__txt { padding: 22px 26px; }
        .pr-kicker { font-size: 11px; padding: 5px 11px; }
        .pr-title { margin-top: 12px; font-size: clamp(30px, 1.8vw + 16px, 46px); line-height: 1.06; }
        .pr-lead { margin-top: 12px; font-size: 15.5px; line-height: 1.6; }
        .pr-actions { margin-top: 18px; }
        .pr-actions .pr-btn { height: 46px; padding: 0 18px; font-size: 14px; }
        .pr-trust { margin-top: 14px; font-size: 13px; }
        .prestation-cover-frame, .pr-visual--empty { aspect-ratio: 16 / 11; }
        .pr-slide-card { padding: 12px 16px; }
        .pr-slide-card b { font-size: 18px; }
        @media (max-width: 1023px) { .pr-hero__in { grid-template-columns: minmax(0, 1fr); padding: 26px 20px 52px; } }
        @media (max-width: 640px) { .pr-hero__in { padding: 20px 16px 50px; } .pr-hero__txt { padding: 18px 16px; } .pr-title { font-size: clamp(26px, 7vw, 32px); } .pr-lead { font-size: 14.5px; } .prestation-cover-frame, .pr-visual--empty { aspect-ratio: 4 / 3.2; } }
        /* Chiffres aux couleurs du logo : coucher de soleil, palmier, lagune, anneau doré */
        .pr-stat { --c: #c2560d; --t: #fdf0e1; position: relative; overflow: hidden; display: grid; grid-template-columns: auto minmax(0, 1fr); grid-template-rows: auto auto; column-gap: 14px; align-items: center; padding: 20px 20px 18px; background: var(--t); border: 1.5px solid #e6cf98; }
        .pr-stat::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 4px; background: var(--c); }
        .pr-stat.is-sun { --c: #d9650f; --t: #fdf0e1; }
        .pr-stat.is-palm { --c: #4a8a2a; --t: #eef5e4; }
        .pr-stat.is-lagoon { --c: #1f8079; --t: #e3f3f1; }
        .pr-stat.is-gold { --c: #b0861c; --t: #fbf3dc; }
        .pr-stat__ic { grid-row: 1 / span 2; width: 52px; height: 52px; border-radius: 16px; background: var(--c); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; }
        .pr-stat__ic .ico { color: #fff; }
        .pr-stat b { color: var(--c); font-size: 36px; }
        .pr-stat > span:last-child { margin-top: 4px; font-size: 13.5px; font-weight: 600; color: #3a2f22; }
        @media (max-width: 640px) { .pr-stat { grid-template-columns: minmax(0, 1fr); padding: 16px; } .pr-stat__ic { grid-row: auto; width: 40px; height: 40px; border-radius: 12px; font-size: 19px; margin-bottom: 10px; } .pr-stat b { font-size: 28px; } }
        .reveal { opacity: 0; transform: translateY(16px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .reveal { opacity: 1; transform: none; transition: none; } .pr-card__img img { transition: none; } }

        @media (max-width: 1023px) {
            .pr-hero__in { grid-template-columns: minmax(0, 1fr); gap: 22px; padding: 40px 20px 60px; }
            .pr-stats { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pr-cards { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pr-steps { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .pr-pillars { grid-template-columns: minmax(0, 1fr); }
            .pr-cta { grid-template-columns: minmax(0, 1fr); }
        }
        @media (max-width: 640px) {
            .pr-wrap { padding: 0 16px; }
            .pr-sec { padding: 52px 0; }
            .pr-hero__in { padding: 28px 16px 56px; }
            .pr-hero__txt { padding: 20px 18px; border-radius: 22px; }
            .pr-lead { font-size: 15.5px; }
            .pr-actions .pr-btn { flex: 1 1 auto; }
            .prestation-cover-frame, .pr-visual--empty { border-radius: 22px; aspect-ratio: 4 / 3.4; }
            .pr-stat { padding: 16px; }
            .pr-stat b { font-size: 30px; }
            .pr-cards, .pr-steps, .pr-pillar-grid { grid-template-columns: minmax(0, 1fr); }
            .pr-pillar-main { padding: 24px; border-radius: 22px; }
            .pr-cta { padding: 30px 22px; border-radius: 24px; }
            .pr-cta__actions .pr-btn { flex: 1 1 100%; }
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-354c0e2d.css') }}?v={{ @filemtime(public_path('css/tw-354c0e2d.css')) }}">
</head>
<body class="min-h-screen">
    @include('partials.page-background')
@include('partials.public-top-nav')
@php
    $fmtN = fn ($n) => number_format((int) $n, 0, ',', ' ');
@endphp

{{-- ══ HÉROS : promesse + bannières administrées ══ --}}
<section class="pr-hero">
    @include('partials.ivorian-motifs', ['culture' => 'national'])
    <div class="pr-hero__in">
        <div class="pr-hero__txt reveal">
            <span class="pr-kicker"><x-icon name="case" />{{ __('Pour les professionnels du tourisme et de la culture') }}</span>
            <h1 class="pr-title">{{ __('Faites rayonner votre établissement') }} <em>{{ __('en Côte d\'Ivoire') }}</em></h1>
            <p class="pr-lead">{{ __('Reportages, référencement, réservation en ligne et vidéo : nous mettons votre offre devant les voyageurs qui préparent leur séjour, et vous aidons à transformer leur intérêt en réservations.') }}</p>
            <div class="pr-actions">
                <button type="button" class="pr-btn is-main" onclick="window.openContactModal && window.openContactModal()"><x-icon name="chat-round-dots" />{{ __('Demander un devis') }}</button>
                <a href="#prestation-items" class="pr-btn is-line"><x-icon name="widget" />{{ __('Voir nos prestations') }}</a>
            </div>
            <ul class="pr-trust">
                <li><x-icon name="check-circle" />{{ __('Réponse sous 48 h') }}</li>
                <li><x-icon name="check-circle" />{{ __('Devis gratuit') }}</li>
                <li><x-icon name="check-circle" />{{ __('Contenus en français et en anglais') }}</li>
            </ul>
        </div>

        <div class="pr-visual reveal">
            @if($banners->isNotEmpty())
                <div class="prestation-cover-frame" id="prestation-banner-carousel">
                    @foreach($banners as $i => $banner)
                        <div class="prestation-banner-slide {{ $i === 0 ? '' : 'hidden' }}">
                            @if($banner->image_url)<img src="{{ $banner->image_url }}" alt="{{ $banner->title ?: __('Nos Prestations') }}" @if($i > 0) loading="lazy" @endif>@endif
                            @if($banner->title || $banner->content || $banner->link_url)
                                <div class="pr-slide-card">
                                    <small>{{ __('Trésors d\'Ivoire') }}</small>
                                    @if($banner->title)<b>{{ $banner->title }}</b>@endif
                                    @if($banner->content)<p>{{ $banner->content }}</p>@endif
                                    @if($banner->link_url)<a href="{{ $banner->link_url }}">{{ $banner->link_label ?: __('Demander un devis') }} →</a>@endif
                                </div>
                            @endif
                        </div>
                    @endforeach
                    @if($banners->count() > 1)
                        <div class="pr-carousel-ui">
                            <div class="prestation-banner-dots">
                                @foreach($banners as $i => $banner)
                                    <button type="button" class="prestation-banner-dot {{ $i === 0 ? 'is-active' : '' }}" aria-label="{{ __('Bannière :n', ['n' => $i + 1]) }}"></button>
                                @endforeach
                            </div>
                            <button type="button" class="prestation-banner-arrow" id="prestation-banner-prev" aria-label="{{ __('Bannière précédente') }}">‹</button>
                            <button type="button" class="prestation-banner-arrow" id="prestation-banner-next" aria-label="{{ __('Bannière suivante') }}">›</button>
                        </div>
                    @endif
                </div>
            @else
                <div class="pr-visual--empty" aria-hidden="true">
                    <div><x-icon name="notes" /><b>{{ __('Reportages') }}</b></div>
                    <div><x-icon name="streets-map-point" /><b>{{ __('Référencement') }}</b></div>
                    <div><x-icon name="calendar" /><b>{{ __('Réservation') }}</b></div>
                    <div><x-icon name="videocamera-record" /><b>{{ __('Vidéo') }}</b></div>
                </div>
            @endif
        </div>
    </div>
</section>

{{-- ══ CHIFFRES ══ --}}
<div class="pr-wrap">
    <div class="pr-stats reveal" aria-label="{{ __('La plateforme en chiffres') }}">
        <div class="pr-stat is-sun"><span class="pr-stat__ic"><x-icon name="shop" /></span><b>{{ $fmtN($stats['providers']) }}</b><span>{{ __('établissements partenaires') }}</span></div>
        <div class="pr-stat is-palm"><span class="pr-stat__ic"><x-icon name="city" /></span><b>{{ $fmtN($stats['cities']) }}</b><span>{{ __('villes et destinations') }}</span></div>
        <div class="pr-stat is-lagoon"><span class="pr-stat__ic"><x-icon name="map-point" /></span><b>{{ $fmtN($stats['sites']) }}</b><span>{{ __('sites touristiques référencés') }}</span></div>
        <div class="pr-stat is-gold"><span class="pr-stat__ic"><x-icon name="book" /></span><b>{{ $fmtN($stats['articles']) }}</b><span>{{ __('articles publiés') }}</span></div>
    </div>
</div>

{{-- ══ PRESTATIONS (administrées) ══ --}}
<section id="prestation-items" class="pr-sec scroll-mt-20">
    <div class="pr-wrap">
        <div class="pr-head reveal">
            <div>
                <p class="pr-label">{{ __('Ce que nous proposons') }}</p>
                <h2 class="pr-h2">{{ __('Nos différentes prestations') }}</h2>
                <p class="pr-sub">{{ __('Des formules pensées pour les hôtels, restaurants, agences, sites culturels et organisateurs d\'événements.') }}</p>
            </div>
            <button type="button" class="pr-btn is-line" onclick="window.openContactModal && window.openContactModal()"><x-icon name="chat-round-dots" />{{ __('Parler à un conseiller') }}</button>
        </div>

        @if($items->isEmpty())
            <p class="pr-empty">{{ __('Aucune prestation publiée pour le moment.') }}</p>
        @else
            <div class="pr-cards">
                @foreach($items as $item)
                    <article class="pr-card reveal">
                        @if($item->image_url)
                            <div class="pr-card__img"><img src="{{ $item->image_url }}" alt="{{ $item->title }}" loading="lazy" decoding="async"></div>
                        @endif
                        <div class="pr-card__body">
                            <div class="pr-card__top">
                                <span class="pr-card__ic">
                                    @if($item->icon && str_contains($item->icon, 'fa-'))<i class="{{ $item->icon }}" aria-hidden="true"></i>@else<x-icon name="star-shine" />@endif
                                </span>
                                <span class="pr-card__num" aria-hidden="true">{{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                            </div>
                            <h3>{{ $item->title }}</h3>
                            @if($item->description)<p>{{ $item->description }}</p>@endif
                            @if($item->link_url)
                                <a href="{{ $item->link_url }}" class="pr-card__link">{{ $item->link_label ?: __('En savoir plus') }} →</a>
                            @else
                                <button type="button" class="pr-card__link" style="background:none;border:0;padding-left:0;cursor:pointer" onclick="window.openContactModal && window.openContactModal()">{{ __('Demander un devis') }} →</button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</section>

{{-- ══ MÉTHODE ══ --}}
<section class="pr-sec" style="padding-top: 0">
    <div class="pr-wrap">
        <div class="pr-head reveal">
            <div>
                <p class="pr-label">{{ __('Comment ça marche') }}</p>
                <h2 class="pr-h2">{{ __('De votre demande à la mise en ligne') }}</h2>
            </div>
        </div>
        <ol class="pr-steps">
            <li class="pr-step reveal"><b class="n">1</b><h3>{{ __('Échange') }}</h3><p>{{ __('Vous nous présentez votre établissement, vos objectifs et votre saison.') }}</p><small>{{ __('Gratuit') }}</small></li>
            <li class="pr-step reveal"><b class="n">2</b><h3>{{ __('Proposition') }}</h3><p>{{ __('Nous vous envoyons une offre détaillée : contenus, formats, calendrier et budget.') }}</p><small>{{ __('Devis sous 48 h') }}</small></li>
            <li class="pr-step reveal"><b class="n">3</b><h3>{{ __('Production') }}</h3><p>{{ __('Reportage, photos, vidéo et fiche optimisée, validés avec vous avant publication.') }}</p><small>{{ __('Vous validez') }}</small></li>
            <li class="pr-step reveal"><b class="n">4</b><h3>{{ __('Diffusion & suivi') }}</h3><p>{{ __('Mise en ligne sur le magazine et l\'annuaire, relais sur nos réseaux, suivi des visites et des demandes.') }}</p><small>{{ __('Tableau de bord') }}</small></li>
        </ol>
    </div>
</section>

{{-- ══ ATOUTS ══ --}}
<section class="pr-sec" style="padding-top: 0">
    <div class="pr-wrap">
        <div class="pr-pillars">
            <div class="pr-pillar-main reveal">
                <div>
                    <p class="pr-label" style="color:#f6c38f">{{ __('Pourquoi Trésors d\'Ivoire') }}</p>
                    <h3>{{ __('Un magazine, un annuaire et une plateforme de réservation, réunis') }}</h3>
                    <p>{{ __('Vos clients découvrent votre établissement dans un contenu éditorial, le retrouvent sur la carte et réservent sans quitter le site.') }}</p>
                </div>
                <ul>
                    <li><x-icon name="verified-check" />{{ __('Badge « Vérifié » pour les établissements contrôlés') }}</li>
                    <li><x-icon name="global" />{{ __('Pages en français et en anglais') }}</li>
                    <li><x-icon name="shield-check" />{{ __('Paiements et réservations sécurisés') }}</li>
                </ul>
            </div>
            <div class="pr-pillar-grid">
                <div class="pr-pillar reveal"><x-icon name="notes" /><b>{{ __('Visibilité éditoriale') }}</b><span>{{ __('Articles, portraits et guides qui donnent envie de venir.') }}</span></div>
                <div class="pr-pillar reveal"><x-icon name="streets-map-point" /><b>{{ __('Référencement local') }}</b><span>{{ __('Fiche complète, carte, itinéraire et recherche « autour de moi ».') }}</span></div>
                <div class="pr-pillar reveal"><x-icon name="calendar" /><b>{{ __('Réservation en ligne') }}</b><span>{{ __('Chambres, tables, activités et billets réservables directement.') }}</span></div>
                <div class="pr-pillar reveal"><x-icon name="videocamera-record" /><b>{{ __('Vidéo & médiathèque') }}</b><span>{{ __('Vos vidéos mises en avant, reliées à vos offres.') }}</span></div>
            </div>
        </div>
    </div>
</section>

{{-- ══ VIDÉO DE PRÉSENTATION ══ --}}
@if($settings->hasVideo())
<section class="pr-sec" style="padding-top: 0">
    <div class="pr-wrap">
        <div class="pr-head reveal"><div><p class="pr-label">{{ __('Découvrir') }}</p><h2 class="pr-h2">{{ __('Notre vidéo de présentation') }}</h2></div></div>
        <div class="pr-video reveal">
            @if($settings->video_source === 'upload')
                <video src="{{ $settings->video_url }}" @if($settings->video_poster_url) poster="{{ $settings->video_poster_url }}" @endif controls preload="none"></video>
            @else
                <iframe src="{{ $settings->video_embed_src }}" title="{{ __('Vidéo de présentation — Nos Prestations') }}"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
            @endif
        </div>
    </div>
</section>
@endif

{{-- ══ CATALOGUE INTERACTIF ══ --}}
@if($settings->catalog_enabled && $settings->catalog_file_url)
<section class="pr-sec" style="padding-top: 0">
    <div class="pr-wrap">
        <div class="pr-head reveal">
            <div>
                <p class="pr-label">{{ __('Catalogue') }}</p>
                <h2 class="pr-h2">{{ $settings->catalog_title ?: __('Notre catalogue de prestations') }}</h2>
            </div>
            <a href="{{ $settings->catalog_file_url }}" target="_blank" rel="noopener" class="pr-btn is-line"><x-icon name="upload" />{{ __('Télécharger le PDF') }}</a>
        </div>

        {{--
            Feuilletage interactif ("page qui tourne") rendu avec PDF.js (Mozilla) +
            page-flip (StPageFlip) — deux librairies open-source chargées depuis leurs
            CDN officiels. Dégradation progressive : le lien de téléchargement reste
            affiché tant que le feuilletage n'a pas fini de s'initialiser, et redevient
            visible si le rendu échoue (PDF illisible, script bloqué, etc.).
        --}}
        <div class="text-center">
            <div id="pdf-flipbook-wrap" class="hidden justify-center">
                <div id="pdf-flipbook" data-pdf-url="{{ $settings->catalog_file_url }}"></div>
            </div>
            <div id="pdf-flipbook-loading" class="df-catalog-frame flex flex-col items-center justify-center gap-3 py-16">
                <i class="fas fa-circle-notch fa-spin text-3xl" style="color:#b9500a"></i>
                <p class="text-sm" style="color:#6b6458">{{ __('Chargement du feuilletage…') }}</p>
            </div>
            <div id="pdf-flipbook-fallback" class="df-catalog-frame hidden flex-col items-center justify-center gap-4 py-16 text-center px-6">
                <x-icon name="book" class="text-4xl" style="color:#b9500a" />
                <p class="max-w-md" style="color:#4a443a">{{ __('Le feuilletage interactif n\'a pas pu se charger. Le catalogue reste disponible en téléchargement.') }}</p>
                <a href="{{ $settings->catalog_file_url }}" target="_blank" rel="noopener" class="pr-btn is-main"><x-icon name="book" />{{ __('Ouvrir le catalogue (PDF)') }}</a>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js"></script>
        <script type="module">
            (async () => {
                const container = document.getElementById('pdf-flipbook');
                const wrap = document.getElementById('pdf-flipbook-wrap');
                const loading = document.getElementById('pdf-flipbook-loading');
                const fallback = document.getElementById('pdf-flipbook-fallback');
                if (!container) return;

                const showFallback = () => {
                    loading?.remove();
                    fallback?.classList.remove('hidden');
                    fallback?.classList.add('flex');
                };

                try {
                    if (typeof St === 'undefined') throw new Error('page-flip non chargé');

                    const pdfjsLib = await import('https://cdnjs.cloudflare.com/ajax/libs/pdf.js/6.2.108/pdf.min.mjs');
                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/6.2.108/pdf.worker.min.mjs';

                    const pdf = await pdfjsLib.getDocument({ url: container.dataset.pdfUrl }).promise;
                    const renderScale = 1.6;
                    const images = [];
                    let baseWidth = 500, baseHeight = 700;

                    for (let i = 1; i <= pdf.numPages; i++) {
                        const page = await pdf.getPage(i);
                        if (i === 1) {
                            const baseViewport = page.getViewport({ scale: 1 });
                            baseWidth = baseViewport.width;
                            baseHeight = baseViewport.height;
                        }
                        const viewport = page.getViewport({ scale: renderScale });
                        const canvas = document.createElement('canvas');
                        canvas.width = viewport.width;
                        canvas.height = viewport.height;
                        await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                        images.push(canvas.toDataURL('image/jpeg', 0.9));
                    }

                    if (!images.length) throw new Error('PDF vide');

                    loading?.remove();
                    wrap.classList.remove('hidden');
                    wrap.classList.add('flex');

                    const pageFlip = new St.PageFlip(container, {
                        width: baseWidth,
                        height: baseHeight,
                        size: 'stretch',
                        minWidth: 280,
                        maxWidth: 1000,
                        minHeight: 360,
                        maxHeight: 1400,
                        showCover: true,
                        mobileScrollSupport: true,
                        maxShadowOpacity: 0.5,
                    });
                    pageFlip.loadFromImages(images);
                } catch (e) {
                    console.error('Feuilletage catalogue : échec du chargement', e);
                    showFallback();
                }
            })();
        </script>
    </div>
</section>
@endif

{{-- ══ APPEL FINAL ══ --}}
<section class="pr-sec" style="padding-top: 0">
    <div class="pr-wrap">
        <div class="pr-cta reveal">
            @include('partials.ivorian-motifs', ['culture' => 'national', 'band' => false])
            <div class="pr-cta__in">
                <h2>{{ __('Prêt à accueillir plus de visiteurs ?') }}</h2>
                <p>{{ __('Parlez-nous de votre projet : nous revenons vers vous avec une proposition adaptée à votre établissement et à votre budget.') }}</p>
            </div>
            <div class="pr-cta__actions pr-cta__in">
                <button type="button" class="pr-btn is-white" onclick="window.openContactModal && window.openContactModal()"><x-icon name="chat-round-dots" />{{ __('Demander un devis') }}</button>
                <a href="{{ route('plans.public') }}" class="pr-btn is-ghost"><x-icon name="star" />{{ __('Voir les abonnements') }}</a>
                <a href="{{ route('register') }}" class="pr-btn is-ghost"><x-icon name="shop" />{{ __('Devenir prestataire') }}</a>
            </div>
        </div>
    </div>
</section>

@include('partials.homepage-footer')

@if($banners->count() > 1)
<script>
    (function () {
        const section = document.getElementById('prestation-banner-carousel');
        const slides = section.querySelectorAll('.prestation-banner-slide');
        const dots = section.querySelectorAll('.prestation-banner-dot');
        const prev = document.getElementById('prestation-banner-prev');
        const next = document.getElementById('prestation-banner-next');
        const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        let current = 0;
        let timer = null;

        function goTo(index) {
            slides[current].classList.add('hidden');
            dots[current].classList.remove('is-active');
            current = (index + slides.length) % slides.length;
            slides[current].classList.remove('hidden');
            dots[current].classList.add('is-active');
        }
        function start() { if (!reduce) timer = setInterval(() => goTo(current + 1), 6000); }
        function stop() { clearInterval(timer); }
        function restart() { stop(); start(); }

        dots.forEach((dot, i) => dot.addEventListener('click', () => { goTo(i); restart(); }));
        prev.addEventListener('click', () => { goTo(current - 1); restart(); });
        next.addEventListener('click', () => { goTo(current + 1); restart(); });
        section.addEventListener('mouseenter', stop);
        section.addEventListener('mouseleave', start);
        section.addEventListener('focusin', stop);
        start();
    })();
</script>
@endif
<script>
    const revealEls = document.querySelectorAll('.reveal');
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                revealObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });
    revealEls.forEach(el => revealObs.observe(el));
</script>
@include('partials.image-protection')
</body>
</html>
