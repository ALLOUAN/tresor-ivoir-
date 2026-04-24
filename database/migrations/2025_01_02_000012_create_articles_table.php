<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->constrained('article_categories');
            $table->foreignId('author_id')->constrained('users');
            $table->string('title_fr', 255);
            $table->string('title_en', 255)->nullable();
            $table->string('slug_fr', 300)->unique();
            $table->string('slug_en', 300)->unique()->nullable();
            $table->string('excerpt_fr', 500)->nullable();
            $table->string('excerpt_en', 500)->nullable();
            $table->longText('content_fr')->nullable();
            $table->longText('content_en')->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->string('cover_alt', 300)->nullable();
            $table->tinyInteger('reading_time')->nullable();
            $table->smallInteger('word_count')->nullable();
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->tinyInteger('is_featured')->default(0);
            $table->tinyInteger('is_destination')->default(0);
            $table->tinyInteger('is_sponsored')->default(0);
            $table->foreignId('sponsor_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->enum('status', ['draft', 'review', 'published', 'archived'])->default('draft');
            $table->string('meta_title_fr', 70)->nullable();
            $table->string('meta_desc_fr', 165)->nullable();
            $table->string('meta_title_en', 70)->nullable();
            $table->string('meta_desc_en', 165)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE articles ADD FULLTEXT fulltext_articles_title_fr (title_fr)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
