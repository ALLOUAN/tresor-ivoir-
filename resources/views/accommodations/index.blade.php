<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résidences &amp; Hôtels — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary {
            background: #f2790f;
            box-shadow: 0 10px 24px rgba(242,121,15,0.3);
            transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .hero-lodging {
            background:
                radial-gradient(120% 100% at 15% 0%, rgba(242,121,15,0.16), transparent 55%),
                linear-gradient(150deg, #241e14 0%, #14130f 65%);
        }
        .filter-card { border: 1px solid rgba(0,0,0,0.08); background: #ffffff; box-shadow: 0 14px 34px rgba(20,18,12,0.08); }
        .accom-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .accom-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(194,94,10,0.14); border-color: rgba(242,121,15,0.3); }
        .accom-cover { position: relative; overflow: hidden; background: #14130f; }
        .accom-cover img { transition: transform .5s ease; }
        .accom-card:hover .accom-cover img { transform: scale(1.06); }
        #filterPanel { transition: none; }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <section class="hero-lodging relative py-20 overflow-hidden">
        <div class="max-w-6xl mx-auto px-6 text-center relative z-10">
            <div class="inline-block rounded-2xl bg-green-950/70 backdrop-blur-md px-6 py-8 sm:px-12 sm:py-10">
                <p class="text-orange-300 text-sm font-medium uppercase tracking-widest mb-3">Résidences &amp; Hôtels</p>
                <h1 class="font-serif text-4xl md:text-5xl font-bold text-white mb-4">Trouvez votre hébergement idéal</h1>
                <p class="text-gray-200 text-xl max-w-2xl mx-auto">Hôtels et résidences vérifiés partout en Côte d'Ivoire — réservation en ligne avec acompte sécurisé.</p>
            </div>
        </div>
    </section>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 -mt-8 relative z-10 pb-16">

        <button type="button" id="filterToggleBtn" class="lg:hidden w-full mb-4 flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white border border-black/10 shadow-md font-semibold text-sm">
            <i class="fas fa-sliders"></i> Filtrer
        </button>

        <div id="filterPanel"
             class="hidden lg:!block lg:!static lg:!inset-auto lg:!bg-transparent lg:!p-0 lg:!z-auto fixed inset-0 z-50 bg-[#14130f]/95 p-5 overflow-y-auto">
            <div class="flex items-center justify-between mb-4 lg:hidden">
                <p class="text-white font-semibold">Filtrer les résultats</p>
                <button type="button" id="filterCloseBtn" class="w-9 h-9 rounded-full bg-white/10 text-white flex items-center justify-center"><i class="fas fa-xmark"></i></button>
            </div>

            <form method="GET" action="{{ route('accommodations.index') }}" class="filter-card rounded-2xl p-5 mb-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Région</label>
                        <select name="region" id="regionSelect" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">Toutes régions</option>
                            @foreach($regions as $r)
                                <option value="{{ $r }}" @selected($region === $r)>{{ $r }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Ville</label>
                        <select name="ville" id="citySelect" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">Toutes villes</option>
                            @foreach($cities as $c)
                                <option value="{{ $c->id }}" @selected((string) $cityId === (string) $c->id)>{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Type</label>
                        <select name="type" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">Tous types</option>
                            @foreach(['hotel'=>'Hôtel','residence'=>'Résidence','resort'=>'Resort','guesthouse'=>'Maison d\'hôtes','hostel'=>'Auberge de jeunesse','auberge'=>'Auberge','villa'=>'Villa','eco_lodge'=>'Éco-lodge'] as $val => $label)
                                <option value="{{ $val }}" @selected($type === $val)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Arrivée</label>
                        <input type="date" name="arrivee" value="{{ $checkIn }}" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Départ</label>
                        <input type="date" name="depart" value="{{ $checkOut }}" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Voyageurs</label>
                        <input type="number" name="voyageurs" min="1" max="30" value="{{ $guests ?: '' }}" placeholder="2" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-3 mt-3">
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Budget (à partir de, par nuit)</label>
                        <select name="budget" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="">Tous budgets</option>
                            <option value="low" @selected($budget==='low')>Moins de 50 000 XOF</option>
                            <option value="mid" @selected($budget==='mid')>50 000 – 100 000 XOF</option>
                            <option value="high" @selected($budget==='high')>100 000 – 200 000 XOF</option>
                            <option value="luxury" @selected($budget==='luxury')>Plus de 200 000 XOF</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2">
                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-[#8a7f6b] mb-1">Trier par</label>
                        <select name="tri" class="w-full bg-[#f6f3ed] border border-black/10 rounded-lg px-3 py-2.5 text-sm">
                            <option value="pertinence" @selected($sort==='pertinence')>Pertinence</option>
                            <option value="prix_asc" @selected($sort==='prix_asc')>Prix croissant</option>
                            <option value="prix_desc" @selected($sort==='prix_desc')>Prix décroissant</option>
                            <option value="popularite" @selected($sort==='popularite')>Popularité</option>
                        </select>
                    </div>
                    <div class="lg:col-span-2 flex items-end gap-2">
                        <button type="submit" class="btn-primary btn-shine flex-1 px-4 py-2.5 rounded-lg font-bold text-sm">
                            <i class="fas fa-magnifying-glass mr-1.5"></i> Rechercher
                        </button>
                        <a href="{{ route('accommodations.index') }}" class="px-4 py-2.5 rounded-lg border border-black/10 text-sm font-semibold hover:bg-black/5 transition">Réinitialiser</a>
                    </div>
                </div>
            </form>
        </div>

        <p class="text-[#8a7f6b] text-sm mb-4">{{ $accommodations->count() }} hébergement(s) trouvé(s)</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($accommodations as $a)
            <div class="accom-card rounded-2xl overflow-hidden">
                <div class="accom-cover h-44">
                    @php $photo = $a->media->first(); @endphp
                    @if($photo)
                        <img src="{{ $photo->url }}" alt="{{ $a->name }}" class="w-full h-full object-cover" loading="lazy">
                    @elseif($a->cover_image)
                        <img src="{{ $a->cover_image }}" alt="{{ $a->name }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-hotel text-3xl"></i></div>
                    @endif
                    <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3 py-1 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                        {{ $a->type_label }}
                    </span>
                    @if($checkIn && $checkOut)
                    <span class="absolute top-3 right-3 inline-flex items-center gap-1 rounded-full bg-emerald-500/90 px-2.5 py-1 text-[10px] font-bold text-white">
                        <i class="fas fa-circle-check text-[9px]"></i> Disponible
                    </span>
                    @endif
                </div>
                <div class="p-4">
                    <p class="font-serif font-bold text-lg leading-snug truncate">{{ $a->name }}</p>
                    <p class="text-[#8a7f6b] text-xs mt-1">
                        <i class="fas fa-location-dot text-[#d4630a]/70 mr-1"></i>
                        {{ $a->city?->name }}{{ $a->city?->region_administrative ? ' · '.$a->city->region_administrative : '' }}
                    </p>

                    @if(!empty($a->amenities))
                    <div class="flex flex-wrap gap-1.5 mt-2.5">
                        @foreach(collect($a->amenities)->take(3) as $am)
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-orange-500/10 text-[#c25e0a] font-semibold">{{ $am['label'] ?? '' }}</span>
                        @endforeach
                    </div>
                    @endif

                    <div class="flex items-center justify-between mt-3">
                        @if($a->starting_price_xof)
                        <p class="text-sm"><span class="text-[#8a7f6b] text-xs">à partir de</span><br>
                            <span class="font-extrabold text-[#c25e0a]">{{ number_format($a->starting_price_xof, 0, ',', ' ') }} XOF</span><span class="text-[#8a7f6b] text-xs">/nuit</span>
                        </p>
                        @else
                        <p class="text-xs text-[#8a7f6b]">Tarif sur demande</p>
                        @endif
                        @if($a->provider && $a->provider->rating_avg)
                        <span class="inline-flex items-center gap-1 text-xs font-bold text-[#c25e0a]">
                            <i class="fas fa-star text-[10px]"></i> {{ number_format((float) $a->provider->rating_avg, 1) }}
                        </span>
                        @endif
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <a href="{{ route('accommodations.rooms', $a->slug) }}" class="flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg border border-black/10 hover:border-orange-400/50 hover:bg-orange-50 text-xs font-bold transition">
                            <i class="fas fa-eye text-[11px]"></i> Voir les détails
                        </a>
                        <a href="{{ route('accommodations.rooms', $a->slug) }}" class="btn-primary btn-shine flex-1 inline-flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-lg text-xs font-bold">
                            <i class="fas fa-bed text-[11px]"></i> Réserver
                        </a>
                    </div>
                </div>
            </div>
            @empty
            <div class="col-span-full text-center py-16 rounded-2xl border border-dashed border-black/10 bg-white/60">
                <i class="fas fa-hotel text-3xl mb-3 block text-[#c9bfa8]"></i>
                <p class="text-[#8a7f6b]">Aucun hébergement ne correspond à ces critères. Essayez d'élargir votre recherche.</p>
            </div>
            @endforelse
        </div>
    </div>

@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
(function () {
    const toggleBtn = document.getElementById('filterToggleBtn');
    const closeBtn = document.getElementById('filterCloseBtn');
    const panel = document.getElementById('filterPanel');
    if (toggleBtn && panel) toggleBtn.addEventListener('click', () => panel.classList.remove('hidden'));
    if (closeBtn && panel) closeBtn.addEventListener('click', () => panel.classList.add('hidden'));

    const regionSelect = document.getElementById('regionSelect');
    const citySelect = document.getElementById('citySelect');
    if (!regionSelect || !citySelect) return;

    const citiesUrl = @json(route('accommodations.cities'));
    const initialCityId = @json($cityId ? (string) $cityId : '');

    regionSelect.addEventListener('change', () => {
        const region = regionSelect.value;
        citySelect.innerHTML = '<option value="">Toutes villes</option>';
        if (!region) return;

        fetch(`${citiesUrl}?region=${encodeURIComponent(region)}`, { headers: { 'Accept': 'application/json' } })
            .then((r) => r.json())
            .then((data) => {
                (data.cities || []).forEach((c) => {
                    const opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.name;
                    if (String(c.id) === initialCityId) opt.selected = true;
                    citySelect.appendChild(opt);
                });
            });
    });
})();
</script>
</body>
</html>
