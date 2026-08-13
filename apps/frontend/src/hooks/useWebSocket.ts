/**
 * WebSocket 실시간 연결 훅
 * 서버의 센서 데이터를 수신하여 Zustand 스토어에 업데이트
 */

import { useEffect, useRef, useCallback } from 'react'
import { useSmartFarmStore } from '../stores/smartfarm.store'

const WS_URL = import.meta.env.VITE_WS_URL ?? 'ws://localhost:3000'
const RECONNECT_INTERVAL = 3000
const MAX_RECONNECT_ATTEMPTS = 10
const PING_INTERVAL = 25000

export function useWebSocket() {
  const wsRef = useRef<WebSocket | null>(null)
  const reconnectTimerRef = useRef<NodeJS.Timeout>()
  const pingTimerRef = useRef<NodeJS.Timeout>()
  const reconnectAttemptsRef = useRef(0)

  const {
    setWsConnected,
    updateGreenhouseSensors,
    updateActuatorState,
    updateEss,
    updateOutdoorWeather,
    addAlert,
    farmId,
  } = useSmartFarmStore()

  const handleMessage = useCallback((event: MessageEvent) => {
    try {
      const { event: eventType, data } = JSON.parse(event.data)

      switch (eventType) {
        case 'connected':
          // 구독 메시지 전송
          wsRef.current?.send(JSON.stringify({ type: 'subscribe:farm', farmId }))
          break

        case 'telemetry:update':
          updateGreenhouseSensors(data.houseId, data.data, data.timestamp)
          break

        case 'actuator:state':
          updateActuatorState(data.houseId, data.deviceId, {
            state: data.state,
            position: data.position,
          })
          break

        case 'ess:update':
          updateEss(data.data)
          break

        case 'alert:triggered':
          addAlert({
            level: data.level,
            houseId: data.houseId,
            message: data.message,
            timestamp: data.timestamp,
          })
          break

        case 'device:status':
          // 오프라인 디바이스 처리
          if (!data.isOnline && data.bufferedRecords > 0) {
            addAlert({
              level: 'warning',
              houseId: data.houseId,
              message: `${data.houseId} 디바이스 오프라인 → 온라인 복귀 (${data.bufferedRecords}건 동기화 중)`,
              timestamp: data.timestamp,
            })
          }
          break

        case 'pong':
          // 연결 유지 확인
          break

        default:
          console.debug('[WS] Unknown event:', eventType)
      }
    } catch (error) {
      console.error('[WS] 메시지 파싱 오류:', error)
    }
  }, [farmId, updateGreenhouseSensors, updateActuatorState, updateEss, addAlert])

  const connect = useCallback(() => {
    if (wsRef.current?.readyState === WebSocket.OPEN) return

    const ws = new WebSocket(`${WS_URL}/ws/live`)
    wsRef.current = ws

    ws.onopen = () => {
      setWsConnected(true)
      reconnectAttemptsRef.current = 0
      console.info('[WS] ✅ 연결 성공')

      // Ping 타이머 설정 (연결 유지)
      pingTimerRef.current = setInterval(() => {
        if (ws.readyState === WebSocket.OPEN) {
          ws.send(JSON.stringify({ type: 'ping' }))
        }
      }, PING_INTERVAL)
    }

    ws.onmessage = handleMessage

    ws.onclose = () => {
      setWsConnected(false)
      clearInterval(pingTimerRef.current)

      // 자동 재연결
      if (reconnectAttemptsRef.current < MAX_RECONNECT_ATTEMPTS) {
        reconnectAttemptsRef.current++
        const delay = Math.min(RECONNECT_INTERVAL * reconnectAttemptsRef.current, 30000)
        console.warn(`[WS] 연결 해제 → ${delay}ms 후 재연결 (${reconnectAttemptsRef.current}/${MAX_RECONNECT_ATTEMPTS})`)
        reconnectTimerRef.current = setTimeout(connect, delay)
      } else {
        console.error('[WS] 최대 재연결 횟수 초과')
        addAlert({
          level: 'critical',
          message: '서버 연결이 끊겼습니다. 페이지를 새로고침해 주세요.',
          timestamp: new Date().toISOString(),
        })
      }
    }

    ws.onerror = (error) => {
      console.error('[WS] 오류:', error)
    }
  }, [handleMessage, setWsConnected, addAlert])

  useEffect(() => {
    connect()
    return () => {
      clearTimeout(reconnectTimerRef.current)
      clearInterval(pingTimerRef.current)
      wsRef.current?.close()
    }
  }, [connect])

  return {
    isConnected: wsRef.current?.readyState === WebSocket.OPEN,
  }
}
