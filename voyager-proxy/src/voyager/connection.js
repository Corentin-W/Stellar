import { EventEmitter } from 'events';
import net from 'net';
import os from 'os';
import { v4 as uuidv4 } from 'uuid';
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
        logger.info('⏳ Waiting for Version or Polling event (SessionKey capture)...');
      });

      this.socket.on('data', async (data) => {
        try {
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
                // CRITICAL: Extract raw Timestamp string BEFORE JSON.parse for MAC calculation
                // JSON.parse converts to float, but we need the exact string representation
                // The regex now supports:
                // - Regular numbers: 123.456
                // - Scientific notation: 1.23e10 or 1.23E-5
                let rawTimestamp = null;
                const timestampMatch = line.match(/"Timestamp":\s*([0-9.eE+-]+)/);
                if (timestampMatch) {
                  rawTimestamp = timestampMatch[1]; // Extract as string, preserving exact representation
                  logger.debug(`🔍 Raw Timestamp extracted: "${rawTimestamp}"`);
                }

                const message = JSON.parse(line);
                const eventType = message.Event || message.method || 'unknown';
                logger.info(`✅ Parsed message - Event: ${eventType}`);

                // Log unknown messages and JSON-RPC errors in detail
                if (eventType === 'unknown') {
                  logger.error(`⚠️  ========== UNKNOWN MESSAGE ==========`);
                  logger.error(JSON.stringify(message, null, 2));
                  logger.error(`⚠️  =====================================`);
                }

                // Log JSON-RPC errors specifically
                if (message.error) {
                  logger.error(`❌ ========== JSON-RPC ERROR ==========`);
                  logger.error(`   ID: ${message.id}`);
                  logger.error(`   Code: ${message.error.code}`);
                  logger.error(`   Message: ${message.error.message}`);
                  if (message.error.data) {
                    logger.error(`   Data: ${JSON.stringify(message.error.data)}`);
                  }
                  logger.error(`❌ ======================================`);
                }

                await this.handleMessage(message);

                // FALLBACK: If we receive Polling event before Version, use its Timestamp as SessionKey
                if (message.Event === 'Polling' && !this.sessionKey && !this.isAuthenticated) {
                  logger.warn('⚠️ Received Polling before Version event - using Polling Timestamp as SessionKey');
                  if (!rawTimestamp) {
                    logger.error('❌ CRITICAL: Could not extract raw Timestamp from Polling event! MAC will fail!');
                    logger.error(`   Full line: ${line}`);
                  }
                  const timestampValue = rawTimestamp || (message.Timestamp ? String(message.Timestamp) : null);
                  logger.info(`   SessionKey (from Polling): ${timestampValue}`);
                  this.sessionKey = timestampValue;
                  // Trigger the same authentication flow as Version event
                  message.Event = 'Version';
                  message.VOYVersion = 'Unknown (from Polling)';
                }

                // Resolve connection promise on Version event
                if (message.Event === 'Version' && !this.isAuthenticated) {
                  logger.info(`✅ Version event received`);
                  logger.info(`   Voyager version: ${message.VOYVersion}`);
                  logger.info(`   SessionKey (raw string): ${rawTimestamp}`);
                  logger.info(`   SessionKey (parsed float): ${message.Timestamp}`);
                  this.latestState.version = message;

                  // CRITICAL: Store RAW SessionKey string for RoboTarget MAC calculation
                  // The SessionKey MUST be the exact string representation from the JSON
                  // If rawTimestamp is null (extraction failed), log ERROR and use fallback
                  if (!rawTimestamp) {
                    logger.error('❌ CRITICAL: Could not extract raw Timestamp from Version event!');
                    logger.error('   This will cause MAC errors for RoboTarget commands!');
                    logger.error(`   Full line: ${line}`);
                    logger.warn('   Using fallback: message.Timestamp.toString() - MAC may fail!');
                  }
                  this.sessionKey = rawTimestamp || (message.Timestamp ? String(message.Timestamp) : null);

                  // Verify SessionKey is valid
                  if (!this.sessionKey) {
                    logger.error('❌ FATAL: SessionKey is null! RoboTarget commands will NOT work!');
                  } else {
                    logger.info(`✅ SessionKey stored: "${this.sessionKey}" (length: ${this.sessionKey.length})`);
                  }

                  // ÉTAPE 2: Authenticate if required (< 5 seconds)
                  if (this.config.auth.enabled) {
                    try {
                      logger.info('🔐 STEP 2: Authenticating (< 5 seconds window)...');
                      await this.auth.authenticate();
                      this.isAuthenticated = true;
                      logger.info('✅ Authentication successful');
                    } catch (error) {
                      // Check if error is "Authentication Level not Allow this request"
                      // This means Voyager doesn't have authentication enabled
                      if (error.message && error.message.includes('Authentication Level not Allow')) {
                        logger.warn('⚠️ Voyager authentication not enabled - continuing without auth');
                        logger.info('💡 To use authentication: enable it in Voyager settings and configure username/password');
                        this.isAuthenticated = true; // Consider authenticated (no auth required)
                      } else {
                        // Real authentication error - abort
                        logger.error('❌ Authentication failed:', error);
                        reject(error);
                        this.disconnect();
                        return;
                      }
                    }
                  } else {
                    this.isAuthenticated = true;
                    logger.info('⚠️ No authentication required - proceeding to RoboTarget Manager Mode');
                  }

                  // ÉTAPE 3: Activate Dashboard Mode (required for JPG/ControlData)
                  try {
                    logger.info('📊 STEP 3: Activating Dashboard Mode...');
                    await this.commands.setDashboardMode(true);
                    logger.info('✅ Dashboard Mode activated (JPG/ControlData stream enabled)');
                  } catch (error) {
                    logger.warn('⚠️ Dashboard Mode activation failed:', error.message);
                    // Continue anyway
                  }

                  // ÉTAPE 4: Activate RoboTarget Manager Mode if SharedSecret and MAC Key are configured
                  if (this.config.auth.sharedSecret && this.config.auth.macKey) {
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
                  } else if (!this.config.auth.sharedSecret || !this.config.auth.macKey) {
                    logger.warn('⚠️ RoboTarget Manager Mode NOT enabled - SharedSecret or MAC Key missing');
                    logger.info('💡 Configure VOYAGER_SHARED_SECRET and VOYAGER_MAC_KEY to enable RoboTarget features');
                  }

                  // ÉTAPE 5: Start Heartbeat (Polling every 5s to prevent 15s timeout)
                  logger.info('💓 STEP 5: Starting Heartbeat...');
                  this.startHeartbeat();

                  logger.info('✅ Connection fully established!');
                  resolve(message);
                }
              } catch (error) {
                logger.error('Error parsing message:', error, 'Raw:', line);
                // Don't crash on parse errors, just skip the message
              }
            }
          }
        } catch (error) {
          logger.error('Error processing data buffer:', error);
          // Reset buffer on critical error to prevent infinite loop
          this.buffer = '';
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
    try {
      // Forward to event handler
      await this.eventHandler.handle(message);

      // Update latest state cache
      if (message.Event === 'ControlData') {
        this.latestState.controlData = message;
      }
    } catch (error) {
      logger.error('Error handling message:', error);
      logger.debug('Problematic message:', JSON.stringify(message).substring(0, 200));
      // Don't crash, just log and continue
    }
  }

  startHeartbeat() {
    // CRITICAL: Send Polling events every 5 seconds to prevent Voyager timeout (15s)
    // Voyager REQUIRES receiving Polling data from the client to maintain connection
    this.heartbeatTimer = setInterval(() => {
      try {
        // Send Polling event
        this.sendPolling();

        // Check if we've received data recently
        const now = Date.now();
        const timeSinceLastData = now - (this.lastDataReceived || now);

        if (timeSinceLastData > this.config.heartbeat.timeout) {
          logger.error(`No data received for ${timeSinceLastData}ms - connection lost`);
          this.handleDisconnect('heartbeat_timeout');
        }
      } catch (error) {
        logger.error('Error in heartbeat:', error);
        // Don't stop heartbeat on error - continue trying
      }
    }, this.config.heartbeat.interval);

    logger.info(`Heartbeat started (${this.config.heartbeat.interval}ms interval)`);
    logger.info('💓 Sending Polling events every 5s to maintain connection');
  }

  stopHeartbeat() {
    if (this.heartbeatTimer) {
      clearInterval(this.heartbeatTimer);
      this.heartbeatTimer = null;
      logger.debug('Heartbeat stopped');
    }
  }

  sendPolling() {
    // CRITICAL: Client MUST send Polling events every 5 seconds to maintain connection
    // Voyager will disconnect after 15s without receiving Polling data
    const polling = {
      Event: 'Polling',
      Timestamp: Date.now() / 1000, // Float timestamp in seconds (NOT Math.floor!)
      Host: os.hostname(),
      Inst: this.config.instance,
    };

    try {
      this.send(polling);
      logger.debug('💓 Heartbeat sent (Polling)');
    } catch (error) {
      logger.error('Failed to send Polling:', error);
    }
  }

  send(data) {
    if (!this.socket || !this.isConnected) {
      throw new Error('Not connected to Voyager');
    }

    const message = JSON.stringify(data) + '\r\n';
    this.socket.write(message);

    // Only log RoboTarget commands at info level
    if (data.method && data.method.includes('RoboTarget')) {
      logger.info(`📤 Sent ${data.method}:\n${JSON.stringify(data, null, 2)}`);
    } else {
      logger.debug('Sent:', data);
    }
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
