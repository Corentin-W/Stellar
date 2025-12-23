// Test Open API RoboTarget (MD5 simple)
import crypto from 'crypto';

const sharedSecret = 'Dherbomez';
const uid = 'test-open-api-001';

// Open API formula: MD5(SharedSecret + UID)
// NO Base64, NO separators, just simple MD5 hex
const macString = sharedSecret + uid;
const mac = crypto.createHash('md5').update(macString).digest('hex');

console.log('=== OPEN API TEST ===');
console.log('MAC String:', macString);
console.log('MD5 (hex):', mac);
console.log('\nCommande à envoyer:');
console.log(JSON.stringify({
  method: 'RemoteOpenRoboTargetGetTargetList',
  params: {
    UID: uid,
    MAC: mac
  },
  id: 100
}, null, 2));
