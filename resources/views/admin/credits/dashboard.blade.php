{{-- resources/views/admin/credits/dashboard.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Gestion des Crédits')

@section('content')
<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">💳 Gestion des Crédits</h1>
        <p class="text-gray-400">Administration du système de crédits et des transactions</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
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
            <div class="mt-4">
                <span class="text-blue-200 text-sm">Ce mois: {{ number_format($stats['monthly_revenue'], 2) }}€</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-green-500/20 to-green-600/20 rounded-xl p-6 border border-green-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-200 text-sm font-medium">Utilisateurs Actifs</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['total_users']) }}</p>
                </div>
                <div class="w-12 h-12 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-green-200 text-sm">Avec crédits achetés</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-purple-500/20 to-purple-600/20 rounded-xl p-6 border border-purple-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-200 text-sm font-medium">Crédits en Circulation</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['credits_in_circulation']) }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-purple-200 text-sm">Moyenne: {{ $stats['avg_credits_per_user'] }} par utilisateur</span>
            </div>
        </div>

        <div class="bg-gradient-to-br from-yellow-500/20 to-yellow-600/20 rounded-xl p-6 border border-yellow-500/30">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-200 text-sm font-medium">Crédits Consommés</p>
                    <p class="text-white text-2xl font-bold">{{ number_format($stats['credits_consumed']) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-yellow-200 text-sm">Utilisation totale</span>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <a href="{{ route('admin.credits.packages') }}"
           class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg p-4 transition-colors group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium">Gérer Packages</h3>
                    <p class="text-gray-400 text-sm">Créer et modifier</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.credits.promotions') }}"
           class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg p-4 transition-colors group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium">Promotions</h3>
                    <p class="text-gray-400 text-sm">Codes promo</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.credits.users') }}"
           class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg p-4 transition-colors group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium">Utilisateurs</h3>
                    <p class="text-gray-400 text-sm">Gestion crédits</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.credits.reports') }}"
           class="bg-white/5 hover:bg-white/10 border border-white/10 rounded-lg p-4 transition-colors group">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <h3 class="text-white font-medium">Rapports</h3>
                    <p class="text-gray-400 text-sm">Analytics</p>
                </div>
            </div>
        </a>
    </div>

    <!-- Recent Transactions -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Transactions Récentes</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($recentTransactions as $transaction)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-{{ $transaction->type === 'purchase' ? 'green' : 'red' }}-500/20 flex items-center justify-center">
                                @if($transaction->type === 'purchase')
                                    <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                    </svg>
                                @endif
                            </div>
                            <div>
                                <div class="text-white font-medium">{{ $transaction->user->name }}</div>
                                <div class="text-gray-400 text-sm">{{ $transaction->formatted_type }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-medium {{ $transaction->type === 'purchase' ? 'text-green-400' : 'text-red-400' }}">
                                {{ $transaction->type === 'purchase' ? '+' : '' }}{{ number_format($transaction->credits_amount) }}
                            </div>
                            <div class="text-gray-400 text-sm">{{ $transaction->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-4">
                    <a href="{{ route('admin.credits.transactions') }}" class="text-blue-400 hover:text-blue-300 text-sm">
                        Voir toutes les transactions →
                    </a>
                </div>
            </div>
        </div>

        <!-- Top Buyers -->
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/10">
                <h3 class="text-lg font-semibold text-white">Top Acheteurs</h3>
            </div>
            <div class="p-6">
                <div class="space-y-4">
                    @foreach($topBuyers as $buyer)
                    <div class="flex items-center justify-between p-3 bg-white/5 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-white font-semibold text-xs">
                                {{ substr($buyer->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="text-white font-medium">{{ $buyer->name }}</div>
                                <div class="text-gray-400 text-sm">{{ Str::limit($buyer->email, 20) }}</div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-white font-medium">{{ number_format($buyer->total_credits_purchased) }}</div>
                            <div class="text-gray-400 text-sm">crédits achetés</div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Package Performance -->
    <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/10">
            <h3 class="text-lg font-semibold text-white">Performance des Packages</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Package</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Prix</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Ventes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Revenus</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/10">
                    @foreach($packageStats as $stat)
                    <tr class="hover:bg-white/5">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    @if($stat['package']->is_featured)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            ⭐ Populaire
                                        </span>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-white">{{ $stat['package']->name }}</div>
                                    <div class="text-sm text-gray-400">{{ $stat['package']->credits_amount }} crédits</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-white">{{ number_format($stat['package']->price_euros, 2) }}€</td>
                        <td class="px-6 py-4 text-white">{{ $stat['sales_count'] }}</td>
                        <td class="px-6 py-4 text-white">{{ number_format($stat['revenue'], 2) }}€</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $stat['package']->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $stat['package']->is_active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

{{-- resources/views/admin/credits/packages/index.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Packages de Crédits')

@section('content')
<div class="container mx-auto px-4 py-8">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white mb-2">📦 Packages de Crédits</h1>
            <p class="text-gray-400">Gestion des offres de crédits disponibles</p>
        </div>
        <a href="{{ route('admin.credits.packages.create') }}"
           class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">
            + Nouveau Package
        </a>
    </div>

    <!-- Packages Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        @foreach($packages as $package)
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden {{ $package->is_featured ? 'ring-2 ring-yellow-500/50' : '' }}">
            <div class="p-6">
                <!-- Package Header -->
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-white">{{ $package->name }}</h3>
                    <div class="flex gap-2">
                        @if($package->is_featured)
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                ⭐ Populaire
                            </span>
                        @endif
                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $package->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $package->is_active ? 'Actif' : 'Inactif' }}
                        </span>
                    </div>
                </div>

                <!-- Package Details -->
                <p class="text-gray-400 text-sm mb-4">{{ $package->description }}</p>

                <div class="space-y-2 mb-6">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Prix:</span>
                        <span class="text-white font-bold">{{ number_format($package->price_euros, 2) }}€</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Crédits de base:</span>
                        <span class="text-white">{{ number_format($package->credits_amount) }}</span>
                    </div>
                    @if($package->bonus_credits > 0)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Crédits bonus:</span>
                        <span class="text-green-400">+{{ number_format($package->bonus_credits) }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-400">Total crédits:</span>
                        <span class="text-white font-bold">{{ number_format($package->total_credits) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Valeur/crédit:</span>
                        <span class="text-white">{{ number_format($package->credit_value, 3) }}€</span>
                    </div>
                </div>

                <!-- Package Stats -->
                <div class="bg-white/5 rounded-lg p-3 mb-4">
                    <div class="text-sm text-gray-400 mb-1">Ventes:
                        <span class="text-white font-medium">{{ $package->transactions()->where('type', 'purchase')->count() }}</span>
                    </div>
                    <div class="text-sm text-gray-400">Revenus:
                        <span class="text-white font-medium">{{ number_format($package->transactions()->where('type', 'purchase')->count() * $package->price_euros, 2) }}€</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex gap-2">
                    <a href="{{ route('admin.credits.packages.edit', $package) }}"
                       class="flex-1 px-3 py-2 bg-blue-500/20 text-blue-400 rounded-lg text-center hover:bg-blue-500/30 transition-colors">
                        Modifier
                    </a>
                    <button onclick="togglePackageStatus({{ $package->id }}, {{ $package->is_active ? 'false' : 'true' }})"
                            class="px-3 py-2 {{ $package->is_active ? 'bg-red-500/20 text-red-400 hover:bg-red-500/30' : 'bg-green-500/20 text-green-400 hover:bg-green-500/30' }} rounded-lg transition-colors">
                        {{ $package->is_active ? 'Désactiver' : 'Activer' }}
                    </button>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex justify-center">
        {{ $packages->links() }}
    </div>
</div>

<script>
function togglePackageStatus(packageId, newStatus) {
    if (confirm(`Êtes-vous sûr de vouloir ${newStatus === 'true' ? 'activer' : 'désactiver'} ce package ?`)) {
        fetch(`/admin/credits/packages/${packageId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                is_active: newStatus === 'true'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Erreur lors de la modification');
            }
        });
    }
}
</script>
@endsection

{{-- resources/views/admin/credits/packages/create.blade.php --}}
@extends('layouts.astral-app')

@section('title', 'Admin - Créer un Package')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">

    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">📦 Créer un Package</h1>
        <p class="text-gray-400">Ajouter un nouveau package de crédits</p>
    </div>

    <form action="{{ route('admin.credits.packages.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Basic Info -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Informations de Base</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Nom du Package</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Pack Explorer">
                    @error('name')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Description</label>
                    <textarea name="description" rows="3"
                              class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                              placeholder="Description du package...">{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Pricing -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Tarification</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-white mb-2">Nombre de Crédits</label>
                    <input type="number" name="credits_amount" value="{{ old('credits_amount') }}" required min="1" max="10000"
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="100">
                    @error('credits_amount')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Prix (€)</label>
                    <input type="number" name="price_euros" value="{{ old('price_euros') }}" required min="0.01" max="1000" step="0.01"
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="9.99">
                    @error('price_euros')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Crédits Bonus</label>
                    <input type="number" name="bonus_credits" value="{{ old('bonus_credits', 0) }}" min="0" max="1000"
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    @error('bonus_credits')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-white mb-2">Ordre d'Affichage</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0"
                           class="w-full bg-white/10 border border-white/20 rounded-lg px-3 py-2 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="0">
                    @error('sort_order')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Options -->
        <div class="bg-white/5 border border-white/10 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Options</h3>

            <div class="space-y-4">
                <div class="flex items-center">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label class="ml-2 block text-sm text-white">Package actif</label>
                </div>

                <div class="flex items-center">
                    <input type="checkbox" name="is_featured" value="1" {{ old('is_featured') ? 'checked' : '' }}
                           class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                    <label class="ml-2 block text-sm text-white">Package populaire (mis en avant)</label>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <button type="submit"
                    class="flex-1 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-lg font-semibold hover:scale-105 transition-transform">
                Créer le Package
            </button>
            <a href="{{ route('admin.credits.packages') }}"
               class="px-6 py-3 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                Annuler
            </a>
        </div>
    </form>
</div>
@endsection
