<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditPackage;
use App\Models\Promotion;
use App\Models\CreditTransaction;
use App\Models\User;
use App\Services\StripeService;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CreditController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreditService $creditService
    ) {
        // Middleware auth appliqué via les routes
    }

    /**
     * Boutique de crédits
     */
    public function shop()
    {
        $packages = CreditPackage::active()->ordered()->get();
        $promotions = Promotion::valid()->take(3)->get();
        $user = auth()->user();
        $recommendations = $this->creditService->getRecommendations($user);

        return view('credits.shop', compact('packages', 'promotions', 'recommendations'));
    }

    /**
     * Détails d'un package
     */
    public function packageDetails(CreditPackage $package)
    {
        if (!$package->is_active) {
            abort(404);
        }

        $relatedPackages = CreditPackage::active()
            ->where('id', '!=', $package->id)
            ->ordered()
            ->take(3)
            ->get();

        return view('credits.package-details', compact('package', 'relatedPackages'));
    }

    /**
     * Valider un code promotionnel (AJAX)
     */
    public function validatePromotion(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'package_id' => 'required|exists:credit_packages,id'
        ]);

        $user = auth()->user();
        $package = CreditPackage::find($request->package_id);

        $result = $this->creditService->validatePromotion(
            $request->code,
            $user,
            $package
        );

        return response()->json($result);
    }

    /**
     * Créer une session de checkout Stripe (redirection)
     */
    public function createCheckoutSession(Request $request)
    {
        Log::info('=== DÉBUT createCheckoutSession ===', [
            'request_data' => $request->all(),
            'user_id' => auth()->id(),
            'timestamp' => now()
        ]);

        try {
            $request->validate([
                'package_id' => 'required|exists:credit_packages,id',
                'promotion_code' => 'nullable|string|max:50'
            ]);

            $user = auth()->user();
            $package = CreditPackage::findOrFail($request->package_id);

            if (!$package->is_active) {
                Log::warning('Package inactif', ['package_id' => $package->id]);
                return back()->with('error', 'Ce package n\'est plus disponible');
            }

            // Validation de la promotion
            $promotion = null;
            $discountAmount = 0;

            if ($request->promotion_code) {
                $promotion = Promotion::byCode($request->promotion_code)->first();

                if ($promotion && $promotion->canBeUsedBy($user) && $promotion->isApplicableToPackage($package)) {
                    if ($promotion->type === 'percentage') {
                        $discountAmount = ($package->price_cents * $promotion->value) / 100;
                    } elseif ($promotion->type === 'fixed_amount') {
                        $discountAmount = min($promotion->value * 100, $package->price_cents);
                    }
                    Log::info('Promotion appliquée', ['discount' => $discountAmount]);
                }
            }

            $finalPrice = max($package->price_cents - $discountAmount, 0);

            // Créer la session Stripe Checkout
            $checkoutSession = $user->stripe()->checkout->sessions->create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => strtolower($package->currency),
                        'product_data' => [
                            'name' => $package->name,
                            'description' => $package->description ?: "Package de {$package->credits_amount} crédits",
                            'metadata' => [
                                'credits_amount' => (string)$package->credits_amount,
                                'bonus_credits' => (string)($package->bonus_credits ?? 0),
                            ],
                        ],
                        'unit_amount' => $finalPrice,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('credits.success') . '?session_id={CHECKOUT_SESSION_ID}',
                'cancel_url' => route('credits.cancel'),
                'metadata' => [
                    'user_id' => (string)$user->id,
                    'package_id' => (string)$package->id,
                    'credits_amount' => (string)$package->credits_amount,
                    'bonus_credits' => (string)($package->bonus_credits ?? 0),
                    'total_credits' => (string)$package->total_credits,
                    'promotion_code' => $promotion?->code ?? '',
                    'promotion_discount' => (string)$discountAmount,
                    'created_at' => now()->toISOString(),
                ],
            ]);

            Log::info('Session Stripe créée', [
                'session_id' => $checkoutSession->id,
                'amount' => $finalPrice
            ]);

            return redirect($checkoutSession->url);

        } catch (\Exception $e) {
            Log::error('Erreur création session', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Impossible de créer la session de paiement: ' . $e->getMessage());
        }
    }

    /**
     * Traiter le succès du paiement avec sécurité renforcée
     */
    public function paymentSuccess(Request $request)
    {
        $sessionId = $request->get('session_id');

        Log::info('=== DÉBUT paymentSuccess ===', [
            'session_id' => $sessionId,
            'user_id' => auth()->id(),
            'timestamp' => now(),
            'version' => '3.0_SECURE'
        ]);

        if (!$sessionId) {
            Log::warning('Session ID manquant');
            return redirect()->route('credits.shop')->with('error', 'Session invalide');
        }

        // PROTECTION 1: Cache pour éviter les traitements simultanés
        $lockKey = "payment_processing_{$sessionId}";
        $lockAcquired = Cache::lock($lockKey, 30)->get(function () use ($sessionId, $request) {
            return $this->processPaymentWithLock($sessionId, $request);
        });

        if (!$lockAcquired) {
            Log::warning('Lock non acquis pour session', ['session_id' => $sessionId]);
            return redirect()->route('credits.shop')->with('error', 'Traitement en cours, veuillez patienter');
        }

        return $lockAcquired;
    }

    /**
     * Traitement du paiement avec verrou
     */
    private function processPaymentWithLock($sessionId, Request $request)
    {
        try {
            // PROTECTION 2: Vérification base de données stricte
            $existingTransaction = CreditTransaction::where('stripe_session_id', $sessionId)
                ->orWhere('metadata->session_id', $sessionId)
                ->first();

            if ($existingTransaction) {
                Log::info('Session déjà traitée (BDD)', [
                    'session_id' => $sessionId,
                    'transaction_id' => $existingTransaction->id,
                    'credits' => $existingTransaction->credits_amount
                ]);

                return view('credits.success', [
                    'session' => (object)[
                        'id' => $sessionId,
                        'amount_total' => $existingTransaction->metadata['amount_total'] ?? 0,
                    ],
                    'credits_added' => $existingTransaction->credits_amount,
                    'already_processed' => true,
                    'message' => 'Cette transaction a déjà été traitée.'
                ]);
            }

            // PROTECTION 3: Vérification cache session traitée
            $cacheKey = "processed_session_{$sessionId}";
            if (Cache::has($cacheKey)) {
                Log::info('Session déjà traitée (Cache)', ['session_id' => $sessionId]);

                return view('credits.success', [
                    'session' => (object)['id' => $sessionId],
                    'credits_added' => 0,
                    'already_processed' => true,
                    'message' => 'Cette transaction a déjà été traitée.'
                ]);
            }

            $user = auth()->user();
            $session = $user->stripe()->checkout->sessions->retrieve($sessionId);

            Log::info('Session Stripe récupérée', [
                'session_id' => $sessionId,
                'payment_status' => $session->payment_status,
                'amount_total' => $session->amount_total
            ]);

            if ($session->payment_status !== 'paid') {
                Log::warning('Paiement non confirmé', [
                    'session_id' => $sessionId,
                    'status' => $session->payment_status
                ]);
                return redirect()->route('credits.shop')->with('error', 'Le paiement n\'a pas été confirmé');
            }

            // PROTECTION 4: Marquer en cache AVANT traitement
            Cache::put($cacheKey, true, now()->addHours(24));

            // Traiter les crédits
            $creditsAdded = $this->processCreditsFromSession($session);

            Log::info('=== FIN paymentSuccess SUCCÈS ===', [
                'session_id' => $sessionId,
                'credits_added' => $creditsAdded
            ]);

            return view('credits.success', [
                'session' => $session,
                'credits_added' => $creditsAdded,
                'already_processed' => false,
                'message' => "Félicitations ! {$creditsAdded} crédits ont été ajoutés à votre compte."
            ]);

        } catch (\Exception $e) {
            Log::error('Erreur traitement paiement', [
                'session_id' => $sessionId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('credits.shop')->with('error', 'Erreur lors du traitement du paiement');
        }
    }

    /**
     * Traiter l'ajout de crédits avec sécurité maximale
     */
    private function processCreditsFromSession($session)
    {
        $metadata = $session->metadata;
        $user = User::find($metadata['user_id']);

        if (!$user) {
            Log::error('Utilisateur introuvable', [
                'session_id' => $session->id,
                'user_id' => $metadata['user_id']
            ]);
            throw new \Exception('Utilisateur introuvable');
        }

        // PROTECTION FINALE: Vérification ultime avant insertion
        $finalCheck = CreditTransaction::where('stripe_session_id', $session->id)->exists();
        if ($finalCheck) {
            Log::warning('Dernier check: session déjà traitée', ['session_id' => $session->id]);
            $existing = CreditTransaction::where('stripe_session_id', $session->id)->first();
            return $existing->credits_amount;
        }

        $creditsToAdd = (int)($metadata['total_credits'] ?? 0);
        $oldBalance = $user->credits_balance;
        $newBalance = $oldBalance + $creditsToAdd;

        Log::info('Traitement crédits', [
            'user_id' => $user->id,
            'credits_to_add' => $creditsToAdd,
            'old_balance' => $oldBalance,
            'new_balance' => $newBalance,
            'session_id' => $session->id
        ]);

        // Transaction atomique avec retry
        $transaction = DB::transaction(function () use ($user, $creditsToAdd, $oldBalance, $newBalance, $session, $metadata) {

            // Ultime vérification dans la transaction
            $exists = CreditTransaction::where('stripe_session_id', $session->id)->lockForUpdate()->exists();
            if ($exists) {
                throw new \Exception('Transaction déjà créée dans la transaction DB');
            }

            // Mettre à jour le solde utilisateur
            $user->lockForUpdate()->increment('credits_balance', $creditsToAdd);

            // Créer la transaction
            return CreditTransaction::create([
                'user_id' => $user->id,
                'type' => 'purchase',
                'credits_amount' => $creditsToAdd,
                'balance_before' => $oldBalance,
                'balance_after' => $newBalance,
                'description' => "Achat de crédits via Stripe Checkout - Session: " . $session->id,
                'stripe_session_id' => $session->id, // INDEX UNIQUE
                'credit_package_id' => $metadata['package_id'] ?? null,
                'metadata' => [
                    'session_id' => $session->id,
                    'amount_total' => $session->amount_total,
                    'promotion_code' => $metadata['promotion_code'] ?? null,
                    'promotion_discount' => $metadata['promotion_discount'] ?? 0,
                    'processed_at' => now()->toISOString(),
                    'security_version' => '3.0'
                ]
            ]);
        }, 3); // 3 tentatives max

        Log::info('Crédits ajoutés avec succès', [
            'user_id' => $user->id,
            'credits_added' => $creditsToAdd,
            'transaction_id' => $transaction->id,
            'session_id' => $session->id
        ]);

        return $creditsToAdd;
    }

    /**
     * Webhook Stripe sécurisé
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (!$signature) {
            Log::warning('Webhook sans signature');
            return response('Missing signature', 400);
        }

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $signature,
                config('services.stripe.webhook_secret')
            );

            Log::info('Webhook Stripe reçu', [
                'type' => $event->type,
                'id' => $event->id
            ]);

            if ($event->type === 'checkout.session.completed') {
                $session = $event->data->object;

                if ($session->payment_status === 'paid') {
                    // Utiliser le même système de lock que paymentSuccess
                    $lockKey = "webhook_processing_{$session->id}";
                    Cache::lock($lockKey, 30)->get(function () use ($session) {
                        $this->processCreditsFromSession($session);
                    });
                }
            }

            return response('Webhook handled', 200);

        } catch (\Exception $e) {
            Log::error('Erreur webhook', [
                'error' => $e->getMessage()
            ]);
            return response('Webhook handling failed', 500);
        }
    }

    /**
     * Historique des transactions
     */
    public function history()
    {
        $user = auth()->user();

        $transactions = CreditTransaction::forUser($user->id)
            ->with(['creditPackage'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $stats = [
            'total_purchased' => CreditTransaction::forUser($user->id)->purchases()->sum('credits_amount'),
            'total_used' => abs(CreditTransaction::forUser($user->id)->usage()->sum('credits_amount')),
            'current_balance' => $user->credits_balance,
            'transactions_count' => CreditTransaction::forUser($user->id)->count()
        ];

        return view('credits.history', compact('transactions', 'stats'));
    }

    /**
     * Solde actuel (API)
     */
    public function balance()
    {
        return response()->json([
            'balance' => auth()->user()->credits_balance,
            'formatted_balance' => number_format(auth()->user()->credits_balance)
        ]);
    }

    /**
     * Page de succès
     */
    public function success()
    {
        return view('credits.success');
    }

    /**
     * Page d'annulation
     */
    public function cancel()
    {
        return view('credits.cancel');
    }

    /**
     * Estimer le coût d'une session
     */
    public function estimateSessionCost(Request $request)
    {
        $request->validate([
            'session_type' => 'required|string|in:observation,imaging,spectroscopy,photometry',
            'duration_minutes' => 'required|integer|min:1|max:240',
            'complexity' => 'required|string|in:low,medium,high,expert'
        ]);

        $cost = $this->creditService->estimateSessionCost(
            $request->session_type,
            $request->duration_minutes,
            $request->complexity
        );

        return response()->json([
            'estimated_cost' => $cost,
            'user_balance' => auth()->user()->credits_balance,
            'sufficient_balance' => auth()->user()->credits_balance >= $cost
        ]);
    }
}
