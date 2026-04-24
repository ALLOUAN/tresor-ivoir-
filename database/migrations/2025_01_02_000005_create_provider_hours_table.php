<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->tinyInteger('day_of_week')->comment('0=Dimanche, 6=Samedi');
            $table->time('open_time')->nullable();
            $table->time('close_time')->nullable();
            $table->tinyInteger('is_closed')->default(0);
            $table->string('note', 255)->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_hours');
    }
};
