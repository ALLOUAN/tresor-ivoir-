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
        Schema::create('homepage_bubbles', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->nullable();
            $table->text('description')->nullable();
            $table->string('link_url', 500)->nullable();
            $table->string('link_label', 150)->nullable();
            $table->decimal('position_top', 5, 2)->default(50);
            $table->decimal('position_left', 5, 2)->default(50);
            $table->string('size', 10)->default('md');
            $table->string('color_hex', 7)->nullable();
            $table->string('icon', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('homepage_bubbles');
    }
};
