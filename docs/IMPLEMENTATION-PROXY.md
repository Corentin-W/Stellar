# 🌐 Guide d'implémentation Proxy Node.js - RoboTarget

> **✅ IMPLÉMENTATION COMPLÉTÉE**
> **Version:** 2.0.0
> **Date:** 12 Décembre 2025

---

## 📋 Table des matières

1. [Statut d'implémentation](#statut-dimplémentation)
2. [Architecture](#architecture)
3. [Fichiers créés](#fichiers-créés)
4. [Routes REST API](#routes-rest-api)
5. [Commandes Voyager](#commandes-voyager)
6. [Event Handlers](#event-handlers)
7. [WebSocket Events](#websocket-events)
8. [Configuration](#configuration)
9. [Tests](#tests)

---

## Statut d'implémentation

### ✅ Phase 2 : Proxy Node.js - TERMINÉE

| Composant | Statut | Fichier | Lignes |
|-----------|--------|---------|--------|
| Routes RoboTarget | ✅ Complété | `src/api/robotarget/routes.js` | 272 |
| Validators | ✅ Complété | `src/api/robotarget/validators.js` | 144 |
| Commands RoboTarget | ✅ Complété | `src/voyager/robotarget/commands.js` | 197 |
| Event Handlers | ✅ Complété | `src/voyager/robotarget/events.js` | 326 |
| Event Integration | ✅ Complété | `src/voyager/events.js` | +40 |
| Main Integration | ✅ Complété | `src/index.js` | +50 |
| Configuration | ✅ Complété | `.env.example` | +3 |

**Total ajouté:** ~1,029 lignes de code

---

## Architecture

### Flux de données complet

```
┌─────────────────────────────────────────────────────────────┐
│                        LARAVEL API                           │
│                     (Backend PHP)                            │
│  POST /api/robotarget/targets                                │
│  GET  /api/robotarget/targets/:guid/progress                 │
└────────────────────┬────────────────────▲───────────────────┘
                     │                     │
              HTTP REST                Webhook
                     │                     │
┌────────────────────▼─────────────────────┼───────────────────┐
│                    VOYAGER PROXY                             │
│                   (Node.js Express)                          │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │         REST API Routes (/api/robotarget/*)         │    │
│  │  • POST /sets - Create Set                          │    │
│  │  • POST /targets - Create Target                    │    │
│  │  • POST /shots - Add Shot                           │    │
│  │  • PUT /targets/:guid/status - Activate/Deactivate  │    │
│  │  • GET /sessions/:guid/result - Get Results         │    │
│  │  • GET /targets/:guid/progress - Live Progress      │    │
│  │  • DELETE /targets/:guid - Remove Target            │    │
│  └───────────────┬─────────────────────────────────────┘    │
│                  │                                            │
│  ┌───────────────▼─────────────────────────────────────┐    │
│  │            RoboTargetCommands                        │    │
│  │  • addSet()                                          │    │
│  │  • addTarget()                                       │    │
│  │  • addShot()                                         │    │
│  │  • setTargetStatus()                                 │    │
│  │  • getSessionListByTarget()                          │    │
│  │  • createCompleteTarget()                            │    │
│  │  • deleteCompleteTarget()                            │    │
│  └───────────────┬─────────────────────────────────────┘    │
│                  │                                            │
│  ┌───────────────▼─────────────────────────────────────┐    │
│  │          Voyager Connection (TCP JSON-RPC)          │    │
│  └─────────────────────────────┬───────────────────────┘    │
│                                 │                             │
│                        ┌────────▼────────┐                   │
│                        │  Event Handler  │                   │
│                        │  • SessionStart │                   │
│                        │  • Progress     │◄──┐               │
│                        │  • SessionEnd   │   │               │
│                        │  • Error        │   │               │
│                        └────────┬────────┘   │               │
│                                 │             │               │
│           ┌─────────────────────┼─────────────┘               │
│           │ WebSocket Broadcast │ Webhook to Laravel          │
│           │                     │                             │
│  ┌────────▼─────────┐  ┌────────▼──────────┐                │
│  │   Socket.IO      │  │  RoboTarget Event │                │
│  │   Broadcasting   │  │     Handler       │                │
│  └──────────────────┘  │  • notifyLaravel()│                │
│                        └───────────────────┘                 │
└──────────────────────────────────────────────────────────────┘
                              │
                     JSON-RPC 2.0 / TCP
                              │
                    ┌─────────▼──────────┐
                    │   VOYAGER SERVER   │
                    │  (Windows, C++)    │
                    │   • RoboTarget     │
                    │   • Scheduler      │
                    └────────────────────┘
```

---

## Fichiers créés

### 1. Routes REST API

**`src/api/robotarget/routes.js`** (272 lignes)

Endpoints implémentés:

```javascript
POST   /api/robotarget/sets                      // Create Set
POST   /api/robotarget/targets                   // Create Target
POST   /api/robotarget/shots                     // Add Shot
PUT    /api/robotarget/targets/:guid/status      // Toggle Active
GET    /api/robotarget/sessions/:targetGuid/result  // Get Results
GET    /api/robotarget/targets/:guid/progress    // Live Progress
DELETE /api/robotarget/targets/:guid             // Delete Target
```

**Features:**
- ✅ Validation des payloads
- ✅ Génération automatique C_Mask
- ✅ Error handling complet
- ✅ Logging détaillé
- ✅ Support ES6 modules

### 2. Validators

**`src/api/robotarget/validators.js`** (144 lignes)

```javascript
// Exported validators
validateSet(req, res, next)
validateTarget(req, res, next)
validateShot(req, res, next)
```

**Validations:**
- ✅ UUID format (RFC 4122)
- ✅ RA format: `HH:MM:SS` (00:00:00 → 23:59:59)
- ✅ DEC format: `±DD:MM:SS` (-90:00:00 → +90:00:00)
- ✅ Priority range: 0-4
- ✅ FilterIndex range: 0-20
- ✅ Exposure range: 0.1-3600s
- ✅ Num range: 1-1000

### 3. RoboTarget Commands

**`src/voyager/robotarget/commands.js`** (197 lignes)

Classe étendue avec méthodes spécifiques:

```javascript
class RoboTargetCommands extends Commands {
  // Status management
  setTargetStatus(data)                    // Active/Inactive

  // Session queries
  getSessionListByTarget(data)             // Get all sessions
  getActiveTargets()                       // List active
  getTargetDetails(guid)                   // Get one target
  getShotsForTarget(targetGuid)           // List shots

  // Set management
  clearSet(setGuid)                        // Clear all targets

  // Bulk operations
  createCompleteTarget(data)               // Set + Target + Shots
  deleteCompleteTarget(targetGuid)        // Delete all + shots

  // Progress tracking
  getTargetProgress(targetGuid)           // Real-time progress

  // Scheduler control
  setSchedulerPaused(pause)               // Pause/Resume
  forceStartTarget(targetGuid)            // Force immediate start
}
```

**Héritage de `Commands`:**
- ✅ `addSet(data)`
- ✅ `updateSet(data)`
- ✅ `deleteSet(guid)`
- ✅ `addTarget(data)`
- ✅ `updateTarget(data)`
- ✅ `deleteTarget(guid)`
- ✅ `addShot(data)`
- ✅ `updateShot(data)`
- ✅ `deleteShot(guid)`

### 4. Event Handlers

**`src/voyager/robotarget/events.js`** (326 lignes)

```javascript
class RoboTargetEventHandler {
  // Event handlers
  handleSessionStart(message)         // Target execution started
  handleSessionComplete(message)      // Target completed (OK/Error/Abort)
  handleSessionAbort(message)         // User aborted
  handleProgress(message)             // Real-time progress updates
  handleShotComplete(message)         // Single shot completed
  handleError(message)                // Error occurred

  // State tracking
  getCurrentState()                   // Get current execution state
  resetState()                        // Reset for testing

  // Laravel integration
  notifyLaravel(eventType, data)     // Webhook to Laravel
}
```

**État trackés:**
```javascript
{
  guidTarget: string | null,
  guidSession: string | null,
  guidSet: string | null,
  startTime: Date | null,
  shotCount: number,
  status: 'idle' | 'running' | 'completed' | 'error' | 'aborted'
}
```

**Webhooks envoyés:**
- `POST /api/webhooks/robotarget/session-start`
- `POST /api/webhooks/robotarget/session-complete` ⚠️ **CRITICAL - Credits**
- `POST /api/webhooks/robotarget/session-abort`
- `POST /api/webhooks/robotarget/session-error`

### 5. Integration dans events.js

**`src/voyager/events.js`** (modifié +40 lignes)

Ajout des handlers RoboTarget:

```javascript
class EventHandler {
  // Nouveaux handlers
  handleRoboTargetSessionStart(message)
  handleRoboTargetSessionComplete(message)
  handleRoboTargetSessionAbort(message)
  handleRoboTargetProgress(message)
  handleRoboTargetShotComplete(message)
  handleRoboTargetError(message)
}
```

### 6. Main Integration

**`src/index.js`** (modifié +50 lignes)

```javascript
class VoyagerProxy {
  constructor() {
    // ...
    this.roboTargetEventHandler = null;  // NEW
  }

  async start() {
    // Initialize RoboTarget Event Handler
    this.roboTargetEventHandler = new RoboTargetEventHandler(
      this.voyagerConnection,
      process.env.LARAVEL_API_URL,
      process.env.VOYAGER_WEBHOOK_SECRET
    );
    this.roboTargetEventHandler.register();

    // Setup event forwarding (6 new events)
    this.voyagerConnection.on('roboTargetSessionStart', ...);
    this.voyagerConnection.on('roboTargetSessionComplete', ...);
    this.voyagerConnection.on('roboTargetSessionAbort', ...);
    this.voyagerConnection.on('roboTargetProgress', ...);
    this.voyagerConnection.on('roboTargetShotComplete', ...);
    this.voyagerConnection.on('roboTargetError', ...);
  }
}
```

---

## Routes REST API

### POST /api/robotarget/sets

Crée un nouveau Set RoboTarget.

**Request:**
```json
{
  "guid_set": "550e8400-e29b-41d4-a716-446655440000",
  "set_name": "NGC Objects December"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Set créé avec succès",
  "result": { /* Voyager response */ }
}
```

### POST /api/robotarget/targets

Crée une nouvelle Target avec shots.

**Request:**
```json
{
  "GuidTarget": "uuid-v4",
  "RefGuidSet": "uuid-v4",
  "TargetName": "M31 - Andromeda",
  "RAJ2000": "00:42:44",
  "DECJ2000": "+41:16:09",
  "Priority": 2,
  "C_AltMin": 30,
  "C_MoonDown": true,
  "C_HFDMeanLimit": 2.5,
  "Shots": [
    {
      "FilterIndex": 0,
      "Exposure": 300,
      "Num": 20,
      "Gain": 100,
      "Offset": 50,
      "Bin": 1
    }
  ]
}
```

**C_Mask auto-généré:**
```
B  = AltMin always present
K  = MoonDown (if C_MoonDown = true)
O  = HFD Limit (if C_HFDMeanLimit > 0)

Example: "BKO" = AltMin + MoonDown + HFD
```

**Response:**
```json
{
  "success": true,
  "message": "Target créée avec succès",
  "result": { /* ... */ },
  "shots_added": 1
}
```

### PUT /api/robotarget/targets/:guid/status

Active ou désactive une Target.

**Request:**
```json
{
  "status": "active"  // or "inactive"
}
```

### GET /api/robotarget/sessions/:targetGuid/result

Récupère les résultats de sessions.

**Response:**
```json
{
  "success": true,
  "sessions": [
    {
      "GuidSession": "uuid",
      "Result": 1,  // 1=OK, 2=Aborted, 3=Error
      "HFDMean": 2.3,
      "ImagesCaptured": 18,
      "SessionStart": "2025-12-12T20:00:00Z",
      "SessionEnd": "2025-12-12T22:30:00Z"
    }
  ]
}
```

### GET /api/robotarget/targets/:guid/progress

Progression temps réel.

**Response:**
```json
{
  "success": true,
  "progress": {
    "guid": "uuid",
    "sequence_name": "M31",
    "sequence_progress": 45,
    "current_image": 9,
    "total_images": 20,
    "current_filter": "Luminance",
    "hfd": 2.1,
    "is_running": true
  }
}
```

---

## Commandes Voyager

### Méthodes JSON-RPC appelées

| Méthode | Commande Voyager | Paramètres |
|---------|------------------|------------|
| `addSet()` | `RoboTargetAddSet` | `{ GuidSet, SetName }` |
| `addTarget()` | `RoboTargetAddTarget` | `{ Target: JSON }` |
| `addShot()` | `RoboTargetAddShot` | `{ Shot: JSON }` |
| `setTargetStatus()` | `RoboTargetSetTargetStatus` | `{ GuidTarget, Status }` |
| `getSessionListByTarget()` | `RoboTargetGetSessionListByTarget` | `{ GuidTarget }` |
| `activateTarget()` | `RoboTargetSetTargetStatus` | `{ GuidTarget, Status: 0 }` |
| `deactivateTarget()` | `RoboTargetSetTargetStatus` | `{ GuidTarget, Status: 1 }` |

---

## Event Handlers

### Événements Voyager écoutés

| Événement Voyager | Handler | WebSocket Emit | Webhook |
|-------------------|---------|----------------|---------|
| `RoboTargetSessionStart` | `handleSessionStart()` | `roboTargetSessionStart` | session-start |
| `RoboTargetSessionComplete` | `handleSessionComplete()` | `roboTargetSessionComplete` | **session-complete** ⚠️ |
| `RoboTargetSessionAbort` | `handleSessionAbort()` | `roboTargetSessionAbort` | session-abort |
| `RoboTargetProgress` | `handleProgress()` | `roboTargetProgress` | - |
| `RoboTargetShotComplete` | `handleShotComplete()` | `roboTargetShotComplete` | - |
| `RoboTargetError` | `handleError()` | `roboTargetError` | session-error |

### Enrichissement des données

Chaque événement est enrichi avec:

```javascript
{
  ...originalMessage,
  parsed: {
    guidTarget: string,
    guidSession: string,
    // ... event-specific fields
  }
}
```

**Exemple SessionComplete:**
```javascript
{
  parsed: {
    guidTarget: "uuid",
    guidSession: "uuid",
    guidSet: "uuid",
    targetName: "M31",
    result: 1,           // 1=OK, 2=Aborted, 3=Error
    resultText: "OK",
    sessionStart: "2025-12-12T20:00:00Z",
    sessionEnd: "2025-12-12T22:30:00Z",
    duration: 9000,      // seconds
    shotsCaptured: 18,
    hfdMean: 2.3,
    reason: null
  }
}
```

---

## WebSocket Events

### Client subscription

```javascript
const socket = io('http://localhost:3000');

// Session lifecycle
socket.on('roboTargetSessionStart', (data) => {
  console.log('Target started:', data.parsed.targetName);
});

socket.on('roboTargetProgress', (data) => {
  updateProgressBar(data.parsed.progress);
});

socket.on('roboTargetSessionComplete', (data) => {
  if (data.parsed.result === 1) {
    showSuccess('Session completed!');
  }
});

// Errors
socket.on('roboTargetError', (data) => {
  showError(data.parsed.errorMessage);
});
```

---

## Configuration

### Variables d'environnement

**`.env.example`** (mis à jour)

```env
# Server Configuration (existing)
NODE_ENV=development
PORT=3000
HOST=0.0.0.0

# Voyager Connection (existing)
VOYAGER_HOST=185.228.120.120
VOYAGER_PORT=23002
VOYAGER_AUTH_ENABLED=true
# ... MAC auth credentials ...

# NEW: Laravel Integration
LARAVEL_API_URL=http://localhost:8000
VOYAGER_WEBHOOK_SECRET=your_webhook_secret_here
```

### Webhook Configuration

**Headers envoyés:**
```http
POST /api/webhooks/robotarget/session-complete
Content-Type: application/json
X-Webhook-Secret: your_webhook_secret_here

{
  "event": "session-complete",
  "timestamp": "2025-12-12T22:30:00Z",
  "data": { /* enriched event data */ }
}
```

**Laravel doit répondre:**
```json
{
  "success": true,
  "message": "Webhook received"
}
```

---

## Tests

### Test unitaire des validators

```bash
cd voyager-proxy
npm test -- validators.test.js
```

```javascript
// Example test
describe('validateTarget', () => {
  it('should validate correct RA format', () => {
    const req = { body: { ra_j2000: '00:42:44' } };
    const res = {};
    const next = jest.fn();

    validateTarget(req, res, next);

    expect(next).toHaveBeenCalled();
  });

  it('should reject invalid RA format', () => {
    const req = { body: { ra_j2000: '25:00:00' } };
    const res = { status: jest.fn().mockReturnThis(), json: jest.fn() };
    const next = jest.fn();

    validateTarget(req, res, next);

    expect(res.status).toHaveBeenCalledWith(400);
    expect(next).not.toHaveBeenCalled();
  });
});
```

### Test d'intégration avec Voyager

```bash
# Start proxy in dev mode
npm run dev

# Test create target flow
curl -X POST http://localhost:3000/api/robotarget/sets \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "guid_set": "550e8400-e29b-41d4-a716-446655440000",
    "set_name": "Test Set"
  }'

curl -X POST http://localhost:3000/api/robotarget/targets \
  -H "Content-Type: application/json" \
  -H "X-API-Key: your-api-key" \
  -d '{
    "GuidTarget": "550e8400-e29b-41d4-a716-446655440001",
    "RefGuidSet": "550e8400-e29b-41d4-a716-446655440000",
    "TargetName": "M31",
    "RAJ2000": "00:42:44",
    "DECJ2000": "+41:16:09"
  }'
```

---

## Checklist d'implémentation

### Routes ✅
- [x] POST /api/robotarget/sets
- [x] POST /api/robotarget/targets
- [x] POST /api/robotarget/shots
- [x] PUT /api/robotarget/targets/:guid/status
- [x] GET /api/robotarget/sessions/:targetGuid/result
- [x] GET /api/robotarget/targets/:guid/progress
- [x] DELETE /api/robotarget/targets/:guid

### Commandes ✅
- [x] addSet()
- [x] addTarget()
- [x] addShot()
- [x] setTargetStatus()
- [x] getSessionListByTarget()
- [x] getActiveTargets()
- [x] createCompleteTarget()
- [x] deleteCompleteTarget()

### Event Handlers ✅
- [x] handleSessionStart()
- [x] handleSessionComplete()
- [x] handleSessionAbort()
- [x] handleProgress()
- [x] handleShotComplete()
- [x] handleError()
- [x] notifyLaravel() webhooks

### Validators ✅
- [x] validateSet()
- [x] validateTarget()
- [x] validateShot()
- [x] UUID format validation
- [x] RA/DEC format validation

### Integration ✅
- [x] Routes integrated in server.js
- [x] Commands integrated in connection.js
- [x] Events integrated in events.js
- [x] WebSocket broadcasting
- [x] Configuration (.env.example)

### Documentation ✅
- [x] API documentation
- [x] Event flow documentation
- [x] Configuration guide
- [x] Test examples

---

## Statistiques finales

**Code ajouté:**
- Routes: 272 lignes
- Validators: 144 lignes
- Commands: 197 lignes
- Event Handlers: 326 lignes
- Intégrations: ~90 lignes
- **Total: ~1,029 lignes**

**Endpoints créés:** 7
**Commandes Voyager:** 15+
**Événements gérés:** 6
**Webhooks:** 4

---

## Prochaines étapes

1. ✅ ~~Implémenter Proxy Node.js~~
2. ✅ ~~Tester routes avec Postman~~
3. ⏭️ Connecter avec Laravel backend
4. ⏭️ Tester workflow complet Create → Execute → Complete
5. ⏭️ Monitoring production

---

**✅ PHASE 2 TERMINÉE AVEC SUCCÈS**

*Dernière mise à jour : 12 Décembre 2025 - 23:45*
*Auteur : Claude Code + Mikaël*
