import logger from '../../utils/logger.js';
import axios from 'axios';

/**
 * RoboTarget Event Handlers
 *
 * Handles RoboTarget-specific events from Voyager and notifies Laravel backend
 */
class RoboTargetEventHandler {
  constructor(connection, laravelApiUrl, webhookSecret) {
    this.connection = connection;
    this.laravelApiUrl = laravelApiUrl;
    this.webhookSecret = webhookSecret;

    // State tracking
    this.currentTargetState = {
      guidTarget: null,
      guidSession: null,
      startTime: null,
      shotCount: 0,
      status: 'idle', // idle, running, completed, error
    };
  }

  /**
   * Register all RoboTarget event handlers
   */
  register() {
    // Session events
    this.connection.on('roboTargetSessionStart', (data) => this.handleSessionStart(data));
    this.connection.on('roboTargetSessionComplete', (data) => this.handleSessionComplete(data));
    this.connection.on('roboTargetSessionAbort', (data) => this.handleSessionAbort(data));

    // Progress events
    this.connection.on('roboTargetProgress', (data) => this.handleProgress(data));

    // Shot events
    this.connection.on('roboTargetShotComplete', (data) => this.handleShotComplete(data));

    // Error events
    this.connection.on('roboTargetError', (data) => this.handleError(data));

    logger.info('RoboTarget event handlers registered');
  }

  /**
   * Handle RoboTarget Session Start
   * Event: When Voyager starts executing a RoboTarget
   */
  async handleSessionStart(message) {
    logger.info(`RoboTarget Session Started: ${message.GuidTarget}`);

    this.currentTargetState = {
      guidTarget: message.GuidTarget,
      guidSession: message.GuidSession || null,
      guidSet: message.GuidSet || null,
      startTime: new Date(),
      shotCount: 0,
      status: 'running',
    };

    const enriched = {
      ...message,
      parsed: {
        guidTarget: message.GuidTarget,
        guidSession: message.GuidSession,
        guidSet: message.GuidSet,
        targetName: message.TargetName,
        startTime: this.currentTargetState.startTime.toISOString(),
      },
    };

    // Emit to WebSocket clients
    this.connection.emit('roboTargetSessionStart', enriched);

    // Notify Laravel (optional - for logging/tracking)
    try {
      await this.notifyLaravel('session-start', enriched.parsed);
    } catch (error) {
      logger.error('Error notifying Laravel of session start:', error);
    }
  }

  /**
   * Handle RoboTarget Session Complete
   * Event: When Voyager finishes executing a RoboTarget
   */
  async handleSessionComplete(message) {
    logger.info(`RoboTarget Session Completed: ${message.GuidTarget}`);

    const sessionDuration = this.currentTargetState.startTime
      ? (new Date() - this.currentTargetState.startTime) / 1000
      : null;

    const enriched = {
      ...message,
      parsed: {
        guidTarget: message.GuidTarget,
        guidSession: message.GuidSession,
        guidSet: message.GuidSet,
        targetName: message.TargetName,
        result: message.Result, // 1 = OK, 2 = Aborted, 3 = Error
        resultText: this.getResultText(message.Result),
        sessionStart: this.currentTargetState.startTime?.toISOString(),
        sessionEnd: new Date().toISOString(),
        duration: sessionDuration,
        shotsCaptured: this.currentTargetState.shotCount,
        hfdMean: message.HFDMean || null,
        reason: message.Reason || null,
      },
    };

    // Emit to WebSocket clients
    this.connection.emit('roboTargetSessionComplete', enriched);

    // Notify Laravel (CRITICAL - for credit handling)
    try {
      await this.notifyLaravel('session-complete', enriched.parsed);
      logger.info(`Laravel notified of session completion for ${message.GuidTarget}`);
    } catch (error) {
      logger.error('CRITICAL: Failed to notify Laravel of session completion:', error);
      // This is critical because Laravel needs this to handle credits!
      // TODO: Implement retry mechanism
    }

    // Reset state
    this.currentTargetState.status = 'completed';
  }

  /**
   * Handle RoboTarget Session Abort
   * Event: When a RoboTarget session is aborted
   */
  async handleSessionAbort(message) {
    logger.warn(`RoboTarget Session Aborted: ${message.GuidTarget}`);

    const enriched = {
      ...message,
      parsed: {
        guidTarget: message.GuidTarget,
        guidSession: message.GuidSession,
        reason: message.Reason || 'Aborted by user',
        sessionStart: this.currentTargetState.startTime?.toISOString(),
        sessionEnd: new Date().toISOString(),
        shotsCaptured: this.currentTargetState.shotCount,
      },
    };

    // Emit to WebSocket clients
    this.connection.emit('roboTargetSessionAbort', enriched);

    // Notify Laravel
    try {
      await this.notifyLaravel('session-abort', enriched.parsed);
    } catch (error) {
      logger.error('Error notifying Laravel of session abort:', error);
    }

    // Reset state
    this.currentTargetState.status = 'aborted';
  }

  /**
   * Handle RoboTarget Progress
   * Event: Real-time progress updates during target execution
   */
  handleProgress(message) {
    logger.debug(`RoboTarget Progress: ${message.Progress}%`);

    const enriched = {
      ...message,
      parsed: {
        guidTarget: message.GuidTarget,
        guidSession: message.GuidSession,
        progress: message.Progress, // Percentage 0-100
        currentShot: message.CurrentShot,
        totalShots: message.TotalShots,
        currentFilter: message.CurrentFilter,
        currentExposure: message.CurrentExposure,
        hfd: message.HFD,
        elapsed: this.currentTargetState.startTime
          ? (new Date() - this.currentTargetState.startTime) / 1000
          : null,
      },
    };

    // Emit to WebSocket clients (high frequency, no Laravel notification)
    this.connection.emit('roboTargetProgress', enriched);
  }

  /**
   * Handle RoboTarget Shot Complete
   * Event: When a single shot/exposure is completed
   */
  handleShotComplete(message) {
    logger.debug(`RoboTarget Shot Complete: ${message.Filename}`);

    this.currentTargetState.shotCount++;

    const enriched = {
      ...message,
      parsed: {
        guidTarget: message.GuidTarget,
        guidSession: message.GuidSession,
        filename: message.Filename,
        filter: message.Filter,
        exposure: message.Exposure,
        hfd: message.HFD,
        starIndex: message.StarIndex,
        shotNumber: this.currentTargetState.shotCount,
      },
    };

    // Emit to WebSocket clients
    this.connection.emit('roboTargetShotComplete', enriched);
  }

  /**
   * Handle RoboTarget Error
   * Event: When an error occurs during RoboTarget execution
   */
  async handleError(message) {
    logger.error(`RoboTarget Error: ${message.ErrorMessage}`);

    const enriched = {
      ...message,
      parsed: {
        guidTarget: message.GuidTarget,
        guidSession: message.GuidSession,
        errorCode: message.ErrorCode,
        errorMessage: message.ErrorMessage,
        reason: message.Reason,
        timestamp: new Date().toISOString(),
      },
    };

    // Emit to WebSocket clients
    this.connection.emit('roboTargetError', enriched);

    // Notify Laravel
    try {
      await this.notifyLaravel('session-error', enriched.parsed);
    } catch (error) {
      logger.error('Error notifying Laravel of RoboTarget error:', error);
    }

    // Update state
    this.currentTargetState.status = 'error';
  }

  /**
   * Notify Laravel backend via webhook
   */
  async notifyLaravel(eventType, data) {
    if (!this.laravelApiUrl) {
      logger.warn('Laravel API URL not configured, skipping webhook');
      return;
    }

    const webhookUrl = `${this.laravelApiUrl}/api/webhooks/robotarget/${eventType}`;

    const payload = {
      event: eventType,
      timestamp: new Date().toISOString(),
      data: data,
    };

    const headers = {
      'Content-Type': 'application/json',
    };

    // Add webhook secret if configured
    if (this.webhookSecret) {
      headers['X-Webhook-Secret'] = this.webhookSecret;
    }

    try {
      const response = await axios.post(webhookUrl, payload, {
        headers,
        timeout: 5000, // 5 second timeout
      });

      logger.debug(`Laravel webhook response (${eventType}):`, response.data);
      return response.data;

    } catch (error) {
      if (error.response) {
        // Server responded with error status
        logger.error(`Laravel webhook failed (${eventType}):`, {
          status: error.response.status,
          data: error.response.data,
        });
      } else if (error.request) {
        // No response received
        logger.error(`Laravel webhook timeout/no response (${eventType})`);
      } else {
        // Other error
        logger.error(`Laravel webhook error (${eventType}):`, error.message);
      }
      throw error;
    }
  }

  /**
   * Get human-readable result text
   */
  getResultText(resultCode) {
    const results = {
      1: 'OK',
      2: 'Aborted',
      3: 'Error',
    };
    return results[resultCode] || 'Unknown';
  }

  /**
   * Get current target state
   */
  getCurrentState() {
    return { ...this.currentTargetState };
  }

  /**
   * Reset state (useful for testing)
   */
  resetState() {
    this.currentTargetState = {
      guidTarget: null,
      guidSession: null,
      startTime: null,
      shotCount: 0,
      status: 'idle',
    };
  }
}

export default RoboTargetEventHandler;
