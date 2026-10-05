@extends('layouts.visitor-public')

@section('title', 'Mes reçus')
@section('page-title', 'Mes reçus')

@section('content')
<div class="max-w-4xl mx-auto">
    @include('partials.visitor-account-nav')

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
        @forelse($reservations as $reservation)
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-slate-800 last:border-b-0">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center">
                    <i class="fas fa-receipt text-orange-400/80"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-semibold truncate">{{ $reservation->accommodation_name }}</p>
                    <p class="text-slate-500 text-xs mt-1">
                        {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }}
                        · <span class="text-orange-400/80">{{ $reservation->reference }}</span>
                        · {{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} XOF
                    </p>
                </div>
                <div class="shrink-0 flex items-center gap-2">
                    <a href="{{ $reservation->receiptUrl() }}"
                       class="text-xs px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition whitespace-nowrap">
                        <i class="fas fa-eye text-[11px] mr-1"></i> Voir
                    </a>
                    <a href="{{ $reservation->receiptPdfUrl() }}"
                       class="text-xs px-3 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-medium transition whitespace-nowrap">
                        <i class="fas fa-download text-[11px] mr-1"></i> PDF
                    </a>
                </div>
            </div>
        @empty
            <div class="px-5 py-14 text-center text-slate-500 text-sm">
                <i class="fas fa-receipt text-slate-700 text-3xl mb-3 block"></i>
                <p>Aucun reçu disponible pour le moment.</p>
                <p class="text-slate-600 text-xs mt-1">Vos reçus apparaissent ici dès qu'un acompte de réservation est payé.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
