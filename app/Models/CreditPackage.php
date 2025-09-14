<?php

// app/Models/CreditPackage.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'credits_amount',
        'price_cents',
        'currency',
        'stripe_price_id',
        'is_active',
        'is_featured',
        'bonus_credits',
        'discount_percentage',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'credits_amount' => 'integer',
        'price_cents' => 'integer',
        'bonus_credits' => 'integer',
        'discount_percentage' => 'integer',
        'sort_order' => 'integer'
    ];

    // Relations
    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price_cents');
    }

    // Accessors
    public function getPriceEurosAttribute()
    {
        return $this->price_cents / 100;
    }

    public function getTotalCreditsAttribute()
    {
        return $this->credits_amount + $this->bonus_credits;
    }

    public function getCreditValueAttribute()
    {
        return $this->total_credits > 0 ? round($this->price_euros / $this->total_credits, 3) : 0;
    }

    public function getSavingsPercentageAttribute()
    {
        // Calcul des économies par rapport au pack de base
        $basePackage = static::active()->orderBy('price_cents')->first();
        if (!$basePackage || $basePackage->id === $this->id) {
            return 0;
        }

        $baseValue = $basePackage->credit_value;
        $thisValue = $this->credit_value;

        return $baseValue > 0 ? round((($baseValue - $thisValue) / $baseValue) * 100) : 0;
    }

    // Méthodes
    public function isApplicableForPromotion(Promotion $promotion): bool
    {
        if (!$promotion->applicable_packages) {
            return true;
        }

        $applicableIds = is_string($promotion->applicable_packages)
            ? json_decode($promotion->applicable_packages, true)
            : $promotion->applicable_packages;

        return in_array($this->id, $applicableIds ?? []);
    }

    public function calculateDiscountedPrice(Promotion $promotion = null): array
    {
        $originalPrice = $this->price_cents;
        $discountAmount = 0;
        $bonusCredits = 0;

        if ($promotion && $promotion->isValid() && $this->isApplicableForPromotion($promotion)) {
            switch ($promotion->type) {
                case 'percentage':
                    $discountAmount = round($originalPrice * ($promotion->value / 100));
                    break;
                case 'fixed_amount':
                    $discountAmount = min($promotion->value, $originalPrice);
                    break;
                case 'bonus_credits':
                    $bonusCredits = $promotion->value;
                    break;
            }
        }

        return [
            'original_price' => $originalPrice,
            'discount_amount' => $discountAmount,
            'final_price' => max(0, $originalPrice - $discountAmount),
            'bonus_credits' => $bonusCredits + $this->bonus_credits,
            'total_credits' => $this->credits_amount + $bonusCredits + $this->bonus_credits
        ];
    }
}
