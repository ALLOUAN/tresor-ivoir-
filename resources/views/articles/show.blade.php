<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $article->meta_title_fr ?: $article->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
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
@include('partials.public-top-nav')

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
    @php
        $contributors = $article->display_uploaders;
    @endphp
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
        @if($contributors->isNotEmpty())
        <div class="flex items-center gap-2 flex-wrap">
            <span class="text-gray-600 text-xs uppercase tracking-wider">Uploaders :</span>
            @foreach($contributors as $contributor)
            <span class="inline-flex items-center rounded-full border border-amber-500/25 bg-amber-500/10 px-2.5 py-1 text-[10px] font-medium text-amber-200">
                {{ $contributor->full_name }}
            </span>
            @endforeach
        </div>
        @endif
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
            @auth
                @if(auth()->user()->role === 'visitor')
                    @if(!($isFavorited ?? false))
                        <form method="POST" action="{{ route('visitor.favorites.store') }}">
                            @csrf
                            <input type="hidden" name="type" value="article">
                            <input type="hidden" name="id" value="{{ $article->id }}">
                            <button type="submit" class="w-7 h-7 rounded-full bg-amber-900/30 hover:bg-amber-900/50 flex items-center justify-center text-amber-300 transition text-xs" title="Ajouter aux favoris">
                                <i class="fas fa-heart"></i>
                            </button>
                        </form>
                    @else
                        <span class="w-7 h-7 rounded-full bg-emerald-900/30 flex items-center justify-center text-emerald-300 text-xs" title="Déjà favori">
                            <i class="fas fa-heart-circle-check"></i>
                        </span>
                    @endif
                @endif
            @endauth
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
        {!! \App\Support\HtmlSanitizer::articleBody($article->content_fr) !!}
    </div>
    @endif

    {{-- Gallery --}}
    @php
        $galleryImages = $article->media
            ->where('type', 'image')
            ->filter(fn ($m) => ! empty($m->url) && $m->url !== $article->cover_url)
            ->values();
    @endphp
    @if($galleryImages->isNotEmpty())
    <section class="mt-10">
        <h2 class="font-serif text-xl font-bold mb-4">Galerie photos</h2>
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
            @foreach($galleryImages as $image)
            <figure class="rounded-xl overflow-hidden border border-white/10 bg-[#141410]">
                <img src="{{ $image->url }}" alt="{{ $image->alt_text ?: $article->title_fr }}" class="w-full h-32 sm:h-36 object-cover">
            </figure>
            @endforeach
        </div>
    </section>
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

    {{-- ── COMMENTAIRES ─────────────────────────────────────────────── --}}
    <section class="mt-14 pt-10 border-t border-white/8" id="commentaires">
        <h2 class="font-serif text-2xl font-bold mb-8">
            Commentaires
            @if($comments->isNotEmpty())
            <span class="text-base font-normal text-gray-500 ml-2">({{ $comments->count() }})</span>
            @endif
        </h2>

        {{-- Flash message --}}
        @if(session('comment_sent'))
        <div class="mb-6 flex items-start gap-3 px-4 py-3 rounded-xl border border-emerald-500/30 bg-emerald-500/10 text-emerald-300 text-sm">
            <i class="fas fa-circle-check mt-0.5 shrink-0"></i>
            <p>{{ session('comment_sent') }}</p>
        </div>
        @endif

        {{-- Existing comments --}}
        @if($comments->isNotEmpty())
        <div class="space-y-5 mb-10">
            @foreach($comments as $comment)
            <div class="flex gap-3">
                <div class="w-9 h-9 rounded-full bg-[#252520] border border-white/8 flex items-center justify-center text-sm font-bold text-amber-400 shrink-0 mt-0.5">
                    {{ strtoupper(substr($comment->display_name, 0, 1)) }}
                </div>
                <div class="flex-1">
                    <div class="flex items-center gap-2 mb-1.5">
                        <span class="text-white font-semibold text-sm">{{ $comment->display_name }}</span>
                        <span class="text-gray-600 text-xs">·</span>
                        <span class="text-gray-600 text-xs">{{ $comment->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-300 text-sm leading-relaxed">{{ $comment->content }}</p>
                </div>
            </div>
            @endforeach
        </div>
        @else
        <p class="text-gray-600 text-sm mb-8">Aucun commentaire pour l'instant. Soyez le premier !</p>
        @endif

        {{-- Comment form --}}
        <div class="bg-[#141410] border border-white/8 rounded-2xl p-5">
            <h3 class="font-serif font-semibold mb-4">Laisser un commentaire</h3>

            @if($errors->has('content') || $errors->has('author_name') || $errors->has('author_email'))
            <div class="mb-4 px-4 py-3 rounded-xl border border-red-500/30 bg-red-500/10 text-red-300 text-sm">
                @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('articles.comments.store', $article) }}" class="space-y-4">
                @csrf

                @guest
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-400 text-xs mb-1.5">Nom <span class="text-amber-500">*</span></label>
                        <input type="text" name="author_name" value="{{ old('author_name') }}" required
                               class="w-full bg-[#0d0d0b] border border-white/10 focus:border-amber-500/40 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition"
                               placeholder="Votre nom">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-xs mb-1.5">E-mail <span class="text-amber-500">*</span></label>
                        <input type="email" name="author_email" value="{{ old('author_email') }}" required
                               class="w-full bg-[#0d0d0b] border border-white/10 focus:border-amber-500/40 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-600 outline-none transition"
                               placeholder="Non publié">
                    </div>
                </div>
                @else
                <p class="text-gray-500 text-xs">Connecté en tant que <span class="text-amber-400">{{ auth()->user()->full_name }}</span></p>
                @endguest

                <div>
                    <label class="block text-gray-400 text-xs mb-1.5">Commentaire <span class="text-amber-500">*</span></label>
                    <textarea name="content" rows="4" required minlength="10" maxlength="2000"
                              class="w-full bg-[#0d0d0b] border border-white/10 focus:border-amber-500/40 rounded-xl px-4 py-3 text-sm text-white placeholder-gray-600 outline-none transition resize-none"
                              placeholder="Partagez votre avis sur cet article…">{{ old('content') }}</textarea>
                    <p class="text-gray-700 text-xs mt-1">Votre commentaire sera publié après modération.</p>
                </div>

                <button type="submit"
                        class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-black font-bold px-5 py-2.5 rounded-xl text-sm transition">
                    <i class="fas fa-paper-plane text-xs"></i>
                    Envoyer le commentaire
                </button>
            </form>
        </div>
    </section>
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

@include('partials.homepage-footer')
</body>
</html>
