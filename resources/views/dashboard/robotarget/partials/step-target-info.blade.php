{{-- Step 1: Target Information --}}
<div class="space-y-6">
    <h2 class="text-2xl font-bold text-gray-900 dark:text-white">
        Informations de la cible
    </h2>

    {{-- Target Name --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Nom de la cible *
        </label>
        <input
            type="text"
            x-model="target.target_name"
            placeholder="Ex: M31 - Galaxie d'Andromède"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
        />
        <p class="mt-1 text-sm text-gray-500">Un nom descriptif pour identifier votre cible</p>
    </div>

    {{-- RA J2000 --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Ascension Droite (RA J2000) *
        </label>
        <input
            type="text"
            x-model="target.ra_j2000"
            placeholder="HH:MM:SS (ex: 00:42:44)"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white font-mono"
        />
        <p class="mt-1 text-sm text-gray-500">Format: HH:MM:SS (heures:minutes:secondes)</p>
    </div>

    {{-- DEC J2000 --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Déclinaison (DEC J2000) *
        </label>
        <input
            type="text"
            x-model="target.dec_j2000"
            placeholder="±DD:MM:SS (ex: +41:16:09)"
            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white font-mono"
        />
        <p class="mt-1 text-sm text-gray-500">Format: ±DD:MM:SS (degrés:arcmin:arcsec)</p>
    </div>

    {{-- Priority --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
            Priorité
        </label>
        <div class="space-y-3">
            <template x-for="priority in [0, 1, 2, 3, 4]" :key="priority">
                <label class="flex items-center p-3 border rounded-lg cursor-pointer transition"
                    :class="[
                        target.priority === priority ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20' : 'border-gray-300 dark:border-gray-600',
                        !canUsePriority(priority) ? 'opacity-50 cursor-not-allowed' : ''
                    ]">
                    <input
                        type="radio"
                        :value="priority"
                        x-model="target.priority"
                        :disabled="!canUsePriority(priority)"
                        class="mr-3"
                    />
                    <div class="flex-1">
                        <span class="font-semibold" x-text="getPriorityLabel(priority)"></span>
                        <span class="text-sm text-gray-500 ml-2" x-show="!canUsePriority(priority)">
                            (Requiert un plan supérieur)
                        </span>
                    </div>
                    <span class="text-sm font-medium text-gray-600">
                        Multiplicateur: <span x-text="'×' + [1.0, 1.0, 1.2, 2.0, 3.0][priority]"></span>
                    </span>
                </label>
            </template>
        </div>
        <p class="mt-2 text-sm text-gray-500">
            Une priorité plus élevée augmente les chances d'exécution mais coûte plus de crédits
        </p>
    </div>

    {{-- Help Box --}}
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex items-start">
            <svg class="w-5 h-5 text-blue-600 mr-2 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-blue-800 dark:text-blue-200">
                <p class="font-semibold mb-1">💡 Astuce</p>
                <p>Utilisez Stellarium, SkySafari ou SIMBAD pour obtenir les coordonnées précises de votre cible en J2000.</p>
            </div>
        </div>
    </div>
</div>
