# 📋 RoboTarget Base Sequences - Documentation Complète

Documentation pour la gestion des Base Sequences (templates .s2q) dans Voyager.

---

## 🎯 Vue d'ensemble

Les **Base Sequences** sont des fichiers templates (`.s2q`) configurés dans Voyager qui servent de modèles pour la création de nouvelles cibles d'observation. Chaque séquence définit un plan d'acquisition type (filtres, expositions, binning, etc.).

### Analogie
Si `AddTarget` est l'acte de commander un plat au restaurant, `GetBaseSequence` consiste à **consulter le menu** (les recettes disponibles). Vous devez choisir une recette avant de pouvoir lancer la préparation.

---

## 🔌 API Backend

### Service Method

**Fichier** : `app/Services/RoboTargetSetService.php`

```php
/**
 * Récupérer les Base Sequences (templates .s2q) disponibles
 *
 * @param string|null $profileName Nom du profil (ex: "Default.v2y") ou null pour tous
 * @return array
 */
public function getBaseSequences(?string $profileName = null): array
```

### Endpoint API

```http
GET /admin/robotarget/api/base-sequences
GET /admin/robotarget/api/base-sequences?profile=Default.v2y
```

**Réponse** :
```json
{
  "success": true,
  "sequences": [
    {
      "guid": "abc-def-123-456",
      "basesequencename": "Deep Sky LRGB",
      "filename": "DeepSky_LRGB.s2q",
      "profilename": "Default.v2y",
      "isdefault": true
    }
  ],
  "count": 1,
  "byProfile": {
    "Default.v2y": {
      "profileName": "Default.v2y",
      "sequences": [...],
      "defaultSequence": {...}
    }
  }
}
```

---

## 💻 Utilisation dans le Code

### Récupérer Toutes les Séquences

```php
use App\Services\RoboTargetSetService;

$setService = app(RoboTargetSetService::class);

// Toutes les séquences de tous les profils
$result = $setService->getBaseSequences();

if ($result['success']) {
    $sequences = $result['sequences'];
    $byProfile = $result['byProfile'];

    foreach ($sequences as $seq) {
        echo $seq['basesequencename'] . "\n";
        echo "GUID: " . $seq['guid'] . "\n";
    }
}
```

### Récupérer les Séquences d'un Profil

```php
// Séquences d'un profil spécifique
$result = $setService->getBaseSequences('Default.v2y');

if ($result['success']) {
    foreach ($result['sequences'] as $seq) {
        if ($seq['isdefault']) {
            echo "⭐ Séquence par défaut: " . $seq['basesequencename'] . "\n";
        }
    }
}
```

### Utiliser le Groupement par Profil

```php
$result = $setService->getBaseSequences();

if ($result['success']) {
    foreach ($result['byProfile'] as $profileName => $group) {
        echo "Profil: {$profileName}\n";
        echo "Nb séquences: " . count($group['sequences']) . "\n";

        if ($group['defaultSequence']) {
            echo "Par défaut: {$group['defaultSequence']['basesequencename']}\n";
        }
    }
}
```

---

## 🖥️ Interface Utilisateur

### Accès dans le Control Panel

1. **Ouvrir le Control Panel** : `https://stellar.test/fr/admin/robotarget/sets`
2. **Cliquer sur le bouton** : `📋 Templates`
3. **Le modal s'ouvre** avec toutes les séquences

### Fonctionnalités du Modal

#### Affichage
- **Groupement automatique** par profil
- **Badge jaune** ⭐ pour la séquence par défaut
- **GUID affiché** en gris (police monospace)
- **Nom du fichier** .s2q visible

#### Filtres
```javascript
// Filtrer par profil
sequenceProfileFilter: 'Default.v2y'  // Affiche uniquement ce profil
sequenceProfileFilter: ''             // Affiche tous les profils
```

#### Actions
```
📋 Copier GUID  - Copie le GUID dans le presse-papier
```

### Code JavaScript

```javascript
// Dans le composant voyagerControl()

// Charger les séquences
await this.viewBaseSequences();

// Filtrer par profil
this.sequenceProfileFilter = 'Default.v2y';

// Copier un GUID
await this.copySequenceGuid(sequence.guid);
```

---

## 📊 Structure des Données

### Séquence
```javascript
{
  guid: "abc-def-123-456",              // GUID unique (requis pour AddTarget)
  basesequencename: "Deep Sky LRGB",    // Nom d'affichage
  filename: "DeepSky_LRGB.s2q",         // Fichier sur le disque
  profilename: "Default.v2y",           // Profil Voyager
  isdefault: true                       // Séquence par défaut du profil
}
```

### Groupement par Profil
```javascript
{
  "Default.v2y": {
    profileName: "Default.v2y",
    sequences: [                        // Toutes les séquences du profil
      {...},
      {...}
    ],
    defaultSequence: {...}              // La séquence par défaut (si existe)
  }
}
```

---

## 🔐 Calcul de la Signature MAC

La commande `RemoteRoboTargetGetBaseSequence` utilise la **règle "1-2-1"** :

```
Formule: Secret|| |SessionKey||  |ID|| |UID
                ^1^            ^2 ^   ^1^
```

- **1 espace** après le 1er bloc de barres (`|| |`)
- **2 espaces** après le 2ème bloc de barres (`||  |`)
- **1 espace** après le 3ème bloc de barres (`|| |`)

**Algorithme** : SHA1 → Hex → Base64

### Configuration Proxy

```javascript
// voyager-proxy/src/api/robotarget/test-mac-route.js
'macFormula': [
    'sep1' => '|| |',   // 1 espace
    'sep2' => '||  |',  // 2 espaces
    'sep3' => '|| |'    // 1 espace
]
```

---

## 🎨 Interface - Style et Couleurs

### Palette
```css
/* Modal header */
.from-teal-900.to-cyan-900     /* Dégradé teal/cyan */

/* Bouton Templates */
.bg-teal-600                    /* Fond teal */
.hover:bg-teal-700              /* Hover teal foncé */

/* Séquence par défaut */
.border-yellow-500/50           /* Bordure jaune */
.bg-yellow-900/10               /* Fond jaune transparent */
.text-yellow-400                /* Texte étoile jaune */

/* Badge par défaut */
.bg-yellow-600                  /* Fond jaune */
```

### Éléments Visuels

**Carte de Séquence** :
```html
<div class="bg-white/5 rounded-lg p-4 border border-white/10">
    <span class="text-yellow-400 text-xl">⭐</span>
    <div class="text-white font-medium">Deep Sky LRGB</div>
    <div class="text-sm text-gray-400">
        📄 DeepSky_LRGB.s2q
        <span class="text-xs font-mono">abc-def-123-456</span>
    </div>
</div>
```

---

## 🚀 Workflow d'Utilisation

### 1. Consulter les Templates Disponibles

```
1. Ouvrir Control Panel
2. Cliquer "📋 Templates"
3. Voir toutes les séquences groupées par profil
4. Identifier la séquence désirée
```

### 2. Copier le GUID de la Séquence

```
1. Trouver la séquence voulue (ex: "Deep Sky LRGB")
2. Cliquer "📋 Copier GUID"
3. Le GUID est dans le presse-papier
```

### 3. Utiliser le GUID pour AddTarget

```php
// Lors de la création d'une Target, utiliser le GUID copié
$targetData = [
    'name' => 'M31 Andromeda',
    'ra' => 10.6846,
    'dec' => 41.2687,
    'baseSequenceGuid' => 'abc-def-123-456',  // GUID de la séquence
    // ...
];
```

---

## 🧪 Tests

### Page de Test

**URL** : `http://stellar.test/test/base-sequences`

**Tests effectués** :
1. ✅ Récupération de toutes les séquences
2. ✅ Groupement par profil
3. ✅ Séquences d'un profil spécifique
4. ✅ Statistiques (compteurs, répartition)

### Tests Manuels

```bash
# Via API
curl http://stellar.test/admin/robotarget/api/base-sequences

# Avec profil spécifique
curl "http://stellar.test/admin/robotarget/api/base-sequences?profile=Default.v2y"
```

### Exemples de Réponse

```json
{
  "success": true,
  "sequences": [
    {
      "guid": "f3a2b1c0-d4e5-6789-abcd-ef0123456789",
      "basesequencename": "Deep Sky LRGB",
      "filename": "DeepSky_LRGB.s2q",
      "profilename": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
      "isdefault": true
    },
    {
      "guid": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
      "basesequencename": "Narrowband SHO",
      "filename": "Narrowband_SHO.s2q",
      "profilename": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
      "isdefault": false
    }
  ],
  "count": 2,
  "byProfile": {
    "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y": {
      "profileName": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
      "sequences": [...],
      "defaultSequence": {
        "guid": "f3a2b1c0-d4e5-6789-abcd-ef0123456789",
        "basesequencename": "Deep Sky LRGB",
        ...
      }
    }
  }
}
```

---

## 📝 Cas d'Usage

### Cas 1 : Créer une Nouvelle Target

**Problème** : Je veux créer une target M31 avec un plan d'acquisition LRGB.

**Solution** :
1. Ouvrir `📋 Templates`
2. Trouver "Deep Sky LRGB" (⭐ séquence par défaut)
3. Copier le GUID
4. Utiliser ce GUID dans `AddTarget`

### Cas 2 : Vérifier les Séquences Disponibles par Profil

**Problème** : Je veux savoir quelles séquences sont disponibles pour mon profil.

**Solution** :
1. Ouvrir `📋 Templates`
2. Filtrer par profil dans le dropdown
3. Voir toutes les séquences de ce profil
4. Identifier la séquence par défaut (⭐)

### Cas 3 : Comparer les Séquences de Différents Profils

**Problème** : J'ai plusieurs profils et je veux voir leurs séquences.

**Solution** :
1. Ouvrir `📋 Templates`
2. Ne pas filtrer (afficher tous les profils)
3. Voir le groupement automatique par profil
4. Comparer les séquences disponibles

---

## 🔍 Debugging

### Console Logs
```javascript
📋 Chargement des Base Sequences...
📊 Réponse API Base Sequences: {...}
✅ Base Sequences chargées: {...}
```

### Vérifier les Données

```javascript
// Dans la console du navigateur
const app = Alpine.$data(document.querySelector('[x-data="voyagerControl()"]'));
console.log(app.baseSequences);           // Toutes les séquences
console.log(app.filteredSequences);       // Séquences filtrées
console.log(app.filteredSequencesByProfile);  // Groupement filtré
```

### Logs Backend

```php
use Illuminate\Support\Facades\Log;

Log::info('GetBaseSequence Request Start', [
    'profileName' => $profileName
]);

Log::info('GetBaseSequence Response', [
    'success' => $result['success'],
    'count' => $result['count']
]);
```

---

## ⚠️ Notes Importantes

### Séquence par Défaut

Chaque profil peut avoir **une seule séquence par défaut** (`isdefault: true`). Cette séquence est recommandée pour la plupart des observations.

### GUID Requis

Le **GUID est obligatoire** pour créer une target via `AddTarget`. Sans GUID de séquence, la création échouera.

### ProfileName Vide

Si `ProfileName` est **vide** (`""`), Voyager retourne **toutes les séquences de tous les profils**.

### Fichiers .s2q

Les fichiers `.s2q` sont des **fichiers de séquence Voyager** configurés dans l'interface Voyager. Ils ne peuvent pas être créés via l'API.

---

## 🔧 Configuration Requise

### .env
```env
VOYAGER_PROXY_URL=http://localhost:3003
VOYAGER_PROXY_API_KEY=your-api-key
```

### Proxy Running
Le proxy doit être démarré :
```bash
cd voyager-proxy
npm run dev
```

### Voyager Connected
Voyager doit être connecté au proxy avec une session active.

---

## 📖 Références

### Documentation Technique
- `VOYAGER-CONTROL-PANEL-GUIDE.md` - Guide complet du Control Panel
- `QUICK-REFERENCE-VOYAGER-API.md` - Référence rapide API
- `ROBOTARGET-CONFIG-API.md` - API de configuration

### Fichiers Clés
```
app/Services/RoboTargetSetService.php
app/Http/Controllers/Admin/RoboTargetAdminController.php
resources/views/admin/robotarget/sets.blade.php
resources/views/test/base-sequences.blade.php
routes/web.php
```

### URLs de Test
```
http://stellar.test/fr/admin/robotarget/sets
http://stellar.test/test/base-sequences
http://stellar.test/admin/robotarget/api/base-sequences
```

---

## 🆘 Troubleshooting

### Problème : "Aucun template trouvé"

**Solution** :
1. Vérifier que Voyager est connecté (indicateur vert)
2. Vérifier que des séquences .s2q sont configurées dans Voyager
3. Tester l'API directement : `/admin/robotarget/api/base-sequences`

### Problème : "Erreur lors du chargement"

**Solution** :
1. Vérifier les logs Laravel : `storage/logs/laravel.log`
2. Vérifier les logs du proxy
3. Tester la connexion : `/test/proxy-connection`

### Problème : "GUID non copié"

**Solution** :
1. Vérifier que le navigateur supporte l'API Clipboard
2. Utiliser HTTPS (requis pour clipboard.writeText)
3. Fallback automatique vers document.execCommand si nécessaire

---

**Version** : 1.0
**Date** : 2025-12-26
**Auteur** : Système Voyager RoboTarget
