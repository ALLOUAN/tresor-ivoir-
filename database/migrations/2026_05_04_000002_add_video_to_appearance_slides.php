<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appearance_slides', function (Blueprint $table) {
            $table->enum('media_type', ['image', 'video'])->default('image')->after('id');
            $table->string('video_desktop_url', 500)->nullable()->after('mobile_image_url');
            $table->string('video_tablet_url',  500)->nullable()->after('video_desktop_url');
            $table->string('video_mobile_url',  500)->nullable()->after('video_tablet_url');
        });
    }

    public function down(): void
    {
        Schema::table('appearance_slides', function (Blueprint $table) {
            $table->dropColumn(['media_type', 'video_desktop_url', 'video_tablet_url', 'video_mobile_url']);
        });
    }
};
