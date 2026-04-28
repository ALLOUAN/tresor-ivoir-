@extends('layouts.app')

@section('title', 'Notifications')
@section('page-title', 'Notifications prestataire')

@section('header-actions')
<form method="POST" action="{{ route('provider.notifications.read-all') }}">
    @csrf
    @method('PATCH')
    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-lg transition">
        <i class="fas fa-check-double"></i> Tout marquer lu
    </button>
</form>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <div class="px-5 py-4 {{ $notification->read_at ? 'opacity-80' : 'bg-amber-900/10' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-white text-sm font-semibold">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="text-slate-300 text-sm mt-1">{{ $data['message'] ?? 'Nouvelle notification.' }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(!empty($data['url']))
                            <form method="POST" action="{{ route('provider.notifications.read', $notification) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="redirect_to" value="{{ $data['url'] }}">
                                <button type="submit" class="text-xs px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200">Voir</button>
                            </form>
                        @endif
                        @if(!$notification->read_at)
                            <form method="POST" action="{{ route('provider.notifications.read', $notification) }}">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs px-3 py-1.5 rounded bg-amber-500/15 hover:bg-amber-500/25 text-amber-300 border border-amber-500/30">Marquer lu</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center text-slate-500 text-sm">Aucune notification pour le moment.</div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection

