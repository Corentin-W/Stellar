{{-- Step 0: Import depuis Voyager --}}
<div class="space-y-5">

    {{-- Header --}}
    <div class="text-center">
        <div class="text-5xl mb-3">🚀</div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            Importer depuis Voyager
        </h2>
        <p class="text-sm text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Sélectionnez un Set ou une Target existante depuis votre installation Voyager
        </p>
    </div>

    {{-- Loading State --}}
    <div x-show="loadingVoyagerSets" class="text-center py-12">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-4 border-blue-600 border-t-transparent"></div>
        <p class="text-gray-600 dark:text-gray-400 mt-4">Chargement des Sets Voyager...</p>
    </div>

    {{-- Error State --}}
    <div x-show="voyagerError && !loadingVoyagerSets" class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
            </svg>
            <div class="flex-1">
                <p class="font-semibold text-red-800 dark:text-red-200">Erreur de connexion à Voyager</p>
                <p class="text-sm text-red-700 dark:text-red-300 mt-1" x-text="voyagerError"></p>
            </div>
        </div>
    </div>

    {{-- Sets List --}}
    <div x-show="!loadingVoyagerSets && !voyagerError && voyagerSets.length > 0">
        <div class="mb-4">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white mb-2">
                📁 Vos Sets Voyager (<span x-text="voyagerSets.length"></span>)
            </h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <template x-for="set in voyagerSets" :key="set.guid">
                <button @click="importFromVoyagerSet(set)"
                    class="text-left p-4 bg-gradient-to-br from-purple-50 to-indigo-50 dark:from-purple-900/20 dark:to-indigo-900/20 border-2 border-purple-200 dark:border-purple-700 rounded-xl hover:shadow-lg hover:scale-105 transition-all duration-200">

                    <div class="flex items-start justify-between mb-2">
                        <div class="flex-1">
                            <h4 class="font-bold text-gray-900 dark:text-white" x-text="set.setname"></h4>
                            <p class="text-xs text-gray-600 dark:text-gray-400 mt-0.5">
                                <span x-text="set.profilename"></span>
                                <template x-if="set.isdefault">
                                    <span class="ml-2 px-1.5 py-0.5 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs rounded">Défaut</span>
                                </template>
                            </p>
                        </div>
                        <div class="flex-shrink-0">
                            <template x-if="set.status === 0">
                                <span class="px-2 py-1 bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200 text-xs rounded-full">Actif</span>
                            </template>
                            <template x-if="set.status === 1">
                                <span class="px-2 py-1 bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 text-xs rounded-full">Inactif</span>
                            </template>
                        </div>
                    </div>

                    <template x-if="set.note">
                        <p class="text-xs text-gray-500 dark:text-gray-400 italic" x-text="set.note"></p>
                    </template>

                    <div class="mt-3 flex items-center justify-between text-xs">
                        <span class="text-gray-600 dark:text-gray-400">
                            <span x-text="set.targetsCount || 0"></span> target(s)
                        </span>
                        <svg class="w-4 h-4 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </button>
            </template>
        </div>
    </div>

    {{-- Empty State --}}
    <div x-show="!loadingVoyagerSets && !voyagerError && voyagerSets.length === 0" class="text-center py-12 bg-gray-50 dark:bg-gray-800/50 rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-700">
        <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 13h6m-3-3v6m-6 1a9 9 0 1118 0H3z"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 font-medium mb-2">
            Aucun Set trouvé dans Voyager
        </p>
        <p class="text-sm text-gray-400 dark:text-gray-500">
            Créez d'abord des Sets dans Voyager ou utilisez la configuration manuelle
        </p>
    </div>

    {{-- Actions --}}
    <div class="flex items-center justify-between pt-4 border-t border-gray-200 dark:border-gray-700">
        <button @click="currentStep = 1" type="button"
            class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 rounded-lg transition-colors">
            ✏️ Configuration manuelle plutôt
        </button>

        <button @click="loadVoyagerSets()" type="button"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
            🔄 Recharger
        </button>
    </div>

</div>
