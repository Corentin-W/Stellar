{{-- resources/views/admin/credits/dashboard.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Gestion des Crédits')

@section('content')
<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between flex-wrap gap-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">💳 Gestion des Crédits</h1>
                <p class="text-gray-400">Administration du système de crédits et des transactions</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.credits.packages.index') }}"
                   class="btn-primary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Gérer les Packages
                </a>
                <a href="{{ route('admin.credits.promotions.index') }}"
                   class="btn-secondary">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 010-2h4z"/>
                    </svg>
                    Gérer les Promos
                </a>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Revenus Total -->
        <div class="bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-xl p-6 border border-blue-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-200 text-sm font-medium">Revenus Total</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['total_revenue'], 2) }}€</p>
                </div>
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Utilisateurs Actifs -->
        <div class="bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-xl p-6 border border-green-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-200 text-sm font-medium">Utilisateurs Actifs</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Crédits en Circulation -->
        <div class="bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-xl p-6 border border-purple-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-200 text-sm font-medium">Crédits en Circulation</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['total_credits_in_circulation']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Transactions ce mois -->
        <div class="bg-gradient-to-br from-orange-500/20 to-orange-600/20 rounded-xl p-6 border border-orange-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-200 text-sm font-medium">Transactions ce mois</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['monthly_transactions']) }}</p>
                </div>
                <div class="w-12 h-12 bg-orange-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">

        <!-- Top Acheteurs -->
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">Top Acheteurs</h3>
                <a href="{{ route('admin.credits.users.index') }}"
                   class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                    Voir tous →
                </a>
            </div>

            @if(!empty($topBuyers) && count($topBuyers) > 0)
                <div class="space-y-4">
                    @foreach($topBuyers as $buyer)
                        <div class="flex items-center justify-between p-4 bg-white/5 rounded-lg">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-semibold">
                                    {{ substr($buyer['name'] ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-white font-medium">{{ $buyer['name'] ?? 'Utilisateur' }}</p>
                                    <p class="text-gray-400 text-sm">{{ $buyer['purchases_count'] }} achats</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-white font-semibold">{{ number_format($buyer['total_spent'], 2) }}€</p>
                                <p class="text-gray-400 text-sm">{{ number_format($buyer['total_credits_purchased']) }} crédits</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                    <p class="text-gray-400 mt-2">Aucun achat pour le moment</p>
                </div>
            @endif
        </div>

        <!-- Performance des Packages -->
        <div class="bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-semibold text-white">Performance des Packages</h3>
                <a href="{{ route('admin.credits.packages.index') }}"
                   class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                    Gérer →
                </a>
            </div>

            @if(!empty($packageStats) && count($packageStats) > 0)
                <div class="space-y-4">
                    @foreach($packageStats as $package)
                        <div class="p-4 bg-white/5 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-white font-medium">{{ $package['name'] }}</h4>
                                <span class="text-green-400 font-semibold">{{ number_format($package['total_revenue'], 2) }}€</span>
                            </div>
                            <div class="grid grid-cols-3 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-400">Ventes:</span>
                                    <span class="text-white font-medium ml-1">{{ $package['sales_count'] }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Prix:</span>
                                    <span class="text-white font-medium ml-1">{{ number_format($package['price_euros'], 2) }}€</span>
                                </div>
                                <div>
                                    <span class="text-gray-400">Crédits:</span>
                                    <span class="text-white font-medium ml-1">{{ number_format($package['total_credits']) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <p class="text-gray-400 mt-2">Aucun package configuré</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Transactions Récentes -->
    <div class="mt-8 bg-white/5 backdrop-blur-sm rounded-xl p-6 border border-white/10">
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-xl font-semibold text-white">Transactions Récentes</h3>
            <a href="{{ route('admin.credits.transactions') }}"
               class="text-blue-400 hover:text-blue-300 text-sm font-medium">
                Voir toutes →
            </a>
        </div>

        @if($recentTransactions->count() > 0)
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="border-b border-white/10">
                        <tr class="text-left">
                            <th class="pb-3 text-gray-400 font-medium">Utilisateur</th>
                            <th class="pb-3 text-gray-400 font-medium">Type</th>
                            <th class="pb-3 text-gray-400 font-medium">Montant</th>
                            <th class="pb-3 text-gray-400 font-medium">Package</th>
                            <th class="pb-3 text-gray-400 font-medium">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($recentTransactions as $transaction)
                            <tr class="hover:bg-white/5 transition-colors">
                                <td class="py-3">
                                    <div class="flex items-center space-x-3">
                                        <div class="w-8 h-8 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white text-sm font-semibold">
                                            {{ substr($transaction->user->name ?? 'A', 0, 1) }}
                                        </div>
                                        <div>
                                            <p class="text-white font-medium">{{ $transaction->user->name ?? 'Utilisateur' }}</p>
                                            <p class="text-gray-400 text-xs">{{ $transaction->user->email ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-medium rounded-full
                                        @if($transaction->type === 'purchase') bg-green-100 text-green-800
                                        @elseif($transaction->type === 'usage') bg-blue-100 text-blue-800
                                        @elseif($transaction->type === 'refund') bg-red-100 text-red-800
                                        @elseif($transaction->type === 'bonus') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        {{ ucfirst($transaction->type) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="text-white font-medium
                                        @if($transaction->credits_amount > 0) text-green-400
                                        @else text-red-400
                                        @endif">
                                        {{ $transaction->credits_amount > 0 ? '+' : '' }}{{ number_format($transaction->credits_amount) }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="text-gray-300">
                                        {{ $transaction->creditPackage->name ?? 'N/A' }}
                                    </span>
                                </td>
                                <td class="py-3">
                                    <span class="text-gray-400 text-sm">
                                        {{ $transaction->created_at->format('d/m/Y H:i') }}
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-8">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-gray-400 mt-2">Aucune transaction récente</p>
            </div>
        @endif
    </div>

    <!-- Liens rapides -->
    <div class="mt-8 grid grid-cols-1 md:grid-cols-3 gap-6">
        <a href="{{ route('admin.credits.packages.create') }}"
           class="block p-6 bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-xl border border-green-500/30 hover:from-green-500/30 hover:to-green-600/30 transition-all">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-white font-semibold">Créer un Package</h4>
                    <p class="text-green-200 text-sm">Nouveau package de crédits</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.credits.promotions.create') }}"
           class="block p-6 bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-xl border border-purple-500/30 hover:from-purple-500/30 hover:to-purple-600/30 transition-all">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 110 2h-1v12a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 010-2h4z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-white font-semibold">Créer une Promotion</h4>
                    <p class="text-purple-200 text-sm">Code promo ou réduction</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.credits.reports') }}"
           class="block p-6 bg-gradient-to-br from-blue-500/20 to-blue-600/20 rounded-xl border border-blue-500/30 hover:from-blue-500/30 hover:to-blue-600/30 transition-all">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-blue-500/20 rounded-lg flex items-center justify-center mr-4">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h4 class="text-white font-semibold">Rapports</h4>
                    <p class="text-blue-200 text-sm">Analytics et statistiques</p>
                </div>
            </div>
        </a>
    </div>
</div>
@endsection

@push('styles')
<style>
.btn-primary {
    @apply inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-purple-600 text-white font-medium rounded-lg hover:from-blue-600 hover:to-purple-700 transition-all;
}

.btn-secondary {
    @apply inline-flex items-center gap-2 px-4 py-2 bg-white/10 text-white font-medium rounded-lg hover:bg-white/20 transition-all backdrop-blur-sm border border-white/20;
}
</style>
@endpush
