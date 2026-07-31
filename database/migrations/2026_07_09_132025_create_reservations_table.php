<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('accommodation_id')
                  ->nullable()
                  ->constrained('accommodations')
                  ->nullOnDelete();
            $table->string('accommodation_name');

            $table->string('room_name');
            $table->unsignedInteger('room_price_xof')->nullable();

            $table->date('check_in');
            $table->date('check_out');
            $table->unsignedSmallInteger('nights')->default(1);
            $table->unsignedSmallInteger('rooms_count')->default(1);
            $table->unsignedSmallInteger('guests_count')->default(1);
            $table->unsignedInteger('total_xof')->nullable();

            $table->string('full_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->text('message')->nullable();

            $table->string('status', 32)->default('new')->index();

            $table->timestamps();

            $table->index('created_at');
            $table->index(['check_in', 'check_out']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
    }
};
