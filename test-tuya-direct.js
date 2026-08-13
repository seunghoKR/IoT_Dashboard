import crypto from 'crypto'

const accessId = 'qsdjvehhx7n8ptuth45v'
const accessSecret = 'f1b450e443494a30950e9ad0095e201f'
const endpoint = 'https://openapi.tuyaus.com'
const deviceId = 'ebb219afdebea03ba3shlz'

function calcSign(accessId, secret, t, accessToken = '', httpMethod = 'GET', url = '', bodyStr = '') {
  const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
  const stringToSign = [httpMethod, contentHash, '', url].join('\n')
  const signStr = accessId + accessToken + t + stringToSign
  return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
}

async function testTuya() {
  console.log('🔌 Tuya Open API 연결 및 실제 디바이스 제어 테스트 시작...')

  // 1. Token 발급
  const t = Date.now().toString()
  const tokenUrl = '/v1.0/token?grant_type=1'
  const tokenSign = calcSign(accessId, accessSecret, t, '', 'GET', tokenUrl)

  const tokenRes = await fetch(`${endpoint}${tokenUrl}`, {
    headers: {
      client_id: accessId,
      sign: tokenSign,
      t: t,
      sign_method: 'HMAC-SHA256'
    }
  })

  const tokenData = await tokenRes.json()
  console.log('🔑 Token 응답:', tokenData)

  if (!tokenData.success) {
    console.error('❌ 토큰 발급 실패!')
    return
  }

  const token = tokenData.result.access_token

  // 2. 디바이스 정보 조회
  const devT = Date.now().toString()
  const devUrl = `/v1.0/devices/${deviceId}`
  const devSign = calcSign(accessId, accessSecret, devT, token, 'GET', devUrl)

  const devRes = await fetch(`${endpoint}${devUrl}`, {
    headers: {
      client_id: accessId,
      access_token: token,
      sign: devSign,
      t: devT,
      sign_method: 'HMAC-SHA256'
    }
  })

  const devData = await devRes.json()
  console.log('📱 디바이스 상태:', JSON.stringify(devData, null, 2))

  // 3. 실제 제어 명령 (스마트 플러그 Toggle)
  const cmdT = Date.now().toString()
  const cmdUrl = `/v1.0/devices/${deviceId}/commands`
  
  // 현재 스위치 상태 확인 후 반대로 토글
  let curSwitch = true
  if (devData.result && devData.result.status) {
    const sw = devData.result.status.find(s => s.code.startsWith('switch'))
    if (sw) curSwitch = sw.value
  }
  const nextSwitch = !curSwitch

  const bodyObj = {
    commands: [{ code: 'switch_1', value: nextSwitch }]
  }
  const bodyStr = JSON.stringify(bodyObj)
  const cmdSign = calcSign(accessId, accessSecret, cmdT, token, 'POST', cmdUrl, bodyStr)

  console.log(`⚡ 스위치 제어 시도: [${curSwitch} -> ${nextSwitch}]`)

  const cmdRes = await fetch(`${endpoint}${cmdUrl}`, {
    method: 'POST',
    headers: {
      client_id: accessId,
      access_token: token,
      sign: cmdSign,
      t: cmdT,
      sign_method: 'HMAC-SHA256',
      'Content-Type': 'application/json'
    },
    body: bodyStr
  })

  const cmdResult = await cmdRes.json()
  console.log('✅ 제어 결과:', cmdResult)
}

testTuya()
