<!DOCTYPE html>
<html lang="fr" id="html-root" class="overflow-x-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chambres — {{ $accommodation->name }} — {{ $siteBrand['site_name'] }}</title>
    @include('partials.theme-init')
    @include('partials.theme-light-bridge')
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { font-family: 'Inter', sans-serif; background: #f6f3ed; color: #1c1915; }
        .font-serif { font-family: 'Playfair Display', serif; }
        .btn-primary {
            background: #f2790f;
            box-shadow: 0 12px 28px rgba(242,121,15,0.35);
            transition: transform .22s ease, filter .22s ease;
            color: #1b1408;
        }
        .btn-primary:hover { transform: translateY(-2px); filter: brightness(1.05); }
        .section-kicker { letter-spacing: .22em; }
        .room-card {
            border: 1px solid rgba(0,0,0,0.07);
            background: linear-gradient(180deg, #ffffff, #fbf8f2);
            box-shadow: 0 10px 28px rgba(20,18,12,0.06);
            transition: transform .25s ease, box-shadow .25s ease, border-color .25s ease;
        }
        .room-card:hover { transform: translateY(-4px); box-shadow: 0 18px 40px rgba(194,94,10,0.14); border-color: rgba(242,121,15,0.3); }
        .room-cover { position: relative; overflow: hidden; background: #14130f; cursor: zoom-in; }
        .room-cover img { transition: transform .5s ease; }
        .room-card:hover .room-cover img { transform: scale(1.06); }
    </style>
</head>
<body class="text-[#1c1915]">
    @include('partials.page-background')
    @include('partials.public-top-nav')

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8 sm:py-12">
        <a href="{{ route('accommodations.show', $accommodation->slug) }}" class="inline-flex items-center gap-2 text-[#c25e0a] hover:text-[#a24d08] transition text-sm font-semibold">
            <i class="fas fa-arrow-left text-[11px]"></i> {{ $accommodation->name }}
        </a>

        <div class="mt-4 mb-8">
            <p class="section-kicker text-[#c25e0a] text-xs font-bold uppercase mb-2">{{ $accommodation->name }}</p>
            <h1 class="font-serif text-3xl sm:text-4xl font-bold mb-2">Chambres &amp; logements disponibles</h1>
            <p class="text-[#8a7f6b] text-sm">
                <i class="fas fa-location-dot text-[#d4630a]/70 mr-1"></i>{{ $accommodation->city?->name }}
                — Choisissez une chambre pour poursuivre votre réservation.
            </p>
        </div>

        @if(!empty($accommodation->room_types))
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
            @foreach($accommodation->room_types as $room)
            @php $roomPhotos = array_values(array_filter((array) ($room['photos'] ?? []))); @endphp
            <div class="room-card rounded-2xl overflow-hidden flex flex-col">
                <div class="room-cover h-48" @if(!empty($roomPhotos)) onclick="openLightbox('{{ $roomPhotos[0] }}')" @endif>
                    @if(!empty($roomPhotos))
                        <img src="{{ $roomPhotos[0] }}" alt="{{ $room['name'] ?? 'Chambre' }}" class="w-full h-full object-cover" loading="lazy">
                    @else
                        <div class="w-full h-full flex items-center justify-center text-white/30"><i class="fas fa-bed text-3xl"></i></div>
                    @endif
                    <div class="absolute inset-0 bg-linear-to-t from-black/50 via-transparent to-transparent"></div>
                    @if(count($roomPhotos) > 1)
                    <span class="absolute bottom-3 right-3 inline-flex items-center gap-1 rounded-full bg-black/55 border border-white/20 px-2.5 py-1 text-[10px] text-white font-semibold backdrop-blur">
                        <i class="fas fa-images text-[9px]"></i> {{ count($roomPhotos) }}
                    </span>
                    @endif
                    @if(!empty($room['price_xof']))
                    <span class="absolute top-3 left-3 inline-flex items-center rounded-full bg-black/55 border border-white/20 px-3 py-1 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur">
                        {{ number_format((int) $room['price_xof'], 0, ',', ' ') }} XOF/nuit
                    </span>
                    @endif
                </div>

                <div class="p-4 flex flex-col flex-1">
                    <p class="font-serif font-bold text-lg leading-snug">{{ $room['name'] ?? 'Chambre' }}</p>
                    <p class="text-[#8a7f6b] text-xs mt-1.5">
                        <i class="fas fa-user mr-1"></i>{{ $room['max_adults'] ?? 2 }} pers. max
                        @if(!empty($room['beds'])) · <i class="fas fa-bed mr-0.5"></i>{{ $room['beds'] }} @endif
                        @if(!empty($room['area_m2'])) · {{ $room['area_m2'] }} m² @endif
                    </p>

                    @if(!empty($room['description']))
                    <p class="text-[#5c5548] text-sm mt-2.5 leading-relaxed">{{ $room['description'] }}</p>
                    @endif

                    @if(!empty($room['amenities']))
                    <div class="flex flex-wrap gap-1.5 mt-3">
                        @foreach((is_array($room['amenities']) ? $room['amenities'] : [$room['amenities']]) as $amenity)
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-orange-500/10 text-[#c25e0a] font-semibold">{{ $amenity }}</span>
                        @endforeach
                    </div>
                    @endif

                    @if(!empty($room['conditions']))
                    <p class="text-[#8a7f6b] text-[11px] mt-3 pt-3 border-t border-black/5 leading-relaxed">
                        <i class="fas fa-circle-info mr-1"></i>{{ $room['conditions'] }}
                    </p>
                    @endif

                    <div class="flex-1"></div>

                    @if(!empty($room['price_xof']))
                    <a href="{{ route('accommodations.show', $accommodation->slug) }}?room={{ urlencode($room['name'] ?? 'Chambre') }}#booking-module"
                       class="btn-primary btn-shine mt-4 inline-flex items-center justify-center gap-2 w-full px-4 py-2.5 rounded-xl font-extrabold text-sm">
                        <i class="fas fa-bed text-sm"></i> Réserver cette chambre
                    </a>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
        @else
        <div class="room-card rounded-2xl py-16 text-center">
            <i class="fas fa-bed text-3xl text-[#c9bfa8] mb-3 block"></i>
            <p class="text-[#8a7f6b] text-sm">Aucune chambre renseignée pour cet établissement pour le moment.</p>
        </div>
        @endif
    </div>

    <div id="lightbox" class="fixed inset-0 z-50 hidden bg-[#0a0907]/96 items-center justify-center p-4" onclick="closeLightbox()">
        <button class="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition" onclick="closeLightbox()">
            <i class="fas fa-xmark"></i>
        </button>
        <img id="lightbox-img" src="" alt="" class="max-w-full max-h-[90vh] rounded-xl object-contain" onclick="event.stopPropagation()">
    </div>

@include('partials.homepage-footer')
@include('partials.image-protection')
<script>
function openLightbox(url) {
    const img = document.getElementById('lightbox-img');
    img.src = url;
    const lb = document.getElementById('lightbox');
    lb.classList.remove('hidden'); lb.classList.add('flex');
    document.body.style.overflow = 'hidden';
}
function closeLightbox() {
    const lb = document.getElementById('lightbox');
    lb.classList.add('hidden'); lb.classList.remove('flex');
    document.body.style.overflow = '';
}
</script>
</body>
</html>
