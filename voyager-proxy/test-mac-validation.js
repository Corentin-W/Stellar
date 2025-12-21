#!/usr/bin/env node

/**
 * Test de validation du calcul MAC selon la documentation Section 6.b
 */

import crypto from 'crypto';

console.log('🧪 Test de validation MAC (Section 6.b)\n');
console.log('='.repeat(80));

// Exemple de la doc Section 6.b
const secret = "pippo";
const sessionKey = "1652231344.88438";
const id = "5";
const uid = "0697f2f9-24e4-4850-84e9-18ea28b05fe9";

// Séparateurs selon la doc
const sep1 = '|| |';   // Secret → SessionKey (1 espace)
const sep2 = '||  |';  // SessionKey → ID (2 espaces) ⚠️
const sep3 = '|| |';   // ID → UID (1 espace)

console.log('\n📋 Paramètres de test:');
console.log(`   Secret: "${secret}"`);
console.log(`   SessionKey: "${sessionKey}"`);
console.log(`   ID: "${id}"`);
console.log(`   UID: "${uid}"`);

console.log('\n🔍 Séparateurs:');
console.log(`   Sep1 (Secret→SessionKey): "${sep1}" (${sep1.length} chars)`);
console.log(`   Sep2 (SessionKey→ID): "${sep2}" (${sep2.length} chars) ⚠️ 2 espaces`);
console.log(`   Sep3 (ID→UID): "${sep3}" (${sep3.length} chars)`);

// Construction de la chaîne MAC
const macString = secret + sep1 + sessionKey + sep2 + id + sep3 + uid;
const expectedString = "pippo|| |1652231344.88438||  |5|| |0697f2f9-24e4-4850-84e9-18ea28b05fe9";

console.log('\n📝 Chaîne MAC construite:');
console.log(`   Construite: "${macString}"`);
console.log(`   Attendue:   "${expectedString}"`);
console.log(`   Longueur: ${macString.length} chars`);
console.log(`   ✓ Match: ${macString === expectedString ? '✅ OUI' : '❌ NON'}`);

if (macString !== expectedString) {
    console.log('\n❌ ERREUR: Les chaînes ne correspondent pas!');
    console.log('\n🔍 Analyse caractère par caractère:');
    for (let i = 0; i < Math.max(macString.length, expectedString.length); i++) {
        const c1 = macString[i] || '';
        const c2 = expectedString[i] || '';
        if (c1 !== c2) {
            console.log(`   Position ${i}: Got '${c1}' (${c1.charCodeAt(0)}) vs Expected '${c2}' (${c2.charCodeAt(0)})`);
        }
    }
}

// Calcul du MAC
const mac = crypto.createHash('sha1').update(macString).digest('base64');
const expectedMAC = "nWq/V98Laq+hFFdMvynnneAyKvk=";

console.log('\n🔐 Calcul MAC (SHA1 → Base64):');
console.log(`   Calculé:  "${mac}"`);
console.log(`   Attendu:  "${expectedMAC}"`);
console.log(`   Longueur: ${mac.length} chars`);
console.log(`   ✓ Match: ${mac === expectedMAC ? '✅ OUI' : '❌ NON'}`);

if (mac === expectedMAC) {
    console.log('\n✅ SUCCÈS: Le calcul MAC est CORRECT!');
    console.log('   L\'algorithme est conforme à la documentation Section 6.b');
} else {
    console.log('\n❌ ÉCHEC: Le calcul MAC est INCORRECT!');
    console.log('   Il y a un problème dans l\'algorithme de calcul');

    // Test avec différents séparateurs
    console.log('\n🔍 Test avec séparateurs uniformes:');
    const testSep = '|| |';
    const testString = secret + testSep + sessionKey + testSep + id + testSep + uid;
    const testMAC = crypto.createHash('sha1').update(testString).digest('base64');
    console.log(`   Sep uniforme "|| |" (1 espace): ${testMAC}`);
    console.log(`   Match: ${testMAC === expectedMAC ? '✅ OUI' : '❌ NON'}`);
}

console.log('\n' + '='.repeat(80));
