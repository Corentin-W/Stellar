// Test UpdateSet command
import fetch from 'node-fetch';

const testUpdateSet = async () => {
  try {
    // Use test-mac endpoint to send UpdateSet command
    const response = await fetch('http://localhost:3003/api/robotarget/test-mac', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        method: 'RemoteRoboTargetUpdateSet',
        params: {
          Guid: '39195ee5-2618-4204-bad7-af8779717eb6', // Set "Nebuleuse" existant
          Name: 'Nebuleuse - Updated',
          ProfileName: '2025-04-27_EEYE_TOA150_F1100_GM2000HPS_ASI6200mm_v10.v2y',
          IsDefault: false,
          Status: 0,
          Tag: 'test-api',
          Note: 'Updated via Reserved API test',
          UID: 'test-update-set-001'
        },
        macFormula: {
          sep1: '||:||',
          sep2: '||:||',
          sep3: '||:||'
        }
      })
    });

    const result = await response.json();
    console.log('=== UPDATE SET TEST ===');
    console.log(JSON.stringify(result, null, 2));

    if (result.success) {
      console.log('\n✅ UpdateSet succeeded!');
    } else {
      console.log('\n❌ UpdateSet failed or timeout');
    }

  } catch (error) {
    console.error('Error:', error.message);
  }
};

testUpdateSet();
