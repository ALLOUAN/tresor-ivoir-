{{--
    Thème de l'administration — aux couleurs du logo, en aplats (aucun dégradé).
    Palette du logo : vert forêt #00874F · vert feuille #00874F · sarcelle #00874F · orange soleil #EB6E00 / #EB6E00
                      ambre #EB6E00 · or #EB6E00 · crème #F3F8F5 · noir chaud #0E3B2A.
    Appliqué seulement au rôle admin (body.adm) ; remplace le pont de thème beige pour ce rôle.
--}}
<style>
    html:not(.dark) body.adm {
        --adm-forest: #00874F; --adm-forest-hover: #00874F; --adm-forest-deep: #00874F;
        --adm-leaf: #00874F; --adm-teal: #00874F; --adm-orange: #EB6E00; --adm-orange-hover: #EB6E00; --adm-orange-text: #EB6E00;
        --adm-amber: #EB6E00; --adm-gold: #EB6E00; --adm-cream: #F3F8F5; --adm-page: #F3F8F5; --adm-surface: #ffffff; --adm-tint: #F3F8F5;
        --adm-line: #F7C396; --adm-ink: #0E3B2A; --adm-soft: #B85600;
        background-color: var(--adm-page) !important; color: var(--adm-ink) !important;
    }

    /* ── Barre latérale : vert forêt uni, texte crème, repère ambre ─────────────────────────────── */
    /* Même fond que la carte d'accueil du tableau de bord : vert forêt profond + motifs ivoiriens discrets. */
    html:not(.dark) body.adm .admin-sidebar { background: #00874F; border-right: 0; box-shadow: none; }
    html:not(.dark) body.adm .admin-sidebar > .ivm .ivm-g { opacity: .05 !important; }
    html:not(.dark) body.adm .admin-sidebar > .ivm .ivm-g > * { stroke: #D8E9E0 !important; }
    html:not(.dark) body.adm .admin-sidebar > .ivm .ivm-g > circle[fill] { fill: #EB6E00 !important; stroke: #EB6E00 !important; }
    html:not(.dark) body.adm .admin-sidebar > :not(.ivm) { position: relative; z-index: 1; }

    /* Espace prestataire : fond de page à motifs (5 %), fixe derrière le contenu. */
    body.adm-provider .pv-bg { position: fixed; inset: 0; z-index: -1; pointer-events: none; }
    @media (min-width: 1024px) { body.adm-provider .pv-bg { left: 18rem; } }
    body.adm-provider .pv-bg > .ivm .ivm-g { opacity: .05 !important; }
    body.adm-provider .pv-bg > .ivm .ivm-g > :nth-child(odd) { stroke: #B85600 !important; }
    body.adm-provider .pv-bg > .ivm .ivm-g > :nth-child(even) { stroke: #00874F !important; }
    html:not(.dark) body.adm .admin-sidebar-brand { background: transparent; border-bottom: 1px solid rgba(243,248,245, .16) !important; }
    html:not(.dark) body.adm .admin-sidebar-brand > div:first-child { background: var(--adm-cream) !important; border-color: var(--adm-gold) !important; }
    html:not(.dark) body.adm .admin-sidebar .text-white,
    html:not(.dark) body.adm .admin-sidebar .text-slate-100 { color: var(--adm-cream) !important; }
    html:not(.dark) body.adm .admin-sidebar .text-slate-500,
    html:not(.dark) body.adm .admin-sidebar .text-slate-400 { color: #DFF0E7 !important; }
    html:not(.dark) body.adm .admin-sidebar .border-slate-800,
    html:not(.dark) body.adm .admin-sidebar .border-slate-700\/50 { border-color: rgba(243,248,245, .16) !important; }
    html:not(.dark) body.adm .admin-sidebar .text-orange-300,
    html:not(.dark) body.adm .admin-sidebar .text-orange-400,
    html:not(.dark) body.adm .admin-sidebar .text-orange-500 { color: var(--adm-amber) !important; }
    html:not(.dark) body.adm .admin-sidebar .role-pill { background: var(--adm-amber); border: 0; box-shadow: none; color: var(--adm-ink) !important; }
    html:not(.dark) body.adm .admin-sidebar .role-pill i { color: var(--adm-ink); }
    html:not(.dark) body.adm .admin-sidebar .nav-section-title { color: var(--adm-amber); font-size: .66rem; letter-spacing: .15em; }
    html:not(.dark) body.adm .admin-sidebar .nav-section-title::after { background: rgba(243,248,245, .18); }
    html:not(.dark) body.adm .admin-sidebar .nav-group > summary:hover { color: var(--adm-cream); }
    html:not(.dark) body.adm .admin-sidebar .nav-group > summary:focus-visible { outline-color: var(--adm-amber); }
    html:not(.dark) body.adm .admin-sidebar .nav-row { color: #D8E9E0; }
    html:not(.dark) body.adm .admin-sidebar .nav-row:hover { background: #00874F; color: #ffffff; }
    html:not(.dark) body.adm .admin-sidebar .nav-row.is-active { background: var(--adm-amber); color: var(--adm-ink); box-shadow: none; font-weight: 700; }
    html:not(.dark) body.adm .admin-sidebar .nav-row-icon { background: rgba(243,248,245, .12); border: 0; color: var(--adm-cream); }
    html:not(.dark) body.adm .admin-sidebar .nav-row:hover .nav-row-icon { background: rgba(243,248,245, .2); color: #ffffff; }
    html:not(.dark) body.adm .admin-sidebar .nav-row.is-active .nav-row-icon { background: var(--adm-ink); color: var(--adm-amber); }
    html:not(.dark) body.adm .admin-sidebar .nav-scroll { scrollbar-color: rgba(243,248,245, .35) transparent; }
    html:not(.dark) body.adm .admin-sidebar-footer { background: transparent; border-top: 1px solid rgba(243,248,245, .16); }
    html:not(.dark) body.adm .admin-sidebar .user-card { background: var(--adm-forest-deep); border: 0; }
    html:not(.dark) body.adm .admin-sidebar .user-avatar { box-shadow: 0 0 0 2px var(--adm-amber); }
    html:not(.dark) body.adm .admin-sidebar .user-card button,
    html:not(.dark) body.adm .admin-sidebar .user-card a { background: rgba(243,248,245, .12) !important; border: 0 !important; color: var(--adm-cream) !important; }
    html:not(.dark) body.adm #sidebar-overlay { background: rgba(14,59,42, .55) !important; }

    /* Menu prestataire (liens .sidebar-link et titres de section) : même rendu que les .nav-row de l'admin. */
    html:not(.dark) body.adm .admin-sidebar nav > p.uppercase {
        display: flex; align-items: center; gap: .5rem; padding: 1rem .75rem .4rem; margin-top: .15rem;
        font-size: .66rem; font-weight: 700; letter-spacing: .15em; color: var(--adm-amber) !important;
    }
    html:not(.dark) body.adm .admin-sidebar nav > p.uppercase::after { content: ''; flex: 1; height: 1px; background: rgba(243,248,245, .18); }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link {
        gap: .75rem; padding: .4rem .65rem .4rem .5rem; margin-bottom: 1px; border-radius: .75rem;
        color: #D8E9E0; font-weight: 500; transition: background .18s ease, color .18s ease;
    }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link > i:first-child {
        width: 2.1rem !important; height: 2.1rem; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
        border-radius: .55rem; background: rgba(243,248,245, .12); color: var(--adm-cream); font-size: .8rem;
    }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link:hover { background: #00874F; color: #ffffff; }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link:hover > i:first-child { background: rgba(243,248,245, .2); color: #ffffff; }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link.active { background: var(--adm-amber); color: var(--adm-ink) !important; font-weight: 700; }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link.active > i:first-child { background: var(--adm-ink); color: var(--adm-amber); }
    html:not(.dark) body.adm .admin-sidebar .sidebar-link .text-slate-500,
    html:not(.dark) body.adm .admin-sidebar .sidebar-link .text-slate-400 { color: #DFF0E7 !important; }

    /* ── Barre du haut ───────────────────────────────────────────────────────────────────────────── */
    html:not(.dark) body.adm header.sticky { background: var(--adm-surface) !important; backdrop-filter: none; border-bottom: 1px solid var(--adm-line) !important; height: auto; min-height: 3.75rem; padding-top: .45rem; padding-bottom: .45rem; }
    html:not(.dark) body.adm header.sticky h1 { color: var(--adm-ink) !important; font-weight: 800; letter-spacing: -.01em; }
    html:not(.dark) body.adm header.sticky h1::before { content: ''; display: inline-block; width: 6px; height: 1.05em; margin-right: .6rem; border-radius: 3px; background: var(--adm-orange); vertical-align: -.15em; }
    html:not(.dark) body.adm header.sticky a[title="Notifications"] { background: var(--adm-tint) !important; border: 1px solid var(--adm-line) !important; color: var(--adm-ink) !important; }
    html:not(.dark) body.adm header.sticky a[title="Notifications"]:hover { background: var(--adm-amber) !important; }
    html:not(.dark) body.adm header.sticky .text-slate-500 { color: var(--adm-soft) !important; }
    html:not(.dark) body.adm header.sticky button { color: var(--adm-ink) !important; }

    /* ── Contenu : surfaces blanches, filets or, texte noir chaud ───────────────────────────────── */
    html:not(.dark) body.adm .bg-green-950 { background-color: var(--adm-page) !important; }
    html:not(.dark) body.adm .bg-slate-800,
    html:not(.dark) body.adm .bg-slate-800\/30,
    html:not(.dark) body.adm .bg-slate-800\/40,
    html:not(.dark) body.adm .bg-slate-800\/50,
    html:not(.dark) body.adm .bg-slate-800\/60,
    html:not(.dark) body.adm .bg-slate-800\/70 { background-color: var(--adm-surface) !important; }
    html:not(.dark) body.adm .bg-green-900 { background-color: var(--adm-surface) !important; }
    html:not(.dark) body.adm .bg-green-950\/70 { background-color: var(--adm-tint) !important; }
    html:not(.dark) body.adm .bg-slate-700,
    html:not(.dark) body.adm .bg-slate-700\/70 { background-color: #F7C396 !important; }
    html:not(.dark) body.adm .bg-slate-900 { background-color: var(--adm-tint) !important; }
    html:not(.dark) body.adm .border-slate-800,
    html:not(.dark) body.adm .border-slate-700,
    html:not(.dark) body.adm .border-slate-700\/70,
    html:not(.dark) body.adm .border-slate-700\/80,
    html:not(.dark) body.adm .border-slate-600,
    html:not(.dark) body.adm .border-white\/10 { border-color: var(--adm-line) !important; }
    html:not(.dark) body.adm .divide-slate-800 > * + *,
    html:not(.dark) body.adm .divide-slate-800\/80 > * + * { border-color: var(--adm-line) !important; }
    html:not(.dark) body.adm .text-white,
    html:not(.dark) body.adm .text-slate-100 { color: var(--adm-ink) !important; }
    html:not(.dark) body.adm .text-slate-200 { color: #0E3B2A !important; }
    html:not(.dark) body.adm .text-slate-300 { color: #0E3B2A !important; }
    html:not(.dark) body.adm .text-slate-400 { color: var(--adm-soft) !important; }
    html:not(.dark) body.adm .text-slate-500 { color: #B85600 !important; }
    html:not(.dark) body.adm .text-slate-600 { color: #EB6E00 !important; }
    html:not(.dark) body.adm .text-slate-700 { color: #EB6E00 !important; }
    html:not(.dark) body.adm .text-orange-300,
    html:not(.dark) body.adm .text-orange-400,
    html:not(.dark) body.adm .text-orange-500 { color: var(--adm-orange-text) !important; }
    html:not(.dark) body.adm .text-emerald-300,
    html:not(.dark) body.adm .text-emerald-400,
    html:not(.dark) body.adm .text-green-400,
    html:not(.dark) body.adm .text-green-100\/80 { color: var(--adm-forest) !important; }
    html:not(.dark) body.adm .text-red-300,
    html:not(.dark) body.adm .text-red-400 { color: #b3261e !important; }
    html:not(.dark) body.adm .text-rose-200 { color: #a4223a !important; }

    /* cartes et blocs d'état en aplats teintés */
    html:not(.dark) body.adm .bg-emerald-900\/30 { background-color: #D8E9E0 !important; }
    html:not(.dark) body.adm .border-emerald-800,
    html:not(.dark) body.adm .border-emerald-500\/30 { border-color: #DFF0E7 !important; }
    html:not(.dark) body.adm .bg-red-900\/30,
    html:not(.dark) body.adm .bg-red-900\/40 { background-color: #fde8e4 !important; }
    html:not(.dark) body.adm .border-red-800 { border-color: #f1b7ae !important; }
    html:not(.dark) body.adm .bg-rose-900\/30 { background-color: #fde6ea !important; }
    html:not(.dark) body.adm .border-rose-700\/40 { border-color: #f0b4c0 !important; }
    html:not(.dark) body.adm .bg-red-700\/90 { background-color: #c0392b !important; color: #fff !important; }

    /* pastilles de statut : aplats teintés + texte foncé lisible (orange = en attente, vert = ok, rouge = erreur) */
    html:not(.dark) body.adm .bg-orange-900\/40,
    html:not(.dark) body.adm .bg-orange-500\/20,
    html:not(.dark) body.adm .bg-orange-500\/15,
    html:not(.dark) body.adm .bg-orange-500\/5,
    html:not(.dark) body.adm .bg-orange-900\/30 { background-color: #FDEFE3 !important; }
    html:not(.dark) body.adm .text-orange-200,
    html:not(.dark) body.adm .text-orange-100,
    html:not(.dark) body.adm .text-amber-200 { color: #B85600 !important; }
    html:not(.dark) body.adm [class*="border-orange-500/"] { border-color: #F7C396 !important; }
    html:not(.dark) body.adm .bg-emerald-500\/20,
    html:not(.dark) body.adm .bg-emerald-500\/15,
    html:not(.dark) body.adm .bg-emerald-900\/40 { background-color: #D8E9E0 !important; }
    html:not(.dark) body.adm .text-emerald-200,
    html:not(.dark) body.adm .text-green-200,
    html:not(.dark) body.adm .text-green-300,
    html:not(.dark) body.adm .text-green-300\/80 { color: var(--adm-forest-deep) !important; }
    html:not(.dark) body.adm [class*="border-emerald-"], html:not(.dark) body.adm [class*="border-green-500/"] { border-color: #DFF0E7 !important; }
    html:not(.dark) body.adm .bg-red-900\/50,
    html:not(.dark) body.adm .bg-red-900\/80,
    html:not(.dark) body.adm .bg-red-500\/20 { background-color: #fbd9d3 !important; }
    html:not(.dark) body.adm .text-red-200 { color: #8f1d16 !important; }
    html:not(.dark) body.adm .bg-rose-500\/20,
    html:not(.dark) body.adm .bg-rose-500\/10 { background-color: #fbdfe5 !important; }
    html:not(.dark) body.adm .text-rose-100,
    html:not(.dark) body.adm .text-rose-300,
    html:not(.dark) body.adm .text-rose-400 { color: #a4223a !important; }
    html:not(.dark) body.adm [class*="border-rose-"] { border-color: #f0b4c0 !important; }
    /* boutons pleins */
    html:not(.dark) body.adm .bg-orange-600 { background-color: var(--adm-orange) !important; color: var(--adm-ink) !important; }
    html:not(.dark) body.adm .bg-orange-400 { background-color: var(--adm-amber) !important; color: var(--adm-ink) !important; }
    html:not(.dark) body.adm .bg-red-600,
    html:not(.dark) body.adm .bg-red-700 { background-color: #c0392b !important; color: #ffffff !important; }
    html:not(.dark) body.adm .bg-emerald-600,
    html:not(.dark) body.adm .bg-emerald-500 { background-color: var(--adm-forest) !important; color: #ffffff !important; }
    /* aucun dégradé dans l'administration : bandeaux et tuiles deviennent des aplats */
    html:not(.dark) body.adm [class*="bg-gradient-to-"] { background-image: none !important; }
    html:not(.dark) body.adm .from-green-700,
    html:not(.dark) body.adm .from-green-500 { background-color: #F7C396 !important; }
    html:not(.dark) body.adm .from-green-900\/40 { background-color: #D8E9E0 !important; }
    html:not(.dark) body.adm .from-emerald-900\/40 { background-color: #D8E9E0 !important; }
    html:not(.dark) body.adm .from-slate-800\/60 { background-color: #F3F8F5 !important; }
    html:not(.dark) body.adm .from-rose-900\/40 { background-color: #fbe5e4 !important; }
    html:not(.dark) body.adm .from-orange-900\/40 { background-color: #F3F8F5 !important; }

    /* boutons et actions */
    html:not(.dark) body.adm .bg-orange-500,
    html:not(.dark) body.adm .bg-orange-500\/90 { background-color: var(--adm-orange) !important; color: var(--adm-ink) !important; }
    html:not(.dark) body.adm .bg-orange-500:hover,
    html:not(.dark) body.adm .hover\:bg-orange-600:hover { background-color: var(--adm-orange-hover) !important; }
    html:not(.dark) body.adm .text-black { color: var(--adm-ink) !important; }
    html:not(.dark) body.adm a.bg-orange-500,
    html:not(.dark) body.adm button.bg-orange-500 { font-weight: 700; border-radius: .75rem; }

    /* formulaires */
    html:not(.dark) body.adm input:not([type="checkbox"]):not([type="radio"]):not([type="color"]):not([type="file"]),
    html:not(.dark) body.adm select,
    html:not(.dark) body.adm textarea { background-color: #ffffff !important; border-color: #F7C396 !important; color: var(--adm-ink) !important; }
    html:not(.dark) body.adm input:focus, html:not(.dark) body.adm select:focus, html:not(.dark) body.adm textarea:focus {
        outline: 2px solid var(--adm-orange); outline-offset: 0; border-color: var(--adm-orange) !important; box-shadow: none !important;
    }
    html:not(.dark) body.adm input[type="checkbox"], html:not(.dark) body.adm input[type="radio"] { accent-color: var(--adm-forest); }
    html:not(.dark) body.adm ::placeholder { color: #557466; }

    /* tableaux : en-têtes verts, lignes lisibles */
    html:not(.dark) body.adm main table thead th,
    html:not(.dark) body.adm main table thead th[class],
    html:not(.dark) body.adm main table thead th[class] * { background-color: var(--adm-forest); color: var(--adm-cream) !important; border-color: var(--adm-forest) !important; }
    html:not(.dark) body.adm main table thead th[class] * { background-color: transparent; }
    html:not(.dark) body.adm main table tbody tr { background-color: transparent !important; }
    html:not(.dark) body.adm main table tbody tr:hover { background-color: var(--adm-tint) !important; }
    html:not(.dark) body.adm main table tbody td { border-color: var(--adm-line); }

    /* cartes : coins plus doux et ombre franche */
    html:not(.dark) body.adm main .rounded-2xl.border,
    html:not(.dark) body.adm main .rounded-xl.border { box-shadow: 0 1px 0 rgba(14,59,42, .04), 0 8px 20px -16px rgba(14,59,42, .35); }

    /* messages */
    html:not(.dark) body.adm #success-toast { background: #D8E9E0 !important; border-color: #DFF0E7 !important; color: var(--adm-forest-deep) !important; box-shadow: 0 10px 24px -12px rgba(14,59,42, .4); }

    /* ══ Aucun dégradé, nulle part (syntaxes Tailwind v3 et v4) ═══════════════════════════════════ */
    html:not(.dark) body.adm [class*="bg-gradient-to-"],
    html:not(.dark) body.adm [class*="bg-linear-to-"],
    html:not(.dark) body.adm [class*="bg-radial"] { background-image: none !important; }
    html:not(.dark) body.adm main [class*="bg-linear-to-"][class*="from-green-9"] { background-color: var(--adm-surface) !important; }
    html:not(.dark) body.adm .premium-shimmer-card::before,
    html:not(.dark) body.adm .premium-shimmer-card::after { display: none !important; }
    html:not(.dark) body.adm [class*="shadow-green-9"],
    html:not(.dark) body.adm [class*="shadow-orange-9"] { box-shadow: 0 1px 0 rgba(14,59,42, .04), 0 8px 20px -16px rgba(14,59,42, .35) !important; }
    /* avatars et pastilles qui étaient en dégradé orange : aplat orange */
    html:not(.dark) body.adm [class*="bg-gradient-to-"]:is([class*="from-orange-4"], [class*="from-orange-5"]) { background-color: var(--adm-orange) !important; }

    /* ══ Couleurs : uniquement les aplats du logo ═════════════════════════════════════════════════
       vert forêt #00874F · vert feuille #00874F · sarcelle #00874F · orange #EB6E00 · ambre #EB6E00 · or #EB6E00 */
    /* rouge / rose (alertes, refus) → orange du logo */
    html:not(.dark) body.adm [class*="text-red-"],
    html:not(.dark) body.adm [class*="text-rose-"],
    html:not(.dark) body.adm [class*="text-pink-"],
    html:not(.dark) body.adm [class*="text-amber-"],
    html:not(.dark) body.adm [class*="text-yellow-"] { color: var(--adm-orange-text) !important; }
    html:not(.dark) body.adm [class*="bg-red-"],
    html:not(.dark) body.adm [class*="bg-rose-"],
    html:not(.dark) body.adm [class*="bg-pink-"],
    html:not(.dark) body.adm [class*="bg-amber-"],
    html:not(.dark) body.adm [class*="bg-yellow-"] { background-color: #FDEFE3 !important; }
    html:not(.dark) body.adm [class*="border-red-"],
    html:not(.dark) body.adm [class*="border-rose-"],
    html:not(.dark) body.adm [class*="border-amber-"] { border-color: #F7C396 !important; }
    /* bleu / ciel → sarcelle ; violet → vert feuille ; émeraude / vert → vert forêt */
    html:not(.dark) body.adm [class*="text-blue-"],
    html:not(.dark) body.adm [class*="text-sky-"],
    html:not(.dark) body.adm [class*="text-cyan-"],
    html:not(.dark) body.adm [class*="text-indigo-"],
    html:not(.dark) body.adm [class*="text-teal-"] { color: var(--adm-teal) !important; }
    html:not(.dark) body.adm [class*="bg-blue-"],
    html:not(.dark) body.adm [class*="bg-sky-"],
    html:not(.dark) body.adm [class*="bg-cyan-"],
    html:not(.dark) body.adm [class*="bg-indigo-"],
    html:not(.dark) body.adm [class*="bg-teal-"] { background-color: #D8E9E0 !important; }
    html:not(.dark) body.adm [class*="text-violet-"],
    html:not(.dark) body.adm [class*="text-purple-"] { color: var(--adm-leaf) !important; }
    html:not(.dark) body.adm [class*="bg-violet-"],
    html:not(.dark) body.adm [class*="bg-purple-"] { background-color: #D8E9E0 !important; }
    html:not(.dark) body.adm [class*="text-emerald-"],
    html:not(.dark) body.adm main [class*="text-green-"] { color: var(--adm-forest) !important; }
    html:not(.dark) body.adm [class*="bg-emerald-"] { background-color: #D8E9E0 !important; }
    /* pastilles de compteur pleines (ex. « 3 en attente ») : orange plein */
    html:not(.dark) body.adm span[class~="bg-rose-500"],
    html:not(.dark) body.adm span[class~="bg-red-500"],
    html:not(.dark) body.adm span[class~="bg-red-600"],
    html:not(.dark) body.adm span[class~="bg-orange-500"],
    html:not(.dark) body.adm span[class~="bg-emerald-500"] { background-color: var(--adm-orange) !important; color: var(--adm-ink) !important; }

    /* ══ Boutons : tous en orange ═════════════════════════════════════════════════════════════════
       Action principale = orange plein ; action secondaire (anciens boutons gris / teintés) = contour orange,
       ce qui garde la différence entre un onglet actif et les autres. */
    html:not(.dark) body.adm main button[class*="px-"],
    html:not(.dark) body.adm main input[type="submit"],
    html:not(.dark) body.adm main a[class*="px-"][class*="py-"][class*="rounded"][class*="font-"]:not([class*="bg-white"]),
    html:not(.dark) body.adm header.sticky a[class*="px-"][class*="py-"],
    html:not(.dark) body.adm #report-modal button[class*="px-"] {
        background-color: #ffffff !important; background-image: none !important;
        border: 1px solid var(--adm-orange) !important; color: var(--adm-orange-text) !important;
    }
    html:not(.dark) body.adm main button[class*="px-"]:hover,
    html:not(.dark) body.adm main a[class*="px-"][class*="py-"][class*="rounded"][class*="font-"]:not([class*="bg-white"]):hover,
    html:not(.dark) body.adm header.sticky a[class*="px-"][class*="py-"]:hover { background-color: #FDEFE3 !important; }
    html:not(.dark) body.adm main button[class*="px-"]:is([class~="bg-orange-400"], [class~="bg-orange-500"], [class~="bg-orange-600"], [class~="bg-emerald-500"], [class~="bg-emerald-600"], [class~="bg-emerald-700"], [class~="bg-green-600"], [class~="bg-green-700"], [class~="bg-red-500"], [class~="bg-red-600"], [class~="bg-red-700"], [class~="bg-rose-600"], [class~="bg-amber-500"], [class~="bg-blue-600"], [class~="bg-sky-600"], [class~="bg-teal-600"]),
    html:not(.dark) body.adm main input[type="submit"],
    html:not(.dark) body.adm main a[class*="px-"][class*="py-"][class*="rounded"][class*="font-"]:is([class~="bg-orange-400"], [class~="bg-orange-500"], [class~="bg-orange-600"], [class~="bg-emerald-500"], [class~="bg-emerald-600"], [class~="bg-emerald-700"], [class~="bg-green-600"], [class~="bg-green-700"], [class~="bg-red-500"], [class~="bg-red-600"], [class~="bg-red-700"], [class~="bg-rose-600"], [class~="bg-amber-500"], [class~="bg-blue-600"], [class~="bg-sky-600"], [class~="bg-teal-600"], [class*="btn-primary"]),
    html:not(.dark) body.adm #report-modal button[class*="px-"][class*="bg-"] {
        background-color: var(--adm-orange) !important; border-color: var(--adm-orange) !important; color: var(--adm-ink) !important;
    }
    html:not(.dark) body.adm main button[class*="px-"]:is([class~="bg-orange-400"], [class~="bg-orange-500"], [class~="bg-orange-600"], [class~="bg-emerald-500"], [class~="bg-emerald-600"], [class~="bg-emerald-700"], [class~="bg-green-600"], [class~="bg-green-700"], [class~="bg-red-500"], [class~="bg-red-600"], [class~="bg-red-700"], [class~="bg-rose-600"], [class~="bg-amber-500"], [class~="bg-blue-600"], [class~="bg-sky-600"], [class~="bg-teal-600"]):hover,
    html:not(.dark) body.adm main a[class*="px-"][class*="py-"][class*="rounded"][class*="font-"]:is([class~="bg-orange-400"], [class~="bg-orange-500"], [class~="bg-orange-600"], [class~="bg-emerald-500"], [class~="bg-emerald-600"], [class~="bg-emerald-700"], [class~="bg-green-600"], [class~="bg-green-700"], [class~="bg-red-500"], [class~="bg-red-600"], [class~="bg-red-700"], [class~="bg-rose-600"], [class~="bg-amber-500"], [class~="bg-blue-600"], [class~="bg-sky-600"], [class~="bg-teal-600"]):hover { background-color: var(--adm-orange-hover) !important; }
    html:not(.dark) body.adm main button[class*="px-"] i,
    html:not(.dark) body.adm main a[class*="px-"][class*="py-"][class*="rounded"][class*="font-"] i { color: inherit !important; }
    html:not(.dark) body.adm main button:disabled { opacity: .55; cursor: not-allowed; }

    @media (max-width: 640px) {
        /* barre du haut : les actions passent à la ligne au lieu de pousser la page hors de l'écran */
        html:not(.dark) body.adm header.sticky { flex-wrap: wrap; row-gap: .4rem; }
        html:not(.dark) body.adm header.sticky > div:first-child { min-width: 0; flex: 1 1 auto; }
        html:not(.dark) body.adm header.sticky > div:last-child { margin-left: auto; flex-wrap: wrap; justify-content: flex-end; gap: .5rem; min-width: 0; }
        html:not(.dark) body.adm header.sticky h1 { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 62vw; }
    }
    /* ── Mobile : plus de débordement horizontal de la page ─────────────────────────────────────── */
    html:not(.dark) body.adm > div.flex-1 { min-width: 0; }
    @media (max-width: 1023px) {
        html:not(.dark) body.adm main { overflow-x: hidden; }
        /* seulement les tableaux sans conteneur défilant : sinon l'en-tête ne couvre plus toute la largeur */
        html:not(.dark) body.adm main :not(.overflow-x-auto) > table { display: block; max-width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        html:not(.dark) body.adm main pre { max-width: 100%; overflow-x: auto; }
    }
</style>
