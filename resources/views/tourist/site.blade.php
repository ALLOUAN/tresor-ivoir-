<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $site->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; } .font-serif { font-family: 'Montserrat', serif; } html { scroll-behavior: smooth; } @media (prefers-reduced-motion: reduce) { html { scroll-behavior: auto; } }</style>
    @include('partials.offer-page-theme')
    <style>
        /* Fiche site touristique, charte du site : crème, vert forêt, orange, Playfair. Photo entière sans texte
           posé dessus, bande d'infos clés, onglets qui suivent la lecture, module « Visiter ». Aplats, ombre au survol. */
        .ts { max-width: 1180px; margin: 0 auto; padding: 22px 20px 64px; }
        .ts-crumb { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 8px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: #557466; }
        .ts-crumb a { color: #B85600; font-weight: 600; text-decoration: none; }
        .ts-crumb a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .ts-crumb li:first-child a { display: inline-flex; align-items: center; gap: 7px; height: 36px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; font-weight: 700; }
        .ts-crumb li:first-child a:hover { border-color: #EB6E00; text-decoration: none; }
        .ts-crumb .sep { color: #8AA99B; }
        .ts-crumb [aria-current] { font-weight: 700; color: #0E3B2A; max-width: 260px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .ts-hero { display: grid; grid-template-columns: minmax(0, .95fr) minmax(0, 1.05fr); gap: 34px; align-items: center; margin: 20px 0 24px; }
        .ts-pills { display: flex; flex-wrap: wrap; gap: 8px; }
        .ts-pill { display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 13px; border-radius: 999px; background: #F3F8F5; color: #B85600; font-size: 12px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .ts-pill i, .ts-pill .ico { font-size: 14px; }
        .ts-pill.is-green { background: #F3F8F5; color: #00874F; }
        .ts-title { margin: 14px 0 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 3vw + 18px, 60px); font-weight: 700; line-height: 1.03; letter-spacing: -.015em; color: #0E3B2A; }
        .ts-lead { margin: 14px 0 0; font-family: 'Montserrat', serif; font-style: italic; font-size: 19px; line-height: 1.5; color: #24503D; }
        .ts-meta { display: flex; flex-wrap: wrap; gap: 8px 20px; margin: 16px 0 0; padding: 0; list-style: none; font-size: 14.5px; color: #24503D; }
        .ts-meta li { display: inline-flex; align-items: center; gap: 7px; }
        .ts-meta .ico { color: #EB6E00; font-size: 17px; }
        .ts-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 22px; }
        .ts-btn { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 50px; padding: 0 20px; border-radius: 999px; font-size: 14.5px; font-weight: 700; text-decoration: none; white-space: nowrap; border: 0; cursor: pointer; transition: background .15s ease, border-color .15s ease; }
        .ts-btn .ico { font-size: 18px; }
        .ts-btn.is-main { background: #EB6E00; color: #fff; }
        .ts-btn.is-main:hover { background: #B85600; color: #fff; }
        .ts-btn.is-line { background: #fff; color: #B85600; border: 1.5px solid #EB6E00; }
        .ts-btn.is-line:hover { background: #F3F8F5; }
        .ts-btn:focus-visible, .ts-thumbs button:focus-visible, .ts-photos button:focus-visible, .ts-rel:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

        .ts-cover { position: relative; aspect-ratio: 4 / 3; border-radius: 28px; overflow: hidden; background: #D8E9E0; border: 1px solid #D8E9E0; }
        .ts-cover > img { width: 100%; height: 100%; object-fit: cover; cursor: zoom-in; transition: opacity .3s ease; }
        .ts-cover > img.is-fading { opacity: 0; }
        .ts-car__nav { position: absolute; top: 50%; z-index: 2; width: 42px; height: 42px; margin-top: -21px; border: 0; border-radius: 999px; background: rgba(255,255,255,.92); color: #0E3B2A; display: inline-flex; align-items: center; justify-content: center; font-size: 19px; cursor: pointer; transition: background .15s ease, color .15s ease; }
        .ts-car__nav:hover { background: #EB6E00; color: #fff; }
        .ts-car__nav:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .ts-car__nav.is-prev { left: 12px; } .ts-car__nav.is-next { right: 12px; }
        @media (prefers-reduced-motion: reduce) { .ts-cover > img { transition: none; } }
        .ts-cover__empty { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 64px; }
        .ts-thumbs { position: absolute; left: 12px; right: 12px; bottom: 12px; display: flex; gap: 8px; }
        .ts-thumbs button { width: 64px; height: 48px; padding: 0; border-radius: 12px; overflow: hidden; border: 2px solid #fff; background: #fff; opacity: .75; cursor: pointer; transition: opacity .15s ease, border-color .15s ease; }
        .ts-thumbs button.is-on, .ts-thumbs button:hover { opacity: 1; border-color: #EB6E00; }
        .ts-thumbs img { width: 100%; height: 100%; object-fit: cover; }
        .ts-thumbs .ts-more { display: flex; align-items: center; justify-content: center; opacity: 1; font-size: 13px; font-weight: 800; color: #B85600; }

        .ts-grid { display: grid; grid-template-columns: minmax(0, 1fr) 360px; gap: 40px; align-items: start; margin-top: 28px; }
        .ts-main { display: flex; flex-direction: column; gap: 50px; min-width: 0; }
        .ts-label { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #EB6E00; }
        .ts-h2 { margin: 0 0 16px; font-family: 'Montserrat', serif; font-size: clamp(24px, 1.2vw + 18px, 32px); font-weight: 700; color: #0E3B2A; }
        .ts-text { margin: 0; font-size: 16px; line-height: 1.8; color: #24503D; white-space: pre-line; }
        .ts-facts { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin-top: 22px; }
        .ts-fact { padding: 16px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; }
        .ts-fact .ico { font-size: 20px; color: #EB6E00; }
        .ts-fact b { display: block; margin-top: 8px; font-size: 16px; color: #0E3B2A; }
        .ts-fact small { display: block; font-size: 12px; color: #557466; }

        .ts-photos { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); grid-auto-rows: 170px; gap: 10px; }
        .ts-photos button { position: relative; padding: 0; border: 0; border-radius: 18px; overflow: hidden; background: #D8E9E0; cursor: zoom-in; }
        .ts-photos button:first-child { grid-column: span 2; grid-row: span 2; }
        .ts-photos img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .ts-photos button:hover img { transform: scale(1.04); }
        .ts-video { aspect-ratio: 16 / 9; border-radius: 20px; overflow: hidden; background: #000; }
        .ts-video iframe { width: 100%; height: 100%; border: 0; }
        .ts-cap { margin: 6px 0 0; font-size: 12.5px; color: #557466; }

        .ts-geo { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 14px; }
        .ts-geo div { padding: 14px 16px; border-radius: 16px; background: #fff; border: 1px solid #D8E9E0; }
        .ts-geo small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .ts-geo b { display: block; margin-top: 3px; font-size: 14.5px; font-weight: 600; color: #0E3B2A; overflow-wrap: anywhere; }
        .ts-way { margin-top: 14px; padding: 18px 20px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; display: flex; flex-direction: column; gap: 14px; }
        .ts-way div { display: grid; grid-template-columns: 40px minmax(0, 1fr); gap: 12px; }
        .ts-way .ico { width: 40px; height: 40px; border-radius: 12px; background: #F3F8F5; color: #EB6E00; justify-content: center; font-size: 19px; }
        .ts-way b { display: block; font-size: 14px; color: #0E3B2A; }
        .ts-way p { margin: 3px 0 0; font-size: 14px; line-height: 1.6; color: #24503D; white-space: pre-line; }
        .ts-embed { aspect-ratio: 16 / 8; border-radius: 20px; overflow: hidden; border: 1px solid #D8E9E0; }
        .ts-embed iframe { width: 100%; height: 100%; border: 0; }

        .ts-hours { border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; overflow: hidden; }
        .ts-hours li { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 18px; font-size: 14px; color: #24503D; border-top: 1px solid #F3F8F5; }
        .ts-hours li:first-child { border-top: 0; }
        .ts-hours li.is-today { background: #F3F8F5; font-weight: 700; color: #0E3B2A; }
        .ts-hours li.is-today span:first-child::after { content: attr(data-today); margin-left: 8px; padding: 2px 8px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 10.5px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; }
        .ts-open { padding: 4px 10px; border-radius: 999px; background: #F3F8F5; color: #00874F; font-size: 12.5px; font-weight: 700; font-variant-numeric: tabular-nums; }
        .ts-closed { padding: 4px 10px; border-radius: 999px; background: #fdecea; color: #b42318; font-size: 12.5px; font-weight: 700; }
        .ts-infos { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; margin-top: 16px; }
        .ts-info { display: flex; gap: 12px; padding: 14px 16px; border-radius: 16px; background: #fff; border: 1px solid #D8E9E0; }
        .ts-info > i { flex: none; width: 36px; height: 36px; border-radius: 11px; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 14px; }
        .ts-info small { display: block; font-size: 12px; color: #557466; }
        .ts-info b { display: block; margin-top: 2px; font-size: 14px; font-weight: 600; color: #0E3B2A; }

        .ts-related { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 14px; }
        .ts-rel { display: flex; flex-direction: column; border-radius: 20px; overflow: hidden; background: #fff; border: 1px solid #D8E9E0; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
        .ts-rel:hover { border-color: #8AA99B; box-shadow: 0 16px 30px -24px rgba(14,59,42,.5); }
        .ts-rel__img { position: relative; aspect-ratio: 4 / 3; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 28px; overflow: hidden; }
        .ts-rel__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .ts-rel__img em { position: absolute; top: 8px; right: 8px; padding: 3px 9px; border-radius: 999px; background: #fff; color: #B85600; font-style: normal; font-size: 11px; font-weight: 800; }
        .ts-rel__body { padding: 12px 14px 14px; }
        .ts-rel__body b { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-family: 'Montserrat', serif; font-size: 16px; line-height: 1.25; color: #0E3B2A; }
        .ts-rel__body span { display: inline-flex; align-items: center; gap: 5px; margin-top: 5px; font-size: 12.5px; color: #557466; }
        .ts-rel__body span .ico { color: #EB6E00; font-size: 13px; }
        .ts-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 12px; }
        .ts-more-link { display: inline-flex; align-items: center; gap: 6px; margin-bottom: 18px; font-size: 14px; font-weight: 700; color: #B85600; text-decoration: none; }
        .ts-more-link:hover { text-decoration: underline; text-underline-offset: 3px; }

        /* Module Visiter */
        .ts-card { position: sticky; top: 140px; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; overflow: hidden; scroll-margin-top: 140px; }
        .ts-card__head { padding: 20px 22px 16px; border-bottom: 1px solid #F3F8F5; }
        .ts-card__head small { display: block; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .ts-card__price { margin: 4px 0 0; font-family: 'Montserrat', serif; font-size: 28px; font-weight: 700; color: #0E3B2A; }
        .ts-today { display: inline-flex; align-items: center; gap: 7px; margin-top: 10px; padding: 5px 11px; border-radius: 999px; font-size: 12.5px; font-weight: 700; }
        .ts-today.is-open { background: #F3F8F5; color: #00874F; }
        .ts-today.is-closed { background: #fdecea; color: #b42318; }
        .ts-today .dot { width: 8px; height: 8px; border-radius: 999px; background: currentColor; }
        .ts-card__body { padding: 18px 22px 20px; display: flex; flex-direction: column; gap: 10px; }
        .ts-card__body .ts-btn { width: 100%; }
        .ts-card__list { margin: 6px 0 0; padding: 14px 0 0; list-style: none; border-top: 1px solid #F3F8F5; display: flex; flex-direction: column; gap: 12px; font-size: 14px; color: #24503D; }
        .ts-card__list li { display: flex; align-items: flex-start; gap: 10px; line-height: 1.45; min-width: 0; }
        .ts-card__list .ico { flex: none; color: #EB6E00; font-size: 18px; margin-top: 1px; }
        .ts-card__list a { color: #0E3B2A; text-decoration: none; overflow-wrap: anywhere; }
        .ts-card__list a:hover { color: #B85600; text-decoration: underline; }
        .ts-card__list small { display: block; font-size: 12px; color: #557466; }

        /* Infos pratiques : statut en direct, semaine en barres, « Bon à savoir », actions */
        .pv-top { display: grid; grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr); gap: 14px; }
        .pv-now { display: flex; flex-direction: column; padding: 22px; border-radius: 24px; background: #00874F; color: #fff; }
        .pv-status { align-self: flex-start; display: inline-flex; align-items: center; gap: 8px; height: 30px; padding: 0 12px; border-radius: 999px; background: #fff; font-size: 12.5px; font-weight: 800; }
        .pv-status .dot { width: 9px; height: 9px; border-radius: 999px; background: currentColor; }
        .pv-status.is-open { color: #00874F; }
        .pv-status.is-open .dot { animation: pv-pulse 1.8s ease-out infinite; }
        .pv-status.is-soon { color: #B85600; }
        .pv-status.is-closed { color: #b42318; }
        @keyframes pv-pulse { 0% { box-shadow: 0 0 0 0 rgba(0,135,79,.55); } 100% { box-shadow: 0 0 0 8px rgba(0,135,79,0); } }
        .pv-big { margin: 16px 0 0; font-family: 'Montserrat', serif; font-size: clamp(30px, 1.6vw + 20px, 40px); font-weight: 700; line-height: 1.1; font-variant-numeric: tabular-nums; }
        .pv-sub { margin: 6px 0 0; font-size: 13.5px; color: #D8E9E0; }
        .pv-day { margin-top: auto; padding-top: 22px; }
        .pv-day__track { position: relative; display: block; height: 12px; border-radius: 999px; background: rgba(255,255,255,.16); }
        .pv-day__win { position: absolute; top: 0; bottom: 0; border-radius: 999px; background: #F7C396; }
        .pv-day__now { position: absolute; top: -5px; width: 4px; height: 22px; margin-left: -2px; border-radius: 4px; background: #fff; box-shadow: 0 0 0 3px #00874F; }
        .pv-day__ticks { display: flex; justify-content: space-between; margin-top: 8px; font-size: 11px; color: #D8E9E0; }
        .pv-week { align-self: start; margin: 0; padding: 8px 0; list-style: none; border-radius: 24px; background: #fff; border: 1px solid #D8E9E0; }
        .pv-week li { display: grid; grid-template-columns: minmax(0, 1.1fr) minmax(0, 1fr) auto; align-items: center; gap: 14px; padding: 12px 18px; }
        .pv-week li + li { border-top: 1px solid #F3F8F5; }
        .pv-week li.is-today { background: #F3F8F5; }
        .pv-week__day { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 8px; font-size: 14px; font-weight: 600; color: #0E3B2A; }
        .pv-week__day em { padding: 2px 8px; border-radius: 999px; background: #EB6E00; color: #fff; font-style: normal; font-size: 10.5px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
        .pv-week__bar { position: relative; display: block; height: 8px; border-radius: 999px; background: #F3F8F5; }
        .pv-week__bar span { position: absolute; top: 0; bottom: 0; border-radius: 999px; background: #00874F; }
        .pv-week li.is-today .pv-week__bar span { background: #EB6E00; }
        .pv-week__bar.is-closed { background: #f6dcd8; }
        .pv-week__h { font-size: 13.5px; font-weight: 700; color: #0E3B2A; font-variant-numeric: tabular-nums; white-space: nowrap; }
        .pv-week__h.is-closed { color: #b42318; }
        .pv-h3 { margin: 26px 0 12px; font-size: 13px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; color: #557466; }
        .pv-tips { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 12px; }
        .pv-tip { display: flex; flex-direction: column; gap: 12px; padding: 18px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; }
        .pv-tip__ic { width: 42px; height: 42px; border-radius: 14px; background: #F3F8F5; color: #EB6E00; display: flex; align-items: center; justify-content: center; font-size: 17px; }
        .pv-tip small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .pv-tip b { display: block; margin-top: 4px; font-size: 15px; line-height: 1.4; font-weight: 600; color: #0E3B2A; }
        .pv-tip.is-warn { background: #F3F8F5; border-color: #F7C396; }
        .pv-tip.is-warn .pv-tip__ic { background: #FDEFE3; color: #B85600; }
        .pv-tip.is-money .pv-tip__ic { background: #F3F8F5; color: #00874F; }
        .pv-actions { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 18px; }
        .pv-actions .ts-btn { height: 46px; }

        #lightbox { background: rgba(14,59,42,.94) !important; }

        @media (max-width: 1023px) {
            .ts-hero { grid-template-columns: 1fr; gap: 20px; }
            .ts-cover { order: -1; }
            .ts-grid { grid-template-columns: 1fr; }
            .ts-card { position: static; }
            .ts-related { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 767px) {
            .ts { padding: 16px 16px 120px; }
            .ts-crumb li.is-mid { display: none; }
            .ts-cover { border-radius: 22px; }
            .ts-thumbs button { width: 52px; height: 40px; }
            .ts-lead { font-size: 17px; }
            .ts-meta { flex-direction: column; gap: 8px; }
            .ts-actions .ts-btn { flex: 1 1 auto; }
            .ts-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .ts-photos { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-auto-rows: 130px; }
            .ts-photos button:last-child:nth-child(even) { grid-column: span 2; }
            .ts-geo, .ts-infos { grid-template-columns: minmax(0, 1fr); }
            .ts-embed { aspect-ratio: 4 / 3; }
            .pv-top { grid-template-columns: minmax(0, 1fr); }
            .pv-week li { grid-template-columns: minmax(0, 1fr) auto; gap: 6px 12px; padding: 12px 16px; }
            .pv-week__bar { grid-column: 1 / -1; grid-row: 2; }
            .pv-tips { grid-template-columns: minmax(0, 1fr); }
            .pv-tip { flex-direction: row; align-items: center; }
            .pv-actions .ts-btn { flex: 1 1 auto; }
        }
        @media (prefers-reduced-motion: reduce) { .ts-photos img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="min-h-screen">
    @include('partials.page-background')
    @include('partials.public-top-nav')
@php
    $heroPhoto = $site->photos->first();
    $heroUrl = $heroPhoto?->url ?: $site->thumbnail;
    $cat = $site->category;
    $catColor = $cat?->color ?: '#EB6E00';
    $place = $site->localite ?? $site->departement;
    $keyFacts = array_filter([
        $site->superficie_ha ? ['ruler-angular', __('Superficie'), number_format($site->superficie_ha, 2, ',', ' ').' ha'] : null,
        $site->altitude_m ? ['flag', __('Altitude'), $site->altitude_m.' m'] : null,
        $site->distance_centre_km ? ['route', __('Distance centre'), $site->distance_centre_km.' km'] : null,
        $site->entrance_fee ? ['ticket', __('Entrée'), $site->entrance_fee] : null,
    ]);
    // Horaires : comprend « Lundi », « Lundi au Samedi », « Lun - Ven », « Tous les jours »… (heure d'Abidjan, UTC+0).
    $dayNames = ['lundi' => 1, 'lun' => 1, 'mardi' => 2, 'mar' => 2, 'mercredi' => 3, 'mer' => 3, 'jeudi' => 4, 'jeu' => 4, 'vendredi' => 5, 'ven' => 5, 'samedi' => 6, 'sam' => 6, 'dimanche' => 7, 'dim' => 7,
        'monday' => 1, 'tuesday' => 2, 'wednesday' => 3, 'thursday' => 4, 'friday' => 5, 'saturday' => 6, 'sunday' => 7];
    $parseDays = function (string $label) use ($dayNames): array {
        $l = mb_strtolower(trim($label));
        if (preg_match('/tous les jours|7\s*j|every ?day|daily/u', $l)) return range(1, 7);
        preg_match_all('/\b(' . implode('|', array_keys($dayNames)) . ')\b/u', $l, $m);
        $found = array_map(fn ($d) => $dayNames[$d], $m[1]);
        if (count($found) === 2 && preg_match('/\b(au|à|a|to)\b|[-–]/u', $l)) {
            [$a, $b] = $found;
            return $a <= $b ? range($a, $b) : array_merge(range($a, 7), range(1, $b));
        }
        return array_values(array_unique($found));
    };
    $toMin = fn ($t) => preg_match('/^(\d{1,2})[:hH](\d{2})?/', (string) $t, $mm) ? ((int) $mm[1]) * 60 + (int) ($mm[2] ?? 0) : null;
    $todayIdx = now('Africa/Abidjan')->isoWeekday();
    $schedRows = collect($site->schedules ?? [])->map(function ($s) use ($parseDays, $toMin, $todayIdx) {
        $o = $toMin($s['opens'] ?? null); $c = $toMin($s['closes'] ?? null);
        if ($o !== null && $c !== null && $c <= $o) $c = 1440;
        $days = $parseDays((string) ($s['day'] ?? ''));
        return $s + ['o' => $o, 'c' => $c, 'is_today' => in_array($todayIdx, $days, true)];
    });
    $todaySchedule = $schedRows->firstWhere('is_today', true);
    $hhmm = fn (int $m) => sprintf('%02d:%02d', intdiv($m, 60) % 24, $m % 60);
    $pvLabels = ['open' => __('Ouvert maintenant'), 'soon' => __('Ferme bientôt'), 'before' => __('Fermé pour le moment'), 'after' => __("Fermé pour aujourd'hui"), 'closes' => __('Ferme à :h'), 'opens' => __('Ouvre à :h'), 'reopen' => __('Réouverture demain')];
    $hasGallery = $site->photos->count() > 1 || $site->videos->isNotEmpty();
    $hasGeo = $site->map_embed_url || ($site->latitude && $site->longitude) || $site->departement || $site->sous_prefecture || $site->localite || $site->point_repere || $site->acces_description;
    $hasPractical = ! empty($site->practical_info) || ! empty($site->schedules);
@endphp
<main class="ts">
    <ol class="ts-crumb" aria-label="{{ __('Fil d\'Ariane') }}">
        <li><a href="{{ route('tourist.city', $site->city->slug) }}"><i class="fas fa-arrow-left" aria-hidden="true"></i>{{ $site->city->name }}</a></li>
        <li class="sep is-mid" aria-hidden="true">/</li>
        <li class="is-mid"><a href="{{ route('tourist.cities') }}">{{ __('Régions') }}</a></li>
        @if($cat)
            <li class="sep is-mid" aria-hidden="true">/</li>
            <li class="is-mid"><a href="{{ route('tourist.category', [$site->city->slug, $cat->slug]) }}">{{ $cat->name }}</a></li>
        @endif
        <li class="sep" aria-hidden="true">/</li>
        <li aria-current="page">{{ $site->name }}</li>
    </ol>

    {{-- ══ EN-TÊTE ══ --}}
    <header class="ts-hero">
        <div class="min-w-0">
            <div class="ts-pills">
                @if($cat)<span class="ts-pill"><i class="{{ $cat->icon ?: 'fas fa-tag' }}" aria-hidden="true"></i>{{ $cat->name }}</span>@endif
                @if($site->is_featured)<span class="ts-pill is-green"><x-icon name="star" />{{ __('Site vedette') }}</span>@endif
            </div>
            <h1 class="ts-title">{{ $site->name }}</h1>
            @if($site->short_description)<p class="ts-lead">{{ $site->localized('short_description') }}</p>@endif
            <ul class="ts-meta">
                <li><x-icon name="city" />{{ $site->city->name }}@if($place) · {{ $place }}@endif</li>
                @if($site->distance_centre_km)<li><x-icon name="route" />{{ $site->distance_centre_km }} {{ __('km du centre') }}</li>@endif
                <li><x-icon name="eye" />{{ number_format($site->views_count, 0, ',', ' ') }} {{ __('vues') }}</li>
            </ul>
            <div class="ts-actions">
                @if($site->latitude && $site->longitude)
                    <a href="{{ route('directions.go', ['site', $site->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer" class="ts-btn is-main"><x-icon name="streets-navigation" />{{ __('Itinéraire') }}</a>
                @endif
                @if($hasGallery)<a href="#galerie" class="ts-btn is-line"><x-icon name="gallery" />{{ __('Photos') }}@if($site->photos->count() > 1) ({{ $site->photos->count() }})@endif</a>@endif
                @if($hasPractical)<a href="#pratique" class="ts-btn is-line"><x-icon name="clock-circle" />{{ __('Horaires & infos') }}</a>@endif
            </div>
        </div>
        <div class="ts-cover">
            @if($heroUrl)
                <img id="mainPhoto" src="{{ $heroUrl }}" alt="{{ $heroPhoto?->alt_text ?: $site->name }}" decoding="async" fetchpriority="high" onclick="openLightbox(this.currentSrc || this.src, @js($site->name))">
            @else
                <span class="ts-cover__empty" style="background: {{ $catColor }}1a; color: {{ $catColor }}"><i class="{{ $cat?->icon ?: 'fas fa-landmark' }}" aria-hidden="true"></i></span>
            @endif
            @if($site->photos->count() > 1)
                {{-- Carrousel : la photo principale défile automatiquement (3 s) sur toutes les photos du site. --}}
                <button type="button" class="ts-car__nav is-prev" data-ts-prev aria-label="{{ __('Photo précédente') }}"><x-icon name="alt-arrow-left" /></button>
                <button type="button" class="ts-car__nav is-next" data-ts-next aria-label="{{ __('Photo suivante') }}"><x-icon name="alt-arrow-right" /></button>
                <script type="application/json" id="ts-car-photos">@json($site->photos->pluck('url')->values())</script>
                <div class="ts-thumbs">
                    @foreach($site->photos->take(4) as $photo)
                        <button type="button" onclick="changeHeroPhoto(@js($photo->url))" data-photo="{{ $photo->url }}" class="{{ $loop->first ? 'is-on' : '' }}" aria-label="{{ __('Photo :n', ['n' => $loop->iteration]) }}"><img src="{{ $photo->url }}" alt=""></button>
                    @endforeach
                    @if($site->photos->count() > 4)<button type="button" class="ts-more" onclick="scrollToGallery()">+{{ $site->photos->count() - 4 }}</button>@endif
                </div>
            @endif
        </div>
    </header>

    {{-- ══ INFOS CLÉS ══ --}}
    <ul class="ox-keys">
        <li><x-icon name="map-point" /><span><small>{{ __('Lieu') }}</small><b>{{ $place ?: $site->city->name }}</b></span></li>
        @if($cat)<li><x-icon name="widget" /><span><small>{{ __('Catégorie') }}</small><b>{{ $cat->name }}</b></span></li>@endif
        <li><x-icon name="ticket" /><span><small>{{ __('Entrée') }}</small><b>{{ $site->entrance_fee ?: __('Non précisé') }}</b></span></li>
        @if($todaySchedule)
            <li class="{{ ($todaySchedule['closed'] ?? false) ? '' : 'is-ok' }}"><x-icon name="clock-circle" /><span><small>{{ __('Aujourd\'hui') }}</small><b>{{ ($todaySchedule['closed'] ?? false) ? __('Fermé') : ($todaySchedule['opens'] ?? '').'–'.($todaySchedule['closes'] ?? '') }}</b></span></li>
        @endif
    </ul>

    <nav class="ox-tabs" data-ox-tabs aria-label="{{ __('Sections de la page') }}">
        <a href="#presentation">{{ __('Présentation') }}</a>
        @if($hasGallery)<a href="#galerie">{{ __('Galerie') }}</a>@endif
        @if($hasGeo)<a href="#acces">{{ __('Accès') }}</a>@endif
        @if($hasPractical)<a href="#pratique">{{ __('Infos pratiques') }}</a>@endif
        @if($related->isNotEmpty())<a href="#a-voir-aussi">{{ __('À voir aussi') }}</a>@endif
    </nav>

    <div class="ts-grid">
        <div class="ts-main">
            {{-- Présentation --}}
            <section class="ox-sec" id="presentation">
                <p class="ts-label">{{ __('Présentation') }}</p>
                <h2 class="ts-h2">{{ __('À découvrir') }}</h2>
                @if($site->description)
                    <p class="ts-text">{{ $site->localized('description') }}</p>
                @elseif($site->short_description)
                    <p class="ts-text">{{ $site->localized('short_description') }}</p>
                @endif
                @if(! empty($keyFacts))
                    <div class="ts-facts">
                        @foreach($keyFacts as [$icon, $label, $val])
                            <div class="ts-fact"><x-icon :name="$icon" /><b>{{ $val }}</b><small>{{ $label }}</small></div>
                        @endforeach
                    </div>
                @endif
            </section>

            {{-- Galerie --}}
            @if($hasGallery)
                <section class="ox-sec" id="galerie">
                    <p class="ts-label">{{ __('Galerie') }}</p>
                    <h2 class="ts-h2">{{ __('Photos') }}@if($site->photos->count() > 1) <span class="font-sans text-base font-normal text-[#557466]">({{ $site->photos->count() }})</span>@endif</h2>
                    @if($site->photos->count() > 1)
                        <div class="ts-photos">
                            @foreach($site->photos as $photo)
                                <button type="button" onclick="openLightbox(@js($photo->url), @js($photo->caption ?? $site->name))" aria-label="{{ __('Agrandir la photo :n', ['n' => $loop->iteration]) }}">
                                    <img src="{{ $photo->url }}" alt="{{ $photo->alt_text ?: $site->name }}" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                    @if($site->videos->isNotEmpty())
                        <h3 class="mt-6 mb-3 flex items-center gap-2 text-lg font-bold text-[#0E3B2A]"><x-icon name="play-circle" class="text-[#EB6E00]" />{{ __('Vidéos') }}</h3>
                        @foreach($site->videos as $video)
                            <div class="ts-video {{ ! $loop->first ? 'mt-4' : '' }}"><iframe src="{{ $video->url }}" allow="autoplay; encrypted-media" allowfullscreen loading="lazy" title="{{ $video->caption ?: $site->name }}"></iframe></div>
                            @if($video->caption)<p class="ts-cap">{{ $video->caption }}</p>@endif
                        @endforeach
                    @endif
                </section>
            @endif

            {{-- Accès --}}
            @if($hasGeo)
                <section class="ox-sec" id="acces">
                    <p class="ts-label">{{ __('Accès') }}</p>
                    <h2 class="ts-h2">{{ __('Situation géographique') }}</h2>
                    @if($site->latitude && $site->longitude)
                        @include('partials.route-map', [
                            'type' => 'site', 'slug' => $site->slug,
                            'lat' => $site->latitude, 'lng' => $site->longitude, 'name' => $site->name,
                            'accent' => '#EB6E00', 'height' => 'clamp(340px, 55vh, 520px)', 'frameClass' => 'rounded-2xl overflow-hidden',
                        ])
                    @elseif($site->map_embed_url)
                        <div class="ts-embed"><iframe src="{{ $site->map_embed_url }}" allowfullscreen loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="{{ __('Carte') }}"></iframe></div>
                    @endif
                    @if($site->departement || $site->sous_prefecture || $site->localite || ($site->latitude && $site->longitude))
                        <div class="ts-geo">
                            @if($site->departement)<div><small>{{ __('Département') }}</small><b>{{ $site->departement }}</b></div>@endif
                            @if($site->sous_prefecture)<div><small>{{ __('Sous-préfecture') }}</small><b>{{ $site->sous_prefecture }}</b></div>@endif
                            @if($site->localite)<div><small>{{ __('Localité') }}</small><b>{{ $site->localite }}</b></div>@endif
                            @if($site->latitude && $site->longitude)<div><small>{{ __('Coordonnées GPS') }}</small><b class="font-mono text-sm">{{ $site->latitude }}, {{ $site->longitude }}</b></div>@endif
                        </div>
                    @endif
                    @if($site->point_repere || $site->acces_description)
                        <div class="ts-way">
                            @if($site->point_repere)<div><x-icon name="point-on-map" /><span><b>{{ __('Point de repère') }}</b><p>{{ $site->point_repere }}</p></span></div>@endif
                            @if($site->acces_description)<div><x-icon name="routing" /><span><b>{{ __('Comment s\'y rendre') }}</b><p>{{ $site->acces_description }}</p></span></div>@endif
                        </div>
                    @endif
                </section>
            @endif

            {{-- Infos pratiques --}}
            @if($hasPractical)
                <section class="ox-sec" id="pratique">
                    <p class="ts-label">{{ __('Préparer sa visite') }}</p>
                    <h2 class="ts-h2">{{ __('Infos pratiques') }}</h2>

                    @if($schedRows->isNotEmpty())
                        <div class="pv-top">
                            {{-- Statut en direct (heure d'Abidjan) --}}
                            <div class="pv-now" data-pv
                                 @if($todaySchedule && ! ($todaySchedule['closed'] ?? false) && $todaySchedule['o'] !== null) data-open="{{ $todaySchedule['o'] }}" data-close="{{ $todaySchedule['c'] }}" @endif>
                                <span class="pv-status is-{{ $todaySchedule && ! ($todaySchedule['closed'] ?? false) ? 'open' : 'closed' }}" data-pv-status>
                                    <span class="dot" aria-hidden="true"></span><span data-pv-label>{{ $todaySchedule && ! ($todaySchedule['closed'] ?? false) ? __('Ouvert aujourd\'hui') : __('Fermé aujourd\'hui') }}</span>
                                </span>
                                <p class="pv-big" data-pv-big>
                                    @if($todaySchedule && ! ($todaySchedule['closed'] ?? false) && $todaySchedule['o'] !== null)
                                        {{ $hhmm($todaySchedule['o']) }} – {{ $hhmm($todaySchedule['c']) }}
                                    @else
                                        {{ __('Repos') }}
                                    @endif
                                </p>
                                <p class="pv-sub" data-pv-sub>{{ __('Horaires d\'aujourd\'hui') }} · {{ ucfirst(now('Africa/Abidjan')->locale(app()->getLocale())->dayName) }}</p>
                                @if($todaySchedule && ! ($todaySchedule['closed'] ?? false) && $todaySchedule['o'] !== null)
                                    <div class="pv-day" aria-hidden="true">
                                        <span class="pv-day__track"><span class="pv-day__win" style="left: {{ round($todaySchedule['o'] / 14.4, 2) }}%; width: {{ round(($todaySchedule['c'] - $todaySchedule['o']) / 14.4, 2) }}%"></span><span class="pv-day__now" data-pv-now hidden></span></span>
                                        <span class="pv-day__ticks"><span>0h</span><span>6h</span><span>12h</span><span>18h</span><span>24h</span></span>
                                    </div>
                                @endif
                            </div>

                            {{-- Semaine --}}
                            <ul class="pv-week" aria-label="{{ __('Horaires d\'ouverture') }}">
                                @foreach($schedRows as $s)
                                    <li class="{{ $s['is_today'] ? 'is-today' : '' }}">
                                        <span class="pv-week__day">{{ $s['day'] }}@if($s['is_today'])<em>{{ __('Aujourd\'hui') }}</em>@endif</span>
                                        @if(($s['closed'] ?? false) || $s['o'] === null)
                                            <span class="pv-week__bar is-closed" aria-hidden="true"></span>
                                            <span class="pv-week__h is-closed">{{ ($s['closed'] ?? false) ? __('Fermé') : (($s['opens'] ?? '—').' – '.($s['closes'] ?? '—')) }}</span>
                                        @else
                                            <span class="pv-week__bar" aria-hidden="true"><span style="left: {{ round($s['o'] / 14.4, 2) }}%; width: {{ round(($s['c'] - $s['o']) / 14.4, 2) }}%"></span></span>
                                            <span class="pv-week__h">{{ $hhmm($s['o']) }} – {{ $hhmm($s['c']) }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if(! empty($site->practical_info))
                        <h3 class="pv-h3">{{ __('Bon à savoir') }}</h3>
                        <div class="pv-tips">
                            @foreach($site->practical_info as $info)
                                @php
                                    $lbl = mb_strtolower((string) ($info['label'] ?? ''));
                                    $tone = preg_match('/sécurité|securite|attention|prudence|vigilan/u', $lbl.' '.mb_strtolower((string) ($info['value'] ?? ''))) ? 'warn' : (preg_match('/paiement|prix|tarif|argent|monnaie/u', $lbl) ? 'money' : 'base');
                                    $hay = $lbl.' '.mb_strtolower((string) ($info['value'] ?? ''));
                                    $tipIcon = collect(['wallet-money' => '/paiement|espèces|carte|prix|tarif|monnaie/u', 'shield-check' => '/sécurité|securite|vigilan|prudence|attention/u', 'plate' => '/maquis|restaurant|manger|repas|cuisine/u', 'car' => '/parking|voiture|stationnement/u', 'clock-circle' => '/horaire|heure|affluence|moment/u', 'camera' => '/photo/u', 'users-group-rounded' => '/guide|visite guidée|groupe/u', 'accessibility' => '/accessib|handicap|pmr/u'])->search(fn ($re) => preg_match($re, $hay)) ?: 'info-circle';
                                @endphp
                                <div class="pv-tip is-{{ $tone }}">
                                    <span class="pv-tip__ic"><x-icon :name="$tipIcon" /></span>
                                    <span class="min-w-0"><small>{{ $info['label'] }}</small><b>{{ $info['value'] }}</b></span>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="pv-actions">
                        @if($site->latitude && $site->longitude)
                            <a href="{{ route('directions.go', ['site', $site->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer" class="ts-btn is-main"><x-icon name="streets-navigation" />{{ __('Y aller') }}</a>
                        @endif
                        @if($site->phone)<a href="tel:{{ $site->phone }}" class="ts-btn is-line"><x-icon name="chat-round-dots" />{{ __('Appeler') }}</a>@endif
                        <button type="button" class="ts-btn is-line" data-pv-share data-done="{{ __('Lien copié') }}"><x-icon name="share" /><span>{{ __('Partager') }}</span></button>
                    </div>
                </section>
            @endif

            {{-- À voir aussi --}}
            @if($related->isNotEmpty())
                <section class="ox-sec" id="a-voir-aussi">
                    <div class="ts-head">
                        <div><p class="ts-label">{{ __('Dans le même thème') }}</p><h2 class="ts-h2">{{ __('À voir aussi') }}</h2></div>
                        @if($cat)<a href="{{ route('tourist.category', [$site->city->slug, $cat->slug]) }}" class="ts-more-link">{{ __('Voir tout') }}<x-icon name="alt-arrow-right" /></a>@endif
                    </div>
                    <div class="ts-related">
                        @foreach($related as $r)
                            @php $rImg = $r->media->first()?->url ?: $r->thumbnail; @endphp
                            <a href="{{ route('tourist.site', $r->slug) }}" class="ts-rel">
                                <span class="ts-rel__img">
                                    <i class="{{ $cat?->icon ?: 'fas fa-landmark' }}" aria-hidden="true"></i>
                                    @if($rImg)<img src="{{ $rImg }}" alt="{{ $r->name }}" loading="lazy" decoding="async" onerror="this.remove()">@endif
                                    @if($r->entrance_fee)<em>{{ $r->entrance_fee }}</em>@endif
                                </span>
                                <span class="ts-rel__body">
                                    <b>{{ $r->name }}</b>
                                    @if($r->localite || $r->departement)<span><x-icon name="map-point" />{{ $r->localite ?? $r->departement }}</span>@endif
                                </span>
                            </a>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>

        {{-- ══ MODULE VISITER ══ --}}
        <aside class="ts-card" id="reserver" aria-label="{{ __('Visiter') }}">
            <div class="ts-card__head">
                <small>{{ __('Entrée') }}</small>
                <p class="ts-card__price">{{ $site->entrance_fee ?: __('Non précisé') }}</p>
                @if($todaySchedule)
                    @if($todaySchedule['closed'] ?? false)
                        <span class="ts-today is-closed"><span class="dot"></span>{{ __('Fermé aujourd\'hui') }}</span>
                    @else
                        <span class="ts-today is-open"><span class="dot"></span>{{ __('Aujourd\'hui') }} · {{ $todaySchedule['o'] !== null ? $hhmm($todaySchedule['o']).'–'.$hhmm($todaySchedule['c']) : ($todaySchedule['opens'] ?? '').'–'.($todaySchedule['closes'] ?? '') }}</span>
                    @endif
                @endif
            </div>
            <div class="ts-card__body">
                @if($site->latitude && $site->longitude)
                    <a href="{{ route('directions.go', ['site', $site->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer" class="ts-btn is-main"><x-icon name="streets-navigation" />{{ __('Afficher l\'itinéraire') }}</a>
                @elseif($site->map_embed_url)
                    <a href="{{ $site->map_embed_url }}" target="_blank" rel="noopener noreferrer" class="ts-btn is-main"><x-icon name="streets-navigation" />{{ __('Afficher l\'itinéraire') }}</a>
                @endif
                @if($site->website)
                    <a href="{{ $site->website }}" target="_blank" rel="noopener" class="ts-btn is-line"><x-icon name="global" />{{ __('Site officiel') }}</a>
                @endif
                <ul class="ts-card__list">
                    <li><x-icon name="map-point" /><span>{{ $site->localite ?: $site->city->name }}@if($site->departement)<small>{{ $site->departement }}@if($site->localite) · {{ $site->city->name }}@endif</small>@endif</span></li>
                    @if($site->phone)<li><x-icon name="chat-round-dots" /><a href="tel:{{ $site->phone }}">{{ $site->phone }}</a></li>@endif
                    @if($site->email)<li><x-icon name="notes" /><a href="mailto:{{ $site->email }}">{{ $site->email }}</a></li>@endif
                    @if($cat)<li><i class="{{ $cat->icon ?: 'fas fa-tag' }} text-[#EB6E00] text-center" style="width:18px;margin-top:4px" aria-hidden="true"></i><span>{{ $cat->name }}<small>{{ $site->city->name }}</small></span></li>@endif
                </ul>
            </div>
        </aside>
    </div>
</main>

{{-- Barre mobile --}}
<div class="ox-bar" data-ox-bar>
    <span><small>{{ __('Entrée') }}</small><b>{{ \Illuminate\Support\Str::limit($site->entrance_fee ?: __('Non précisé'), 22) }}</b></span>
    @if($site->latitude && $site->longitude)
        <a href="{{ route('directions.go', ['site', $site->slug]) }}" data-in-page-route target="_blank" rel="noopener noreferrer"><x-icon name="streets-navigation" />{{ __('Itinéraire') }}</a>
    @else
        <a href="#reserver"><x-icon name="info-circle" />{{ __('Infos') }}</a>
    @endif
</div>

{{-- Visionneuse --}}
<div id="lightbox" class="fixed inset-0 z-50 hidden items-center justify-center p-4" onclick="closeLightbox()">
    <button class="absolute top-4 right-4 w-11 h-11 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
    <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[88vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    <p id="lightbox-caption" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-white/70 text-sm text-center px-4"></p>
</div>

@include('partials.sources-list', ['sources' => $site->sources ?? []])
@include('partials.homepage-footer')

<script>
// ── Photo principale ──
function changeHeroPhoto(url) {
    const img = document.getElementById('mainPhoto');
    // Les images reçoivent un srcset optimisé côté serveur : c'est lui que le navigateur affiche, pas src.
    // On le retire avant de changer de photo, sinon l'image visible ne change jamais.
    if (img) { img.removeAttribute('srcset'); img.removeAttribute('sizes'); img.src = url; }
    document.querySelectorAll('[data-photo]').forEach(btn => btn.classList.toggle('is-on', btn.dataset.photo === url));
}
// ── Carrousel de la photo principale : défilement auto rapide (3 s, fondu bref), flèches, balayage,
//    pause seulement quand l'onglet est caché ; désactivé si l'utilisateur limite les animations. ──
(function () {
    var data = document.getElementById('ts-car-photos');
    var img = document.getElementById('mainPhoto');
    if (!data || !img) return;
    var photos = []; try { photos = JSON.parse(data.textContent) || []; } catch (e) {}
    if (photos.length < 2) return;
    var cover = img.closest('.ts-cover'), i = 0, timer = null;
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    photos.forEach(function (u) { var p = new Image(); p.src = u; });   // préchargement : pas de flash au changement
    function show(n) {
        i = (n + photos.length) % photos.length;
        img.classList.add('is-fading');
        setTimeout(function () { changeHeroPhoto(photos[i]); img.classList.remove('is-fading'); }, reduce ? 0 : 180);
    }
    function play() { stop(); if (!reduce) timer = setInterval(function () { if (!document.hidden) show(i + 1); }, 3000); }
    function stop() { if (timer) clearInterval(timer); timer = null; }
    cover.querySelector('[data-ts-prev]').addEventListener('click', function () { show(i - 1); play(); });
    cover.querySelector('[data-ts-next]').addEventListener('click', function () { show(i + 1); play(); });
    cover.querySelectorAll('[data-photo]').forEach(function (b) { b.addEventListener('click', function () { i = Math.max(0, photos.indexOf(b.dataset.photo)); play(); }); });
    var x0 = null;
    cover.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
    cover.addEventListener('touchend', function (e) { if (x0 === null) return; var dx = e.changedTouches[0].clientX - x0; if (Math.abs(dx) > 40) show(i + (dx < 0 ? 1 : -1)); x0 = null; play(); });
    play();
})();
// ── Vers la galerie ──
function scrollToGallery() {
    document.getElementById('galerie')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}
// ── Visionneuse ──
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

// ── Statut en direct (Côte d'Ivoire = UTC+0) ──
(function () {
    const box = document.querySelector('[data-pv]');
    if (!box || box.dataset.open === undefined) return;
    const open = +box.dataset.open, close = +box.dataset.close;
    const status = box.querySelector('[data-pv-status]'), label = box.querySelector('[data-pv-label]'), sub = box.querySelector('[data-pv-sub]'), now = box.querySelector('[data-pv-now]');
    const T = @json($pvLabels);
    const fmt = m => String(Math.floor(m / 60) % 24).padStart(2, '0') + ':' + String(m % 60).padStart(2, '0');
    function tick() {
        const d = new Date(), m = d.getUTCHours() * 60 + d.getUTCMinutes();
        let k, s;
        if (m < open) { k = 'closed'; label.textContent = T.before; s = T.opens.replace(':h', fmt(open)); }
        else if (m >= close) { k = 'closed'; label.textContent = T.after; s = T.reopen; }
        else if (close - m <= 60) { k = 'soon'; label.textContent = T.soon; s = T.closes.replace(':h', fmt(close)); }
        else { k = 'open'; label.textContent = T.open; s = T.closes.replace(':h', fmt(close)); }
        status.className = 'pv-status is-' + k;
        sub.textContent = s;
        if (now) { now.hidden = false; now.style.left = (m / 14.4) + '%'; }
    }
    tick(); setInterval(tick, 60000);
})();

// ── Partager ──
document.querySelector('[data-pv-share]')?.addEventListener('click', async e => {
    const b = e.currentTarget, data = { title: document.title, url: location.href.split('#')[0] };
    try {
        if (navigator.share) { await navigator.share(data); return; }
        await navigator.clipboard.writeText(data.url);
        b.querySelector('span').textContent = b.dataset.done;
    } catch (err) {}
});
</script>
</body>
</html>
