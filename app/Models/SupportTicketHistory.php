<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SupportTicketHistory extends Model
{
    protected $fillable = [
        'ticket_id',
        'user_id',
        'action',
        'old_value',
        'new_value',
        'description',
    ];

    public $timestamps = ['created_at']; // Pas d'updated_at pour l'historique

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(SupportTicket::class, 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getActionLabelAttribute(): string
    {
        return match($this->action) {
            'created' => 'Ticket créé',
            'status_changed' => 'Statut modifié',
            'priority_changed' => 'Priorité modifiée',
            'assigned' => 'Assigné',
            'unassigned' => 'Non assigné',
            'category_changed' => 'Catégorie modifiée',
            'resolved' => 'Résolu',
            'closed' => 'Fermé',
            'reopened' => 'Réouvert',
            default => ucfirst($this->action),
        };
    }
}
