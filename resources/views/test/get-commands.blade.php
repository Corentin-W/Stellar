<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoboTarget GET Commands Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div x-data="getCommandsTester()" x-init="init()" class="max-w-7xl mx-auto">
        <div class="mb-8">
            <h1 class="text-4xl font-bold mb-2">🔍 RoboTarget GET Commands Tester</h1>
            <p class="text-gray-400">Teste les commandes de lecture (GetSet, GetTarget, GetBaseSequence) et visualise les requêtes/réponses</p>
        </div>

        <!-- Status Bar -->
        <div class="bg-gray-800 rounded-lg p-4 mb-6 flex items-center justify-between">
            <div>
                <span class="text-sm text-gray-400">Proxy Status:</span>
                <span class="ml-2 px-3 py-1 rounded-full text-sm font-bold"
                      :class="proxyConnected ? 'bg-green-600' : 'bg-red-600'">
                    <span x-show="proxyConnected">✅ Connecté</span>
                    <span x-show="!proxyConnected">❌ Déconnecté</span>
                </span>
            </div>
            <div class="text-sm text-gray-400">
                Session Key: <code class="bg-gray-700 px-2 py-1 rounded" x-text="sessionKey || 'N/A'"></code>
            </div>
        </div>

        <!-- Command Selector -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Sélection de la commande</h2>
            <div class="grid grid-cols-3 gap-4">
                <button @click="selectCommand('GetSet')"
                        :class="selectedCommand === 'GetSet' ? 'bg-blue-600 border-blue-400' : 'bg-gray-700 hover:bg-gray-600 border-gray-600'"
                        class="p-4 rounded-lg border-2 transition">
                    <div class="text-lg font-bold mb-1">📁 GetSet</div>
                    <div class="text-xs text-gray-400">Liste tous les Sets</div>
                </button>

                <button @click="selectCommand('GetTarget')"
                        :class="selectedCommand === 'GetTarget' ? 'bg-blue-600 border-blue-400' : 'bg-gray-700 hover:bg-gray-600 border-gray-600'"
                        class="p-4 rounded-lg border-2 transition">
                    <div class="text-lg font-bold mb-1">🎯 GetTarget</div>
                    <div class="text-xs text-gray-400">Liste les Targets d'un Set</div>
                </button>

                <button @click="selectCommand('GetBaseSequence')"
                        :class="selectedCommand === 'GetBaseSequence' ? 'bg-blue-600 border-blue-400' : 'bg-gray-700 hover:bg-gray-600 border-gray-600'"
                        class="p-4 rounded-lg border-2 transition">
                    <div class="text-lg font-bold mb-1">🔢 GetBaseSequence</div>
                    <div class="text-xs text-gray-400">Liste les séquences de base</div>
                </button>
            </div>
        </div>

        <!-- Parameters -->
        <div x-show="selectedCommand" class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Paramètres</h2>

            <!-- GetSet Parameters -->
            <div x-show="selectedCommand === 'GetSet'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">ProfileName (vide = tous les profils)</label>
                    <input type="text" x-model="params.ProfileName"
                           placeholder="Laissez vide pour obtenir tous les Sets de tous les profils"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">💡 Vide retourne tous les Sets de tous les profils</p>
                </div>
            </div>

            <!-- GetTarget Parameters -->
            <div x-show="selectedCommand === 'GetTarget'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">RefGuidSet (obligatoire)</label>
                    <input type="text" x-model="params.RefGuidSet"
                           placeholder="GUID du Set (ex: ffffffff-aaaa-bbbb-cccc-111111111111)"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">💡 Vide retourne toutes les Targets de tous les Sets</p>
                </div>
            </div>

            <!-- GetBaseSequence Parameters -->
            <div x-show="selectedCommand === 'GetBaseSequence'" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-2">ProfileName (vide = tous les profils)</label>
                    <input type="text" x-model="params.ProfileName"
                           placeholder="Laissez vide pour obtenir toutes les séquences"
                           class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                    <p class="text-xs text-gray-400 mt-1">💡 Vide retourne toutes les BaseSequences de tous les profils</p>
                </div>
            </div>

            <!-- Execute Button -->
            <button @click="executeCommand()"
                    :disabled="loading"
                    class="w-full mt-4 bg-gradient-to-r from-green-600 to-blue-600 hover:from-green-700 hover:to-blue-700 disabled:from-gray-600 disabled:to-gray-600 px-6 py-3 rounded-lg font-bold">
                <span x-show="!loading">🚀 Exécuter la commande</span>
                <span x-show="loading">⏳ Envoi en cours...</span>
            </button>
        </div>

        <!-- Request Display -->
        <div x-show="lastRequest" class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">📤 Requête envoyée à Voyager</h2>

            <!-- MAC Calculation -->
            <div class="mb-4 bg-gray-700 rounded-lg p-4">
                <h3 class="font-bold mb-2 text-green-400">🔐 Calcul du MAC</h3>
                <div class="space-y-2 text-sm font-mono">
                    <div>
                        <span class="text-gray-400">Formule Reserved API:</span>
                        <code class="block bg-gray-800 p-2 rounded mt-1">Secret||:||SessionKey||:||ID||:||UID</code>
                    </div>
                    <div x-show="macInfo">
                        <span class="text-gray-400">MAC String:</span>
                        <code class="block bg-gray-800 p-2 rounded mt-1 text-xs break-all" x-text="macInfo?.string"></code>
                    </div>
                    <div x-show="macInfo">
                        <span class="text-gray-400">MAC (Base64):</span>
                        <code class="block bg-gray-800 p-2 rounded mt-1 break-all" x-text="macInfo?.mac"></code>
                    </div>
                </div>
            </div>

            <!-- JSON Request -->
            <div class="bg-black rounded p-4 overflow-auto">
                <pre class="text-xs" x-text="JSON.stringify(lastRequest, null, 2)"></pre>
            </div>
        </div>

        <!-- Response Display -->
        <div x-show="lastResponse" class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">
                <span x-show="lastResponse?.success" class="text-green-400">✅ Réponse reçue</span>
                <span x-show="!lastResponse?.success" class="text-red-400">❌ Erreur</span>
            </h2>

            <!-- Success Info -->
            <div x-show="lastResponse?.success" class="mb-4">
                <div class="flex items-center gap-4 text-sm mb-4">
                    <div class="bg-gray-700 px-4 py-2 rounded">
                        <span class="text-gray-400">Statut:</span>
                        <span class="font-bold text-green-400 ml-2" x-text="lastResponse?.result?.ParamRet?.ret || 'N/A'"></span>
                    </div>
                    <div class="bg-gray-700 px-4 py-2 rounded">
                        <span class="text-gray-400">ActionResultInt:</span>
                        <span class="font-bold ml-2" x-text="lastResponse?.result?.ActionResultInt || 'N/A'"></span>
                    </div>
                    <div class="bg-gray-700 px-4 py-2 rounded" x-show="itemCount !== null">
                        <span class="text-gray-400">Éléments:</span>
                        <span class="font-bold text-blue-400 ml-2" x-text="itemCount"></span>
                    </div>
                </div>

                <!-- Items List -->
                <div x-show="items && items.length > 0" class="mb-4">
                    <h3 class="font-bold mb-2">📋 Liste des éléments</h3>
                    <div class="space-y-2 max-h-96 overflow-y-auto">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="bg-gray-700 p-3 rounded text-sm">
                                <div class="grid grid-cols-2 gap-2">
                                    <!-- For Sets -->
                                    <template x-if="selectedCommand === 'GetSet'">
                                        <div>
                                            <div><span class="text-gray-400">Nom:</span> <span class="font-bold" x-text="item.setname"></span></div>
                                            <div class="text-xs text-gray-400" x-text="item.guid"></div>
                                        </div>
                                    </template>
                                    <!-- For Targets -->
                                    <template x-if="selectedCommand === 'GetTarget'">
                                        <div>
                                            <div><span class="text-gray-400">Nom:</span> <span class="font-bold" x-text="item.targetname"></span></div>
                                            <div class="text-xs text-gray-400" x-text="item.guidtarget"></div>
                                        </div>
                                    </template>
                                    <!-- For BaseSequences -->
                                    <template x-if="selectedCommand === 'GetBaseSequence'">
                                        <div>
                                            <div><span class="text-gray-400">Nom:</span> <span class="font-bold" x-text="item.nameseq"></span></div>
                                            <div class="text-xs text-gray-400" x-text="item.guidbasesequence"></div>
                                        </div>
                                    </template>

                                    <div class="text-right">
                                        <span class="text-gray-400">Status:</span>
                                        <span :class="item.status === 0 ? 'text-green-400' : 'text-red-400'" x-text="item.status === 0 ? 'Actif' : 'Inactif'"></span>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Error Info -->
            <div x-show="!lastResponse?.success" class="mb-4 bg-red-900 border-2 border-red-500 rounded p-4">
                <p class="font-bold mb-2">Erreur:</p>
                <p class="text-sm" x-text="lastResponse?.error || 'Erreur inconnue'"></p>
            </div>

            <!-- Full JSON Response -->
            <details class="bg-gray-700 rounded">
                <summary class="p-4 cursor-pointer font-bold hover:bg-gray-600">
                    🔍 Voir la réponse JSON complète
                </summary>
                <div class="p-4 bg-black rounded-b overflow-auto">
                    <pre class="text-xs" x-text="JSON.stringify(lastResponse, null, 2)"></pre>
                </div>
            </details>
        </div>

        <!-- Quick Actions -->
        <div class="bg-gradient-to-r from-purple-900 to-blue-900 rounded-lg p-6 border-2 border-purple-500">
            <h2 class="text-xl font-bold mb-4">⚡ Actions rapides</h2>
            <div class="grid grid-cols-2 gap-4">
                <button @click="quickGetAllSets()"
                        class="bg-purple-600 hover:bg-purple-700 px-4 py-3 rounded font-bold">
                    📁 Récupérer tous les Sets
                </button>
                <button @click="quickGetAllSequences()"
                        class="bg-blue-600 hover:bg-blue-700 px-4 py-3 rounded font-bold">
                    🔢 Récupérer toutes les Séquences
                </button>
            </div>
        </div>
    </div>

    <script>
        function getCommandsTester() {
            return {
                proxyConnected: false,
                sessionKey: null,
                selectedCommand: null,
                params: {
                    RefGuidSet: '',
                    ProfileName: ''
                },
                loading: false,
                lastRequest: null,
                lastResponse: null,
                macInfo: null,
                items: null,
                itemCount: null,

                async init() {
                    // Check proxy status
                    try {
                        const response = await fetch('http://localhost:3003/api/dashboard/state');
                        const data = await response.json();
                        this.proxyConnected = data.connection?.status === 'connected';
                        this.sessionKey = data.connection?.sessionKey;
                    } catch (error) {
                        console.error('Failed to connect to proxy:', error);
                        this.proxyConnected = false;
                    }
                },

                selectCommand(command) {
                    this.selectedCommand = command;
                    this.params = {
                        RefGuidSet: '',
                        ProfileName: ''
                    };
                    this.lastRequest = null;
                    this.lastResponse = null;
                    this.macInfo = null;
                    this.items = null;
                    this.itemCount = null;
                },

                async executeCommand() {
                    if (!this.selectedCommand) return;

                    this.loading = true;
                    this.lastRequest = null;
                    this.lastResponse = null;
                    this.macInfo = null;
                    this.items = null;
                    this.itemCount = null;

                    try {
                        const method = `RemoteRoboTarget${this.selectedCommand}`;
                        const commandParams = {};

                        // Build params based on command
                        if (this.selectedCommand === 'GetSet') {
                            commandParams.ProfileName = this.params.ProfileName || '';
                        } else if (this.selectedCommand === 'GetTarget') {
                            commandParams.RefGuidSet = this.params.RefGuidSet || '';
                        } else if (this.selectedCommand === 'GetBaseSequence') {
                            commandParams.ProfileName = this.params.ProfileName || '';
                        }

                        // Send command via test-mac endpoint
                        // FORMULE GAGNANTE: Même séparateur que Manager Mode ||:||
                        const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                method: method,
                                params: commandParams,
                                macFormula: {
                                    sep1: '||:||',  // Même que Manager Mode
                                    sep2: '||:||',
                                    sep3: '||:||'
                                }
                            })
                        });

                        const result = await response.json();

                        this.lastRequest = result.command;
                        this.macInfo = result.macInfo;
                        this.lastResponse = result;

                        // Extract items from response
                        if (result.success && result.result?.ParamRet?.list) {
                            this.items = result.result.ParamRet.list;
                            this.itemCount = this.items.length;
                        } else if (result.success && result.result?.parsed?.params?.list) {
                            this.items = result.result.parsed.params.list;
                            this.itemCount = this.items.length;
                        }

                    } catch (error) {
                        this.lastResponse = {
                            success: false,
                            error: error.message
                        };
                    } finally {
                        this.loading = false;
                    }
                },

                async quickGetAllSets() {
                    this.selectCommand('GetSet');
                    this.params.ProfileName = '';
                    await this.executeCommand();
                },

                async quickGetAllSequences() {
                    this.selectCommand('GetBaseSequence');
                    this.params.ProfileName = '';
                    await this.executeCommand();
                }
            }
        }
    </script>
</body>
</html>
