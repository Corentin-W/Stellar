// Global state
let socket = null;
let proxyUrl = 'http://localhost:3000';
let apiKey = '';

// Initialize on load
document.addEventListener('DOMContentLoaded', () => {
    loadConfig();
    log('Interface de test chargée', 'info');
});

// Save/Load config
function loadConfig() {
    const savedUrl = localStorage.getItem('proxyUrl');
    const savedKey = localStorage.getItem('apiKey');

    if (savedUrl) {
        document.getElementById('proxyUrl').value = savedUrl;
        proxyUrl = savedUrl;
    }

    if (savedKey) {
        document.getElementById('apiKey').value = savedKey;
        apiKey = savedKey;
    }
}

function saveConfig() {
    proxyUrl = document.getElementById('proxyUrl').value;
    apiKey = document.getElementById('apiKey').value;

    localStorage.setItem('proxyUrl', proxyUrl);
    localStorage.setItem('apiKey', apiKey);

    log('Configuration sauvegardée', 'success');
}

// ============================================
// API Functions
// ============================================

async function apiRequest(endpoint, method = 'GET', body = null) {
    saveConfig();

    const headers = {
        'Content-Type': 'application/json',
    };

    if (apiKey) {
        headers['X-API-Key'] = apiKey;
    }

    const options = {
        method,
        headers,
    };

    if (body && method !== 'GET') {
        options.body = JSON.stringify(body);
    }

    try {
        const response = await fetch(proxyUrl + endpoint, options);
        const data = await response.json();

        if (response.ok) {
            updateStatus('apiStatus', 'connected');
            return { success: true, data };
        } else {
            return { success: false, error: data, status: response.status };
        }
    } catch (error) {
        updateStatus('apiStatus', 'disconnected');
        return { success: false, error: error.message };
    }
}

function displayResult(elementId, result) {
    const el = document.getElementById(elementId);
    if (result.success) {
        el.textContent = JSON.stringify(result.data, null, 2);
        el.style.color = '#4CAF50';
    } else {
        el.textContent = `Erreur: ${JSON.stringify(result.error || result, null, 2)}`;
        el.style.color = '#f44336';
    }
}

// Test connection
async function testConnection() {
    log('Test de connexion...', 'info');
    const result = await apiRequest('/health');

    if (result.success) {
        log('✅ Connexion réussie au proxy', 'success');

        // Check Voyager connection
        if (result.data.voyager) {
            if (result.data.voyager.connected) {
                updateStatus('voyagerStatus', 'connected');
                log('✅ Voyager connecté et authentifié', 'success');
            } else {
                updateStatus('voyagerStatus', 'disconnected');
                log('⚠️ Voyager non connecté', 'error');
            }
        }
    } else {
        log('❌ Impossible de se connecter au proxy', 'error');
    }

    displayResult('result-health', result);
}

// API Tests
async function apiHealthCheck() {
    log('Health check...', 'info');
    const result = await apiRequest('/health');
    displayResult('result-health', result);
    log(result.success ? '✅ Health check OK' : '❌ Health check failed', result.success ? 'success' : 'error');
}

async function apiConnectionStatus() {
    log('Récupération du statut de connexion...', 'info');
    const result = await apiRequest('/api/status/connection');
    displayResult('result-connection', result);

    if (result.success && result.data.isConnected) {
        updateStatus('voyagerStatus', 'connected');
        log('✅ Voyager connecté', 'success');
    }
}

async function apiDashboardState() {
    log('Récupération de l\'état du dashboard...', 'info');
    const result = await apiRequest('/api/dashboard/state');
    displayResult('result-dashboard', result);

    if (result.success && result.data.data) {
        updateDashboard(result.data.data);
        log('✅ Dashboard state récupéré', 'success');
    }
}

async function apiEnableDashboard() {
    log('Activation du mode Dashboard...', 'info');
    const result = await apiRequest('/api/dashboard/enable', 'POST');
    displayResult('result-enable-dashboard', result);
    log(result.success ? '✅ Dashboard activé' : '❌ Échec activation', result.success ? 'success' : 'error');
}

// ============================================
// Control Commands
// ============================================

async function cmdAbort() {
    log('⛔ Envoi commande ABORT...', 'info');
    const result = await apiRequest('/api/control/abort', 'POST');
    displayResult('result-abort', result);
    log(result.success ? '✅ Abort envoyé' : '❌ Échec abort', result.success ? 'success' : 'error');
}

async function cmdToggleTarget() {
    const targetGuid = document.getElementById('targetGuid').value;
    const activate = document.getElementById('activateTarget').checked;

    if (!targetGuid) {
        log('❌ Target GUID requis', 'error');
        return;
    }

    log(`${activate ? 'Activation' : 'Désactivation'} du target ${targetGuid}...`, 'info');
    const result = await apiRequest('/api/control/toggle', 'POST', {
        targetGuid,
        activate,
    });
    displayResult('result-toggle', result);
    log(result.success ? '✅ Target toggled' : '❌ Échec toggle', result.success ? 'success' : 'error');
}

async function cmdTakeShot() {
    const exposure = parseFloat(document.getElementById('exposure').value);
    const binning = parseInt(document.getElementById('binning').value);
    const filter = parseInt(document.getElementById('filter').value);

    log(`📸 Prise de photo: ${exposure}s, bin ${binning}, filtre ${filter}...`, 'info');
    const result = await apiRequest('/api/camera/shot', 'POST', {
        exposure,
        binning,
        filter,
    });
    displayResult('result-shot', result);
    log(result.success ? '✅ Shot commandé' : '❌ Échec shot', result.success ? 'success' : 'error');
}

async function cmdPark() {
    log('🏠 Park télescope...', 'info');
    const result = await apiRequest('/api/telescope/park', 'POST');
    displayResult('result-telescope', result);
    log(result.success ? '✅ Park commandé' : '❌ Échec park', result.success ? 'success' : 'error');
}

async function cmdUnpark() {
    log('🔓 Unpark télescope...', 'info');
    const result = await apiRequest('/api/telescope/unpark', 'POST');
    displayResult('result-telescope', result);
    log(result.success ? '✅ Unpark commandé' : '❌ Échec unpark', result.success ? 'success' : 'error');
}

async function cmdStartTracking() {
    log('▶️ Démarrage tracking...', 'info');
    const result = await apiRequest('/api/telescope/tracking/start', 'POST');
    displayResult('result-telescope', result);
    log(result.success ? '✅ Tracking démarré' : '❌ Échec tracking', result.success ? 'success' : 'error');
}

async function cmdStopTracking() {
    log('⏹️ Arrêt tracking...', 'info');
    const result = await apiRequest('/api/telescope/tracking/stop', 'POST');
    displayResult('result-telescope', result);
    log(result.success ? '✅ Tracking arrêté' : '❌ Échec stop tracking', result.success ? 'success' : 'error');
}

// ============================================
// WebSocket
// ============================================

function connectWebSocket() {
    saveConfig();

    if (socket && socket.connected) {
        log('⚠️ WebSocket déjà connecté', 'info');
        return;
    }

    log('Connexion WebSocket...', 'info');

    socket = io(proxyUrl, {
        transports: ['websocket', 'polling'],
        reconnection: true,
        reconnectionDelay: 1000,
        reconnectionAttempts: 5,
    });

    socket.on('connect', () => {
        updateStatus('wsStatus', 'connected');
        log('✅ WebSocket connecté', 'success');
        addEvent('connect', { socketId: socket.id });
    });

    socket.on('disconnect', (reason) => {
        updateStatus('wsStatus', 'disconnected');
        log(`❌ WebSocket déconnecté: ${reason}`, 'error');
        addEvent('disconnect', { reason });
    });

    socket.on('initialState', (data) => {
        log('📊 État initial reçu', 'info');
        addEvent('initialState', data);

        if (data.controlData) {
            updateDashboard(data.controlData);
        }
    });

    socket.on('controlData', (data) => {
        const showControlData = document.getElementById('showControlData').checked;
        if (showControlData) {
            addEvent('controlData', data.parsed || data);
        }
        updateDashboard(data);
    });

    socket.on('newJPG', (data) => {
        log('📷 Nouveau JPG preview reçu', 'success');
        addEvent('newJPG', {
            file: data.parsed?.filename || data.File,
            hfd: data.parsed?.hfd || data.HFD,
            exposure: data.parsed?.exposure || data.Expo,
            filter: data.parsed?.filter || data.Filter,
        });
    });

    socket.on('shotRunning', (data) => {
        const progress = data.parsed?.progress || 0;
        log(`📸 Shot en cours: ${progress.toFixed(1)}%`, 'info');
        addEvent('shotRunning', data.parsed || data, 'signal');
    });

    socket.on('signal', (data) => {
        log(`🔔 Signal: ${data.description || data.Code}`, 'info');
        addEvent('signal', data, 'signal');
    });

    socket.on('newFITReady', (data) => {
        log(`✅ Nouvelle image FITS: ${data.parsed?.filename || data.File}`, 'success');
        addEvent('newFITReady', data.parsed || data, 'success');
    });

    socket.on('remoteActionResult', (data) => {
        const status = data.parsed?.status || 'UNKNOWN';
        log(`📥 Résultat commande: ${status}`, status === 'OK' ? 'success' : 'error');
        addEvent('remoteActionResult', data.parsed || data, status === 'OK' ? 'success' : 'error');
    });

    socket.on('connectionState', (data) => {
        log(`🔗 État connexion Voyager: ${data.status}`, 'info');
        addEvent('connectionState', data);

        if (data.status === 'connected') {
            updateStatus('voyagerStatus', 'connected');
        } else {
            updateStatus('voyagerStatus', 'disconnected');
        }
    });

    socket.on('error', (error) => {
        log(`❌ Erreur WebSocket: ${error}`, 'error');
    });
}

function disconnectWebSocket() {
    if (socket) {
        socket.disconnect();
        socket = null;
        updateStatus('wsStatus', 'disconnected');
        log('WebSocket déconnecté manuellement', 'info');
    }
}

// ============================================
// Dashboard Update
// ============================================

function updateDashboard(data) {
    const parsed = data.parsed || {};

    // Voyager Status
    setText('voy-status', parsed.voyagerStatus || data.VOYSTAT || '-');
    setText('voy-setup', formatBool(parsed.setupConnected || data.SETUPCONN));

    // Camera
    if (parsed.camera) {
        setText('cam-conn', formatBool(parsed.camera.connected));
        setText('cam-temp', formatValue(parsed.camera.temperature, '°C'));
        setText('cam-setpoint', formatValue(parsed.camera.setpoint, '°C'));
        setText('cam-power', formatValue(parsed.camera.power, '%'));
        setText('cam-cooling', formatBool(parsed.camera.cooling));
    }

    // Mount
    if (parsed.mount) {
        setText('mnt-conn', formatBool(parsed.mount.connected));
        setText('mnt-park', formatBool(parsed.mount.parked));
        setText('mnt-ra', parsed.mount.ra || '-');
        setText('mnt-dec', parsed.mount.dec || '-');
        setText('mnt-track', formatBool(parsed.mount.tracking));
    }

    // Focuser
    if (parsed.focuser) {
        setText('foc-conn', formatBool(parsed.focuser.connected));
        setText('foc-pos', formatValue(parsed.focuser.position));
        setText('foc-temp', formatValue(parsed.focuser.temperature, '°C'));
    }

    // Sequence
    if (parsed.sequence) {
        setText('seq-name', parsed.sequence.name || '-');
        setText('seq-remain', parsed.sequence.remaining || '-');
    }

    // Guiding
    if (parsed.guiding) {
        setText('guide-status', parsed.guiding.status || '-');
        setText('guide-x', formatValue(parsed.guiding.rmsX, '"'));
        setText('guide-y', formatValue(parsed.guiding.rmsY, '"'));
    }
}

// ============================================
// Utilities
// ============================================

function updateStatus(elementId, status) {
    const el = document.getElementById(elementId);
    el.classList.remove('connected', 'disconnected', 'pending');
    el.classList.add(status);

    const statusText = {
        'connected': 'Connecté',
        'disconnected': 'Déconnecté',
        'pending': 'En attente',
    };

    const prefix = el.textContent.split(':')[0];
    el.textContent = `${prefix}: ${statusText[status]}`;
}

function setText(elementId, value) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = value;
    }
}

function formatBool(value) {
    if (value === true || value === 'true' || value === 1) return '✅ Oui';
    if (value === false || value === 'false' || value === 0) return '❌ Non';
    return '-';
}

function formatValue(value, unit = '') {
    if (value === null || value === undefined || value === -123456789 || value === 123456789) {
        return '-';
    }
    return `${value}${unit}`;
}

// ============================================
// Events Console
// ============================================

function addEvent(type, data, cssClass = '') {
    const eventsDiv = document.getElementById('events');
    const eventItem = document.createElement('div');
    eventItem.className = `event-item ${cssClass}`;

    const timestamp = new Date().toLocaleTimeString('fr-FR');

    eventItem.innerHTML = `
        <div class="event-timestamp">${timestamp}</div>
        <div class="event-type">${type}</div>
        <div class="event-data">${JSON.stringify(data, null, 2)}</div>
    `;

    eventsDiv.appendChild(eventItem);

    // Auto scroll
    if (document.getElementById('autoScroll').checked) {
        eventsDiv.scrollTop = eventsDiv.scrollHeight;
    }

    // Limit events (keep last 100)
    while (eventsDiv.children.length > 100) {
        eventsDiv.removeChild(eventsDiv.firstChild);
    }
}

function clearEvents() {
    document.getElementById('events').innerHTML = '';
    log('Événements effacés', 'info');
}

// ============================================
// Logs Console
// ============================================

function log(message, type = 'info') {
    const logsDiv = document.getElementById('logs');
    const logItem = document.createElement('div');
    logItem.className = `log-item ${type}`;

    const timestamp = new Date().toLocaleTimeString('fr-FR');
    const icon = {
        'info': 'ℹ️',
        'success': '✅',
        'error': '❌',
    }[type] || 'ℹ️';

    logItem.innerHTML = `
        <div class="event-timestamp">${timestamp}</div>
        <div class="event-data">${icon} ${message}</div>
    `;

    logsDiv.appendChild(logItem);

    // Auto scroll
    logsDiv.scrollTop = logsDiv.scrollHeight;

    // Limit logs (keep last 100)
    while (logsDiv.children.length > 100) {
        logsDiv.removeChild(logsDiv.firstChild);
    }

    console.log(`[${timestamp}] ${message}`);
}

function clearLogs() {
    document.getElementById('logs').innerHTML = '';
}
