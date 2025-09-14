{{-- resources/views/layouts/astral-app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a0f">

    <title>@yield('title', 'TelescopeApp - Modern Astral Interface')</title>

    <!-- Favicon -->
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Vite Assets - Remplace les CDN -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <!-- Styles supplémentaires via stack -->
    @stack('styles')
</head>
<body x-data="astralApp()" x-init="init()" class="antialiased">
    <div class="astral-layout min-h-screen">

        <!-- Sidebar -->
        @include('layouts.partials.astral-sidebar')

        <!-- Main Content -->
        <div class="content-area" :class="{ 'sidebar-collapsed': $store.sidebar.collapsed }">

            <!-- Mobile Header -->
            <header class="lg:hidden bg-white/5 backdrop-blur-lg border-b border-white/10 p-4">
                <div class="flex items-center justify-between">
                    <h1 class="font-astral font-bold text-white text-lg">
                        @yield('page-title', 'TelescopeApp')
                    </h1>
                    <button type="button" @click="$store.sidebar.toggle()"
                            class="text-white hover:text-blue-400 transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main class="min-h-screen">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div x-show="$store.sidebar.mobileOpen" x-cloak
         @click="$store.sidebar.close()"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-40 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container fixed top-6 right-6 z-50 max-w-sm space-y-3">
        <template x-for="notification in $store.notifications.items" :key="notification.id">
            <div class="notification"
                 :class="notification.type"
                 x-show="notification.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full">

                <div class="flex items-start gap-3">
                    <div class="flex-shrink-0">
                        <template x-if="notification.type === 'success'">
                            <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        </template>
                        <template x-if="notification.type === 'error'">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                        </template>
                        <template x-if="notification.type === 'info'">
                            <svg class="w-5 h-5 text-blue-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                        </template>
                        <template x-if="notification.type === 'warning'">
                            <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h4 class="text-white font-medium text-sm" x-text="notification.title"></h4>
                        <p class="text-white/70 text-sm mt-1" x-text="notification.message"></p>
                    </div>
                    <button @click="$store.notifications.remove(notification.id)"
                            class="text-white/50 hover:text-white transition-colors">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    <!-- Scripts supplémentaires via stack -->
    @stack('scripts')

    <script>
        // Application principale Alpine.js
        function astralApp() {
            return {
                init() {
                    // Gestion responsive
                    this.handleResize();
                    window.addEventListener('resize', () => this.handleResize());

                    // Raccourcis clavier
                    document.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                            e.preventDefault();
                            this.$store.sidebar.toggle();
                        }
                        if (e.key === 'Escape') {
                            this.$store.sidebar.close();
                        }
                    });

                    console.log('🌌 TelescopeApp Astral Layout initialized');
                },

                handleResize() {
                    if (window.innerWidth >= 1024) {
                        this.$store.sidebar.mobileOpen = false;
                    }
                }
            }
        }
    </script>
</body>
</html>
