@extends('layouts.admin')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Gestion Stripe</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">Synchronisation des abonnements et configuration des prix</p>
        </div>

        <!-- Messages -->
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Statistiques -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 dark:text-gray-400">Total abonnements</div>
                <div class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $stats['total'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 dark:text-gray-400">Actifs</div>
                <div class="text-3xl font-bold text-green-600 mt-2">{{ $stats['active'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 dark:text-gray-400">Sans plan</div>
                <div class="text-3xl font-bold text-orange-600 mt-2">{{ $stats['without_plan'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm text-gray-600 dark:text-gray-400">Doublons</div>
                <div class="text-3xl font-bold text-red-600 mt-2">{{ $stats['duplicates'] }}</div>
            </div>
        </div>

        <!-- Configuration des Price IDs -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Configuration des Price IDs</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Mappez vos plans aux prix Stripe</p>
            </div>
            <div class="p-6">
                <form action="{{ route('admin.stripe.sync-prices') }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <!-- Stardust -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Stardust (29€/mois - 20 crédits)
                            </label>
                            <input type="text"
                                   name="stardust"
                                   value="{{ $configuredPrices['stardust'] }}"
                                   placeholder="price_xxxxxxxxxxxxx"
                                   class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-900 dark:text-white">
                        </div>

                        <!-- Nebula -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Nebula (59€/mois - 60 crédits)
                            </label>
                            <input type="text"
                                   name="nebula"
                                   value="{{ $configuredPrices['nebula'] }}"
                                   placeholder="price_xxxxxxxxxxxxx"
                                   class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-900 dark:text-white">
                        </div>

                        <!-- Quasar -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Quasar (119€/mois - 150 crédits)
                            </label>
                            <input type="text"
                                   name="quasar"
                                   value="{{ $configuredPrices['quasar'] }}"
                                   placeholder="price_xxxxxxxxxxxxx"
                                   class="w-full bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg px-4 py-2 text-gray-900 dark:text-white">
                        </div>
                    </div>

                    <div class="mt-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                            Enregistrer les Price IDs
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Prix disponibles sur Stripe -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Prix mensuels disponibles sur Stripe</h2>
                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Copiez les IDs ci-dessous dans la configuration</p>
            </div>
            <div class="p-6">
                @if(count($stripePrices) > 0)
                    <div class="space-y-3">
                        @foreach($stripePrices as $price)
                            <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                                <div>
                                    <div class="font-mono text-sm text-gray-900 dark:text-white">{{ $price['id'] }}</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        {{ $price['amount'] }} {{ $price['currency'] }}/mois
                                    </div>
                                </div>
                                <button onclick="copyToClipboard('{{ $price['id'] }}')"
                                        class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded text-sm">
                                    Copier
                                </button>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-600 dark:text-gray-400">Aucun prix mensuel trouvé sur Stripe</p>
                @endif
            </div>
        </div>

        <!-- Actions de synchronisation -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Actions de synchronisation</h2>
            </div>
            <div class="p-6 space-y-4">
                <!-- Nettoyer les doublons -->
                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Nettoyer les doublons</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Supprimer les abonnements en double ({{ $stats['duplicates'] }} trouvé(s))
                        </p>
                    </div>
                    <form action="{{ route('admin.stripe.clean-duplicates') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded"
                                onclick="return confirm('Êtes-vous sûr de vouloir supprimer les doublons ?')">
                            Nettoyer
                        </button>
                    </form>
                </div>

                <!-- Synchroniser les abonnements -->
                <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div>
                        <h3 class="font-semibold text-gray-900 dark:text-white">Synchroniser les abonnements</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Récupérer les plans depuis Stripe ({{ $stats['without_plan'] }} sans plan)
                        </p>
                    </div>
                    <form action="{{ route('admin.stripe.sync-subscriptions') }}" method="POST">
                        @csrf
                        <button type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                            Synchroniser
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        alert('Price ID copié: ' + text);
    });
}
</script>
@endsection
