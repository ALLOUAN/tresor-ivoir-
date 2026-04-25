@extends('layouts.app')

@section('title', 'Partenaires')
@section('page-title', 'Tableau de bord')

@section('header-actions')
    <a href="{{ route('admin.administration.partners.create') }}"
        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-rose-500 hover:from-violet-500 hover:to-rose-400 shadow-lg shadow-rose-900/20 transition">
        <i class="fas fa-plus"></i> Nouveau partenaire
    </a>
@endsection

@section('content')

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach([
            ['total', $stats['total'], 'fas fa-handshake', 'Total partenaires', 'border-slate-700'],
            ['active', $stats['active'], 'fas fa-circle-check', 'Actifs', 'border-emerald-800/50'],
            ['featured', $stats['featured'], 'fas fa-star', 'En vedette', 'border-amber-800/50'],
            ['types', $stats['types'], 'fas fa-tags', 'Types', 'border-violet-800/50'],
        ] as [$key, $count, $icon, $label, $border])
            <div class="bg-slate-900 border {{ $border }} rounded-xl p-4">
                <div class="flex items-center justify-between mb-2">
                    <i class="{{ $icon }} text-slate-400 text-sm"></i>
                </div>
                <p class="text-2xl font-bold text-white">{{ $count }}</p>
                <p class="text-slate-500 text-xs mt-0.5">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    {{-- Filtres --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 mb-5">
        <form method="GET" action="{{ route('admin.administration.partners') }}" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs text-slate-500 mb-1">Recherche</label>
                <input type="text" name="q" value="{{ $q }}" placeholder="Nom, email, contact, site web…"
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none focus:border-amber-500/40">
            </div>
            <div class="w-40">
                <label class="block text-xs text-slate-500 mb-1">Type</label>
                <select name="type" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none">
                    <option value="">Tout</option>
                    @foreach($typeOptions as $value => $label)
                        <option value="{{ $value }}" @selected($type === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs text-slate-500 mb-1">Statut</label>
                <select name="status" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none">
                    <option value="" @selected($status === '')>Tout</option>
                    <option value="active" @selected($status === 'active')>Actif</option>
                    <option value="inactive" @selected($status === 'inactive')>Inactif</option>
                </select>
            </div>
            <div class="w-36">
                <label class="block text-xs text-slate-500 mb-1">En vedette</label>
                <select name="featured" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-slate-200 text-sm outline-none">
                    <option value="" @selected($featured === '')>Tout</option>
                    <option value="yes" @selected($featured === 'yes')>Oui</option>
                    <option value="no" @selected($featured === 'no')>Non</option>
                </select>
            </div>
            <button type="submit"
                class="px-5 py-2 rounded-lg text-sm font-semibold text-white bg-gradient-to-r from-violet-600 to-amber-500 hover:from-violet-500 hover:to-amber-400 transition">
                Filtrer
            </button>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
            <h2 class="text-white font-semibold text-sm">Liste des partenaires ({{ $partners->total() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase tracking-wide">
                        <th class="text-left px-4 py-3 w-16">Logo</th>
                        <th class="text-left px-4 py-3">Partenaire</th>
                        <th class="text-left px-4 py-3 hidden md:table-cell">Type</th>
                        <th class="text-left px-4 py-3 hidden lg:table-cell">Contact</th>
                        <th class="text-center px-2 py-3">En vedette</th>
                        <th class="text-center px-2 py-3">Statut</th>
                        <th class="text-center px-2 py-3 hidden sm:table-cell">Ordre</th>
                        <th class="text-left px-4 py-3 hidden xl:table-cell">Date début</th>
                        <th class="text-right px-4 py-3">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-800">
                    @forelse($partners as $partner)
                        @php $typeEnum = $partner->typeEnum(); @endphp
                        <tr class="hover:bg-slate-800/30 transition">
                            <td class="px-4 py-3">
                                @if($partner->logo_url)
                                    <img src="{{ $partner->logo_url }}" alt="" class="w-10 h-10 rounded-lg object-contain bg-slate-800 border border-slate-700 p-0.5">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-600">
                                        <i class="fas fa-image text-xs"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3 min-w-[160px]">
                                <p class="text-white font-medium">{{ $partner->name }}</p>
                                @if($partner->website_url)
                                    <a href="{{ $partner->website_url }}" target="_blank" rel="noopener" class="text-blue-400 hover:text-blue-300 text-xs truncate block max-w-[220px]">
                                        {{ $partner->website_url }}
                                    </a>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                @if($typeEnum)
                                    <span class="inline-flex px-2 py-0.5 rounded-full text-[11px] font-medium border {{ $typeEnum->badgeClasses() }}">
                                        {{ $typeEnum->label() }}
                                    </span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell text-slate-400 text-xs">
                                @if($partner->contact_person || $partner->contact_email)
                                    <p class="text-slate-300"><i class="fas fa-user text-slate-500 mr-1"></i>{{ $partner->contact_person ?? '—' }}</p>
                                    <p class="text-slate-500 truncate max-w-[180px]">{{ $partner->contact_email ?? '' }}</p>
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-2 py-3 text-center">
                                <form method="POST" action="{{ route('admin.administration.partners.toggle-featured', $partner) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Basculer"
                                        class="min-w-[3.25rem] px-2 py-1 rounded-full text-[11px] font-semibold transition {{ $partner->is_featured ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-400 hover:bg-slate-600' }}">
                                        {{ $partner->is_featured ? 'Oui' : 'Non' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-2 py-3 text-center">
                                <form method="POST" action="{{ route('admin.administration.partners.toggle-active', $partner) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" title="Basculer"
                                        class="min-w-[3.25rem] px-2 py-1 rounded-full text-[11px] font-semibold transition {{ $partner->is_active ? 'bg-blue-600 text-white' : 'bg-slate-700 text-slate-400 hover:bg-slate-600' }}">
                                        {{ $partner->is_active ? 'Actif' : 'Off' }}
                                    </button>
                                </form>
                            </td>
                            <td class="px-2 py-3 text-center text-slate-400 hidden sm:table-cell">{{ $partner->sort_order }}</td>
                            <td class="px-4 py-3 text-slate-500 text-xs hidden xl:table-cell whitespace-nowrap">
                                {{ $partner->partnership_start_date?->format('d/m/Y') ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-1">
                                    @if($partner->website_url)
                                        <a href="{{ $partner->website_url }}" target="_blank" rel="noopener"
                                            class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-blue-900/40 flex items-center justify-center text-slate-400 hover:text-blue-300 transition" title="Voir le site">
                                            <i class="fas fa-eye text-xs"></i>
                                        </a>
                                    @endif
                                    <a href="{{ route('admin.administration.partners.edit', $partner) }}"
                                        class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-amber-900/40 flex items-center justify-center text-slate-400 hover:text-amber-300 transition" title="Modifier">
                                        <i class="fas fa-pen text-xs"></i>
                                    </a>
                                    <form method="POST" action="{{ route('admin.administration.partners.destroy', $partner) }}"
                                        class="inline" onsubmit="return confirm('Supprimer ce partenaire ?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-slate-800 hover:bg-red-900/40 flex items-center justify-center text-slate-400 hover:text-red-300 transition" title="Supprimer">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-5 py-16 text-center text-slate-500">
                                <i class="fas fa-handshake text-3xl mb-3 block text-slate-700"></i>
                                Aucun partenaire pour le moment.
                                <a href="{{ route('admin.administration.partners.create') }}" class="text-amber-400 hover:underline ml-1">Créer le premier</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($partners->hasPages())
            <div class="px-5 py-4 border-t border-slate-800 flex items-center justify-between text-xs text-slate-500">
                <span>{{ $partners->firstItem() }}–{{ $partners->lastItem() }} sur {{ $partners->total() }}</span>
                <div class="flex gap-1">
                    @if(!$partners->onFirstPage())
                        <a href="{{ $partners->previousPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">← Préc.</a>
                    @endif
                    @if($partners->hasMorePages())
                        <a href="{{ $partners->nextPageUrl() }}" class="px-3 py-1.5 bg-slate-800 hover:bg-slate-700 rounded-lg transition">Suiv. →</a>
                    @endif
                </div>
            </div>
        @endif
    </div>

@endsection
