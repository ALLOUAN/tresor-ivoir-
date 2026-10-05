@extends('layouts.app')

@section('title', 'Commandes Art & Créations')
@section('page-title', 'Commandes — Art & Créations')

@section('content')

<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Commandes totales</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total_orders']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Payées</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($stats['paid_orders']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Ventes totales</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total_sales_xof'], 0, ',', ' ') }} XOF</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Commissions perçues</p>
        <p class="text-orange-400 text-2xl font-bold mt-1">{{ number_format($stats['total_commission_xof'], 0, ',', ' ') }} XOF</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center gap-2">
        <a href="{{ route('admin.art-orders.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === null ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
            Toutes
        </a>
        @foreach(\App\Models\ArtworkOrder::STATUS_LABELS as $value => $label)
            <a href="{{ route('admin.art-orders.index', ['status' => $value]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === $value ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Référence</th>
                    <th class="text-left px-5 py-3">Œuvre</th>
                    <th class="text-left px-5 py-3">Artiste</th>
                    <th class="text-left px-5 py-3">Acheteur</th>
                    <th class="text-left px-5 py-3">Montant</th>
                    <th class="text-left px-5 py-3">Commission</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Détail</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $order->reference }}</td>
                        <td class="px-5 py-3 text-white">{{ $order->artwork?->title }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ $order->provider?->name }}</td>
                        <td class="px-5 py-3 text-slate-300">{{ $order->buyer_name }}</td>
                        <td class="px-5 py-3 text-slate-200 whitespace-nowrap">{{ number_format((int) $order->amount_total_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3 text-orange-300 whitespace-nowrap">{{ number_format((int) $order->commission_amount_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3">
                            @php
                                $statusClass = match($order->status) {
                                    \App\Models\ArtworkOrder::STATUS_PAID, \App\Models\ArtworkOrder::STATUS_DELIVERED => 'bg-emerald-500/20 text-emerald-300',
                                    \App\Models\ArtworkOrder::STATUS_PENDING_PAYMENT, \App\Models\ArtworkOrder::STATUS_SHIPPED => 'bg-orange-500/20 text-orange-300',
                                    \App\Models\ArtworkOrder::STATUS_CANCELLED, \App\Models\ArtworkOrder::STATUS_REFUNDED, \App\Models\ArtworkOrder::STATUS_OVERSOLD => 'bg-red-500/20 text-red-300',
                                    default => 'bg-slate-500/20 text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $statusClass }}">{{ $order->labelForStatus() }}</span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            <a href="{{ route('admin.art-orders.show', $order) }}" class="text-slate-400 hover:text-white text-xs">
                                Voir <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-10 text-center text-slate-500">Aucune commande.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $orders->links() }}
</div>

@endsection
