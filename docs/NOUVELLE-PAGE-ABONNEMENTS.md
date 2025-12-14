# 🎨 Nouvelle Page d'Abonnements RoboTarget

## 📋 Vue d'ensemble

La page `/fr/subscriptions/choose` a été complètement redesignée pour offrir deux expériences distinctes :

1. **Pour les utilisateurs NON abonnés** : Page de découverte et souscription
2. **Pour les utilisateurs ABONNÉS** : Page de gestion avec factures et changement de plan

---

## 🎯 Pour les utilisateurs NON abonnés

### Ce qu'ils voient :

#### 1. **En-tête explicatif**
```
Choisissez votre plan RoboTarget
Accédez à notre télescope robotisé professionnel et capturez vos cibles favorites
automatiquement, de jour comme de nuit, depuis n'importe où dans le monde
```

#### 2. **Section "Comment fonctionnent les crédits ?"**

Une grande section avec fond dégradé indigo/violet contenant 4 cartes explicatives :

- **⏱️ 1 crédit = 1 heure**
  - Explication : Le coût de base selon la durée estimée
  - Exemple : 3 heures = 3 crédits de base

- **🎯 Multiplicateurs qualité**
  - Priorité élevée, nuit noire (×2), garantie HFD (×1.5)
  - Pour garantir les meilleures conditions

- **💰 Remboursement auto**
  - Si une session échoue → crédits remboursés
  - Vous ne payez que pour les images réussies

- **🔄 Renouvellement mensuel**
  - Renouvellement le 1er de chaque mois
  - Crédits non reportés

**Bonus** : Exemple de calcul concret
```
• Target M31 : 10 Luminance (5min) + 10×RGB (3min) = 1.33h = 2 crédits de base
• Avec priorité normale (×1.5) = 3 crédits
• Avec option nuit noire (×2) = 6 crédits total
```

#### 3. **Grille des 3 plans**

Chaque carte de plan contient :

**Header (en-tête)**
- Badge emoji (🌟 Stardust, 🌌 Nebula, ⚡ Quasar)
- Nom du plan
- Tagline descriptive
- Prix en gros (29€, 59€, 119€)
- Nombre de crédits avec équivalence en heures

**Fonctionnalités (expandables)**
- Chaque feature est cliquable pour voir une explication détaillée
- Icône verte ✓ pour les fonctionnalités
- Texte explicatif qui s'ouvre au clic

**Limitations (expandables)**
- Icône rouge ✗ pour les restrictions
- Texte explicatif qui s'ouvre au clic
- Seulement pour Stardust (Nebula et Quasar n'ont pas de restrictions)

**Inclus**
- Liste à puces des services inclus
- Support, stockage, formats, etc.

**Call-to-Action**
- Bouton gradient bleu/violet pour Nebula (POPULAIRE)
- Bouton gris pour les autres
- Badge "🎁 7 jours d'essai gratuit"

#### 4. **Section FAQ (Questions fréquentes)**

4 questions expandables :
1. Puis-je changer de plan en cours de mois ?
2. Les crédits non utilisés sont-ils reportés ?
3. Que se passe-t-il si mes images sont floues ?
4. Puis-je acheter des crédits supplémentaires ?

---

## 👤 Pour les utilisateurs ABONNÉS

### Ce qu'ils voient :

#### 1. **En-tête personnalisé**
```
Mon Abonnement RoboTarget
Gérez votre abonnement et consultez vos factures
```

#### 2. **Carte "Plan actuel"** (gradient bleu/violet)

Affiche :
- Badge + Nom du plan (ex: 🌌 Nebula)
- Prix mensuel (ex: 59€/mois)
- 3 colonnes de stats :
  - Crédits mensuels (ex: 60)
  - Solde actuel (ex: 45) en jaune
  - Statut (Actif ou Essai gratuit)

Si en période d'essai :
```
ℹ️ Votre période d'essai gratuit se termine le 20/12/2025
```

#### 3. **Tableau des factures**

Tableau avec colonnes :
- Numéro (ex: INV-202512-001)
- Date (ex: 01/12/2025)
- Description (ex: Abonnement Nebula - Décembre 2025)
- Montant (ex: 59€)
- Statut (badge vert "✓ Payée")

*Note : Pour l'instant, factures de démonstration (2 derniers mois)*

#### 4. **Section "Changer de plan"**

Titre + explication :
```
Changer de plan
Passez à un plan supérieur ou inférieur selon vos besoins
```

Puis affichage de la **même grille des 3 plans** avec quelques différences :

**Différences visuelles** :
- Le plan actuel a un badge vert "✓ PLAN ACTUEL" au-dessus
- Le plan actuel a une bordure verte
- Le plan actuel a un bouton désactivé "✓ Votre plan actuel"
- Les autres plans ont un bouton "Passer à [Nom]" au lieu de "Commencer avec"
- Pas de badge "7 jours d'essai gratuit" (déjà abonné)

**Pas de section FAQ** (déjà abonné, connait déjà le système)

---

## 🎨 Design et cohérence

### Thème global
- Design cohérent avec les autres pages (dark mode supporté)
- Cartes blanches sur fond clair (dark:bg-gray-800)
- Ombres et transitions douces
- Responsive (mobile, tablette, desktop)

### Couleurs
- **Plans** : Gradient bleu/violet pour le plan populaire
- **Plan actuel** : Bordure et badge verts
- **Features** : Icônes vertes ✓
- **Restrictions** : Icônes rouges ✗
- **Crédits** : Bleu (blue-600)
- **Statut actif** : Vert

### Interactivité
- Features et restrictions expandables (Alpine.js)
- FAQ expandable (Alpine.js)
- Hover effects sur les cartes
- Transitions fluides

---

## ⚙️ Fonctionnalités

### 1. **Nouveau abonnement**

Workflow :
```
1. User non abonné visite /fr/subscriptions/choose
2. Lit les explications sur les crédits
3. Compare les 3 plans
4. Clique sur "Commencer avec Nebula" (par exemple)
5. → POST /fr/subscriptions/subscribe avec plan=nebula
6. → Création de l'abonnement
7. → Ajout de 60 crédits au solde
8. → Redirection vers /fr/robotarget
9. → Message de succès "Félicitations ! Votre abonnement Nebula est actif..."
```

### 2. **Changement de plan**

Workflow :
```
1. User avec abonnement Stardust visite /fr/subscriptions/choose
2. Voit son plan actuel avec badge vert
3. Voit les factures des 2 derniers mois
4. Décide de passer à Nebula
5. Clique sur "Passer à Nebula"
6. → POST /fr/subscriptions/subscribe avec plan=nebula
7. → Mise à jour de l'abonnement existant
8. → Ajustement des crédits (+40 car 60-20)
9. → Redirection vers /fr/subscriptions/choose
10. → Message "Votre plan a été changé de Stardust à Nebula..."
```

### 3. **Clic sur plan actuel**

```
1. User clique sur son plan actuel
2. → Bouton désactivé, rien ne se passe
3. Bouton gris avec texte "✓ Votre plan actuel"
```

---

## 🔧 Fichiers modifiés

### 1. **SubscriptionController.php**

**Méthode `choose()`** :
- Génère les données complètes des plans avec `getPlansData()`
- Génère les factures de démo avec `getDemoInvoices()` si abonné
- Calcule l'historique d'utilisation des crédits
- Passe tout à la vue

**Méthode `getPlansData()`** (nouvelle) :
- Retourne un tableau détaillé des 3 plans
- Features avec explications (array associatif)
- Restrictions avec explications
- Services inclus
- Taglines

**Méthode `getDemoInvoices()`** (nouvelle) :
- Génère 2 factures de démonstration
- Mois actuel + mois précédent
- Avec numéro, date, montant, statut

**Méthode `subscribe()`** (modifiée) :
- **SI déjà abonné** : Change le plan au lieu de créer un nouveau
  - Met à jour plan et credits_per_month
  - Ajuste le solde de crédits (différence)
  - Redirige vers /subscriptions/choose
- **SI non abonné** : Crée un nouvel abonnement
  - Ajoute les crédits initiaux
  - Redirige vers /robotarget

### 2. **choose.blade.php**

Structure complète en 2 modes :

**Mode NON abonné** (`@if(!$currentSubscription)`) :
- En-tête de bienvenue
- Section explicative crédits
- Grille des 3 plans
- FAQ
- Lien retour dashboard

**Mode ABONNÉ** (`@if($currentSubscription)`) :
- En-tête "Mon Abonnement"
- Carte plan actuel (gradient)
- Tableau factures
- Section "Changer de plan"
- Grille des 3 plans (avec badges différents)
- Lien retour targets

**Composants réutilisés** :
- Même grille de plans dans les 2 modes
- Conditions pour badges et boutons différents
- Alpine.js pour expandables

---

## 📊 Données des plans

### Stardust (29€/mois)

**Fonctionnalités** :
- Priority Low (0-1) → "Vos targets seront traitées en priorité basse"
- 20 crédits/mois → "Environ 20h d'observation par mois"
- Accès RoboTarget → "Interface web complète de gestion"
- Mode One-Shot uniquement → "Une session par target, idéal pour débuter"
- Dashboard temps réel → "Suivez vos acquisitions en direct"

**Limitations** :
- Pas de nuit noire → "Les sessions peuvent inclure la lune"
- Pas de garantie HFD → "Pas de garantie de netteté"
- Pas de projets multi-nuits → "Une seule session par target"

**Inclus** :
- Support email standard
- Stockage 30 jours
- Téléchargement FITS

### Nebula (59€/mois) ⭐ POPULAIRE

**Fonctionnalités** :
- Priority Normal (0-2) → "Priorité normale à élevée pour vos sessions"
- 60 crédits/mois → "Environ 60h d'observation par mois"
- Option Nuit noire 🌙 → "Acquisition sans pollution lunaire (×2 crédits)"
- Projets multi-nuits → "Répétez vos sessions plusieurs nuits"
- HFD fixe à 4.0 ⭐ → "Garantie de netteté standard"
- Dashboard avancé → "Statistiques et graphiques détaillés"

**Limitations** : Aucune

**Inclus** :
- Support prioritaire
- Stockage 90 jours
- Téléchargement FITS + PNG
- Historique complet

### Quasar (119€/mois)

**Fonctionnalités** :
- Priority First (0-4) 🏆 → "Coupe-file complet, priorité maximale"
- 150 crédits/mois → "Environ 150h d'observation par mois"
- Nuit noire incluse 🌙 → "Sans surcoût - qualité optimale garantie"
- HFD ajustable (1.5-4.0) ⭐⭐⭐ → "Contrôle précis de la netteté"
- Gestion avancée Sets → "Organisez vos acquisitions en projets"
- Projets multi-nuits illimités → "Répétez autant que nécessaire"
- Support prioritaire 24/7 → "Réponse garantie sous 2h"

**Limitations** : Aucune

**Inclus** :
- Support dédié 24/7
- Stockage illimité
- Tous formats (FITS, PNG, TIFF)
- API avancée
- Pré-traitement optionnel

---

## 🧪 Comment tester

### Test 1 : Nouvel abonnement

1. Se connecter avec un compte SANS abonnement
2. Aller sur `/fr/subscriptions/choose`
3. **Vérifier** : Section explicative des crédits visible
4. **Vérifier** : 3 plans affichés avec badge "7 jours d'essai"
5. **Vérifier** : FAQ visible en bas
6. Cliquer sur les features pour voir les explications
7. Cliquer sur "Commencer avec Nebula"
8. **Vérifier** : Redirection vers `/fr/robotarget`
9. **Vérifier** : Message de succès
10. **Vérifier** : Solde de crédits = 60

### Test 2 : Changement de plan (upgrade)

1. Se connecter avec un compte abonné à Stardust
2. Aller sur `/fr/subscriptions/choose`
3. **Vérifier** : Carte gradient avec plan actuel
4. **Vérifier** : Tableau des factures (2 lignes)
5. **Vérifier** : Stardust a badge vert "PLAN ACTUEL"
6. **Vérifier** : Bouton Stardust désactivé
7. Cliquer sur "Passer à Nebula"
8. **Vérifier** : Reste sur `/fr/subscriptions/choose`
9. **Vérifier** : Message "Votre plan a été changé de Stardust à Nebula"
10. **Vérifier** : Solde de crédits augmenté de +40

### Test 3 : Changement de plan (downgrade)

1. Se connecter avec un compte abonné à Quasar
2. Aller sur `/fr/subscriptions/choose`
3. Cliquer sur "Passer à Nebula"
4. **Vérifier** : Message de changement de plan
5. **Vérifier** : Solde de crédits diminué de -90

### Test 4 : Clic sur plan actuel

1. Avoir un abonnement Nebula actif
2. Aller sur `/fr/subscriptions/choose`
3. Cliquer sur le bouton "✓ Votre plan actuel"
4. **Vérifier** : Rien ne se passe (bouton désactivé)

### Test 5 : Responsive design

1. Ouvrir `/fr/subscriptions/choose` sur mobile
2. **Vérifier** : Grille passe en 1 colonne
3. **Vérifier** : Cartes s'empilent verticalement
4. **Vérifier** : Texte lisible, pas de débordement

---

## 🎯 Points clés de l'UX

### Pour les nouveaux utilisateurs

✅ **Clarté** : Section explicative complète sur le système de crédits
✅ **Transparence** : Exemple de calcul concret
✅ **Confiance** : Remboursement automatique expliqué
✅ **Comparaison facile** : 3 plans côte à côte avec détails expandables
✅ **Rassurance** : FAQ pour répondre aux questions courantes
✅ **Incentive** : Badge "7 jours d'essai gratuit"

### Pour les utilisateurs abonnés

✅ **Vue d'ensemble** : Plan actuel, crédits, statut en un coup d'œil
✅ **Transparence** : Factures visibles
✅ **Flexibilité** : Changement de plan facile
✅ **Feedback clair** : Messages de confirmation
✅ **Pas de friction** : Pas besoin de chercher, tout est sur une seule page

---

## 🚀 Prochaines améliorations possibles

### Court terme
- [ ] Intégration Stripe pour vrais paiements
- [ ] Vraies factures PDF téléchargeables
- [ ] Graphique d'utilisation des crédits
- [ ] Historique complet des transactions

### Moyen terme
- [ ] Options de paiement annuel (réduction)
- [ ] Achat de packs de crédits supplémentaires
- [ ] Notifications email avant fin d'essai
- [ ] Gestion des moyens de paiement

### Long terme
- [ ] Plans personnalisés pour entreprises
- [ ] API de facturation
- [ ] Exportation comptable
- [ ] Programme de parrainage

---

## 📝 Notes techniques

### Alpine.js utilisé pour :
- Expandables features/restrictions (x-data, @click, x-show, x-collapse)
- FAQ accordion (openFaq state)
- Animations de rotation des flèches

### Blade directives :
- `@if($currentSubscription)` pour basculer entre les 2 modes
- `@foreach` pour itérer sur plans, features, restrictions
- `@json()` pour passer les données subscription si besoin plus tard

### Tailwind classes importantes :
- `lg:scale-105` pour agrandir le plan populaire
- `border-green-500` pour le plan actuel
- Gradients : `from-blue-600 to-purple-600`
- Dark mode : `dark:bg-gray-800`, `dark:text-white`

---

## ✅ Résumé

La nouvelle page `/fr/subscriptions/choose` offre maintenant :

**Pour les nouveaux** : Une expérience de découverte complète et pédagogique
**Pour les abonnés** : Un hub de gestion complet avec factures et changement de plan

Le tout dans un design cohérent, moderne, et entièrement responsive ! 🎨
