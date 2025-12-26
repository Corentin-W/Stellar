// Test pour comparer les 2 formules MAC possibles pour GetSet

const testFormulas = async () => {
  console.log('🧪 TEST DES FORMULES MAC POUR GetSet\n');
  console.log('='.repeat(80));

  const formulas = [
    {
      name: 'Formule 1: TOUS les séparateurs avec 1 espace',
      description: 'Secret|| |SessionKey|| |ID|| |UID',
      sep1: '|| |',   // 1 espace
      sep2: '|| |',   // 1 espace
      sep3: '|| |'    // 1 espace
    },
    {
      name: 'Formule 2: Règle "1-2-1" (1 espace, 2 espaces, 1 espace)',
      description: 'Secret|| |SessionKey||  |ID|| |UID',
      sep1: '|| |',   // 1 espace
      sep2: '||  |',  // 2 espaces
      sep3: '|| |'    // 1 espace
    }
  ];

  for (let i = 0; i < formulas.length; i++) {
    const formula = formulas[i];

    console.log(`\n📝 Test ${i + 1}/${formulas.length}: ${formula.name}`);
    console.log(`   Format: ${formula.description}`);
    console.log(`   sep1: "${formula.sep1}" (${formula.sep1.length} chars)`);
    console.log(`   sep2: "${formula.sep2}" (${formula.sep2.length} chars)`);
    console.log(`   sep3: "${formula.sep3}" (${formula.sep3.length} chars)`);

    try {
      const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          method: 'RemoteRoboTargetGetSet',
          params: {
            ProfileName: ""
          },
          macFormula: {
            sep1: formula.sep1,
            sep2: formula.sep2,
            sep3: formula.sep3
          }
        })
      });

      const result = await response.json();

      console.log(`   MAC String: ${result.macInfo?.string}`);
      console.log(`   MAC: ${result.macInfo?.mac?.substring(0, 30)}...`);

      // Attendre la réponse (avec timeout de 5s)
      await new Promise(resolve => setTimeout(resolve, 5000));

      if (result.success) {
        if (result.result?.ParamRet?.ret === 'DONE') {
          console.log(`   ✅ SUCCESS! Voyager a répondu "DONE"`);
          const sets = result.result?.ParamRet?.list || [];
          console.log(`   📊 ${sets.length} Set(s) récupéré(s)`);

          console.log('\n' + '='.repeat(80));
          console.log(`🎯 FORMULE GAGNANTE: ${formula.name}`);
          console.log('='.repeat(80));
          return;
        } else {
          console.log(`   ⚠️ Voyager a répondu mais ret = "${result.result?.ParamRet?.ret}"`);
        }
      } else if (result.error?.includes('timeout') || result.error?.includes('Timeout')) {
        console.log(`   ❌ Timeout - Voyager n'a pas répondu`);
      } else if (result.result?.ParamRet?.ret) {
        console.log(`   ❌ Erreur: ${result.result.ParamRet.ret}`);
      } else {
        console.log(`   ❌ Échec: ${result.error || 'Erreur inconnue'}`);
      }

    } catch (error) {
      console.log(`   ❌ Erreur: ${error.message}`);
    }
  }

  console.log('\n' + '='.repeat(80));
  console.log('❌ AUCUNE FORMULE N\'A FONCTIONNÉ');
  console.log('='.repeat(80));
  console.log('\nVérifiez:');
  console.log('  1. Que Voyager a une license Advanced ou Full');
  console.log('  2. Que le ProfileName dans Voyager existe');
  console.log('  3. Les logs du serveur pour voir les erreurs MAC exactes');
};

testFormulas();
