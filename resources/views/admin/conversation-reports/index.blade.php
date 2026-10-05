@extends('layouts.app')

@section('title', 'Signalements messagerie')
@section('page-title', 'Signalements messagerie client-prestataire')

@section('content')

<div class="grid grid-cols-3 gap-4 mb-6">
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">En attente</p>
        <p class="text-orange-400 text-2xl font-bold mt-1">{{ number_format($counts['pending']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Examinés</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['reviewed']) }}</p>
    </div>
    <div class="bg-green-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Rejetés</p>
        <p class="text-slate-400 text-2xl font-bold mt-1">{{ number_format($counts['dismissed']) }}</p>
    </div>
</div>

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">File de modération</h2>
        <form method="GET" action="{{ route('admin.conversation-reports.index') }}" class="mt-4 flex gap-2">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="pending" @selected($status === 'pending')>En attente</option>
                <option value="reviewed" @selected($status === 'reviewed')>Examinés</option>
                <option value="dismissed" @selected($status === 'dismissed')>Rejetés</option>
                <option value="all" @selected($status === 'all')>Tous</option>
            </select>
            <button type="submit" class="bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
        </form>
    </div>

    <div class="divide-y divide-slate-800/80">
        @forelse($reports as $report)
            <a href="{{ route('admin.conversation-reports.show', $report) }}" class="block px-5 py-4 hover:bg-slate-800/30 transition">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <div class="flex items-center gap-2">
                            <p class="text-white font-medium truncate">{{ \App\Models\ConversationReport::REASON_LABELS[$report->reason] ?? $report->reason }}</p>
                            <span class="px-2 py-0.5 rounded-full text-[11px] {{ $report->status === 'pending' ? 'bg-orange-500/20 text-orange-300' : ($report->status === 'reviewed' ? 'bg-emerald-500/20 text-emerald-300' : 'bg-slate-500/20 text-slate-300') }}">
                                {{ ['pending' => 'En attente', 'reviewed' => 'Examiné', 'dismissed' => 'Rejeté'][$report->status] ?? $report->status }}
                            </span>
                        </div>
                        <p class="text-slate-400 text-sm mt-1">
                            Signalé par {{ $report->reporter?->full_name }} · contre {{ $report->reportedUser?->full_name }}
                        </p>
                        <p class="text-slate-500 text-xs mt-1">
                            Conversation avec {{ $report->conversation->provider->name }} · {{ $report->created_at?->translatedFormat('d M Y H:i') }}
                        </p>
                        @if($report->details)
                            <p class="text-slate-500 text-xs mt-1 line-clamp-2">{{ $report->details }}</p>
                        @endif
                    </div>
                </div>
            </a>
        @empty
            <div class="px-5 py-10 text-center text-slate-500">Aucun signalement.</div>
        @endforelse
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $reports->links() }}
    </div>
</div>
@endsection
