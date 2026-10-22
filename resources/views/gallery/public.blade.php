<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>{{ __('Galerie Trésors d\'Ivoire') }} — {{ $siteBrand['site_name'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Découvrez la galerie photo {{ $siteBrand['site_name'] }} : paysages, culture et art de vivre en Côte d'Ivoire.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1d1a16; }
        /* Médiathèque — galerie photo, même charte que la page Vidéos : crème, vert forêt, orange, Playfair.
           Mosaïque « bento » en tête, puis mosaïque à proportions réelles. Aplats, ombre au survol seulement. */
        .gm { background: #f6f3ed; padding-bottom: 72px; }
        .gm-wrap { max-width: 1320px; margin: 0 auto; padding: 0 20px; }
        .gm-top { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 14px; padding: 26px 0 18px; }
        .gm-tabs { display: inline-flex; gap: 4px; padding: 4px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; }
        .gm-tabs a { display: inline-flex; align-items: center; gap: 8px; height: 40px; padding: 0 16px; border-radius: 999px; font-size: 14px; font-weight: 700; color: #4a443a; text-decoration: none; }
        .gm-tabs a i { font-size: 18px; color: #b9500a; }
        .gm-tabs a:hover { background: #fdf4ec; color: #9a4308; }
        .gm-tabs a.is-on { background: #b9500a; color: #fff; }
        .gm-tabs a.is-on i { color: #fff; }
        .gm-stats { display: flex; flex-wrap: wrap; gap: 6px 20px; margin: 0; padding: 0; list-style: none; font-size: 14px; color: #4a443a; }
        .gm-stats li { display: inline-flex; align-items: center; gap: 8px; }
        .gm-stats i { color: #b9500a; font-size: 18px; }
        .gm-stats b { color: #1d1a16; }

        /* Mosaïque d'en-tête */
        .gm-bento { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); grid-template-rows: repeat(2, minmax(210px, auto)); gap: 14px; }
        .gm-tile { position: relative; overflow: hidden; border-radius: 26px; background: #e9e1d2; border: 1px solid #e6dccb; display: block; color: inherit; text-decoration: none; }
        .gm-tile img { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; transition: transform .6s ease; }
        .gm-tile:hover img { transform: scale(1.03); }
        .gm-tile:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 3px; }
        .gm-intro { grid-column: span 2; display: flex; flex-direction: column; justify-content: space-between; gap: 20px; padding: 30px; background: #1b6b3a; border-color: #1b6b3a; color: #fff; }
        .gm-intro.has-photo { background-size: cover; background-position: center; background-image: var(--gm-hero); }
        .gm-intro.has-photo::before { content: ''; position: absolute; inset: 0; background: rgba(20,83,45,.86); }
        .gm-intro > * { position: relative; }
        .gm-kicker { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .14em; text-transform: uppercase; color: #febf48; }
        .gm-kicker i { font-size: 16px; }
        .gm-title { margin: 12px 0 0; font-family: 'Playfair Display', serif; font-size: clamp(34px, 2.6vw + 16px, 56px); font-weight: 700; line-height: 1.02; letter-spacing: -.015em; color: #fff; }
        .gm-lead { margin: 14px 0 0; max-width: 520px; font-size: 16.5px; line-height: 1.6; color: rgba(255,255,255,.86); }
        .gm-cta { align-self: flex-start; display: inline-flex; align-items: center; gap: 10px; height: 50px; padding: 0 8px 0 20px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; transition: background .15s ease; }
        .gm-cta:hover { background: #9a4308; color: #fff; }
        .gm-cta span { width: 36px; height: 36px; border-radius: 999px; background: #fff; color: #b9500a; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; }
        .gm-feature { grid-column: 3 / span 2; grid-row: span 2; min-height: 440px; }
        .gm-tile__txt { position: absolute; left: 14px; right: 14px; bottom: 14px; display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 14px; border-radius: 18px; background: rgba(255,255,255,.95); }
        .gm-tile__txt small { display: block; font-size: 11.5px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: #b9500a; }
        .gm-tile__txt b { display: block; margin-top: 2px; font-size: 15.5px; line-height: 1.3; color: #1d1a16; }
        .gm-feature .gm-tile__txt b { font-family: 'Playfair Display', serif; font-size: 22px; }
        .gm-tile__go { flex: none; width: 40px; height: 40px; border-radius: 999px; background: #b9500a; color: #fff; font-size: 18px; display: inline-flex; align-items: center; justify-content: center; }
        .gm-badge { position: absolute; z-index: 2; top: 16px; left: 16px; display: inline-flex; align-items: center; gap: 6px; height: 30px; padding: 0 12px; border-radius: 999px; background: #febf48; color: #3d2b00; font-size: 12px; font-weight: 800; }

        /* Tri */
        .gm-bar { position: sticky; top: 72px; z-index: 20; margin-top: 26px; background: #f6f3ed; border-bottom: 1px solid #e6dccb; }
        .gm-bar__in { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 12px 0; }
        .gm-sorts { display: flex; gap: 8px; overflow-x: auto; scrollbar-width: none; }
        .gm-sorts::-webkit-scrollbar { display: none; }
        .gm-sort { flex: none; display: inline-flex; align-items: center; gap: 8px; height: 42px; padding: 0 16px 0 12px; border-radius: 999px; background: #fff; border: 1px solid #e6dccb; color: #3a352c; font-size: 14px; font-weight: 600; text-decoration: none; }
        .gm-sort i { font-size: 18px; color: #b9500a; }
        .gm-sort:hover { border-color: #b9500a; }
        .gm-sort.is-on { background: #b9500a; border-color: #b9500a; color: #fff; }
        .gm-sort.is-on i { color: #fff; }
        .gm-note { display: inline-flex; align-items: center; gap: 8px; font-size: 13px; color: #6b6458; white-space: nowrap; }
        .gm-note i { color: #1b6b3a; font-size: 16px; }

        /* Mosaïque à proportions réelles (hauteur de chaque carte calculée sur l'image) */
        .gm-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); grid-auto-rows: 8px; column-gap: 14px; margin-top: 24px; }
        .gm-card { position: relative; grid-row-end: span 34; margin: 0 0 14px; border-radius: 20px; overflow: hidden; background: #e9e1d2; }
        .gm-card__link { display: block; height: 100%; }
        .gm-card__link img { display: block; width: 100%; height: auto; transition: transform .6s ease; }
        .gm-card:hover .gm-card__link img { transform: scale(1.03); }
        .gm-card__link:focus-visible { outline: 3px solid #ef7c2b; outline-offset: -3px; }
        .gm-card__badge { position: absolute; top: 10px; left: 10px; display: inline-flex; align-items: center; gap: 5px; height: 26px; padding: 0 10px; border-radius: 999px; background: #febf48; color: #3d2b00; font-size: 11.5px; font-weight: 800; pointer-events: none; }
        .gm-card__cap { position: absolute; left: 0; right: 0; bottom: 0; display: flex; align-items: flex-end; justify-content: space-between; gap: 8px; margin: 0; padding: 40px 10px 10px; opacity: 0; transition: opacity .2s ease; pointer-events: none; }
        .gm-card__cap::before { content: ''; position: absolute; inset: 0; background: rgba(20,18,14,.5); }
        .gm-card__cap > * { position: relative; pointer-events: auto; }
        .gm-card:hover .gm-card__cap, .gm-card:focus-within .gm-card__cap { opacity: 1; }
        .gm-card__title { min-width: 0; font-size: 13.5px; font-weight: 700; line-height: 1.3; color: #fff; display: -webkit-box; -webkit-line-clamp: 2; line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        .gm-card__actions { flex: none; display: flex; gap: 6px; }
        .gm-act { display: inline-flex; align-items: center; gap: 6px; height: 34px; padding: 0 11px; border: 0; border-radius: 999px; background: #fff; color: #1d1a16; font-size: 12px; font-weight: 700; text-decoration: none; cursor: pointer; }
        .gm-act:hover { background: #fdf4ec; color: #9a4308; }
        .gm-act.gallery-download-btn { background: #b9500a; color: #fff; }
        .gm-act.gallery-download-btn:hover { background: #9a4308; color: #fff; }
        .gm-act:focus-visible { outline: 3px solid #ef7c2b; outline-offset: 2px; }
        .gm-loading { display: none; justify-content: center; padding: 30px 0; color: #b9500a; font-size: 22px; }
        .gm-loading.is-on { display: flex; }
        .gm-end { display: none; margin: 18px 0 0; text-align: center; font-size: 13.5px; color: #8a7f6b; }
        .gm-end.is-on { display: block; }

        .gm-empty { margin-top: 30px; padding: 54px 24px; border-radius: 26px; background: #fff; border: 1px dashed #d8cdb8; text-align: center; color: #4a443a; }
        .gm-empty > i { width: 76px; height: 76px; border-radius: 22px; background: #fdf4ec; color: #b9500a; font-size: 38px; }
        .gm-empty h2 { margin: 16px 0 6px; font-family: 'Playfair Display', serif; font-size: 28px; color: #1d1a16; }

        @media (hover: none) { .gm-card__cap { opacity: 1; padding-top: 26px; } .gm-card__title { display: none; } }
        @media (max-width: 1100px) { .gm-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
        @media (max-width: 1023px) {
            .gm-bento { grid-template-columns: repeat(2, minmax(0, 1fr)); grid-template-rows: auto; }
            .gm-intro { grid-column: 1 / -1; }
            .gm-feature { grid-column: 1 / -1; grid-row: auto; min-height: 0; aspect-ratio: 16 / 10; }
            .gm-mini { aspect-ratio: 4 / 5; }
            .gm-note { display: none; }
        }
        @media (max-width: 767px) {
            .gm-wrap { padding: 0 16px; }
            .gm { padding-bottom: 110px; }
            .gm-stats { display: none; }
            .gm-intro { padding: 22px; }
            .gm-feature { aspect-ratio: 4 / 5; }
            .gm-tile__txt { left: 10px; right: 10px; bottom: 10px; padding: 10px 12px; }
            .gm-feature .gm-tile__txt b { font-size: 18px; }
            .gm-mini .gm-tile__txt b { font-size: 13px; }
            .gm-mini .gm-tile__go { display: none; }
            .gm-bar { top: 60px; }
            .gm-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); column-gap: 10px; }
            .gm-card { margin-bottom: 10px; border-radius: 16px; }
            .gm-act { height: 30px; padding: 0 9px; }
        }
        @media (prefers-reduced-motion: reduce) { .gm-tile img, .gm-card__link img, .gm-card__cap { transition: none; } }
    </style>
    <link rel="stylesheet" href="{{ asset('css/tw-a2d53ae5.css') }}?v={{ @filemtime(public_path('css/tw-a2d53ae5.css')) }}">
</head>
<body class="antialiased">
    @include('partials.page-background')
    @include('partials.public-top-nav')

@php
    $galleryHeroImage = \Illuminate\Support\Facades\Schema::hasTable('gallery_hero_images')
        ? \App\Models\GalleryHeroImage::query()->find(1)
        : null;
    $heroPhoto = $galleryHeroImage && $galleryHeroImage->isVisible() ? $galleryHeroImage->image_url : null;
    $sort = $sort ?? 'recentes';
    $highlights = $highlights ?? collect();
    $feature = $highlights->first();
    $minis = $highlights->slice(1, 2);
    $total = ($galleryImages ?? null)?->total() ?? 0;
    $tileTitle = fn ($i) => trim((string) ($i->title ?: $i->alt_text ?: __('Photo')));
    $showUrl = fn ($i) => filled($i->uuid) ? route('gallery.public.show', $i->uuid) : url($i->url);
@endphp

<main class="gm">
    <div class="gm-wrap">
        <div class="gm-top">
            <nav class="gm-tabs" aria-label="{{ __('Médiathèque') }}">
                <a href="{{ route('gallery.public') }}" class="is-on" aria-current="page"><x-icon name="camera" />{{ __('Galerie photo') }}</a>
                <a href="{{ route('videos.index') }}"><x-icon name="clapperboard-play" />{{ __('Vidéos') }}</a>
            </nav>
            <ul class="gm-stats">
                <li><x-icon name="gallery" /><b>{{ number_format($total, 0, ',', ' ') }}</b> {{ trans_choice('photo|photos', $total) }}</li>
                @if(($totals->likes ?? 0) > 0)<li><x-icon name="heart" /><b>{{ number_format($totals->likes, 0, ',', ' ') }}</b> {{ __('j\'aime') }}</li>@endif
                @if(($totals->downloads ?? 0) > 0)<li><x-icon name="upload" /><b>{{ number_format($totals->downloads, 0, ',', ' ') }}</b> {{ __('téléchargements') }}</li>@endif
            </ul>
        </div>

        <section class="gm-bento" aria-label="{{ __('À la une') }}">
            <div class="gm-tile gm-intro {{ $heroPhoto ? 'has-photo' : '' }}" @if($heroPhoto) style="--gm-hero: url('{{ $heroPhoto }}');" @endif>
                <div>
                    <p class="gm-kicker"><x-icon name="camera" />{{ __('Photographie') }}</p>
                    <h1 class="gm-title">{{ __('Galerie Trésors d\'Ivoire') }}</h1>
                    <p class="gm-lead">{{ __('Un regard sur la Côte d\'Ivoire : instants choisis par la rédaction, à consulter et télécharger gratuitement.') }}</p>
                </div>
                @if($total > 0)
                    <a href="#gallery-grid" class="gm-cta">{{ __('Parcourir la galerie') }}<span><x-icon name="alt-arrow-down" /></span></a>
                @endif
            </div>
            @if($feature)
                <a href="{{ $showUrl($feature) }}" class="gm-tile gm-feature">
                    <img src="{{ url($feature->url) }}" alt="{{ $feature->alt_text ?: $tileTitle($feature) }}" decoding="async">
                    <span class="gm-badge"><x-icon name="star" />{{ $feature->is_featured ? __('Coup de cœur') : __('À la une') }}</span>
                    <span class="gm-tile__txt"><span><small>{{ __('Photographie') }}</small><b>{{ \Illuminate\Support\Str::limit($tileTitle($feature), 60) }}</b></span><span class="gm-tile__go" aria-hidden="true"><x-icon name="alt-arrow-right" /></span></span>
                </a>
            @endif
            @foreach($minis as $m)
                <a href="{{ $showUrl($m) }}" class="gm-tile gm-mini">
                    <img src="{{ url($m->url) }}" alt="{{ $m->alt_text ?: $tileTitle($m) }}" loading="lazy" decoding="async">
                    <span class="gm-tile__txt"><span><b>{{ \Illuminate\Support\Str::limit($tileTitle($m), 40) }}</b></span><span class="gm-tile__go" aria-hidden="true"><x-icon name="alt-arrow-right" /></span></span>
                </a>
            @endforeach
        </section>
    </div>

    @if($total > 0)
        <div class="gm-bar">
            <div class="gm-wrap gm-bar__in">
                <nav class="gm-sorts" aria-label="{{ __('Trier') }}">
                    @foreach(['recentes' => ['clock-circle', __('Récentes')], 'populaires' => ['heart', __('Les plus aimées')], 'telecharges' => ['upload', __('Les plus téléchargées')]] as $key => [$icon, $label])
                        <a href="{{ route('gallery.public', $key === 'recentes' ? [] : ['tri' => $key]) }}#gallery-grid" class="gm-sort {{ $sort === $key ? 'is-on' : '' }}" @if($sort === $key) aria-current="true" @endif><x-icon :name="$icon" />{{ $label }}</a>
                    @endforeach
                </nav>
                <span class="gm-note"><x-icon name="verified-check" />{{ __('Téléchargement gratuit, crédit apprécié') }}</span>
            </div>
        </div>
    @endif

    <div class="gm-wrap">
        @if(($galleryImages ?? collect())->isEmpty())
            <div class="gm-empty">
                <x-icon name="gallery" />
                <h2>{{ __('La galerie sera bientôt enrichie.') }}</h2>
                <p>{{ __('Revenez bientôt : la rédaction sélectionne de nouveaux instants.') }}</p>
            </div>
        @else
            <div id="gallery-grid" class="gm-grid"
                 data-has-more="{{ $galleryImages->hasMorePages() ? '1' : '0' }}"
                 data-next-page="{{ $galleryImages->currentPage() + 1 }}"
                 data-fetch-url="{{ route('gallery.public', $sort === 'recentes' ? [] : ['tri' => $sort]) }}">
                @include('gallery.partials.cards')
            </div>
            {{-- Sentinelle observée pour déclencher le chargement de la page suivante. --}}
            <div id="gallery-sentinel" class="h-1"></div>
            <div id="gallery-loading" class="gm-loading" role="status" aria-label="{{ __('Chargement') }}"><i class="fas fa-circle-notch fa-spin"></i></div>
            <p class="gm-end" id="gallery-end">{{ __('Vous avez tout vu. Revenez bientôt pour de nouvelles photos.') }}</p>
        @endif
    </div>
</main>

<script>
(function () {
    const grid = document.getElementById('gallery-grid');
    if (!grid) return;

    // ── Mosaïque : chaque carte occupe le nombre de rangées (8 px) correspondant à la hauteur réelle de sa photo ──
    const ROW = 8;
    function size(card) {
        const img = card.querySelector('img');
        if (!img || !img.naturalWidth) return;
        const gap = parseFloat(getComputedStyle(card).marginBottom) || 0;
        const h = card.clientWidth * img.naturalHeight / img.naturalWidth;
        card.style.gridRowEnd = 'span ' + Math.ceil((h + gap) / ROW);
    }
    function wire(card) {
        if (card.dataset.wired) return;
        card.dataset.wired = '1';
        const img = card.querySelector('img');
        if (img.complete) size(card); else img.addEventListener('load', () => size(card), { once: true });
    }
    grid.querySelectorAll('[data-gm-card]').forEach(wire);
    let rt = null;
    window.addEventListener('resize', () => { clearTimeout(rt); rt = setTimeout(() => grid.querySelectorAll('[data-gm-card]').forEach(size), 120); });

    // ── Défilement infini (à la Pinterest), en gardant le tri choisi ──
    const sentinel = document.getElementById('gallery-sentinel');
    const loading = document.getElementById('gallery-loading');
    const end = document.getElementById('gallery-end');
    let hasMore = grid.dataset.hasMore === '1';
    let nextPage = parseInt(grid.dataset.nextPage, 10) || 2;
    let fetching = false;
    if (!hasMore && grid.children.length > 20) end.classList.add('is-on');

    async function loadMore() {
        if (fetching || !hasMore) return;
        fetching = true;
        loading.classList.add('is-on');
        try {
            const url = new URL(grid.dataset.fetchUrl, location.origin);
            url.searchParams.set('page', nextPage);
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' } });
            const data = await res.json();
            if (data.html) {
                const temp = document.createElement('div');
                temp.innerHTML = data.html;
                temp.querySelectorAll('[data-gm-card]').forEach(c => { grid.appendChild(c); wire(c); });
            }
            hasMore = !!data.has_more;
            nextPage = data.next_page || (nextPage + 1);
        } catch (err) {
            // Silencieux : l'utilisateur peut toujours recharger la page.
        } finally {
            fetching = false;
            loading.classList.remove('is-on');
            if (!hasMore) { sentinelObs.disconnect(); end.classList.add('is-on'); }
        }
    }
    const sentinelObs = new IntersectionObserver((entries) => { if (entries.some(e => e.isIntersecting)) loadMore(); }, { rootMargin: '600px 0px' });
    sentinelObs.observe(sentinel);
})();
</script>

@include('partials.gallery-like-script')
@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
