<?php

// app/Services/StripeService.php

namespace App\Services;

use App\Models\User;
use App\Models\CreditPackage;
use App\Models\Promotion;
use App\Models\CreditTransaction;
use Laravel\Cashier\Cashier;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('cashier.secret'));
    }

    /**
     * Créer un Payment Intent pour l'achat de crédits
     */
    public function createPaymentIntent(
        User $user,
        CreditPackage $package,
        ?Promotion $promotion = null
    ): array {
        try {
            // S'assurer que l'utilisateur a un customer Stripe
            if (!$user->hasStripeId()) {
                $user->createAsStripeCustomer();
            }

            // Calculer le prix final avec promotion
            $discount = $promotion ? $promotion->calculateDiscount($package) : [
                'discount_amount' => 0,
                'final_price' => $package->price_cents,
                'bonus_credits' => 0
            ];

            // Minimum 50 centimes pour Stripe
            $amount = max(50, $discount['final_price']);

            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount,
                'currency' => strtolower($package->currency),
                'customer' => $user->stripe_id,
                'description' => "Achat de crédits: {$package->name}",
                'metadata' => [
                    'user_id' => $user->id,
                    'package_id' => $package->id,
                    'package_name' => $package->name,
                    'credits_amount' => $package->total_credits,
                    'promotion_code' => $promotion?->code,
                    'promotion_discount' => $discount['discount_amount'],
                    'bonus_credits' => $discount['bonus_credits'],
                    'original_price' => $package->price_cents,
                    'type' => 'credit_purchase'
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id,
                'amount' => $amount,
                'discount' => $discount
            ];

        } catch (ApiErrorException $e) {
            Log::error('Stripe Payment Intent creation failed', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de la création du paiement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Confirmer et traiter le paiement
     */
    public function confirmPayment(string $paymentIntentId): array
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);

            if ($paymentIntent->status !== 'succeeded') {
                return [
                    'success' => false,
                    'error' => 'Le paiement n\'a pas été confirmé'
                ];
            }

            // Extraire les métadonnées
            $metadata = $paymentIntent->metadata;
            $user = User::find($metadata['user_id']);
            $package = CreditPackage::find($metadata['package_id']);
            $promotion = $metadata['promotion_code']
                ? Promotion::byCode($metadata['promotion_code'])->first()
                : null;

            if (!$user || !$package) {
                return [
                    'success' => false,
                    'error' => 'Données invalides'
                ];
            }

            // Traiter l'ajout de crédits dans une transaction
            DB::transaction(function () use ($user, $package, $promotion, $metadata, $paymentIntentId) {
                $creditsToAdd = $package->total_credits + (int)($metadata['bonus_credits'] ?? 0);
                $this->addCreditsToUser($user, $creditsToAdd, $package, $promotion, $paymentIntentId);

                // Enregistrer l'utilisation de la promotion si applicable
                if ($promotion) {
                    $promotion->recordUsage($user);
                }
            });

            return [
                'success' => true,
                'credits_added' => $package->total_credits + (int)($metadata['bonus_credits'] ?? 0),
                'new_balance' => $user->fresh()->credits_balance
            ];

        } catch (\Exception $e) {
            Log::error('Payment confirmation failed', [
                'payment_intent_id' => $paymentIntentId,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors de la confirmation du paiement'
            ];
        }
    }

    /**
     * Ajouter des crédits à un utilisateur
     */
    private function addCreditsToUser(
        User $user,
        int $creditsAmount,
        CreditPackage $package,
        ?Promotion $promotion = null,
        ?string $stripePaymentIntentId = null
    ): void {
        $oldBalance = $user->credits_balance;
        $newBalance = $oldBalance + $creditsAmount;

        // Mettre à jour le solde
        $user->update(['credits_balance' => $newBalance]);

        // Créer la transaction
        CreditTransaction::create([
            'user_id' => $user->id,
            'type' => 'purchase',
            'credits_amount' => $creditsAmount,
            'balance_before' => $oldBalance,
            'balance_after' => $newBalance,
            'description' => "Achat de crédits - {$package->name}" .
                           ($promotion ? " (Code: {$promotion->code})" : ''),
            'stripe_payment_intent_id' => $stripePaymentIntentId,
            'credit_package_id' => $package->id,
            'metadata' => [
                'package_name' => $package->name,
                'package_price' => $package->price_cents,
                'base_credits' => $package->credits_amount,
                'bonus_credits' => $package->bonus_credits,
                'promotion_code' => $promotion?->code,
                'promotion_bonus' => $promotion && $promotion->type === 'bonus_credits'
                    ? $promotion->value : 0
            ]
        ]);
    }

    /**
     * Synchroniser les packages avec les prix Stripe
     */
    public function syncPackagePrices(): void
    {
        try {
            $packages = CreditPackage::whereNull('stripe_price_id')
                                   ->orWhere('stripe_price_id', '')
                                   ->get();

            foreach ($packages as $package) {
                $product = $this->stripe->products->create([
                    'name' => $package->name,
                    'description' => $package->description,
                    'metadata' => [
                        'credits_amount' => $package->credits_amount,
                        'bonus_credits' => $package->bonus_credits,
                        'package_id' => $package->id
                    ]
                ]);

                $price = $this->stripe->prices->create([
                    'product' => $product->id,
                    'unit_amount' => $package->price_cents,
                    'currency' => strtolower($package->currency),
                    'metadata' => [
                        'package_id' => $package->id
                    ]
                ]);

                $package->update(['stripe_price_id' => $price->id]);
            }

        } catch (ApiErrorException $e) {
            Log::error('Failed to sync package prices with Stripe', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Traiter les webhooks Stripe
     */
    public function handleWebhook(array $payload, string $signature): array
    {
        try {
            $event = \Stripe\Webhook::constructEvent(
                json_encode($payload),
                $signature,
                config('cashier.webhook.secret')
            );

            switch ($event->type) {
                case 'payment_intent.succeeded':
                    return $this->handlePaymentSucceeded($event->data->object);

                case 'payment_intent.payment_failed':
                    return $this->handlePaymentFailed($event->data->object);

                default:
                    return ['success' => true, 'message' => 'Event not handled'];
            }

        } catch (\Exception $e) {
            Log::error('Webhook handling failed', [
                'error' => $e->getMessage(),
                'payload' => $payload
            ]);

            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Traiter un paiement réussi via webhook
     */
    private function handlePaymentSucceeded($paymentIntent): array
    {
        // Le paiement sera déjà traité via confirmPayment()
        // mais on peut ajouter des logs ou notifications ici
        Log::info('Payment succeeded via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'user_id' => $paymentIntent->metadata['user_id'] ?? null
        ]);

        return ['success' => true, 'message' => 'Payment processed'];
    }

    /**
     * Traiter un paiement échoué via webhook
     */
    private function handlePaymentFailed($paymentIntent): array
    {
        Log::warning('Payment failed via webhook', [
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $paymentIntent->amount,
            'user_id' => $paymentIntent->metadata['user_id'] ?? null,
            'last_payment_error' => $paymentIntent->last_payment_error ?? null
        ]);

        // Ici on pourrait notifier l'utilisateur de l'échec

        return ['success' => true, 'message' => 'Payment failure processed'];
    }

    /**
     * Créer un remboursement
     */
    public function createRefund(string $paymentIntentId, ?int $amount = null): array
    {
        try {
            $refund = $this->stripe->refunds->create([
                'payment_intent' => $paymentIntentId,
                'amount' => $amount, // Si null, remboursement total
            ]);

            return [
                'success' => true,
                'refund_id' => $refund->id,
                'amount' => $refund->amount,
                'status' => $refund->status
            ];

        } catch (ApiErrorException $e) {
            Log::error('Refund creation failed', [
                'payment_intent_id' => $paymentIntentId,
                'amount' => $amount,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => 'Erreur lors du remboursement: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Récupérer les statistiques de paiement
     */
    public function getPaymentStats(\DateTime $from, \DateTime $to): array
    {
        try {
            $payments = $this->stripe->paymentIntents->all([
                'created' => [
                    'gte' => $from->getTimestamp(),
                    'lte' => $to->getTimestamp()
                ],
                'limit' => 100
            ]);

            $totalAmount = 0;
            $successfulCount = 0;
            $failedCount = 0;

            foreach ($payments->data as $payment) {
                if ($payment->status === 'succeeded') {
                    $totalAmount += $payment->amount;
                    $successfulCount++;
                } else {
                    $failedCount++;
                }
            }

            return [
                'total_amount' => $totalAmount,
                'successful_payments' => $successfulCount,
                'failed_payments' => $failedCount,
                'success_rate' => $successfulCount > 0
                    ? round(($successfulCount / ($successfulCount + $failedCount)) * 100, 2)
                    : 0
            ];

        } catch (ApiErrorException $e) {
            Log::error('Failed to retrieve payment stats', [
                'error' => $e->getMessage()
            ]);

            return [
                'error' => $e->getMessage()
            ];
        }
    }
}
