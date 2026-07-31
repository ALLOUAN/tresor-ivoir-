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

        <a href="{{ route('providers.show', $reservation->provider?->slug ?? '') }}"
           class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-400 text-white font-semibold text-sm transition">
            <i class="fas fa-arrow-left text-xs"></i> Retour à la fiche établissement
        </a>
    </div>
</div>
@endsection
