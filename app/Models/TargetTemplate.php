<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetTemplate extends Model
{
    protected $fillable = [
        'template_id',
        'name',
        'type',
        'constellation',
        'difficulty',
        'short_description',
        'full_description',
        'tips',
        'preview_image',
        'thumbnail_image',
        'gallery_images',
        'ra_hours',
        'ra_minutes',
        'ra_seconds',
        'dec_degrees',
        'dec_minutes',
        'dec_seconds',
        'best_months',
        'estimated_time',
        'recommended_shots',
        'is_active',
        'display_order',
        'tags',
    ];

    protected $casts = [
        'best_months' => 'array',
        'recommended_shots' => 'array',
        'gallery_images' => 'array',
        'tags' => 'array',
        'is_active' => 'boolean',
        'ra_seconds' => 'decimal:1',
        'dec_seconds' => 'decimal:1',
    ];

    /**
     * Scope for active templates only
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope by difficulty
     */
    public function scopeByDifficulty($query, string $difficulty)
    {
        return $query->where('difficulty', $difficulty);
    }

    /**
     * Get formatted RA string
     */
    public function getFormattedRaAttribute(): string
    {
        return sprintf('%02d:%02d:%04.1f', $this->ra_hours, $this->ra_minutes, $this->ra_seconds);
    }

    /**
     * Get formatted DEC string
     */
    public function getFormattedDecAttribute(): string
    {
        $sign = $this->dec_degrees >= 0 ? '+' : '-';
        return sprintf('%s%02d:%02d:%04.1f', $sign, abs($this->dec_degrees), $this->dec_minutes, $this->dec_seconds);
    }

    /**
     * Get difficulty badge color
     */
    public function getDifficultyColorAttribute(): string
    {
        return match($this->difficulty) {
            'beginner' => 'green',
            'intermediate' => 'yellow',
            'advanced' => 'red',
            default => 'gray'
        };
    }

    /**
     * Get difficulty label in French
     */
    public function getDifficultyLabelAttribute(): string
    {
        return match($this->difficulty) {
            'beginner' => 'Débutant',
            'intermediate' => 'Intermédiaire',
            'advanced' => 'Avancé',
            default => $this->difficulty
        };
    }
}
