<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Types de visite « au niveau du marché » (billetterie des sites et musées, GetYourGuide, Viator) :
 *  - créneaux horaires avec places limitées (entrée à heure fixe) ;
 *  - tarifs par catégorie de visiteur (adulte, enfant, étudiant, résident…) ;
 *  - annulation gratuite jusqu'à X heures avant, et « réserver maintenant, payer sur place » ;
 *  - fiche détaillée : langues du guide, inclus / non inclus, point de rendez-vous, accessibilité, à prévoir ;
 *  - tarif week-end et tarif heures creuses ;
 *  - options payantes (audioguide, photos, repas…).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tourist_visit_types', function (Blueprint $table) {
            $table->json('price_tiers')->nullable()->after('price_xof');            // [{label, price_xof}]
            $table->json('slot_times')->nullable()->after('max_participants');      // ["09:00", "09:30", …]
            $table->unsignedSmallInteger('slot_capacity')->nullable()->after('slot_times');
            $table->unsignedSmallInteger('free_cancellation_hours')->nullable()->after('slot_capacity'); // null = non remboursable
            $table->boolean('allow_pay_on_site')->default(false)->after('free_cancellation_hours');
            $table->json('languages')->nullable()->after('allow_pay_on_site');
            $table->text('included')->nullable()->after('languages');
            $table->text('not_included')->nullable()->after('included');
            $table->string('meeting_point', 255)->nullable()->after('not_included');
            $table->string('accessibility', 255)->nullable()->after('meeting_point');
            $table->text('to_bring')->nullable()->after('accessibility');
            $table->smallInteger('weekend_adjust_percent')->nullable()->after('to_bring');     // ex. +20 ou −10
            $table->string('offpeak_until', 5)->nullable()->after('weekend_adjust_percent');    // créneaux avant cette heure
            $table->unsignedTinyInteger('offpeak_discount_percent')->nullable()->after('offpeak_until');
            $table->json('addons')->nullable()->after('offpeak_discount_percent');             // [{label, price_xof, per: person|booking}]
        });

        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->string('visit_time', 5)->nullable()->after('desired_date');
            $table->json('tier_breakdown')->nullable()->after('residents_count');   // [{label, qty, unit_price_xof}]
            $table->json('addons')->nullable()->after('tier_breakdown');            // [{label, qty, unit_price_xof}]
            $table->string('language', 40)->nullable()->after('addons');
            $table->boolean('pay_on_site')->default(false)->after('language');
            $table->smallInteger('price_adjust_percent')->nullable()->after('pay_on_site');
            $table->timestamp('cancellable_until')->nullable()->after('price_adjust_percent');
            $table->index(['tourist_visit_type_id', 'desired_date', 'visit_time'], 'tv_type_date_time_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tourist_visits', function (Blueprint $table) {
            $table->dropIndex('tv_type_date_time_idx');
            $table->dropColumn(['visit_time', 'tier_breakdown', 'addons', 'language', 'pay_on_site', 'price_adjust_percent', 'cancellable_until']);
        });
        Schema::table('tourist_visit_types', function (Blueprint $table) {
            $table->dropColumn(['price_tiers', 'slot_times', 'slot_capacity', 'free_cancellation_hours', 'allow_pay_on_site', 'languages',
                'included', 'not_included', 'meeting_point', 'accessibility', 'to_bring', 'weekend_adjust_percent', 'offpeak_until',
                'offpeak_discount_percent', 'addons']);
        });
    }
};
