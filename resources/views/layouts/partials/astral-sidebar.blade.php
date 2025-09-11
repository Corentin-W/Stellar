{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}
<aside class="sidebar fixed left-0 top-0 h-full z-40"
       :class="{ 'collapsed': collapsed, 'mobile-open': mobileOpen }"
       x-data="sidebarNavigation()">



    <!-- Language Switcher -->
    <div class="p-4 border-b border-white/10" x-show="!collapsed">
        <div class="flex items-center gap-2">
            <button @click="setLanguage('fr')"
                    class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                    :class="currentLanguage === 'fr' ? 'bg-blue-500 text-white' : 'text-white/60 hover:text-white hover:bg-white/10'">
                FR
            </button>
            <button @click="setLanguage('en')"
                    class="px-3 py-1 rounded-md text-xs font-medium transition-colors"
                    :class="currentLanguage === 'en' ? 'bg-blue-500 text-white' : 'text-white/60 hover:text-white hover:bg-white/10'">
                EN
            </button>
        </div>
    </div>

    <!-- Search -->
    <div class="p-4" x-show="!collapsed">
        <div class="search-container">
            <svg class="search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   :placeholder="translations.search_cosmos"
                   class="search-input"
                   x-model="searchQuery"
                   @input="search()">
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 pb-4 space-y-1 overflow-y-auto">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-item flex items-center p-3 text-white"
           :class="{ 'active': isActive('/dashboard') }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.dashboard"></span>
        </a>

        <!-- Telescope Control -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('telescope')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.telescope_control"></span>
            <div class="status-online ml-auto" x-show="!collapsed && telescopeStatus === 'online'"></div>
        </a>

        <!-- Observation Sessions -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('sessions')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.sessions"></span>
            <span class="badge ml-auto" x-show="!collapsed && activeSessions > 0" x-text="activeSessions"></span>
        </a>

        <!-- Astrophoto Gallery -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('gallery')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.gallery"></span>
        </a>

        <!-- Section Divider -->
        <div class="pt-6" x-show="!collapsed">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide px-3 mb-3" x-text="translations.astro_tools"></p>
        </div>

        <!-- Weather Monitor -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('weather')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.weather"></span>
            <span class="badge ml-auto bg-green-500" x-show="!collapsed && weatherStatus === 'good'" x-text="translations.ok"></span>
        </a>

        <!-- Lunar Calendar -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('lunar')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.lunar_calendar"></span>
        </a>

        <!-- Target Planner -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('targets')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 1.657-2.657 1.657-2.657A8 8 0 0118.657 17.657z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.target_planner"></span>
        </a>

        <!-- Deep Sky Catalog -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('catalog')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.catalog"></span>
        </a>

        <!-- Quick Actions Section -->
        <div class="pt-6" x-show="!collapsed">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide px-3 mb-3" x-text="translations.quick_actions"></p>
        </div>

        <!-- New Session -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('newSession')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.new_session"></span>
        </a>

        <!-- Auto Guide -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('autoGuide')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.auto_guide"></span>
        </a>

        <!-- Capture Image -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('capture')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.capture_image"></span>
        </a>

        <!-- System Section -->
        <div class="pt-6" x-show="!collapsed">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide px-3 mb-3" x-text="translations.system"></p>
        </div>

        <!-- System Health -->
        <div class="sidebar-item flex items-center p-3 text-white/70" style="cursor: default;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.system_health"></span>
            <span class="badge ml-auto bg-green-500" x-show="!collapsed" x-text="systemHealth + '%'"></span>
        </div>

        <!-- Notifications -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('notifications')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0v14z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.notifications"></span>
            <span class="badge ml-auto" x-show="!collapsed && notificationCount > 0" x-text="notificationCount"></span>
        </a>

        <!-- Settings -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('settings')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.settings"></span>
        </a>

        <!-- Help -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('help')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="translations.help_support"></span>
        </a>

        <!-- Theme Toggle -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="toggleTheme()">
            <svg class="w-5 h-5 flex-shrink-0" x-show="!isDarkMode" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg class="w-5 h-5 flex-shrink-0" x-show="isDarkMode" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed" x-text="isDarkMode ? translations.light_mode : translations.dark_mode"></span>
        </a>

    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-white/10" x-show="!collapsed">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">{{ auth()->user()->name ?? 'Astronomer' }}</p>
                <p class="text-white/60 text-xs">{{ ucfirst(auth()->user()->subscription_type ?? 'Explorer') }} • Online</p>
            </div>
        </div>
    </div>

    <!-- Collapse Toggle -->
    <div class="p-4 border-t border-white/10">
        <button @click="toggleSidebar()"
                class="w-full flex items-center justify-center p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors">
            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': $root.collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

</aside>

<script>
function sidebarNavigation() {
    return {
        searchQuery: '',
        telescopeStatus: 'online',
        activeSessions: 3,
        weatherStatus: 'good',
        systemHealth: 98,
        notificationCount: 2,
        currentPath: window.location.pathname,
        isDarkMode: localStorage.getItem('theme') !== 'light',
        currentLanguage: localStorage.getItem('language') || '{{ app()->getLocale() }}',
        translations: @json(__('app')),

        toggleSidebar() {
            // Appeler la fonction du parent (astralApp)
            if (this.$root && this.$root.toggleSidebar) {
                this.$root.toggleSidebar();
            } else {
                // Fallback si $root n'est pas disponible
                const event = new CustomEvent('sidebar-toggle');
                document.dispatchEvent(event);
            }
        },

        isActive(path) {
            if (path === '/dashboard') {
                return this.currentPath === '/dashboard' || this.currentPath === '/';
            }
            return this.currentPath.startsWith(path);
        },

        search() {
            if (this.searchQuery.length < 2) return;

            console.log('Searching for:', this.searchQuery);
            window.showNotification(
                this.translations.search_cosmos.replace('...', ''),
                `${this.currentLanguage === 'fr' ? 'Recherche de' : 'Searching for'} "${this.searchQuery}"...`,
                'info',
                2000
            );
        },

        setLanguage(lang) {
            this.currentLanguage = lang;
            localStorage.setItem('language', lang);

            // Redirection vers la route avec la nouvelle locale
            const currentUrl = window.location.pathname;
            const newUrl = currentUrl.replace(/^\/(fr|en)/, `/${lang}`);

            window.showNotification(
                lang === 'fr' ? 'Langue changée' : 'Language Changed',
                lang === 'fr' ? 'Interface en français' : 'Interface in English',
                'info',
                2000
            );

            // Redirection après un court délai
            setTimeout(() => {
                window.location.href = newUrl;
            }, 500);
        },

        toggleTheme() {
            this.isDarkMode = !this.isDarkMode;
            const theme = this.isDarkMode ? 'dark' : 'light';

            localStorage.setItem('theme', theme);
            document.documentElement.setAttribute('data-theme', theme);

            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            window.showNotification(
                this.translations.theme_changed,
                `${this.translations.switched_to_mode} ${this.isDarkMode ? this.translations.dark_mode.toLowerCase() : this.translations.light_mode.toLowerCase()}`,
                'info',
                2000
            );
        },

        handleAction(action) {
            const actions = {
                telescope: () => {
                    if (this.telescopeStatus === 'online') {
                        window.showNotification(this.translations.telescope_control, 'Opening telescope control panel...', 'info');
                    } else {
                        window.showNotification(this.translations.error, this.translations.telescope_not_connected, 'error');
                    }
                },
                sessions: () => {
                    window.showNotification(this.translations.sessions, 'Loading observation sessions...', 'info');
                },
                gallery: () => {
                    window.showNotification(this.translations.gallery, 'Opening astrophoto gallery...', 'info');
                },
                weather: () => {
                    window.showNotification(this.translations.weather, 'Loading atmospheric conditions...', 'info');
                },
                lunar: () => {
                    window.showNotification(this.translations.lunar_calendar, 'Opening moon phase calculator...', 'info');
                },
                targets: () => {
                    window.showNotification(this.translations.target_planner, 'Loading celestial target planner...', 'info');
                },
                catalog: () => {
                    window.showNotification(this.translations.catalog, 'Opening deep sky catalog...', 'info');
                },
                newSession: () => {
                    window.showNotification(this.translations.new_session, 'Creating new observation session...', 'info');
                },
                autoGuide: () => {
                    if (this.telescopeStatus === 'online') {
                        window.showNotification(this.translations.auto_guide, 'Starting auto-guiding system...', 'success');
                    } else {
                        window.showNotification(this.translations.error, this.translations.telescope_not_connected, 'error');
                    }
                },
                capture: () => {
                    if (this.telescopeStatus === 'online') {
                        window.showNotification(this.translations.capture_image, 'Starting image capture...', 'info');
                        setTimeout(() => {
                            window.showNotification('Success', 'Image captured successfully!', 'success');
                        }, 3000);
                    } else {
                        window.showNotification(this.translations.error, this.translations.telescope_not_connected, 'error');
                    }
                },
                notifications: () => {
                    window.showNotification(this.translations.notifications, 'Opening notification center...', 'info');
                    this.notificationCount = 0;
                },
                settings: () => {
                    window.showNotification(this.translations.settings, 'Opening application settings...', 'info');
                },
                help: () => {
                    window.showNotification(this.translations.help_support, 'Opening help documentation...', 'info');
                }
            };

            if (actions[action]) {
                actions[action]();
            }
        },

        init() {
            // Écouter l'événement de toggle global
            document.addEventListener('sidebar-toggle', () => {
                // Force la mise à jour de l'état collapsed
                this.$nextTick(() => {
                    const sidebar = document.querySelector('.sidebar');
                    if (sidebar) {
                        const isCollapsed = sidebar.classList.contains('collapsed');
                        // Mettre à jour l'état local pour synchroniser l'icône
                        if (this.$root) {
                            this.$root.collapsed = isCollapsed;
                        }
                    }
                });
            });

            // Mise à jour périodique des statuts
            setInterval(() => {
                if (Math.random() > 0.9) {
                    this.systemHealth = Math.max(95, Math.min(100, this.systemHealth + (Math.random() * 2 - 1)));
                }
            }, 5000);

            // Initialiser le thème au chargement
            this.isDarkMode = localStorage.getItem('theme') !== 'light';
        }
    }
}
</script>{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}
<aside class="sidebar fixed left-0 top-0 h-full z-40"
       :class="{ 'collapsed': collapsed, 'mobile-open': mobileOpen }"
       x-data="sidebarNavigation()">



    <!-- Search -->
    <div class="p-4" x-show="!collapsed">
        <div class="search-container">
            <svg class="search-icon w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   placeholder="Search cosmos..."
                   class="search-input"
                   x-model="searchQuery"
                   @input="search()">
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 pb-4 space-y-1 overflow-y-auto">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-item flex items-center p-3 text-white"
           :class="{ 'active': isActive('/dashboard') }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Dashboard</span>
        </a>

        <!-- Telescope Control -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('telescope')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Telescope Control</span>
            <div class="status-online ml-auto" x-show="!collapsed && telescopeStatus === 'online'"></div>
        </a>

        <!-- Observation Sessions -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('sessions')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Sessions</span>
            <span class="badge ml-auto" x-show="!collapsed && activeSessions > 0" x-text="activeSessions"></span>
        </a>

        <!-- Astrophoto Gallery -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('gallery')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Gallery</span>
        </a>

        <!-- Section Divider -->
        <div class="pt-6" x-show="!collapsed">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide px-3 mb-3">Astro Tools</p>
        </div>

        <!-- Weather Monitor -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('weather')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Weather</span>
            <span class="badge ml-auto bg-green-500" x-show="!collapsed && weatherStatus === 'good'" x-text="weatherStatus.toUpperCase()"></span>
        </a>

        <!-- Lunar Calendar -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('lunar')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Lunar Calendar</span>
        </a>

        <!-- Target Planner -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('targets')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 1.657-2.657 1.657-2.657A8 8 0 0118.657 17.657z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Target Planner</span>
        </a>

        <!-- Deep Sky Catalog -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('catalog')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Catalog</span>
        </a>

        <!-- Quick Actions Section -->
        <div class="pt-6" x-show="!collapsed">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide px-3 mb-3">Quick Actions</p>
        </div>

        <!-- New Session -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('newSession')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">New Session</span>
        </a>

        <!-- Auto Guide -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('autoGuide')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Auto Guide</span>
        </a>

        <!-- Capture Image -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('capture')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Capture Image</span>
        </a>

        <!-- System Section -->
        <div class="pt-6" x-show="!collapsed">
            <p class="text-white/40 text-xs font-medium uppercase tracking-wide px-3 mb-3">System</p>
        </div>

        <!-- System Health -->
        <div class="sidebar-item flex items-center p-3 text-white/70" style="cursor: default;">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">System Health</span>
            <span class="badge ml-auto bg-green-500" x-show="!collapsed" x-text="systemHealth + '%'"></span>
        </div>

        <!-- Notifications -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('notifications')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0v14z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Notifications</span>
            <span class="badge ml-auto" x-show="!collapsed && notificationCount > 0" x-text="notificationCount"></span>
        </a>

        <!-- Settings -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('settings')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Settings</span>
        </a>

        <!-- Help -->
        <a href="#"
           class="sidebar-item flex items-center p-3 text-white/70 hover:text-white"
           @click="handleAction('help')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!collapsed">Help & Support</span>
        </a>

    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-white/10" x-show="!collapsed">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">{{ auth()->user()->name ?? 'Astronomer' }}</p>
                <p class="text-white/60 text-xs">{{ ucfirst(auth()->user()->subscription_type ?? 'Explorer') }} • Online</p>
            </div>
        </div>
    </div>

    <!-- Collapse Toggle -->
    <div class="p-4 border-t border-white/10">
        <button @click="$root.toggleSidebar()"
                class="w-full flex items-center justify-center p-2 rounded-lg bg-white/5 hover:bg-white/10 text-white transition-colors">
            <svg class="w-5 h-5 transition-transform" :class="{ 'rotate-180': $root.collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

</aside>

<script>
function sidebarNavigation() {
    return {
        searchQuery: '',
        telescopeStatus: 'online',
        activeSessions: 3,
        weatherStatus: 'good',
        systemHealth: 98,
        notificationCount: 2,
        currentPath: window.location.pathname,
        isDarkMode: localStorage.getItem('theme') !== 'light',

        isActive(path) {
            if (path === '/dashboard') {
                return this.currentPath === '/dashboard' || this.currentPath === '/';
            }
            return this.currentPath.startsWith(path);
        },

        search() {
            if (this.searchQuery.length < 2) return;

            // Simulation de recherche
            console.log('Searching for:', this.searchQuery);
            window.showNotification('Search', `Searching for "${this.searchQuery}"...`, 'info', 2000);
        },

        toggleTheme() {
            this.isDarkMode = !this.isDarkMode;
            const theme = this.isDarkMode ? 'dark' : 'light';

            localStorage.setItem('theme', theme);
            document.documentElement.setAttribute('data-theme', theme);

            if (this.isDarkMode) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            window.showNotification(
                'Theme Changed',
                `Switched to ${this.isDarkMode ? 'dark' : 'light'} mode`,
                'info',
                2000
            );
        },

        handleAction(action) {
            const actions = {
                telescope: () => {
                    if (this.telescopeStatus === 'online') {
                        window.showNotification('Telescope Control', 'Opening telescope control panel...', 'info');
                    } else {
                        window.showNotification('Error', 'Telescope not connected', 'error');
                    }
                },
                sessions: () => {
                    window.showNotification('Sessions', 'Loading observation sessions...', 'info');
                },
                gallery: () => {
                    window.showNotification('Gallery', 'Opening astrophoto gallery...', 'info');
                },
                weather: () => {
                    window.showNotification('Weather', 'Loading atmospheric conditions...', 'info');
                },
                lunar: () => {
                    window.showNotification('Lunar Calendar', 'Opening moon phase calculator...', 'info');
                },
                targets: () => {
                    window.showNotification('Target Planner', 'Loading celestial target planner...', 'info');
                },
                catalog: () => {
                    window.showNotification('Catalog', 'Opening deep sky catalog...', 'info');
                },
                newSession: () => {
                    window.showNotification('New Session', 'Creating new observation session...', 'info');
                },
                autoGuide: () => {
                    if (this.telescopeStatus === 'online') {
                        window.showNotification('Auto Guide', 'Starting auto-guiding system...', 'success');
                    } else {
                        window.showNotification('Error', 'Telescope not connected', 'error');
                    }
                },
                capture: () => {
                    if (this.telescopeStatus === 'online') {
                        window.showNotification('Capture', 'Starting image capture...', 'info');
                        setTimeout(() => {
                            window.showNotification('Success', 'Image captured successfully!', 'success');
                        }, 3000);
                    } else {
                        window.showNotification('Error', 'Telescope not connected', 'error');
                    }
                },
                notifications: () => {
                    window.showNotification('Notifications', 'Opening notification center...', 'info');
                    this.notificationCount = 0;
                },
                settings: () => {
                    window.showNotification('Settings', 'Opening application settings...', 'info');
                },
                help: () => {
                    window.showNotification('Help', 'Opening help documentation...', 'info');
                }
            };

            if (actions[action]) {
                actions[action]();
            }
        },

        init() {
            // Mise à jour périodique des statuts
            setInterval(() => {
                // Simulation de mise à jour des métriques
                if (Math.random() > 0.9) {
                    this.systemHealth = Math.max(95, Math.min(100, this.systemHealth + (Math.random() * 2 - 1)));
                }
            }, 5000);

            // Initialiser le thème au chargement
            this.isDarkMode = localStorage.getItem('theme') !== 'light';
        }
    }
}
</script>
