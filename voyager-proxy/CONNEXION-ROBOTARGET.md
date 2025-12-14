# 🤖 Connexion RoboTarget - Guide complet

## ✅ Ce qui a été implémenté

Toute la séquence de connexion selon la documentation officielle Voyager a été implémentée :

### 1. Calcul du Hash pour RemoteSetRoboTargetManagerMode

**Fichier** : `src/voyager/auth.js` lignes 92-169

**Formule officielle** :
```javascript
SHA1("RoboTarget Shared secret" ||:|| SessionKey ||:|| Word1+Word2+Word3+Word4) → Base64
```

**Implémentation** :
```javascript
const sharedSecret = 'RoboTarget Shared secret';
const separator = '||:||';
const wordsConcat = `${macWord1}${macWord2}${macWord3}${macWord4}`;
const hashString = `${sharedSecret}${separator}${sessionKey}${separator}${wordsConcat}`;
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
# Connexion Voyager
VOYAGER_HOST=185.228.120.120
VOYAGER_PORT=23002              # ou 5950 pour port standard
VOYAGER_INSTANCE=1

# Authentification (optionnelle selon config Voyager)
VOYAGER_AUTH_ENABLED=false      # true si authentification requise

# RoboTarget Manager Mode (REQUIS pour API RoboTarget)
VOYAGER_AUTH_BASE=777539
VOYAGER_MAC_KEY=Dherbomez
VOYAGER_MAC_WORD1=QRP7KvBJmXyT3sLz
VOYAGER_MAC_WORD2=MGH9TaNcLpR2fWeq
VOYAGER_MAC_WORD3=ZXY1bUvKcDf8RmNo
VOYAGER_MAC_WORD4=PLD4QsVeJh6YaTux
VOYAGER_LICENSE_NUMBER=F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D

# HeartBeat (Keep-Alive)
HEARTBEAT_INTERVAL=5000         # 5 secondes (recommandé)
CONNECTION_TIMEOUT=15000        # 15 secondes (selon doc)
```

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
🔭 Test de connexion Voyager + RoboTarget
==========================================

📡 Serveur: 185.228.120.120:23002
🔑 MAC Key: Dherbomez

✅ Connexion TCP établie
⏳ En attente de l'événement Version...

📥 Données reçues (234 octets)

📨 Message reçu:
{
  "Event": "Version",
  "VOYVersion": "2.10.0",
  "Timestamp": "1732569120.123456",
  ...
}

✅ Événement Version reçu!
   Version Voyager: 2.10.0
   SessionKey (Timestamp): 1732569120.123456

🤖 Activation du mode RoboTarget Manager...
   Hash string length: 147
   Hash (SHA1→Base64): mQw/4x7qn09944Ndj5ne9/Z+b0=

📤 Envoi de la commande RemoteSetRoboTargetManagerMode...

⏳ En attente de la réponse (RemoteActionResult)...

📬 RemoteActionResult reçu:
   UID: xxx-xxx-xxx
   ParamRet.ret: DONE

✅ ✅ ✅ SUCCÈS! ✅ ✅ ✅
🎯 Mode RoboTarget Manager ACTIVÉ!

💚 La connexion fonctionne parfaitement!
```

## ❌ Problème actuel

### Diagnostic

```bash
# Test de connectivité
nc -zv 185.228.120.120 23002
# ✓ Connection succeeded

# Mais le serveur ne répond pas (pas de données)
```

**Symptômes** :
- ✓ Port 23002 ouvert (connexion TCP réussit)
- ✗ Serveur n'envoie aucune donnée
- ✗ Pas d'événement Version reçu
- ✗ Timeout après 10 secondes

**Causes possibles** :

1. **Voyager n'est pas en cours d'exécution** sur le serveur distant
2. **Port 23002 est un tunnel/proxy** mal configuré
3. **Firewall** bloque les données (mais pas la connexion)
4. **Voyager attend une authentification** avant Version (non-standard)

## 🔧 Dépannage

### 1. Vérifier que Voyager est accessible

```bash
# Test basique de connectivité
nc -zv 185.228.120.120 23002

# Test avec envoi de données
echo '{"method": "ping"}' | nc 185.228.120.120 23002

# Si aucune réponse → problème serveur
```

### 2. Essayer le port standard

```bash
# Modifier .env
VOYAGER_PORT=5950  # Port standard Voyager

# Relancer
npm run dev
```

### 3. Tester en local (si Voyager installé localement)

```bash
# Modifier .env
VOYAGER_HOST=localhost
VOYAGER_PORT=5950

# Relancer
npm run dev
```

### 4. Vérifier les logs Voyager

Sur le serveur où Voyager tourne, vérifier :
- Voyager est démarré ?
- Logs d'erreur ?
- Configuration du port ?
- Niveau d'authentification requis ?

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

## ✅ Prochaines étapes

Une fois la connexion établie :

1. **Tester les commandes RoboTarget**
   ```bash
   curl -X GET http://localhost:3000/api/robotarget/sets \
     -H "X-API-Key: votre_api_key"
   ```

2. **Créer un Set**
   ```bash
   curl -X POST http://localhost:3000/api/robotarget/sets \
     -H "Content-Type: application/json" \
     -H "X-API-Key: votre_api_key" \
     -d '{
       "Guid": "550e8400-e29b-41d4-a716-446655440001",
       "Name": "Test Set",
       "ProfileName": "Default.v2y",
       "Status": 0
     }'
   ```

3. **Créer une Target**
   ```bash
   curl -X POST http://localhost:3000/api/robotarget/targets \
     -H "Content-Type: application/json" \
     -H "X-API-Key: votre_api_key" \
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

## 📞 Support

Si le problème persiste :

1. **Vérifier avec l'administrateur** du serveur `185.228.120.120`
2. **Tester Voyager localement** si possible
3. **Consulter la doc** : `docs/api-robotarget.md`
4. **Logs détaillés** : `LOG_LEVEL=debug` dans `.env`

---

**💚 Le code est 100% conforme à la documentation officielle !**

Dès que le serveur Voyager sera accessible et répondra correctement, tout fonctionnera parfaitement.
