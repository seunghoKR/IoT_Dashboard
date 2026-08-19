/**
 * 스마트 빌딩 상단 마스터 컨트롤 바
 * - 건물 전체 전등 끄기 (Master All-Off)
 * - 층별 일괄 소등
 * - 실시간 소비전력 및 보안 상태
 */

import React from 'react'
import { styled } from '../../lib/stitches.config'
import { useBuildingStore } from '../../stores/building.store'

export function BuildingMasterControl() {
  const {
    totalPowerWatts,
    monthlyKwh,
    outdoorTemp,
    securityArmed,
    turnOffAllBuildingLights,
    turnOffAllBuildingDevices,
    turnOffFloor1,
    turnOffFloor2,
    turnOffFloor3,
    floor1,
    floor2,
    floor3,
  } = useBuildingStore()

  // 전체 조명 켜진 개수 집계
  const totalLightsOn =
    floor1.lobbyLights.filter(Boolean).length +
    (floor1.menRestroom.light ? 1 : 0) +
    (floor1.womenRestroom.light ? 1 : 0) +
    floor2.sanctuaryLights.filter(Boolean).length +
    floor3.pastorRoomLights.filter(Boolean).length +
    floor3.meetingRoomLights.filter(Boolean).length

  return (
    <MasterWrapper>
      {/* 통계 및 상태 지표 요약 */}
      <StatSummaryGrid>
        <StatCard>
          <StatIcon>⚡</StatIcon>
          <div>
            <StatLabel>실시간 전력</StatLabel>
            <StatValue>
              {totalPowerWatts} <span>W</span>
            </StatValue>
          </div>
        </StatCard>

        <StatCard>
          <StatIcon>💡</StatIcon>
          <div>
            <StatLabel>점등 중인 조명</StatLabel>
            <StatValue highlight={totalLightsOn > 0}>
              {totalLightsOn} <span>/ 18구</span>
            </StatValue>
          </div>
        </StatCard>

        <StatCard>
          <StatIcon>📊</StatIcon>
          <div>
            <StatLabel>당월 누적 전력</StatLabel>
            <StatValue>
              {monthlyKwh} <span>kWh</span>
            </StatValue>
          </div>
        </StatCard>

        <StatCard>
          <StatIcon>🛡️</StatIcon>
          <div>
            <StatLabel>보안 / 외기온도</StatLabel>
            <StatValue>
              {securityArmed ? '경비 가동' : '해제'} <span>({outdoorTemp}°C)</span>
            </StatValue>
          </div>
        </StatCard>
      </StatSummaryGrid>

      {/* 마스터 원클릭 액션 버튼 그룹 */}
      <ActionToolbar>
        <ButtonGroupLeft>
          <MasterOffButton onClick={turnOffAllBuildingLights}>
            🚨 건물 전체 전등 끄기 (Master Off)
          </MasterOffButton>
          <SafeAllOffButton onClick={turnOffAllBuildingDevices}>
            🌙 전체 외출/퇴근 모드 (가전+전등 Off)
          </SafeAllOffButton>
        </ButtonGroupLeft>

        <ButtonGroupRight>
          <FloorOffBtn onClick={turnOffFloor3}>
            3F 전체 끄기
          </FloorOffBtn>
          <FloorOffBtn onClick={turnOffFloor2}>
            2F 전체 끄기
          </FloorOffBtn>
          <FloorOffBtn onClick={turnOffFloor1}>
            1F 전체 끄기
          </FloorOffBtn>
        </ButtonGroupRight>
      </ActionToolbar>
    </MasterWrapper>
  )
}

const MasterWrapper = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$3',
})

const StatSummaryGrid = styled('div', {
  display: 'grid',
  gridTemplateColumns: 'repeat(4, 1fr)',
  gap: '$3',

  '@tablet': { gridTemplateColumns: 'repeat(2, 1fr)' },
  '@mobile': { gridTemplateColumns: '1fr' },
})

const StatCard = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$3 $4',
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
  boxShadow: '$sm',
})

const StatIcon = styled('div', {
  fontSize: '24px',
  background: '$bgMuted',
  width: '42px',
  height: '42px',
  borderRadius: '$md',
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
})

const StatLabel = styled('div', {
  fontSize: '$xs',
  color: '$textMuted',
  fontWeight: '$medium',
})

const StatValue = styled('div', {
  fontSize: '$lg',
  fontWeight: '$bold',
  color: '$textPrimary',
  display: 'flex',
  alignItems: 'baseline',
  gap: '4px',

  '& span': {
    fontSize: '$xs',
    color: '$textSecondary',
    fontWeight: '$normal',
  },

  variants: {
    highlight: {
      true: {
        color: '#D97706',
      },
    },
  },
})

const ActionToolbar = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$3 $4',
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'center',
  flexWrap: 'wrap',
  gap: '$3',
})

const ButtonGroupLeft = styled('div', {
  display: 'flex',
  gap: '$2',
  flexWrap: 'wrap',
})

const ButtonGroupRight = styled('div', {
  display: 'flex',
  gap: '$2',
  flexWrap: 'wrap',
})

const MasterOffButton = styled('button', {
  background: 'linear-gradient(135deg, #EF4444 0%, #DC2626 100%)',
  color: '#FFFFFF',
  border: 'none',
  borderRadius: '$md',
  padding: '$2 $4',
  fontSize: '$sm',
  fontWeight: '$bold',
  cursor: 'pointer',
  boxShadow: '0 2px 8px rgba(239, 68, 68, 0.3)',
  transition: 'all $fast',

  '&:hover': {
    transform: 'translateY(-1px)',
    boxShadow: '0 4px 12px rgba(239, 68, 68, 0.45)',
  },
  '&:active': {
    transform: 'translateY(0)',
  },
})

const SafeAllOffButton = styled('button', {
  background: '$bgMuted',
  color: '$textPrimary',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$2 $3',
  fontSize: '$sm',
  fontWeight: '$semibold',
  cursor: 'pointer',
  transition: 'all $fast',

  '&:hover': {
    borderColor: '$primary',
    color: '$primary',
  },
})

const FloorOffBtn = styled('button', {
  background: '$bgBase',
  color: '$textSecondary',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$2 $3',
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
