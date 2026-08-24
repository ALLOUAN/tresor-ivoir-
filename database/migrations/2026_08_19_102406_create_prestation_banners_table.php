<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('prestation_banners', function (Blueprint $table) {
            $table->id();
            $table->string('image_url', 500)->nullable();
            $table->string('title')->nullable();
            $table->longText('content')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(1);
            $table->timestamps();

            $table->index(['is_active', 'display_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('prestation_banners');
    }
};
