@extends('layouts.visitor-public')

@section('title', 'Mes réservations')
@section('page-title', 'Mes réservations')

@section('content')
<div class="max-w-5xl mx-auto">
    @include('partials.visitor-account-nav')

    {{-- Cartes stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $statCards = [
                ['key' => null, 'label' => 'Total', 'value' => $stats['total'], 'icon' => 'fa-layer-group', 'bg' => 'bg-slate-700', 'color' => 'text-slate-300'],
                ['key' => 'upcoming', 'label' => 'À venir', 'value' => $stats['upcoming'], 'icon' => 'fa-clock', 'bg' => 'bg-blue-500/20', 'color' => 'text-blue-300'],
                ['key' => 'ongoing', 'label' => 'En cours', 'value' => $stats['ongoing'], 'icon' => 'fa-hourglass-half', 'bg' => 'bg-emerald-500/20', 'color' => 'text-emerald-300'],
                ['key' => 'completed', 'label' => 'Terminées', 'value' => $stats['completed'], 'icon' => 'fa-circle-check', 'bg' => 'bg-orange-500/20', 'color' => 'text-orange-300'],
                ['key' => 'cancelled', 'label' => 'Annulées', 'value' => $stats['cancelled'], 'icon' => 'fa-circle-xmark', 'bg' => 'bg-rose-500/20', 'color' => 'text-rose-300'],
            ];
        @endphp
        @foreach($statCards as $card)
            <a href="{{ route('visitor.reservations.index', $card['key'] ? ['statut' => $card['key']] : []) }}"
               class="bg-green-900 border rounded-xl p-4 transition {{ $statusFilter === $card['key'] || (!$statusFilter && !$card['key']) ? 'border-orange-500/60' : 'border-slate-800 hover:border-orange-600/40' }}">
                <div class="w-8 h-8 rounded-lg {{ $card['bg'] }} flex items-center justify-center mb-2">
                    <i class="fas {{ $card['icon'] }} {{ $card['color'] }} text-sm"></i>
                </div>
                <p class="text-white text-xl font-bold">{{ $card['value'] }}</p>
                <p class="text-slate-500 text-xs mt-0.5">{{ $card['label'] }}</p>
            </a>
        @endforeach
    </div>

    {{-- Liste --}}
    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
        @forelse($reservations as $reservation)
            @php
                $badgeColors = [
                    'upcoming' => 'text-blue-300 bg-blue-500/10',
                    'ongoing' => 'text-emerald-300 bg-emerald-500/10',
                    'completed' => 'text-orange-300 bg-orange-500/10',
                    'cancelled' => 'text-rose-300 bg-rose-500/10',
                ];
            @endphp
            <div class="flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4 border-b border-slate-800 last:border-b-0">
                <div class="shrink-0 w-11 h-11 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center">
                    <i class="fas fa-hotel text-orange-400/80"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <p class="text-white text-sm font-semibold truncate">{{ $reservation->accommodation_name }}</p>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-medium {{ $badgeColors[$reservation->computed_status] ?? 'text-slate-400 bg-slate-800' }}">
                            {{ $reservation->labelForComputedStatus() }}
                        </span>
                    </div>
                    <p class="text-slate-500 text-xs mt-1">
                        {{ $reservation->accommodation?->type_label }}
                        @if($reservation->accommodation?->city)
                            · {{ $reservation->accommodation->city->name }} ({{ $reservation->accommodation->city->region_administrative }})
                        @endif
                    </p>
                    <p class="text-slate-500 text-xs mt-1">
                        {{ $reservation->check_in->format('d/m/Y') }} → {{ $reservation->check_out->format('d/m/Y') }}
                        · {{ $reservation->nights }} nuit{{ $reservation->nights > 1 ? 's' : '' }}
                        · {{ $reservation->guests_count }} pers.
                        · <span class="text-orange-400/80">{{ $reservation->reference }}</span>
                    </p>
                </div>
                <div class="shrink-0 flex items-center gap-3">
                    <div class="text-right">
                        <p class="text-white text-sm font-semibold">{{ number_format((int) $reservation->total_xof, 0, ',', ' ') }} XOF</p>
                        <p class="text-slate-500 text-[11px] mt-0.5">{{ $reservation->labelForPaymentStatus() }}</p>
                    </div>
                    <a href="{{ route('visitor.reservations.show', $reservation) }}"
                       class="shrink-0 text-xs px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 text-slate-200 transition whitespace-nowrap">
                        Voir les détails
                    </a>
                </div>
            </div>
        @empty
            <div class="px-5 py-14 text-center text-slate-500 text-sm">
                <i class="fas fa-bed text-slate-700 text-3xl mb-3 block"></i>
                <p class="mb-3">{{ $statusFilter ? 'Aucune réservation dans cette catégorie.' : "Vous n'avez pas encore de réservation." }}</p>
                <a href="{{ route('accommodations.index') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-xs font-medium rounded-lg transition">
                    <i class="fas fa-magnifying-glass"></i> Trouver un hôtel ou une résidence
                </a>
            </div>
        @endforelse
    </div>
</div>
@endsection
