@extends('layouts.app')

@section('title', "Paramètres d'accueil")
@section('page-title', "Paramètres d'accueil")

@section('content')
@include('admin.system.partials.administration-settings-tabs', ['active' => 'homepage'])

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden shadow-lg shadow-black/20">
    <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
        <div class="w-10 h-10 rounded-lg bg-violet-600/20 border border-violet-500/40 flex items-center justify-center shrink-0">
            <i class="fas fa-house text-violet-300"></i>
        </div>
        <div>
            <h2 class="text-white font-semibold text-lg">Accueil</h2>
            <p class="text-slate-400 text-xs mt-0.5">Configurez le contenu principal de la page d'accueil.</p>
        </div>
    </div>

    <form method="POST" action="{{ route('admin.administration.homepage.update') }}" class="p-5 sm:p-6 space-y-6 max-w-3xl">
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

        @unless($hasHomeDestinationColumn ?? false)
            <div class="rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-100">
                Le réglage n'est pas encore disponible sur cette base. Exécutez <code>php artisan migrate</code> puis rechargez la page.
            </div>
        @endunless

        <div>
            <label for="home_destination_article_id" class="block text-sm text-slate-300 mb-1">Destination du mois (hero)</label>
            <select name="home_destination_article_id" id="home_destination_article_id"
                    @disabled(!($hasHomeDestinationColumn ?? false))
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Automatique (article mis en avant)</option>
                <option value="0" @selected((string) old('home_destination_article_id', $settings->home_destination_article_id) === '0')>Aucun element mis en avant</option>
                @foreach($publishedArticles as $article)
                    <option value="{{ $article->id }}" @selected((string) old('home_destination_article_id', $settings->home_destination_article_id) === (string) $article->id)>
                        {{ $article->title_fr }}@if($article->published_at) - {{ $article->published_at->format('d/m/Y') }}@endif
                    </option>
                @endforeach
            </select>
            <p class="text-slate-500 text-xs mt-1">Choix possible: automatique, aucun element mis en avant, ou un article precis.</p>
        </div>

        <div class="pt-2">
            <button type="submit"
                    @disabled(!($hasHomeDestinationColumn ?? false))
                    class="inline-flex items-center gap-2 bg-violet-600 hover:bg-violet-500 text-white text-sm font-semibold px-5 py-2.5 rounded-lg transition">
                <i class="fas fa-floppy-disk"></i>
                Enregistrer
            </button>
        </div>
    </form>
</div>
@endsection
