@extends('layouts.app')

@section('title', 'Conversation')
@section('page-title', 'Conversation avec ' . $conversation->client->full_name)

@section('content')
<div class="mb-4 flex items-center justify-between gap-3">
    <a href="{{ route('provider.client-conversations.index') }}" class="text-orange-400 hover:text-orange-300 text-sm transition">
        ← Retour aux messages clients
    </a>
    @unless($conversation->isBlocked())
    <div class="flex items-center gap-2">
        <button type="button" onclick="document.getElementById('report-modal').classList.remove('hidden'); document.getElementById('report-modal').classList.add('flex');"
                class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 transition">
            <i class="fas fa-flag mr-1"></i>Signaler
        </button>
        <form method="POST" action="{{ route('provider.client-conversations.block', $conversation) }}" onsubmit="return confirm('Bloquer ce client ? Vous ne pourrez plus échanger de messages avec lui dans cette conversation.');">
            @csrf
            <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-300 hover:text-red-300 transition">
                <i class="fas fa-ban mr-1"></i>Bloquer
            </button>
        </form>
    </div>
    @endunless
</div>

@include('partials.chat-security-notice')

@if(session('chat_warning'))
    <div class="mb-4 px-4 py-3 rounded-lg border border-rose-500/30 bg-rose-500/10 text-rose-200 text-xs">
        <i class="fas fa-triangle-exclamation mr-1"></i>{{ session('chat_warning') }}
    </div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between gap-3">
        <div>
            <p class="text-white font-semibold">{{ $conversation->client->full_name }}</p>
            @if($conversation->subject)
                <p class="text-slate-500 text-xs mt-0.5">{{ $conversation->subject }}</p>
            @endif
        </div>
        <span class="px-2 py-1 rounded-full text-xs {{ $conversation->status === 'closed' ? 'bg-slate-500/20 text-slate-300' : 'bg-emerald-500/20 text-emerald-300' }}">
            {{ $conversation->status === 'closed' ? 'Fermée' : 'Ouverte' }}
        </span>
    </div>

    <div class="px-5 py-4 space-y-3 max-h-[60vh] overflow-y-auto bg-green-950/30">
        @forelse($messages as $message)
            @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-2xl rounded-xl px-4 py-3 text-sm {{ $mine ? 'bg-orange-500/20 border border-orange-500/35 text-orange-100' : 'bg-slate-800 border border-slate-700 text-slate-200' }}">
                    <p class="text-[11px] {{ $mine ? 'text-orange-300/90' : 'text-slate-400' }} mb-1">
                        {{ $message->sender->full_name }} · {{ $message->created_at?->translatedFormat('d M Y H:i') }}
                    </p>
                    <p class="whitespace-pre-line leading-relaxed">{{ $message->body }}</p>
                    @if($message->is_flagged)
                        <p class="mt-2 text-[11px] text-rose-300"><i class="fas fa-triangle-exclamation mr-1"></i>Ce message a été signalé automatiquement pour vérification.</p>
                    @endif
                    @if(! $mine)
                        <button type="button"
                                class="mt-2 text-[11px] px-2 py-1 rounded bg-white/10 hover:bg-white/20"
                                onclick="document.getElementById('report-message-id').value='{{ $message->id }}'; document.getElementById('report-modal').classList.remove('hidden'); document.getElementById('report-modal').classList.add('flex');">
                            <i class="fas fa-flag mr-1"></i>Signaler ce message
                        </button>
                    @endif
                    @if($message->attachments->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($message->attachments as $attachment)
                                @php $isInline = str_starts_with((string) $attachment->mime_type, 'image/') || $attachment->mime_type === 'application/pdf'; @endphp
                                <a href="{{ route('provider.client-conversations.attachments.download', [$conversation, $attachment]) }}"
                                   class="inline-flex items-center gap-1 rounded-md border border-white/20 px-2 py-1 text-[11px] hover:bg-white/10 transition">
                                    <i class="fas fa-paperclip"></i>{{ $attachment->file_name }}
                                </a>
                                @if($isInline)
                                    @if(str_starts_with((string) $attachment->mime_type, 'image/'))
                                        <img src="{{ route('provider.client-conversations.attachments.preview', [$conversation, $attachment]) }}?thumb=1" alt="{{ $attachment->file_name }}" class="mt-2 max-h-48 rounded-lg border border-white/20">
                                    @elseif($attachment->mime_type === 'application/pdf')
                                        <iframe src="{{ route('provider.client-conversations.attachments.preview', [$conversation, $attachment]) }}" class="mt-2 h-56 w-full rounded-lg border border-white/20"></iframe>
                                    @endif
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <p class="text-slate-500 text-sm text-center py-8">Aucun message.</p>
        @endforelse
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        @if($conversation->isBlocked())
            <p class="text-slate-500 text-sm text-center py-2"><i class="fas fa-ban mr-1"></i>Cette conversation est bloquée. Vous ne pouvez plus échanger de messages.</p>
        @else
        <form method="POST" action="{{ route('provider.client-conversations.reply', $conversation) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <textarea name="message" rows="3" required
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                      placeholder="Écrire un message au client..."></textarea>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Pièces jointes (optionnel)</label>
                <input type="file" name="attachments[]" multiple
                       class="block w-full text-xs text-slate-300 file:mr-3 file:rounded file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-slate-200 hover:file:bg-slate-600">
            </div>
            <div class="flex justify-end">
                <button class="bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-lg px-4 py-2 text-sm">
                    Envoyer
                </button>
            </div>
        </form>
        @endif
    </div>
</div>

<div id="report-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-green-950/70 px-4">
    <div class="w-full max-w-md rounded-xl border border-slate-700 bg-green-900 p-5 shadow-2xl">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h3 class="text-slate-100 font-semibold">Signaler</h3>
            <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden'); document.getElementById('report-modal').classList.remove('flex');" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('provider.client-conversations.report', $conversation) }}" class="space-y-3">
            @csrf
            <input type="hidden" name="message_id" id="report-message-id" value="">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Motif</label>
                <select name="reason" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    @foreach(\App\Models\ConversationReport::REASON_LABELS as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Détails (optionnel)</label>
                <textarea name="details" rows="3" maxlength="2000" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"></textarea>
            </div>
            <div class="flex items-center justify-end gap-2">
                <button type="button" onclick="document.getElementById('report-modal').classList.add('hidden'); document.getElementById('report-modal').classList.remove('flex');" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm">Annuler</button>
                <button type="submit" class="px-3 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold">Envoyer le signalement</button>
            </div>
        </form>
    </div>
</div>
@endsection
