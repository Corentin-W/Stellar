#!/usr/bin/env node

/**
 * Test script for RoboTarget API
 *
 * Usage: node test-robotarget.js
 */

const API_URL = 'http://localhost:3000';
const API_KEY = 'f5b05ea4e31c5408b307a59b2aa64c3a564ca19ca0985f1fb58ed385290ea09d';

async function apiRequest(method, endpoint, body = null) {
  const options = {
    method,
    headers: {
      'Content-Type': 'application/json',
      'X-API-Key': API_KEY,
    },
  };

  if (body) {
    options.body = JSON.stringify(body);
  }

  const response = await fetch(`${API_URL}${endpoint}`, options);
  const data = await response.json();

  if (!response.ok) {
    throw new Error(`HTTP ${response.status}: ${JSON.stringify(data)}`);
  }

  return data;
}

async function testRoboTarget() {
  console.log('🧪 Test RoboTarget API\n');

  try {
    // 1. Créer un Set
    console.log('1️⃣ Création d\'un Set...');
    const setGuid = crypto.randomUUID();
    const setResult = await apiRequest('POST', '/api/robotarget/sets', {
      Guid: setGuid,
      Name: 'Test Set - ' + new Date().toISOString(),
      ProfileName: 'Default.v2y',
      Status: 0, // Enabled
      Tag: 'test_stellar',
    });
    console.log('   ✅ Set créé:', setGuid);
    console.log('   Résultat:', JSON.stringify(setResult, null, 2), '\n');

    // 2. Créer une Target
    console.log('2️⃣ Création d\'une Target (M31)...');
    const targetGuid = crypto.randomUUID();
    const targetResult = await apiRequest('POST', '/api/robotarget/targets', {
      GuidTarget: targetGuid,
      RefGuidSet: setGuid,
      TargetName: 'M31 Andromeda',
      RAJ2000: '00:42:44.330',
      DECJ2000: '+41:16:09.00',
      PA: 0,
      DateCreation: Math.floor(Date.now() / 1000),
      Status: 0, // Active
      Priority: 2,
      IsRepeat: true,
      Repeat: 1,
      // Contraintes
      C_Mask: 'BDE',
      C_AltMin: 30,
      C_HAStart: -3,
      C_HAEnd: 3,
    });
    console.log('   ✅ Target créée:', targetGuid);
    console.log('   Résultat:', JSON.stringify(targetResult, null, 2), '\n');

    // 3. Créer des Shots
    console.log('3️⃣ Création de Shots...');
    const filters = [
      { name: 'Luminance', filter: 0, exposure: 300, num: 20 },
      { name: 'Red', filter: 1, exposure: 180, num: 10 },
      { name: 'Green', filter: 2, exposure: 180, num: 10 },
      { name: 'Blue', filter: 3, exposure: 180, num: 10 },
    ];

    for (const shot of filters) {
      const shotGuid = crypto.randomUUID();
      const shotResult = await apiRequest('POST', '/api/robotarget/shots', {
        GuidShot: shotGuid,
        RefGuidTarget: targetGuid,
        FilterIndex: shot.filter,
        Num: shot.num,
        Bin: 1,
        ReadoutMode: 0,
        Type: 0, // LIGHT
        Speed: 0,
        Gain: 100,
        Offset: 10,
        Exposure: shot.exposure,
        Order: filters.indexOf(shot) + 1,
        Enabled: true,
      });
      console.log(`   ✅ Shot ${shot.name}: ${shot.num}x${shot.exposure}s`);
    }
    console.log();

    // 4. Lister les Sets
    console.log('4️⃣ Liste des Sets...');
    const setsResult = await apiRequest('GET', '/api/robotarget/sets');
    console.log('   ✅ Nombre de Sets:', setsResult.result?.length || 0);
    console.log();

    // 5. Lister les Targets du Set
    console.log('5️⃣ Liste des Targets du Set...');
    const targetsResult = await apiRequest('GET', `/api/robotarget/targets?setGuid=${setGuid}`);
    console.log('   ✅ Nombre de Targets:', targetsResult.result?.length || 0);
    console.log();

    // 6. Activer la Target
    console.log('6️⃣ Activation de la Target...');
    const activateResult = await apiRequest('POST', `/api/robotarget/targets/${targetGuid}/activate`);
    console.log('   ✅ Target activée');
    console.log('   Résultat:', JSON.stringify(activateResult, null, 2), '\n');

    // 7. Désactiver la Target
    console.log('7️⃣ Désactivation de la Target...');
    const deactivateResult = await apiRequest('POST', `/api/robotarget/targets/${targetGuid}/deactivate`);
    console.log('   ✅ Target désactivée');
    console.log();

    // 8. Supprimer (optionnel - commenté pour garder les données)
    /*
    console.log('8️⃣ Nettoyage...');
    await apiRequest('DELETE', `/api/robotarget/targets/${targetGuid}`);
    console.log('   ✅ Target supprimée');
    await apiRequest('DELETE', `/api/robotarget/sets/${setGuid}`);
    console.log('   ✅ Set supprimé');
    */

    console.log('\n🎉 Tous les tests ont réussi !');
    console.log('\n💡 Note: Les données créées sont conservées dans Voyager.');
    console.log('   Pour les supprimer, décommentez la section de nettoyage.');

  } catch (error) {
    console.error('\n❌ Erreur:', error.message);
    console.error(error);
    process.exit(1);
  }
}

// Exécuter les tests
testRoboTarget();
