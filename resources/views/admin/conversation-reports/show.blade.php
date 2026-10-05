@extends('layouts.app')

@section('title', 'Signalement')
@section('page-title', 'Détail du signalement')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.conversation-reports.index') }}" class="text-orange-400 hover:text-orange-300 text-sm transition">
        ← Retour aux signalements
    </a>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-green-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold mb-3">Signalement</h2>
            <dl class="space-y-2 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs">Motif</dt>
                    <dd class="text-slate-200">{{ \App\Models\ConversationReport::REASON_LABELS[$report->reason] ?? $report->reason }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Statut</dt>
                    <dd>
                        <span class="px-2 py-0.5 rounded-full text-[11px] {{ $report->status === 'pending' ? 'bg-orange-500/20 text-orange-300' : ($report->status === 'reviewed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300') }}">
                            {{ ['pending' => 'En attente', 'reviewed' => 'Examiné', 'dismissed' => 'Rejeté'][$report->status] ?? $report->status }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Signalé par</dt>
                    <dd class="text-slate-200">{{ $report->reporter?->full_name }} ({{ $report->reporter?->email }})</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Utilisateur signalé</dt>
                    <dd class="text-slate-200">{{ $report->reportedUser?->full_name }} ({{ $report->reportedUser?->email }})</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs">Prestataire concerné</dt>
                    <dd class="text-slate-200">{{ $report->conversation->provider->name }}</dd>
                </div>
                @if($report->details)
                    <div>
                        <dt class="text-slate-500 text-xs">Détails</dt>
                        <dd class="text-slate-200 whitespace-pre-line">{{ $report->details }}</dd>
                    </div>
                @endif
                @if($report->message)
                    <div>
                        <dt class="text-slate-500 text-xs">Message signalé</dt>
                        <dd class="text-slate-200 whitespace-pre-line bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 mt-1">{{ $report->message->body }}</dd>
                    </div>
                @endif
                @if($report->reviewer)
                    <div>
                        <dt class="text-slate-500 text-xs">Examiné par</dt>
                        <dd class="text-slate-200">{{ $report->reviewer->full_name }} · {{ $report->reviewed_at?->translatedFormat('d M Y H:i') }}</dd>
                    </div>
                @endif
            </dl>

            @if($report->status === 'pending')
            <div class="mt-5 pt-4 border-t border-slate-800 space-y-3">
                <form method="POST" action="{{ route('admin.conversation-reports.update-status', $report) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="reviewed">
                    <label class="flex items-center gap-2 text-xs text-slate-400 mb-3">
                        <input type="checkbox" name="suspend_user" value="1" class="rounded border-slate-600 bg-slate-800 text-orange-500">
                        Suspendre le compte de l'utilisateur signalé
                    </label>
                    <button type="submit" class="w-full bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Marquer comme examiné
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.conversation-reports.update-status', $report) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="dismissed">
                    <button type="submit" class="w-full bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Rejeter le signalement
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>

    <div class="lg:col-span-2 bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <p class="text-white font-semibold">Contexte de la conversation</p>
            <p class="text-slate-500 text-xs mt-0.5">{{ $report->conversation->client?->full_name }} ↔ {{ $report->conversation->provider->name }}</p>
        </div>
        <div class="px-5 py-4 space-y-3 max-h-[70vh] overflow-y-auto bg-green-950/30">
            @forelse($messages as $message)
                @php $isClient = (int) $message->sender_id === (int) $report->conversation->client_id; @endphp
                <div class="flex {{ $isClient ? 'justify-start' : 'justify-end' }}">
                    <div class="max-w-2xl rounded-xl px-4 py-3 text-sm {{ $message->id === $report->message_id ? 'bg-rose-500/15 border border-rose-500/40 text-rose-100' : ($isClient ? 'bg-slate-800 border border-slate-700 text-slate-200' : 'bg-orange-500/20 border border-orange-500/35 text-orange-100') }}">
                        <p class="text-[11px] text-slate-400 mb-1">
                            {{ $message->sender->full_name }} · {{ $message->created_at?->translatedFormat('d M Y H:i') }}
                            @if($message->id === $report->message_id)
                                <span class="text-rose-300 font-semibold">· message signalé</span>
                            @endif
                        </p>
                        <p class="whitespace-pre-line leading-relaxed">{{ $message->body }}</p>
                        @if($message->is_flagged)
                            <p class="mt-2 text-[11px] text-rose-300"><i class="fas fa-triangle-exclamation mr-1"></i>Signalé automatiquement ({{ $message->flagged_reason }})</p>
                        @endif
                    </div>
                </div>
            @empty
                <p class="text-slate-500 text-sm text-center py-8">Aucun message.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
