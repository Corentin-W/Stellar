{{-- Step 3: Shot Configuration - Version Compact & Visuelle --}}
<div class="space-y-5">

    {{-- Header --}}
    <div>
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            📸 Plan d'acquisition
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
            Définissez vos filtres, temps de pose et quantité d'images
        </p>
    </div>

    {{-- Quick Presets - En haut pour plus de visibilité --}}
    <div class="bg-gradient-to-br from-purple-50 to-pink-50 dark:from-purple-900/10 dark:to-pink-900/10 rounded-xl p-4 border border-purple-100 dark:border-purple-800">
        <div class="flex items-center gap-2 mb-3">
            <span class="text-lg">⚡</span>
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                Presets rapides
            </h3>
        </div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
            <button type="button"
                @click="currentShot = { filter_index: 0, filter_name: 'Luminance', exposure: 300, num: 20, gain: 100, offset: 50, bin: 1 }"
                class="px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 transition-all text-xs font-medium">
                <div class="flex items-center justify-center gap-1">
                    <span class="w-2 h-2 bg-gray-400 rounded-full"></span>
                    <span>L 5m ×20</span>
                </div>
            </button>
            <button type="button"
                @click="currentShot = { filter_index: 1, filter_name: 'Red', exposure: 180, num: 10, gain: 100, offset: 50, bin: 1 }"
                class="px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all text-xs font-medium">
                <div class="flex items-center justify-center gap-1">
                    <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                    <span>R 3m ×10</span>
                </div>
            </button>
            <button type="button"
                @click="currentShot = { filter_index: 4, filter_name: 'Ha', exposure: 600, num: 15, gain: 100, offset: 50, bin: 1 }"
                class="px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 transition-all text-xs font-medium">
                <div class="flex items-center justify-center gap-1">
                    <span class="w-2 h-2 bg-red-700 rounded-full"></span>
                    <span>Hα 10m ×15</span>
                </div>
            </button>
            <button type="button"
                @click="currentShot = { filter_index: 5, filter_name: 'OIII', exposure: 600, num: 15, gain: 100, offset: 50, bin: 1 }"
                class="px-3 py-2 bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-cyan-400 hover:bg-cyan-50 dark:hover:bg-cyan-900/20 transition-all text-xs font-medium">
                <div class="flex items-center justify-center gap-1">
                    <span class="w-2 h-2 bg-cyan-500 rounded-full"></span>
                    <span>OIII 10m ×15</span>
                </div>
            </button>
        </div>
    </div>

    {{-- Add Shot Form - Compact --}}
    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-xl p-5 border border-blue-100 dark:border-blue-800">
        <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
            ➕ Ajouter une acquisition
        </h3>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            {{-- Filter --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5 flex items-center gap-1">
                    Filtre
                    <div x-data="{ show: false }" class="relative">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="show" @click.away="show = false"
                            class="absolute left-0 mt-1 w-56 bg-gray-900 text-white text-xs rounded-lg p-3 z-10 shadow-lg">
                            <strong>Filtres disponibles:</strong><br>
                            L/R/G/B pour RGB<br>
                            Ha/OIII/SII pour narrowband
                        </div>
                    </div>
                </label>
                <select
                    x-model="currentShot.filter_index"
                    @change="updateFilterName()"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500">
                    <template x-for="filter in filterOptions" :key="filter.index">
                        <option :value="filter.index" x-text="filter.name"></option>
                    </template>
                </select>
            </div>

            {{-- Exposure --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5 flex items-center gap-1">
                    Exposition (s)
                    <div x-data="{ show: false }" class="relative">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="show" @click.away="show = false"
                            class="absolute left-0 mt-1 w-48 bg-gray-900 text-white text-xs rounded-lg p-3 z-10 shadow-lg">
                            RGB: 180-300s<br>
                            Narrowband: 600-900s<br>
                            Luminance: 300-600s
                        </div>
                    </div>
                </label>
                <input
                    type="number"
                    x-model.number="currentShot.exposure"
                    min="0.1"
                    max="3600"
                    step="10"
                    placeholder="300"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                />
            </div>

            {{-- Number of shots --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5 flex items-center gap-1">
                    Nombre de poses
                    <div x-data="{ show: false }" class="relative">
                        <button @mouseenter="show = true" @mouseleave="show = false" type="button"
                            class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                        <div x-show="show" @click.away="show = false"
                            class="absolute left-0 mt-1 w-44 bg-gray-900 text-white text-xs rounded-lg p-3 z-10 shadow-lg">
                            Minimum recommandé: 10 poses par filtre
                        </div>
                    </div>
                </label>
                <input
                    type="number"
                    x-model.number="currentShot.num"
                    min="1"
                    max="1000"
                    step="1"
                    placeholder="20"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg text-sm focus:ring-2 focus:ring-blue-500"
                />
            </div>

            {{-- Add Button --}}
            <div class="flex items-end">
                <button type="button"
                    @click="addShot()"
                    class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-semibold text-sm flex items-center justify-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Ajouter
                </button>
            </div>
        </div>

        {{-- Dynamic hint based on current filter --}}
        <div class="mt-3 p-3 bg-white/50 dark:bg-gray-800/30 rounded-lg">
            <p class="text-xs text-gray-600 dark:text-gray-300">
                <template x-if="currentShot.filter_name?.includes('Luminance')">
                    <span>💡 <strong>Luminance:</strong> Représente ~50% du temps total. Utilisez des poses de 5-10 minutes.</span>
                </template>
                <template x-if="currentShot.filter_name?.includes('Red') || currentShot.filter_name?.includes('Green') || currentShot.filter_name?.includes('Blue')">
                    <span>🎨 <strong>RGB:</strong> Temps d'exposition équilibrés entre R/G/B (3-5 minutes par pose).</span>
                </template>
                <template x-if="currentShot.filter_name?.includes('Ha') || currentShot.filter_name?.includes('OIII') || currentShot.filter_name?.includes('SII')">
                    <span>🌈 <strong>Narrowband:</strong> Poses longues recommandées (10-15 minutes) pour capturer les faibles émissions.</span>
                </template>
            </p>
        </div>
    </div>

    {{-- Shots List --}}
    <div x-show="target.shots.length > 0">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">
                📋 Acquisitions planifiées (<span x-text="target.shots.length"></span>)
            </h3>
        </div>

        <div class="space-y-2">
            <template x-for="(shot, index) in target.shots" :key="index">
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-3 hover:border-blue-300 dark:hover:border-blue-600 transition-all">
                    <div class="flex items-center justify-between gap-3">
                        {{-- Shot Info - Compact Grid --}}
                        <div class="flex-1 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-0.5">Filtre</p>
                                <p class="font-semibold text-gray-900 dark:text-white flex items-center gap-1">
                                    <span class="w-2 h-2 rounded-full"
                                        :class="{
                                            'bg-gray-400': shot.filter_name?.includes('Luminance'),
                                            'bg-red-500': shot.filter_name?.includes('Red') || shot.filter_name?.includes('Ha'),
                                            'bg-green-500': shot.filter_name?.includes('Green'),
                                            'bg-blue-500': shot.filter_name?.includes('Blue'),
                                            'bg-cyan-500': shot.filter_name?.includes('OIII'),
                                            'bg-yellow-500': shot.filter_name?.includes('SII')
                                        }"></span>
                                    <span x-text="shot.filter_name"></span>
                                </p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-0.5">Exposition</p>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="formatTime(shot.exposure)"></p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-0.5">Poses</p>
                                <p class="font-semibold text-gray-900 dark:text-white" x-text="shot.num"></p>
                            </div>
                            <div>
                                <p class="text-gray-500 dark:text-gray-400 mb-0.5">Durée totale</p>
                                <p class="font-semibold text-blue-600" x-text="formatTime(shot.exposure * shot.num)"></p>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-1">
                            <button type="button"
                                @click="editShot(index)"
                                class="p-1.5 text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-900/20 rounded transition"
                                title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button type="button"
                                @click="removeShot(index)"
                                class="p-1.5 text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded transition"
                                title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totals - Compact Version --}}
        <div class="mt-4 bg-gradient-to-r from-green-50 to-blue-50 dark:from-green-900/10 dark:to-blue-900/10 rounded-xl p-4 border border-green-200 dark:border-green-800">
            <div class="flex items-center gap-2 mb-3">
                <span class="text-lg">📊</span>
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Totaux</h4>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Total images</p>
                    <p class="text-2xl font-bold text-green-600" x-text="getTotalImages()"></p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Temps exposition</p>
                    <p class="text-2xl font-bold text-blue-600" x-text="formatTime(getTotalExposureTime())"></p>
                </div>
                <div class="text-center">
                    <p class="text-xs text-gray-600 dark:text-gray-400 mb-1">Temps total</p>
                    <p class="text-2xl font-bold text-purple-600" x-text="formatDuration(getTotalExposureTime() / 3600 * 1.3)"></p>
                    <p class="text-xs text-gray-500 dark:text-gray-400">(+30% overhead)</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Empty State - Compact --}}
    <div x-show="target.shots.length === 0" class="text-center py-8 bg-gray-50 dark:bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
        <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m5 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 font-medium">
            Aucune acquisition configurée
        </p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">
            Utilisez les presets ou ajoutez manuellement vos acquisitions
        </p>
    </div>

    {{-- Help Box - Compact --}}
    <div class="bg-blue-50 dark:bg-blue-900/10 rounded-lg p-4 border-l-4 border-blue-600">
        <div class="flex gap-3">
            <div class="flex-shrink-0 text-xl">
                💡
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                    Conseils pour vos acquisitions
                </h4>
                <ul class="text-xs text-gray-600 dark:text-gray-400 space-y-1">
                    <li>• <strong>RGB:</strong> Temps d'exposition similaires pour chaque canal (3-5min)</li>
                    <li>• <strong>Narrowband:</strong> Expositions plus longues (10-15min par pose)</li>
                    <li>• <strong>Luminance:</strong> Représente ~50% du temps total d'acquisition</li>
                    <li>• <strong>Overhead:</strong> ~30% ajouté pour changements de filtres et calibrations</li>
                </ul>
            </div>
        </div>
    </div>

</div>
