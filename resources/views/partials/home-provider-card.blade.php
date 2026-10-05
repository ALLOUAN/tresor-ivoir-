@php
    $pCoverImg = $p->cover_url
        ?: $p->media->where('type', 'image')->sortBy('sort_order')->first()?->url
        ?: $p->accommodation?->cover_image;
@endphp
<a href="{{ route('providers.show', $p->slug) }}" class="group relative rounded-2xl border border-white/10 overflow-hidden p-4 sm:p-5 hover:border-gold-500/35 hover:-translate-y-1 transition-all duration-300 shadow-lg shadow-green-950/20">
    @if($pCoverImg)
        <img src="{{ $pCoverImg }}" alt="" class="home-provider-card-bg absolute inset-0 w-full h-full object-cover" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-br from-[rgba(20,18,14,0.92)] via-[rgba(20,18,14,0.78)] to-[rgba(20,18,14,0.55)]"></div>
    @else
        <div class="absolute inset-0 bg-dark-700/75"></div>
    @endif

    <div class="relative z-10">
        <div class="flex items-start justify-between mb-3">
            <div class="w-11 h-11 rounded-xl border border-white/10 bg-[rgba(20,18,14,0.55)] backdrop-blur-sm flex items-center justify-center group-hover:border-gold-500/35 group-hover:bg-gold-500/10 transition">
                <i class="fas fa-store text-gold-400/70 group-hover:text-gold-300 transition"></i>
            </div>
            @if($p->is_verified)
            <span class="inline-flex items-center bg-emerald-500/15 text-emerald-300 text-[10px] font-bold px-2.5 py-0.5 rounded-full border border-emerald-400/35 backdrop-blur-sm">
                <i class="fas fa-badge-check mr-1 text-[9px]"></i>Vérifié
            </span>
            @endif
        </div>
        <p class="text-[#ffffff] text-sm sm:text-[15px] font-semibold font-serif leading-snug line-clamp-2">{{ $p->name }}</p>
        <p class="text-[rgba(255,255,255,0.72)] text-xs mt-1">{{ $p->category->name_fr ?? 'Prestataire' }}</p>
        <div class="mt-3 flex items-center justify-between">
            <div class="flex items-center gap-1">
                @for($i = 1; $i <= 5; $i++)
                <i class="fas fa-star text-[10px] {{ $i <= round((float) ($p->rating_avg ?? 0)) ? 'text-gold-400' : 'text-[rgba(255,255,255,0.25)]' }}"></i>
                @endfor
            </div>
            <span class="text-[rgba(255,255,255,0.8)] text-xs">{{ number_format((float) ($p->rating_avg ?? 0), 1) }} ({{ (int) ($p->rating_count ?? 0) }})</span>
        </div>
    </div>
</a>
