@extends('layouts.app')

@section('title', 'Commande ' . $order->reference)
@section('page-title', 'Commande ' . $order->reference)

@section('header-actions')
    <a href="{{ route('admin.art-orders.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
    </a>
@endsection

@section('content')

<div class="max-w-2xl space-y-6">
    @if($order->status === \App\Models\ArtworkOrder::STATUS_OVERSOLD)
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-5 sm:p-6 text-red-200 text-sm">
            <p class="font-semibold flex items-center gap-2 mb-1"><i class="fas fa-triangle-exclamation"></i> Survente détectée</p>
            <p>Cette pièce était vendue en exemplaire unique. Le paiement de cet acheteur a bien été encaissé par CinetPay, mais l'œuvre avait déjà été vendue à un autre acheteur au moment de la confirmation. Aucune expédition n'est possible — enregistrez le remboursement ci-dessous.</p>
        </div>
    @endif
    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Œuvre & artiste</h3>
        <div class="flex items-center gap-3 mb-4">
            @if(!empty($order->artwork?->images[0]))
                <img src="{{ $order->artwork->images[0] }}" class="w-14 h-14 rounded-lg object-cover border border-slate-700" alt="">
            @endif
            <div>
                <p class="text-white font-medium">{{ $order->artwork?->title }}</p>
                <p class="text-slate-500 text-xs">{{ $order->provider?->name }}</p>
            </div>
        </div>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Acheteur</h3>
        <p class="text-white font-medium">{{ $order->buyer_name }}</p>
        <a href="mailto:{{ $order->buyer_email }}" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $order->buyer_email }}</a>
        @if($order->buyer_phone)
            <p class="text-slate-400 text-sm">{{ $order->buyer_phone }}</p>
        @endif
    </div>

    <div class="bg-green-900 border border-orange-500/25 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Paiement & répartition</h3>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Statut</p>
                <p class="text-white text-sm font-medium">{{ $order->labelForStatus() }}</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Montant total</p>
                <p class="text-white text-sm font-medium">{{ number_format((int) $order->amount_total_xof, 0, ',', ' ') }} XOF</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Commission ({{ rtrim(rtrim(number_format((float) $order->commission_percent, 2), '0'), '.') }}%)</p>
                <p class="text-orange-300 text-sm font-bold">{{ number_format((int) $order->commission_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Part artiste</p>
                <p class="text-emerald-300 text-sm font-bold">{{ number_format((int) $order->artist_net_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
        </div>
        @if($order->paid_at)
            <p class="text-slate-500 text-xs mt-4">Payée le {{ $order->paid_at->format('d/m/Y à H:i') }}</p>
        @endif
        @if($order->shipped_at)
            <p class="text-slate-500 text-xs mt-1">Expédiée le {{ $order->shipped_at->format('d/m/Y à H:i') }}</p>
        @endif
        @if($order->delivered_at)
            <p class="text-slate-500 text-xs mt-1">Livrée le {{ $order->delivered_at->format('d/m/Y à H:i') }} — solde artiste libéré</p>
        @endif
    </div>

    @if(in_array($order->status, [\App\Models\ArtworkOrder::STATUS_PAID, \App\Models\ArtworkOrder::STATUS_SHIPPED, \App\Models\ArtworkOrder::STATUS_DELIVERED, \App\Models\ArtworkOrder::STATUS_OVERSOLD], true))
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
            <h3 class="text-white font-semibold text-sm mb-4">Remboursement</h3>
            <form method="POST" action="{{ route('admin.art-orders.refund', $order) }}"
                  onsubmit="return confirm('Enregistrer ce remboursement ? Cette action reprend le solde de l\'artiste (disponible puis en attente) et marque la commande comme remboursée.');"
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
            <p class="text-slate-600 text-[11px] mt-2">Remboursement manuel — l'exécution réelle (CinetPay) reste à effectuer hors système ; cette action reprend la part artiste (disponible et/ou en attente) et marque la commande comme remboursée.</p>
        </div>
    @endif
</div>

@endsection
