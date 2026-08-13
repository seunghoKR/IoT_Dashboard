// stub routes for compilation
import type { FastifyInstance } from 'fastify'
export async function authRoutes(fastify: FastifyInstance) {
  fastify.post('/login', async () => ({ token: 'stub' }))
  fastify.post('/refresh', async () => ({ token: 'stub' }))
  fastify.delete('/logout', async () => ({ ok: true }))
}
export async function farmRoutes(fastify: FastifyInstance) {
  fastify.get('/', async () => ({ farms: [] }))
}
export async function deviceRoutes(fastify: FastifyInstance) {
  fastify.get('/', async () => ({ devices: [] }))
}
export async function automationRoutes(fastify: FastifyInstance) {
  fastify.get('/', async () => ({ rules: [] }))
}
export async function alertRoutes(fastify: FastifyInstance) {
  fastify.get('/', async () => ({ alerts: [] }))
}
export async function essRoutes(fastify: FastifyInstance) {
  fastify.get('/latest', async () => ({ ess: null }))
}
