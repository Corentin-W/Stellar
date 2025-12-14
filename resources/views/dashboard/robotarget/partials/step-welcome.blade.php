{{-- Step 0: Mode Sélection et Catalogue --}}
<div class="max-w-3xl mx-auto">
    <div class="text-center mb-8">
        <div class="text-6xl mb-4">🌌</div>
        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
            Bienvenue dans le Target Planner
        </h2>
        <p class="text-gray-600 dark:text-gray-400">
            Choisissez comment vous souhaitez créer votre cible d'observation
        </p>
    </div>

    {{-- Mode Selection --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
        {{-- Mode Assisté --}}
        <button
            @click="setMode('assisted')"
            class="group relative bg-gradient-to-br from-blue-50 to-purple-50 dark:from-blue-900/20 dark:to-purple-900/20 rounded-xl p-6 border-2 hover:border-blue-500 dark:hover:border-blue-400 transition-all cursor-pointer"
            :class="creationMode === 'assisted' ? 'border-blue-500 dark:border-blue-400 ring-4 ring-blue-500/20' : 'border-gray-200 dark:border-gray-700'"
        >
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/40 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mode Assisté</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Parfait pour les débutants
                </p>
                <ul class="text-xs text-left text-gray-700 dark:text-gray-300 space-y-1">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Catalogue d'objets populaires
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Paramètres préconfigurés
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Explications détaillées
                    </li>
                </ul>
            </div>
        </button>

        {{-- Mode Manuel --}}
        <button
            @click="setMode('manual')"
            class="group relative bg-gradient-to-br from-gray-50 to-slate-50 dark:from-gray-800 dark:to-slate-800 rounded-xl p-6 border-2 hover:border-purple-500 dark:hover:border-purple-400 transition-all cursor-pointer"
            :class="creationMode === 'manual' ? 'border-purple-500 dark:border-purple-400 ring-4 ring-purple-500/20' : 'border-gray-200 dark:border-gray-700'"
        >
            <div class="flex flex-col items-center text-center">
                <div class="w-16 h-16 rounded-full bg-purple-100 dark:bg-purple-900/40 flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Mode Manuel</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                    Pour les utilisateurs expérimentés
                </p>
                <ul class="text-xs text-left text-gray-700 dark:text-gray-300 space-y-1">
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Contrôle total des paramètres
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Coordonnées personnalisées
                    </li>
                    <li class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-purple-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        Configuration avancée
                    </li>
                </ul>
            </div>
        </button>
    </div>

    {{-- Catalogue d'objets (Mode Assisté) --}}
    <div x-show="creationMode === 'assisted'" x-transition class="mt-8">
        <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-4 text-center">
            📖 Choisissez votre cible
        </h3>

        {{-- Filtres par difficulté --}}
        <div class="flex justify-center gap-2 mb-6">
            <button
                @click="catalogFilter = 'all'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                Tous
            </button>
            <button
                @click="catalogFilter = 'beginner'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'beginner' ? 'bg-green-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                🌱 Débutant
            </button>
            <button
                @click="catalogFilter = 'intermediate'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'intermediate' ? 'bg-yellow-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                ⭐ Intermédiaire
            </button>
            <button
                @click="catalogFilter = 'advanced'"
                class="px-4 py-2 rounded-lg text-sm font-medium transition"
                :class="catalogFilter === 'advanced' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600'"
            >
                🚀 Avancé
            </button>
        </div>

        {{-- Liste des cibles --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto pr-2">
            <template x-for="target in getFilteredCatalog()" :key="target.id">
                <button
                    @click="loadTemplateTarget(target)"
                    class="text-left bg-white dark:bg-gray-800 rounded-lg p-4 border-2 border-gray-200 dark:border-gray-700 hover:border-blue-500 dark:hover:border-blue-400 transition-all group"
                >
                    <div class="flex items-start justify-between mb-2">
                        <div>
                            <h4 class="font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400" x-text="target.name"></h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400" x-text="target.type + ' • ' + target.constellation"></p>
                        </div>
                        <span
                            class="px-2 py-1 rounded text-xs font-medium"
                            :class="{
                                'bg-green-100 text-green-800': target.difficulty === 'beginner',
                                'bg-yellow-100 text-yellow-800': target.difficulty === 'intermediate',
                                'bg-red-100 text-red-800': target.difficulty === 'advanced'
                            }"
                            x-text="target.difficulty === 'beginner' ? 'Débutant' : target.difficulty === 'intermediate' ? 'Inter.' : 'Avancé'"
                        ></span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2" x-text="target.description"></p>
                    <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-gray-500">
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="target.estimated_time"></span>
                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <span x-text="target.best_months.slice(0, 3).join(', ')"></span>
                        </span>
                    </div>
                </button>
            </template>
        </div>

        <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-200">
                    <p class="font-semibold mb-1">💡 Conseil</p>
                    <p>Sélectionnez une cible du catalogue pour charger automatiquement ses coordonnées et les paramètres d'acquisition recommandés. Vous pourrez ensuite les personnaliser si nécessaire.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Bouton Continuer --}}
    <div class="mt-8 text-center">
        <button
            @click="startCreation()"
            :disabled="!creationMode"
            class="px-8 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition font-medium disabled:opacity-50 disabled:cursor-not-allowed"
        >
            <span x-show="creationMode === 'assisted'">Commencer avec le mode assisté →</span>
            <span x-show="creationMode === 'manual'">Créer manuellement →</span>
            <span x-show="!creationMode">Choisissez un mode pour continuer</span>
        </button>
    </div>
</div>
