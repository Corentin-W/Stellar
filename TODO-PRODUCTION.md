# 📋 TODO AVANT PRODUCTION - STELLAR

**Date:** 14 Décembre 2025
**Statut Global:** 71% Complet
**Temps estimé avant production:** 2-3 semaines

---

## 🚨 CRITIQUE - À FAIRE IMMÉDIATEMENT

### 1. Exécuter les Migrations Manquantes
**Statut:** ❌ BLOQUANT
**Impact:** L'app ne fonctionne pas sans ces tables

```bash
php artisan migrate
```

**Migrations manquantes:**
- `create_users_table` - Table utilisateurs
- `create_cache_table` - Cache système
- `create_jobs_table` - Queue de jobs
- `create_customer_columns` - Colonnes Cashier Stripe
- `create_subscription_items_table` - Items d'abonnement Stripe

**Pourquoi c'est critique:**
- Sans users table: impossible de se connecter
- Sans jobs table: les queues ne marchent pas (emails, jobs)
- Sans tables Cashier: les abonnements Stripe ne se sauvegardent pas

---

### 2. Créer la Table credit_transactions
**Statut:** ❌ CRITIQUE
**Impact:** Aucun historique des transactions de crédits

**Fichier:** `/database/migrations/XXXX_create_credit_transactions_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type'); // purchase, usage, hold, refund, admin_adjustment
            $table->integer('credits_amount'); // Peut être négatif
            $table->integer('balance_before');
            $table->integer('balance_after');
            $table->string('description')->nullable();
            $table->string('reference_type')->nullable(); // RoboTarget, Subscription, etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->foreignId('credit_package_id')->nullable();
            $table->foreignId('created_by')->nullable(); // Pour ajustements admin
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credit_transactions');
    }
};
```

**Ensuite:**
1. Créer le modèle `CreditTransaction`
2. Utiliser dans `User::addCredits()`, `deductCredits()`, etc.
3. Afficher dans l'historique utilisateur

---

### 3. Sécuriser les Webhooks Voyager
**Statut:** ❌ CRITIQUE (TODO ligne 282)
**Impact:** N'importe qui peut envoyer de fausses données

**Fichier:** `/app/Http/Controllers/Api/RoboTargetController.php`

```php
public function webhookSessionComplete(Request $request): JsonResponse
{
    // Valider le secret webhook
    $secret = config('services.voyager.webhook_secret');
    $receivedSecret = $request->header('X-Webhook-Secret');

    if (!$secret || $receivedSecret !== $secret) {
        \Log::warning('Invalid webhook signature', [
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // ... reste du code
}
```

**Configurer dans `.env`:**
```env
VOYAGER_WEBHOOK_SECRET=your-secure-random-string-here
```

**Dans le proxy Voyager:**
```javascript
// Ajouter le header lors de l'envoi du webhook
headers: {
    'X-Webhook-Secret': process.env.VOYAGER_WEBHOOK_SECRET
}
```

---

### 4. Configurer le Scheduler (Cron)
**Statut:** ❌ BLOQUANT
**Impact:** Jobs automatiques ne s'exécutent pas

**Sur le serveur de production, ajouter au crontab:**
```bash
* * * * * cd /path/to/stellar && php artisan schedule:run >> /dev/null 2>&1
```

**Pour tester en local:**
```bash
php artisan schedule:work
```

**Jobs qui dépendent du scheduler:**
- `CreditMonthlyAllowanceJob` - Renouvellement crédits mensuels
- `CheckStaleTargetsJob` - Détection targets expirées

---

### 5. Tester le Flow Complet Stripe
**Statut:** ⚠️ À TESTER

**Checklist:**
- [ ] Créer un abonnement → Vérifier crédits ajoutés
- [ ] Changer de plan → Vérifier changement de crédits
- [ ] Annuler abonnement → Vérifier status
- [ ] Paiement échoué → Vérifier webhook reçu
- [ ] Renouvellement mensuel → Vérifier crédits renouvelés

---

## 🔥 IMPORTANT - Phase 2

### 6. Implémenter les Emails de Notification
**Statut:** ❌ TODO dans 5 fichiers
**Impact:** Utilisateurs non informés des événements importants

**Emails à créer:**

#### A. Emails d'abonnement
**Fichier:** `SubscriptionController.php`

```php
// Ligne 431 - Abonnement annulé
Mail::to($user->email)->send(new SubscriptionCancelled($subscription));

// Ligne 455 - Renouvellement confirmé
Mail::to($user->email)->send(new SubscriptionRenewed($subscription, $creditsAdded));

// Ligne 471 - Paiement échoué
Mail::to($user->email)->send(new PaymentFailed($user, $invoice));
```

#### B. Emails de crédits
**Fichier:** `CreditMonthlyAllowanceJob.php` (ligne 103)

```php
// Crédits renouvelés
Mail::to($user->email)->send(new CreditsRenewed($user, $creditsAdded));
```

#### C. Emails RoboTarget
**Fichier:** `CheckStaleTargetsJob.php` (ligne 76)

```php
// Target expirée
Mail::to($user->email)->send(new TargetExpired($target));
```

**Templates à créer:**
- `resources/views/emails/subscription-cancelled.blade.php`
- `resources/views/emails/subscription-renewed.blade.php`
- `resources/views/emails/payment-failed.blade.php`
- `resources/views/emails/credits-renewed.blade.php`
- `resources/views/emails/target-expired.blade.php`

**Configuration .env:**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io  # Pour test
MAIL_PORT=2525
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@stellarloc.com
MAIL_FROM_NAME="${APP_NAME}"
```

---

### 7. Écrire les Tests Essentiels
**Statut:** ❌ 0% de couverture
**Impact:** Bugs non détectés, régressions possibles

**Tests minimum à écrire:**

#### A. Tests d'abonnement
**Fichier:** `tests/Feature/SubscriptionTest.php`

```php
test('user can subscribe to stardust plan', function () {
    $user = User::factory()->create();

    // Simuler checkout Stripe
    $response = $this->actingAs($user)
        ->post(route('subscriptions.subscribe'), [
            'plan' => 'stardust'
        ]);

    expect($user->subscription)->not->toBeNull();
    expect($user->credits_balance)->toBe(20);
});

test('user can switch from stardust to nebula', function () {
    // Test changement de plan
});

test('webhook handles subscription cancelled', function () {
    // Test webhook Stripe
});
```

#### B. Tests RoboTarget
**Fichier:** `tests/Feature/RoboTargetTest.php`

```php
test('creating target holds credits', function () {
    $user = User::factory()->create(['credits_balance' => 100]);

    $target = RoboTarget::factory()->create([
        'user_id' => $user->id,
        'credits_held' => 20
    ]);

    expect($user->fresh()->credits_balance)->toBe(80);
});

test('completed target charges held credits', function () {
    // Test capture de crédits
});

test('failed target refunds credits', function () {
    // Test remboursement
});
```

#### C. Tests de crédits
**Fichier:** `tests/Unit/CreditSystemTest.php`

```php
test('monthly renewal adds credits correctly', function () {
    // Test job de renouvellement
});

test('stale targets are detected and refunded', function () {
    // Test job de détection
});
```

**Lancer les tests:**
```bash
php artisan test
```

---

## ⚡ AMÉLIORATIONS - Phase 3

### 8. Finaliser le Mode Assisté
**Statut:** ⚠️ Partiellement fait
**Impact:** Débutants ne peuvent pas utiliser facilement

**Ce qui manque:**
- Catalogue d'objets populaires (M42, M31, etc.)
- Templates de configuration
- Aide contextuelle

**Voir:** `docs/MODE-ASSISTE-DEBUTANTS.md` pour le plan complet

---

### 9. Implémenter la Galerie d'Images
**Statut:** ❌ Manquant
**Impact:** Utilisateurs ne voient pas leurs résultats

**Fonctionnalités à ajouter:**
- Télécharger images FITS depuis Voyager
- Conversion FITS → JPG pour preview
- Galerie par target
- Téléchargement individuel/batch
- Métadonnées (HFD, Star Index, etc.)

---

### 10. Améliorer le Monitoring Temps Réel
**Statut:** ⚠️ Partiellement fait
**Impact:** Utilisateurs ne savent pas l'état actuel

**À améliorer:**
- WebSocket reconnexion automatique
- Barre de progression en direct
- Notifications push navigateur
- Aperçu image en cours

---

## 🎯 PRODUCTION READY - Phase 4

### 11. Configurer Stripe Webhooks
**Statut:** ❌ Manuel requis

**Dans le Stripe Dashboard:**
1. Aller dans Developers → Webhooks
2. Ajouter endpoint: `https://stellar.test/stripe/webhook`
3. Sélectionner événements:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.paid`
   - `invoice.payment_failed`
4. Copier le Signing Secret
5. Ajouter au `.env`: `STRIPE_WEBHOOK_SECRET=whsec_xxx`

---

### 12. Configurer Queue Workers (Production)
**Statut:** ❌ Requis pour production

**Installer Supervisor:**
```bash
sudo apt install supervisor
```

**Fichier:** `/etc/supervisor/conf.d/stellar-worker.conf`
```ini
[program:stellar-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/stellar/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/stellar/storage/logs/worker.log
stopwaitsecs=3600
```

**Recharger:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start stellar-worker:*
```

---

### 13. Optimiser pour Production
**Statut:** ❌ Non fait

**Checklist:**
```bash
# Cacher les routes
php artisan route:cache

# Cacher les config
php artisan config:cache

# Cacher les vues
php artisan view:cache

# Optimiser l'autoloader
composer install --optimize-autoloader --no-dev

# Activer le mode maintenance pendant le déploiement
php artisan down
# ... déploiement ...
php artisan up
```

---

### 14. Documentation Finale
**Statut:** ⚠️ Incomplète

**À créer:**
- [ ] README.md mis à jour
- [ ] Guide d'installation
- [ ] Guide de déploiement
- [ ] Variables d'environnement expliquées
- [ ] Troubleshooting commun
- [ ] API documentation (Swagger)

---

### 15. Monitoring & Sécurité
**Statut:** ❌ Non configuré

**À installer:**
- **Sentry** - Tracking d'erreurs
- **Laravel Telescope** - Debugging (dev uniquement)
- **Rate limiting** - Protection API
- **Logs centralisés** - CloudWatch/Papertrail
- **Backups automatiques** - Base de données

---

## 📊 RÉSUMÉ PAR PRIORITÉ

### 🚨 CRITIQUE (Semaine 1)
1. ✅ Exécuter migrations
2. ✅ Créer table credit_transactions
3. ✅ Sécuriser webhooks
4. ✅ Configurer scheduler
5. ✅ Tester flow Stripe end-to-end

**Temps estimé:** 2-3 jours

### 🔥 IMPORTANT (Semaine 2)
6. ✅ Implémenter tous les emails
7. ✅ Écrire tests essentiels (>50% coverage)
8. ✅ Finaliser Mode Assisté

**Temps estimé:** 5-7 jours

### ⚡ AMÉLIORATIONS (Semaine 3)
9. ✅ Galerie d'images
10. ✅ Monitoring temps réel amélioré
11. ✅ Configurer Stripe webhooks production
12. ✅ Queue workers (Supervisor)

**Temps estimé:** 5-7 jours

### 🎯 PRODUCTION READY (Semaine 4)
13. ✅ Optimisations production
14. ✅ Documentation complète
15. ✅ Monitoring & Sécurité

**Temps estimé:** 3-5 jours

---

## ⚠️ CHECKLIST PRÉ-DÉPLOIEMENT

Avant de mettre en production, vérifier:

- [ ] Toutes les migrations exécutées
- [ ] Table credit_transactions créée et utilisée
- [ ] Webhooks sécurisés (signature validation)
- [ ] Cron job configuré sur serveur
- [ ] Emails configurés et testés (5 types)
- [ ] Tests écrits et passent (>50% coverage)
- [ ] Mode Assisté fonctionnel
- [ ] Webhooks Stripe enregistrés
- [ ] Queue workers tournent (Supervisor)
- [ ] Route/config cache activés
- [ ] Monitoring d'erreurs (Sentry)
- [ ] Backups automatiques configurés
- [ ] README.md à jour
- [ ] Variables .env documentées
- [ ] Certificat SSL valide
- [ ] Firewall configuré
- [ ] Rate limiting activé
- [ ] Logs rotatifs configurés

---

## 🎓 COMMANDES UTILES

```bash
# Vérifier l'état des migrations
php artisan migrate:status

# Lister les jobs planifiés
php artisan schedule:list

# Vérifier la queue
php artisan queue:failed

# Retenter les jobs échoués
php artisan queue:retry all

# Lancer les tests
php artisan test

# Générer un coverage report
php artisan test --coverage

# Vider tous les caches
php artisan optimize:clear

# Voir les routes
php artisan route:list

# Vérifier la config Stripe
php artisan tinker
> config('cashier.secret')
> config('services.stripe')
```

---

**Dernière mise à jour:** 14 Décembre 2025
**Statut:** 71% → Cible: 100%
**ETA Production:** 2-3 semaines avec développement focalisé
