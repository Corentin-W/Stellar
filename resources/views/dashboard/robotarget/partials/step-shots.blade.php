{{-- Step 3: Shot Configuration --}}
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Configuration des acquisitions
    </h2>

    <p class="text-gray-600 dark:text-gray-400">
        Définissez les filtres, expositions et nombre de poses pour votre cible.
    </p>

    {{-- Add Shot Form --}}
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-6 border-2 border-dashed border-blue-300 dark:border-gray-600">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
            ➕ Ajouter une acquisition
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Filter --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Filtre
                </label>
                <select
                    x-model="currentShot.filter_index"
                    @change="updateFilterName()"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                >
                    <template x-for="filter in filterOptions" :key="filter.index">
                        <option :value="filter.index" x-text="filter.name"></option>
                    </template>
                </select>
            </div>

            {{-- Exposure --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Exposition (s)
                </label>
                <input
                    type="number"
                    x-model.number="currentShot.exposure"
                    min="0.1"
                    max="3600"
                    step="10"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                />
            </div>

            {{-- Number of shots --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Nombre
                </label>
                <input
                    type="number"
                    x-model.number="currentShot.num"
                    min="1"
                    max="1000"
                    step="1"
                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-lg dark:bg-gray-700 dark:text-white"
                />
            </div>

            {{-- Add Button --}}
            <div class="flex items-end">
                <button
                    @click="addShot()"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold"
                >
                    ➕ Ajouter
                </button>
            </div>
        </div>

        {{-- Quick presets --}}
        <div class="mt-4 pt-4 border-t border-blue-200 dark:border-gray-600">
            <p class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Presets rapides:</p>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
                <button
                    @click="currentShot = { filter_index: 0, filter_name: 'Luminance', exposure: 300, num: 20, gain: 100, offset: 50, bin: 1 }"
                    class="px-3 py-2 bg-white dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm"
                >
                    Luminance 5m ×20
                </button>
                <button
                    @click="currentShot = { filter_index: 1, filter_name: 'Red', exposure: 180, num: 10, gain: 100, offset: 50, bin: 1 }"
                    class="px-3 py-2 bg-white dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm"
                >
                    Rouge 3m ×10
                </button>
                <button
                    @click="currentShot = { filter_index: 4, filter_name: 'Ha', exposure: 600, num: 15, gain: 100, offset: 50, bin: 1 }"
                    class="px-3 py-2 bg-white dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm"
                >
                    Ha 10m ×15
                </button>
                <button
                    @click="currentShot = { filter_index: 5, filter_name: 'OIII', exposure: 600, num: 15, gain: 100, offset: 50, bin: 1 }"
                    class="px-3 py-2 bg-white dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 text-sm"
                >
                    OIII 10m ×15
                </button>
            </div>
        </div>
    </div>

    {{-- Shots List --}}
    <div x-show="target.shots.length > 0">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">
            📋 Acquisitions planifiées (<span x-text="target.shots.length"></span>)
        </h3>

        <div class="space-y-3">
            <template x-for="(shot, index) in target.shots" :key="index">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-300 dark:border-gray-600 p-4 hover:shadow-md transition">
                    <div class="flex items-center justify-between">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Filtre</p>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="shot.filter_name"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Exposition</p>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="formatTime(shot.exposure)"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Nombre</p>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="shot.num + ' poses'"></p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">Durée totale</p>
                                <p class="font-semibold text-blue-600" x-text="formatTime(shot.exposure * shot.num)"></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2 ml-4">
                            <button
                                @click="editShot(index)"
                                class="p-2 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                title="Modifier"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button
                                @click="removeShot(index)"
                                class="p-2 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                title="Supprimer"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totals --}}
        <div class="mt-6 bg-gradient-to-r from-green-50 to-blue-50 dark:from-gray-800 dark:to-gray-700 rounded-lg p-4 border border-green-200 dark:border-gray-600">
            <h4 class="font-semibold text-gray-900 dark:text-white mb-3">
                📊 Totaux
            </h4>
            <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Total images</p>
                    <p class="text-2xl font-bold text-green-600" x-text="getTotalImages()"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Temps d'exposition</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="formatTime(getTotalExposureTime())"></p>
                </div>
                <div>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Temps estimé total</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="formatDuration(getTotalExposureTime() / 3600 * 1.3)"></p>
                    <p class="text-xs text-gray-500">(avec overhead 30%)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State --}}
    <div x-show="target.shots.length === 0" class="text-center py-12">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 text-lg">
            Aucune acquisition configurée
        </p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mt-1">
            Ajoutez au moins une acquisition pour continuer
        </p>
    </div>

    {{-- Help Box --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">💡 Conseils</p>
                <ul class="list-disc list-inside space-y-1">
                    <li>Pour RGB: utilisez des temps d'exposition similaires pour chaque canal (ex: 3min par filtre)</li>
                    <li>Pour narrowband (Ha/OIII/SII): utilisez des expositions plus longues (10-15min par pose)</li>
                    <li>La luminance nécessite généralement 50% du temps total d'acquisition</li>
                    <li>Un overhead de ~30% est ajouté pour les changements de filtres et calibrations</li>
                </ul>
            </div>
        </div>
    </div>
</div>
