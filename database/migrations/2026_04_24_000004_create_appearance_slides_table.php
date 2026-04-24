<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appearance_slides', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('subtitle', 255)->nullable();
            $table->text('description')->nullable();
            $table->string('desktop_image_url', 500);
            $table->string('tablet_image_url', 500)->nullable();
            $table->string('mobile_image_url', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(1);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appearance_slides');
    }
};
