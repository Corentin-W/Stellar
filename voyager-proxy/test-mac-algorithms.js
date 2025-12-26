// Tester les 2 algorithmes possibles pour le MAC

import crypto from 'crypto';

const testAlgorithms = () => {
  console.log('🔬 TEST DES ALGORITHMES DE CALCUL MAC\n');
  console.log('='.repeat(80));

  // Données de l'exemple doc
  const secret = "pippo";
  const sessionKey = "1652231344.88438";
  const id = 5;
  const uid = "0697f2f9-24e4-4850-84e9-18ea28b05fe9";
  const macString = `${secret}|| |${sessionKey}|| |${id}|| |${uid}`;

  console.log('📝 Chaîne MAC:', macString);
  console.log('\nMAC attendu (doc):', "nWq/V98Laq+hFFdMvynnneAyKvk=");

  console.log('\n' + '='.repeat(80));
  console.log('ALGORITHME 1: SHA1 → HEX string → Base64(HEX string)');
  console.log('='.repeat(80));

  const hex1 = crypto.createHash('sha1').update(macString).digest('hex');
  const mac1 = Buffer.from(hex1, 'utf8').toString('base64');

  console.log(`  SHA1 (hex): ${hex1}`);
  console.log(`  Base64:     ${mac1}`);
  console.log(`  Match:      ${mac1 === "nWq/V98Laq+hFFdMvynnneAyKvk=" ? '✅ OUI!' : '❌ NON'}`);

  console.log('\n' + '='.repeat(80));
  console.log('ALGORITHME 2: SHA1 → Binary → Base64(Binary)');
  console.log('='.repeat(80));

  const mac2 = crypto.createHash('sha1').update(macString).digest('base64');

  console.log(`  SHA1 → Base64: ${mac2}`);
  console.log(`  Match:         ${mac2 === "nWq/V98Laq+hFFdMvynnneAyKvk=" ? '✅ OUI!' : '❌ NON'}`);

  console.log('\n' + '='.repeat(80));
  console.log('ALGORITHME 3: SHA1 → Binary → HEX from binary → Base64(HEX string)');
  console.log('='.repeat(80));

  const binary = crypto.createHash('sha1').update(macString).digest();
  const hex3 = binary.toString('hex');
  const mac3 = Buffer.from(hex3, 'utf8').toString('base64');

  console.log(`  SHA1 (binary → hex): ${hex3}`);
  console.log(`  Base64:              ${mac3}`);
  console.log(`  Match:               ${mac3 === "nWq/V98Laq+hFFdMvynnneAyKvk=" ? '✅ OUI!' : '❌ NON'}`);

  console.log('\n' + '='.repeat(80));
  console.log('🎯 RÉSULTAT');
  console.log('='.repeat(80));

  if (mac1 === "nWq/V98Laq+hFFdMvynnneAyKvk=") {
    console.log('✅ Algorithme 1 est CORRECT!');
  } else if (mac2 === "nWq/V98Laq+hFFdMvynnneAyKvk=") {
    console.log('✅ Algorithme 2 est CORRECT!');
  } else if (mac3 === "nWq/V98Laq+hFFdMvynnneAyKvk=") {
    console.log('✅ Algorithme 3 est CORRECT!');
  } else {
    console.log('❌ AUCUN algorithme ne correspond!');
    console.log('   Il y a peut-être une erreur dans la doc ou dans les séparateurs.');
  }
};

testAlgorithms();
