import { EventEmitter } from 'events';
import net from 'net';
import logger from '../utils/logger.js';
import EventHandler from './events.js';
import Authentication from './auth.js';
import Commands from './commands.js';

class VoyagerConnection extends EventEmitter {
  constructor(config) {
    super();
    this.config = config;
    this.socket = null;
    this.isConnected = false;
    this.isAuthenticated = false;
    this.reconnectAttempts = 0;
    this.heartbeatTimer = null;
    this.connectionTimer = null;
    this.lastDataReceived = null;
    this.buffer = '';

    this.eventHandler = new EventHandler(this);
    this.auth = new Authentication(this);
    this.commands = new Commands(this);

    // Cache for latest state
    this.latestState = {
      version: null,
      controlData: null,
      connection: {
        status: 'disconnected',
        connectedAt: null,
        reconnectAttempts: 0,
      },
    };
  }

  async connect() {
    return new Promise((resolve, reject) => {
      logger.info(`Connecting to Voyager at ${this.config.host}:${this.config.port}...`);

      this.socket = new net.Socket();
      this.socket.setEncoding('utf8');
      this.socket.setKeepAlive(true, 10000);
      this.socket.setTimeout(this.config.heartbeat.timeout);

      this.socket.connect(this.config.port, this.config.host, () => {
        logger.info('TCP connection established');
        this.isConnected = true;
        this.reconnectAttempts = 0;
        this.latestState.connection.status = 'connected';
        this.latestState.connection.connectedAt = new Date().toISOString();
        this.emit('connectionStateChange', this.latestState.connection);
      });

      this.socket.on('data', async (data) => {
        this.lastDataReceived = Date.now();
        this.buffer += data;

        // Process complete JSON lines (ending with \r\n)
        const lines = this.buffer.split('\r\n');
        this.buffer = lines.pop() || ''; // Keep incomplete line in buffer

        for (const line of lines) {
          if (line.trim()) {
            try {
              const message = JSON.parse(line);
              await this.handleMessage(message);

              // Resolve connection promise on Version event
              if (message.Event === 'Version' && !this.isAuthenticated) {
                logger.info(`Voyager version: ${message.VOYVersion}`);
                this.latestState.version = message;

                // Authenticate if required
                if (this.config.auth.enabled) {
                  try {
                    await this.auth.authenticate();
                    this.isAuthenticated = true;
                    this.startHeartbeat();
                    resolve(message);
                  } catch (error) {
                    logger.error('Authentication failed:', error);
                    reject(error);
                    this.disconnect();
                  }
                } else {
                  this.isAuthenticated = true;
                  this.startHeartbeat();
                  resolve(message);
                }
              }
            } catch (error) {
              logger.error('Error parsing message:', error, 'Raw:', line);
            }
          }
        }
      });

      this.socket.on('timeout', () => {
        logger.warn('Socket timeout - connection inactive');
        this.handleDisconnect('timeout');
      });

      this.socket.on('error', (error) => {
        logger.error('Socket error:', error);
        this.handleDisconnect('error');
        reject(error);
      });

      this.socket.on('close', () => {
        logger.warn('Socket closed');
        this.handleDisconnect('close');
      });

      // Connection timeout
      this.connectionTimer = setTimeout(() => {
        if (!this.isAuthenticated) {
          logger.error('Connection timeout - no Version event received');
          reject(new Error('Connection timeout'));
          this.disconnect();
        }
      }, 10000);
    });
  }

  async handleMessage(message) {
    // Forward to event handler
    await this.eventHandler.handle(message);

    // Update latest state cache
    if (message.Event === 'ControlData') {
      this.latestState.controlData = message;
    }
  }

  startHeartbeat() {
    // Send Polling event every interval
    this.heartbeatTimer = setInterval(() => {
      this.sendPolling();

      // Check if we've received data recently
      const now = Date.now();
      const timeSinceLastData = now - (this.lastDataReceived || now);

      if (timeSinceLastData > this.config.heartbeat.timeout) {
        logger.error(`No data received for ${timeSinceLastData}ms - connection lost`);
        this.handleDisconnect('heartbeat_timeout');
      }
    }, this.config.heartbeat.interval);

    logger.info(`Heartbeat started (${this.config.heartbeat.interval}ms interval)`);
  }

  stopHeartbeat() {
    if (this.heartbeatTimer) {
      clearInterval(this.heartbeatTimer);
      this.heartbeatTimer = null;
      logger.debug('Heartbeat stopped');
    }
  }

  sendPolling() {
    const polling = {
      Event: 'Polling',
      Timestamp: Date.now() / 1000,
      Host: require('os').hostname(),
      Inst: this.config.instance,
    };

    this.send(polling);
  }

  send(data) {
    if (!this.socket || !this.isConnected) {
      throw new Error('Not connected to Voyager');
    }

    const message = JSON.stringify(data) + '\r\n';
    this.socket.write(message);
    logger.debug('Sent:', data);
  }

  handleDisconnect(reason) {
    this.isConnected = false;
    this.isAuthenticated = false;
    this.stopHeartbeat();

    if (this.connectionTimer) {
      clearTimeout(this.connectionTimer);
      this.connectionTimer = null;
    }

    this.latestState.connection.status = 'disconnected';
    this.emit('connectionStateChange', this.latestState.connection);

    logger.warn(`Disconnected: ${reason}`);

    // Attempt reconnection
    if (this.reconnectAttempts < this.config.reconnect.maxAttempts) {
      this.reconnectAttempts++;
      this.latestState.connection.reconnectAttempts = this.reconnectAttempts;

      logger.info(
        `Reconnecting in ${this.config.reconnect.delay}ms (attempt ${this.reconnectAttempts}/${this.config.reconnect.maxAttempts})`
      );

      setTimeout(() => {
        this.connect().catch((error) => {
          logger.error('Reconnection failed:', error);
        });
      }, this.config.reconnect.delay);
    } else {
      logger.error('Max reconnection attempts reached');
      this.emit('maxReconnectAttemptsReached');
    }
  }

  async disconnect() {
    logger.info('Disconnecting from Voyager...');

    this.stopHeartbeat();

    if (this.socket) {
      this.socket.destroy();
      this.socket = null;
    }

    this.isConnected = false;
    this.isAuthenticated = false;
    this.latestState.connection.status = 'disconnected';

    logger.info('Disconnected');
  }

  // Proxy to commands
  async sendCommand(method, params = {}) {
    return this.commands.send(method, params);
  }

  getState() {
    return {
      ...this.latestState,
      isConnected: this.isConnected,
      isAuthenticated: this.isAuthenticated,
      reconnectAttempts: this.reconnectAttempts,
    };
  }
}

export default VoyagerConnection;
