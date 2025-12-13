#!/usr/bin/env node
/**
 * Script de test de connexion Voyager + RoboTarget
 * Conforme à la documentation: docs/doc_voyager/connexion_et_maintien.md
 *
 * Usage: node test-connection.js
 *
 * Ce script teste (dans l'ordre strict) :
 * 1. Connexion TCP au serveur Voyager
 * 2. Réception de l'événement Version (capture SessionKey)
 * 3. Authentification MAC (< 5 secondes après Version)
 * 4. Activation du mode Dashboard (flux JPG/ControlData)
 * 5. Activation du mode RoboTarget Manager
 * 6. Heartbeat (Polling) toutes les 5 secondes
 */

import 'dotenv/config';
import net from 'net';
import crypto from 'crypto';
import { v4 as uuidv4 } from 'uuid';

const HOST = process.env.VOYAGER_HOST || '185.228.120.120';
const PORT = parseInt(process.env.VOYAGER_PORT) || 23002;
const AUTH_BASE = process.env.VOYAGER_AUTH_BASE;
const MAC_KEY = process.env.VOYAGER_MAC_KEY;
const MAC_WORD1 = process.env.VOYAGER_MAC_WORD1;
const MAC_WORD2 = process.env.VOYAGER_MAC_WORD2;
const MAC_WORD3 = process.env.VOYAGER_MAC_WORD3;
const MAC_WORD4 = process.env.VOYAGER_MAC_WORD4;
const LICENSE_NUMBER = process.env.VOYAGER_LICENSE_NUMBER;
const USERNAME = process.env.VOYAGER_USERNAME;
const PASSWORD = process.env.VOYAGER_PASSWORD;
const AUTH_ENABLED = process.env.VOYAGER_AUTH_ENABLED === 'true';

console.log('🔭 Test de connexion Voyager + RoboTarget (Documentation Complète)');
console.log('====================================================================\n');
console.log(`📡 Serveur: ${HOST}:${PORT}`);
console.log(`🔑 MAC Key: ${MAC_KEY}`);
console.log(`🔐 Auth: ${AUTH_ENABLED ? 'ACTIVÉE' : 'DÉSACTIVÉE'}\n`);

let sessionKey = null;
let buffer = '';
let isAuthenticated = false;
let isDashboardModeActive = false;
let isRoboTargetModeActive = false;
let heartbeatInterval = null;

const socket = new net.Socket();
socket.setEncoding('utf8');

socket.connect(PORT, HOST, () => {
  console.log('✅ Connexion TCP établie');
  console.log('⏳ En attente de l\'événement Version...\n');
});

socket.on('data', async (data) => {
  buffer += data;
  console.log(`📥 Données reçues (${data.length} octets)`);

  const lines = buffer.split('\r\n');
  buffer = lines.pop() || '';

  for (const line of lines) {
    if (line.trim()) {
      try {
        const message = JSON.parse(line);
        console.log(`\n📨 Message reçu: ${message.Event || message.method || 'unknown'}`);
        if (process.env.DEBUG === 'true') {
          console.log(JSON.stringify(message, null, 2));
        }

        // ÉTAPE 1: Événement Version (capture SessionKey)
        if (message.Event === 'Version' && !sessionKey) {
          sessionKey = message.Timestamp;
          console.log(`\n✅ ÉTAPE 1: Événement Version reçu!`);
          console.log(`   Version Voyager: ${message.VOYVersion}`);
          console.log(`   SessionKey (Timestamp): ${sessionKey}`);
          console.log(`   ⏱️  Délai max pour auth: 5 secondes\n`);

          // ÉTAPE 2: Authentification (< 5 secondes)
          if (AUTH_ENABLED) {
            await authenticate();
          } else {
            console.log(`⚠️  ÉTAPE 2: Authentification DÉSACTIVÉE (mode test)\n`);
            isAuthenticated = true;
            // Passer directement aux modes
            await activateDashboardMode();
          }
        }

        // Réponse AuthenticateUserBase
        if (message.id === 1 && message.authbase) {
          console.log(`\n✅ ÉTAPE 2: Authentification réussie!`);
          console.log(`   Utilisateur: ${message.authbase.Username || 'N/A'}`);
          console.log(`   Permissions: ${message.authbase.Permissions || 'N/A'}\n`);
          isAuthenticated = true;

          // ÉTAPE 3: Activer Dashboard Mode
          await activateDashboardMode();
        }

        // Réponse RemoteAuthenticationRequest (MAC)
        if (message.id === 1 && message.authresponse) {
          console.log(`\n✅ ÉTAPE 2: Authentification MAC réussie!`);
          console.log(`   Username: ${message.authresponse.Username || 'N/A'}\n`);
          isAuthenticated = true;

          // ÉTAPE 3: Activer Dashboard Mode
          await activateDashboardMode();
        }

        // RemoteActionResult (pour Dashboard et RoboTarget)
        if (message.Event === 'RemoteActionResult') {
          console.log(`\n📬 RemoteActionResult reçu:`);
          console.log(`   UID: ${message.UID}`);
          console.log(`   ParamRet.ret: ${message.ParamRet?.ret || 'N/A'}`);

          if (message.ParamRet?.ret === 'DONE') {
            if (!isDashboardModeActive) {
              console.log(`\n✅ ÉTAPE 3: Mode Dashboard ACTIVÉ!`);
              console.log(`   ➡️  Flux JPG/ControlData disponible\n`);
              isDashboardModeActive = true;

              // ÉTAPE 4: Activer RoboTarget Manager Mode
              await activateRoboTargetManagerMode();
            } else if (!isRoboTargetModeActive) {
              console.log(`\n✅ ÉTAPE 4: Mode RoboTarget Manager ACTIVÉ!`);
              console.log(`   ➡️  API RoboTarget disponible\n`);
              isRoboTargetModeActive = true;

              // ÉTAPE 5: Démarrer le Heartbeat
              startHeartbeat();

              console.log(`\n✅ ✅ ✅ SUCCÈS COMPLET! ✅ ✅ ✅`);
              console.log(`🎯 Tous les modes sont actifs!`);
              console.log(`💚 La connexion fonctionne parfaitement!`);
              console.log(`\n📊 Heartbeat actif - Le script restera connecté 30s pour tester...\n`);

              // Laisser tourner 30 secondes pour tester le heartbeat
              setTimeout(() => {
                console.log(`\n✅ Test terminé avec succès!\n`);
                stopHeartbeat();
                socket.destroy();
                process.exit(0);
              }, 30000);
            }
          } else {
            console.log(`\n❌ Échec: ${message.ParamRet?.ret || 'unknown'}`);
            process.exit(1);
          }
        }

        // Événement Polling (réponse du serveur)
        if (message.Event === 'Polling') {
          console.log(`💓 Heartbeat reçu du serveur`);
        }

        // Événement ControlData (si Dashboard mode actif)
        if (message.Event === 'ControlData') {
          console.log(`📊 ControlData reçu (status détaillé)`);
        }

      } catch (error) {
        console.error(`⚠️ Erreur de parsing: ${error.message}`);
        if (process.env.DEBUG === 'true') {
          console.error(`   Ligne brute: ${line}`);
        }
      }
    }
  }
});

socket.on('error', (error) => {
  console.error(`\n❌ Erreur socket: ${error.message}`);
  stopHeartbeat();
  process.exit(1);
});

socket.on('close', () => {
  console.log(`\n⚠️ Connexion fermée par le serveur`);
  stopHeartbeat();

  if (!sessionKey) {
    console.log(`\n❌ Échec: Aucun événement Version reçu`);
    console.log(`\n💡 Vérifications à faire:`);
    console.log(`   1. Voyager est-il en cours d'exécution?`);
    console.log(`   2. Le port ${PORT} est-il le bon?`);
    console.log(`   3. Y a-t-il un firewall?`);
  } else if (!isRoboTargetModeActive) {
    console.log(`\n❌ Échec: Connexion fermée avant activation complète`);
    console.log(`\n📊 État de la connexion:`);
    console.log(`   SessionKey reçu: ${sessionKey ? '✅' : '❌'}`);
    console.log(`   Authentifié: ${isAuthenticated ? '✅' : '❌'}`);
    console.log(`   Dashboard Mode: ${isDashboardModeActive ? '✅' : '❌'}`);
    console.log(`   RoboTarget Mode: ${isRoboTargetModeActive ? '✅' : '❌'}`);
  }

  process.exit(1);
});

/**
 * ÉTAPE 2: Authentification (< 5 secondes après Version)
 * Deux méthodes possibles:
 * - AuthenticateUserBase: user:password encodé en Base64
 * - RemoteAuthenticationRequest: MAC authentication
 */
async function authenticate() {
  // Priorité à l'authentification MAC si AUTH_BASE est défini
  if (AUTH_BASE && MAC_KEY) {
    console.log(`🔐 ÉTAPE 2: Authentification MAC...`);
    return authenticateMAC();
  }

  // Sinon, authentification standard Base64
  if (USERNAME && PASSWORD) {
    console.log(`🔐 ÉTAPE 2: Authentification Base (user:password)...`);
    return authenticateBase();
  }

  throw new Error('Aucune méthode d\'authentification disponible');
}

async function authenticateBase() {
  const credentials = `${USERNAME}:${PASSWORD}`;
  const base64Credentials = Buffer.from(credentials).toString('base64');

  const command = {
    method: 'AuthenticateUserBase',
    params: {
      UID: uuidv4(),
      Base: base64Credentials,
    },
    id: 1,
  };

  console.log(`📤 Envoi AuthenticateUserBase...`);
  socket.write(JSON.stringify(command) + '\r\n');
  console.log(`⏳ En attente de la réponse...\n`);
}

async function authenticateMAC() {
  const uid = uuidv4();

  // Calcul du MAC
  const authString = `${AUTH_BASE}${MAC_KEY}${MAC_WORD1 || ''}${MAC_WORD2 || ''}${MAC_WORD3 || ''}${MAC_WORD4 || ''}`;
  const mac = crypto.createHash('md5').update(authString).digest('hex');

  const command = {
    method: 'RemoteAuthenticationRequest',
    params: {
      UID: uid,
      AuthBase: parseInt(AUTH_BASE),
      MacKey: MAC_KEY,
      Word1: MAC_WORD1 || '',
      Word2: MAC_WORD2 || '',
      Word3: MAC_WORD3 || '',
      Word4: MAC_WORD4 || '',
      LicenseNumber: LICENSE_NUMBER || '',
      MAC: mac,
    },
    id: 1,
  };

  console.log(`📤 Envoi RemoteAuthenticationRequest...`);
  socket.write(JSON.stringify(command) + '\r\n');
  console.log(`⏳ En attente de la réponse...\n`);
}

/**
 * ÉTAPE 3: Activer le mode Dashboard
 * Requis pour recevoir les flux JPG/ControlData toutes les 2 secondes
 */
async function activateDashboardMode() {
  console.log(`📊 ÉTAPE 3: Activation du mode Dashboard...`);

  const command = {
    method: 'RemoteSetDashboardMode',
    params: {
      UID: uuidv4(),
      On: true, // Activer
      Period: 2000, // Période en ms (2 secondes)
    },
    id: 2,
  };

  console.log(`📤 Envoi RemoteSetDashboardMode...`);
  socket.write(JSON.stringify(command) + '\r\n');
  console.log(`⏳ En attente de RemoteActionResult...\n`);
}

/**
 * ÉTAPE 4: Activer le mode RoboTarget Manager
 * Hash formula: SHA1("RoboTarget Shared secret" ||:|| SessionKey ||:|| Word1+Word2+Word3+Word4)
 */
async function activateRoboTargetManagerMode() {
  if (!MAC_KEY || !sessionKey) {
    throw new Error('MAC_KEY et SessionKey requis');
  }

  console.log(`🤖 ÉTAPE 4: Activation du mode RoboTarget Manager...`);

  const uid = uuidv4();

  // Calcul du Hash selon la doc officielle
  const sharedSecret = 'RoboTarget Shared secret';
  const separator = '||:||';
  const wordsConcat = `${MAC_WORD1 || ''}${MAC_WORD2 || ''}${MAC_WORD3 || ''}${MAC_WORD4 || ''}`;
  const hashString = `${sharedSecret}${separator}${sessionKey}${separator}${wordsConcat}`;
  const hash = crypto.createHash('sha1').update(hashString).digest('base64');

  console.log(`   Hash string length: ${hashString.length}`);
  console.log(`   Hash (SHA1→Base64): ${hash}`);

  const command = {
    method: 'RemoteSetRoboTargetManagerMode',
    params: {
      UID: uid,
      MACKey: MAC_KEY,
      Hash: hash,
    },
    id: 3,
  };

  console.log(`📤 Envoi RemoteSetRoboTargetManagerMode...`);
  socket.write(JSON.stringify(command) + '\r\n');
  console.log(`⏳ En attente de RemoteActionResult...\n`);
}

/**
 * ÉTAPE 5: Heartbeat (Polling)
 * CRITIQUE: Le serveur coupe la connexion si aucune donnée reçue pendant 15s
 * Solution: Envoyer un événement Polling toutes les 5 secondes
 */
function startHeartbeat() {
  console.log(`💓 ÉTAPE 5: Démarrage du Heartbeat (toutes les 5 secondes)...\n`);

  heartbeatInterval = setInterval(() => {
    const polling = {
      Event: 'Polling',
      Timestamp: Date.now() / 1000,
      Host: 'ProxyClient',
      Inst: 1,
    };

    socket.write(JSON.stringify(polling) + '\r\n');
    console.log(`💓 Heartbeat envoyé (${new Date().toLocaleTimeString()})`);
  }, 5000); // Toutes les 5 secondes
}

function stopHeartbeat() {
  if (heartbeatInterval) {
    clearInterval(heartbeatInterval);
    heartbeatInterval = null;
    console.log(`💓 Heartbeat arrêté`);
  }
}

// Timeout global de 60 secondes (pour permettre le test du heartbeat)
setTimeout(() => {
  console.log(`\n⏱️ Timeout global - 60 secondes écoulées`);
  if (!isRoboTargetModeActive) {
    console.log(`❌ La connexion n'est pas complètement établie`);
    console.log(`\n📊 État final:`);
    console.log(`   SessionKey reçu: ${sessionKey ? '✅' : '❌'}`);
    console.log(`   Authentifié: ${isAuthenticated ? '✅' : '❌'}`);
    console.log(`   Dashboard Mode: ${isDashboardModeActive ? '✅' : '❌'}`);
    console.log(`   RoboTarget Mode: ${isRoboTargetModeActive ? '✅' : '❌'}`);
  }
  stopHeartbeat();
  socket.destroy();
  process.exit(isRoboTargetModeActive ? 0 : 1);
}, 60000);
