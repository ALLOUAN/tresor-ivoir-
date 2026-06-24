<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodations', function (Blueprint $table) {
            $table->id();

            // Localisation
            $table->foreignId('city_id')
                  ->constrained('tourist_cities')
                  ->restrictOnDelete();

            // Identité
            $table->string('name');
            $table->string('slug')->unique();
            $table->enum('type', [
                'hotel', 'resort', 'guesthouse',
                'hostel', 'auberge', 'villa', 'eco_lodge',
            ])->default('hotel');
            $table->tinyInteger('stars')->unsigned()->default(0)->comment('0 = non classé');

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

            // Horaires accueil
            $table->time('check_in_time')->nullable()->comment('ex. 14:00');
            $table->time('check_out_time')->nullable()->comment('ex. 12:00');

            // JSON fusionnés
            $table->json('amenities')->nullable()->comment('[{icon, label}] — piscine, wifi, spa…');
            $table->json('category_ids')->nullable()->comment('[1,3,5] — tourist_categories IDs');
            $table->json('room_types')->nullable()->comment('[{name, max_adults, max_children, area_m2, price_xof, price_eur, amenities[], thumbnail}]');
            $table->json('booking_links')->nullable()->comment('[{provider_name, logo_url, affiliate_url, is_official, badge_text, sort_order}]');

            // Méta
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->softDeletes();
            $table->timestamps();

            // Index
            $table->index('city_id');
            $table->index(['is_active', 'is_featured']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodations');
    }
};
