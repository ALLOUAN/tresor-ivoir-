{{-- « Voir sur mon mur » : l'œuvre à l'échelle au-dessus d'un canapé (2 m) et à côté d'une silhouette (1,70 m). --}}
@php $w = (float) $artwork->width_cm; $h = (float) $artwork->height_cm; @endphp
@if($w > 0 && $h > 0 && ! empty($artwork->images[0]))
    <button type="button" id="wall-open" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-orange-700 hover:underline"><i class="fas fa-couch"></i>{{ __('Voir sur mon mur') }}</button>
    <div id="wall-modal" class="hidden fixed inset-0 z-[70] bg-black/60 p-4 items-center justify-center" role="dialog" aria-modal="true" aria-label="{{ __('Voir sur mon mur') }}">
        <div class="relative w-full max-w-3xl rounded-2xl bg-white p-4 shadow-2xl">
            <div class="flex items-center justify-between gap-3 mb-3">
                <p class="font-serif text-lg font-bold">{{ __('À l\'échelle sur votre mur') }}</p>
                <button type="button" id="wall-close" class="w-9 h-9 rounded-full hover:bg-black/5" aria-label="{{ __('Fermer') }}"><i class="fas fa-xmark"></i></button>
            </div>
            <div id="wall-scene" class="relative w-full overflow-hidden rounded-xl" style="aspect-ratio: 16 / 9; background: #D8E9E0;">
                {{-- 1 % de largeur de scène = 4 cm (scène de 4 m de large) --}}
                <img src="{{ $artwork->images[0] }}" alt="{{ $artwork->title }}" class="absolute shadow-[0_12px_28px_rgba(0,0,0,.35)] object-cover"
                     style="width: {{ min(95, $w / 4) }}%; height: {{ min(80, $h / 2.25) }}%; left: {{ 50 - min(95, $w / 4) / 2 }}%; bottom: {{ 34 + 5 }}%;">
                <div class="absolute bottom-0 inset-x-0 h-[14%] bg-[#EB6E00]"></div>
                <div class="absolute rounded-t-2xl bg-[#557466]" style="width: 50%; height: 18%; left: 25%; bottom: 14%;" aria-hidden="true"></div>
                <div class="absolute rounded-xl bg-[#557466]" style="width: 54%; height: 7%; left: 23%; bottom: 14%;" aria-hidden="true"></div>
                <svg class="absolute" style="height: 75.5%; right: 8%; bottom: 14%;" viewBox="0 0 40 170" aria-hidden="true"><g fill="#24503D" opacity=".55"><circle cx="20" cy="12" r="11"/><rect x="9" y="25" width="22" height="70" rx="9"/><rect x="10" y="90" width="8" height="80" rx="4"/><rect x="22" y="90" width="8" height="80" rx="4"/></g></svg>
            </div>
            <div class="flex flex-wrap items-center gap-2 mt-3 text-xs text-[#557466]">
                <span>{{ __('Couleur du mur :') }}</span>
                @foreach(['#D8E9E0', '#F3F8F5', '#D8E9E0', '#24503D', '#EB6E00'] as $c)
                    <button type="button" class="wall-color w-7 h-7 rounded-full border border-black/15" style="background: {{ $c }}" data-color="{{ $c }}" aria-label="{{ __('Mur :c', ['c' => $c]) }}"></button>
                @endforeach
                <span class="ml-auto">{{ __('Œuvre : :w × :h cm · canapé 2 m · silhouette 1,70 m', ['w' => $w, 'h' => $h]) }}</span>
            </div>
        </div>
    </div>
    <script>
    (function () {
        const m = document.getElementById('wall-modal');
        document.getElementById('wall-open').addEventListener('click', () => { m.classList.remove('hidden'); m.classList.add('flex'); });
        document.getElementById('wall-close').addEventListener('click', () => { m.classList.add('hidden'); m.classList.remove('flex'); });
        m.addEventListener('click', e => { if (e.target === m) { m.classList.add('hidden'); m.classList.remove('flex'); } });
        document.querySelectorAll('.wall-color').forEach(b => b.addEventListener('click', () => document.getElementById('wall-scene').style.background = b.dataset.color));
    })();
    </script>
@endif
