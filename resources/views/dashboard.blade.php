@extends('layouts.app')

@section('title', __('auth.dashboard'))

@section('content')
<div class="min-h-screen px-6 py-12">
    <div class="max-w-7xl mx-auto">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-4xl md:text-6xl font-bold mb-4 font-mono">
                <span class="bg-gradient-to-r from-purple-400 to-pink-400 bg-clip-text text-transparent">
                    {{ __('auth.dashboard') }}
                </span>
            </h1>
            <p class="text-xl text-gray-300">
                {{ __('app.welcome') }}, {{ auth()->user()->name }} ! 🚀
            </p>
        </div>

        <!-- Stats Cards -->
        <div class="grid md:grid-cols-3 gap-6 mb-12">
            <div class="cosmic-glow p-6 rounded-2xl text-center">
                <div class="w-12 h-12 rounded-full nebula-gradient planet-glow mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-purple-300 mb-2">Observations</h3>
                <p class="text-3xl font-bold text-white">42</p>
            </div>

            <div class="cosmic-glow p-6 rounded-2xl text-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-pink-400 to-purple-500 planet-glow mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-pink-300 mb-2">Amis</h3>
                <p class="text-3xl font-bold text-white">12</p>
            </div>

            <div class="cosmic-glow p-6 rounded-2xl text-center">
                <div class="w-12 h-12 rounded-full bg-gradient-to-r from-blue-400 to-cyan-500 planet-glow mx-auto mb-4 flex items-center justify-center">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-blue-300 mb-2">Défis</h3>
                <p class="text-3xl font-bold text-white">8</p>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="cosmic-glow p-8 rounded-2xl">
            <h2 class="text-2xl font-bold mb-6 text-purple-300">Activité récente</h2>
            <div class="space-y-4">
                <div class="flex items-center space-x-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 flex items-center justify-center">
                        🌟
                    </div>
                    <div>
                        <p class="text-white">Observation de Jupiter</p>
                        <p class="text-sm text-gray-400">Il y a 2 heures</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-purple-400 to-pink-500 flex items-center justify-center">
                        🌙
                    </div>
                    <div>
                        <p class="text-white">Phase lunaire ajoutée</p>
                        <p class="text-sm text-gray-400">Hier</p>
                    </div>
                </div>

                <div class="flex items-center space-x-4 p-4 bg-white/5 rounded-xl">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-400 to-cyan-500 flex items-center justify-center">
                        🚀
                    </div>
                    <div>
                        <p class="text-white">Nouveau membre de la communauté</p>
                        <p class="text-sm text-gray-400">Il y a 3 jours</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Floating elements -->
<div class="fixed top-1/4 left-10 w-6 h-6 rounded-full bg-gradient-to-r from-yellow-400 to-orange-500 opacity-30 animate-pulse"></div>
<div class="fixed bottom-1/4 right-10 w-8 h-8 rounded-full bg-gradient-to-r from-purple-400 to-pink-500 opacity-20 animate-bounce" style="animation-delay: 2s;"></div>
@endsection
