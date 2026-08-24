@extends('layouts.app')

@section('title', 'Image de fond — Page Tourisme')
@section('page-title', 'Image de fond — Page Tourisme')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'tourist-hero-image'])

@if(session('success'))
<div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
    <i class="fas fa-circle-check shrink-0"></i> {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="mb-5 px-4 py-3 bg-red-900/30 border border-red-800 text-red-300 text-sm rounded-xl">
    <ul class="list-disc list-inside space-y-0.5">
        @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
    </ul>
</div>
@endif

<div class="bg-green-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-green-950/20">
    <div class="px-5 py-4 bg-gradient-to-r from-green-700 via-green-600 to-green-600">
        <h2 class="text-white font-semibold text-lg tracking-tight">Image de fond — bandeau « Régions Touristiques »</h2>
        <p class="text-green-100/80 text-xs mt-0.5">Cette image s'affiche automatiquement en fond du bandeau d'accueil de la page « Tourisme » (liste des régions/villes).</p>
    </div>

    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">

        {{-- Aperçu de l'état actuel --}}
        <div>
            <p class="text-xs text-slate-400 mb-2 uppercase tracking-wide">Aperçu — état actuel</p>
            <div class="rounded-xl border border-slate-800 bg-slate-800/30 overflow-hidden">
                @if($touristHeroImage->image_url)
                    <img src="{{ $touristHeroImage->image_url }}" alt="" class="w-full h-48 object-cover bg-slate-900">
                @else
                    <div class="w-full h-48 flex items-center justify-center text-slate-600">
                        <i class="fas fa-image text-3xl"></i>
                    </div>
                @endif
                <div class="p-3 flex items-center justify-between">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $touristHeroImage->isVisible() ? 'bg-emerald-500/20 text-emerald-300 border border-emerald-500/30' : 'bg-slate-600/30 text-slate-300 border border-slate-600/40' }}">
                        {{ $touristHeroImage->isVisible() ? 'Affichée sur le site' : 'Non affichée' }}
                    </span>
                    @if($touristHeroImage->image_url)
                        <div class="flex items-center gap-2">
                            <form method="POST" action="{{ route('admin.administration.tourist-hero-image.toggle') }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500/90 hover:bg-orange-400 text-white text-xs font-medium transition">
                                    <i class="fas {{ $touristHeroImage->is_active ? 'fa-eye' : 'fa-eye-slash' }}"></i>
                                    {{ $touristHeroImage->is_active ? 'Désactiver' : 'Activer' }}
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.administration.tourist-hero-image.destroy') }}" onsubmit="return confirm('Supprimer l\'image de fond ?');">
                                @csrf @method('DELETE')
                                <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-700/90 hover:bg-red-600 text-white text-xs font-medium transition">
                                    <i class="fas fa-trash-can"></i> Supprimer
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Formulaire d'ajout / remplacement --}}
        <div>
            <p class="text-xs text-slate-400 mb-2 uppercase tracking-wide">{{ $touristHeroImage->image_url ? 'Remplacer l\'image' : 'Ajouter une image' }}</p>
            <form method="POST" action="{{ route('admin.administration.tourist-hero-image.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PUT')

                <label class="flex flex-col items-center justify-center border-2 border-dashed border-slate-700 hover:border-orange-500/50 rounded-xl p-5 cursor-pointer transition group">
                    <i class="fas fa-cloud-arrow-up text-2xl text-slate-600 group-hover:text-orange-400/70 mb-2 transition"></i>
                    <span class="text-slate-500 text-xs group-hover:text-slate-300 transition">Cliquez ou glissez une image (JPG, PNG, WebP — 5 Mo max)</span>
                    <input type="file" name="image_file" accept="image/jpeg,image/png,image/webp" class="hidden"
                           onchange="const r=new FileReader();r.onload=e=>{document.getElementById('tourist_hero_image_preview').src=e.target.result;document.getElementById('tourist_hero_image_preview').classList.remove('hidden');};r.readAsDataURL(this.files[0])">
                </label>
                <img id="tourist_hero_image_preview" src="" class="w-full h-40 object-cover rounded-lg border border-slate-700 bg-slate-900 hidden">

                <label class="flex items-center gap-2.5 cursor-pointer group">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ $touristHeroImage->is_active ? 'checked' : '' }}
                           class="rounded border-slate-600 bg-slate-800 text-orange-500">
                    <span class="text-sm text-slate-300 group-hover:text-white transition">Afficher l'image sur le site</span>
                </label>

                <button type="submit" class="w-full px-6 py-2.5 bg-orange-500 hover:bg-orange-600 text-black font-bold text-sm rounded-xl transition flex items-center justify-center gap-2">
                    <i class="fas fa-save"></i> Enregistrer
                </button>
            </form>
        </div>
    </div>
</div>

@endsection
