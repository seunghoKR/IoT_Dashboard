/**
 * 메인 대시보드 페이지 - 5동 전체 한눈뷰 + Tuya Smart Plug 시각화 카드리스트
 */

import { useSmartFarmStore } from '../stores/smartfarm.store'
import { styled } from '../lib/stitches.config'
import { GreenhouseCard } from '../components/dashboard/GreenhouseCard'
import { WeatherBar } from '../components/dashboard/WeatherBar'
import { EssMiniBar } from '../components/dashboard/EssMiniBar'
import { AlertBanner } from '../components/dashboard/AlertBanner'
import { SmartPlugCard } from '../components/dashboard/SmartPlugCard'

export function DashboardPage() {
  const { farmName, greenhouses, outdoorWeather, ess, alerts } = useSmartFarmStore()

  const criticalAlerts = alerts.filter((a) => a.level === 'critical' && !a.read)

  return (
    <PageWrapper>
      {/* 긴급 경보 배너 */}
      {criticalAlerts.length > 0 && (
        <AlertBanner alerts={criticalAlerts} />
      )}

      {/* 외부 기상 + ESS 상태 바 */}
      <TopBar>
        <WeatherBar weather={outdoorWeather} />
        <EssMiniBar ess={ess} />
      </TopBar>

      {/* 🔌 Tuya 스마트 플러그 시각화 카드 위젯 */}
      <SmartPlugCard
        deviceId="ebb219afdebea03ba3shlz"
        deviceName="Smart Plug (실제 Tuya 수경재배/양수기 플러그)"
      />

      {/* 페이지 헤더 */}
      <PageHeader>
        <FarmTitle>{farmName}</FarmTitle>
        <FarmSubtitle>비닐하우스 5동 실시간 현황</FarmSubtitle>
      </PageHeader>

      {/* 5동 카드 그리드 */}
      <GreenhouseGrid>
        {Object.values(greenhouses).map((house) => (
          <GreenhouseCard key={house.houseId} greenhouse={house} />
        ))}
      </GreenhouseGrid>
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

const TopBar = styled('div', {
  display: 'flex',
  gap: '$4',
  alignItems: 'stretch',

  '@tablet': { flexDirection: 'column' },
})

const PageHeader = styled('div', {
  display: 'flex',
  flexDirection: 'column',
  gap: '$1',
})

const FarmTitle = styled('h1', {
  fontSize: '$2xl',
  fontWeight: '$bold',
  color: '$textPrimary',
  letterSpacing: '$tight',
})

const FarmSubtitle = styled('p', {
  fontSize: '$md',
  color: '$textSecondary',
})

const GreenhouseGrid = styled('div', {
  display: 'grid',
  gridTemplateColumns: 'repeat(3, 1fr)',
  gap: '$4',

  '@desktop': { gridTemplateColumns: 'repeat(3, 1fr)' },
  '@tablet': { gridTemplateColumns: 'repeat(2, 1fr)' },
  '@mobile': { gridTemplateColumns: '1fr' },
})
