<?php

namespace App\Jobs;

use App\Models\Subscription;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreditMonthlyAllowanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     *
     * Renouvelle les crédits mensuels pour tous les abonnements actifs.
     * Ce job devrait être exécuté via le scheduler tous les mois.
     */
    public function handle(): void
    {
        Log::info('[CreditMonthlyAllowance] Début du renouvellement mensuel des crédits');

        // Récupérer tous les abonnements actifs
        $activeSubscriptions = Subscription::where('status', 'active')
            ->with('user')
            ->get();

        if ($activeSubscriptions->isEmpty()) {
            Log::info('[CreditMonthlyAllowance] Aucun abonnement actif trouvé');
            return;
        }

        $successCount = 0;
        $errorCount = 0;

        foreach ($activeSubscriptions as $subscription) {
            try {
                // Vérifier que l'abonnement n'a pas expiré
                if ($subscription->hasEnded()) {
                    Log::info('[CreditMonthlyAllowance] Abonnement expiré ignoré', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $subscription->user_id,
                    ]);
                    continue;
                }

                $user = $subscription->user;

                if (!$user) {
                    Log::error('[CreditMonthlyAllowance] Utilisateur introuvable', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $subscription->user_id,
                    ]);
                    $errorCount++;
                    continue;
                }

                // Déterminer le nombre de crédits à ajouter
                $creditsToAdd = $subscription->credits_per_month;

                if ($creditsToAdd <= 0) {
                    Log::warning('[CreditMonthlyAllowance] Abonnement avec 0 crédits', [
                        'subscription_id' => $subscription->id,
                        'plan' => $subscription->plan,
                    ]);
                    continue;
                }

                // Ajouter les crédits en transaction
                DB::transaction(function () use ($user, $creditsToAdd, $subscription) {
                    $user->addCredits(
                        amount: $creditsToAdd,
                        type: 'subscription_renewal',
                        description: "Renouvellement mensuel - Plan {$subscription->getPlanName()}",
                        reference: $subscription
                    );
                });

                Log::info('[CreditMonthlyAllowance] Crédits renouvelés', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $user->id,
                    'plan' => $subscription->plan,
                    'credits_added' => $creditsToAdd,
                    'new_balance' => $user->fresh()->credits_balance,
                ]);

                $successCount++;

                // TODO: Envoyer notification à l'utilisateur
                // Notification::send($user, new MonthlyCreditsRenewedNotification($creditsToAdd));

            } catch (\Exception $e) {
                Log::error('[CreditMonthlyAllowance] Erreur lors du renouvellement', [
                    'subscription_id' => $subscription->id,
                    'user_id' => $subscription->user_id ?? null,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                $errorCount++;
            }
        }

        Log::info('[CreditMonthlyAllowance] Renouvellement terminé', [
            'total_subscriptions' => $activeSubscriptions->count(),
            'success_count' => $successCount,
            'error_count' => $errorCount,
        ]);
    }

    /**
     * Tags pour Horizon (si utilisé)
     */
    public function tags(): array
    {
        return ['subscription', 'credits', 'monthly-allowance'];
    }
}
