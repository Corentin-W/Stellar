<?php

// app/Http/Controllers/StripeApiController.php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\Promotion;
use App\Models\User;
use App\Services\StripeService;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class StripeApiController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreditService $creditService
    ) {
        $this->middleware('auth');
    }

    /**
     * Créer un Payment Intent pour un package de crédits
     */
    public function createPaymentIntent(Request $request)
    {
        try {
            $validated = $request->validate([
                'package_id' => 'required|exists:credit_packages,id',
                'promotion_code' => 'nullable|string|max:50'
            ]);

            $package = CreditPackage::findOrFail($validated['package_id']);
            $user = Auth::user();

            // Vérifier que le package est actif
            if (!$package->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce package de crédits n\'est plus disponible'
                ], 400);
            }

            // Valider et appliquer la promotion si fournie
            $promotion = null;
            if (!empty($validated['promotion_code'])) {
                $promotionValidation = $this->creditService->validatePromotionCode(
                    $validated['promotion_code'],
                    $user,
                    $package
                );

                if (!$promotionValidation['valid']) {
                    return response()->json([
                        'success' => false,
                        'message' => $promotionValidation['message'],
                        'error_type' => 'promotion_invalid'
                    ], 400);
                }

                $promotion = $promotionValidation['promotion'];
            }

            // Calculer le prix final avec promotions
            $pricing = $this->creditService->calculateFinalPricing($package, $promotion);

            // Vérifier que le prix final est valide
            if ($pricing['final_price'] < 50) { // Minimum Stripe: 0.50€
                return response()->json([
                    'success' => false,
                    'message' => 'Le montant minimum pour un paiement est de 0.50€'
                ], 400);
            }

            // Créer le Payment Intent via Stripe
            $paymentResult = $this->stripeService->processPayment(
                $user,
                $pricing['final_price'],
                $package,
                $promotion
            );

            Log::info('Payment Intent created successfully', [
                'user_id' => $user->id,
                'package_id' => $package->id,
                'payment_intent_id' => $paymentResult['payment_intent_id'],
                'amount' => $pricing['final_price'],
                'promotion_code' => $promotion?->code
            ]);

            return response()->json([
                'success' => true,
                'client_secret' => $paymentResult['client_secret'],
                'payment_intent_id' => $paymentResult['payment_intent_id'],
                'pricing' => $pricing,
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'description' => $package->description
                ],
                'promotion' => $promotion ? [
                    'code' => $promotion->code,
                    'name' => $promotion->name,
                    'type' => $promotion->type,
                    'value' => $promotion->value
                ] : null
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create Payment Intent', [
                'user_id' => Auth::id(),
                'package_id' => $request->package_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de créer le paiement. Veuillez réessayer.',
                'error_type' => 'payment_creation_failed'
            ], 500);
        }
    }

    /**
     * Confirmer un paiement après validation côté client
     */
    public function confirmPayment(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_intent_id' => 'required|string'
            ]);

            $paymentConfirmation = $this->stripeService->confirmPayment($validated['payment_intent_id']);

            if ($paymentConfirmation['succeeded']) {
                Log::info('Payment confirmed successfully', [
                    'user_id' => Auth::id(),
                    'payment_intent_id' => $validated['payment_intent_id'],
                    'amount' => $paymentConfirmation['amount']
                ]);

                // Le webhook Stripe se chargera d'ajouter les crédits
                return response()->json([
                    'success' => true,
                    'message' => 'Paiement confirmé avec succès',
                    'payment_status' => $paymentConfirmation['status'],
                    'metadata' => $paymentConfirmation['metadata']
                ]);
            } else {
                Log::warning('Payment confirmation failed', [
                    'user_id' => Auth::id(),
                    'payment_intent_id' => $validated['payment_intent_id'],
                    'status' => $paymentConfirmation['status']
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Le paiement n\'a pas pu être confirmé',
                    'payment_status' => $paymentConfirmation['status']
                ], 400);
            }

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to confirm payment', [
                'user_id' => Auth::id(),
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de confirmer le paiement'
            ], 500);
        }
    }

    /**
     * Obtenir l'historique des paiements Stripe d'un utilisateur
     */
    public function paymentHistory(Request $request)
    {
        try {
            $user = Auth::user();
            $limit = min($request->input('limit', 20), 50); // Max 50

            $payments = $this->stripeService->getCustomerPayments($user, $limit);

            return response()->json([
                'success' => true,
                'payments' => $payments,
                'total' => count($payments)
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment history', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer l\'historique des paiements'
            ], 500);
        }
    }

    /**
     * Créer un remboursement (admin uniquement)
     */
    public function createRefund(Request $request)
    {
        // Vérifier les permissions admin
        if (!Auth::user()->admin) {
            return response()->json([
                'success' => false,
                'message' => 'Accès non autorisé'
            ], 403);
        }

        try {
            $validated = $request->validate([
                'payment_intent_id' => 'required|string',
                'amount_euros' => 'nullable|numeric|min:0.01',
                'reason' => 'nullable|string|max:255'
            ]);

            $amountCents = $validated['amount_euros'] ? round($validated['amount_euros'] * 100) : null;
            $reason = $validated['reason'] ?? 'requested_by_customer';

            $refund = $this->stripeService->createRefund(
                $validated['payment_intent_id'],
                $amountCents,
                $reason
            );

            Log::info('Refund created by admin', [
                'admin_id' => Auth::id(),
                'payment_intent_id' => $validated['payment_intent_id'],
                'refund_id' => $refund['refund_id'],
                'amount' => $refund['amount'],
                'reason' => $reason
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Remboursement créé avec succès',
                'refund' => $refund
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to create refund', [
                'admin_id' => Auth::id(),
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de créer le remboursement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtenir les détails d'un paiement spécifique
     */
    public function paymentDetails(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_intent_id' => 'required|string'
            ]);

            $user = Auth::user();

            // Récupérer la transaction correspondante
            $transaction = $user->creditTransactions()
                              ->where('stripe_payment_intent_id', $validated['payment_intent_id'])
                              ->with('creditPackage')
                              ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaction non trouvée'
                ], 404);
            }

            // Récupérer les détails Stripe
            $paymentDetails = $this->stripeService->confirmPayment($validated['payment_intent_id']);

            return response()->json([
                'success' => true,
                'transaction' => [
                    'id' => $transaction->id,
                    'credits_amount' => $transaction->credits_amount,
                    'description' => $transaction->description,
                    'created_at' => $transaction->created_at,
                    'package' => $transaction->creditPackage ? [
                        'name' => $transaction->creditPackage->name,
                        'price_euros' => $transaction->creditPackage->price_euros
                    ] : null,
                    'metadata' => $transaction->metadata
                ],
                'stripe_details' => [
                    'payment_intent_id' => $validated['payment_intent_id'],
                    'status' => $paymentDetails['status'],
                    'amount' => $paymentDetails['amount'],
                    'succeeded' => $paymentDetails['succeeded']
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve payment details', [
                'user_id' => Auth::id(),
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les détails du paiement'
            ], 500);
        }
    }

    /**
     * Calculer le prix avec promotion (avant création du Payment Intent)
     */
    public function calculatePrice(Request $request)
    {
        try {
            $validated = $request->validate([
                'package_id' => 'required|exists:credit_packages,id',
                'promotion_code' => 'nullable|string|max:50'
            ]);

            $package = CreditPackage::findOrFail($validated['package_id']);
            $user = Auth::user();

            if (!$package->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Package non disponible'
                ], 400);
            }

            $promotion = null;
            $promotionValid = false;
            $promotionMessage = null;

            if (!empty($validated['promotion_code'])) {
                $promotionValidation = $this->creditService->validatePromotionCode(
                    $validated['promotion_code'],
                    $user,
                    $package
                );

                $promotionValid = $promotionValidation['valid'];
                $promotionMessage = $promotionValidation['message'];

                if ($promotionValid) {
                    $promotion = $promotionValidation['promotion'];
                }
            }

            $pricing = $this->creditService->calculateFinalPricing($package, $promotion);

            return response()->json([
                'success' => true,
                'package' => [
                    'id' => $package->id,
                    'name' => $package->name,
                    'credits_amount' => $package->credits_amount,
                    'bonus_credits' => $package->bonus_credits,
                    'price_cents' => $package->price_cents,
                    'price_euros' => $package->price_euros
                ],
                'promotion' => [
                    'valid' => $promotionValid,
                    'message' => $promotionMessage,
                    'code' => $promotion?->code,
                    'name' => $promotion?->name,
                    'type' => $promotion?->type
                ],
                'pricing' => [
                    'original_price' => $pricing['original_price'],
                    'discount_amount' => $pricing['discount_amount'],
                    'final_price' => $pricing['final_price'],
                    'base_credits' => $pricing['base_credits'],
                    'bonus_credits' => $pricing['bonus_credits'],
                    'promotion_bonus' => $pricing['promotion_bonus'],
                    'total_credits' => $pricing['total_credits'],
                    'price_per_credit' => $pricing['total_credits'] > 0 ? round($pricing['final_price'] / $pricing['total_credits'] / 100, 4) : 0
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to calculate price', [
                'user_id' => Auth::id(),
                'package_id' => $request->package_id,
                'promotion_code' => $request->promotion_code,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du calcul du prix'
            ], 500);
        }
    }

    /**
     * Vérifier le statut d'un paiement
     */
    public function checkPaymentStatus(Request $request)
    {
        try {
            $validated = $request->validate([
                'payment_intent_id' => 'required|string'
            ]);

            $user = Auth::user();

            // Vérifier que ce Payment Intent appartient à l'utilisateur
            $transaction = $user->creditTransactions()
                              ->where('stripe_payment_intent_id', $validated['payment_intent_id'])
                              ->first();

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Paiement non trouvé'
                ], 404);
            }

            $paymentStatus = $this->stripeService->confirmPayment($validated['payment_intent_id']);

            return response()->json([
                'success' => true,
                'payment_intent_id' => $validated['payment_intent_id'],
                'status' => $paymentStatus['status'],
                'succeeded' => $paymentStatus['succeeded'],
                'amount' => $paymentStatus['amount'],
                'credits_added' => $transaction->credits_amount,
                'transaction_id' => $transaction->id
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Failed to check payment status', [
                'user_id' => Auth::id(),
                'payment_intent_id' => $request->payment_intent_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de vérifier le statut du paiement'
            ], 500);
        }
    }

    /**
     * Obtenir les méthodes de paiement sauvegardées d'un client
     */
    public function savedPaymentMethods()
    {
        try {
            $user = Auth::user();

            if (!$user->stripe_customer_id) {
                return response()->json([
                    'success' => true,
                    'payment_methods' => []
                ]);
            }

            // Cette fonctionnalité nécessiterait une extension du StripeService
            // Pour l'instant, on retourne une liste vide
            return response()->json([
                'success' => true,
                'payment_methods' => [],
                'message' => 'Fonctionnalité en développement'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve saved payment methods', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les méthodes de paiement'
            ], 500);
        }
    }

    /**
     * Obtenir les statistiques de paiement d'un utilisateur
     */
    public function userPaymentStats()
    {
        try {
            $user = Auth::user();
            $stats = $user->getCreditStats();

            // Ajouter des statistiques Stripe
            $stripePayments = $this->stripeService->getCustomerPayments($user, 100);

            $totalSpent = 0;
            $successfulPayments = 0;
            $lastPaymentDate = null;

            foreach ($stripePayments as $payment) {
                if ($payment['status'] === 'succeeded') {
                    $totalSpent += $payment['amount'];
                    $successfulPayments++;

                    if (!$lastPaymentDate || $payment['created'] > $lastPaymentDate) {
                        $lastPaymentDate = $payment['created'];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'credit_stats' => $stats,
                'payment_stats' => [
                    'total_spent_cents' => $totalSpent,
                    'total_spent_euros' => $totalSpent / 100,
                    'successful_payments' => $successfulPayments,
                    'total_payments' => count($stripePayments),
                    'last_payment_date' => $lastPaymentDate ? date('Y-m-d H:i:s', $lastPaymentDate) : null,
                    'avg_payment_amount' => $successfulPayments > 0 ? round($totalSpent / $successfulPayments / 100, 2) : 0
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to retrieve user payment stats', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Impossible de récupérer les statistiques'
            ], 500);
        }
    }
}
