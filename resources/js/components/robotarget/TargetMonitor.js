/**
 * Target Monitor Component
 *
 * Real-time monitoring of RoboTarget execution via WebSocket
 */
import { getVoyagerWebSocket } from '../../services/VoyagerWebSocket.js';

export default (targetGuid) => ({
  // State
  target: null,
  isLoading: true,
  error: null,

  // Real-time session data
  session: {
    status: 'idle', // idle, running, completed, error, aborted
    guidSession: null,
    startTime: null,
    endTime: null,
    progress: 0,
    currentShot: 0,
    totalShots: 0,
    currentFilter: null,
    currentExposure: null,
    hfd: null,
    shotsCaptured: 0,
    result: null,
    reason: null,
  },

  // WebSocket connection
  ws: null,
  isConnected: false,
  unsubscribers: [],

  // Initialize
  init() {
    this.loadTarget();
    this.initWebSocket();
  },

  // Load target data from API
  async loadTarget() {
    this.isLoading = true;
    this.error = null;

    try {
      const response = await fetch(`/api/robotarget/targets/${targetGuid}`, {
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
      });

      const data = await response.json();

      if (data.success) {
        this.target = data.target;

        // Load existing session data if available
        if (this.target.latest_session) {
          this.updateSessionFromData(this.target.latest_session);
        }
      } else {
        this.error = data.message || 'Erreur lors du chargement de la target';
      }
    } catch (error) {
      this.error = 'Erreur réseau';
      console.error('Load target error:', error);
    } finally {
      this.isLoading = false;
    }
  },

  // Initialize WebSocket connection
  initWebSocket() {
    try {
      this.ws = getVoyagerWebSocket();

      // Subscribe to connection events
      const connectedUnsub = this.ws.on('connected', () => {
        this.isConnected = true;
        console.log('WebSocket connected');
      });

      const disconnectedUnsub = this.ws.on('disconnected', () => {
        this.isConnected = false;
        console.log('WebSocket disconnected');
      });

      // Subscribe to RoboTarget events for this specific target
      const sessionStartUnsub = this.ws.on('roboTargetSessionStart', (data) => {
        if (data.parsed?.guidTarget === targetGuid) {
          this.handleSessionStart(data.parsed);
        }
      });

      const progressUnsub = this.ws.on('roboTargetProgress', (data) => {
        if (data.parsed?.guidTarget === targetGuid) {
          this.handleProgress(data.parsed);
        }
      });

      const shotCompleteUnsub = this.ws.on('roboTargetShotComplete', (data) => {
        if (data.parsed?.guidTarget === targetGuid) {
          this.handleShotComplete(data.parsed);
        }
      });

      const sessionCompleteUnsub = this.ws.on('roboTargetSessionComplete', (data) => {
        if (data.parsed?.guidTarget === targetGuid) {
          this.handleSessionComplete(data.parsed);
        }
      });

      const sessionAbortUnsub = this.ws.on('roboTargetSessionAbort', (data) => {
        if (data.parsed?.guidTarget === targetGuid) {
          this.handleSessionAbort(data.parsed);
        }
      });

      const errorUnsub = this.ws.on('roboTargetError', (data) => {
        if (data.parsed?.guidTarget === targetGuid) {
          this.handleError(data.parsed);
        }
      });

      // Store unsubscribers for cleanup
      this.unsubscribers = [
        connectedUnsub,
        disconnectedUnsub,
        sessionStartUnsub,
        progressUnsub,
        shotCompleteUnsub,
        sessionCompleteUnsub,
        sessionAbortUnsub,
        errorUnsub,
      ];

      this.isConnected = this.ws.isConnected;

    } catch (error) {
      console.error('Failed to initialize WebSocket:', error);
      this.error = 'WebSocket non disponible';
    }
  },

  // Event handlers
  handleSessionStart(data) {
    console.log('Session started:', data);

    this.session = {
      status: 'running',
      guidSession: data.guidSession,
      startTime: data.startTime,
      endTime: null,
      progress: 0,
      currentShot: 0,
      totalShots: this.target?.shots?.length || 0,
      currentFilter: null,
      currentExposure: null,
      hfd: null,
      shotsCaptured: 0,
      result: null,
      reason: null,
    };

    // Reload target to get updated status
    this.loadTarget();
  },

  handleProgress(data) {
    console.log('Progress update:', data);

    this.session.progress = data.progress || 0;
    this.session.currentShot = data.currentShot || 0;
    this.session.totalShots = data.totalShots || this.session.totalShots;
    this.session.currentFilter = data.currentFilter;
    this.session.currentExposure = data.currentExposure;
    this.session.hfd = data.hfd;
  },

  handleShotComplete(data) {
    console.log('Shot completed:', data);

    this.session.shotsCaptured++;
  },

  handleSessionComplete(data) {
    console.log('Session completed:', data);

    this.session.status = 'completed';
    this.session.endTime = data.sessionEnd;
    this.session.progress = 100;
    this.session.result = data.result;
    this.session.shotsCaptured = data.shotsCaptured || this.session.shotsCaptured;

    // Reload target to get final status and credits update
    this.loadTarget();
  },

  handleSessionAbort(data) {
    console.log('Session aborted:', data);

    this.session.status = 'aborted';
    this.session.endTime = data.sessionEnd;
    this.session.reason = data.reason;

    // Reload target
    this.loadTarget();
  },

  handleError(data) {
    console.error('Session error:', data);

    this.session.status = 'error';
    this.session.reason = data.errorMessage || data.reason;

    // Reload target
    this.loadTarget();
  },

  // Update session from existing data
  updateSessionFromData(sessionData) {
    this.session = {
      status: this.mapResultToStatus(sessionData.result),
      guidSession: sessionData.session_guid,
      startTime: sessionData.session_start,
      endTime: sessionData.session_end,
      progress: sessionData.result === 1 ? 100 : 0,
      currentShot: 0,
      totalShots: this.target?.shots?.length || 0,
      currentFilter: null,
      currentExposure: null,
      hfd: sessionData.hfd_mean,
      shotsCaptured: sessionData.images_captured || 0,
      result: sessionData.result,
      reason: null,
    };
  },

  // Map result code to status
  mapResultToStatus(result) {
    const statusMap = {
      1: 'completed',
      2: 'aborted',
      3: 'error',
    };
    return statusMap[result] || 'idle';
  },

  // Actions
  async submitTarget() {
    if (this.isLoading) return;

    this.isLoading = true;
    this.error = null;

    try {
      const response = await fetch(`/api/robotarget/targets/${targetGuid}/submit`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
      });

      const data = await response.json();

      if (data.success) {
        // Reload target to reflect new status
        await this.loadTarget();
      } else {
        this.error = data.message || 'Erreur lors de la soumission';
      }
    } catch (error) {
      this.error = 'Erreur réseau';
      console.error('Submit error:', error);
    } finally {
      this.isLoading = false;
    }
  },

  async cancelTarget() {
    if (this.isLoading) return;

    if (!confirm('Voulez-vous vraiment annuler cette target ?')) {
      return;
    }

    this.isLoading = true;
    this.error = null;

    try {
      const response = await fetch(`/api/robotarget/targets/${targetGuid}/cancel`, {
        method: 'DELETE',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
      });

      const data = await response.json();

      if (data.success) {
        // Redirect to targets list
        window.location.href = '/dashboard/robotarget';
      } else {
        this.error = data.message || 'Erreur lors de l\'annulation';
      }
    } catch (error) {
      this.error = 'Erreur réseau';
      console.error('Cancel error:', error);
    } finally {
      this.isLoading = false;
    }
  },

  // Helpers
  getStatusColor(status) {
    const colors = {
      'idle': 'gray',
      'pending': 'yellow',
      'active': 'blue',
      'running': 'purple',
      'completed': 'green',
      'error': 'red',
      'aborted': 'orange',
    };
    return colors[status] || 'gray';
  },

  getStatusLabel(status) {
    const labels = {
      'idle': 'En attente',
      'pending': 'En attente',
      'active': 'Actif',
      'running': 'En cours',
      'executing': 'En exécution',
      'completed': 'Terminé',
      'error': 'Erreur',
      'aborted': 'Annulé',
    };
    return labels[status] || status;
  },

  getResultLabel(result) {
    const labels = {
      1: 'Succès',
      2: 'Annulé',
      3: 'Erreur',
    };
    return labels[result] || 'Inconnu';
  },

  formatDuration(start, end) {
    if (!start) return '-';

    const startDate = new Date(start);
    const endDate = end ? new Date(end) : new Date();
    const duration = (endDate - startDate) / 1000; // seconds

    const hours = Math.floor(duration / 3600);
    const minutes = Math.floor((duration % 3600) / 60);

    if (hours > 0) {
      return `${hours}h ${minutes}m`;
    }
    return `${minutes}m`;
  },

  formatTime(isoString) {
    if (!isoString) return '-';
    return new Date(isoString).toLocaleString('fr-FR');
  },

  // Cleanup
  destroy() {
    // Unsubscribe from all events
    this.unsubscribers.forEach(unsub => unsub());
    this.unsubscribers = [];
  },
});
