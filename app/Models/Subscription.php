<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    // Plans disponibles
    const STARDUST = 'stardust';
    const NEBULA = 'nebula';
    const QUASAR = 'quasar';

    // Crédits par plan
    const CREDITS_PER_PLAN = [
        self::STARDUST => 20,
        self::NEBULA => 60,
        self::QUASAR => 150,
    ];

    // Tarifs par plan (en euros)
    const PRICES = [
        self::STARDUST => 29,
        self::NEBULA => 59,
        self::QUASAR => 119,
    ];

    protected $fillable = [
        'user_id',
        'plan',
        'credits_per_month',
        'status',
        'type',
        'stripe_id',
        'stripe_status',
        'stripe_price',
        'quantity',
        'trial_ends_at',
        'ends_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Getters
     */
    public function getPlanName(): string
    {
        return match($this->plan) {
            self::STARDUST => 'Stardust',
            self::NEBULA => 'Nebula',
            self::QUASAR => 'Quasar',
            default => 'Unknown'
        };
    }

    public function getPlanBadge(): string
    {
        return match($this->plan) {
            self::STARDUST => '🌟',
            self::NEBULA => '🌌',
            self::QUASAR => '⚡',
            default => ''
        };
    }

    /**
     * Permissions - Priority
     */
    public function canUsePriority(int $priority): bool
    {
        return match($this->plan) {
            self::STARDUST => $priority <= 1,
            self::NEBULA => $priority <= 2,
            self::QUASAR => $priority <= 4,
            default => false
        };
    }

    public function getMaxPriority(): int
    {
        return match($this->plan) {
            self::STARDUST => 1,
            self::NEBULA => 2,
            self::QUASAR => 4,
            default => 0
        };
    }

    /**
     * Permissions - Moon Down (Nuit noire)
     */
    public function canUseMoonDown(): bool
    {
        return in_array($this->plan, [self::NEBULA, self::QUASAR]);
    }

    /**
     * Permissions - HFD Guarantee (Garantie netteté)
     */
    public function canAdjustHFD(): bool
    {
        return $this->plan === self::QUASAR;
    }

    public function getDefaultHFDLimit(): ?float
    {
        return match($this->plan) {
            self::STARDUST => null,
            self::NEBULA => 4.0,
            self::QUASAR => 1.5, // Ajustable de 1.5 à 4.0
            default => null
        };
    }

    /**
     * Permissions - Repeat (Projets multi-nuits)
     */
    public function canUseRepeat(): bool
    {
        return in_array($this->plan, [self::NEBULA, self::QUASAR]);
    }

    /**
     * Permissions - Sets (Gestion avancée des sets)
     */
    public function canManageSets(): bool
    {
        return $this->plan === self::QUASAR;
    }

    /**
     * Status checks
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    public function hasEnded(): bool
    {
        return $this->ends_at && $this->ends_at->isPast();
    }
}
