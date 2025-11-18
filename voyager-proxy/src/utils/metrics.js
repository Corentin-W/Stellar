import logger from './logger.js';

class MetricsCollector {
  constructor() {
    this.metrics = {
      startTime: Date.now(),
      events: {},
      commands: {},
      websocket: {
        totalConnections: 0,
        currentConnections: 0,
        messagesReceived: 0,
        messagesSent: 0,
      },
      api: {
        totalRequests: 0,
        errors: 0,
      },
      voyager: {
        reconnections: 0,
        lastConnectedAt: null,
        totalDowntime: 0,
      },
    };

    this.interval = null;
  }

  start() {
    const intervalMs = parseInt(process.env.METRICS_INTERVAL || 60000);

    this.interval = setInterval(() => {
      this.logMetrics();
    }, intervalMs);

    logger.info('Metrics collector started');
  }

  stop() {
    if (this.interval) {
      clearInterval(this.interval);
      this.interval = null;
      logger.info('Metrics collector stopped');
    }
  }

  recordEvent(eventType) {
    if (!this.metrics.events[eventType]) {
      this.metrics.events[eventType] = 0;
    }
    this.metrics.events[eventType]++;
  }

  recordCommand(commandType, success = true) {
    if (!this.metrics.commands[commandType]) {
      this.metrics.commands[commandType] = { success: 0, failed: 0 };
    }

    if (success) {
      this.metrics.commands[commandType].success++;
    } else {
      this.metrics.commands[commandType].failed++;
    }
  }

  recordWebSocketConnection() {
    this.metrics.websocket.totalConnections++;
    this.metrics.websocket.currentConnections++;
  }

  recordWebSocketDisconnection() {
    this.metrics.websocket.currentConnections--;
  }

  recordWebSocketMessage(direction = 'received') {
    if (direction === 'received') {
      this.metrics.websocket.messagesReceived++;
    } else {
      this.metrics.websocket.messagesSent++;
    }
  }

  recordApiRequest() {
    this.metrics.api.totalRequests++;
  }

  recordApiError() {
    this.metrics.api.errors++;
  }

  recordVoyagerReconnection() {
    this.metrics.voyager.reconnections++;
  }

  recordVoyagerConnected() {
    this.metrics.voyager.lastConnectedAt = new Date().toISOString();
  }

  getMetrics() {
    const uptime = Date.now() - this.metrics.startTime;

    return {
      uptime: {
        seconds: Math.floor(uptime / 1000),
        formatted: this.formatUptime(uptime),
      },
      ...this.metrics,
    };
  }

  logMetrics() {
    const metrics = this.getMetrics();

    logger.info('=== METRICS REPORT ===');
    logger.info(`Uptime: ${metrics.uptime.formatted}`);
    logger.info(`WebSocket: ${metrics.websocket.currentConnections} active, ${metrics.websocket.totalConnections} total`);
    logger.info(`API: ${metrics.api.totalRequests} requests, ${metrics.api.errors} errors`);
    logger.info(`Voyager: ${metrics.voyager.reconnections} reconnections`);
    logger.info(`Events: ${JSON.stringify(metrics.events)}`);
    logger.info('======================');
  }

  formatUptime(ms) {
    const seconds = Math.floor(ms / 1000);
    const minutes = Math.floor(seconds / 60);
    const hours = Math.floor(minutes / 60);
    const days = Math.floor(hours / 24);

    if (days > 0) {
      return `${days}d ${hours % 24}h ${minutes % 60}m`;
    } else if (hours > 0) {
      return `${hours}h ${minutes % 60}m ${seconds % 60}s`;
    } else if (minutes > 0) {
      return `${minutes}m ${seconds % 60}s`;
    } else {
      return `${seconds}s`;
    }
  }

  reset() {
    this.metrics = {
      startTime: Date.now(),
      events: {},
      commands: {},
      websocket: {
        totalConnections: 0,
        currentConnections: 0,
        messagesReceived: 0,
        messagesSent: 0,
      },
      api: {
        totalRequests: 0,
        errors: 0,
      },
      voyager: {
        reconnections: 0,
        lastConnectedAt: null,
        totalDowntime: 0,
      },
    };
    logger.info('Metrics reset');
  }
}

export default MetricsCollector;
