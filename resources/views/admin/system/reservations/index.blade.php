@extends('layouts.app')

@section('title', 'Réservations')
@section('page-title', 'Demandes de réservation')

@section('header-actions')
    <a href="{{ route('admin.reservations.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold px-3 py-2 rounded-lg shrink-0">
        <i class="fas fa-download"></i>
        Exporter (CSV)
    </a>
@endsection

@section('content')

@php
    $statusLabels = \App\Models\Reservation::statusOptions();
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
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
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Ce mois</p>
        <p class="text-green-200 text-2xl font-bold mt-1">{{ number_format($stats['this_month']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-900/40 to-green-900 border border-orange-500/30 rounded-xl p-4 col-span-2 sm:col-span-1">
        <p class="text-orange-300/80 text-xs font-medium">Commissions encaissées</p>
        <p class="text-orange-200 text-xl font-bold mt-1">{{ number_format((int) $stats['commissions_xof']) }} XOF</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-6">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
        <div class="w-10 h-10 rounded-lg bg-orange-600/20 border border-orange-500/40 flex items-center justify-center shrink-0">
            <i class="fas fa-filter text-orange-300 text-sm"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold">Filtres de recherche</h2>
            <p class="text-slate-500 text-xs mt-0.5">Client, hébergement, chambre.</p>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.reservations.index') }}" class="p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label for="q" class="block text-xs text-slate-500 mb-1">Recherche</label>
                <div class="relative">
                    <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" name="q" id="q" value="{{ $q }}"
                           placeholder="Nom, email, hébergement..."
                           class="w-full pl-9 pr-3 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-100">
                </div>
            </div>
            <div>
                <label for="status" class="block text-xs text-slate-500 mb-1">Statut</label>
                <select name="status" id="status" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Tous</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="date_from" class="block text-xs text-slate-500 mb-1">Date début</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom ?? '' }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label for="date_to" class="block text-xs text-slate-500 mb-1">Date fin</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo ?? '' }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-100">
                </div>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-filter"></i>
                Filtrer
            </button>
            <a href="{{ route('admin.reservations.index') }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-600 text-slate-400 hover:text-white hover:bg-slate-700 transition shrink-0"
               title="Effacer les filtres" aria-label="Effacer les filtres">
                <i class="fas fa-xmark"></i>
            </a>
        </div>
    </form>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-white font-semibold">Liste des demandes</h2>
        <span class="text-xs font-semibold text-slate-300 bg-slate-500/15 border border-slate-500/30 px-3 py-1 rounded-full">
            {{ $reservations->total() }} résultat(s)
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Client</th>
                    <th class="text-left px-5 py-3">Hébergement / Chambre</th>
                    <th class="text-left px-5 py-3">Séjour</th>
                    <th class="text-left px-5 py-3">Total</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Paiement</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($reservations as $reservation)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top">
                            <div class="flex gap-2">
                                <span class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                    <i class="fas fa-user text-xs"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-white font-medium truncate">{{ $reservation->full_name }}</p>
                                    <p class="text-slate-500 text-xs truncate">{{ $reservation->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-200 max-w-[220px]">
                            <p class="font-medium truncate">{{ $reservation->accommodation_name }}</p>
                            <p class="text-slate-500 text-xs truncate">{{ $reservation->room_name }}</p>
                        </td>
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
                        <td class="px-5 py-3 align-top whitespace-nowrap">
                            @if($reservation->payment_status === \App\Models\Reservation::PAYMENT_DEPOSIT_PAID)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-500/20 text-orange-300 border border-orange-500/35">
                                    Acompte payé
                                </span>
                                <p class="text-orange-300/70 text-[10px] mt-1">
                                    +{{ number_format((int) $reservation->commission_amount_xof) }} XOF commission
                                </p>
                            @elseif($reservation->payment_status === \App\Models\Reservation::PAYMENT_PENDING)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/35">En attente</span>
                            @elseif($reservation->payment_status === \App\Models\Reservation::PAYMENT_FAILED)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/35">Échoué</span>
                            @elseif($reservation->payment_status === \App\Models\Reservation::PAYMENT_REFUNDED)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-600/30 text-slate-300 border border-slate-500/35">Remboursé</span>
                            @else
                                <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 align-top text-slate-400 whitespace-nowrap text-xs">
                            {{ $reservation->created_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('admin.reservations.show', $reservation) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-orange-500/15 text-orange-400 hover:bg-orange-500/25 border border-orange-500/30 transition mr-1"
                               title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.reservations.destroy', $reservation) }}" class="inline"
                                  onsubmit="return confirm('Supprimer cette réservation ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-500/15 text-rose-400 hover:bg-rose-500/25 border border-rose-500/30 transition"
                                        title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-slate-500">
                            <i class="fas fa-calendar-xmark text-3xl mb-3 opacity-40 block"></i>
                            Aucune réservation pour ces critères.
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
