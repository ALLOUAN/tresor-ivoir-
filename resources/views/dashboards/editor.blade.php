@extends('layouts.app')

@section('title', 'Espace Éditeur')
@section('page-title', 'Tableau de bord — Éditeur')

@section('header-actions')
<a href="#" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white text-xs font-medium rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvel article
</a>
@endsection

@section('content')

{{-- ── Stats ───────────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
    @php
    $stats_cards = [
        ['label' => 'Mes articles',   'value' => $stats['my_total'],       'icon' => 'fa-newspaper',      'color' => 'text-blue-400',   'bg' => 'bg-blue-900/20'],
        ['label' => 'Publiés',        'value' => $stats['my_published'],    'icon' => 'fa-circle-check',   'color' => 'text-emerald-400', 'bg' => 'bg-emerald-900/20'],
        ['label' => 'Brouillons',     'value' => $stats['my_drafts'],      'icon' => 'fa-file-pen',       'color' => 'text-slate-400',  'bg' => 'bg-slate-700/30'],
        ['label' => 'En révision',    'value' => $stats['global_review'],  'icon' => 'fa-clock',          'color' => 'text-amber-400',  'bg' => 'bg-amber-900/20'],
        ['label' => 'Événements',     'value' => $stats['upcoming_events'],'icon' => 'fa-calendar-days',  'color' => 'text-violet-400', 'bg' => 'bg-violet-900/20'],
        ['label' => 'Vues totales',   'value' => number_format($stats['total_views']), 'icon' => 'fa-eye','color' => 'text-rose-400',   'bg' => 'bg-rose-900/20'],
    ];
    @endphp
    @foreach($stats_cards as $card)
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="w-8 h-8 rounded-lg {{ $card['bg'] }} flex items-center justify-center">
                <i class="fas {{ $card['icon'] }} {{ $card['color'] }} text-sm"></i>
            </div>
        </div>
        <p class="text-white text-xl font-bold">{{ $card['value'] }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $card['label'] }}</p>
    </div>
    @endforeach
</div>

{{-- ── Two columns ─────────────────────────────────────────────────────── --}}
<div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-6">

    {{-- My articles --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-pen-nib text-blue-400"></i> Mes derniers articles
            </h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir tout →</a>
        </div>
        <div class="divide-y divide-slate-800">
            @forelse($my_articles as $article)
            <div class="px-5 py-3 flex items-center justify-between gap-3 hover:bg-slate-800/40 transition">
                <div class="flex-1 min-w-0">
                    <p class="text-white text-sm font-medium truncate">{{ $article->title }}</p>
                    <p class="text-slate-500 text-xs mt-0.5">
                        {{ $article->category->name ?? '—' }} ·
                        {{ $article->created_at->diffForHumans() }}
                    </p>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    @php
                    $statusMap = ['published'=>['text-emerald-400','bg-emerald-900/30','Publié'],'draft'=>['text-slate-400','bg-slate-800','Brouillon'],'review'=>['text-amber-400','bg-amber-900/30','Révision'],'archived'=>['text-slate-500','bg-slate-800','Archivé']];
                    [$stc, $stbg, $stlabel] = $statusMap[$article->status] ?? ['text-slate-400','bg-slate-800',$article->status];
                    @endphp
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs {{ $stc }} {{ $stbg }} border border-transparent">
                        {{ $stlabel }}
                    </span>
                    <span class="text-slate-500 text-xs"><i class="fas fa-eye mr-1"></i>{{ number_format($article->views_count) }}</span>
                    <a href="#" class="text-slate-600 hover:text-amber-400 text-xs transition"><i class="fas fa-pen"></i></a>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-500 text-sm">
                <i class="fas fa-file-pen text-slate-600 text-2xl mb-2 block"></i>
                Vous n'avez pas encore d'articles. <a href="#" class="text-amber-400">Créer le premier</a>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Pending articles (for review) --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
            <h2 class="text-white font-semibold text-sm flex items-center gap-2">
                <i class="fas fa-clock text-amber-400"></i> Articles en révision
                @if($stats['global_review'] > 0)
                <span class="bg-amber-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $stats['global_review'] }}</span>
                @endif
            </h2>
            <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Réviser →</a>
        </div>
        <div class="divide-y divide-slate-800">
            @forelse($pending_articles as $article)
            <div class="px-5 py-3 hover:bg-slate-800/40 transition">
                <div class="flex items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $article->title }}</p>
                        <div class="flex items-center gap-2 mt-1">
                            <div class="flex items-center gap-1.5">
                                <div class="w-5 h-5 rounded-full bg-slate-700 flex items-center justify-center text-xs">
                                    {{ strtoupper(substr($article->author->first_name ?? '?', 0, 1)) }}
                                </div>
                                <span class="text-slate-400 text-xs">{{ $article->author->first_name ?? 'Inconnu' }}</span>
                            </div>
                            <span class="text-slate-600 text-xs">·</span>
                            <span class="text-slate-500 text-xs">{{ $article->category->name ?? '—' }}</span>
                            <span class="text-slate-600 text-xs">·</span>
                            <span class="text-slate-500 text-xs">{{ $article->created_at->diffForHumans() }}</span>
                        </div>
                    </div>
                    <a href="#" class="shrink-0 px-3 py-1 bg-blue-900/40 hover:bg-blue-800/40 text-blue-300 text-xs rounded-lg transition border border-blue-800">
                        <i class="fas fa-eye mr-1"></i>Relire
                    </a>
                </div>
            </div>
            @empty
            <div class="px-5 py-8 text-center text-slate-500 text-sm">
                <i class="fas fa-check-circle text-emerald-400 text-2xl mb-2 block"></i>
                Aucun article en attente de révision.
            </div>
            @endforelse
        </div>
    </div>
</div>

{{-- ── Upcoming events ─────────────────────────────────────────────────── --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold text-sm flex items-center gap-2">
            <i class="fas fa-calendar-days text-violet-400"></i> Événements à venir
        </h2>
        <a href="#" class="text-amber-400 hover:text-amber-300 text-xs transition">Voir tout →</a>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="text-slate-500 text-xs uppercase tracking-wide border-b border-slate-800">
                    <th class="text-left px-5 py-3">Événement</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Catégorie</th>
                    <th class="text-left px-5 py-3">Date</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Lieu</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($upcoming_events as $event)
                <tr class="hover:bg-slate-800/40 transition">
                    <td class="px-5 py-3">
                        <p class="text-white font-medium">{{ $event->title }}</p>
                    </td>
                    <td class="px-5 py-3 hidden sm:table-cell">
                        <span class="text-violet-300 text-xs bg-violet-900/30 px-2 py-0.5 rounded-full">
                            {{ $event->category->name ?? '—' }}
                        </span>
                    </td>
                    <td class="px-5 py-3">
                        <p class="text-amber-400 text-xs font-medium">{{ $event->starts_at->format('d M Y') }}</p>
                        <p class="text-slate-500 text-xs">{{ $event->starts_at->format('H:i') }}</p>
                    </td>
                    <td class="px-5 py-3 hidden md:table-cell text-slate-400 text-xs">
                        {{ $event->city }}{{ $event->location ? ', ' . $event->location : '' }}
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-8 text-center text-slate-500 text-sm">Aucun événement à venir.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection
