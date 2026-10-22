@extends('layouts.app')

@section('title', 'Mon équipe')
@section('page-title', 'Mon équipe')

@section('content')
@php $field = 'w-full bg-slate-800 border border-slate-700 focus:border-orange-500/40 rounded-lg px-3 py-2 text-sm text-slate-100 outline-none'; @endphp
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if(session('error') || $errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ session('error') ?? $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-5 gap-5">
    <div class="lg:col-span-3 space-y-3">
        <p class="text-slate-400 text-sm">Votre équipe (réception, salle, accueil du site) gère les réservations, les demandes, les billets et les messages clients avec son propre compte. Elle n'a <b>aucun accès</b> à votre portefeuille, vos retraits, votre abonnement ni à votre fiche publique.</p>
        @forelse($members as $m)
            <div class="bg-green-900 border border-slate-800 rounded-xl p-4 flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-white font-semibold">{{ $m->user?->full_name }} @if($m->job_title)<span class="text-slate-400 font-normal text-sm">· {{ $m->job_title }}</span>@endif</p>
                    <p class="text-slate-400 text-xs">{{ $m->user?->email }} · ajouté le {{ $m->created_at->format('d/m/Y') }}
                        @if($m->user?->last_login_at) · dernière connexion {{ $m->user->last_login_at->diffForHumans() }} @else · <span class="text-amber-300">invitation en attente</span>@endif
                    </p>
                </div>
                <form method="POST" action="{{ route('provider.team.destroy', $m) }}" onsubmit="return confirm('Retirer l\'accès de ce membre ?');">
                    @csrf @method('DELETE')
                    <button class="text-xs text-rose-300 hover:text-rose-200 border border-slate-700 rounded-lg px-3 py-1.5">Retirer l'accès</button>
                </form>
            </div>
        @empty
            <div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center text-slate-400 text-sm">Aucun membre pour le moment.</div>
        @endforelse
    </div>

    <form method="POST" action="{{ route('provider.team.store') }}" class="lg:col-span-2 bg-green-900 border border-slate-800 rounded-xl p-5 space-y-3 self-start">
        @csrf
        <h2 class="text-white font-semibold"><i class="fas fa-user-plus text-orange-400 mr-1"></i> Inviter un membre</h2>
        <div class="grid grid-cols-2 gap-2">
            <input name="first_name" required maxlength="80" value="{{ old('first_name') }}" placeholder="Prénom" class="{{ $field }}">
            <input name="last_name" required maxlength="80" value="{{ old('last_name') }}" placeholder="Nom" class="{{ $field }}">
        </div>
        <input type="email" name="email" required maxlength="255" value="{{ old('email') }}" placeholder="E-mail professionnel" class="{{ $field }}">
        <input name="job_title" maxlength="80" value="{{ old('job_title') }}" placeholder="Poste (ex. Réceptionniste)" class="{{ $field }}">
        <button class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2.5 rounded-lg">
            <i class="fas fa-paper-plane"></i> Envoyer l'invitation
        </button>
        <p class="text-slate-500 text-xs">Le membre reçoit un e-mail pour choisir son mot de passe, puis se connecte normalement.</p>
    </form>
</div>
@endsection
