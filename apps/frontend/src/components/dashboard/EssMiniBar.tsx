/**
 * ESS 배터리 미니 바 컴포넌트
 */

import { styled } from '../../lib/stitches.config'
import type { EssState } from '../../stores/smartfarm.store'

interface EssMiniBarProps {
  ess: EssState | null
}

export function EssMiniBar({ ess }: EssMiniBarProps) {
  if (!ess) return (
    <EssCard>
      <EssTitle>🔋 ESS</EssTitle>
      <EssValue>데이터 없음</EssValue>
    </EssCard>
  )

  const isCharging = ess.current_a > 0
  const socLevel = ess.soc_percent >= 50 ? 'good' : ess.soc_percent >= 20 ? 'warning' : 'critical'

  return (
    <EssCard>
      <EssTitle>🔋 ESS 전력</EssTitle>
      <EssContent>
        {/* 배터리 게이지 */}
        <BatteryGauge>
          <BatteryFill percent={ess.soc_percent} level={socLevel} />
          <BatteryText>{ess.soc_percent.toFixed(0)}%</BatteryText>
        </BatteryGauge>

        <EssDetails>
          <EssValue level={socLevel}>
            {isCharging ? '⚡ 충전 중' : '방전 중'}
          </EssValue>
          <EssSubtext>{ess.voltage_v.toFixed(1)}V · {Math.abs(ess.current_a).toFixed(1)}A</EssSubtext>
          {!isCharging && (
            <EssSubtext>잔여 약 {ess.estimatedHoursRemaining}시간</EssSubtext>
          )}
          {ess.solar_power_w > 0 && (
            <EssSubtext>☀️ 태양광 {ess.solar_power_w.toFixed(0)}W</EssSubtext>
          )}
        </EssDetails>
      </EssContent>
    </EssCard>
  )
}

const EssCard = styled('div', {
  minWidth: '220px',
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  padding: '$4 $5',
  boxShadow: '$sm',
  display: 'flex',
  flexDirection: 'column',
  gap: '$2',
})

const EssTitle = styled('span', {
  fontSize: '$xs',
  fontWeight: '$semibold',
  color: '$textMuted',
  textTransform: 'uppercase',
  letterSpacing: '$widest',
})

const EssContent = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
})

const BatteryGauge = styled('div', {
  position: 'relative',
  width: '60px',
  height: '24px',
  border: '2px solid $borderStrong',
  borderRadius: '$xs',
  overflow: 'hidden',

  '&::after': {
    content: '""',
    position: 'absolute',
    right: '-6px',
    top: '50%',
    transform: 'translateY(-50%)',
    width: '4px',
    height: '10px',
    background: '$borderStrong',
    borderRadius: '0 2px 2px 0',
  },
})

const BatteryFill = styled('div', {
  height: '100%',
  transition: 'width $slow',

  variants: {
    level: {
      good: { background: '$success' },
      warning: { background: '$warning' },
      critical: { background: '$danger' },
    },
    percent: {},
  },
}, (props: { percent?: number }) => ({
  width: `${props?.percent ?? 0}%`,
}))

const BatteryText = styled('span', {
  position: 'absolute',
  inset: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  fontSize: '10px',
  fontWeight: '$bold',
  color: '$textInverse',
  textShadow: '0 0 4px rgba(0,0,0,0.5)',
})

const EssDetails = styled('div', {
  flexColumn: true,
  gap: '2px',
})

const EssValue = styled('span', {
  fontSize: '$md',
  fontWeight: '$semibold',

  variants: {
    level: {
      good: { color: '$success' },
      warning: { color: '$warning' },
      critical: { color: '$danger' },
    },
  },
})

const EssSubtext = styled('span', {
  fontSize: '$xs',
  color: '$textMuted',
})
