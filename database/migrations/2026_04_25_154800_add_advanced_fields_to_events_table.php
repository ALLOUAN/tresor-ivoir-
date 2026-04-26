<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->string('cover_alt', 300)->nullable()->after('cover_url');
            $table->string('organizer_email', 255)->nullable()->after('organizer_phone');
            $table->unsignedInteger('capacity')->nullable()->after('organizer_email');
            $table->timestamp('registration_deadline')->nullable()->after('capacity');
            $table->string('timezone', 64)->nullable()->after('registration_deadline');
            $table->string('meta_title_en', 70)->nullable()->after('meta_desc_fr');
            $table->string('meta_desc_en', 165)->nullable()->after('meta_title_en');
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'cover_alt',
                'organizer_email',
                'capacity',
                'registration_deadline',
                'timezone',
                'meta_title_en',
                'meta_desc_en',
            ]);
        });
    }
};
