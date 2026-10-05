@extends('layouts.app')

@section('title', 'Espace Art & Créations')
@section('page-title', 'Espace Art & Créations')

@section('header-actions')
    <a href="{{ route('provider.artworks.create') }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-400 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Publier une œuvre
    </a>
@endsection

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Œuvres publiées</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($artworkCounts['published']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-900/40 to-green-900 border border-orange-500/30 rounded-xl p-4">
        <p class="text-orange-300/80 text-xs font-medium">En attente de validation</p>
        <p class="text-orange-200 text-2xl font-bold mt-1">{{ number_format($artworkCounts['pending_review']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-blue-900/40 to-green-900 border border-blue-500/30 rounded-xl p-4">
        <p class="text-blue-300/80 text-xs font-medium">Commandes à expédier</p>
        <p class="text-blue-200 text-2xl font-bold mt-1">{{ number_format($orderStats['to_ship']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-green-900/40 to-green-900 border border-green-500/30 rounded-xl p-4">
        <p class="text-green-300/80 text-xs font-medium">Solde disponible</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format((int) ($wallet->balance_available_xof ?? 0), 0, ',', ' ') }} XOF</p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <a href="{{ route('provider.artworks.index') }}" class="bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition">
        <i class="fas fa-palette text-orange-400 text-lg"></i>
        <p class="text-white font-semibold mt-2">Mes œuvres</p>
        <p class="text-slate-500 text-xs mt-1">Ajouter, modifier ou retirer vos créations.</p>
    </a>
    <a href="{{ route('provider.art-orders.index') }}" class="bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition">
        <i class="fas fa-box-open text-orange-400 text-lg"></i>
        <p class="text-white font-semibold mt-2">Commandes Art</p>
        <p class="text-slate-500 text-xs mt-1">Suivre les ventes et gérer les livraisons.</p>
    </a>
    <a href="{{ route('provider.wallet.index') }}" class="bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition">
        <i class="fas fa-wallet text-orange-400 text-lg"></i>
        <p class="text-white font-semibold mt-2">Portefeuille</p>
        <p class="text-slate-500 text-xs mt-1">Revenus, commissions et retraits.</p>
    </a>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold text-sm">Commandes récentes</h2>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Référence</th>
                    <th class="text-left px-5 py-3">Œuvre</th>
                    <th class="text-left px-5 py-3">Part artiste</th>
                    <th class="text-left px-5 py-3">Statut</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($recentOrders as $order)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 font-mono text-xs text-slate-300">{{ $order->reference }}</td>
                        <td class="px-5 py-3 text-white">{{ $order->artwork?->title }}</td>
                        <td class="px-5 py-3 text-emerald-300 whitespace-nowrap">{{ number_format((int) $order->artist_net_amount_xof, 0, ',', ' ') }} XOF</td>
                        <td class="px-5 py-3 text-slate-300">{{ $order->labelForStatus() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-10 text-center text-slate-500">Aucune commande pour l'instant.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
