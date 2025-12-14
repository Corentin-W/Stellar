// resources/js/app.js
import './bootstrap.js';
import './echo.js'; // Laravel Echo pour WebSocket temps réel
import Alpine from 'alpinejs';

// Import RoboTarget components
import RoboTargetManager from './components/robotarget/RoboTargetManager.js';
import TargetMonitor from './components/robotarget/TargetMonitor.js';
import PricingCalculator from './components/robotarget/PricingCalculator.js';
import LiveMonitor from './components/robotarget/LiveMonitor.js';

// Configuration Alpine.js
window.Alpine = Alpine;

// Make RoboTarget components globally available
window.RoboTargetManager = RoboTargetManager;
window.TargetMonitor = TargetMonitor;
window.PricingCalculator = PricingCalculator;
window.LiveMonitor = LiveMonitor;

// ============================================
// STORES GLOBAUX ALPINE.JS
// ============================================

// Sidebar store removed: mobile/desktop behavior handled with Tailwind only

// Sidebar JS removed; handled by Tailwind peer/checked pattern

// Store pour le télescope
Alpine.store('telescope', {
    status: 'online',
    isConnected: true,
    currentSession: null,
    coordinates: {
        ra: '12h 30m 45s',
        dec: '+41° 16\' 09"'
    },

    connect() {
        this.isConnected = true;
        this.status = 'online';
        window.showNotification('Telescope Connected', 'Connection established successfully', 'success');
    },

    disconnect() {
        this.isConnected = false;
        this.status = 'offline';
        window.showNotification('Telescope Disconnected', 'Connection lost', 'warning');
    },

    goto(target) {
        if (this.isConnected) {
            window.showNotification('GoTo Command', `Moving to ${target}...`, 'info');
            return true;
        }
        window.showNotification('Error', 'Telescope not connected', 'error');
        return false;
    }
});

// Store pour les notifications
Alpine.store('notifications', {
    items: [],
    maxItems: 5,

    add(notification) {
        const id = Date.now() + Math.random();
        const item = {
            id,
            title: notification.title || 'Notification',
            message: notification.message || '',
            type: notification.type || 'info',
            duration: notification.duration || 5000,
            show: true,
            timestamp: new Date(),
            ...notification
        };

        this.items.unshift(item);

        // Limiter le nombre de notifications
        if (this.items.length > this.maxItems) {
            this.items = this.items.slice(0, this.maxItems);
        }

        // Auto-suppression
        if (item.duration > 0) {
            setTimeout(() => {
                this.remove(id);
            }, item.duration);
        }

        return id;
    },

    remove(id) {
        const notification = this.items.find(n => n.id === id);
        if (notification) {
            notification.show = false;
            setTimeout(() => {
                this.items = this.items.filter(n => n.id !== id);
            }, 300);
        }
    },

    clear() {
        this.items.forEach(item => {
            item.show = false;
        });
        setTimeout(() => {
            this.items = [];
        }, 300);
    },

    get unreadCount() {
        return this.items.filter(item => item.show).length;
    }
});

// Store pour les paramètres utilisateur
Alpine.store('settings', {
    theme: localStorage.getItem('theme') || 'dark',
    language: localStorage.getItem('language') || 'fr',
    notifications: {
        desktop: localStorage.getItem('desktop-notifications') === 'true',
        sound: localStorage.getItem('sound-notifications') === 'true',
        telescope: localStorage.getItem('telescope-notifications') === 'true'
    },
    telescope: {
        autoConnect: localStorage.getItem('auto-connect') === 'true',
        autoGuide: localStorage.getItem('auto-guide') === 'true',
        safetyMode: localStorage.getItem('safety-mode') !== 'false'
    },

    setTheme(theme) {
        this.theme = theme;
        localStorage.setItem('theme', theme);
        document.documentElement.setAttribute('data-theme', theme);

        if (theme === 'dark') {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    },

    setLanguage(language) {
        this.language = language;
        localStorage.setItem('language', language);
        // Ici on pourrait déclencher un changement de langue
    },

    updateNotificationSettings(key, value) {
        this.notifications[key] = value;
        localStorage.setItem(`${key}-notifications`, value);
    },

    updateTelescopeSettings(key, value) {
        this.telescope[key] = value;
        localStorage.setItem(key, value);
    }
});


// ============================================
// FONCTIONS UTILITAIRES GLOBALES
// ============================================

window.TelescopeApp = {
    // Formatage du temps
    formatTime(date) {
        return new Intl.DateTimeFormat('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        }).format(date);
    },

    // Formatage des coordonnées
    formatCoordinates(ra, dec) {
        return {
            ra: typeof ra === 'string' ? ra : this.decimalToRA(ra),
            dec: typeof dec === 'string' ? dec : this.decimalToDec(dec)
        };
    },

    // Conversion décimal vers RA
    decimalToRA(decimal) {
        const hours = Math.floor(decimal / 15);
        const minutes = Math.floor((decimal % 15) * 4);
        const seconds = Math.floor(((decimal % 15) * 4 - minutes) * 60);
        return `${hours.toString().padStart(2, '0')}h ${minutes.toString().padStart(2, '0')}m ${seconds.toString().padStart(2, '0')}s`;
    },

    // Conversion décimal vers DEC
    decimalToDec(decimal) {
        const sign = decimal >= 0 ? '+' : '-';
        const abs = Math.abs(decimal);
        const degrees = Math.floor(abs);
        const minutes = Math.floor((abs - degrees) * 60);
        const seconds = Math.floor(((abs - degrees) * 60 - minutes) * 60);
        return `${sign}${degrees.toString().padStart(2, '0')}° ${minutes.toString().padStart(2, '0')}' ${seconds.toString().padStart(2, '0')}"`;
    },

    // Validation des coordonnées
    validateCoordinates(ra, dec) {
        const raPattern = /^([0-1]?[0-9]|2[0-3])h\s([0-5]?[0-9])m\s([0-5]?[0-9])s$/;
        const decPattern = /^[+-]?([0-8]?[0-9]|90)°\s([0-5]?[0-9])'\s([0-5]?[0-9])"$/;
        return raPattern.test(ra) && decPattern.test(dec);
    },

    // Gestion des erreurs
    handleError(error, context = '') {
        console.error(`[TelescopeApp${context ? ' - ' + context : ''}]:`, error);

        let message = 'An unexpected error occurred';
        if (error.message) {
            message = error.message;
        } else if (typeof error === 'string') {
            message = error;
        }

        window.showNotification('Error', message, 'error');
    },

    // Debounce utility
    debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    },

    // Throttle utility
    throttle(func, limit) {
        let inThrottle;
        return function() {
            const args = arguments;
            const context = this;
            if (!inThrottle) {
                func.apply(context, args);
                inThrottle = true;
                setTimeout(() => inThrottle = false, limit);
            }
        }
    }
};

// ============================================
// FONCTION GLOBALE POUR LES NOTIFICATIONS
// ============================================

window.showNotification = function(title, message, type = 'info', duration = 5000) {
    Alpine.store('notifications').add({ title, message, type, duration });

    // Notification desktop si activée
    if (Alpine.store('settings').notifications.desktop && 'Notification' in window) {
        if (Notification.permission === 'granted') {
            new Notification(title, {
                body: message,
                icon: '/favicon.ico',
                tag: 'telescope-app'
            });
        }
    }

    // Son si activé
    if (Alpine.store('settings').notifications.sound) {
        const audio = new Audio('/sounds/notification.mp3');
        audio.volume = 0.3;
        audio.play().catch(() => {}); // Ignore les erreurs audio
    }
};

// ============================================
// GESTIONNAIRES D'ÉVÉNEMENTS GLOBAUX
// ============================================

document.addEventListener('DOMContentLoaded', () => {
    // Initialisation du thème
    const savedTheme = localStorage.getItem('theme') || 'dark';
    Alpine.store('settings').setTheme(savedTheme);

    // Demande de permission pour les notifications desktop
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }

    // Mise à jour météo périodique (protégée si store absent)
    setInterval(() => {
        try {
            if (document.visibilityState === 'visible') {
                const weather = Alpine.store('weather');
                if (weather && typeof weather.update === 'function') {
                    weather.update();
                }
            }
        } catch (e) {
            // ignore si store non défini
        }
    }, 300000); // 5 minutes

    // Sauvegarde automatique des paramètres
    window.addEventListener('beforeunload', () => {
        localStorage.setItem('last-session', JSON.stringify({
            timestamp: new Date().toISOString(),
            telescope: Alpine.store('telescope').status
        }));
    });

    console.log('🌌 TelescopeApp initialized successfully');
});

// Raccourcis clavier globaux
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + B : (désactivé) gestion sidebar supprimée

    // Ctrl/Cmd + K : Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.focus();
        }
    }

    // Escape : (désactivé) gestion sidebar supprimée

    // Ctrl/Cmd + Shift + N : Nouvelle session
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'N') {
        e.preventDefault();
        window.showNotification('New Session', 'Opening session planner...', 'info');
    }

    // Ctrl/Cmd + Shift + C : Capture image
    if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'C') {
        e.preventDefault();
        if (Alpine.store('telescope').isConnected) {
            window.showNotification('Capture', 'Starting image capture...', 'info');
        } else {
            window.showNotification('Error', 'Telescope not connected', 'error');
        }
    }
});

// Gestion de la visibilité de la page
document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
        // Rafraîchir les données critiques (si store météo présent)
        try {
            const weather = Alpine.store('weather');
            if (weather && typeof weather.update === 'function') {
                weather.update();
            }
        } catch (e) {}
    }
});

// Gestion des erreurs JavaScript globales
window.addEventListener('error', (e) => {
    TelescopeApp.handleError(e.error, 'Global Error');
});

window.addEventListener('unhandledrejection', (e) => {
    TelescopeApp.handleError(e.reason, 'Unhandled Promise');
});

// Démarrage d'Alpine.js
Alpine.start();
