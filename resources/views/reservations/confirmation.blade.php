@extends('layouts.visitor-public')

@section('title', 'Réservation confirmée')
@section('page-title', 'Réservation confirmée')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-emerald-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <i class="fas fa-circle-check text-emerald-600 text-xl"></i>
            </div>
            <div>
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900">Acompte payé avec succès</h1>
                <p class="text-gray-500 text-sm">{{ $reservation->accommodation_name }}</p>
            </div>
        </div>

        <p class="text-gray-600 text-sm leading-relaxed mb-6">
            Votre demande de réservation a été transmise à l'établissement. L'équipe confirmera la disponibilité
            de votre chambre sous peu — vous recevrez la confirmation définitive par e-mail à
            <span class="font-semibold text-gray-800">{{ $reservation->email }}</span>.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Chambre</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $reservation->room_name }}</p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Séjour</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">
                    {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }}
                    <span class="text-gray-400">({{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }})</span>
                </p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Voyageurs</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">
                    {{ $reservation->guests_count }} pers. · {{ $reservation->rooms_count }} chambre{{ $reservation->rooms_count > 1 ? 's' : '' }}
                </p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Référence</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">RES-{{ str_pad((string) $reservation->id, 6, '0', STR_PAD_LEFT) }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 mb-6">
            <div class="flex items-center justify-between text-sm mb-1.5">
                <span class="text-gray-600">Total du séjour</span>
                <span class="text-gray-800 font-medium">{{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} XOF</span>
            </div>
            <div class="flex items-center justify-between text-sm mb-1.5">
                <span class="text-gray-600">Acompte payé en ligne</span>
                <span class="text-emerald-700 font-bold">{{ number_format((int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</span>
            </div>
            <div class="flex items-center justify-between text-sm pt-1.5 border-t border-orange-200/70">
                <span class="text-gray-600">Solde à régler sur place</span>
                <span class="text-gray-800 font-semibold">{{ number_format((int) $reservation->total_xof - (int) $reservation->deposit_amount_xof, 0, ',', ' ') }} XOF</span>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 mb-6">
            <div class="flex items-center gap-2 mb-2">
                <i class="fas fa-id-card text-amber-600"></i>
                <p class="text-gray-800 font-semibold text-sm">Fiche d'enregistrement voyageur</p>
            </div>
            <p class="text-gray-600 text-sm mb-3">
                Conformément à la réglementation du Ministère du Tourisme et des Loisirs.
            </p>
            @if($reservation->guestRegistration)
                <span class="inline-flex items-center gap-2 text-emerald-700 text-sm font-semibold">
                    <i class="fas fa-circle-check"></i> Fiche déjà complétée
                </span>
            @else
                {{-- Ne devrait plus se produire : le paiement de l'acompte exige désormais que la fiche soit remplie au préalable. --}}
                <a href="{{ $reservation->guestRegistrationUrl() }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-500 text-white font-semibold text-sm transition">
                    <i class="fas fa-pen-to-square text-xs"></i> Compléter ma fiche d'enregistrement
                </a>
            @endif
        </div>

        @if($reservation->accommodation && $reservation->accommodation->hasCoordinates())
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 mb-6">
            <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-location-dot text-emerald-600"></i>
                <p class="text-gray-800 font-semibold text-sm">Localisation exacte débloquée</p>
            </div>
            <p class="text-gray-600 text-sm mb-3">
                {{ trim(($reservation->accommodation->adresse ?? '').', '.($reservation->accommodation->quartier ?? ''), ', ') ?: 'Adresse communiquée ci-dessous.' }}
            </p>
            <div class="rounded-xl overflow-hidden border border-emerald-200" style="aspect-ratio:16/8;">
                <iframe src="https://www.google.com/maps?q={{ $reservation->accommodation->latitude }},{{ $reservation->accommodation->longitude }}&output=embed"
                        class="w-full h-full" frameborder="0" loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
            <a href="{{ $reservation->accommodation->google_maps_url }}" target="_blank" rel="noopener noreferrer"
               class="mt-3 inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm transition">
                <i class="fas fa-diamond-turn-right text-xs"></i> Itinéraire
            </a>
        </div>
        @endif

        <div class="flex flex-wrap gap-2">
            <a href="{{ $reservation->receiptUrl() }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 hover:border-orange-300 hover:bg-orange-50 text-gray-800 font-semibold text-sm transition">
                <i class="fas fa-receipt text-xs"></i> Voir mon reçu
            </a>
            <a href="{{ $reservation->receiptPdfUrl() }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-gray-200 hover:border-orange-300 hover:bg-orange-50 text-gray-800 font-semibold text-sm transition">
                <i class="fas fa-download text-xs"></i> Télécharger le PDF
            </a>
            <a href="{{ $reservation->establishmentUrl() }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white font-semibold text-sm transition">
                <i class="fas fa-arrow-left text-xs"></i> Retour à la fiche établissement
            </a>
        </div>
    </div>
</div>
@endsection
