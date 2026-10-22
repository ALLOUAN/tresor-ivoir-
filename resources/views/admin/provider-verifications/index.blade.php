@extends('layouts.app')

@section('title', 'Vérifications prestataires')
@section('page-title', 'Vérifications prestataires')

@section('content')
@php use App\Models\ProviderDocument; @endphp

@if(session('success'))
    <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">{{ session('success') }}</div>
@endif
@if($errors->any())
    <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-700/40 text-rose-200 text-sm rounded-xl">{{ $errors->first() }}</div>
@endif

<div class="flex flex-wrap gap-2 mb-5">
    @foreach(ProviderDocument::STATUS_LABELS as $key => $label)
        <a href="{{ route('admin.provider-verifications.index', ['statut' => $key]) }}"
           class="px-3 py-1.5 rounded-lg text-sm border transition {{ $status === $key ? 'bg-orange-500 border-orange-500 text-black font-semibold' : 'border-slate-700 text-slate-300 hover:text-white' }}">
            {{ $label }} <span class="opacity-70">({{ $counts[$key] ?? 0 }})</span>
        </a>
    @endforeach
</div>

<div class="space-y-3">
    @forelse($documents as $doc)
        <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $doc->provider?->name ?? '—' }}
                        @if($doc->provider?->is_verified)<i class="fas fa-circle-check text-emerald-400 text-xs ml-1" title="Déjà vérifié"></i>@endif
                    </p>
                    <p class="text-slate-400 text-sm">{{ $doc->typeLabel() }}</p>
                    <p class="text-slate-500 text-xs mt-1">Envoyé le {{ $doc->created_at->format('d/m/Y H:i') }} — {{ $doc->original_name }}</p>
                    @if($doc->reviewed_at)
                        <p class="text-slate-500 text-xs">Examiné le {{ $doc->reviewed_at->format('d/m/Y H:i') }} par {{ $doc->reviewer?->full_name ?? '—' }}</p>
                    @endif
                    @if($doc->rejection_reason)
                        <p class="text-rose-300 text-xs mt-1">Motif : {{ $doc->rejection_reason }}</p>
                    @endif
                </div>
                <a href="{{ route('admin.provider-verifications.download', $doc) }}" target="_blank" rel="noopener"
                   class="inline-flex items-center gap-2 text-sm text-orange-300 hover:text-orange-200 border border-slate-700 rounded-lg px-3 py-2 shrink-0">
                    <i class="fas fa-file-lines text-xs"></i> Ouvrir le document
                </a>
            </div>

            @if($doc->status === ProviderDocument::STATUS_PENDING)
                <div class="mt-4 flex flex-wrap items-start gap-2 border-t border-slate-800 pt-4">
                    <form method="POST" action="{{ route('admin.provider-verifications.approve', $doc) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                            <i class="fas fa-check text-xs"></i> Valider
                        </button>
                    </form>
                    <form method="POST" action="{{ route('admin.provider-verifications.reject', $doc) }}" class="flex flex-1 flex-wrap gap-2 min-w-[240px]">
                        @csrf
                        <input type="text" name="reason" required maxlength="500" placeholder="Motif du refus (illisible, expiré…)"
                               class="flex-1 min-w-0 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <button type="submit" class="inline-flex items-center gap-2 bg-rose-600/80 hover:bg-rose-500 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                            <i class="fas fa-xmark text-xs"></i> Refuser
                        </button>
                    </form>
                </div>
            @endif
        </div>
    @empty
        <div class="bg-green-900 border border-slate-800 rounded-xl p-8 text-center text-slate-400 text-sm">Aucun document dans cette liste.</div>
    @endforelse
</div>

<div class="mt-5">{{ $documents->links() }}</div>
@endsection
