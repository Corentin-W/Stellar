{{-- resources/views/layouts/partials/astral-sidebar.blade.php --}}

<aside class="sidebar fixed left-0 top-0 h-full z-40"
       :class="{
           'collapsed': $store.sidebar.collapsed,
           'mobile-open': $store.sidebar.mobileOpen
       }"
       x-data="sidebarNavigation()">

    <!-- Bouton Fermeture Mobile -->
    <button @click="$store.sidebar.close()"
            class="mobile-close-btn lg:hidden">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    <!-- Logo Section -->
    <div class="p-4 border-b border-white/10">
        <div class="flex items-center gap-3" :class="{ 'justify-center': $store.sidebar.collapsed }">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                </svg>
            </div>
            <div x-show="!$store.sidebar.collapsed || window.innerWidth < 1024" class="min-w-0">
                <h1 class="font-astral font-bold text-white text-lg truncate">TelescopeApp</h1>
                <p class="text-white/60 text-xs">Astral Interface</p>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="p-4" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
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

    <!-- Credits Balance (if authenticated) -->
    @auth
    <div class="px-4 mb-4" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
        <div class="bg-gradient-to-r from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-lg p-3">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-white font-semibold">{{ number_format(auth()->user()->credits_balance ?? 0) }}</div>
                    <div class="text-white/60 text-xs">Crédits disponibles</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-yellow-500/20 flex items-center justify-center">
                    <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>
    @endauth

    <!-- Quick Dashboard Button -->
    <div class="px-4 mb-3" x-show="(!$store.sidebar.collapsed || window.innerWidth < 1024) && currentPath !== '/dashboard' && !currentPath.includes('dashboard')">
        <a href="{{ route('dashboard') }}"
           class="w-full inline-flex items-center justify-center p-2 rounded-lg bg-blue-500/20 text-blue-200 hover:bg-blue-500/30 transition-colors"
           @click="closeMobileMenu()">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="text-sm font-medium">{{ __('app.sidebar.dashboard') }}</span>
        </a>
    </div>

    <!-- Language Switcher (Compact) -->
    <div class="px-4 mb-4" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
        <div class="flex items-center justify-between text-xs">
            <div class="flex items-center gap-2 text-white/60">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"></circle>
                    <path d="M2 12h20M12 2a15 15 0 010 20M12 2a15 15 0 000 20" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"></path>
                </svg>
                <span>{{ __('app.language') }}</span>
            </div>
            <div class="flex items-center gap-1">
                <form method="POST" action="{{ route('locale.change', 'fr') }}">
                    @csrf
                    <button type="submit" class="px-2 py-1 rounded-md {{ app()->getLocale() === 'fr' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5' }}">FR</button>
                </form>
                <form method="POST" action="{{ route('locale.change', 'en') }}">
                    @csrf
                    <button type="submit" class="px-2 py-1 rounded-md {{ app()->getLocale() === 'en' ? 'bg-white/10 text-white' : 'text-white/70 hover:text-white hover:bg-white/5' }}">EN</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 px-4 pb-4 space-y-1 overflow-y-auto">

        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="sidebar-item"
           :class="{ 'active': isActive('/dashboard') }"
           @click="closeMobileMenu()">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 14a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">{{ __('app.sidebar.dashboard') }}</span>
        </a>

        <!-- Credits Shop -->
        <a href="{{ route('credits.shop') }}"
           class="sidebar-item"
           :class="{ 'active': isActive('/credits/shop') }"
           @click="closeMobileMenu()">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">Boutique de Crédits</span>
            <span class="badge ml-auto bg-green-500" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
            </span>
        </a>

        <!-- Credits History -->
        <a href="{{ route('credits.history') }}"
           class="sidebar-item"
           :class="{ 'active': isActive('/credits/history') }"
           @click="closeMobileMenu()">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">Historique</span>
        </a>

        <!-- Divider for Admin Section -->
        @if(auth()->user()->admin == 1)
        <div class="px-3 py-2" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
            <div class="border-t border-white/10"></div>
            <div class="text-xs text-white/50 mt-2 font-medium">ADMINISTRATION</div>
        </div>

        <!-- Admin Panel -->
        <a href="{{ route('admin.panel') }}"
           class="sidebar-item"
           :class="{ 'active': isActive('/admin/panel') }"
           @click="closeMobileMenu()">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">Panel Admin</span>
        </a>

        <!-- Admin Credits Management -->
        <a href="{{ route('admin.credits.dashboard') }}"
           class="sidebar-item"
           :class="{ 'active': isActive('/admin/credits') }"
           @click="closeMobileMenu()">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">Gestion Crédits</span>
            <span class="badge ml-auto bg-red-500" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9 12a1 1 0 002 0V8a1 1 0 10-2 0v4zm1-7a.75.75 0 00-.75.75v.01c0 .414.336.75.75.75s.75-.336.75-.75v-.01A.75.75 0 0010 5z"/>
                </svg>
            </span>
        </a>
        @endif

        <!-- Settings -->
        <a href="#"
           class="sidebar-item"
           @click.prevent="handleAction('settings')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">{{ __('app.sidebar.settings') }}</span>
        </a>

        <!-- Help -->
        <a href="#"
           class="sidebar-item"
           @click.prevent="handleAction('help')">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">{{ __('app.sidebar.help_support') }}</span>
        </a>

        <!-- Logout -->
        <form method="POST" action="{{ route('logout', ['locale' => app()->getLocale()]) }}">
            @csrf
            <button type="submit" class="sidebar-item w-full text-left" @click="closeMobileMenu()">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H7a2 2 0 01-2-2V7a2 2 0 012-2h4a2 2 0 012 2v1"/>
                </svg>
                <span class="ml-3 font-medium" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">{{ __('app.sidebar.logout') }}</span>
            </button>
        </form>

    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-white/10" x-show="!$store.sidebar.collapsed || window.innerWidth < 1024">
        <div class="flex items-center gap-3 p-3 rounded-lg bg-white/5 hover:bg-white/10 transition-colors">
            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-sm flex-shrink-0">
                {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white font-medium text-sm truncate">
                    {{ auth()->user()->name ?? 'Astronomer' }}
                    @if(auth()->user()->admin == 1)
                        <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">Admin</span>
                    @endif
                </p>
                <p class="text-white/60 text-xs">{{ ucfirst(auth()->user()->subscription_type ?? __('app.sidebar.explorer')) }} • {{ __('app.sidebar.online') }}</p>
            </div>
        </div>
    </div>

    <!-- Collapse Toggle (Desktop Only) -->
    <div class="p-4 border-t border-white/10 hidden lg:block">
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
                return this.currentPath === '/dashboard' || this.currentPath === '/' || this.currentPath.includes('dashboard');
            }
            return this.currentPath.startsWith(path);
        },

        search() {
            if (this.searchQuery.length < 2) return;
            console.log('Searching for:', this.searchQuery);
            window.showNotification('Search', `Searching for "${this.searchQuery}"...`, 'info', 2000);
        },

        closeMobileMenu() {
            // Fermer le menu mobile après clic sur un lien
            if (window.innerWidth < 1024) {
                this.$store.sidebar.close();
            }
        },

        handleAction(action) {
            // Fermer le mobile menu d'abord
            this.closeMobileMenu();

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
            // Gestion du redimensionnement pour fermer le menu mobile
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.$store.sidebar.close();
                }
            });

            // Animation de santé système
            setInterval(() => {
                if (Math.random() > 0.9) {
                    this.systemHealth = Math.max(95, Math.min(100, this.systemHealth + (Math.random() * 2 - 1)));
                }
            }, 5000);

            console.log('🎛️ Sidebar Navigation initialized with mobile support');
        }
    }
}
</script>
