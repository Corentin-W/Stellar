<?php

namespace App\Http\Controllers;

use App\Models\Equipment;
use App\Models\EquipmentBooking;
use App\Models\EquipmentTimeSlot;
use App\Models\EquipmentBlackout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Afficher le calendrier de réservation
     */
    public function calendar(Request $request)
    {
        $equipments = Equipment::where('is_active', true)
                              ->where('status', 'available')
                              ->orderBy('name')
                              ->get();

        $selectedEquipment = null;
        $activeTimeSlotCount = 0;

        if ($request->filled('equipment_id')) {
            $selectedEquipment = Equipment::findOrFail($request->integer('equipment_id'));
            $activeTimeSlotCount = EquipmentTimeSlot::where('equipment_id', $selectedEquipment->id)
                ->where('is_active', true)
                ->count();
        }

        return view('bookings.calendar', compact('equipments', 'selectedEquipment', 'activeTimeSlotCount'));
    }

    /**
     * API: Récupérer les événements du calendrier
     */
    public function events(Request $request)
    {
        $equipmentId = $request->equipment_id;
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        $events = [];

        if ($equipmentId) {
            // Réservations existantes
            $bookings = EquipmentBooking::where('equipment_id', $equipmentId)
                ->whereIn('status', ['pending', 'confirmed'])
                ->whereBetween('start_datetime', [$start, $end])
                ->with('user')
                ->get();

            foreach ($bookings as $booking) {
                $isOwn = $booking->user_id === auth()->id();

                $events[] = [
                    'id' => 'booking-' . $booking->id,
                    'title' => $isOwn ? 'Ma réservation' : 'Réservé',
                    'start' => $booking->start_datetime->toIso8601String(),
                    'end' => $booking->end_datetime->toIso8601String(),
                    'backgroundColor' => $this->getStatusColor($booking->status),
                    'borderColor' => $this->getStatusColor($booking->status),
                    'classNames' => ['booking-event', 'booking-' . $booking->status],
                    'extendedProps' => [
                        'type' => 'booking',
                        'bookingId' => $booking->id,
                        'status' => $booking->status,
                        'isOwn' => $isOwn,
                        'userName' => $isOwn ? 'Vous' : 'Utilisateur',
                        'cost' => $booking->credits_cost
                    ]
                ];
            }

            // Blocages
            $blackouts = EquipmentBlackout::forEquipment($equipmentId)
                ->whereBetween('start_datetime', [$start, $end])
                ->get();

            foreach ($blackouts as $blackout) {
                $events[] = [
                    'id' => 'blackout-' . $blackout->id,
                    'title' => '🚫 ' . $blackout->reason,
                    'start' => $blackout->start_datetime->toIso8601String(),
                    'end' => $blackout->end_datetime->toIso8601String(),
                    'backgroundColor' => '#ef4444',
                    'borderColor' => '#dc2626',
                    'display' => 'background',
                    'extendedProps' => [
                        'type' => 'blackout',
                        'reason' => $blackout->reason,
                        'description' => $blackout->description
                    ]
                ];
            }
        }

        return response()->json($events);
    }

    /**
     * API: Récupérer les plages horaires actives
     */
    public function timeSlots(Request $request)
    {
        $equipmentId = $request->integer('equipment_id');

        if (!$equipmentId) {
            return response()->json([]);
        }

        $timeSlots = EquipmentTimeSlot::where('equipment_id', $equipmentId)
            ->where('is_active', true)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get(['id', 'day_of_week', 'start_time', 'end_time', 'max_concurrent_bookings']);

        return response()->json($timeSlots);
    }

    /**
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $equipment = Equipment::findOrFail($request->equipment_id);
        $startUtc = Carbon::parse($request->start)->utc();
        $endUtc = Carbon::parse($request->end)->utc();

        $start = $startUtc->copy()->setTimezone($this->bookingTimezone());
        $end = $endUtc->copy()->setTimezone($this->bookingTimezone());

        // Vérifier disponibilité
        $isAvailable = $this->checkAvailability($equipment->id, $startUtc, $endUtc);

        if (!$isAvailable) {
            return back()->with('error', 'Ce créneau n\'est pas disponible.');
        }

        // Calculer le coût
        $hours = $startUtc->diffInHours($endUtc);
        $cost = $equipment->price_per_hour_credits * $hours;

        return view('bookings.create', compact('equipment', 'start', 'end', 'cost'));
    }

    /**
     * Enregistrer une nouvelle réservation
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'required|exists:equipment,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'user_notes' => 'nullable|string|max:1000'
        ]);

        $equipment = Equipment::findOrFail($validated['equipment_id']);
        $startUtc = Carbon::parse($validated['start_datetime'])->utc();
        $endUtc = Carbon::parse($validated['end_datetime'])->utc();

        // Vérifier disponibilité
        if (!$this->checkAvailability($equipment->id, $startUtc, $endUtc)) {
            return back()->with('error', 'Ce créneau n\'est plus disponible.');
        }

        // Calculer coût
        $hours = $startUtc->diffInHours($endUtc);
        $cost = $equipment->price_per_hour_credits * $hours;

        // Vérifier crédits
        if (auth()->user()->credits_balance < $cost) {
            return back()->with('error', 'Crédits insuffisants. Coût: ' . $cost . ' crédits.');
        }

        DB::beginTransaction();
        try {
            // Créer réservation
            $booking = EquipmentBooking::create([
                'equipment_id' => $equipment->id,
                'user_id' => auth()->id(),
                'start_datetime' => $startUtc,
                'end_datetime' => $endUtc,
                'credits_cost' => $cost,
                'status' => 'pending',
                'user_notes' => $validated['user_notes'] ?? null
            ]);

            // Débiter temporairement les crédits (en attente de validation)
            auth()->user()->decrement('credits_balance', $cost);

            DB::commit();

            return redirect()->route('bookings.my-bookings')
                ->with('success', 'Réservation créée ! En attente de validation par un administrateur.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la réservation: ' . $e->getMessage());
        }
    }

    /**
     * Mes réservations
     */
    public function myBookings()
    {
        $bookings = EquipmentBooking::where('user_id', auth()->id())
            ->with('equipment')
            ->orderBy('start_datetime', 'desc')
            ->paginate(15);

        return view('bookings.my-bookings', compact('bookings'));
    }

    /**
     * Afficher/contrôler l'accès à l'équipement pour une réservation donnée.
     */
    public function access(Request $request, $locale = null, $booking = null)
    {
        $booking = $this->resolveBooking($booking);

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        $timezone = $this->bookingTimezone();
        $reference = now($timezone);

        $state = $booking->getAccessState($reference);

        if ($state === 'blocked') {
            abort(403, "Accès non autorisé pour cette réservation.");
        }

        $booking->loadMissing('equipment');

        $currentLocale = $locale ?? app()->getLocale();

        $controlRoutes = [
            'status' => route('bookings.control.status', ['locale' => $currentLocale, 'booking' => $booking]),
            'abort' => route('bookings.control.abort', ['locale' => $currentLocale, 'booking' => $booking]),
            'toggle' => route('bookings.control.toggle', ['locale' => $currentLocale, 'booking' => $booking]),
            'preview' => route('bookings.control.preview', ['locale' => $currentLocale, 'booking' => $booking]),
            'webcam' => config('services.voyager.webcam_url'),
        ];

        return view('bookings.access', [
            'booking' => $booking,
            'equipment' => $booking->equipment,
            'state' => $state,
            'start' => $booking->start_datetime->copy()->setTimezone($timezone),
            'end' => $booking->end_datetime->copy()->setTimezone($timezone),
            'current' => $reference,
            'secondsToStart' => $booking->secondsUntilStart($reference),
            'secondsToEnd' => $booking->secondsUntilEnd($reference),
            'timezoneLabel' => $timezone,
            'controlRoutes' => $controlRoutes,
            'targetPlan' => $booking->target_plan,
        ]);
    }

    /**
     * Annuler une réservation
     */
    public function cancel(Request $request, $locale = null, $booking = null)
    {
        $booking = $this->resolveBooking($booking);

        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canBeCancelled()) {
            return $this->respond($request, [
                'status' => 'error',
                'message' => 'Cette réservation ne peut plus être annulée.',
            ], 422);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason']
            ]);

            $refundAmount = $booking->credits_cost - $booking->credits_refunded;
            auth()->user()->increment('credits_balance', $refundAmount);
            $booking->update(['credits_refunded' => $refundAmount]);

            DB::commit();

            return $this->respond($request, [
                'status' => 'success',
                'message' => 'Réservation annulée et crédits remboursés.',
                'bookingId' => $booking->id,
                'redirect' => $this->redirectToMyBookingsUrl($request),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->respond($request, [
                'status' => 'error',
                'message' => "Erreur lors de l'annulation.",
            ], 500);
        }
    }

    // Méthodes privées
    private function checkAvailability(int $equipmentId, Carbon $startUtc, Carbon $endUtc): bool
    {
        $bookingTimezone = $this->bookingTimezone();
        $startLocal = $startUtc->copy()->setTimezone($bookingTimezone);
        $endLocal = $endUtc->copy()->setTimezone($bookingTimezone);

        $timeSlots = EquipmentTimeSlot::where('equipment_id', $equipmentId)
            ->where('is_active', true)
            ->get();

        $matchingSlot = null;

        if ($timeSlots->isNotEmpty()) {
            $dayOfWeek = $startLocal->dayOfWeek;
            $daySlots = $timeSlots->where('day_of_week', $dayOfWeek);

            foreach ($daySlots as $slot) {
                $slotStart = $startLocal->copy()->setTimeFromTimeString($slot->start_time);
                $slotEnd = $startLocal->copy()->setTimeFromTimeString($slot->end_time);

                if ($slotEnd->lessThanOrEqualTo($slotStart)) {
                    $slotEnd->addDay();
                }

                if ($startLocal->greaterThanOrEqualTo($slotStart) && $endLocal->lessThanOrEqualTo($slotEnd)) {
                    $matchingSlot = $slot;
                    break;
                }
            }

            if (!$matchingSlot) {
                return false;
            }
        }

        $overlappingBookingsQuery = EquipmentBooking::where('equipment_id', $equipmentId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function($query) use ($startUtc, $endUtc) {
                $query->whereBetween('start_datetime', [$startUtc->copy(), $endUtc->copy()])
                      ->orWhereBetween('end_datetime', [$startUtc->copy(), $endUtc->copy()])
                      ->orWhere(function($q) use ($startUtc, $endUtc) {
                          $q->where('start_datetime', '<=', $startUtc->copy())
                            ->where('end_datetime', '>=', $endUtc->copy());
                      });
            });

        if ($matchingSlot) {
            $maxBookings = max(1, $matchingSlot->max_concurrent_bookings ?? 1);

            if ($overlappingBookingsQuery->count() >= $maxBookings) {
                return false;
            }
        } else {
            if ($overlappingBookingsQuery->exists()) {
                return false;
            }
        }

        $hasBlackout = EquipmentBlackout::forEquipment($equipmentId)
            ->where(function($query) use ($startUtc, $endUtc) {
                $query->whereBetween('start_datetime', [$startUtc->copy(), $endUtc->copy()])
                      ->orWhereBetween('end_datetime', [$startUtc->copy(), $endUtc->copy()])
                      ->orWhere(function($q) use ($startUtc, $endUtc) {
                          $q->where('start_datetime', '<=', $startUtc->copy())
                            ->where('end_datetime', '>=', $endUtc->copy());
                      });
            })
            ->exists();

        return !$hasBlackout;
    }

    protected function bookingTimezone(): string
    {
        return config('app.booking_timezone', config('app.timezone', 'UTC'));
    }

    private function getStatusColor($status)
    {
        return match($status) {
            'pending' => '#f59e0b',
            'confirmed' => '#10b981',
            'rejected' => '#ef4444',
            'cancelled' => '#6b7280',
            'completed' => '#3b82f6',
            default => '#9ca3af'
        };
    }

    private function redirectToMyBookings(Request $request)
    {
        return redirect()->to($this->redirectToMyBookingsUrl($request));
    }

    private function redirectToMyBookingsUrl(Request $request): string
    {
        $locale = $request->route('locale') ?? app()->getLocale();

        return route('bookings.my-bookings', ['locale' => $locale]);
    }

    protected function resolveBooking($booking): EquipmentBooking
    {
        if ($booking instanceof EquipmentBooking) {
            return $booking;
        }

        if (empty($booking)) {
            abort(404);
        }

        return EquipmentBooking::findOrFail($booking);
    }

    private function respond(Request $request, array $payload, int $status = 200)
    {
        if ($request->wantsJson() || $request->expectsJson() || $request->isXmlHttpRequest()) {
            return response()->json($payload, $status);
        }

        if ($payload['status'] === 'success') {
            return $this->redirectToMyBookings($request)->with('success', $payload['message']);
        }

        return $this->redirectToMyBookings($request)->with('error', $payload['message']);
    }
}
