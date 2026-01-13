<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoboTargetSession;
use App\Events\RoboTargetSessionStarted;
use App\Events\RoboTargetProgress;
use App\Events\RoboTargetShotRunning;
use App\Events\RoboTargetImageReady;
use App\Events\RoboTargetSessionCompleted;
use App\Mail\RoboTargetSessionStartedMail;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class VoyagerEventController extends Controller
{
    /**
     * Handle session started event from Voyager Proxy
     */
    public function sessionStarted(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_guid' => 'nullable|string',
            'target_guid' => 'nullable|string',
            'TargetUID' => 'nullable|string', // Support for Voyager event format
            'TargetName' => 'nullable|string',
            'voyager_data' => 'nullable|array',
        ]);

        try {
            // Try to find target by guid (prefer TargetUID from Voyager event)
            $targetGuid = $validated['TargetUID'] ?? $validated['target_guid'];

            if (!$targetGuid) {
                return response()->json([
                    'success' => false,
                    'error' => 'Missing target_guid or TargetUID'
                ], 400);
            }

            $target = \App\Models\RoboTarget::where('guid', $targetGuid)->first();

            if (!$target) {
                Log::warning('Target not found for Voyager event', ['targetUID' => $targetGuid]);
                return response()->json([
                    'success' => false,
                    'error' => 'Target not found'
                ], 404);
            }

            // Update target status to executing
            $target->markAsExecuting();

            // Create or update session
            $session = RoboTargetSession::updateOrCreate(
                [
                    'robo_target_id' => $target->id,
                    'session_guid' => $validated['session_guid'] ?? null,
                ],
                [
                    'session_start' => now(),
                    'raw_data' => $request->all(),
                ]
            );

            // Dispatch event (will trigger SendTargetStartedNotifications listener)
            event(new RoboTargetSessionStarted(
                $session,
                $validated['voyager_data'] ?? $request->all()
            ));

            Log::info('Session started event dispatched', [
                'session_id' => $session->id,
                'target' => $target->target_name,
                'user_id' => $target->user_id,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle session started event', [
                'error' => $e->getMessage(),
                'request' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle progress update from Voyager Proxy
     */
    public function progress(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_guid' => 'required|string',
            'progress' => 'required|array',
            'progress.percentage' => 'nullable|numeric',
            'progress.current_shot' => 'nullable|integer',
            'progress.total_shots' => 'nullable|integer',
            'progress.remaining' => 'nullable|integer',
            'progress.camera' => 'nullable|array',
            'progress.mount' => 'nullable|array',
        ]);

        try {
            $session = RoboTargetSession::where('session_guid', $validated['session_guid'])
                ->with('roboTarget')
                ->firstOrFail();

            // Broadcast progress event
            broadcast(new RoboTargetProgress(
                $session,
                $validated['progress']
            ));

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle progress event', [
                'error' => $e->getMessage(),
                'session_guid' => $validated['session_guid'],
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle shot running event from Voyager Proxy (sent every second during exposure)
     */
    public function shotRunning(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_guid' => 'required|string',
            'File' => 'nullable|string',
            'Expo' => 'nullable|numeric',
            'Elapsed' => 'nullable|numeric',
            'ElapsedPerc' => 'nullable|numeric',
            'Status' => 'nullable|integer',
        ]);

        try {
            $session = RoboTargetSession::where('session_guid', $validated['session_guid'])
                ->with('roboTarget')
                ->firstOrFail();

            // Broadcast shot running event
            broadcast(new RoboTargetShotRunning(
                $session,
                $validated
            ));

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle shot running event', [
                'error' => $e->getMessage(),
                'session_guid' => $validated['session_guid'],
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle new image ready event from Voyager Proxy
     */
    public function imageReady(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_guid' => 'required|string',
            'image' => 'required|array',
            'image.filename' => 'nullable|string',
            'image.thumbnail' => 'nullable|string', // Base64 thumbnail
            'image.filter' => 'nullable|string',
            'image.exposure' => 'nullable|numeric',
            'image.hfd' => 'nullable|numeric',
            'image.timestamp' => 'nullable|string',
        ]);

        try {
            $session = RoboTargetSession::where('session_guid', $validated['session_guid'])
                ->with('roboTarget')
                ->firstOrFail();

            // Broadcast image ready event
            broadcast(new RoboTargetImageReady(
                $session,
                $validated['image']
            ));

            Log::info('Image ready event broadcasted', [
                'session_id' => $session->id,
                'filename' => $validated['image']['filename'] ?? 'unknown',
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle image ready event', [
                'error' => $e->getMessage(),
                'session_guid' => $validated['session_guid'],
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle session completed event from Voyager Proxy
     */
    public function sessionCompleted(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'session_guid' => 'required|string',
            'completion_data' => 'nullable|array',
        ]);

        try {
            $session = RoboTargetSession::where('session_guid', $validated['session_guid'])
                ->with('roboTarget.user')
                ->firstOrFail();

            // Broadcast completion event
            broadcast(new RoboTargetSessionCompleted(
                $session,
                $validated['completion_data'] ?? []
            ));

            // TODO: Send completion email notification

            Log::info('Session completed event broadcasted', [
                'session_id' => $session->id,
                'images_accepted' => $session->images_accepted,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle session completed event', [
                'error' => $e->getMessage(),
                'session_guid' => $validated['session_guid'],
            ]);

            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
