<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Loisirs & Culture « au niveau du marché » (FareHarbor, Bókun, Peek, parcs de loisirs, centres sportifs) :
 *  1. créneaux en temps réel, réservation immédiate et billet à QR code ;
 *  2. ressources partagées (salle, terrain, matériel) ;
 *  3. formules anniversaire / groupe / entreprise avec acompte ;
 *  4. tarifs par catégorie, options, heures creuses / week-end ;
 *  5. conditions de participation et décharge en ligne ;
 *  6. cartes multi-séances et abonnements ;
 *  7. liste d'attente sur les créneaux complets ;
 *  8. annulation gratuite et report par le client.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('slug')->nullable()->after('name');
            $table->json('slot_times')->nullable()->after('max_participants');
            $table->unsignedSmallInteger('slot_capacity')->nullable()->after('slot_times');
            $table->json('weekdays')->nullable()->after('slot_capacity');           // [1..7] ISO, null = tous les jours
            $table->boolean('instant_booking')->default(true)->after('weekdays');
            $table->json('price_tiers')->nullable()->after('price_xof');            // [{label, price_xof}]
            $table->json('addons')->nullable()->after('price_tiers');               // [{label, price_xof, per}]
            $table->smallInteger('weekend_adjust_percent')->nullable()->after('addons');
            $table->string('offpeak_until', 5)->nullable()->after('weekend_adjust_percent');
            $table->unsignedTinyInteger('offpeak_discount_percent')->nullable()->after('offpeak_until');
            $table->unsignedSmallInteger('free_cancellation_hours')->nullable()->after('instant_booking');
            $table->unsignedTinyInteger('min_age')->nullable()->after('free_cancellation_hours');
            $table->unsignedSmallInteger('min_height_cm')->nullable()->after('min_age');
            $table->string('level', 20)->nullable()->after('min_height_cm');
            $table->text('requirements')->nullable()->after('level');                // contre-indications, conditions
            $table->text('to_bring')->nullable()->after('requirements');
            $table->boolean('requires_waiver')->default(false)->after('to_bring');
            $table->text('waiver_text')->nullable()->after('requires_waiver');
        });
        foreach (DB::table('activities')->orderBy('id')->get(['id', 'name']) as $a) {
            DB::table('activities')->where('id', $a->id)->update(['slug' => Str::slug($a->name).'-'.$a->id]);
        }
        Schema::table('activities', fn (Blueprint $table) => $table->unique('slug'));

        Schema::create('leisure_resources', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->unsignedSmallInteger('units')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('activity_resource', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('leisure_resource_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('units')->default(1);
            $table->boolean('per_participant')->default(false);
            $table->unique(['activity_id', 'leisure_resource_id']);
        });

        Schema::create('leisure_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->string('slug')->unique();
            $table->string('kind', 20)->default('birthday');                     // birthday | group | corporate
            $table->text('description')->nullable();
            $table->text('includes')->nullable();
            $table->unsignedInteger('weekday_price_xof');
            $table->unsignedInteger('weekend_price_xof')->nullable();
            $table->unsignedSmallInteger('included_participants')->default(10);
            $table->unsignedInteger('extra_participant_xof')->default(0);
            $table->unsignedSmallInteger('min_participants')->default(1);
            $table->unsignedSmallInteger('max_participants')->nullable();
            $table->unsignedSmallInteger('duration_minutes')->default(120);
            $table->unsignedInteger('deposit_xof')->default(0);
            $table->json('slot_times')->nullable();
            $table->foreignId('leisure_resource_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('free_cancellation_days')->default(7);
            $table->json('images')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leisure_passes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->constrained()->cascadeOnDelete();
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sessions')->nullable();                 // null = illimité (abonnement)
            $table->unsignedSmallInteger('validity_days')->default(180);
            $table->unsignedInteger('price_xof');
            $table->json('activity_ids')->nullable();                             // null = toutes les activités
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('leisure_pass_purchases', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('leisure_pass_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('pass_name', 150);
            $table->unsignedSmallInteger('sessions_total')->nullable();
            $table->unsignedSmallInteger('sessions_used')->default(0);
            $table->json('activity_ids')->nullable();
            $table->date('expires_on')->nullable();
            $table->unsignedInteger('amount_xof');
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->string('status', 20)->default('pending_payment')->index();   // pending_payment | active | failed
            $table->string('gateway', 20)->nullable();
            $table->string('gateway_txn_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_bookings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignId('activity_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leisure_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('provider_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('leisure_pass_purchase_id')->nullable()->constrained()->nullOnDelete();
            $table->string('item_name', 150);
            $table->date('booking_date');
            $table->string('booking_time', 5)->nullable();
            $table->unsignedSmallInteger('duration_minutes')->nullable();
            $table->unsignedSmallInteger('participants');
            $table->json('tier_breakdown')->nullable();
            $table->json('addons')->nullable();
            $table->smallInteger('price_adjust_percent')->nullable();
            $table->unsignedInteger('amount_total_xof');
            $table->unsignedInteger('amount_paid_xof')->default(0);
            $table->decimal('commission_percent', 5, 2)->default(0);
            $table->unsignedInteger('commission_amount_xof')->default(0);
            $table->string('status', 20)->default('pending_payment')->index();   // pending_payment | paid | cancelled | refunded | failed
            $table->string('gateway', 20)->nullable();
            $table->string('gateway_txn_id')->nullable()->index();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('cancellable_until')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->unsignedInteger('refund_xof')->nullable();
            $table->unsignedTinyInteger('reschedule_count')->default(0);
            $table->json('waiver')->nullable();                                   // {names: [...], accepted_at, ip}
            $table->string('ticket_code', 16)->nullable()->unique();
            $table->timestamp('checked_in_at')->nullable();
            $table->foreignId('checked_in_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('full_name', 150);
            $table->string('email');
            $table->string('phone', 30)->nullable();
            $table->text('message')->nullable();
            $table->string('celebrant_name', 120)->nullable();
            $table->timestamps();
            $table->index(['activity_id', 'booking_date', 'booking_time']);
        });

        Schema::create('activity_waitlist', function (Blueprint $table) {
            $table->id();
            $table->foreignId('activity_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('booking_date');
            $table->string('booking_time', 5)->nullable();
            $table->unsignedSmallInteger('participants')->default(1);
            $table->string('status', 20)->default('waiting')->index();           // waiting | notified | booked | expired | cancelled
            $table->timestamp('notified_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->unique(['activity_id', 'user_id', 'booking_date', 'booking_time'], 'waitlist_unique');
        });

        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->foreignId('activity_booking_id')->nullable()->after('tour_booking_id')->constrained()->nullOnDelete();
            $table->foreignId('leisure_pass_purchase_id')->nullable()->after('activity_booking_id')->constrained()->nullOnDelete();
            $table->unique(['activity_booking_id', 'type']);
            $table->unique(['leisure_pass_purchase_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('wallet_transactions', function (Blueprint $table) {
            $table->dropUnique(['activity_booking_id', 'type']);
            $table->dropUnique(['leisure_pass_purchase_id', 'type']);
            $table->dropConstrainedForeignId('activity_booking_id');
            $table->dropConstrainedForeignId('leisure_pass_purchase_id');
        });
        Schema::dropIfExists('activity_waitlist');
        Schema::dropIfExists('activity_bookings');
        Schema::dropIfExists('leisure_pass_purchases');
        Schema::dropIfExists('leisure_passes');
        Schema::dropIfExists('leisure_packages');
        Schema::dropIfExists('activity_resource');
        Schema::dropIfExists('leisure_resources');
        Schema::table('activities', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn(['slug', 'slot_times', 'slot_capacity', 'weekdays', 'instant_booking', 'price_tiers', 'addons',
                'weekend_adjust_percent', 'offpeak_until', 'offpeak_discount_percent', 'free_cancellation_hours', 'min_age',
                'min_height_cm', 'level', 'requirements', 'to_bring', 'requires_waiver', 'waiver_text']);
        });
    }
};
