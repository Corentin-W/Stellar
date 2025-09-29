<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\EquipmentBooking;
use App\Models\EquipmentTimeSlot;
use App\Models\EquipmentBlackout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BookingAdminController extends Controller
{
    /**
     * Dashboard des réservations
     */
    public function dashboard()
    {
        // Vérifier si les tables existent
        if (!\Schema::hasTable('equipment_bookings')) {
            return view('admin.bookings.dashboard', [
                'stats' => [
                    'pending' => 0,
                    'confirmed' => 0,
                    'total_today' => 0,
                    'total_revenue' => 0,
                ],
                'pendingBookings' => collect([]),
                'recentBookings' => collect([]),
                'equipments' => Equipment::where('is_active', true)->get(),
            ]);
        }

        $stats = [
            'pending' => EquipmentBooking::where('status', 'pending')->count(),
            'confirmed' => EquipmentBooking::where('status', 'confirmed')->count(),
            'total_today' => EquipmentBooking::whereDate('start_datetime', today())->count(),
            'total_revenue' => EquipmentBooking::where('status', 'confirmed')->sum('credits_cost'),
        ];

        $pendingBookings = EquipmentBooking::with(['equipment', 'user'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $recentBookings = EquipmentBooking::with(['equipment', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(20)
            ->get();

        $equipments = Equipment::where('is_active', true)->get();

        return view('admin.bookings.dashboard', compact('stats', 'pendingBookings', 'recentBookings', 'equipments'));
    }

    /**
     * Détails d'une réservation
     */
    public function show(EquipmentBooking $booking)
    {
        $booking->load(['equipment', 'user', 'validator']);
        return view('admin.bookings.show', compact('booking'));
    }

    /**
     * Valider une réservation
     */
    public function confirm(Request $request, EquipmentBooking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Cette réservation ne peut plus être validée.');
        }

        $validated = $request->validate([
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $booking->update([
                'status' => 'confirmed',
                'validated_by' => auth()->id(),
                'validated_at' => now(),
                'admin_notes' => $validated['admin_notes'] ?? null
            ]);

            DB::commit();

            return back()->with('success', 'Réservation confirmée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors de la confirmation.');
        }
    }

    /**
     * Rejeter une réservation
     */
    public function reject(Request $request, EquipmentBooking $booking)
    {
        if ($booking->status !== 'pending') {
            return back()->with('error', 'Cette réservation ne peut plus être rejetée.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:500',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        DB::beginTransaction();
        try {
            $booking->update([
                'status' => 'rejected',
                'validated_by' => auth()->id(),
                'validated_at' => now(),
                'rejection_reason' => $validated['rejection_reason'],
                'admin_notes' => $validated['admin_notes'] ?? null
            ]);

            // Rembourser les crédits
            $refundAmount = $booking->credits_cost - $booking->credits_refunded;
            $booking->user->increment('credits_balance', $refundAmount);
            $booking->update(['credits_refunded' => $refundAmount]);

            DB::commit();

            return back()->with('success', 'Réservation rejetée et crédits remboursés.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erreur lors du rejet.');
        }
    }

    /**
     * Gestion des plages horaires
     */
    public function timeSlots(Equipment $equipment)
    {
        $timeSlots = EquipmentTimeSlot::where('equipment_id', $equipment->id)
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();

        return view('admin.bookings.time-slots', compact('equipment', 'timeSlots'));
    }

    /**
     * Créer une plage horaire
     */
    public function storeTimeSlot(Request $request, Equipment $equipment)
    {
        $validated = $request->validate([
            'day_of_week' => 'required|integer|between:0,6',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'max_concurrent_bookings' => 'required|integer|min:1|max:10',
            'is_active' => 'boolean'
        ]);

        $timeSlot = EquipmentTimeSlot::create([
            'equipment_id' => $equipment->id,
            ...$validated
        ]);

        return back()->with('success', 'Plage horaire créée avec succès.');
    }

    /**
     * Supprimer une plage horaire
     */
    public function destroyTimeSlot(EquipmentTimeSlot $timeSlot)
    {
        $timeSlot->delete();
        return back()->with('success', 'Plage horaire supprimée.');
    }

    /**
     * Gestion des blocages
     */
    public function blackouts()
    {
        $blackouts = EquipmentBlackout::with(['equipment', 'creator'])
            ->where('end_datetime', '>=', now())
            ->orderBy('start_datetime')
            ->paginate(20);

        $equipments = Equipment::where('is_active', true)->get();

        return view('admin.bookings.blackouts', compact('blackouts', 'equipments'));
    }
    

    /**
     * Créer un blocage
     */
    public function storeBlackout(Request $request)
    {
        $validated = $request->validate([
            'equipment_id' => 'nullable|exists:equipment,id',
            'start_datetime' => 'required|date|after:now',
            'end_datetime' => 'required|date|after:start_datetime',
            'reason' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        EquipmentBlackout::create([
            ...$validated,
            'created_by' => auth()->id()
        ]);

        return back()->with('success', 'Blocage créé avec succès.');
    }

    /**
     * Supprimer un blocage
     */
    public function destroyBlackout(EquipmentBlackout $blackout)
    {
        $blackout->delete();
        return back()->with('success', 'Blocage supprimé.');
    }

    /**
     * Calendrier admin avec toutes les réservations
     */
    public function calendar()
    {
        $equipments = Equipment::where('is_active', true)->get();
        return view('admin.bookings.calendar', compact('equipments'));
    }

    /**
     * API: Événements du calendrier admin
     */
    public function calendarEvents(Request $request)
    {
        $start = Carbon::parse($request->start);
        $end = Carbon::parse($request->end);
        $equipmentId = $request->equipment_id;

        $query = EquipmentBooking::with(['user', 'equipment'])
            ->whereBetween('start_datetime', [$start, $end]);

        if ($equipmentId) {
            $query->where('equipment_id', $equipmentId);
        }

        $bookings = $query->get();

        $events = [];
        foreach ($bookings as $booking) {
            $events[] = [
                'id' => 'booking-' . $booking->id,
                'title' => $booking->user->name . ' - ' . $booking->equipment->name,
                'start' => $booking->start_datetime->toIso8601String(),
                'end' => $booking->end_datetime->toIso8601String(),
                'backgroundColor' => $this->getAdminStatusColor($booking->status),
                'borderColor' => $this->getAdminStatusColor($booking->status),
                'url' => route('admin.bookings.show', $booking),
                'extendedProps' => [
                    'status' => $booking->status,
                    'userName' => $booking->user->name,
                    'equipmentName' => $booking->equipment->name,
                    'cost' => $booking->credits_cost
                ]
            ];
        }

        return response()->json($events);
    }

    private function getAdminStatusColor($status)
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
