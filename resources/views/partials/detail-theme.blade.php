{{--
    Fiches établissement (restaurants, sites, agences, loisirs, transports) : même langage visuel
    que la fiche hébergement. Présentation seule : en-tête éditorial, galerie en mosaïque, sections
    posées en cartes ; aplats aux couleurs du logo, boutons orange, aucun dégradé.
    À inclure après le bloc <style> de la page (corps marqué de la classe « dx »).
--}}
<style>
        .hx-back { display: inline-flex; align-items: center; gap: 8px; height: 38px; padding: 0 14px; border-radius: 999px; background: #fff; border: 1px solid #ece5d6; color: #9a4308; font-size: 13px; font-weight: 700; }
        .hx-back:hover { border-color: #ef7c2b; color: #9a4308; }
        .hx-back i { font-size: 11px; }
        .hx-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 18px; margin: 22px 0 22px; }
        .hx-pills { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px; }
        .hx-pill { display: inline-flex; align-items: center; gap: 7px; height: 30px; padding: 0 12px; border-radius: 999px; background: #fdf4ec; color: #9a4308; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
        .hx-pill.is-gold { background: #febf48; color: #1f1a0e; letter-spacing: .12em; }
        .hx-pill.is-ok { background: #eef6f0; color: #14532d; text-transform: none; letter-spacing: 0; }
        .hx-title { margin: 0; font-family: 'Playfair Display', serif; font-size: clamp(34px, 3.4vw + 14px, 56px); font-weight: 700; line-height: 1.04; letter-spacing: -.02em; color: #1d1a16; }
        .hx-meta { display: flex; flex-wrap: wrap; align-items: center; gap: 10px 18px; margin: 14px 0 0; font-size: 15px; color: #4a443a; }
        .hx-meta > span { display: inline-flex; align-items: center; gap: 7px; }
        .hx-meta i { color: #1b6b3a; font-size: 13px; }
        .hx-rate { height: 32px; padding: 0 12px; border-radius: 999px; background: #f6ecd5; color: #6f5416; font-weight: 700; font-size: 14px; }
        .hx-rate i { color: #b8892b !important; }
        .hx-rate small { font-weight: 500; font-size: 12px; }
        .hx-rate.is-new { background: #eef6f0; color: #14532d; }
        .hx-rate.is-new i { color: #1b6b3a !important; }
        .hx-actions { display: flex; align-items: center; gap: 10px; }
        .hx-actions .ti-round { background: #fff; border: 1px solid #ece5d6; box-shadow: none; }

        .hx-gallery { display: grid; grid-template-columns: 2fr 1fr 1fr; grid-template-rows: repeat(2, minmax(0, 1fr)); gap: 10px; height: clamp(340px, 52vh, 540px); }
        .hx-gallery.is-single { grid-template-columns: 1fr; grid-template-rows: 1fr; }
        .hx-gallery__main { grid-row: span 2; border-radius: 28px; overflow: hidden; position: relative; background: #f3eee4; }
        .hx-gallery__main img { transition: transform .7s ease; }
        .hx-gallery__main:hover img { transform: scale(1.03); }
        .hx-gallery__empty { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }
        .hx-gallery__empty i { width: 76px; height: 76px; border-radius: 999px; background: #fff; color: #ef7c2b; display: inline-flex; align-items: center; justify-content: center; font-size: 28px; }
        .hx-gallery__tile { position: relative; padding: 0; border: 0; border-radius: 22px; overflow: hidden; background: #f3eee4; cursor: pointer; }
        .hx-gallery__tile img { width: 100%; height: 100%; object-fit: cover; transition: transform .5s ease; }
        .hx-gallery__tile:hover img { transform: scale(1.07); }
        .hx-gallery__tile:focus-visible, .hx-arrow:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .hx-gallery__more { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; background: rgba(29,26,22,.55); color: #fff; font-size: 16px; font-weight: 700; }
        .hx-gallery.is-single .hx-gallery__main { grid-row: auto; }
        .hx-gallery.is-empty { height: 200px; }
        .hx-gallery.is-empty .hx-gallery__main { aspect-ratio: auto; }
        .hx-zoom, .hx-gallery__count { position: absolute; z-index: 2; display: inline-flex; align-items: center; justify-content: center; gap: 6px; border-radius: 999px; background: rgba(255,255,255,.95); color: #1d1a16; box-shadow: 0 2px 8px rgba(29,26,22,.18); }
        .hx-zoom { right: 16px; bottom: 16px; width: 42px; height: 42px; font-size: 14px; }
        .hx-gallery__count { left: 16px; bottom: 16px; height: 32px; padding: 0 12px; font-size: 13px; font-weight: 700; }
        .hx-arrow { position: absolute; top: 50%; z-index: 3; width: 46px; height: 46px; margin-top: -23px; border: 0; border-radius: 999px; background: rgba(255,255,255,.95); color: #1d1a16; display: inline-flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: 0 6px 18px rgba(29,26,22,.25); transition: transform .15s ease, background .15s ease; }
        .hx-arrow:hover { background: #b9500a; color: #fff; transform: scale(1.06); }
        .hx-arrow.is-prev { left: 16px; } .hx-arrow.is-next { right: 16px; }
        .hx-videos { display: flex; gap: 10px; margin-top: 10px; }
        .hx-videos__tile { position: relative; width: 120px; height: 80px; padding: 0; border: 0; border-radius: 16px; overflow: hidden; background: #1d1a16; cursor: pointer; }
        .hx-videos__tile img { width: 100%; height: 100%; object-fit: cover; }
        .hx-videos__play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; background: rgba(29,26,22,.35); }

        .hx-tabs { position: sticky; top: 76px; z-index: 20; display: flex; gap: 4px; padding: 6px; overflow-x: auto; scrollbar-width: none; background: rgba(255,255,255,.96); border: 1px solid #ece5d6; border-radius: 999px; box-shadow: 0 10px 30px -24px rgba(29,26,22,.5); }
        .hx-tabs::-webkit-scrollbar { display: none; }
        .hx-tabs a { flex: none; height: 38px; padding: 0 16px; border-radius: 999px; display: inline-flex; align-items: center; color: #4a443a; font-size: 14px; font-weight: 600; transition: background .15s ease, color .15s ease; }
        .hx-tabs a:hover { background: #fdf4ec; color: #9a4308; }
        .hx-kicker { margin: 0 0 6px; font-size: 12px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #b9500a; }
        .hx-h2 { margin: 0 0 18px; font-family: 'Playfair Display', serif; font-size: clamp(24px, 1.4vw + 16px, 30px); font-weight: 700; line-height: 1.15; color: #1d1a16; }
        .hx-sechead { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 10px; margin-bottom: 4px; }
        .hx-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 18px; color: #9a4308; font-size: 14px; font-weight: 700; }
        .hx-link i { font-size: 11px; transition: transform .2s ease; }
        .hx-link:hover i { transform: translateX(3px); }
        section[id] { scroll-margin-top: 150px; }

        .hx-lead { margin: 0 0 14px; font-family: 'Playfair Display', serif; font-size: clamp(20px, 1vw + 14px, 24px); line-height: 1.4; color: #1d1a16; }
        .hx-text { margin: 0; font-size: 16px; line-height: 1.75; color: #3d372c; white-space: pre-line; }
        .hx-facts { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 24px 0 0; padding: 0; list-style: none; }
        .hx-facts li { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 14px; border-radius: 20px; background: #fff; border: 1px solid #ece5d6; font-size: 15px; font-weight: 700; color: #1d1a16; }
        .hx-facts small { display: block; margin-bottom: 3px; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b6458; }
        .hx-facts__icon { width: 42px; height: 42px; flex-shrink: 0; border-radius: 14px; background: #fdf4ec; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }

        .hx-amen { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px; margin: 0; padding: 0; list-style: none; }
        .hx-amen li { display: flex; align-items: center; gap: 12px; min-width: 0; padding: 12px 14px; border-radius: 18px; background: #fff; border: 1px solid #ece5d6; font-size: 14px; font-weight: 600; color: #1d1a16; }
        .hx-amen__icon { width: 38px; height: 38px; flex-shrink: 0; border-radius: 999px; background: #eef6f0; color: #1b6b3a; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; }
        .hx-map { position: relative; border-radius: 28px; overflow: hidden; background: #f3eee4; border: 1px solid #ece5d6; }
        .hx-map iframe { display: block; width: 100%; height: 300px; border: 0; filter: saturate(.85); }
        .hx-map__lock { position: absolute; left: 14px; right: 14px; bottom: 14px; display: flex; gap: 14px; align-items: flex-start; padding: 16px; border-radius: 20px; background: rgba(255,255,255,.97); box-shadow: 0 12px 30px -16px rgba(29,26,22,.5); }
        .hx-map__title { margin: 0; font-size: 15px; font-weight: 700; color: #1d1a16; }
        .hx-map__text { margin: 4px 0 8px; font-size: 13px; line-height: 1.55; color: #6b6458; }
        .hx-map__lock .hx-link { margin: 0; }
        .hx-map:not(:has(iframe)) .hx-map__lock { position: static; }

        .hx-score { display: inline-flex; align-items: center; gap: 12px; margin-bottom: 18px; padding: 8px 16px 8px 8px; border-radius: 999px; background: #fff; border: 1px solid #ece5d6; font-size: 13px; color: #6b6458; }
        .hx-score strong { width: 46px; height: 46px; border-radius: 999px; background: #1b6b3a; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 17px; }
        .hx-score i { color: #b8892b; }
        .hx-reviews { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .hx-review { padding: 18px; border-radius: 22px; background: #fff; border: 1px solid #ece5d6; }
        .hx-review__head { display: flex; align-items: center; gap: 10px; }
        .hx-review__avatar { width: 40px; height: 40px; flex-shrink: 0; border-radius: 999px; background: #327661; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; }
        .hx-review__name { display: block; font-size: 14px; font-weight: 700; color: #1d1a16; }
        .hx-review__ok { display: inline-flex; align-items: center; gap: 5px; font-size: 12px; font-weight: 600; color: #1b6b3a; }
        .hx-review__stars { margin-left: auto; display: inline-flex; gap: 2px; color: #b8892b; font-size: 12px; }
        .hx-review__stars .is-off { color: #e3dccb; }
        .hx-review__title { margin: 12px 0 0; font-size: 14px; font-weight: 700; color: #1d1a16; }
        .hx-review__text { margin: 6px 0 0; font-size: 14px; line-height: 1.6; color: #4a443a; }

        /* Reprise des classes existantes des fiches */
        body.dx .btn-primary { background: #b9500a; color: #fff; box-shadow: 0 14px 30px -14px rgba(185,80,10,.7); }
        body.dx .btn-primary:hover { background: #9a4308; filter: none; }
        body.dx .section-kicker { margin-bottom: 6px; font-size: 12px; letter-spacing: .16em; color: #b9500a; }
        body.dx section > h2.font-serif { margin-bottom: 18px; font-size: clamp(24px, 1.4vw + 16px, 30px); line-height: 1.15; color: #1d1a16; }
        body.dx .bento-card { background: #fff; border: 1px solid #ece5d6; border-radius: 20px; box-shadow: none; transition: border-color .2s ease, transform .2s ease; }
        body.dx .bento-card:hover { border-color: #d8cdb8; }
        body.dx .bento-card.flex:hover { transform: translateY(-2px); }
        body.dx .bento-icon { width: 42px; height: 42px; border-radius: 14px; background: #fdf4ec; color: #b9500a; }
        body.dx .bento-card .w-14 { border-radius: 14px; }
        body.dx .dx-main h3.text-sm { color: #1b6b3a; letter-spacing: .12em; }
        body.dx .review-card { padding: 18px; background: #fff; border: 1px solid #ece5d6; border-radius: 22px; box-shadow: none; }
        body.dx .contact-card { background: #fff; border: 1px solid #ece5d6; border-radius: 30px; box-shadow: 0 26px 60px -36px rgba(29,26,22,.55); }
        body.dx .contact-card input:focus, body.dx .contact-card textarea:focus, body.dx .contact-card select:focus { outline: 2px solid #ef7c2b; outline-offset: 0; border-color: #ef7c2b; }
        body.dx .menu-sheet { border-radius: 26px; border-color: #ece5d6; background: #fff; }
        body.dx .menu-group__title { color: #1b6b3a; }
        body.dx .menu-sheet__more { color: #9a4308; }
        body.dx .hx-amen__icon i { color: inherit !important; }


        /* Aperçu, offres, localisation, avis (fiches hors hébergement) */
        .dx-muted { color: #8a7f6b; font-style: italic; }
        .dx-facts { grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .dx-facts li.is-wide { grid-column: 1 / -1; }
        .dx-facts a { color: inherit; }
        .dx-facts a:hover { color: #9a4308; }
        .dx-cat { display: flex; align-items: center; gap: 10px; margin: 0 0 12px; font-size: 13px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #14532d; }
        .dx-cat__icon { width: 32px; height: 32px; border-radius: 10px; background: #eef6f0; color: #1b6b3a; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; }
        .dx-cat__count { min-width: 22px; height: 22px; padding: 0 7px; border-radius: 999px; background: #f6f3ec; color: #6b6458; font-size: 11px; letter-spacing: 0; display: inline-flex; align-items: center; justify-content: center; }
        .dx-offers { display: flex; flex-direction: column; gap: 10px; }
        .dx-offer { display: grid; grid-template-columns: 76px minmax(0, 1fr) auto; gap: 14px; align-items: center; padding: 8px; background: #fff; border: 1px solid #ece5d6; border-radius: 22px; transition: transform .2s ease, border-color .2s ease, box-shadow .2s ease; }
        .dx-offer:hover { transform: translateY(-2px); border-color: #d8cdb8; box-shadow: 0 16px 34px -28px rgba(29,26,22,.5); }
        .dx-offer__media { position: relative; width: 76px; height: 76px; border-radius: 16px; overflow: hidden; background: #fdf4ec; color: #ef7c2b; display: flex; align-items: center; justify-content: center; font-size: 22px; }
        .dx-offer__media[role="button"] { cursor: zoom-in; }
        .dx-offer__media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
        .dx-offer__body { min-width: 0; display: flex; flex-direction: column; gap: 7px; }
        .dx-offer__name { margin: 0; font-family: 'Playfair Display', serif; font-size: 17px; font-weight: 700; line-height: 1.25; color: #1d1a16; }
        .dx-specs { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
        .dx-specs li { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; background: #f6f3ec; color: #3a352c; font-size: 12px; font-weight: 600; }
        .dx-specs i { font-size: 10px; color: #b9500a; }
        .dx-offer__side { display: flex; align-items: center; gap: 12px; padding-right: 6px; }
        .dx-offer__price { margin: 0; text-align: right; line-height: 1.1; white-space: nowrap; }
        .dx-offer__price strong { font-size: 19px; font-weight: 800; color: #1d1a16; letter-spacing: -.01em; }
        .dx-offer__price small { font-size: 11px; font-weight: 600; color: #6b6458; }
        .dx-offer__ask { width: 42px; height: 42px; flex-shrink: 0; border-radius: 999px; background: #b9500a; color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: background .15s ease, transform .15s ease; }
        .dx-offer__ask:hover { background: #9a4308; color: #fff; transform: scale(1.06); }
        .dx-offer__ask:focus-visible, .dx-offer__media[role="button"]:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .hx-review__date { display: block; font-size: 12px; color: #6b6458; }
        .dx-map-card { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 16px; border-top: 1px solid #ece5d6; background: #fff; }
        .dx-map-card p { margin: 0; display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 600; color: #1d1a16; }
        .dx-map-card small { display: block; font-size: 11px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: #6b6458; }
        .dx-route { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 999px; background: #b9500a; color: #fff !important; font-size: 13px; font-weight: 700; transition: background .15s ease; }
        .dx-route:hover { background: #9a4308; }
        .dx-note { margin: 0; padding: 14px 16px; border-radius: 18px; background: #f6f3ec; font-size: 13px; line-height: 1.55; color: #4a443a; }
        @media (max-width: 767px) {
            .dx-facts { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            .dx-offer { grid-template-columns: 64px minmax(0, 1fr); }
            .dx-offer__media { width: 64px; height: 64px; }
            .dx-offer__side { grid-column: 1 / -1; justify-content: space-between; padding: 8px 4px 2px; border-top: 1px solid #ece5d6; }
        }

        /* Réservation de visite : types de visite en cartes de choix */
        .tvp-step { margin: 0 0 8px; font-size: 12px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: #6b6458; }
        .tvp-modes { display: flex; flex-direction: column; gap: 8px; }
        .tvp-tab { display: grid; grid-template-columns: auto auto minmax(0, 1fr) auto; align-items: center; gap: 10px; width: 100%; padding: 12px 14px; text-align: left; background: #fff !important; color: #1d1a16 !important; border: 1.5px solid #ece5d6; border-radius: 18px; cursor: pointer; transition: border-color .15s ease, background .15s ease, box-shadow .15s ease; }
        .tvp-tab:hover { border-color: #ef7c2b; }
        .tvp-tab:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .tvp-tab.is-preview { grid-template-columns: auto minmax(0, 1fr) auto; cursor: default; }
        .tvp-tab.is-preview:hover { border-color: #ece5d6; }
        .tvp-tab__radio { width: 20px; height: 20px; border-radius: 999px; border: 2px solid #c9bfa8; background: #fff; transition: border-color .15s ease, box-shadow .15s ease; }
        .tvp-tab__icon { width: 38px; height: 38px; border-radius: 12px; background: #fdf4ec; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }
        .tvp-tab__txt { min-width: 0; display: flex; flex-direction: column; gap: 2px; }
        .tvp-tab__txt strong { font-size: 14.5px; font-weight: 700; }
        .tvp-tab__txt small { font-size: 12px; line-height: 1.35; color: #6b6458; }
        .tvp-tab__price { text-align: right; font-size: 14px; font-weight: 800; white-space: nowrap; }
        .tvp-tab__price small { display: block; font-size: 11px; font-weight: 600; color: #6b6458; }
        .tvp-tab.is-active { border-color: #b9500a; background: #fdf4ec !important; box-shadow: 0 0 0 3px rgba(239,124,43,.16); }
        .tvp-tab.is-active .tvp-tab__radio { border-color: #b9500a; box-shadow: inset 0 0 0 4px #fff; background: #b9500a; }
        .tvp-tab.is-active .tvp-tab__icon { background: #b9500a; color: #fff; }
        #tvp-booking-module .tvp-method { background: #fff !important; color: #1d1a16 !important; border: 1.5px solid #ece5d6 !important; border-radius: 14px !important; }
        html body #tvp-booking-module .tvp-method.is-active, html body #tvp-booking-module .tvp-method.is-active * { background: #b9500a !important; color: #fff !important; border-color: #b9500a !important; }
        /* Types de visite : badges, fiche détaillée, créneaux, catégories de tarif, options. */
        .tvp-badges { grid-column: 1 / -1; display: flex; flex-wrap: wrap; gap: 4px; padding-left: 30px; }
        .tvp-tab.is-preview .tvp-badges { padding-left: 0; }
        .tvp-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 999px; background: #f4efe4; color: #5c5548; font-size: 10.5px; font-weight: 700; line-height: 1.5; white-space: nowrap; }
        .tvp-badge i { font-size: 9px; }
        .tvp-badge.is-ok { background: #e7f3ec; color: #1b6b3a; }
        .tvp-badge.is-deal { background: #fff3d6; color: #7a5300; }
        .tvp-details { border: 1px solid #ece5d6; border-radius: 14px; background: #fffdf8; font-size: 12.5px; color: #3b362e; }
        .tvp-details summary { display: flex; align-items: center; gap: 8px; padding: 10px 12px; font-weight: 700; cursor: pointer; list-style: none; }
        .tvp-details summary::-webkit-details-marker { display: none; }
        .tvp-details summary > i:first-child { color: #b9500a; }
        .tvp-details__chev { margin-left: auto; font-size: 10px; transition: transform .15s ease; }
        .tvp-details[open] .tvp-details__chev { transform: rotate(180deg); }
        .tvp-details__body { padding: 0 12px 12px; display: flex; flex-direction: column; gap: 6px; }
        .tvp-details__body p { margin: 0; }
        .tvp-list { margin: 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 3px; }
        .tvp-list i { width: 16px; font-size: 11px; }
        .tvp-list .is-ok { color: #1b6b3a; } .tvp-list .is-no { color: #a3341f; } .tvp-list .is-info { color: #9a6a00; }
        .tvp-slots { display: grid; grid-template-columns: repeat(auto-fill, minmax(84px, 1fr)); gap: 6px; }
        .tvp-slot { position: relative; display: flex; flex-direction: column; align-items: center; gap: 1px; padding: 8px 4px; border-radius: 12px; border: 1.5px solid #ece5d6; background: #fff !important; color: #1d1a16 !important; cursor: pointer; transition: border-color .15s ease, box-shadow .15s ease; }
        .tvp-slot strong { font-size: 13.5px; font-weight: 800; }
        .tvp-slot small { font-size: 10.5px; color: #1b6b3a; font-weight: 600; }
        .tvp-slot em { position: absolute; top: -7px; right: -4px; padding: 0 5px; border-radius: 999px; font-size: 9.5px; font-style: normal; font-weight: 800; background: #febf48; color: #3d2b00; }
        .tvp-slot em.is-up { background: #f4efe4; color: #5c5548; }
        .tvp-slot:hover:not(:disabled) { border-color: #ef7c2b; }
        .tvp-slot:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .tvp-slot:disabled, .tvp-slot.is-small { background: #f1eee8 !important; color: #a39c8e !important; border-style: dashed; cursor: not-allowed; }
        .tvp-slot:disabled small, .tvp-slot.is-small small { color: #a39c8e; }
        .tvp-slot:disabled strong { text-decoration: line-through; }
        html body #tvp-booking-module .tvp-slot.is-active { border-color: #b9500a; background: #b9500a !important; color: #fff !important; box-shadow: 0 0 0 3px rgba(239,124,43,.18); }
        html body #tvp-booking-module .tvp-slot.is-active small { color: #fff; }
        .tvp-legend { display: flex; align-items: center; gap: 6px; margin: 6px 0 0; font-size: 11px; color: #6b6458; }
        .tvp-legend__dot { width: 10px; height: 10px; border-radius: 3px; border: 1.5px solid #ece5d6; background: #fff; }
        .tvp-legend__dot.is-off { margin-left: 8px; background: #f1eee8; border-style: dashed; }
        .tvp-tiers, .tvp-addons { display: flex; flex-direction: column; gap: 6px; }
        .tvp-tier { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 8px 10px 8px 12px; border: 1px solid #ece5d6; border-radius: 14px; background: #fff; }
        .tvp-tier__txt { display: flex; flex-direction: column; min-width: 0; }
        .tvp-tier__txt strong { font-size: 13.5px; } .tvp-tier__txt small { font-size: 11.5px; color: #6b6458; }
        .tvp-stepper { display: inline-flex; align-items: center; gap: 4px; }
        .tvp-stepper button { width: 34px; height: 34px; border-radius: 999px; border: 1.5px solid #ece5d6; background: #fff !important; color: #b9500a !important; font-size: 12px; }
        .tvp-stepper button:hover { border-color: #b9500a; }
        .tvp-stepper button:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 1px; }
        .tvp-stepper output { min-width: 24px; text-align: center; font-weight: 800; font-size: 14px; }
        .tvp-addon { display: grid; grid-template-columns: auto minmax(0, 1fr) auto; align-items: center; gap: 10px; padding: 9px 12px; border: 1px solid #ece5d6; border-radius: 14px; background: #fff; font-size: 13px; cursor: pointer; }
        .tvp-addon input { width: 18px; height: 18px; accent-color: #b9500a; }
        .tvp-addon:has(input:checked) { border-color: #b9500a; background: #fdf4ec; }
        .tvp-addon__price { font-weight: 700; white-space: nowrap; font-size: 12.5px; } .tvp-addon__price small { font-weight: 500; color: #6b6458; }
        .tvp-breakdown { display: flex; flex-direction: column; gap: 3px; padding-bottom: 8px; margin-bottom: 8px; border-bottom: 1px dashed #e2dacb; font-size: 12px; color: #5c5548; }
        .tvp-breakdown p { margin: 0; display: flex; justify-content: space-between; gap: 8px; }
        .tvp-breakdown p.is-deal { color: #1b6b3a; font-weight: 700; }
        #tvp-booking-module input:focus, #tvp-booking-module textarea:focus { outline: 2px solid #ef7c2b; outline-offset: 0; border-color: #ef7c2b; }

        /* Galerie « fiche produit » : bande de vignettes verticale + grande image */
        .hx-gal { display: grid; grid-template-columns: 92px minmax(0, 1fr); gap: 14px; height: clamp(360px, 58vh, 580px); }
        .hx-gal.is-single { grid-template-columns: minmax(0, 1fr); }
        .hx-gal__thumbs { display: flex; flex-direction: column; gap: 10px; overflow-y: auto; padding: 2px; scrollbar-width: thin; scroll-snap-type: y proximity; }
        .hx-gal__thumb { position: relative; flex: none; width: 84px; height: 84px; padding: 0; border: 2px solid transparent; border-radius: 16px; overflow: hidden; background: #f3eee4; cursor: pointer; opacity: .72; scroll-snap-align: start; transition: opacity .15s ease, border-color .15s ease, transform .15s ease; }
        .hx-gal__thumb img { width: 100%; height: 100%; object-fit: cover; }
        .hx-gal__thumb:hover { opacity: 1; transform: translateY(-1px); }
        .hx-gal__thumb.is-active { opacity: 1; border-color: #b9500a; box-shadow: 0 0 0 3px rgba(239,124,43,.18); }
        .hx-gal__thumb:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .hx-gal__play { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; color: #fff; background: rgba(29,26,22,.45); font-size: 14px; }
        .hx-gal__main { position: relative; border-radius: 28px; overflow: hidden; background: #f6f3ec; border: 1px solid #ece5d6; }
        .hx-gal__main > img { transition: opacity .25s ease; }
        .hx-gal__tools { position: absolute; z-index: 3; top: 14px; right: 14px; display: flex; flex-direction: column; gap: 8px; }
        .hx-gal__tool { width: 42px; height: 42px; border: 0; border-radius: 999px; background: rgba(255,255,255,.96); color: #1d1a16; display: inline-flex; align-items: center; justify-content: center; font-size: 15px; cursor: pointer; box-shadow: 0 2px 10px rgba(29,26,22,.18); transition: color .15s ease, transform .15s ease; }
        .hx-gal__tool:hover { color: #b9500a; transform: scale(1.06); }
        .hx-gal__tool.is-on { color: #e11d2a; }
        .hx-gal__tool:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .hx-gal .hx-arrow { width: 44px; height: 44px; margin-top: -22px; }
        @media (max-width: 767px) {
            .hx-gal { grid-template-columns: minmax(0, 1fr); height: auto; gap: 10px; }
            .hx-gal__main { order: -1; aspect-ratio: 4 / 3; border-radius: 22px; }
            .hx-gal__thumbs { flex-direction: row; overflow-x: auto; overflow-y: hidden; scroll-snap-type: x proximity; }
            .hx-gal__thumb { width: 64px; height: 64px; border-radius: 12px; }
            .hx-gal .hx-arrow { width: 38px; height: 38px; margin-top: -19px; }
        }
        @media (prefers-reduced-motion: reduce) { .hx-gal__thumb, .hx-gal__main > img { transition: none; } }
        .hx-gallery__room { position: absolute; z-index: 2; left: 16px; top: 16px; display: inline-flex; align-items: center; gap: 7px; height: 32px; padding: 0 12px; border-radius: 999px; background: #1b6b3a; color: #fff; font-size: 13px; font-weight: 700; box-shadow: 0 2px 8px rgba(29,26,22,.18); }
        /* Élément choisi (?item=…) : bandeau sobre sous le titre, à la place d'une photo absente. */
        .hx-pick { margin-bottom: 18px; border-radius: 24px; background: #fff; border: 1px solid #ece5d6; overflow: hidden; }
        .hx-pick__main { display: grid; grid-template-columns: minmax(0, 1fr) 240px; }
        .hx-pick__body { padding: 22px 24px; min-width: 0; }
        .hx-pick__kicker { margin: 0; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #b9500a; }
        .hx-pick__desc { margin: 10px 0 0; font-size: 15.5px; line-height: 1.65; color: #3a352c; max-width: 60ch; }
        .hx-pick__links { display: flex; flex-wrap: wrap; gap: 8px 20px; margin-top: 16px; }
        .hx-pick__link { display: inline-flex; align-items: center; gap: 7px; font-size: 13.5px; font-weight: 600; color: #4a443a; text-decoration: underline; text-decoration-color: #d8cdb8; text-underline-offset: 3px; }
        .hx-pick__link:hover { color: #9a4308; text-decoration-color: currentColor; }
        .hx-pick__link i { font-size: 15px; color: #b9500a; }
        .hx-pick__buy { display: flex; flex-direction: column; justify-content: center; gap: 10px; padding: 22px 24px; background: #f8f4ec; border-left: 1px solid #ece5d6; }
        .hx-pick__price { margin: 0; font-size: 28px; font-weight: 800; line-height: 1; color: #1d1a16; font-variant-numeric: tabular-nums; }
        .hx-pick__price small { font-size: 13px; font-weight: 600; color: #6b6458; }
        .hx-pick__add, .hx-pick__cart { display: inline-flex; align-items: center; justify-content: center; gap: 9px; height: 46px; border: 0; border-radius: 999px; font-size: 14px; font-weight: 700; cursor: pointer; transition: background .15s ease; }
        .hx-pick__add { background: #1d1a16; color: #fff; }
        .hx-pick__add:hover { background: #b9500a; }
        .hx-pick__step { display: none; align-items: center; justify-content: space-between; height: 46px; padding: 4px; border-radius: 999px; background: #1b6b3a; color: #fff; }
        .hx-pick__step button { width: 38px; height: 38px; border: 0; border-radius: 999px; background: transparent; color: #fff; cursor: pointer; }
        .hx-pick__step button:hover { background: rgba(255,255,255,.18); }
        .hx-pick__step b { font-size: 16px; font-variant-numeric: tabular-nums; }
        .hx-pick__cart { display: none; background: #fff; color: #1d1a16; border: 1.5px solid #e3dccb; }
        .hx-pick__cart:hover { border-color: #b9500a; color: #9a4308; }
        .hx-pick__cart i { font-size: 12px; }
        .hx-pick.is-in .hx-pick__add { display: none; }
        .hx-pick.is-in .hx-pick__step, .hx-pick.is-in .hx-pick__cart { display: inline-flex; }
        .hx-pick button:focus-visible, .hx-pick a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .hx-pick__more { padding: 16px 24px 18px; border-top: 1px solid #ece5d6; }
        .hx-pick__more-title { margin: 0 0 10px; font-size: 12px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: #6b6458; }
        .hx-pick__more ul { display: flex; gap: 8px; margin: 0; padding: 0; list-style: none; overflow-x: auto; scrollbar-width: none; }
        .hx-pick__more ul::-webkit-scrollbar { display: none; }
        .hx-pick__more a { flex: none; display: inline-flex; align-items: center; gap: 10px; height: 40px; padding: 0 16px; border-radius: 999px; background: #f6f3ec; color: #1d1a16; font-size: 13.5px; font-weight: 600; white-space: nowrap; text-decoration: none; transition: background .15s ease; }
        .hx-pick__more a:hover { background: #fdf4ec; color: #9a4308; }
        .hx-pick__more small { font-size: 12px; font-weight: 700; color: #8a7f6b; }
        @media (max-width: 767px) {
            .hx-pick__main { grid-template-columns: 1fr; }
            .hx-pick__body, .hx-pick__buy { padding: 18px; }
            .hx-pick__buy { border-left: 0; border-top: 1px solid #ece5d6; display: grid; grid-template-columns: auto 1fr; align-items: center; }
            .hx-pick__price { font-size: 24px; }
            .hx-pick__cart { grid-column: 1 / -1; }
            .hx-pick__more { padding: 14px 18px 16px; }
        }
        .hx-gal.is-empty { height: 200px; }
        @media (max-width: 767px) { .hx-gal.is-empty .hx-gal__main { aspect-ratio: auto; height: 200px; } }
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
            .hx-amen, .hx-reviews { grid-template-columns: 1fr; }
            .hx-tabs { top: 64px; }
        }
        @media (prefers-reduced-motion: reduce) { .hx-gallery__main img, .hx-gallery__tile img, body.dx .bento-card { transition: none; } }
</style>
