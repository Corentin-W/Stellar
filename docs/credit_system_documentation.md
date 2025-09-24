# Système de Crédits avec Stripe Cashier - Laravel 12

## Table des Matières

1. [Vue d'ensemble](#vue-densemble)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Structure de la base de données](#structure-de-la-base-de-données)
5. [Modèles](#modèles)
6. [Services](#services)
7. [Contrôleurs](#contrôleurs)
8. [Vues](#vues)
9. [Routes](#routes)
10. [Utilisation](#utilisation)
11. [Administration](#administration)
12. [Sécurité](#sécurité)
13. [Tests](#tests)
14. [Déploiement](#déploiement)
15. [Maintenance](#maintenance)

## Vue d'ensemble

Ce système de crédits permet aux utilisateurs d'acheter des packages de crédits via Stripe et de les utiliser pour accéder aux fonctionnalités de votre application. Il inclut :

- **Boutique de crédits** avec packages configurables
- **Système de promotions** avec codes promo
- **Paiements sécurisés** via Stripe Cashier
- **Historique détaillé** des transactions
- **Interface d'administration** complète
- **Webhooks Stripe** pour la confirmation automatique

### Fonctionnalités principales

- ✅ Achat de packages de crédits
- ✅ Codes promotionnels (pourcentage, montant fixe, crédits bonus)
- ✅ Gestion administrative des packages et promotions
- ✅ Historique complet des transactions
- ✅ Intégration Stripe Cashier avec Payment Intents
- ✅ Interface responsive avec Tailwind CSS
- ✅ Support multilingue (FR/EN)
- ✅ Système de webhooks sécurisé

## Installation

### 1. Installer Laravel Cashier

```bash
composer require laravel/cashier
```

### 2. Publier les migrations Cashier

```bash
php artisan vendor:publish --tag="cashier-migrations"
```

### 3. Créer les tables personnalisées

Exécutez ce SQL dans votre base de données :

```sql
-- Table pour les packages de crédits
CREATE TABLE `credit_packages` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `credits_amount` int(11) NOT NULL,
  `price_cents` int(11) NOT NULL,
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'EUR',
  `stripe_price_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `bonus_credits` int(11) NOT NULL DEFAULT '0',
  `discount_percentage` int(11) DEFAULT NULL,
  `sort_order` int(11) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_packages_is_active_index` (`is_active`),
  KEY `credit_packages_is_featured_index` (`is_featured`),
  KEY `credit_packages_sort_order_index` (`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les promotions
CREATE TABLE `promotions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `type` enum('percentage','fixed_amount','bonus_credits') COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` decimal(10,2) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `usage_limit` int(11) DEFAULT NULL,
  `usage_count` int(11) NOT NULL DEFAULT '0',
  `user_limit` int(11) DEFAULT NULL,
  `applicable_packages` json DEFAULT NULL,
  `minimum_purchase` int(11) DEFAULT NULL,
  `starts_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promotions_code_unique` (`code`),
  KEY `promotions_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pour les transactions de crédits
CREATE TABLE `credit_transactions` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `type` enum('purchase','usage','refund','bonus','admin_adjustment') COLLATE utf8mb4_unicode_ci NOT NULL,
  `credits_amount` int(11) NOT NULL,
  `balance_before` int(11) NOT NULL,
  `balance_after` int(11) NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `reference_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference_id` bigint(20) unsigned DEFAULT NULL,
  `stripe_payment_intent_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `credit_package_id` bigint(20) unsigned DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `metadata` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `credit_transactions_user_id_foreign` (`user_id`),
  KEY `credit_transactions_type_index` (`type`),
  CONSTRAINT `credit_transactions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table pivot pour l'utilisation des promotions
CREATE TABLE `promotion_user` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `promotion_id` bigint(20) unsigned NOT NULL,
  `user_id` bigint(20) unsigned NOT NULL,
  `usage_count` int(11) NOT NULL DEFAULT '1',
  `first_used_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `promotion_user_promotion_id_user_id_unique` (`promotion_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ajouter la colonne credits_balance à la table users
ALTER TABLE `users` ADD COLUMN `credits_balance` int(11) NOT NULL DEFAULT '0' AFTER `email_verified_at`;
ALTER TABLE `users` ADD INDEX `users_credits_balance_index` (`credits_balance`);
```

### 4. Modifier le modèle User

Dans `app/Models/User.php` :

```php
use Laravel\Cashier\Billable;
use App\Traits\HasCredits;

class User extends Authenticatable
{
    use Billable, HasCredits;
    
    protected $fillable = [
        // ... autres champs existants
        'credits_balance',
    ];
    
    protected $casts = [
        // ... autres casts existants
        'credits_balance' => 'integer',
    ];
}
```

## Configuration

### Variables d'environnement

Dans votre fichier `.env` :

```env
# Stripe Configuration
STRIPE_KEY=pk_tesXXXXX
STRIPE_SECRET=sk_tXXXXXX
STRIPE_WEBHOOK_SECRET=whsec_...
CASHIER_CURRENCY=eur

# Optionnel
CREDIT_BASE_COST_PER_UNIT=0.002
CREDIT_SYSTEM_ENABLED=true
```

### Configuration Cashier

Le fichier `config/cashier.php` est automatiquement configuré :

```php
return [
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
    'webhook' => [
        'secret' => env('STRIPE_WEBHOOK_SECRET'),
        'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
    ],
    'currency' => env('CASHIER_CURRENCY', 'eur'),
    'currency_locale' => env('CASHIER_CURRENCY_LOCALE', 'fr_FR'),
];
```

## Structure de la base de données

### Tables principales

1. **credit_packages** - Packages de crédits configurables
2. **promotions** - Codes promotionnels
3. **credit_transactions** - Historique de toutes les transactions
4. **promotion_user** - Utilisation des promotions par utilisateur
5. **users** - Colonne `credits_balance` ajoutée

### Relations

- Un utilisateur a plusieurs transactions (`User` → `CreditTransaction`)
- Une transaction appartient à un package (`CreditTransaction` → `CreditPackage`)
- Les promotions peuvent être utilisées par plusieurs utilisateurs (`Promotion` ↔ `User`)

## Modèles

### CreditPackage

Représente un package de crédits achetable :

```php
// Attributs principaux
$package->name              // Nom du package
$package->credits_amount    // Nombre de crédits de base
$package->bonus_credits     // Crédits bonus
$package->price_cents       // Prix en centimes
$package->is_active         // Package actif
$package->is_featured       // Package mis en avant

// Attributs calculés
$package->price_euros       // Prix en euros
$package->total_credits     // Total crédits (base + bonus)
$package->credit_value      // Valeur par crédit
```

### CreditTransaction

Enregistre toutes les transactions de crédits :

```php
// Types de transactions
'purchase'         // Achat de crédits
'usage'           // Utilisation de crédits
'refund'          // Remboursement
'bonus'           // Crédits bonus
'admin_adjustment' // Ajustement administrateur

// Attributs
$transaction->credits_amount    // Montant (+ ou -)
$transaction->balance_before    // Solde avant
$transaction->balance_after     // Solde après
$transaction->description       // Description
```

### Promotion

Gère les codes promotionnels :

```php
// Types de promotions
'percentage'     // Pourcentage de réduction
'fixed_amount'   // Montant fixe de réduction
'bonus_credits'  // Crédits bonus

// Méthodes principales
$promotion->canBeUsedBy($user)           // Vérifier si utilisable
$promotion->calculateDiscount($package)   // Calculer la réduction
$promotion->recordUsage($user)           // Enregistrer l'utilisation
```

## Services

### StripeService

Service principal pour l'intégration Stripe :

```php
// Créer un Payment Intent
$result = $stripeService->createPaymentIntent($user, $package, $promotion);

// Confirmer un paiement
$result = $stripeService->confirmPayment($paymentIntentId);

// Traiter les webhooks
$result = $stripeService->handleWebhook($payload, $signature);

// Synchroniser avec Stripe
$stripeService->syncPackagePrices();
```

### CreditService

Service pour la gestion des crédits :

```php
// Obtenir des recommandations
$recommendations = $creditService->getRecommendations($user);

// Estimer le coût d'une session
$cost = $creditService->estimateSessionCost($type, $duration, $complexity);
```

## Contrôleurs

### CreditController

Gère les interactions utilisateur :

- `shop()` - Affiche la boutique
- `validatePromotion()` - Valide un code promo
- `createPaymentIntent()` - Crée un Payment Intent
- `confirmPayment()` - Confirme le paiement
- `history()` - Historique des transactions

### CreditAdminController

Gestion administrative :

- `dashboard()` - Tableau de bord admin
- `packages()` - Gestion des packages
- `promotions()` - Gestion des promotions
- `users()` - Gestion des utilisateurs

## Vues

### Interface utilisateur

1. **credits/shop.blade.php** - Boutique de crédits
2. **credits/history.blade.php** - Historique des transactions
3. **credits/success.blade.php** - Page de succès

### Interface administrative

1. **admin/credits/packages/index.blade.php** - Liste des packages
2. **admin/credits/packages/create.blade.php** - Créer un package

## Routes

### Routes API

```php
// API pour les crédits
Route::prefix('api/credits')->middleware(['auth'])->group(function () {
    Route::post('/create-payment-intent', [CreditController::class, 'createPaymentIntent']);
    Route::post('/confirm-payment', [CreditController::class, 'confirmPayment']);
    Route::post('/validate-promotion', [CreditController::class, 'validatePromotion']);
    Route::get('/balance', [CreditController::class, 'balance']);
});

// Webhook Stripe (sans auth)
Route::post('/stripe/webhook', [CreditController::class, 'stripeWebhook']);
```

### Routes utilisateur

```php
Route::middleware('auth')->group(function () {
    Route::get('/credits/shop', [CreditController::class, 'shop'])->name('credits.shop');
    Route::get('/credits/history', [CreditController::class, 'history'])->name('credits.history');
    Route::get('/credits/success', function() {
        return view('credits.success');
    })->name('credits.success');
});
```

### Routes admin

```php
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::get('/credits', [CreditAdminController::class, 'dashboard'])->name('admin.credits.dashboard');
    Route::resource('/credits/packages', CreditAdminController::class);
});
```

## Utilisation

### Côté utilisateur

#### Ajouter des crédits

```php
$user = auth()->user();
$user->addCredits(100, 'Crédits bonus');
```

#### Déduire des crédits

```php
if ($user->hasEnoughCredits(10)) {
    $user->deductCredits(10, 'Utilisation d\'une fonctionnalité');
}
```

#### Vérifier le solde

```php
$balance = $user->credits_balance;
$hasEnough = $user->hasEnoughCredits(50);
```

#### Obtenir les statistiques

```php
$stats = $user->getCreditStats();
// Retourne : current_balance, total_purchased, total_used, etc.
```

### Côté administrateur

#### Créer un package

```php
CreditPackage::create([
    'name' => 'Starter Pack',
    'description' => 'Parfait pour commencer',
    'credits_amount' => 100,
    'price_cents' => 999,
    'bonus_credits' => 10,
    'is_active' => true
]);
```

#### Créer une promotion

```php
Promotion::create([
    'code' => 'WELCOME20',
    'name' => 'Bienvenue',
    'type' => 'percentage',
    'value' => 20,
    'is_active' => true,
    'expires_at' => now()->addDays(30)
]);
```

#### Ajuster les crédits d'un utilisateur

```php
$user->adminAdjustCredits(50, 'Compensation service', auth()->id());
```

## Administration

### Dashboard administrateur

Le dashboard fournit :

- Vue d'ensemble des ventes
- Statistiques des packages
- Utilisateurs les plus actifs
- Revenus et métriques

### Gestion des packages

- Créer/modifier/supprimer des packages
- Activer/désactiver des packages
- Définir l'ordre d'affichage
- Synchronisation avec Stripe

### Gestion des promotions

- Codes promotionnels personnalisés
- Limites d'utilisation (globale et par utilisateur)
- Dates de validité
- Types de réductions variés

## Sécurité

### Vérifications importantes

1. **Validation des webhooks Stripe** - Toujours vérifier la signature
2. **Contrôle d'accès** - Routes admin protégées
3. **Validation des montants** - Éviter les montants négatifs
4. **Protection CSRF** - Tous les formulaires protégés

### Bonnes pratiques

```php
// Toujours vérifier avant de déduire des crédits
if (!$user->hasEnoughCredits($cost)) {
    throw new InsufficientCreditsException();
}

// Utiliser des transactions pour les opérations critiques
DB::transaction(function () use ($user, $cost) {
    $user->deductCredits($cost, 'Action utilisateur');
    // Autres opérations...
});
```

## Tests

### Tests recommandés

1. **Test d'achat de package** avec cartes Stripe test
2. **Test de code promotionnel** validation et application
3. **Test de webhook** simulation d'événements Stripe
4. **Test de déduction** vérification des soldes
5. **Test d'interface admin** CRUD des packages

### Cartes de test Stripe

```
Succès : 4242424242424242
Échec : 4000000000000002
3D Secure : 4000002500003155
```

### Configuration de test

```php
// Dans vos tests
$this->actingAs($user)
     ->post('/api/credits/create-payment-intent', [
         'package_id' => $package->id
     ])
     ->assertJson(['success' => true]);
```

## Déploiement

### Configuration du webhook en production

1. Allez sur le [Dashboard Stripe](https://dashboard.stripe.com/webhooks)
2. Créez un endpoint : `https://votre-domaine.com/stripe/webhook`
3. Sélectionnez les événements :
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
4. Copiez le secret dans `STRIPE_WEBHOOK_SECRET`

### Optimisations production

```php
// Cache des packages actifs
Cache::remember('active_packages', 3600, function () {
    return CreditPackage::active()->ordered()->get();
});

// Queue pour les webhooks longs
Route::post('/stripe/webhook', [WebhookController::class, 'handle'])
     ->middleware('throttle:stripe-webhooks');
```

## Maintenance

### Surveillance

Surveillez ces métriques :

- Taux de succès des paiements
- Temps de réponse des webhooks
- Erreurs dans les logs Laravel
- Utilisation des codes promo

### Logs importants

```bash
# Erreurs Laravel
tail -f storage/logs/laravel.log

# Webhooks Stripe (si configuré)
tail -f storage/logs/stripe.log
```

### Commandes utiles

```bash
# Synchroniser les prix Stripe
php artisan tinker
>>> app(App\Services\StripeService::class)->syncPackagePrices()

# Nettoyer les transactions anciennes (optionnel)
>>> CreditTransaction::where('created_at', '<', now()->subYear())->delete()

# Vérifier l'intégrité des soldes
>>> User::whereColumn('credits_balance', '!=', DB::raw('(
        SELECT COALESCE(SUM(credits_amount), 0) 
        FROM credit_transactions 
        WHERE user_id = users.id
    )'))->get()
```

### Backup recommandé

Sauvegardez régulièrement :
- Table `users` (colonne `credits_balance`)
- Table `credit_transactions` (historique complet)
- Table `credit_packages`
- Configuration Stripe

---

## Support et Questions

Pour toute question ou problème :

1. Vérifiez les logs Laravel et Stripe
2. Consultez la documentation Stripe Cashier
3. Testez avec les cartes de test Stripe
4. Vérifiez la configuration des webhooks

Cette documentation couvre l'ensemble du système de crédits. Le système est extensible et peut être adapté selon vos besoins spécifiques.
