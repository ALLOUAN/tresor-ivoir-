<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('prestation_settings', function (Blueprint $table) {
            $table->dropColumn(['banner_image_url', 'banner_title', 'banner_content']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestation_settings', function (Blueprint $table) {
            $table->string('banner_image_url', 500)->nullable();
            $table->string('banner_title')->nullable();
            $table->longText('banner_content')->nullable();
        });
    }
};
