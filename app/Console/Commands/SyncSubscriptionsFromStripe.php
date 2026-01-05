<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Console\Command;
use Stripe\StripeClient;

class SyncSubscriptionsFromStripe extends Command
{
    protected $signature = 'subscriptions:sync
                          {--user= : Synchroniser uniquement un utilisateur spécifique (ID)}
                          {--fix-plan : Mettre à jour le plan pour les abonnements sans plan}';

    protected $description = 'Synchroniser les abonnements depuis Stripe vers la base de données locale';

    private StripeClient $stripe;

    public function __construct()
    {
        parent::__construct();
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    public function handle()
    {
        $this->info('🔄 Synchronisation des abonnements depuis Stripe...');

        $query = \App\Models\Subscription::query();

        // Filtrer par utilisateur si spécifié
        if ($userId = $this->option('user')) {
            $query->where('user_id', $userId);
            $this->info("Filtrage: utilisateur ID {$userId}");
        }

        // Si option --fix-plan, filtrer uniquement les abonnements sans plan
        if ($this->option('fix-plan')) {
            $query->where(function ($q) {
                $q->whereNull('plan')
                  ->orWhere('plan', '');
            });
            $this->info("Filtrage: abonnements sans plan uniquement");
        }

        $subscriptions = $query->get();
        $this->info("📊 {$subscriptions->count()} abonnement(s) à traiter\n");

        $updated = 0;
        $errors = 0;

        foreach ($subscriptions as $subscription) {
            try {
                $user = $subscription->user;
                if (!$user || !$user->stripe_id) {
                    $this->warn("⚠️  Abonnement #{$subscription->id}: utilisateur sans stripe_id");
                    continue;
                }

                $this->line("Processing abonnement #{$subscription->id} (User: {$user->email})");

                // Récupérer les abonnements Stripe de l'utilisateur
                $stripeSubscriptions = $this->stripe->subscriptions->all([
                    'customer' => $user->stripe_id,
                    'limit' => 10
                ]);

                if (count($stripeSubscriptions->data) === 0) {
                    $this->warn("  ⚠️  Aucun abonnement Stripe trouvé");
                    continue;
                }

                // Trouver l'abonnement actif
                $stripeSubscription = null;
                foreach ($stripeSubscriptions->data as $sub) {
                    if ($sub->status === 'active' || $sub->status === 'trialing') {
                        $stripeSubscription = $sub;
                        break;
                    }
                }

                // Si pas d'abonnement actif, prendre le plus récent
                if (!$stripeSubscription && count($stripeSubscriptions->data) > 0) {
                    $stripeSubscription = $stripeSubscriptions->data[0];
                }

                if (!$stripeSubscription) {
                    $this->warn("  ⚠️  Aucun abonnement valide trouvé");
                    continue;
                }

                // Extraire les informations
                $updateData = [
                    'stripe_id' => $stripeSubscription->id,
                    'stripe_status' => $stripeSubscription->status,
                ];

                // Récupérer le plan depuis les métadonnées
                if (isset($stripeSubscription->metadata['plan'])) {
                    $plan = $stripeSubscription->metadata['plan'];
                    $updateData['plan'] = $plan;

                    // Mettre à jour les crédits basés sur le plan
                    if (isset(Subscription::CREDITS_PER_PLAN[$plan])) {
                        $updateData['credits_per_month'] = Subscription::CREDITS_PER_PLAN[$plan];
                    }

                    $this->info("  ✅ Plan trouvé dans métadonnées: {$plan}");
                }
                // Sinon, essayer de déduire depuis le price ID
                elseif (isset($stripeSubscription->items->data[0]->price->id)) {
                    $priceId = $stripeSubscription->items->data[0]->price->id;
                    $updateData['stripe_price'] = $priceId;

                    $plan = $this->getPlanFromPriceId($priceId);
                    if ($plan) {
                        $updateData['plan'] = $plan;
                        $updateData['credits_per_month'] = Subscription::CREDITS_PER_PLAN[$plan];
                        $this->info("  ✅ Plan déduit du price ID: {$plan}");
                    } else {
                        $this->warn("  ⚠️  Impossible de déterminer le plan (price ID: {$priceId})");
                    }
                }

                // Mettre à jour l'abonnement local
                $subscription->update($updateData);
                $updated++;

                $plan = $updateData['plan'] ?? 'N/A';
                $status = $updateData['stripe_status'];
                $this->info("  ✨ Synchronisé: plan={$plan}, status={$status}\n");

            } catch (\Exception $e) {
                $errors++;
                $this->error("  ❌ Erreur: {$e->getMessage()}\n");
            }
        }

        $this->newLine();
        $this->info("✅ Synchronisation terminée!");
        $this->info("   • {$updated} abonnement(s) mis à jour");
        if ($errors > 0) {
            $this->warn("   • {$errors} erreur(s)");
        }

        return 0;
    }

    private function getPlanFromPriceId(string $priceId): ?string
    {
        $priceIds = [
            Subscription::STARDUST => config('cashier.price_ids.stardust'),
            Subscription::NEBULA => config('cashier.price_ids.nebula'),
            Subscription::QUASAR => config('cashier.price_ids.quasar'),
        ];

        foreach ($priceIds as $plan => $configuredPriceId) {
            if ($configuredPriceId && $priceId === $configuredPriceId) {
                return $plan;
            }
        }

        // Si aucun match, afficher les price IDs configurés pour debug
        $this->warn("    Price IDs configurés:");
        foreach ($priceIds as $plan => $configPriceId) {
            $match = $configPriceId === $priceId ? '✓' : ' ';
            $this->warn("    [{$match}] {$plan}: " . ($configPriceId ?: 'non configuré'));
        }

        return null;
    }
}
