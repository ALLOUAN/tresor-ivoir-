<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cultures Ivoiriennes — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .people-card { transition: transform .2s ease, box-shadow .2s ease; }
        .people-card:hover { transform: translateY(-4px); box-shadow: 0 16px 32px rgba(0,0,0,.4); }
        html:not(.dark) body { background:#e9e5d9; color: #1c1915; }
        html:not(.dark) .people-card { background:#e9e5d9 !important; border-color: rgba(0,0,0,.08) !important; }
        /* Image de fond configurable (back-office) du bandeau de la page Cultures. */
        .cultural-hero {
            background-image: linear-gradient(rgba(233,229,217,.55), rgba(233,229,217,.55));
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        /* Image configurée : couleurs vraies, sans voile — la lisibilité du texte
           est déjà assurée par le cadre bg-green-950/70 qui l'entoure. */
        .cultural-hero[style*="--cultural-hero-bg-image"] {
            background-image: var(--cultural-hero-bg-image, none);
        }
        /* Grille 2 colonnes sur mobile pour les pilules de filtre par domaine,
           même traitement que sur la page d'accueil : texte qui passe à la
           ligne + hauteur de tuile uniforme, quel que soit l'appareil. */
        @media (max-width: 639px) {
            #domain-filter-row { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); justify-content: initial; }
            #domain-filter-row .domain-filter-pill {
                white-space: normal; width: 100%; text-align: left;
                min-height: 52px; box-sizing: border-box;
            }
        }
    </style>
</head>
<body class="bg-[#ffffff] text-white min-h-screen">
    @include('partials.page-background')

@include('partials.public-top-nav')

@php
    $culturalHeroImage = \Illuminate\Support\Facades\Schema::hasTable('cultural_hero_images')
        ? \App\Models\CulturalHeroImage::query()->find(1)
        : null;
@endphp

{{-- Hero --}}
<section class="relative py-20 overflow-hidden {{ ($culturalHeroImage && $culturalHeroImage->isVisible()) ? 'cultural-hero' : '' }}"
    @if($culturalHeroImage && $culturalHeroImage->isVisible())
        style="--cultural-hero-bg-image: url('{{ $culturalHeroImage->image_url }}');"
    @endif
>
    <div class="absolute inset-0 bg-gradient-to-b from-orange-900/20 to-transparent pointer-events-none"></div>
    <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
        <div class="inline-block rounded-2xl bg-green-950/70 backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
            <p class="text-orange-400 text-sm font-medium uppercase tracking-widest mb-3">Patrimoine Vivant</p>
            <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">
                Cultures Ivoiriennes
            </h1>
            <p class="text-gray-200 text-xl max-w-2xl mx-auto">
                Partez à la rencontre des peuples qui façonnent l'identité culturelle de la Côte d'Ivoire.
            </p>
        </div>
    </div>
</section>

{{-- Domaines culturels --}}
@if($domains->isNotEmpty())
<section class="max-w-6xl mx-auto px-6 pb-8">
    <div class="flex flex-wrap gap-2 justify-center" id="domain-filter-row">
        <a href="{{ route('cultural.peoples') }}"
            class="domain-filter-pill inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition
                {{ !request('domaine') ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700 hover:text-white border border-slate-700' }}">
            <i class="fas fa-globe text-xs"></i> Tous les peuples
        </a>
        @foreach($domains as $domain)
        <a href="{{ route('cultural.peoples', ['domaine' => $domain->slug]) }}"
            class="domain-filter-pill inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full text-sm font-medium transition border
                {{ request('domaine') === $domain->slug
                    ? 'bg-orange-500 text-black border-orange-500'
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
    @if($domain)
    <p class="text-center text-slate-500 text-sm mb-6">
        {{ $peoples->count() }} peuple(s) lié(s) au domaine « {{ $domain->name }} »
        <a href="{{ route('cultural.peoples') }}" class="text-orange-400 hover:text-orange-300 ml-1"><i class="fas fa-times-circle"></i> Effacer</a>
    </p>
    @endif
    @if($peoples->isEmpty())
    <div class="text-center py-20 text-slate-500">
        <i class="fas fa-users text-5xl mb-4 block text-slate-700"></i>
        @if($domain)
        <p>Aucun peuple lié au domaine « {{ $domain->name }} » pour le moment.</p>
        <a href="{{ route('cultural.peoples') }}" class="text-orange-400 hover:text-orange-300 text-sm mt-2 inline-block">Voir tous les peuples</a>
        @else
        <p>Aucun peuple disponible pour le moment.</p>
        @endif
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($peoples as $people)
        <a href="{{ route('cultural.people', $people->slug) }}"
            class="people-card block bg-[#ffffff] border border-slate-800 rounded-2xl overflow-hidden group">
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
                    style="background: #e9e5d9;">
                    <i class="fas fa-users text-5xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-green-950/80 via-green-950/20 to-transparent"></div>

                @if($people->is_featured)
                <span class="absolute top-3 left-3 px-2.5 py-1 bg-orange-500 text-black text-xs font-bold rounded-full">
                    <i class="fas fa-star mr-1"></i>À la une
                </span>
                @endif

                {{-- Zone géographique badge --}}
                @if($people->zone_geographique)
                <span class="absolute top-3 right-3 px-2.5 py-1 rounded-full bg-green-950/50 backdrop-blur-sm border border-white/10 text-slate-300 text-xs">
                    {{ $people->zone_geographique }}
                </span>
                @endif

                <div class="absolute bottom-3 left-4 right-4">
                    <h2 class="text-white font-serif text-xl font-bold">{{ $people->name }}</h2>
                    @if($people->capitale_culturelle)
                    <p class="text-orange-300/80 text-xs mt-0.5">
                        <i class="fas fa-landmark mr-1"></i>{{ $people->capitale_culturelle }}
                    </p>
                    @endif
                </div>
            </div>

            {{-- Info --}}
            <div class="p-4">
                @if($people->famille_linguistique)
                <p class="text-orange-400/70 text-xs font-medium mb-2">
                    <i class="fas fa-language mr-1"></i>{{ $people->famille_linguistique }}
                </p>
                @endif
                @if($people->description)
                <p class="text-slate-400 text-sm line-clamp-2 mb-3">{{ $people->description }}</p>
                @endif
                <div class="flex items-center justify-between">
                    @if($people->population_estimee)
                    <span class="text-slate-500 text-xs">
                        <i class="fas fa-people-group text-orange-400/50 mr-1"></i>
                        ~{{ number_format($people->population_estimee) }}
                    </span>
                    @else
                    <span></span>
                    @endif
                    <span class="text-orange-400 text-xs group-hover:translate-x-1 transition-transform inline-flex items-center gap-1">
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
