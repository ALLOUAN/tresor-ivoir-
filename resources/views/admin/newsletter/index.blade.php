@extends('layouts.app')

@section('title', 'Newsletter — messages aux abonnés')
@section('page-title', 'Newsletter')

@section('content')

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

    {{-- ── Envoi d’un message à tous les abonnés actifs ───────────────────── --}}
    <div class="grid gap-6 lg:grid-cols-3 mb-8">
        <div class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-xl p-5">
            <div class="w-10 h-10 rounded-lg bg-amber-500/15 border border-amber-500/30 flex items-center justify-center mb-3">
                <i class="fas fa-paper-plane text-amber-300"></i>
            </div>
            <h2 class="text-white font-semibold text-base mb-2">Destinataires</h2>
            <p class="text-slate-500 text-xs uppercase tracking-wider mb-1">Abonnés actifs</p>
            @if($hasSubscribers)
                <p class="text-white text-3xl font-bold font-serif">{{ $activeCount }}</p>
                <p class="text-slate-500 text-xs mt-3 leading-relaxed">
                    Chaque envoi part à <strong class="text-slate-300">toutes les adresses au statut « actif »</strong> (inscrites sur le site ou depuis l’espace membre).
                </p>
            @else
                <p class="text-amber-400/90 text-sm">Table absente ou aucune donnée — voir les alertes ci-dessous.</p>
            @endif
        </div>
        <div class="lg:col-span-2 bg-slate-900 border border-slate-800 rounded-xl p-5">
            <h2 class="text-white font-semibold text-lg mb-1 flex items-center gap-2">
                <i class="fas fa-envelope text-sky-400"></i>
                Envoyer un message aux abonnés
            </h2>
            <p class="text-slate-400 text-xs leading-relaxed mb-5">
                Rédigez l’objet et le corps du mail. Choisissez <strong class="text-slate-300">texte simple</strong> (sécurisé, retours à la ligne conservés) ou <strong class="text-slate-300">HTML</strong> pour une mise en forme avancée.
                Pour <strong class="text-slate-300">une seule adresse</strong>, utilisez le lien <span class="text-sky-400/90">Individuel</span> dans le tableau des inscrits.
                Configuration e-mail : <span class="text-slate-300">MAIL_*</span> dans <code class="text-[11px] bg-slate-800 px-1 rounded">.env</code>.
            </p>
            @if($hasSubscribers && $hasCampaigns && $activeCount > 0)
                <form method="post" action="{{ route('admin.newsletter.send') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label for="title" class="block text-xs text-slate-400 mb-1">Référence interne (historique)</label>
                        <input type="text" name="title" id="title" required maxlength="255" value="{{ old('title') }}"
                               class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-600"
                               placeholder="Ex. : Message du 24 avril — Actualités">
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="subject_fr" class="block text-xs text-slate-400 mb-1">Objet du mail (FR) *</label>
                            <input type="text" name="subject_fr" id="subject_fr" required maxlength="255" value="{{ old('subject_fr') }}"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white"
                                   placeholder="Objet visible dans la boîte mail">
                        </div>
                        <div>
                            <label for="subject_en" class="block text-xs text-slate-400 mb-1">Objet (EN)</label>
                            <input type="text" name="subject_en" id="subject_en" maxlength="255" value="{{ old('subject_en') }}"
                                   class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white">
                        </div>
                    </div>
                    <fieldset class="space-y-2">
                        <legend class="block text-xs text-slate-400 mb-1">Format du message *</legend>
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
                                  class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-mono text-xs leading-relaxed"
                                  placeholder="Bonjour,

Votre texte ici… (ou balises HTML si format HTML coché).">{{ old('content_fr') }}</textarea>
                    </div>
                    <div>
                        <label for="content_en" class="block text-xs text-slate-400 mb-1">Message (EN)</label>
                        <textarea name="content_en" id="content_en" rows="5"
                                  class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white font-mono text-xs leading-relaxed">{{ old('content_en') }}</textarea>
                        <p class="text-slate-600 text-[11px] mt-1">Optionnel : utilisé pour les abonnés avec locale <code class="bg-slate-800 px-1 rounded">en</code>.</p>
                    </div>
                    <div class="flex flex-wrap items-center gap-3 pt-2 border-t border-slate-800/80">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-400 text-slate-900 text-sm font-bold rounded-lg transition">
                            <i class="fas fa-paper-plane"></i>
                            Envoyer le message à {{ $activeCount }} abonné(s) actif(s)
                        </button>
                    </div>
                </form>
            @elseif($hasSubscribers && $activeCount === 0)
                <p class="text-slate-500 text-sm">Aucun abonné actif : ajoutez des inscriptions depuis le site ou réactivez des comptes avant d’envoyer un message.</p>
            @elseif(! $hasCampaigns)
                <p class="text-amber-400/90 text-sm">Table <code class="text-xs bg-slate-800 px-1 rounded">newsletter_campaigns</code> absente — exécutez les migrations.</p>
            @elseif(! $hasSubscribers)
                <p class="text-amber-400/90 text-sm">Table <code class="text-xs bg-slate-800 px-1 rounded">newsletter_subscribers</code> absente — exécutez les migrations.</p>
            @endif
        </div>
    </div>

    @if($hasSubscribers && $subscribers)
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden mb-8">
            <div class="px-5 py-4 border-b border-slate-800 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 bg-slate-800/40">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/15 border border-emerald-500/30 flex items-center justify-center shrink-0">
                        <i class="fas fa-address-book text-emerald-300"></i>
                    </div>
                    <div>
                        <h2 class="text-white font-semibold text-lg">Liste des inscrits</h2>
                        <p class="text-slate-400 text-xs mt-0.5">Filtrez par e-mail ou statut ; les messages partent uniquement aux abonnés <strong class="text-emerald-300/90">actifs</strong>.</p>
                    </div>
                </div>
                <a href="{{ route('admin.newsletter.subscribers.export', array_filter(['q' => $q, 'status' => $statusFilter])) }}"
                   class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg border border-slate-600 text-slate-200 hover:bg-slate-800 text-sm transition shrink-0">
                    <i class="fas fa-file-csv text-emerald-400"></i>
                    Exporter CSV
                </a>
            </div>
            <div class="px-5 py-4 border-b border-slate-800 flex flex-wrap gap-3 text-xs">
                <span class="px-2.5 py-1 rounded-lg bg-slate-800 text-slate-300">Total <strong class="text-white">{{ $subscriberStats['total'] }}</strong></span>
                <span class="px-2.5 py-1 rounded-lg bg-emerald-500/10 text-emerald-300">Actifs <strong>{{ $subscriberStats['active'] }}</strong></span>
                <span class="px-2.5 py-1 rounded-lg bg-amber-500/10 text-amber-200">En attente <strong>{{ $subscriberStats['pending'] }}</strong></span>
                <span class="px-2.5 py-1 rounded-lg bg-slate-700 text-slate-400">Désinscrits <strong>{{ $subscriberStats['unsubscribed'] }}</strong></span>
                <span class="px-2.5 py-1 rounded-lg bg-rose-500/10 text-rose-200">Rebonds <strong>{{ $subscriberStats['bounced'] }}</strong></span>
            </div>
            <form method="get" action="{{ route('admin.newsletter.index') }}" class="px-5 py-3 border-b border-slate-800 flex flex-col sm:flex-row gap-3 sm:items-end">
                <div class="flex-1 min-w-0">
                    <label for="q" class="block text-xs text-slate-500 mb-1">Recherche e-mail</label>
                    <input type="search" name="q" id="q" value="{{ $q }}" maxlength="200" placeholder="ex. @gmail.com"
                           class="w-full max-w-md bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white placeholder-slate-600">
                </div>
                <div>
                    <label for="status" class="block text-xs text-slate-500 mb-1">Statut</label>
                    <select name="status" id="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-white min-w-[160px]">
                        <option value="">Tous</option>
                        <option value="active" @selected($statusFilter === 'active')>Actif</option>
                        <option value="pending" @selected($statusFilter === 'pending')>En attente</option>
                        <option value="unsubscribed" @selected($statusFilter === 'unsubscribed')>Désinscrit</option>
                        <option value="bounced" @selected($statusFilter === 'bounced')>Rebond</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-600 text-white text-sm font-medium rounded-lg transition">Filtrer</button>
                    <a href="{{ route('admin.newsletter.index') }}" class="px-4 py-2 border border-slate-600 text-slate-300 text-sm rounded-lg hover:bg-slate-800 transition">Réinitialiser</a>
                </div>
            </form>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-500 text-xs uppercase border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3 font-medium">E-mail</th>
                            <th class="px-5 py-3 font-medium">Statut</th>
                            <th class="px-5 py-3 font-medium">Source</th>
                            <th class="px-5 py-3 font-medium">Compte</th>
                            <th class="px-5 py-3 font-medium">Inscrit le</th>
                            <th class="px-5 py-3 font-medium text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @foreach($subscribers as $s)
                            <tr class="text-slate-300 hover:bg-slate-800/30">
                                <td class="px-5 py-3 text-white font-mono text-xs">{{ $s->email }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        {{ $s->status === 'active' ? 'bg-emerald-500/15 text-emerald-300' : '' }}
                                        {{ $s->status === 'pending' ? 'bg-amber-500/15 text-amber-200' : '' }}
                                        {{ $s->status === 'unsubscribed' ? 'bg-slate-700 text-slate-400' : '' }}
                                        {{ $s->status === 'bounced' ? 'bg-rose-500/15 text-rose-300' : '' }}">
                                        {{ $s->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs">{{ $s->source ?? '—' }}</td>
                                <td class="px-5 py-3 text-slate-500 text-xs">
                                    @if($s->user)
                                        <span class="text-slate-300">{{ $s->user->first_name }} {{ $s->user->last_name }}</span>
                                        <span class="text-slate-600">#{{ $s->user_id }}</span>
                                    @else
                                        —
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-slate-500 text-xs whitespace-nowrap">{{ $s->created_at?->format('d/m/Y H:i') }}</td>
                                <td class="px-5 py-3 text-right">
                                    @if($s->status === 'active')
                                        <a href="{{ route('admin.newsletter.subscribers.message', $s) }}"
                                           class="inline-flex items-center gap-1.5 text-xs font-medium text-sky-400 hover:text-sky-300 transition">
                                            <i class="fas fa-envelope text-[10px]"></i>
                                            Individuel
                                        </a>
                                    @else
                                        <span class="text-slate-600 text-xs" title="Réservé aux abonnés actifs">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-4 border-t border-slate-800">
                {{ $subscribers->links() }}
            </div>
        </div>
    @elseif(! $hasSubscribers)
        <div class="mb-8 px-4 py-3 bg-amber-500/10 border border-amber-500/25 text-amber-100 text-sm rounded-xl">
            Table <code class="text-xs bg-slate-900 px-1 rounded">newsletter_subscribers</code> absente — exécutez les migrations pour collecter les inscriptions.
        </div>
    @endif

    @if($hasCampaigns)
        <div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center gap-3 bg-slate-800/40">
                <div class="w-10 h-10 rounded-lg bg-violet-500/15 border border-violet-500/30 flex items-center justify-center shrink-0">
                    <i class="fas fa-clock-rotate-left text-violet-300"></i>
                </div>
                <div>
                    <h2 class="text-white font-semibold text-lg">Historique des messages envoyés</h2>
                    <p class="text-slate-400 text-xs mt-0.5">Chaque envoi groupé est enregistré ici.</p>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="text-slate-500 text-xs uppercase border-b border-slate-800">
                        <tr>
                            <th class="px-5 py-3 font-medium">Référence</th>
                            <th class="px-5 py-3 font-medium">Objet (FR)</th>
                            <th class="px-5 py-3 font-medium">Statut</th>
                            <th class="px-5 py-3 font-medium text-right">Destinataires</th>
                            <th class="px-5 py-3 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-800">
                        @forelse($campaigns as $c)
                            <tr class="text-slate-300 hover:bg-slate-800/30">
                                <td class="px-5 py-3 text-white font-medium">{{ $c->title }}</td>
                                <td class="px-5 py-3 max-w-xs truncate" title="{{ $c->subject_fr }}">{{ $c->subject_fr }}</td>
                                <td class="px-5 py-3">
                                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium
                                        {{ $c->status === 'sent' ? 'bg-emerald-500/15 text-emerald-300' : '' }}
                                        {{ $c->status === 'draft' ? 'bg-slate-700 text-slate-400' : '' }}
                                        {{ $c->status === 'sending' ? 'bg-amber-500/15 text-amber-200' : '' }}
                                        {{ $c->status === 'cancelled' ? 'bg-rose-500/15 text-rose-300' : '' }}">
                                        {{ $c->status }}
                                    </span>
                                </td>
                                <td class="px-5 py-3 text-right tabular-nums">{{ $c->recipients_count }}</td>
                                <td class="px-5 py-3 text-slate-500 text-xs whitespace-nowrap">
                                    {{ $c->sent_at?->format('d/m/Y H:i') ?? '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-10 text-center text-slate-500">Aucun envoi enregistré pour l’instant.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

@endsection
