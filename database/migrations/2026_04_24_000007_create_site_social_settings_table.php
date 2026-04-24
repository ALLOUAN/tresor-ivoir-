<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_social_settings', function (Blueprint $table) {
            $table->id();
            $table->string('facebook_url', 500)->nullable();
            $table->string('twitter_url', 500)->nullable();
            $table->string('linkedin_url', 500)->nullable();
            $table->string('instagram_url', 500)->nullable();
            $table->string('youtube_url', 500)->nullable();
            $table->string('whatsapp_phone', 64)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_social_settings');
    }
};
