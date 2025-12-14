@extends('layouts.admin')

@section('title', 'Modifier le Plan ' . $plan->name)

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <div class="mb-8">
        <a href="{{ route('admin.plans.index') }}" class="text-purple-400 hover:text-purple-300 mb-4 inline-flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
            Retour aux plans
        </a>
        <h1 class="text-3xl font-bold text-white mt-4">Modifier le Plan {{ $plan->name }}</h1>
        <p class="text-gray-400">Code: <span class="font-mono text-purple-400">{{ $plan->plan }}</span></p>
    </div>

    @if ($errors->any())
    <div class="bg-red-500/10 border border-red-500/50 text-red-400 px-4 py-3 rounded-lg mb-6">
        <ul class="list-disc list-inside">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <form action="{{ route('admin.plans.update', $plan->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-6 space-y-6">
            <!-- Nom du Plan -->
            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-2">
                    Nom du Plan
                </label>
                <input type="text"
                       name="name"
                       id="name"
                       value="{{ old('name', $plan->name) }}"
                       class="w-full bg-gray-900/50 border border-gray-600 text-white rounded-lg px-4 py-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                       required>
            </div>

            <!-- Prix -->
            <div>
                <label for="price" class="block text-sm font-medium text-gray-300 mb-2">
                    Prix Mensuel (€)
                </label>
                <div class="relative">
                    <input type="number"
                           name="price"
                           id="price"
                           step="0.01"
                           min="0"
                           value="{{ old('price', $plan->price) }}"
                           class="w-full bg-gray-900/50 border border-gray-600 text-white rounded-lg px-4 py-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           required>
                    <span class="absolute right-3 top-2 text-gray-400">EUR</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Prix de base avant réduction</p>
            </div>

            <!-- Crédits par Mois -->
            <div>
                <label for="credits_per_month" class="block text-sm font-medium text-gray-300 mb-2">
                    Crédits par Mois
                </label>
                <input type="number"
                       name="credits_per_month"
                       id="credits_per_month"
                       min="1"
                       value="{{ old('credits_per_month', $plan->credits_per_month) }}"
                       class="w-full bg-gray-900/50 border border-gray-600 text-white rounded-lg px-4 py-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                       required>
                <p class="text-xs text-gray-500 mt-1">Nombre de crédits attribués chaque mois</p>
            </div>

            <!-- Période d'Essai -->
            <div>
                <label for="trial_days" class="block text-sm font-medium text-gray-300 mb-2">
                    Période d'Essai (jours)
                </label>
                <input type="number"
                       name="trial_days"
                       id="trial_days"
                       min="0"
                       max="365"
                       value="{{ old('trial_days', $plan->trial_days) }}"
                       class="w-full bg-gray-900/50 border border-gray-600 text-white rounded-lg px-4 py-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                       required>
                <p class="text-xs text-gray-500 mt-1">Nombre de jours gratuits pour les nouveaux abonnés (0 = aucun)</p>
            </div>

            <!-- Réduction -->
            <div>
                <label for="discount_percentage" class="block text-sm font-medium text-gray-300 mb-2">
                    Réduction / Promotion (%)
                </label>
                <div class="relative">
                    <input type="number"
                           name="discount_percentage"
                           id="discount_percentage"
                           step="0.01"
                           min="0"
                           max="100"
                           value="{{ old('discount_percentage', $plan->discount_percentage) }}"
                           class="w-full bg-gray-900/50 border border-gray-600 text-white rounded-lg px-4 py-2 focus:border-purple-500 focus:ring-1 focus:ring-purple-500"
                           required>
                    <span class="absolute right-3 top-2 text-gray-400">%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Pourcentage de réduction appliqué (0 = aucune réduction)</p>

                <!-- Preview Prix Final -->
                <div class="mt-3 p-3 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-300">Prix final après réduction:</span>
                        <span id="final-price" class="text-lg font-bold text-purple-400">
                            {{ number_format($plan->getFinalPrice(), 2) }}€
                        </span>
                    </div>
                </div>
            </div>

            <!-- Stripe Price ID -->
            <div>
                <label for="stripe_price_id" class="block text-sm font-medium text-gray-300 mb-2">
                    Stripe Price ID (optionnel)
                </label>
                <input type="text"
                       name="stripe_price_id"
                       id="stripe_price_id"
                       value="{{ old('stripe_price_id', $plan->stripe_price_id) }}"
                       placeholder="price_xxxxxxxxxxxxx"
                       class="w-full bg-gray-900/50 border border-gray-600 text-white rounded-lg px-4 py-2 font-mono text-sm focus:border-purple-500 focus:ring-1 focus:ring-purple-500">
                <p class="text-xs text-gray-500 mt-1">ID du prix Stripe pour ce plan</p>
            </div>

            <!-- Plan Actif -->
            <div class="flex items-center gap-3 p-4 bg-gray-900/50 rounded-lg border border-gray-700">
                <input type="checkbox"
                       name="is_active"
                       id="is_active"
                       {{ old('is_active', $plan->is_active) ? 'checked' : '' }}
                       class="w-5 h-5 text-purple-600 bg-gray-700 border-gray-600 rounded focus:ring-purple-500">
                <label for="is_active" class="flex-1">
                    <span class="text-sm font-medium text-gray-300">Plan Actif</span>
                    <p class="text-xs text-gray-500 mt-0.5">Si désactivé, le plan n'apparaîtra pas dans les options d'abonnement</p>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center gap-4">
            <button type="submit"
                    class="flex-1 bg-purple-600 hover:bg-purple-700 text-white font-medium py-3 px-6 rounded-lg transition-colors">
                Enregistrer les modifications
            </button>
            <a href="{{ route('admin.plans.index') }}"
               class="px-6 py-3 text-gray-400 hover:text-white transition-colors">
                Annuler
            </a>
        </div>
    </form>
</div>

<script>
// Preview du prix final
document.addEventListener('DOMContentLoaded', function() {
    const priceInput = document.getElementById('price');
    const discountInput = document.getElementById('discount_percentage');
    const finalPriceDisplay = document.getElementById('final-price');

    function updateFinalPrice() {
        const price = parseFloat(priceInput.value) || 0;
        const discount = parseFloat(discountInput.value) || 0;
        const finalPrice = price * (1 - (discount / 100));
        finalPriceDisplay.textContent = finalPrice.toFixed(2) + '€';
    }

    priceInput.addEventListener('input', updateFinalPrice);
    discountInput.addEventListener('input', updateFinalPrice);
});
</script>
@endsection
