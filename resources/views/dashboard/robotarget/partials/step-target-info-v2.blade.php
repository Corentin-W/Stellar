{{-- Step 1: Target Information - Version Compact & Moderne --}}
<div class="space-y-5">

    {{-- Header avec aide --}}
    <div class="flex items-start justify-between">
        <div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                🎯 Votre Cible
            </h2>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                Renseignez les coordonnées de l'objet céleste que vous souhaitez photographier
            </p>
        </div>

        {{-- Tooltip global --}}
        <div x-data="{ showHelp: false }" class="relative">
            <button @click="showHelp = !showHelp"
                class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 hover:bg-blue-200 dark:hover:bg-blue-900/50 transition-colors flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </button>

            <div x-show="showHelp" @click.away="showHelp = false"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                class="absolute right-0 mt-2 w-72 bg-white dark:bg-gray-700 rounded-lg shadow-xl border border-gray-200 dark:border-gray-600 p-4 z-10">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">💡 Comment trouver les coordonnées?</h4>
                <ul class="text-sm text-gray-600 dark:text-gray-300 space-y-2">
                    <li>• Utilisez <a href="https://stellarium-web.org/" target="_blank" class="text-blue-600 hover:underline">Stellarium</a> ou <a href="http://simbad.u-strasbg.fr/simbad/" target="_blank" class="text-blue-600 hover:underline">SIMBAD</a></li>
                    <li>• RA: Heures:Minutes:Secondes</li>
                    <li>• DEC: Degrés:Minutes:Secondes</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Nom de la target --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Nom de la cible
            <span class="text-red-500">*</span>
        </label>
        <input type="text"
            x-model="target.target_name"
            placeholder="ex: M31 Andromeda, NGC 7000..."
            class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white placeholder-gray-400 text-sm"
            required>
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Donnez un nom facilement reconnaissable à votre cible
        </p>
    </div>

    {{-- Coordonnées - Grid compact --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">

        {{-- RA (Ascension Droite) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                <span>RA (Ascension Droite)</span>
                <span class="text-red-500">*</span>
                <div x-data="{ show: false }" class="relative">
                    <button @mouseenter="show = true" @mouseleave="show = false" type="button"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="show" @click.away="show = false"
                        class="absolute left-0 mt-1 w-48 bg-gray-900 text-white text-xs rounded-lg p-2 z-10 shadow-lg">
                        Format: HH:MM:SS<br>
                        Exemple: 00:42:44
                    </div>
                </div>
            </label>
            <input type="text"
                x-model="target.ra_j2000"
                placeholder="HH:MM:SS (ex: 00:42:44)"
                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white placeholder-gray-400 text-sm font-mono"
                required>
        </div>

        {{-- DEC (Déclinaison) --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2 flex items-center gap-1">
                <span>DEC (Déclinaison)</span>
                <span class="text-red-500">*</span>
                <div x-data="{ show: false }" class="relative">
                    <button @mouseenter="show = true" @mouseleave="show = false" type="button"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                    <div x-show="show" @click.away="show = false"
                        class="absolute left-0 mt-1 w-52 bg-gray-900 text-white text-xs rounded-lg p-2 z-10 shadow-lg">
                        Format: ±DD:MM:SS<br>
                        Exemple: +41:16:09
                    </div>
                </div>
            </label>
            <input type="text"
                x-model="target.dec_j2000"
                placeholder="±DD:MM:SS (ex: +41:16:09)"
                class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white placeholder-gray-400 text-sm font-mono"
                required>
        </div>
    </div>

    {{-- Info supplémentaires - Optionnelles et repliables --}}
    <div x-data="{ expanded: false }" class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <button @click="expanded = !expanded" type="button"
            class="flex items-center justify-between w-full text-left">
            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                Informations complémentaires (optionnel)
            </span>
            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="expanded && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="expanded" x-collapse class="mt-4 space-y-4">

            {{-- Type d'objet --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Type d'objet
                </label>
                <select x-model="target.object_type"
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 text-gray-900 dark:text-white text-sm">
                    <option value="">Non spécifié</option>
                    <option value="galaxy">🌌 Galaxie</option>
                    <option value="nebula">🌈 Nébuleuse</option>
                    <option value="cluster">⭐ Amas stellaire</option>
                    <option value="planet">🪐 Planète</option>
                    <option value="other">🔭 Autre</option>
                </select>
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                    Notes personnelles
                </label>
                <textarea x-model="target.notes"
                    rows="3"
                    placeholder="Ajoutez vos remarques, objectifs, contexte..."
                    class="w-full px-3 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all text-gray-900 dark:text-white placeholder-gray-400 text-sm resize-none"></textarea>
            </div>
        </div>
    </div>

    {{-- Recherche rapide (future feature) --}}
    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/10 dark:to-indigo-900/10 rounded-lg p-4 border border-blue-200 dark:border-blue-800">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white mb-1">
                    🚀 Recherche intelligente (bientôt)
                </h4>
                <p class="text-xs text-gray-600 dark:text-gray-400">
                    Tapez simplement "M31" et on remplira automatiquement toutes les coordonnées pour vous !
                </p>
            </div>
        </div>
    </div>

</div>
