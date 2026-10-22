@extends('layouts.app')

@section('title', 'Vérification du compte')
@section('page-title', 'Vérification du compte')

@section('content')
@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

@php
    use App\Models\ProviderDocument;
    $byType = $documents->groupBy('type');
@endphp

<div class="max-w-3xl space-y-6">
    <div class="bg-green-900 border {{ $provider->is_verified ? 'border-emerald-600/40' : 'border-amber-600/40' }} rounded-xl p-5 flex items-start gap-4">
        <div class="w-11 h-11 rounded-full flex items-center justify-center shrink-0 {{ $provider->is_verified ? 'bg-emerald-500/15 text-emerald-300' : 'bg-amber-500/15 text-amber-300' }}">
            <i class="fas {{ $provider->is_verified ? 'fa-circle-check' : 'fa-hourglass-half' }} text-lg"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold">{{ $provider->is_verified ? 'Votre compte est vérifié' : 'Votre compte n\'est pas encore vérifié' }}</h2>
            <p class="text-slate-400 text-sm mt-1">
                @if($provider->is_verified)
                    Votre établissement affiche le badge « vérifié » et vous pouvez retirer vos gains depuis votre portefeuille.
                @else
                    Pour protéger les voyageurs et vos paiements, nous vérifions chaque établissement avant le premier retrait d'argent.
                    Envoyez les pièces ci-dessous ; elles sont examinées sous 48 h ouvrées et ne sont jamais rendues publiques.
                @endif
            </p>
        </div>
    </div>

    <div class="bg-green-900 border border-slate-800 rounded-xl divide-y divide-slate-800">
        @foreach(ProviderDocument::TYPE_LABELS as $type => $label)
            @php
                $latest = $byType->get($type)?->first();
                $required = in_array($type, ProviderDocument::REQUIRED_TYPES, true);
                $tone = match ($latest?->status) {
                    ProviderDocument::STATUS_APPROVED => 'text-emerald-300 bg-emerald-500/10',
                    ProviderDocument::STATUS_REJECTED => 'text-rose-300 bg-rose-500/10',
                    ProviderDocument::STATUS_PENDING => 'text-amber-300 bg-amber-500/10',
                    default => 'text-slate-400 bg-slate-800',
                };
            @endphp
            <div class="p-5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <p class="text-white text-sm font-medium">{{ $label }} @if($required)<span class="text-orange-400">*</span>@endif</p>
                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $tone }}">{{ $latest ? $latest->statusLabel() : 'Non envoyé' }}</span>
                </div>
                @if($latest?->status === ProviderDocument::STATUS_REJECTED && $latest->rejection_reason)
                    <p class="text-rose-300 text-xs mt-2">Motif du refus : {{ $latest->rejection_reason }}</p>
                @endif
                @if($latest)
                    <p class="text-slate-500 text-xs mt-1">Dernier envoi : {{ $latest->original_name }} — {{ $latest->created_at->format('d/m/Y') }}</p>
                @endif
                @if($latest?->status !== ProviderDocument::STATUS_APPROVED)
                    <form method="POST" action="{{ route('provider.verification.store') }}" enctype="multipart/form-data" class="mt-3 flex flex-wrap items-center gap-2">
                        @csrf
                        <input type="hidden" name="type" value="{{ $type }}">
                        <input type="file" name="document" required accept=".pdf,image/*"
                               class="flex-1 min-w-0 text-xs text-slate-300 file:mr-3 file:px-3 file:py-1.5 file:rounded-lg file:border-0 file:bg-slate-700 file:text-slate-200">
                        <button type="submit" class="inline-flex items-center gap-2 bg-orange-500 hover:bg-orange-600 text-black text-xs font-semibold px-4 py-2 rounded-lg transition">
                            <i class="fas fa-upload"></i> Envoyer
                        </button>
                    </form>
                @endif
            </div>
        @endforeach
    </div>
    <p class="text-slate-500 text-xs">* Pièces obligatoires. Formats acceptés : PDF, JPG, PNG, WEBP — 8 Mo maximum.</p>
</div>
@endsection
