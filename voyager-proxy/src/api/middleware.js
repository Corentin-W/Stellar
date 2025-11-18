import logger from '../utils/logger.js';

export const authMiddleware = (req, res, next) => {
  const apiKey = process.env.API_KEY;

  if (!apiKey) {
    // No API key configured, skip auth
    return next();
  }

  const providedKey = req.headers['x-api-key'] || req.query.api_key;

  if (!providedKey) {
    return res.status(401).json({
      error: 'Unauthorized',
      message: 'API key is required',
    });
  }

  if (providedKey !== apiKey) {
    logger.warn(`Invalid API key attempt from ${req.ip}`);
    return res.status(403).json({
      error: 'Forbidden',
      message: 'Invalid API key',
    });
  }

  next();
};

export const errorHandler = (err, req, res, next) => {
  logger.error('Error:', err);

  const statusCode = err.statusCode || 500;
  const message = err.message || 'Internal Server Error';

  res.status(statusCode).json({
    error: err.name || 'Error',
    message,
    ...(process.env.NODE_ENV === 'development' && { stack: err.stack }),
  });
};
