<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Prestataire multi-secteurs : un même compte (une même fiche) peut exercer dans plusieurs secteurs
 * (ex. hôtel + restaurant). Le secteur principal reste providers.category_id ; les secteurs
 * supplémentaires sont listés ici. Les abonnements achetés ensemble sont reliés par bundle_parent_id
 * (un seul paiement, un abonnement par secteur).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_sectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('provider_categories')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['provider_id', 'category_id']);
        });

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->foreignId('bundle_parent_id')->nullable()->after('upgraded_from_id')
                ->constrained('subscriptions')->nullOnDelete();
            // Secteur couvert par l'abonnement (les forfaits génériques servent à plusieurs secteurs).
            $table->foreignId('sector_category_id')->nullable()->after('plan_id')
                ->constrained('provider_categories')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sector_category_id');
            $table->dropConstrainedForeignId('bundle_parent_id');
        });
        Schema::dropIfExists('provider_sectors');
    }
};
