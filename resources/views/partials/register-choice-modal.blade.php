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

    {{-- Fond assombri --}}
    <div id="register-choice-backdrop"
         class="absolute inset-0 bg-black/60 opacity-0 transition-opacity duration-300"></div>

    <style>
        /* Choix du profil : en-tête vert à motifs, deux grandes cartes cliquables (client / prestataire),
           étape 1 sur 2, lien de connexion. Aplats ; ombre au survol seulement. */
        .rc { --sunset: 235,110,0; --palm: 0,135,79; position: relative; width: 100%; max-width: 820px; max-height: 92vh; overflow-y: auto; border-radius: 28px; background: #fff; color: #0E3B2A; font-family: 'Montserrat', sans-serif; }
        .rc-head { position: relative; overflow: hidden; padding: 26px 30px 30px; background: #00874F; color: #fff; }
        .rc-head .ivm-g { opacity: .1 !important; }
        .rc-head .ivm-g > * { stroke: #F7C396 !important; }
        .rc-head .ivm-band { height: 6px; }
        .rc-head > :not(.ivm):not(.ivm-band) { position: relative; z-index: 1; }
        .rc-step { display: inline-flex; align-items: center; gap: 8px; margin: 0; font-size: 11.5px; font-weight: 800; letter-spacing: .1em; text-transform: uppercase; color: #F7C396; }
        .rc-step i { display: inline-flex; gap: 4px; }
        .rc-step i b { width: 22px; height: 4px; border-radius: 4px; background: rgba(255,255,255,.25); }
        .rc-step i b:first-child { background: #F7C396; }
        .rc-title { margin: 10px 48px 0 0; font-family: 'Montserrat', serif; font-size: clamp(25px, 3vw, 32px); font-weight: 700; line-height: 1.12; color: #fff; }
        .rc-sub { margin: 6px 0 0; font-size: 15px; color: #D8E9E0; }
        .rc-close { position: absolute; right: 18px; top: 18px; z-index: 2; width: 40px; height: 40px; border: 0; border-radius: 999px; background: rgba(255,255,255,.12); color: #fff; font-size: 20px; line-height: 1; cursor: pointer; }
        .rc-close:hover { background: rgba(255,255,255,.22); }
        #register-choice-panel .rc-close { position: absolute; left: auto; right: 18px; top: 18px; margin: 0; }

        .rc-body { padding: 22px 24px 20px; }
        .rc-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 16px; }
        .rc-card { --c: var(--sunset); position: relative; display: flex; flex-direction: column; padding: 20px; border-radius: 22px; background: #fff; border: 1.5px solid #D8E9E0; transition: border-color .15s ease, box-shadow .2s ease, transform .2s ease; }
        .rc-card.is-pro { --c: var(--palm); }
        .rc-card:hover, .rc-card:focus-within { border-color: rgb(var(--c)); box-shadow: 0 22px 40px -30px rgba(14,59,42,.55); transform: translateY(-2px); }
        .rc-card__top { display: flex; align-items: center; gap: 12px; }
        .rc-ico { flex: none; width: 52px; height: 52px; border-radius: 16px; display: inline-flex; align-items: center; justify-content: center; background: rgba(var(--c), .12); color: rgb(var(--c)); }
        .rc-ico .ico { width: 26px; height: 26px; }
        .rc-who { margin: 0; font-size: 12px; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: rgb(var(--c)); }
        .rc-card h3 { margin: 2px 0 0; font-family: 'Montserrat', serif; font-size: 21px; font-weight: 700; line-height: 1.2; color: #0E3B2A; }
        .rc-desc { margin: 12px 0 0; font-size: 14px; line-height: 1.55; color: #24503D; }
        .rc-list { flex: 1; display: flex; flex-direction: column; gap: 8px; margin: 14px 0 0; padding: 0; list-style: none; }
        .rc-list li { display: flex; align-items: flex-start; gap: 9px; font-size: 14px; line-height: 1.4; color: #0E3B2A; }
        .rc-list li > .ico { flex: none; width: 18px; height: 18px; margin: 1px 0 0; padding: 0; border: 0; border-radius: 0; background: none; color: rgb(var(--c)); }
        .rc-note { display: inline-flex; align-items: center; gap: 7px; align-self: flex-start; margin: 16px 0 0; padding: 6px 11px; border-radius: 999px; background: rgba(var(--c), .1); font-size: 12.5px; font-weight: 700; color: #0E3B2A; }
        .rc-note .ico { width: 15px; height: 15px; color: rgb(var(--c)); }
        .rc-cta { display: flex; align-items: center; justify-content: center; gap: 9px; height: 50px; margin-top: 16px; border-radius: 999px; background: #EB6E00; color: #fff; font-size: 15px; font-weight: 800; text-decoration: none; transition: background .15s ease; }
        .rc-cta:hover { background: #B85600; }
        .rc-cta .ico { width: 18px; height: 18px; }
        /* Toute la carte est cliquable (le lien s'étend sur la carte) */
        .rc-cta::after { content: ''; position: absolute; inset: 0; border-radius: 22px; }
        .rc-cta:focus-visible { outline: 3px solid #EB6E00; outline-offset: 3px; }
        .rc-close:focus-visible, .rc-foot a:focus-visible { outline: 3px solid #EB6E00; outline-offset: 2px; }

        .rc-foot { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 8px 16px; margin-top: 18px; padding-top: 16px; border-top: 1px solid #F3F8F5; font-size: 13.5px; color: #557466; }
        .rc-foot a { font-weight: 700; color: #B85600; text-decoration: none; }
        .rc-foot a:hover { text-decoration: underline; text-underline-offset: 3px; }
        .rc-foot span { display: inline-flex; align-items: center; gap: 6px; }
        .rc-foot .ico { width: 15px; height: 15px; color: #00874F; }

        @media (max-width: 640px) {
            #register-choice-modal { padding: 0 !important; align-items: flex-end !important; }
            .rc { max-width: none; max-height: 94vh; border-radius: 24px 24px 0 0; }
            .rc-head { padding: 18px 18px 22px; }
            .rc-title { font-size: 23px; }
            .rc-sub { font-size: 14px; }
            .rc-body { padding: 14px 14px 16px; }
            .rc-grid { grid-template-columns: minmax(0, 1fr); gap: 10px; }
            .rc-card { padding: 14px; border-radius: 18px; }
            .rc-ico { width: 44px; height: 44px; border-radius: 14px; }
            .rc-card h3 { font-size: 18px; }
            .rc-desc { display: none; }
            .rc-list { gap: 6px; margin-top: 10px; }
            .rc-list li:nth-child(n+4) { display: none; }
            .rc-list li { font-size: 13.5px; }
            .rc-note { margin-top: 10px; }
            .rc-cta { height: 46px; margin-top: 12px; }
            .rc-cta::after { border-radius: 18px; }
        }
        @media (prefers-reduced-motion: reduce) { .rc-card { transition: none; } }
    </style>

    {{-- Panneau (les classes opacity-0 / scale-95 servent à l'animation d'ouverture) --}}
    <div id="register-choice-panel" class="rc opacity-0 scale-95 transition-all duration-300">

        <div class="rc-head">
            @include('partials.ivorian-motifs', ['culture' => 'national'])
            <button type="button" data-close-register-modal aria-label="{{ __('Fermer') }}" class="rc-close">×</button>
            <p class="rc-step"><i aria-hidden="true"><b></b><b></b></i>{{ __('Étape 1 sur 2 · Votre profil') }}</p>
            <h2 id="register-choice-title" class="rc-title">{{ __('Rejoindre') }} {{ $siteBrand['site_name'] ?? config('app.name') }}</h2>
            <p class="rc-sub">{{ __('Choisissez le type de compte qui correspond à votre besoin') }}</p>
        </div>

        <div class="rc-body">
            <div class="rc-grid">

                {{-- Compte Client --}}
                <div class="rc-card">
                    <div class="rc-card__top">
                        <span class="rc-ico"><x-icon name="suitcase" /></span>
                        <div>
                            <p class="rc-who">{{ __('Je voyage, je découvre') }}</p>
                            <h3>{{ __('Compte Client') }}</h3>
                        </div>
                    </div>
                    <p class="rc-desc">{{ __('Créez un compte client pour profiter pleinement de la plateforme.') }}</p>
                    <ul class="rc-list">
                        <li><x-icon name="check-circle" />{{ __('Effectuer des réservations') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Effectuer des achats sur la plateforme') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Gérer ses commandes et réservations') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Consulter son historique') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Gérer ses informations personnelles') }}</li>
                    </ul>
                    <span class="rc-note"><x-icon name="smile-circle" />{{ __('Inscription gratuite') }}</span>
                    <a href="{{ route('register', ['role' => 'visitor']) }}" class="rc-cta">
                        <x-icon name="user-rounded" />{{ __('Créer un compte client') }}
                    </a>
                </div>

                {{-- Compte Prestataire --}}
                <div class="rc-card is-pro">
                    <div class="rc-card__top">
                        <span class="rc-ico"><x-icon name="shop" /></span>
                        <div>
                            <p class="rc-who">{{ __('Je propose mes services') }}</p>
                            <h3>{{ __('Compte Prestataire') }}</h3>
                        </div>
                    </div>
                    <p class="rc-desc">{{ __('Créez un compte prestataire pour proposer vos services et développer votre activité sur la plateforme.') }}</p>
                    <ul class="rc-list">
                        <li><x-icon name="check-circle" />{{ __('Présenter son activité et ses services') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Publier et gérer ses offres selon son secteur d\'activité') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Accéder à son espace prestataire') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Souscrire à un abonnement prestataire') }}</li>
                        <li><x-icon name="check-circle" />{{ __('Gérer les informations de son activité') }}</li>
                    </ul>
                    <span class="rc-note"><x-icon name="tag-price" />{{ __('Abonnement à choisir à l\'étape suivante') }}</span>
                    <a href="{{ route('plans.public') }}" class="rc-cta">
                        <x-icon name="case" />{{ __('Créer un compte prestataire') }}
                    </a>
                </div>
            </div>

            <div class="rc-foot">
                <span><x-icon name="shield-check" />{{ __('Vos données restent confidentielles.') }}</span>
                <p style="margin:0">{{ __('Déjà un compte ?') }} <button type="button" data-close-register-modal style="border:0;background:none;padding:0;font:inherit;font-weight:700;color:#B85600;cursor:pointer">{{ __('Se connecter') }}</button></p>
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
