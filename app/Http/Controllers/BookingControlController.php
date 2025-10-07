<?php

namespace App\Http\Controllers;

use App\Models\EquipmentBooking;
use App\Services\VoyagerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookingControlController extends BookingController
{
    public function status(Request $request, $locale = null, $booking = null, VoyagerService $voyager): JsonResponse
    {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $booking->loadMissing('equipment');

        $timezone = $this->bookingTimezone();
        $reference = now($timezone);
        $state = $booking->getAccessState($reference);

        if (!in_array($state, ['upcoming', 'active'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La fenêtre de contrôle n\'est pas ouverte pour cette réservation.',
                'booking' => [
                    'state' => $state,
                ],
            ], 422);
        }

        $start = $booking->start_datetime->copy()->setTimezone($timezone);
        $end = $booking->end_datetime->copy()->setTimezone($timezone);
        $secondsToStart = $booking->secondsUntilStart($reference);
        $secondsToEnd = $booking->secondsUntilEnd($reference);

        $control = $voyager->getControlOverview([
            'set_guid' => $booking->voyager_set_guid ?? null,
            'target_guid' => $booking->voyager_target_guid ?? null,
            'state' => $state,
            'seconds_to_start' => $secondsToStart,
            'seconds_to_end' => $secondsToEnd,
            'target_name' => optional($booking->equipment)->name,
        ]);

        return response()->json([
            'status' => 'ok',
            'booking' => [
                'id' => $booking->id,
                'state' => $state,
                'starts_at' => $start->toIso8601String(),
                'ends_at' => $end->toIso8601String(),
                'seconds_to_start' => $secondsToStart,
                'seconds_to_end' => $secondsToEnd,
                'timezone' => $timezone,
            ],
            'control' => $control,
        ]);
    }

    public function abort(Request $request, $locale = null, $booking = null, VoyagerService $voyager): JsonResponse
    {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $timezone = $this->bookingTimezone();
        $reference = now($timezone);

        if (!$booking->isAccessWindowOpen($reference)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La session n\'est pas active, aucune commande d\'arrêt envoyée.',
            ], 422);
        }

        if (!$booking->voyager_target_guid && !$booking->voyager_set_guid) {
            return response()->json([
                'status' => 'error',
                'message' => 'Aucun identifiant Voyager n\'est associé à cette réservation.',
            ], 422);
        }

        $result = $voyager->abortTarget(
            $booking->voyager_target_guid ?? null,
            $booking->voyager_set_guid ?? null
        );

        return response()->json([
            'status' => $result['successful'] ? 'ok' : 'error',
            'message' => $result['message'] ?? null,
            'source' => $result['source'] ?? null,
            'payload' => $result['payload'] ?? null,
        ], $result['successful'] ? 200 : 500);
    }

    public function toggle(Request $request, $locale = null, $booking = null, VoyagerService $voyager): JsonResponse
    {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $data = $request->validate([
            'object_guid' => 'required|string',
            'object_type' => 'required|integer|in:0,1,2',
            'operation' => 'required|integer|in:0,1',
        ]);

        $result = $voyager->toggleObject([
            'object_guid' => $data['object_guid'],
            'object_type' => (int) $data['object_type'],
            'operation' => (int) $data['operation'],
        ]);

        return response()->json([
            'status' => $result['successful'] ? 'ok' : 'error',
            'message' => $result['message'] ?? null,
            'source' => $result['source'] ?? null,
            'payload' => $result['payload'] ?? null,
        ], $result['successful'] ? 200 : 500);
    }

    public function preview(Request $request, $locale = null, $booking = null, VoyagerService $voyager): JsonResponse
    {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $timezone = $this->bookingTimezone();
        $reference = now($timezone);
        $state = $booking->getAccessState($reference);

        if (!in_array($state, ['upcoming', 'active'], true)) {
            return response()->json([
                'status' => 'error',
                'message' => 'La fenêtre d’accès n’est pas ouverte, impossible de récupérer le flux caméra.',
            ], 422);
        }

        $preview = $voyager->getCameraPreview([
            'set_guid' => $booking->voyager_set_guid ?? null,
            'target_guid' => $booking->voyager_target_guid ?? null,
            'target_name' => optional($booking->equipment)->name,
            'state' => $state,
        ]);

        $hasImage = !empty($preview['image']);

        return response()->json([
            'status' => $hasImage ? 'ok' : 'error',
            'preview' => $preview,
        ], $hasImage ? 200 : 500);
    }

    private function assertOwnership(EquipmentBooking $booking): void
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
    }
}
