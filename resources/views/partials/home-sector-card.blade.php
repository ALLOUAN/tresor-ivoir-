<a href="{{ $it['url'] }}" class="group relative rounded-2xl border border-white/10 overflow-hidden p-4 sm:p-5 hover:border-gold-500/35 hover:-translate-y-1 transition-all duration-300 shadow-lg shadow-green-950/20">
    @if($it['image'])
        <img src="{{ $it['image'] }}" alt="" class="home-provider-card-bg absolute inset-0 w-full h-full object-cover" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-br from-[rgba(20,18,14,0.92)] via-[rgba(20,18,14,0.78)] to-[rgba(20,18,14,0.55)]"></div>
    @else
        <div class="absolute inset-0 bg-dark-700/75"></div>
    @endif

    <div class="relative z-10">
        <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-xl border border-white/10 bg-[rgba(20,18,14,0.55)] backdrop-blur-sm flex items-center justify-center group-hover:border-gold-500/35 group-hover:bg-gold-500/10 transition">
                <i class="fas {{ $it['icon'] }} text-gold-400/70 group-hover:text-gold-300 transition"></i>
            </div>
            <div class="flex flex-col items-end gap-1">
                @if($it['verified'])
                <span class="inline-flex items-center bg-emerald-500/15 text-emerald-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-400/35 backdrop-blur-sm">
                    <i class="fas fa-badge-check mr-1 text-[9px]"></i>Vérifié
                </span>
                @endif
                @if($it['badge'])
                <span class="inline-flex items-center rounded-full bg-black/55 border border-white/20 px-2.5 py-0.5 text-[10px] uppercase tracking-wide text-orange-100 font-bold backdrop-blur-sm">{{ $it['badge'] }}</span>
                @endif
            </div>
        </div>
        @if(!empty($showSector) && !empty($it['sector']))
        <p class="text-gold-400 text-[10px] uppercase tracking-wider font-semibold mb-1">{{ $it['sector'] }}</p>
        @endif
        <p class="text-[#ffffff] text-sm sm:text-[15px] font-semibold font-serif leading-snug line-clamp-2">{{ $it['name'] }}</p>
        @if($it['location'] !== '')
        <p class="text-[rgba(255,255,255,0.72)] text-xs mt-1"><i class="fas fa-location-dot mr-1 opacity-70"></i>{{ $it['location'] }}</p>
        @endif
        @if($it['description'])
        <p class="text-[rgba(255,255,255,0.72)] text-xs mt-2 line-clamp-2">{{ $it['description'] }}</p>
        @endif
        <div class="mt-3 flex items-center justify-between gap-2">
            @if($it['price'])
            <p class="text-[rgba(255,255,255,0.8)] text-xs leading-tight">à partir de<br>
                <span class="text-[#ffffff] font-extrabold text-sm">{{ number_format($it['price'], 0, ',', ' ') }} XOF</span>/nuit
            </p>
            @else
            <span class="text-gold-400 text-xs font-semibold inline-flex items-center gap-1.5">Découvrir <i class="fas fa-arrow-right text-[10px]"></i></span>
            @endif
            @if($it['rating'])
            <span class="inline-flex items-center gap-1 text-xs font-bold text-gold-400">
                <i class="fas fa-star text-[10px]"></i> {{ number_format($it['rating'], 1) }}
            </span>
            @endif
        </div>
    </div>
</a>
