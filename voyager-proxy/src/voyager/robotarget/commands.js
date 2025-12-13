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
}

export default RoboTargetCommands;
