@php
use App\Models\Subscription;
@endphp

@extends('layouts.astral-app')

@section('title', 'Gérer mon abonnement')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">
            Gérer mon abonnement RoboTarget
        </h1>

        @if(session('error'))
        <div class="mb-6 bg-red-100 dark:bg-red-900/20 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded">
            {{ session('error') }}
        </div>
        @endif

        @if($subscription)
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
                        {{ $subscription->getPlanBadge() }} {{ $subscription->getPlanName() }}
                    </h2>
                    <p class="text-gray-600 dark:text-gray-400 mt-1">
                        Status: <span class="font-semibold">{{ ucfirst($subscription->status) }}</span>
                    </p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-gray-900 dark:text-white">
                        @if($subscription->plan && isset(Subscription::PRICES[$subscription->plan]))
                            {{ Subscription::PRICES[$subscription->plan] }}€
                        @else
                            <span class="text-orange-600">À synchroniser</span>
                        @endif
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">par mois</div>
                </div>
            </div>

            <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Crédits mensuels</h3>
                <div class="flex items-center justify-between">
                    <span class="text-gray-600 dark:text-gray-400">Allocation mensuelle</span>
                    <span class="font-bold text-gray-900 dark:text-white">{{ $subscription->credits_per_month }} crédits</span>
                </div>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-gray-600 dark:text-gray-400">Solde actuel</span>
                    <span class="font-bold text-blue-600">{{ $user->credits_balance }} crédits</span>
                </div>
            </div>

            <div class="mt-6 flex gap-4">
                <a href="{{ route('subscriptions.choose', ['locale' => app()->getLocale()]) }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Changer de plan
                </a>
                <a href="{{ route('robotarget.index', ['locale' => app()->getLocale()]) }}"
                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                    Retour aux targets
                </a>
            </div>
        </div>
        @else
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-8 text-center">
            <div class="text-6xl mb-4">📭</div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">
                Aucun abonnement actif
            </h2>
            <p class="text-gray-600 dark:text-gray-400 mb-6">
                Choisissez un plan pour commencer à utiliser RoboTarget
            </p>
            <a href="{{ route('subscriptions.choose', ['locale' => app()->getLocale()]) }}"
               class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-lg font-medium transition-colors">
                Voir les plans disponibles
            </a>
        </div>
        @endif
    </div>
</div>
@endsection
