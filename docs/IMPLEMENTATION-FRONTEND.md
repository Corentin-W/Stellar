# 🎨 Guide d'implémentation Frontend - RoboTarget

> **✅ IMPLÉMENTATION COMPLÉTÉE**
> **Version:** 2.0.0
> **Date:** 12 Décembre 2025

---

## 📋 Table des matières

1. [Statut d'implémentation](#statut-dimplémentation)
2. [Architecture](#architecture)
3. [Composants Alpine.js](#composants-alpinejs)
4. [Vues Blade](#vues-blade)
5. [Services](#services)
6. [Flux utilisateur](#flux-utilisateur)
7. [WebSocket temps réel](#websocket-temps-réel)
8. [Styling et UI/UX](#styling-et-uiux)

---

## Statut d'implémentation

### ✅ Phase 3 : Frontend - TERMINÉE

| Composant | Statut | Fichier | Lignes |
|-----------|--------|---------|--------|
| RoboTarget Manager | ✅ Complété | `js/components/robotarget/RoboTargetManager.js` | 421 |
| Pricing Calculator | ✅ Complété | `js/components/robotarget/PricingCalculator.js` | 270 |
| Target Monitor | ✅ Complété | `js/components/robotarget/TargetMonitor.js` | 379 |
| WebSocket Service | ✅ Complété | `js/services/VoyagerWebSocket.js` | 243 |
| Vue Création | ✅ Complété | `views/dashboard/robotarget/create.blade.php` | 200+ |
| Partial Step 1 | ✅ Complété | `views/dashboard/robotarget/partials/step-target-info.blade.php` | 77 |
| Partial Step 2 | ✅ Complété | `views/dashboard/robotarget/partials/step-constraints.blade.php` | 133 |
| Partial Step 3 | ✅ Complété | `views/dashboard/robotarget/partials/step-shots.blade.php` | 166 |
| Partial Step 4 | ✅ Complété | `views/dashboard/robotarget/partials/step-review.blade.php` | 189 |

**Total ajouté:** ~2,078 lignes de code

---

## Architecture

### Stack technologique

```
┌─────────────────────────────────────────────────────────────┐
│                      FRONTEND STACK                          │
│                                                               │
│  ┌─────────────────────────────────────────────────────┐    │
│  │               Alpine.js Components                   │    │
│  │  • Reactive data binding                             │    │
│  │  • Event handling                                    │    │
│  │  • Form validation                                   │    │
│  │  • State management                                  │    │
│  └─────────────────────────────────────────────────────┘    │
│                          │                                    │
│  ┌─────────────────────────────────────────────────────┐    │
│  │              Blade Templates (Laravel)               │    │
│  │  • Server-side rendering                             │    │
│  │  • Component partials                                │    │
│  │  • Data injection                                    │    │
│  └─────────────────────────────────────────────────────┘    │
│                          │                                    │
│  ┌─────────────────────────────────────────────────────┐    │
│  │          Tailwind CSS + Dark Mode                    │    │
│  │  • Utility-first CSS                                 │    │
│  │  • Responsive design                                 │    │
│  │  • Custom components                                 │    │
│  └─────────────────────────────────────────────────────┘    │
│                          │                                    │
│  ┌─────────────────────────────────────────────────────┐    │
│  │         WebSocket Client (Socket.IO)                 │    │
│  │  • Real-time updates                                 │    │
│  │  • Auto-reconnection                                 │    │
│  │  • Event subscriptions                               │    │
│  └─────────────────────────────────────────────────────┘    │
└───────────────────────┬───────────────────────────────────────┘
                        │
                   HTTP + WS
                        │
          ┌─────────────▼──────────────┐
          │    Laravel Backend         │
          │  + Voyager Proxy           │
          └────────────────────────────┘
```

---

## Composants Alpine.js

### 1. RoboTargetManager.js (421 lignes)

**Responsabilités:**
- Gestion workflow multi-étapes (4 steps)
- Validation formulaire temps réel
- Pricing dynamique
- Soumission API

**API publique:**

```javascript
export default () => ({
  // State
  currentStep: number,              // 1-4
  isLoading: boolean,
  errorMessage: string | null,
  successMessage: string | null,

  // Target configuration
  target: {
    target_name: string,
    ra_j2000: string,              // HH:MM:SS
    dec_j2000: string,             // ±DD:MM:SS
    priority: number,              // 0-4
    c_moon_down: boolean,
    c_hfd_mean_limit: number | null,
    c_alt_min: number,            // 0-90
    shots: Shot[]
  },

  // Current shot being edited
  currentShot: {
    filter_index: number,
    filter_name: string,
    exposure: number,             // seconds
    num: number,
    gain: number,
    offset: number,
    bin: number
  },

  // Pricing data
  pricing: {
    estimated_credits: number,
    estimated_hours: number,
    base_cost: number,
    multipliers: Object,
    breakdown: Array
  },

  // User info (injected)
  subscription: Subscription,
  creditsBalance: number,

  // Navigation
  nextStep(): void,
  prevStep(): void,
  goToStep(step: number): void,

  // Validation
  validateCurrentStep(): boolean,
  validateRA(ra: string): boolean,
  validateDEC(dec: string): boolean,

  // Shots management
  addShot(): void,
  removeShot(index: number): void,
  editShot(index: number): void,
  updateFilterName(): void,

  // Pricing
  calculatePricing(): Promise<void>,

  // Submit
  submitTarget(): Promise<void>,

  // Helpers
  generateUUID(): string,
  formatDuration(hours: number): string,
  formatTime(seconds: number): string,
  getTotalExposureTime(): number,
  getTotalImages(): number,
  getPriorityLabel(priority: number): string,
  canUsePriority(priority: number): boolean
})
```

**Validations implémentées:**

```javascript
// RA Format: HH:MM:SS (00:00:00 → 23:59:59)
validateRA(ra) {
  const raRegex = /^([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/;
  return raRegex.test(ra);
}

// DEC Format: ±DD:MM:SS (-90:00:00 → +90:00:00)
validateDEC(dec) {
  const decRegex = /^[+-]([0-8][0-9]|90):([0-5][0-9]):([0-5][0-9])$/;
  return decRegex.test(dec);
}

// Priority with plan restrictions
canUsePriority(priority) {
  const maxPriorities = {
    'stardust': 1,   // Priorities 0-1
    'nebula': 2,     // Priorities 0-2
    'quasar': 4,     // Priorities 0-4
  };
  return priority <= (maxPriorities[this.subscription.plan] || 0);
}
```

**Exemple d'utilisation:**

```html
<div x-data="RoboTargetManager()" x-init="init()">
  <!-- Step 1: Target Info -->
  <div x-show="currentStep === 1">
    <input x-model="target.target_name" placeholder="M31" />
    <input x-model="target.ra_j2000" placeholder="00:42:44" />
    <button @click="nextStep()">Suivant →</button>
  </div>

  <!-- Pricing (auto-updates) -->
  <div x-show="currentStep >= 3">
    <p>Crédits estimés: <span x-text="pricing.estimated_credits"></span></p>
  </div>
</div>
```

### 2. PricingCalculator.js (270 lignes)

**Responsabilités:**
- Calculateur standalone de pricing
- Recommandation de plans
- Simulation mensuelle

**API publique:**

```javascript
export default () => ({
  // State
  isCalculating: boolean,
  pricing: PricingEstimate | null,
  error: string | null,

  // Configuration
  selectedPlan: 'stardust' | 'nebula' | 'quasar',
  targetConfig: {
    priority: number,
    c_moon_down: boolean,
    c_hfd_mean_limit: number | null,
    shots: Shot[]
  },

  // Plans
  plans: {
    stardust: { name, price: 29, credits: 20, maxPriority: 1 },
    nebula: { name, price: 59, credits: 60, maxPriority: 2 },
    quasar: { name, price: 119, credits: 150, maxPriority: 4 }
  },

  // Actions
  calculate(): Promise<void>,
  getRecommendation(): Promise<void>,
  addShot(): void,
  removeShot(index: number): void,
  resetCalculator(): void,

  // Getters
  getTotalExposure(): number,
  getTotalImages(): number,
  getPriorityMultiplier(): number,
  getMoonDownMultiplier(): number,
  getHFDMultiplier(): number,
  getTotalMultiplier(): number,
  canAfford(): boolean
})
```

**Formule de pricing:**

```
Final Cost = Base Hours × BASE_COST_PER_HOUR
           × Priority Multiplier
           × Moon Down Multiplier
           × HFD Multiplier

Où:
- BASE_COST_PER_HOUR = 1.0 crédit/heure
- Priority Multiplier:
  • Priority 0-1: ×1.0
  • Priority 2: ×1.2
  • Priority 3: ×2.0
  • Priority 4: ×3.0
- Moon Down: ×2.0 (si activé)
- HFD Guarantee: ×1.5 (si activé)
```

**Exemple:**

```
Target: M31
- Total exposure: 2 hours
- Priority: 2 (Normale)
- Moon Down: Yes
- HFD: 2.5px

Calcul:
  Base: 2h × 1.0 = 2 crédits
  × 1.2 (Priority 2)
  × 2.0 (Moon Down)
  × 1.5 (HFD)
  = 7.2 → 8 crédits
```

### 3. TargetMonitor.js (379 lignes)

**Responsabilités:**
- Monitoring temps réel via WebSocket
- Affichage progression
- Actions submit/cancel

**API publique:**

```javascript
export default (targetGuid) => ({
  // State
  target: RoboTarget | null,
  isLoading: boolean,
  error: string | null,

  // Real-time session data
  session: {
    status: 'idle' | 'running' | 'completed' | 'error' | 'aborted',
    guidSession: string | null,
    startTime: Date | null,
    endTime: Date | null,
    progress: number,        // 0-100
    currentShot: number,
    totalShots: number,
    currentFilter: string,
    currentExposure: number,
    hfd: number | null,
    shotsCaptured: number,
    result: number | null,   // 1=OK, 2=Aborted, 3=Error
    reason: string | null
  },

  // WebSocket
  ws: VoyagerWebSocket,
  isConnected: boolean,
  unsubscribers: Function[],

  // Lifecycle
  init(): void,
  destroy(): void,

  // Data loading
  loadTarget(): Promise<void>,

  // WebSocket
  initWebSocket(): void,
  handleSessionStart(data): void,
  handleProgress(data): void,
  handleShotComplete(data): void,
  handleSessionComplete(data): void,
  handleSessionAbort(data): void,
  handleError(data): void,

  // Actions
  submitTarget(): Promise<void>,
  cancelTarget(): Promise<void>,

  // Helpers
  getStatusColor(status): string,
  getStatusLabel(status): string,
  getResultLabel(result): string,
  formatDuration(start, end): string,
  formatTime(isoString): string
})
```

**États de session:**

```javascript
session: {
  status: 'idle',          // Initial state
  guidSession: null,
  startTime: null,
  progress: 0,
  currentShot: 0,
  totalShots: 20,
  currentFilter: null,
  currentExposure: null,
  hfd: null,
  shotsCaptured: 0,
  result: null,
  reason: null
}

// After SessionStart event
session.status = 'running'
session.startTime = '2025-12-12T20:00:00Z'

// After Progress events
session.progress = 45
session.currentShot = 9
session.currentFilter = 'Luminance'
session.hfd = 2.1

// After SessionComplete event
session.status = 'completed'
session.endTime = '2025-12-12T22:30:00Z'
session.progress = 100
session.result = 1  // 1=OK, 2=Aborted, 3=Error
session.shotsCaptured = 18
```

### 4. VoyagerWebSocket.js (243 lignes)

**Service singleton pour WebSocket**

**API publique:**

```javascript
class VoyagerWebSocket {
  // Connection
  connect(): void
  disconnect(): void
  scheduleReconnect(): void

  // Events (pub/sub pattern)
  on(event: string, callback: Function): UnsubscribeFunction
  once(event: string, callback: Function): UnsubscribeFunction
  off(event: string, callback?: Function): void
  emit(event: string, data: any): void

  // Messaging
  send(event: string, data?: any): boolean

  // State
  getState(): {
    isConnected: boolean,
    isReconnecting: boolean,
    reconnectAttempts: number,
    readyState: number
  }
}

// Factory functions
export function createVoyagerWebSocket(url, options)
export function getVoyagerWebSocket()
```

**Configuration:**

```javascript
const options = {
  autoConnect: true,            // Connect immediately
  maxReconnectAttempts: 10,     // Max retry
  reconnectDelay: 3000          // Base delay (ms)
}
```

**Reconnection strategy:**

```
Attempt 1: 3s delay
Attempt 2: 6s delay   (3s × 2)
Attempt 3: 12s delay  (3s × 3)
...
Attempt 10: 30s delay (3s × 10)
→ Give up after 10 attempts
```

**Exemple d'utilisation:**

```javascript
import { createVoyagerWebSocket, getVoyagerWebSocket } from '@/services/VoyagerWebSocket';

// Initialize once in app.js
const ws = createVoyagerWebSocket('ws://localhost:3000', {
  autoConnect: true,
  maxReconnectAttempts: 10,
  reconnectDelay: 3000
});

// Use in components
const ws = getVoyagerWebSocket();

// Subscribe to events
const unsubscribe = ws.on('roboTargetProgress', (data) => {
  console.log(`Progress: ${data.parsed.progress}%`);
});

// Unsubscribe (important for cleanup!)
unsubscribe();

// Or subscribe once
ws.once('roboTargetSessionComplete', (data) => {
  alert('Target completed!');
});
```

---

## Vues Blade

### Structure

```
resources/views/dashboard/robotarget/
├── create.blade.php              // Main creation view
└── partials/
    ├── step-target-info.blade.php      // Step 1
    ├── step-constraints.blade.php      // Step 2
    ├── step-shots.blade.php            // Step 3
    └── step-review.blade.php           // Step 4
```

### create.blade.php (200+ lignes)

**Structure:**

```blade
@extends('layouts.app')

@section('content')
<div class="container">
  <div x-data="RoboTargetManager()" x-init="init()">

    {{-- Progress Stepper --}}
    <div class="stepper">
      <div :class="currentStep >= 1 ? 'active' : ''">1. Cible</div>
      <div :class="currentStep >= 2 ? 'active' : ''">2. Contraintes</div>
      <div :class="currentStep >= 3 ? 'active' : ''">3. Acquisitions</div>
      <div :class="currentStep >= 4 ? 'active' : ''">4. Résumé</div>
    </div>

    {{-- Error/Success Messages --}}
    <div x-show="errorMessage" class="alert-error">...</div>
    <div x-show="successMessage" class="alert-success">...</div>

    {{-- Form Card --}}
    <div class="card">
      {{-- Step content --}}
      <div x-show="currentStep === 1">
        @include('dashboard.robotarget.partials.step-target-info')
      </div>
      <div x-show="currentStep === 2">
        @include('dashboard.robotarget.partials.step-constraints')
      </div>
      <div x-show="currentStep === 3">
        @include('dashboard.robotarget.partials.step-shots')
      </div>
      <div x-show="currentStep === 4">
        @include('dashboard.robotarget.partials.step-review')
      </div>

      {{-- Navigation --}}
      <div class="card-footer">
        <button @click="prevStep()" x-show="currentStep > 1">← Précédent</button>
        <button @click="nextStep()" x-show="currentStep < 4">Suivant →</button>
        <button @click="submitTarget()" x-show="currentStep === 4">✓ Créer</button>
      </div>
    </div>

    {{-- Pricing Sidebar (from step 3) --}}
    <div x-show="currentStep >= 3" class="pricing-sidebar">
      <h3>💰 Estimation des crédits</h3>
      <div>Crédits: {{ pricing.estimated_credits }}</div>
      <div>Durée: {{ pricing.estimated_hours }}h</div>
      <div>Solde: {{ creditsBalance }}</div>
    </div>

  </div>
</div>
@endsection

@push('scripts')
<script type="module">
  import RoboTargetManager from '/resources/js/components/robotarget/RoboTargetManager.js';
  window.RoboTargetManager = RoboTargetManager;

  // Inject user data
  window.userSubscription = @json(auth()->user()->subscription);
  window.userCredits = {{ auth()->user()->credits_balance }};
</script>
@endpush
```

### step-target-info.blade.php (77 lignes)

**Champs:**

```html
<!-- Nom de la cible -->
<input
  type="text"
  x-model="target.target_name"
  placeholder="Ex: M31 - Galaxie d'Andromède"
/>

<!-- RA J2000 (HH:MM:SS) -->
<input
  type="text"
  x-model="target.ra_j2000"
  placeholder="00:42:44"
  class="font-mono"
/>

<!-- DEC J2000 (±DD:MM:SS) -->
<input
  type="text"
  x-model="target.dec_j2000"
  placeholder="+41:16:09"
  class="font-mono"
/>

<!-- Priority (0-4) with plan restrictions -->
<template x-for="priority in [0, 1, 2, 3, 4]">
  <label>
    <input
      type="radio"
      :value="priority"
      x-model="target.priority"
      :disabled="!canUsePriority(priority)"
    />
    <span x-text="getPriorityLabel(priority)"></span>
    <span x-show="!canUsePriority(priority)">(Requiert plan supérieur)</span>
  </label>
</template>
```

### step-constraints.blade.php (133 lignes)

**Contraintes:**

```html
<!-- Altitude minimale (slider 0-90°) -->
<input
  type="range"
  min="0"
  max="90"
  step="5"
  x-model="target.c_alt_min"
/>
<span x-text="target.c_alt_min + '°'"></span>

<!-- Moon Down (checkbox + warning) -->
<label>
  <input type="checkbox" x-model="target.c_moon_down" />
  🌙 Lune couchée obligatoire
  <span class="badge">Multiplicateur ×2.0</span>
</label>
<div x-show="target.c_moon_down" class="warning">
  ⚠️ Cette contrainte réduit les opportunités et double le coût
</div>

<!-- HFD Guarantee (checkbox + slider) -->
<label>
  <input
    type="checkbox"
    x-model.lazy="target.c_hfd_mean_limit"
    @change="if (!target.c_hfd_mean_limit) target.c_hfd_mean_limit = null; else target.c_hfd_mean_limit = 2.5;"
  />
  ⭐ Garantie qualité HFD
  <span class="badge">Multiplicateur ×1.5</span>
</label>

<div x-show="target.c_hfd_mean_limit !== null">
  <input
    type="range"
    min="1.5"
    max="4.0"
    step="0.1"
    x-model="target.c_hfd_mean_limit"
  />
  <span x-text="target.c_hfd_mean_limit + 'px'"></span>

  <!-- Quick presets -->
  <button @click="target.c_hfd_mean_limit = 2.0">Excellent (2.0)</button>
  <button @click="target.c_hfd_mean_limit = 2.5">Bon (2.5)</button>
  <button @click="target.c_hfd_mean_limit = 3.0">Correct (3.0)</button>
</div>

<!-- Summary box -->
<div class="summary">
  <div>Altitude min: {{ target.c_alt_min }}°</div>
  <div>Lune: {{ target.c_moon_down ? 'Oui (×2.0)' : 'Non' }}</div>
  <div>HFD: {{ target.c_hfd_mean_limit ? target.c_hfd_mean_limit + 'px (×1.5)' : 'Non' }}</div>
  <div>Multiplicateur total: ×{{ (c_moon_down ? 2 : 1) * (c_hfd ? 1.5 : 1) }}</div>
</div>
```

### step-shots.blade.php (166 lignes)

**Add shot form:**

```html
<div class="add-shot-form">
  <!-- Filter -->
  <select x-model="currentShot.filter_index" @change="updateFilterName()">
    <template x-for="filter in filterOptions">
      <option :value="filter.index" x-text="filter.name"></option>
    </template>
  </select>

  <!-- Exposure -->
  <input type="number" x-model.number="currentShot.exposure" placeholder="300" />

  <!-- Num -->
  <input type="number" x-model.number="currentShot.num" placeholder="10" />

  <!-- Add button -->
  <button @click="addShot()">➕ Ajouter</button>
</div>

<!-- Presets rapides -->
<div class="presets">
  <button @click="currentShot = { filter_index: 0, filter_name: 'Luminance', exposure: 300, num: 20, ... }">
    Luminance 5m ×20
  </button>
  <button @click="currentShot = { filter_index: 4, filter_name: 'Ha', exposure: 600, num: 15, ... }">
    Ha 10m ×15
  </button>
</div>

<!-- Shots list -->
<template x-for="(shot, index) in target.shots">
  <div class="shot-card">
    <div>{{ shot.filter_name }}</div>
    <div>{{ shot.exposure }}s × {{ shot.num }}</div>
    <div>Total: {{ shot.exposure * shot.num }}s</div>
    <button @click="editShot(index)">✎</button>
    <button @click="removeShot(index)">🗑</button>
  </div>
</template>

<!-- Totals -->
<div class="totals">
  <div>Total images: {{ getTotalImages() }}</div>
  <div>Temps exposition: {{ formatTime(getTotalExposureTime()) }}</div>
  <div>Temps estimé: {{ formatDuration(getTotalExposureTime() / 3600 * 1.3) }}</div>
</div>
```

### step-review.blade.php (189 lignes)

**Final review:**

```html
<!-- Target summary -->
<div class="summary-card">
  <h3>🎯 {{ target.target_name }}</h3>

  <!-- Coordinates -->
  <div>RA: {{ target.ra_j2000 }}</div>
  <div>DEC: {{ target.dec_j2000 }}</div>

  <!-- Priority -->
  <div>Priorité: {{ getPriorityLabel(target.priority) }}</div>

  <!-- Constraints -->
  <div>Altitude min: {{ target.c_alt_min }}°</div>
  <div>Lune couchée: {{ target.c_moon_down ? 'Oui (×2.0)' : 'Non' }}</div>
  <div>HFD: {{ target.c_hfd_mean_limit ? target.c_hfd_mean_limit + 'px (×1.5)' : 'Non' }}</div>

  <!-- Shots -->
  <template x-for="shot in target.shots">
    <div>{{ shot.filter_name }}: {{ shot.exposure }}s × {{ shot.num }}</div>
  </template>

  <div>Total: {{ getTotalImages() }} images, {{ formatTime(getTotalExposureTime()) }}</div>
</div>

<!-- Final pricing -->
<div class="pricing-final">
  <div>Coût de base: {{ pricing.base_cost }} crédits</div>
  <div>Multiplicateur: ×{{ pricing.multipliers.total_multiplier }}</div>
  <div class="total">TOTAL: {{ pricing.estimated_credits }} crédits</div>

  <div>Votre solde: {{ creditsBalance }} crédits</div>
  <div>Après: {{ creditsBalance - pricing.estimated_credits }} crédits</div>

  <!-- Insufficient credits warning -->
  <div x-show="creditsBalance < pricing.estimated_credits" class="alert-error">
    ⚠️ Crédits insuffisants
  </div>
</div>

<!-- Terms -->
<div class="terms">
  <p>📝 Important:</p>
  <ul>
    <li>Crédits réservés immédiatement</li>
    <li>Capturés uniquement si session réussie</li>
    <li>Remboursés automatiquement en cas d'erreur</li>
  </ul>
</div>
```

---

## Services

### VoyagerWebSocket.js

**Lifecycle:**

```
┌───────────────────────────────────────────┐
│           WebSocket Lifecycle             │
└───────────────────────────────────────────┘

[DISCONNECTED]
      │
      │ connect()
      ▼
[CONNECTING]
      │
      │ onopen
      ▼
[CONNECTED] ◄──────┐
      │            │
      │ onclose    │ Reconnect
      ▼            │
[RECONNECTING] ────┘
      │
      │ Max attempts reached
      ▼
[FAILED]
```

**Event flow:**

```javascript
// Internal events (lifecycle)
ws.emit('connected')                    // Connection established
ws.emit('disconnected', { code, reason })  // Connection lost
ws.emit('error', error)                 // Error occurred

// RoboTarget events (from Voyager)
ws.emit('roboTargetSessionStart', data)
ws.emit('roboTargetProgress', data)
ws.emit('roboTargetShotComplete', data)
ws.emit('roboTargetSessionComplete', data)
ws.emit('roboTargetSessionAbort', data)
ws.emit('roboTargetError', data)

// Wildcard listener
ws.emit('*', { event, data })          // All events
```

---

## Flux utilisateur

### Création de target (workflow complet)

```
┌─────────────────────────────────────────────────────────────┐
│                   CREATE TARGET WORKFLOW                     │
└─────────────────────────────────────────────────────────────┘

1. User → /dashboard/robotarget/create

2. Step 1: Target Info
   ├─ Input: Nom = "M31 - Andromeda"
   ├─ Input: RA = "00:42:44"
   ├─ Input: DEC = "+41:16:09"
   ├─ Select: Priority = 2 (Normale)
   └─ Click: "Suivant" → validateCurrentStep() → nextStep()

3. Step 2: Constraints
   ├─ Slider: Altitude min = 30°
   ├─ Check: Moon Down = true (×2.0)
   ├─ Check: HFD = 2.5px (×1.5)
   └─ Click: "Suivant" → nextStep()

4. Step 3: Shots
   ├─ Add: Luminance 300s × 20
   ├─ Add: Ha 600s × 15
   ├─ Add: OIII 600s × 15
   │
   ├─ Auto: calculatePricing()
   │  └─ API: POST /api/pricing/estimate
   │     → pricing = { estimated_credits: 45, ... }
   │
   └─ Click: "Suivant" → nextStep()

5. Step 4: Review
   ├─ Display: Full summary
   ├─ Display: Pricing = 45 crédits
   ├─ Check: Balance = 60 crédits ✓
   └─ Click: "✓ Créer la Target"

6. Submit
   ├─ Generate: guid_target = uuid-v4()
   ├─ Generate: guid_set = uuid-v4()
   │
   └─ API: POST /api/robotarget/targets
      {
        guid_target: "uuid",
        guid_set: "uuid",
        target_name: "M31",
        ra_j2000: "00:42:44",
        dec_j2000: "+41:16:09",
        priority: 2,
        c_alt_min: 30,
        c_moon_down: true,
        c_hfd_mean_limit: 2.5,
        shots: [
          { filter_index: 0, exposure: 300, num: 20, ... },
          { filter_index: 4, exposure: 600, num: 15, ... },
          { filter_index: 5, exposure: 600, num: 15, ... }
        ]
      }

7. Laravel Backend
   ├─ Validate payload
   ├─ Calculate pricing (verify)
   ├─ Check credits balance
   ├─ Create RoboTarget (DB)
   ├─ Create RoboTargetShots (DB)
   ├─ Hold credits
   └─ Forward to Proxy

8. Voyager Proxy
   ├─ POST /api/robotarget/sets
   ├─ POST /api/robotarget/targets
   ├─ POST /api/robotarget/shots (×3)
   └─ PUT /api/robotarget/targets/{guid}/status (active)

9. Response
   ├─ Success: Show message "Target créée avec succès !"
   └─ Redirect: /dashboard/robotarget (after 2s)

10. List view
    └─ Display: Target with status "Pending"
```

### Monitoring de target (temps réel)

```
┌─────────────────────────────────────────────────────────────┐
│                   MONITOR TARGET WORKFLOW                    │
└─────────────────────────────────────────────────────────────┘

1. User → /dashboard/robotarget/{guid}

2. Page Load
   ├─ Component: TargetMonitor(guid)
   │
   ├─ API: GET /api/robotarget/targets/{guid}
   │  └─ Load target data + latest session
   │
   └─ WebSocket: Connect to ws://localhost:3000
      └─ Subscribe to events for {guid}

3. WebSocket Connected
   ├─ Event: connected
   └─ isConnected = true

4. User clicks "Submit"
   └─ API: POST /api/robotarget/targets/{guid}/submit
      └─ Laravel → Proxy → Voyager: Activate target

5. Voyager starts execution
   │
   ├─ Event: roboTargetSessionStart
   │  ├─ parsed.guidTarget = {guid}
   │  ├─ parsed.startTime = "2025-12-12T20:00:00Z"
   │  └─ UI: session.status = 'running'
   │
   ├─ Event: roboTargetProgress (every ~5s)
   │  ├─ parsed.progress = 45
   │  ├─ parsed.currentShot = 9
   │  ├─ parsed.totalShots = 20
   │  ├─ parsed.currentFilter = "Luminance"
   │  ├─ parsed.hfd = 2.1
   │  └─ UI: Update progress bar
   │
   ├─ Event: roboTargetShotComplete (×20)
   │  └─ UI: session.shotsCaptured++
   │
   └─ Event: roboTargetSessionComplete
      ├─ parsed.result = 1 (OK)
      ├─ parsed.sessionEnd = "2025-12-12T22:30:00Z"
      ├─ parsed.shotsCaptured = 18
      ├─ parsed.hfdMean = 2.3
      │
      └─ UI: session.status = 'completed'
         ├─ Show success message
         └─ Display final stats

6. Webhook to Laravel
   └─ POST /api/webhooks/robotarget/session-complete
      ├─ RoboTargetSession::create()
      └─ RoboTargetSession::handleCredits()
         ├─ Result = 1 (OK) → captureCredits()
         └─ Result = 3 (Error) → refundCredits()

7. Page Refresh
   └─ Display: Final status + session results
```

---

## WebSocket temps réel

### Événements RoboTarget

| Événement | Data | UI Update |
|-----------|------|-----------|
| `roboTargetSessionStart` | `{ guidTarget, guidSession, startTime }` | Status → "Running" 🟢 |
| `roboTargetProgress` | `{ progress, currentShot, hfd }` | Progress bar: N% |
| `roboTargetShotComplete` | `{ filename, filter, hfd }` | Counter: N/Total |
| `roboTargetSessionComplete` | `{ result, sessionEnd, shotsCaptured }` | Status → "Completed" ✓ |
| `roboTargetSessionAbort` | `{ reason }` | Status → "Aborted" ⚠️ |
| `roboTargetError` | `{ errorMessage }` | Status → "Error" ❌ |

### Integration pattern

```javascript
// In TargetMonitor component
export default (targetGuid) => ({
  init() {
    this.initWebSocket();
  },

  initWebSocket() {
    this.ws = getVoyagerWebSocket();

    // Subscribe to events
    this.unsubscribers.push(
      this.ws.on('roboTargetSessionStart', (data) => {
        if (data.parsed.guidTarget === targetGuid) {
          this.handleSessionStart(data.parsed);
        }
      }),

      this.ws.on('roboTargetProgress', (data) => {
        if (data.parsed.guidTarget === targetGuid) {
          this.handleProgress(data.parsed);
        }
      }),

      // ... other events
    );
  },

  handleProgress(data) {
    this.session.progress = data.progress;
    this.session.currentShot = data.currentShot;
    this.session.hfd = data.hfd;
    // UI auto-updates via Alpine.js reactivity
  },

  destroy() {
    // Cleanup: unsubscribe from all events
    this.unsubscribers.forEach(unsub => unsub());
  }
});
```

---

## Styling et UI/UX

### Tailwind CSS

**Custom theme:**

```javascript
// tailwind.config.js
module.exports = {
  theme: {
    extend: {
      colors: {
        'stellar-blue': '#1E40AF',
        'stellar-purple': '#7C3AED',
        'stellar-green': '#059669',
      }
    }
  }
}
```

**Dark mode:**

```html
<!-- Auto dark mode based on OS preference -->
<div class="bg-white dark:bg-gray-800">
  <p class="text-gray-900 dark:text-white">Text</p>
</div>
```

### Responsive design

```html
<!-- Mobile-first responsive grid -->
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
  <div>Column 1</div>
  <div>Column 2</div>
  <div>Column 3</div>
  <div>Column 4</div>
</div>
```

### Animations

```html
<!-- Fade transition -->
<div x-show="visible" x-transition>
  Smooth fade in/out
</div>

<!-- Custom transition -->
<div
  x-show="error"
  x-transition:enter="transition ease-out duration-300"
  x-transition:enter-start="opacity-0 transform scale-90"
  x-transition:enter-end="opacity-100 transform scale-100"
  x-transition:leave="transition ease-in duration-200"
  x-transition:leave-start="opacity-100 transform scale-100"
  x-transition:leave-end="opacity-0 transform scale-90"
>
  Error message
</div>
```

### Components library

**Status badges:**

```html
<span class="badge" :class="`badge-${getStatusColor(status)}`">
  {{ getStatusLabel(status) }}
</span>

<!-- CSS -->
<style>
.badge { @apply px-2 py-1 rounded text-xs font-semibold; }
.badge-green { @apply bg-green-100 text-green-800; }
.badge-yellow { @apply bg-yellow-100 text-yellow-800; }
.badge-red { @apply bg-red-100 text-red-800; }
</style>
```

**Progress bars:**

```html
<div class="progress-bar">
  <div class="progress-fill" :style="`width: ${session.progress}%`"></div>
</div>

<!-- CSS -->
<style>
.progress-bar {
  @apply w-full h-4 bg-gray-200 rounded-full overflow-hidden;
}
.progress-fill {
  @apply h-full bg-blue-600 transition-all duration-300;
}
</style>
```

---

## Checklist d'implémentation

### Composants Alpine.js ✅
- [x] RoboTargetManager.js (421 lignes)
- [x] PricingCalculator.js (270 lignes)
- [x] TargetMonitor.js (379 lignes)
- [x] VoyagerWebSocket.js (243 lignes)

### Vues Blade ✅
- [x] create.blade.php (200+ lignes)
- [x] step-target-info.blade.php (77 lignes)
- [x] step-constraints.blade.php (133 lignes)
- [x] step-shots.blade.php (166 lignes)
- [x] step-review.blade.php (189 lignes)

### Features ✅
- [x] Multi-step form wizard (4 steps)
- [x] RA/DEC format validation
- [x] Real-time pricing calculation
- [x] Credits balance check
- [x] Shot presets (Lum, RGB, Ha, OIII)
- [x] WebSocket live updates
- [x] Progress monitoring
- [x] Session tracking
- [x] Error handling
- [x] Loading states
- [x] Responsive design
- [x] Dark mode support
- [x] Smooth transitions

### Integration ✅
- [x] Alpine.js setup
- [x] Tailwind CSS
- [x] Laravel Blade
- [x] WebSocket connection
- [x] API endpoints integration
- [x] User data injection

---

## Statistiques finales

**Code frontend:**
- Composants JS: 1,313 lignes
- Vues Blade: 765 lignes
- **Total: ~2,078 lignes**

**Composants:** 4
**Vues:** 5
**Événements WebSocket:** 6
**Validators:** 2 (RA, DEC)

---

## Prochaines étapes

1. ✅ ~~Implémenter composants Alpine.js~~
2. ✅ ~~Créer vues Blade~~
3. ✅ ~~Intégrer WebSocket~~
4. ⏭️ **Ajouter routes web.php Laravel**
5. ⏭️ **Créer vue index.blade.php (liste targets)**
6. ⏭️ **Créer vue show.blade.php (monitor)**
7. ⏭️ Intégrer dans sidebar navigation
8. ⏭️ Tests E2E workflow complet

---

**✅ PHASE 3 TERMINÉE AVEC SUCCÈS**

*Dernière mise à jour : 12 Décembre 2025 - 23:55*
*Auteur : Claude Code + Mikaël*
