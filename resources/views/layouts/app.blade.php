{{-- resources/views/layouts/app.blade.php --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data x-init="$store.darkMode.init()">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0b0f">

    <title>@yield('title', 'TelescopeApp - Modern Telescope Control')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">

    <!-- Preconnect pour les performances -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- SEO Meta -->
    <meta name="description" content="Modern telescope control and astrophotography platform">
    <meta name="keywords" content="telescope, astrophotography, astronomy, control, remote">

    <!-- CSS -->
    <link rel="stylesheet" href="{{ mix('css/app.css') }}">

    <!-- Scripts en head pour Alpine -->
    <script defer src="{{ mix('js/app.js') }}"></script>

    @stack('head')
</head>
<body class="antialiased" x-data="{ sidebarOpen: false }" x-init="$store.darkMode.init()">
    <div class="app-layout">
        <!-- Sidebar -->
        @include('layouts.partials.sidebar')

        <!-- Contenu principal -->
        <div class="main-content" :class="{ 'sidebar-collapsed': $store.sidebar.collapsed }">
            <!-- Navbar -->
            @include('layouts.partials.navbar')

            <!-- Contenu de la page -->
            <main class="relative">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Container de notifications -->
    <div class="notifications-container" x-data="notificationSystem()">
        <template x-for="notification in notifications" :key="notification.id">
            <div class="notification-toast"
                 :class="notification.type"
                 x-show="notification.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-x-full"
                 x-transition:enter-end="opacity-100 transform translate-x-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-x-0"
                 x-transition:leave-end="opacity-0 transform translate-x-full">

                <div class="notification-content">
                    <div class="notification-icon"
                         :class="{
                             'bg-green-100 text-green-600': notification.type === 'success',
                             'bg-red-100 text-red-600': notification.type === 'error',
                             'bg-blue-100 text-blue-600': notification.type === 'info',
                             'bg-yellow-100 text-yellow-600': notification.type === 'warning'
                         }">
                        <!-- Icon SVG basé sur le type -->
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <template x-if="notification.type === 'success'">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </template>
                            <template x-if="notification.type === 'error'">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </template>
                            <template x-if="notification.type === 'info'">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </template>
                            <template x-if="notification.type === 'warning'">
                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </template>
                        </svg>
                    </div>

                    <div class="notification-body">
                        <div class="notification-title" x-text="notification.title"></div>
                        <div class="notification-message" x-text="notification.message"></div>
                    </div>
                </div>

                <button @click="remove(notification.id)" class="notification-close">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    @stack('scripts')

    <!-- Scripts Alpine additionnels -->
    <script>
        // Système de notifications global
        function notificationSystem() {
            return {
                notifications: [],

                init() {
                    // Écouter les événements personnalisés
                    window.addEventListener('show-notification', (event) => {
                        this.add(event.detail);
                    });

                    // Synchroniser avec le store Alpine
                    this.$watch('$store.notifications.items', (newItems) => {
                        this.notifications = newItems.filter(item => item.show);
                    });
                },

                add(notification) {
                    Alpine.store('notifications').add(notification);
                },

                remove(id) {
                    Alpine.store('notifications').remove(id);
                }
            }
        }

        // Fonctions globales utilitaires
        window.showNotification = function(title, message, type = 'info', duration = 5000) {
            Alpine.store('notifications').add({ title, message, type, duration });
        };

        // Raccourcis clavier globaux
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K pour la recherche
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.search-input');
                if (searchInput) {
                    searchInput.focus();
                }
            }

            // Ctrl/Cmd + D pour basculer le mode sombre
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                Alpine.store('darkMode').toggle();
            }

            // Escape pour fermer les modaux/dropdowns
            if (e.key === 'Escape') {
                Alpine.store('sidebar').close();
            }
        });

        // Gestion responsive
        const handleResize = () => {
            if (window.innerWidth >= 1024) {
                Alpine.store('sidebar').close();
            }
        };

        window.addEventListener('resize', handleResize);

        // Initialisation
        document.addEventListener('DOMContentLoaded', function() {
            // Animation d'entrée
            document.body.classList.add('fade-in');

            // Message de bienvenue
            setTimeout(() => {
                showNotification(
                    'Welcome to TelescopeApp!',
                    'Your modern telescope control platform is ready.',
                    'success'
                );
            }, 1000);

            console.log('🔭 TelescopeApp initialized successfully');
        });
    </script>

    <style>
        /* Animations d'entrée pour le body */
        body {
            opacity: 0;
            transition: opacity 0.5s ease;
        }

        body.fade-in {
            opacity: 1;
        }

        /* Styles pour les états de chargement */
        .loading {
            pointer-events: none;
            opacity: 0.6;
        }

        .loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top-color: var(--accent-blue);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Amélioration des focus states pour l'accessibilité */
        .focus-visible {
            outline: 2px solid var(--accent-blue);
            outline-offset: 2px;
        }

        /* Optimisations pour les appareils tactiles */
        @media (hover: none) and (pointer: coarse) {
            .hover\:scale-105:hover {
                transform: none;
            }

            .hover\:translateY-2:hover {
                transform: none;
            }
        }
    </style>
</body>
</html>
