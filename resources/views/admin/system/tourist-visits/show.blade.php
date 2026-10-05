@extends('layouts.app')

@section('title', 'Visite ' . $visit->reference)
@section('page-title', 'Visite ' . $visit->reference)

@section('header-actions')
    <a href="{{ route('admin.tourist-visits.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
    </a>
@endsection

@section('content')

<div class="max-w-2xl space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 bg-rose-900/30 border border-rose-800 text-rose-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation shrink-0"></i> {{ session('error') }}
    </div>
    @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Site & prestataire</h3>
        <p class="text-white font-medium">{{ $visit->experience_name }}</p>
        <p class="text-slate-500 text-xs">{{ $visit->provider?->name }}</p>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Visiteur</h3>
        <p class="text-white font-medium">{{ $visit->full_name }}</p>
        <a href="mailto:{{ $visit->email }}" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $visit->email }}</a>
        @if($visit->phone)
            <p class="text-slate-400 text-sm">{{ $visit->phone }}</p>
        @endif
    </div>

    <div class="bg-green-900 border border-orange-500/25 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Visite & paiement</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Type</p>
                <p class="text-white text-sm font-medium">{{ $visit->labelForType() }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Participants</p>
                <p class="text-white text-sm font-medium">{{ $visit->participants_count }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Statut</p>
                <p class="text-white text-sm font-medium">{{ $visit->labelForStatus() }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Date</p>
                <p class="text-white text-sm font-medium">{{ optional($visit->session_date ?? $visit->desired_date)->format('d/m/Y') ?? '—' }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Montant total</p>
                <p class="text-white text-sm font-medium">{{ number_format((int) $visit->amount_total_xof, 0, ',', ' ') }} XOF</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Commission ({{ rtrim(rtrim(number_format((float) $visit->commission_percent, 2), '0'), '.') }}%)</p>
                <p class="text-orange-300 text-sm font-bold">{{ number_format((int) $visit->commission_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Part prestataire</p>
                <p class="text-emerald-300 text-sm font-bold">{{ number_format((int) $visit->provider_net_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Moyen de paiement</p>
                <p class="text-white text-sm font-medium">{{ $visit->gateway ? ucfirst($visit->gateway) : '—' }}</p>
            </div>
        </div>
        @if($visit->paid_at)
            <p class="text-slate-500 text-xs mt-4">Payée le {{ $visit->paid_at->format('d/m/Y à H:i') }}</p>
        @endif
        @if($visit->cancelled_at)
            <p class="text-slate-500 text-xs mt-1">Annulée le {{ $visit->cancelled_at->format('d/m/Y à H:i') }}</p>
        @endif
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Changer le statut</h3>
        <form method="POST" action="{{ route('admin.tourist-visits.update', $visit) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            @method('PATCH')
            <div>
                <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    @foreach(\App\Models\TouristVisit::STATUS_LABELS as $value => $label)
                        <option value="{{ $value }}" @selected($visit->status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-check"></i> Mettre à jour
            </button>
        </form>
    </div>

    @if($visit->status === \App\Models\TouristVisit::STATUS_PAID)
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
            <h3 class="text-white font-semibold text-sm mb-4">Remboursement</h3>
            <form method="POST" action="{{ route('admin.tourist-visits.refund', $visit) }}"
                  onsubmit="return confirm('Enregistrer ce remboursement ? Cette action reprend le solde du prestataire et marque la visite comme remboursée.');"
                  class="flex flex-wrap items-end gap-3">
                @csrf
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-slate-400 text-xs mb-1">Note (optionnel)</label>
                    <input type="text" name="note" maxlength="500" placeholder="Motif du remboursement"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <button type="submit" class="inline-flex items-center gap-2 bg-rose-600/80 hover:bg-rose-500 text-white text-sm font-semibold px-4 py-2.5 rounded-lg">
                    <i class="fas fa-rotate-left text-xs"></i> Enregistrer le remboursement
                </button>
            </form>
            <p class="text-slate-600 text-[11px] mt-2">Remboursement manuel — l'exécution réelle (CinetPay) reste à effectuer hors système ; cette action reprend la part prestataire et marque la visite comme remboursée.</p>
        </div>
    @endif
</div>

@endsection
