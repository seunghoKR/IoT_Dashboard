import net from 'net'
import fs from 'fs'
import path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

const FTP_HOST = '115.68.168.215'
const FTP_USER = 'nuriohga'
const FTP_PASS = 'seungho0409#'
const REMOTE_DIR = '/public_html/IoT_Dashboard'

class SimpleFtp {
  constructor(host, user, pass) {
    this.host = host
    this.user = user
    this.pass = pass
    this.socket = null
    this.dataSocket = null
  }

  connect() {
    return new Promise((resolve, reject) => {
      this.socket = net.connect(21, this.host, () => {
        console.log(`📡 FTP 서버 연결 성공: ${this.host}`)
      })

      let buffer = ''
      this.socket.on('data', async (data) => {
        buffer += data.toString()
        const lines = buffer.split('\r\n')
        buffer = lines.pop()

        for (const line of lines) {
          console.log('< ' + line)
          if (line.startsWith('220 ')) {
            this.send(`USER ${this.user}`)
          } else if (line.startsWith('331 ')) {
            this.send(`PASS ${this.pass}`)
          } else if (line.startsWith('230 ')) {
            console.log('✅ FTP 로그인 성공!')
            resolve()
          } else if (line.startsWith('530 ')) {
            reject(new Error('FTP 로그인 실패: ' + line))
          }
        }
      })

      this.socket.on('error', reject)
    })
  }

  send(cmd) {
    console.log('> ' + cmd)
    this.socket.write(cmd + '\r\n')
  }

  async sendCmd(cmd, expectedCode) {
    return new Promise((resolve, reject) => {
      const onData = (data) => {
        const str = data.toString()
        console.log('< ' + str.trim())
        if (str.startsWith(expectedCode) || str.startsWith('200') || str.startsWith('250') || str.startsWith('226')) {
          this.socket.removeListener('data', onData)
          resolve(str)
        } else if (str.startsWith('5') || str.startsWith('4')) {
          this.socket.removeListener('data', onData)
          reject(new Error(str))
        }
      }
      this.socket.on('data', onData)
      this.send(cmd)
    })
  }

  async pasv() {
    return new Promise((resolve, reject) => {
      const onData = (data) => {
        const str = data.toString()
        if (str.startsWith('227 ')) {
          this.socket.removeListener('data', onData)
          const match = str.match(/\((\d+),(\d+),(\d+),(\d+),(\d+),(\d+)\)/)
          if (match) {
            const ip = `${match[1]}.${match[2]}.${match[3]}.${match[4]}`
            const port = parseInt(match[5]) * 256 + parseInt(match[6])
            resolve({ ip, port })
          } else {
            reject(new Error('PASV 응답 파싱 실패'))
          }
        }
      }
      this.socket.on('data', onData)
      this.send('PASV')
    })
  }

  async mkdir(dir) {
    try {
      await this.sendCmd(`MKD ${dir}`, '257')
    } catch (e) {
      // 이미 존재하는 경우 무시
    }
  }

  async uploadFile(localPath, remotePath) {
    const pasvInfo = await this.pasv()
    const fileData = fs.readFileSync(localPath)

    return new Promise((resolve, reject) => {
      const dataConn = net.connect(pasvInfo.port, this.host, () => {
        console.log(`📤 데이터 연결 성공: ${remotePath} (${fileData.length} bytes)`)
        dataConn.write(fileData, () => {
          dataConn.end()
        })
      })

      dataConn.on('close', () => {
        console.log(`✅ 데이터 전송 완료: ${remotePath}`)
      })

      dataConn.on('error', reject)

      this.sendCmd(`STOR ${remotePath}`, '150').then(() => {
        resolve()
      }).catch(reject)
    })
  }

  close() {
    if (this.socket) {
      this.send('QUIT')
      this.socket.destroy()
    }
  }
}

async function run() {
  const ftp = new SimpleFtp(FTP_HOST, FTP_USER, FTP_PASS)
  try {
    await ftp.connect()
    await ftp.mkdir('/public_html/IoT_Dashboard')
    await ftp.sendCmd('CWD /public_html/IoT_Dashboard', '250')

    const localDir = path.join(__dirname, 'web_deploy')
    const files = fs.readdirSync(localDir)

    for (const file of files) {
      const localPath = path.join(localDir, file)
      const remotePath = `/public_html/IoT_Dashboard/${file}`
      if (fs.statSync(localPath).isFile()) {
        console.log(`🚀 업로드 시작: ${file}...`)
        await ftp.uploadFile(localPath, remotePath)
      }
    }

    console.log('\n🎉 [성공!] 모든 배포 파일이 /public_html/IoT_Dashboard 호스팅 폴더로 자동 업로드되었습니다!')
  } catch (err) {
    console.error('❌ 업로드 오류:', err.message)
  } finally {
    ftp.close()
  }
}

run()
