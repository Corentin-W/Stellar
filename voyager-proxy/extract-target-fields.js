// Extract field structure from existing Target
import fs from 'fs';

const data = JSON.parse(fs.readFileSync('./nebuleuse-targets.json', 'utf8'));
const target = data.result.ParamRet.list[0];

console.log('=== EXISTING TARGET STRUCTURE ===\n');
console.log('Target:', target.targetname);
console.log('GUID:', target.guid);
console.log('\n=== ALL FIELDS (alphabetical) ===\n');

const fields = Object.keys(target).sort();
fields.forEach(field => {
  const value = target[field];
  const type = typeof value;
  const valueStr = type === 'object' ? JSON.stringify(value) : value;
  console.log(`${field.padEnd(35)} ${type.padEnd(10)} ${valueStr}`);
});

console.log('\n=== NON-EMPTY/NON-ZERO FIELDS ===\n');
const nonEmpty = fields.filter(field => {
  const val = target[field];
  return val !== '' && val !== 0 && val !== '0' && val !== 'false' && val !== false && val !== null && !(Array.isArray(val) && val.length === 0);
});

nonEmpty.forEach(field => {
  const value = target[field];
  const valueStr = typeof value === 'object' ? JSON.stringify(value) : value;
  console.log(`${field.padEnd(35)} = ${valueStr}`);
});
