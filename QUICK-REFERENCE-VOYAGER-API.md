# 🚀 Voyager API - Référence Rapide

Aide-mémoire pour les développeurs utilisant l'API Voyager Control Panel.

---

## 📡 Endpoints API

### Sets
```http
GET    /admin/robotarget/api/sets                  # Liste tous les Sets
POST   /admin/robotarget/api/sets                  # Créer un Set
PUT    /admin/robotarget/api/sets/{guid}           # Modifier un Set
DELETE /admin/robotarget/api/sets/{guid}           # Supprimer un Set
POST   /admin/robotarget/api/sets/{guid}/toggle    # Activer/Désactiver
```

### Targets
```http
GET /admin/robotarget/api/sets/{setGuid}/targets   # Targets d'un Set
```

### Shots
```http
GET /admin/robotarget/api/targets/{targetGuid}/shots           # Shots planifiés
GET /admin/robotarget/api/targets/{targetGuid}/shots-done      # Shots capturés
GET /admin/robotarget/api/targets/{targetGuid}/shots-all       # Tout
```

### Configuration Matérielle
```http
GET /admin/robotarget/api/config/hardware                      # Config complète
GET /admin/robotarget/api/config/hardware?profile=Default.v2y  # Profil spécifique
GET /admin/robotarget/api/config/filters                       # Filtres (simple)
GET /admin/robotarget/api/config/filters/{index}               # Détails filtre
GET /admin/robotarget/api/config/profiles                      # Tous les profils
GET /admin/robotarget/api/config/profiles/{name}               # Profil spécifique
```

---

## 💻 Exemples JavaScript

### Charger les Sets
```javascript
const response = await fetch('/admin/robotarget/api/sets', {
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});
const data = await response.json();
console.log(data.sets);
```

### Créer un Set
```javascript
const response = await fetch('/admin/robotarget/api/sets', {
    method: 'POST',
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    },
    body: JSON.stringify({
        name: 'Mon Set',
        profile_name: 'Default.v2y',
        tag: 'M31',
        status: 0,
        is_default: false
    })
});
const result = await response.json();
```

### Charger la Config Matérielle
```javascript
const response = await fetch('/admin/robotarget/api/config/hardware');
const data = await response.json();

if (data.success) {
    const activeProfile = data.parsed.activeProfile;

    console.log('Profil actif:', activeProfile.name);
    console.log('Type capteur:', activeProfile.sensorType);
    console.log('Filtres:', activeProfile.filters);
}
```

### Charger les Shots d'une Target
```javascript
const targetGuid = 'xxx-xxx-xxx';
const response = await fetch(`/admin/robotarget/api/targets/${targetGuid}/shots`);
const data = await response.json();

if (data.success) {
    data.shots.forEach(shot => {
        console.log(`Filtre ${shot.filterindex}: ${shot.num}x ${shot.exposure}s`);
    });
}
```

---

## 🐘 Exemples PHP

### Utiliser le Service
```php
use App\Services\RoboTargetSetService;
use App\Services\RoboTargetShotService;

$setService = app(RoboTargetSetService::class);
$shotService = app(RoboTargetShotService::class);

// Récupérer tous les Sets
$sets = $setService->getSets();

// Créer un Set
$result = $setService->addSet([
    'name' => 'Mon Set',
    'profile_name' => 'Default.v2y',
    'tag' => 'M31',
    'status' => 0
]);

// Récupérer les Targets d'un Set
$targets = $setService->getTargets($setGuid);

// Récupérer les Shots d'une Target
$shots = $shotService->getPlannedShots($targetGuid);

// Récupérer la config matérielle
$config = $shotService->getHardwareConfiguration();
```

### Formater les Données
```php
// Formater un temps d'exposition
$time = $shotService->formatExposureTime(330); // "5m 30s"

// Obtenir le nom d'un filtre
$filterName = $shotService->getFilterName(0); // "L-Chroma"

// Obtenir les détails d'un filtre
$filter = $shotService->getFilterDetails(0);
// ['index' => 0, 'name' => 'L-Chroma', 'offset' => 0, 'magMin' => 5, 'magMax' => 7]
```

---

## 📦 Structures de Données

### Set
```json
{
  "guid": "xxx-xxx-xxx",
  "setname": "M31 Andromeda",
  "profilename": "Default.v2y",
  "tag": "M31",
  "status": 0,
  "isdefault": false,
  "note": "Test"
}
```

### Target
```json
{
  "guid": "xxx-xxx-xxx",
  "targetname": "M31",
  "ra": 10.6846,
  "dec": 41.2687,
  "pa": 0,
  "status": 0
}
```

### Shot
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

### Filtre
```json
{
  "index": 0,
  "name": "L-Chroma",
  "offset": 0,
  "magMin": 5,
  "magMax": 7
}
```

---

## 🎨 Alpine.js

### Méthodes Utiles
```javascript
// Dans le composant voyagerControl()

// Charger les Sets
await this.refreshSets();

// Ouvrir le modal de création
this.openCreateModal();

// Voir les Targets d'un Set
await this.viewTargets(set);

// Voir les Shots d'une Target
await this.viewShots(target);

// Voir la config matérielle
await this.viewHardwareConfig();

// Formater une exposition
this.formatExposure(330); // "5m 30s"

// Obtenir le nom d'un filtre
this.getFilterName(0); // "L-Chroma"
```

### Accéder aux Données
```javascript
// Dans la console ou dans le template
this.sets                    // Tous les Sets
this.filteredSets            // Sets filtrés
this.filterConfig            // Config filtres (simple)
this.hardwareConfig          // Config complète
this.currentSetTargets       // Targets du Set courant
this.currentTargetShots      // Shots de la Target courante
```

---

## 🔑 Variables Importantes

### Statuts
```javascript
0 = Actif
1 = Inactif
```

### Types de Capteur
```javascript
0 = Monochrome
1 = Couleur
2 = DSLR
```

### Mapping Filtres
```javascript
filterindex: 0 → L-Chroma
filterindex: 1 → R-Chroma
filterindex: 2 → G-Chroma
...
```

---

## 🛠️ Commandes Utiles

### Laravel
```bash
# Vider le cache
php artisan config:clear
php artisan cache:clear

# Vérifier les routes
php artisan route:list | grep robotarget

# Logs en temps réel
tail -f storage/logs/laravel.log
```

### Proxy Voyager
```bash
# Démarrer le proxy
cd voyager-proxy
npm run dev

# Vérifier le status
curl http://localhost:3003/health

# Vérifier le dashboard
curl http://localhost:3003/api/dashboard/state
```

### Tests
```bash
# Test proxy connection
http://stellar.test/test/proxy-connection

# Test hardware config
http://stellar.test/test/hardware-config

# Test shots API
http://stellar.test/test/shots-api
```

---

## 🐛 Debug Quick

### Vérifier la Connexion
```bash
# Ping le proxy
curl http://localhost:3003/health

# État Voyager
curl http://localhost:3003/api/dashboard/state
```

### Console JavaScript
```javascript
// État de l'app
Alpine.$data(document.querySelector('[x-data]'))

// Logs
console.log('Sets:', this.sets);
console.log('Config:', this.hardwareConfig);
```

### Logs PHP
```php
use Illuminate\Support\Facades\Log;

Log::info('Debug Sets', ['sets' => $sets]);
Log::error('Erreur', ['error' => $e->getMessage()]);
```

---

## 📊 Formules MAC

### GetSet, GetTarget, GetShot (Reserved API)
```
Formula: ||:||  (colon separator)
Example: Secret||:||SessionKey||:||ID||:||UID
```

### GetConfigDataShot (Reserved API)
```
Formula: || |...||  |...|| |  (1-2-1 spaces)
Example: Secret|| |SessionKey||  |ID|| |UID
```

### Open API (GetShotDoneList)
```
Formula: MD5(SharedSecret + UID)
No Base64 encoding
```

---

## ⚡ Performance Tips

### Frontend
```javascript
// Charger les filtres une seule fois
async init() {
    await this.loadFilterConfig(); // Cache client
}

// Éviter les rechargements inutiles
if (this.filterConfig) return; // Déjà chargé
```

### Backend
```php
// Utiliser les timeouts adaptés
Http::timeout(60) // Pour GetShot (commande lourde)
Http::timeout(30) // Pour les autres

// Activer le logging pour debug
Log::info('Request', ['method' => $method, 'elapsed_ms' => $elapsed]);
```

---

## 🔗 Liens Utiles

### URLs
- Control Panel: `/fr/admin/robotarget/sets`
- Test Proxy: `/test/proxy-connection`
- Test Config: `/test/hardware-config`

### Docs
- `VOYAGER-CONTROL-PANEL-GUIDE.md` - Guide complet
- `ROBOTARGET-CONFIG-API.md` - API configuration
- `ROBOTARGET-SETS-PRODUCTION-GUIDE.md` - Guide production

---

**Dernière mise à jour** : 2025-12-26
