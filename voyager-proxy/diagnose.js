#!/usr/bin/env node
/**
 * 🔍 DIAGNOSTIC COMPLET VOYAGER
 *
 * Ce script teste chaque étape de la connexion pour identifier
 * exactement où se trouve le problème.
 */

import 'dotenv/config';
import net from 'net';
import { spawn } from 'child_process';

const CONFIG = {
  host: process.env.VOYAGER_HOST || '185.228.120.120',
  port: parseInt(process.env.VOYAGER_PORT) || 23002,
  authBase: process.env.VOYAGER_AUTH_BASE,
  macKey: process.env.VOYAGER_MAC_KEY,
  macWord1: process.env.VOYAGER_MAC_WORD1,
  macWord2: process.env.VOYAGER_MAC_WORD2,
  macWord3: process.env.VOYAGER_MAC_WORD3,
  macWord4: process.env.VOYAGER_MAC_WORD4,
  licenseNumber: process.env.VOYAGER_LICENSE_NUMBER,
};

console.log('╔════════════════════════════════════════════════════════╗');
console.log('║     🔍 DIAGNOSTIC CONNEXION VOYAGER + ROBOTARGET      ║');
console.log('╚════════════════════════════════════════════════════════╝\n');

// Résultats du diagnostic
const results = {
  dns: { status: '⏳', message: 'En cours...' },
  ping: { status: '⏳', message: 'En cours...' },
  tcp: { status: '⏳', message: 'En cours...' },
  dataReceived: { status: '⏳', message: 'En cours...' },
  versionEvent: { status: '⏳', message: 'En cours...' },
  authParams: { status: '⏳', message: 'En cours...' },
};

// TEST 1: Résolution DNS
async function testDNS() {
  console.log('\n📋 TEST 1/6: Résolution DNS');
  console.log('─'.repeat(60));

  try {
    const { Resolver } = await import('dns/promises');
    const resolver = new Resolver();
    const addresses = await resolver.resolve4(CONFIG.host);

    results.dns.status = '✅';
    results.dns.message = `Résolu vers: ${addresses.join(', ')}`;
    console.log(`✅ DNS résolu: ${CONFIG.host} → ${addresses[0]}`);
    return true;
  } catch (error) {
    results.dns.status = '❌';
    results.dns.message = error.message;
    console.log(`❌ Échec DNS: ${error.message}`);
    console.log(`💡 L'hôte "${CONFIG.host}" n'existe pas ou n'est pas accessible`);
    return false;
  }
}

// TEST 2: Ping (vérifier si l'hôte répond)
async function testPing() {
  console.log('\n📋 TEST 2/6: Ping (ICMP)');
  console.log('─'.repeat(60));

  return new Promise((resolve) => {
    const ping = spawn('ping', ['-c', '3', '-W', '2', CONFIG.host]);
    let output = '';

    ping.stdout.on('data', (data) => {
      output += data.toString();
    });

    ping.on('close', (code) => {
      if (code === 0) {
        const match = output.match(/(\d+)% packet loss/);
        const loss = match ? match[1] : '?';
        results.ping.status = '✅';
        results.ping.message = `${loss}% perte de paquets`;
        console.log(`✅ Ping réussi (${loss}% perte)`);
        resolve(true);
      } else {
        results.ping.status = '⚠️';
        results.ping.message = 'Pas de réponse ICMP (firewall?)';
        console.log(`⚠️  Pas de réponse ping (peut être normal si ICMP bloqué)`);
        resolve(true); // Continue quand même
      }
    });

    setTimeout(() => {
      ping.kill();
      results.ping.status = '⚠️';
      results.ping.message = 'Timeout';
      console.log(`⚠️  Timeout ping`);
      resolve(true);
    }, 10000);
  });
}

// TEST 3: Connexion TCP
async function testTCP() {
  console.log('\n📋 TEST 3/6: Connexion TCP');
  console.log('─'.repeat(60));

  return new Promise((resolve) => {
    const socket = new net.Socket();
    socket.setTimeout(5000);

    socket.connect(CONFIG.port, CONFIG.host, () => {
      results.tcp.status = '✅';
      results.tcp.message = `Port ${CONFIG.port} ouvert`;
      console.log(`✅ Connexion TCP établie sur ${CONFIG.host}:${CONFIG.port}`);

      // TEST 4: Réception de données
      testDataReception(socket).then(resolve);
    });

    socket.on('error', (error) => {
      results.tcp.status = '❌';
      results.tcp.message = error.message;
      console.log(`❌ Échec connexion TCP: ${error.message}`);

      if (error.code === 'ECONNREFUSED') {
        console.log(`💡 Le port ${CONFIG.port} est FERMÉ ou rien n'écoute dessus`);
        console.log(`   → Vérifiez que Voyager est démarré`);
        console.log(`   → Vérifiez le numéro de port (défaut: 5950)`);
      } else if (error.code === 'ETIMEDOUT') {
        console.log(`💡 Timeout de connexion`);
        console.log(`   → L'hôte existe mais ne répond pas`);
        console.log(`   → Firewall bloque probablement le port ${CONFIG.port}`);
      }

      resolve(false);
    });

    socket.on('timeout', () => {
      results.tcp.status = '❌';
      results.tcp.message = 'Timeout (5s)';
      console.log(`❌ Timeout de connexion après 5 secondes`);
      socket.destroy();
      resolve(false);
    });
  });
}

// TEST 4: Réception de données
async function testDataReception(socket) {
  console.log('\n📋 TEST 4/6: Réception de données du serveur');
  console.log('─'.repeat(60));

  return new Promise((resolve) => {
    let dataReceived = false;
    let buffer = '';

    socket.on('data', (data) => {
      if (!dataReceived) {
        dataReceived = true;
        results.dataReceived.status = '✅';
        results.dataReceived.message = `${data.length} octets reçus`;
        console.log(`✅ Données reçues (${data.length} octets)`);
        console.log(`📦 Premier paquet (200 premiers caractères):`);
        console.log(`   ${data.toString().substring(0, 200)}...`);
      }

      buffer += data.toString();

      // TEST 5: Vérifier l'événement Version
      testVersionEvent(buffer, socket, resolve);
    });

    // Timeout de 15 secondes
    setTimeout(() => {
      if (!dataReceived) {
        results.dataReceived.status = '❌';
        results.dataReceived.message = 'Aucune donnée reçue (15s)';
        results.versionEvent.status = '❌';
        results.versionEvent.message = 'Non testé (pas de données)';

        console.log(`❌ PROBLÈME CRITIQUE: Aucune donnée reçue après 15 secondes`);
        console.log(`\n💡 DIAGNOSTIC:`);
        console.log(`   1. La connexion TCP fonctionne (socket ouvert)`);
        console.log(`   2. MAIS le serveur ne renvoie AUCUNE donnée`);
        console.log(`\n🔍 CAUSES POSSIBLES:`);
        console.log(`   ❌ Le port ${CONFIG.port} n'est PAS le bon port Voyager`);
        console.log(`      → Port par défaut de Voyager: 5950`);
        console.log(`      → Votre configuration: ${CONFIG.port}`);
        console.log(`   ❌ Voyager n'est pas démarré sur le serveur distant`);
        console.log(`   ❌ Vous êtes connecté à un proxy/tunnel SSH qui n'est pas configuré`);
        console.log(`   ❌ Voyager écoute sur 127.0.0.1 uniquement (pas accessible depuis l'extérieur)`);
        console.log(`\n📝 ACTIONS À FAIRE:`);
        console.log(`   1. Vérifiez que Voyager tourne sur le serveur distant`);
        console.log(`   2. Vérifiez le port configuré dans Voyager (Preferences → Remote)`);
        console.log(`   3. Testez avec le port 5950 (port par défaut)`);
        console.log(`   4. Si vous utilisez un tunnel SSH, vérifiez la configuration`);
      }

      socket.destroy();
      resolve(!dataReceived);
    }, 15000);
  });
}

// TEST 5: Événement Version
function testVersionEvent(buffer, socket, resolve) {
  const lines = buffer.split('\r\n');

  for (const line of lines) {
    if (line.trim()) {
      try {
        const message = JSON.parse(line);

        if (message.Event === 'Version') {
          results.versionEvent.status = '✅';
          results.versionEvent.message = `Version ${message.VOYVersion}, SessionKey: ${message.Timestamp}`;

          console.log(`\n📋 TEST 5/6: Événement Version`);
          console.log('─'.repeat(60));
          console.log(`✅ Événement Version reçu!`);
          console.log(`   Version Voyager: ${message.VOYVersion}`);
          console.log(`   SessionKey: ${message.Timestamp}`);
          console.log(`   Hôte: ${message.Host}`);
          console.log(`   Instance: ${message.Inst}`);

          // TEST 6: Vérifier les paramètres d'authentification
          testAuthParams();

          socket.destroy();
          resolve(true);
          return;
        }
      } catch (e) {
        // Ligne non-JSON, ignorer
      }
    }
  }
}

// TEST 6: Paramètres d'authentification
function testAuthParams() {
  console.log(`\n📋 TEST 6/6: Paramètres d'authentification`);
  console.log('─'.repeat(60));

  const issues = [];

  console.log(`Configuration actuelle:`);
  console.log(`   AUTH_BASE: ${CONFIG.authBase || '❌ NON DÉFINI'}`);
  console.log(`   MAC_KEY: ${CONFIG.macKey || '❌ NON DÉFINI'}`);
  console.log(`   MAC_WORD1: ${CONFIG.macWord1 ? '✅ Défini' : '❌ NON DÉFINI'}`);
  console.log(`   MAC_WORD2: ${CONFIG.macWord2 ? '✅ Défini' : '❌ NON DÉFINI'}`);
  console.log(`   MAC_WORD3: ${CONFIG.macWord3 ? '✅ Défini' : '❌ NON DÉFINI'}`);
  console.log(`   MAC_WORD4: ${CONFIG.macWord4 ? '✅ Défini' : '❌ NON DÉFINI'}`);
  console.log(`   LICENSE_NUMBER: ${CONFIG.licenseNumber ? '✅ Défini' : '⚠️  Optionnel'}`);

  if (!CONFIG.authBase) {
    issues.push('AUTH_BASE manquant');
  }

  if (!CONFIG.macKey) {
    issues.push('MAC_KEY manquant');
  }

  if (!CONFIG.macWord1 || !CONFIG.macWord2 || !CONFIG.macWord3 || !CONFIG.macWord4) {
    issues.push('MAC_WORDx incomplets');
  }

  if (issues.length > 0) {
    results.authParams.status = '⚠️';
    results.authParams.message = issues.join(', ');
    console.log(`\n⚠️  Paramètres d'authentification incomplets:`);
    issues.forEach(issue => console.log(`   - ${issue}`));
    console.log(`\n💡 Ces paramètres sont requis pour RoboTarget Manager Mode`);
  } else {
    results.authParams.status = '✅';
    results.authParams.message = 'Tous les paramètres présents';
    console.log(`\n✅ Tous les paramètres d'authentification sont définis`);
  }
}

// Afficher le résumé final
function printSummary() {
  console.log('\n\n');
  console.log('╔════════════════════════════════════════════════════════╗');
  console.log('║              📊 RÉSUMÉ DU DIAGNOSTIC                   ║');
  console.log('╚════════════════════════════════════════════════════════╝\n');

  console.log(`${results.dns.status} DNS: ${results.dns.message}`);
  console.log(`${results.ping.status} Ping: ${results.ping.message}`);
  console.log(`${results.tcp.status} TCP: ${results.tcp.message}`);
  console.log(`${results.dataReceived.status} Données: ${results.dataReceived.message}`);
  console.log(`${results.versionEvent.status} Version: ${results.versionEvent.message}`);
  console.log(`${results.authParams.status} Auth: ${results.authParams.message}`);

  console.log('\n' + '═'.repeat(60));

  // Diagnostic global
  if (results.versionEvent.status === '✅') {
    console.log('\n🎉 CONNEXION COMPLÈTE RÉUSSIE !');
    console.log('   Le serveur Voyager fonctionne correctement.');
    if (results.authParams.status === '✅') {
      console.log('   Tous les paramètres sont OK pour RoboTarget.');
    } else {
      console.log('   ⚠️  Vérifiez les paramètres d\'authentification ci-dessus.');
    }
  } else if (results.tcp.status === '✅' && results.dataReceived.status === '❌') {
    console.log('\n❌ PROBLÈME IDENTIFIÉ: PORT INCORRECT');
    console.log(`   Le port ${CONFIG.port} accepte les connexions mais ne renvoie pas de données.`);
    console.log(`   → Essayez le port 5950 (port par défaut de Voyager)`);
    console.log(`   → Ou vérifiez la configuration du port dans Voyager`);
  } else if (results.tcp.status === '❌') {
    console.log('\n❌ PROBLÈME IDENTIFIÉ: SERVEUR NON ACCESSIBLE');
    console.log(`   Impossible de se connecter à ${CONFIG.host}:${CONFIG.port}`);
    console.log(`   → Vérifiez que le serveur est accessible`);
    console.log(`   → Vérifiez les paramètres firewall`);
  }

  console.log('\n');
}

// Exécuter tous les tests
async function runDiagnostics() {
  console.log(`🎯 Cible: ${CONFIG.host}:${CONFIG.port}`);
  console.log(`📅 Date: ${new Date().toLocaleString()}\n`);

  const dnsOk = await testDNS();
  if (!dnsOk) {
    printSummary();
    process.exit(1);
  }

  await testPing();

  const tcpOk = await testTCP();
  if (!tcpOk) {
    printSummary();
    process.exit(1);
  }

  // Les tests 4, 5, 6 sont lancés dans testTCP

  // Attendre que tout soit terminé
  setTimeout(() => {
    printSummary();
    process.exit(0);
  }, 16000);
}

// Lancer le diagnostic
runDiagnostics();
