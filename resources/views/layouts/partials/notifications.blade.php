<!-- resources/views/layouts/partials/notifications.blade.php -->
<div x-data="notificationSystem()"
     x-show="notifications.length > 0"
     class="notification-container">

    <template x-for="notification in notifications" :key="notification.id">
        <div x-show="notification.show"
             x-transition:enter="notification-enter"
             x-transition:enter-start="notification-enter-start"
             x-transition:enter-end="notification-enter-end"
             x-transition:leave="notification-leave"
             x-transition:leave-start="notification-leave-start"
             x-transition:leave-end="notification-leave-end"
             class="notification-toast"
             :class="getNotificationClass(notification.type)"
             @click="removeNotification(notification.id)">

            <div class="notification-icon">
                <template x-if="notification.type === 'success'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </template>

                <template x-if="notification.type === 'error'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                    </svg>
                </template>

                <template x-if="notification.type === 'warning'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M1 21h22L12 2 1 21zm12-3h-2v-2h2v2zm0-4h-2v-4h2v4z"/>
                    </svg>
                </template>

                <template x-if="notification.type === 'info'">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
                    </svg>
                </template>
            </div>

            <div class="notification-content">
                <div class="notification-title" x-text="notification.title"></div>
                <div class="notification-message" x-text="notification.message"></div>
            </div>

            <button class="notification-close" @click.stop="removeNotification(notification.id)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                </svg>
            </button>
        </div>
    </template>
</div>

<style>
.notification-container {
    position: fixed;
    top: 100px;
    right: var(--space-lg);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
    max-width: 400px;
    width: 100%;
}

.notification-toast {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
    padding: var(--space-lg);
    background: var(--bg-secondary);
    border: 1px solid var(--glass-border);
    border-radius: var(--radius-lg);
    box-shadow: var(--shadow-xl);
    backdrop-filter: var(--blur-md);
    cursor: pointer;
    transition: var(--transition-base);
    position: relative;
}

.notification-toast:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-2xl);
}

.notification-toast.success {
    border-left: 4px solid var(--color-success);
}

.notification-toast.error {
    border-left: 4px solid var(--color-error);
}

.notification-toast.warning {
    border-left: 4px solid var(--color-warning);
}

.notification-toast.info {
    border-left: 4px solid var(--color-secondary);
}

.notification-icon {
    flex-shrink: 0;
    width: 40px;
    height: 40px;
    border-radius: var(--radius-lg);
    display: flex;
    align-items: center;
    justify-content: center;
}

.notification-toast.success .notification-icon {
    background: rgba(16, 185, 129, 0.1);
    color: var(--color-success);
}

.notification-toast.error .notification-icon {
    background: rgba(239, 68, 68, 0.1);
    color: var(--color-error);
}

.notification-toast.warning .notification-icon {
    background: rgba(245, 158, 11, 0.1);
    color: var(--color-warning);
}

.notification-toast.info .notification-icon {
    background: rgba(6, 182, 212, 0.1);
    color: var(--color-secondary);
}

.notification-content {
    flex: 1;
    min-width: 0;
}

.notification-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-primary);
    margin-bottom: 4px;
}

.notification-message {
    font-size: 13px;
    color: var(--text-secondary);
    line-height: 1.4;
}

.notification-close {
    position: absolute;
    top: var(--space-md);
    right: var(--space-md);
    background: none;
    border: none;
    color: var(--text-tertiary);
    cursor: pointer;
    padding: var(--space-xs);
    border-radius: var(--radius-md);
    transition: var(--transition-base);
}

.notification-close:hover {
    background: var(--glass-bg);
    color: var(--text-primary);
}

/* Animations */
.notification-enter {
    transition: all 0.3s ease-out;
}

.notification-enter-start {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
}

.notification-enter-end {
    opacity: 1;
    transform: translateX(0) scale(1);
}

.notification-leave {
    transition: all 0.2s ease-in;
}

.notification-leave-start {
    opacity: 1;
    transform: translateX(0) scale(1);
}

.notification-leave-end {
    opacity: 0;
    transform: translateX(100%) scale(0.9);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .notification-container {
        top: 80px;
        right: var(--space-md);
        left: var(--space-md);
        max-width: none;
    }

    .notification-toast {
        padding: var(--space-md);
    }

    .notification-title {
        font-size: 13px;
    }

    .notification-message {
        font-size: 12px;
    }
}
</style>

<script>
function notificationSystem() {
    return {
        notifications: [],

        init() {
            // Listen for custom notification events
            window.addEventListener('show-notification', (event) => {
                this.addNotification(event.detail);
            });

            // Listen to Alpine store changes
            this.$watch('$store.notifications.items', (newItems) => {
                // Sync with store notifications if needed
                this.syncWithStore(newItems);
            });
        },

        addNotification(notification) {
            const id = Date.now() + Math.random();
            const newNotification = {
                id,
                title: notification.title || 'Notification',
                message: notification.message || '',
                type: notification.type || 'info',
                duration: notification.duration || 5000,
                show: true,
                ...notification
            };

            this.notifications.push(newNotification);

            // Auto remove after duration
            if (newNotification.duration > 0) {
                setTimeout(() => {
                    this.removeNotification(id);
                }, newNotification.duration);
            }
        },

        removeNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                notification.show = false;
                // Remove from array after animation
                setTimeout(() => {
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 300);
            }
        },

        getNotificationClass(type) {
            const classes = {
                success: 'success',
                error: 'error',
                warning: 'warning',
                info: 'info'
            };
            return classes[type] || 'info';
        },

        syncWithStore(storeItems) {
            // Optional: Sync with Alpine store notifications
            // This can be used to show store notifications as toasts
        },

        clearAll() {
            this.notifications.forEach(notification => {
                notification.show = false;
            });
            setTimeout(() => {
                this.notifications = [];
            }, 300);
        }
    }
}

// Global notification function
window.showNotification = function(title, message, type = 'info', duration = 5000) {
    window.dispatchEvent(new CustomEvent('show-notification', {
        detail: { title, message, type, duration }
    }));
};
</script>
