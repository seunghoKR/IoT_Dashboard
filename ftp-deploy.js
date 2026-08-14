import * as ftp from 'basic-ftp'
import * as path from 'path'
import { fileURLToPath } from 'url'

const __filename = fileURLToPath(import.meta.url)
const __dirname = path.dirname(__filename)

async function uploadToIwinv() {
  const client = new ftp.Client()
  client.ftp.verbose = true

  try {
    console.log('🚀 iwinv FTP 서버에 연결 중: 115.68.168.215...')
    await client.access({
      host: '115.68.168.215',
      user: 'nuriohga',
      password: 'seungho0409#',
      secure: false
    })

    console.log('📁 원격 디렉토리 생성 및 이동: /public_html/IoT_Dashboard...')
    await client.ensureDir('/public_html/IoT_Dashboard')

    const localDirPath = path.join(__dirname, 'web_deploy')
    console.log(`📤 로컬 파일 업로드 중: ${localDirPath} -> /public_html/IoT_Dashboard...`)
    await client.uploadFromDir(localDirPath)

    console.log('✅ iwinv 호스팅 서버로 업로드 성공 완료!')
  } catch (err) {
    console.error('❌ FTP 업로드 오류:', err)
  }
  client.close()
}

uploadToIwinv()
