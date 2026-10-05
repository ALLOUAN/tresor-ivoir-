@extends('layouts.visitor-public')

@section('title', 'Visite confirmée')
@section('page-title', 'Visite confirmée')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white border border-emerald-200 rounded-3xl p-6 sm:p-8 shadow-sm">
        <div class="flex items-center gap-3 mb-5">
            <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center shrink-0">
                <i class="fas fa-circle-check text-emerald-600 text-xl"></i>
            </div>
            <div>
                <h1 class="font-serif text-xl sm:text-2xl font-bold text-gray-900">Visite confirmée</h1>
                <p class="text-gray-500 text-sm">{{ $visit->experience_name }}</p>
            </div>
        </div>

        <p class="text-gray-600 text-sm leading-relaxed mb-6">
            Votre réservation a bien été enregistrée. Vous recevrez toutes les informations utiles à
            <span class="font-semibold text-gray-800">{{ $visit->email }}</span>.
        </p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 mb-6">
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Type de visite</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->labelForType() }}</p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Date</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">
                    {{ optional($visit->session_date ?? $visit->desired_date)->format('d/m/Y') ?? 'À convenir avec le prestataire' }}
                    @if($visit->session_time_label) · {{ $visit->session_time_label }} @endif
                </p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Participants</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->participants_count }} personne(s){{ $visit->with_guide ? ' · avec guide' : '' }}</p>
            </div>
            <div class="px-4 py-3 rounded-xl bg-gray-50 border border-gray-100">
                <p class="text-[10px] font-semibold uppercase tracking-widest text-gray-400">Référence</p>
                <p class="text-gray-800 text-sm font-medium mt-0.5">{{ $visit->reference }}</p>
            </div>
        </div>

        <div class="rounded-2xl border border-orange-200 bg-orange-50 px-4 py-4 mb-6">
            <div class="flex items-center justify-between text-sm">
                <span class="text-gray-600">{{ $visit->amount_total_xof > 0 ? 'Montant payé' : 'Visite gratuite' }}</span>
                <span class="text-emerald-700 font-bold">{{ number_format((int) $visit->amount_total_xof, 0, ',', ' ') }} XOF</span>
            </div>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ route('tourist-experience.show', $visit->experience?->slug) }}"
               class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-400 text-white font-semibold text-sm transition">
                <i class="fas fa-arrow-left text-xs"></i> Retour à la fiche du site
            </a>
        </div>
    </div>
</div>
@endsection
