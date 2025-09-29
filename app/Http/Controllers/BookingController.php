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
        if ($request->has('equipment_id')) {
            $selectedEquipment = Equipment::findOrFail($request->equipment_id);
        }

        return view('bookings.calendar', compact('equipments', 'selectedEquipment'));
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
     * Afficher le formulaire de création
     */
    public function create(Request $request)
    {
        $equipment = Equipment::findOrFail($request->equipment_id);
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);

        // Vérifier disponibilité
        $isAvailable = $this->checkAvailability($equipment->id, $start, $end);

        if (!$isAvailable) {
            return back()->with('error', 'Ce créneau n\'est pas disponible.');
        }

        // Calculer le coût
        $hours = $start->diffInHours($end);
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
        $start = Carbon::parse($validated['start_datetime']);
        $end = Carbon::parse($validated['end_datetime']);

        // Vérifier disponibilité
        if (!$this->checkAvailability($equipment->id, $start, $end)) {
            return back()->with('error', 'Ce créneau n\'est plus disponible.');
        }

        // Calculer coût
        $hours = $start->diffInHours($end);
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
                'start_datetime' => $start,
                'end_datetime' => $end,
                'credits_cost' => $cost,
                'status' => 'pending',
                'user_notes' => $validated['user_notes']
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
     * Annuler une réservation
     */
    public function cancel(Request $request, EquipmentBooking $booking)
    {
        if ($booking->user_id !== auth()->id()) {
            abort(403);
        }

        if (!$booking->canBeCancelled()) {
            return back()->with('error', 'Cette réservation ne peut plus être annulée.');
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

            // Rembourser les crédits
            $refundAmount = $booking->credits_cost - $booking->credits_refunded;
            auth()->user()->increment('credits_balance', $refundAmount);
            $booking->update(['credits_refunded' => $refundAmount]);

            DB::commit();

            return back()->with('success', 'Réservation annulée et crédits remboursés.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de l\'annulation.');
        }
    }

    // Méthodes privées
    private function checkAvailability($equipmentId, $start, $end)
    {
        // Vérifier les réservations existantes
        $hasConflict = EquipmentBooking::where('equipment_id', $equipmentId)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('start_datetime', [$start, $end])
                      ->orWhereBetween('end_datetime', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                      });
            })
            ->exists();

        if ($hasConflict) {
            return false;
        }

        // Vérifier les blocages
        $hasBlackout = EquipmentBlackout::forEquipment($equipmentId)
            ->where(function($query) use ($start, $end) {
                $query->whereBetween('start_datetime', [$start, $end])
                      ->orWhereBetween('end_datetime', [$start, $end])
                      ->orWhere(function($q) use ($start, $end) {
                          $q->where('start_datetime', '<=', $start)
                            ->where('end_datetime', '>=', $end);
                      });
            })
            ->exists();

        return !$hasBlackout;
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
}
