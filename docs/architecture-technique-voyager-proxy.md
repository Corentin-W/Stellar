# 🏗️ Architecture Technique - Voyager Proxy

> Documentation complète pour comprendre EXACTEMENT qui fait quoi et comment tout fonctionne

**Auteur** : Documentation technique pour développeur
**Date** : 18 novembre 2024
**Version** : 1.0.0

---

## 📚 Table des Matières

1. [Vue d'ensemble globale](#vue-densemble-globale)
2. [Architecture en couches](#architecture-en-couches)
3. [Flux de données complet](#flux-de-données-complet)
4. [Le Proxy - Rôle et Responsabilités](#le-proxy---rôle-et-responsabilités)
5. [L'API REST - Détails techniques](#lapi-rest---détails-techniques)
6. [Le WebSocket - Temps réel](#le-websocket---temps-réel)
7. [Connexion TCP/IP à Voyager](#connexion-tcpip-à-voyager)
8. [Commandes détaillées](#commandes-détaillées)
9. [Événements détaillés](#événements-détaillés)
10. [Cas d'usage concrets](#cas-dusage-concrets)

---

## 1. Vue d'ensemble globale

### Le problème à résoudre

**Voyager Application Server** est un logiciel Windows qui contrôle un observatoire astronomique :
- Télescope (position, mouvement)
- Caméra (température, expositions)
- Focuser (mise au point)
- Guidage (correction position)
- Séquences automatiques d'observation

**Voyager communique via TCP/IP** avec un protocole propriétaire JSON-RPC 2.0.

**Notre besoin :**
- Contrôler Voyager depuis le web (Laravel)
- Afficher l'état en temps réel
- Permettre aux utilisateurs de piloter le télescope pendant leur session

**La solution : Le Proxy Node.js**

```
┌─────────────────────────────────────────────────────────────────┐
│                         ARCHITECTURE                             │
└─────────────────────────────────────────────────────────────────┘

Internet/Réseau Local
        │
        ▼
┌───────────────────┐     HTTP/WebSocket      ┌──────────────────┐
│   Laravel App     │ ◄──────────────────────►│   Node.js Proxy  │
│   (Stellar)       │                          │   (Port 3000)    │
│                   │                          │                  │
│  - Interface Web  │                          │  - API REST      │
│  - Réservations   │                          │  - WebSocket     │
│  - Gestion Users  │                          │  - Cache État    │
└───────────────────┘                          │                  │
                                               │                  │
                                               ▼                  │
                                        JSON-RPC TCP/IP           │
                                        (Port 5950)               │
                                               │                  │
                                               ▼                  │
                                    ┌──────────────────┐          │
                                    │  Voyager Server  │          │
                                    │   (Windows PC)   │          │
                                    │                  │          │
                                    │  - RoboTarget    │          │
                                    │  - Scheduler     │          │
                                    │  - Sequences     │          │
                                    └────────┬─────────┘          │
                                             │                    │
                                             ▼                    │
                                    ┌──────────────────┐          │
                                    │   Équipements    │          │
                                    │   Physiques      │          │
                                    │                  │          │
                                    │  - Télescope     │          │
                                    │  - Monture       │          │
                                    │  - Caméra        │          │
                                    │  - Focuser       │          │
                                    │  - Roue filtres  │          │
                                    └──────────────────┘
```

---

## 2. Architecture en couches

### Couche 1 : Laravel (Frontend + Business Logic)

**Rôle** : Interface utilisateur et gestion métier

**Responsabilités :**
- Authentification utilisateurs
- Gestion des réservations
- Calcul des crédits
- Interface web (Blade + Alpine.js)
- Base de données (MySQL)
- Jobs et queues

**Ce qu'elle NE fait PAS :**
- ❌ Communiquer directement avec Voyager (c'est le proxy qui le fait)
- ❌ Maintenir une connexion TCP persistante (trop lourd)
- ❌ Parser les événements Voyager (c'est le proxy)

**Communication avec le proxy :**
```php
// Laravel → Proxy
$response = Http::withHeaders([
    'X-API-Key' => $apiKey
])->post('http://proxy.domain.com/api/control/abort');

// Ou via WebSocket (Laravel Echo)
Echo.channel('booking.' . bookingId)
    .listen('ControlDataUpdated', (data) => {
        // Mise à jour UI
    });
```

---

### Couche 2 : Le Proxy Node.js (Cœur du système)

**Rôle** : Pont entre Laravel et Voyager

**Pourquoi Node.js et pas PHP/Laravel ?**

1. **Connexions persistantes** : Node.js excelle dans les connexions TCP longues
2. **Event-driven** : Architecture par événements (parfait pour Voyager)
3. **WebSocket natif** : Socket.IO intégré facilement
4. **Performance** : Gère des milliers de connexions simultanées
5. **Non-blocking I/O** : Idéal pour du temps réel

**Responsabilités du proxy :**

```javascript
┌─────────────────────────────────────────────────────┐
│              PROXY NODE.JS (Port 3000)              │
├─────────────────────────────────────────────────────┤
│                                                     │
│  ┌─────────────────┐    ┌────────────────┐        │
│  │   API REST      │    │   WebSocket    │        │
│  │   (Express)     │    │   (Socket.IO)  │        │
│  │                 │    │                │        │
│  │  - Authentifie  │    │  - Broadcast   │        │
│  │  - Valide       │    │  - Temps réel  │        │
│  │  - Route        │    │  - Events      │        │
│  └────────┬────────┘    └────────┬───────┘        │
│           │                      │                 │
│           └──────────┬───────────┘                 │
│                      ▼                             │
│           ┌─────────────────────┐                  │
│           │  Voyager Connection │                  │
│           │  (TCP Client)       │                  │
│           │                     │                  │
│           │  - Socket TCP       │                  │
│           │  - Auth Base64      │                  │
│           │  - Heartbeat 5s     │                  │
│           │  - Auto-reconnect   │                  │
│           └──────────┬──────────┘                  │
│                      │                             │
│                      ▼                             │
│           ┌─────────────────────┐                  │
│           │  Event Handler      │                  │
│           │                     │                  │
│           │  - Parse events     │                  │
│           │  - Enrich data      │                  │
│           │  - Emit to WS       │                  │
│           └─────────────────────┘                  │
│                                                     │
│           ┌─────────────────────┐                  │
│           │  Commands           │                  │
│           │                     │                  │
│           │  - RPC calls        │                  │
│           │  - Promises         │                  │
│           │  - Timeout          │                  │
│           └─────────────────────┘                  │
│                                                     │
│           ┌─────────────────────┐                  │
│           │  Cache/State        │                  │
│           │                     │                  │
│           │  - Latest state     │                  │
│           │  - Connection info  │                  │
│           └─────────────────────┘                  │
│                                                     │
└─────────────────────────────────────────────────────┘
```

**Ce que fait EXACTEMENT le proxy :**

1. **Connexion TCP persistante** à Voyager (port 5950)
2. **Heartbeat automatique** toutes les 5s (keep-alive)
3. **Reçoit les événements** Voyager en temps réel
4. **Parse et enrichit** les données
5. **Cache l'état actuel** (dernières données)
6. **Expose une API REST** pour Laravel
7. **Broadcast via WebSocket** pour le temps réel
8. **Gère la reconnexion** automatique si Voyager crash
9. **Log tout** pour debug

---

### Couche 3 : Voyager Application Server

**Rôle** : Contrôleur d'observatoire

**Ce qu'il fait :**
- Pilote les équipements ASCOM
- Gère les séquences d'observation
- RoboTarget (automatisation)
- PlateSolve (astrométrie)
- Autofocus
- Guidage

**Ce qu'il expose :**
- Serveur TCP/IP JSON-RPC 2.0 sur port 5950
- Événements temps réel
- API de commandes

---

## 3. Flux de données complet

### Flux 1 : Laravel demande l'état actuel

```
┌─────────────┐                                    ┌──────────────┐
│   Laravel   │                                    │  Node Proxy  │
└──────┬──────┘                                    └──────┬───────┘
       │                                                  │
       │ 1. GET /api/dashboard/state                     │
       │    Header: X-API-Key: xxx                       │
       ├────────────────────────────────────────────────►│
       │                                                  │
       │                                                  │ 2. Vérifie API Key
       │                                                  │
       │                                                  │ 3. Retourne cache
       │                                                  │    (pas de call Voyager)
       │                                                  │
       │ 4. JSON Response                                │
       │ {                                               │
       │   "success": true,                              │
       │   "data": {                                     │
       │     "VOYSTAT": 1,                               │
       │     "CCDTEMP": -15,                             │
       │     ...                                         │
       │   }                                             │
       │ }                                               │
       │◄────────────────────────────────────────────────┤
       │                                                  │
       ▼                                                  ▼
```

**Détail technique :**

1. **Laravel** : `$response = Http::get($proxyUrl . '/api/dashboard/state')`
2. **Proxy** : Middleware `authMiddleware` vérifie le header `X-API-Key`
3. **Proxy** : Route `/api/dashboard/state` appelle `req.voyager.getState()`
4. **Proxy** : Retourne le **cache** (dernière `ControlData` reçue)
5. **Laravel** : Parse le JSON et affiche

**Pourquoi un cache ?**
- ✅ Réponse instantanée (pas besoin d'attendre Voyager)
- ✅ Pas de surcharge de Voyager
- ✅ Données déjà à jour (événement toutes les 2s)

---

### Flux 2 : Laravel envoie une commande

```
┌─────────────┐         ┌──────────────┐         ┌────────────┐
│   Laravel   │         │  Node Proxy  │         │  Voyager   │
└──────┬──────┘         └──────┬───────┘         └─────┬──────┘
       │                       │                        │
       │ 1. POST /api/control/abort                    │
       ├──────────────────────►│                        │
       │                       │                        │
       │                       │ 2. Génère UUID        │
       │                       │    + Timestamp         │
       │                       │                        │
       │                       │ 3. JSON-RPC Request    │
       │                       │ {                      │
       │                       │   "method": "RemoteAbortAction",
       │                       │   "params": {           │
       │                       │     "UID": "uuid..."    │
       │                       │   },                    │
       │                       │   "id": 123            │
       │                       │ }                      │
       │                       ├───────────────────────►│
       │                       │                        │
       │                       │                        │ 4. Exécute
       │                       │                        │    commande
       │                       │                        │
       │                       │ 5. Envoie événement    │
       │                       │    RemoteActionResult  │
       │                       │◄───────────────────────┤
       │                       │ {                      │
       │                       │   "Event": "RemoteActionResult",
       │                       │   "UID": "uuid...",    │
       │                       │   "ActionResultInt": 4 │ (OK)
       │                       │ }                      │
       │                       │                        │
       │                       │ 6. Parse résultat      │
       │                       │    Match UID           │
       │                       │                        │
       │ 7. JSON Response      │                        │
       │ {                     │                        │
       │   "success": true,    │                        │
       │   "result": {         │                        │
       │     "status": "OK"    │                        │
       │   }                   │                        │
       │ }                     │                        │
       │◄──────────────────────┤                        │
       │                       │                        │
       ▼                       ▼                        ▼
```

**Détail technique :**

1. **Laravel** : `Http::post($url . '/api/control/abort')`
2. **Proxy** : Route appelle `req.voyager.commands.abort()`
3. **Proxy** : Génère `UUID` unique + `id` timestamp
4. **Proxy** : Crée une `Promise` en attente du résultat
5. **Proxy** : Envoie JSON-RPC via socket TCP
6. **Voyager** : Reçoit, exécute, renvoie `RemoteActionResult`
7. **Proxy** : Event handler reçoit l'événement
8. **Proxy** : Match `UID`, résout la Promise
9. **Proxy** : Retourne à Laravel

**Système de promesses :**

```javascript
// Dans commands.js
const pendingCommands = new Map();

async send(method, params) {
  const uid = uuidv4();

  return new Promise((resolve, reject) => {
    const timeout = setTimeout(() => {
      pendingCommands.delete(uid);
      reject(new Error('Timeout'));
    }, 30000);

    pendingCommands.set(uid, { resolve, reject, timeout });

    // Envoi TCP
    this.connection.send({
      method,
      params: { UID: uid, ...params },
      id: Date.now()
    });
  });
}

// Quand événement arrive
onRemoteActionResult(event) {
  const pending = pendingCommands.get(event.UID);
  if (pending) {
    clearTimeout(pending.timeout);
    if (event.ActionResultInt === 4) { // OK
      pending.resolve(event);
    } else {
      pending.reject(new Error(event.Motivo));
    }
    pendingCommands.delete(event.UID);
  }
}
```

---

### Flux 3 : WebSocket temps réel

```
┌──────────────┐         ┌──────────────┐         ┌────────────┐
│  Navigateur  │         │  Node Proxy  │         │  Voyager   │
└──────┬───────┘         └──────┬───────┘         └─────┬──────┘
       │                        │                        │
       │ 1. Connect WebSocket   │                        │
       │    ws://proxy:3000     │                        │
       ├───────────────────────►│                        │
       │                        │                        │
       │ 2. Socket.IO           │                        │
       │    Handshake           │                        │
       │◄───────────────────────┤                        │
       │                        │                        │
       │ 3. emit('initialState')│                        │
       │◄───────────────────────┤                        │
       │ {                      │                        │
       │   connection: {...},   │                        │
       │   version: {...},      │                        │
       │   controlData: {...}   │                        │
       │ }                      │                        │
       │                        │                        │
       │                        │   Toutes les 2s        │
       │                        │◄───────────────────────┤
       │                        │   Event: ControlData   │
       │                        │                        │
       │                        │ 4. Parse + Enrich      │
       │                        │                        │
       │ 5. emit('controlData', data)                   │
       │◄───────────────────────┤                        │
       │                        │                        │
       │ 6. UI Update           │                        │
       │    (Alpine.js)         │                        │
       │                        │                        │
       │                        │   Shot running         │
       │                        │◄───────────────────────┤
       │                        │   Event: ShotRunning   │
       │                        │                        │
       │ 7. emit('shotRunning', progress)               │
       │◄───────────────────────┤                        │
       │                        │                        │
       │                        │   Image ready          │
       │                        │◄───────────────────────┤
       │                        │   Event: NewFITReady   │
       │                        │                        │
       │ 8. emit('newFITReady', imageInfo)              │
       │◄───────────────────────┤                        │
       │                        │                        │
       ▼                        ▼                        ▼
```

**Détail technique :**

1. **Client** : `socket = io('http://proxy:3000')`
2. **Proxy** : Accepte connexion WebSocket
3. **Proxy** : Enregistre client dans `clients Map`
4. **Proxy** : Envoie état initial (cache)
5. **Voyager** : Envoie `ControlData` toutes les 2s
6. **Proxy** : Event handler reçoit → Parse → Enrich
7. **Proxy** : `io.emit('controlData', enrichedData)` (broadcast)
8. **Client** : `socket.on('controlData', updateUI)`

**Avantage WebSocket vs Polling :**

```javascript
// ❌ Mauvais : Polling HTTP
setInterval(() => {
  fetch('/api/dashboard/state')
    .then(r => r.json())
    .then(updateUI);
}, 2000); // Requête toutes les 2s

// ✅ Bon : WebSocket push
socket.on('controlData', (data) => {
  updateUI(data); // Instantané, pas de polling
});
```

**Économies :**
- Polling : 30 requêtes/minute × 100 users = 3000 req/min
- WebSocket : 1 connexion persistante par user = 100 connexions

---

## 4. Le Proxy - Rôle et Responsabilités

### 4.1 Pourquoi un proxy ?

**Problème sans proxy :**

```
❌ Laravel essaie de se connecter directement à Voyager

- PHP n'est pas fait pour les connexions TCP persistantes
- Chaque requête = nouvelle connexion TCP (lent)
- Pas de gestion événements temps réel native
- Surcharge Voyager (100 users = 100 connexions TCP)
- Heartbeat compliqué à gérer en PHP
- Pas de WebSocket natif dans Laravel
```

**Solution avec proxy :**

```
✅ Proxy Node.js comme pont

- 1 seule connexion TCP au Voyager (partagée)
- Node.js = expert des connexions persistantes
- Event-driven architecture (parfait pour Voyager)
- Cache l'état → Laravel = réponses instantanées
- WebSocket natif (Socket.IO)
- Heartbeat automatique géré
- Reconnexion automatique
```

### 4.2 Responsabilités exactes

**Le proxy EST responsable de :**

✅ **Connexion TCP**
- Établir et maintenir la connexion à Voyager
- Heartbeat (Polling event) toutes les 5s
- Gérer les timeouts (15s sans réponse = reconnexion)
- Retry automatique avec backoff

✅ **Authentification**
- Encoder credentials en Base64
- Envoyer `AuthenticateUserBase`
- Stocker session authentifiée

✅ **Gestion des événements**
- Recevoir tous les events Voyager
- Parser le JSON (chaque ligne = 1 event)
- Enrichir les données (ajouter infos lisibles)
- Mettre à jour le cache

✅ **API REST**
- Authentifier les requêtes (API Key)
- Valider les paramètres
- Router vers la bonne commande
- Retourner réponse formatée

✅ **WebSocket**
- Accepter connexions clients
- Broadcaster événements Voyager
- Gérer rooms (par réservation)
- Ping/pong keepalive

✅ **Cache et État**
- Stocker dernière `ControlData`
- Stocker état connexion
- Fournir `getState()` instantané

✅ **Logs et Métriques**
- Logger toutes les actions
- Collecter métriques (events, API calls)
- Rotation logs quotidienne

**Le proxy N'EST PAS responsable de :**

❌ Gérer les réservations (c'est Laravel)
❌ Gérer les crédits utilisateurs (c'est Laravel)
❌ Authentifier les users finaux (c'est Laravel)
❌ Stocker en base de données (c'est Laravel)
❌ Logique métier (c'est Laravel)
❌ Pilote directement les équipements (c'est Voyager)

---

## 5. L'API REST - Détails techniques

### 5.1 Architecture Express

```javascript
// src/api/server.js
class ApiServer {
  setupMiddleware() {
    // 1. Sécurité
    this.app.use(helmet()); // Headers sécurité

    // 2. CORS
    this.app.use(cors({
      origin: process.env.CORS_ORIGIN.split(','),
      credentials: true
    }));

    // 3. Body parsing
    this.app.use(express.json({ limit: '10mb' }));

    // 4. Rate limiting
    this.app.use('/api/', rateLimit({
      windowMs: 15 * 60 * 1000, // 15 min
      max: 100 // 100 requêtes max
    }));

    // 5. Auth middleware
    this.app.use(/^\/api\/(?!health).*/, authMiddleware);

    // 6. Attach voyager connection
    this.app.use((req, res, next) => {
      req.voyager = this.voyagerConnection;
      next();
    });
  }
}
```

### 5.2 Sécurité

**Authentification par API Key :**

```javascript
// src/api/middleware.js
export const authMiddleware = (req, res, next) => {
  const apiKey = process.env.API_KEY;

  if (!apiKey) return next(); // Pas de sécurité en dev

  const providedKey = req.headers['x-api-key'] || req.query.api_key;

  if (!providedKey) {
    return res.status(401).json({
      error: 'Unauthorized',
      message: 'API key is required'
    });
  }

  if (providedKey !== apiKey) {
    logger.warn(`Invalid API key from ${req.ip}`);
    return res.status(403).json({
      error: 'Forbidden',
      message: 'Invalid API key'
    });
  }

  next();
};
```

**Rate Limiting :**

Limite à **100 requêtes par 15 minutes** par IP pour éviter les abus.

**CORS :**

Autorise uniquement les domaines configurés (Laravel + localhost pour test).

### 5.3 Routes détaillées

**Endpoint : GET /health**

```javascript
// Pas d'auth requise
// Utilisé pour health checks (monitoring, load balancer)
{
  status: 'ok',
  timestamp: '2024-11-18T20:00:00.000Z',
  uptime: 3600, // secondes
  voyager: {
    connected: true,
    authenticated: true
  }
}
```

**Endpoint : GET /api/status/connection**

```javascript
// Retourne état connexion Voyager
{
  success: true,
  connection: {
    status: 'connected',
    connectedAt: '2024-11-18T19:00:00.000Z',
    reconnectAttempts: 0
  },
  isConnected: true,
  isAuthenticated: true,
  version: {
    VOYVersion: 'Release 2.0.14f',
    ...
  }
}
```

**Endpoint : GET /api/dashboard/state**

```javascript
// Retourne état complet système (cache)
{
  success: true,
  timestamp: '2024-11-18T20:00:00.000Z',
  data: {
    Event: 'ControlData',
    VOYSTAT: 1, // 0=STOPPED, 1=IDLE, 2=RUN, 3=ERROR
    SETUPCONN: true,
    CCDCONN: true,
    CCDTEMP: -15,
    CCDPOW: 50,
    CCDSETP: -15,
    CCDCOOL: true,
    MNTCONN: true,
    MNTPARK: false,
    MNTRA: '12:34:56',
    MNTDEC: '+45:12:34',
    MNTTRACK: true,
    AFCONN: true,
    AFPOS: 12345,
    AFTEMP: 12.5,
    SEQNAME: 'M31_LRGB',
    SEQREMAIN: '02:15:30',
    GUIDESTAT: 2, // 0=STOPPED, 1=SETTLING, 2=RUNNING
    GUIDEX: -0.25,
    GUIDEY: 0.18,
    // + données enrichies
    parsed: {
      voyagerStatus: 'IDLE',
      camera: { connected: true, temperature: -15, ... },
      mount: { ... },
      focuser: { ... },
      guiding: { ... }
    }
  }
}
```

**Endpoint : POST /api/control/abort**

```javascript
// Envoie commande RemoteAbortAction à Voyager
// Attend le résultat (timeout 30s)

Request: (vide, pas de body)

Response:
{
  success: true,
  message: 'Abort command sent',
  result: {
    Event: 'RemoteActionResult',
    UID: 'uuid...',
    ActionResultInt: 4, // OK
    parsed: {
      status: 'OK',
      statusCode: 4
    }
  }
}
```

**Endpoint : POST /api/robotarget/sets**

```javascript
// Crée un Set RoboTarget

Request:
{
  "Guid": "550e8400-e29b-41d4-a716-446655440000",
  "Name": "User_123_Booking_456",
  "ProfileName": "Default.v2y",
  "Status": 0, // 0=Enabled
  "Tag": "stellar_booking_456"
}

Response:
{
  success: true,
  message: 'Set created',
  result: {
    Event: 'RemoteActionResult',
    UID: 'uuid...',
    ActionResultInt: 4
  }
}
```

---

## 6. Le WebSocket - Temps réel

### 6.1 Architecture Socket.IO

```javascript
// src/websocket/server.js
class WebSocketServer {
  start() {
    this.io = new Server(this.httpServer, {
      cors: {
        origin: process.env.WS_CORS_ORIGIN.split(','),
        credentials: true
      },
      pingInterval: 25000, // Ping toutes les 25s
      pingTimeout: 60000,  // Timeout 60s
      transports: ['websocket', 'polling'] // Fallback
    });

    this.io.on('connection', (socket) => {
      // Client connecté
      this.handleConnection(socket);
    });
  }

  handleConnection(socket) {
    // 1. Enregistrer client
    this.clients.set(socket.id, {
      socket,
      connectedAt: new Date(),
      ip: socket.handshake.address
    });

    // 2. Envoyer état initial
    socket.emit('initialState', this.voyager.getState());

    // 3. Gérer events client
    socket.on('subscribe', (room) => socket.join(room));
    socket.on('command', (data) => this.handleCommand(socket, data));
    socket.on('disconnect', () => this.clients.delete(socket.id));
  }

  broadcast(event, data, room = null) {
    if (room) {
      this.io.to(room).emit(event, data);
    } else {
      this.io.emit(event, data); // Tous les clients
    }
  }
}
```

### 6.2 Événements client → serveur

**subscribe**
```javascript
// Client veut recevoir events d'une room spécifique
socket.emit('subscribe', 'booking_456');
// → Le client rejoint la room 'booking_456'
// → Recevra seulement les events de cette réservation
```

**command**
```javascript
// Client envoie une commande directement via WS
socket.emit('command', {
  id: 'cmd_123',
  method: 'RemoteAbortAction',
  params: {}
});

// Proxy → Voyager → Résultat
socket.on('commandResult', (result) => {
  if (result.id === 'cmd_123') {
    console.log('Abort OK');
  }
});
```

**getState**
```javascript
// Client demande état actuel
socket.emit('getState');

socket.on('state', (state) => {
  console.log(state);
});
```

### 6.3 Événements serveur → client

**initialState**
```javascript
// Envoyé immédiatement à la connexion
{
  connection: {
    status: 'connected',
    connectedAt: '...',
    reconnectAttempts: 0
  },
  version: { VOYVersion: '...', ... },
  controlData: { ... } // État actuel si disponible
}
```

**controlData**
```javascript
// Toutes les 2 secondes (si Dashboard activé)
{
  Event: 'ControlData',
  Timestamp: 1700339876.123,
  VOYSTAT: 1,
  // ... toutes les données
  parsed: {
    voyagerStatus: 'IDLE',
    camera: { ... },
    mount: { ... },
    // ... données enrichies
  }
}
```

**newJPG**
```javascript
// Quand Voyager envoie aperçu caméra
{
  Event: 'NewJPGReady',
  File: 'C:\\...\\Image.fit',
  Base64Data: '/9j/4AAQSkZJRg...', // Image encodée Base64
  HFD: 4.53,
  StarIndex: 8.21,
  Expo: 1,
  Filter: 'L',
  parsed: {
    filename: 'Image.fit',
    imageData: '...', // Base64
    hfd: 4.53,
    // ... infos parsées
  }
}
```

**shotRunning**
```javascript
// Toutes les secondes pendant exposition
{
  Event: 'ShotRunning',
  Remain: 3.5, // Temps restant
  Total: 5.0,  // Durée totale
  parsed: {
    remaining: 3.5,
    total: 5.0,
    progress: 30 // Pourcentage
  }
}
```

**signal**
```javascript
// Changements d'état Voyager
{
  Event: 'Signal',
  Code: 501, // IDLE
  description: 'IDLE',
  // Codes importants :
  // 501 = IDLE (prêt)
  // 502 = Action en cours
  // 503 = Action arrêtée
  // 18 = Shot en cours
}
```

**newFITReady**
```javascript
// Nouvelle image FITS sauvegardée
{
  Event: 'NewFITReady',
  File: 'C:\\...\\M31_20241118_203045.fit',
  Type: 0, // 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT
  VoyType: 'SHOT',
  SeqTarget: 'M31',
  parsed: {
    filename: 'M31_20241118_203045.fit',
    type: 'LIGHT',
    target: 'M31'
  }
}
```

**remoteActionResult**
```javascript
// Résultat d'une commande
{
  Event: 'RemoteActionResult',
  UID: 'uuid...',
  ActionResultInt: 4,
  Motivo: '',
  parsed: {
    uid: 'uuid...',
    status: 'OK',
    statusCode: 4
  }
}
```

**connectionState**
```javascript
// État connexion Voyager change
{
  status: 'connected', // ou 'disconnected'
  connectedAt: '...',
  reconnectAttempts: 0
}
```

---

## 7. Connexion TCP/IP à Voyager

### 7.1 Protocole JSON-RPC 2.0

**Format des messages :**

```javascript
// COMMANDE (Client → Serveur)
{
  "method": "NomDeLaMethode",
  "params": {
    "UID": "uuid-unique",
    "Param1": "valeur1",
    "Param2": 123
  },
  "id": 1234567890
}

// ÉVÉNEMENT (Serveur → Client)
{
  "Event": "NomEvenement",
  "Timestamp": 1700339876.123,
  "Host": "hal9000",
  "Inst": 1,
  "Data1": "valeur",
  "Data2": 456
}

// RÉPONSE COMMANDE (Serveur → Client)
{
  "jsonrpc": "2.0",
  "result": { ... },
  "id": 1234567890
}
```

**Terminateur de ligne :** Chaque message termine par `\r\n`

### 7.2 Connexion et Heartbeat

```javascript
// src/voyager/connection.js
class VoyagerConnection {
  async connect() {
    return new Promise((resolve, reject) => {
      this.socket = new net.Socket();
      this.socket.setEncoding('utf8');

      // 1. Connexion TCP
      this.socket.connect(this.config.port, this.config.host, () => {
        console.log('TCP connected');
      });

      // 2. Réception Version event (auto)
      this.socket.on('data', (data) => {
        const lines = data.split('\r\n');
        for (const line of lines) {
          if (line.trim()) {
            const message = JSON.parse(line);

            if (message.Event === 'Version') {
              // 3. Authentification si requise
              if (this.config.auth.enabled) {
                this.authenticate().then(resolve).catch(reject);
              } else {
                resolve();
              }

              // 4. Démarrer heartbeat
              this.startHeartbeat();
            }
          }
        }
      });
    });
  }

  startHeartbeat() {
    this.heartbeatTimer = setInterval(() => {
      // Envoyer Polling event
      this.send({
        Event: 'Polling',
        Timestamp: Date.now() / 1000,
        Host: os.hostname(),
        Inst: 1
      });

      // Vérifier timeout
      const timeSinceLastData = Date.now() - this.lastDataReceived;
      if (timeSinceLastData > 15000) {
        console.error('Timeout - reconnecting');
        this.handleDisconnect('timeout');
      }
    }, 5000); // Toutes les 5s
  }
}
```

**Flux heartbeat :**

```
Client                      Voyager
  │                           │
  ├──── Polling ─────────────►│  (t=0s)
  │                           │
  │◄──── Polling ─────────────┤  (t=0.1s)
  │                           │
  ├──── Polling ─────────────►│  (t=5s)
  │                           │
  │◄──── Polling ─────────────┤  (t=5.1s)
  │                           │
  │         ...               │
  │                           │
  │  (Pas de réponse >15s)    │
  │                           │
  ├─ Timeout détecté          │
  ├─ Reconnexion              │
  │                           │
```

### 7.3 Authentification Base64

```javascript
async authenticate() {
  const credentials = `${this.username}:${this.password}`;
  const base64 = Buffer.from(credentials).toString('base64');

  const authCommand = {
    method: 'AuthenticateUserBase',
    params: {
      UID: uuidv4(),
      Base: base64
    },
    id: 1
  };

  this.send(authCommand);

  // Attendre réponse avec id=1
  return new Promise((resolve, reject) => {
    this.socket.on('data', (data) => {
      const response = JSON.parse(data);
      if (response.id === 1) {
        if (response.authbase) {
          console.log('Authenticated as', response.authbase.Username);
          resolve(response.authbase);
        } else {
          reject(new Error('Auth failed'));
        }
      }
    });
  });
}
```

---

## 8. Commandes détaillées

Voici TOUTES les commandes disponibles avec leur but exact.

### 8.1 Commandes de Contrôle

**RemoteAbortAction**
```javascript
// BUT : Arrêter immédiatement toute action en cours
// QUAND : L'utilisateur clique "Abort" pendant une exposition
// EFFET : Stop exposition, mouvement, autofocus, etc.

await proxy.commands.abort();

// Voyager → Arrête tout → Signal 503 (Action Stopped)
```

**RemoteSetDashboardMode**
```javascript
// BUT : Activer le mode Dashboard dans Voyager
// QUAND : Au démarrage du proxy
// EFFET : Voyager envoie ControlData toutes les 2s

await proxy.commands.setDashboardMode(true);

// Voyager → Active Dashboard → ControlData toutes les 2s
```

**RemoteTakeShot**
```javascript
// BUT : Prendre une photo test
// QUAND : Test caméra, prévisualisation
// EFFET : Exposition + sauvegarde FITS

await proxy.commands.takeShot(
  exposure: 1,    // Durée en secondes
  binning: 1,     // 1x1, 2x2, etc.
  filterIndex: 0  // Index filtre (0=L, 1=R, 2=G, 3=B, ...)
);

// Voyager → Exposition → ShotRunning → NewFITReady
```

### 8.2 Commandes Télescope

**RemotePark**
```javascript
// BUT : Parquer le télescope (position sécurité)
// QUAND : Fin de session, météo mauvaise
// EFFET : Télescope va en position park

await proxy.commands.park();

// Monture → Position park → MNTPARK = true
```

**RemoteUnpark**
```javascript
// BUT : Sortir le télescope du park
// QUAND : Début d'observation
// EFFET : Télescope prêt à bouger

await proxy.commands.unpark();

// Monture → Unpark → MNTPARK = false
```

**RemoteSetTracking**
```javascript
// BUT : Activer/désactiver le suivi stellaire
// QUAND : Observer (tracking ON), pointer (tracking OFF)
// EFFET : Monture suit rotation Terre

await proxy.commands.startTracking(); // Val: true
await proxy.commands.stopTracking();  // Val: false

// Monture → Suivi ON/OFF → MNTTRACK = true/false
```

### 8.3 Commandes Caméra

**RemoteCoolCamera**
```javascript
// BUT : Refroidir la caméra à température cible
// QUAND : Début observation (réduit bruit thermique)
// EFFET : TEC (refroidisseur) allumé

await proxy.commands.coolCamera(-15); // -15°C

// Caméra → Cooling ON → CCDTEMP descend progressivement
```

**RemoteWarmCamera**
```javascript
// BUT : Réchauffer la caméra
// QUAND : Fin observation (éviter condensation)
// EFFET : TEC arrêté, température remonte

await proxy.commands.warmCamera();

// Caméra → Cooling OFF → CCDTEMP remonte
```

### 8.4 Commandes Utilitaires

**RemoteAutoFocus**
```javascript
// BUT : Mise au point automatique
// QUAND : Changement température, filtre, début session
// EFFET : Série de poses pour trouver focus optimal

await proxy.commands.autofocus();

// Focuser → Séquence autofocus → AFPOS ajusté → Signal 5 → OK/Erreur
```

**RemotePlateSolve**
```javascript
// BUT : Résoudre position exacte du télescope (astrométrie)
// QUAND : Vérifier pointage, synchroniser
// EFFET : Photo + analyse étoiles → RA/DEC précises

await proxy.commands.platesolve();

// Caméra → Photo → Astrométrie → Sync monture
```

### 8.5 Commandes RoboTarget

**RoboTargetAddSet**
```javascript
// BUT : Créer un "Set" (dossier) pour organiser les cibles
// QUAND : Nouvelle réservation utilisateur
// EFFET : Conteneur logique pour grouper targets

await proxy.commands.addSet({
  Guid: 'uuid-du-set',
  Name: 'User_123_Booking_456',
  ProfileName: 'Default.v2y',
  Status: 0, // 0=Enabled, 1=Disabled
  Tag: 'stellar_booking_456'
});

// RoboTarget → Set créé
```

**RoboTargetAddTarget**
```javascript
// BUT : Ajouter une cible à observer
// QUAND : Configuration observation utilisateur
// EFFET : Cible ajoutée au scheduler

await proxy.commands.addTarget({
  GuidTarget: 'uuid-target',
  RefGuidSet: 'uuid-du-set',
  RefGuidBaseSequence: 'uuid-sequence',
  TargetName: 'M31 - Andromeda',
  RAJ2000: '00:42:44.3',  // Ascension droite J2000
  DECJ2000: '+41:16:09',  // Déclinaison J2000
  PA: 0,                   // Angle position
  DateCreation: Date.now() / 1000,
  Status: 0,               // 0=Active
  Priority: 2,             // 1=Low, 2=Normal, 3=High
  IsRepeat: true,
  Repeat: 1,               // Nombre répétitions
  // Contraintes
  C_Mask: 'BDE',           // B=AltMin, D=HAStart, E=HAEnd
  C_AltMin: 30,            // Altitude minimum (degrés)
  C_HAStart: -3,           // Heure angle début
  C_HAEnd: 3,              // Heure angle fin
  C_DateStart: startTimestamp,
  C_DateEnd: endTimestamp
});

// RoboTarget → Target ajouté → Scheduler peut le prendre
```

**RoboTargetAddShot**
```javascript
// BUT : Définir une prise de vue (filtre, exposition, quantité)
// QUAND : Plan d'observation utilisateur
// EFFET : Shot ajouté à la séquence

await proxy.commands.addShot({
  GuidShot: 'uuid-shot',
  RefGuidTarget: 'uuid-target',
  FilterIndex: 1,      // 0=L, 1=R, 2=G, 3=B, 4=Ha, 5=OIII, 6=SII
  Num: 20,             // 20 poses
  Bin: 1,              // Binning 1x1
  ReadoutMode: 0,
  Type: 0,             // 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT
  Speed: 0,
  Gain: 100,           // Gain caméra
  Offset: 50,          // Offset caméra
  Exposure: 300,       // 300 secondes (5 min)
  Order: 1,            // Ordre d'exécution
  Enabled: true
});

// RoboTarget → Shot configuré
```

**RoboTargetSetTargetStatus**
```javascript
// BUT : Activer/désactiver une cible
// QUAND : Début/fin session utilisateur
// EFFET : Scheduler inclut/exclut la cible

await proxy.commands.activateTarget('uuid-target');
// Status → 0 (Active)

await proxy.commands.deactivateTarget('uuid-target');
// Status → 1 (Inactive)

// RoboTarget → Target activée/désactivée
```

---

## 9. Événements détaillés

### 9.1 Version
**Reçu à** : Connexion TCP établie (premier événement)
**Fréquence** : Une seule fois

```javascript
{
  Event: 'Version',
  Timestamp: 1700339876.123,
  Host: 'hal9000',
  Inst: 1,
  VOYVersion: 'Release 2.0.14f - Built 2024-01-15',
  VOYSubver: '',
  MsgVersion: 1
}
```

**Utilité** :
- Confirme connexion TCP OK
- Version Voyager pour compatibilité
- Déclenche authentification (si requise)

### 9.2 Polling
**Reçu à** : Toutes les 5 secondes (heartbeat)
**Fréquence** : Toutes les 5s

```javascript
{
  Event: 'Polling',
  Timestamp: 1700339881.456,
  Host: 'hal9000',
  Inst: 1
}
```

**Utilité** :
- Keep-alive connexion
- Proxy vérifie : si pas reçu pendant 15s → timeout
- Bidirectionnel : client ET serveur envoient

### 9.3 ControlData
**Reçu à** : Toutes les 2 secondes (si Dashboard activé)
**Fréquence** : Toutes les 2s

```javascript
{
  Event: 'ControlData',
  Timestamp: 1700339876.789,
  Host: 'hal9000',
  Inst: 1,

  // TEMPS
  TI: '2024-11-18 20:31:16',      // Heure locale
  TIUTC: '2024-11-18 19:31:16',   // Heure UTC

  // VOYAGER
  VOYSTAT: 2,          // 0=STOPPED, 1=IDLE, 2=RUN, 3=ERROR
  SETUPCONN: true,     // Setup connecté

  // CAMÉRA
  CCDCONN: true,       // Caméra connectée
  CCDTEMP: -14.8,      // Température actuelle (°C)
  CCDPOW: 52,          // Puissance refroidissement (%)
  CCDSETP: -15,        // Température consigne (°C)
  CCDCOOL: true,       // Cooling activé

  // MONTURE
  MNTCONN: true,       // Monture connectée
  MNTPARK: false,      // Parkée
  MNTRA: '12:34:56',   // Ascension droite actuelle
  MNTDEC: '+45:12:34', // Déclinaison actuelle
  MNTTRACK: true,      // Suivi activé

  // FOCUSER
  AFCONN: true,        // Focuser connecté
  AFPOS: 12345,        // Position actuelle
  AFTEMP: 12.5,        // Température focuser

  // SÉQUENCE
  SEQNAME: 'M31_LRGB', // Nom séquence en cours
  SEQREMAIN: '02:15:30', // Temps restant

  // GUIDAGE
  GUIDESTAT: 2,        // 0=STOPPED, 1=SETTLING, 2=RUNNING
  GUIDEX: -0.259,      // Erreur RMS X (arcsec)
  GUIDEY: 0.039,       // Erreur RMS Y (arcsec)

  // ROTATEUR
  ROTCONN: false,      // Rotateur connecté
  ROTPOS: 0,           // Position rotateur

  // MÉTÉO
  ALLSKYTEMP: 15.2,    // Température extérieure
  ALLSKYHUM: 65,       // Humidité (%)
  ALLSKYWIND: 5,       // Vent (km/h)
  SAFE: true,          // Conditions sûres

  // Valeurs spéciales :
  // -123456789 = OFF (non utilisé)
  // +123456789 = ERROR (erreur/non disponible)
}
```

**Utilité** :
- **État complet du système** en temps réel
- Dashboard UI temps réel
- Détection problèmes (température, guidage, etc.)
- Mise à jour cache proxy

### 9.4 Signal
**Reçu à** : Changement d'état système
**Fréquence** : Sur événement

```javascript
{
  Event: 'Signal',
  Timestamp: 1700339876.999,
  Host: 'hal9000',
  Inst: 1,
  Code: 502
}
```

**Codes importants** :

| Code | Signification | Quand |
|------|---------------|-------|
| 1 | Autofocus Error | Autofocus échoué |
| 2 | Action Queue Empty | File d'actions vide |
| 5 | Autofocus Running | Autofocus en cours |
| 18 | Shot Running | Prise de vue en cours |
| 500 | General Error | Erreur générale |
| 501 | IDLE | Prêt, rien en cours |
| 502 | Action Running | Action en cours |
| 503 | Action Stopped | Action arrêtée |

**Utilité** :
- Notifications changements d'état
- Afficher dans UI (badges, alertes)
- Déclencher actions (ex: Signal 503 après abort)

### 9.5 NewFITReady
**Reçu à** : Image FITS sauvegardée sur disque
**Fréquence** : À chaque image

```javascript
{
  Event: 'NewFITReady',
  Timestamp: 1700339880.123,
  Host: 'hal9000',
  Inst: 1,
  File: 'C:\\Users\\astro\\Voyager\\FIT\\M31_20241118_203045.fit',
  Type: 0,           // 0=LIGHT, 1=BIAS, 2=DARK, 3=FLAT
  VoyType: 'SHOT',   // TEST, SHOT, SYNC
  SeqTarget: 'M31'   // Nom cible
}
```

**Utilité** :
- Notifier utilisateur : nouvelle image capturée
- Incrémenter compteur images
- Déclencher job Laravel : copier FITS, générer preview
- Mise à jour progression séquence

### 9.6 NewJPGReady
**Reçu à** : Preview JPG généré (Mode Dashboard uniquement)
**Fréquence** : À chaque image (si Dashboard actif)

```javascript
{
  Event: 'NewJPGReady',
  Timestamp: 1700339880.456,
  Host: 'hal9000',
  Inst: 1,
  File: 'C:\\...\\M31_20241118_203045.fit',
  SequenceTarget: 'M31',
  TimeInfo: 1700339879.123,
  Expo: 300,           // Exposition (secondes)
  Bin: 1,              // Binning
  Filter: 'Ha',        // Filtre utilisé
  HFD: 4.53,           // Half Flux Diameter (qualité focus)
  StarIndex: 8.21,     // Indice qualité étoiles
  PixelDimX: 4656,     // Largeur image
  PixelDimY: 3520,     // Hauteur image
  Base64Data: '/9j/4AAQSkZJRgABA...' // Image encodée Base64 (JPG)
}
```

**Utilité** :
- Afficher preview temps réel dans navigateur
- Pas besoin de télécharger FITS (lourd)
- Vérifier focus (HFD), qualité (StarIndex)
- Feedback visuel immédiat utilisateur

**Exemple affichage :**
```html
<img src="data:image/jpeg;base64,/9j/4AAQSkZJRgABA..." />
```

### 9.7 ShotRunning
**Reçu à** : Pendant exposition
**Fréquence** : Toutes les secondes pendant exposition

```javascript
{
  Event: 'ShotRunning',
  Timestamp: 1700339881.789,
  Host: 'hal9000',
  Inst: 1,
  Remain: 287,  // Secondes restantes
  Total: 300    // Durée totale
}
```

**Utilité** :
- Progress bar exposition temps réel
- Calcul pourcentage : `(Total - Remain) / Total * 100`
- Animation UI (compte à rebours)

**Exemple UI :**
```javascript
const progress = ((300 - 287) / 300) * 100; // 4.3%
progressBar.style.width = progress + '%';
countdown.textContent = formatTime(287); // "04:47"
```

### 9.8 RemoteActionResult
**Reçu à** : Après exécution commande
**Fréquence** : Pour chaque commande envoyée

```javascript
{
  Event: 'RemoteActionResult',
  Timestamp: 1700339882.123,
  Host: 'hal9000',
  Inst: 1,
  UID: '550e8400-e29b-41d4-a716-446655440000', // UUID commande
  ActionResultInt: 4,   // Code résultat
  Motivo: '',           // Raison si erreur
  ParamRet: {           // Paramètres retournés (optionnel)
    DownloadAndSaveTime: 3.07
  }
}
```

**Codes résultat** :

| Code | État | Signification |
|------|------|---------------|
| 0 | NEED_INIT | En attente initialisation |
| 1 | READY | Prêt à exécuter |
| 2 | RUNNING | En cours d'exécution |
| 4 | OK | ✅ Succès |
| 5 | ERROR | ❌ Erreur |
| 6 | ABORTING | Annulation en cours |
| 7 | ABORTED | Annulé |
| 8 | TIMEOUT | Timeout |
| 10 | OK_PARTIAL | Succès partiel |

**Utilité** :
- Résoudre Promise commande
- Afficher succès/erreur utilisateur
- Gérer retry si erreur
- Logger résultats

**Matching UID :**
```javascript
// Envoi commande
const uid = uuidv4();
send({ method: 'RemoteAbortAction', params: { UID: uid } });

// Réception résultat
onRemoteActionResult(event) {
  if (event.UID === uid) {
    if (event.ActionResultInt === 4) {
      console.log('Abort OK');
    } else {
      console.error('Abort failed:', event.Motivo);
    }
  }
}
```

### 9.9 ShutDown
**Reçu à** : Voyager va se fermer
**Fréquence** : Une fois (avant extinction)

```javascript
{
  Event: 'ShutDown',
  Timestamp: 1700339900.000,
  Host: 'hal9000',
  Inst: 1
}
```

**Utilité** :
- Déconnecter proprement
- Notifier utilisateurs
- Passer en mode "Voyager offline"
- Attendre redémarrage avant reconnexion

**Action proxy :**
```javascript
onShutDown() {
  logger.warn('Voyager shutting down!');
  this.disconnect(); // Fermer socket proprement
  // Ne PAS retry immédiatement
}
```

---

## 10. Cas d'usage concrets

### Cas 1 : Utilisateur démarre sa session

**Contexte** : Utilisateur a réservé 20h-22h, on est 19h50

**Laravel (J-1) :**
1. Job `PrepareObservationJob` s'exécute
2. Appelle proxy : `POST /api/robotarget/sets` → Crée Set
3. Appelle proxy : `POST /api/robotarget/targets` → Crée Target
4. Pour chaque shot : `POST /api/robotarget/shots`
5. Sauvegarde GUIDs en base : `voyager_set_guid`, `voyager_target_guid`

**Laravel (20h00) :**
1. Job `StartObservationJob` s'exécute
2. Appelle proxy : `POST /api/robotarget/targets/{guid}/activate`
3. RoboTarget scheduler → Prend en charge la cible
4. Voyager → Commence observation automatiquement

**Utilisateur (20h05) :**
1. Ouvre page `/bookings/{id}/access`
2. Page charge : Alpine.js `bookingControlPanel`
3. JavaScript connecte WebSocket : `io('https://proxy.domain.com')`
4. WebSocket → `socket.emit('subscribe', 'booking_456')`
5. Reçoit `initialState` → Affiche dashboard initial
6. Toutes les 2s : `controlData` → Dashboard se met à jour
7. Voit : Température caméra, position monture, progression séquence

**Pendant exposition (20h15) :**
1. Voyager → Démarre exposition 300s
2. Proxy reçoit : `Signal` code 18 (Shot Running)
3. Proxy broadcast : `shotRunning` toutes les 1s
4. UI utilisateur : Progress bar 0% → 100%
5. Fin exposition : `NewFITReady` event
6. Proxy broadcast : `newFITReady`
7. Laravel Job : `ProcessNewImageJob` copie FITS, génère preview
8. UI : Notification "Image capturée !", compteur +1

**Utilisateur clique Abort (20h45) :**
1. Click bouton "Arrêter" dans UI
2. JavaScript : `fetch('/api/control/abort', { method: 'POST' })`
3. Laravel proxy : Vérifie API Key → OK
4. Proxy : `commands.abort()` → Envoie JSON-RPC à Voyager
5. Voyager : Arrête exposition, retourne `RemoteActionResult` code 4
6. Proxy : Résout Promise, retourne à Laravel
7. Laravel : `{ success: true }`
8. UI : Notification "Session arrêtée"
9. Proxy broadcast : `Signal` code 503 (Action Stopped)
10. Tous les users connectés : Voient le signal

**Fin session (22h00) :**
1. Job `EndObservationJob` s'exécute
2. Appelle proxy : `POST /api/robotarget/targets/{guid}/deactivate`
3. RoboTarget → Désactive la cible
4. Laravel : Génère rapport session, envoie email
5. UI : Message "Session terminée"

---

### Cas 2 : Voyager crash et redémarre

**Avant crash :**
- Proxy connecté, Dashboard actif
- 5 utilisateurs regardent UI temps réel
- Observations en cours

**Voyager crash (panne électrique) :**
1. Proxy : Socket `error` event
2. Proxy : `handleDisconnect('error')`
3. Proxy : Marque `isConnected = false`
4. Proxy broadcast : `connectionState` → `{ status: 'disconnected' }`
5. UI utilisateurs : Badge "Voyager: Déconnecté" devient rouge
6. Proxy : Démarre reconnexion (tentative 1/10 dans 5s)

**Reconnexion (tentative 1 à 5min) :**
```
Tentative 1 (5s)   : ❌ Connection refused
Tentative 2 (10s)  : ❌ Connection refused
Tentative 3 (15s)  : ❌ Connection refused
...
```

**Voyager redémarre (8min) :**
1. Voyager : Serveur TCP écoute sur port 5950
2. Proxy tentative 6 : ✅ TCP connected
3. Proxy reçoit : `Version` event
4. Proxy : Authentifie (Base64)
5. Proxy : ✅ Authenticated
6. Proxy : Démarre heartbeat
7. Proxy : Active Dashboard mode
8. Proxy : `isConnected = true`, `reconnectAttempts = 0`
9. Proxy broadcast : `connectionState` → `{ status: 'connected' }`
10. UI utilisateurs : Badge "Voyager: Connecté" redevient vert
11. Proxy : Reçoit `ControlData` → Dashboard fonctionnel

**Résultat :**
- ✅ Reconnexion automatique
- ✅ Aucune intervention manuelle
- ✅ Users informés en temps réel
- ✅ Observations reprennent (RoboTarget gère)

---

## 📚 Résumé pour développeur

### Tu dois retenir

**Le proxy est un PONT intelligent entre Laravel et Voyager qui :**

1. **Maintient UNE connexion TCP persistante** (partagée par tous les users)
2. **Reçoit les événements** Voyager en temps réel (12 types)
3. **Parse et enrichit** les données brutes
4. **Cache l'état actuel** pour réponses instantanées
5. **Expose API REST** pour Laravel (25+ endpoints)
6. **Broadcast WebSocket** pour UI temps réel
7. **Gère heartbeat** automatique (5s)
8. **Reconnecte automatiquement** si problème
9. **Log tout** pour debug

**Laravel NE doit PAS :**
- ❌ Se connecter directement à Voyager (trop lourd, pas fait pour ça)
- ❌ Gérer les événements temps réel (c'est le proxy)
- ❌ Maintenir connexion TCP (PHP pas adapté)

**Laravel DOIT :**
- ✅ Appeler l'API REST du proxy
- ✅ Utiliser WebSocket pour temps réel (Laravel Echo)
- ✅ Gérer la logique métier (réservations, crédits, users)
- ✅ Traiter les événements reçus (ex: nouvelle image → job)

**L'interface de test sert à :**
- ✅ Valider le proxy fonctionne
- ✅ Tester toutes les commandes
- ✅ Voir les événements temps réel
- ✅ Débugger avant intégration Laravel

---

**Fichier créé** : `docs/architecture-technique-voyager-proxy.md`

Maintenant je vais améliorer l'interface de test avec des explications sur chaque commande...

---
