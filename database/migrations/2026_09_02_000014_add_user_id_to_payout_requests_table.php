<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // provider_id devient nullable : une demande de retrait peut désormais venir d'un
        // prestataire OU d'un client (jamais les deux) — un foreignId()->change() nécessite
        // doctrine/dbal, on passe par une ALTER brute comme ailleurs dans ce projet pour ce
        // genre de changement de colonne.
        DB::statement('ALTER TABLE payout_requests MODIFY provider_id BIGINT UNSIGNED NULL');

        Schema::table('payout_requests', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('provider_id')->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payout_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });

        DB::statement('ALTER TABLE payout_requests MODIFY provider_id BIGINT UNSIGNED NOT NULL');
    }
};
