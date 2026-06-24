<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cultures Ivoiriennes — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .people-card { transition: transform .2s ease, box-shadow .2s ease; }
        .people-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(0,0,0,.4); }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .people-card { background: #fff !important; border-color: rgba(0,0,0,.08) !important; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Hero --}}
<section class="relative py-20 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-amber-900/20 to-transparent pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <p class="text-amber-400 text-sm font-medium uppercase tracking-widest mb-3">Patrimoine Vivant</p>
        <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
            Cultures Ivoiriennes
        </h1>
        <p class="text-slate-400 text-lg max-w-2xl mx-auto">
            Partez à la rencontre des peuples qui façonnent l'identité culturelle de la Côte d'Ivoire.
        </p>
    </div>
</section>

{{-- Domaines culturels --}}
@if($domains->isNotEmpty())
<section class="max-w-6xl mx-auto px-6 pb-8">
    <div class="flex flex-wrap gap-2 justify-center">
        <a href="{{ route('cultural.peoples') }}"
            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition
                {{ !request('domaine') ? 'bg-amber-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700' }}">
            <i class="fas fa-globe text-xs"></i> Tous les peuples
        </a>
        @foreach($domains as $domain)
        <a href="{{ route('cultural.peoples', ['domaine' => $domain->slug]) }}"
            class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition border
                {{ request('domaine') === $domain->slug
                    ? 'bg-amber-500 text-black border-amber-500'
                    : 'bg-slate-800/60 text-slate-300 hover:bg-slate-700 hover:text-white border-slate-700' }}"
            style="{{ request('domaine') === $domain->slug || !$domain->color ? '' : 'border-color:'.$domain->color.'40' }}">
            @if($domain->icon)<i class="{{ $domain->icon }} text-xs" style="{{ ($domain->color && request('domaine') !== $domain->slug) ? 'color:'.$domain->color : '' }}"></i>@endif
            {{ $domain->name }}
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Grille peuples --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    @if($peoples->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <i class="fas fa-users text-5xl mb-4 block text-slate-700"></i>
        <p>Aucun peuple disponible pour le moment.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($peoples as $people)
        <a href="{{ route('cultural.people', $people->slug) }}"
            class="people-card block bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden group">
            {{-- Image --}}
            <div class="relative h-52 overflow-hidden bg-slate-800">
                @if($people->cover_image)
                <img src="{{ $people->cover_image }}" alt="{{ $people->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($people->thumbnail)
                <img src="{{ $people->thumbnail }}" alt="{{ $people->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center"
                    style="background: linear-gradient(135deg, #78350f 0%, #1c1917 60%, #0d0d0b 100%);">
                    <i class="fas fa-users text-5xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>

                @if($people->is_featured)
                <span class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-black text-xs font-bold rounded-full">
                    <i class="fas fa-star mr-1"></i>À la une
                </span>
                @endif

                {{-- Zone géographique badge --}}
                @if($people->zone_geographique)
                <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-black/50 backdrop-blur-sm border border-white/10 text-slate-300 text-xs">
                    {{ $people->zone_geographique }}
                </span>
                @endif

                <div class="absolute bottom-3 left-4 right-4">
                    <h2 class="text-white font-serif text-xl font-bold">{{ $people->name }}</h2>
                    @if($people->capitale_culturelle)
                    <p class="text-amber-300/80 text-xs mt-0.5">
                        <i class="fas fa-landmark mr-1"></i>{{ $people->capitale_culturelle }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="p-4">
                @if($people->famille_linguistique)
                <p class="text-amber-400/70 text-xs font-medium mb-2">
                    <i class="fas fa-language mr-1"></i>{{ $people->famille_linguistique }}
                </p>
                @endif
                @if($people->description)
                <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ $people->description }}</p>
                @endif
                <div class="flex items-center justify-between">
                    @if($people->population_estimee)
                    <span class="text-slate-500 text-xs">
                        <i class="fas fa-people-group text-amber-400/50 mr-1"></i>
                        ~{{ number_format($people->population_estimee) }}
                    </span>
                    @else
                    <span></span>
                    @endif
                    <span class="text-amber-400 text-xs group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                        Découvrir <i class="fas fa-arrow-right text-[10px]"></i>
                    </span>
                </div>
            </div>
        </a>
        @endforeach
    </div>
    @endif
</section>

@include('partials.homepage-footer')
</body>
</html>
