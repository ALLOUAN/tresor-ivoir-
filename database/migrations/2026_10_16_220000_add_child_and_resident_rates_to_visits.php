<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourist_experiences', function (Blueprint $table) {
            $table->unsignedInteger('visit_child_price_xof')->nullable();
            $table->unsignedInteger('visit_resident_price_xof')->nullable();
        });
        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->unsignedSmallInteger('residents_count')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('tourist_experiences', fn (Blueprint $table) => $table->dropColumn(['visit_child_price_xof', 'visit_resident_price_xof']));
        Schema::table('tourist_visits', fn (Blueprint $table) => $table->dropColumn(['children_count', 'residents_count']));
    }
};
