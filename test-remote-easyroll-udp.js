import net from 'net'
import dgram from 'dgram'

const cafePublicIp = '180.227.195.211'
const remoteBlinds = [
  { name: '1번 블라인드 (포트 8891)', port: 8891, localIp: '192.168.100.57' },
  { name: '2번 블라인드 (포트 8892)', port: 8892, localIp: '192.168.100.77' },
  { name: '3번 블라인드 (포트 8893)', port: 8893, localIp: '192.168.100.82' }
]

console.log(`☕ [커피마실 카페] 180.227.195.211 -> 내부 48899 (TCP/UDP) 포트포워드 연동 테스트 시작...`)

// UDP Broadcast / Unicast 패킷 전송 (Easy-Roll Dooya UDP 프로토콜)
const udpClient = dgram.createSocket('udp4')
const discoveryMsg = Buffer.from('cmd=read&dev=all')

remoteBlinds.forEach(blind => {
  udpClient.send(discoveryMsg, blind.port, cafePublicIp, (err) => {
    if (!err) {
      console.log(`📡 [UDP 패킷 전송 성공!] ${blind.name} -> ${cafePublicIp}:${blind.port} (내부 ${blind.localIp}:48899 로 전달됨)`)
    }
  })
})

setTimeout(() => {
  udpClient.close()
}, 3000)
