<?php

// app/Http/Controllers/CreditController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CreditPackage;
use App\Models\Promotion;
use App\Models\CreditTransaction;
use App\Services\StripeService;
use App\Services\CreditService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreditService $creditService
    ) {
        // $this->middleware('auth');
    }

     /**
     * Boutique de crédits
     */
    public function shop()
    {
        $packages = CreditPackage::active()
                                ->ordered()
                                ->get();

        $promotions = Promotion::valid()
                              ->take(3)
                              ->get();

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
     * Créer un Payment Intent pour l'achat
     */
    public function createPaymentIntent(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:credit_packages,id',
            'promotion_code' => 'nullable|string|max:50'
        ]);

        $user = auth()->user();
        $package = CreditPackage::findOrFail($request->package_id);

        if (!$package->is_active) {
            return response()->json([
                'success' => false,
                'error' => 'Ce package n\'est plus disponible'
            ]);
        }

        $promotion = null;
        if ($request->promotion_code) {
            $promotion = Promotion::byCode($request->promotion_code)->first();

            if (!$promotion || !$promotion->canBeUsedBy($user) || !$promotion->isApplicableToPackage($package)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Code promotionnel invalide'
                ]);
            }
        }

        $result = $this->stripeService->createPaymentIntent($user, $package, $promotion);

        return response()->json($result);
    }

    /**
     * Confirmer le paiement
     */
    public function confirmPayment(Request $request)
    {
        $request->validate([
            'payment_intent_id' => 'required|string'
        ]);

        $result = $this->stripeService->confirmPayment($request->payment_intent_id);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Paiement confirmé ! Vos crédits ont été ajoutés.',
                'credits_added' => $result['credits_added'],
                'new_balance' => $result['new_balance'],
                'redirect_url' => route('credits.success')
            ]);
        }

        return response()->json($result);
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
            'total_purchased' => CreditTransaction::forUser($user->id)
                                                 ->purchases()
                                                 ->sum('credits_amount'),
            'total_used' => abs(CreditTransaction::forUser($user->id)
                                                ->usage()
                                                ->sum('credits_amount')),
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

    /**
     * Page de succès après achat
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
     * Webhook Stripe
     */
    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('Stripe-Signature');

        if (!$signature) {
            Log::warning('Stripe webhook called without signature');
            return response('Missing signature', 400);
        }

        $result = $this->stripeService->handleWebhook(
            json_decode($payload, true),
            $signature
        );

        if ($result['success']) {
            return response('Webhook handled');
        }

        Log::error('Stripe webhook handling failed', $result);
        return response('Webhook handling failed', 400);
    }
}
