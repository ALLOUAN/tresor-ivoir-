@extends('layouts.app')

@section('title', 'Litiges')
@section('page-title', 'Litiges sur les réservations')

@section('content')
@php $statusLabels = \App\Models\Dispute::STATUS_LABELS; @endphp
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

<div class="flex flex-wrap gap-2 mb-5">
    @foreach($statusLabels as $key => $label)
        <a href="{{ route('admin.disputes.index', ['statut' => $key]) }}"
           class="px-3 py-1.5 rounded-lg text-sm border transition {{ $status === $key ? 'bg-orange-500 border-orange-500 text-black font-semibold' : 'border-slate-700 text-slate-300 hover:text-white' }}">
            {{ $label }} <span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<div class="space-y-4">
    @forelse($disputes as $d)
        @php $r = $d->reservation; @endphp
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $d->reasonLabel() }}</p>
                    <p class="text-slate-400 text-xs mt-0.5">
                        <a href="{{ route('admin.reservations.show', $r) }}" class="text-orange-300 hover:text-orange-200">{{ $r?->reference }}</a>
                        · {{ $r?->accommodation_name }} · client {{ $r?->full_name }}
                        · séjour {{ $r?->check_in?->format('d/m/Y') }} → {{ $r?->check_out?->format('d/m/Y') }}
                        · acompte {{ number_format((int) $r?->deposit_amount_xof, 0, ',', ' ') }} XOF ({{ $r?->labelForPaymentStatus() }})
                    </p>
                </div>
                <span class="text-xs text-slate-500">Ouvert le {{ $d->created_at->format('d/m/Y H:i') }}</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3">
                <div class="bg-slate-800/60 rounded-lg px-3 py-2">
                    <p class="text-slate-500 text-[11px] mb-1">Client</p>
                    <p class="text-slate-200 text-sm whitespace-pre-line">{{ $d->description }}</p>
                </div>
                <div class="bg-slate-800/60 rounded-lg px-3 py-2">
                    <p class="text-slate-500 text-[11px] mb-1">Établissement</p>
                    <p class="text-slate-200 text-sm whitespace-pre-line">{{ $d->provider_response ?: 'Pas encore de réponse.' }}</p>
                </div>
            </div>

            @if($d->isOpen())
                <form method="POST" action="{{ route('admin.disputes.resolve', $d) }}" class="mt-4 pt-4 border-t border-slate-800 grid grid-cols-1 md:grid-cols-4 gap-2 items-start"
                      onsubmit="return confirm('Clôturer ce litige avec cette décision ?');">
                    @csrf
                    <select name="decision" required class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="client">En faveur du client (rembourser)</option>
                        <option value="provider">En faveur de l'établissement</option>
                    </select>
                    <input type="number" name="refund_xof" min="0" max="{{ (int) $r?->deposit_amount_xof }}" value="{{ (int) $r?->deposit_amount_xof }}"
                           title="Montant remboursé au client (si décision en sa faveur)"
                           class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <input type="text" name="resolution" required maxlength="2000" placeholder="Motif de la décision (envoyé aux deux parties)"
                           class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <button type="submit" class="md:col-span-4 justify-self-start inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
                        <i class="fas fa-gavel text-xs"></i> Rendre la décision
                    </button>
                </form>
            @else
                <p class="text-emerald-300 text-sm mt-3">{{ $d->statusLabel() }} — {{ $d->resolution }}
                    <span class="text-slate-500 text-xs">({{ $d->resolver?->full_name }}, {{ $d->resolved_at?->format('d/m/Y') }})</span></p>
            @endif
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center text-slate-400 text-sm">Aucun litige dans cette liste.</div>
    @endforelse
</div>
<div class="mt-5">{{ $disputes->links() }}</div>
@endsection
