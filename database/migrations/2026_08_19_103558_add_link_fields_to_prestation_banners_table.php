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
        Schema::table('prestation_banners', function (Blueprint $table) {
            $table->string('link_url', 500)->nullable()->after('content');
            $table->string('link_label', 150)->nullable()->after('link_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('prestation_banners', function (Blueprint $table) {
            $table->dropColumn(['link_url', 'link_label']);
        });
    }
};
