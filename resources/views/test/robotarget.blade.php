<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>🔬 Test RoboTarget - Stellar</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-900 text-white">
    <div x-data="robotargetTest()" class="container mx-auto px-4 py-8 max-w-7xl">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-4xl font-bold mb-2">🔬 Test RoboTarget</h1>
            <p class="text-gray-400">Interface de test pour la connexion Voyager → Matériel</p>
            <a href="{{ route('dashboard') }}" class="text-blue-400 hover:text-blue-300 text-sm">← Retour au dashboard</a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- LEFT COLUMN: Status & Controls -->
            <div class="space-y-6">

                <!-- Connection Status -->
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span>📡</span>
                        Statut de Connexion
                    </h2>

                    <div class="space-y-3">
                        <!-- Laravel → Proxy -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">Laravel → Proxy</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium"
                                  :class="proxyStatus === 'connected' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'">
                                <span x-text="proxyStatus === 'connected' ? '✓ Connecté' : '✗ Déconnecté'"></span>
                            </span>
                        </div>

                        <!-- Proxy → Voyager -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">Proxy → Voyager</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium"
                                  :class="voyagerStatus === 'connected' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'">
                                <span x-text="voyagerStatus === 'connected' ? '✓ Connecté' : '✗ Déconnecté'"></span>
                            </span>
                        </div>

                        <!-- Voyager → Matériel -->
                        <div class="flex items-center justify-between">
                            <span class="text-gray-300">Voyager → Matériel</span>
                            <span class="px-3 py-1 rounded-full text-sm font-medium"
                                  :class="equipmentStatus === 'ready' ? 'bg-green-500/20 text-green-400' : 'bg-yellow-500/20 text-yellow-400'">
                                <span x-text="equipmentStatus === 'ready' ? '✓ Prêt' : '⚠ Vérifier'"></span>
                            </span>
                        </div>
                    </div>

                    <button @click="checkStatus()"
                            class="w-full mt-4 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                        🔄 Rafraîchir le statut
                    </button>
                </div>

                <!-- Quick Test Target -->
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span>🎯</span>
                        Test Rapide
                    </h2>

                    <div class="space-y-4">
                        <!-- Preset Targets -->
                        <div>
                            <label class="block text-sm font-medium mb-2">Cible de test</label>
                            <select x-model="selectedPreset" @change="loadPreset()" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2">
                                <option value="">-- Choisir une cible --</option>
                                <option value="m42">M42 - Nébuleuse d'Orion (Facile)</option>
                                <option value="m31">M31 - Galaxie d'Andromède</option>
                                <option value="m51">M51 - Galaxie du Tourbillon</option>
                                <option value="custom">✏️ Configuration manuelle</option>
                            </select>
                        </div>

                        <!-- Target Details (shown when preset selected) -->
                        <div x-show="selectedPreset && selectedPreset !== 'custom'" class="bg-gray-900/50 rounded-lg p-4 space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Nom:</span>
                                <span x-text="testTarget.target_name"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">RA:</span>
                                <span x-text="testTarget.ra_j2000"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">DEC:</span>
                                <span x-text="testTarget.dec_j2000"></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-400">Shots:</span>
                                <span x-text="testTarget.shots.length + ' poses'"></span>
                            </div>
                        </div>

                        <!-- Manual Config (shown when custom selected) -->
                        <div x-show="selectedPreset === 'custom'" class="space-y-3">
                            <input type="text" x-model="testTarget.target_name" placeholder="Nom de la cible" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2">
                            <input type="text" x-model="testTarget.ra_j2000" placeholder="RA (HH:MM:SS)" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2">
                            <input type="text" x-model="testTarget.dec_j2000" placeholder="DEC (±DD:MM:SS)" class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2">
                        </div>

                        <!-- Base Sequence GUID (always shown when preset selected) -->
                        <div x-show="selectedPreset" class="space-y-2">
                            <label class="block text-sm font-medium text-gray-300">
                                Base Sequence GUID
                                <span class="text-xs text-gray-500">(requis - créer une séquence dans Voyager)</span>
                            </label>

                            <!-- Button to load sequences from Voyager -->
                            <button
                                @click="loadBaseSequences()"
                                type="button"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm transition flex items-center justify-center gap-2">
                                <span>📋 Charger les séquences depuis Voyager</span>
                            </button>

                            <!-- Dropdown to select a sequence (shown after loading) -->
                            <select
                                x-show="availableSequences.length > 0"
                                x-model="testTarget.base_sequence_guid"
                                @change="onSequenceSelected()"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 text-sm">
                                <option value="">-- Sélectionner une séquence --</option>
                                <template x-for="seq in availableSequences" :key="seq.guid">
                                    <option :value="seq.guid" x-text="seq.name + ' (' + seq.guid.substring(0, 8) + '...)'"></option>
                                </template>
                            </select>

                            <!-- Manual input (fallback) -->
                            <input
                                type="text"
                                x-model="testTarget.base_sequence_guid"
                                placeholder="00000000-0000-0000-0000-000000000000"
                                class="w-full bg-gray-700 border border-gray-600 rounded-lg px-4 py-2 font-mono text-sm"
                                pattern="[0-9a-fA-F]{8}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{4}-[0-9a-fA-F]{12}">
                            <p class="text-xs text-yellow-400">
                                ⚠️ Ce GUID doit correspondre à une séquence template existante dans Voyager
                            </p>
                        </div>

                        <!-- Submit Button -->
                        <button @click="submitTestTarget()"
                                :disabled="!selectedPreset || isLoading"
                                :class="isLoading ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                                class="w-full bg-green-600 text-white px-4 py-3 rounded-lg font-medium transition flex items-center justify-center gap-2">
                            <span x-show="!isLoading">🚀 Envoyer à Voyager</span>
                            <span x-show="isLoading" class="flex items-center gap-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Envoi en cours...
                            </span>
                        </button>

                        <!-- Success/Error Messages -->
                        <div x-show="successMessage" class="bg-green-500/20 border border-green-500 text-green-400 px-4 py-3 rounded-lg text-sm">
                            <span x-text="successMessage"></span>
                        </div>
                        <div x-show="errorMessage" class="bg-red-500/20 border border-red-500 text-red-400 px-4 py-3 rounded-lg text-sm">
                            <span x-text="errorMessage"></span>
                        </div>
                    </div>
                </div>

                <!-- Debug Commands -->
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span>🛠️</span>
                        Commandes de Debug
                    </h2>

                    <div class="grid grid-cols-2 gap-2">
                        <button @click="sendCommand('ping')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm transition">
                            📡 Ping
                        </button>
                        <button @click="sendCommand('status')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm transition">
                            📊 Status
                        </button>
                        <button @click="sendCommand('dashboard')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm transition">
                            📈 Dashboard
                        </button>
                        <button @click="sendCommand('targets')" class="bg-gray-700 hover:bg-gray-600 px-3 py-2 rounded text-sm transition">
                            🎯 List Targets
                        </button>
                    </div>
                </div>
            </div>

            <!-- RIGHT COLUMN: Logs & Events -->
            <div class="space-y-6">

                <!-- Event Log -->
                <div class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-xl font-bold flex items-center gap-2">
                            <span>📜</span>
                            Journal d'Événements
                        </h2>
                        <button @click="logs = []" class="text-sm text-gray-400 hover:text-white transition">
                            🗑️ Effacer
                        </button>
                    </div>

                    <div class="bg-gray-900 rounded-lg p-4 h-[500px] overflow-y-auto font-mono text-xs space-y-1">
                        <template x-for="log in logs.slice().reverse()" :key="log.id">
                            <div class="flex gap-2">
                                <span class="text-gray-500" x-text="log.time"></span>
                                <span :class="{
                                    'text-green-400': log.type === 'success',
                                    'text-red-400': log.type === 'error',
                                    'text-blue-400': log.type === 'info',
                                    'text-yellow-400': log.type === 'warning'
                                }" x-text="log.message"></span>
                            </div>
                        </template>
                        <div x-show="logs.length === 0" class="text-gray-500 text-center py-8">
                            Aucun événement pour le moment...
                        </div>
                    </div>
                </div>

                <!-- Current Target Info -->
                <div x-show="currentTarget" class="bg-gray-800 rounded-lg p-6 border border-gray-700">
                    <h2 class="text-xl font-bold mb-4 flex items-center gap-2">
                        <span>🎯</span>
                        Cible Active
                    </h2>

                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-400">GUID:</span>
                            <span class="font-mono text-xs" x-text="currentTarget?.guid"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Statut:</span>
                            <span class="px-2 py-1 rounded text-xs font-medium"
                                  :class="{
                                      'bg-green-500/20 text-green-400': currentTarget?.status === 'completed',
                                      'bg-blue-500/20 text-blue-400': currentTarget?.status === 'executing',
                                      'bg-yellow-500/20 text-yellow-400': currentTarget?.status === 'active',
                                      'bg-gray-500/20 text-gray-400': currentTarget?.status === 'pending'
                                  }"
                                  x-text="currentTarget?.status"></span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-400">Images:</span>
                            <span x-text="currentTarget?.images_captured || 0"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function robotargetTest() {
            return {
                proxyStatus: 'unknown',
                voyagerStatus: 'unknown',
                equipmentStatus: 'unknown',
                isLoading: false,
                selectedPreset: '',
                successMessage: '',
                errorMessage: '',
                testTarget: {
                    target_name: '',
                    ra_j2000: '',
                    dec_j2000: '',
                    priority: 0,
                    c_moon_down: false,
                    c_alt_min: 30,
                    base_sequence_guid: '',
                    shots: []
                },
                currentTarget: null,
                logs: [],
                availableSequences: [],
                presets: {
                    m42: {
                        target_name: 'M42 - Nébuleuse d\'Orion (Test)',
                        ra_j2000: '05:35:17',
                        dec_j2000: '-05:23:28',
                        priority: 0,
                        c_moon_down: false,
                        c_alt_min: 30,
                        base_sequence_guid: '',
                        shots: [
                            { filter_index: 0, filter_name: 'Luminance', exposure: 60, num: 3, gain: 100, offset: 50, bin: 1 }
                        ]
                    },
                    m31: {
                        target_name: 'M31 - Andromède (Test)',
                        ra_j2000: '00:42:44',
                        dec_j2000: '+41:16:09',
                        priority: 0,
                        c_moon_down: false,
                        c_alt_min: 30,
                        base_sequence_guid: '',
                        shots: [
                            { filter_index: 0, filter_name: 'Luminance', exposure: 120, num: 2, gain: 100, offset: 50, bin: 1 }
                        ]
                    },
                    m51: {
                        target_name: 'M51 - Tourbillon (Test)',
                        ra_j2000: '13:29:53',
                        dec_j2000: '+47:11:43',
                        priority: 0,
                        c_moon_down: false,
                        c_alt_min: 30,
                        base_sequence_guid: '',
                        shots: [
                            { filter_index: 0, filter_name: 'Luminance', exposure: 90, num: 3, gain: 100, offset: 50, bin: 1 }
                        ]
                    }
                },

                init() {
                    this.checkStatus();
                    this.addLog('info', 'Interface de test chargée');
                    setInterval(() => this.checkStatus(), 10000);
                },

                async checkStatus() {
                    try {
                        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
                        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

                        const response = await fetch(proxyUrl + '/api/status/connection', {
                            headers: { 'X-API-Key': apiKey }
                        });

                        if (response.ok) {
                            const data = await response.json();
                            this.proxyStatus = 'connected';
                            this.voyagerStatus = data.isConnected ? 'connected' : 'disconnected';
                            this.equipmentStatus = data.isAuthenticated ? 'ready' : 'not_ready';
                            this.addLog('success', 'Statut mis à jour');
                        } else {
                            this.proxyStatus = 'disconnected';
                            this.addLog('error', 'Impossible de contacter le proxy');
                        }
                    } catch (error) {
                        this.proxyStatus = 'disconnected';
                        this.addLog('error', 'Erreur réseau: ' + error.message);
                    }
                },

                loadPreset() {
                    if (this.selectedPreset && this.selectedPreset !== 'custom') {
                        this.testTarget = { ...this.presets[this.selectedPreset] };
                        this.addLog('info', `Preset chargé: ${this.testTarget.target_name}`);
                    } else if (this.selectedPreset === 'custom') {
                        this.testTarget = {
                            target_name: '',
                            ra_j2000: '',
                            dec_j2000: '',
                            priority: 0,
                            c_moon_down: false,
                            c_alt_min: 30,
                            base_sequence_guid: '',
                            shots: [{ filter_index: 0, filter_name: 'Luminance', exposure: 60, num: 3, gain: 100, offset: 50, bin: 1 }]
                        };
                    }
                    this.errorMessage = '';
                    this.successMessage = '';
                },

                async submitTestTarget() {
                    this.isLoading = true;
                    this.errorMessage = '';
                    this.successMessage = '';

                    try {
                        this.addLog('info', `Envoi de la cible: ${this.testTarget.target_name}`);

                        const payload = {
                            guid_set: this.generateUUID(),
                            guid_target: this.generateUUID(),
                            ...this.testTarget
                        };

                        const response = await fetch('/test/robotarget/targets/complete', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify(payload)
                        });

                        const data = await response.json();

                        if (data.success) {
                            this.successMessage = '✅ Target envoyée à Voyager avec succès !';
                            this.currentTarget = data.target;
                            this.addLog('success', `Target créée: ${data.target.guid}`);
                            this.selectedPreset = '';
                        } else {
                            this.errorMessage = '❌ ' + (data.message || 'Erreur inconnue');
                            this.addLog('error', data.message || 'Erreur lors de la création');
                        }
                    } catch (error) {
                        this.errorMessage = '❌ Erreur réseau: ' + error.message;
                        this.addLog('error', 'Erreur réseau: ' + error.message);
                    } finally {
                        this.isLoading = false;
                    }
                },

                async loadBaseSequences() {
                    this.addLog('info', 'Chargement des séquences depuis Voyager...');
                    try {
                        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
                        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

                        const response = await fetch(`${proxyUrl}/api/robotarget/base-sequences`, {
                            headers: { 'X-API-Key': apiKey }
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }

                        const data = await response.json();

                        if (data.success && data.baseSequences) {
                            // Parse the response to extract sequences
                            // The format depends on Voyager's response structure
                            const sequences = this.parseBaseSequences(data.baseSequences);
                            this.availableSequences = sequences;
                            this.addLog('success', `${sequences.length} séquence(s) chargée(s)`);
                        } else {
                            this.addLog('warning', 'Aucune séquence trouvée');
                        }
                    } catch (error) {
                        this.addLog('error', `Erreur: ${error.message}`);
                        console.error('Error loading base sequences:', error);
                    }
                },

                parseBaseSequences(response) {
                    // Parse Voyager response to extract base sequences
                    // According to official Voyager documentation, the response format is:
                    // ParamRet.list = Array of { guid, basesequencename, filename, profilename, isdefault, status, note }
                    const sequences = [];

                    try {
                        // Log raw response for debugging
                        console.log('Raw base sequences response:', response);

                        // Official format: ParamRet.list array
                        if (response.ParamRet && response.ParamRet.list) {
                            const list = response.ParamRet.list;
                            if (Array.isArray(list)) {
                                list.forEach(seq => {
                                    sequences.push({
                                        guid: seq.guid,
                                        name: seq.basesequencename || seq.filename || 'Unnamed Sequence',
                                        profile: seq.profilename || 'Unknown',
                                        status: seq.status === 0 ? 'Enabled' : 'Disabled'
                                    });
                                });
                            }
                        }
                        // Fallback for parsed format
                        else if (response.parsed && Array.isArray(response.parsed)) {
                            response.parsed.forEach(seq => {
                                sequences.push({
                                    guid: seq.guid || seq.Guid,
                                    name: seq.basesequencename || seq.Name || 'Unnamed Sequence',
                                    profile: seq.profilename || 'Unknown',
                                    status: seq.status === 0 ? 'Enabled' : 'Disabled'
                                });
                            });
                        }

                        this.addLog('info', `Parsed ${sequences.length} sequences from response`);
                    } catch (error) {
                        this.addLog('error', `Parse error: ${error.message}`);
                        console.error('Parse error:', error);
                    }

                    return sequences;
                },

                onSequenceSelected() {
                    const selected = this.availableSequences.find(s => s.guid === this.testTarget.base_sequence_guid);
                    if (selected) {
                        this.addLog('info', `Séquence sélectionnée: ${selected.name}`);
                    }
                },

                async sendCommand(cmd) {
                    this.addLog('info', `Commande: ${cmd}`);
                    try {
                        const proxyUrl = "{{ config('services.voyager.proxy_url') }}";
                        const apiKey = "{{ config('services.voyager.proxy_api_key') }}";

                        let endpoint = '';
                        switch(cmd) {
                            case 'ping':
                            case 'status':
                                endpoint = `/api/status/connection`;
                                break;
                            case 'dashboard':
                                endpoint = `/api/dashboard/state`;
                                break;
                            case 'targets':
                                endpoint = `/api/robotarget/sets`;
                                break;
                            default:
                                endpoint = `/api/${cmd}`;
                        }

                        const response = await fetch(`${proxyUrl}${endpoint}`, {
                            headers: { 'X-API-Key': apiKey }
                        });
                        const data = await response.json();
                        this.addLog('success', `Réponse ${cmd}: ${JSON.stringify(data).substring(0, 100)}`);
                        console.log(`[${cmd}]`, data);
                    } catch (error) {
                        this.addLog('error', `Erreur ${cmd}: ${error.message}`);
                    }
                },

                addLog(type, message) {
                    this.logs.push({
                        id: Date.now() + Math.random(),
                        time: new Date().toLocaleTimeString(),
                        type: type,
                        message: message
                    });
                    if (this.logs.length > 100) this.logs.shift();
                },

                generateUUID() {
                    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                        const r = Math.random() * 16 | 0;
                        const v = c === 'x' ? r : (r & 0x3 | 0x8);
                        return v.toString(16);
                    });
                }
            }
        }
    </script>
</body>
</html>
