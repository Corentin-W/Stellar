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
    const { sharedSecret, macKey, macWord1, macWord2, macWord3, macWord4 } = this.connection.config.auth;

    if (!sharedSecret || !macKey || !sessionKey) {
      throw new Error('SHARED_SECRET, MAC_KEY and SessionKey required for RoboTarget Manager Mode');
    }

    logger.info('🤖 Activating RoboTarget Manager Mode...');

    // Build Hash for RemoteSetRoboTargetManagerMode
    // Formula: Testing various combinations
    const wordsConcat = `${macWord1 || ''}${macWord2 || ''}${macWord3 || ''}${macWord4 || ''}`;

    // FORMULA FROM DOCUMENTATION:
    // SHA1("RoboTarget Shared secret" ||:|| SessionKey ||:|| MAC1+MAC2+MAC3+MAC4) → Base64
    // Separator: "||:||"
    // SessionKey: Timestamp from Version event (as is, with decimals)

    const separator = '||:||';

    // Correct formula according to documentation
    const hashString = `${sharedSecret}${separator}${sessionKey}${separator}${wordsConcat}`;

    logger.info(`🔍 Using documented hash formula for SessionKey: ${sessionKey}`);
    logger.info(`   Shared Secret: ${sharedSecret}`);
    logger.info(`   MAC Key: ${macKey}`);
    logger.info(`   SessionKey: ${sessionKey}`);
    logger.info(`   Words: ${wordsConcat.substring(0, 20)}...`);
    logger.info(`   Formula: SharedSecret||:||SessionKey||:||Words`);
    logger.debug(`   Full hash string: ${hashString.substring(0, 80)}...`);

    const variations = [
      { name: 'Official Formula: MACKey||:||SessionKey||:||Words', string: hashString },
    ];

    logger.info(`🔍 Testing ${variations.length} hash variations for SessionKey: ${sessionKey}`);

    // Try each variation until one succeeds
    for (let i = 0; i < variations.length; i++) {
      const variation = variations[i];

      // CORRECT HASH CALCULATION (Section 6.a du protocole NDA):
      // 1. SHA1 → bytes
      // 2. Convert bytes to hexadecimal string (40 chars lowercase)
      // 3. Base64 encode the hex string (not the raw bytes!)
      const sha1Hex = crypto.createHash('sha1').update(variation.string).digest('hex');
      const hash = Buffer.from(sha1Hex).toString('base64');

      logger.info(`📝 Trying ${variation.name} (${i + 1}/${variations.length})`);
      logger.debug(`   Hash string: ${variation.string.substring(0, 50)}...`);
      logger.debug(`   SHA1 (hex): ${sha1Hex}`);
      logger.info(`   Hash (Base64 of hex): ${hash}`);

      try {
        const result = await this.tryRoboTargetActivation(macKey, hash, i + 1);
        logger.info(`✅ RoboTarget Manager Mode activated successfully with ${variation.name}!`);
        return result;
      } catch (error) {
        logger.warn(`❌ ${variation.name} failed: ${error.message}`);

        // If it's the last variation, throw the error
        if (i === variations.length - 1) {
          throw new Error(`All ${variations.length} hash variations failed. Last error: ${error.message}`);
        }

        // Otherwise, continue to next variation
        logger.info(`   Trying next variation...`);
      }
    }
  }

  async tryRoboTargetActivation(macKey, hash, attemptId) {
    const uid = uuidv4();

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
      // Listen for response (either RemoteActionResult or JSON-RPC error)
      const onData = (data) => {
        const lines = data.toString().split('\r\n');

        for (const line of lines) {
          if (line.trim()) {
            try {
              const response = JSON.parse(line);

              // Check for JSON-RPC error (MAC Error)
              if (response.id === 3 && response.error) {
                clearTimeout(timeout);
                if (this.connection.socket) {
                  this.connection.socket.removeListener('data', onData);
                }
                reject(new Error(response.error.message || 'Unknown JSON-RPC error'));
                return;
              }

              // Check for successful RemoteActionResult
              if (response.Event === 'RemoteActionResult' && response.UID === uid) {
                clearTimeout(timeout);
                if (this.connection.socket) {
                  this.connection.socket.removeListener('data', onData);
                }

                // Check ParamRet.ret === "DONE"
                if (response.ParamRet && response.ParamRet.ret === 'DONE') {
                  logger.info(`   Status: ${response.ParamRet.ret}`);
                  resolve(response);
                } else {
                  const error = response.ParamRet?.ret || 'Unknown error';
                  reject(new Error(`Activation failed: ${error}`));
                }
              }
            } catch (error) {
              logger.debug(`Parse error (ignoring): ${error.message}`);
            }
          }
        }
      };

      const timeout = setTimeout(() => {
        if (this.connection.socket) {
          this.connection.socket.removeListener('data', onData);
        }
        reject(new Error('Timeout waiting for response'));
      }, 3000); // 3 seconds per attempt

      if (!this.connection.socket || !this.connection.isConnected) {
        clearTimeout(timeout);
        reject(new Error('Not connected to Voyager'));
        return;
      }

      this.connection.socket.on('data', onData);

      // Send command
      logger.debug(`Sending RemoteSetRoboTargetManagerMode (attempt ${attemptId}): ${JSON.stringify(command)}`);
      this.connection.send(command);
    });
  }

  /**
   * Generate MAC for RoboTarget commands (after Manager Mode is activated)
   * Formula from NDA documentation Section 6.b:
   * SHA1(SharedSecret + Sep1 + SessionKey + Sep2 + ID_JSON-RPC + Sep3 + UID_Commande) → Base64 DIRECT
   *
   * CRITICAL: Les séparateurs ne sont PAS uniformes (selon doc Section 6.b) :
   * - Sep1 (Secret → SessionKey) : "|| |" (Pipe, Pipe, 1 Espace, Pipe)
   * - Sep2 (SessionKey → ID) : "||  |" (Pipe, Pipe, 2 Espaces, Pipe) ⚠️
   * - Sep3 (ID → UID) : "|| |" (Pipe, Pipe, 1 Espace, Pipe)
   *
   * Exemple de la doc Section 6.b :
   * "pippo|| |1652231344.88438||  |5|| |0697f2f9-24e4-4850-84e9-18ea28b05fe9"
   *                            ^^^^  <= DEUX espaces ici
   * MAC attendu : nWq/V98Laq+hFFdMvynnneAyKvk= (28 chars)
   *
   * Encoding: Binary SHA1 → Base64 (28 chars) - NOT Hex → Base64 (that's only for Manager Mode activation)
   */
  generateRoboTargetMAC(sessionKey, jsonRpcId, commandUid) {
    const sharedSecret = this.connection.config.auth.sharedSecret;

    // CRITICAL: Convert all parameters to strings to ensure correct concatenation
    // SessionKey is already a string, but jsonRpcId and commandUid might be numbers
    const sessionKeyStr = String(sessionKey);
    const jsonRpcIdStr = String(jsonRpcId);
    const commandUidStr = String(commandUid);

    // IMPORTANT: Séparateurs NON-UNIFORMES selon Section 6.b de la doc NDA
    const sep1 = '|| |';   // Secret → SessionKey (1 espace)
    const sep2 = '||  |';  // SessionKey → ID (2 espaces) ⚠️
    const sep3 = '|| |';   // ID → UID (1 espace)

    // Concatenation avec séparateurs NON-uniformes
    const macString = sharedSecret + sep1 + sessionKeyStr + sep2 + jsonRpcIdStr + sep3 + commandUidStr;

    // Binary SHA1 → Base64 DIRECT (result: 28 chars)
    // NOT Hex → Base64 (that's only for RemoteSetRoboTargetManagerMode)
    const mac = crypto.createHash('sha1').update(macString).digest('base64');

    logger.info(`🔐 MAC generation for RoboTarget command:`);
    logger.info(`   SharedSecret: ${sharedSecret}`);
    logger.info(`   SessionKey: ${sessionKeyStr}`);
    logger.info(`   JSON-RPC ID: ${jsonRpcIdStr}`);
    logger.info(`   Command UID: ${commandUidStr}`);
    logger.info(`   Sep1 (Secret→SessionKey): "${sep1}" (1 espace)`);
    logger.info(`   Sep2 (SessionKey→ID): "${sep2}" (2 espaces) ⚠️`);
    logger.info(`   Sep3 (ID→UID): "${sep3}" (1 espace)`);
    logger.info(`   MAC string: ${macString}`);
    logger.info(`   MAC (Base64): ${mac}`);
    return mac;
  }

  sendAuthCommand(authCommand, responseKey) {
    return new Promise((resolve, reject) => {
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
                  if (this.connection.socket) {
                    this.connection.socket.removeListener('data', onData);
                  }
                  resolve(authData);
                } else if (response.error) {
                  logger.error('Authentication failed:', response.error);
                  if (this.connection.socket) {
                    this.connection.socket.removeListener('data', onData);
                  }
                  reject(new Error(response.error.message || 'Authentication failed'));
                } else if (response.result && response.result.success) {
                  logger.info('✅ Authentication successful');
                  if (this.connection.socket) {
                    this.connection.socket.removeListener('data', onData);
                  }
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

      const timeout = setTimeout(() => {
        if (this.connection.socket) {
          this.connection.socket.removeListener('data', onData);
        }
        reject(new Error('Authentication timeout'));
      }, 5000);

      if (!this.connection.socket || !this.connection.isConnected) {
        clearTimeout(timeout);
        reject(new Error('Not connected to Voyager'));
        return;
      }

      this.connection.socket.on('data', onData);

      // Send authentication command
      logger.debug(`Sending auth command: ${JSON.stringify(authCommand).substring(0, 100)}...`);
      this.connection.send(authCommand);
    });
  }
}

export default Authentication;
