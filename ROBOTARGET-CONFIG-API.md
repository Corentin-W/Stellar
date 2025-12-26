# RoboTarget Configuration API

Documentation de l'API de configuration matérielle pour Voyager RoboTarget.

## 📋 Vue d'ensemble

L'API de configuration permet de récupérer toutes les informations matérielles configurées dans Voyager :
- **Filtres** : Noms, offsets, magnitude min/max
- **Modes de lecture** : Readout modes disponibles (ex: "16 bit")
- **Vitesses** : Vitesses de téléchargement disponibles
- **Profils** : Tous les profils Voyager (.v2y)
- **Type de capteur** : Monochrome, Couleur, DSLR

## 🏗️ Architecture

### Service : `RoboTargetShotService`
**Emplacement** : `app/Services/RoboTargetShotService.php`

Service dédié à la gestion des shots et de la configuration matérielle.

### Controller : `RoboTargetShotController`
**Emplacement** : `app/Http/Controllers/Admin/RoboTargetShotController.php`

Controller exposant les endpoints API pour la configuration et les shots.

## 🔌 Endpoints API

### Configuration Matérielle Complète

```http
GET /admin/robotarget/api/config/hardware
GET /admin/robotarget/api/config/hardware?profile=Default.v2y
```

**Réponse** :
```json
{
  "success": true,
  "profiles": [
    {
      "guid": "xxx-xxx-xxx",
      "name": "Default.v2y",
      "isactive": true,
      "sensortype": 0,
      "iscmos": false,
      "filters": {
        "FilterNum": 8,
        "Filter1_Name": "L-Chroma",
        "Filter1_Offset": 0,
        "Filter2_Name": "H-Chroma-3nm",
        "Filter2_Offset": 0
      },
      "readoutmode": {
        "ReadoutNum": 1,
        "Readout1_Name": "16 bit",
        "Readout1_Index": 0
      }
    }
  ],
  "count": 1,
  "parsed": {
    "activeProfile": {
      "guid": "xxx-xxx-xxx",
      "name": "Default.v2y",
      "isActive": true,
      "sensorType": "Monochrome",
      "sensorTypeCode": 0,
      "isCmos": false,
      "filters": [
        {
          "index": 0,
          "name": "L-Chroma",
          "offset": 0,
          "magMin": null,
          "magMax": null
        }
      ],
      "readoutModes": [
        {
          "name": "16 bit",
          "index": 0
        }
      ],
      "speeds": []
    },
    "allProfiles": [...]
  }
}
```

### Configuration des Filtres (Simple)

```http
GET /admin/robotarget/api/config/filters
```

**Réponse** :
```json
{
  "success": true,
  "filters": [
    {
      "index": 0,
      "name": "L-Chroma",
      "offset": 0,
      "magMin": null,
      "magMax": null
    }
  ],
  "profileName": "Default.v2y",
  "sensorType": 0,
  "isCmos": false
}
```

### Détails d'un Filtre Spécifique

```http
GET /admin/robotarget/api/config/filters/{filterIndex}
```

**Exemple** : `GET /admin/robotarget/api/config/filters/0`

**Réponse** :
```json
{
  "success": true,
  "filter": {
    "index": 0,
    "name": "L-Chroma",
    "offset": 0,
    "magMin": null,
    "magMax": null
  }
}
```

### Liste des Profils

```http
GET /admin/robotarget/api/config/profiles
```

**Réponse** :
```json
{
  "success": true,
  "profiles": [
    {
      "guid": "xxx",
      "name": "Default.v2y",
      "isActive": true,
      "sensorType": "Monochrome",
      "isCmos": false,
      "filters": [...],
      "readoutModes": [...],
      "speeds": [...]
    }
  ],
  "activeProfile": {...}
}
```

### Configuration d'un Profil Spécifique

```http
GET /admin/robotarget/api/config/profiles/{profileName}
```

**Exemple** : `GET /admin/robotarget/api/config/profiles/Default.v2y`

## 💻 Utilisation dans le Code

### Récupérer la Configuration Complète

```php
use App\Services\RoboTargetShotService;

$shotService = app(RoboTargetShotService::class);

// Tous les profils
$config = $shotService->getHardwareConfiguration();

// Un profil spécifique
$config = $shotService->getHardwareConfiguration('Default.v2y');

if ($config['success']) {
    $activeProfile = $config['parsed']['activeProfile'];
    $filters = $activeProfile['filters'];
    $readoutModes = $activeProfile['readoutModes'];
}
```

### Récupérer Uniquement les Filtres

```php
$config = $shotService->getFilterConfiguration();

if ($config['success']) {
    foreach ($config['filters'] as $filter) {
        echo "{$filter['index']}: {$filter['name']}\n";
    }
}
```

### Obtenir le Nom d'un Filtre

```php
// À partir d'un filterindex
$filterName = $shotService->getFilterName(0); // "L-Chroma"
```

### Obtenir les Détails d'un Filtre

```php
$filter = $shotService->getFilterDetails(0);

if ($filter) {
    echo "Nom: {$filter['name']}\n";
    echo "Offset: {$filter['offset']}\n";
}
```

### Lister Tous les Profils

```php
$result = $shotService->getAllProfiles();

if ($result['success']) {
    $activeProfile = $result['activeProfile'];
    $allProfiles = $result['profiles'];

    foreach ($allProfiles as $profile) {
        if ($profile['isActive']) {
            echo "✅ {$profile['name']}\n";
        } else {
            echo "   {$profile['name']}\n";
        }
    }
}
```

## 📊 Structure des Données

### Type de Capteur

| Code | Type |
|------|------|
| 0 | Monochrome |
| 1 | Color |
| 2 | DSLR |

### Filtre
```php
[
    'index' => 0,
    'name' => 'L-Chroma',
    'offset' => 0,
    'magMin' => null,  // Magnitude minimale (optionnel)
    'magMax' => null   // Magnitude maximale (optionnel)
]
```

### Mode de Lecture
```php
[
    'name' => '16 bit',
    'index' => 0
]
```

### Vitesse
```php
[
    'name' => 'Fast',
    'index' => 0
]
```

## 🧪 Page de Test

Une page de test complète est disponible :

```
http://stellar.test/test/hardware-config
```

Cette page teste :
1. ✅ Récupération de la configuration complète
2. ✅ Filtres du profil actif
3. ✅ Détails d'un filtre spécifique
4. ✅ Liste de tous les profils

## 🔐 Calcul de la Signature MAC

La méthode `RemoteRoboTargetGetConfigDataShot` utilise la **règle "1-2-1"** :

```
Secret|| |SessionKey||  |ID|| |UID
       ^1^            ^2 ^   ^1^
```

- **1 espace** après le 1er bloc de barres (`|| |`)
- **2 espaces** après le 2ème bloc de barres (`||  |`)
- **1 espace** après le 3ème bloc de barres (`|| |`)

**Algorithme** : SHA1 → Hex → Base64

Cette règle est différente de celle utilisée pour `GetShot`, `GetSet`, etc. qui utilisent `||:||`.

## 📝 Notes Importantes

1. **ProfileName vide** : Si le paramètre `ProfileName` est vide, Voyager retourne TOUS les profils
2. **Profil actif** : Le profil avec `isactive: true` est celui actuellement chargé dans Voyager
3. **Parsing automatique** : Le service parse automatiquement les données au format `Filter1_Name`, `Filter2_Name`, etc.
4. **Rétrocompatibilité** : La méthode `getFilterConfiguration()` est conservée pour rétrocompatibilité
5. **Logging** : Toutes les requêtes sont loggées avec les temps de réponse

## 🔄 Migration depuis l'Ancien Code

Si vous utilisez encore `RoboTargetSetService::getConfigDataShot()` :

```php
// Ancien code (RoboTargetSetService)
$config = $setService->getConfigDataShot();

// Nouveau code (RoboTargetShotService)
$config = $shotService->getFilterConfiguration();
```

Le nouveau service offre plus de fonctionnalités et un parsing plus complet.

## 🚀 Endpoints Complémentaires

### Shots

```http
GET /admin/robotarget/api/targets/{targetGuid}/shots
GET /admin/robotarget/api/targets/{targetGuid}/shots-done
GET /admin/robotarget/api/targets/{targetGuid}/shots-all
```

### Targets

```http
GET /admin/robotarget/api/sets/{setGuid}/targets
```

### Sets

```http
GET /admin/robotarget/api/sets
POST /admin/robotarget/api/sets
PUT /admin/robotarget/api/sets/{guid}
DELETE /admin/robotarget/api/sets/{guid}
```
