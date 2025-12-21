<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckFeatureAccess
{
    /**
     * Handle an incoming request.
     *
     * Vérifie que l'utilisateur a accès à une fonctionnalité spécifique selon son plan.
     *
     * Usage dans les routes :
     * Route::middleware(['auth:sanctum', CheckFeatureAccess::class . ':moon_down'])->post('/targets');
     */
    public function handle(Request $request, Closure $next, ?string $feature = null): Response
    {
        $user = $request->user();

        // Si pas d'utilisateur authentifié, laisser passer (géré par auth middleware)
        if (!$user) {
            return $next($request);
        }

        // Les administrateurs ont accès à toutes les fonctionnalités
        if ($user->is_admin) {
            return $next($request);
        }

        $subscription = $user->subscription;

        // Si pas d'abonnement, bloquer
        if (!$subscription || !$subscription->isActive()) {
            return response()->json([
                'success' => false,
                'message' => 'Abonnement requis pour accéder à cette fonctionnalité',
                'error_code' => 'NO_ACTIVE_SUBSCRIPTION',
            ], 403);
        }

        // Si pas de feature spécifiée, juste vérifier l'abonnement actif
        if (!$feature) {
            return $next($request);
        }

        // Vérifier la feature demandée
        $hasAccess = match($feature) {
            'moon_down' => $subscription->canUseMoonDown(),
            'hfd_adjust' => $subscription->canAdjustHFD(),
            'repeat' => $subscription->canUseRepeat(),
            'sets' => $subscription->canManageSets(),
            default => false
        };

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => $this->getFeatureMessage($feature, $subscription->plan),
                'error_code' => 'FEATURE_NOT_AVAILABLE',
                'feature' => $feature,
                'current_plan' => $subscription->plan,
                'required_plans' => $this->getRequiredPlans($feature),
            ], 403);
        }

        return $next($request);
    }

    /**
     * Message d'erreur personnalisé selon la feature
     */
    protected function getFeatureMessage(string $feature, string $currentPlan): string
    {
        $featureLabels = [
            'moon_down' => 'Option Nuit Noire',
            'hfd_adjust' => 'Garantie Netteté HFD ajustable',
            'repeat' => 'Projets multi-nuits',
            'sets' => 'Gestion avancée des Sets',
        ];

        $label = $featureLabels[$feature] ?? $feature;

        return "{$label} n'est pas disponible avec votre plan {$currentPlan}. Veuillez upgrader votre abonnement.";
    }

    /**
     * Plans requis pour une feature
     */
    protected function getRequiredPlans(string $feature): array
    {
        return match($feature) {
            'moon_down' => ['nebula', 'quasar'],
            'hfd_adjust' => ['quasar'],
            'repeat' => ['nebula', 'quasar'],
            'sets' => ['quasar'],
            default => []
        };
    }
}
