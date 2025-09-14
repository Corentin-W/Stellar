<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ObservationSession extends Model
{
    protected $fillable = [
        'user_id',
        'telescope_id',
        'target_name',
        'target_coordinates',
        'status',
        'credits_cost',
        'duration_minutes',
        'scheduled_at',
        'started_at',
        'completed_at',
        'images_captured',
        'weather_conditions',
        'session_data',
        'credit_transaction_id'
    ];

    protected $casts = [
        'target_coordinates' => 'array',
        'credits_cost' => 'integer',
        'duration_minutes' => 'integer',
        'images_captured' => 'integer',
        'scheduled_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'weather_conditions' => 'array',
        'session_data' => 'array'
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class);
    }

    public function imageCaptures(): HasMany
    {
        return $this->hasMany(ImageCapture::class, 'session_id');
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Méthodes
    public function start(): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $this->update([
            'status' => 'active',
            'started_at' => now()
        ]);

        return true;
    }

    public function complete(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'duration_minutes' => $this->started_at ? $this->started_at->diffInMinutes(now()) : 0
        ]);

        return true;
    }

    public function cancel(): bool
    {
        if (!in_array($this->status, ['scheduled', 'active'])) {
            return false;
        }

        $this->update(['status' => 'cancelled']);

        // Rembourser les crédits si nécessaire
        if ($this->credit_transaction_id && $this->credits_cost > 0) {
            $this->user->addCredits(
                $this->credits_cost,
                'refund',
                "Remboursement session annulée: {$this->target_name}",
                $this
            );
        }

        return true;
    }
}
