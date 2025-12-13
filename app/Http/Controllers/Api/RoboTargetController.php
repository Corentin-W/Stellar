<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\RoboTargetService;
use App\Models\RoboTarget;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class RoboTargetController extends Controller
{
    protected RoboTargetService $roboTargetService;

    public function __construct(RoboTargetService $roboTargetService)
    {
        $this->roboTargetService = $roboTargetService;
    }

    /**
     * Create a new target
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'target_name' => 'required|string|max:255',
            'ra_j2000' => 'required|string', // Format HH:MM:SS
            'dec_j2000' => 'required|string', // Format +DD:MM:SS
            'priority' => 'required|integer|min:0|max:4',
            'c_moon_down' => 'boolean',
            'c_hfd_mean_limit' => 'nullable|numeric|min:1.5|max:4.0',
            'c_alt_min' => 'nullable|integer|min:10|max:80',
            'c_ha_start' => 'nullable|numeric|min:-12|max:0',
            'c_ha_end' => 'nullable|numeric|min:0|max:12',
            'date_start' => 'nullable|date',
            'date_end' => 'nullable|date|after:date_start',
            'is_repeat' => 'boolean',
            'repeat_count' => 'nullable|integer|min:1|max:30',
            'shots' => 'required|array|min:1',
            'shots.*.filter_index' => 'required|integer|min:0',
            'shots.*.filter_name' => 'required|string',
            'shots.*.exposure' => 'required|integer|min:1',
            'shots.*.num' => 'required|integer|min:1',
            'shots.*.gain' => 'nullable|integer|min:0|max:500',
            'shots.*.offset' => 'nullable|integer|min:0|max:100',
            'shots.*.bin' => 'nullable|integer|in:1,2,3,4',
            'shots.*.type' => 'nullable|integer|in:0,1,2,3',
        ]);

        $user = $request->user();

        try {
            $target = $this->roboTargetService->createTarget($user, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Cible créée avec succès',
                'target' => [
                    'id' => $target->id,
                    'guid' => $target->guid,
                    'target_name' => $target->target_name,
                    'status' => $target->status,
                    'estimated_credits' => $target->estimated_credits,
                    'credits_held' => $target->credits_held,
                    'shots_count' => $target->shots->count(),
                    'estimated_duration' => $target->getFormattedDuration(),
                ],
                'credits_remaining' => $user->fresh()->credits_balance,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Submit target to Voyager
     */
    public function submit(Request $request, string $guid): JsonResponse
    {
        $target = RoboTarget::where('guid', $guid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        if (!$target->isPending()) {
            return response()->json([
                'success' => false,
                'message' => 'La cible doit être en statut pending pour être soumise',
            ], 400);
        }

        try {
            $result = $this->roboTargetService->submitToVoyager($target);

            return response()->json([
                'success' => true,
                'message' => 'Cible soumise à Voyager avec succès',
                'result' => $result,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la soumission',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user's targets
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'date_from', 'date_to']);
        $targets = $this->roboTargetService->getUserTargets($request->user(), $filters);

        return response()->json([
            'success' => true,
            'targets' => $targets->map(function ($target) {
                return [
                    'id' => $target->id,
                    'guid' => $target->guid,
                    'target_name' => $target->target_name,
                    'ra_j2000' => $target->ra_j2000,
                    'dec_j2000' => $target->dec_j2000,
                    'priority' => $target->priority,
                    'status' => $target->status,
                    'status_label' => $target->getStatusLabel(),
                    'status_color' => $target->getStatusColor(),
                    'estimated_credits' => $target->estimated_credits,
                    'credits_charged' => $target->credits_charged,
                    'estimated_duration' => $target->getFormattedDuration(),
                    'shots_count' => $target->shots->count(),
                    'sessions_count' => $target->sessions->count(),
                    'has_successful_session' => $target->hasSuccessfulSession(),
                    'created_at' => $target->created_at,
                ];
            }),
            'total' => $targets->count(),
        ]);
    }

    /**
     * Get a single target
     */
    public function show(Request $request, string $guid): JsonResponse
    {
        $target = RoboTarget::with(['shots', 'sessions'])
            ->where('guid', $guid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'target' => [
                'id' => $target->id,
                'guid' => $target->guid,
                'set_guid' => $target->set_guid,
                'target_name' => $target->target_name,
                'ra_j2000' => $target->ra_j2000,
                'dec_j2000' => $target->dec_j2000,
                'priority' => $target->priority,
                'c_moon_down' => $target->c_moon_down,
                'c_hfd_mean_limit' => $target->c_hfd_mean_limit,
                'c_alt_min' => $target->c_alt_min,
                'c_ha_start' => $target->c_ha_start,
                'c_ha_end' => $target->c_ha_end,
                'c_mask' => $target->c_mask,
                'date_start' => $target->date_start,
                'date_end' => $target->date_end,
                'is_repeat' => $target->is_repeat,
                'status' => $target->status,
                'status_label' => $target->getStatusLabel(),
                'estimated_credits' => $target->estimated_credits,
                'credits_held' => $target->credits_held,
                'credits_charged' => $target->credits_charged,
                'estimated_duration' => $target->getFormattedDuration(),
                'shots' => $target->shots->map(function ($shot) {
                    return [
                        'filter_name' => $shot->filter_name,
                        'filter_index' => $shot->filter_index,
                        'exposure' => $shot->exposure,
                        'num' => $shot->num,
                        'gain' => $shot->gain,
                        'offset' => $shot->offset,
                        'bin' => $shot->bin,
                        'display_name' => $shot->getDisplayName(),
                    ];
                }),
                'sessions' => $target->sessions->map(function ($session) {
                    return [
                        'session_guid' => $session->session_guid,
                        'session_start' => $session->session_start,
                        'session_end' => $session->session_end,
                        'result' => $session->result,
                        'result_label' => $session->getResultLabel(),
                        'hfd_mean' => $session->hfd_mean,
                        'images_captured' => $session->images_captured,
                        'images_accepted' => $session->images_accepted,
                        'acceptance_rate' => $session->getFormattedAcceptanceRate(),
                        'duration' => $session->getFormattedDuration(),
                    ];
                }),
                'created_at' => $target->created_at,
                'updated_at' => $target->updated_at,
            ],
        ]);
    }

    /**
     * Get target progress (real-time from Voyager)
     */
    public function progress(Request $request, string $guid): JsonResponse
    {
        $target = RoboTarget::where('guid', $guid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $progress = $this->roboTargetService->getTargetProgress($target);

        return response()->json([
            'success' => true,
            'target_guid' => $target->guid,
            'status' => $target->status,
            'progress' => $progress,
        ]);
    }

    /**
     * Cancel a target
     */
    public function cancel(Request $request, string $guid): JsonResponse
    {
        $target = RoboTarget::where('guid', $guid)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        try {
            $this->roboTargetService->cancelTarget($target);

            return response()->json([
                'success' => true,
                'message' => 'Cible annulée et crédits remboursés',
                'target' => [
                    'guid' => $target->guid,
                    'status' => $target->fresh()->status,
                    'credits_refunded' => $target->credits_held,
                ],
                'credits_balance' => $request->user()->fresh()->credits_balance,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Get user statistics
     */
    public function stats(Request $request): JsonResponse
    {
        $stats = $this->roboTargetService->getUserStats($request->user());

        return response()->json([
            'success' => true,
            'stats' => $stats,
        ]);
    }

    /**
     * Webhook to handle session complete events from Voyager Proxy
     */
    public function webhookSessionComplete(Request $request): JsonResponse
    {
        // TODO: Ajouter validation webhook signature

        $eventData = $request->all();

        try {
            $this->roboTargetService->handleSessionComplete($eventData);

            return response()->json([
                'success' => true,
                'message' => 'Session complete event processed',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
