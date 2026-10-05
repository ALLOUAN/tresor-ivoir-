<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notre carte — {{ $restaurant->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary {
            background: #f2790f;
            box-shadow: 0 12px 28px rgba(242,121,15,0.3);
            transition: transform .22s ease, filter .22s ease;
            color: #fff;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .section-kicker { letter-spacing: .22em; }
        .dish-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .dish-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(242,121,15,0.14); border-color: rgba(242,121,15,0.3); }
        .dish-cover { position: relative; overflow: hidden; cursor: zoom-in; }
        .dish-cover img { transition: transform .5s ease; }
        .dish-card:hover .dish-cover img { transform: scale(1.06); }
        .dish-cover-placeholder {
            background:
                radial-gradient(120% 100% at 20% 0%, rgba(242,121,15,0.16), transparent 60%),
                linear-gradient(150deg, #2a1b17 0%, #14100e 70%);
        }
        .dish-name-line {
            flex: 1; margin: 0 .6rem; border-bottom: 2px dotted rgba(28,25,21,0.2);
            transform: translateY(-4px);
        }
        .dish-link { transition: gap .18s ease, color .18s ease; }
        .dish-link:hover { gap: .55rem; color: #1d8348; }

        .filter-sidebar {
            border: 1px solid rgba(0,0,0,0.06);
            background: linear-gradient(165deg, #ffffff, #fbf6ee);
            box-shadow: 0 16px 40px rgba(20,18,12,0.08);
        }
        .filter-btn {
            position: relative;
            display: flex; align-items: center; gap: .7rem;
            width: 100%; padding: .7rem .9rem;
            border-radius: .9rem;
            background: transparent;
            transition: background .2s ease, transform .2s ease;
        }
        .filter-btn:hover { background: rgba(39,174,96,0.08); transform: translateX(2px); }
        .filter-btn .filter-icon {
            width: 2.3rem; height: 2.3rem; border-radius: .75rem; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
            background: rgba(39,174,96,0.1); color: #27AE60;
            transition: background .2s ease, color .2s ease, transform .2s ease;
        }
        .filter-btn .filter-count {
            margin-left: auto; font-size: .7rem; font-weight: 700;
            background: rgba(28,25,21,0.06); color: #8a7f6b;
            padding: .15rem .5rem; border-radius: 999px;
            transition: background .2s ease, color .2s ease;
        }
        .filter-btn.is-active {
            background: linear-gradient(135deg, #58d68d 0%, #27AE60 60%, #1d8348 100%);
            box-shadow: 0 10px 24px rgba(39,174,96,0.35);
        }
        .filter-btn.is-active .filter-icon { background: rgba(255,255,255,0.2); color: #fff; transform: scale(1.08); }
        .filter-btn.is-active .filter-count { background: rgba(255,255,255,0.22); color: #fff; }
        .filter-btn.is-active span.filter-label { color: #fff; }
        .filter-label { font-weight: 700; font-size: .9rem; color: #1c1915; transition: color .2s ease; }

        .menu-category-block { transition: opacity .25s ease; }
        .menu-category-block.is-hidden { display: none; }

        .category-hero { position: relative; }
        .category-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            z-index: 0;
            background-image: var(--category-hero-bg-image, none);
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    {{-- ═══ HERO ═══ --}}
    <section class="relative py-20 overflow-hidden {{ $restaurant->thumbnail ? 'category-hero' : '' }}"
        @if($restaurant->thumbnail)
            style="--category-hero-bg-image: url('{{ $restaurant->thumbnail }}');"
        @endif
    >
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="inline-block rounded-2xl bg-[#0f2a18] backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
                <nav class="text-xs text-[#94a3b8] mb-4">
                    <a href="{{ route('restaurant.index') }}" class="hover:text-[#fb923c] transition">Restaurants &amp; Gastronomie</a>
                    <span class="mx-2 text-[#475569]">/</span>
                    <a href="{{ route('restaurant.index', ['ville' => $restaurant->city_id]) }}" class="hover:text-[#fb923c] transition">{{ $restaurant->city?->name }}</a>
                    <span class="mx-2 text-[#475569]">/</span>
                    <span class="text-[#ffffff]">Notre carte</span>
                </nav>
                <p class="text-[#ffffff] text-sm font-medium uppercase tracking-widest mb-3">{{ $restaurant->name }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-[#ffffff] mb-4">
                    Notre carte
                </h1>
                <p class="text-[#e5e7eb] text-xl max-w-2xl mx-auto">
                    <i class="fas fa-location-dot mr-1"></i>{{ $restaurant->city?->name }}
                    — Choisissez un plat pour poursuivre votre échange avec l'établissement.
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

        @if($menuByCategory->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-8 items-start">

            {{-- ═══ SIDEBAR FILTRE PAR CATÉGORIE ═══ --}}
            <div class="filter-sidebar rounded-2xl p-4 lg:sticky lg:top-24">
                <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#8a7f6b] px-2 mb-3">Filtrer par catégorie</p>
                <div id="filter-list" class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-1">
                    <button type="button" data-filter="all" class="filter-btn is-active shrink-0">
                        <span class="filter-icon"><i class="fas fa-border-all text-sm"></i></span>
                        <span class="filter-label">Tous les plats</span>
                        <span class="filter-count">{{ $menuByCategory->flatten(1)->count() }}</span>
                    </button>
                    @foreach($menuByCategory as $categoryName => $items)
                    @php $catSlug = $items->first()->category->slug ?? \Illuminate\Support\Str::slug($categoryName); @endphp
                    <button type="button" data-filter="{{ $catSlug }}" class="filter-btn shrink-0">
                        <span class="filter-icon"><i class="{{ $items->first()->category->icon ?? 'fas fa-utensils' }} text-sm"></i></span>
                        <span class="filter-label">{{ $categoryName }}</span>
                        <span class="filter-count">{{ $items->count() }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- ═══ PLATS ═══ --}}
            <div class="space-y-10">
            @foreach($menuByCategory as $categoryName => $items)
            @php $catSlug = $items->first()->category->slug ?? \Illuminate\Support\Str::slug($categoryName); @endphp
            <div class="menu-category-block" data-cat="{{ $catSlug }}">
                <h2 class="text-[#27AE60] text-sm font-bold uppercase tracking-wide mb-4">{{ $categoryName }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    @foreach($items as $item)
                    <div class="dish-card rounded-2xl overflow-hidden flex flex-col">
                        <div class="dish-cover h-60 @if(empty($item->images[0])) dish-cover-placeholder @endif" @if(!empty($item->images[0])) onclick="openLightbox('{{ $item->images[0] }}')" @endif>
                            @if(!empty($item->images[0]))
                                <img src="{{ $item->images[0] }}" alt="{{ $item->name }}" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white/20"><i class="fas fa-utensils text-4xl"></i></div>
                            @endif
                        </div>
                        <div class="p-6 flex flex-col flex-1">
                            <div class="flex items-baseline">
                                <p class="font-serif font-bold text-xl leading-snug shrink-0">{{ $item->name }}</p>
                                <span class="dish-name-line"></span>
                                <p class="text-[#f2790f] font-extrabold text-lg whitespace-nowrap shrink-0">{{ number_format((int) $item->price_xof, 0, ',', ' ') }}<span class="text-xs font-semibold ml-0.5">XOF</span></p>
                            </div>
                            @if($item->description)
                            <p class="text-[#8a7f6b] text-base mt-3 italic leading-relaxed">{{ $item->description }}</p>
                            @endif
                            <div class="flex-1"></div>
                            <a href="{{ route('restaurant.show', $restaurant->slug) }}?item={{ urlencode($item->name) }}#contact-card"
                               class="dish-link inline-flex items-center gap-1.5 mt-4 text-[#27AE60] text-sm font-bold self-start">
                                <i class="fas fa-comment-dots text-sm"></i> Contacter à ce sujet
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            </div>
        </div>
        @else
        <div class="dish-card rounded-2xl py-16 text-center">
            <i class="fas fa-utensils text-3xl text-[#c9bfa8] mb-3 block"></i>
            <p class="text-[#8a7f6b] text-sm">Aucun plat renseigné pour cet établissement pour le moment.</p>
        </div>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

@include('partials.homepage-footer')
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

(function () {
    const buttons = document.querySelectorAll('.filter-btn');
    const blocks = document.querySelectorAll('.menu-category-block');
    if (!buttons.length) return;

    buttons.forEach(function (btn) {
        btn.addEventListener('click', function () {
            const filter = btn.getAttribute('data-filter');

            buttons.forEach(function (b) { b.classList.remove('is-active'); });
            btn.classList.add('is-active');

            blocks.forEach(function (block) {
                const show = filter === 'all' || block.getAttribute('data-cat') === filter;
                block.classList.toggle('is-hidden', !show);
            });
        });
    });
})();
</script>
</body>
</html>
