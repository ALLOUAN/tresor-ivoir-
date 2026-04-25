<?php

namespace Database\Seeders\Data;

/**
 * Contenus HTML par défaut pour le centre d'information (FR + EN).
 *
 * @return array{title_fr: string, title_en: string, body_fr: string, body_en: string}
 */
final class InformationPageDefaults
{
    public static function pack(string $slug): array
    {
        return match ($slug) {
            'about' => self::about(),
            'user-guide' => self::userGuide(),
            'faq' => self::faq(),
            'legal-notice' => self::legalNotice(),
            'privacy-policy' => self::privacyPolicy(),
            default => [
                'title_fr' => 'Page',
                'title_en' => 'Page',
                'body_fr' => '<p>Contenu à compléter.</p>',
                'body_en' => '<p>Content to be completed.</p>',
            ],
        };
    }

    /** @return array{title_fr: string, title_en: string, body_fr: string, body_en: string} */
    private static function about(): array
    {
        $fr = <<<'HTML'
<section>
<p class="text-sm opacity-80">Dernière mise à jour indicative — à adapter lors de la mise en ligne.</p>
<h2 id="mission">Notre mission</h2>
<p><strong>Trésors d’Ivoire</strong> est un magazine numérique dédié à la culture, au patrimoine et au tourisme en Côte d’Ivoire. Nous sélectionnons des reportages, des adresses et des événements pour faire découvrir le pays avec exigence et sensibilité.</p>
<h2 id="equipe">L’équipe</h2>
<p>Une rédaction indépendante travaille avec des contributeurs locaux (guides, restaurateurs, artistes) pour garantir des contenus vérifiés et actualisés.</p>
<ul>
<li>Rédaction &amp; modération éditoriale</li>
<li>Partenariats &amp; annuaire prestataires</li>
<li>Support lecteurs &amp; newsletter</li>
</ul>
<h2 id="valeurs">Nos valeurs</h2>
<p>Respect des personnes et des lieux, promotion du savoir-faire ivoirien, transparence sur les contenus sponsorisés lorsqu’ils existent.</p>
</section>
HTML;

        $en = <<<'HTML'
<section>
<p class="text-sm opacity-80">Indicative last update — adjust before going live.</p>
<h2 id="mission">Our mission</h2>
<p><strong>Trésors d’Ivoire</strong> is a digital magazine focused on culture, heritage and tourism in Côte d’Ivoire. We curate stories, places and events to help readers explore the country with care and depth.</p>
<h2 id="equipe">The team</h2>
<p>An independent editorial team works with local contributors (guides, chefs, artists) to keep content accurate and fresh.</p>
<h2 id="valeurs">Our values</h2>
<p>Respect for people and places, promotion of Ivorian know-how, transparency when content is sponsored.</p>
</section>
HTML;

        return [
            'title_fr' => 'À propos',
            'title_en' => 'About us',
            'body_fr' => $fr,
            'body_en' => $en,
        ];
    }

    /** @return array{title_fr: string, title_en: string, body_fr: string, body_en: string} */
    private static function userGuide(): array
    {
        $fr = <<<'HTML'
<section>
<h2 id="connexion">Connexion</h2>
<ol>
<li>Ouvrez la page de connexion depuis le menu du site.</li>
<li>Saisissez votre adresse e-mail et votre mot de passe.</li>
<li>Selon votre rôle (visiteur, éditeur, prestataire, administrateur), vous serez redirigé vers le tableau de bord correspondant.</li>
</ol>
<h2 id="articles">Articles (éditeurs)</h2>
<p>Créez un brouillon, enregistrez-le, puis soumettez-le pour révision lorsque le texte est prêt. Un administrateur peut publier ou demander des modifications.</p>
<h2 id="prestataires">Annuaire (prestataires)</h2>
<p>Complétez votre fiche établissement, ajoutez des photos conformes aux règles du site, et assurez-vous que votre abonnement est actif pour les fonctionnalités premium.</p>
<aside class="opacity-90 border-l-2 border-amber-500/50 pl-4 my-4">
<p><strong>Besoin d’aide ?</strong> Contactez l’administrateur du site via le formulaire prévu à cet effet.</p>
</aside>
</section>
HTML;

        $en = <<<'HTML'
<section>
<h2 id="connexion">Sign-in</h2>
<ol>
<li>Open the sign-in page from the site menu.</li>
<li>Enter your email and password.</li>
<li>Depending on your role, you will be redirected to the appropriate dashboard.</li>
</ol>
<h2 id="articles">Articles (editors)</h2>
<p>Create a draft, save it, then submit it for review when ready. An administrator can publish or request changes.</p>
<h2 id="prestataires">Directory (providers)</h2>
<p>Complete your listing, add compliant photos, and keep your subscription active for premium features.</p>
</section>
HTML;

        return [
            'title_fr' => 'Guide d\'utilisation',
            'title_en' => 'User guide',
            'body_fr' => $fr,
            'body_en' => $en,
        ];
    }

    /** @return array{title_fr: string, title_en: string, body_fr: string, body_en: string} */
    private static function faq(): array
    {
        $fr = <<<'HTML'
<section>
<h2 id="sommaire">Questions fréquentes</h2>
<details>
<summary>Comment créer un compte ?</summary>
<p>Utilisez le formulaire d’inscription. Un e-mail de vérification peut vous être demandé selon la configuration du site.</p>
</details>
<details>
<summary>Comment devenir prestataire référencé ?</summary>
<p>Créez un compte avec le rôle adapté, complétez votre fiche et respectez les conditions de l’annuaire. Un administrateur valide les fiches.</p>
</details>
<details>
<summary>Comment signaler une erreur dans un article ?</summary>
<p>Utilisez le formulaire de contact en précisant le lien de la page et la nature de l’erreur.</p>
</details>
<details>
<summary>Où gérer mes données personnelles ?</summary>
<p>Consultez la <strong>Politique de confidentialité</strong> et contactez-nous pour l’accès, la rectification ou la suppression de vos données lorsque la loi l’autorise.</p>
</details>
</section>
HTML;

        $en = <<<'HTML'
<section>
<h2 id="sommaire">Frequently asked questions</h2>
<details>
<summary>How do I create an account?</summary>
<p>Use the registration form. Email verification may be required depending on site settings.</p>
</details>
<details>
<summary>How can I be listed as a provider?</summary>
<p>Create the appropriate account, complete your profile, and follow directory rules. An administrator validates listings.</p>
</details>
<details>
<summary>How do I report an error in an article?</summary>
<p>Use the contact form with the page URL and a short description of the issue.</p>
</details>
</section>
HTML;

        return [
            'title_fr' => 'FAQ',
            'title_en' => 'FAQ',
            'body_fr' => $fr,
            'body_en' => $en,
        ];
    }

    /** @return array{title_fr: string, title_en: string, body_fr: string, body_en: string} */
    private static function legalNotice(): array
    {
        $fr = <<<'HTML'
<section>
<p class="text-sm opacity-80"><strong>Éditeur du site</strong> — [Raison sociale], [forme juridique], au capital de [montant] FCFA, dont le siège social est situé [adresse].</p>
<p>RCS / CC : [numéro] — TVA intracommunautaire : [le cas échéant]</p>
<p><strong>Directeur de la publication</strong> : [nom, prénom]</p>
<h2 id="hebergeur">Hébergeur</h2>
<p>[Nom de l’hébergeur], [adresse], [téléphone], [site web].</p>
<h2 id="propriete-intellectuelle">Propriété intellectuelle</h2>
<p>L’ensemble des contenus (textes, images, logos, vidéos) est protégé. Toute reproduction non autorisée est interdite sauf mention expresse.</p>
<h2 id="responsabilite">Responsabilité</h2>
<p>Le site peut contenir des liens externes ; l’éditeur n’est pas responsable du contenu de ces sites tiers.</p>
</section>
HTML;

        $en = <<<'HTML'
<section>
<p class="text-sm opacity-80"><strong>Publisher</strong> — [Legal name], [legal form], registered office [address].</p>
<h2 id="hebergeur">Hosting</h2>
<p>[Hosting company], [address], [phone], [website].</p>
<h2 id="propriete-intellectuelle">Intellectual property</h2>
<p>All content is protected. Unauthorized reproduction is prohibited unless expressly stated.</p>
<h2 id="responsabilite">Liability</h2>
<p>The site may contain external links; the publisher is not responsible for third-party content.</p>
</section>
HTML;

        return [
            'title_fr' => 'Mentions légales',
            'title_en' => 'Legal notice',
            'body_fr' => $fr,
            'body_en' => $en,
        ];
    }

    /** @return array{title_fr: string, title_en: string, body_fr: string, body_en: string} */
    private static function privacyPolicy(): array
    {
        $fr = <<<'HTML'
<section>
<h2 id="introduction">Introduction</h2>
<p>Nous respectons votre vie privée. Cette politique décrit les données que nous collectons, les finalités, les durées de conservation et vos droits.</p>
<h2 id="donnees">Données collectées</h2>
<ul>
<li><strong>Compte</strong> : identité, e-mail, rôle.</li>
<li><strong>Newsletter</strong> : e-mail, date d’inscription, preuve de consentement.</li>
<li><strong>Navigation</strong> : journaux techniques, cookies selon votre choix.</li>
</ul>
<h2 id="bases-legales">Bases légales</h2>
<p>Exécution du contrat (compte, services), intérêt légitime (sécurité, amélioration du site), consentement (newsletter, cookies non essentiels).</p>
<h2 id="droits">Vos droits</h2>
<p>Accès, rectification, effacement, limitation, opposition, portabilité lorsque applicable — contact : [email DPO ou contact].</p>
<h2 id="conservation">Durées</h2>
<p>Les données sont conservées pendant la durée nécessaire aux finalités, puis archivées ou supprimées selon les obligations légales.</p>
</section>
HTML;

        $en = <<<'HTML'
<section>
<h2 id="introduction">Introduction</h2>
<p>We respect your privacy. This policy explains what we collect, why, how long we keep it, and your rights.</p>
<h2 id="donnees">Data we collect</h2>
<ul>
<li><strong>Account</strong>: identity, email, role.</li>
<li><strong>Newsletter</strong>: email, subscription date, consent evidence.</li>
<li><strong>Technical logs</strong> and cookies depending on your choices.</li>
</ul>
<h2 id="bases-legales">Legal bases</h2>
<p>Contract performance, legitimate interests (security, improvements), consent (non-essential cookies, marketing where applicable).</p>
<h2 id="droits">Your rights</h2>
<p>Access, rectification, erasure, restriction, objection, portability where applicable — contact: [DPO or contact email].</p>
</section>
HTML;

        return [
            'title_fr' => 'Politique de confidentialité',
            'title_en' => 'Privacy policy',
            'body_fr' => $fr,
            'body_en' => $en,
        ];
    }
}
