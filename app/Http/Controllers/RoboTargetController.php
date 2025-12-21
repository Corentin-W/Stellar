<?php

namespace App\Http\Controllers;

use App\Models\RoboTarget;
use App\Models\RoboTargetSession;
use App\Services\RoboTargetService;
use Illuminate\Http\Request;
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
