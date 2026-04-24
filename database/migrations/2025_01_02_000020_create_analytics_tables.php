<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('clicks_phone')->default(0);
            $table->unsignedInteger('clicks_website')->default(0);
            $table->unsignedInteger('clicks_direction')->default(0);
            $table->unsignedInteger('new_reviews')->default(0);
            $table->unsignedInteger('search_appearances')->default(0);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['provider_id', 'date']);
        });

        Schema::create('article_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->date('date');
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('shares_count')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->unsignedSmallInteger('avg_read_time_sec')->default(0);
            $table->decimal('bounce_rate', 5, 2)->default(0);
            $table->string('referrer_source', 100)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['article_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_analytics');
        Schema::dropIfExists('provider_analytics');
    }
};
