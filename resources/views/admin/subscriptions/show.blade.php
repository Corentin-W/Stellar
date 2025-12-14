@extends('layouts.admin')

@section('title', 'Détails Abonnement')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Détails de l'Abonnement</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-2">{{ $subscription->user->name }} ({{ $subscription->user->email }})</p>
        </div>
        <a href="{{ route('admin.subscriptions.subscribers') }}"
           class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition">
            ← Retour à la liste
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne gauche - Infos principales -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Subscription Info -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Informations d'abonnement</h2>

                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Plan</label>
                        <div class="mt-2">
                            <span class="px-4 py-2 inline-block rounded-lg text-sm font-semibold
                                {{ $subscription->plan === 'stardust' ? 'bg-blue-100 text-blue-800' : '' }}
                                {{ $subscription->plan === 'nebula' ? 'bg-purple-100 text-purple-800' : '' }}
                                {{ $subscription->plan === 'quasar' ? 'bg-yellow-100 text-yellow-800' : '' }}">
                                {{ $subscription->getPlanName() }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Statut</label>
                        <div class="mt-2">
                            @if($subscription->stripe_status === 'active')
                                <span class="px-4 py-2 inline-block rounded-lg text-sm font-semibold bg-green-100 text-green-800">
                                    ✓ Actif
                                </span>
                            @elseif($subscription->stripe_status === 'trialing')
                                <span class="px-4 py-2 inline-block rounded-lg text-sm font-semibold bg-yellow-100 text-yellow-800">
                                    ⏱ Période d'essai
                                </span>
                            @elseif($subscription->stripe_status === 'past_due')
                                <span class="px-4 py-2 inline-block rounded-lg text-sm font-semibold bg-red-100 text-red-800">
                                    ⚠️ Paiement en retard
                                </span>
                            @else
                                <span class="px-4 py-2 inline-block rounded-lg text-sm font-semibold bg-gray-100 text-gray-800">
                                    {{ $subscription->stripe_status }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Prix mensuel</label>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ \App\Models\Subscription::PRICES[$subscription->plan] }}€/mois
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Crédits mensuels</label>
                        <div class="mt-2 text-2xl font-bold text-gray-900 dark:text-white">
                            {{ $subscription->credits_per_month }}
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Créé le</label>
                        <div class="mt-2 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->created_at->format('d/m/Y H:i') }}
                            <div class="text-xs text-gray-500 mt-1">{{ $subscription->created_at->diffForHumans() }}</div>
                        </div>
                    </div>

                    @if($subscription->trial_ends_at)
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Fin d'essai</label>
                        <div class="mt-2 text-sm text-gray-900 dark:text-white">
                            {{ $subscription->trial_ends_at->format('d/m/Y H:i') }}
                            @if($subscription->trial_ends_at->isFuture())
                                <div class="text-xs text-yellow-600 mt-1">{{ $subscription->trial_ends_at->diffForHumans() }}</div>
                            @endif
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Stripe ID</label>
                        <div class="mt-2 text-sm font-mono text-gray-900 dark:text-white">
                            {{ $subscription->stripe_id ?? 'N/A' }}
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Stripe Customer</label>
                        <div class="mt-2 text-sm font-mono text-gray-900 dark:text-white">
                            {{ $subscription->user->stripe_id ?? 'N/A' }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Historique des crédits -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-6">Historique des Crédits</h2>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-900">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Date</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Type</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Montant</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Description</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            @forelse($creditHistory as $transaction)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                    {{ \Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded
                                        {{ $transaction->type === 'purchase' ? 'bg-green-100 text-green-800' : '' }}
                                        {{ $transaction->type === 'usage' ? 'bg-red-100 text-red-800' : '' }}
                                        {{ $transaction->type === 'refund' ? 'bg-blue-100 text-blue-800' : '' }}
                                        {{ $transaction->type === 'admin_adjustment' ? 'bg-purple-100 text-purple-800' : '' }}">
                                        {{ $transaction->type }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right text-sm font-medium
                                    {{ $transaction->credits_amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $transaction->credits_amount > 0 ? '+' : '' }}{{ $transaction->credits_amount }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $transaction->description ?? 'N/A' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                    Aucune transaction
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Colonne droite - Actions -->
        <div class="space-y-6">
            <!-- Solde crédits -->
            <div class="bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl shadow-md p-6 text-white">
                <h3 class="text-lg font-semibold mb-2">Solde Crédits</h3>
                <div class="text-4xl font-bold">{{ $subscription->user->credits_balance }}</div>
                <div class="text-sm opacity-90 mt-2">sur {{ $subscription->credits_per_month }} mensuels</div>

                <div class="mt-4 bg-white bg-opacity-20 rounded-lg h-2">
                    <div class="bg-white h-2 rounded-lg"
                         style="width: {{ min(100, ($subscription->user->credits_balance / max($subscription->credits_per_month, 1)) * 100) }}%"></div>
                </div>
            </div>

            <!-- Ajuster les crédits -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Ajuster les Crédits</h3>

                <form action="{{ route('admin.subscriptions.adjust-credits', $subscription->user) }}" method="POST">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Montant
                            </label>
                            <input type="number"
                                   name="amount"
                                   placeholder="Ex: 50 ou -20"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                          bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                   required>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Nombre positif pour ajouter, négatif pour retirer
                            </p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Raison
                            </label>
                            <textarea name="reason"
                                      rows="3"
                                      placeholder="Raison de l'ajustement..."
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                             bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                      required></textarea>
                        </div>

                        <button type="submit"
                                class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium">
                            💾 Ajuster
                        </button>
                    </div>
                </form>
            </div>

            <!-- Annuler l'abonnement -->
            @if($subscription->status === 'active')
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6 border-2 border-red-200">
                <h3 class="text-lg font-bold text-red-600 mb-4">⚠️ Zone Dangereuse</h3>

                <form action="{{ route('admin.subscriptions.cancel', $subscription) }}" method="POST"
                      onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cet abonnement ?')">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                                Raison de l'annulation
                            </label>
                            <textarea name="reason"
                                      rows="3"
                                      placeholder="Raison de l'annulation..."
                                      class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg
                                             bg-white dark:bg-gray-700 text-gray-900 dark:text-white"
                                      required></textarea>
                        </div>

                        <button type="submit"
                                class="w-full px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition font-medium">
                            ❌ Annuler l'Abonnement
                        </button>

                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            L'abonnement sera annulé immédiatement dans Stripe et en local.
                        </p>
                    </div>
                </form>
            </div>
            @endif

            <!-- Liens Stripe -->
            <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md p-6">
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4">🔗 Liens Stripe</h3>

                <div class="space-y-2">
                    @if($subscription->user->stripe_id)
                    <a href="https://dashboard.stripe.com/customers/{{ $subscription->user->stripe_id }}"
                       target="_blank"
                       class="block px-4 py-2 text-center bg-purple-100 text-purple-700 rounded-lg hover:bg-purple-200 transition text-sm font-medium">
                        Voir le Client Stripe →
                    </a>
                    @endif

                    @if($subscription->stripe_id)
                    <a href="https://dashboard.stripe.com/subscriptions/{{ $subscription->stripe_id }}"
                       target="_blank"
                       class="block px-4 py-2 text-center bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 transition text-sm font-medium">
                        Voir l'Abonnement Stripe →
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
