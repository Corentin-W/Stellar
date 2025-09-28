<?php

// app/Services/StripeService.php

namespace App\Services;

use Stripe\Price;
use Stripe\Stripe;
use Stripe\Product;
use App\Models\User;
use Stripe\StripeClient;
use App\Models\Promotion;
use Laravel\Cashier\Cashier;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class StripeService
{
    private StripeClient $stripe;

    public function __construct()
    {
        // Vérifier que la configuration existe
        $secretKey = env('STRIPE_SECRET');

        if (empty($secretKey)) {
            throw new \Exception('Stripe secret key not configured. Please set STRIPE_SECRET in your .env file.');
        }

        $this->stripe = new StripeClient($secretKey);
    }

public function createPaymentIntent(CreditPackage $package, $promotionCode = null): array
    {
        try {
            $amount = $package->price_cents;

            // Appliquer la promotion si fournie
            if ($promotionCode) {
                // Logic pour appliquer la promotion
                // À implémenter selon vos besoins
            }

            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $amount,
                'currency' => strtolower($package->currency),
                'metadata' => [
                    'package_id' => $package->id,
                    'credits_amount' => $package->credits_amount,
                    'user_id' => auth()->id()
                ]
            ]);

            return [
                'client_secret' => $paymentIntent->client_secret,
                'payment_intent_id' => $paymentIntent->id
            ];

        } catch (\Exception $e) {
            Log::error('Failed to create payment intent', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
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
 * Synchroniser les prix Stripe avec les packages
 */
public function syncPackagePrices(): void
{
    $packages = CreditPackage::active()->get();

    foreach ($packages as $package) {
        try {
            $productId = $package->stripe_product_id;

            // 1. Gérer le produit (créer seulement s'il n'existe pas)
            if (!$productId) {
                $productData = [
                    'name' => $package->name ?: 'Package de crédits',
                    'metadata' => [
                        'package_id' => (string)$package->id,
                        'credits_amount' => (string)$package->credits_amount,
                        'bonus_credits' => (string)($package->bonus_credits ?? 0)
                    ]
                ];

                // Ajouter description seulement si elle est valide
                $description = trim($package->description ?? '');
                if ($description !== '') {
                    $productData['description'] = $description;
                }

                $product = $this->stripe->products->create($productData);
                $productId = $product->id;
                $package->update(['stripe_product_id' => $productId]);
            } else {
                // 2. Mettre à jour le produit existant (nom, description, metadata)
                $updateData = [
                    'name' => $package->name ?: 'Package de crédits',
                    'metadata' => [
                        'package_id' => (string)$package->id,
                        'credits_amount' => (string)$package->credits_amount,
                        'bonus_credits' => (string)($package->bonus_credits ?? 0)
                    ]
                ];

                $description = trim($package->description ?? '');
                if ($description !== '') {
                    $updateData['description'] = $description;
                }

                $this->stripe->products->update($productId, $updateData);
            }

            // 3. Désactiver l'ancien prix s'il existe
            if ($package->stripe_price_id) {
                $this->stripe->prices->update($package->stripe_price_id, [
                    'active' => false
                ]);
            }

            // 4. Créer un nouveau prix (obligatoire car on ne peut pas modifier un prix)
            $price = $this->stripe->prices->create([
                'unit_amount' => $package->price_cents,
                'currency' => $package->currency ?? 'eur',
                'product' => $productId
            ]);

            // 5. Mettre à jour le package avec le nouveau prix
            $package->update(['stripe_price_id' => $price->id]);

            Log::info('Stripe price synced for package', [
                'package_id' => $package->id,
                'product_id' => $productId,
                'old_price_id' => $package->getOriginal('stripe_price_id'),
                'new_price_id' => $price->id,
                'amount' => $package->price_cents
            ]);

        } catch (ApiErrorException $e) {
            Log::error('Failed to sync Stripe price', [
                'package_id' => $package->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

/**
 * Synchroniser un package spécifique avec Stripe
 */
public function syncSinglePackage(CreditPackage $package, ?string $oldStripePriceId = null): void
{
    try {
        $productId = $package->stripe_product_id;

        // Gérer le produit
        if (!$productId) {
            // Créer le produit
            $productData = [
                'name' => $package->name ?: 'Package de crédits',
                'metadata' => [
                    'package_id' => (string)$package->id,
                    'credits_amount' => (string)$package->credits_amount,
                    'bonus_credits' => (string)($package->bonus_credits ?? 0)
                ]
            ];

            $description = trim($package->description ?? '');
            if ($description !== '') {
                $productData['description'] = $description;
            }

            $product = $this->stripe->products->create($productData);
            $productId = $product->id;
            $package->update(['stripe_product_id' => $productId]);
        } else {
            // Mettre à jour le produit existant
            $updateData = [
                'name' => $package->name ?: 'Package de crédits',
                'metadata' => [
                    'package_id' => (string)$package->id,
                    'credits_amount' => (string)$package->credits_amount,
                    'bonus_credits' => (string)($package->bonus_credits ?? 0)
                ]
            ];

            $description = trim($package->description ?? '');
            if ($description !== '') {
                $updateData['description'] = $description;
            }

            $this->stripe->products->update($productId, $updateData);
        }

        // Utiliser l'ancien prix fourni ou celui du package
        $priceToDisable = $oldStripePriceId ?? $package->stripe_price_id;

        // Désactiver l'ancien prix s'il existe
        if ($priceToDisable) {
            try {
                $this->stripe->prices->update($priceToDisable, [
                    'active' => false
                ]);
                Log::info('Old Stripe price disabled', [
                    'package_id' => $package->id,
                    'disabled_price_id' => $priceToDisable
                ]);
            } catch (ApiErrorException $e) {
                Log::warning('Failed to disable old price', [
                    'price_id' => $priceToDisable,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Créer nouveau prix
        $price = $this->stripe->prices->create([
            'unit_amount' => $package->price_cents,
            'currency' => $package->currency ?? 'eur',
            'product' => $productId
        ]);

        $package->update(['stripe_price_id' => $price->id]);

        Log::info('Stripe price synced for package', [
            'package_id' => $package->id,
            'product_id' => $productId,
            'old_price_id' => $priceToDisable,
            'new_price_id' => $price->id,
            'amount' => $package->price_cents
        ]);

    } catch (ApiErrorException $e) {
        Log::error('Failed to sync package with Stripe', [
            'package_id' => $package->id,
            'error' => $e->getMessage()
        ]);
        throw new \Exception('Impossible de synchroniser avec Stripe: ' . $e->getMessage());
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

private function createStripePrice(CreditPackage $package): void
    {
        try {
            Log::info('Création produit Stripe pour package', [
                'package_id' => $package->id,
                'package_name' => $package->name
            ]);

            // Préparer les données du produit
            $productData = [
                'name' => $package->name,
                'metadata' => [
                    'credits_amount' => $package->credits_amount,
                    'bonus_credits' => $package->bonus_credits ?? 0,
                    'package_id' => $package->id
                ]
            ];

            // Ajouter la description seulement si elle n'est pas vide
            if (!empty($package->description)) {
                $productData['description'] = $package->description;
            }

            // Créer le produit Stripe
            $product = Product::create($productData);

            Log::info('Produit Stripe créé', [
                'product_id' => $product->id,
                'package_id' => $package->id
            ]);

            // Créer le prix
            $price = Price::create([
                'unit_amount' => $package->price_cents,
                'currency' => strtolower($package->currency),
                'product' => $product->id,
                'metadata' => [
                    'package_id' => $package->id,
                    'credits_amount' => $package->credits_amount
                ]
            ]);

            Log::info('Prix Stripe créé', [
                'price_id' => $price->id,
                'package_id' => $package->id,
                'amount' => $package->price_cents
            ]);

            // Mettre à jour le package avec le price_id
            $package->update(['stripe_price_id' => $price->id]);

            Log::info('Package mis à jour avec stripe_price_id', [
                'package_id' => $package->id,
                'stripe_price_id' => $price->id
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to create Stripe price for package', [
                'package_id' => $package->id,
                'package_name' => $package->name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }
}
