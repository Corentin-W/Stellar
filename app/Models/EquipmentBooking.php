<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EquipmentBooking extends Model
{
    protected $fillable = [
        'equipment_id',
        'user_id',
        'start_datetime',
        'end_datetime',
        'status',
        'credits_cost',
        'credits_refunded',
        'admin_notes',
        'user_notes',
        'validated_by',
        'validated_at',
        'cancelled_at',
        'cancellation_reason',
        'rejection_reason'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'validated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'credits_cost' => 'integer',
        'credits_refunded' => 'integer'
    ];

    // Relations
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function validator()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_datetime', '>', now())
                     ->whereIn('status', ['pending', 'confirmed']);
    }

    // Helpers
    public function getDurationInHours()
    {
        return $this->start_datetime->diffInHours($this->end_datetime);
    }

    public function getStatusBadgeClass()
    {
        return match($this->status) {
            'pending' => 'bg-yellow-500',
            'confirmed' => 'bg-green-500',
            'rejected' => 'bg-red-500',
            'cancelled' => 'bg-gray-500',
            'completed' => 'bg-blue-500',
            default => 'bg-gray-400'
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'pending' => 'En attente',
            'confirmed' => 'Confirmée',
            'rejected' => 'Rejetée',
            'cancelled' => 'Annulée',
            'completed' => 'Terminée',
            default => 'Inconnu'
        };
    }

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'confirmed'])
               && $this->start_datetime->isFuture();
    }
}
