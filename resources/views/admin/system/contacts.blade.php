@extends('layouts.app')

@section('title', 'Informations de contact')
@section('page-title', 'Informations de contact')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'contacts'])

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
        <div class="w-10 h-10 rounded-lg bg-sky-600/20 border border-sky-500/40 flex items-center justify-center shrink-0">
            <i class="fas fa-envelope text-sky-300"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold text-lg">Informations de contact</h2>
            <p class="text-slate-400 text-xs mt-0.5">Téléphones, e-mails, horaires et coordonnées géographiques.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.administration.contacts.update') }}" class="p-5 sm:p-6 space-y-5 max-w-4xl">
        @csrf
        @method('PUT')

        @if ($errors->any())
            <div class="rounded-lg border border-rose-500/40 bg-rose-500/10 px-4 py-3 text-sm text-rose-200">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="phone_1" class="block text-sm text-slate-300 mb-1">Téléphone 1</label>
                <input type="text" name="phone_1" id="phone_1" maxlength="64"
                       value="{{ old('phone_1', $contact->phone_1) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label for="phone_2" class="block text-sm text-slate-300 mb-1">Téléphone 2</label>
                <input type="text" name="phone_2" id="phone_2" maxlength="64"
                       value="{{ old('phone_2', $contact->phone_2) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="email_primary" class="block text-sm text-slate-300 mb-1">E-mail principal</label>
                <input type="email" name="email_primary" id="email_primary" maxlength="255"
                       value="{{ old('email_primary', $contact->email_primary) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label for="email_secondary" class="block text-sm text-slate-300 mb-1">E-mail secondaire</label>
                <input type="email" name="email_secondary" id="email_secondary" maxlength="255"
                       value="{{ old('email_secondary', $contact->email_secondary) }}"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
        </div>

        <div>
            <label for="contact_form_email" class="block text-sm text-slate-300 mb-1">E-mail pour formulaire de contact</label>
            <input type="email" name="contact_form_email" id="contact_form_email" maxlength="255"
                   value="{{ old('contact_form_email', $contact->contact_form_email) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <p class="text-slate-500 text-xs mt-1">Adresse qui recevra les messages envoyés depuis le formulaire de contact du site. Les messages sont aussi listés sous <span class="text-slate-400 font-medium">Administration → Messages reçus</span>.</p>
        </div>

        <div>
            <label for="opening_hours" class="block text-sm text-slate-300 mb-1">Horaires d’ouverture</label>
            <textarea name="opening_hours" id="opening_hours" rows="3"
                      class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('opening_hours', $contact->opening_hours) }}</textarea>
        </div>

        <div>
            <label for="address" class="block text-sm text-slate-300 mb-1">Adresse</label>
            <input type="text" name="address" id="address" maxlength="500"
                   value="{{ old('address', $contact->address) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="latitude" class="block text-sm text-slate-300 mb-1">Latitude (Google Maps)</label>
                <input type="text" name="latitude" id="latitude" maxlength="32" inputmode="decimal"
                       value="{{ old('latitude', $contact->latitude) }}"
                       placeholder="ex. 5.3364"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
            <div>
                <label for="longitude" class="block text-sm text-slate-300 mb-1">Longitude (Google Maps)</label>
                <input type="text" name="longitude" id="longitude" maxlength="32" inputmode="decimal"
                       value="{{ old('longitude', $contact->longitude) }}"
                       placeholder="ex. -4.0277"
                       class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            </div>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-violet-600 hover:from-amber-400 hover:to-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-lock"></i>
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
