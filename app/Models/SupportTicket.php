<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupportTicket extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'assigned_to',
        'ticket_number',
        'subject',
        'priority',
        'status',
        'last_reply_at',
        'last_reply_by',
        'resolved_at',
        'resolved_by',
        'closed_at',
        'closed_by',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'category_id' => 'integer',
        'assigned_to' => 'integer',
        'last_reply_by' => 'integer',
        'resolved_by' => 'integer',
        'closed_by' => 'integer',
        'last_reply_at' => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupportCategory::class, 'category_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function lastReplyBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_reply_by');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function closedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(SupportMessage::class, 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(SupportAttachment::class, 'ticket_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(SupportTicketHistory::class, 'ticket_id');
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

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeAssignedTo($query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeOpen($query)
    {
        return $query->whereIn('status', ['open', 'in_progress', 'waiting_user', 'waiting_admin']);
    }

    public function scopeClosed($query)
    {
        return $query->whereIn('status', ['resolved', 'closed']);
    }

    public function scopeUrgent($query)
    {
        return $query->where('priority', 'urgent');
    }

    // Accesseurs
    public function getStatusLabelAttribute()
    {
        return match($this->status) {
            'open' => 'Ouvert',
            'in_progress' => 'En cours',
            'waiting_user' => 'En attente utilisateur',
            'waiting_admin' => 'En attente admin',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            default => ucfirst($this->status)
        };
    }

    public function getPriorityLabelAttribute()
    {
        return match($this->priority) {
            'low' => 'Faible',
            'normal' => 'Normale',
            'high' => 'Élevée',
            'urgent' => 'Urgente',
            default => ucfirst($this->priority)
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'open' => 'bg-green-100 text-green-800',
            'in_progress' => 'bg-blue-100 text-blue-800',
            'waiting_user' => 'bg-orange-100 text-orange-800',
            'waiting_admin' => 'bg-yellow-100 text-yellow-800',
            'resolved' => 'bg-purple-100 text-purple-800',
            'closed' => 'bg-gray-100 text-gray-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getPriorityColorAttribute()
    {
        return match($this->priority) {
            'low' => 'bg-gray-100 text-gray-800',
            'normal' => 'bg-blue-100 text-blue-800',
            'high' => 'bg-orange-100 text-orange-800',
            'urgent' => 'bg-red-100 text-red-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    public function getIsOverdueAttribute()
    {
        if (in_array($this->status, ['resolved', 'closed'])) {
            return false;
        }

        $hours = match($this->priority) {
            'urgent' => 2,
            'high' => 8,
            'normal' => 24,
            'low' => 48,
            default => 24
        };

        $deadline = $this->last_reply_at ?: $this->created_at;
        return $deadline->addHours($hours)->isPast();
    }

    public function getResponseTimeAttribute()
    {
        if (!$this->last_reply_at || $this->last_reply_by === $this->user_id) {
            return null;
        }

        return $this->created_at->diffForHumans($this->last_reply_at, true);
    }

    public function getResolutionTimeAttribute()
    {
        if (!$this->resolved_at) {
            return null;
        }

        return $this->created_at->diffForHumans($this->resolved_at, true);
    }

    // Méthodes
    public function isOpen()
    {
        return in_array($this->status, ['open', 'in_progress', 'waiting_user', 'waiting_admin']);
    }

    public function isClosed()
    {
        return in_array($this->status, ['resolved', 'closed']);
    }

    public function canBeRepliedTo()
    {
        return $this->status !== 'closed';
    }

    public function canBeAssigned()
    {
        return $this->isOpen();
    }

    public function markAsRead($userId = null)
    {
        // Marquer tous les messages comme lus pour cet utilisateur
        // Cette fonctionnalité pourrait être implémentée plus tard
        return $this;
    }
}
