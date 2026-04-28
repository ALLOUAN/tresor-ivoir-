<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title_fr }} — {{ $siteBrand['site_name'] }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,600;0,700&family=Inter:wght@300;400;500;600&family=Lora:ital,wght@0,400;0,500;1,400&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .prose-info { font-family: 'Lora', serif; font-size: 1.0625rem; line-height: 1.85; color: #d1cfc8; }
        .prose-info p { margin-bottom: 1.25rem; }
        .prose-info h2 { font-family: 'Playfair Display', serif; font-size: 1.35rem; font-weight: 700; color: #fff; margin: 2rem 0 0.75rem; }
        .prose-info ul { margin: 0 0 1.25rem 1.1rem; list-style: disc; }
        .prose-info ol { margin: 0 0 1.25rem 1.1rem; list-style: decimal; }
        .prose-info li { margin-bottom: 0.35rem; }
        .prose-info a { color: #f5b942; text-decoration: underline; }
        .prose-info strong { color: #fff; font-weight: 600; }
        .prose-info details { margin-bottom: 0.75rem; border: 1px solid rgba(255,255,255,.08); border-radius: 0.75rem; padding: 0.75rem 1rem; background: rgba(255,255,255,.02); }
        .prose-info summary { cursor: pointer; font-weight: 600; color: #e8e6df; }
        .prose-info aside { margin: 1.25rem 0; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

<header class="bg-[#0d0d0b]/95 backdrop-blur border-b border-white/8 sticky top-0 z-40">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 h-14 flex items-center justify-between gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
            @if(!empty($siteBrand['logo_url']))
                <div class="w-7 h-7 rounded-lg bg-white/5 border border-white/10 flex items-center justify-center overflow-hidden p-0.5">
                    <img src="{{ $siteBrand['logo_url'] }}" alt="" class="max-w-full max-h-full object-contain">
                </div>
            @else
                <div class="w-7 h-7 rounded-lg bg-amber-500 flex items-center justify-center">
                    <i class="fas fa-gem text-black text-xs"></i>
                </div>
            @endif
            <span class="font-serif font-bold text-amber-400 text-sm hidden sm:block truncate max-w-[200px]">{{ $siteBrand['site_name'] }}</span>
        </a>
        <a href="{{ route('home') }}" class="text-xs text-gray-500 hover:text-amber-400 transition">
            <i class="fas fa-arrow-left mr-1"></i>Accueil
        </a>
    </div>
</header>

<article class="max-w-3xl mx-auto px-4 sm:px-6 py-10 sm:py-14">
    <nav class="flex items-center gap-2 text-xs text-gray-600 mb-8">
        <a href="{{ route('home') }}" class="hover:text-amber-400 transition">Accueil</a>
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
