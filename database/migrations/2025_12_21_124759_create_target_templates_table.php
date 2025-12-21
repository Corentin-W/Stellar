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
        Schema::create('target_templates', function (Blueprint $table) {
            $table->id();
            $table->string('template_id')->unique(); // ex: m42, m31, etc.
            $table->string('name'); // ex: M42 - Grande Nébuleuse d'Orion
            $table->string('type'); // ex: Nébuleuse, Galaxie, Amas Globulaire
            $table->string('constellation');
            $table->enum('difficulty', ['beginner', 'intermediate', 'advanced'])->default('beginner');

            // Descriptions et présentation
            $table->text('short_description'); // Description courte pour le catalogue
            $table->longText('full_description')->nullable(); // Description complète avec détails
            $table->text('tips')->nullable(); // Conseils pour photographier

            // Images
            $table->string('preview_image')->nullable(); // Image de preview principale
            $table->string('thumbnail_image')->nullable(); // Miniature pour le catalogue
            $table->json('gallery_images')->nullable(); // Galerie d'images supplémentaires

            // Coordonnées
            $table->integer('ra_hours');
            $table->integer('ra_minutes');
            $table->decimal('ra_seconds', 4, 1);
            $table->integer('dec_degrees');
            $table->integer('dec_minutes');
            $table->decimal('dec_seconds', 4, 1);

            // Informations pratiques
            $table->json('best_months'); // ["Nov", "Déc", "Jan"]
            $table->string('estimated_time')->nullable(); // ex: "2h20min"
            $table->json('recommended_shots'); // Configuration des shots recommandés

            // Métadonnées
            $table->boolean('is_active')->default(true);
            $table->integer('display_order')->default(0);
            $table->json('tags')->nullable(); // Tags pour recherche/filtre

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('target_templates');
    }
};
