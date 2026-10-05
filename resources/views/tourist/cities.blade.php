<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Régions Touristiques — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .city-card { transition: transform .2s ease, box-shadow .2s ease; }
        .city-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(0,0,0,.4); }
        html:not(.dark) body { background:#e9e5d9; color: #1c1915; }
        html:not(.dark) .city-card { background:#f0ece1 !important; border-color: rgba(0,0,0,.12) !important; box-shadow: 0 8px 20px rgba(0,0,0,.05); }
        /* Image de fond configurable (back-office) du bandeau de la page Tourisme. */
        .tourist-hero {
            background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55));
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .tourist-hero[style*="--tourist-hero-bg-image"] {
            background-image: var(--tourist-hero-bg-image, none);
        }
    </style>
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')

@include('partials.public-top-nav')

@php
    $touristHeroImage = \Illuminate\Support\Facades\Schema::hasTable('tourist_hero_images')
        ? \App\Models\TouristHeroImage::query()->find(1)
        : null;
@endphp

{{-- Hero --}}
<section class="relative py-20 overflow-hidden {{ ($touristHeroImage && $touristHeroImage->isVisible()) ? 'tourist-hero' : '' }}"
    @if($touristHeroImage && $touristHeroImage->isVisible())
        style="--tourist-hero-bg-image: url('{{ $touristHeroImage->image_url }}');"
    @endif
>
    <div class="absolute inset-0 bg-gradient-to-b from-orange-900/20 to-transparent pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <div class="inline-block rounded-2xl bg-green-950/70 backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
            <p class="text-orange-400 text-sm font-medium uppercase tracking-widest mb-3">Découverte</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                Régions Touristiques
            </h1>
            <p class="text-gray-200 text-xl max-w-2xl mx-auto">
                Explorez les villes et leurs merveilles touristiques à travers toute la Côte d'Ivoire.
            </p>
        </div>
    </div>
</section>

{{-- Filtre par catégorie --}}
@if($categories->isNotEmpty())
<section class="max-w-6xl mx-auto px-6 pb-8">
    <div class="flex flex-wrap justify-center gap-2.5">
        <a href="{{ route('tourist.cities') }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-sm transition
               {{ !$category ? 'bg-orange-500 text-black border-orange-500 font-semibold' : 'bg-white/5 text-slate-300 border-slate-700 hover:border-orange-500/40' }}">
            Toutes catégories
        </a>
        @foreach($categories as $cat)
        <a href="{{ route('tourist.cities', ['categorie' => $cat->slug]) }}"
           class="inline-flex items-center gap-2 px-4 py-2 rounded-full border text-sm transition
               {{ $category && $category->id === $cat->id ? 'bg-orange-500 text-black border-orange-500 font-semibold' : 'bg-white/5 text-slate-300 border-slate-700 hover:border-orange-500/40' }}">
            <span class="w-5 h-5 rounded-full overflow-hidden flex items-center justify-center shrink-0">
                @if($cat->icon_image_url)
                    <img src="{{ $cat->icon_image_url }}" alt="" class="w-full h-full object-cover">
                @else
                    <i class="{{ $cat->icon ?: 'fas fa-tag' }} text-xs"></i>
                @endif
            </span>
            {{ $cat->name }}
        </a>
        @endforeach
    </div>
</section>
@endif

{{-- Cities grid --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    @if($category)
    <p class="text-center text-slate-500 text-sm mb-6">
        {{ $cities->count() }} ville(s) proposant « {{ $category->name }} »
        <a href="{{ route('tourist.cities') }}" class="text-orange-400 hover:text-orange-300 ml-1"><i class="fas fa-times-circle"></i> Effacer</a>
    </p>
    @endif
    @if($cities->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <i class="fas fa-city text-5xl mb-4 block text-slate-700"></i>
        @if($category)
        <p>Aucune ville ne propose « {{ $category->name }} » pour le moment.</p>
        <a href="{{ route('tourist.cities') }}" class="text-orange-400 hover:text-orange-300 text-sm mt-2 inline-block">Voir toutes les villes</a>
        @else
        <p>Aucune ville disponible pour le moment.</p>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($cities as $city)
        <a href="{{ route('tourist.city', $city->slug) }}"
            class="city-card block bg-[#ffffff] border border-slate-800 rounded-2xl overflow-hidden group">
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
                <div class="absolute inset-0 bg-gradient-to-t from-green-950/70 to-transparent"></div>

                @if($city->is_featured)
                <span class="absolute top-3 left-3 px-2.5 py-1 bg-orange-500 text-black text-xs font-bold rounded-full">
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
                <p class="text-orange-400/80 text-xs font-medium mb-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>{{ $city->region_administrative }}
                </p>
                @endif
                @if($city->description)
                <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ $city->description }}</p>
                @endif
                <div class="flex items-center justify-between">
                    <span class="text-slate-500 text-xs">
                        <i class="fas fa-map-pin text-orange-400/60 mr-1"></i>
                        {{ $city->sites_count }} site(s) touristique(s)
                    </span>
                    <span class="text-orange-400 text-xs group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
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
