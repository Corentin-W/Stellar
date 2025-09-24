<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Carbon\Carbon;

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

    protected $attributes = [
        'metadata' => '[]'
    ];

    // Constantes pour les types de transactions
    const TYPE_PURCHASE = 'purchase';
    const TYPE_USAGE = 'usage';
    const TYPE_REFUND = 'refund';
    const TYPE_BONUS = 'bonus';
    const TYPE_ADMIN_ADJUSTMENT = 'admin_adjustment';

    const TYPES = [
        self::TYPE_PURCHASE => 'Achat',
        self::TYPE_USAGE => 'Utilisation',
        self::TYPE_REFUND => 'Remboursement',
        self::TYPE_BONUS => 'Bonus',
        self::TYPE_ADMIN_ADJUSTMENT => 'Ajustement Admin'
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

    public function reference(): MorphTo
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
        return $query->where('type', self::TYPE_PURCHASE);
    }

    public function scopeUsage($query)
    {
        return $query->where('type', self::TYPE_USAGE);
    }

    public function scopeRefunds($query)
    {
        return $query->where('type', self::TYPE_REFUND);
    }

    public function scopeBonus($query)
    {
        return $query->where('type', self::TYPE_BONUS);
    }

    public function scopeAdminAdjustments($query)
    {
        return $query->where('type', self::TYPE_ADMIN_ADJUSTMENT);
    }

    public function scopeAdditions($query)
    {
        return $query->whereIn('type', [self::TYPE_PURCHASE, self::TYPE_BONUS])
                    ->orWhere(function ($q) {
                        $q->where('type', self::TYPE_ADMIN_ADJUSTMENT)
                          ->where('credits_amount', '>', 0);
                    });
    }

    public function scopeDeductions($query)
    {
        return $query->where('type', self::TYPE_USAGE)
                    ->orWhere(function ($q) {
                        $q->where('type', self::TYPE_ADMIN_ADJUSTMENT)
                          ->where('credits_amount', '<', 0);
                    });
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    public function scopeBetweenDates($query, Carbon $start, Carbon $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ]);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeWithStripePayment($query)
    {
        return $query->whereNotNull('stripe_payment_intent_id');
    }

    public function scopeByAmountRange($query, int $min, int $max)
    {
        return $query->whereBetween('credits_amount', [$min, $max]);
    }

    public function scopeHighValue($query, int $threshold = 1000)
    {
        return $query->where('credits_amount', '>=', $threshold);
    }

    public function scopeOrderByAmount($query, $direction = 'desc')
    {
        return $query->orderBy('credits_amount', $direction);
    }

    // Accessors
    public function getIsAdditionAttribute(): bool
    {
        return in_array($this->type, [self::TYPE_PURCHASE, self::TYPE_BONUS])
               || ($this->type === self::TYPE_ADMIN_ADJUSTMENT && $this->credits_amount > 0);
    }

    public function getIsDeductionAttribute(): bool
    {
        return $this->type === self::TYPE_USAGE
               || ($this->type === self::TYPE_ADMIN_ADJUSTMENT && $this->credits_amount < 0);
    }

    public function getFormattedTypeAttribute(): string
    {
        return self::TYPES[$this->type] ?? ucfirst(str_replace('_', ' ', $this->type));
    }

    public function getFormattedAmountAttribute(): string
    {
        $prefix = $this->is_addition ? '+' : '';
        return $prefix . number_format(abs($this->credits_amount));
    }

    public function getFormattedBalanceBeforeAttribute(): string
    {
        return number_format($this->balance_before);
    }

    public function getFormattedBalanceAfterAttribute(): string
    {
        return number_format($this->balance_after);
    }

    public function getAmountColorClassAttribute(): string
    {
        return $this->is_addition ? 'text-green-400' : 'text-red-400';
    }

    public function getTypeColorClassAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PURCHASE => 'text-blue-400',
            self::TYPE_USAGE => 'text-orange-400',
            self::TYPE_REFUND => 'text-purple-400',
            self::TYPE_BONUS => 'text-green-400',
            self::TYPE_ADMIN_ADJUSTMENT => 'text-yellow-400',
            default => 'text-gray-400'
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            self::TYPE_PURCHASE => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1',
            self::TYPE_USAGE => 'M13 10V3L4 14h7v7l9-11h-7z',
            self::TYPE_REFUND => 'M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6',
            self::TYPE_BONUS => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
            self::TYPE_ADMIN_ADJUSTMENT => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z',
            default => 'M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2'
        };
    }

    public function getBalanceChangeAttribute(): int
    {
        return $this->balance_after - $this->balance_before;
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d/m/Y à H:i');
    }

    public function getShortStripeIdAttribute(): ?string
    {
        return $this->stripe_payment_intent_id
            ? 'pi_' . substr($this->stripe_payment_intent_id, 3, 8) . '...'
            : null;
    }

    public function getEstimatedValueAttribute(): float
    {
        if ($this->creditPackage && $this->type === self::TYPE_PURCHASE) {
            return $this->creditPackage->price_euros;
        }

        // Estimation basée sur une valeur moyenne par crédit
        $averageCreditValue = 0.01; // 1 centime par crédit (à ajuster)
        return abs($this->credits_amount) * $averageCreditValue;
    }

    // Méthodes métier
    public function canBeRefunded(): bool
    {
        return $this->type === self::TYPE_PURCHASE
            && $this->stripe_payment_intent_id
            && $this->created_at->diffInHours(now()) <= 24; // Remboursable sous 24h
    }

    public function getRefundableAmount(): int
    {
        if (!$this->canBeRefunded()) {
            return 0;
        }

        // Vérifier que l'utilisateur a encore assez de crédits
        $currentBalance = $this->user->credits_balance;
        return min($this->credits_amount, $currentBalance);
    }

    public function createRefund(string $reason, ?int $adminId = null): ?self
    {
        if (!$this->canBeRefunded()) {
            return null;
        }

        $refundableAmount = $this->getRefundableAmount();
        if ($refundableAmount <= 0) {
            return null;
        }

        $user = $this->user;
        $oldBalance = $user->credits_balance;
        $newBalance = $oldBalance - $refundableAmount;

        // Mettre à jour le solde utilisateur
        $user->update(['credits_balance' => $newBalance]);

        // Créer la transaction de remboursement
        return self::create([
            'user_id' => $user->id,
            'type' => self::TYPE_REFUND,
            'credits_amount' => -$refundableAmount,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'description' => "Remboursement: {$reason}",
            'reference_type' => self::class,
            'reference_id' => $this->id,
            'stripe_payment_intent_id' => $this->stripe_payment_intent_id,
            'created_by' => $adminId,
            'metadata' => [
                'refund_reason' => $reason,
                'original_transaction_id' => $this->id,
                'refund_type' => 'partial',
                'admin_id' => $adminId
            ]
        ]);
    }

    public function isBalanceConsistent(): bool
    {
        return $this->balance_change === $this->credits_amount;
    }

    public function getRelatedTransactions()
    {
        $related = collect();

        // Transaction originale si c'est un remboursement
        if ($this->type === self::TYPE_REFUND && $this->reference_type === self::class) {
            $original = self::find($this->reference_id);
            if ($original) {
                $related->push($original);
            }
        }

        // Remboursements de cette transaction
        if ($this->type === self::TYPE_PURCHASE) {
            $refunds = self::where('reference_type', self::class)
                          ->where('reference_id', $this->id)
                          ->where('type', self::TYPE_REFUND)
                          ->get();
            $related = $related->concat($refunds);
        }

        return $related;
    }

    public function hasMetadata(string $key): bool
    {
        return isset($this->metadata[$key]);
    }

    public function getMetadata(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    public function setMetadata(string $key, $value): bool
    {
        $metadata = $this->metadata;
        $metadata[$key] = $value;
        return $this->update(['metadata' => $metadata]);
    }

    public function addMetadata(array $data): bool
    {
        $metadata = array_merge($this->metadata, $data);
        return $this->update(['metadata' => $metadata]);
    }

    // Méthodes statiques utiles
    public static function getTotalCreditsForUser(int $userId): int
    {
        return static::forUser($userId)->sum('credits_amount');
    }

    public static function getTotalSpentByUser(int $userId): float
    {
        return static::forUser($userId)
                    ->purchases()
                    ->with('creditPackage')
                    ->get()
                    ->sum(function ($transaction) {
                        return $transaction->creditPackage?->price_euros ?? 0;
                    });
    }

    public static function getStatsForPeriod(Carbon $start, Carbon $end): array
    {
        $transactions = static::betweenDates($start, $end)->get();

        return [
            'total_transactions' => $transactions->count(),
            'total_credits_purchased' => $transactions->where('type', self::TYPE_PURCHASE)->sum('credits_amount'),
            'total_credits_used' => abs($transactions->where('type', self::TYPE_USAGE)->sum('credits_amount')),
            'total_revenue' => $transactions->where('type', self::TYPE_PURCHASE)
                                          ->with('creditPackage')
                                          ->sum(function ($transaction) {
                                              return $transaction->creditPackage?->price_euros ?? 0;
                                          }),
            'unique_users' => $transactions->pluck('user_id')->unique()->count(),
            'avg_transaction_size' => $transactions->where('credits_amount', '>', 0)->avg('credits_amount')
        ];
    }

    public static function getTopUsers(int $limit = 10, int $days = 30): \Illuminate\Database\Eloquent\Collection
    {
        return static::with('user')
                    ->recent($days)
                    ->selectRaw('user_id, SUM(credits_amount) as total_credits, COUNT(*) as transaction_count')
                    ->groupBy('user_id')
                    ->orderBy('total_credits', 'desc')
                    ->limit($limit)
                    ->get();
    }

    public static function getDailyStats(int $days = 30): \Illuminate\Support\Collection
    {
        return static::selectRaw('DATE(created_at) as date,
                                 COUNT(*) as transactions,
                                 SUM(CASE WHEN type = "purchase" THEN credits_amount ELSE 0 END) as credits_purchased,
                                 SUM(CASE WHEN type = "usage" THEN ABS(credits_amount) ELSE 0 END) as credits_used')
                    ->where('created_at', '>=', now()->subDays($days))
                    ->groupBy('date')
                    ->orderBy('date')
                    ->get();
    }

    // Validation et contraintes
    public static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            // Validation des contraintes métier
            if (!in_array($transaction->type, array_keys(self::TYPES))) {
                throw new \InvalidArgumentException('Type de transaction invalide');
            }

            if ($transaction->balance_after !== ($transaction->balance_before + $transaction->credits_amount)) {
                throw new \InvalidArgumentException('Calcul du solde incohérent');
            }

            // Vérification que le solde ne devient pas négatif (sauf pour les ajustements admin)
            if ($transaction->balance_after < 0 && $transaction->type !== self::TYPE_ADMIN_ADJUSTMENT) {
                throw new \InvalidArgumentException('Le solde ne peut pas être négatif');
            }
        });

        static::created(function ($transaction) {
            // Log des transactions importantes
            if (abs($transaction->credits_amount) >= 1000) {
                \Log::info('Large credit transaction created', [
                    'transaction_id' => $transaction->id,
                    'user_id' => $transaction->user_id,
                    'type' => $transaction->type,
                    'amount' => $transaction->credits_amount
                ]);
            }
        });
    }

    // Export et conversion
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'formatted_type' => $this->formatted_type,
            'credits_amount' => $this->credits_amount,
            'formatted_amount' => $this->formatted_amount,
            'balance_before' => $this->balance_before,
            'balance_after' => $this->balance_after,
            'description' => $this->description,
            'created_at' => $this->created_at->toISOString(),
            'formatted_date' => $this->formatted_date,
            'is_addition' => $this->is_addition,
            'package' => $this->creditPackage?->toApiArray()
        ];
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
              ->orWhere('stripe_payment_intent_id', 'like', "%{$term}%")
              ->orWhereHas('user', function ($userQuery) use ($term) {
                  $userQuery->where('name', 'like', "%{$term}%")
                           ->orWhere('email', 'like', "%{$term}%");
              });
        });
    }
}
