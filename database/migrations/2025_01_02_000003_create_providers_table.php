<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('providers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('category_id')->constrained('provider_categories');
            $table->string('name', 255);
            $table->string('slug', 300)->unique();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('short_desc_fr', 500)->nullable();
            $table->string('logo_url', 500)->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 150)->nullable();
            $table->string('region', 150)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('phone2', 20)->nullable();
            $table->string('email', 255)->nullable();
            $table->string('website', 500)->nullable();
            $table->string('facebook_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('tiktok_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->enum('price_range', ['budget', 'mid', 'premium', 'luxury'])->nullable();
            $table->decimal('price_min', 10, 2)->nullable();
            $table->decimal('price_max', 10, 2)->nullable();
            $table->decimal('rating_avg', 3, 2)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->unsignedInteger('clicks_phone')->default(0);
            $table->unsignedInteger('clicks_website')->default(0);
            $table->enum('status', ['pending', 'active', 'suspended', 'deleted'])->default('pending');
            $table->tinyInteger('is_verified')->default(0);
            $table->tinyInteger('is_featured')->default(0);
            $table->string('meta_title_fr', 70)->nullable();
            $table->string('meta_desc_fr', 165)->nullable();
            $table->string('meta_title_en', 70)->nullable();
            $table->string('meta_desc_en', 165)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        if (DB::connection()->getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE providers ADD FULLTEXT fulltext_providers_name (name)');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('providers');
    }
};
