<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test RoboTarget API - Stellar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gradient-to-br from-space-900 via-nebula-900 to-cosmic-900 text-gray-100 min-h-screen">
    <div class="container mx-auto px-4 py-8" x-data="robotargetTest()">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-star-400 to-nebula-400 mb-2">
                🤖 Test RoboTarget API
            </h1>
            <p class="text-gray-400">Interface de test pour créer et gérer des Sets, Targets et Shots</p>

            <!-- Explication générale -->
            <div class="mt-4 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                <h3 class="font-semibold text-blue-300 mb-2">ℹ️ Comment ça marche ?</h3>
                <ol class="text-sm text-gray-300 space-y-2 list-decimal list-inside">
                    <li><strong>Set</strong> = Un dossier qui contient toutes vos observations d'une soirée</li>
                    <li><strong>Target</strong> = Une cible dans le ciel (M31, M42, etc.) avec ses coordonnées exactes</li>
                    <li><strong>Shot</strong> = Une série de photos à prendre (20 photos de 300s en filtre Luminance, par exemple)</li>
                </ol>
                <p class="text-xs text-gray-400 mt-2">💡 Vous créez d'abord un Set, puis une Target dans ce Set, puis des Shots pour cette Target.</p>
            </div>

            <div class="mt-4 flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <div :class="proxyStatus === 'connected' ? 'bg-green-500' : 'bg-red-500'" class="w-3 h-3 rounded-full animate-pulse"></div>
                    <span class="text-sm" x-text="proxyStatus === 'connected' ? 'Proxy connecté' : 'Proxy déconnecté'"></span>
                </div>
                <div class="flex items-center gap-2">
                    <div :class="voyagerConnected ? 'bg-green-500' : 'bg-yellow-500'" class="w-3 h-3 rounded-full"></div>
                    <span class="text-sm" x-text="voyagerConnected ? 'Voyager connecté' : 'Voyager déconnecté'"></span>
                </div>
                <button @click="runDiagnostics" class="ml-auto px-4 py-2 bg-blue-500 hover:bg-blue-600 rounded-lg text-sm font-semibold transition">
                    🔍 Diagnostiquer
                </button>
            </div>
        </div>

        <!-- Section Configuration -->
        <div class="mb-8">
            <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold text-star-300">⚙️ Configuration de connexion</h2>
                    <button @click="configPanel.expanded = !configPanel.expanded"
                            class="px-3 py-1 bg-white/10 rounded-lg hover:bg-white/20 transition text-sm">
                        <span x-text="configPanel.expanded ? '▼ Masquer' : '▶ Afficher'"></span>
                    </button>
                </div>

                <div x-show="configPanel.expanded" x-transition class="space-y-4">
                    <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg mb-4">
                        <p class="text-sm text-blue-200">
                            💡 Modifiez ces paramètres pour tester différentes configurations sans toucher aux fichiers.
                            Les modifications sont temporaires et ne persistent qu'en session.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- URL du Proxy -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                🌐 URL du Proxy Node.js
                                <span class="text-xs text-gray-400">(localhost ou IP distant)</span>
                            </label>
                            <input type="text"
                                   x-model="configPanel.proxyUrl"
                                   placeholder="http://localhost:3000"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                Exemple : http://192.168.1.10:3000
                            </p>
                        </div>

                        <!-- Hôte Voyager -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                🔭 Hôte Voyager
                                <span class="text-xs text-gray-400">(IP du serveur)</span>
                            </label>
                            <input type="text"
                                   x-model="configPanel.voyagerHost"
                                   placeholder="185.228.120.120"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                IP ou nom d'hôte du serveur Voyager
                            </p>
                        </div>

                        <!-- Port Voyager -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                🔌 Port Voyager
                                <span class="text-xs text-gray-400">(port TCP)</span>
                            </label>
                            <input type="number"
                                   x-model="configPanel.voyagerPort"
                                   placeholder="23002"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                23002 (custom) ou 5950 (standard)
                            </p>
                        </div>

                        <!-- Timeout -->
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                ⏱️ Timeout (secondes)
                                <span class="text-xs text-gray-400">(délai max d'attente)</span>
                            </label>
                            <input type="number"
                                   x-model="configPanel.timeout"
                                   placeholder="20"
                                   min="5"
                                   max="60"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                Entre 5 et 60 secondes
                            </p>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="flex gap-3 pt-4 border-t border-white/10">
                        <button @click="testConnection"
                                class="flex-1 px-4 py-2 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg font-semibold hover:from-green-600 hover:to-emerald-600 transition">
                            <span x-show="!configPanel.testing">🔌 Tester cette connexion</span>
                            <span x-show="configPanel.testing">⏳ Test en cours...</span>
                        </button>
                        <button @click="saveConfig"
                                class="flex-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg font-semibold hover:from-blue-600 hover:to-indigo-600 transition">
                            💾 Sauvegarder (session)
                        </button>
                        <button @click="resetConfig"
                                class="px-4 py-2 bg-white/10 rounded-lg font-semibold hover:bg-white/20 transition">
                            🔄 Réinitialiser
                        </button>
                    </div>

                    <!-- Résultat du test -->
                    <div x-show="configPanel.testResult.show" x-transition
                         :class="{
                            'bg-green-500/10 border-green-500/30': configPanel.testResult.success,
                            'bg-red-500/10 border-red-500/30': !configPanel.testResult.success
                         }"
                         class="p-4 rounded-lg border">
                        <p class="font-semibold mb-2">
                            <span x-show="configPanel.testResult.success">✅ Test réussi !</span>
                            <span x-show="!configPanel.testResult.success">❌ Test échoué</span>
                        </p>
                        <p class="text-sm" x-text="configPanel.testResult.message"></p>
                        <div x-show="configPanel.testResult.details" class="mt-2 text-xs font-mono bg-black/30 p-2 rounded">
                            <pre x-text="JSON.stringify(configPanel.testResult.details, null, 2)"></pre>
                        </div>
                    </div>

                    <!-- Presets rapides -->
                    <div class="pt-4 border-t border-white/10">
                        <h3 class="text-sm font-semibold mb-3">⚡ Configurations rapides</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-2">
                            <button @click="loadPreset('local')"
                                    class="px-3 py-2 bg-white/5 rounded-lg hover:bg-white/10 transition text-left">
                                <p class="font-semibold text-sm">🏠 Local</p>
                                <p class="text-xs text-gray-400">localhost:3000</p>
                            </button>
                            <button @click="loadPreset('remote')"
                                    class="px-3 py-2 bg-white/5 rounded-lg hover:bg-white/10 transition text-left">
                                <p class="font-semibold text-sm">🌍 Distant (actuel)</p>
                                <p class="text-xs text-gray-400">185.228.120.120:23002</p>
                            </button>
                            <button @click="loadPreset('standard')"
                                    class="px-3 py-2 bg-white/5 rounded-lg hover:bg-white/10 transition text-left">
                                <p class="font-semibold text-sm">📡 Port standard</p>
                                <p class="text-xs text-gray-400">185.228.120.120:5950</p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section Diagnostics -->
        <div x-show="diagnostics.show" x-transition class="mb-8">
            <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-semibold text-star-300">🔍 Diagnostic de connexion</h2>
                    <button @click="diagnostics.show = false" class="text-gray-400 hover:text-white">✕ Fermer</button>
                </div>

                <div x-show="diagnostics.loading" class="text-center py-8">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-star-400"></div>
                    <p class="mt-4 text-gray-400">Analyse en cours...</p>
                </div>

                <div x-show="!diagnostics.loading" class="space-y-4">
                    <!-- Tests de diagnostic -->
                    <template x-for="(test, index) in diagnostics.tests" :key="index">
                        <div class="p-4 rounded-lg border"
                             :class="{
                                'bg-green-500/10 border-green-500/30': test.status === 'success',
                                'bg-yellow-500/10 border-yellow-500/30': test.status === 'warning',
                                'bg-red-500/10 border-red-500/30': test.status === 'error',
                                'bg-blue-500/10 border-blue-500/30': test.status === 'info'
                             }">
                            <div class="flex items-start gap-3">
                                <div class="text-2xl">
                                    <span x-show="test.status === 'success'">✅</span>
                                    <span x-show="test.status === 'warning'">⚠️</span>
                                    <span x-show="test.status === 'error'">❌</span>
                                    <span x-show="test.status === 'info'">ℹ️</span>
                                </div>
                                <div class="flex-1">
                                    <h3 class="font-semibold mb-1" x-text="test.name"></h3>
                                    <p class="text-sm mb-2" x-text="test.message"></p>

                                    <!-- Détails -->
                                    <div x-show="Object.keys(test.details || {}).length > 0" class="mt-3 space-y-2">
                                        <template x-for="(value, key) in test.details" :key="key">
                                            <div class="text-sm">
                                                <span class="text-gray-400" x-text="key.replace(/_/g, ' ') + ':'"></span>
                                                <div class="ml-4 mt-1">
                                                    <!-- Si c'est un tableau -->
                                                    <template x-if="Array.isArray(value)">
                                                        <ul class="list-disc list-inside space-y-1">
                                                            <template x-for="(item, i) in value" :key="i">
                                                                <li x-text="item" class="text-gray-300"></li>
                                                            </template>
                                                        </ul>
                                                    </template>
                                                    <!-- Si c'est une string -->
                                                    <template x-if="!Array.isArray(value)">
                                                        <span x-text="value" class="text-gray-300"></span>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>

                    <!-- Résumé final -->
                    <div class="mt-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                        <h3 class="font-semibold text-blue-200 mb-2">💡 Que faire maintenant ?</h3>
                        <div class="text-sm text-gray-300 space-y-2">
                            <p><strong>Si le proxy est déconnecté :</strong> Démarrez-le avec <code class="bg-black/30 px-2 py-1 rounded">cd voyager-proxy && npm run dev</code></p>
                            <p><strong>Si Voyager ne répond pas :</strong> Vérifiez que Voyager tourne sur le serveur 185.228.120.120:23002</p>
                            <p><strong>Si tout est vert :</strong> Vous pouvez créer des Sets/Targets et ils seront vraiment envoyés à Voyager !</p>
                        </div>
                    </div>

                    <div class="text-xs text-gray-500 text-center">
                        Dernière analyse : <span x-text="diagnostics.timestamp"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Notifications -->
        <div x-show="notification.show"
             x-transition
             :class="notification.type === 'success' ? 'bg-green-500/20 border-green-500' : 'bg-red-500/20 border-red-500'"
             class="mb-6 p-4 rounded-lg border backdrop-blur-sm">
            <p x-text="notification.message"></p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Colonne gauche : Création -->
            <div class="space-y-6">
                <!-- Créer un Set -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <h2 class="text-2xl font-semibold mb-4 text-star-300">1️⃣ Créer un Set</h2>

                    <!-- Explication Set -->
                    <div class="mb-4 p-3 bg-yellow-500/10 border border-yellow-500/30 rounded-lg">
                        <p class="text-sm text-yellow-200">
                            <strong>🗂️ Qu'est-ce qu'un Set ?</strong><br>
                            C'est comme un dossier pour organiser vos observations. Vous pouvez y mettre plusieurs cibles (galaxies, nébuleuses, etc.).
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            📌 Exemple : "Soirée du 24 novembre 2024" ou "Session M31"
                        </p>
                    </div>

                    <form @submit.prevent="createSet" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Nom du Set
                                <span class="text-xs text-gray-400">(donnez-lui un nom parlant)</span>
                            </label>
                            <input type="text"
                                   x-model="setForm.name"
                                   required
                                   placeholder="Ex: Observation M31 - 24 Nov 2024"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent">
                            <p class="text-xs text-gray-400 mt-1">
                                💡 Ce nom vous aidera à retrouver vos observations plus tard
                            </p>
                        </div>
                        <button type="submit"
                                :disabled="loading.set"
                                class="w-full px-4 py-2 bg-gradient-to-r from-star-500 to-nebula-500 rounded-lg font-semibold hover:from-star-600 hover:to-nebula-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span x-show="!loading.set">Créer Set</span>
                            <span x-show="loading.set">⏳ Création en cours...</span>
                        </button>
                    </form>
                    <div x-show="createdSet.guid" class="mt-4 p-3 bg-green-500/20 rounded-lg border border-green-500/30">
                        <p class="text-sm">✅ <strong>Set créé avec succès !</strong></p>
                        <p class="text-xs font-mono mt-1 text-gray-300">GUID: <span x-text="createdSet.guid"></span></p>
                        <p class="text-xs text-gray-400 mt-2">👇 Copiez ce GUID pour créer une Target ci-dessous</p>
                    </div>
                </div>

                <!-- Créer une Target -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <h2 class="text-2xl font-semibold mb-4 text-star-300">2️⃣ Créer une Target</h2>

                    <!-- Explication Target -->
                    <div class="mb-4 p-3 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                        <p class="text-sm text-purple-200">
                            <strong>🎯 Qu'est-ce qu'une Target ?</strong><br>
                            C'est un objet céleste précis que vous voulez photographier. Vous devez donner ses coordonnées exactes dans le ciel.
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            📌 Exemple : M31 (galaxie d'Andromède), M42 (nébuleuse d'Orion)
                        </p>
                    </div>

                    <form @submit.prevent="createTarget" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Set GUID <span class="text-red-400">*</span>
                                <span class="text-xs text-gray-400">(le GUID du Set créé ci-dessus)</span>
                            </label>
                            <input type="text"
                                   x-model="targetForm.set_guid"
                                   required
                                   placeholder="Coller le GUID du Set ci-dessus"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                💡 Ce champ relie votre Target au Set. Utilisez le bouton "Utiliser" dans la liste à droite.
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Nom de la cible
                                <span class="text-xs text-gray-400">(nom de l'objet céleste)</span>
                            </label>
                            <input type="text"
                                   x-model="targetForm.name"
                                   required
                                   placeholder="Ex: M31 Andromeda"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent">
                            <p class="text-xs text-gray-400 mt-1">
                                💡 Utilisez les exemples en bas à droite pour remplir automatiquement
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    RA (J2000)
                                    <span class="text-xs text-gray-400">(Ascension Droite)</span>
                                </label>
                                <input type="text"
                                       x-model="targetForm.ra"
                                       required
                                       placeholder="00:42:44.330"
                                       class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                                <p class="text-xs text-gray-400 mt-1">
                                    📍 Position horizontale dans le ciel
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    DEC (J2000)
                                    <span class="text-xs text-gray-400">(Déclinaison)</span>
                                </label>
                                <input type="text"
                                       x-model="targetForm.dec"
                                       required
                                       placeholder="+41:16:09.00"
                                       class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                                <p class="text-xs text-gray-400 mt-1">
                                    📍 Position verticale dans le ciel
                                </p>
                            </div>
                        </div>
                        <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                            <p class="text-xs text-blue-200">
                                ℹ️ Les coordonnées RA/DEC sont comme latitude/longitude, mais pour le ciel. Vous pouvez les trouver sur des sites comme Stellarium ou AstroBin.
                            </p>
                        </div>
                        <button type="submit"
                                :disabled="loading.target"
                                class="w-full px-4 py-2 bg-gradient-to-r from-nebula-500 to-cosmic-500 rounded-lg font-semibold hover:from-nebula-600 hover:to-cosmic-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span x-show="!loading.target">Créer Target</span>
                            <span x-show="loading.target">⏳ Création en cours...</span>
                        </button>
                    </form>
                    <div x-show="createdTarget.guid" class="mt-4 p-3 bg-green-500/20 rounded-lg border border-green-500/30">
                        <p class="text-sm">✅ <strong>Target créée avec succès !</strong></p>
                        <p class="text-xs font-mono mt-1 text-gray-300">GUID: <span x-text="createdTarget.guid"></span></p>
                        <p class="text-xs text-gray-400 mt-2">👇 Maintenant, créez des Shots pour définir quelles photos prendre</p>
                    </div>
                </div>

                <!-- Créer un Shot -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <h2 class="text-2xl font-semibold mb-4 text-star-300">3️⃣ Créer un Shot</h2>

                    <!-- Explication Shot -->
                    <div class="mb-4 p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                        <p class="text-sm text-green-200">
                            <strong>📸 Qu'est-ce qu'un Shot ?</strong><br>
                            C'est une série de photos identiques. Par exemple : "20 photos de 300 secondes en filtre Luminance".
                        </p>
                        <p class="text-xs text-gray-400 mt-2">
                            📌 Vous pouvez créer plusieurs Shots différents pour une même Target (L, R, G, B, etc.)
                        </p>
                    </div>

                    <form @submit.prevent="createShot" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium mb-2">
                                Target GUID <span class="text-red-400">*</span>
                                <span class="text-xs text-gray-400">(le GUID de la Target créée ci-dessus)</span>
                            </label>
                            <input type="text"
                                   x-model="shotForm.target_guid"
                                   required
                                   placeholder="Coller le GUID de la Target ci-dessus"
                                   class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent font-mono text-sm">
                            <p class="text-xs text-gray-400 mt-1">
                                💡 Ce champ relie votre Shot à une Target. Utilisez le bouton "Shot" dans la liste à droite.
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Filtre
                                    <span class="text-xs text-gray-400">(type de lumière capturée)</span>
                                </label>
                                <select x-model="shotForm.filter"
                                        class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent">
                                    <option value="L">Luminance (L) - Toute la lumière</option>
                                    <option value="R">Red (R) - Lumière rouge</option>
                                    <option value="G">Green (G) - Lumière verte</option>
                                    <option value="B">Blue (B) - Lumière bleue</option>
                                    <option value="Ha">H-alpha (Ha) - Hydrogène</option>
                                    <option value="OIII">Oxygen III (OIII) - Oxygène</option>
                                    <option value="SII">Sulfur II (SII) - Soufre</option>
                                </select>
                                <p class="text-xs text-gray-400 mt-1">
                                    🌈 L/R/G/B = couleurs normales | Ha/OIII/SII = nébuleuses
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Binning
                                    <span class="text-xs text-gray-400">(regroupement pixels)</span>
                                </label>
                                <select x-model="shotForm.binning"
                                        class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent">
                                    <option value="1">1x1 (qualité max)</option>
                                    <option value="2">2x2 (plus rapide)</option>
                                    <option value="3">3x3</option>
                                    <option value="4">4x4</option>
                                </select>
                                <p class="text-xs text-gray-400 mt-1">
                                    📊 1x1 = meilleure qualité | 2x2+ = photos plus rapides
                                </p>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Exposition (secondes)
                                    <span class="text-xs text-gray-400">(durée de chaque photo)</span>
                                </label>
                                <input type="number"
                                       x-model="shotForm.exposure"
                                       required
                                       min="0.1"
                                       step="0.1"
                                       class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-1">
                                    ⏱️ 300s = 5 minutes par photo
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium mb-2">
                                    Quantité
                                    <span class="text-xs text-gray-400">(nombre de photos)</span>
                                </label>
                                <input type="number"
                                       x-model="shotForm.quantity"
                                       required
                                       min="1"
                                       class="w-full px-4 py-2 bg-white/10 border border-white/20 rounded-lg focus:ring-2 focus:ring-star-500 focus:border-transparent">
                                <p class="text-xs text-gray-400 mt-1">
                                    📷 Plus il y en a, meilleure est la qualité finale
                                </p>
                            </div>
                        </div>
                        <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                            <p class="text-xs text-blue-200">
                                💡 <strong>Exemple :</strong> 20 photos × 300 secondes = 100 minutes (1h40) de temps télescope
                            </p>
                        </div>
                        <button type="submit"
                                :disabled="loading.shot"
                                class="w-full px-4 py-2 bg-gradient-to-r from-cosmic-500 to-star-500 rounded-lg font-semibold hover:from-cosmic-600 hover:to-star-600 disabled:opacity-50 disabled:cursor-not-allowed transition">
                            <span x-show="!loading.shot">Créer Shot</span>
                            <span x-show="loading.shot">⏳ Création en cours...</span>
                        </button>
                    </form>
                    <div x-show="createdShot.guid" class="mt-4 p-3 bg-green-500/20 rounded-lg border border-green-500/30">
                        <p class="text-sm">✅ <strong>Shot créé avec succès !</strong></p>
                        <p class="text-xs font-mono mt-1 text-gray-300">GUID: <span x-text="createdShot.guid"></span></p>
                        <p class="text-xs text-gray-400 mt-2">🎉 Vous pouvez créer d'autres Shots avec d'autres filtres pour la même Target</p>
                    </div>
                </div>
            </div>

            <!-- Colonne droite : Liste et actions -->
            <div class="space-y-6">
                <!-- Liste des Sets -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-semibold text-star-300">📋 Sets</h2>
                        <button @click="listSets"
                                class="px-3 py-1 bg-white/10 rounded-lg hover:bg-white/20 transition text-sm">
                            Rafraîchir
                        </button>
                    </div>
                    <div x-show="sets.length === 0" class="text-gray-400 text-center py-8">
                        Aucun set pour le moment
                    </div>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <template x-for="set in sets" :key="set.Guid">
                            <div class="p-3 bg-white/5 rounded-lg border border-white/10 hover:border-star-500/50 transition">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <p class="font-semibold" x-text="set.Name"></p>
                                        <p class="text-xs font-mono text-gray-400 mt-1" x-text="set.Guid"></p>
                                    </div>
                                    <button @click="targetForm.set_guid = set.Guid; showNotification('GUID copié dans le formulaire Target', 'success')"
                                            class="px-2 py-1 bg-star-500/20 text-star-300 rounded text-xs hover:bg-star-500/30 transition">
                                        Utiliser
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Liste des Targets -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-2xl font-semibold text-star-300">🎯 Targets</h2>
                        <div class="flex gap-2">
                            <input type="text"
                                   x-model="targetListSetGuid"
                                   placeholder="Collez un Set GUID ici"
                                   class="px-3 py-1 bg-white/10 border border-white/20 rounded-lg text-sm w-64 font-mono">
                            <button @click="listTargets"
                                    :disabled="!targetListSetGuid"
                                    class="px-3 py-1 bg-white/10 rounded-lg hover:bg-white/20 transition text-sm disabled:opacity-50">
                                Charger
                            </button>
                        </div>
                    </div>
                    <div class="mb-3 p-2 bg-blue-500/10 border border-blue-500/30 rounded text-xs text-blue-200">
                        💡 Collez un GUID de Set ci-dessus pour voir ses Targets
                    </div>
                    <div x-show="targets.length === 0" class="text-gray-400 text-center py-8">
                        Entrez un Set GUID et cliquez sur "Charger"
                    </div>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <template x-for="target in targets" :key="target.GuidTarget">
                            <div class="p-3 bg-white/5 rounded-lg border border-white/10 hover:border-nebula-500/50 transition">
                                <div class="flex justify-between items-start mb-2">
                                    <div class="flex-1">
                                        <p class="font-semibold" x-text="target.TargetName"></p>
                                        <p class="text-xs font-mono text-gray-400 mt-1" x-text="target.GuidTarget"></p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            RA: <span x-text="target.RAJ2000"></span> |
                                            DEC: <span x-text="target.DECJ2000"></span>
                                        </p>
                                    </div>
                                    <div class="flex gap-1 flex-col">
                                        <button @click="activateTarget(target.GuidTarget)"
                                                title="Dire à Voyager de photographier cette cible"
                                                class="px-2 py-1 bg-green-500/20 text-green-300 rounded text-xs hover:bg-green-500/30 transition">
                                            ✅ Activer
                                        </button>
                                        <button @click="deactivateTarget(target.GuidTarget)"
                                                title="Mettre cette cible en pause"
                                                class="px-2 py-1 bg-red-500/20 text-red-300 rounded text-xs hover:bg-red-500/30 transition">
                                            ⏸️ Désactiver
                                        </button>
                                        <button @click="shotForm.target_guid = target.GuidTarget; showNotification('GUID copié dans le formulaire Shot', 'success')"
                                                title="Copier le GUID pour créer un Shot"
                                                class="px-2 py-1 bg-cosmic-500/20 text-cosmic-300 rounded text-xs hover:bg-cosmic-500/30 transition">
                                            📸 Shot
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Exemples de cibles populaires -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <h2 class="text-xl font-semibold mb-4 text-star-300">💡 Exemples de cibles populaires</h2>
                    <p class="text-xs text-gray-400 mb-3">
                        Cliquez pour remplir automatiquement le formulaire Target avec ces coordonnées célèbres.
                    </p>
                    <div class="space-y-2">
                        <button @click="fillTarget('M31 Andromeda', '00:42:44.330', '+41:16:09.00')"
                                class="w-full text-left px-3 py-2 bg-white/5 rounded-lg hover:bg-white/10 transition">
                            <p class="font-semibold">M31 - Galaxie d'Andromède</p>
                            <p class="text-xs text-gray-400">RA: 00:42:44 | DEC: +41:16:09</p>
                            <p class="text-xs text-green-300">🌌 Galaxie spirale géante</p>
                        </button>
                        <button @click="fillTarget('M42 Orion Nebula', '05:35:17.300', '-05:23:28.00')"
                                class="w-full text-left px-3 py-2 bg-white/5 rounded-lg hover:bg-white/10 transition">
                            <p class="font-semibold">M42 - Nébuleuse d'Orion</p>
                            <p class="text-xs text-gray-400">RA: 05:35:17 | DEC: -05:23:28</p>
                            <p class="text-xs text-pink-300">☁️ Nébuleuse en émission</p>
                        </button>
                        <button @click="fillTarget('M51 Whirlpool Galaxy', '13:29:52.700', '+47:11:43.00')"
                                class="w-full text-left px-3 py-2 bg-white/5 rounded-lg hover:bg-white/10 transition">
                            <p class="font-semibold">M51 - Galaxie du Tourbillon</p>
                            <p class="text-xs text-gray-400">RA: 13:29:52 | DEC: +47:11:43</p>
                            <p class="text-xs text-blue-300">🌀 Galaxie spirale avec bras bien définis</p>
                        </button>
                    </div>
                </div>

                <!-- Guide d'activation -->
                <div class="bg-white/5 backdrop-blur-md rounded-xl p-6 border border-white/10">
                    <h2 class="text-xl font-semibold mb-4 text-star-300">🚀 Étape finale</h2>
                    <div class="space-y-3">
                        <div class="p-3 bg-green-500/10 border border-green-500/30 rounded-lg">
                            <p class="text-sm font-semibold text-green-200 mb-1">✅ Activer une Target</p>
                            <p class="text-xs text-gray-300">
                                Une fois que vous avez créé vos Shots, cliquez sur "Activer" dans la liste des Targets.
                                Cela dira à Voyager : "Cette cible est prête à être photographiée".
                            </p>
                        </div>
                        <div class="p-3 bg-red-500/10 border border-red-500/30 rounded-lg">
                            <p class="text-sm font-semibold text-red-200 mb-1">⏸️ Désactiver une Target</p>
                            <p class="text-xs text-gray-300">
                                Cela met la Target en pause. Le télescope ignorera cette cible jusqu'à ce que vous la réactiviez.
                            </p>
                        </div>
                        <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg">
                            <p class="text-sm font-semibold text-blue-200 mb-1">ℹ️ GUID (identifiant unique)</p>
                            <p class="text-xs text-gray-300">
                                Chaque Set/Target/Shot a un GUID (comme un numéro d'identification).
                                C'est ce qui permet de les relier entre eux et de les retrouver.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function robotargetTest() {
            return {
                proxyStatus: 'disconnected',
                voyagerConnected: false,

                loading: {
                    set: false,
                    target: false,
                    shot: false,
                },

                notification: {
                    show: false,
                    message: '',
                    type: 'success'
                },

                diagnostics: {
                    show: false,
                    loading: false,
                    tests: [],
                    timestamp: ''
                },

                configPanel: {
                    expanded: false,
                    testing: false,
                    proxyUrl: '{{ config("services.voyager.proxy_url", "http://localhost:3000") }}',
                    voyagerHost: '185.228.120.120',
                    voyagerPort: 23002,
                    timeout: 20,
                    testResult: {
                        show: false,
                        success: false,
                        message: '',
                        details: null
                    }
                },

                setForm: {
                    name: ''
                },

                targetForm: {
                    set_guid: '',
                    name: '',
                    ra: '',
                    dec: ''
                },

                shotForm: {
                    target_guid: '',
                    filter: 'L',
                    exposure: 300,
                    quantity: 10,
                    binning: 1
                },

                createdSet: { guid: '' },
                createdTarget: { guid: '' },
                createdShot: { guid: '' },

                sets: [],
                targets: [],
                targetListSetGuid: '',

                init() {
                    // Charger la config sauvegardée
                    this.loadSavedConfig();

                    this.checkProxyStatus();
                    this.listSets();
                    // Lancer le diagnostic automatiquement
                    setTimeout(() => this.runDiagnostics(), 1000);
                },

                async checkProxyStatus() {
                    try {
                        const response = await fetch(this.configPanel.proxyUrl + '/health');
                        if (response.ok) {
                            this.proxyStatus = 'connected';
                            const data = await response.json();
                            this.voyagerConnected = data.voyager?.connected || false;
                        }
                    } catch (error) {
                        this.proxyStatus = 'disconnected';
                    }
                },

                async createSet() {
                    this.loading.set = true;
                    try {
                        const response = await fetch('/test/robotarget/sets', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.setForm)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.createdSet.guid = data.guid;
                            this.showNotification('Set créé avec succès!', 'success');
                            this.setForm.name = '';
                            this.listSets();
                        } else {
                            throw new Error(data.message || 'Erreur lors de la création');
                        }
                    } catch (error) {
                        this.showNotification('Erreur: ' + error.message, 'error');
                    } finally {
                        this.loading.set = false;
                    }
                },

                async createTarget() {
                    this.loading.target = true;
                    try {
                        const response = await fetch('/test/robotarget/targets', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.targetForm)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.createdTarget.guid = data.guid;
                            this.showNotification('Target créée avec succès!', 'success');
                            this.targetForm.name = '';
                            this.targetForm.ra = '';
                            this.targetForm.dec = '';
                            if (this.targetListSetGuid === this.targetForm.set_guid) {
                                this.listTargets();
                            }
                        } else {
                            throw new Error(data.message || 'Erreur lors de la création');
                        }
                    } catch (error) {
                        this.showNotification('Erreur: ' + error.message, 'error');
                    } finally {
                        this.loading.target = false;
                    }
                },

                async createShot() {
                    this.loading.shot = true;
                    try {
                        const response = await fetch('/test/robotarget/shots', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(this.shotForm)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.createdShot.guid = data.guid;
                            this.showNotification('Shot créé avec succès!', 'success');
                        } else {
                            throw new Error(data.message || 'Erreur lors de la création');
                        }
                    } catch (error) {
                        this.showNotification('Erreur: ' + error.message, 'error');
                    } finally {
                        this.loading.shot = false;
                    }
                },

                async listSets() {
                    try {
                        const response = await fetch('/test/robotarget/sets');
                        const data = await response.json();
                        this.sets = data.sets || [];
                    } catch (error) {
                        this.showNotification('Erreur lors du chargement des sets', 'error');
                    }
                },

                async listTargets() {
                    if (!this.targetListSetGuid) return;

                    try {
                        const response = await fetch(`/test/robotarget/targets?set_guid=${this.targetListSetGuid}`);
                        const data = await response.json();
                        this.targets = data.targets || [];
                    } catch (error) {
                        this.showNotification('Erreur lors du chargement des targets', 'error');
                    }
                },

                async activateTarget(guid) {
                    try {
                        const response = await fetch(`/test/robotarget/targets/${guid}/activate`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            this.showNotification('Target activée!', 'success');
                        }
                    } catch (error) {
                        this.showNotification('Erreur lors de l\'activation', 'error');
                    }
                },

                async deactivateTarget(guid) {
                    try {
                        const response = await fetch(`/test/robotarget/targets/${guid}/deactivate`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        });

                        if (response.ok) {
                            this.showNotification('Target désactivée!', 'success');
                        }
                    } catch (error) {
                        this.showNotification('Erreur lors de la désactivation', 'error');
                    }
                },

                fillTarget(name, ra, dec) {
                    this.targetForm.name = name;
                    this.targetForm.ra = ra;
                    this.targetForm.dec = dec;
                    this.showNotification('Coordonnées copiées dans le formulaire', 'success');
                },

                showNotification(message, type = 'success') {
                    this.notification.message = message;
                    this.notification.type = type;
                    this.notification.show = true;

                    setTimeout(() => {
                        this.notification.show = false;
                    }, 3000);
                },

                async runDiagnostics() {
                    this.diagnostics.show = true;
                    this.diagnostics.loading = true;
                    this.diagnostics.tests = [];

                    try {
                        const response = await fetch('/test/robotarget/diagnostics');
                        const data = await response.json();

                        this.diagnostics.tests = data.tests || [];
                        this.diagnostics.timestamp = new Date(data.timestamp).toLocaleString('fr-FR');
                    } catch (error) {
                        this.diagnostics.tests = [{
                            name: 'Erreur',
                            status: 'error',
                            message: 'Impossible de lancer le diagnostic: ' + error.message,
                            details: {}
                        }];
                    } finally {
                        this.diagnostics.loading = false;
                    }
                },

                async testConnection() {
                    this.configPanel.testing = true;
                    this.configPanel.testResult.show = false;

                    try {
                        // Test 1: Connexion au proxy
                        const proxyResponse = await fetch(this.configPanel.proxyUrl + '/health', {
                            timeout: this.configPanel.timeout * 1000
                        });

                        if (!proxyResponse.ok) {
                            throw new Error(`Proxy HTTP ${proxyResponse.status}`);
                        }

                        const proxyData = await proxyResponse.json();

                        this.configPanel.testResult = {
                            show: true,
                            success: true,
                            message: `Connexion au proxy réussie ! Voyager ${proxyData.voyager?.connected ? 'connecté ✅' : 'déconnecté ❌'}`,
                            details: {
                                proxy_url: this.configPanel.proxyUrl,
                                proxy_uptime: Math.round(proxyData.uptime) + 's',
                                voyager_host: this.configPanel.voyagerHost + ':' + this.configPanel.voyagerPort,
                                voyager_connected: proxyData.voyager?.connected ? 'OUI' : 'NON',
                                voyager_authenticated: proxyData.voyager?.authenticated ? 'OUI' : 'NON',
                            }
                        };

                        // Mettre à jour les indicateurs
                        this.proxyStatus = 'connected';
                        this.voyagerConnected = proxyData.voyager?.connected || false;

                    } catch (error) {
                        this.configPanel.testResult = {
                            show: true,
                            success: false,
                            message: 'Erreur de connexion: ' + error.message,
                            details: {
                                proxy_url: this.configPanel.proxyUrl,
                                erreur: error.message,
                                solution: 'Vérifiez que le proxy Node.js tourne et que l\'URL est correcte'
                            }
                        };

                        this.proxyStatus = 'disconnected';
                    } finally {
                        this.configPanel.testing = false;
                    }
                },

                saveConfig() {
                    // Sauvegarder en session storage
                    sessionStorage.setItem('voyager_config', JSON.stringify({
                        proxyUrl: this.configPanel.proxyUrl,
                        voyagerHost: this.configPanel.voyagerHost,
                        voyagerPort: this.configPanel.voyagerPort,
                        timeout: this.configPanel.timeout
                    }));

                    this.showNotification('Configuration sauvegardée pour cette session', 'success');

                    // Re-tester la connexion
                    this.checkProxyStatus();
                },

                resetConfig() {
                    this.configPanel.proxyUrl = '{{ config("services.voyager.proxy_url", "http://localhost:3000") }}';
                    this.configPanel.voyagerHost = '185.228.120.120';
                    this.configPanel.voyagerPort = 23002;
                    this.configPanel.timeout = 20;
                    this.configPanel.testResult.show = false;

                    // Supprimer de la session
                    sessionStorage.removeItem('voyager_config');

                    this.showNotification('Configuration réinitialisée', 'success');
                },

                loadPreset(preset) {
                    const presets = {
                        local: {
                            proxyUrl: 'http://localhost:3000',
                            voyagerHost: 'localhost',
                            voyagerPort: 5950,
                            timeout: 20
                        },
                        remote: {
                            proxyUrl: 'http://localhost:3000',
                            voyagerHost: '185.228.120.120',
                            voyagerPort: 23002,
                            timeout: 20
                        },
                        standard: {
                            proxyUrl: 'http://localhost:3000',
                            voyagerHost: '185.228.120.120',
                            voyagerPort: 5950,
                            timeout: 20
                        }
                    };

                    const config = presets[preset];
                    if (config) {
                        Object.assign(this.configPanel, config);
                        this.showNotification(`Configuration "${preset}" chargée`, 'success');
                        this.configPanel.testResult.show = false;
                    }
                },

                loadSavedConfig() {
                    // Charger depuis la session si existe
                    const saved = sessionStorage.getItem('voyager_config');
                    if (saved) {
                        try {
                            const config = JSON.parse(saved);
                            Object.assign(this.configPanel, config);
                        } catch (e) {
                            console.error('Failed to load saved config', e);
                        }
                    }
                }
            };
        }
    </script>
</body>
</html>
