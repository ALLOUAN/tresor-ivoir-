<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Art & Créations « au niveau du marché » (Saatchi Art, Artsy, galeries d'Abidjan) :
 *  1. page artiste et abonnés (alerte aux nouvelles œuvres) ;
 *  2. livraison (retrait atelier, Abidjan, intérieur), assurance, suivi, réception confirmée ;
 *  3. faire une offre (prix plancher privé, contre-offre, validité 48 h) ;
 *  4. réservation avec acompte et paiement en 3 fois ;
 *  5. « voir sur mon mur » à l'échelle (dimensions en cm) ;
 *  6. garantie de retour 7 jours, artiste payé après le délai ;
 *  7. éditions limitées numérotées par format ;
 *  8. commandes sur mesure : brief, devis, acompte, esquisse validée, solde.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artist_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name', 150);
            $table->string('tagline', 200)->nullable();
            $table->text('bio')->nullable();
            $table->text('statement')->nullable();
            $table->string('city', 120)->nullable();
            $table->unsignedSmallInteger('birth_year')->nullable();
            $table->text('education')->nullable();
            $table->text('exhibitions')->nullable();
            $table->string('photo')->nullable();
            $table->string('studio_photo')->nullable();
            $table->boolean('accepts_commissions')->default(true);
            $table->unsignedInteger('commission_from_xof')->nullable();
            $table->unsignedTinyInteger('commission_deposit_percent')->default(50);
            $table->string('pickup_address', 255)->nullable();
            $table->unsignedInteger('abidjan_delivery_xof')->nullable();
            $table->unsignedInteger('country_delivery_xof')->nullable();
            $table->unsignedInteger('large_supplement_xof')->nullable();
            $table->decimal('insurance_percent', 4, 2)->default(2);
            $table->boolean('returns_accepted')->default(true);
            $table->timestamps();
        });

        Schema::create('artist_follows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['user_id', 'provider_id']);
        });

        Schema::table('artworks', function (Blueprint $table) {
            $table->decimal('width_cm', 6, 1)->nullable()->after('dimensions');
            $table->decimal('height_cm', 6, 1)->nullable()->after('width_cm');
            $table->decimal('depth_cm', 6, 1)->nullable()->after('height_cm');
            $table->boolean('accepts_offers')->default(false)->after('price_xof');
            $table->unsignedInteger('min_offer_xof')->nullable()->after('accepts_offers');
            $table->boolean('layaway_allowed')->default(false)->after('min_offer_xof');
        });

        Schema::create('artwork_editions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->string('label', 120);
            $table->decimal('width_cm', 6, 1)->nullable();
            $table->decimal('height_cm', 6, 1)->nullable();
            $table->unsignedInteger('price_xof');
            $table->unsignedSmallInteger('edition_size');
            $table->unsignedSmallInteger('sold_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('artwork_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('artwork_id')->constrained()->cascadeOnDelete();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('amount_xof');
            $table->unsignedInteger('counter_amount_xof')->nullable();
            $table->string('message', 500)->nullable();
            $table->string('status', 20)->default('pending')->index();      // pending | accepted | countered | declined | expired | paid | withdrawn
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
        });

        Schema::table('artwork_orders', function (Blueprint $table) {
            $table->foreignId('artwork_edition_id')->nullable()->after('artwork_id')->constrained()->nullOnDelete();
            $table->string('edition_label', 120)->nullable()->after('artwork_edition_id');
            $table->unsignedSmallInteger('edition_number')->nullable()->after('edition_label');
            $table->unsignedSmallInteger('edition_size')->nullable()->after('edition_number');
            $table->foreignId('artwork_offer_id')->nullable()->after('edition_size')->constrained()->nullOnDelete();
            $table->string('delivery_mode', 20)->nullable()->after('artist_net_amount_xof');   // pickup | abidjan | country
            $table->string('delivery_address', 255)->nullable()->after('delivery_mode');
            $table->string('delivery_city', 120)->nullable()->after('delivery_address');
            $table->unsignedInteger('delivery_fee_xof')->default(0)->after('delivery_city');
            $table->unsignedInteger('insurance_xof')->default(0)->after('delivery_fee_xof');
            $table->string('carrier', 80)->nullable()->after('insurance_xof');
            $table->string('tracking_number', 80)->nullable()->after('carrier');
            $table->string('payment_plan', 10)->default('full')->after('tracking_number');      // full | layaway
            $table->unsignedInteger('amount_paid_xof')->default(0)->after('payment_plan');
            $table->json('installments')->nullable()->after('amount_paid_xof');               // [{n, amount_xof, due_on, paid_at}]
            $table->unsignedTinyInteger('pending_installment')->nullable()->after('installments');
            $table->timestamp('received_at')->nullable()->after('delivered_at');
            $table->string('return_status', 20)->nullable()->after('received_at');              // requested | accepted | refused | refunded
            $table->string('return_reason', 500)->nullable()->after('return_status');
            $table->timestamp('return_requested_at')->nullable()->after('return_reason');
        });

        Schema::create('art_commissions', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('subject', 200);
            $table->text('brief');
            $table->string('format', 80)->nullable();
            $table->string('medium', 80)->nullable();
            $table->string('palette', 120)->nullable();
            $table->unsignedInteger('budget_xof')->nullable();
            $table->date('wanted_by')->nullable();
            $table->json('references')->nullable();
            $table->string('status', 20)->default('requested')->index();
            // requested | quoted | declined | deposit_paid | sketch_sent | revision | sketch_approved | balance_paid | delivered | cancelled
            $table->unsignedInteger('quote_xof')->nullable();
            $table->unsignedSmallInteger('quote_days')->nullable();
            $table->text('quote_message')->nullable();
            $table->unsignedInteger('deposit_xof')->nullable();
            $table->unsignedInteger('amount_paid_xof')->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->json('sketches')->nullable();
            $table->text('sketch_note')->nullable();
            $table->text('revision_note')->nullable();
            $table->unsignedTinyInteger('revisions_used')->default(0);
            $table->string('pending_stage', 10)->nullable();
            $table->string('gateway_txn_id')->nullable()->index();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('art_commission_id')->nullable()->after('transport_booking_id')->constrained()->nullOnDelete();
            $table->unique(['art_commission_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['art_commission_id', 'type']);
            $table->dropConstrainedForeignId('art_commission_id');
        });
        Schema::dropIfExists('art_commissions');
        Schema::table('artwork_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('artwork_edition_id');
            $table->dropConstrainedForeignId('artwork_offer_id');
            $table->dropColumn(['edition_label', 'edition_number', 'edition_size', 'delivery_mode', 'delivery_address', 'delivery_city',
                'delivery_fee_xof', 'insurance_xof', 'carrier', 'tracking_number', 'payment_plan', 'amount_paid_xof', 'installments',
                'pending_installment', 'received_at', 'return_status', 'return_reason', 'return_requested_at']);
        });
        Schema::dropIfExists('artwork_offers');
        Schema::dropIfExists('artwork_editions');
        Schema::table('artworks', fn (Blueprint $table) => $table->dropColumn(['width_cm', 'height_cm', 'depth_cm', 'accepts_offers', 'min_offer_xof', 'layaway_allowed']));
        Schema::dropIfExists('artist_follows');
        Schema::dropIfExists('artist_profiles');
    }
};
