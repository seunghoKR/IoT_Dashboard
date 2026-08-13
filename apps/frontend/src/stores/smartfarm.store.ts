/**
 * Zustand 전역 상태 관리
 * - 실시간 텔레메트리 데이터 (5동)
 * - ESS 전력 상태
 * - WebSocket 연결 상태
 * - 테마 / 사용자 설정
 */

import { create } from 'zustand'
import { immer } from 'zustand/middleware/immer'

// ── 타입 정의 ─────────────────────────────────────────────────────────
export interface SensorData {
  air_temp: number
  air_humidity: number
  co2_ppm: number
  soil_moisture: number
  light_lux: number
  wind_speed?: number
  rain_detected?: boolean
}

export interface ActuatorStatus {
  doubleCover: { position: number; state: string }
  sideFlapLeft: { position: number; state: string }
  sideFlapRight: { position: number; state: string }
  roofVent: { position: number; state: string }
  waterPump: { active: boolean; totalRuntimeToday: number }
  ventFan: { speed: 0 | 1 | 2 | 3; active: boolean }
  circulationFan: { active: boolean }
  co2Supply: { active: boolean }
  boilerValve: { active: boolean }
}

export interface GreenhouseState {
  houseId: string
  name: string
  cropType: string
  isOnline: boolean
  lastUpdated: string
  sensors: SensorData | null
  actuators: ActuatorStatus | null
  alertLevel: 'normal' | 'warning' | 'critical'
}

export interface EssState {
  soc_percent: number
  voltage_v: number
  current_a: number
  solar_power_w: number
  load_power_w: number
  battery_temp_c: number
  estimatedHoursRemaining: number
  timestamp: string
}

export interface Alert {
  id: string
  level: 'info' | 'warning' | 'critical'
  houseId?: string
  message: string
  timestamp: string
  read: boolean
}

// ── 스토어 ────────────────────────────────────────────────────────────
interface SmartFarmStore {
  // 연결 상태
  wsConnected: boolean
  mqttConnected: boolean

  // 농장 데이터
  farmId: string
  farmName: string

  // 비닐하우스 5동 데이터
  greenhouses: Record<string, GreenhouseState>

  // 외부 기상 데이터
  outdoorWeather: {
    temperature: number
    humidity: number
    windSpeed: number
    rainDetected: boolean
    solarRadiation: number
  } | null

  // ESS 전력 상태
  ess: EssState | null

  // 알림 목록
  alerts: Alert[]
  unreadAlertCount: number

  // UI 상태
  selectedHouseId: string | null
  theme: 'light' | 'dark' | 'high-contrast'
  sidebarOpen: boolean

  // 액션
  setWsConnected: (connected: boolean) => void
  updateGreenhouseSensors: (houseId: string, sensors: SensorData, timestamp: string) => void
  updateActuatorState: (houseId: string, deviceId: string, state: any) => void
  updateEss: (ess: EssState) => void
  updateOutdoorWeather: (weather: SmartFarmStore['outdoorWeather']) => void
  addAlert: (alert: Omit<Alert, 'id' | 'read'>) => void
  markAlertRead: (id: string) => void
  clearAlerts: () => void
  setSelectedHouse: (houseId: string | null) => void
  setTheme: (theme: SmartFarmStore['theme']) => void
  setSidebarOpen: (open: boolean) => void
}

export const useSmartFarmStore = create<SmartFarmStore>()(
  immer((set) => ({
    // 초기 상태
    wsConnected: false,
    mqttConnected: false,
    farmId: 'cheongjeong',
    farmName: '청정원 스마트팜',

    greenhouses: {
      h01: {
        houseId: 'h01',
        name: '1동 방울토마토',
        cropType: 'cherry_tomato',
        isOnline: false,
        lastUpdated: '',
        sensors: null,
        actuators: null,
        alertLevel: 'normal',
      },
      h02: {
        houseId: 'h02',
        name: '2동 방울토마토',
        cropType: 'cherry_tomato',
        isOnline: false,
        lastUpdated: '',
        sensors: null,
        actuators: null,
        alertLevel: 'normal',
      },
      h03: {
        houseId: 'h03',
        name: '3동 방울토마토',
        cropType: 'cherry_tomato',
        isOnline: false,
        lastUpdated: '',
        sensors: null,
        actuators: null,
        alertLevel: 'normal',
      },
      h04: {
        houseId: 'h04',
        name: '4동 오이',
        cropType: 'cucumber',
        isOnline: false,
        lastUpdated: '',
        sensors: null,
        actuators: null,
        alertLevel: 'normal',
      },
      h05: {
        houseId: 'h05',
        name: '5동 파프리카',
        cropType: 'paprika',
        isOnline: false,
        lastUpdated: '',
        sensors: null,
        actuators: null,
        alertLevel: 'normal',
      },
    },

    outdoorWeather: null,
    ess: null,
    alerts: [],
    unreadAlertCount: 0,
    selectedHouseId: null,
    theme: 'light',
    sidebarOpen: true,

    // ── 액션 구현 ──────────────────────────────────────────────────
    setWsConnected: (connected) => set((state) => {
      state.wsConnected = connected
    }),

    updateGreenhouseSensors: (houseId, sensors, timestamp) => set((state) => {
      if (state.greenhouses[houseId]) {
        state.greenhouses[houseId].sensors = sensors
        state.greenhouses[houseId].lastUpdated = timestamp
        state.greenhouses[houseId].isOnline = true

        // 알림 레벨 자동 계산
        let alertLevel: 'normal' | 'warning' | 'critical' = 'normal'
        if (
          sensors.air_temp > 38 ||
          sensors.air_humidity < 30 ||
          sensors.co2_ppm > 3000
        ) {
          alertLevel = 'critical'
        } else if (
          sensors.air_temp > 33 ||
          sensors.air_humidity < 50 ||
          sensors.soil_moisture < 30
        ) {
          alertLevel = 'warning'
        }
        state.greenhouses[houseId].alertLevel = alertLevel
      }
    }),

    updateActuatorState: (houseId, deviceId, deviceState) => set((state) => {
      if (state.greenhouses[houseId] && state.greenhouses[houseId].actuators) {
        const actuators = state.greenhouses[houseId].actuators!
        ;(actuators as any)[deviceId] = deviceState
      }
    }),

    updateEss: (ess) => set((state) => {
      // 잔여 시간 계산 (방전 중일 때)
      const powerW = Math.abs(ess.load_power_w - ess.solar_power_w)
      const capacityWh = 10000 // 10kWh ESS
      const remainingWh = (ess.soc_percent / 100) * capacityWh
      const estimatedHours = powerW > 0 ? remainingWh / powerW : 999

      state.ess = { ...ess, estimatedHoursRemaining: Math.round(estimatedHours * 10) / 10 }
    }),

    updateOutdoorWeather: (weather) => set((state) => {
      state.outdoorWeather = weather
    }),

    addAlert: (alert) => set((state) => {
      const newAlert: Alert = {
        ...alert,
        id: crypto.randomUUID(),
        read: false,
      }
      state.alerts.unshift(newAlert) // 최신순
      if (state.alerts.length > 100) state.alerts.pop() // 최대 100개 유지
      state.unreadAlertCount = state.alerts.filter((a) => !a.read).length
    }),

    markAlertRead: (id) => set((state) => {
      const alert = state.alerts.find((a) => a.id === id)
      if (alert) {
        alert.read = true
        state.unreadAlertCount = state.alerts.filter((a) => !a.read).length
      }
    }),

    clearAlerts: () => set((state) => {
      state.alerts = []
      state.unreadAlertCount = 0
    }),

    setSelectedHouse: (houseId) => set((state) => {
      state.selectedHouseId = houseId
    }),

    setTheme: (theme) => set((state) => {
      state.theme = theme
      document.documentElement.className = theme === 'light' ? '' : `${theme}-theme`
    }),

    setSidebarOpen: (open) => set((state) => {
      state.sidebarOpen = open
    }),
  }))
)
