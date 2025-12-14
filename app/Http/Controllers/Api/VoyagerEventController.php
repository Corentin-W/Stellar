<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoboTargetSession;
use App\Events\RoboTargetSessionStarted;
use App\Events\RoboTargetProgress;
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
            'session_guid' => 'required|string',
            'target_guid' => 'nullable|string',
            'voyager_data' => 'nullable|array',
        ]);

        try {
            $session = RoboTargetSession::where('session_guid', $validated['session_guid'])
                ->with('roboTarget.user')
                ->firstOrFail();

            // Broadcast event
            broadcast(new RoboTargetSessionStarted(
                $session,
                $validated['voyager_data'] ?? []
            ));

            // Send email notification
            Mail::to($session->roboTarget->user->email)
                ->send(new RoboTargetSessionStartedMail($session));

            Log::info('Session started event broadcasted', [
                'session_id' => $session->id,
                'target' => $session->roboTarget->target_name,
            ]);

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            Log::error('Failed to handle session started event', [
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
