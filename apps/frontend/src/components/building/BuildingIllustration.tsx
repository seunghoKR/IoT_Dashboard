/**
 * 3층 스마트 빌딩 인터랙티브 일러스트레이션 컴포넌트
 */

import React from 'react'
import { styled, keyframes } from '../../lib/stitches.config'
import { useBuildingStore } from '../../stores/building.store'

const fanRotate = keyframes({
  '0%': { transform: 'rotate(0deg)' },
  '100%': { transform: 'rotate(360deg)' },
})

const pulseGlow = keyframes({
  '0%': { opacity: 0.7, filter: 'drop-shadow(0 0 4px rgba(251, 191, 36, 0.4))' },
  '100%': { opacity: 1, filter: 'drop-shadow(0 0 14px rgba(251, 191, 36, 0.9))' },
})

const coolAir = keyframes({
  '0%': { transform: 'translateY(-2px)', opacity: 0.3 },
  '50%': { transform: 'translateY(4px)', opacity: 0.8 },
  '100%': { transform: 'translateY(10px)', opacity: 0 },
})

export function BuildingIllustration() {
  const {
    activeFloor,
    setActiveFloor,
    floor1,
    floor2,
    floor3,
  } = useBuildingStore()

  // 층별 조명 켜진 개수 계산
  const f3PastorOn = floor3.pastorRoomLights.filter(Boolean).length
  const f3MeetingOn = floor3.meetingRoomLights.filter(Boolean).length
  const f2SanctuaryOn = floor2.sanctuaryLights.filter(Boolean).length
  const f1LobbyOn = floor1.lobbyLights.filter(Boolean).length

  return (
    <IllustrationCard>
      <CardHeader>
        <div>
          <CardTitle>🏛️ 건물 실시간 모니터링 뷰</CardTitle>
          <CardSub>층을 클릭하면 해당 층으로 이동 및 상태를 확인합니다</CardSub>
        </div>
        <FloorBadges>
          <FloorBadge
            active={activeFloor === 'all'}
            onClick={() => setActiveFloor('all')}
          >
            전체 층
          </FloorBadge>
          <FloorBadge
            active={activeFloor === 3}
            onClick={() => setActiveFloor(3)}
          >
            3F
          </FloorBadge>
          <FloorBadge
            active={activeFloor === 2}
            onClick={() => setActiveFloor(2)}
          >
            2F
          </FloorBadge>
          <FloorBadge
            active={activeFloor === 1}
            onClick={() => setActiveFloor(1)}
          >
            1F
          </FloorBadge>
        </FloorBadges>
      </CardHeader>

      <BuildingStage>
        {/* 하늘 및 배경 장식 */}
        <SkyBackground />

        {/* 3층 건물 본체 */}
        <BuildingStructure>
          {/* 루프탑 / 옥상 */}
          <RooftopArea>
            <RoofSolarPanels>
              <SolarPanel />
              <SolarPanel />
              <SolarPanel />
            </RoofSolarPanels>
            <RoofHvacUnit>
              <FanBlade spinning={floor2.hvac.power || floor1.ac.power} />
            </RoofHvacUnit>
            <RoofSign>VISION CENTER</RoofSign>
          </RooftopArea>

          {/* 3층 : 목양실 & 미팅룸 */}
          <FloorContainer
            isFocused={activeFloor === 3 || activeFloor === 'all'}
            onClick={() => setActiveFloor(3)}
          >
            <FloorLabelTag>3F 목양실 / 미팅룸</FloorLabelTag>
            <FloorGrid cols={2}>
              {/* 목양실 */}
              <RoomUnit isLit={f3PastorOn > 0}>
                <RoomHeader>
                  <RoomName>목양실 (Pastor)</RoomName>
                  <LightStatusBadge active={f3PastorOn > 0}>
                    💡 {f3PastorOn}/2구
                  </LightStatusBadge>
                </RoomHeader>
                <RoomInterior>
                  <DeskGraphic />
                  <BookshelfGraphic />
                  {f3PastorOn > 0 && <LightChandelier on={true} count={f3PastorOn} />}
                </RoomInterior>
              </RoomUnit>

              {/* 미팅룸 */}
              <RoomUnit isLit={f3MeetingOn > 0}>
                <RoomHeader>
                  <RoomName>미팅룸 (Meeting)</RoomName>
                  <LightStatusBadge active={f3MeetingOn > 0}>
                    💡 {f3MeetingOn}/2구
                  </LightStatusBadge>
                </RoomHeader>
                <RoomInterior>
                  <MeetingTableGraphic />
                  <WhiteboardGraphic />
                  {f3MeetingOn > 0 && <LightChandelier on={true} count={f3MeetingOn} />}
                </RoomInterior>
              </RoomUnit>
            </FloorGrid>
          </FloorContainer>

          {/* 2층 : 대예배실 */}
          <FloorContainer
            isFocused={activeFloor === 2 || activeFloor === 'all'}
            onClick={() => setActiveFloor(2)}
          >
            <FloorLabelTag>2F 대예배실 (Sanctuary)</FloorLabelTag>
            <RoomUnit isLit={f2SanctuaryOn > 0} isLarge>
              <RoomHeader>
                <RoomName>대예배실 홀</RoomName>
                <HeaderRightGroup>
                  <HvacBadge active={floor2.hvac.power}>
                    {floor2.hvac.power ? `❄️ ${floor2.hvac.targetTemp}°C` : 'HVAC OFF'}
                  </HvacBadge>
                  <LightStatusBadge active={f2SanctuaryOn > 0}>
                    💡 {f2SanctuaryOn}/6구
                  </LightStatusBadge>
                </HeaderRightGroup>
              </RoomHeader>
              <RoomInterior sanctuary>
                <AltarGraphic />
                <PewRowsGraphic />
                {floor2.hvac.power && <AirFlowAnimation />}
                {f2SanctuaryOn > 0 && (
                  <SanctuaryLightsRow>
                    {Array.from({ length: 6 }).map((_, i) => (
                      <CeilingLightBulb key={i} on={floor2.sanctuaryLights[i]} />
                    ))}
                  </SanctuaryLightsRow>
                )}
              </RoomInterior>
            </RoomUnit>
          </FloorContainer>

          {/* 1층 : 로비, 남녀 화장실, 메인 출입문 */}
          <FloorContainer
            isFocused={activeFloor === 1 || activeFloor === 'all'}
            onClick={() => setActiveFloor(1)}
          >
            <FloorLabelTag>1F 메인 로비 & 화장실 & 현관</FloorLabelTag>
            <FloorGrid cols={3}>
              {/* 로비 */}
              <RoomUnit isLit={f1LobbyOn > 0} style={{ gridColumn: 'span 2' }}>
                <RoomHeader>
                  <RoomName>메인 로비 (Lobby)</RoomName>
                  <HeaderRightGroup>
                    <HvacBadge active={floor1.ac.power}>
                      {floor1.ac.power ? `❄️ ${floor1.ac.targetTemp}°C` : '에어컨 OFF'}
                    </HvacBadge>
                    <LightStatusBadge active={f1LobbyOn > 0}>
                      💡 {f1LobbyOn}/6구
                    </LightStatusBadge>
                  </HeaderRightGroup>
                </RoomHeader>
                <RoomInterior>
                  <ReceptionGraphic />
                  <SofaGraphic />
                  {f1LobbyOn > 0 && (
                    <LobbyLightsGrid>
                      {Array.from({ length: 6 }).map((_, i) => (
                        <CeilingLightBulb key={i} on={floor1.lobbyLights[i]} small />
                      ))}
                    </LobbyLightsGrid>
                  )}
                </RoomInterior>
              </RoomUnit>

              {/* 화장실 & 보안 출입문 */}
              <RightServiceArea>
                {/* 화장실 섹션 */}
                <MiniRestroomRow>
                  <RestroomCard isLit={floor1.menRestroom.light}>
                    <span>🚹 남</span>
                    <MiniIcons>
                      <MiniLight on={floor1.menRestroom.light}>💡</MiniLight>
                      <MiniFan on={floor1.menRestroom.fan}>🌀</MiniFan>
                    </MiniIcons>
                  </RestroomCard>
                  <RestroomCard isLit={floor1.womenRestroom.light}>
                    <span>🚺 여</span>
                    <MiniIcons>
                      <MiniLight on={floor1.womenRestroom.light}>💡</MiniLight>
                      <MiniFan on={floor1.womenRestroom.fan}>🌀</MiniFan>
                    </MiniIcons>
                  </RestroomCard>
                </MiniRestroomRow>

                {/* 메인 도어락 */}
                <DoorLockCard locked={floor1.doorLock.locked}>
                  <span>🚪 메인 게이트</span>
                  <strong>{floor1.doorLock.locked ? '🔒 잠김' : '🔓 열림'}</strong>
                </DoorLockCard>
              </RightServiceArea>
            </FloorGrid>
          </FloorContainer>

          {/* 지상 조경 및 잔디 */}
          <GroundLandscaping>
            <TreeGraphic />
            <GrassField />
            <EntranceWalkway />
            <TreeGraphic />
          </GroundLandscaping>
        </BuildingStructure>
      </BuildingStage>
    </IllustrationCard>
  )
}

// Styled Components
const IllustrationCard = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$lg',
  padding: '$5',
  display: 'flex',
  flexDirection: 'column',
  gap: '$4',
  boxShadow: '$sm',
  position: 'relative',
  overflow: 'hidden',
})

const CardHeader = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  flexWrap: 'wrap',
  gap: '$3',
})

const CardTitle = styled('h2', {
  fontSize: '$lg',
  fontWeight: '$bold',
  color: '$textPrimary',
  display: 'flex',
  alignItems: 'center',
  gap: '$2',
})

const CardSub = styled('p', {
  fontSize: '$xs',
  color: '$textMuted',
  marginTop: '2px',
})

const FloorBadges = styled('div', {
  display: 'flex',
  gap: '$1',
  background: '$bgMuted',
  padding: '3px',
  borderRadius: '$md',
})

const FloorBadge = styled('button', {
  border: 'none',
  background: 'transparent',
  padding: '$1 $3',
  fontSize: '$xs',
  fontWeight: '$semibold',
  borderRadius: '$sm',
  cursor: 'pointer',
  color: '$textSecondary',
  transition: 'all $fast',

  '&:hover': {
    color: '$textPrimary',
  },

  variants: {
    active: {
      true: {
        background: '$bgCard',
        color: '$primary',
        boxShadow: '$xs',
      },
    },
  },
})

const BuildingStage = styled('div', {
  position: 'relative',
  background: 'linear-gradient(180deg, #E0F2FE 0%, #BAE6FD 50%, #E2E8F0 100%)',
  borderRadius: '$md',
  padding: '$6 $5 $4',
  display: 'flex',
  flexDirection: 'column',
  alignItems: 'center',
  minHeight: '520px',
  border: '1px solid rgba(0,0,0,0.06)',
  overflow: 'hidden',
})

const SkyBackground = styled('div', {
  position: 'absolute',
  top: 0,
  left: 0,
  right: 0,
  height: '100%',
  pointerEvents: 'none',
  background: 'radial-gradient(circle at 80% 20%, rgba(254, 240, 138, 0.4) 0%, transparent 40%)',
})

const BuildingStructure = styled('div', {
  position: 'relative',
  width: '100%',
  maxWidth: '560px',
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
  zIndex: 1,
})

const RooftopArea = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-end',
  padding: '0 $4',
  height: '42px',
  marginBottom: '-2px',
})

const RoofSolarPanels = styled('div', {
  display: 'flex',
  gap: '4px',
})

const SolarPanel = styled('div', {
  width: '28px',
  height: '14px',
  background: 'linear-gradient(135deg, #1E293B, #3B82F6)',
  border: '1px solid #94A3B8',
  borderRadius: '2px',
  transform: 'skewX(-15deg)',
})

const RoofHvacUnit = styled('div', {
  width: '32px',
  height: '22px',
  background: '#CBD5E1',
  border: '1px solid #94A3B8',
  borderRadius: '4px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
})

const FanBlade = styled('div', {
  width: '12px',
  height: '12px',
  border: '2px solid #475569',
  borderTopColor: 'transparent',
  borderRadius: '50%',

  variants: {
    spinning: {
      true: {
        animation: `${fanRotate} 0.6s linear infinite`,
      },
    },
  },
})

const RoofSign = styled('div', {
  background: '#1E293B',
  color: '#F8FAFC',
  fontSize: '9px',
  fontWeight: '800',
  letterSpacing: '1px',
  padding: '3px 8px',
  borderRadius: '3px',
  border: '1px solid #475569',
})

const FloorContainer = styled('div', {
  background: 'rgba(255, 255, 255, 0.92)',
  backdropFilter: 'blur(8px)',
  border: '2px solid #E2E8F0',
  borderRadius: '$md',
  padding: '$3',
  position: 'relative',
  transition: 'all 0.25s ease',
  cursor: 'pointer',
  boxShadow: '0 4px 12px rgba(0,0,0,0.04)',

  '&:hover': {
    borderColor: '$primary',
    transform: 'translateY(-2px)',
    boxShadow: '0 8px 20px rgba(0,0,0,0.08)',
  },

  variants: {
    isFocused: {
      true: {
        opacity: 1,
      },
      false: {
        opacity: 0.45,
        filter: 'grayscale(40%)',
      },
    },
  },
})

const FloorLabelTag = styled('div', {
  position: 'absolute',
  top: '-10px',
  left: '$3',
  background: '#334155',
  color: '#FFFFFF',
  fontSize: '10px',
  fontWeight: '$bold',
  padding: '1px 8px',
  borderRadius: '$full',
  letterSpacing: '0.5px',
})

const FloorGrid = styled('div', {
  display: 'grid',
  gap: '$2',
  marginTop: '$1',

  variants: {
    cols: {
      2: { gridTemplateColumns: '1fr 1fr' },
      3: { gridTemplateColumns: '1.8fr 1.2fr' },
    },
  },
})

const RoomUnit = styled('div', {
  background: '#F8FAFC',
  border: '1.5px solid #CBD5E1',
  borderRadius: '$sm',
  padding: '$2 $3',
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
  minHeight: '85px',
  position: 'relative',
  transition: 'all 0.3s ease',

  variants: {
    isLit: {
      true: {
        background: 'linear-gradient(180deg, #FEF9C3 0%, #FEF08A 100%)',
        borderColor: '#FACC15',
        boxShadow: 'inset 0 0 15px rgba(250, 204, 21, 0.4)',
      },
    },
    isLarge: {
      true: {
        minHeight: '105px',
      },
    },
  },
})

const RoomHeader = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
})

const RoomName = styled('span', {
  fontSize: '11px',
  fontWeight: '$bold',
  color: '$textPrimary',
})

const HeaderRightGroup = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$2',
})

const LightStatusBadge = styled('span', {
  fontSize: '10px',
  fontWeight: '$semibold',
  padding: '1px 6px',
  borderRadius: '$full',
  background: '#E2E8F0',
  color: '#64748B',

  variants: {
    active: {
      true: {
        background: '#FEF08A',
        color: '#854D0E',
        border: '1px solid #FACC15',
      },
    },
  },
})

const HvacBadge = styled('span', {
  fontSize: '10px',
  fontWeight: '$semibold',
  padding: '1px 6px',
  borderRadius: '$full',
  background: '#E0F2FE',
  color: '#0369A1',

  variants: {
    active: {
      true: {
        background: '#BAE6FD',
        color: '#0284C7',
      },
    },
  },
})

const RoomInterior = styled('div', {
  flex: 1,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  position: 'relative',
  minHeight: '40px',

  variants: {
    sanctuary: {
      true: {
        minHeight: '60px',
      },
    },
  },
})

const DeskGraphic = styled('div', {
  width: '36px',
  height: '14px',
  background: '#94A3B8',
  borderRadius: '2px',
  position: 'absolute',
  bottom: '4px',
  left: '10px',
})

const BookshelfGraphic = styled('div', {
  width: '18px',
  height: '32px',
  background: '#64748B',
  borderRadius: '2px',
  position: 'absolute',
  bottom: '4px',
  right: '10px',
})

const MeetingTableGraphic = styled('div', {
  width: '50px',
  height: '18px',
  background: '#94A3B8',
  borderRadius: '8px',
  position: 'absolute',
  bottom: '6px',
})

const WhiteboardGraphic = styled('div', {
  width: '28px',
  height: '16px',
  background: '#FFFFFF',
  border: '1px solid #94A3B8',
  borderRadius: '2px',
  position: 'absolute',
  top: '2px',
  right: '8px',
})

const AltarGraphic = styled('div', {
  width: '60px',
  height: '20px',
  background: '#B45309',
  borderRadius: '3px',
  position: 'absolute',
  bottom: '8px',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  color: '#FFFFFF',
  fontSize: '10px',
  fontWeight: '$bold',
  boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
  '&::after': {
    content: '✝️',
    fontSize: '10px',
  },
})

const PewRowsGraphic = styled('div', {
  width: '100%',
  height: '8px',
  position: 'absolute',
  bottom: '2px',
  display: 'flex',
  justifyContent: 'space-around',
  padding: '0 20px',
  opacity: 0.6,
  '&::before, &::after': {
    content: '""',
    width: '35%',
    height: '4px',
    background: '#78350F',
    borderRadius: '2px',
  },
})

const SanctuaryLightsRow = styled('div', {
  position: 'absolute',
  top: '4px',
  width: '80%',
  display: 'flex',
  justifyContent: 'space-between',
})

const CeilingLightBulb = styled('div', {
  width: '10px',
  height: '10px',
  borderRadius: '50%',
  background: '#CBD5E1',

  variants: {
    on: {
      true: {
        background: '#FACC15',
        boxShadow: '0 0 10px #F59E0B',
        animation: `${pulseGlow} 1.5s infinite alternate`,
      },
    },
    small: {
      true: {
        width: '8px',
        height: '8px',
      },
    },
  },
})

const LightChandelier = styled('div', {
  position: 'absolute',
  top: '4px',
  width: '14px',
  height: '14px',
  borderRadius: '50%',
  background: '#FACC15',
  boxShadow: '0 0 12px #F59E0B',
  animation: `${pulseGlow} 1.5s infinite alternate`,
})

const AirFlowAnimation = styled('div', {
  position: 'absolute',
  top: '12px',
  right: '24px',
  width: '2px',
  height: '12px',
  background: 'rgba(56, 189, 248, 0.8)',
  boxShadow: '6px 0 rgba(56, 189, 248, 0.8), -6px 0 rgba(56, 189, 248, 0.8)',
  animation: `${coolAir} 1s infinite ease-in-out`,
})

const ReceptionGraphic = styled('div', {
  width: '45px',
  height: '18px',
  background: '#0F766E',
  borderRadius: '3px',
  position: 'absolute',
  bottom: '4px',
  left: '12px',
})

const SofaGraphic = styled('div', {
  width: '32px',
  height: '14px',
  background: '#0284C7',
  borderRadius: '4px',
  position: 'absolute',
  bottom: '6px',
  right: '18px',
})

const LobbyLightsGrid = styled('div', {
  position: 'absolute',
  top: '4px',
  width: '70%',
  display: 'flex',
  justifyContent: 'space-around',
})

const RightServiceArea = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
})

const MiniRestroomRow = styled('div', {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '$1',
})

const RestroomCard = styled('div', {
  background: '#F1F5F9',
  border: '1px solid #CBD5E1',
  borderRadius: '$sm',
  padding: '$1 $2',
  fontSize: '10px',
  fontWeight: '$bold',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',

  variants: {
    isLit: {
      true: {
        background: '#FEF9C3',
        borderColor: '#FACC15',
      },
    },
  },
})

const MiniIcons = styled('div', {
  display: 'flex',
  gap: '2px',
  fontSize: '9px',
})

const MiniLight = styled('span', {
  opacity: 0.3,
  variants: { on: { true: { opacity: 1 } } },
})

const MiniFan = styled('span', {
  opacity: 0.3,
  display: 'inline-block',
  variants: {
    on: {
      true: {
        opacity: 1,
        animation: `${fanRotate} 0.5s linear infinite`,
      },
    },
  },
})

const DoorLockCard = styled('div', {
  background: '#F8FAFC',
  border: '1px solid #CBD5E1',
  borderRadius: '$sm',
  padding: '$1 $2',
  fontSize: '10px',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',

  variants: {
    locked: {
      true: {
        color: '$success',
      },
      false: {
        color: '$danger',
        background: '#FEF2F2',
        borderColor: '#FCA5A5',
      },
    },
  },
})

const GroundLandscaping = styled('div', {
  display: 'flex',
  alignItems: 'flex-end',
  justifyContent: 'space-between',
  padding: '0 $4',
  height: '24px',
  marginTop: '$1',
})

const TreeGraphic = styled('div', {
  width: '22px',
  height: '22px',
  borderRadius: '50%',
  background: 'linear-gradient(180deg, #22C55E 0%, #15803D 100%)',
  boxShadow: '0 2px 4px rgba(0,0,0,0.1)',
  '&::after': {
    content: '""',
    display: 'block',
    width: '4px',
    height: '6px',
    background: '#78350F',
    margin: '20px auto 0',
  },
})

const GrassField = styled('div', {
  flex: 1,
  height: '6px',
  background: '#4ADE80',
  borderRadius: '$full',
  margin: '0 $2',
})

const EntranceWalkway = styled('div', {
  width: '40px',
  height: '10px',
  background: '#CBD5E1',
  borderRadius: '2px',
})
