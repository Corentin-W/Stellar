<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoboTargetSession extends Model
{
    // Result codes from Voyager
    const RESULT_OK = 1;
    const RESULT_ABORTED = 2;
    const RESULT_ERROR = 3;

    protected $fillable = [
        'robo_target_id',
        'session_guid',
        'session_start',
        'session_end',
        'result',
        'result_text',
        'hfd_mean',
        'hfd_stdev',
        'images_captured',
        'images_accepted',
        'images_rejected',
        'raw_data',
    ];

    protected $casts = [
        'session_start' => 'datetime',
        'session_end' => 'datetime',
        'hfd_mean' => 'decimal:2',
        'hfd_stdev' => 'decimal:2',
        'raw_data' => 'array',
    ];

    /**
     * Accessors for backward compatibility
     */
    public function getStatusAttribute(): string
    {
        // Map result code to status string
        return match($this->result) {
            self::RESULT_OK => 'completed',
            self::RESULT_ABORTED => 'aborted',
            self::RESULT_ERROR => 'error',
            default => 'pending'
        };
    }

    public function getStartedAtAttribute()
    {
        return $this->session_start;
    }

    public function getCompletedAtAttribute()
    {
        return $this->session_end;
    }

    public function getTotalDurationAttribute(): ?int
    {
        return $this->getDuration();
    }

    public function getGuidSessionAttribute()
    {
        return $this->session_guid;
    }

    /**
     * Relations
     */
    public function roboTarget(): BelongsTo
    {
        return $this->belongsTo(RoboTarget::class);
    }

    /**
     * Alias for roboTarget relation (for convenience)
     */
    public function target(): BelongsTo
    {
        return $this->roboTarget();
    }

    /**
     * Result checks
     */
    public function isSuccess(): bool
    {
        return $this->result === self::RESULT_OK;
    }

    public function isAborted(): bool
    {
        return $this->result === self::RESULT_ABORTED;
    }

    public function isError(): bool
    {
        return $this->result === self::RESULT_ERROR;
    }

    /**
     * Get result label
     */
    public function getResultLabel(): string
    {
        return match($this->result) {
            self::RESULT_OK => '✅ Succès',
            self::RESULT_ABORTED => '⚠️ Annulée',
            self::RESULT_ERROR => '❌ Erreur',
            default => '❓ Inconnu'
        };
    }

    /**
     * Get result color
     */
    public function getResultColor(): string
    {
        return match($this->result) {
            self::RESULT_OK => 'green',
            self::RESULT_ABORTED => 'yellow',
            self::RESULT_ERROR => 'red',
            default => 'gray'
        };
    }

    /**
     * Session duration
     */
    public function getDuration(): ?int
    {
        if (!$this->session_start || !$this->session_end) {
            return null;
        }

        return $this->session_end->diffInSeconds($this->session_start);
    }

    public function getFormattedDuration(): ?string
    {
        $duration = $this->getDuration();

        if (!$duration) {
            return null;
        }

        $hours = floor($duration / 3600);
        $minutes = floor(($duration % 3600) / 60);

        return "{$hours}h {$minutes}m";
    }

    /**
     * Quality metrics
     */
    public function getAcceptanceRate(): ?float
    {
        if ($this->images_captured === 0) {
            return null;
        }

        return ($this->images_accepted / $this->images_captured) * 100;
    }

    public function getFormattedAcceptanceRate(): ?string
    {
        $rate = $this->getAcceptanceRate();

        if ($rate === null) {
            return null;
        }

        return number_format($rate, 1) . '%';
    }

    public function hasGoodQuality(): bool
    {
        // Considéré comme bonne qualité si :
        // - HFD mean < 3.0 (bonnes étoiles)
        // - Acceptance rate > 70%

        if (!$this->hfd_mean || !$this->getAcceptanceRate()) {
            return false;
        }

        return $this->hfd_mean < 3.0 && $this->getAcceptanceRate() > 70;
    }

    /**
     * Parse and store Voyager event data
     */
    public static function createFromVoyagerEvent(RoboTarget $target, array $eventData): self
    {
        return self::create([
            'robo_target_id' => $target->id,
            'session_guid' => $eventData['guid_session'] ?? null,
            'session_start' => $eventData['session_start'] ?? now(),
            'session_end' => $eventData['session_end'] ?? now(),
            'result' => $eventData['result'] ?? self::RESULT_ERROR,
            'result_text' => $eventData['result_text'] ?? '',
            'hfd_mean' => $eventData['hfd_mean'] ?? null,
            'hfd_stdev' => $eventData['hfd_stdev'] ?? null,
            'images_captured' => $eventData['images_captured'] ?? 0,
            'images_accepted' => $eventData['images_accepted'] ?? 0,
            'images_rejected' => $eventData['images_rejected'] ?? 0,
            'raw_data' => $eventData,
        ]);
    }

    /**
     * Update target status based on result
     */
    public function updateTargetStatus(): void
    {
        match($this->result) {
            self::RESULT_OK => $this->roboTarget->markAsCompleted(),
            self::RESULT_ABORTED => $this->roboTarget->markAsAborted(),
            self::RESULT_ERROR => $this->roboTarget->markAsError(),
            default => null
        };
    }

    /**
     * Handle credits based on result
     */
    public function handleCredits(): void
    {
        if ($this->isSuccess()) {
            // Succès : capture (débit définitif)
            $this->roboTarget->captureCredits();
        } else {
            // Erreur ou Abort : refund
            $this->roboTarget->refundCredits();
        }
    }
}
