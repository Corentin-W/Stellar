<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SubscriptionController extends Controller
{
    /**
     * Get available plans
     */
    public function plans(): JsonResponse
    {
        $plans = [
            [
                'id' => Subscription::STARDUST,
                'name' => 'Stardust',
                'badge' => '🌟',
                'price' => Subscription::PRICES[Subscription::STARDUST],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::STARDUST],
                'features' => [
                    'Priority Low (0-1)',
                    '20 crédits/mois',
                    'Accès RoboTarget',
                ],
                'restrictions' => [
                    'Pas de nuit noire',
                    'Pas de garantie HFD',
                    'One-shot uniquement',
                ],
            ],
            [
                'id' => Subscription::NEBULA,
                'name' => 'Nebula',
                'badge' => '🌌',
                'price' => Subscription::PRICES[Subscription::NEBULA],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::NEBULA],
                'popular' => true,
                'features' => [
                    'Priority Normal (0-2)',
                    '60 crédits/mois',
                    'Option Nuit noire',
                    'Dashboard temps réel',
                    'Projets multi-nuits',
                ],
                'restrictions' => [
                    'HFD fixe à 4.0',
                ],
            ],
            [
                'id' => Subscription::QUASAR,
                'name' => 'Quasar',
                'badge' => '⚡',
                'price' => Subscription::PRICES[Subscription::QUASAR],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::QUASAR],
                'features' => [
                    'Priority First (0-4) - Coupe-file',
                    '150 crédits/mois',
                    'Nuit noire incluse',
                    'Garantie netteté HFD ajustable',
                    'Gestion avancée Sets',
                    'Projets multi-nuits',
                ],
                'restrictions' => [],
            ],
        ];

        return response()->json([
            'success' => true,
            'plans' => $plans,
        ]);
    }

    /**
     * Get current user subscription
     */
    public function current(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun abonnement actif',
                'subscription' => null,
            ]);
        }

        return response()->json([
            'success' => true,
            'subscription' => [
                'plan' => $subscription->plan,
                'plan_name' => $subscription->getPlanName(),
                'badge' => $subscription->getPlanBadge(),
                'credits_per_month' => $subscription->credits_per_month,
                'status' => $subscription->status,
                'is_active' => $subscription->isActive(),
                'is_on_trial' => $subscription->isOnTrial(),
                'trial_ends_at' => $subscription->trial_ends_at,
                'ends_at' => $subscription->ends_at,
                'permissions' => [
                    'max_priority' => $subscription->getMaxPriority(),
                    'can_use_moon_down' => $subscription->canUseMoonDown(),
                    'can_adjust_hfd' => $subscription->canAdjustHFD(),
                    'can_use_repeat' => $subscription->canUseRepeat(),
                    'can_manage_sets' => $subscription->canManageSets(),
                ],
            ],
            'credits_balance' => $user->credits_balance,
            'legacy_credits' => $user->legacy_credits ?? 0,
        ]);
    }

    /**
     * Subscribe to a plan
     */
    public function subscribe(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|in:stardust,nebula,quasar',
            'payment_method_id' => 'nullable|string', // Stripe payment method
        ]);

        $user = $request->user();

        // Vérifier si l'utilisateur a déjà un abonnement actif
        if ($user->subscription && $user->subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Vous avez déjà un abonnement actif',
            ], 400);
        }

        try {
            // Créer l'abonnement
            $subscription = Subscription::create([
                'user_id' => $user->id,
                'plan' => $validated['plan'],
                'credits_per_month' => Subscription::CREDITS_PER_PLAN[$validated['plan']],
                'status' => 'trial', // 7 jours d'essai
                'trial_ends_at' => now()->addDays(7),
            ]);

            // Ajouter les crédits du premier mois
            $user->increment('credits_balance', $subscription->credits_per_month);

            // TODO: Intégration Stripe pour le paiement récurrent
            // if ($validated['payment_method_id']) {
            //     $user->createOrGetStripeCustomer();
            //     $user->newSubscription('default', $stripePrice)->create($validated['payment_method_id']);
            // }

            return response()->json([
                'success' => true,
                'message' => 'Abonnement créé avec succès',
                'subscription' => [
                    'plan' => $subscription->plan,
                    'plan_name' => $subscription->getPlanName(),
                    'credits_per_month' => $subscription->credits_per_month,
                    'status' => $subscription->status,
                    'trial_ends_at' => $subscription->trial_ends_at,
                ],
                'credits_balance' => $user->credits_balance,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la création de l\'abonnement',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change subscription plan
     */
    public function changePlan(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'new_plan' => 'required|in:stardust,nebula,quasar',
        ]);

        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun abonnement actif',
            ], 400);
        }

        $oldPlan = $subscription->plan;
        $newPlan = $validated['new_plan'];

        if ($oldPlan === $newPlan) {
            return response()->json([
                'success' => false,
                'message' => 'Vous êtes déjà sur ce plan',
            ], 400);
        }

        try {
            // Mettre à jour le plan
            $subscription->update([
                'plan' => $newPlan,
                'credits_per_month' => Subscription::CREDITS_PER_PLAN[$newPlan],
            ]);

            // TODO: Mettre à jour l'abonnement Stripe
            // $user->subscription('default')->swap($newStripePrice);

            return response()->json([
                'success' => true,
                'message' => 'Plan modifié avec succès',
                'subscription' => [
                    'old_plan' => $oldPlan,
                    'new_plan' => $subscription->plan,
                    'credits_per_month' => $subscription->credits_per_month,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors du changement de plan',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel subscription
     */
    public function cancel(Request $request): JsonResponse
    {
        $user = $request->user();
        $subscription = $user->subscription;

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'Aucun abonnement actif',
            ], 400);
        }

        try {
            // Marquer comme annulé (mais accès conservé jusqu'à la fin de période)
            $subscription->update([
                'status' => 'cancelled',
                'ends_at' => now()->endOfMonth(), // Fin du mois en cours
            ]);

            // TODO: Annuler dans Stripe
            // $user->subscription('default')->cancel();

            return response()->json([
                'success' => true,
                'message' => 'Abonnement annulé. Accès conservé jusqu\'au ' . $subscription->ends_at->format('d/m/Y'),
                'subscription' => [
                    'plan' => $subscription->plan,
                    'status' => $subscription->status,
                    'ends_at' => $subscription->ends_at,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
