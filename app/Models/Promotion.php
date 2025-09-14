<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
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
        'min_purchase_amount',
        'max_uses',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
        'applicable_packages'
    ];

    protected $casts = [
        'value' => 'integer',
        'min_purchase_amount' => 'integer',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_packages' => 'array'
    ];

    // Relations
    public function usages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
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

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsStartedAttribute()
    {
        return !$this->starts_at || $this->starts_at->isPast();
    }

    public function getIsUsageExceededAttribute()
    {
        return $this->max_uses && $this->used_count >= $this->max_uses;
    }

    public function getRemainingUsesAttribute()
    {
        return $this->max_uses ? max(0, $this->max_uses - $this->used_count) : null;
    }

    // Méthodes
    public function isValid(): bool
    {
        return $this->is_active
            && $this->is_started
            && !$this->is_expired
            && !$this->is_usage_exceeded;
    }

    public function canBeUsedBy(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Vérifier si l'utilisateur a déjà utilisé ce code
        return !$this->usages()->where('user_id', $user->id)->exists();
    }

    public function isApplicableToPackage(CreditPackage $package): bool
    {
        if (!$this->applicable_packages) {
            return true;
        }

        return in_array($package->id, $this->applicable_packages);
    }

    public function meetsMinimumPurchase(int $amountCents): bool
    {
        return $amountCents >= $this->min_purchase_amount;
    }

    public function calculateDiscount(CreditPackage $package): array
    {
        if (!$this->isApplicableToPackage($package) || !$this->meetsMinimumPurchase($package->price_cents)) {
            return [
                'discount_amount' => 0,
                'bonus_credits' => 0,
                'applicable' => false
            ];
        }

        $discountAmount = 0;
        $bonusCredits = 0;

        switch ($this->type) {
            case 'percentage':
                $discountAmount = round($package->price_cents * ($this->value / 100));
                break;
            case 'fixed_amount':
                $discountAmount = min($this->value, $package->price_cents);
                break;
            case 'bonus_credits':
                $bonusCredits = $this->value;
                break;
        }

        return [
            'discount_amount' => $discountAmount,
            'bonus_credits' => $bonusCredits,
            'applicable' => true
        ];
    }

    public function markAsUsed(User $user, CreditTransaction $transaction = null): PromotionUsage
    {
        $this->increment('used_count');

        return $this->usages()->create([
            'user_id' => $user->id,
            'credit_transaction_id' => $transaction?->id,
            'discount_amount' => 0, // Sera mis à jour selon le contexte
        ]);
    }
}
