<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_contact_settings', function (Blueprint $table) {
            $table->id();
            $table->string('phone_1', 64)->nullable();
            $table->string('phone_2', 64)->nullable();
            $table->string('email_primary', 255)->nullable();
            $table->string('email_secondary', 255)->nullable();
            $table->string('contact_form_email', 255)->nullable();
            $table->text('opening_hours')->nullable();
            $table->string('address', 500)->nullable();
            $table->string('latitude', 32)->nullable();
            $table->string('longitude', 32)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_contact_settings');
    }
};
