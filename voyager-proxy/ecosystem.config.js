// PM2 configuration for production deployment
// Usage: pm2 start ecosystem.config.js

module.exports = {
  apps: [
    {
      name: 'stellar-voyager-proxy',
      script: './src/index.js',
      instances: 1, // Single instance (stateful connection to Voyager)
      exec_mode: 'fork',
      node_args: '--max-old-space-size=512',

      // Environment
      env: {
        NODE_ENV: 'production',
      },

      // Restart policy
      autorestart: true,
      max_restarts: 10,
      min_uptime: '10s',
      restart_delay: 5000,

      // Logs
      error_file: './logs/pm2-error.log',
      out_file: './logs/pm2-out.log',
      log_date_format: 'YYYY-MM-DD HH:mm:ss Z',
      merge_logs: true,

      // Performance
      max_memory_restart: '500M',

      // Monitoring
      listen_timeout: 10000,
      kill_timeout: 5000,

      // Advanced
      wait_ready: true,
      shutdown_with_message: true,
    },
  ],
};
