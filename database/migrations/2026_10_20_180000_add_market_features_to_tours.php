<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Circuits « au niveau du marché » (TourRadar, Viator, GetYourGuide, WeTravel, Zaui) :
 *  - page détaillée avec programme jour par jour, inclus / non inclus ;
 *  - acompte à la réservation puis solde avant le départ ;
 *  - départ garanti à partir d'un minimum de voyageurs ;
 *  - tarifs enfant, supplément chambre individuelle, tarif dégressif (privatif), options ;
 *  - lieux de prise en charge ;
 *  - réservation immédiate sur les départs programmés ;
 *  - conditions d'annulation par circuit ;
 *  - liste nominative des voyageurs.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->string('summary', 300)->nullable()->after('description');
            $table->json('itinerary')->nullable()->after('summary');            // [{title, description, meals, accommodation}]
            $table->text('included')->nullable()->after('itinerary');
            $table->text('not_included')->nullable()->after('included');
            $table->json('pickup_points')->nullable()->after('not_included');    // [{label, time, note}]
            $table->unsignedInteger('child_price_xof')->nullable()->after('price_xof');
            $table->unsignedTinyInteger('child_age_max')->nullable()->after('child_price_xof');
            $table->unsignedInteger('single_supplement_xof')->nullable()->after('child_age_max');
            $table->json('group_prices')->nullable()->after('single_supplement_xof'); // [{min, price_xof}] tarif dégressif
            $table->json('extras')->nullable()->after('group_prices');           // [{label, price_xof, per}]
            $table->unsignedSmallInteger('min_participants')->nullable()->after('max_participants');
            $table->unsignedTinyInteger('guarantee_days')->default(7)->after('min_participants');
            $table->unsignedTinyInteger('deposit_percent')->nullable()->after('guarantee_days'); // null = paiement intégral
            $table->unsignedSmallInteger('balance_due_days')->default(15)->after('deposit_percent');
            $table->json('cancellation_policy')->nullable()->after('balance_due_days'); // [{days, percent}]
            $table->boolean('instant_booking')->default(true)->after('cancellation_policy');
            $table->string('difficulty', 20)->nullable()->after('instant_booking');
            $table->json('languages')->nullable()->after('difficulty');
        });

        foreach (DB::table('tour_packages')->orderBy('id')->get(['id', 'name']) as $tour) {
            DB::table('tour_packages')->where('id', $tour->id)->update(['slug' => Str::slug($tour->name).'-'.$tour->id]);
        }
        Schema::table('tour_packages', fn (Blueprint $table) => $table->unique('slug'));

        Schema::table('tour_departures', function (Blueprint $table) {
            $table->unsignedSmallInteger('min_participants')->nullable()->after('seats_total');
            $table->timestamp('guaranteed_at')->nullable()->after('is_active');
            $table->timestamp('cancelled_at')->nullable()->after('guaranteed_at');
            $table->string('cancel_reason', 255)->nullable()->after('cancelled_at');
        });

        Schema::create('tour_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('tour_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tour_departure_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('tour_name');
            $table->date('departure_date');
            $table->unsignedSmallInteger('adults')->default(1);
            $table->unsignedSmallInteger('children')->default(0);
            $table->unsignedSmallInteger('travelers_count')->default(1);
            $table->unsignedSmallInteger('single_rooms')->default(0);
            $table->string('pickup_point', 255)->nullable();
            $table->json('extras')->nullable();                 // [{label, qty, unit_price_xof}]
            $table->json('price_lines')->nullable();            // détail du prix figé à la réservation
            $table->unsignedInteger('amount_total_xof');
            $table->unsignedInteger('deposit_xof')->default(0); // 0 = paiement intégral
            $table->date('balance_due_on')->nullable();
            $table->unsignedInteger('amount_paid_xof')->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('status', 20)->default('pending_payment')->index();
            $table->string('pending_stage', 10)->nullable();    // deposit | balance | full
            $table->unsignedInteger('pending_amount_xof')->nullable();
            $table->string('gateway_txn_id')->nullable()->index();
            $table->json('cancellation_policy')->nullable();
            $table->unsignedInteger('refund_xof')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 20)->nullable();     // client | agency | departure | unpaid
            $table->timestamp('balance_reminded_at')->nullable();
            $table->string('full_name', 150);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->json('travelers')->nullable();              // [{name, id_number, phone}]
            $table->string('emergency_contact', 200)->nullable();
            $table->timestamps();
            $table->index(['tour_departure_id', 'status']);
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('tour_booking_id')->nullable()->after('booking_request_id')->constrained()->nullOnDelete();
            $table->unique(['tour_booking_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['tour_booking_id', 'type']);
            $table->dropConstrainedForeignId('tour_booking_id');
        });
        Schema::dropIfExists('tour_bookings');
        Schema::table('tour_departures', fn (Blueprint $table) => $table->dropColumn(['min_participants', 'guaranteed_at', 'cancelled_at', 'cancel_reason']));
        Schema::table('tour_packages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'summary', 'itinerary', 'included', 'not_included', 'pickup_points', 'child_price_xof', 'child_age_max',
                'single_supplement_xof', 'group_prices', 'extras', 'min_participants', 'guarantee_days', 'deposit_percent', 'balance_due_days',
                'cancellation_policy', 'instant_booking', 'difficulty', 'languages']);
        });
    }
};
