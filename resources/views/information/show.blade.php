<!DOCTYPE html>
<html lang="fr" id="html-root" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .prose-info { font-family: 'Lora', serif; font-size: 1.0625rem; line-height: 1.85; color: #d1cfc8; }
        .prose-info p { margin-bottom: 1.25rem; }
        .prose-info h2 { font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #fff; margin: 2rem 0 0.75rem; }
        .prose-info ul { margin: 0 0 1.25rem 1.1rem; list-style: disc; }
        .prose-info ol { margin: 0 0 1.25rem 1.1rem; list-style: decimal; }
        .prose-info li { margin-bottom: 0.35rem; }
        .prose-info a { color: #fa9a3c; text-decoration: underline; }
        .prose-info strong { color: #fff; font-weight: 600; }
        .prose-info details { margin-bottom: 0.75rem; border: 1px solid rgba(255,255,255,.08); border-radius: 0.75rem; padding: 0.75rem 1rem; background: rgba(233, 229, 217, .02); }
        .prose-info summary { cursor: pointer; font-weight: 600; color: #e8e6df; }
        .prose-info aside { margin: 1.25rem 0; }
        html:not(.dark) .prose-info { color:#44413a; }
        html:not(.dark) .prose-info h2,
        html:not(.dark) .prose-info strong { color:#1c1915; }
        html:not(.dark) .prose-info details { border-color:rgba(0,0,0,.1); background:rgba(0,0,0,.02); }
        html:not(.dark) .prose-info summary { color:#2d2a23; }
    </style>
    @include('partials.theme-light-bridge')
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')

@include('partials.public-top-nav')

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
    <nav class="flex items-center gap-2 text-xs text-gray-600 mb-8">
        <a href="{{ route('home') }}" class="hover:text-orange-400 transition">Accueil</a>
        <i class="fas fa-chevron-right text-[9px]"></i>
        <span class="text-gray-400">{{ $page->title_fr }}</span>
    </nav>

    <h1 class="font-serif text-3xl sm:text-4xl font-bold text-white mb-8">{{ $page->title_fr }}</h1>

    <div class="prose-info">
        @if(filled($page->body_fr))
            {!! $page->body_fr !!}
        @else
            <p class="text-gray-500 italic">Le contenu de cette page sera bientôt disponible.</p>
        @endif
    </div>
</article>

@include('partials.homepage-footer')
</body>
</html>
