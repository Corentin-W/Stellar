# 🚀 STELLAR - Roadmap du Projet

> Dernière mise à jour : 18 novembre 2024

## 📊 Vue d'ensemble

**Stellar** (TelescopeApp / STELLARLOC) est une plateforme de gestion et location d'équipement astronomique avec contrôle distant des télescopes.

### Progression globale

```
████████████████░░░░  75% - Projet en phase avancée
```

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
- [x] Table credit_packages
- [x] Table promotions
- [x] Table credit_transactions
- [x] Table equipment_bookings
- [x] Relations et index optimisés

---

## ✅ Phase 2 : Gestion d'équipement - TERMINÉ

### Interface administrateur
- [x] CRUD complet des équipements
- [x] Upload multiple d'images
- [x] Upload multiple de vidéos
- [x] Spécifications techniques JSON dynamiques
- [x] Gestion des statuts (available, unavailable, maintenance, reserved)
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

## ✅ Phase 3 : Système de crédits - TERMINÉ

### Intégration Stripe Cashier
- [x] Installation et configuration Cashier
- [x] Création de Payment Intents
- [x] Gestion des webhooks sécurisés
- [x] Confirmation automatique des paiements
- [x] Support multi-devises (EUR par défaut)

### Packages de crédits
- [x] Création de packages configurables
- [x] Prix en centimes
- [x] Crédits bonus
- [x] Réduction en pourcentage
- [x] Packages vedettes (featured)
- [x] Synchronisation prix Stripe

### Système de promotions
- [x] Codes promotionnels uniques
- [x] Types : pourcentage, montant fixe, bonus crédits
- [x] Limites d'utilisation globales
- [x] Limites par utilisateur
- [x] Dates de validité (starts_at, expires_at)
- [x] Packages applicables configurables
- [x] Montant minimum d'achat

### Gestion des transactions
- [x] Trait HasCredits pour User
- [x] Historique complet des transactions
- [x] Types : purchase, usage, refund, bonus, admin_adjustment
- [x] Balance avant/après chaque transaction
- [x] Métadonnées JSON
- [x] Référence vers objets liés

### Interface utilisateur
- [x] Boutique de crédits (shop)
- [x] Validation de codes promo en temps réel
- [x] Historique des transactions
- [x] Page de succès post-achat
- [x] Affichage du solde

### Interface admin
- [x] Dashboard administrateur
- [x] Gestion des packages
- [x] Gestion des promotions
- [x] Vue des utilisateurs et soldes
- [x] Ajustements manuels de crédits
- [x] Statistiques de ventes

---

## ✅ Phase 4 : Système de réservation - TERMINÉ

### Modèle de réservation
- [x] Table equipment_bookings
- [x] Calcul d'états d'accès (pending, upcoming, active, finished, cancelled)
- [x] Gestion des créneaux horaires
- [x] Validation des conflits de réservation
- [x] Calcul automatique des coûts en crédits

### Contrôle d'accès temporel
- [x] Page d'accès dédiée par réservation
- [x] Compte à rebours avant démarrage
- [x] Minuterie pendant la session active
- [x] Déverrouillage automatique au début du créneau
- [x] Verrouillage automatique à la fin
- [x] Rafraîchissement auto de la page

### Sécurité des réservations
- [x] Vérification propriétaire (user_id)
- [x] Statuts bloquants (rejected, cancelled)
- [x] États selon confirmation
- [x] Messages d'erreur 403 pour accès refusé

### Interface utilisateur
- [x] Page "Mes réservations"
- [x] Boutons contextuels selon l'état
- [x] Affichage des consignes d'utilisation
- [x] Informations de localisation
- [x] Résumé post-session

---

## 🔄 Phase 5 : Intégration Voyager - EN COURS

### Documentation
- [x] Documentation Voyager Event Methods
- [x] Documentation RoboTarget JSON-RCP
- [x] PDF VoyagerAS
- [x] PDF Voyager RoboTarget Reserved API

### À implémenter
- [ ] Service de connexion Voyager
- [ ] Contrôles télescope depuis l'interface
- [ ] Récupération du statut en temps réel
- [ ] Gestion des événements Voyager
- [ ] Interface de pilotage dans la page d'accès
- [ ] Logs des commandes envoyées
- [ ] Gestion des erreurs de connexion

---

## 🚧 Phase 6 : Interface publique - À FAIRE

### Catalogue d'équipement public
- [ ] Page d'index publique `/equipment`
- [ ] Filtrage par type d'équipement
- [ ] Filtrage par disponibilité
- [ ] Recherche par mots-clés
- [ ] Tri (prix, popularité, nouveauté)
- [ ] Cartes d'équipement avec image principale
- [ ] Pagination optimisée

### Page détails équipement
- [ ] Vue détaillée publique `/equipment/{id}`
- [ ] Galerie complète images/vidéos
- [ ] Spécifications techniques formatées
- [ ] Calendrier de disponibilité
- [ ] Calcul automatique de coût
- [ ] Bouton de réservation (si connecté)
- [ ] Vérification crédits suffisants
- [ ] Modal de confirmation

### Système de réservation utilisateur
- [ ] Formulaire de réservation
- [ ] Sélection de créneaux horaires
- [ ] Validation des conflits en temps réel
- [ ] Déduction automatique des crédits
- [ ] Confirmation par email
- [ ] Récapitulatif de réservation

### Wishlist
- [ ] Ajout aux favoris
- [ ] Page de wishlist utilisateur
- [ ] Notifications de disponibilité

---

## 🚧 Phase 7 : Fonctionnalités avancées - À FAIRE

### Notifications
- [ ] Système de notifications in-app
- [ ] Email de confirmation de réservation
- [ ] Rappel avant début de session
- [ ] Alerte fin de session imminente
- [ ] Notification nouveaux équipements
- [ ] Alertes promotionnelles

### Comparateur d'équipements
- [ ] Interface de sélection multiple
- [ ] Tableau comparatif
- [ ] Filtres de comparaison
- [ ] Export PDF/PNG

### Recherche avancée
- [ ] Filtres multiples combinés
- [ ] Recherche par spécifications
- [ ] Recherche par gamme de prix
- [ ] Sauvegarde de recherches

### Système d'évaluation
- [ ] Notes et avis utilisateurs
- [ ] Modération des avis
- [ ] Affichage moyenne et détails
- [ ] Photos utilisateurs dans avis

---

## 🚧 Phase 8 : API et intégrations - À FAIRE

### API RESTful publique
- [ ] `GET /api/equipment` - Liste équipements actifs
- [ ] `GET /api/equipment/{id}` - Détails équipement
- [ ] `GET /api/equipment/featured` - Équipements vedettes
- [ ] `GET /api/equipment/available` - Disponibilités
- [ ] Documentation OpenAPI/Swagger

### API authentifiée
- [ ] `POST /api/equipment/{id}/reserve` - Réserver
- [ ] `GET /api/user/reservations` - Mes réservations
- [ ] `PUT /api/reservations/{id}` - Modifier réservation
- [ ] `DELETE /api/reservations/{id}` - Annuler réservation
- [ ] Tokens API personnels

### Intégrations externes
- [ ] API météo (OpenWeather, etc.)
- [ ] Calendrier astronomique
- [ ] Phases lunaires
- [ ] Éphémérides
- [ ] Carte du ciel (Stellarium Web)

---

## 🚧 Phase 9 : Analytics et reporting - À FAIRE

### Dashboard statistiques
- [ ] Tableau de bord analytics admin
- [ ] Graphiques d'utilisation
- [ ] Revenus par période
- [ ] Équipements les plus réservés
- [ ] Taux de conversion
- [ ] Durée moyenne de session

### Rapports
- [ ] Rapport mensuel automatique
- [ ] Export Excel/CSV
- [ ] Rapport d'utilisation par équipement
- [ ] Analyse de rentabilité
- [ ] Suggestions d'optimisation tarifaire

### Logs et audit
- [ ] Audit trail complet
- [ ] Logs des modifications admin
- [ ] Historique des prix
- [ ] Traçabilité des ajustements de crédits

---

## 🚧 Phase 10 : Automations et optimisations - À FAIRE

### Automations
- [ ] Passage auto de `active` à `completed`
- [ ] Libération auto des créneaux expirés
- [ ] Envoi auto d'emails de rappel
- [ ] Mise en maintenance auto (calendrier)
- [ ] Archivage auto des anciennes réservations

### Performance
- [ ] Cache des packages actifs
- [ ] Cache des équipements vedettes
- [ ] Lazy loading des images
- [ ] CDN pour les médias
- [ ] Optimisation des requêtes N+1
- [ ] Queue pour webhooks longs

### Maintenance prédictive
- [ ] Alertes heures d'utilisation
- [ ] Planification maintenance
- [ ] Historique maintenance
- [ ] Coûts de maintenance

---

## 🚧 Phase 11 : Mobile et PWA - À FAIRE

### Progressive Web App
- [ ] Service Worker
- [ ] Manifest.json
- [ ] Installation sur écran d'accueil
- [ ] Mode hors ligne basique
- [ ] Notifications push

### Optimisations mobile
- [ ] Touch gestures
- [ ] Menu mobile optimisé
- [ ] Formulaires adaptés mobile
- [ ] Performance mobile (Lighthouse > 90)

---

## 🚧 Phase 12 : Sécurité et conformité - À RENFORCER

### Sécurité
- [x] Protection CSRF
- [x] Validation uploads
- [ ] Limitation de taux (rate limiting)
- [ ] Scan antivirus fichiers uploadés
- [ ] Protection DDoS
- [ ] Headers de sécurité (CSP, etc.)
- [ ] Audit sécurité externe

### Conformité
- [ ] RGPD complet
- [ ] CGU/CGV
- [ ] Politique de confidentialité
- [ ] Cookies consent
- [ ] Droit à l'oubli
- [ ] Export données utilisateur

### Backup et disaster recovery
- [ ] Backup automatique quotidien
- [ ] Backup hors site
- [ ] Plan de reprise d'activité
- [ ] Tests de restauration

---

## 📋 Backlog et idées futures

### Fonctionnalités communautaires
- [ ] Forum utilisateurs
- [ ] Partage de photos/observations
- [ ] Galerie communautaire
- [ ] Challenges mensuels

### Gamification
- [ ] Badges de réalisation
- [ ] Niveaux utilisateur
- [ ] Programme de fidélité
- [ ] Parrainage avec bonus

### Marketplace
- [ ] Vente d'images
- [ ] Abonnements mensuels
- [ ] Packs "tout inclus"
- [ ] Services de traitement d'images

### Intégrations avancées
- [ ] Observatoires partenaires
- [ ] Réservation multi-sites
- [ ] Session partagée (éducation)
- [ ] Live streaming des observations

---

## 🐛 Bugs connus et corrections

### À corriger
- [ ] Test `ExampleTest` échoue (route `/` redirige vers `/fr`)
- [ ] Vérifier intégrité des soldes de crédits périodiquement
- [ ] Optimiser chargement galerie sur mobile

---

## 📝 Documentation à compléter

### Technique
- [x] Frontend (CSS & JS)
- [x] Système de crédits
- [x] Gestion d'équipement
- [x] Système de réservation
- [x] Thème astral
- [x] Intégration Voyager (docs externes)
- [ ] Guide d'installation complet
- [ ] Guide de déploiement
- [ ] Guide de contribution

### Utilisateur
- [ ] Guide utilisateur complet
- [ ] FAQ
- [ ] Tutoriels vidéo
- [ ] Guide de démarrage rapide

### Administrateur
- [ ] Guide administrateur
- [ ] Procédures de maintenance
- [ ] Gestion des incidents
- [ ] Formation admin

---

## 🎯 Priorités court terme (Next Sprint)

1. **Finaliser intégration Voyager** (Phase 5)
   - Service de connexion
   - Interface de pilotage basique
   - Récupération statut télescope

2. **Catalogue public** (Phase 6 - partie 1)
   - Page d'index publique
   - Filtres de base
   - Cartes d'équipement

3. **Page détails publique** (Phase 6 - partie 2)
   - Vue détaillée équipement
   - Calendrier de disponibilité
   - Bouton de réservation

4. **Corrections et optimisations**
   - Fixer test ExampleTest
   - Cache des packages
   - Optimisation images

---

## 📊 Métriques de succès

### Techniques
- Performance Lighthouse > 90
- Temps de réponse < 200ms
- Disponibilité > 99.5%
- 0 vulnérabilités critiques

### Fonctionnelles
- Taux de conversion visite → réservation > 15%
- Satisfaction utilisateur > 4.5/5
- Temps moyen de réservation < 3 minutes
- Taux de complétion des sessions > 95%

---

## 🤝 Contribution

Pour contribuer au projet :
1. Consulter cette roadmap
2. Vérifier les issues GitHub
3. Discuter en équipe avant de commencer
4. Suivre les conventions du projet
5. Créer une PR avec description détaillée

---

**Légende :**
- ✅ Terminé et testé
- 🔄 En cours de développement
- 🚧 Planifié, non commencé
- [ ] À faire
- [x] Fait

*Document vivant - Mis à jour régulièrement par l'équipe*
