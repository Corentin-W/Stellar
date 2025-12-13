import { v4 as uuidv4 } from 'uuid';
import crypto from 'crypto';
import logger from '../utils/logger.js';

class Authentication {
  constructor(connection) {
    this.connection = connection;
  }

  async authenticate() {
    if (!this.connection.config.auth.enabled) {
      logger.info('Authentication disabled');
      return true;
    }

    // CORRECTION: Toujours utiliser AuthenticateUserBase (avec authBase déjà en Base64)
    const { authBase, username, password } = this.connection.config.auth;

    let base64Credentials;

    // Si authBase est fourni (déjà encodé en Base64), on l'utilise directement
    if (authBase) {
      base64Credentials = authBase;
      logger.info('Authenticating with pre-encoded Base64 credentials...');
    }
    // Sinon, on encode username:password
    else if (username && password) {
      logger.info(`Authenticating as ${username}...`);
      const credentials = `${username}:${password}`;
      base64Credentials = Buffer.from(credentials).toString('base64');
    }
    else {
      throw new Error('Username/password or authBase required for authentication');
    }

    // Send authentication command (STANDARD selon la doc)
    const authCommand = {
      method: 'AuthenticateUserBase',
      params: {
        UID: uuidv4(),
        Base: base64Credentials,
      },
      id: 1,
    };

    return this.sendAuthCommand(authCommand, 'authbase');
  }

  async authenticateMAC() {
    const { authBase, macKey, macWord1, macWord2, macWord3, macWord4, licenseNumber } = this.connection.config.auth;

    if (!authBase || !macKey) {
      throw new Error('AUTH_BASE and MAC_KEY required for MAC authentication');
    }

    logger.info(`Authenticating with MAC (key: ${macKey})...`);

    const uid = uuidv4();

    // Build MAC authentication request
    // Format: AuthBase + MacKey + Word1 + Word2 + Word3 + Word4
    const authString = `${authBase}${macKey}${macWord1 || ''}${macWord2 || ''}${macWord3 || ''}${macWord4 || ''}`;
    const mac = crypto.createHash('md5').update(authString).digest('hex');

    logger.debug(`MAC auth string: ${authString.substring(0, 20)}...`);
    logger.debug(`Generated MAC: ${mac}`);

    // Send MAC authentication command
    const authCommand = {
      method: 'RemoteAuthenticationRequest',
      params: {
        UID: uid,
        AuthBase: parseInt(authBase),
        MacKey: macKey,
        Word1: macWord1 || '',
        Word2: macWord2 || '',
        Word3: macWord3 || '',
        Word4: macWord4 || '',
        LicenseNumber: licenseNumber || '',
        MAC: mac,
      },
      id: 1,
    };

    return this.sendAuthCommand(authCommand, 'authresponse');
  }

  /**
   * Activate RoboTarget Manager Mode (REQUIRED for RoboTarget API)
   * Must be called AFTER authentication and AFTER receiving Version event
   *
   * Hash formula: SHA1("RoboTarget Shared secret" ||:|| SessionKey ||:|| Word1+Word2+Word3+Word4) → Base64
   */
  async activateRoboTargetManagerMode(sessionKey) {
    const { macKey, macWord1, macWord2, macWord3, macWord4 } = this.connection.config.auth;

    if (!macKey || !sessionKey) {
      throw new Error('MAC_KEY and SessionKey required for RoboTarget Manager Mode');
    }

    logger.info('🤖 Activating RoboTarget Manager Mode...');

    const uid = uuidv4();

    // Build Hash for RemoteSetRoboTargetManagerMode
    // Formula: SHA1("RoboTarget Shared secret" ||:|| SessionKey ||:|| Word1+Word2+Word3+Word4)
    const sharedSecret = 'RoboTarget Shared secret';
    const separator = '||:||';
    const wordsConcat = `${macWord1 || ''}${macWord2 || ''}${macWord3 || ''}${macWord4 || ''}`;
    const hashString = `${sharedSecret}${separator}${sessionKey}${separator}${wordsConcat}`;

    const hash = crypto.createHash('sha1').update(hashString).digest('base64');

    logger.debug(`Hash string length: ${hashString.length}`);
    logger.debug(`Generated Hash: ${hash}`);

    // Send RemoteSetRoboTargetManagerMode command
    const command = {
      method: 'RemoteSetRoboTargetManagerMode',
      params: {
        UID: uid,
        MACKey: macKey,
        Hash: hash,
      },
      id: 3,
    };

    return new Promise((resolve, reject) => {
      const timeout = setTimeout(() => {
        reject(new Error('RoboTarget Manager Mode activation timeout'));
      }, 10000);

      // Listen for RemoteActionResult
      const onData = (data) => {
        const lines = data.toString().split('\r\n');

        for (const line of lines) {
          if (line.trim()) {
            try {
              const response = JSON.parse(line);

              // Check if this is the RemoteActionResult for our command
              if (response.Event === 'RemoteActionResult' && response.UID === uid) {
                clearTimeout(timeout);
                this.connection.socket.removeListener('data', onData);

                // Check ParamRet.ret === "DONE"
                if (response.ParamRet && response.ParamRet.ret === 'DONE') {
                  logger.info('✅ RoboTarget Manager Mode activated successfully!');
                  logger.info(`Status: ${response.ParamRet.ret}`);
                  resolve(response);
                } else {
                  const error = response.ParamRet?.ret || 'Unknown error';
                  logger.error(`❌ RoboTarget Manager Mode activation failed: ${error}`);
                  reject(new Error(`RoboTarget activation failed: ${error}`));
                }
              }
            } catch (error) {
              logger.debug(`Parse error (ignoring): ${error.message}`);
            }
          }
        }
      };

      this.connection.socket.on('data', onData);

      // Send command
      logger.debug(`Sending RemoteSetRoboTargetManagerMode: ${JSON.stringify(command)}`);
      this.connection.send(command);
    });
  }

  /**
   * Generate MAC for RoboTarget commands (after Manager Mode is activated)
   * Formula: SHA1(SharedSecret + SessionKey + JSONRPCid + CommandUID) → Base64
   */
  generateRoboTargetMAC(sessionKey, jsonRpcId, commandUid) {
    const sharedSecret = 'RoboTarget Shared secret';
    const macString = `${sharedSecret}${sessionKey}${jsonRpcId}${commandUid}`;
    const mac = crypto.createHash('sha1').update(macString).digest('base64');

    logger.debug(`MAC string: ${macString.substring(0, 40)}... → ${mac}`);
    return mac;
  }

  sendAuthCommand(authCommand, responseKey) {
    return new Promise((resolve, reject) => {
      const timeout = setTimeout(() => {
        reject(new Error('Authentication timeout'));
      }, 5000);

      // Listen for authentication response
      const onData = (data) => {
        const lines = data.toString().split('\r\n');

        for (const line of lines) {
          if (line.trim()) {
            try {
              const response = JSON.parse(line);

              if (response.id === 1) {
                clearTimeout(timeout);

                // Check for successful authentication
                if (response[responseKey] || response.authbase) {
                  const authData = response[responseKey] || response.authbase;
                  logger.info(`✅ Authenticated successfully`);
                  if (authData.Username) {
                    logger.info(`Username: ${authData.Username}`);
                  }
                  if (authData.Permissions) {
                    logger.info(`Permissions: ${authData.Permissions}`);
                  }
                  this.connection.socket.removeListener('data', onData);
                  resolve(authData);
                } else if (response.error) {
                  logger.error('Authentication failed:', response.error);
                  this.connection.socket.removeListener('data', onData);
                  reject(new Error(response.error.message || 'Authentication failed'));
                } else if (response.result && response.result.success) {
                  logger.info('✅ Authentication successful');
                  this.connection.socket.removeListener('data', onData);
                  resolve(response.result);
                }
              }
            } catch (error) {
              // Ignore parsing errors for other messages
              logger.debug(`Parse error (ignoring): ${error.message}`);
            }
          }
        }
      };

      this.connection.socket.on('data', onData);

      // Send authentication command
      logger.debug(`Sending auth command: ${JSON.stringify(authCommand).substring(0, 100)}...`);
      this.connection.send(authCommand);
    });
  }
}

export default Authentication;
