{{-- Mode Assisté - Step 1: Catalogue des Targets --}}
<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                📸 Choisissez votre objet céleste
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Sélectionnez parmi notre catalogue d'objets pré-configurés
            </p>
        </div>
        <button @click="creationMode = null; currentStep = 0" type="button"
            class="text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">
            ← Changer de mode
        </button>
    </div>

    {{-- Filters --}}
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4 mb-6">
        <div class="flex flex-wrap gap-3">
            <button @click="catalogFilter = 'all'" type="button"
                :class="catalogFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                Tous
            </button>
            <button @click="catalogFilter = 'beginner'" type="button"
                :class="catalogFilter === 'beginner' ? 'bg-green-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                🌟 Débutant
            </button>
            <button @click="catalogFilter = 'intermediate'" type="button"
                :class="catalogFilter === 'intermediate' ? 'bg-orange-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                ⭐ Intermédiaire
            </button>
            <button @click="catalogFilter = 'advanced'" type="button"
                :class="catalogFilter === 'advanced' ? 'bg-red-600 text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                🔥 Avancé
            </button>
        </div>
    </div>

    {{-- Loading State --}}
    <div x-show="loadingCatalog" class="text-center py-16">
        <div class="inline-block animate-spin rounded-full h-16 w-16 border-4 border-blue-600 border-t-transparent"></div>
        <p class="text-gray-600 dark:text-gray-400 mt-4 font-medium">Chargement du catalogue...</p>
    </div>

    {{-- Catalog Grid --}}
    <div x-show="!loadingCatalog" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <template x-for="template in getFilteredCatalog()" :key="template.id">
            <div @click="selectAssistedTarget(template)"
                class="group cursor-pointer bg-white dark:bg-gray-800 rounded-xl overflow-hidden border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-500 hover:shadow-2xl transition-all duration-300">

                {{-- Image --}}
                <div class="relative h-48 bg-gray-900 overflow-hidden">
                    <img :src="template.preview_image || '/images/placeholder-space.jpg'"
                         :alt="template.name"
                         class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">

                    {{-- Overlay gradient --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>

                    {{-- Difficulty Badge --}}
                    <div class="absolute top-3 right-3">
                        <span :class="{
                            'bg-green-500': template.difficulty === 'beginner',
                            'bg-orange-500': template.difficulty === 'intermediate',
                            'bg-red-500': template.difficulty === 'advanced'
                        }" class="px-2 py-1 text-white text-xs font-bold rounded-full shadow-lg">
                            <span x-text="template.difficulty === 'beginner' ? '🌟 Débutant' : template.difficulty === 'intermediate' ? '⭐ Inter.' : '🔥 Avancé'"></span>
                        </span>
                    </div>

                    {{-- Title overlay --}}
                    <div class="absolute bottom-3 left-3 right-3">
                        <h3 class="text-white font-bold text-lg drop-shadow-lg" x-text="template.name"></h3>
                        <p class="text-white/90 text-xs mt-1" x-text="template.type + ' • ' + template.constellation"></p>
                    </div>
                </div>

                {{-- Content --}}
                <div class="p-4">
                    {{-- Description --}}
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-3 line-clamp-2" x-text="template.short_description"></p>

                    {{-- Info badges --}}
                    <div class="flex flex-wrap gap-2 mb-3">
                        <template x-if="template.best_months">
                            <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 text-xs rounded-full">
                                📅 <span x-text="template.best_months.join(', ')"></span>
                            </span>
                        </template>
                        <template x-if="template.estimated_time">
                            <span class="px-2 py-1 bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 text-xs rounded-full">
                                ⏱️ <span x-text="template.estimated_time"></span>
                            </span>
                        </template>
                    </div>

                    {{-- CTA --}}
                    <div class="flex items-center justify-between pt-3 border-t border-gray-200 dark:border-gray-700">
                        <span class="text-xs text-gray-500 dark:text-gray-400">Cliquez pour sélectionner</span>
                        <svg class="w-5 h-5 text-blue-600 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- Empty State --}}
    <div x-show="!loadingCatalog && getFilteredCatalog().length === 0"
         class="text-center py-16 bg-gray-50 dark:bg-gray-800/50 rounded-xl">
        <svg class="w-20 h-20 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
        </svg>
        <p class="text-gray-500 dark:text-gray-400 text-lg font-medium">Aucun objet trouvé</p>
        <p class="text-gray-400 dark:text-gray-500 text-sm mt-2">Essayez de changer les filtres</p>
    </div>

</div>
