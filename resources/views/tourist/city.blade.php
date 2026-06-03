<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $city->name }} — Régions Touristiques — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .cat-card { transition: transform .2s ease, border-color .2s ease; }
        .cat-card:hover { transform: translateY(-3px); }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .cat-card { background: #fff !important; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Bannière Ville --}}
<section class="relative h-72 md:h-[420px] overflow-hidden">

    {{-- Image de bannière --}}
    @if($city->cover_image)
    <img src="{{ $city->cover_image }}" alt="Bannière {{ $city->name }}"
        class="w-full h-full object-cover scale-105"
        style="object-position: center 40%;">
    @elseif($city->thumbnail)
    <img src="{{ $city->thumbnail }}" alt="Bannière {{ $city->name }}"
        class="w-full h-full object-cover scale-105">
    @else
    <div class="w-full h-full"
        style="background: linear-gradient(135deg, #78350f 0%, #1c1917 50%, #0d0d0b 100%);">
        <div class="absolute inset-0 flex items-center justify-center opacity-10">
            <i class="fas fa-city text-white" style="font-size: 12rem;"></i>
        </div>
    </div>
    @endif

    {{-- Dégradé bas renforcé --}}
    <div class="absolute inset-0 bg-gradient-to-t from-[#0d0d0b] via-[#0d0d0b]/50 to-transparent"></div>
    {{-- Dégradé latéral gauche --}}
    <div class="absolute inset-0 bg-linear-to-r from-[#0d0d0b]/60 to-transparent"></div>

    {{-- Contenu positionné sur la bannière --}}
    <div class="absolute inset-0 flex flex-col justify-end">
        <div class="max-w-6xl mx-auto w-full px-6 pb-10">

            {{-- Breadcrumb --}}
            <nav class="text-xs text-slate-400 mb-4 flex items-center gap-1.5">
                <a href="{{ route('tourist.cities') }}" class="hover:text-amber-400 transition flex items-center gap-1">
                    <i class="fas fa-map-location-dot text-amber-400/60 text-[10px]"></i> Régions
                </a>
                <i class="fas fa-chevron-right text-slate-700 text-[8px]"></i>
                <span class="text-white">{{ $city->name }}</span>
            </nav>

            {{-- Titre --}}
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-3 drop-shadow-lg">
                {{ $city->name }}
            </h1>

            {{-- Badges infos --}}
            <div class="flex flex-wrap items-center gap-3">
                @if($city->district)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-sm border border-white/10 text-slate-300 text-xs">
                    <i class="fas fa-map text-amber-400/70 text-[10px]"></i>
                    {{ $city->district }}
                </span>
                @endif
                @if($city->region_administrative)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-black/40 backdrop-blur-sm border border-white/10 text-slate-400 text-xs">
                    <i class="fas fa-layer-group text-amber-400/50 text-[10px]"></i>
                    {{ $city->region_administrative }}
                </span>
                @endif
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-amber-500/20 backdrop-blur-sm border border-amber-500/30 text-amber-300 text-xs">
                    <i class="fas fa-map-pin text-[10px]"></i>
                    {{ $city->sites()->where('is_active', 1)->count() }} site(s) touristique(s)
                </span>
            </div>
        </div>
    </div>

    {{-- Badge vedette --}}
    @if($city->is_featured)
    <div class="absolute top-5 right-6">
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 text-black text-xs font-bold rounded-full shadow-lg">
            <i class="fas fa-star text-[10px]"></i> Destination vedette
        </span>
    </div>
    @endif

</section>

{{-- Description --}}
@if($city->description)
<section class="max-w-6xl mx-auto px-6 py-8">
    <p class="text-slate-400 text-base leading-relaxed max-w-3xl">{{ $city->description }}</p>
</section>
@endif

{{-- Catégories --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    <h2 class="text-white font-serif text-2xl font-bold mb-8">Catégories touristiques</h2>

    @if($categories->isEmpty())
    <div class="text-center py-16 text-slate-500 border border-slate-800 rounded-2xl">
        <i class="fas fa-tags text-4xl mb-3 block text-slate-700"></i>
        Aucune catégorie touristique disponible pour {{ $city->name }}.
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($categories as $cat)
        <a href="{{ route('tourist.category', [$city->slug, $cat->slug]) }}"
            class="cat-card flex items-center gap-4 bg-[#111110] border border-slate-800 hover:border-amber-500/40 rounded-2xl p-5 group">
            <div class="w-14 h-14 rounded-xl flex items-center justify-center text-2xl shrink-0"
                style="{{ $cat->color ? 'background:' . $cat->color . '22; color:' . $cat->color : 'background:#1e293b; color:#94a3b8' }}">
                <i class="{{ $cat->icon ?: 'fas fa-tag' }}"></i>
            </div>
            <div class="flex-1 min-w-0">
                <h3 class="text-white font-semibold group-hover:text-amber-400 transition truncate">{{ $cat->name }}</h3>
                <p class="text-slate-500 text-xs mt-0.5">
                    {{ $cat->sites_count }} site(s) à explorer
                </p>
                @if($cat->description)
                <p class="text-slate-600 text-xs mt-1 line-clamp-1">{{ $cat->description }}</p>
                @endif
            </div>
            <i class="fas fa-chevron-right text-slate-700 group-hover:text-amber-400/60 transition text-xs shrink-0"></i>
        </a>
        @endforeach
    </div>
    @endif
</section>

@include('partials.homepage-footer')
</body>
</html>
