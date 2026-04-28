@extends('layouts.app')

@section('title', 'Messagerie')
@section('page-title', 'Messagerie avec l’administrateur')

@section('content')
<style>
    .conversation-row {
        border-left: 4px solid rgba(100, 116, 139, 0.6);
        transition: all .25s ease;
    }
    .conversation-row:hover {
        transform: translateY(-1px);
        box-shadow: 0 10px 30px rgba(2, 6, 23, 0.35);
    }
    .conversation-type-pill {
        border-radius: 9999px;
        padding: 0.25rem 0.65rem;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.02em;
    }
    .conversation-type-urgent {
        border-left-color: rgba(244, 63, 94, 0.9);
        background: linear-gradient(90deg, rgba(190, 18, 60, 0.12), transparent 35%);
    }
    .conversation-type-urgent .conversation-type-pill {
        background: rgba(244, 63, 94, 0.2);
        color: rgb(251 113 133);
        border: 1px solid rgba(244, 63, 94, 0.35);
    }
    .conversation-type-unread {
        border-left-color: rgba(245, 158, 11, 0.95);
        background: linear-gradient(90deg, rgba(245, 158, 11, 0.13), transparent 35%);
    }
    .conversation-type-unread .conversation-type-pill {
        background: rgba(245, 158, 11, 0.2);
        color: rgb(251 191 36);
        border: 1px solid rgba(245, 158, 11, 0.4);
    }
    .conversation-type-closed {
        border-left-color: rgba(100, 116, 139, 0.9);
        background: linear-gradient(90deg, rgba(100, 116, 139, 0.12), transparent 35%);
    }
    .conversation-type-closed .conversation-type-pill {
        background: rgba(100, 116, 139, 0.22);
        color: rgb(203 213 225);
        border: 1px solid rgba(148, 163, 184, 0.32);
    }
    .conversation-type-standard {
        border-left-color: rgba(16, 185, 129, 0.9);
        background: linear-gradient(90deg, rgba(16, 185, 129, 0.11), transparent 35%);
    }
    .conversation-type-standard .conversation-type-pill {
        background: rgba(16, 185, 129, 0.2);
        color: rgb(110 231 183);
        border: 1px solid rgba(16, 185, 129, 0.35);
    }
</style>
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-xl p-5">
        <h2 class="text-white font-semibold mb-4">Nouvelle conversation</h2>
        <form method="POST" action="{{ route('provider.conversations.store') }}" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs text-slate-400 mb-1">Sujet</label>
                <input type="text" name="subject" value="{{ old('subject') }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                       placeholder="Ex: Validation de mon profil">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Message *</label>
                <textarea name="message" rows="4" required
                          class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                          placeholder="Expliquez votre demande...">{{ old('message') }}</textarea>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Pièces jointes (optionnel)</label>
                <input type="file" name="attachments[]" multiple
                       class="block w-full text-xs text-slate-300 file:mr-3 file:rounded file:border-0 file:bg-slate-700 file:px-3 file:py-1.5 file:text-slate-200 hover:file:bg-slate-600">
            </div>
            <button class="w-full bg-amber-500 hover:bg-amber-600 text-black font-semibold rounded-lg px-4 py-2.5 text-sm">
                Envoyer
            </button>
        </form>
    </div>

    <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold">Mes conversations</h2>
        </div>
        <div class="divide-y divide-slate-800/80">
            @forelse($conversations as $conversation)
                @php
                    $conversationType = 'standard';
                    $conversationTypeLabel = 'Standard';
                    if ($conversation->priority === 'urgent') {
                        $conversationType = 'urgent';
                        $conversationTypeLabel = 'Urgent';
                    } elseif (($conversation->unread_count ?? 0) > 0) {
                        $conversationType = 'unread';
                        $conversationTypeLabel = 'Non lu';
                    } elseif ($conversation->status === 'closed') {
                        $conversationType = 'closed';
                        $conversationTypeLabel = 'Fermée';
                    }
                @endphp
                @php
                    $arrivalNumber = (int) (($conversations->firstItem() ?? 1) + $loop->index);
                @endphp
                <a href="{{ route('provider.conversations.show', $conversation) }}" data-conversation-row data-conversation-id="{{ $conversation->id }}" class="conversation-row conversation-type-{{ $conversationType }} block px-5 py-4 hover:bg-slate-800/30 transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="inline-flex items-center justify-center min-w-[1.9rem] h-[1.9rem] rounded-full bg-slate-800 border border-slate-700 text-slate-300 text-[11px] font-bold">
                                    #{{ $arrivalNumber }}
                                </span>
                                <p class="text-white font-medium truncate">{{ $conversation->subject ?: 'Conversation sans sujet' }}</p>
                                <span class="conversation-type-pill">{{ $conversationTypeLabel }}</span>
                            </div>
                            <p class="text-slate-400 text-sm mt-1 line-clamp-2" data-last-preview>{{ $conversation->last_message_preview ?: 'Aucun message.' }}</p>
                        @if($conversation->assignedAdmin)
                            <p class="text-slate-500 text-xs mt-1">Assigné à: {{ $conversation->assignedAdmin->full_name }}</p>
                        @endif
                            <p class="text-slate-500 text-xs mt-2">
                                {{ $conversation->last_message_at?->translatedFormat('d M Y H:i') ?? $conversation->created_at?->translatedFormat('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                        <span data-priority-pill class="px-2 py-0.5 rounded-full text-[11px] {{ $conversation->priority === 'urgent' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-500/20 text-slate-300' }}">
                            {{ $conversation->priority === 'urgent' ? 'Urgent' : 'Normal' }}
                        </span>
                            <span data-status-pill class="px-2 py-0.5 rounded-full text-[11px] {{ $conversation->status === 'closed' ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                                {{ $conversation->status === 'closed' ? 'Fermée' : 'Ouverte' }}
                            </span>
                            @if($conversation->unread_count > 0)
                                <span data-unread-pill class="px-2 py-0.5 rounded-full bg-amber-500 text-black text-[11px] font-bold">
                                    {{ $conversation->unread_count }} non lu{{ $conversation->unread_count > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span data-unread-pill class="hidden px-2 py-0.5 rounded-full bg-amber-500 text-black text-[11px] font-bold"></span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
                <div class="px-5 py-10 text-center text-slate-500">Aucune conversation pour le moment.</div>
            @endforelse
        </div>
        <div class="px-5 py-4 border-t border-slate-800">
            {{ $conversations->links() }}
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const url = "{{ route('provider.conversations.poll') }}";
        const rows = () => Array.from(document.querySelectorAll('[data-conversation-row]'));
        if (!rows().length) return;

        async function refreshConversations() {
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const json = await res.json();
                const map = new Map((json.items || []).map(item => [String(item.id), item]));
                rows().forEach((row) => {
                    const item = map.get(String(row.dataset.conversationId));
                    if (!item) return;
                    row.classList.remove('conversation-type-urgent', 'conversation-type-unread', 'conversation-type-closed', 'conversation-type-standard');
                    let typeClass = 'conversation-type-standard';
                    let typeLabel = 'Standard';
                    const unread = Number(item.unread_count || 0);
                    if (item.priority === 'urgent') {
                        typeClass = 'conversation-type-urgent';
                        typeLabel = 'Urgent';
                    } else if (unread > 0) {
                        typeClass = 'conversation-type-unread';
                        typeLabel = 'Non lu';
                    } else if (item.status === 'closed') {
                        typeClass = 'conversation-type-closed';
                        typeLabel = 'Fermée';
                    }
                    row.classList.add(typeClass);
                    const preview = row.querySelector('[data-last-preview]');
                    if (preview && item.last_message_preview) preview.textContent = item.last_message_preview;
                    const typePill = row.querySelector('.conversation-type-pill');
                    if (typePill) typePill.textContent = typeLabel;

                    const statusPill = row.querySelector('[data-status-pill]');
                    if (statusPill) {
                        statusPill.textContent = item.status === 'closed' ? 'Fermée' : 'Ouverte';
                        statusPill.className = 'px-2 py-0.5 rounded-full text-[11px] ' + (item.status === 'closed' ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300');
                    }
                    const priorityPill = row.querySelector('[data-priority-pill]');
                    if (priorityPill) {
                        priorityPill.textContent = item.priority === 'urgent' ? 'Urgent' : 'Normal';
                        priorityPill.className = 'px-2 py-0.5 rounded-full text-[11px] ' + (item.priority === 'urgent' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-500/20 text-slate-300');
                    }
                    const unreadPill = row.querySelector('[data-unread-pill]');
                    if (unreadPill) {
                        const unread = Number(item.unread_count || 0);
                        if (unread > 0) {
                            unreadPill.classList.remove('hidden');
                            unreadPill.textContent = unread + ' non lu' + (unread > 1 ? 's' : '');
                        } else {
                            unreadPill.classList.add('hidden');
                            unreadPill.textContent = '';
                        }
                    }
                });
            } catch (_) {}
        }

        setInterval(refreshConversations, 15000);
    })();
</script>
@endpush

