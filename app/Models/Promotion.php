<?php

// app/Models/Promotion.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Promotion extends Model
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'type',
        'value',
        'is_active',
        'usage_limit',
        'usage_count',
        'user_limit',
        'applicable_packages',
        'minimum_purchase',
        'starts_at',
        'expires_at'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'value' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'user_limit' => 'integer',
        'minimum_purchase' => 'integer',
        'applicable_packages' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime'
    ];

    const TYPES = [
        'percentage' => 'Pourcentage de réduction',
        'fixed_amount' => 'Montant fixe',
        'bonus_credits' => 'Crédits bonus'
    ];

    // Relations
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
                    ->withPivot('usage_count', 'first_used_at', 'last_used_at')
                    ->withTimestamps();
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class, 'reference_id')
                    ->where('reference_type', self::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        $now = now();
        return $query->active()
                    ->where(function($q) use ($now) {
                        $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
                    })
                    ->where(function($q) use ($now) {
                        $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
                    })
                    ->where(function($q) {
                        $q->whereNull('usage_limit')
                          ->orWhereColumn('usage_count', '<', 'usage_limit');
                    });
    }

    public function scopeByCode($query, $code)
    {
        return $query->where('code', strtoupper($code));
    }

    // Accessors & Mutators
    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = strtoupper($value);
    }

    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->lt($now)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function getUsageRemainingAttribute(): ?int
    {
        if (!$this->usage_limit) {
            return null;
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    public function getFormattedTypeAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst($this->type);
    }

    public function getFormattedValueAttribute(): string
    {
        return match($this->type) {
            'percentage' => $this->value . '%',
            'fixed_amount' => number_format($this->value / 100, 2) . '€',
            'bonus_credits' => '+' . number_format($this->value) . ' crédits',
            default => (string) $this->value
        };
    }

    // Méthodes principales
    public function canBeUsedBy(User $user): bool
    {
        if (!$this->is_valid) {
            return false;
        }

        if ($this->user_limit) {
            $userUsage = $this->users()->where('user_id', $user->id)->first();
            if ($userUsage && $userUsage->pivot->usage_count >= $this->user_limit) {
                return false;
            }
        }

        return true;
    }

    public function isApplicableToPackage(CreditPackage $package): bool
    {
        if (!$this->applicable_packages) {
            return true; // Applicable à tous les packages
        }

        return in_array($package->id, $this->applicable_packages);
    }

    public function calculateDiscount(CreditPackage $package): array
    {
        if (!$this->isApplicableToPackage($package)) {
            return [
                'discount_amount' => 0,
                'final_price' => $package->price_cents,
                'bonus_credits' => 0
            ];
        }

        $discountAmount = 0;
        $bonusCredits = 0;
        $finalPrice = $package->price_cents;

        switch ($this->type) {
            case 'percentage':
                $discountAmount = round($package->price_cents * ($this->value / 100));
                $finalPrice = $package->price_cents - $discountAmount;
                break;

            case 'fixed_amount':
                $discountAmount = min($this->value * 100, $package->price_cents); // Éviter les prix négatifs
                $finalPrice = $package->price_cents - $discountAmount;
                break;

            case 'bonus_credits':
                $bonusCredits = (int) $this->value;
                break;
        }

        return [
            'discount_amount' => $discountAmount,
            'final_price' => max(0, $finalPrice),
            'bonus_credits' => $bonusCredits
        ];
    }

    public function recordUsage(User $user): void
    {
        // Incrémenter le compteur global
        $this->increment('usage_count');

        // Enregistrer/mettre à jour l'utilisation par l'utilisateur
        $this->users()->syncWithoutDetaching([
            $user->id => [
                'usage_count' => $this->users()->where('user_id', $user->id)->exists()
                    ? $this->users()->where('user_id', $user->id)->first()->pivot->usage_count + 1
                    : 1,
                'first_used_at' => $this->users()->where('user_id', $user->id)->exists()
                    ? $this->users()->where('user_id', $user->id)->first()->pivot->first_used_at
                    : now(),
                'last_used_at' => now(),
                'updated_at' => now()
            ]
        ]);
    }

    public function getValidationMessage(): ?string
    {
        if (!$this->is_active) {
            return 'Ce code promo n\'est plus actif';
        }

        $now = now();

        if ($this->starts_at && $this->starts_at->gt($now)) {
            return 'Ce code promo n\'est pas encore valide';
        }

        if ($this->expires_at && $this->expires_at->lt($now)) {
            return 'Ce code promo a expiré';
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return 'Ce code promo a atteint sa limite d\'utilisation';
        }

        return null;
    }
}
