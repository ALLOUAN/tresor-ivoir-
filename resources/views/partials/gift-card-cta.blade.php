{{-- Bouton « Offrir un bon cadeau » (si l'établissement vend des bons). Paramètre : provider. --}}
@if(($provider ?? null) && $provider->gift_cards_enabled && $provider->slug)
    <a href="{{ route('gift-cards.show', $provider->slug) }}" class="flex items-center justify-center gap-2 w-full px-4 py-3 rounded-2xl border border-[#febf48]/70 bg-[#fff8e6] text-[#1c1915] font-bold text-sm hover:bg-[#fff1cc] transition">
        <i class="fas fa-gift text-[#c25e0a]"></i> {{ __('Offrir un bon cadeau') }}
    </a>
@endif
