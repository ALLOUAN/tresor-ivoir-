<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('tour_categories');
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedInteger('price_xof');
            $table->unsignedSmallInteger('duration_days')->nullable();
            $table->unsignedSmallInteger('max_participants')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tour_packages');
    }
};
