/**
 * InfluxDB 서비스
 * 시계열 센서 데이터 읽기/쓰기
 */

import { InfluxDB, Point, WriteApi, QueryApi } from '@influxdata/influxdb-client'
import type { SensorPayload, EssStatusPayload } from './mqtt.service.js'
import { logger } from '../lib/logger.js'
import 'dotenv/config'

class InfluxService {
  private writeApi: WriteApi
  private queryApi: QueryApi

  constructor() {
    const client = new InfluxDB({
      url: process.env.INFLUXDB_URL ?? 'http://localhost:8086',
      token: process.env.INFLUXDB_TOKEN ?? 'my-super-secret-auth-token',
    })

    this.writeApi = client.getWriteApi(
      process.env.INFLUXDB_ORG ?? 'smartfarm',
      process.env.INFLUXDB_BUCKET ?? 'greenhouse_telemetry',
      'ms' // 타임스탬프 정밀도: 밀리초
    )

    this.queryApi = client.getQueryApi(process.env.INFLUXDB_ORG ?? 'smartfarm')
  }

  /**
   * 센서 데이터 저장
   */
  async writeSensorData(
    farmId: string,
    houseId: string,
    sensorType: string,
    data: SensorPayload
  ): Promise<void> {
    try {
      const measurement = `greenhouse_${sensorType}` // greenhouse_environment, greenhouse_soil...
      const point = new Point(measurement)
        .tag('farm_id', farmId)
        .tag('house_id', houseId)
        .tag('sensor_id', data.sensor_id)
        .timestamp(new Date(data.timestamp))

      // 환경 센서 필드
      if (data.air_temp !== undefined) point.floatField('air_temp', data.air_temp)
      if (data.air_humidity !== undefined) point.floatField('air_humidity', data.air_humidity)
      if (data.co2_ppm !== undefined) point.floatField('co2_ppm', data.co2_ppm)
      if (data.light_lux !== undefined) point.floatField('light_lux', data.light_lux)
      if (data.par !== undefined) point.floatField('par', data.par)

      // 토양 센서 필드
      if (data.soil_moisture !== undefined) point.floatField('soil_moisture', data.soil_moisture)
      if (data.soil_temp !== undefined) point.floatField('soil_temp', data.soil_temp)
      if (data.ec_value !== undefined) point.floatField('ec_value', data.ec_value)
      if (data.ph_value !== undefined) point.floatField('ph_value', data.ph_value)

      // 기상 센서 필드
      if (data.wind_speed !== undefined) point.floatField('wind_speed', data.wind_speed)
      if (data.wind_dir !== undefined) point.floatField('wind_dir', data.wind_dir)
      if (data.rain_detected !== undefined) point.booleanField('rain_detected', data.rain_detected)
      if (data.solar_radiation !== undefined) point.floatField('solar_radiation', data.solar_radiation)
      if (data.outdoor_temp !== undefined) point.floatField('outdoor_temp', data.outdoor_temp)
      if (data.outdoor_humidity !== undefined) point.floatField('outdoor_humidity', data.outdoor_humidity)

      // 디바이스 헬스
      if (data.rssi !== undefined) point.intField('rssi', data.rssi)
      if (data.heap_free !== undefined) point.intField('heap_free', data.heap_free)
      if (data.uptime !== undefined) point.intField('uptime', data.uptime)

      this.writeApi.writePoint(point)
      await this.writeApi.flush()
    } catch (error) {
      logger.error({ err: error, farmId, houseId, sensorType }, 'InfluxDB 쓰기 오류')
    }
  }

  /**
   * ESS 전력 상태 저장
   */
  async writeEssStatus(farmId: string, data: EssStatusPayload): Promise<void> {
    try {
      const point = new Point('ess_monitoring')
        .tag('farm_id', farmId)
        .floatField('soc_percent', data.soc_percent)
        .floatField('voltage_v', data.voltage_v)
        .floatField('current_a', data.current_a)
        .floatField('solar_power_w', data.solar_power_w)
        .floatField('load_power_w', data.load_power_w)
        .floatField('battery_temp_c', data.battery_temp_c)
        .timestamp(new Date(data.timestamp))

      this.writeApi.writePoint(point)
      await this.writeApi.flush()
    } catch (error) {
      logger.error({ err: error, farmId }, 'ESS InfluxDB 쓰기 오류')
    }
  }

  /**
   * 시계열 데이터 조회 (프론트엔드 차트용)
   */
  async queryTelemetry(options: {
    farmId: string
    houseId: string
    measurement: string
    field: string
    range: string   // '-1h', '-24h', '-7d', '-30d'
    aggregateWindow?: string  // '1m', '5m', '1h'
  }): Promise<Array<{ time: string; value: number }>> {
    const { farmId, houseId, measurement, field, range, aggregateWindow = '5m' } = options

    const fluxQuery = `
      from(bucket: "${process.env.INFLUXDB_BUCKET ?? 'greenhouse_telemetry'}")
        |> range(start: ${range})
        |> filter(fn: (r) => r._measurement == "${measurement}")
        |> filter(fn: (r) => r.farm_id == "${farmId}")
        |> filter(fn: (r) => r.house_id == "${houseId}")
        |> filter(fn: (r) => r._field == "${field}")
        |> aggregateWindow(every: ${aggregateWindow}, fn: mean, createEmpty: false)
        |> yield(name: "mean")
    `

    const results: Array<{ time: string; value: number }> = []

    return new Promise((resolve, reject) => {
      this.queryApi.queryRows(fluxQuery, {
        next: (row, tableMeta) => {
          const obj = tableMeta.toObject(row)
          results.push({
            time: obj._time as string,
            value: obj._value as number,
          })
        },
        error: (error) => {
          logger.error({ err: error, options }, 'InfluxDB 조회 오류')
          reject(error)
        },
        complete: () => resolve(results),
      })
    })
  }

  /**
   * 최신 센서값 조회 (대시보드 현재값 표시)
   */
  async queryLatestValues(farmId: string, houseId: string): Promise<Record<string, number>> {
    const fluxQuery = `
      from(bucket: "${process.env.INFLUXDB_BUCKET ?? 'greenhouse_telemetry'}")
        |> range(start: -10m)
        |> filter(fn: (r) => r.farm_id == "${farmId}")
        |> filter(fn: (r) => r.house_id == "${houseId}")
        |> last()
        |> pivot(rowKey: ["_time"], columnKey: ["_field"], valueColumn: "_value")
    `

    const results: Record<string, number> = {}

    return new Promise((resolve, reject) => {
      this.queryApi.queryRows(fluxQuery, {
        next: (row, tableMeta) => {
          const obj = tableMeta.toObject(row)
          Object.assign(results, obj)
        },
        error: (error) => reject(error),
        complete: () => resolve(results),
      })
    })
  }
}

export const influxService = new InfluxService()
