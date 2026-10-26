{{--
    Charte graphique « Le Joyau » (V1, octobre 2026) — point d'entrée unique du système visuel.

    Inclus depuis partials.theme-init : chargé sur toutes les pages publiques, prestataire, client et admin.
    Toute évolution de la charte (couleurs, police, boutons, rayons) se fait ici.

    Palette officielle — trois couleurs, aucune autre, pas de dégradé :
      Vert Ivoire   #00874F  textes, fonds, titres (≈ 30 %)
      Orange Ivoire #EB6E00  accent : boutons, filets, mots-clés (≈ 10 %)
      Blanc Ivoire  #FFFFFF  fond dominant (≈ 60 %)
    Nuances dérivées UNIQUEMENT de ces trois couleurs, pour la lisibilité (contraste WCAG AA) :
      vert profond (titres, texte), vert foncé (survol), teintes claires (fonds alternés, bordures),
      orange foncé (texte orange et survol des boutons).

    Typographie : une seule famille, Montserrat (ExtraBold titres, Bold rubriques, SemiBold boutons,
    Medium texte). Les anciennes familles (Inter, Playfair Display…) ont été remplacées dans les vues ;
    les classes utilitaires historiques convergent aussi vers Montserrat.
--}}
@php $tiLogos = \App\Models\SiteSetting::charterLogos(); @endphp
<link rel="icon" href="{{ $tiLogos['favicon_url'] }}" type="image/png" sizes="32x32">
<link rel="apple-touch-icon" href="{{ $tiLogos['apple_icon_url'] }}">
<meta name="theme-color" content="#00874F">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,500;1,600;1,700&display=swap" rel="stylesheet">
<style>
    :root {
        /* Palette officielle */
        --ti-green: #00874F;
        --ti-orange: #EB6E00;
        --ti-white: #FFFFFF;
        /* Nuances dérivées (lisibilité) */
        --ti-green-deep: #006B3F;      /* survol des éléments verts, fonds profonds */
        --ti-ink: #0E3B2A;             /* titres et texte principal */
        --ti-text: #24503D;            /* texte courant */
        --ti-muted: #557466;           /* texte secondaire (AA sur blanc) */
        --ti-faint: #8AA99B;           /* indications, éléments désactivés */
        --ti-line: #D8E9E0;            /* bordures */
        --ti-surface: #F3F8F5;         /* fonds alternés */
        --ti-green-tint: #E8F4EE;      /* fond teinté vert */
        --ti-orange-strong: #B85600;   /* texte orange, survol des boutons orange */
        --ti-orange-tint: #FDEFE3;     /* fond teinté orange */
        /* Typographie */
        --font-sans: 'Montserrat', ui-sans-serif, system-ui, -apple-system, 'Segoe UI', sans-serif;
        --font-serif: var(--font-sans);
        --font-elegant: var(--font-sans);
        --font-reading: var(--font-sans);
        --leading-body: 1.65;
        /* Formes */
        --ti-radius-sm: 10px;
        --ti-radius: 16px;
        --ti-radius-lg: 24px;
        --ti-focus: 0 0 0 3px rgba(235, 110, 0, .35);
    }

    html, body { font-family: var(--font-sans); font-weight: 500; -webkit-font-smoothing: antialiased; }
    .font-sans, .font-serif, .font-elegant, .font-plus, .font-display { font-family: var(--font-sans) !important; }
    .font-reading, .prose-content, .prose-info { font-family: var(--font-reading); line-height: var(--leading-body); }
    p { line-height: var(--leading-body); }

    /* Titres : Montserrat ExtraBold / Bold, interlettrage légèrement resserré (Montserrat est large). */
    :where(h1, h2) { font-weight: 800; letter-spacing: -.012em; }
    :where(h3, h4) { font-weight: 700; letter-spacing: -.005em; }
    /* Les majuscules gardent leurs accents (règle de la charte) : aucune transformation à appliquer. */

    /* Liens et focus : orange de la charte, visible au clavier. */
    :where(a, button, input, select, textarea, summary):focus-visible { outline: 2px solid var(--ti-orange); outline-offset: 2px; }
    ::selection { background: var(--ti-orange); color: #fff; }

    /* Charte : pas de dégradé. Les classes de dégradé encore présentes deviennent des aplats. */
    [class*="bg-gradient-to-"] { background-image: none !important; }

    /* Boutons orange (classes Tailwind historiques) : texte blanc et survol orange foncé, partout.
       Les classes à opacité (bg-orange-500/20…) sont des fonds teintés : non concernées. */
    .bg-orange-400, .bg-orange-500, .bg-orange-600, .btn-gold-header { color: #fff !important; }
    :is(.bg-orange-400, .bg-orange-500, .bg-orange-600) :is(.text-black, .text-dark-900, .text-slate-900, .text-gray-900) { color: inherit !important; }
    .hover\:bg-orange-600:hover, .hover\:bg-orange-500:hover, .hover\:bg-orange-400:hover { background-color: var(--ti-orange-strong) !important; }
    .bg-green-600, .bg-green-700, .bg-emerald-600 { color: #fff; }
    .hover\:bg-green-700:hover, .hover\:bg-emerald-700:hover { background-color: var(--ti-green-deep) !important; }

    /* ── En-têtes de section (charte « Le Joyau ») ───────────────────────────
       Surtitre en pastille orange clair précédée de l'emblème, titre Montserrat ExtraBold
       dont le dernier mot est en orange (comme « D'IVOIRE » dans le logo), filet bicolore
       orange + vert (aplats, aucun dégradé), chapô lisible. */
    .sec-head { max-width: 640px; }
    .sec-head--center { margin-left: auto; margin-right: auto; text-align: center; }
    .sec-kicker { display: inline-flex; align-items: center; gap: 8px; margin: 0 0 14px; padding: 5px 14px 5px 6px; border-radius: 999px; background: #FDEFE3; color: #B85600; font-size: 11.5px; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; line-height: 1.2; }
    .sec-kicker::before { content: ''; flex: none; width: 22px; height: 22px; border-radius: 999px; background: #fff url('/images/brand/emblem.png?v=2026-10') center / 13px auto no-repeat; box-shadow: 0 0 0 1px #F6D6BA; }
    .sec-title { margin: 0; font-size: clamp(30px, 3.2vw, 44px); font-weight: 800; line-height: 1.08; letter-spacing: -.02em; color: #0E3B2A !important; }
    .sec-title em { font-style: normal; color: #EB6E00; }
    .sec-title::after { content: ''; display: block; width: 44px; height: 4px; margin-top: 16px; border-radius: 2px; background: #EB6E00; box-shadow: 52px 0 0 0 #00874F; }
    .sec-head--center .sec-title::after { margin-left: calc(50% - 48px); }
    .sec-lead { margin: 16px 0 0; max-width: 56ch; font-size: 17px; font-weight: 500; line-height: 1.6; color: #557466 !important; }
    .sec-head--center .sec-lead { margin-left: auto; margin-right: auto; }
    @media (max-width: 640px) {
        .sec-kicker { font-size: 10.5px; letter-spacing: .12em; }
        .sec-title { font-size: 28px; }
        .sec-lead { font-size: 15px; }
    }

    /* Composants réutilisables de la charte (disponibles partout) */
    .ti-btn { display: inline-flex; align-items: center; justify-content: center; gap: .5rem; min-height: 48px; padding: 0 1.4rem; border-radius: 999px; border: 0; background: var(--ti-orange); color: #fff; font: 700 15px/1 var(--font-sans); text-decoration: none; cursor: pointer; transition: background .15s ease; }
    .ti-btn:hover { background: var(--ti-orange-strong); color: #fff; }
    .ti-btn--green { background: var(--ti-green); }
    .ti-btn--green:hover { background: var(--ti-green-deep); }
    .ti-btn--ghost { background: #fff; color: var(--ti-green); box-shadow: inset 0 0 0 1.5px var(--ti-green); }
    .ti-btn--ghost:hover { background: var(--ti-green-tint); color: var(--ti-green-deep); }
    .ti-card { background: #fff; border: 1px solid var(--ti-line); border-radius: var(--ti-radius-lg); }
    .ti-card:hover { box-shadow: 0 18px 36px -28px rgba(14, 59, 42, .45); }
    .ti-filet { display: block; width: 72px; height: 4px; border-radius: 2px; background: var(--ti-orange); }
</style>
