// Test GetSet en utilisant EXACTEMENT la même formule que Manager Mode qui a fonctionné

const testGetSetLikeManager = async () => {
  console.log('🧪 TEST GetSet avec la formule de Manager Mode (qui a fonctionné)\n');
  console.log('='.repeat(80));

  console.log('\nManager Mode utilise: Secret||:||SessionKey||:||MAC1||:||MAC2||:||MAC3||:||MAC4');
  console.log('Testons GetSet avec: Secret||:||SessionKey||:||ID||:||UID\n');

  const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({
      method: 'RemoteRoboTargetGetSet',
      params: {
        ProfileName: ""  // Vide = tous les profils
      },
      macFormula: {
        sep1: '||:||',  // MÊME séparateur que Manager Mode
        sep2: '||:||',
        sep3: '||:||'
      }
    })
  });

  const result = await response.json();

  console.log('📝 MAC String:', result.macInfo?.string);
  console.log('🔐 MAC:', result.macInfo?.mac);

  // Attendre 5 secondes pour la réponse
  await new Promise(resolve => setTimeout(resolve, 5000));

  console.log('\n📊 RÉSULTAT:');
  if (result.success && result.result?.ParamRet?.ret === 'DONE') {
    console.log('✅ SUCCESS! Voyager a répondu "DONE"');
    const sets = result.result.ParamRet.list || [];
    console.log(`📊 ${sets.length} Set(s) trouvé(s)`);
    if (sets.length > 0) {
      console.log('\nPremier Set:');
      console.log(JSON.stringify(sets[0], null, 2));
    }
  } else if (result.error) {
    console.log(`❌ Erreur: ${result.error}`);
  } else if (result.result?.ParamRet?.ret) {
    console.log(`⚠️ Voyager a répondu: ${result.result.ParamRet.ret}`);
  } else {
    console.log('❌ Pas de réponse ou timeout');
  }

  console.log('\n' + '='.repeat(80));
};

testGetSetLikeManager();
