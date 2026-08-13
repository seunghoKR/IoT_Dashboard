import crypto from 'crypto'

const accessId = 'qsdjvehhx7n8ptuth45v'
const accessSecret = 'f1b450e443494a30950e9ad0095e201f'
const endpoint = 'https://openapi.tuyaus.com'

const plug1_id = 'ebb219afdebea03ba3shlz'   // 책상등
const plug2_id = '42362638a4e57cb3cd0b'   // 3D 프린터

function calcSign(accessId, secret, t, accessToken = '', httpMethod = 'GET', url = '', bodyStr = '') {
  const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
  const stringToSign = [httpMethod, contentHash, '', url].join('\n')
  const signStr = accessId + accessToken + t + stringToSign
  return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
}

async function testDualPlugs() {
  console.log('🔌 [Tuya Cloud & Local] 1번(책상등) & 2번(3D 프린터) 듀얼 제어 테스트 시작...')

  // 1. Token 발급
  const t = Date.now().toString()
  const tokenUrl = '/v1.0/token?grant_type=1'
  const tokenSign = calcSign(accessId, accessSecret, t, '', 'GET', tokenUrl)

  const tokenRes = await fetch(`${endpoint}${tokenUrl}`, {
    headers: { client_id: accessId, sign: tokenSign, t: t, sign_method: 'HMAC-SHA256' }
  })
  const tokenData = await tokenRes.json()
  if (!tokenData.success) {
    console.error('❌ 토큰 발급 실패:', tokenData)
    return
  }
  const token = tokenData.result.access_token

  // 2. 1번 장치 (책상등) 전원 ON/OFF 토글
  const cmdT1 = Date.now().toString()
  const cmdUrl1 = `/v1.0/devices/${plug1_id}/commands`
  const bodyObj1 = { commands: [{ code: 'switch_1', value: true }] }
  const bodyStr1 = JSON.stringify(bodyObj1)
  const cmdSign1 = calcSign(accessId, accessSecret, cmdT1, token, 'POST', cmdUrl1, bodyStr1)

  const res1 = await fetch(`${endpoint}${cmdUrl1}`, {
    method: 'POST',
    headers: { client_id: accessId, access_token: token, sign: cmdSign1, t: cmdT1, sign_method: 'HMAC-SHA256', 'Content-Type': 'application/json' },
    body: bodyStr1
  })
  console.log('💡 1번 [책상등] 응답:', await res1.json())

  // 3. 2번 장치 (3D 프린터) 전원 ON/OFF 토글
  const cmdT2 = Date.now().toString()
  const cmdUrl2 = `/v1.0/devices/${plug2_id}/commands`
  const bodyObj2 = { commands: [{ code: 'switch_1', value: true }] }
  const bodyStr2 = JSON.stringify(bodyObj2)
  const cmdSign2 = calcSign(accessId, accessSecret, cmdT2, token, 'POST', cmdUrl2, bodyStr2)

  const res2 = await fetch(`${endpoint}${cmdUrl2}`, {
    method: 'POST',
    headers: { client_id: accessId, access_token: token, sign: cmdSign2, t: cmdT2, sign_method: 'HMAC-SHA256', 'Content-Type': 'application/json' },
    body: bodyStr2
  })
  console.log('🖨️ 2번 [3D 프린터] 응답:', await res2.json())
}

testDualPlugs()
