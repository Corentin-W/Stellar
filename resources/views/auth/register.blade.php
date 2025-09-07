@extends('layouts.app')

@section('title', __('auth.register'))

@section('content')
<div class="min-h-screen flex items-center justify-center px-6 py-12">
    <div class="max-w-md w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <div class="w-16 h-16 rounded-full nebula-gradient planet-glow mx-auto mb-4 flex items-center justify-center">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold font-mono mb-2">
                <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    {{ __('auth.register') }}
                </span>
            </h1>
            <p class="text-gray-400">{{ __('app.join_community') }}</p>
        </div>

        <!-- Social Register Buttons -->
        <div class="space-y-3 mb-6">
            <a href="{{ route('social.login', ['provider' => 'google', 'locale' => app()->getLocale()]) }}"
               class="w-full flex items-center justify-center px-4 py-3 border border-gray-600 rounded-xl hover:bg-white/5 transition-colors cosmic-glow">
                <svg class="w-5 h-5 mr-3" viewBox="0 0 24 24">
                    <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                    <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                    <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                    <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                </svg>
                {{ __('auth.sign_in_with') }} Google
            </a>

            <a href="{{ route('social.login', ['provider' => 'github', 'locale' => app()->getLocale()]) }}"
               class="w-full flex items-center justify-center px-4 py-3 border border-gray-600 rounded-xl hover:bg-white/5 transition-colors cosmic-glow">
                <svg class="w-5 h-5 mr-3 fill-current" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
                {{ __('auth.sign_in_with') }} GitHub
            </a>
        </div>

        <!-- Divider -->
        <div class="relative mb-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-600"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-2 bg-transparent text-gray-400">{{ __('auth.or_continue_with') }}</span>
            </div>
        </div>

        <!-- Register Form -->
        <form method="POST" action="{{ route('register', app()->getLocale()) }}" class="space-y-6">
            @csrf

            <!-- Name -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    {{ __('auth.name') }}
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       required
                       autofocus
                       class="w-full px-4 py-3 bg-transparent border border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors cosmic-glow text-white placeholder-gray-400"
                       placeholder="Jean Dupont">
                @error('name')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-2">
                    {{ __('auth.email') }}
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       required
                       class="w-full px-4 py-3 bg-transparent border border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors cosmic-glow text-white placeholder-gray-400"
                       placeholder="nom@exemple.com">
                @error('email')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    {{ __('auth.password') }}
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       required
                       class="w-full px-4 py-3 bg-transparent border border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors cosmic-glow text-white placeholder-gray-400"
                       placeholder="••••••••">
                @error('password')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <!-- Confirm Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                    {{ __('auth.confirm_password') }}
                </label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       required
                       class="w-full px-4 py-3 bg-transparent border border-gray-600 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-colors cosmic-glow text-white placeholder-gray-400"
                       placeholder="••••••••">
            </div>

            <!-- Submit Button -->
            <button type="submit"
                    class="w-full py-3 px-4 rounded-xl nebula-gradient hover:shadow-2xl transition-all transform hover:scale-105 font-semibold text-lg">
                {{ __('auth.register') }}
            </button>
        </form>

        <!-- Login Link -->
        <div class="text-center mt-6">
            <p class="text-gray-400">
                {{ __('auth.already_registered') }}
                <a href="{{ route('login', app()->getLocale()) }}" class="text-purple-400 hover:text-purple-300 transition-colors font-medium">
                    {{ __('auth.login') }}
                </a>
            </p>
        </div>

        <!-- Back to Home -->
        <div class="text-center mt-4">
            <a href="{{ url(app()->getLocale()) }}" class="text-sm text-gray-400 hover:text-gray-300 transition-colors">
                ← {{ __('app.home') }}
            </a>
        </div>
    </div>
</div>

<!-- Floating elements -->
<div class="fixed top-1/4 left-10 w-10 h-10 rounded-full bg-gradient-to-r from-purple-400 to-blue-500 opacity-20 animate-pulse"></div>
<div class="fixed bottom-1/4 right-10 w-8 h-8 rounded-full bg-gradient-to-r from-pink-400 to-yellow-400 opacity-30 animate-bounce" style="animation-delay: 1.5s;"></div>
<div class="fixed top-1/3 right-20 w-6 h-6 rounded-full bg-gradient-to-r from-green-400 to-cyan-500 opacity-25 animate-ping" style="animation-delay: 3s;"></div>
@endsection
