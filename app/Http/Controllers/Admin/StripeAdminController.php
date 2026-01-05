<?php

namespace App\Http\Controllers\Admin;

use App\Models\Subscription;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class StripeAdminController extends Controller
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(env('STRIPE_SECRET'));
    }

    /**
     * Page principale de gestion Stripe
     */
    public function index()
    {
        // Récupérer les Price IDs configurés
        $configuredPrices = [
            'stardust' => config('cashier.price_ids.stardust'),
            'nebula' => config('cashier.price_ids.nebula'),
            'quasar' => config('cashier.price_ids.quasar'),
        ];

        // Statistiques des abonnements
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', 'active')->count(),
            'without_plan' => Subscription::where(function($q) {
                $q->whereNull('plan')->orWhere('plan', '');
            })->count(),
            'duplicates' => $this->countDuplicates(),
        ];

        // Récupérer les prix depuis Stripe
        $stripePrices = [];
        try {
            $prices = $this->stripe->prices->all(['limit' => 100, 'active' => true]);
            foreach ($prices->data as $price) {
                if ($price->type === 'recurring' && $price->recurring->interval === 'month') {
                    $stripePrices[] = [
                        'id' => $price->id,
                        'amount' => $price->unit_amount / 100,
                        'currency' => strtoupper($price->currency),
                        'product' => $price->product,
                    ];
                }
            }
        } catch (\Exception $e) {
            Log::error('Failed to fetch Stripe prices', ['error' => $e->getMessage()]);
        }

        return view('admin.stripe.index', compact('configuredPrices', 'stats', 'stripePrices'));
    }

    /**
     * Synchroniser les Price IDs depuis Stripe
     */
    public function syncPrices(Request $request)
    {
        try {
            $validated = $request->validate([
                'stardust' => 'nullable|string|starts_with:price_',
                'nebula' => 'nullable|string|starts_with:price_',
                'quasar' => 'nullable|string|starts_with:price_',
            ]);

            // Mettre à jour le fichier .env
            $this->updateEnvFile([
                'STRIPE_PRICE_STARDUST' => $validated['stardust'] ?? '',
                'STRIPE_PRICE_NEBULA' => $validated['nebula'] ?? '',
                'STRIPE_PRICE_QUASAR' => $validated['quasar'] ?? '',
            ]);

            // Vider le cache de config
            Artisan::call('config:clear');

            return redirect()
                ->route('admin.stripe.index')
                ->with('success', 'Price IDs mis à jour avec succès!');

        } catch (\Exception $e) {
            Log::error('Failed to sync prices', ['error' => $e->getMessage()]);
            return redirect()
                ->back()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Nettoyer les abonnements dupliqués
     */
    public function cleanDuplicates(Request $request)
    {
        try {
            $deleted = 0;

            // Trouver les stripe_id en double
            $duplicates = DB::table('subscriptions')
                ->select('stripe_id', DB::raw('COUNT(*) as count'))
                ->whereNotNull('stripe_id')
                ->where('stripe_id', '!=', '')
                ->groupBy('stripe_id')
                ->having('count', '>', 1)
                ->get();

            foreach ($duplicates as $duplicate) {
                $subscriptions = Subscription::where('stripe_id', $duplicate->stripe_id)
                    ->orderBy('id')
                    ->get();

                // Garder le premier, supprimer les autres
                $toDelete = $subscriptions->skip(1);
                foreach ($toDelete as $sub) {
                    $sub->delete();
                    $deleted++;
                }
            }

            return redirect()
                ->route('admin.stripe.index')
                ->with('success', "{$deleted} doublon(s) supprimé(s)");

        } catch (\Exception $e) {
            Log::error('Failed to clean duplicates', ['error' => $e->getMessage()]);
            return redirect()
                ->back()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Synchroniser les abonnements depuis Stripe
     */
    public function syncSubscriptions(Request $request)
    {
        try {
            $updated = 0;
            $errors = 0;

            $subscriptions = Subscription::where(function($q) {
                $q->whereNull('plan')->orWhere('plan', '');
            })->get();

            foreach ($subscriptions as $subscription) {
                try {
                    $user = $subscription->user;
                    if (!$user || !$user->stripe_id) {
                        continue;
                    }

                    // Récupérer les abonnements Stripe
                    $stripeSubscriptions = $this->stripe->subscriptions->all([
                        'customer' => $user->stripe_id,
                        'limit' => 10
                    ]);

                    if (count($stripeSubscriptions->data) === 0) {
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

                    if (!$stripeSubscription && count($stripeSubscriptions->data) > 0) {
                        $stripeSubscription = $stripeSubscriptions->data[0];
                    }

                    if (!$stripeSubscription) {
                        continue;
                    }

                    // Extraire les informations
                    $updateData = [
                        'stripe_status' => $stripeSubscription->status,
                    ];

                    // Récupérer le plan depuis les métadonnées
                    if (isset($stripeSubscription->metadata['plan'])) {
                        $plan = $stripeSubscription->metadata['plan'];
                        $updateData['plan'] = $plan;
                        if (isset(Subscription::CREDITS_PER_PLAN[$plan])) {
                            $updateData['credits_per_month'] = Subscription::CREDITS_PER_PLAN[$plan];
                        }
                    }
                    // Sinon, déduire depuis le price ID
                    elseif (isset($stripeSubscription->items->data[0]->price->id)) {
                        $priceId = $stripeSubscription->items->data[0]->price->id;
                        $updateData['stripe_price'] = $priceId;

                        $plan = $this->getPlanFromPriceId($priceId);
                        if ($plan) {
                            $updateData['plan'] = $plan;
                            $updateData['credits_per_month'] = Subscription::CREDITS_PER_PLAN[$plan];
                        }
                    }

                    $subscription->update($updateData);
                    $updated++;

                } catch (\Exception $e) {
                    $errors++;
                    Log::error('Failed to sync subscription', [
                        'subscription_id' => $subscription->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            $message = "{$updated} abonnement(s) synchronisé(s)";
            if ($errors > 0) {
                $message .= " ({$errors} erreur(s))";
            }

            return redirect()
                ->route('admin.stripe.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Failed to sync subscriptions', ['error' => $e->getMessage()]);
            return redirect()
                ->back()
                ->with('error', 'Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Compter les doublons
     */
    private function countDuplicates(): int
    {
        return DB::table('subscriptions')
            ->select('stripe_id', DB::raw('COUNT(*) as count'))
            ->whereNotNull('stripe_id')
            ->where('stripe_id', '!=', '')
            ->groupBy('stripe_id')
            ->having('count', '>', 1)
            ->count();
    }

    /**
     * Obtenir le plan depuis le Price ID
     */
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

        return null;
    }

    /**
     * Mettre à jour le fichier .env
     */
    private function updateEnvFile(array $data): void
    {
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);

        foreach ($data as $key => $value) {
            // Échapper les valeurs
            $value = str_replace('"', '\"', $value);

            // Si la clé existe, la remplacer
            if (preg_match("/^{$key}=/m", $envContent)) {
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}=\"{$value}\"",
                    $envContent
                );
            } else {
                // Sinon, l'ajouter à la fin
                $envContent .= "\n{$key}=\"{$value}\"";
            }
        }

        file_put_contents($envFile, $envContent);
    }
}
