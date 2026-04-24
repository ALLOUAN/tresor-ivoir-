<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained('providers')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->tinyInteger('rating');
            $table->tinyInteger('rating_quality')->nullable();
            $table->tinyInteger('rating_price')->nullable();
            $table->tinyInteger('rating_welcome')->nullable();
            $table->tinyInteger('rating_clean')->nullable();
            $table->string('title', 200)->nullable();
            $table->text('comment')->nullable();
            $table->string('author_name', 150)->nullable();
            $table->date('visit_date')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'flagged'])->default('pending');
            $table->foreignId('moderated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('moderated_at')->nullable();
            $table->string('rejection_reason', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
