/**
 * 자동화 규칙 엔진
 * 
 * 센서 데이터 수신 시 등록된 자동화 규칙을 평가하여
 * 조건 만족 시 액추에이터를 자동 제어합니다.
 * 
 * 규칙 예시:
 * - 온도 > 35°C → 측창 80% 열기 + 환풍기 고속
 * - 강우 감지 → 모든 창문 즉시 닫기 (긴급)
 * - 토양수분 < 30% → 양수기 5분 가동
 * - CO₂ < 600ppm → CO₂ 공급기 ON
 */

import { interlockService } from './interlock.service.js'
import { mqttService } from './mqtt.service.js'
import { logger } from '../lib/logger.js'
import type { SensorPayload } from './mqtt.service.js'

// ── 자동화 규칙 타입 ───────────────────────────────────────────────────
export interface AutomationRule {
  id: string
  farmId: string
  houseId: string
  name: string
  isActive: boolean
  priority: number  // 높을수록 우선 실행

  trigger: {
    sensorType: string    // 'air_temp', 'soil_moisture', 'rain_detected', ...
    operator: '>' | '<' | '>=' | '<=' | '==' | '!='
    value: number | boolean
    timeRange?: { start: string; end: string }  // '06:00' ~ '20:00'
  }

  actions: Array<{
    deviceId: string
    command: 'OPEN' | 'CLOSE' | 'STOP' | 'ON' | 'OFF'
    position?: number
    duration?: number   // 초 단위 (양수기 타이머 등)
  }>

  // 복귀 조건 (선택사항)
  resetTrigger?: {
    sensorType: string
    operator: '>' | '<' | '>=' | '<=' | '==' | '!='
    value: number | boolean
  }
  resetActions?: Array<{
    deviceId: string
    command: 'OPEN' | 'CLOSE' | 'STOP' | 'ON' | 'OFF'
    position?: number
  }>

  cooldownSec: number   // 재실행 방지 쿨다운 (초)
  lastTriggeredAt?: Date
}

// ── 기본 내장 자동화 규칙 (청정원 스마트팜) ───────────────────────────
export const DEFAULT_RULES: Omit<AutomationRule, 'id'>[] = [
  {
    farmId: 'cheongjeong',
    houseId: 'all', // 모든 동에 적용
    name: '강우 감지 시 긴급 창문 닫기',
    isActive: true,
    priority: 100,  // 최우선
    trigger: {
      sensorType: 'rain_detected',
      operator: '==',
      value: true,
    },
    actions: [
      { deviceId: 'roof_window', command: 'CLOSE' },
      { deviceId: 'side_window_left', command: 'CLOSE' },
      { deviceId: 'side_window_right', command: 'CLOSE' },
      { deviceId: 'thermal_curtain', command: 'CLOSE' },
    ],
    cooldownSec: 300,
  },
  {
    farmId: 'cheongjeong',
    houseId: 'all',
    name: '강풍 시 창문 닫기',
    isActive: true,
    priority: 95,
    trigger: {
      sensorType: 'wind_speed',
      operator: '>',
      value: 8.0,  // 8m/s 초과 시
    },
    actions: [
      { deviceId: 'roof_window', command: 'CLOSE' },
      { deviceId: 'side_window_left', command: 'CLOSE' },
      { deviceId: 'side_window_right', command: 'CLOSE' },
    ],
    resetTrigger: {
      sensorType: 'wind_speed',
      operator: '<',
      value: 5.0,
    },
    resetActions: [
      { deviceId: 'side_window_left', command: 'OPEN', position: 50 },
      { deviceId: 'side_window_right', command: 'OPEN', position: 50 },
    ],
    cooldownSec: 600,
  },
  {
    farmId: 'cheongjeong',
    houseId: 'all',
    name: '과온 시 환기',
    isActive: true,
    priority: 80,
    trigger: {
      sensorType: 'air_temp',
      operator: '>',
      value: 33,
      timeRange: { start: '06:00', end: '20:00' },
    },
    actions: [
      { deviceId: 'side_window_left', command: 'OPEN', position: 80 },
      { deviceId: 'side_window_right', command: 'OPEN', position: 80 },
      { deviceId: 'roof_window', command: 'OPEN', position: 60 },
      { deviceId: 'vent_fan', command: 'ON' },
    ],
    resetTrigger: { sensorType: 'air_temp', operator: '<', value: 28 },
    resetActions: [
      { deviceId: 'side_window_left', command: 'CLOSE' },
      { deviceId: 'side_window_right', command: 'CLOSE' },
      { deviceId: 'roof_window', command: 'CLOSE' },
      { deviceId: 'vent_fan', command: 'OFF' },
    ],
    cooldownSec: 300,
  },
  {
    farmId: 'cheongjeong',
    houseId: 'all',
    name: '토양수분 부족 시 관수',
    isActive: true,
    priority: 60,
    trigger: {
      sensorType: 'soil_moisture',
      operator: '<',
      value: 40,
      timeRange: { start: '07:00', end: '17:00' },
    },
    actions: [
      { deviceId: 'water_pump', command: 'ON', duration: 300 }, // 5분 가동
    ],
    cooldownSec: 3600, // 1시간 쿨다운
  },
  {
    farmId: 'cheongjeong',
    houseId: 'all',
    name: 'CO₂ 부족 시 공급',
    isActive: true,
    priority: 50,
    trigger: {
      sensorType: 'co2_ppm',
      operator: '<',
      value: 600,
      timeRange: { start: '08:00', end: '18:00' },
    },
    actions: [
      { deviceId: 'co2_supply', command: 'ON', duration: 120 }, // 2분 공급
    ],
    resetTrigger: { sensorType: 'co2_ppm', operator: '>', value: 900 },
    resetActions: [{ deviceId: 'co2_supply', command: 'OFF' }],
    cooldownSec: 600,
  },
  {
    farmId: 'cheongjeong',
    houseId: 'all',
    name: '야간 보온 커튼 닫기',
    isActive: true,
    priority: 70,
    trigger: {
      sensorType: 'air_temp',
      operator: '<',
      value: 15,
      timeRange: { start: '18:00', end: '23:59' },
    },
    actions: [
      { deviceId: 'thermal_curtain', command: 'CLOSE' },
    ],
    cooldownSec: 1800,
  },
]

// ── 자동화 엔진 ────────────────────────────────────────────────────────
class AutomationEngine {
  private rules: AutomationRule[] = []
  private ruleTimers = new Map<string, NodeJS.Timeout>()

  loadRules(rules: AutomationRule[]): void {
    this.rules = rules.sort((a, b) => b.priority - a.priority) // 우선순위 정렬
    logger.info({ count: rules.length }, '✅ 자동화 규칙 로드 완료')
  }

  /**
   * 센서 데이터 수신 시 규칙 평가
   */
  async evaluate(
    farmId: string,
    houseId: string,
    sensorType: string,
    data: SensorPayload
  ): Promise<void> {
    const applicableRules = this.rules.filter(
      (rule) =>
        rule.isActive &&
        rule.farmId === farmId &&
        (rule.houseId === houseId || rule.houseId === 'all') &&
        rule.trigger.sensorType in data
    )

    for (const rule of applicableRules) {
      await this.evaluateRule(rule, farmId, houseId, data)
    }
  }

  private async evaluateRule(
    rule: AutomationRule,
    farmId: string,
    houseId: string,
    data: SensorPayload
  ): Promise<void> {
    // 쿨다운 체크
    if (rule.lastTriggeredAt) {
      const elapsed = (Date.now() - rule.lastTriggeredAt.getTime()) / 1000
      if (elapsed < rule.cooldownSec) return
    }

    // 시간 범위 체크
    if (rule.trigger.timeRange) {
      if (!this.isInTimeRange(rule.trigger.timeRange)) return
    }

    // 센서값 가져오기
    const sensorValue = (data as any)[rule.trigger.sensorType]
    if (sensorValue === undefined) return

    // 조건 평가
    const conditionMet = this.evaluateCondition(
      sensorValue,
      rule.trigger.operator,
      rule.trigger.value
    )

    if (!conditionMet) return

    // 조건 만족! 액션 실행
    logger.info({ rule: rule.name, farmId, houseId }, `⚡ 자동화 규칙 실행: ${rule.name}`)
    rule.lastTriggeredAt = new Date()

    for (const action of rule.actions) {
      await interlockService.executeSafeCommand(
        farmId,
        houseId,
        action.deviceId,
        action.command as any,
        { source: 'auto' }
      )

      // 타이머 액션 (양수기 등 duration 있는 경우)
      if (action.duration) {
        const timerId = `${rule.id}-${action.deviceId}`
        const existingTimer = this.ruleTimers.get(timerId)
        if (existingTimer) clearTimeout(existingTimer)

        const timer = setTimeout(async () => {
          mqttService.sendCommand(farmId, houseId, action.deviceId, {
            command: 'OFF',
            source: 'auto',
          })
          this.ruleTimers.delete(timerId)
          logger.info({ deviceId: action.deviceId }, `⏱ 타이머 완료 → 장치 OFF`)
        }, action.duration * 1000)

        this.ruleTimers.set(timerId, timer)
      }
    }
  }

  private evaluateCondition(
    value: number | boolean,
    operator: AutomationRule['trigger']['operator'],
    threshold: number | boolean
  ): boolean {
    switch (operator) {
      case '>': return (value as number) > (threshold as number)
      case '<': return (value as number) < (threshold as number)
      case '>=': return (value as number) >= (threshold as number)
      case '<=': return (value as number) <= (threshold as number)
      case '==': return value === threshold
      case '!=': return value !== threshold
      default: return false
    }
  }

  private isInTimeRange(range: { start: string; end: string }): boolean {
    const now = new Date()
    const currentTime = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}`
    return currentTime >= range.start && currentTime <= range.end
  }
}

export const automationEngine = new AutomationEngine()
