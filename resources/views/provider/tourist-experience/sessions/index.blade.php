@extends('layouts.app')

@section('title', 'Sessions de visite groupée')
@section('page-title', 'Sessions de visite groupée')

@section('header-actions')
    <a href="{{ route('provider.tourist-experience.sessions.create') }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Créer une session
    </a>
@endsection

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

@if(!$experience)
<div class="px-4 py-3 bg-amber-900/30 border border-amber-800 text-amber-200 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-info shrink-0"></i>
    Complétez d'abord la <a href="{{ route('provider.tourist-experience.profile.edit') }}" class="underline font-semibold">fiche établissement</a> avant de créer des sessions.
</div>
@else
<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3">Période</th>
                    <th class="text-left px-5 py-3">Capacité</th>
                    <th class="text-left px-5 py-3">Places restantes</th>
                    <th class="text-left px-5 py-3">Prix / pers.</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($sessions as $session)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top text-white font-medium whitespace-nowrap">{{ $session->session_date->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $session->period_label ?: '—' }}</td>
                        <td class="px-5 py-3 align-top text-slate-300">{{ $session->capacity }}</td>
                        <td class="px-5 py-3 align-top">
                            @if($session->isFull())
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/35">Complet</span>
                            @else
                                <span class="text-emerald-300 font-medium">{{ $session->remainingSeats() }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 align-top text-slate-200 whitespace-nowrap">
                            {{ $session->price_per_person_xof ? number_format((int) $session->price_per_person_xof, 0, ',', ' ').' XOF' : 'Gratuit' }}
                        </td>
                        <td class="px-5 py-3 align-top">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold
                                @if($session->is_active) bg-emerald-500/20 text-emerald-300 border border-emerald-500/35
                                @else bg-slate-700/40 text-slate-300 border border-slate-600/40 @endif">
                                {{ $session->is_active ? 'Active' : 'Fermée' }}
                            </span>
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('provider.tourist-experience.sessions.edit', $session) }}" class="text-slate-400 hover:text-white text-xs mr-3">
                                <i class="fas fa-pen"></i> Modifier
                            </a>
                            <form method="POST" action="{{ route('provider.tourist-experience.sessions.destroy', $session) }}" class="inline"
                                  onsubmit="return confirm('Supprimer cette session ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-400 hover:text-rose-300 text-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-10 text-center text-slate-500">
                            Aucune session de visite groupée pour l'instant.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">
    {{ $sessions->links() }}
</div>
@endif

@endsection
