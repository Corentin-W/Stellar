<?php

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
use Illuminate\Validation\Rule;
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
        $revenueChart = $this->getRevenueChartData();

        return view('admin.credits.dashboard', compact(
            'stats',
            'recentTransactions',
            'topBuyers',
            'packageStats',
            'revenueChart'
        ));
    }

    /**
     * Gestion des packages de crédits
     */
    public function packages()
    {
        $packages = CreditPackage::withCount(['transactions as sales_count' => function($query) {
                                   $query->where('type', 'purchase');
                               }])
                               ->orderBy('sort_order')
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
            'description' => 'nullable|string|max:1000',
            'credits_amount' => 'required|integer|min:1|max:50000',
            'price_euros' => 'required|numeric|min:0.01|max:5000',
            'bonus_credits' => 'nullable|integer|min:0|max:10000',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000'
        ], [
            'name.required' => 'Le nom du package est obligatoire',
            'credits_amount.required' => 'Le nombre de crédits est obligatoire',
            'credits_amount.min' => 'Le nombre de crédits doit être au minimum de 1',
            'price_euros.required' => 'Le prix est obligatoire',
            'price_euros.min' => 'Le prix doit être supérieur à 0.01€'
        ]);

        // Convertir le prix en centimes
        $validated['price_cents'] = round($validated['price_euros'] * 100);
        unset($validated['price_euros']);

        // Valeurs par défaut
        $validated['currency'] = 'EUR';
        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');
        $validated['sort_order'] = $validated['sort_order'] ?? 0;

        try {
            $package = CreditPackage::create($validated);

            // Synchroniser avec Stripe
            $this->stripeService->syncPackagePrices();

            return redirect()->route('admin.credits.packages')
                           ->with('success', 'Package créé avec succès et synchronisé avec Stripe');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])
                           ->withInput();
        }
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
            'description' => 'nullable|string|max:1000',
            'credits_amount' => 'required|integer|min:1|max:50000',
            'price_euros' => 'required|numeric|min:0.01|max:5000',
            'bonus_credits' => 'nullable|integer|min:0|max:10000',
            'discount_percentage' => 'nullable|integer|min:0|max:100',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
            'sort_order' => 'nullable|integer|min:0|max:1000'
        ]);

        // Convertir le prix en centimes
        $validated['price_cents'] = round($validated['price_euros'] * 100);
        unset($validated['price_euros']);

        $validated['is_active'] = $request->has('is_active');
        $validated['is_featured'] = $request->has('is_featured');

        try {
            $package->update($validated);

            return redirect()->route('admin.credits.packages')
                           ->with('success', 'Package mis à jour avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Activer/désactiver un package (AJAX)
     */
    public function togglePackageStatus(Request $request, CreditPackage $package)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $package->update(['is_active' => $request->is_active]);

            return response()->json([
                'success' => true,
                'message' => $request->is_active ? 'Package activé' : 'Package désactivé',
                'status' => $request->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer un package
     */
    public function deletePackage(CreditPackage $package)
    {
        // Vérifier qu'il n'y a pas de transactions associées
        if ($package->transactions()->exists()) {
            return redirect()->back()
                           ->withErrors(['error' => 'Impossible de supprimer un package ayant des transactions associées']);
        }

        try {
            $package->delete();

            return redirect()->route('admin.credits.packages')
                           ->with('success', 'Package supprimé avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion des promotions
     */
    public function promotions()
    {
        $promotions = Promotion::withCount('usages')
                             ->orderBy('created_at', 'desc')
                             ->paginate(20);

        return view('admin.credits.promotions.index', compact('promotions'));
    }

    /**
     * Créer une nouvelle promotion
     */
    public function createPromotion()
    {
        $packages = CreditPackage::active()->orderBy('sort_order')->get();
        return view('admin.credits.promotions.create', compact('packages'));
    }

    /**
     * Sauvegarder une nouvelle promotion
     */
    public function storePromotion(Request $request)
    {
        $validated = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_num',
                Rule::unique('promotions', 'code')
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount,bonus_credits',
            'value' => 'required|integer|min:1',
            'min_purchase_amount_euros' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1|max:100000',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:credit_packages,id'
        ], [
            'code.required' => 'Le code promo est obligatoire',
            'code.unique' => 'Ce code promo existe déjà',
            'code.alpha_num' => 'Le code ne peut contenir que des lettres et chiffres',
            'name.required' => 'Le nom de la promotion est obligatoire',
            'type.required' => 'Le type de promotion est obligatoire',
            'value.required' => 'La valeur de la promotion est obligatoire',
            'expires_at.after' => 'La date d\'expiration doit être après la date de début'
        ]);

        // Traitement des données
        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        // Convertir le montant minimum en centimes si fourni
        if (isset($validated['min_purchase_amount_euros'])) {
            $validated['min_purchase_amount'] = round($validated['min_purchase_amount_euros'] * 100);
            unset($validated['min_purchase_amount_euros']);
        }

        // Pour les remises fixes, convertir en centimes
        if ($validated['type'] === 'fixed_amount') {
            $validated['value'] = $validated['value'] * 100;
        }

        try {
            Promotion::create($validated);

            return redirect()->route('admin.credits.promotions')
                           ->with('success', 'Promotion créée avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la création: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Éditer une promotion
     */
    public function editPromotion(Promotion $promotion)
    {
        $packages = CreditPackage::active()->orderBy('sort_order')->get();
        return view('admin.credits.promotions.edit', compact('promotion', 'packages'));
    }

    /**
     * Mettre à jour une promotion
     */
    public function updatePromotion(Request $request, Promotion $promotion)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount,bonus_credits',
            'value' => 'required|integer|min:1',
            'min_purchase_amount_euros' => 'nullable|numeric|min:0',
            'max_uses' => 'nullable|integer|min:1|max:100000',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:credit_packages,id'
        ]);

        $validated['is_active'] = $request->has('is_active');

        // Convertir le montant minimum en centimes si fourni
        if (isset($validated['min_purchase_amount_euros'])) {
            $validated['min_purchase_amount'] = round($validated['min_purchase_amount_euros'] * 100);
            unset($validated['min_purchase_amount_euros']);
        }

        // Pour les remises fixes, convertir en centimes
        if ($validated['type'] === 'fixed_amount') {
            $validated['value'] = $validated['value'] * 100;
        }

        try {
            $promotion->update($validated);

            return redirect()->route('admin.credits.promotions')
                           ->with('success', 'Promotion mise à jour avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la mise à jour: ' . $e->getMessage()])
                           ->withInput();
        }
    }

    /**
     * Activer/désactiver une promotion (AJAX)
     */
    public function togglePromotionStatus(Request $request, Promotion $promotion)
    {
        $request->validate([
            'is_active' => 'required|boolean'
        ]);

        try {
            $promotion->update(['is_active' => $request->is_active]);

            return response()->json([
                'success' => true,
                'message' => $request->is_active ? 'Promotion activée' : 'Promotion désactivée',
                'status' => $request->is_active
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la modification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Supprimer une promotion
     */
    public function deletePromotion(Promotion $promotion)
    {
        // Vérifier qu'il n'y a pas d'utilisations
        if ($promotion->usages()->exists()) {
            return redirect()->back()
                           ->withErrors(['error' => 'Impossible de supprimer une promotion ayant été utilisée']);
        }

        try {
            $promotion->delete();

            return redirect()->route('admin.credits.promotions')
                           ->with('success', 'Promotion supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion des utilisateurs et crédits
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Filtres de recherche
        if ($request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filtres par type
        switch ($request->filter) {
            case 'with_credits':
                $query->where('credits_balance', '>', 0);
                break;
            case 'big_buyers':
                $query->where('total_credits_purchased', '>', 500);
                break;
            case 'recent_buyers':
                $query->whereHas('creditTransactions', function($q) {
                    $q->where('type', 'purchase')
                      ->where('created_at', '>=', now()->subDays(30));
                });
                break;
            case 'inactive':
                $query->where('credits_balance', 0)
                      ->where('total_credits_purchased', 0);
                break;
        }

        // Tri
        $sortBy = $request->sort_by ?? 'total_credits_purchased';
        $sortDirection = $request->sort_direction ?? 'desc';

        $allowedSorts = ['name', 'email', 'credits_balance', 'total_credits_purchased', 'total_credits_used', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortDirection);
        }

        $users = $query->paginate(20)->withQueryString();

        // Statistiques pour les filtres
        $filterStats = [
            'total' => User::count(),
            'with_credits' => User::where('credits_balance', '>', 0)->count(),
            'big_buyers' => User::where('total_credits_purchased', '>', 500)->count(),
            'recent_buyers' => User::whereHas('creditTransactions', function($q) {
                $q->where('type', 'purchase')->where('created_at', '>=', now()->subDays(30));
            })->count()
        ];

        return view('admin.credits.users.index', compact('users', 'filterStats'));
    }

    /**
     * Détails d'un utilisateur
     */
    public function userDetails(User $user)
    {
        $transactions = $user->creditTransactions()
                            ->with(['creditPackage', 'createdBy'])
                            ->latest()
                            ->paginate(30);

        $analytics = $this->creditService->getUserCreditAnalytics($user, 90);
        $stats = $user->getCreditStats();

        // Sessions d'observation récentes
        $recentSessions = $user->observationSessions()
                              ->latest()
                              ->limit(10)
                              ->get();

        return view('admin.credits.users.details', compact('user', 'transactions', 'analytics', 'stats', 'recentSessions'));
    }

    /**
     * Ajuster les crédits d'un utilisateur (AJAX)
     */
    public function adjustUserCredits(Request $request, User $user)
    {
        $validated = $request->validate([
            'amount' => 'required|integer|min:-50000|max:50000',
            'reason' => 'required|string|max:255'
        ], [
            'amount.required' => 'Le montant est obligatoire',
            'amount.integer' => 'Le montant doit être un nombre entier',
            'amount.min' => 'Le montant ne peut pas être inférieur à -50000',
            'amount.max' => 'Le montant ne peut pas être supérieur à 50000',
            'reason.required' => 'La raison est obligatoire'
        ]);

        try {
            if ($validated['amount'] > 0) {
                // Ajout de crédits
                $transaction = $user->addCredits(
                    $validated['amount'],
                    'admin_adjustment',
                    "Ajustement admin: " . $validated['reason'],
                    null,
                    null,
                    auth()->user()
                );
                $message = "Ajout de {$validated['amount']} crédits effectué avec succès";
            } else {
                // Déduction de crédits
                $amount = abs($validated['amount']);

                if ($user->credits_balance < $amount) {
                    return response()->json([
                        'success' => false,
                        'message' => "Solde insuffisant. Solde actuel: {$user->credits_balance} crédits"
                    ], 400);
                }

                $transaction = $user->deductCredits(
                    $amount,
                    "Ajustement admin: " . $validated['reason']
                );
                $message = "Déduction de {$amount} crédits effectuée avec succès";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'new_balance' => $user->fresh()->credits_balance,
                'transaction_id' => $transaction->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'ajustement: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Liste des transactions
     */
    public function transactions(Request $request)
    {
        $query = CreditTransaction::with(['user', 'creditPackage', 'createdBy']);

        // Filtres
        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->user_search) {
            $query->whereHas('user', function($q) use ($request) {
                $q->where('name', 'like', "%{$request->user_search}%")
                  ->orWhere('email', 'like', "%{$request->user_search}%");
            });
        }

        if ($request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $transactions = $query->latest()->paginate(50)->withQueryString();

        // Statistiques pour les filtres
        $typeStats = CreditTransaction::selectRaw('type, COUNT(*) as count')
                                    ->groupBy('type')
                                    ->pluck('count', 'type')
                                    ->toArray();

        return view('admin.credits.transactions', compact('transactions', 'typeStats'));
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
        $packagePerformance = $this->getPackagePerformanceData($startDate);

        return view('admin.credits.reports', compact(
            'period',
            'revenueData',
            'usageData',
            'conversionData',
            'packagePerformance'
        ));
    }

    /**
     * Export des transactions en CSV
     */
    public function exportTransactions(Request $request)
    {
        $startDate = $request->input('start_date', now()->subMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', now()->format('Y-m-d'));
        $type = $request->input('type');

        $query = CreditTransaction::with(['user', 'creditPackage'])
                                 ->whereBetween('created_at', [$startDate, $endDate]);

        if ($type) {
            $query->where('type', $type);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $filename = 'transactions_credits_' . $startDate . '_' . $endDate . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');

            // BOM pour Excel
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // Headers CSV
            fputcsv($file, [
                'ID Transaction',
                'Date',
                'Heure',
                'Utilisateur',
                'Email',
                'Type',
                'Montant Crédits',
                'Solde Avant',
                'Solde Après',
                'Package',
                'Prix Package (€)',
                'Description',
                'Stripe Payment ID',
                'Créé par'
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->id,
                    $transaction->created_at->format('Y-m-d'),
                    $transaction->created_at->format('H:i:s'),
                    $transaction->user->name,
                    $transaction->user->email,
                    $transaction->formatted_type,
                    $transaction->credits_amount,
                    $transaction->balance_before,
                    $transaction->balance_after,
                    $transaction->creditPackage?->name ?? 'N/A',
                    $transaction->creditPackage ? number_format($transaction->creditPackage->price_euros, 2) : 'N/A',
                    $transaction->description,
                    $transaction->stripe_payment_intent_id ?? 'N/A',
                    $transaction->createdBy?->name ?? 'System'
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
        // Utilisateurs avec crédits
        $totalUsersWithCredits = User::whereHas('creditTransactions')->count();

        // Revenus total et mensuel
        $totalRevenue = DB::table('credit_transactions')
                         ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                         ->where('credit_transactions.type', 'purchase')
                         ->sum('credit_packages.price_cents') / 100;

        $monthlyRevenue = DB::table('credit_transactions')
                           ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                           ->where('credit_transactions.type', 'purchase')
                           ->where('credit_transactions.created_at', '>=', now()->subMonth())
                           ->sum('credit_packages.price_cents') / 100;

        // Crédits en circulation et consommés
        $creditsInCirculation = User::sum('credits_balance');
        $creditsConsumed = CreditTransaction::where('type', 'usage')->sum('credits_amount') * -1;
        $totalCreditsPurchased = CreditTransaction::where('type', 'purchase')->sum('credits_amount');

        // Moyennes
        $avgCreditsPerUser = $totalUsersWithCredits > 0 ? round($creditsInCirculation / $totalUsersWithCredits) : 0;
        $avgRevenuePerUser = $totalUsersWithCredits > 0 ? round($totalRevenue / $totalUsersWithCredits, 2) : 0;

        return [
            'total_users' => $totalUsersWithCredits,
            'total_revenue' => $totalRevenue,
            'monthly_revenue' => $monthlyRevenue,
            'weekly_revenue' => $this->getWeeklyRevenue(),
            'credits_in_circulation' => $creditsInCirculation,
            'credits_consumed' => $creditsConsumed,
            'total_credits_purchased' => $totalCreditsPurchased,
            'avg_credits_per_user' => $avgCreditsPerUser,
            'avg_revenue_per_user' => $avgRevenuePerUser,
            'conversion_rate' => $this->getConversionRate(),
            'growth_rate' => $this->getGrowthRate()
        ];
    }

    private function getWeeklyRevenue()
    {
        return DB::table('credit_transactions')
                ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                ->where('credit_transactions.type', 'purchase')
                ->where('credit_transactions.created_at', '>=', now()->subWeek())
                ->sum('credit_packages.price_cents') / 100;
    }

    private function getConversionRate()
    {
        $totalUsers = User::count();
        $buyingUsers = User::whereHas('creditTransactions', function($query) {
            $query->where('type', 'purchase');
        })->count();

        return $totalUsers > 0 ? round(($buyingUsers / $totalUsers) * 100, 1) : 0;
    }

    private function getGrowthRate()
    {
        $thisMonth = DB::table('credit_transactions')
                      ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                      ->where('credit_transactions.type', 'purchase')
                      ->where('credit_transactions.created_at', '>=', now()->startOfMonth())
                      ->sum('credit_packages.price_cents');

        $lastMonth = DB::table('credit_transactions')
                      ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                      ->where('credit_transactions.type', 'purchase')
                      ->whereBetween('credit_transactions.created_at', [
                          now()->subMonth()->startOfMonth(),
                          now()->subMonth()->endOfMonth()
                      ])
                      ->sum('credit_packages.price_cents');

        if ($lastMonth == 0) return 0;

        return round((($thisMonth - $lastMonth) / $lastMonth) * 100, 1);
    }

    private function getTopBuyers($limit = 10)
    {
        return User::orderBy('total_credits_purchased', 'desc')
                  ->where('total_credits_purchased', '>', 0)
                  ->limit($limit)
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
                               $salesCount = $package->sales_count;
                               $revenue = $salesCount * $package->price_cents / 100;

                               return [
                                   'package' => $package,
                                   'sales_count' => $salesCount,
                                   'revenue' => $revenue,
                                   'avg_credits_per_sale' => $salesCount > 0 ? $package->total_credits : 0
                               ];
                           })
                           ->sortByDesc('revenue');
    }

    private function getRevenueChartData()
    {
        return DB::table('credit_transactions')
                ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                ->where('credit_transactions.type', 'purchase')
                ->where('credit_transactions.created_at', '>=', now()->subDays(30))
                ->selectRaw('DATE(credit_transactions.created_at) as date,
                           SUM(credit_packages.price_cents) as revenue,
                           COUNT(*) as transactions')
                ->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function($item) {
                    return [
                        'date' => $item->date,
                        'revenue' => $item->revenue / 100,
                        'transactions' => $item->transactions
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
            'conversion_rate' => $newUsers > 0 ? round(($buyingUsers / $newUsers) * 100, 1) : 0,
            'total_purchases' => CreditTransaction::where('type', 'purchase')
                                                ->where('created_at', '>=', $startDate)
                                                ->count()
        ];
    }

    private function getPackagePerformanceData($startDate)
    {
        return DB::table('credit_transactions')
                ->join('credit_packages', 'credit_transactions.credit_package_id', '=', 'credit_packages.id')
                ->where('credit_transactions.type', 'purchase')
                ->where('credit_transactions.created_at', '>=', $startDate)
                ->selectRaw('credit_packages.name,
                           credit_packages.id,
                           COUNT(*) as sales,
                           SUM(credit_packages.price_cents) as revenue,
                           SUM(credit_transactions.credits_amount) as credits_sold')
                ->groupBy('credit_packages.id', 'credit_packages.name')
                ->orderByDesc('revenue')
                ->get()
                ->map(function($item) {
                    return [
                        'name' => $item->name,
                        'sales' => $item->sales,
                        'revenue' => $item->revenue / 100,
                        'credits_sold' => $item->credits_sold,
                        'avg_sale_value' => $item->sales > 0 ? round(($item->revenue / 100) / $item->sales, 2) : 0
                    ];
                });
    }
}
