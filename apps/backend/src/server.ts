/**
 * 스마트팜 대시보드 - Fastify 메인 서버
 * Node.js 22 + Fastify 5 + Socket.io + MQTT Bridge + Tuya Cloud Open API
 */

import Fastify from 'fastify'
import fastifyCors from '@fastify/cors'
import fastifyWebsocket from '@fastify/websocket'
import fastifyJwt from '@fastify/jwt'
import fastifyHelmet from '@fastify/helmet'
import fastifyRateLimit from '@fastify/rate-limit'
import { mqttService } from './services/mqtt.service.js'
import { tuyaService } from './services/tuya.service.js'
import { websocketHandler } from './plugins/websocket.plugin.js'
import { authRoutes } from './routes/auth.routes.js'
import { farmRoutes } from './routes/farm.routes.js'
import { greenhouseRoutes } from './routes/greenhouse.routes.js'
import { deviceRoutes } from './routes/device.routes.js'
import { automationRoutes } from './routes/automation.routes.js'
import { alertRoutes } from './routes/alert.routes.js'
import { essRoutes } from './routes/ess.routes.js'
import { tuyaRoutes } from './routes/tuya.routes.js'
import { logger } from './lib/logger.js'
import 'dotenv/config'

const PORT = parseInt(process.env.PORT ?? '3000')
const HOST = process.env.HOST ?? '0.0.0.0'

async function buildServer() {
  const fastify = Fastify({
    logger: {
      level: process.env.NODE_ENV === 'production' ? 'info' : 'debug',
    },
  })

  // ── 보안 플러그인 ──────────────────────────────────────────────────
  await fastify.register(fastifyHelmet, {
    contentSecurityPolicy: false,
  })

  await fastify.register(fastifyRateLimit, {
    max: 100,
    timeWindow: '1 minute',
    skipOnError: true,
  })

  // ── CORS ──────────────────────────────────────────────────────────
  await fastify.register(fastifyCors, {
    origin: process.env.CORS_ORIGIN ?? 'http://localhost:5173',
    methods: ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'],
    allowedHeaders: ['Content-Type', 'Authorization'],
    credentials: true,
  })

  // ── JWT 인증 ──────────────────────────────────────────────────────
  await fastify.register(fastifyJwt, {
    secret: process.env.JWT_SECRET ?? 'smartfarm-super-secret-change-in-prod',
    sign: { expiresIn: process.env.JWT_EXPIRES_IN ?? '24h' },
  })

  // ── WebSocket (실시간 센서 데이터 Push) ───────────────────────────
  await fastify.register(fastifyWebsocket)
  await fastify.register(websocketHandler)

  // ── REST API 라우터 ───────────────────────────────────────────────
  await fastify.register(authRoutes, { prefix: '/api/auth' })
  await fastify.register(farmRoutes, { prefix: '/api/farms' })
  await fastify.register(greenhouseRoutes, { prefix: '/api/farms' })
  await fastify.register(deviceRoutes, { prefix: '/api/devices' })
  await fastify.register(automationRoutes, { prefix: '/api/automations' })
  await fastify.register(alertRoutes, { prefix: '/api/alerts' })
  await fastify.register(essRoutes, { prefix: '/api/ess' })
  await fastify.register(tuyaRoutes, { prefix: '/api/tuya' })

  // ── 헬스체크 ─────────────────────────────────────────────────────
  fastify.get('/health', async () => ({
    status: 'ok',
    timestamp: new Date().toISOString(),
    mqtt: mqttService.isConnected(),
    tuya: true,
    version: '1.0.0',
  }))

  // ── 에러 핸들러 ──────────────────────────────────────────────────
  fastify.setErrorHandler((error, request, reply) => {
    logger.error({ err: error, url: request.url }, 'Unhandled error')
    reply.status(error.statusCode ?? 500).send({
      success: false,
      error: error.message,
      code: error.code,
    })
  })

  return fastify
}

async function start() {
  const server = await buildServer()

  // ── MQTT 서비스 시작 ─────────────────────────────────────────────
  try {
    await mqttService.connect()
    logger.info('✅ MQTT 서비스 연결 완료')
  } catch (err) {
    logger.warn('⚠️ MQTT 서버 연결 실패 (Tuya API 모드로 계속 작동합니다)')
  }

  // ── Tuya API 서비스 시작 ─────────────────────────────────────────
  tuyaService.initialize()
  tuyaService.startPolling()

  // ── 서버 시작 ─────────────────────────────────────────────────────
  try {
    await server.listen({ port: PORT, host: HOST })
    logger.info(`🌱 Tuya 연동 스마트팜 백엔드 서버 실행 중: http://${HOST}:${PORT}`)
  } catch (err) {
    logger.error(err)
    process.exit(1)
  }

  // 정상 종료 처리
  const shutdown = async () => {
    logger.info('서버 종료 중...')
    tuyaService.stopPolling()
    await mqttService.disconnect()
    await server.close()
    process.exit(0)
  }

  process.on('SIGTERM', shutdown)
  process.on('SIGINT', shutdown)
}

start()
