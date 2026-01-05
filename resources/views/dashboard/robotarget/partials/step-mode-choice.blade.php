{{-- Step 0: Choix du Mode --}}
<div class="space-y-6">

    {{-- Header --}}
    <div class="text-center mb-8">
        <div class="text-6xl mb-4">🌌</div>
        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-3">
            Comment souhaitez-vous créer votre Target ?
        </h2>
        <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
            Choisissez le mode qui correspond le mieux à votre niveau d'expertise
        </p>
    </div>

    {{-- Mode Selection Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-5xl mx-auto">

        {{-- Mode Assisté --}}
        <button @click="creationMode = 'assisted'; loadAssistedTargets();" type="button"
            class="group relative bg-gradient-to-br from-blue-50 via-indigo-50 to-purple-50 dark:from-blue-900/20 dark:via-indigo-900/20 dark:to-purple-900/20 border-3 border-blue-300 dark:border-blue-700 rounded-2xl p-8 hover:shadow-2xl hover:scale-105 transition-all duration-300 text-left overflow-hidden">

            {{-- Badge Recommandé --}}
            <div class="absolute top-4 right-4">
                <span class="px-3 py-1 bg-blue-600 text-white text-xs font-bold rounded-full shadow-lg">
                    ⭐ Recommandé
                </span>
            </div>

            {{-- Icon --}}
            <div class="text-6xl mb-4">🎨</div>

            {{-- Title --}}
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                Mode Assisté
            </h3>

            {{-- Description --}}
            <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                Choisissez parmi notre <strong>catalogue d'objets célestes</strong> avec photos et descriptions.
                Vous réglez uniquement les <strong>paramètres de qualité</strong> selon votre budget.
            </p>

            {{-- Features --}}
            <ul class="space-y-2 mb-6">
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Objets pré-configurés avec photos haute qualité</span>
                </li>
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Guidage pas à pas pour la qualité d'image</span>
                </li>
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-green-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Contrôle précis du budget en crédits</span>
                </li>
            </ul>

            {{-- CTA --}}
            <div class="flex items-center justify-between pt-4 border-t border-blue-200 dark:border-blue-700">
                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">Idéal pour débuter</span>
                <svg class="w-6 h-6 text-blue-600 dark:text-blue-400 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </button>

        {{-- Mode Manuel --}}
        <button @click="creationMode = 'manual'; currentStep = 1;" type="button"
            class="group relative bg-gradient-to-br from-orange-50 via-red-50 to-pink-50 dark:from-orange-900/20 dark:via-red-900/20 dark:to-pink-900/20 border-3 border-orange-300 dark:border-orange-700 rounded-2xl p-8 hover:shadow-2xl hover:scale-105 transition-all duration-300 text-left overflow-hidden">

            {{-- Badge Expert --}}
            <div class="absolute top-4 right-4">
                <span class="px-3 py-1 bg-orange-600 text-white text-xs font-bold rounded-full shadow-lg">
                    🔥 Expert
                </span>
            </div>

            {{-- Icon --}}
            <div class="text-6xl mb-4">⚙️</div>

            {{-- Title --}}
            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
                Mode Manuel
            </h3>

            {{-- Description --}}
            <p class="text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                Créez votre target de A à Z avec un <strong>contrôle total</strong> sur tous les paramètres techniques Voyager.
                Pour les utilisateurs expérimentés.
            </p>

            {{-- Features --}}
            <ul class="space-y-2 mb-6">
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Coordonnées personnalisées (RA/DEC)</span>
                </li>
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Configuration avancée (Gain, Offset, Binning)</span>
                </li>
                <li class="flex items-start gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <svg class="w-5 h-5 text-orange-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Contraintes environnementales complètes</span>
                </li>
            </ul>

            {{-- CTA --}}
            <div class="flex items-center justify-between pt-4 border-t border-orange-200 dark:border-orange-700">
                <span class="text-sm font-medium text-orange-600 dark:text-orange-400">Maximum de contrôle</span>
                <svg class="w-6 h-6 text-orange-600 dark:text-orange-400 group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </div>
        </button>

    </div>

    {{-- Info Box --}}
    <div class="max-w-3xl mx-auto mt-8 bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
        <div class="flex gap-4">
            <div class="flex-shrink-0 text-3xl">💡</div>
            <div class="flex-1">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Quelle différence entre les deux modes ?</h4>
                <div class="text-sm text-gray-600 dark:text-gray-400 space-y-2">
                    <p>
                        <strong class="text-blue-600">Mode Assisté :</strong> Nous avons pré-configuré des objets célestes populaires avec les meilleurs paramètres.
                        Vous choisissez simplement la qualité d'image souhaitée, et le prix s'ajuste automatiquement.
                    </p>
                    <p>
                        <strong class="text-orange-600">Mode Manuel :</strong> Vous maîtrisez Voyager et souhaitez configurer précisément chaque paramètre (filtres, temps de pose, contraintes atmosphériques, etc.).
                        Vous avez un contrôle total mais devez connaître les aspects techniques.
                    </p>
                </div>
            </div>
        </div>
    </div>

</div>
