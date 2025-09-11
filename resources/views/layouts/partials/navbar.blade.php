{{-- resources/views/layouts/partials/navbar.blade.php --}}
<header class="top-navbar">
    <div class="navbar-left">
        <!-- Menu mobile -->
        <button @click="$store.sidebar.toggle()" class="lg:hidden navbar-button">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Titre et breadcrumbs -->
        <div>
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            <div class="breadcrumbs">
                <a href="#" class="breadcrumb-item">Home</a>
                <span class="breadcrumb-separator">/</span>
                <span class="breadcrumb-item">@yield('page-title', 'Dashboard')</span>
            </div>
            <div class="filter-pills">
                <button class="filter-pill">
                    <span>Date:</span>
                    <strong>Now</strong>
                </button>
                <button class="filter-pill">
                    <span>Product:</span>
                    <strong>All</strong>
                </button>
                <button class="filter-pill">
                    <span>Profile:</span>
                    <strong>{{ auth()->user()->name ?? 'User' }}</strong>
                </button>
            </div>
        </div>
    </div>

    <!-- Recherche centrale -->
    <div class="navbar-center">
        <div class="search-container" x-data="searchBox()">
            <svg class="search-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <input type="text"
                   x-model="query"
                   @focus="isOpen = true"
                   @keydown.escape="close()"
                   class="search-input"
                   placeholder="Search telescopes, sessions, images...">
        </div>
    </div>

    <!-- Actions droite -->
    <div class="navbar-right">
        <!-- Statut télescope -->
        <div class="navbar-button tooltip"
             data-tooltip="Telescope Status"
             :class="{ 'active': $store.telescope.status === 'online' }">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <div class="status-indicator"
                 :class="$store.telescope.status === 'online' ? 'status-online' : 'status-offline'"></div>
        </div>

        <!-- Météo -->
        <div class="navbar-button tooltip" data-tooltip="Weather Conditions">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"/>
            </svg>
        </div>

        <!-- Mode sombre -->
        <button @click="$store.darkMode.toggle()"
                class="navbar-button dark-mode-toggle tooltip"
                data-tooltip="Toggle Theme">
            <svg x-show="!$store.darkMode.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
            <svg x-show="$store.darkMode.isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
            </svg>
        </button>

        <!-- Notifications -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="navbar-button tooltip" data-tooltip="Notifications">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0v14z"/>
                </svg>
                <div class="notification-badge" x-show="$store.notifications.unreadCount > 0">
                    <span x-text="$store.notifications.unreadCount"></span>
                </div>
            </button>

            <!-- Dropdown notifications -->
            <div x-show="open"
                 x-transition
                 @click.away="open = false"
                 class="dropdown"
                 style="top: 100%; right: 0; width: 320px; margin-top: 8px;">
                <div class="p-4 border-b border-gray-700">
                    <h3 class="font-semibold text-white">Notifications</h3>
                </div>
                <div class="max-h-64 overflow-y-auto">
                    <div class="dropdown-item">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <div>
                            <div class="text-white text-sm font-medium">Session completed</div>
                            <div class="text-gray-400 text-xs">Andromeda Galaxy captured successfully</div>
                        </div>
                    </div>
                    <div class="dropdown-item">
                        <div class="w-2 h-2 bg-blue-500 rounded-full"></div>
                        <div>
                            <div class="text-white text-sm font-medium">Weather update</div>
                            <div class="text-gray-400 text-xs">Clear skies for tonight</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions rapides -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="navbar-button tooltip" data-tooltip="Quick Actions">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                </svg>
            </button>

            <!-- Dropdown actions -->
            <div x-show="open"
                 x-transition
                 @click.away="open = false"
                 class="dropdown"
                 style="top: 100%; right: 0; width: 240px; margin-top: 8px;">
                <a href="#" class="dropdown-item">
                    <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center text-white">🔭</div>
                    <span class="text-white">Control Telescope</span>
                </a>
                <a href="#" class="dropdown-item">
                    <div class="w-8 h-8 bg-green-600 rounded-lg flex items-center justify-center text-white">➕</div>
                    <span class="text-white">New Session</span>
                </a>
                <a href="#" class="dropdown-item">
                    <div class="w-8 h-8 bg-purple-600 rounded-lg flex items-center justify-center text-white">📸</div>
                    <span class="text-white">View Gallery</span>
                </a>
            </div>
        </div>
    </div>
</header>
