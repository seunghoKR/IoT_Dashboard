/**
 * 모터 인터록(Interlock) 안전 서비스
 * 
 * 핵심 안전 기능:
 * - 정회전(올림)과 역회전(내림) 동시 활성화 → 물리적 차단
 * - 방향 전환 시 Dead Time 500ms 적용
 * - 인터록 그룹 관리 (같은 그룹 내 동시 작동 금지)
 * - 리밋스위치 상태 기반 명령 거부
 */

import { logger } from '../lib/logger.js'
import { mqttService } from './mqtt.service.js'

// ── 모터 상태 타입 ─────────────────────────────────────────────────────
interface MotorState {
  deviceId: string
  farmId: string
  houseId: string
  state: 'OPEN' | 'CLOSED' | 'OPENING' | 'CLOSING' | 'STOPPED' | 'FAULT'
  position: number      // 0(완전 닫힘) ~ 100(완전 열림)
  limitSwitchTop: boolean     // 완전 열림 리밋스위치
  limitSwitchBottom: boolean  // 완전 닫힘 리밋스위치
  interlockGroup: string | null
  lastCommandTime: number
  lastDirection: 'OPEN' | 'CLOSE' | null
}

interface CommandValidationResult {
  allowed: boolean
  reason?: string
  requiresDeadTime?: boolean
  deadTimeMs?: number
}

// ── 인터록 서비스 ──────────────────────────────────────────────────────
class InterlockService {
  private motorStates = new Map<string, MotorState>()
  private readonly DEAD_TIME_MS = 500  // 방향 전환 Dead Time (ms)

  /**
   * 액추에이터 상태 업데이트 (MQTT 피드백 수신 시 호출)
   */
  updateState(
    deviceId: string,
    state: MotorState['state'],
    position?: number
  ): void {
    const current = this.motorStates.get(deviceId)
    if (current) {
      current.state = state
      if (position !== undefined) current.position = position

      // 리밋스위치 자동 판단 (위치 기반)
      current.limitSwitchTop = position === 100
      current.limitSwitchBottom = position === 0
    }
  }

  /**
   * 장치 등록 (서버 시작 시 DB에서 로드)
   */
  registerDevice(config: {
    deviceId: string
    farmId: string
    houseId: string
    interlockGroup?: string
  }): void {
    this.motorStates.set(config.deviceId, {
      deviceId: config.deviceId,
      farmId: config.farmId,
      houseId: config.houseId,
      state: 'STOPPED',
      position: 0,
      limitSwitchTop: false,
      limitSwitchBottom: true,
      interlockGroup: config.interlockGroup ?? null,
      lastCommandTime: 0,
      lastDirection: null,
    })
  }

  /**
   * 제어 명령 유효성 검증 (3중 인터록 검사)
   * 
   * 검사 순서:
   * 1. 리밋스위치 상태 확인 (물리적 한계)
   * 2. 인터록 그룹 내 다른 모터 동작 여부
   * 3. 반대 방향 전환 시 Dead Time 계산
   */
  async validateCommand(
    deviceId: string,
    command: 'OPEN' | 'CLOSE' | 'STOP'
  ): Promise<CommandValidationResult> {
    const motor = this.motorStates.get(deviceId)

    // 등록되지 않은 장치 (허용, 단 경고 로그)
    if (!motor) {
      logger.warn({ deviceId }, '⚠️ 미등록 장치에 대한 명령 - 인터록 우회됨')
      return { allowed: true }
    }

    // ── 검사 1: STOP은 항상 허용 ────────────────────────────────────
    if (command === 'STOP') {
      return { allowed: true }
    }

    // ── 검사 2: 리밋스위치 상태 ─────────────────────────────────────
    if (command === 'OPEN' && motor.limitSwitchTop) {
      return {
        allowed: false,
        reason: `[${deviceId}] 이미 완전 개방 상태 (리밋스위치 상단 ON)`,
      }
    }
    if (command === 'CLOSE' && motor.limitSwitchBottom) {
      return {
        allowed: false,
        reason: `[${deviceId}] 이미 완전 폐쇄 상태 (리밋스위치 하단 ON)`,
      }
    }

    // ── 검사 3: 인터록 그룹 내 다른 모터 동작 중인지 확인 ────────────
    if (motor.interlockGroup) {
      for (const [otherId, otherMotor] of this.motorStates) {
        if (
          otherId !== deviceId &&
          otherMotor.interlockGroup === motor.interlockGroup &&
          (otherMotor.state === 'OPENING' || otherMotor.state === 'CLOSING')
        ) {
          return {
            allowed: false,
            reason: `[인터록 위반] 같은 그룹(${motor.interlockGroup})의 ${otherId}가 동작 중`,
          }
        }
      }
    }

    // ── 검사 4: 반대 방향 전환 시 Dead Time ──────────────────────────
    const newDirection = command === 'OPEN' ? 'OPEN' : 'CLOSE'
    const isDirectionChange =
      motor.lastDirection !== null &&
      motor.lastDirection !== newDirection &&
      (motor.state === 'OPENING' || motor.state === 'CLOSING')

    if (isDirectionChange) {
      const elapsed = Date.now() - motor.lastCommandTime
      const remainingDeadTime = this.DEAD_TIME_MS - elapsed

      if (remainingDeadTime > 0) {
        return {
          allowed: true,
          requiresDeadTime: true,
          deadTimeMs: remainingDeadTime,
        }
      }
    }

    // 모든 검사 통과
    motor.lastCommandTime = Date.now()
    motor.lastDirection = newDirection

    return { allowed: true }
  }

  /**
   * 안전 명령 실행 (Dead Time 처리 포함)
   */
  async executeSafeCommand(
    farmId: string,
    houseId: string,
    deviceId: string,
    command: 'OPEN' | 'CLOSE' | 'STOP',
    options?: {
      position?: number
      source?: 'manual' | 'auto' | 'schedule' | 'emergency'
      requestedBy?: string
    }
  ): Promise<{ success: boolean; message: string }> {
    const validation = await this.validateCommand(deviceId, command)

    if (!validation.allowed) {
      logger.warn({ deviceId, command, reason: validation.reason }, '🔒 인터록 차단')
      return { success: false, message: validation.reason ?? '인터록 차단됨' }
    }

    // Dead Time 필요 시 먼저 STOP 발행 후 대기
    if (validation.requiresDeadTime && validation.deadTimeMs) {
      logger.info({ deviceId, deadTimeMs: validation.deadTimeMs }, '⏱ Dead Time 적용 - 먼저 정지')

      mqttService.sendCommand(farmId, houseId, deviceId, {
        command: 'STOP',
        source: 'auto',
      })

      await new Promise((resolve) => setTimeout(resolve, validation.deadTimeMs))
    }

    // 실제 명령 발행
    mqttService.sendCommand(farmId, houseId, deviceId, {
      command,
      position: options?.position,
      source: options?.source ?? 'manual',
      requestedBy: options?.requestedBy,
    })

    // 상태 업데이트
    const motor = this.motorStates.get(deviceId)
    if (motor) {
      motor.state = command === 'OPEN' ? 'OPENING' : command === 'CLOSE' ? 'CLOSING' : 'STOPPED'
    }

    logger.info({ farmId, houseId, deviceId, command }, '✅ 안전 명령 실행 완료')
    return { success: true, message: '명령 실행 완료' }
  }

  /**
   * 감우/강풍 감지 시 전체 창문 긴급 닫기
   * (인터록 우선순위: Emergency > 일반 명령)
   */
  async emergencyCloseAll(
    farmId: string,
    houseId: string,
    reason: string
  ): Promise<void> {
    logger.warn({ farmId, houseId, reason }, '🚨 긴급 전체 닫기 실행!')

    const windowDevices = [
      'roof_window',
      'side_window_left',
      'side_window_right',
      'thermal_curtain',
    ]

    for (const deviceId of windowDevices) {
      // 긴급 상황은 인터록 우선순위 최고 → 진행 중인 동작 강제 정지 후 닫기
      mqttService.sendCommand(farmId, houseId, deviceId, {
        command: 'STOP',
        source: 'emergency',
      })

      await new Promise((resolve) => setTimeout(resolve, this.DEAD_TIME_MS))

      mqttService.sendCommand(farmId, houseId, deviceId, {
        command: 'CLOSE',
        source: 'emergency',
      })

      // 상태 업데이트
      const motor = this.motorStates.get(deviceId)
      if (motor) motor.state = 'CLOSING'
    }
  }

  getMotorState(deviceId: string): MotorState | undefined {
    return this.motorStates.get(deviceId)
  }

  getAllStates(): MotorState[] {
    return Array.from(this.motorStates.values())
  }
}

export const interlockService = new InterlockService()
