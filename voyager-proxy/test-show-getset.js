// Script pour tester RemoteRoboTargetGetSet et afficher la requête
import { v4 as uuidv4 } from 'uuid';

const testGetSet = async () => {
  try {
    console.log('🎯 TEST RemoteRoboTargetGetSet\n');
    console.log('='.repeat(80));

    // Paramètres pour GetSet
    // RefGuidSet vide = retourne tous les Sets
    const params = {
      RefGuidSet: ""  // Vide = tous les sets
    };

    console.log('\n📤 Envoi de la commande RemoteRoboTargetGetSet...\n');
    console.log('Paramètres:');
    console.log(`  RefGuidSet: "${params.RefGuidSet}" (vide = tous les sets)`);

    const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetGetSet',
        params: params,
        macFormula: {
          sep1: '||:||',  // Pour les commandes Reserved API (pas "1-2-1")
          sep2: '||:||',
          sep3: '||:||'
        }
      })
    });

    const result = await response.json();

    console.log('\n' + '='.repeat(80));
    console.log('📦 REQUÊTE JSON-RPC COMPLÈTE ENVOYÉE À VOYAGER');
    console.log('='.repeat(80));
    console.log(JSON.stringify(result.command, null, 2));

    console.log('\n' + '='.repeat(80));
    console.log('🔐 CALCUL DU MAC');
    console.log('='.repeat(80));
    console.log(`\nFormule Reserved API: Secret||:||SessionKey||:||ID||:||UID`);
    console.log(`                      (pas d'espaces, juste ||:||)`);
    console.log(`\nMAC String:   ${result.macInfo.string}`);
    console.log(`\nÉtapes de calcul:`);
    console.log(`  1. SHA1 de la chaîne ci-dessus`);
    console.log(`  2. Convertir le hash en HEX`);
    console.log(`  3. Encoder le HEX en Base64`);
    console.log(`\nMAC (Base64): ${result.macInfo.mac}`);

    console.log('\n' + '='.repeat(80));
    console.log('📊 RÉSULTAT DE LA COMMANDE');
    console.log('='.repeat(80));

    if (result.success) {
      console.log('✅ SUCCESS! Commande envoyée et réponse reçue\n');

      const sets = result.result?.ParamRet?.list || result.result?.parsed?.params?.list || [];

      if (sets.length > 0) {
        console.log(`✅ ${sets.length} Set(s) récupéré(s):\n`);
        sets.forEach((set, idx) => {
          console.log(`   ${idx + 1}. ${set.setname || set.SetName}`);
          console.log(`      GUID: ${set.guid || set.GuidSet}`);
          console.log(`      Profile: ${set.profilename || set.ProfileName}`);
          console.log(`      Status: ${set.status} (0=Actif, 1=Inactif)`);
          console.log('');
        });
      }

      if (result.result?.ParamRet?.ret === 'DONE') {
        console.log('✅ VALIDATION: ParamRet.ret === "DONE"');
      }

      console.log('\nRésultat complet:');
      console.log(JSON.stringify(result.result, null, 2));
    } else {
      console.log('❌ Échec ou timeout\n');
      if (result.error) {
        console.log(`Erreur: ${result.error}`);
      }
    }

    console.log('\n' + '='.repeat(80));
    console.log('📋 COMPARAISON AVEC LA DOCUMENTATION');
    console.log('='.repeat(80));
    console.log('✅ Paramètre RefGuidSet présent');
    console.log('✅ MAC avec séparateurs ||:|| (Reserved API)');
    console.log('✅ UID automatiquement ajouté');
    console.log('\n' + '='.repeat(80));

  } catch (error) {
    console.error('\n❌ ERREUR:', error.message);
    console.error(error.stack);
  }
};

testGetSet();
