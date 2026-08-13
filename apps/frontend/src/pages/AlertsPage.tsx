import { useSmartFarmStore } from '../stores/smartfarm.store'
import { styled } from '../lib/stitches.config'

export function AlertsPage() {
  const { alerts, markAlertRead, clearAlerts } = useSmartFarmStore()

  return (
    <PageWrapper>
      <PageHeader>
        <h1>🔔 알림 이력</h1>
        {alerts.length > 0 && (
          <ClearButton onClick={clearAlerts}>전체 삭제</ClearButton>
        )}
      </PageHeader>

      {alerts.length === 0 ? (
        <EmptyState>알림이 없습니다 ✨</EmptyState>
      ) : (
        <AlertList>
          {alerts.map((alert) => (
            <AlertItem
              key={alert.id}
              level={alert.level}
              unread={!alert.read}
              onClick={() => markAlertRead(alert.id)}
            >
              <AlertIcon>
                {alert.level === 'critical' ? '🚨' : alert.level === 'warning' ? '⚠️' : 'ℹ️'}
              </AlertIcon>
              <AlertContent>
                <AlertMessage>{alert.message}</AlertMessage>
                <AlertMeta>
                  {alert.houseId && <span>{alert.houseId} · </span>}
                  <span>{new Date(alert.timestamp).toLocaleString('ko-KR')}</span>
                </AlertMeta>
              </AlertContent>
              {!alert.read && <UnreadDot />}
            </AlertItem>
          ))}
        </AlertList>
      )}
    </PageWrapper>
  )
}

const PageWrapper = styled('div', { padding: '$6' })
const PageHeader = styled('div', {
  flexBetween: true,
  marginBottom: '$5',
  '& h1': { fontSize: '$2xl', fontWeight: '$bold', color: '$textPrimary' },
})
const ClearButton = styled('button', {
  padding: '$2 $4',
  borderRadius: '$md',
  border: '1px solid $border',
  background: '$bgCard',
  color: '$textSecondary',
  cursor: 'pointer',
  fontSize: '$sm',
  '&:hover': { borderColor: '$danger', color: '$danger' },
})
const EmptyState = styled('div', {
  textAlign: 'center',
  padding: '$10',
  color: '$textMuted',
  fontSize: '$lg',
})
const AlertList = styled('div', { display: 'flex', flexDirection: 'column', gap: '$2' })
const AlertItem = styled('div', {
  display: 'flex',
  alignItems: 'flex-start',
  gap: '$3',
  padding: '$4',
  background: '$bgCard',
  border: '1px solid $border',
  borderRadius: '$md',
  cursor: 'pointer',
  transition: 'all $fast',
  '&:hover': { boxShadow: '$sm' },

  variants: {
    level: {
      critical: { borderLeft: '3px solid $danger' },
      warning: { borderLeft: '3px solid $warning' },
      info: { borderLeft: '3px solid $info' },
    },
    unread: {
      true: { background: 'hsl(140, 30%, 99%)' },
      false: { opacity: 0.7 },
    },
  },
})
const AlertIcon = styled('span', { fontSize: '20px', flexShrink: 0 })
const AlertContent = styled('div', { flex: 1 })
const AlertMessage = styled('div', { fontSize: '$md', color: '$textPrimary', fontWeight: '$medium' })
const AlertMeta = styled('div', { fontSize: '$xs', color: '$textMuted', marginTop: '$1' })
const UnreadDot = styled('div', {
  size: '8px',
  borderRadius: '$full',
  background: '$primary',
  flexShrink: 0,
  marginTop: '6px',
})
