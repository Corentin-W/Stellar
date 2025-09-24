{{-- resources/views/credits/history.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Historique des Crédits - AstroSphere')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-900 via-purple-900/50 to-slate-900 relative overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(circle_at_50%_50%,rgba(120,119,198,0.1),transparent_50%)]"></div>

    <div class="relative z-10 max-w-6xl mx-auto px-6 py-12">

        <!-- Header -->
        <div class="flex items-center justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-white">Historique des Crédits</h1>
                <p class="text-white/60 mt-2">Suivez toutes vos transactions de crédits</p>
            </div>
            <a href="{{ route('credits.shop') }}"
               class="px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 hover:from-blue-600 hover:to-purple-700 text-white font-semibold rounded-xl transition-all">
                Acheter des crédits
            </a>
        </div>

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Solde Actuel</p>
                        <p class="text-2xl font-bold text-white">{{ number_format($stats['current_balance']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Acheté</p>
                        <p class="text-2xl font-bold text-green-400">{{ number_format($stats['total_purchased']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Total Utilisé</p>
                        <p class="text-2xl font-bold text-orange-400">{{ number_format($stats['total_used']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-orange-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-white/60 text-sm">Transactions</p>
                        <p class="text-2xl font-bold text-purple-400">{{ number_format($stats['transactions_count']) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Historique des transactions -->
        <div class="bg-white/5 backdrop-blur-sm border border-white/10 rounded-2xl overflow-hidden">
            <div class="p-6 border-b border-white/10">
                <h3 class="text-xl font-bold text-white">Historique des Transactions</h3>
            </div>

            @if($transactions->count() > 0)
            <div class="divide-y divide-white/10">
                @foreach($transactions as $transaction)
                <div class="p-6 hover:bg-white/5 transition-colors">
                    <div class="flex items-center justify-between">

                        <!-- Informations principales -->
                        <div class="flex items-center gap-4">
                            <!-- Icône du type de transaction -->
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center
                                        {{ $transaction->is_addition ? 'bg-green-500/20' : 'bg-red-500/20' }}">
                                @if($transaction->type === 'purchase')
                                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                                    </svg>
                                @elseif($transaction->type === 'usage')
                                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                @elseif($transaction->type === 'bonus')
                                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                @else
                                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                @endif
