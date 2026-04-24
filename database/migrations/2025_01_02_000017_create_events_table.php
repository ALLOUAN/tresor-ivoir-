<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('category_id')->constrained('event_categories');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('provider_id')->nullable()->constrained('providers')->nullOnDelete();
            $table->string('title_fr', 255);
            $table->string('title_en', 255)->nullable();
            $table->string('slug', 300)->unique();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->string('cover_url', 500)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->tinyInteger('is_recurring')->default(0);
            $table->string('recurrence_rule', 255)->nullable();
            $table->string('location_name', 255)->nullable();
            $table->string('address', 500)->nullable();
            $table->string('city', 150)->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('price', 10, 2)->nullable();
            $table->tinyInteger('is_free')->default(0);
            $table->string('ticket_url', 500)->nullable();
            $table->string('organizer_name', 255)->nullable();
            $table->string('organizer_phone', 20)->nullable();
            $table->enum('status', ['draft', 'published', 'cancelled', 'past'])->default('draft');
            $table->unsignedInteger('views_count')->default(0);
            $table->string('meta_title_fr', 70)->nullable();
            $table->string('meta_desc_fr', 165)->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
