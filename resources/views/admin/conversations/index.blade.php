@extends('layouts.app')

@section('title', 'Messagerie prestataires')
@section('page-title', 'Messagerie prestataires')

@section('content')
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
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
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Conversations prestataires</h2>
        <form method="POST" action="{{ route('admin.conversations.start') }}" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3 bg-slate-800/40 border border-slate-700/60 rounded-lg p-3">
            @csrf
            <select name="provider_id" required class="md:col-span-2 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Choisir un prestataire</option>
                @foreach($providers as $provider)
                    <option value="{{ $provider->id }}" @selected((string) old('provider_id') === (string) $provider->id)>
                        {{ $provider->name }}@if($provider->user) · {{ $provider->user->full_name }}@endif
                    </option>
                @endforeach
            </select>
            <input type="text" name="subject" required maxlength="255" placeholder="Sujet"
                   class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                   value="{{ old('subject') }}">
            <select name="priority" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="normal" @selected(old('priority') === 'normal')>Priorité normale</option>
                <option value="urgent" @selected(old('priority') === 'urgent')>Priorité urgente</option>
            </select>
            <textarea name="message" required rows="2" maxlength="4000" placeholder="Votre message..."
                      class="md:col-span-5 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('message') }}</textarea>
            <div class="md:col-span-5 flex justify-end">
                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
                    Démarrer une conversation ciblée
                </button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.conversations.broadcast') }}" class="mt-4 grid grid-cols-1 md:grid-cols-5 gap-3 bg-slate-800/40 border border-slate-700/60 rounded-lg p-3">
            @csrf
            <input type="text" name="subject" required maxlength="255" placeholder="Sujet du message global"
                   class="md:col-span-2 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                   value="{{ old('subject') }}">
            <select name="priority" class="bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="normal" @selected(old('priority') === 'normal')>Priorité normale</option>
                <option value="urgent" @selected(old('priority') === 'urgent')>Priorité urgente</option>
            </select>
            <textarea name="message" required rows="2" maxlength="4000" placeholder="Message à envoyer à tous les prestataires..."
                      class="md:col-span-2 bg-slate-900 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('message') }}</textarea>
            <label class="md:col-span-5 inline-flex items-center gap-2 text-xs text-slate-300">
                <input type="checkbox"
                       name="only_without_open_conversation"
                       value="1"
                       @checked(old('only_without_open_conversation'))
                       class="rounded border-slate-600 bg-slate-900 text-amber-500 focus:ring-amber-500/50">
                Envoyer uniquement aux prestataires sans conversation ouverte
            </label>
            <div class="md:col-span-5 flex justify-end">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">
                    Envoyer à tous les prestataires
                </button>
            </div>
        </form>
        <form method="GET" action="{{ route('admin.conversations.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher sujet ou prestataire..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                <option value="open" @selected($status === 'open')>Ouverte</option>
                <option value="closed" @selected($status === 'closed')>Fermée</option>
            </select>
            <select name="priority" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Toutes priorités</option>
                <option value="normal" @selected($priority === 'normal')>Normale</option>
                <option value="urgent" @selected($priority === 'urgent')>Urgente</option>
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.conversations.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Reset</a>
            </div>
        </form>
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
            <a href="{{ route('admin.conversations.show', $conversation) }}" data-conversation-row data-conversation-id="{{ $conversation->id }}" class="conversation-row conversation-type-{{ $conversationType }} block px-5 py-4 hover:bg-slate-800/30 transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center justify-center min-w-[1.9rem] h-[1.9rem] rounded-full bg-slate-800 border border-slate-700 text-slate-300 text-[11px] font-bold">
                                #{{ $arrivalNumber }}
                            </span>
                            <p class="text-white font-medium truncate">{{ $conversation->subject ?: 'Conversation sans sujet' }}</p>
                            <span class="conversation-type-pill">{{ $conversationTypeLabel }}</span>
                        </div>
                        <p class="text-slate-400 text-sm mt-1">
                            Prestataire: {{ $conversation->provider->name }}
                            @if($conversation->provider->user)
                                · {{ $conversation->provider->user->full_name }}
                            @endif
                        </p>
                        @if($conversation->assignedAdmin)
                            <p class="text-slate-500 text-xs mt-1">Assigné à: {{ $conversation->assignedAdmin->full_name }}</p>
                        @endif
                        <p class="text-slate-500 text-xs mt-1 line-clamp-2" data-last-preview>{{ $conversation->last_message_preview ?: 'Aucun message.' }}</p>
                    </div>
                    <div class="flex flex-col items-end gap-3 shrink-0">
                        <span data-priority-pill class="px-3.5 py-1.5 rounded-full text-[14px] font-semibold {{ $conversation->priority === 'urgent' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-500/20 text-slate-300' }}">
                            {{ $conversation->priority === 'urgent' ? 'Urgent' : 'Normal' }}
                        </span>
                        <span data-status-pill class="px-3.5 py-1.5 rounded-full text-[14px] font-semibold {{ $conversation->status === 'closed' ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                            {{ $conversation->status === 'closed' ? 'Fermée' : 'Ouverte' }}
                        </span>
                        @if($conversation->unread_count > 0)
                            <span data-unread-pill class="px-3.5 py-1.5 rounded-full bg-amber-500 text-black text-[14px] font-bold">
                                {{ $conversation->unread_count }} non lu{{ $conversation->unread_count > 1 ? 's' : '' }}
                            </span>
                        @else
                            <span data-unread-pill class="hidden px-3.5 py-1.5 rounded-full bg-amber-500 text-black text-[14px] font-bold"></span>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="px-5 py-10 text-center text-slate-500">Aucune conversation trouvée.</div>
        @endforelse
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $conversations->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const url = "{{ route('admin.conversations.poll') }}";
        const params = new URLSearchParams(window.location.search);
        const rows = () => Array.from(document.querySelectorAll('[data-conversation-row]'));
        if (!rows().length) return;

        async function refreshConversations() {
            try {
                const res = await fetch(url + (params.toString() ? '?' + params.toString() : ''), {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
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
                        statusPill.className = 'px-3.5 py-1.5 rounded-full text-[14px] font-semibold ' + (item.status === 'closed' ? 'bg-rose-500/20 text-rose-300' : 'bg-emerald-500/20 text-emerald-300');
                    }
                    const priorityPill = row.querySelector('[data-priority-pill]');
                    if (priorityPill) {
                        priorityPill.textContent = item.priority === 'urgent' ? 'Urgent' : 'Normal';
                        priorityPill.className = 'px-3.5 py-1.5 rounded-full text-[14px] font-semibold ' + (item.priority === 'urgent' ? 'bg-rose-500/20 text-rose-300' : 'bg-slate-500/20 text-slate-300');
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

