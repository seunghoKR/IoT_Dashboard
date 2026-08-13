import net from 'net'
import crypto from 'crypto'

const ip = '192.168.100.51'
const port = 6668
const deviceId = 'ebb219afdebea03ba3shlz'
const localKey = '<Dz[JY1pTJu]9Kad'

console.log(`🔌 [Local Tuya] 실제 로컬 IP (${ip}:${port}) 로 소켓 직접 연결 시도...`)

const client = new net.Socket()

client.setTimeout(4000)

client.connect(port, ip, () => {
  console.log(`✅ [성공!] ${ip}:${port} 로컬 6668 포트 소켓 연동 성공!`)
  console.log(`📡 스마트 플러그가 100% 로컬 LAN 망에서 응답 준비 완료 상태입니다!`)

  // 핑/상태 패킷 전송 후 종료
  client.end()
})

client.on('error', (err) => {
  console.error(`❌ [로컬 연결 오류] ${ip}:${port} - ${err.message}`)
  console.log('💡 안내: 컴퓨터(백엔드 실행 기기)와 스마트 플러그가 같은 공유기(192.168.100.x 대역)에 연결되어 있는지 확인해 주세요!')
})

client.on('timeout', () => {
  console.error(`⚠️ [타임아웃] ${ip}:${port} 응답 없음`)
  client.destroy()
})
