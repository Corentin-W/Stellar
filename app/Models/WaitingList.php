<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WaitingList extends Model
{
    use HasFactory;

    protected $table = 'waiting_list';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'interest_level',
        'ip_address',
        'user_agent',
        'status',
        'confirmed_at',
        'confirmation_token',
        'metadata',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
        'metadata' => 'array',
    ];

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->whereNotNull('confirmed_at');
    }

    public function scopeByInterest($query, $level)
    {
        return $query->where('interest_level', $level);
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function getInterestLabelAttribute()
    {
        $labels = [
            'debutant' => 'Débutant curieux',
            'amateur' => 'Amateur passionné',
            'avance' => 'Utilisateur avancé',
            'professionnel' => 'Professionnel'
        ];

        return $labels[$this->interest_level] ?? $this->interest_level;
    }

    // Méthodes
    public function generateConfirmationToken()
    {
        $this->confirmation_token = Str::random(64);
        $this->save();

        return $this->confirmation_token;
    }

    public function confirm()
    {
        $this->confirmed_at = now();
        $this->confirmation_token = null;
        $this->save();
    }

    public function markAsContacted()
    {
        $this->status = 'contacted';
        $this->save();
    }

    public function markAsConverted()
    {
        $this->status = 'converted';
        $this->save();
    }

    public function isConfirmed()
    {
        return !is_null($this->confirmed_at);
    }

    // Stats statiques
    public static function getStats()
    {
        return [
            'total' => self::count(),
            'confirmed' => self::confirmed()->count(),
            'pending' => self::pending()->count(),
            'by_interest' => [
                'debutant' => self::byInterest('debutant')->count(),
                'amateur' => self::byInterest('amateur')->count(),
                'avance' => self::byInterest('avance')->count(),
                'professionnel' => self::byInterest('professionnel')->count(),
            ],
            'recent' => self::where('created_at', '>=', now()->subDays(7))->count(),
        ];
    }
}
