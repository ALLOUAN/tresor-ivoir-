<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Bannières publicitaires de la page d'accueil (deux emplacements entre les sections),
 * gérées depuis l'administration. Deux bannières par défaut mettent en avant des
 * rubriques du site ; l'équipe les remplace par ses propres annonces.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_promo_banners', function (Blueprint $table) {
            $table->id();
            $table->string('placement', 30)->index();          // after_annuaire | after_cultures
            $table->string('kicker', 80)->nullable();            // petit surtitre (« Offres du moment »)
            $table->string('title_strong', 90);                  // début du titre, en gras
            $table->string('title_rest', 120)->nullable();       // suite du titre, en maigre
            $table->string('accent', 120)->nullable();           // ligne d'accroche orange
            $table->string('cta_label', 40)->default('Découvrir');
            $table->string('link_url', 500);
            $table->boolean('open_new_tab')->default(false);
            $table->string('image_url')->nullable();             // visuel détouré (PNG transparent conseillé)
            $table->string('icon', 60)->nullable();              // icône de repli si aucun visuel
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });

        DB::table('home_promo_banners')->insert([
            [
                'placement' => 'after_annuaire', 'kicker' => 'Espace professionnel',
                'title_strong' => 'Référencez', 'title_rest' => 'votre établissement',
                'accent' => 'Visibilité dès 9 900 FCFA / mois', 'cta_label' => 'Voir les offres',
                'link_url' => '/abonnements', 'open_new_tab' => false, 'image_url' => null, 'icon' => 'fa-store',
                'is_active' => true, 'display_order' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'placement' => 'after_cultures', 'kicker' => 'Nouveau dans la Médiathèque',
                'title_strong' => "Cinéma d'Ivoire", 'title_rest' => 'documentaires & fictions',
                'accent' => 'Bandes-annonces, projections, où regarder', 'cta_label' => 'Explorer',
                'link_url' => '/cinema-d-ivoire', 'open_new_tab' => false, 'image_url' => null, 'icon' => 'fa-clapperboard',
                'is_active' => true, 'display_order' => 1, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('home_promo_banners');
    }
};
