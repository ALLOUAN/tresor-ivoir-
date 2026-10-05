@extends('layouts.app')

@section('title', 'Espace Agence de Voyages')
@section('page-title', 'Espace Agence de Voyages')

@section('header-actions')
    <a href="{{ route('provider.tours.create') }}"
       class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg transition">
        <i class="fas fa-plus text-xs"></i> Ajouter un circuit
    </a>
@endsection

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-2 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-emerald-900/40 to-green-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Circuits disponibles</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($tourCounts['available']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-orange-900/40 to-green-900 border border-orange-500/30 rounded-xl p-4">
        <p class="text-orange-300/80 text-xs font-medium">Circuits indisponibles</p>
        <p class="text-orange-200 text-2xl font-bold mt-1">{{ number_format($tourCounts['unavailable']) }}</p>
    </div>
</div>

<a href="{{ route('provider.tours.index') }}" class="block bg-green-900 border border-slate-800 rounded-xl p-5 hover:border-orange-500/40 transition max-w-sm">
    <i class="fas fa-route text-orange-400 text-lg"></i>
    <p class="text-white font-semibold mt-2">Mes circuits</p>
    <p class="text-slate-500 text-xs mt-1">Ajouter, modifier ou retirer vos circuits.</p>
</a>

@endsection
