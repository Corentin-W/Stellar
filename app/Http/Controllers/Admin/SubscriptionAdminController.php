<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SubscriptionAdminController extends Controller
{
    // Le middleware est géré dans routes/web.php via Route::middleware(['auth', 'admin'])
    // Plus besoin de constructeur dans Laravel 11

    /**
     * Dashboard principal des abonnements
     */
    public function dashboard()
    {
        $stats = $this->getSubscriptionStats();
        $recentSubscriptions = Subscription::with('user')
            ->latest()
            ->limit(10)
            ->get();

        $planDistribution = $this->getPlanDistribution();
        $monthlyRevenue = $this->getMonthlyRevenueChart();

        return view('admin.subscriptions.dashboard', compact(
            'stats',
            'recentSubscriptions',
            'planDistribution',
            'monthlyRevenue'
        ));
    }

    /**
     * Gestion des plans d'abonnement
     */
    public function plans()
    {
        $plans = $this->getPlansData();
        $stripeConfig = $this->getStripeConfiguration();

        return view('admin.subscriptions.plans', compact('plans', 'stripeConfig'));
    }

    /**
     * Mettre à jour la configuration Stripe d'un plan
     */
    public function updatePlanStripe(Request $request, string $plan)
    {
        $validated = $request->validate([
            'stripe_price_id' => 'required|string|starts_with:price_',
        ]);

        $envKey = 'STRIPE_PRICE_' . strtoupper($plan);

        try {
            // Mettre à jour le fichier .env
            $this->updateEnvFile($envKey, $validated['stripe_price_id']);

            return redirect()
                ->route('admin.subscriptions.plans')
                ->with('success', "Price ID Stripe mis à jour pour le plan {$plan}");

        } catch (\Exception $e) {
            \Log::error('Erreur mise à jour Stripe Price ID', [
                'plan' => $plan,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()]);
        }
    }

    /**
     * Créer automatiquement les plans dans Stripe
     */
    public function createStripePlans()
    {
        try {
            \Log::info('Admin initiated Stripe plans creation', [
                'admin_id' => auth()->id(),
            ]);

            // Exécuter la commande Artisan
            \Artisan::call('stripe:setup-plans');

            $output = \Artisan::output();

            // Vérifier si la commande a réussi
            if (str_contains($output, 'completed')) {
                return redirect()
                    ->route('admin.subscriptions.plans')
                    ->with('success', 'Plans Stripe créés avec succès ! Les Price IDs ont été automatiquement configurés.');
            } else {
                return redirect()
                    ->route('admin.subscriptions.plans')
                    ->withErrors(['error' => 'Une erreur est survenue lors de la création des plans. Vérifiez les logs.']);
            }

        } catch (\Exception $e) {
            \Log::error('Erreur création plans Stripe', [
                'admin_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la création des plans: ' . $e->getMessage()]);
        }
    }

    /**
     * Synchroniser avec Stripe
     */
    public function syncWithStripe()
    {
        try {
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));

            // Récupérer tous les abonnements Stripe
            $stripeSubscriptions = $stripe->subscriptions->all(['limit' => 100]);

            $syncedCount = 0;
            $errorsCount = 0;

            foreach ($stripeSubscriptions->data as $stripeSub) {
                try {
                    $user = User::where('stripe_id', $stripeSub->customer)->first();

                    if (!$user) {
                        continue;
                    }

                    // Mettre à jour ou créer l'abonnement local
                    Subscription::updateOrCreate(
                        ['stripe_id' => $stripeSub->id],
                        [
                            'user_id' => $user->id,
                            'type' => 'default',
                            'stripe_status' => $stripeSub->status,
                            'stripe_price' => $stripeSub->items->data[0]->price->id ?? null,
                        ]
                    );

                    $syncedCount++;

                } catch (\Exception $e) {
                    $errorsCount++;
                    \Log::error('Erreur sync abonnement', [
                        'subscription_id' => $stripeSub->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            return redirect()
                ->route('admin.subscriptions.dashboard')
                ->with('success', "Synchronisation terminée : {$syncedCount} abonnements synchronisés, {$errorsCount} erreurs");

        } catch (\Exception $e) {
            \Log::error('Erreur synchronisation Stripe', [
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de la synchronisation: ' . $e->getMessage()]);
        }
    }

    /**
     * Liste des utilisateurs abonnés
     */
    public function subscribers()
    {
        $subscribers = User::whereHas('subscription', function($query) {
            $query->where('status', 'active');
        })
        ->with('subscription')
        ->orderBy('created_at', 'desc')
        ->paginate(20);

        return view('admin.subscriptions.subscribers', compact('subscribers'));
    }

    /**
     * Détails d'un abonnement utilisateur
     */
    public function showSubscription(Subscription $subscription)
    {
        $subscription->load('user');

        $creditHistory = DB::table('credit_transactions')
            ->where('user_id', $subscription->user_id)
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return view('admin.subscriptions.show', compact('subscription', 'creditHistory'));
    }

    /**
     * Annuler un abonnement (admin)
     */
    public function cancelSubscription(Request $request, Subscription $subscription)
    {
        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        try {
            $user = $subscription->user;

            // Annuler dans Stripe si subscribed
            if ($user->subscribed('default')) {
                $user->subscription('default')->cancel();
            }

            // Mettre à jour localement
            $subscription->update([
                'status' => 'cancelled',
                'stripe_status' => 'canceled',
                'ends_at' => now(),
            ]);

            \Log::info('Admin cancelled subscription', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'reason' => $request->reason
            ]);

            return redirect()
                ->route('admin.subscriptions.subscribers')
                ->with('success', 'Abonnement annulé avec succès');

        } catch (\Exception $e) {
            \Log::error('Erreur annulation abonnement admin', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de l\'annulation: ' . $e->getMessage()]);
        }
    }

    /**
     * Ajuster manuellement les crédits d'un utilisateur
     */
    public function adjustCredits(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|integer|min:-10000|max:10000',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $oldBalance = $user->credits_balance;
            $newBalance = $oldBalance + $request->amount;

            $user->update(['credits_balance' => $newBalance]);

            \Log::info('Admin adjusted user credits', [
                'user_id' => $user->id,
                'admin_id' => auth()->id(),
                'old_balance' => $oldBalance,
                'adjustment' => $request->amount,
                'new_balance' => $newBalance,
                'reason' => $request->reason
            ]);

            return redirect()->back()
                ->with('success', "Ajustement de {$request->amount} crédits effectué avec succès");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withErrors(['error' => 'Erreur lors de l\'ajustement: ' . $e->getMessage()]);
        }
    }

    /**
     * Rapports et analytics
     */
    public function reports()
    {
        $dateStart = request('date_start', now()->subDays(30)->format('Y-m-d'));
        $dateEnd = request('date_end', now()->format('Y-m-d'));

        $stats = $this->getReportsData($dateStart, $dateEnd);

        return view('admin.subscriptions.reports', compact('stats', 'dateStart', 'dateEnd'));
    }

    // ==========================================
    // MÉTHODES PRIVÉES - CALCULS ET STATS
    // ==========================================

    /**
     * Statistiques générales des abonnements
     */
    private function getSubscriptionStats(): array
    {
        $activeSubscriptions = Subscription::where('status', 'active')->count();
        $totalSubscriptions = Subscription::count();

        // MRR (Monthly Recurring Revenue)
        $mrr = Subscription::where('status', 'active')
            ->get()
            ->sum(function ($sub) {
                return Subscription::PRICES[$sub->plan] ?? 0;
            });

        // Nouveaux abonnements ce mois
        $newThisMonth = Subscription::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // Annulations ce mois
        $cancelledThisMonth = Subscription::where('status', 'cancelled')
            ->whereMonth('updated_at', now()->month)
            ->whereYear('updated_at', now()->year)
            ->count();

        // Churn rate (taux d'annulation)
        $churnRate = $totalSubscriptions > 0
            ? round(($cancelledThisMonth / max($totalSubscriptions, 1)) * 100, 2)
            : 0;

        // Distribution par plan
        $planCounts = Subscription::where('status', 'active')
            ->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();

        return [
            'active_subscriptions' => $activeSubscriptions,
            'total_subscriptions' => $totalSubscriptions,
            'mrr' => $mrr,
            'new_this_month' => $newThisMonth,
            'cancelled_this_month' => $cancelledThisMonth,
            'churn_rate' => $churnRate,
            'stardust_count' => $planCounts[Subscription::STARDUST] ?? 0,
            'nebula_count' => $planCounts[Subscription::NEBULA] ?? 0,
            'quasar_count' => $planCounts[Subscription::QUASAR] ?? 0,
            'trial_subscriptions' => Subscription::where('stripe_status', 'trialing')->count(),
            'past_due_subscriptions' => Subscription::where('stripe_status', 'past_due')->count(),
        ];
    }

    /**
     * Distribution des plans
     */
    private function getPlanDistribution(): array
    {
        $distribution = Subscription::where('status', 'active')
            ->select('plan', DB::raw('count(*) as count'))
            ->groupBy('plan')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->plan => $item->count];
            })
            ->toArray();

        return [
            Subscription::STARDUST => $distribution[Subscription::STARDUST] ?? 0,
            Subscription::NEBULA => $distribution[Subscription::NEBULA] ?? 0,
            Subscription::QUASAR => $distribution[Subscription::QUASAR] ?? 0,
        ];
    }

    /**
     * Revenu mensuel (graphique)
     */
    private function getMonthlyRevenueChart(int $months = 12): array
    {
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);

            $mrr = Subscription::where('status', 'active')
                ->whereYear('created_at', '<=', $date->year)
                ->whereMonth('created_at', '<=', $date->month)
                ->get()
                ->sum(function ($sub) {
                    return Subscription::PRICES[$sub->plan] ?? 0;
                });

            $data[] = [
                'month' => $date->format('M Y'),
                'mrr' => $mrr,
                'subscribers' => Subscription::where('status', 'active')
                    ->whereYear('created_at', '<=', $date->year)
                    ->whereMonth('created_at', '<=', $date->month)
                    ->count()
            ];
        }

        return $data;
    }

    /**
     * Données des plans
     */
    private function getPlansData(): array
    {
        return [
            [
                'id' => Subscription::STARDUST,
                'name' => 'Stardust',
                'price' => Subscription::PRICES[Subscription::STARDUST],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::STARDUST],
                'subscribers' => Subscription::where('plan', Subscription::STARDUST)
                    ->where('status', 'active')
                    ->count(),
                'mrr' => Subscription::where('plan', Subscription::STARDUST)
                    ->where('status', 'active')
                    ->count() * Subscription::PRICES[Subscription::STARDUST],
            ],
            [
                'id' => Subscription::NEBULA,
                'name' => 'Nebula',
                'price' => Subscription::PRICES[Subscription::NEBULA],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::NEBULA],
                'subscribers' => Subscription::where('plan', Subscription::NEBULA)
                    ->where('status', 'active')
                    ->count(),
                'mrr' => Subscription::where('plan', Subscription::NEBULA)
                    ->where('status', 'active')
                    ->count() * Subscription::PRICES[Subscription::NEBULA],
            ],
            [
                'id' => Subscription::QUASAR,
                'name' => 'Quasar',
                'price' => Subscription::PRICES[Subscription::QUASAR],
                'credits' => Subscription::CREDITS_PER_PLAN[Subscription::QUASAR],
                'subscribers' => Subscription::where('plan', Subscription::QUASAR)
                    ->where('status', 'active')
                    ->count(),
                'mrr' => Subscription::where('plan', Subscription::QUASAR)
                    ->where('status', 'active')
                    ->count() * Subscription::PRICES[Subscription::QUASAR],
            ],
        ];
    }

    /**
     * Configuration Stripe
     */
    private function getStripeConfiguration(): array
    {
        return [
            'stardust' => env('STRIPE_PRICE_STARDUST', 'Non configuré'),
            'nebula' => env('STRIPE_PRICE_NEBULA', 'Non configuré'),
            'quasar' => env('STRIPE_PRICE_QUASAR', 'Non configuré'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET') ? '✓ Configuré' : '✗ Non configuré',
            'stripe_key' => env('STRIPE_KEY') ? '✓ Configuré' : '✗ Non configuré',
        ];
    }

    /**
     * Données pour les rapports
     */
    private function getReportsData(string $dateStart, string $dateEnd): array
    {
        $start = Carbon::parse($dateStart);
        $end = Carbon::parse($dateEnd);

        $newSubscriptions = Subscription::whereBetween('created_at', [$start, $end])->count();
        $cancelledSubscriptions = Subscription::where('status', 'cancelled')
            ->whereBetween('updated_at', [$start, $end])
            ->count();

        $revenue = Subscription::where('status', 'active')
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->sum(function ($sub) {
                return Subscription::PRICES[$sub->plan] ?? 0;
            });

        return [
            'new_subscriptions' => $newSubscriptions,
            'cancelled_subscriptions' => $cancelledSubscriptions,
            'net_growth' => $newSubscriptions - $cancelledSubscriptions,
            'revenue' => $revenue,
            'churn_rate' => $newSubscriptions > 0
                ? round(($cancelledSubscriptions / $newSubscriptions) * 100, 2)
                : 0,
        ];
    }

    /**
     * Mettre à jour le fichier .env
     */
    private function updateEnvFile(string $key, string $value): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        // Échapper les caractères spéciaux dans la valeur
        $value = str_replace('$', '\$', $value);

        // Si la clé existe, la remplacer
        if (preg_match("/^{$key}=.*/m", $envContent)) {
            $envContent = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $envContent
            );
        } else {
            // Sinon, l'ajouter à la fin
            $envContent .= "\n{$key}={$value}\n";
        }

        file_put_contents($envFile, $envContent);

        // Recharger la config
        \Artisan::call('config:clear');
    }
}
