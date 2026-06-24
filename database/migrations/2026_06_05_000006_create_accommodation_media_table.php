<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accommodation_media', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accommodation_id')
                  ->constrained('accommodations')
                  ->cascadeOnDelete();

            $table->enum('type', ['photo', 'video'])->default('photo');
            $table->string('url');
            $table->string('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['accommodation_id', 'type']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accommodation_media');
    }
};
