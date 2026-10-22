{{--
    Charte de l'espace client (pages sous layouts.visitor-public).
    Les vues utilisent des utilitaires Tailwind « sombres » (bg-green-900, text-slate-*, bg-slate-800…) :
    on les traduit ici, uniquement dans .va, vers la charte claire du site (crème, blanc, orange #b9500a,
    vert forêt, statuts aux couleurs du logo). Aplats ; ombre au survol seulement. Aucune logique touchée.
--}}
<style>
    html body { background: #f6f3ed !important; color: #1d1a16 !important; }

    /* ── En-tête de page ── */
    .va-head { position: relative; overflow: hidden; padding: 112px 0 26px; background: #fbf8f2; border-bottom: 1px solid #ece5d6; }
    .va-head > .ivm .ivm-g { opacity: .09 !important; }
    .va-head__in { position: relative; z-index: 1; max-width: 64rem; margin: 0 auto; padding: 0 24px; }
    .va-crumb { display: flex; align-items: center; gap: 8px; margin: 0; font-family: 'Inter', sans-serif; font-size: 13px; color: #6b6458; }
    .va-crumb a { color: #9a4308; font-weight: 600; text-decoration: none; }
    .va-crumb a:hover { text-decoration: underline; text-underline-offset: 3px; }
    .va-title { margin: 8px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(26px, 3vw, 34px); font-weight: 700; line-height: 1.12; color: #1d1a16 !important; }
    .va-top { height: 96px; }
    @media (max-width: 640px) {
        .va-head { padding: 92px 0 18px; }
        .va-head__in { padding: 0 16px; }
        .va-top { height: 76px; }
    }

    /* ── Messages flash ── */
    .va-flash > div { display: flex; align-items: center; gap: 10px; padding: 12px 14px !important; border-radius: 14px !important; font-size: 14px; }
    html .va-flash .bg-emerald-900\/30 { background: #eef6ef !important; border: 1px solid #cfe4d3 !important; color: #1f5a33 !important; }
    html .va-flash .bg-rose-900\/30 { background: #fdeceb !important; border: 1px solid #f5c2bd !important; color: #8a241c !important; }

    /* ── Typographie ── */
    .va { font-family: 'Inter', sans-serif; color: #1d1a16; }
    html .va .text-white, html .va .text-slate-100 { color: #1d1a16 !important; }
    html .va .text-slate-200 { color: #2f2a22 !important; }
    html .va .text-slate-300 { color: #3a342b !important; }
    html .va .text-slate-400 { color: #5f574b !important; }
    html .va .text-slate-500 { color: #6b6458 !important; }
    html .va .text-slate-600, html .va .text-slate-700 { color: #a89e8b !important; }
    html .va .text-orange-400, html .va .text-orange-300, html .va .text-orange-400\/80 { color: #b9500a !important; }
    html .va .text-gold-400, html .va .text-gold-300 { color: #9a6b0a !important; }
    .va h2, .va h3 { font-weight: 700; }

    /* ── Cartes et panneaux ── */
    html .va .bg-green-900, html .va .bg-green-950 { background: #fff !important; }
    html .va .border-slate-800, html .va .border-slate-700, html .va .border-slate-600 { border-color: #ece5d6 !important; }
    html .va .divide-slate-800 > * + *, html .va .divide-slate-700 > * + * { border-color: #f0eadf !important; }
    html .va .border-white\/10, html .va .border-white\/20 { border-color: #ece5d6 !important; }
    html .va .rounded-xl { border-radius: 18px !important; }
    html .va .rounded-2xl { border-radius: 22px !important; }
    html .va .bg-green-900.border, html .va .bg-green-900.rounded-xl, html .va .bg-green-900.rounded-2xl { transition: border-color .15s ease, box-shadow .2s ease; }
    html .va a.bg-green-900:hover, html .va .bg-green-900.hover\:border-orange-600\/50:hover, html .va .bg-green-900.hover\:border-orange-600\/40:hover { border-color: #d8cdb8 !important; box-shadow: 0 18px 30px -26px rgba(29,26,22,.5); }
    html .va .border-orange-500\/60 { border-color: #b9500a !important; box-shadow: inset 0 0 0 1px #b9500a; }

    /* ── Surfaces secondaires (vignettes, boutons neutres, champs) ── */
    html .va .bg-slate-800, html .va .bg-slate-800\/50, html .va .bg-slate-800\/60, html .va .bg-slate-800\/40, html .va .bg-slate-900, html .va .bg-white\/5, html .va .bg-white\/\[0\.04\] { background: #fbf9f5 !important; }
    html .va .bg-slate-700 { background: #f1ebdf !important; }
    html .va .hover\:bg-slate-700:hover, html .va .hover\:bg-slate-800:hover { background: #fdf4ec !important; }
    html .va .hover\:text-white:hover { color: #9a4308 !important; }
    html .va .hover\:border-slate-500:hover, html .va .hover\:border-slate-600:hover { border-color: #b9500a !important; }
    html .va a.bg-slate-800, html .va button.bg-slate-800 { color: #3a2f22 !important; font-weight: 600; }

    /* ── Boutons principaux : orange de la charte, texte blanc ── */
    html .va .bg-orange-500, html .va .bg-orange-600 { background: #b9500a !important; color: #fff !important; border-color: #b9500a !important; }
    html .va .bg-orange-500.text-black, html .va .bg-orange-500 .text-black { color: #fff !important; }
    html .va .hover\:bg-orange-600:hover, html .va .hover\:bg-orange-400:hover { background: #9a4308 !important; }
    html .va button.bg-orange-500, html .va a.bg-orange-500 { border-radius: 999px !important; font-weight: 700; }
    html .va .bg-emerald-600 { background: #1f7a43 !important; color: #fff !important; }
    html .va .bg-emerald-600:hover { background: #17623a !important; }

    /* ── Étiquettes de statut (couleurs du logo) ── */
    html .va .bg-blue-500\/10, html .va .bg-blue-500\/20 { background: rgba(31,128,121,.12) !important; }
    html .va .text-blue-300, html .va .text-blue-400 { color: #1a6b65 !important; }
    html .va .bg-emerald-500\/10, html .va .bg-emerald-500\/20, html .va .bg-emerald-900\/30 { background: rgba(74,138,42,.13) !important; }
    html .va .text-emerald-300, html .va .text-emerald-200, html .va .text-emerald-400 { color: #2f6a17 !important; }
    html .va .bg-orange-500\/10, html .va .bg-orange-500\/20, html .va .bg-orange-500\/15 { background: rgba(217,101,15,.12) !important; }
    html .va .bg-amber-500\/10, html .va .bg-amber-500\/20 { background: rgba(201,138,12,.14) !important; }
    html .va .text-amber-300, html .va .text-amber-200, html .va .text-amber-400 { color: #8a5e05 !important; }
    html .va .bg-rose-500\/10, html .va .bg-rose-500\/20, html .va .bg-rose-900\/30, html .va .bg-red-500\/10 { background: rgba(192,54,44,.1) !important; }
    html .va .text-rose-300, html .va .text-rose-200, html .va .text-rose-400, html .va .text-red-300, html .va .text-red-400 { color: #a3291f !important; }
    html .va .hover\:text-rose-200:hover, html .va .hover\:text-rose-300:hover { color: #8a241c !important; }
    html .va .border-rose-500\/30, html .va .border-rose-700\/40 { border-color: #f5c2bd !important; }
    html .va .border-emerald-500\/30, html .va .border-emerald-700\/40, html .va .border-emerald-700\/30 { border-color: #cfe4d3 !important; }
    html .va .rounded-full.text-\[11px\], html .va span.rounded-full.text-xs { font-weight: 700 !important; }

    /* ── Champs de formulaire ── */
    html .va input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):not([type="file"]),
    html .va select, html .va textarea {
        background: #fbf9f5 !important; border: 1.5px solid #e1d7c4 !important; color: #1d1a16 !important; border-radius: 12px !important;
        transition: border-color .15s ease, background .15s ease, box-shadow .15s ease;
    }
    html .va input::placeholder, html .va textarea::placeholder { color: #9a8f7c !important; }
    html .va input:not([type="checkbox"]):not([type="radio"]):focus, html .va select:focus, html .va textarea:focus {
        outline: none !important; border-color: #b9500a !important; background: #fff !important; box-shadow: 0 0 0 4px rgba(185,80,10,.12) !important;
    }
    html .va input[type="checkbox"], html .va input[type="radio"] { accent-color: #b9500a; }
    html .va label { color: #1d1a16; }

    /* ── Tableaux ── */
    html .va table { border-collapse: separate; border-spacing: 0; }
    html .va thead th { background: #fbf9f5 !important; color: #6b6458 !important; font-size: 12px; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; }
    html .va tbody tr:hover td { background: #fdfbf7; }

    /* ── Pagination ── */
    html .va nav[role="navigation"] a, html .va nav[role="navigation"] span[aria-current] > span, html .va nav[role="navigation"] span > span { border-radius: 10px !important; }
    html .va nav[role="navigation"] span[aria-current] > span { background: #b9500a !important; border-color: #b9500a !important; color: #fff !important; }

    /* ── États vides ── */
    .va-empty { padding: 40px 20px; text-align: center; }
    .va-empty__ico { display: inline-flex; align-items: center; justify-content: center; width: 64px; height: 64px; border-radius: 20px; background: #fdf4ec; color: #b9500a; }
    .va-empty__ico .ico { width: 30px; height: 30px; }
    .va-empty__title { margin: 14px 0 0; font-family: 'Playfair Display', serif; font-size: 19px; font-weight: 700; color: #1d1a16; }
    .va-empty__text { max-width: 44ch; margin: 6px auto 0; font-size: 14px; line-height: 1.55; color: #6b6458; }
    .va-empty__cta { display: inline-flex; align-items: center; gap: 8px; height: 44px; margin-top: 16px; padding: 0 20px; border-radius: 999px; background: #b9500a; color: #fff !important; font-size: 14px; font-weight: 700; text-decoration: none; }
    .va-empty__cta:hover { background: #9a4308; }
    .va-empty__cta .ico { width: 16px; height: 16px; }
    .va .space-y-3 > .va-empty { background: #fff; border: 1px solid #ece5d6; border-radius: 22px; }

    /* ── Chiffres en 5 colonnes : compacts sur mobile (au lieu de 3 rangées) ── */
    @media (max-width: 640px) {
        html .va .grid.sm\:grid-cols-5 { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; gap: 6px !important; }
        html .va .grid.sm\:grid-cols-5 > a { padding: 10px 4px !important; text-align: center; border-radius: 14px !important; }
        html .va .grid.sm\:grid-cols-5 > a > div:first-child { display: none !important; }
        html .va .grid.sm\:grid-cols-5 > a > p:last-child { font-size: 10.5px !important; line-height: 1.2; }
    }

    /* ── Focus visible ── */
    .va a:focus-visible, .va button:focus-visible, .va summary:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
</style>
