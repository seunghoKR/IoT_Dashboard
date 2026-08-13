/**
 * 비닐하우스(동) 라우터
 * 센서 실시간값, 이력 조회, 액추에이터 제어
 */

import type { FastifyInstance } from 'fastify'
import { z } from 'zod'
import { influxService } from '../services/influx.service.js'
import { interlockService } from '../services/interlock.service.js'
import { mqttService } from '../services/mqtt.service.js'

const commandSchema = z.object({
  deviceId: z.string(),
  command: z.enum(['OPEN', 'CLOSE', 'STOP', 'ON', 'OFF']),
  position: z.number().min(0).max(100).optional(),
  source: z.enum(['manual', 'auto', 'schedule', 'emergency']).default('manual'),
})

export async function greenhouseRoutes(fastify: FastifyInstance): Promise<void> {

  // ── 모든 동 목록 ─────────────────────────────────────────────────────
  fastify.get('/:farmId/greenhouses', async (request, reply) => {
    const { farmId } = request.params as { farmId: string }

    // TODO: PostgreSQL에서 조회
    return {
      success: true,
      data: [
        { id: 'h01', name: '1동 방울토마토', houseNumber: 1, cropType: 'cherry_tomato' },
        { id: 'h02', name: '2동 방울토마토', houseNumber: 2, cropType: 'cherry_tomato' },
        { id: 'h03', name: '3동 방울토마토', houseNumber: 3, cropType: 'cherry_tomato' },
        { id: 'h04', name: '4동 오이',      houseNumber: 4, cropType: 'cucumber' },
        { id: 'h05', name: '5동 파프리카',  houseNumber: 5, cropType: 'paprika' },
      ],
    }
  })

  // ── 동별 최신 센서값 (대시보드 실시간 카드) ──────────────────────────
  fastify.get('/:farmId/greenhouses/:houseId/latest', async (request, reply) => {
    const { farmId, houseId } = request.params as { farmId: string; houseId: string }

    const data = await influxService.queryLatestValues(farmId, houseId)
    return { success: true, data }
  })

  // ── 동별 시계열 이력 (차트용) ─────────────────────────────────────────
  fastify.get('/:farmId/greenhouses/:houseId/history', async (request, reply) => {
    const { farmId, houseId } = request.params as { farmId: string; houseId: string }
    const { field = 'air_temp', range = '-24h', measurement = 'greenhouse_environment' } =
      request.query as { field?: string; range?: string; measurement?: string }

    const data = await influxService.queryTelemetry({
      farmId,
      houseId,
      measurement,
      field,
      range,
    })

    return { success: true, data }
  })

  // ── 액추에이터 제어 (인터록 검증 포함) ───────────────────────────────
  fastify.post('/:farmId/greenhouses/:houseId/control', async (request, reply) => {
    const { farmId, houseId } = request.params as { farmId: string; houseId: string }
    const body = commandSchema.parse(request.body)

    // 인터록 안전 검증 + 명령 실행
    const result = await interlockService.executeSafeCommand(
      farmId,
      houseId,
      body.deviceId,
      body.command as any,
      { source: body.source, requestedBy: 'user' }
    )

    if (!result.success) {
      return reply.status(400).send({
        success: false,
        error: result.message,
        code: 'INTERLOCK_VIOLATION',
      })
    }

    return { success: true, message: result.message }
  })

  // ── 긴급 전체 닫기 ────────────────────────────────────────────────────
  fastify.post('/:farmId/greenhouses/:houseId/emergency-close', async (request, reply) => {
    const { farmId, houseId } = request.params as { farmId: string; houseId: string }
    const { reason = '수동 긴급 닫기' } = request.body as { reason?: string }

    await interlockService.emergencyCloseAll(farmId, houseId, reason)

    return { success: true, message: '긴급 창문 닫기 명령 전송 완료' }
  })

  // ── ESS 현재 상태 ─────────────────────────────────────────────────────
  fastify.get('/:farmId/ess/latest', async (request, reply) => {
    const { farmId } = request.params as { farmId: string }
    const data = await influxService.queryTelemetry({
      farmId,
      houseId: 'ess',
      measurement: 'ess_monitoring',
      field: 'soc_percent',
      range: '-10m',
      aggregateWindow: '1m',
    })
    return { success: true, data: data[data.length - 1] }
  })
}
