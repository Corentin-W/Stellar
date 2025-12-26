// Reproduire EXACTEMENT l'exemple de la documentation

import crypto from 'crypto';

const testExactExample = () => {
  console.log('📖 REPRODUCTION EXACTE DE L\'EXEMPLE DE LA DOCUMENTATION\n');
  console.log('='.repeat(80));

  // Données de l'exemple doc (ligne 240)
  const secret = "pippo";
  const sessionKey = "1652231344.88438";
  const id = 5;
  const uid = "0697f2f9-24e4-4850-84e9-18ea28b05fe9";

  // Formule documentée
  const macString = `${secret}|| |${sessionKey}|| |${id}|| |${uid}`;
  console.log('\n📝 Chaîne MAC de la documentation:');
  console.log(macString);

  // Calculer le MAC selon la doc
  const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
  const mac = Buffer.from(hexHash, 'utf8').toString('base64');

  console.log('\n🔐 Calcul:');
  console.log(`  SHA1 (hex): ${hexHash}`);
  console.log(`  Base64:     ${mac}`);

  console.log('\n📊 Comparaison avec la doc:');
  console.log(`  MAC doc:    nWq/V98Laq+hFFdMvynnneAyKvk=`);
  console.log(`  Notre MAC:  ${mac}`);

  if (mac === "nWq/V98Laq+hFFdMvynnneAyKvk=") {
    console.log('\n  ✅ PARFAIT! Notre algorithme est correct!');
  } else {
    console.log('\n  ❌ DIFFÉRENT! Notre algorithme est incorrect!');
  }

  console.log('\n' + '='.repeat(80));
  console.log('\n💡 MAINTENANT, testons avec VOS données réelles:');
  console.log('='.repeat(80));

  // Vos données réelles
  const yourSecret = "Dherbomez";
  const yourSessionKey = "1766742010.96748"; // Du dernier test
  const yourId = 2;
  const yourUid = "f0adb852-7d52-4aeb-96bd-7384f0a725f8";

  const yourMacString = `${yourSecret}|| |${yourSessionKey}|| |${yourId}|| |${yourUid}`;
  console.log('\n📝 Votre chaîne MAC:');
  console.log(yourMacString);

  const yourHexHash = crypto.createHash('sha1').update(yourMacString).digest('hex');
  const yourMac = Buffer.from(yourHexHash, 'utf8').toString('base64');

  console.log('\n🔐 Votre MAC:');
  console.log(`  SHA1 (hex): ${yourHexHash}`);
  console.log(`  Base64:     ${yourMac}`);

  console.log('\n✅ Utilisez ce MAC pour tester avec Voyager!');
  console.log('='.repeat(80));
};

testExactExample();
