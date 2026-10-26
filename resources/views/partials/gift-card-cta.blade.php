{{-- Bouton « Offrir un bon cadeau » (si l'établissement vend des bons). Paramètre : provider. --}}
@if(($provider ?? null) && $provider->gift_cards_enabled && $provider->slug)
    <a href="{{ route('gift-cards.show', $provider->slug) }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-2xl border border-[#EB6E00]/70 bg-[#F3F8F5] text-[#0E3B2A] font-bold text-sm hover:bg-[#F3F8F5] transition">
        <i class="fas fa-gift text-[#EB6E00]"></i> {{ __('Offrir un bon cadeau') }}
    </a>
@endif
