<?php

// app/Http/Controllers/CreditController.php

namespace App\Http\Controllers;

use App\Models\CreditPackage;
use App\Models\Promotion;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreditController extends Controller
{
    public function __construct(
        private CreditService $creditService
    ) {}

    /**
     * Afficher la boutique de crédits
     */
    public function shop()
    {
        $packages = CreditPackage::active()
                                ->ordered()
                                ->get();

        $featuredPackages = $packages->where('is_featured', true);
        $user = Auth::user();

        $recommendations = $user ? $this->creditService->getPackageRecommendations($user) : [];
        $userStats = $user ? $user->getCreditStats() : null;

        // Promotions actives
        $activePromotions = Promotion::valid()->get();

        return view('credits.shop', compact(
            'packages',
            'featuredPackages',
            'recommendations',
            'userStats',
            'activePromotions'
        ));
    }

    /**
     * Afficher les détails d'un package
     */
    public function packageDetails(CreditPackage $package)
    {
        if (!$package->is_active) {
            abort(404);
        }

        $user = Auth::user();
        $relatedPackages = CreditPackage::active()
                                      ->where('id', '!=', $package->id)
                                      ->ordered()
                                      ->limit(3)
                                      ->get();

        return view('credits.package-details', compact('package', 'relatedPackages'));
    }

    /**
     * Valider un code promo
     */
    public function validatePromotion(Request $request)
    {
        $request->validate([
            'code' => 'required|string|max:50',
            'package_id' => 'required|exists:credit_packages,id'
        ]);

        $package = CreditPackage::findOrFail($request->package_id);
        $user = Auth::user();

        $validation = $this->creditService->validatePromotionCode(
            $request->code,
            $user,
            $package
        );

        return response()->json($validation);
    }

    /**
     * Traiter l'achat de crédits
     */
    public function purchase(Request $request)
    {
        $request->validate([
            'package_id' => 'required|exists:credit_packages,id',
            'promotion_code' => 'nullable|string|max:50',
            'payment_method_id' => 'required|string'
        ]);

        $package = CreditPackage::findOrFail($request->package_id);
        $user = Auth::user();

        if (!$package->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Ce package n\'est plus disponible'
            ], 400);
        }

        $promotion = null;
        if ($request->promotion_code) {
            $promotionValidation = $this->creditService->validatePromotionCode(
                $request->promotion_code,
                $user,
                $package
            );

            if (!$promotionValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $promotionValidation['message']
                ], 400);
            }

            $promotion = $promotionValidation['promotion'];
        }

        try {
            $result = $this->creditService->purchaseCredits(
                $user,
                $package,
                $promotion,
                ['payment_method_id' => $request->payment_method_id]
            );

            return response()->json([
                'success' => true,
                'message' => 'Achat réussi ! Vos crédits ont été ajoutés à votre compte.',
                'data' => [
                    'credits_added' => $result['credits_added'],
                    'new_balance' => $result['new_balance'],
                    'transaction_id' => $result['transaction']->id,
                    'client_secret' => $result['payment_result']['client_secret']
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Historique des transactions de crédits
     */
    public function history()
    {
        $user = Auth::user();
        $transactions = $user->getCreditsHistory(100);
        $stats = $user->getCreditStats();
        $analytics = $this->creditService->getUserCreditAnalytics($user, 30);

        return view('credits.history', compact('transactions', 'stats', 'analytics'));
    }

    /**
     * API pour obtenir le solde actuel
     */
    public function balance()
    {
        $user = Auth::user();

        return response()->json([
            'balance' => $user->credits_balance,
            'stats' => $user->getCreditStats()
        ]);
    }

    /**
     * Estimation du coût d'une session
     */
    public function estimateSessionCost(Request $request)
    {
        $request->validate([
            'duration_minutes' => 'required|integer|min:1|max:480',
            'expected_images' => 'required|integer|min:0|max:1000',
            'telescope_id' => 'nullable|string'
        ]);

        $cost = $this->creditService->estimateSessionCost($request->all());

        return response()->json([
            'estimated_cost' => $cost,
            'breakdown' => [
                'base_cost' => 10,
                'duration_cost' => round($request->duration_minutes * 0.5),
                'image_cost' => $request->expected_images * 2
            ]
        ]);
    }
}

// ================================================

// app/Http/Controllers/Admin/CreditAdminController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\Promotion;
use App\Models\User;
use App\Services\CreditService;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CreditAdminController extends Controller
{
    public function __construct(
        private CreditService $creditService,
        private StripeService $stripeService
    ) {
        $this->middleware(['auth', 'admin']);
    }

    /**
     * Dashboard principal des crédits
     */
    public function dashboard()
    {
        $stats = $this->getCreditStats();
        $recentTransactions = CreditTransaction::with(['user', 'creditPackage'])
                                             ->latest()
                                             ->limit(10)
                                             ->get();

        $topBuyers = $this->getTopBuyers();
        $packageStats = $this->getPackageStats();

        return view('admin.credits.dashboard', compact(
            'stats',
            'recentTransactions',
            'topBuyers',
            'packageStats'
        ));
    }

    /**
     * Gestion des packages de crédits
     */
    public function packages()
    {
        $packages = CreditPackage::orderBy('sort_order')
                                ->orderBy('price_cents')
                                ->paginate(20);

        return view('admin.credits.packages.index', compact('packages'));
    }

    /**
     * Créer un nouveau package
     */
    public function createPackage()
    {
        return view('admin.credits.packages.create');
    }

    /**
     * Sauvegarder un nouveau package
     */
    public function storePackage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credits_amount' => 'required|integer|min:1|max:10000',
            'price_euros' => 'required|numeric|min:0.01|max:1000',
            'bonus_credits' => 'nullable|integer|min:0|max:1000',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $validated['price_cents'] = round($validated['price_euros'] * 100);
        unset($validated['price_euros']);

        $package = CreditPackage::create($validated);

        // Synchroniser avec Stripe
        try {
            $this->stripeService->syncPackagePrices();
        } catch (\Exception $e) {
            \Log::warning('Failed to sync package with Stripe: ' . $e->getMessage());
        }

        return redirect()->route('admin.credits.packages')
                        ->with('success', 'Package créé avec succès');
    }

    /**
     * Éditer un package
     */
    public function editPackage(CreditPackage $package)
    {
        return view('admin.credits.packages.edit', compact('package'));
    }

    /**
     * Mettre à jour un package
     */
    public function updatePackage(Request $request, CreditPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'credits_amount' => 'required|integer|min:1|max:10000',
            'price_euros' => 'required|numeric|min:0.01|max:1000',
            'bonus_credits' => 'nullable|integer|min:0|max:1000',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0'
        ]);

        $validated['price_cents'] = round($validated['price_euros'] * 100);
        unset($validated['price_euros']);

        $package->update($validated);

        return redirect()->route('admin.credits.packages')
                        ->with('success', 'Package mis à jour avec succès');
    }

    /**
     * Gestion des promotions
     */
    public function promotions()
    {
        $promotions = Promotion::orderBy('created_at', 'desc')->paginate(20);
        return view('admin.credits.promotions.index', compact('promotions'));
    }

    /**
     * Créer une nouvelle promotion
     */
    public function createPromotion()
    {
        $packages = CreditPackage::active()->get();
        return view('admin.credits.promotions.create', compact('packages'));
    }

    /**
     * Sauvegarder une nouvelle promotion
     */
    public function storePromotion(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:50|unique:promotions,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,bonus_credits',
            'value' => 'required|integer|min:1',
            'min_purchase_amount' => 'nullable|integer|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:credit_packages,id'
        ]);

        $validated['code'] = strtoupper($validated['code']);

        if ($validated['type'] === 'fixed_amount') {
            $validated['value'] = $validated['value'] * 100; // Convertir en centimes
        }

        Promotion::create($validated);

        return redirect()->route('admin.credits.promotions')
                        ->with('success', 'Promotion créée avec succès');
    }

    /**
     * Éditer une promotion
     */
    public function editPromotion(Promotion $promotion)
    {
        $packages = CreditPackage::active()->get();
        return view('admin.credits.promotions.edit', compact('promotion', 'packages'));
    }

    /**
     * Mettre à jour une promotion
     */
    public function updatePromotion(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:percentage,fixed_amount,bonus_credits',
            'value' => 'required|integer|min:1',
            'min_purchase_amount' => 'nullable|integer|min:0',
            'max_uses' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:credit_packages,id'
        ]);

        if ($validated['type'] === 'fixed_amount') {
            $validated['value'] = $validated['value'] * 100; // Convertir en centimes
        }

        $promotion->update($validated);

        return redirect()->route('admin.credits.promotions')
                        ->with('success', 'Promotion mise à jour avec succès');
    }

    /**
     * Gestion des utilisateurs et ajustements de crédits
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        if ($request->filter === 'with_credits') {
            $query->where('credits_balance', '>', 0);
        } elseif ($request->filter === 'big_buyers') {
            $query->where('total_credits_purchased', '>', 500);
        }

        $users = $query->orderBy('total_credits_purchased', 'desc')
                      ->paginate(20);

        return view('admin.credits.users.index', compact('users'));
    }

    /**
     * Détails d'un utilisateur
     */
    public function userDetails(User $user)
    {
        $transactions = $user->creditTransactions()
                            ->with(['creditPackage', 'createdBy'])
                            ->latest()
                            ->paginate(20);

        $analytics = $this->creditService->getUserCreditAnalytics($user, 90);
        $stats = $user->getCreditStats();

        return view('admin.credits.users.details', compact('user', 'transactions', 'analytics', 'stats'));
    }

    /**
     * Ajuster les crédits d'un utilisateur
     */
    public function adjustUserCredits(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:-10000|max:10000',
            'reason' => 'required|string|max:255'
        ]);

        try {
            if ($validated['amount'] > 0) {
                $user->addCredits(
                    $validated['amount'],
                    'admin_adjustment',
                    "Ajustement admin: " . $validated['reason'],
                    null,
                    null,
                    auth()->user()
                );
                $message = "Ajout de {$validated['amount']} crédits effectué";
            } else {
                $user->deductCredits(
                    abs($validated['amount']),
                    "Ajustement admin: " . $validated['reason']
                );
                $message = "Déduction de " . abs($validated['amount']) . " crédits effectuée";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'new_balance' => $user->fresh()->credits_balance
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Rapports et analytiques
     */
    public function reports(Request $request)
    {
        $period = $request->input('period', '30');
        $startDate = Carbon::now()->subDays($period);

        $revenueData = $this->getRevenueData($startDate);
        $usageData = $this->getUsageData($startDate);
        $conversionData = $this->getConversionData($startDate);

        return view('admin.credits.reports', compact(
            'period',
            'revenueData',
            'usageData',
            'conversionData'
        ));
    }

    /**
     * Export des données
     */
    public function exportTransactions(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth());
        $endDate = $request->input('end_date', now());

        $transactions = CreditTransaction::with(['user', 'creditPackage'])
                                        ->whereBetween('created_at', [$startDate, $endDate])
                                        ->orderBy('created_at', 'desc')
                                        ->get();

        $filename = 'credit_transactions_' . now()->format('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // Headers CSV
            fputcsv($file, [
                'ID',
                'Date',
                'Utilisateur',
                'Email',
                'Type',
                'Montant Crédits',
                'Solde Avant',
                'Solde Après',
                'Package',
                'Description',
                'Stripe Payment ID'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->user->name,
                    $transaction->user->email,
                    $transaction->formatted_type,
                    $transaction->credits_amount,
                    $transaction->balance_before,
                    $transaction->balance_after,
                    $transaction->creditPackage?->name,
                    $transaction->description,
                    $transaction->stripe_payment_intent_id
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ================================================
    // MÉTHODES PRIVÉES POUR STATISTIQUES
    // ================================================

    private function getCreditStats()
    {
        $totalUsers = User::whereHas('creditTransactions')->count();
        $totalRevenue = DB::table('credit_transactions')
                         ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                         ->where('credit_transactions.type', 'purchase')
                         ->sum('credit_packages.price_cents');

        $monthlyRevenue = DB::table('credit_transactions')
                           ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                           ->where('credit_transactions.type', 'purchase')
                           ->where('credit_transactions.created_at', '>=', now()->subMonth())
                           ->sum('credit_packages.price_cents');

        $creditsInCirculation = User::sum('credits_balance');
        $creditsConsumed = CreditTransaction::where('type', 'usage')->sum('credits_amount') * -1;

        return [
            'total_users' => $totalUsers,
            'total_revenue' => $totalRevenue / 100,
            'monthly_revenue' => $monthlyRevenue / 100,
            'credits_in_circulation' => $creditsInCirculation,
            'credits_consumed' => $creditsConsumed,
            'avg_credits_per_user' => $totalUsers > 0 ? round($creditsInCirculation / $totalUsers) : 0
        ];
    }

    private function getTopBuyers()
    {
        return User::orderBy('total_credits_purchased', 'desc')
                  ->where('total_credits_purchased', '>', 0)
                  ->limit(10)
                  ->get();
    }

    private function getPackageStats()
    {
        return CreditPackage::withCount(['transactions as sales_count' => function($query) {
                               $query->where('type', 'purchase');
                           }])
                           ->with(['transactions' => function($query) {
                               $query->where('type', 'purchase');
                           }])
                           ->get()
                           ->map(function($package) {
                               $revenue = $package->transactions->count() * $package->price_cents;
                               return [
                                   'package' => $package,
                                   'sales_count' => $package->sales_count,
                                   'revenue' => $revenue / 100
                               ];
                           });
    }

    private function getRevenueData($startDate)
    {
        return DB::table('credit_transactions')
                ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                ->where('credit_transactions.type', 'purchase')
                ->where('credit_transactions.created_at', '>=', $startDate)
                ->selectRaw('DATE(credit_transactions.created_at) as date,
                           SUM(credit_packages.price_cents) as revenue,
                           COUNT(*) as transactions')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
    }

    private function getUsageData($startDate)
    {
        return DB::table('credit_transactions')
                ->where('type', 'usage')
                ->where('created_at', '>=', $startDate)
                ->selectRaw('DATE(created_at) as date,
                           SUM(ABS(credits_amount)) as credits_used,
                           COUNT(*) as usage_transactions')
                ->groupBy('date')
                ->orderBy('date')
                ->get();
    }

    private function getConversionData($startDate)
    {
        $newUsers = User::where('created_at', '>=', $startDate)->count();
        $buyingUsers = User::whereHas('creditTransactions', function($query) use ($startDate) {
                          $query->where('type', 'purchase')
                                ->where('created_at', '>=', $startDate);
                      })->count();

        return [
            'new_users' => $newUsers,
            'buying_users' => $buyingUsers,
            'conversion_rate' => $newUsers > 0 ? round(($buyingUsers / $newUsers) * 100, 1) : 0
        ];
    }
}
