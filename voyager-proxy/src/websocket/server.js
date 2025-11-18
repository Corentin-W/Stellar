import { Server } from 'socket.io';
import logger from '../utils/logger.js';

class WebSocketServer {
  constructor(httpServer, voyagerConnection) {
    this.httpServer = httpServer;
    this.voyagerConnection = voyagerConnection;
    this.io = null;
    this.clients = new Map();
  }

  start() {
    const corsOrigin = process.env.WS_CORS_ORIGIN || process.env.CORS_ORIGIN || 'http://localhost';

    this.io = new Server(this.httpServer, {
      cors: {
        origin: corsOrigin.split(','),
        credentials: true,
      },
      pingInterval: parseInt(process.env.WS_PING_INTERVAL || 25000),
      pingTimeout: parseInt(process.env.WS_PING_TIMEOUT || 60000),
      transports: ['websocket', 'polling'],
    });

    this.setupEventHandlers();

    logger.info('WebSocket server initialized');
  }

  setupEventHandlers() {
    this.io.on('connection', (socket) => {
      const clientId = socket.id;
      const clientIp = socket.handshake.address;

      logger.info(`WebSocket client connected: ${clientId} from ${clientIp}`);

      this.clients.set(clientId, {
        socket,
        connectedAt: new Date(),
        ip: clientIp,
      });

      // Send current state immediately
      const state = this.voyagerConnection.getState();
      socket.emit('initialState', {
        connection: state.connection,
        version: state.version,
        controlData: state.controlData,
      });

      // Client events
      socket.on('subscribe', (room) => {
        logger.debug(`Client ${clientId} subscribing to room: ${room}`);
        socket.join(room);
      });

      socket.on('unsubscribe', (room) => {
        logger.debug(`Client ${clientId} unsubscribing from room: ${room}`);
        socket.leave(room);
      });

      socket.on('ping', () => {
        socket.emit('pong', { timestamp: Date.now() });
      });

      socket.on('getState', () => {
        const currentState = this.voyagerConnection.getState();
        socket.emit('state', currentState);
      });

      // Client commands (proxied to Voyager)
      socket.on('command', async (data) => {
        try {
          logger.info(`Command from ${clientId}: ${data.method}`);

          const result = await this.voyagerConnection.sendCommand(
            data.method,
            data.params || {}
          );

          socket.emit('commandResult', {
            id: data.id,
            success: true,
            result,
          });
        } catch (error) {
          logger.error(`Command error for ${clientId}:`, error);

          socket.emit('commandResult', {
            id: data.id,
            success: false,
            error: error.message,
          });
        }
      });

      socket.on('disconnect', (reason) => {
        logger.info(`WebSocket client disconnected: ${clientId} (${reason})`);
        this.clients.delete(clientId);
      });

      socket.on('error', (error) => {
        logger.error(`WebSocket error for ${clientId}:`, error);
      });
    });

    logger.info('WebSocket event handlers configured');
  }

  broadcast(event, data, room = null) {
    if (room) {
      this.io.to(room).emit(event, data);
      logger.debug(`Broadcast ${event} to room ${room}`);
    } else {
      this.io.emit(event, data);
      logger.debug(`Broadcast ${event} to all clients`);
    }
  }

  sendToClient(clientId, event, data) {
    const client = this.clients.get(clientId);
    if (client) {
      client.socket.emit(event, data);
      logger.debug(`Sent ${event} to client ${clientId}`);
    }
  }

  getConnectedClients() {
    return Array.from(this.clients.values()).map((client) => ({
      id: client.socket.id,
      ip: client.ip,
      connectedAt: client.connectedAt,
      rooms: Array.from(client.socket.rooms),
    }));
  }

  stop() {
    if (this.io) {
      // Notify all clients
      this.broadcast('serverShutdown', {
        message: 'Server is shutting down',
        timestamp: new Date().toISOString(),
      });

      // Close all connections
      this.io.close(() => {
        logger.info('WebSocket server closed');
      });

      this.clients.clear();
    }
  }
}

export default WebSocketServer;
