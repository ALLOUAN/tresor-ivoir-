{{--
    Charte d'interface des pages publiques — point d'entrée unique.

    Inclus en fin de partials.public-top-nav (donc après les <style> propres à
    chaque page) : ces règles harmonisent les composants que chaque vue
    redéfinissait à sa façon (bento-card, btn-primary, field-input, tab-btn…),
    sans toucher au markup ni au comportement.

    Rôle des couleurs (une couleur = un rôle, aplats sans dégradé) :
      --ti-forest  vert forêt   identité : bandeau haut, menu actif, liens de navigation
      --ti-action  orange       actions : réserver, envoyer, rechercher, s'abonner
      --ti-gold    or           accent rare : notes, élément courant ; jamais en texte courant
    Les couleurs propres à chaque secteur (hôtels, restaurants…) restent des
    repères (icônes, accroches) mais ne colorent plus les boutons d'action.

    Contrastes vérifiés sur blanc : action 4,98:1, forest 6,54:1, texte 16:1,
    texte secondaire 9:1, texte discret 5,8:1.
--}}
<style>
    html:not(.dark) {
        /* Identité */
        --ti-forest: #00874F;
        --ti-forest-800: #00874F;
        --ti-forest-50: #F3F8F5;
        --ti-forest-100: #D8E9E0;
        /* Action */
        --ti-action: #EB6E00;
        --ti-action-700: #B85600;
        --ti-action-800: #B85600;
        --ti-action-50: #F3F8F5;
        --ti-action-100: #D8E9E0;
        --ti-brand-orange: #EB6E00; /* orange du logo : décor uniquement, jamais du texte */
        /* Accent */
        --ti-gold: #EB6E00;
        --ti-gold-text: #B85600;
        --ti-gold-100: #F3F8F5;
        /* Neutres chauds */
        --ti-ink: #0E3B2A;
        --ti-body: #24503D;
        --ti-muted: #557466;
        --ti-line: #D8E9E0;
        --ti-line-strong: #8AA99B;
        --ti-paper: #FFFFFF;
        --ti-sand: #F3F8F5;
        /* Formes */
        --ti-radius-sm: 8px;
        --ti-radius: 12px;
        --ti-radius-lg: 16px;
        --ti-shadow-sm: 0 1px 2px rgb(14 59 42 / 0.06);
        --ti-shadow: 0 8px 24px rgb(14 59 42 / 0.08);
        --ti-shadow-lg: 0 16px 40px rgb(14 59 42 / 0.12);
        --ti-focus: 0 0 0 3px rgb(235 110 0 / 0.25);

        /* Jetons du pont de thème clair : fond papier, cartes blanches, filets fins. */
        --ti-bg: var(--ti-paper);
        --ti-surface-1: #ffffff;
        --ti-surface-2: var(--ti-sand);
        --ti-surface-3: #D8E9E0;
        --ti-border-1: var(--ti-line);
        --ti-border-2: var(--ti-line-strong);
    }

    /* ── Base ───────────────────────────────────────────── */
    /* Fond unique de toutes les pages : papier chaud (référence : page Chambres).
       body[class] / [style] : passe devant les fonds blancs ou beiges posés par
       certaines pages (bg-[#ffffff] réécrit par le pont de thème, style inline). */
    html:not(.dark) body,
    html:not(.dark) body[class],
    html:not(.dark) body[style] {
        background: var(--ti-paper) !important;
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }
    /* Grandes sections à fond uni (blanc, sable, beige) : même papier que la page.
       Les sections à photo ou image décorative d'admin gardent leur visuel. */
    html:not(.dark) section:is(.bg-white, .bg-dark-800, .bg-dark-900, .bg-green-950, .bg-\[\#ffffff\], .bg-\[\#F3F8F5\], .gallery-mesh, .events-hero, .hero-establishments),
    html:not(.dark) section[style*="#D8E9E0"],
    html:not(.dark) :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art) {
        background: var(--ti-paper) !important;
    }
    html:not(.dark) .bg-white { background-color: #ffffff !important; }
    ::selection { background: var(--ti-gold-100); color: var(--ti-ink); }

    /* Focus clavier visible et homogène (souris : aucun contour parasite). */
    html:not(.dark) a:focus-visible,
    html:not(.dark) button:focus-visible,
    html:not(.dark) [role="button"]:focus-visible,
    html:not(.dark) summary:focus-visible {
        outline: 2px solid var(--ti-action);
        outline-offset: 2px;
        border-radius: 6px;
    }
    html:not(.dark) input:focus-visible,
    html:not(.dark) select:focus-visible,
    html:not(.dark) textarea:focus-visible {
        outline: none;
        border-color: var(--ti-action) !important;
        box-shadow: var(--ti-focus) !important;
    }

    /* ── Accroches de section ───────────────────────────── */
    html:not(.dark) .section-kicker {
        letter-spacing: .16em;
        font-weight: 700;
    }

    /* ── Cartes ─────────────────────────────────────────── */
    html:not(.dark) .bento-card,
    html:not(.dark) .provider-list-card,
    html:not(.dark) .info-block {
        background: #ffffff !important;
        border: 1px solid var(--ti-line) !important;
        box-shadow: var(--ti-shadow-sm) !important;
        transition: box-shadow .2s ease, border-color .2s ease !important;
    }
    html:not(.dark) a.bento-card:hover,
    html:not(.dark) .bento-card.group:hover,
    html:not(.dark) .provider-list-card:hover,
    html:not(.dark) a.info-block:hover {
        transform: none !important;
        border-color: var(--ti-line-strong) !important;
        box-shadow: var(--ti-shadow) !important;
    }
    /* Pastille d'icône : teinte plate de la couleur du secteur (définie par la page). */
    html:not(.dark) .bento-icon {
        background: color-mix(in srgb, currentColor 11%, #ffffff) !important;
        border-radius: 10px !important;
    }
    /* Visuel absent : fond sable plutôt qu'un rectangle blanc vide. */
    html:not(.dark) .provider-list-card > div:first-child {
        background: var(--ti-sand) !important;
    }
    html:not(.dark) .provider-list-card > div:first-child > div {
        color: #8AA99B !important;
    }

    /* Cartes à fond sombre (photo assombrie ou aplat vert) : le pont de thème clair
       fonce les accents pour un fond clair ; on rétablit des teintes claires lisibles. */
    html:not(.dark) .ti-on-dark .text-gold-400,
    html:not(.dark) .ti-on-dark .text-gold-400\/70,
    html:not(.dark) .ti-on-dark .text-gold-300 { color: #F7C396 !important; }
    html:not(.dark) .ti-on-dark .text-emerald-300 { color: #DFF0E7 !important; }
    html:not(.dark) .ti-on-dark .text-orange-100 { color: #F3F8F5 !important; }

    /* ══ Cartes de l'accueil — même famille que la carte établissement ══════════════════════════
       Photo posée dans la carte, pastilles, une info par zone, bouton rond orange, soulèvement au survol.
       Aplats uniquement (aucun dégradé). */
    .ti-round {
        width: 44px; height: 44px; flex-shrink: 0; border: 0; border-radius: 999px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgb(255 255 255 / .95); color: var(--ti-ink); font-size: 16px;
        box-shadow: 0 2px 8px rgb(14 59 42 / .14); transition: transform .15s ease, color .15s ease, background .15s ease;
    }
    .ti-round:hover { transform: scale(1.06); color: var(--ti-action); }
    .ti-round.is-on { background: var(--ti-ink); color: #EB6E00; }
    .ti-round:focus-visible { outline: 3px solid var(--ti-brand-orange); outline-offset: 2px; }
    .ti-cta {
        display: inline-flex; align-items: center; gap: 10px; flex-shrink: 0;
        height: 46px; padding: 0 5px 0 18px; border-radius: 999px;
        background: var(--ti-action); color: #ffffff; font-size: 14px; font-weight: 700; white-space: nowrap;
        transition: background .15s ease;
    }
    .ti-cta i { width: 36px; height: 36px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center; background: rgb(255 255 255 / .2); font-size: 13px; transition: transform .2s ease; }
    .group:hover .ti-cta { background: var(--ti-action-700); }
    .group:hover .ti-cta i { transform: translateX(3px); }
    .ti-outline-btn {
        display: flex; align-items: center; justify-content: center; gap: 8px; height: 48px; margin-top: 16px;
        border: 1.5px solid var(--ti-brand-orange); border-radius: 999px;
        color: var(--ti-action-700); font-size: 14px; font-weight: 700; transition: background .15s ease;
    }
    .ti-outline-btn:hover { background: var(--ti-action-50); color: var(--ti-action-700); }
    .ti-outline-btn i { font-size: 12px; }

    /* ── Article à la une ── */
    .ti-feat {
        position: relative; display: flex; flex-direction: column;
        padding: 10px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 30px;
        box-shadow: 0 18px 44px -32px rgb(14 59 42 / .45);
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .ti-feat:hover, .ti-feat:focus-within { transform: translateY(-6px); box-shadow: 0 30px 60px -34px rgb(14 59 42 / .55); }
    .ti-feat__media { position: relative; aspect-ratio: 16 / 9; border-radius: 22px; overflow: hidden; background: var(--ti-sand); }
    .ti-feat__media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .7s ease; }
    .ti-feat:hover .ti-feat__media img { transform: scale(1.04); }
    .ti-feat__placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 42px; color: var(--ti-brand-orange); }
    .ti-feat__top { position: absolute; top: 14px; left: 14px; right: 14px; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .ti-feat__actions { display: flex; gap: 8px; }
    .ti-feat__hot { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: #EB6E00; color: #0E3B2A; font-size: 12px; font-weight: 700; }
    .ti-feat__hot i { font-size: 11px; }
    .ti-feat__time { position: absolute; left: 14px; bottom: 14px; display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgb(14 59 42 / .7); color: #ffffff; font-size: 12px; font-weight: 600; }
    .ti-feat__body { display: flex; flex-direction: column; gap: 12px; padding: 20px 14px 8px; }
    .ti-feat__meta { display: flex; flex-wrap: wrap; align-items: center; gap: 8px 16px; margin: 0; font-size: 13px; color: var(--ti-muted); }
    .ti-feat__meta > span { display: inline-flex; align-items: center; gap: 6px; }
    .ti-feat__cat { height: 28px; padding: 0 11px 0 5px; border-radius: 999px; background: var(--ti-action-50); color: var(--ti-action-700); font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .ti-feat__cat i { width: 20px; height: 20px; border-radius: 999px; background: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 10px; }
    .ti-feat__title { margin: 0; font-family: var(--font-serif); font-size: clamp(24px, 2vw + 12px, 32px); font-weight: 700; line-height: 1.15; letter-spacing: -.015em; color: var(--ti-ink); }
    .ti-feat__link { color: inherit; }
    .ti-feat__link::after { content: ''; position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
    .ti-feat__actions > * { position: relative; z-index: 2; }
    .ti-feat__excerpt { margin: 0; font-size: 15px; line-height: 1.6; color: var(--ti-body); display: -webkit-box; -webkit-line-clamp: 3; line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden; }
    .ti-feat__foot { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 6px 14px 10px; padding-top: 16px; border-top: 1px solid #D8E9E0; }
    .ti-feat__author { display: flex; align-items: center; gap: 10px; min-width: 0; font-size: 14px; font-weight: 600; color: var(--ti-ink); }
    .ti-feat__author small { display: block; font-size: 12px; font-weight: 500; color: var(--ti-muted); }
    .ti-feat__avatar { width: 40px; height: 40px; flex-shrink: 0; border-radius: 999px; background: var(--ti-forest); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 700; }

    /* ── À lire aussi ── */
    .ti-readlist { padding: 24px 22px 20px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 30px; box-shadow: 0 18px 44px -34px rgb(14 59 42 / .4); }
    .ti-readlist__head h3 { display: flex; align-items: center; gap: 10px; margin: 0 0 6px; font-family: var(--font-serif); font-size: 20px; font-weight: 700; color: var(--ti-ink); }
    .ti-readlist__head h3 i { width: 34px; height: 34px; border-radius: 10px; background: var(--ti-action-50); color: var(--ti-action); display: inline-flex; align-items: center; justify-content: center; font-size: 15px; }
    .ti-readlist__item { display: grid; grid-template-columns: 40px minmax(0, 1fr) 68px; gap: 14px; align-items: center; padding: 14px 8px; border-radius: 18px; border-bottom: 1px solid #F3F8F5; transition: background .15s ease; }
    .ti-readlist__item:last-of-type { border-bottom: 0; }
    .ti-readlist__item:hover { background: #F3F8F5; }
    .ti-readlist__num { font-family: var(--font-serif); font-size: 28px; font-weight: 700; line-height: 1; color: #D8E9E0; transition: color .15s ease; }
    .ti-readlist__item:hover .ti-readlist__num { color: var(--ti-brand-orange); }
    .ti-readlist__cat { display: block; margin-bottom: 5px; font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--ti-forest); }
    .ti-readlist__title { display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; font-family: var(--font-serif); font-size: 16px; font-weight: 700; line-height: 1.3; color: var(--ti-ink); }
    .ti-readlist__meta { display: block; margin-top: 5px; font-size: 12px; color: var(--ti-muted); }
    .ti-readlist__thumb { width: 68px; height: 68px; border-radius: 16px; overflow: hidden; background: var(--ti-action-50); color: var(--ti-action); display: flex; align-items: center; justify-content: center; font-size: 22px; }
    .ti-readlist__thumb img { width: 100%; height: 100%; object-fit: cover; }

    /* ── Destinations ── */
    .ti-dest-grid { display: grid; grid-template-columns: 1.5fr 1fr; grid-template-rows: repeat(3, minmax(200px, auto)); gap: 18px; }
    .ti-dest { position: relative; display: block; min-height: 200px; border-radius: 28px; overflow: hidden; background: var(--ti-forest-800); box-shadow: 0 22px 50px -36px rgb(14 59 42 / .6); transition: transform .25s ease, box-shadow .25s ease; }
    .ti-dest.is-hero { grid-row: span 3; min-height: 560px; }
    .ti-dest:hover, .ti-dest:focus-visible { transform: translateY(-6px); box-shadow: 0 34px 70px -38px rgb(14 59 42 / .7); }
    .ti-dest:focus-visible { outline: 3px solid var(--ti-brand-orange); outline-offset: 3px; }
    .ti-dest img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .8s ease; }
    .ti-dest:hover img { transform: scale(1.05); }
    .ti-dest__top { position: absolute; top: 16px; left: 16px; right: 16px; display: flex; justify-content: space-between; }
    .ti-dest__badge { display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: rgb(255 255 255 / .95); color: var(--ti-ink); font-size: 12px; font-weight: 600; }
    .ti-dest__badge i { font-size: 10px; color: var(--ti-action); }
    .ti-dest__badge.is-star { background: #EB6E00; color: #0E3B2A; }
    .ti-dest__badge.is-star i { color: #0E3B2A; }
    .ti-dest__panel { position: absolute; left: 12px; right: 12px; bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 12px 14px 18px; border-radius: 20px; background: rgb(255 255 255 / .96); box-shadow: 0 10px 30px -18px rgb(14 59 42 / .6); }
    .ti-dest__name { display: block; font-family: var(--font-serif); font-size: 20px; font-weight: 700; line-height: 1.1; color: var(--ti-ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ti-dest.is-hero .ti-dest__name { font-size: clamp(24px, 2vw + 10px, 30px); }
    .ti-dest__district { display: flex; align-items: center; gap: 6px; margin-top: 5px; font-size: 13px; color: var(--ti-body); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ti-dest__district i { font-size: 11px; color: var(--ti-forest); }
    .ti-dest__chips { display: flex; gap: 6px; margin-top: 10px; }
    .ti-dest__chip { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; background: var(--ti-forest-50); color: var(--ti-forest-800); font-size: 12px; font-weight: 600; }
    .ti-dest__go { width: 48px; height: 48px; flex-shrink: 0; border-radius: 999px; background: var(--ti-action); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 16px; transition: transform .2s ease, background .2s ease; }
    .ti-dest:hover .ti-dest__go { transform: rotate(-45deg); background: var(--ti-action-700); }
    .ti-dest-strip { display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-top: 24px; }
    .ti-dest-strip__label { margin-right: 6px; font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--ti-muted); }
    .ti-dest-chip { display: inline-flex; align-items: center; gap: 10px; height: 52px; padding: 0 18px 0 6px; border-radius: 999px; background: #ffffff; border: 1px solid #D8E9E0; color: var(--ti-ink); font-size: 14px; font-weight: 600; line-height: 1.15; transition: border-color .15s ease, transform .15s ease; }
    .ti-dest-chip:hover { border-color: var(--ti-brand-orange); transform: translateY(-2px); color: var(--ti-ink); }
    .ti-dest-chip small { display: block; font-size: 12px; font-weight: 500; color: var(--ti-muted); }
    .ti-dest-chip__avatar { position: relative; width: 40px; height: 40px; flex-shrink: 0; border-radius: 999px; overflow: hidden; display: inline-flex; align-items: center; justify-content: center; font-family: var(--font-serif); font-size: 15px; font-weight: 700; color: #ffffff; }
    .ti-dest-chip__avatar img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; }
    @media (max-width: 1023px) {
        .ti-dest-grid { grid-template-columns: 1fr 1fr; grid-template-rows: auto; }
        .ti-dest.is-hero { grid-column: 1 / -1; grid-row: auto; min-height: 380px; }
    }
    @media (max-width: 639px) {
        .ti-dest-grid { grid-template-columns: 1fr; }
        .ti-dest { min-height: 230px; }
        .ti-dest.is-hero { min-height: 320px; }
    }

    /* ── Carte peuple (partials.people-card) ── */
    .ti-people-card { display: flex; flex-direction: column; height: 100%; padding: 9px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 28px; color: var(--ti-ink); box-shadow: 0 18px 44px -34px rgb(14 59 42 / .45); transition: transform .25s ease, box-shadow .25s ease; }
    .ti-people-card:hover, .ti-people-card:focus-visible { transform: translateY(-6px); box-shadow: 0 30px 60px -34px rgb(14 59 42 / .55); color: var(--ti-ink); }
    .ti-people-card:focus-visible { outline: 3px solid var(--ti-brand-orange); outline-offset: 3px; }
    .ti-people-card__media { position: relative; aspect-ratio: 16 / 11; border-radius: 20px; overflow: hidden; background: var(--ti-sand); }
    .ti-people-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .7s ease; }
    .ti-people-card:hover .ti-people-card__media img { transform: scale(1.05); }
    .ti-people-card__pattern { position: absolute; inset: 0; opacity: .12; }
    .ti-people-card__initial { position: absolute; right: 14px; bottom: -14px; font-family: var(--font-serif); font-size: 150px; font-weight: 700; line-height: 1; color: rgb(255 255 255 / .16); }
    .ti-people-card__overname { position: absolute; left: 18px; bottom: 14px; right: 18px; font-family: var(--font-serif); font-size: 26px; font-weight: 700; line-height: 1.1; color: #ffffff; }
    .ti-people-card__top { position: absolute; top: 12px; left: 12px; right: 12px; display: flex; justify-content: space-between; gap: 8px; }
    .ti-people-card__pill { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgb(255 255 255 / .95); color: var(--ti-ink); font-size: 12px; font-weight: 600; }
    .ti-people-card__pill i { font-size: 11px; color: var(--ti-forest); }
    .ti-people-card__pill.is-featured { background: #EB6E00; color: #0E3B2A; }
    .ti-people-card__pill.is-featured i { color: #0E3B2A; }
    .ti-people-card__body { flex: 1; display: flex; flex-direction: column; gap: 12px; padding: 16px 10px 6px; }
    .ti-people-card__name { margin: 0; font-family: var(--font-serif); font-size: 24px; font-weight: 700; line-height: 1.1; }
    .ti-people-card__capital { display: flex; align-items: center; gap: 8px; margin: 0; font-size: 13px; color: var(--ti-body); }
    .ti-people-card__capital i { width: 26px; height: 26px; flex-shrink: 0; border-radius: 8px; background: var(--ti-action-50); color: var(--ti-action); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
    .ti-people-card__capital strong { font-weight: 700; color: var(--ti-ink); }
    .ti-people-card__stats { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; margin: 0; }
    .ti-people-card__stats > div { padding: 10px 12px; border-radius: 14px; background: #F3F8F5; min-width: 0; }
    .ti-people-card__stats dt { display: flex; align-items: center; gap: 5px; margin-bottom: 6px; font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--ti-muted); }
    .ti-people-card__stats dd { margin: 0; font-size: 14px; font-weight: 700; color: var(--ti-ink); overflow-wrap: anywhere; }
    .ti-people-card__footer { display: flex; align-items: center; justify-content: space-between; margin: 4px 10px 6px; padding-top: 12px; border-top: 1px solid #D8E9E0; font-size: 13px; font-weight: 700; color: var(--ti-action-700); }
    .ti-people-card__go { width: 40px; height: 40px; border-radius: 999px; background: var(--ti-action); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: transform .2s ease; }
    .ti-people-card:hover .ti-people-card__go { transform: rotate(-45deg); }

    /* ── Carte événement en billet (partials.event-card) ── */
    .ti-event-card { position: relative; display: flex; flex-direction: column; height: 100%; padding: 9px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 28px; box-shadow: 0 18px 44px -34px rgb(14 59 42 / .45); transition: transform .25s ease, box-shadow .25s ease; }
    .ti-event-card:hover, .ti-event-card:focus-within { transform: translateY(-6px); box-shadow: 0 30px 60px -34px rgb(14 59 42 / .55); }
    .ti-event-card__media { position: relative; aspect-ratio: 16 / 10; border-radius: 20px; overflow: hidden; background: var(--ti-forest); }
    .ti-event-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .7s ease; }
    .ti-event-card:hover .ti-event-card__media img { transform: scale(1.05); }
    .ti-event-card__placeholder { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; font-size: 48px; color: rgb(255 255 255 / .85); }
    .ti-event-card__stamp { position: absolute; left: 12px; top: 12px; width: 62px; padding: 9px 0 8px; border-radius: 16px; background: #ffffff; text-align: center; box-shadow: 0 8px 20px -10px rgb(14 59 42 / .5); }
    .ti-event-card__stamp b { display: block; font-family: var(--font-serif); font-size: 26px; font-weight: 700; line-height: 1; color: var(--ti-ink); }
    .ti-event-card__stamp span { display: block; margin-top: 3px; font-size: 11px; font-weight: 700; letter-spacing: .1em; text-transform: uppercase; color: var(--ti-action); }
    .ti-event-card__save { position: absolute; top: 12px; right: 12px; z-index: 2; }
    .ti-event-card__countdown { position: absolute; left: 12px; bottom: 12px; display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgb(14 59 42 / .7); color: #ffffff; font-size: 12px; font-weight: 700; }
    .ti-event-card__countdown.is-soon { background: #EB6E00; color: #0E3B2A; }
    .ti-event-card__body { flex: 1; display: flex; flex-direction: column; gap: 10px; padding: 16px 10px 14px; }
    .ti-event-card__category { display: inline-flex; align-self: flex-start; align-items: center; gap: 6px; height: 26px; margin: 0; padding: 0 10px; border-radius: 999px; background: var(--ti-action-50); color: var(--ti-action-700); font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .ti-event-card__category i { font-size: 10px; }
    .ti-event-card__title { margin: 0; font-family: var(--font-serif); font-size: 21px; font-weight: 700; line-height: 1.2; color: var(--ti-ink); }
    .ti-event-card__link { color: inherit; }
    .ti-event-card__link::after { content: ''; position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
    .ti-event-card__meta { display: flex; flex-direction: column; gap: 7px; margin: 0; padding: 0; list-style: none; font-size: 13px; color: var(--ti-body); }
    .ti-event-card__meta li { display: flex; align-items: center; gap: 9px; }
    .ti-event-card__meta i { width: 26px; height: 26px; flex-shrink: 0; border-radius: 8px; background: #F3F8F5; color: var(--ti-forest); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
    .ti-event-card__stub { position: relative; display: flex; align-items: center; justify-content: space-between; gap: 12px; margin: 0 10px 6px; padding-top: 14px; border-top: 2px dashed #D8E9E0; }
    .ti-event-card__stub::before, .ti-event-card__stub::after { content: ''; position: absolute; top: -12px; width: 22px; height: 22px; border-radius: 999px; background: var(--ti-paper); border: 1px solid #D8E9E0; }
    .ti-event-card__stub::before { left: -31px; border-left-color: transparent; border-bottom-color: transparent; transform: rotate(45deg); }
    .ti-event-card__stub::after { right: -31px; border-right-color: transparent; border-top-color: transparent; transform: rotate(45deg); }
    .ti-event-card__price { font-size: 19px; font-weight: 700; line-height: 1.1; color: var(--ti-ink); white-space: nowrap; }
    .ti-event-card__price small { display: block; margin-bottom: 3px; font-size: 11px; font-weight: 600; color: var(--ti-muted); }
    .ti-event-card__price em { font-style: normal; font-size: 12px; font-weight: 600; color: var(--ti-muted); }
    .ti-event-card__price.is-free { color: var(--ti-forest); }
    .ti-event-card__cta, .ti-event-card__more { position: relative; z-index: 2; display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 16px; border-radius: 999px; background: var(--ti-action); color: #ffffff; font-size: 13px; font-weight: 700; white-space: nowrap; transition: background .15s ease; }
    .ti-event-card__more { z-index: auto; }
    .ti-event-card__cta:hover, .ti-event-card:hover .ti-event-card__more { background: var(--ti-action-700); color: #ffffff; }
    .ti-event-card__cta i, .ti-event-card__more i { font-size: 11px; }

    /* ── Carte œuvre façon galerie (partials.artwork-card) ── */
    .ti-art-card { position: relative; display: flex; flex-direction: column; height: 100%; padding: 9px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 28px; box-shadow: 0 18px 44px -34px rgb(14 59 42 / .45); transition: transform .25s ease, box-shadow .25s ease; }
    .ti-art-card:hover, .ti-art-card:focus-within { transform: translateY(-6px); box-shadow: 0 30px 60px -34px rgb(14 59 42 / .55); }
    .ti-art-card__wall { position: relative; aspect-ratio: 4 / 3; border-radius: 20px; background: #F3F8F5; display: flex; align-items: center; justify-content: center; }
    .ti-art-card__frame { width: 64%; height: 70%; padding: 8px; background: #ffffff; border: 6px solid #0E3B2A; box-shadow: 0 22px 30px -18px rgb(14 59 42 / .6); transition: transform .35s ease; }
    .ti-art-card:hover .ti-art-card__frame { transform: scale(1.04); }
    .ti-art-card__frame img { width: 100%; height: 100%; object-fit: cover; display: block; }
    .ti-art-card__placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: #F7C396; color: var(--ti-muted); font-size: 26px; }
    .ti-art-card__pills { position: absolute; top: 12px; left: 12px; display: flex; flex-direction: column; align-items: flex-start; gap: 6px; }
    .ti-art-card__pill { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: #ffffff; color: var(--ti-ink); font-size: 12px; font-weight: 600; box-shadow: 0 2px 8px rgb(14 59 42 / .1); }
    .ti-art-card__pill i { font-size: 11px; color: var(--ti-action); }
    .ti-art-card__pill.is-cert { background: var(--ti-forest-50); color: var(--ti-forest-800); }
    .ti-art-card__pill.is-cert i { color: var(--ti-forest); }
    .ti-art-card__pill.is-sold { background: var(--ti-ink); color: #ffffff; }
    .ti-art-card__body { flex: 1; display: flex; flex-direction: column; gap: 10px; padding: 16px 10px 6px; }
    .ti-art-card__category { display: inline-flex; align-items: center; gap: 7px; margin: 0; font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; color: var(--ti-action-700); }
    .ti-art-card__category i { width: 24px; height: 24px; border-radius: 7px; background: var(--ti-action-50); color: var(--ti-action); display: inline-flex; align-items: center; justify-content: center; font-size: 11px; }
    .ti-art-card__title { margin: 0; font-family: var(--font-serif); font-size: 22px; font-weight: 700; line-height: 1.15; color: var(--ti-ink); }
    .ti-art-card__link { color: inherit; }
    .ti-art-card__link::after { content: ''; position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
    .ti-art-card__artist { display: flex; align-items: center; gap: 9px; margin: 0; font-size: 14px; font-weight: 600; color: var(--ti-ink); }
    .ti-art-card__artist a { position: relative; z-index: 2; color: inherit; text-decoration: underline; text-decoration-color: var(--ti-line-strong); text-underline-offset: 3px; }
    .ti-art-card__artist a:hover { color: var(--ti-action); }
    .ti-art-card__avatar { width: 32px; height: 32px; flex-shrink: 0; border-radius: 999px; background: #00874F; color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
    .ti-art-card__specs { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
    .ti-art-card__specs li { display: inline-flex; align-items: center; height: 26px; padding: 0 10px; border-radius: 9px; background: #F3F8F5; color: #24503D; font-size: 12px; }
    .ti-art-card__footer { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 6px 10px 6px; padding-top: 12px; border-top: 1px solid #D8E9E0; }
    .ti-art-card__price { font-size: 19px; font-weight: 700; color: var(--ti-ink); white-space: nowrap; }
    .ti-art-card__price small { font-size: 12px; font-weight: 600; color: var(--ti-muted); }
    .ti-art-card__more { display: inline-flex; align-items: center; gap: 10px; height: 44px; padding: 0 5px 0 16px; border-radius: 999px; background: var(--ti-action); color: #ffffff; font-size: 13px; font-weight: 700; transition: background .15s ease; }
    .ti-art-card__more i { width: 34px; height: 34px; border-radius: 999px; background: rgb(255 255 255 / .2); display: inline-flex; align-items: center; justify-content: center; font-size: 12px; transition: transform .2s ease; }
    .ti-art-card:hover .ti-art-card__more { background: var(--ti-action-700); }
    .ti-art-card:hover .ti-art-card__more i { transform: translateX(3px); }
    .ti-art-card.is-sold .ti-art-card__frame { filter: grayscale(.6); }

    /* ── Tuiles de complément (agenda, galerie, commande sur mesure) ── */
    .ti-event-more {
        display: flex; flex-direction: column; justify-content: flex-end; align-items: flex-start; gap: 12px; height: 100%; min-height: 300px;
        padding: 30px 28px; border-radius: 28px; background: #ffffff; border: 1px solid #D8E9E0; color: var(--ti-ink);
        box-shadow: 0 18px 44px -34px rgb(14 59 42 / .4); transition: transform .25s ease, box-shadow .25s ease;
    }
    .ti-event-more:hover { transform: translateY(-6px); box-shadow: 0 30px 60px -34px rgb(14 59 42 / .5); color: var(--ti-ink); }
    .ti-event-more__icon { width: 56px; height: 56px; margin-bottom: auto; border-radius: 18px; background: var(--ti-action-50); color: var(--ti-action); display: inline-flex; align-items: center; justify-content: center; font-size: 22px; }
    .ti-event-more__title { font-family: var(--font-serif); font-size: 24px; font-weight: 700; line-height: 1.15; }
    .ti-event-more__text { font-size: 15px; line-height: 1.6; color: var(--ti-body); }
    .ti-event-more__link { display: inline-flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: var(--ti-action-700); }
    .ti-event-more__link i { width: 40px; height: 40px; border-radius: 999px; background: var(--ti-action); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 13px; transition: transform .2s ease; }
    .ti-event-more:hover .ti-event-more__link i { transform: translateX(3px); }
    .ti-event-more.is-forest { background: var(--ti-forest); border-color: var(--ti-forest); color: #ffffff; }
    .ti-event-more.is-forest:hover { color: #ffffff; }
    .ti-event-more.is-forest .ti-event-more__icon { background: rgb(255 255 255 / .14); color: #EB6E00; }
    .ti-event-more.is-forest .ti-event-more__text { color: #D8E9E0; }
    .ti-event-more.is-forest .ti-event-more__link { color: #ffffff; }
    .ti-event-more.is-forest .ti-event-more__link i { background: #EB6E00; color: #0E3B2A; }

    /* ── Partenaires ── */
    .ti-partner { display: flex; flex-direction: column; align-items: flex-start; gap: 12px; padding: 22px 20px 18px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 26px; box-shadow: 0 14px 36px -30px rgb(14 59 42 / .45); transition: transform .25s ease, border-color .25s ease; }
    .ti-partner:hover { transform: translateY(-6px); border-color: var(--ti-brand-orange); }
    .ti-partner__logo { width: 100%; height: 64px; display: flex; align-items: center; justify-content: flex-start; }
    .ti-partner__logo img { max-width: 100%; max-height: 100%; object-fit: contain; }
    .ti-partner__mono { width: 56px; height: 56px; border-radius: 18px; display: inline-flex; align-items: center; justify-content: center; font-family: var(--font-serif); font-size: 19px; font-weight: 700; color: #ffffff; letter-spacing: .02em; }
    .ti-partner__name { margin: 0; min-height: 42px; font-family: var(--font-serif); font-size: 16px; font-weight: 700; line-height: 1.3; color: var(--ti-ink); display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ti-partner__type { display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px; font-size: 11px; font-weight: 700; letter-spacing: .06em; text-transform: uppercase; }
    .ti-partner__type i { font-size: 11px; }
    .ti-partner__visit { margin-top: auto; display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--ti-action-700); }
    .ti-partner__visit i { font-size: 11px; transition: transform .2s ease; }
    .ti-partner:hover .ti-partner__visit i { transform: translate(2px, -2px); }
    .ti-partner__visit.is-off { color: var(--ti-muted); font-weight: 500; }

    /* ── Carte ville (/tourisme, partials.city-card) ── */
    .ti-city { display: flex; flex-direction: column; height: 100%; padding: 9px; background: #ffffff; border: 1px solid #D8E9E0; border-radius: 28px; color: var(--ti-ink); box-shadow: 0 18px 44px -34px rgb(14 59 42 / .45); transition: transform .25s ease, box-shadow .25s ease; }
    .ti-city:hover, .ti-city:focus-visible { transform: translateY(-6px); box-shadow: 0 30px 60px -34px rgb(14 59 42 / .55); color: var(--ti-ink); }
    .ti-city:focus-visible { outline: 3px solid var(--ti-brand-orange); outline-offset: 3px; }
    .ti-city__media { position: relative; aspect-ratio: 16 / 10; border-radius: 20px; overflow: hidden; }
    .ti-city__media img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .7s ease; }
    .ti-city__media img.ti-city__photo { object-fit: contain; }
    .ti-city__media img.ti-city__backdrop { filter: blur(18px) saturate(1.1); transform: scale(1.15); }
    .ti-city:hover .ti-city__media img.ti-city__photo { transform: scale(1.03); }
    .ti-city__pattern { position: absolute; inset: 0; opacity: .12; }
    .ti-city__initial { position: absolute; right: 16px; bottom: -18px; font-family: var(--font-serif); font-size: 140px; font-weight: 700; line-height: 1; color: rgb(255 255 255 / .18); }
    .ti-city__top { position: absolute; top: 12px; left: 12px; right: 12px; z-index: 1; display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; }
    .ti-city__pill { display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 11px; border-radius: 999px; background: rgb(255 255 255 / .95); color: var(--ti-ink); font-size: 12px; font-weight: 600; box-shadow: 0 2px 8px rgb(14 59 42 / .12); }
    .ti-city__pill i { font-size: 11px; color: var(--ti-forest); }
    .ti-city__pill.is-star { background: #EB6E00; color: #0E3B2A; }
    .ti-city__pill.is-star i { color: #0E3B2A; }
    .ti-city__body { flex: 1; display: flex; flex-direction: column; gap: 8px; padding: 16px 10px 6px; }
    .ti-city__name { margin: 0; font-family: var(--font-serif); font-size: 24px; font-weight: 700; line-height: 1.1; letter-spacing: -.01em; color: var(--ti-ink); }
    .ti-city__where { display: flex; align-items: center; gap: 7px; margin: 0; font-size: 13px; color: var(--ti-body); }
    .ti-city__where i { width: 22px; height: 22px; flex-shrink: 0; border-radius: 999px; background: var(--ti-forest-50); color: var(--ti-forest); display: inline-flex; align-items: center; justify-content: center; font-size: 10px; }
    .ti-city__text { margin: 2px 0 0; font-size: 14px; line-height: 1.55; color: var(--ti-body); display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .ti-city__footer { display: flex; align-items: center; justify-content: space-between; margin: 8px 10px 6px; padding-top: 12px; border-top: 1px solid #D8E9E0; font-size: 13px; font-weight: 700; color: var(--ti-action-700); }
    .ti-city__go { width: 42px; height: 42px; border-radius: 999px; background: var(--ti-action); color: #ffffff; display: inline-flex; align-items: center; justify-content: center; font-size: 14px; transition: transform .2s ease, background .2s ease; }
    .ti-city:hover .ti-city__go { transform: rotate(-45deg); background: var(--ti-action-700); }

    /* En-tête de /tourisme : accroche en pastille et chiffres clés */
    .ti-tour-hero__kicker { display: inline-flex; align-items: center; gap: 7px; margin: 0 0 14px; padding: 6px 14px; border-radius: 999px; background: var(--ti-action-50); color: var(--ti-action-700) !important; font-size: 12px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
    .ti-tour-hero__stats { display: flex; flex-wrap: wrap; justify-content: center; gap: 10px; margin: 22px 0 0; padding: 0; list-style: none; }
    .ti-tour-hero__stats li { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px 0 6px; border-radius: 999px; background: #ffffff; border: 1px solid #D8E9E0; color: var(--ti-body) !important; font-size: 14px; }
    .ti-tour-hero__stats li i { width: 30px; height: 30px; border-radius: 999px; background: var(--ti-forest-50); color: var(--ti-forest); display: inline-flex; align-items: center; justify-content: center; font-size: 12px; }
    .ti-tour-hero__stats li strong { color: var(--ti-ink) !important; font-weight: 800; }
    .ti-tour-bar { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 18px; }
    .ti-tour-bar__count { margin: 0; font-size: 15px; color: var(--ti-body); }
    .ti-tour-bar__count strong { font-family: var(--font-serif); font-size: 22px; color: var(--ti-ink); }
    .ti-tour-bar__count span { color: var(--ti-muted); }
    .ti-tour-bar__clear { display: inline-flex; align-items: center; gap: 7px; height: 38px; padding: 0 14px; border-radius: 999px; border: 1.5px solid var(--ti-brand-orange); color: var(--ti-action-700); font-size: 13px; font-weight: 700; }
    .ti-tour-bar__clear:hover { background: var(--ti-action-50); color: var(--ti-action-700); }

    @media (prefers-reduced-motion: reduce) {
        .ti-city { transition: none; }
        .ti-city:hover { transform: none; }
        .ti-feat, .ti-dest, .ti-people-card, .ti-event-card, .ti-art-card, .ti-event-more, .ti-partner { transition: none; }
        .ti-feat:hover, .ti-dest:hover, .ti-people-card:hover, .ti-event-card:hover, .ti-art-card:hover, .ti-event-more:hover, .ti-partner:hover { transform: none; }
    }


    /* ── Carte établissement (home-sector-card / home-provider-card) ──────────────────────────
       Photo d'abord, posée dans la carte ; une information par zone ; bouton qui dit où il mène.
       Toute la carte est cliquable via le lien étendu du titre ; favori / itinéraire / partage passent au-dessus.
       Aplats uniquement (aucun dégradé). */
    .ti-place-card {
        position: relative;
        display: flex; flex-direction: column; height: 100%;
        padding: 8px;
        background: #ffffff;
        border: 1px solid #D8E9E0;
        border-radius: 26px;
        box-shadow: 0 1px 2px rgb(14 59 42 / .05), 0 18px 40px -30px rgb(14 59 42 / .38);
        transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        color: var(--ti-ink);
    }
    .ti-place-card:hover, .ti-place-card:focus-within {
        transform: translateY(-6px);
        border-color: var(--ti-line-strong);
        box-shadow: 0 2px 4px rgb(14 59 42 / .06), 0 30px 60px -32px rgb(14 59 42 / .5);
    }
    .ti-place-card .sr-only { position: absolute; width: 1px; height: 1px; overflow: hidden; clip: rect(0 0 0 0); white-space: nowrap; }

    /* Photo */
    .ti-place-card__media {
        position: relative;
        aspect-ratio: 4 / 3;
        border-radius: 19px;
        background: var(--ti-sand);
        overflow: hidden;
    }
    .ti-place-card__media img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform .6s ease, opacity .45s ease; }
    .ti-place-card:hover .ti-place-card__media img { transform: scale(1.05); }
    .ti-place-card__media img.ti-place-card__hover { position: absolute; inset: 0; opacity: 0; }
    .ti-place-card:hover .ti-place-card__media img.ti-place-card__hover,
    .ti-place-card:focus-within .ti-place-card__media img.ti-place-card__hover { opacity: 1; }
    /* Sans photo : le pictogramme du secteur, centré dans un rond. */
    .ti-place-card__icon { position: absolute; inset: 0; display: flex; align-items: center; justify-content: center; }
    .ti-place-card__icon i {
        width: 64px; height: 64px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
        background: #ffffff; color: var(--ti-brand-orange); font-size: 24px; box-shadow: var(--ti-shadow-sm);
    }
    .ti-place-card__media:has(img) .ti-place-card__icon { display: none; }
    @media (max-width: 639px) { .ti-place-card__media:not(:has(img)) { aspect-ratio: 16 / 7; } }

    .ti-place-card__top { position: absolute; top: 10px; left: 10px; right: 10px; z-index: 2; display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; pointer-events: none; }
    .ti-place-card__top > * { pointer-events: auto; }
    .ti-place-card__pills { display: flex; flex-wrap: wrap; gap: 6px; }
    .ti-place-card__pill {
        display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 11px; border-radius: 999px;
        background: rgb(255 255 255 / .94); color: var(--ti-ink); font-size: 12px; font-weight: 600; line-height: 1;
        box-shadow: 0 2px 8px rgb(14 59 42 / .12);
    }
    .ti-place-card__pill i { font-size: 11px; color: var(--ti-brand-orange); }
    .ti-place-card__pill.is-ok { background: var(--ti-forest-50); color: var(--ti-forest-800); }
    .ti-place-card__pill.is-ok i { color: var(--ti-forest); }
    .ti-place-card__fav {
        width: 44px; height: 44px; flex-shrink: 0; border: 0; border-radius: 999px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgb(255 255 255 / .94); color: var(--ti-ink); font-size: 17px;
        box-shadow: 0 2px 8px rgb(14 59 42 / .15); transition: transform .15s ease, color .15s ease;
    }
    .ti-place-card__fav i { color: #8AA99B; transition: color .15s ease; }
    .ti-place-card__fav:hover { transform: scale(1.08); }
    .ti-place-card__fav:hover i, .ti-place-card__fav.is-on i { color: #d4343c; }
    .ti-place-card__fav:focus-visible { outline: 3px solid var(--ti-brand-orange); outline-offset: 2px; }

    .ti-place-card__bottom { position: absolute; left: 12px; right: 12px; bottom: 12px; z-index: 2; display: flex; justify-content: space-between; align-items: center; transition: opacity .2s ease; }
    .ti-place-card__dots { display: flex; gap: 5px; }
    .ti-place-card__dots span { width: 7px; height: 4px; border-radius: 2px; background: rgb(255 255 255 / .6); }
    .ti-place-card__dots span.is-on { width: 20px; background: #ffffff; }
    .ti-place-card:hover .ti-place-card__dots span.is-on { width: 7px; background: rgb(255 255 255 / .6); }
    .ti-place-card:hover .ti-place-card__dots span:nth-child(2) { width: 20px; background: #ffffff; }
    .ti-place-card__count {
        display: inline-flex; align-items: center; gap: 6px; height: 26px; padding: 0 10px; border-radius: 999px;
        background: rgb(14 59 42 / .66); color: #ffffff; font-size: 12px; font-weight: 600;
    }
    .ti-place-card__count i { font-size: 11px; }

    /* Actions rapides : apparaissent au survol (souris) ; masquées sur écran tactile. */
    .ti-place-card__quick {
        position: absolute; left: 0; right: 0; bottom: 12px; z-index: 3;
        display: flex; justify-content: center; gap: 10px;
        opacity: 0; transform: translateY(8px); pointer-events: none;
        transition: opacity .2s ease, transform .2s ease;
    }
    .ti-place-card:hover .ti-place-card__quick, .ti-place-card__quick:focus-within { opacity: 1; transform: none; pointer-events: auto; }
    .ti-place-card:hover .ti-place-card__bottom { opacity: 0; }
    .ti-place-card__qbtn {
        width: 44px; height: 44px; border: 0; border-radius: 999px; cursor: pointer;
        display: inline-flex; align-items: center; justify-content: center;
        background: #ffffff; color: var(--ti-ink); font-size: 15px; text-decoration: none;
        box-shadow: 0 6px 16px rgb(14 59 42 / .25); transition: background .15s ease, color .15s ease;
    }
    .ti-place-card__qbtn:hover { background: var(--ti-action); color: #ffffff; }
    .ti-place-card__qbtn:focus-visible { outline: 3px solid var(--ti-brand-orange); outline-offset: 2px; }
    @media (hover: none) { .ti-place-card__quick { display: none; } }

    /* Corps */
    .ti-place-card__body { flex: 1; display: flex; flex-direction: column; gap: 8px; padding: 14px 8px 4px; }
    .ti-place-card__row { display: flex; justify-content: space-between; align-items: center; gap: 8px; }
    .ti-place-card__sector {
        display: inline-flex; align-items: center; gap: 7px; min-width: 0;
        font-size: 11px; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: var(--ti-action-700);
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .ti-place-card__sector i {
        width: 24px; height: 24px; flex-shrink: 0; border-radius: 7px; display: inline-flex; align-items: center; justify-content: center;
        background: var(--ti-action-50); color: var(--ti-action); font-size: 11px;
    }
    .ti-place-card__rate {
        display: inline-flex; align-items: center; gap: 5px; flex-shrink: 0; height: 26px; padding: 0 10px; border-radius: 999px;
        background: var(--ti-gold-100); color: #B85600; font-size: 12px; font-weight: 700;
    }
    .ti-place-card__rate i { font-size: 10px; color: var(--ti-gold); }
    .ti-place-card__rate small { font-size: 11px; font-weight: 500; color: #B85600; }
    .ti-place-card__rate.is-new { background: var(--ti-forest-50); color: var(--ti-forest-800); }
    .ti-place-card__rate.is-new i { color: var(--ti-forest); }
    .ti-place-card__name {
        margin: 0;
        font-family: var(--font-serif); font-size: 20px; font-weight: 700; line-height: 1.22; letter-spacing: -.01em;
        display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .ti-place-card__link { color: inherit; text-decoration: none; }
    .ti-place-card__link::after { content: ''; position: absolute; inset: 0; z-index: 1; border-radius: inherit; }
    .ti-place-card__link:focus-visible { outline: none; }
    .ti-place-card:has(.ti-place-card__link:focus-visible) { outline: 3px solid var(--ti-brand-orange); outline-offset: 3px; }
    .ti-place-card__location { display: flex; align-items: center; gap: 6px; min-width: 0; margin: 0; font-size: 13px; color: var(--ti-body); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .ti-place-card__location i {
        width: 20px; height: 20px; flex-shrink: 0; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
        background: var(--ti-forest-50); color: var(--ti-forest); font-size: 10px;
    }
    .ti-place-card__text {
        margin: 0; font-size: 13px; line-height: 1.55; color: var(--ti-body);
        display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
    }
    .ti-place-card__amenities { display: flex; flex-wrap: wrap; gap: 6px; margin: 0; padding: 0; list-style: none; }
    .ti-place-card__amenities li {
        display: inline-flex; align-items: center; gap: 6px; height: 28px; padding: 0 10px; border-radius: 10px;
        background: #F3F8F5; color: #24503D; font-size: 12px; font-weight: 500; white-space: nowrap;
    }
    .ti-place-card__amenities li i { font-size: 11px; color: var(--ti-muted); }
    .ti-place-card__amenities li.is-more { background: #ffffff; border: 1px dashed var(--ti-line-strong); color: var(--ti-muted); }
    .ti-place-card__trust { display: flex; flex-wrap: wrap; gap: 4px 12px; margin: 0; font-size: 12px; font-weight: 600; color: var(--ti-forest-800); }
    .ti-place-card__trust span { display: inline-flex; align-items: center; gap: 5px; }
    .ti-place-card__trust i { font-size: 11px; color: var(--ti-forest); }

    /* Pied : prix à gauche, action à droite */
    .ti-place-card__footer {
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        margin: 8px 8px 6px; padding-top: 12px; border-top: 1px solid #D8E9E0;
    }
    .ti-place-card__price { line-height: 1.15; white-space: nowrap; min-width: 0; }
    .ti-place-card__price small { display: block; margin-bottom: 3px; font-size: 12px; font-weight: 500; color: var(--ti-muted); }
    .ti-place-card__price strong { font-size: 17px; font-weight: 700; color: var(--ti-ink); letter-spacing: -.01em; }
    .ti-place-card__price em { font-style: normal; font-size: 12px; color: var(--ti-muted); }
    .ti-place-card__more {
        display: inline-flex; align-items: center; gap: 10px; flex-shrink: 0;
        height: 44px; padding: 0 5px 0 16px; border-radius: 999px;
        background: var(--ti-action); color: #ffffff; font-size: 13px; font-weight: 700; white-space: nowrap;
        transition: background .15s ease;
    }
    .ti-place-card__more i {
        width: 34px; height: 34px; border-radius: 999px; display: inline-flex; align-items: center; justify-content: center;
        background: rgb(255 255 255 / .2); font-size: 12px; transition: transform .2s ease;
    }
    .ti-place-card:hover .ti-place-card__more { background: var(--ti-action-700); }
    .ti-place-card:hover .ti-place-card__more i { transform: translateX(3px); }

    /* Petits écrans : prix et bouton tiennent sur une ligne. */
    @media (max-width: 420px) {
        .ti-place-card__price strong { font-size: 16px; }
        .ti-place-card__more { gap: 8px; padding-left: 13px; font-size: 12.5px; }
        .ti-place-card__more i { width: 30px; height: 30px; }
    }

    /* Message (favori ajouté, lien copié) */
    .ti-place-toast {
        position: fixed; left: 50%; bottom: 24px; z-index: 90; transform: translateX(-50%);
        max-width: calc(100vw - 32px); padding: 10px 16px; border-radius: 999px;
        background: var(--ti-ink); color: #ffffff; font-size: 14px; font-weight: 600; box-shadow: var(--ti-shadow-lg);
    }

    @media (prefers-reduced-motion: reduce) {
        .ti-place-card, .ti-place-card:hover, .ti-place-card:focus-within { transform: none; }
        .ti-place-card__media img, .ti-place-card__quick { transition: none; }
        .ti-place-card:hover .ti-place-card__media img { transform: none; }
    }

    /* ── Texte posé sur photo (voile « from-green-950/xx » vers le haut) ──
       Le pont de thème clair fonce .text-white & co. : sous ce voile, on garantit
       un voile réellement sombre et un texte clair. */
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] {
        background: rgba(14,59,42, 0.39) !important;
    }
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ .text-white,
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * .text-white,
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * h2,
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * h3 { color: #ffffff !important; }
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * [class*="text-slate-3"],
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * [class*="text-gray-3"],
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * [class*="text-white/"] { color: rgba(255,255,255,.82) !important; }
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * [class*="text-orange-3"],
    html:not(.dark) [class*="-to-t"][class*="from-green-950/"] ~ * [class*="text-gold-3"] { color: #F7C396 !important; }

    /* ── Bandeau d'en-tête des listes sectorielles ──
       Fond sable uni (au lieu d'un dégradé presque noir) ; le titre se pose
       directement dessus, sans carte flottante. Aucune image d'admin n'y est liée. */
    html:not(.dark) :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art) {
        background: var(--ti-paper) !important;
        padding-top: 56px !important;
        padding-bottom: 72px !important;
    }
    /* Carte crème autour du titre des bandeaux (listes, Magazine, Cultures, Agenda…) :
       opaque, sans flou de l'image derrière, texte sombre. */
    html:not(.dark) .ti-hero-card {
        background: #FFFFFF !important;
        border: 1px solid var(--ti-line);
        box-shadow: 0 12px 32px rgb(14 59 42 / .08);
        backdrop-filter: none !important;
        -webkit-backdrop-filter: none !important;
    }
    html:not(.dark) .ti-hero-card h1 { color: var(--ti-ink) !important; }
    html:not(.dark) .ti-hero-card h1 ~ p { color: var(--ti-body) !important; }
    html:not(.dark) :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art) h1 { color: var(--ti-ink) !important; }
    html:not(.dark) :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art) h1 + p { color: var(--ti-body) !important; font-size: 1.125rem; }
    html:not(.dark) :is(.hero-lodging, .hero-art) .text-orange-300 { color: var(--ti-action) !important; font-weight: 600; }

    /* Sur une liste sectorielle, l'étiquette répétant le nom du secteur sur chaque
       carte est redondante (les hébergements gardent la leur : elle indique le type). */
    html:not(.dark) .venue-cover > span.absolute { display: none !important; }

    /* ── Cartes des listes sectorielles (hébergements, restaurants, loisirs…) ── */
    html:not(.dark) .accom-card,
    html:not(.dark) .venue-card,
    html:not(.dark) .cat-card {
        background: #ffffff !important;
        border: 1px solid var(--ti-line) !important;
        box-shadow: var(--ti-shadow-sm) !important;
        transition: box-shadow .2s ease, border-color .2s ease !important;
    }
    html:not(.dark) .accom-card:hover,
    html:not(.dark) .venue-card:hover,
    html:not(.dark) .cat-card:hover {
        transform: none !important;
        border-color: var(--ti-line-strong) !important;
        box-shadow: var(--ti-shadow) !important;
    }
    html:not(.dark) .accom-cover,
    html:not(.dark) .venue-cover { background: var(--ti-sand) !important; }
    /* Pastilles décoratives floues dans le coin des cartes de rubrique : retirées. */
    html:not(.dark) .cat-card > .absolute.rounded-full { display: none !important; }
    html:not(.dark) .cat-card i.text-gold-400 { color: var(--ti-action) !important; }
    html:not(.dark) .cat-card .bg-white\/5 { background: var(--ti-action-50) !important; }
    html:not(.dark) .accom-cover > div > i,
    html:not(.dark) .venue-cover > div > i { color: #8AA99B !important; }
    html:not(.dark) .accom-card:hover .accom-cover img,
    html:not(.dark) .venue-card:hover .venue-cover img { transform: scale(1.03) !important; }
    /* Étiquette de secteur posée sur le visuel : pastille claire lisible sur photo comme sur sable. */
    html:not(.dark) .accom-cover > span.absolute,
    html:not(.dark) .venue-cover > span.absolute,
    html:not(.dark) .hero-cover > div.absolute > span,
    html:not(.dark) .item-cover > span.absolute:not(.zoom-hint),
    html:not(.dark) .room-cover > span.absolute:not(.zoom-hint) {
        background: rgba(255,255,255,.94) !important;
        border: 0 !important;
        color: var(--ti-ink) !important;
        backdrop-filter: none !important;
        letter-spacing: .04em;
    }

    /* ── Visuels des fiches et sous-pages (bandeau, prestations, chambres, plats) ──
       Sans photo, fond sable + pictogramme discret au lieu d'un bloc presque noir. */
    html:not(.dark) .hero-cover,
    html:not(.dark) .item-cover,
    html:not(.dark) .room-cover,
    html:not(.dark) .art-cover,
    html:not(.dark) .dish-cover-placeholder { background: var(--ti-sand) !important; }
    html:not(.dark) .hero-cover > div > i,
    html:not(.dark) .item-cover > div > i,
    html:not(.dark) .room-cover > div > i,
    html:not(.dark) .art-cover > div > i,
    html:not(.dark) .dish-cover > div > i { color: #8AA99B !important; }
    html:not(.dark) .dish-card,
    html:not(.dark) .item-card,
    html:not(.dark) .room-card {
        background: #ffffff !important;
        border: 1px solid var(--ti-line) !important;
        box-shadow: var(--ti-shadow-sm) !important;
        transition: box-shadow .2s ease, border-color .2s ease !important;
    }
    html:not(.dark) .dish-card:hover,
    html:not(.dark) .item-card:hover,
    html:not(.dark) .room-card:hover {
        transform: none !important;
        border-color: var(--ti-line-strong) !important;
        box-shadow: var(--ti-shadow) !important;
    }

    /* En-tête des sous-pages (carte, activités, circuits, offres) : même carte crème
       (.ti-hero-card) ; ses textes, prévus pour un fond vert foncé, passent en sombre. */
    html:not(.dark) .ti-hero-card .text-\[\#ffffff\] { color: var(--ti-ink) !important; }
    html:not(.dark) .ti-hero-card .text-\[\#F3F8F5\] { color: var(--ti-body) !important; }
    html:not(.dark) .ti-hero-card nav,
    html:not(.dark) .ti-hero-card nav .text-\[\#24503D\] { color: var(--ti-muted) !important; }
    html:not(.dark) .ti-hero-card nav a { color: var(--ti-body) !important; }
    html:not(.dark) .ti-hero-card nav a:hover { color: var(--ti-action) !important; text-decoration: underline; }
    html:not(.dark) .ti-hero-card nav span.text-\[\#ffffff\] { color: var(--ti-ink) !important; font-weight: 500; }
    html:not(.dark) .ti-hero-card nav + p.uppercase { color: var(--ti-action) !important; font-weight: 600; }

    /* Filtres latéraux des sous-pages (carte, activités, circuits, offres). */
    html:not(.dark) .filter-btn, html:not(.dark) .filter-label { text-align: left; }
    /* Mobile/tablette : rangée horizontale défilante — boutons à largeur naturelle,
       pour que plusieurs catégories soient visibles (au lieu d'un seul bouton pleine largeur). */
    @media (max-width: 1023px) {
        html:not(.dark) .filter-btn { width: auto !important; white-space: nowrap; padding: .5rem .8rem !important; }
    }
    html:not(.dark) .filter-btn:hover { transform: none !important; background: var(--ti-sand) !important; }
    html:not(.dark) .filter-btn.is-active {
        background: var(--ti-forest) !important;
        box-shadow: none !important;
    }
    html:not(.dark) .filter-btn.is-active .filter-icon { transform: none !important; }

    /* Cartes partenaires (accueil) : blanches à filet, comme les autres cartes. */
    html:not(.dark) .partner-card {
        background: #ffffff !important;
        border: 1px solid var(--ti-line) !important;
        box-shadow: var(--ti-shadow-sm) !important;
        transform: none !important;
    }
    html:not(.dark) .partner-card:hover { border-color: var(--ti-line-strong) !important; box-shadow: var(--ti-shadow) !important; }
    html:not(.dark) .partner-card .bg-white\/5,
    html:not(.dark) .partner-card [class*="bg-dark-7"] { background: var(--ti-action-50) !important; }

    /* Carte de contact des fiches : l'ancre #contact-card ne doit pas la glisser sous
       le header collant ; rappel de l'élément choisi (?item=…). */
    #contact-card { scroll-margin-top: 7.5rem; }
    .ti-contact-item {
        display: flex; align-items: flex-start; gap: 10px;
        margin-bottom: 14px; padding: 10px 12px;
        border-radius: 10px;
        background: var(--ti-action-50);
        border: 1px solid var(--ti-action-100);
        font-size: 13px; line-height: 1.45; color: var(--ti-body);
    }
    .ti-contact-item i { margin-top: 2px; font-size: 11px; color: var(--ti-action); }
    .ti-contact-item strong { color: var(--ti-ink); font-weight: 600; }

    /* ── Cartes d'article (Magazine) ──────────────────────── */
    html:not(.dark) .article-card {
        background: #ffffff !important;
        border: 1px solid var(--ti-line) !important;
        box-shadow: var(--ti-shadow-sm) !important;
        transition: box-shadow .2s ease, border-color .2s ease !important;
    }
    html:not(.dark) .article-card:hover { border-color: var(--ti-line-strong) !important; box-shadow: var(--ti-shadow) !important; }
    html:not(.dark) .article-card .card-img.bg-dark-700,
    html:not(.dark) .article-card .card-img.bg-\[\#ffffff\] { background: var(--ti-sand) !important; }
    html:not(.dark) .article-card h3 { color: var(--ti-ink) !important; }
    html:not(.dark) .ti-article-kicker { color: var(--ti-action); }
    /* Grande carte à la une : texte clair sur le voile sombre de la photo. */
    html:not(.dark) .ti-article-overlay h3,
    html:not(.dark) .ti-article-overlay span,
    html:not(.dark) .ti-article-overlay .text-gray-400 { color: #ffffff !important; }
    html:not(.dark) .ti-article-overlay .ti-article-kicker { color: #F7C396 !important; }
    html:not(.dark) .ti-article-overlay .text-gray-400 { opacity: .85; }


    /* Couleurs de secteur employées comme texte sur fond clair : teintes foncées
       équivalentes (contraste AA). Les zones sombres (bandeau photo, cartes
       .ti-on-dark) gardent la teinte d'origine. */
    html:not(.dark) .text-\[\#00874F\]:not([id$="hero-wrap"] *):not(.ti-on-dark *) { color: #00874F !important; }
    html:not(.dark) .text-\[\#00874F\]:not([id$="hero-wrap"] *):not(.ti-on-dark *) { color: #00874F !important; }
    html:not(.dark) .text-\[\#2980B9\]:not([id$="hero-wrap"] *):not(.ti-on-dark *) { color: #1f6391 !important; }
    html:not(.dark) .text-\[\#EB6E00\]:not([id$="hero-wrap"] *):not(.ti-on-dark *) { color: #B85600 !important; }
    html:not(.dark) .text-\[\#EB6E00\]:not([id$="hero-wrap"] *):not(.ti-on-dark *) { color: #EB6E00 !important; }
    /* Orange du logo (#EB6E00 / orange-500) employé comme texte : orange d'action lisible. */
    html:not(.dark) .text-\[\#EB6E00\]:not([id$="hero-wrap"] *):not(.ti-on-dark *),
    html:not(.dark) .text-orange-500:not([id$="hero-wrap"] *):not(.ti-on-dark *) { color: #EB6E00 !important; }

    /* ── Boutons ────────────────────────────────────────── */
    /* Action principale : orange plein, texte blanc, pas d'élévation au survol. */
    html:not(.dark) .btn-primary {
        background: var(--ti-action) !important;
        border: 1px solid var(--ti-action) !important;
        color: #ffffff !important;
        box-shadow: var(--ti-shadow-sm) !important;
        transition: background-color .15s ease, border-color .15s ease, box-shadow .15s ease !important;
    }
    html:not(.dark) .btn-primary i,
    html:not(.dark) .btn-primary .text-white { color: #ffffff !important; }
    html:not(.dark) .btn-primary:hover {
        background: var(--ti-action-700) !important;
        border-color: var(--ti-action-700) !important;
        transform: none !important;
        filter: none !important;
    }
    html:not(.dark) .btn-primary:active {
        background: var(--ti-action-800) !important;
    }
    html:not(.dark) .btn-primary:disabled,
    html:not(.dark) .btn-primary[aria-disabled="true"] {
        background: #D8E9E0 !important;
        border-color: #D8E9E0 !important;
        color: #557466 !important;
        cursor: not-allowed;
        box-shadow: none !important;
    }

    /* Boutons d'action écrits en utilitaires Tailwind (bg-orange-500/600, bg-gold-500) : même orange d'action. */
    html:not(.dark) button.bg-orange-500,
    html:not(.dark) a.bg-orange-500,
    html:not(.dark) button.bg-orange-600,
    html:not(.dark) a.bg-orange-600,
    html:not(.dark) button.bg-gold-500,
    html:not(.dark) a.bg-gold-500 {
        background-color: var(--ti-action) !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    html:not(.dark) button.bg-orange-500:hover,
    html:not(.dark) a.bg-orange-500:hover,
    html:not(.dark) button.bg-orange-600:hover,
    html:not(.dark) a.bg-orange-600:hover,
    html:not(.dark) button.bg-gold-500:hover,
    html:not(.dark) a.bg-gold-500:hover {
        background-color: var(--ti-action-700) !important;
    }
    html:not(.dark) button.bg-orange-500 i,
    html:not(.dark) a.bg-orange-500 i,
    html:not(.dark) button.bg-gold-500 i,
    html:not(.dark) a.bg-gold-500 i { color: #ffffff !important; }

    /* Badges (« À la une », « Populaire »…) en orange vif + texte noir : orange
       d'action + texte blanc, lisible et cohérent avec les boutons. */
    html:not(.dark) span.bg-orange-500:not(:empty),
    html:not(.dark) div.bg-orange-500:not(:empty),
    html:not(.dark) span.bg-gold-500:not(:empty) {
        background-color: var(--ti-action) !important;
        color: #ffffff !important;
    }
    html:not(.dark) span.bg-orange-500:not(:empty) i,
    html:not(.dark) div.bg-orange-500:not(:empty) i,
    html:not(.dark) span.bg-gold-500:not(:empty) i { color: #ffffff !important; }

    /* Pastilles de catégorie (Magazine, accueil) et bouton de recherche du Magazine :
       aplats sans dégradé ni lueur, catégorie active en vert forêt. */
    html:not(.dark) .category-chip {
        background: #ffffff !important;
        border: 1px solid var(--ti-line) !important;
        color: var(--ti-body) !important;
        box-shadow: none !important;
    }
    html:not(.dark) .category-chip:hover { border-color: var(--ti-line-strong) !important; color: var(--ti-ink) !important; }
    html:not(.dark) .category-chip.is-active {
        background: var(--ti-forest) !important;
        border-color: var(--ti-forest) !important;
        color: #ffffff !important;
    }
    html:not(.dark) .category-chip.is-active * { color: #ffffff !important; }
    html:not(.dark) .category-chip::after,
    html:not(.dark) .category-chip.is-active::after { display: none !important; }
    html:not(.dark) .articles-search-btn {
        background: var(--ti-action) !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }
    html:not(.dark) .articles-search-btn:hover { background: var(--ti-action-700) !important; }
    html:not(.dark) .articles-search-btn i { color: #ffffff !important; }

    /* Sélecteur de langue : langue active en vert forêt, texte blanc. */
    html:not(.dark) .lang-switch-ultra a.bg-green-500 {
        background: var(--ti-forest) !important;
        color: #ffffff !important;
        box-shadow: none !important;
    }

    /* ── Formulaires ────────────────────────────────────── */
    html:not(.dark) .field-label {
        color: var(--ti-ink) !important;
        font-weight: 600;
        font-size: 13px;
        letter-spacing: 0;
    }
    html:not(.dark) .field-input {
        background: #ffffff !important;
        border: 1px solid var(--ti-line-strong) !important;
        border-radius: 10px !important;
        color: var(--ti-ink) !important;
        transition: border-color .15s ease, box-shadow .15s ease;
    }
    html:not(.dark) .field-input:hover { border-color: #EB6E00 !important; }
    html:not(.dark) .field-input:focus {
        outline: none;
        border-color: var(--ti-action) !important;
        box-shadow: var(--ti-focus) !important;
    }
    html:not(.dark) input::placeholder,
    html:not(.dark) textarea::placeholder { color: #557466 !important; }
    html:not(.dark) input[type="checkbox"],
    html:not(.dark) input[type="radio"] { accent-color: var(--ti-action); }

    /* ── Onglets ────────────────────────────────────────── */
    html:not(.dark) .tab-btn { color: var(--ti-muted) !important; font-weight: 500; }
    html:not(.dark) .tab-btn:hover:not(.active) { color: var(--ti-ink) !important; }
    html:not(.dark) .tab-btn.active { color: var(--ti-action) !important; font-weight: 600; }
    html:not(.dark) .tab-btn.active::after { background: var(--ti-action) !important; }

    /* ── Pagination (vue vendor/pagination) ─────────────── */
    html:not(.dark) .ti-pagination a,
    html:not(.dark) .ti-pagination span[aria-current] > span,
    html:not(.dark) .ti-pagination span[aria-disabled] > span {
        min-width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0 12px;
        border-radius: var(--ti-radius-sm);
        border: 1px solid var(--ti-line);
        background: #ffffff;
        color: var(--ti-body);
        font-size: 14px;
        font-weight: 500;
        transition: border-color .15s ease, color .15s ease, background-color .15s ease;
    }
    html:not(.dark) .ti-pagination a:hover { border-color: var(--ti-line-strong); color: var(--ti-ink); }
    html:not(.dark) .ti-pagination span[aria-current] > span {
        background: var(--ti-forest);
        border-color: var(--ti-forest);
        color: #ffffff;
        font-weight: 600;
    }
    html:not(.dark) .ti-pagination span[aria-disabled] > span { color: #8AA99B; background: transparent; }

    /* ── Mouvement ──────────────────────────────────────── */
    /* Apparitions au défilement plus discrètes (les pages gardent leur logique). */
    html:not(.dark) .reveal:not(.is-visible):not(.visible) { transform: translateY(14px) !important; }
    @media (prefers-reduced-motion: reduce) {
        html:not(.dark) .reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
    }

    /* Motifs de secteur dans les bandeaux des listes : présents mais discrets (la carte du titre reste opaque). */
    :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art, .hero-regions, .hero-cultures, .hero-magazine, .hero-events) .ivm-g { opacity: .24 !important; }
    @media (max-width: 640px) { :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art, .hero-regions, .hero-cultures, .hero-magazine, .hero-events) .ivm-g { opacity: .3 !important; } }

    .hero-magazine.has-admin-bg .ivm-g, .hero-events[style*="--events-hero-bg-image"] .ivm-g { opacity: .13 !important; }
    .hero-events > a { background: #fff !important; border-color: #D8E9E0 !important; color: #B85600 !important; }

    /* Encart newsletter du pied de page : trame discrète côté formulaire, lisière kita en bas de la carte */
    .nl-motif .ivm-g { opacity: .2 !important; }
    .nl-motif > :not(.ivm) { position: relative; z-index: 1; }
    .nl-motif #newsletter-email { background: #fff !important; }
    .nl-kita { position: absolute; left: 0; right: 0; bottom: 0; height: 6px; background-color: #EB6E00; background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='48' height='6'><rect width='16' height='6' fill='%23EB6E00'/><rect x='16' width='6' height='6' fill='%23EB6E00'/><rect x='22' width='14' height='6' fill='%2300874F'/><rect x='36' width='4' height='6' fill='%23D8E9E0'/><rect x='40' width='8' height='6' fill='%2300874F'/></svg>"); background-size: 48px 6px; }

    /* En-têtes à carte (.ti-hero-card) sur mobile : compacts, la bande de motifs reste visible autour */
    @media (max-width: 640px) {
        html:not(.dark) :is(.hero-lodging, .hero-restaurant, .hero-leisure, .hero-experience, .hero-agency, .hero-transport, .hero-art, .hero-regions, .hero-cultures, .hero-magazine, .hero-events) { padding-top: 34px !important; padding-bottom: 40px !important; }
        html:not(.dark) .ti-hero-card { padding: 16px 14px 14px !important; border-radius: 18px !important; }
        html:not(.dark) .ti-hero-card h1 { font-size: clamp(24px, 7vw, 30px) !important; line-height: 1.1 !important; margin-bottom: 8px !important; }
        html:not(.dark) .ti-hero-card h1 ~ p { font-size: 14px !important; line-height: 1.45 !important; }
        .ti-hero-card > p:first-child, .ti-tour-hero__kicker { margin-bottom: 8px !important; font-size: 10.5px !important; padding: 5px 10px !important; }
        .ti-tour-hero__stats { margin-top: 12px !important; gap: 6px !important; }
        .ti-tour-hero__stats li { height: 32px !important; padding: 0 11px 0 4px !important; font-size: 12.5px !important; gap: 6px !important; }
        .ti-tour-hero__stats li i { width: 24px !important; height: 24px !important; font-size: 10.5px !important; }
    }

    /* ── Icônes de contenu en pastille ─────────────────────
       Icône posée en tête d'une ligne de texte (li, p, dd, petites lignes d'infos) : pastille ronde crème,
       fin contour sable, icône orange terre cuite. Spécificité nulle (:where) : toute icône déjà habillée par
       une page garde son style. Exclus : boutons, liens, menus, onglets, formulaires, pied de page, notes. */
    :where(main, section, article, aside, .ti-page) :where(li, p:not(.ti-tour-hero__kicker), dd, .ti-meta-row):not(:where(nav, #main-header, .public-header, #home-top-bar, body > footer, .footer-v2, .footer-ultra, #newsletter-footer, form, button, a, [role="tablist"], [role="menu"], .ti-tabbar, .flash-ticker, .mdd, .ox-tabs, .ox-bar, .ti-place-card__amenities, .ti-place-card__trust, .ti-tour-hero__stats, .ab-trust) *)
        > :where(.ico, i.fas, i.far, i.fa-solid, i.fa-regular):first-child:not(:where(.fa-star, .fa-star-half-stroke, .fa-spin, .fa-circle, .fa-check, .fa-xmark, .fa-chevron-right, .fa-chevron-down, .fa-arrow-right, .fa-arrow-left)) {
        flex: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 28px;
        height: 28px;
        margin-right: 8px;
        border-radius: 9px;
        background: var(--ti-ic-tint, #F3F8F5);
        border: 1px solid color-mix(in srgb, var(--ti-ic, #EB6E00) 22%, transparent);
        color: var(--ti-ic, #EB6E00);
        font-size: 12px;
        line-height: 1;
        vertical-align: middle;
    }
    :where(main, section, article, aside, .ti-page) :where(li, p, dd, .ti-meta-row) > :where(.ico):first-child > svg { width: 15px; height: 15px; }
    /* Sur fond sombre (blocs verts / encre) : pastille claire translucide, icône dorée */
    :where(.pr-pillar-main, .pr-cta, .cm-steps, .ap-quote, .tc-plan, [data-dark-surface]) :where(li, p) > :where(.ico, i.fas, i.far):first-child {
        background: rgba(255,255,255,.1);
        border-color: rgba(255,255,255,.18);
        color: #F7C396;
    }

    /* ── Modèle unique des pastilles d'icônes (référence : onglets « secteurs » de l'accueil) ─────────
       Carré arrondi (≈ 1/3 de la taille), fond teinté clair, liseré de la couleur à 22 %, icône dans la couleur.
       La couleur suit le thème de l'icône (Font Awesome, ou composant x-icon via data-icon), partout sur le site :
       charte « Le Joyau » (deux accents seulement) : Orange Ivoire pour hébergement, table, culture & agenda ;
       Vert Ivoire pour nature & lieux, voyage, transport & pratique ; icône non classée = orange.
       Pas de !important : les états propres à chaque page (actif, survol, fond sombre) restent prioritaires. */
@php
    $tiIconThemes = [
        // [couleur, teinte, icônes Font Awesome, icônes Solar (x-icon)]
        ['#EB6E00', '#FDEFE3', ['bed', 'hotel', 'building', 'house', 'house-chimney', 'door-open', 'key', 'bath', 'bell-concierge', 'store'], ['bed', 'sofa', 'armchair', 'bath', 'window-frame', 'sleeping', 'buildings', 'city', 'shop', 'chair']],
        ['#EB6E00', '#FDEFE3', ['utensils', 'bowl-food', 'bowl-rice', 'champagne-glasses', 'wine-glass', 'martini-glass', 'mug-hot', 'mug-saucer', 'burger', 'pizza-slice', 'fish', 'pepper-hot', 'plate-wheat', 'kitchen-set', 'cake-candles', 'wheat-awn'], ['plate', 'chef-hat', 'cup-hot', 'wineglass']],
        ['#00874F', '#E8F4EE', ['mountain-sun', 'mountain', 'tree', 'leaf', 'seedling', 'water', 'droplet', 'umbrella-beach', 'sun', 'paw', 'feather', 'location-dot', 'map-pin', 'map-location-dot', 'location-crosshairs', 'city', 'place-of-worship'], ['leaf', 'sun', 'umbrella', 'swimming', 'map-point', 'point-on-map', 'streets-map-point', 'gps', 'snowflake', 'meditation', 'walking', 'running', 'running-round']],
        ['#00874F', '#E8F4EE', ['plane', 'plane-departure', 'plane-arrival', 'route', 'map', 'compass', 'globe', 'earth-africa', 'suitcase', 'suitcase-rolling', 'passport', 'signs-post', 'diamond-turn-right'], ['plane', 'route', 'routing', 'map', 'compass', 'global', 'signpost-2', 'suitcase', 'streets-navigation', 'radar']],
        ['#EB6E00', '#FDEFE3', ['masks-theater', 'music', 'palette', 'landmark', 'monument', 'building-columns', 'ticket', 'drum', 'camera', 'film', 'book-open', 'newspaper', 'gem', 'trophy', 'calendar', 'calendar-days', 'calendar-check', 'scroll', 'pen-nib', 'photo-film'], ['masks', 'palette', 'camera', 'ticket', 'star', 'star-shine', 'confetti', 'book', 'notes', 'calendar', 'clapperboard-play', 'videocamera-record', 'video-frame-play-vertical', 'play', 'play-circle', 'gallery', 't-shirt', 'hanger', 'ferris-wheel', 'flag']],
        ['#00874F', '#E8F4EE', ['bus', 'van-shuttle', 'car', 'car-side', 'taxi', 'ship', 'train', 'motorcycle', 'clock', 'phone', 'envelope', 'circle-info', 'info', 'users', 'user', 'user-group', 'language', 'shield-halved', 'credit-card', 'wallet', 'wifi', 'square-parking'], ['bus', 'car', 'scooter', 'wheel', 'delivery', 'clock-circle', 'hourglass', 'history', 'sort-by-time', 'info-circle', 'shield-check', 'wallet-money', 'card', 'tag-price', 'user-id', 'user-rounded', 'users-group-rounded', 'users-group-two-rounded', 'chat-round-dots', 'accessibility', 'qr-code', 'wi-fi-router', 'calculator']],
    ];
    // Pastilles d'icônes des composants et des pages publiques (le conteneur reçoit fond, liseré et couleur).
    $tiIconChips = '.ti-readlist__head h3 i, .ti-people-card__capital i, .ti-event-card__meta i, .ti-art-card__category i, .ti-event-more__icon, .ti-city__where i, .ti-tour-hero__stats li i, .ti-place-card__sector i, .ti-place-card__location i, .hx-facts__icon, .hx-amen__icon, .hx-cond__icon, .pv-tip__ic, .pr-stat__ic, .pr-card__ic, .pl-card__ic, .tc-cat__ic, .ab-cat__ic, .dx-cat__icon, .mn-cat__icon, .lx-pk__icon, .vd-stat__ico, .vd-row__ico, .gr-prep__ico, .ts-way .ico, .rr__amen i, .annuaire-tab-icon, .ev-meta .ico, .ev-where .ico, .ev-card__list .ico, .ts-meta .ico, .ts-card__list .ico, .ox-keys .ico, .mdd-ico, .ci-kind__ic, .ci-empty__ic, .cf-facts dt, .cs-kind .ic';
@endphp
    :root { --ti-ic: #EB6E00; --ti-ic-tint: #FDEFE3; }
@foreach($tiIconThemes as [$tiC, $tiT, $tiFa, $tiSolar])
    @php
        $tiIcons = implode(', ', array_merge(array_map(fn ($n) => '.fa-'.$n, $tiFa), array_map(fn ($n) => '[data-icon="'.$n.'"]', $tiSolar)));
    @endphp
    :is({!! $tiIcons !!}), :is({!! $tiIconChips !!}):has(:is({!! $tiIcons !!})) { --ti-ic: {{ $tiC }}; --ti-ic-tint: {{ $tiT }}; }
@endforeach
    :is({!! $tiIconChips !!}) {
        border-radius: 32%;
        background: var(--ti-ic-tint);
        border: 1px solid color-mix(in srgb, var(--ti-ic) 22%, transparent);
        color: var(--ti-ic);
    }
    :is({!! $tiIconChips !!}) > :is(i, .ico) { color: inherit; }
</style>
