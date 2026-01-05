{{-- Mode Assisté - Step 3: Review & Submit --}}
<div class="space-y-6" x-show="selectedTemplate !== null">

    {{-- Header avec target sélectionnée --}}
    <div class="bg-gradient-to-r from-purple-600 to-blue-600 rounded-xl p-6 text-white">
        <div class="flex items-center gap-4">
            <img :src="selectedTemplate?.preview_image || ''" :alt="selectedTemplate?.name || ''"
                 class="w-24 h-24 rounded-lg object-cover border-2 border-white/30">
            <div class="flex-1">
                <h2 class="text-2xl font-bold" x-text="selectedTemplate?.name || ''"></h2>
                <p class="text-white/90 text-sm mt-1" x-text="(selectedTemplate?.type || '') + ' • ' + (selectedTemplate?.constellation || '')"></p>
            </div>
        </div>
    </div>

    {{-- Résumé Configuration --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            📋 Configuration choisie
        </h3>

        <div class="grid grid-cols-2 gap-4">
            {{-- Exposition --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Temps d'exposition</div>
                <div class="text-2xl font-bold text-blue-600" x-text="Math.floor(assistedConfig.exposure / 60) + 'm ' + (assistedConfig.exposure % 60) + 's'"></div>
            </div>

            {{-- Nombre de poses --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Nombre de poses</div>
                <div class="text-2xl font-bold text-blue-600" x-text="assistedConfig.num + ' poses'"></div>
            </div>

            {{-- Binning --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Binning</div>
                <div class="text-lg font-bold text-blue-600" x-text="assistedConfig.bin + '×' + assistedConfig.bin"></div>
            </div>

            {{-- Gain --}}
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Gain caméra</div>
                <div class="text-lg font-bold text-blue-600" x-text="assistedConfig.gain"></div>
            </div>
        </div>
    </div>

    {{-- Contraintes environnementales --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            🌙 Contraintes de qualité
        </h3>

        <div class="space-y-3">
            {{-- Altitude --}}
            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                <span class="text-sm text-gray-700 dark:text-gray-300">Altitude minimale</span>
                <span class="font-bold text-blue-600" x-text="assistedConfig.c_alt_min + '°'"></span>
            </div>

            {{-- Lune couchée --}}
            <div x-show="assistedConfig.c_moon_down" class="flex items-center justify-between p-3 bg-purple-50 dark:bg-purple-900/20 rounded-lg border border-purple-200 dark:border-purple-800">
                <span class="text-sm text-gray-700 dark:text-gray-300">🌑 Lune couchée exigée</span>
                <span class="text-xs font-bold text-orange-600">×2.0 crédits</span>
            </div>

            {{-- HFD --}}
            <div x-show="assistedConfig.enableHfd" class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg border border-green-200 dark:border-green-800">
                <span class="text-sm text-gray-700 dark:text-gray-300">⭐ HFD max: <span class="font-bold" x-text="assistedConfig.c_hfd_mean_limit + 'px'"></span></span>
                <span class="text-xs font-bold text-orange-600">×1.5 crédits</span>
            </div>

            {{-- SQM --}}
            <div x-show="assistedConfig.enableSqm" class="flex items-center justify-between p-3 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-200 dark:border-indigo-800">
                <span class="text-sm text-gray-700 dark:text-gray-300">🌃 SQM min: <span class="font-bold" x-text="assistedConfig.c_sqm_min"></span></span>
                <span class="text-xs font-bold text-orange-600">×1.3 crédits</span>
            </div>
        </div>
    </div>

    {{-- Estimation finale --}}
    <div class="bg-gradient-to-br from-purple-100 via-blue-100 to-green-100 dark:from-gray-800 dark:to-gray-700 rounded-xl p-6 border-2 border-purple-300 dark:border-gray-600">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            💰 Coût final
        </h3>

        <div class="grid grid-cols-3 gap-4 mb-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Temps total</div>
                <div class="text-2xl font-bold text-blue-600" x-text="formatDuration(calculateAssistedDuration() / 3600)"></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Multiplicateur</div>
                <div class="text-2xl font-bold text-orange-600" x-text="'×' + calculateAssistedMultiplier().toFixed(1)"></div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg p-4 text-center ring-2 ring-purple-500">
                <div class="text-xs text-gray-600 dark:text-gray-400 mb-1">Coût final</div>
                <div class="text-3xl font-bold text-purple-600" x-text="calculateAssistedCredits() + ' crédits'"></div>
            </div>
        </div>

        <div class="text-xs text-gray-600 dark:text-gray-400 text-center">
            Votre solde: <span class="font-bold" :class="creditsBalance >= calculateAssistedCredits() ? 'text-green-600' : 'text-red-600'" x-text="creditsBalance + ' crédits'"></span>
        </div>

        {{-- Avertissement crédits insuffisants --}}
        <div x-show="creditsBalance < calculateAssistedCredits()" class="mt-4 p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="flex-1">
                    <div class="font-semibold text-red-800 dark:text-red-200">Crédits insuffisants</div>
                    <div class="text-sm text-red-700 dark:text-red-300 mt-1">
                        Il vous manque <span class="font-bold" x-text="calculateAssistedCredits() - creditsBalance"></span> crédits.
                        <a href="{{ route('subscriptions.choose', ['locale' => app()->getLocale()]) }}" class="underline hover:no-underline">Changer de plan</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex gap-4">
        <button @click="currentStep = 2" type="button"
            class="flex-1 px-6 py-3 bg-gray-200 dark:bg-gray-600 text-gray-800 dark:text-white rounded-lg hover:bg-gray-300 dark:hover:bg-gray-500 transition font-medium">
            ← Modifier les paramètres
        </button>

        <button @click="submitAssistedTarget()" type="button"
            :disabled="isLoading || creditsBalance < calculateAssistedCredits()"
            class="flex-1 px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed">
            <span x-show="!isLoading">✓ Créer la Target</span>
            <span x-show="isLoading">⏳ Création en cours...</span>
        </button>
    </div>

</div>
