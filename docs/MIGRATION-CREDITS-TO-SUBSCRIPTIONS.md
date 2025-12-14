# 🔄 Migration - Système de Crédits vers Abonnements

## 🎯 Changement de modèle

### ❌ Ancien système (RETIRÉ)
- Achat ponctuel de packages de crédits (10, 50, 100 crédits, etc.)
- Gestion admin des packages via `/admin/credits`
- Promotions et codes promo
- Prix en centimes stockés en DB

### ✅ Nouveau système (ACTUEL)
- **Abonnements mensuels récurrents** (Stardust, Nebula, Quasar)
- Renouvellement automatique des crédits le 1er du mois
- Gestion admin via `/admin/subscriptions`
- Prix définis dans le modèle `Subscription` (constantes)

---

## 🗑️ Ce qui a été retiré

### Routes supprimées

Toutes les routes `/admin/credits/*` ont été retirées :

```php
// ❌ SUPPRIMÉ
Route::get('/admin/credits', ...)                    // Dashboard crédits
Route::get('/admin/credits/packages', ...)           // Gestion packages
Route::get('/admin/credits/packages/create', ...)    // Créer package
Route::get('/admin/credits/promotions', ...)         // Gestion promotions
Route::get('/admin/credits/users', ...)              // Liste utilisateurs
Route::get('/admin/credits/reports', ...)            // Rapports
Route::get('/admin/credits/transactions', ...)       // Transactions
```

### Controller conservé (mais non utilisé)

**`app/Http/Controllers/Admin/CreditAdminController.php`**
- ⚠️ Le fichier existe toujours mais n'est plus utilisé
- Aucune route ne pointe vers lui
- Peut être supprimé ou archivé

### Vues conservées (mais non utilisées)

**`resources/views/admin/credits/*`**
- `dashboard.blade.php`
- `packages/index.blade.php`
- `packages/create.blade.php`
- `packages/edit.blade.php`
- `promotions/*`
- `users/*`

⚠️ Ces fichiers existent toujours mais ne sont plus accessibles.

---

## 📊 Tables de base de données

### Tables toujours utilisées

✅ **`users`** : Champ `credits_balance` toujours utilisé
✅ **`subscriptions`** : Nouvelle table pour les abonnements
✅ **`credit_transactions`** : Toujours utilisée pour l'historique

### Tables obsolètes (peuvent être supprimées)

❌ **`credit_packages`** : Anciens packages de crédits
❌ **`promotions`** : Codes promo (plus utilisés)

**Migration pour supprimer** (OPTIONNEL) :
```bash
php artisan make:migration drop_credit_packages_and_promotions_tables
```

```php
public function up()
{
    Schema::dropIfExists('credit_packages');
    Schema::dropIfExists('promotions');
}

public function down()
{
    // Recréer les tables si rollback nécessaire
}
```

⚠️ **Attention** : Ne supprime ces tables que si aucune donnée historique n'est nécessaire.

---

## 🔄 Nouvelle logique de crédits

### Ancien système
1. Utilisateur achète un package (ex: 100 crédits pour 50€)
2. Crédits ajoutés immédiatement
3. Pas de renouvellement automatique
4. Crédits conservés indéfiniment

### Nouveau système
1. Utilisateur s'abonne à un plan mensuel
2. Crédits ajoutés lors de la création d'abonnement
3. **Renouvellement automatique le 1er du mois** (via webhook `invoice.paid`)
4. **Crédits non utilisés ne sont PAS reportés** (reset au montant mensuel)

**Code du renouvellement** (dans `SubscriptionController::handleInvoicePaid`) :
```php
$creditsPerMonth = $user->subscription->credits_per_month;
$user->update(['credits_balance' => $creditsPerMonth]);
```

---

## 🔧 Actions si tu veux restaurer l'ancien système

### Étape 1 : Restaurer les routes

Dans `routes/web.php`, rétablir les routes admin crédits :
```php
Route::get('/admin/credits', [CreditAdminController::class, 'dashboard'])
    ->name('admin.credits.dashboard');
// ... etc
```

### Étape 2 : Vérifier les modèles

S'assurer que les modèles existent :
- `app/Models/CreditPackage.php`
- `app/Models/Promotion.php`

### Étape 3 : Vérifier les tables

S'assurer que les tables existent dans la DB :
```sql
SHOW TABLES LIKE 'credit_packages';
SHOW TABLES LIKE 'promotions';
```

---

## 📈 Comparaison des deux systèmes

| Aspect | Ancien (Packages) | Nouveau (Abonnements) |
|--------|-------------------|-----------------------|
| **Modèle** | Achat ponctuel | Abonnement récurrent |
| **Paiement** | Une fois | Mensuel automatique |
| **Renouvellement** | Manuel (utilisateur achète) | Automatique (Stripe) |
| **Crédits non utilisés** | Conservés | Reset chaque mois |
| **Prix** | Stockés en DB | Définis dans le code |
| **Promotions** | Codes promo | Périodes d'essai (7j) |
| **Admin** | `/admin/credits` | `/admin/subscriptions` |
| **Stripe** | Checkout ponctuel | Subscriptions API |
| **Webhooks** | `checkout.session.completed` | `invoice.paid`, `subscription.*` |

---

## 🎓 Avantages du nouveau système

### Pour l'utilisateur
✅ **Simplicité** : Un paiement mensuel, pas besoin de racheter
✅ **Prévisibilité** : Toujours le même quota chaque mois
✅ **Essai gratuit** : 7 jours pour tester sans engagement
✅ **Flexibilité** : Change de plan à tout moment

### Pour toi (admin/business)
✅ **Revenu récurrent** : MRR prévisible
✅ **Rétention** : Les utilisateurs restent abonnés
✅ **Moins de friction** : Pas besoin de relancer pour racheter
✅ **Analytics** : MRR, churn rate, LTV calculables
✅ **Automatisation** : Stripe gère tout (renouvellement, échecs, annulations)

---

## 🚨 Points d'attention

### Utilisateurs avec crédits achetés avant migration

Si tu avais des utilisateurs avec des crédits achetés via l'ancien système :

**Option 1 : Leur conserver les crédits**
- Ne rien faire, ils gardent leur solde actuel
- Ils peuvent s'abonner pour avoir des crédits mensuels en plus

**Option 2 : Migrer vers abonnement**
- Calculer l'équivalent en abonnement
- Les abonner manuellement
- Ajuster les crédits via `/admin/subscriptions/{id}`

**Option 3 : Remboursement**
- Calculer la valeur restante
- Rembourser via Stripe
- Proposer un abonnement à la place

### Transactions historiques

Les `credit_transactions` avec `type = 'purchase'` pointent vers `credit_packages`.

Si tu supprimes la table `credit_packages`, ces transactions perdront la référence.

**Solution** : Conserver la table `credit_packages` pour l'historique, même si plus utilisée.

---

## ✅ Checklist migration complète

Si tu veux complètement supprimer l'ancien système :

- [ ] Vérifier qu'aucun utilisateur n'a de crédits de l'ancien système
- [ ] Archiver les vues admin crédits : `mv resources/views/admin/credits resources/views/admin/credits.old`
- [ ] Supprimer ou archiver `CreditAdminController.php`
- [ ] (Optionnel) Supprimer les tables `credit_packages` et `promotions`
- [ ] (Optionnel) Nettoyer les migrations liées aux packages
- [ ] Mettre à jour la navigation admin pour retirer les liens vers `/admin/credits`
- [ ] Tester que `/admin/subscriptions` fonctionne correctement
- [ ] Vérifier que les webhooks Stripe sont bien configurés

---

## 🔗 Documentation associée

- **`ADMIN-SUBSCRIPTIONS-GUIDE.md`** : Guide complet du nouveau système admin
- **`STRIPE-INTEGRATION-COMPLETE.md`** : Intégration Stripe abonnements
- **`GUIDE-SYSTEME-CREDITS.md`** : Guide utilisateur du système de crédits

---

**Date de migration** : 13 décembre 2025
**Ancien système** : Packages de crédits ponctuels
**Nouveau système** : Abonnements mensuels récurrents (Stardust, Nebula, Quasar)

**✅ La migration est terminée. Le système d'abonnements est maintenant actif.**
