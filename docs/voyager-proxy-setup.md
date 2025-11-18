# 🚀 Voyager Proxy - Guide de mise en place

> Guide complet pour déployer le proxy Voyager sur votre serveur cloud managé

**Date**: 18 novembre 2024
**Version**: 1.0.0

---

## ✅ Ce qui a été créé

Le projet **voyager-proxy** est maintenant complet avec :

### 📁 Structure du projet

```
voyager-proxy/
├── src/
│   ├── index.js                    # Point d'entrée
│   ├── voyager/
│   │   ├── connection.js           # Connexion TCP/IP à Voyager
│   │   ├── events.js               # Gestionnaire d'événements
│   │   ├── auth.js                 # Authentification
│   │   └── commands.js             # Commandes RPC (RoboTarget, Control, etc.)
│   ├── api/
│   │   ├── server.js               # Serveur Express
│   │   ├── routes.js               # Routes API REST
│   │   └── middleware.js           # Authentification API
│   ├── websocket/
│   │   └── server.js               # Serveur Socket.IO (temps réel)
│   └── utils/
│       ├── logger.js               # Logs Winston
│       └── metrics.js              # Métriques
├── config/                         # (créé automatiquement)
├── logs/                           # (créé automatiquement)
├── package.json                    # Dépendances Node.js
├── .env.example                    # Template configuration
├── .gitignore                      # Fichiers à ignorer
├── Dockerfile                      # Image Docker
├── docker-compose.yml              # Orchestration Docker
├── ecosystem.config.js             # Configuration PM2
└── README.md                       # Documentation complète
```

### 🎯 Fonctionnalités implémentées

#### ✅ Connexion Voyager
- Connexion TCP/IP persistante (port 5950)
- Heartbeat automatique (5s)
- Reconnexion automatique avec retry
- Timeout et gestion des erreurs
- Support multi-instances Voyager

#### ✅ Authentification
- Authentification Base64
- Timeout 5s après connexion
- Validation credentials

#### ✅ Événements en temps réel
- `Version` - Infos serveur
- `Polling` - Heartbeat
- `ControlData` - État système (toutes les 2s)
- `Signal` - Changements d'état
- `NewFITReady` - Nouvelles images FITS
- `NewJPGReady` - Aperçus Base64
- `ShotRunning` - Progression exposition
- `RemoteActionResult` - Résultats commandes
- `ShutDown` - Arrêt Voyager

#### ✅ API REST complète
- Dashboard state
- Connection status
- Control (abort, toggle target)
- RoboTarget (sets, targets, shots)
- Telescope control (park, tracking)
- Camera control (cooling, shots)
- Utilities (autofocus, platesolve)

#### ✅ WebSocket temps réel
- Broadcasting événements Voyager
- Rooms pour multi-utilisateurs
- Ping/pong keepalive
- Commandes depuis clients
- Gestion déconnexions

#### ✅ Sécurité
- API Key authentication
- CORS configuré
- Rate limiting
- Helmet (security headers)
- Validation entrées

#### ✅ Monitoring
- Logs Winston (rotation quotidienne)
- Métriques collectées
- Rapport périodique
- Health check endpoint

#### ✅ Déploiement
- Docker / Docker Compose
- PM2 avec auto-restart
- Systemd service
- Nginx reverse proxy
- SSL/HTTPS ready

---

## 🚀 Installation sur votre serveur cloud

### Étape 1 : Prérequis serveur

```bash
# SSH vers votre serveur
ssh user@your-server.com

# Installer Node.js 20 LTS
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt-get install -y nodejs

# Vérifier versions
node --version   # v20.x.x
npm --version    # v10.x.x

# Installer PM2 globalement
sudo npm install -g pm2
```

### Étape 2 : Upload du projet

Depuis votre machine locale :

```bash
# Depuis le dossier Stellar
cd /Users/w/Herd/Stellar

# Upload vers serveur
rsync -avz --exclude 'node_modules' --exclude 'logs' \
  voyager-proxy/ user@your-server:/opt/stellar-voyager-proxy/
```

### Étape 3 : Configuration sur le serveur

```bash
# SSH vers serveur
ssh user@your-server

# Aller dans le dossier
cd /opt/stellar-voyager-proxy

# Installer les dépendances
npm ci --production

# Créer fichier .env
cp .env.example .env
nano .env
```

**Configuration `.env` minimale :**

```env
# Environnement
NODE_ENV=production
PORT=3000
HOST=0.0.0.0

# Voyager (REMPLACEZ PAR VOS VRAIES VALEURS)
VOYAGER_HOST=192.168.1.100          # IP de votre serveur Voyager
VOYAGER_PORT=5950
VOYAGER_INSTANCE=1

# Authentification Voyager
VOYAGER_AUTH_ENABLED=true
VOYAGER_USERNAME=votre_username     # Votre username Voyager
VOYAGER_PASSWORD=votre_password     # Votre password Voyager

# API Security
API_KEY=genere_une_cle_secrete_ici  # Générez une clé aléatoire forte

# CORS (votre domaine Laravel)
CORS_ORIGIN=https://votredomaine.com
WS_CORS_ORIGIN=https://votredomaine.com

# Logs
LOG_LEVEL=info
```

**Générer une API Key sécurisée :**

```bash
node -e "console.log(require('crypto').randomBytes(32).toString('hex'))"
```

### Étape 4 : Démarrage avec PM2

```bash
# Créer dossier logs
mkdir -p logs

# Démarrer avec PM2
pm2 start ecosystem.config.js

# Vérifier que ça tourne
pm2 status

# Voir les logs
pm2 logs stellar-voyager-proxy

# Sauvegarder la configuration PM2
pm2 save

# Auto-démarrage au boot du serveur
pm2 startup
# Suivre les instructions affichées (copier-coller la commande)
```

**Vérification :**

```bash
# Test health check
curl http://localhost:3000/health

# Devrait retourner :
# {
#   "status": "ok",
#   "timestamp": "...",
#   "uptime": ...,
#   "voyager": {
#     "connected": true,
#     "authenticated": true
#   }
# }
```

### Étape 5 : Nginx Reverse Proxy

**Installation Nginx (si pas déjà fait) :**

```bash
sudo apt update
sudo apt install nginx
```

**Configuration :**

```bash
# Créer configuration
sudo nano /etc/nginx/sites-available/voyager-proxy
```

**Contenu `/etc/nginx/sites-available/voyager-proxy` :**

```nginx
server {
    listen 80;
    server_name proxy.votredomaine.com;  # CHANGEZ PAR VOTRE SOUS-DOMAINE

    # Logs
    access_log /var/log/nginx/voyager-proxy-access.log;
    error_log /var/log/nginx/voyager-proxy-error.log;

    # API Routes
    location / {
        proxy_pass http://localhost:3000;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection 'upgrade';
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        # Timeouts (important pour long polling)
        proxy_connect_timeout 60s;
        proxy_send_timeout 60s;
        proxy_read_timeout 60s;
    }

    # WebSocket Support
    location /socket.io/ {
        proxy_pass http://localhost:3000/socket.io/;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_cache_bypass $http_upgrade;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;

        # WebSocket timeouts
        proxy_connect_timeout 7d;
        proxy_send_timeout 7d;
        proxy_read_timeout 7d;
    }
}
```

**Activer la configuration :**

```bash
# Créer lien symbolique
sudo ln -s /etc/nginx/sites-available/voyager-proxy /etc/nginx/sites-enabled/

# Tester config
sudo nginx -t

# Recharger Nginx
sudo systemctl reload nginx
```

### Étape 6 : SSL avec Certbot

```bash
# Installer Certbot
sudo apt install certbot python3-certbot-nginx

# Obtenir certificat SSL (remplacez par votre domaine)
sudo certbot --nginx -d proxy.votredomaine.com

# Suivre les instructions
# Choisir : Redirect HTTP to HTTPS (option 2)

# Renouvellement automatique (vérifier)
sudo certbot renew --dry-run
```

**Après SSL, votre proxy sera accessible à :**
- `https://proxy.votredomaine.com/health`
- `https://proxy.votredomaine.com/api/dashboard/state`
- `wss://proxy.votredomaine.com` (WebSocket)

### Étape 7 : Firewall

```bash
# Autoriser HTTP/HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp

# Si Voyager est sur le même serveur, ouvrir port 5950 en local uniquement
# (pas besoin si Voyager est sur un autre serveur du même réseau)

# Vérifier règles
sudo ufw status
```

### Étape 8 : Vérification finale

```bash
# Test depuis votre machine locale
curl https://proxy.votredomaine.com/health

# Test avec API Key
curl -H "X-API-Key: votre_api_key" \
  https://proxy.votredomaine.com/api/status/connection

# Logs PM2
pm2 logs stellar-voyager-proxy --lines 50
```

---

## 🔗 Intégration avec Laravel

### Étape 1 : Mettre à jour `.env` Laravel

```env
# Proxy Voyager
VOYAGER_PROXY_URL=https://proxy.votredomaine.com
VOYAGER_PROXY_API_KEY=votre_api_key_generee

# WebSocket (pour Laravel Echo)
PUSHER_APP_ID=
PUSHER_APP_KEY=
PUSHER_APP_SECRET=
PUSHER_APP_CLUSTER=
BROADCAST_DRIVER=redis
```

### Étape 2 : Modifier VoyagerService Laravel

Fichier `app/Services/VoyagerService.php` :

```php
<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VoyagerService
{
    private string $proxyUrl;
    private string $apiKey;

    public function __construct()
    {
        $this->proxyUrl = config('services.voyager.proxy_url');
        $this->apiKey = config('services.voyager.proxy_api_key');
    }

    private function request(string $method, string $endpoint, array $data = [])
    {
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $this->apiKey,
                'Accept' => 'application/json',
            ])->timeout(30);

            if ($method === 'GET') {
                $response = $response->get($this->proxyUrl . $endpoint, $data);
            } else {
                $response = $response->post($this->proxyUrl . $endpoint, $data);
            }

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('Voyager proxy error', [
                'endpoint' => $endpoint,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Voyager proxy exception', [
                'endpoint' => $endpoint,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function getControlOverview()
    {
        return $this->request('GET', '/api/dashboard/state');
    }

    public function abortTarget()
    {
        return $this->request('POST', '/api/control/abort');
    }

    public function toggleObject(string $targetGuid, bool $activate)
    {
        return $this->request('POST', '/api/control/toggle', [
            'targetGuid' => $targetGuid,
            'activate' => $activate,
        ]);
    }

    public function getCameraPreview()
    {
        return $this->request('GET', '/api/camera/preview');
    }

    // RoboTarget methods
    public function addSet(array $data)
    {
        return $this->request('POST', '/api/robotarget/sets', $data);
    }

    public function addTarget(array $data)
    {
        return $this->request('POST', '/api/robotarget/targets', $data);
    }

    public function addShot(array $data)
    {
        return $this->request('POST', '/api/robotarget/shots', $data);
    }

    public function activateTarget(string $guid)
    {
        return $this->request('POST', "/api/robotarget/targets/{$guid}/activate");
    }

    public function deactivateTarget(string $guid)
    {
        return $this->request('POST', "/api/robotarget/targets/{$guid}/deactivate");
    }
}
```

### Étape 3 : Ajouter config dans `config/services.php`

```php
'voyager' => [
    'proxy_url' => env('VOYAGER_PROXY_URL', 'http://localhost:3000'),
    'proxy_api_key' => env('VOYAGER_PROXY_API_KEY'),
    'profile' => env('VOYAGER_PROFILE', 'Default.v2y'),
    'default_sequence_guid' => env('VOYAGER_DEFAULT_SEQUENCE_GUID'),
    'webcam_url' => env('VOYAGER_WEBCAM_URL'),
],
```

### Étape 4 : Tester depuis Laravel

```bash
php artisan tinker
```

```php
$voyager = app(\App\Services\VoyagerService::class);

// Test connexion
$status = $voyager->getControlOverview();
dd($status);

// Test abort
$result = $voyager->abortTarget();
dd($result);
```

---

## 📊 Monitoring et Maintenance

### Commandes PM2 utiles

```bash
# Voir status
pm2 status

# Logs en temps réel
pm2 logs stellar-voyager-proxy

# Redémarrer
pm2 restart stellar-voyager-proxy

# Arrêter
pm2 stop stellar-voyager-proxy

# Monitoring interactif
pm2 monit

# Infos détaillées
pm2 describe stellar-voyager-proxy

# Vider les logs
pm2 flush
```

### Vérifier les logs applicatifs

```bash
cd /opt/stellar-voyager-proxy

# Logs d'application
tail -f logs/application-$(date +%Y-%m-%d).log

# Logs d'erreurs uniquement
tail -f logs/error-$(date +%Y-%m-%d).log

# Chercher les erreurs
grep ERROR logs/application-*.log

# Métriques
grep "METRICS REPORT" logs/application-*.log | tail -20
```

### Vérifier santé du serveur

```bash
# CPU et mémoire
pm2 describe stellar-voyager-proxy | grep -A 5 "Monit"

# Connexion à Voyager
curl -H "X-API-Key: votre_key" \
  https://proxy.votredomaine.com/api/status/connection | jq

# Health check
curl https://proxy.votredomaine.com/health | jq
```

### Mise à jour du proxy

```bash
# Depuis votre machine locale
cd /Users/w/Herd/Stellar
rsync -avz --exclude 'node_modules' --exclude 'logs' \
  voyager-proxy/ user@your-server:/opt/stellar-voyager-proxy/

# Sur le serveur
ssh user@your-server
cd /opt/stellar-voyager-proxy
npm ci --production
pm2 restart stellar-voyager-proxy
```

---

## 🐛 Troubleshooting

### Proxy ne démarre pas

```bash
# Vérifier logs PM2
pm2 logs --err

# Vérifier port disponible
sudo netstat -tulpn | grep 3000

# Tester manuellement
NODE_ENV=production node src/index.js
```

### Impossible de se connecter à Voyager

```bash
# Ping vers Voyager
ping 192.168.1.100

# Telnet vers port
telnet 192.168.1.100 5950

# Vérifier credentials dans .env
cat .env | grep VOYAGER

# Logs de connexion
pm2 logs | grep -i "connect\|auth"
```

### Erreurs 401 Unauthorized depuis Laravel

```bash
# Vérifier API Key correspond entre :
# - .env du proxy (API_KEY=...)
# - .env Laravel (VOYAGER_PROXY_API_KEY=...)

# Tester avec curl
curl -H "X-API-Key: la_vraie_key" \
  https://proxy.votredomaine.com/api/status/connection
```

### WebSocket ne fonctionne pas

```bash
# Vérifier config Nginx
sudo nginx -t
sudo tail -f /var/log/nginx/voyager-proxy-error.log

# Vérifier CORS dans .env du proxy
WS_CORS_ORIGIN=https://votredomaine.com

# Redémarrer tout
pm2 restart stellar-voyager-proxy
sudo systemctl reload nginx
```

---

## 📞 Support

Pour toute question ou problème :

1. **Vérifier logs** : `pm2 logs stellar-voyager-proxy`
2. **Consulter** : `/opt/stellar-voyager-proxy/README.md`
3. **Tester santé** : `curl https://proxy.votredomaine.com/health`

---

## ✅ Checklist de déploiement

- [ ] Node.js 20 LTS installé
- [ ] PM2 installé globalement
- [ ] Projet uploadé sur serveur
- [ ] `.env` configuré avec vraies valeurs
- [ ] `npm ci --production` exécuté
- [ ] PM2 démarré et sauvegardé
- [ ] PM2 startup configuré
- [ ] Nginx installé et configuré
- [ ] SSL Certbot configuré
- [ ] Firewall configuré (80, 443)
- [ ] Health check fonctionne
- [ ] Laravel `.env` mis à jour
- [ ] VoyagerService mis à jour
- [ ] Tests connexion depuis Laravel OK

---

**Le proxy Voyager est prêt pour la production ! 🚀**
