<?php

// app/Traits/HasCredits.php

namespace App\Traits;

use App\Models\CreditTransaction;
use App\Models\CreditPackage;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasCredits
{
    /**
     * Relation avec les transactions de crédits
     */
    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    /**
     * Relation avec les tickets de support
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(\App\Models\SupportTicket::class);
    }

    /**
     * Ajouter des crédits au solde de l'utilisateur
     */
    public function addCredits(int $amount, string $description = '', array $metadata = []): CreditTransaction
    {
        $oldBalance = $this->credits_balance ?? 0;
        $newBalance = $oldBalance + $amount;

        // Mettre à jour le solde
        $this->update(['credits_balance' => $newBalance]);

        // Créer la transaction
        return CreditTransaction::create([
            'user_id' => $this->id,
            'type' => 'bonus',
            'credits_amount' => $amount,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'description' => $description ?: "Ajout de {$amount} crédits",
            'metadata' => $metadata
        ]);
    }

    /**
     * Déduire des crédits du solde de l'utilisateur
     */
    public function deductCredits(int $amount, string $description = '', array $metadata = []): ?CreditTransaction
    {
        if (!$this->hasEnoughCredits($amount)) {
            return null;
        }

        $oldBalance = $this->credits_balance ?? 0;
        $newBalance = $oldBalance - $amount;

        // Mettre à jour le solde
        $this->update(['credits_balance' => $newBalance]);

        // Créer la transaction
        return CreditTransaction::create([
            'user_id' => $this->id,
            'type' => 'usage',
            'credits_amount' => -$amount, // Négatif pour les déductions
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'description' => $description ?: "Utilisation de {$amount} crédits",
            'metadata' => $metadata
        ]);
    }

    /**
     * Vérifier si l'utilisateur a suffisamment de crédits
     */
    public function hasEnoughCredits(int $amount): bool
    {
        return ($this->credits_balance ?? 0) >= $amount;
    }

    /**
     * Obtenir le solde formaté
     */
    public function getFormattedCreditsBalanceAttribute(): string
    {
        return number_format($this->credits_balance ?? 0);
    }

    /**
     * Obtenir les statistiques de crédits de l'utilisateur
     */
    public function getCreditStats(): array
    {
        $transactions = $this->creditTransactions();

        return [
            'current_balance' => $this->credits_balance ?? 0,
            'total_purchased' => $transactions->where('type', 'purchase')->sum('credits_amount'),
            'total_used' => abs($transactions->where('type', 'usage')->sum('credits_amount')),
            'total_bonus' => $transactions->where('type', 'bonus')->sum('credits_amount'),
            'transaction_count' => $transactions->count(),
            'first_purchase' => $transactions->where('type', 'purchase')->orderBy('created_at')->first(),
            'last_activity' => $transactions->orderBy('created_at', 'desc')->first()
        ];
    }

    /**
     * Obtenir les transactions récentes
     */
    public function getRecentCreditTransactions(int $limit = 10)
    {
        return $this->creditTransactions()
                    ->with('creditPackage')
                    ->orderBy('created_at', 'desc')
                    ->limit($limit)
                    ->get();
    }

    /**
     * Calculer le coût estimé pour une session
     */
    public function estimateSessionCost(string $type = 'text', int $minutes = 30, string $complexity = 'medium'): int
    {
        // Tarification de base (à ajuster selon vos besoins)
        $baseCosts = [
            'text' => 1,     // 1 crédit par minute
            'image' => 5,    // 5 crédits par minute
            'video' => 10,   // 10 crédits par minute
        ];

        $complexityMultipliers = [
            'low' => 0.8,
            'medium' => 1.0,
            'high' => 1.5,
        ];

        $baseCost = $baseCosts[$type] ?? $baseCosts['text'];
        $multiplier = $complexityMultipliers[$complexity] ?? $complexityMultipliers['medium'];

        return (int) ceil($baseCost * $minutes * $multiplier);
    }

    /**
     * Vérifier si l'utilisateur peut effectuer une action nécessitant des crédits
     */
    public function canPerformAction(string $action, array $parameters = []): array
    {
        $estimatedCost = match($action) {
            'text_generation' => $this->estimateSessionCost('text', $parameters['minutes'] ?? 30, $parameters['complexity'] ?? 'medium'),
            'image_generation' => $this->estimateSessionCost('image', $parameters['count'] ?? 1, $parameters['complexity'] ?? 'medium'),
            'video_processing' => $this->estimateSessionCost('video', $parameters['minutes'] ?? 5, $parameters['complexity'] ?? 'medium'),
            default => 10 // Coût par défaut
        };

        $canPerform = $this->hasEnoughCredits($estimatedCost);

        return [
            'can_perform' => $canPerform,
            'estimated_cost' => $estimatedCost,
            'current_balance' => $this->credits_balance ?? 0,
            'missing_credits' => $canPerform ? 0 : $estimatedCost - ($this->credits_balance ?? 0)
        ];
    }

    /**
     * Obtenir les packages recommandés pour l'utilisateur
     */
    public function getRecommendedPackages(): \Illuminate\Database\Eloquent\Collection
    {
        $currentBalance = $this->credits_balance ?? 0;
        $recentUsage = $this->creditTransactions()
                           ->where('type', 'usage')
                           ->where('created_at', '>=', now()->subDays(30))
                           ->sum('credits_amount');

        $monthlyUsage = abs($recentUsage);

        // Logique de recommandation basée sur l'usage
        if ($monthlyUsage > 2000) {
            return CreditPackage::active()->where('credits_amount', '>=', 2000)->ordered()->take(3)->get();
        } elseif ($monthlyUsage > 500) {
            return CreditPackage::active()->whereBetween('credits_amount', [500, 2000])->ordered()->take(3)->get();
        } else {
            return CreditPackage::active()->where('credits_amount', '<=', 500)->ordered()->take(3)->get();
        }
    }

    /**
     * Effectuer un ajustement administrateur des crédits
     */
    public function adminAdjustCredits(int $amount, string $reason, int $adminId): CreditTransaction
    {
        $oldBalance = $this->credits_balance ?? 0;
        $newBalance = $oldBalance + $amount;

        // Mettre à jour le solde
        $this->update(['credits_balance' => $newBalance]);

        // Créer la transaction
        return CreditTransaction::create([
            'user_id' => $this->id,
            'type' => 'admin_adjustment',
            'credits_amount' => $amount,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'description' => "Ajustement administrateur: {$reason}",
            'created_by' => $adminId,
            'metadata' => [
                'adjustment_reason' => $reason,
                'admin_user_id' => $adminId,
                'adjustment_type' => $amount > 0 ? 'credit' : 'debit'
            ]
        ]);
    }

    /**
     * Obtenir l'historique des achats de packages
     */
    public function getPurchaseHistory()
    {
        return $this->creditTransactions()
                    ->where('type', 'purchase')
                    ->with('creditPackage')
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($transaction) {
                        return [
                            'date' => $transaction->created_at,
                            'package' => $transaction->creditPackage,
                            'credits_received' => $transaction->credits_amount,
                            'amount_paid' => $transaction->creditPackage ? $transaction->creditPackage->price_euros : 0,
                            'stripe_payment_id' => $transaction->stripe_payment_intent_id
                        ];
                    });
    }

    /**
     * Calculer le total dépensé par l'utilisateur
     */
    public function getTotalSpentAttribute(): float
    {
        return $this->creditTransactions()
                    ->where('type', 'purchase')
                    ->with('creditPackage')
                    ->get()
                    ->sum(function ($transaction) {
                        return $transaction->creditPackage ? $transaction->creditPackage->price_euros : 0;
                    });
    }

    /**
     * Vérifier si l'utilisateur est un client VIP (basé sur les achats)
     */
    public function isVipCustomer(): bool
    {
        return $this->total_spent >= 100; // VIP si plus de 100€ dépensés
    }

    /**
     * Obtenir le niveau de fidélité de l'utilisateur
     */
    public function getLoyaltyLevel(): array
    {
        $totalSpent = $this->total_spent;
        $totalPurchased = $this->creditTransactions()->where('type', 'purchase')->sum('credits_amount');

        if ($totalSpent >= 500) {
            return ['level' => 'Diamond', 'color' => 'text-purple-400', 'discount' => 15];
        } elseif ($totalSpent >= 200) {
            return ['level' => 'Gold', 'color' => 'text-yellow-400', 'discount' => 10];
        } elseif ($totalSpent >= 50) {
            return ['level' => 'Silver', 'color' => 'text-gray-400', 'discount' => 5];
        } else {
            return ['level' => 'Bronze', 'color' => 'text-orange-400', 'discount' => 0];
        }
    }
}

