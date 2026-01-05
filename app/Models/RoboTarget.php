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
     * Convert RA from HH:MM:SS string to decimal hours
     */
    private function convertRAToDecimal(string $ra): float
    {
        // Parse HH:MM:SS format
        $parts = explode(':', $ra);
        $hours = (float) ($parts[0] ?? 0);
        $minutes = (float) ($parts[1] ?? 0);
        $seconds = (float) ($parts[2] ?? 0);

        // Convert to decimal hours
        return $hours + ($minutes / 60) + ($seconds / 3600);
    }

    /**
     * Convert DEC from DD:MM:SS string to decimal degrees
     */
    private function convertDECToDecimal(string $dec): float
    {
        // Parse +DD:MM:SS or -DD:MM:SS format
        $isNegative = str_starts_with($dec, '-');
        $dec = ltrim($dec, '+-');

        $parts = explode(':', $dec);
        $degrees = (float) ($parts[0] ?? 0);
        $arcminutes = (float) ($parts[1] ?? 0);
        $arcseconds = (float) ($parts[2] ?? 0);

        // Convert to decimal degrees
        $decimal = $degrees + ($arcminutes / 60) + ($arcseconds / 3600);

        return $isNegative ? -$decimal : $decimal;
    }

    /**
     * Conversion to Voyager payload
     *
     * @param string|null $baseSequenceGuid GUID de la BaseSequence (obligatoire)
     */
    public function toVoyagerPayload(?string $baseSequenceGuid = null): array
    {
        return [
            // Identification (required)
            'GuidTarget' => $this->guid,
            'RefGuidSet' => $this->set_guid,
            'RefGuidBaseSequence' => $baseSequenceGuid ?? '', // OBLIGATOIRE selon la documentation Voyager
            'TargetName' => $this->target_name,
            'Tag' => '',
            'DateCreation' => time(),

            // Position (required) - MUST be numeric decimal values!
            'RAJ2000' => $this->convertRAToDecimal($this->ra_j2000),
            'DECJ2000' => $this->convertDECToDecimal($this->dec_j2000),
            'PA' => 0,

            // Status
            'Status' => 0, // 0 = Enabled
            'StatusOp' => 0, // 0 = Idle
            'Priority' => $this->priority,
            'Note' => '',

            // Overrides
            'IsRepeat' => $this->is_repeat,
            'Repeat' => $this->repeat_count ?? 1,
            'IsFinishActualExposure' => false,
            'IsCoolSetPoint' => false,
            'CoolSetPoint' => -10,
            'IsWaitShot' => false,
            'WaitShot' => 0,
            'IsGuideTime' => false,
            'GuideTime' => 2.0,
            'IsOffsetRF' => false,
            'OffsetRF' => 0,

            // Constraints
            'C_ID' => \Illuminate\Support\Str::uuid()->toString(),
            'C_Mask' => $this->c_mask ?? $this->generateConstraintMask(),
            'C_AltMin' => $this->c_alt_min,
            'C_SqmMin' => 0,
            'C_HAStart' => (float) $this->c_ha_start,
            'C_HAEnd' => (float) $this->c_ha_end,
            'C_DateStart' => 0, // OBLIGATOIRE - 0 = pas de contrainte de date de début
            'C_DateEnd' => 0, // OBLIGATOIRE - 0 = pas de contrainte de date de fin
            'C_TimeStart' => 0, // OBLIGATOIRE - 0 = pas de contrainte d'heure de début
            'C_TimeEnd' => 0, // OBLIGATOIRE - 0 = pas de contrainte d'heure de fin
            'C_MoonDown' => $this->c_moon_down,
            'C_MoonPhaseMin' => 0,
            'C_MoonPhaseMax' => 100,
            'C_MoonDistanceDegree' => 30,
            'C_MoonDistanceLorentzian' => 0,
            'C_HFDMeanLimit' => $this->c_hfd_mean_limit ? (float) $this->c_hfd_mean_limit : 0,
            'C_MaxTimeForDay' => 0,
            'C_MaxTime' => 0, // Max time in minutes allowed for target session
            'C_AirMassMin' => 1.0,
            'C_AirMassMax' => 2.5,

            // OneShot constraints
            'C_OSDateStart' => 0, // Date for oneshot target start (0 = not used)
            'C_OSTimeStart' => 0, // Time for oneshot target start (0 = not used)
            'C_OSEarly' => 0, // Minute to start early for oneshot target

            // Preset Time Interval constraints
            'C_PINTEarly' => 0, // Minute to start early for preset time interval target
            'C_PINTReset' => false, // Reset Progress at each sequence run
            'C_PINTIntervals' => [], // JSON object Array of interval

            // Secondary specialized constraints
            'C_Mask2' => '', // OBLIGATOIRE - List of secondary constraints (empty = none active)
            'C_L01' => false, // And Moon Up for Moon Phase min constraints (L)
            'C_M01' => false, // Or Moon Down for Moon phase max constraint (M)
            'C_N01' => false, // Or Moon Down for Moon Distance constraint (N)
            'C_S01' => false, // Or Moon Down for Lorentzian Moon Avoidance constraint (S)

            // Token (Reserved OpenSkyGems)
            'Token' => '', // OBLIGATOIRE - Reserved field, must be empty string

            // Dynamic object fields (REQUIRED!)
            'TType' => 0, // 0 = DSO (Deep Sky Object) - MANDATORY!
            'TKey' => '',
            'TName' => '',
            'IsDynamicPointingOverride' => false,
            'DynamicPointingOverride' => 0,
            'DynEachX_Seconds' => 0,
            'DynEachX_Realign' => false,
            'DynEachX_NoPlateSolve' => false, // No plate solve during realign

            // Shots (will be removed before sending, added separately)
            'Shots' => $this->shots->map(fn($shot) => $shot->toVoyagerPayload())->toArray(),
        ];
    }
}
