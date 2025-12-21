import winston from 'winston';
import DailyRotateFile from 'winston-daily-rotate-file';
import path from 'path';
import { fileURLToPath } from 'url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));

const logLevel = process.env.LOG_LEVEL || 'info';
const logDir = process.env.LOG_DIR || path.join(__dirname, '../../logs');

// Custom format
const customFormat = winston.format.combine(
  winston.format.timestamp({ format: 'YYYY-MM-DD HH:mm:ss' }),
  winston.format.errors({ stack: true }),
  winston.format.printf(({ timestamp, level, message, stack, ...meta }) => {
    let log = `${timestamp} [${level.toUpperCase()}]: ${message}`;

    // Add metadata if present
    const metaKeys = Object.keys(meta);
    if (metaKeys.length > 0) {
      log += ` ${JSON.stringify(meta)}`;
    }

    // Add stack trace for errors
    if (stack) {
      log += `\n${stack}`;
    }

    return log;
  })
);

// Filter for console: only show RoboTarget related messages, errors, and warnings
const roboTargetFilter = winston.format((info) => {
  // Always show errors and warnings
  if (info.level === 'error' || info.level === 'warn') {
    return info;
  }

  // Show RoboTarget related messages
  const msg = info.message || '';
  if (msg.includes('RoboTarget') ||
      msg.includes('MAC') ||
      msg.includes('RemoteRoboTarget') ||
      msg.includes('🎯') ||
      msg.includes('🔍') ||
      msg.includes('✅') ||
      msg.includes('🔐') ||
      msg.includes('📤') ||
      msg.includes('❌') ||
      msg.includes('Command sent: RemoteRoboTarget') ||
      msg.includes('Command timeout: RemoteRoboTarget') ||
      msg.includes('Sent RemoteRoboTarget') ||
      msg.includes('POST /api/robotarget')) {
    return info;
  }

  // Show connection state changes
  if (msg.includes('Connection fully established') ||
      msg.includes('RoboTarget Manager Mode') ||
      msg.includes('SessionKey stored') ||
      msg.includes('Starting Stellar Voyager Proxy') ||
      msg.includes('Stellar Voyager Proxy is ready')) {
    return info;
  }

  // Filter out everything else
  return false;
})();

// Console format (colorized)
const consoleFormat = winston.format.combine(
  winston.format.colorize(),
  winston.format.timestamp({ format: 'HH:mm:ss' }),
  roboTargetFilter,
  winston.format.printf(({ timestamp, level, message, ...meta }) => {
    let log = `${timestamp} ${level}: ${message}`;

    const metaKeys = Object.keys(meta);
    if (metaKeys.length > 0 && meta.stack === undefined) {
      log += ` ${JSON.stringify(meta, null, 2)}`;
    }

    return log;
  })
);

// Transports
const transports = [
  // Console output
  new winston.transports.Console({
    format: consoleFormat,
    level: logLevel,
  }),

  // File output - all logs
  new DailyRotateFile({
    filename: path.join(logDir, 'application-%DATE%.log'),
    datePattern: 'YYYY-MM-DD',
    format: customFormat,
    maxSize: process.env.LOG_MAX_SIZE || '20m',
    maxFiles: process.env.LOG_MAX_FILES || '14d',
    level: logLevel,
  }),

  // File output - errors only
  new DailyRotateFile({
    filename: path.join(logDir, 'error-%DATE%.log'),
    datePattern: 'YYYY-MM-DD',
    format: customFormat,
    maxSize: process.env.LOG_MAX_SIZE || '20m',
    maxFiles: process.env.LOG_MAX_FILES || '30d',
    level: 'error',
  }),
];

// Create logger instance
const logger = winston.createLogger({
  level: logLevel,
  format: customFormat,
  transports,
  exitOnError: false,
});

// Create a stream for Morgan or other loggers
logger.stream = {
  write: (message) => {
    logger.info(message.trim());
  },
};

export default logger;
