<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payout_request_id')->constrained('payout_requests')->cascadeOnDelete();
            $table->string('event_type', 40);
            $table->string('actor_type', 10);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->json('details')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('payout_request_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payout_audit_logs');
    }
};
