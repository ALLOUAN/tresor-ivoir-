@extends('layouts.visitor-public')

@section('title', 'Mes notifications')
@section('page-title', 'Notifications')

@section('header-actions')
<form method="POST" action="{{ route('visitor.notifications.read-all') }}">
    @csrf
    @method('PATCH')
    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs rounded-lg transition">
        <i class="fas fa-check-double"></i> Tout marquer lu
    </button>
</form>
@endsection

@section('content')
<div class="max-w-5xl mx-auto">
    @if(session('success'))
        <div class="mb-4 px-4 py-3 bg-emerald-900/30 border border-emerald-700/40 text-emerald-200 text-sm rounded-xl">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden divide-y divide-slate-800">
        @forelse($notifications as $notification)
            @php $data = $notification->data; @endphp
            <div class="px-5 py-4 {{ $notification->read_at ? 'opacity-80' : 'bg-amber-900/10' }}">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-white text-sm font-semibold">{{ $data['title'] ?? 'Notification' }}</p>
                        <p class="text-slate-300 text-sm mt-1">{{ $data['message'] ?? 'Mise à jour de votre espace visiteur.' }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!empty($data['url']))
                        <a href="{{ $data['url'] }}" class="shrink-0 text-xs px-3 py-1.5 rounded bg-slate-800 hover:bg-slate-700 text-slate-200">Voir</a>
                    @endif
                </div>
            </div>
        @empty
            <div class="px-5 py-10 text-center text-slate-500 text-sm">
                Aucune notification pour le moment.
            </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
</div>
@endsection
