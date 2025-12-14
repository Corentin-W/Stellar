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
        Schema::table('subscriptions', function (Blueprint $table) {
            // Ajout des champs pour le modèle RoboTarget
            if (!Schema::hasColumn('subscriptions', 'plan')) {
                $table->enum('plan', ['stardust', 'nebula', 'quasar'])->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('subscriptions', 'credits_per_month')) {
                $table->integer('credits_per_month')->default(0)->after('plan');
            }
            if (!Schema::hasColumn('subscriptions', 'status')) {
                $table->enum('status', ['active', 'inactive', 'trial', 'cancelled'])->default('trial')->after('credits_per_month');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn(['plan', 'credits_per_month', 'status']);
        });
    }
};
