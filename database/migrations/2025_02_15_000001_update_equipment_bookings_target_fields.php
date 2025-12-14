<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipment_bookings')) {
            Schema::table('equipment_bookings', function (Blueprint $table) {
                if (!Schema::hasColumn('equipment_bookings', 'target_name')) {
                    $table->string('target_name')->nullable();
                }

                if (!Schema::hasColumn('equipment_bookings', 'target_ra')) {
                    $table->double('target_ra')->nullable();
                }

                if (!Schema::hasColumn('equipment_bookings', 'target_dec')) {
                    $table->double('target_dec')->nullable();
                }

                if (!Schema::hasColumn('equipment_bookings', 'target_pa')) {
                    $table->double('target_pa')->nullable();
                }

                if (!Schema::hasColumn('equipment_bookings', 'target_plan')) {
                    $table->json('target_plan')->nullable();
                }
            });
        }
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
