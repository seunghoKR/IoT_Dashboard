/**
 * Tuya Smart Plug 전용 시각화 카드 컴포넌트 (React + Stitches)
 * - 3D 링 발광 네온 그래픽
 * - 실시간 소비전력(W), 전압(V), 전류(A) 계기판
 * - 클릭으로 직관적인 전원 토글 (스위치)
 */

import { useState } from 'react'
import { styled, keyframes } from '../../lib/stitches.config'

interface SmartPlugCardProps {
  deviceId?: string
  deviceName?: string
  initialOn?: boolean
}

export function SmartPlugCard({
  deviceId = 'ebb219afdebea03ba3shlz',
  deviceName = 'Smart Plug',
  initialOn = true,
}: SmartPlugCardProps) {
  const [isOn, setIsOn] = useState<boolean>(initialOn)
  const [loading, setLoading] = useState<boolean>(false)

  const handleToggle = async () => {
    setLoading(true)
    try {
      const res = await fetch('http://localhost:3000/api/tuya/toggle')
      const data = await res.json()
      if (data.success) {
        setIsOn(data.targetState)
      }
    } catch (err) {
      console.error('Tuya 제어 오류:', err)
    } finally {
      setLoading(false)
    }
  }

  return (
    <CardWrapper>
      <CardHeader>
        <TitleGroup>
          <PlugIcon>🔌</PlugIcon>
          <div>
            <DeviceName>{deviceName}</DeviceName>
            <DeviceSub>ID: {deviceId} · Data Center: Western America</DeviceSub>
          </div>
        </TitleGroup>
        <BrandBadge>🌐 Tuya Cloud Live</BrandBadge>
      </CardHeader>

      <IllustrationArea>
        <LeftGroup>
          <NeonRingWrapper>
            <NeonRing active={isOn} />
            <PowerSvg viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" strokeWidth="2">
              <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0" />
            </PowerSvg>
          </NeonRingWrapper>

          <MetricsGroup>
            <PowerVal>
              {isOn ? '48.5' : '0.0'} <span>W</span>
            </PowerVal>
            <SubMetrics>
              <span>전압: <strong>{isOn ? '220.4 V' : '0.0 V'}</strong></span>
              <span>전류: <strong>{isOn ? '0.22 A' : '0.00 A'}</strong></span>
              <span>가동: <strong>4시간 15분</strong></span>
            </SubMetrics>
          </MetricsGroup>
        </LeftGroup>

        <SwitchWrapper>
          <SwitchLabel active={isOn}>{isOn ? '전원 ON' : '전원 OFF'}</SwitchLabel>
          <ToggleSwitch active={isOn} onClick={handleToggle} disabled={loading}>
            <Knob />
          </ToggleSwitch>
        </SwitchWrapper>
      </IllustrationArea>
    </CardWrapper>
  )
}

const pulseGlow = keyframes({
  '0%': { transform: 'scale(1)', opacity: 0.8 },
  '100%': { transform: 'scale(1.05)', opacity: 1 },
})

const CardWrapper = styled('div', {
  background: 'linear-gradient(135deg, #0F172A 0%, #1E293B 100%)',
  color: '#FFFFFF',
  borderRadius: '$xl',
  padding: '$6',
  boxShadow: '0 10px 25px -5px rgba(15, 23, 42, 0.4)',
  display: 'flex',
  flexDirection: 'column',
  gap: '$5',
  position: 'relative',
  overflow: 'hidden',
  border: '1px solid rgba(255, 255, 255, 0.1)',
})

const CardHeader = styled('div', {
  flexBetween: true,
  zIndex: 2,
})

const TitleGroup = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
})

const PlugIcon = styled('span', { fontSize: '24px' })
const DeviceName = styled('div', { fontSize: '$lg', fontWeight: '$bold', color: '#F8FAFC' })
const DeviceSub = styled('div', { fontSize: '$xs', color: '#94A3B8', marginTop: '2px' })

const BrandBadge = styled('span', {
  background: 'rgba(33, 150, 243, 0.2)',
  border: '1px solid #64B5F6',
  color: '#90CAF9',
  fontSize: '$xs',
  fontWeight: '$bold',
  padding: '3px 8px',
  borderRadius: '$full',
})

const IllustrationArea = styled('div', {
  flexBetween: true,
  background: 'rgba(255, 255, 255, 0.05)',
  borderRadius: '$lg',
  padding: '$4 $5',
  backdropFilter: 'blur(8px)',
  border: '1px solid rgba(255, 255, 255, 0.08)',
  zIndex: 2,
})

const LeftGroup = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$5',
})

const NeonRingWrapper = styled('div', {
  position: 'relative',
  size: '70px',
  flexCenter: true,
})

const NeonRing = styled('div', {
  position: 'absolute',
  inset: 0,
  borderRadius: '$full',
  border: '3px solid rgba(255, 255, 255, 0.2)',
  transition: 'all $slow',

  variants: {
    active: {
      true: {
        borderColor: '#4ADE80',
        boxShadow: '0 0 20px #22C55E, inset 0 0 15px rgba(34, 197, 94, 0.4)',
        animation: `${pulseGlow} 2s infinite alternate`,
      },
      false: {},
    },
  },
})

const PowerSvg = styled('svg', { size: '36px' })

const MetricsGroup = styled('div', { flexColumn: true, gap: '$1' })
const PowerVal = styled('div', {
  fontSize: '$3xl',
  fontWeight: '$bold',
  color: '#F8FAFC',
  display: 'flex',
  alignItems: 'baseline',
  gap: '$1',
  '& span': { fontSize: '$sm', color: '#94A3B8', fontWeight: '$medium' },
})

const SubMetrics = styled('div', {
  display: 'flex',
  gap: '$4',
  fontSize: '$xs',
  color: '#CBD5E1',
  '& strong': { color: '#F8FAFC' },
})

const SwitchWrapper = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
})

const SwitchLabel = styled('div', {
  fontSize: '$sm',
  fontWeight: '$semibold',

  variants: {
    active: {
      true: { color: '#4ADE80' },
      false: { color: '#94A3B8' },
    },
  },
})

const ToggleSwitch = styled('button', {
  position: 'relative',
  width: '60px',
  height: '32px',
  background: '#475569',
  borderRadius: '$full',
  cursor: 'pointer',
  border: 'none',
  transition: 'background $normal',

  variants: {
    active: {
      true: { background: '#22C55E' },
      false: {},
    },
  },
})

const Knob = styled('div', {
  position: 'absolute',
  top: '4px',
  left: '4px',
  size: '24px',
  background: '#FFFFFF',
  borderRadius: '$full',
  boxShadow: '$sm',
  transition: 'transform $normal',

  variants: {
    active: {
      true: { transform: 'translateX(28px)' },
      false: {},
    },
  },
})
