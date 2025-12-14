import logger from '../utils/logger.js';
import eventRelay from '../api/event-relay.js';

class EventHandler {
  constructor(connection) {
    this.connection = connection;
  }

  async handle(message) {
    if (!message.Event) {
      logger.warn('Message without Event field:', message);
      return;
    }

    const eventType = message.Event;

    try {
      // Call specific handler if exists
      const handlerMethod = `handle${eventType}`;
      if (typeof this[handlerMethod] === 'function') {
        await this[handlerMethod](message);
      } else {
        logger.debug(`Unhandled event: ${eventType}`);
        this.connection.emit('unknownEvent', message);
      }
    } catch (error) {
      logger.error(`Error handling event ${eventType}:`, error);
    }
  }

  handleVersion(message) {
    logger.info(`Voyager Version: ${message.VOYVersion}`);
    this.connection.emit('version', message);
  }

  handlePolling(message) {
    // Heartbeat received
    logger.debug('Polling received');
    this.connection.emit('polling', message);
  }

  handleSignal(message) {
    const signalCodes = {
      1: 'Autofocus Error',
      2: 'Action Queue Empty',
      5: 'Autofocus Running',
      18: 'Shot Running',
      500: 'General Error',
      501: 'IDLE',
      502: 'Action Running',
      503: 'Action Stopped',
    };

    const description = signalCodes[message.Code] || `Unknown Signal ${message.Code}`;
    logger.info(`Signal: ${description} (${message.Code})`);

    this.connection.emit('signal', {
      ...message,
      description,
    });
  }

  handleControlData(message) {
    // Main dashboard state update
    logger.debug('ControlData received');

    // Parse and enrich the data
    const enriched = {
      ...message,
      parsed: {
        voyagerStatus: this.parseVoyagerStatus(message.VOYSTAT),
        setupConnected: message.SETUPCONN,
        camera: {
          connected: message.CCDCONN,
          temperature: this.parseValue(message.CCDTEMP),
          power: this.parseValue(message.CCDPOW),
          setpoint: this.parseValue(message.CCDSETP),
          cooling: message.CCDCOOL,
        },
        mount: {
          connected: message.MNTCONN,
          parked: message.MNTPARK,
          ra: message.MNTRA,
          dec: message.MNTDEC,
          tracking: message.MNTTRACK,
        },
        focuser: {
          connected: message.AFCONN,
          position: this.parseValue(message.AFPOS),
          temperature: this.parseValue(message.AFTEMP),
        },
        sequence: {
          name: message.SEQNAME,
          remaining: message.SEQREMAIN,
        },
        guiding: {
          status: this.parseGuidingStatus(message.GUIDESTAT),
          rmsX: this.parseValue(message.GUIDEX),
          rmsY: this.parseValue(message.GUIDEY),
        },
      },
    };

    this.connection.emit('controlData', enriched);
  }

  async handleNewJPGReady(message) {
    logger.info(`New JPG preview ready: ${message.File}`);

    const enriched = {
      ...message,
      parsed: {
        filename: message.File,
        target: message.SequenceTarget,
        timestamp: message.TimeInfo,
        exposure: message.Expo,
        binning: message.Bin,
        filter: message.Filter,
        hfd: message.HFD,
        starIndex: message.StarIndex,
        dimensions: {
          width: message.PixelDimX,
          height: message.PixelDimY,
        },
        imageData: message.Base64Data,
      },
    };

    this.connection.emit('newJPG', enriched);

    // Relay to Laravel API (if this is part of a RoboTarget session)
    if (message.GuidSession) {
      try {
        await eventRelay.imageReady(
          message.GuidSession,
          {
            filename: message.File,
            thumbnail: message.Base64Data, // Base64 JPG thumbnail
            filter: message.Filter,
            exposure: message.Expo,
            hfd: message.HFD,
            timestamp: message.TimeInfo || new Date().toISOString()
          }
        );
      } catch (error) {
        logger.error('Failed to relay image ready event', error);
      }
    }
  }

  handleNewFITReady(message) {
    logger.info(`New FITS ready: ${message.File}`);

    const fitTypes = {
      0: 'LIGHT',
      1: 'BIAS',
      2: 'DARK',
      3: 'FLAT',
    };

    const enriched = {
      ...message,
      parsed: {
        filename: message.File,
        type: fitTypes[message.Type] || 'UNKNOWN',
        voyType: message.VoyType,
        target: message.SeqTarget,
      },
    };

    this.connection.emit('newFITReady', enriched);
  }

  handleShotRunning(message) {
    logger.debug(`Shot running: ${message.Remain}s remaining`);

    const enriched = {
      ...message,
      parsed: {
        remaining: message.Remain,
        total: message.Total,
        progress: message.Total > 0 ? ((message.Total - message.Remain) / message.Total) * 100 : 0,
      },
    };

    this.connection.emit('shotRunning', enriched);
  }

  handleRemoteActionResult(message) {
    const resultCodes = {
      0: 'NEED_INIT',
      1: 'READY',
      2: 'RUNNING',
      4: 'OK',
      5: 'ERROR',
      6: 'ABORTING',
      7: 'ABORTED',
      8: 'TIMEOUT',
      10: 'OK_PARTIAL',
    };

    const status = resultCodes[message.ActionResultInt] || 'UNKNOWN';
    logger.info(`Remote Action Result: ${status} (UID: ${message.UID})`);

    const enriched = {
      ...message,
      parsed: {
        uid: message.UID,
        status,
        statusCode: message.ActionResultInt,
        reason: message.Motivo,
        params: message.ParamRet,
      },
    };

    this.connection.emit('remoteActionResult', enriched);
  }

  handleShutDown(message) {
    logger.warn('Voyager is shutting down!');
    this.connection.emit('shutdown', message);

    // Initiate clean disconnect
    setTimeout(() => {
      this.connection.disconnect();
    }, 1000);
  }

  // RoboTarget Event Handlers

  async handleRoboTargetSessionStart(message) {
    logger.info(`RoboTarget Session Started: ${message.GuidTarget}`);
    this.connection.emit('roboTargetSessionStart', message);

    // Relay to Laravel API
    try {
      await eventRelay.sessionStarted(
        message.GuidSession,
        message.GuidTarget,
        {
          target_guid: message.GuidTarget,
          session_guid: message.GuidSession,
          timestamp: new Date().toISOString()
        }
      );
    } catch (error) {
      logger.error('Failed to relay session start event', error);
    }
  }

  async handleRoboTargetSessionComplete(message) {
    logger.info(`RoboTarget Session Completed: ${message.GuidTarget}`);
    this.connection.emit('roboTargetSessionComplete', message);

    // Relay to Laravel API
    try {
      await eventRelay.sessionCompleted(
        message.GuidSession,
        {
          target_guid: message.GuidTarget,
          result: message.Result,
          images_captured: message.ImagesCaptured,
          images_accepted: message.ImagesAccepted,
          timestamp: new Date().toISOString()
        }
      );
    } catch (error) {
      logger.error('Failed to relay session complete event', error);
    }
  }

  handleRoboTargetSessionAbort(message) {
    logger.warn(`RoboTarget Session Aborted: ${message.GuidTarget}`);
    this.connection.emit('roboTargetSessionAbort', message);
  }

  handleRoboTargetProgress(message) {
    logger.debug(`RoboTarget Progress: ${message.Progress}%`);
    this.connection.emit('roboTargetProgress', message);
  }

  handleRoboTargetShotComplete(message) {
    logger.debug(`RoboTarget Shot Complete`);
    this.connection.emit('roboTargetShotComplete', message);
  }

  handleRoboTargetError(message) {
    logger.error(`RoboTarget Error: ${message.ErrorMessage || 'Unknown error'}`);
    this.connection.emit('roboTargetError', message);
  }

  // Helper methods
  parseVoyagerStatus(status) {
    const statuses = {
      0: 'STOPPED',
      1: 'IDLE',
      2: 'RUN',
      3: 'ERROR',
    };
    return statuses[status] || 'UNKNOWN';
  }

  parseGuidingStatus(status) {
    const statuses = {
      0: 'STOPPED',
      1: 'SETTLING',
      2: 'RUNNING',
    };
    return statuses[status] || 'UNKNOWN';
  }

  parseValue(value) {
    // Voyager uses special values
    if (value === -123456789) return null; // OFF
    if (value === 123456789) return null; // ERROR
    return value;
  }
}

export default EventHandler;
