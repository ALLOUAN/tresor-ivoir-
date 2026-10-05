@extends('layouts.app')

@section('title', 'Commande ' . $order->reference)
@section('page-title', 'Commande ' . $order->reference)

@section('header-actions')
    <a href="{{ route('provider.art-orders.index') }}"
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

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
        <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-slate-500 text-xs uppercase tracking-wide">Œuvre vendue</p>
                <h2 class="text-white font-serif text-xl font-semibold mt-1 break-words">{{ $order->artwork?->title }}</h2>
            </div>
            <div class="shrink-0 text-right text-xs text-slate-500">
                <p>{{ $order->created_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="p-5 sm:p-6 space-y-5">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                    <i class="fas fa-user"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $order->buyer_name }}</p>
                    <a href="mailto:{{ $order->buyer_email }}" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $order->buyer_email }}</a>
                    @if($order->buyer_phone)
                        <p class="text-slate-400 text-sm">{{ $order->buyer_phone }}</p>
                    @endif
                </div>
            </div>

            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Part qui vous revient</p>
                <p class="text-white text-lg font-bold">{{ number_format((int) $order->artist_net_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
        </div>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-1">Statut de la commande</h3>
        <p class="text-slate-500 text-xs mb-4">
            Statut actuel : <span class="text-slate-300 font-medium">{{ $order->labelForStatus() }}</span>
        </p>

        @if($order->status === \App\Models\ArtworkOrder::STATUS_PAID)
            <form method="POST" action="{{ route('provider.art-orders.status', $order) }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ \App\Models\ArtworkOrder::STATUS_SHIPPED }}">
                <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                    <i class="fas fa-truck"></i> Marquer comme expédiée
                </button>
            </form>
        @elseif($order->status === \App\Models\ArtworkOrder::STATUS_SHIPPED)
            <form method="POST" action="{{ route('provider.art-orders.status', $order) }}"
                  onsubmit="return confirm('Confirmer la livraison ? Votre solde sera crédité immédiatement.');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ \App\Models\ArtworkOrder::STATUS_DELIVERED }}">
                <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                    <i class="fas fa-box-open"></i> Confirmer la livraison
                </button>
            </form>
            <p class="text-slate-600 text-[11px] mt-2">Expédiée le {{ $order->shipped_at?->format('d/m/Y à H:i') }}. Confirmez la livraison dès que l'acheteur a reçu l'œuvre pour débloquer votre solde.</p>
        @elseif($order->status === \App\Models\ArtworkOrder::STATUS_DELIVERED)
            <p class="text-emerald-400 text-sm"><i class="fas fa-circle-check mr-1"></i> Livrée le {{ $order->delivered_at?->format('d/m/Y à H:i') }} — solde disponible.</p>
        @else
            <p class="text-slate-500 text-sm">Aucune action possible sur cette commande pour l'instant.</p>
        @endif
    </div>
</div>
@endsection
