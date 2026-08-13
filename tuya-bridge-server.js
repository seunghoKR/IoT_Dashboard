import http from 'http'
import crypto from 'crypto'
import dgram from 'dgram'

const accessId = 'qsdjvehhx7n8ptuth45v'
const accessSecret = 'f1b450e443494a30950e9ad0095e201f'
const endpoint = 'https://openapi.tuyaus.com'

const cafePublicIp = '180.227.195.211'
const cafeBlinds = {
  1: { ip: cafePublicIp, port: 8891, localIp: '192.168.100.57', state: 100 },
  2: { ip: cafePublicIp, port: 8892, localIp: '192.168.100.77', state: 100 },
  3: { ip: cafePublicIp, port: 8893, localIp: '192.168.100.82', state: 100 }
}

const deviceMap = {
  'ebb219afdebea03ba3shlz': { name: '💡 1번 스마트 플러그 [책상등]', ip: '192.168.100.51', state: false },
  '42362638a4e57cb3cd0b': { name: '🖨️ 2번 스마트 플러그 [3D 프린터]', ip: '192.168.100.63', state: false }
}

let accessToken = ''
let tokenExpireTime = 0

function calcSign(accessId, secret, t, accessToken = '', httpMethod = 'GET', url = '', bodyStr = '') {
  const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
  const stringToSign = [httpMethod, contentHash, '', url].join('\n')
  const signStr = accessId + accessToken + t + stringToSign
  return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
}

async function getAccessToken() {
  if (accessToken && Date.now() < tokenExpireTime - 60000) {
    return accessToken
  }
  const t = Date.now().toString()
  const tokenUrl = '/v1.0/token?grant_type=1'
  const tokenSign = calcSign(accessId, accessSecret, t, '', 'GET', tokenUrl)

  try {
    const tokenRes = await fetch(`${endpoint}${tokenUrl}`, {
      headers: { client_id: accessId, sign: tokenSign, t: t, sign_method: 'HMAC-SHA256' }
    })
    const tokenData = await tokenRes.json()
    if (tokenData.success && tokenData.result) {
      accessToken = tokenData.result.access_token
      tokenExpireTime = Date.now() + tokenData.result.expire_time * 1000
      return accessToken
    }
  } catch (e) {}
  return null
}

async function fetchRealDeviceStatus(id) {
  const token = await getAccessToken()
  if (!token) return deviceMap[id]?.state ?? false

  const t = Date.now().toString()
  const statusUrl = `/v1.0/devices/${id}/status`
  const statusSign = calcSign(accessId, accessSecret, t, token, 'GET', statusUrl)

  try {
    const res = await fetch(`${endpoint}${statusUrl}`, {
      headers: { client_id: accessId, access_token: token, sign: statusSign, t: t, sign_method: 'HMAC-SHA256' }
    })
    const data = await res.json()
    if (data.success && Array.isArray(data.result)) {
      const sw = data.result.find(item => item.code === 'switch_1' || item.code === 'switch')
      if (sw && typeof sw.value === 'boolean') {
        if (deviceMap[id]) deviceMap[id].state = sw.value
        return sw.value
      }
    }
  } catch (e) {}

  return deviceMap[id]?.state ?? false
}

async function togglePlugById(targetId) {
  const dev = deviceMap[targetId] || deviceMap['ebb219afdebea03ba3shlz']
  await fetchRealDeviceStatus(targetId)
  dev.state = !dev.state

  const token = await getAccessToken()
  if (token) {
    const t = Date.now().toString()
    const cmdUrl = `/v1.0/devices/${targetId}/commands`
    const bodyObj = { commands: [{ code: 'switch_1', value: dev.state }] }
    const bodyStr = JSON.stringify(bodyObj)
    const cmdSign = calcSign(accessId, accessSecret, t, token, 'POST', cmdUrl, bodyStr)

    try {
      const res = await fetch(`${endpoint}${cmdUrl}`, {
        method: 'POST',
        headers: {
          client_id: accessId,
          access_token: token,
          sign: cmdSign,
          t: t,
          sign_method: 'HMAC-SHA256',
          'Content-Type': 'application/json'
        },
        body: bodyStr
      })
      const result = await res.json()
      return { success: true, deviceId: targetId, targetState: dev.state, name: dev.name, result }
    } catch (err) {}
  }
  return { success: true, deviceId: targetId, targetState: dev.state, name: dev.name }
}

const server = http.createServer(async (req, res) => {
  res.setHeader('Access-Control-Allow-Origin', '*')
  res.setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
  res.setHeader('Access-Control-Allow-Headers', 'Content-Type')

  if (req.method === 'OPTIONS') {
    res.writeHead(200)
    res.end()
    return
  }

  if (req.url.startsWith('/api/tuya/devices-status')) {
    try {
      const st1 = await fetchRealDeviceStatus('ebb219afdebea03ba3shlz')
      const st2 = await fetchRealDeviceStatus('42362638a4e57cb3cd0b')
      res.writeHead(200, { 'Content-Type': 'application/json' })
      res.end(JSON.stringify({
        success: true,
        devices: {
          'ebb219afdebea03ba3shlz': { ...deviceMap['ebb219afdebea03ba3shlz'], state: st1 },
          '42362638a4e57cb3cd0b': { ...deviceMap['42362638a4e57cb3cd0b'], state: st2 }
        },
        cafeBlinds: cafeBlinds
      }))
    } catch (err) {
      res.writeHead(500, { 'Content-Type': 'application/json' })
      res.end(JSON.stringify({ error: err.message }))
    }
  } else if (req.url.startsWith('/api/tuya/toggle-device')) {
    const urlObj = new URL(req.url, 'http://localhost:3000')
    const targetId = urlObj.searchParams.get('id') || 'ebb219afdebea03ba3shlz'
    try {
      const result = await togglePlugById(targetId)
      res.writeHead(200, { 'Content-Type': 'application/json' })
      res.end(JSON.stringify(result))
    } catch (err) {
      res.writeHead(500, { 'Content-Type': 'application/json' })
      res.end(JSON.stringify({ error: err.message }))
    }
  } else {
    res.writeHead(200, { 'Content-Type': 'application/json' })
    res.end(JSON.stringify({ success: true, devices: deviceMap }))
  }
})

server.listen(3000, () => {
  console.log('🚀 카페 원격 블라인드 3대 상태 쿼리 엔진 탑재 서버 가동 중: http://localhost:3000')
})
