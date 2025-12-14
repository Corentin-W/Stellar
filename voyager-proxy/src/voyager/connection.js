import { EventEmitter } from 'events';
import net from 'net';
import os from 'os';
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

    // RoboTarget Manager Mode state
    this.isRoboTargetManagerMode = false;
    this.sessionKey = null;
  }

  async connect() {
    return new Promise((resolve, reject) => {
      logger.info(`Connecting to Voyager at ${this.config.host}:${this.config.port}...`);

      this.socket = new net.Socket();
      this.socket.setEncoding('utf8');
      this.socket.setKeepAlive(true, 10000);
      this.socket.setTimeout(this.config.heartbeat.timeout);

      this.socket.connect(this.config.port, this.config.host, async () => {
        logger.info('TCP connection established');
        this.isConnected = true;
        this.reconnectAttempts = 0;
        this.latestState.connection.status = 'connected';
        this.latestState.connection.connectedAt = new Date().toISOString();
        this.emit('connectionStateChange', this.latestState.connection);

        // CONFORME À LA DOC: NE PAS authentifier avant l'événement Version
        // L'authentification se fait dans handleMessage() après réception de Version
        logger.info('⏳ Waiting for Version event (SessionKey capture)...');
      });

      this.socket.on('data', async (data) => {
        this.lastDataReceived = Date.now();
        logger.debug(`📥 Raw data received: ${data.toString().substring(0, 200)}...`);
        this.buffer += data;

        // Process complete JSON lines (ending with \r\n)
        const lines = this.buffer.split('\r\n');
        this.buffer = lines.pop() || ''; // Keep incomplete line in buffer

        for (const line of lines) {
          if (line.trim()) {
            logger.debug(`📨 Processing line: ${line.substring(0, 100)}...`);
            try {
              const message = JSON.parse(line);
              logger.info(`✅ Parsed message - Event: ${message.Event || message.method || 'unknown'}`);
              await this.handleMessage(message);

              // Resolve connection promise on Version event
              if (message.Event === 'Version' && !this.isAuthenticated) {
                logger.info(`✅ Version event received`);
                logger.info(`   Voyager version: ${message.VOYVersion}`);
                logger.info(`   SessionKey (Timestamp): ${message.Timestamp}`);
                this.latestState.version = message;
                this.sessionKey = message.Timestamp; // Store SessionKey for RoboTarget

                // ÉTAPE 2: Authenticate if required (< 5 seconds)
                if (this.config.auth.enabled) {
                  try {
                    logger.info('🔐 STEP 2: Authenticating (< 5 seconds window)...');
                    await this.auth.authenticate();
                    this.isAuthenticated = true;
                    logger.info('✅ Authentication successful');
                  } catch (error) {
                    logger.error('❌ Authentication failed:', error);
                    reject(error);
                    this.disconnect();
                    return;
                  }
                } else {
                  this.isAuthenticated = true;
                  logger.info('⚠️ No authentication required (test mode)');
                }

                // ÉTAPE 3: Activate Dashboard Mode (required for JPG/ControlData)
                try {
                  logger.info('📊 STEP 3: Activating Dashboard Mode...');
                  await this.commands.send('RemoteSetDashboardMode', {
                    UID: require('uuid').v4(),
                    On: true,
                    Period: 2000, // 2 seconds
                  });
                  logger.info('✅ Dashboard Mode activated (JPG/ControlData stream enabled)');
                } catch (error) {
                  logger.warn('⚠️ Dashboard Mode activation failed:', error.message);
                  // Continue anyway
                }

                // ÉTAPE 4: Activate RoboTarget Manager Mode if MAC Key is configured
                if (this.config.auth.macKey) {
                  try {
                    logger.info('🤖 STEP 4: Activating RoboTarget Manager Mode...');
                    await this.auth.activateRoboTargetManagerMode(this.sessionKey);
                    this.isRoboTargetManagerMode = true;
                    logger.info('✅ RoboTarget Manager Mode ACTIVE - All RoboTarget commands available');
                  } catch (error) {
                    logger.error('❌ Failed to activate RoboTarget Manager Mode:', error);
                    logger.warn('⚠️ RoboTarget commands will NOT work without Manager Mode');
                    // Don't reject - continue with basic connection
                  }
                }

                // ÉTAPE 5: Start Heartbeat (Polling every 5s to prevent 15s timeout)
                logger.info('💓 STEP 5: Starting Heartbeat...');
                this.startHeartbeat();

                logger.info('✅ Connection fully established!');
                resolve(message);
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
      Host: os.hostname(),
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

    // Attempt reconnection with exponential backoff
    if (this.reconnectAttempts < this.config.reconnect.maxAttempts) {
      this.reconnectAttempts++;
      this.latestState.connection.reconnectAttempts = this.reconnectAttempts;

      // Exponential backoff: 5s, 10s, 20s, 40s, ..., max 5min (300s)
      // Conforme à la doc: docs/doc_voyager/connexion_et_maintien.md
      const baseDelay = this.config.reconnect.delay || 5000; // 5 seconds base
      const exponentialDelay = Math.min(
        baseDelay * Math.pow(2, this.reconnectAttempts - 1),
        300000 // Max 5 minutes
      );

      logger.info(
        `Reconnecting in ${exponentialDelay}ms (attempt ${this.reconnectAttempts}/${this.config.reconnect.maxAttempts})`
      );

      setTimeout(() => {
        this.connect().catch((error) => {
          logger.error('Reconnection failed:', error);
        });
      }, exponentialDelay);
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
