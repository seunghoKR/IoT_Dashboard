/**
 * 스마트 빌딩 메인 대시보드 페이지
 * 좌측: 3층 스마트 빌딩 일러스트레이션
 * 우측: 마스터 제어 + 층별 IoT 스위치/장치 패널
 */

import React from 'react'
import { styled } from '../lib/stitches.config'
import { useBuildingStore } from '../stores/building.store'
import { BuildingIllustration } from '../components/building/BuildingIllustration'
import { BuildingMasterControl } from '../components/building/BuildingMasterControl'
import { FloorControlPanel } from '../components/building/FloorControlPanel'

export function BuildingDashboardPage() {
  const { buildingName } = useBuildingStore()

  return (
    <PageWrapper>
      {/* 상단 빌딩 타이틀 & 헤더 */}
      <HeaderSection>
        <div>
          <BuildingTitle>🏢 {buildingName}</BuildingTitle>
          <BuildingSubtitle>3층 복합 건축물 실시간 IoT 통합 제어 센터</BuildingSubtitle>
        </div>
      </HeaderSection>

      {/* 대시보드 메인 2컬럼 뷰 */}
      <MainGrid>
        {/* 좌측: 건물 단면 일러스트레이션 */}
        <LeftCol>
          <BuildingIllustration />
        </LeftCol>

        {/* 우측: 마스터 제어 바 및 층별 스위치 제어 */}
        <RightCol>
          <BuildingMasterControl />
          <FloorControlPanel />
        </RightCol>
      </MainGrid>
    </PageWrapper>
  )
}

const PageWrapper = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$5',
  padding: '$6',
  minHeight: '100vh',

  '@tablet': { padding: '$4' },
  '@mobile': { padding: '$3' },
})

const HeaderSection = styled('div', {
  display: 'flex',
  justifyContent: 'space-between',
  alignItems: 'flex-end',
})

const BuildingTitle = styled('h1', {
  fontSize: '$2xl',
  fontWeight: '$bold',
  color: '$textPrimary',
  letterSpacing: '$tight',
})

const BuildingSubtitle = styled('p', {
  fontSize: '$sm',
  color: '$textSecondary',
  marginTop: '2px',
})

const MainGrid = styled('div', {
  display: 'grid',
  gridTemplateColumns: '480px 1fr',
  gap: '$6',
  alignItems: 'start',

  '@desktop': {
    gridTemplateColumns: '440px 1fr',
  },
  '@tablet': {
    gridTemplateColumns: '1fr',
  },
})

const LeftCol = styled('div', {
  position: 'sticky',
  top: '$4',

  '@tablet': {
    position: 'static',
  },
})

const RightCol = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$5',
  minWidth: 0,
})
