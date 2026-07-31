<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->foreignId('provider_id')->nullable()->unique()->after('city_id')
                ->constrained('providers')->nullOnDelete();
        });

        // Backfill : les seeders utilisent le même slug côté Provider et Accommodation
        // pour un même établissement (ex. sofitel-abidjan-hotel-ivoire).
        DB::statement('UPDATE accommodations a JOIN providers p ON p.slug = a.slug SET a.provider_id = p.id');
    }

    public function down(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('provider_id');
        });
    }
};
