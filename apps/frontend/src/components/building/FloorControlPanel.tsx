/**
 * 층별 장치 제어 패널 (1층, 2층, 3층) - 태블릿 친화형 정사각형 스위치 적용
 */

import React from 'react'
import { styled } from '../../lib/stitches.config'
import { useBuildingStore } from '../../stores/building.store'

export function FloorControlPanel() {
  const {
    activeFloor,
    floor1,
    floor2,
    floor3,
    // 1층
    toggleLobbyLight,
    setAllLobbyLights,
    toggleMenRestroomLight,
    toggleMenRestroomFan,
    toggleWomenRestroomLight,
    toggleWomenRestroomFan,
    toggleDoorLock,
    setFirstFloorAc,
    turnOffFloor1,
    // 2층
    toggleSanctuaryLight,
    setAllSanctuaryLights,
    setSanctuaryHvac,
    turnOffFloor2,
    // 3층
    togglePastorRoomLight,
    setAllPastorRoomLights,
    toggleMeetingRoomLight,
    setAllMeetingRoomLights,
    turnOffFloor3,
  } = useBuildingStore()

  const showFloor3 = activeFloor === 'all' || activeFloor === 3
  const showFloor2 = activeFloor === 'all' || activeFloor === 2
  const showFloor1 = activeFloor === 'all' || activeFloor === 1

  return (
    <PanelContainer>
      {/* 3층 섹션 */}
      {showFloor3 && (
        <FloorSectionCard>
          <SectionHeader>
            <HeaderLeft>
              <FloorBadge color="purple">3F</FloorBadge>
              <div>
                <SectionTitle>3층 목양실 & 미팅룸</SectionTitle>
              </div>
            </HeaderLeft>
            <SectionActions>
              <FloorOffSmallBtn onClick={turnOffFloor3}>
                3F 소등
              </FloorOffSmallBtn>
            </SectionActions>
          </SectionHeader>

          <ControlGrid cols={2}>
            {/* 목양실 */}
            <DeviceCard>
              <DeviceHeader>
                <DeviceTitle>📖 목양실 전등 (2구)</DeviceTitle>
                <AllBtn
                  onClick={() =>
                    setAllPastorRoomLights(
                      !floor3.pastorRoomLights.every(Boolean)
                    )
                  }
                >
                  전체
                </AllBtn>
              </DeviceHeader>
              <SquareSwitchRow>
                {floor3.pastorRoomLights.map((on, idx) => (
                  <SquareTouchBtn
                    key={idx}
                    active={on}
                    onClick={() => togglePastorRoomLight(idx)}
                  >
                    <SwitchIcon>💡</SwitchIcon>
                    <SwitchLabel>{idx + 1}구</SwitchLabel>
                  </SquareTouchBtn>
                ))}
              </SquareSwitchRow>
            </DeviceCard>

            {/* 미팅룸 */}
            <DeviceCard>
              <DeviceHeader>
                <DeviceTitle>👥 미팅룸 전등 (2구)</DeviceTitle>
                <AllBtn
                  onClick={() =>
                    setAllMeetingRoomLights(
                      !floor3.meetingRoomLights.every(Boolean)
                    )
                  }
                >
                  전체
                </AllBtn>
              </DeviceHeader>
              <SquareSwitchRow>
                {floor3.meetingRoomLights.map((on, idx) => (
                  <SquareTouchBtn
                    key={idx}
                    active={on}
                    onClick={() => toggleMeetingRoomLight(idx)}
                  >
                    <SwitchIcon>💡</SwitchIcon>
                    <SwitchLabel>{idx + 1}구</SwitchLabel>
                  </SquareTouchBtn>
                ))}
              </SquareSwitchRow>
            </DeviceCard>
          </ControlGrid>
        </FloorSectionCard>
      )}

      {/* 2층 섹션 */}
      {showFloor2 && (
        <FloorSectionCard>
          <SectionHeader>
            <HeaderLeft>
              <FloorBadge color="blue">2F</FloorBadge>
              <div>
                <SectionTitle>2층 대예배실 (Sanctuary)</SectionTitle>
              </div>
            </HeaderLeft>
            <SectionActions>
              <FloorOffSmallBtn onClick={turnOffFloor2}>
                2F 소등
              </FloorOffSmallBtn>
            </SectionActions>
          </SectionHeader>

          <ControlGrid cols={2}>
            {/* 6구 조명 정사각형 스위치 */}
            <DeviceCard>
              <DeviceHeader>
                <DeviceTitle>💡 예배실 메인 조명 (6구)</DeviceTitle>
                <AllBtn
                  onClick={() =>
                    setAllSanctuaryLights(
                      !floor2.sanctuaryLights.every(Boolean)
                    )
                  }
                >
                  전체
                </AllBtn>
              </DeviceHeader>
              <SquareSwitchRow>
                {floor2.sanctuaryLights.map((on, idx) => (
                  <SquareTouchBtn
                    key={idx}
                    active={on}
                    onClick={() => toggleSanctuaryLight(idx)}
                  >
                    <SwitchIcon>💡</SwitchIcon>
                    <SwitchLabel>{idx + 1}구</SwitchLabel>
                  </SquareTouchBtn>
                ))}
              </SquareSwitchRow>
            </DeviceCard>

            {/* 냉난방기 */}
            <DeviceCard>
              <DeviceHeader>
                <DeviceTitle>❄️ 시스템 냉난방기</DeviceTitle>
                <PowerSwitch
                  active={floor2.hvac.power}
                  onClick={() => setSanctuaryHvac({ power: !floor2.hvac.power })}
                >
                  {floor2.hvac.power ? '가동중' : '정지'}
                </PowerSwitch>
              </DeviceHeader>

              <HvacBody disabled={!floor2.hvac.power}>
                <TempDisplayRow>
                  <div>
                    <HvacLabel>설정 온도</HvacLabel>
                    <TargetTempText>{floor2.hvac.targetTemp}°C</TargetTempText>
                  </div>
                  <TempStepper>
                    <StepBtn
                      disabled={!floor2.hvac.power}
                      onClick={() =>
                        setSanctuaryHvac({ targetTemp: floor2.hvac.targetTemp - 1 })
                      }
                    >
                      -
                    </StepBtn>
                    <StepBtn
                      disabled={!floor2.hvac.power}
                      onClick={() =>
                        setSanctuaryHvac({ targetTemp: floor2.hvac.targetTemp + 1 })
                      }
                    >
                      +
                    </StepBtn>
                  </TempStepper>
                  <ModeButtonGroup>
                    {(['cool', 'heat'] as const).map((mode) => (
                      <ModeBtn
                        key={mode}
                        active={floor2.hvac.mode === mode}
                        disabled={!floor2.hvac.power}
                        onClick={() => setSanctuaryHvac({ mode })}
                      >
                        {mode === 'cool' ? '❄️ 냉방' : '🔥 난방'}
                      </ModeBtn>
                    ))}
                  </ModeButtonGroup>
                </TempDisplayRow>
              </HvacBody>
            </DeviceCard>
          </ControlGrid>
        </FloorSectionCard>
      )}

      {/* 1층 섹션 */}
      {showFloor1 && (
        <FloorSectionCard>
          <SectionHeader>
            <HeaderLeft>
              <FloorBadge color="green">1F</FloorBadge>
              <div>
                <SectionTitle>1층 메인 로비 & 편의시설 & 현관</SectionTitle>
              </div>
            </HeaderLeft>
            <SectionActions>
              <FloorOffSmallBtn onClick={turnOffFloor1}>
                1F 소등
              </FloorOffSmallBtn>
            </SectionActions>
          </SectionHeader>

          <ControlGrid cols={2}>
            {/* 로비 전등 6구 정사각형 스위치 */}
            <DeviceCard>
              <DeviceHeader>
                <DeviceTitle>💡 메인 로비 조명 (6구)</DeviceTitle>
                <AllBtn
                  onClick={() =>
                    setAllLobbyLights(!floor1.lobbyLights.every(Boolean))
                  }
                >
                  전체
                </AllBtn>
              </DeviceHeader>
              <SquareSwitchRow>
                {floor1.lobbyLights.map((on, idx) => (
                  <SquareTouchBtn
                    key={idx}
                    active={on}
                    onClick={() => toggleLobbyLight(idx)}
                  >
                    <SwitchIcon>💡</SwitchIcon>
                    <SwitchLabel>{idx + 1}구</SwitchLabel>
                  </SquareTouchBtn>
                ))}
              </SquareSwitchRow>
            </DeviceCard>

            {/* 화장실 & 도어락 */}
            <DeviceCard>
              <DeviceHeader>
                <DeviceTitle>🚻 화장실 & 🚪 도어락</DeviceTitle>
              </DeviceHeader>
              <FacilityRow>
                <SquareSwitchRow>
                  <SquareTouchBtn
                    active={floor1.menRestroom.light}
                    onClick={toggleMenRestroomLight}
                    title="남성 화장실"
                  >
                    <SwitchIcon>🚹</SwitchIcon>
                    <SwitchLabel>남화</SwitchLabel>
                  </SquareTouchBtn>
                  <SquareTouchBtn
                    active={floor1.womenRestroom.light}
                    onClick={toggleWomenRestroomLight}
                    title="여성 화장실"
                  >
                    <SwitchIcon>🚺</SwitchIcon>
                    <SwitchLabel>여화</SwitchLabel>
                  </SquareTouchBtn>
                </SquareSwitchRow>

                <DoorControlButton
                  locked={floor1.doorLock.locked}
                  onClick={toggleDoorLock}
                >
                  {floor1.doorLock.locked ? '🔓 문 열기' : '🔒 문 잠그기'}
                </DoorControlButton>
              </FacilityRow>
            </DeviceCard>
          </ControlGrid>
        </FloorSectionCard>
      )}
    </PanelContainer>
  )
}

const PanelContainer = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$3',
})

const FloorSectionCard = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$3 $4',
  display: 'flex',
  flexDirection: 'column',
  gap: '$3',
  boxShadow: '$sm',
})

const SectionHeader = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  paddingBottom: '$2',
  borderBottom: '1px solid $border',
})

const HeaderLeft = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$2',
})

const FloorBadge = styled('div', {
  fontSize: '$xs',
  fontWeight: '$bold',
  padding: '1px 6px',
  borderRadius: '$sm',
  variants: {
    color: {
      purple: { background: '#F3E8FF', color: '#7E22CE' },
      blue: { background: '#DBEAFE', color: '#1D4ED8' },
      green: { background: '#DCFCE7', color: '#15803D' },
    },
  },
})

const SectionTitle = styled('h3', {
  fontSize: '$sm',
  fontWeight: '$bold',
  color: '$textPrimary',
})

const SectionActions = styled('div', {})

const FloorOffSmallBtn = styled('button', {
  background: '$bgMuted',
  color: '$textSecondary',
  border: '1px solid $border',
  borderRadius: '$sm',
  padding: '2px 8px',
  fontSize: '$xs',
  fontWeight: '$semibold',
  cursor: 'pointer',
  transition: 'all $fast',

  '&:hover': {
    background: '#FEE2E2',
    color: '#DC2626',
    borderColor: '#FCA5A5',
  },
})

const ControlGrid = styled('div', {
  display: 'grid',
  gap: '$3',

  variants: {
    cols: {
      2: {
        gridTemplateColumns: 'repeat(2, 1fr)',
        '@tablet': { gridTemplateColumns: '1fr' },
      },
    },
  },
})

const DeviceCard = styled('div', {
  background: '$bgBase',
  border: '1px solid $border',
  borderRadius: '$sm',
  padding: '$3',
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
})

const DeviceHeader = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
})

const DeviceTitle = styled('h4', {
  fontSize: '$xs',
  fontWeight: '$semibold',
  color: '$textPrimary',
})

const AllBtn = styled('button', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$sm',
  padding: '1px 6px',
  fontSize: '10px',
  fontWeight: '$medium',
  color: '$textSecondary',
  cursor: 'pointer',

  '&:hover': {
    borderColor: '$primary',
    color: '$primary',
  },
})

/* 🔲 완벽한 정사각형 터치 스위치 버튼 */
const SquareSwitchRow = styled('div', {
  display: 'flex',
  gap: '$2',
  alignItems: 'center',
  flexWrap: 'wrap',
})

const SquareTouchBtn = styled('button', {
  width: '46px',
  height: '46px',
  minWidth: '46px',
  minHeight: '46px',
  aspectRatio: '1 / 1',
  background: '$bgCard',
  border: '1.5px solid $border',
  borderRadius: '$md',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  justifyContent: 'center',
  gap: '2px',
  cursor: 'pointer',
  transition: 'all 0.18s ease',
  padding: 0,

  '&:hover': {
    borderColor: '#F59E0B',
    transform: 'translateY(-1px)',
  },
  '&:active': {
    transform: 'scale(0.95)',
  },

  variants: {
    active: {
      true: {
        background: 'linear-gradient(180deg, #FEF9C3 0%, #FEF08A 100%)',
        borderColor: '#F59E0B',
        boxShadow: '0 0 10px rgba(245, 158, 11, 0.35)',
        color: '#854D0E',
      },
      false: {
        color: '$textMuted',
      },
    },
  },
})

const SwitchIcon = styled('span', {
  fontSize: '14px',
  lineHeight: 1,
})

const SwitchLabel = styled('span', {
  fontSize: '10px',
  fontWeight: '$bold',
  lineHeight: 1,
})

const PowerSwitch = styled('button', {
  border: 'none',
  padding: '2px 8px',
  borderRadius: '$full',
  fontSize: '10px',
  fontWeight: '$bold',
  cursor: 'pointer',
  transition: 'all $fast',

  variants: {
    active: {
      true: {
        background: '#10B981',
        color: '#FFFFFF',
      },
      false: {
        background: '#6B7280',
        color: '#FFFFFF',
      },
    },
  },
})

const HvacBody = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
  transition: 'opacity 0.2s ease',

  variants: {
    disabled: {
      true: {
        opacity: 0.45,
        pointerEvents: 'none',
      },
    },
  },
})

const TempDisplayRow = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  background: '$bgCard',
  padding: '$2 $3',
  borderRadius: '$md',
  border: '1px solid $border',
})

const HvacLabel = styled('div', {
  fontSize: '10px',
  color: '$textMuted',
})

const TargetTempText = styled('div', {
  fontSize: '$lg',
  fontWeight: '$bold',
  color: '$primary',
})

const TempStepper = styled('div', {
  display: 'flex',
  gap: '3px',
})

const StepBtn = styled('button', {
  size: '28px',
  borderRadius: '$sm',
  border: '1px solid $border',
  background: '$bgBase',
  fontSize: '$sm',
  fontWeight: '$bold',
  cursor: 'pointer',

  '&:hover:not(:disabled)': {
    background: '$primaryLight',
    color: '$primary',
    borderColor: '$primary',
  },
})

const ModeButtonGroup = styled('div', {
  display: 'flex',
  gap: '3px',
})

const ModeBtn = styled('button', {
  padding: '2px 6px',
  borderRadius: '$sm',
  border: '1px solid $border',
  background: '$bgCard',
  fontSize: '10px',
  fontWeight: '$semibold',
  cursor: 'pointer',

  variants: {
    active: {
      true: {
        background: '$primaryLight',
        borderColor: '$primary',
        color: '$primary',
      },
    },
  },
})

const FacilityRow = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  gap: '$2',
})

const DoorControlButton = styled('button', {
  padding: '$2 $3',
  borderRadius: '$md',
  border: 'none',
  fontSize: '$xs',
  fontWeight: '$bold',
  cursor: 'pointer',
  transition: 'all $fast',

  variants: {
    locked: {
      true: {
        background: '#10B981',
        color: '#FFFFFF',
        '&:hover': { background: '#059669' },
      },
      false: {
        background: '#EF4444',
        color: '#FFFFFF',
        '&:hover': { background: '#DC2626' },
      },
    },
  },
})
