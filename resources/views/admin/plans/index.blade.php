@extends('layouts.admin')

@section('title', 'Gestion des Plans')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-white mb-2">Gestion des Plans Commerciaux</h1>
        <p class="text-gray-400">Configurez les prix, crédits, promotions et périodes d'essai pour chaque plan.</p>
    </div>

    @if(session('success'))
    <div class="bg-green-500/10 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-6">
        {{ session('success') }}
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        @foreach($plans as $plan)
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-lg p-6 hover:border-purple-500/50 transition-all">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-2xl font-bold text-white">{{ $plan->name }}</h3>
                    <span class="text-sm text-gray-400 uppercase">{{ $plan->plan }}</span>
                </div>
                @if(!$plan->is_active)
                <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded">Inactif</span>
                @endif
            </div>

            <div class="space-y-3 mb-6">
                <!-- Prix -->
                <div class="flex items-baseline gap-2">
                    @if($plan->hasDiscount())
                        <span class="text-3xl font-bold text-green-400">{{ number_format($plan->getFinalPrice(), 2) }}€</span>
                        <span class="text-lg text-gray-500 line-through">{{ number_format($plan->price, 2) }}€</span>
                        <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded">-{{ $plan->discount_percentage }}%</span>
                    @else
                        <span class="text-3xl font-bold text-white">{{ number_format($plan->price, 2) }}€</span>
                    @endif
                    <span class="text-gray-400">/mois</span>
                </div>

                <!-- Crédits -->
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <span class="text-gray-300">{{ $plan->credits_per_month }} crédits/mois</span>
                </div>

                <!-- Période d'essai -->
                <div class="flex items-center gap-2 text-sm">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    @if($plan->trial_days > 0)
                        <span class="text-blue-300">{{ $plan->trial_days }} jours gratuits</span>
                    @else
                        <span class="text-gray-500">Aucune période d'essai</span>
                    @endif
                </div>

                <!-- Stripe Price ID -->
                @if($plan->stripe_price_id)
                <div class="flex items-center gap-2 text-xs">
                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                    </svg>
                    <span class="text-gray-500 font-mono">{{ $plan->stripe_price_id }}</span>
                </div>
                @endif
            </div>

            <a href="{{ route('admin.plans.edit', $plan->id) }}"
               class="block w-full text-center bg-purple-600 hover:bg-purple-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                Modifier
            </a>
        </div>
        @endforeach
    </div>

    <div class="mt-8 p-4 bg-blue-500/10 border border-blue-500/50 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div class="text-sm text-blue-300">
                <p class="font-medium mb-1">Important</p>
                <p class="text-blue-200/80">Les modifications de prix ne s'appliquent qu'aux nouveaux abonnements. Les abonnements existants conservent leur prix actuel jusqu'au renouvellement.</p>
            </div>
        </div>
    </div>
</div>
@endsection
