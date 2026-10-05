<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            // null = wallet plateforme. MySQL autorise plusieurs lignes NULL dans une
            // colonne UNIQUE, donc l'unicité du singleton plateforme est garantie côté
            // applicatif (Wallet::platform(), firstOrCreate) plutôt que par la contrainte DB.
            $table->foreignId('provider_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('balance_available_xof')->default(0);
            $table->unsignedBigInteger('balance_pending_xof')->default(0);
            $table->string('currency', 3)->default('XOF');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
