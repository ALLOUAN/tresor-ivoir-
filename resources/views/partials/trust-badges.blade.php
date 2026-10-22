{{-- Badges de confiance d'une fiche. Paramètres : provider (nullable), freeCancelDays (int, hébergements). --}}
@php
    $tbResponse = ($provider ?? null)?->responseBadge();
    $tbFree = (int) ($freeCancelDays ?? 0);
@endphp
@if($tbResponse || $tbFree > 0)
    <div class="flex flex-wrap gap-2 mb-3">
        @if($tbResponse)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#eef6e8] border border-[#31621c]/20 text-[#31621c] text-xs font-bold px-3 py-1.5">
                <i class="fas fa-bolt text-[11px]"></i> {{ $tbResponse }}
            </span>
        @endif
        @if($tbFree > 0)
            <span class="inline-flex items-center gap-1.5 rounded-full bg-[#eef6e8] border border-[#31621c]/20 text-[#31621c] text-xs font-bold px-3 py-1.5">
                <i class="fas fa-rotate-left text-[11px]"></i> {{ trans_choice('Annulation gratuite jusqu\'à :n jour avant|Annulation gratuite jusqu\'à :n jours avant', $tbFree, ['n' => $tbFree]) }}
            </span>
        @endif
    </div>
@endif
