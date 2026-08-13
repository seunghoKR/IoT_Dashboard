import { Routes, Route, Navigate } from 'react-router-dom'
import { useWebSocket } from './hooks/useWebSocket'
import { DashboardLayout } from './components/ui/DashboardLayout'
import { DashboardPage } from './pages/DashboardPage'
import { GreenhouseDetailPage } from './pages/GreenhouseDetailPage'
import { AutomationPage } from './pages/AutomationPage'
import { EssPage } from './pages/EssPage'
import { AlertsPage } from './pages/AlertsPage'
import { SettingsPage } from './pages/SettingsPage'

export default function App() {
  // WebSocket 실시간 연결 (앱 전체 수명주기)
  useWebSocket()

  return (
    <Routes>
      {/* 대시보드 레이아웃 (사이드바 포함) */}
      <Route element={<DashboardLayout />}>
        <Route index element={<Navigate to="/dashboard" replace />} />
        <Route path="/dashboard" element={<DashboardPage />} />
        <Route path="/greenhouses/:houseId" element={<GreenhouseDetailPage />} />
        <Route path="/automation" element={<AutomationPage />} />
        <Route path="/ess" element={<EssPage />} />
        <Route path="/alerts" element={<AlertsPage />} />
        <Route path="/settings" element={<SettingsPage />} />
      </Route>

      {/* 404 */}
      <Route path="*" element={<Navigate to="/dashboard" replace />} />
    </Routes>
  )
}
