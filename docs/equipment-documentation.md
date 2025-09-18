# 🔭 Documentation - Système de Gestion du Matériel d'Observation

## Table des Matières
1. [Vue d'ensemble](#vue-densemble)
2. [Architecture du système](#architecture-du-système)
3. [Installation et configuration](#installation-et-configuration)
4. [Fonctionnalités](#fonctionnalités)
5. [Interface administrateur](#interface-administrateur)
6. [Interface utilisateur](#interface-utilisateur)
7. [API et intégrations](#api-et-intégrations)
8. [Maintenance et évolution](#maintenance-et-évolution)

---

## Vue d'ensemble

Le système de gestion du matériel d'observation de **STELLARLOC** permet la gestion complète des équipements astronomiques disponibles pour les utilisateurs. Il intègre :

- **Gestion administrative** complète du matériel
- **Système de réservation** avec crédits
- **Interface utilisateur** moderne et intuitive
- **Gestion des médias** (images, vidéos)
- **Spécifications techniques** détaillées

### 🎯 Objectifs
- Centraliser la gestion de tous les équipements d'observation
- Permettre aux utilisateurs de découvrir et réserver du matériel
- Automatiser la tarification et la disponibilité
- Offrir une expérience utilisateur premium

---

## Architecture du Système

### 🗄️ Base de Données

```sql
-- Table principale du matériel
CREATE TABLE equipment (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    type ENUM('telescope', 'mount', 'camera', 'accessory', 'complete_setup') NOT NULL,
    status ENUM('available', 'unavailable', 'maintenance', 'reserved') NOT NULL,
    location VARCHAR(255) NULL,
    specifications JSON NULL,
    images JSON NULL,
    videos JSON NULL,
    price_per_hour_credits INT UNSIGNED NULL,
    is_featured BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

### 📁 Structure des Fichiers

```
app/
├── Models/
│   └── Equipment.php                    # Modèle principal
├── Http/Controllers/
│   ├── Admin/
│   │   └── EquipmentController.php      # Contrôleur admin
│   └── EquipmentController.php          # Contrôleur public
resources/views/
├── admin/equipment/
│   ├── index.blade.php                  # Liste admin
│   ├── create.blade.php                 # Création
│   ├── edit.blade.php                   # Modification
│   └── show.blade.php                   # Détails admin
└── equipment/
    ├── index.blade.php                  # Catalogue public
    └── show.blade.php                   # Détails public
storage/app/public/equipment/
├── images/                              # Images des équipements
└── videos/                              # Vidéos des équipements
```

---

## Installation et Configuration

### 1. 📦 Installation de Base

```bash
# Création des dossiers nécessaires
mkdir -p resources/views/admin/equipment
mkdir -p storage/app/public/equipment/{images,videos}

# Lien symbolique pour le storage
php artisan storage:link
```

### 2. 🗃️ Base de Données

```bash
# Exécuter le SQL de création de table
mysql -u username -p database_name < equipment_table.sql
```

### 3. 📄 Fichiers à Créer

1. **Modèle** : `app/Models/Equipment.php`
2. **Contrôleur Admin** : `app/Http/Controllers/Admin/EquipmentController.php`
3. **Vues Admin** : Toutes les vues dans `resources/views/admin/equipment/`

### 4. 🛣️ Routes

Ajouter dans `routes/web.php` :

```php
// Routes admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('equipment', App\Http\Controllers\Admin\EquipmentController::class);
    Route::post('equipment/{equipment}/toggle-status', [App\Http\Controllers\Admin\EquipmentController::class, 'toggleStatus'])->name('equipment.toggle-status');
    Route::post('equipment/{equipment}/toggle-active', [App\Http\Controllers\Admin\EquipmentController::class, 'toggleActive'])->name('equipment.toggle-active');
    Route::post('equipment/{equipment}/toggle-featured', [App\Http\Controllers\Admin\EquipmentController::class, 'toggleFeatured'])->name('equipment.toggle-featured');
});
```

---

## Fonctionnalités

### 🔧 Types d'Équipements

| Type | Description | Exemple |
|------|-------------|---------|
| `telescope` | Tubes optiques | Takahashi TOA-150B |
| `mount` | Montures | 10Micron GM2000 HPS |
| `camera` | Caméras d'acquisition | ZWO ASI6400MM Pro |
| `accessory` | Accessoires | Filtres, focuser, etc. |
| `complete_setup` | Setup complet | Installation complète prête à l'usage |

### 📊 Statuts Disponibles

| Statut | Description | Badge |
|--------|-------------|-------|
| `available` | Disponible à la réservation | 🟢 Vert |
| `unavailable` | Temporairement indisponible | 🔴 Rouge |
| `maintenance` | En maintenance | 🟠 Orange |
| `reserved` | Actuellement réservé | 🔵 Bleu |

### 💰 Système de Prix

- **Tarification horaire** en crédits
- **Prix flexible** par équipement
- **Gratuité possible** (prix = 0)
- **Calculs automatiques** de coûts

### 📸 Gestion des Médias

#### Images
- **Formats supportés** : JPG, PNG, GIF, WebP
- **Taille maximale** : 5MB par image
- **Stockage** : `storage/app/public/equipment/images/`
- **Affichage** : Galerie avec modal de zoom

#### Vidéos
- **Formats supportés** : MP4, MOV, AVI, WMV
- **Taille maximale** : 50MB par vidéo
- **Stockage** : `storage/app/public/equipment/videos/`
- **Lecture** : Player HTML5 intégré

### 🛠️ Spécifications Techniques

Stockage flexible au format JSON permettant :
- **Spécifications dynamiques** (clé-valeur)
- **Structure adaptable** selon le type d'équipement
- **Affichage formaté** automatique

Exemple pour un setup complet :
```json
{
    "mount": "10Micron GM2000 HPS",
    "telescope": "Takahashi TOA-150B",
    "main_camera": "ZWO ASI6400MM Pro (mono, refroidie)",
    "filters": ["Chroma LRGB", "SHO 3 nm"],
    "guide_camera": "ZWO ASI174",
    "focuser": "Primaluce Lab Esatto 4\"",
    "software": ["Voyager Advanced", "RoboTarget"]
}
```

---

## Interface Administrateur

### 📋 Page d'Index (`/admin/equipment`)

#### Fonctionnalités
- **Liste paginée** de tous les équipements
- **Filtres avancés** : type, statut, recherche textuelle
- **Statistiques en temps réel** : total, disponibles, maintenance, vedettes
- **Actions rapides** : voir, éditer, basculer statut, supprimer
- **Tri** par ordre personnalisé et nom

#### Colonnes Affichées
- Image miniature
- Nom et description (avec badges)
- Type d'équipement
- Statut actuel
- Localisation
- Prix par heure
- Actions disponibles

### ➕ Création d'Équipement (`/admin/equipment/create`)

#### Sections du Formulaire

1. **Informations de Base**
   - Nom (obligatoire)
   - Type d'équipement
   - Statut initial
   - Localisation
   - Prix par heure
   - Description

2. **Spécifications Techniques**
   - Interface dynamique clé-valeur
   - Bouton d'ajout de lignes
   - Présets selon le type d'équipement

3. **Médias**
   - Upload multiple d'images
   - Upload multiple de vidéos
   - Validation côté client

4. **Options**
   - Ordre de tri
   - Équipement vedette
   - Équipement actif

### 👁️ Détails d'Équipement (`/admin/equipment/{id}`)

#### Sections d'Affichage

1. **En-tête**
   - Nom et badges de statut
   - Actions rapides (toggle statut, vedette, actif)
   - Bouton de modification

2. **Informations Générales**
   - Tous les détails de l'équipement
   - Dates de création/modification
   - Statut et configuration

3. **Spécifications Techniques**
   - Affichage formaté des spécifications
   - Organisation par sections

4. **Médias**
   - Galerie d'images avec modal de zoom
   - Lecteur vidéo intégré
   - Compteurs de médias

5. **Actions**
   - Modification
   - Suppression avec confirmation

### ✏️ Modification d'Équipement (`/admin/equipment/{id}/edit`)

#### Fonctionnalités Avancées
- **Édition en place** des spécifications existantes
- **Gestion sélective** des médias :
  - Cases à cocher pour supprimer
  - Upload de nouveaux fichiers
  - Préservation des existants
- **Aperçu en temps réel** des modifications
- **Validation** côté client et serveur

---

## Interface Utilisateur

### 🏪 Catalogue Public (`/equipment`)

*À développer prochainement*

#### Fonctionnalités Prévues
- **Affichage des équipements actifs** uniquement
- **Filtrage par type** et disponibilité
- **Recherche** par mots-clés
- **Tri** par prix, popularité, nouveauté
- **Cartes d'équipement** avec images principales

### 🔍 Détails Équipement (`/equipment/{id}`)

*À développer prochainement*

#### Fonctionnalités Prévues
- **Galerie complète** d'images et vidéos
- **Spécifications détaillées**
- **Calendrier de disponibilité**
- **Système de réservation** intégré
- **Calcul automatique** des coûts
- **Bouton de réservation** (si crédits suffisants)

---

## API et Intégrations

### 🔗 Endpoints API (À Développer)

```php
// Routes API publiques
GET    /api/equipment              // Liste des équipements actifs
GET    /api/equipment/{id}         // Détails d'un équipement
GET    /api/equipment/featured     // Équipements vedettes
GET    /api/equipment/available    // Équipements disponibles

// Routes API authentifiées
POST   /api/equipment/{id}/reserve // Réserver un équipement
GET    /api/user/reservations      // Réservations utilisateur
```

### 📊 Intégrations Possibles

#### Système de Réservation
```php
// Exemple d'intégration avec le système de crédits
class EquipmentReservation {
    public function reserve(Equipment $equipment, User $user, $hours) {
        $cost = $equipment->price_per_hour_credits * $hours;
        
        if ($user->credits_balance >= $cost) {
            // Créer réservation
            // Débiter crédits
            // Envoyer notifications
        }
    }
}
```

#### Notifications
- **Email** : Confirmation de réservation
- **Dashboard** : Alertes de disponibilité
- **Admin** : Notifications de nouvelle réservation

---

## Maintenance et Évolution

### 🔧 Tâches de Maintenance

#### Quotidienne
- **Vérification** des statuts d'équipement
- **Nettoyage** des fichiers temporaires
- **Sauvegarde** des images/vidéos

#### Hebdomadaire
- **Optimisation** de la base de données
- **Analyse** des statistiques d'utilisation
- **Vérification** de l'espace de stockage

#### Mensuelle
- **Archivage** des anciennes réservations
- **Mise à jour** des prix si nécessaire
- **Audit** des performances

### 📈 Évolutions Prévues

#### Phase 2 : Interface Utilisateur
- [ ] Catalogue public responsive
- [ ] Système de recherche avancée
- [ ] Comparateur d'équipements
- [ ] Wishlist utilisateur

#### Phase 3 : Réservations
- [ ] Calendrier de réservation
- [ ] Système de créneaux horaires
- [ ] Gestion des conflits
- [ ] Annulation/modification de réservations

#### Phase 4 : Analytics
- [ ] Tableau de bord statistiques
- [ ] Rapport d'utilisation
- [ ] Analyse de rentabilité
- [ ] Suggestions d'optimisation

#### Phase 5 : Automations
- [ ] Synchronisation avec des observatoires
- [ ] API météo pour disponibilité
- [ ] Maintenance prédictive
- [ ] Notifications intelligentes

### 🛡️ Sécurité

#### Mesures Implementées
- **Validation** stricte des uploads
- **Authentification** obligatoire pour l'admin
- **Sanitisation** des données JSON
- **Protection CSRF** sur tous les formulaires

#### Mesures Recommandées
- **Limitation de taux** sur l'upload
- **Scan antivirus** des fichiers uploadés
- **Audit logs** des modifications admin
- **Backup automatique** des données critiques

---

## 📞 Support et Contact

### 🐛 Signalement de Bugs
- Interface de support intégrée
- Issues GitHub pour les développeurs
- Documentation des erreurs communes

### 📚 Formation
- Guide administrateur
- Tutoriels vidéo (à venir)
- FAQ utilisateur

### 🔄 Mises à Jour
- Versioning sémantique
- Notes de version détaillées
- Migration automatique des données

---

*Dernière mise à jour : Version 1.0.0 - Documentation générée automatiquement*