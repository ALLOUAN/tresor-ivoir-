<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Ajoute "manual" à l'enum payment_method : permet à l'admin d'attribuer un abonnement
     * en back-office sans forcer un faux libellé de moyen de paiement mobile (le prestataire
     * n'ayant réellement rien payé via CinetPay dans ce cas).
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY payment_method ENUM('orange_money','mtn_momo','wave','moov_money','card','paypal','cinetpay','free','manual') NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE subscriptions MODIFY payment_method ENUM('orange_money','mtn_momo','wave','moov_money','card','paypal','cinetpay','free') NULL");
    }
};
