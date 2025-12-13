@extends('layouts.astral-app')

@section('title', 'Abonnements RoboTarget')

@section('content')
<div class="container mx-auto px-4 py-8">

    @if($currentSubscription)
        {{-- VUE POUR UTILISATEUR ABONNÉ --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">
                Mon Abonnement RoboTarget
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Gérez votre abonnement et consultez vos factures
            </p>
        </div>

        {{-- Plan actuel --}}
        <div class="bg-gradient-to-br from-blue-600 to-purple-600 rounded-lg shadow-lg p-8 mb-8 text-white">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <div class="text-sm opacity-80 mb-1">Votre plan actuel</div>
                    <h2 class="text-4xl font-bold flex items-center gap-3">
                        <span class="text-5xl">{{ $currentSubscription->getPlanBadge() }}</span>
                        {{ $currentSubscription->getPlanName() }}
                    </h2>
                </div>
                <div class="text-right">
                    <div class="text-4xl font-bold">{{ \App\Models\Subscription::PRICES[$currentSubscription->plan] }}€</div>
                    <div class="text-sm opacity-80">par mois</div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-6 border-t border-white/20">
                <div>
                    <div class="text-sm opacity-80">Crédits mensuels</div>
                    <div class="text-2xl font-bold">{{ $currentSubscription->credits_per_month }}</div>
                </div>
                <div>
                    <div class="text-sm opacity-80">Solde actuel</div>
                    <div class="text-2xl font-bold text-yellow-300">{{ $user->credits_balance }}</div>
                </div>
                <div>
                    <div class="text-sm opacity-80">Statut</div>
                    <div class="text-2xl font-bold capitalize">
                        @if($currentSubscription->trial_ends_at && $currentSubscription->trial_ends_at->isFuture())
                            🎁 Essai gratuit
                        @else
                            ✅ {{ $currentSubscription->status }}
                        @endif
                    </div>
                </div>
            </div>

            @if($currentSubscription->trial_ends_at && $currentSubscription->trial_ends_at->isFuture())
            <div class="mt-4 bg-white/10 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-sm">
                        Votre période d'essai gratuit se termine le {{ $currentSubscription->trial_ends_at->format('d/m/Y') }}
                    </span>
                </div>
            </div>
            @endif
        </div>

        {{-- Factures --}}
        @if(count($invoices) > 0)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow mb-8">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">📄 Factures</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Numéro</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Description</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Montant</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider">Statut</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach($invoices as $invoice)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-white">
                                {{ $invoice['id'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                {{ $invoice['date']->format('d/m/Y') }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-gray-400">
                                {{ $invoice['description'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 dark:text-white">
                                {{ $invoice['amount'] }}€
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                    ✓ Payée
                                </span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Changer de plan --}}
        <div class="mb-8">
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Changer de plan
            </h3>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Passez à un plan supérieur ou inférieur selon vos besoins
            </p>
        </div>
    @else
        {{-- VUE POUR UTILISATEUR NON ABONNÉ --}}
        <div class="mb-8 text-center">
            <h1 class="text-4xl font-bold text-gray-900 dark:text-white mb-4">
                Choisissez votre plan RoboTarget
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto">
                Accédez à notre télescope robotisé professionnel et capturez vos cibles favorites automatiquement,
                de jour comme de nuit, depuis n'importe où dans le monde
            </p>
        </div>

        {{-- Explication du système de crédits --}}
        <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-8 mb-8 border border-indigo-100 dark:border-gray-600">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                <span class="text-3xl">💡</span>
                Comment fonctionnent les crédits ?
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-blue-100 dark:bg-blue-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">⏱️</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2">1 crédit = 1 heure d'occupation</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                Le coût reflète le <strong>temps total d'occupation du télescope</strong>, incluant :
                            </p>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 ml-4">
                                <li>• Temps d'exposition de vos poses</li>
                                <li>• Overheads techniques (~30s/pose)</li>
                                <li>• Lecture capteur, sauvegarde, guidage</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-purple-100 dark:bg-purple-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">🎯</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Multiplicateurs qualité</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                Des options premium pour garantir les meilleures conditions :
                            </p>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1 ml-4">
                                <li>• Priorité élevée (×1.2 à ×3.0)</li>
                                <li>• Nuit noire sans lune (×2.0)</li>
                                <li>• Garantie netteté HFD (×1.5)</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-green-100 dark:bg-green-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">💰</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Remboursement automatique</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                <strong>Vous ne payez que pour les images réussies</strong>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Si votre target échoue (météo, problème technique, images floues avec garantie HFD),
                                vos crédits sont automatiquement remboursés dans les 24h.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white dark:bg-gray-800 rounded-lg p-6">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-orange-100 dark:bg-orange-900/30 rounded-lg flex items-center justify-center flex-shrink-0">
                            <span class="text-2xl">🔄</span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-900 dark:text-white mb-2">Renouvellement mensuel</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                Vos crédits sont renouvelés chaque 1er du mois.
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                ⚠️ Les crédits non utilisés ne sont pas reportés.
                                Planifiez vos observations pour en profiter au maximum !
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Exemple de calcul détaillé --}}
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-6 border-2 border-blue-200 dark:border-blue-900">
                <h4 class="font-bold text-gray-900 dark:text-white mb-3 flex items-center gap-2">
                    <span class="text-xl">📊</span>
                    Exemple de calcul détaillé
                </h4>

                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4 mb-4">
                    <div class="text-sm font-mono text-gray-700 dark:text-gray-300 space-y-2">
                        <div class="font-bold text-gray-900 dark:text-white mb-2">Target M31 - Configuration :</div>
                        <div class="ml-4 space-y-1">
                            <div>• 10 poses Luminance × 5min = 50min exposition + <span class="text-blue-600 dark:text-blue-400">5min overhead</span></div>
                            <div>• 10 poses Red × 3min = 30min exposition + <span class="text-blue-600 dark:text-blue-400">5min overhead</span></div>
                            <div>• 10 poses Green × 3min = 30min exposition + <span class="text-blue-600 dark:text-blue-400">5min overhead</span></div>
                            <div>• 10 poses Blue × 3min = 30min exposition + <span class="text-blue-600 dark:text-blue-400">5min overhead</span></div>
                        </div>

                        <div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-3">
                            <div class="font-semibold">Total occupation télescope :</div>
                            <div class="ml-4 mt-1">
                                <div>→ Exposition : <strong>140 minutes</strong></div>
                                <div>→ Overheads : <strong>20 minutes</strong> (40 poses × 30s)</div>
                                <div class="text-blue-600 dark:text-blue-400 font-bold mt-2">→ TOTAL : 160 minutes ≈ 3 heures = <strong class="text-lg">3 crédits de base</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="space-y-2 text-sm">
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <span class="text-gray-700 dark:text-gray-300">Sans options</span>
                        <span class="font-bold text-gray-900 dark:text-white">3 crédits</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded">
                        <span class="text-gray-700 dark:text-gray-300">Avec priorité normale (×1.2)</span>
                        <span class="font-bold text-gray-900 dark:text-white">4 crédits</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded border border-purple-200 dark:border-purple-800">
                        <span class="text-gray-700 dark:text-gray-300">Avec option nuit noire (×2.0)</span>
                        <span class="font-bold text-purple-600 dark:text-purple-400">6 crédits</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded border border-blue-200 dark:border-blue-800">
                        <span class="text-gray-700 dark:text-gray-300">Avec nuit noire + garantie HFD (×2.0 × ×1.5)</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">9 crédits</span>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">💡</span>
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            <strong>Conseil :</strong> Faire moins de poses longues est plus efficace que beaucoup de poses courtes.
                            Exemple : 10×5min coûte moins cher que 50×1min pour la même exposition totale !
                        </p>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Grille des plans --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        @foreach($plans as $plan)
        <div class="relative">
            {{-- Badge Popular --}}
            @if(isset($plan['popular']) && $plan['popular'])
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                <span class="bg-gradient-to-r from-yellow-400 to-orange-500 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">
                    ⭐ PLUS POPULAIRE
                </span>
            </div>
            @endif

            {{-- Badge Current Plan --}}
            @if($currentSubscription && $currentSubscription->plan === $plan['id'])
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                <span class="bg-green-500 text-white px-4 py-1 rounded-full text-sm font-bold shadow-lg">
                    ✓ PLAN ACTUEL
                </span>
            </div>
            @endif

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden border-2 transition-all
                {{ $currentSubscription && $currentSubscription->plan === $plan['id'] ? 'border-green-500' : 'border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500' }}
                {{ isset($plan['popular']) && $plan['popular'] ? 'lg:scale-105' : '' }} h-full flex flex-col">

                {{-- Header --}}
                <div class="p-6 {{ isset($plan['popular']) && $plan['popular'] ? 'bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-600' : '' }}">
                    <div class="text-center mb-4">
                        <div class="text-6xl mb-2">{{ $plan['badge'] }}</div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $plan['name'] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $plan['tagline'] }}</p>
                    </div>

                    <div class="text-center py-4">
                        <div class="flex items-end justify-center gap-1">
                            <span class="text-5xl font-black text-gray-900 dark:text-white">{{ $plan['price'] }}</span>
                            <span class="text-2xl text-gray-600 dark:text-gray-400 mb-2">€</span>
                        </div>
                        <div class="text-gray-600 dark:text-gray-400 text-sm mt-1">par mois</div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg p-3 text-center">
                        <div class="text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $plan['credits'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400">crédits par mois</div>
                        <div class="text-xs text-gray-500 dark:text-gray-500 mt-1">≈ {{ $plan['credits'] }}h d'observation</div>
                    </div>
                </div>

                {{-- Features --}}
                <div class="p-6 flex-1 flex flex-col">
                    <h4 class="font-bold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Fonctionnalités</h4>
                    <div class="space-y-2 mb-6">
                        @foreach($plan['features'] as $feature => $explanation)
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="w-full flex items-start gap-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded p-2 transition">
                                <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <div class="flex-1">
                                    <span class="text-gray-900 dark:text-white text-sm font-medium">{{ $feature }}</span>
                                    <svg class="w-4 h-4 inline-block ml-1 text-gray-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="open" x-collapse class="ml-7 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-2 rounded mt-1">
                                {{ $explanation }}
                            </div>
                        </div>
                        @endforeach
                    </div>

                    @if(count($plan['restrictions']) > 0)
                    <h4 class="font-bold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Limitations</h4>
                    <div class="space-y-2 mb-6">
                        @foreach($plan['restrictions'] as $restriction => $explanation)
                        <div x-data="{ open: false }" class="relative">
                            <button @click="open = !open" class="w-full flex items-start gap-2 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded p-2 transition">
                                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <div class="flex-1">
                                    <span class="text-gray-600 dark:text-gray-400 text-sm">{{ $restriction }}</span>
                                    <svg class="w-4 h-4 inline-block ml-1 text-gray-400" :class="{ 'rotate-180': open }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </div>
                            </button>
                            <div x-show="open" x-collapse class="ml-7 text-xs text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-700 p-2 rounded mt-1">
                                {{ $explanation }}
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <h4 class="font-bold text-gray-900 dark:text-white mb-3 text-sm uppercase tracking-wide">Inclus</h4>
                    <div class="space-y-1 mb-6">
                        @foreach($plan['included'] as $item)
                        <div class="flex items-center gap-2">
                            <div class="w-1.5 h-1.5 bg-blue-500 rounded-full"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">{{ $item }}</span>
                        </div>
                        @endforeach
                    </div>

                    {{-- CTA --}}
                    <div class="mt-auto">
                        <form method="POST" action="{{ route('subscriptions.subscribe', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $plan['id'] }}">

                            @if($currentSubscription && $currentSubscription->plan === $plan['id'])
                                <button type="button" disabled
                                        class="w-full py-3 rounded-lg font-bold text-white bg-green-500 cursor-not-allowed">
                                    ✓ Votre plan actuel
                                </button>
                            @else
                                <button type="submit"
                                        class="w-full py-3 rounded-lg font-bold text-white transition-all
                                               {{ isset($plan['popular']) && $plan['popular']
                                                  ? 'bg-gradient-to-r from-blue-600 to-purple-600 hover:from-blue-700 hover:to-purple-700 shadow-lg'
                                                  : 'bg-gray-700 hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-500' }}">
                                    @if($currentSubscription)
                                        Passer à {{ $plan['name'] }}
                                    @else
                                        Commencer avec {{ $plan['name'] }}
                                    @endif
                                </button>
                            @endif
                        </form>

                        @if(!$currentSubscription)
                        <div class="mt-2 text-center">
                            <span class="text-xs text-green-600 dark:text-green-400 font-medium">🎁 7 jours d'essai gratuit</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- FAQ Section --}}
    @if(!$currentSubscription)
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8 mb-8">
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-6 text-center">
            Questions fréquentes
        </h2>

        <div class="space-y-4 max-w-3xl mx-auto" x-data="{ openFaq: null }">
            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <button @click="openFaq = openFaq === 1 ? null : 1"
                        class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                    <span class="font-medium text-gray-900 dark:text-white">Puis-je changer de plan en cours de mois ?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 1 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openFaq === 1" x-collapse class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                    Oui, vous pouvez upgrader ou downgrader votre plan à tout moment. Le changement est immédiat et vous serez facturé au prorata.
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <button @click="openFaq = openFaq === 2 ? null : 2"
                        class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                    <span class="font-medium text-gray-900 dark:text-white">Les crédits non utilisés sont-ils reportés ?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 2 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openFaq === 2" x-collapse class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                    Non, les crédits sont renouvelés le 1er de chaque mois et ceux non utilisés ne sont pas reportés au mois suivant.
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <button @click="openFaq = openFaq === 3 ? null : 3"
                        class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                    <span class="font-medium text-gray-900 dark:text-white">Que se passe-t-il si mes images sont floues ou de mauvaise qualité ?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 3 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openFaq === 3" x-collapse class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                    Si vous avez choisi l'option garantie HFD et que les images ne respectent pas le seuil, vos crédits sont automatiquement remboursés.
                    En cas de problème technique du télescope, un remboursement complet est effectué.
                </div>
            </div>

            <div class="border border-gray-200 dark:border-gray-700 rounded-lg">
                <button @click="openFaq = openFaq === 4 ? null : 4"
                        class="w-full flex items-center justify-between p-4 text-left hover:bg-gray-50 dark:hover:bg-gray-700 rounded-lg transition">
                    <span class="font-medium text-gray-900 dark:text-white">Puis-je acheter des crédits supplémentaires ?</span>
                    <svg class="w-5 h-5 text-gray-500 transition-transform" :class="{ 'rotate-180': openFaq === 4 }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="openFaq === 4" x-collapse class="px-4 pb-4 text-gray-600 dark:text-gray-400">
                    Actuellement, l'achat de crédits supplémentaires n'est pas disponible. Nous recommandons de passer à un plan supérieur si vous avez besoin de plus d'heures d'observation.
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Retour --}}
    <div class="text-center">
        <a href="{{ route($currentSubscription ? 'robotarget.index' : 'dashboard', ['locale' => app()->getLocale()]) }}"
           class="inline-flex items-center gap-2 text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            {{ $currentSubscription ? 'Retour aux targets' : 'Retour au dashboard' }}
        </a>
    </div>
</div>
@endsection
