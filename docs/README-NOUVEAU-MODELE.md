# 🚀 Stellar - Transition vers le modèle RoboTarget

> **Date de transition :** 12 Décembre 2025
> **Statut :** 📚 Documentation complétée - Prêt pour développement

---

## 🎯 Résumé de la transition

### Ce qui change

#### ❌ Ancien modèle : Réservations horaires
```
User → Réserve créneau 20h-22h → Accède manuellement au matériel → Contrôle en direct
```

**Problèmes :**
- Nécessite présence utilisateur
- Sous-utilisation (météo, conditions)
- Gestion complexe des créneaux
- Expérience limitée

#### ✅ Nouveau modèle : RoboTarget automatisé
```
User → S'abonne (Stardust/Nebula/Quasar) → Configure cibles → RoboTarget automatise → Récupère images
```

**Avantages :**
- 🤖 Automatisation complète
- 🌙 Optimisation conditions
- 💳 Paiement à l'usage (crédits)
- 🎯 Multi-cibles parallèles
- ⭐ Garanties qualité

---

## 📚 Documentation créée

### 1. [MODELE-ROBOTARGET-OVERVIEW.md](./MODELE-ROBOTARGET-OVERVIEW.md)

**Contenu :**
- Vue d'ensemble complète du nouveau modèle
- Architecture globale (Frontend → Laravel → Proxy → Voyager)
- Modèle économique (3 abonnements)
- Flux utilisateur détaillé
- Exemples d'implémentation technique

**À lire en premier** ✨

### 2. [CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md](./CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md)

**Contenu :**
- Détails des 3 abonnements (Stardust/Nebula/Quasar)
- Pricing Engine complet
- Cycle de vie des transactions (Hold → Capture/Refund)
- Intégration Stripe Billing
- API et code d'exemple

**Documentation technique complète** 💳

### 3. Documentation existante conservée

#### ✅ À conserver
- `architecture-technique-voyager-proxy.md` - Architecture proxy (reste valide)
- `astral_documentation.md` - Design system frontend (reste valide)
- `equipment-documentation.md` - Gestion équipements (reste valide)
- `roadmap.md` - Roadmap projet (sera mise à jour)

#### 📦 Archivés dans `docs/archive/`
- `booking-access-documentation.md` - Système de réservations obsolète
- `credit_system_documentation_OLD.md` - Ancien système de crédits v1

---

## 🏗️ Structure de la nouvelle documentation

```
docs/
├── README-NOUVEAU-MODELE.md          ← VOUS ÊTES ICI
├── MODELE-ROBOTARGET-OVERVIEW.md     ← Vue d'ensemble (COMMENCER ICI)
├── CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md ← Système de crédits détaillé
│
├── architecture-technique-voyager-proxy.md  (conservé)
├── astral_documentation.md                   (conservé)
├── equipment-documentation.md                (conservé)
├── roadmap.md                                (à mettre à jour)
│
├── doc_voyager/
│   ├── 📑 Spécification Technique _ Implémentation RoboTarget & Modèle Économique.md
│   ├── Voyager RoboTarget Reserved API.md
│   └── connexion_et_maintien.md
│
└── archive/
    ├── booking-access-documentation.md
    └── credit_system_documentation_OLD.md
```

---

## 🎓 Guides de lecture par profil

### 👨‍💼 Product Owner / Business

**Lire dans cet ordre :**

1. **README-NOUVEAU-MODELE.md** (ce fichier)
   - Comprendre la transition
   - Vue d'ensemble rapide

2. **MODELE-ROBOTARGET-OVERVIEW.md** → Section "Modèle économique"
   - Les 3 abonnements
   - Tarification
   - ROI et MRR

3. **doc_voyager/📑 Spécification Technique**.md** → Sections 2 et 3
   - Offres commerciales détaillées
   - Moteur de crédits
   - Tarifs Stripe

### 👨‍💻 Développeur Backend (Laravel)

**Lire dans cet ordre :**

1. **MODELE-ROBOTARGET-OVERVIEW.md** → Sections "Architecture" et "Flux utilisateur"
   - Comprendre le flow global
   - Interactions entre composants

2. **CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md** → TOUT
   - Modèles Laravel à créer
   - PricingEngine
   - Cycle de vie transactions
   - Intégration Stripe

3. **doc_voyager/📑 Spécification Technique**.md** → Section 4
   - Génération payload JSON
   - Paramètres API Voyager

4. **architecture-technique-voyager-proxy.md** → Section "L'API REST"
   - Endpoints disponibles
   - Format des requêtes/réponses

### 👨‍💻 Développeur Proxy (Node.js)

**Lire dans cet ordre :**

1. **architecture-technique-voyager-proxy.md** → TOUT
   - Architecture proxy existante
   - Connexion TCP Voyager
   - Event handlers

2. **MODELE-ROBOTARGET-OVERVIEW.md** → Section "Implémentation technique / Proxy"
   - Nouvelles routes RoboTarget
   - Nouvelles commandes
   - Handlers spécifiques

3. **doc_voyager/Voyager RoboTarget Reserved API.md**
   - Toutes les commandes RoboTarget
   - Paramètres détaillés
   - Contraintes et masques

### 🎨 Développeur Frontend

**Lire dans cet ordre :**

1. **MODELE-ROBOTARGET-OVERVIEW.md** → Section "Flux utilisateur"
   - Comprendre le parcours utilisateur
   - Étapes de configuration

2. **astral_documentation.md**
   - Design System Astral existant
   - Composants disponibles
   - Animations et effets

3. **MODELE-ROBOTARGET-OVERVIEW.md** → Section "Implémentation / Frontend"
   - Composant Target Planner
   - Template Blade
   - Intégration Alpine.js

4. **CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md** → Section "API"
   - Endpoints à appeler
   - Format des requêtes

---

## 🔑 Concepts clés à comprendre

### 1. Les 3 abonnements

| Plan | Prix | Crédits | Cible | USP |
|------|------|---------|-------|-----|
| 🌟 **Stardust** | 29€ | 20 | Débutant | Point d'entrée accessible |
| 🌌 **Nebula** | 59€ | 60 | Amateur | Options avancées (nuit noire, dashboard) |
| ⚡ **Quasar** | 119€ | 150 | Expert | Priorité + Garanties qualité |

### 2. Correspondance avec Voyager RoboTarget

Chaque abonnement débloque des fonctionnalités API :

```javascript
// Stardust
{
  Priority: 0-1,           // 🔒 Forcé
  C_MoonDown: false,       // 🔒 Forcé (lune acceptée)
  C_HFDMeanLimit: 0,       // 🔒 Pas de garantie
  IsRepeat: false          // 🔒 One-shot
}

// Nebula
{
  Priority: ≤ 2,           // ✅ Choix 0-2
  C_MoonDown: true/false,  // ✅ Option (+100% si true)
  C_HFDMeanLimit: 4.0,     // ✅ Standard (fixe)
  IsRepeat: true           // ✅ Multi-nuits
}

// Quasar
{
  Priority: ≤ 4,           // ✅ Choix 0-4
  C_MoonDown: true,        // ✅ Toujours
  C_HFDMeanLimit: 1.5-4.0, // ✅ Curseur ajustable
  IsRepeat: true,          // ✅ Multi-nuits
  Sets: true               // ✅ Gestion Sets
}
```

### 3. Pricing Engine

**Formule :**
```
Coût_Final = (Durée_Estimée * Coût_Base) * Multiplicateurs
```

**Multiplicateurs :**
- Priority 0-1 : x1.0
- Priority 2 : x1.2
- Priority 3 : x2.0
- Priority 4 : x3.0
- Nuit noire : x2.0
- Garantie HFD : x1.5

**Exemple :**
```
Configuration : 2h, Priority 2, Nuit noire
Coût = 2h * 1.2 * 2.0 = 4.8 → 5 crédits
```

### 4. Cycle de vie des crédits

```
HOLD → EXECUTING → VERIFYING → CAPTURED (success) ✅
                              → REFUNDED (error/abort) 💰
```

**Garantie "Satisfait ou Remboursé" :**
- Si Result = 1 (OK) → Débit définitif
- Si Result = 2/3 (Aborted/Error) → Remboursement automatique

---

## 🚀 Prochaines étapes (développement)

### Phase 1 : Backend Laravel

**Priorité : Haute**

#### À créer :

1. **Modèles**
   - `app/Models/Subscription.php`
   - `app/Models/RoboTarget.php`
   - `app/Models/RoboTargetShot.php`

2. **Migrations**
   - `create_subscriptions_table`
   - `create_robo_targets_table`
   - `create_robo_target_shots_table`

3. **Services**
   - `app/Services/PricingEngine.php`
   - `app/Services/RoboTargetService.php`
   - Compléter `app/Services/VoyagerService.php`

4. **Contrôleurs**
   - `app/Http/Controllers/SubscriptionController.php`
   - `app/Http/Controllers/RoboTargetController.php`
   - `app/Http/Controllers/StripeWebhookController.php`

5. **Middleware**
   - `app/Http/Middleware/RequireActiveSubscription.php`

6. **Routes API**
   - `/api/subscriptions/*`
   - `/api/robotarget/*`
   - `/api/pricing/*`

**Référence complète :**
- [CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md](./CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md) → Section "Implémentation technique"
- [MODELE-ROBOTARGET-OVERVIEW.md](./MODELE-ROBOTARGET-OVERVIEW.md) → Section "Phase 1 : Laravel"

### Phase 2 : Proxy Node.js

**Priorité : Haute**

#### À ajouter :

1. **Routes**
   - `POST /api/robotarget/sets`
   - `POST /api/robotarget/targets`
   - `POST /api/robotarget/shots`
   - `GET /api/robotarget/sessions/:targetGuid/result`
   - `PUT /api/robotarget/targets/:guid/status`

2. **Commandes**
   - `RemoteRoboTargetAddSet`
   - `RemoteRoboTargetAddTarget`
   - `RemoteRoboTargetAddShot`
   - `RemoteRoboTargetSetTargetStatus`
   - `RemoteRoboTargetGetSessionListByTarget`

3. **Event Handlers**
   - `RemoteRoboTargetSessionComplete`
   - Broadcast WebSocket pour dashboard

**Référence complète :**
- [MODELE-ROBOTARGET-OVERVIEW.md](./MODELE-ROBOTARGET-OVERVIEW.md) → Section "Phase 2 : Proxy"
- [architecture-technique-voyager-proxy.md](./architecture-technique-voyager-proxy.md) → Section "Commandes RoboTarget"

### Phase 3 : Frontend

**Priorité : Moyenne**

#### À créer :

1. **Composants Alpine.js**
   - `resources/js/components/targetPlanner.js`
   - `resources/js/components/subscriptionPicker.js`
   - `resources/js/components/dashboardRoboTarget.js`

2. **Vues Blade**
   - `resources/views/subscriptions/choose.blade.php`
   - `resources/views/target-planner.blade.php`
   - `resources/views/dashboard-robotarget.blade.php`

3. **Intégration Sidebar Astrale**
   - Ajouter "Target Planner" dans sidebar
   - Indicateur de crédits restants
   - Badge plan actif

**Référence complète :**
- [MODELE-ROBOTARGET-OVERVIEW.md](./MODELE-ROBOTARGET-OVERVIEW.md) → Section "Phase 3 : Frontend"
- [astral_documentation.md](./astral_documentation.md) → Design System

### Phase 4 : Tests

**Priorité : Moyenne**

#### À tester :

1. **Tests unitaires**
   - PricingEngine (calculs)
   - Subscription (permissions)
   - Cycle de vie transactions

2. **Tests d'intégration**
   - Flow complet création cible
   - Webhooks Stripe
   - Hold → Capture/Refund

3. **Tests end-to-end**
   - Parcours utilisateur complet
   - Dashboard temps réel
   - Notifications

---

## 📞 Questions fréquentes

### Q : Que deviennent les utilisateurs existants ?

**R :** Migration nécessaire :
1. Crédits existants conservés
2. Migration vers abonnement Stardust (ou choix)
3. Email d'information sur le nouveau modèle

### Q : Les anciens packages de crédits restent disponibles ?

**R :** Non, passage complet au modèle abonnement. Les crédits achetés précédemment restent utilisables.

### Q : Peut-on combiner abonnement + packs additionnels ?

**R :** Oui ! Modèle recommandé :
- Abonnement mensuel (base)
- Packs de crédits additionnels (si besoin ponctuel)

### Q : Que se passe-t-il en cas de résiliation ?

**R :**
- Accès conservé jusqu'à fin de période payée
- Crédits restants utilisables
- Pas de renouvellement automatique

### Q : Comment gérer les remboursements ?

**R :** Automatique via le cycle Hold → Refund si :
- Cible en erreur (Result = 3)
- Cible abandonnée (Result = 2)
- Timeout/problème technique

---

## 🎯 Check-list avant développement

### Pour le Product Owner

- [ ] Valider les 3 paliers d'abonnement
- [ ] Valider les prix (29€ / 59€ / 119€)
- [ ] Valider les quantités de crédits (20 / 60 / 150)
- [ ] Valider les restrictions par palier
- [ ] Préparer CGV/CGU
- [ ] Préparer emails de communication
- [ ] Définir stratégie de migration utilisateurs existants

### Pour les développeurs

- [ ] Lire **MODELE-ROBOTARGET-OVERVIEW.md** (vue d'ensemble)
- [ ] Lire **CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md** (détails techniques)
- [ ] Créer branche `feature/robotarget-model`
- [ ] Configurer environnement Stripe (test)
- [ ] Préparer structure de base (modèles, migrations)

### Pour le Designer

- [ ] Lire **astral_documentation.md** (Design System)
- [ ] Concevoir UI "Target Planner"
- [ ] Concevoir UI "Subscription Picker"
- [ ] Concevoir Dashboard RoboTarget
- [ ] Préparer icônes/assets pour les 3 plans

---

## 📝 Changelog documentation

### 12 Décembre 2025

#### ✅ Créé
- `MODELE-ROBOTARGET-OVERVIEW.md` - Vue d'ensemble complète
- `CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md` - Système de crédits v2
- `README-NOUVEAU-MODELE.md` - Ce fichier

#### 📦 Archivé
- `booking-access-documentation.md` → `archive/`
- `credit_system_documentation.md` → `archive/credit_system_documentation_OLD.md`

#### 🔄 À mettre à jour
- `roadmap.md` - Refléter nouveau modèle
- `architecture-technique-voyager-proxy.md` - Ajouter section RoboTarget complète

---

## 🤝 Contribution

Pour contribuer au développement :

1. Lire cette documentation complète
2. Créer une branche feature depuis `main`
3. Suivre les conventions du projet
4. Tester localement
5. Créer une PR avec description détaillée

---

**Questions ? Besoin de clarifications ?**

Contacter l'équipe produit ou consulter la documentation détaillée dans chaque fichier.

---

*Document créé le 12 Décembre 2025*
*Dernière mise à jour : 12 Décembre 2025*
