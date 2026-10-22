<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Transports & Mobilité « au niveau du marché » (Transfeero, Wheely, VEVS, UTB Ticket) :
 *  1. transferts aéroport à prix fixe par zone, numéro de vol, accueil pancarte, attente gratuite ;
 *  2. devis instantané chauffeur (prise en charge + km + heure, minimum, forfaits) ;
 *  3. flotte de véhicules et disponibilités (pas de double réservation) ;
 *  4. location : dates, caution, km inclus, documents, état des lieux, contrat ;
 *  5. lignes régulières à horaires, choix du siège, billet QR ;
 *  6. options et suppléments (nuit, siège enfant, bagages…) ;
 *  7. chauffeur et véhicule communiqués, statut du trajet ;
 *  8. annulation gratuite et modification.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transport_offers', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('mode', 20)->nullable()->after('slug');                          // transfer | chauffeur | rental | line
            $table->string('vehicle_category', 20)->nullable()->after('mode');
            $table->unsignedSmallInteger('max_luggage')->nullable()->after('max_passengers');
            $table->json('zone_prices')->nullable()->after('price_xof');                    // transfert : [{label, price_xof}]
            $table->string('origin_label', 150)->nullable()->after('zone_prices');          // ex. Aéroport FHB
            $table->unsignedTinyInteger('round_trip_discount_percent')->nullable()->after('origin_label');
            $table->unsignedSmallInteger('free_waiting_minutes')->nullable()->after('round_trip_discount_percent');
            $table->unsignedInteger('base_fare_xof')->nullable()->after('free_waiting_minutes');   // chauffeur
            $table->unsignedInteger('per_km_xof')->nullable()->after('base_fare_xof');
            $table->unsignedInteger('per_hour_xof')->nullable()->after('per_km_xof');
            $table->unsignedInteger('minimum_xof')->nullable()->after('per_hour_xof');
            $table->unsignedInteger('half_day_xof')->nullable()->after('minimum_xof');
            $table->unsignedInteger('full_day_xof')->nullable()->after('half_day_xof');
            $table->unsignedSmallInteger('included_km')->nullable()->after('full_day_xof');
            $table->unsignedInteger('weekly_daily_xof')->nullable()->after('included_km');       // location : prix / jour dès 7 jours
            $table->unsignedInteger('deposit_xof')->nullable()->after('weekly_daily_xof');        // caution au retrait
            $table->unsignedInteger('extra_km_xof')->nullable()->after('deposit_xof');
            $table->unsignedTinyInteger('min_driver_age')->nullable()->after('extra_km_xof');
            $table->string('route_from', 120)->nullable()->after('min_driver_age');              // ligne
            $table->string('route_to', 120)->nullable()->after('route_from');
            $table->unsignedSmallInteger('trip_minutes')->nullable()->after('route_to');
            $table->json('extras')->nullable()->after('trip_minutes');                           // [{label, price_xof, per}] per: booking|day|passenger
            $table->unsignedTinyInteger('night_surcharge_percent')->nullable()->after('extras');
            $table->unsignedSmallInteger('free_cancellation_hours')->nullable()->after('night_surcharge_percent');
            $table->boolean('instant_booking')->default(true)->after('free_cancellation_hours');
        });
        foreach (DB::table('transport_offers')->orderBy('id')->get(['id', 'name']) as $o) {
            DB::table('transport_offers')->where('id', $o->id)->update(['slug' => Str::slug($o->name).'-'.$o->id]);
        }
        Schema::table('transport_offers', fn (Blueprint $table) => $table->unique('slug'));

        Schema::create('transport_vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('category', 20)->default('berline');
            $table->string('plate', 20)->nullable();
            $table->unsignedSmallInteger('seats')->default(4);
            $table->unsignedSmallInteger('luggage')->default(2);
            $table->boolean('has_ac')->default(true);
            $table->string('photo')->nullable();
            $table->date('unavailable_from')->nullable();   // entretien / indisponibilité
            $table->date('unavailable_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transport_drivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('phone', 30);
            $table->string('languages', 120)->nullable();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('transport_trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transport_offer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transport_vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('departs_at');
            $table->unsignedSmallInteger('seats_total');
            $table->unsignedTinyInteger('seats_per_row')->default(4);
            $table->unsignedInteger('price_xof')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['transport_offer_id', 'departs_at']);
        });

        Schema::create('transport_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('transport_offer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_vehicle_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('transport_trip_id')->nullable()->constrained()->nullOnDelete();
            $table->string('mode', 20);
            $table->string('offer_name', 150);
            $table->string('pickup_label', 255)->nullable();
            $table->decimal('pickup_lat', 10, 7)->nullable();
            $table->decimal('pickup_lng', 10, 7)->nullable();
            $table->string('dropoff_label', 255)->nullable();
            $table->decimal('dropoff_lat', 10, 7)->nullable();
            $table->decimal('dropoff_lng', 10, 7)->nullable();
            $table->decimal('distance_km', 7, 1)->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->boolean('round_trip')->default(false);
            $table->dateTime('return_at')->nullable();
            $table->string('flight_number', 12)->nullable();
            $table->unsignedSmallInteger('passengers')->default(1);
            $table->unsignedSmallInteger('luggage')->default(0);
            $table->json('seats')->nullable();
            $table->json('extras')->nullable();
            $table->json('price_lines')->nullable();
            $table->unsignedInteger('amount_total_xof');
            $table->unsignedInteger('deposit_on_site_xof')->nullable();
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('status', 20)->default('pending_payment')->index();   // pending_payment | paid | cancelled | refunded | failed
            $table->string('trip_status', 20)->nullable();                        // assigned | en_route | arrived | in_progress | completed | no_show
            $table->string('gateway', 20)->nullable();
            $table->string('gateway_txn_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancellable_until')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('refund_xof')->nullable();
            $table->json('documents')->nullable();          // location : permis, pièce d'identité
            $table->timestamp('contract_accepted_at')->nullable();
            $table->json('inspection')->nullable();          // location : {out: {km, fuel, photos, at}, in: {...}}
            $table->string('ticket_code', 16)->nullable()->unique();
            $table->timestamp('checked_in_at')->nullable();
            $table->string('full_name', 150);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->timestamps();
            $table->index(['transport_vehicle_id', 'starts_at', 'ends_at']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('transport_booking_id')->nullable()->after('leisure_pass_purchase_id')->constrained()->nullOnDelete();
            $table->unique(['transport_booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['transport_booking_id', 'type']);
            $table->dropConstrainedForeignId('transport_booking_id');
        });
        Schema::dropIfExists('transport_bookings');
        Schema::dropIfExists('transport_trips');
        Schema::dropIfExists('transport_drivers');
        Schema::dropIfExists('transport_vehicles');
        Schema::table('transport_offers', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'mode', 'vehicle_category', 'max_luggage', 'zone_prices', 'origin_label', 'round_trip_discount_percent',
                'free_waiting_minutes', 'base_fare_xof', 'per_km_xof', 'per_hour_xof', 'minimum_xof', 'half_day_xof', 'full_day_xof', 'included_km',
                'weekly_daily_xof', 'deposit_xof', 'extra_km_xof', 'min_driver_age', 'route_from', 'route_to', 'trip_minutes', 'extras',
                'night_surcharge_percent', 'free_cancellation_hours', 'instant_booking']);
        });
    }
};
