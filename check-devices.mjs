import crypto from 'crypto'

const accessId = 'qsdjvehhx7n8ptuth45v'
const accessSecret = 'f1b450e443494a30950e9ad0095e201f'
const endpoint = 'https://openapi.tuyaus.com'

const devices = [
  { name: '1동 개폐기 (4-433)', id: 'eb654aa2437462ea40dfjw' },
  { name: '송풍기 (smart plug)', id: '42362638a4e57cb3cd0b' },
  { name: '양수기 (Smart Plug)', id: 'ebb219afdebea03ba3shlz' }
]

function calcSign(accessId, secret, t, accessToken = '', httpMethod = 'GET', url = '', bodyStr = '') {
  const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
  const stringToSign = [httpMethod, contentHash, '', url].join('\n')
  const signStr = accessId + accessToken + t + stringToSign
  return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
}

async function checkAllTuyaDevices() {
  console.log('🔍 Tuya 클라우드 연동 상태 종합 점검 시작...')

  // 1. 토큰 발급
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
  console.log('✅ Tuya Cloud OpenAPI 인증 토큰 발급 성공!')

  // 2. 각 기기 상태 조회
  for (const dev of devices) {
    const devT = Date.now().toString()
    const devUrl = `/v1.0/devices/${dev.id}`
    const devSign = calcSign(accessId, accessSecret, devT, token, 'GET', devUrl)

    const res = await fetch(`${endpoint}${devUrl}`, {
      headers: {
        client_id: accessId,
        access_token: token,
        sign: devSign,
        t: devT,
        sign_method: 'HMAC-SHA256'
      }
    })
    const data = await res.json()
    console.log(`\n========================================`)
    console.log(`📌 기기: ${dev.name} [ID: ${dev.id}]`)
    if (data.success) {
      console.log(`- 클라우드 등록 이름: ${data.result.name}`)
      console.log(`- 온라인 상태: ${data.result.online ? '🟢 Online (연결됨)' : '🔴 Offline'}`)
      console.log(`- 카테고리/제품: ${data.result.category} / ${data.result.product_name}`)
      console.log(`- IP / Local Key: ${data.result.ip} / ${data.result.local_key ? '정상보유' : '없음'}`)
      console.log(`- 현재 상태(Status):`, JSON.stringify(data.result.status))
    } else {
      console.log(`❌ 조회 실패:`, data)
    }
  }
}

checkAllTuyaDevices()
