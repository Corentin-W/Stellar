import Commands from '../commands.js';
import logger from '../../utils/logger.js';

/**
 * RoboTarget Commands
 *
 * Extends the base Commands class with RoboTarget-specific functionality
 * and convenience methods for the REST API.
 */
class RoboTargetCommands extends Commands {
  constructor(connection) {
    super(connection);
  }

  /**
   * Set target status (active/inactive)
   * @param {Object} data - { GuidTarget, TargetActive }
   */
  async setTargetStatus(data) {
    const { GuidTarget, TargetActive } = data;

    if (TargetActive) {
      logger.info(`Activating target: ${GuidTarget}`);
      return this.activateTarget(GuidTarget);
    } else {
      logger.info(`Deactivating target: ${GuidTarget}`);
      return this.deactivateTarget(GuidTarget);
    }
  }

  /**
   * Get session list for a specific target
   * @param {Object} data - { GuidTarget }
   */
  async getSessionListByTarget(data) {
    const { GuidTarget } = data;

    logger.info(`Getting session list for target: ${GuidTarget}`);

    return this.send('RoboTargetGetSessionListByTarget', {
      GuidTarget,
    });
  }

  /**
   * Get all active targets
   */
  async getActiveTargets() {
    logger.info('Getting all active targets');

    return this.send('RoboTargetGetActiveTargets');
  }

  /**
   * Get target details by GUID
   * @param {string} guid - Target GUID
   */
  async getTargetDetails(guid) {
    logger.info(`Getting target details: ${guid}`);

    return this.send('RoboTargetGetTarget', {
      GuidTarget: guid,
    });
  }

  /**
   * Get shot list for a target
   * @param {string} targetGuid - Target GUID
   */
  async getShotsForTarget(targetGuid) {
    logger.info(`Getting shots for target: ${targetGuid}`);

    return this.send('RoboTargetGetShots', {
      GuidTarget: targetGuid,
    });
  }

  /**
   * Clear all targets in a set
   * @param {string} setGuid - Set GUID
   */
  async clearSet(setGuid) {
    logger.info(`Clearing all targets in set: ${setGuid}`);

    return this.send('RoboTargetClearSet', {
      GuidSet: setGuid,
    });
  }

  /**
   * Create a complete target with set and shots in one operation
   * @param {Object} data - Complete target configuration
   */
  async createCompleteTarget(data) {
    const { set, target, shots } = data;

    try {
      // 1. Create or update the Set
      logger.info(`Creating set: ${set.GuidSet}`);
      await this.addSet(set);

      // 2. Create the Target
      logger.info(`Creating target: ${target.GuidTarget}`);
      await this.addTarget(target);

      // 3. Add all shots
      if (shots && Array.isArray(shots) && shots.length > 0) {
        logger.info(`Adding ${shots.length} shots to target`);
        for (const shot of shots) {
          await this.addShot({
            RefGuidTarget: target.GuidTarget,
            ...shot,
          });
        }
      }

      return {
        success: true,
        message: 'Target created successfully',
        set: set.GuidSet,
        target: target.GuidTarget,
        shots: shots?.length || 0,
      };

    } catch (error) {
      logger.error('Error creating complete target:', error);
      throw error;
    }
  }

  /**
   * Delete a complete target with all its shots
   * @param {string} targetGuid - Target GUID
   */
  async deleteCompleteTarget(targetGuid) {
    try {
      // 1. Get all shots for this target
      const shotsResult = await this.getShotsForTarget(targetGuid);
      const shots = shotsResult.parsed?.shots || [];

      // 2. Delete all shots
      if (shots.length > 0) {
        logger.info(`Deleting ${shots.length} shots`);
        for (const shot of shots) {
          await this.deleteShot(shot.GuidShot);
        }
      }

      // 3. Delete the target
      logger.info(`Deleting target: ${targetGuid}`);
      await this.deleteTarget(targetGuid);

      return {
        success: true,
        message: 'Target deleted successfully',
        shotsDeleted: shots.length,
      };

    } catch (error) {
      logger.error('Error deleting complete target:', error);
      throw error;
    }
  }

  /**
   * Get progress information for a target
   * This queries Voyager's current session state
   * @param {string} targetGuid - Target GUID
   */
  async getTargetProgress(targetGuid) {
    logger.info(`Getting progress for target: ${targetGuid}`);

    return this.send('RoboTargetGetProgress', {
      GuidTarget: targetGuid,
    });
  }

  /**
   * Pause/Resume RoboTarget scheduler
   * @param {boolean} pause - true to pause, false to resume
   */
  async setSchedulerPaused(pause) {
    logger.info(`${pause ? 'Pausing' : 'Resuming'} RoboTarget scheduler`);

    return this.send('RoboTargetSetSchedulerPaused', {
      Paused: pause,
    });
  }

  /**
   * Force start a specific target now
   * @param {string} targetGuid - Target GUID
   */
  async forceStartTarget(targetGuid) {
    logger.info(`Force starting target: ${targetGuid}`);

    return this.send('RoboTargetForceStart', {
      GuidTarget: targetGuid,
    });
  }

  /**
   * Get list of completed shots for a session
   * @param {string} sessionGuid - Session GUID
   * @returns {Promise} List of completed shots with metadata
   */
  async getShotDoneBySessionList(sessionGuid) {
    logger.info(`Getting completed shots for session: ${sessionGuid}`);

    return this.send('RoboTargetGetShotDoneBySessionList', {
      RefGuidSession: sessionGuid,
    });
  }

  /**
   * Get list of completed shots for a set
   * @param {string} setGuid - Set GUID
   * @returns {Promise} List of completed shots with metadata
   */
  async getShotDoneBySetList(setGuid) {
    logger.info(`Getting completed shots for set: ${setGuid}`);

    return this.send('RoboTargetGetShotDoneBySetList', {
      RefGuidSet: setGuid,
    });
  }

  /**
   * Get JPG image for a specific shot
   * @param {string} shotDoneGuid - Shot Done GUID
   * @param {string} fitFileName - Optional FIT file name (if not using GUID)
   * @returns {Promise} Base64 JPG data + metadata (HFD, StarIndex, etc.)
   */
  async getShotJpg(shotDoneGuid, fitFileName = '') {
    logger.info(`Getting JPG for shot: ${shotDoneGuid || fitFileName}`);

    return this.send('RoboTargetGetShotJpg', {
      RefGuidShotDone: shotDoneGuid || '',
      FITFileName: fitFileName,
    });
  }

  /**
   * Get list of completed shots since a specific timestamp
   * @param {number} sinceTimestamp - Unix timestamp (epoch)
   * @param {string} targetGuid - Optional target GUID filter
   * @param {string} setGuid - Optional set GUID filter
   * @returns {Promise} List of completed shots since timestamp
   */
  async getShotDoneSinceList(sinceTimestamp, targetGuid = '', setGuid = '') {
    logger.info(`Getting completed shots since: ${new Date(sinceTimestamp * 1000).toISOString()}`);

    return this.send('RoboTargetGetShotDoneSinceList', {
      Since: sinceTimestamp,
      RefGuidTarget: targetGuid,
      RefGuidSet: setGuid,
    });
  }
}

export default RoboTargetCommands;
