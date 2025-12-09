<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Voyager - STELLAR</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        .cosmic-card {
            background: linear-gradient(135deg, rgba(30, 27, 75, 0.9) 0%, rgba(74, 47, 189, 0.7) 100%);
            border: 1px solid rgba(139, 92, 246, 0.3);
            border-radius: 1rem;
            backdrop-filter: blur(10px);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.875rem;
            font-weight: 600;
        }
        .status-success {
            background-color: rgba(34, 197, 94, 0.2);
            color: rgb(134, 239, 172);
            border: 1px solid rgb(34, 197, 94);
        }
        .status-error {
            background-color: rgba(239, 68, 68, 0.2);
            color: rgb(252, 165, 165);
            border: 1px solid rgb(239, 68, 68);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 0.75rem 1.5rem;
            border-radius: 0.5rem;
            font-weight: 600;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            width: 100%;
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.5);
        }
        .btn-primary:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-900 via-purple-900 to-slate-900 min-h-screen" x-data="voyagerTest()">

    <div class="max-w-7xl mx-auto py-12 px-4">
        <!-- Header -->
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white mb-4">
                🔭 Test Connexion Voyager
            </h1>
            <p class="text-xl text-purple-200">
                Interface de test pour vérifier la connexion au matériel
            </p>
        </div>

        <!-- Status Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Proxy Status -->
            <div class="cosmic-card p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-white">Proxy API</h3>
                    <span class="status-badge"
                          :class="proxyConnected ? 'status-success' : 'status-error'"
                          x-text="proxyConnected ? 'Connecté' : 'Déconnecté'">
                    </span>
                </div>
                <p class="text-sm text-gray-400">http://localhost:3000</p>
            </div>

            <!-- Voyager Status -->
            <div class="cosmic-card p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-white">Voyager</h3>
                    <span class="status-badge"
                          :class="voyagerConnected ? 'status-success' : 'status-error'"
                          x-text="voyagerConnected ? 'Connecté' : 'Déconnecté'">
                    </span>
                </div>
                <p class="text-sm text-gray-400" x-text="voyagerVersion || '127.0.0.1:5950'"></p>
            </div>

            <!-- WebSocket Status -->
            <div class="cosmic-card p-6">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-lg font-semibold text-white">WebSocket</h3>
                    <span class="status-badge"
                          :class="wsConnected ? 'status-success' : 'status-error'"
                          x-text="wsConnected ? 'Connecté' : 'Déconnecté'">
                    </span>
                </div>
                <p class="text-sm text-gray-400">Temps réel</p>
            </div>
        </div>

        <!-- Actions -->
        <div class="cosmic-card p-6 mb-8">
            <h2 class="text-2xl font-bold text-white mb-6">🧪 Tests de connexion</h2>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <button @click="testHealth()"
                        class="btn-primary"
                        :disabled="loading">
                    <span x-show="!loading">🔌 Health Check</span>
                    <span x-show="loading">⏳ Test...</span>
                </button>

                <button @click="testConnection()"
                        class="btn-primary"
                        :disabled="loading">
                    <span x-show="!loading">📡 Test Connexion</span>
                    <span x-show="loading">⏳ Test...</span>
                </button>

                <button @click="enableDashboard()"
                        class="btn-primary"
                        :disabled="loading">
                    <span x-show="!loading">📊 Activer Dashboard</span>
                    <span x-show="loading">⏳ Activation...</span>
                </button>

                <button @click="getState()"
                        class="btn-primary"
                        :disabled="loading">
                    <span x-show="!loading">📈 État Système</span>
                    <span x-show="loading">⏳ Chargement...</span>
                </button>
            </div>
        </div>

        <!-- Dashboard Data -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8" x-show="state" style="display: none;">
            <!-- Voyager Info -->
            <div class="cosmic-card p-6">
                <h3 class="text-xl font-bold text-white mb-4">🔭 Voyager</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Statut:</span>
                        <span class="text-white font-semibold" x-text="getVoyagerStatus()"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Setup:</span>
                        <span class="text-white" x-text="state?.SETUPCONN ? '✅ Oui' : '❌ Non'"></span>
                    </div>
                </div>
            </div>

            <!-- Camera Info -->
            <div class="cosmic-card p-6">
                <h3 class="text-xl font-bold text-white mb-4">📷 Caméra</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Connectée:</span>
                        <span class="text-white" x-text="state?.CCDCONN ? '✅ Oui' : '❌ Non'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Température:</span>
                        <span class="text-white" x-text="(state?.CCDTEMP !== undefined ? state.CCDTEMP : '-') + '°C'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Cooling:</span>
                        <span class="text-white" x-text="state?.CCDCOOL ? '✅ Actif' : '❌ Inactif'"></span>
                    </div>
                </div>
            </div>

            <!-- Mount Info -->
            <div class="cosmic-card p-6">
                <h3 class="text-xl font-bold text-white mb-4">🔭 Monture</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Connectée:</span>
                        <span class="text-white" x-text="state?.MNTCONN ? '✅ Oui' : '❌ Non'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Parkée:</span>
                        <span class="text-white" x-text="state?.MNTPARK ? '✅ Oui' : '❌ Non'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Tracking:</span>
                        <span class="text-white" x-text="state?.MNTTRACK ? '✅ Actif' : '❌ Inactif'"></span>
                    </div>
                </div>
            </div>

            <!-- Focuser Info -->
            <div class="cosmic-card p-6">
                <h3 class="text-xl font-bold text-white mb-4">🎯 Focuser</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Connecté:</span>
                        <span class="text-white" x-text="state?.AFCONN ? '✅ Oui' : '❌ Non'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Position:</span>
                        <span class="text-white" x-text="state?.AFPOS || '-'"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Température:</span>
                        <span class="text-white" x-text="(state?.AFTEMP !== undefined ? state.AFTEMP : '-') + '°C'"></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Logs -->
        <div class="cosmic-card p-6">
            <h3 class="text-xl font-bold text-white mb-4">📝 Logs</h3>
            <div class="bg-black/50 rounded-lg p-4 h-64 overflow-y-auto font-mono text-sm">
                <template x-for="(log, index) in logs" :key="index">
                    <div class="mb-1" :class="{
                        'text-green-400': log.type === 'success',
                        'text-red-400': log.type === 'error',
                        'text-blue-400': log.type === 'info',
                        'text-gray-400': log.type === 'debug'
                    }">
                        <span class="text-gray-500" x-text="log.time"></span>
                        <span x-text="log.message"></span>
                    </div>
                </template>
            </div>
        </div>

    </div>

<script>
function voyagerTest() {
    return {
        proxyConnected: false,
        voyagerConnected: false,
        wsConnected: false,
        voyagerVersion: '',
        state: null,
        loading: false,
        logs: [],

        init() {
            this.log('info', '🚀 Interface de test initialisée');
            this.log('info', 'Proxy URL: http://localhost:3000');
        },

        log(type, message) {
            const time = new Date().toLocaleTimeString();
            this.logs.unshift({ time, type, message });
            if (this.logs.length > 50) this.logs.pop();
        },

        async testHealth() {
            this.loading = true;
            this.log('info', '🔍 Test Health Check...');

            try {
                const response = await fetch('http://localhost:3000/health');
                const data = await response.json();

                if (data.status === 'ok') {
                    this.proxyConnected = true;
                    this.voyagerConnected = data.voyager?.connected || false;
                    this.log('success', '✅ Health Check OK');
                    this.log('info', `⏱️ Uptime: ${Math.floor(data.uptime)}s`);
                } else {
                    this.log('error', '❌ Health Check échoué');
                }
            } catch (error) {
                this.proxyConnected = false;
                this.log('error', '❌ Erreur: ' + error.message);
                this.log('error', '💡 Vérifiez que le proxy tourne sur le port 3000');
            } finally {
                this.loading = false;
            }
        },

        async testConnection() {
            this.loading = true;
            this.log('info', '🔍 Test connexion Voyager...');

            try {
                const response = await fetch('http://localhost:3000/api/status/connection');
                const data = await response.json();

                if (data.success) {
                    this.voyagerConnected = data.isConnected;
                    this.voyagerVersion = data.version?.VOYVersion || '';
                    this.log('success', '✅ Connexion Voyager OK');
                    this.log('info', `📦 Version: ${this.voyagerVersion}`);
                    if (data.isAuthenticated) {
                        this.log('success', '🔐 Authentifié');
                    }
                } else {
                    this.log('error', '❌ Connexion Voyager échouée');
                }
            } catch (error) {
                this.log('error', '❌ Erreur: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async enableDashboard() {
            this.loading = true;
            this.log('info', '📊 Activation Dashboard Mode...');

            try {
                const response = await fetch('http://localhost:3000/api/dashboard/enable', {
                    method: 'POST'
                });
                const data = await response.json();

                if (data.success) {
                    this.log('success', '✅ Dashboard activé');
                    this.log('info', '⏳ Attente de 2 secondes...');
                    // Attendre 2 secondes puis récupérer l'état
                    setTimeout(() => this.getState(), 2000);
                } else {
                    this.log('error', '❌ Activation échouée');
                }
            } catch (error) {
                this.log('error', '❌ Erreur: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        async getState() {
            this.loading = true;
            this.log('info', '📥 Récupération état système...');

            try {
                const response = await fetch('http://localhost:3000/api/dashboard/state');
                const data = await response.json();

                if (data.success && data.data) {
                    this.state = data.data;
                    this.log('success', '✅ État système récupéré');
                    this.log('debug', `🔭 Statut Voyager: ${this.getVoyagerStatus()}`);
                    this.log('debug', `📷 Caméra: ${data.data.CCDCONN ? 'connectée' : 'déconnectée'}`);
                    this.log('debug', `🔭 Monture: ${data.data.MNTCONN ? 'connectée' : 'déconnectée'}`);
                } else {
                    this.log('error', '❌ Échec récupération état');
                    this.log('info', '💡 Activez d\'abord le Dashboard Mode');
                }
            } catch (error) {
                this.log('error', '❌ Erreur: ' + error.message);
            } finally {
                this.loading = false;
            }
        },

        getVoyagerStatus() {
            if (!this.state) return '-';
            const status = this.state.VOYSTAT;
            const map = { 0: 'STOPPED', 1: 'IDLE', 2: 'RUN', 3: 'ERROR' };
            return map[status] || 'UNKNOWN';
        }
    }
}
</script>

</body>
</html>
