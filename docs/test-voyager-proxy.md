# 🧪 Guide de Test - Voyager Proxy

> Guide complet pour tester le proxy Voyager avant l'intégration Laravel

**Créé le** : 18 novembre 2024

---

## 🎯 Objectifs du test

Avant de déployer le proxy en production et de l'intégrer à Laravel, nous devons valider que :

1. ✅ Le proxy se connecte correctement à Voyager
2. ✅ L'authentification fonctionne
3. ✅ Les événements sont reçus et parsés
4. ✅ L'API REST répond correctement
5. ✅ Le WebSocket diffuse les événements temps réel
6. ✅ Les commandes (abort, toggle, etc.) fonctionnent
7. ✅ Le système est stable pendant plusieurs minutes

---

## 📦 Ce qui a été créé

### Proxy Voyager Node.js

```
voyager-proxy/
├── src/
│   ├── index.js                 # Point d'entrée
│   ├── voyager/                 # Connexion TCP, events, auth, commands
│   ├── api/                     # API REST Express
│   ├── websocket/               # Socket.IO temps réel
│   └── utils/                   # Logger, metrics
├── test-ui/                     # 🆕 Interface de test
│   ├── index.html               # Interface web
│   ├── style.css                # Thème spatial
│   ├── app.js                   # Logique test
│   └── README.md                # Doc interface
├── package.json
├── .env.example
├── Dockerfile
├── docker-compose.yml
├── ecosystem.config.js          # PM2 config
├── README.md                    # Doc complète
└── QUICKSTART.md                # 🆕 Guide démarrage rapide
```

### Interface de Test

**URL** : `test-ui/index.html`

**Fonctionnalités** :

1. **Configuration** - URL proxy + API Key
2. **Tests API REST** - 25+ endpoints
3. **Commandes de contrôle** - Abort, Toggle, Shot, Telescope
4. **Dashboard temps réel** - Via WebSocket
5. **Console événements** - Tous les events WebSocket
6. **Console logs** - Historique actions

**Design** : Thème spatial sombre avec badges de statut en temps réel

---

## 🚀 Démarrage

### Prérequis

- Node.js 20+ installé
- Voyager Application Server accessible
- Python 3 (pour servir l'interface de test)

### 1️⃣ Installation

```bash
cd /Users/w/Herd/Stellar/voyager-proxy

# Installer dépendances
npm install
```

### 2️⃣ Configuration

```bash
# Copier exemple
cp .env.example .env

# Éditer configuration
nano .env
```

**Configuration minimale pour test local :**

```env
NODE_ENV=development
PORT=3000

# Voyager - MODIFIER !
VOYAGER_HOST=192.168.1.100      # Votre IP Voyager
VOYAGER_PORT=5950

# Auth (désactiver pour test initial)
VOYAGER_AUTH_ENABLED=false

# API (vide pour test)
API_KEY=

# CORS (pour interface test)
CORS_ORIGIN=http://localhost,http://localhost:8080

# Dashboard
ENABLE_DASHBOARD_MODE=true

# Logs verbeux
LOG_LEVEL=debug
```

### 3️⃣ Démarrer le Proxy

**Terminal 1 :**

```bash
cd /Users/w/Herd/Stellar/voyager-proxy
npm run dev
```

**Attendez :**

```
✅ Stellar Voyager Proxy is ready!
📡 Voyager: 192.168.1.100:5950
🌍 API: http://0.0.0.0:3000
```

### 4️⃣ Démarrer l'Interface de Test

**Terminal 2 :**

```bash
cd /Users/w/Herd/Stellar/voyager-proxy/test-ui
python3 -m http.server 8080
```

**Ouvrir navigateur :** http://localhost:8080

---

## 🧪 Tests à effectuer

### Phase 1 : Connexion de base (2 min)

#### Test 1.1 : Health Check

**Dans l'interface :**
1. Cliquer **"🔌 Tester Connexion"**

**Résultat attendu :**
- ✅ Badge "API: Connecté" devient vert
- ✅ Badge "Voyager: Connecté" devient vert
- ✅ Section "Health Check" affiche JSON avec `status: "ok"`

**Si échec :**
- Vérifier que le proxy tourne (terminal 1)
- Vérifier URL dans config (doit être `http://localhost:3000`)

#### Test 1.2 : Connection Status

**Dans l'interface :**
1. Cliquer **"Connection Status" → Test**

**Résultat attendu :**
```json
{
  "success": true,
  "connection": {
    "status": "connected",
    "connectedAt": "2024-11-18T...",
    "reconnectAttempts": 0
  },
  "isConnected": true,
  "isAuthenticated": true,
  "version": {
    "VOYVersion": "Release 2.0.14f",
    ...
  }
}
```

**Vérifier :**
- `isConnected: true`
- `isAuthenticated: true`
- Version Voyager affichée

#### Test 1.3 : Dashboard State

**Dans l'interface :**
1. Cliquer **"Enable Dashboard Mode" → Activer**
2. Attendre 2 secondes
3. Cliquer **"Dashboard State" → Test**

**Résultat attendu :**
```json
{
  "success": true,
  "data": {
    "VOYSTAT": 1,
    "SETUPCONN": true,
    "CCDCONN": true,
    "CCDTEMP": -15,
    "MNTCONN": true,
    ...
  }
}
```

**Vérifier :**
- Données complètes de Voyager
- `SETUPCONN: true`
- `CCDCONN: true`
- `MNTCONN: true`

---

### Phase 2 : WebSocket Temps Réel (5 min)

#### Test 2.1 : Connexion WebSocket

**Dans l'interface :**
1. Cliquer **"🔌 Connecter WebSocket"**

**Résultat attendu :**
- ✅ Badge "WebSocket: Connecté" devient vert
- ✅ Console "Événements WebSocket" affiche :
  ```
  [timestamp] connect
  {
    "socketId": "abc123..."
  }

  [timestamp] initialState
  {
    "connection": {...},
    "version": {...},
    "controlData": {...}
  }
  ```

**Logs interface (en bas) :**
- ✅ "WebSocket connecté"
- ✅ "État initial reçu"

#### Test 2.2 : Événements ControlData

**Dans l'interface :**
1. Cocher **"Afficher ControlData (verbose)"**

**Résultat attendu :**
- ✅ Événement `controlData` apparaît **toutes les 2 secondes**
- ✅ Contient données parsées :
  ```json
  {
    "voyagerStatus": "IDLE",
    "camera": {
      "connected": true,
      "temperature": -15,
      "power": 50,
      ...
    },
    "mount": {...},
    "focuser": {...}
  }
  ```

#### Test 2.3 : Dashboard Temps Réel

**Observer section "📊 Dashboard Temps Réel" :**

**Cartes qui doivent s'afficher :**

1. **Voyager**
   - Statut: IDLE (ou RUN)
   - Setup: ✅ Oui

2. **📷 Caméra**
   - Connectée: ✅ Oui
   - Température: -15°C (ou valeur réelle)
   - Consigne: -15°C
   - Puissance: 50%
   - Cooling: ✅ Oui

3. **🔭 Monture**
   - Connectée: ✅ Oui
   - Parkée: ❌ Non (ou Oui si parkée)
   - RA: 12:34:56
   - DEC: +45:12:34
   - Tracking: ✅ Oui (si actif)

4. **🎯 Focuser**
   - Connecté: ✅ Oui
   - Position: 12345
   - Température: 12.5°C

5. **📋 Séquence**
   - Nom: (nom séquence ou -)
   - Restant: (temps ou -)

6. **🎯 Guidage**
   - Statut: RUNNING (ou STOPPED)
   - RMS X: 0.25"
   - RMS Y: 0.18"

**Test dynamique :**
1. Observer pendant **30 secondes**
2. Vérifier que les valeurs **se mettent à jour**
3. Température peut varier légèrement
4. Position RA/DEC change si tracking actif

---

### Phase 3 : Commandes de Contrôle (10 min)

#### Test 3.1 : Telescope Control

**Test Start Tracking :**
1. Cliquer **"Start Tracking"**
2. Observer résultat dans carte "🔭 Telescope"
3. Vérifier événement WebSocket `remoteActionResult`

**Résultat attendu :**
```json
{
  "success": true,
  "result": {
    "parsed": {
      "status": "OK",
      "statusCode": 4,
      ...
    }
  }
}
```

**Test Stop Tracking :**
1. Cliquer **"Stop Tracking"**
2. Même vérifications

**Test Park/Unpark :**
1. Cliquer **"Park"** → OK
2. Observer dans Dashboard : "Parkée: ✅ Oui"
3. Cliquer **"Unpark"** → OK
4. Observer dans Dashboard : "Parkée: ❌ Non"

#### Test 3.2 : Take Shot

**Configuration :**
1. Remplir champs :
   - Exposure: `1` (secondes)
   - Binning: `1`
   - Filter: `0` (L)

2. Cliquer **"Prendre Photo"**

**Résultat attendu :**

**Console Événements :**
```
[timestamp] shotRunning
{
  "remaining": 0.8,
  "total": 1,
  "progress": 20
}

[timestamp] shotRunning
{
  "remaining": 0.5,
  "total": 1,
  "progress": 50
}

[timestamp] newFITReady
{
  "filename": "C:\\...\\Image_20241118_123456.fit",
  "type": "LIGHT",
  "target": ""
}
```

**Console Logs :**
- ✅ "Shot commandé"
- ✅ "Shot en cours: 20.0%"
- ✅ "Shot en cours: 50.0%"
- ✅ "Nouvelle image FITS: Image_..."

**Durée** : ~1-2 secondes (exposition 1s + download)

#### Test 3.3 : Abort

**Test durant exposition longue :**
1. Configurer : Exposure = `10` secondes
2. Cliquer **"Prendre Photo"**
3. **Pendant l'exposition**, cliquer **"⛔ Arrêter"**

**Résultat attendu :**
- ✅ Événement `signal` avec Code 503 (Action Stopped)
- ✅ Exposition s'arrête
- ✅ Log "Abort envoyé"

#### Test 3.4 : Toggle Target (optionnel)

**Si vous avez un Target GUID :**
1. Remplir **Target GUID** : `votre-guid-ici`
2. Cocher **"Activer"**
3. Cliquer **"Toggle"**

**Résultat attendu :**
- ✅ `{ "success": true }`
- ✅ Target activé dans RoboTarget

---

### Phase 4 : Stabilité (15 min)

#### Test 4.1 : Longue durée

**Laisser tourner pendant 15 minutes :**

1. **Observer logs proxy (terminal 1) :**
   ```
   Polling received
   ControlData received
   Polling received
   ControlData received
   ...
   ```

2. **Observer interface :**
   - Dashboard continue de se mettre à jour
   - Pas de déconnexions WebSocket
   - Pas d'erreurs dans console

3. **Vérifier métriques (toutes les 60s dans logs proxy) :**
   ```
   === METRICS REPORT ===
   Uptime: 15m 32s
   WebSocket: 1 active, 1 total
   API: 25 requests, 0 errors
   Voyager: 0 reconnections
   Events: {"controlData":450,"polling":180,...}
   ======================
   ```

**Résultats attendus :**
- ✅ Pas de reconnexions Voyager
- ✅ 0 erreurs API
- ✅ Événements reçus régulièrement
- ✅ Dashboard toujours à jour

#### Test 4.2 : Déconnexion/Reconnexion

**Test résilience :**
1. **Arrêter Voyager** (si possible)
2. Observer logs proxy :
   ```
   ❌ Socket closed
   ⚠️ Disconnected: close
   ℹ️ Reconnecting in 5000ms (attempt 1/10)
   ```
3. Badge "Voyager: Déconnecté" devient rouge
4. **Redémarrer Voyager**
5. Observer reconnexion automatique :
   ```
   ✅ Connected to Voyager Application Server
   ```
6. Badge redevient vert

**Résultat attendu :**
- ✅ Reconnexion automatique
- ✅ Dashboard redevient fonctionnel
- ✅ Pas de crash du proxy

---

## ✅ Checklist de Validation

### Connexions
- [ ] API proxy accessible (http://localhost:3000/health)
- [ ] Voyager connecté et authentifié
- [ ] WebSocket connecté
- [ ] Heartbeat maintenu (Polling toutes les 5s)

### Événements
- [ ] Version reçu à la connexion
- [ ] ControlData reçu toutes les 2s (si Dashboard activé)
- [ ] Signal reçu lors changements d'état
- [ ] ShotRunning reçu pendant expositions
- [ ] NewFITReady reçu après images

### API REST
- [ ] Health check fonctionne
- [ ] Connection status fonctionne
- [ ] Dashboard state fonctionne
- [ ] Enable Dashboard fonctionne

### Commandes
- [ ] Start/Stop Tracking fonctionnent
- [ ] Park/Unpark fonctionnent
- [ ] Take Shot fonctionne
- [ ] Abort fonctionne
- [ ] Toggle Target fonctionne (si testé)

### Dashboard Temps Réel
- [ ] Statut Voyager s'affiche
- [ ] Données caméra s'affichent et se mettent à jour
- [ ] Données monture s'affichent
- [ ] Données focuser s'affichent
- [ ] Données guidage s'affichent (si actif)
- [ ] Séquence s'affiche (si active)

### Stabilité
- [ ] Fonctionne pendant 15+ minutes sans erreur
- [ ] Pas de fuites mémoire visible
- [ ] Reconnexion automatique OK
- [ ] Pas de crash

---

## 🎉 Validation Réussie

**Si tous les tests passent, vous avez validé :**

✅ Le proxy se connecte correctement à Voyager
✅ L'authentification fonctionne
✅ Les événements sont reçus et parsés correctement
✅ L'API REST est opérationnelle
✅ Le WebSocket diffuse en temps réel
✅ Les commandes fonctionnent
✅ Le système est stable

**Vous êtes prêt pour :**

1. **Déployer en production** sur votre serveur cloud
2. **Intégrer avec Laravel** (modifier VoyagerService)
3. **Créer l'interface utilisateur finale** dans Stellar

---

## 🐛 Résolution de Problèmes

### Proxy ne démarre pas

**Erreur : ECONNREFUSED**
- Vérifier que Voyager est accessible
- Tester : `telnet 192.168.1.100 5950`
- Vérifier firewall

**Erreur : Authentication timeout**
- Désactiver auth pour test : `VOYAGER_AUTH_ENABLED=false`
- Vérifier credentials si auth activée

### WebSocket ne fonctionne pas

**CORS Error**
- Ajouter dans `.env` du proxy : `CORS_ORIGIN=http://localhost:8080`
- Redémarrer proxy

**Connexion fermée**
- Vérifier logs proxy pour erreurs
- Vérifier URL correcte dans interface

### Pas de données Dashboard

**ControlData vide**
- Cliquer "Enable Dashboard Mode"
- Attendre 2-3 secondes
- Rafraîchir "Dashboard State"

**Valeurs à "-"**
- Vérifier que Voyager envoie les données
- Vérifier logs pour "ControlData received"

---

## 📚 Documentation Complète

- **voyager-proxy/README.md** - Doc proxy complète
- **voyager-proxy/QUICKSTART.md** - Guide démarrage rapide
- **voyager-proxy/test-ui/README.md** - Doc interface test
- **docs/voyager-proxy-setup.md** - Guide déploiement production
- **docs/roadmap-controle-telescope.md** - Roadmap complète

---

## 📞 Support

**Logs à consulter :**

```bash
# Logs proxy en temps réel
tail -f /Users/w/Herd/Stellar/voyager-proxy/logs/application-*.log

# Console navigateur (F12)
# Onglet Console pour erreurs JS
# Onglet Network pour requêtes API/WS
```

**Fichiers importants :**

- `.env` - Configuration proxy
- `src/voyager/connection.js` - Logique connexion
- `src/api/routes.js` - Routes API
- `test-ui/app.js` - Logique interface test

---

**Happy Testing! 🔭🚀**

*Une fois tous les tests validés, passez au déploiement production !*
