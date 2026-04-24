@extends('layouts.app')

@section('title', 'Avis')
@section('page-title', 'Modération des avis')

@section('content')
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Tous</p><p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">En attente</p><p class="text-amber-400 text-2xl font-bold mt-1">{{ number_format($counts['pending']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Approuvés</p><p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['approved']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Rejetés</p><p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($counts['rejected']) }}</p></div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4"><p class="text-slate-500 text-xs">Signalés</p><p class="text-violet-400 text-2xl font-bold mt-1">{{ number_format($counts['flagged']) }}</p></div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Liste des avis</h2>
        <form method="GET" action="{{ route('admin.reviews.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher auteur, commentaire, prestataire..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                @foreach(['all' => 'Tous', 'pending' => 'En attente', 'approved' => 'Approuvés', 'rejected' => 'Rejetés', 'flagged' => 'Signalés'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.reviews.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Reset</a>
            </div>
        </form>
    </div>

    <div class="divide-y divide-slate-800/80">
        @forelse($reviews as $review)
            <div class="px-5 py-4">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <p class="text-white font-medium">{{ $review->author_name ?: ($review->user->full_name ?? 'Anonyme') }} · <span class="text-amber-400">{{ $review->rating }}★</span></p>
                        <p class="text-slate-400 text-xs mt-0.5">Prestataire: {{ $review->provider->name ?? '—' }}</p>
                        @if($review->title)<p class="text-slate-200 text-sm mt-2 font-medium">{{ $review->title }}</p>@endif
                        <p class="text-slate-300 text-sm mt-1">{{ $review->comment }}</p>
                        @if($review->rejection_reason)<p class="text-red-300 text-xs mt-2">Motif rejet: {{ $review->rejection_reason }}</p>@endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if($review->status !== 'approved')
                            <form method="POST" action="{{ route('admin.reviews.approve', $review) }}">@csrf @method('PATCH')
                                <button class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-3 py-1.5 rounded">Approuver</button>
                            </form>
                        @endif
                        @if($review->status !== 'rejected')
                            <form method="POST" action="{{ route('admin.reviews.reject', $review) }}" class="flex items-center gap-2">@csrf @method('PATCH')
                                <input type="text" name="reason" placeholder="Motif (optionnel)" class="bg-slate-800 border border-slate-700 rounded px-2 py-1 text-xs text-slate-100">
                                <button class="bg-orange-600 hover:bg-orange-500 text-white text-xs px-3 py-1.5 rounded">Rejeter</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.reviews.flag', $review) }}">@csrf @method('PATCH')
                            <button class="bg-violet-600 hover:bg-violet-500 text-white text-xs px-3 py-1.5 rounded">Signaler</button>
                        </form>
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" onsubmit="return confirm('Supprimer cet avis ?');">@csrf @method('DELETE')
                            <button class="bg-red-700 hover:bg-red-600 text-white text-xs px-3 py-1.5 rounded">Supprimer</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="px-5 py-8 text-center text-slate-500">Aucun avis trouvé.</div>
        @endforelse
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $reviews->links() }}
    </div>
</div>
@endsection
