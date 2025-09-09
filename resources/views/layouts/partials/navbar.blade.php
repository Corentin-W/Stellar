<!-- resources/views/layouts/partials/navbar.blade.php -->
<header class="top-navbar">
    <div class="navbar-content">
        <!-- Left Section -->
        <div class="flex items-center gap-4">
            <!-- Mobile Menu Toggle -->
            <button @click="$store.sidebar.toggle()" class="lg:hidden icon-btn">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <!-- Page Title Section -->
            <div class="flex flex-col">
                <h1 class="text-xl lg:text-2xl font-bold text-slate-900 dark:text-white">
                    @yield('page-title', 'Dashboard')
                </h1>
                @if(isset($breadcrumbs))
                    <nav class="hidden sm:flex items-center space-x-2 text-sm">
                        @foreach($breadcrumbs as $crumb)
                            @if($loop->last)
                                <span class="text-slate-600 dark:text-slate-400 font-medium">{{ $crumb['title'] }}</span>
                            @else
                                <a href="{{ $crumb['url'] }}" class="text-slate-500 dark:text-slate-500 hover:text-slate-700 dark:hover:text-slate-300 transition-colors duration-200">
                                    {{ $crumb['title'] }}
                                </a>
                                <svg class="w-4 h-4 text-slate-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endforeach
                    </nav>
                @endif
                <!-- Quick filters (pills) -->
                <div class="hidden md:flex items-center gap-2 mt-2">
                    <button class="pill">
                        <span class="text-slate-500 dark:text-slate-300">Date:</span>
                        <span class="font-semibold">Now</span>
                    </button>
                    <button class="pill">
                        <span class="text-slate-500 dark:text-slate-300">Product:</span>
                        <span class="font-semibold">All</span>
                    </button>
                    <button class="pill">
                        <span class="text-slate-500 dark:text-slate-300">Profile:</span>
                        <span class="font-semibold">Bogdan</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Center Section - Search -->
        <div class="hidden lg:flex flex-1 justify-center px-6">
            <div class="w-full max-w-lg" x-data="searchBox()">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input type="text"
                           x-model="query"
                           @focus="isOpen = true"
                           @keydown.escape="close()"
                           @keydown.enter="performSearch()"
                           class="input pl-10 pr-10 bg-white/50 dark:bg-slate-800/50 border-slate-200/50 dark:border-slate-700/50 placeholder-slate-500 dark:placeholder-slate-400"
                           placeholder="Search telescopes, sessions, images..."
                           autocomplete="off">

                    <div x-show="query.length > 0"
                         @click="query = ''; close()"
                         class="absolute inset-y-0 right-0 pr-3 flex items-center cursor-pointer">
                        <svg class="w-5 h-5 text-slate-400 hover:text-slate-600 dark:hover:text-slate-300 transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </div>
                </div>

                <!-- Search Results Dropdown -->
                <div x-show="isOpen && query.length > 2"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95"
                     class="absolute top-full left-0 right-0 mt-2 glass rounded-2xl shadow-2xl z-50 max-h-96 overflow-hidden"
                     @click.away="close()">

                    <div class="p-4 space-y-4">
                        <!-- Telescopes -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Telescopes</h4>
                            <div class="space-y-1">
                                <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group">
                                    <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center text-white">
                                        🔭
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900 dark:text-white">Celestron NexStar 8SE</div>
                                        <div class="text-xs text-emerald-600 dark:text-emerald-400">Available now</div>
                                    </div>
                                    <div class="status-dot status-online"></div>
                                </a>
                            </div>
                        </div>

                        <!-- Recent Images -->
                        <div>
                            <h4 class="text-xs font-semibold text-slate-500 dark:text-slate-400 uppercase tracking-wider mb-2">Recent Images</h4>
                            <div class="space-y-1">
                                <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group">
                                    <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center text-white">
                                        📸
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium text-slate-900 dark:text-white">Andromeda Galaxy</div>
                                        <div class="text-xs text-slate-500 dark:text-slate-400">Captured 2 hours ago</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="pt-2 border-t border-slate-200/50 dark:border-slate-700/50">
                            <button @click="performSearch()" class="w-full text-left text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                                See all results for "<span x-text="query"></span>"
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Section -->
        <div class="flex items-center gap-3">
            <!-- Telescope Status -->
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 glass rounded-full"
                 :class="{
                     'text-emerald-600 dark:text-emerald-400': $store.telescope.status === 'online',
                     'text-slate-500 dark:text-slate-400': $store.telescope.status === 'offline',
                     'text-amber-600 dark:text-amber-400': $store.telescope.status === 'connecting'
                 }">
                <div class="status-dot"
                     :class="{
                         'status-online': $store.telescope.status === 'online',
                         'status-offline': $store.telescope.status === 'offline',
                         'bg-amber-500 animate-pulse': $store.telescope.status === 'connecting'
                     }"></div>
                <span class="text-xs font-medium" x-text="$store.telescope.status.charAt(0).toUpperCase() + $store.telescope.status.slice(1)">Offline</span>
            </div>

            <!-- Weather Widget -->
            <div class="hidden lg:flex items-center gap-2 px-3 py-1.5 glass rounded-full text-xs text-slate-600 dark:text-slate-400" x-data="{ weather: $store.telescope.weather }">
                <svg class="w-4 h-4 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                </svg>
                <span x-text="Math.round(weather.temperature) + '°C'">-2°C</span>
                <span x-text="weather.condition">Clear</span>
            </div>

            <!-- Session Timer -->
            <div x-show="$store.telescope.currentSession"
                 x-transition
                 class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-full text-xs font-semibold text-emerald-600 dark:text-emerald-400">
                <svg class="w-4 h-4 animate-spin" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"/>
                </svg>
                <span>2h 34m</span>
            </div>

            <!-- Dark Mode Toggle -->
            <button @click="$store.darkMode.toggle()"
                    class="icon-btn group"
                    title="Toggle theme">
                <svg x-show="!$store.darkMode.isDark" class="w-5 h-5 group-hover:text-amber-500 transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd"/>
                </svg>
                <svg x-show="$store.darkMode.isDark" class="w-5 h-5 group-hover:text-blue-400 transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"/>
                </svg>
            </button>

            <!-- Notifications -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="icon-btn relative group">
                    <svg class="w-5 h-5 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M10 2C7.79 2 6 3.79 6 6v3.5L4.5 11c-.28 0-.5.22-.5.5s.22.5.5.5h11c.28 0 .5-.22.5-.5s-.22-.5-.5-.5L14 9.5V6c0-2.21-1.79-4-4-4zm2 13h-4c0 1.1.9 2 2 2s2-.9 2-2z"/>
                    </svg>
                    <span x-show="$store.notifications.unreadCount > 0"
                          class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full animate-pulse"
                          x-text="$store.notifications.unreadCount"></span>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                     class="absolute top-full right-0 mt-2 w-96 glass rounded-2xl shadow-2xl z-50"
                     @click.away="open = false">

                    <div class="p-4 border-b border-slate-200/50 dark:border-slate-700/50">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-semibold text-slate-900 dark:text-white">Notifications</h3>
                            <button @click="$store.notifications.markAllAsRead()"
                                    class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                                Mark all read
                            </button>
                        </div>
                    </div>

                    <div class="max-h-80 overflow-y-auto scrollbar-thin">
                        <template x-for="notification in $store.notifications.items.slice(0, 5)" :key="notification.id">
                            <div class="p-4 hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 border-b border-slate-200/50 dark:border-slate-700/50 last:border-b-0"
                                 :class="{ 'bg-indigo-50/50 dark:bg-indigo-900/10': !notification.read }">
                                <div class="flex gap-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                             :class="{
                                                 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-600': notification.type === 'success',
                                                 'bg-red-100 dark:bg-red-900/30 text-red-600': notification.type === 'error',
                                                 'bg-amber-100 dark:bg-amber-900/30 text-amber-600': notification.type === 'warning',
                                                 'bg-blue-100 dark:bg-blue-900/30 text-blue-600': notification.type === 'info'
                                             }">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                            </svg>
                                        </div>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-slate-900 dark:text-white" x-text="notification.title"></p>
                                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1" x-text="notification.message"></p>
                                        <p class="text-xs text-slate-500 dark:text-slate-500 mt-2" x-text="window.utils.formatRelativeTime(notification.timestamp)"></p>
                                    </div>
                                    <div x-show="!notification.read" class="flex-shrink-0">
                                        <div class="w-2 h-2 bg-indigo-600 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        </template>

                        <div x-show="$store.notifications.items.length === 0" class="p-8 text-center">
                            <div class="w-16 h-16 mx-auto mb-4 bg-slate-100 dark:bg-slate-800 rounded-2xl flex items-center justify-center">
                                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5V3h0v14z"/>
                                </svg>
                            </div>
                            <p class="text-slate-500 dark:text-slate-400">No notifications yet</p>
                        </div>
                    </div>

                    <div x-show="$store.notifications.items.length > 5" class="p-4 border-t border-slate-200/50 dark:border-slate-700/50 text-center">
                        <a href="" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 dark:hover:text-indigo-300 font-medium">
                            View all notifications
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" class="icon-btn group">
                    <svg class="w-5 h-5 group-hover:text-indigo-600 dark:group-hover:text-indigo-400 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"/>
                    </svg>
                </button>

                <div x-show="open"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-1"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 translate-y-1"
                     class="absolute top-full right-0 mt-2 w-64 glass rounded-2xl shadow-2xl z-50 p-4"
                     @click.away="open = false">

                    <h3 class="text-sm font-semibold text-slate-900 dark:text-white mb-3">Quick Actions</h3>

                    <div class="space-y-2">
                        <a href="" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group" @click="open = false">
                            <div class="w-8 h-8 bg-gradient-to-br from-purple-500 to-pink-600 rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                🔭
                            </div>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">Control Telescope</span>
                        </a>

                        <a href="" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group" @click="open = false">
                            <div class="w-8 h-8 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                ➕
                            </div>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">New Session</span>
                        </a>

                        <a href="" class="flex items-center gap-3 p-3 rounded-xl hover:bg-white/50 dark:hover:bg-slate-800/50 transition-all duration-200 group" @click="open = false">
                            <div class="w-8 h-8 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg flex items-center justify-center text-white group-hover:scale-110 transition-transform duration-200">
                                📸
                            </div>
                            <span class="text-sm font-medium text-slate-900 dark:text-white">View Gallery</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
