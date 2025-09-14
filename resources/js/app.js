// resources/js/app.js
import './bootstrap.js';
import Alpine from 'alpinejs';

// Configuration Alpine.js
window.Alpine = Alpine;

// ============================================
// STORES GLOBAUX ALPINE.JS
// ============================================

// Store global pour la sidebar
// Amélioration du store sidebar pour mobile - VERSION CORRIGÉE
document.addEventListener('alpine:init', () => {
    // Assurons-nous de redéfinir complètement le store sidebar
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('sidebar-collapsed') === 'true',
        mobileOpen: false,

        toggle() {
            if (window.innerWidth >= 1024) {
                // Desktop: toggle collapsed state
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebar-collapsed', this.collapsed);
                console.log('Desktop sidebar toggled:', this.collapsed);
            } else {
                // Mobile: toggle mobile menu avec animations fluides
                this.mobileOpen = !this.mobileOpen;
                console.log('Mobile sidebar toggled:', this.mobileOpen);

                // Prévenir le scroll du body quand la sidebar est ouverte
                if (this.mobileOpen) {
                    document.body.style.overflow = 'hidden';
                    document.body.classList.add('sidebar-mobile-open');
                } else {
                    document.body.style.overflow = '';
                    document.body.classList.remove('sidebar-mobile-open');
                }

                // Feedback haptique sur mobile si disponible
                if ('vibrate' in navigator && this.mobileOpen) {
                    navigator.vibrate(50);
                }
            }
        },

        close() {
            if (this.mobileOpen) {
                this.mobileOpen = false;
                document.body.style.overflow = '';
                document.body.classList.remove('sidebar-mobile-open');
                console.log('Mobile sidebar closed');
            }
        },

        open() {
            if (window.innerWidth < 1024 && !this.mobileOpen) {
                this.mobileOpen = true;
                document.body.style.overflow = 'hidden';
                document.body.classList.add('sidebar-mobile-open');

                // Feedback haptique
                if ('vibrate' in navigator) {
                    navigator.vibrate(50);
                }
                console.log('Mobile sidebar opened');
            }
        },

        toggleCollapse() {
            if (window.innerWidth >= 1024) {
                this.collapsed = !this.collapsed;
                localStorage.setItem('sidebar-collapsed', this.collapsed);
                console.log('Desktop sidebar collapse toggled:', this.collapsed);
            }
        }
    });
});

// Gestionnaire d'événements amélioré pour mobile
document.addEventListener('DOMContentLoaded', () => {
    // Attendre qu'Alpine soit initialisé
    setTimeout(() => {
        initializeMobileSidebar();
    }, 100);
});

function initializeMobileSidebar() {
    console.log('🔧 Initializing mobile sidebar enhancements...');

    // Gestion du swipe pour ouvrir/fermer la sidebar sur mobile
    let touchStartX = 0;
    let touchStartY = 0;
    let touchEndX = 0;
    let touchEndY = 0;
    let isSwipeDetected = false;

    // Détection du swipe depuis le bord gauche pour ouvrir
    document.addEventListener('touchstart', (e) => {
        touchStartX = e.changedTouches[0].screenX;
        touchStartY = e.changedTouches[0].screenY;
        isSwipeDetected = false;
    }, { passive: true });

    document.addEventListener('touchmove', (e) => {
        // Empêcher le scroll horizontal pendant le swipe
        if (Math.abs(e.changedTouches[0].screenX - touchStartX) > 10) {
            isSwipeDetected = true;
        }
    }, { passive: true });

    document.addEventListener('touchend', (e) => {
        if (isSwipeDetected) {
            touchEndX = e.changedTouches[0].screenX;
            touchEndY = e.changedTouches[0].screenY;
            handleSwipe();
        }
    }, { passive: true });

    function handleSwipe() {
        const swipeThreshold = 100;
        const edgeThreshold = 50;
        const verticalThreshold = 150;

        const horizontalDistance = touchEndX - touchStartX;
        const verticalDistance = Math.abs(touchEndY - touchStartY);

        // Ignorer si le swipe est trop vertical
        if (verticalDistance > verticalThreshold) return;

        try {
            const sidebarStore = Alpine.store('sidebar');

            // Swipe depuis le bord gauche vers la droite (ouvrir)
            if (touchStartX < edgeThreshold &&
                horizontalDistance > swipeThreshold &&
                window.innerWidth < 1024 &&
                !sidebarStore.mobileOpen) {
                sidebarStore.open();
                console.log('✅ Swipe to open detected');
            }

            // Swipe vers la gauche (fermer)
            if (horizontalDistance < -swipeThreshold &&
                window.innerWidth < 1024 &&
                sidebarStore.mobileOpen) {
                sidebarStore.close();
                console.log('✅ Swipe to close detected');
            }
        } catch (error) {
            console.error('❌ Error handling swipe:', error);
        }
    }

    // Fermer la sidebar quand on redimensionne vers desktop
    window.addEventListener('resize', () => {
        try {
            if (window.innerWidth >= 1024) {
                const sidebarStore = Alpine.store('sidebar');
                if (sidebarStore && sidebarStore.mobileOpen) {
                    sidebarStore.close();
                    console.log('🖥️ Closed mobile sidebar on resize to desktop');
                }
            }
        } catch (error) {
            console.error('❌ Error on resize:', error);
        }
    });

    // Gestion de l'orientation mobile
    window.addEventListener('orientationchange', () => {
        setTimeout(() => {
            try {
                const sidebarStore = Alpine.store('sidebar');
                if (sidebarStore && sidebarStore.mobileOpen) {
                    sidebarStore.close();
                    console.log('📱 Closed sidebar on orientation change');
                }
            } catch (error) {
                console.error('❌ Error on orientation change:', error);
            }
        }, 100);
    });

    // Amélioration de l'accessibilité clavier
    document.addEventListener('keydown', (e) => {
        try {
            const sidebarStore = Alpine.store('sidebar');

            // Escape ferme la sidebar mobile
            if (e.key === 'Escape' && sidebarStore && sidebarStore.mobileOpen) {
                e.preventDefault();
                sidebarStore.close();
                console.log('⌨️ Sidebar closed with Escape key');
            }

            // Entrée ou espace sur le bouton menu mobile
            if ((e.key === 'Enter' || e.key === ' ') &&
                e.target.closest('[data-mobile-menu-button]')) {
                e.preventDefault();
                sidebarStore.toggle();
                console.log('⌨️ Sidebar toggled with keyboard');
            }
        } catch (error) {
            console.error('❌ Error handling keyboard:', error);
        }
    });

    // Focus management pour l'accessibilité
    const sidebarElement = document.querySelector('.sidebar');
    if (sidebarElement) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' &&
                    mutation.attributeName === 'class') {
                    try {
                        if (sidebarElement.classList.contains('mobile-open')) {
                            // Focus le premier lien navigable
                            const firstLink = sidebarElement.querySelector('.sidebar-item');
                            if (firstLink) {
                                setTimeout(() => {
                                    firstLink.focus();
                                    console.log('🎯 Focused first sidebar item');
                                }, 200);
                            }
                        }
                    } catch (error) {
                        console.error('❌ Error managing focus:', error);
                    }
                }
            });
        });

        observer.observe(sidebarElement, {
            attributes: true,
            attributeFilter: ['class']
        });
    }

    console.log('📱 Mobile sidebar enhancements successfully loaded');
}

// Fonction utilitaire pour détecter les appareils tactiles
window.isTouchDevice = () => {
    return 'ontouchstart' in window || navigator.maxTouchPoints > 0;
};

// CSS dynamique pour les animations mobile (version sécurisée)
function addMobileStyles() {
    // Vérifier si les styles n'ont pas déjà été ajoutés
    if (document.getElementById('mobile-sidebar-styles')) {
        return;
    }

    const mobileStyles = document.createElement('style');
    mobileStyles.id = 'mobile-sidebar-styles';
    mobileStyles.textContent = `
        /* Classe ajoutée au body quand la sidebar mobile est ouverte */
        body.sidebar-mobile-open {
            touch-action: none;
            overflow: hidden !important;
            position: fixed;
            width: 100%;
        }

        /* Animation d'ouverture fluide */
        @media (max-width: 1024px) {
            .sidebar.mobile-open {
                animation: slideInMobile 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            }

            @keyframes slideInMobile {
                0% {
                    transform: translateX(-100%);
                    opacity: 0.8;
                }
                100% {
                    transform: translateX(0);
                    opacity: 1;
                }
            }

            /* Animation des éléments internes */
            .sidebar.mobile-open .sidebar-item {
                animation: fadeInUp 0.6s ease-out forwards;
                animation-delay: calc(var(--item-index, 0) * 0.05s);
            }
        }

        /* Indicateur de swipe */
        .swipe-indicator {
            position: fixed;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60px;
            background: linear-gradient(180deg, transparent, rgba(79, 70, 229, 0.6), transparent);
            border-radius: 0 4px 4px 0;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 30;
            pointer-events: none;
        }

        .swipe-indicator.show {
            opacity: 1;
        }

        @media (min-width: 1024px) {
            .swipe-indicator {
                display: none;
            }
        }

        /* Debug helper */
        .debug-sidebar {
            position: fixed;
            top: 10px;
            right: 10px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 5px;
            font-size: 10px;
            z-index: 9999;
            border-radius: 3px;
        }
    `;

    document.head.appendChild(mobileStyles);
    console.log('🎨 Mobile styles added successfully');
}

// Initialiser les styles mobile
document.addEventListener('DOMContentLoaded', () => {
    addMobileStyles();

    // Ajouter l'indicateur de swipe sur mobile
    if (window.innerWidth < 1024 && !document.querySelector('.swipe-indicator')) {
        const swipeIndicator = document.createElement('div');
        swipeIndicator.className = 'swipe-indicator';
        document.body.appendChild(swipeIndicator);

        // Montrer l'indicateur brièvement au chargement
        setTimeout(() => {
            swipeIndicator.classList.add('show');
            setTimeout(() => {
                swipeIndicator.classList.remove('show');
            }, 2000);
        }, 1000);
    }
});

console.log('📱 Mobile sidebar script loaded');

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
            sidebar: Alpine.store('sidebar'),
            telescope: Alpine.store('telescope').status
        }));
    });

    console.log('🌌 TelescopeApp initialized successfully');
});

// Raccourcis clavier globaux
document.addEventListener('keydown', (e) => {
    // Ctrl/Cmd + B : Toggle sidebar
    if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
        e.preventDefault();
        Alpine.store('sidebar').toggle();
    }

    // Ctrl/Cmd + K : Focus search
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        const searchInput = document.querySelector('.search-input');
        if (searchInput) {
            searchInput.focus();
        }
    }

    // Escape : Fermer overlays
    if (e.key === 'Escape') {
        Alpine.store('sidebar').close();
    }

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
