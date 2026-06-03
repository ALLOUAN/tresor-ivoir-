<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $site->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .gallery-thumb { transition: opacity .2s; cursor: pointer; }
        .gallery-thumb:hover { opacity: .8; }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .info-card { background: #fff !important; border-color: rgba(0,0,0,.08) !important; }
        .related-card { transition: transform .2s ease; }
        .related-card:hover { transform: translateY(-3px); }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Hero + Galerie principale --}}
<section class="max-w-6xl mx-auto px-6 pt-8 pb-4">
    {{-- Breadcrumb --}}
    <nav class="text-xs text-slate-400 mb-6">
        <a href="{{ route('tourist.cities') }}" class="hover:text-amber-400 transition">Régions</a>
        <span class="mx-2 text-slate-600">/</span>
        <a href="{{ route('tourist.city', $site->city->slug) }}" class="hover:text-amber-400 transition">{{ $site->city->name }}</a>
        <span class="mx-2 text-slate-600">/</span>
        <a href="{{ route('tourist.category', [$site->city->slug, $site->category->slug]) }}" class="hover:text-amber-400 transition">{{ $site->category->name }}</a>
        <span class="mx-2 text-slate-600">/</span>
        <span class="text-white">{{ $site->name }}</span>
    </nav>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Photo principale --}}
            @php $mainPhoto = $site->photos->first(); @endphp
            <div class="relative h-72 md:h-96 rounded-2xl overflow-hidden bg-slate-800">
                @if($mainPhoto)
                <img id="mainPhoto" src="{{ $mainPhoto->url }}" alt="{{ $mainPhoto->alt_text ?: $site->name }}"
                    class="w-full h-full object-cover">
                @elseif($site->thumbnail)
                <img src="{{ $site->thumbnail }}" alt="{{ $site->name }}" class="w-full h-full object-cover">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-image text-6xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent pointer-events-none"></div>
                @if($site->is_featured)
                <span class="absolute top-4 left-4 px-3 py-1 bg-amber-500 text-black text-xs font-bold rounded-full">
                    <i class="fas fa-star mr-1"></i>Site vedette
                </span>
                @endif
                @if($site->entrance_fee)
                <span class="absolute top-4 right-4 px-3 py-1 bg-black/70 text-white text-xs rounded-full">
                    <i class="fas fa-ticket mr-1 text-amber-400"></i>{{ $site->entrance_fee }}
                </span>
                @endif
            </div>

            {{-- Galerie miniatures --}}
            @if($site->photos->count() > 1)
            <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                @foreach($site->photos as $photo)
                <img src="{{ $photo->url }}" alt="{{ $photo->alt_text }}"
                    class="gallery-thumb w-full h-16 rounded-xl object-cover border-2 {{ $loop->first ? 'border-amber-500' : 'border-transparent' }}"
                    onclick="changeMainPhoto(this, '{{ $photo->url }}')">
                @endforeach
            </div>
            @endif

            {{-- Titre --}}
            <div>
                <div class="flex items-start justify-between gap-4 mb-3">
                    <h1 class="font-serif text-3xl md:text-4xl font-bold text-white leading-tight">
                        {{ $site->name }}
                    </h1>
                    <div class="flex items-center gap-2 shrink-0">
                        <span class="text-slate-500 text-xs flex items-center gap-1">
                            <i class="fas fa-eye text-slate-600"></i>
                            {{ number_format($site->views_count) }}
                        </span>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-sm text-slate-400 mb-4">
                    <a href="{{ route('tourist.city', $site->city->slug) }}"
                        class="flex items-center gap-1.5 hover:text-amber-400 transition">
                        <i class="fas fa-city text-amber-400/60 text-xs"></i>
                        {{ $site->city->name }}
                    </a>
                    <span class="text-slate-700">·</span>
                    <a href="{{ route('tourist.category', [$site->city->slug, $site->category->slug]) }}"
                        class="flex items-center gap-1.5 hover:text-amber-400 transition">
                        <i class="{{ $site->category->icon ?: 'fas fa-tag' }} text-xs"
                            style="{{ $site->category->color ? 'color:'.$site->category->color : '' }}"></i>
                        {{ $site->category->name }}
                    </a>
                    @if($site->localite)
                    <span class="text-slate-700">·</span>
                    <span class="flex items-center gap-1">
                        <i class="fas fa-map-marker-alt text-amber-400/60 text-xs"></i>
                        {{ $site->localite }}
                    </span>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($site->description)
            <div class="prose prose-invert max-w-none">
                <div class="text-slate-300 leading-relaxed text-base whitespace-pre-line">{{ $site->description }}</div>
            </div>
            @endif

            {{-- Vidéos --}}
            @if($site->videos->isNotEmpty())
            <div>
                <h2 class="text-white font-semibold text-lg mb-4 flex items-center gap-2">
                    <i class="fas fa-play-circle text-amber-400"></i> Vidéos
                </h2>
                <div class="space-y-4">
                    @foreach($site->videos as $video)
                    <div class="rounded-xl overflow-hidden bg-slate-800 aspect-video">
                        <iframe src="{{ $video->url }}" class="w-full h-full"
                            frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
                    </div>
                    @if($video->caption)
                    <p class="text-slate-500 text-xs">{{ $video->caption }}</p>
                    @endif
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- Colonne latérale --}}
        <div class="space-y-5">

            {{-- Infos de contact --}}
            <div class="info-card bg-[#111110] border border-slate-800 rounded-2xl p-5">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-address-card text-amber-400"></i> Informations
                </h3>
                <ul class="space-y-3 text-sm">
                    @if($site->entrance_fee)
                    <li class="flex items-start gap-3 text-slate-400">
                        <i class="fas fa-ticket text-amber-400 text-xs mt-0.5 w-4"></i>
                        <span>{{ $site->entrance_fee }}</span>
                    </li>
                    @endif
                    @if($site->phone)
                    <li class="flex items-start gap-3 text-slate-400">
                        <i class="fas fa-phone text-amber-400 text-xs mt-0.5 w-4"></i>
                        <a href="tel:{{ $site->phone }}" class="hover:text-amber-400 transition">{{ $site->phone }}</a>
                    </li>
                    @endif
                    @if($site->email)
                    <li class="flex items-start gap-3 text-slate-400">
                        <i class="fas fa-envelope text-amber-400 text-xs mt-0.5 w-4"></i>
                        <a href="mailto:{{ $site->email }}" class="hover:text-amber-400 transition truncate">{{ $site->email }}</a>
                    </li>
                    @endif
                    @if($site->website)
                    <li class="flex items-start gap-3 text-slate-400">
                        <i class="fas fa-globe text-amber-400 text-xs mt-0.5 w-4"></i>
                        <a href="{{ $site->website }}" target="_blank" rel="noopener"
                            class="hover:text-amber-400 transition truncate">{{ $site->website }}</a>
                    </li>
                    @endif
                </ul>
            </div>

            {{-- Situation géographique --}}
            <div class="info-card bg-[#111110] border border-slate-800 rounded-2xl p-5">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-map-location-dot text-amber-400"></i> Situation géographique
                </h3>
                <ul class="space-y-2 text-sm text-slate-400">
                    @if($site->departement)
                    <li><span class="text-slate-600 text-xs uppercase tracking-wide block">Département</span>{{ $site->departement }}</li>
                    @endif
                    @if($site->sous_prefecture)
                    <li class="pt-1"><span class="text-slate-600 text-xs uppercase tracking-wide block">Sous-préfecture</span>{{ $site->sous_prefecture }}</li>
                    @endif
                    @if($site->localite)
                    <li class="pt-1"><span class="text-slate-600 text-xs uppercase tracking-wide block">Localité</span>{{ $site->localite }}</li>
                    @endif
                    @if($site->altitude_m)
                    <li class="pt-1"><span class="text-slate-600 text-xs uppercase tracking-wide block">Altitude</span>{{ $site->altitude_m }} m</li>
                    @endif
                    @if($site->superficie_ha)
                    <li class="pt-1"><span class="text-slate-600 text-xs uppercase tracking-wide block">Superficie</span>{{ number_format($site->superficie_ha, 2) }} ha</li>
                    @endif
                    @if($site->distance_centre_km)
                    <li class="pt-1"><span class="text-slate-600 text-xs uppercase tracking-wide block">Distance du centre</span>{{ $site->distance_centre_km }} km</li>
                    @endif
                    @if($site->latitude && $site->longitude)
                    <li class="pt-1">
                        <span class="text-slate-600 text-xs uppercase tracking-wide block">Coordonnées GPS</span>
                        <a href="https://maps.google.com/?q={{ $site->latitude }},{{ $site->longitude }}" target="_blank"
                            class="text-amber-400/80 hover:text-amber-400 transition text-xs">
                            {{ $site->latitude }}, {{ $site->longitude }}
                            <i class="fas fa-external-link-alt ml-1 text-[10px]"></i>
                        </a>
                    </li>
                    @endif
                </ul>
                @if($site->point_repere)
                <div class="mt-3 pt-3 border-t border-slate-800">
                    <span class="text-slate-600 text-xs uppercase tracking-wide block mb-1">Point de repère</span>
                    <p class="text-slate-400 text-xs">{{ $site->point_repere }}</p>
                </div>
                @endif
                @if($site->acces_description)
                <div class="mt-3 pt-3 border-t border-slate-800">
                    <span class="text-slate-600 text-xs uppercase tracking-wide block mb-1">Comment s'y rendre</span>
                    <p class="text-slate-400 text-xs leading-relaxed">{{ $site->acces_description }}</p>
                </div>
                @endif
            </div>

            {{-- Horaires --}}
            @if(!empty($site->schedules))
            <div class="info-card bg-[#111110] border border-slate-800 rounded-2xl p-5">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-clock text-amber-400"></i> Horaires d'ouverture
                </h3>
                <ul class="space-y-2">
                    @foreach($site->schedules as $s)
                    <li class="flex items-center justify-between text-sm">
                        <span class="text-slate-400">{{ $s['day'] }}</span>
                        @if($s['closed'] ?? false)
                        <span class="text-red-400 text-xs">Fermé</span>
                        @else
                        <span class="text-slate-300 text-xs font-mono">
                            {{ $s['opens'] ?? '—' }} – {{ $s['closes'] ?? '—' }}
                        </span>
                        @endif
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Infos pratiques --}}
            @if(!empty($site->practical_info))
            <div class="info-card bg-[#111110] border border-slate-800 rounded-2xl p-5">
                <h3 class="text-white font-semibold mb-4 flex items-center gap-2">
                    <i class="fas fa-circle-info text-amber-400"></i> Infos pratiques
                </h3>
                <ul class="space-y-3">
                    @foreach($site->practical_info as $info)
                    <li class="flex items-start gap-3 text-sm">
                        @if(!empty($info['icon']))
                        <i class="{{ $info['icon'] }} text-amber-400/70 text-xs mt-0.5 w-4 shrink-0"></i>
                        @else
                        <i class="fas fa-circle-dot text-amber-400/70 text-xs mt-0.5 w-4 shrink-0"></i>
                        @endif
                        <div>
                            <span class="text-slate-500 text-xs block">{{ $info['label'] }}</span>
                            <span class="text-slate-300">{{ $info['value'] }}</span>
                        </div>
                    </li>
                    @endforeach
                </ul>
            </div>
            @endif

            {{-- Carte Google Maps --}}
            @if($site->map_embed_url)
            <div class="info-card bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden">
                <div class="p-4 border-b border-slate-800">
                    <h3 class="text-white font-semibold flex items-center gap-2">
                        <i class="fas fa-map text-amber-400"></i> Carte
                    </h3>
                </div>
                <div class="aspect-video">
                    <iframe src="{{ $site->map_embed_url }}" class="w-full h-full"
                        frameborder="0" allowfullscreen loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
            @elseif($site->latitude && $site->longitude)
            <a href="https://maps.google.com/?q={{ $site->latitude }},{{ $site->longitude }}" target="_blank"
                class="info-card block bg-[#111110] border border-slate-800 hover:border-amber-500/40 rounded-2xl p-4 text-center transition group">
                <i class="fas fa-map-location-dot text-2xl text-amber-400/60 group-hover:text-amber-400 transition mb-2 block"></i>
                <p class="text-slate-400 text-sm group-hover:text-white transition">Voir sur Google Maps</p>
            </a>
            @endif

        </div>
    </div>
</section>

{{-- Sites similaires --}}
@if($related->isNotEmpty())
<section class="max-w-6xl mx-auto px-6 pb-20">
    <h2 class="text-white font-serif text-2xl font-bold mb-6">Sites similaires</h2>
    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach($related as $r)
        @php $rPhoto = $r->media->first(); @endphp
        <a href="{{ route('tourist.site', $r->slug) }}"
            class="related-card block bg-[#111110] border border-slate-800 rounded-xl overflow-hidden group">
            <div class="h-32 bg-slate-800 relative overflow-hidden">
                @if($rPhoto)
                <img src="{{ $rPhoto->url }}" alt="{{ $r->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($r->thumbnail)
                <img src="{{ $r->thumbnail }}" alt="{{ $r->name }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-image text-2xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
            </div>
            <div class="p-3">
                <h3 class="text-white text-xs font-semibold group-hover:text-amber-400 transition line-clamp-2">{{ $r->name }}</h3>
            </div>
        </a>
        @endforeach
    </div>
</section>
@endif

@include('partials.homepage-footer')

<script>
function changeMainPhoto(thumb, url) {
    document.getElementById('mainPhoto').src = url;
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.remove('border-amber-500'));
    document.querySelectorAll('.gallery-thumb').forEach(t => t.classList.add('border-transparent'));
    thumb.classList.remove('border-transparent');
    thumb.classList.add('border-amber-500');
}
</script>
</body>
</html>
