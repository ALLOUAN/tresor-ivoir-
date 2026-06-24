<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cultural_elements', function (Blueprint $table) {
            $table->id();

            // Relations
            $table->foreignId('domain_id')->constrained('cultural_domains');

            // Identité
            $table->string('name', 150);
            $table->string('slug', 180)->unique();
            $table->string('short_description', 300)->nullable();
            $table->longText('description')->nullable();
            $table->text('origine_historique')->nullable();
            $table->string('thumbnail', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->string('website', 300)->nullable();

            // Patrimoine
            $table->enum('niveau_risque', ['stable', 'vulnerable', 'en_danger', 'disparu'])->default('stable');
            $table->string('unesco_status', 100)->nullable();

            // Relations fusionnées (JSON)
            $table->json('people_roles')->nullable();  // [{people_id, role}]
            $table->json('city_ids')->nullable();       // [1, 3, 5]

            // Infos pratiques (JSON)
            $table->json('meilleure_periode')->nullable();
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
            DB::statement('ALTER TABLE cultural_elements ADD FULLTEXT fulltext_cultural_elements_name (name)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_elements');
    }
};
