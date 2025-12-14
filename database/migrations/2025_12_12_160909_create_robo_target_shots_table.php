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
        if (!Schema::hasTable('robo_target_shots')) {
            Schema::create('robo_target_shots', function (Blueprint $table) {
                $table->id();
                $table->foreignId('robo_target_id')->constrained()->cascadeOnDelete();

                // Filter settings
                $table->integer('filter_index'); // Index du filtre dans la roue
                $table->string('filter_name'); // Nom du filtre (Ha, OIII, Lum, etc.)

                // Shot parameters
                $table->integer('exposure'); // Durée d'exposition en secondes
                $table->integer('num'); // Nombre de poses
                $table->integer('gain')->default(100);
                $table->integer('offset')->default(50);
                $table->integer('bin')->default(1);
                $table->integer('type')->default(0); // 0=LIGHT, 1=DARK, 2=FLAT, 3=BIAS

                // Order
                $table->integer('order')->default(0);

                $table->timestamps();

                // Indexes
                $table->index('robo_target_id');
                $table->index(['robo_target_id', 'order']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('robo_target_shots');
    }
};
