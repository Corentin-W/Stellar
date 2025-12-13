@extends('layouts.admin')

@section('title', 'Gestion des Plans')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gestion des Plans d'Abonnement</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Configurer les plans Stardust, Nebula et Quasar</p>
        </div>
        <a href="{{ route('admin.subscriptions.dashboard') }}"
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
            ← Retour au dashboard
        </a>
    </div>

    <!-- Configuration Stripe -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-6 mb-8">
        <div class="flex items-start gap-4">
            <div class="flex-shrink-0">
                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100">Configuration Stripe</h3>
                    <form action="{{ route('admin.subscriptions.create-stripe-plans') }}" method="POST" onsubmit="return confirm('Créer automatiquement les 3 plans (Stardust, Nebula, Quasar) dans Stripe ?')">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-gradient-to-r from-purple-600 to-blue-600 text-white rounded-lg hover:from-purple-700 hover:to-blue-700 transition font-medium text-sm shadow-md">
                            🚀 Créer les plans automatiquement
                        </button>
                    </form>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                    <div>
                        <span class="font-medium text-blue-800 dark:text-blue-200">Clé API :</span>
                        <span class="ml-2 text-blue-700 dark:text-blue-300">{{ $stripeConfig['stripe_key'] }}</span>
                    </div>
                    <div>
                        <span class="font-medium text-blue-800 dark:text-blue-200">Webhook Secret :</span>
                        <span class="ml-2 text-blue-700 dark:text-blue-300">{{ $stripeConfig['webhook_secret'] }}</span>
                    </div>
                </div>
                <div class="mt-3 space-y-2">
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        🎯 <strong>Méthode automatique (recommandée) :</strong> Cliquez sur "Créer les plans automatiquement" pour créer les 3 plans dans Stripe et récupérer automatiquement les Price IDs.
                    </p>
                    <p class="text-xs text-blue-700 dark:text-blue-300">
                        📝 <strong>Méthode manuelle :</strong> Créez les produits dans le <a href="https://dashboard.stripe.com/products" target="_blank" class="underline">Stripe Dashboard</a> et collez les Price IDs ci-dessous.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Plans Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-lg overflow-hidden border-2
            {{ $plan['id'] === 'nebula' ? 'border-purple-500 ring-2 ring-purple-200 dark:ring-purple-800' : 'border-gray-200 dark:border-gray-700' }}">

            <!-- Header -->
            <div class="p-6
                {{ $plan['id'] === 'stardust' ? 'bg-gradient-to-br from-blue-500 to-blue-600' : '' }}
                {{ $plan['id'] === 'nebula' ? 'bg-gradient-to-br from-purple-500 to-purple-600' : '' }}
                {{ $plan['id'] === 'quasar' ? 'bg-gradient-to-br from-yellow-500 to-orange-500' : '' }}">

                <div class="text-center">
                    <div class="text-4xl mb-2">
                        {{ $plan['id'] === 'stardust' ? '🌟' : '' }}
                        {{ $plan['id'] === 'nebula' ? '🌌' : '' }}
                        {{ $plan['id'] === 'quasar' ? '⚡' : '' }}
                    </div>
                    <h3 class="text-2xl font-bold text-white">{{ $plan['name'] }}</h3>
                    <div class="mt-4">
                        <span class="text-5xl font-bold text-white">{{ $plan['price'] }}€</span>
                        <span class="text-white text-opacity-80">/mois</span>
                    </div>
                    <div class="mt-2 text-white text-opacity-90">
                        {{ $plan['credits'] }} crédits/mois
                    </div>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6">
                <!-- Stats -->
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ $plan['subscribers'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">Abonnés</div>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 text-center">
                        <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ number_format($plan['mrr'], 0) }}€</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">MRR</div>
                    </div>
                </div>

                <!-- Stripe Price ID Form -->
                <form action="{{ route('admin.subscriptions.plans.update-stripe', $plan['id']) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            Stripe Price ID
                        </label>
                        <div class="relative">
                            <input type="text"
                                   name="stripe_price_id"
                                   value="{{ $stripeConfig[$plan['id']] }}"
                                   placeholder="price_xxxxxxxxxxxxxxxxxxxxx"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white
                                          focus:ring-2 focus:ring-blue-500 focus:border-transparent
                                          font-mono text-sm"
                                   pattern="price_[a-zA-Z0-9]+"
                                   required>
                            @if(str_starts_with($stripeConfig[$plan['id']], 'price_'))
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none">
                                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        @error('stripe_price_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                            Commence par <code class="bg-gray-100 dark:bg-gray-700 px-1 py-0.5 rounded">price_</code>
                        </p>
                    </div>

                    <button type="submit"
                            class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                        💾 Sauvegarder le Price ID
                    </button>
                </form>

                <!-- Info Section -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                    <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Configuration actuelle</h4>
                    <div class="space-y-2 text-sm text-gray-600 dark:text-gray-400">
                        <div class="flex justify-between">
                            <span>Prix mensuel :</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $plan['price'] }}€</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Crédits/mois :</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ $plan['credits'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span>Prix/crédit :</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ number_format($plan['price'] / $plan['credits'], 2) }}€</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Documentation -->
    <div class="mt-8 bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">📚 Comment configurer les Price IDs</h2>

        <div class="prose dark:prose-invert max-w-none">
            <ol class="space-y-4 text-gray-700 dark:text-gray-300">
                <li>
                    <strong>Créer les produits dans Stripe Dashboard :</strong>
                    <ul class="mt-2 space-y-1 text-sm">
                        <li>• Aller sur <a href="https://dashboard.stripe.com/products" target="_blank" class="text-blue-600 hover:underline">https://dashboard.stripe.com/products</a></li>
                        <li>• Cliquer sur "+ Ajouter un produit"</li>
                        <li>• Créer un produit pour chaque plan (Stardust, Nebula, Quasar)</li>
                        <li>• Type : "Récurrent", Fréquence : "Mensuelle"</li>
                    </ul>
                </li>
                <li>
                    <strong>Copier le Price ID généré :</strong>
                    <p class="text-sm mt-2">Après création, Stripe génère un Price ID (ex: <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">price_1AbCdEfGhIjKlMnO</code>)</p>
                </li>
                <li>
                    <strong>Coller le Price ID dans le formulaire ci-dessus et sauvegarder</strong>
                    <p class="text-sm mt-2">Le Price ID sera automatiquement ajouté au fichier <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">.env</code></p>
                </li>
                <li>
                    <strong>Tester le flux de paiement :</strong>
                    <p class="text-sm mt-2">Aller sur <code class="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded">/fr/subscriptions/choose</code> et tester l'abonnement en mode test</p>
                </li>
            </ol>
        </div>

        <div class="mt-6 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                </svg>
                <div>
                    <h4 class="font-semibold text-yellow-900 dark:text-yellow-200">Important</h4>
                    <p class="text-sm text-yellow-800 dark:text-yellow-300 mt-1">
                        Les modifications des Price IDs affectent immédiatement les nouveaux abonnements. Les abonnements existants conservent leur ancien Price ID jusqu'à renouvellement.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
