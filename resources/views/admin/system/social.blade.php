@extends('layouts.app')

@section('title', 'Réseaux sociaux')
@section('page-title', 'Réseaux sociaux')

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'social'])

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
        <div class="w-10 h-10 rounded-lg bg-sky-600/20 border border-sky-500/40 flex items-center justify-center shrink-0">
            <i class="fas fa-share-nodes text-sky-300"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold text-lg">Réseaux sociaux</h2>
            <p class="text-slate-400 text-xs mt-0.5">Liens complets (https://…) vers vos pages officielles.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.administration.social.update') }}" class="p-5 sm:p-6 space-y-5 max-w-3xl">
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

        <div>
            <label for="facebook_url" class="flex items-center gap-2 text-sm text-slate-300 mb-1">
                <i class="fab fa-facebook text-blue-400 w-5 text-center"></i> Facebook
            </label>
            <input type="url" name="facebook_url" id="facebook_url" maxlength="500" placeholder="https://facebook.com/…"
                   value="{{ old('facebook_url', $social->facebook_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="twitter_url" class="flex items-center gap-2 text-sm text-slate-300 mb-1">
                <i class="fab fa-twitter text-slate-200 w-5 text-center"></i> Twitter / X
            </label>
            <input type="url" name="twitter_url" id="twitter_url" maxlength="500" placeholder="https://twitter.com/…"
                   value="{{ old('twitter_url', $social->twitter_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="linkedin_url" class="flex items-center gap-2 text-sm text-slate-300 mb-1">
                <i class="fab fa-linkedin text-sky-400 w-5 text-center"></i> LinkedIn
            </label>
            <input type="url" name="linkedin_url" id="linkedin_url" maxlength="500" placeholder="https://linkedin.com/…"
                   value="{{ old('linkedin_url', $social->linkedin_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="instagram_url" class="flex items-center gap-2 text-sm text-slate-300 mb-1">
                <i class="fab fa-instagram text-pink-400 w-5 text-center"></i> Instagram
            </label>
            <input type="url" name="instagram_url" id="instagram_url" maxlength="500" placeholder="https://instagram.com/…"
                   value="{{ old('instagram_url', $social->instagram_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="youtube_url" class="flex items-center gap-2 text-sm text-slate-300 mb-1">
                <i class="fab fa-youtube text-red-500 w-5 text-center"></i> YouTube
            </label>
            <input type="url" name="youtube_url" id="youtube_url" maxlength="500" placeholder="https://youtube.com/…"
                   value="{{ old('youtube_url', $social->youtube_url) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
        </div>

        <div>
            <label for="whatsapp_phone" class="flex items-center gap-2 text-sm text-slate-300 mb-1">
                <i class="fab fa-whatsapp text-emerald-400 w-5 text-center"></i> WhatsApp
            </label>
            <input type="text" name="whatsapp_phone" id="whatsapp_phone" maxlength="64" placeholder="+225 05 05 93 94 47"
                   value="{{ old('whatsapp_phone', $social->whatsapp_phone) }}"
                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <p class="text-slate-500 text-xs mt-1">Format international recommandé&nbsp;: <span class="text-slate-400 font-mono">+225 05 XX XX XX XX</span></p>
        </div>

        <div class="pt-2">
            <button type="submit"
                    class="inline-flex items-center gap-2 bg-gradient-to-r from-rose-500 to-violet-600 hover:from-rose-400 hover:to-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-lock"></i>
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
