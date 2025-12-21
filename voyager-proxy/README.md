# 🔭 Stellar Voyager Proxy

Proxy server for Voyager Application Server integration with the Stellar telescope booking platform.

## 📋 Overview

This proxy server acts as a bridge between your Laravel application and the Voyager Application Server, providing:

- **TCP/IP Connection** to Voyager (JSON-RPC 2.0)
- **REST API** for Laravel integration
- **WebSocket** for real-time updates
- **RoboTarget API** for automated observations
- **Heartbeat & Reconnection** management
- **Comprehensive Logging** and metrics

## 🏗️ Architecture

```
┌──────────────┐      HTTP/WebSocket       ┌──────────────┐      JSON-RPC TCP      ┌──────────┐
│   Laravel    │ ◄──────────────────────► │  Node.js     │ ◄───────────────────► │ Voyager  │
│  Application │                           │    Proxy     │      Port 5950         │  Server  │
└──────────────┘                           └──────────────┘                         └──────────┘
```

## 🚀 Quick Start

### Prerequisites

- Node.js >= 20.0.0
- NPM >= 10.0.0
- Access to Voyager Application Server (TCP port 5950)

### Installation

```bash
# Clone or copy the proxy folder
cd voyager-proxy

# Install dependencies
npm install

# Copy environment file
cp .env.example .env

# Edit configuration
nano .env
```

### Configuration

Edit `.env` with your Voyager settings:

```env
# Server
PORT=3002
HOST=0.0.0.0

# Voyager Connection
VOYAGER_HOST=127.0.0.1
VOYAGER_PORT=5950

# Authentication (required for RoboTarget Manager Mode)
VOYAGER_AUTH_ENABLED=true
VOYAGER_USERNAME=admin
VOYAGER_PASSWORD=your_password

# RoboTarget NDA Authentication (required for RoboTarget API)
VOYAGER_SHARED_SECRET=your_secret         # Must match "Secret" field in Voyager COMMON tab
VOYAGER_AUTH_BASE=base64_credentials      # Base64 of "username:password"
VOYAGER_MAC_KEY=your_mac_key
VOYAGER_MAC_WORD1=word1
VOYAGER_MAC_WORD2=word2
VOYAGER_MAC_WORD3=word3
VOYAGER_MAC_WORD4=word4
VOYAGER_LICENSE_NUMBER=your_license

# API Security
API_KEY=your_secret_api_key

# CORS (your Laravel domain)
CORS_ORIGIN=http://localhost,https://stellar.test
```

### 🤖 RoboTarget NDA Authentication

**IMPORTANT**: To use RoboTarget API, you must:

1. **Configure the Shared Secret** in Voyager's COMMON tab
2. **Set the same value** in `VOYAGER_SHARED_SECRET` in `.env`
3. **Configure MAC Words** (WORD1-4) in both Voyager and `.env`
4. **Restart Voyager** after changing the COMMON tab configuration
5. **Use the correct hash algorithm** (Section 6.a of NDA protocol)

**Hash Algorithm (CRITICAL):**

The hash for `RemoteSetRoboTargetManagerMode` must be calculated as follows:

```javascript
// CORRECT (Section 6.a of NDA protocol):
// 1. SHA1 → convert to hexadecimal string (40 lowercase chars)
// 2. Base64 encode the hex string (NOT the raw bytes!)
const sha1Hex = crypto.createHash('sha1').update(hashString).digest('hex');
const hash = Buffer.from(sha1Hex).toString('base64');

// INCORRECT (old version):
const hash = crypto.createHash('sha1').update(hashString).digest('base64');
```

**Formula**: `SHA1(SharedSecret ||:|| SessionKey ||:|| MAC1+MAC2+MAC3+MAC4) → Hex → Base64`

Where:
- `SharedSecret` = value from `VOYAGER_SHARED_SECRET`
- `SessionKey` = Timestamp from Voyager's Version event
- Separator = `||:||` (pipe pipe colon pipe pipe)
- MAC1+MAC2+MAC3+MAC4 = concatenated without separator

For detailed documentation, see `CONNEXION-ROBOTARGET.md`.

### Development

```bash
# Start in development mode (with auto-reload)
npm run dev
```

### Production

```bash
# Start in production mode
npm start

# Or with PM2 (recommended)
npm install -g pm2
pm2 start ecosystem.config.js
pm2 save
pm2 startup
```

## 🐳 Docker Deployment

### Using Docker Compose (Recommended for Cloud)

```bash
# Build and start
docker-compose up -d

# View logs
docker-compose logs -f

# Stop
docker-compose down
```

### Using Dockerfile

```bash
# Build image
docker build -t stellar-voyager-proxy .

# Run container
docker run -d \
  --name voyager-proxy \
  -p 3002:3002 \
  --env-file .env \
  stellar-voyager-proxy
```

## ☁️ Cloud Deployment

### Deploying to Cloud Server

1. **Upload files to server:**
```bash
rsync -avz --exclude node_modules voyager-proxy/ user@your-server:/opt/stellar-voyager-proxy/
```

2. **SSH to server and install:**
```bash
ssh user@your-server
cd /opt/stellar-voyager-proxy
npm ci --production
```

3. **Configure environment:**
```bash
cp .env.example .env
nano .env
```

4. **Start with PM2:**
```bash
npm install -g pm2
pm2 start ecosystem.config.js
pm2 save
pm2 startup  # Follow instructions to enable auto-start
```

5. **Setup reverse proxy (Nginx):**

```nginx
# /etc/nginx/sites-available/voyager-proxy

server {
    listen 80;
    server_name proxy.yourdomain.com;

    location / {
        proxy_pass http://localhost:3002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    }

    # WebSocket support
    location /socket.io/ {
        proxy_pass http://localhost:3002/socket.io/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
    }
}
```

6. **Enable SSL with Certbot:**
```bash
sudo certbot --nginx -d proxy.yourdomain.com
```

### Systemd Service (Alternative to PM2)

Create `/etc/systemd/system/voyager-proxy.service`:

```ini
[Unit]
Description=Stellar Voyager Proxy
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/opt/stellar-voyager-proxy
ExecStart=/usr/bin/node src/index.js
Restart=always
RestartSec=10
StandardOutput=syslog
StandardError=syslog
SyslogIdentifier=voyager-proxy
Environment=NODE_ENV=production

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable voyager-proxy
sudo systemctl start voyager-proxy
sudo systemctl status voyager-proxy
```

## 📡 API Documentation

### Authentication

All API requests (except `/health`) require an API key:

```bash
curl -H "X-API-Key: your_secret_key" http://localhost:3002/api/dashboard/state
```

### Endpoints

#### Health Check
```http
GET /health
```

#### Dashboard State
```http
GET /api/dashboard/state
```

Returns current Voyager state (updated every 2s).

#### Connection Status
```http
GET /api/status/connection
```

#### Control - Abort
```http
POST /api/control/abort
```

#### Control - Toggle Target
```http
POST /api/control/toggle
Content-Type: application/json

{
  "targetGuid": "uuid-here",
  "activate": true
}
```

#### RoboTarget - Create Set
```http
POST /api/robotarget/sets
Content-Type: application/json

{
  "Guid": "uuid",
  "Name": "My Observation Set",
  "ProfileName": "Default.v2y",
  "Status": 0
}
```

#### RoboTarget - Create Target
```http
POST /api/robotarget/targets
Content-Type: application/json

{
  "GuidTarget": "uuid",
  "RefGuidSet": "set-uuid",
  "TargetName": "M31",
  "RAJ2000": "00:42:44",
  "DECJ2000": "+41:16:09"
}
```

See `src/api/routes.js` for complete API documentation.

## 🔌 WebSocket Events

### Client → Server

```javascript
socket.emit('subscribe', 'roomName');
socket.emit('getState');
socket.emit('command', {
  id: 'unique-id',
  method: 'RemoteAbortAction',
  params: {}
});
```

### Server → Client

```javascript
// Real-time events
socket.on('controlData', (data) => { /* Dashboard state */ });
socket.on('newJPG', (data) => { /* Camera preview */ });
socket.on('shotRunning', (data) => { /* Exposure progress */ });
socket.on('signal', (data) => { /* Status changes */ });
socket.on('newFITReady', (data) => { /* New image captured */ });
socket.on('remoteActionResult', (data) => { /* Command result */ });
socket.on('connectionState', (data) => { /* Connection status */ });
```

## 🔍 Monitoring

### PM2 Monitoring
```bash
pm2 monit
pm2 logs voyager-proxy
pm2 status
```

### Logs Location
- Application logs: `./logs/application-YYYY-MM-DD.log`
- Error logs: `./logs/error-YYYY-MM-DD.log`
- PM2 logs: `./logs/pm2-*.log`

### Metrics Endpoint
```bash
# Metrics are logged periodically (default: every 60s)
# Check logs for "=== METRICS REPORT ==="
tail -f logs/application-*.log | grep METRICS
```

## 🛠️ Troubleshooting

### Connection Issues

**Problem**: Cannot connect to Voyager

```bash
# Check if Voyager is accessible
telnet 192.168.1.100 5950

# Check firewall
sudo ufw status
sudo ufw allow 5950/tcp

# Check proxy logs
pm2 logs voyager-proxy
```

**Problem**: Authentication fails

- Verify credentials in `.env`
- Check Voyager authentication settings
- Ensure Base64 encoding is correct

### API Issues

**Problem**: 401 Unauthorized

- Verify API_KEY in `.env`
- Include `X-API-Key` header in requests

**Problem**: CORS errors

- Update `CORS_ORIGIN` in `.env`
- Restart proxy after changes

### Performance Issues

**Problem**: High memory usage

```bash
# Check memory
pm2 describe voyager-proxy

# Adjust memory limit in ecosystem.config.js
max_memory_restart: '500M'

# Restart
pm2 restart voyager-proxy
```

## 🔧 Development

### Project Structure

```
voyager-proxy/
├── src/
│   ├── index.js                 # Entry point
│   ├── voyager/
│   │   ├── connection.js        # TCP connection manager
│   │   ├── events.js            # Event handlers
│   │   ├── auth.js              # Authentication
│   │   └── commands.js          # RPC commands
│   ├── api/
│   │   ├── server.js            # Express server
│   │   ├── routes.js            # API routes
│   │   └── middleware.js        # Auth middleware
│   ├── websocket/
│   │   └── server.js            # Socket.IO server
│   └── utils/
│       ├── logger.js            # Winston logger
│       └── metrics.js           # Metrics collector
├── logs/                        # Log files
├── package.json
├── .env
├── ecosystem.config.js          # PM2 config
├── Dockerfile
└── docker-compose.yml
```

### Adding New Commands

Edit `src/voyager/commands.js`:

```javascript
async myNewCommand(param1, param2) {
  return this.send('VoyagerMethodName', {
    Param1: param1,
    Param2: param2,
  });
}
```

Add route in `src/api/routes.js`:

```javascript
router.post('/my-endpoint', async (req, res, next) => {
  try {
    const result = await req.voyager.commands.myNewCommand(
      req.body.param1,
      req.body.param2
    );
    res.json({ success: true, result });
  } catch (error) {
    next(error);
  }
});
```

## 📚 Resources

- [Voyager Application Server Documentation](https://www.starkeeper.it)
- [JSON-RPC 2.0 Specification](https://www.jsonrpc.org/specification)
- [Socket.IO Documentation](https://socket.io/docs/)
- [PM2 Documentation](https://pm2.keymetrics.io/)

## 📄 License

MIT License - See LICENSE file

## 🤝 Support

For issues or questions:
- Check logs: `pm2 logs voyager-proxy`
- Review this README
- Contact: support@yourdomain.com

---

**Made with ❤️ for the Stellar Platform**
