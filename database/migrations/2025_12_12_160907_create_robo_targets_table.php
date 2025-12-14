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
        if (!Schema::hasTable('robo_targets')) {
            Schema::create('robo_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->uuid('guid')->unique();
                $table->uuid('set_guid');

                // Target Info
                $table->string('target_name');
                $table->string('ra_j2000'); // Format HH:MM:SS
                $table->string('dec_j2000'); // Format +DD:MM:SS

                // Priority and constraints
                $table->tinyInteger('priority')->default(0); // 0-4
                $table->boolean('c_moon_down')->default(false);
                $table->decimal('c_hfd_mean_limit', 4, 2)->nullable();
                $table->integer('c_alt_min')->default(30);
                $table->decimal('c_ha_start', 5, 2)->default(-12);
                $table->decimal('c_ha_end', 5, 2)->default(12);
                $table->string('c_mask')->nullable();

                // Dates
                $table->date('date_start')->nullable();
                $table->date('date_end')->nullable();

                // Repeat settings
                $table->boolean('is_repeat')->default(false);
                $table->integer('repeat_count')->nullable();

                // Status
                $table->enum('status', [
                    'pending',
                    'active',
                    'executing',
                    'completed',
                    'error',
                    'aborted'
                ])->default('pending');

                // Credits
                $table->integer('estimated_credits')->default(0);
                $table->integer('credits_held')->default(0);
                $table->integer('credits_charged')->default(0);

                $table->timestamps();

                // Indexes
                $table->index(['user_id', 'status']);
                $table->index('guid');
                $table->index('set_guid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robo_targets');
    }
};
