<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentTimeSlot extends Model
{
    protected $fillable = [
        'equipment_id',
        'day_of_week',
        'start_time',
        'end_time',
        'is_active',
        'max_concurrent_bookings'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'max_concurrent_bookings' => 'integer',
        'day_of_week' => 'integer'
    ];

    // Relations
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    // Helper methods
    public function getDayName()
    {
        $days = [
            0 => 'Dimanche',
            1 => 'Lundi',
            2 => 'Mardi',
            3 => 'Mercredi',
            4 => 'Jeudi',
            5 => 'Vendredi',
            6 => 'Samedi'
        ];
        return $days[$this->day_of_week] ?? '';
    }

    public function getFormattedTime()
    {
        return substr($this->start_time, 0, 5) . ' - ' . substr($this->end_time, 0, 5);
    }
}
