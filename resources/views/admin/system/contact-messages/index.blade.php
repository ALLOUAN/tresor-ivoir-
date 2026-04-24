@extends('layouts.app')

@section('title', 'Gestion des contacts')
@section('page-title', 'Gestion des contacts')

@section('header-actions')
    <a href="{{ route('admin.administration.contact-messages.export', array_merge(request()->query(), ['format' => 'csv'])) }}"
       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-xs sm:text-sm font-semibold px-3 py-2 rounded-lg shrink-0">
        <i class="fas fa-download"></i>
        Exporter (CSV)
    </a>
@endsection

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'contact-messages'])

@php
    $statusLabels = \App\Models\ContactMessage::statusOptions();
@endphp

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3 sm:gap-4 mb-6">
    <div class="bg-gradient-to-br from-violet-900/40 to-slate-900 border border-violet-500/30 rounded-xl p-4">
        <p class="text-violet-300/80 text-xs font-medium">Total</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($stats['total']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-rose-900/40 to-slate-900 border border-rose-500/30 rounded-xl p-4">
        <p class="text-rose-300/80 text-xs font-medium">Nouveaux</p>
        <p class="text-rose-200 text-2xl font-bold mt-1">{{ number_format($stats['new']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-amber-900/40 to-slate-900 border border-amber-500/30 rounded-xl p-4">
        <p class="text-amber-300/80 text-xs font-medium">En cours</p>
        <p class="text-amber-200 text-2xl font-bold mt-1">{{ number_format($stats['in_progress']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-emerald-900/40 to-slate-900 border border-emerald-500/30 rounded-xl p-4">
        <p class="text-emerald-300/80 text-xs font-medium">Traités</p>
        <p class="text-emerald-200 text-2xl font-bold mt-1">{{ number_format($stats['done']) }}</p>
    </div>
    <div class="bg-gradient-to-br from-sky-900/40 to-slate-900 border border-sky-500/30 rounded-xl p-4 col-span-2 sm:col-span-1 lg:col-span-1">
        <p class="text-sky-300/80 text-xs font-medium">Ce mois</p>
        <p class="text-sky-200 text-2xl font-bold mt-1">{{ number_format($stats['this_month']) }}</p>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20 mb-6">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
        <div class="w-10 h-10 rounded-lg bg-amber-600/20 border border-amber-500/40 flex items-center justify-center shrink-0">
            <i class="fas fa-filter text-amber-300 text-sm"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold">Filtres de recherche</h2>
            <p class="text-slate-500 text-xs mt-0.5">Nom, e-mail, sujet ou contenu du message.</p>
        </div>
    </div>
    <form method="GET" action="{{ route('admin.administration.contact-messages.index') }}" class="p-5 sm:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-2">
                <label for="q" class="block text-xs text-slate-500 mb-1">Recherche</label>
                <div class="relative">
                    <i class="fas fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-500 text-xs"></i>
                    <input type="text" name="q" id="q" value="{{ $q }}"
                           placeholder="Nom, email, sujet..."
                           class="w-full pl-9 pr-3 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-sm text-slate-100">
                </div>
            </div>
            <div>
                <label for="status" class="block text-xs text-slate-500 mb-1">Statut</label>
                <select name="status" id="status" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                    <option value="">Tous</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label for="date_from" class="block text-xs text-slate-500 mb-1">Date début</label>
                    <input type="date" name="date_from" id="date_from" value="{{ $dateFrom ?? '' }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label for="date_to" class="block text-xs text-slate-500 mb-1">Date fin</label>
                    <input type="date" name="date_to" id="date_to" value="{{ $dateTo ?? '' }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-2 py-2 text-sm text-slate-100">
                </div>
            </div>
        </div>
        <div class="mt-4 flex flex-wrap items-center gap-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-violet-600 hover:from-amber-400 hover:to-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-filter"></i>
                Filtrer
            </button>
            <a href="{{ route('admin.administration.contact-messages.index') }}"
               class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-600 text-slate-400 hover:text-white hover:bg-slate-700 transition shrink-0"
               title="Effacer les filtres" aria-label="Effacer les filtres">
                <i class="fas fa-xmark"></i>
            </a>
        </div>
    </form>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-white font-semibold">Liste des contacts</h2>
        <span class="text-xs font-semibold text-sky-300 bg-sky-500/15 border border-sky-500/30 px-3 py-1 rounded-full">
            {{ $messages->total() }} résultat(s)
        </span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Expéditeur</th>
                    <th class="text-left px-5 py-3">Objet</th>
                    <th class="text-left px-5 py-3 min-w-[200px]">Message</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($messages as $message)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3 align-top">
                            <div class="flex gap-2">
                                <span class="w-8 h-8 rounded-full bg-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                                    <i class="fas fa-user text-xs"></i>
                                </span>
                                <div class="min-w-0">
                                    <p class="text-white font-medium truncate">{{ $message->name }}</p>
                                    <p class="text-slate-500 text-xs truncate">{{ $message->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-200 font-medium max-w-[200px]">
                            <span class="line-clamp-2">{{ $message->subject }}</span>
                        </td>
                        <td class="px-5 py-3 align-top text-slate-400">
                            <span class="line-clamp-2">{{ Str::limit($message->message, 120) }}</span>
                        </td>
                        <td class="px-5 py-3 align-top whitespace-nowrap">
                            @if($message->status === \App\Models\ContactMessage::STATUS_NEW)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-rose-500/20 text-rose-300 border border-rose-500/35">Nouveau</span>
                            @elseif($message->status === \App\Models\ContactMessage::STATUS_IN_PROGRESS)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-500/20 text-amber-200 border border-amber-500/35">En cours</span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-500/20 text-emerald-200 border border-emerald-500/35">Traité</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 align-top text-slate-400 whitespace-nowrap text-xs">
                            {{ $message->created_at?->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-5 py-3 align-top text-right whitespace-nowrap">
                            <a href="{{ route('admin.administration.contact-messages.show', $message) }}"
                               class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-sky-500/15 text-sky-400 hover:bg-sky-500/25 border border-sky-500/30 transition mr-1"
                               title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.administration.contact-messages.destroy', $message) }}" class="inline"
                                  onsubmit="return confirm('Supprimer ce message ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="inline-flex items-center justify-center w-9 h-9 rounded-lg bg-rose-500/15 text-rose-400 hover:bg-rose-500/25 border border-rose-500/30 transition"
                                        title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-slate-500">
                            <i class="fas fa-inbox text-3xl mb-3 opacity-40 block"></i>
                            Aucun message pour ces critères.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($messages->hasPages())
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $messages->links() }}
        </div>
    @endif
</div>

@endsection
