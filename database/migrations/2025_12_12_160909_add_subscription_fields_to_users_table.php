<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Crédits actuels (inclus subscription + legacy)
            if (!Schema::hasColumn('users', 'credits_balance')) {
                $table->integer('credits_balance')->default(0)->after('email');
            }

            // Crédits legacy (conservés de l'ancien système)
            if (!Schema::hasColumn('users', 'legacy_credits')) {
                $table->integer('legacy_credits')->default(0)->after('credits_balance');
            }

            // Stripe customer ID (si pas déjà présent)
            if (!Schema::hasColumn('users', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['credits_balance', 'legacy_credits']);
        });
    }
};
