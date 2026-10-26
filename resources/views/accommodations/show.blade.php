<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $accommodation->name }} — {{ __('Résidences & Hôtels') }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.share-meta', ['title' => $accommodation->name, 'description' => $accommodation->short_description ?: $accommodation->description, 'image' => $accommodation->cover_image ?: $accommodation->thumbnail, 'url' => route('accommodations.show', $accommodation)])
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/flatpickr.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/flatpickr/4.6.13/l10n/fr.js"></script>
    <style>
        /* ── Calendrier de disponibilité (chambre) — thème aux couleurs du site ── */
        .flatpickr-calendar { box-shadow: 0 20px 45px rgba(14,59,42,0.18); border-radius: 1rem; font-family: 'Montserrat', sans-serif; }
        .flatpickr-day.selected, .flatpickr-day.startRange, .flatpickr-day.endRange {
            background: #EB6E00; border-color: #EB6E00; color: #fff;
        }
        .flatpickr-day.inRange {
            background: rgba(235,110,0,0.14); border-color: rgba(235,110,0,0.14); box-shadow: -5px 0 0 rgba(235,110,0,0.14), 5px 0 0 rgba(235,110,0,0.14);
        }
        .flatpickr-day.selected:hover, .flatpickr-day.startRange:hover, .flatpickr-day.endRange:hover { background: #EB6E00; border-color: #EB6E00; }
        .flatpickr-day.today { border-color: #EB6E00; }
        .flatpickr-day.flatpickr-disabled, .flatpickr-day.flatpickr-disabled:hover {
            color: #8AA99B; text-decoration: line-through; cursor: not-allowed;
        }
        .bkp-date-loading { position: relative; }
        .bkp-date-loading::after {
            content: ''; position: absolute; inset: 0; border-radius: .75rem;
            background: rgba(14,59,42, 0.02);
            pointer-events: none;
        }
    </style>
    <style>
        html { scroll-behavior: smooth; }
        body { font-family: 'Montserrat', sans-serif; background: #F3F8F5; color: #0E3B2A; }
        .font-serif { font-family: 'Montserrat', serif; }
        .reveal { opacity: 0; transform: translateY(26px); transition: opacity .7s cubic-bezier(.2,.7,.2,1), transform .7s cubic-bezier(.2,.7,.2,1); }
        .reveal.is-visible { opacity: 1; transform: none; }
        @media (prefers-reduced-motion: reduce) { .reveal, .reveal.is-visible { opacity:1!important; transform:none!important; transition:none!important; } }

        .btn-primary {
            background: #EB6E00;
            box-shadow: 0 12px 28px rgba(235,110,0,0.35);
            transition: transform .22s ease, box-shadow .22s ease, filter .22s ease;
            color: #0E3B2A;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .btn-primary:disabled { transform:none!important; filter:none!important; }

        .hero-cover { position: relative; overflow: hidden; background: #0E3B2A; }
        .hero-cover img { transition: transform .6s ease; }
        .hero-cover:hover img { transform: scale(1.05); }
        .glass-arrow { border: 1px solid rgba(255,255,255,0.18); background: rgba(14,59,42,0.35); backdrop-filter: blur(10px); transition: transform .2s ease, background .2s ease; }
        .glass-arrow:hover { background: rgba(14,59,42,0.6); transform: scale(1.06); }
        .gallery-tile { position: relative; overflow: hidden; cursor: pointer; border-radius: .85rem; }
        .gallery-tile img { transition: transform .4s ease; }
        .gallery-tile:hover img { transform: scale(1.08); }

        .bento-card {
            border: 1px solid rgba(14,59,42,0.07);
            background: #FFFFFF;
            box-shadow: 0 10px 28px rgba(14,59,42,0.06);
        }
        .bento-icon {
            width: 2.2rem; height: 2.2rem; border-radius: 0.75rem;
            display: flex; align-items: center; justify-content: center;
            background: rgba(235,110,0, 0.12);
            color: #EB6E00;
        }
        .section-kicker { letter-spacing: .22em; }
        .review-card { border: 1px solid rgba(14,59,42,0.07); background: #ffffff; box-shadow: 0 8px 22px rgba(14,59,42,0.05); }
        .related-tile { border: 1px solid rgba(14,59,42,0.07); background: #ffffff; box-shadow: 0 10px 26px rgba(14,59,42,0.06); transition: transform .25s ease, box-shadow .25s ease; }
        .related-tile:hover { transform: translateY(-4px); box-shadow: 0 16px 34px rgba(235,110,0,0.16); }

        .booking-card { border: 1px solid rgba(14,59,42,0.08); background: #FFFFFF; box-shadow: 0 20px 50px rgba(14,59,42,0.1); }
        .bkp-room-card { transition: transform .18s ease, border-color .18s ease, background .18s ease; }
        .bkp-room-card:hover { transform: translateY(-2px); }
        .bkp-room-card.is-selected { border-color: rgba(235,110,0,0.55) !important; background: rgba(235,110,0,0.07) !important; box-shadow: 0 10px 22px rgba(235,110,0,0.14); }
        .bkp-pay-method { transition: border-color .18s ease, background .18s ease; }
        .bkp-pay-method.is-active { border-color: rgba(235,110,0,0.55) !important; background: rgba(235,110,0,0.07) !important; }

        .mobile-cta-bar {
            border-top: 1px solid rgba(14,59,42,0.08); background: rgba(255,255,255,0.92); backdrop-filter: blur(14px);
            box-shadow: 0 -10px 26px rgba(14,59,42,0.1); padding-bottom: env(safe-area-inset-bottom, 0);
        }

        /* ══ Fiche hébergement — refonte visuelle (présentation seule) ══════════════════════════════
           Galerie en mosaïque, en-tête éditorial, sections en cartes posées, module de réservation
           « carte d'embarquement » ; aplats aux couleurs du logo, aucun dégradé. */
        .hx-back { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; color: #B85600; font-size: 13px; font-weight: 700; }
        .hx-back:hover { border-color: #EB6E00; color: #B85600; }
        .hx-back i { font-size: 11px; }
        .hx-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 18px; margin: 22px 0 22px; }
        .hx-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .hx-pill { display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 999px; background: #F3F8F5; color: #B85600; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .hx-pill.is-gold { background: #EB6E00; color: #0E3B2A; letter-spacing: .12em; }
        .hx-pill.is-ok { background: #F3F8F5; color: #00874F; text-transform: none; letter-spacing: 0; }
        .hx-title { margin: 0; font-family: 'Montserrat', serif; font-size: clamp(34px, 3.4vw + 14px, 56px); font-weight: 700; line-height: 1.04; letter-spacing: -.02em; color: #0E3B2A; }
        .hx-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 18px; margin: 14px 0 0; font-size: 15px; color: #24503D; }
        .hx-meta > span { display: inline-flex; align-items: center; gap: 7px; }
        .hx-meta i { color: #00874F; font-size: 13px; }
        .hx-rate { height: 32px; padding: 0 12px; border-radius: 999px; background: #F3F8F5; color: #B85600; font-weight: 700; font-size: 14px; }
        .hx-rate i { color: #EB6E00 !important; }
        .hx-rate small { font-weight: 500; font-size: 12px; }
        .hx-rate.is-new { background: #F3F8F5; color: #00874F; }
        .hx-rate.is-new i { color: #00874F !important; }
        .hx-actions { display: flex; align-items: center; gap: 10px; }
        .hx-actions .ti-round { background: #fff; border: 1px solid #D8E9E0; box-shadow: none; }

        .hx-gallery { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 10px; height: clamp(340px, 52vh, 540px); }
        .hx-gallery.is-single { grid-template-columns: 1fr; grid-template-rows: 1fr; }
        .hx-gallery__main { grid-row: span 2; border-radius: 28px; overflow: hidden; position: relative; background: #F3F8F5; }
        .hx-gallery__main img { transition: transform .7s ease; }
        .hx-gallery__main:hover img { transform: scale(1.03); }
        .hx-gallery__empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .hx-gallery__empty i { width: 76px; height: 76px; border-radius: 999px; background: #fff; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; }
        .hx-gallery__tile { position: relative; padding: 0; border: 0; border-radius: 22px; overflow: hidden; background: #F3F8F5; cursor: pointer; }
        .hx-gallery__tile img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .hx-gallery__tile:hover img { transform: scale(1.07); }
        .hx-gallery__tile:focus-visible, .hx-arrow:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .hx-gallery__more { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(14,59,42,.55); color: #fff; font-size: 16px; font-weight: 700; }
        .hx-gallery.is-single .hx-gallery__main { grid-row: auto; }
        .hx-zoom, .hx-gallery__count { position: absolute; z-index: 2; display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 999px; background: rgba(255,255,255,.95); color: #0E3B2A; box-shadow: 0 2px 8px rgba(14,59,42,.18); }
        .hx-zoom { right: 16px; bottom: 16px; width: 42px; height: 42px; font-size: 14px; }
        .hx-gallery__room { position: absolute; z-index: 2; left: 16px; top: 16px; display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 12px; border-radius: 999px; background: #00874F; color: #fff; font-size: 13px; font-weight: 700; box-shadow: 0 2px 8px rgba(14,59,42,.18); }
        .hx-gallery__count { left: 16px; bottom: 16px; height: 32px; padding: 0 12px; font-size: 13px; font-weight: 700; }
        .hx-arrow { position: absolute; top: 50%; z-index: 3; width: 46px; height: 46px; margin-top: -23px; border: 0; border-radius: 999px; background: rgba(255,255,255,.95); color: #0E3B2A; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 6px 18px rgba(14,59,42,.25); transition: transform .15s ease, background .15s ease; }
        .hx-arrow:hover { background: #EB6E00; color: #fff; transform: scale(1.06); }
        .hx-arrow.is-prev { left: 16px; } .hx-arrow.is-next { right: 16px; }
        .hx-videos { display: flex; gap: 10px; margin-top: 10px; }
        .hx-videos__tile { position: relative; width: 120px; height: 80px; padding: 0; border: 0; border-radius: 16px; overflow: hidden; background: #0E3B2A; cursor: pointer; }
        .hx-videos__tile img { width: 100%; height: 100%; object-fit: cover; }
        .hx-videos__play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; background: rgba(14,59,42,.35); }

        /* Galerie « fiche produit » : bande de vignettes verticale + grande image */
        .hx-gal { display: grid; grid-template-columns: 92px minmax(0, 1fr); gap: 14px; height: clamp(360px, 58vh, 580px); }
        .hx-gal.is-single { grid-template-columns: minmax(0, 1fr); }
        .hx-gal__thumbs { display: flex; flex-direction: column; gap: 10px; overflow-y: auto; padding: 2px; scrollbar-width: thin; scroll-snap-type: y proximity; }
        .hx-gal__thumb { position: relative; flex: none; width: 84px; height: 84px; padding: 0; border: 2px solid transparent; border-radius: 16px; overflow: hidden; background: #F3F8F5; cursor: pointer; opacity: .72; scroll-snap-align: start; transition: opacity .15s ease, border-color .15s ease, transform .15s ease; }
        .hx-gal__thumb img { width: 100%; height: 100%; object-fit: cover; }
        .hx-gal__thumb:hover { opacity: 1; transform: translateY(-1px); }
        .hx-gal__thumb.is-active { opacity: 1; border-color: #EB6E00; box-shadow: 0 0 0 3px rgba(235,110,0,.18); }
        .hx-gal__thumb:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .hx-gal__play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; background: rgba(14,59,42,.45); font-size: 14px; }
        .hx-gal__main { position: relative; border-radius: 28px; overflow: hidden; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .hx-gal__main > img { transition: opacity .25s ease; }
        .hx-gal__tools { position: absolute; z-index: 3; top: 14px; right: 14px; display: flex; flex-direction: column; gap: 8px; }
        .hx-gal__tool { width: 42px; height: 42px; border: 0; border-radius: 999px; background: rgba(255,255,255,.96); color: #0E3B2A; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; box-shadow: 0 2px 10px rgba(14,59,42,.18); transition: color .15s ease, transform .15s ease; }
        .hx-gal__tool:hover { color: #EB6E00; transform: scale(1.06); }
        .hx-gal__tool.is-on { color: #e11d2a; }
        .hx-gal__tool:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
        .hx-gal .hx-arrow { width: 44px; height: 44px; margin-top: -22px; }
        @media (max-width: 767px) {
            .hx-gal { grid-template-columns: minmax(0, 1fr); height: auto; gap: 10px; }
            .hx-gal__main { order: -1; aspect-ratio: 4 / 3; border-radius: 22px; }
            .hx-gal__thumbs { flex-direction: row; overflow-x: auto; overflow-y: hidden; scroll-snap-type: x proximity; }
            .hx-gal__thumb { width: 64px; height: 64px; border-radius: 12px; }
            .hx-gal .hx-arrow { width: 38px; height: 38px; margin-top: -19px; }
        }
        @media (prefers-reduced-motion: reduce) { .hx-gal__thumb, .hx-gal__main > img { transition: none; } }

        .hx-tabs { position: sticky; top: 76px; z-index: 20; display: flex; gap: 4px; padding: 6px; overflow-x: auto; scrollbar-width: none; background: rgba(255,255,255,.96); border: 1px solid #D8E9E0; border-radius: 999px; box-shadow: 0 10px 30px -24px rgba(14,59,42,.5); }
        .hx-tabs::-webkit-scrollbar { display: none; }
        .hx-tabs a { flex: none; height: 38px; padding: 0 16px; border-radius: 999px; display: inline-flex; align-items: center; color: #24503D; font-size: 14px; font-weight: 600; transition: background .15s ease, color .15s ease; }
        .hx-tabs a:hover { background: #F3F8F5; color: #B85600; }
        .hx-kicker { margin: 0 0 6px; font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #EB6E00; }
        .hx-h2 { margin: 0 0 18px; font-family: 'Montserrat', serif; font-size: clamp(24px, 1.4vw + 16px, 30px); font-weight: 700; line-height: 1.15; color: #0E3B2A; }
        .hx-sechead { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 10px; margin-bottom: 4px; }
        .hx-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px; color: #B85600; font-size: 14px; font-weight: 700; }
        .hx-link i { font-size: 11px; transition: transform .2s ease; }
        .hx-link:hover i { transform: translateX(3px); }
        section[id] { scroll-margin-top: 150px; }

        .hx-lead { margin: 0 0 14px; font-family: 'Montserrat', serif; font-size: clamp(20px, 1vw + 14px, 24px); line-height: 1.4; color: #0E3B2A; }
        .hx-text { margin: 0; font-size: 16px; line-height: 1.75; color: #24503D; white-space: pre-line; }
        .hx-facts { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 24px 0 0; padding: 0; list-style: none; }
        .hx-facts li { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; border-radius: 20px; background: #fff; border: 1px solid #D8E9E0; font-size: 15px; font-weight: 700; color: #0E3B2A; }
        .hx-facts small { display: block; margin-bottom: 3px; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #557466; }
        .hx-facts__icon { width: 42px; height: 42px; flex-shrink: 0; border-radius: 14px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }

        .hx-amen { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 0; padding: 0; list-style: none; }
        .hx-amen li { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 12px 14px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; font-size: 14px; font-weight: 600; color: #0E3B2A; }
        .hx-amen__icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 999px; background: #F3F8F5; color: #00874F; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }

        .hx-rooms { display: flex; flex-direction: column; gap: 14px; }
        .hx-room { display: grid; grid-template-columns: 130px minmax(0, 1fr) auto; gap: 16px; align-items: center; padding: 10px; background: #fff; border: 1px solid #D8E9E0; border-radius: 26px; box-shadow: 0 14px 36px -30px rgba(14,59,42,.45); transition: transform .2s ease, border-color .2s ease; }
        .hx-room:hover { transform: translateY(-3px); border-color: #8AA99B; }
        .hx-room__media { position: relative; height: 120px; border-radius: 18px; overflow: hidden; background: #F3F8F5; display: flex; align-items: center; justify-content: center; color: #EB6E00; font-size: 24px; }
        .hx-room__media[role="button"] { cursor: zoom-in; }
        .hx-room__media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .hx-room__count { position: absolute; right: 8px; bottom: 8px; height: 24px; padding: 0 8px; border-radius: 999px; background: rgba(14,59,42,.7); color: #fff; font-size: 11px; font-weight: 700; display: inline-flex; align-items: center; }
        .hx-room__body { min-width: 0; display: flex; flex-direction: column; gap: 8px; }
        .hx-room__name { margin: 0; font-family: 'Montserrat', serif; font-size: 20px; font-weight: 700; color: #0E3B2A; }
        .hx-room__specs { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
        .hx-room__specs li { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border-radius: 999px; background: #F3F8F5; color: #24503D; font-size: 12.5px; }
        .hx-room__specs i { font-size: 11px; color: #EB6E00; }
        .hx-room__amen { margin: 0; font-size: 13px; color: #557466; }
        .hx-room__badges { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; }
        .hx-room__badges span { display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; background: #EB6E00; color: #0E3B2A; font-size: 12px; font-weight: 700; }
        .hx-room__badges i { font-size: 10px; }
        .hx-room__cond { margin: 0; display: flex; gap: 6px; font-size: 12px; color: #557466; }
        .hx-room__cond i { margin-top: 2px; color: #B85600; }
        .hx-room__side { display: flex; flex-direction: column; align-items: flex-end; gap: 10px; padding-right: 8px; }
        .hx-room__price { margin: 0; text-align: right; line-height: 1.1; }
        .hx-room__price small { display: block; margin-bottom: 3px; font-size: 11px; color: #557466; }
        .hx-room__price strong { font-size: 22px; font-weight: 800; color: #0E3B2A; letter-spacing: -.01em; }
        .hx-room__price em { display: block; font-style: normal; font-size: 12px; color: #557466; }

        .hx-cond { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .hx-cond__item { display: flex; gap: 14px; align-items: flex-start; padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; font-size: 14px; line-height: 1.6; color: #24503D; }
        .hx-cond__item p { margin: 0; }
        .hx-cond__item.is-ok { background: #F3F8F5; border-color: #D8E9E0; color: #00874F; font-weight: 600; }
        .hx-cond__item.is-wide { grid-column: 1 / -1; }
        .hx-cond__icon { width: 40px; height: 40px; flex-shrink: 0; border-radius: 14px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }
        .hx-cond__item.is-ok .hx-cond__icon { background: #fff; color: #00874F; }

        .hx-map { position: relative; border-radius: 28px; overflow: hidden; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .hx-map iframe { display: block; width: 100%; height: 300px; border: 0; filter: saturate(.85); }
        .hx-map__lock { position: absolute; left: 14px; right: 14px; bottom: 14px; display: flex; gap: 14px; align-items: flex-start; padding: 16px; border-radius: 20px; background: rgba(255,255,255,.97); box-shadow: 0 12px 30px -16px rgba(14,59,42,.5); }
        .hx-map__title { margin: 0; font-size: 15px; font-weight: 700; color: #0E3B2A; }
        .hx-map__text { margin: 4px 0 8px; font-size: 13px; line-height: 1.55; color: #557466; }
        .hx-map__lock .hx-link { margin: 0; }
        .hx-map:not(:has(iframe)) .hx-map__lock { position: static; }

        .hx-score { display: inline-flex; align-items: center; gap: 12px; margin-bottom: 18px; padding: 8px 16px 8px 8px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; font-size: 13px; color: #557466; }
        .hx-score strong { width: 46px; height: 46px; border-radius: 999px; background: #00874F; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; }
        .hx-score i { color: #EB6E00; }
        .hx-reviews { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .hx-review { padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
        .hx-review__head { display: flex; align-items: center; gap: 10px; }
        .hx-review__avatar { width: 40px; height: 40px; flex-shrink: 0; border-radius: 999px; background: #00874F; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
        .hx-review__name { display: block; font-size: 14px; font-weight: 700; color: #0E3B2A; }
        .hx-review__ok { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: #00874F; }
        .hx-review__stars { margin-left: auto; display: inline-flex; gap: 2px; color: #EB6E00; font-size: 12px; }
        .hx-review__stars .is-off { color: #D8E9E0; }
        .hx-review__title { margin: 12px 0 0; font-size: 14px; font-weight: 700; color: #0E3B2A; }
        .hx-review__text { margin: 6px 0 0; font-size: 14px; line-height: 1.6; color: #24503D; }

        /* Module de réservation : carte nette, en-tête prix, sélection orange */
        .booking-card { background: #fff !important; border: 1px solid #D8E9E0 !important; border-radius: 30px !important; box-shadow: 0 26px 60px -36px rgba(14,59,42,.55) !important; }
        .hx-book-head { display: flex; align-items: flex-end; justify-content: space-between; gap: 10px; margin: -4px -4px 16px; padding: 14px 16px; border-radius: 22px; background: #00874F; color: #fff; }
        .hx-book-head small { display: block; font-size: 12px; color: #D8E9E0; }
        .hx-book-head strong { font-size: 26px; font-weight: 800; letter-spacing: -.01em; }
        .hx-book-head em { font-style: normal; font-size: 13px; color: #D8E9E0; }
        .hx-book-head .hx-book-rate { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 11px; border-radius: 999px; background: #EB6E00; color: #0E3B2A; font-size: 13px; font-weight: 700; }
        .bkp-room-card { border-radius: 18px !important; }
        .bkp-room-card.is-selected { border-color: #EB6E00 !important; background: #F3F8F5 !important; box-shadow: 0 0 0 3px rgba(235,110,0,.18) !important; }
        .bkp-pay-method.is-active { border-color: #EB6E00 !important; background: #F3F8F5 !important; }
        #booking-module input, #booking-module .bkp-step { font-size: 15px; }
        #booking-module input:focus { outline: 2px solid #EB6E00; outline-offset: 0; border-color: #EB6E00; }
        #bkp-submit { background: #EB6E00; color: #fff; box-shadow: 0 14px 30px -14px rgba(235,110,0,.7); border-radius: 999px; }
        #bkp-submit:hover { background: #B85600; }
        /* Compte express : note sous les coordonnées et encart du code (saisi sans quitter la page) */
        .bkx-note { display: flex; gap: 8px; margin: 2px 0 0; font-size: 12px; line-height: 1.45; color: #557466; }
        .bkx-note i { margin-top: 2px; color: #00874F; }
        .bkx { margin: 0 0 12px; padding: 14px; border-radius: 18px; background: #F3F8F5; border: 1px solid #D8E9E0; }
        .bkx[hidden] { display: none; }
        .bkx-title { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 14px; font-weight: 800; color: #0E3B2A; }
        .bkx-title i { color: #EB6E00; }
        .bkx-text { margin: 4px 0 10px; font-size: 12.5px; color: #24503D; }
        .bkx-input { width: 100%; height: 52px; border-radius: 14px; border: 1.5px solid #D8E9E0; background: #fff; text-align: center; font-size: 24px; font-weight: 800; letter-spacing: .45em; color: #0E3B2A; font-variant-numeric: tabular-nums; }
        .bkx-input:focus { outline: none; border-color: #EB6E00; box-shadow: 0 0 0 4px rgba(235,110,0,.14); }
        .bkx-err { min-height: 0; margin: 6px 0 0; font-size: 12.5px; font-weight: 600; color: #b42318; }
        .bkx-err:empty { display: none; }
        .bkx-actions { display: flex; flex-wrap: wrap; align-items: center; gap: 6px 14px; margin-top: 10px; }
        .bkx-ok { flex: 1 1 100%; height: 46px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 14px; font-weight: 800; cursor: pointer; }
        .bkx-ok:hover { background: #B85600; }
        .bkx-ok[disabled] { opacity: .6; cursor: progress; }
        .bkx-link { border: 0; padding: 0; background: none; font-size: 12.5px; font-weight: 700; color: #B85600; cursor: pointer; }
        .bkx-link:hover { text-decoration: underline; text-underline-offset: 3px; }
        .bkx-link[disabled] { color: #8AA99B; cursor: default; text-decoration: none; }
        .bkx-hint { margin: 8px 0 0; font-size: 11.5px; color: #557466; }

        @media (max-width: 1023px) {
            .hx-gallery { grid-template-columns: 1fr 1fr; grid-template-rows: 2fr 1fr; height: auto; }
            .hx-gallery__main { grid-column: 1 / -1; grid-row: auto; aspect-ratio: 16 / 10; }
            .hx-gallery__tile { aspect-ratio: 4 / 3; }
            .hx-gallery__tile:nth-of-type(n+4) { display: none; }
        }
        @media (max-width: 767px) {
            .hx-head { align-items: flex-start; }
            .hx-actions .ti-cta { display: none; }
            .hx-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .hx-amen, .hx-cond, .hx-reviews { grid-template-columns: 1fr; }
            .hx-room { grid-template-columns: 96px minmax(0, 1fr); }
            .hx-room__media { height: 96px; }
            .hx-room__side { grid-column: 1 / -1; flex-direction: row; align-items: center; justify-content: space-between; padding: 8px 4px 2px; border-top: 1px solid #D8E9E0; }
            .hx-room__price { text-align: left; }
            .hx-map__lock { position: static; margin: 0; border-radius: 0; box-shadow: none; }
            .hx-tabs { top: 64px; }
        }
        @media (prefers-reduced-motion: reduce) { .hx-room, .hx-gallery__main img, .hx-gallery__tile img { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-99914b93.css') }}?v={{ @filemtime(public_path('css/tw-99914b93.css')) }}">
</head>
<body class="text-[#0E3B2A]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-10">
        <a href="{{ route('accommodations.index') }}" class="hx-back">
            <i class="fas fa-arrow-left" aria-hidden="true"></i> {{ __('Résidences & Hôtels') }}
        </a>

        @php
            $photos = $accommodation->photos;
            $mainPhoto = $photos->first()?->url ?: $accommodation->cover_image;
            $hxRating = (float) ($accommodation->provider?->rating_avg ?? 0);
            $hxReviews = (int) ($accommodation->provider?->rating_count ?? 0);
            $hxPrices = collect($accommodation->room_types ?? [])->pluck('price_xof')->filter(fn ($p) => (int) $p > 0);
            $hxWhere = collect([$accommodation->quartier, $accommodation->city?->name, $accommodation->city?->region_administrative])->filter()->unique()->join(' · ');
        @endphp

        {{-- ── EN-TÊTE : type, nom, lieu, note, actions ─────────────────── --}}
        <header class="hx-head reveal is-visible">
            <div class="min-w-0">
                <div class="hx-pills">
                    <span class="hx-pill"><i class="fas fa-hotel" aria-hidden="true"></i>{{ $accommodation->type_label }}</span>
                    @if($accommodation->stars)
                        <span class="hx-pill is-gold" aria-label="{{ $accommodation->stars }} {{ __('étoiles') }}">{{ str_repeat('★', $accommodation->stars) }}</span>
                    @endif
                    @if($accommodation->provider?->is_verified)
                        <span class="hx-pill is-ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Vérifié') }}</span>
                    @endif
                </div>
                <h1 class="hx-title">{{ $accommodation->name }}</h1>
                <p class="hx-meta">
                    @if($hxWhere)<span><i class="fas fa-location-dot" aria-hidden="true"></i>{{ $hxWhere }}</span>@endif
                    @if($hxRating > 0)
                        <span class="hx-rate"><i class="fas fa-star" aria-hidden="true"></i>{{ number_format($hxRating, 1, ',', ' ') }}@if($hxReviews > 0)<small>({{ trans_choice(':count avis|:count avis', $hxReviews) }})</small>@endif</span>
                    @else
                        <span class="hx-rate is-new"><i class="fas fa-wand-magic-sparkles" aria-hidden="true"></i>{{ __('Nouveau') }}</span>
                    @endif
                </p>
            </div>
            <div class="hx-actions">
                <button type="button" class="ti-round" data-place-share="{{ route('accommodations.show', $accommodation->slug) }}" data-place-title="{{ $accommodation->name }}" aria-label="{{ __('Partager :name', ['name' => $accommodation->name]) }}" title="{{ __('Partager') }}"><i class="fas fa-share-nodes" aria-hidden="true"></i></button>
                @include('partials.place-card-fav', ['providerId' => $accommodation->provider_id, 'name' => $accommodation->name, 'favClass' => 'ti-round'])
                @if($hxPrices->isNotEmpty())
                    <a href="#booking-module" class="ti-cta">{{ __('Réserver') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                @endif
            </div>
        </header>

        {{-- ── GALERIE en mosaïque (photo principale + 4 vignettes) ───────── --}}
        @php
            // Photos de la galerie : celles de la chambre demandée (?room=…) en premier, puis celles de
            // l'établissement ; sans chambre précisée, l'établissement puis toutes les chambres.
            $hxRoomPhotos = fn ($room) => array_values(array_filter((array) ($room['photos'] ?? [])));
            $hxRooms = collect($accommodation->room_types ?? []);
            $hxSelectedRoom = is_string(request('room')) ? $hxRooms->first(fn ($r) => ($r['name'] ?? null) === request('room')) : null;
            $hxHotelUrls = collect([$mainPhoto])->merge($photos->pluck('url'))->filter();
            $hxGalleryUrls = ($hxSelectedRoom
                    ? collect($hxRoomPhotos($hxSelectedRoom))->merge($hxHotelUrls)
                    : $hxHotelUrls->merge($hxRooms->flatMap($hxRoomPhotos)))
                ->filter()->unique()->values();
            $hxGalleryLabel = $hxSelectedRoom && $hxRoomPhotos($hxSelectedRoom) ? $hxSelectedRoom['name'] : null;
            $mainPhoto = $hxGalleryUrls->first();

            $heroGalleryItems = $hxGalleryUrls->map(fn ($url) => ['type' => 'photo', 'url' => $url]);
            foreach ($accommodation->videos as $v) {
                $heroGalleryItems->push(['type' => 'video', 'embedUrl' => $v->embedUrl(), 'url' => $v->url]);
            }
            $hxThumbs = $hxGalleryUrls->slice(1, 4)->values();
            $hxMorePhotos = max(0, $hxGalleryUrls->count() - 5);
        @endphp
        <script id="hero-gallery-json" type="application/json">@json($heroGalleryItems->values())</script>
        <div class="hx-gal {{ $heroGalleryItems->count() <= 1 ? 'is-single' : '' }} reveal is-visible">
            {{-- Bande de vignettes : toutes les photos (et vidéos), la vignette active est encadrée --}}
            @if($heroGalleryItems->count() > 1)
                <div class="hx-gal__thumbs" aria-label="{{ __('Photos de :name', ['name' => $accommodation->name]) }}">
                    @foreach($heroGalleryItems as $i => $item)
                        <button type="button" class="hx-gal__thumb {{ $i === 0 ? 'is-active' : '' }}" data-hero-index="{{ $i }}"
                                onclick="showHeroItem({{ $i }})" aria-label="{{ $item['type'] === 'video' ? __('Lire la vidéo') : __('Voir la photo :n', ['n' => $i + 1]) }}"
                                aria-current="{{ $i === 0 ? 'true' : 'false' }}">
                            @if($item['type'] === 'video')
                                @php $vThumb = $accommodation->videos->firstWhere('url', $item['url'])?->videoThumbnailUrl(); @endphp
                                @if($vThumb)<img src="{{ $vThumb }}" alt="" loading="lazy" decoding="async">@endif
                                <span class="hx-gal__play" aria-hidden="true"><i class="fas fa-play"></i></span>
                            @else
                                <img src="{{ $item['url'] }}" alt="" loading="lazy" decoding="async">
                            @endif
                        </button>
                    @endforeach
                </div>
            @endif

            <div class="hero-cover hx-gal__main" id="hero-cover">
                @if($hxGalleryLabel)
                    <span class="hx-gallery__room"><i class="fas fa-bed" aria-hidden="true"></i>{{ __('Photos : :room', ['room' => $hxGalleryLabel]) }}</span>
                @endif
                @if($mainPhoto)
                    <img id="hero-cover-img" src="{{ $mainPhoto }}" alt="{{ $hxGalleryLabel ? $hxGalleryLabel.' — '.$accommodation->name : $accommodation->name }}"
                         class="w-full h-full object-cover cursor-zoom-in" decoding="async"
                         onclick="openLightbox(this.currentSrc || this.src)">
                @else
                    <div id="hero-cover-empty" class="hx-gallery__empty"><i class="fas fa-hotel" aria-hidden="true"></i></div>
                @endif
                <div id="hero-cover-video-wrap" class="absolute inset-0 hidden">
                    <iframe id="hero-cover-video-iframe" src="" class="w-full h-full hidden" loading="lazy"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen></iframe>
                    <video id="hero-cover-video-native" src="" controls preload="metadata" class="w-full h-full object-cover hidden"></video>
                </div>

                {{-- Actions en haut à droite : favori, agrandir --}}
                <div class="hx-gal__tools">
                    @include('partials.place-card-fav', ['providerId' => $accommodation->provider_id, 'name' => $accommodation->name, 'favClass' => 'hx-gal__tool'])
                    @if($mainPhoto)
                        <button type="button" id="hero-zoom-hint" class="hx-gal__tool" onclick="const i = document.getElementById('hero-cover-img'); openLightbox(i.currentSrc || i.src)" aria-label="{{ __('Agrandir la photo') }}" title="{{ __('Agrandir la photo') }}"><i class="fas fa-expand" aria-hidden="true"></i></button>
                    @endif
                </div>

                @if($heroGalleryItems->count() > 1)
                    <button type="button" onclick="heroSlide(-1)" class="hx-arrow is-prev" aria-label="{{ __('Photo précédente') }}"><i class="fas fa-chevron-left" aria-hidden="true"></i></button>
                    <button type="button" onclick="heroSlide(+1)" class="hx-arrow is-next" aria-label="{{ __('Photo suivante') }}"><i class="fas fa-chevron-right" aria-hidden="true"></i></button>
                    <span class="hx-gallery__count"><i class="fas fa-images" aria-hidden="true"></i><span id="hero-index">1</span>&nbsp;/&nbsp;{{ $heroGalleryItems->count() }}</span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 lg:gap-10 items-start mt-8">
            <div class="lg:col-span-3 space-y-12">

                {{-- Navigation de la fiche (ancres) --}}
                <nav class="hx-tabs" aria-label="{{ __('Sections de la fiche') }}">
                    <a href="#apercu">{{ __('Aperçu') }}</a>
                    @if(!empty($accommodation->amenities))<a href="#equipements">{{ __('Équipements') }}</a>@endif
                    @if(!empty($accommodation->room_types))<a href="#chambres-disponibles">{{ __('Chambres') }}</a>@endif
                    <a href="#conditions">{{ __('Conditions') }}</a>
                    <a href="#localisation">{{ __('Localisation') }}</a>
                    @if($reviews->isNotEmpty())<a href="#avis">{{ __('Avis') }}</a>@endif
                </nav>

                {{-- ── APERÇU ─────────────────────────────────────────────── --}}
                <section class="reveal" id="apercu">
                    @if($accommodation->short_description)
                        <p class="hx-lead">{{ $accommodation->localized('short_description') }}</p>
                    @endif
                    <p class="hx-text">{{ $accommodation->localized('description') ?: __('Description non disponible.') }}</p>

                    <ul class="hx-facts">
                        @if($accommodation->check_in_time)
                            <li><span class="hx-facts__icon"><i class="fas fa-right-to-bracket" aria-hidden="true"></i></span><span><small>{{ __('Arrivée') }}</small>{{ \Illuminate\Support\Str::of($accommodation->check_in_time)->substr(0, 5) }}</span></li>
                        @endif
                        @if($accommodation->check_out_time)
                            <li><span class="hx-facts__icon"><i class="fas fa-right-from-bracket" aria-hidden="true"></i></span><span><small>{{ __('Départ') }}</small>{{ \Illuminate\Support\Str::of($accommodation->check_out_time)->substr(0, 5) }}</span></li>
                        @endif
                        <li><span class="hx-facts__icon"><i class="fas fa-city" aria-hidden="true"></i></span><span><small>{{ __('Ville') }}</small>{{ $accommodation->city?->name ?: 'N/A' }}</span></li>
                        <li><span class="hx-facts__icon"><i class="fas fa-phone" aria-hidden="true"></i></span><span class="min-w-0"><small>{{ __('Contact') }}</small><span class="block truncate">{{ $accommodation->phone ?: __('Via réservation') }}</span></span></li>
                    </ul>
                </section>

                {{-- ── ÉQUIPEMENTS ────────────────────────────────────────── --}}
                @if(!empty($accommodation->amenities))
                <section class="reveal" id="equipements">
                    <p class="hx-kicker">{{ __('Confort') }}</p>
                    <h2 class="hx-h2">{{ __('Équipements & services') }}</h2>
                    <ul class="hx-amen">
                        @foreach($accommodation->amenities as $am)
                            <li><span class="hx-amen__icon"><i class="{{ $am['icon'] ?? 'fas fa-check' }}" aria-hidden="true"></i></span>{{ $am['label'] ?? '' }}</li>
                        @endforeach
                    </ul>
                </section>
                @endif

                {{-- ── CHAMBRES ───────────────────────────────────────────── --}}
                @if(!empty($accommodation->room_types))
                <section class="reveal" id="chambres-disponibles">
                    <div class="hx-sechead">
                        <div>
                            <p class="hx-kicker">{{ __('Hébergement') }}</p>
                            <h2 class="hx-h2">{{ __('Chambres & logements disponibles') }}</h2>
                        </div>
                        <a href="{{ route('accommodations.rooms', $accommodation->slug) }}" class="hx-link">{{ __('Voir sur une page dédiée') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                    </div>
                    <div class="hx-rooms">
                        @foreach($accommodation->room_types as $room)
                        @php
                            $roomPhotos = array_values(array_filter((array) ($room['photos'] ?? [])));
                            $roomBadges = app(\App\Services\RoomRates::class)->badges($room);
                        @endphp
                        <article class="hx-room">
                            <div class="hx-room__media" @if(!empty($roomPhotos)) role="button" tabindex="0" onclick="openLightbox('{{ $roomPhotos[0] }}')" onkeydown="if(event.key==='Enter'){openLightbox('{{ $roomPhotos[0] }}')}" aria-label="{{ __('Agrandir la photo de :name', ['name' => $room['name'] ?? __('Chambre')]) }}" @endif>
                                @if(!empty($roomPhotos))
                                    <img src="{{ $roomPhotos[0] }}" alt="" loading="lazy" decoding="async">
                                    @if(count($roomPhotos) > 1)<span class="hx-room__count">+{{ count($roomPhotos) - 1 }}</span>@endif
                                @else
                                    <i class="fas fa-bed" aria-hidden="true"></i>
                                @endif
                            </div>
                            <div class="hx-room__body">
                                <h3 class="hx-room__name">{{ $room['name'] ?? __('Chambre') }}</h3>
                                <ul class="hx-room__specs">
                                    <li><i class="fas fa-user-group" aria-hidden="true"></i>{{ $room['max_adults'] ?? 2 }} {{ __('pers. max') }}</li>
                                    @if(!empty($room['beds']))<li><i class="fas fa-bed" aria-hidden="true"></i>{{ $room['beds'] }}</li>@endif
                                    @if(!empty($room['area_m2']))<li><i class="fas fa-ruler-combined" aria-hidden="true"></i>{{ $room['area_m2'] }} m²</li>@endif
                                </ul>
                                @if(!empty($room['amenities']))
                                    <p class="hx-room__amen">{{ is_array($room['amenities']) ? implode(' · ', $room['amenities']) : $room['amenities'] }}</p>
                                @endif
                                @if($roomBadges)
                                    <p class="hx-room__badges">@foreach($roomBadges as $badge)<span><i class="fas fa-tag" aria-hidden="true"></i>{{ $badge }}</span>@endforeach</p>
                                @endif
                                @if(!empty($room['conditions']))
                                    <p class="hx-room__cond"><i class="fas fa-circle-info" aria-hidden="true"></i>{{ $room['conditions'] }}</p>
                                @endif
                            </div>
                            <div class="hx-room__side">
                                @if(!empty($room['price_xof']))
                                    <p class="hx-room__price"><small>{{ __('À partir de') }}</small><strong>{{ number_format((int) $room['price_xof'], 0, ',', ' ') }}</strong> <em>{{ __('FCFA / nuit') }}</em></p>
                                    <button type="button" onclick="reserveThisRoom('{{ addslashes($room['name'] ?? __('Chambre')) }}')" class="ti-cta">
                                        {{ __('Choisir') }}<i class="fas fa-arrow-right" aria-hidden="true"></i>
                                    </button>
                                @else
                                    <p class="hx-room__price"><small>{{ __('Tarif') }}</small><strong>{{ __('Sur demande') }}</strong></p>
                                @endif
                            </div>
                        </article>
                        @endforeach
                    </div>
                </section>
                @endif

                {{-- ── CONDITIONS ─────────────────────────────────────────── --}}
                @php $freeDays = (new \App\Models\Reservation(['accommodation_id' => $accommodation->id]))->setRelation('accommodation', $accommodation)->freeCancellationDays(); @endphp
                <section class="reveal" id="conditions">
                    <p class="hx-kicker">{{ __('À savoir') }}</p>
                    <h2 class="hx-h2">{{ __('Conditions de réservation & d\'annulation') }}</h2>
                    <div class="hx-cond">
                        <div class="hx-cond__item is-ok">
                            <span class="hx-cond__icon"><i class="fas fa-rotate-left" aria-hidden="true"></i></span>
                            <p>{{ __('Annulation gratuite en ligne jusqu\'à') }} {{ $freeDays }} {{ __('jour') }}{{ $freeDays > 1 ? 's' : '' }} {{ __('avant l\'arrivée : l\'acompte est remboursé en totalité. Au-delà, l\'acompte n\'est plus remboursable.') }}</p>
                        </div>
                        <div class="hx-cond__item">
                            <span class="hx-cond__icon"><i class="fas fa-shield-halved" aria-hidden="true"></i></span>
                            <p>{{ __('Paiement sécurisé via CinetPay · solde réglé sur place') }}</p>
                        </div>
                        @if($accommodation->cancellation_policy)
                            <div class="hx-cond__item is-wide">
                                <span class="hx-cond__icon"><i class="fas fa-file-lines" aria-hidden="true"></i></span>
                                <p class="whitespace-pre-line">{{ $accommodation->cancellation_policy }}</p>
                            </div>
                        @endif
                    </div>
                </section>

                {{-- ── LOCALISATION (floutée avant réservation payée) ─────── --}}
                <section class="reveal" id="localisation">
                    <p class="hx-kicker">{{ __('Où se trouve l\'établissement') }}</p>
                    <h2 class="hx-h2">{{ __('Localisation') }}</h2>
                    <div class="hx-map">
                        @if($accommodation->city?->name)
                            <iframe src="https://www.google.com/maps?q={{ urlencode($accommodation->city->name) }}&z=12&output=embed"
                                    title="{{ __('Carte : :name', ['name' => $accommodation->city->name]) }}"
                                    loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                        @endif
                        <div class="hx-map__lock">
                            <span class="hx-cond__icon"><i class="fas fa-lock" aria-hidden="true"></i></span>
                            <div class="min-w-0">
                                <p class="hx-map__title">{{ __('Adresse exacte et itinéraire précis') }}</p>
                                <p class="hx-map__text">{{ __('Pour des raisons de sécurité, l\'adresse précise n\'est communiquée qu\'aux voyageurs ayant réservé et payé l\'acompte en ligne. Elle apparaîtra automatiquement sur votre page de confirmation.') }}</p>
                                <a href="#booking-module" class="hx-link">{{ __('Réserver pour débloquer') }}<i class="fas fa-arrow-right" aria-hidden="true"></i></a>
                            </div>
                        </div>
                    </div>
                </section>

                {{-- ── AVIS ────────────────────────────────────────────────── --}}
                @if($reviews->isNotEmpty())
                <section class="reveal" id="avis">
                    <div class="hx-sechead">
                        <div>
                            <p class="hx-kicker">{{ __('Retours d\'expérience') }}</p>
                            <h2 class="hx-h2">{{ __('Avis clients') }}</h2>
                        </div>
                        @if($hxRating > 0)
                            <span class="hx-score"><strong>{{ number_format($hxRating, 1, ',', ' ') }}</strong><span><i class="fas fa-star" aria-hidden="true"></i> {{ trans_choice(':count avis|:count avis', max($hxReviews, $reviews->count())) }}</span></span>
                        @endif
                    </div>
                    <div class="hx-reviews">
                        @foreach($reviews as $review)
                            @php $rvName = $review->author_name ?: ($review->user->full_name ?? __('Anonyme')); @endphp
                            <article class="hx-review">
                                <div class="hx-review__head">
                                    <span class="hx-review__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($rvName, 0, 1)) }}</span>
                                    <span class="min-w-0">
                                        <span class="hx-review__name">{{ $rvName }}</span>
                                        @if($review->is_verified)<span class="hx-review__ok"><i class="fas fa-circle-check" aria-hidden="true"></i>{{ __('Séjour vérifié') }}</span>@endif
                                    </span>
                                    <span class="hx-review__stars" aria-label="{{ $review->rating }}/5">@for($s = 1; $s <= 5; $s++)<i class="fas fa-star {{ $s <= (int) $review->rating ? '' : 'is-off' }}" aria-hidden="true"></i>@endfor</span>
                                </div>
                                @if($review->title)<p class="hx-review__title">{{ $review->title }}</p>@endif
                                <p class="hx-review__text">{{ $review->comment }}</p>
                            </article>
                        @endforeach
                    </div>
                </section>
                @endif
            </div>

            {{-- ═══ SIDEBAR RÉSERVATION (sticky) ═══════════════════════════ --}}
            <div class="lg:col-span-2">
                <div class="lg:sticky lg:top-24 space-y-3 reveal" id="booking-anchor">
                    @if(!empty($accommodation->room_types))
                    @php
                        $depositPercent = app(\App\Services\ReservationPricingService::class)->depositPercent();
                        $cheapestRoom = collect($accommodation->room_types)->min('price_xof');
                        $canBook = auth()->check() && auth()->user()->hasVerifiedEmail();
                        // Lecture seule : ne crée pas de wallet juste pour afficher un solde à 0.
                        $walletBalanceXof = $canBook ? (int) (\App\Models\Wallet::where('user_id', auth()->id())->value('balance_available_xof') ?? 0) : 0;
                    @endphp
                    @include('partials.trust-badges', ['provider' => $accommodation->provider, 'freeCancelDays' => $freeDays ?? 0])
                    @if($accommodation->provider?->gift_cards_enabled)<div class="mb-3">@include('partials.gift-card-cta', ['provider' => $accommodation->provider])</div>@endif
                    <div class="booking-card rounded-3xl p-5 relative" id="booking-module" data-deposit-percent="{{ $depositPercent }}">
                        @if($hxPrices->isNotEmpty())
                        <div class="hx-book-head">
                            <p class="m-0"><small>{{ __('À partir de') }}</small><strong>{{ number_format((int) $hxPrices->min(), 0, ',', ' ') }}</strong> <em>{{ __('FCFA / nuit') }}</em></p>
                            @if($hxRating > 0)<span class="hx-book-rate"><i class="fas fa-star" aria-hidden="true"></i>{{ number_format($hxRating, 1, ',', ' ') }}</span>@endif
                        </div>
                        @endif
                        <h2 class="font-serif text-xl font-bold mb-1">{{ __('Réserver une chambre') }}</h2>
                        <p class="text-[#557466] text-xs mb-4">{{ __('Acompte de') }} {{ rtrim(rtrim(number_format($depositPercent, 1), '0'), '.') }}{{ __('% payé en ligne · solde réglé sur place à l\'hôtel.') }}</p>


                        <div>
                        <div class="grid grid-cols-1 gap-3 mb-5">
                            @foreach($accommodation->room_types as $room)
                            <button type="button"
                                    class="bkp-room-card text-left rounded-2xl border border-black/8 bg-white hover:border-orange-400/50 p-3.5 flex items-center gap-3"
                                    data-room-id="{{ $room['id'] ?? '' }}"
                                    data-room-name="{{ $room['name'] ?? __('Chambre') }}"
                                    data-room-price="{{ (int) ($room['price_xof'] ?? 0) }}">
                                @if(!empty($room['thumbnail']))
                                <div class="w-16 h-16 shrink-0 rounded-xl overflow-hidden bg-black/5">
                                    <img src="{{ $room['thumbnail'] }}" alt="" class="w-full h-full object-cover">
                                </div>
                                @endif
                                <div class="min-w-0 flex-1">
                                    <p class="text-[#0E3B2A] text-sm font-semibold truncate">{{ $room['name'] ?? __('Chambre') }}</p>
                                    <p class="text-[#557466] text-xs mt-0.5">
                                        <i class="fas fa-user text-[#EB6E00]/70 mr-1"></i>{{ $room['max_adults'] ?? 2 }} {{ __('pers. max') }}
                                        @if(!empty($room['area_m2'])) · {{ $room['area_m2'] }} m² @endif
                                    </p>
                                    <p class="text-[#EB6E00] text-sm font-bold mt-1">
                                        {{ number_format((int) ($room['price_xof'] ?? 0), 0, ',', ' ') }} {{ __('FCFA') }}<span class="text-[#557466] font-normal text-xs">{{ __('/nuit') }}</span>
                                    </p>
                                    @foreach(app(\App\Services\RoomRates::class)->badges($room) as $badge)
                                        <span class="inline-block mt-1 mr-1 px-2 py-0.5 rounded-full bg-[#00874F]/10 text-[#00874F] text-[11px] font-semibold">{{ $badge }}</span>
                                    @endforeach
                                </div>
                            </button>
                            @endforeach
                        </div>

                        {{-- Placé AVANT les champs de date : le calendrier s'ouvre juste en
                             dessous d'eux et recouvrirait ce message s'il était positionné
                             après (constaté : invisible une fois le calendrier ouvert). --}}
                        <p id="bkp-dates-hint" class="text-sm text-[#24503D] font-medium leading-snug mb-2 flex items-start gap-2">
                            <i class="fas fa-calendar-days text-[#EB6E00] mt-0.5"></i>
                            <span>{{ __('Choisissez d\'abord une chambre pour voir ses disponibilités.') }}</span>
                        </p>
                        <div id="bkp-dates-legend" class="hidden items-center gap-2.5 mb-3 flex-wrap">
                            <span class="inline-flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-emerald-50 border border-emerald-200 text-xs font-semibold text-emerald-700">
                                <span class="w-5 h-5 rounded-md border border-emerald-300 bg-white flex items-center justify-center text-[10px] font-bold text-emerald-700">12</span>
                                <i class="fas fa-circle-check text-emerald-500"></i>
                                {{ __('Disponible') }}
                            </span>
                            <span class="inline-flex items-center gap-2 pl-1.5 pr-3 py-1 rounded-full bg-red-50 border border-red-200 text-xs font-semibold text-red-600">
                                <span class="w-5 h-5 rounded-md bg-red-100 border border-red-200 flex items-center justify-center text-[10px] font-bold text-red-400 line-through">12</span>
                                <i class="fas fa-ban text-red-400"></i>
                                {{ __('Indisponible') }}
                            </span>
                        </div>
                        <div id="bkp-dates-wrap" class="grid grid-cols-2 gap-2.5 mb-3">
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#557466] mb-1">{{ __('Arrivée') }}</label>
                                <input type="text" id="bkp-checkin" readonly placeholder="{{ __('Sélectionner') }}"
                                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2 text-sm cursor-pointer">
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#557466] mb-1">{{ __('Départ') }}</label>
                                <input type="text" id="bkp-checkout" readonly placeholder="{{ __('Sélectionner') }}"
                                       class="w-full bg-white border border-black/10 rounded-xl px-3 py-2 text-sm cursor-pointer">
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 mb-4">
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#557466] mb-1">{{ __('Chambres') }}</label>
                                <div class="flex items-center bg-white border border-black/10 rounded-xl overflow-hidden">
                                    <button type="button" class="bkp-step w-9 h-9 text-[#557466] hover:text-[#EB6E00]" data-target="bkp-rooms" data-step="-1">−</button>
                                    <span id="bkp-rooms" class="flex-1 text-center text-sm font-semibold" data-value="1">1</span>
                                    <button type="button" class="bkp-step w-9 h-9 text-[#557466] hover:text-[#EB6E00]" data-target="bkp-rooms" data-step="1">+</button>
                                </div>
                            </div>
                            <div>
                                <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#557466] mb-1">{{ __('Voyageurs') }}</label>
                                <div class="flex items-center bg-white border border-black/10 rounded-xl overflow-hidden">
                                    <button type="button" class="bkp-step w-9 h-9 text-[#557466] hover:text-[#EB6E00]" data-target="bkp-guests" data-step="-1">−</button>
                                    <span id="bkp-guests" class="flex-1 text-center text-sm font-semibold" data-value="2">2</span>
                                    <button type="button" class="bkp-step w-9 h-9 text-[#557466] hover:text-[#EB6E00]" data-target="bkp-guests" data-step="1">+</button>
                                </div>
                            </div>
                        </div>

                        <div id="bkp-breakdown" class="hidden rounded-2xl border border-orange-400/25 bg-orange-50 px-4 py-3.5 mb-4 text-sm">
                            <div class="flex items-center justify-between text-[#24503D] mb-1">
                                <span id="bkp-nights-label"></span>
                                <span id="bkp-total" class="text-[#0E3B2A] font-medium"></span>
                            </div>
                            <div id="bkp-discount-row" class="hidden flex items-center justify-between text-[#00874F] font-semibold">
                                <span id="bkp-discount-label"></span>
                                <span id="bkp-discount"></span>
                            </div>
                            <div id="bkp-promo-row" class="hidden flex items-center justify-between text-[#00874F] font-semibold">
                                <span id="bkp-promo-label"></span>
                                <span id="bkp-promo"></span>
                            </div>
                            <p id="bkp-variable" class="hidden text-xs text-[#557466]">{{ __('Tarif variable selon les dates (week-end, saison).') }}</p>
                            <div class="flex items-center justify-between pt-1.5 mt-1.5 border-t border-orange-400/20">
                                <span class="text-[#24503D]">{{ __('Acompte à payer maintenant') }}</span>
                                <span id="bkp-deposit" class="text-[#EB6E00] font-bold"></span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-2.5 mb-3">
                            <input type="text" id="bkp-full-name" placeholder="{{ __('Nom complet') }}" value="{{ auth()->user()?->full_name }}" {{ auth()->check() ? 'readonly' : '' }} autocomplete="name" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm placeholder:text-[#8AA99B] {{ auth()->check() ? 'bg-black/[0.03] text-[#24503D]' : '' }}">
                            <input type="tel" id="bkp-phone" placeholder="{{ __('Téléphone') }}" value="{{ auth()->user()?->phone }}" {{ auth()->user()?->phone ? 'readonly' : '' }} autocomplete="tel" inputmode="tel" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm placeholder:text-[#8AA99B] {{ auth()->user()?->phone ? 'bg-black/[0.03] text-[#24503D]' : '' }}">
                            <input type="email" id="bkp-email" placeholder="{{ __('Email') }}" value="{{ auth()->user()?->email }}" {{ auth()->check() ? 'readonly' : '' }} autocomplete="email" inputmode="email" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm placeholder:text-[#8AA99B] {{ auth()->check() ? 'bg-black/[0.03] text-[#24503D]' : '' }}">
                            @unless($canBook)
                                <p class="bkx-note"><i class="fas fa-circle-info" aria-hidden="true"></i>{{ auth()->check() ? __('Un code de confirmation sera envoyé à votre adresse avant le paiement.') : __('Pas encore de compte ? Il est créé pour vous : un code de confirmation sera envoyé à cette adresse avant le paiement.') }}</p>
                            @endunless
                            @if($accommodation->provider_id && \App\Models\ProviderPromoCode::where('provider_id', $accommodation->provider_id)->where('is_active', true)->exists())
                                <div>
                                    <input type="text" id="bkp-promo-code" maxlength="30" autocapitalize="characters" placeholder="{{ __('Code promo (facultatif)') }}" class="w-full bg-white border border-black/10 rounded-xl px-3 py-2.5 text-sm uppercase placeholder:normal-case placeholder:text-[#8AA99B]">
                                    <p id="bkp-promo-error" class="hidden mt-1 text-xs text-red-700"></p>
                                </div>
                            @endif
                        </div>

                        @if($canBook)
                        <div class="mb-3">
                            <label class="block text-[10px] font-semibold uppercase tracking-widest text-[#557466] mb-1.5">{{ __('Moyen de paiement') }}</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" id="bkp-pay-cinetpay" data-method="cinetpay"
                                        class="bkp-pay-method is-active text-left rounded-xl border-2 border-black/8 bg-white px-3 py-2.5">
                                    <span class="block text-[#0E3B2A] text-xs font-bold"><i class="fas fa-mobile-screen mr-1"></i>{{ __('CinetPay') }}</span>
                                    <span class="block text-[#557466] text-[10px] mt-0.5">{{ __('Mobile Money / Carte') }}</span>
                                </button>
                                <button type="button" id="bkp-pay-wallet" data-method="wallet"
                                        class="bkp-pay-method text-left rounded-xl border-2 border-black/8 bg-white px-3 py-2.5">
                                    <span class="block text-[#0E3B2A] text-xs font-bold"><i class="fas fa-wallet mr-1"></i>{{ __('Mon solde Wallet') }}</span>
                                    <span class="block text-[#557466] text-[10px] mt-0.5">{{ number_format($walletBalanceXof, 0, ',', ' ') }} {{ __('FCFA disponible') }}</span>
                                </button>
                            </div>
                        </div>
                        @endif

                        <div id="bkp-error" class="hidden mb-3 px-3 py-2 bg-red-50 border border-red-200 rounded-lg text-red-700 text-xs" role="alert"></div>

                        @unless($canBook)
                        <div id="bkx" class="bkx" hidden>
                            @csrf
                            <p class="bkx-title"><i class="fas fa-envelope-open-text" aria-hidden="true"></i>{{ __('Confirmez votre e-mail') }}</p>
                            <p class="bkx-text">{{ __('Code à 6 chiffres envoyé à') }} <b data-bkx-email></b>. {{ __('Valable 10 minutes.') }}</p>
                            <label for="bkx-code" class="sr-only">{{ __('Code de confirmation') }}</label>
                            <input id="bkx-code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="14" placeholder="••••••" class="bkx-input">
                            <p class="bkx-err" data-bkx-err role="alert"></p>
                            <div class="bkx-actions">
                                <button type="button" class="bkx-ok" data-bkx-verify>{{ __('Confirmer et payer') }}</button>
                                <button type="button" class="bkx-link" data-bkx-resend>{{ __('Renvoyer le code') }}</button>
                                <button type="button" class="bkx-link" data-bkx-edit>{{ __('Modifier l\'e-mail') }}</button>
                            </div>
                            <p class="bkx-hint">{{ __('Pensez à regarder dans les courriers indésirables.') }}</p>
                        </div>
                        @endunless

                        <button type="button" id="bkp-submit" disabled
                                class="btn-primary btn-shine flex items-center justify-center gap-2.5 w-full px-5 py-4 rounded-2xl font-extrabold text-[15px] disabled:opacity-50 disabled:pointer-events-none">
                            <i class="fas fa-lock text-sm"></i>
                            <span id="bkp-submit-label">{{ __('Sélectionnez une chambre') }}</span>
                        </button>
                        <p class="text-center text-xs text-[#8AA99B] py-1 mt-2">{{ __('Paiement sécurisé via CinetPay · solde réglé sur place') }}</p>
                        </div>
                    </div>
                    @elseif($accommodation->provider && ($accommodation->provider->reserve_url || $accommodation->provider->website))
                    <div class="booking-card rounded-3xl p-5">
                        <h2 class="font-serif text-xl font-bold mb-1">{{ __('Contacter cet établissement') }}</h2>
                        <p class="text-[#557466] text-xs mb-4">{{ __('Réservation directement auprès du prestataire.') }}</p>
                        <a href="{{ $accommodation->provider->reserve_url ?: $accommodation->provider->website }}"
                           target="_blank" rel="noopener noreferrer"
                           class="btn-primary btn-shine flex items-center justify-between w-full px-5 py-4 rounded-2xl font-extrabold text-[15px] group">
                            <span class="flex items-center gap-2.5"><i class="fas fa-hotel text-sm"></i> {{ __('Effectuer une réservation') }}</span>
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
        <section class="mt-16 reveal">
            <p class="hx-kicker">{{ __('Dans la même ville') }}</p>
            <h2 class="hx-h2">{{ __('Autres hébergements') }}</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach($related as $r)
                    @include('partials.home-sector-card', ['it' => \App\Support\HomeSectorShowcase::card($r, 'hotels')])
                @endforeach
            </div>
        </section>
        @endif
    </div>

    @if(!empty($accommodation->room_types))
    <div class="mobile-cta-bar fixed bottom-0 inset-x-0 z-40 lg:hidden px-4 py-3 flex items-center gap-3">
        <div class="flex-1 min-w-0">
            <p class="text-[#557466] text-[10px] uppercase font-semibold">{{ __('À partir de') }}</p>
            <p class="font-bold text-sm truncate">{{ number_format((int) ($cheapestRoom ?? 0), 0, ',', ' ') }} {{ __('FCFA') }}<span class="text-[#557466] font-normal text-xs">{{ __('/nuit') }}</span></p>
        </div>
        <a href="{{ route('accommodations.rooms', $accommodation->slug) }}" class="btn-primary btn-shine shrink-0 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-extrabold text-xs">
            <i class="fas fa-bed"></i> {{ __('Réserver') }}
        </a>
    </div>
    @endif

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0E3B2A]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

    <div class="pb-20 lg:pb-0">
@include('partials.homepage-footer')
    </div>
@include('partials.place-card-script')
@include('partials.image-protection')
<script>
function openLightbox(url) {
    const img = document.getElementById('lightbox-img');
    img.src = url;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}

function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}

// ── Galerie héro : affiche la photo/vidéo cliquée dans le cadre principal ──
function setHeroPhoto(url) {
    const img = document.getElementById('hero-cover-img');
    const empty = document.getElementById('hero-cover-empty');
    const videoWrap = document.getElementById('hero-cover-video-wrap');
    const iframe = document.getElementById('hero-cover-video-iframe');
    const native = document.getElementById('hero-cover-video-native');

    videoWrap.classList.add('hidden');
    if (iframe) iframe.src = '';
    if (native) native.src = '';

    if (empty) empty.classList.add('hidden');
    if (img) {
        // Le srcset généré pour la première photo prendrait le pas sur le nouveau src : on le retire.
        img.removeAttribute('srcset');
        img.removeAttribute('sizes');
        img.src = url;
        img.classList.remove('hidden');
    }
    document.getElementById('hero-zoom-hint')?.classList.remove('hidden');
}

function setHeroVideo(embedUrl, directUrl) {
    const img = document.getElementById('hero-cover-img');
    const empty = document.getElementById('hero-cover-empty');
    const videoWrap = document.getElementById('hero-cover-video-wrap');
    const iframe = document.getElementById('hero-cover-video-iframe');
    const native = document.getElementById('hero-cover-video-native');

    if (img) img.classList.add('hidden');
    if (empty) empty.classList.add('hidden');
    document.getElementById('hero-zoom-hint')?.classList.add('hidden');
    videoWrap.classList.remove('hidden');

    if (embedUrl) {
        iframe.src = embedUrl + '?autoplay=1&mute=1&muted=1&playsinline=1';
        iframe.classList.remove('hidden');
        native.classList.add('hidden');
        native.src = '';
    } else {
        native.src = directUrl;
        native.muted = true;
        native.classList.remove('hidden');
        iframe.classList.add('hidden');
        iframe.src = '';
        native.play?.().catch(() => {});
    }
}

// ── Défilement automatique de la galerie héro, sans clic ──────────────────
(function () {
    const jsonEl = document.getElementById('hero-gallery-json');
    if (!jsonEl) return;
    const items = JSON.parse(jsonEl.textContent);
    if (items.length <= 1) return;

    let heroIndex = 0;

    const counter = document.getElementById('hero-index');
    const thumbs = Array.from(document.querySelectorAll('.hx-gal__thumb'));
    let autoplay = true;

    function render() {
        const item = items[heroIndex];
        if (item.type === 'video') {
            setHeroVideo(item.embedUrl, item.url);
        } else {
            setHeroPhoto(item.url);
        }
        if (counter) counter.textContent = heroIndex + 1;
        thumbs.forEach(function (t) {
            const on = Number(t.dataset.heroIndex) === heroIndex;
            t.classList.toggle('is-active', on);
            t.setAttribute('aria-current', on ? 'true' : 'false');
            if (on) t.scrollIntoView({ block: 'nearest', inline: 'nearest' });
        });
    }

    // Choix du visiteur : arrête le défilement automatique.
    window.showHeroItem = function (i, fromTimer) {
        if (!fromTimer) autoplay = false;
        heroIndex = ((i % items.length) + items.length) % items.length;
        render();
    };

    window.heroSlide = function (dir) {
        window.showHeroItem(heroIndex + dir);
    };

    // Clavier : flèches gauche / droite quand la galerie a le focus.
    document.querySelector('.hx-gal')?.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowLeft') { e.preventDefault(); window.heroSlide(-1); }
        if (e.key === 'ArrowRight') { e.preventDefault(); window.heroSlide(1); }
    });

    setInterval(function () {
        if (autoplay && !document.hidden) window.showHeroItem(heroIndex + 1, true);
    }, 5000);
})();
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeLightbox(); });

// ── Booking module (chambre + acompte en ligne) ────────────────────────────
(function () {
    const module = document.getElementById('booking-module');
    if (!module) return;

    const depositPercent = parseFloat(module.dataset.depositPercent || '30');
    let selectedRoom = null;
    let paymentMethod = 'cinetpay';
    window.tivCanBook = {{ $canBook ?? false ? 'true' : 'false' }};
    let blockedRanges = []; // périodes déjà réservées pour la chambre sélectionnée
    let availabilityToken = 0; // ignore les réponses obsolètes si l'utilisateur change vite de chambre

    const checkin = document.getElementById('bkp-checkin');
    const checkout = document.getElementById('bkp-checkout');
    const datesWrap = document.getElementById('bkp-dates-wrap');
    const datesHint = document.getElementById('bkp-dates-hint');
    const datesHintText = datesHint.querySelector('span'); // le <p> a aussi une icône, on ne touche qu'au texte
    const datesLegend = document.getElementById('bkp-dates-legend');
    const submitBtn = document.getElementById('bkp-submit');
    const submitLabel = document.getElementById('bkp-submit-label');
    const breakdown = document.getElementById('bkp-breakdown');
    const errorBox = document.getElementById('bkp-error');

    // Formatage local (pas toISOString(), qui décale selon le fuseau horaire local
    // — un décalage d'un jour ici recréerait exactement le bug qu'on cherche à éviter).
    function toISO(date) {
        const y = date.getFullYear();
        const m = String(date.getMonth() + 1).padStart(2, '0');
        const d = String(date.getDate()).padStart(2, '0');
        return y + '-' + m + '-' + d;
    }

    function addDays(iso, n) {
        const d = new Date(iso + 'T00:00:00');
        d.setDate(d.getDate() + n);
        return toISO(d);
    }

    // Chevauchement [aStart, aEnd) / [bStart, bEnd) — même règle que côté serveur
    // (Reservation::hasConflict) : le jour de départ d'une réservation reste libre
    // pour une arrivée le même jour (usage hôtelier standard).
    function rangesOverlap(aStart, aEnd, bStart, bEnd) {
        return aStart < bEnd && aEnd > bStart;
    }

    function selectionOverlapsBlocked(inIso, outIso) {
        return blockedRanges.some((r) => rangesOverlap(inIso, outIso, r.check_in, r.check_out));
    }

    function formatFr(date) {
        return String(date.getDate()).padStart(2, '0') + '/' + String(date.getMonth() + 1).padStart(2, '0') + '/' + date.getFullYear();
    }

    // Calendrier de disponibilité — une seule instance flatpickr en mode "range",
    // partagée par les deux champs Arrivée/Départ (cliquer sur l'un ou l'autre
    // ouvre le même calendrier). Désactivé tant qu'aucune chambre n'est choisie.
    // Pas d'altInput ici : nos deux champs stylés (Arrivée/Départ) jouent déjà ce
    // rôle — on écrit nous-mêmes leur affichage (jj/mm/aaaa) et leur valeur ISO
    // exploitable (dataset.iso), plutôt que de laisser flatpickr imposer sa propre
    // chaîne "date1 to date2" dans le champ Arrivée en mode range.
    const picker = window.flatpickr(checkin, {
        mode: 'range',
        locale: 'fr',
        dateFormat: 'Y-m-d',
        minDate: 'today',
        disable: [],
        // Toujours en dessous du champ, jamais au-dessus : le positionnement
        // automatique de flatpickr peut sinon basculer le calendrier vers le
        // haut par manque de place visible, recouvrant le message et la
        // légende juste au-dessus des champs de date.
        position: 'below',
        static: true,
        onChange: function (selectedDates) {
            if (selectedDates.length >= 1) {
                checkin.value = formatFr(selectedDates[0]);
                checkin.dataset.iso = toISO(selectedDates[0]);
            }
            if (selectedDates.length === 2) {
                const inIso = checkin.dataset.iso;
                const outIso = toISO(selectedDates[1]);
                if (selectionOverlapsBlocked(inIso, outIso)) {
                    // Sécurité en plus du calendrier lui-même (qui empêche déjà de
                    // sélectionner à travers une date désactivée) : si malgré tout la
                    // période chevauche une réservation, on refuse et on réinitialise.
                    errorBox.textContent = {{ \Illuminate\Support\Js::from(__('Cette période n\'est plus disponible pour cette chambre. Merci de choisir d\'autres dates.')) }};
                    errorBox.classList.remove('hidden');
                    picker.clear();
                    checkin.value = '';
                    checkin.dataset.iso = '';
                    checkout.value = '';
                    checkout.dataset.iso = '';
                    recompute();
                    return;
                }
                errorBox.classList.add('hidden');
                checkout.value = formatFr(selectedDates[1]);
                checkout.dataset.iso = outIso;
            } else {
                checkout.value = '';
                checkout.dataset.iso = '';
            }
            recompute();
        },
    });
    checkout.addEventListener('click', () => picker.open());

    function setDatesEnabled(enabled) {
        datesWrap.classList.toggle('opacity-50', !enabled);
        datesWrap.classList.toggle('pointer-events-none', !enabled);
        picker.set('clickOpens', enabled);
    }
    setDatesEnabled(false);

    document.querySelectorAll('.bkp-pay-method').forEach((btn) => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.bkp-pay-method').forEach((b) => b.classList.remove('is-active'));
            btn.classList.add('is-active');
            paymentMethod = btn.dataset.method;
        });
    });

    document.querySelectorAll('.bkp-room-card').forEach((card) => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.bkp-room-card').forEach((c) => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            selectedRoom = {
                id: card.dataset.roomId || null,
                name: card.dataset.roomName,
                price: parseInt(card.dataset.roomPrice, 10) || 0,
            };

            // Chaque chambre a son propre calendrier : on repart d'une sélection
            // vierge et on recharge ses disponibilités avant de rouvrir le calendrier.
            picker.clear();
            checkin.value = '';
            checkin.dataset.iso = '';
            checkout.value = '';
            checkout.dataset.iso = '';
            blockedRanges = [];
            picker.set('disable', []);
            setDatesEnabled(false);
            datesHintText.textContent = {{ \Illuminate\Support\Js::from(__('Chargement des disponibilités...')) }};
            datesHint.classList.remove('hidden');
            datesLegend.classList.add('hidden');
            datesWrap.classList.add('bkp-date-loading');
            recompute();

            const myToken = ++availabilityToken;
            const params = new URLSearchParams({ accommodation_id: '{{ $accommodation->id }}' });
            if (selectedRoom.id) params.set('room_id', selectedRoom.id);
            else params.set('room_name', selectedRoom.name);

            fetch('{{ route("reservations.availability") }}?' + params.toString(), { headers: { Accept: 'application/json' } })
                .then((res) => res.json())
                .then((data) => {
                    if (myToken !== availabilityToken) return; // réponse obsolète (chambre changée depuis)
                    datesWrap.classList.remove('bkp-date-loading');
                    setDatesEnabled(true);
                    if (data.success) {
                        blockedRanges = data.blocked_ranges || [];
                        // La date de départ d'une réservation existante reste sélectionnable
                        // (arrivée le même jour) : on désactive donc jusqu'à la veille du départ.
                        picker.set('disable', blockedRanges.map((r) => ({ from: r.check_in, to: addDays(r.check_out, -1) })));
                        datesHintText.textContent = blockedRanges.length
                            ? {{ \Illuminate\Support\Js::from(__('Voici les jours disponibles pour cette chambre : les dates déjà réservées sont grisées et barrées ci-dessous.')) }}
                            : {{ \Illuminate\Support\Js::from(__('Voici les jours disponibles pour cette chambre : toutes les dates sont libres.')) }};
                        datesLegend.classList.remove('hidden');
                        datesLegend.classList.add('flex');
                    } else {
                        datesHintText.textContent = {{ \Illuminate\Support\Js::from(__('Disponibilités indisponibles pour le moment — vérification faite à la réservation.')) }};
                    }
                    // Le calendrier s'ouvre automatiquement après la sélection d'une chambre.
                    picker.open();
                })
                .catch(() => {
                    if (myToken !== availabilityToken) return;
                    datesWrap.classList.remove('bkp-date-loading');
                    setDatesEnabled(true);
                    datesHintText.textContent = {{ \Illuminate\Support\Js::from(__('Disponibilités indisponibles pour le moment — vérification faite à la réservation.')) }};
                    picker.open();
                });
        });
    });

    document.querySelectorAll('.bkp-step').forEach((btn) => {
        btn.addEventListener('click', () => {
            const el = document.getElementById(btn.dataset.target);
            if (!el) return;
            const step = parseInt(btn.dataset.step, 10);
            const current = parseInt(el.dataset.value || '1', 10);
            const next = Math.max(1, current + step);
            el.dataset.value = String(next);
            el.textContent = String(next);
            recompute();
        });
    });

    function nightsBetween(inStr, outStr) {
        if (!inStr || !outStr) return 0;
        const diff = Math.round((new Date(outStr + 'T00:00:00') - new Date(inStr + 'T00:00:00')) / 86400000);
        return diff > 0 ? diff : 0;
    }

    function recompute() {
        if (!selectedRoom) {
            submitLabel.textContent = {{ \Illuminate\Support\Js::from(__('Sélectionnez une chambre')) }};
            submitBtn.disabled = true;
            breakdown.classList.add('hidden');
            return;
        }
        const nights = nightsBetween(checkin.dataset.iso, checkout.dataset.iso);
        const rooms = parseInt(document.getElementById('bkp-rooms').dataset.value || '1', 10);
        if (nights <= 0) {
            submitLabel.textContent = {{ \Illuminate\Support\Js::from(__('Choisissez vos dates')) }};
            submitBtn.disabled = true;
            breakdown.classList.add('hidden');
            return;
        }
        const total = selectedRoom.price * nights * rooms;
        const deposit = Math.round(total * depositPercent / 100);
        document.getElementById('bkp-nights-label').textContent =
            nights + ' nuit' + (nights > 1 ? 's' : '') + ' × ' + rooms + ' chambre' + (rooms > 1 ? 's' : '');
        document.getElementById('bkp-total').textContent = total.toLocaleString('fr-FR') + ' FCFA';
        document.getElementById('bkp-deposit').textContent = deposit.toLocaleString('fr-FR') + ' FCFA';
        breakdown.classList.remove('hidden');
        submitLabel.textContent = {{ \Illuminate\Support\Js::from(__('Réserver et payer l\'acompte (')) }} + deposit.toLocaleString('fr-FR') + ' FCFA)';
        submitBtn.disabled = false;
        refreshQuote(rooms);
    }

    // Devis serveur (saisons, week-end, remises) : il remplace le calcul local, qui ne sert que d'affichage immédiat.
    let quoteCtrl = null;
    async function refreshQuote(rooms) {
        if (!selectedRoom || !checkin.dataset.iso || !checkout.dataset.iso) return;
        quoteCtrl?.abort(); quoteCtrl = new AbortController();
        const q = new URLSearchParams({ accommodation_id: {{ (int) $accommodation->id }}, check_in: checkin.dataset.iso, check_out: checkout.dataset.iso, rooms_count: rooms });
        if (selectedRoom.id) q.set('room_id', selectedRoom.id); else q.set('room_name', selectedRoom.name);
        const promoInput = document.getElementById('bkp-promo-code');
        if (promoInput?.value.trim()) q.set('promo_code', promoInput.value.trim());
        try {
            const r = await fetch({{ \Illuminate\Support\Js::from(route('reservations.quote')) }} + '?' + q, { headers: { Accept: 'application/json' }, signal: quoteCtrl.signal });
            const d = await r.json();
            if (!d.success) return;
            document.getElementById('bkp-total').textContent = d.subtotal.toLocaleString('fr-FR') + ' FCFA';
            const row = document.getElementById('bkp-discount-row');
            row.classList.toggle('hidden', !d.discount_xof);
            if (d.discount_xof) {
                document.getElementById('bkp-discount-label').textContent = d.discount_label;
                document.getElementById('bkp-discount').textContent = '− ' + d.discount_xof.toLocaleString('fr-FR') + ' FCFA';
            }
            const promoRow = document.getElementById('bkp-promo-row');
            promoRow.classList.toggle('hidden', !d.promo_discount_xof);
            if (d.promo_discount_xof) {
                document.getElementById('bkp-promo-label').textContent = {{ \Illuminate\Support\Js::from(__('Code promo')) }} + ' (' + d.promo_label + ')';
                document.getElementById('bkp-promo').textContent = '− ' + d.promo_discount_xof.toLocaleString('fr-FR') + ' FCFA';
            }
            const promoError = document.getElementById('bkp-promo-error');
            if (promoError) { promoError.textContent = d.promo_error || ''; promoError.classList.toggle('hidden', !d.promo_error); }
            document.getElementById('bkp-variable').classList.toggle('hidden', !d.variable);
            document.getElementById('bkp-deposit').textContent = d.deposit.toLocaleString('fr-FR') + ' FCFA';
            submitLabel.textContent = {{ \Illuminate\Support\Js::from(__('Réserver et payer l\'acompte (')) }} + d.deposit.toLocaleString('fr-FR') + ' FCFA)';
        } catch (e) { /* calcul local conservé */ }
    }

    document.getElementById('bkp-promo-code')?.addEventListener('change', () => recompute());

    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            errorBox.classList.add('hidden');
            const fullName = document.getElementById('bkp-full-name').value.trim();
            const email = document.getElementById('bkp-email').value.trim();
            const phone = document.getElementById('bkp-phone').value.trim();
            if (!fullName || !email) {
                errorBox.textContent = {{ \Illuminate\Support\Js::from(__('Merci de renseigner votre nom complet et votre email.')) }};
                errorBox.classList.remove('hidden');
                return;
            }
            if (!window.tivCanBook) { window.tivExpressStart?.(fullName, email, phone); return; }
            const originalLabel = submitLabel.textContent;
            submitBtn.disabled = true;
            submitLabel.textContent = {{ \Illuminate\Support\Js::from(__('Traitement en cours...')) }};
            try {
                const res = await fetch('{{ route("reservations.payment.initiate") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        accommodation_id: {{ $accommodation->id }},
                        room_id: selectedRoom.id,
                        room_name: selectedRoom.name,
                        room_price_xof: selectedRoom.price,
                        check_in: checkin.dataset.iso,
                        check_out: checkout.dataset.iso,
                        rooms_count: parseInt(document.getElementById('bkp-rooms').dataset.value || '1', 10),
                        guests_count: parseInt(document.getElementById('bkp-guests').dataset.value || '2', 10),
                        full_name: fullName,
                        email: email,
                        phone: phone,
                        payment_method: paymentMethod,
                        promo_code: document.getElementById('bkp-promo-code')?.value.trim() || null,
                    }),
                });
                const data = await res.json();
                if (data.success && data.redirect_url) {
                    window.location.href = data.redirect_url;
                    return;
                }
                errorBox.textContent = data.message || {{ \Illuminate\Support\Js::from(__('Une erreur est survenue, merci de réessayer.')) }};
                errorBox.classList.remove('hidden');
            } catch (e) {
                errorBox.textContent = {{ \Illuminate\Support\Js::from(__('Erreur réseau, merci de réessayer.')) }};
                errorBox.classList.remove('hidden');
            } finally {
                submitBtn.disabled = false;
                submitLabel.textContent = originalLabel;
            }
        });
    }
})();

// ── Depuis la page dédiée aux chambres : présélectionne la chambre choisie
// (?room=...) en réutilisant le sélecteur de carte déjà câblé ci-dessus.
(function () {
    const roomName = new URLSearchParams(window.location.search).get('room');
    if (!roomName) return;
    const card = document.querySelector('.bkp-room-card[data-room-name="' + roomName.replace(/"/g, '\\"') + '"]');
    if (card) card.click();
})();

// ── Depuis la liste illustrée des chambres (sur cette même page) : présélectionne
// la chambre dans le module de réservation puis y amène.
function reserveThisRoom(roomName) {
    const card = document.querySelector('.bkp-room-card[data-room-name="' + roomName.replace(/"/g, '\\"') + '"]');
    if (card) card.click();
    document.getElementById('booking-module')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

(function () {
    // Arrivée directe via ancre (#chambres-disponibles, #booking-module...) : le navigateur
    // y saute instantanément, souvent avant que l'IntersectionObserver n'ait eu la main —
    // la section resterait invisible (opacity:0) malgré le saut. On révèle tout d'un coup
    // dans ce cas précis plutôt que de laisser l'utilisateur atterrir sur du contenu masqué.
    if (window.location.hash) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const items = document.querySelectorAll('.reveal:not(.is-visible)');
    if (!('IntersectionObserver' in window) || !items.length) {
        document.querySelectorAll('.reveal').forEach(el => el.classList.add('is-visible'));
        return;
    }
    const io = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) { entry.target.classList.add('is-visible'); io.unobserve(entry.target); }
        });
    }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
    items.forEach(el => io.observe(el));
})();
</script>
@unless($canBook ?? false)
<script>
// ── Compte express : code envoyé par e-mail, saisi dans le module, puis paiement enchaîné ──
(function () {
    var box = document.getElementById('bkx');
    if (!box) return;
    var codeIn = document.getElementById('bkx-code'), errEl = box.querySelector('[data-bkx-err]'), emailEl = box.querySelector('[data-bkx-email]');
    var okBtn = box.querySelector('[data-bkx-verify]'), resendBtn = box.querySelector('[data-bkx-resend]'), editBtn = box.querySelector('[data-bkx-edit]');
    var submitBtn = document.getElementById('bkp-submit'), mainErr = document.getElementById('bkp-error');
    var urls = {{ \Illuminate\Support\Js::from(['start' => route('account.express.start'), 'verify' => route('account.express.verify')]) }};
    var txt = {{ \Illuminate\Support\Js::from(['net' => __('Erreur réseau, merci de réessayer.'), 'wait' => __('Renvoyer dans :s s'), 'resend' => __('Renvoyer le code'), 'sending' => __('Envoi du code…'), 'checking' => __('Vérification…'), 'ok' => __('Confirmer et payer'), 'digits' => __('Le code contient 6 chiffres.')]) }};
    var last = null, timer = null;

    function token() { return document.querySelector('meta[name="csrf-token"]')?.content || box.querySelector('input[name="_token"]')?.value || ''; }
    async function post(url, body) {
        var r = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token() }, body: JSON.stringify(body) });
        var d = {}; try { d = await r.json(); } catch (e) {}
        if (!r.ok && !d.message && d.errors) d.message = Object.values(d.errors)[0][0];
        return { ok: r.ok, data: d };
    }
    function showErr(el, m) { el.textContent = m || ''; if (el === mainErr) el.classList.toggle('hidden', !m); }
    function cooldown() {
        var s = 30; resendBtn.disabled = true;
        clearInterval(timer);
        timer = setInterval(function () { s--; resendBtn.textContent = s > 0 ? txt.wait.replace(':s', s) : txt.resend; if (s <= 0) { clearInterval(timer); resendBtn.disabled = false; } }, 1000);
        resendBtn.textContent = txt.wait.replace(':s', s);
    }

    window.tivExpressStart = async function (fullName, email, phone) {
        last = { full_name: fullName, email: email, phone: phone, website: '' };
        showErr(mainErr, ''); showErr(errEl, '');
        submitBtn.disabled = true;
        var label = document.getElementById('bkp-submit-label'), before = label.textContent;
        label.textContent = txt.sending;
        try {
            var res = await post(urls.start, last);
            if (res.data.status === 'ready') { window.tivCanBook = true; submitBtn.disabled = false; label.textContent = before; submitBtn.click(); return; }
            if (!res.ok) { showErr(mainErr, res.data.message || txt.net); return; }
            emailEl.textContent = res.data.email || email;
            box.hidden = false; submitBtn.style.display = "none";
            codeIn.value = ''; codeIn.focus();
            cooldown();
        } catch (e) { showErr(mainErr, txt.net); }
        finally { submitBtn.disabled = false; label.textContent = before; }
    };

    async function verify() {
        var code = codeIn.value.replace(/\D/g, '');
        if (code.length !== 6) { showErr(errEl, txt.digits); codeIn.focus(); return; }
        okBtn.disabled = true; okBtn.textContent = txt.checking; showErr(errEl, '');
        try {
            var res = await post(urls.verify, { code: code });
            if (!res.ok) { showErr(errEl, res.data.message || txt.net); codeIn.select(); return; }
            // Session renouvelée à la connexion : on reprend le nouveau jeton avant de payer.
            var meta = document.querySelector('meta[name="csrf-token"]'); if (meta && res.data.csrf) meta.setAttribute('content', res.data.csrf);
            window.tivCanBook = true;
            ['bkp-full-name', 'bkp-email'].forEach(function (id) { var el = document.getElementById(id); if (el) el.readOnly = true; });
            box.hidden = true; submitBtn.style.display = "";
            document.querySelectorAll('.bkx-note').forEach(function (n) { n.remove(); });
            submitBtn.click();
        } catch (e) { showErr(errEl, txt.net); }
        finally { okBtn.disabled = false; okBtn.textContent = txt.ok; }
    }

    codeIn.addEventListener('input', function () {
        codeIn.value = codeIn.value.replace(/\D/g, '').slice(0, 6);
        if (codeIn.value.length === 6) verify();
    });
    codeIn.addEventListener('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); verify(); } });
    okBtn.addEventListener('click', verify);
    resendBtn.addEventListener('click', function () { if (last) window.tivExpressStart(last.full_name, last.email, last.phone); });
    editBtn.addEventListener('click', function () {
        box.hidden = true; submitBtn.style.display = "";
        var em = document.getElementById('bkp-email'); if (em && !em.readOnly) { em.focus(); em.select(); }
    });
})();
</script>
@endunless
</body>
</html>
