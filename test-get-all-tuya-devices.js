import crypto from 'crypto'

const accessId = 'qsdjvehhx7n8ptuth45v'
const accessSecret = 'f1b450e443494a30950e9ad0095e201f'
const endpoint = 'https://openapi.tuyaus.com'

function calcSign(accessId, secret, t, accessToken = '', httpMethod = 'GET', url = '', bodyStr = '') {
  const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
  const stringToSign = [httpMethod, contentHash, '', url].join('\n')
  const signStr = accessId + accessToken + t + stringToSign
  return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
}

async function getAllDevices() {
  console.log('🔍 Tuya 계정 내 모든 실제 디바이스 목록 조회 시작...')

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
  const uid = tokenData.result.uid

  // 2. 유저별 디바이스 목록 조회 (/v1.0/users/{uid}/devices 또는 프로젝트 디바이스)
  const devT = Date.now().toString()
  const devUrl = `/v1.0/users/${uid}/devices`
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
  console.log('📱 프로젝트 전체 디바이스 목록:', JSON.stringify(devData, null, 2))

  // 3. 만약 1번 장치(ebb219afdebea03ba3shlz) 단독 조회가 되는지 재확인
  const singleT = Date.now().toString()
  const singleUrl = `/v1.0/devices/ebb219afdebea03ba3shlz`
  const singleSign = calcSign(accessId, accessSecret, singleT, token, 'GET', singleUrl)

  const singleRes = await fetch(`${endpoint}${singleUrl}`, {
    headers: {
      client_id: accessId,
      access_token: token,
      sign: singleSign,
      t: singleT,
      sign_method: 'HMAC-SHA256'
    }
  })

  const singleData = await singleRes.json()
  console.log('🔌 1번 장치 직접 응답:', JSON.stringify(singleData, null, 2))
}

getAllDevices()
