# 🔭 Voyager Control Panel - Guide Complet

Documentation complète du panneau de contrôle Voyager intégré dans l'application web.

---

## 📋 Vue d'ensemble

Le **Voyager Control Panel** est une interface web complète permettant de gérer et contrôler Voyager à distance. Il permet de visualiser et manipuler :

- **Sets** : Collections de cibles d'observation
- **Targets** : Cibles individuelles avec coordonnées RA/DEC
- **Shots** : Plans d'acquisition (filtres, expositions, progression)
- **Configuration Matérielle** : Filtres, modes de lecture, vitesses, profils

---

## 🚀 Accès au Panel

**URL** : `https://stellar.test/fr/admin/robotarget/sets`

**Prérequis** :
- Authentification en tant qu'administrateur
- Voyager Proxy en cours d'exécution (`http://localhost:3003`)
- Voyager connecté au proxy

---

## 🎯 Fonctionnalités Principales

### 1. Gestion des Sets

#### Affichage
- **Tableau complet** avec colonnes : Nom, Profil, Tag, Statut, Défaut
- **Indicateurs visuels** : Badge vert/rouge pour le statut
- **Étoile jaune** ⭐ pour le Set par défaut
- **GUID affiché** en gris sous chaque nom

#### Filtres et Recherche
- **Barre de recherche** : Recherche dans nom, tag et profil
- **Filtre par statut** : Tous / Actifs / Inactifs
- **Filtre par profil** : Dropdown avec tous les profils disponibles

#### Actions sur les Sets
```
🎯 Targets    - Voir les targets du Set
👁️ Voir       - Afficher les détails complets
✏️ Modifier   - Éditer les propriétés
🔒 Désactiver - Toggle actif/inactif
🗑️ Supprimer - Supprimer le Set
```

#### Création de Set
```php
Champs requis:
- Nom du Set *
- Profil Voyager *

Champs optionnels:
- Tag
- Statut (Actif/Inactif)
- Set par défaut (checkbox)
- Note
```

---

### 2. Gestion des Targets

#### Affichage
Accessible via le bouton **🎯 Targets** sur chaque Set.

**Modal affichant** :
- Nom de la target
- Coordonnées RA/DEC
- Rotation PA
- Statut actif/inactif

#### Actions
```
📸 Voir Shots - Afficher le plan d'acquisition
```

---

### 3. Visualisation des Shots

#### Affichage
Accessible via le bouton **📸 Voir Shots** sur chaque Target.

**Tableau d'acquisition** avec colonnes :
- **Filtre** : Nom du filtre (L-Chroma, Ha, OIII, etc.)
- **Exposition** : Durée formatée (5m 30s)
- **Quantité** : Nombre de poses (ex: 20x)
- **Binning** : Mode de binning (ex: 1x1, 2x2)
- **Gain** : Valeur de gain ou "-"
- **Progression** : Barre de progression visuelle + compteur (10/20)

**Fonctionnalités** :
- **Noms de filtres intelligents** : Mapping automatique filterindex → nom
- **Format d'exposition** : Conversion secondes → minutes/heures
- **Barre de progression** :
  - Vert : Images acceptées
  - Gris : Reste à faire
  - Pourcentage calculé : `(auxshotdone / auxtotshot) * 100`

#### Code JavaScript
```javascript
getFilterName(filterIndex) {
    // Retourne le nom du filtre depuis filterConfig
    // Gère les objets {name, offset} et les strings simples
}

formatExposure(seconds) {
    // Convertit 330s → "5m 30s"
    // Convertit 3600s → "1h"
}
```

---

### 4. Configuration Matérielle ⚙️

#### Accès
Bouton **⚙️ Configuration** dans le header du Control Panel.

#### Contenu du Modal

##### 📋 Profil Actif
```
- Nom du profil : 2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y
- Type de capteur : Monochrome
- Technologie : CMOS / CCD
```

##### 🎨 Filtres Configurés
Pour chaque filtre :
```
#0  L-Chroma        Offset: 0    Magnitude: 5 - 7
#1  H-Chroma-3nm    Offset: 0    Magnitude: 2 - 4
```

**Affichage** :
- Badge bleu indigo avec l'index (#0, #1...)
- Nom du filtre en blanc
- Offset et magnitude en gris

##### 📖 Modes de Lecture
```
#0 Default
#1 16 bit
#2 12 bit Low Noise
```

Grille 3 colonnes avec badges violets.

##### ⚡ Vitesses de Téléchargement
```
#0 Default
#1 Fast
#2 Slow
```

Grille 3 colonnes avec badges verts.

##### 📚 Tous les Profils Disponibles
Liste des profils avec :
- ✅ Indicateur du profil actif
- Surbrillance verte pour le profil actif
- Compteurs : Type, Nb filtres, Nb modes

---

## 🔌 API Backend

### Endpoints Utilisés

```http
# Sets
GET  /admin/robotarget/api/sets
POST /admin/robotarget/api/sets
PUT  /admin/robotarget/api/sets/{guid}
DELETE /admin/robotarget/api/sets/{guid}
POST /admin/robotarget/api/sets/{guid}/toggle

# Targets
GET /admin/robotarget/api/sets/{setGuid}/targets

# Shots
GET /admin/robotarget/api/targets/{targetGuid}/shots
GET /admin/robotarget/api/targets/{targetGuid}/shots-done

# Configuration
GET /admin/robotarget/api/config/hardware
GET /admin/robotarget/api/config/filters
GET /admin/robotarget/api/config/profiles
```

### Exemple de Requête

```javascript
// Charger la configuration matérielle
const response = await fetch('/admin/robotarget/api/config/hardware', {
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});

const data = await response.json();

if (data.success) {
    const activeProfile = data.parsed.activeProfile;
    const filters = activeProfile.filters;
    const readoutModes = activeProfile.readoutModes;
}
```

---

## 💻 Architecture Technique

### Frontend (Alpine.js)

#### État du Composant
```javascript
{
    // Données
    sets: [],                           // Liste des Sets
    connected: false,                   // État connexion Voyager
    hardwareConfig: null,               // Config matérielle
    filterConfig: null,                 // Filtres (simple)

    // Modals
    showModal: false,                   // Modal création/édition Set
    showTargetsModal: false,            // Modal liste Targets
    showShotsModal: false,              // Modal plan acquisition
    showHardwareConfigModal: false,     // Modal config matérielle

    // Loading
    loading: false,                     // Chargement Sets
    loadingTargets: false,              // Chargement Targets
    loadingShots: false,                // Chargement Shots
    loadingHardwareConfig: false,       // Chargement config

    // Sélections
    selectedSet: null,                  // Set sélectionné
    selectedTarget: null,               // Target sélectionnée
    currentSetTargets: [],              // Targets du Set courant
    currentTargetShots: [],             // Shots de la Target courante

    // Filtres
    searchQuery: '',                    // Recherche textuelle
    filterStatus: 'all',                // Filtre par statut
    filterProfile: '',                  // Filtre par profil
}
```

#### Méthodes Principales
```javascript
// Initialisation
async init()
async loadFilterConfig()

// Sets
async refreshSets()
async viewTargets(set)
openCreateModal()
async saveSet()
async deleteSet(set)
async toggleSet(set)

// Targets & Shots
async viewShots(target)
closeShotsModal()

// Configuration Matérielle
async viewHardwareConfig()
closeHardwareConfigModal()

// Utilitaires
getFilterName(filterIndex)
formatExposure(seconds)
```

---

### Backend (Laravel)

#### Services

**RoboTargetSetService** (`app/Services/RoboTargetSetService.php`)
- Gestion des Sets et Targets
- Méthodes : `getSets()`, `addSet()`, `updateSet()`, `deleteSet()`, `getTargets()`

**RoboTargetShotService** (`app/Services/RoboTargetShotService.php`)
- Gestion des Shots et configuration matérielle
- Méthodes :
  - `getPlannedShots($targetGuid)`
  - `getCapturedShots($targetGuid)`
  - `getHardwareConfiguration($profileName)`
  - `getFilterConfiguration()`
  - `getAllProfiles()`

#### Controllers

**RoboTargetAdminController** (`app/Http/Controllers/Admin/RoboTargetAdminController.php`)
- Routes pour Sets et Targets

**RoboTargetShotController** (`app/Http/Controllers/Admin/RoboTargetShotController.php`)
- Routes pour Shots et configuration

---

## 📊 Format des Données

### Profil de Configuration
```json
{
  "guid": "xxx-xxx-xxx",
  "name": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
  "isActive": true,
  "sensorType": "Monochrome",
  "sensorTypeCode": 0,
  "isCmos": true,
  "filters": [
    {
      "index": 0,
      "name": "L-Chroma",
      "offset": 0,
      "magMin": 5,
      "magMax": 7
    }
  ],
  "readoutModes": [
    {
      "name": "Default",
      "index": 0
    }
  ],
  "speeds": [
    {
      "name": "Default",
      "index": 0
    }
  ]
}
```

### Shot Planifié
```json
{
  "guid": "xxx-xxx-xxx",
  "filterindex": 0,
  "exposure": 300,
  "num": 20,
  "bin": 1,
  "gain": 100,
  "auxtotshot": 20,
  "auxshotdone": 10
}
```

---

## 🎨 Interface Utilisateur

### Palette de Couleurs

```css
/* Statuts */
.bg-green-500    /* Actif, Connecté, Progression */
.bg-red-500      /* Inactif, Déconnecté */
.bg-blue-600     /* Actions principales */
.bg-indigo-600   /* Configuration, Filtres */
.bg-purple-600   /* Targets, Modes lecture */

/* Dégradés */
.from-blue-900.to-purple-900    /* Header principal */
.from-indigo-900.to-purple-900  /* Modal Shots */
.from-indigo-900.to-blue-900    /* Modal Config */
```

### Composants Réutilisables

**Badge Filtre**
```html
<span class="px-3 py-1 rounded bg-indigo-600 text-white font-mono">
    #0 L-Chroma
</span>
```

**Barre de Progression**
```html
<div class="flex-1 bg-gray-700 rounded-full h-2">
    <div class="bg-green-500 h-full" style="width: 50%"></div>
</div>
<span>10/20</span>
```

---

## 🔧 Configuration Requise

### .env
```env
VOYAGER_PROXY_URL=http://localhost:3003
VOYAGER_PROXY_API_KEY=your-api-key
```

### config/services.php
```php
'voyager' => [
    'proxy_url' => env('VOYAGER_PROXY_URL', 'http://localhost:3003'),
    'proxy_api_key' => env('VOYAGER_PROXY_API_KEY'),
],
```

### Proxy Voyager
Le proxy doit être démarré :
```bash
cd voyager-proxy
npm run dev
```

---

## 🚨 Gestion des Erreurs

### Affichage des Erreurs
- **Toasts/Alerts** pour les erreurs API
- **Messages console** pour le debug
- **Indicateurs de loading** pendant les requêtes

### Cas d'Erreur Gérés
```javascript
// Timeout
catch (error) {
    if (error.name === 'TimeoutError') {
        alert('⏱️ Timeout: Voyager met trop de temps à répondre');
    }
}

// Erreur réseau
catch (error) {
    if (!navigator.onLine) {
        alert('📡 Pas de connexion internet');
    }
}

// Erreur Voyager
if (!data.success) {
    alert('❌ Erreur: ' + data.error);
}
```

---

## 📈 Performance

### Optimisations

1. **Chargement initial** : Filtres chargés une seule fois au `init()`
2. **Cache client** : `filterConfig` conservé en mémoire
3. **Lazy loading** : Config matérielle chargée uniquement à la demande
4. **Timeouts adaptés** :
   - Commandes rapides : 30s
   - GetShot : 60s (commande lourde)
   - Requêtes config : 30s

### Métriques
- Chargement Sets : ~500ms
- Chargement Targets : ~1s
- Chargement Shots : ~2-5s (dépend de la quantité)
- Chargement Config : ~1s

---

## 🔍 Debugging

### Console Logs
```javascript
// Initialisation
🔭 Voyager Control Panel initialized
📊 4 Sets chargés

// Chargement filtres
✅ Configuration des filtres chargée: {...}

// Chargement config matérielle
🔧 Chargement de la configuration matérielle...
📊 Réponse API Hardware Config: {...}
✅ Configuration chargée: {...}

// Chargement shots
📸 Chargement des Shots pour target: {...}
📊 Réponse API Shots: {...}
```

### Vérifications
```javascript
// Dans la console du navigateur
const app = Alpine.store('voyagerControl');
console.log(app.sets);              // Tous les Sets
console.log(app.filterConfig);      // Configuration filtres
console.log(app.hardwareConfig);    // Configuration complète
```

---

## 📝 Workflow Typique

### Créer un Set et Ajouter une Target

1. **Créer le Set**
   - Cliquer sur "➕ Nouveau Set"
   - Remplir nom, profil, tag
   - Sauvegarder

2. **Voir les Targets**
   - Cliquer sur "🎯 Targets" du Set créé
   - Modal s'ouvre (vide au début)

3. **Voir la Config pour Créer une Target**
   - Cliquer sur "⚙️ Configuration"
   - Noter les filtres disponibles
   - Noter les modes de lecture

4. **Créer la Target** (via Voyager ou API)
   - Utiliser les index de filtres notés
   - Définir RA/DEC, rotation
   - Ajouter les shots

5. **Vérifier le Plan**
   - Rafraîchir les Targets
   - Cliquer "📸 Voir Shots"
   - Vérifier filtres, expositions, progression

---

## 🎓 Formation Utilisateur

### Pour les Débutants

1. **Comprendre la hiérarchie** :
   ```
   Set (Collection)
   └── Target (Cible)
       └── Shot (Configuration d'exposition)
   ```

2. **Consulter la config avant de créer** :
   - Toujours vérifier les filtres disponibles
   - Noter les index (0, 1, 2...)
   - Vérifier les modes de lecture

3. **Utiliser les filtres de recherche** :
   - Taper "M31" pour trouver toutes les Andromeda
   - Filtrer par profil pour un télescope spécifique

### Pour les Avancés

1. **API directe** : Utiliser les endpoints pour l'automatisation
2. **Batch operations** : Scripts pour créer plusieurs Sets/Targets
3. **Monitoring** : Utiliser les shots-done pour suivre la progression

---

## 🔒 Sécurité

### Middleware Admin
- Route protégée par `AdminMiddleware`
- Vérification du rôle utilisateur
- Redirection si non-admin

### CSRF Protection
- Token CSRF dans toutes les requêtes POST/PUT/DELETE
- Validation côté serveur

### API Key Proxy
- En-tête `X-API-Key` pour le proxy Voyager
- Configuration dans `.env`

---

## 📖 Ressources

### Documentation Technique
- `ROBOTARGET-CONFIG-API.md` - API de configuration
- `ROBOTARGET-SETS-PRODUCTION-GUIDE.md` - Guide des Sets

### Fichiers Clés
```
resources/views/admin/robotarget/sets.blade.php
app/Services/RoboTargetSetService.php
app/Services/RoboTargetShotService.php
app/Http/Controllers/Admin/RoboTargetAdminController.php
app/Http/Controllers/Admin/RoboTargetShotController.php
```

### URLs de Test
```
http://stellar.test/fr/admin/robotarget/sets
http://stellar.test/test/hardware-config
http://stellar.test/test/proxy-connection
```

---

## 🆘 Troubleshooting

### Problème : "Aucun Set trouvé"
**Solution** :
1. Vérifier que Voyager est connecté (indicateur vert)
2. Cliquer sur "🔄 Rafraîchir"
3. Vérifier les filtres de recherche

### Problème : "Timeout lors du chargement des Shots"
**Solution** :
1. Augmenter le timeout PHP à 90s (déjà fait)
2. Vérifier que Voyager répond : `/test/proxy-connection`
3. Vérifier les logs du proxy

### Problème : "Filtres affichés comme Filter 0, Filter 1"
**Solution** :
1. Vérifier que `filterConfig` est chargé (console)
2. Recharger la page
3. Vérifier l'API `/api/config/filters`

---

**Version** : 1.0
**Date** : 2025-12-26
**Auteur** : Système Voyager Control Panel
