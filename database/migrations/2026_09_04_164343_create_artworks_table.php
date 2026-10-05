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
        Schema::create('artworks', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->constrained('artwork_categories');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('medium')->nullable();
            $table->string('dimensions')->nullable();
            $table->smallInteger('year_created')->nullable();
            $table->json('images')->nullable();
            $table->unsignedInteger('price_xof');
            $table->unsignedInteger('stock_quantity')->default(1);
            $table->string('status')->default('published'); // draft, pending_review, published, rejected, suspended, sold, withdrawn
            $table->boolean('is_featured')->default(false);
            $table->unsignedInteger('views_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artworks');
    }
};
