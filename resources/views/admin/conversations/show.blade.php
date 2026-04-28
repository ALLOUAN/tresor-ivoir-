@extends('layouts.app')

@section('title', 'Conversation prestataire')
@section('page-title', 'Conversation prestataire')

@section('content')
<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <a href="{{ route('admin.conversations.index') }}" class="text-amber-400 hover:text-amber-300 text-sm transition">
        ← Retour à la messagerie
    </a>
    <form method="POST" action="{{ route('admin.conversations.status', $conversation) }}" class="flex items-center gap-2">
        @csrf
        @method('PATCH')
        <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <option value="open" @selected($conversation->status === 'open')>Ouverte</option>
            <option value="closed" @selected($conversation->status === 'closed')>Fermée</option>
        </select>
        <select name="priority" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <option value="normal" @selected($conversation->priority === 'normal')>Normale</option>
            <option value="urgent" @selected($conversation->priority === 'urgent')>Urgente</option>
        </select>
        <select name="assigned_admin_id" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <option value="">Non assignée</option>
            @foreach($admins as $admin)
                <option value="{{ $admin->id }}" @selected((int) $conversation->assigned_admin_id === (int) $admin->id)>
                    {{ $admin->full_name }}
                </option>
            @endforeach
        </select>
        <button class="bg-slate-700 hover:bg-slate-600 text-white text-sm px-3 py-2 rounded-lg">Mettre à jour</button>
    </form>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <p class="text-white font-semibold">{{ $conversation->subject ?: 'Conversation sans sujet' }}</p>
        <p class="text-slate-500 text-xs mt-0.5">
            Prestataire: {{ $conversation->provider->name }}
            @if($conversation->provider->user)
                · {{ $conversation->provider->user->full_name }} ({{ $conversation->provider->user->email }})
            @endif
        </p>
        <p class="text-slate-500 text-xs mt-0.5">
            Priorité: {{ $conversation->priority === 'urgent' ? 'Urgente' : 'Normale' }}
            @if($conversation->assignedAdmin)
                · Assigné à: {{ $conversation->assignedAdmin->full_name }}
            @endif
        </p>
    </div>

    <div class="px-5 py-4 space-y-3 max-h-[60vh] overflow-y-auto bg-slate-950/30">
        @forelse($messages as $message)
            @php $mine = (int) $message->sender_id === (int) auth()->id(); @endphp
            <div class="flex {{ $mine ? 'justify-end' : 'justify-start' }}">
                <div class="max-w-3xl rounded-xl px-4 py-3 text-sm {{ $mine ? 'bg-amber-500/20 border border-amber-500/35 text-amber-100' : 'bg-slate-800 border border-slate-700 text-slate-200' }}">
                    <p class="text-[11px] {{ $mine ? 'text-amber-300/90' : 'text-slate-400' }} mb-1">
                        {{ $message->sender->full_name }} · {{ $message->created_at?->translatedFormat('d M Y H:i') }}
                    </p>
                    <p class="whitespace-pre-line leading-relaxed">{{ $message->body }}</p>
                    @if($mine)
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button"
                                    class="text-[11px] px-2 py-1 rounded bg-white/10 hover:bg-white/20"
                                    data-edit-message-btn
                                    data-edit-action="{{ route('admin.conversations.messages.update', [$conversation, $message]) }}"
                                    data-edit-body="{{ e($message->body) }}">
                                Modifier
                            </button>
                            <form method="POST" action="{{ route('admin.conversations.messages.delete', [$conversation, $message]) }}" onsubmit="return confirm('Supprimer ce message ?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-[11px] px-2 py-1 rounded bg-rose-500/20 hover:bg-rose-500/30 text-rose-200">Supprimer</button>
                            </form>
                        </div>
                    @endif
                    @if($message->attachments->isNotEmpty())
                        <div class="mt-2 flex flex-wrap gap-2">
                            @foreach($message->attachments as $attachment)
                                @php $isInline = str_starts_with((string) $attachment->mime_type, 'image/') || $attachment->mime_type === 'application/pdf'; @endphp
                                <a href="{{ route('admin.conversations.attachments.download', [$conversation, $attachment]) }}"
                                   class="inline-flex items-center gap-1 rounded-md border border-white/20 px-2 py-1 text-[11px] hover:bg-white/10 transition">
                                    <i class="fas fa-paperclip"></i>{{ $attachment->file_name }}
                                </a>
                                @if($isInline)
                                    @if(str_starts_with((string) $attachment->mime_type, 'image/'))
                                        <img src="{{ route('admin.conversations.attachments.preview', [$conversation, $attachment]) }}?thumb=1" alt="{{ $attachment->file_name }}" class="mt-2 max-h-48 rounded-lg border border-white/20">
                                    @elseif($attachment->mime_type === 'application/pdf')
                                        <iframe src="{{ route('admin.conversations.attachments.preview', [$conversation, $attachment]) }}" class="mt-2 h-56 w-full rounded-lg border border-white/20"></iframe>
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
        <form method="POST" action="{{ route('admin.conversations.reply', $conversation) }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <textarea name="message" rows="3" required
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                      placeholder="Écrire une réponse au prestataire..."></textarea>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Pièces jointes (optionnel)</label>
                <input type="file" name="attachments[]" multiple
                       class="block w-full text-xs text-slate-300 file:mr-3 file:rounded file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-slate-200 hover:file:bg-slate-600">
            </div>
            <div class="flex justify-end">
                <button class="bg-amber-500 hover:bg-amber-600 text-black font-semibold rounded-lg px-4 py-2 text-sm">
                    Envoyer
                </button>
            </div>
        </form>
    </div>
</div>

<div id="edit-message-modal" class="fixed inset-0 z-[70] hidden items-center justify-center bg-slate-950/70 px-4">
    <div class="w-full max-w-xl rounded-xl border border-slate-700 bg-slate-900 p-4 shadow-2xl">
        <div class="flex items-center justify-between gap-3 mb-3">
            <h3 class="text-slate-100 font-semibold">Modifier le message</h3>
            <button type="button" id="edit-message-close" class="h-8 w-8 inline-flex items-center justify-center rounded-lg text-slate-400 hover:text-white hover:bg-slate-800">
                <i class="fas fa-xmark"></i>
            </button>
        </div>
        <form method="POST" id="edit-message-form" class="space-y-3">
            @csrf
            @method('PATCH')
            <textarea name="body" id="edit-message-body" rows="6" maxlength="4000" required
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                      placeholder="Modifier votre message..."></textarea>
            <div class="flex items-center justify-end gap-2">
                <button type="button" id="edit-message-cancel" class="px-3 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm">Annuler</button>
                <button type="submit" class="px-3 py-2 rounded-lg bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold">Enregistrer</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const modal = document.getElementById('edit-message-modal');
        const form = document.getElementById('edit-message-form');
        const bodyInput = document.getElementById('edit-message-body');
        const closeBtn = document.getElementById('edit-message-close');
        const cancelBtn = document.getElementById('edit-message-cancel');
        if (!modal || !form || !bodyInput) return;

        const openModal = (action, body) => {
            form.setAttribute('action', action);
            bodyInput.value = body || '';
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            setTimeout(() => bodyInput.focus(), 0);
        };
        const closeModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        document.querySelectorAll('[data-edit-message-btn]').forEach((btn) => {
            btn.addEventListener('click', () => {
                openModal(btn.dataset.editAction || '', btn.dataset.editBody || '');
            });
        });
        [closeBtn, cancelBtn].forEach((el) => el && el.addEventListener('click', closeModal));
        modal.addEventListener('click', (e) => {
            if (e.target === modal) closeModal();
        });
    })();
</script>
@endpush

