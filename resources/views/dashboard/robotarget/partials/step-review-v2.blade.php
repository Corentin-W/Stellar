{{-- Step 4: Review & Submit - Version Compact & Visuelle --}}
<div class="space-y-5">

    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            ✓ Vérification finale
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Contrôlez les informations avant de soumettre votre cible
        </p>
    </div>

    {{-- Target Summary Card - Compact --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl border-2 border-gray-200 dark:border-gray-700 overflow-hidden">
        {{-- Header avec gradient --}}
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-5 py-3">
            <h3 class="text-lg font-bold flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="target.target_name"></span>
            </h3>
        </div>

        <div class="p-5 space-y-4">
            {{-- Coordonnées --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                    <span>🎯</span>
                    <span>Coordonnées J2000</span>
                </h4>
                <div class="grid grid-cols-2 gap-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-3">
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">RA (Ascension Droite)</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white" x-text="target.ra_j2000"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-0.5">DEC (Déclinaison)</p>
                        <p class="text-sm font-mono font-semibold text-gray-900 dark:text-white" x-text="target.dec_j2000"></p>
                    </div>
                </div>
            </div>

            {{-- Contraintes - Compact --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                    <span>⚙️</span>
                    <span>Contraintes d'observation</span>
                </h4>
                <div class="space-y-2">
                    {{-- Altitude --}}
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span>📐</span>
                            <span class="text-gray-900 dark:text-white">Altitude min.</span>
                        </div>
                        <span class="font-semibold text-blue-600 text-sm" x-text="target.c_alt_min + '°'"></span>
                    </div>

                    {{-- Moon --}}
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2"
                        :class="target.c_moon_down ? 'ring-2 ring-purple-400' : ''">
                        <div class="flex items-center gap-2 text-sm">
                            <span>🌙</span>
                            <span class="text-gray-900 dark:text-white">Nuit noire</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="font-semibold text-sm" :class="target.c_moon_down ? 'text-purple-600' : 'text-gray-500'" x-text="target.c_moon_down ? 'Oui' : 'Non'"></span>
                            <span x-show="target.c_moon_down" class="px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs font-semibold rounded">×2 crédits</span>
                        </div>
                    </div>

                    {{-- Hour Angle (if set) --}}
                    <div x-show="target.ha_start || target.ha_end"
                        class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
                        <div class="flex items-center gap-2 text-sm">
                            <span>🕐</span>
                            <span class="text-gray-900 dark:text-white">Angle horaire</span>
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white text-sm" x-text="(target.ha_start || 'auto') + ' → ' + (target.ha_end || 'auto')"></span>
                    </div>
                </div>
            </div>

            {{-- Acquisitions - Compact List --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2 flex items-center gap-1">
                    <span>📸</span>
                    <span>Acquisitions (<span x-text="target.shots.length"></span>)</span>
                </h4>
                <div class="space-y-1.5">
                    <template x-for="(shot, index) in target.shots" :key="index">
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700/50 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-3 text-sm">
                                <span class="w-2 h-2 rounded-full"
                                    :class="{
                                        'bg-gray-400': shot.filter_name?.includes('Luminance'),
                                        'bg-red-500': shot.filter_name?.includes('Red') || shot.filter_name?.includes('Ha'),
                                        'bg-green-500': shot.filter_name?.includes('Green'),
                                        'bg-blue-500': shot.filter_name?.includes('Blue'),
                                        'bg-cyan-500': shot.filter_name?.includes('OIII'),
                                        'bg-yellow-500': shot.filter_name?.includes('SII')
                                    }"></span>
                                <span class="font-semibold text-gray-900 dark:text-white min-w-[80px]" x-text="shot.filter_name"></span>
                                <span class="text-gray-600 dark:text-gray-400" x-text="formatTime(shot.exposure)"></span>
                                <span class="text-gray-500 dark:text-gray-500">×</span>
                                <span class="text-gray-600 dark:text-gray-400" x-text="shot.num"></span>
                            </div>
                            <span class="font-semibold text-blue-600 text-sm" x-text="formatTime(shot.exposure * shot.num)"></span>
                        </div>
                    </template>
                </div>

                {{-- Totals - Inline --}}
                <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-2 gap-3 text-center">
                        <div class="bg-green-50 dark:bg-green-900/10 rounded-lg p-2">
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Total images</p>
                            <p class="text-lg font-bold text-green-600" x-text="getTotalImages()"></p>
                        </div>
                        <div class="bg-blue-50 dark:bg-blue-900/10 rounded-lg p-2">
                            <p class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Temps total</p>
                            <p class="text-lg font-bold text-blue-600" x-text="formatDuration(getTotalExposureTime() / 3600)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pricing Summary - Compact & Visual --}}
    <div class="bg-gradient-to-br from-purple-100 via-blue-100 to-green-100 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 rounded-xl border-2 border-purple-300 dark:border-gray-600 p-5">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            <span>💰</span>
            <span>Estimation des coûts</span>
        </h3>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <div class="bg-white dark:bg-gray-700 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Coût de base</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="pricing.base_cost"></p>
                <p class="text-xs text-gray-500">crédits</p>
            </div>
            <div class="bg-white dark:bg-gray-700 rounded-lg p-3 text-center">
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Multiplicateur</p>
                <p class="text-xl font-bold text-blue-600" x-text="'×' + (pricing.multipliers?.total_multiplier || 1.0).toFixed(1)"></p>
                <p class="text-xs text-gray-500">total</p>
            </div>
            <div class="bg-white dark:bg-gray-700 rounded-lg p-3 text-center ring-2 ring-purple-500">
                <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Coût final</p>
                <p class="text-2xl font-bold text-purple-600" x-text="pricing.estimated_credits"></p>
                <p class="text-xs text-gray-500">crédits</p>
            </div>
        </div>

        {{-- Credits Balance --}}
        <div class="bg-white dark:bg-gray-700 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Votre solde actuel</p>
                    <p class="text-xl font-bold text-gray-900 dark:text-white" x-text="creditsBalance + ' crédits'"></p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-0.5">Après cette target</p>
                    <p class="text-xl font-bold"
                        :class="creditsBalance >= pricing.estimated_credits ? 'text-green-600' : 'text-red-600'"
                        x-text="(creditsBalance - pricing.estimated_credits) + ' crédits'"></p>
                </div>
            </div>

            {{-- Insufficient Credits Warning --}}
            <div x-show="creditsBalance < pricing.estimated_credits"
                class="mt-3 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-red-800 dark:text-red-200">Crédits insuffisants</p>
                        <p class="text-xs text-red-700 dark:text-red-300 mt-0.5">
                            Rechargez votre compte ou souscrivez à un plan supérieur pour continuer.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estimated Duration --}}
        <div class="mt-3 text-center">
            <p class="text-sm text-gray-600 dark:text-gray-400">
                ⏱️ Durée estimée: <span class="font-semibold text-gray-900 dark:text-white" x-text="formatDuration(pricing.estimated_hours)"></span>
            </p>
            <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">(overhead système ~30% inclus)</p>
        </div>
    </div>

    {{-- Important Notes - Compact --}}
    <div class="bg-yellow-50 dark:bg-yellow-900/10 rounded-lg p-4 border-l-4 border-yellow-600">
        <div class="flex gap-3">
            <div class="flex-shrink-0 text-xl">
                📝
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                    Important à savoir
                </h4>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• Les crédits seront <strong>réservés</strong> immédiatement après soumission</li>
                    <li>• Ils ne seront <strong>capturés</strong> qu'en cas de session réussie</li>
                    <li>• En cas d'erreur, les crédits seront <strong>remboursés automatiquement</strong></li>
                    <li>• Les conditions météo peuvent impacter la durée réelle</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Quick Edit Buttons - Helper --}}
    <div class="bg-blue-50 dark:bg-blue-900/10 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Besoin de modifier quelque chose ?</p>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">Utilisez les boutons de navigation pour revenir en arrière</p>
            </div>
            <div class="flex gap-2">
                <button type="button"
                    @click="currentStep = 1"
                    class="px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    🎯 Cible
                </button>
                <button type="button"
                    @click="currentStep = 2"
                    class="px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    ⚙️ Contraintes
                </button>
                <button type="button"
                    @click="currentStep = 3"
                    class="px-3 py-1.5 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-lg text-xs font-medium hover:bg-gray-50 dark:hover:bg-gray-700 transition">
                    📸 Acquisitions
                </button>
            </div>
        </div>
    </div>

</div>
