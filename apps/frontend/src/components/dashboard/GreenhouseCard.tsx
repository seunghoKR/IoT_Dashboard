/**
 * 비닐하우스 카드 컴포넌트
 * 대시보드 메인에서 각 동의 현황을 보여주는 카드
 * Stitches Variant로 알림 레벨 (normal/warning/critical) 스타일 분기
 */

import { useNavigate } from 'react-router-dom'
import { styled, keyframes } from '../../lib/stitches.config'
import { GreenhouseState } from '../../stores/smartfarm.store'

const CROP_EMOJI: Record<string, string> = {
  cherry_tomato: '🍅',
  cucumber: '🥒',
  paprika: '🫑',
  lettuce: '🥬',
  strawberry: '🍓',
  default: '🌱',
}

interface GreenhouseCardProps {
  greenhouse: GreenhouseState
}

export function GreenhouseCard({ greenhouse }: GreenhouseCardProps) {
  const navigate = useNavigate()
  const { houseId, name, cropType, sensors, actuators, alertLevel, isOnline, lastUpdated } = greenhouse
  const emoji = CROP_EMOJI[cropType] ?? CROP_EMOJI.default

  return (
    <CardWrapper
      alertLevel={alertLevel}
      isOnline={isOnline}
      onClick={() => navigate(`/greenhouses/${houseId}`)}
    >
      {/* 카드 헤더 */}
      <CardHeader>
        <HouseTitle>
          <span>{emoji}</span>
          <span>{name}</span>
        </HouseTitle>
        <StatusDot isOnline={isOnline} alertLevel={alertLevel} />
      </CardHeader>

      {/* 센서 데이터 */}
      {sensors ? (
        <SensorGrid>
          <SensorItem>
            <SensorLabel>🌡 온도</SensorLabel>
            <SensorValue alert={sensors.air_temp > 33 ? (sensors.air_temp > 38 ? 'critical' : 'warning') : 'normal'}>
              {sensors.air_temp.toFixed(1)}°C
            </SensorValue>
          </SensorItem>

          <SensorItem>
            <SensorLabel>💧 습도</SensorLabel>
            <SensorValue alert={sensors.air_humidity < 40 ? 'warning' : 'normal'}>
              {sensors.air_humidity.toFixed(0)}%
            </SensorValue>
          </SensorItem>

          <SensorItem>
            <SensorLabel>🌱 CO₂</SensorLabel>
            <SensorValue alert={sensors.co2_ppm > 2000 ? 'warning' : 'normal'}>
              {sensors.co2_ppm}ppm
            </SensorValue>
          </SensorItem>

          <SensorItem>
            <SensorLabel>🪴 토양</SensorLabel>
            <SensorValue alert={sensors.soil_moisture < 30 ? 'warning' : 'normal'}>
              {sensors.soil_moisture.toFixed(0)}%
            </SensorValue>
          </SensorItem>
        </SensorGrid>
      ) : (
        <NoDataText>{isOnline ? '데이터 로딩 중...' : '오프라인'}</NoDataText>
      )}

      {/* 액추에이터 요약 */}
      {actuators && (
        <ActuatorBar>
          <ActuatorBadge active={actuators.sideFlapLeft.position > 0}>
            측창 {actuators.sideFlapLeft.position}%
          </ActuatorBadge>
          <ActuatorBadge active={actuators.doubleCover.position === 0}>
            보온 {actuators.doubleCover.position === 0 ? '닫힘' : `${actuators.doubleCover.position}%`}
          </ActuatorBadge>
          <ActuatorBadge active={actuators.waterPump.active}>
            {actuators.waterPump.active ? '💧 관수중' : '양수기 대기'}
          </ActuatorBadge>
          <ActuatorBadge active={actuators.ventFan.active}>
            환풍 {actuators.ventFan.speed > 0 ? `${actuators.ventFan.speed}단` : '꺼짐'}
          </ActuatorBadge>
        </ActuatorBar>
      )}

      {/* 마지막 업데이트 */}
      <LastUpdated>
        {lastUpdated ? `마지막 갱신: ${new Date(lastUpdated).toLocaleTimeString('ko-KR')}` : '—'}
      </LastUpdated>

      {/* 자세히 보기 링크 */}
      <DetailLink>상세 보기 →</DetailLink>
    </CardWrapper>
  )
}

// ── Styled Components (Stitches) ────────────────────────────────────────

const pulseAnimation = keyframes({
  '0%, 100%': { opacity: 1 },
  '50%': { opacity: 0.4 },
})

const CardWrapper = styled('div', {
  position: 'relative',
  background: '$bgCard',
  borderRadius: '$lg',
  padding: '$5',
  border: '1.5px solid $border',
  boxShadow: '$sm',
  cursor: 'pointer',
  transition: 'transform $normal, box-shadow $normal, border-color $normal',

  '&:hover': {
    transform: 'translateY(-2px)',
    boxShadow: '$lg',
    borderColor: '$primary',
  },

  // Variants: 알림 레벨에 따른 좌측 보더 컬러
  variants: {
    alertLevel: {
      normal: {
        '&::before': {
          content: '""',
          position: 'absolute',
          left: 0, top: '$3', bottom: '$3',
          width: '3px',
          borderRadius: '$full',
          background: '$statusNormal',
        },
      },
      warning: {
        border: '1.5px solid $warningLight',
        background: 'hsl(42, 100%, 99%)',
        '&::before': {
          content: '""',
          position: 'absolute',
          left: 0, top: '$3', bottom: '$3',
          width: '3px',
          borderRadius: '$full',
          background: '$statusWarning',
        },
      },
      critical: {
        border: '1.5px solid $dangerLight',
        background: 'hsl(0, 80%, 99.5%)',
        animation: `${pulseAnimation} 2s ease-in-out infinite`,
        '&::before': {
          content: '""',
          position: 'absolute',
          left: 0, top: '$3', bottom: '$3',
          width: '3px',
          borderRadius: '$full',
          background: '$statusCritical',
        },
      },
    },
    isOnline: {
      false: {
        opacity: 0.65,
      },
      true: {},
    },
  },

  defaultVariants: {
    alertLevel: 'normal',
    isOnline: true,
  },
})

const CardHeader = styled('div', {
  flexBetween: true,
  marginBottom: '$4',
})

const HouseTitle = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$2',
  fontSize: '$lg',
  fontWeight: '$semibold',
  color: '$textPrimary',
})

const StatusDot = styled('div', {
  size: '10px',
  borderRadius: '$full',
  flexShrink: 0,

  variants: {
    isOnline: {
      true: {},
      false: { background: '$statusOffline' },
    },
    alertLevel: {
      normal: { background: '$statusNormal' },
      warning: {
        background: '$statusWarning',
        animation: `${pulseAnimation} 1.5s ease-in-out infinite`,
      },
      critical: {
        background: '$statusCritical',
        animation: `${pulseAnimation} 0.8s ease-in-out infinite`,
      },
    },
  },

  compoundVariants: [
    {
      isOnline: false,
      alertLevel: 'normal',
      css: { background: '$statusOffline' },
    },
  ],

  defaultVariants: { isOnline: true, alertLevel: 'normal' },
})

const SensorGrid = styled('div', {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  gap: '$3',
  marginBottom: '$4',
})

const SensorItem = styled('div', {
  flexColumn: true,
  gap: '$1',
})

const SensorLabel = styled('span', {
  fontSize: '$xs',
  color: '$textMuted',
  fontWeight: '$medium',
})

const SensorValue = styled('span', {
  fontSize: '$xl',
  fontWeight: '$bold',
  letterSpacing: '$tight',

  variants: {
    alert: {
      normal: { color: '$textPrimary' },
      warning: { color: '$warning' },
      critical: { color: '$danger' },
    },
  },

  defaultVariants: { alert: 'normal' },
})

const ActuatorBar = styled('div', {
  display: 'flex',
  flexWrap: 'wrap',
  gap: '$2',
  marginBottom: '$3',
})

const ActuatorBadge = styled('span', {
  fontSize: '$xs',
  fontWeight: '$medium',
  padding: '$1 $2',
  borderRadius: '$full',
  border: '1px solid',
  transition: 'all $fast',

  variants: {
    active: {
      true: {
        background: '$primaryLight',
        borderColor: '$primary200',
        color: '$primary600',
      },
      false: {
        background: '$bgMuted',
        borderColor: '$border',
        color: '$textMuted',
      },
    },
  },

  defaultVariants: { active: false },
})

const NoDataText = styled('p', {
  fontSize: '$md',
  color: '$textMuted',
  textAlign: 'center',
  padding: '$6 0',
})

const LastUpdated = styled('span', {
  fontSize: '$xs',
  color: '$textMuted',
  display: 'block',
  marginTop: '$2',
})

const DetailLink = styled('span', {
  display: 'block',
  marginTop: '$3',
  fontSize: '$sm',
  fontWeight: '$semibold',
  color: '$primary',
  transition: 'color $fast',

  '&:hover': { color: '$primaryHover' },
})
