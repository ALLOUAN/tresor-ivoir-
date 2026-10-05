<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->nullOnDelete();
        });

        // Rattachement rétroactif des réservations invité existantes par correspondance d'email.
        DB::statement('
            UPDATE reservations
            JOIN users ON users.email = reservations.email
            SET reservations.user_id = users.id
            WHERE reservations.user_id IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
        });
    }
};
