# 📋 TODO PRODUCTION - VERSION RÉVISÉE

**Date:** 14 Décembre 2025
**Statut après vérification:** ~90% Complet ⬆️ (+5%)
**Temps estimé:** 1 semaine

---

## ✅ CE QUI EST DÉJÀ FAIT (Vérifié)

### Base de données ✓
- ✅ Toutes les tables existent (users, jobs, cache, subscriptions, etc.)
- ✅ credit_transactions existe déjà
- ✅ Tables RoboTarget complètes (targets, shots, sessions)

### Backend ✓
- ✅ Système d'abonnements complet (Stardust, Nebula, Quasar)
- ✅ Système de crédits avec hold/capture/refund
- ✅ API RoboTarget fonctionnelle
- ✅ PricingEngine avec multiplicateurs
- ✅ Jobs planifiés (renouvellement, stale targets)
- ✅ Webhooks Stripe configurés
- ✅ Admin panel avec stats
- ✅ **NOUVEAU:** Système complet de récupération d'images (Proxy + Laravel API)

### Frontend ✓
- ✅ Interface création de targets (wizard 4 étapes)
- ✅ Monitoring temps réel
- ✅ Estimation prix en direct
- ✅ Mode Assisté (structure créée)
- ✅ Sidebar adaptée au système d'abonnements
- ✅ **NOUVEAU:** Galerie d'images complète avec visionneuse modale

---

## 🔧 CE QUI RESTE À FAIRE

### 1. **Emails de notification** (5 TODOs)
**Fichiers concernés:**

#### A. `SubscriptionController.php`
- **Ligne 431** : Email annulation abonnement
  ```php
  // TODO: Envoyer un email de notification
  Mail::to($user->email)->send(new SubscriptionCancelled($subscription));
  ```

- **Ligne 455** : Email renouvellement confirmé
  ```php
  // TODO: Envoyer un email de confirmation de renouvellement
  Mail::to($user->email)->send(new SubscriptionRenewed($subscription, $creditsAdded));
  ```

- **Ligne 471** : Email paiement échoué
  ```php
  // TODO: Envoyer un email de notification d'échec de paiement
  Mail::to($user->email)->send(new PaymentFailed($user, $invoice));
  ```

#### B. `CreditMonthlyAllowanceJob.php`
- **Ligne 103** : Email renouvellement crédits
  ```php
  // TODO: Email confirmation
  Mail::to($user->email)->send(new CreditsRenewed($user, $creditsAdded));
  ```

#### C. `CheckStaleTargetsJob.php`
- **Ligne 76** : Email target expirée
  ```php
  // TODO: Notifier l'utilisateur
  Mail::to($user->email)->send(new TargetExpired($target));
  ```

**Action requise:**
1. Créer les 5 classes Mailable
2. Créer les 5 templates Blade
3. Configurer SMTP dans .env
4. Tester l'envoi

**Temps estimé:** 1-2 jours

---

### 2. **Sécuriser le webhook Voyager**
**Fichier:** `Api/RoboTargetController.php` ligne 282

**Actuellement:**
```php
public function webhookSessionComplete(Request $request): JsonResponse
{
    // TODO: Ajouter validation webhook signature
```

**Solution:**
```php
public function webhookSessionComplete(Request $request): JsonResponse
{
    $secret = config('services.voyager.webhook_secret');
    $receivedSecret = $request->header('X-Webhook-Secret');

    if (!$secret || $receivedSecret !== $secret) {
        \Log::warning('Invalid webhook signature', [
            'ip' => $request->ip(),
        ]);
        return response()->json(['error' => 'Unauthorized'], 401);
    }

    // ... reste du code
}
```

**Temps estimé:** 30 minutes

---

### 3. **Finaliser le Mode Assisté**
**Statut actuel:**
- ✅ Interface créée (`step-welcome.blade.php`)
- ✅ Catalogue d'objets créé (`popular-targets.js`)
- ✅ Logique dans `RoboTargetManager.js`
- ⚠️ Assets buildés mais peut nécessiter test

**À vérifier:**
1. Le catalogue se charge correctement
2. La sélection d'un objet pré-remplit les coordonnées
3. Les filtres par difficulté fonctionnent
4. La transition vers l'étape 1 est fluide

**Action:** Tester le flow complet sur `/robotarget/create`

**Temps estimé:** 1-2 heures de test + corrections

---

### 4. **Configurer le Scheduler (Production)**

**Sur serveur de production, ajouter au crontab:**
```bash
* * * * * cd /path/to/stellar && php artisan schedule:run >> /dev/null 2>&1
```

**Jobs qui en dépendent:**
- Renouvellement crédits mensuels (`CreditMonthlyAllowanceJob`)
- Détection targets expirées (`CheckStaleTargetsJob`)

**En développement:**
```bash
php artisan schedule:work
```

**Temps estimé:** 15 minutes

---

### 5. **Stripe Webhooks (Production)**

**Dans le Stripe Dashboard:**
1. Developers → Webhooks
2. Add endpoint: `https://yourdomain.com/stripe/webhook`
3. Sélectionner événements:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `invoice.paid`
   - `invoice.payment_failed`
4. Copier Signing Secret
5. Ajouter au `.env`: `STRIPE_WEBHOOK_SECRET=whsec_xxx`

**Temps estimé:** 10 minutes

---

### 6. **Créer les plans Stripe**

Tu as déjà la commande prête :
```bash
php artisan stripe:setup-plans
```

Ou via l'interface admin :
`https://stellar.test/admin/subscriptions/plans` → Bouton "🚀 Créer les plans automatiquement"

**Vérifier ensuite:**
- Les 3 produits existent dans Stripe Dashboard
- Les Price IDs sont dans le `.env`
- La config est chargée (`php artisan config:clear`)

**Temps estimé:** 5 minutes

---

### 7. **Tests (Optionnel mais recommandé)**

**Tests critiques à écrire:**

```php
// tests/Feature/SubscriptionFlowTest.php
test('user can subscribe and receive credits', function () {
    // Test flow complet
});

test('webhook handles subscription cancellation', function () {
    // Test webhook
});

// tests/Feature/RoboTargetTest.php
test('creating target holds credits', function () {
    // Test hold
});

test('completed session charges credits', function () {
    // Test capture
});

test('failed session refunds credits', function () {
    // Test refund
});
```

**Temps estimé:** 1-2 jours (si tu veux faire des tests)

---

### 8. **Documentation utilisateur**

**Créer des guides simples:**
- Comment choisir un abonnement
- Comment créer sa première target
- Comment utiliser le Mode Assisté
- Comprendre les multiplicateurs de prix
- FAQ

**Temps estimé:** 1 jour

---

### 9. **Queue Workers (Production)**

**Installer Supervisor:**
```bash
sudo apt install supervisor
```

**Config:** `/etc/supervisor/conf.d/stellar-worker.conf`
```ini
[program:stellar-worker]
command=php /path/to/stellar/artisan queue:work --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
```

**Recharger:**
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start stellar-worker:*
```

**Temps estimé:** 30 minutes

---

### 10. **Optimisations Production**

```bash
# Cache
php artisan route:cache
php artisan config:cache
php artisan view:cache

# Composer
composer install --optimize-autoloader --no-dev

# Permissions
chmod -R 755 storage bootstrap/cache
```

**Temps estimé:** 15 minutes

---

## 📊 PRIORISATION

### 🔥 URGENT (Avant mise en production)
1. ✅ Sécuriser webhook Voyager (30min)
2. ✅ Configurer scheduler (15min)
3. ✅ Créer plans Stripe (5min)
4. ✅ Configurer Stripe webhooks production (10min)

**Total:** ~1 heure

### 🎯 IMPORTANT (Première semaine)
5. ✅ Implémenter les 5 emails (1-2 jours)
6. ✅ Tester Mode Assisté (1-2h)
7. ✅ Queue workers production (30min)
8. ✅ Optimisations (15min)

**Total:** 2-3 jours

### ⚡ AMÉLIORATION (Si temps disponible)
9. Tests automatisés (1-2 jours)
10. Documentation utilisateur (1 jour)

**Total:** 2-3 jours

---

## ✅ CHECKLIST PRÉ-DÉPLOIEMENT

### Configuration
- [ ] `.env` production configuré (DB, Stripe, Mail)
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Certificat SSL valide
- [ ] Stripe webhooks enregistrés
- [ ] SMTP configuré et testé

### Code
- [ ] Webhook Voyager sécurisé
- [ ] Emails implémentés (ou désactivés temporairement)
- [ ] Assets buildés (`npm run build`)
- [ ] Composer optimisé

### Serveur
- [ ] Cron job scheduler configuré
- [ ] Queue workers tournent (Supervisor)
- [ ] Permissions correctes (755/644)
- [ ] Logs rotatifs configurés
- [ ] Firewall configuré

### Stripe
- [ ] 3 produits créés (Stardust, Nebula, Quasar)
- [ ] Price IDs dans `.env`
- [ ] Webhooks testés
- [ ] Mode live activé (ou test mode OK)

### Tests
- [ ] Flow complet : inscription → abonnement → crédits ✓
- [ ] Créer une target → hold crédits ✓
- [ ] Webhook session complete → capture crédits ✓
- [ ] Mode Assisté fonctionne ✓
- [ ] Admin peut voir stats ✓

---

## 🎉 RÉSUMÉ RÉVISÉ

**Statut réel:** ~85% Complet

**Ce qui est solide:**
- ✅ Architecture complète
- ✅ Base de données OK
- ✅ Backend fonctionnel
- ✅ Frontend avancé
- ✅ Système de crédits robuste
- ✅ Abonnements Stripe intégrés

**Ce qui manque vraiment:**
1. Les 5 emails (2 jours)
2. Sécurisation webhook (30min)
3. Config serveur production (2h)
4. Tests (optionnel, 2 jours)

**Temps total avant production:**
- **Minimum viable:** 3-4 jours (sans tests)
- **Recommandé:** 1-2 semaines (avec tests + polish)

---

**Prochaine action recommandée:**

Si tu veux continuer maintenant, je te conseille de commencer par la plus rapide et critique :

```bash
# 1. Tester le Mode Assisté
# Va sur https://stellar.test/fr/robotarget/create
# Sélectionne "Mode Assisté" et teste

# 2. Créer les plans Stripe
php artisan stripe:setup-plans
```

Veux-tu que je t'aide sur un point spécifique ?
