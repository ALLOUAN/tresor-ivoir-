<!DOCTYPE html>
<html lang="fr" id="html-root" class="scroll-smooth overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @if(!empty($siteBrand['favicon_url']))
        <link rel="icon" href="{{ $siteBrand['favicon_url'] }}" type="image/png">
    @endif
    <title>Galerie Trésors d'Ivoire — {{ $siteBrand['site_name'] }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Découvrez la galerie photo {{ $siteBrand['site_name'] }} : paysages, culture et art de vivre en Côte d'Ivoire.">
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        gold: { 300:'#fdbe7b', 400:'#fa9a3c', 500:'#f2790f', 600:'#d4630a' },
                        dark: { 600:'#e9e5d9', 700:'#e9e5d9', 800:'#e9e5d9', 900:'#e9e5d9' },
                    }
                }
            }
        }
    </script>
    <style>
        * { box-sizing: border-box; }
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }
        .font-plus    { font-family: 'Plus Jakarta Sans', 'Inter', system-ui, sans-serif; }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background:#e9e5d9; }
        ::-webkit-scrollbar-thumb { background: #f2790f; border-radius: 3px; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .55s ease, transform .55s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .gold-line::after {
            content: '';
            display: block;
            width: 52px;
            height: 2px;
            background: linear-gradient(90deg, #f2790f, #fa9a3c);
            margin-top: 10px;
        }
        .gallery-mesh {
            background-color:#e9e5d9;
            background-image:
                radial-gradient(ellipse 120% 80% at 10% -10%, rgba(242, 121, 15, 0.14), transparent 55%),
                radial-gradient(ellipse 90% 60% at 100% 0%, rgba(242, 121, 15, 0.06), transparent 50%),
                radial-gradient(ellipse 70% 50% at 50% 110%, rgba(242, 121, 15, 0.05), transparent 55%);
        }
        .gallery-grid-noise {
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)' opacity='0.035'/%3E%3C/svg%3E");
        }
        .gallery-title-gradient {
            background: linear-gradient(135deg, #fff 0%, #fdbe7b 45%, #f2790f 100%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .gallery-hero-shell {
            background-color: #050403;
            background-image:
                linear-gradient(135deg, rgba(255, 255, 255,0.97) 0%, rgba(26,21,6,0.92) 42%, rgba(21,18,8,0.95) 100%),
                radial-gradient(ellipse 100% 60% at 0% 0%, rgba(242, 121, 15, 0.18), transparent 52%),
                radial-gradient(ellipse 80% 50% at 100% 20%, rgba(242, 121, 15, 0.07), transparent 48%),
                radial-gradient(ellipse 60% 40% at 50% 100%, rgba(242, 121, 15, 0.06), transparent 55%);
        }
        /* Image de fond configurable (back-office) : posée sur le calque assombrissant
           (voir .gallery-hero-bgphoto plus bas) plutôt que sur .gallery-hero-shell,
           qui est entièrement recouvert par ce calque opaque. Par défaut (sans image),
           le dégradé reste opaque à l'identique du design d'origine. */
        .gallery-hero-bgphoto {
            background-image: linear-gradient(to bottom right, #0d0904, #07070c, #0d0904);
        }
        .gallery-hero-bgphoto[style*="--gallery-hero-bg-image"] {
            background-image:
                linear-gradient(to bottom right, rgba(13,9,4,.78), rgba(7,7,12,.78), rgba(13,9,4,.78)),
                var(--gallery-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .gallery-hero-grid {
            background-image:
                repeating-linear-gradient(45deg, rgba(242, 121, 15, 0.04) 0, rgba(242, 121, 15, 0.04) 1px, transparent 0, transparent 14px);
        }
        .gallery-hero-title {
            background: linear-gradient(125deg, #e9e5d9 0%, #fef9c3 28%, #fa9a3c 52%, #9f4709 88%);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            letter-spacing: -0.02em;
        }
        .gallery-hero-glow {
            filter: blur(64px);
            opacity: 0.85;
        }
        /* Bandeau harmonisé avec les pages Annuaire/Magazine/Événements/Cultures :
           mêmes dimensions (py-20, max-w-6xl, cadre bg-green-950/70). */
        .gallery-hero {
            background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55));
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .gallery-hero[style*="--gallery-hero-bg-image"] {
            background-image: var(--gallery-hero-bg-image, none);
        }
        html:not(.dark) .gallery-hero-shell {
            background-color:#e9e5d9;
            background-image:
                linear-gradient(135deg, rgba(255,255,255,0.98) 0%, rgba(248,244,236,0.96) 42%, rgba(245,239,228,0.98) 100%),
                radial-gradient(ellipse 100% 60% at 0% 0%, rgba(242, 121, 15,0.14), transparent 52%);
            border-bottom-color: rgba(0,0,0,0.08);
        }
        html:not(.dark) .gallery-title-gradient {
            background: none;
            color: #1c1915;
            -webkit-text-fill-color: #1c1915;
        }
        html:not(.dark) .gallery-mesh {
            background-color:#e9e5d9;
            background-image:
                radial-gradient(ellipse 120% 80% at 10% -10%, rgba(242, 121, 15, 0.08), transparent 55%);
        }
        .gallery-selection-badge {
            border-color: rgba(255,255,255,0.16);
            background: rgba(0,0,0,0.3);
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
        }
        .gallery-selection-copy {
            color: rgba(243,244,246,0.98);
            text-shadow: 0 2px 10px rgba(0,0,0,0.45);
        }
        .gallery-selection-note {
            color: rgba(229,231,235,0.94);
        }
        html:not(.dark) .gallery-selection-badge {
            border-color: rgba(255, 255, 255,0.12);
            background: rgba(233, 229, 217, 0.9);
            box-shadow: 0 8px 24px -16px rgba(255, 255, 255,0.2), inset 0 1px 0 rgba(255,255,255,0.9);
        }
        html:not(.dark) .gallery-selection-copy {
            color: #374151;
            text-shadow: none;
        }
        html:not(.dark) .gallery-selection-note {
            color: #4b5563;
        }
    </style>
</head>
<body class="bg-dark-900 text-white antialiased font-sans">
    @include('partials.page-background')

@include('partials.public-top-nav')

@php
    $galleryHeroImage = \Illuminate\Support\Facades\Schema::hasTable('gallery_hero_images')
        ? \App\Models\GalleryHeroImage::query()->find(1)
        : null;
@endphp

<section class="relative py-20 overflow-hidden {{ ($galleryHeroImage && $galleryHeroImage->isVisible()) ? 'gallery-hero' : '' }}"
    @if($galleryHeroImage && $galleryHeroImage->isVisible())
        style="--gallery-hero-bg-image: url('{{ $galleryHeroImage->image_url }}');"
    @endif
>
    <div class="absolute inset-0 bg-gradient-to-b from-orange-900/20 to-transparent pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <div class="inline-block rounded-2xl bg-green-950/70 backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
            <p class="text-orange-400 text-sm font-medium uppercase tracking-widest mb-3">Photographie</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                Galerie Trésors d'Ivoire
            </h1>
            <p class="text-gray-200 text-xl max-w-2xl mx-auto">
                Un regard sur la Côte d'Ivoire : instants choisis par la rédaction, à consulter et télécharger gratuitement.
            </p>
        </div>
    </div>
</section>

<section class="relative isolate py-16 sm:py-24 border-t border-white/[0.06] gallery-mesh overflow-hidden">
    <div class="pointer-events-none absolute inset-0 gallery-grid-noise opacity-90"></div>
    <div class="pointer-events-none absolute -top-32 left-1/2 h-[28rem] w-[min(100%,56rem)] -translate-x-1/2 rounded-full bg-gradient-to-b from-orange-500/15 via-orange-600/5 to-transparent blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 h-64 w-64 rounded-full bg-green-600/5 blur-3xl"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6">
        @if(($galleryImages ?? collect())->isEmpty())
            <div class="text-center py-24 sm:py-28 rounded-[1.75rem] border border-dashed border-white/10 bg-white/[0.02] backdrop-blur-xl shadow-[0_0_0_1px_rgba(255,255,255,0.04)_inset] reveal">
                <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-2xl border border-orange-500/20 bg-gradient-to-br from-orange-500/20 to-transparent">
                    <i class="fas fa-images text-2xl text-orange-400/80"></i>
                </div>
                <p class="text-white/90 font-plus text-lg font-medium tracking-tight">La galerie sera bientôt enrichie.</p>
                <p class="text-gray-300 text-sm mt-3 max-w-md mx-auto leading-relaxed">Publiez des images depuis l’administration (section « Accueil - Galerie »), actives et datées.</p>
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 mt-10 px-6 py-3 rounded-full bg-orange-500 text-black text-sm font-bold shadow-lg shadow-orange-900/30 hover:bg-orange-400 transition">
                    <i class="fas fa-arrow-left text-xs"></i> Retour à l’accueil
                </a>
            </div>
        @else
            <div class="mb-12 sm:mb-16 flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between reveal">
                <div class="max-w-2xl">
                    <div class="gallery-selection-badge inline-flex items-center gap-2 rounded-full border px-3.5 py-1.5 backdrop-blur-md mb-5">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-60"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-400"></span>
                        </span>
                        <span class="gallery-selection-copy text-[11px] font-plus font-semibold uppercase tracking-[0.2em]">{{ $galleryImages->total() }} visuel{{ $galleryImages->total() > 1 ? 's' : '' }}</span>
                    </div>
                    <h2 class="font-serif text-3xl sm:text-4xl md:text-5xl font-semibold tracking-tight leading-[1.1] gallery-title-gradient">
                        Sélection
                    </h2>
                    <p class="gallery-selection-note mt-4 text-sm sm:text-base font-plus leading-relaxed max-w-lg">
                        Cliquez sur une carte pour la fiche détaillée — survolez pour un aperçu dynamique.
                    </p>
                </div>
                <div class="gallery-selection-copy hidden sm:flex items-center gap-2 text-xs font-plus uppercase tracking-widest">
                    <i class="fas fa-grip text-orange-500/40"></i>
                    Grille adaptative
                </div>
            </div>

            <div id="gallery-grid" class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 sm:gap-3.5"
                 data-has-more="{{ $galleryImages->hasMorePages() ? '1' : '0' }}"
                 data-next-page="{{ $galleryImages->currentPage() + 1 }}"
                 data-fetch-url="{{ route('gallery.public') }}">
                @include('gallery.partials.cards')
            </div>

            {{-- Sentinelle observée pour déclencher le chargement de la page suivante. --}}
            <div id="gallery-sentinel" class="h-1"></div>
            <div id="gallery-loading" class="hidden justify-center py-10">
                <i class="fas fa-circle-notch fa-spin text-orange-400 text-xl"></i>
            </div>
        @endif
    </div>
</section>

<script>
    const reveals = document.querySelectorAll('.reveal');
    const obs = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.08 });
    reveals.forEach(el => obs.observe(el));

    // Défilement infini (à la Pinterest) : charge automatiquement la page
    // suivante quand la sentinelle en bas de grille entre dans le viewport.
    (function () {
        const grid = document.getElementById('gallery-grid');
        const sentinel = document.getElementById('gallery-sentinel');
        const loading = document.getElementById('gallery-loading');
        if (!grid || !sentinel) return;

        let hasMore = grid.dataset.hasMore === '1';
        let nextPage = parseInt(grid.dataset.nextPage, 10) || 2;
        let fetching = false;
        const fetchUrl = grid.dataset.fetchUrl;

        function revealNewCards(container) {
            container.querySelectorAll('.reveal:not(.visible)').forEach((el) => el.classList.add('visible'));
        }

        async function loadMore() {
            if (fetching || !hasMore) return;
            fetching = true;
            loading?.classList.remove('hidden');
            loading?.classList.add('flex');

            try {
                const res = await fetch(`${fetchUrl}?page=${nextPage}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                });
                const data = await res.json();

                if (data.html) {
                    const temp = document.createElement('div');
                    temp.innerHTML = data.html;
                    while (temp.firstChild) grid.appendChild(temp.firstChild);
                    revealNewCards(grid);
                }

                hasMore = !!data.has_more;
                nextPage = data.next_page || (nextPage + 1);
            } catch (err) {
                // Silencieux : l'utilisateur peut toujours recharger la page.
            } finally {
                fetching = false;
                loading?.classList.add('hidden');
                loading?.classList.remove('flex');
                if (!hasMore) sentinelObs.disconnect();
            }
        }

        const sentinelObs = new IntersectionObserver((entries) => {
            if (entries.some((e) => e.isIntersecting)) loadMore();
        }, { rootMargin: '600px 0px' });
        sentinelObs.observe(sentinel);
    })();
</script>

@include('partials.gallery-like-script')
@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
