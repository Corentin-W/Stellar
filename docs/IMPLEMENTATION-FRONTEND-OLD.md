# 🎨 Guide d'implémentation Frontend - Target Planner & UI

> **Guide pratique step-by-step**
> **Version:** 1.0.0
> **Date:** 12 Décembre 2025

---

## 📋 Table des matières

1. [Prérequis](#prérequis)
2. [Structure du projet](#structure-du-projet)
3. [Composants Alpine.js](#composants-alpinejs)
4. [Vues Blade](#vues-blade)
5. [WebSocket temps réel](#websocket-temps-réel)
6. [Intégration Sidebar](#intégration-sidebar)
7. [Styling (Tailwind + Astral)](#styling)

---

## Prérequis

### Packages frontend

```bash
# Alpine.js (déjà installé normalement)
npm install alpinejs

# Socket.IO client
npm install socket.io-client

# Axios (si pas déjà installé)
npm install axios
```

### Configuration

```javascript
// resources/js/app.js (vérifier/ajouter)

import Alpine from 'alpinejs';
import { io } from 'socket.io-client';

window.Alpine = Alpine;
window.io = io;

Alpine.start();
```

---

## Structure du projet

```
resources/
├── js/
│   ├── app.js                        (existant)
│   ├── components/
│   │   ├── subscriptionPicker.js    ✨ NOUVEAU
│   │   ├── targetPlanner.js         ✨ NOUVEAU
│   │   ├── catalogBrowser.js        ✨ NOUVEAU
│   │   ├── shotConfigurator.js      ✨ NOUVEAU
│   │   ├── dashboardRoboTarget.js   ✨ NOUVEAU
│   │   └── costEstimator.js         ✨ NOUVEAU
│   │
│   └── utils/
│       └── websocket.js              ✨ NOUVEAU
│
├── views/
│   ├── subscriptions/
│   │   ├── choose.blade.php         ✨ NOUVEAU
│   │   └── manage.blade.php         ✨ NOUVEAU
│   │
│   ├── robotarget/
│   │   ├── target-planner.blade.php ✨ NOUVEAU
│   │   ├── my-targets.blade.php     ✨ NOUVEAU
│   │   └── target-detail.blade.php  ✨ NOUVEAU
│   │
│   └── layouts/
│       └── partials/
│           └── astral-sidebar.blade.php  🔄 MODIFIER
│
└── css/
    └── app.css                       (existant - Astral theme)
```

---

## Composants Alpine.js

### 1. SubscriptionPicker Component

```javascript
// resources/js/components/subscriptionPicker.js

export default function subscriptionPicker() {
  return {
    // État
    selectedPlan: null,
    plans: [],
    loading: false,
    error: null,

    // Init
    async init() {
      await this.loadPlans();
    },

    // Load plans from API
    async loadPlans() {
      try {
        this.loading = true;
        const response = await axios.get('/api/subscriptions/plans');
        this.plans = response.data.plans;
      } catch (error) {
        this.error = 'Erreur lors du chargement des abonnements';
        console.error(error);
      } finally {
        this.loading = false;
      }
    },

    // Select plan
    selectPlan(plan) {
      this.selectedPlan = plan;
    },

    // Subscribe
    async subscribe() {
      if (!this.selectedPlan) return;

      try {
        this.loading = true;

        const response = await axios.post('/api/subscriptions/subscribe', {
          plan: this.selectedPlan.id,
          payment_method_id: this.paymentMethodId // From Stripe Elements
        });

        if (response.data.success) {
          window.location.href = '/dashboard';
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la souscription';
      } finally {
        this.loading = false;
      }
    },

    // Helpers
    getPlanBadge(plan) {
      return {
        'stardust': '🌟',
        'nebula': '🌌',
        'quasar': '⚡'
      }[plan] || '';
    },

    getPlanColor(plan) {
      return {
        'stardust': 'blue',
        'nebula': 'purple',
        'quasar': 'yellow'
      }[plan] || 'gray';
    }
  };
}

// Register component
document.addEventListener('alpine:init', () => {
  Alpine.data('subscriptionPicker', subscriptionPicker);
});
```

### 2. TargetPlanner Component (Principal)

```javascript
// resources/js/components/targetPlanner.js

export default function targetPlanner() {
  return {
    // État navigation
    currentStep: 1,
    steps: ['select', 'configure', 'constraints', 'review'],

    // Données
    selectedObject: null,
    catalogResults: [],
    searchQuery: '',
    shots: [],
    constraints: {
      priority: 0,
      moonDown: false,
      hfdLimit: 0,
      altMin: 30,
      haStart: -12,
      haEnd: 12,
      dateStart: null,
      dateEnd: null
    },

    // User data
    userSubscription: null,
    userCredits: 0,

    // Coût
    estimatedCost: 0,
    costBreakdown: null,

    // UI state
    loading: false,
    error: null,
    showFilterModal: false,

    // Init
    async init() {
      await this.loadUserData();
      await this.loadCatalog();
    },

    // Load user data
    async loadUserData() {
      try {
        const response = await axios.get('/api/user/subscription');
        this.userSubscription = response.data.subscription;
        this.userCredits = response.data.credits_balance;

        // Set default priority based on plan
        this.constraints.priority = this.getDefaultPriority();
      } catch (error) {
        console.error('Error loading user data:', error);
      }
    },

    // Load celestial objects catalog
    async loadCatalog() {
      try {
        const response = await axios.get('/api/catalog/objects');
        this.catalogResults = response.data.objects;
      } catch (error) {
        console.error('Error loading catalog:', error);
      }
    },

    // Search catalog
    async searchCatalog() {
      if (!this.searchQuery) {
        await this.loadCatalog();
        return;
      }

      try {
        const response = await axios.get(`/api/catalog/search?q=${this.searchQuery}`);
        this.catalogResults = response.data.objects;
      } catch (error) {
        console.error('Error searching catalog:', error);
      }
    },

    // Select celestial object
    selectObject(object) {
      this.selectedObject = object;
      this.currentStep = 2;

      // Auto-fill coordinates
      this.constraints.ra = object.ra;
      this.constraints.dec = object.dec;
    },

    // Add filter shot
    addShot(filter, exposure, quantity, gain, offset) {
      this.shots.push({
        filter_index: filter.index,
        filter_name: filter.name,
        exposure: exposure,
        num: quantity,
        gain: gain || 100,
        offset: offset || 50,
        bin: 1,
        type: 0 // LIGHT
      });

      this.showFilterModal = false;
      this.calculateCost();
    },

    // Remove shot
    removeShot(index) {
      this.shots.splice(index, 1);
      this.calculateCost();
    },

    // Calculate cost
    async calculateCost() {
      if (this.shots.length === 0) {
        this.estimatedCost = 0;
        return;
      }

      try {
        const response = await axios.post('/api/pricing/estimate', {
          subscription_plan: this.userSubscription?.plan,
          target: {
            priority: this.constraints.priority,
            c_moon_down: this.constraints.moonDown,
            c_hfd_mean_limit: this.constraints.hfdLimit,
            shots: this.shots
          }
        });

        this.estimatedCost = response.data.estimation.final_cost;
        this.costBreakdown = response.data.estimation;
      } catch (error) {
        console.error('Error calculating cost:', error);
      }
    },

    // Navigate steps
    nextStep() {
      if (this.currentStep < 4) {
        this.currentStep++;
      }
    },

    prevStep() {
      if (this.currentStep > 1) {
        this.currentStep--;
      }
    },

    goToStep(step) {
      this.currentStep = step;
    },

    // Submit target
    async submitTarget() {
      if (!this.validate()) return;

      try {
        this.loading = true;

        const response = await axios.post('/api/robotarget/targets', {
          target: this.selectedObject,
          shots: this.shots,
          constraints: this.constraints
        });

        if (response.data.success) {
          // Success notification
          this.showNotification('Cible créée avec succès !', 'success');

          // Redirect to dashboard
          setTimeout(() => {
            window.location.href = '/dashboard/robotarget';
          }, 1500);
        }
      } catch (error) {
        this.error = error.response?.data?.message || 'Erreur lors de la création de la cible';
        this.showNotification(this.error, 'error');
      } finally {
        this.loading = false;
      }
    },

    // Validation
    validate() {
      if (!this.selectedObject) {
        this.error = 'Veuillez sélectionner un objet céleste';
        return false;
      }

      if (this.shots.length === 0) {
        this.error = 'Veuillez ajouter au moins un filtre';
        return false;
      }

      if (this.userCredits < this.estimatedCost) {
        this.error = 'Crédits insuffisants';
        return false;
      }

      return true;
    },

    // Feature checks
    get canUseMoonDown() {
      return ['nebula', 'quasar'].includes(this.userSubscription?.plan);
    },

    get canAdjustHFD() {
      return this.userSubscription?.plan === 'quasar';
    },

    get maxPriority() {
      if (!this.userSubscription) return 0;
      return {
        'stardust': 1,
        'nebula': 2,
        'quasar': 4
      }[this.userSubscription.plan] || 0;
    },

    getDefaultPriority() {
      return this.userSubscription?.plan === 'stardust' ? 0 : 2;
    },

    // Helpers
    getTotalExposureTime() {
      return this.shots.reduce((total, shot) => {
        return total + (shot.exposure * shot.num);
      }, 0);
    },

    getEstimatedDuration() {
      const totalSeconds = this.getTotalExposureTime();
      const overheadSeconds = this.shots.reduce((total, shot) => total + shot.num, 0) * 30;
      return this.formatDuration(totalSeconds + overheadSeconds);
    },

    formatDuration(seconds) {
      const hours = Math.floor(seconds / 3600);
      const minutes = Math.floor((seconds % 3600) / 60);
      return `${hours}h ${minutes}m`;
    },

    showNotification(message, type) {
      // Implement with your notification system
      console.log(`[${type}] ${message}`);
    }
  };
}

// Register component
document.addEventListener('alpine:init', () => {
  Alpine.data('targetPlanner', targetPlanner);
});
```

### 3. DashboardRoboTarget Component

```javascript
// resources/js/components/dashboardRoboTarget.js

import { io } from 'socket.io-client';

export default function dashboardRoboTarget() {
  return {
    // WebSocket
    socket: null,
    connected: false,

    // Targets
    targets: [],
    activeTarget: null,

    // Real-time data
    controlData: null,
    progress: {},
    latestImages: [],

    // UI state
    loading: true,

    // Init
    async init() {
      await this.loadTargets();
      this.connectWebSocket();
    },

    // Load user targets
    async loadTargets() {
      try {
        const response = await axios.get('/api/robotarget/my-targets');
        this.targets = response.data.targets;
        this.loading = false;
      } catch (error) {
        console.error('Error loading targets:', error);
      }
    },

    // Connect to WebSocket
    connectWebSocket() {
      const proxyUrl = window.VOYAGER_PROXY_URL || 'http://localhost:3000';

      this.socket = io(proxyUrl, {
        transports: ['websocket', 'polling']
      });

      this.socket.on('connect', () => {
        console.log('WebSocket connected');
        this.connected = true;
      });

      this.socket.on('disconnect', () => {
        console.log('WebSocket disconnected');
        this.connected = false;
      });

      // Listen to control data
      this.socket.on('controlData', (data) => {
        this.controlData = data;
        this.updateTargetsStatus(data);
      });

      // Listen to RoboTarget events
      this.socket.on('robotargetProgress', (data) => {
        this.updateProgress(data);
      });

      this.socket.on('robotargetSessionComplete', (data) => {
        this.handleSessionComplete(data);
      });

      this.socket.on('newFITReady', (data) => {
        this.handleNewImage(data);
      });
    },

    // Update targets status from control data
    updateTargetsStatus(controlData) {
      // Update active target info based on sequence name
      if (controlData.SEQNAME) {
        const target = this.targets.find(t => t.target_name === controlData.SEQNAME);
        if (target) {
          target.status = 'executing';
          target.progress = controlData.SEQPROGRESS || 0;
          this.activeTarget = target;
        }
      }
    },

    // Update progress
    updateProgress(data) {
      this.progress[data.parsed.guid_target] = data.parsed;

      // Update target in list
      const target = this.targets.find(t => t.guid === data.parsed.guid_target);
      if (target) {
        target.progress = data.parsed.percentage;
      }
    },

    // Handle session complete
    async handleSessionComplete(data) {
      const target = this.targets.find(t => t.guid === data.parsed.guid_target);

      if (target) {
        target.status = data.parsed.result === 'OK' ? 'completed' : 'error';
        target.result = data.parsed.result;

        // Show notification
        if (data.parsed.result === 'OK') {
          this.showNotification(`🎉 Cible "${target.target_name}" terminée !`, 'success');
        } else {
          this.showNotification(`⚠️ Cible "${target.target_name}" en erreur`, 'error');
        }

        // Reload targets to get updated data
        await this.loadTargets();
      }
    },

    // Handle new image
    handleNewImage(data) {
      this.latestImages.unshift({
        file: data.File,
        target: data.SeqTarget,
        filter: data.Filter,
        hfd: data.HFD,
        timestamp: Date.now()
      });

      // Keep only last 10 images
      if (this.latestImages.length > 10) {
        this.latestImages = this.latestImages.slice(0, 10);
      }
    },

    // Helpers
    getTargetStatusColor(status) {
      return {
        'pending': 'yellow',
        'active': 'blue',
        'executing': 'purple',
        'completed': 'green',
        'error': 'red',
        'aborted': 'red'
      }[status] || 'gray';
    },

    getTargetStatusLabel(status) {
      return {
        'pending': '⏳ En attente',
        'active': '✅ Active',
        'executing': '🔄 En cours',
        'completed': '✅ Terminée',
        'error': '⚠️ Erreur',
        'aborted': '❌ Annulée'
      }[status] || status;
    },

    showNotification(message, type) {
      // Implement with your notification system
      console.log(`[${type}] ${message}`);
    },

    // Cleanup
    destroy() {
      if (this.socket) {
        this.socket.disconnect();
      }
    }
  };
}

// Register component
document.addEventListener('alpine:init', () => {
  Alpine.data('dashboardRoboTarget', dashboardRoboTarget);
});
```

---

## Vues Blade

### 1. Subscription Picker

```blade
{{-- resources/views/subscriptions/choose.blade.php --}}

<x-app-layout>
    <div class="min-h-screen bg-gradient-to-b from-gray-900 to-black py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            {{-- Header --}}
            <div class="text-center mb-12">
                <h1 class="text-4xl font-bold text-white mb-4">
                    Choisissez votre abonnement
                </h1>
                <p class="text-xl text-gray-300">
                    Accédez à l'astrophotographie distante automatisée
                </p>
            </div>

            {{-- Plans --}}
            <div x-data="subscriptionPicker" class="grid md:grid-cols-3 gap-8">

                {{-- Stardust Plan --}}
                <div class="bg-gray-800 rounded-2xl p-8 border-2 border-gray-700 hover:border-blue-500 transition-all duration-300">
                    <div class="text-center mb-6">
                        <span class="text-5xl">🌟</span>
                        <h3 class="text-2xl font-bold text-white mt-4">Stardust</h3>
                        <p class="text-gray-400 mt-2">Pour débuter</p>
                    </div>

                    <div class="text-center mb-6">
                        <span class="text-5xl font-bold text-white">29€</span>
                        <span class="text-gray-400">/mois</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            20 crédits/mois
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Priorité Low
                        </li>
                        <li class="flex items-center text-gray-400">
                            <svg class="w-5 h-5 text-gray-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                            Pas de nuit noire
                        </li>
                    </ul>

                    <button
                        @click="selectPlan('stardust')"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200"
                    >
                        Choisir Stardust
                    </button>
                </div>

                {{-- Nebula Plan --}}
                <div class="bg-gradient-to-br from-purple-900 to-blue-900 rounded-2xl p-8 border-2 border-purple-500 transform scale-105 relative">
                    {{-- Popular badge --}}
                    <div class="absolute -top-4 left-1/2 transform -translate-x-1/2">
                        <span class="bg-purple-600 text-white px-4 py-1 rounded-full text-sm font-bold">
                            POPULAIRE
                        </span>
                    </div>

                    <div class="text-center mb-6">
                        <span class="text-5xl">🌌</span>
                        <h3 class="text-2xl font-bold text-white mt-4">Nebula</h3>
                        <p class="text-purple-200 mt-2">Pour amateurs confirmés</p>
                    </div>

                    <div class="text-center mb-6">
                        <span class="text-5xl font-bold text-white">59€</span>
                        <span class="text-purple-200">/mois</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            60 crédits/mois
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Priorité Normal
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Option Nuit noire
                        </li>
                        <li class="flex items-center text-white">
                            <svg class="w-5 h-5 text-green-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Dashboard temps réel
                        </li>
                    </ul>

                    <button
                        @click="selectPlan('nebula')"
                        class="w-full bg-white hover:bg-gray-100 text-purple-900 font-bold py-3 px-6 rounded-lg transition-colors duration-200"
                    >
                        Choisir Nebula
                    </button>
                </div>

                {{-- Quasar Plan --}}
                <div class="bg-gray-800 rounded-2xl p-8 border-2 border-yellow-500 hover:border-yellow-400 transition-all duration-300">
                    <div class="text-center mb-6">
                        <span class="text-5xl">⚡</span>
                        <h3 class="text-2xl font-bold text-white mt-4">Quasar</h3>
                        <p class="text-gray-400 mt-2">Pour experts VIP</p>
                    </div>

                    <div class="text-center mb-6">
                        <span class="text-5xl font-bold text-white">119€</span>
                        <span class="text-gray-400">/mois</span>
                    </div>

                    <ul class="space-y-3 mb-8">
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            150 crédits/mois
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Priorité First (coupe-file)
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Nuit noire incluse
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Garantie netteté (HFD)
                        </li>
                        <li class="flex items-center text-gray-300">
                            <svg class="w-5 h-5 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            Projets multi-nuits
                        </li>
                    </ul>

                    <button
                        @click="selectPlan('quasar')"
                        class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-bold py-3 px-6 rounded-lg transition-colors duration-200"
                    >
                        Choisir Quasar
                    </button>
                </div>

            </div>

        </div>
    </div>
</x-app-layout>
```

### 2. Target Planner (Simplifié)

```blade
{{-- resources/views/robotarget/target-planner.blade.php --}}

<x-app-layout>
    <div class="min-h-screen bg-gradient-to-b from-gray-900 to-black py-8" x-data="targetPlanner">

        {{-- Steps indicator --}}
        <div class="max-w-4xl mx-auto px-4 mb-8">
            <div class="flex items-center justify-between">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center">
                        <div
                            class="w-10 h-10 rounded-full flex items-center justify-center font-bold"
                            :class="currentStep > index ? 'bg-green-500 text-white' : currentStep === index + 1 ? 'bg-blue-500 text-white' : 'bg-gray-700 text-gray-400'"
                            x-text="index + 1"
                        ></div>
                        <div
                            x-show="index < steps.length - 1"
                            class="w-24 h-1 mx-2"
                            :class="currentStep > index + 1 ? 'bg-green-500' : 'bg-gray-700'"
                        ></div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Step 1: Select object --}}
        <div x-show="currentStep === 1" class="max-w-6xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-6">Sélectionnez un objet céleste</h2>

            <input
                type="text"
                x-model="searchQuery"
                @input.debounce="searchCatalog"
                placeholder="Rechercher M31, NGC7000..."
                class="w-full bg-gray-800 text-white border border-gray-700 rounded-lg px-4 py-3 mb-6"
            />

            <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-4">
                <template x-for="object in catalogResults" :key="object.id">
                    <div
                        @click="selectObject(object)"
                        class="bg-gray-800 rounded-lg p-4 cursor-pointer hover:bg-gray-700 transition-colors"
                    >
                        <img
                            :src="object.preview_url"
                            :alt="object.name"
                            class="w-full h-32 object-cover rounded mb-3"
                        />
                        <h3 class="text-white font-bold" x-text="object.name"></h3>
                        <p class="text-gray-400 text-sm" x-text="object.type"></p>
                        <p class="text-gray-500 text-xs mt-1">
                            RA: <span x-text="object.ra"></span> |
                            Dec: <span x-text="object.dec"></span>
                        </p>
                    </div>
                </template>
            </div>
        </div>

        {{-- Step 2: Configure shots --}}
        <div x-show="currentStep === 2" class="max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-6">
                Configuration de <span x-text="selectedObject?.name"></span>
            </h2>

            {{-- Shot list --}}
            <div class="mb-6">
                <button
                    @click="showFilterModal = true"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
                >
                    ➕ Ajouter un filtre
                </button>

                <div class="mt-4 space-y-2">
                    <template x-for="(shot, index) in shots" :key="index">
                        <div class="bg-gray-800 rounded-lg p-4 flex items-center justify-between">
                            <div class="text-white">
                                <span x-text="shot.filter_name"></span>:
                                <span x-text="`${shot.num} x ${shot.exposure}s`"></span>
                            </div>
                            <button
                                @click="removeShot(index)"
                                class="text-red-500 hover:text-red-600"
                            >
                                🗑️
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Navigation --}}
            <div class="flex justify-between">
                <button
                    @click="prevStep"
                    class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded"
                >
                    ← Précédent
                </button>
                <button
                    @click="nextStep"
                    :disabled="shots.length === 0"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    Suivant →
                </button>
            </div>
        </div>

        {{-- Step 3: Constraints --}}
        <div x-show="currentStep === 3" class="max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-6">Options et contraintes</h2>

            <div class="bg-gray-800 rounded-lg p-6 space-y-6">

                {{-- Priority --}}
                <div>
                    <label class="block text-white font-bold mb-2">Priorité</label>
                    <select
                        x-model.number="constraints.priority"
                        @change="calculateCost"
                        class="w-full bg-gray-700 text-white border border-gray-600 rounded px-4 py-2"
                    >
                        <option value="0">Very Low</option>
                        <option value="1">Low</option>
                        <option value="2" :disabled="maxPriority < 2">Normal</option>
                        <option value="3" :disabled="maxPriority < 3">High</option>
                        <option value="4" :disabled="maxPriority < 4">First</option>
                    </select>
                </div>

                {{-- Moon Down --}}
                <div x-show="canUseMoonDown">
                    <label class="flex items-center space-x-3 cursor-pointer">
                        <input
                            type="checkbox"
                            x-model="constraints.moonDown"
                            @change="calculateCost"
                            class="form-checkbox h-5 w-5 text-blue-600"
                        />
                        <span class="text-white font-bold">
                            Nuit noire uniquement (+100% crédits)
                        </span>
                    </label>
                </div>

                {{-- HFD Guarantee --}}
                <div x-show="canAdjustHFD">
                    <label class="block text-white font-bold mb-2">
                        Garantie netteté (HFD)
                    </label>
                    <input
                        type="range"
                        min="1.5"
                        max="4.0"
                        step="0.1"
                        x-model.number="constraints.hfdLimit"
                        @input="calculateCost"
                        class="w-full"
                    />
                    <div class="text-gray-400 mt-1">
                        < <span x-text="constraints.hfdLimit"></span> pixels
                    </div>
                </div>

            </div>

            {{-- Navigation --}}
            <div class="flex justify-between mt-6">
                <button
                    @click="prevStep"
                    class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded"
                >
                    ← Précédent
                </button>
                <button
                    @click="nextStep"
                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded"
                >
                    Suivant →
                </button>
            </div>
        </div>

        {{-- Step 4: Review & Submit --}}
        <div x-show="currentStep === 4" class="max-w-4xl mx-auto px-4">
            <h2 class="text-3xl font-bold text-white mb-6">Récapitulatif</h2>

            <div class="bg-gray-800 rounded-lg p-6 mb-6">
                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white mb-2">Cible</h3>
                    <p class="text-gray-300" x-text="selectedObject?.name"></p>
                </div>

                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white mb-2">
                        Filtres (<span x-text="shots.length"></span>)
                    </h3>
                    <ul class="space-y-1">
                        <template x-for="shot in shots">
                            <li class="text-gray-300">
                                <span x-text="shot.filter_name"></span>:
                                <span x-text="`${shot.num} x ${shot.exposure}s`"></span>
                            </li>
                        </template>
                    </ul>
                </div>

                <div class="mb-6">
                    <h3 class="text-xl font-bold text-white mb-2">Durée estimée</h3>
                    <p class="text-gray-300" x-text="getEstimatedDuration()"></p>
                </div>

                {{-- Cost Breakdown --}}
                <div class="bg-gray-900 rounded-lg p-6 mt-6">
                    <h3 class="text-2xl font-bold text-white mb-4">💰 Coût estimé</h3>

                    <div class="text-5xl font-bold text-blue-400 mb-4">
                        <span x-text="estimatedCost"></span> crédits
                    </div>

                    <div class="space-y-2 text-gray-300">
                        <div class="flex justify-between">
                            <span>Solde actuel:</span>
                            <span class="font-bold" x-text="`${userCredits} crédits`"></span>
                        </div>
                        <div class="flex justify-between">
                            <span>Reste après:</span>
                            <span
                                class="font-bold"
                                :class="userCredits - estimatedCost >= 0 ? 'text-green-400' : 'text-red-400'"
                                x-text="`${userCredits - estimatedCost} crédits`"
                            ></span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex justify-between">
                <button
                    @click="prevStep"
                    class="bg-gray-700 hover:bg-gray-600 text-white font-bold py-2 px-6 rounded"
                >
                    ← Précédent
                </button>
                <button
                    @click="submitTarget"
                    :disabled="loading || userCredits < estimatedCost"
                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-8 rounded disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    <span x-show="!loading">✅ Valider la cible</span>
                    <span x-show="loading">⏳ Création en cours...</span>
                </button>
            </div>
        </div>

    </div>
</x-app-layout>
```

---

## WebSocket temps réel

### Configuration WebSocket

```javascript
// resources/js/utils/websocket.js

import { io } from 'socket.io-client';

export class VoyagerWebSocket {
  constructor() {
    this.socket = null;
    this.connected = false;
    this.listeners = new Map();
  }

  connect(url = null) {
    const socketUrl = url || window.VOYAGER_PROXY_URL || 'http://localhost:3000';

    this.socket = io(socketUrl, {
      transports: ['websocket', 'polling'],
      reconnection: true,
      reconnectionDelay: 1000,
      reconnectionDelayMax: 5000,
      reconnectionAttempts: Infinity
    });

    this.setupHandlers();
    return this.socket;
  }

  setupHandlers() {
    this.socket.on('connect', () => {
      console.log('✅ WebSocket connected');
      this.connected = true;
      this.emit('connection:status', { connected: true });
    });

    this.socket.on('disconnect', () => {
      console.log('❌ WebSocket disconnected');
      this.connected = false;
      this.emit('connection:status', { connected: false });
    });

    this.socket.on('error', (error) => {
      console.error('WebSocket error:', error);
      this.emit('connection:error', error);
    });

    // Forward all Voyager events
    this.socket.onAny((eventName, data) => {
      this.emit(eventName, data);
    });
  }

  on(event, callback) {
    if (!this.listeners.has(event)) {
      this.listeners.set(event, []);
    }
    this.listeners.get(event).push(callback);
  }

  off(event, callback) {
    if (!this.listeners.has(event)) return;
    const callbacks = this.listeners.get(event);
    const index = callbacks.indexOf(callback);
    if (index > -1) {
      callbacks.splice(index, 1);
    }
  }

  emit(event, data) {
    if (!this.listeners.has(event)) return;
    this.listeners.get(event).forEach(callback => callback(data));
  }

  disconnect() {
    if (this.socket) {
      this.socket.disconnect();
      this.socket = null;
      this.connected = false;
    }
  }
}

// Singleton instance
export const voyagerWS = new VoyagerWebSocket();
```

### Utilisation dans les composants

```javascript
// Dans un composant Alpine.js
import { voyagerWS } from '@/utils/websocket';

export default function myComponent() {
  return {
    init() {
      voyagerWS.connect();

      // Listen to control data
      voyagerWS.on('controlData', (data) => {
        this.handleControlData(data);
      });

      // Listen to RoboTarget events
      voyagerWS.on('robotargetProgress', (data) => {
        this.updateProgress(data);
      });

      voyagerWS.on('robotargetSessionComplete', (data) => {
        this.handleComplete(data);
      });
    },

    destroy() {
      // Cleanup listeners when component is destroyed
      voyagerWS.off('controlData', this.handleControlData);
      voyagerWS.off('robotargetProgress', this.updateProgress);
    }
  };
}
```

---

## Intégration Sidebar

### Modification de la Sidebar Astrale

```blade
{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}

<aside class="astral-sidebar">
    {{-- User info --}}
    <div class="sidebar-user-section">
        <div class="flex items-center justify-between p-4">
            <div>
                <p class="text-white font-bold">{{ auth()->user()->name }}</p>
                <p class="text-gray-400 text-sm">{{ auth()->user()->email }}</p>
            </div>

            @if(auth()->user()->subscription)
                <div class="subscription-badge badge-{{ auth()->user()->subscription->plan }}">
                    {{ auth()->user()->subscription->getPlanBadge() }}
                </div>
            @endif
        </div>

        {{-- Credits balance --}}
        @if(auth()->user()->subscription)
            <div class="px-4 py-2 bg-gray-800 border-t border-gray-700">
                <div class="flex items-center justify-between">
                    <span class="text-gray-400 text-sm">Crédits restants</span>
                    <span class="text-white font-bold">
                        {{ auth()->user()->credits_balance }} 💫
                    </span>
                </div>
                <div class="mt-1 bg-gray-700 rounded-full h-2">
                    <div
                        class="bg-blue-500 h-2 rounded-full"
                        style="width: {{ (auth()->user()->credits_balance / auth()->user()->subscription->credits_per_month) * 100 }}%"
                    ></div>
                </div>
            </div>
        @endif
    </div>

    {{-- Navigation --}}
    <nav class="sidebar-nav mt-6">

        {{-- Dashboard --}}
        <a href="/dashboard" class="sidebar-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <span class="sidebar-icon">🏠</span>
            <span class="sidebar-text">Dashboard</span>
        </a>

        {{-- RoboTarget Section --}}
        <div class="sidebar-section mt-6">
            <p class="sidebar-section-title">🤖 RoboTarget</p>

            <a href="/target-planner" class="sidebar-item {{ request()->is('target-planner') ? 'active' : '' }}">
                <span class="sidebar-icon">🎯</span>
                <span class="sidebar-text">Target Planner</span>
            </a>

            <a href="/my-targets" class="sidebar-item {{ request()->is('my-targets') ? 'active' : '' }}">
                <span class="sidebar-icon">📋</span>
                <span class="sidebar-text">Mes Cibles</span>
                @if($pendingTargets = auth()->user()->roboTargets()->where('status', 'executing')->count())
                    <span class="sidebar-badge badge-blue">{{ $pendingTargets }}</span>
                @endif
            </a>

            <a href="/dashboard/robotarget" class="sidebar-item {{ request()->is('dashboard/robotarget') ? 'active' : '' }}">
                <span class="sidebar-icon">📊</span>
                <span class="sidebar-text">Temps réel</span>
                <span class="sidebar-indicator pulse-green" x-show="voyagerConnected"></span>
            </a>
        </div>

        {{-- Equipment Section (existing) --}}
        <div class="sidebar-section mt-6">
            <p class="sidebar-section-title">🔭 Équipement</p>

            <a href="/equipment" class="sidebar-item {{ request()->is('equipment') ? 'active' : '' }}">
                <span class="sidebar-icon">🔧</span>
                <span class="sidebar-text">Liste</span>
            </a>

            <a href="/control" class="sidebar-item {{ request()->is('control') ? 'active' : '' }}">
                <span class="sidebar-icon">🎮</span>
                <span class="sidebar-text">Contrôle manuel</span>
            </a>
        </div>

        {{-- Account Section --}}
        <div class="sidebar-section mt-6">
            <p class="sidebar-section-title">👤 Compte</p>

            <a href="/subscriptions" class="sidebar-item {{ request()->is('subscriptions*') ? 'active' : '' }}">
                <span class="sidebar-icon">💳</span>
                <span class="sidebar-text">Abonnement</span>
                @if(auth()->user()->subscription)
                    <span class="sidebar-badge badge-{{ auth()->user()->subscription->plan }}">
                        {{ strtoupper(auth()->user()->subscription->plan) }}
                    </span>
                @endif
            </a>

            <a href="/history" class="sidebar-item {{ request()->is('history') ? 'active' : '' }}">
                <span class="sidebar-icon">📜</span>
                <span class="sidebar-text">Historique</span>
            </a>

            <a href="/profile" class="sidebar-item {{ request()->is('profile') ? 'active' : '' }}">
                <span class="sidebar-icon">⚙️</span>
                <span class="sidebar-text">Paramètres</span>
            </a>
        </div>

    </nav>

    {{-- Footer --}}
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sidebar-item hover:bg-red-600 w-full text-left">
                <span class="sidebar-icon">🚪</span>
                <span class="sidebar-text">Déconnexion</span>
            </button>
        </form>
    </div>
</aside>
```

### Ajout des styles pour les badges

```css
/* resources/css/components/sidebar.css */

.subscription-badge {
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: bold;
  text-transform: uppercase;
}

.badge-stardust {
  background: linear-gradient(135deg, #3B82F6, #2563EB);
  color: white;
}

.badge-nebula {
  background: linear-gradient(135deg, #8B5CF6, #6D28D9);
  color: white;
}

.badge-quasar {
  background: linear-gradient(135deg, #F59E0B, #D97706);
  color: white;
}

.sidebar-badge {
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  font-size: 0.625rem;
  font-weight: bold;
  margin-left: auto;
}

.badge-blue {
  background-color: #3B82F6;
  color: white;
}

.sidebar-indicator {
  width: 8px;
  height: 8px;
  border-radius: 50%;
  margin-left: auto;
}

.pulse-green {
  background-color: #10B981;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}
```

### Alpine.js pour la sidebar (indicateurs temps réel)

```javascript
// resources/js/components/sidebar.js

import { voyagerWS } from '@/utils/websocket';

export default function sidebar() {
  return {
    voyagerConnected: false,
    executingTargets: 0,
    completedToday: 0,

    init() {
      this.loadStats();
      this.connectWebSocket();

      // Refresh stats every minute
      setInterval(() => this.loadStats(), 60000);
    },

    async loadStats() {
      try {
        const response = await axios.get('/api/robotarget/stats');
        this.executingTargets = response.data.executing;
        this.completedToday = response.data.completed_today;
      } catch (error) {
        console.error('Error loading stats:', error);
      }
    },

    connectWebSocket() {
      voyagerWS.connect();

      voyagerWS.on('connection:status', ({ connected }) => {
        this.voyagerConnected = connected;
      });

      voyagerWS.on('robotargetProgress', () => {
        this.loadStats();
      });

      voyagerWS.on('robotargetSessionComplete', () => {
        this.loadStats();
      });
    }
  };
}

// Register component
document.addEventListener('alpine:init', () => {
  Alpine.data('sidebar', sidebar);
});
```

---

## Styling

### Astral Design System - Usage

Le projet utilise déjà le **Design System Astral** documenté dans `astral_documentation.md`.

#### Classes Tailwind principales utilisées

```css
/* Couleurs */
bg-gray-900      /* Background principal */
bg-gray-800      /* Cards */
bg-gray-700      /* Inputs */
text-white       /* Texte principal */
text-gray-400    /* Texte secondaire */

/* Plans */
text-blue-500    /* Stardust */
text-purple-500  /* Nebula */
text-yellow-500  /* Quasar */

/* Status */
text-green-500   /* Success */
text-red-500     /* Error */
text-yellow-500  /* Pending */

/* Animations */
transition-all duration-300
hover:scale-105
animate-pulse
```

#### Composants réutilisables

```blade
{{-- Button Primary --}}
<button class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded-lg transition-colors duration-200">
    Texte
</button>

{{-- Card --}}
<div class="bg-gray-800 rounded-lg p-6 border border-gray-700 hover:border-gray-600 transition-all">
    Contenu
</div>

{{-- Input --}}
<input
    type="text"
    class="w-full bg-gray-700 text-white border border-gray-600 rounded-lg px-4 py-2 focus:border-blue-500 focus:outline-none"
/>

{{-- Badge Status --}}
<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-900 text-green-200">
    ✅ Active
</span>
```

#### Gradients personnalisés

```css
/* Stardust gradient */
.gradient-stardust {
  background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
}

/* Nebula gradient */
.gradient-nebula {
  background: linear-gradient(135deg, #8B5CF6 0%, #6D28D9 100%);
}

/* Quasar gradient */
.gradient-quasar {
  background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);
}

/* Background animated */
.bg-animated {
  background: linear-gradient(
    to bottom,
    theme('colors.gray.900') 0%,
    theme('colors.black') 100%
  );
}
```

### Responsive Design

```blade
{{-- Mobile First --}}
<div class="
    grid
    grid-cols-1       /* Mobile: 1 colonne */
    md:grid-cols-2    /* Tablet: 2 colonnes */
    lg:grid-cols-3    /* Desktop: 3 colonnes */
    gap-4
">
    <!-- Content -->
</div>

{{-- Sidebar responsive --}}
<aside class="
    hidden            /* Caché sur mobile */
    lg:block          /* Visible sur desktop */
    w-64
">
    <!-- Sidebar -->
</aside>
```

### Dark Mode (déjà activé)

Le projet est entièrement conçu en dark mode par défaut. Les couleurs sont optimisées pour:
- Réduire la fatigue oculaire
- Thème "cosmos/espace"
- Contraste optimal pour les images astronomiques

---

## 🎯 Checklist finale

### Composants Alpine.js
- [x] subscriptionPicker
- [x] targetPlanner
- [x] dashboardRoboTarget
- [x] sidebar (avec WebSocket)

### Vues Blade
- [x] subscriptions/choose.blade.php
- [x] robotarget/target-planner.blade.php
- [ ] robotarget/my-targets.blade.php (à créer)
- [ ] robotarget/target-detail.blade.php (à créer)

### Utilitaires
- [x] websocket.js
- [x] Sidebar integration

### Routes à ajouter
```php
// routes/web.php

Route::middleware(['auth', 'verified'])->group(function () {
    // Subscriptions
    Route::get('/subscriptions', [SubscriptionController::class, 'choose'])->name('subscriptions.choose');
    Route::get('/subscriptions/manage', [SubscriptionController::class, 'manage'])->name('subscriptions.manage');

    // RoboTarget
    Route::get('/target-planner', [RoboTargetController::class, 'planner'])->name('robotarget.planner');
    Route::get('/my-targets', [RoboTargetController::class, 'myTargets'])->name('robotarget.my-targets');
    Route::get('/dashboard/robotarget', [RoboTargetController::class, 'dashboard'])->name('robotarget.dashboard');
    Route::get('/target/{guid}', [RoboTargetController::class, 'show'])->name('robotarget.show');
});
```

### API Routes
```php
// routes/api.php

Route::middleware(['auth:sanctum'])->group(function () {
    // User data
    Route::get('/user/subscription', [UserController::class, 'subscription']);

    // Catalog
    Route::get('/catalog/objects', [CatalogController::class, 'index']);
    Route::get('/catalog/search', [CatalogController::class, 'search']);

    // Pricing
    Route::post('/pricing/estimate', [PricingController::class, 'estimate']);

    // RoboTarget
    Route::post('/robotarget/targets', [RoboTargetController::class, 'store']);
    Route::get('/robotarget/my-targets', [RoboTargetController::class, 'list']);
    Route::get('/robotarget/stats', [RoboTargetController::class, 'stats']);
});
```

---

## 📚 Prochaines étapes

1. **Créer les vues manquantes** :
   - `my-targets.blade.php` (liste des cibles utilisateur)
   - `target-detail.blade.php` (détails d'une cible + résultats)

2. **Intégrer Stripe Elements** :
   - Composant de paiement dans subscription picker
   - Gestion des webhooks

3. **Tests Frontend** :
   - Tester le flow complet
   - Tester WebSocket en conditions réelles
   - Tester responsive design

4. **Optimisations** :
   - Lazy loading images
   - Cache des données catalogue
   - Progressive Web App (PWA) ?

---

## 📖 Documentation de référence

- [Astral Design System](../astral_documentation.md)
- [MODELE-ROBOTARGET-OVERVIEW.md](./MODELE-ROBOTARGET-OVERVIEW.md)
- [IMPLEMENTATION-LARAVEL.md](./IMPLEMENTATION-LARAVEL.md)
- [IMPLEMENTATION-PROXY.md](./IMPLEMENTATION-PROXY.md)
- [Alpine.js Docs](https://alpinejs.dev)
- [Tailwind CSS Docs](https://tailwindcss.com)
- [Socket.IO Client Docs](https://socket.io/docs/v4/client-api/)

---

**Guide Frontend complété ! ✅**

*Dernière mise à jour : 12 Décembre 2025*