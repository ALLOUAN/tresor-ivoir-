@extends('layouts.visitor-public')

@section('title', __('Messages'))
@section('page-title', __('Mes messages'))

@section('content')
<div class="max-w-3xl mx-auto">
    @include('partials.visitor-account-nav')

    <div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold">{{ __('Conversations avec les prestataires') }}</h2>
            <p class="text-slate-500 text-xs mt-1">{{ __('Pour démarrer une nouvelle conversation, rendez-vous sur la fiche d\'un établissement et cliquez sur « Contacter le prestataire ».') }}</p>
            <form method="GET" action="{{ route('visitor.conversations.index') }}" class="mt-4 flex gap-2">
                <input type="text" name="q" value="{{ $search }}" placeholder="{{ __('Rechercher un prestataire ou un sujet...') }}"
                       class="flex-1 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">{{ __('Rechercher') }}</button>
                @if($search)
                    <a href="{{ route('visitor.conversations.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">{{ __('Reset') }}</a>
                @endif
            </form>
        </div>

        <div class="divide-y divide-slate-800/80">
            @forelse($conversations as $conversation)
                <a href="{{ route('visitor.conversations.show', $conversation) }}" data-conversation-row data-conversation-id="{{ $conversation->id }}" class="block px-5 py-4 hover:bg-[#f3eee4] transition">
                    <div class="flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-white font-medium truncate">{{ $conversation->provider->name }}</p>
                                @if($conversation->isBlocked())
                                    <span class="px-2 py-0.5 rounded-full text-[11px] bg-slate-500/20 text-slate-300"><i class="fas fa-ban mr-1"></i>{{ __('Bloquée') }}</span>
                                @endif
                            </div>
                            @if($conversation->subject)
                                <p class="text-slate-400 text-xs mt-0.5">{{ $conversation->subject }}</p>
                            @endif
                            <p class="text-slate-400 text-sm mt-1 line-clamp-2" data-last-preview>{{ $conversation->last_message_preview ?: __('Aucun message.') }}</p>
                            <p class="text-slate-500 text-xs mt-2">
                                {{ $conversation->last_message_at?->translatedFormat('d M Y H:i') ?? $conversation->created_at?->translatedFormat('d M Y H:i') }}
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2 shrink-0">
                            <span data-status-pill class="px-2 py-0.5 rounded-full text-[11px] {{ $conversation->status === 'closed' ? 'bg-slate-500/20 text-slate-300' : 'bg-emerald-500/20 text-emerald-300' }}">
                                {{ $conversation->status === 'closed' ? __('Fermée') : __('Ouverte') }}
                            </span>
                            @if($conversation->unread_count > 0)
                                <span data-unread-pill class="px-2 py-0.5 rounded-full bg-orange-500 text-white text-[11px] font-bold">
                                    {{ $conversation->unread_count }} {{ __('non lu') }}{{ $conversation->unread_count > 1 ? 's' : '' }}
                                </span>
                            @else
                                <span data-unread-pill class="hidden px-2 py-0.5 rounded-full bg-orange-500 text-white text-[11px] font-bold"></span>
                            @endif
                        </div>
                    </div>
                </a>
            @empty
            @include('partials.visitor-empty', ['icon' => 'chat-round-dots', 'title' => __('Aucune conversation pour le moment.'), 'text' => __('Posez une question à un établissement depuis sa fiche : la discussion apparaîtra ici.'), 'cta' => ['url' => route('providers.index'), 'label' => __('Trouver un établissement'), 'icon' => 'map-point']])
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
        const url = "{{ route('visitor.conversations.poll') }}";
        const rows = () => Array.from(document.querySelectorAll('[data-conversation-row]'));
        if (!rows().length) return;

        async function refreshConversations() {
            // Onglet en arrière-plan : pas de requête (économise les quotas de l'hébergement).
            if (document.hidden) return;
            try {
                const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                if (!res.ok) return;
                const json = await res.json();
                const map = new Map((json.items || []).map(item => [String(item.id), item]));
                rows().forEach((row) => {
                    const item = map.get(String(row.dataset.conversationId));
                    if (!item) return;
                    const preview = row.querySelector('[data-last-preview]');
                    if (preview && item.last_message_preview) preview.textContent = item.last_message_preview;
                    const statusPill = row.querySelector('[data-status-pill]');
                    if (statusPill) {
                        statusPill.textContent = item.status === 'closed' ? {{ \Illuminate\Support\Js::from(__('Fermée')) }} : 'Ouverte';
                        statusPill.className = 'px-2 py-0.5 rounded-full text-[11px] ' + (item.status === 'closed' ? 'bg-slate-500/20 text-slate-300' : 'bg-emerald-500/20 text-emerald-300');
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
        document.addEventListener('visibilitychange', () => { if (!document.hidden) refreshConversations(); });
    })();
</script>
@endpush
