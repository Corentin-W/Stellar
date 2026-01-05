<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RoboTarget MAC Tester</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-white min-h-screen p-8">
    <div x-data="macTester()" x-init="init()" class="max-w-6xl mx-auto">
        <h1 class="text-3xl font-bold mb-8">🔐 RoboTarget MAC Tester</h1>

        <!-- Configuration -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Configuration</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Shared Secret</label>
                    <input type="text" x-model="config.sharedSecret" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Session Key (auto)</label>
                    <input type="text" x-model="config.sessionKey" readonly class="w-full bg-gray-600 border border-gray-600 rounded px-3 py-2">
                </div>
            </div>
        </div>

        <!-- Test MAC Formulas -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Test MAC Formulas</h2>

            <div class="space-y-4">
                <template x-for="(formula, index) in formulas" :key="index">
                    <div class="bg-gray-700 p-4 rounded">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="font-bold" x-text="formula.name"></h3>
                            <button @click="calculateMAC(index)" class="bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded text-sm">
                                Calculer
                            </button>
                        </div>
                        <div class="text-xs font-mono mb-2">
                            <span class="text-gray-400">Formule:</span>
                            <span x-text="formula.description"></span>
                        </div>
                        <template x-if="formula.result">
                            <div class="mt-3 space-y-2">
                                <div class="text-xs">
                                    <span class="text-gray-400">String:</span>
                                    <code class="bg-gray-800 px-2 py-1 rounded block mt-1" x-text="formula.result.string"></code>
                                </div>
                                <div class="text-xs">
                                    <span class="text-gray-400">MAC:</span>
                                    <code class="bg-gray-800 px-2 py-1 rounded block mt-1" x-text="formula.result.mac"></code>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>

        <!-- Auto-Test Section -->
        <div class="bg-gradient-to-r from-purple-900 to-blue-900 rounded-lg p-6 mb-6 border-2 border-purple-500">
            <h2 class="text-2xl font-bold mb-4">🤖 Auto-Test (Recommandé)</h2>
            <p class="text-sm text-gray-300 mb-4">
                Lance automatiquement tous les tests de formules MAC jusqu'à trouver celle qui fonctionne.
                Cela teste 7 variantes différentes séquentiellement.
            </p>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Commande à tester</label>
                <select x-model="autoTestCommand" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                    <option value="">-- Choisir --</option>
                    <option value="RemoteRoboTargetGetSet">RemoteRoboTargetGetSet (List Sets)</option>
                    <option value="RemoteRoboTargetGetBaseSequence">RemoteRoboTargetGetBaseSequence (List Sequences)</option>
                    <option value="RemoteRoboTargetGetTarget">RemoteRoboTargetGetTarget (List Targets)</option>
                    <option value="RemoteRoboTargetAddSet">RemoteRoboTargetAddSet (Create Set)</option>
                </select>
            </div>

            <button @click="runAutoTest()" :disabled="!autoTestCommand || isAutoTesting"
                    class="w-full bg-gradient-to-r from-purple-600 to-blue-600 hover:from-purple-700 hover:to-blue-700 disabled:from-gray-600 disabled:to-gray-600 px-6 py-4 rounded-lg font-bold text-lg shadow-lg">
                <span x-show="!isAutoTesting">🚀 Lancer Auto-Test (Toutes les variantes)</span>
                <span x-show="isAutoTesting">⏳ Test en cours... <span x-text="autoTestProgress"></span></span>
            </button>

            <template x-if="autoTestResult">
                <div class="mt-4 p-4 rounded-lg" :class="autoTestResult.success ? 'bg-green-900 border-2 border-green-500' : 'bg-red-900 border-2 border-red-500'">
                    <h3 class="font-bold text-lg mb-2" x-text="autoTestResult.success ? '✅ Formule trouvée !' : '❌ Aucune formule ne fonctionne'"></h3>
                    <template x-if="autoTestResult.successfulVariant">
                        <div class="space-y-2">
                            <p class="text-sm"><span class="font-bold">Formule gagnante:</span> <span x-text="autoTestResult.successfulVariant.name"></span></p>
                            <div class="bg-black bg-opacity-50 p-3 rounded font-mono text-xs">
                                <div>Sep1: "<span x-text="autoTestResult.successfulVariant.sep1"></span>"</div>
                                <div>Sep2: "<span x-text="autoTestResult.successfulVariant.sep2"></span>"</div>
                                <div>Sep3: "<span x-text="autoTestResult.successfulVariant.sep3"></span>"</div>
                                <div>Conversion: <span x-text="autoTestResult.successfulVariant.conversion"></span></div>
                            </div>
                        </div>
                    </template>
                    <template x-if="!autoTestResult.success && autoTestResult.allResults">
                        <div class="mt-2 text-sm">
                            <p class="mb-2">Résultats des tests:</p>
                            <div class="space-y-1 max-h-48 overflow-y-auto">
                                <template x-for="(result, index) in autoTestResult.allResults" :key="index">
                                    <div class="text-xs">
                                        <span x-text="result.success ? '✅' : '❌'"></span>
                                        <span x-text="result.variant"></span>
                                        <span x-show="result.error" class="text-red-400" x-text="' - ' + result.error"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                </div>
            </template>
        </div>

        <!-- Manual Test Commands -->
        <div class="bg-gray-800 rounded-lg p-6 mb-6">
            <h2 class="text-xl font-bold mb-4">Test Manuel (Avancé)</h2>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Sélectionner une formule MAC</label>
                <select x-model="selectedFormulaIndex" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                    <option value="">-- Choisir --</option>
                    <template x-for="(formula, index) in formulas" :key="index">
                        <option :value="index" x-text="formula.name"></option>
                    </template>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Commande à tester</label>
                <select x-model="selectedCommand" @change="loadCommandTemplate()" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                    <option value="">-- Choisir --</option>
                    <option value="RemoteRoboTargetGetSet">RemoteRoboTargetGetSet (List Sets)</option>
                    <option value="RemoteRoboTargetGetBaseSequence">RemoteRoboTargetGetBaseSequence (List Sequences)</option>
                    <option value="RemoteRoboTargetGetTarget">RemoteRoboTargetGetTarget (List Targets)</option>
                    <option value="RemoteRoboTargetAddSet">RemoteRoboTargetAddSet (Create Set)</option>
                </select>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium mb-2">Paramètres JSON</label>
                <textarea x-model="commandParams" rows="10" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2 font-mono text-xs"></textarea>
            </div>

            <button @click="sendTestCommand()" :disabled="!selectedFormulaIndex || !selectedCommand || isLoading"
                    class="w-full bg-green-600 hover:bg-green-700 disabled:bg-gray-600 px-6 py-3 rounded font-bold">
                <span x-show="!isLoading">🚀 Envoyer la commande</span>
                <span x-show="isLoading">⏳ Envoi en cours...</span>
            </button>
        </div>

        <!-- Results -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-bold mb-4">Résultats</h2>
            <div class="space-y-4 max-h-96 overflow-y-auto">
                <template x-for="(log, index) in logs" :key="index">
                    <div class="bg-gray-700 p-3 rounded text-sm font-mono">
                        <div class="flex items-center gap-2 mb-1">
                            <span :class="{
                                'text-green-400': log.type === 'success',
                                'text-red-400': log.type === 'error',
                                'text-blue-400': log.type === 'info'
                            }" x-text="log.timestamp"></span>
                            <span :class="{
                                'text-green-400': log.type === 'success',
                                'text-red-400': log.type === 'error',
                                'text-blue-400': log.type === 'info'
                            }" x-text="log.type.toUpperCase()"></span>
                        </div>
                        <pre class="text-xs whitespace-pre-wrap" x-text="log.message"></pre>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <script>
        function macTester() {
            return {
                config: {
                    sharedSecret: 'Dherbomez',
                    sessionKey: '',
                },
                autoTestCommand: '',
                isAutoTesting: false,
                autoTestProgress: '',
                autoTestResult: null,
                formulas: [
                    {
                        name: 'UNIFORM "|| |" (1 espace partout)',
                        description: 'Secret + "|| |" + SessionKey + "|| |" + ID + "|| |" + UID',
                        sep1: '|| |',
                        sep2: '|| |',
                        sep3: '|| |',
                        result: null
                    },
                    {
                        name: 'NON-UNIFORM "||  |" (2 espaces au milieu)',
                        description: 'Secret + "|| |" + SessionKey + "||  |" + ID + "|| |" + UID',
                        sep1: '|| |',
                        sep2: '||  |',
                        sep3: '|| |',
                        result: null
                    },
                    {
                        name: 'UNIFORM "|||" (3 barres)',
                        description: 'Secret + "|||" + SessionKey + "|||" + ID + "|||" + UID',
                        sep1: '|||',
                        sep2: '|||',
                        sep3: '|||',
                        result: null
                    }
                ],
                selectedFormulaIndex: '',
                selectedCommand: '',
                commandParams: '',
                isLoading: false,
                logs: [],

                init() {
                    this.addLog('info', 'Interface de test MAC chargée');
                    this.fetchSessionKey();
                },

                async fetchSessionKey() {
                    try {
                        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
                        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

                        const response = await fetch(`${proxyUrl}/api/status/connection`, {
                            headers: { 'X-API-Key': apiKey }
                        });
                        const data = await response.json();

                        if (data.sessionKey) {
                            this.config.sessionKey = data.sessionKey;
                            this.addLog('success', `SessionKey récupéré: ${data.sessionKey}`);
                        }
                    } catch (error) {
                        this.addLog('error', `Erreur récupération SessionKey: ${error.message}`);
                    }
                },

                async calculateMAC(index) {
                    const formula = this.formulas[index];
                    const testId = '5';
                    const testUid = '12345678-1234-1234-1234-123456789abc';

                    const macString = this.config.sharedSecret + formula.sep1 + this.config.sessionKey + formula.sep2 + testId + formula.sep3 + testUid;

                    try {
                        // Use Web Crypto API
                        const encoder = new TextEncoder();
                        const data = encoder.encode(macString);
                        const hashBuffer = await crypto.subtle.digest('SHA-1', data);
                        const hashArray = Array.from(new Uint8Array(hashBuffer));
                        const hashBase64 = btoa(String.fromCharCode.apply(null, hashArray));

                        formula.result = {
                            string: macString,
                            mac: hashBase64
                        };

                        this.addLog('success', `MAC calculé pour "${formula.name}": ${hashBase64}`);
                    } catch (error) {
                        this.addLog('error', `Erreur calcul MAC: ${error.message}`);
                    }
                },

                loadCommandTemplate() {
                    const templates = {
                        'RemoteRoboTargetGetSet': {
                            ProfileName: '',
                        },
                        'RemoteRoboTargetGetBaseSequence': {
                            ProfileName: '',
                        },
                        'RemoteRoboTargetGetTarget': {
                            RefGuidSet: '', // Empty = all targets from all sets
                        },
                        'RemoteRoboTargetAddSet': {
                            Guid: this.generateGuid(),
                            Name: 'Test Set MAC',
                            ProfileName: 'Default.v2y',
                            IsDefault: false,
                            Status: 0,
                            Note: 'Test MAC validation'
                        }
                    };

                    this.commandParams = JSON.stringify(templates[this.selectedCommand] || {}, null, 2);
                },

                async sendTestCommand() {
                    if (!this.selectedFormulaIndex || !this.selectedCommand) {
                        this.addLog('error', 'Sélectionner une formule et une commande');
                        return;
                    }

                    this.isLoading = true;
                    const formula = this.formulas[this.selectedFormulaIndex];

                    try {
                        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
                        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

                        const params = JSON.parse(this.commandParams);

                        // Send to custom test endpoint
                        const response = await fetch(`${proxyUrl}/api/robotarget/test-mac`, {
                            method: 'POST',
                            headers: {
                                'X-API-Key': apiKey,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                method: this.selectedCommand,
                                params: params,
                                macFormula: {
                                    sep1: formula.sep1,
                                    sep2: formula.sep2,
                                    sep3: formula.sep3
                                }
                            })
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.addLog('success', `Commande envoyée avec succès\n${JSON.stringify(data, null, 2)}`);
                        } else {
                            this.addLog('error', `Erreur: ${data.message}\n${JSON.stringify(data, null, 2)}`);
                        }
                    } catch (error) {
                        this.addLog('error', `Erreur: ${error.message}`);
                    } finally {
                        this.isLoading = false;
                    }
                },

                generateGuid() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                },

                async runAutoTest() {
                    if (!this.autoTestCommand) {
                        this.addLog('error', 'Veuillez sélectionner une commande pour l\'auto-test');
                        return;
                    }

                    this.isAutoTesting = true;
                    this.autoTestResult = null;
                    this.autoTestProgress = 'Initialisation...';

                    try {
                        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
                        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

                        // Prepare command params based on command type
                        let params = {};
                        if (this.autoTestCommand === 'RemoteRoboTargetAddSet') {
                            params = {
                                Guid: this.generateGuid(),
                                Name: 'AutoTest Set',
                                ProfileName: 'Default.v2y',
                                IsDefault: false,
                                Status: 0,
                                Note: 'Auto-test MAC validation'
                            };
                        } else {
                            params = {
                                ProfileName: '',
                            };
                        }

                        this.addLog('info', `🤖 Démarrage auto-test pour: ${this.autoTestCommand}`);
                        this.autoTestProgress = 'Envoi des tests...';

                        const response = await fetch(`${proxyUrl}/api/robotarget/auto-test-mac`, {
                            method: 'POST',
                            headers: {
                                'X-API-Key': apiKey,
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                method: this.autoTestCommand,
                                params: params
                            })
                        });

                        const data = await response.json();

                        this.autoTestResult = data;

                        if (data.success) {
                            this.addLog('success', `✅ SUCCÈS! Formule trouvée: ${data.successfulVariant.name}\n${JSON.stringify(data.recommendation, null, 2)}`);
                        } else {
                            this.addLog('error', `❌ Aucune formule n'a fonctionné\n${data.message}`);
                            if (data.allResults) {
                                data.allResults.forEach((result, index) => {
                                    const status = result.success ? '✅' : '❌';
                                    this.addLog('info', `${status} Test ${index + 1}: ${result.variant}`);
                                });
                            }
                        }

                    } catch (error) {
                        this.addLog('error', `Erreur auto-test: ${error.message}`);
                        this.autoTestResult = {
                            success: false,
                            message: error.message
                        };
                    } finally {
                        this.isAutoTesting = false;
                        this.autoTestProgress = '';
                    }
                },

                addLog(type, message) {
                    this.logs.unshift({
                        timestamp: new Date().toLocaleTimeString(),
                        type: type,
                        message: message
                    });
                }
            }
        }
    </script>
</body>
</html>
