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
        Schema::create('prestation_items', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('image_url', 500)->nullable();
            $table->string('icon', 100)->nullable()->comment('Classe FontAwesome utilisée si aucune image n\'est fournie');
            $table->string('link_url', 500)->nullable();
            $table->string('link_label', 150)->nullable();
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
        Schema::dropIfExists('prestation_items');
    }
};
