<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number', 30)->unique()->comment('ex: TDI-2025-00001');
            $table->foreignId('payment_id')->constrained('payments');
            $table->foreignId('provider_id')->constrained('providers');
            $table->decimal('amount_ht', 10, 2);
            $table->decimal('tax_rate', 5, 2)->default(18.00);
            $table->decimal('tax_amount', 10, 2);
            $table->decimal('amount_ttc', 10, 2);
            $table->string('pdf_url', 500)->nullable();
            $table->dateTime('issued_at');
            $table->timestamp('due_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
