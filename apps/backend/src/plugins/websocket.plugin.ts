/**
 * WebSocket 플러그인
 * 실시간 센서 데이터를 프론트엔드로 Push
 */

import type { FastifyInstance } from 'fastify'
import type { WebSocket } from '@fastify/websocket'
import { logger } from '../lib/logger.js'

// ── 연결된 클라이언트 관리 ─────────────────────────────────────────────
const clients = new Map<string, { socket: WebSocket; farmIds: string[] }>()

/**
 * 모든 연결된 클라이언트에 이벤트 브로드캐스트
 * (MQTT 서비스에서 호출)
 */
export function websocketBroadcast(event: string, data: unknown): void {
  const message = JSON.stringify({ event, data, timestamp: new Date().toISOString() })

  for (const [clientId, client] of clients) {
    try {
      if (client.socket.readyState === 1 /* OPEN */) {
        client.socket.send(message)
      } else {
        clients.delete(clientId)
      }
    } catch {
      clients.delete(clientId)
    }
  }
}

/**
 * 특정 농장에만 브로드캐스트
 */
export function websocketBroadcastToFarm(farmId: string, event: string, data: unknown): void {
  const message = JSON.stringify({ event, data, timestamp: new Date().toISOString() })

  for (const [clientId, client] of clients) {
    if (client.farmIds.includes(farmId)) {
      try {
        if (client.socket.readyState === 1) {
          client.socket.send(message)
        }
      } catch {
        clients.delete(clientId)
      }
    }
  }
}

/**
 * WebSocket 플러그인 등록
 */
export async function websocketHandler(fastify: FastifyInstance): Promise<void> {
  // 실시간 데이터 스트림 엔드포인트
  fastify.get(
    '/ws/live',
    { websocket: true },
    (socket, request) => {
      const clientId = crypto.randomUUID()

      logger.info({ clientId, ip: request.ip }, '🔌 WebSocket 클라이언트 연결')

      clients.set(clientId, { socket, farmIds: [] })

      // 연결 확인 메시지 전송
      socket.send(
        JSON.stringify({
          event: 'connected',
          data: { clientId, message: '스마트팜 실시간 데이터 스트림 연결됨' },
        })
      )

      // 클라이언트 메시지 처리
      socket.on('message', (rawMessage) => {
        try {
          const message = JSON.parse(rawMessage.toString())

          switch (message.type) {
            // 특정 농장 구독
            case 'subscribe:farm': {
              const client = clients.get(clientId)
              if (client && message.farmId) {
                if (!client.farmIds.includes(message.farmId)) {
                  client.farmIds.push(message.farmId)
                }
                socket.send(
                  JSON.stringify({ event: 'subscribed', data: { farmId: message.farmId } })
                )
              }
              break
            }

            // Ping (연결 유지)
            case 'ping':
              socket.send(JSON.stringify({ event: 'pong', data: { ts: Date.now() } }))
              break

            default:
              logger.warn({ type: message.type }, '알 수 없는 WebSocket 메시지 타입')
          }
        } catch (error) {
          logger.error({ err: error }, 'WebSocket 메시지 파싱 오류')
        }
      })

      // 연결 종료 처리
      socket.on('close', () => {
        clients.delete(clientId)
        logger.info({ clientId }, '🔌 WebSocket 클라이언트 연결 해제')
      })

      socket.on('error', (error) => {
        logger.error({ err: error, clientId }, 'WebSocket 오류')
        clients.delete(clientId)
      })
    }
  )
}

export { clients as wsClients }
