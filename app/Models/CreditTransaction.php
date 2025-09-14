<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'credits_amount',
        'balance_before',
        'balance_after',
        'description',
        'reference_type',
        'reference_id',
        'stripe_payment_intent_id',
        'credit_package_id',
        'created_by',
        'metadata'
    ];

    protected $casts = [
        'credits_amount' => 'integer',
        'balance_before' => 'integer',
        'balance_after' => 'integer',
        'reference_id' => 'integer',
        'credit_package_id' => 'integer',
        'created_by' => 'integer',
        'metadata' => 'array'
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditPackage(): BelongsTo
    {
        return $this->belongsTo(CreditPackage::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relation polymorphe pour reference
    public function reference()
    {
        return $this->morphTo('reference', 'reference_type', 'reference_id');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopePurchases($query)
    {
        return $query->where('type', 'purchase');
    }

    public function scopeUsage($query)
    {
        return $query->where('type', 'usage');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    // Accessors
    public function getIsAdditionAttribute()
    {
        return in_array($this->type, ['purchase', 'bonus', 'admin_adjustment']) && $this->credits_amount > 0;
    }

    public function getIsDeductionAttribute()
    {
        return in_array($this->type, ['usage', 'refund', 'admin_adjustment']) && $this->credits_amount < 0;
    }

    public function getFormattedTypeAttribute()
    {
        return match($this->type) {
            'purchase' => 'Achat',
            'usage' => 'Utilisation',
            'refund' => 'Remboursement',
            'bonus' => 'Bonus',
            'admin_adjustment' => 'Ajustement Admin',
            default => ucfirst($this->type)
        };
    }
}

