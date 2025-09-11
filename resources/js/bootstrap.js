// resources/js/bootstrap.js

/**
 * Bootstrap file for TelescopeApp
 * Configuration des dépendances et utilitaires globaux
 */

// Configuration CSRF Token pour les requêtes HTTP
window.csrf_token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

// Configuration Axios si disponible
if (typeof window.axios !== 'undefined') {
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
    if (window.csrf_token) {
        window.axios.defaults.headers.common['X-CSRF-TOKEN'] = window.csrf_token;
    }
}

// Configuration Echo pour WebSockets (si Laravel Echo est utilisé)
// import Echo from 'laravel-echo';
// import Pusher from 'pusher-js';
// window.Pusher = Pusher;
// window.Echo = new Echo({
//     broadcaster: 'pusher',
//     key: process.env.MIX_PUSHER_APP_KEY,
//     cluster: process.env.MIX_PUSHER_APP_CLUSTER,
//     forceTLS: true
// });

/**
 * Configuration globale TelescopeApp
 */
window.TelescopeConfig = {
    // Configuration API
    api: {
        baseUrl: '/api',
        timeout: 30000,
        retryAttempts: 3
    },

    // Configuration télescope
    telescope: {
        connectionTimeout: 10000,
        commandTimeout: 30000,
        maxRetries: 3,
        safetyChecks: true
    },

    // Configuration météo
    weather: {
        updateInterval: 300000, // 5 minutes
        apiTimeout: 10000
    },

    // Configuration notifications
    notifications: {
        maxItems: 5,
        defaultDuration: 5000,
        position: 'top-right'
    },

    // Configuration interface
    ui: {
        animationDuration: 300,
        debounceDelay: 300,
        throttleDelay: 100
    }
};

/**
 * Utilitaires globaux
 */
window.Utils = {
    // Formatage de nombres
    formatNumber(num, decimals = 2) {
        return new Intl.NumberFormat('en-US', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }).format(num);
    },

    // Formatage de dates
    formatDate(date, options = {}) {
        const defaultOptions = {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit'
        };
        return new Intl.DateTimeFormat('en-US', { ...defaultOptions, ...options }).format(new Date(date));
    },

    // Formatage de durée
    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = seconds % 60;

        if (hours > 0) {
            return `${hours}h ${minutes}m ${secs}s`;
        } else if (minutes > 0) {
            return `${minutes}m ${secs}s`;
        } else {
            return `${secs}s`;
        }
    },

    // Génération d'ID unique
    generateId() {
        return Date.now().toString(36) + Math.random().toString(36).substr(2);
    },

    // Validation email
    isValidEmail(email) {
        const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return re.test(email);
    },

    // Copie dans le presse-papier
    async copyToClipboard(text) {
        try {
            await navigator.clipboard.writeText(text);
            return true;
        } catch (err) {
            // Fallback pour les navigateurs plus anciens
            const textArea = document.createElement('textarea');
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            try {
                document.execCommand('copy');
                document.body.removeChild(textArea);
                return true;
            } catch (err) {
                document.body.removeChild(textArea);
                return false;
            }
        }
    },

    // Download de fichier
    downloadFile(data, filename, type = 'text/plain') {
        const blob = new Blob([data], { type });
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = filename;
        document.body.appendChild(a);
        a.click();
        window.URL.revokeObjectURL(url);
        document.body.removeChild(a);
    },

    // Détection du type d'appareil
    getDeviceType() {
        const width = window.innerWidth;
        if (width < 640) return 'mobile';
        if (width < 1024) return 'tablet';
        return 'desktop';
    },

    // Détection des capacités du navigateur
    getBrowserCapabilities() {
        return {
            webgl: !!window.WebGLRenderingContext,
            webgl2: !!window.WebGL2RenderingContext,
            canvas: !!document.createElement('canvas').getContext,
            localStorage: typeof Storage !== 'undefined',
            indexedDB: !!window.indexedDB,
            serviceWorker: 'serviceWorker' in navigator,
            notifications: 'Notification' in window,
            geolocation: 'geolocation' in navigator,
            camera: !!(navigator.mediaDevices && navigator.mediaDevices.getUserMedia),
            fullscreen: !!(document.fullscreenEnabled || document.webkitFullscreenEnabled || document.mozFullScreenEnabled)
        };
    }
};

/**
 * Gestionnaire d'API simplifié
 */
window.API = {
    async request(method, endpoint, data = null, options = {}) {
        const config = {
            method: method.toUpperCase(),
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                ...(window.csrf_token && { 'X-CSRF-TOKEN': window.csrf_token })
            },
            ...options
        };

        if (data && (method.toUpperCase() === 'POST' || method.toUpperCase() === 'PUT' || method.toUpperCase() === 'PATCH')) {
            config.body = JSON.stringify(data);
        }

        try {
            const response = await fetch(`${window.TelescopeConfig.api.baseUrl}${endpoint}`, config);

            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }

            const result = await response.json();
            return result;
        } catch (error) {
            console.error('API Request failed:', error);
            throw error;
        }
    },

    get(endpoint, options = {}) {
        return this.request('GET', endpoint, null, options);
    },

    post(endpoint, data, options = {}) {
        return this.request('POST', endpoint, data, options);
    },

    put(endpoint, data, options = {}) {
        return this.request('PUT', endpoint, data, options);
    },

    patch(endpoint, data, options = {}) {
        return this.request('PATCH', endpoint, data, options);
    },

    delete(endpoint, options = {}) {
        return this.request('DELETE', endpoint, null, options);
    }
};

/**
 * Gestionnaire de cache simple
 */
window.Cache = {
    storage: new Map(),

    set(key, value, ttl = 3600000) { // TTL par défaut: 1 heure
        const item = {
            value,
            expiry: Date.now() + ttl
        };
        this.storage.set(key, item);
    },

    get(key) {
        const item = this.storage.get(key);
        if (!item) return null;

        if (Date.now() > item.expiry) {
            this.storage.delete(key);
            return null;
        }

        return item.value;
    },

    has(key) {
        return this.get(key) !== null;
    },

    delete(key) {
        return this.storage.delete(key);
    },

    clear() {
        this.storage.clear();
    },

    size() {
        return this.storage.size;
    }
};

/**
 * Gestionnaire de performance
 */
window.Performance = {
    marks: new Map(),

    mark(name) {
        this.marks.set(name, performance.now());
    },

    measure(name, startMark) {
        const start = this.marks.get(startMark);
        if (!start) {
            console.warn(`Performance mark '${startMark}' not found`);
            return 0;
        }

        const duration = performance.now() - start;
        console.log(`⏱️ ${name}: ${duration.toFixed(2)}ms`);
        return duration;
    },

    getMemoryUsage() {
        if (performance.memory) {
            return {
                used: Math.round(performance.memory.usedJSHeapSize / 1048576),
                total: Math.round(performance.memory.totalJSHeapSize / 1048576),
                limit: Math.round(performance.memory.jsHeapSizeLimit / 1048576)
            };
        }
        return null;
    }
};

/**
 * Gestionnaire d'événements personnalisés
 */
window.EventBus = {
    events: new Map(),

    on(event, callback) {
        if (!this.events.has(event)) {
            this.events.set(event, new Set());
        }
        this.events.get(event).add(callback);
    },

    off(event, callback) {
        if (this.events.has(event)) {
            this.events.get(event).delete(callback);
        }
    },

    emit(event, data = null) {
        if (this.events.has(event)) {
            this.events.get(event).forEach(callback => {
                try {
                    callback(data);
                } catch (error) {
                    console.error(`Error in event handler for '${event}':`, error);
                }
            });
        }
    },

    once(event, callback) {
        const onceCallback = (data) => {
            callback(data);
            this.off(event, onceCallback);
        };
        this.on(event, onceCallback);
    }
};

/**
 * Détection et gestion des erreurs
 */
window.ErrorHandler = {
    init() {
        // Erreurs JavaScript
        window.addEventListener('error', (event) => {
            this.logError('JavaScript Error', {
                message: event.message,
                filename: event.filename,
                line: event.lineno,
                column: event.colno,
                stack: event.error?.stack
            });
        });

        // Promesses rejetées
        window.addEventListener('unhandledrejection', (event) => {
            this.logError('Unhandled Promise Rejection', {
                reason: event.reason,
                promise: event.promise
            });
        });

        // Erreurs de ressources
        window.addEventListener('error', (event) => {
            if (event.target !== window) {
                this.logError('Resource Error', {
                    type: event.target.tagName,
                    source: event.target.src || event.target.href,
                    message: 'Failed to load resource'
                });
            }
        }, true);
    },

    logError(type, details) {
        const error = {
            type,
            details,
            timestamp: new Date().toISOString(),
            userAgent: navigator.userAgent,
            url: window.location.href
        };

        console.error(`[${type}]`, details);

        // Envoyer à un service de logging si configuré
        this.sendToLoggingService(error);
    },

    sendToLoggingService(error) {
        // Implémentation du service de logging
        // Par exemple, envoyer à Sentry, LogRocket, etc.
        if (window.TelescopeConfig.logging?.enabled) {
            // fetch('/api/logs', {
            //     method: 'POST',
            //     headers: { 'Content-Type': 'application/json' },
            //     body: JSON.stringify(error)
            // }).catch(() => {}); // Ignore les erreurs de logging
        }
    }
};

/**
 * Gestionnaire de connexion réseau
 */
window.NetworkMonitor = {
    isOnline: navigator.onLine,
    callbacks: new Set(),

    init() {
        window.addEventListener('online', () => {
            this.isOnline = true;
            this.notifyCallbacks('online');
            window.showNotification('Connection Restored', 'Network connection is back online', 'success', 3000);
        });

        window.addEventListener('offline', () => {
            this.isOnline = false;
            this.notifyCallbacks('offline');
            window.showNotification('Connection Lost', 'Working in offline mode', 'warning', 5000);
        });
    },

    onStatusChange(callback) {
        this.callbacks.add(callback);
    },

    offStatusChange(callback) {
        this.callbacks.delete(callback);
    },

    notifyCallbacks(status) {
        this.callbacks.forEach(callback => {
            try {
                callback(status);
            } catch (error) {
                console.error('Error in network status callback:', error);
            }
        });
    }
};

/**
 * Gestionnaire de raccourcis clavier
 */
window.KeyboardShortcuts = {
    shortcuts: new Map(),
    isRecording: false,

    register(combination, callback, description = '') {
        const key = this.normalizeKey(combination);
        this.shortcuts.set(key, { callback, description });
    },

    unregister(combination) {
        const key = this.normalizeKey(combination);
        this.shortcuts.delete(key);
    },

    normalizeKey(combination) {
        return combination
            .toLowerCase()
            .split('+')
            .map(key => key.trim())
            .sort()
            .join('+');
    },

    handleKeyDown(event) {
        if (this.isRecording) return;

        const combination = [];
        if (event.ctrlKey) combination.push('ctrl');
        if (event.metaKey) combination.push('meta');
        if (event.altKey) combination.push('alt');
        if (event.shiftKey) combination.push('shift');

        const key = event.key.toLowerCase();
        if (!['control', 'meta', 'alt', 'shift'].includes(key)) {
            combination.push(key);
        }

        const normalizedKey = combination.sort().join('+');
        const shortcut = this.shortcuts.get(normalizedKey);

        if (shortcut) {
            event.preventDefault();
            shortcut.callback(event);
        }
    },

    getRegisteredShortcuts() {
        return Array.from(this.shortcuts.entries()).map(([key, data]) => ({
            combination: key,
            description: data.description
        }));
    }
};

/**
 * Initialisation des gestionnaires
 */
document.addEventListener('DOMContentLoaded', () => {
    // Initialiser les gestionnaires
    window.ErrorHandler.init();
    window.NetworkMonitor.init();

    // Gestionnaire de raccourcis clavier
    document.addEventListener('keydown', (event) => {
        window.KeyboardShortcuts.handleKeyDown(event);
    });

    // Détection de la performance
    window.Performance.mark('bootstrap-complete');

    // Logging des capacités du navigateur
    console.log('🔧 Browser Capabilities:', window.Utils.getBrowserCapabilities());
    console.log('📱 Device Type:', window.Utils.getDeviceType());

    if (window.Performance.getMemoryUsage()) {
        console.log('💾 Memory Usage:', window.Performance.getMemoryUsage());
    }
});

/**
 * Service Worker (si disponible)
 */
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const registration = await navigator.serviceWorker.register('/sw.js');
            console.log('🔧 Service Worker registered:', registration);

            // Écouter les mises à jour
            registration.addEventListener('updatefound', () => {
                const newWorker = registration.installing;
                newWorker.addEventListener('statechange', () => {
                    if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                        // Nouvelle version disponible
                        window.showNotification(
                            'Update Available',
                            'A new version of TelescopeApp is available. Refresh to update.',
                            'info',
                            10000
                        );
                    }
                });
            });
        } catch (error) {
            console.log('Service Worker registration failed:', error);
        }
    });
}

/**
 * Configuration des intercepteurs de requêtes
 */
if (typeof window.fetch !== 'undefined') {
    const originalFetch = window.fetch;
    window.fetch = async (...args) => {
        const [resource, config] = args;

        // Ajouter des headers par défaut
        const enhancedConfig = {
            ...config,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                ...(window.csrf_token && { 'X-CSRF-TOKEN': window.csrf_token }),
                ...config?.headers
            }
        };

        try {
            const response = await originalFetch(resource, enhancedConfig);

            // Gestion des erreurs HTTP
            if (!response.ok) {
                const error = new Error(`HTTP ${response.status}: ${response.statusText}`);
                error.response = response;
                throw error;
            }

            return response;
        } catch (error) {
            // Gestion des erreurs réseau
            if (!navigator.onLine) {
                window.showNotification('Network Error', 'No internet connection', 'error');
            }
            throw error;
        }
    };
}

console.log('🚀 TelescopeApp Bootstrap loaded successfully');
