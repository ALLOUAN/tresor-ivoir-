<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reservation_id')->unique()->constrained()->cascadeOnDelete();

            // Étape 1 — Voyage & documents
            $table->string('travel_type'); // domestique, international
            $table->string('document_type'); // cni, passeport, permis_conduire, carte_consulaire
            $table->string('document_number');
            $table->string('document_scan_front_url');
            $table->string('document_scan_back_url');
            $table->string('travel_reason');
            $table->string('selfie_url');
            $table->date('hotel_check_in_date');
            $table->time('hotel_check_in_time');
            $table->date('hotel_check_out_date');
            $table->time('hotel_check_out_time');

            // Étape 2 — Identité
            $table->string('first_name');
            $table->string('last_name');
            $table->date('birth_date');
            $table->string('birth_place');
            $table->string('father_name');
            $table->string('mother_name');
            $table->string('profession');
            $table->string('home_address');
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();

            // Étape 3 — Contact & validation
            $table->string('phone_country_code')->default('+225');
            $table->string('phone_number');
            $table->string('email')->nullable();
            $table->boolean('data_confirmed')->default(false);
            $table->string('signature_url');

            $table->timestamp('submitted_at');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_registrations');
    }
};
