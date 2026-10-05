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
        Schema::table('cultural_domains', function (Blueprint $table) {
            $table->string('icon_image_url', 500)->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cultural_domains', function (Blueprint $table) {
            $table->dropColumn('icon_image_url');
        });
    }
};
