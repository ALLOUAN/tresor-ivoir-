@extends('layouts.app')

@section('title', 'Commandes Art & Créations')
@section('page-title', 'Commandes reçues')

@section('content')

@if(session('success'))
    <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check"></i> {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation"></i> {{ session('error') }}
    </div>
@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Total</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-900/40 to-green-900 border border-orange-500/30 rounded-xl p-4">
        <p class="text-orange-300/80 text-xs font-medium">Payées, à expédier</p>
        <p class="text-orange-200 text-2xl font-bold mt-1">{{ number_format($stats['paid']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-blue-900/40 to-green-900 border border-blue-500/30 rounded-xl p-4">
        <p class="text-blue-300/80 text-xs font-medium">Expédiées</p>
        <p class="text-blue-200 text-2xl font-bold mt-1">{{ number_format($stats['shipped']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Livrées</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($stats['delivered']) }}</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20 mb-6">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center gap-2">
        <a href="{{ route('provider.art-orders.index') }}"
           class="px-3 py-1.5 rounded-lg text-xs font-semibold transition {{ $status === '' ? 'bg-orange-500 text-black' : 'bg-slate-800 text-slate-300 hover:bg-slate-700' }}">
            Toutes
        </a>
        @foreach(\App\Models\ArtworkOrder::STATUS_LABELS as $value => $label)
            <a href="{{ route('provider.art-orders.index', ['status' => $value]) }}"
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
                    <th class="text-left px-5 py-3">Acheteur</th>
                    <th class="text-left px-5 py-3">Part artiste</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($orders as $order)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $order->reference }}</td>
                        <td class="px-5 py-3 text-white">{{ $order->artwork?->title }}</td>
                        <td class="px-5 py-3 align-top">
                            <p class="text-slate-200">{{ $order->buyer_name }}</p>
                            <p class="text-slate-500 text-xs">{{ $order->buyer_email }}</p>
                        </td>
                        <td class="px-5 py-3 text-emerald-300 whitespace-nowrap">{{ number_format((int) $order->artist_net_amount_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3 whitespace-nowrap">{{ $order->labelForStatus() }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('provider.art-orders.show', $order) }}" class="text-slate-400 hover:text-white text-xs">
                                Détail <i class="fas fa-arrow-right text-[10px] ml-1"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-10 text-center text-slate-500">Aucune commande pour l'instant.</td>
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
