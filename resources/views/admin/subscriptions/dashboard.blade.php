@extends('layouts.admin')

@section('title', 'Dashboard Abonnements')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Dashboard Abonnements</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Vue d'ensemble des abonnements RoboTarget</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.subscriptions.plans') }}"
               class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                ⚙️ Gérer les plans
            </a>
            <form action="{{ route('admin.subscriptions.sync-stripe') }}" method="POST" class="inline">
                @csrf
                <button type="submit"
                        class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition">
                    🔄 Sync Stripe
                </button>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Active Subscriptions -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Abonnements Actifs</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['active_subscriptions'] }}</p>
                </div>
                <div class="bg-green-100 dark:bg-green-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                +{{ $stats['new_this_month'] }} ce mois
            </p>
        </div>

        <!-- MRR -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">MRR</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ number_format($stats['mrr'], 0) }}€</p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Monthly Recurring Revenue
            </p>
        </div>

        <!-- Churn Rate -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-red-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Taux d'Annulation</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['churn_rate'] }}%</p>
                </div>
                <div class="bg-red-100 dark:bg-red-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-red-600 dark:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                {{ $stats['cancelled_this_month'] }} annulations ce mois
            </p>
        </div>

        <!-- Trial -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-600 dark:text-gray-400">En Essai</p>
                    <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['trial_subscriptions'] }}</p>
                </div>
                <div class="bg-yellow-100 dark:bg-yellow-900 p-3 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                7 jours d'essai gratuit
            </p>
        </div>
    </div>

    <!-- Distribution des plans -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Plans Distribution -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Distribution des Plans</h2>

            <div class="space-y-4">
                <!-- Stardust -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            🌟 Stardust
                        </span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $stats['stardust_count'] }} ({{ $stats['active_subscriptions'] > 0 ? round(($stats['stardust_count'] / $stats['active_subscriptions']) * 100) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-blue-500 h-2 rounded-full"
                             style="width: {{ $stats['active_subscriptions'] > 0 ? ($stats['stardust_count'] / $stats['active_subscriptions']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Nebula -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            🌌 Nebula
                        </span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $stats['nebula_count'] }} ({{ $stats['active_subscriptions'] > 0 ? round(($stats['nebula_count'] / $stats['active_subscriptions']) * 100) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-purple-500 h-2 rounded-full"
                             style="width: {{ $stats['active_subscriptions'] > 0 ? ($stats['nebula_count'] / $stats['active_subscriptions']) * 100 : 0 }}%"></div>
                    </div>
                </div>

                <!-- Quasar -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                            ⚡ Quasar
                        </span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">
                            {{ $stats['quasar_count'] }} ({{ $stats['active_subscriptions'] > 0 ? round(($stats['quasar_count'] / $stats['active_subscriptions']) * 100) : 0 }}%)
                        </span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-yellow-500 h-2 rounded-full"
                             style="width: {{ $stats['active_subscriptions'] > 0 ? ($stats['quasar_count'] / $stats['active_subscriptions']) * 100 : 0 }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Graphique MRR -->
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Évolution MRR (12 mois)</h2>

            <div class="space-y-3">
                @foreach(array_slice($monthlyRevenue, -6) as $data)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-20">{{ $data['month'] }}</span>
                    <div class="flex-1 mx-4">
                        <div class="w-full bg-gray-200 rounded-full h-6">
                            <div class="bg-gradient-to-r from-blue-500 to-purple-600 h-6 rounded-full flex items-center justify-end px-2"
                                 style="width: {{ max(10, ($data['mrr'] / max($stats['mrr'], 1)) * 100) }}%">
                                <span class="text-xs text-white font-medium">{{ number_format($data['mrr'], 0) }}€</span>
                            </div>
                        </div>
                    </div>
                    <span class="text-sm text-gray-600 dark:text-gray-400 w-16 text-right">{{ $data['subscribers'] }} sub</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Abonnements récents -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">Abonnements Récents</h2>
            <a href="{{ route('admin.subscriptions.subscribers') }}"
               class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                Voir tous →
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Utilisateur</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Crédits</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Créé le</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($recentSubscriptions as $subscription)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $subscription->user->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $subscription->user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-semibold rounded-full
                                {{ $subscription->plan === 'stardust' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $subscription->plan === 'nebula' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $subscription->plan === 'quasar' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ ucfirst($subscription->plan) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                            {{ $subscription->credits_per_month }} /mois
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($subscription->stripe_status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Actif</span>
                            @elseif($subscription->stripe_status === 'trialing')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Essai</span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">{{ $subscription->stripe_status }}</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $subscription->created_at->diffForHumans() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('admin.subscriptions.show', $subscription) }}"
                               class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                                Détails
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            Aucun abonnement
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
