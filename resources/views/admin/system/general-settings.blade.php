@extends('layouts.app')

@section('title', 'Paramètres généraux')
@section('page-title', 'Paramètres généraux')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'settings'])

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
        <div class="w-10 h-10 rounded-lg bg-violet-600/20 border border-violet-500/40 flex items-center justify-center shrink-0">
            <i class="fas fa-gear text-violet-300"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold text-lg">Paramètres généraux</h2>
            <p class="text-slate-400 text-xs mt-0.5">Identité du site, couleurs, fuseau horaire et mode maintenance.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.administration.settings.update') }}" enctype="multipart/form-data" class="p-5 sm:p-6 space-y-6 max-w-3xl">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <p class="font-medium text-rose-100 mb-1">Corrigez les champs suivants :</p>
                <ul class="list-disc list-inside space-y-0.5 text-rose-200/90">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div>
            <label for="site_name" class="block text-sm text-slate-300 mb-1">Nom du site *</label>
            <input type="text" name="site_name" id="site_name" required maxlength="255"
                   value="{{ old('site_name', $settings->site_name) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="site_slogan" class="block text-sm text-slate-300 mb-1">Slogan du site</label>
            <input type="text" name="site_slogan" id="site_slogan" maxlength="255"
                   value="{{ old('site_slogan', $settings->site_slogan) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="site_description" class="block text-sm text-slate-300 mb-1">Description du site</label>
            <textarea name="site_description" id="site_description" rows="4"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('site_description', $settings->site_description) }}</textarea>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-2">Logo du site</label>
            @if($settings->logo_url)
                <div class="mb-3 inline-block rounded-lg border border-slate-700 bg-slate-800/50 p-2 max-w-xs">
                    <img src="{{ $settings->logo_url }}" alt="Logo actuel" class="max-h-24 w-auto object-contain">
                </div>
            @endif
            <input type="file" name="site_logo" id="site_logo" accept="image/jpeg,image/png,image/webp"
                   class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs">
            <p class="text-slate-500 text-xs mt-1">JPEG, PNG ou WebP — max 2&nbsp;Mo. Laisser vide pour conserver le logo actuel.</p>
        </div>

        <div>
            <label class="block text-sm text-slate-300 mb-2">Favicon</label>
            @if($settings->favicon_url)
                <div class="mb-3 inline-flex items-center gap-2 rounded-lg border border-slate-700 bg-slate-800/50 p-2">
                    <img src="{{ $settings->favicon_url }}" alt="" class="h-10 w-10 object-contain rounded">
                </div>
            @endif
            <input type="file" name="favicon" id="favicon" accept="image/png,image/jpeg,image/webp,.ico"
                   class="w-full text-sm text-slate-200 file:mr-3 file:rounded file:border-0 file:bg-violet-600 file:px-3 file:py-2 file:text-white file:text-xs">
            <p class="text-slate-500 text-xs mt-1">PNG, JPEG, WebP ou ICO — max 512&nbsp;Ko.</p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="primary_color" class="block text-sm text-slate-300 mb-1">Couleur principale *</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="primary_color" id="primary_color" required
                           value="{{ old('primary_color', $settings->primary_color ?? '#7c3aed') }}"
                           class="h-10 w-14 cursor-pointer rounded border border-slate-600 bg-slate-800 p-0.5">
                    <input type="text" readonly value="{{ old('primary_color', $settings->primary_color ?? '#7c3aed') }}"
                           class="flex-1 bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-400 font-mono" id="primary_color_hex">
                </div>
            </div>
            <div>
                <label for="secondary_color" class="block text-sm text-slate-300 mb-1">Couleur secondaire</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="secondary_color" id="secondary_color"
                           value="{{ old('secondary_color', $settings->secondary_color ?? '#0ea5e9') }}"
                           class="h-10 w-14 cursor-pointer rounded border border-slate-600 bg-slate-800 p-0.5">
                    <input type="text" readonly value="{{ old('secondary_color', $settings->secondary_color ?? '#0ea5e9') }}"
                           class="flex-1 bg-slate-800/50 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-400 font-mono" id="secondary_color_hex">
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="timezone" class="block text-sm text-slate-300 mb-1">Fuseau horaire *</label>
                <select name="timezone" id="timezone" required
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100 max-h-48">
                    @foreach (timezone_identifiers_list() as $tz)
                        <option value="{{ $tz }}" @selected(old('timezone', $settings->timezone) === $tz)>{{ $tz }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="default_language" class="block text-sm text-slate-300 mb-1">Langue par défaut *</label>
                <select name="default_language" id="default_language" required
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <option value="fr" @selected(old('default_language', $settings->default_language) === 'fr')>Français</option>
                    <option value="en" @selected(old('default_language', $settings->default_language) === 'en')>English</option>
                </select>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 rounded-lg border border-slate-700 bg-slate-800/30 px-4 py-3">
            <div>
                <p class="text-sm font-medium text-white">Mode maintenance</p>
                <p class="text-xs text-slate-500 mt-0.5">Le site public affiche une page d’attente. Connexion, admins et back-office restent accessibles.</p>
            </div>
            <label class="inline-flex items-center gap-3 cursor-pointer shrink-0" title="Activer la page maintenance pour les visiteurs">
                <input type="checkbox" name="maintenance_mode" value="1"
                       class="h-5 w-5 rounded border-slate-600 bg-slate-800 text-violet-600 focus:ring-violet-500"
                       @checked($errors->any() ? old('maintenance_mode') === '1' : $settings->maintenance_mode)>
                <span class="text-sm text-slate-400">Activer</span>
            </label>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-floppy-disk"></i>
                Enregistrer les modifications
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        function syncHex(colorInput, hexInput) {
            if (!colorInput || !hexInput) return;
            hexInput.value = colorInput.value;
            colorInput.addEventListener('input', function () { hexInput.value = colorInput.value; });
        }
        syncHex(document.getElementById('primary_color'), document.getElementById('primary_color_hex'));
        syncHex(document.getElementById('secondary_color'), document.getElementById('secondary_color_hex'));
    })();
</script>
@endpush
