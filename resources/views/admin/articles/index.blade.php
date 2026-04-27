@extends('layouts.app')

@section('title', 'Modération des articles')
@section('page-title', 'Articles')

@section('header-actions')
<div class="flex items-center gap-2">
    <a href="{{ route('admin.categories.articles') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-folder"></i> Rubriques
    </a>
    <a href="{{ route('editor.articles.create') }}"
       class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
        <i class="fas fa-circle-plus"></i> Nouvel article
    </a>
</div>
@endsection

@section('content')

{{-- Stats bar --}}
<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['all',       $counts['all'],       'fas fa-newspaper',    'text-slate-300', 'Tous'],
        ['review',    $counts['review'],     'fas fa-clock',        'text-amber-400', 'En révision'],
        ['published', $counts['published'],  'fas fa-circle-check', 'text-emerald-400', 'Publiés'],
        ['draft',     $counts['draft'],      'fas fa-pen',          'text-slate-500', 'Brouillons'],
    ] as [$key, $count, $icon, $color, $label])
    <a href="{{ route('admin.articles.index', $key !== 'all' ? ['status' => $key] : []) }}"
       class="bg-slate-900 border {{ ($status ?? '') === ($key === 'all' ? '' : $key) ? 'border-amber-500/40' : 'border-slate-800' }} rounded-xl p-4 hover:border-slate-700 transition group">
        <div class="flex items-center justify-between mb-2">
            <i class="{{ $icon }} {{ $color }} text-sm"></i>
            @if($key === 'review' && $count > 0)
            <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
            @endif
        </div>
        <p class="text-2xl font-bold text-white">{{ $count }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $label }}</p>
    </a>
    @endforeach
</div>

{{-- Filters --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-5">
    <form method="GET" action="{{ route('admin.articles.index') }}" class="flex flex-wrap gap-3 items-end">
        {{-- Status tabs --}}
        <div class="flex items-center gap-1 flex-1 min-w-0 bg-slate-800 border border-slate-700 rounded-lg p-1">
            @foreach([''=>'Tous', 'draft'=>'Brouillons', 'review'=>'Révision', 'published'=>'Publiés', 'archived'=>'Archivés'] as $val => $lbl)
            <a href="{{ route('admin.articles.index', array_filter(['status' => $val ?: null, 'q' => $search, 'category' => $category])) }}"
               class="shrink-0 px-3 py-1.5 rounded-md text-xs font-medium transition whitespace-nowrap
                      {{ ($status ?? '') === $val ? 'bg-slate-700 text-white' : 'text-slate-500 hover:text-white' }}">
                {{ $lbl }}
                @if($val !== '')
                <span class="ml-1 text-[10px] opacity-60">{{ $counts[$val] ?? 0 }}</span>
                @endif
            </a>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="flex gap-2">
            <input type="text" name="q" value="{{ $search }}" placeholder="Chercher un article…"
                class="w-52 bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
            <select name="category"
                class="bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition">
                <option value="">Toutes rubriques</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ $category == $cat->id ? 'selected':'' }}>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 bg-slate-700 hover:bg-slate-600 text-slate-300 text-xs rounded-lg transition">
                <i class="fas fa-search"></i>
            </button>
        </div>
    </form>
</div>

{{-- Flash --}}
@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check"></i> {{ session('success') }}
</div>
@endif

{{-- Table --}}
<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                    <th class="text-left px-5 py-3">Article</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Rubrique</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Auteur</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3 hidden sm:table-cell">Date</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($articles as $article)
                <tr class="hover:bg-slate-800/30 transition group {{ $article->status === 'review' ? 'bg-amber-900/5' : '' }}">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($article->cover_url)
                            <img src="{{ $article->cover_url }}" class="w-12 h-10 rounded-lg object-cover shrink-0 hidden sm:block">
                            @else
                            <div class="w-12 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 hidden sm:block">
                                <i class="fas fa-image text-slate-600 text-sm"></i>
                            </div>
                            @endif
                            <div class="min-w-0">
                                <p class="text-white font-medium text-sm line-clamp-1">{{ $article->title_fr }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    @if($article->is_featured)
                                    <span class="text-amber-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>À la une</span>
                                    @endif
                                    @if($article->is_sponsored)
                                    <span class="text-purple-400 text-[10px]"><i class="fas fa-handshake mr-0.5"></i>Sponsorisé</span>
                                    @endif
                                    <span class="text-slate-600 text-[10px]">
                                        <i class="fas fa-eye mr-0.5"></i>{{ number_format($article->views_count ?? 0) }}
                                    </span>
                                    @if($article->reading_time)
                                    <span class="text-slate-600 text-[10px]">{{ $article->reading_time }} min</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        <span class="text-amber-400/80 text-xs">{{ $article->category->name_fr ?? '—' }}</span>
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <div class="flex items-center gap-2">
                            <div class="w-6 h-6 rounded-full bg-slate-700 flex items-center justify-center text-[10px] font-bold text-amber-400 shrink-0">
                                {{ strtoupper(substr($article->author->first_name ?? '?', 0, 1)) }}
                            </div>
                            <span class="text-slate-400 text-xs">{{ $article->author->full_name ?? 'N/A' }}</span>
                        </div>
                    </td>
                    <td class="px-5 py-4">
                        @php $statusMap = [
                            'published' => ['bg-emerald-900/40 text-emerald-300 border-emerald-800', 'Publié'],
                            'draft'     => ['bg-slate-800 text-slate-400 border-slate-700', 'Brouillon'],
                            'review'    => ['bg-amber-900/40 text-amber-300 border-amber-800', 'En révision'],
                            'archived'  => ['bg-slate-800 text-slate-500 border-slate-700', 'Archivé'],
                        ]; [$cls, $lbl] = $statusMap[$article->status] ?? ['bg-slate-800 text-slate-400 border-slate-700', $article->status]; @endphp
                        <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium border {{ $cls }}">{{ $lbl }}</span>
                    </td>
                    <td class="px-5 py-4 hidden sm:table-cell text-slate-500 text-xs">
                        {{ $article->updated_at->diffForHumans() }}
                    </td>
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-end gap-1">

                            {{-- View --}}
                            @if($article->status === 'published')
                            <a href="{{ route('articles.show', $article->slug_fr) }}" target="_blank"
                               class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-blue-900/50 flex items-center justify-center text-slate-400 hover:text-blue-300 transition" title="Voir">
                                <i class="fas fa-eye text-xs"></i>
                            </a>
                            @endif

                            {{-- Edit --}}
                            <a href="{{ route('editor.articles.edit', $article) }}"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition"
                                title="Modifier">
                                <i class="fas fa-pen text-xs"></i>
                            </a>

                            {{-- Publish (if review) --}}
                            @if($article->status === 'review')
                            <form method="POST" action="{{ route('admin.articles.publish', $article) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="w-7 h-7 rounded-lg bg-emerald-900/40 hover:bg-emerald-900/70 flex items-center justify-center text-emerald-400 transition" title="Publier">
                                    <i class="fas fa-check text-xs"></i>
                                </button>
                            </form>

                            {{-- Reject modal trigger --}}
                            <button type="button"
                                data-reject-id="{{ $article->id }}"
                                data-reject-title="{{ $article->title_fr }}"
                                onclick="openRejectButton(this)"
                                class="w-7 h-7 rounded-lg bg-red-900/30 hover:bg-red-900/50 flex items-center justify-center text-red-400 transition" title="Rejeter">
                                <i class="fas fa-xmark text-xs"></i>
                            </button>
                            @endif

                            {{-- Archive (if published) --}}
                            @if($article->status === 'published')
                            <form method="POST" action="{{ route('admin.articles.archive', $article) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 flex items-center justify-center text-slate-400 hover:text-slate-200 transition" title="Archiver">
                                    <i class="fas fa-box-archive text-xs"></i>
                                </button>
                            </form>
                            @endif

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.articles.destroy', $article) }}"
                                onsubmit="return confirm('Supprimer définitivement « {{ addslashes($article->title_fr) }} » ?')" class="inline">
                                @csrf @method('DELETE')
                                <button class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-5 py-16 text-center text-slate-500">
                        <i class="fas fa-newspaper text-3xl mb-3 block text-slate-700"></i>
                        Aucun article
                        @if($status) avec le statut "{{ ['draft'=>'Brouillon','review'=>'En révision','published'=>'Publié','archived'=>'Archivé'][$status] ?? $status }}"@endif.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($articles->hasPages())
    <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
        <span>{{ $articles->firstItem() }}–{{ $articles->lastItem() }} sur {{ $articles->total() }}</span>
        <div class="flex gap-1">
            @if(!$articles->onFirstPage())
            <a href="{{ $articles->previousPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
            @endif
            @if($articles->hasMorePages())
            <a href="{{ $articles->nextPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ── Edit article modal ───────────────────────────────────────────────── --}}
<div id="editArticleModal" class="fixed inset-0 z-50 hidden items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeEditArticleModal()"></div>
    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-3xl shadow-2xl max-h-[90vh] overflow-y-auto">
        <h3 class="text-white font-semibold mb-4">Modifier l'article</h3>
        <form method="POST" id="editArticleForm" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            @method('PUT')

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Titre (FR)</label>
                <input type="text" name="title_fr" id="edit_title_fr" required
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Rubrique</label>
                <select name="category_id" id="edit_category_id" required
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name_fr }}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Statut</label>
                <select name="status" id="edit_status" required
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <option value="draft">Brouillon</option>
                    <option value="review">En révision</option>
                    <option value="published">Publié</option>
                    <option value="archived">Archivé</option>
                </select>
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Temps de lecture (min)</label>
                <input type="number" name="reading_time" id="edit_reading_time" min="1" max="240"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>

            <div>
                <label class="block text-xs text-slate-400 mb-1">Date de publication</label>
                <input type="datetime-local" name="published_at" id="edit_published_at"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">URL image de couverture</label>
                <input type="url" name="cover_url" id="edit_cover_url"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Extrait (FR)</label>
                <textarea name="excerpt_fr" id="edit_excerpt_fr" rows="2"
                          class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"></textarea>
            </div>

            <div class="md:col-span-2">
                <label class="block text-xs text-slate-400 mb-1">Contenu (FR)</label>
                <textarea name="content_fr" id="edit_content_fr" rows="6"
                          class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"></textarea>
            </div>

            <div class="md:col-span-2 flex items-center gap-5">
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_featured" id="edit_is_featured" value="1" class="rounded border-slate-600 bg-slate-800 text-amber-500">
                    Mettre à la une
                </label>
                <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                    <input type="checkbox" name="is_sponsored" id="edit_is_sponsored" value="1" class="rounded border-slate-600 bg-slate-800 text-amber-500">
                    Sponsorisé
                </label>
            </div>

            <div class="md:col-span-2 flex justify-end gap-3">
                <button type="button" onclick="closeEditArticleModal()"
                        class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">
                    Annuler
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold rounded-lg transition">
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── Reject modal ──────────────────────────────────────────────────────── --}}
<div id="rejectModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/70 backdrop-blur-sm" onclick="closeReject()"></div>
    <div class="relative bg-slate-900 border border-slate-700 rounded-2xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-white font-semibold mb-1">Rejeter l'article</h3>
        <p id="rejectTitle" class="text-slate-400 text-sm mb-4 line-clamp-1"></p>
        <form method="POST" id="rejectForm">
            @csrf @method('PATCH')
            <div class="mb-4">
                <label class="block text-xs text-slate-400 mb-2">Motif du rejet (optionnel)</label>
                <textarea name="reason" rows="3" placeholder="Expliquez pourquoi l'article est renvoyé en brouillon…"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-red-500/50 rounded-xl px-4 py-3 text-slate-300 text-sm outline-none transition resize-none"></textarea>
            </div>
            <div class="flex gap-3 justify-end">
                <button type="button" onclick="closeReject()"
                    class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-sm rounded-lg transition">
                    Annuler
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition">
                    <i class="fas fa-xmark mr-1"></i> Rejeter
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEditArticleModal(button) {
    const form = document.getElementById('editArticleForm');
    form.action = button.dataset.updateUrl;

    document.getElementById('edit_title_fr').value = button.dataset.titleFr || '';
    document.getElementById('edit_category_id').value = button.dataset.categoryId || '';
    document.getElementById('edit_status').value = button.dataset.status || 'draft';
    document.getElementById('edit_reading_time').value = button.dataset.readingTime || '';
    document.getElementById('edit_cover_url').value = button.dataset.coverUrl || '';
    document.getElementById('edit_excerpt_fr').value = button.dataset.excerptFr || '';
    document.getElementById('edit_content_fr').value = button.dataset.contentFr || '';
    document.getElementById('edit_published_at').value = button.dataset.publishedAt || '';
    document.getElementById('edit_is_featured').checked = button.dataset.isFeatured === '1';
    document.getElementById('edit_is_sponsored').checked = button.dataset.isSponsored === '1';

    const modal = document.getElementById('editArticleModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeEditArticleModal() {
    const modal = document.getElementById('editArticleModal');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

function openRejectButton(button) {
    const id = button.dataset.rejectId;
    const title = button.dataset.rejectTitle || '';
    document.getElementById('rejectTitle').textContent = title;
    document.getElementById('rejectForm').action = '/admin/articles/' + id + '/reject';
    document.getElementById('rejectModal').classList.remove('hidden');
    document.getElementById('rejectModal').classList.add('flex');
}
function closeReject() {
    document.getElementById('rejectModal').classList.add('hidden');
    document.getElementById('rejectModal').classList.remove('flex');
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') closeReject(); });
</script>
@endpush

@endsection
