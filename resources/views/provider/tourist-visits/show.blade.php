@extends('layouts.app')

@section('title', 'Visite ' . $visit->reference)
@section('page-title', 'Visite ' . $visit->reference)

@section('header-actions')
    <a href="{{ route('provider.tourist-visits.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i> Retour à la liste
    </a>
@endsection

@section('content')

<div class="max-w-2xl space-y-6">

    @if(session('success'))
    <div class="px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="px-4 py-3 bg-rose-900/30 border border-rose-800 text-rose-300 text-sm rounded-xl flex items-center gap-2">
        <i class="fas fa-circle-exclamation shrink-0"></i> {{ session('error') }}
    </div>
    @endif

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
        <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-slate-500 text-xs uppercase tracking-wide">Site touristique</p>
                <h2 class="text-white font-serif text-xl font-semibold mt-1 break-words">{{ $visit->experience_name }}</h2>
            </div>
            <div class="shrink-0 text-right text-xs text-slate-500">
                <p>{{ $visit->created_at?->format('d/m/Y H:i') }}</p>
            </div>
        </div>
        <div class="p-5 sm:p-6 space-y-5">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                    <i class="fas fa-user"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $visit->full_name }}</p>
                    <a href="mailto:{{ $visit->email }}" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $visit->email }}</a>
                    @if($visit->phone)
                        <p class="text-slate-400 text-sm">{{ $visit->phone }}</p>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Type de visite</p>
                    <p class="text-white text-sm font-medium">{{ $visit->labelForType() }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Participants</p>
                    <p class="text-white text-sm font-medium">{{ $visit->participants_count }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Avec guide</p>
                    <p class="text-white text-sm font-medium">{{ $visit->with_guide ? 'Oui' : 'Non' }}</p>
                </div>
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Date</p>
                    <p class="text-white text-sm font-medium">{{ optional($visit->session_date ?? $visit->desired_date)->format('d/m/Y') ?? '—' }}</p>
                </div>
                @if($visit->session_time_label)
                <div>
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Période</p>
                    <p class="text-white text-sm font-medium">{{ $visit->session_time_label }}</p>
                </div>
                @endif
                @if($visit->message)
                <div class="col-span-2 sm:col-span-3">
                    <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Message</p>
                    <p class="text-slate-300 text-sm">{{ $visit->message }}</p>
                </div>
                @endif
            </div>

            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-1">Votre part nette</p>
                <p class="text-white text-lg font-bold">{{ number_format((int) $visit->provider_net_amount_xof, 0, ',', ' ') }} XOF</p>
            </div>
        </div>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-1">Statut de la visite</h3>
        <p class="text-slate-500 text-xs mb-4">
            Statut actuel : <span class="text-slate-300 font-medium">{{ $visit->labelForStatus() }}</span>
        </p>

        @if(in_array($visit->status, [\App\Models\TouristVisit::STATUS_PENDING_PAYMENT, \App\Models\TouristVisit::STATUS_PAID], true))
            <form method="POST" action="{{ route('provider.tourist-visits.status', $visit) }}"
                  onsubmit="return confirm('Annuler cette visite ?');">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ \App\Models\TouristVisit::STATUS_CANCELLED }}">
                <button type="submit" class="inline-flex items-center gap-2 bg-rose-600/80 hover:bg-rose-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                    <i class="fas fa-ban"></i> Annuler cette visite
                </button>
            </form>
            <p class="text-slate-600 text-[11px] mt-2">Un remboursement éventuel (si déjà payée) est traité par l'administration.</p>
        @else
            <p class="text-slate-500 text-sm">Aucune action possible sur cette visite.</p>
        @endif
    </div>
</div>
@endsection
