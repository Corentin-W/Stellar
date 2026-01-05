<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoboTargetShot extends Model
{
    // Shot types
    const TYPE_LIGHT = 0;
    const TYPE_DARK = 1;
    const TYPE_FLAT = 2;
    const TYPE_BIAS = 3;

    protected $fillable = [
        'robo_target_id',
        'filter_index',
        'filter_name',
        'exposure',
        'num',
        'gain',
        'offset',
        'bin',
        'type',
        'order',
    ];

    protected $casts = [
        'filter_index' => 'integer',
        'exposure' => 'integer',
        'num' => 'integer',
        'gain' => 'integer',
        'offset' => 'integer',
        'bin' => 'integer',
        'type' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Relations
     */
    public function roboTarget(): BelongsTo
    {
        return $this->belongsTo(RoboTarget::class);
    }

    /**
     * Getters
     */
    public function getTotalExposureTime(): int
    {
        return $this->exposure * $this->num;
    }

    public function getFormattedExposureTime(): string
    {
        $seconds = $this->getTotalExposureTime();
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;

        if ($hours > 0) {
            return "{$hours}h {$minutes}m";
        } elseif ($minutes > 0) {
            return "{$minutes}m {$secs}s";
        } else {
            return "{$secs}s";
        }
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            self::TYPE_LIGHT => 'Light',
            self::TYPE_DARK => 'Dark',
            self::TYPE_FLAT => 'Flat',
            self::TYPE_BIAS => 'Bias',
            default => 'Unknown'
        };
    }

    /**
     * Conversion to Voyager payload
     */
    public function toVoyagerPayload(): array
    {
        return [
            // Unique identifiers
            'GuidShot' => (string) \Illuminate\Support\Str::uuid(), // Generate unique GUID for shot
            'RefGuidTarget' => '', // Will be set by RoboTargetService

            // Shot configuration
            'Label' => '', // Optional suffix for file name
            'FilterIndex' => $this->filter_index,
            'Num' => $this->num,
            'Bin' => $this->bin,
            'ReadoutMode' => 0, // 0 = default readout mode
            'Type' => $this->type, // 0=Light 1=Bias 2=Dark 3=Flat
            'Speed' => 0, // 0 = default speed
            'Gain' => $this->gain,
            'Offset' => $this->offset,
            'Exposure' => (float) $this->exposure, // Numeric (float)
            'Order' => $this->order ?? -1, // -1 = automatic order
            'Done' => false, // Not used
            'Enabled' => true, // Shot is enabled
        ];
    }

    /**
     * Helpers for common filters
     */
    public static function getCommonFilters(): array
    {
        return [
            ['index' => 0, 'name' => 'Luminance', 'shortname' => 'L'],
            ['index' => 1, 'name' => 'Red', 'shortname' => 'R'],
            ['index' => 2, 'name' => 'Green', 'shortname' => 'G'],
            ['index' => 3, 'name' => 'Blue', 'shortname' => 'B'],
            ['index' => 4, 'name' => 'Ha', 'shortname' => 'Ha'],
            ['index' => 5, 'name' => 'OIII', 'shortname' => 'OIII'],
            ['index' => 6, 'name' => 'SII', 'shortname' => 'SII'],
        ];
    }

    /**
     * Display format for UI
     */
    public function getDisplayName(): string
    {
        return "{$this->filter_name}: {$this->num} x {$this->exposure}s";
    }

    public function getFullDescription(): string
    {
        return "{$this->filter_name}: {$this->num} x {$this->exposure}s @ Gain {$this->gain} Offset {$this->offset} Bin {$this->bin}x{$this->bin}";
    }
}
