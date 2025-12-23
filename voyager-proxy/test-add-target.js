// Test AddTarget command with complete parameters
import fetch from 'node-fetch';
import fs from 'fs';

const testAddTarget = async () => {
  try {
    // Load complete target parameters
    const targetParams = JSON.parse(
      fs.readFileSync('./test-complete-target.json', 'utf8')
    );

    // Use test-mac endpoint to send AddTarget command
    const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetAddTarget',
        params: targetParams.params,
        macFormula: {
          sep1: '||:||',
          sep2: '||:||',
          sep3: '||:||'
        }
      })
    });

    const result = await response.json();
    console.log('=== ADD TARGET TEST ===');
    console.log(JSON.stringify(result, null, 2));

    if (result.success) {
      console.log('\n✅ AddTarget succeeded!');
      console.log(`Target created: ${targetParams.params.TargetName}`);
    } else {
      console.log('\n❌ AddTarget failed or timeout');
    }

  } catch (error) {
    console.error('Error:', error.message);
  }
};

testAddTarget();
