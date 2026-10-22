@extends('layouts.app')

@section('title', 'Tables & activités')
@section('page-title', 'Demandes de réservation — restaurants, loisirs, circuits')

@section('content')
@php $types = \App\Models\BookingRequest::TYPES; $statusLabels = \App\Models\BookingRequest::STATUS_LABELS; @endphp
<form method="GET" class="flex flex-wrap gap-2 mb-5">
    <select name="type" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        <option value="">Tous les types</option>
        @foreach($types as $key => $t)
            <option value="{{ $key }}" @selected($type === $key)>{{ $t['label'] }}</option>
        @endforeach
    </select>
    <select name="statut" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        <option value="">Tous les statuts</option>
        @foreach($statusLabels as $key => $label)
            <option value="{{ $key }}" @selected($status === $key)>{{ $label }} ({{ $counts[$key] ?? 0 }})</option>
        @endforeach
    </select>
    <button class="bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
</form>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-x-auto">
    <table class="w-full text-sm min-w-[720px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-800">
                <th class="px-4 py-3">Réf.</th><th class="px-4 py-3">Établissement</th><th class="px-4 py-3">Client</th>
                <th class="px-4 py-3">Date</th><th class="px-4 py-3">Pers.</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3">Paiement</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($bookings as $b)
                <tr class="text-slate-200">
                    <td class="px-4 py-3 font-mono text-xs">{{ $b->reference }}</td>
                    <td class="px-4 py-3">{{ $b->bookable_name }}<br><span class="text-slate-500 text-xs">{{ $b->typeLabel() }}@if($b->item_name) — {{ $b->item_name }}@endif @if($b->pickup_location)<br>{{ $b->pickup_location }} → {{ $b->dropoff_location }}@endif</span></td>
                    <td class="px-4 py-3">{{ $b->full_name }}<br><span class="text-slate-500 text-xs">{{ $b->email }}</span></td>
                    <td class="px-4 py-3">{{ $b->booking_date->format('d/m/Y') }}@if($b->booking_time) {{ $b->booking_time }}@endif</td>
                    <td class="px-4 py-3">{{ $b->party_size }}</td>
                    <td class="px-4 py-3">{{ $b->statusLabel() }}</td>
                    <td class="px-4 py-3">
                        @if((int) $b->amount_due_xof > 0)
                            {{ number_format((int) $b->amount_due_xof, 0, ',', ' ') }} XOF<br><span class="text-slate-500 text-xs">{{ $b->paymentLabel() }}</span>
                            @if($b->payment_status === 'paid' && $b->status === 'cancelled')
                                <form method="POST" action="{{ route('admin.booking-requests.refund', $b) }}" onsubmit="return confirm('Enregistrer le remboursement de cette réservation ?');" class="mt-1">
                                    @csrf
                                    <button class="text-xs text-rose-300 hover:text-rose-200 underline">Rembourser</button>
                                </form>
                            @endif
                        @else
                            <span class="text-slate-500 text-xs">Sur place</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Aucune demande.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $bookings->links() }}</div>
@endsection
