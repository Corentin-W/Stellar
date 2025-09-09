// resources/js/telescope.js

// ===========================
// ALPINE.JS STORES
// ===========================

// Dark Mode Store
document.addEventListener('alpine:init', () => {
    Alpine.store('darkMode', {
        isDark: localStorage.getItem('darkMode') === 'true' ||
                (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),

        toggle() {
            this.isDark = !this.isDark;
            localStorage.setItem('darkMode', this.isDark);
            this.updateTheme();
        },

        init() {
            this.updateTheme();
            // Listen for system theme changes
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', (e) => {
                if (!localStorage.getItem('darkMode')) {
                    this.isDark = e.matches;
                    this.updateTheme();
                }
            });
        },

        updateTheme() {
            if (this.isDark) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
    });

    // Sidebar Store
    Alpine.store('sidebar', {
        isOpen: false,

        toggle() {
            this.isOpen = !this.isOpen;
        },

        close() {
            this.isOpen = false;
        },

        open() {
            this.isOpen = true;
        }
    });

    // Telescope Status Store
    Alpine.store('telescope', {
        status: 'offline',
        isConnected: false,
        currentSession: null,
        weather: {
            temperature: -2,
            condition: 'Clear',
            humidity: 45,
            windSpeed: 5
        },

        connect() {
            this.status = 'connecting';
            // Simulate connection delay
            setTimeout(() => {
                this.status = 'online';
                this.isConnected = true;
                this.checkTelescopeStatus();
            }, 2000);
        },

        disconnect() {
            this.status = 'offline';
            this.isConnected = false;
            this.currentSession = null;
        },

        checkTelescopeStatus() {
            if (this.isConnected) {
                // Simulate periodic status check
                setInterval(() => {
                    this.updateWeather();
                }, 300000); // Update every 5 minutes
            }
        },

        updateWeather() {
            // Simulate weather updates
            this.weather.temperature = Math.floor(Math.random() * 20) - 10;
            this.weather.humidity = Math.floor(Math.random() * 50) + 30;
            this.weather.windSpeed = Math.floor(Math.random() * 15) + 1;
        },

        startSession(sessionData) {
            this.currentSession = {
                id: sessionData.id,
                startTime: new Date(),
                duration: sessionData.duration,
                target: sessionData.target
            };
        },

        endSession() {
            this.currentSession = null;
        }
    });

    // Notifications Store
    Alpine.store('notifications', {
        items: [
            {
                id: 1,
                title: 'Session completed',
                message: 'Your imaging session of M31 has finished',
                time: new Date(Date.now() - 2 * 60 * 1000),
                read: false,
                type: 'success'
            },
            {
                id: 2,
                title: 'Session reminder',
                message: 'Your next session starts in 30 minutes',
                time: new Date(Date.now() - 15 * 60 * 1000),
                read: false,
                type: 'info'
            },
            {
                id: 3,
                title: 'Weather alert',
                message: 'Cloud coverage increasing, consider shorter exposures',
                time: new Date(Date.now() - 45 * 60 * 1000),
                read: true,
                type: 'warning'
            }
        ],

        get unreadCount() {
            return this.items.filter(item => !item.read).length;
        },

        markAsRead(id) {
            const item = this.items.find(item => item.id === id);
            if (item) {
                item.read = true;
            }
        },

        markAllAsRead() {
            this.items.forEach(item => item.read = true);
        },

        addNotification(notification) {
            this.items.unshift({
                id: Date.now(),
                time: new Date(),
                read: false,
                ...notification
            });
        },

        removeNotification(id) {
            this.items = this.items.filter(item => item.id !== id);
        }
    });
});

// ===========================
// UTILITIES
// ===========================

// Format time utility
function formatTime(seconds) {
    const hours = Math.floor(seconds / 3600);
    const minutes = Math.floor((seconds % 3600) / 60);
    const secs = seconds % 60;

    if (hours > 0) {
        return `${hours}:${minutes.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    }
    return `${minutes}:${secs.toString().padStart(2, '0')}`;
}

// Format relative time
function formatRelativeTime(date) {
    const now = new Date();
    const diff = now - date;
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);

    if (minutes < 1) return 'Just now';
    if (minutes < 60) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
    if (hours < 24) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
    return `${days} day${days > 1 ? 's' : ''} ago`;
}

// Debounce function
function debounce(func, wait, immediate) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            timeout = null;
            if (!immediate) func(...args);
        };
        const callNow = immediate && !timeout;
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
        if (callNow) func(...args);
    };
}

// ===========================
// TELESCOPE CONTROLS
// ===========================

class TelescopeController {
    constructor() {
        this.isConnected = false;
        this.currentPosition = { ra: 0, dec: 0 };
        this.isTracking = false;
        this.isSlewing = false;
    }

    async connect() {
        try {
            // Simulate connection to telescope
            await this.delay(2000);
            this.isConnected = true;
            Alpine.store('telescope').connect();
            this.showNotification('Connected to telescope', 'success');
            return true;
        } catch (error) {
            this.showNotification('Failed to connect to telescope', 'error');
            return false;
        }
    }

    async disconnect() {
        this.isConnected = false;
        this.isTracking = false;
        this.isSlewing = false;
        Alpine.store('telescope').disconnect();
        this.showNotification('Disconnected from telescope', 'info');
    }

    async slewTo(ra, dec) {
        if (!this.isConnected) {
            this.showNotification('Telescope not connected', 'error');
            return false;
        }

        try {
            this.isSlewing = true;
            this.showNotification('Slewing to target...', 'info');

            // Simulate slewing time
            await this.delay(3000);

            this.currentPosition = { ra, dec };
            this.isSlewing = false;
            this.showNotification('Slew completed', 'success');
            return true;
        } catch (error) {
            this.isSlewing = false;
            this.showNotification('Slew failed', 'error');
            return false;
        }
    }

    toggleTracking() {
        if (!this.isConnected) {
            this.showNotification('Telescope not connected', 'error');
            return;
        }

        this.isTracking = !this.isTracking;
        this.showNotification(
            this.isTracking ? 'Tracking enabled' : 'Tracking disabled',
            'info'
        );
    }

    async takeImage(exposure, filter = 'Luminance') {
        if (!this.isConnected) {
            this.showNotification('Telescope not connected', 'error');
            return false;
        }

        try {
            this.showNotification(`Starting ${exposure}s exposure with ${filter} filter`, 'info');

            // Simulate exposure time
            await this.delay(exposure * 1000);

            const imageId = Date.now();
            this.showNotification('Image captured successfully', 'success');

            // Add to gallery (simulate)
            this.addToGallery(imageId, filter, exposure);

            return imageId;
        } catch (error) {
            this.showNotification('Image capture failed', 'error');
            return false;
        }
    }

    // Helper methods
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    showNotification(message, type) {
        Alpine.store('notifications').addNotification({
            title: 'Telescope',
            message: message,
            type: type
        });
    }

    addToGallery(imageId, filter, exposure) {
        // This would typically send to your backend
        console.log(`Image ${imageId} added to gallery: ${filter} filter, ${exposure}s exposure`);
    }
}

// Global telescope controller instance
window.telescopeController = new TelescopeController();

// ===========================
// SESSION MANAGEMENT
// ===========================

class SessionManager {
    constructor() {
        this.activeSessions = [];
        this.upcomingSessions = [];
    }

    async bookSession(sessionData) {
        try {
            // Simulate API call
            await this.delay(1000);

            const session = {
                id: Date.now(),
                ...sessionData,
                status: 'scheduled',
                createdAt: new Date()
            };

            this.upcomingSessions.push(session);

            Alpine.store('notifications').addNotification({
                title: 'Session booked',
                message: `Session for ${sessionData.target} scheduled for ${this.formatDate(sessionData.startTime)}`,
                type: 'success'
            });

            return session;
        } catch (error) {
            Alpine.store('notifications').addNotification({
                title: 'Booking failed',
                message: 'Failed to book session. Please try again.',
                type: 'error'
            });
            throw error;
        }
    }

    async startSession(sessionId) {
        const session = this.upcomingSessions.find(s => s.id === sessionId);
        if (!session) {
            throw new Error('Session not found');
        }

        session.status = 'active';
        session.startedAt = new Date();

        this.activeSessions.push(session);
        this.upcomingSessions = this.upcomingSessions.filter(s => s.id !== sessionId);

        Alpine.store('telescope').startSession(session);

        Alpine.store('notifications').addNotification({
            title: 'Session started',
            message: `Imaging session for ${session.target} has begun`,
            type: 'success'
        });
    }

    async endSession(sessionId) {
        const session = this.activeSessions.find(s => s.id === sessionId);
        if (!session) {
            throw new Error('Session not found');
        }

        session.status = 'completed';
        session.endedAt = new Date();

        this.activeSessions = this.activeSessions.filter(s => s.id !== sessionId);

        Alpine.store('telescope').endSession();

        Alpine.store('notifications').addNotification({
            title: 'Session completed',
            message: `Imaging session for ${session.target} has finished`,
            type: 'success'
        });
    }

    getUpcomingSessions() {
        return this.upcomingSessions.sort((a, b) => a.startTime - b.startTime);
    }

    getActiveSessions() {
        return this.activeSessions;
    }

    // Helper methods
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    }

    formatDate(date) {
        return new Intl.DateTimeFormat('en-US', {
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }
}

// Global session manager instance
window.sessionManager = new SessionManager();

// ===========================
// INITIALIZATION
// ===========================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode
    Alpine.store('darkMode').init();

    // Initialize telescope status check
    Alpine.store('telescope').checkTelescopeStatus();

    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }

        // Ctrl/Cmd + D for dark mode toggle
        if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
            e.preventDefault();
            Alpine.store('darkMode').toggle();
        }

        // Escape to close modals/dropdowns
        if (e.key === 'Escape') {
            Alpine.store('sidebar').close();
        }
    });

    // Handle responsive sidebar
    const mediaQuery = window.matchMedia('(max-width: 1024px)');
    function handleResponsiveChange(e) {
        if (!e.matches) {
            Alpine.store('sidebar').close();
        }
    }

    mediaQuery.addListener(handleResponsiveChange);
    handleResponsiveChange(mediaQuery);

    // Auto-save form data
    const autoSaveForms = document.querySelectorAll('[data-auto-save]');
    autoSaveForms.forEach(form => {
        const formInputs = form.querySelectorAll('input, textarea, select');
        const debouncedSave = debounce(() => {
            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());
            localStorage.setItem(`autosave_${form.dataset.autoSave}`, JSON.stringify(data));
        }, 1000);

        formInputs.forEach(input => {
            input.addEventListener('input', debouncedSave);
        });

        // Restore saved data
        const savedData = localStorage.getItem(`autosave_${form.dataset.autoSave}`);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.entries(data).forEach(([name, value]) => {
                    const input = form.querySelector(`[name="${name}"]`);
                    if (input) {
                        input.value = value;
                    }
                });
            } catch (e) {
                console.error('Failed to restore form data:', e);
            }
        }
    });

    console.log('Telescope App initialized successfully');
});

// ===========================
// EXPORT FOR GLOBAL ACCESS
// ===========================

window.TelescopeApp = {
    formatTime,
    formatRelativeTime,
    debounce,
    TelescopeController,
    SessionManager
};
