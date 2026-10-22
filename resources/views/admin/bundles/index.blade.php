@extends('layouts.app')

@section('title', 'Offres groupées')
@section('page-title', 'Offres groupées')

@section('header-actions')
    <a href="{{ route('admin.bundles.create') }}" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
        <i class="fas fa-plus text-xs"></i> Nouvelle offre
    </a>
@endsection

@section('content')
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif

<p class="text-slate-400 text-sm mb-4">Composez des séjours tout compris à partir des fiches du site. Le prestataire porteur reçoit les demandes, fixe le montant final et encaisse le paiement en ligne.</p>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-x-auto">
    <table class="w-full text-sm min-w-[720px]">
        <thead>
            <tr class="text-left text-slate-400 border-b border-slate-800">
                <th class="px-4 py-3">Offre</th><th class="px-4 py-3">Porteur</th><th class="px-4 py-3">Durée</th>
                <th class="px-4 py-3">Prix / pers.</th><th class="px-4 py-3">Éléments</th><th class="px-4 py-3">Statut</th><th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-800">
            @forelse($bundles as $b)
                <tr class="text-slate-200">
                    <td class="px-4 py-3">
                        <p class="font-semibold">{{ $b->title }} @if($b->is_featured)<i class="fas fa-star text-amber-400 text-xs ml-1" title="Mise en avant"></i>@endif</p>
                        <p class="text-slate-500 text-xs">{{ $b->city?->name }}</p>
                    </td>
                    <td class="px-4 py-3">{{ $b->provider?->name }}</td>
                    <td class="px-4 py-3">{{ $b->duration_days }} j</td>
                    <td class="px-4 py-3">{{ number_format($b->price_xof, 0, ',', ' ') }} XOF</td>
                    <td class="px-4 py-3">{{ count($b->includes ?? []) }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('admin.bundles.toggle', $b) }}">
                            @csrf @method('PATCH')
                            <button class="text-xs px-2.5 py-1 rounded-full {{ $b->is_active ? 'bg-emerald-500/15 text-emerald-300' : 'bg-slate-800 text-slate-400' }}">{{ $b->is_active ? 'Publiée' : 'Masquée' }}</button>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($b->is_active)<a href="{{ route('bundles.show', $b) }}" target="_blank" class="text-slate-400 hover:text-white mr-3" title="Voir"><i class="fas fa-eye"></i></a>@endif
                        <a href="{{ route('admin.bundles.edit', $b) }}" class="text-orange-300 hover:text-orange-200 mr-3" title="Modifier"><i class="fas fa-pen"></i></a>
                        <form method="POST" action="{{ route('admin.bundles.destroy', $b) }}" class="inline" onsubmit="return confirm('Supprimer cette offre ?');">
                            @csrf @method('DELETE')
                            <button class="text-rose-300 hover:text-rose-200" title="Supprimer"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Aucune offre pour le moment. Créez la première avec « Nouvelle offre ».</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-5">{{ $bundles->links() }}</div>
@endsection
