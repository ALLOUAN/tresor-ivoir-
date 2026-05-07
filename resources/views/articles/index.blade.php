<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Cormorant+Garamond:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        serif:   ['Playfair Display', 'Georgia', 'serif'],
                        elegant: ['Cormorant Garamond', 'Georgia', 'serif'],
                    },
                    colors: {
                        gold: { 300:'#fcd68a', 400:'#f5b942', 500:'#e8a020', 600:'#c4811a' },
                        dark: { 500:'#2e2e26', 600:'#252520', 700:'#1c1c16', 800:'#141410', 900:'#0d0d0b' },
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif   { font-family: 'Playfair Display', Georgia, serif; }
        .font-elegant { font-family: 'Cormorant Garamond', Georgia, serif; }
        .article-card:hover .card-img { transform: scale(1.05); }
        .card-img { transition: transform .5s ease; }
        .reveal { opacity: 0; transform: translateY(20px); transition: opacity .6s ease, transform .6s ease; }
        .reveal.visible { opacity: 1; transform: translateY(0); }
        .agenda-panel {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            background:
                linear-gradient(140deg, rgba(20,20,16,0.94), rgba(12,12,10,0.97)),
                radial-gradient(100% 70% at 0% 0%, rgba(232,160,32,0.12), transparent 55%);
            box-shadow: 0 24px 52px rgba(0,0,0,0.35), inset 0 1px 0 rgba(255,255,255,0.05);
            overflow: hidden;
        }
        .agenda-panel::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, rgba(232,160,32,0.08), transparent 35%, rgba(255,255,255,0.02));
            pointer-events: none;
        }
        .agenda-card {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            background: linear-gradient(180deg, rgba(26,26,21,0.95), rgba(14,14,11,0.96));
            box-shadow: 0 12px 30px rgba(0,0,0,0.28);
            transition: transform .26s cubic-bezier(0.2, 0.8, 0.2, 1), border-color .25s ease, box-shadow .26s ease;
            overflow: hidden;
        }
        .agenda-card:hover {
            transform: translateY(-4px);
            border-color: rgba(232,160,32,0.45);
            box-shadow: 0 18px 42px rgba(0,0,0,0.4), 0 0 22px rgba(232,160,32,0.14);
        }
        .agenda-card-cover {
            position: relative;
            overflow: hidden;
            background: #161612;
        }
        .agenda-card-cover::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to top, rgba(0,0,0,0.65), rgba(0,0,0,0.1) 45%, rgba(0,0,0,0.2));
            pointer-events: none;
        }
        .agenda-card:hover .agenda-card-cover img { transform: scale(1.07); }
        .agenda-card-cover img { transition: transform .5s ease; }
        .articles-hero {
            position: relative;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            background:
                linear-gradient(130deg, rgba(20,20,16,0.92), rgba(13,13,11,0.97)),
                radial-gradient(110% 90% at 0% 0%, rgba(232,160,32,0.14), transparent 55%),
                radial-gradient(90% 80% at 100% 10%, rgba(120,90,40,0.08), transparent 50%);
            overflow: hidden;
        }
        .articles-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                linear-gradient(120deg, rgba(255,255,255,0.03), transparent 35%, rgba(255,255,255,0.015)),
                repeating-linear-gradient(90deg, transparent 0, transparent 62px, rgba(232,160,32,0.02) 62px, rgba(232,160,32,0.02) 63px);
            pointer-events: none;
            opacity: .8;
        }
        .articles-search-shell {
            border: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.02));
            backdrop-filter: blur(12px) saturate(125%);
            box-shadow: 0 10px 26px rgba(0,0,0,0.25), inset 0 1px 0 rgba(255,255,255,0.06);
        }
        .articles-search-input {
            border: 1px solid rgba(255,255,255,0.1);
            background: rgba(28,28,22,0.88);
            transition: border-color .2s ease, box-shadow .2s ease;
        }
        .articles-search-input:focus {
            border-color: rgba(232,160,32,0.5);
            box-shadow: 0 0 0 3px rgba(232,160,32,0.14);
        }
        .articles-search-btn {
            background: linear-gradient(135deg, #f5b942 0%, #e8a020 55%, #c4811a 100%);
            box-shadow: 0 8px 20px rgba(232,160,32,0.28);
            transition: transform .2s ease, filter .2s ease, box-shadow .2s ease;
        }
        .articles-search-btn:hover {
            transform: translateY(-1px);
            filter: brightness(1.04);
            box-shadow: 0 12px 26px rgba(232,160,32,0.35);
        }
        .category-chip {
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.1);
            background: linear-gradient(135deg, rgba(255,255,255,0.06), rgba(255,255,255,0.02));
            color: rgba(203, 213, 225, 0.9);
            transition: transform .2s ease, border-color .2s ease, background-color .2s ease, color .2s ease, box-shadow .2s ease;
        }
        .category-chip::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(120deg, transparent, rgba(255,255,255,0.16), transparent);
            transform: translateX(-130%);
            transition: transform .45s ease;
            pointer-events: none;
        }
        .category-chip::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: 0;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            border-radius: 2px;
            background: linear-gradient(90deg, #c4811a, #f5b942, #e8a020);
            transition: width .24s ease;
            box-shadow: 0 0 10px rgba(232,160,32,0.45);
        }
        .category-chip:hover {
            transform: translateY(-1px);
            color: #ffffff;
            border-color: rgba(232,160,32,0.3);
            background: linear-gradient(135deg, rgba(255,255,255,0.1), rgba(255,255,255,0.03));
            box-shadow: 0 0 14px rgba(232,160,32,0.14);
        }
        .category-chip:hover::before {
            transform: translateX(130%);
        }
        .category-chip:hover::after {
            width: 72%;
        }
        .category-chip.is-active {
            border-color: rgba(232,160,32,0.52);
            background: linear-gradient(135deg, #f5b942, #e8a020);
            color: #090705;
            box-shadow: 0 8px 20px rgba(232,160,32,0.3);
        }
        .category-chip.is-active::after {
            width: 76%;
            background: rgba(9,7,5,0.8);
            box-shadow: none;
        }
        .categories-strip {
            position: relative;
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 9999px;
            background: rgba(8,8,7,0.38);
            backdrop-filter: blur(10px) saturate(120%);
            padding: 0.45rem;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .categories-strip::-webkit-scrollbar {
            display: none;
        }
        .footer-ultra {
            position: relative;
            background-color: #060504;
            background-image:
                radial-gradient(ellipse 90% 50% at 50% -20%, rgba(232, 160, 32, 0.09), transparent 55%),
                radial-gradient(ellipse 50% 40% at 100% 100%, rgba(99, 102, 241, 0.05), transparent 45%);
        }
        .footer-ultra::before {
            content: '';
            position: absolute;
            inset: 0;
            background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='fn'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.85' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23fn)' opacity='0.03'/%3E%3C/svg%3E");
            pointer-events: none;
            opacity: 0.9;
        }
        .footer-ultra-inner { position: relative; z-index: 1; }
        .footer-v2-link {
            display: block;
            padding: 0.32rem 0;
            font-size: 0.8125rem;
            color: rgba(163, 163, 163, 0.92);
            transition: color .18s ease, padding-left .18s ease;
        }
        .footer-v2-link:hover {
            color: #fde68a;
            padding-left: 0.35rem;
        }
        .social-links-wrap {
            position: relative;
        }
        .social-icon-ultra {
            position: relative;
            overflow: hidden;
            border-radius: 0.62rem;
            backdrop-filter: blur(8px);
            transition: transform .28s cubic-bezier(0.2, 0.8, 0.2, 1), color .25s ease, border-color .25s ease, box-shadow .3s ease, background-color .25s ease;
            isolation: isolate;
        }
        .social-icon-ultra::before {
            content: '';
            position: absolute;
            inset: -1px;
            border-radius: inherit;
            background: conic-gradient(from 180deg, rgba(232,160,32,0.0), rgba(232,160,32,0.45), rgba(255,255,255,0.16), rgba(232,160,32,0.0));
            opacity: 0;
            transform: rotate(0deg);
            transition: opacity .28s ease;
            pointer-events: none;
            z-index: 0;
        }
        .social-icon-ultra::after {
            content: '';
            position: absolute;
            inset: 1px;
            border-radius: calc(0.62rem - 1px);
            background: linear-gradient(180deg, rgba(255,255,255,0.08), rgba(255,255,255,0.01));
            opacity: 0;
            transition: opacity .25s ease;
            z-index: 0;
        }
        .social-icon-ultra i {
            position: relative;
            z-index: 1;
            transition: transform .25s ease;
        }
        .social-icon-ultra:hover {
            transform: translateY(-3px) scale(1.06);
            box-shadow: 0 10px 28px rgba(0,0,0,0.35), 0 0 18px rgba(232,160,32,0.22);
        }
        .social-icon-ultra:hover::before {
            opacity: 0.9;
            animation: socialRingSpin 1.2s linear infinite;
        }
        .social-icon-ultra:hover::after {
            opacity: 1;
        }
        .social-icon-ultra:hover i {
            transform: scale(1.1);
        }
        .social-icon-ultra.social-icon-wa:hover {
            box-shadow: 0 10px 28px rgba(0,0,0,0.35), 0 0 18px rgba(16,185,129,0.28);
        }
        .social-icon-ultra.social-icon-wa::before {
            background: conic-gradient(from 180deg, rgba(16,185,129,0), rgba(16,185,129,0.6), rgba(236,253,245,0.25), rgba(16,185,129,0));
        }
        .footer-logo-link {
            display: inline-flex;
            align-items: flex-start;
            gap: 0.75rem;
        }
        .footer-logo-ring {
            position: relative;
            border-radius: 0.7rem;
            padding: 2px;
            background: linear-gradient(135deg, rgba(232,160,32,0.55), rgba(255,255,255,0.14), rgba(232,160,32,0.25));
            box-shadow: 0 8px 26px rgba(0,0,0,0.35), 0 0 0 1px rgba(255,255,255,0.05) inset;
            transition: transform .32s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow .32s ease;
            animation: footerLogoFloat 5.4s ease-in-out infinite;
        }
        .footer-logo-ring::before {
            content: '';
            position: absolute;
            inset: -3px;
            border-radius: inherit;
            background: conic-gradient(from 180deg, rgba(232,160,32,0), rgba(232,160,32,0.45), rgba(255,255,255,0.1), rgba(232,160,32,0));
            opacity: 0.4;
            filter: blur(5px);
            animation: footerLogoSpin 8s linear infinite;
            pointer-events: none;
        }
        .footer-logo-inner {
            border-radius: calc(0.7rem - 2px);
            overflow: hidden;
            background: rgba(13,13,11,0.9);
            transition: transform .32s cubic-bezier(0.2, 0.8, 0.2, 1), filter .32s ease;
        }
        .footer-logo-link:hover .footer-logo-ring {
            transform: translateY(-2px) scale(1.04);
            box-shadow: 0 12px 30px rgba(0,0,0,0.42), 0 0 22px rgba(232,160,32,0.25), 0 0 0 1px rgba(255,255,255,0.08) inset;
        }
        .footer-logo-link:hover .footer-logo-ring::before {
            opacity: 0.82;
        }
        .footer-logo-link:hover .footer-logo-inner {
            transform: scale(1.04);
            filter: brightness(1.08) saturate(1.08);
        }
        @keyframes footerLogoFloat {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }
        @keyframes footerLogoSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes socialRingSpin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        html:not(.dark) .articles-hero {
            border-bottom-color: rgba(0,0,0,0.08);
            background:
                linear-gradient(130deg, rgba(255,255,255,0.95), rgba(248,244,236,0.98)),
                radial-gradient(110% 90% at 0% 0%, rgba(232,160,32,0.10), transparent 55%);
        }
        html:not(.dark) .articles-search-shell {
            border-color: rgba(0,0,0,0.08);
            background: rgba(255,255,255,0.9);
            box-shadow: 0 10px 22px rgba(0,0,0,0.06);
        }
        html:not(.dark) .articles-search-input {
            border-color: rgba(0,0,0,0.1);
            background: #ffffff;
            color: #1c1915;
        }
        html:not(.dark) .articles-search-input::placeholder { color:#7c796f; }
        html:not(.dark) .articles-hero p.text-amber-300 { color:#92400e !important; }
        html:not(.dark) .articles-hero p.text-gray-500 { color:#6b6860 !important; }
        html:not(.dark) .categories-strip {
            border-color: rgba(0,0,0,0.1);
            background: rgba(255,255,255,0.92);
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }
        html:not(.dark) .category-chip {
            border-color: rgba(0,0,0,0.12);
            background: rgba(255,255,255,0.9);
            color: #2d2a23;
        }
        html:not(.dark) .category-chip:hover {
            color: #1c1915;
            border-color: rgba(180,83,9,0.35);
            background: rgba(245,158,11,0.10);
            box-shadow: 0 0 14px rgba(180,83,9,0.12);
        }
        html:not(.dark) .category-chip.is-active {
            border-color: rgba(180,83,9,0.45);
            background: linear-gradient(135deg, #f5b942, #e8a020);
            color: #1c1915;
            box-shadow: 0 8px 18px rgba(180,83,9,0.2);
        }
        html:not(.dark) .agenda-panel {
            border-color: rgba(0,0,0,0.1);
            background:
                linear-gradient(140deg, rgba(255,255,255,0.98), rgba(247,243,235,0.98)),
                radial-gradient(100% 70% at 0% 0%, rgba(232,160,32,0.08), transparent 55%);
            box-shadow: 0 16px 30px rgba(0,0,0,0.07);
        }
        html:not(.dark) .agenda-card {
            border-color: rgba(0,0,0,0.1);
            background: linear-gradient(180deg, #ffffff, #f8f4ec);
            box-shadow: 0 12px 24px rgba(0,0,0,0.06);
        }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">
@include('partials.public-top-nav')

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="articles-hero">
    <div class="relative z-[1] max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-amber-300 text-xs tracking-[.25em] uppercase mb-2 font-semibold">Magazine</p>
                <h1 class="font-serif text-3xl sm:text-4xl font-bold">
                    @if($active_category)
                        {{ $active_category->name_fr }}
                    @elseif($search)
                        Résultats pour "{{ $search }}"
                    @else
                        Tous les articles
                    @endif
                </h1>
                <p class="text-gray-500 text-sm mt-2">{{ $articles->total() }} article{{ $articles->total() > 1 ? 's' : '' }}</p>
            </div>
            {{-- Search --}}
            <form method="GET" action="{{ route('articles.index') }}" class="articles-search-shell p-2 rounded-2xl flex gap-2 w-full sm:w-auto">
                <input name="q" value="{{ $search }}" placeholder="Rechercher…"
                    class="articles-search-input flex-1 sm:w-64 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none">
                <button class="articles-search-btn px-4 py-2.5 text-black rounded-xl">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </form>
        </div>

        {{-- Category tabs --}}
        <div class="categories-strip flex items-center gap-2.5 overflow-x-auto mt-6 pb-1">
            <a href="{{ route('articles.index') }}"
               class="category-chip {{ !$active_category && !$search ? 'is-active' : '' }} shrink-0 px-5 py-2 rounded-full text-sm font-semibold">
                Tous
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
               class="category-chip {{ $active_category?->id === $cat->id ? 'is-active' : '' }} shrink-0 px-5 py-2 rounded-full text-sm font-semibold">
                {{ $cat->name_fr }}
            </a>
            @endforeach
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

    {{-- ── Featured (if no filter) ──────────────────────────────── --}}
    @if(!$active_category && !$search && $featured->isNotEmpty())
    <div class="mb-12">
        <h2 class="font-serif text-xl font-bold mb-6 flex items-center gap-2">
            <i class="fas fa-star text-amber-400 text-base"></i> À la une
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            @foreach($featured as $i => $art)
            @php $contributors = $art->display_uploaders; @endphp
            <a href="{{ route('articles.show', $art->slug_fr) }}"
               class="article-card group {{ $i === 0 ? 'lg:col-span-2 lg:row-span-2' : '' }} block relative overflow-hidden rounded-2xl bg-[#1c1c16]">
                <div class="overflow-hidden {{ $i === 0 ? 'h-72 lg:h-full min-h-[280px]' : 'h-44' }}">
                    @if($art->cover_url)
                    <img src="{{ $art->cover_url }}" alt="{{ $art->cover_alt }}"
                         class="card-img w-full h-full object-cover">
                    @else
                    <div class="card-img w-full h-full bg-gradient-to-br from-[#1c1c16] to-[#252520] flex items-center justify-center">
                        <i class="fas fa-image text-[#333] text-3xl"></i>
                    </div>
                    @endif
                </div>
                <div class="absolute inset-0 bg-linear-to-t from-black/95 via-black/30 to-transparent"></div>
                <div class="absolute bottom-0 left-0 right-0 p-4 sm:p-5">
                    <span class="text-amber-400 text-[11px] uppercase tracking-widest">{{ $art->category->name_fr ?? '—' }}</span>
                    <h3 class="font-serif font-bold mt-1 group-hover:text-amber-300 transition leading-snug {{ $i === 0 ? 'text-lg sm:text-xl' : 'text-sm' }}">
                        {{ $art->title_fr }}
                    </h3>
                    <div class="flex items-center gap-2 text-xs text-gray-400 mt-2">
                        <span>{{ $art->author->first_name ?? 'Rédaction' }}</span>
                        <span>·</span>
                        <span>{{ $art->published_at?->diffForHumans() }}</span>
                        @if($art->reading_time)
                        <span>· {{ $art->reading_time }} min</span>
                        @endif
                    </div>
                    @if($contributors->isNotEmpty())
                    <div class="mt-2 flex flex-wrap gap-1.5">
                        @foreach($contributors->take(3) as $contributor)
                        <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-2 py-0.5 text-[10px] font-medium text-amber-200">
                            {{ $contributor->full_name }}
                        </span>
                        @endforeach
                    </div>
                    @endif
                </div>
                @if($art->is_featured)
                <span class="absolute top-3 left-3 bg-amber-500 text-black text-[10px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wide">
                    <i class="fas fa-star mr-0.5 text-[9px]"></i>À la une
                </span>
                @endif
            </a>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── Article grid ──────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        @forelse($articles as $article)
        @php $contributors = $article->display_uploaders; @endphp
        <a href="{{ route('articles.show', $article->slug_fr) }}"
           class="article-card group bg-dark-700/40 hover:bg-dark-700 border border-white/5 hover:border-gold-500/20 rounded-xl overflow-hidden transition-all duration-300 reveal">
            <div class="h-32 bg-dark-600 relative overflow-hidden">
                @if($article->cover_url)
                    <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?? $article->title_fr }}"
                         class="card-img absolute inset-0 w-full h-full object-cover">
                @else
                    <div class="card-img absolute inset-0 bg-gradient-to-br from-dark-700 to-dark-500 flex items-center justify-center">
                        <i class="fas fa-image text-dark-400 text-lg"></i>
                    </div>
                @endif
                @if($article->is_featured)
                <span class="absolute top-2 left-2 bg-gold-500 text-dark-900 text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">À la une</span>
                @endif
                @if($article->is_sponsored)
                <span class="absolute top-2 right-2 bg-white/10 backdrop-blur-sm text-white text-[9px] px-2 py-0.5 rounded-full">Sponsorisé</span>
                @endif
            </div>
            <div class="p-3">
                <span class="text-gold-400/70 text-[10px] uppercase tracking-wider font-elegant">{{ $article->category?->name_fr ?? '—' }}</span>
                <h3 class="font-serif text-xs font-semibold mt-1 line-clamp-2 group-hover:text-gold-300 transition leading-snug">
                    {{ $article->title_fr }}
                </h3>
                @if($contributors->isNotEmpty())
                <div class="mt-2 flex flex-wrap gap-1">
                    @foreach($contributors->take(2) as $contributor)
                    <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-1.5 py-0.5 text-[9px] font-medium text-amber-200">
                        {{ $contributor->first_name }}
                    </span>
                    @endforeach
                </div>
                @endif
            </div>
        </a>
        @empty
        @if(!($active_category?->slug === 'agenda' && ($agendaEvents ?? collect())->isNotEmpty()))
            <div class="col-span-full py-20 text-center text-gray-600">
                <i class="fas fa-newspaper text-4xl mb-3 block opacity-20"></i>
                <p class="text-lg font-serif">Aucun article trouvé</p>
                <p class="text-sm mt-1">Essayez une autre recherche ou catégorie.</p>
            </div>
        @endif
        @endforelse
    </div>

    @if($active_category?->slug === 'agenda')
    <section class="agenda-panel mt-10 rounded-2xl p-4 sm:p-6">
        <div class="relative z-[1]">
            <h2 class="font-serif text-xl sm:text-2xl font-bold mb-5 flex items-center gap-2.5">
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-lg border border-amber-500/30 bg-amber-500/10 text-amber-300">
                    <i class="fas fa-calendar-days text-sm"></i>
                </span>
                Tous les événements
            </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @forelse(($agendaEvents ?? collect()) as $event)
                <a href="{{ route('events.show', $event->slug) }}" class="agenda-card rounded-xl">
                    <div class="agenda-card-cover h-36">
                        @if($event->cover_url)
                            <img src="{{ $event->cover_url }}" alt="{{ $event->cover_alt ?: $event->title_fr }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center text-gray-600">
                                <i class="fas fa-calendar-days text-2xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="p-4">
                        <p class="text-amber-300 text-[11px] uppercase tracking-[0.14em]">{{ $event->category->name_fr ?? 'Événement' }}</p>
                        <h3 class="text-white font-semibold mt-1.5 leading-snug">{{ $event->title_fr }}</h3>
                        <p class="text-gray-400 text-sm mt-2">{{ $event->starts_at?->format('d/m/Y H:i') }} · {{ $event->city ?: 'Côte d\'Ivoire' }}</p>
                    </div>
                </a>
            @empty
                <div class="col-span-full rounded-xl border border-white/8 bg-[#141410] p-6 text-center text-gray-500">
                    Aucun événement publié pour le moment.
                </div>
            @endforelse
        </div>
        </div>
    </section>
    @endif

    {{-- Pagination --}}
    @if($articles->hasPages())
    <div class="flex justify-center mt-10">
        <div class="flex items-center gap-1">
            @if($articles->onFirstPage())
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] text-gray-600 text-sm cursor-not-allowed">
                <i class="fas fa-chevron-left text-xs"></i>
            </span>
            @else
            <a href="{{ $articles->previousPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] hover:bg-[#252520] text-gray-300 text-sm transition">
                <i class="fas fa-chevron-left text-xs"></i>
            </a>
            @endif

            @foreach($articles->getUrlRange(max(1, $articles->currentPage()-2), min($articles->lastPage(), $articles->currentPage()+2)) as $page => $url)
            <a href="{{ $url }}" class="w-9 h-9 flex items-center justify-center rounded-lg text-sm transition {{ $page === $articles->currentPage() ? 'bg-amber-500 text-black font-bold' : 'bg-[#1c1c16] hover:bg-[#252520] text-gray-300' }}">
                {{ $page }}
            </a>
            @endforeach

            @if($articles->hasMorePages())
            <a href="{{ $articles->nextPageUrl() }}" class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] hover:bg-[#252520] text-gray-300 text-sm transition">
                <i class="fas fa-chevron-right text-xs"></i>
            </a>
            @else
            <span class="w-9 h-9 flex items-center justify-center rounded-lg bg-[#1c1c16] text-gray-600 text-sm cursor-not-allowed">
                <i class="fas fa-chevron-right text-xs"></i>
            </span>
            @endif
        </div>
    </div>
    @endif
</div>

@include('partials.homepage-footer')

<script>
    const revealEls = document.querySelectorAll('.reveal');
    const revealObs = new IntersectionObserver((entries) => {
        entries.forEach((entry, i) => {
            if (entry.isIntersecting) {
                setTimeout(() => entry.target.classList.add('visible'), i * 60);
                revealObs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });
    revealEls.forEach(el => revealObs.observe(el));
</script>
</body>
</html>
