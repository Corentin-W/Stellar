@extends('layouts.astral-app')

@section('title', 'Créer une Target RoboTarget')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                🎯 Nouvelle Target RoboTarget
            </h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">
                Créez une nouvelle cible pour observation automatisée
            </p>
        </div>

        {{-- Alpine.js Component --}}
        <div x-data="RoboTargetManager()" x-init="init()">
            {{-- Progress Steps (visible après step 0) --}}
            <div class="mb-8" x-show="currentStep > 0" x-transition>
                <div class="flex items-center justify-between">
                    {{-- Step 1: Target Info --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(1)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition"
                                :class="currentStep >= 1 ? 'border-blue-600 bg-blue-100' : 'border-gray-300 bg-gray-100'">
                                <span class="font-semibold">1</span>
                            </div>
                            <span class="mt-2 text-sm font-medium">Cible</span>
                        </button>
                    </div>

                    <div class="flex-1 h-1 mx-2"
                        :class="currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-300'"></div>

                    {{-- Step 2: Constraints --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(2)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition"
                                :class="currentStep >= 2 ? 'border-blue-600 bg-blue-100' : 'border-gray-300 bg-gray-100'">
                                <span class="font-semibold">2</span>
                            </div>
                            <span class="mt-2 text-sm font-medium">Contraintes</span>
                        </button>
                    </div>

                    <div class="flex-1 h-1 mx-2"
                        :class="currentStep >= 3 ? 'bg-blue-600' : 'bg-gray-300'"></div>

                    {{-- Step 3: Shots --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(3)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition"
                                :class="currentStep >= 3 ? 'border-blue-600 bg-blue-100' : 'border-gray-300 bg-gray-100'">
                                <span class="font-semibold">3</span>
                            </div>
                            <span class="mt-2 text-sm font-medium">Acquisitions</span>
                        </button>
                    </div>

                    <div class="flex-1 h-1 mx-2"
                        :class="currentStep >= 4 ? 'bg-blue-600' : 'bg-gray-300'"></div>

                    {{-- Step 4: Review --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(4)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 4 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 transition"
                                :class="currentStep >= 4 ? 'border-blue-600 bg-blue-100' : 'border-gray-300 bg-gray-100'">
                                <span class="font-semibold">4</span>
                            </div>
                            <span class="mt-2 text-sm font-medium">Résumé</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Error Message --}}
            <div x-show="errorMessage"
                x-transition
                class="mb-6 bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-800 font-medium" x-text="errorMessage"></span>
                </div>
            </div>

            {{-- Success Message --}}
            <div x-show="successMessage"
                x-transition
                class="mb-6 bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 font-medium" x-text="successMessage"></span>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    {{-- Step 0: Welcome & Catalog --}}
                    <div x-show="currentStep === 0" x-transition>
                        @include('dashboard.robotarget.partials.step-welcome')
                    </div>

                    {{-- Step 1: Target Information --}}
                    <div x-show="currentStep === 1" x-transition>
                        @include('dashboard.robotarget.partials.step-target-info')
                    </div>

                    {{-- Step 2: Constraints --}}
                    <div x-show="currentStep === 2" x-transition>
                        @include('dashboard.robotarget.partials.step-constraints')
                    </div>

                    {{-- Step 3: Shots --}}
                    <div x-show="currentStep === 3" x-transition>
                        @include('dashboard.robotarget.partials.step-shots')
                    </div>

                    {{-- Step 4: Review --}}
                    <div x-show="currentStep === 4" x-transition>
                        @include('dashboard.robotarget.partials.step-review')
                    </div>
                </div>

                {{-- Navigation Buttons --}}
                <div class="bg-gray-50 dark:bg-gray-700 px-6 py-4 flex justify-between" x-show="currentStep > 0">
                    <button
                        @click="prevStep()"
                        x-show="currentStep > 1"
                        class="px-6 py-2 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition"
                    >
                        ← Précédent
                    </button>

                    <div class="flex-1"></div>

                    <button
                        @click="nextStep()"
                        x-show="currentStep < 4"
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
                    >
                        Suivant →
                    </button>

                    <button
                        @click="submitTarget()"
                        x-show="currentStep === 4"
                        :disabled="isLoading"
                        class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!isLoading">✓ Créer la Target</span>
                        <span x-show="isLoading">⏳ Création...</span>
                    </button>
                </div>
            </div>

            {{-- Pricing Sidebar (visible from step 3) --}}
            <div x-show="currentStep >= 3"
                x-transition
                class="mt-6 bg-gradient-to-br from-purple-50 to-blue-50 dark:from-gray-800 dark:to-gray-700 rounded-lg shadow-lg p-6">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    💰 Estimation des crédits
                </h3>

                <div x-show="pricing.estimated_credits > 0">
                    {{-- Résumé principal --}}
                    <div class="bg-white dark:bg-gray-800 rounded-lg p-4 mb-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Crédits estimés</p>
                                <p class="text-3xl font-bold text-purple-600" x-text="pricing.estimated_credits"></p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Occupation totale</p>
                                <p class="text-2xl font-bold text-blue-600" x-text="formatDuration(pricing.estimated_hours)"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Détail du calcul --}}
                    <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-4 mb-4">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300">Détail du calcul</h4>
                        </div>

                        <div class="space-y-2 text-xs">
                            {{-- Liste des shots --}}
                            <template x-for="(shot, index) in target.shots" :key="index">
                                <div class="flex items-center justify-between text-gray-600 dark:text-gray-400">
                                    <span x-text="`${shot.num}× ${shot.filter_name} (${shot.exposure}s)`"></span>
                                    <span class="font-mono" x-text="formatTime(shot.num * shot.exposure)"></span>
                                </div>
                            </template>

                            <div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2">
                                <div class="flex justify-between text-gray-700 dark:text-gray-300">
                                    <span>Temps d'exposition</span>
                                    <span class="font-semibold" x-text="formatTime(getTotalExposureTime())"></span>
                                </div>
                                <div class="flex justify-between text-blue-600 dark:text-blue-400 mt-1">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        Overheads techniques
                                    </span>
                                    <span class="font-semibold" x-text="`${getTotalImages()}× 30s ≈ ${Math.ceil(getTotalImages() * 30 / 60)}min`"></span>
                                </div>
                                <div class="flex justify-between font-bold text-gray-900 dark:text-white mt-2 pt-2 border-t border-gray-300 dark:border-gray-600">
                                    <span>Occupation totale</span>
                                    <span x-text="formatDuration(pricing.estimated_hours)"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Explication overhead --}}
                        <div class="mt-3 p-2 bg-blue-50 dark:bg-blue-900/20 rounded text-xs text-gray-600 dark:text-gray-400">
                            <div class="flex items-start gap-1">
                                <span>💡</span>
                                <span>Les overheads (~30s/pose) incluent : lecture capteur, sauvegarde FITS, vérification guidage.</span>
                            </div>
                        </div>
                    </div>

                    {{-- Multiplicateurs --}}
                    <div class="bg-white/50 dark:bg-gray-800/50 rounded-lg p-4 mb-4">
                        <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Multiplicateurs appliqués</h4>
                        <ul class="text-sm space-y-2">
                            <li class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">
                                    Priorité <span class="font-medium" x-text="'(' + getPriorityLabel(target.priority) + ')'"></span>
                                </span>
                                <span class="font-semibold text-gray-900 dark:text-white" x-text="'×' + (pricing.multipliers?.priority_multiplier || 1.0)"></span>
                            </li>
                            <li x-show="target.c_moon_down" class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Nuit noire 🌙</span>
                                <span class="font-semibold text-purple-600 dark:text-purple-400">×2.0</span>
                            </li>
                            <li x-show="target.c_hfd_mean_limit" class="flex justify-between items-center">
                                <span class="text-gray-600 dark:text-gray-400">Garantie netteté HFD ⭐</span>
                                <span class="font-semibold text-yellow-600 dark:text-yellow-400">×1.5</span>
                            </li>
                        </ul>
                    </div>

                    {{-- Solde et validation --}}
                    <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm text-gray-600 dark:text-gray-400">Votre solde actuel</span>
                            <span class="text-lg font-bold"
                                :class="creditsBalance >= pricing.estimated_credits ? 'text-green-600' : 'text-red-600'"
                                x-text="creditsBalance + ' crédits'"></span>
                        </div>

                        <div x-show="creditsBalance >= pricing.estimated_credits" class="flex items-center gap-2 text-sm text-green-600 dark:text-green-400">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Crédits suffisants</span>
                        </div>

                        <div x-show="creditsBalance < pricing.estimated_credits" class="p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded text-sm text-red-700 dark:text-red-400">
                            <div class="flex items-start gap-2">
                                <svg class="w-4 h-4 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <div>
                                    <div class="font-semibold">Crédits insuffisants</div>
                                    <div class="text-xs mt-1">
                                        Il vous manque <span class="font-bold" x-text="pricing.estimated_credits - creditsBalance"></span> crédits.
                                        <a href="{{ route('subscriptions.choose', ['locale' => app()->getLocale()]) }}" class="underline hover:no-underline">Changer de plan</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div x-show="pricing.estimated_credits === 0" class="text-center py-8">
                    <div class="text-4xl mb-2">📊</div>
                    <p class="text-gray-500 dark:text-gray-400">Ajoutez des acquisitions pour voir l'estimation</p>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Inject user data for RoboTargetManager component
    window.userSubscription = @json(auth()->user()->subscription);
    window.userCredits = {{ auth()->user()->credits_balance }};
</script>
@endpush
@endsection
