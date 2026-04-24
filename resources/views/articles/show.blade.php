<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->meta_title_fr ?: $article->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    @if($article->meta_desc_fr)
    <meta name="description" content="{{ $article->meta_desc_fr }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700;1,400&family=Inter:wght@300;400;500;600&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .prose-content { font-family: 'Lora', serif; font-size: 1.0625rem; line-height: 1.85; color: #d1cfc8; }
        .prose-content p { margin-bottom: 1.5rem; }
        .prose-content h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 700; color: #fff; margin: 2.5rem 0 1rem; }
        .prose-content h3 { font-family: 'Playfair Display', serif; font-size: 1.2rem; font-weight: 600; color: #e5e3db; margin: 2rem 0 0.75rem; }
        .prose-content blockquote { border-left: 3px solid #e8a020; padding-left: 1.25rem; margin: 2rem 0; color: #b5b2a8; font-style: italic; }
        .prose-content ul { list-style: none; padding: 0; margin-bottom: 1.5rem; }
        .prose-content ul li::before { content: '—'; color: #e8a020; margin-right: .6rem; }
        .prose-content a { color: #f5b942; text-decoration: underline; }
        .prose-content strong { color: #fff; font-weight: 600; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

{{-- Header --}}
<header class="bg-[#0d0d0b]/95 backdrop-blur border-b border-white/8 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
        <a href="/" class="flex items-center gap-2 shrink-0">
            <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center">
                <i class="fas fa-gem text-black text-xs"></i>
            </div>
            <span class="font-serif font-bold text-amber-400 text-sm hidden sm:block">{{ $siteBrand['site_name'] }}</span>
        </a>
        <div class="flex items-center gap-2 text-xs text-gray-500">
            <a href="{{ route('articles.index') }}" class="hover:text-amber-400 transition">Articles</a>
            <i class="fas fa-chevron-right text-[9px]"></i>
            <span class="text-gray-400 truncate max-w-[200px]">{{ $article->category->name_fr ?? '—' }}</span>
        </div>
        @auth
        <a href="{{ route('dashboard') }}" class="shrink-0 px-3 py-1.5 border border-amber-500/40 text-amber-400 text-xs rounded-lg hover:border-amber-400 transition">Dashboard</a>
        @else
        <a href="{{ route('login') }}" class="shrink-0 px-3 py-1.5 bg-amber-500 text-black text-xs font-bold rounded-lg hover:bg-amber-400 transition">Connexion</a>
        @endauth
    </div>
</header>

<article class="max-w-4xl mx-auto px-4 sm:px-6 py-10 sm:py-14">

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-2 text-xs text-gray-600 mb-8">
        <a href="/" class="hover:text-amber-400 transition">Accueil</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <a href="{{ route('articles.index') }}" class="hover:text-amber-400 transition">Articles</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <a href="{{ route('articles.index', ['categorie' => $article->category->slug ?? '']) }}"
           class="hover:text-amber-400 transition">{{ $article->category->name_fr ?? '—' }}</a>
    </nav>

    {{-- Category + badges --}}
    <div class="flex flex-wrap items-center gap-2 mb-5">
        <a href="{{ route('articles.index', ['categorie' => $article->category->slug ?? '']) }}"
           class="px-3 py-1 bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-semibold rounded-full hover:bg-amber-500/15 transition uppercase tracking-wide">
            {{ $article->category->name_fr ?? '—' }}
        </a>
        @if($article->is_featured)
        <span class="px-3 py-1 bg-amber-500 text-black text-xs font-bold rounded-full uppercase tracking-wide">
            <i class="fas fa-star mr-1 text-[9px]"></i>À la une
        </span>
        @endif
        @if($article->is_destination)
        <span class="px-3 py-1 bg-blue-900/40 border border-blue-700/40 text-blue-300 text-xs rounded-full">Destination</span>
        @endif
        @if($article->is_sponsored)
        <span class="px-3 py-1 bg-white/5 border border-white/10 text-gray-400 text-xs rounded-full">Sponsorisé</span>
        @endif
    </div>

    {{-- Title --}}
    <h1 class="font-serif text-3xl sm:text-4xl md:text-5xl font-bold leading-[1.15] mb-5">
        {{ $article->title_fr }}
    </h1>

    {{-- Excerpt --}}
    @if($article->excerpt_fr)
    <p class="text-gray-300 text-lg sm:text-xl font-light leading-relaxed mb-6 border-l-2 border-amber-500/40 pl-4">
        {{ $article->excerpt_fr }}
    </p>
    @endif

    {{-- Meta --}}
    <div class="flex flex-wrap items-center gap-4 sm:gap-6 py-5 border-y border-white/8 mb-8 text-sm">
        <div class="flex items-center gap-2">
            <div class="w-8 h-8 rounded-full bg-[#252520] flex items-center justify-center text-xs font-bold text-amber-400">
                {{ strtoupper(substr($article->author->first_name ?? '?', 0, 1)) }}
            </div>
            <div>
                <p class="text-white font-medium text-sm">{{ $article->author->full_name ?? 'Rédaction' }}</p>
                <p class="text-gray-600 text-xs">Auteur</p>
            </div>
        </div>
        @if($article->published_at)
        <div class="text-gray-500 text-sm">
            <i class="fas fa-calendar-days text-amber-500/50 mr-1.5"></i>
            {{ $article->published_at->translatedFormat('d F Y') }}
        </div>
        @endif
        @if($article->reading_time)
        <div class="text-gray-500 text-sm">
            <i class="fas fa-clock text-amber-500/50 mr-1.5"></i>
            {{ $article->reading_time }} min de lecture
        </div>
        @endif
        <div class="text-gray-500 text-sm">
            <i class="fas fa-eye text-amber-500/50 mr-1.5"></i>
            {{ number_format($article->views_count) }} vues
        </div>

        {{-- Share --}}
        <div class="ml-auto flex items-center gap-2">
            <span class="text-gray-600 text-xs">Partager :</span>
            <a href="#" class="w-7 h-7 rounded-full bg-[#1c1c16] hover:bg-blue-900/50 flex items-center justify-center text-gray-400 hover:text-blue-300 transition text-xs">
                <i class="fab fa-facebook-f"></i>
            </a>
            <a href="#" class="w-7 h-7 rounded-full bg-[#1c1c16] hover:bg-sky-900/50 flex items-center justify-center text-gray-400 hover:text-sky-300 transition text-xs">
                <i class="fab fa-twitter"></i>
            </a>
            <button onclick="navigator.clipboard.writeText(window.location.href)"
                class="w-7 h-7 rounded-full bg-[#1c1c16] hover:bg-amber-900/30 flex items-center justify-center text-gray-400 hover:text-amber-400 transition text-xs" title="Copier le lien">
                <i class="fas fa-link"></i>
            </button>
        </div>
    </div>

    {{-- Cover image --}}
    @if($article->cover_url)
    <figure class="mb-10 rounded-2xl overflow-hidden">
        <img src="{{ $article->cover_url }}" alt="{{ $article->cover_alt ?: $article->title_fr }}"
             class="w-full max-h-[500px] object-cover">
        @if($article->cover_alt)
        <figcaption class="text-center text-xs text-gray-600 mt-2 italic">{{ $article->cover_alt }}</figcaption>
        @endif
    </figure>
    @else
    <div class="mb-10 rounded-2xl bg-[#1c1c16] h-64 flex items-center justify-center">
        <i class="fas fa-image text-4xl text-[#2a2a24]"></i>
    </div>
    @endif

    {{-- Content --}}
    @if($article->content_fr)
    <div class="prose-content max-w-none">
        {!! nl2br(e($article->content_fr)) !!}
    </div>
    @endif

    {{-- Tags --}}
    @if($article->tags->isNotEmpty())
    <div class="flex flex-wrap items-center gap-2 mt-10 pt-8 border-t border-white/8">
        <span class="text-gray-600 text-xs font-medium uppercase tracking-wider mr-1">Tags :</span>
        @foreach($article->tags as $tag)
        <a href="{{ route('articles.index', ['tag' => $tag->slug]) }}"
           class="px-3 py-1 bg-[#1c1c16] border border-white/8 hover:border-amber-500/30 hover:text-amber-400 text-gray-400 text-xs rounded-full transition">
            #{{ $tag->name_fr }}
        </a>
        @endforeach
    </div>
    @endif

    {{-- Author card --}}
    <div class="mt-10 p-5 bg-[#141410] border border-white/8 rounded-2xl flex items-start gap-4">
        <div class="w-12 h-12 rounded-full bg-[#252520] flex items-center justify-center text-lg font-bold text-amber-400 shrink-0">
            {{ strtoupper(substr($article->author->first_name ?? '?', 0, 1)) }}
        </div>
        <div>
            <p class="text-white font-semibold font-serif">{{ $article->author->full_name ?? 'Rédaction '.$siteBrand['site_name'] }}</p>
            <p class="text-gray-500 text-xs mt-0.5 mb-2">Contributeur · {{ $siteBrand['site_name'] }}</p>
            <p class="text-gray-500 text-sm">Passionné de culture et de tourisme ivoirien, notre équipe éditoriale explore et documente les richesses de la Côte d'Ivoire.</p>
        </div>
    </div>
</article>

{{-- Related articles --}}
@if($related->isNotEmpty())
<section class="border-t border-white/5 bg-[#141410]">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <h2 class="font-serif text-xl font-bold mb-6">Dans la même rubrique</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-5">
            @foreach($related as $rel)
            <a href="{{ route('articles.show', $rel->slug_fr) }}"
               class="group bg-[#0d0d0b] border border-white/5 hover:border-amber-500/20 rounded-xl overflow-hidden transition-all duration-300">
                <div class="h-36 bg-[#1c1c16] overflow-hidden">
                    @if($rel->cover_url)
                    <img src="{{ $rel->cover_url }}" alt="{{ $rel->cover_alt }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    @else
                    <div class="w-full h-full flex items-center justify-center"><i class="fas fa-image text-[#2a2a24] text-xl"></i></div>
                    @endif
                </div>
                <div class="p-4">
                    <span class="text-amber-400/70 text-[10px] uppercase tracking-wider">{{ $rel->category->name_fr ?? '—' }}</span>
                    <h3 class="font-serif text-sm font-semibold mt-1 line-clamp-2 group-hover:text-amber-300 transition leading-snug">{{ $rel->title_fr }}</h3>
                    <p class="text-gray-600 text-xs mt-2">{{ $rel->published_at?->format('d M Y') }}</p>
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>
@endif

<footer class="border-t border-white/5 bg-[#0d0d0b] py-6">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 flex items-center justify-between text-xs text-gray-600">
        <span>&copy; {{ date('Y') }} {{ $siteBrand['site_name'] }}</span>
        <a href="{{ route('articles.index') }}" class="hover:text-amber-400 transition">← Retour aux articles</a>
    </div>
</footer>

</body>
</html>
