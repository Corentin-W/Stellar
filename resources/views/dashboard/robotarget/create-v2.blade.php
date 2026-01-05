@extends('layouts.astral-app')

@section('title', 'Créer une Target RoboTarget - V2')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Compact Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                🎯 Nouvelle Target RoboTarget
            </h1>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                Créez une nouvelle cible pour observation automatisée
            </p>
        </div>

        {{-- Alpine.js Component --}}
        <div x-data="RoboTargetManager()" x-init="init()">
            {{-- Progress Steps (visible après step 0) --}}
            <div class="mb-6" x-show="currentStep > 0" x-transition>
                <div class="flex items-center justify-between">
                    {{-- Step 1: Target Info --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(1)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 1 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition text-sm"
                                :class="currentStep >= 1 ? 'border-blue-600 bg-blue-100 dark:bg-blue-900' : 'border-gray-300 bg-gray-100 dark:bg-gray-700'">
                                <span class="font-semibold">1</span>
                            </div>
                            <span class="mt-1 text-xs font-medium">Cible</span>
                        </button>
                    </div>

                    <div class="flex-1 h-0.5 mx-2"
                        :class="currentStep >= 2 ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"></div>

                    {{-- Step 2: Constraints --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(2)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 2 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition text-sm"
                                :class="currentStep >= 2 ? 'border-blue-600 bg-blue-100 dark:bg-blue-900' : 'border-gray-300 bg-gray-100 dark:bg-gray-700'">
                                <span class="font-semibold">2</span>
                            </div>
                            <span class="mt-1 text-xs font-medium">Contraintes</span>
                        </button>
                    </div>

                    <div class="flex-1 h-0.5 mx-2"
                        :class="currentStep >= 3 ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"></div>

                    {{-- Step 3: Shots --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(3)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 3 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition text-sm"
                                :class="currentStep >= 3 ? 'border-blue-600 bg-blue-100 dark:bg-blue-900' : 'border-gray-300 bg-gray-100 dark:bg-gray-700'">
                                <span class="font-semibold">3</span>
                            </div>
                            <span class="mt-1 text-xs font-medium">Acquisitions</span>
                        </button>
                    </div>

                    <div class="flex-1 h-0.5 mx-2"
                        :class="currentStep >= 4 ? 'bg-blue-600' : 'bg-gray-300 dark:bg-gray-600'"></div>

                    {{-- Step 4: Review --}}
                    <div class="flex-1">
                        <button
                            @click="goToStep(4)"
                            class="flex flex-col items-center w-full"
                            :class="currentStep >= 4 ? 'text-blue-600' : 'text-gray-400'"
                        >
                            <div class="w-8 h-8 rounded-full flex items-center justify-center border-2 transition text-sm"
                                :class="currentStep >= 4 ? 'border-blue-600 bg-blue-100 dark:bg-blue-900' : 'border-gray-300 bg-gray-100 dark:bg-gray-700'">
                                <span class="font-semibold">4</span>
                            </div>
                            <span class="mt-1 text-xs font-medium">Résumé</span>
                        </button>
                    </div>
                </div>
            </div>

            {{-- Error Message --}}
            <div x-show="errorMessage"
                x-transition
                class="mb-6 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-red-800 dark:text-red-200 font-medium" x-text="errorMessage"></span>
                </div>
            </div>

            {{-- Success Message --}}
            <div x-show="successMessage"
                x-transition
                class="mb-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg p-4">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span class="text-green-800 dark:text-green-200 font-medium" x-text="successMessage"></span>
                </div>
            </div>

            {{-- Form Card --}}
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <div class="p-6">
                    {{-- Step 0: Mode Choice --}}
                    <div x-show="currentStep === 0 && creationMode === null" x-transition>
                        @include('dashboard.robotarget.partials.step-mode-choice')
                    </div>

                    {{-- MODE ASSISTÉ --}}
                    <template x-if="creationMode === 'assisted'">
                        <div>
                            {{-- Step 1: Catalogue des targets --}}
                            <div x-show="currentStep === 1" x-transition>
                                @include('dashboard.robotarget.partials.assisted.step-catalog')
                            </div>

                            {{-- Step 2: Paramètres qualité --}}
                            <div x-show="currentStep === 2" x-transition>
                                @include('dashboard.robotarget.partials.assisted.step-quality')
                            </div>

                            {{-- Step 3: Review --}}
                            <div x-show="currentStep === 3" x-transition>
                                @include('dashboard.robotarget.partials.assisted.step-review')
                            </div>
                        </div>
                    </template>

                    {{-- MODE MANUEL --}}
                    <template x-if="creationMode === 'manual'">
                        <div>
                            {{-- Step 1: Target Information --}}
                            <div x-show="currentStep === 1" x-transition>
                                @include('dashboard.robotarget.partials.step-target-info-v2')
                            </div>

                            {{-- Step 2: Constraints --}}
                            <div x-show="currentStep === 2" x-transition>
                                @include('dashboard.robotarget.partials.step-constraints-v2')
                            </div>

                            {{-- Step 3: Shots --}}
                            <div x-show="currentStep === 3" x-transition>
                                @include('dashboard.robotarget.partials.step-shots-v2')
                            </div>

                            {{-- Step 4: Review --}}
                            <div x-show="currentStep === 4" x-transition>
                                @include('dashboard.robotarget.partials.step-review-v2')
                            </div>
                        </div>
                    </template>
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
    window.userSubscription = @json($subscription);
    window.userCredits = {{ $creditsBalance }};
</script>
@endpush
@endsection
