<?php

// app/Services/CreditService.php

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
     * Acheter un package de crédits
     */
    public function purchaseCredits(
        User $user,
        CreditPackage $package,
        Promotion $promotion = null,
        array $paymentMethodData = []
    ): array {
        // Vérifier la validité du package
        if (!$package->is_active) {
            throw new \Exception('Ce package de crédits n\'est plus disponible');
        }

        // Vérifier et appliquer la promotion
        $promotionData = null;
        if ($promotion) {
            if (!$promotion->canBeUsedBy($user)) {
                throw new \Exception('Cette promotion ne peut pas être utilisée');
            }
            $promotionData = $promotion->calculateDiscount($package);
        }

        // Calculer le prix final
        $pricing = $this->calculateFinalPricing($package, $promotion);

        DB::beginTransaction();
        try {
            // Traitement du paiement Stripe
            $paymentResult = $this->stripeService->processPayment(
                $user,
                $pricing['final_price'],
                $package,
                $promotion,
                $paymentMethodData
            );

            // Créer la transaction de crédits
            $transaction = $user->addCredits(
                $pricing['total_credits'],
                'purchase',
                "Achat de {$package->name}" . ($promotion ? " (Code: {$promotion->code})" : ""),
                null,
                $package
            );

            // Associer le payment intent Stripe
            $transaction->update([
                'stripe_payment_intent_id' => $paymentResult['payment_intent_id']
            ]);

            // Marquer la promotion comme utilisée
            if ($promotion) {
                $promotionUsage = $promotion->markAsUsed($user, $transaction);
                $promotionUsage->update(['discount_amount' => $pricing['discount_amount']]);
            }

            // Log de l'achat
            Log::info('Credit purchase completed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'credits_amount' => $pricing['total_credits'],
                'amount_paid' => $pricing['final_price'],
                'promotion_code' => $promotion?->code,
                'transaction_id' => $transaction->id
            ]);

            DB::commit();

            return [
                'success' => true,
                'transaction' => $transaction,
                'payment_result' => $paymentResult,
                'credits_added' => $pricing['total_credits'],
                'amount_paid' => $pricing['final_price'],
                'new_balance' => $user->fresh()->credits_balance
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Credit purchase failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Calculer le prix final avec promotions
     */
    public function calculateFinalPricing(CreditPackage $package, Promotion $promotion = null): array
    {
        $pricing = [
            'original_price' => $package->price_cents,
            'discount_amount' => 0,
            'final_price' => $package->price_cents,
            'base_credits' => $package->credits_amount,
            'bonus_credits' => $package->bonus_credits,
            'promotion_bonus' => 0,
            'total_credits' => $package->credits_amount + $package->bonus_credits
        ];

        if ($promotion && $promotion->isValid() && $promotion->isApplicableToPackage($package)) {
            $discount = $promotion->calculateDiscount($package);

            $pricing['discount_amount'] = $discount['discount_amount'];
            $pricing['promotion_bonus'] = $discount['bonus_credits'];
            $pricing['final_price'] = max(0, $pricing['original_price'] - $pricing['discount_amount']);
            $pricing['total_credits'] += $pricing['promotion_bonus'];
        }

        return $pricing;
    }

    /**
     * Consommer des crédits pour une action
     */
    public function consumeCredits(
        User $user,
        int $amount,
        string $description,
        $reference = null
    ): CreditTransaction {
        if (!$user->hasEnoughCredits($amount)) {
            throw new \Exception('Solde de crédits insuffisant. Solde actuel: ' . $user->credits_balance);
        }

        return $user->deductCredits($amount, $description, $reference);
    }

    /**
     * Estimation du coût d'une session d'observation
     */
    public function estimateSessionCost(array $sessionData): int
    {
        $baseCost = 10; // Coût de base par session
        $durationCost = ($sessionData['duration_minutes'] ?? 0) * 0.5; // 0.5 crédit par minute
        $imageCost = ($sessionData['expected_images'] ?? 0) * 2; // 2 crédits par image

        return (int) ceil($baseCost + $durationCost + $imageCost);
    }

    /**
     * Statistiques des crédits pour un utilisateur
     */
    public function getUserCreditAnalytics(User $user, int $days = 30): array
    {
        $startDate = now()->subDays($days);

        $transactions = $user->creditTransactions()
                            ->where('created_at', '>=', $startDate)
                            ->get();

        $purchases = $transactions->where('type', 'purchase');
        $usage = $transactions->where('type', 'usage');

        return [
            'period_days' => $days,
            'current_balance' => $user->credits_balance,
            'total_purchased_period' => $purchases->sum('credits_amount'),
            'total_used_period' => abs($usage->sum('credits_amount')),
            'amount_spent_period' => $this->calculateSpentAmount($purchases),
            'avg_daily_usage' => $days > 0 ? abs($usage->sum('credits_amount')) / $days : 0,
            'efficiency_score' => $this->calculateEfficiencyScore($user),
            'top_usage_categories' => $this->getTopUsageCategories($user, $days),
            'purchase_history' => $purchases->map(function ($transaction) {
                return [
                    'date' => $transaction->created_at,
                    'package' => $transaction->creditPackage?->name,
                    'credits' => $transaction->credits_amount,
                    'amount' => $transaction->metadata['amount_paid'] ?? 0
                ];
            })
        ];
    }

    /**
     * Calculer le score d'efficacité d'un utilisateur
     */
    private function calculateEfficiencyScore(User $user): int
    {
        $totalPurchased = $user->total_credits_purchased;
        $totalUsed = $user->total_credits_used;

        if ($totalPurchased === 0) return 0;

        $usageRate = ($totalUsed / $totalPurchased) * 100;
        $balanceRatio = ($user->credits_balance / $totalPurchased) * 100;

        // Score basé sur l'utilisation et la gestion du solde
        if ($usageRate > 80 && $balanceRatio < 20) return 100; // Utilisation optimale
        if ($usageRate > 60) return 80;
        if ($usageRate > 40) return 60;
        if ($usageRate > 20) return 40;
        return 20;
    }

    /**
     * Obtenir les catégories d'utilisation principales
     */
    private function getTopUsageCategories(User $user, int $days): array
    {
        $transactions = $user->creditTransactions()
                            ->where('type', 'usage')
                            ->where('created_at', '>=', now()->subDays($days))
                            ->get();

        $categories = [];
        foreach ($transactions as $transaction) {
            $category = $this->categorizeTra​nsaction($transaction);
            $categories[$category] = ($categories[$category] ?? 0) + abs($transaction->credits_amount);
        }

        arsort($categories);
        return array_slice($categories, 0, 5, true);
    }

    /**
     * Catégoriser une transaction
     */
    private function categorizeTransaction(CreditTransaction $transaction): string
    {
        if ($transaction->reference_type === 'ObservationSession') {
            return 'Sessions d\'observation';
        }
        if ($transaction->reference_type === 'ImageCapture') {
            return 'Capture d\'images';
        }
        if (str_contains($transaction->description, 'traitement')) {
            return 'Traitement d\'images';
        }
        return 'Autres';
    }

    /**
     * Calculer le montant dépensé
     */
    private function calculateSpentAmount($purchases): int
    {
        return $purchases->sum(function ($transaction) {
            return $transaction->metadata['amount_paid'] ?? 0;
        });
    }

    /**
     * Valider et appliquer un code promo
     */
    public function validatePromotionCode(string $code, User $user, CreditPackage $package = null): array
    {
        $promotion = Promotion::byCode($code)->valid()->first();

        if (!$promotion) {
            return [
                'valid' => false,
                'message' => 'Code promo invalide ou expiré'
            ];
        }

        if (!$promotion->canBeUsedBy($user)) {
            return [
                'valid' => false,
                'message' => 'Ce code promo a déjà été utilisé'
            ];
        }

        if ($package && !$promotion->isApplicableToPackage($package)) {
            return [
                'valid' => false,
                'message' => 'Ce code promo ne s\'applique pas à ce package'
            ];
        }

        if ($package && !$promotion->meetsMinimumPurchase($package->price_cents)) {
            $minAmount = $promotion->min_purchase_amount / 100;
            return [
                'valid' => false,
                'message' => "Montant minimum requis: {$minAmount}€"
            ];
        }

        $discount = $package ? $promotion->calculateDiscount($package) : null;

        return [
            'valid' => true,
            'promotion' => $promotion,
            'discount' => $discount,
            'message' => 'Code promo valide!'
        ];
    }

    /**
     * Recommandations de packages pour un utilisateur
     */
    public function getPackageRecommendations(User $user): array
    {
        $stats = $user->getCreditStats();
        $monthlyUsage = $stats['monthly_usage'];

        $recommendations = [];

        // Analyser les habitudes d'utilisation
        if ($monthlyUsage > 200) {
            $recommendations[] = [
                'type' => 'heavy_user',
                'packages' => CreditPackage::active()->where('credits_amount', '>=', 500)->get(),
                'reason' => 'Basé sur votre forte utilisation mensuelle'
            ];
        } elseif ($monthlyUsage > 100) {
            $recommendations[] = [
                'type' => 'regular_user',
                'packages' => CreditPackage::active()->whereBetween('credits_amount', [200, 500])->get(),
                'reason' => 'Parfait pour votre utilisation régulière'
            ];
        } else {
            $recommendations[] = [
                'type' => 'light_user',
                'packages' => CreditPackage::active()->where('credits_amount', '<=', 200)->get(),
                'reason' => 'Idéal pour débuter ou une utilisation occasionnelle'
            ];
        }

        // Ajouter les packages en promotion
        $promotions = Promotion::valid()->get();
        if ($promotions->count() > 0) {
            $recommendations[] = [
                'type' => 'promotion',
                'packages' => CreditPackage::active()->featured()->get(),
                'reason' => 'Offres spéciales disponibles',
                'promotions' => $promotions
            ];
        }

        return $recommendations;
    }
}

// ================================================

// app/Services/StripeService.php

namespace App\Services;

use App\Models\User;
use App\Models\CreditPackage;
use App\Models\Promotion;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    /**
     * Traiter un paiement pour des crédits
     */
    public function processPayment(
        User $user,
        int $amountCents,
        CreditPackage $package,
        Promotion $promotion = null,
        array $paymentMethodData = []
    ): array {
        try {
            // S'assurer que l'utilisateur a un customer Stripe
            $customer = $this->ensureStripeCustomer($user);

            // Créer le Payment Intent
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amountCents,
                'currency' => $package->currency,
                'customer' => $customer->id,
                'description' => "Achat de crédits: {$package->name}",
                'metadata' => [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'credits_amount' => $package->credits_amount + $package->bonus_credits,
                    'promotion_code' => $promotion?->code,
                    'original_price' => $package->price_cents,
                    'discount_amount' => $promotion ? $promotion->calculateDiscount($package)['discount_amount'] : 0
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            Log::info('Stripe Payment Intent created', [
                'payment_intent_id' => $paymentIntent->id,
                'user_id' => $user->id,
                'amount' => $amountCents,
                'package_id' => $package->id
            ]);

            return [
                'payment_intent_id' => $paymentIntent->id,
                'client_secret' => $paymentIntent->client_secret,
                'status' => $paymentIntent->status,
                'amount' => $paymentIntent->amount,
                'customer_id' => $customer->id
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe payment failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage(),
                'stripe_error_code' => $e->getStripeCode()
            ]);

            throw new \Exception('Erreur de paiement: ' . $e->getUserMessage());
        }
    }

    /**
     * S'assurer qu'un utilisateur a un customer Stripe
     */
    private function ensureStripeCustomer(User $user)
    {
        if ($user->stripe_customer_id) {
            try {
                return $this->stripe->customers->retrieve($user->stripe_customer_id);
            } catch (ApiErrorException $e) {
                // Customer n'existe plus, on en crée un nouveau
                Log::warning('Stripe customer not found, creating new one', [
                    'user_id' => $user->id,
                    'old_customer_id' => $user->stripe_customer_id
                ]);
            }
        }

        // Créer un nouveau customer
        $customer = $this->stripe->customers->create([
            'email' => $user->email,
            'name' => $user->name,
            'metadata' => [
                'user_id' => $user->id,
                'app_name' => config('app.name'),
            ]
        ]);

        $user->update(['stripe_customer_id' => $customer->id]);

        return $customer;
    }

    /**
     * Confirmer un paiement
     */
    public function confirmPayment(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            return [
                'status' => $paymentIntent->status,
                'succeeded' => $paymentIntent->status === 'succeeded',
                'amount' => $paymentIntent->amount,
                'metadata' => $paymentIntent->metadata->toArray()
            ];

        } catch (ApiErrorException $e) {
            Log::error('Failed to confirm payment', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Impossible de confirmer le paiement');
        }
    }

    /**
     * Créer un remboursement
     */
    public function createRefund(string $paymentIntentId, int $amountCents = null, string $reason = null): array
    {
        try {
            $refundData = [
                'payment_intent' => $paymentIntentId,
                'reason' => $reason ?? 'requested_by_customer'
            ];

            if ($amountCents) {
                $refundData['amount'] = $amountCents;
            }

            $refund = $this->stripe->refunds->create($refundData);

            Log::info('Stripe refund created', [
                'refund_id' => $refund->id,
                'payment_intent_id' => $paymentIntentId,
                'amount' => $refund->amount,
                'reason' => $reason
            ]);

            return [
                'refund_id' => $refund->id,
                'status' => $refund->status,
                'amount' => $refund->amount
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe refund failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            throw new \Exception('Impossible de créer le remboursement');
        }
    }

    /**
     * Obtenir l'historique des paiements d'un utilisateur
     */
    public function getCustomerPayments(User $user, int $limit = 20): array
    {
        if (!$user->stripe_customer_id) {
            return [];
        }

        try {
            $paymentIntents = $this->stripe->paymentIntents->all([
                'customer' => $user->stripe_customer_id,
                'limit' => $limit
            ]);

            return array_map(function ($paymentIntent) {
                return [
                    'id' => $paymentIntent->id,
                    'amount' => $paymentIntent->amount,
                    'currency' => $paymentIntent->currency,
                    'status' => $paymentIntent->status,
                    'created' => $paymentIntent->created,
                    'description' => $paymentIntent->description,
                    'metadata' => $paymentIntent->metadata->toArray()
                ];
            }, $paymentIntents->data);

        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve customer payments', [
                'user_id' => $user->id,
                'customer_id' => $user->stripe_customer_id,
                'error' => $e->getMessage()
            ]);

            return [];
        }
    }

    /**
     * Synchroniser les prix Stripe avec les packages
     */
    public function syncPackagePrices(): void
    {
        $packages = CreditPackage::active()->whereNull('stripe_price_id')->get();

        foreach ($packages as $package) {
            try {
                $price = $this->stripe->prices->create([
                    'unit_amount' => $package->price_cents,
                    'currency' => $package->currency,
                    'product_data' => [
                        'name' => $package->name,
                        'description' => $package->description,
                        'metadata' => [
                            'package_id' => $package->id,
                            'credits_amount' => $package->credits_amount,
                            'bonus_credits' => $package->bonus_credits
                        ]
                    ]
                ]);

                $package->update(['stripe_price_id' => $price->id]);

                Log::info('Stripe price created for package', [
                    'package_id' => $package->id,
                    'price_id' => $price->id
                ]);

            } catch (ApiErrorException $e) {
                Log::error('Failed to create Stripe price', [
                    'package_id' => $package->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
