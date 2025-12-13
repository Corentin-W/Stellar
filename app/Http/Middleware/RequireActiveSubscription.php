<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveSubscription
{
    /**
     * Handle an incoming request.
     *
     * Vérifie que l'utilisateur a un abonnement RoboTarget actif.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Si pas d'utilisateur authentifié, laisser passer (géré par auth middleware)
        if (!$user) {
            return $next($request);
        }

        // Vérifier que l'utilisateur a un abonnement
        $subscription = $user->subscription;

        if (!$subscription) {
            // Si requête API, retourner JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun abonnement actif. Veuillez souscrire à un plan pour accéder à RoboTarget.',
                    'error_code' => 'NO_SUBSCRIPTION',
                    'redirect_url' => route('subscriptions.choose', ['locale' => app()->getLocale()]),
                ], 403);
            }

            // Requête web : rediriger vers la page de choix de plan
            return redirect()
                ->route('subscriptions.choose', ['locale' => app()->getLocale()])
                ->with('error', 'Vous devez souscrire à un abonnement RoboTarget pour accéder à cette fonctionnalité.');
        }

        // Vérifier que l'abonnement est actif
        if (!$subscription->isActive()) {
            // Si requête API, retourner JSON
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Votre abonnement n\'est pas actif.',
                    'error_code' => 'SUBSCRIPTION_INACTIVE',
                    'subscription_status' => $subscription->status,
                    'redirect_url' => route('subscriptions.manage', ['locale' => app()->getLocale()]),
                ], 403);
            }

            // Requête web : rediriger vers la gestion d'abonnement
            return redirect()
                ->route('subscriptions.manage', ['locale' => app()->getLocale()])
                ->with('error', 'Votre abonnement n\'est pas actif. Veuillez le réactiver.');
        }

        // Attacher l'abonnement à la requête pour éviter de le recharger
        $request->attributes->set('subscription', $subscription);

        return $next($request);
    }
}
