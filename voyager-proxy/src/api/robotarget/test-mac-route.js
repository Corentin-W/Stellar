import crypto from 'crypto';
import logger from '../../utils/logger.js';

/**
 * Test MAC endpoint - allows testing different MAC formulas
 */
export function setupTestMacRoute(router, roboTargetCommands, connection) {

  /**
   * Auto-test endpoint - tests all MAC formula variants automatically
   */
  router.post('/auto-test-mac', async (req, res) => {
    try {
      const { method, params } = req.body;

      if (!method || !params) {
        return res.status(400).json({
          success: false,
          message: 'Missing required fields: method, params',
        });
      }

      // Add RefGuidSet parameter (required by RemoteRoboTargetGetSet)
      if (params.RefGuidSet === undefined) {
        params.RefGuidSet = '';
      }

      // Keep ProfileName empty as per official documentation example
      // Empty ProfileName will return all sets for all profiles
      if (params.ProfileName === undefined) {
        params.ProfileName = '';
      }
      logger.info(`📝 Using ProfileName: "${params.ProfileName}", RefGuidSet: "${params.RefGuidSet}"`);


      // Get current session key
      const sessionKey = connection.sessionKey;
      if (!sessionKey) {
        return res.status(503).json({
          success: false,
          message: 'No active session. Connect to Voyager first.',
        });
      }

      const UID = params.UID || crypto.randomUUID();
      const sharedSecret = connection.config.auth.sharedSecret;
      const macKey = connection.config.auth.macKey;

      // Define all MAC formula variants to test
      // HYPOTHESIS: Maybe ALL Reserved API commands use the same separator as Manager Mode?
      const macVariants = [
        {
          name: '✅ TEST 1: SharedSecret + "||:||" (like Manager Mode) + SHA1→Hex→Base64',
          secret: sharedSecret,
          sep1: '||:||',  // Same as Manager Mode activation
          sep2: '||:||',
          sep3: '||:||',
          conversion: 'hex'
        },
        {
          name: 'TEST 2: Original NDA format "|| |...||  |...|| |" + SHA1→Hex→Base64',
          secret: sharedSecret,
          sep1: '|| |',   // 1 SPACE
          sep2: '||  |',  // 2 SPACES
          sep3: '|| |',   // 1 SPACE
          conversion: 'hex'
        },
        {
          name: 'TEST 3: No separators (like Open API) + SHA1→Hex→Base64',
          secret: sharedSecret,
          sep1: '',
          sep2: '',
          sep3: '',
          conversion: 'hex'
        },
      ];

      const results = [];
      let successfulVariant = null;

      // Test each variant sequentially
      for (let i = 0; i < macVariants.length; i++) {
        const variant = macVariants[i];

        logger.info(`\n🧪 Testing variant ${i + 1}/${macVariants.length}: ${variant.name}`);

        try {
          // Get CURRENT session key (in case Voyager reconnected)
          const currentSessionKey = connection.sessionKey;
          if (!currentSessionKey) {
            logger.error('   ❌ No session key available - skipping variant');
            results.push({
              variant: variant.name,
              success: false,
              error: 'No session key available'
            });
            continue;
          }

          const jsonRpcId = connection.commands.nextId++;

          // Calculate MAC with this variant
          const macString = variant.secret + variant.sep1 + currentSessionKey + variant.sep2 + String(jsonRpcId) + variant.sep3 + UID;

          let mac;
          if (variant.conversion === 'hex') {
            // SHA1 → HEX string → Base64 encode of the HEX STRING (not binary!)
            // Example: SHA1 "69efaf..." → Base64("69efaf...") = "NjllZmFm..."
            const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
            mac = Buffer.from(hexHash, 'utf8').toString('base64');
          } else {
            // SHA1 direct binary → Base64
            mac = crypto.createHash('sha1').update(macString).digest('base64');
          }

          logger.info(`   MAC String: ${macString}`);
          logger.info(`   MAC: ${mac}`);

          // Build command
          const command = {
            method,
            params: {
              ...params,
              UID,
              MAC: mac,
              // NOTE: MACKey is ONLY for RemoteSetRoboTargetManagerMode, NOT for standard commands
            },
            id: jsonRpcId,
          };

          logger.info(`   📤 Sending command...`);

          // Send command
          connection.send(command);

          // Wait for response with timeout
          const result = await new Promise((resolve, reject) => {
            const timeout = setTimeout(() => {
              reject(new Error('Timeout (10s)'));
            }, 10000);

            const handleEvent = (event) => {
              if (event.UID === UID) {
                clearTimeout(timeout);
                connection.off('remoteActionResult', handleEvent);
                resolve(event);
              }
            };

            connection.on('remoteActionResult', handleEvent);
          });

          // Check if successful (ParamRet.ret === "DONE")
          const isSuccess = result.ParamRet?.ret === 'DONE';

          results.push({
            variant: variant.name,
            macString,
            mac,
            success: isSuccess,
            result: result,
            error: null
          });

          if (isSuccess) {
            logger.info(`   ✅ SUCCESS! This variant works!`);
            successfulVariant = variant;
            break; // Stop testing, we found the working formula
          } else {
            logger.info(`   ❌ FAILED: ${result.ParamRet?.ret || 'Unknown error'}`);
          }

        } catch (error) {
          logger.error(`   ❌ ERROR: ${error.message}`);
          results.push({
            variant: variant.name,
            success: false,
            error: error.message
          });
        }

        // Small delay between tests to avoid overwhelming Voyager
        await new Promise(resolve => setTimeout(resolve, 500));
      }

      // Return results
      res.json({
        success: !!successfulVariant,
        message: successfulVariant
          ? `Formule trouvée: ${successfulVariant.name}`
          : 'Aucune formule n\'a fonctionné',
        successfulVariant,
        allResults: results,
        recommendation: successfulVariant ? {
          sep1: successfulVariant.sep1,
          sep2: successfulVariant.sep2,
          sep3: successfulVariant.sep3,
          conversion: successfulVariant.conversion
        } : null
      });

    } catch (error) {
      logger.error('❌ Auto-test MAC error:', error);
      res.status(500).json({
        success: false,
        message: 'Internal server error',
        error: error.message,
      });
    }
  });

  router.post('/test-mac', async (req, res) => {
    try {
      const { method, params, macFormula } = req.body;

      if (!method || !params || !macFormula) {
        return res.status(400).json({
          success: false,
          message: 'Missing required fields: method, params, macFormula',
        });
      }

      const { sep1, sep2, sep3 } = macFormula;
      const UID = params.UID || crypto.randomUUID();

      // Get current session key
      const sessionKey = connection.sessionKey;
      if (!sessionKey) {
        return res.status(503).json({
          success: false,
          message: 'No active session. Connect to Voyager first.',
        });
      }

      // Get next JSON-RPC ID
      const jsonRpcId = connection.commands.nextId++;

      // Calculate MAC with custom formula
      const sharedSecret = connection.config.auth.sharedSecret;
      const macString = sharedSecret + sep1 + sessionKey + sep2 + String(jsonRpcId) + sep3 + UID;

      // SHA1 → HEX → Base64 (algorithme standard pour RoboTarget)
      const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
      const mac = Buffer.from(hexHash, 'utf8').toString('base64');

      logger.info('🧪 TEST MAC - Custom formula:');
      logger.info(`   Sep1: "${sep1}"`);
      logger.info(`   Sep2: "${sep2}"`);
      logger.info(`   Sep3: "${sep3}"`);
      logger.info(`   MAC String: ${macString}`);
      logger.info(`   MAC: ${mac}`);

      // Build command
      const command = {
        method,
        params: {
          ...params,
          UID,
          MAC: mac,
        },
        id: jsonRpcId,
      };

      logger.info(`📤 Sending test command: ${method}`);
      logger.info(JSON.stringify(command, null, 2));

      // Send command
      connection.send(command);

      // Wait for response (simplified - in production use proper event handling)
      const waitForResponse = () => {
        return new Promise((resolve, reject) => {
          const timeout = setTimeout(() => {
            reject(new Error('Command timeout (30s)'));
          }, 30000);

          const handleEvent = (event) => {
            if (event.UID === UID) {
              clearTimeout(timeout);
              connection.off('remoteActionResult', handleEvent);
              resolve(event);
            }
          };

          connection.on('remoteActionResult', handleEvent);
        });
      };

      try {
        const result = await waitForResponse();

        logger.info('✅ Test command result:', result);

        res.json({
          success: true,
          message: 'Command sent successfully',
          macInfo: {
            formula: `${sep1} + ${sep2} + ${sep3}`,
            string: macString,
            mac: mac,
          },
          command: command,
          result: result,
        });
      } catch (timeoutError) {
        res.json({
          success: false,
          message: 'Command sent but no response received',
          macInfo: {
            formula: `${sep1} + ${sep2} + ${sep3}`,
            string: macString,
            mac: mac,
          },
          command: command,
          error: timeoutError.message,
        });
      }

    } catch (error) {
      logger.error('❌ Test MAC error:', error);
      res.status(500).json({
        success: false,
        message: 'Internal server error',
        error: error.message,
      });
    }
  });

  /**
   * Test Open API - Simple MD5(SharedSecret + UID)
   */
  router.post('/test-open-api', async (req, res) => {
    try {
      const { uid } = req.body;

      if (!uid) {
        return res.status(400).json({
          success: false,
          message: 'Missing required field: uid',
        });
      }

      const sharedSecret = connection.config.auth.sharedSecret;

      // Open API formula: MD5(SharedSecret + UID) - NO Base64, just hex
      const macString = sharedSecret + uid;
      const mac = crypto.createHash('md5').update(macString).digest('hex');

      logger.info('🧪 TEST OPEN API:');
      logger.info(`   MAC String: ${macString}`);
      logger.info(`   MD5 (hex): ${mac}`);

      const jsonRpcId = connection.commands.nextId++;

      // Build Open API command
      const command = {
        method: 'RemoteOpenRoboTargetGetTargetList',
        params: {
          UID: uid,
          MAC: mac,
        },
        id: jsonRpcId,
      };

      logger.info(`📤 Sending Open API command...`);
      logger.info(JSON.stringify(command, null, 2));

      // Send command
      connection.send(command);

      // Wait for response
      const waitForResponse = () => {
        return new Promise((resolve, reject) => {
          const timeout = setTimeout(() => {
            reject(new Error('Command timeout (10s)'));
          }, 10000);

          const handleEvent = (event) => {
            if (event.UID === uid) {
              clearTimeout(timeout);
              connection.off('remoteActionResult', handleEvent);
              resolve(event);
            }
          };

          connection.on('remoteActionResult', handleEvent);
        });
      };

      try {
        const result = await waitForResponse();

        logger.info('✅ Open API result:', result);

        res.json({
          success: true,
          message: 'Open API command successful',
          macInfo: {
            string: macString,
            md5: mac,
          },
          command: command,
          result: result,
        });
      } catch (timeoutError) {
        res.json({
          success: false,
          message: 'Command sent but no response received',
          macInfo: {
            string: macString,
            md5: mac,
          },
          command: command,
          error: timeoutError.message,
        });
      }

    } catch (error) {
      logger.error('❌ Test Open API error:', error);
      res.status(500).json({
        success: false,
        message: 'Internal server error',
        error: error.message,
      });
    }
  });
}
