<?php

namespace App\Services;

use App\Models\User;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\Promotion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditService
{
    public function __construct(
        private StripeService $stripeService
    ) {}

    /**
     * Obtenir les recommandations pour un utilisateur
     * Méthode principale appelée par le contrôleur
     */
    public function getRecommendations(User $user): array
    {
        return $this->getPackageRecommendations($user);
    }

    /**
     * Recommandations de packages pour un utilisateur
     */
    public function getPackageRecommendations(User $user): array
    {
        $stats = $user->getCreditStats();
        $recommendations = [];

        // Si l'utilisateur n'a jamais acheté de crédits
        if ($stats['total_purchased'] == 0) {
            $recommendations[] = [
                'type' => 'first_time',
                'packages' => CreditPackage::active()->where('credits_amount', '<=', 200)->ordered()->take(3)->get(),
                'reason' => 'Idéal pour débuter - Petits packages pour tester'
            ];
        } else {
            // Calculer l'usage mensuel moyen
            $monthlyUsage = $this->calculateMonthlyUsage($user);

            if ($monthlyUsage > 500) {
                $recommendations[] = [
                    'type' => 'heavy_user',
                    'packages' => CreditPackage::active()->where('credits_amount', '>=', 1000)->ordered()->take(3)->get(),
                    'reason' => 'Pour votre usage intensif - Meilleure valeur'
                ];
            } elseif ($monthlyUsage > 100) {
                $recommendations[] = [
                    'type' => 'regular_user',
                    'packages' => CreditPackage::active()->whereBetween('credits_amount', [200, 1000])->ordered()->take(3)->get(),
                    'reason' => 'Parfait pour votre utilisation régulière'
                ];
            } else {
                $recommendations[] = [
                    'type' => 'light_user',
                    'packages' => CreditPackage::active()->where('credits_amount', '<=', 500)->ordered()->take(3)->get(),
                    'reason' => 'Adapté à votre usage occasionnel'
                ];
            }
        }

        // Packages populaires
        $popularPackages = CreditPackage::active()->featured()->ordered()->get();
        if ($popularPackages->count() > 0) {
            $recommendations[] = [
                'type' => 'popular',
                'packages' => $popularPackages,
                'reason' => 'Les plus populaires - Choix de la communauté'
            ];
        }

        // Packages avec promotions actives
        $activePromotions = Promotion::valid()->get();
        if ($activePromotions->count() > 0) {
            $promotionPackages = CreditPackage::active()->ordered()->take(3)->get();
            $recommendations[] = [
                'type' => 'promotion',
                'packages' => $promotionPackages,
                'reason' => 'Offres spéciales disponibles',
                'promotions' => $activePromotions
            ];
        }

        return $recommendations;
    }

    /**
     * Calculer l'usage mensuel moyen d'un utilisateur
     */
    private function calculateMonthlyUsage(User $user): int
    {
        $usageTransactions = $user->creditTransactions()
                                 ->where('type', 'usage')
                                 ->where('created_at', '>=', now()->subMonths(3))
                                 ->get();

        if ($usageTransactions->isEmpty()) {
            return 0;
        }

        $totalUsage = abs($usageTransactions->sum('credits_amount'));
        $months = max(1, now()->diffInMonths($usageTransactions->first()->created_at));

        return (int) ($totalUsage / $months);
    }

    /**
     * Valider un code promotionnel
     */
    public function validatePromotion(string $code, User $user, CreditPackage $package): array
    {
        $promotion = Promotion::byCode($code)->first();

        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Code promotionnel invalide'
            ];
        }

        if (!$promotion->is_valid) {
            return [
                'valid' => false,
                'message' => $promotion->getValidationMessage() ?? 'Code promotionnel invalide'
            ];
        }

        if (!$promotion->canBeUsedBy($user)) {
            return [
                'valid' => false,
                'message' => 'Vous avez déjà utilisé ce code promotionnel'
            ];
        }

        if (!$promotion->isApplicableToPackage($package)) {
            return [
                'valid' => false,
                'message' => 'Ce code n\'est pas applicable à ce package'
            ];
        }

        $discount = $promotion->calculateDiscount($package);

        return [
            'valid' => true,
            'message' => 'Code promotionnel appliqué !',
            'promotion' => [
                'code' => $promotion->code,
                'name' => $promotion->name,
                'type' => $promotion->type,
                'formatted_value' => $promotion->formatted_value ?? $promotion->value,
                'discount_amount' => $discount['discount_amount'],
                'final_price' => $discount['final_price'],
                'bonus_credits' => $discount['bonus_credits']
            ]
        ];
    }

    /**
     * Estimer le coût d'une session
     */
    public function estimateSessionCost(string $type = 'observation', int $minutes = 30, string $complexity = 'medium'): int
    {
        // Coûts de base par type d'activité
        $baseCosts = [
            'observation' => 2,    // 2 crédits par minute
            'imaging' => 5,        // 5 crédits par minute
            'spectroscopy' => 8,   // 8 crédits par minute
            'photometry' => 3,     // 3 crédits par minute
        ];

        // Multiplicateurs selon la complexité
        $complexityMultipliers = [
            'low' => 0.8,
            'medium' => 1.0,
            'high' => 1.5,
            'expert' => 2.0
        ];

        $baseCost = $baseCosts[$type] ?? $baseCosts['observation'];
        $multiplier = $complexityMultipliers[$complexity] ?? $complexityMultipliers['medium'];

        // Coût minimum de 10 crédits
        return max(10, (int) ceil($baseCost * $minutes * $multiplier));
    }

    /**
     * Calculer le prix final avec promotion
     */
    public function calculateFinalPrice(CreditPackage $package, ?Promotion $promotion = null): array
    {
        if (!$promotion) {
            return [
                'original_price' => $package->price_cents,
                'final_price' => $package->price_cents,
                'discount_amount' => 0,
                'bonus_credits' => 0,
                'total_credits' => $package->total_credits
            ];
        }

        $discount = $promotion->calculateDiscount($package);

        return [
            'original_price' => $package->price_cents,
            'final_price' => $discount['final_price'],
            'discount_amount' => $discount['discount_amount'],
            'bonus_credits' => $discount['bonus_credits'],
            'total_credits' => $package->total_credits + $discount['bonus_credits']
        ];
    }

    /**
     * Acheter des crédits avec Stripe
     */
    public function purchaseCredits(User $user, CreditPackage $package, ?Promotion $promotion = null): array
    {
        if (!$package->is_active) {
            throw new \Exception('Ce package n\'est plus disponible');
        }

        $pricing = $this->calculateFinalPrice($package, $promotion);

        DB::beginTransaction();
        try {
            // Créer le Payment Intent Stripe
            $paymentResult = $this->stripeService->createPaymentIntent($user, $package, $promotion);

            if (!$paymentResult['success']) {
                throw new \Exception($paymentResult['error']);
            }

            DB::commit();

            return [
                'success' => true,
                'payment_intent' => $paymentResult['payment_intent_id'],
                'client_secret' => $paymentResult['client_secret'],
                'amount' => $paymentResult['amount'],
                'credits_to_receive' => $pricing['total_credits']
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Credit purchase failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Confirmer l'achat après paiement Stripe
     */
    public function confirmPurchase(string $paymentIntentId): array
    {
        return $this->stripeService->confirmPayment($paymentIntentId);
    }

    /**
     * Obtenir les statistiques d'usage d'un utilisateur
     */
    public function getUserStats(User $user, int $days = 30): array
    {
        $transactions = $user->creditTransactions()
                            ->where('created_at', '>=', now()->subDays($days))
                            ->get();

        $purchased = $transactions->where('type', 'purchase')->sum('credits_amount');
        $used = abs($transactions->where('type', 'usage')->sum('credits_amount'));

        return [
            'current_balance' => $user->credits_balance,
            'purchased_period' => $purchased,
            'used_period' => $used,
            'transactions_count' => $transactions->count(),
            'avg_daily_usage' => $days > 0 ? round($used / $days, 1) : 0,
            'efficiency_rate' => $purchased > 0 ? round(($used / $purchased) * 100, 1) : 0
        ];
    }

    /**
     * Vérifier si un utilisateur peut effectuer une action
     */
    public function canUserAfford(User $user, int $requiredCredits): array
    {
        $hasEnough = $user->hasEnoughCredits($requiredCredits);
        $currentBalance = $user->credits_balance;

        return [
            'can_afford' => $hasEnough,
            'current_balance' => $currentBalance,
            'required_credits' => $requiredCredits,
            'missing_credits' => $hasEnough ? 0 : $requiredCredits - $currentBalance,
            'suggested_package' => $hasEnough ? null : $this->getSuggestedPackage($requiredCredits - $currentBalance)
        ];
    }

    /**
     * Suggérer un package basé sur les crédits manquants
     */
    private function getSuggestedPackage(int $missingCredits): ?CreditPackage
    {
        return CreditPackage::active()
                           ->where('credits_amount', '>=', $missingCredits)
                           ->orderBy('credits_amount')
                           ->first();
    }

    /**
     * Gérer l'utilisation des crédits
     */
    public function consumeCredits(User $user, int $amount, string $description, $reference = null): bool
    {
        if (!$user->hasEnoughCredits($amount)) {
            return false;
        }

        $transaction = $user->deductCredits($amount, $description);

        if ($reference) {
            $transaction->update([
                'reference_type' => get_class($reference),
                'reference_id' => $reference->id
            ]);
        }

        return true;
    }

    /**
     * Obtenir l'historique détaillé d'un utilisateur
     */
    public function getUserHistory(User $user, int $limit = 50): array
    {
        $transactions = $user->creditTransactions()
                            ->with(['creditPackage'])
                            ->orderBy('created_at', 'desc')
                            ->limit($limit)
                            ->get();

        $stats = [
            'total_purchased' => $user->creditTransactions()->where('type', 'purchase')->sum('credits_amount'),
            'total_used' => abs($user->creditTransactions()->where('type', 'usage')->sum('credits_amount')),
            'current_balance' => $user->credits_balance,
            'transaction_count' => $user->creditTransactions()->count(),
            'first_purchase' => $user->creditTransactions()->where('type', 'purchase')->orderBy('created_at')->first()?->created_at,
            'last_activity' => $user->creditTransactions()->orderBy('created_at', 'desc')->first()?->created_at
        ];

        return [
            'transactions' => $transactions,
            'stats' => $stats,
            'summary' => $this->generateUserSummary($user, $stats)
        ];
    }

    /**
     * Générer un résumé pour l'utilisateur
     */
    private function generateUserSummary(User $user, array $stats): array
    {
        $level = 'Débutant';
        $badgeClass = 'bg-gray-500';

        if ($stats['total_purchased'] >= 5000) {
            $level = 'Expert';
            $badgeClass = 'bg-purple-500';
        } elseif ($stats['total_purchased'] >= 2000) {
            $level = 'Avancé';
            $badgeClass = 'bg-blue-500';
        } elseif ($stats['total_purchased'] >= 500) {
            $level = 'Intermédiaire';
            $badgeClass = 'bg-green-500';
        }

        return [
            'level' => $level,
            'badge_class' => $badgeClass,
            'usage_efficiency' => $stats['total_purchased'] > 0 ? round(($stats['total_used'] / $stats['total_purchased']) * 100) : 0,
            'monthly_average' => $stats['first_purchase'] ? round($stats['total_used'] / max(1, now()->diffInMonths($stats['first_purchase']))) : 0
        ];
    }
}
