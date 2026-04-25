@extends('layouts.app')

@section('title', 'Message à un abonné')
@section('page-title', 'Newsletter')

@section('content')

    <div class="mb-6">
        <a href="{{ route('admin.newsletter.index') }}" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-amber-400 transition">
            <i class="fas fa-arrow-left text-xs"></i>
            Retour à la newsletter
        </a>
    </div>

    @if(session('success'))
        <div class="mb-5 px-4 py-3 bg-emerald-900/30 border border-emerald-800 text-emerald-300 text-sm rounded-xl flex items-center gap-2">
            <i class="fas fa-circle-check"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 px-4 py-3 bg-rose-900/30 border border-rose-800 text-rose-200 text-sm rounded-xl">
            <ul class="list-disc list-inside space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-xl p-5 h-fit">
            <h2 class="text-white font-semibold text-sm mb-4">Destinataire</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-slate-500 text-xs uppercase tracking-wider mb-0.5">E-mail</dt>
                    <dd class="text-white font-mono text-xs break-all">{{ $subscriber->email }}</dd>
                </div>
                <div>
                    <dt class="text-slate-500 text-xs uppercase tracking-wider mb-0.5">Statut</dt>
                    <dd><span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-emerald-500/15 text-emerald-300">{{ $subscriber->status }}</span></dd>
                </div>
                @if($subscriber->first_name)
                    <div>
                        <dt class="text-slate-500 text-xs uppercase tracking-wider mb-0.5">Prénom</dt>
                        <dd class="text-slate-200">{{ $subscriber->first_name }}</dd>
                    </div>
                @endif
                @if($subscriber->user)
                    <div>
                        <dt class="text-slate-500 text-xs uppercase tracking-wider mb-0.5">Compte lié</dt>
                        <dd class="text-slate-200">{{ $subscriber->user->first_name }} {{ $subscriber->user->last_name }}</dd>
                    </div>
                @endif
                <div>
                    <dt class="text-slate-500 text-xs uppercase tracking-wider mb-0.5">Locale</dt>
                    <dd class="text-slate-300">{{ $subscriber->locale }}</dd>
                </div>
            </dl>
        </div>

        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-lg mb-1">Message individuel</h2>
            <p class="text-slate-400 text-xs leading-relaxed mb-5">
                Ce message est envoyé <strong class="text-slate-300">uniquement à cette adresse</strong>. Il est enregistré dans l’historique (type individuel).
            </p>

            <form method="post" action="{{ route('admin.newsletter.subscribers.message.send', $subscriber) }}" class="space-y-4">
                @csrf
                <div>
                    <label for="title" class="block text-xs text-slate-400 mb-1">Référence interne (optionnel)</label>
                    <input type="text" name="title" id="title" maxlength="255" value="{{ old('title') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-600"
                           placeholder="Par défaut : Individuel — {{ $subscriber->email }}">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="subject_fr" class="block text-xs text-slate-400 mb-1">Objet du mail (FR) *</label>
                        <input type="text" name="subject_fr" id="subject_fr" required maxlength="255" value="{{ old('subject_fr') }}"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                    </div>
                    <div>
                        <label for="subject_en" class="block text-xs text-slate-400 mb-1">Objet (EN)</label>
                        <input type="text" name="subject_en" id="subject_en" maxlength="255" value="{{ old('subject_en') }}"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                    </div>
                </div>
                <fieldset class="space-y-2">
                    <legend class="block text-xs text-slate-400 mb-1">Format *</legend>
                    <div class="flex flex-wrap gap-4 text-sm text-slate-300">
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="content_format" value="plain" class="text-amber-500 bg-slate-800 border-slate-600"
                                   @checked(old('content_format', 'plain') === 'plain')>
                            Texte simple
                        </label>
                        <label class="inline-flex items-center gap-2 cursor-pointer">
                            <input type="radio" name="content_format" value="html" class="text-amber-500 bg-slate-800 border-slate-600"
                                   @checked(old('content_format') === 'html')>
                            HTML
                        </label>
                    </div>
                </fieldset>
                <div>
                    <label for="content_fr" class="block text-xs text-slate-400 mb-1">Message (FR) *</label>
                    <textarea name="content_fr" id="content_fr" rows="10" required
                              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-mono text-xs leading-relaxed">{{ old('content_fr') }}</textarea>
                </div>
                <div>
                    <label for="content_en" class="block text-xs text-slate-400 mb-1">Message (EN)</label>
                    <textarea name="content_en" id="content_en" rows="5"
                              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-mono text-xs leading-relaxed">{{ old('content_en') }}</textarea>
                </div>
                <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-800/80">
                    <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold rounded-lg transition">
                        <i class="fas fa-paper-plane"></i>
                        Envoyer à {{ $subscriber->email }}
                    </button>
                    <a href="{{ route('admin.newsletter.index') }}" class="text-sm text-slate-500 hover:text-slate-300 transition">Annuler</a>
                </div>
            </form>
        </div>
    </div>

@endsection
