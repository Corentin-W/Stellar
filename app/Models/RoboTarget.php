<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class RoboTarget extends Model
{
    // Status constants
    const STATUS_PENDING = 'pending';
    const STATUS_ACTIVE = 'active';
    const STATUS_EXECUTING = 'executing';
    const STATUS_COMPLETED = 'completed';
    const STATUS_ERROR = 'error';
    const STATUS_ABORTED = 'aborted';

    protected $fillable = [
        'user_id',
        'guid',
        'set_guid',
        'target_name',
        'ra_j2000',
        'dec_j2000',
        'priority',
        'c_moon_down',
        'c_hfd_mean_limit',
        'c_alt_min',
        'c_ha_start',
        'c_ha_end',
        'c_mask',
        'date_start',
        'date_end',
        'is_repeat',
        'repeat_count',
        'status',
        'estimated_credits',
        'credits_held',
        'credits_charged',
    ];

    protected $casts = [
        'c_moon_down' => 'boolean',
        'c_hfd_mean_limit' => 'decimal:2',
        'c_ha_start' => 'decimal:2',
        'c_ha_end' => 'decimal:2',
        'is_repeat' => 'boolean',
        'date_start' => 'date',
        'date_end' => 'date',
    ];

    /**
     * Boot method - Generate GUID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (!$model->guid) {
                $model->guid = (string) Str::uuid();
            }
            if (!$model->set_guid) {
                $model->set_guid = (string) Str::uuid();
            }
        });
    }

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shots(): HasMany
    {
        return $this->hasMany(RoboTargetShot::class)->orderBy('order');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(RoboTargetSession::class);
    }

    /**
     * Scopes
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeExecuting($query)
    {
        return $query->where('status', self::STATUS_EXECUTING);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Status helpers
     */
    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isExecuting(): bool
    {
        return $this->status === self::STATUS_EXECUTING;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function hasError(): bool
    {
        return $this->status === self::STATUS_ERROR;
    }

    public function isAborted(): bool
    {
        return $this->status === self::STATUS_ABORTED;
    }

    /**
     * Status updates
     */
    public function markAsActive(): bool
    {
        return $this->update(['status' => self::STATUS_ACTIVE]);
    }

    public function markAsExecuting(): bool
    {
        return $this->update(['status' => self::STATUS_EXECUTING]);
    }

    public function markAsCompleted(): bool
    {
        return $this->update(['status' => self::STATUS_COMPLETED]);
    }

    public function markAsError(): bool
    {
        return $this->update(['status' => self::STATUS_ERROR]);
    }

    public function markAsAborted(): bool
    {
        return $this->update(['status' => self::STATUS_ABORTED]);
    }

    /**
     * Credits management
     */
    public function holdCredits(int $amount): bool
    {
        // Vérifier que l'utilisateur a assez de crédits
        if ($this->user->credits_balance < $amount) {
            return false;
        }

        // Débiter les crédits de l'utilisateur
        $this->user->decrement('credits_balance', $amount);

        // Mettre à jour les crédits held
        $this->update([
            'credits_held' => $amount,
            'estimated_credits' => $amount,
        ]);

        return true;
    }

    public function captureCredits(): bool
    {
        // Débiter définitivement les crédits held
        $this->update([
            'credits_charged' => $this->credits_held,
        ]);

        return true;
    }

    public function refundCredits(): bool
    {
        // Rembourser les crédits à l'utilisateur
        $this->user->increment('credits_balance', $this->credits_held);

        // Réinitialiser les crédits held
        $this->update([
            'credits_held' => 0,
            'credits_charged' => 0,
        ]);

        return true;
    }

    /**
     * Constraint Mask generation (C_Mask)
     */
    public function generateConstraintMask(): string
    {
        $mask = '';

        // B = AltMin (toujours présent)
        $mask .= 'B';

        // K = MoonDown
        if ($this->c_moon_down) {
            $mask .= 'K';
        }

        // O = HFD Mean Limit
        if ($this->c_hfd_mean_limit) {
            $mask .= 'O';
        }

        return $mask;
    }

    /**
     * Get status label
     */
    public function getStatusLabel(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => '⏳ En attente',
            self::STATUS_ACTIVE => '✅ Active',
            self::STATUS_EXECUTING => '🔄 En cours',
            self::STATUS_COMPLETED => '✅ Terminée',
            self::STATUS_ERROR => '⚠️ Erreur',
            self::STATUS_ABORTED => '❌ Annulée',
            default => $this->status
        };
    }

    /**
     * Get status color
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_ACTIVE => 'blue',
            self::STATUS_EXECUTING => 'purple',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_ERROR => 'red',
            self::STATUS_ABORTED => 'red',
            default => 'gray'
        };
    }

    /**
     * Get total exposure time (seconds)
     */
    public function getTotalExposureTime(): int
    {
        return $this->shots->sum(function ($shot) {
            return $shot->exposure * $shot->num;
        });
    }

    /**
     * Get estimated duration (including overhead)
     */
    public function getEstimatedDuration(): int
    {
        $exposureTime = $this->getTotalExposureTime();
        $overheadTime = $this->shots->sum('num') * 30; // 30s par image (overhead)

        return $exposureTime + $overheadTime;
    }

    /**
     * Format duration for display
     */
    public function getFormattedDuration(): string
    {
        $seconds = $this->getEstimatedDuration();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);

        return "{$hours}h {$minutes}m";
    }

    /**
     * Get latest session
     */
    public function getLatestSession(): ?RoboTargetSession
    {
        return $this->sessions()->latest()->first();
    }

    /**
     * Has successful session
     */
    public function hasSuccessfulSession(): bool
    {
        return $this->sessions()->where('result', 1)->exists();
    }

    /**
     * Conversion to Voyager payload
     */
    public function toVoyagerPayload(): array
    {
        return [
            'GuidTarget' => $this->guid,
            'RefGuidSet' => $this->set_guid,
            'TargetName' => $this->target_name,
            'RAJ2000' => $this->ra_j2000,
            'DECJ2000' => $this->dec_j2000,
            'Priority' => $this->priority,
            'C_Mask' => $this->c_mask ?? $this->generateConstraintMask(),
            'C_MoonDown' => $this->c_moon_down,
            'C_AltMin' => $this->c_alt_min,
            'C_HAStart' => (float) $this->c_ha_start,
            'C_HAEnd' => (float) $this->c_ha_end,
            'C_HFDMeanLimit' => $this->c_hfd_mean_limit ? (float) $this->c_hfd_mean_limit : 0,
            'DateStart' => $this->date_start?->format('Y-m-d') ?? '',
            'DateEnd' => $this->date_end?->format('Y-m-d') ?? '',
            'IsRepeat' => $this->is_repeat,
            'Shots' => $this->shots->map(fn($shot) => $shot->toVoyagerPayload())->toArray(),
        ];
    }
}
