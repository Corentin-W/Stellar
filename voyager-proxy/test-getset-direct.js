// Test direct RemoteRoboTargetGetSet avec affichage des séparateurs exacts

const testGetSet = async () => {
  try {
    console.log('🎯 TEST DIRECT RemoteRoboTargetGetSet\n');
    console.log('='.repeat(80));

    // Séparateurs EXPLICITES avec vérification
    const sep1 = '||' + ' ' + '|';  // pipe pipe espace pipe
    const sep2 = '||' + '  ' + '|'; // pipe pipe espace espace pipe
    const sep3 = '||' + ' ' + '|';  // pipe pipe espace pipe

    console.log('\n🔍 VÉRIFICATION DES SÉPARATEURS:');
    console.log(`sep1: "${sep1}" (longueur: ${sep1.length}, attendu: 4)`);
    console.log(`sep2: "${sep2}" (longueur: ${sep2.length}, attendu: 5)`);
    console.log(`sep3: "${sep3}" (longueur: ${sep3.length}, attendu: 4)`);

    // Afficher les bytes exacts
    console.log('\n📏 BYTES EXACTS (codes ASCII):');
    console.log(`sep1: [${[...sep1].map(c => c.charCodeAt(0)).join(', ')}]`);
    console.log(`sep2: [${[...sep2].map(c => c.charCodeAt(0)).join(', ')}]`);
    console.log(`sep3: [${[...sep3].map(c => c.charCodeAt(0)).join(', ')}]`);

    if (sep1.length !== 4) {
      console.error('❌ ERREUR: sep1 devrait avoir 4 caractères!');
      return;
    }
    if (sep2.length !== 5) {
      console.error('❌ ERREUR: sep2 devrait avoir 5 caractères (|| avec 2 espaces)!');
      return;
    }
    if (sep3.length !== 4) {
      console.error('❌ ERREUR: sep3 devrait avoir 4 caractères!');
      return;
    }

    console.log('✅ Séparateurs valides!\n');

    // Paramètres pour GetSet
    const params = {
      ProfileName: ""  // Vide = tous les sets de tous les profils
    };

    console.log('📤 Envoi de la commande RemoteRoboTargetGetSet...\n');
    console.log('Paramètres:');
    console.log(`  ProfileName: "${params.ProfileName}" (vide = tous les profils)`);
    console.log('\nFormule MAC attendue:');
    console.log('  Secret|| |SessionKey||  |ID|| |UID');
    console.log('         ^^^^          ^^^^^ ^^^^');
    console.log('       1 espace    2 espaces  1 espace\n');

    const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetGetSet',
        params: params,
        macFormula: {
          sep1: sep1,
          sep2: sep2,
          sep3: sep3
        }
      })
    });

    const result = await response.json();

    console.log('='.repeat(80));
    console.log('🔐 CHAÎNE MAC CALCULÉE');
    console.log('='.repeat(80));
    console.log(result.macInfo.string);
    console.log('\n📏 Vérification des espaces dans la chaîne MAC:');

    // Analyser la chaîne MAC pour compter les espaces
    const macStr = result.macInfo.string;
    const parts = macStr.split('||');
    console.log(`Nombre de segments séparés par ||: ${parts.length}`);
    parts.forEach((part, idx) => {
      console.log(`  Segment ${idx}: "${part}" (${part.length} caractères)`);
    });

    console.log('\n='.repeat(80));
    console.log('📊 RÉSULTAT');
    console.log('='.repeat(80));

    if (result.success) {
      console.log('✅ SUCCESS! Commande envoyée et réponse reçue\n');

      const sets = result.result?.ParamRet?.list || result.result?.parsed?.params?.list || [];

      if (sets.length > 0) {
        console.log(`✅ ${sets.length} Set(s) récupéré(s)\n`);
        sets.slice(0, 3).forEach((set, idx) => {
          console.log(`   ${idx + 1}. ${set.setname || set.SetName}`);
        });
        if (sets.length > 3) {
          console.log(`   ... et ${sets.length - 3} autres`);
        }
      }

      if (result.result?.ParamRet?.ret === 'DONE') {
        console.log('\n✅ VALIDATION: ParamRet.ret === "DONE"');
      } else {
        console.log(`\n⚠️ ATTENTION: ParamRet.ret = "${result.result?.ParamRet?.ret}"`);
      }
    } else {
      console.log('❌ Échec ou timeout\n');
      if (result.error) {
        console.log(`Erreur: ${result.error}`);
      }
      if (result.result?.ParamRet) {
        console.log(`Voyager a répondu: ${JSON.stringify(result.result.ParamRet)}`);
      }
    }

    console.log('\n' + '='.repeat(80));

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error(error.stack);
  }
};

testGetSet();
