/**
 * Tuya Open API 연동 서비스
 * Tuya Cloud OpenAPI (Western America Data Center)
 * 
 * - HMAC-SHA256 서명 기반 Tuya API 토큰 발급 및 자동 갱신
 * - Tuya 디바이스 상태 조회 (GET /v1.0/devices/{device_id})
 * - Tuya 디바이스 제어 (POST /v1.0/devices/{device_id}/commands)
 * - Tuya 스마트 플러그, 개폐기, 온습도 센서 등 상태 백엔드 및 InfluxDB/Socket.io 연동
 */

import crypto from 'crypto'
import { logger } from '../lib/logger.js'
import { websocketBroadcast } from '../plugins/websocket.plugin.ts'
import { influxService } from './influx.service.js'

interface TuyaTokenResponse {
  result: {
    access_token: string
    expire_time: number
    refresh_token: string
    uid: string
  }
  success: boolean
  t: number
  tid: string
}

interface TuyaDeviceStatus {
  code: string
  value: boolean | number | string
}

interface TuyaDeviceInfoResponse {
  result: {
    id: string
    name: string
    product_name: string
    online: boolean
    status: TuyaDeviceStatus[]
    category: string
    update_time: number
  }
  success: boolean
}

class TuyaService {
  private accessId: string = ''
  private accessSecret: string = ''
  private endpoint: string = ''
  private accessToken: string = ''
  private tokenExpireTime: number = 0
  private pollingInterval: NodeJS.Timeout | null = null

  initialize() {
    this.accessId = process.env.TUYA_ACCESS_ID ?? ''
    this.accessSecret = process.env.TUYA_ACCESS_SECRET ?? ''
    this.endpoint = process.env.TUYA_ENDPOINT ?? 'https://openapi.tuyaus.com'

    if (!this.accessId || !this.accessSecret) {
      logger.warn('⚠️ Tuya API 계정 정보가 설정되지 않았습니다.')
      return
    }

    logger.info({ endpoint: this.endpoint, accessId: this.accessId }, '🔌 Tuya Open API 서비스 초기화')
  }

  /**
   * HMAC-SHA256 기반 Tuya API 서명 생성 알고리즘
   */
  private calcSign(
    accessId: string,
    secret: string,
    t: string,
    accessToken: string = '',
    nonce: string = '',
    httpMethod: string = 'GET',
    url: string = '',
    bodyStr: string = ''
  ): string {
    const contentHash = crypto.createHash('sha256').update(bodyStr).digest('hex')
    const stringToSign = [httpMethod, contentHash, '', url].join('\n')
    const signStr = accessId + accessToken + t + nonce + stringToSign
    return crypto.createHmac('sha256', secret).update(signStr).digest('hex').toUpperCase()
  }

  /**
   * Access Token 발급
   */
  async getAccessToken(): Promise<string> {
    if (this.accessToken && Date.now() < this.tokenExpireTime - 60000) {
      return this.accessToken
    }

    const t = Date.now().toString()
    const url = '/v1.0/token?grant_type=1'
    const sign = this.calcSign(this.accessId, this.accessSecret, t, '', '', 'GET', url)

    try {
      const response = await fetch(`${this.endpoint}${url}`, {
        method: 'GET',
        headers: {
          client_id: this.accessId,
          sign: sign,
          t: t,
          sign_method: 'HMAC-SHA256',
        },
      })

      const data = (await response.json()) as TuyaTokenResponse

      if (data.success && data.result) {
        this.accessToken = data.result.access_token
        this.tokenExpireTime = Date.now() + data.result.expire_time * 1000
        logger.info('✅ Tuya Access Token 발급 성공!')
        return this.accessToken
      } else {
        logger.error({ data }, 'Tuya 토큰 발급 실패')
        throw new Error('Tuya Access Token 발급 실패')
      }
    } catch (error) {
      logger.error({ err: error }, 'Tuya 토큰 요청 중 에러 발생')
      throw error
    }
  }

  /**
   * Tuya API 범용 호출 메서드
   */
  private async requestApi<T>(
    method: 'GET' | 'POST' | 'PUT' | 'DELETE',
    path: string,
    body: object | null = null
  ): Promise<T> {
    const token = await this.getAccessToken()
    const t = Date.now().toString()
    const bodyStr = body ? JSON.stringify(body) : ''
    const sign = this.calcSign(
      this.accessId,
      this.accessSecret,
      t,
      token,
      '',
      method,
      path,
      bodyStr
    )

    const headers: Record<string, string> = {
      client_id: this.accessId,
      access_token: token,
      sign: sign,
      t: t,
      sign_method: 'HMAC-SHA256',
      'Content-Type': 'application/json',
    }

    const response = await fetch(`${this.endpoint}${path}`, {
      method,
      headers,
      body: body ? bodyStr : undefined,
    })

    return (await response.json()) as T
  }

  /**
   * Tuya 디바이스 상세 정보 및 상태 조회
   */
  async getDeviceInfo(deviceId: string): Promise<TuyaDeviceInfoResponse['result'] | null> {
    try {
      const data = await this.requestApi<TuyaDeviceInfoResponse>(
        'GET',
        `/v1.0/devices/${deviceId}`
      )
      if (data.success && data.result) {
        return data.result
      }
      return null
    } catch (error) {
      logger.error({ err: error, deviceId }, 'Tuya 장치 정보 조회 실패')
      return null
    }
  }

  /**
   * Tuya 디바이스 제어 명령 전송 (스마트 플러그 ON/OFF, 개폐기 제어 등)
   * 예: sendCommand("ebb219afdebea03ba3shlz", [{ code: "switch_1", value: true }])
   */
  async sendCommand(
    deviceId: string,
    commands: Array<{ code: string; value: boolean | number | string }>
  ): Promise<boolean> {
    try {
      logger.info({ deviceId, commands }, '🔌 Tuya 디바이스 제어 명령 발송')
      const data = await this.requestApi<{ success: boolean }>(
        'POST',
        `/v1.0/devices/${deviceId}/commands`,
        { commands }
      )

      if (data.success) {
        logger.info({ deviceId }, '✅ Tuya 제어 성공!')
        
        // 제어 상태 프론트엔드 및 스토어 알림
        websocketBroadcast('tuya:device_updated', {
          deviceId,
          commands,
          timestamp: new Date().toISOString(),
        })

        return true
      }
      return false
    } catch (error) {
      logger.error({ err: error, deviceId, commands }, 'Tuya 제어 명령 실행 에러')
      return false
    }
  }

  /**
   * 주기적 Tuya 상태 동기화 (5초 주기 폴링 & 대시보드 업데이트)
   */
  startPolling(sampleDeviceId?: string) {
    const targetDeviceId = sampleDeviceId ?? process.env.TUYA_SAMPLE_DEVICE_ID
    if (!targetDeviceId) return

    logger.info({ targetDeviceId }, '🔄 Tuya 디바이스 실시간 상태 동기화 시작 (5초 주기)')

    this.pollingInterval = setInterval(async () => {
      const info = await this.getDeviceInfo(targetDeviceId)
      if (info) {
        // 프론트엔드로 WebSocket Broadcast
        websocketBroadcast('tuya:status_update', {
          deviceId: info.id,
          name: info.name,
          online: info.online,
          status: info.status,
          timestamp: new Date().toISOString(),
        })

        // 온습도/스마트플러그 등의 수치를 InfluxDB에 기록
        const switchStatus = info.status.find((s) => s.code.startsWith('switch'))
        if (switchStatus) {
          await influxService.writeSensorData('cheongjeong', 'h01', 'actuator_tuya', {
            timestamp: new Date().toISOString(),
            sensor_id: info.id,
            rssi: info.online ? -50 : -99,
          })
        }
      }
    }, 5000)
  }

  stopPolling() {
    if (this.pollingInterval) {
      clearInterval(this.pollingInterval)
      this.pollingInterval = null
    }
  }
}

export const tuyaService = new TuyaService()
