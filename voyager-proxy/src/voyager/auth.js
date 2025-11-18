import { v4 as uuidv4 } from 'uuid';
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

    const { username, password } = this.connection.config.auth;

    if (!username || !password) {
      throw new Error('Username and password required for authentication');
    }

    logger.info(`Authenticating as ${username}...`);

    // Create Base64 encoded credentials
    const credentials = `${username}:${password}`;
    const base64Credentials = Buffer.from(credentials).toString('base64');

    // Send authentication command
    const authCommand = {
      method: 'AuthenticateUserBase',
      params: {
        UID: uuidv4(),
        Base: base64Credentials,
      },
      id: 1,
    };

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

                if (response.authbase) {
                  logger.info(`✅ Authenticated as ${response.authbase.Username}`);
                  logger.info(`Permissions: ${response.authbase.Permissions}`);
                  this.connection.socket.removeListener('data', onData);
                  resolve(response.authbase);
                } else if (response.error) {
                  logger.error('Authentication failed:', response.error);
                  this.connection.socket.removeListener('data', onData);
                  reject(new Error(response.error.message || 'Authentication failed'));
                }
              }
            } catch (error) {
              // Ignore parsing errors for other messages
            }
          }
        }
      };

      this.connection.socket.on('data', onData);

      // Send authentication command
      this.connection.send(authCommand);
    });
  }
}

export default Authentication;
