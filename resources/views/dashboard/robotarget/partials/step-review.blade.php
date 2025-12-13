{{-- Step 4: Review & Submit --}}
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        ✓ Vérification et soumission
    </h2>

    <p class="text-gray-600 dark:text-gray-400">
        Vérifiez les informations avant de créer votre target RoboTarget.
    </p>

    {{-- Target Summary --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border-2 border-gray-300 dark:border-gray-600 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-4">
            <h3 class="text-xl font-bold flex items-center">
                <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span x-text="target.target_name"></span>
            </h3>
        </div>

        <div class="p-6 space-y-6">
            {{-- Coordinates --}}
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <span class="text-2xl mr-2">🎯</span>
                    Coordonnées
                </h4>
                <div class="grid grid-cols-2 gap-4 bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">RA J2000</p>
                        <p class="text-lg font-mono font-semibold text-gray-900 dark:text-white" x-text="target.ra_j2000"></p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600 dark:text-gray-400">DEC J2000</p>
                        <p class="text-lg font-mono font-semibold text-gray-900 dark:text-white" x-text="target.dec_j2000"></p>
                    </div>
                </div>
            </div>

            {{-- Priority --}}
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <span class="text-2xl mr-2">⚡</span>
                    Priorité
                </h4>
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-4">
                    <div class="flex items-center justify-between">
                        <span class="text-lg font-semibold text-gray-900 dark:text-white" x-text="getPriorityLabel(target.priority)"></span>
                        <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 rounded-full font-semibold" x-text="'Multiplicateur: ×' + [1.0, 1.0, 1.2, 2.0, 3.0][target.priority]"></span>
                    </div>
                </div>
            </div>

            {{-- Constraints --}}
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <span class="text-2xl mr-2">⚙️</span>
                    Contraintes
                </h4>
                <div class="space-y-2">
                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="flex items-center">
                            <span class="text-xl mr-2">📐</span>
                            <span class="text-gray-900 dark:text-white">Altitude minimale</span>
                        </div>
                        <span class="font-semibold text-gray-900 dark:text-white" x-text="target.c_alt_min + '°'"></span>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                        :class="target.c_moon_down ? 'ring-2 ring-purple-400' : ''">
                        <div class="flex items-center">
                            <span class="text-xl mr-2">🌙</span>
                            <span class="text-gray-900 dark:text-white">Lune couchée</span>
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold" :class="target.c_moon_down ? 'text-purple-600' : 'text-gray-500'" x-text="target.c_moon_down ? 'Oui' : 'Non'"></span>
                            <span x-show="target.c_moon_down" class="ml-2 px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs font-semibold rounded">×2.0</span>
                        </div>
                    </div>

                    <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3"
                        :class="target.c_hfd_mean_limit ? 'ring-2 ring-green-400' : ''">
                        <div class="flex items-center">
                            <span class="text-xl mr-2">⭐</span>
                            <span class="text-gray-900 dark:text-white">Garantie HFD</span>
                        </div>
                        <div class="flex items-center">
                            <span class="font-semibold" :class="target.c_hfd_mean_limit ? 'text-green-600' : 'text-gray-500'" x-text="target.c_hfd_mean_limit ? target.c_hfd_mean_limit + 'px' : 'Non'"></span>
                            <span x-show="target.c_hfd_mean_limit" class="ml-2 px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-semibold rounded">×1.5</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Shots List --}}
            <div>
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3 flex items-center">
                    <span class="text-2xl mr-2">📸</span>
                    Acquisitions (<span x-text="target.shots.length"></span>)
                </h4>
                <div class="space-y-2">
                    <template x-for="(shot, index) in target.shots" :key="index">
                        <div class="flex items-center justify-between bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                            <div class="flex items-center space-x-4">
                                <span class="font-semibold text-gray-900 dark:text-white min-w-[80px]" x-text="shot.filter_name"></span>
                                <span class="text-gray-600 dark:text-gray-400" x-text="formatTime(shot.exposure)"></span>
                                <span class="text-gray-600 dark:text-gray-400">×</span>
                                <span class="text-gray-600 dark:text-gray-400" x-text="shot.num"></span>
                            </div>
                            <span class="font-semibold text-blue-600" x-text="formatTime(shot.exposure * shot.num)"></span>
                        </div>
                    </template>
                </div>

                {{-- Shot Totals --}}
                <div class="mt-4 pt-4 border-t border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Total images</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="getTotalImages()"></p>
                        </div>
                        <div class="text-center">
                            <p class="text-sm text-gray-600 dark:text-gray-400">Temps total</p>
                            <p class="text-2xl font-bold text-blue-600" x-text="formatDuration(getTotalExposureTime() / 3600)"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Final Pricing Summary --}}
    <div class="bg-gradient-to-br from-purple-100 via-blue-100 to-green-100 dark:from-gray-800 dark:via-gray-700 dark:to-gray-800 rounded-lg border-2 border-purple-300 dark:border-gray-600 p-6">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4 flex items-center">
            <span class="text-2xl mr-2">💰</span>
            Résumé des coûts
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="bg-white dark:bg-gray-700 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Coût de base</p>
                <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="pricing.base_cost + ' crédits'"></p>
            </div>
            <div class="bg-white dark:bg-gray-700 rounded-lg p-4 text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Multiplicateur total</p>
                <p class="text-2xl font-bold text-blue-600" x-text="'×' + (pricing.multipliers?.total_multiplier || 1.0).toFixed(1)"></p>
            </div>
            <div class="bg-white dark:bg-gray-700 rounded-lg p-4 text-center ring-2 ring-purple-500">
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Coût final</p>
                <p class="text-3xl font-bold text-purple-600" x-text="pricing.estimated_credits + ' crédits'"></p>
            </div>
        </div>

        {{-- Credits Balance --}}
        <div class="bg-white dark:bg-gray-700 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Votre solde actuel</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white" x-text="creditsBalance + ' crédits'"></p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-600 dark:text-gray-400">Après cette target</p>
                    <p class="text-2xl font-bold"
                        :class="creditsBalance >= pricing.estimated_credits ? 'text-green-600' : 'text-red-600'"
                        x-text="(creditsBalance - pricing.estimated_credits) + ' crédits'"></p>
                </div>
            </div>

            {{-- Insufficient Credits Warning --}}
            <div x-show="creditsBalance < pricing.estimated_credits"
                class="mt-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="font-semibold text-red-800 dark:text-red-200">Crédits insuffisants</p>
                        <p class="text-sm text-red-700 dark:text-red-300">
                            Vous devez recharger votre compte ou souscrire à un plan supérieur.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estimated Duration --}}
        <div class="mt-4 text-center text-sm text-gray-600 dark:text-gray-400">
            <p>⏱️ Durée estimée d'observation: <span class="font-semibold text-gray-900 dark:text-white" x-text="formatDuration(pricing.estimated_hours)"></span></p>
            <p class="text-xs mt-1">(incluant overhead système de ~30%)</p>
        </div>
    </div>

    {{-- Terms Agreement --}}
    <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-yellow-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-yellow-800 dark:text-yellow-200">
                <p class="font-semibold mb-1">📝 Important</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Les crédits seront <strong>réservés</strong> immédiatement après la soumission</li>
                    <li>Les crédits seront <strong>capturés</strong> uniquement en cas de session réussie</li>
                    <li>En cas d'erreur ou d'annulation, les crédits seront <strong>remboursés automatiquement</strong></li>
                    <li>Les conditions météo et seeing peuvent impacter la durée réelle d'observation</li>
                </ul>
            </div>
        </div>
    </div>
</div>
