import net from 'net'

const easyRollDevices = [
  { name: '커피마실 1번 블라인드', ip: '192.168.100.57', deviceId: 'EZS15N1100036', mac: 'AC:67:B2:D2:C6:64' },
  { name: '커피마실 2번 블라인드', ip: '192.168.100.77', deviceId: 'EZS15N1100039', mac: 'F0:08:D1:60:11:30' },
  { name: '커피마실 3번 블라인드', ip: '192.168.100.82', deviceId: 'EZS15N1100022', mac: 'F0:08:D1:60:15:28' }
]

console.log('☕ [커피마실 카페] 이지롤 EASY-ROLL Wi-Fi 롤블라인드 3대 소켓 테스트 시작...')

easyRollDevices.forEach(dev => {
  // 이지롤/Dooya 표준 포트 48899 & 8899 & 6668
  [8899, 48899, 6668].forEach(port => {
    const client = new net.Socket()
    client.setTimeout(2500)
    client.connect(port, dev.ip, () => {
      console.log(`✅ [성공!] ${dev.name} (${dev.ip}:${port}) 로컬 포트 연동 성공!`)
      client.end()
    })
    client.on('error', () => {})
    client.on('timeout', () => client.destroy())
  })
})
