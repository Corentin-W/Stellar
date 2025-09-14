<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImageCapture extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'filename',
        'file_path',
        'file_size',
        'image_type',
        'filter_used',
        'exposure_time',
        'iso_value',
        'credits_cost',
        'processing_status',
        'metadata',
        'credit_transaction_id'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'exposure_time' => 'decimal:2',
        'iso_value' => 'integer',
        'credits_cost' => 'integer',
        'metadata' => 'array'
    ];

    // Relations
    public function session(): BelongsTo
    {
        return $this->belongsTo(ObservationSession::class, 'session_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creditTransaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class);
    }

    // Scopes
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('image_type', $type);
    }

    public function scopeProcessed($query)
    {
        return $query->where('processing_status', 'processed');
    }

    // Accessors
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getDownloadUrlAttribute()
    {
        return route('images.download', $this->id);
    }
}
