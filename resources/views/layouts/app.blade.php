<!-- resources/views/layouts/app.blade.php -->
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4f46e5" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#1e293b" media="(prefers-color-scheme: dark)">

    <title>@yield('title', 'TelescopeApp - Remote Telescope Control')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="/favicon.svg">
    <link rel="alternate icon" href="/favicon.ico">
    <link rel="apple-touch-icon" href="/apple-touch-icon.png">

    <!-- Vite CSS -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('head')
</head>
<body class="h-full antialiased"
      x-data="{ sidebarOpen: false }"
      :class="{ 'overflow-hidden lg:overflow-auto': $store.sidebar.isOpen }"
      x-init="$store.darkMode.init()">

    <div class="dashboard-layout neo-ui">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Main Content Area -->
        <div class="main-wrapper">
            <!-- Top Navigation -->
            @include('layouts.partials.navbar')

            <!-- Page Content -->
            <main class="page-content">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-transition
                         x-init="setTimeout(() => show = false, 5000)"
                         class="notification-success mb-6 animate-slide-down">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-emerald-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-emerald-800 dark:text-emerald-200">
                                    {{ session('success') }}
                                </p>
                            </div>
                            <button @click="show = false" class="flex-shrink-0 p-1 text-emerald-600 hover:text-emerald-800 dark:hover:text-emerald-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div x-data="{ show: true }"
                         x-show="show"
                         x-transition
                         x-init="setTimeout(() => show = false, 5000)"
                         class="notification-error mb-6 animate-slide-down">
                        <div class="flex items-center gap-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-red-800 dark:text-red-200">
                                    {{ session('error') }}
                                </p>
                            </div>
                            <button @click="show = false" class="flex-shrink-0 p-1 text-red-600 hover:text-red-800 dark:hover:text-red-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endif

                <!-- Main Content -->
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div x-show="$store.sidebar.isOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="mobile-overlay"
         @click="$store.sidebar.close()"></div>

    <!-- Toast Notifications -->
    @include('layouts.partials.notifications')

    <!-- Loading Overlay -->
    <div x-data="{ loading: false }"
         x-show="loading"
         x-transition
         class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[9999]"
         x-on:loading.window="loading = $event.detail.loading">
        <div class="glass rounded-2xl p-8 flex items-center gap-4 animate-fade-in">
            <div class="animate-spin rounded-full h-8 w-8 border-2 border-indigo-600 border-t-transparent"></div>
            <span class="text-slate-900 dark:text-white font-medium">Loading...</span>
        </div>
    </div>

    <!-- PWA Install Prompt -->
    <div x-data="pwaInstall()"
         x-show="showInstallPrompt"
         x-transition
         class="fixed bottom-6 left-6 right-6 lg:left-auto lg:right-6 lg:w-96 card animate-slide-up z-50">
        <div class="flex items-center gap-4">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center glow-primary">
                    <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                    </svg>
                </div>
            </div>
            <div class="flex-1">
                <h3 class="font-semibold text-slate-900 dark:text-white">Install TelescopeApp</h3>
                <p class="text-sm text-slate-600 dark:text-slate-400">Add to your home screen for quick access</p>
            </div>
            <div class="flex gap-2">
                <button @click="dismissInstallPrompt()"
                        class="btn-ghost px-3 py-1.5 text-xs">
                    Later
                </button>
                <button @click="installApp()"
                        class="btn-primary px-4 py-1.5 text-xs">
                    Install
                </button>
            </div>
        </div>
    </div>

    @stack('scripts')

    <!-- PWA & Loading Scripts -->
    <script>
        // PWA Install component
        function pwaInstall() {
            return {
                deferredPrompt: null,
                showInstallPrompt: false,

                init() {
                    window.addEventListener('beforeinstallprompt', (e) => {
                        e.preventDefault();
                        this.deferredPrompt = e;
                        this.showInstallPrompt = true;
                    });

                    window.addEventListener('appinstalled', () => {
                        this.showInstallPrompt = false;
                        this.deferredPrompt = null;
                        window.showNotification('App Installed', 'TelescopeApp has been added to your home screen!', 'success');
                    });
                },

                async installApp() {
                    if (this.deferredPrompt) {
                        this.deferredPrompt.prompt();
                        const { outcome } = await this.deferredPrompt.userChoice;
                        this.deferredPrompt = null;
                        this.showInstallPrompt = false;
                    }
                },

                dismissInstallPrompt() {
                    this.showInstallPrompt = false;
                    localStorage.setItem('pwa-install-dismissed', Date.now());
                }
            }
        }

        // Global loading state
        window.showLoading = function(show = true) {
            window.dispatchEvent(new CustomEvent('loading', {
                detail: { loading: show }
            }));
        };

        // Enhanced error handling with beautiful notifications
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            window.showNotification('System Error', 'An unexpected error occurred', 'error');
        });

        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
            window.showNotification('Network Error', 'A connection error occurred', 'error');
        });

        // Service Worker for PWA
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('/sw.js')
                    .then(function(registration) {
                        console.log('ServiceWorker registration successful');
                    })
                    .catch(function(err) {
                        console.log('ServiceWorker registration failed: ', err);
                    });
            });
        }

        // Performance monitoring
        window.addEventListener('load', function() {
            const perfData = performance.getEntriesByType('navigation')[0];
            if (perfData.loadEventEnd > 3000) {
                console.warn('Page load time exceeded 3 seconds:', perfData.loadEventEnd);
            }
        });

        // Preload critical images
        const criticalImages = [
            '/images/logo.svg',
            '/images/hero-bg.jpg'
        ];

        criticalImages.forEach(src => {
            const link = document.createElement('link');
            link.rel = 'preload';
            link.as = 'image';
            link.href = src;
            document.head.appendChild(link);
        });
    </script>
</body>
</html>
