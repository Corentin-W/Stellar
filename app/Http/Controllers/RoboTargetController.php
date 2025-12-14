<?php

namespace App\Http\Controllers;

use App\Models\RoboTarget;
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

        return view('dashboard.robotarget.index', [
            'targets' => $targets,
            'stats' => $stats,
            'filters' => $filters,
            'subscription' => $user->subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }

    /**
     * Show the form for creating a new target
     */
    public function create(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.robotarget.create', [
            'subscription' => $user->subscription,
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

        return view('dashboard.robotarget.show', [
            'target' => $target,
            'subscription' => $user->subscription,
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
                $query->where('status', 'completed')
                    ->where('images_accepted', '>', 0)
                    ->orderBy('started_at', 'desc');
            }])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.robotarget.gallery', [
            'targets' => $targets,
            'subscription' => $user->subscription,
            'creditsBalance' => $user->credits_balance,
        ]);
    }
}
