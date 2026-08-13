/**
 * MQTT 서비스
 * HiveMQ Cloud 연결, 토픽 구독, 메시지 처리, 실시간 WebSocket 브릿지
 */

import mqtt, { type MqttClient } from 'mqtt'
import { EventEmitter } from 'events'
import { influxService } from './influx.service.js'
import { websocketBroadcast } from '../plugins/websocket.plugin.js'
import { interlockService } from './interlock.service.js'
import { automationEngine } from './automation-engine.service.js'
import { logger } from '../lib/logger.js'
import 'dotenv/config'

// ── MQTT 토픽 상수 ─────────────────────────────────────────────────────
export const TOPICS = {
  SENSOR_ALL: 'farm/+/house/+/sensor/#',
  STATUS_ALL: 'farm/+/house/+/status',
  ESS_ALL: 'farm/+/ess/status',
  WEATHER: 'farm/+/weather/outdoor',
  ACTUATOR_STATE: 'farm/+/house/+/actuator/+/state',
} as const

// ── 센서 데이터 타입 ───────────────────────────────────────────────────
export interface SensorPayload {
  timestamp: string
  sensor_id: string
  // 환경 센서
  air_temp?: number       // °C
  air_humidity?: number   // %RH
  co2_ppm?: number        // ppm
  light_lux?: number      // lux
  par?: number            // μmol/m²/s
  // 토양 센서
  soil_moisture?: number  // %
  soil_temp?: number      // °C
  ec_value?: number       // mS/cm
  ph_value?: number       // pH
  // 기상 센서
  wind_speed?: number     // m/s
  wind_dir?: number       // degrees
  rain_detected?: boolean
  solar_radiation?: number // W/m²
  outdoor_temp?: number
  outdoor_humidity?: number
  // 디바이스 상태
  rssi?: number
  heap_free?: number
  uptime?: number
}

export interface ActuatorStatePayload {
  state: 'OPEN' | 'CLOSED' | 'OPENING' | 'CLOSING' | 'STOPPED' | 'FAULT'
  position?: number   // 0~100%
  timestamp: string
}

export interface EssStatusPayload {
  soc_percent: number     // 충전율 %
  voltage_v: number       // 배터리 전압 V
  current_a: number       // 전류 A (양수=충전, 음수=방전)
  solar_power_w: number   // 태양광 발전량 W
  load_power_w: number    // 부하 소비량 W
  battery_temp_c: number  // 배터리 온도 °C
  timestamp: string
}

// ── MQTT 서비스 클래스 ─────────────────────────────────────────────────
class MqttService extends EventEmitter {
  private client: MqttClient | null = null
  private connected = false
  private reconnectCount = 0

  async connect(): Promise<void> {
    const options: mqtt.IClientOptions = {
      host: process.env.MQTT_HOST ?? 'localhost',
      port: parseInt(process.env.MQTT_PORT ?? '1883'),
      protocol: process.env.MQTT_HOST?.includes('hivemq') ? 'mqtts' : 'mqtt',
      username: process.env.MQTT_USERNAME,
      password: process.env.MQTT_PASSWORD,
      clientId: `${process.env.MQTT_CLIENT_ID ?? 'smartfarm-backend'}-${Date.now()}`,
      clean: false, // 영속 세션 (QoS 보장)
      reconnectPeriod: 5000,
      connectTimeout: 30000,
      keepalive: 60,
      // LWT (Last Will Testament) - 연결 끊김 감지
      will: {
        topic: 'farm/bridge/status',
        payload: JSON.stringify({ status: 'offline', timestamp: new Date().toISOString() }),
        qos: 1,
        retain: true,
      },
    }

    return new Promise((resolve, reject) => {
      this.client = mqtt.connect(options)

      this.client.on('connect', () => {
        this.connected = true
        this.reconnectCount = 0
        logger.info('🔌 MQTT 브로커 연결 성공')

        // 브릿지 온라인 상태 발행
        this.publish('farm/bridge/status', {
          status: 'online',
          timestamp: new Date().toISOString(),
        }, { retain: true })

        // 토픽 구독
        this.subscribeAll()
        resolve()
      })

      this.client.on('message', this.handleMessage.bind(this))

      this.client.on('reconnect', () => {
        this.reconnectCount++
        logger.warn(`MQTT 재연결 시도 중... (${this.reconnectCount}번째)`)
      })

      this.client.on('error', (error) => {
        logger.error({ err: error }, 'MQTT 연결 오류')
        if (!this.connected) reject(error)
      })

      this.client.on('offline', () => {
        this.connected = false
        logger.warn('MQTT 오프라인 상태')
      })
    })
  }

  private subscribeAll(): void {
    const topics = Object.values(TOPICS)
    this.client?.subscribe(topics, { qos: 1 }, (err) => {
      if (err) {
        logger.error({ err }, 'MQTT 토픽 구독 실패')
      } else {
        logger.info({ topics }, '✅ MQTT 토픽 구독 완료')
      }
    })
  }

  private async handleMessage(topic: string, payload: Buffer): Promise<void> {
    try {
      const data = JSON.parse(payload.toString())
      const parts = topic.split('/')
      // farm/{farmId}/house/{houseId}/...
      const farmId = parts[1]
      const houseId = parts[3]

      // ── 센서 데이터 처리 ────────────────────────────────────────
      if (topic.includes('/sensor/')) {
        const sensorType = parts[5] // environment, soil, light, weather
        await this.processSensorData(farmId, houseId, sensorType, data as SensorPayload)
      }

      // ── 액추에이터 상태 피드백 처리 ──────────────────────────────
      else if (topic.includes('/actuator/') && topic.endsWith('/state')) {
        const deviceId = parts[5]
        await this.processActuatorState(farmId, houseId, deviceId, data as ActuatorStatePayload)
      }

      // ── ESS 전력 상태 처리 ───────────────────────────────────────
      else if (topic.includes('/ess/')) {
        await this.processEssStatus(farmId, data as EssStatusPayload)
      }

      // ── 기기 상태 (Heartbeat) ────────────────────────────────────
      else if (topic.endsWith('/status')) {
        await this.processDeviceStatus(farmId, houseId, data)
      }

    } catch (error) {
      logger.error({ err: error, topic }, 'MQTT 메시지 처리 오류')
    }
  }

  private async processSensorData(
    farmId: string,
    houseId: string,
    sensorType: string,
    data: SensorPayload
  ): Promise<void> {
    // 1. InfluxDB에 시계열 데이터 저장
    await influxService.writeSensorData(farmId, houseId, sensorType, data)

    // 2. WebSocket으로 프론트엔드에 실시간 Push
    websocketBroadcast('telemetry:update', {
      farmId,
      houseId,
      sensorType,
      data,
      timestamp: data.timestamp,
    })

    // 3. 자동화 규칙 엔진 평가 (조건 만족 시 액추에이터 자동 제어)
    await automationEngine.evaluate(farmId, houseId, sensorType, data)

    // 4. 알림 임계값 평가
    this.emit('sensor:data', { farmId, houseId, sensorType, data })
  }

  private async processActuatorState(
    farmId: string,
    houseId: string,
    deviceId: string,
    data: ActuatorStatePayload
  ): Promise<void> {
    // 인터록 상태 업데이트
    interlockService.updateState(deviceId, data.state, data.position)

    // WebSocket Push
    websocketBroadcast('actuator:state', {
      farmId,
      houseId,
      deviceId,
      state: data.state,
      position: data.position,
      timestamp: data.timestamp,
    })
  }

  private async processEssStatus(farmId: string, data: EssStatusPayload): Promise<void> {
    // InfluxDB 저장
    await influxService.writeEssStatus(farmId, data)

    // WebSocket Push
    websocketBroadcast('ess:update', { farmId, data })

    // ESS SOC 위험 수준 경보
    if (data.soc_percent < 20) {
      websocketBroadcast('alert:triggered', {
        level: data.soc_percent < 10 ? 'critical' : 'warning',
        farmId,
        message: `ESS 배터리 잔량 부족: ${data.soc_percent}%`,
        timestamp: new Date().toISOString(),
      })
    }
  }

  private async processDeviceStatus(farmId: string, houseId: string, data: any): Promise<void> {
    const isOnline = data.status !== 'offline'

    // WebSocket Push (기기 온/오프라인 상태 변경)
    websocketBroadcast('device:status', {
      farmId,
      houseId,
      deviceId: data.device_id,
      isOnline,
      rssi: data.rssi,
      bufferedRecords: data.buffered_records ?? 0,
      timestamp: new Date().toISOString(),
    })
  }

  /**
   * MQTT 메시지 발행 (액추에이터 제어 명령)
   */
  publish(topic: string, payload: object, options?: mqtt.IClientPublishOptions): void {
    if (!this.client || !this.connected) {
      logger.warn({ topic }, 'MQTT 연결 안됨 - 발행 실패')
      return
    }

    this.client.publish(
      topic,
      JSON.stringify(payload),
      { qos: 1, ...options },
      (err) => {
        if (err) logger.error({ err, topic }, 'MQTT 발행 오류')
      }
    )
  }

  /**
   * 액추에이터 제어 명령 발행
   */
  sendCommand(
    farmId: string,
    houseId: string,
    deviceId: string,
    command: {
      command: 'OPEN' | 'CLOSE' | 'STOP' | 'ON' | 'OFF'
      position?: number
      source: 'manual' | 'auto' | 'schedule' | 'emergency'
      requestedBy?: string
    }
  ): void {
    const topic = `farm/${farmId}/house/${houseId}/actuator/${deviceId}/command`
    this.publish(topic, {
      ...command,
      timestamp: new Date().toISOString(),
    })
    logger.info({ topic, command }, '💡 액추에이터 명령 발행')
  }

  /**
   * 감우 감지 시 전체 창문 긴급 닫기
   */
  emergencyCloseAll(farmId: string, houseId: string, reason: string): void {
    logger.warn({ farmId, houseId, reason }, '🚨 긴급 창문 닫기 실행!')

    const devices = ['roof_window', 'side_window_left', 'side_window_right', 'thermal_curtain']
    for (const deviceId of devices) {
      this.sendCommand(farmId, houseId, deviceId, {
        command: 'CLOSE',
        source: 'emergency',
      })
    }

    websocketBroadcast('alert:triggered', {
      level: 'critical',
      farmId,
      houseId,
      message: `긴급 창문 닫기 실행: ${reason}`,
      timestamp: new Date().toISOString(),
    })
  }

  isConnected(): boolean {
    return this.connected
  }

  async disconnect(): Promise<void> {
    return new Promise((resolve) => {
      if (this.client) {
        this.client.end(false, {}, resolve)
      } else {
        resolve()
      }
    })
  }
}

export const mqttService = new MqttService()
