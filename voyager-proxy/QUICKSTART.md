# 🚀 Quick Start Guide - Voyager Proxy

Guide de démarrage rapide pour tester le proxy Voyager localement.

---

## ⚡ Démarrage Rapide (5 minutes)

### Étape 1 : Installer les dépendances

```bash
cd /Users/w/Herd/Stellar/voyager-proxy
npm install
```

### Étape 2 : Configurer

```bash
# Copier le fichier d'exemple
cp .env.example .env

# Éditer la configuration
nano .env
```

**Configuration minimale pour test :**

```env
NODE_ENV=development
PORT=3002
HOST=0.0.0.0

# Voyager - MODIFIER AVEC VOS VALEURS
VOYAGER_HOST=127.0.0.1         # localhost pour test local
VOYAGER_PORT=5950
VOYAGER_INSTANCE=1

# Auth Voyager (REQUIS pour RoboTarget Manager Mode)
VOYAGER_AUTH_ENABLED=true
VOYAGER_USERNAME=admin
VOYAGER_PASSWORD=6383

# RoboTarget NDA Authentication (REQUIS pour RoboTarget API)
# ⚠️ VOYAGER_SHARED_SECRET doit correspondre au champ "Secret" dans l'onglet COMMON de Voyager
VOYAGER_SHARED_SECRET=Dherbomez
VOYAGER_AUTH_BASE=YWRtaW46NjM4Mw==
VOYAGER_MAC_KEY=Dherbomez
VOYAGER_MAC_WORD1=QRP7KvBJmXyT3sLz
VOYAGER_MAC_WORD2=MGH9TaNcLpR2fWeq
VOYAGER_MAC_WORD3=ZXY1bUvKcDf8RmNo
VOYAGER_MAC_WORD4=PLD4QsVeJh6YaTux
VOYAGER_LICENSE_NUMBER=F738-EAF6-3F29-F079-8E1E-DD77-F2BE-4A0D

# API Security (vide pour test)
API_KEY=

# CORS
CORS_ORIGIN=http://localhost,http://localhost:8080,http://stellar.test,https://stellar.test

# Dashboard
ENABLE_DASHBOARD_MODE=true

# Logs
LOG_LEVEL=debug
```

**⚠️ IMPORTANT pour RoboTarget:**
- `VOYAGER_SHARED_SECRET` doit être identique au champ "Secret" dans l'onglet COMMON de Voyager
- Redémarrer Voyager après avoir modifié le champ "Secret"
- Les valeurs ci-dessus sont des exemples - utiliser vos propres valeurs

### Étape 3 : Démarrer le proxy

```bash
npm run dev
```

**Vous devriez voir :**

```
🚀 Starting Stellar Voyager Proxy...
Environment: development
Port: 3002
📊 Metrics collector started
🌐 API Server listening on port 3002
🔌 WebSocket server started
🎯 RoboTarget event handler registered
Connecting to Voyager at 127.0.0.1:5950...
TCP connection established
⏳ Waiting for Version event...
✅ Version event received
   Voyager version: Release 2.3.14
   SessionKey: 1734637469.906
🔐 Authenticating...
✅ Authenticated successfully as admin
📊 Dashboard Mode activated
🤖 Activating RoboTarget Manager Mode...
✅ RoboTarget Manager Mode ACTIVATED (Status: DONE)
💓 Heartbeat started
✅ Connection fully established!
🔭 Connected to Voyager Application Server
✅ Stellar Voyager Proxy is ready!
📡 Voyager: 127.0.0.1:5950
🌍 API: http://0.0.0.0:3002
```

### Étape 4 : Tester l'API

**Dans un autre terminal :**

```bash
# Test health check
curl http://localhost:3002/health

# Devrait retourner :
# {
#   "status": "ok",
#   "timestamp": "...",
#   "uptime": ...,
#   "voyager": {
#     "connected": true,
#     "authenticated": true,
#     "roboTargetManagerMode": true
#   }
# }
```

### Étape 5 : Ouvrir l'interface de test

```bash
# Dans un autre terminal
cd /Users/w/Herd/Stellar/voyager-proxy/test-ui

# Démarrer serveur HTTP
python3 -m http.server 8080
```

**Ouvrir dans le navigateur :** http://localhost:8080

---

## 🎯 Tests à effectuer

### ✅ Test 1 : Vérification de base

1. Ouvrir http://localhost:8080
2. Cliquer **"🔌 Tester Connexion"**
3. Vérifier les 3 statuts :
   - API : Connecté ✅
   - WebSocket : (pas encore)
   - Voyager : Connecté ✅

### ✅ Test 2 : Dashboard

1. Cliquer **"Enable Dashboard Mode"**
2. Attendre 2 secondes
3. Cliquer **"Dashboard State"**
4. Observer les données dans le résultat

### ✅ Test 3 : WebSocket Temps Réel

1. Cliquer **"🔌 Connecter WebSocket"**
2. Vérifier **WebSocket Status : Connecté** ✅
3. Observer la console "Événements WebSocket"
4. Cocher **"Afficher ControlData"** pour voir les mises à jour toutes les 2s
5. Observer le **Dashboard Temps Réel** se mettre à jour automatiquement

### ✅ Test 4 : Commandes

**Telescope Control :**
```
1. Cliquer "Start Tracking"
2. Observer le résultat (devrait être OK)
3. Cliquer "Stop Tracking"
```

**Take Shot :**
```
1. Remplir : Exposure = 1, Binning = 1, Filter = 0
2. Cliquer "Prendre Photo"
3. Observer dans "Événements WebSocket" :
   - shotRunning avec progression
   - newFITReady quand terminé
```

**Abort :**
```
1. Pendant une exposition longue (5s+)
2. Cliquer "⛔ Arrêter"
3. Observer signal 503 (Action Stopped)
```

### ✅ Test 5 : RoboTarget (NDA Authentication)

**⚠️ Prérequis** : RoboTarget Manager Mode doit être ACTIVÉ (voir logs du proxy)

**Interface de test Laravel :**
```
1. Ouvrir : https://stellar.test/test/robotarget
2. Vérifier les statuts :
   - Proxy Status : Connecté ✅
   - Voyager Status : Connecté ✅
   - RoboTarget Mode : ACTIVÉ ✅
3. Tester avec preset "M42 - Orion Nebula"
4. Observer les logs temps réel
```

**Via API :**
```bash
# Créer un Set
curl -X POST http://localhost:3002/api/robotarget/sets \
  -H "Content-Type: application/json" \
  -d '{
    "Guid": "550e8400-e29b-41d4-a716-446655440001",
    "Name": "Test Set",
    "ProfileName": "Default.v2y",
    "Status": 0
  }'

# Devrait retourner : { "success": true, "result": { ... } }
```

**Erreur "MAC Error" ?**
- Vérifier `VOYAGER_SHARED_SECRET` correspond au champ "Secret" dans Voyager COMMON
- Vérifier l'algorithme de hachage dans `src/voyager/auth.js` (Section 6.a du protocole NDA)
- Redémarrer Voyager après modification du "Secret"
- Voir `CONNEXION-ROBOTARGET.md` pour détails

---

## 📊 Que vérifier ?

### Dans les logs du proxy (terminal 1)

```bash
# Devrait afficher :
✅ Authenticated as admin
Heartbeat started (5000ms interval)
ControlData received
Polling received
```

### Dans l'interface de test (navigateur)

**Statuts :**
- 🟢 API Status : Connecté
- 🟢 WebSocket Status : Connecté
- 🟢 Voyager Status : Connecté

**Dashboard Temps Réel :**
- Voyager : IDLE ou RUN
- Setup : ✅ Oui
- Caméra Connectée : ✅ Oui
- Monture Connectée : ✅ Oui
- Température caméra : -15°C (ou autre valeur)
- Position RA/DEC : valeurs qui changent

**Console Événements :**
- `connect` avec socketId
- `initialState` avec données
- `controlData` toutes les 2 secondes (si option activée)
- `shotRunning` pendant expositions
- `newFITReady` quand image prête

---

## 🐛 Dépannage

### Problème : Proxy ne démarre pas

**Erreur : Cannot find module**
```bash
# Réinstaller dépendances
rm -rf node_modules
npm install
```

**Erreur : Port 3000 already in use**
```bash
# Changer PORT dans .env
PORT=3001
```

### Problème : Ne se connecte pas à Voyager

**Erreur : Connection timeout**

1. Vérifier que Voyager tourne
2. Vérifier l'IP dans `.env`
3. Tester la connexion :
   ```bash
   telnet 192.168.1.100 5950
   ```
4. Vérifier firewall

**Erreur : Authentication failed**

1. Désactiver auth pour tester :
   ```env
   VOYAGER_AUTH_ENABLED=false
   ```
2. Ou vérifier username/password

### Problème : WebSocket ne se connecte pas

**CORS Error**

1. Vérifier `CORS_ORIGIN` dans `.env` du proxy
2. Ajouter `http://localhost:8080`
3. Redémarrer proxy

**Connexion fermée immédiatement**

1. Vérifier logs du proxy
2. Vérifier que le proxy tourne bien
3. Vérifier URL dans interface (http://localhost:3000)

### Problème : Pas de données Dashboard

**Dashboard State vide**

1. Cliquer "Enable Dashboard Mode"
2. Attendre 2-3 secondes
3. Re-tester "Dashboard State"

**ControlData ne s'affiche pas**

1. Cocher "Afficher ControlData" dans interface
2. Vérifier logs proxy pour "ControlData received"

---

## 🎉 Si tout fonctionne

**Vous devriez voir :**

1. ✅ Proxy connecté à Voyager
2. ✅ API répond correctement
3. ✅ WebSocket envoie des événements
4. ✅ Dashboard se met à jour en temps réel
5. ✅ Commandes fonctionnent
6. ✅ Événements shotRunning/newFIT arrivent

**Félicitations ! Le proxy est opérationnel** 🎊

---

## 📚 Prochaines étapes

### 1. Tests avancés

- Tester RoboTarget (Create Set/Target/Shot)
- Tester Camera Control (Cool/Warm)
- Tester Utilities (Autofocus/PlateSolve)

### 2. Déploiement

Suivre : `docs/voyager-proxy-setup.md`

### 3. Intégration Laravel

Suivre : `docs/voyager-proxy-setup.md` section "Intégration avec Laravel"

---

## 🆘 Support

**Logs à vérifier :**

```bash
# Logs proxy
tail -f logs/application-*.log

# Logs PM2 (si utilisé)
pm2 logs voyager-proxy

# Console navigateur
F12 → Console (pour erreurs JS/WebSocket)
```

**Fichiers de config :**

- `.env` - Configuration proxy
- `test-ui/index.html` - Interface de test
- `test-ui/app.js` - Logique interface

---

## 📖 Documentation complète

- **README.md** - Documentation proxy complète
- **test-ui/README.md** - Documentation interface de test
- **docs/voyager-proxy-setup.md** - Guide déploiement production

---

**Happy Testing! 🔭**
