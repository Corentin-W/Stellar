<!-- resources/views/layouts/partials/sidebar.blade.php -->
<aside class="sidebar"
       :class="{ 'open': $store.sidebar.isOpen }"
       x-data="navigation()">

    <!-- Sidebar Header -->
    <div class="sidebar-header">
        <div class="flex items-center gap-3">
            <div class="relative">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center glow-primary animate-glow">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <div class="absolute -top-1 -right-1 w-3 h-3 bg-emerald-500 rounded-full animate-pulse"></div>
            </div>
            <div>
                <h1 class="text-xl font-bold gradient-text">TelescopeApp</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400">Remote Control</p>
            </div>
        </div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav scrollbar-thin">
        <!-- Main Navigation -->
        <div class="space-y-2">
            <div class="px-2">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                    Navigation
                </h3>
            </div>

            <a href="{{ route('dashboard', 'fr') }}"
               class="nav-item group"
               :class="{ 'active': isActive('/dashboard') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M13 9V3h8v6h-8zM3 13V3h8v10H3zm0 8V15h8v6H3zm10 0V11h8v10h-8z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Dashboard</span>
                <div class="w-2 h-2 rounded-full bg-indigo-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </a>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/telescope') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-purple-500 to-pink-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Control</span>
                <div class="status-dot status-online"
                     :class="{ 'status-offline': $store.telescope.status === 'offline' }"></div>
            </a>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/sessions') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-500 to-teal-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V8h14v11zM7 10h5v5H7z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Sessions</span>
                @if(isset($pendingSessions) && $pendingSessions > 0)
                    <div class="badge-error animate-pulse">{{ $pendingSessions }}</div>
                @endif
            </a>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/gallery') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M21 19V5c0-1.1-.9-2-2-2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2zM8.5 13.5l2.5 3.01L14.5 12l4.5 6H5l3.5-4.5z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Gallery</span>
                <div class="w-2 h-2 rounded-full bg-amber-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </a>
        </div>

        <!-- Services Section -->
        <div class="space-y-2 mt-8">
            <div class="px-2">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                    Services
                </h3>
            </div>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/lunar') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-slate-600 to-slate-800 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8 0-1.85.63-3.55 1.69-4.9.16-.21.43-.1.43.17v.73c0 4.97 4.03 9 9 9h.73c.27 0 .38.27.17.43-1.35 1.06-3.05 1.69-4.9 1.69z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Lunar Calendar</span>
                <div class="w-2 h-2 rounded-full bg-slate-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </a>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/subscription') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-violet-500 to-purple-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Premium</span>
                @if(auth()->user()->subscription_type === 'free')
                    <div class="badge-warning animate-pulse text-xs">Upgrade</div>
                @endif
            </a>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/shop') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-rose-500 to-pink-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M7 18c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2zM1 2v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.14 0-.25-.11-.25-.25l.03-.12L8.1 13h7.45c.75 0 1.41-.41 1.75-1.03L21.7 4H5.21l-.94-2H1zm16 16c-1.1 0-2 .9-2 2s.9 2 2 2 2-.9 2-2-.9-2-2-2z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Shop</span>
                <div class="w-2 h-2 rounded-full bg-rose-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </a>
        </div>

        <!-- Community Section -->
        <div class="space-y-2 mt-8">
            <div class="px-2">
                <h3 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-3">
                    Community
                </h3>
            </div>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/community') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-cyan-500 to-blue-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M16 4c0-1.11.89-2 2-2s2 .89 2 2-.89 2-2 2-2-.89-2-2zM4 18v-4h8.5v-.5c0-.55.45-1 1-1s1 .45 1 1v.5H23v4H4zM13 12.5c0-.83-.67-1.5-1.5-1.5S10 11.67 10 12.5v5h3v-5z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Community</span>
                <div class="w-2 h-2 rounded-full bg-cyan-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </a>

            <a href=""
               class="nav-item group"
               :class="{ 'active': isActive('/support') }">
                <div class="flex items-center justify-center w-8 h-8 rounded-lg bg-gradient-to-br from-green-500 to-emerald-600 text-white group-hover:scale-110 transition-transform duration-200">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11 18h2v-2h-2v2zm1-16C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-14c-2.21 0-4 1.79-4 4h2c0-1.1.9-2 2-2s2 .9 2 2c0 2-3 1.75-3 5h2c0-2.25 3-2.5 3-5 0-2.21-1.79-4-4-4z"/>
                    </svg>
                </div>
                <span class="flex-1 font-medium">Support</span>
                <div class="w-2 h-2 rounded-full bg-green-400 opacity-0 group-hover:opacity-100 transition-opacity duration-200"></div>
            </a>
        </div>
    </nav>

    <!-- User Section -->
    <div class="sidebar-footer">
        <div class="space-y-4">
            <!-- Quick Stats -->
            <div class="card-compact">
                <div class="flex items-center justify-between text-xs">
                    <span class="text-slate-600 dark:text-slate-400">Session Time</span>
                    <span class="font-semibold text-slate-900 dark:text-white" x-text="$store.telescope.currentSession ? '2h 34m' : '--'">--</span>
                </div>
                <div class="flex items-center justify-between text-xs mt-1">
                    <span class="text-slate-600 dark:text-slate-400">Images Captured</span>
                    <span class="font-semibold text-slate-900 dark:text-white">24</span>
                </div>
            </div>

            <!-- User Profile -->
            <div class="relative" x-data="userMenu()">
                <button @click="toggle()" class="w-full p-3 glass glass-hover rounded-xl transition-all duration-200 hover:scale-105">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-indigo-600 to-purple-600 flex items-center justify-center text-white font-semibold overflow-hidden">
                                @if(auth()->user()->avatar)
                                    <img src="{{ auth()->user()->avatar }}" alt="Avatar" class="w-full h-full object-cover">
                                @else
                                    <span class="text-sm">{{ substr(auth()->user()->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="absolute -top-1 -right-1 w-4 h-4 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-full flex items-center justify-center">
                                <div class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                        </div>
                        <div class="flex-1 text-left">
                            <div class="font-semibold text-slate-900 dark:text-white text-sm truncate">
                                {{ auth()->user()->name }}
                            </div>
                            <div class="text-xs text-slate-500 dark:text-slate-400">
                                {{ ucfirst(auth()->user()->subscription_type ?? 'Free') }} Plan
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-slate-400 transition-transform duration-200"
                             :class="{ 'rotate-180': isOpen }"
                             fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </button>

                <!-- User Dropdown -->
                <div x-show="isOpen"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                     x-transition:enter-end="opacity-1 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-1 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                     class="absolute bottom-full left-0 right-0 mb-2 p-2 glass rounded-xl shadow-2xl z-50"
                     @click.away="close()">

                    <a href=""
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group"
                       @click="close()">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">Profile Settings</span>
                    </a>

                    <a href=""
                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group"
                       @click="close()">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-500 to-slate-700 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-900 dark:text-white">App Settings</span>
                    </a>

                    <button @click="$store.darkMode.toggle(); close()"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                            <svg x-show="!$store.darkMode.isDark" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                            </svg>
                            <svg x-show="$store.darkMode.isDark" class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium text-slate-900 dark:text-white" x-text="$store.darkMode.isDark ? 'Light Mode' : 'Dark Mode'">Dark Mode</span>
                    </button>

                    <div class="h-px bg-slate-200 dark:bg-slate-700 my-2"></div>

                    <button @click="logout(); close()"
                            class="w-full flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 transition-all duration-200 group text-red-600 dark:text-red-400">
                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 01-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-sm font-medium">Sign Out</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</aside>
