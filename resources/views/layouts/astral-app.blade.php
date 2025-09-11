{{-- resources/views/layouts/astral-app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f0f23">

    <title>@yield('title', 'TelescopeApp - Modern Astral Interface')</title>

    <!-- Preconnect for performance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Custom CSS -->
    @stack('styles')
    <style>
        :root {
            --sidebar-width: 280px;
            --sidebar-collapsed: 64px;
            --transition-smooth: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            --gradient-primary: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --gradient-secondary: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.1);
        }

        * {
            font-family: 'Inter', sans-serif;
        }

        .font-astral {
            font-family: 'Orbitron', monospace;
        }

        body {
            background: #0a0a0f;
            min-height: 100vh;
            overflow-x: hidden;
            position: relative;
        }

        /* Fond spatial avec étoiles */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background:
                radial-gradient(2px 2px at 20px 30px, #ffffff, transparent),
                radial-gradient(2px 2px at 40px 70px, rgba(255,255,255,0.8), transparent),
                radial-gradient(1px 1px at 90px 40px, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 130px 80px, rgba(255,255,255,0.4), transparent),
                radial-gradient(2px 2px at 160px 30px, rgba(255,255,255,0.7), transparent),
                radial-gradient(1px 1px at 200px 60px, rgba(255,255,255,0.5), transparent),
                radial-gradient(1px 1px at 240px 90px, rgba(255,255,255,0.3), transparent),
                radial-gradient(2px 2px at 280px 10px, rgba(255,255,255,0.8), transparent),
                radial-gradient(1px 1px at 320px 50px, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 360px 80px, rgba(255,255,255,0.4), transparent),
                radial-gradient(2px 2px at 20px 120px, rgba(255,255,255,0.7), transparent),
                radial-gradient(1px 1px at 60px 140px, rgba(255,255,255,0.5), transparent),
                radial-gradient(1px 1px at 100px 160px, rgba(255,255,255,0.3), transparent),
                radial-gradient(2px 2px at 140px 130px, rgba(255,255,255,0.8), transparent),
                radial-gradient(1px 1px at 180px 150px, rgba(255,255,255,0.6), transparent),
                radial-gradient(1px 1px at 220px 170px, rgba(255,255,255,0.4), transparent),
                radial-gradient(2px 2px at 260px 110px, rgba(255,255,255,0.7), transparent),
                radial-gradient(1px 1px at 300px 140px, rgba(255,255,255,0.5), transparent),
                radial-gradient(1px 1px at 340px 160px, rgba(255,255,255,0.3), transparent),
                radial-gradient(2px 2px at 380px 120px, rgba(255,255,255,0.8), transparent),
                linear-gradient(135deg, #0a0a0f 0%, #0f0f23 25%, #1a1a2e 50%, #16213e 75%, #0a0a0f 100%);
            background-size: 400px 200px, 400px 200px, 400px 200px, 400px 200px, 400px 200px,
                           400px 200px, 400px 200px, 400px 200px, 400px 200px, 400px 200px,
                           400px 200px, 400px 200px, 400px 200px, 400px 200px, 400px 200px,
                           400px 200px, 400px 200px, 400px 200px, 400px 200px, 400px 200px,
                           100% 100%;
            background-repeat: repeat;
            animation: twinkle 8s infinite;
        }

        /* Animation scintillement léger des étoiles */
        @keyframes twinkle {
            0%, 100% { opacity: 1; }
            25% { opacity: 0.8; }
            50% { opacity: 0.9; }
            75% { opacity: 0.7; }
        }

        /* Sidebar moderne */
        .sidebar {
            width: var(--sidebar-width);
            transition: var(--transition-smooth);
            backdrop-filter: blur(20px);
            background: linear-gradient(180deg,
                rgba(255, 255, 255, 0.1) 0%,
                rgba(255, 255, 255, 0.05) 100%);
            border-right: 1px solid var(--glass-border);
        }

        .sidebar.collapsed {
            width: var(--sidebar-collapsed);
        }

        .sidebar-item {
            transition: var(--transition-smooth);
            border-radius: 12px;
            margin-bottom: 4px;
        }

        .sidebar-item:hover {
            background: rgba(255, 255, 255, 0.1);
            transform: translateX(4px);
        }

        .sidebar-item.active {
            background: var(--gradient-primary);
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
        }

        .badge {
            background: var(--gradient-secondary);
            color: white;
            font-size: 10px;
            font-weight: 600;
            padding: 2px 6px;
            border-radius: 10px;
            min-width: 18px;
            text-align: center;
        }

        .content-area {
            margin-left: var(--sidebar-width);
            transition: var(--transition-smooth);
            min-height: 100vh;
        }

        .content-area.sidebar-collapsed {
            margin-left: var(--sidebar-collapsed);
        }

        /* Dashboard cards */
        .dashboard-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 16px;
            transition: var(--transition-smooth);
        }

        .dashboard-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            border-color: rgba(255, 255, 255, 0.2);
        }

        /* Search */
        .search-container {
            position: relative;
        }

        .search-input {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            padding: 12px 16px 12px 40px;
            color: white;
            width: 100%;
            transition: var(--transition-smooth);
        }

        .search-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
        }

        /* Status indicators */
        .status-online {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.5;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                position: fixed;
                z-index: 50;
                height: 100vh;
            }

            .sidebar.mobile-open {
                transform: translateX(0);
            }

            .content-area {
                margin-left: 0;
            }

            .content-area.sidebar-collapsed {
                margin-left: 0;
            }
        }

        /* Notifications */
        .notification {
            background: var(--glass-bg);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 12px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }

        .notification.success {
            border-left: 4px solid #10b981;
        }

        .notification.error {
            border-left: 4px solid #ef4444;
        }

        .notification.info {
            border-left: 4px solid #3b82f6;
        }
    </style>
</head>
<body x-data="astralApp()" x-init="init()">
    <div class="astral-layout">
        <!-- Sidebar -->
        @include('layouts.partials.astral-sidebar')

        <!-- Main Content -->
        <div class="content-area" :class="{ 'sidebar-collapsed': collapsed }">

            <!-- Mobile Header -->
            <header class="lg:hidden bg-white/5 backdrop-blur-lg border-b border-white/10 p-4">
                <div class="flex items-center justify-between">
                    <h1 class="font-astral font-bold text-white text-lg">@yield('page-title', 'TelescopeApp')</h1>
                    <button @click="mobileOpen = !mobileOpen" class="text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>
                </div>
            </header>

            <!-- Page Content -->
            <main>
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div x-show="mobileOpen"
         @click="mobileOpen = false"
         class="fixed inset-0 bg-black/50 backdrop-blur-sm z-30 lg:hidden"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <!-- Notifications Container -->
    <div class="notifications-container fixed top-6 right-6 z-50 max-w-sm" x-data="notifications()">
        <template x-for="notification in items" :key="notification.id">
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
                    </div>
                    <div class="flex-1">
                        <h4 class="text-white font-medium text-sm" x-text="notification.title"></h4>
                        <p class="text-white/70 text-sm mt-1" x-text="notification.message"></p>
                    </div>
                    <button @click="remove(notification.id)" class="text-white/50 hover:text-white">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>
        </template>
    </div>

    @stack('scripts')

    <script>
        // Application principale
        function astralApp() {
            return {
                collapsed: false,
                mobileOpen: false,

                init() {
                    // État persistant de la sidebar
                    this.collapsed = localStorage.getItem('sidebar-collapsed') === 'true';

                    // Appliquer l'état initial au DOM
                    this.$nextTick(() => {
                        document.querySelector('.sidebar')?.classList.toggle('collapsed', this.collapsed);
                        document.querySelector('.content-area')?.classList.toggle('sidebar-collapsed', this.collapsed);
                    });

                    // Responsive behavior
                    this.handleResize();
                    window.addEventListener('resize', () => this.handleResize());

                    // Keyboard shortcuts
                    document.addEventListener('keydown', (e) => {
                        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
                            e.preventDefault();
                            this.toggleSidebar();
                        }
                        if (e.key === 'Escape') {
                            this.mobileOpen = false;
                        }
                    });

                    console.log('🌌 TelescopeApp Astral initialized');
                },

                handleResize() {
                    if (window.innerWidth >= 1024) {
                        this.mobileOpen = false;
                    }
                },

                toggleSidebar() {
                    if (window.innerWidth < 1024) {
                        this.mobileOpen = !this.mobileOpen;
                    } else {
                        this.collapsed = !this.collapsed;
                        localStorage.setItem('sidebar-collapsed', this.collapsed);

                        // Appliquer les classes immédiatement
                        const sidebar = document.querySelector('.sidebar');
                        const contentArea = document.querySelector('.content-area');

                        if (sidebar) {
                            sidebar.classList.toggle('collapsed', this.collapsed);
                        }
                        if (contentArea) {
                            contentArea.classList.toggle('sidebar-collapsed', this.collapsed);
                        }
                    }
                }
            }
        }

        // Système de notifications
        function notifications() {
            return {
                items: [],

                add(notification) {
                    const id = Date.now() + Math.random();
                    const item = {
                        id,
                        title: notification.title || 'Notification',
                        message: notification.message || '',
                        type: notification.type || 'info',
                        show: true,
                        ...notification
                    };

                    this.items.unshift(item);

                    // Limiter à 5 notifications
                    if (this.items.length > 5) {
                        this.items = this.items.slice(0, 5);
                    }

                    // Auto-remove after 5 seconds
                    setTimeout(() => {
                        this.remove(id);
                    }, notification.duration || 5000);

                    return id;
                },

                remove(id) {
                    const notification = this.items.find(n => n.id === id);
                    if (notification) {
                        notification.show = false;
                        setTimeout(() => {
                            this.items = this.items.filter(n => n.id !== id);
                        }, 300);
                    }
                }
            }
        }

        // Fonction globale pour les notifications
        window.showNotification = function(title, message, type = 'info', duration = 5000) {
            const notificationSystem = document.querySelector('[x-data*="notifications()"]').__x.$data;
            notificationSystem.add({ title, message, type, duration });
        };

        // Dark mode support
        if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</body>
</html>
