<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sites Touristiques — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .hero-experience {
            background:
                radial-gradient(120% 100% at 15% 0%, rgba(39,174,96,0.18), transparent 55%),
                linear-gradient(150deg, #241e14 0%, #14130f 65%);
        }
        .filter-card { border: 1px solid rgba(0,0,0,0.08); background: #ffffff; box-shadow: 0 14px 34px rgba(20,18,12,0.08); }
        .venue-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .venue-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(39,174,96,0.14); border-color: rgba(39,174,96,0.3); }
        .venue-cover { position: relative; overflow: hidden; background: #14130f; }
        .venue-cover img { transition: transform .5s ease; }
        .venue-card:hover .venue-cover img { transform: scale(1.06); }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-experience relative py-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="inline-block rounded-2xl bg-green-950/70 backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
                <p class="text-emerald-300 text-sm font-medium uppercase tracking-widest mb-3">Sites Touristiques</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">Explorez la Côte d'Ivoire</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">Parcs, sites naturels et expériences touristiques vérifiés, proposés par des prestataires locaux.</p>
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        <form method="GET" action="{{ route('tourist-experience.index') }}" class="filter-card rounded-2xl p-5 mb-8">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Ville</label>
                    <select name="ville" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="">Toutes villes</option>
                        @foreach($cities as $c)
                            <option value="{{ $c->id }}" @selected((string) $cityId === (string) $c->id)>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Trier par</label>
                    <select name="tri" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                        <option value="pertinence" @selected($sort==='pertinence')>Pertinence</option>
                        <option value="recent" @selected($sort==='recent')>Plus récent</option>
                        <option value="popularite" @selected($sort==='popularite')>Popularité</option>
                    </select>
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 px-4 py-2.5 rounded-lg font-bold text-sm text-white" style="background:#27AE60;">
                        <i class="fas fa-magnifying-glass mr-1.5"></i> Rechercher
                    </button>
                    <a href="{{ route('tourist-experience.index') }}" class="px-4 py-2.5 rounded-lg border border-black/10 text-sm font-semibold hover:bg-black/5 transition">Réinitialiser</a>
                </div>
            </div>
        </form>

        <p class="text-[#8a7f6b] text-sm mb-4">{{ $experiences->count() }} site(s) trouvé(s)</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($experiences as $experience)
            <a href="{{ route('tourist-experience.activities', $experience->slug) }}" class="venue-card rounded-2xl overflow-hidden block">
                <div class="venue-cover h-44">
                    @php $photo = $experience->media->first(); @endphp
                    @if($photo)
                        <img src="{{ $photo->url }}" alt="{{ $experience->name }}" class="w-full h-full object-cover" loading="lazy">
                    @elseif($experience->cover_image)
                        <img src="{{ $experience->cover_image }}" alt="{{ $experience->name }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-landmark text-3xl"></i></div>
                    @endif
                    <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3 py-1 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                        Sites Touristiques
                    </span>
                </div>
                <div class="p-4">
                    <p class="font-serif font-bold text-lg leading-snug truncate">{{ $experience->name }}</p>
                    <p class="text-[#8a7f6b] text-xs mt-1">
                        <i class="fas fa-location-dot text-[#27AE60]/70 mr-1"></i>
                        {{ $experience->city?->name }}{{ $experience->city?->region_administrative ? ' · '.$experience->city->region_administrative : '' }}
                    </p>

                    @if($experience->short_description)
                    <p class="text-[#5c5548] text-sm mt-2.5 line-clamp-2">{{ $experience->short_description }}</p>
                    @endif

                    <div class="flex items-center justify-between mt-3">
                        @if($experience->provider && $experience->provider->rating_avg)
                        <span class="inline-flex items-center gap-1 text-xs font-bold" style="color:#27AE60;">
                            <i class="fas fa-star text-[10px]"></i> {{ number_format((float) $experience->provider->rating_avg, 1) }}
                        </span>
                        @endif
                        <span class="inline-flex items-center gap-1.5 text-xs font-bold" style="color:#27AE60;">
                            Voir la fiche <i class="fas fa-arrow-right text-[10px]"></i>
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-16 rounded-2xl border border-dashed border-black/10 bg-white/60">
                <i class="fas fa-landmark text-3xl mb-3 block text-[#c9bfa8]"></i>
                <p class="text-[#8a7f6b]">Aucun site ne correspond à ces critères.</p>
            </div>
            @endforelse
        </div>
    </div>

@include('partials.homepage-footer')
@include('partials.image-protection')
</body>
</html>
