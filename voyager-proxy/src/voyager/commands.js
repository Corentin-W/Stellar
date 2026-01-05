import { v4 as uuidv4 } from 'uuid';
import logger from '../utils/logger.js';

class Commands {
  constructor(connection) {
    this.connection = connection;
    this.pendingCommands = new Map();
    this.nextId = 1; // Sequential ID counter for JSON-RPC messages
  }

  async send(method, params = {}) {
    const uid = uuidv4();
    const id = this.nextId++;

    // Reset counter if it gets too large (keep it well within Int32 range)
    if (this.nextId > 2000000000) {
      this.nextId = 1;
    }

    // Build base command params
    const commandParams = {
      UID: uid,
      ...params,
    };

    // Add MAC authentication for RoboTarget commands if Manager Mode is active
    if (method.startsWith('RemoteRoboTarget')) {
      logger.info(`🔍 RoboTarget command detected: ${method}`);
      logger.info(`   Manager Mode Active: ${this.connection.isRoboTargetManagerMode}`);
      logger.info(`   Session Key exists: ${!!this.connection.sessionKey}`);

      if (this.connection.isRoboTargetManagerMode && this.connection.sessionKey) {
        const mac = this.connection.auth.generateRoboTargetMAC(
          this.connection.sessionKey,
          id,
          uid
        );
        commandParams.MAC = mac;
        logger.info(`✅ Added MAC to ${method}: ${mac.substring(0, 20)}...`);
      } else {
        logger.error(`❌ Cannot send ${method}: RoboTarget Manager Mode not active or missing session key!`);
        logger.error(`   This command will likely fail or timeout.`);
      }
    }

    const command = {
      method,
      params: commandParams,
      id,
    };

    return new Promise((resolve, reject) => {
      const timeout = setTimeout(() => {
        this.pendingCommands.delete(uid);
        reject(new Error(`Command timeout: ${method}`));
      }, 30000); // 30 seconds timeout

      this.pendingCommands.set(uid, { resolve, reject, timeout, method });

      // Listen for RemoteActionResult
      const listener = (result) => {
        if (result.UID === uid || result.parsed?.uid === uid) {
          const pending = this.pendingCommands.get(uid);
          if (pending) {
            clearTimeout(pending.timeout);
            this.pendingCommands.delete(uid);

            const status = result.parsed?.statusCode || result.ActionResultInt;

            if (status === 4 || status === 10) {
              // OK or OK_PARTIAL
              resolve(result);
            } else if (status === 5) {
              // ERROR
              reject(new Error(result.parsed?.reason || result.Motivo || 'Command failed'));
            } else {
              resolve(result); // Return intermediate states
            }
          }
        }
      };

      this.connection.once('remoteActionResult', listener);

      try {
        this.connection.send(command);
        logger.info(`Command sent: ${method} (UID: ${uid})`);
      } catch (error) {
        clearTimeout(timeout);
        this.pendingCommands.delete(uid);
        this.connection.removeListener('remoteActionResult', listener);
        reject(error);
      }
    });
  }

  // RoboTarget Commands

  async addSet(data) {
    return this.send('RemoteRoboTargetAddSet', data);
  }

  async updateSet(data) {
    return this.send('RemoteRoboTargetUpdateSet', data);
  }

  async deleteSet(guid) {
    return this.send('RemoteRoboTargetDeleteSet', {
      GuidSet: guid,
    });
  }

  async addTarget(data) {
    return this.send('RemoteRoboTargetAddTarget', data);
  }

  async updateTarget(data) {
    return this.send('RemoteRoboTargetUpdateTarget', data);
  }

  async deleteTarget(guid) {
    return this.send('RemoteRoboTargetDeleteTarget', {
      GuidTarget: guid,
    });
  }

  async activateTarget(guid) {
    return this.send('RemoteRoboTargetEnableDisableObject', {
      RefGuidObject: guid,
      ObjectType: 1, // 1 = Target
      OperationType: 0, // 0 = Enable
    });
  }

  async deactivateTarget(guid) {
    return this.send('RemoteRoboTargetEnableDisableObject', {
      RefGuidObject: guid,
      ObjectType: 1, // 1 = Target
      OperationType: 1, // 1 = Disable
    });
  }

  async addShot(data) {
    return this.send('RemoteRoboTargetAddShot', data);
  }

  async updateShot(data) {
    return this.send('RemoteRoboTargetUpdateShot', data);
  }

  async deleteShot(guid) {
    return this.send('RemoteRoboTargetDeleteShot', {
      GuidShot: guid,
    });
  }

  async listSets() {
    logger.info('🎯 listSets() called - sending RemoteRoboTargetGetSet command');
    const result = await this.send('RemoteRoboTargetGetSet', {
      RefGuidSet: '', // Empty string returns all sets
    });
    logger.info('🎯 listSets() result received:', result);
    return result;
  }

  async listTargetsForSet(setGuid) {
    logger.info(`🎯 listTargetsForSet() called for set: ${setGuid}`);
    return this.send('RemoteRoboTargetGetTarget', {
      RefGuidSet: setGuid || '', // Empty string returns all targets
    });
  }

  async listBaseSequences(profileName = '') {
    logger.info('🎯 listBaseSequences() called - sending RemoteRoboTargetGetBaseSequence command');
    const result = await this.send('RemoteRoboTargetGetBaseSequence', {
      ProfileName: profileName, // Empty string returns all base sequences for all profiles
    });
    logger.info('🎯 listBaseSequences() result received:', result);
    return result;
  }

  // Control Commands

  async abort() {
    return this.send('RemoteAbortAction');
  }

  async setDashboardMode(enabled = true) {
    // RemoteSetDashboardMode doesn't return RemoteActionResult
    // Just send the command directly
    const uid = uuidv4();
    const id = this.nextId++;

    // Reset counter if it gets too large (keep it well within Int32 range)
    if (this.nextId > 2000000000) {
      this.nextId = 1;
    }

    const command = {
      method: 'RemoteSetDashboardMode',
      params: {
        UID: uid,
        IsOn: enabled,
      },
      id,
    };

    logger.info(`Setting Dashboard Mode: ${enabled}`);
    this.connection.send(command);

    // Return immediately without waiting for response
    return Promise.resolve({ success: true, uid });
  }

  async takeShot(exposure, binning = 1, filter = 0) {
    return this.send('RemoteTakeShot', {
      Expo: exposure,
      Bin: binning,
      FilterIndex: filter,
    });
  }

  async autofocus() {
    return this.send('RemoteAutoFocus');
  }

  async platesolve() {
    return this.send('RemotePlateSolve');
  }

  async park() {
    return this.send('RemotePark');
  }

  async unpark() {
    return this.send('RemoteUnpark');
  }

  async startTracking() {
    return this.send('RemoteSetTracking', { Val: true });
  }

  async stopTracking() {
    return this.send('RemoteSetTracking', { Val: false });
  }

  async coolCamera(temperature) {
    return this.send('RemoteCoolCamera', {
      Temp: temperature,
      On: true,
    });
  }

  async warmCamera() {
    return this.send('RemoteCoolCamera', {
      On: false,
    });
  }
}

export default Commands;
