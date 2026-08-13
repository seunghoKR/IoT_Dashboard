import { useSmartFarmStore } from '../../stores/smartfarm.store'
import { styled } from '../../lib/stitches.config'
import type { Alert } from '../../stores/smartfarm.store'

interface AlertBannerProps {
  alerts: Alert[]
}

export function AlertBanner({ alerts }: AlertBannerProps) {
  const { markAlertRead } = useSmartFarmStore()
  const latest = alerts[0]

  return (
    <Banner>
      <BannerIcon>🚨</BannerIcon>
      <BannerText>
        <strong>[긴급]</strong> {latest.message}
        {alerts.length > 1 && ` 외 ${alerts.length - 1}건`}
      </BannerText>
      <DismissButton onClick={() => alerts.forEach(a => markAlertRead(a.id))}>
        ✕
      </DismissButton>
    </Banner>
  )
}

const Banner = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
  padding: '$3 $5',
  background: '$danger',
  color: '$textInverse',
  borderRadius: '$md',
  fontSize: '$md',
  fontWeight: '$medium',
})
const BannerIcon = styled('span', { fontSize: '20px', flexShrink: 0 })
const BannerText = styled('span', { flex: 1 })
const DismissButton = styled('button', {
  background: 'rgba(255,255,255,0.2)',
  border: 'none',
  color: '$textInverse',
  width: '28px',
  height: '28px',
  borderRadius: '$full',
  cursor: 'pointer',
  fontSize: '$md',
  flexShrink: 0,
  display: 'flex',
  alignItems: 'center',
  justifyContent: 'center',
  '&:hover': { background: 'rgba(255,255,255,0.3)' },
})
