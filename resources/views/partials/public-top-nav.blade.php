@php
    $topContact = $siteBrand['contact'] ?? [];
    $topPhoneDisplay = !empty($topContact['phone_1']) ? $topContact['phone_1'] : '+225 27 22 48 36 90';
    $topPhoneHref = 'tel:'.preg_replace('/[^\d+]/', '', $topPhoneDisplay);
    $publicInfoPages = \Illuminate\Support\Facades\Schema::hasTable('information_pages')
        ? \App\Models\InformationPage::query()->orderBy('sort_order')->orderBy('id')->get()
        : collect();
    $publicInfoGuide = $publicInfoPages->firstWhere('slug', 'user-guide');
    $publicInfoFaq = $publicInfoPages->firstWhere('slug', 'faq');
    $publicNavItems = [
        ['label' => __('Accueil'),               'icon' => 'fas fa-house',            'href' => route('home'),               'active' => request()->routeIs('home')],
        ['label' => __('Tourisme'),              'icon' => 'fas fa-umbrella-beach',   'href' => route('tourist.cities'),     'active' => request()->routeIs('tourist.*')],
        [
            'label' => __('Résidences & Hôtels'), 'shortLabel' => __('Hôtels & Résidences'), 'icon' => 'fas fa-bed',
            'href' => route('accommodations.index'), 'dropdown' => true,
            'active' => request()->routeIs('accommodations.*') || (request()->routeIs('providers.index') && request()->get('categorie')),
        ],
        ['label' => __('Cultures & Traditions'), 'shortLabel' => __('Cultures'), 'icon' => 'fas fa-masks-theater',    'href' => route('cultural.peoples'),   'active' => request()->routeIs('cultural.*')],
        // Retiré de la barre du haut (reste dans le menu mobile).
        ['headerHidden' => true, 'label' => __('Annuaire'),              'icon' => 'fas fa-address-book',     'href' => route('providers.index'),    'active' => request()->routeIs('providers.*') && ! request()->get('categorie')],
        ['label' => __('Magazine'),              'icon' => 'fas fa-newspaper',        'href' => route('articles.index'),     'active' => request()->routeIs('articles.*') || request()->routeIs('discoveries.*')],
        ['label' => __('Événements'),            'icon' => 'fas fa-calendar-days',    'href' => route('events.index'),       'active' => request()->routeIs('events.*')],
    ];
    $publicNavItems[] = ['wideOnly' => true, 'label' => __('Autour de moi'), 'icon' => 'fas fa-location-crosshairs', 'href' => route('nearby.index'), 'active' => request()->routeIs('nearby.*')];
    // Offres groupées : lien affiché seulement quand au moins une offre est publiée.
    if (\Illuminate\Support\Facades\Schema::hasTable('bundles') && \App\Models\Bundle::active()->exists()) {
        $publicNavItems[] = ['wideOnly' => true, 'label' => __('Offres groupées'), 'icon' => 'fas fa-layer-group', 'href' => route('bundles.index'), 'active' => request()->routeIs('bundles.*')];
    }
    if (config('ai.enabled', true)) {
        $publicNavItems[] = ['wideOnly' => true, 'label' => __('Mon séjour'), 'icon' => 'fas fa-route', 'href' => route('trip.create'), 'active' => request()->routeIs('trip.*') || request()->routeIs('ai.*')];
    }
    // Secteurs d'activité racine (configurés depuis /admin/prestataires/categories) — alimente
    // dynamiquement le menu déroulant "Hôtels & Résidences" ; toute catégorie ajoutée/désactivée
    // depuis le back-office se répercute automatiquement ici, sans toucher au code.
    // Icône et accroche par secteur dans les menus (présentation seulement ; secteur inconnu : icône générique).
    $sectorMeta = [
        'restaurants' => ['chef-hat', __('Maquis, tables et saveurs')],
        'sites-touristiques' => ['map-point', __('Lieux à visiter et à comprendre')],
        'agences-voyages' => ['plane', __('Circuits et séjours organisés')],
        'loisirs-culture' => ['confetti', __('Activités, spectacles, détente')],
        'transports' => ['bus', __('Transferts, chauffeurs, location')],
        'art-creations' => ['palette', __('Œuvres d\'artistes ivoiriens')],
    ];
    $providerSectors = \App\Models\ProviderCategory::where('is_active', true)
        ->whereNull('parent_id')
        ->orderBy('sort_order')->orderBy('name_fr')
        ->get();
    // Chaque secteur ayant sa propre fiche riche (mirroir du système Hôtels) est redirigé vers sa
    // page dédiée plutôt que l'annuaire générique filtré ; les secteurs pas encore migrés vers ce
    // patron retombent sur l'annuaire (?categorie=...).
    $sectorUrl = function (\App\Models\ProviderCategory $sector): string {
        return match ($sector->slug) {
            'loisirs-culture' => route('leisure.index'),
            'art-creations' => route('art.index'),
            'restaurants' => route('restaurant.index'),
            'sites-touristiques' => route('tourist-experience.index'),
            'agences-voyages' => route('travel-agency.index'),
            'transports' => route('transport-company.index'),
            default => route('providers.index', ['categorie' => $sector->slug]),
        };
    };
    $publicGalleryActive = request()->routeIs('gallery.public');
    $publicMediaActive = $publicGalleryActive || request()->routeIs('gallery.public.*', 'videos.*', 'comics.*');
    $headerImage = $headerImage ?? (\Illuminate\Support\Facades\Schema::hasTable('header_images')
        ? \App\Models\HeaderImage::query()->find(1)
        : null);
    $activeFlashInfos = \Illuminate\Support\Facades\Schema::hasTable('flash_infos')
        ? \App\Models\FlashInfo::currentlyVisible()->orderBy('display_order')->orderByDesc('id')->get()
        : collect();
@endphp

<style>
    .font-plus { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }

    /* ── Top bar ─────────────────────────────────────────── */
    .public-topbar {
        position: relative;
        isolation: isolate;
        /* Orange terre cuite : avec l'en-tête blanc et le bandeau vert, le haut de page reprend les couleurs du drapeau. */
        background-color: #b9500a;
        /* Mêmes frises que le Flash info (losanges en haut, chevrons en bas), couleurs adaptées au fond orange. */
        background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='14' viewBox='0 0 56 14'><path d='M14 2 l5 5 l-5 5 l-5 -5 z' fill='none' stroke='%23fff4e6' stroke-opacity='.26' stroke-width='1.2'/><path d='M14 5 l2 2 l-2 2 l-2 -2 z' fill='%23f6c38f' fill-opacity='.4'/><circle cx='42' cy='7' r='1.3' fill='%23fff4e6' fill-opacity='.3'/></svg>"), url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='12' viewBox='0 0 56 12'><path d='M30 9 l5 -5 l5 5 l5 -5 l5 5' fill='none' stroke='%23123a25' stroke-opacity='.3' stroke-width='1.3'/><circle cx='8' cy='6' r='1.2' fill='%23fff4e6' fill-opacity='.28'/></svg>");
        background-size: 56px 14px, 56px 12px;
        background-position: 0 3px, 28px calc(100% - 5px);
        background-repeat: repeat-x, repeat-x;
        border-bottom: 1px solid rgba(255,255,255,0.14);
    }
    .public-topbar > * { position: relative; z-index: 1; }
    .public-slogan-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
        padding: 0.3rem 0;
    }
    .public-pulse-dot {
        width: 7px; height: 7px; border-radius: 9999px;
        background: #ffffff;
    }
    @keyframes publicPulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50% { opacity: 0.65; transform: scale(0.92); }
    }
    /* Couleurs fixées en CSS pur (et non via classes Tailwind text-green-*) :
       le pont de thème clair (theme-light-bridge) réécrit ces classes en vert
       très foncé pour les usages sur fond clair, ce qui les rendait quasi
       invisibles sur ce bandeau à fond vert foncé. */
    .public-slogan-text { color: #fff4e6; }
    /* Liens de la barre orange : pastilles en aplat (fond plus foncé que la barre, fin contour clair) ;
       au survol, la pastille passe en crème et le texte en orange de la charte. */
    .public-topbar-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        height: 32px;
        padding: 0 0.85rem;
        border-radius: 999px;
        border: 1px solid rgba(255,244,230,0.28);
        background: #9a4308;
        font-size: 12px;
        font-weight: 600;
        color: #fff4e6;
        transition: color .15s ease, background-color .15s ease, border-color .15s ease;
    }
    .public-topbar-action:hover,
    .public-topbar-action:focus-visible {
        color: #9a4308;
        background: #fff4e6;
        border-color: #fff4e6;
        outline: none;
    }
    .public-topbar-action i { font-size: 11px; color: #f6c38f; transition: color .15s ease; }
    .public-topbar-action:hover i,
    .public-topbar-action:focus-visible i { color: #b9500a; }

    /* ── Flash info ticker ───────────────────────────────── */
    /* Bandeau d'annonces sobre : fond encre plat, pastille orange, texte lisible.
       Les calques décoratifs (.flash-ticker-sheen / -accent) restent dans le markup mais sont neutres. */
    /* Frises tissées (losanges en haut, chevrons en bas, d'après les pagnes akan et baoulés) en couleurs du site ; la ligne de texte reste dégagée. */
    .flash-ticker-bg { background-color: #123a25; background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='14' viewBox='0 0 56 14'><path d='M14 2 l5 5 l-5 5 l-5 -5 z' fill='none' stroke='%23e2742a' stroke-opacity='.28' stroke-width='1.2'/><path d='M14 5 l2 2 l-2 2 l-2 -2 z' fill='%23e0a12e' fill-opacity='.34'/><circle cx='42' cy='7' r='1.3' fill='%23e0a12e' fill-opacity='.3'/></svg>"), url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='12' viewBox='0 0 56 12'><path d='M30 9 l5 -5 l5 5 l5 -5 l5 5' fill='none' stroke='%23f3ead8' stroke-opacity='.24' stroke-width='1.2'/><circle cx='8' cy='6' r='1.2' fill='%23e0a12e' fill-opacity='.28'/></svg>"); background-size: 56px 14px, 56px 12px; background-position: 0 2px, 28px calc(100% - 5px); background-repeat: repeat-x, repeat-x; }
    .flash-ticker-sheen,
    .flash-ticker-accent { display: none; }
    /* Lisière kita (3 px) sous le bandeau */
    .flash-ticker-accent-bottom { height: 3px !important; background-color: #b9500a; background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='48' height='3'><rect width='16' height='3' fill='%23b9500a'/><rect x='16' width='6' height='3' fill='%23e0a12e'/><rect x='22' width='14' height='3' fill='%231b6b3a'/><rect x='36' width='4' height='3' fill='%23f3ead8'/><rect x='40' width='8' height='3' fill='%23b9500a'/></svg>"); background-size: 48px 3px; }
    .flash-ticker > .relative { min-height: 0 !important; padding-top: .55rem !important; padding-bottom: .55rem !important; }

    .flash-ticker-badge {
        position: relative;
        display: inline-flex; align-items: center; gap: 10px;
        padding: 4px 12px 4px 4px;
        border-radius: 999px;
        background: rgba(255,255,255,.06);
    }
    .flash-ticker-badge-orb {
        position: relative;
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px;
        background: #b9500a;
        flex-shrink: 0;
    }
    .flash-ticker-badge-ring { display: none; }
    .flash-ticker-badge-icon {
        position: relative;
        font-size: 12px;
        color: #ffffff;
    }
    .flash-ticker-badge-copy {
        position: relative;
        display: flex; align-items: baseline; gap: 6px;
        font-size: 12px; font-weight: 700; letter-spacing: .12em;
        white-space: nowrap;
    }
    .flash-ticker-badge-live { color: #fdba74; text-transform: uppercase; }
    .flash-ticker-badge-title { color: rgba(255,255,255,.7); text-transform: uppercase; font-weight: 600; }

    .flash-ticker-track {
        -webkit-mask-image: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65));
                mask-image: linear-gradient(rgba(0, 0, 0, 0.65), rgba(0, 0, 0, 0.65));
    }

    @keyframes flash-ticker-marquee {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }
    .flash-ticker-scroll {
        animation-name: flash-ticker-marquee;
        animation-timing-function: linear;
        animation-iteration-count: infinite;
    }
    .flash-ticker-track:hover .flash-ticker-scroll,
    .flash-ticker-track:focus-within .flash-ticker-scroll { animation-play-state: paused; }

    .flash-ticker-item {
        display: inline-flex; align-items: center;
        padding: 4px 14px;
        border-radius: 6px;
        font-size: 14px; font-weight: 500;
        color: rgba(255,255,255,.88);
        text-decoration: none;
        transition: color .15s ease, background-color .15s ease;
    }
    a.flash-ticker-item:hover {
        color: #fff;
        background: rgba(255,255,255,.07);
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .flash-ticker-marker {
        width: 6px; height: 6px; border-radius: 999px;
        margin-right: 10px; flex-shrink: 0;
        background: #94a3b8;
    }
    .flash-ticker-item--info    .flash-ticker-marker { background: #7cc4ee; }
    .flash-ticker-item--success .flash-ticker-marker { background: #6fcf8f; }
    .flash-ticker-item--warning .flash-ticker-marker { background: #f2c14e; }
    .flash-ticker-item--urgent  .flash-ticker-marker { background: #f2797b; }
    /* Séparateur : petit losange ocre, en écho à la trame */
    .flash-ticker-sep {
        width: 6px; height: 6px;
        margin: 0 14px;
        flex-shrink: 0;
        display: inline-block;
        border-radius: 1px;
        transform: rotate(45deg);
        background: #e0a12e;
        opacity: .85;
    }

    .flash-ticker-close {
        width: 30px; height: 30px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px;
        color: rgba(255,255,255,.55);
        transition: background-color .15s ease, color .15s ease;
        font-size: 14px;
    }
    .flash-ticker-close:hover {
        background: rgba(255,255,255,.08);
        color: #ffffff;
    }

    @media (prefers-reduced-motion: reduce) {
        .flash-ticker-scroll { animation: none; }
    }

    @media (max-width: 639px) {
        .flash-ticker-badge { padding: 3px; }
        .flash-ticker-badge-copy { display: none; }
        .flash-ticker-badge-orb { width: 26px; height: 26px; }
        .flash-ticker-item { font-size: 13px; padding: 4px 10px; }
    }

    /* ── Header ──────────────────────────────────────────── */
    /* En-tête : frise en filigrane des armoiries ivoiriennes (tête d'éléphant, soleil levant, palmier) aux couleurs
       du drapeau, et liseré tricolore en haut. Une image d'en-tête définie en administration passe par-dessus. */
    .public-header {
        background-color: rgba(255,255,255,.97);
        background-image: var(--header-bg-image, none), url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='84' viewBox='0 0 240 84'><g fill='none' stroke='%23f77f00' stroke-opacity='.22' stroke-width='1.7' stroke-linecap='round' stroke-linejoin='round'><path d='M40 30 q0 -14 14 -14 q14 0 14 14'/><path d='M40 30 q-22 -10 -22 10 q0 16 18 14'/><path d='M68 30 q22 -10 22 10 q0 16 -18 14'/><path d='M48 40 q0 16 4 24 q2 6 -4 8'/><path d='M60 40 q0 18 -4 28'/><path d='M46 46 q-8 6 -6 14'/><path d='M62 46 q8 6 6 14'/></g><g fill='%23f77f00' fill-opacity='.26'><circle cx='48' cy='31' r='1.4'/><circle cx='60' cy='31' r='1.4'/></g><g fill='none' stroke='%23d4951c' stroke-opacity='.26' stroke-width='1.7' stroke-linecap='round'><path d='M106 60 a16 16 0 0 1 32 0'/><path d='M98 60 h48'/><path d='M122 38 v-8 M108 44 l-6 -6 M136 44 l6 -6 M100 54 l-8 -2 M144 54 l8 -2'/></g><g fill='none' stroke='%23009e60' stroke-opacity='.2' stroke-width='1.7' stroke-linecap='round'><path d='M196 74 q-3 -26 2 -46'/><path d='M198 28 q-16 -6 -26 4'/><path d='M198 28 q16 -6 26 4'/><path d='M198 28 q-10 -14 -22 -12'/><path d='M198 28 q10 -14 22 -12'/><path d='M198 28 v-10'/></g></svg>");
        background-size: cover, 240px 84px;
        background-position: center, 0 50%;
        background-repeat: no-repeat, repeat-x;
        border-bottom: 1px solid #e8e1d3;
        transition: box-shadow .2s ease;
    }
    /* Menu et marque posés sur des capsules blanches unies : textes nets, la frise reste visible autour. */
    .public-nav-rail { justify-self: center; width: max-content; max-width: 100%; padding: 5px 8px; border-radius: 999px; background: #fff; border: 1px solid #ece4d4; }
    .public-brand-rail { padding: 4px 16px 4px 4px; border-radius: 22px; background: #fff; border: 1px solid #ece4d4; }
    @media (max-width: 639px) { .public-brand-rail { padding: 3px 10px 3px 3px; border-radius: 18px; } }
    /* Boutons ronds de l'en-tête (recherche, compte, menu) : fond blanc uni au-dessus de la frise ; les boutons colorés gardent le leur. */
    #main-header > div [class*="bg-white/"], #main-header > div button[class*="rounded"]:not([class*="bg-"]), #main-header .btn-ghost-header, #nav-user-dropdown-btn { background-color: #fff !important; }
    .public-header::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px; pointer-events: none;
        background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='30' height='3'><rect width='12' height='3' fill='%23f77f00'/><rect x='12' width='6' height='3' fill='%23efe6d4'/><rect x='18' width='12' height='3' fill='%23009e60'/></svg>"); background-size: 30px 3px; background-repeat: repeat-x; }
    .public-header.header-scrolled {
        box-shadow: 0 6px 18px rgba(29,26,22,0.07);
    }
    .public-nav-pill {
        position: relative;
        padding: 0.5rem 0.55rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 500;
        letter-spacing: 0.01em;
        color: #4a443a;
        transition: color .15s ease, background-color .15s ease;
    }
    @media (min-width: 1536px) {
        .public-nav-pill { padding: 0.5rem 0.7rem; }
    }
    /* Menu desktop en texte seul : les pictogrammes restent dans le menu mobile. */
    .public-nav-pill > i:first-child { display: none; }
    .public-nav-pill:hover {
        color: #1d1a16;
        background: #f3eee4;
    }
    /* Page courante : texte vert forêt + filet or sous le libellé. */
    .public-nav-pill-glow::after {
        content: '';
        position: absolute;
        left: 0.65rem;
        right: 0.65rem;
        bottom: 1px;
        height: 2px;
        border-radius: 2px;
        background: #b8892b;
        transform: scaleX(0);
        transition: transform .2s ease;
    }
    .public-nav-pill-glow.is-active::after { transform: scaleX(1); }
    .public-nav-pill.is-active {
        color: #14532d;
        font-weight: 600;
        background: transparent;
    }
    /* Panneau du mega-menu "Hôtels & Résidences" : fond toujours sombre,
       non affecté par le pont de theme clair (qui réécrit text-gray-200 en
       texte sombre, ce qui le rendait invisible sur ce fond resté sombre). */
    #hotels-nav-panel .text-gray-200 { color: #e5e7eb !important; }
    #hotels-nav-panel a:hover { color: #ffffff !important; background-color: rgba(255,255,255,0.05) !important; }
    #hotels-nav-panel .border-white\/10 { border-color: rgba(255,255,255,0.1) !important; }
    .logo-ring {
        position: relative;
        border-radius: 12px;
        padding: 1px;
        background: #e8e1d3;
    }
    .logo-ring-inner {
        border-radius: 11px;
        overflow: hidden;
        background: #ffffff;
    }
    /* Nom du site : vert forêt plat (le dégradé vert clair d'origine manquait de contraste). */
    #main-header .group p.bg-clip-text {
        background: none !important;
        color: #14532d !important;
        -webkit-text-fill-color: #14532d;
    }
    .lang-switch-ultra {
        display: inline-flex;
        padding: 2px;
        border-radius: 8px;
        background: #f3eee4;
        border: 1px solid #e8e1d3;
    }
    .lang-switch-ultra a {
        border-radius: 6px;
        padding: 0.3rem 0.6rem;
        font-size: 11px;
        font-weight: 600;
        letter-spacing: 0.06em;
        transition: background-color .15s ease, color .15s ease;
    }
    .btn-ghost-header {
        border-radius: 8px;
        border: 1px solid #e8e1d3;
        background: #ffffff;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }
    .btn-ghost-header:hover {
        border-color: #d8cdb8;
        background: #faf8f3;
    }
    /* Bouton d'action du header : orange d'action plein. */
    .btn-gold-header {
        border-radius: 8px;
        background: #b9500a;
        color: #ffffff !important;
        transition: background-color .15s ease;
    }
    .btn-gold-header:hover { background: #9a4308; }
    .btn-gold-header i, .btn-gold-header span { color: #ffffff !important; }
    #public-mobile-menu.mobile-menu-ultra {
        background: #ffffff;
        border-top: 1px solid #e8e1d3;
        box-shadow: 0 16px 32px rgba(29,26,22,0.10);
    }
    #public-mobile-menu.mobile-menu-ultra a {
        border-radius: 10px;
        border: 1px solid transparent;
    }
    #public-mobile-menu.mobile-menu-ultra a:hover { background: #f3eee4; }
    #public-mobile-menu.mobile-menu-ultra a.is-active {
        background: #eef6f0;
        border-color: #d9ecdf;
        color: #14532d;
        font-weight: 600;
    }

    /* ── Dropdown "Mon espace" ───────────────────────────── */
    :root {
        --dd-bg: rgba(8,10,14,0.96);
        --dd-border: rgba(255,255,255,0.07);
        --dd-shadow: 0 32px 64px -8px rgba(0,0,0,0.9), 0 0 0 1px rgba(255,255,255,0.03), inset 0 1px 0 rgba(255,255,255,0.06);
        --dd-divider: rgba(255,255,255,0.06);
        --dd-head-gradient: rgba(242, 121, 15,0.07);
    }
    html:not(.dark) {
        --dd-bg: rgba(255,253,248,0.99);
        --dd-border: rgba(0,0,0,0.09);
        --dd-shadow: 0 24px 48px -8px rgba(0,0,0,0.12), 0 0 0 1px rgba(0,0,0,0.05), inset 0 1px 0 rgba(255,255,255,0.9);
        --dd-divider: rgba(0,0,0,0.07);
        --dd-head-gradient: rgba(242, 121, 15,0.05);
    }
    .nav-dd-lnk {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.45rem 0.625rem; border-radius: 0.625rem;
        font-size: 0.8125rem; font-weight: 500; color: #44413a;
        text-decoration: none; white-space: nowrap;
        background: none; border: none; cursor: pointer;
        transition: background .15s ease, color .15s ease;
        width: 100%; text-align: left;
    }
    .nav-dd-lnk:hover { background: rgba(0,0,0,0.04); color: #1c1915; }
    .nav-dd-lnk:hover .nav-dd-icon { background: rgba(242, 121, 15,0.14); color: #9f4709; }
    .nav-dd-icon {
        width: 1.875rem; height: 1.875rem; border-radius: 0.5rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(0,0,0,0.05); color: #544f47; font-size: 0.7rem;
        transition: background .15s ease, color .15s ease;
    }
    .nav-dd-danger { color: #dc2626; }
    .nav-dd-danger .nav-dd-icon { color: #dc2626; background: rgba(239,68,68,0.08); }
    .nav-dd-danger:hover { background: rgba(239,68,68,0.06); color: #b91c1c; }
</style>

{{-- ══════════════════════════════════════════════════════════
     TOP BAR
══════════════════════════════════════════════════════════ --}}
<div id="home-top-bar" class="public-topbar font-plus text-gray-300 hidden md:block">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        @if(session('contact_success') || session('contact_error'))
            <div class="py-2.5 text-center text-[11px] font-semibold tracking-wide border-b border-white/[0.06] {{ session('contact_success') ? 'text-emerald-400/95' : 'text-red-400/95' }}">
                {{ session('contact_success') ?? session('contact_error') }}
            </div>
        @endif
        <div class="py-3.5 flex flex-wrap items-center justify-between gap-y-3 gap-x-5 min-h-[3.25rem]">
            <div class="public-slogan-pill">
                <span class="public-pulse-dot shrink-0" aria-hidden="true"></span>
                <span class="public-slogan-text text-[11px] sm:text-xs font-semibold tracking-[0.14em] uppercase">
                    {{ $siteBrand['site_slogan'] ?: __('Magazine Culturel & Touristique Premium') }}
                </span>
            </div>
            <div class="flex flex-wrap items-center justify-end gap-2.5 sm:gap-3">
                <a href="{{ $topPhoneHref }}" class="public-topbar-action">
                    <i class="fas fa-phone-volume"></i>
                    <span>{{ $topPhoneDisplay }}</span>
                </a>
                <a href="{{ route('prestations.public') }}" class="public-topbar-action">
                    <i class="fas fa-concierge-bell"></i>
                    {{ __('Nos prestations') }}
                </a>
                <button type="button" onclick="openContactModal()" class="public-topbar-action cursor-pointer">
                    <i class="fas fa-message"></i>
                    {{ __('Contact') }}
                </button>
                @if($publicInfoGuide)
                <a href="{{ route('information.show', $publicInfoGuide) }}" class="public-topbar-action">
                    <i class="fas fa-book-open"></i>
                    {{ __('Guide') }}
                </a>
                @endif
                @if($publicInfoFaq)
                <a href="{{ route('information.show', $publicInfoFaq) }}" class="public-topbar-action">
                    <i class="fas fa-circle-question"></i>
                    {{ __('FAQ') }}
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@if(session('contact_success') || session('contact_error'))
    <div id="home-contact-flash-mobile" class="md:hidden bg-white border-b border-black/5 text-center text-[11px] py-2 px-4 font-medium {{ session('contact_success') ? 'text-emerald-600' : 'text-red-600' }}">
        {{ session('contact_success') ?? session('contact_error') }}
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════
     HEADER
══════════════════════════════════════════════════════════ --}}
<header id="main-header" class="public-header font-plus sticky top-0 z-40"
    @if($headerImage && $headerImage->isVisible())
        style="--header-bg-image: url('{{ $headerImage->image_url }}');"
    @endif
>
    <div class="max-w-[96rem] mx-auto px-4 sm:px-6 lg:px-8 h-[4.25rem] md:h-[5.25rem] grid grid-cols-[auto_1fr_auto] items-center gap-4 md:gap-6">
        <a href="{{ route('home') }}" class="public-brand-rail flex items-center gap-3 sm:gap-3.5 shrink-0 group">
            @if(!empty($siteBrand['logo_url']))
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-12 h-12 md:w-[4rem] md:h-[4rem] flex items-center justify-center">
                        <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain p-0.5">
                    </div>
                </div>
            @else
                <div class="logo-ring shrink-0">
                    <div class="logo-ring-inner w-12 h-12 md:w-[4rem] md:h-[4rem] flex items-center justify-center bg-linear-to-br from-green-400 to-green-600">
                        <i class="fas fa-gem text-dark-900 text-sm md:text-base drop-shadow-sm"></i>
                    </div>
                </div>
            @endif
            <div class="hidden sm:block min-w-0">
                <p class="text-transparent bg-clip-text bg-linear-to-r from-green-200 via-green-400 to-green-600 font-serif font-bold text-base md:text-lg leading-tight tracking-tight truncate group-hover:from-green-100 group-hover:to-green-300 transition-all duration-300">
                    {{ $siteBrand['site_name'] }}
                </p>
                <p class="text-gray-500 group-hover:text-gray-400 text-[9px] md:text-[10px] tracking-[0.22em] uppercase truncate font-plus font-medium mt-0.5 transition-colors">
                    {{ $siteBrand['site_slogan'] ?: __('Magazine Premium') }}
                </p>
            </div>
        </a>

        <nav class="public-nav-rail hidden min-[1400px]:flex items-center justify-center gap-0.5 min-w-0 overflow-x-hidden overflow-y-visible">
            @foreach($publicNavItems as $item)
                @continue(! empty($item['headerHidden']))
                @if(!empty($item['dropdown']))
                <div class="relative" id="hotels-nav-dropdown">
                    <a href="{{ $item['href'] }}"
                       class="public-nav-pill public-nav-pill-glow whitespace-nowrap inline-flex items-center gap-1 {{ $item['active'] ? 'is-active' : '' }} {{ ! empty($item['wideOnly']) ? 'max-[1599px]:!hidden' : '' }}">
                        <i class="{{ $item['icon'] }} text-[10px] opacity-80"></i>
                        {{ $item['shortLabel'] ?? $item['label'] }}
                        <i class="fas fa-chevron-down text-[7px] opacity-60 shrink-0 transition-transform duration-200" id="hotels-nav-chevron"></i>
                    </a>
                    <div class="hidden fixed z-[9999]" id="hotels-nav-panel">
                        <div class="mdd is-wide" role="menu" aria-label="{{ $item['label'] }}">
                            <a href="{{ route('accommodations.index') }}" class="mdd-item {{ request()->routeIs('accommodations.*') ? 'is-on' : '' }}" role="menuitem">
                                <span class="mdd-ico"><x-icon name="bed" /></span>
                                <span class="mdd-txt"><b>{{ __('Hôtels') }}</b><small>{{ __('Hôtels, résidences et lodges') }}</small></span>
                            </a>
                            @foreach($providerSectors as $sector)
                                @continue($sector->slug === 'hotels')
                                @php [$secIcon, $secText] = $sectorMeta[$sector->slug] ?? ['widget', __('Adresses vérifiées')]; @endphp
                                <a href="{{ $sectorUrl($sector) }}" class="mdd-item" role="menuitem">
                                    <span class="mdd-ico"><x-icon :name="$secIcon" /></span>
                                    <span class="mdd-txt"><b>{{ $sector->localized('name') }}</b><small>{{ $secText }}</small></span>
                                </a>
                            @endforeach
                            <p class="mdd-foot"><span>{{ __('Toutes nos adresses sont vérifiées.') }}</span><a href="{{ route('providers.index') }}">{{ __('Voir l\'annuaire') }} →</a></p>
                        </div>
                    </div>
                </div>
                <script>
                (function () {
                    var wrap = document.getElementById('hotels-nav-dropdown');
                    if (!wrap || wrap.dataset.wired) return;
                    wrap.dataset.wired = '1';
                    var panel = document.getElementById('hotels-nav-panel');
                    var chevron = document.getElementById('hotels-nav-chevron');
                    // Sort le panneau de la hiérarchie du menu (position:fixed calculée en JS) pour
                    // échapper à tout ancêtre qui le tronquerait/masquerait (overflow, stacking context...).
                    document.body.appendChild(panel);
                    var closeTimer = null;
                    function position() {
                        var rect = wrap.getBoundingClientRect();
                        // Panneau large : il reste dans l'écran (mesuré une fois affiché).
                        panel.style.left = Math.max(8, Math.min(rect.left, window.innerWidth - panel.offsetWidth - 16)) + 'px';
                        panel.style.top = (rect.bottom + 8) + 'px';
                    }
                    function open() {
                        clearTimeout(closeTimer);
                        panel.classList.remove('hidden');
                        position();
                        chevron.classList.add('rotate-180');
                    }
                    function scheduleClose() {
                        clearTimeout(closeTimer);
                        closeTimer = setTimeout(function () {
                            panel.classList.add('hidden');
                            chevron.classList.remove('rotate-180');
                        }, 200);
                    }
                    wrap.addEventListener('mouseenter', open);
                    wrap.addEventListener('mouseleave', scheduleClose);
                    wrap.addEventListener('focusin', open);
                    wrap.addEventListener('focusout', scheduleClose);
                    panel.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
                    panel.addEventListener('mouseleave', scheduleClose);
                    window.addEventListener('scroll', function () {
                        if (!panel.classList.contains('hidden')) panel.classList.add('hidden');
                    }, true);
                    window.addEventListener('resize', function () {
                        if (!panel.classList.contains('hidden')) position();
                    });
                })();
                </script>
                @else
                <a href="{{ $item['href'] }}"
                   class="public-nav-pill public-nav-pill-glow whitespace-nowrap inline-flex items-center gap-1 {{ $item['active'] ? 'is-active' : '' }} {{ ! empty($item['wideOnly']) ? 'max-[1599px]:!hidden' : '' }}">
                    <i class="{{ $item['icon'] }} text-[10px] opacity-80"></i>
                    {{ $item['shortLabel'] ?? $item['label'] }}
                </a>
                @endif
            @endforeach
            {{-- Médiathèque : Galerie photo + Vidéos (menu déroulant, même mécanique que « Hôtels & Résidences »). --}}
            <div class="relative" id="media-nav-dropdown">
                <a href="{{ route('gallery.public') }}" aria-haspopup="true"
                   class="public-nav-pill public-nav-pill-glow whitespace-nowrap inline-flex items-center gap-1 {{ $publicMediaActive ? 'is-active' : '' }}">
                    <i class="fas fa-photo-film text-[10px] opacity-80"></i>
                    {{ __('Médiathèque') }}
                    <i class="fas fa-chevron-down text-[7px] opacity-60 shrink-0 transition-transform duration-200" id="media-nav-chevron"></i>
                </a>
                {{-- Styles propres au panneau (classes « mdd- ») : le thème clair réécrit les couleurs Tailwind
                     (text-gray-200, text-white…), ce qui rendait les libellés illisibles sur fond vert. --}}
                <style>
                    .mdd { width: 300px; padding: 8px; border-radius: 18px; background: #fff; border: 1px solid #e6dccb; box-shadow: 0 24px 50px -28px rgba(29,26,22,.45); font-family: 'Inter', sans-serif; }
                    .mdd-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; text-decoration: none; transition: background .15s ease; }
                    .mdd-item:hover, .mdd-item:focus-visible { background: #fdf4ec; outline: none; }
                    .mdd-item.is-on { background: #f6f3ec; }
                    .mdd-ico { flex: none; width: 42px; height: 42px; border-radius: 12px; background: #fdf4ec; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
                    .mdd-item:hover .mdd-ico { background: #b9500a; color: #fff; }
                    .mdd-txt b { display: block; font-size: 14.5px; font-weight: 700; color: #1d1a16; line-height: 1.3; }
                    .mdd-txt small { display: block; margin-top: 2px; font-size: 12.5px; color: #6b6458; line-height: 1.35; }
                    .mdd-item:hover .mdd-txt b { color: #9a4308; }
                    .mdd.is-wide { width: 620px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2px; }
                    .mdd-foot { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 6px 4px 2px; padding: 10px 8px 4px; border-top: 1px solid #f0eadf; font-size: 12.5px; color: #6b6458; }
                    .mdd-foot a { font-weight: 700; color: #9a4308; text-decoration: none; }
                    .mdd-foot a:hover { text-decoration: underline; text-underline-offset: 3px; }
                    .mdd-new { display: inline-block; margin-left: 4px; padding: 1px 7px; border-radius: 999px; background: #b9500a; color: #fff; font-style: normal; font-size: 10.5px; font-weight: 800; vertical-align: 2px; }
                </style>
                <div class="hidden fixed z-[9999]" id="media-nav-panel">
                    <div class="mdd" role="menu" aria-label="{{ __('Médiathèque') }}">
                        <a href="{{ route('gallery.public') }}" class="mdd-item {{ request()->routeIs('gallery.public*') ? 'is-on' : '' }}" role="menuitem">
                            <span class="mdd-ico"><x-icon name="camera" /></span>
                            <span class="mdd-txt"><b>{{ __('Galerie photo') }}</b><small>{{ __('Les plus belles images du pays') }}</small></span>
                        </a>
                        <a href="{{ route('videos.index') }}" class="mdd-item {{ request()->routeIs('videos.*') ? 'is-on' : '' }}" role="menuitem">
                            <span class="mdd-ico"><x-icon name="clapperboard-play" /></span>
                            <span class="mdd-txt"><b>{{ __('Vidéos') }}</b><small>{{ __('À regarder, puis à réserver') }}</small></span>
                        </a>
                        <a href="{{ route('comics.index') }}" class="mdd-item {{ request()->routeIs('comics.*') ? 'is-on' : '' }}" role="menuitem">
                            <span class="mdd-ico"><x-icon name="book" /></span>
                            <span class="mdd-txt"><b>{{ __('Les Astuces de Mian') }} <em class="mdd-new">{{ __('Nouveau') }}</em></b><small>{{ __('Bandes dessinées à lire gratuitement') }}</small></span>
                        </a>
                    </div>
                </div>
            </div>
            <script>
            (function () {
                var wrap = document.getElementById('media-nav-dropdown');
                if (!wrap || wrap.dataset.wired) return;
                wrap.dataset.wired = '1';
                var panel = document.getElementById('media-nav-panel'), chevron = document.getElementById('media-nav-chevron'), t = null;
                document.body.appendChild(panel);
                function position() { var r = wrap.getBoundingClientRect(); panel.style.left = Math.min(r.left, window.innerWidth - 316) + 'px'; panel.style.top = (r.bottom + 8) + 'px'; }
                function open() { clearTimeout(t); position(); panel.classList.remove('hidden'); chevron.classList.add('rotate-180'); }
                function later() { clearTimeout(t); t = setTimeout(function () { panel.classList.add('hidden'); chevron.classList.remove('rotate-180'); }, 200); }
                wrap.addEventListener('mouseenter', open); wrap.addEventListener('mouseleave', later);
                wrap.addEventListener('focusin', open); wrap.addEventListener('focusout', later);
                panel.addEventListener('mouseenter', function () { clearTimeout(t); }); panel.addEventListener('mouseleave', later);
                panel.addEventListener('focusin', function () { clearTimeout(t); }); panel.addEventListener('focusout', later);
                window.addEventListener('scroll', function () { panel.classList.add('hidden'); }, true);
                window.addEventListener('resize', function () { if (!panel.classList.contains('hidden')) position(); });
            })();
            </script>
        </nav>

        <div class="flex items-center gap-2 sm:gap-2.5 shrink-0">
            {{-- Sélecteur FR/EN masqué tant que l'interface n'est pas traduite (il ne changeait que sa propre couleur).
                 Pour le réafficher : LANGUAGE_SWITCH=true dans le .env une fois les traductions en place. --}}
            @if(env('LANGUAGE_SWITCH', false))
            <div class="lang-switch-ultra hidden sm:inline-flex">
                <a href="{{ route('lang.switch', 'fr') }}"
                   class="{{ session('locale', app()->getLocale()) === 'fr' ? 'bg-green-500 text-dark-900 shadow-sm' : 'text-gray-400 hover:text-white' }}">FR</a>
                <a href="{{ route('lang.switch', 'en') }}"
                   class="{{ session('locale', app()->getLocale()) === 'en' ? 'bg-green-500 text-dark-900 shadow-sm' : 'text-gray-400 hover:text-white' }}">EN</a>
            </div>
            @endif

            {{-- Recherche --}}
            <a href="{{ route('search') }}"
               class="w-9 h-9 flex items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-gray-400 hover:text-green-300 hover:border-green-500/30 hover:bg-green-500/5 transition"
               title="{{ __('Recherche') }}">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </a>

            @auth
            @php $__initials = strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)); @endphp
            <div class="relative block" id="nav-user-dropdown-wrap">

                {{-- Trigger --}}
                <button type="button" id="nav-user-dropdown-btn"
                        class="group inline-flex items-center gap-2 pl-1.5 pr-2 2xl:pr-3.5 py-1.5 rounded-full border border-white/10 bg-white/4 hover:border-orange-500/30 hover:bg-orange-500/6 transition-all duration-200">
                    <span class="w-7 h-7 rounded-full bg-linear-to-br from-orange-400 to-orange-600 flex items-center justify-center text-[11px] font-bold text-white shadow-sm">
                        {{ $__initials }}
                    </span>
                    <span class="hidden 2xl:inline text-sm font-medium text-gray-300 group-hover:text-orange-100 transition-colors">{{ __('Mon espace') }}</span>
                    <i class="fas fa-chevron-down text-[9px] text-gray-500 group-hover:text-orange-400 transition-all duration-200" id="nav-dd-chevron"></i>
                </button>

                {{-- Dropdown --}}
                <div id="nav-user-dropdown"
                     class="hidden absolute right-0 top-full mt-3 w-72 z-50"
                     role="menu">
                    <div class="rounded-2xl overflow-hidden"
                         style="background:var(--dd-bg);border:1px solid var(--dd-border);box-shadow:var(--dd-shadow);backdrop-filter:blur(32px)">

                        {{-- En-tête utilisateur --}}
                        <div class="relative px-4 pt-4 pb-3.5 overflow-hidden">
                            <div class="absolute inset-0" style="background:var(--dd-head-gradient)"></div>
                            <div class="relative flex items-start gap-3">
                                <div class="w-11 h-11 rounded-xl bg-linear-to-br from-orange-400 to-orange-600 flex items-center justify-center text-white text-sm font-bold shadow-lg shrink-0"
                                     style="box-shadow:0 8px 24px rgba(242, 121, 15,0.3)">
                                    {{ $__initials }}
                                </div>
                                <div class="min-w-0 flex-1 pt-0.5">
                                    <p class="text-white text-sm font-semibold leading-tight truncate">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</p>
                                    <p class="text-slate-500 text-xs truncate mt-0.5">{{ auth()->user()->email }}</p>
                                    <span class="mt-1.5 inline-flex items-center gap-1.5 text-[10px] font-semibold px-2 py-0.5 rounded-full
                                        @if(auth()->user()->role==='admin') bg-orange-500/10 text-orange-300 border border-orange-500/20
                                        @elseif(auth()->user()->role==='editor') bg-green-500/10 text-green-300 border border-green-500/20
                                        @elseif(auth()->user()->role==='provider') bg-orange-500/10 text-orange-300 border border-orange-500/20
                                        @else bg-emerald-500/10 text-emerald-300 border border-emerald-500/20 @endif">
                                        <span class="w-1.5 h-1.5 rounded-full
                                            @if(auth()->user()->role==='admin') bg-orange-400
                                            @elseif(auth()->user()->role==='editor') bg-green-400
                                            @elseif(auth()->user()->role==='provider') bg-orange-400
                                            @else bg-emerald-400 @endif"></span>
                                        {{ ucfirst(auth()->user()->role) }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div style="height:1px;background:var(--dd-divider);margin:0 1rem"></div>

                        {{-- Navigation --}}
                        <div class="p-2">
                            @if(auth()->user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> {{ __('Tableau de bord') }}
                                </a>
                                <a href="{{ route('admin.users.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-users"></i></span> {{ __('Utilisateurs') }}
                                </a>
                                <a href="{{ route('admin.payments.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-chart-line"></i></span> {{ __('Finance') }}
                                </a>
                            @elseif(auth()->user()->role === 'editor')
                                <a href="{{ route('editor.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> {{ __('Tableau de bord') }}
                                </a>
                                <a href="{{ route('editor.articles.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-pen-nib"></i></span> {{ __('Mes articles') }}
                                </a>
                            @elseif(auth()->user()->role === 'provider')
                                <a href="{{ route('provider.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> {{ __('Tableau de bord') }}
                                </a>
                                <a href="{{ route('provider.profile.edit') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-store"></i></span> {{ __('Ma fiche') }}
                                </a>
                                <a href="{{ route('provider.billing.plans') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gem"></i></span> {{ __('Mon forfait') }}
                                </a>
                            @else
                                <a href="{{ route('visitor.dashboard') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-gauge-high"></i></span> {{ __('Tableau de bord') }}
                                </a>
                                <a href="{{ route('visitor.reservations.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-bed"></i></span> {{ __('Mes réservations') }}
                                </a>
                                <a href="{{ route('visitor.receipts.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-receipt"></i></span> {{ __('Mes reçus') }}
                                </a>
                                <a href="{{ route('visitor.wallet.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-wallet"></i></span> {{ __('Mon portefeuille') }}
                                </a>
                                <a href="{{ route('visitor.purchases.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-image"></i></span> {{ __('Mes achats') }}
                                </a>
                                <a href="{{ route('visitor.art-orders.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-palette"></i></span> {{ __('Mes commandes Art') }}
                                </a>
                                <a href="{{ route('visitor.profile.edit') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-user-pen"></i></span> {{ __('Mon profil') }}
                                </a>
                                <a href="{{ route('visitor.favorites.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-heart"></i></span> {{ __('Mes favoris') }}
                                </a>
                                <a href="{{ route('visitor.notifications.index') }}" class="nav-dd-lnk">
                                    <span class="nav-dd-icon"><i class="fas fa-bell"></i></span> {{ __('Notifications') }}
                                </a>
                            @endif
                        </div>

                        <div style="height:1px;background:var(--dd-divider);margin:0 1rem"></div>

                        {{-- Déconnexion --}}
                        <div class="p-2">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="nav-dd-lnk nav-dd-danger w-full text-left">
                                    <span class="nav-dd-icon"><i class="fas fa-right-from-bracket"></i></span>
                                    {{ __('Déconnexion') }}
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-200 btn-ghost-header">
                {{ __('Connexion') }}
            </a>
            <a href="{{ route('plans.public') }}"
               class="inline-flex items-center gap-1.5 px-3.5 sm:px-5 py-2 text-dark-900 text-xs sm:text-sm font-bold btn-gold-header">
                <i class="fas fa-star text-[10px] sm:text-xs opacity-90 hidden sm:inline"></i>
                <span>{{ __('S’abonner') }}</span>
            </a>
            @endauth

            <button id="public-menu-toggle" type="button"
                class="min-[1400px]:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-gray-200 hover:text-white hover:border-green-500/30 hover:bg-green-500/5 transition">
                <i class="fas fa-bars-staggered text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="public-mobile-menu" class="mobile-menu-ultra min-[1400px]:hidden hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 space-y-1.5 font-plus">
            @foreach($publicNavItems as $item)
                @if(!empty($item['dropdown']))
                <div>
                    <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.fa-chevron-down').classList.toggle('rotate-180')"
                            class="w-full flex items-center justify-between px-4 py-3 text-gray-700 font-medium text-sm tracking-wide {{ $item['active'] ? 'is-active' : '' }}">
                        <span>{{ $item['label'] }}</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div class="hidden">
                        <div class="flex flex-col divide-y divide-black/5 px-2 py-1">
                            <a href="{{ route('accommodations.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                                <span class="w-8 h-8 rounded-lg bg-[#fdf4ec] text-[#b9500a] inline-flex items-center justify-center text-base"><x-icon name="bed" /></span>
                                <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Hôtels') }}</p>
                                <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                            </a>
                            @foreach($providerSectors as $sector)
                                @continue($sector->slug === 'hotels')
                                <a href="{{ $sectorUrl($sector) }}" class="flex items-center gap-3 px-2 py-3 group">
                                    <span class="w-8 h-8 rounded-lg bg-[#fdf4ec] text-[#b9500a] inline-flex items-center justify-center text-base"><x-icon :name="($sectorMeta[$sector->slug] ?? ['widget'])[0]" /></span>
                                    <p class="font-semibold text-xs text-gray-800 flex-1">{{ $sector->localized('name') }}</p>
                                    <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ $item['href'] }}" class="block px-4 py-3 text-gray-700 font-medium text-sm tracking-wide {{ $item['active'] ? 'is-active' : '' }}">
                    {{ $item['label'] }}
                </a>
                @endif
            @endforeach
            {{-- Médiathèque (accordéon) --}}
            <div>
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.fa-chevron-down').classList.toggle('rotate-180'); this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true')"
                        aria-expanded="{{ $publicMediaActive ? 'true' : 'false' }}"
                        class="w-full flex items-center justify-between px-4 py-3 text-gray-700 font-medium text-sm tracking-wide {{ $publicMediaActive ? 'is-active' : '' }}">
                    <span>{{ __('Médiathèque') }}</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $publicMediaActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div class="{{ $publicMediaActive ? '' : 'hidden' }}">
                    <div class="flex flex-col divide-y divide-black/5 px-2 py-1">
                        <a href="{{ route('gallery.public') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="w-8 h-8 rounded-lg bg-[#fdf4ec] text-[#b9500a] inline-flex items-center justify-center text-base"><x-icon name="camera" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Galerie photo') }}</p>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                        </a>
                        <a href="{{ route('videos.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="w-8 h-8 rounded-lg bg-[#fdf4ec] text-[#b9500a] inline-flex items-center justify-center text-base"><x-icon name="clapperboard-play" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Vidéos') }}</p>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                        </a>
                        <a href="{{ route('comics.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="w-8 h-8 rounded-lg bg-[#fdf4ec] text-[#b9500a] inline-flex items-center justify-center text-base"><x-icon name="book" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Les Astuces de Mian') }} <span style="display:inline-block;margin-left:4px;padding:1px 7px;border-radius:999px;background:#b9500a;color:#fff;font-size:10px;font-weight:800">{{ __('Nouveau') }}</span></p>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                        </a>
                    </div>
                </div>
            </div>
            <div class="pt-4 mt-2 border-t border-black/10 flex flex-col gap-2">
                <a href="{{ route('login') }}" class="text-center py-3 text-sm font-semibold text-gray-700 btn-ghost-header">{{ __('Connexion') }}</a>
                <a href="{{ route('plans.public') }}" class="text-center py-3 text-sm font-bold text-dark-900 btn-gold-header">{{ __('S’abonner') }}</a>
            </div>
        </div>
    </div>
</header>

{{-- ══════════════════════════════════════════════════════════
     FLASH INFO — bandeau défilant, géré depuis le back-office
     (Administration → Apparence → Flash info)
══════════════════════════════════════════════════════════ --}}
@if($activeFlashInfos->isNotEmpty())
@php
    $flashAllDismissible = $activeFlashInfos->every(fn ($f) => $f->is_dismissible);
    $flashIdsKey = $activeFlashInfos->pluck('id')->implode(',');
    $flashDuration = max(35, $activeFlashInfos->count() * 10);
@endphp
<div id="flash-ticker" class="flash-ticker relative z-10 overflow-hidden text-white"
     data-flash-ids="{{ $flashIdsKey }}" role="region" aria-label="{{ __('Fil d\'actualités') }}">
    <div class="flash-ticker-bg absolute inset-0 pointer-events-none" aria-hidden="true"></div>
    <div class="flash-ticker-sheen absolute inset-0 pointer-events-none" aria-hidden="true"></div>
    <div class="flash-ticker-accent absolute inset-x-0 top-0 h-px pointer-events-none" aria-hidden="true"></div>
    <div class="flash-ticker-accent-bottom absolute inset-x-0 bottom-0 h-px pointer-events-none" aria-hidden="true"></div>

    <div class="relative mx-auto flex min-h-[3.75rem] max-w-[95rem] items-center gap-2.5 px-3 py-2.5 sm:min-h-[4.5rem] sm:gap-5 sm:px-5 sm:py-4 md:min-h-[5.75rem] md:gap-8 md:px-10">
        <div class="shrink-0">
            <span class="flash-ticker-badge">
                <span class="flash-ticker-badge-orb">
                    <span class="flash-ticker-badge-ring"></span>
                    <span class="flash-ticker-badge-ring flash-ticker-badge-ring--2"></span>
                    <i class="fas fa-tower-broadcast flash-ticker-badge-icon"></i>
                </span>
                <span class="flash-ticker-badge-copy">
                    <span class="flash-ticker-badge-live">{{ __('Flash') }}</span>
                    <span class="flash-ticker-badge-title">{{ __('Info') }}</span>
                </span>
            </span>
        </div>

        <div class="flash-ticker-track relative min-w-0 flex-1 overflow-hidden">
            <div class="flash-ticker-scroll flex w-max items-center whitespace-nowrap" style="animation-duration: {{ $flashDuration }}s">
                @for ($pass = 0; $pass < 2; $pass++)
                    @foreach($activeFlashInfos as $flash)
                        @if($flash->link_url)
                        <a href="{{ $flash->link_url }}" class="flash-ticker-item flash-ticker-item--{{ $flash->type }}">
                            <span class="flash-ticker-marker"></span>{{ $flash->message }}
                        </a>
                        @else
                        <span class="flash-ticker-item flash-ticker-item--{{ $flash->type }}">
                            <span class="flash-ticker-marker"></span>{{ $flash->message }}
                        </span>
                        @endif
                        <span class="flash-ticker-sep" aria-hidden="true"></span>
                    @endforeach
                @endfor
            </div>
        </div>

        @if($flashAllDismissible)
        <button type="button" class="flash-ticker-close shrink-0" onclick="dismissFlashTicker()" aria-label="{{ __('Fermer le fil d\'actualités') }}">
            <i class="fas fa-xmark"></i>
        </button>
        @endif
    </div>
</div>
<script>
    (function () {
        var el = document.getElementById('flash-ticker');
        if (!el) return;
        var storageKey = 'flashTickerDismissed';
        var currentIds = el.dataset.flashIds || '';

        window.dismissFlashTicker = function () {
            el.remove();
            try { localStorage.setItem(storageKey, currentIds); } catch (e) { /* indisponible : ignoré */ }
        };

        // Exécuté immédiatement pour éviter tout flash si le visiteur avait déjà
        // fermé exactement ce même jeu de messages lors d'une visite précédente.
        try {
            if (localStorage.getItem(storageKey) === currentIds) el.remove();
        } catch (e) { /* localStorage indisponible : on ignore */ }
    })();
</script>
@endif

{{-- ══════════════════════════════════════════════════════════
     MODAL DE CONTACT
══════════════════════════════════════════════════════════ --}}
<div id="contact-modal" class="hidden fixed inset-0 z-[60] p-4 bg-black/40" role="dialog" aria-modal="true" aria-labelledby="contact-modal-title">
    <div class="absolute inset-0" onclick="closeContactModal()" aria-hidden="true"></div>
    <style>
        /* Fenêtre de contact : en-tête vert à motifs, sujets en un clic, champs confortables. Aplats. */
        .ct { position: relative; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; border-radius: 26px; background: #fff; color: #1d1a16; font-family: 'Inter', sans-serif; }
        .ct-head { position: relative; overflow: hidden; padding: 22px 24px 26px; background: #123a25; color: #fff; }
        .ct-head .ivm-g { opacity: .1 !important; }
        .ct-head .ivm-g > * { stroke: #f6c38f !important; }
        .ct-head > :not(.ivm):not(.ivm-band) { position: relative; z-index: 1; }
        .ct-head .ivm-band { height: 6px; }
        .ct-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .ct-kick { display: inline-flex; align-items: center; gap: 7px; padding: 5px 11px; border-radius: 999px; background: rgba(255,255,255,.12); color: #f6c38f; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .ct-title { margin: 10px 0 0; font-family: 'Playfair Display', serif; font-size: 26px; font-weight: 700; line-height: 1.15; color: #fff; }
        .ct-sub { margin: 6px 0 0; font-size: 14px; color: #cfe6d7; }
        .ct-close { flex: none; width: 40px; height: 40px; border-radius: 999px; border: 0; background: rgba(255,255,255,.12); color: #fff; font-size: 18px; cursor: pointer; }
        .ct-close:hover { background: rgba(255,255,255,.22); }
        .ct-trust { display: flex; flex-wrap: wrap; gap: 6px 14px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 12.5px; color: #e7f3ec; }
        .ct-trust li { display: inline-flex; align-items: center; gap: 6px; }
        .ct-trust a { color: #fff; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
        .ct-form { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 14px; }
        .ct-topics { display: flex; flex-wrap: wrap; gap: 6px; }
        .ct-topics button { height: 34px; padding: 0 13px; border-radius: 999px; border: 1px solid #e6dccb; background: #fff; color: #3a2f22; font-size: 13px; font-weight: 600; cursor: pointer; }
        .ct-topics button:hover { border-color: #b9500a; color: #9a4308; }
        .ct-topics button.is-on { background: #fdf4ec; border-color: #b9500a; color: #9a4308; }
        .ct-label { display: block; margin: 0 0 6px; font-size: 13px; font-weight: 700; color: #1d1a16; }
        .ct-label em { font-style: normal; font-weight: 600; color: #8a7f6b; }
        .ct-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .ct-input { width: 100%; min-height: 48px; padding: 12px 14px; border-radius: 14px; border: 1.5px solid #e6dccb; background: #fbf9f5; color: #1d1a16; font: inherit; font-size: 15px; outline: none; transition: border-color .15s ease, background .15s ease; }
        .ct-input:focus { border-color: #b9500a; background: #fff; box-shadow: 0 0 0 4px rgba(185,80,10,.12); }
        textarea.ct-input { min-height: 130px; resize: vertical; line-height: 1.55; }
        .ct-err { margin: 5px 0 0; font-size: 12.5px; font-weight: 600; color: #b42318; }
        .ct-count { margin: 5px 0 0; text-align: right; font-size: 12px; color: #8a7f6b; }
        .ct-send { display: flex; align-items: center; justify-content: center; gap: 9px; height: 52px; border: 0; border-radius: 999px; background: #b9500a; color: #fff; font-size: 15.5px; font-weight: 800; cursor: pointer; }
        .ct-send:hover { background: #9a4308; }
        .ct-legal { margin: 0; text-align: center; font-size: 12px; color: #8a7f6b; }
        .ct button:focus-visible, .ct a:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        @media (max-width: 520px) {
            #contact-modal { padding: 0 !important; align-items: flex-end !important; }
            .ct { max-width: none; max-height: 94vh; border-radius: 24px 24px 0 0; }
            .ct-row { grid-template-columns: minmax(0, 1fr); }
            .ct-title { font-size: 22px; }
            .ct-head { padding: 18px 18px 22px; }
            .ct-form { padding: 16px 18px 20px; }
        }
    </style>
    <div class="ct">
        <div class="ct-head">
            @include('partials.ivorian-motifs', ['culture' => 'newsletter'])
            <div class="ct-top">
                <div>
                    <span class="ct-kick"><x-icon name="chat-round-dots" />{{ __('Contact') }}</span>
                    <p id="contact-modal-title" class="ct-title">{{ __('Envoyez-nous un message') }}</p>
                    <p class="ct-sub">{{ __('Nous vous répondrons dans les plus brefs délais.') }}</p>
                </div>
                <button type="button" onclick="closeContactModal()" class="ct-close" aria-label="{{ __('Fermer') }}">×</button>
            </div>
            <ul class="ct-trust">
                <li><x-icon name="shield-check" />{{ __('Données protégées') }}</li>
                <li><x-icon name="chat-round-dots" />{{ __('Ou appelez le') }} <a href="{{ $topPhoneHref }}">{{ $topPhoneDisplay }}</a></li>
            </ul>
        </div>
        <form method="post" action="{{ route('contact.store') }}" class="ct-form">
            @csrf
            {{-- Marqueur : rouvre cette fenêtre seulement si l'erreur vient de ce formulaire
                 (la connexion et l'inscription ont aussi un champ « email »). --}}
            <input type="hidden" name="_contact_form" value="1">

            <div>
                <span class="ct-label">{{ __('Votre demande concerne') }} <em>{{ __('(facultatif)') }}</em></span>
                <div class="ct-topics" data-ct-topics>
                    @foreach([__('Réservation'), __('Devenir prestataire'), __('Partenariat'), __('Signaler une erreur'), __('Autre')] as $topic)
                        <button type="button">{{ $topic }}</button>
                    @endforeach
                </div>
            </div>

            <div class="ct-row">
                <div>
                    <label for="contact-name" class="ct-label">{{ __('Nom complet') }}</label>
                    <input id="contact-name" name="name" type="text" required maxlength="255" value="{{ old('name') }}" class="ct-input" placeholder="{{ __('Jean Dupont') }}" autocomplete="name">
                    @error('name')<p class="ct-err">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="contact-email" class="ct-label">{{ __('E-mail') }}</label>
                    <input id="contact-email" name="email" type="email" required maxlength="255" value="{{ old('email') }}" class="ct-input" placeholder="vous@exemple.ci" autocomplete="email">
                    @error('email')<p class="ct-err">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="contact-subject" class="ct-label">{{ __('Objet') }}</label>
                <input id="contact-subject" name="subject" type="text" required maxlength="255" value="{{ old('subject') }}" class="ct-input" placeholder="{{ __('Sujet de votre message') }}">
                @error('subject')<p class="ct-err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="contact-message" class="ct-label">{{ __('Message') }}</label>
                <textarea id="contact-message" name="message" rows="5" required maxlength="5000" class="ct-input" placeholder="{{ __('Comment pouvons-nous vous aider ?') }}">{{ old('message') }}</textarea>
                <p class="ct-count" data-ct-count>0 / 5000</p>
                @error('message')<p class="ct-err">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="ct-send"><x-icon name="plane" />{{ __('Envoyer le message') }}</button>
            <p class="ct-legal">{{ __('Vos informations servent uniquement à vous répondre.') }}</p>
        </form>
    </div>
    <script>
    (function () {
        const subject = document.getElementById('contact-subject'), msg = document.getElementById('contact-message'), count = document.querySelector('[data-ct-count]');
        document.querySelectorAll('[data-ct-topics] button').forEach(b => b.addEventListener('click', () => {
            document.querySelectorAll('[data-ct-topics] button').forEach(x => x.classList.toggle('is-on', x === b));
            subject.value = b.textContent.trim();
        }));
        const upd = () => { count.textContent = msg.value.length + ' / 5000'; };
        msg.addEventListener('input', upd); upd();
    })();
    </script>
</div>
@if(old('_contact_form') && $errors->hasAny(['name', 'email', 'subject', 'message']))
    <span id="contact-modal-autoopen" class="hidden" aria-hidden="true"></span>
@endif

@include('partials.mian-welcome')

{{-- Barre d'onglets mobile (bas d'écran) : accès rapide + bouton Menu (ouvre le menu de l'en-tête) --}}
@php
    $tabUser = auth()->user();
    $tabDashboard = $tabUser ? match ($tabUser->role) {
        'admin' => route('admin.dashboard'),
        'editor' => route('editor.dashboard'),
        'provider' => route('provider.dashboard'),
        default => route('visitor.dashboard'),
    } : null;
@endphp
<style>
    .ti-tabbar { display: none; }
    @media (max-width: 767px) {
        body { padding-bottom: calc(72px + env(safe-area-inset-bottom)); }
        .ti-tabbar {
            display: grid; grid-template-columns: repeat(5, 1fr); align-items: end;
            position: fixed; left: 0; right: 0; bottom: 0; z-index: 45;
            padding: 6px 4px calc(6px + env(safe-area-inset-bottom));
            background: #ffffff; border-top: 1px solid #e8e1d3; box-shadow: 0 -6px 20px rgb(29 26 22 / .08);
        }
        .ti-tab {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            padding: 6px 2px; border: 0; background: none; cursor: pointer;
            font: 600 11px/1.1 Inter, system-ui, sans-serif; color: #6b6458; text-decoration: none;
        }
        .ti-tab i { font-size: 19px; height: 24px; display: flex; align-items: center; }
        .ti-tab.is-active { color: #14532d; }
        .ti-tab.is-active i { background: #e6f1e9; border-radius: 10px; padding: 0 14px; }
        .ti-tab--gold { color: #9a4308; }
        .ti-tab--gold i { color: #b8892b; }
        .ti-tab--main { margin-top: -26px; color: #14532d; }
        .ti-tab--main i {
            width: 56px; height: 56px; border-radius: 50%; padding: 0; justify-content: center;
            background: #1b6b3a; color: #ffffff; font-size: 22px; border: 4px solid #ffffff;
            box-shadow: 0 8px 18px rgb(27 107 58 / .35);
        }
        .ti-tab--main.is-active i { background: #14532d; padding: 0; border-radius: 50%; }
        .ti-tab[aria-expanded="true"] { color: #b9500a; }
    }
</style>
<nav class="ti-tabbar" aria-label="{{ __('Navigation mobile') }}">
    <a href="{{ route('home') }}" class="ti-tab {{ request()->routeIs('home') ? 'is-active' : '' }}">
        <i class="fas fa-house"></i><span>{{ __('Accueil') }}</span>
    </a>
    <a href="{{ route('tourist.cities') }}" class="ti-tab {{ request()->routeIs('tourist.*') ? 'is-active' : '' }}">
        <i class="fas fa-umbrella-beach"></i><span>{{ __('Tourisme') }}</span>
    </a>
    @if($tabUser)
    <a href="{{ $tabDashboard }}" class="ti-tab ti-tab--main">
        <i class="fas fa-user"></i><span>{{ __('Mon espace') }}</span>
    </a>
    <a href="{{ route('events.index') }}" class="ti-tab {{ request()->routeIs('events.*') ? 'is-active' : '' }}">
        <i class="fas fa-calendar-days"></i><span>{{ __('Événements') }}</span>
    </a>
    @else
    <a href="{{ route('login') }}" class="ti-tab ti-tab--main {{ request()->routeIs('login') ? 'is-active' : '' }}">
        <i class="fas fa-right-to-bracket"></i><span>{{ __('Connexion') }}</span>
    </a>
    <a href="{{ route('plans.public') }}" class="ti-tab ti-tab--gold">
        <i class="fas fa-star"></i><span>{{ __('S’abonner') }}</span>
    </a>
    @endif
    <button type="button" id="ti-tab-menu" class="ti-tab" aria-expanded="false" aria-controls="public-mobile-menu">
        <i class="fas fa-bars"></i><span>{{ __('Menu') }}</span>
    </button>
</nav>
@include('partials.ai-chat-widget')
<script>
    (function () {
        var tab = document.getElementById('ti-tab-menu');
        var menu = document.getElementById('public-mobile-menu');
        if (!tab || !menu) return;
        function sync() { tab.setAttribute('aria-expanded', menu.classList.contains('hidden') ? 'false' : 'true'); }
        tab.addEventListener('click', function () {
            menu.classList.toggle('hidden');
            sync();
        });
        var burger = document.getElementById('public-menu-toggle');
        if (burger) burger.addEventListener('click', function () { setTimeout(sync, 0); });
    })();
</script>

@include('partials.theme-light-bridge')
<script>
    (function () {
        // Menu mobile
        const toggle = document.getElementById('public-menu-toggle');
        const menu   = document.getElementById('public-mobile-menu');
        if (toggle && menu) toggle.addEventListener('click', () => menu.classList.toggle('hidden'));

        // Dropdown "Mon espace"
        const btn      = document.getElementById('nav-user-dropdown-btn');
        const dropdown = document.getElementById('nav-user-dropdown');
        const chevron  = document.getElementById('nav-dd-chevron');
        const wrap     = document.getElementById('nav-user-dropdown-wrap');
        if (btn && dropdown) {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const isOpen = !dropdown.classList.contains('hidden');
                dropdown.classList.toggle('hidden', isOpen);
                chevron?.classList.toggle('rotate-180', !isOpen);
            });
            document.addEventListener('click', (e) => { if (!wrap?.contains(e.target)) { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); } });
            document.addEventListener('keydown', (e) => { if (e.key === 'Escape') { dropdown.classList.add('hidden'); chevron?.classList.remove('rotate-180'); } });
        }

        // Header scroll effect
        const header = document.getElementById('main-header');
        function syncHeaderScroll() {
            if (!header) return;
            if (window.scrollY > 40) header.classList.add('header-scrolled');
            else header.classList.remove('header-scrolled');
        }
        window.addEventListener('scroll', syncHeaderScroll, { passive: true });
        syncHeaderScroll();

        // Contact modal
        window.openContactModal = function () {
            const el = document.getElementById('contact-modal');
            if (!el) return;
            el.classList.remove('hidden');
            el.classList.add('flex', 'items-center', 'justify-center');
            document.body.style.overflow = 'hidden';
            setTimeout(() => document.getElementById('contact-name')?.focus(), 60);
        };
        window.closeContactModal = function () {
            const el = document.getElementById('contact-modal');
            if (!el) return;
            el.classList.add('hidden');
            el.classList.remove('flex', 'items-center', 'justify-center');
            document.body.style.overflow = '';
        };
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape') window.closeContactModal(); });
        if (document.getElementById('contact-modal-autoopen')) window.openContactModal();
    })();
</script>

@include('partials.homepage-bubbles')

{{-- Charte d'interface commune des pages publiques (après les styles propres à chaque page). --}}
@include('partials.ui-kit')
