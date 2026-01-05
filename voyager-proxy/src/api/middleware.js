import logger from '../utils/logger.js';

export const authMiddleware = (req, res, next) => {
  console.log('🔐 [Auth Middleware] Request received:', {
    method: req.method,
    path: req.path,
    ip: req.ip,
    hasApiKeyHeader: !!req.headers['x-api-key'],
    hasApiKeyQuery: !!req.query.api_key,
  });

  const apiKey = process.env.API_KEY;

  if (!apiKey) {
    console.log('⚠️  [Auth Middleware] No API key configured, skipping auth');
    // No API key configured, skip auth
    return next();
  }

  const providedKey = req.headers['x-api-key'] || req.query.api_key;

  if (!providedKey) {
    console.log('❌ [Auth Middleware] No API key provided in request');
    return res.status(401).json({
      error: 'Unauthorized',
      message: 'API key is required',
    });
  }

  if (providedKey !== apiKey) {
    console.log('❌ [Auth Middleware] Invalid API key:', {
      provided: providedKey.substring(0, 20) + '...',
      expected: apiKey.substring(0, 20) + '...',
    });
    logger.warn(`Invalid API key attempt from ${req.ip}`);
    return res.status(403).json({
      error: 'Forbidden',
      message: 'Invalid API key',
    });
  }

  console.log('✅ [Auth Middleware] API key valid, passing to next middleware');
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
