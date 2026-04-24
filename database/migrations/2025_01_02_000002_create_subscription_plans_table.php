<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->enum('code', ['bronze', 'silver', 'gold'])->unique();
            $table->string('name_fr', 100);
            $table->string('name_en', 100);
            $table->decimal('price_monthly', 10, 2);
            $table->decimal('price_yearly', 10, 2);
            $table->tinyInteger('photos_limit');
            $table->smallInteger('description_chars');
            $table->tinyInteger('has_video')->default(0);
            $table->tinyInteger('has_newsletter')->default(0);
            $table->tinyInteger('has_homepage')->default(0);
            $table->tinyInteger('has_social_posts')->default(0);
            $table->tinyInteger('has_verified_badge')->default(0);
            $table->enum('stats_level', ['basic', 'advanced', 'full']);
            $table->enum('support_level', ['email', 'chat', 'dedicated']);
            $table->tinyInteger('min_duration_months');
            $table->tinyInteger('is_active')->default(1);
            $table->tinyInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
