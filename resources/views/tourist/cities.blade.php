<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Régions Touristiques — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .city-card { transition: transform .2s ease, box-shadow .2s ease; }
        .city-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(0,0,0,.4); }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .city-card { background: #fff !important; border-color: rgba(0,0,0,.08) !important; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Hero --}}
<section class="relative py-20 overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-b from-amber-900/20 to-transparent pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <p class="text-amber-400 text-sm font-medium uppercase tracking-widest mb-3">Découverte</p>
        <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
            Régions Touristiques
        </h1>
        <p class="text-slate-400 text-lg max-w-2xl mx-auto">
            Explorez les villes et leurs merveilles touristiques à travers toute la Côte d'Ivoire.
        </p>
    </div>
</section>

{{-- Cities grid --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    @if($cities->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <i class="fas fa-city text-5xl mb-4 block text-slate-700"></i>
        <p>Aucune ville disponible pour le moment.</p>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($cities as $city)
        <a href="{{ route('tourist.city', $city->slug) }}"
            class="city-card block bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden group">
            {{-- Cover image --}}
            <div class="relative h-48 overflow-hidden bg-slate-800">
                @if($city->cover_image)
                <img src="{{ $city->cover_image }}" alt="{{ $city->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($city->thumbnail)
                <img src="{{ $city->thumbnail }}" alt="{{ $city->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-city text-5xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/70 to-transparent"></div>

                @if($city->is_featured)
                <span class="absolute top-3 left-3 px-2.5 py-1 bg-amber-500 text-black text-xs font-bold rounded-full">
                    <i class="fas fa-star mr-1"></i>À la une
                </span>
                @endif

                <div class="absolute bottom-3 left-4">
                    <h2 class="text-white font-serif text-xl font-bold">{{ $city->name }}</h2>
                    @if($city->district)
                    <p class="text-slate-300 text-xs">{{ $city->district }}</p>
                    @endif
                </div>
            </div>
            {{-- Info --}}
            <div class="p-4">
                @if($city->region_administrative)
                <p class="text-amber-400/80 text-xs font-medium mb-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $city->region_administrative }}
                </p>
                @endif
                @if($city->description)
                <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ $city->description }}</p>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-xs">
                        <i class="fas fa-map-pin text-amber-400/60 mr-1"></i>
                        {{ $city->sites_count }} site(s) touristique(s)
                    </span>
                    <span class="text-amber-400 text-xs group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
                        Explorer <i class="fas fa-arrow-right text-[10px]"></i>
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
