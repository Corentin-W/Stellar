<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionUsage extends Model
{
    protected $fillable = [
        'promotion_id',
        'user_id',
        'credit_transaction_id',
        'discount_amount',
        'used_at'
    ];

    protected $casts = [
        'discount_amount' => 'integer',
        'used_at' => 'datetime'
    ];

    // Relations
    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class);
    }
}
