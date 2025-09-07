<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - @yield('title', __('app.welcome'))</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900" rel="stylesheet" />
    <link href="https://fonts.bunny.net/css?family=space-mono:400,700" rel="stylesheet" />

    <!-- Scripts et CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        mono: ['Space Mono', 'monospace'],
                    }
                }
            }
        }
    </script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        .star-field {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: linear-gradient(135deg, #0c0a1e 0%, #1a1443 25%, #2d1b69 50%, #0c0a1e 100%);
            overflow: hidden;
        }

        .star {
            position: absolute;
            background: white;
            border-radius: 50%;
            animation: twinkle 2s infinite alternate;
        }

        @keyframes twinkle {
            0% { opacity: 0.3; }
            100% { opacity: 1; }
        }

        .cosmic-glow {
            background: linear-gradient(135deg, rgba(147, 51, 234, 0.1) 0%, rgba(79, 70, 229, 0.1) 50%, rgba(236, 72, 153, 0.1) 100%);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nebula-gradient {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .planet-glow {
            box-shadow: 0 0 30px rgba(147, 51, 234, 0.3), 0 0 60px rgba(79, 70, 229, 0.2);
        }
    </style>
</head>

<body class="font-sans antialiased text-white min-h-screen relative">
    <!-- Star field background -->
    <div class="star-field" id="starField"></div>

    <!-- Navigation -->
    <nav class="cosmic-glow fixed top-0 w-full z-50 px-6 py-4">
        <div class="max-w-7xl mx-auto flex justify-between items-center">
            <div class="flex items-center space-x-2">
                <div class="w-8 h-8 rounded-full nebula-gradient planet-glow"></div>
                <span class="text-xl font-bold font-mono">{{ __('app.app_name') }}</span>
            </div>

            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ url(app()->getLocale()) }}" class="hover:text-purple-300 transition-colors">
                    {{ __('app.home') }}
                </a>
                <a href="#" class="hover:text-purple-300 transition-colors">
                    {{ __('app.about') }}
                </a>
                <a href="#" class="hover:text-purple-300 transition-colors">
                    {{ __('app.contact') }}
                </a>
            </div>

            <div class="flex items-center space-x-4">
                <!-- Language switcher -->
                <div class="relative" x-data="{ open: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg cosmic-glow hover:bg-white/5 transition-colors">
                        <span class="text-sm">{{ strtoupper(app()->getLocale()) }}</span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-32 cosmic-glow rounded-lg shadow-lg">
                        <a href="{{ route('locale', 'fr') }}" class="block px-4 py-2 text-sm hover:bg-white/5 rounded-t-lg transition-colors">
                            🇫🇷 Français
                        </a>
                        <a href="{{ route('locale', 'en') }}" class="block px-4 py-2 text-sm hover:bg-white/5 rounded-b-lg transition-colors">
                            🇬🇧 English
                        </a>
                    </div>
                </div>

                <!-- Auth buttons -->
                @guest
                    <a href="{{ route('login', app()->getLocale()) }}" class="px-4 py-2 rounded-lg border border-purple-400 hover:bg-purple-400/20 transition-colors">
                        {{ __('auth.login') }}
                    </a>
                    <a href="{{ route('register', app()->getLocale()) }}" class="px-4 py-2 rounded-lg nebula-gradient hover:shadow-lg transition-shadow">
                        {{ __('auth.register') }}
                    </a>
                @else
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-2 px-3 py-2 rounded-lg cosmic-glow hover:bg-white/5 transition-colors">
                            @if(auth()->user()->avatar)
                                <img src="{{ auth()->user()->avatar }}" alt="{{ auth()->user()->name }}" class="w-6 h-6 rounded-full">
                            @endif
                            <span>{{ auth()->user()->name }}</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 cosmic-glow rounded-lg shadow-lg">
                            <a href="{{ route('dashboard', app()->getLocale()) }}" class="block px-4 py-2 text-sm hover:bg-white/5 rounded-t-lg transition-colors">
                                {{ __('auth.dashboard') }}
                            </a>
                            <form method="POST" action="{{ route('logout', app()->getLocale()) }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-white/5 rounded-b-lg transition-colors">
                                    {{ __('auth.logout') }}
                                </button>
                            </form>
                        </div>
                    </div>
                @endguest
            </div>
        </div>
    </nav>

    <!-- Main content -->
    <main class="pt-20">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="cosmic-glow mt-20 py-8">
        <div class="max-w-7xl mx-auto px-6 text-center">
            <p class="text-sm text-gray-300">
                {{ __('app.made_with_love') }}
            </p>
        </div>
    </footer>

    <script>
        // Generate stars
        function createStars() {
            const starField = document.getElementById('starField');
            const numStars = 100;

            for (let i = 0; i < numStars; i++) {
                const star = document.createElement('div');
                star.className = 'star';
                star.style.left = Math.random() * 100 + '%';
                star.style.top = Math.random() * 100 + '%';
                star.style.width = Math.random() * 3 + 1 + 'px';
                star.style.height = star.style.width;
                star.style.animationDelay = Math.random() * 2 + 's';
                starField.appendChild(star);
            }
        }

        createStars();
    </script>
</body>
</html>
