@extends('layouts.admin')

@section('title', 'Abonnés')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Utilisateurs Abonnés</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Liste de tous les utilisateurs avec un abonnement actif</p>
        </div>
        <a href="{{ route('admin.subscriptions.dashboard') }}"
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
            ← Retour au dashboard
        </a>
    </div>

    <!-- Filters -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-4 mb-6">
        <form method="GET" class="flex gap-4 items-end">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Rechercher
                </label>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Nom, email..."
                       class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                              bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Plan
                </label>
                <select name="plan"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                               bg-white dark:bg-gray-700 text-gray-900 dark:text-white">
                    <option value="">Tous</option>
                    <option value="stardust" {{ request('plan') === 'stardust' ? 'selected' : '' }}>Stardust</option>
                    <option value="nebula" {{ request('plan') === 'nebula' ? 'selected' : '' }}>Nebula</option>
                    <option value="quasar" {{ request('plan') === 'quasar' ? 'selected' : '' }}>Quasar</option>
                </select>
            </div>
            <button type="submit"
                    class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                Filtrer
            </button>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-900">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Utilisateur
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Plan
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Crédits
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Statut
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Inscrit depuis
                        </th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                    @forelse($subscribers as $user)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">
                                        {{ $user->name }}
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $user->email }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-3 py-1 text-xs font-semibold rounded-full
                                {{ $user->subscription->plan === 'stardust' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200' : '' }}
                                {{ $user->subscription->plan === 'nebula' ? 'bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200' : '' }}
                                {{ $user->subscription->plan === 'quasar' ? 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200' : '' }}">
                                {{ ucfirst($user->subscription->plan) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900 dark:text-white">
                                <div class="font-medium">{{ $user->credits_balance }} / {{ $user->subscription->credits_per_month }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    {{ round(($user->credits_balance / max($user->subscription->credits_per_month, 1)) * 100) }}% restants
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($user->subscription->stripe_status === 'active')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ✓ Actif
                                </span>
                            @elseif($user->subscription->stripe_status === 'trialing')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                    ⏱ Essai
                                </span>
                            @elseif($user->subscription->stripe_status === 'past_due')
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200">
                                    ⚠️ Retard
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200">
                                    {{ $user->subscription->stripe_status }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            <div>{{ $user->subscription->created_at->format('d/m/Y') }}</div>
                            <div class="text-xs">{{ $user->subscription->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                            <a href="{{ route('admin.subscriptions.show', $user->subscription) }}"
                               class="text-blue-600 hover:text-blue-900 dark:hover:text-blue-400">
                                Détails
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                </svg>
                                <p class="text-lg font-medium">Aucun abonné trouvé</p>
                                <p class="text-sm mt-1">Aucun utilisateur ne correspond à vos critères de recherche</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($subscribers->hasPages())
        <div class="bg-gray-50 dark:bg-gray-900 px-6 py-4 border-t border-gray-200 dark:border-gray-700">
            {{ $subscribers->links() }}
        </div>
        @endif
    </div>

    <!-- Stats Summary -->
    <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Total abonnés</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $subscribers->total() }}</div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">MRR Total</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ number_format($subscribers->sum(function($user) { return \App\Models\Subscription::PRICES[$user->subscription->plan] ?? 0; }), 0) }}€
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">ARR Projeté</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                {{ number_format($subscribers->sum(function($user) { return \App\Models\Subscription::PRICES[$user->subscription->plan] ?? 0; }) * 12, 0) }}€
            </div>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">Crédits en circulation</div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ number_format($subscribers->sum('credits_balance'), 0) }}</div>
        </div>
    </div>
</div>
@endsection
