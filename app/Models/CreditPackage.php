<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'credits_amount',
        'price_cents',
        'currency',
        'stripe_price_id',
        'is_active',
        'is_featured',
        'bonus_credits',
        'discount_percentage',
        'sort_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'credits_amount' => 'integer',
        'price_cents' => 'integer',
        'bonus_credits' => 'integer',
        'discount_percentage' => 'integer',
        'sort_order' => 'integer'
    ];

    protected $attributes = [
        'currency' => 'EUR',
        'is_active' => true,
        'is_featured' => false,
        'bonus_credits' => 0,
        'sort_order' => 0
    ];

    // Relations
    public function transactions(): HasMany
    {
        return $this->hasMany(CreditTransaction::class);
    }

    public function successfulTransactions(): HasMany
    {
        return $this->transactions()->where('type', 'purchase');
    }

    // SCOPES - OBLIGATOIRES
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('price_cents', 'asc');
    }

    public function scopeByPriceRange($query, $minCents, $maxCents)
    {
        return $query->whereBetween('price_cents', [$minCents, $maxCents]);
    }

    public function scopeByCreditsRange($query, $minCredits, $maxCredits)
    {
        return $query->whereBetween('credits_amount', [$minCredits, $maxCredits]);
    }

    public function scopePopular($query, $days = 30)
    {
        return $query->withCount(['transactions' => function ($q) use ($days) {
            $q->where('type', 'purchase')
              ->where('created_at', '>=', now()->subDays($days));
        }])->orderBy('transactions_count', 'desc');
    }

    // ACCESSORS - OBLIGATOIRES
    public function getPriceEurosAttribute(): float
    {
        return $this->price_cents / 100;
    }

    public function getTotalCreditsAttribute(): int
    {
        return $this->credits_amount + ($this->bonus_credits ?? 0);
    }

    public function getCreditValueAttribute(): float
    {
        return $this->total_credits > 0 ? round($this->price_euros / $this->total_credits, 4) : 0;
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price_euros, 2) . ' €';
    }

    public function getFormattedCreditsAttribute(): string
    {
        return number_format($this->credits_amount);
    }

    public function getFormattedTotalCreditsAttribute(): string
    {
        return number_format($this->total_credits);
    }

    public function getSavingsPercentageAttribute(): int
    {
        // Calcul des économies par rapport au pack de base (le moins cher actif)
        $basePackage = static::active()->orderBy('price_cents')->first();

        if (!$basePackage || $basePackage->id === $this->id || $basePackage->credit_value === 0) {
            return 0;
        }

        $baseValue = $basePackage->credit_value;
        $thisValue = $this->credit_value;

        if ($thisValue >= $baseValue) {
            return 0; // Pas d'économies
        }

        return (int) round((($baseValue - $thisValue) / $baseValue) * 100);
    }

    public function getValuePropositionAttribute(): string
    {
        if ($this->bonus_credits > 0) {
            $bonusPercentage = round(($this->bonus_credits / $this->credits_amount) * 100);
            return "Inclut {$bonusPercentage}% de crédits bonus";
        }

        if ($this->savings_percentage > 0) {
            return "Économisez {$this->savings_percentage}%";
        }

        return "Valeur : {$this->credit_value}€ par crédit";
    }

    // MUTATORS
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = trim($value);
    }

    public function setPriceEurosAttribute($value)
    {
        $this->attributes['price_cents'] = (int) round($value * 100);
    }

    // MÉTHODES MÉTIER
    public function isApplicableForPromotion($promotion): bool
    {
        if (!$promotion || !isset($promotion->applicable_packages)) {
            return true; // Applicable à tous les packages si pas de restriction
        }

        $applicableIds = is_array($promotion->applicable_packages)
            ? $promotion->applicable_packages
            : json_decode($promotion->applicable_packages, true);

        return in_array($this->id, $applicableIds ?? []);
    }

    public function canBePurchased(): bool
    {
        return $this->is_active
            && $this->price_cents > 0
            && $this->credits_amount > 0;
    }

    public function getRecommendationReason(?User $user = null): string
    {
        if ($this->is_featured) {
            return "Le plus populaire - Meilleur rapport qualité/prix";
        }

        if ($this->savings_percentage > 15) {
            return "Excellente affaire - Économisez {$this->savings_percentage}%";
        }

        if ($this->bonus_credits > 0) {
            $bonusPercentage = round(($this->bonus_credits / $this->credits_amount) * 100);
            return "Crédits bonus inclus (+{$bonusPercentage}%)";
        }

        if ($user && $this->credits_amount <= 500) {
            return "Parfait pour commencer";
        }

        if ($this->credits_amount >= 2000) {
            return "Idéal pour une utilisation intensive";
        }

        return "Bon rapport qualité/prix";
    }

    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    public function toggleFeatured(): bool
    {
        return $this->update(['is_featured' => !$this->is_featured]);
    }

    // MÉTHODES STATIQUES UTILES
    public static function getRecommendedForUser(?User $user = null): \Illuminate\Database\Eloquent\Collection
    {
        if (!$user) {
            return static::active()->featured()->ordered()->take(3)->get();
        }

        // Logique de recommandation simple basée sur l'historique
        return static::active()->ordered()->take(3)->get();
    }

    public static function getMostPopular(int $limit = 3): \Illuminate\Database\Eloquent\Collection
    {
        return static::active()->popular(30)->take($limit)->get();
    }

    public static function getBestValue(): ?CreditPackage
    {
        return static::active()->get()->sortBy('credit_value')->first();
    }

    public static function getStarterPack(): ?CreditPackage
    {
        return static::active()->orderBy('price_cents')->first();
    }

    public static function getPremiumPack(): ?CreditPackage
    {
        return static::active()->orderBy('price_cents', 'desc')->first();
    }

    // CONVERSIONS ET API
    public function toApiArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'credits_amount' => $this->credits_amount,
            'bonus_credits' => $this->bonus_credits,
            'total_credits' => $this->total_credits,
            'price_cents' => $this->price_cents,
            'price_euros' => $this->price_euros,
            'currency' => $this->currency,
            'is_active' => $this->is_active,
            'is_featured' => $this->is_featured,
            'credit_value' => $this->credit_value,
            'savings_percentage' => $this->savings_percentage,
            'value_proposition' => $this->value_proposition
        ];
    }

    // RECHERCHE ET FILTRES
    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    public function scopeFilter($query, array $filters)
    {
        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (isset($filters['featured'])) {
            $query->where('is_featured', $filters['featured']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price_cents', '>=', $filters['min_price'] * 100);
        }

        if (isset($filters['max_price'])) {
            $query->where('price_cents', '<=', $filters['max_price'] * 100);
        }

        return $query;
    }

    // VALIDATION LORS DE LA SAUVEGARDE
    public static function boot()
    {
        parent::boot();

        static::saving(function ($package) {
            // Validation métier
            if ($package->price_cents < 50) { // Minimum Stripe
                throw new \InvalidArgumentException('Le prix minimum est de 0,50€');
            }

            if ($package->credits_amount <= 0) {
                throw new \InvalidArgumentException('Le nombre de crédits doit être positif');
            }

            if ($package->bonus_credits < 0) {
                throw new \InvalidArgumentException('Les crédits bonus ne peuvent pas être négatifs');
            }
        });

        static::deleting(function ($package) {
            // Empêcher la suppression si des transactions existent
            if ($package->transactions()->exists()) {
                throw new \InvalidArgumentException('Impossible de supprimer un package avec des transactions');
            }
        });
    }
}
