# 🚀 Configuration Automatique des Plans Stripe

Ce guide explique comment configurer automatiquement les plans d'abonnement dans Stripe sans avoir à copier/coller manuellement les Price IDs.

## 📋 Prérequis

1. **Compte Stripe configuré** avec les clés API dans `.env` :
```env
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
```

2. **Laravel Cashier** installé (déjà fait dans ce projet)

## 🎯 Méthode 1 : Via l'Interface Admin (Recommandée)

### Étape 1 : Accéder à la page de gestion des plans

1. Connectez-vous en tant qu'admin
2. Allez sur : `https://stellar.test/admin/subscriptions/plans`

### Étape 2 : Créer les plans automatiquement

1. Cliquez sur le bouton **"🚀 Créer les plans automatiquement"**
2. Confirmez la création
3. Attendez quelques secondes (la commande s'exécute en arrière-plan)

### Étape 3 : Vérification

✅ Si tout s'est bien passé :
- Vous verrez un message de succès
- Les 3 Price IDs seront automatiquement remplis dans les formulaires
- Votre fichier `.env` sera mis à jour avec les nouvelles variables

🔍 Vous pouvez vérifier dans votre [Stripe Dashboard](https://dashboard.stripe.com/products) que les 3 produits ont été créés :
- 🌟 **Stardust** - 29€/mois - 20 crédits
- 🌌 **Nebula** - 59€/mois - 60 crédits
- ⚡ **Quasar** - 119€/mois - 150 crédits

## 💻 Méthode 2 : Via la Ligne de Commande

Si vous préférez utiliser la ligne de commande :

```bash
# Créer les plans
php artisan stripe:setup-plans

# Forcer la recréation (si les plans existent déjà)
php artisan stripe:setup-plans --force
```

### Sortie attendue :

```
🚀 Starting Stripe plans setup...

📦 Processing plan: 🌟 Stardust
   + Creating new product...
   ✓ Product: prod_xxxxxxxxxxxxx
   + Creating new price...
   ✓ Price: price_xxxxxxxxxxxxx

📦 Processing plan: 🌌 Nebula
   + Creating new product...
   ✓ Product: prod_xxxxxxxxxxxxx
   + Creating new price...
   ✓ Price: price_xxxxxxxxxxxxx

📦 Processing plan: ⚡ Quasar
   + Creating new product...
   ✓ Product: prod_xxxxxxxxxxxxx
   ✓ Price: price_xxxxxxxxxxxxx

💾 Updating .env file with Price IDs...
✅ .env file updated successfully!

📊 Summary of created plans:
+-------------------+----------+--------+---------+-------------------------+
| Plan              | Name     | Price  | Credits | Price ID                |
+-------------------+----------+--------+---------+-------------------------+
| 🌟 Stardust       | Stardust | 29.00€ | 20      | price_xxxxxxxxxxxxx     |
| 🌌 Nebula         | Nebula   | 59.00€ | 60      | price_xxxxxxxxxxxxx     |
| ⚡ Quasar         | Quasar   | 119.00€| 150     | price_xxxxxxxxxxxxx     |
+-------------------+----------+--------+---------+-------------------------+

🎉 Stripe plans setup completed!
```

## 🔄 Que fait la commande ?

La commande `stripe:setup-plans` effectue automatiquement :

1. **Création des produits Stripe** avec :
   - Nom du plan (Stardust, Nebula, Quasar)
   - Description détaillée
   - Métadonnées (plan, crédits/mois)

2. **Création des prices récurrents** avec :
   - Montant en centimes (2900, 5900, 11900)
   - Devise (EUR)
   - Récurrence mensuelle
   - Métadonnées

3. **Mise à jour automatique du `.env`** :
   ```env
   STRIPE_PRICE_STARDUST=price_xxxxxxxxxxxxx
   STRIPE_PRICE_NEBULA=price_xxxxxxxxxxxxx
   STRIPE_PRICE_QUASAR=price_xxxxxxxxxxxxx
   ```

4. **Clear du cache de configuration** pour prendre en compte les nouveaux Price IDs

## 🔧 Gestion des Plans Existants

### Si les plans existent déjà

Par défaut, la commande **réutilise les plans existants** au lieu d'en créer de nouveaux.

Pour forcer la recréation :
```bash
php artisan stripe:setup-plans --force
```

### Mise à jour manuelle d'un Price ID

Si vous avez créé un plan manuellement et souhaitez mettre à jour un seul Price ID :

1. Allez sur `/admin/subscriptions/plans`
2. Collez le Price ID dans le champ correspondant
3. Cliquez sur "💾 Sauvegarder le Price ID"

## ⚠️ Dépannage

### Erreur : "Stripe secret key not configured"

Vérifiez que votre `.env` contient :
```env
STRIPE_SECRET=sk_test_xxxxxxxxxxxxx  # ou sk_live_ en production
```

### Erreur : "No such price"

Les Price IDs ont changé. Relancez la commande :
```bash
php artisan stripe:setup-plans --force
```

### Les Price IDs ne s'affichent pas

Videz le cache :
```bash
php artisan config:clear
```

## 🌐 Mode Production

Avant de passer en production :

1. Changez les clés Stripe dans `.env` pour utiliser les clés live :
   ```env
   STRIPE_KEY=pk_live_xxxxx
   STRIPE_SECRET=sk_live_xxxxx
   ```

2. Relancez la commande pour créer les plans en live :
   ```bash
   php artisan stripe:setup-plans
   ```

3. Les Price IDs seront automatiquement mis à jour dans le `.env`

## 📚 Références

- [Documentation Stripe Products](https://stripe.com/docs/api/products)
- [Documentation Stripe Prices](https://stripe.com/docs/api/prices)
- [Laravel Cashier](https://laravel.com/docs/billing)

---

✨ **Astuce** : Une fois les plans créés, vous n'avez plus besoin de relancer cette commande, sauf si vous modifiez les prix ou ajoutez de nouveaux plans.
