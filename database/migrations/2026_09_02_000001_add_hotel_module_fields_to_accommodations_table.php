<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('accommodations', function (Blueprint $table) {
            $table->text('cancellation_policy')->nullable()->after('description');
        });

        DB::statement("ALTER TABLE accommodations MODIFY type ENUM(
            'hotel','resort','guesthouse','hostel','auberge','villa','eco_lodge','residence'
        ) DEFAULT 'hotel'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE accommodations MODIFY type ENUM(
            'hotel','resort','guesthouse','hostel','auberge','villa','eco_lodge'
        ) DEFAULT 'hotel'");

        Schema::table('accommodations', function (Blueprint $table) {
            $table->dropColumn('cancellation_policy');
        });
    }
};
