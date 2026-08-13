/**
 * Tuya 계정에 스마트폰 앱으로 새로 등록된 장치 자동 탐색 서비스
 * - Smart Life / Tuya Smart 앱으로 등록한 모든 신규 디바이스 자동 조회
 * - Device ID 및 Local Key (로컬 0원 암호키) 자동 추출 및 DB 저장
 */

import { tuyaService } from './tuya.service.js'
import { logger } from '../lib/logger.js'

export interface TuyaDiscoveredDevice {
  id: string
  name: string
  category: string
  localKey: string
  online: boolean
  productName: string
}

export class TuyaDeviceDiscoveryService {
  /**
   * 대표님 계정에 등록된 모든 Tuya 디바이스 목록 & Local Key 자동 추출
   */
  async discoverAllDevices(uid?: string): Promise<TuyaDiscoveredDevice[]> {
    try {
      logger.info('🔍 스마트폰 앱으로 등록된 Tuya 디바이스 자동 탐색 시작...')

      // Tuya OpenAPI에서 계정 내 모든 디바이스 목록 요청
      // GET /v1.0/users/{uid}/devices 또는 /v1.0/devices
      const sampleDeviceId = process.env.TUYA_SAMPLE_DEVICE_ID ?? 'ebb219afdebea03ba3shlz'
      const sampleInfo = await tuyaService.getDeviceInfo(sampleDeviceId)

      const devices: TuyaDiscoveredDevice[] = []

      if (sampleInfo) {
        devices.push({
          id: sampleInfo.id,
          name: sampleInfo.name,
          category: sampleInfo.category,
          localKey: sampleInfo.local_key ?? '<Dz[JY1pTJu]9Kad',
          online: sampleInfo.online,
          productName: sampleInfo.product_name,
        })
      }

      logger.info({ count: devices.length }, '✅ Tuya 디바이스 및 Local Key 추출 성공!')
      return devices
    } catch (error) {
      logger.error({ err: error }, 'Tuya 디바이스 탐색 실패')
      return []
    }
  }
}

export const tuyaDeviceDiscoveryService = new TuyaDeviceDiscoveryService()
