@extends('layouts.app')

@section('title', 'Maintenance')
@section('page-title', 'Mode Maintenance')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'maintenance'])

@php
    $allowedIpsCount = count($settings->allowedIpsList());
@endphp

<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
<div class="xl:col-span-2 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between gap-4 bg-slate-800/40">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-600/20 border border-amber-500/40 flex items-center justify-center shrink-0">
                <i class="fas fa-screwdriver-wrench text-amber-300"></i>
            </div>
            <div>
                <h2 class="text-white font-semibold text-lg">Maintenance du site</h2>
                <p class="text-slate-400 text-xs mt-0.5">Activez/désactivez le mode maintenance du site public.</p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-semibold border {{ $settings->maintenance_mode ? 'bg-emerald-500/15 border-emerald-500/40 text-emerald-200' : 'bg-slate-700/40 border-slate-600 text-slate-300' }}">
                <i class="fas {{ $settings->maintenance_mode ? 'fa-circle-check' : 'fa-circle' }}"></i>
                {{ $settings->maintenance_mode ? 'Activée' : 'Désactivée' }}
            </span>
            <form method="POST" action="{{ route('admin.administration.maintenance.toggle') }}">
                @csrf
                @method('PATCH')
                <button type="submit"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg text-xs font-semibold transition {{ $settings->maintenance_mode ? 'bg-rose-600 hover:bg-rose-500 text-white' : 'bg-emerald-600 hover:bg-emerald-500 text-white' }}">
                    <i class="fas {{ $settings->maintenance_mode ? 'fa-power-off' : 'fa-bolt' }}"></i>
                    {{ $settings->maintenance_mode ? 'Désactiver maintenant' : 'Activer maintenance maintenant' }}
                </button>
            </form>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.administration.maintenance.update') }}" class="p-5 sm:p-6 space-y-6 max-w-4xl">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <p class="font-medium text-rose-100 mb-1">Corrigez les champs suivants :</p>
                <ul class="list-disc list-inside space-y-0.5 text-rose-200/90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-lg border border-slate-700 bg-slate-800/30 px-4 py-3">
            <div>
                <p class="text-sm font-medium text-white">Mode maintenance</p>
                <p class="text-xs text-slate-500 mt-0.5">Le site public affichera la page d'attente. Le back-office admin reste accessible.</p>
            </div>
            <label class="inline-flex items-center gap-3 cursor-pointer shrink-0">
                <input type="checkbox" name="maintenance_mode" value="1"
                       class="h-5 w-5 rounded border-slate-600 bg-slate-800 text-violet-600 focus:ring-violet-500"
                       @checked($errors->any() ? old('maintenance_mode') === '1' : $settings->maintenance_mode)>
                <span class="text-sm text-slate-300">Activer</span>
            </label>
        </div>

        <div>
            <label for="maintenance_message" class="block text-sm text-slate-300 mb-1">Message affiché aux visiteurs</label>
            <textarea name="maintenance_message" id="maintenance_message" rows="4" maxlength="2000"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                      placeholder="Nous effectuons une mise à jour. Merci de revenir un peu plus tard.">{{ old('maintenance_message', $settings->maintenance_message) }}</textarea>
            <p class="text-slate-500 text-xs mt-1">Ce message est affiché sur la page maintenance publique.</p>
        </div>

        <div>
            <label for="maintenance_allowed_ips" class="block text-sm text-slate-300 mb-1">IPs autorisées (bypass maintenance)</label>
            <textarea name="maintenance_allowed_ips" id="maintenance_allowed_ips" rows="3" maxlength="2000"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                      placeholder="127.0.0.1, ::1, 192.168.1.10">{{ old('maintenance_allowed_ips', $settings->maintenance_allowed_ips) }}</textarea>
            <p class="text-slate-500 text-xs mt-1">Séparez les IPs par virgule, espace ou saut de ligne.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="maintenance_progress" class="block text-sm text-slate-300 mb-1">Progression des travaux (%)</label>
                <input type="number" name="maintenance_progress" id="maintenance_progress" min="0" max="100"
                       value="{{ old('maintenance_progress', $settings->maintenance_progress) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <p class="text-slate-500 text-xs mt-1">Affiché dans le résumé (0 à 100).</p>
            </div>
            <div>
                <label for="maintenance_eta" class="block text-sm text-slate-300 mb-1">Durée estimée</label>
                <input type="text" name="maintenance_eta" id="maintenance_eta" maxlength="120"
                       value="{{ old('maintenance_eta', $settings->maintenance_eta) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                       placeholder="ex. 3h, 45 min, demain 18h">
                <p class="text-slate-500 text-xs mt-1">Texte libre (ex. 3h, 2 jours).</p>
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-floppy-disk"></i>
                Enregistrer les paramètres
            </button>
        </div>
    </form>
</div>
    <div class="space-y-6">
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
            <div class="px-5 py-4 border-b border-slate-800 bg-slate-800/40">
                <h3 class="text-white font-semibold text-base">Résumé</h3>
            </div>
            <div class="p-5 space-y-3 text-sm">
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-slate-400">Statut</span>
                    <span class="font-semibold {{ $settings->maintenance_mode ? 'text-amber-300' : 'text-emerald-300' }}">
                        {{ $settings->maintenance_mode ? 'Maintenance' : 'En ligne' }}
                    </span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-slate-400">Progression</span>
                    <span class="font-semibold text-slate-200">{{ $settings->maintenance_progress !== null ? (int) $settings->maintenance_progress.'%' : '-' }}</span>
                </div>
                <div class="flex items-center justify-between border-b border-slate-800 pb-3">
                    <span class="text-slate-400">Durée estimée</span>
                    <span class="font-semibold text-slate-200">{{ $settings->maintenance_eta ? $settings->maintenance_eta : '-' }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-400">IPs autorisées</span>
                    <span class="font-semibold text-slate-200">{{ $allowedIpsCount }}</span>
                </div>
            </div>
        </div>

        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
            <div class="p-6 text-center">
                <div class="w-14 h-14 mx-auto rounded-full bg-blue-600/15 border border-blue-500/30 flex items-center justify-center mb-3">
                    <i class="fas fa-eye text-blue-300 text-xl"></i>
                </div>
                <h3 class="text-white font-semibold text-lg">Prévisualiser</h3>
                <p class="text-slate-400 text-sm mt-2">Voir la page de maintenance telle que les visiteurs la verront.</p>
                <a href="{{ route('admin.administration.maintenance.preview') }}" target="_blank"
                   class="inline-flex items-center gap-2 mt-4 text-sm font-semibold text-blue-300 hover:text-blue-200 transition">
                    <i class="fas fa-up-right-from-square text-xs"></i>
                    Voir la page
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
