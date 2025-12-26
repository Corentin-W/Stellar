// Test AUTO-TEST MAC pour GetSet
const testAutoGetSet = async () => {
  try {
    console.log('🎯 AUTO-TEST RemoteRoboTargetGetSet\n');
    console.log('Ce test va essayer automatiquement 3 formules MAC différentes:');
    console.log('  1. Formule Manager Mode: Secret||:||SessionKey||:||ID||:||UID');
    console.log('  2. Formule NDA (1-2-1): Secret|| |SessionKey||  |ID|| |UID');
    console.log('  3. Formule Open API: Secret + UID (pas de séparateurs)\n');
    console.log('='.repeat(80));

    const response = await fetch('http://localhost:3003/api/robotarget/auto-test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetGetSet',
        params: {
          ProfileName: ""  // Vide = tous les profiles
        }
      })
    });

    const result = await response.json();

    console.log('\n📊 RÉSULTATS DES TESTS:\n');

    if (result.allResults) {
      result.allResults.forEach((test, idx) => {
        console.log(`\nTest ${idx + 1}: ${test.variant}`);
        if (test.success) {
          console.log('  ✅ SUCCESS!');
          console.log(`  MAC: ${test.mac}`);
          if (test.result?.ParamRet?.list) {
            console.log(`  Sets trouvés: ${test.result.ParamRet.list.length}`);
          }
        } else {
          console.log('  ❌ FAILED');
          if (test.error) {
            console.log(`  Erreur: ${test.error}`);
          } else if (test.result?.ParamRet?.ret) {
            console.log(`  Voyager a répondu: ${test.result.ParamRet.ret}`);
          }
        }
      });
    }

    console.log('\n' + '='.repeat(80));
    console.log('🎯 RÉSULTAT FINAL');
    console.log('='.repeat(80));

    if (result.success && result.successfulVariant) {
      console.log(`✅ FORMULE TROUVÉE: ${result.successfulVariant.name}`);
      console.log('\n📋 Configuration à utiliser:');
      console.log(`  sep1: "${result.recommendation.sep1}"`);
      console.log(`  sep2: "${result.recommendation.sep2}"`);
      console.log(`  sep3: "${result.recommendation.sep3}"`);
      console.log(`  conversion: ${result.recommendation.conversion}`);
    } else {
      console.log('❌ AUCUNE FORMULE N\'A FONCTIONNÉ');
      console.log('\nPossibles raisons:');
      console.log('  1. Voyager n\'est pas connecté');
      console.log('  2. RoboTarget Manager Mode n\'est pas activé');
      console.log('  3. Pas de license Advanced/Full sur Voyager');
      console.log('  4. SharedSecret ou SessionKey incorrect');
    }

    console.log('\n' + '='.repeat(80));

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error(error.stack);
  }
};

testAutoGetSet();
