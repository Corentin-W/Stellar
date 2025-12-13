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
        Schema::create('robo_target_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('robo_target_id')->constrained()->cascadeOnDelete();

            // Session info from Voyager
            $table->uuid('session_guid')->nullable();
            $table->timestamp('session_start')->nullable();
            $table->timestamp('session_end')->nullable();

            // Results
            $table->integer('result')->nullable(); // 1=OK, 2=Aborted, 3=Error
            $table->string('result_text')->nullable();

            // Quality metrics
            $table->decimal('hfd_mean', 4, 2)->nullable();
            $table->decimal('hfd_stdev', 4, 2)->nullable();
            $table->integer('images_captured')->default(0);
            $table->integer('images_accepted')->default(0);
            $table->integer('images_rejected')->default(0);

            // Raw data from Voyager (JSON)
            $table->json('raw_data')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('robo_target_id');
            $table->index('session_guid');
            $table->index(['robo_target_id', 'result']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robo_target_sessions');
    }
};
