@extends('layouts.app')

@section('title', $session->exists ? 'Modifier la session' : 'Créer une session')
@section('page-title', $session->exists ? 'Modifier la session' : 'Créer une session de visite groupée')

@section('header-actions')
    <a href="{{ route('provider.tourist-experience.sessions.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour
    </a>
@endsection

@section('content')

<div class="max-w-2xl">
    @if($errors->any())
        <div class="mb-4 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">
            <ul class="list-disc pl-4 space-y-0.5">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $session->exists ? route('provider.tourist-experience.sessions.update', $session) : route('provider.tourist-experience.sessions.store') }}"
          class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6 space-y-5">
        @csrf
        @if($session->exists) @method('PUT') @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Date de la visite</label>
                <input type="date" name="session_date" required min="{{ now()->toDateString() }}"
                       value="{{ old('session_date', optional($session->session_date)->format('Y-m-d')) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Période / heure</label>
                <input type="text" name="period_label" maxlength="100" placeholder="ex: 10h00 ou Matinée"
                       value="{{ old('period_label', $session->period_label) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Capacité (nombre de places)</label>
                <input type="number" name="capacity" required min="1" max="9999"
                       value="{{ old('capacity', $session->capacity) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                @if($session->exists)
                    <p class="text-slate-600 text-[11px] mt-1">{{ $session->bookedSeats() }} place(s) déjà réservée(s).</p>
                @endif
            </div>
            <div>
                <label class="block text-slate-400 text-xs mb-1.5">Prix par personne (XOF)</label>
                <input type="number" name="price_per_person_xof" min="0" placeholder="Laisser vide = gratuit"
                       value="{{ old('price_per_person_xof', $session->price_per_person_xof) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
            </div>
        </div>

        <div>
            <label class="block text-slate-400 text-xs mb-1.5">Conditions particulières</label>
            <textarea name="conditions" rows="3" maxlength="2000"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">{{ old('conditions', $session->conditions) }}</textarea>
        </div>

        <label class="flex items-center gap-2.5 cursor-pointer group">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1"
                   class="rounded border-slate-600 bg-slate-800 text-orange-500"
                   {{ old('is_active', $session->exists ? $session->is_active : true) ? 'checked' : '' }}>
            <span class="text-sm text-slate-300 group-hover:text-white transition">
                Session active <span class="text-slate-500 text-xs">(ouverte aux réservations)</span>
            </span>
        </label>

        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
            <i class="fas fa-floppy-disk"></i> {{ $session->exists ? 'Enregistrer' : 'Créer la session' }}
        </button>
    </form>
</div>

@endsection
