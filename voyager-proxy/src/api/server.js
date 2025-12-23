import express from 'express';
import cors from 'cors';
import helmet from 'helmet';
import compression from 'compression';
import rateLimit from 'express-rate-limit';
import logger from '../utils/logger.js';
import createRouter from './routes.js';
import { authMiddleware } from './middleware.js';

class ApiServer {
  constructor(voyagerConnection) {
    this.voyagerConnection = voyagerConnection;
    this.app = express();
    this.httpServer = null;
    this.io = null; // Will be set by WebSocket server

    this.setupMiddleware();
    this.setupRoutes();
    this.setupErrorHandling();
  }

  setupMiddleware() {
    // Security
    this.app.use(helmet());

    // CORS
    const corsOrigin = process.env.CORS_ORIGIN || 'http://localhost';
    this.app.use(
      cors({
        origin: corsOrigin.split(','),
        credentials: true,
      })
    );

    // Compression
    this.app.use(compression());

    // Body parsing
    this.app.use(express.json({ limit: '10mb' }));
    this.app.use(express.urlencoded({ extended: true }));

    // Rate limiting
    const limiter = rateLimit({
      windowMs: parseInt(process.env.RATE_LIMIT_WINDOW || 15) * 60 * 1000,
      max: parseInt(process.env.RATE_LIMIT_MAX_REQUESTS || 100),
      message: 'Too many requests from this IP, please try again later.',
    });
    this.app.use('/api/', limiter);

    // Request logging
    this.app.use((req, res, next) => {
      logger.debug(`${req.method} ${req.path}`);
      next();
    });

    // Attach voyager connection to request
    this.app.use((req, res, next) => {
      req.voyager = this.voyagerConnection;
      next();
    });

    // Authentication middleware (except for health check)
    this.app.use(/^\/api\/(?!health).*/, authMiddleware);
  }

  setupRoutes() {
    // Health check
    this.app.get('/health', (req, res) => {
      const state = this.voyagerConnection.getState();
      res.json({
        status: 'ok',
        timestamp: new Date().toISOString(),
        uptime: process.uptime(),
        voyager: {
          connected: state.isConnected,
          authenticated: state.isAuthenticated,
        },
      });
    });

    // API routes - Create router with voyagerConnection and io
    const apiRoutes = createRouter(this.voyagerConnection, this.io);
    this.app.use('/api', apiRoutes);

    // 404 handler
    this.app.use((req, res) => {
      res.status(404).json({
        error: 'Not Found',
        message: `Route ${req.method} ${req.path} not found`,
      });
    });
  }

  setupErrorHandling() {
    this.app.use((err, req, res, next) => {
      logger.error('API Error:', err);

      const statusCode = err.statusCode || 500;
      const message = err.message || 'Internal Server Error';

      res.status(statusCode).json({
        error: err.name || 'Error',
        message,
        ...(process.env.NODE_ENV === 'development' && { stack: err.stack }),
      });
    });
  }

  async start() {
    const port = parseInt(process.env.PORT || 3000);
    const host = process.env.HOST || '0.0.0.0';

    return new Promise((resolve) => {
      this.httpServer = this.app.listen(port, host, () => {
        logger.info(`API server listening on ${host}:${port}`);
        resolve();
      });

      // Increase timeout for long-running operations
      this.httpServer.timeout = 120000; // 2 minutes
    });
  }

  async stop() {
    if (this.httpServer) {
      return new Promise((resolve) => {
        this.httpServer.close(() => {
          logger.info('API server stopped');
          resolve();
        });
      });
    }
  }
}

export default ApiServer;
