<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_agencies', function (Blueprint $table) {
            $table->id();

            // Lien 1:1 optionnel vers la fiche prestataire (même patron que leisure_venues.provider_id).
            $table->foreignId('provider_id')->nullable()->unique()
                  ->constrained('providers')->nullOnDelete();

            // Localisation
            $table->foreignId('city_id')
                  ->constrained('tourist_cities')
                  ->restrictOnDelete();

            // Identité
            $table->string('name');
            $table->string('slug')->unique();

            // Descriptions
            $table->string('short_description')->nullable();
            $table->longText('description')->nullable();

            // Adresse
            $table->string('adresse')->nullable();
            $table->string('quartier')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            // Contact
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();

            // Médias principaux
            $table->string('thumbnail')->nullable();
            $table->string('cover_image')->nullable();

            // Équipements
            $table->json('amenities')->nullable()->comment('[{icon, label}]');

            // Méta
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            $table->index('city_id');
            $table->index(['is_active', 'is_featured']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('travel_agencies');
    }
};
