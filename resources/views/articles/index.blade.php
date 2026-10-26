<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Articles') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
        <style>
        body { font-family: 'Montserrat', sans-serif; }
        .font-serif   { font-family: 'Montserrat', Georgia, serif; }
        .font-elegant { font-family: 'Montserrat', Georgia, serif; }
        .article-card:hover .card-img { transform: scale(1.05); }
        .card-img { transition: transform .5s ease; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .agenda-panel {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            background:
                linear-gradient(rgba(85,116,102, 0.96), rgba(85,116,102, 0.96)),
                linear-gradient(transparent, transparent);
            box-shadow: 0 24px 52px rgba(14,59,42,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
            overflow: hidden;
        }
        .agenda-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(247,195,150, 0.03);
            pointer-events: none;
        }
        .agenda-card {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            background: rgba(14,59,42, 0.96);
            box-shadow: 0 12px 30px rgba(14,59,42,0.28);
            transition: transform .26s cubic-bezier(0.2, 0.8, 0.2, 1), border-color .25s ease, box-shadow .26s ease;
            overflow: hidden;
        }
        .agenda-card:hover {
            transform: translateY(-4px);
            border-color: rgba(235,110,0,0.45);
            box-shadow: 0 18px 42px rgba(14,59,42,0.4), 0 0 22px rgba(235,110,0,0.14);
        }
        .agenda-card-cover {
            position: relative;
            overflow: hidden;
            background: #0E3B2A;
        }
        .agenda-card-cover::after {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(14,59,42, 0.32);
            pointer-events: none;
        }
        .agenda-card:hover .agenda-card-cover img { transform: scale(1.07); }
        .agenda-card-cover img { transition: transform .5s ease; }
        .articles-hero {
            position: relative;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background-image:
                linear-gradient(rgba(255, 255, 255, 0.95), rgba(255, 255, 255, 0.95)),
                linear-gradient(transparent, transparent),
                linear-gradient(transparent, transparent),
                var(--articles-hero-bg-image, none);
            background-size: auto, auto, auto, cover;
            background-position: 0 0, 0 0, 0 0, center;
            background-repeat: repeat, no-repeat, no-repeat, no-repeat;
            overflow: hidden;
        }
        /* Image de fond configurable (back-office) : voile blanchi allégé pour
           laisser l'image transparaître (même traitement que les autres pages). */
        /* Image configurée : couleurs vraies, sans voile — la lisibilité du texte
           est déjà assurée par le cadre bg-green-950/70 qui l'entoure. */
        .articles-hero[style*="--articles-hero-bg-image"] {
            background-image: var(--articles-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .articles-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(rgba(255, 255, 255, 0.02), rgba(255, 255, 255, 0.02)),
                linear-gradient(rgba(235,110,0, 0.01), rgba(235,110,0, 0.01));
            pointer-events: none;
            opacity: .8;
        }
        .articles-search-shell {
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(12px) saturate(125%);
            box-shadow: 0 10px 26px rgba(14,59,42,0.25), inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .articles-search-input {
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(216,233,224, 0.88);
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .articles-search-input:focus {
            border-color: rgba(235,110,0,0.5);
            box-shadow: 0 0 0 3px rgba(235,110,0,0.14);
        }
        .articles-search-btn {
            background: #EB6E00;
            box-shadow: 0 8px 20px rgba(235,110,0,0.28);
            transition: transform .2s ease, filter .2s ease, box-shadow .2s ease;
        }
        .articles-search-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 12px 26px rgba(235,110,0,0.35);
        }
        .category-chip {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(255, 255, 255, 0.04);
            color: rgba(203, 213, 225, 0.9);
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .category-chip::before {
            content: '';
            position: absolute;
            inset: 0;
            background: rgba(255, 255, 255, 0.05);
            transform: translateX(-130%);
            transition: transform .45s ease;
            pointer-events: none;
        }
        .category-chip::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            border-radius: 2px;
            background: #EB6E00;
            transition: width .24s ease;
            box-shadow: 0 0 10px rgba(235,110,0,0.45);
        }
        .category-chip:hover {
            transform: translateY(-1px);
            color: #ffffff;
            border-color: rgba(235,110,0,0.3);
            background: rgba(255, 255, 255, 0.07);
            box-shadow: 0 0 14px rgba(235,110,0,0.14);
        }
        .category-chip:hover::before {
            transform: translateX(130%);
        }
        .category-chip:hover::after {
            width: 72%;
        }
        .category-chip.is-active {
            border-color: rgba(235,110,0,0.52);
            background: #EB6E00;
            color: #0E3B2A;
            box-shadow: 0 8px 20px rgba(235,110,0,0.3);
        }
        .category-chip.is-active::after {
            width: 76%;
            background: rgba(14,59,42,0.8);
            box-shadow: none;
        }
        .categories-strip {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 9999px;
            background: rgba(14,59,42,0.38);
            backdrop-filter: blur(10px) saturate(120%);
            padding: 0.45rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .categories-strip::-webkit-scrollbar {
            display: none;
        }
        .categories-strip {
            -webkit-mask-image: none;
                    mask-image: none;
        }
        .categories-nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 5;
            width: 34px; height: 34px;
            border-radius: 999px;
            display: inline-flex; align-items: center; justify-content: center;
            background: rgba(14,59,42,0.9);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.16);
            box-shadow: 0 8px 18px rgba(14,59,42,0.4);
            cursor: pointer;
            font-size: 12px;
            transition: background .2s ease, transform .2s ease, opacity .2s ease;
        }
        .categories-nav-arrow:hover { background: #EB6E00; transform: translateY(-50%) scale(1.06); }
        .categories-nav-arrow--left { left: -4px; }
        .categories-nav-arrow--right { right: -4px; }
        .categories-nav-arrow[hidden] { display: none; }
        .footer-ultra {
            position: relative;
            background-color:#D8E9E0;
            background-image:
                linear-gradient(transparent, transparent),
                linear-gradient(transparent, transparent);
        }
        .footer-ultra::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='fn'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23fn)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.9;
        }
        .footer-ultra-inner { position: relative; z-index: 1; }
        .footer-v2-link {
            display: block;
            padding: 0.32rem 0;
            font-size: 0.8125rem;
            color: rgba(138,169,155, 0.92);
            transition: color .18s ease, padding-left .18s ease;
        }
        .footer-v2-link:hover {
            color: #F7C396;
            padding-left: 0.35rem;
        }
        .social-links-wrap {
            position: relative;
        }
        .social-icon-ultra {
            position: relative;
            overflow: hidden;
            border-radius: 0.62rem;
            backdrop-filter: blur(8px);
            transition: transform .28s cubic-bezier(0.2, 0.8, 0.2, 1), color .25s ease, border-color .25s ease, box-shadow .3s ease, background-color .25s ease;
            isolation: isolate;
        }
        .social-icon-ultra::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: rgba(235,110,0, 0.15);
            opacity: 0;
            transform: rotate(0deg);
            transition: opacity .28s ease;
            pointer-events: none;
            z-index: 0;
        }
        .social-icon-ultra::after {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: calc(0.62rem - 1px);
            background: rgba(255, 255, 255, 0.05);
            opacity: 0;
            transition: opacity .25s ease;
            z-index: 0;
        }
        .social-icon-ultra i {
            position: relative;
            z-index: 1;
            transition: transform .25s ease;
        }
        .social-icon-ultra:hover {
            transform: translateY(-3px) scale(1.06);
            box-shadow: 0 10px 28px rgba(14,59,42,0.35), 0 0 18px rgba(235,110,0,0.22);
        }
        .social-icon-ultra:hover::before {
            opacity: 0.9;
            animation: socialRingSpin 1.2s linear infinite;
        }
        .social-icon-ultra:hover::after {
            opacity: 1;
        }
        .social-icon-ultra:hover i {
            transform: scale(1.1);
        }
        .social-icon-ultra.social-icon-wa:hover {
            box-shadow: 0 10px 28px rgba(14,59,42,0.35), 0 0 18px rgba(0,135,79,0.28);
        }
        .social-icon-ultra.social-icon-wa::before {
            background: rgba(0,135,79, 0.21);
        }
        .footer-logo-link {
            display: inline-flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .footer-logo-ring {
            position: relative;
            border-radius: 0.7rem;
            padding: 2px;
            background: rgba(235,110,0, 0.31);
            box-shadow: 0 8px 26px rgba(14,59,42,0.35), 0 0 0 1px rgba(255,255,255,0.05) inset;
            transition: transform .32s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow .32s ease;
            animation: footerLogoFloat 5.4s ease-in-out infinite;
        }
        .footer-logo-ring::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: inherit;
            background: rgba(235,110,0, 0.14);
            opacity: 0.4;
            filter: blur(5px);
            animation: footerLogoSpin 8s linear infinite;
            pointer-events: none;
        }
        .footer-logo-inner {
            border-radius: calc(0.7rem - 2px);
            overflow: hidden;
            background: rgba(216,233,224, 0.9);
            transition: transform .32s cubic-bezier(0.2, 0.8, 0.2, 1), filter .32s ease;
        }
        .footer-logo-link:hover .footer-logo-ring {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 12px 30px rgba(14,59,42,0.42), 0 0 22px rgba(235,110,0,0.25), 0 0 0 1px rgba(255,255,255,0.08) inset;
        }
        .footer-logo-link:hover .footer-logo-ring::before {
            opacity: 0.82;
        }
        .footer-logo-link:hover .footer-logo-inner {
            transform: scale(1.04);
            filter: brightness(1.08) saturate(1.08);
        }
        @keyframes footerLogoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }
        @keyframes footerLogoSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes socialRingSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        html:not(.dark) .articles-hero {
            border-bottom-color: rgba(14,59,42,0.08);
            background-image:
                linear-gradient(rgba(255,255,255, 0.97), rgba(255,255,255, 0.97)),
                linear-gradient(transparent, transparent),
                var(--articles-hero-bg-image, none);
            background-size: auto, auto, cover;
            background-position: 0 0, 0 0, center;
            background-repeat: repeat, no-repeat, no-repeat;
        }
        html:not(.dark) .articles-hero[style*="--articles-hero-bg-image"] {
            background-image: var(--articles-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        html:not(.dark) .articles-search-shell {
            border-color: rgba(14,59,42,0.08);
            background: rgba(216,233,224, 0.9);
            box-shadow: 0 10px 22px rgba(14,59,42,0.06);
        }
        html:not(.dark) .articles-search-input {
            border-color: rgba(14,59,42,0.1);
            background:#D8E9E0;
            color: #0E3B2A;
        }
        html:not(.dark) .articles-search-input::placeholder { color:#24503D; }
        html:not(.dark) .articles-hero p.text-orange-300 { color:#B85600 !important; }
        html:not(.dark) .articles-hero p.text-gray-500 { color:#24503D !important; }
        html:not(.dark) .categories-strip {
            border-color: rgba(14,59,42,0.1);
            background: rgba(216,233,224, 0.92);
            box-shadow: 0 8px 20px rgba(14,59,42,0.05);
        }
        html:not(.dark) .categories-nav-arrow {
            background: rgba(255,255,255,0.96);
            color: #0E3B2A;
            border-color: rgba(14,59,42,0.1);
            box-shadow: 0 8px 18px rgba(14,59,42,0.12);
        }
        html:not(.dark) .categories-nav-arrow:hover { background: #EB6E00; color: #fff; }
        html:not(.dark) .category-chip {
            border-color: rgba(14,59,42,0.12);
            background: rgba(216,233,224, 0.9);
            color: #0E3B2A;
        }
        html:not(.dark) .category-chip:hover {
            color: #0E3B2A;
            border-color: rgba(235,110,0,0.35);
            background: rgba(235,110,0,0.10);
            box-shadow: 0 0 14px rgba(235,110,0,0.12);
        }
        html:not(.dark) .category-chip.is-active {
            border-color: rgba(235,110,0,0.45);
            background: #EB6E00;
            color: #0E3B2A;
            box-shadow: 0 8px 18px rgba(235,110,0,0.2);
        }
        html:not(.dark) .agenda-panel {
            border-color: rgba(14,59,42,0.1);
            background:
                linear-gradient(rgba(255,255,255, 0.98), rgba(255,255,255, 0.98)),
                linear-gradient(transparent, transparent);
            box-shadow: 0 16px 30px rgba(14,59,42,0.07);
        }
        html:not(.dark) .agenda-card {
            border-color: rgba(14,59,42,0.1);
            background: #F3F8F5;
            box-shadow: 0 12px 24px rgba(14,59,42,0.06);
        }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-354c0e2d.css') }}?v={{ @filemtime(public_path('css/tw-354c0e2d.css')) }}">
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')
@include('partials.public-top-nav')

@php
    $articlesHeroImage = \Illuminate\Support\Facades\Schema::hasTable('articles_hero_images')
        ? \App\Models\ArticlesHeroImage::query()->find(1)
        : null;
    $authorName = fn ($a) => strcasecmp(trim((string) ($a->author->full_name ?? '')), 'Super Admin') === 0 ? __('Rédaction') : ($a->author->full_name ?? __('Rédaction'));
    // Couleur du logo par rubrique, stable sur toute la page
    $catTone = $categories->values()->mapWithKeys(fn ($c, $i) => [$c->id => 'c'.(($i % 7) + 1)]);
@endphp
<style>
    /* Page Magazine : charte du site et couleurs du logo. Images entières, légendes sur cartes blanches,
       aplats, ombre au survol seulement. Classes conservées : .categories-nav / .categories-strip / .categories-nav-arrow. */
    .mg { max-width: 1180px; margin: 0 auto; padding: 26px 20px 64px; color: #0E3B2A; }
    .lc { --c: 235,110,0; }
    .lc.c2 { --c: 0,135,79; } .lc.c3 { --c: 0,135,79; } .lc.c4 { --c: 235,110,0; } .lc.c5 { --c: 0,135,79; } .lc.c6 { --c: 235,110,0; } .lc.c7 { --c: 184,86,0; }
    .mg-bar { display: flex; align-items: center; gap: 12px; padding: 8px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
    .mg-bar .categories-nav { flex: 1; min-width: 0; margin: 0 !important; }
    .mg-bar .categories-strip { scrollbar-width: none; padding: 0 !important; }
    .mg-bar .categories-strip::-webkit-scrollbar { display: none; }
    .mg-chip { flex: none; display: inline-flex; align-items: center; height: 40px; padding: 0 16px; border-radius: 999px; background: #F3F8F5; color: #0E3B2A; font-size: 14px; font-weight: 600; text-decoration: none; white-space: nowrap; transition: background .15s ease, color .15s ease; }
    .mg-chip:hover { background: #D8E9E0; color: #0E3B2A; }
    .mg-chip.is-active { background: #00874F; color: #fff; }
    .mg-search { flex: none; display: flex; align-items: center; gap: 6px; height: 44px; padding: 0 6px 0 14px; border-radius: 999px; background: #F3F8F5; }
    .mg-search input { width: 200px; border: 0; background: transparent; outline: none; font-size: 14px; color: #0E3B2A; }
    .mg-search button { width: 34px; height: 34px; border-radius: 999px; border: 0; background: #EB6E00; color: #fff; cursor: pointer; display: flex; align-items: center; justify-content: center; }
    .mg-search button:hover { background: #B85600; }
    .mg-search:focus-within { outline: 2px solid #EB6E00; }
    .categories-nav-arrow { width: 34px; height: 34px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; color: #B85600; }

    .mg-label { margin: 0 0 6px; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #EB6E00; }
    .mg-h2 { margin: 0 0 18px; font-family: 'Montserrat', serif; font-size: clamp(24px, 1.2vw + 18px, 32px); font-weight: 700; color: #0E3B2A; }
    .mg-sec { margin-top: 40px; }

    /* À la une */
    .mg-feat { display: grid; grid-template-columns: minmax(0, 1.5fr) minmax(0, 1fr); gap: 16px; }
    .mg-lead { position: relative; display: block; border-radius: 28px; overflow: hidden; background: #D8E9E0; color: inherit; text-decoration: none; min-height: 440px; }
    .mg-lead img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
    .mg-lead:hover img { transform: scale(1.03); }
    .mg-lead__cap { position: absolute; left: 16px; right: 16px; bottom: 16px; padding: 18px 20px; border-radius: 20px; background: #fff; }
    .mg-lead__cap b { display: block; margin-top: 6px; font-family: 'Montserrat', serif; font-size: clamp(20px, 1vw + 16px, 28px); line-height: 1.2; color: #0E3B2A; }
    .mg-lead__cap p { margin: 8px 0 0; font-size: 14.5px; line-height: 1.55; color: #24503D; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .mg-side { display: flex; flex-direction: column; gap: 16px; }
    .mg-mini { display: grid; grid-template-columns: 140px minmax(0, 1fr); gap: 14px; padding: 10px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; flex: 1; }
    .mg-mini:hover { border-color: #8AA99B; box-shadow: 0 16px 30px -26px rgba(14,59,42,.5); }
    .mg-mini__img { position: relative; border-radius: 15px; overflow: hidden; background: #F3F8F5; min-height: 120px; }
    .mg-mini__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    .mg-mini__body { display: flex; flex-direction: column; gap: 6px; padding: 4px 4px 4px 0; min-width: 0; }
    .mg-mini__body b { font-family: 'Montserrat', serif; font-size: 17px; line-height: 1.25; color: #0E3B2A; display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .mg-kick { display: inline-flex; align-self: flex-start; align-items: center; height: 24px; padding: 0 10px; border-radius: 999px; background: rgba(var(--c), .12); color: rgb(var(--c)); font-size: 11px; font-weight: 800; letter-spacing: .06em; text-transform: uppercase; }
    .mg-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 4px 12px; font-size: 12.5px; color: #557466; }
    .mg-meta span { display: inline-flex; align-items: center; gap: 5px; }
    .mg-star { position: absolute; top: 14px; left: 14px; display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: #fff; color: #B85600; font-size: 11.5px; font-weight: 800; }

    /* Grille */
    .mg-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 18px; }
    .mg-card { display: flex; flex-direction: column; border-radius: 24px; overflow: hidden; background: #fff; border: 1px solid #D8E9E0; color: inherit; text-decoration: none; transition: border-color .15s ease, box-shadow .2s ease; }
    .mg-card:hover { border-color: #8AA99B; box-shadow: 0 20px 36px -28px rgba(14,59,42,.55); }
    .mg-card__img { position: relative; aspect-ratio: 16 / 10; background: #F3F8F5; overflow: hidden; display: flex; align-items: center; justify-content: center; color: #EB6E00; font-size: 30px; }
    .mg-card__img img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
    .mg-card:hover .mg-card__img img { transform: scale(1.04); }
    .mg-card__sp { position: absolute; top: 12px; right: 12px; height: 26px; padding: 0 10px; border-radius: 999px; background: #fff; color: #557466; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; }
    .mg-card__body { flex: 1; display: flex; flex-direction: column; gap: 8px; padding: 16px 18px 18px; }
    .mg-card__body b { font-family: 'Montserrat', serif; font-size: 19px; line-height: 1.25; color: #0E3B2A; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .mg-card__body p { margin: 0; font-size: 14px; line-height: 1.55; color: #24503D; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .mg-card__foot { margin-top: auto; padding-top: 12px; border-top: 1px solid #F3F8F5; }
    .mg-contrib { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 8px; }
    .mg-contrib span { height: 22px; padding: 0 9px; border-radius: 999px; background: #F3F8F5; color: #B85600; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; }
    .mg-empty { grid-column: 1 / -1; padding: 50px 20px; border-radius: 24px; background: #fff; border: 1px dashed #8AA99B; text-align: center; color: #557466; }
    .mg-empty b { display: block; font-family: 'Montserrat', serif; font-size: 22px; color: #0E3B2A; }

    /* Agenda */
    .mg-agenda { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 16px; }
    .mg-ev { display: flex; flex-direction: column; border-radius: 22px; overflow: hidden; background: #fff; border: 1px solid #D8E9E0; color: inherit; text-decoration: none; }
    .mg-ev:hover { border-color: #8AA99B; }
    .mg-ev__img { aspect-ratio: 16 / 9; background: #F3F8F5; overflow: hidden; }
    .mg-ev__img img { width: 100%; height: 100%; object-fit: cover; }
    .mg-ev__body { padding: 14px 16px 16px; }
    .mg-ev__body b { display: block; margin-top: 4px; font-family: 'Montserrat', serif; font-size: 17px; color: #0E3B2A; }

    /* Pagination */
    .mg-pages { display: flex; justify-content: center; gap: 6px; margin-top: 36px; }
    .mg-pages a, .mg-pages span { min-width: 42px; height: 42px; padding: 0 12px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: #fff; border: 1px solid #D8E9E0; color: #0E3B2A; font-size: 14px; font-weight: 700; text-decoration: none; }
    .mg-pages a:hover { border-color: #EB6E00; color: #B85600; }
    .mg-pages .is-on { background: #00874F; border-color: #00874F; color: #fff; }
    .mg-pages .is-off { color: #8AA99B; }

    .mg a:focus-visible, .mg button:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

    @media (max-width: 1023px) {
        .mg-feat { grid-template-columns: minmax(0, 1fr); }
        .mg-lead { min-height: 380px; }
        .mg-side { flex-direction: row; }
        .mg-mini { grid-template-columns: minmax(0, 1fr); }
        .mg-mini__img { min-height: 150px; }
        .mg-grid, .mg-agenda { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 640px) {
        .mg { padding: 18px 16px 48px; }
        .mg-bar { flex-direction: column; align-items: stretch; border-radius: 20px; }
        .mg-search input { width: 100%; }
        .mg-lead { min-height: 330px; border-radius: 22px; }
        .mg-lead__cap { left: 10px; right: 10px; bottom: 10px; padding: 14px 16px; }
        .mg-side { flex-direction: column; }
        .mg-mini { grid-template-columns: 110px minmax(0, 1fr); }
        .mg-mini__img { min-height: 100px; }
        .mg-grid, .mg-agenda { grid-template-columns: minmax(0, 1fr); }
        .mg-pages a, .mg-pages span { min-width: 38px; height: 38px; }
    }
    @media (prefers-reduced-motion: reduce) { .mg-lead img, .mg-card__img img { transition: none; } }
</style>

{{-- ── En-tête ── --}}
<section class="articles-hero hero-magazine relative py-20 overflow-hidden {{ ($articlesHeroImage && $articlesHeroImage->isVisible()) ? 'has-admin-bg' : '' }}"
    @if($articlesHeroImage && $articlesHeroImage->isVisible())
        style="--articles-hero-bg-image: url('{{ $articlesHeroImage->image_url }}');"
    @endif
>
    @include('partials.ivorian-motifs', ['culture' => 'magazine', 'band' => ! ($articlesHeroImage && $articlesHeroImage->isVisible())])
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <div class="ti-hero-card inline-block rounded-2xl px-6 py-8 sm:px-12 sm:py-10">
            <p class="text-orange-300 text-sm font-medium uppercase tracking-widest mb-3">{{ __('Magazine') }}</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                @if($active_category)
                    {{ $active_category->name_fr }}
                @elseif($search)
                    {{ __('Résultats pour "') }}{{ $search }}"
                @else
                    {{ __('Tous les articles') }}
                @endif
            </h1>
            <p class="text-gray-200 text-xl max-w-2xl mx-auto">{{ $articles->total() }} {{ __('article') }}{{ $articles->total() > 1 ? 's' : '' }}</p>
        </div>
    </div>
</section>

<main class="mg">
    {{-- Barre : rubriques + recherche --}}
    <div class="mg-bar">
        <div class="categories-nav relative">
            <button type="button" class="categories-nav-arrow categories-nav-arrow--left" data-dir="-1" aria-label="{{ __('Catégories précédentes') }}" hidden><i class="fas fa-chevron-left"></i></button>
            <div class="categories-strip flex items-center gap-2 overflow-x-auto">
                <a href="{{ route('articles.index') }}" class="mg-chip {{ ! $active_category && ! $search ? 'is-active' : '' }}">{{ __('Tous') }}</a>
                @foreach($categories as $cat)
                    <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}" class="mg-chip {{ $active_category?->id === $cat->id ? 'is-active' : '' }}">{{ $cat->localized('name') }}</a>
                @endforeach
            </div>
            <button type="button" class="categories-nav-arrow categories-nav-arrow--right" data-dir="1" aria-label="{{ __('Catégories suivantes') }}"><i class="fas fa-chevron-right"></i></button>
        </div>
        <form method="GET" action="{{ route('articles.index') }}" class="mg-search" role="search">
            <x-icon name="magnifier" class="text-[#557466]" />
            <input name="q" value="{{ $search }}" placeholder="{{ __('Rechercher un article…') }}" aria-label="{{ __('Rechercher un article') }}">
            <button type="submit" aria-label="{{ __('Rechercher') }}"><x-icon name="alt-arrow-right" /></button>
        </form>
    </div>

    {{-- À la une (sans filtre) --}}
    @if(! $active_category && ! $search && $featured->isNotEmpty())
        @php $lead = $featured->first(); $rest = $featured->slice(1); @endphp
        <section class="mg-sec" aria-labelledby="mg-une">
            <p class="mg-label">{{ __('Sélection de la rédaction') }}</p>
            <h2 class="mg-h2" id="mg-une">{{ __('À la une') }}</h2>
            <div class="mg-feat">
                <a href="{{ route('articles.show', $lead->slug_fr) }}" class="mg-lead lc {{ $catTone[$lead->category_id] ?? 'c1' }}">
                    @if($lead->cover_url)<img src="{{ $lead->cover_url }}" alt="{{ $lead->cover_alt ?: $lead->localized('title') }}" decoding="async" fetchpriority="high">@endif
                    <span class="mg-star"><x-icon name="star" />{{ __('À la une') }}</span>
                    <span class="mg-lead__cap">
                        <span class="mg-kick">{{ $lead->category?->localized('name') ?? __('Magazine') }}</span>
                        <b>{{ $lead->localized('title') }}</b>
                        @if($lead->localized('excerpt'))<p>{{ $lead->localized('excerpt') }}</p>@endif
                        <span class="mg-meta" style="margin-top:10px">
                            <span><x-icon name="user-rounded" />{{ $authorName($lead) }}</span>
                            <span><x-icon name="calendar" />{{ $lead->published_at?->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</span>
                            @if($lead->reading_time)<span><x-icon name="clock-circle" />{{ $lead->reading_time }} {{ __('min') }}</span>@endif
                        </span>
                    </span>
                </a>
                @if($rest->isNotEmpty())
                    <div class="mg-side">
                        @foreach($rest as $art)
                            <a href="{{ route('articles.show', $art->slug_fr) }}" class="mg-mini lc {{ $catTone[$art->category_id] ?? 'c1' }}">
                                <span class="mg-mini__img" style="display:flex;align-items:center;justify-content:center;color:#EB6E00;font-size:26px"><x-icon name="notes" />@if($art->cover_url)<img src="{{ $art->cover_url }}" alt="{{ $art->cover_alt ?: $art->localized('title') }}" loading="lazy" decoding="async">@endif</span>
                                <span class="mg-mini__body">
                                    <span class="mg-kick">{{ $art->category?->localized('name') ?? __('Magazine') }}</span>
                                    <b>{{ $art->localized('title') }}</b>
                                    <span class="mg-meta"><span>{{ $art->published_at?->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</span>@if($art->reading_time)<span>· {{ $art->reading_time }} {{ __('min') }}</span>@endif</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </section>
    @endif

    {{-- Tous les articles --}}
    <section class="mg-sec" aria-labelledby="mg-all">
        <p class="mg-label">{{ $active_category ? __('Rubrique') : ($search ? __('Recherche') : __('Derniers articles')) }}</p>
        <h2 class="mg-h2" id="mg-all">{{ $active_category ? $active_category->localized('name') : ($search ? __('Résultats') : __('À lire en ce moment')) }}</h2>
        <div class="mg-grid">
            @forelse($articles as $article)
                @php $contributors = $article->display_uploaders->reject(fn ($c) => strcasecmp(trim((string) $c->full_name), 'Super Admin') === 0); @endphp
                <a href="{{ route('articles.show', $article->slug_fr) }}" class="mg-card lc {{ $catTone[$article->category_id] ?? 'c1' }} reveal">
                    <span class="mg-card__img">
                        <x-icon name="notes" />
                        @if($article->cover_url)<img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?? $article->localized('title') }}" loading="lazy" decoding="async">@endif
                        @if($article->is_featured)<span class="mg-star"><x-icon name="star" />{{ __('À la une') }}</span>@endif
                        @if($article->is_sponsored)<span class="mg-card__sp">{{ __('Sponsorisé') }}</span>@endif
                    </span>
                    <span class="mg-card__body">
                        <span class="mg-kick">{{ $article->category?->localized('name') ?? __('Magazine') }}</span>
                        <b>{{ $article->localized('title') }}</b>
                        @if($article->localized('excerpt'))<p>{{ $article->localized('excerpt') }}</p>@endif
                        <span class="mg-card__foot">
                            <span class="mg-meta">
                                <span><x-icon name="user-rounded" />{{ $authorName($article) }}</span>
                                <span>{{ $article->published_at?->locale(app()->getLocale())->isoFormat('D MMM YYYY') }}</span>
                                @if($article->reading_time)<span><x-icon name="clock-circle" />{{ $article->reading_time }} {{ __('min') }}</span>@endif
                            </span>
                            @if($contributors->isNotEmpty())
                                <span class="mg-contrib">@foreach($contributors->take(3) as $contributor)<span>{{ $contributor->full_name }}</span>@endforeach</span>
                            @endif
                        </span>
                    </span>
                </a>
            @empty
                @if(! ($active_category?->slug === 'agenda' && ($agendaEvents ?? collect())->isNotEmpty()))
                    <div class="mg-empty"><b>{{ __('Aucun article trouvé') }}</b>{{ __('Essayez une autre recherche ou catégorie.') }}</div>
                @endif
            @endforelse
        </div>
    </section>

    @if($active_category?->slug === 'agenda')
        <section class="mg-sec" aria-labelledby="mg-agenda">
            <p class="mg-label">{{ __('Agenda') }}</p>
            <h2 class="mg-h2" id="mg-agenda">{{ __('Tous les événements') }}</h2>
            <div class="mg-agenda">
                @forelse(($agendaEvents ?? collect()) as $event)
                    <a href="{{ route('events.show', $event->slug) }}" class="mg-ev lc c{{ ($loop->index % 7) + 1 }}">
                        <span class="mg-ev__img">@if($event->cover_url)<img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->localized('title') }}" loading="lazy">@endif</span>
                        <span class="mg-ev__body">
                            <span class="mg-kick">{{ $event->category?->localized('name') ?? __('Événement') }}</span>
                            <b>{{ $event->localized('title') }}</b>
                            <span class="mg-meta" style="margin-top:6px"><span><x-icon name="calendar" />{{ $event->starts_at?->format('d/m/Y H:i') }}</span><span><x-icon name="map-point" />{{ $event->city ?: 'Côte d\'Ivoire' }}</span></span>
                        </span>
                    </a>
                @empty
                    <div class="mg-empty">{{ __('Aucun événement publié pour le moment.') }}</div>
                @endforelse
            </div>
        </section>
    @endif

    {{-- Pagination --}}
    @if($articles->hasPages())
        <nav class="mg-pages" aria-label="{{ __('Pagination') }}">
            @if($articles->onFirstPage())<span class="is-off" aria-hidden="true">‹</span>@else<a href="{{ $articles->previousPageUrl() }}" aria-label="{{ __('Page précédente') }}">‹</a>@endif
            @foreach($articles->getUrlRange(max(1, $articles->currentPage() - 2), min($articles->lastPage(), $articles->currentPage() + 2)) as $page => $url)
                <a href="{{ $url }}" class="{{ $page === $articles->currentPage() ? 'is-on' : '' }}" @if($page === $articles->currentPage()) aria-current="page" @endif>{{ $page }}</a>
            @endforeach
            @if($articles->hasMorePages())<a href="{{ $articles->nextPageUrl() }}" aria-label="{{ __('Page suivante') }}">›</a>@else<span class="is-off" aria-hidden="true">›</span>@endif
        </nav>
    @endif
</main>

@include('partials.homepage-footer')

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

    // Categories strip: scroll arrows + molette horizontale (la liste dépasse la largeur visible)
    document.querySelectorAll('.categories-nav').forEach((nav) => {
        const strip = nav.querySelector('.categories-strip');
        const prev = nav.querySelector('.categories-nav-arrow--left');
        const next = nav.querySelector('.categories-nav-arrow--right');
        if (!strip || !prev || !next) return;

        const updateArrows = () => {
            const max = strip.scrollWidth - strip.clientWidth;
            prev.hidden = strip.scrollLeft <= 4;
            next.hidden = max <= 4 || strip.scrollLeft >= max - 4;
        };
        const scrollByStep = (dir) => {
            strip.scrollBy({ left: dir * Math.round(strip.clientWidth * 0.7), behavior: 'smooth' });
        };

        prev.addEventListener('click', () => scrollByStep(-1));
        next.addEventListener('click', () => scrollByStep(1));
        strip.addEventListener('scroll', updateArrows, { passive: true });
        strip.addEventListener('wheel', (e) => {
            if (Math.abs(e.deltaY) > Math.abs(e.deltaX)) {
                strip.scrollLeft += e.deltaY;
                e.preventDefault();
            }
        }, { passive: false });
        window.addEventListener('resize', updateArrows);
        updateArrows();
    });
</script>
@include('partials.image-protection')
</body>
</html>
