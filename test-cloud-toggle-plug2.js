import crypto from 'crypto'

const accessId = 'qsdjvehhx7n8ptuth45v'
const accessSecret = 'f1b450e443494a30950e9ad0095e201f'
const endpoint = 'https://openapi.tuyaus.com'

// 두 번째 스마트 플러그 ID
const deviceId2 = '42362638a4e57cb3cd0b'

function calcSign(accessId, secret, t, accessToken = '', httpMethod = 'GET', url = '', bodyStr = '') {
  const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
  const stringToSign = [httpMethod, contentHash, '', url].join('\n')
  const signStr = accessId + accessToken + t + stringToSign
  return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
}

async function testPlug2Control() {
  console.log(`🔌 [Tuya Cloud] 2번 스마트 플러그 (${deviceId2}) 직접 제어 테스트 시작...`)

  // 1. Token 발급
  const t = Date.now().toString()
  const tokenUrl = '/v1.0/token?grant_type=1'
  const tokenSign = calcSign(accessId, accessSecret, t, '', 'GET', tokenUrl)

  const tokenRes = await fetch(`${endpoint}${tokenUrl}`, {
    headers: { client_id: accessId, sign: tokenSign, t: t, sign_method: 'HMAC-SHA256' }
  })
  const tokenData = await tokenRes.json()
  console.log('🔑 Token 응답:', tokenData)

  if (!tokenData.success) {
    console.error('❌ 토큰 발급 실패!')
    return
  }

  const token = tokenData.result.access_token

  // 2. 2번 장치 스위치 전원 ON 명령
  const cmdT = Date.now().toString()
  const cmdUrl = `/v1.0/devices/${deviceId2}/commands`
  const bodyObj = { commands: [{ code: 'switch_1', value: true }] }
  const bodyStr = JSON.stringify(bodyObj)
  const cmdSign = calcSign(accessId, accessSecret, cmdT, token, 'POST', cmdUrl, bodyStr)

  console.log('⚡ 2번 플러그 전원 ON 명령 발송 중...')

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
  console.log('✅ 2번 플러그 제어 응답:', cmdResult)
}

testPlug2Control()
