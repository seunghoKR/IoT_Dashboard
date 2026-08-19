import { create } from 'zustand'
import { BuildingState, HvacState } from '../types/building.types'

interface BuildingStore extends BuildingState {
  // 모드 및 층 선택
  setActiveFloor: (floor: 1 | 2 | 3 | 'all') => void
  setSelectedRoom: (room: string | null) => void

  // 1층 제어
  toggleLobbyLight: (index: number) => void
  setAllLobbyLights: (on: boolean) => void
  toggleMenRestroomLight: () => void
  toggleMenRestroomFan: () => void
  toggleWomenRestroomLight: () => void
  toggleWomenRestroomFan: () => void
  toggleDoorLock: () => void
  setFirstFloorAc: (updates: Partial<HvacState>) => void

  // 2층 제어
  toggleSanctuaryLight: (index: number) => void
  setAllSanctuaryLights: (on: boolean) => void
  setSanctuaryHvac: (updates: Partial<HvacState>) => void

  // 3층 제어
  togglePastorRoomLight: (index: number) => void
  setAllPastorRoomLights: (on: boolean) => void
  toggleMeetingRoomLight: (index: number) => void
  setAllMeetingRoomLights: (on: boolean) => void

  // 마스터 제어 (일괄 끄기)
  turnOffFloor1: () => void
  turnOffFloor2: () => void
  turnOffFloor3: () => void
  turnOffAllBuildingLights: () => void
  turnOffAllBuildingDevices: () => void
}

export const useBuildingStore = create<BuildingStore>((set, get) => ({
  buildingName: '비전 스마트 센트럴 센터',
  activeFloor: 'all',
  selectedRoom: null,
  totalPowerWatts: 1420,
  monthlyKwh: 342.8,
  outdoorTemp: 24.5,
  securityArmed: true,

  floor1: {
    lobbyLights: [true, true, true, false, false, false],
    menRestroom: { light: true, fan: true, occupied: false },
    womenRestroom: { light: false, fan: false, occupied: false },
    doorLock: { locked: true, battery: 94, lastOpenedAt: '방금 전' },
    ac: {
      power: true,
      mode: 'cool',
      targetTemp: 23,
      currentTemp: 24.2,
      fanSpeed: 'auto',
    },
  },

  floor2: {
    sanctuaryLights: [true, true, true, true, true, true],
    hvac: {
      power: true,
      mode: 'cool',
      targetTemp: 22,
      currentTemp: 23.5,
      fanSpeed: 'medium',
    },
  },

  floor3: {
    pastorRoomLights: [true, false],
    meetingRoomLights: [false, false],
  },

  setActiveFloor: (floor) => set({ activeFloor: floor }),
  setSelectedRoom: (room) => set({ selectedRoom: room }),

  // 1층
  toggleLobbyLight: (index) =>
    set((state) => {
      const next = [...state.floor1.lobbyLights]
      next[index] = !next[index]
      return { floor1: { ...state.floor1, lobbyLights: next } }
    }),

  setAllLobbyLights: (on) =>
    set((state) => ({
      floor1: { ...state.floor1, lobbyLights: Array(6).fill(on) },
    })),

  toggleMenRestroomLight: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        menRestroom: {
          ...state.floor1.menRestroom,
          light: !state.floor1.menRestroom.light,
        },
      },
    })),

  toggleMenRestroomFan: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        menRestroom: {
          ...state.floor1.menRestroom,
          fan: !state.floor1.menRestroom.fan,
        },
      },
    })),

  toggleWomenRestroomLight: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        womenRestroom: {
          ...state.floor1.womenRestroom,
          light: !state.floor1.womenRestroom.light,
        },
      },
    })),

  toggleWomenRestroomFan: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        womenRestroom: {
          ...state.floor1.womenRestroom,
          fan: !state.floor1.womenRestroom.fan,
        },
      },
    })),

  toggleDoorLock: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        doorLock: {
          ...state.floor1.doorLock,
          locked: !state.floor1.doorLock.locked,
          lastOpenedAt: '방금 전 수동 제어',
        },
      },
    })),

  setFirstFloorAc: (updates) =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        ac: { ...state.floor1.ac, ...updates },
      },
    })),

  // 2층
  toggleSanctuaryLight: (index) =>
    set((state) => {
      const next = [...state.floor2.sanctuaryLights]
      next[index] = !next[index]
      return { floor2: { ...state.floor2, sanctuaryLights: next } }
    }),

  setAllSanctuaryLights: (on) =>
    set((state) => ({
      floor2: { ...state.floor2, sanctuaryLights: Array(6).fill(on) },
    })),

  setSanctuaryHvac: (updates) =>
    set((state) => ({
      floor2: {
        ...state.floor2,
        hvac: { ...state.floor2.hvac, ...updates },
      },
    })),

  // 3층
  togglePastorRoomLight: (index) =>
    set((state) => {
      const next = [...state.floor3.pastorRoomLights]
      next[index] = !next[index]
      return { floor3: { ...state.floor3, pastorRoomLights: next } }
    }),

  setAllPastorRoomLights: (on) =>
    set((state) => ({
      floor3: { ...state.floor3, pastorRoomLights: Array(2).fill(on) },
    })),

  toggleMeetingRoomLight: (index) =>
    set((state) => {
      const next = [...state.floor3.meetingRoomLights]
      next[index] = !next[index]
      return { floor3: { ...state.floor3, meetingRoomLights: next } }
    }),

  setAllMeetingRoomLights: (on) =>
    set((state) => ({
      floor3: { ...state.floor3, meetingRoomLights: Array(2).fill(on) },
    })),

  // 일괄 제어
  turnOffFloor1: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        lobbyLights: Array(6).fill(false),
        menRestroom: { ...state.floor1.menRestroom, light: false, fan: false },
        womenRestroom: { ...state.floor1.womenRestroom, light: false, fan: false },
      },
    })),

  turnOffFloor2: () =>
    set((state) => ({
      floor2: {
        ...state.floor2,
        sanctuaryLights: Array(6).fill(false),
      },
    })),

  turnOffFloor3: () =>
    set((state) => ({
      floor3: {
        ...state.floor3,
        pastorRoomLights: Array(2).fill(false),
        meetingRoomLights: Array(2).fill(false),
      },
    })),

  turnOffAllBuildingLights: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        lobbyLights: Array(6).fill(false),
        menRestroom: { ...state.floor1.menRestroom, light: false },
        womenRestroom: { ...state.floor1.womenRestroom, light: false },
      },
      floor2: {
        ...state.floor2,
        sanctuaryLights: Array(6).fill(false),
      },
      floor3: {
        ...state.floor3,
        pastorRoomLights: Array(2).fill(false),
        meetingRoomLights: Array(2).fill(false),
      },
    })),

  turnOffAllBuildingDevices: () =>
    set((state) => ({
      floor1: {
        ...state.floor1,
        lobbyLights: Array(6).fill(false),
        menRestroom: { ...state.floor1.menRestroom, light: false, fan: false },
        womenRestroom: { ...state.floor1.womenRestroom, light: false, fan: false },
        ac: { ...state.floor1.ac, power: false },
      },
      floor2: {
        ...state.floor2,
        sanctuaryLights: Array(6).fill(false),
        hvac: { ...state.floor2.hvac, power: false },
      },
      floor3: {
        ...state.floor3,
        pastorRoomLights: Array(2).fill(false),
        meetingRoomLights: Array(2).fill(false),
      },
    })),
}))
