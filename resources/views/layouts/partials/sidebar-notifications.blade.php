{{-- Notifications Widget for Sidebar --}}
@auth
<div x-data="notificationsWidget()" x-init="init()" class="px-3 mb-3">
    <button @click="toggleDropdown()"
            type="button"
            class="w-full flex items-center justify-between p-2.5 rounded-md bg-white/5 hover:bg-white/10 transition-all group relative">
        <div class="flex items-center gap-2">
            <div class="relative">
                <svg class="w-5 h-5 text-white/70 group-hover:text-white transition-colors" :class="{'animate-bounce': hasNew}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span x-show="unreadCount > 0"
                      x-text="unreadCount"
                      class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-[16px] h-4 px-1 text-[9px] font-bold text-white bg-red-500 rounded-full border border-gray-900"></span>
            </div>
            <span class="text-sm font-medium text-white/70 group-hover:text-white transition-colors">Notifications</span>
        </div>
        <svg class="w-4 h-4 text-white/50 transition-transform" :class="{'rotate-180': isOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="isOpen"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         @click.away="isOpen = false"
         class="mt-2 bg-white/10 backdrop-blur-md border border-white/20 rounded-md shadow-lg max-h-96 overflow-hidden">

        {{-- Header --}}
        <div class="px-3 py-2 border-b border-white/10 flex items-center justify-between">
            <span class="text-xs font-semibold text-white uppercase tracking-wide">Notifications</span>
            <button @click="markAllAsRead()"
                    x-show="unreadCount > 0"
                    class="text-[10px] text-blue-400 hover:text-blue-300 font-medium uppercase tracking-wide transition-colors">
                Tout marquer lu
            </button>
        </div>

        {{-- Notifications List --}}
        <div class="max-h-80 overflow-y-auto">
            <template x-if="notifications.length === 0">
                <div class="px-4 py-8 text-center">
                    <div class="text-3xl mb-2">🔕</div>
                    <p class="text-sm text-white/50">Aucune notification</p>
                </div>
            </template>

            <template x-for="notification in notifications" :key="notification.id">
                <a :href="notification.action_url || '#'"
                   @click="markAsRead(notification.id)"
                   class="block px-3 py-2.5 hover:bg-white/5 transition-colors border-b border-white/5 last:border-0 group">
                    <div class="flex items-start gap-2">
                        <div class="text-lg flex-shrink-0" x-text="notification.icon"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs font-semibold text-white mb-0.5 group-hover:text-blue-400 transition-colors" x-text="notification.title"></p>
                            <p class="text-[11px] text-white/60 line-clamp-2" x-text="notification.message"></p>
                            <p class="text-[10px] text-white/40 mt-1" x-text="notification.created_at"></p>
                        </div>
                        <div x-show="!notification.read_at" class="w-2 h-2 bg-blue-500 rounded-full flex-shrink-0 mt-1"></div>
                    </div>
                </a>
            </template>
        </div>

        {{-- Footer --}}
        <div x-show="notifications.length > 0" class="px-3 py-2 border-t border-white/10">
            <a href="#" class="text-xs text-blue-400 hover:text-blue-300 font-medium transition-colors flex items-center justify-center gap-1">
                Voir toutes les notifications
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
    </div>
</div>

<script>
function notificationsWidget() {
    return {
        isOpen: false,
        notifications: [],
        unreadCount: 0,
        hasNew: false,

        init() {
            this.fetchNotifications();

            // Refresh every 30 seconds
            setInterval(() => {
                this.fetchNotifications();
            }, 30000);

            // Listen for real-time notifications (if broadcasting is enabled)
            if (typeof window.Echo !== 'undefined') {
                window.Echo.private('user.{{ auth()->id() }}')
                    .listen('.session.started', (e) => {
                        this.hasNew = true;
                        this.fetchNotifications();

                        // Stop animation after 3 seconds
                        setTimeout(() => {
                            this.hasNew = false;
                        }, 3000);
                    });
            }
        },

        toggleDropdown() {
            this.isOpen = !this.isOpen;
            if (this.isOpen) {
                this.fetchNotifications();
            }
        },

        async fetchNotifications() {
            try {
                const response = await fetch('/api/notifications', {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin'
                });

                if (response.ok) {
                    const data = await response.json();
                    this.notifications = data.notifications;
                    this.unreadCount = data.unread_count;
                }
            } catch (error) {
                console.error('Failed to fetch notifications:', error);
            }
        },

        async markAsRead(notificationId) {
            try {
                await fetch(`/api/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                this.fetchNotifications();
            } catch (error) {
                console.error('Failed to mark notification as read:', error);
            }
        },

        async markAllAsRead() {
            try {
                await fetch('/api/notifications/read-all', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    credentials: 'same-origin'
                });

                this.fetchNotifications();
            } catch (error) {
                console.error('Failed to mark all as read:', error);
            }
        }
    }
}
</script>
@endauth
