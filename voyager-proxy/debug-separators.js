// Debug: Vérifier les séparateurs byte par byte
import crypto from 'crypto';

const sharedSecret = 'Dherbomez';
const sessionKey = '1766411915.42938';
const jsonRpcId = '2';
const uid = 'final-test-001';

// Définir les séparateurs
const sep1 = '|| |';   // 2 barres + 1 espace + 1 barre
const sep2 = '||  |';  // 2 barres + 2 espaces + 1 barre
const sep3 = '|| |';   // 2 barres + 1 espace + 1 barre

console.log('=== ANALYSE DES SÉPARATEURS ===');
console.log('Sep1:', JSON.stringify(sep1), 'Longueur:', sep1.length, 'Bytes:', Buffer.from(sep1).toString('hex'));
console.log('Sep2:', JSON.stringify(sep2), 'Longueur:', sep2.length, 'Bytes:', Buffer.from(sep2).toString('hex'));
console.log('Sep3:', JSON.stringify(sep3), 'Longueur:', sep3.length, 'Bytes:', Buffer.from(sep3).toString('hex'));

// Construire la chaîne MAC
const macString = sharedSecret + sep1 + sessionKey + sep2 + jsonRpcId + sep3 + uid;

console.log('\n=== CHAÎNE MAC ===');
console.log('MAC String:', macString);
console.log('Longueur totale:', macString.length);

// Méthode 1: SHA1 → Hex → Base64
const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
const macHexToBase64 = Buffer.from(hexHash, 'utf8').toString('base64');

console.log('\n=== HACHAGE ===');
console.log('SHA1 (hex):', hexHash);
console.log('MAC (Hex→Base64):', macHexToBase64);

// Méthode 2: Direct
const macDirect = crypto.createHash('sha1').update(macString).digest('base64');
console.log('MAC (Direct Binary→Base64):', macDirect);

// Vérifier chaque partie
console.log('\n=== DÉCOMPOSITION ===');
console.log('1. SharedSecret:', JSON.stringify(sharedSecret));
console.log('2. Sep1:', JSON.stringify(sep1));
console.log('3. SessionKey:', JSON.stringify(sessionKey));
console.log('4. Sep2:', JSON.stringify(sep2));
console.log('5. ID:', JSON.stringify(jsonRpcId));
console.log('6. Sep3:', JSON.stringify(sep3));
console.log('7. UID:', JSON.stringify(uid));
