/**
 * 대시보드 레이아웃 - 사이드바 + 메인 컨텐츠 + [스마트팜 ⇄ 스마트빌딩] 듀얼 모드 지원
 */

import { useState } from 'react'
import { Outlet, NavLink, useLocation, useNavigate } from 'react-router-dom'
import { styled } from '../../lib/stitches.config'
import { useSmartFarmStore } from '../../stores/smartfarm.store'
import { useBuildingStore } from '../../stores/building.store'

export function DashboardLayout() {
  const location = useLocation()
  const navigate = useNavigate()
  const isBuildingMode = location.pathname.startsWith('/building')

  const { farmName, wsConnected, unreadAlertCount, theme, setTheme } = useSmartFarmStore()
  const { buildingName } = useBuildingStore()

  const navItems = isBuildingMode
    ? [
        { to: '/building', icon: '🏢', label: '빌딩 대시보드' },
        { to: '/tablet-bms', icon: '📱', label: '태블릿 8-Col 관제' },
        { to: '/alerts', icon: '🔔', label: '보안/알림' },
        { to: '/settings', icon: '⚙️', label: '설정' },
      ]
    : [
        { to: '/dashboard', icon: '🏠', label: '농가 대시보드' },
        { to: '/automation', icon: '⚡', label: '자동화 규칙' },
        { to: '/ess', icon: '🔋', label: 'ESS 전력' },
        { to: '/alerts', icon: '🔔', label: '알림' },
        { to: '/settings', icon: '⚙️', label: '설정' },
      ]

  return (
    <LayoutWrapper>
      {/* 사이드바 */}
      <Sidebar>
        {/* 모드 스위처 (스마트팜 vs 스마트빌딩) */}
        <ModeSwitcherWrapper>
          <ModeToggleBtn
            active={!isBuildingMode}
            onClick={() => navigate('/dashboard')}
          >
            🌱 농가 모드
          </ModeToggleBtn>
          <ModeToggleBtn
            active={isBuildingMode}
            onClick={() => navigate('/building')}
          >
            🏢 건물 모드
          </ModeToggleBtn>
        </ModeSwitcherWrapper>

        {/* 로고 */}
        <SidebarHeader>
          <Logo>{isBuildingMode ? '🏢' : '🌱'}</Logo>
          <div>
            <FarmName>{isBuildingMode ? buildingName : farmName}</FarmName>
            <ConnectionStatus connected={wsConnected}>
              {wsConnected ? '● 실시간 연결됨' : '● 연결 중...'}
            </ConnectionStatus>
          </div>
        </SidebarHeader>

        {/* 네비게이션 */}
        <Nav>
          {navItems.map((item) => (
            <NavItem key={item.to}>
              <StyledNavLink to={item.to}>
                <NavIcon>{item.icon}</NavIcon>
                <NavLabel>{item.label}</NavLabel>
                {item.to === '/alerts' && unreadAlertCount > 0 && (
                  <AlertBadge>{unreadAlertCount}</AlertBadge>
                )}
              </StyledNavLink>
            </NavItem>
          ))}
        </Nav>

        {/* 테마 토글 */}
        <SidebarFooter>
          <ThemeToggle>
            {(['light', 'dark', 'high-contrast'] as const).map((t) => (
              <ThemeButton
                key={t}
                active={theme === t}
                onClick={() => setTheme(t)}
                title={t === 'light' ? '라이트' : t === 'dark' ? '다크' : '고대비'}
              >
                {t === 'light' ? '☀️' : t === 'dark' ? '🌙' : '◐'}
              </ThemeButton>
            ))}
          </ThemeToggle>
        </SidebarFooter>
      </Sidebar>

      {/* 메인 컨텐츠 */}
      <MainContent>
        <Outlet />
      </MainContent>
    </LayoutWrapper>
  )
}

const LayoutWrapper = styled('div', {
  display: 'flex',
  minHeight: '100vh',
  background: '$bgBase',
})

const Sidebar = styled('aside', {
  width: '240px',
  background: '$bgCard',
  borderRight: '1px solid $border',
  display: 'flex',
  flexDirection: 'column',
  position: 'sticky',
  top: 0,
  height: '100vh',
  flexShrink: 0,
  boxShadow: '$sm',

  '@tablet': {
    display: 'none', // 모바일에서는 하단 탭바로 대체
  },
})

const ModeSwitcherWrapper = styled('div', {
  display: 'grid',
  gridTemplateColumns: '1fr 1fr',
  padding: '$3 $3 $1',
  gap: '4px',
  background: '$bgMuted',
  margin: '$2 $2 0',
  borderRadius: '$md',
})

const ModeToggleBtn = styled('button', {
  border: 'none',
  padding: '$2 $1',
  borderRadius: '$sm',
  fontSize: '11px',
  fontWeight: '$bold',
  cursor: 'pointer',
  background: 'transparent',
  color: '$textSecondary',
  transition: 'all $fast',

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

const SidebarHeader = styled('div', {
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
  padding: '$4 $4',
  borderBottom: '1px solid $border',
})

const Logo = styled('div', {
  fontSize: '28px',
  lineHeight: 1,
  flexShrink: 0,
})

const FarmName = styled('div', {
  fontSize: '$sm',
  fontWeight: '$bold',
  color: '$textPrimary',
})

const ConnectionStatus = styled('div', {
  fontSize: '$xs',
  marginTop: '2px',

  variants: {
    connected: {
      true: { color: '$success' },
      false: { color: '$textMuted' },
    },
  },
})

const Nav = styled('nav', {
  flex: 1,
  padding: '$3 $2',
  display: 'flex',
  flexDirection: 'column',
  gap: '$1',
  overflowY: 'auto',
})

const NavItem = styled('div', {})

const StyledNavLink = styled(NavLink, {
  display: 'flex',
  alignItems: 'center',
  gap: '$3',
  padding: '$3 $3',
  borderRadius: '$md',
  textDecoration: 'none',
  color: '$textSecondary',
  fontWeight: '$medium',
  fontSize: '$md',
  transition: 'all $fast',
  position: 'relative',

  '&:hover': {
    background: '$bgMuted',
    color: '$textPrimary',
  },

  '&.active': {
    background: '$primaryLight',
    color: '$primary',
    fontWeight: '$semibold',
  },
})

const NavIcon = styled('span', { fontSize: '18px', width: '24px', textAlign: 'center' })
const NavLabel = styled('span', { flex: 1 })

const AlertBadge = styled('span', {
  background: '$danger',
  color: '$textInverse',
  fontSize: '10px',
  fontWeight: '$bold',
  padding: '2px 6px',
  borderRadius: '$full',
  minWidth: '18px',
  textAlign: 'center',
})

const MainContent = styled('main', {
  flex: 1,
  overflowY: 'auto',
  minWidth: 0,
})

const SidebarFooter = styled('div', {
  padding: '$4',
  borderTop: '1px solid $border',
})

const ThemeToggle = styled('div', {
  display: 'flex',
  gap: '$2',
})

const ThemeButton = styled('button', {
  size: '32px',
  borderRadius: '$md',
  border: '1px solid $border',
  background: '$bgMuted',
  cursor: 'pointer',
  fontSize: '16px',
  transition: 'all $fast',

  '&:hover': { borderColor: '$primary' },

  variants: {
    active: {
      true: {
        background: '$primaryLight',
        borderColor: '$primary',
      },
      false: {},
    },
  },
})
