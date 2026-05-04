@extends('layouts.app')

@section('title', 'Utilisateurs')
@section('page-title', 'Attribution des roles et permissions')

@section('content')
@php
    $roleLabels = [
        'admin' => 'Administrateur',
        'editor' => 'Editeur',
        'provider' => 'Prestataire',
        'visitor' => 'Visiteur',
    ];

    $roleBadgeClasses = [
        'admin' => 'bg-rose-500/20 text-rose-300 border-rose-500/40',
        'editor' => 'bg-blue-500/20 text-blue-300 border-blue-500/40',
        'provider' => 'bg-violet-500/20 text-violet-300 border-violet-500/40',
        'visitor' => 'bg-emerald-500/20 text-emerald-300 border-emerald-500/40',
    ];

@endphp

@php
    $showCreateUserForm = old('first_name') || old('last_name') || old('email') || $errors->has('password');
@endphp

<div class="flex flex-wrap items-center gap-3 mb-6">
    <button
        type="button"
        id="open-create-user-modal"
        class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg"
    >
        Creer un utilisateur
    </button>
    <span class="text-slate-500 text-sm">Cliquez pour ouvrir la fenetre de creation.</span>
</div>

<div id="create-user-modal" class="fixed inset-0 z-50 {{ $showCreateUserForm ? '' : 'hidden' }}">
    <div id="create-user-modal-overlay" class="absolute inset-0 bg-black/70"></div>
    <div class="relative min-h-full flex items-center justify-center p-4">
        <div class="w-full max-w-3xl bg-slate-900 border border-slate-700 rounded-2xl shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-slate-800">
                <div>
                    <h2 class="text-white font-semibold">Creer un utilisateur</h2>
                    <p class="text-slate-400 text-sm mt-1">Ajoutez un nouvel utilisateur avec un role et des permissions optionnelles.</p>
                </div>
                <button type="button" id="close-create-user-modal" class="text-slate-400 hover:text-white text-lg">
                    <i class="fas fa-xmark"></i>
                </button>
            </div>

            <form method="POST" action="{{ route('admin.users.store') }}" class="grid grid-cols-1 md:grid-cols-2 gap-4 p-5">
                @csrf

                <div>
                    <label class="block text-slate-300 text-xs mb-1">Prenom</label>
                    <input name="first_name" type="text" required value="{{ old('first_name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>

                <div>
                    <label class="block text-slate-300 text-xs mb-1">Nom</label>
                    <input name="last_name" type="text" required value="{{ old('last_name') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>

                <div>
                    <label class="block text-slate-300 text-xs mb-1">Email</label>
                    <input name="email" type="email" required value="{{ old('email') }}"
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>

                <div>
                    <label class="block text-slate-300 text-xs mb-1">Mot de passe</label>
                    <input name="password" type="password" required
                           class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                </div>

                <div>
                    <label class="block text-slate-300 text-xs mb-1">Role</label>
                    <select name="role" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100" required>
                        @foreach($roles as $role)
                            <option value="{{ $role }}" @selected(old('role', 'visitor') === $role)>
                                {{ $roleLabels[$role] ?? ucfirst($role) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-slate-300 text-xs mb-1">Permissions directes (optionnel)</label>
                    <select
                        name="granted_permissions[]"
                        multiple
                        size="6"
                        class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-xs text-slate-100"
                    >
                        @foreach($permissionOptions as $group => $permissions)
                            <optgroup label="{{ $group }}">
                                @foreach($permissions as $permission)
                                    <option value="{{ $permission->value }}" @selected(in_array($permission->value, old('granted_permissions', []), true))>
                                        {{ $permission->label() }}
                                    </option>
                                @endforeach
                            </optgroup>
                        @endforeach
                    </select>
                    <p class="text-slate-500 text-xs mt-1">Ctrl/Cmd + clic pour multi-selection.</p>
                </div>

                <div class="md:col-span-2 flex items-center gap-3">
                    <button type="submit" class="bg-cyan-500 hover:bg-cyan-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Creer l'utilisateur
                    </button>
                    <button type="button" id="cancel-create-user-modal" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                        Annuler
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden">
    <div class="px-5 py-4 border-b border-slate-800">
        <h2 class="text-white font-semibold">Liste des utilisateurs</h2>
        <p class="text-slate-400 text-sm mt-1">Modifiez le role et ajoutez des permissions specifiques par utilisateur.</p>
        <form method="GET" action="{{ route('admin.users.index') }}" class="mt-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="md:col-span-2">
                <input
                    type="text"
                    name="q"
                    value="{{ $search }}"
                    placeholder="Rechercher par nom ou email..."
                    class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100"
                >
            </div>
            <div>
                <select name="role" class="w-full bg-slate-800 border border-slate-700 rounded-lg px-3 py-2 text-sm text-slate-100">
                    <option value="">Tous les roles</option>
                    @foreach($roles as $role)
                        <option value="{{ $role }}" @selected($roleFilter === $role)>{{ $roleLabels[$role] ?? ucfirst($role) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-2">
                <button type="submit" class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                    Filtrer
                </button>
                <a href="{{ route('admin.users.index') }}" class="bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold px-4 py-2 rounded-lg">
                    Reinitialiser
                </a>
            </div>
        </form>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-slate-800">
                    <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">Utilisateur</th>
                    <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">Role actuel</th>
                    <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">Changer role</th>
                    <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">Permissions utilisateur</th>
                    <th class="text-left px-5 py-3 text-slate-500 font-medium uppercase tracking-wide text-xs">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-800/80">
                @forelse($users as $u)
                    <tr class="hover:bg-slate-800/30 transition">
                        <td class="px-5 py-3">
                            <p class="text-slate-200 font-medium">{{ $u->full_name }}</p>
                            <p class="text-slate-500 text-xs mt-0.5">{{ $u->email }}</p>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs {{ $roleBadgeClasses[$u->role] ?? 'bg-slate-700/20 text-slate-300 border-slate-600/40' }}">
                                {{ $roleLabels[$u->role] ?? ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="px-5 py-3">
                            <form method="POST" action="{{ route('admin.users.role.update', $u) }}" class="flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <select
                                    name="role"
                                    class="bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-500"
                                    @if(auth()->id() === $u->id) disabled @endif
                                >
                                    @foreach($roles as $role)
                                        <option value="{{ $role }}" @selected($u->role === $role)>
                                            {{ $roleLabels[$role] ?? ucfirst($role) }}
                                        </option>
                                    @endforeach
                                </select>
                                <button
                                    type="submit"
                                    class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition disabled:opacity-50 disabled:cursor-not-allowed"
                                    @if(auth()->id() === $u->id) disabled @endif
                                >
                                    Mettre a jour role
                                </button>
                            </form>
                            @if(auth()->id() === $u->id)
                                <p class="text-slate-500 text-xs mt-1">Votre role n'est pas modifiable.</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 min-w-[340px]">
                            <form method="POST" action="{{ route('admin.users.permissions.update', $u) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <select
                                    name="granted_permissions[]"
                                    multiple
                                    size="7"
                                    class="w-full bg-slate-800 border border-slate-700 text-slate-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-cyan-500"
                                >
                                    @foreach($permissionOptions as $group => $permissions)
                                        <optgroup label="{{ $group }}">
                                            @foreach($permissions as $permission)
                                                <option
                                                    value="{{ $permission->value }}"
                                                    @selected(in_array($permission->value, $u->granted_permissions ?? [], true))
                                                >
                                                    {{ $permission->label() }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                <div class="flex items-center gap-2">
                                    <button
                                        type="submit"
                                        class="bg-cyan-500 hover:bg-cyan-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition"
                                    >
                                        Enregistrer permissions
                                    </button>
                                    <span class="text-slate-500 text-xs">Ctrl/Cmd + clic pour multi-selection.</span>
                                </div>
                                @if(!empty($u->granted_permissions))
                                    <p class="text-slate-500 text-xs">
                                        Permissions directes: {{ count($u->granted_permissions) }}
                                    </p>
                                @endif
                            </form>
                        </td>
                        <td class="px-5 py-3">
                            @if(auth()->id() !== $u->id)
                                <form method="POST"
                                      action="{{ route('admin.users.destroy', $u) }}"
                                      onsubmit="return confirm('Supprimer « {{ addslashes($u->full_name) }} » ? Cette action est irréversible.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="inline-flex items-center gap-1.5 bg-red-700/90 hover:bg-red-600 text-white text-xs font-semibold px-3 py-2 rounded-lg transition">
                                        <i class="fas fa-trash-can text-[11px]"></i> Supprimer
                                    </button>
                                </form>
                            @else
                                <span class="text-slate-600 text-xs">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-slate-500">Aucun utilisateur trouve.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="px-5 py-4 border-t border-slate-800">
        {{ $users->links() }}
    </div>
</div>

<script>
    (function () {
        const modal = document.getElementById('create-user-modal');
        const openButton = document.getElementById('open-create-user-modal');
        const closeButton = document.getElementById('close-create-user-modal');
        const cancelButton = document.getElementById('cancel-create-user-modal');
        const overlay = document.getElementById('create-user-modal-overlay');

        if (!modal || !openButton || !closeButton || !cancelButton || !overlay) {
            return;
        }

        const openModal = function () {
            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        };

        const closeModal = function () {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        };

        openButton.addEventListener('click', openModal);
        closeButton.addEventListener('click', closeModal);
        cancelButton.addEventListener('click', closeModal);
        overlay.addEventListener('click', closeModal);

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                closeModal();
            }
        });
    })();
</script>
@endsection
