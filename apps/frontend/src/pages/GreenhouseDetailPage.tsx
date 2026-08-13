/**
 * 동 상세 페이지 - 실시간 차트 + 액추에이터 제어 + CCTV
 */

import { useParams, useNavigate } from 'react-router-dom'
import { useQuery } from '@tanstack/react-query'
import {
  LineChart, Line, XAxis, YAxis, CartesianGrid, Tooltip,
  ResponsiveContainer, Legend
} from 'recharts'
import { styled } from '../lib/stitches.config'
import { useSmartFarmStore } from '../stores/smartfarm.store'
import { ActuatorControlPanel } from '../components/greenhouse/ActuatorControlPanel'

export function GreenhouseDetailPage() {
  const { houseId } = useParams<{ houseId: string }>()
  const navigate = useNavigate()
  const { greenhouses, farmId } = useSmartFarmStore()

  const house = houseId ? greenhouses[houseId] : null

  // 24시간 온도 이력 조회
  const { data: tempHistory } = useQuery({
    queryKey: ['history', farmId, houseId, 'air_temp'],
    queryFn: async () => {
      const res = await fetch(
        `/api/farms/${farmId}/greenhouses/${houseId}/history?field=air_temp&range=-24h`
      )
      return res.json()
    },
    refetchInterval: 60000, // 1분마다 갱신
    enabled: !!houseId,
  })

  if (!house) return <div>하우스를 찾을 수 없습니다.</div>

  return (
    <PageWrapper>
      {/* 헤더 */}
      <PageHeader>
        <BackButton onClick={() => navigate('/dashboard')}>← 대시보드</BackButton>
        <HouseTitle>
          {house.name}
          <OnlineBadge online={house.isOnline}>
            {house.isOnline ? '온라인' : '오프라인'}
          </OnlineBadge>
        </HouseTitle>
        <HeaderActions>
          <CCTVButton onClick={() => navigate(`/greenhouses/${houseId}/cctv`)}>
            📹 CCTV 보기
          </CCTVButton>
          <EmergencyButton>
            🚨 긴급 닫기
          </EmergencyButton>
        </HeaderActions>
      </PageHeader>

      <ContentGrid>
        {/* 현재 센서값 카드 */}
        <SensorSection>
          <SectionTitle>실시간 센서</SectionTitle>
          {house.sensors && (
            <SensorCards>
              <SensorCard>
                <CardLabel>🌡 온도</CardLabel>
                <CardValue>{house.sensors.air_temp.toFixed(1)}°C</CardValue>
                <CardRange>적정: 22~28°C</CardRange>
              </SensorCard>
              <SensorCard>
                <CardLabel>💧 습도</CardLabel>
                <CardValue>{house.sensors.air_humidity.toFixed(0)}%</CardValue>
                <CardRange>적정: 60~80%</CardRange>
              </SensorCard>
              <SensorCard>
                <CardLabel>🌱 CO₂</CardLabel>
                <CardValue>{house.sensors.co2_ppm}ppm</CardValue>
                <CardRange>적정: 800~1200ppm</CardRange>
              </SensorCard>
              <SensorCard>
                <CardLabel>🪴 토양수분</CardLabel>
                <CardValue>{house.sensors.soil_moisture.toFixed(0)}%</CardValue>
                <CardRange>적정: 50~75%</CardRange>
              </SensorCard>
            </SensorCards>
          )}
        </SensorSection>

        {/* 액추에이터 제어 패널 */}
        <ControlSection>
          <SectionTitle>장치 제어</SectionTitle>
          {houseId && <ActuatorControlPanel farmId={farmId} houseId={houseId} actuators={house.actuators} />}
        </ControlSection>

        {/* 실시간 차트 */}
        <ChartSection>
          <SectionTitle>24시간 온도 추이</SectionTitle>
          <ChartWrapper>
            <ResponsiveContainer width="100%" height={250}>
              <LineChart data={tempHistory?.data ?? []}>
                <CartesianGrid strokeDasharray="3 3" stroke="var(--colors-border)" />
                <XAxis
                  dataKey="time"
                  tickFormatter={(v) => new Date(v).toLocaleTimeString('ko-KR', { hour: '2-digit', minute: '2-digit' })}
                  tick={{ fontSize: 11 }}
                />
                <YAxis
                  domain={['auto', 'auto']}
                  tickFormatter={(v) => `${v}°C`}
                  tick={{ fontSize: 11 }}
                />
                <Tooltip
                  labelFormatter={(v) => new Date(v).toLocaleString('ko-KR')}
                  formatter={(v: number) => [`${v.toFixed(1)}°C`, '온도']}
                />
                <Legend />
                <Line
                  type="monotone"
                  dataKey="value"
                  stroke="hsl(140, 50%, 38%)"
                  strokeWidth={2}
                  dot={false}
                  name="온도 (°C)"
                />
              </LineChart>
            </ResponsiveContainer>
          </ChartWrapper>
        </ChartSection>
      </ContentGrid>
    </PageWrapper>
  )
}

// Styled Components
const PageWrapper = styled('div', { padding: '$6', '@tablet': { padding: '$4' } })
const PageHeader = styled('div', {
  flexBetween: true,
  marginBottom: '$6',
  flexWrap: 'wrap',
  gap: '$3',
})
const BackButton = styled('button', {
  background: 'none',
  border: 'none',
  color: '$primary',
  cursor: 'pointer',
  fontSize: '$md',
  fontWeight: '$medium',
  padding: '$2 0',
  '&:hover': { color: '$primaryHover' },
})
const HouseTitle = styled('h1', {
  fontSize: '$2xl',
  fontWeight: '$bold',
  color: '$textPrimary',
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
})
const OnlineBadge = styled('span', {
  fontSize: '$sm',
  fontWeight: '$medium',
  padding: '$1 $3',
  borderRadius: '$full',
  variants: {
    online: {
      true: { background: '$successLight', color: '$success' },
      false: { background: '$bgMuted', color: '$textMuted' },
    },
  },
})
const HeaderActions = styled('div', { display: 'flex', gap: '$3' })
const CCTVButton = styled('button', {
  padding: '$2 $4',
  borderRadius: '$md',
  border: '1px solid $border',
  background: '$bgCard',
  color: '$textPrimary',
  cursor: 'pointer',
  fontSize: '$sm',
  fontWeight: '$medium',
  '&:hover': { borderColor: '$primary', color: '$primary' },
})
const EmergencyButton = styled('button', {
  padding: '$2 $4',
  borderRadius: '$md',
  border: 'none',
  background: '$danger',
  color: '$textInverse',
  cursor: 'pointer',
  fontSize: '$sm',
  fontWeight: '$semibold',
  '&:hover': { background: 'hsl(0, 72%, 42%)' },
})
const ContentGrid = styled('div', {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gridTemplateRows: 'auto auto',
  gap: '$5',
  '@tablet': { gridTemplateColumns: '1fr' },
})
const SectionTitle = styled('h2', {
  fontSize: '$lg',
  fontWeight: '$semibold',
  color: '$textPrimary',
  marginBottom: '$4',
})
const SensorSection = styled('section', {})
const ControlSection = styled('section', {})
const ChartSection = styled('section', { gridColumn: '1 / -1' })
const SensorCards = styled('div', {
  display: 'grid',
  gridTemplateColumns: 'repeat(2, 1fr)',
  gap: '$3',
})
const SensorCard = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$4',
  boxShadow: '$sm',
})
const CardLabel = styled('div', { fontSize: '$sm', color: '$textMuted', marginBottom: '$2' })
const CardValue = styled('div', {
  fontSize: '$2xl',
  fontWeight: '$bold',
  color: '$textPrimary',
  letterSpacing: '$tight',
})
const CardRange = styled('div', { fontSize: '$xs', color: '$textMuted', marginTop: '$1' })
const ChartWrapper = styled('div', {
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$4',
  boxShadow: '$sm',
})
