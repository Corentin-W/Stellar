<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SubscriptionController extends Controller
{
    /**
     * Afficher la page de choix de plan
     */
    public function choose(Request $request): View
    {
        $user = $request->user();

        $plans = $this->getPlansData();

        // Données supplémentaires si l'utilisateur a déjà un abonnement
        $invoices = [];
        $usageHistory = null;

        if ($user->subscription) {
            // Pour l'instant, factures de démonstration
            $invoices = $this->getDemoInvoices($user->subscription);

            // Historique d'utilisation des crédits
            $usageHistory = [
                'total_used' => $user->credit_transactions()->where('type', 'hold')->sum('amount'),
                'total_refunded' => $user->credit_transactions()->where('type', 'refund')->sum('amount'),
                'current_balance' => $user->credits_balance,
            ];
        }

        return view('subscriptions.choose', [
            'plans' => $plans,
            'currentSubscription' => $user->subscription,
            'user' => $user,
            'invoices' => $invoices,
            'usageHistory' => $usageHistory,
        ]);
    }

    /**
     * Obtenir les données détaillées des plans
     */
    private function getPlansData(): array
    {
        return [
            [
                'id' => Subscription::STARDUST,
                'name' => 'Stardust',
                'badge' => '🌟',
                'price' => Subscription::PRICES[Subscription::STARDUST],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::STARDUST],
                'description' => 'Idéal pour débuter avec RoboTarget',
                'tagline' => 'Parfait pour tester notre télescope robotisé',
                'features' => [
                    'Priority Low (0-1)' => 'Vos targets seront traitées en priorité basse',
                    '20 crédits/mois' => 'Environ 20h d\'observation par mois',
                    'Accès RoboTarget' => 'Interface web complète de gestion',
                    'Mode One-Shot uniquement' => 'Une session par target, idéal pour débuter',
                    'Dashboard temps réel' => 'Suivez vos acquisitions en direct',
                ],
                'restrictions' => [
                    'Pas de nuit noire' => 'Les sessions peuvent inclure la lune',
                    'Pas de garantie HFD' => 'Pas de garantie de netteté',
                    'Pas de projets multi-nuits' => 'Une seule session par target',
                ],
                'included' => [
                    'Support email standard',
                    'Stockage 30 jours',
                    'Téléchargement FITS',
                ],
            ],
            [
                'id' => Subscription::NEBULA,
                'name' => 'Nebula',
                'badge' => '🌌',
                'price' => Subscription::PRICES[Subscription::NEBULA],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::NEBULA],
                'popular' => true,
                'description' => 'Le choix des amateurs passionnés',
                'tagline' => 'Pour des images de qualité professionnelle',
                'features' => [
                    'Priority Normal (0-2)' => 'Priorité normale à élevée pour vos sessions',
                    '60 crédits/mois' => 'Environ 60h d\'observation par mois',
                    'Option Nuit noire 🌙' => 'Acquisition sans pollution lunaire (×2 crédits)',
                    'Projets multi-nuits' => 'Répétez vos sessions plusieurs nuits',
                    'HFD fixe à 4.0 ⭐' => 'Garantie de netteté standard',
                    'Dashboard avancé' => 'Statistiques et graphiques détaillés',
                ],
                'restrictions' => [],
                'included' => [
                    'Support prioritaire',
                    'Stockage 90 jours',
                    'Téléchargement FITS + PNG',
                    'Historique complet',
                ],
            ],
            [
                'id' => Subscription::QUASAR,
                'name' => 'Quasar',
                'badge' => '⚡',
                'price' => Subscription::PRICES[Subscription::QUASAR],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::QUASAR],
                'description' => 'Pour les astrophotographes experts',
                'tagline' => 'Contrôle total et qualité maximale',
                'features' => [
                    'Priority First (0-4) 🏆' => 'Coupe-file complet, priorité maximale',
                    '150 crédits/mois' => 'Environ 150h d\'observation par mois',
                    'Nuit noire incluse 🌙' => 'Sans surcoût - qualité optimale garantie',
                    'HFD ajustable (1.5-4.0) ⭐⭐⭐' => 'Contrôle précis de la netteté',
                    'Gestion avancée Sets' => 'Organisez vos acquisitions en projets',
                    'Projets multi-nuits illimités' => 'Répétez autant que nécessaire',
                    'Support prioritaire 24/7' => 'Réponse garantie sous 2h',
                ],
                'restrictions' => [],
                'included' => [
                    'Support dédié 24/7',
                    'Stockage illimité',
                    'Tous formats (FITS, PNG, TIFF)',
                    'API avancée',
                    'Pré-traitement optionnel',
                ],
            ],
        ];
    }

    /**
     * Générer des factures de démonstration
     */
    private function getDemoInvoices($subscription): array
    {
        return [
            [
                'id' => 'INV-' . now()->format('Ym') . '-001',
                'date' => now()->startOfMonth(),
                'amount' => Subscription::PRICES[$subscription->plan],
                'status' => 'paid',
                'description' => 'Abonnement ' . $subscription->getPlanName() . ' - ' . now()->format('F Y'),
            ],
            [
                'id' => 'INV-' . now()->subMonth()->format('Ym') . '-001',
                'date' => now()->subMonth()->startOfMonth(),
                'amount' => Subscription::PRICES[$subscription->plan],
                'status' => 'paid',
                'description' => 'Abonnement ' . $subscription->getPlanName() . ' - ' . now()->subMonth()->format('F Y'),
            ],
        ];
    }

    /**
     * Créer ou changer un abonnement avec Stripe
     */
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'plan' => 'required|in:stardust,nebula,quasar',
        ]);

        $user = $request->user();
        $newPlan = $validated['plan'];

        // Si l'utilisateur a déjà un abonnement
        if ($user->subscription && $user->subscription->isActive()) {
            return $this->switchPlan($user, $newPlan);
        }

        // Nouvel abonnement → Rediriger vers Stripe Checkout
        return $this->createCheckoutSession($user, $newPlan);
    }

    /**
     * Changer de plan pour un utilisateur déjà abonné
     */
    protected function switchPlan($user, $newPlan)
    {
        // Vérifier s'il s'agit du même plan
        if ($user->subscription->plan === $newPlan) {
            return redirect()
                ->route('subscriptions.choose', ['locale' => app()->getLocale()])
                ->with('info', 'Vous êtes déjà abonné à ce plan.');
        }

        $oldPlan = $user->subscription->getPlanName();
        $oldCredits = $user->subscription->credits_per_month;
        $newCredits = Subscription::CREDITS_PER_PLAN[$newPlan];

        // Si l'utilisateur a un abonnement Stripe existant
        if ($user->subscribed('default')) {
            // Utiliser Cashier pour changer le plan
            $stripePriceId = $this->getStripePriceId($newPlan);
            $user->subscription('default')->swap($stripePriceId);

            // Mettre à jour notre modèle personnalisé
            $user->subscription->update([
                'plan' => $newPlan,
                'credits_per_month' => $newCredits,
            ]);
        } else {
            // Fallback : mise à jour manuelle (mode démo)
            $user->subscription->update([
                'plan' => $newPlan,
                'credits_per_month' => $newCredits,
            ]);
        }

        // Ajuster les crédits (différence entre ancien et nouveau plan)
        $creditDifference = $newCredits - $oldCredits;
        if ($creditDifference > 0) {
            $user->increment('credits_balance', $creditDifference);
        } elseif ($creditDifference < 0) {
            $user->decrement('credits_balance', abs($creditDifference));
        }

        return redirect()
            ->route('subscriptions.choose', ['locale' => app()->getLocale()])
            ->with('success', "Votre plan a été changé de {$oldPlan} à " . Subscription::where('plan', $newPlan)->first()->getPlanName() . ". Vos crédits ont été ajustés.");
    }

    /**
     * Créer une session Stripe Checkout
     */
    protected function createCheckoutSession($user, $plan)
    {
        $stripePriceId = $this->getStripePriceId($plan);
        $planDetails = $this->getPlansData()[$this->getPlanIndex($plan)];

        try {
            $checkout = $user->newSubscription('default', $stripePriceId)
                ->trialDays(7) // 7 jours d'essai gratuit
                ->checkout([
                    'success_url' => route('subscriptions.success', ['locale' => app()->getLocale()]) . '?session_id={CHECKOUT_SESSION_ID}',
                    'cancel_url' => route('subscriptions.choose', ['locale' => app()->getLocale()]),
                    'metadata' => [
                        'plan' => $plan,
                        'credits_per_month' => Subscription::CREDITS_PER_PLAN[$plan],
                    ],
                ]);

            return redirect($checkout->url);
        } catch (\Exception $e) {
            \Log::error('Stripe Checkout Error: ' . $e->getMessage());

            return redirect()
                ->route('subscriptions.choose', ['locale' => app()->getLocale()])
                ->with('error', 'Erreur lors de la création de la session de paiement. Veuillez réessayer.');
        }
    }

    /**
     * Page de succès après paiement Stripe
     */
    public function success(Request $request)
    {
        $user = $request->user();

        // Récupérer la session Stripe
        $sessionId = $request->query('session_id');

        if (!$sessionId) {
            return redirect()->route('subscriptions.choose', ['locale' => app()->getLocale()]);
        }

        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            $plan = $session->metadata->plan ?? null;

            if ($plan && !$user->subscription) {
                // Créer notre modèle personnalisé Subscription
                Subscription::create([
                    'user_id' => $user->id,
                    'type' => 'default',
                    'plan' => $plan,
                    'credits_per_month' => Subscription::CREDITS_PER_PLAN[$plan],
                    'status' => 'active',
                    'stripe_id' => $session->subscription,
                    'stripe_status' => 'active',
                    'trial_ends_at' => now()->addDays(7),
                ]);

                // Ajouter les crédits initiaux
                $user->increment('credits_balance', Subscription::CREDITS_PER_PLAN[$plan]);
            }

            return redirect()
                ->route('robotarget.index', ['locale' => app()->getLocale()])
                ->with('success', 'Félicitations ! Votre abonnement est actif. Vous avez ' . Subscription::CREDITS_PER_PLAN[$plan] . ' crédits.');
        } catch (\Exception $e) {
            \Log::error('Stripe Session Retrieve Error: ' . $e->getMessage());

            return redirect()
                ->route('subscriptions.choose', ['locale' => app()->getLocale()])
                ->with('error', 'Erreur lors de la vérification du paiement.');
        }
    }

    /**
     * Obtenir le Price ID Stripe selon le plan
     */
    protected function getStripePriceId($plan): string
    {
        // À configurer avec vos vrais Price IDs Stripe
        return match($plan) {
            Subscription::STARDUST => env('STRIPE_PRICE_STARDUST', 'price_stardust_monthly'),
            Subscription::NEBULA => env('STRIPE_PRICE_NEBULA', 'price_nebula_monthly'),
            Subscription::QUASAR => env('STRIPE_PRICE_QUASAR', 'price_quasar_monthly'),
            default => throw new \Exception("Plan invalide: {$plan}"),
        };
    }

    /**
     * Obtenir l'index du plan dans le tableau
     */
    protected function getPlanIndex($plan): int
    {
        return match($plan) {
            Subscription::STARDUST => 0,
            Subscription::NEBULA => 1,
            Subscription::QUASAR => 2,
            default => 0,
        };
    }

    /**
     * Page de gestion de l'abonnement
     */
    public function manage(Request $request): View
    {
        $user = $request->user();
        $subscription = $user->subscription;

        return view('subscriptions.manage', [
            'subscription' => $subscription,
            'user' => $user,
        ]);
    }

    /**
     * Webhook Stripe pour gérer les événements d'abonnement
     */
    public function webhook(Request $request)
    {
        $endpoint_secret = config('cashier.webhook.secret');
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            // Payload invalide
            \Log::error('Stripe Webhook - Invalid payload: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid payload'], 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Signature invalide
            \Log::error('Stripe Webhook - Invalid signature: ' . $e->getMessage());
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        // Gérer l'événement
        switch ($event->type) {
            case 'customer.subscription.created':
            case 'customer.subscription.updated':
                $this->handleSubscriptionUpdate($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionCancelled($event->data->object);
                break;

            case 'invoice.paid':
                $this->handleInvoicePaid($event->data->object);
                break;

            case 'invoice.payment_failed':
                $this->handlePaymentFailed($event->data->object);
                break;

            default:
                \Log::info('Stripe Webhook - Unhandled event type: ' . $event->type);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Gérer la mise à jour d'un abonnement
     */
    protected function handleSubscriptionUpdate($stripeSubscription)
    {
        $user = \App\Models\User::where('stripe_id', $stripeSubscription->customer)->first();

        if (!$user) {
            \Log::warning('User not found for Stripe customer: ' . $stripeSubscription->customer);
            return;
        }

        // Mettre à jour le statut de l'abonnement
        if ($user->subscription) {
            $user->subscription->update([
                'stripe_status' => $stripeSubscription->status,
            ]);

            \Log::info("Subscription updated for user {$user->id}, status: {$stripeSubscription->status}");
        }
    }

    /**
     * Gérer l'annulation d'un abonnement
     */
    protected function handleSubscriptionCancelled($stripeSubscription)
    {
        $user = \App\Models\User::where('stripe_id', $stripeSubscription->customer)->first();

        if (!$user || !$user->subscription) {
            return;
        }

        $user->subscription->update([
            'status' => 'cancelled',
            'stripe_status' => 'canceled',
            'ends_at' => now(),
        ]);

        \Log::info("Subscription cancelled for user {$user->id}");

        // TODO: Envoyer un email de notification
    }

    /**
     * Gérer le paiement d'une facture (renouvellement mensuel)
     */
    protected function handleInvoicePaid($invoice)
    {
        $user = \App\Models\User::where('stripe_id', $invoice->customer)->first();

        if (!$user || !$user->subscription) {
            return;
        }

        // Renouveler les crédits mensuels
        $creditsPerMonth = $user->subscription->credits_per_month;

        // Remettre le solde au montant mensuel (pas d'ajout, juste reset)
        $user->update([
            'credits_balance' => $creditsPerMonth,
        ]);

        \Log::info("Credits renewed for user {$user->id}: {$creditsPerMonth} credits");

        // TODO: Envoyer un email de confirmation de renouvellement
    }

    /**
     * Gérer l'échec de paiement
     */
    protected function handlePaymentFailed($invoice)
    {
        $user = \App\Models\User::where('stripe_id', $invoice->customer)->first();

        if (!$user) {
            return;
        }

        \Log::warning("Payment failed for user {$user->id}, invoice {$invoice->id}");

        // TODO: Envoyer un email de notification d'échec de paiement
        // TODO: Optionnel - marquer l'abonnement comme "past_due"
    }
}
