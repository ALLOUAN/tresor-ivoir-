<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('site_media_items', function (Blueprint $table) {
            $table->unsignedInteger('downloads_count')->default(0)->after('likes_count');
        });
    }

    public function down(): void
    {
        Schema::table('site_media_items', function (Blueprint $table) {
            $table->dropColumn('downloads_count');
        });
    }
};
