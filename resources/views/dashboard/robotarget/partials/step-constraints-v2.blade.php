{{-- Step 2: Constraints - Version Compact & Pédagogique --}}
<div class="space-y-5">

    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            ⚙️ Contraintes d'observation
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Définissez les conditions optimales pour garantir la qualité de vos images
        </p>
    </div>

    {{-- Altitude minimale avec slider visuel --}}
    <div class="bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-900/10 dark:to-cyan-900/10 rounded-xl p-5 border border-blue-100 dark:border-blue-800">
        <div class="flex items-start justify-between mb-3">
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                    📐 Altitude minimale
                    <span class="px-2 py-0.5 bg-blue-600 text-white text-xs rounded-full" x-text="target.c_alt_min + '°'"></span>
                </h3>
                <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                    Hauteur minimale de l'objet au-dessus de l'horizon
                </p>
            </div>

            {{-- Visual indicator --}}
            <div class="text-4xl" x-text="target.c_alt_min >= 60 ? '🌟' : (target.c_alt_min >= 40 ? '⭐' : '🌠')"></div>
        </div>

        {{-- Slider moderne --}}
        <div class="mt-4">
            <input type="range"
                x-model.number="target.c_alt_min"
                min="20"
                max="80"
                step="5"
                class="w-full h-2 bg-blue-200 dark:bg-blue-700 rounded-lg appearance-none cursor-pointer accent-blue-600">

            <div class="flex justify-between text-xs text-gray-500 dark:text-gray-400 mt-2">
                <span>20° (Plus de temps)</span>
                <span>50° (Équilibré)</span>
                <span>80° (Meilleure qualité)</span>
            </div>
        </div>

        {{-- Explication dynamique --}}
        <div class="mt-3 p-3 bg-white/50 dark:bg-gray-800/30 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-300">
                <template x-if="target.c_alt_min < 40">
                    <span>💡 <strong>Basse altitude:</strong> Plus de temps d'observation mais traversée d'une atmosphère plus épaisse (turbulence)</span>
                </template>
                <template x-if="target.c_alt_min >= 40 && target.c_alt_min < 60">
                    <span>✨ <strong>Altitude recommandée:</strong> Bon compromis entre temps d'observation et qualité d'image</span>
                </template>
                <template x-if="target.c_alt_min >= 60">
                    <span>🌟 <strong>Haute altitude:</strong> Meilleure qualité d'image mais fenêtre d'observation réduite</span>
                </template>
            </p>
        </div>
    </div>

    {{-- Phase lunaire avec visualisation --}}
    <div class="bg-gradient-to-br from-indigo-50 to-purple-50 dark:from-indigo-900/10 dark:to-purple-900/10 rounded-xl p-5 border border-indigo-100 dark:border-indigo-800">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white flex items-center gap-2">
                🌙 Contrainte lunaire
                @if($subscription && $subscription->canUseMoonDown())
                    <span class="px-2 py-0.5 bg-green-600 text-white text-xs rounded-full">Disponible</span>
                @else
                    <span class="px-2 py-0.5 bg-gray-400 text-white text-xs rounded-full">Plan Nebula+ requis</span>
                @endif
            </h3>
            <p class="text-xs text-gray-600 dark:text-gray-400 mt-1">
                La lune peut polluer vos images, surtout pour les nébuleuses
            </p>
        </div>

        <div class="space-y-3">
            {{-- Option 1: Peu importe --}}
            <label class="flex items-start p-3 bg-white dark:bg-gray-800 rounded-lg border-2 cursor-pointer transition-all"
                :class="!target.c_moon_down ? 'border-blue-600 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                <input type="radio"
                    name="moon_constraint"
                    :value="false"
                    x-model="target.c_moon_down"
                    class="mt-0.5 text-blue-600 focus:ring-blue-500">
                <div class="ml-3 flex-1">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">🌕 Peu importe</span>
                        <span class="text-xs font-semibold text-green-600">×1 crédit</span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        Observer même avec la lune visible (planètes, galaxies brillantes)
                    </p>
                </div>
            </label>

            {{-- Option 2: Nuit noire --}}
            @if($subscription && $subscription->canUseMoonDown())
                <label class="flex items-start p-3 bg-white dark:bg-gray-800 rounded-lg border-2 cursor-pointer transition-all"
                    :class="target.c_moon_down ? 'border-purple-600 bg-purple-50 dark:bg-purple-900/20' : 'border-gray-200 dark:border-gray-700 hover:border-gray-300'">
                    <input type="radio"
                        name="moon_constraint"
                        :value="true"
                        x-model="target.c_moon_down"
                        class="mt-0.5 text-purple-600 focus:ring-purple-500">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">🌑 Nuit noire</span>
                            <span class="text-xs font-semibold text-orange-600">×2 crédits</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Lune sous l'horizon (essentiel pour nébuleuses)
                        </p>
                    </div>
                </label>
            @else
                <div class="flex items-start p-3 bg-gray-100 dark:bg-gray-700/50 rounded-lg border-2 border-gray-300 dark:border-gray-600 opacity-60">
                    <input type="radio"
                        name="moon_constraint"
                        :value="true"
                        disabled
                        class="mt-0.5 text-purple-600 focus:ring-purple-500">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-medium text-gray-900 dark:text-white">🌑 Nuit noire</span>
                            <span class="text-xs font-semibold text-orange-600">×2 crédits</span>
                        </div>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            Lune sous l'horizon (essentiel pour nébuleuses)
                        </p>
                        <p class="text-xs text-amber-600 mt-1 font-medium">
                            ⚠️ Passez au plan Nebula ou Quasar pour débloquer cette option
                        </p>
                    </div>
                </div>
            @endif
        </div>

        {{-- Visual moon phases --}}
        <div class="mt-4 p-3 bg-white/50 dark:bg-gray-800/30 rounded-lg">
            <div class="flex items-center justify-around text-2xl">
                <div class="text-center">
                    <div>🌑</div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">Nouvelle</span>
                </div>
                <div class="text-gray-300 dark:text-gray-600">→</div>
                <div class="text-center">
                    <div>🌓</div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">1er quartier</span>
                </div>
                <div class="text-gray-300 dark:text-gray-600">→</div>
                <div class="text-center">
                    <div>🌕</div>
                    <span class="text-xs text-gray-600 dark:text-gray-400">Pleine</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Angle horaire (optionnel) --}}
    <div x-data="{ advanced: false }">
        <button @click="advanced = !advanced" type="button"
            class="w-full flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
            <div class="flex items-center gap-2">
                <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                </svg>
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                    Contraintes avancées (optionnel)
                </span>
            </div>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="advanced && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="advanced" x-collapse class="mt-3 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 space-y-4">

            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                    Angle horaire (HA)
                    <div x-data="{ show: false }" class="relative">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button"
                            class="text-gray-400 hover:text-gray-600">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="show" @click.away="show = false"
                            class="absolute left-0 mt-1 w-64 bg-gray-900 text-white text-xs rounded-lg p-3 z-10 shadow-lg">
                            <strong>Angle horaire:</strong> Position de l'objet par rapport au méridien.
                            <br>0h = objet au méridien (plus haut dans le ciel)
                        </div>
                    </div>
                </label>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">HA Début</label>
                        <input type="number"
                            x-model.number="target.ha_start"
                            min="-12"
                            max="12"
                            step="0.5"
                            placeholder="-6"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 dark:text-gray-400 mb-1">HA Fin</label>
                        <input type="number"
                            x-model.number="target.ha_end"
                            min="-12"
                            max="12"
                            step="0.5"
                            placeholder="+6"
                            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm">
                    </div>
                </div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                    💡 Laissez vide pour une observation automatique toute la nuit
                </p>
            </div>

        </div>
    </div>

    {{-- Info bulle récapitulative --}}
    <div class="bg-blue-50 dark:bg-blue-900/10 rounded-lg p-4 border-l-4 border-blue-600">
        <div class="flex gap-3">
            <div class="flex-shrink-0 text-2xl">
                💡
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    Récapitulatif de vos contraintes
                </h4>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• Altitude minimum: <strong x-text="target.c_alt_min + '°'"></strong></li>
                    <li x-show="target.c_moon_down">• 🌑 Nuit noire activée (×2 crédits)</li>
                    <li x-show="!target.c_moon_down">• 🌕 Observation sans contrainte lunaire</li>
                    <li x-show="target.ha_start || target.ha_end">
                        • Angle horaire: <span x-text="(target.ha_start || 'auto') + ' → ' + (target.ha_end || 'auto')"></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

</div>
