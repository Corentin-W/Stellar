import crypto from 'crypto';

// Test MAC generation with the exact example from official doc
const sharedSecret = 'pippo';
const sessionKey = '1652231344.88438';
const id = '5';
const uid = '0697f2f9-24e4-4850-84e9-18ea28b05fe9';

// Test different separators
const tests = [
  {
    name: 'UNIFORM || | (all same)',
    macString: sharedSecret + '|| |' + sessionKey + '|| |' + id + '|| |' + uid,
  },
  {
    name: 'UNIFORM ||| (all same)',
    macString: sharedSecret + '|||' + sessionKey + '|||' + id + '|||' + uid,
  },
  {
    name: 'NON-UNIFORM (1 space, 2 spaces, 1 space)',
    macString: sharedSecret + '|| |' + sessionKey + '||  |' + id + '|| |' + uid,
  },
];

console.log('Testing MAC formulas with official example from doc:\n');
console.log('NOTE: Doc says "hash reported are only for didactical scope", so we cannot verify!\n');

tests.forEach(({ name, macString }) => {
  const mac = crypto.createHash('sha1').update(macString).digest('base64');

  console.log(`Formula: ${name}`);
  console.log(`String: ${macString}`);
  console.log(`MAC: ${mac}`);
  console.log(`Expected (from doc): nWq/V98Laq+hFFdMvynnneAyKvk= (but it's fake)`);
  console.log('---\n');
});

// Test with your actual credentials
console.log('\nTesting with YOUR credentials (RemoteRoboTargetAddSet):\n');
const yourSecret = 'Dherbomez';
const yourSessionKey = '1766335570.49203'; // From your logs
const yourId = '2';
const yourUid = '480e0310-d3b0-43c0-8201-7e08f512d7a1'; // From your logs

const yourTests = [
  {
    name: 'UNIFORM || | (currently in code)',
    sep1: '|| |',
    sep2: '|| |',
    sep3: '|| |',
  },
  {
    name: 'NON-UNIFORM (Section 6.b)',
    sep1: '|| |',    // 1 space
    sep2: '||  |',   // 2 spaces
    sep3: '|| |',    // 1 space
  },
];

yourTests.forEach(({ name, sep1, sep2, sep3 }) => {
  const macString = yourSecret + sep1 + yourSessionKey + sep2 + yourId + sep3 + yourUid;
  const mac = crypto.createHash('sha1').update(macString).digest('base64');

  console.log(`Formula: ${name}`);
  console.log(`String: ${macString}`);
  console.log(`MAC: ${mac}`);
  console.log('---\n');
});

console.log('\n⚠️  We need to check which one Voyager accepts!');
