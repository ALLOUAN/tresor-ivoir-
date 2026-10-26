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
    $publicMediaActive = $publicGalleryActive || request()->routeIs('gallery.public.*', 'videos.*', 'comics.*', 'films.*');
    $headerImage = $headerImage ?? (\Illuminate\Support\Facades\Schema::hasTable('header_images')
        ? \App\Models\HeaderImage::query()->find(1)
        : null);
    $activeFlashInfos = \Illuminate\Support\Facades\Schema::hasTable('flash_infos')
        ? \App\Models\FlashInfo::currentlyVisible()->orderBy('display_order')->orderByDesc('id')->get()
        : collect();
@endphp

<style>
    .font-plus { font-family: 'Montserrat', 'Montserrat', system-ui, sans-serif; }

    /* ── Top bar ─────────────────────────────────────────── */
    .public-topbar {
        position: relative;
        isolation: isolate;
        /* Charte « Le Joyau » : le vert porte les fonds (texte blanc, contraste AA) ; l'orange reste l'accent. */
        background-color: #00874F;
        /* Mêmes frises que le Flash info (losanges en haut, chevrons en bas), couleurs adaptées au fond orange. */
        background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='14' viewBox='0 0 56 14'><path d='M14 2 l5 5 l-5 5 l-5 -5 z' fill='none' stroke='%23F3F8F5' stroke-opacity='.26' stroke-width='1.2'/><path d='M14 5 l2 2 l-2 2 l-2 -2 z' fill='%23F7C396' fill-opacity='.4'/><circle cx='42' cy='7' r='1.3' fill='%23F3F8F5' fill-opacity='.3'/></svg>"), url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='12' viewBox='0 0 56 12'><path d='M30 9 l5 -5 l5 5 l5 -5 l5 5' fill='none' stroke='%23FFFFFF' stroke-opacity='.18' stroke-width='1.3'/><circle cx='8' cy='6' r='1.2' fill='%23F3F8F5' fill-opacity='.28'/></svg>");
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
    .public-slogan-text { color: #FFFFFF; }
    /* Liens de la barre orange : pastilles en aplat (fond plus foncé que la barre, fin contour clair) ;
       au survol, la pastille passe en crème et le texte en orange de la charte. */
    .public-topbar-action {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        height: 32px;
        padding: 0 0.85rem;
        border-radius: 999px;
        border: 1px solid rgba(255,255,255,0.32);
        background: rgba(255,255,255,0.1);
        font-size: 12px;
        font-weight: 600;
        color: #FFFFFF;
        transition: color .15s ease, background-color .15s ease, border-color .15s ease;
    }
    .public-topbar-action:hover,
    .public-topbar-action:focus-visible {
        color: #00874F;
        background: #FFFFFF;
        border-color: #FFFFFF;
        outline: none;
    }
    .public-topbar-action i { font-size: 11px; color: #FFFFFF; transition: color .15s ease; }
    .public-topbar-action:hover i,
    .public-topbar-action:focus-visible i { color: #EB6E00; }

    /* ── Flash info ticker ───────────────────────────────── */
    /* Bandeau d'annonces sobre : fond encre plat, pastille orange, texte lisible.
       Les calques décoratifs (.flash-ticker-sheen / -accent) restent dans le markup mais sont neutres. */
    /* Frises tissées (losanges en haut, chevrons en bas, d'après les pagnes akan et baoulés) en couleurs du site ; la ligne de texte reste dégagée. */
    .flash-ticker-bg { background-color: #E8F4EE; background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='14' viewBox='0 0 56 14'><path d='M14 2 l5 5 l-5 5 l-5 -5 z' fill='none' stroke='%23EB6E00' stroke-opacity='.28' stroke-width='1.2'/><path d='M14 5 l2 2 l-2 2 l-2 -2 z' fill='%23EB6E00' fill-opacity='.34'/><circle cx='42' cy='7' r='1.3' fill='%23EB6E00' fill-opacity='.3'/></svg>"), url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='56' height='12' viewBox='0 0 56 12'><path d='M30 9 l5 -5 l5 5 l5 -5 l5 5' fill='none' stroke='%23D8E9E0' stroke-opacity='.24' stroke-width='1.2'/><circle cx='8' cy='6' r='1.2' fill='%23EB6E00' fill-opacity='.28'/></svg>"); background-size: 56px 14px, 56px 12px; background-position: 0 2px, 28px calc(100% - 5px); background-repeat: repeat-x, repeat-x; }
    .flash-ticker-sheen,
    .flash-ticker-accent { display: none; }
    /* Lisière kita (3 px) sous le bandeau */
    .flash-ticker-accent-bottom { height: 3px !important; background-color: #EB6E00; background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='48' height='3'><rect width='16' height='3' fill='%23EB6E00'/><rect x='16' width='6' height='3' fill='%23EB6E00'/><rect x='22' width='14' height='3' fill='%2300874F'/><rect x='36' width='4' height='3' fill='%23D8E9E0'/><rect x='40' width='8' height='3' fill='%23EB6E00'/></svg>"); background-size: 48px 3px; }
    .flash-ticker > .relative { min-height: 0 !important; padding-top: .55rem !important; padding-bottom: .55rem !important; }

    .flash-ticker-badge {
        position: relative;
        display: inline-flex; align-items: center; gap: 10px;
        padding: 4px 12px 4px 4px;
        border-radius: 999px;
        background: #FFFFFF;
    }
    .flash-ticker-badge-orb {
        position: relative;
        width: 28px; height: 28px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 999px;
        background: #EB6E00;
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
    .flash-ticker-badge-live { color: #B85600; text-transform: uppercase; }
    .flash-ticker-badge-title { color: #557466; text-transform: uppercase; font-weight: 600; }

    .flash-ticker-track {
        -webkit-mask-image: none;
                mask-image: none;
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
        color: #0E3B2A;
        text-decoration: none;
        transition: color .15s ease, background-color .15s ease;
    }
    a.flash-ticker-item:hover {
        color: #00874F;
        background: #FFFFFF;
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .flash-ticker-marker {
        width: 6px; height: 6px; border-radius: 999px;
        margin-right: 10px; flex-shrink: 0;
        background: #8AA99B;
    }
    .flash-ticker-item--info    .flash-ticker-marker { background: #7cc4ee; }
    .flash-ticker-item--success .flash-ticker-marker { background: #00874F; }
    .flash-ticker-item--warning .flash-ticker-marker { background: #EB6E00; }
    .flash-ticker-item--urgent  .flash-ticker-marker { background: #f2797b; }
    /* Séparateur : petit losange ocre, en écho à la trame */
    .flash-ticker-sep {
        width: 6px; height: 6px;
        margin: 0 14px;
        flex-shrink: 0;
        display: inline-block;
        border-radius: 1px;
        transform: rotate(45deg);
        background: #EB6E00;
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
        background-image: var(--header-bg-image, none), url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='240' height='84' viewBox='0 0 240 84'><g fill='none' stroke='%23EB6E00' stroke-opacity='.22' stroke-width='1.7' stroke-linecap='round' stroke-linejoin='round'><path d='M40 30 q0 -14 14 -14 q14 0 14 14'/><path d='M40 30 q-22 -10 -22 10 q0 16 18 14'/><path d='M68 30 q22 -10 22 10 q0 16 -18 14'/><path d='M48 40 q0 16 4 24 q2 6 -4 8'/><path d='M60 40 q0 18 -4 28'/><path d='M46 46 q-8 6 -6 14'/><path d='M62 46 q8 6 6 14'/></g><g fill='%23EB6E00' fill-opacity='.26'><circle cx='48' cy='31' r='1.4'/><circle cx='60' cy='31' r='1.4'/></g><g fill='none' stroke='%23EB6E00' stroke-opacity='.26' stroke-width='1.7' stroke-linecap='round'><path d='M106 60 a16 16 0 0 1 32 0'/><path d='M98 60 h48'/><path d='M122 38 v-8 M108 44 l-6 -6 M136 44 l6 -6 M100 54 l-8 -2 M144 54 l8 -2'/></g><g fill='none' stroke='%2300874F' stroke-opacity='.2' stroke-width='1.7' stroke-linecap='round'><path d='M196 74 q-3 -26 2 -46'/><path d='M198 28 q-16 -6 -26 4'/><path d='M198 28 q16 -6 26 4'/><path d='M198 28 q-10 -14 -22 -12'/><path d='M198 28 q10 -14 22 -12'/><path d='M198 28 v-10'/></g></svg>");
        background-size: cover, 240px 84px;
        background-position: center, 0 50%;
        background-repeat: no-repeat, repeat-x;
        border-bottom: 1px solid #D8E9E0;
        transition: box-shadow .2s ease;
    }
    /* Menu et marque posés sur des capsules blanches unies : textes nets, la frise reste visible autour. */
    .public-nav-rail { justify-self: center; width: max-content; max-width: 100%; padding: 5px 8px; border-radius: 999px; background: #fff; border: 1px solid #D8E9E0; }
    .public-brand-rail { padding: 4px 16px 4px 4px; border-radius: 22px; background: #fff; border: 1px solid #D8E9E0; }
    @media (max-width: 639px) { .public-brand-rail { padding: 3px 10px 3px 3px; border-radius: 18px; } }

    /* Avatar du compte : aplat orange de la marque (pas de dégradé), à toutes les tailles. */
    #main-header .hdr-avatar { background: #EB6E00; }

    /* ── En-tête mobile (< 640 px) : logo rond + nom du site à gauche, trois boutons ronds identiques de 44 px
          à droite (recherche, compte, menu) — zones tactiles ≥ 44 px (Apple HIG), sans cadre ni dégradé. ── */
    @media (max-width: 639px) {
        #main-header > div:not(#public-mobile-menu) { height: 64px; gap: 10px; }
        /* « S’abonner » est déjà dans la barre d’onglets du bas : retiré de l’en-tête pour laisser le nom du site lisible. */
        #main-header .hdr-subscribe { display: none; }
        #main-header > div:not(#public-mobile-menu) > div:last-child { justify-self: end; grid-column: -2 / -1; }
        #main-header .public-brand-rail { padding: 0; border: 0; border-radius: 0; background: transparent; gap: 10px; min-width: 0; }
        #main-header .public-brand-rail .logo-ring { border-radius: 999px; padding: 2px; background: #D8E9E0; }
        #main-header .public-brand-rail .logo-ring-inner { width: 40px; height: 40px; border-radius: 999px; }
        #main-header .public-brand-rail > div.hidden { display: block; min-width: 0; }
        #main-header .public-brand-rail > div.hidden p:first-child { font-size: 16px; line-height: 1.1; white-space: nowrap; }
        #main-header .public-brand-rail > div.hidden p + p { display: none; }
        #main-header .hdr-btn, #main-header .hdr-user { width: 44px; height: 44px; padding: 0; justify-content: center; border-radius: 999px; border: 1px solid #D8E9E0 !important; background: #ffffff !important; color: #0E3B2A; }
        #main-header .hdr-btn i { font-size: 15px; }
        #main-header .hdr-user { gap: 0; }
        #main-header .hdr-user #nav-dd-chevron { display: none; }
        #main-header .hdr-user .hdr-avatar { width: 36px; height: 36px; font-size: 12.5px; letter-spacing: .02em; }
        #main-header .hdr-btn:active, #main-header .hdr-user:active { background: #F3F8F5 !important; }
    }
    @media (max-width: 359px) { #main-header .public-brand-rail > div.hidden { display: none; } }
    /* Boutons ronds de l'en-tête (recherche, compte, menu) : fond blanc uni au-dessus de la frise ; les boutons colorés gardent le leur. */
    #main-header > div [class*="bg-white/"], #main-header > div button[class*="rounded"]:not([class*="bg-"]), #main-header .btn-ghost-header, #nav-user-dropdown-btn { background-color: #fff !important; }
    .public-header::before { content: ''; position: absolute; left: 0; right: 0; top: 0; height: 3px; pointer-events: none;
        background-image: url("data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' width='30' height='3'><rect width='12' height='3' fill='%23EB6E00'/><rect x='12' width='6' height='3' fill='%23D8E9E0'/><rect x='18' width='12' height='3' fill='%2300874F'/></svg>"); background-size: 30px 3px; background-repeat: repeat-x; }
    .public-header.header-scrolled {
        box-shadow: 0 6px 18px rgba(14,59,42,0.07);
    }
    .public-nav-pill {
        position: relative;
        padding: 0.5rem 0.55rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 500;
        letter-spacing: 0.01em;
        color: #24503D;
        transition: color .15s ease, background-color .15s ease;
    }
    @media (min-width: 1536px) {
        .public-nav-pill { padding: 0.45rem 0.55rem; }
    }
    /* Menu desktop : mini-pastille d'icône devant chaque rubrique (modèle commun des icônes, couleur du thème). */
    .public-nav-pill > i:first-child {
        display: inline-flex; align-items: center; justify-content: center; flex: none;
        width: 24px; height: 24px; margin-right: 5px; border-radius: 8px;
        font-size: 11px !important; opacity: 1 !important;
        background: var(--ti-ic-tint, #F3F8F5);
        border: 1px solid color-mix(in srgb, var(--ti-ic, #EB6E00) 22%, transparent);
        color: var(--ti-ic, #EB6E00);
        transition: background .15s ease, color .15s ease;
    }
    .public-nav-pill:hover > i:first-child, .public-nav-pill.is-active > i:first-child { background: var(--ti-ic, #EB6E00); color: #ffffff; }
    /* Visiteur non connecté (Connexion + S'abonner) : sous 1600 px, la place manque, les pastilles d'icône sont masquées. */
    @media (max-width: 1599px) {
        #main-header:has(.hdr-subscribe) .public-nav-pill > i:first-child { display: none; }
    }
    /* Bouton de compte : libellé « Mon espace » sur une ligne, affiché seulement sur très grand écran (sinon avatar seul). */
    #main-header .hdr-user > span:not(.hdr-avatar) { white-space: nowrap; }
    @media (max-width: 2099px) { #main-header .hdr-user > span:not(.hdr-avatar) { display: none !important; } #main-header .hdr-user { padding-right: 8px; } }

    /* ── Ordinateur (≥ 1024 px) : menu complet à toutes les largeurs, compacté par paliers au lieu d'être
          remplacé par le bouton burger. ── */
    @media (min-width: 1024px) {
        #main-header .public-nav-rail { display: flex !important; }
        #public-menu-toggle, #public-mobile-menu { display: none !important; }
        /* Colonne du menu bornée (minmax 0) : le menu ne pousse jamais la recherche / Connexion / S'abonner hors de l'écran.
           Conteneur élargi pour les grands écrans. */
        #main-header > div:not(#public-mobile-menu) { grid-template-columns: auto minmax(0, 1fr) auto; max-width: 120rem; }
    }
    /* Visiteur déconnecté (Connexion + S'abonner prennent ~300 px) : pastilles d'icône jusqu'à 1800 px seulement,
       et nom du site masqué sous 1600 px. */
    @media (min-width: 1600px) and (max-width: 1799px) {
        #main-header:has(.hdr-subscribe) .public-nav-pill > i:first-child { display: none !important; }
    }
    @media (min-width: 1024px) and (max-width: 1599px) {
        #main-header:has(.hdr-subscribe) .public-brand-rail { padding: 4px; border-radius: 999px; }
        #main-header:has(.hdr-subscribe) .public-brand-rail > div:not(.logo-ring) { display: none !important; }
    }
    /* Même règle pour toutes les rubriques (principales et secondaires) : sous 1600 px, libellés seuls, sans pastille d'icône. */
    @media (min-width: 1024px) and (max-width: 1599px) {
        #main-header .public-nav-pill > i:first-child { display: none !important; }
        #main-header .public-nav-pill { padding: .45rem .5rem; }
    }
    @media (min-width: 1024px) and (max-width: 1365px) {
        #main-header > div:not(#public-mobile-menu) { gap: 12px; padding-left: 16px; padding-right: 16px; }
        #main-header .public-brand-rail .logo-ring-inner { width: 3.25rem; height: 3.25rem; }
        #main-header .public-nav-rail { padding: 4px 6px; }
        #main-header .public-nav-pill { padding: .4rem .45rem; font-size: 12.5px; }
        #main-header .hdr-subscribe > span { white-space: nowrap; }
    }
    /* Logo toujours visible ; seul le nom du site s'efface quand la place manque. */
    @media (min-width: 1024px) and (max-width: 1439px) {
        #main-header .public-brand-rail { padding: 4px; border-radius: 999px; }
        #main-header .public-brand-rail > .logo-ring { display: block !important; }
        #main-header .public-brand-rail > div:not(.logo-ring) { display: none !important; }
    }
    @media (min-width: 1024px) and (max-width: 1199px) {
        #main-header .public-nav-pill { padding: .38rem .38rem; font-size: 12px; letter-spacing: 0; }
        #main-header .public-nav-pill [id$="-nav-chevron"] { display: none; }
        #main-header .btn-ghost-header { padding-left: 10px; padding-right: 10px; }
        #main-header .hdr-subscribe { padding-left: 12px; padding-right: 12px; }
    }
    .public-nav-pill:hover {
        color: #0E3B2A;
        background: #F3F8F5;
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
        background: #EB6E00;
        transform: scaleX(0);
        transition: transform .2s ease;
    }
    .public-nav-pill-glow.is-active::after { transform: scaleX(1); }
    .public-nav-pill.is-active {
        color: #00874F;
        font-weight: 600;
        background: transparent;
    }
    /* Panneau du mega-menu "Hôtels & Résidences" : fond toujours sombre,
       non affecté par le pont de theme clair (qui réécrit text-gray-200 en
       texte sombre, ce qui le rendait invisible sur ce fond resté sombre). */
    #hotels-nav-panel .text-gray-200 { color: #F3F8F5 !important; }
    #hotels-nav-panel a:hover { color: #ffffff !important; background-color: rgba(255,255,255,0.05) !important; }
    #hotels-nav-panel .border-white\/10 { border-color: rgba(255,255,255,0.1) !important; }
    /* Logo officiel horizontal (charte « Le Joyau ») : ≥ 140 px de large à l'écran (taille minimale), sans cadre ni effet. */
    /* Zone de protection (charte p. 3) : fond blanc et marge autour du logo, aucun décor ni menu ne l'approche. */
    #main-header .ti-header-logo { padding: 6px 12px !important; margin-right: 10px; border: 0 !important; border-radius: 12px !important; background: #fff !important; position: relative; z-index: 2; }
    #main-header .ti-header-logo__img { display: block; height: 54px; width: auto; max-width: none; }
    @media (min-width: 1024px) and (max-width: 1599px) { #main-header .ti-header-logo__img { height: 44px; } #main-header .ti-header-logo { padding: 5px 8px !important; margin-right: 4px; } #main-header .public-nav-pill { padding: .4rem .4rem; font-size: 12.5px; } }
    @media (max-width: 1365px) { #main-header .ti-header-logo__img { height: 42px; } }
    /* 1024–1199 px : « Autour de moi » et « Mon séjour » quittent la barre (toujours accessibles depuis l'accueil et le menu mobile). */
    @media (min-width: 1024px) and (max-width: 1199px) { #main-header .public-nav-rail a[href*="autour-de-moi"], #main-header .public-nav-rail a[href*="mon-sejour"] { display: none !important; } }
    /* Montserrat est plus large que l'ancienne police : entre 1024 et 1439 px, menu resserré pour que toutes les rubriques restent visibles. */
    @media (min-width: 1024px) and (max-width: 1439px) { #main-header .public-nav-pill { padding: .34rem .3rem !important; font-size: 11.5px !important; letter-spacing: -.01em !important; } #main-header .ti-header-logo__img { height: 38px; } #main-header .ti-header-logo { padding: 4px 6px !important; margin-right: 0; } #main-header > div:not(#public-mobile-menu) { gap: 10px !important; } #main-header .btn-ghost-header, #main-header .hdr-subscribe { padding-left: 12px !important; padding-right: 12px !important; font-size: 13px !important; } }
    @media (max-width: 639px) { #main-header .ti-header-logo__img { height: 42px; } }
    @media (max-width: 359px) { #main-header .ti-header-logo__img { height: 38px; } }
    .logo-ring {
        position: relative;
        border-radius: 12px;
        padding: 1px;
        background: #D8E9E0;
    }
    .logo-ring-inner {
        border-radius: 11px;
        overflow: hidden;
        background: #ffffff;
    }
    /* Nom du site : vert forêt plat (le dégradé vert clair d'origine manquait de contraste). */
    #main-header .group p.bg-clip-text {
        background: none !important;
        color: #00874F !important;
        -webkit-text-fill-color: #00874F;
    }
    .lang-switch-ultra {
        display: inline-flex;
        padding: 2px;
        border-radius: 8px;
        background: #F3F8F5;
        border: 1px solid #D8E9E0;
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
        border: 1px solid #D8E9E0;
        background: #ffffff;
        transition: border-color .15s ease, background-color .15s ease, color .15s ease;
    }
    .btn-ghost-header:hover {
        border-color: #8AA99B;
        background: #FFFFFF;
    }
    /* Bouton d'action du header : orange d'action plein. */
    .btn-gold-header {
        border-radius: 8px;
        background: #EB6E00;
        color: #ffffff !important;
        transition: background-color .15s ease;
    }
    .btn-gold-header:hover { background: #B85600; }
    .btn-gold-header i, .btn-gold-header span { color: #ffffff !important; }
    #public-mobile-menu.mobile-menu-ultra {
        background: #ffffff;
        border-top: 1px solid #D8E9E0;
        box-shadow: 0 16px 32px rgba(14,59,42,0.10);
    }
    #public-mobile-menu.mobile-menu-ultra a {
        border-radius: 10px;
        border: 1px solid transparent;
    }
    /* Mobile : le menu (dans l'en-tête collant) ne descend jamais sous la barre d'onglets du bas ;
       il défile à l'intérieur quand un accordéon (Résidences, Médiathèque) l'allonge. */
    @media (max-width: 767px) {
        #public-mobile-menu.mobile-menu-ultra {
            max-height: calc(100vh - 64px - 78px - env(safe-area-inset-bottom));
            max-height: calc(100dvh - 64px - 78px - env(safe-area-inset-bottom));
            overflow-y: auto;
            overscroll-behavior: contain;
            -webkit-overflow-scrolling: touch;
            padding-bottom: 12px;
        }
        /* Menu ouvert : le bouton de l'assistant ne recouvre plus les rubriques. */
        body:has(#public-mobile-menu:not(.hidden)) .ti-chat-fab { display: none; }
    }
    #public-mobile-menu.mobile-menu-ultra a:hover { background: #F3F8F5; }
    #public-mobile-menu.mobile-menu-ultra a.is-active {
        background: #F3F8F5;
        border-color: #D8E9E0;
        color: #00874F;
        font-weight: 600;
    }

    /* ── Dropdown "Mon espace" ───────────────────────────── */
    :root {
        --dd-bg: rgba(8,10,14,0.96);
        --dd-border: rgba(255,255,255,0.07);
        --dd-shadow: 0 32px 64px -8px rgba(14,59,42,0.9), 0 0 0 1px rgba(255,255,255,0.03), inset 0 1px 0 rgba(255,255,255,0.06);
        --dd-divider: rgba(255,255,255,0.06);
        --dd-head-gradient: rgba(235,110,0,0.07);
    }
    html:not(.dark) {
        --dd-bg: rgba(255,255,255,0.99);
        --dd-border: rgba(14,59,42,0.09);
        --dd-shadow: 0 24px 48px -8px rgba(14,59,42,0.12), 0 0 0 1px rgba(14,59,42,0.05), inset 0 1px 0 rgba(255,255,255,0.9);
        --dd-divider: rgba(14,59,42,0.07);
        --dd-head-gradient: rgba(235,110,0,0.05);
    }
    .nav-dd-lnk {
        display: flex; align-items: center; gap: 0.75rem;
        padding: 0.45rem 0.625rem; border-radius: 0.625rem;
        font-size: 0.8125rem; font-weight: 500; color: #24503D;
        text-decoration: none; white-space: nowrap;
        background: none; border: none; cursor: pointer;
        transition: background .15s ease, color .15s ease;
        width: 100%; text-align: left;
    }
    .nav-dd-lnk:hover { background: rgba(14,59,42,0.04); color: #0E3B2A; }
    .nav-dd-lnk:hover .nav-dd-icon { background: rgba(235,110,0,0.14); color: #B85600; }
    .nav-dd-icon {
        width: 1.875rem; height: 1.875rem; border-radius: 0.5rem; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
        background: rgba(14,59,42,0.05); color: #24503D; font-size: 0.7rem;
        transition: background .15s ease, color .15s ease;
    }
    .nav-dd-danger { color: #dc2626; }
    .nav-dd-danger .nav-dd-icon { color: #dc2626; background: rgba(239,68,68,0.08); }
    .nav-dd-danger:hover { background: rgba(239,68,68,0.06); color: #b91c1c; }

    /* Menu « Mon espace » : même famille que le menu utilisateur du back-office. */
    .pdd { --pdd-forest: #00874F; --pdd-orange: #EB6E00; --pdd-amber: #EB6E00; --pdd-ink: #0E3B2A; --pdd-soft: #24503D; --pdd-line: #D8E9E0;
        position: absolute; right: 0; top: calc(100% + 12px); z-index: 50; width: 330px; max-width: calc(100vw - 24px); padding: 8px; border: 1px solid var(--pdd-line); border-radius: 24px; background: rgba(255,255,255,.97); backdrop-filter: blur(14px); box-shadow: 0 30px 60px -28px rgba(14,59,42,.45), 0 2px 6px rgba(14,59,42,.05); transform-origin: top right; animation: pdd-in .22s cubic-bezier(.2,.9,.3,1.2); color: var(--pdd-ink); }
    .pdd.hidden { display: none; }
    @keyframes pdd-in { from { opacity: 0; transform: translateY(-6px) scale(.96); } to { opacity: 1; transform: none; } }
    .pdd-head { position: relative; overflow: hidden; display: flex; align-items: center; gap: 14px; padding: 18px 16px 26px; border-radius: 18px; background: #00874F; color: #fff; }
    .pdd-head .ivm-g { opacity: .2 !important; }
    .pdd-head .ivm-g > * { stroke: #F7C396 !important; }
    .pdd-head .ivm-band { height: 8px; }
    .pdd-head > .pdd-av, .pdd-head > .pdd-head__txt { position: relative; z-index: 2; }
    .pdd-av { position: relative; width: 58px; height: 58px; flex: none; padding: 2px; border-radius: 999px; background: var(--pdd-forest); }
    .pdd-av > span { width: 100%; height: 100%; border-radius: inherit; border: 2px solid #fff; background: #EB6E00; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 19px; font-weight: 800; letter-spacing: .04em; }
    .pdd-av::after { content: ''; position: absolute; right: 0; bottom: 0; width: 13px; height: 13px; border-radius: 999px; background: #00874F; border: 2px solid #00874F; }
    .pdd-head__txt { min-width: 0; }
    .pdd-head__txt b { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 16px; font-weight: 800; color: #fff; }
    .pdd-head__txt small { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; margin-top: 2px; font-size: 12.5px; color: rgba(255,255,255,.75); }
    .pdd-badge { display: inline-flex; align-items: center; gap: 6px; margin-top: 9px; padding: 4px 10px; border-radius: 999px; background: var(--pdd-amber); color: var(--pdd-ink); font-size: 11px; font-weight: 800; letter-spacing: .05em; text-transform: uppercase; }
    .pdd-badge i { font-size: 10px; }
    .pdd-list { display: grid; gap: 2px; padding: 8px 0 4px; }
    .pdd-item { --t: 0,135,79; display: flex; align-items: center; gap: 12px; padding: 8px 10px; border-radius: 14px; color: var(--pdd-ink); text-decoration: none; transition: background .18s ease; }
    .pdd-feature { margin-top: 8px; }
    .pdd-ic { width: 38px; height: 38px; flex: none; border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; background: rgba(var(--t), .1); color: rgb(var(--t)); font-size: 15px; transition: background .18s ease, color .18s ease, transform .18s ease; }
    .pdd-txt { flex: 1; min-width: 0; display: flex; flex-direction: column; line-height: 1.2; }
    .pdd-txt b { font-size: 14px; font-weight: 700; }
    .pdd-txt small { margin-top: 2px; font-size: 12px; color: var(--pdd-soft); }
    .pdd-go { font-size: 11px; color: rgb(var(--t)); opacity: 0; transform: translateX(-6px); transition: opacity .18s ease, transform .18s ease; }
    .pdd-item:hover, .pdd-item:focus-visible, .pdd-tile:hover, .pdd-tile:focus-visible { background: rgba(var(--t), .07); outline: none; }
    .pdd-item:hover .pdd-ic, .pdd-item:focus-visible .pdd-ic, .pdd-tile:hover .pdd-ic, .pdd-tile:focus-visible .pdd-ic { background: rgb(var(--t)); color: #fff; transform: rotate(-6deg); }
    .pdd-item:hover .pdd-go, .pdd-item:focus-visible .pdd-go { opacity: 1; transform: none; }
    .pdd-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 4px; padding: 4px 0; }
    .pdd-tile { --t: 0,135,79; display: flex; align-items: center; gap: 10px; padding: 7px 8px; border-radius: 14px; color: var(--pdd-ink); font-size: 13px; font-weight: 700; text-decoration: none; transition: background .18s ease; }
    .pdd-tile .pdd-ic { width: 32px; height: 32px; border-radius: 10px; font-size: 13px; }
    .pdd-out { display: flex; align-items: center; justify-content: center; gap: 9px; width: 100%; height: 44px; margin-top: 6px; border: 1px solid #f5c9cf !important; border-radius: 14px; background: #FFFFFF !important; color: #be123c; font-size: 14px; font-weight: 800; cursor: pointer; transition: background .18s ease, color .18s ease, border-color .18s ease; }
    .pdd-out:hover, .pdd-out:focus-visible { background: #e11d48 !important; border-color: #e11d48 !important; color: #fff; outline: none; }
    @media (max-width: 640px) { .pdd { position: fixed; top: 72px; left: 12px; right: 12px; width: auto; max-width: none; } }
    @media (prefers-reduced-motion: reduce) { .pdd { animation: none; } .pdd-ic, .pdd-go { transition: none; } }
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
                {{-- Offre aux professionnels (B2B) : sans objet pour un client connecté. --}}
                @unless(auth()->user()?->role === 'visitor')
                <a href="{{ route('prestations.public') }}" class="public-topbar-action">
                    <i class="fas fa-concierge-bell"></i>
                    {{ __('Nos prestations') }}
                </a>
                @endunless
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
        {{-- Logo officiel « Le Joyau », version horizontale sans signature (largeur < 200 px : charte, p. 3). --}}
        <a href="{{ route('home') }}" class="public-brand-rail ti-header-logo flex items-center shrink-0" aria-label="{{ $siteBrand['site_name'] }} — {{ __('Accueil') }}">
            <img src="{{ $siteBrand['logo_compact_url'] ?? $siteBrand['logo_url'] }}" alt="{{ $siteBrand['site_name'] }}" width="1200" height="369" class="ti-header-logo__img">
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
                {{-- Rubriques secondaires (is-wide) : toujours visibles sur ordinateur, réduites à leur icône sur les écrans étroits. --}}
                <a href="{{ $item['href'] }}" @if(! empty($item['wideOnly'])) title="{{ $item['label'] }}" @endif
                   class="public-nav-pill public-nav-pill-glow whitespace-nowrap inline-flex items-center gap-1 {{ $item['active'] ? 'is-active' : '' }} {{ ! empty($item['wideOnly']) ? 'is-wide' : '' }}">
                    <i class="{{ $item['icon'] }} text-[10px] opacity-80"></i>
                    <span class="pill-label">{{ $item['shortLabel'] ?? $item['label'] }}</span>
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
                    .mdd { width: 300px; padding: 8px; border-radius: 18px; background: #fff; border: 1px solid #D8E9E0; box-shadow: 0 24px 50px -28px rgba(14,59,42,.45); font-family: 'Montserrat', sans-serif; }
                    .mdd-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 12px; text-decoration: none; transition: background .15s ease; }
                    .mdd-item:hover, .mdd-item:focus-visible { background: #F3F8F5; outline: none; }
                    .mdd-item.is-on { background: #F3F8F5; }
                    .mdd-ico { flex: none; width: 42px; height: 42px; border-radius: 12px; background: #F3F8F5; color: #EB6E00; display: inline-flex; align-items: center; justify-content: center; font-size: 20px; }
                    .mdd-item:hover .mdd-ico { background: var(--ti-ic, #EB6E00); border-color: var(--ti-ic, #EB6E00); color: #fff; }
                    .mdd-ico--sm { width: 32px; height: 32px; border-radius: 10px; font-size: 16px; }
                    .mdd-txt b { display: block; font-size: 14.5px; font-weight: 700; color: #0E3B2A; line-height: 1.3; }
                    .mdd-txt small { display: block; margin-top: 2px; font-size: 12.5px; color: #557466; line-height: 1.35; }
                    .mdd-item:hover .mdd-txt b { color: #B85600; }
                    .mdd.is-wide { width: 620px; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 2px; }
                    .mdd-foot { grid-column: 1 / -1; display: flex; align-items: center; justify-content: space-between; gap: 10px; margin: 6px 4px 2px; padding: 10px 8px 4px; border-top: 1px solid #F3F8F5; font-size: 12.5px; color: #557466; }
                    .mdd-foot a { font-weight: 700; color: #B85600; text-decoration: none; }
                    .mdd-foot a:hover { text-decoration: underline; text-underline-offset: 3px; }
                    .mdd-new { display: inline-block; margin-left: 4px; padding: 1px 7px; border-radius: 999px; background: #EB6E00; color: #fff; font-style: normal; font-size: 10.5px; font-weight: 800; vertical-align: 2px; }
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
                        <a href="{{ route('films.index') }}" class="mdd-item {{ request()->routeIs('films.*') ? 'is-on' : '' }}" role="menuitem">
                            <span class="mdd-ico"><x-icon name="videocamera-record" /></span>
                            <span class="mdd-txt"><b>{{ __('Cinéma d\'Ivoire') }} <em class="mdd-new">{{ __('Nouveau') }}</em></b><small>{{ __('Films documentaires et de fiction') }}</small></span>
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
               class="hdr-btn w-9 h-9 flex items-center justify-center rounded-full border border-white/10 bg-white/[0.04] text-gray-400 hover:text-green-300 hover:border-green-500/30 hover:bg-green-500/5 transition"
               title="{{ __('Recherche') }}">
                <i class="fas fa-magnifying-glass text-sm"></i>
            </a>

            @auth
            @php $__initials = strtoupper(substr(auth()->user()->first_name ?? '', 0, 1) . substr(auth()->user()->last_name ?? '', 0, 1)); @endphp
            <div class="relative block" id="nav-user-dropdown-wrap">

                {{-- Trigger --}}
                <button type="button" id="nav-user-dropdown-btn"
                        class="hdr-user group inline-flex items-center gap-2 pl-1.5 pr-2 2xl:pr-3.5 py-1.5 rounded-full border border-white/10 bg-white/4 hover:border-orange-500/30 hover:bg-orange-500/6 transition-all duration-200">
                    <span class="hdr-avatar w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold text-white">
                        {{ $__initials }}
                    </span>
                    <span class="hidden 2xl:inline text-sm font-medium text-gray-300 group-hover:text-orange-100 transition-colors">{{ __('Mon espace') }}</span>
                    <i class="fas fa-chevron-down text-[9px] text-gray-500 group-hover:text-orange-400 transition-all duration-200" id="nav-dd-chevron"></i>
                </button>

                {{-- Dropdown (même famille que le menu utilisateur du back-office) --}}
                @php
                    $__u = auth()->user();
                    $__role = $__u->role;
                    $__roleLabel = ['admin' => __('Admin'), 'editor' => __('Éditeur'), 'provider' => __('Prestataire')][$__role] ?? __('Voyageur');
                    // [route, icône, libellé, sous-titre, teinte RGB]
                    $__links = match ($__role) {
                        'admin' => [
                            [route('admin.dashboard'), 'fa-gauge-high', __('Tableau de bord'), __('Vue d\'ensemble'), '49,98,28'],
                            [route('admin.users.index'), 'fa-users', __('Utilisateurs'), __('Comptes et rôles'), '50,118,97'],
                            [route('admin.payments.index'), 'fa-chart-line', __('Finance'), __('Paiements et revenus'), '201,138,12'],
                            [route('account.edit'), 'fa-user', __('Mon compte'), __('Profil et mot de passe'), '239,124,43'],
                        ],
                        'editor' => [
                            [route('editor.dashboard'), 'fa-gauge-high', __('Tableau de bord'), __('Vue d\'ensemble'), '49,98,28'],
                            [route('editor.articles.index'), 'fa-pen-nib', __('Mes articles'), __('Rédiger et publier'), '50,118,97'],
                            [route('account.edit'), 'fa-user', __('Mon compte'), __('Profil et mot de passe'), '239,124,43'],
                        ],
                        'provider' => [
                            [route('provider.dashboard'), 'fa-gauge-high', __('Tableau de bord'), __('Vue d\'ensemble'), '49,98,28'],
                            [route('provider.profile.edit'), 'fa-store', __('Ma fiche'), __('Votre activité en ligne'), '50,118,97'],
                            [route('provider.billing.plans'), 'fa-gem', __('Mon forfait'), __('Abonnement et options'), '201,138,12'],
                            [route('account.edit'), 'fa-user', __('Mon compte'), __('Profil et mot de passe'), '239,124,43'],
                        ],
                        default => null,
                    };
                    $__tiles = $__links ? [] : [
                        [route('visitor.reservations.index'), 'fa-bed', __('Réservations'), '49,98,28'],
                        [route('visitor.receipts.index'), 'fa-receipt', __('Reçus'), '50,118,97'],
                        [route('visitor.wallet.index'), 'fa-wallet', __('Portefeuille'), '201,138,12'],
                        [route('visitor.purchases.index'), 'fa-image', __('Achats'), '239,124,43'],
                        [route('visitor.art-orders.index'), 'fa-palette', __('Commandes Art'), '185,80,10'],
                        [route('visitor.favorites.index'), 'fa-heart', __('Favoris'), '225,29,72'],
                        [route('visitor.notifications.index'), 'fa-bell', __('Notifications'), '50,118,97'],
                        [route('visitor.profile.edit'), 'fa-user-pen', __('Mon profil'), '49,98,28'],
                    ];
                @endphp
                <div id="nav-user-dropdown" class="hidden pdd" role="menu">
                    <div class="pdd-head">
                        @include('partials.ivorian-motifs', ['culture' => 'national'])
                        <span class="pdd-av"><span>{{ $__initials }}</span></span>
                        <div class="pdd-head__txt">
                            <b>{{ $__u->first_name }} {{ $__u->last_name }}</b>
                            <small>{{ $__u->email }}</small>
                            <span class="pdd-badge"><i class="fas {{ $__role === 'visitor' ? 'fa-suitcase-rolling' : 'fa-shield-halved' }}"></i>{{ $__roleLabel }}</span>
                        </div>
                    </div>

                    @if($__links)
                        <div class="pdd-list">
                            @foreach($__links as [$__href, $__icon, $__label, $__sub, $__tone])
                                <a href="{{ $__href }}" class="pdd-item" role="menuitem" style="--t: {{ $__tone }}">
                                    <span class="pdd-ic"><i class="fas {{ $__icon }}"></i></span>
                                    <span class="pdd-txt"><b>{{ $__label }}</b><small>{{ $__sub }}</small></span>
                                    <i class="fas fa-arrow-right pdd-go" aria-hidden="true"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <a href="{{ route('visitor.dashboard') }}" class="pdd-item pdd-feature" role="menuitem" style="--t: 49,98,28">
                            <span class="pdd-ic"><i class="fas fa-gauge-high"></i></span>
                            <span class="pdd-txt"><b>{{ __('Tableau de bord') }}</b><small>{{ __('Votre voyage en un coup d\'œil') }}</small></span>
                            <i class="fas fa-arrow-right pdd-go" aria-hidden="true"></i>
                        </a>
                        <div class="pdd-grid">
                            @foreach($__tiles as [$__href, $__icon, $__label, $__tone])
                                <a href="{{ $__href }}" class="pdd-tile" role="menuitem" style="--t: {{ $__tone }}">
                                    <span class="pdd-ic"><i class="fas {{ $__icon }}"></i></span>{{ $__label }}
                                </a>
                            @endforeach
                        </div>
                    @endif

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="pdd-out" role="menuitem"><i class="fas fa-power-off"></i>{{ __('Déconnexion') }}</button>
                    </form>
                </div>
            </div>
            @else
            <a href="{{ route('login') }}"
               class="hidden sm:inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-gray-200 btn-ghost-header">
                {{ __('Connexion') }}
            </a>
            <a href="{{ route('plans.public') }}"
               class="hdr-subscribe inline-flex items-center gap-1.5 px-3.5 sm:px-5 py-2 text-dark-900 text-xs sm:text-sm font-bold btn-gold-header">
                <i class="fas fa-star text-[10px] sm:text-xs opacity-90 hidden sm:inline"></i>
                <span>{{ __('S’abonner') }}</span>
            </a>
            @endauth

            <button id="public-menu-toggle" type="button"
                class="hdr-btn min-[1400px]:hidden w-10 h-10 flex items-center justify-center rounded-xl border border-white/10 bg-white/[0.04] text-gray-200 hover:text-white hover:border-green-500/30 hover:bg-green-500/5 transition">
                <i class="fas fa-bars-staggered text-sm"></i>
            </button>
        </div>
    </div>

    {{-- Mobile menu --}}
    <div id="public-mobile-menu" class="mobile-menu-ultra min-[1400px]:hidden hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-4 space-y-1.5 font-plus">
            @foreach($publicNavItems as $item)
                @continue(! empty($item['headerHidden']))  {{-- Annuaire : masqué dans le menu mobile comme dans l'en-tête --}}
                @if(!empty($item['dropdown']))
                <div>
                    <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.fa-chevron-down').classList.toggle('rotate-180')"
                            class="mnav-row w-full flex items-center justify-between px-3 py-2 text-gray-700 font-medium text-sm tracking-wide {{ $item['active'] ? 'is-active' : '' }}">
                        <span class="flex items-center gap-3"><span class="mdd-ico mdd-ico--sm"><i class="{{ $item['icon'] ?? 'fas fa-circle' }}" aria-hidden="true"></i></span>{{ $item['label'] }}</span>
                        <i class="fas fa-chevron-down text-xs transition-transform duration-200"></i>
                    </button>
                    <div class="hidden">
                        <div class="flex flex-col divide-y divide-black/5 px-2 py-1">
                            <a href="{{ route('accommodations.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                                <span class="mdd-ico mdd-ico--sm"><x-icon name="bed" /></span>
                                <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Hôtels') }}</p>
                                <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                            </a>
                            @foreach($providerSectors as $sector)
                                @continue($sector->slug === 'hotels')
                                <a href="{{ $sectorUrl($sector) }}" class="flex items-center gap-3 px-2 py-3 group">
                                    <span class="mdd-ico mdd-ico--sm"><x-icon :name="($sectorMeta[$sector->slug] ?? ['widget'])[0]" /></span>
                                    <p class="font-semibold text-xs text-gray-800 flex-1">{{ $sector->localized('name') }}</p>
                                    <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
                @else
                <a href="{{ $item['href'] }}" class="mnav-row flex items-center gap-3 px-3 py-2 text-gray-700 font-medium text-sm tracking-wide {{ $item['active'] ? 'is-active' : '' }}">
                    <span class="mdd-ico mdd-ico--sm"><i class="{{ $item['icon'] ?? 'fas fa-circle' }}" aria-hidden="true"></i></span>{{ $item['label'] }}
                </a>
                @endif
            @endforeach
            {{-- Médiathèque (accordéon) --}}
            <div>
                <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.fa-chevron-down').classList.toggle('rotate-180'); this.setAttribute('aria-expanded', this.getAttribute('aria-expanded') === 'true' ? 'false' : 'true')"
                        aria-expanded="{{ $publicMediaActive ? 'true' : 'false' }}"
                        class="mnav-row w-full flex items-center justify-between px-3 py-2 text-gray-700 font-medium text-sm tracking-wide {{ $publicMediaActive ? 'is-active' : '' }}">
                    <span class="flex items-center gap-3"><span class="mdd-ico mdd-ico--sm"><i class="fas fa-photo-film" aria-hidden="true"></i></span>{{ __('Médiathèque') }}</span>
                    <i class="fas fa-chevron-down text-xs transition-transform duration-200 {{ $publicMediaActive ? 'rotate-180' : '' }}"></i>
                </button>
                <div class="{{ $publicMediaActive ? '' : 'hidden' }}">
                    <div class="flex flex-col divide-y divide-black/5 px-2 py-1">
                        <a href="{{ route('gallery.public') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="mdd-ico mdd-ico--sm"><x-icon name="camera" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Galerie photo') }}</p>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                        </a>
                        <a href="{{ route('videos.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="mdd-ico mdd-ico--sm"><x-icon name="clapperboard-play" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Vidéos') }}</p>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                        </a>
                        <a href="{{ route('films.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="mdd-ico mdd-ico--sm"><x-icon name="videocamera-record" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Cinéma d\'Ivoire') }} <span style="display:inline-block;margin-left:4px;padding:1px 7px;border-radius:999px;background:#EB6E00;color:#fff;font-size:10px;font-weight:800">{{ __('Nouveau') }}</span></p>
                            <i class="fas fa-chevron-right text-[10px] text-gray-300 group-hover:text-gray-500 transition"></i>
                        </a>
                        <a href="{{ route('comics.index') }}" class="flex items-center gap-3 px-2 py-3 group">
                            <span class="mdd-ico mdd-ico--sm"><x-icon name="book" /></span>
                            <p class="font-semibold text-xs text-gray-800 flex-1">{{ __('Les Astuces de Mian') }} <span style="display:inline-block;margin-left:4px;padding:1px 7px;border-radius:999px;background:#EB6E00;color:#fff;font-size:10px;font-weight:800">{{ __('Nouveau') }}</span></p>
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
        .ct { position: relative; width: 100%; max-width: 560px; max-height: 92vh; overflow-y: auto; border-radius: 26px; background: #fff; color: #0E3B2A; font-family: 'Montserrat', sans-serif; }
        .ct-head { position: relative; overflow: hidden; padding: 22px 24px 26px; background: #00874F; color: #fff; }
        .ct-head .ivm-g { opacity: .1 !important; }
        .ct-head .ivm-g > * { stroke: #F7C396 !important; }
        .ct-head > :not(.ivm):not(.ivm-band) { position: relative; z-index: 1; }
        .ct-head .ivm-band { height: 6px; }
        .ct-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 14px; }
        .ct-kick { display: inline-flex; align-items: center; gap: 7px; padding: 5px 11px; border-radius: 999px; background: rgba(255,255,255,.12); color: #F7C396; font-size: 11px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; }
        .ct-title { margin: 10px 0 0; font-family: 'Montserrat', serif; font-size: 26px; font-weight: 700; line-height: 1.15; color: #fff; }
        .ct-sub { margin: 6px 0 0; font-size: 14px; color: #D8E9E0; }
        .ct-close { flex: none; width: 40px; height: 40px; border-radius: 999px; border: 0; background: rgba(255,255,255,.12); color: #fff; font-size: 18px; cursor: pointer; }
        .ct-close:hover { background: rgba(255,255,255,.22); }
        .ct-trust { display: flex; flex-wrap: wrap; gap: 6px 14px; margin: 14px 0 0; padding: 0; list-style: none; font-size: 12.5px; color: #F3F8F5; }
        .ct-trust li { display: inline-flex; align-items: center; gap: 6px; }
        .ct-trust a { color: #fff; font-weight: 700; text-decoration: underline; text-underline-offset: 3px; }
        .ct-form { padding: 20px 24px 24px; display: flex; flex-direction: column; gap: 14px; }
        .ct-topics { display: flex; flex-wrap: wrap; gap: 6px; }
        .ct-topics button { height: 34px; padding: 0 13px; border-radius: 999px; border: 1px solid #D8E9E0; background: #fff; color: #0E3B2A; font-size: 13px; font-weight: 600; cursor: pointer; }
        .ct-topics button:hover { border-color: #EB6E00; color: #B85600; }
        .ct-topics button.is-on { background: #F3F8F5; border-color: #EB6E00; color: #B85600; }
        .ct-label { display: block; margin: 0 0 6px; font-size: 13px; font-weight: 700; color: #0E3B2A; }
        .ct-label em { font-style: normal; font-weight: 600; color: #557466; }
        .ct-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px; }
        .ct-input { width: 100%; min-height: 48px; padding: 12px 14px; border-radius: 14px; border: 1.5px solid #D8E9E0; background: #FFFFFF; color: #0E3B2A; font: inherit; font-size: 15px; outline: none; transition: border-color .15s ease, background .15s ease; }
        .ct-input:focus { border-color: #EB6E00; background: #fff; box-shadow: 0 0 0 4px rgba(235,110,0,.12); }
        textarea.ct-input { min-height: 130px; resize: vertical; line-height: 1.55; }
        .ct-err { margin: 5px 0 0; font-size: 12.5px; font-weight: 600; color: #b42318; }
        .ct-count { margin: 5px 0 0; text-align: right; font-size: 12px; color: #557466; }
        .ct-send { display: flex; align-items: center; justify-content: center; gap: 9px; height: 52px; border: 0; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15.5px; font-weight: 800; cursor: pointer; }
        .ct-send:hover { background: #B85600; }
        .ct-legal { margin: 0; text-align: center; font-size: 12px; color: #557466; }
        .ct button:focus-visible, .ct a:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }
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
            background: #ffffff; border-top: 1px solid #D8E9E0; box-shadow: 0 -6px 20px rgb(14 59 42 / .08);
        }
        .ti-tab {
            display: flex; flex-direction: column; align-items: center; gap: 3px;
            padding: 6px 2px; border: 0; background: none; cursor: pointer;
            font: 600 11px/1.1 Montserrat, system-ui, sans-serif; color: #557466; text-decoration: none;
        }
        .ti-tab i { font-size: 19px; height: 24px; display: flex; align-items: center; }
        .ti-tab.is-active { color: #00874F; }
        .ti-tab.is-active i { background: #F3F8F5; border-radius: 10px; padding: 0 14px; }
        .ti-tab--gold { color: #B85600; }
        .ti-tab--gold i { color: #EB6E00; }
        .ti-tab--main { margin-top: -26px; color: #00874F; }
        .ti-tab--main i {
            width: 56px; height: 56px; border-radius: 50%; padding: 0; justify-content: center;
            background: #00874F; color: #ffffff; font-size: 22px; border: 4px solid #ffffff;
            box-shadow: 0 8px 18px rgb(0 135 79 / .35);
        }
        .ti-tab--main.is-active i { background: #00874F; padding: 0; border-radius: 50%; }
        .ti-tab[aria-expanded="true"] { color: #EB6E00; }
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
