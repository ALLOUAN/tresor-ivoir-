<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 120)->unique();
            $table->string('name_fr', 150);
            $table->string('name_en', 150);
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->string('icon', 80)->nullable();
            $table->tinyInteger('sort_order')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->string('meta_title_fr', 70)->nullable();
            $table->string('meta_desc_fr', 165)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_categories');
    }
};
