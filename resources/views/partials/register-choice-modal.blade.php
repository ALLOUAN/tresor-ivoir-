{{--
    Modal de choix du type de compte, ouvert par tout élément portant
    l'attribut [data-open-register-modal] (ex: le bouton "S'inscrire" de la
    page de connexion). Remplace le renvoi direct vers /abonnements : on
    laisse d'abord la personne choisir client ou prestataire, chaque choix
    menant vers son propre parcours d'inscription (déjà géré côté serveur
    par AuthController::showRegister — /register affiche le formulaire
    prestataire, /register?role=visitor le formulaire client ; le
    prestataire est ensuite redirigé vers /abonnements après son inscription
    s'il n'a pas déjà choisi un forfait).
--}}
<div id="register-choice-modal"
     class="fixed inset-0 z-[100] hidden items-center justify-center p-4"
     role="dialog" aria-modal="true" aria-labelledby="register-choice-title">

    {{-- Fond assombri + flouté --}}
    <div id="register-choice-backdrop"
         class="absolute inset-0 bg-black/60 backdrop-blur-sm opacity-0 transition-opacity duration-300"></div>

    {{-- Panneau --}}
    <div id="register-choice-panel"
         class="relative w-full max-w-3xl bg-green-900 border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8 opacity-0 scale-95 transition-all duration-300 max-h-[90vh] overflow-y-auto">

        {{-- Fermer --}}
        <button type="button" data-close-register-modal
                aria-label="Fermer"
                class="absolute top-4 right-4 w-9 h-9 rounded-full flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition">
            <i class="fas fa-xmark text-lg"></i>
        </button>

        {{-- En-tête --}}
        <div class="text-center mb-8 pr-8">
            <h2 id="register-choice-title" class="text-2xl sm:text-3xl font-bold text-white">
                Rejoindre {{ $siteBrand['site_name'] ?? config('app.name') }}
            </h2>
            <p class="text-slate-400 text-sm mt-2">Choisissez le type de compte qui correspond à votre besoin</p>
        </div>

        {{-- Les deux options --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">

            {{-- Compte Client --}}
            <div class="group flex flex-col rounded-2xl border border-slate-700 bg-slate-800/40 p-6 transition-all duration-300 hover:border-orange-400/50 hover:-translate-y-1 hover:shadow-xl hover:shadow-orange-500/10">
                <div class="w-14 h-14 rounded-2xl bg-orange-500/15 flex items-center justify-center mb-4 group-hover:bg-orange-500/25 transition">
                    <i class="fas fa-user text-2xl text-orange-400"></i>
                </div>
                <h3 class="text-white text-lg font-semibold mb-2">Compte Client</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">
                    Créez un compte client pour profiter pleinement de la plateforme.
                </p>
                <ul class="space-y-2 text-sm text-slate-300 mb-6 flex-1">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-orange-400 text-xs mt-1"></i>
                        <span>Effectuer des achats sur la plateforme</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-orange-400 text-xs mt-1"></i>
                        <span>Effectuer des réservations</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-orange-400 text-xs mt-1"></i>
                        <span>Gérer ses commandes et réservations</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-orange-400 text-xs mt-1"></i>
                        <span>Consulter son historique</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-orange-400 text-xs mt-1"></i>
                        <span>Gérer ses informations personnelles</span>
                    </li>
                </ul>
                <a href="{{ route('register', ['role' => 'visitor']) }}"
                   class="w-full inline-flex items-center justify-center gap-2 bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 rounded-lg transition duration-150 text-sm">
                    <i class="fas fa-user-plus"></i>
                    Créer un compte client
                </a>
            </div>

            {{-- Compte Prestataire --}}
            <div class="group flex flex-col rounded-2xl border border-slate-700 bg-slate-800/40 p-6 transition-all duration-300 hover:border-emerald-400/50 hover:-translate-y-1 hover:shadow-xl hover:shadow-emerald-500/10">
                <div class="w-14 h-14 rounded-2xl bg-emerald-500/15 flex items-center justify-center mb-4 group-hover:bg-emerald-500/25 transition">
                    <i class="fas fa-store text-2xl text-emerald-400"></i>
                </div>
                <h3 class="text-white text-lg font-semibold mb-2">Compte Prestataire</h3>
                <p class="text-slate-400 text-sm leading-relaxed mb-4">
                    Créez un compte prestataire pour proposer vos services et développer votre activité sur la plateforme.
                </p>
                <ul class="space-y-2 text-sm text-slate-300 mb-6 flex-1">
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-400 text-xs mt-1"></i>
                        <span>Souscrire à un abonnement prestataire</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-400 text-xs mt-1"></i>
                        <span>Présenter son activité et ses services</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-400 text-xs mt-1"></i>
                        <span>Publier et gérer ses offres selon son secteur d'activité</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-400 text-xs mt-1"></i>
                        <span>Accéder à son espace prestataire</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <i class="fas fa-check text-emerald-400 text-xs mt-1"></i>
                        <span>Gérer les informations de son activité</span>
                    </li>
                </ul>
                <a href="{{ route('plans.public') }}"
                   class="w-full inline-flex items-center justify-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-2.5 rounded-lg transition duration-150 text-sm">
                    <i class="fas fa-briefcase"></i>
                    Créer un compte prestataire
                </a>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var modal = document.getElementById('register-choice-modal');
    if (!modal) return;
    var backdrop = document.getElementById('register-choice-backdrop');
    var panel = document.getElementById('register-choice-panel');
    var lastFocused = null;

    function openModal() {
        lastFocused = document.activeElement;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        // Force reflow avant de retirer les classes de transition initiales,
        // pour que le navigateur anime bien depuis l'état "invisible".
        void modal.offsetHeight;
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'scale-95');
        document.body.style.overflow = 'hidden';
        var closeBtn = modal.querySelector('[data-close-register-modal]');
        if (closeBtn) closeBtn.focus();
    }

    function closeModal() {
        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'scale-95');
        document.body.style.overflow = '';
        setTimeout(function () {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
        if (lastFocused && typeof lastFocused.focus === 'function') lastFocused.focus();
    }

    document.querySelectorAll('[data-open-register-modal]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            openModal();
        });
    });

    modal.querySelectorAll('[data-close-register-modal]').forEach(function (btn) {
        btn.addEventListener('click', closeModal);
    });

    // Fermeture au clic en dehors du panneau (sur le fond assombri).
    modal.addEventListener('click', function (e) {
        if (e.target === modal || e.target === backdrop) closeModal();
    });

    // Fermeture avec la touche Échap.
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && !modal.classList.contains('hidden')) closeModal();
    });
})();
</script>
