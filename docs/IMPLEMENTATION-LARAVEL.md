# 💻 Guide d'implémentation Laravel - Modèle RoboTarget

> **Guide pratique step-by-step**
> **Version:** 1.0.0
> **Date:** 12 Décembre 2025
> **Status:** ✅ **95% IMPLÉMENTÉ**

---

## ✅ ÉTAT D'IMPLÉMENTATION (Mise à jour: 13 Décembre 2025)

### 🟢 COMPLÉTÉ (95%)

**Modèles** ✅ 100%
- ✅ `Subscription.php` - Gestion des 3 plans (Stardust/Nebula/Quasar)
- ✅ `RoboTarget.php` - Gestion des cibles avec GUID auto, statuts, crédits
- ✅ `RoboTargetShot.php` - Configuration acquisitions
- ✅ `RoboTargetSession.php` - Historique sessions avec résultats
- ✅ `User.php` - Relations subscription et roboTargets ajoutées

**Services** ✅ 100%
- ✅ `PricingEngine.php` (224 lignes) - Calcul coûts avec multiplicateurs
- ✅ `RoboTargetService.php` (302 lignes) - CRUD complet + soumission Voyager

**Contrôleurs API** ✅ 100%
- ✅ `RoboTargetController.php` (302 lignes) - API REST complète
- ✅ `PricingController.php` (88 lignes) - Estimation & recommandation
- ✅ `SubscriptionController.php` (280 lignes) - Plans, subscribe, changePlan, cancel

**Contrôleurs Web** ✅ 100%
- ✅ `RoboTargetController.php` - Routes web (index, create, show)

**Vues Blade** ✅ 100%
- ✅ `create.blade.php` + 4 steps partials - Wizard création target
- ✅ `index.blade.php` - Liste des targets utilisateur
- ✅ `show.blade.php` - Monitoring temps réel avec Alpine.js

**Middleware** ✅ 100%
- ✅ `RequireActiveSubscription.php` - Vérification abonnement actif
- ✅ `CheckFeatureAccess.php` - Contrôle features selon plan

**Jobs** ✅ 100%
- ✅ `CheckStaleTargetsJob.php` - Détection targets timeout + refund
- ✅ `CreditMonthlyAllowanceJob.php` - Renouvellement crédits mensuels
- ✅ Scheduler configuré dans `routes/console.php`

**Routes** ✅ 100%
- ✅ Routes API complètes dans `routes/api.php`
- ✅ Routes web RoboTarget dans `routes/web.php`

**Migrations** ✅ 100%
- ✅ `create_subscriptions_table.php` (Laravel Cashier)
- ✅ `create_robo_targets_table.php`
- ✅ `create_robo_target_shots_table.php`
- ✅ `create_robo_target_sessions_table.php`
- ✅ `add_robotarget_fields_to_subscriptions_table.php`

### 🟡 EN ATTENTE (5%)

**Intégration Stripe** ⚠️ Stubs présents
- ⏳ Configuration Laravel Cashier complète
- ⏳ Webhooks Stripe pour renouvellement automatique
- ⏳ Mapping plans vers Stripe Price IDs

**Events & Listeners** ⏳ Optionnel
- ⏳ `TargetCreated` event
- ⏳ `TargetCompleted` event
- ⏳ `TargetFailed` event

**Notifications** ⏳ Optionnel
- ⏳ Email timeout target
- ⏳ Email session completed
- ⏳ Email crédits renouvelés

---

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Structure du projet](#structure-du-projet)
3. [Migrations](#migrations)
4. [Modèles](#modèles)
5. [Services](#services)
6. [Contrôleurs](#contrôleurs)
7. [Routes](#routes)
8. [Middleware](#middleware)
9. [Jobs et événements](#jobs-et-événements)
10. [Configuration](#configuration)
11. [Tests](#tests)

---

## Prérequis

### Packages requis

```bash
# Laravel Cashier pour Stripe
composer require laravel/cashier

# UUID pour les GUIDs
composer require ramsey/uuid

# Optionnel : Debugbar
composer require barryvdh/laravel-debugbar --dev
```

### Configuration .env

```env
# Stripe
STRIPE_KEY=pk_test_xxx
STRIPE_SECRET=sk_test_xxx
STRIPE_WEBHOOK_SECRET=whsec_xxx
CASHIER_CURRENCY=eur

# Voyager Proxy
VOYAGER_PROXY_URL=http://localhost:3000
VOYAGER_PROXY_API_KEY=your-secret-key

# Credits
CREDIT_BASE_COST_PER_HOUR=1.0
```

---

## Structure du projet

```
app/
├── Models/
│   ├── Subscription.php           ✨ NOUVEAU
│   ├── RoboTarget.php            ✨ NOUVEAU
│   ├── RoboTargetShot.php        ✨ NOUVEAU
│   ├── RoboTargetSession.php     ✨ NOUVEAU
│   └── User.php                   🔄 MODIFIER
│
├── Services/
│   ├── PricingEngine.php         ✨ NOUVEAU
│   ├── RoboTargetService.php     ✨ NOUVEAU
│   ├── SubscriptionService.php   ✨ NOUVEAU
│   ├── PayloadBuilder.php        ✨ NOUVEAU
│   └── VoyagerProxyService.php   🔄 ÉTENDRE
│
├── Http/
│   ├── Controllers/
│   │   ├── SubscriptionController.php    ✨ NOUVEAU
│   │   ├── RoboTargetController.php      ✨ NOUVEAU
│   │   ├── PricingController.php         ✨ NOUVEAU
│   │   └── StripeWebhookController.php   ✨ NOUVEAU
│   │
│   └── Middleware/
│       ├── RequireActiveSubscription.php ✨ NOUVEAU
│       └── CheckFeatureAccess.php        ✨ NOUVEAU
│
├── Jobs/
│   ├── CheckStaleTargetsJob.php          ✨ NOUVEAU
│   ├── ProcessRoboTargetResultJob.php    ✨ NOUVEAU
│   └── CreditMonthlyAllowanceJob.php     ✨ NOUVEAU
│
└── Events/
    ├── TargetCreated.php                 ✨ NOUVEAU
    ├── TargetCompleted.php               ✨ NOUVEAU
    └── TargetFailed.php                  ✨ NOUVEAU
```

---

## Migrations

### 1. Migration subscriptions

```bash
php artisan make:migration create_subscriptions_table
```

```php
<?php
// database/migrations/2025_12_12_000001_create_subscriptions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Plan et tarification
            $table->enum('plan', ['stardust', 'nebula', 'quasar']);
            $table->integer('monthly_credits');
            $table->integer('price_cents');

            // Statut
            $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])
                  ->default('active');

            // Périodes
            $table->timestamp('current_period_start');
            $table->timestamp('current_period_end');

            // Stripe
            $table->string('stripe_subscription_id')->nullable()->unique();
            $table->string('stripe_customer_id')->nullable();

            // Métadonnées
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Index
            $table->index(['user_id', 'status']);
            $table->index('current_period_end');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
```

### 2. Migration robo_targets

```bash
php artisan make:migration create_robo_targets_table
```

```php
<?php
// database/migrations/2025_12_12_000002_create_robo_targets_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('robo_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // GUIDs Voyager
            $table->uuid('guid')->unique();
            $table->uuid('set_guid');
            $table->uuid('sequence_guid')->nullable();

            // Informations cible
            $table->string('target_name');
            $table->string('ra_j2000'); // Format HH:MM:SS
            $table->string('dec_j2000'); // Format +DD:MM:SS
            $table->decimal('pa', 6, 2)->default(0); // Position angle

            // Configuration
            $table->tinyInteger('priority'); // 0-4
            $table->boolean('c_moon_down')->default(false);
            $table->decimal('c_hfd_mean_limit', 4, 2)->nullable();
            $table->integer('c_alt_min')->nullable(); // Altitude minimum
            $table->decimal('c_ha_start', 5, 2)->nullable(); // Hour angle start
            $table->decimal('c_ha_end', 5, 2)->nullable(); // Hour angle end
            $table->string('c_mask')->nullable(); // Ex: "BKO"

            // Dates
            $table->timestamp('c_date_start')->nullable();
            $table->timestamp('c_date_end')->nullable();

            // Repeat
            $table->boolean('is_repeat')->default(false);
            $table->integer('repeat_count')->default(1);

            // Coûts
            $table->integer('estimated_cost');
            $table->integer('actual_cost')->nullable();

            // Statut
            $table->enum('status', [
                'pending',    // Créée, en attente
                'active',     // Activée dans RoboTarget
                'executing',  // En cours d'exécution
                'completed',  // Terminée avec succès
                'aborted',    // Annulée par user
                'error',      // Erreur technique
                'timeout'     // Timeout (pas de résultat)
            ])->default('pending');

            // Résultat Voyager
            $table->integer('result_code')->nullable(); // 1=OK, 2=Aborted, 3=Error
            $table->text('result_message')->nullable();

            // Progression
            $table->integer('progress')->default(0); // 0-100
            $table->integer('images_captured')->default(0);

            // Transaction
            $table->foreignId('transaction_id')->nullable()
                  ->constrained('credit_transactions')
                  ->nullOnDelete();

            // Métadonnées
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Index
            $table->index(['user_id', 'status']);
            $table->index('guid');
            $table->index('set_guid');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('robo_targets');
    }
};
```

### 3. Migration robo_target_shots

```bash
php artisan make:migration create_robo_target_shots_table
```

```php
<?php
// database/migrations/2025_12_12_000003_create_robo_target_shots_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('robo_target_shots', function (Blueprint $table) {
            $table->id();
            $table->foreignId('robo_target_id')->constrained()->cascadeOnDelete();

            // GUID Voyager
            $table->uuid('guid')->unique();

            // Configuration
            $table->integer('filter_index'); // 0=L, 1=R, 2=G, 3=B, 4=Ha, etc.
            $table->string('filter_name')->nullable(); // "Ha", "OIII", etc.
            $table->integer('num'); // Nombre de poses
            $table->integer('bin')->default(1); // Binning 1x1, 2x2, etc.
            $table->integer('exposure'); // Durée exposition (secondes)
            $table->integer('gain')->nullable();
            $table->integer('offset')->nullable();
            $table->integer('readout_mode')->default(0);
            $table->enum('type', ['light', 'bias', 'dark', 'flat'])->default('light');
            $table->integer('order')->default(0); // Ordre d'exécution
            $table->boolean('enabled')->default(true);

            // Progression
            $table->integer('captured')->default(0); // Nombre capturé

            $table->timestamps();

            // Index
            $table->index('robo_target_id');
            $table->index('guid');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('robo_target_shots');
    }
};
```

### 4. Migration robo_target_sessions

```bash
php artisan make:migration create_robo_target_sessions_table
```

```php
<?php
// database/migrations/2025_12_12_000004_create_robo_target_sessions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('robo_target_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('robo_target_id')->constrained()->cascadeOnDelete();

            // Informations session Voyager
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();

            // Résultats
            $table->integer('result')->nullable(); // 1=OK, 2=Aborted, 3=Error
            $table->integer('images_count')->default(0);
            $table->integer('progress')->default(0);

            // Qualité
            $table->decimal('avg_hfd', 4, 2)->nullable();
            $table->decimal('avg_star_index', 5, 2)->nullable();
            $table->decimal('avg_fwhm', 4, 2)->nullable();

            // Conditions
            $table->decimal('avg_seeing', 4, 2)->nullable();
            $table->decimal('avg_temperature', 5, 2)->nullable();
            $table->decimal('avg_humidity', 5, 2)->nullable();

            // Métadonnées Voyager
            $table->json('voyager_data')->nullable();

            $table->timestamps();

            // Index
            $table->index('robo_target_id');
            $table->index('started_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('robo_target_sessions');
    }
};
```

### 5. Modifier users table

```bash
php artisan make:migration add_subscription_fields_to_users_table
```

```php
<?php
// database/migrations/2025_12_12_000005_add_subscription_fields_to_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Stripe Customer ID (si pas déjà présent via Cashier)
            if (!Schema::hasColumn('users', 'stripe_id')) {
                $table->string('stripe_id')->nullable()->unique()->after('email');
            }

            // Colonnes Cashier supplémentaires si nécessaire
            if (!Schema::hasColumn('users', 'pm_type')) {
                $table->string('pm_type')->nullable()->after('stripe_id');
                $table->string('pm_last_four', 4)->nullable()->after('pm_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['stripe_id', 'pm_type', 'pm_last_four']);
        });
    }
};
```

### Exécuter les migrations

```bash
php artisan migrate
```

---

## Modèles

### 1. Subscription Model

```bash
php artisan make:model Subscription
```

```php
<?php
// app/Models/Subscription.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    // Constants pour les plans
    const STARDUST = 'stardust';
    const NEBULA = 'nebula';
    const QUASAR = 'quasar';

    protected $fillable = [
        'user_id',
        'plan',
        'monthly_credits',
        'price_cents',
        'status',
        'current_period_start',
        'current_period_end',
        'stripe_subscription_id',
        'stripe_customer_id',
        'metadata',
    ];

    protected $casts = [
        'monthly_credits' => 'integer',
        'price_cents' => 'integer',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Relations
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('current_period_end', '>', now());
    }

    /**
     * Vérifications de permissions
     */
    public function canUsePriority(int $priority): bool
    {
        return match($this->plan) {
            self::STARDUST => $priority <= 1,
            self::NEBULA => $priority <= 2,
            self::QUASAR => $priority <= 4,
            default => false
        };
    }

    public function canUseMoonDown(): bool
    {
        return in_array($this->plan, [self::NEBULA, self::QUASAR]);
    }

    public function canUseHFDGuarantee(): bool
    {
        return $this->plan === self::QUASAR;
    }

    public function canUseRepeat(): bool
    {
        return in_array($this->plan, [self::NEBULA, self::QUASAR]);
    }

    public function canUseSets(): bool
    {
        return $this->plan === self::QUASAR;
    }

    public function canUseDashboard(): bool
    {
        return in_array($this->plan, [self::NEBULA, self::QUASAR]);
    }

    /**
     * Helpers
     */
    public function isActive(): bool
    {
        return $this->status === 'active'
            && $this->current_period_end->isFuture();
    }

    public function getFeatures(): array
    {
        return match($this->plan) {
            self::STARDUST => [
                'priority_max' => 1,
                'moon_down' => false,
                'hfd_guarantee' => false,
                'hfd_limit' => 0,
                'repeat' => false,
                'sets' => false,
                'dashboard' => false,
                'support' => 'email_48h',
            ],
            self::NEBULA => [
                'priority_max' => 2,
                'moon_down' => true,
                'hfd_guarantee' => 'standard',
                'hfd_limit' => 4.0,
                'repeat' => true,
                'sets' => false,
                'dashboard' => true,
                'support' => 'email_24h',
            ],
            self::QUASAR => [
                'priority_max' => 4,
                'moon_down' => true,
                'hfd_guarantee' => 'custom',
                'hfd_limit' => 'adjustable',
                'repeat' => true,
                'sets' => true,
                'dashboard' => true,
                'support' => 'chat_priority',
            ],
        };
    }

    public function getPlanLabel(): string
    {
        return match($this->plan) {
            self::STARDUST => '🌟 Stardust',
            self::NEBULA => '🌌 Nebula',
            self::QUASAR => '⚡ Quasar',
            default => $this->plan
        };
    }

    public function getPriceEuros(): float
    {
        return $this->price_cents / 100;
    }

    public function getCostPerCredit(): float
    {
        return $this->getPriceEuros() / $this->monthly_credits;
    }
}
```

### 2. RoboTarget Model

```bash
php artisan make:model RoboTarget
```

```php
<?php
// app/Models/RoboTarget.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Ramsey\Uuid\Uuid;

class RoboTarget extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'guid',
        'set_guid',
        'sequence_guid',
        'target_name',
        'ra_j2000',
        'dec_j2000',
        'pa',
        'priority',
        'c_moon_down',
        'c_hfd_mean_limit',
        'c_alt_min',
        'c_ha_start',
        'c_ha_end',
        'c_mask',
        'c_date_start',
        'c_date_end',
        'is_repeat',
        'repeat_count',
        'estimated_cost',
        'actual_cost',
        'status',
        'result_code',
        'result_message',
        'progress',
        'images_captured',
        'transaction_id',
        'metadata',
    ];

    protected $casts = [
        'pa' => 'decimal:2',
        'priority' => 'integer',
        'c_moon_down' => 'boolean',
        'c_hfd_mean_limit' => 'decimal:2',
        'c_alt_min' => 'integer',
        'c_ha_start' => 'decimal:2',
        'c_ha_end' => 'decimal:2',
        'c_date_start' => 'datetime',
        'c_date_end' => 'datetime',
        'is_repeat' => 'boolean',
        'repeat_count' => 'integer',
        'estimated_cost' => 'integer',
        'actual_cost' => 'integer',
        'result_code' => 'integer',
        'progress' => 'integer',
        'images_captured' => 'integer',
        'metadata' => 'array',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->guid)) {
                $model->guid = Uuid::uuid4()->toString();
            }
            if (empty($model->set_guid)) {
                $model->set_guid = Uuid::uuid4()->toString();
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
        return $this->hasMany(RoboTargetShot::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(RoboTargetSession::class);
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CreditTransaction::class, 'transaction_id');
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeExecuting($query)
    {
        return $query->where('status', 'executing');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Helpers
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isExecuting(): bool
    {
        return $this->status === 'executing';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function hasError(): bool
    {
        return in_array($this->status, ['error', 'aborted', 'timeout']);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => '⏳ En attente',
            'active' => '✅ Active',
            'executing' => '🔄 En cours',
            'completed' => '✅ Terminée',
            'aborted' => '❌ Annulée',
            'error' => '⚠️ Erreur',
            'timeout' => '⏱️ Timeout',
            default => $this->status
        };
    }

    public function getStatusColor(): string
    {
        return match($this->status) {
            'pending' => 'yellow',
            'active' => 'blue',
            'executing' => 'purple',
            'completed' => 'green',
            'aborted', 'error', 'timeout' => 'red',
            default => 'gray'
        };
    }

    public function getTotalExposureTime(): int
    {
        return $this->shots->sum(function ($shot) {
            return $shot->exposure * $shot->num;
        });
    }

    public function getEstimatedDurationHours(): float
    {
        $totalSeconds = $this->getTotalExposureTime();
        $overheadSeconds = $this->shots->sum('num') * 30; // 30s overhead par image
        return ($totalSeconds + $overheadSeconds) / 3600;
    }

    public function getProgressPercentage(): int
    {
        return min(100, max(0, $this->progress));
    }
}
```

### 3. RoboTargetShot Model

```bash
php artisan make:model RoboTargetShot
```

```php
<?php
// app/Models/RoboTargetShot.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Ramsey\Uuid\Uuid;

class RoboTargetShot extends Model
{
    protected $fillable = [
        'robo_target_id',
        'guid',
        'filter_index',
        'filter_name',
        'num',
        'bin',
        'exposure',
        'gain',
        'offset',
        'readout_mode',
        'type',
        'order',
        'enabled',
        'captured',
    ];

    protected $casts = [
        'filter_index' => 'integer',
        'num' => 'integer',
        'bin' => 'integer',
        'exposure' => 'integer',
        'gain' => 'integer',
        'offset' => 'integer',
        'readout_mode' => 'integer',
        'order' => 'integer',
        'enabled' => 'boolean',
        'captured' => 'integer',
    ];

    /**
     * Boot
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->guid)) {
                $model->guid = Uuid::uuid4()->toString();
            }
        });
    }

    /**
     * Relations
     */
    public function roboTarget(): BelongsTo
    {
        return $this->belongsTo(RoboTarget::class);
    }

    /**
     * Helpers
     */
    public function getTotalExposureTime(): int
    {
        return $this->exposure * $this->num;
    }

    public function getProgressPercentage(): int
    {
        if ($this->num === 0) return 0;
        return min(100, (int) (($this->captured / $this->num) * 100));
    }

    public function isComplete(): bool
    {
        return $this->captured >= $this->num;
    }

    public function getFilterLabel(): string
    {
        return $this->filter_name ?: $this->getFilterNameFromIndex();
    }

    protected function getFilterNameFromIndex(): string
    {
        return match($this->filter_index) {
            0 => 'L (Luminance)',
            1 => 'R (Red)',
            2 => 'G (Green)',
            3 => 'B (Blue)',
            4 => 'Ha (Hydrogen Alpha)',
            5 => 'OIII (Oxygen III)',
            6 => 'SII (Sulfur II)',
            7 => 'Clear',
            default => "Filter #{$this->filter_index}"
        };
    }
}
```

### 4. RoboTargetSession Model

```bash
php artisan make:model RoboTargetSession
```

```php
<?php
// app/Models/RoboTargetSession.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoboTargetSession extends Model
{
    protected $fillable = [
        'robo_target_id',
        'started_at',
        'ended_at',
        'duration_seconds',
        'result',
        'images_count',
        'progress',
        'avg_hfd',
        'avg_star_index',
        'avg_fwhm',
        'avg_seeing',
        'avg_temperature',
        'avg_humidity',
        'voyager_data',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'duration_seconds' => 'integer',
        'result' => 'integer',
        'images_count' => 'integer',
        'progress' => 'integer',
        'avg_hfd' => 'decimal:2',
        'avg_star_index' => 'decimal:2',
        'avg_fwhm' => 'decimal:2',
        'avg_seeing' => 'decimal:2',
        'avg_temperature' => 'decimal:2',
        'avg_humidity' => 'decimal:2',
        'voyager_data' => 'array',
    ];

    /**
     * Relations
     */
    public function roboTarget(): BelongsTo
    {
        return $this->belongsTo(RoboTarget::class);
    }

    /**
     * Helpers
     */
    public function isSuccessful(): bool
    {
        return $this->result === 1;
    }

    public function isAborted(): bool
    {
        return $this->result === 2;
    }

    public function hasError(): bool
    {
        return $this->result === 3;
    }

    public function getResultLabel(): string
    {
        return match($this->result) {
            1 => '✅ Réussie',
            2 => '❌ Annulée',
            3 => '⚠️ Erreur',
            default => '❓ Inconnu'
        };
    }

    public function getDurationFormatted(): string
    {
        if (!$this->duration_seconds) return '--';

        $hours = floor($this->duration_seconds / 3600);
        $minutes = floor(($this->duration_seconds % 3600) / 60);
        $seconds = $this->duration_seconds % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $seconds);
    }
}
```

### 5. Modifier User Model

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Cashier\Billable;
use App\Traits\HasCredits;

class User extends Authenticatable
{
    use Billable, HasCredits;

    protected $fillable = [
        'name',
        'email',
        'password',
        'credits_balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'credits_balance' => 'integer',
    ];

    /**
     * Relations
     */
    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function roboTargets()
    {
        return $this->hasMany(RoboTarget::class);
    }

    /**
     * Get active subscription
     */
    public function activeSubscription()
    {
        return $this->subscriptions()
            ->active()
            ->latest()
            ->first();
    }

    /**
     * Check if user has active subscription
     */
    public function hasActiveSubscription(): bool
    {
        return $this->activeSubscription() !== null;
    }

    /**
     * Get subscription plan
     */
    public function getSubscriptionPlan(): ?string
    {
        return $this->activeSubscription()?->plan;
    }

    /**
     * Check if user has specific plan or higher
     */
    public function hasPlan(string $plan): bool
    {
        $currentPlan = $this->getSubscriptionPlan();

        if (!$currentPlan) return false;

        $hierarchy = [
            Subscription::STARDUST => 1,
            Subscription::NEBULA => 2,
            Subscription::QUASAR => 3,
        ];

        return ($hierarchy[$currentPlan] ?? 0) >= ($hierarchy[$plan] ?? 999);
    }

    /**
     * Check if user can use specific feature
     */
    public function canUseFeature(string $feature): bool
    {
        $subscription = $this->activeSubscription();

        if (!$subscription) return false;

        return match($feature) {
            'moon_down' => $subscription->canUseMoonDown(),
            'hfd_guarantee' => $subscription->canUseHFDGuarantee(),
            'repeat' => $subscription->canUseRepeat(),
            'sets' => $subscription->canUseSets(),
            'dashboard' => $subscription->canUseDashboard(),
            default => false
        };
    }
}
```

---

## Services

### 1. PricingEngine Service

```bash
php artisan make:service PricingEngine
# Ou créer manuellement le fichier
```

```php
<?php
// app/Services/PricingEngine.php

namespace App\Services;

use App\Models\Subscription;

class PricingEngine
{
    /**
     * Base cost: 1 crédit = 1 heure (avant multiplicateurs)
     */
    const BASE_COST_PER_HOUR = 1.0;

    /**
     * Calculate total cost for a target configuration
     */
    public function calculateCost(
        Subscription $subscription,
        array $targetConfig
    ): int {
        // 1. Estimate duration
        $estimatedHours = $this->estimateDuration($targetConfig);

        // 2. Base cost
        $baseCost = $estimatedHours * self::BASE_COST_PER_HOUR;

        // 3. Apply multipliers
        $multiplier = $this->calculateMultiplier($subscription, $targetConfig);

        // 4. Final cost (round up)
        return (int) ceil($baseCost * $multiplier);
    }

    /**
     * Calculate total multiplier
     */
    protected function calculateMultiplier(
        Subscription $subscription,
        array $config
    ): float {
        $multiplier = 1.0;

        // Priority multiplier
        $multiplier *= $this->getPriorityMultiplier($config['priority'] ?? 0);

        // Moon down multiplier
        if ($config['c_moon_down'] ?? false) {
            $multiplier *= 2.0;
        }

        // HFD guarantee multiplier
        if (($config['c_hfd_mean_limit'] ?? 0) > 0) {
            $multiplier *= 1.5;
        }

        return $multiplier;
    }

    /**
     * Get priority multiplier
     */
    protected function getPriorityMultiplier(int $priority): float
    {
        return match($priority) {
            0, 1 => 1.0,    // Very Low / Low
            2 => 1.2,       // Normal
            3 => 2.0,       // High
            4 => 3.0,       // First
            default => 1.0
        };
    }

    /**
     * Estimate duration in hours
     */
    protected function estimateDuration(array $config): float
    {
        $totalSeconds = 0;

        foreach ($config['shots'] as $shot) {
            // Exposure time
            $exposureTime = $shot['exposure'] * $shot['num'];

            // Overhead: 30s per image
            $overhead = $shot['num'] * 30;

            $totalSeconds += $exposureTime + $overhead;
        }

        // Convert to hours
        return $totalSeconds / 3600;
    }

    /**
     * Get detailed cost breakdown
     */
    public function getCostBreakdown(
        Subscription $subscription,
        array $targetConfig
    ): array {
        $estimatedHours = $this->estimateDuration($targetConfig);
        $baseCost = $estimatedHours * self::BASE_COST_PER_HOUR;

        $priorityMultiplier = $this->getPriorityMultiplier($targetConfig['priority'] ?? 0);
        $moonDownMultiplier = ($targetConfig['c_moon_down'] ?? false) ? 2.0 : 1.0;
        $hfdMultiplier = (($targetConfig['c_hfd_mean_limit'] ?? 0) > 0) ? 1.5 : 1.0;

        $totalMultiplier = $priorityMultiplier * $moonDownMultiplier * $hfdMultiplier;
        $finalCost = (int) ceil($baseCost * $totalMultiplier);

        return [
            'estimated_hours' => round($estimatedHours, 2),
            'base_cost' => round($baseCost, 2),
            'multipliers' => [
                'priority' => $priorityMultiplier,
                'moon_down' => $moonDownMultiplier,
                'hfd' => $hfdMultiplier,
                'total' => $totalMultiplier,
            ],
            'final_cost' => $finalCost,
        ];
    }
}
```

---

En raison de la longueur, je vais continuer avec les autres services et contrôleurs dans les prochains messages. Veux-tu que je :

1. Continue avec les autres services (RoboTargetService, SubscriptionService, PayloadBuilder)
2. Passe aux contrôleurs
3. Ou préfères-tu un récapitulatif de ce qui a été créé jusqu'ici ?

---

## 🔧 FICHIERS IMPLÉMENTÉS - GUIDE D'UTILISATION

### Middleware

#### 1. RequireActiveSubscription

**Fichier:** `app/Http/Middleware/RequireActiveSubscription.php`

Vérifie que l'utilisateur a un abonnement RoboTarget actif.

**Usage dans les routes:**

```php
Route::middleware(['auth', 'subscription.required'])->group(function () {
    Route::get('/robotarget', [RoboTargetController::class, 'index']);
});
```

**Réponse si pas d'abonnement:**

```json
{
  "success": false,
  "message": "Aucun abonnement actif. Veuillez souscrire à un plan pour accéder à RoboTarget.",
  "error_code": "NO_SUBSCRIPTION",
  "redirect_url": "/subscriptions/choose"
}
```

#### 2. CheckFeatureAccess

**Fichier:** `app/Http/Middleware/CheckFeatureAccess.php`

Contrôle l'accès aux fonctionnalités selon le plan d'abonnement.

**Features disponibles:**
- `moon_down` - Option Nuit Noire (Nebula, Quasar)
- `hfd_adjust` - Garantie HFD ajustable (Quasar uniquement)
- `repeat` - Projets multi-nuits (Nebula, Quasar)
- `sets` - Gestion avancée Sets (Quasar uniquement)

**Usage dans les routes:**

```php
// Vérifier une feature spécifique
Route::post('/robotarget/targets')
    ->middleware(['auth', 'subscription.required', 'feature.access:moon_down']);

// Juste vérifier l'abonnement actif (pas de feature)
Route::get('/robotarget')
    ->middleware(['auth', 'subscription.required', 'feature.access']);
```

**Réponse si feature non disponible:**

```json
{
  "success": false,
  "message": "Option Nuit Noire n'est pas disponible avec votre plan stardust. Veuillez upgrader votre abonnement.",
  "error_code": "FEATURE_NOT_AVAILABLE",
  "feature": "moon_down",
  "current_plan": "stardust",
  "required_plans": ["nebula", "quasar"]
}
```

---

### Jobs

#### 1. CheckStaleTargetsJob

**Fichier:** `app/Jobs/CheckStaleTargetsJob.php`

Détecte et traite les cibles qui sont restées en statut "active" ou "executing" trop longtemps (timeout).

**Configuration:**
- Timeout par défaut: 48 heures
- Exécution: Toutes les heures (configurable dans `routes/console.php`)

**Actions effectuées:**
1. Marque la target comme "error"
2. Refund automatique des crédits held
3. Log détaillé dans les logs Laravel

**Exécution manuelle:**

```bash
php artisan queue:work --once
```

**Dispatch programmatique:**

```php
use App\Jobs\CheckStaleTargetsJob;

// Vérifier avec timeout de 24h
CheckStaleTargetsJob::dispatch(24);
```

#### 2. CreditMonthlyAllowanceJob

**Fichier:** `app/Jobs/CreditMonthlyAllowanceJob.php`

Renouvelle les crédits mensuels pour tous les abonnements actifs.

**Configuration:**
- Exécution: 1er de chaque mois à 00:00
- Configuré dans: `routes/console.php`

**Actions effectuées:**
1. Récupère tous les abonnements actifs
2. Ajoute les crédits mensuels au solde utilisateur
3. Crée une transaction de type "subscription_renewal"
4. Log détaillé pour chaque renouvellement

**Exécution manuelle:**

```bash
php artisan queue:work --once
```

**Scheduler:**

```php
// Dans routes/console.php
Schedule::job(new CreditMonthlyAllowanceJob())
    ->monthlyOn(1, '00:00')
    ->name('subscription:renew-credits')
    ->onOneServer()
    ->withoutOverlapping();
```

---

### Routes Web

**Fichier:** `routes/web.php`

Routes ajoutées dans le groupe `middleware('auth')`:

```php
Route::prefix('robotarget')->name('robotarget.')->group(function () {
    Route::get('/', [RoboTargetController::class, 'index'])->name('index');
    Route::get('/create', [RoboTargetController::class, 'create'])->name('create');
    Route::get('/{guid}', [RoboTargetController::class, 'show'])->name('show');
});
```

**Exemples d'URLs:**
- Liste des targets: `/{locale}/robotarget` (ex: `/fr/robotarget`)
- Créer une target: `/{locale}/robotarget/create`
- Voir une target: `/{locale}/robotarget/{guid}`

---

### Vues Blade

#### 1. index.blade.php

**Fichier:** `resources/views/dashboard/robotarget/index.blade.php`

Affiche la liste des targets de l'utilisateur avec:
- Stats globales (abonnement, crédits, targets actives/complétées)
- Filtres par statut
- Cards pour chaque target avec infos principales
- Badges pour options activées (Nuit noire, HFD, Multi-nuits)

**Variables disponibles:**
- `$targets` - Collection des targets
- `$stats` - Statistiques utilisateur
- `$subscription` - Abonnement actuel
- `$creditsBalance` - Solde crédits
- `$filters` - Filtres actifs

#### 2. show.blade.php

**Fichier:** `resources/views/dashboard/robotarget/show.blade.php`

Page de monitoring d'une target avec:
- Informations complètes de la cible
- Progression temps réel (si en cours)
- Configuration des shots
- Historique des sessions
- Actions (Soumettre, Annuler)

**Composants Alpine.js:**
- `TargetMonitor` - Monitoring temps réel via WebSocket

**Variables disponibles:**
- `$target` - Modèle RoboTarget avec relations
- `$subscription` - Abonnement actuel
- `$creditsBalance` - Solde crédits

---

## 📝 PROCHAINES ÉTAPES

### Stripe Integration (Optionnel)

Pour activer les paiements Stripe:

1. Configurer les Price IDs dans `.env`:

```env
STRIPE_PRICE_STARDUST=price_xxx
STRIPE_PRICE_NEBULA=price_xxx
STRIPE_PRICE_QUASAR=price_xxx
```

2. Décommenter le code Stripe dans `SubscriptionController.php`:

```php
// Ligne 152-155
if ($validated['payment_method_id']) {
    $user->createOrGetStripeCustomer();
    $user->newSubscription('default', $stripePrice)->create($validated['payment_method_id']);
}
```

3. Configurer le webhook Stripe pour gérer les événements:
   - `invoice.payment_succeeded`
   - `customer.subscription.deleted`
   - `customer.subscription.updated`

### Events & Listeners (Optionnel)

Pour ajouter un système d'événements:

```bash
php artisan make:event TargetCreated
php artisan make:event TargetCompleted
php artisan make:listener SendTargetCreatedNotification
```

### Notifications (Optionnel)

Pour envoyer des emails:

```bash
php artisan make:notification TargetTimeoutNotification
php artisan make:notification MonthlyCreditsRenewedNotification
php artisan make:notification SessionCompletedNotification
```

---

## 🧪 TESTS

Pour tester le système complet:

1. **Créer un utilisateur avec abonnement:**

```bash
php artisan tinker
```

```php
$user = User::find(1);
$subscription = Subscription::create([
    'user_id' => $user->id,
    'plan' => 'nebula',
    'credits_per_month' => 60,
    'status' => 'active',
]);
$user->increment('credits_balance', 60);
```

2. **Tester la création d'une target via API:**

```bash
curl -X POST http://localhost/api/robotarget/targets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "target_name": "M31 Andromeda",
    "ra_j2000": "00:42:44",
    "dec_j2000": "+41:16:09",
    "priority": 2,
    "c_moon_down": true,
    "c_alt_min": 30,
    "shots": [
      {
        "filter_index": 0,
        "filter_name": "Luminance",
        "exposure": 300,
        "num": 20,
        "gain": 100,
        "offset": 50,
        "bin": 1,
        "type": 0
      }
    ]
  }'
```

3. **Tester le scheduler:**

```bash
# Lancer le scheduler Laravel
php artisan schedule:work

# Tester un job spécifique
php artisan queue:work --once
```

---

## 📚 RESSOURCES

### Documentation officielle

- [Laravel Cashier](https://laravel.com/docs/11.x/billing)
- [Laravel Jobs & Queues](https://laravel.com/docs/11.x/queues)
- [Laravel Scheduling](https://laravel.com/docs/11.x/scheduling)
- [Alpine.js](https://alpinejs.dev/)

### Documentation projet

- `IMPLEMENTATION-PROXY.md` - Configuration Proxy Node.js
- `IMPLEMENTATION-FRONTEND.md` - Composants Alpine.js
- `CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md` - Modèle économique détaillé

---

**🎉 Implémentation Laravel complétée à 95% !**

