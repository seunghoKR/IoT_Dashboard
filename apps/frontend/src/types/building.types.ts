/**
 * 스마트 빌딩 IoT 타입 정의
 */

export interface HvacState {
  power: boolean
  mode: 'cool' | 'heat' | 'fan' | 'auto'
  targetTemp: number
  currentTemp: number
  fanSpeed: 'low' | 'medium' | 'high' | 'auto'
}

export interface RestroomState {
  light: boolean
  fan: boolean
  occupied: boolean
}

export interface DoorLockState {
  locked: boolean
  battery: number
  lastOpenedAt?: string
}

export interface FirstFloorDevices {
  lobbyLights: boolean[] // 6구 (true/false)
  menRestroom: RestroomState
  womenRestroom: RestroomState
  doorLock: DoorLockState
  ac: HvacState
}

export interface SecondFloorDevices {
  sanctuaryLights: boolean[] // 6구 (예배실 조명)
  hvac: HvacState // 예배실 냉난방기
}

export interface ThirdFloorDevices {
  pastorRoomLights: boolean[] // 2구 (목양실)
  meetingRoomLights: boolean[] // 2구 (미팅룸)
}

export interface BuildingState {
  buildingName: string
  activeFloor: 1 | 2 | 3 | 'all'
  selectedRoom: string | null
  totalPowerWatts: number
  monthlyKwh: number
  outdoorTemp: number
  securityArmed: boolean
  floor1: FirstFloorDevices
  floor2: SecondFloorDevices
  floor3: ThirdFloorDevices
}
