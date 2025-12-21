// Node.js 18+ has built-in fetch
const API_URL = 'http://localhost:3002';

async function testCreateTarget() {
  console.log('🧪 Testing target creation with corrected parameters...\n');

  // 1. Create Set
  console.log('1️⃣ Creating Set...');
  const setData = {
    Guid: 'a1b2c3d4-e5f6-7890-abcd-ef1234567890',
    Name: 'Test Set - Auto Test',
    ProfileName: 'Default.v2y',
    IsDefault: false,
    Tag: 'auto_test',
    Status: 0,
    Note: 'Created by automated test script'
  };

  try {
    const setResponse = await fetch(`${API_URL}/api/robotarget/sets`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': 'test-key-123'
      },
      body: JSON.stringify(setData)
    });

    const setResult = await setResponse.json();
    console.log('✅ Set creation response:', JSON.stringify(setResult, null, 2));

    if (!setResult.success) {
      console.error('❌ Set creation failed:', setResult);
      return;
    }

    // 2. Create Target
    console.log('\n2️⃣ Creating Target...');
    const targetData = {
      GuidTarget: 'b2c3d4e5-f6a7-8901-bcde-f12345678901',
      RefGuidSet: setData.Guid,
      RefGuidBaseSequence: '',
      TargetName: 'M42 - Test Target',
      RAJ2000: 5.588055555555555,
      DECJ2000: -5.391111111111112,
      PA: 0,
      DateCreation: Math.floor(Date.now() / 1000),
      Status: 0,
      Priority: 0,
      IsRepeat: true,
      Repeat: 1,
      C_Mask: 'BDE',
      C_AltMin: 30,
      C_HAStart: -3,
      C_HAEnd: 3
    };

    const targetResponse = await fetch(`${API_URL}/api/robotarget/targets`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-API-Key': 'test-key-123'
      },
      body: JSON.stringify(targetData)
    });

    const targetResult = await targetResponse.json();
    console.log('✅ Target creation response:', JSON.stringify(targetResult, null, 2));

    if (targetResult.success) {
      console.log('\n🎉 SUCCESS! Target created successfully!');
    } else {
      console.error('\n❌ FAILED! Target creation failed:', targetResult);
    }

  } catch (error) {
    console.error('❌ Test failed with error:', error.message);
  }
}

testCreateTarget();
