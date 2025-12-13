{{-- Step 2: Constraints --}}
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Contraintes d'observation
    </h2>

    <p class="text-gray-600 dark:text-gray-400">
        Définissez les conditions dans lesquelles votre cible peut être observée.
    </p>

    {{-- Altitude Minimale --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            📐 Altitude minimale (degrés)
        </label>
        <div class="flex items-center space-x-4">
            <input
                type="range"
                min="0"
                max="90"
                step="5"
                x-model="target.c_alt_min"
                class="flex-1"
            />
            <span class="text-2xl font-bold text-blue-600 w-16 text-right" x-text="target.c_alt_min + '°'"></span>
        </div>
        <p class="mt-2 text-sm text-gray-500">
            Angle minimum au-dessus de l'horizon (recommandé: 30°-40°)
        </p>
    </div>

    {{-- Moon Down Constraint --}}
    <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
        <label class="flex items-start cursor-pointer">
            <input
                type="checkbox"
                x-model="target.c_moon_down"
                class="mt-1 mr-3"
            />
            <div class="flex-1">
                <div class="flex items-center">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">
                        🌙 Lune couchée obligatoire
                    </span>
                    <span class="ml-2 px-2 py-0.5 bg-purple-100 dark:bg-purple-900 text-purple-800 dark:text-purple-200 text-xs font-semibold rounded">
                        Multiplicateur ×2.0
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    La cible ne sera observée que lorsque la lune est sous l'horizon.
                    Idéal pour les cibles faibles ou narrowband.
                </p>
                <div x-show="target.c_moon_down" class="mt-2 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded text-sm text-yellow-800 dark:text-yellow-200">
                    ⚠️ Cette contrainte réduit significativement les opportunités d'observation et double le coût en crédits.
                </div>
            </div>
        </label>
    </div>

    {{-- HFD Mean Limit --}}
    <div class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
        <label class="flex items-start cursor-pointer">
            <input
                type="checkbox"
                x-model.lazy="target.c_hfd_mean_limit"
                @change="if (!target.c_hfd_mean_limit) target.c_hfd_mean_limit = null; else target.c_hfd_mean_limit = 2.5;"
                class="mt-1 mr-3"
            />
            <div class="flex-1">
                <div class="flex items-center">
                    <span class="text-lg font-semibold text-gray-900 dark:text-white">
                        ⭐ Garantie qualité HFD (Half Flux Diameter)
                    </span>
                    <span class="ml-2 px-2 py-0.5 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs font-semibold rounded">
                        Multiplicateur ×1.5
                    </span>
                </div>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Garantit une qualité d'image maximale (étoiles fines). Les images avec un HFD supérieur à la limite seront rejetées et recommencées.
                </p>

                <div x-show="target.c_hfd_mean_limit !== null" class="mt-3 space-y-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                        Limite HFD maximale (pixels)
                    </label>
                    <div class="flex items-center space-x-4">
                        <input
                            type="range"
                            min="1.5"
                            max="4.0"
                            step="0.1"
                            x-model="target.c_hfd_mean_limit"
                            class="flex-1"
                        />
                        <span class="text-xl font-bold text-green-600 w-16 text-right" x-text="target.c_hfd_mean_limit + 'px'"></span>
                    </div>
                    <div class="grid grid-cols-3 gap-2 text-xs text-center mt-2">
                        <button @click="target.c_hfd_mean_limit = 2.0" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">
                            Excellent (2.0)
                        </button>
                        <button @click="target.c_hfd_mean_limit = 2.5" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">
                            Bon (2.5)
                        </button>
                        <button @click="target.c_hfd_mean_limit = 3.0" class="px-2 py-1 bg-gray-100 dark:bg-gray-700 rounded hover:bg-gray-200">
                            Correct (3.0)
                        </button>
                    </div>
                    <p class="text-xs text-gray-500 mt-2">
                        Plus la valeur est basse, plus les étoiles seront fines (seeing excellent requis).
                    </p>
                </div>
            </div>
        </label>
    </div>

    {{-- Summary Box --}}
    <div class="bg-gradient-to-r from-blue-50 to-purple-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-4 border border-blue-200 dark:border-gray-600">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-2">
            📊 Résumé des contraintes
        </h3>
        <div class="space-y-2 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Altitude minimale:</span>
                <span class="font-semibold text-gray-900 dark:text-white" x-text="target.c_alt_min + '°'"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Lune couchée:</span>
                <span class="font-semibold" :class="target.c_moon_down ? 'text-purple-600' : 'text-gray-500'" x-text="target.c_moon_down ? 'Oui (×2.0)' : 'Non'"></span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-600 dark:text-gray-400">Garantie HFD:</span>
                <span class="font-semibold" :class="target.c_hfd_mean_limit ? 'text-green-600' : 'text-gray-500'" x-text="target.c_hfd_mean_limit ? target.c_hfd_mean_limit + 'px (×1.5)' : 'Non'"></span>
            </div>
            <div class="border-t border-gray-300 dark:border-gray-600 pt-2 mt-2">
                <div class="flex justify-between">
                    <span class="text-gray-900 dark:text-white font-semibold">Multiplicateur total:</span>
                    <span class="text-lg font-bold text-blue-600" x-text="'×' + ((target.c_moon_down ? 2.0 : 1.0) * (target.c_hfd_mean_limit ? 1.5 : 1.0)).toFixed(1)"></span>
                </div>
            </div>
        </div>
    </div>

    {{-- Help Box --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">💡 Recommandations</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Pour du grand champ ou narrowband: privilégiez "Lune couchée"</li>
                    <li>Pour de l'astrophotographie planétaire: utilisez la garantie HFD avec une limite basse</li>
                    <li>Pour maximiser les chances d'observation: utilisez le minimum de contraintes</li>
                </ul>
            </div>
        </div>
    </div>
</div>
