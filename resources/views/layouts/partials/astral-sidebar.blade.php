{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}
<aside class="sidebar fixed left-0 top-0 h-full z-40"
       :class="{
           'collapsed': $store.sidebar.collapsed,
           'mobile-open': $store.sidebar.mobileOpen
       }"
       x-data="sidebarNavigation()">

    <!-- Logo Section -->
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3" :class="{ 'justify-center': $store.sidebar.collapsed }">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div x-show="!$store.sidebar.collapsed" class="min-w-0">
                <h1 class="font-astral font-bold text-white text-lg truncate">TelescopeApp</h1>
                <p class="text-white/60 text-xs">Astral Interface</p>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="p-4" x-show="!$store.sidebar.collapsed">
        <div class="search-container">
            <svg class="search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   placeholder="{{ __('app.sidebar.search_placeholder') }}"
                   class="search-input"
                   x-model="searchQuery"
                   @input="search()">
        </div>
    </div>

    <!-- Language Switcher -->
<div class="px-4 mb-4" x-show="!$store.sidebar.collapsed">
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open"
                class="w-full flex items-center justify-between p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M8 12h8M12 8v8"></path>
                </svg>
                <span class="text-sm">{{ strtoupper(app()->getLocale()) }}</span>
            </div>
            <svg class="w-4 h-4 transition-transform" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>

        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-75"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @click.away="open = false"
             class="absolute z-50 mt-1 w-full bg-gray-800 border border-white/10 rounded-lg shadow-lg">

            {{-- VERSION SIMPLIFIÉE : Un seul paramètre --}}
            <form method="POST" action="{{ route('locale.change', 'fr') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-white/10 text-white text-sm {{ app()->getLocale() === 'fr' ? 'bg-white/5' : '' }}">
                    🇫🇷 Français
                </button>
            </form>

            <form method="POST" action="{{ route('locale.change', 'en') }}">
                @csrf
                <button type="submit"
                        class="w-full flex items-center gap-2 px-3 py-2 text-left hover:bg-white/10 text-white text-sm {{ app()->getLocale() === 'en' ? 'bg-white/5' : '' }}">
                    🇬🇧 English
                </button>
            </form>
        </div>
    </div>
</div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 pb-4 space-y-1 overflow-y-auto">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-item"
           :class="{ 'active': isActive('/dashboard') }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed">{{ __('app.sidebar.notifications') }}</span>
            <span class="badge ml-auto" x-show="!$store.sidebar.collapsed && $store.notifications.unreadCount > 0" x-text="$store.notifications.unreadCount"></span>
        </a>

        <!-- Settings -->
        <a href="#"
           class="sidebar-item"
           @click.prevent="handleAction('settings')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed">{{ __('app.sidebar.settings') }}</span>
        </a>

        <!-- Help -->
        <a href="#"
           class="sidebar-item"
           @click.prevent="handleAction('help')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed">{{ __('app.sidebar.help_support') }}</span>
        </a>

    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-white/10" x-show="!$store.sidebar.collapsed">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">{{ auth()->user()->name ?? 'Astronomer' }}</p>
                <p class="text-white/60 text-xs">{{ ucfirst(auth()->user()->subscription_type ?? __('app.sidebar.explorer')) }} • {{ __('app.sidebar.online') }}</p>
            </div>
        </div>
    </div>

    <!-- Collapse Toggle -->
    <div class="p-4 border-t border-white/10">
        <button @click="$store.sidebar.toggleCollapse()"
                class="w-full flex items-center justify-center p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors">
            <svg class="w-5 h-5 transition-transform duration-200"
                 :class="{ 'rotate-180': $store.sidebar.collapsed }"
                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

</aside>

<script>
function sidebarNavigation() {
    return {
        searchQuery: '',
        activeSessions: 3,
        systemHealth: 98,
        currentPath: window.location.pathname,

        isActive(path) {
            if (path === '/dashboard') {
                return this.currentPath === '/dashboard' || this.currentPath === '/';
            }
            return this.currentPath.startsWith(path);
        },

        search() {
            if (this.searchQuery.length < 2) return;

            console.log('Searching for:', this.searchQuery);
            window.showNotification('Search', `Searching for "${this.searchQuery}"...`, 'info', 2000);
        },

        handleAction(action) {
            const actions = {
                settings: () => {
                    window.showNotification('{{ __("app.sidebar.settings") }}', 'Opening application settings...', 'info');
                },
                help: () => {
                    window.showNotification('{{ __("app.sidebar.help_support") }}', 'Opening help documentation...', 'info');
                }
            };

            if (actions[action]) {
                actions[action]();
            }
        },

        init() {
            setInterval(() => {
                if (Math.random() > 0.9) {
                    this.systemHealth = Math.max(95, Math.min(100, this.systemHealth + (Math.random() * 2 - 1)));
                }
            }, 5000);
        }
    }
}
</script>
