<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Billet électronique des visites : code unique (QR) et contrôle à l'entrée. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->string('ticket_code', 16)->nullable()->unique();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->dropConstrainedForeignId('checked_in_by');
            $table->dropUnique(['ticket_code']);
            $table->dropColumn(['ticket_code', 'checked_in_at']);
        });
    }
};
