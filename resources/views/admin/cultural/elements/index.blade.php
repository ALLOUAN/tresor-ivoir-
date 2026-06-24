@extends('layouts.app')

@section('title', 'Cultures Ivoiriennes — Éléments')
@section('page-title', 'Éléments Culturels')

@section('header-actions')
<a href="{{ route('admin.cultural.elements.create') }}"
    class="inline-flex items-center gap-1.5 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-black font-semibold text-xs rounded-lg transition">
    <i class="fas fa-circle-plus"></i> Nouvel élément
</a>
@endsection

@section('content')

@include('admin.cultural.partials.subnav')

{{-- Stats --}}
<div class="grid grid-cols-3 gap-4 mb-6">
    @foreach([
        ['fas fa-masks-theater', 'text-slate-300',   $counts['total'],     'Total'],
        ['fas fa-circle-check',  'text-emerald-400', $counts['active'],    'Actifs'],
        ['fas fa-triangle-exclamation', 'text-red-400', $counts['en_danger'], 'En danger / Disparus'],
    ] as [$icon, $color, $val, $lbl])
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <i class="{{ $icon }} {{ $color }} text-sm mb-2 block"></i>
        <p class="text-2xl font-bold text-white">{{ $val }}</p>
        <p class="text-slate-500 text-xs mt-0.5">{{ $lbl }}</p>
    </div>
    @endforeach
</div>

{{-- Filtres --}}
<form method="GET" action="{{ route('admin.cultural.elements.index') }}" class="mb-5 flex gap-2 flex-wrap">
    <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher un élément…"
        class="flex-1 min-w-40 bg-slate-900 border border-slate-800 focus:border-amber-500/40 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none transition placeholder-slate-600">
    <select name="domain"
        class="bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none">
        <option value="">Tous les domaines</option>
        @foreach($domains as $d)
        <option value="{{ $d->id }}" {{ $domainId == $d->id ? 'selected' : '' }}>{{ $d->name }}</option>
        @endforeach
    </select>
    <select name="risk"
        class="bg-slate-900 border border-slate-800 rounded-lg px-3 py-2 text-slate-300 text-xs outline-none">
        <option value="">Tous risques</option>
        @foreach(['stable'=>'Stable','vulnerable'=>'Vulnérable','en_danger'=>'En danger','disparu'=>'Disparu'] as $val => $lbl)
        <option value="{{ $val }}" {{ $risk === $val ? 'selected' : '' }}>{{ $lbl }}</option>
        @endforeach
    </select>
    <button class="px-3 py-2 bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs rounded-lg transition">
        <i class="fas fa-search"></i>
    </button>
</form>

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
                    <th class="text-left px-5 py-3">Élément</th>
                    <th class="text-left px-5 py-3 hidden md:table-cell">Domaine</th>
                    <th class="text-left px-5 py-3 hidden lg:table-cell">Risque</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-right px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800">
                @forelse($elements as $element)
                @php
                    $riskColors = [
                        'stable'     => 'bg-emerald-900/40 text-emerald-400',
                        'vulnerable' => 'bg-yellow-900/40 text-yellow-400',
                        'en_danger'  => 'bg-orange-900/40 text-orange-400',
                        'disparu'    => 'bg-red-900/40 text-red-400',
                    ];
                    $riskLabels = ['stable'=>'Stable','vulnerable'=>'Vulnérable','en_danger'=>'En danger','disparu'=>'Disparu'];
                @endphp
                <tr class="hover:bg-slate-800/30 transition">
                    <td class="px-5 py-4">
                        <div class="flex items-center gap-3">
                            @if($element->thumbnail)
                            <img src="{{ $element->thumbnail }}" class="w-10 h-10 rounded-lg object-cover shrink-0 hidden sm:block">
                            @else
                            <div class="w-10 h-10 rounded-lg bg-slate-800 flex items-center justify-center shrink-0 hidden sm:block">
                                <i class="fas fa-masks-theater text-slate-600 text-sm"></i>
                            </div>
                            @endif
                            <div>
                                <p class="text-white font-medium">{{ $element->name }}</p>
                                @if($element->is_featured)
                                <span class="text-amber-400 text-[10px]"><i class="fas fa-star mr-0.5"></i>En vedette</span>
                                @endif
                            </div>
                        </div>
                    </td>
                    <td class="px-5 py-4 hidden md:table-cell">
                        @if($element->domain)
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px]"
                            style="background-color: {{ $element->domain->color }}22; color: {{ $element->domain->color }}">
                            <i class="{{ $element->domain->icon }} text-[9px]"></i>
                            {{ $element->domain->name }}
                        </span>
                        @endif
                    </td>
                    <td class="px-5 py-4 hidden lg:table-cell">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] {{ $riskColors[$element->niveau_risque] ?? '' }}">
                            {{ $riskLabels[$element->niveau_risque] ?? $element->niveau_risque }}
                        </span>
                    </td>
                    <td class="px-5 py-4">
                        <form method="POST" action="{{ route('admin.cultural.elements.toggle', $element) }}">
                            @csrf @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold transition
                                    {{ $element->is_active
                                        ? 'bg-emerald-900/40 text-emerald-400 hover:bg-red-900/40 hover:text-red-400'
                                        : 'bg-slate-800 text-slate-500 hover:bg-emerald-900/40 hover:text-emerald-400' }}">
                                <i class="fas fa-circle text-[8px]"></i>
                                {{ $element->is_active ? 'Actif' : 'Inactif' }}
                            </button>
                        </form>
                    </td>
                    <td class="px-5 py-4 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form method="POST" action="{{ route('admin.cultural.elements.featured', $element) }}">
                                @csrf @method('PATCH')
                                <button type="submit"
                                    class="w-7 h-7 rounded-lg flex items-center justify-center transition
                                        {{ $element->is_featured ? 'bg-amber-500/20 text-amber-400' : 'bg-slate-800 text-slate-600 hover:text-amber-400' }}">
                                    <i class="fas fa-star text-xs"></i>
                                </button>
                            </form>
                            <a href="{{ route('admin.cultural.elements.edit', $element) }}"
                                class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition">
                                <i class="fas fa-pen text-xs"></i>
                            </a>
                            <form method="POST" action="{{ route('admin.cultural.elements.destroy', $element) }}"
                                onsubmit="return confirm('Supprimer {{ addslashes($element->name) }} ?')">
                                @csrf @method('DELETE')
                                <button type="submit"
                                    class="w-7 h-7 rounded-lg bg-slate-800 hover:bg-red-900/50 text-slate-500 hover:text-red-400 flex items-center justify-center transition">
                                    <i class="fas fa-trash text-xs"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-12 text-center text-slate-600">
                        <i class="fas fa-masks-theater text-4xl mb-3 block"></i>
                        Aucun élément culturel trouvé.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{ $elements->links() }}

@endsection
