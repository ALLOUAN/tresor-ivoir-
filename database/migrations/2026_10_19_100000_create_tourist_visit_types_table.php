<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Types de visite personnalisés d'un site touristique (« Visite nocturne », « Visite VIP »…),
 * créés par le prestataire ou l'administrateur. Les modes fixes « individuelle » et « avec guide »
 * déjà configurés sont convertis en types ; la visite groupée reste gérée par sessions.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourist_visit_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tourist_experience_id')->constrained('tourist_experiences')->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('description', 500)->nullable();
            $table->string('icon', 60)->nullable();
            // Prix par participant (null = gratuit).
            $table->unsignedInteger('price_xof')->nullable();
            // Visite accompagnée d'un guide, avec un éventuel supplément forfaitaire par réservation.
            $table->boolean('includes_guide')->default(false);
            $table->unsignedInteger('guide_supplement_xof')->nullable();
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('max_participants')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['tourist_experience_id', 'is_active']);
        });

        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->foreignId('tourist_visit_type_id')->nullable()->after('tourist_visit_session_id')
                ->constrained('tourist_visit_types')->nullOnDelete();
            // Nom figé au moment de la réservation (le type peut être renommé ou supprimé ensuite).
            $table->string('visit_type_name', 120)->nullable()->after('visit_type');
        });

        // Conversion des modes fixes déjà configurés.
        $now = now();
        DB::table('tourist_experiences')
            ->where(fn ($q) => $q->where('visit_individual_enabled', true)->orWhere('visit_guided_enabled', true))
            ->orderBy('id')
            ->each(function ($e) use ($now) {
                $rows = [];
                if ($e->visit_individual_enabled) {
                    $rows[] = ['name' => 'Visite individuelle', 'description' => 'À votre rythme, à la date de votre choix', 'icon' => 'fa-person-walking',
                        'price_xof' => $e->visit_individual_price_xof, 'includes_guide' => false, 'guide_supplement_xof' => null, 'sort_order' => 1];
                }
                if ($e->visit_guided_enabled) {
                    $rows[] = ['name' => 'Visite avec guide', 'description' => "Accompagné d'un guide du site", 'icon' => 'fa-user-tie',
                        'price_xof' => $e->visit_individual_price_xof, 'includes_guide' => true, 'guide_supplement_xof' => $e->visit_guide_supplement_xof, 'sort_order' => 2];
                }
                foreach ($rows as $row) {
                    DB::table('tourist_visit_types')->insert($row + [
                        'tourist_experience_id' => $e->id, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            });
    }

    public function down(): void
    {
        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tourist_visit_type_id');
            $table->dropColumn('visit_type_name');
        });
        Schema::dropIfExists('tourist_visit_types');
    }
};
