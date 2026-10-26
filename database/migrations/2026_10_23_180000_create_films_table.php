<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rubrique « Cinéma d'Ivoire » (Médiathèque) : fiches de films documentaires et de fiction,
 * proposées par l'équipe ou soumises par les professionnels (contrôle éditorial avant publication).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('films', function (Blueprint $table) {
            $table->id();
            $table->string('title', 180);
            $table->string('slug', 200)->unique();
            $table->string('kind', 20)->index();                 // documentaire | fiction
            $table->string('genre', 60)->nullable();             // drame, histoire, société, nature…
            $table->string('tagline', 220)->nullable();          // accroche éditoriale
            $table->text('synopsis')->nullable();
            $table->string('poster_path')->nullable();           // affiche (portrait)
            $table->string('backdrop_path')->nullable();         // image de fond (paysage)
            $table->string('director', 160)->nullable()->index();
            $table->text('director_bio')->nullable();
            $table->text('cast')->nullable();                    // distribution / intervenants
            $table->string('production', 220)->nullable();       // société(s) de production
            $table->string('country', 80)->nullable()->index();
            $table->unsignedSmallInteger('year')->nullable()->index();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->string('language', 80)->nullable();
            $table->string('trailer_url', 500)->nullable();      // YouTube / Vimeo (droits vérifiés)
            $table->string('watch_url', 500)->nullable();        // plateforme officielle, billetterie, distributeur
            $table->string('watch_label', 80)->nullable();
            $table->string('distributor_contact', 220)->nullable();
            $table->text('awards')->nullable();
            $table->json('screenings')->nullable();              // [{date, place, url}]
            $table->text('credits')->nullable();                 // crédits des visuels
            $table->boolean('rights_confirmed')->default(false); // autorisations de diffusion obtenues
            $table->boolean('is_sponsored')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->string('status', 20)->default('draft')->index(); // draft | pending | published | rejected | withdrawn
            $table->string('review_note', 500)->nullable();
            $table->string('submitter_name', 160)->nullable();
            $table->string('submitter_email', 190)->nullable();
            $table->string('submitter_phone', 40)->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('clicks_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('films');
    }
};
