@php
    $accountNavItems = [
        ['label' => 'Tableau de bord', 'icon' => 'fa-gauge-high', 'route' => 'visitor.dashboard', 'active' => request()->routeIs('visitor.dashboard')],
        ['label' => 'Mes réservations', 'icon' => 'fa-bed', 'route' => 'visitor.reservations.index', 'active' => request()->routeIs('visitor.reservations.*')],
        ['label' => 'Mes reçus', 'icon' => 'fa-receipt', 'route' => 'visitor.receipts.index', 'active' => request()->routeIs('visitor.receipts.*')],
        ['label' => 'Mon portefeuille', 'icon' => 'fa-wallet', 'route' => 'visitor.wallet.index', 'active' => request()->routeIs('visitor.wallet.*')],
        ['label' => 'Messages', 'icon' => 'fa-message', 'route' => 'visitor.conversations.index', 'active' => request()->routeIs('visitor.conversations.*')],
        ['label' => 'Mon profil', 'icon' => 'fa-user-pen', 'route' => 'visitor.profile.edit', 'active' => request()->routeIs('visitor.profile.edit') && !request()->query('securite')],
        ['label' => 'Sécurité', 'icon' => 'fa-shield-halved', 'route' => 'visitor.profile.edit', 'params' => ['securite' => 1], 'active' => request()->routeIs('visitor.profile.edit') && request()->query('securite')],
    ];
    $accountNavUnreadMessages = \App\Models\Conversation::where('client_id', auth()->id())
        ->whereHas('messages', fn ($q) => $q->whereNull('read_at')->where('sender_id', '!=', auth()->id()))
        ->count();
@endphp

<div class="flex items-center gap-2 overflow-x-auto pb-1 mb-6 -mx-1 px-1" style="scrollbar-width:none;">
    @foreach($accountNavItems as $item)
        <a href="{{ route($item['route'], $item['params'] ?? []) }}"
           class="shrink-0 inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border transition
                  {{ $item['active']
                        ? 'bg-orange-500 border-orange-500 text-white'
                        : 'bg-green-900 border-slate-800 text-slate-300 hover:border-orange-600/50 hover:text-white' }}">
            <i class="fas {{ $item['icon'] }} text-xs"></i> {{ $item['label'] }}
            @if($item['route'] === 'visitor.conversations.index' && $accountNavUnreadMessages > 0)
                <span class="inline-flex min-w-[1.1rem] h-[1.1rem] px-1 items-center justify-center rounded-full {{ $item['active'] ? 'bg-white/25 text-white' : 'bg-orange-500 text-white' }} text-[10px] font-bold leading-none">
                    {{ $accountNavUnreadMessages > 99 ? '99+' : $accountNavUnreadMessages }}
                </span>
            @endif
        </a>
    @endforeach
    <form method="POST" action="{{ route('logout') }}" class="shrink-0">
        @csrf
        <button type="submit"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium border border-slate-800 bg-green-900 text-slate-400 hover:border-rose-600/50 hover:text-rose-300 transition">
            <i class="fas fa-arrow-right-from-bracket text-xs"></i> Déconnexion
        </button>
    </form>
</div>
