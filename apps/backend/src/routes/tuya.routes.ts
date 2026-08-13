/**
 * Tuya 디바이스 전용 REST API 라우터
 */

import type { FastifyInstance } from 'fastify'
import { z } from 'zod'
import { tuyaService } from '../services/tuya.service.js'

const commandSchema = z.object({
  code: z.string(),
  value: z.union([z.boolean(), z.number(), z.string()]),
})

export async function tuyaRoutes(fastify: FastifyInstance): Promise<void> {
  // Tuya 디바이스 상태 조회
  fastify.get('/devices/:deviceId', async (request, reply) => {
    const { deviceId } = request.params as { deviceId: string }
    const info = await tuyaService.getDeviceInfo(deviceId)

    if (!info) {
      return reply.status(444).send({ success: false, message: 'Tuya 디바이스를 찾을 수 없거나 연결 실패' })
    }

    return { success: true, data: info }
  })

  // Tuya 디바이스 제어 명령 전송 (스마트플러그 ON/OFF 등)
  fastify.post('/devices/:deviceId/command', async (request, reply) => {
    const { deviceId } = request.params as { deviceId: string }
    const body = commandSchema.parse(request.body)

    const success = await tuyaService.sendCommand(deviceId, [{ code: body.code, value: body.value }])

    if (!success) {
      return reply.status(400).send({ success: false, error: 'Tuya 제어 명령 실행 실패' })
    }

    return { success: true, message: `Tuya 장치 [${deviceId}] 명령 (${body.code} = ${body.value}) 성공!` }
  })
}
