<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nos circuits — {{ $agency->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary {
            background: #27AE60;
            box-shadow: 0 12px 28px rgba(39,174,96,0.3);
            transition: transform .22s ease, filter .22s ease;
            color: #fff;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .section-kicker { letter-spacing: .22em; }
        .item-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .item-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(41,128,185,0.14); border-color: rgba(41,128,185,0.3); }
        .item-cover { position: relative; overflow: hidden; background: #14130f; cursor: zoom-in; }
        .item-cover img { transition: transform .5s ease; }
        .item-card:hover .item-cover img { transform: scale(1.06); }

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

        .tour-category-block { transition: opacity .25s ease; }
        .tour-category-block.is-hidden { display: none; }

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
    <section class="relative py-20 overflow-hidden {{ $agency->thumbnail ? 'category-hero' : '' }}"
        @if($agency->thumbnail)
            style="--category-hero-bg-image: url('{{ $agency->thumbnail }}');"
        @endif
    >
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="inline-block rounded-2xl bg-[#0f2a18] backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
                <nav class="text-xs text-[#94a3b8] mb-4">
                    <a href="{{ route('travel-agency.index') }}" class="hover:text-[#5dade2] transition">Agences de Voyages &amp; Tours</a>
                    <span class="mx-2 text-[#475569]">/</span>
                    <a href="{{ route('travel-agency.index', ['ville' => $agency->city_id]) }}" class="hover:text-[#5dade2] transition">{{ $agency->city?->name }}</a>
                    <span class="mx-2 text-[#475569]">/</span>
                    <span class="text-[#ffffff]">Nos circuits</span>
                </nav>
                <p class="text-[#ffffff] text-sm font-medium uppercase tracking-widest mb-3">{{ $agency->name }}</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-[#ffffff] mb-4">
                    Nos circuits
                </h1>
                <p class="text-[#e5e7eb] text-xl max-w-2xl mx-auto">
                    <i class="fas fa-location-dot mr-1"></i>{{ $agency->city?->name }}
                    — Choisissez un circuit pour poursuivre votre échange avec l'agence.
                </p>
            </div>
        </div>
    </section>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-8 sm:py-12">

        @if($toursByCategory->isNotEmpty())
        <div class="grid grid-cols-1 lg:grid-cols-[240px_1fr] gap-8 items-start">

            {{-- ═══ SIDEBAR FILTRE PAR CATÉGORIE ═══ --}}
            <div class="filter-sidebar rounded-2xl p-4 lg:sticky lg:top-24">
                <p class="text-[10px] font-bold uppercase tracking-[.18em] text-[#8a7f6b] px-2 mb-3">Filtrer par catégorie</p>
                <div id="filter-list" class="flex lg:flex-col gap-2 overflow-x-auto lg:overflow-visible pb-1">
                    <button type="button" data-filter="all" class="filter-btn is-active shrink-0">
                        <span class="filter-icon"><i class="fas fa-border-all text-sm"></i></span>
                        <span class="filter-label">Tous les circuits</span>
                        <span class="filter-count">{{ $toursByCategory->flatten(1)->count() }}</span>
                    </button>
                    @foreach($toursByCategory as $categoryName => $tours)
                    @php $catSlug = $tours->first()->category->slug ?? \Illuminate\Support\Str::slug($categoryName); @endphp
                    <button type="button" data-filter="{{ $catSlug }}" class="filter-btn shrink-0">
                        <span class="filter-icon"><i class="{{ $tours->first()->category->icon ?? 'fas fa-route' }} text-sm"></i></span>
                        <span class="filter-label">{{ $categoryName }}</span>
                        <span class="filter-count">{{ $tours->count() }}</span>
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- ═══ CIRCUITS ═══ --}}
            <div class="space-y-10">
            @foreach($toursByCategory as $categoryName => $tours)
            @php $catSlug = $tours->first()->category->slug ?? \Illuminate\Support\Str::slug($categoryName); @endphp
            <div class="tour-category-block" data-cat="{{ $catSlug }}">
                <h2 class="text-[#27AE60] text-sm font-bold uppercase tracking-wide mb-4">{{ $categoryName }}</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    @foreach($tours as $tour)
                    <div class="item-card rounded-2xl overflow-hidden flex flex-col">
                        <div class="item-cover h-40" @if(!empty($tour->images[0])) onclick="openLightbox('{{ $tour->images[0] }}')" @endif>
                            @if(!empty($tour->images[0]))
                                <img src="{{ $tour->images[0] }}" alt="{{ $tour->name }}" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-route text-3xl"></i></div>
                            @endif
                            <div class="absolute inset-0 bg-linear-to-t from-black/50 via-transparent to-transparent"></div>
                            <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3 py-1 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                                {{ number_format((int) $tour->price_xof, 0, ',', ' ') }} XOF
                            </span>
                        </div>
                        <div class="p-4 flex flex-col flex-1">
                            <p class="font-serif font-bold text-lg leading-snug">{{ $tour->name }}</p>
                            <p class="text-[#8a7f6b] text-xs mt-1.5">
                                @if($tour->duration_days) <i class="fas fa-calendar-days mr-0.5"></i>{{ $tour->duration_days }} jour(s) @endif
                                @if($tour->duration_days && $tour->max_participants) · @endif
                                @if($tour->max_participants) <i class="fas fa-user mr-0.5"></i>{{ $tour->max_participants }} pers. max @endif
                            </p>
                            @if($tour->description)
                            <p class="text-[#5c5548] text-sm mt-2 leading-relaxed">{{ $tour->description }}</p>
                            @endif
                            <div class="flex-1"></div>
                            <a href="{{ route('travel-agency.show', $agency->slug) }}?item={{ urlencode($tour->name) }}#contact-card"
                               class="btn-primary btn-shine mt-4 inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl font-extrabold text-sm">
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
        <div class="item-card rounded-2xl py-16 text-center">
            <i class="fas fa-route text-3xl text-[#c9bfa8] mb-3 block"></i>
            <p class="text-[#8a7f6b] text-sm">Aucun circuit renseigné pour cette agence pour le moment.</p>
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
    const blocks = document.querySelectorAll('.tour-category-block');
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
