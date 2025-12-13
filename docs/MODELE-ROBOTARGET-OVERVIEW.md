# 🌟 Stellar - Modèle RoboTarget : Vue d'ensemble

> **Date:** 12 Décembre 2025
> **Version:** 2.0.0
> **Statut:** 🚀 Nouveau modèle en cours d'implémentation

---

## 📋 Table des matières

1. [Changement de paradigme](#changement-de-paradigme)
2. [Architecture globale](#architecture-globale)
3. [Modèle économique](#modèle-économique)
4. [Flux utilisateur](#flux-utilisateur)
5. [Implémentation technique](#implémentation-technique)
6. [Documentation associée](#documentation-associée)

---

## Changement de paradigme

### 🔄 Ancien modèle (Réservations)

```
Utilisateur → Réserve un créneau horaire → Accède au matériel → Contrôle manuel
```

**Problèmes :**
- Nécessite présence utilisateur pendant tout le créneau
- Sous-utilisation du matériel (météo, conditions)
- Complexité de gestion des créneaux
- Expérience utilisateur limitée

### ✨ Nouveau modèle (RoboTarget)

```
Utilisateur → Configure des cibles → RoboTarget automatise → Récupère les images
```

**Avantages :**
- 🤖 **Automatisation complète** : RoboTarget gère l'observation
- 🌙 **Optimisation** : Observation uniquement quand conditions optimales
- 💳 **Crédits flexibles** : Paye uniquement ce qui est utilisé
- 🎯 **Multi-cibles** : Plusieurs objets célestes en parallèle
- ⭐ **Qualité garantie** : Options de garantie netteté (HFD)

---

## Architecture globale

### Stack technique

```
┌─────────────────────────────────────────────────────────────┐
│                    ASTRAL STELLAR                           │
│                                                             │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐     │
│  │   Frontend   │  │   Laravel    │  │  Node Proxy  │     │
│  │              │  │              │  │              │     │
│  │  Target      │◄─┤  Business    │◄─┤  Voyager     │     │
│  │  Planner     │  │  Logic       │  │  Bridge      │     │
│  │              │  │              │  │              │     │
│  │  - Config    │  │  - Pricing   │  │  - TCP/IP    │     │
│  │  - Preview   │  │  - Credits   │  │  - Commands  │     │
│  │  - Dashboard │  │  - Subs      │  │  - Events    │     │
│  └──────────────┘  └──────────────┘  └──────┬───────┘     │
│                                              │             │
└──────────────────────────────────────────────┼─────────────┘
                                               │
                                               ▼
                                    ┌──────────────────┐
                                    │  Voyager Server  │
                                    │  (RoboTarget)    │
                                    │                  │
                                    │  - Scheduler     │
                                    │  - Automation    │
                                    │  - Image Acq.    │
                                    └────────┬─────────┘
                                             │
                                             ▼
                                    ┌──────────────────┐
                                    │   Télescope      │
                                    │   Caméra         │
                                    │   Monture        │
                                    └──────────────────┘
```

### Composants principaux

#### 1. Frontend (Target Planner)
- Interface de configuration des cibles
- Sélection d'objets célestes (catalogue)
- Configuration des filtres, expositions, quantités
- Preview du coût en crédits
- Dashboard de suivi temps réel

#### 2. Laravel (Business Logic)
- Gestion des abonnements (Stardust/Nebula/Quasar)
- Moteur de tarification (Pricing Engine)
- Système de crédits avec hold/capture
- Génération des payloads RoboTarget
- Webhook handlers

#### 3. Proxy Node.js
- Connexion persistante TCP à Voyager
- Envoi des commandes RoboTarget
- Réception des événements temps réel
- WebSocket pour le dashboard
- API REST pour Laravel

#### 4. Voyager RoboTarget
- Scheduler intelligent
- Gestion automatique des cibles
- Contrôle des équipements
- Acquisition d'images selon contraintes

---

## Modèle économique

### Les 3 abonnements

| Abonnement | Prix | Crédits | Cible | Restrictions |
|------------|------|---------|-------|--------------|
| **🌟 Stardust** | 29€ | 20 | Débutant | Priority 0-1, Pas nuit noire, One-shot |
| **🌌 Nebula** | 59€ | 60 | Amateur | Priority 2, Option nuit noire, Dashboard |
| **⚡ Quasar** | 119€ | 150 | Expert | Priority 3-4, Garantie HFD, Multi-nuits |

### Correspondance avec l'API Voyager

#### Stardust (Éco)
```json
{
  "Priority": 0,              // Very Low ou Low (1)
  "C_MoonDown": false,        // Forcé à false (lune acceptée)
  "C_HFDMeanLimit": 0,        // Pas de garantie netteté
  "IsRepeat": false           // One-shot uniquement
}
```

#### Nebula (Standard)
```json
{
  "Priority": 2,              // Normal
  "C_MoonDown": true,         // Option débloquée (x2 crédits)
  "C_HFDMeanLimit": 4.0,      // Netteté standard (fixe)
  "Dashboard": true           // Accès temps réel
}
```

#### Quasar (Premium)
```json
{
  "Priority": 3,              // High ou First (4)
  "C_MoonDown": true,         // Toujours disponible
  "C_HFDMeanLimit": 2.5,      // Curseur ajustable < 4.0
  "IsRepeat": true,           // Projets multi-nuits
  "Sets": true                // Gestion de Sets avancée
}
```

### Moteur de tarification

#### Formule de base
```
Coût_Final = (Durée_Estimée * Coût_Base_Horaire) * Multiplicateurs
```

#### Multiplicateurs

| Option | Paramètre API | Multiplicateur |
|--------|---------------|----------------|
| Priorité Éco (0-1) | `Priority: 0-1` | **x1.0** |
| Priorité Standard (2) | `Priority: 2` | **x1.2** |
| Priorité VIP (4) | `Priority: 4` | **x3.0** |
| Nuit Noire | `C_MoonDown: true` | **x2.0** |
| Garantie HFD | `C_HFDMeanLimit > 0` | **x1.5** |

#### Exemple de calcul

**Configuration :**
- Abonnement : Nebula
- Cible : M31
- Durée estimée : 2 heures
- Options : Nuit noire activée
- Priority : 2 (Normal)

**Calcul :**
```
Coût_Base = 2h * 5 crédits/h = 10 crédits
Multiplicateurs = 1.2 (Priority 2) * 2.0 (Nuit noire) = 2.4
Coût_Final = 10 * 2.4 = 24 crédits
```

### Cycle de vie des crédits

```
1. HOLD (Réservation)
   ↓
   Crédits "gelés" mais pas détruits
   ↓
2. EXÉCUTION
   ↓
   RoboTarget traite la cible
   ↓
3. VÉRIFICATION RÉSULTAT
   ↓
   ├─ Result = 1 (OK) → DÉBIT DÉFINITIF ✅
   ├─ Result = 2 (Aborted) → REMBOURSEMENT AUTOMATIQUE 💰
   └─ Result = 3 (Error) → REMBOURSEMENT AUTOMATIQUE 💰
```

**API de vérification :**
```javascript
// Laravel interroge le résultat
const result = await proxy.getRoboTargetSessionResult(targetGuid);

if (result === 1) {
  // Débit définitif
  transaction.capture();
} else {
  // Remboursement
  transaction.refund();
}
```

---

## Flux utilisateur

### 1. Configuration d'une cible

```
Utilisateur connecté
   ↓
Dashboard → "Nouvelle Cible"
   ↓
Target Planner (Sidebar Astrale)
   ↓
┌─────────────────────────────────┐
│ 1. Sélection objet céleste      │
│    - Catalogue intégré           │
│    - Recherche par nom           │
│    - Suggestions                 │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ 2. Configuration                 │
│    ✓ Coordonnées (auto)          │
│    ✓ Filtres (L, R, G, B, Ha...) │
│    ✓ Expositions (durée)         │
│    ✓ Quantité de poses           │
│    ✓ Binning, Gain, Offset       │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ 3. Contraintes (selon abonnement)│
│    Stardust:                     │
│      - Priority 🔒 (forcé 0-1)   │
│    Nebula:                       │
│      □ Nuit noire (+100%)        │
│      - Priority ≤ 2              │
│    Quasar:                       │
│      ☑ Nuit noire                │
│      ☑ Garantie HFD < 2.5px      │
│      - Priority jusqu'à 4        │
│      ☑ Multi-nuits (IsRepeat)    │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ 4. Estimation coût               │
│                                  │
│    💰 Coût estimé: 24 crédits    │
│    💳 Solde actuel: 60 crédits   │
│    📊 Reste après: 36 crédits    │
│                                  │
│    [Valider la cible]            │
└──────────┬──────────────────────┘
           ↓
VALIDATION
```

### 2. Traitement backend

```
User clique "Valider"
   ↓
Laravel reçoit la requête
   ↓
┌─────────────────────────────────┐
│ Validation                       │
│  ✓ User a assez de crédits ?     │
│  ✓ Abonnement autorise options ? │
│  ✓ Coordonnées valides ?         │
│  ✓ Dates cohérentes ?            │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ Calcul du coût final             │
│  - Pricing Engine                │
│  - Application multiplicateurs   │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ HOLD des crédits                 │
│  - Crédits gelés                 │
│  - Transaction "pending"         │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ Génération Payload JSON          │
│  {                               │
│    method: "RemoteRoboTargetAddTarget",│
│    params: {                     │
│      UID: "uuid...",             │
│      TargetName: "M31",          │
│      Priority: 2,                │
│      C_MoonDown: true,           │
│      C_Mask: "BK",               │
│      RefGuidSet: "set-uuid",     │
│      RAJ2000: "00:42:44.3",      │
│      DECJ2000: "+41:16:09",      │
│      ...                         │
│    }                             │
│  }                               │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ Envoi au Proxy                   │
│  POST /api/robotarget/targets    │
│  Header: X-API-Key               │
└──────────┬──────────────────────┘
           ↓
Proxy → Voyager (TCP)
   ↓
Voyager → RoboTarget Scheduler
   ↓
Cible ajoutée ✅
```

### 3. Exécution automatique

```
RoboTarget Scheduler (Voyager)
   ↓
┌─────────────────────────────────┐
│ Évaluation des contraintes       │
│  ✓ Altitude > C_AltMin           │
│  ✓ Heure angle dans range        │
│  ✓ Lune down si C_MoonDown       │
│  ✓ Date dans range               │
└──────────┬──────────────────────┘
           ↓
   Conditions OK ?
   ↓           ↓
  OUI         NON
   ↓           ↓
START      ATTENDRE
   ↓
┌─────────────────────────────────┐
│ Séquence automatique             │
│  1. Slew (pointage)              │
│  2. Center & Sync                │
│  3. Autofocus                    │
│  4. Start Guiding                │
│  5. Capture images               │
│     → Pour chaque filtre/shot    │
│     → Events en temps réel       │
│  6. Dithering (si activé)        │
│  7. Répéter jusqu'à complet      │
└──────────┬──────────────────────┘
           ↓
FIN (Result = 1, 2 ou 3)
```

### 4. Dashboard temps réel

```
User ouvre Dashboard
   ↓
Frontend → WebSocket vers Proxy
   ↓
Proxy envoie événements Voyager
   ↓
┌─────────────────────────────────┐
│ Events reçus toutes les 2s       │
│                                  │
│  ControlData:                    │
│    - VOYSTAT: 2 (RUN)            │
│    - SEQNAME: "M31_LRGB"         │
│    - SEQREMAIN: "01:23:45"       │
│    - CCDTEMP: -15°C              │
│    - GUIDESTAT: 2 (RUNNING)      │
│                                  │
│  ShotRunning:                    │
│    - Remain: 287s                │
│    - Total: 300s                 │
│    → Progress bar: 4%            │
│                                  │
│  NewFITReady:                    │
│    - File: "M31_001.fit"         │
│    - Type: LIGHT                 │
│    → Notification ✅              │
│                                  │
│  NewJPGReady:                    │
│    - Base64Data: "..."           │
│    - HFD: 2.3px ✅               │
│    → Preview image               │
└─────────────────────────────────┘
```

### 5. Fin de cible et facturation

```
RoboTarget termine la cible
   ↓
Event: RemoteRoboTargetSessionComplete
   ↓
Proxy → Broadcast WebSocket
   ↓
Laravel Webhook Handler
   ↓
┌─────────────────────────────────┐
│ Interrogation résultat           │
│  GET /api/robotarget/sessions/   │
│      {targetGuid}/result         │
└──────────┬──────────────────────┘
           ↓
┌─────────────────────────────────┐
│ Result reçu                      │
│                                  │
│  Result = 1 (OK) ✅              │
│    → CAPTURE des crédits hold    │
│    → Transaction: "completed"    │
│    → Email: "Images prêtes"      │
│                                  │
│  Result = 2/3 (Abort/Error) ⚠️   │
│    → REFUND automatique          │
│    → Transaction: "refunded"     │
│    → Email: "Remboursement"      │
└─────────────────────────────────┘
           ↓
User notifié + Images disponibles
```

---

## Implémentation technique

### Phase 1 : Laravel (Backend)

**Modèles à créer/modifier :**

```php
// app/Models/Subscription.php (NOUVEAU)
class Subscription extends Model
{
    const STARDUST = 'stardust';
    const NEBULA = 'nebula';
    const QUASAR = 'quasar';

    public function canUsePriority($priority) { ... }
    public function canUseMoonDown() { ... }
    public function canUseHFDGuarantee() { ... }
}

// app/Models/RoboTarget.php (NOUVEAU)
class RoboTarget extends Model
{
    protected $fillable = [
        'user_id', 'target_name', 'guid', 'set_guid',
        'ra_j2000', 'dec_j2000', 'priority',
        'estimated_cost', 'actual_cost', 'status'
    ];

    public function user() { ... }
    public function shots() { ... }
    public function sessions() { ... }
}

// app/Models/RoboTargetShot.php (NOUVEAU)
class RoboTargetShot extends Model
{
    // Configuration d'une prise de vue (filtre, expo, quantité)
}

// app/Models/User.php (MODIFIER)
class User extends Authenticatable
{
    use Billable, HasCredits;

    public function subscription() {
        return $this->hasOne(Subscription::class)->latest();
    }

    public function activeSubscription() {
        return $this->subscription()
            ->where('status', 'active')
            ->first();
    }
}
```

**Services :**

```php
// app/Services/PricingEngine.php (NOUVEAU)
class PricingEngine
{
    public function calculateCost(
        Subscription $sub,
        array $targetConfig
    ): int {
        $baseCost = $this->estimateDuration($targetConfig)
                  * config('credits.base_cost_per_hour');

        $multiplier = 1.0;

        // Priority multiplier
        if ($targetConfig['priority'] <= 1) {
            $multiplier *= 1.0;
        } elseif ($targetConfig['priority'] == 2) {
            $multiplier *= 1.2;
        } else {
            $multiplier *= 3.0;
        }

        // Moon down
        if ($targetConfig['c_moon_down']) {
            $multiplier *= 2.0;
        }

        // HFD guarantee
        if ($targetConfig['c_hfd_mean_limit'] > 0) {
            $multiplier *= 1.5;
        }

        return (int) ceil($baseCost * $multiplier);
    }
}

// app/Services/RoboTargetService.php (NOUVEAU)
class RoboTargetService
{
    public function createTarget(User $user, array $config): RoboTarget
    {
        // 1. Valider abonnement
        $sub = $user->activeSubscription();
        if (!$sub) throw new NoSubscriptionException();

        // 2. Valider options
        $this->validateOptions($sub, $config);

        // 3. Calculer coût
        $cost = app(PricingEngine::class)->calculateCost($sub, $config);

        // 4. Vérifier crédits
        if (!$user->hasEnoughCredits($cost)) {
            throw new InsufficientCreditsException();
        }

        // 5. HOLD crédits
        $transaction = $user->holdCredits($cost, "RoboTarget: {$config['name']}");

        // 6. Générer payload
        $payload = $this->buildPayload($config);

        // 7. Envoyer au proxy
        $result = app(VoyagerProxyService::class)
            ->addTarget($payload);

        // 8. Créer record
        $target = RoboTarget::create([
            'user_id' => $user->id,
            'guid' => $payload['params']['UID'],
            'estimated_cost' => $cost,
            'transaction_id' => $transaction->id,
            ...
        ]);

        return $target;
    }
}
```

**Migrations :**

```php
// database/migrations/2025_12_12_create_subscriptions_table.php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->enum('plan', ['stardust', 'nebula', 'quasar']);
    $table->integer('monthly_credits');
    $table->integer('price_cents');
    $table->enum('status', ['active', 'cancelled', 'expired']);
    $table->timestamp('current_period_start');
    $table->timestamp('current_period_end');
    $table->string('stripe_subscription_id')->nullable();
    $table->timestamps();
});

// database/migrations/2025_12_12_create_robo_targets_table.php
Schema::create('robo_targets', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('guid')->unique();
    $table->string('set_guid');
    $table->string('target_name');
    $table->string('ra_j2000');
    $table->string('dec_j2000');
    $table->integer('priority');
    $table->boolean('c_moon_down')->default(false);
    $table->decimal('c_hfd_mean_limit', 4, 2)->nullable();
    $table->integer('estimated_cost');
    $table->integer('actual_cost')->nullable();
    $table->enum('status', [
        'pending', 'active', 'completed', 'aborted', 'error'
    ])->default('pending');
    $table->integer('result_code')->nullable();
    $table->foreignId('transaction_id')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
});
```

### Phase 2 : Proxy Node.js

**Nouvelles routes :**

```javascript
// src/api/routes/robotarget.js
router.post('/sets', authMiddleware, async (req, res) => {
  const { Guid, Name, ProfileName, Status, Tag } = req.body;

  const result = await req.voyager.commands.addSet({
    Guid,
    Name,
    ProfileName,
    Status,
    Tag
  });

  res.json({ success: true, result });
});

router.post('/targets', authMiddleware, async (req, res) => {
  const payload = req.body;

  // Valider payload
  validateTargetPayload(payload);

  // Envoyer à Voyager
  const result = await req.voyager.commands.addTarget(payload);

  res.json({ success: true, result });
});

router.get('/sessions/:targetGuid/result', authMiddleware, async (req, res) => {
  const { targetGuid } = req.params;

  const result = await req.voyager.commands.getSessionResult(targetGuid);

  res.json({
    success: true,
    result: result.Result, // 1=OK, 2=Aborted, 3=Error
    data: result
  });
});
```

**Nouvelles commandes :**

```javascript
// src/voyager/commands/robotarget.js
class RoboTargetCommands {
  constructor(connection) {
    this.connection = connection;
  }

  async addSet(params) {
    return this.connection.send('RemoteRoboTargetAddSet', params);
  }

  async addTarget(params) {
    // Générer C_Mask dynamiquement
    let mask = '';
    if (params.C_AltMin) mask += 'B';
    if (params.C_MoonDown) mask += 'K';
    if (params.C_HFDMeanLimit) mask += 'O';
    // ... autres contraintes

    params.C_Mask = mask;

    return this.connection.send('RemoteRoboTargetAddTarget', params);
  }

  async addShot(params) {
    return this.connection.send('RemoteRoboTargetAddShot', params);
  }

  async setTargetStatus(guid, status) {
    return this.connection.send('RemoteRoboTargetSetTargetStatus', {
      GuidTarget: guid,
      Status: status
    });
  }

  async getSessionResult(targetGuid) {
    return this.connection.send('RemoteRoboTargetGetSessionListByTarget', {
      GuidTarget: targetGuid
    });
  }
}
```

### Phase 3 : Frontend (Target Planner)

**Composant Alpine.js :**

```javascript
// resources/js/components/targetPlanner.js
Alpine.data('targetPlanner', () => ({
  // État
  currentStep: 1,
  selectedObject: null,
  filters: [],
  constraints: {},
  estimatedCost: 0,
  userSubscription: null,
  userCredits: 0,

  // Init
  init() {
    this.loadUserData();
    this.loadCatalog();
  },

  // Sélection objet
  selectObject(celestialObject) {
    this.selectedObject = celestialObject;
    this.currentStep = 2;

    // Auto-fill coordonnées
    this.constraints.ra = celestialObject.ra;
    this.constraints.dec = celestialObject.dec;
  },

  // Configuration filtres
  addFilter(filter, exposure, quantity) {
    this.filters.push({ filter, exposure, quantity });
    this.calculateCost();
  },

  // Calcul coût
  async calculateCost() {
    const response = await fetch('/api/pricing/estimate', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        subscription: this.userSubscription,
        filters: this.filters,
        constraints: this.constraints
      })
    });

    const data = await response.json();
    this.estimatedCost = data.cost;
  },

  // Options selon abonnement
  get canUseMoonDown() {
    return ['nebula', 'quasar'].includes(this.userSubscription?.plan);
  },

  get canAdjustHFD() {
    return this.userSubscription?.plan === 'quasar';
  },

  get maxPriority() {
    if (this.userSubscription?.plan === 'stardust') return 1;
    if (this.userSubscription?.plan === 'nebula') return 2;
    return 4;
  },

  // Validation
  async submitTarget() {
    if (!this.validate()) return;

    const response = await fetch('/api/robotarget/targets', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        target: this.selectedObject,
        filters: this.filters,
        constraints: this.constraints
      })
    });

    if (response.ok) {
      // Notification succès
      // Redirect vers dashboard
    }
  }
}));
```

**Template Blade :**

```blade
{{-- resources/views/target-planner.blade.php --}}
<div x-data="targetPlanner" class="target-planner-container">

  {{-- Étape 1: Sélection objet --}}
  <div x-show="currentStep === 1" class="step-1">
    <h2>Sélectionnez un objet céleste</h2>

    <input
      type="text"
      x-model="searchQuery"
      placeholder="Rechercher M31, NGC7000..."
      @input.debounce="searchCatalog"
    />

    <div class="catalog-grid">
      <template x-for="object in catalogResults" :key="object.id">
        <div
          @click="selectObject(object)"
          class="catalog-card"
        >
          <img :src="object.preview" />
          <h3 x-text="object.name"></h3>
          <p x-text="object.type"></p>
          <p x-text="`RA: ${object.ra}, Dec: ${object.dec}`"></p>
        </div>
      </template>
    </div>
  </div>

  {{-- Étape 2: Configuration --}}
  <div x-show="currentStep === 2" class="step-2">
    <h2>Configuration de <span x-text="selectedObject?.name"></span></h2>

    {{-- Filtres et expositions --}}
    <div class="filters-config">
      <button @click="showFilterModal = true">
        ➕ Ajouter un filtre
      </button>

      <template x-for="(filter, index) in filters" :key="index">
        <div class="filter-row">
          <span x-text="filter.filter"></span>
          <span x-text="`${filter.exposure}s × ${filter.quantity}`"></span>
          <button @click="removeFilter(index)">🗑️</button>
        </div>
      </template>
    </div>

    {{-- Contraintes selon abonnement --}}
    <div class="constraints-config">
      <h3>Options</h3>

      {{-- Priority --}}
      <label>
        Priority
        <select
          x-model="constraints.priority"
          :disabled="userSubscription?.plan === 'stardust'"
        >
          <option value="0">Very Low</option>
          <option value="1">Low</option>
          <option value="2" :disabled="maxPriority < 2">Normal</option>
          <option value="3" :disabled="maxPriority < 3">High</option>
          <option value="4" :disabled="maxPriority < 4">First</option>
        </select>
      </label>

      {{-- Nuit noire --}}
      <label x-show="canUseMoonDown">
        <input
          type="checkbox"
          x-model="constraints.moonDown"
          @change="calculateCost"
        />
        Nuit noire uniquement (+100%)
      </label>

      {{-- Garantie HFD --}}
      <div x-show="canAdjustHFD">
        <label>
          Garantie netteté (HFD)
          <input
            type="range"
            min="1.5"
            max="4.0"
            step="0.1"
            x-model="constraints.hfdLimit"
            @input="calculateCost"
          />
          <span x-text="`< ${constraints.hfdLimit}px`"></span>
        </label>
      </div>
    </div>
  </div>

  {{-- Étape 3: Validation --}}
  <div x-show="currentStep === 3" class="step-3">
    <h2>Récapitulatif</h2>

    <div class="summary">
      <p><strong>Cible:</strong> <span x-text="selectedObject?.name"></span></p>
      <p><strong>Filtres:</strong> <span x-text="filters.length"></span> configurés</p>
      <p><strong>Durée estimée:</strong> <span x-text="estimatedDuration"></span></p>

      <div class="cost-breakdown">
        <h3>💰 Coût estimé</h3>
        <p class="cost-amount" x-text="`${estimatedCost} crédits`"></p>
        <p class="balance">Solde actuel: <span x-text="userCredits"></span> crédits</p>
        <p class="remaining">Reste après: <span x-text="userCredits - estimatedCost"></span> crédits</p>
      </div>

      <button
        @click="submitTarget"
        :disabled="userCredits < estimatedCost"
        class="btn-validate"
      >
        ✅ Valider la cible
      </button>
    </div>
  </div>

</div>
```

---

## Documentation associée

### Documentation technique

1. **[📑 Spécification Technique](./doc_voyager/📑%20Spécification%20Technique%20_%20Implémentation%20RoboTarget%20&%20Modèle%20Économique.md)**
   - Détails complets du modèle économique
   - Correspondances API Voyager
   - Formules de tarification

2. **[🏗️ Architecture Voyager Proxy](./architecture-technique-voyager-proxy.md)**
   - Architecture du proxy Node.js
   - Flux de données
   - Événements et commandes

3. **[💳 Système de Crédits v2](./CREDIT-SYSTEM-V2.md)** *(À créer)*
   - Abonnements (Stardust/Nebula/Quasar)
   - Pricing Engine
   - Hold/Capture/Refund

4. **[🎨 Frontend - Target Planner](./FRONTEND-TARGET-PLANNER.md)** *(À créer)*
   - Interface utilisateur
   - Composants Alpine.js
   - Design System Astral

5. **[🔧 Guide d'implémentation Laravel](./IMPLEMENTATION-LARAVEL.md)** *(À créer)*
   - Modèles et migrations
   - Services et contrôleurs
   - Routes et middleware

6. **[🌐 Guide d'implémentation Proxy](./IMPLEMENTATION-PROXY.md)** *(À créer)*
   - Nouvelles routes RoboTarget
   - Commandes et événements
   - Handlers spécifiques

### API RoboTarget

7. **[📘 Voyager RoboTarget Reserved API](./doc_voyager/Voyager%20RoboTarget%20Reserved%20API.md)**
   - Documentation officielle Voyager
   - Toutes les commandes RoboTarget
   - Paramètres et contraintes

---

## Prochaines étapes

### Phase actuelle : Documentation ✅

- [x] Vue d'ensemble du modèle
- [ ] Réécriture documentation crédits
- [ ] Guide implémentation Laravel
- [ ] Guide implémentation Proxy
- [ ] Guide implémentation Frontend

### Phase suivante : Développement

1. **Backend Laravel**
   - Créer modèles (Subscription, RoboTarget, RoboTargetShot)
   - Implémenter PricingEngine
   - Créer RoboTargetService
   - Routes API

2. **Proxy Node.js**
   - Routes RoboTarget
   - Commandes avancées
   - Event handlers

3. **Frontend**
   - Target Planner (Sidebar)
   - Dashboard temps réel
   - Notifications

4. **Tests**
   - Tests unitaires
   - Tests d'intégration
   - Tests end-to-end

---

**Document vivant - Mis à jour régulièrement**

*Dernière modification : 12 Décembre 2025*
