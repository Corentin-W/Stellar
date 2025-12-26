// Vérifier l'exemple de Manager Mode qui devrait être correct

import crypto from 'crypto';

const testManagerModeExample = () => {
  console.log('🔬 VÉRIFICATION DE L\'EXEMPLE MANAGER MODE (Doc ligne 203-205)\n');
  console.log('='.repeat(80));

  // Exemple doc Manager Mode
  const macString = "pippo||:||1652231344.88438||:||12345678abcdefgplutopaperino";
  const expectedHex = "69efafc940cabd1797da7dc57a1452cdaae6d0ff";
  const expectedBase64 = "NjllZmFmYzk0MGNhYmQxNzk3ZGE3ZGM1N2ExNDUyY2RhYWU2ZDBmZg==";

  console.log('📝 Chaîne MAC:', macString);
  console.log('\nRésultats attendus (doc):');
  console.log(`  SHA1 (hex):    ${expectedHex}`);
  console.log(`  Base64:        ${expectedBase64}`);

  console.log('\n' + '='.repeat(80));
  console.log('TEST 1: SHA1 → HEX → Base64(HEX string)');
  console.log('='.repeat(80));

  const hex1 = crypto.createHash('sha1').update(macString).digest('hex');
  const mac1 = Buffer.from(hex1, 'utf8').toString('base64');

  console.log(`  SHA1 (hex):    ${hex1}`);
  console.log(`  Match HEX:     ${hex1 === expectedHex ? '✅ OUI!' : '❌ NON'}`);
  console.log(`  Base64:        ${mac1}`);
  console.log(`  Match Base64:  ${mac1 === expectedBase64 ? '✅ OUI!' : '❌ NON'}`);

  console.log('\n' + '='.repeat(80));
  console.log('TEST 2: SHA1 → Binary → Base64(Binary)');
  console.log('='.repeat(80));

  const mac2 = crypto.createHash('sha1').update(macString).digest('base64');
  console.log(`  SHA1 → Base64: ${mac2}`);
  console.log(`  Match:         ${mac2 === expectedBase64 ? '✅ OUI!' : '❌ NON'}`);

  console.log('\n' + '='.repeat(80));
  console.log('🎯 CONCLUSION');
  console.log('='.repeat(80));

  if (hex1 === expectedHex && mac1 === expectedBase64) {
    console.log('✅ ALGORITHME 1 EST CORRECT: SHA1 → HEX → Base64(HEX string)');
    console.log('\n📋 Cet algorithme doit être utilisé pour TOUTES les commandes RoboTarget!');
  } else if (mac2 === expectedBase64) {
    console.log('✅ ALGORITHME 2 EST CORRECT: SHA1 → Binary → Base64(Binary)');
    console.log('\n⚠️ Nous devons changer notre implémentation!');
  } else {
    console.log('❌ AUCUN algorithme ne correspond!');
  }
};

testManagerModeExample();
