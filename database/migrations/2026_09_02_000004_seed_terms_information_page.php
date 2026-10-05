<?php

use App\Models\InformationPage;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $bodyFr = <<<'HTML'
<section>
<p class="text-sm opacity-80">Dernière mise à jour indicative — à adapter lors de la mise en ligne.</p>
<h2>1. Objet</h2>
<p>Les présentes conditions générales d'utilisation régissent l'accès et l'utilisation de la plateforme, notamment la création d'un compte client, la recherche d'hébergements et de prestataires, et la réservation d'hôtels et de résidences.</p>
<h2>2. Création de compte</h2>
<p>La création d'un compte est obligatoire avant toute réservation d'hôtel ou de résidence. L'utilisateur s'engage à fournir des informations exactes et à jour, et à préserver la confidentialité de ses identifiants.</p>
<h2>3. Réservations et paiement</h2>
<p>Toute réservation d'hébergement fait l'objet du paiement en ligne d'un acompte. Le solde est réglé directement auprès de l'établissement. Les conditions d'annulation propres à chaque établissement sont indiquées sur sa fiche.</p>
<h2>4. Responsabilités</h2>
<p>La plateforme met en relation les utilisateurs avec des prestataires indépendants et ne saurait être tenue responsable de la prestation fournie par ces derniers.</p>
<h2>5. Données personnelles</h2>
<p>Le traitement des données personnelles est détaillé dans la politique de confidentialité du site.</p>
</section>
HTML;

        $bodyEn = <<<'HTML'
<section>
<p class="text-sm opacity-80">Indicative last update — to be adapted before going live.</p>
<h2>1. Purpose</h2>
<p>These terms of use govern access to and use of the platform, including account creation, searching for accommodations and providers, and booking hotels and residences.</p>
<h2>2. Account creation</h2>
<p>Creating an account is required before booking any hotel or residence. Users agree to provide accurate information and to keep their credentials confidential.</p>
<h2>3. Bookings and payment</h2>
<p>Every accommodation booking requires an online deposit payment. The remaining balance is paid directly to the establishment. Cancellation terms specific to each establishment are shown on its page.</p>
<h2>4. Liability</h2>
<p>The platform connects users with independent providers and cannot be held responsible for the service delivered by them.</p>
<h2>5. Personal data</h2>
<p>The processing of personal data is detailed in the site's privacy policy.</p>
</section>
HTML;

        InformationPage::query()->firstOrCreate(
            ['slug' => 'conditions-generales-utilisation'],
            [
                'title_fr' => 'Conditions générales d\'utilisation',
                'title_en' => 'Terms of Use',
                'body_fr' => $bodyFr,
                'body_en' => $bodyEn,
                'sort_order' => 99,
            ]
        );
    }

    public function down(): void
    {
        InformationPage::query()->where('slug', 'conditions-generales-utilisation')->delete();
    }
};
