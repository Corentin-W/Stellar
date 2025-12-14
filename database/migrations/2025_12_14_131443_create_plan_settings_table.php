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
        Schema::create('plan_settings', function (Blueprint $table) {
            $table->id();
            $table->string('plan')->unique(); // stardust, nebula, quasar
            $table->string('name'); // Nom affiché
            $table->decimal('price', 8, 2); // Prix en euros
            $table->integer('credits_per_month'); // Crédits mensuels
            $table->integer('trial_days')->default(0); // Jours de gratuité
            $table->decimal('discount_percentage', 5, 2)->default(0); // Réduction %
            $table->boolean('is_active')->default(true); // Plan actif ou non
            $table->string('stripe_price_id')->nullable(); // Stripe Price ID
            $table->timestamps();
        });

        // Insérer les valeurs par défaut
        DB::table('plan_settings')->insert([
            [
                'plan' => 'stardust',
                'name' => 'Stardust',
                'price' => 29,
                'credits_per_month' => 20,
                'trial_days' => 0,
                'discount_percentage' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan' => 'nebula',
                'name' => 'Nebula',
                'price' => 59,
                'credits_per_month' => 60,
                'trial_days' => 0,
                'discount_percentage' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'plan' => 'quasar',
                'name' => 'Quasar',
                'price' => 119,
                'credits_per_month' => 150,
                'trial_days' => 0,
                'discount_percentage' => 0,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plan_settings');
    }
};
