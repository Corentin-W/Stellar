# 💳 Configuration Stripe - Guide Complet

## 🎯 Objectif

Configurer Stripe pour gérer les abonnements mensuels RoboTarget (Stardust, Nebula, Quasar).

---

## ✅ Pré-requis

- ✅ Laravel Cashier installé (déjà fait)
- ✅ Clés API Stripe en mode test configurées dans `.env`
- ⏳ Créer les produits et prix dans Stripe Dashboard
- ⏳ Configurer le webhook Stripe

---

## 📋 Étape 1 : Créer les produits dans Stripe Dashboard

### 1.1 Se connecter à Stripe

1. Aller sur https://dashboard.stripe.com
2. Passer en **mode test** (toggle en haut à droite)

### 1.2 Créer les 3 produits d'abonnement

**Produit 1 : Stardust**
1. Aller dans **Produits** > **+ Ajouter un produit**
2. Remplir :
   - **Nom** : `RoboTarget Stardust`
   - **Description** : `Abonnement mensuel RoboTarget - Plan Stardust (20 crédits/mois)`
   - **Type de facturation** : `Récurrent`
   - **Prix** : `29.00 EUR`
   - **Fréquence** : `Mensuelle`
   - **ID du prix** : Stripe génère automatiquement (exemple : `price_1AbCdEfGhIjKlMnO`)
3. **Enregistrer**
4. **COPIER LE PRICE ID** généré (commence par `price_...`)

**Produit 2 : Nebula**
1. Aller dans **Produits** > **+ Ajouter un produit**
2. Remplir :
   - **Nom** : `RoboTarget Nebula`
   - **Description** : `Abonnement mensuel RoboTarget - Plan Nebula (60 crédits/mois)`
   - **Type de facturation** : `Récurrent`
   - **Prix** : `59.00 EUR`
   - **Fréquence** : `Mensuelle`
3. **Enregistrer**
4. **COPIER LE PRICE ID**

**Produit 3 : Quasar**
1. Aller dans **Produits** > **+ Ajouter un produit**
2. Remplir :
   - **Nom** : `RoboTarget Quasar`
   - **Description** : `Abonnement mensuel RoboTarget - Plan Quasar (150 crédits/mois)`
   - **Type de facturation** : `Récurrent`
   - **Prix** : `119.00 EUR`
   - **Fréquence** : `Mensuelle`
3. **Enregistrer**
4. **COPIER LE PRICE ID**

---

## 📋 Étape 2 : Configurer les Price IDs dans `.env`

Ajouter ces lignes dans votre fichier `.env` :

```bash
# Stripe Price IDs (mode test)
STRIPE_PRICE_STARDUST=price_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PRICE_NEBULA=price_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PRICE_QUASAR=price_xxxxxxxxxxxxxxxxxxxxx
```

**IMPORTANT** : Remplacer `price_xxxxxxxxxxxxxxxxxxxxx` par les vrais Price IDs copiés depuis Stripe.

---

## 📋 Étape 3 : Configurer le webhook Stripe

### 3.1 Créer l'endpoint webhook dans Stripe

1. Aller dans **Développeurs** > **Webhooks** > **+ Ajouter un endpoint**
2. Remplir :
   - **URL de l'endpoint** : `https://votre-domaine.com/api/stripe/webhook`
   - **Description** : `Webhook RoboTarget Subscriptions`
3. **Sélectionner les événements à écouter** :
   - ✅ `customer.subscription.created` - Nouvel abonnement créé
   - ✅ `customer.subscription.updated` - Abonnement modifié (changement de plan)
   - ✅ `customer.subscription.deleted` - Abonnement annulé
   - ✅ `invoice.paid` - Facture payée (renouvellement mensuel)
   - ✅ `invoice.payment_failed` - Paiement échoué
   - ✅ `checkout.session.completed` - Session de paiement terminée

4. **Ajouter l'endpoint**
5. **COPIER LE SECRET DE SIGNATURE** (commence par `whsec_...`)

### 3.2 Ajouter le secret webhook dans `.env`

```bash
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx
```

---

## 📋 Étape 4 : Implémenter la gestion des webhooks

Le webhook handler doit gérer les événements suivants :

### 4.1 Créer le controller webhook

Ajouter dans `SubscriptionController.php` :

```php
/**
 * Webhook Stripe pour gérer les événements d'abonnement
 */
public function webhook(Request $request)
{
    $endpoint_secret = config('cashier.webhook.secret');
    $payload = $request->getContent();
    $sig_header = $request->header('Stripe-Signature');

    try {
        $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
    } catch (\UnexpectedValueException $e) {
        // Payload invalide
        return response()->json(['error' => 'Invalid payload'], 400);
    } catch (\Stripe\Exception\SignatureVerificationException $e) {
        // Signature invalide
        return response()->json(['error' => 'Invalid signature'], 400);
    }

    // Gérer l'événement
    switch ($event->type) {
        case 'customer.subscription.created':
        case 'customer.subscription.updated':
            $this->handleSubscriptionUpdate($event->data->object);
            break;

        case 'customer.subscription.deleted':
            $this->handleSubscriptionCancelled($event->data->object);
            break;

        case 'invoice.paid':
            $this->handleInvoicePaid($event->data->object);
            break;

        case 'invoice.payment_failed':
            $this->handlePaymentFailed($event->data->object);
            break;
    }

    return response()->json(['success' => true]);
}

/**
 * Gérer la mise à jour d'un abonnement
 */
protected function handleSubscriptionUpdate($stripeSubscription)
{
    $user = \App\Models\User::where('stripe_id', $stripeSubscription->customer)->first();

    if (!$user) {
        \Log::warning('User not found for Stripe customer: ' . $stripeSubscription->customer);
        return;
    }

    // Mettre à jour le statut de l'abonnement
    if ($user->subscription) {
        $user->subscription->update([
            'stripe_status' => $stripeSubscription->status,
        ]);
    }
}

/**
 * Gérer l'annulation d'un abonnement
 */
protected function handleSubscriptionCancelled($stripeSubscription)
{
    $user = \App\Models\User::where('stripe_id', $stripeSubscription->customer)->first();

    if (!$user || !$user->subscription) {
        return;
    }

    $user->subscription->update([
        'status' => 'cancelled',
        'stripe_status' => 'canceled',
        'ends_at' => now(),
    ]);

    // Optionnel : envoyer un email de notification
}

/**
 * Gérer le paiement d'une facture (renouvellement mensuel)
 */
protected function handleInvoicePaid($invoice)
{
    $user = \App\Models\User::where('stripe_id', $invoice->customer)->first();

    if (!$user || !$user->subscription) {
        return;
    }

    // Renouveler les crédits mensuels
    $creditsPerMonth = $user->subscription->credits_per_month;

    // Remettre le solde au montant mensuel (pas d'ajout, juste reset)
    $user->update([
        'credits_balance' => $creditsPerMonth,
    ]);

    \Log::info("Credits renewed for user {$user->id}: {$creditsPerMonth} credits");
}

/**
 * Gérer l'échec de paiement
 */
protected function handlePaymentFailed($invoice)
{
    $user = \App\Models\User::where('stripe_id', $invoice->customer)->first();

    if (!$user) {
        return;
    }

    // Envoyer un email de notification
    // Optionnel : marquer l'abonnement comme "past_due"

    \Log::warning("Payment failed for user {$user->id}, invoice {$invoice->id}");
}
```

### 4.2 Ajouter la route webhook

Dans `routes/api.php` (ou `web.php` sans middleware auth) :

```php
// Webhook Stripe (SANS middleware auth)
Route::post('/stripe/webhook', [\App\Http\Controllers\SubscriptionController::class, 'webhook'])
    ->name('stripe.subscription.webhook');
```

**IMPORTANT** : Cette route doit être **SANS** middleware `auth` car Stripe l'appelle directement.

---

## 📋 Étape 5 : Tester le flux complet

### 5.1 Test en mode test Stripe

1. **Démarrer le serveur** : `php artisan serve`
2. **Se connecter** avec un compte utilisateur
3. **Aller sur** `/fr/subscriptions/choose`
4. **Cliquer** sur "S'abonner" pour un plan
5. **Vérifier** la redirection vers Stripe Checkout
6. **Utiliser une carte de test Stripe** :
   - Numéro : `4242 4242 4242 4242`
   - Date : N'importe quelle date future
   - CVC : N'importe quel 3 chiffres
7. **Valider le paiement**
8. **Vérifier** :
   - Redirection vers `/fr/subscriptions/success`
   - Message de succès affiché
   - Crédits ajoutés au compte (`credits_balance`)
   - Abonnement créé dans la table `subscriptions`

### 5.2 Tester le webhook localement avec Stripe CLI

```bash
# Installer Stripe CLI (si pas déjà fait)
brew install stripe/stripe-cli/stripe

# Se connecter
stripe login

# Rediriger les webhooks vers votre serveur local
stripe listen --forward-to localhost:8000/api/stripe/webhook

# Dans un autre terminal, déclencher des événements de test
stripe trigger customer.subscription.created
stripe trigger invoice.paid
```

### 5.3 Vérifier dans Stripe Dashboard

1. Aller dans **Paiements** > **Abonnements**
2. Vérifier que l'abonnement test apparaît
3. Aller dans **Développeurs** > **Webhooks** > **Votre endpoint**
4. Consulter les **événements reçus** et leur statut (200 OK)

---

## 📋 Étape 6 : Passer en production

### 6.1 Créer les produits en mode LIVE

1. **Basculer en mode Live** dans Stripe Dashboard
2. **Répéter l'étape 1** (créer les 3 produits)
3. **Copier les nouveaux Price IDs** (différents du mode test)

### 6.2 Configurer `.env` pour la production

```bash
# Stripe Keys (mode LIVE)
STRIPE_KEY=pk_live_xxxxxxxxxxxxxxxxxxxxx
STRIPE_SECRET=sk_live_xxxxxxxxxxxxxxxxxxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxxxxxxxxxxxxxxxxxx

# Stripe Price IDs (mode LIVE)
STRIPE_PRICE_STARDUST=price_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PRICE_NEBULA=price_xxxxxxxxxxxxxxxxxxxxx
STRIPE_PRICE_QUASAR=price_xxxxxxxxxxxxxxxxxxxxx
```

### 6.3 Configurer le webhook en production

1. Créer un nouveau endpoint webhook pointant vers `https://astral-stellar.com/api/stripe/webhook`
2. Activer les mêmes événements qu'en test
3. Copier le nouveau secret webhook

---

## 🧪 Scénarios de test

### Test 1 : Nouvel abonnement
- ✅ Utilisateur sans abonnement clique sur "S'abonner"
- ✅ Redirection vers Stripe Checkout
- ✅ Paiement réussi
- ✅ Redirection vers page de succès
- ✅ Crédits ajoutés
- ✅ Abonnement créé en DB

### Test 2 : Changement de plan (upgrade)
- ✅ Utilisateur Stardust passe à Nebula
- ✅ Stripe met à jour l'abonnement (prorata automatique)
- ✅ Crédits ajustés (différence)
- ✅ Plan mis à jour en DB

### Test 3 : Changement de plan (downgrade)
- ✅ Utilisateur Quasar passe à Nebula
- ✅ Stripe met à jour l'abonnement
- ✅ Crédits réduits (différence négative)
- ✅ Plan mis à jour en DB

### Test 4 : Renouvellement mensuel automatique
- ✅ Le 1er du mois, Stripe facture automatiquement
- ✅ Webhook `invoice.paid` reçu
- ✅ Crédits reset au montant mensuel
- ✅ Email de confirmation (optionnel)

### Test 5 : Échec de paiement
- ✅ Carte expirée ou refusée
- ✅ Webhook `invoice.payment_failed` reçu
- ✅ Email d'alerte envoyé
- ✅ Abonnement marqué `past_due`
- ✅ Stripe réessaie automatiquement (selon config)

### Test 6 : Annulation d'abonnement
- ✅ Utilisateur annule depuis Stripe ou l'app
- ✅ Webhook `customer.subscription.deleted` reçu
- ✅ Statut mis à jour en DB
- ✅ Accès maintenu jusqu'à fin de période payée
- ✅ Plus de renouvellement ensuite

---

## 🔒 Sécurité

### Validation des webhooks
- ✅ **TOUJOURS** vérifier la signature Stripe (`Stripe-Signature` header)
- ✅ Utiliser `\Stripe\Webhook::constructEvent()` pour valider
- ✅ Rejeter les requêtes non signées

### Protection contre la fraude
- ✅ Activer **Stripe Radar** (inclus gratuitement)
- ✅ Configurer des règles de prévention de fraude
- ✅ Surveiller les tentatives de paiement échouées

### Gestion des erreurs
- ✅ Logger tous les événements webhook
- ✅ Envoyer des alertes en cas d'échec répété
- ✅ Prévoir un système de retry manuel

---

## 📊 Monitoring

### Métriques à surveiller
1. **Taux de conversion** : Visiteurs → Abonnés
2. **Churn rate** : Taux d'annulation mensuel
3. **MRR (Monthly Recurring Revenue)** : Revenu mensuel récurrent
4. **Échecs de paiement** : Taux d'échec et raisons
5. **Évolution des plans** : Upgrades vs Downgrades

### Outils Stripe
- **Stripe Dashboard** : Vue d'ensemble temps réel
- **Stripe Sigma** : Requêtes SQL sur vos données (payant)
- **Webhooks logs** : Historique des événements webhook

---

## ❓ FAQ

### Q: Que se passe-t-il si un utilisateur a déjà un abonnement ?
**R:** La méthode `switchPlan()` gère le changement de plan via Cashier `swap()`, qui calcule automatiquement le prorata.

### Q: Les crédits non utilisés sont-ils reportés ?
**R:** Non, les crédits sont remis au montant mensuel à chaque renouvellement. C'est géré dans `handleInvoicePaid()`.

### Q: Comment gérer les remboursements ?
**R:** Manuellement depuis Stripe Dashboard ou via l'API Stripe. Laravel Cashier ne gère pas automatiquement les remboursements.

### Q: Peut-on offrir des essais gratuits ?
**R:** Oui ! C'est déjà configuré : `.trialDays(7)` dans `createCheckoutSession()`.

### Q: Comment annuler un abonnement ?
**R:** Via Cashier : `$user->subscription('default')->cancel()` ou `cancelNow()` pour annulation immédiate.

### Q: Les webhooks sont-ils fiables ?
**R:** Oui, mais il faut gérer l'**idempotence** (ne pas traiter 2 fois le même événement). Stripe envoie parfois plusieurs fois le même webhook.

---

## 🎓 Ressources utiles

- **Laravel Cashier Docs** : https://laravel.com/docs/11.x/billing
- **Stripe Webhooks Guide** : https://stripe.com/docs/webhooks
- **Stripe Testing Cards** : https://stripe.com/docs/testing
- **Stripe CLI** : https://stripe.com/docs/stripe-cli

---

**Dernière mise à jour** : 13 décembre 2025
**Version** : 1.0
