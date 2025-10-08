<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\EquipmentBooking;
use App\Services\TargetSuggestionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingPreparationController extends Controller
{
    public function show(
        Request $request,
        $locale = null,
        $booking = null,
        TargetSuggestionService $suggestionService
    ): View {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $suggestions = $suggestionService->suggest($booking, 6);

        return view('bookings.prepare', [
            'booking' => $booking,
            'suggestions' => $suggestions,
            'currentPlan' => $booking->target_plan,
        ]);
    }

    public function store(
        Request $request,
        $locale = null,
        $booking = null,
        TargetSuggestionService $suggestionService
    ): RedirectResponse|JsonResponse {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $validated = $request->validate([
            'target_slug' => 'required|string',
            'notes' => 'nullable|string|max:1000',
        ]);

        $target = collect(config('observation_targets', []))
            ->firstWhere('slug', $validated['target_slug']);

        if (!$target) {
            return $this->respond($request, [
                'status' => 'error',
                'message' => 'Cible inconnue.',
            ], 422);
        }

        $suggestion = collect(
            $suggestionService->suggest($booking, count(config('observation_targets', [])))
        )->firstWhere('slug', $target['slug']);

        $plan = [
            'target_slug' => $target['slug'],
            'name' => $target['name'],
            'type' => $target['type'] ?? null,
            'constellation' => $target['constellation'] ?? null,
            'recommended_filters' => $target['recommended_filters'] ?? [],
            'recommended_duration' => $suggestion['recommended_duration'] ?? null,
            'visibility' => $suggestion['visibility'] ?? null,
            'thumbnail' => $target['thumbnail'] ?? null,
            'description' => $target['description'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'selected_at' => now()->toIso8601String(),
        ];

        $booking->update([
            'target_name' => $target['name'],
            'target_ra' => $target['ra_hours'] ?? null,
            'target_dec' => $target['dec_degrees'] ?? null,
            'target_pa' => $target['pa'] ?? 0.0,
            'target_plan' => $plan,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Cible programmée pour votre session.',
                'booking' => $booking->fresh(),
            ]);
        }

        return redirect()
            ->route('bookings.prepare', ['locale' => $locale ?? app()->getLocale(), 'booking' => $booking])
            ->with('success', 'Cible programmée pour votre session.');
    }

    public function destroy(Request $request, $locale = null, $booking = null): RedirectResponse|JsonResponse
    {
        $booking = $this->resolveBooking($booking);
        $this->assertOwnership($booking);

        $booking->update([
            'target_name' => null,
            'target_ra' => null,
            'target_dec' => null,
            'target_pa' => null,
            'target_plan' => null,
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Plan de session réinitialisé.',
            ]);
        }

        return redirect()
            ->route('bookings.prepare', ['locale' => $locale ?? app()->getLocale(), 'booking' => $booking])
            ->with('success', 'Plan de session réinitialisé.');
    }

    private function assertOwnership(EquipmentBooking $booking): void
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function resolveBooking($booking): EquipmentBooking
    {
        if ($booking instanceof EquipmentBooking) {
            return $booking;
        }

        if (empty($booking)) {
            abort(404);
        }

        return EquipmentBooking::findOrFail($booking);
    }
}
