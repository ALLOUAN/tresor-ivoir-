@extends('layouts.app')

@section('title', 'Rubriques articles')
@section('page-title', 'Rubriques articles')

@section('header-actions')
    <a href="{{ route('admin.articles.index') }}"
       class="inline-flex items-center gap-1.5 px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-arrow-left"></i> Retour aux articles
    </a>
@endsection

@section('content')

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Nouvelle rubrique --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-5 mb-8">
        <h2 class="text-white font-semibold text-sm mb-4 flex items-center gap-2">
            <i class="fas fa-circle-plus text-amber-400"></i> Nouvelle rubrique
        </h2>
        <form method="POST" action="{{ route('admin.categories.articles.store') }}" class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 items-end">
            @csrf
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-500 mb-1.5">Nom (FR) <span class="text-red-400">*</span></label>
                <input type="text" name="name_fr" value="{{ old('name_fr') }}" required maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none">
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs text-slate-500 mb-1.5">Nom (EN)</label>
                <input type="text" name="name_en" value="{{ old('name_en') }}" maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none"
                    placeholder="Optionnel">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1.5">Slug <span class="text-red-400">*</span></label>
                <input type="text" name="slug" value="{{ old('slug') }}" required maxlength="100"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none font-mono text-xs"
                    placeholder="ex: culture">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1.5">Couleur</label>
                <input type="text" name="color_hex" value="{{ old('color_hex', '#f59e0b') }}" maxlength="7"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none font-mono text-xs"
                    placeholder="#f59e0b">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1.5">Icône (classe FA)</label>
                <input type="text" name="icon" value="{{ old('icon') }}" maxlength="50"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none font-mono text-xs"
                    placeholder="fa-folder">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1.5">Ordre</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}"
                    class="w-full bg-slate-800 border border-slate-700 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none">
            </div>
            <div class="flex items-center gap-2 pb-2">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" id="create_active" class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500/30" @checked(old('is_active', '1') === '1')>
                <label for="create_active" class="text-xs text-slate-400">Active</label>
            </div>
            <div class="xl:col-span-6 flex justify-end">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 hover:bg-amber-600 text-black text-sm font-semibold rounded-lg transition">
                    <i class="fas fa-check"></i> Créer la rubrique
                </button>
            </div>
        </form>
    </div>

    {{-- Liste --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-white font-semibold text-sm">Rubriques existantes</h2>
            <span class="text-slate-500 text-xs">{{ $categories->count() }} rubrique(s)</span>
        </div>

        <div class="divide-y divide-slate-800">
            @forelse($categories as $cat)
                <div class="p-5 hover:bg-slate-800/20 transition">
                    <form method="POST" action="{{ route('admin.categories.articles.update', $cat) }}" class="space-y-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex flex-wrap items-start gap-4 justify-between">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($cat->color_hex)
                                    <span class="w-10 h-10 rounded-xl shrink-0 border border-slate-700 shadow-inner"
                                        style="background-color: {{ $cat->color_hex }}"></span>
                                @else
                                    <span class="w-10 h-10 rounded-xl shrink-0 bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-500">
                                        <i class="fas fa-palette text-sm"></i>
                                    </span>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-white font-medium text-sm truncate">{{ $cat->name_fr }}</p>
                                    <p class="text-slate-500 text-xs font-mono truncate">{{ $cat->slug }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="text-slate-500 text-xs">
                                    <i class="fas fa-newspaper mr-1"></i>{{ $cat->articles_count }} article(s)
                                </span>
                                <button type="submit"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-800 hover:bg-amber-500/20 hover:text-amber-300 text-slate-300 text-xs rounded-lg border border-slate-700 transition">
                                    <i class="fas fa-floppy-disk"></i> Enregistrer
                                </button>
                            </div>
                        </div>
                        <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-slate-500 mb-1">Nom (FR)</label>
                                <input type="text" name="name_fr" value="{{ $cat->name_fr }}" required maxlength="100"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none focus:border-amber-500/40">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-slate-500 mb-1">Nom (EN)</label>
                                <input type="text" name="name_en" value="{{ $cat->name_en }}" maxlength="100"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none focus:border-amber-500/40">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-slate-500 mb-1">Couleur (#hex)</label>
                                <input type="text" name="color_hex" value="{{ $cat->color_hex }}" maxlength="7"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none font-mono text-xs focus:border-amber-500/40">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-slate-500 mb-1">Icône</label>
                                <input type="text" name="icon" value="{{ $cat->icon }}" maxlength="50"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none font-mono text-xs focus:border-amber-500/40">
                            </div>
                            <div>
                                <label class="block text-[10px] uppercase tracking-wide text-slate-500 mb-1">Ordre d’affichage</label>
                                <input type="number" name="sort_order" value="{{ $cat->sort_order }}"
                                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none focus:border-amber-500/40">
                            </div>
                            <div class="flex items-center gap-2 pt-6">
                                <input type="hidden" name="is_active" value="0">
                                <input type="checkbox" name="is_active" value="1" id="active_{{ $cat->id }}"
                                    class="rounded border-slate-600 bg-slate-800 text-amber-500 focus:ring-amber-500/30"
                                    @checked($cat->is_active)>
                                <label for="active_{{ $cat->id }}" class="text-xs text-slate-400">Rubrique active</label>
                            </div>
                        </div>
                    </form>
                </div>
            @empty
                <div class="px-5 py-16 text-center text-slate-500">
                    <i class="fas fa-folder-open text-3xl mb-3 block text-slate-700"></i>
                    Aucune rubrique pour le moment. Créez-en une ci-dessus.
                </div>
            @endforelse
        </div>
    </div>

@endsection
