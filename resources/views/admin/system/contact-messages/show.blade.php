@extends('layouts.app')

@section('title', 'Message de contact')
@section('page-title', 'Détail du message')

@section('header-actions')
    <a href="{{ route('admin.administration.contact-messages.index') }}"
       class="inline-flex items-center gap-2 text-slate-400 hover:text-white text-sm border border-slate-600 rounded-lg px-3 py-2">
        <i class="fas fa-arrow-left text-xs"></i>
        Retour à la liste
    </a>
@endsection

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'contact-messages'])

<div class="max-w-3xl space-y-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
        <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap items-start justify-between gap-4">
            <div class="min-w-0">
                <p class="text-slate-500 text-xs uppercase tracking-wide">Objet</p>
                <h2 class="text-white font-serif text-xl font-semibold mt-1 break-words">{{ $contactMessage->subject }}</h2>
            </div>
            <div class="shrink-0 text-right text-xs text-slate-500">
                <p>{{ $contactMessage->created_at?->format('d/m/Y H:i') }}</p>
                @if($contactMessage->read_at)
                    <p class="mt-1 text-slate-600">Lu le {{ $contactMessage->read_at->format('d/m/Y H:i') }}</p>
                @endif
            </div>
        </div>
        <div class="p-5 sm:p-6 space-y-5">
            <div class="flex items-start gap-3">
                <span class="w-10 h-10 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 shrink-0">
                    <i class="fas fa-user"></i>
                </span>
                <div class="min-w-0">
                    <p class="text-white font-semibold">{{ $contactMessage->name }}</p>
                    <a href="mailto:{{ $contactMessage->email }}" class="text-sky-400 hover:text-sky-300 text-sm break-all">{{ $contactMessage->email }}</a>
                </div>
            </div>
            <div>
                <p class="text-slate-500 text-xs uppercase tracking-wide mb-2">Message</p>
                <div class="rounded-lg border border-slate-800 bg-slate-950/80 px-4 py-3 text-slate-200 text-sm whitespace-pre-wrap break-words">{{ $contactMessage->message }}</div>
            </div>
        </div>
    </div>

    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 sm:p-6">
        <h3 class="text-white font-semibold text-sm mb-4">Statut</h3>
        <form method="POST" action="{{ route('admin.administration.contact-messages.update', $contactMessage) }}" class="flex flex-wrap items-end gap-3">
            @csrf
            @method('PATCH')
            <select name="status" class="flex-1 min-w-[200px] bg-slate-800 border border-slate-700 rounded-lg px-3 py-2.5 text-sm text-slate-100">
                @foreach(\App\Models\ContactMessage::statusOptions() as $value => $label)
                    <option value="{{ $value }}" @selected($contactMessage->status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-violet-600 hover:from-amber-400 hover:to-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-floppy-disk"></i>
                Enregistrer
            </button>
        </form>
    </div>

    <div class="flex justify-end">
        <form method="POST" action="{{ route('admin.administration.contact-messages.destroy', $contactMessage) }}"
              onsubmit="return confirm('Supprimer définitivement ce message ?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="inline-flex items-center gap-2 text-rose-400 hover:text-rose-300 text-sm border border-rose-500/40 rounded-lg px-4 py-2 hover:bg-rose-500/10 transition">
                <i class="fas fa-trash text-xs"></i>
                Supprimer le message
            </button>
        </form>
    </div>
</div>
@endsection
