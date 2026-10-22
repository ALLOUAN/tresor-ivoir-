<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Avis « séjour vérifié » : lien vers la réservation réelle qui prouve que l'auteur est
 * bien venu. Les avis existants restent publiés, simplement sans le badge.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_verified')->default(false)->after('status');
            $table->foreignId('reservation_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reservation_id');
            $table->dropColumn('is_verified');
        });
    }
};
