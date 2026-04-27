@extends('layouts.app')

@section('title', 'Prestataires')
@section('page-title', 'Gestion des prestataires')

@section('content')
<div class="mb-6 flex justify-end">
    <button type="button"
            onclick="openCreateProviderModal()"
            class="inline-flex items-center gap-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
        <i class="fas fa-plus"></i>
        Créer un prestataire
    </button>
</div>

<div id="create-provider-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeCreateProviderModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-white font-semibold">Créer un prestataire</h2>
                <button type="button" onclick="closeCreateProviderModal()" class="text-slate-400 hover:text-white">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            @if($errors->any())
                <div id="create-provider-errors" class="mx-5 mt-4 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-sm">
                    <p class="font-semibold mb-1">Le formulaire contient des erreurs :</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.providers.store') }}" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 overflow-y-auto">
                @csrf
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Prénom *</label>
                    <input type="text" name="first_name" required value="{{ old('first_name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Nom *</label>
                    <input type="text" name="last_name" required value="{{ old('last_name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Email du compte *</label>
                    <input type="email" name="user_email" required value="{{ old('user_email') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Mot de passe *</label>
                    <input type="password" name="password" required
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-300 mb-1">Nom du prestataire *</label>
                    <input type="text" name="name" required value="{{ old('name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Catégorie *</label>
                    <select name="category_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="">Choisir...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" @selected((string) old('category_id') === (string) $cat->id)>{{ $cat->name_fr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Statut *</label>
                    <select name="status" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        @foreach(['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu'] as $value => $label)
                            <option value="{{ $value }}" @selected(old('status', 'pending') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Email public</label>
                    <input type="email" name="provider_email" value="{{ old('provider_email') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Téléphone</label>
                    <input type="text" name="phone" value="{{ old('phone') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Ville</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Adresse</label>
                    <input type="text" name="address" value="{{ old('address') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-300 mb-1">Description (FR)</label>
                    <textarea name="description_fr" rows="3"
                              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_fr') }}</textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured'))
                               class="rounded border-slate-600 bg-slate-800 text-amber-500">
                        Mettre en avant
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_verified" value="1" @checked(old('is_verified'))
                               class="rounded border-slate-600 bg-slate-800 text-amber-500">
                        Vérifié
                    </label>
                </div>
                <div class="md:col-span-2 flex justify-end gap-2 pt-1">
                    <button type="button" onclick="closeCreateProviderModal()"
                            class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Annuler
                    </button>
                    <button type="submit"
                            class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Créer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit-provider-modal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-black/70" onclick="closeEditProviderModal()"></div>
    <div class="absolute inset-0 p-4 sm:p-6 flex items-center justify-center">
        <div class="w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-xl shadow-2xl overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-5 py-4 border-b border-slate-800 flex items-center justify-between">
                <h2 class="text-white font-semibold">Modifier un prestataire</h2>
                <button type="button" onclick="closeEditProviderModal()" class="text-slate-400 hover:text-white">
                    <i class="fas fa-xmark text-lg"></i>
                </button>
            </div>

            @if($errors->any())
                <div id="edit-provider-errors" class="mx-5 mt-4 p-3 bg-red-900/30 border border-red-700 rounded-lg text-red-200 text-sm">
                    <p class="font-semibold mb-1">Le formulaire contient des erreurs :</p>
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" id="edit-provider-form" action="" class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4 overflow-y-auto">
                @csrf
                @method('PATCH')
                <input type="hidden" name="edit_provider_id" id="edit_provider_id" value="{{ old('edit_provider_id') }}">
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Prénom *</label>
                    <input type="text" name="first_name" id="edit_first_name" required value="{{ old('first_name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Nom *</label>
                    <input type="text" name="last_name" id="edit_last_name" required value="{{ old('last_name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Email du compte *</label>
                    <input type="email" name="user_email" id="edit_user_email" required value="{{ old('user_email') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Changer le mot de passe (optionnel)</label>
                    <input type="password" name="password" id="edit_password"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                           placeholder="Laisser vide pour conserver l'actuel">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Nom du prestataire *</label>
                    <input type="text" name="name" id="edit_name" required value="{{ old('name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Catégorie *</label>
                    <select name="category_id" id="edit_category_id" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        <option value="">Choisir...</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name_fr }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Statut *</label>
                    <select name="status" id="edit_status" required class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                        @foreach(['pending' => 'En attente', 'active' => 'Actif', 'suspended' => 'Suspendu'] as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Email public</label>
                    <input type="email" name="provider_email" id="edit_provider_email" value="{{ old('provider_email') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Téléphone</label>
                    <input type="text" name="phone" id="edit_phone" value="{{ old('phone') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Ville</label>
                    <input type="text" name="city" id="edit_city" value="{{ old('city') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div>
                    <label class="block text-sm text-slate-300 mb-1">Adresse</label>
                    <input type="text" name="address" id="edit_address" value="{{ old('address') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-sm text-slate-300 mb-1">Description (FR)</label>
                    <textarea name="description_fr" id="edit_description_fr" rows="3"
                              class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">{{ old('description_fr') }}</textarea>
                </div>
                <div class="md:col-span-2 flex items-center gap-6">
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_featured" id="edit_is_featured" value="1"
                               class="rounded border-slate-600 bg-slate-800 text-amber-500">
                        Mettre en avant
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-slate-300">
                        <input type="checkbox" name="is_verified" id="edit_is_verified" value="1"
                               class="rounded border-slate-600 bg-slate-800 text-amber-500">
                        Vérifié
                    </label>
                </div>
                <div class="md:col-span-2 flex justify-end gap-2 pt-1">
                    <button type="button" onclick="closeEditProviderModal()"
                            class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Annuler
                    </button>
                    <button type="submit"
                            class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Tous</p>
        <p class="text-white text-2xl font-bold mt-1">{{ number_format($counts['all']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Actifs</p>
        <p class="text-emerald-400 text-2xl font-bold mt-1">{{ number_format($counts['active']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">En attente</p>
        <p class="text-amber-400 text-2xl font-bold mt-1">{{ number_format($counts['pending']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Suspendus</p>
        <p class="text-red-400 text-2xl font-bold mt-1">{{ number_format($counts['suspended']) }}</p>
    </div>
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4">
        <p class="text-slate-500 text-xs">Mis en avant</p>
        <p class="text-violet-400 text-2xl font-bold mt-1">{{ number_format($counts['featured']) }}</p>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Liste des prestataires</h2>
        <form method="GET" action="{{ route('admin.providers.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="text" name="q" value="{{ $search }}" placeholder="Rechercher nom, ville ou email..."
                   class="md:col-span-2 bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
            <select name="status" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Tous les statuts</option>
                @foreach(['active' => 'Actif', 'pending' => 'En attente', 'suspended' => 'Suspendu'] as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="category" class="bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                <option value="">Toutes les catégories</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" @selected((string) $category === (string) $cat->id)>{{ $cat->name_fr }}</option>
                @endforeach
            </select>
            <div class="md:col-span-4 flex items-center gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Filtrer</button>
                <a href="{{ route('admin.providers.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">Réinitialiser</a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800 text-slate-500 text-xs uppercase">
                    <th class="text-left px-5 py-3">Prestataire</th>
                    <th class="text-left px-5 py-3">Catégorie</th>
                    <th class="text-left px-5 py-3">Contact</th>
                    <th class="text-left px-5 py-3">Statut</th>
                    <th class="text-left px-5 py-3">Contenus</th>
                    <th class="text-left px-5 py-3">Compte</th>
                    <th class="text-left px-5 py-3">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($providers as $provider)
                    <tr class="hover:bg-slate-800/30">
                        <td class="px-5 py-3">
                            <p class="text-white font-medium">{{ $provider->name }}</p>
                            <p class="text-slate-500 text-xs mt-0.5">{{ $provider->city ?: 'Ville non précisée' }}</p>
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ $provider->category->name_fr ?? '—' }}</td>
                        <td class="px-5 py-3">
                            <p class="text-slate-200">{{ $provider->email ?: '—' }}</p>
                            <p class="text-slate-500 text-xs mt-0.5">{{ $provider->phone ?: 'Téléphone non renseigné' }}</p>
                        </td>
                        <td class="px-5 py-3">
                            @php
                                $statusClass = match($provider->status) {
                                    'active' => 'bg-emerald-500/20 text-emerald-300',
                                    'pending' => 'bg-amber-500/20 text-amber-300',
                                    'suspended' => 'bg-red-500/20 text-red-300',
                                    default => 'bg-slate-500/20 text-slate-300',
                                };
                            @endphp
                            <span class="px-2.5 py-1 rounded-full text-xs {{ $statusClass }}">{{ $provider->status ?? '—' }}</span>
                        </td>
                        <td class="px-5 py-3 text-slate-300">
                            <p class="text-xs">Articles: <span class="text-white font-semibold">{{ $provider->sponsored_articles_count ?? 0 }}</span></p>
                            <p class="text-xs mt-0.5">Événements: <span class="text-white font-semibold">{{ $provider->events_count ?? 0 }}</span></p>
                            <p class="text-xs mt-0.5">Photos/Médias: <span class="text-white font-semibold">{{ $provider->media_count ?? 0 }}</span></p>
                        </td>
                        <td class="px-5 py-3 text-slate-300">{{ $provider->user->email ?? 'Aucun utilisateur lié' }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.providers.content', $provider) }}"
                                   class="bg-violet-600 hover:bg-violet-500 text-white text-xs px-3 py-1.5 rounded">
                                    Gérer contenus
                                </a>
                                @if($provider->status !== 'active')
                                    <form method="POST" action="{{ route('admin.providers.validate', $provider) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-emerald-600 hover:bg-emerald-500 text-white text-xs px-3 py-1.5 rounded">
                                            Valider
                                        </button>
                                    </form>
                                @endif
                                <button type="button"
                                        onclick="openEditProviderModal(this)"
                                        data-update-url="{{ route('admin.providers.update', $provider) }}"
                                        data-provider-id="{{ $provider->id }}"
                                        data-first-name="{{ $provider->user->first_name ?? '' }}"
                                        data-last-name="{{ $provider->user->last_name ?? '' }}"
                                        data-user-email="{{ $provider->user->email ?? '' }}"
                                        data-name="{{ $provider->name }}"
                                        data-category-id="{{ $provider->category_id }}"
                                        data-status="{{ $provider->status }}"
                                        data-provider-email="{{ $provider->email ?? '' }}"
                                        data-phone="{{ $provider->phone ?? '' }}"
                                        data-city="{{ $provider->city ?? '' }}"
                                        data-address="{{ $provider->address ?? '' }}"
                                        data-description-fr="{{ $provider->description_fr ?? '' }}"
                                        data-is-featured="{{ $provider->is_featured ? '1' : '0' }}"
                                        data-is-verified="{{ $provider->is_verified ? '1' : '0' }}"
                                        class="bg-blue-600 hover:bg-blue-500 text-white text-xs px-3 py-1.5 rounded">
                                    Modifier
                                </button>
                                @if($provider->status !== 'suspended')
                                    <form method="POST"
                                          action="{{ route('admin.providers.suspend', $provider) }}"
                                          onsubmit="return confirm('Confirmer la suspension de ce prestataire ?');">
                                        @csrf
                                        @method('PATCH')
                                        <button class="bg-orange-600 hover:bg-orange-500 text-white text-xs px-3 py-1.5 rounded">
                                            Suspendre
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-8 text-center text-slate-500">Aucun prestataire trouvé.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $providers->links() }}
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openCreateProviderModal() {
        document.getElementById('create-provider-modal').classList.remove('hidden');
    }

    function closeCreateProviderModal() {
        document.getElementById('create-provider-modal').classList.add('hidden');
    }

    function openEditProviderModal(button) {
        const form = document.getElementById('edit-provider-form');
        form.action = button.dataset.updateUrl;

        document.getElementById('edit_provider_id').value = button.dataset.providerId || '';
        document.getElementById('edit_first_name').value = button.dataset.firstName || '';
        document.getElementById('edit_last_name').value = button.dataset.lastName || '';
        document.getElementById('edit_user_email').value = button.dataset.userEmail || '';
        document.getElementById('edit_password').value = '';
        document.getElementById('edit_name').value = button.dataset.name || '';
        document.getElementById('edit_category_id').value = button.dataset.categoryId || '';
        document.getElementById('edit_status').value = button.dataset.status || 'pending';
        document.getElementById('edit_provider_email').value = button.dataset.providerEmail || '';
        document.getElementById('edit_phone').value = button.dataset.phone || '';
        document.getElementById('edit_city').value = button.dataset.city || '';
        document.getElementById('edit_address').value = button.dataset.address || '';
        document.getElementById('edit_description_fr').value = button.dataset.descriptionFr || '';
        document.getElementById('edit_is_featured').checked = button.dataset.isFeatured === '1';
        document.getElementById('edit_is_verified').checked = button.dataset.isVerified === '1';

        document.getElementById('edit-provider-modal').classList.remove('hidden');
    }

    function closeEditProviderModal() {
        document.getElementById('edit-provider-modal').classList.add('hidden');
    }

    if (document.getElementById('create-provider-errors')) {
        openCreateProviderModal();
    }

    if (document.getElementById('edit-provider-errors')) {
        const providerId = document.getElementById('edit_provider_id').value;
        if (providerId) {
            const trigger = document.querySelector('[data-provider-id="' + providerId + '"]');
            if (trigger) {
                openEditProviderModal(trigger);
            }
        } else {
            document.getElementById('edit-provider-modal').classList.remove('hidden');
        }
    }
</script>
@endpush
