{{-- Mode Assisté - Step 2: Paramètres Qualité --}}
<div class="space-y-6" x-show="selectedTemplate !== null">

    {{-- Header avec preview de l'objet --}}
    <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-xl p-6 text-white">
        <div class="flex items-center gap-4">
            <img :src="selectedTemplate?.preview_image || ''" :alt="selectedTemplate?.name || ''"
                 class="w-24 h-24 rounded-lg object-cover border-2 border-white/30">
            <div class="flex-1">
                <h2 class="text-2xl font-bold" x-text="selectedTemplate?.name || ''"></h2>
                <p class="text-white/90 text-sm mt-1" x-text="(selectedTemplate?.type || '') + ' • ' + (selectedTemplate?.constellation || '')"></p>
                <p class="text-white/80 text-xs mt-2" x-text="selectedTemplate?.tips || ''"></p>
            </div>
        </div>
    </div>

    <p class="text-gray-600 dark:text-gray-400">
        Ajustez les paramètres selon votre budget et le niveau de qualité souhaité
    </p>

    {{-- Section 1: Plan d'exposition --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            📸 Plan d'exposition
        </h3>

        <div class="space-y-4">
            {{-- Exposition par pose --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Temps d'exposition par pose
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" x-model.number="assistedConfig.exposure" min="60" max="900" step="30"
                           class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    <span class="text-2xl font-bold text-blue-600 min-w-[100px] text-right" x-text="Math.floor(assistedConfig.exposure / 60) + 'm ' + (assistedConfig.exposure % 60) + 's'"></span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    💡 Plus long = plus de signal mais risque de saturation. Recommandé: 3-5 minutes
                </p>
            </div>

            {{-- Nombre de poses --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre de poses par filtre
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" x-model.number="assistedConfig.num" min="5" max="50" step="5"
                           class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    <span class="text-2xl font-bold text-blue-600 min-w-[100px] text-right" x-text="assistedConfig.num + ' poses'"></span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    💡 Plus de poses = meilleur rapport signal/bruit (SNR)
                </p>
            </div>

            {{-- Binning --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Binning (Résolution vs Sensibilité)
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <button @click="assistedConfig.bin = 1" type="button"
                            :class="assistedConfig.bin === 1 ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'"
                            class="p-4 border-2 rounded-lg text-left transition-all hover:shadow-md">
                        <div class="font-bold text-gray-900 dark:text-white">1×1 - Haute Résolution</div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Maximum de détails, temps plus long</p>
                        <span class="text-xs text-orange-600 font-medium">+20% coût</span>
                    </button>
                    <button @click="assistedConfig.bin = 2" type="button"
                            :class="assistedConfig.bin === 2 ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600'"
                            class="p-4 border-2 rounded-lg text-left transition-all hover:shadow-md">
                        <div class="font-bold text-gray-900 dark:text-white">2×2 - Sensibilité</div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">Plus rapide, meilleur SNR</p>
                        <span class="text-xs text-green-600 font-medium">Coût standard</span>
                    </button>
                </div>
            </div>

            {{-- Gain --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Gain caméra
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" x-model.number="assistedConfig.gain" min="0" max="200" step="10"
                           class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    <span class="text-xl font-bold text-blue-600 min-w-[80px] text-right" x-text="assistedConfig.gain"></span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    💡 Gain=100 (Unity Gain) recommandé pour la plupart des cas
                </p>
            </div>
        </div>
    </div>

    {{-- Section 2: Contraintes Environnementales --}}
    <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            🌙 Contraintes de Qualité
            <span class="text-xs font-normal text-gray-500">(plus strict = plus cher)</span>
        </h3>

        <div class="space-y-4">
            {{-- Altitude minimale --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Altitude minimale (seeing)
                </label>
                <div class="flex items-center gap-4">
                    <input type="range" x-model.number="assistedConfig.c_alt_min" min="20" max="70" step="5"
                           class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-blue-600">
                    <span class="text-xl font-bold text-blue-600 min-w-[60px] text-right" x-text="assistedConfig.c_alt_min + '°'"></span>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    Plus haut = meilleur seeing mais fenêtre d'observation réduite <span class="text-orange-600 font-medium">(×1.1 si >50°)</span>
                </p>
            </div>

            {{-- Lune couchée --}}
            <label class="flex items-start p-4 bg-purple-50 dark:bg-purple-900/20 border-2 rounded-lg cursor-pointer transition-all"
                   :class="assistedConfig.c_moon_down ? 'border-purple-600' : 'border-purple-200 dark:border-purple-800'">
                <input type="checkbox" x-model="assistedConfig.c_moon_down"
                       class="mt-1 text-purple-600 focus:ring-purple-500 rounded">
                <div class="ml-3 flex-1">
                    <div class="flex items-center justify-between">
                        <span class="font-medium text-gray-900 dark:text-white">🌑 Exiger la lune couchée</span>
                        <span class="text-xs font-bold text-orange-600">×2.0 crédits</span>
                    </div>
                    <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                        Essentiel pour les nébuleuses en bande étroite. Réduit fortement les opportunités.
                    </p>
                </div>
            </label>

            {{-- HFD Limite --}}
            <div>
                <label class="flex items-start p-4 bg-green-50 dark:bg-green-900/20 border-2 rounded-lg cursor-pointer transition-all"
                       :class="assistedConfig.enableHfd ? 'border-green-600' : 'border-green-200 dark:border-green-800'">
                    <input type="checkbox" x-model="assistedConfig.enableHfd"
                           class="mt-1 text-green-600 focus:ring-green-500 rounded">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">⭐ Garantie finesse (HFD)</span>
                            <span class="text-xs font-bold text-orange-600">×1.5 crédits</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            Rejette les images avec des étoiles trop grosses (mauvais seeing/buée)
                        </p>
                    </div>
                </label>

                <div x-show="assistedConfig.enableHfd" class="mt-3 pl-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        HFD maximum autorisé
                    </label>
                    <div class="flex items-center gap-4">
                        <input type="range" x-model.number="assistedConfig.c_hfd_mean_limit" min="1.5" max="4.0" step="0.1"
                               class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-green-600">
                        <span class="text-xl font-bold text-green-600 min-w-[80px] text-right" x-text="assistedConfig.c_hfd_mean_limit + 'px'"></span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                        <span>1.5px (Excellent)</span>
                        <span>2.5px (Bon)</span>
                        <span>4.0px (Acceptable)</span>
                    </div>
                </div>
            </div>

            {{-- SQM (Qualité du ciel) --}}
            <div>
                <label class="flex items-start p-4 bg-indigo-50 dark:bg-indigo-900/20 border-2 rounded-lg cursor-pointer transition-all"
                       :class="assistedConfig.enableSqm ? 'border-indigo-600' : 'border-indigo-200 dark:border-indigo-800'">
                    <input type="checkbox" x-model="assistedConfig.enableSqm"
                           class="mt-1 text-indigo-600 focus:ring-indigo-500 rounded">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-gray-900 dark:text-white">🌃 Exiger un ciel noir (SQM)</span>
                            <span class="text-xs font-bold text-orange-600">×1.3 crédits</span>
                        </div>
                        <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                            Nécessite un capteur SQM. Shooter uniquement si le ciel est très noir.
                        </p>
                    </div>
                </label>

                <div x-show="assistedConfig.enableSqm" class="mt-3 pl-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                        SQM minimum
                    </label>
                    <div class="flex items-center gap-4">
                        <input type="range" x-model.number="assistedConfig.c_sqm_min" min="18" max="22" step="0.5"
                               class="flex-1 h-2 bg-gray-200 dark:bg-gray-700 rounded-lg appearance-none cursor-pointer accent-indigo-600">
                        <span class="text-xl font-bold text-indigo-600 min-w-[80px] text-right" x-text="assistedConfig.c_sqm_min"></span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500 mt-2">
                        <span>18 (Banlieue)</span>
                        <span>20 (Rural)</span>
                        <span>22 (Excellent)</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Estimation en temps réel --}}
    <div class="bg-gradient-to-br from-purple-100 via-blue-100 to-green-100 dark:from-gray-800 dark:to-gray-700 rounded-xl p-6 border-2 border-purple-300 dark:border-gray-600">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            💰 Estimation instantanée
        </h3>

        <div class="grid grid-cols-3 gap-4">
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

        <div class="mt-4 text-xs text-gray-600 dark:text-gray-400 text-center">
            Votre solde: <span class="font-bold" :class="creditsBalance >= calculateAssistedCredits() ? 'text-green-600' : 'text-red-600'" x-text="creditsBalance + ' crédits'"></span>
        </div>
    </div>

</div>
