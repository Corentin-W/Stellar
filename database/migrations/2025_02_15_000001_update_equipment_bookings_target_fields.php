<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('equipment_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('equipment_bookings', 'target_name')) {
                $table->string('target_name')->nullable()->after('voyager_target_guid');
            }

            if (!Schema::hasColumn('equipment_bookings', 'target_ra')) {
                $table->double('target_ra')->nullable()->after('target_name');
            }

            if (!Schema::hasColumn('equipment_bookings', 'target_dec')) {
                $table->double('target_dec')->nullable()->after('target_ra');
            }

            if (!Schema::hasColumn('equipment_bookings', 'target_pa')) {
                $table->double('target_pa')->nullable()->after('target_dec');
            }

            if (!Schema::hasColumn('equipment_bookings', 'target_plan')) {
                $table->json('target_plan')->nullable()->after('target_pa');
            }
        });
    }

    public function down(): void
    {
        Schema::table('equipment_bookings', function (Blueprint $table) {
            if (Schema::hasColumn('equipment_bookings', 'target_plan')) {
                $table->dropColumn('target_plan');
            }

            if (Schema::hasColumn('equipment_bookings', 'target_pa')) {
                $table->dropColumn('target_pa');
            }

            if (Schema::hasColumn('equipment_bookings', 'target_dec')) {
                $table->dropColumn('target_dec');
            }

            if (Schema::hasColumn('equipment_bookings', 'target_ra')) {
                $table->dropColumn('target_ra');
            }

            if (Schema::hasColumn('equipment_bookings', 'target_name')) {
                $table->dropColumn('target_name');
            }
        });
    }
};
