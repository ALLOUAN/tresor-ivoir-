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
        Schema::table('events', function (Blueprint $table) {
            $table->string('subtitle_fr', 200)->nullable()->after('title_en');
            $table->string('subtitle_en', 200)->nullable()->after('subtitle_fr');
            $table->string('audience', 150)->nullable()->after('city');
            $table->json('program')->nullable()->after('description_en');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['subtitle_fr', 'subtitle_en', 'audience', 'program']);
        });
    }
};
