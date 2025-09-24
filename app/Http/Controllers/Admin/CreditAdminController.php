<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CreditPackage;
use App\Models\CreditTransaction;
use App\Models\Promotion;
use App\Models\User;
use App\Services\StripeService;
use App\Services\CreditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class CreditAdminController extends Controller
{
    public function __construct(
        private StripeService $stripeService,
        private CreditService $creditService
    ) {
        // $this->middleware(['auth', 'admin']);
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

            return redirect()->route('admin.credits.packages.index')
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

            return redirect()->route('admin.credits.packages.index')
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

            return redirect()->route('admin.credits.packages.index')
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
        $promotions = Promotion::withCount('users')
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
            'value' => 'required|numeric|min:0.01',
            'usage_limit' => 'nullable|integer|min:1|max:100000',
            'user_limit' => 'nullable|integer|min:1|max:100',
            'minimum_purchase' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date|after_or_equal:today',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:credit_packages,id'
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        // Convertir la valeur selon le type
        if ($validated['type'] === 'fixed_amount') {
            $validated['value'] = $validated['value'] * 100; // Convertir en centimes
        }

        try {
            Promotion::create($validated);

            return redirect()->route('admin.credits.promotions.index')
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
            'code' => [
                'required',
                'string',
                'max:50',
                'alpha_num',
                Rule::unique('promotions', 'code')->ignore($promotion->id)
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|in:percentage,fixed_amount,bonus_credits',
            'value' => 'required|numeric|min:0.01',
            'usage_limit' => 'nullable|integer|min:1|max:100000',
            'user_limit' => 'nullable|integer|min:1|max:100',
            'minimum_purchase' => 'nullable|integer|min:0',
            'is_active' => 'boolean',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'applicable_packages' => 'nullable|array',
            'applicable_packages.*' => 'exists:credit_packages,id'
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');

        // Convertir la valeur selon le type
        if ($validated['type'] === 'fixed_amount') {
            $validated['value'] = $validated['value'] * 100; // Convertir en centimes
        }

        try {
            $promotion->update($validated);

            return redirect()->route('admin.credits.promotions.index')
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
        try {
            $promotion->delete();

            return redirect()->route('admin.credits.promotions.index')
                           ->with('success', 'Promotion supprimée avec succès');

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de la suppression: ' . $e->getMessage()]);
        }
    }

    /**
     * Gestion des utilisateurs
     */
    public function users()
    {
        $users = User::withCount('creditTransactions')
                    ->withSum('creditTransactions as total_purchased', DB::raw('CASE WHEN type = "purchase" THEN credits_amount ELSE 0 END'))
                    ->orderBy('credits_balance', 'desc')
                    ->paginate(20);

        return view('admin.credits.users.index', compact('users'));
    }

    /**
     * Détails d'un utilisateur
     */
    public function userDetails(User $user)
    {
        $transactions = $user->creditTransactions()
                            ->with('creditPackage')
                            ->orderBy('created_at', 'desc')
                            ->paginate(20);

        $stats = [
            'current_balance' => $user->credits_balance,
            'total_purchased' => $user->creditTransactions()->where('type', 'purchase')->sum('credits_amount'),
            'total_used' => abs($user->creditTransactions()->where('type', 'usage')->sum('credits_amount')),
            'total_spent' => $user->creditTransactions()
                                 ->where('type', 'purchase')
                                 ->with('creditPackage')
                                 ->get()
                                 ->sum(function ($transaction) {
                                     return $transaction->creditPackage ? $transaction->creditPackage->price_euros : 0;
                                 }),
            'first_purchase' => $user->creditTransactions()->where('type', 'purchase')->orderBy('created_at')->first(),
            'last_activity' => $user->creditTransactions()->orderBy('created_at', 'desc')->first()
        ];

        return view('admin.credits.users.show', compact('user', 'transactions', 'stats'));
    }

    /**
     * Ajuster les crédits d'un utilisateur
     */
    public function adjustUserCredits(Request $request, User $user)
    {
        $request->validate([
            'amount' => 'required|integer|min:-50000|max:50000',
            'reason' => 'required|string|max:500'
        ]);

        try {
            $user->adminAdjustCredits(
                $request->amount,
                $request->reason,
                auth()->id()
            );

            return redirect()->back()
                           ->with('success', "Ajustement de {$request->amount} crédits effectué avec succès");

        } catch (\Exception $e) {
            return redirect()->back()
                           ->withErrors(['error' => 'Erreur lors de l\'ajustement: ' . $e->getMessage()]);
        }
    }

    /**
     * Rapports et statistiques
     */
    public function reports()
    {
        $dateStart = request('date_start', now()->subDays(30)->format('Y-m-d'));
        $dateEnd = request('date_end', now()->format('Y-m-d'));

        $stats = CreditTransaction::getStatsForPeriod(
            Carbon::parse($dateStart),
            Carbon::parse($dateEnd)
        );

        $dailyStats = CreditTransaction::getDailyStats(30);
        $topUsers = CreditTransaction::getTopUsers(10, 30);

        return view('admin.credits.reports', compact('stats', 'dailyStats', 'topUsers', 'dateStart', 'dateEnd'));
    }

    /**
     * Export des transactions
     */
    public function exportTransactions(Request $request)
    {
        $dateStart = $request->get('date_start', now()->subDays(30)->format('Y-m-d'));
        $dateEnd = $request->get('date_end', now()->format('Y-m-d'));

        $transactions = CreditTransaction::with(['user', 'creditPackage'])
                                       ->whereBetween('created_at', [$dateStart, $dateEnd])
                                       ->orderBy('created_at', 'desc')
                                       ->get();

        $filename = "transactions_" . $dateStart . "_to_" . $dateEnd . ".csv";

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function() use ($transactions) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'Utilisateur', 'Type', 'Montant', 'Solde Avant', 'Solde Après', 'Description', 'Package']);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->created_at->format('Y-m-d H:i:s'),
                    $transaction->user->name ?? 'N/A',
                    $transaction->formatted_type,
                    $transaction->credits_amount,
                    $transaction->balance_before,
                    $transaction->balance_after,
                    $transaction->description,
                    $transaction->creditPackage->name ?? 'N/A'
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Liste des transactions
     */
    public function transactions()
    {
        $transactions = CreditTransaction::with(['user', 'creditPackage'])
                                       ->orderBy('created_at', 'desc')
                                       ->paginate(50);

        return view('admin.credits.transactions', compact('transactions'));
    }

/**
 * Statistiques générales - VERSION CORRIGÉE
 */
private function getCreditStats(): array
{
    $totalUsers = User::where('credits_balance', '>', 0)->count();
    $totalCreditsInCirculation = User::sum('credits_balance');

    // Calcul du revenu total avec une requête JOIN correcte
    $totalRevenue = DB::table('credit_transactions as ct')
        ->join('credit_packages as cp', 'ct.credit_package_id', '=', 'cp.id')
        ->where('ct.type', 'purchase')
        ->sum('cp.price_cents');
    $totalRevenue = $totalRevenue / 100; // Convertir en euros

    $monthlyStats = DB::table('credit_transactions as ct')
        ->leftJoin('credit_packages as cp', 'ct.credit_package_id', '=', 'cp.id')
        ->where('ct.created_at', '>=', now()->subMonth())
        ->selectRaw('
            COUNT(*) as transactions_count,
            SUM(CASE WHEN ct.type = "purchase" THEN ct.credits_amount ELSE 0 END) as credits_sold,
            COUNT(CASE WHEN ct.type = "purchase" THEN 1 END) as purchases_count
        ')
        ->first();

    return [
        'total_users' => $totalUsers,
        'total_credits_in_circulation' => $totalCreditsInCirculation,
        'total_revenue' => $totalRevenue,
        'monthly_transactions' => $monthlyStats->transactions_count ?? 0,
        'monthly_credits_sold' => $monthlyStats->credits_sold ?? 0,
        'monthly_purchases' => $monthlyStats->purchases_count ?? 0,
        'active_packages' => CreditPackage::where('is_active', true)->count(),
        'active_promotions' => Promotion::where('is_active', true)
                                     ->where(function($query) {
                                         $query->whereNull('expires_at')
                                               ->orWhere('expires_at', '>', now());
                                     })
                                     ->count()
    ];
}


private function getTopBuyers(int $limit = 5): array
{
    $topBuyers = DB::table('users as u')
        ->leftJoin('credit_transactions as ct', function($join) {
            $join->on('u.id', '=', 'ct.user_id')
                 ->where('ct.type', '=', 'purchase');
        })
        ->leftJoin('credit_packages as cp', 'ct.credit_package_id', '=', 'cp.id')
        ->select([
            'u.id',
            'u.name',
            'u.email',
            'u.credits_balance',
            DB::raw('COUNT(ct.id) as purchases_count'),
            DB::raw('SUM(ct.credits_amount) as total_credits_purchased'),
            DB::raw('COALESCE(SUM(cp.price_cents), 0) as total_spent_cents')
        ])
        ->groupBy('u.id', 'u.name', 'u.email', 'u.credits_balance')
        ->having('purchases_count', '>', 0)
        ->orderByDesc('purchases_count')
        ->limit($limit)
        ->get();

    return $topBuyers->map(function($buyer) {
        return [
            'id' => $buyer->id,
            'name' => $buyer->name,
            'email' => $buyer->email,
            'credits_balance' => $buyer->credits_balance,
            'purchases_count' => $buyer->purchases_count,
            'total_credits_purchased' => $buyer->total_credits_purchased,
            'total_spent' => $buyer->total_spent_cents / 100
        ];
    })->toArray();
}

/**
 * Statistiques des packages - VERSION CORRIGÉE
 */
private function getPackageStats(): array
{
    $packageStats = DB::table('credit_packages as cp')
        ->leftJoin('credit_transactions as ct', function($join) {
            $join->on('cp.id', '=', 'ct.credit_package_id')
                 ->where('ct.type', '=', 'purchase');
        })
        ->select([
            'cp.id',
            'cp.name',
            'cp.price_cents',
            'cp.credits_amount',
            'cp.bonus_credits',
            'cp.is_active',
            'cp.is_featured',
            DB::raw('COUNT(ct.id) as sales_count'),
            DB::raw('SUM(ct.credits_amount) as total_credits_sold'),
            DB::raw('COALESCE(SUM(cp.price_cents), 0) as total_revenue_cents')
        ])
        ->groupBy(
            'cp.id', 'cp.name', 'cp.price_cents', 'cp.credits_amount',
            'cp.bonus_credits', 'cp.is_active', 'cp.is_featured'
        )
        ->orderByDesc('sales_count')
        ->limit(10)
        ->get();

    return $packageStats->map(function($package) {
        $totalCredits = $package->credits_amount + ($package->bonus_credits ?? 0);

        return [
            'id' => $package->id,
            'name' => $package->name,
            'price_euros' => $package->price_cents / 100,
            'credits_amount' => $package->credits_amount,
            'bonus_credits' => $package->bonus_credits ?? 0,
            'total_credits' => $totalCredits,
            'is_active' => $package->is_active,
            'is_featured' => $package->is_featured,
            'sales_count' => $package->sales_count,
            'total_credits_sold' => $package->total_credits_sold ?? 0,
            'total_revenue' => $package->total_revenue_cents / 100,
            'avg_sale_value' => $package->sales_count > 0 ?
                round(($package->total_revenue_cents / 100) / $package->sales_count, 2) : 0,
            'credit_value' => $package->price_cents > 0 && $totalCredits > 0 ?
                round(($package->price_cents / 100) / $totalCredits, 4) : 0
        ];
    })->toArray();
}
}
