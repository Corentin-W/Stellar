import axios from 'axios';
import logger from '../utils/logger.js';

/**
 * EventRelay - Relaye les événements Voyager vers l'API Laravel
 */
class EventRelay {
  constructor() {
    this.apiBaseUrl = process.env.LARAVEL_API_URL || 'http://localhost:8000/api';
    this.enabled = process.env.ENABLE_EVENT_RELAY !== 'false';

    if (this.enabled) {
      logger.info(`EventRelay initialized - API URL: ${this.apiBaseUrl}`);
    } else {
      logger.warn('EventRelay is disabled');
    }
  }

  /**
   * Send session started event
   */
  async sessionStarted(sessionGuid, targetGuid, voyagerData = {}) {
    if (!this.enabled) return;

    try {
      const response = await axios.post(`${this.apiBaseUrl}/voyager/events/session-started`, {
        session_guid: sessionGuid,
        target_guid: targetGuid,
        voyager_data: voyagerData
      }, {
        timeout: 5000
      });

      logger.info('Session started event sent to Laravel', {
        session_guid: sessionGuid,
        status: response.status
      });

      return response.data;
    } catch (error) {
      logger.error('Failed to send session started event', {
        session_guid: sessionGuid,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Send progress update
   */
  async progress(sessionGuid, progressData) {
    if (!this.enabled) return;

    try {
      const response = await axios.post(`${this.apiBaseUrl}/voyager/events/progress`, {
        session_guid: sessionGuid,
        progress: {
          percentage: progressData.percentage,
          current_shot: progressData.currentShot,
          total_shots: progressData.totalShots,
          remaining: progressData.remaining,
          camera: progressData.camera,
          mount: progressData.mount
        }
      }, {
        timeout: 5000
      });

      logger.debug('Progress update sent to Laravel', {
        session_guid: sessionGuid,
        percentage: progressData.percentage
      });

      return response.data;
    } catch (error) {
      logger.error('Failed to send progress update', {
        session_guid: sessionGuid,
        error: error.message
      });
      // Don't throw - progress updates are non-critical
    }
  }

  /**
   * Send image ready event
   */
  async imageReady(sessionGuid, imageData) {
    if (!this.enabled) return;

    try {
      const response = await axios.post(`${this.apiBaseUrl}/voyager/events/image-ready`, {
        session_guid: sessionGuid,
        image: {
          filename: imageData.filename,
          thumbnail: imageData.thumbnail, // Base64
          filter: imageData.filter,
          exposure: imageData.exposure,
          hfd: imageData.hfd,
          timestamp: imageData.timestamp
        }
      }, {
        timeout: 10000 // Plus de temps pour les images
      });

      logger.info('Image ready event sent to Laravel', {
        session_guid: sessionGuid,
        filename: imageData.filename,
        status: response.status
      });

      return response.data;
    } catch (error) {
      logger.error('Failed to send image ready event', {
        session_guid: sessionGuid,
        error: error.message
      });
      // Don't throw - images will be accessible via other means
    }
  }

  /**
   * Send session completed event
   */
  async sessionCompleted(sessionGuid, completionData = {}) {
    if (!this.enabled) return;

    try {
      const response = await axios.post(`${this.apiBaseUrl}/voyager/events/session-completed`, {
        session_guid: sessionGuid,
        completion_data: completionData
      }, {
        timeout: 5000
      });

      logger.info('Session completed event sent to Laravel', {
        session_guid: sessionGuid,
        status: response.status
      });

      return response.data;
    } catch (error) {
      logger.error('Failed to send session completed event', {
        session_guid: sessionGuid,
        error: error.message
      });
      throw error;
    }
  }

  /**
   * Test connection to Laravel API
   */
  async testConnection() {
    try {
      const response = await axios.get(`${this.apiBaseUrl}/subscriptions/plans`, {
        timeout: 5000
      });

      logger.info('Laravel API connection test successful', {
        status: response.status
      });

      return true;
    } catch (error) {
      logger.error('Laravel API connection test failed', {
        error: error.message
      });

      return false;
    }
  }
}

export default new EventRelay();
