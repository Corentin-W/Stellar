<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportMessage extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'message',
        'is_internal',
        'is_system',
        'attachments',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'is_system' => 'boolean',
        'attachments' => 'array',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachmentFiles(): HasMany
    {
        return $this->hasMany(SupportAttachment::class, 'message_id');
    }

    public function scopePublic($query)
    {
        return $query->where('is_internal', false);
    }

    public function scopeInternal($query)
    {
        return $query->where('is_internal', true);
    }

    public function scopeSystem($query)
    {
        return $query->where('is_system', true);
    }

    public function isFromAdmin(): bool
    {
        return $this->user && $this->user->admin;
    }
}
