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
        <div class="mb-12 text-center relative">
            <div class="absolute inset-0 bg-gradient-to-r from-blue-600/10 via-purple-600/10 to-pink-600/10 blur-3xl -z-10"></div>
            <h1 class="text-5xl md:text-6xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 mb-6 animate-gradient">
                Choisissez votre plan RoboTarget
            </h1>
            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-300 max-w-3xl mx-auto leading-relaxed">
                Accédez à notre télescope robotisé professionnel et capturez vos cibles favorites <span class="font-semibold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600">automatiquement</span>,
                de jour comme de nuit, depuis n'importe où dans le monde
            </p>
            <div class="mt-6 flex items-center justify-center gap-8 text-sm text-gray-500 dark:text-gray-400">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>7 jours d'essai gratuit</span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>Annulation à tout moment</span>
                </div>
            </div>
        </div>
    @endif

    {{-- Grille des plans --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-16">
        @foreach($plans as $plan)
        <div class="relative group">
            {{-- Badge Popular --}}
            @if(isset($plan['popular']) && $plan['popular'])
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                <span class="bg-gradient-to-r from-yellow-400 via-orange-500 to-pink-500 text-white px-6 py-2 rounded-full text-sm font-bold shadow-2xl animate-pulse">
                    ⭐ PLUS POPULAIRE
                </span>
            </div>
            @endif

            {{-- Badge Current Plan --}}
            @if($currentSubscription && $currentSubscription->plan === $plan['id'])
            <div class="absolute -top-4 left-1/2 transform -translate-x-1/2 z-10">
                <span class="bg-gradient-to-r from-green-500 to-emerald-600 text-white px-6 py-2 rounded-full text-sm font-bold shadow-2xl">
                    ✓ PLAN ACTUEL
                </span>
            </div>
            @endif

            <div class="relative bg-white dark:bg-gray-800 rounded-2xl shadow-xl overflow-hidden border-2 transition-all duration-300 h-full flex flex-col
                {{ $currentSubscription && $currentSubscription->plan === $plan['id'] ? 'border-green-500 scale-105' : 'border-gray-200 dark:border-gray-700' }}
                {{ isset($plan['popular']) && $plan['popular'] ? 'lg:scale-105 border-purple-500 dark:border-purple-600' : '' }}
                group-hover:shadow-2xl group-hover:-translate-y-2">

                {{-- Gradient overlay pour effet moderne --}}
                <div class="absolute inset-0 bg-gradient-to-br {{ isset($plan['popular']) && $plan['popular'] ? 'from-blue-600/5 via-purple-600/5 to-pink-600/5' : 'from-gray-900/0 to-gray-900/5 dark:from-white/0 dark:to-white/5' }} pointer-events-none"></div>

                {{-- Header --}}
                <div class="relative p-8 {{ isset($plan['popular']) && $plan['popular'] ? 'bg-gradient-to-br from-blue-50 via-purple-50 to-pink-50 dark:from-gray-700 dark:via-purple-900/20 dark:to-gray-600' : 'bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-750' }}">
                    <div class="text-center mb-6">
                        <div class="text-7xl mb-3 transform transition-transform group-hover:scale-110">{{ $plan['badge'] }}</div>
                        <h3 class="text-3xl font-black text-gray-900 dark:text-white mb-2">{{ $plan['name'] }}</h3>
                        <p class="text-sm text-gray-600 dark:text-gray-300 font-medium">{{ $plan['tagline'] }}</p>
                    </div>

                    <div class="text-center py-6">
                        <div class="flex items-baseline justify-center gap-1">
                            <span class="text-6xl font-black {{ isset($plan['popular']) && $plan['popular'] ? 'text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600' : 'text-gray-900 dark:text-white' }}">{{ $plan['price'] }}</span>
                            <span class="text-3xl text-gray-600 dark:text-gray-400 font-bold">€</span>
                        </div>
                        <div class="text-gray-500 dark:text-gray-400 text-sm mt-2 font-medium">par mois</div>
                    </div>

                    <div class="relative bg-gradient-to-br from-white to-gray-50 dark:from-gray-800 dark:to-gray-900 rounded-xl p-4 text-center shadow-inner border border-gray-200 dark:border-gray-700">
                        <div class="text-4xl font-black {{ isset($plan['popular']) && $plan['popular'] ? 'text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600' : 'text-blue-600 dark:text-blue-400' }}">{{ $plan['credits'] }}</div>
                        <div class="text-xs text-gray-600 dark:text-gray-400 font-semibold mt-1">crédits par mois</div>
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
                    <div class="mt-auto relative">
                        <form method="POST" action="{{ route('subscriptions.subscribe', ['locale' => app()->getLocale()]) }}">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $plan['id'] }}">

                            @if($currentSubscription && $currentSubscription->plan === $plan['id'])
                                <button type="button" disabled
                                        class="relative w-full py-4 rounded-xl font-bold text-white bg-gradient-to-r from-green-500 to-emerald-600 cursor-not-allowed shadow-lg overflow-hidden">
                                    <span class="relative z-10">✓ Votre plan actuel</span>
                                </button>
                            @else
                                <button type="submit"
                                        class="relative w-full py-4 rounded-xl font-bold text-white transition-all duration-300 shadow-lg hover:shadow-2xl transform hover:scale-105 overflow-hidden group
                                               {{ isset($plan['popular']) && $plan['popular']
                                                  ? 'bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600 hover:from-blue-700 hover:via-purple-700 hover:to-pink-700'
                                                  : 'bg-gradient-to-r from-gray-700 to-gray-800 hover:from-gray-600 hover:to-gray-700 dark:from-gray-600 dark:to-gray-700 dark:hover:from-gray-500 dark:hover:to-gray-600' }}">
                                    <span class="relative z-10 flex items-center justify-center gap-2">
                                        @if($currentSubscription)
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                            </svg>
                                            Passer à {{ $plan['name'] }}
                                        @else
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                            </svg>
                                            Commencer avec {{ $plan['name'] }}
                                        @endif
                                    </span>
                                    <div class="absolute inset-0 bg-white/20 transform translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
                                </button>
                            @endif
                        </form>

                        @if(!$currentSubscription)
                        <div class="mt-3 text-center">
                            <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-bold {{ isset($plan['popular']) && $plan['popular'] ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white' : 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' }}">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 4a2 2 0 012-2h6a2 2 0 012 2v14l-5-2.5L5 18V4z"/>
                                </svg>
                                7 jours d'essai gratuit
                            </span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Explication du système de crédits (APRÈS les plans) --}}
    @if(!$currentSubscription)
    <div class="relative mb-16">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-500/10 via-purple-500/10 to-pink-500/10 blur-3xl"></div>
        <div class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-8 md:p-12 border border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-2xl animate-bounce">
                        💡
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600">
                        Comment fonctionnent les crédits ?
                    </h2>
                </div>
                <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                    Un système simple et transparent : vous ne payez que pour le temps d'occupation du télescope
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                <div class="group bg-gradient-to-br from-blue-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-blue-100 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">⏱️</span>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2 text-lg">1 crédit = 1 heure d'occupation</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                Le coût reflète le <strong>temps total d'occupation du télescope</strong>, incluant :
                            </p>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1.5 ml-4">
                                <li class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                    Temps d'exposition de vos poses
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                    Overheads techniques (~30s/pose)
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-blue-500"></div>
                                    Lecture capteur, sauvegarde, guidage
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="group bg-gradient-to-br from-purple-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-purple-100 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">🎯</span>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2 text-lg">Multiplicateurs qualité</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                Des options premium pour garantir les meilleures conditions :
                            </p>
                            <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1.5 ml-4">
                                <li class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-purple-500"></div>
                                    Priorité élevée (×1.2 à ×3.0)
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-purple-500"></div>
                                    Nuit noire sans lune (×2.0)
                                </li>
                                <li class="flex items-center gap-2">
                                    <div class="w-1.5 h-1.5 rounded-full bg-purple-500"></div>
                                    Garantie netteté HFD (×1.5)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="group bg-gradient-to-br from-green-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-green-100 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-green-500 to-green-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">💰</span>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2 text-lg">Remboursement automatique</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
                                <strong>Vous ne payez que pour les images réussies</strong>
                            </p>
                            <p class="text-xs text-gray-600 dark:text-gray-400">
                                Si votre target échoue (météo, problème technique, images floues avec garantie HFD),
                                vos crédits sont automatiquement remboursés dans les 24h.
                            </p>
                        </div>
                    </div>
                </div>

                <div class="group bg-gradient-to-br from-orange-50 to-white dark:from-gray-700 dark:to-gray-800 rounded-2xl p-6 border border-orange-100 dark:border-gray-600 hover:shadow-xl transition-all duration-300 hover:-translate-y-1">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg group-hover:scale-110 transition-transform">
                            <span class="text-3xl">🔄</span>
                        </div>
                        <div>
                            <h3 class="font-black text-gray-900 dark:text-white mb-2 text-lg">Renouvellement mensuel</h3>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mb-2">
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

            {{-- Exemple de calcul compact --}}
            <div class="bg-gradient-to-br from-gray-50 to-white dark:from-gray-800 dark:to-gray-900 rounded-2xl p-6 border-2 border-blue-200 dark:border-blue-900/50 shadow-inner">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-lg">
                        📊
                    </div>
                    <h4 class="font-black text-gray-900 dark:text-white text-lg">
                        Exemple de calcul pour M31
                    </h4>
                </div>

                <div class="grid md:grid-cols-2 gap-6">
                    <div class="bg-white/50 dark:bg-gray-700/50 rounded-xl p-4 backdrop-blur">
                        <div class="text-xs font-mono text-gray-700 dark:text-gray-300 space-y-2">
                            <div class="font-bold text-gray-900 dark:text-white mb-2">Configuration :</div>
                            <div class="space-y-1 text-xs">
                                <div>• 10 poses L × 5min = 55min</div>
                                <div>• 10 poses R × 3min = 35min</div>
                                <div>• 10 poses G × 3min = 35min</div>
                                <div>• 10 poses B × 3min = 35min</div>
                            </div>
                            <div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2">
                                <div class="font-bold text-blue-600 dark:text-blue-400">
                                    Total : 160min ≈ 3h = <span class="text-lg">3 crédits</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between p-2.5 bg-white dark:bg-gray-700 rounded-lg">
                            <span class="text-gray-700 dark:text-gray-300 text-xs">Sans options</span>
                            <span class="font-bold text-gray-900 dark:text-white">3 crédits</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                            <span class="text-gray-700 dark:text-gray-300 text-xs">Avec nuit noire (×2.0)</span>
                            <span class="font-bold text-purple-600 dark:text-purple-400">6 crédits</span>
                        </div>
                        <div class="flex items-center justify-between p-2.5 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                            <span class="text-gray-700 dark:text-gray-300 text-xs">Nuit noire + HFD (×3.0)</span>
                            <span class="font-bold text-blue-600 dark:text-blue-400">9 crédits</span>
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 bg-gradient-to-r from-yellow-50 to-orange-50 dark:from-yellow-900/20 dark:to-orange-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg">
                    <div class="flex items-start gap-2">
                        <span class="text-lg">💡</span>
                        <p class="text-xs text-gray-700 dark:text-gray-300">
                            <strong>Conseil :</strong> Faire moins de poses longues est plus efficace que beaucoup de poses courtes.
                            Exemple : 10×5min coûte moins cher que 50×1min !
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- FAQ Section --}}
    @if(!$currentSubscription)
    <div class="relative mb-16">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/5 via-purple-500/5 to-pink-500/5 blur-3xl"></div>
        <div class="relative bg-white/80 dark:bg-gray-800/80 backdrop-blur-xl rounded-3xl p-8 md:p-12 border border-gray-200/50 dark:border-gray-700/50 shadow-2xl">
            <div class="text-center mb-10">
                <div class="inline-flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center text-2xl">
                        ❓
                    </div>
                    <h2 class="text-3xl md:text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-blue-600 via-purple-600 to-pink-600">
                        Questions fréquentes
                    </h2>
                </div>
            </div>

            <div class="space-y-4 max-w-3xl mx-auto" x-data="{ openFaq: null }">
                <div class="group bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden transition-all duration-300 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-xl">
                    <button @click="openFaq = openFaq === 1 ? null : 1"
                            class="w-full flex items-center justify-between p-5 text-left transition-colors">
                        <span class="font-bold text-gray-900 dark:text-white text-base">Puis-je changer de plan en cours de mois ?</span>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center flex-shrink-0 ml-4 transition-transform" :class="{ 'rotate-180': openFaq === 1 }">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 1" x-collapse class="px-5 pb-5">
                        <div class="bg-gradient-to-br from-blue-50 to-purple-50 dark:from-gray-700 dark:to-gray-750 rounded-xl p-4 text-gray-700 dark:text-gray-300">
                            Oui, vous pouvez upgrader ou downgrader votre plan à tout moment. Le changement est immédiat et vous serez facturé au prorata.
                        </div>
                    </div>
                </div>

                <div class="group bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden transition-all duration-300 hover:border-purple-500 dark:hover:border-purple-500 hover:shadow-xl">
                    <button @click="openFaq = openFaq === 2 ? null : 2"
                            class="w-full flex items-center justify-between p-5 text-left transition-colors">
                        <span class="font-bold text-gray-900 dark:text-white text-base">Les crédits non utilisés sont-ils reportés ?</span>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center flex-shrink-0 ml-4 transition-transform" :class="{ 'rotate-180': openFaq === 2 }">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 2" x-collapse class="px-5 pb-5">
                        <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-gray-700 dark:to-gray-750 rounded-xl p-4 text-gray-700 dark:text-gray-300">
                            Non, les crédits sont renouvelés le 1er de chaque mois et ceux non utilisés ne sont pas reportés au mois suivant.
                        </div>
                    </div>
                </div>

                <div class="group bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden transition-all duration-300 hover:border-green-500 dark:hover:border-green-500 hover:shadow-xl">
                    <button @click="openFaq = openFaq === 3 ? null : 3"
                            class="w-full flex items-center justify-between p-5 text-left transition-colors">
                        <span class="font-bold text-gray-900 dark:text-white text-base">Que se passe-t-il si mes images sont floues ou de mauvaise qualité ?</span>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center flex-shrink-0 ml-4 transition-transform" :class="{ 'rotate-180': openFaq === 3 }">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 3" x-collapse class="px-5 pb-5">
                        <div class="bg-gradient-to-br from-green-50 to-emerald-50 dark:from-gray-700 dark:to-gray-750 rounded-xl p-4 text-gray-700 dark:text-gray-300">
                            Si vous avez choisi l'option garantie HFD et que les images ne respectent pas le seuil, vos crédits sont automatiquement remboursés.
                            En cas de problème technique du télescope, un remboursement complet est effectué.
                        </div>
                    </div>
                </div>

                <div class="group bg-white dark:bg-gray-800 border-2 border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden transition-all duration-300 hover:border-orange-500 dark:hover:border-orange-500 hover:shadow-xl">
                    <button @click="openFaq = openFaq === 4 ? null : 4"
                            class="w-full flex items-center justify-between p-5 text-left transition-colors">
                        <span class="font-bold text-gray-900 dark:text-white text-base">Puis-je acheter des crédits supplémentaires ?</span>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center flex-shrink-0 ml-4 transition-transform" :class="{ 'rotate-180': openFaq === 4 }">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </button>
                    <div x-show="openFaq === 4" x-collapse class="px-5 pb-5">
                        <div class="bg-gradient-to-br from-orange-50 to-red-50 dark:from-gray-700 dark:to-gray-750 rounded-xl p-4 text-gray-700 dark:text-gray-300">
                            Actuellement, l'achat de crédits supplémentaires n'est pas disponible. Nous recommandons de passer à un plan supérieur si vous avez besoin de plus d'heures d'observation.
                        </div>
                    </div>
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
