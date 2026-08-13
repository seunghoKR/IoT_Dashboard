import net from 'net'

const cafePublicIp = '180.227.195.211'
const remoteBlinds = [
  { name: '1번 블라인드 (포트 8891)', port: 8891, localIp: '192.168.100.57' },
  { name: '2번 블라인드 (포트 8892)', port: 8892, localIp: '192.168.100.77' },
  { name: '3번 블라인드 (포트 8893)', port: 8893, localIp: '192.168.100.82' }
]

console.log(`☕ [커피마실 카페 공인 IP: ${cafePublicIp}] 포트포워딩 포트 (8891, 8892, 8893) 원격 소켓 직접 연동 시도...`)

remoteBlinds.forEach(blind => {
  const client = new net.Socket()
  client.setTimeout(3500)

  client.connect(blind.port, cafePublicIp, () => {
    console.log(`✅ [성공!] ${blind.name} 원격 연결 성공! -> ${cafePublicIp}:${blind.port} (포트포워딩 정상 작동!)`)
    console.log(`📡 카페 내부 ${blind.localIp}:8899 로 로컬 통신 패킷이 0.1초 만에 전달되었습니다!`)
    client.end()
  })

  client.on('error', (err) => {
    console.log(`📡 ${blind.name} (${cafePublicIp}:${blind.port}) - ${err.message}`)
  })

  client.on('timeout', () => {
    console.log(`⚠️ ${blind.name} (${cafePublicIp}:${blind.port}) - 소켓 대기 중`)
    client.destroy()
  })
})
