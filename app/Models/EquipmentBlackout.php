<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentBlackout extends Model
{
    protected $fillable = [
        'equipment_id',
        'start_datetime',
        'end_datetime',
        'reason',
        'description',
        'created_by'
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime'
    ];

    // Relations
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('end_datetime', '>', now());
    }

    public function scopeForEquipment($query, $equipmentId)
    {
        return $query->where(function($q) use ($equipmentId) {
            $q->where('equipment_id', $equipmentId)
              ->orWhereNull('equipment_id');
        });
    }
}
