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
PORT=3000
HOST=0.0.0.0

# Voyager - MODIFIER AVEC VOS VALEURS
VOYAGER_HOST=192.168.1.100     # IP de votre Voyager
VOYAGER_PORT=5950
VOYAGER_INSTANCE=1

# Auth Voyager (optionnel pour test)
VOYAGER_AUTH_ENABLED=false
# VOYAGER_USERNAME=admin
# VOYAGER_PASSWORD=password

# API Security (vide pour test)
API_KEY=

# CORS
CORS_ORIGIN=http://localhost,http://localhost:8080

# Dashboard
ENABLE_DASHBOARD_MODE=true

# Logs
LOG_LEVEL=debug
```

### Étape 3 : Démarrer le proxy

```bash
npm run dev
```

**Vous devriez voir :**

```
🚀 Starting Stellar Voyager Proxy...
Environment: development
Port: 3000
📊 Metrics collector started
🌐 API Server listening on port 3000
🔌 WebSocket server started
Connecting to Voyager at 192.168.1.100:5950...
TCP connection established
Voyager version: Release 2.0.14f
✅ Authenticated as admin
🔭 Connected to Voyager Application Server
✅ Stellar Voyager Proxy is ready!
📡 Voyager: 192.168.1.100:5950
🌍 API: http://0.0.0.0:3000
```

### Étape 4 : Tester l'API

**Dans un autre terminal :**

```bash
# Test health check
curl http://localhost:3000/health

# Devrait retourner :
# {
#   "status": "ok",
#   "timestamp": "...",
#   "uptime": ...,
#   "voyager": {
#     "connected": true,
#     "authenticated": true
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
