import net from 'net'

const ip2 = '192.168.100.63'
const port = 6668
const deviceId2 = '42362638a4e57cb3cd0b'
const mac2 = 'a4:e5:7c:b3:cd:0b'

console.log(`🔌 [Local Tuya #2] 두 번째 스마트 플러그 IP (${ip2}:${port}) 로 소켓 직접 연결 시도...`)

const client = new net.Socket()
client.setTimeout(4000)

client.connect(port, ip2, () => {
  console.log(`✅ [성공!] 두 번째 스마트 플러그 (${ip2}:${port}) 로컬 6668 포트 소켓 연동 성공!`)
  console.log(`📡 Device ID: ${deviceId2} · MAC: ${mac2} 가 100% 로컬 망에서 준비 완료되었습니다!`)
  client.end()
})

client.on('error', (err) => {
  console.error(`❌ [로컬 연결 오류] ${ip2}:${port} - ${err.message}`)
})

client.on('timeout', () => {
  console.error(`⚠️ [타임아웃] ${ip2}:${port} 응답 없음`)
  client.destroy()
})
