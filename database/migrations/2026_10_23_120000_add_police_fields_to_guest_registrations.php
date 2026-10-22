<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fiche d'enregistrement voyageur : champs habituels d'une fiche de police hôtelière
 * (nationalité, validité de la pièce, provenance / destination). Le verso devient facultatif
 * pour un passeport (une seule page d'identité).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('guest_registrations', function (Blueprint $table) {
            $table->string('nationality', 80)->nullable()->after('last_name');
            $table->date('document_expiry_date')->nullable()->after('document_number');
            $table->string('coming_from', 150)->nullable()->after('travel_reason');
            $table->string('going_to', 150)->nullable()->after('coming_from');
            $table->string('document_scan_back_url')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('guest_registrations', function (Blueprint $table) {
            $table->dropColumn(['nationality', 'document_expiry_date', 'coming_from', 'going_to']);
        });
    }
};
