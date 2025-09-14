<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Laravel\Cashier\Billable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'admin',
        'last_login_at',
        'credits_balance',
        'total_credits_purchased',
        'total_credits_used',
        'stripe_customer_id',
        'subscription_type'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'last_login_at' => 'datetime',
            'credits_balance' => 'integer',
            'total_credits_purchased' => 'integer',
            'total_credits_used' => 'integer'
        ];
    }

    /**
     * Vérifie si l'utilisateur est admin
     */
    public function isAdmin(): bool
    {
        return $this->admin == 1;
    }

    public function creditTransactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function observationSessions(): HasMany
    {
        return $this->hasMany(ObservationSession::class);
    }

    public function imageCaptures(): HasMany
    {
        return $this->hasMany(ImageCapture::class);
    }

    public function promotionUsages(): HasMany
    {
        return $this->hasMany(PromotionUsage::class);
    }

    // Méthodes de gestion des crédits à ajouter dans User.php
    public function addCredits(
        int $amount,
        string $type = 'purchase',
        string $description = null,
        $reference = null,
        CreditPackage $package = null,
        User $createdBy = null
    ): CreditTransaction {
        $balanceBefore = $this->credits_balance;
        $balanceAfter = $balanceBefore + $amount;

        // Mettre à jour le solde
        $this->update(['credits_balance' => $balanceAfter]);

        if ($type === 'purchase') {
            $this->increment('total_credits_purchased', $amount);
        }

        // Créer la transaction
        return $this->creditTransactions()->create([
            'type' => $type,
            'credits_amount' => $amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
            'credit_package_id' => $package?->id,
            'created_by' => $createdBy?->id
        ]);
    }

    public function deductCredits(
        int $amount,
        string $description = null,
        $reference = null
    ): CreditTransaction {
        if ($this->credits_balance < $amount) {
            throw new \Exception('Solde de crédits insuffisant');
        }

        $balanceBefore = $this->credits_balance;
        $balanceAfter = $balanceBefore - $amount;

        // Mettre à jour le solde
        $this->update(['credits_balance' => $balanceAfter]);
        $this->increment('total_credits_used', $amount);

        // Créer la transaction
        return $this->creditTransactions()->create([
            'type' => 'usage',
            'credits_amount' => -$amount,
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $description,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id
        ]);
    }

    public function hasEnoughCredits(int $amount): bool
    {
        return $this->credits_balance >= $amount;
    }

    public function getCreditsHistory(int $limit = 50)
    {
        return $this->creditTransactions()
                    ->with(['creditPackage', 'createdBy'])
                    ->latest()
                    ->limit($limit)
                    ->get();
    }

    // Méthodes pour les statistiques
    public function getCreditStats(): array
    {
        return [
            'current_balance' => $this->credits_balance,
            'total_purchased' => $this->total_credits_purchased,
            'total_used' => $this->total_credits_used,
            'efficiency_rate' => $this->total_credits_purchased > 0
                ? round(($this->total_credits_used / $this->total_credits_purchased) * 100, 1)
                : 0,
            'last_purchase' => $this->creditTransactions()
                                ->where('type', 'purchase')
                                ->latest()
                                ->first()?->created_at,
            'monthly_usage' => $this->creditTransactions()
                                ->where('type', 'usage')
                                ->where('created_at', '>=', now()->subMonth())
                                ->sum('credits_amount') * -1
        ];
    }
}
