# Modifications pour implémenter RemoteRoboTargetGetTarget et RemoteRoboTargetAddTarget

Date: 2025-12-20
Objectif: Implémenter les fonctionnalités de récupération et création de targets conformément à la documentation Voyager RoboTarget JSON-RPC API

## 1. Modifications dans `src/voyager/commands.js`

### A. Ajouter la détection des commandes `RemoteRoboTarget`

**Ligne 21 - AVANT:**
```javascript
if (method.startsWith('RoboTarget')) {
```

**Ligne 21 - APRÈS:**
```javascript
if (method.startsWith('RoboTarget') || method.startsWith('RemoteRoboTarget')) {
```

### B. Modifier la méthode `addTarget` pour utiliser les paramètres directs

**Lignes 111-115 - AVANT:**
```javascript
async addTarget(data) {
  return this.send('RoboTargetAddTarget', {
    Target: JSON.stringify(data),
  });
}
```

**Lignes 111-130 - APRÈS:**
```javascript
/**
 * Add target with direct parameters (API v2 - RemoteRoboTargetAddTarget)
 * @param {Object} data - Target data with direct parameters
 * @param {string} data.GuidTarget - Unique GUID for the target
 * @param {string} data.RefGuidSet - GUID of the parent Set
 * @param {string} data.RefGuidBaseSequence - GUID of the base sequence template (.s2q)
 * @param {string} data.TargetName - Name of the target
 * @param {number} data.RAJ2000 - Right Ascension in decimal hours
 * @param {number} data.DECJ2000 - Declination in decimal degrees
 * @param {string} data.C_Mask - Constraint mask (e.g., "B" for altitude, "L" for moon)
 * @param {number} [data.Status=0] - Status (0 = active, 1 = inactive)
 * @param {number} [data.Priority=2] - Priority level
 * @param {number} [data.C_AltMin=30] - Minimum altitude constraint
 * @param {number} [data.C_HAStart=-3] - Hour angle start
 * @param {number} [data.C_HAEnd=3] - Hour angle end
 */
async addTarget(data) {
  return this.send('RemoteRoboTargetAddTarget', {
    ...data,
  });
}
```

### C. Ajouter la méthode `getTargets`

**À AJOUTER après la méthode `listTargetsForSet` (après ligne 172):**
```javascript
/**
 * Get targets using RemoteRoboTargetGetTarget
 * @param {string} refGuidSet - GUID of the Set to filter by (empty string returns all targets)
 * @returns {Promise} Promise resolving to RemoteActionResult with target list
 */
async getTargets(refGuidSet = '') {
  logger.info(`🎯 getTargets() called - refGuidSet: "${refGuidSet || 'ALL'}"`);
  const result = await this.send('RemoteRoboTargetGetTarget', {
    RefGuidSet: refGuidSet,
  });
  logger.info('🎯 getTargets() result received:', result);
  return result;
}
```

## 2. Modifications dans `src/api/routes.js`

### A. Modifier la route GET `/robotarget/targets` pour supporter les deux APIs

**Lignes 248-268 - AVANT:**
```javascript
router.get('/robotarget/targets', async (req, res, next) => {
  try {
    const { setGuid } = req.query;

    if (!setGuid) {
      return res.status(400).json({
        success: false,
        error: 'setGuid query parameter is required',
      });
    }

    const result = await req.voyager.commands.listTargetsForSet(setGuid);

    res.json({
      success: true,
      result,
    });
  } catch (error) {
    next(error);
  }
});
```

**Lignes 248-278 - APRÈS:**
```javascript
router.get('/robotarget/targets', async (req, res, next) => {
  try {
    const { setGuid, refGuidSet } = req.query;

    // Support both old API (setGuid) and new API (refGuidSet with RemoteRoboTargetGetTarget)
    if (refGuidSet !== undefined) {
      // New API: RemoteRoboTargetGetTarget (refGuidSet can be empty string for all targets)
      const result = await req.voyager.commands.getTargets(refGuidSet);
      return res.json({
        success: true,
        method: 'RemoteRoboTargetGetTarget',
        result,
      });
    } else if (setGuid) {
      // Old API: RoboTargetListTargets
      const result = await req.voyager.commands.listTargetsForSet(setGuid);
      return res.json({
        success: true,
        method: 'RoboTargetListTargets',
        result,
      });
    } else {
      return res.status(400).json({
        success: false,
        error: 'Either setGuid or refGuidSet query parameter is required. Use refGuidSet="" to get all targets.',
      });
    }
  } catch (error) {
    next(error);
  }
});
```

## 3. Modifications dans le contrôleur Laravel

### Fichier: `app/Http/Controllers/RoboTargetTestController.php`

**À AJOUTER après la méthode `listTargets` (après ligne 104):**

```php
/**
 * Get all targets using RemoteRoboTargetGetTarget
 */
public function getAllTargets()
{
    $result = $this->voyager->getTargets(''); // Empty string = all targets

    return response()->json([
        'success' => true,
        'targets' => $result,
    ]);
}

/**
 * Get targets for a specific set using RemoteRoboTargetGetTarget
 */
public function getTargetsForSet(Request $request)
{
    $setGuid = $request->query('set_guid', '');

    $result = $this->voyager->getTargets($setGuid);

    return response()->json([
        'success' => true,
        'set_guid' => $setGuid ?: 'ALL',
        'targets' => $result,
    ]);
}
```

### Fichier: `app/Services/VoyagerService.php`

**À AJOUTER:**

```php
/**
 * Get targets using RemoteRoboTargetGetTarget
 * @param string $refGuidSet GUID of the set (empty string for all targets)
 */
public function getTargets($refGuidSet = '')
{
    $params = ['refGuidSet' => $refGuidSet];

    $response = Http::withHeaders([
        'X-API-Key' => config('services.voyager.proxy_api_key'),
    ])->timeout(config('services.voyager.timeout', 20))
      ->get(config('services.voyager.proxy_url') . '/api/robotarget/targets', $params);

    if ($response->successful()) {
        return $response->json();
    }

    throw new \Exception('Failed to get targets: ' . $response->body());
}
```

## 4. Modifications dans la vue de test

### Fichier: `resources/views/test/robotarget.blade.php`

**À AJOUTER dans la section "Commandes de Debug" (autour de la ligne 154):**

```html
<button @click="sendCommand('get-all-targets')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm transition">
    🎯 Get All Targets
</button>
```

**À MODIFIER dans la fonction `sendCommand` du script Alpine.js (autour de la ligne 391):**

```javascript
async sendCommand(cmd) {
    this.addLog('info', `Commande: ${cmd}`);
    try {
        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

        let endpoint = '';
        switch(cmd) {
            case 'ping':
            case 'status':
                endpoint = `/api/status/connection`;
                break;
            case 'dashboard':
                endpoint = `/api/dashboard/state`;
                break;
            case 'targets':
                endpoint = `/api/robotarget/sets`;
                break;
            case 'get-all-targets':
                endpoint = `/api/robotarget/targets?refGuidSet=`;
                break;
            default:
                endpoint = `/api/${cmd}`;
        }

        const response = await fetch(`${proxyUrl}${endpoint}`, {
            headers: { 'X-API-Key': apiKey }
        });
        const data = await response.json();
        this.addLog('success', `Réponse ${cmd}: ${JSON.stringify(data).substring(0, 100)}`);
        console.log(`[${cmd}]`, data);
    } catch (error) {
        this.addLog('error', `Erreur ${cmd}: ${error.message}`);
    }
}
```

## 5. Routes à ajouter dans Laravel

### Fichier: `routes/web.php`

**À AJOUTER dans la section des routes de test RoboTarget:**

```php
Route::get('/test/robotarget/targets/all', [RoboTargetTestController::class, 'getAllTargets'])
    ->name('test.robotarget.get-all-targets');

Route::get('/test/robotarget/targets/by-set', [RoboTargetTestController::class, 'getTargetsForSet'])
    ->name('test.robotarget.get-targets-for-set');
```

## 6. Test des modifications

### Test 1: Récupérer toutes les targets

**Via API Node.js:**
```bash
curl -H "X-API-Key: votre_api_key" \
  "http://localhost:3000/api/robotarget/targets?refGuidSet="
```

**Via Laravel:**
```bash
curl "https://stellar.test/test/robotarget/targets/all"
```

### Test 2: Récupérer les targets d'un Set spécifique

**Via API Node.js:**
```bash
curl -H "X-API-Key: votre_api_key" \
  "http://localhost:3000/api/robotarget/targets?refGuidSet=GUID_DU_SET"
```

**Via Laravel:**
```bash
curl "https://stellar.test/test/robotarget/targets/by-set?set_guid=GUID_DU_SET"
```

### Test 3: Créer une target avec RemoteRoboTargetAddTarget

**Exemple de payload:**
```json
{
  "GuidTarget": "550e8400-e29b-41d4-a716-446655440000",
  "RefGuidSet": "660e8400-e29b-41d4-a716-446655440001",
  "RefGuidBaseSequence": "770e8400-e29b-41d4-a716-446655440002",
  "TargetName": "M42-Orion",
  "RAJ2000": 5.5881,
  "DECJ2000": -5.3911,
  "Status": 0,
  "Priority": 2,
  "C_Mask": "BDE",
  "C_AltMin": 30,
  "C_HAStart": -3,
  "C_HAEnd": 3
}
```

**Curl:**
```bash
curl -X POST \
  -H "Content-Type: application/json" \
  -H "X-API-Key: votre_api_key" \
  -d @target.json \
  "http://localhost:3000/api/robotarget/targets"
```

## Notes importantes

1. **Calcul du MAC**: Le MAC est automatiquement calculé par la méthode `generateRoboTargetMAC` dans `src/voyager/auth.js` selon la formule :
   ```
   SHA1(SharedSecret || | SessionKey || | JSON-RPC-ID || | UID) → Base64
   ```
   avec le séparateur `|| |` (avec espace)

2. **Conversion de coordonnées**:
   - **RA**: Convertir de HH:MM:SS en heures décimales (ex: 05:35:17 → 5.5881)
   - **DEC**: Convertir de ±DD:MM:SS en degrés décimaux (ex: -05:23:28 → -5.3911)

3. **C_Mask**: Chaîne de caractères pour activer les contraintes
   - "B" : Altitude minimale (C_AltMin)
   - "D" : Heure angle (C_HAStart, C_HAEnd)
   - "E" : Élévation
   - "L" : Phase de Lune

4. **Hiérarchie obligatoire**: Set > Target > Shot
   - Une Target doit toujours appartenir à un Set
   - Un Shot doit toujours appartenir à une Target

## Application des modifications

Pour appliquer ces modifications:

1. Arrêter le serveur de développement Node.js s'il tourne
2. Appliquer les modifications dans l'ordre: commands.js → routes.js → VoyagerService.php → RoboTargetTestController.php → robotarget.blade.php
3. Redémarrer le serveur Node.js
4. Tester sur https://stellar.test/test/robotarget
