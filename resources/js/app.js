// resources/js/app.js
import './bootstrap';
import Alpine from 'alpinejs';

// Make Alpine available globally
window.Alpine = Alpine;

// ===========================
// ALPINE STORES
// ===========================

// Dark Mode Store with persistence
Alpine.store('darkMode', {
    isDark: localStorage.getItem('darkMode') === 'true' ||
            (!localStorage.getItem('darkMode') && window.matchMedia('(prefers-color-scheme: dark)').matches),

    toggle() {
        this.isDark = !this.isDark;
        localStorage.setItem('darkMode', this.isDark.toString());
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

        // Update meta theme color for mobile browsers
        const metaThemeColor = document.querySelector('meta[name="theme-color"]');
        if (metaThemeColor) {
            metaThemeColor.setAttribute('content', this.isDark ? '#0f172a' : '#ffffff');
        }
    }
});

// Sidebar Store
Alpine.store('sidebar', {
    isOpen: false,

    toggle() {
        this.isOpen = !this.isOpen;

        // Prevent body scroll when sidebar is open on mobile
        if (window.innerWidth < 1024) {
            document.body.style.overflow = this.isOpen ? 'hidden' : '';
        }
    },

    close() {
        this.isOpen = false;
        document.body.style.overflow = '';
    },

    open() {
        this.isOpen = true;
        if (window.innerWidth < 1024) {
            document.body.style.overflow = 'hidden';
        }
    }
});

// Telescope Status Store
Alpine.store('telescope', {
    status: 'offline',
    isConnected: false,
    isTracking: false,
    currentPosition: { ra: 0, dec: 0 },
    currentSession: null,
    lastUpdate: null,
    statusInterval: null,
    weather: {
        temperature: -2,
        condition: 'Clear',
        humidity: 45,
        windSpeed: 5,
        visibility: 25,
        cloudCover: 5
    },

    async connect() {
        this.status = 'connecting';

        try {
            // Simulate connection delay
            await this.delay(2000);

            this.status = 'online';
            this.isConnected = true;
            this.lastUpdate = new Date();

            // Start periodic updates
            this.startStatusUpdates();

            // Show success notification
            this.showNotification('Successfully connected to telescope', 'success');

            return true;
        } catch (error) {
            this.status = 'error';
            this.isConnected = false;
            this.showNotification('Failed to connect to telescope', 'error');
            return false;
        }
    },

    disconnect() {
        this.status = 'offline';
        this.isConnected = false;
        this.isTracking = false;
        this.currentSession = null;
        this.lastUpdate = null;

        // Stop periodic updates
        this.stopStatusUpdates();

        this.showNotification('Disconnected from telescope', 'info');
    },

    startStatusUpdates() {
        if (this.statusInterval) {
            clearInterval(this.statusInterval);
        }

        this.statusInterval = setInterval(() => {
            if (this.isConnected) {
                this.updateWeather();
                this.updatePosition();
                this.lastUpdate = new Date();
            }
        }, 30000); // Update every 30 seconds
    },

    stopStatusUpdates() {
        if (this.statusInterval) {
            clearInterval(this.statusInterval);
            this.statusInterval = null;
        }
    },

    updateWeather() {
        // Simulate weather updates with realistic variations
        const baseTemp = -2;
        this.weather.temperature = baseTemp + (Math.random() * 6 - 3); // ±3°C variation
        this.weather.humidity = Math.max(20, Math.min(90, this.weather.humidity + (Math.random() * 10 - 5)));
        this.weather.windSpeed = Math.max(0, Math.min(20, this.weather.windSpeed + (Math.random() * 4 - 2)));
        this.weather.cloudCover = Math.max(0, Math.min(100, this.weather.cloudCover + (Math.random() * 20 - 10)));

        // Update condition based on cloud cover
        if (this.weather.cloudCover < 10) {
            this.weather.condition = 'Clear';
        } else if (this.weather.cloudCover < 30) {
            this.weather.condition = 'Partly Cloudy';
        } else if (this.weather.cloudCover < 70) {
            this.weather.condition = 'Cloudy';
        } else {
            this.weather.condition = 'Overcast';
        }
    },

    updatePosition() {
        // Simulate telescope position updates (sidereal tracking)
        if (this.isTracking) {
            this.currentPosition.ra += 0.004; // Approximate sidereal rate
            if (this.currentPosition.ra >= 360) {
                this.currentPosition.ra -= 360;
            }
        }
    },

    async slewTo(ra, dec, targetName = 'Target') {
        if (!this.isConnected) {
            this.showNotification('Telescope not connected', 'error');
            return false;
        }

        try {
            this.status = 'slewing';
            this.showNotification(`Slewing to ${targetName}...`, 'info');

            // Calculate slew time based on distance
            const distance = Math.sqrt(
                Math.pow(ra - this.currentPosition.ra, 2) +
                Math.pow(dec - this.currentPosition.dec, 2)
            );
            const slewTime = Math.max(1000, distance * 100); // Minimum 1 second

            await this.delay(slewTime);

            this.currentPosition = { ra, dec };
            this.status = 'online';
            this.showNotification(`Successfully slewed to ${targetName}`, 'success');

            return true;
        } catch (error) {
            this.status = 'online';
            this.showNotification('Slew operation failed', 'error');
            return false;
        }
    },

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
    },

    startSession(sessionData) {
        this.currentSession = {
            id: sessionData.id || Date.now(),
            startTime: new Date(),
            duration: sessionData.duration || 3600,
            target: sessionData.target || 'Unknown',
            ...sessionData
        };

        this.showNotification(`Started imaging session: ${this.currentSession.target}`, 'success');
    },

    endSession() {
        if (this.currentSession) {
            const duration = Math.floor((new Date() - this.currentSession.startTime) / 1000);
            this.showNotification(
                `Session completed: ${this.currentSession.target} (${this.formatDuration(duration)})`,
                'success'
            );
            this.currentSession = null;
        }
    },

    // Helper methods
    delay(ms) {
        return new Promise(resolve => setTimeout(resolve, ms));
    },

    formatDuration(seconds) {
        const hours = Math.floor(seconds / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);

        if (hours > 0) {
            return `${hours}h ${minutes}m`;
        }
        return `${minutes}m`;
    },

    showNotification(message, type) {
        Alpine.store('notifications').add({
            title: 'Telescope',
            message: message,
            type: type
        });
    }
});

// Notifications Store
Alpine.store('notifications', {
    items: [],
    maxItems: 5,

    add(notification) {
        const id = Date.now() + Math.random();
        const newNotification = {
            id,
            title: notification.title || 'Notification',
            message: notification.message || '',
            type: notification.type || 'info',
            duration: notification.duration || 5000,
            timestamp: new Date(),
            read: false,
            show: true,
            ...notification
        };

        // Add to beginning of array
        this.items.unshift(newNotification);

        // Keep only maxItems
        if (this.items.length > this.maxItems) {
            this.items = this.items.slice(0, this.maxItems);
        }

        // Auto remove after duration if specified
        if (newNotification.duration > 0) {
            setTimeout(() => {
                this.remove(id);
            }, newNotification.duration);
        }

        return id;
    },

    remove(id) {
        const notification = this.items.find(n => n.id === id);
        if (notification) {
            notification.show = false;
            // Remove from array after animation
            setTimeout(() => {
                this.items = this.items.filter(n => n.id !== id);
            }, 300);
        }
    },

    markAsRead(id) {
        const notification = this.items.find(n => n.id === id);
        if (notification) {
            notification.read = true;
        }
    },

    markAllAsRead() {
        this.items.forEach(item => item.read = true);
    },

    clear() {
        this.items.forEach(item => item.show = false);
        setTimeout(() => {
            this.items = [];
        }, 300);
    },

    get unreadCount() {
        return this.items.filter(item => !item.read).length;
    }
});

// Search Store
Alpine.store('search', {
    query: '',
    results: [],
    isSearching: false,
    recentSearches: JSON.parse(localStorage.getItem('telescope_recent_searches') || '[]'),

    async performSearch(query) {
        if (!query || query.length < 2) {
            this.results = [];
            return;
        }

        this.isSearching = true;
        this.query = query;

        try {
            // Simulate API call delay
            await new Promise(resolve => setTimeout(resolve, 300));

            // Mock search results
            this.results = this.generateMockResults(query);

            // Add to recent searches
            this.addToRecent(query);

        } catch (error) {
            console.error('Search failed:', error);
            this.results = [];
        } finally {
            this.isSearching = false;
        }
    },

    generateMockResults(query) {
        const mockData = [
            { type: 'telescope', title: 'Celestron NexStar 8SE', subtitle: 'Available now', icon: '🔭' },
            { type: 'target', title: 'Andromeda Galaxy (M31)', subtitle: 'Deep sky object', icon: '🌌' },
            { type: 'image', title: 'Orion Nebula', subtitle: 'Captured 2 hours ago', icon: '📸' },
            { type: 'session', title: 'Ring Nebula Session', subtitle: 'Scheduled for tonight', icon: '⏰' },
            { type: 'user', title: 'AstroPhotographer42', subtitle: 'Community member', icon: '👤' }
        ];

        return mockData.filter(item =>
            item.title.toLowerCase().includes(query.toLowerCase()) ||
            item.subtitle.toLowerCase().includes(query.toLowerCase())
        ).slice(0, 8);
    },

    addToRecent(query) {
        // Remove if already exists
        this.recentSearches = this.recentSearches.filter(s => s !== query);

        // Add to beginning
        this.recentSearches.unshift(query);

        // Keep only last 10
        this.recentSearches = this.recentSearches.slice(0, 10);

        // Save to localStorage
        localStorage.setItem('telescope_recent_searches', JSON.stringify(this.recentSearches));
    },

    clearResults() {
        this.results = [];
        this.query = '';
    }
});

// ===========================
// ALPINE COMPONENTS
// ===========================

// Navigation component
Alpine.data('navigation', () => ({
    currentPath: window.location.pathname,

    isActive(path) {
        if (path === '/dashboard') {
            return this.currentPath === '/dashboard' || this.currentPath === '/';
        }
        return this.currentPath.startsWith(path);
    },

    navigate(url) {
        window.location.href = url;
    }
}));

// Search component
Alpine.data('searchBox', () => ({
    query: '',
    isOpen: false,
    searchTimeout: null,

    init() {
        // Debounced search
        this.$watch('query', (value) => {
            clearTimeout(this.searchTimeout);
            this.searchTimeout = setTimeout(() => {
                Alpine.store('search').performSearch(value);
            }, 300);
        });
    },

    handleKeydown(event) {
        if (event.key === 'Escape') {
            this.close();
        } else if (event.key === 'Enter' && this.query) {
            this.performSearch();
        }
    },

    performSearch() {
        if (this.query.trim()) {
            window.location.href = `/search?q=${encodeURIComponent(this.query.trim())}`;
        }
    },

    close() {
        this.isOpen = false;
        this.query = '';
        Alpine.store('search').clearResults();
    }
}));

// User menu component
Alpine.data('userMenu', () => ({
    isOpen: false,

    toggle() {
        this.isOpen = !this.isOpen;
    },

    close() {
        this.isOpen = false;
    },

    async logout() {
        try {
            const response = await fetch('/logout', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json'
                }
            });

            if (response.ok) {
                window.location.href = '/login';
            }
        } catch (error) {
            console.error('Logout failed:', error);
        }
    }
}));

// Notification system component
Alpine.data('notificationSystem', () => ({
    notifications: [],

    init() {
        // Listen for new notifications from store
        this.$watch('$store.notifications.items', (newItems) => {
            this.notifications = newItems.filter(item => item.show);
        });

        // Listen for custom notification events
        window.addEventListener('show-notification', (event) => {
            Alpine.store('notifications').add(event.detail);
        });
    },

    remove(id) {
        Alpine.store('notifications').remove(id);
    },

    getIconClass(type) {
        const classes = {
            success: 'text-emerald-600',
            error: 'text-red-600',
            warning: 'text-amber-600',
            info: 'text-blue-600'
        };
        return classes[type] || classes.info;
    }
}));

// ===========================
// GLOBAL FUNCTIONS
// ===========================

// Global notification function
window.showNotification = function(title, message, type = 'info', duration = 5000) {
    Alpine.store('notifications').add({ title, message, type, duration });
};

// Telescope control functions
window.telescopeControl = {
    async connect() {
        return await Alpine.store('telescope').connect();
    },

    async disconnect() {
        Alpine.store('telescope').disconnect();
    },

    async slewTo(ra, dec, targetName) {
        return await Alpine.store('telescope').slewTo(ra, dec, targetName);
    },

    toggleTracking() {
        Alpine.store('telescope').toggleTracking();
    }
};

// Utility functions
window.utils = {
    formatTime(date) {
        return new Intl.DateTimeFormat('en-US', {
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    },

    formatDate(date) {
        return new Intl.DateTimeFormat('en-US', {
            weekday: 'short',
            month: 'short',
            day: 'numeric'
        }).format(date);
    },

    formatRelativeTime(date) {
        const now = new Date();
        const diff = now - date;
        const minutes = Math.floor(diff / 60000);
        const hours = Math.floor(diff / 3600000);
        const days = Math.floor(diff / 86400000);

        if (minutes < 1) return 'Just now';
        if (minutes < 60) return `${minutes}m ago`;
        if (hours < 24) return `${hours}h ago`;
        return `${days}d ago`;
    },

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
    }
};

// ===========================
// INITIALIZATION
// ===========================

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode
    Alpine.store('darkMode').init();

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

    // Handle responsive behavior
    const handleResize = () => {
        if (window.innerWidth >= 1024) {
            Alpine.store('sidebar').close();
            document.body.style.overflow = '';
        }
    };

    window.addEventListener('resize', handleResize);

    console.log('🔭 Telescope App initialized successfully');
});

// Start Alpine
Alpine.start();
