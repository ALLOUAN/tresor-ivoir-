@extends('layouts.app')

@section('title', 'Réservations')
@section('page-title', 'Réservations reçues')

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Total</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-rose-900/40 to-green-900 border border-rose-500/30 rounded-xl p-4">
        <p class="text-rose-300/80 text-xs font-medium">Nouvelles</p>
        <p class="text-rose-200 text-2xl font-bold mt-1">{{ number_format($stats['new']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Confirmées</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($stats['confirmed']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-slate-800/60 to-green-900 border border-slate-600/30 rounded-xl p-4">
        <p class="text-slate-400 text-xs font-medium">Annulées</p>
        <p class="text-slate-300 text-2xl font-bold mt-1">{{ number_format($stats['cancelled']) }}</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-6">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center gap-2">
        <a href="{{ route('provider.reservations.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === '' ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
            Toutes
        </a>
        @foreach(\App\Models\Reservation::statusOptions() as $value => $label)
            <a href="{{ route('provider.reservations.index', ['status' => $value]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === $value ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Client</th>
                    <th class="text-left px-5 py-3">Chambre</th>
                    <th class="text-left px-5 py-3">Séjour</th>
                    <th class="text-left px-5 py-3">Total</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($reservations as $reservation)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top">
                            <p class="text-white font-medium truncate">{{ $reservation->full_name }}</p>
                            <p class="text-slate-500 text-xs truncate">{{ $reservation->email }}</p>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-200">{{ $reservation->room_name }}</td>
                        <td class="px-5 py-3 align-top text-slate-400 text-xs whitespace-nowrap">
                            {{ $reservation->check_in?->format('d/m/Y') }} → {{ $reservation->check_out?->format('d/m/Y') }}
                            <br>{{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }} · {{ $reservation->rooms_count }} ch. · {{ $reservation->guests_count }} pers.
                        </td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">
                            {{ $reservation->total_xof ? number_format($reservation->total_xof, 0, ',', ' ').' XOF' : '—' }}
                        </td>
                        <td class="px-5 py-3 align-top whitespace-nowrap">
                            @if($reservation->status === \App\Models\Reservation::STATUS_NEW)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/35">Nouvelle</span>
                            @elseif($reservation->status === \App\Models\Reservation::STATUS_CONFIRMED)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-500/35">Confirmée</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-600/30 text-slate-300 border border-slate-500/35">Annulée</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('provider.reservations.show', $reservation) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-green-500/15 text-green-400 hover:bg-green-500/25 border border-green-500/30 transition"
                               title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            <i class="fas fa-calendar-xmark text-3xl mb-3 opacity-40 block"></i>
                            Aucune réservation pour le moment.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $reservations->links() }}
        </div>
    @endif
</div>

@endsection
