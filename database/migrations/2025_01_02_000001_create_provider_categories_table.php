<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('provider_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 100)->unique();
            $table->string('name_fr', 150);
            $table->string('name_en', 150);
            $table->string('icon', 100)->nullable();
            $table->string('color_hex', 7)->nullable();
            $table->text('description_fr')->nullable();
            $table->text('description_en')->nullable();
            $table->foreignId('parent_id')->nullable()->constrained('provider_categories')->nullOnDelete();
            $table->tinyInteger('sort_order')->default(0);
            $table->tinyInteger('is_active')->default(1);
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('provider_categories');
    }
};
