#!/usr/bin/env node

import 'dotenv/config';
import logger from './utils/logger.js';
import VoyagerConnection from './voyager/connection.js';
import ApiServer from './api/server.js';
import WebSocketServer from './websocket/server.js';
import MetricsCollector from './utils/metrics.js';
import RoboTargetEventHandler from './voyager/robotarget/events.js';

class VoyagerProxy {
  constructor() {
    this.voyagerConnection = null;
    this.apiServer = null;
    this.wsServer = null;
    this.metricsCollector = null;
    this.roboTargetEventHandler = null;
    this.isShuttingDown = false;
  }

  async start() {
    try {
      logger.info('🚀 Starting Stellar Voyager Proxy...');
      logger.info(`Environment: ${process.env.NODE_ENV}`);
      logger.info(`Port: ${process.env.PORT}`);

      // Initialize metrics collector
      if (process.env.ENABLE_METRICS === 'true') {
        this.metricsCollector = new MetricsCollector();
        this.metricsCollector.start();
        logger.info('📊 Metrics collector started');
      }

      // Initialize Voyager connection
      this.voyagerConnection = new VoyagerConnection({
        host: process.env.VOYAGER_HOST,
        port: parseInt(process.env.VOYAGER_PORT),
        instance: parseInt(process.env.VOYAGER_INSTANCE || 1),
        auth: {
          enabled: process.env.VOYAGER_AUTH_ENABLED === 'true',
          username: process.env.VOYAGER_USERNAME,
          password: process.env.VOYAGER_PASSWORD,
          // MAC authentication parameters
          authBase: process.env.VOYAGER_AUTH_BASE,
          sharedSecret: process.env.VOYAGER_SHARED_SECRET,
          macKey: process.env.VOYAGER_MAC_KEY,
          macWord1: process.env.VOYAGER_MAC_WORD1,
          macWord2: process.env.VOYAGER_MAC_WORD2,
          macWord3: process.env.VOYAGER_MAC_WORD3,
          macWord4: process.env.VOYAGER_MAC_WORD4,
          licenseNumber: process.env.VOYAGER_LICENSE_NUMBER,
        },
        heartbeat: {
          interval: parseInt(process.env.HEARTBEAT_INTERVAL || 5000),
          timeout: parseInt(process.env.CONNECTION_TIMEOUT || 15000),
        },
        reconnect: {
          delay: parseInt(process.env.RECONNECT_DELAY || 5000),
          maxAttempts: parseInt(process.env.MAX_RECONNECT_ATTEMPTS || 10),
        },
      });

      // Initialize API server
      this.apiServer = new ApiServer(this.voyagerConnection);
      await this.apiServer.start();
      logger.info(`🌐 API Server listening on port ${process.env.PORT}`);

      // Initialize WebSocket server
      this.wsServer = new WebSocketServer(
        this.apiServer.httpServer,
        this.voyagerConnection
      );
      this.wsServer.start();
      logger.info('🔌 WebSocket server started');

      // Initialize RoboTarget Event Handler
      this.roboTargetEventHandler = new RoboTargetEventHandler(
        this.voyagerConnection,
        process.env.LARAVEL_API_URL,
        process.env.VOYAGER_WEBHOOK_SECRET
      );
      this.roboTargetEventHandler.register();
      logger.info('🎯 RoboTarget event handler registered');

      // Setup event forwarding to WebSocket
      this.setupEventForwarding();

      // Connect to Voyager (non-blocking, will retry automatically)
      this.voyagerConnection.connect()
        .then(() => {
          logger.info('🔭 Connected to Voyager Application Server');
        })
        .catch((error) => {
          logger.warn('⚠️ Could not connect to Voyager (will retry automatically)');
          logger.warn(`Voyager connection error: ${error.message}`);
          logger.info('💡 Proxy is running in disconnected mode - API and WebSocket are functional');
        });

      logger.info('✅ Stellar Voyager Proxy is ready!');
      logger.info(`📡 Voyager: ${process.env.VOYAGER_HOST}:${process.env.VOYAGER_PORT} (connecting...)`);
      logger.info(`🌍 API: http://${process.env.HOST}:${process.env.PORT}`);

      // Setup graceful shutdown
      this.setupGracefulShutdown();
    } catch (error) {
      logger.error('Failed to start proxy:', error);
      process.exit(1);
    }
  }

  setupEventForwarding() {
    // Forward Voyager events to WebSocket clients
    this.voyagerConnection.on('controlData', (data) => {
      this.wsServer.broadcast('controlData', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('controlData');
      }
    });

    this.voyagerConnection.on('newJPG', (data) => {
      this.wsServer.broadcast('newJPG', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('newJPG');
      }
    });

    this.voyagerConnection.on('shotRunning', (data) => {
      this.wsServer.broadcast('shotRunning', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('shotRunning');
      }
    });

    this.voyagerConnection.on('signal', (data) => {
      this.wsServer.broadcast('signal', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('signal');
      }
    });

    this.voyagerConnection.on('newFITReady', (data) => {
      this.wsServer.broadcast('newFITReady', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('newFITReady');
      }
    });

    this.voyagerConnection.on('remoteActionResult', (data) => {
      this.wsServer.broadcast('remoteActionResult', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('remoteActionResult');
      }
    });

    this.voyagerConnection.on('connectionStateChange', (state) => {
      this.wsServer.broadcast('connectionState', state);
      logger.info(`Connection state: ${state.status}`);
    });

    // RoboTarget Events
    this.voyagerConnection.on('roboTargetSessionStart', (data) => {
      this.wsServer.broadcast('roboTargetSessionStart', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('roboTargetSessionStart');
      }
    });

    this.voyagerConnection.on('roboTargetSessionComplete', (data) => {
      this.wsServer.broadcast('roboTargetSessionComplete', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('roboTargetSessionComplete');
      }
    });

    this.voyagerConnection.on('roboTargetSessionAbort', (data) => {
      this.wsServer.broadcast('roboTargetSessionAbort', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('roboTargetSessionAbort');
      }
    });

    this.voyagerConnection.on('roboTargetProgress', (data) => {
      this.wsServer.broadcast('roboTargetProgress', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('roboTargetProgress');
      }
    });

    this.voyagerConnection.on('roboTargetShotComplete', (data) => {
      this.wsServer.broadcast('roboTargetShotComplete', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('roboTargetShotComplete');
      }
    });

    this.voyagerConnection.on('roboTargetError', (data) => {
      this.wsServer.broadcast('roboTargetError', data);
      if (this.metricsCollector) {
        this.metricsCollector.recordEvent('roboTargetError');
      }
    });
  }

  setupGracefulShutdown() {
    const shutdown = async (signal) => {
      if (this.isShuttingDown) {
        logger.warn('Shutdown already in progress...');
        return;
      }

      this.isShuttingDown = true;
      logger.info(`\n${signal} received, shutting down gracefully...`);

      try {
        // Stop accepting new connections
        if (this.apiServer) {
          await this.apiServer.stop();
          logger.info('API server stopped');
        }

        // Close WebSocket connections
        if (this.wsServer) {
          this.wsServer.stop();
          logger.info('WebSocket server stopped');
        }

        // Disconnect from Voyager
        if (this.voyagerConnection) {
          await this.voyagerConnection.disconnect();
          logger.info('Disconnected from Voyager');
        }

        // Stop metrics
        if (this.metricsCollector) {
          this.metricsCollector.stop();
          logger.info('Metrics collector stopped');
        }

        logger.info('✅ Graceful shutdown completed');
        process.exit(0);
      } catch (error) {
        logger.error('Error during shutdown:', error);
        process.exit(1);
      }
    };

    // Handle different termination signals
    process.on('SIGTERM', () => shutdown('SIGTERM'));
    process.on('SIGINT', () => shutdown('SIGINT'));
    process.on('SIGUSR2', () => shutdown('SIGUSR2')); // nodemon restart

    // Handle uncaught errors
    process.on('uncaughtException', (error) => {
      logger.error('Uncaught Exception:', error);
      shutdown('uncaughtException');
    });

    process.on('unhandledRejection', (reason, promise) => {
      logger.error('Unhandled Rejection at:', promise, 'reason:', reason);
      shutdown('unhandledRejection');
    });
  }
}

// Start the proxy
const proxy = new VoyagerProxy();
proxy.start();


