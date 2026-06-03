<!DOCTYPE html>
<html lang="fr" id="html-root" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $category->name }} à {{ $city->name }} — {{ $siteBrand['site_name'] ?? 'Trésors d\'Ivoire' }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .site-card { transition: transform .2s ease, box-shadow .2s ease; }
        .site-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(0,0,0,.4); }
        html:not(.dark) body { background: #f8f5f0; color: #1a1a1a; }
        html:not(.dark) .site-card { background: #fff !important; border-color: rgba(0,0,0,.08) !important; }
    </style>
</head>
<body class="bg-[#0d0d0b] text-white min-h-screen">

@include('partials.public-top-nav')

{{-- Header --}}
<section class="max-w-6xl mx-auto px-6 py-12">
    <nav class="text-xs text-slate-400 mb-5">
        <a href="{{ route('tourist.cities') }}" class="hover:text-amber-400 transition">Régions</a>
        <span class="mx-2 text-slate-600">/</span>
        <a href="{{ route('tourist.city', $city->slug) }}" class="hover:text-amber-400 transition">{{ $city->name }}</a>
        <span class="mx-2 text-slate-600">/</span>
        <span class="text-white">{{ $category->name }}</span>
    </nav>

    <div class="flex items-center gap-4 mb-2">
        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0"
            style="{{ $category->color ? 'background:' . $category->color . '22; color:' . $category->color : 'background:#1e293b; color:#94a3b8' }}">
            <i class="{{ $category->icon ?: 'fas fa-tag' }}"></i>
        </div>
        <div>
            <h1 class="font-serif text-3xl font-bold text-white">{{ $category->name }}</h1>
            <p class="text-slate-400 text-sm">{{ $city->name }} — {{ $sites->count() }} site(s)</p>
        </div>
    </div>
    @if($category->description)
    <p class="text-slate-400 mt-4 max-w-2xl">{{ $category->description }}</p>
    @endif
</section>

{{-- Sites list --}}
<section class="max-w-6xl mx-auto px-6 pb-20">
    @if($sites->isEmpty())
    <div class="text-center py-16 text-slate-500 border border-slate-800 rounded-2xl">
        <i class="fas fa-map-pin text-4xl mb-3 block text-slate-700"></i>
        Aucun site disponible dans cette catégorie pour le moment.
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($sites as $site)
        @php $firstPhoto = $site->media->first(); @endphp
        <a href="{{ route('tourist.site', $site->slug) }}"
            class="site-card block bg-[#111110] border border-slate-800 rounded-2xl overflow-hidden group">
            <div class="relative h-44 bg-slate-800">
                @if($firstPhoto)
                <img src="{{ $firstPhoto->url }}" alt="{{ $firstPhoto->alt_text ?: $site->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @elseif($site->thumbnail)
                <img src="{{ $site->thumbnail }}" alt="{{ $site->name }}"
                    class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                @else
                <div class="w-full h-full flex items-center justify-center">
                    <i class="fas fa-image text-4xl text-slate-700"></i>
                </div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                @if($site->is_featured)
                <span class="absolute top-3 left-3 px-2 py-0.5 bg-amber-500 text-black text-[10px] font-bold rounded-full">
                    <i class="fas fa-star mr-0.5"></i>Vedette
                </span>
                @endif
                @if($site->entrance_fee)
                <span class="absolute top-3 right-3 px-2 py-0.5 bg-black/60 text-white text-[10px] rounded-full">
                    {{ $site->entrance_fee }}
                </span>
                @endif
            </div>
            <div class="p-4">
                <h3 class="text-white font-semibold group-hover:text-amber-400 transition line-clamp-1 mb-1">
                    {{ $site->name }}
                </h3>
                @if($site->localite || $site->departement)
                <p class="text-amber-400/70 text-xs mb-2">
                    <i class="fas fa-map-marker-alt mr-1"></i>
                    {{ $site->localite ?? $site->departement }}
                </p>
                @endif
                @if($site->short_description)
                <p class="text-slate-500 text-xs line-clamp-2 mb-3">{{ $site->short_description }}</p>
                @endif
                <div class="flex items-center justify-between text-xs text-slate-600">
                    <span><i class="fas fa-eye mr-1"></i>{{ number_format($site->views_count) }} vues</span>
                    @if($site->distance_centre_km)
                    <span><i class="fas fa-route mr-1"></i>{{ $site->distance_centre_km }} km</span>
                    @endif
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
