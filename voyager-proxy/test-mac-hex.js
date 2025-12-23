import crypto from 'crypto';

// Paramètres
const sharedSecret = 'Dherbomez';
const sessionKey = '1766410829.75612';
const jsonRpcId = '2';
const uid = '055bcdac-e149-4d67-b9ad-5d8a1148cdad';

// Construction de la chaîne MAC avec séparateurs asymétriques
const sep1 = '|| |';   // 1 espace
const sep2 = '||  |';  // 2 ESPACES
const sep3 = '|| |';   // 1 espace

const macString = sharedSecret + sep1 + sessionKey + sep2 + jsonRpcId + sep3 + uid;

console.log('MAC String:', macString);
console.log('');

// Méthode 1: SHA1 → Hex → Base64 (comme RemoteSetRoboTargetManagerMode)
const hexHash = crypto.createHash('sha1').update(macString).digest('hex');
const macHexToBase64 = Buffer.from(hexHash, 'utf8').toString('base64');

console.log('Méthode HEX→Base64 (NDA DOC):');
console.log('  SHA1 (hex):', hexHash);
console.log('  MAC:', macHexToBase64);
console.log('');

// Méthode 2: SHA1 direct → Base64
const macDirectBase64 = crypto.createHash('sha1').update(macString).digest('base64');

console.log('Méthode Direct Binary→Base64:');
console.log('  MAC:', macDirectBase64);
console.log('');

// Commande à envoyer
const command = {
  method: 'RemoteRoboTargetGetSet',
  params: {
    ProfileName: '',
    RefGuidSet: '',
    UID: uid,
    MAC: macHexToBase64  // Utilise Hex→Base64
  },
  id: parseInt(jsonRpcId)
};

console.log('Commande avec HEX→Base64:');
console.log(JSON.stringify(command, null, 2));
