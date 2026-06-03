<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tourist_sites', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('city_id')->constrained('tourist_cities')->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('tourist_categories');

            // Identité
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 300)->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail', 500)->nullable();

            // Contact & infos pratiques
            $table->string('entrance_fee', 100)->nullable();
            $table->string('website', 300)->nullable();
            $table->string('phone', 30)->nullable();
            $table->string('email', 150)->nullable();

            // Situation géographique
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('departement', 100)->nullable();
            $table->string('sous_prefecture', 100)->nullable();
            $table->string('localite', 150)->nullable();
            $table->unsignedSmallInteger('altitude_m')->nullable();
            $table->decimal('superficie_ha', 10, 2)->nullable();
            $table->decimal('distance_centre_km', 5, 1)->nullable();
            $table->string('point_repere', 250)->nullable();
            $table->text('acces_description')->nullable();
            $table->text('map_embed_url')->nullable();

            // Horaires & infos pratiques (JSON)
            $table->json('schedules')->nullable();
            $table->json('practical_info')->nullable();

            // Méta
            $table->tinyInteger('is_featured')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE tourist_sites ADD FULLTEXT fulltext_tourist_sites_name (name)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('tourist_sites');
    }
};
