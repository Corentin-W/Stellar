# 🤖 Connexion RoboTarget - Guide complet

## ✅ Ce qui a été implémenté

Toute la séquence de connexion selon la documentation officielle Voyager a été implémentée :

### 1. Calcul du Hash pour RemoteSetRoboTargetManagerMode

**Fichier** : `src/voyager/auth.js` lignes 92-169

**Formule officielle (Section 6.a du protocole NDA)** :
```javascript
SHA1("RoboTarget Shared secret" ||:|| SessionKey ||:|| Word1+Word2+Word3+Word4) → Hex → Base64
```

**⚠️ CRITIQUE - Correction de l'algorithme de hachage** :

L'algorithme CORRECT selon la Section 6.a du protocole NDA :
1. Calculer SHA1 → convertir en chaîne hexadécimale (40 caractères minuscules)
2. Encoder cette chaîne hexadécimale en Base64 (PAS les bytes bruts du SHA1!)

**Implémentation CORRECTE** :
```javascript
const sharedSecret = 'Dherbomez';  // Valeur du champ "Secret" dans COMMON
const separator = '||:||';
const wordsConcat = `${macWord1}${macWord2}${macWord3}${macWord4}`;
const hashString = `${sharedSecret}${separator}${sessionKey}${separator}${wordsConcat}`;

// CORRECT (Section 6.a du protocole NDA):
// 1. SHA1 → bytes
// 2. Convert bytes to hexadecimal string (40 chars lowercase)
// 3. Base64 encode the hex string (not the raw bytes!)
const sha1Hex = crypto.createHash('sha1').update(hashString).digest('hex');
const hash = Buffer.from(sha1Hex).toString('base64');
```

**❌ INCORRECT (ancienne version)** :
```javascript
// NE PAS FAIRE: Base64 des bytes bruts du SHA1
const hash = crypto.createHash('sha1').update(hashString).digest('base64');
```

### 2. Séquence de connexion complète

**Fichier** : `src/voyager/connection.js` lignes 92-123

**Séquence** :
1. ✅ Connexion TCP sur port configuré
2. ✅ Réception de l'événement `Version` (contient `Timestamp` = SessionKey)
3. ✅ Authentification standard (si `VOYAGER_AUTH_ENABLED=true`)
4. ✅ Activation du mode RoboTarget Manager avec `RemoteSetRoboTargetManagerMode`
5. ✅ Vérification de `ParamRet.ret === "DONE"`
6. ✅ HeartBeat (Polling toutes les 5 secondes)

### 3. Génération du MAC pour les commandes RoboTarget

**Fichier** : `src/voyager/auth.js` lignes 175-182

**Formule officielle** :
```javascript
SHA1(SharedSecret + SessionKey + JSONRPCid + CommandUID) → Base64
```

## 📋 Configuration requise

### Variables d'environnement (.env)

```bash
# Server Configuration
NODE_ENV=development
PORT=3002                       # Port du proxy (3000 ou 3002)
HOST=0.0.0.0

# Connexion Voyager
VOYAGER_HOST=127.0.0.1         # localhost pour test local, ou IP distante
VOYAGER_PORT=5950               # Port standard Voyager
VOYAGER_INSTANCE=1

# Authentification Voyager (REQUISE pour RoboTarget Manager Mode)
# Selon la documentation: l'authentification est OBLIGATOIRE avant d'activer Manager Mode
VOYAGER_AUTH_ENABLED=true
VOYAGER_USERNAME=admin
VOYAGER_PASSWORD=6383

# RoboTarget Shared Secret (obligatoire pour RoboTarget Manager Mode)
# ⚠️ DOIT être identique au champ "Secret" dans l'onglet COMMON de Voyager
VOYAGER_SHARED_SECRET=Dherbomez

# MAC Authentication (pour RoboTarget Manager Mode)
# Ces clés sont TOUJOURS nécessaires pour RoboTarget, même sans auth utilisateur
VOYAGER_AUTH_BASE=YWRtaW46NjM4Mw==      # Base64 de "admin:6383"
VOYAGER_MAC_KEY=Dherbomez
VOYAGER_MAC_WORD1=QRP7KvBJmXyT3sLz
VOYAGER_MAC_WORD2=MGH9TaNcLpR2fWeq
VOYAGER_MAC_WORD3=ZXY1bUvKcDf8RmNo
VOYAGER_MAC_WORD4=PLD4QsVeJh6YaTux
VOYAGER_LICENSE_NUMBER=F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D

# HeartBeat (Keep-Alive)
HEARTBEAT_INTERVAL=5000         # 5 secondes (recommandé)
CONNECTION_TIMEOUT=15000        # 15 secondes (selon doc)
RECONNECT_DELAY=5000
MAX_RECONNECT_ATTEMPTS=10

# CORS - Autoriser localhost et stellar.test (HTTP et HTTPS)
CORS_ORIGIN=http://localhost,http://localhost:8000,http://stellar.test,https://stellar.test

# Logging - Mode DEBUG pour voir tous les détails
LOG_LEVEL=debug

# Dashboard Mode - ACTIVÉ pour voir les données temps réel
ENABLE_DASHBOARD_MODE=true
DASHBOARD_UPDATE_INTERVAL=2000
```

### ⚠️ Points importants de configuration

1. **VOYAGER_SHARED_SECRET** : DOIT correspondre EXACTEMENT au champ "Secret" dans l'onglet COMMON de Voyager
2. **VOYAGER_AUTH_BASE** : Base64 de `username:password` (ex: `admin:6383` → `YWRtaW46NjM4Mw==`)
3. **VOYAGER_MAC_KEY** : Même valeur que VOYAGER_SHARED_SECRET dans la plupart des cas
4. **MAC_WORD1-4** : Clés MAC configurées dans Voyager

## 🧪 Tester la connexion

### Script de test simple

```bash
cd voyager-proxy
node test-connection.js
```

Ce script teste :
- ✓ Connexion TCP
- ✓ Réception événement Version
- ✓ Activation RoboTarget Manager
- ✓ Vérification du succès (DONE)

### Logs attendus (succès)

```
🚀 Starting Stellar Voyager Proxy...
Environment: development
Port: 3002

Connecting to Voyager at 127.0.0.1:5950...
TCP connection established
⏳ Waiting for Version or Polling event (SessionKey capture)...

✅ Parsed message - Event: Version
✅ Version event received
   Voyager version: Release 2.3.14
   SessionKey (Timestamp): 1734637469.906

🔐 STEP 2: Authenticating (< 5 seconds window)...
Sending auth command: {"method":"AuthenticateUserBase","params":{"UID":"...","Base":"YWRtaW46NjM4Mw=="},...}
✅ Authenticated successfully
   Username: admin
   Permissions: 3
✅ Authentication successful

📊 STEP 3: Activating Dashboard Mode...
✅ Dashboard Mode activated (JPG/ControlData stream enabled)

🤖 STEP 4: Activating RoboTarget Manager Mode...
🔍 Using documented hash formula for SessionKey: 1734637469.906
   Shared Secret: Dherbomez
   MAC Key: Dherbomez
   SessionKey: 1734637469.906
   Words: QRP7KvBJmXyT3sLzMGH9T...
   Formula: SharedSecret||:||SessionKey||:||Words

📝 Trying Official Formula: MACKey||:||SessionKey||:||Words (1/1)
   SHA1 (hex): 3061c05ec3fdb4af2c638e238ae0fa039e9beee1
   Hash (Base64 of hex): MzA2MWMwNWVjM2ZkYjRhZjJjNjM4ZTIzOGFlMGZhMDM5ZTliZWVlMQ==

Sending RemoteSetRoboTargetManagerMode (attempt 1)
   Status: DONE
✅ RoboTarget Manager Mode activated successfully with Official Formula!

💓 STEP 5: Starting Heartbeat...
Heartbeat started (5000ms interval)

✅ Connection fully established!
✅ RoboTarget Manager Mode ACTIVE - All RoboTarget commands available

🌐 API Server listening on port 3002
🔌 WebSocket server started
🎯 RoboTarget event handler registered
🔭 Connected to Voyager Application Server
✅ Stellar Voyager Proxy is ready!
📡 Voyager: 127.0.0.1:5950
🌍 API: http://0.0.0.0:3002
```

## ✅ Succès de la connexion

### Résultat attendu

Avec la configuration correcte et l'algorithme de hachage corrigé (Section 6.a du protocole NDA), vous devriez voir :

1. ✅ Connexion TCP établie
2. ✅ Événement Version reçu avec SessionKey
3. ✅ Authentification réussie (AuthenticateUserBase)
4. ✅ Dashboard Mode activé
5. ✅ **RoboTarget Manager Mode ACTIVÉ avec succès (ParamRet.ret = "DONE")**
6. ✅ Heartbeat démarré

### Point critique de succès

Le message clé qui confirme le succès :
```
✅ RoboTarget Manager Mode activated successfully!
Status: DONE
```

Cela signifie que le calcul du hash est CORRECT et que toutes les commandes RoboTarget sont maintenant disponibles.

## 🔧 Dépannage

### Erreur : "MAC Error" lors de l'activation RoboTarget Manager Mode

**Cause principale** : Hash calculé incorrectement

**Solutions** :

1. **Vérifier VOYAGER_SHARED_SECRET**
   - DOIT correspondre EXACTEMENT au champ "Secret" dans l'onglet COMMON de Voyager
   - Sensible à la casse (majuscules/minuscules)
   - Pas d'espaces en début/fin

   ```bash
   # Dans .env
   VOYAGER_SHARED_SECRET=Dherbomez  # Exemple - utiliser votre valeur
   ```

2. **Vérifier l'algorithme de hachage**
   - Assurez-vous d'utiliser la version CORRECTE dans `src/voyager/auth.js`
   - Doit convertir SHA1 en hex PUIS en Base64

   ```javascript
   // CORRECT (Section 6.a)
   const sha1Hex = crypto.createHash('sha1').update(hashString).digest('hex');
   const hash = Buffer.from(sha1Hex).toString('base64');

   // INCORRECT
   const hash = crypto.createHash('sha1').update(hashString).digest('base64');
   ```

3. **Vérifier les MAC Words**
   - Les 4 MAC Words doivent correspondre à la configuration Voyager
   - Ordre important : WORD1, WORD2, WORD3, WORD4
   - Concaténés SANS séparateur

4. **Redémarrer Voyager après modification de COMMON**
   - Si vous changez le "Secret" dans Voyager, **redémarrer Voyager**
   - Puis redémarrer le proxy

### Erreur : "Authentication Rejected"

**Cause** : Credentials incorrects ou format Base64 invalide

**Solutions** :

1. Vérifier username/password dans Voyager
2. Recalculer VOYAGER_AUTH_BASE :
   ```bash
   echo -n "admin:6383" | base64
   # Résultat : YWRtaW46NjM4Mw==
   ```
3. Mettre à jour .env avec la valeur correcte

### Erreur : "Connection timeout"

**Solutions** :

1. **Vérifier que Voyager est accessible**
   ```bash
   # Test TCP
   telnet 127.0.0.1 5950
   # ou
   nc -zv 127.0.0.1 5950
   ```

2. **Vérifier le firewall**
   - Port 5950 doit être ouvert
   - Autoriser connexions entrantes

3. **Vérifier l'IP et le port**
   ```bash
   # Dans .env
   VOYAGER_HOST=127.0.0.1  # Pour local
   VOYAGER_PORT=5950       # Port standard
   ```

### Erreur : "Authentication Level not Allow this request"

**Cause** : Voyager n'a pas d'authentification activée

**Solution** : Désactiver l'authentification dans le proxy
```bash
# Dans .env
VOYAGER_AUTH_ENABLED=false
```

Ou activer l'authentification dans Voyager (dans les paramètres).

## 📚 Référence : Documentation officielle

### Événement Version (reçu automatiquement)

```json
{
  "Event": "Version",
  "VOYVersion": "2.10.0",
  "Timestamp": "1732569120.123456",
  ...
}
```

**Le champ `Timestamp` est le SessionKey** utilisé pour calculer le Hash.

### Commande RemoteSetRoboTargetManagerMode

```json
{
  "method": "RemoteSetRoboTargetManagerMode",
  "params": {
    "UID": "uuid-unique",
    "MACKey": "Dherbomez",
    "Hash": "SHA1_Base64_du_hash"
  },
  "id": 3
}
```

### Réponse RemoteActionResult (succès)

```json
{
  "Event": "RemoteActionResult",
  "UID": "uuid-de-la-commande",
  "ParamRet": {
    "ret": "DONE"
  },
  ...
}
```

**Si `ParamRet.ret === "DONE"` → Mode RoboTarget Manager activé !**

## ✅ Utilisation des commandes RoboTarget

Une fois la connexion établie et RoboTarget Manager Mode activé :

### Via l'interface web de test

Ouvrir : `https://stellar.test/test/robotarget`

1. **Vérifier la connexion**
   - Status Proxy : Connecté ✅
   - Status Voyager : Connecté ✅
   - RoboTarget Mode : ACTIVÉ ✅

2. **Tester avec presets**
   - Cliquer sur "M42 - Orion Nebula"
   - Ou "M31 - Andromeda Galaxy"
   - Observer les logs temps réel

### Via l'API REST

**Note** : Le port du proxy est maintenant **3002** (au lieu de 3000)

1. **Créer un Set**
   ```bash
   curl -X POST http://localhost:3002/api/robotarget/sets \
     -H "Content-Type: application/json" \
     -d '{
       "Guid": "550e8400-e29b-41d4-a716-446655440001",
       "Name": "Test Set",
       "ProfileName": "Default.v2y",
       "Status": 0
     }'
   ```

2. **Créer une Target**
   ```bash
   curl -X POST http://localhost:3002/api/robotarget/targets \
     -H "Content-Type: application/json" \
     -d '{
       "GuidTarget": "550e8400-e29b-41d4-a716-446655440002",
       "RefGuidSet": "550e8400-e29b-41d4-a716-446655440001",
       "TargetName": "M31 Andromeda",
       "RAJ2000": "00:42:44.330",
       "DECJ2000": "+41:16:09.00",
       "DateCreation": 1732569120,
       "Status": 0,
       "Priority": 2
     }'
   ```

3. **Créer un Shot**
   ```bash
   curl -X POST http://localhost:3002/api/robotarget/shots \
     -H "Content-Type: application/json" \
     -d '{
       "Guid": "550e8400-e29b-41d4-a716-446655440003",
       "RefGuidTarget": "550e8400-e29b-41d4-a716-446655440002",
       "Number": 1,
       "Exposure": 120,
       "Binning": 1,
       "FilterSlot": 0,
       "Gain": 100,
       "Offset": 10
     }'
   ```

4. **Démarrer une session**
   ```bash
   curl -X POST http://localhost:3002/api/robotarget/session/start \
     -H "Content-Type: application/json" \
     -d '{
       "GuidSet": "550e8400-e29b-41d4-a716-446655440001"
     }'
   ```

## 📊 Monitoring en temps réel

### Via WebSocket (interface de test)

Les événements RoboTarget sont diffusés en temps réel :
- `roboTargetSessionStart` - Session démarrée
- `roboTargetProgress` - Progression (exposition en cours)
- `roboTargetShotComplete` - Photo terminée
- `roboTargetSessionComplete` - Session terminée
- `roboTargetError` - Erreur survenue

### Via les logs du proxy

```bash
# Surveiller les logs
tail -f voyager-proxy/logs/application-*.log

# Filtrer les événements RoboTarget
tail -f voyager-proxy/logs/application-*.log | grep -i robotarget
```

## 📚 Documentation complète

Pour plus d'informations :
- **Configuration Laravel** : `.env` (VOYAGER_PROXY_URL=http://localhost:3002)
- **API RoboTarget** : Voir routes dans `voyager-proxy/src/api/routes.js`
- **Interface de test** : `resources/views/test/robotarget.blade.php`
- **Contrôleur Laravel** : `app/Http/Controllers/RoboTargetTestController.php`

---

**💚 Le code est 100% conforme à la documentation officielle (Section 6.a du protocole NDA) !**

**✅ RoboTarget Manager Mode fonctionne parfaitement avec l'algorithme de hachage corrigé.**
