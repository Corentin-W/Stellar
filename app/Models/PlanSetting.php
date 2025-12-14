<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlanSetting extends Model
{
    protected $fillable = [
        'plan',
        'name',
        'price',
        'credits_per_month',
        'trial_days',
        'discount_percentage',
        'is_active',
        'stripe_price_id',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the final price after discount
     */
    public function getFinalPrice(): float
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - ($this->discount_percentage / 100));
        }
        return (float) $this->price;
    }

    /**
     * Get discount amount
     */
    public function getDiscountAmount(): float
    {
        if ($this->discount_percentage > 0) {
            return $this->price * ($this->discount_percentage / 100);
        }
        return 0;
    }

    /**
     * Check if plan has active discount
     */
    public function hasDiscount(): bool
    {
        return $this->discount_percentage > 0;
    }

    /**
     * Get plan by slug
     */
    public static function getByPlan(string $plan): ?self
    {
        return self::where('plan', $plan)->first();
    }
}
