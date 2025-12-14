# 👨‍💼 Guide Admin - Gestion des Abonnements RoboTarget

## 🎯 Vue d'ensemble

Ce guide explique comment gérer le système d'abonnements RoboTarget en tant qu'administrateur. Le système gère 3 plans mensuels récurrents (Stardust, Nebula, Quasar) avec paiements automatiques via Stripe.

---

## 📊 Dashboard des Abonnements

**URL** : `/admin/subscriptions`

### Statistiques affichées

1. **Abonnements Actifs** : Nombre total d'abonnements en cours
2. **MRR (Monthly Recurring Revenue)** : Revenu mensuel récurrent total
3. **Taux d'Annulation** : Pourcentage de churn ce mois
4. **En Essai** : Nombre d'utilisateurs en période d'essai gratuit (7 jours)

### Distribution des Plans

Visualisation en temps réel de la répartition des abonnés entre les 3 plans :
- 🌟 **Stardust** : 29€/mois - 20 crédits
- 🌌 **Nebula** : 59€/mois - 60 crédits
- ⚡ **Quasar** : 119€/mois - 150 crédits

### Évolution MRR

Graphique des 12 derniers mois montrant :
- MRR mensuel en euros
- Nombre d'abonnés par mois

### Actions disponibles

- **⚙️ Gérer les plans** : Configure les Price IDs Stripe
- **🔄 Sync Stripe** : Synchronise les abonnements depuis Stripe

---

## ⚙️ Gestion des Plans

**URL** : `/admin/subscriptions/plans`

### Configuration Stripe

Cette page permet de configurer les **Price IDs Stripe** pour chaque plan.

#### Étapes de configuration

1. **Créer les produits dans Stripe Dashboard**
   - Aller sur https://dashboard.stripe.com/products
   - Créer 3 produits récurrents mensuels :
     - **Stardust** : 29€/mois
     - **Nebula** : 59€/mois
     - **Quasar** : 119€/mois
   - Copier le Price ID généré (commence par `price_...`)

2. **Configurer dans l'interface admin**
   - Coller le Price ID dans le champ correspondant
   - Cliquer sur "💾 Sauvegarder le Price ID"
   - Le Price ID sera automatiquement ajouté au fichier `.env`

3. **Vérification**
   - Une coche verte ✓ apparaît si le Price ID est configuré
   - Le statut est visible en haut de la page :
     - `✓ Configuré` : OK
     - `✗ Non configuré` : À configurer

#### Statistiques par plan

Pour chaque plan, vous pouvez voir :
- **Nombre d'abonnés actuels**
- **MRR du plan** (abonnés × prix)
- **Prix par crédit** : Prix mensuel / Crédits mensuels
- **Configuration actuelle** : Prix, crédits, ratio

#### ⚠️ Important

> Les modifications des Price IDs affectent **immédiatement** les nouveaux abonnements. Les abonnements existants conservent leur ancien Price ID jusqu'au prochain renouvellement.

---

## 👥 Liste des Abonnés

**URL** : `/admin/subscriptions/subscribers`

### Filtres disponibles

- **Recherche** : Par nom ou email
- **Plan** : Filtrer par Stardust, Nebula ou Quasar
- Pagination : 20 abonnés par page

### Informations affichées

Pour chaque abonné :
- **Nom et email**
- **Plan actuel** (badge coloré)
- **Crédits** : Solde actuel / Crédits mensuels (+ pourcentage restant)
- **Statut** :
  - ✓ Actif (vert)
  - ⏱ Essai (jaune)
  - ⚠️ Retard (rouge) - Paiement échoué
  - Autres statuts Stripe
- **Date d'inscription**
- **Actions** : Voir les détails

### Statistiques du bas

- **Total abonnés** : Sur la page actuelle
- **MRR Total** : Revenu mensuel des abonnés affichés
- **ARR Projeté** : MRR × 12
- **Crédits en circulation** : Total des crédits non utilisés

---

## 🔍 Détails d'un Abonnement

**URL** : `/admin/subscriptions/{subscription}`

### Informations d'abonnement

- **Plan** : Badge coloré avec nom du plan
- **Statut** : Actif, Essai, Retard, etc.
- **Prix mensuel** : Montant facturé chaque mois
- **Crédits mensuels** : Quota renouvelé le 1er du mois
- **Date de création** : Avec différence en temps humain
- **Fin d'essai** : Si période d'essai active
- **Stripe ID** : ID de l'abonnement Stripe
- **Stripe Customer** : ID du client Stripe

### Solde Crédits

Affichage visuel du solde actuel :
- **Barre de progression** : % de crédits restants
- Exemple : "45 / 60" = 45 crédits restants sur 60 mensuels

### Historique des Crédits

Tableau des transactions récentes :
- **Date** : Horodatage de la transaction
- **Type** :
  - `purchase` (vert) : Achat ou renouvellement
  - `usage` (rouge) : Utilisation (target soumise)
  - `refund` (bleu) : Remboursement (target échouée)
  - `admin_adjustment` (violet) : Ajustement manuel admin
- **Montant** : +X ou -X crédits
- **Description** : Raison de la transaction

### 💰 Ajuster les Crédits

Formulaire pour ajustement manuel :
1. **Montant** : Nombre positif (ajouter) ou négatif (retirer)
2. **Raison** : Justification obligatoire
3. Cliquer sur "💾 Ajuster"

**Cas d'usage** :
- Compensation suite à un problème technique
- Bonus promotionnel
- Correction d'une erreur de facturation

### ⚠️ Zone Dangereuse

Formulaire d'annulation d'abonnement :
1. **Raison de l'annulation** : Justification obligatoire
2. Cliquer sur "❌ Annuler l'Abonnement"
3. Confirmation obligatoire via popup

**Effet** :
- Annulation immédiate dans Stripe
- Statut mis à jour en DB : `status = 'cancelled'`
- L'utilisateur conserve ses crédits jusqu'à fin de période payée
- Plus de renouvellement automatique

### 🔗 Liens Stripe

Accès rapide vers :
- **Voir le Client Stripe** : Page customer dans Stripe Dashboard
- **Voir l'Abonnement Stripe** : Page subscription dans Stripe Dashboard

---

## 🔄 Synchronisation Stripe

**Bouton** : "🔄 Sync Stripe" (sur le dashboard)

### Fonctionnement

1. Récupère tous les abonnements depuis Stripe (limite 100)
2. Pour chaque abonnement :
   - Trouve l'utilisateur via `stripe_id`
   - Met à jour ou crée l'abonnement local
   - Synchronise le statut Stripe
3. Affiche le résultat :
   - X abonnements synchronisés
   - X erreurs

### Quand utiliser ?

- Après modifications manuelles dans Stripe Dashboard
- Pour corriger des désynchronisations
- Après résolution de problèmes de webhook

⚠️ **Note** : Les webhooks Stripe gèrent normalement la synchronisation automatique. Cette fonction est un **fallback manuel**.

---

## 📈 Rapports et Analytics

**URL** : `/admin/subscriptions/reports`

### Filtres

- **Date de début** : Par défaut : il y a 30 jours
- **Date de fin** : Par défaut : aujourd'hui

### Métriques affichées

1. **Nouveaux abonnements** : Nombre de créations sur la période
2. **Annulations** : Nombre d'annulations sur la période
3. **Croissance nette** : Nouveaux - Annulations
4. **Revenu** : Total généré sur la période
5. **Taux de churn** : (Annulations / Nouveaux) × 100

### Export

- Bouton "📥 Exporter CSV" pour télécharger les données

---

## 📊 Métriques et KPIs

### MRR (Monthly Recurring Revenue)

**Formule** :
```
MRR = Σ(Prix plan × Nombre d'abonnés actifs du plan)
```

**Exemple** :
- 10 Stardust × 29€ = 290€
- 5 Nebula × 59€ = 295€
- 2 Quasar × 119€ = 238€
- **MRR Total = 823€**

### ARR (Annual Recurring Revenue)

**Formule** :
```
ARR = MRR × 12
```

**Exemple** : 823€ × 12 = **9 876€**

### Churn Rate (Taux d'annulation)

**Formule** :
```
Churn Rate = (Annulations ce mois / Total abonnements début mois) × 100
```

**Exemple** :
- 100 abonnés début mois
- 5 annulations
- **Churn Rate = 5%**

### ARPU (Average Revenue Per User)

**Formule** :
```
ARPU = MRR / Nombre d'abonnés actifs
```

**Exemple** : 823€ / 17 = **48,41€**

### LTV (Lifetime Value) - Estimé

**Formule simple** :
```
LTV = ARPU / Churn Rate mensuel
```

**Exemple** : 48,41€ / 0,05 = **968€**

---

## 🔔 Webhooks Stripe

### Événements gérés automatiquement

Le système écoute les webhooks Stripe suivants :

1. **`customer.subscription.created`** : Nouvel abonnement créé
   - Création en DB si n'existe pas
   - Ajout des crédits initiaux

2. **`customer.subscription.updated`** : Abonnement modifié
   - Mise à jour du statut local
   - Log de la modification

3. **`customer.subscription.deleted`** : Abonnement annulé
   - Statut = `cancelled`
   - `ends_at` = maintenant

4. **`invoice.paid`** : **IMPORTANT - Renouvellement mensuel**
   - Reset des crédits au montant mensuel
   - Log du renouvellement

5. **`invoice.payment_failed`** : Échec de paiement
   - Log d'alerte
   - Email de notification (TODO)

6. **`checkout.session.completed`** : Session de paiement terminée
   - Confirmation de création d'abonnement

### Endpoint webhook

**URL** : `https://votre-domaine.com/stripe/subscription-webhook`

**Configuration** : Voir `docs/STRIPE-CONFIGURATION.md`

### Logs des webhooks

Les événements webhook sont loggés dans :
- **Laravel logs** : `storage/logs/laravel.log`
- **Stripe Dashboard** : Développeurs > Webhooks > Événements

---

## ⚙️ Tâches de maintenance

### Mensuelle (automatique via webhook)

✅ **Renouvellement des crédits** (1er du mois)
- Webhook `invoice.paid` reçu
- Crédits reset au montant mensuel du plan
- Ancien solde perdu (non reporté)

### Hebdomadaire (manuel)

🔧 **Vérification des abonnements `past_due`**
1. Aller sur `/admin/subscriptions/subscribers`
2. Filtrer les abonnements en retard
3. Vérifier dans Stripe Dashboard :
   - Tentatives de paiement automatiques
   - Mettre à jour la carte si nécessaire
   - Contacter l'utilisateur

### Mensuelle (manuel)

📊 **Analyse du churn**
1. Aller sur `/admin/subscriptions/reports`
2. Période : mois précédent
3. Analyser :
   - Taux de churn
   - Raisons d'annulation (logs)
   - Plans les plus annulés

🎯 **Optimisation des plans**
1. Comparer les MRR par plan
2. Identifier les plans sous-performants
3. Envisager des ajustements :
   - Prix
   - Nombre de crédits
   - Fonctionnalités

---

## 🚨 Gestion des problèmes

### Utilisateur ne reçoit pas ses crédits mensuels

**Diagnostic** :
1. Vérifier le statut de l'abonnement : `/admin/subscriptions/{subscription}`
2. Vérifier le webhook `invoice.paid` dans Stripe Dashboard
3. Vérifier les logs Laravel : `storage/logs/laravel.log`

**Solution** :
1. Si webhook pas reçu : Déclencher manuellement avec "🔄 Sync Stripe"
2. Si problème de crédit : Ajuster manuellement via "Ajuster les Crédits"

### Abonnement en `past_due`

**Cause** : Paiement échoué (carte expirée, fonds insuffisants)

**Action** :
1. Contacter l'utilisateur
2. Demander mise à jour de la carte dans Stripe
3. Stripe réessaiera automatiquement (3 tentatives)
4. Si échec : Annulation automatique après 23 jours

### Désynchronisation Stripe ↔ Laravel

**Symptômes** :
- Statut différent entre Stripe et DB
- Abonnement existe dans Stripe mais pas en DB

**Solution** :
1. Cliquer sur "🔄 Sync Stripe" sur le dashboard
2. Vérifier les logs pour erreurs
3. Si problème persiste : Créer manuellement en DB

### Price ID incorrect

**Symptômes** :
- Erreur lors de la création d'abonnement
- "No such price: price_xxxxx"

**Solution** :
1. Aller sur `/admin/subscriptions/plans`
2. Vérifier que les Price IDs correspondent à Stripe Dashboard
3. Mettre à jour si nécessaire
4. Tester avec carte de test

---

## 📚 Ressources utiles

### Documentation technique

- **Configuration Stripe** : `docs/STRIPE-CONFIGURATION.md`
- **Intégration complète** : `docs/STRIPE-INTEGRATION-COMPLETE.md`
- **Guide utilisateur crédits** : `docs/GUIDE-SYSTEME-CREDITS.md`

### Liens externes

- **Stripe Dashboard** : https://dashboard.stripe.com
- **Stripe Webhooks** : https://dashboard.stripe.com/webhooks
- **Stripe Docs - Subscriptions** : https://stripe.com/docs/billing/subscriptions
- **Laravel Cashier Docs** : https://laravel.com/docs/11.x/billing

### Accès rapides

| Page | URL | Description |
|------|-----|-------------|
| Dashboard | `/admin/subscriptions` | Vue d'ensemble |
| Plans | `/admin/subscriptions/plans` | Configurer Price IDs |
| Abonnés | `/admin/subscriptions/subscribers` | Liste complète |
| Rapports | `/admin/subscriptions/reports` | Analytics |

---

## ✅ Checklist de démarrage

Lors de la première mise en place :

- [ ] Créer les 3 produits dans Stripe Dashboard (Stardust, Nebula, Quasar)
- [ ] Copier les 3 Price IDs dans `/admin/subscriptions/plans`
- [ ] Configurer le webhook Stripe pointant vers `/stripe/subscription-webhook`
- [ ] Vérifier que le webhook secret est dans `.env`
- [ ] Tester un abonnement en mode test avec carte `4242 4242 4242 4242`
- [ ] Vérifier que les crédits sont ajoutés après paiement
- [ ] Vérifier que le webhook `invoice.paid` fonctionne (logs)
- [ ] Documenter les logins admin pour l'équipe
- [ ] Configurer les alertes email pour paiements échoués

---

## 🎓 Bonnes pratiques

### Ajustements de crédits

- ✅ **Toujours** justifier la raison dans le formulaire
- ✅ Logger toutes les actions (automatique)
- ✅ Communiquer avec l'utilisateur si ajustement important
- ❌ **Éviter** les ajustements fréquents (signe d'un problème système)

### Annulations d'abonnements

- ✅ **Demander** la raison à l'utilisateur avant annulation
- ✅ Logger la raison dans le formulaire
- ✅ Analyser les raisons d'annulation mensuellement
- ❌ **Ne jamais** annuler sans justification

### Modifications de prix

- ✅ **Prévenir** les utilisateurs 30 jours avant
- ✅ **Créer** un nouveau Price ID Stripe (pas modifier l'ancien)
- ✅ **Grandfathering** : Laisser les anciens abonnés à l'ancien prix
- ❌ **Ne jamais** modifier un Price ID actif

### Monitoring

- ✅ **Consulter** le dashboard quotidiennement
- ✅ **Analyser** les webhooks échoués hebdomadairement
- ✅ **Exporter** les rapports mensuellement
- ✅ **Surveiller** le churn rate (alerte si > 10%)

---

**Dernière mise à jour** : 13 décembre 2025
**Version** : 1.0
**Auteur** : Claude

**Pour toute question** : Consulter `docs/STRIPE-CONFIGURATION.md` ou les logs Laravel.
