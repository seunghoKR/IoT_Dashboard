/**
 * 액추에이터 제어 패널
 * 인터록 안전 기능이 내장된 개폐기/펌프/팬 제어 UI + Tuya 스마트플러그 연동
 */

import { useState } from 'react'
import { styled } from '../../lib/stitches.config'
import type { ActuatorStatus } from '../../stores/smartfarm.store'

interface ActuatorControlPanelProps {
  farmId: string
  houseId: string
  actuators: ActuatorStatus | null
}

export function ActuatorControlPanel({ farmId, houseId, actuators }: ActuatorControlPanelProps) {
  const [loading, setLoading] = useState<string | null>(null)
  const [tuyaPlugState, setTuyaPlugState] = useState<boolean>(true)

  const sendCommand = async (
    deviceId: string,
    command: 'OPEN' | 'CLOSE' | 'STOP' | 'ON' | 'OFF',
    position?: number
  ) => {
    setLoading(deviceId)
    try {
      const res = await fetch(`/api/farms/${farmId}/greenhouses/${houseId}/control`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ deviceId, command, position, source: 'manual' }),
      })
      const data = await res.json()
      if (!data.success) {
        alert(`⚠️ 인터록 차단: ${data.error}`)
      }
    } catch (error) {
      console.error('제어 명령 실패:', error)
    } finally {
      setLoading(null)
    }
  }

  // Tuya Smart Plug 직접 제어 (테스트 디바이스 ebb219afdebea03ba3shlz)
  const sendTuyaControl = async (turnOn: boolean) => {
    setLoading('tuya_plug')
    try {
      const sampleDeviceId = 'ebb219afdebea03ba3shlz'
      const res = await fetch(`/api/tuya/devices/${sampleDeviceId}/command`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ code: 'switch_1', value: turnOn }),
      })
      const data = await res.json()
      if (data.success) {
        setTuyaPlugState(turnOn)
      } else {
        alert(`Tuya 제어 실패: ${data.error}`)
      }
    } catch (err) {
      console.error('Tuya 제어 실패:', err)
    } finally {
      setLoading(null)
    }
  }

  return (
    <PanelGrid>
      {/* Tuya 연동 스마트 플러그 카드 */}
      <ControlCard style={{ gridColumn: '1 / -1', borderColor: 'hsl(140, 50%, 38%)', background: 'hsl(140, 60%, 98%)' }}>
        <CardTitle style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between' }}>
          <span>🌐 Tuya 스마트 플러그 (ID: ebb219af...)</span>
          <TuyaBadge>Tuya Cloud 연동</TuyaBadge>
        </CardTitle>
        <ActiveIndicator active={tuyaPlugState}>
          {tuyaPlugState ? '● 가동 중 (ON)' : '● 정지 (OFF)'}
        </ActiveIndicator>
        <ButtonRow>
          <PumpButton
            action="stop"
            disabled={!tuyaPlugState || loading === 'tuya_plug'}
            onClick={() => sendTuyaControl(false)}
          >
            🔌 전원 OFF
          </PumpButton>
          <PumpButton
            action="start"
            disabled={tuyaPlugState || loading === 'tuya_plug'}
            onClick={() => sendTuyaControl(true)}
          >
            ⚡ 전원 ON
          </PumpButton>
        </ButtonRow>
      </ControlCard>

      {/* 이중 보온덮개 */}
      <ControlCard>
        <CardTitle>🪟 이중 보온덮개</CardTitle>
        <PositionBar percent={actuators?.doubleCover.position ?? 0} />
        <PositionLabel>{actuators?.doubleCover.position ?? 0}%</PositionLabel>
        <StatusText state={actuators?.doubleCover.state ?? 'STOPPED'}>
          {actuators?.doubleCover.state ?? '—'}
        </StatusText>
        <ButtonRow>
          <MotorButton
            dir="close"
            disabled={loading === 'double_cover'}
            onClick={() => sendCommand('thermal_curtain', 'CLOSE')}
          >
            ▼ 내리기
          </MotorButton>
          <StopButton onClick={() => sendCommand('thermal_curtain', 'STOP')}>■ 정지</StopButton>
          <MotorButton
            dir="open"
            disabled={loading === 'double_cover'}
            onClick={() => sendCommand('thermal_curtain', 'OPEN')}
          >
            ▲ 올리기
          </MotorButton>
        </ButtonRow>
        <InterlockBadge>✅ 인터록: 활성화</InterlockBadge>
      </ControlCard>

      {/* 측창 개폐기 */}
      <ControlCard>
        <CardTitle>🌬 측창 개폐기</CardTitle>
        <PositionBar percent={actuators?.sideFlapLeft.position ?? 0} />
        <PositionLabel>좌 {actuators?.sideFlapLeft.position ?? 0}% · 우 {actuators?.sideFlapRight.position ?? 0}%</PositionLabel>
        <ButtonRow>
          <MotorButton
            dir="close"
            disabled={!!loading}
            onClick={() => {
              sendCommand('side_window_left', 'CLOSE')
              sendCommand('side_window_right', 'CLOSE')
            }}
          >
            ◀ 닫기
          </MotorButton>
          <StopButton onClick={() => {
            sendCommand('side_window_left', 'STOP')
            sendCommand('side_window_right', 'STOP')
          }}>■</StopButton>
          <MotorButton
            dir="open"
            disabled={!!loading}
            onClick={() => {
              sendCommand('side_window_left', 'OPEN')
              sendCommand('side_window_right', 'OPEN')
            }}
          >
            열기 ▶
          </MotorButton>
        </ButtonRow>
        <InterlockBadge>✅ 인터록: 활성화</InterlockBadge>
      </ControlCard>

      {/* 양수기 */}
      <ControlCard>
        <CardTitle>💧 양수기 (관수 펌프)</CardTitle>
        <ActiveIndicator active={actuators?.waterPump.active ?? false}>
          {actuators?.waterPump.active ? '● 가동 중' : '● 대기'}
        </ActiveIndicator>
        <SubInfo>오늘 가동: {Math.floor((actuators?.waterPump.totalRuntimeToday ?? 0) / 60)}분</SubInfo>
        <ButtonRow>
          <PumpButton
            action="stop"
            disabled={!actuators?.waterPump.active}
            onClick={() => sendCommand('water_pump', 'OFF')}
          >
            ■ 정지
          </PumpButton>
          <PumpButton
            action="start"
            disabled={actuators?.waterPump.active}
            onClick={() => sendCommand('water_pump', 'ON')}
          >
            ▶ 가동
          </PumpButton>
        </ButtonRow>
        <InterlockBadge>✅ 건수 보호: 활성화</InterlockBadge>
      </ControlCard>

      {/* 환풍기 */}
      <ControlCard>
        <CardTitle>🌀 환풍기</CardTitle>
        <SpeedDisplay>
          {actuators?.ventFan.speed === 0 ? '꺼짐' :
           actuators?.ventFan.speed === 1 ? '저속 (1단)' :
           actuators?.ventFan.speed === 2 ? '중속 (2단)' : '고속 (3단)'}
        </SpeedDisplay>
        <SpeedButtons>
          <SpeedBtn active={actuators?.ventFan.speed === 0} onClick={() => sendCommand('vent_fan', 'OFF')}>꺼짐</SpeedBtn>
          <SpeedBtn active={actuators?.ventFan.speed === 1} onClick={() => sendCommand('vent_fan', 'ON')}>저</SpeedBtn>
          <SpeedBtn active={actuators?.ventFan.speed === 2} onClick={() => sendCommand('vent_fan', 'ON')}>중</SpeedBtn>
          <SpeedBtn active={actuators?.ventFan.speed === 3} onClick={() => sendCommand('vent_fan', 'ON')}>고</SpeedBtn>
        </SpeedButtons>
      </ControlCard>
    </PanelGrid>
  )
}

// Styled Components
const PanelGrid = styled('div', {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '$4',
  '@mobile': { gridTemplateColumns: '1fr' },
})

const ControlCard = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$4',
  boxShadow: '$sm',
  display: 'flex',
  flexDirection: 'column',
  gap: '$3',
})

const CardTitle = styled('div', {
  fontSize: '$md',
  fontWeight: '$semibold',
  color: '$textPrimary',
})

const TuyaBadge = styled('span', {
  fontSize: '$xs',
  padding: '$1 $2',
  borderRadius: '$full',
  background: '$primary',
  color: '$textInverse',
  fontWeight: '$bold',
})

const PositionBar = styled('div', {
  height: '8px',
  background: '$bgMuted',
  borderRadius: '$full',
  position: 'relative',
  overflow: 'hidden',

  '&::after': {
    content: '""',
    position: 'absolute',
    left: 0, top: 0, bottom: 0,
    background: 'linear-gradient(90deg, $primary400, $primary)',
    borderRadius: '$full',
    transition: 'width $slow',
  },
}, (props: { percent?: number }) => ({
  '&::after': { width: `${props?.percent ?? 0}%` },
}))

const PositionLabel = styled('div', { fontSize: '$sm', color: '$textSecondary', fontWeight: '$medium' })

const StatusText = styled('div', {
  fontSize: '$xs',
  fontWeight: '$medium',
  variants: {
    state: {
      OPENING: { color: '$primary' },
      CLOSING: { color: '$warning' },
      OPEN: { color: '$success' },
      CLOSED: { color: '$textMuted' },
      STOPPED: { color: '$textMuted' },
      FAULT: { color: '$danger' },
    },
  },
})

const ButtonRow = styled('div', { display: 'flex', gap: '$2' })

const MotorButton = styled('button', {
  flex: 1,
  padding: '$2',
  borderRadius: '$sm',
  border: 'none',
  cursor: 'pointer',
  fontSize: '$sm',
  fontWeight: '$semibold',
  transition: 'all $fast',

  '&:disabled': { opacity: 0.5, cursor: 'not-allowed' },

  variants: {
    dir: {
      open: {
        background: '$primaryLight',
        color: '$primary',
        '&:hover:not(:disabled)': { background: '$primary100' },
      },
      close: {
        background: '$bgMuted',
        color: '$textSecondary',
        '&:hover:not(:disabled)': { background: '$border' },
      },
    },
  },
})

const StopButton = styled('button', {
  padding: '$2 $3',
  borderRadius: '$sm',
  border: '1px solid $border',
  background: '$bgCard',
  color: '$textPrimary',
  cursor: 'pointer',
  fontSize: '$sm',
  fontWeight: '$bold',
  '&:hover': { background: '$bgMuted' },
})

const InterlockBadge = styled('div', {
  fontSize: '$xs',
  color: '$success',
  fontWeight: '$medium',
})

const ActiveIndicator = styled('div', {
  fontSize: '$lg',
  fontWeight: '$bold',
  variants: {
    active: {
      true: { color: '$success' },
      false: { color: '$textMuted' },
    },
  },
})

const SubInfo = styled('div', { fontSize: '$sm', color: '$textMuted' })

const PumpButton = styled('button', {
  flex: 1,
  padding: '$2',
  borderRadius: '$sm',
  border: 'none',
  cursor: 'pointer',
  fontSize: '$sm',
  fontWeight: '$semibold',
  '&:disabled': { opacity: 0.4, cursor: 'not-allowed' },

  variants: {
    action: {
      start: {
        background: '$primaryLight',
        color: '$primary',
        '&:hover:not(:disabled)': { background: '$primary100' },
      },
      stop: {
        background: '$dangerLight',
        color: '$danger',
        '&:hover:not(:disabled)': { background: 'hsl(0, 80%, 92%)' },
      },
    },
  },
})

const SpeedDisplay = styled('div', {
  fontSize: '$lg',
  fontWeight: '$semibold',
  color: '$textPrimary',
})

const SpeedButtons = styled('div', { display: 'flex', gap: '$2' })

const SpeedBtn = styled('button', {
  flex: 1,
  padding: '$2',
  borderRadius: '$sm',
  border: '1px solid $border',
  background: '$bgMuted',
  color: '$textSecondary',
  cursor: 'pointer',
  fontSize: '$sm',
  fontWeight: '$medium',
  transition: 'all $fast',

  variants: {
    active: {
      true: {
        background: '$primaryLight',
        borderColor: '$primary',
        color: '$primary',
        fontWeight: '$bold',
      },
      false: {},
    },
  },
})
