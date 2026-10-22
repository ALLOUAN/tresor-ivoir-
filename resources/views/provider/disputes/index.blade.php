@extends('layouts.app')

@section('title', 'Litiges')
@section('page-title', 'Litiges')

@section('content')
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

<p class="text-slate-400 text-sm mb-4">Quand un client signale un problème sur une réservation payée, la somme correspondante est suspendue le temps que notre équipe arbitre. Donnez votre version des faits pour accélérer la décision.</p>

<div class="space-y-3">
    @forelse($disputes as $d)
        <div class="bg-green-900 border {{ $d->isOpen() ? 'border-amber-600/40' : 'border-slate-800' }} rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $d->reasonLabel() }}</p>
                    <p class="text-slate-400 text-xs mt-0.5">
                        Réservation {{ $d->reservation?->reference }} · {{ $d->reservation?->full_name }}
                        · {{ $d->reservation?->check_in?->format('d/m/Y') }} → {{ $d->reservation?->check_out?->format('d/m/Y') }}
                    </p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-semibold shrink-0 {{ $d->isOpen() ? 'text-amber-300 bg-amber-500/10' : 'text-slate-300 bg-slate-800' }}">{{ $d->statusLabel() }}</span>
            </div>
            <p class="text-slate-300 text-sm mt-3 bg-slate-800/60 rounded-lg px-3 py-2">« {{ $d->description }} »</p>

            @if($d->provider_response)
                <p class="text-slate-400 text-xs mt-2">Votre réponse ({{ $d->provider_responded_at?->format('d/m/Y') }}) : {{ $d->provider_response }}</p>
            @endif
            @if($d->resolution)
                <p class="text-emerald-300 text-xs mt-2">Décision : {{ $d->resolution }}</p>
            @endif

            @if($d->isOpen() && ! $d->provider_response)
                <form method="POST" action="{{ route('provider.disputes.respond', $d) }}" class="mt-3 pt-3 border-t border-slate-800 space-y-2">
                    @csrf
                    <textarea name="provider_response" rows="3" required minlength="10" maxlength="3000"
                              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                              placeholder="Votre version des faits…"></textarea>
                    <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
                        <i class="fas fa-reply text-xs"></i> Répondre
                    </button>
                </form>
            @endif
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center text-slate-400 text-sm">Aucun litige. Tant mieux !</div>
    @endforelse
</div>
<div class="mt-5">{{ $disputes->links() }}</div>
@endsection
