# RoboTarget Sets API

API complète pour gérer les Sets Voyager avec calcul automatique du MAC.

## 🔐 Authentification

Toutes les routes nécessitent une authentification Sanctum:

```bash
Authorization: Bearer {votre_token}
```

## 📋 Routes disponibles

### 1. Liste tous les Sets

```http
GET /api/robotarget/sets
GET /api/robotarget/sets?profile_name=MonProfile.v2y
```

**Réponse:**
```json
{
  "success": true,
  "sets": [
    {
      "guid": "2fea3ea2-84cd-4488-b641-bff46be09c8e",
      "setname": "Comets",
      "profilename": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
      "isdefault": false,
      "status": 0,
      "tag": "Comets",
      "note": ""
    }
  ],
  "count": 4
}
```

### 2. Récupérer un Set par GUID

```http
GET /api/robotarget/sets/{guid}
```

**Réponse:**
```json
{
  "success": true,
  "set": {
    "guid": "2fea3ea2-84cd-4488-b641-bff46be09c8e",
    "setname": "Comets",
    "profilename": "2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y",
    "isdefault": false,
    "status": 0,
    "tag": "Comets",
    "note": ""
  }
}
```

### 3. Créer un nouveau Set

```http
POST /api/robotarget/sets
Content-Type: application/json

{
  "name": "Mon Nouveau Set",
  "profile_name": "MonProfile.v2y",
  "is_default": false,
  "status": 0,
  "tag": "galaxies",
  "note": "Set de galaxies pour l'hiver",
  "guid": "optionnel-peut-etre-auto-generé"
}
```

**Réponse:**
```json
{
  "success": true,
  "guid": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx",
  "result": {
    "ret": "DONE"
  }
}
```

### 4. Mettre à jour un Set

```http
PUT /api/robotarget/sets/{guid}
Content-Type: application/json

{
  "name": "Nouveau nom",
  "status": 1,
  "tag": "nouveau-tag",
  "note": "Nouvelle note"
}
```

**Réponse:**
```json
{
  "success": true,
  "result": {
    "ret": "DONE"
  }
}
```

### 5. Supprimer un Set

```http
DELETE /api/robotarget/sets/{guid}
```

**⚠️ ATTENTION:** Cela supprime également toutes les Targets et données associées!

**Réponse:**
```json
{
  "success": true,
  "result": {
    "ret": "DONE"
  }
}
```

### 6. Activer un Set

```http
POST /api/robotarget/sets/{guid}/enable
```

**Réponse:**
```json
{
  "success": true,
  "enabled": true,
  "result": {
    "ret": "DONE"
  }
}
```

### 7. Désactiver un Set

```http
POST /api/robotarget/sets/{guid}/disable
```

**Réponse:**
```json
{
  "success": true,
  "enabled": false,
  "result": {
    "ret": "DONE"
  }
}
```

### 8. Sets par profil

```http
GET /api/robotarget/profiles/{profileName}/sets
```

**Exemple:**
```http
GET /api/robotarget/profiles/MonProfile.v2y/sets
```

### 9. Statut de connexion Voyager

```http
GET /api/robotarget/status
```

**Réponse:**
```json
{
  "success": true,
  "timestamp": "2025-12-26T08:44:36.635Z",
  "data": {
    "Event": "ControlData",
    "Host": "EEyeMDherbomez",
    "VOYSTAT": 0
  }
}
```

## 📝 Exemples d'utilisation

### JavaScript/Fetch

```javascript
// Liste tous les Sets
const response = await fetch('http://localhost:8000/api/robotarget/sets', {
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Accept': 'application/json'
  }
});
const data = await response.json();
console.log(data.sets);

// Créer un nouveau Set
const createResponse = await fetch('http://localhost:8000/api/robotarget/sets', {
  method: 'POST',
  headers: {
    'Authorization': 'Bearer YOUR_TOKEN',
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  body: JSON.stringify({
    name: 'Mon Set',
    profile_name: 'Default.v2y',
    tag: 'test',
    note: 'Créé via API'
  })
});
const result = await createResponse.json();
console.log('Set créé:', result.guid);
```

### cURL

```bash
# Liste tous les Sets
curl -X GET http://localhost:8000/api/robotarget/sets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Accept: application/json"

# Créer un Set
curl -X POST http://localhost:8000/api/robotarget/sets \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Mon Set",
    "profile_name": "Default.v2y",
    "tag": "test"
  }'

# Supprimer un Set
curl -X DELETE http://localhost:8000/api/robotarget/sets/GUID_DU_SET \
  -H "Authorization: Bearer YOUR_TOKEN"
```

### PHP (Laravel)

```php
use App\Services\RoboTargetSetService;

class MyController extends Controller
{
    public function __construct(
        private RoboTargetSetService $setService
    ) {}

    public function index()
    {
        // Liste tous les Sets
        $result = $this->setService->getSets();

        if ($result['success']) {
            return response()->json($result['sets']);
        }

        return response()->json(['error' => $result['error']], 400);
    }

    public function create(Request $request)
    {
        // Créer un nouveau Set
        $result = $this->setService->addSet([
            'name' => $request->name,
            'profile_name' => $request->profile_name,
            'tag' => $request->tag,
            'note' => $request->note,
        ]);

        return response()->json($result);
    }
}
```

## 🔐 Calcul automatique du MAC

Le service `RoboTargetSetService` gère automatiquement:

- ✅ Génération du UID unique
- ✅ Récupération du SessionKey
- ✅ Calcul du MAC avec la formule correcte: `Secret||:||SessionKey||:||ID||:||UID`
- ✅ Algorithme SHA1 → HEX → Base64
- ✅ Envoi de la commande à Voyager via le proxy
- ✅ Gestion des timeouts et erreurs

## ⚙️ Configuration

Fichier `.env`:

```env
VOYAGER_PROXY_URL=http://localhost:3003
VOYAGER_HOST=localhost
VOYAGER_PORT=5950
VOYAGER_PROFILE=Default.v2y
```

## 🎯 Statut des Sets

- `0` = Activé (Enabled)
- `1` = Désactivé (Disabled)

## 📌 Notes importantes

1. **ProfileName requis:** Lors de la création d'un Set, le profil doit exister dans Voyager
2. **GUID unique:** Chaque Set a un GUID unique généré automatiquement
3. **Suppression cascade:** Supprimer un Set supprime toutes ses Targets
4. **Manager Mode:** Le proxy doit être connecté avec Manager Mode activé
5. **License:** Voyager doit avoir une license Advanced ou Full

## 🐛 Dépannage

### Erreur "MAC Error"
- Vérifiez que le proxy est démarré: `cd voyager-proxy && npm run dev`
- Vérifiez que Manager Mode est activé dans les logs

### Erreur "Timeout"
- Vérifiez que Voyager est démarré et accessible
- Vérifiez la connexion proxy → Voyager

### Set non trouvé
- Utilisez `GET /api/robotarget/sets` pour lister tous les Sets disponibles
- Vérifiez que le GUID est correct
