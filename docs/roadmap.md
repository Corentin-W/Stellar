# 🚀 STELLAR - Roadmap du Projet

> **Dernière mise à jour : 12 Décembre 2025**
> **⚠️ TRANSITION MAJEURE : Passage au modèle RoboTarget**

---

## 📊 Vue d'ensemble

**Stellar** est une plateforme SaaS d'astrophotographie distante automatisée via RoboTarget, avec système d'abonnements (Stardust/Nebula/Quasar).

### Progression globale

```
████████████████░░░░  75% - En transition vers modèle RoboTarget
```

### 🔄 Changement de paradigme

#### Ancien modèle (phases 1-5) ❌
- Réservations horaires
- Contrôle manuel du matériel
- Présence utilisateur requise

#### Nouveau modèle (phase 6+) ✅
- **Abonnements mensuels** (Stardust/Nebula/Quasar)
- **Automatisation RoboTarget**
- **Crédits à la consommation**
- **Facturation à l'usage réel**

---

## ✅ Phase 1 : Fondations - TERMINÉ

### Infrastructure de base
- [x] Installation Laravel 12
- [x] Configuration Webpack Mix + Tailwind CSS v4
- [x] Alpine.js intégré avec stores globaux
- [x] Système de routing avec locale (FR/EN)
- [x] Configuration environnement (Herd)

### Design System Astral
- [x] Thème cosmique/galactique immersif
- [x] Sidebar astrale rétractable complète
- [x] Dashboard cosmique avec métriques
- [x] Palette de couleurs spatiales
- [x] Animations et effets (nébuleuses, étoiles scintillantes)
- [x] Responsive mobile/tablet/desktop
- [x] Dark mode avec persistence
- [x] Typographie (Inter + Orbitron)

### Base de données
- [x] Migration users avec credits_balance
- [x] Table equipment complète
- [x] Table credit_packages (sera remplacée par subscriptions)
- [x] Table promotions
- [x] Table credit_transactions
- [x] Relations et index optimisés

---

## ✅ Phase 2 : Gestion d'équipement - TERMINÉ

### Interface administrateur
- [x] CRUD complet des équipements
- [x] Upload multiple d'images
- [x] Upload multiple de vidéos
- [x] Spécifications techniques JSON dynamiques
- [x] Gestion des statuts (available, unavailable, maintenance)
- [x] Système de tri et ordre d'affichage
- [x] Filtres avancés (type, statut, recherche)
- [x] Statistiques en temps réel
- [x] Toggle rapides (statut, featured, active)
- [x] Galerie avec modal de zoom

### Types d'équipement supportés
- [x] Telescope - Tubes optiques
- [x] Mount - Montures
- [x] Camera - Caméras d'acquisition
- [x] Accessory - Accessoires
- [x] Complete Setup - Installations complètes

### Stockage et médias
- [x] Storage public configuré
- [x] Validation uploads (taille, format)
- [x] Organisation dossiers (images, videos)
- [x] Affichage optimisé des médias

---

## ✅ Phase 3 : Système de crédits v1 - TERMINÉ (sera migré v2)

### Intégration Stripe Cashier
- [x] Installation et configuration Cashier
- [x] Création de Payment Intents
- [x] Gestion des webhooks sécurisés
- [x] Confirmation automatique des paiements
- [x] Support multi-devises (EUR par défaut)

### Gestion des transactions
- [x] Trait HasCredits pour User
- [x] Historique complet des transactions
- [x] Types : purchase, usage, refund, bonus, admin_adjustment
- [x] Balance avant/après chaque transaction
- [x] Métadonnées JSON
- [x] Référence vers objets liés

### Interface administrateur
- [x] Dashboard administrateur
- [x] Vue des utilisateurs et soldes
- [x] Ajustements manuels de crédits
- [x] Statistiques de ventes

**⚠️ Note :** Ce système v1 sera migré vers le système v2 avec abonnements.

---

## ✅ Phase 4 : Système de réservation - TERMINÉ (obsolète)

**⚠️ OBSOLÈTE : Remplacé par le modèle RoboTarget**

### Ce qui reste utile
- [x] Table credit_transactions (réutilisée)
- [x] Trait HasCredits (adapté pour v2)
- [x] Intégration Stripe (base pour abonnements)

### Ce qui est remplacé
- ❌ equipment_bookings → robo_targets
- ❌ Créneaux horaires → Configuration de cibles
- ❌ Contrôle manuel → Automatisation RoboTarget

---

## ✅ Phase 5 : Intégration Voyager Base - TERMINÉ

### Documentation
- [x] Documentation Voyager Event Methods
- [x] Documentation RoboTarget JSON-RCP
- [x] Spécifications techniques complètes
- [x] PDF Voyager RoboTarget Reserved API

### Proxy Node.js (voyager-proxy/)
- [x] Connexion TCP/IP persistante (port 5950)
- [x] Authentification Base64
- [x] Heartbeat automatique avec reconnexion
- [x] Event handlers (ControlData, NewJPGReady, ShotRunning, etc.)
- [x] API REST de base (dashboard, control)
- [x] WebSocket temps réel (Socket.IO)
- [x] Sécurité (API Key, CORS, rate limiting)
- [x] Interface de test (test-ui/)

### Intégration Laravel de base
- [x] VoyagerService avec fallback mock
- [x] Configuration services.php
- [x] Header API Key dans requêtes HTTP

**✅ Base solide pour extension RoboTarget**

---

## 🚧 Phase 6 : Modèle RoboTarget - EN COURS

**Priorité : CRITIQUE 🔴**

### Documentation ✅ TERMINÉ
- [x] MODELE-ROBOTARGET-OVERVIEW.md
- [x] CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md
- [x] README-NOUVEAU-MODELE.md
- [x] Archivage ancienne documentation
- [x] Mise à jour roadmap

### Backend Laravel - À FAIRE 🔴

#### Modèles et base de données
- [ ] Migration `subscriptions` table
- [ ] Migration `robo_targets` table
- [ ] Migration `robo_target_shots` table
- [ ] Migration `robo_target_sessions` table
- [ ] Modèle `Subscription` avec permissions
- [ ] Modèle `RoboTarget` avec relations
- [ ] Modèle `RoboTargetShot` (filtres/expositions)
- [ ] Modèle `RoboTargetSession` (résultats)
- [ ] Modifier `User` pour abonnements

#### Services
- [ ] `PricingEngine` (calcul coûts)
- [ ] `RoboTargetService` (logique métier)
- [ ] `SubscriptionService` (gestion abonnements)
- [ ] Étendre `VoyagerProxyService` (commandes RoboTarget)
- [ ] `PayloadBuilder` (génération JSON Voyager)

#### Contrôleurs
- [ ] `SubscriptionController` (souscription, annulation)
- [ ] `RoboTargetController` (CRUD cibles)
- [ ] `RoboTargetShotController` (gestion shots)
- [ ] `PricingController` (estimation coûts)
- [ ] `StripeWebhookController` (billing events)

#### Middleware
- [ ] `RequireActiveSubscription`
- [ ] `CheckFeatureAccess` (selon plan)
- [ ] `ValidateRoboTargetConfig`

#### Routes API
- [ ] `/api/subscriptions/*` (gestion abonnements)
- [ ] `/api/robotarget/targets/*` (CRUD cibles)
- [ ] `/api/robotarget/shots/*` (configuration shots)
- [ ] `/api/pricing/estimate` (estimation coûts)
- [ ] `/stripe/webhook` (webhooks Stripe Billing)

#### Jobs et événements
- [ ] `CheckStaleTargetsJob` (timeout)
- [ ] `ProcessRoboTargetResultJob` (résultats)
- [ ] `CreditMonthlyAllowanceJob` (renouvellement)
- [ ] Events : `TargetCreated`, `TargetCompleted`, `TargetFailed`

### Proxy Node.js - À FAIRE 🔴

#### Routes RoboTarget
- [ ] `POST /api/robotarget/sets` (créer Set)
- [ ] `POST /api/robotarget/targets` (ajouter Target)
- [ ] `POST /api/robotarget/shots` (ajouter Shot)
- [ ] `GET /api/robotarget/sessions/:targetGuid/result`
- [ ] `PUT /api/robotarget/targets/:guid/status` (activer/désactiver)
- [ ] `GET /api/robotarget/targets/:guid/progress`
- [ ] `DELETE /api/robotarget/targets/:guid`

#### Commandes Voyager
- [ ] `RemoteRoboTargetAddSet`
- [ ] `RemoteRoboTargetAddTarget`
- [ ] `RemoteRoboTargetAddShot`
- [ ] `RemoteRoboTargetSetTargetStatus`
- [ ] `RemoteRoboTargetGetSessionListByTarget`
- [ ] `RemoteRoboTargetGetSessionContainerCountByTarget`
- [ ] `RemoteRoboTargetGetShotJpg`

#### Event Handlers
- [ ] `RemoteRoboTargetSessionComplete` handler
- [ ] `RemoteRoboTargetSessionProgress` handler
- [ ] Broadcast WebSocket pour dashboard RoboTarget
- [ ] Enrichissement événements spécifiques RoboTarget

#### Validators
- [ ] Validation payloads RoboTarget
- [ ] Validation contraintes (C_Mask)
- [ ] Validation coordonnées (RA/DEC)

### Frontend - À FAIRE 🟡

#### Composants Alpine.js
- [ ] `subscriptionPicker` (choix plan)
- [ ] `targetPlanner` (configuration cibles)
- [ ] `catalogBrowser` (objets célestes)
- [ ] `shotConfigurator` (filtres/expositions)
- [ ] `constraintsEditor` (options selon plan)
- [ ] `costEstimator` (calcul temps réel)
- [ ] `dashboardRoboTarget` (suivi temps réel)

#### Vues Blade
- [ ] `subscriptions/choose.blade.php`
- [ ] `subscriptions/manage.blade.php`
- [ ] `target-planner.blade.php`
- [ ] `my-targets.blade.php`
- [ ] `dashboard-robotarget.blade.php`
- [ ] `target-detail.blade.php` (progression)

#### Intégration Sidebar Astrale
- [ ] Item "Target Planner" dans sidebar
- [ ] Item "Mes Cibles" dans sidebar
- [ ] Badge plan actif (Stardust/Nebula/Quasar)
- [ ] Indicateur crédits restants
- [ ] Notifications cibles terminées

#### WebSocket temps réel
- [ ] Connexion dashboard RoboTarget
- [ ] Écoute événements progression
- [ ] Mise à jour UI temps réel
- [ ] Notifications toast

### Intégration Stripe Billing - À FAIRE 🔴

#### Produits Stripe
- [ ] Créer produit "Stardust" (29€/mois)
- [ ] Créer produit "Nebula" (59€/mois)
- [ ] Créer produit "Quasar" (119€/mois)
- [ ] Configurer webhooks billing
- [ ] Tester en mode test

#### Webhooks
- [ ] `invoice.payment_succeeded` (renouvellement)
- [ ] `customer.subscription.created`
- [ ] `customer.subscription.updated`
- [ ] `customer.subscription.deleted`
- [ ] `invoice.payment_failed`

### Tests - À FAIRE 🟡

#### Tests unitaires
- [ ] PricingEngine (calculs)
- [ ] Subscription (permissions)
- [ ] RoboTarget (validations)
- [ ] PayloadBuilder (génération JSON)

#### Tests d'intégration
- [ ] Flow création cible complet
- [ ] Cycle Hold → Capture/Refund
- [ ] Webhooks Stripe
- [ ] Proxy ↔ Voyager communication

#### Tests end-to-end
- [ ] Parcours utilisateur complet
- [ ] Souscription → Cible → Résultat
- [ ] Dashboard temps réel
- [ ] Notifications

---

## 🚧 Phase 7 : Catalogue d'objets célestes - PLANIFIÉ

**Dépend de : Phase 6**

### Base de données
- [ ] Table `celestial_objects`
- [ ] Coordonnées J2000 (RA/DEC)
- [ ] Types (galaxie, nébuleuse, amas, etc.)
- [ ] Métadonnées (magnitude, taille, etc.)
- [ ] Images preview

### Import catalogues
- [ ] Messier (M1-M110)
- [ ] NGC (New General Catalogue)
- [ ] IC (Index Catalogue)
- [ ] Caldwell
- [ ] Intégration SIMBAD/NED (API)

### Interface utilisateur
- [ ] Recherche objets (nom, type)
- [ ] Filtres avancés
- [ ] Tri par visibilité ce soir
- [ ] Preview images DSS/SDSS
- [ ] Auto-fill coordonnées dans Target Planner

---

## 🚧 Phase 8 : Dashboard Analytics - PLANIFIÉ

**Dépend de : Phase 6**

### Métriques utilisateur
- [ ] Crédits consommés/période
- [ ] Nombre de cibles complétées
- [ ] Images capturées
- [ ] Temps total d'observation
- [ ] Objets favoris
- [ ] Graphiques mensuels

### Métriques admin
- [ ] MRR (Monthly Recurring Revenue)
- [ ] Répartition abonnements
- [ ] Taux de conversion
- [ ] Churn rate
- [ ] Crédits consommés vs alloués
- [ ] Objets les plus populaires
- [ ] Utilisation télescope (temps)

---

## 🚧 Phase 9 : Galerie utilisateur - PLANIFIÉ

**Dépend de : Phase 6**

### Gestion images
- [ ] Récupération automatique FITS
- [ ] Génération previews JPG
- [ ] Stockage organisé par cible
- [ ] Métadonnées FITS (HFD, StarIndex, etc.)
- [ ] Download pack complet

### Interface galerie
- [ ] Vue grille/liste
- [ ] Filtres par cible/date/filtre
- [ ] Lightbox avec zoom
- [ ] Affichage métadonnées
- [ ] Partage public (optionnel)
- [ ] Export ZIP

---

## 🚧 Phase 10 : Notifications avancées - PLANIFIÉ

**Dépend de : Phase 6**

### Email
- [ ] Cible créée (confirmation)
- [ ] Cible démarrée (RoboTarget commence)
- [ ] Cible complétée (images prêtes)
- [ ] Cible échouée (remboursement)
- [ ] Renouvellement abonnement
- [ ] Crédits faibles

### In-app
- [ ] Notifications temps réel
- [ ] Badge compteur
- [ ] Toast messages
- [ ] Centre de notifications

### Push (optionnel)
- [ ] Web Push API
- [ ] Notifications mobiles
- [ ] Préférences utilisateur

---

## 🚧 Phase 11 : Fonctionnalités avancées - BACKLOG

### Projets multi-nuits (Quasar)
- [ ] Planification plusieurs nuits
- [ ] Gestion Sets complexes
- [ ] Mosaïques automatiques
- [ ] Suivi progression multi-jours

### Collaboratif
- [ ] Partage de cibles entre utilisateurs
- [ ] Projets en équipe
- [ ] Chat intégré
- [ ] Galerie communautaire

### Intégrations externes
- [ ] API météo avancée
- [ ] Calendrier astronomique
- [ ] Stellarium Web (carte du ciel)
- [ ] Astrometry.net (plate-solving cloud)
- [ ] PixInsight / Siril (processing)

---

## 🚧 Phase 12 : Mobile et PWA - BACKLOG

### Progressive Web App
- [ ] Service Worker
- [ ] Manifest.json
- [ ] Installation sur écran d'accueil
- [ ] Mode hors ligne (lecture)
- [ ] Notifications push

### Optimisations mobile
- [ ] Touch gestures
- [ ] Menu mobile optimisé
- [ ] Formulaires adaptés
- [ ] Performance mobile (Lighthouse > 90)
- [ ] App native (React Native/Flutter) ?

---

## 🚧 Phase 13 : Sécurité et conformité - CONTINU

### Sécurité
- [x] Protection CSRF
- [x] Validation uploads
- [ ] Rate limiting API
- [ ] Scan antivirus uploads
- [ ] Protection DDoS (Cloudflare)
- [ ] Headers sécurité (CSP, HSTS)
- [ ] Audit sécurité externe
- [ ] Penetration testing

### Conformité
- [ ] RGPD complet
- [ ] CGU/CGV spécifiques abonnements
- [ ] Politique de confidentialité
- [ ] Cookies consent
- [ ] Droit à l'oubli
- [ ] Export données utilisateur
- [ ] Mentions légales

### Backup et disaster recovery
- [ ] Backup automatique quotidien
- [ ] Backup hors site (S3)
- [ ] Plan de reprise d'activité
- [ ] Tests de restauration mensuels
- [ ] Monitoring uptime (UptimeRobot)

---

## 📋 Backlog et idées futures

### IA et Machine Learning
- [ ] Suggestions de cibles optimales
- [ ] Prédiction qualité seeing
- [ ] Auto-stacking intelligent
- [ ] Détection objets dans images
- [ ] Recommandations personnalisées

### Gamification
- [ ] Badges de réalisation
- [ ] Niveaux utilisateur
- [ ] Leaderboard (images/temps)
- [ ] Challenges mensuels
- [ ] Programme de fidélité

### Marketplace
- [ ] Vente d'images traitées
- [ ] Services de traitement payants
- [ ] Mentoring astrophotographie
- [ ] Location matériel personnel

### Multi-sites
- [ ] Support plusieurs observatoires
- [ ] Choix site par utilisateur
- [ ] Comparaison conditions
- [ ] Load balancing automatique

---

## 🐛 Bugs connus et corrections

### À corriger
- [ ] Test `ExampleTest` échoue (route `/` redirige vers `/fr`)
- [ ] Optimiser chargement galerie sur mobile
- [ ] Vérifier intégrité soldes crédits (job planifié)

### Améliorations
- [ ] Cache Redis pour packages/subscriptions
- [ ] Queue pour webhooks longs
- [ ] Optimisation N+1 queries
- [ ] CDN pour médias

---

## 📝 Documentation

### ✅ Documentation technique créée (Phase 6)
- [x] MODELE-ROBOTARGET-OVERVIEW.md
- [x] CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md
- [x] README-NOUVEAU-MODELE.md
- [x] architecture-technique-voyager-proxy.md
- [x] astral_documentation.md
- [x] equipment-documentation.md

### 🚧 Documentation à créer
- [ ] IMPLEMENTATION-LARAVEL.md (guide complet)
- [ ] IMPLEMENTATION-PROXY.md (guide complet)
- [ ] IMPLEMENTATION-FRONTEND.md (guide complet)
- [ ] MIGRATION-GUIDE.md (utilisateurs existants)
- [ ] API-REFERENCE.md (OpenAPI/Swagger)
- [ ] DEPLOYMENT-GUIDE.md (production)

### Utilisateur
- [ ] Guide utilisateur complet
- [ ] FAQ
- [ ] Tutoriels vidéo
- [ ] Guide de démarrage rapide
- [ ] Comparatif abonnements

### Administrateur
- [ ] Guide administrateur
- [ ] Procédures de maintenance
- [ ] Gestion des incidents
- [ ] Formation admin
- [ ] Monitoring et alertes

---

## 🎯 Priorités court terme (Sprint actuel)

### 🔴 CRITIQUE - Phase 6 : Modèle RoboTarget

**1. Backend Laravel (2-3 semaines)**
   - Créer modèles (Subscription, RoboTarget, RoboTargetShot)
   - Créer migrations
   - Implémenter PricingEngine
   - Implémenter RoboTargetService
   - Routes API de base

**2. Proxy Node.js (1 semaine)**
   - Routes RoboTarget (/sets, /targets, /shots)
   - Commandes Voyager RoboTarget
   - Event handlers spécifiques

**3. Intégration Stripe Billing (1 semaine)**
   - Créer produits Stripe
   - Implémenter webhooks
   - Tester cycle complet

**4. Frontend MVP (2 semaines)**
   - Subscription picker
   - Target Planner basique
   - Dashboard RoboTarget minimal

### 🟡 IMPORTANT - Tests et validation

**5. Tests (1 semaine)**
   - Tests unitaires PricingEngine
   - Tests intégration Hold/Capture/Refund
   - Tests end-to-end parcours complet

**6. Documentation implémentation (parallèle)**
   - Guide Laravel détaillé
   - Guide Proxy détaillé
   - Guide Frontend détaillé

### 🟢 NICE TO HAVE - Améliorations

**7. Catalogue objets célestes (post-MVP)**
   - Import Messier
   - Recherche basique
   - Auto-fill coordonnées

**8. Galerie basique (post-MVP)**
   - Récupération FITS
   - Preview JPG
   - Download

---

## 📊 Métriques de succès

### Techniques
- Performance Lighthouse > 90
- Temps de réponse API < 200ms
- Disponibilité > 99.5%
- 0 vulnérabilités critiques
- Uptime Voyager > 95%

### Business (nouveau modèle)
- **MRR** (Monthly Recurring Revenue) : Objectif 10k€/mois
- **Churn rate** < 5%
- **Taux de conversion** visite → abonnement > 10%
- **LTV/CAC** > 3
- **Satisfaction utilisateur** > 4.5/5

### Fonctionnelles
- Temps moyen création cible < 3 minutes
- Taux de succès cibles > 85%
- Taux de remboursement < 10%
- Images capturées/mois > 1000

---

## 🤝 Contribution

Pour contribuer au projet :

1. **Lire la documentation**
   - `README-NOUVEAU-MODELE.md` (vue d'ensemble)
   - Documentation spécifique à votre rôle

2. **Créer une branche feature**
   ```bash
   git checkout -b feature/robotarget-xxx
   ```

3. **Suivre les conventions**
   - Code style Laravel/Node.js
   - Commits conventionnels
   - Tests obligatoires

4. **Créer une PR**
   - Description détaillée
   - Lien vers issue
   - Screenshots si UI

---

## 📈 Timeline estimée

```
Décembre 2025 : 📚 Documentation complète (✅ FAIT)
Janvier 2026 :  💻 Développement Backend + Proxy
Février 2026 :  🎨 Développement Frontend + Intégration
Mars 2026 :     🧪 Tests + Corrections + Optimisations
Avril 2026 :    🚀 Déploiement production + Migration utilisateurs
Mai 2026 :      📊 Monitoring + Analytics + Améliorations
```

---

## 🎓 Ressources

### Documentation interne
- [Vue d'ensemble RoboTarget](./README-NOUVEAU-MODELE.md)
- [Modèle RoboTarget complet](./MODELE-ROBOTARGET-OVERVIEW.md)
- [Système de crédits v2](./CREDIT-SYSTEM-V2-SUBSCRIPTIONS.md)
- [Architecture Voyager Proxy](./architecture-technique-voyager-proxy.md)
- [Design System Astral](./astral_documentation.md)

### Documentation externe
- [Voyager RoboTarget API](./doc_voyager/Voyager%20RoboTarget%20Reserved%20API.md)
- [Spécifications techniques](./doc_voyager/📑%20Spécification%20Technique.md)
- [Laravel 12 Documentation](https://laravel.com/docs/12.x)
- [Stripe Billing Documentation](https://stripe.com/docs/billing)
- [Alpine.js Documentation](https://alpinejs.dev/)

---

**Légende :**
- ✅ Terminé et testé
- 🔄 En cours de développement
- 🚧 Planifié, non commencé
- ❌ Obsolète / Abandonné
- [ ] À faire
- [x] Fait

---

**Questions ? Besoin de clarifications ?**

Consulter `README-NOUVEAU-MODELE.md` ou contacter l'équipe produit.

---

*Document vivant - Mis à jour régulièrement par l'équipe*

*Dernière modification majeure : 12 Décembre 2025 (Transition RoboTarget)*
