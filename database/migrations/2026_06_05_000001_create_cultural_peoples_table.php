<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cultural_peoples', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 120)->unique();
            $table->enum('zone_geographique', ['Nord', 'Sud', 'Est', 'Ouest', 'Centre'])->nullable();
            $table->string('famille_linguistique', 100)->nullable();
            $table->string('langue_principale', 100)->nullable();
            $table->unsignedInteger('population_estimee')->nullable();
            $table->string('capitale_culturelle', 100)->nullable();
            $table->text('description')->nullable();
            $table->longText('histoire')->nullable();
            $table->json('symboles')->nullable();
            $table->string('thumbnail', 500)->nullable();
            $table->string('cover_image', 500)->nullable();
            $table->tinyInteger('is_featured')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cultural_peoples');
    }
};
