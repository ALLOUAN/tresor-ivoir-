<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .article-card:hover .card-img { transform: scale(1.05); }
        .card-img { transition: transform .5s ease; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

{{-- ── Mini header ──────────────────────────────────────────────── --}}
<header class="bg-[#0d0d0b]/95 backdrop-blur border-b border-white/8 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-16 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2.5 shrink-0">
            <div class="w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center">
                <i class="fas fa-gem text-black text-sm"></i>
            </div>
            <span class="font-serif font-bold text-amber-400 hidden sm:block">{{ $siteBrand['site_name'] }}</span>
        </a>
        <nav class="hidden md:flex items-center gap-1 text-sm">
            <a href="/" class="px-3 py-2 text-gray-400 hover:text-white transition">Accueil</a>
            <a href="{{ route('articles.index') }}" class="px-3 py-2 text-amber-400 font-medium">Articles</a>
            <a href="#" class="px-3 py-2 text-gray-400 hover:text-white transition">Découvertes</a>
            <a href="#" class="px-3 py-2 text-gray-400 hover:text-white transition">Annuaire</a>
        </nav>
        <div class="flex items-center gap-2">
            @auth
            <a href="{{ route('dashboard') }}" class="px-3 py-1.5 border border-amber-500/40 text-amber-400 text-xs rounded-lg hover:border-amber-400 transition">
                <i class="fas fa-gauge-high mr-1"></i>Dashboard
            </a>
            @else
            <a href="{{ route('login') }}" class="px-4 py-1.5 bg-amber-500 hover:bg-amber-400 text-black text-xs font-bold rounded-lg transition">Connexion</a>
            @endauth
        </div>
    </div>
</header>

{{-- ── Page header ──────────────────────────────────────────────── --}}
<div class="bg-[#141410] border-b border-white/5">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-6">
            <div>
                <p class="text-amber-400 text-xs tracking-[.25em] uppercase mb-2">Magazine</p>
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
            <form method="GET" action="{{ route('articles.index') }}" class="flex gap-2 w-full sm:w-auto">
                <input name="q" value="{{ $search }}" placeholder="Rechercher…"
                    class="flex-1 sm:w-64 bg-[#1c1c16] border border-white/10 focus:border-amber-400/40 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition">
                <button class="px-4 py-2.5 bg-amber-500 hover:bg-amber-400 text-black rounded-xl transition">
                    <i class="fas fa-search text-sm"></i>
                </button>
            </form>
        </div>

        {{-- Category tabs --}}
        <div class="flex items-center gap-2 overflow-x-auto mt-6 pb-1">
            <a href="{{ route('articles.index') }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium transition {{ !$active_category && !$search ? 'bg-amber-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/8 hover:text-white' }}">
                Tous
            </a>
            @foreach($categories as $cat)
            <a href="{{ route('articles.index', ['categorie' => $cat->slug]) }}"
               class="shrink-0 px-4 py-1.5 rounded-full text-xs font-medium transition {{ $active_category?->id === $cat->id ? 'bg-amber-500 text-black' : 'bg-white/5 text-gray-400 hover:bg-white/8 hover:text-white' }}">
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
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @forelse($articles as $article)
        <a href="{{ route('articles.show', $article->slug_fr) }}"
           class="article-card group bg-[#141410] border border-white/5 hover:border-amber-500/25 rounded-2xl overflow-hidden transition-all duration-300">
            <div class="h-44 overflow-hidden relative">
                @if($article->cover_url)
                <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt }}"
                     class="card-img w-full h-full object-cover">
                @else
                <div class="card-img w-full h-full bg-[#1c1c16] flex items-center justify-center">
                    <i class="fas fa-image text-[#2a2a24] text-2xl"></i>
                </div>
                @endif
                @if($article->is_featured)
                <span class="absolute top-2 left-2 bg-amber-500 text-black text-[9px] font-bold px-2 py-0.5 rounded-full uppercase">À la une</span>
                @endif
                @if($article->is_sponsored)
                <span class="absolute top-2 right-2 bg-white/10 backdrop-blur text-white text-[9px] px-2 py-0.5 rounded-full">Sponsorisé</span>
                @endif
            </div>
            <div class="p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-amber-400/80 text-[11px] uppercase tracking-wider font-medium">{{ $article->category->name_fr ?? '—' }}</span>
                    @if($article->reading_time)
                    <span class="text-gray-600 text-[11px]"><i class="fas fa-clock mr-0.5"></i>{{ $article->reading_time }} min</span>
                    @endif
                </div>
                <h3 class="font-serif text-sm font-semibold line-clamp-2 group-hover:text-amber-300 transition leading-snug">
                    {{ $article->title_fr }}
                </h3>
                @if($article->excerpt_fr)
                <p class="text-gray-500 text-xs mt-2 line-clamp-2 leading-relaxed">{{ $article->excerpt_fr }}</p>
                @endif
                <div class="flex items-center justify-between mt-3 pt-3 border-t border-white/5">
                    <span class="text-gray-600 text-xs">{{ $article->published_at?->format('d M Y') }}</span>
                    <span class="text-gray-600 text-xs"><i class="fas fa-eye mr-0.5"></i>{{ number_format($article->views_count) }}</span>
                </div>
            </div>
        </a>
        @empty
        <div class="col-span-full py-20 text-center text-gray-600">
            <i class="fas fa-newspaper text-4xl mb-3 block text-[#1c1c16]"></i>
            <p class="text-lg font-serif">Aucun article trouvé</p>
            <p class="text-sm mt-1">Essayez une autre recherche ou catégorie.</p>
        </div>
        @endforelse
    </div>

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

{{-- Footer minimal --}}
<footer class="border-t border-white/5 bg-[#141410] py-6 mt-10">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-gray-600">
        <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
        <a href="{{ route('login') }}" class="hover:text-amber-400 transition">Espace membres</a>
    </div>
</footer>

</body>
</html>
