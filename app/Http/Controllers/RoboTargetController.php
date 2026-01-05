<?php

namespace App\Http\Controllers;

use App\Models\RoboTarget;
use App\Models\RoboTargetSession;
use App\Services\RoboTargetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RoboTargetController extends Controller
{
    protected RoboTargetService $roboTargetService;

    public function __construct(RoboTargetService $roboTargetService)
    {
        $this->roboTargetService = $roboTargetService;
    }

    /**
     * Display a listing of the user's targets
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        // Filtres depuis query string
        $filters = [
            'status' => $request->query('status'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
        ];

        $targets = $this->roboTargetService->getUserTargets($user, $filters);
        $stats = $this->roboTargetService->getUserStats($user);

        // Pour les admins sans abonnement, créer une subscription fictive avec tous les accès
        $subscription = $user->subscription ?? ($user->is_admin ? $this->getAdminFakeSubscription($user) : null);

        return view('dashboard.robotarget.index', [
            'targets' => $targets,
            'stats' => $stats,
            'filters' => $filters,
            'subscription' => $subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Show the form for creating a new target
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        // Pour les admins sans abonnement, créer une subscription fictive avec tous les accès
        $subscription = $user->subscription ?? ($user->is_admin ? $this->getAdminFakeSubscription($user) : null);

        return view('dashboard.robotarget.create', [
            'subscription' => $subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Show the form for creating a new target (V2 - Compact & Modern)
     */
    public function createV2(Request $request): View
    {
        $user = $request->user();

        // Pour les admins sans abonnement, créer une subscription fictive avec tous les accès
        $subscription = $user->subscription ?? ($user->is_admin ? $this->getAdminFakeSubscription($user) : null);

        return view('dashboard.robotarget.create-v2', [
            'subscription' => $subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Store a new target (API endpoint)
     */
    public function store(Request $request)
    {
        \Log::info('📤 [RoboTarget Store] Method called', [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
        ]);

        try {
            // Manual auth check (since middleware redirect issues with API)
            if (!Auth::check()) {
                \Log::warning('❌ [RoboTarget Store] User not authenticated');
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated',
                ], 401);
            }

            $user = Auth::user();

            \Log::info('📤 [RoboTarget Store] Received request', [
                'user_id' => $user->id,
                'payload' => $request->all(),
            ]);

            // Validate request
            $validated = $request->validate([
                'guid_set' => 'required|string',
                'guid_target' => 'required|string',
                'target_name' => 'required|string|max:255',
                'ra_j2000' => 'required|string',
                'dec_j2000' => 'required|string',
                'priority' => 'integer|min:0|max:4',
                'c_moon_down' => 'boolean',
                'c_hfd_mean_limit' => 'nullable|numeric',
                'c_alt_min' => 'integer|min:0|max:90',
                'c_sqm_min' => 'nullable|numeric',
                'shots' => 'required|array|min:1',
                'shots.*.filter_index' => 'required|integer',
                'shots.*.filter_name' => 'required|string',
                'shots.*.exposure' => 'required|numeric|min:0.1',
                'shots.*.num' => 'required|integer|min:1',
                'shots.*.gain' => 'required|integer|min:0',
                'shots.*.offset' => 'required|integer|min:0',
                'shots.*.bin' => 'required|integer|in:1,2',
                'is_assisted' => 'boolean',
            ]);

            \Log::info('✅ [RoboTarget Store] Validation passed');

            // Create target using service
            $target = $this->roboTargetService->createTarget($user, $validated);

            \Log::info('✅ [RoboTarget Store] Target created successfully', [
                'target_id' => $target->id,
                'guid' => $target->guid,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Target créée avec succès',
                'data' => [
                    'target' => [
                        'id' => $target->id,
                        'guid' => $target->guid,
                        'target_name' => $target->target_name,
                        'status' => $target->status,
                        'credits_held' => $target->credits_held,
                    ],
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error('❌ [RoboTarget Store] Validation failed', [
                'errors' => $e->errors(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Données invalides',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            \Log::error('❌ [RoboTarget Store] Error creating target', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified target with real-time monitoring
     */
    public function show(Request $request, string $guid): View
    {
        $user = $request->user();

        $target = RoboTarget::with(['shots', 'sessions'])
            ->where('guid', $guid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Pour les admins sans abonnement, créer une subscription fictive avec tous les accès
        $subscription = $user->subscription ?? ($user->is_admin ? $this->getAdminFakeSubscription($user) : null);

        return view('dashboard.robotarget.show', [
            'target' => $target,
            'subscription' => $subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Display the user's image gallery
     */
    public function gallery(Request $request): View
    {
        $user = $request->user();

        // Get completed targets with sessions
        $targets = RoboTarget::where('user_id', $user->id)
            ->with(['sessions' => function ($query) {
                $query->where('result', RoboTargetSession::RESULT_OK)
                    ->where('images_accepted', '>', 0)
                    ->orderBy('session_start', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        // Pour les admins sans abonnement, créer une subscription fictive avec tous les accès
        $subscription = $user->subscription ?? ($user->is_admin ? $this->getAdminFakeSubscription($user) : null);

        return view('dashboard.robotarget.gallery', [
            'targets' => $targets,
            'subscription' => $subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Display live monitoring page for a target session
     */
    public function monitor(Request $request, string $guid): View
    {
        $user = $request->user();

        $target = RoboTarget::with(['sessions'])->where('guid', $guid)
            ->where('user_id', $user->id)
            ->firstOrFail();

        // Get the active session or the most recent one
        $session = $target->sessions()
            ->whereNull('result') // Session en cours
            ->latest('session_start')
            ->first();

        if (!$session) {
            // Si pas de session en cours, prendre la dernière
            $session = $target->sessions()
                ->latest('session_start')
                ->firstOrFail();
        }

        // Pour les admins sans abonnement, créer une subscription fictive avec tous les accès
        $subscription = $user->subscription ?? ($user->is_admin ? $this->getAdminFakeSubscription($user) : null);

        return view('dashboard.robotarget.monitor', [
            'target' => $target,
            'session' => $session,
            'subscription' => $subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Crée une subscription fictive pour les administrateurs
     * afin qu'ils puissent accéder à toutes les fonctionnalités
     */
    private function getAdminFakeSubscription($user)
    {
        $subscription = new \App\Models\Subscription();
        $subscription->user_id = $user->id;
        $subscription->plan = 'quasar'; // Plan le plus élevé
        $subscription->status = 'active';
        $subscription->starts_at = now();
        $subscription->ends_at = now()->addYear();

        return $subscription;
    }
}
