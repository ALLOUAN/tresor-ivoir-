<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Offres groupées : un forfait composé par l'administration (hébergement + sites + table + transport…),
 * vendu à un prix par personne. Le prestataire « porteur » reçoit les demandes et l'argent.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('city_id')->nullable()->constrained('tourist_cities')->nullOnDelete();
            $table->string('title', 180);
            $table->string('title_en', 180)->nullable();
            $table->string('slug', 200)->unique();
            $table->string('summary', 300)->nullable();
            $table->string('summary_en', 300)->nullable();
            $table->text('description')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_image')->nullable();
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->unsignedInteger('price_xof');
            $table->unsignedSmallInteger('min_people')->default(1);
            $table->unsignedSmallInteger('max_people')->nullable();
            $table->json('includes')->nullable(); // [{type, id}] : éléments du catalogue inclus
            $table->json('highlights')->nullable(); // lignes libres « Ce qui est compris »
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::table('booking_requests', function (Blueprint $table) {
            $table->foreignId('bundle_id')->nullable()->after('bookable_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('booking_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bundle_id');
        });
        Schema::dropIfExists('bundles');
    }
};
