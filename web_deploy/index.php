<?php
require_once __DIR__ . '/config.php';

$initHouses = [
    1 => ['id' => 1, 'name' => '🍓 1동 설향 딸기 재배하우스', 'crop' => '딸기 (설향)'],
    2 => ['id' => 2, 'name' => '🌱 2동 육묘 및 보조 온실', 'crop' => '딸기 모종']
];

try {
    $pdo = getDbConnection();
    $prefix = DB_PREFIX;
    $stmt = $pdo->query("SELECT * FROM `{$prefix}houses` ORDER BY `sort_order` ASC, `id` ASC");
    $dbHouses = [];
    while ($row = $stmt->fetch()) {
        $hId = (int)$row['id'];
        $dbHouses[$hId] = [
            'id' => $hId,
            'name' => $row['house_name'],
            'crop' => $row['crop_type']
        ];
    }
    if (!empty($dbHouses)) {
        $initHouses = $dbHouses;
    }
} catch (Exception $e) {
    // DB 오류 시 기본 2개 동 유지
}
$initHousesJson = json_encode($initHouses, JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>🍓 누리오 스마트 팜 (Nurio Smart Farm) - 비주얼 디지털 트윈 관제</title>
  
  <!-- 📱 PWA & 태블릿 앱 바로가기 메타태그 및 매니페스트 -->
  <link rel="manifest" href="manifest.json">
  <link rel="icon" type="image/svg+xml" href="icon.svg">
  <link rel="apple-touch-icon" href="icon.svg">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#070B19">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
  
  <!-- Google Identity Services (GIS) -->
  <script src="https://accounts.google.com/gsi/client" async defer></script>

  <style>
    :root {
      --bg-base: #070B19;
      --bg-card: #152238;
      --bg-card-sub: #1E2D4A;
      --bg-btn: #1D2D49;
      --primary: #10B981;
      --primary-light: rgba(16, 185, 129, 0.2);
      --primary-border: #059669;
      --cyan: #06B6D4;
      --indigo: #6366F1;
      --amber: #F59E0B;
      --rose: #F43F5E;
      --sky: #38BDF8;
      --text-primary: #FFFFFF;
      --text-secondary: #E2E8F0;
      --text-muted: #94A3B8;
      --border: rgba(255, 255, 255, 0.16);
      --border-bright: rgba(255, 255, 255, 0.3);
      --border-glow: rgba(16, 185, 129, 0.5);
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: "Pretendard", -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--bg-base);
      color: var(--text-primary);
      display: flex;
      flex-direction: column;
      min-height: 100vh;
      overflow-x: hidden;
      transition: background 0.8s ease;
    }

    /* 🌦️ 실시간 날씨별 동적 전역 배경 */
    body.weather-night {
      background: radial-gradient(circle at 50% 10%, #131E3A 0%, #070B19 100%);
    }
    body.weather-day-clear {
      background: radial-gradient(circle at 50% 10%, #1D4E89 0%, #09152A 100%);
    }
    body.weather-day-cloudy {
      background: radial-gradient(circle at 50% 10%, #2B3A4F 0%, #0C1322 100%);
    }
    body.weather-rain {
      background: radial-gradient(circle at 50% 10%, #1A2E3B 0%, #060F17 100%);
    }

    /* 📱 100% 무스크롤 WUXGA/WQXGA 16:10 완벽 고정 핏 (Tablet/PC Zero-Scroll Mode) */
    /* 📱 100% 무스크롤 WUXGA/WQXGA 16:10 완벽 고정 핏 (Tablet/PC Zero-Scroll Mode) */
    body {
      height: 100vh;
      height: 100dvh;
      max-height: 100dvh;
      overflow: hidden !important;
      padding: 4px 8px;
      display: flex;
      flex-direction: column;
      gap: 4px;
    }

    /* 🌾 메인 통합 뷰포트 레이아웃 */
    main {
      flex: 1;
      width: 100%;
      height: 100%;
      min-height: 0;
      display: flex;
      flex-direction: column;
      gap: 4px;
      overflow: hidden;
    }

    .dashboard-split-layout {
      flex: 1;
      height: 100%;
      min-height: 0;
      gap: 8px;
      display: grid;
      grid-template-columns: 1.6fr 1fr;
      overflow: hidden;
    }

    /* ========================================================
       🌟 [단일 통합 스마트 탑바] 로고 + 해상도 + 날씨/예보 + 전체화면 + 사용자 관리 일체화
       ======================================================== */
    .dashboard-unified-top-bar {
      height: 36px;
      min-height: 36px;
      max-height: 36px;
      background: rgba(15, 25, 45, 0.94);
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 0 10px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      backdrop-filter: blur(16px);
      box-shadow: 0 3px 10px rgba(0,0,0,0.3);
      flex-shrink: 0;
    }

    .top-bar-left {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-shrink: 0;
    }
    .brand-logo { font-size: 18px; }
    .brand-name { font-size: 14px; font-weight: 800; color: #FFFFFF; letter-spacing: -0.3px; white-space: nowrap; }

    /* 🖥️ 해상도 인디케이터 배지 */
    .res-indicator-badge {
      background: rgba(30, 45, 74, 0.9);
      border: 1px solid #38BDF8;
      border-radius: 6px;
      padding: 1px 5px;
      font-size: 9.5px;
      font-weight: 800;
      color: #7DD3FC;
      display: flex;
      align-items: center;
      gap: 3px;
      white-space: nowrap;
    }

    /* ⛶ 전체화면 버튼 */
    .btn-fullscreen-toggle {
      background: linear-gradient(180deg, #1E3A8A 0%, #172554 100%);
      border: 1.5px solid #3B82F6;
      color: #93C5FD;
      border-radius: 8px;
      padding: 3px 8px;
      font-size: 11px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3), inset 0 1px 2px rgba(255, 255, 255, 0.2);
      transition: all 0.16s ease;
      white-space: nowrap;
    }
    .btn-fullscreen-toggle:hover {
      background: linear-gradient(180deg, #2563EB 0%, #1D4ED8 100%);
      color: #FFFFFF;
      border-color: #60A5FA;
      transform: translateY(-1px);
    }
    .btn-fullscreen-toggle:active {
      transform: translateY(1px) scale(0.97);
    }

    /* 🌤️ 중앙 날씨 & 예보 슬라이더 */
    .top-bar-center {
      display: flex;
      align-items: center;
      gap: 6px;
      overflow: hidden;
      flex: 1;
      justify-content: center;
    }
    .weather-live-info {
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 11px;
      font-weight: 700;
      white-space: nowrap;
    }
    .region-select-badge {
      background: rgba(56, 189, 248, 0.15);
      border: 1px solid #38BDF8;
      color: #BAE6FD;
      font-size: 9px;
      padding: 1px 4px;
      border-radius: 4px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 2px;
      font-weight: 700;
    }
    .forecast-slider {
      display: flex;
      align-items: center;
      gap: 5px;
      font-size: 10px;
      color: var(--text-secondary);
      overflow-x: auto;
    }
    .forecast-item {
      display: flex;
      align-items: center;
      gap: 2px;
      background: rgba(255, 255, 255, 0.05);
      padding: 1px 4px;
      border-radius: 4px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      white-space: nowrap;
    }

    /* 👤 우측 관리자 & 설정 */
    .top-bar-right {
      display: flex;
      align-items: center;
      gap: 5px;
      flex-shrink: 0;
    }
    .admin-badge {
      background: rgba(30, 45, 74, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 14px;
      padding: 1px 6px;
      display: flex;
      align-items: center;
      gap: 4px;
      font-size: 10.5px;
      font-weight: 700;
      color: #F8FAFC;
      white-space: nowrap;
    }
    .admin-avatar {
      width: 16px;
      height: 16px;
      border-radius: 50%;
      background: #4285F4;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 9.5px;
      font-weight: 900;
      color: #FFFFFF;
    }
    .google-pill {
      background: rgba(66, 133, 244, 0.2);
      border: 1px solid #4285F4;
      color: #93C5FD;
      font-size: 8.5px;
      padding: 1px 3px;
      border-radius: 5px;
    }

    /* 🖥️ [WQXGA 2560x1600 모드] 초고해상도 스케일업 스타일 */
    body.res-wqxga {
      padding: 6px 12px;
      gap: 6px;
    }
    body.res-wqxga .dashboard-unified-top-bar {
      height: 40px;
      min-height: 40px;
      max-height: 40px;
    }
    body.res-wqxga .brand-name { font-size: 16px; }
    body.res-wqxga .ctrl-tile .tile-name {
      font-size: 16px;
      font-weight: 900;
    }
    body.res-wqxga .ctrl-tile .tile-icon {
      font-size: 26px;
    }

    /* 🔒 로그아웃(게스트 모니터링) 모드일 때: 전체 너비 100% 모니터링 뷰 */
    body.logged-out .dashboard-split-layout {
      grid-template-columns: 1fr !important;
    }
    body.logged-out .control-deck-pane {
      display: none !important;
    }
    body.logged-out .admin-elements {
      display: none !important;
    }
    body.logged-in .guest-elements {
      display: none !important;
    }

    /* 🔑 로그인 / 로그아웃 버튼 */
    /* 🔑 로그인 / 로그아웃 버튼 (3D Tactile) */
    .btn-google-login {
      background: linear-gradient(180deg, #4E8EFF 0%, #2A6CEB 100%);
      border: 1.5px solid #79A9FF;
      color: #FFFFFF;
      border-radius: 10px;
      padding: 6px 12px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      box-shadow: 0 4px 10px rgba(42, 108, 235, 0.4), inset 0 1px 2px rgba(255, 255, 255, 0.4);
      transition: all 0.16s ease;
    }
    .btn-google-login:hover {
      background: linear-gradient(180deg, #3B7BF6 0%, #1A5CD8 100%);
      transform: translateY(-1px);
      box-shadow: 0 6px 14px rgba(42, 108, 235, 0.5), inset 0 1px 2px rgba(255, 255, 255, 0.5);
    }
    .btn-google-login:active {
      transform: translateY(1px) scale(0.97);
      box-shadow: 0 1px 3px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.3);
    }

    .btn-logout {
      background: linear-gradient(180deg, #3A1B28 0%, #25101A 100%);
      border: 1.5px solid #F43F5E;
      color: #FDA4AF;
      border-radius: 10px;
      padding: 5px 10px;
      font-size: 11px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      box-shadow: 0 3px 8px rgba(244, 63, 94, 0.25), inset 0 1px 2px rgba(255, 255, 255, 0.15);
      transition: all 0.16s ease;
    }
    .btn-logout:hover {
      background: linear-gradient(180deg, #E11D48 0%, #BE123C 100%);
      color: #FFFFFF;
      transform: translateY(-1px);
      box-shadow: 0 5px 12px rgba(244, 63, 94, 0.4);
    }
    .btn-logout:active {
      transform: translateY(1px) scale(0.97);
      box-shadow: 0 1px 3px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.3);
    }

    /* ⚙️ 설정 버튼 (3D Tactile) */
    .btn-header-setting {
      background: linear-gradient(180deg, #2A3B5C 0%, #1A2842 100%);
      border: 1.5px solid rgba(255, 255, 255, 0.25);
      color: #FFFFFF;
      border-radius: 10px;
      padding: 5px 10px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 5px;
      box-shadow: 0 3px 8px rgba(0,0,0,0.3), inset 0 1px 2px rgba(255, 255, 255, 0.2);
      transition: all 0.16s ease;
    }
    .btn-header-setting:hover {
      background: linear-gradient(180deg, #33486F 0%, #203152 100%);
      border-color: #38BDF8;
      box-shadow: 0 4px 12px rgba(56, 189, 248, 0.35);
      transform: translateY(-1px);
    }
    .btn-header-setting:active {
      transform: translateY(1px) scale(0.97);
      box-shadow: 0 1px 3px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.3);
    }

    /* 🌾 메인 레이아웃 */
    main {
      flex: 1;
      padding: 8px 14px;
      max-width: 1760px;
      margin: 0 auto;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 8px;
    }

    .dashboard-split-layout {
      display: grid;
      grid-template-columns: 1.62fr 1fr;
      gap: 12px;
      width: 100%;
      flex: 1;
      min-height: 0;
      transition: grid-template-columns 0.3s ease;
    }

    /* ========================================================
       🍓 [좌측 영역] 대형 메인 뷰 + 섬네일 하우스 관제 덱
       ======================================================== */
    .visual-twin-pane {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 0;
      height: 100%;
    }

    /* 🌤️ 상단 지역 날씨 & 예상 예보 바 */
    .weather-forecast-bar {
      background: rgba(21, 34, 56, 0.88);
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 6px 12px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 8px;
      backdrop-filter: blur(10px);
    }
    .weather-live-info {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      font-weight: 700;
      white-space: nowrap;
    }
    .region-select-badge {
      background: rgba(56, 189, 248, 0.15);
      border: 1px solid #38BDF8;
      color: #BAE6FD;
      font-size: 10px;
      padding: 2px 6px;
      border-radius: 4px;
      cursor: pointer;
      display: inline-flex;
      align-items: center;
      gap: 3px;
      font-weight: 700;
    }
    .forecast-slider {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 11px;
      color: var(--text-secondary);
      overflow-x: auto;
    }
    .forecast-item {
      display: flex;
      align-items: center;
      gap: 3px;
      background: rgba(255, 255, 255, 0.05);
      padding: 2px 6px;
      border-radius: 4px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      white-space: nowrap;
    }

    /* 🏠 [일체형 메인 관제 카드] 메인 하우스 뷰 + 하단 섬네일 덱 통합 */
    .twin-stage-card {
      background: var(--bg-card);
      border: 1px solid var(--border);
      border-radius: 12px;
      padding: 4px 6px;
      display: flex;
      flex-direction: column;
      gap: 4px;
      flex: 1;
      min-height: 0;
      position: relative;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.35);
    }

    .stage-header-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 2px 4px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .house-title-tag {
      font-size: 15px;
      font-weight: 900;
      color: #FFFFFF;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .crop-pill {
      background: rgba(16, 185, 129, 0.25);
      color: #6EE7B7;
      border: 1px solid #10B981;
      padding: 1px 6px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 800;
    }
    .multi-status-pill {
      background: rgba(245, 158, 11, 0.2);
      color: #FDE68A;
      border: 1px solid #F59E0B;
      padding: 1px 6px;
      border-radius: 4px;
      font-size: 10px;
      font-weight: 800;
      display: none;
    }

    /* 캔버스 래퍼 & SVG (하단 바닥선에 0px 완벽 밀착) */
    .greenhouse-svg-wrapper {
      flex: 1;
      min-height: 0;
      position: relative;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      background: #0B132B;
      border-radius: 8px;
      border: 1px solid rgba(255, 255, 255, 0.08);
      overflow: hidden;
    }
    .greenhouse-svg-wrapper svg {
      width: 100%;
      height: 100%;
      display: block;
    }

    /* 실내 온·습도 HUD 위젯 */
    .sensor-hud-center {
      position: absolute;
      top: 8px;
      left: 10px;
      background: rgba(15, 23, 42, 0.9);
      border: 1px solid rgba(56, 189, 248, 0.4);
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.5);
      border-radius: 8px;
      padding: 4px 10px;
      display: flex;
      align-items: center;
      gap: 10px;
      z-index: 10;
      backdrop-filter: blur(8px);
    }
    .hud-stat-box { display: flex; flex-direction: column; }
    .hud-label { font-size: 9px; color: var(--text-muted); font-weight: 700; }
    .hud-value-temp { font-size: 14px; font-weight: 900; color: #34D399; }
    .hud-value-hum { font-size: 14px; font-weight: 900; color: #38BDF8; }

    /* 실외 날씨 효과 배지 */
    .outdoor-weather-effect-box {
      position: absolute;
      top: 8px;
      right: 10px;
      background: rgba(15, 23, 42, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 8px;
      padding: 4px 8px;
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 10px;
      font-weight: 800;
      color: #F8FAFC;
      z-index: 10;
    }

    /* ========================================================
       🏡 [메인 하우스 바로 아래] 슬라이딩 "섬네일 하우스" 덱 (140px 슬림 핏)
       ======================================================== */
    .thumbnail-houses-deck-box {
      background: rgba(11, 19, 43, 0.95);
      border: 1.5px solid rgba(255, 255, 255, 0.16);
      border-radius: 10px;
      padding: 4px 6px;
      display: flex;
      flex-direction: column;
      gap: 3px;
      height: 140px;
      min-height: 140px;
      max-height: 140px;
      box-shadow: 0 4px 14px rgba(0,0,0,0.4);
    }

    .thumb-section-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 2px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .thumb-section-title {
      font-size: 11.5px;
      font-weight: 800;
      color: #BAE6FD;
      display: flex;
      align-items: center;
      gap: 6px;
    }

    /* 가로 슬라이딩 컨테이너 */
    .thumbnail-houses-slider {
      display: flex;
      align-items: stretch;
      gap: 10px;
      flex: 1;
      min-height: 0;
      overflow-x: auto;
      overflow-y: hidden;
      padding: 2px 2px 4px 2px;
      scroll-behavior: smooth;
      -webkit-overflow-scrolling: touch;
    }
    .thumbnail-houses-slider::-webkit-scrollbar { height: 5px; }
    .thumbnail-houses-slider::-webkit-scrollbar-track { background: rgba(0, 0, 0, 0.3); border-radius: 4px; }
    .thumbnail-houses-slider::-webkit-scrollbar-thumb { background: rgba(56, 189, 248, 0.5); border-radius: 4px; }

    /* 🔲 큼직하고 시원한 3D 섬네일 하우스 카드 */
    .thumb-house-card {
      background: linear-gradient(180deg, #1A2942 0%, #121E33 100%);
      border: 2px solid rgba(255, 255, 255, 0.16);
      border-radius: 12px;
      width: 210px;
      min-width: 210px;
      height: 100%;
      padding: 6px 8px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      cursor: pointer;
      position: relative;
      transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
      user-select: none;
      box-shadow: 0 4px 10px rgba(0,0,0,0.35), inset 0 1px 2px rgba(255,255,255,0.1);
      flex-shrink: 0;
    }
    .thumb-house-card:hover {
      border-color: #38BDF8;
      background: linear-gradient(180deg, #223758 0%, #162640 100%);
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(56, 189, 248, 0.3);
    }
    .thumb-house-card.active {
      background: linear-gradient(180deg, #0F3A47 0%, #0A2630 100%);
      border-color: #10B981;
      box-shadow: 0 0 14px rgba(16, 185, 129, 0.55), inset 0 1px 2px rgba(255,255,255,0.2);
    }
    .thumb-house-card.multi-selected {
      background: linear-gradient(180deg, #422F15 0%, #2A1D0B 100%) !important;
      border-color: #F59E0B !important;
      box-shadow: 0 0 14px rgba(245, 158, 11, 0.6) !important;
    }

    .thumb-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 13.5px;
      font-weight: 900;
      color: #FFFFFF;
      border-bottom: 1px solid rgba(255,255,255,0.08);
      padding-bottom: 4px;
    }
    .thumb-checkbox {
      display: none;
      width: 16px;
      height: 16px;
      accent-color: #F59E0B;
      cursor: pointer;
    }
    .multi-mode .thumb-checkbox { display: inline-block; }

    .thumb-stage-canvas {
      flex: 1;
      width: 100%;
      min-height: 0;
      margin: 2px 0;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    /* ========================================================
       🎛️ [우측 영역] 3열(3개씩) 콤팩트 제어 그리드 & 최하단 유틸 바
       ======================================================== */
    .control-deck-pane {
      display: flex;
      flex-direction: column;
      gap: 8px;
      min-width: 0;
      height: 100%;
    }

    .action-tile-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      grid-auto-rows: 1fr;
      gap: 8px;
      flex: 1;
      min-height: 0;
      align-content: stretch;
    }

    /* 🔲 3D 입체형 정사각형 스위치 버튼 (기본 OFF 상태: 메탈릭 실버 베젤 & 슬레이트 그라데이션) */
    .ctrl-tile {
      aspect-ratio: 1 / 1;
      width: 100%;
      height: auto;
      max-height: 100%;
      background: linear-gradient(180deg, #F8FAFC 0%, #E2E8F0 48%, #CBD5E1 100%);
      border: 2px solid #5A6982;
      border-radius: 16px;
      padding: 6px 4px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      cursor: pointer;
      position: relative;
      overflow: hidden;
      transition: all 0.16s cubic-bezier(0.16, 1, 0.3, 1);
      user-select: none;
      box-shadow: 
        0 6px 14px rgba(0, 0, 0, 0.45),
        0 2px 4px rgba(0, 0, 0, 0.3),
        inset 0 2px 3px rgba(255, 255, 255, 0.9),
        inset 0 -3px 5px rgba(15, 23, 42, 0.35);
    }
    .ctrl-tile:hover {
      transform: translateY(-2px);
      box-shadow: 
        0 8px 18px rgba(0, 0, 0, 0.5),
        0 3px 6px rgba(0, 0, 0, 0.35),
        inset 0 2px 3px rgba(255, 255, 255, 1),
        inset 0 -3px 5px rgba(15, 23, 42, 0.4);
      border-color: #94A3B8;
    }
    .ctrl-tile:active {
      transform: translateY(2px) scale(0.96);
      box-shadow: 
        0 2px 5px rgba(0, 0, 0, 0.5),
        inset 0 3px 6px rgba(0, 0, 0, 0.4);
    }

    .ctrl-tile .tile-icon {
      font-size: 22px;
      transition: transform 0.2s;
      filter: drop-shadow(0 1px 2px rgba(0, 0, 0, 0.25));
    }
    .ctrl-tile .tile-name {
      font-size: 13px;
      font-weight: 900;
      color: #0F172A;
      text-align: center;
      white-space: nowrap;
      letter-spacing: -0.3px;
      text-shadow: 0 1px 1px rgba(255, 255, 255, 0.9);
    }
    .ctrl-tile .tile-state {
      font-size: 9.5px;
      font-weight: 800;
      padding: 1.5px 6px;
      border-radius: 5px;
      background: rgba(15, 23, 42, 0.12);
      color: #334155;
      white-space: nowrap;
      border: 1px solid rgba(15, 23, 42, 0.15);
    }

    /* 💖 3D 입체형 활성화(ON) 상태: 핑크/코랄 메탈릭 베젤 & 중앙 소프트 글로우 */
    .ctrl-tile.active {
      border: 2.5px solid #F43F5E;
      background: radial-gradient(circle at 50% 45%, #FFFFFF 0%, #FFE4E6 35%, #FECDD3 65%, #FDA4AF 100%);
      box-shadow: 
        0 6px 18px rgba(244, 63, 94, 0.5),
        0 2px 4px rgba(0, 0, 0, 0.25),
        inset 0 2px 4px rgba(255, 255, 255, 1),
        inset 0 -3px 6px rgba(225, 29, 72, 0.35);
    }
    .ctrl-tile.active:hover {
      box-shadow: 
        0 8px 22px rgba(244, 63, 94, 0.65),
        0 3px 6px rgba(0, 0, 0, 0.3),
        inset 0 2px 4px rgba(255, 255, 255, 1),
        inset 0 -3px 6px rgba(225, 29, 72, 0.4);
      border-color: #FB7185;
    }
    .ctrl-tile.active .tile-name {
      color: #881337;
      text-shadow: 0 1px 2px rgba(255, 255, 255, 0.8), 0 0 12px rgba(244, 63, 94, 0.35);
    }
    .ctrl-tile.active .tile-state {
      background: rgba(136, 19, 55, 0.16);
      color: #881337;
      border-color: rgba(136, 19, 55, 0.3);
      font-weight: 900;
    }

    /* 🔲 미지정 빈 슬롯 */
    .ctrl-tile.tile-empty {
      background: rgba(21, 34, 56, 0.5);
      border: 2px dashed rgba(255, 255, 255, 0.25);
      box-shadow: inset 0 2px 6px rgba(0, 0, 0, 0.3);
    }
    .ctrl-tile.tile-empty:hover {
      border-color: #38BDF8;
      background: rgba(30, 45, 74, 0.7);
    }
    .ctrl-tile.tile-empty .tile-icon { font-size: 18px; color: var(--text-muted); }
    .ctrl-tile.tile-empty .tile-name { font-size: 11px; color: var(--text-muted); text-shadow: none; font-weight: 700; }
    .ctrl-tile.tile-empty .tile-state { font-size: 8.5px; background: transparent; border: none; }

    /* 📱 최하단 유틸 바 */
    .deck-bottom-util-bar {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 8px;
      min-height: 40px;
    }
    .btn-util {
      background: linear-gradient(180deg, #243556 0%, #17243C 100%);
      border: 1.5px solid rgba(255, 255, 255, 0.2);
      color: #FFFFFF;
      border-radius: 10px;
      padding: 8px 10px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 6px;
      transition: all 0.16s ease;
      box-shadow: 0 4px 10px rgba(0,0,0,0.3), inset 0 1px 2px rgba(255,255,255,0.25);
    }
    .btn-util:hover {
      background: linear-gradient(180deg, #2E436D 0%, #1E2E4E 100%);
      border-color: #38BDF8;
      transform: translateY(-1px);
    }
    .btn-util:active {
      transform: translateY(1px) scale(0.97);
      box-shadow: 0 1px 3px rgba(0,0,0,0.4), inset 0 2px 4px rgba(0,0,0,0.3);
    }
    .btn-util.active {
      background: linear-gradient(180deg, #0EA5E9 0%, #0284C7 100%);
      border-color: #7DD3FC;
      box-shadow: 0 4px 12px rgba(14, 165, 233, 0.4), inset 0 1px 2px rgba(255,255,255,0.4);
    }

    /* 💨 송풍기 CSS 360도 회전 애니메이션 (SVG 완벽 지원) */
    @keyframes fanSpinAnim {
      0% { transform: rotate(0deg); }
      100% { transform: rotate(360deg); }
    }
    .fan-rotating {
      transform-box: fill-box;
      transform-origin: 50% 50%;
      animation: fanSpinAnim 0.45s linear infinite !important;
    }

    /* 💧 물방울 애니메이션 */
    @keyframes dropPulse {
      0%, 100% { transform: translateY(0); opacity: 0.9; }
      50% { transform: translateY(3px); opacity: 0.2; }
    }
    .water-drop-anim {
      animation: dropPulse 0.7s ease-in-out infinite;
    }

    /* 📱 반응형 */
    @media (max-width: 1024px) {
      .dashboard-split-layout { grid-template-columns: 1fr; height: auto; }
      .greenhouse-svg-wrapper { min-height: 240px; }
    }

    /* 📂 모달 팝업 스타일 */
    .modal-backdrop {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(8px);
      z-index: 1000;
      display: flex;
      align-items: center;
      justify-content: center;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s;
    }
    .modal-backdrop.active { opacity: 1; pointer-events: auto; }
    .modal-box {
      background: #111C30;
      border: 1px solid var(--border-bright);
      border-radius: 14px;
      width: 92%;
      max-width: 480px;
      max-height: 90vh;
      overflow-y: auto;
      padding: 18px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
    .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 8px; }
    .modal-title { font-size: 15px; font-weight: 800; color: #FFFFFF; }
    .btn-close-modal { background: none; border: none; color: #94A3B8; font-size: 18px; cursor: pointer; }
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-label { font-size: 11px; font-weight: 700; color: var(--text-secondary); }
    .form-input, .form-select {
      background: #0B132B;
      border: 1px solid var(--border);
      color: #FFFFFF;
      padding: 8px 10px;
      border-radius: 6px;
      font-size: 12px;
    }
    .btn-submit {
      background: #10B981;
      border: none;
      color: #FFFFFF;
      padding: 10px;
      border-radius: 6px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
    }
  </style>
</head>
<body class="weather-night logged-in" id="main-body">

  <!-- 🌾 전체 단일 뷰포트 메인 컨테이너 (100dvh 완전 밀착) -->
  <main>
    <!-- 🌟 [단일 통합 스마트 탑바] 로고 + 해상도인식 + 날씨/예보 + 계정 관리 일체화 (높이 38px) -->
    <div class="dashboard-unified-top-bar">
      <!-- 좌측: 로고 & 해상도 배지 -->
      <div class="top-bar-left">
        <span class="brand-logo">🍓</span>
        <span class="brand-name">누리오 스마트 팜</span>
        <div class="res-indicator-badge" id="res-mode-badge" title="현재 감지된 고정 해상도 규격">
          <span>🖥️</span><span id="res-mode-text">WUXGA (1920×1200)</span>
        </div>
      </div>

      <!-- 중앙: 실시간 날씨 & 주간 예보 슬라이더 -->
      <div class="top-bar-center">
        <div class="weather-live-info">
          <span id="weather-icon-live">🌙</span>
          <span id="weather-text-live">충남 논산 · 맑은 밤 24.6°C / 95%</span>
          <button class="region-select-badge" onclick="openRegionModal()" title="지역 변경">
            <span>📍</span><span id="region-current-name">충남 논산</span>
          </button>
        </div>
        <div class="forecast-slider" id="forecast-bar-items">
          <div class="forecast-item"><span>오늘 28°/19°</span><span>☀️ 맑음</span></div>
          <div class="forecast-item"><span>내일 29°/20°</span><span>⛅ 구름</span></div>
          <div class="forecast-item"><span>모레 27°/21°</span><span>🌧️ 비</span></div>
        </div>
      </div>

      <!-- 우측: 관리자 프로필 & 전체화면 & 설정/로그아웃 -->
      <div class="top-bar-right">
        <!-- ⛶ 전체화면 전환 버튼 (시스템 바 제거) -->
        <button class="btn-fullscreen-toggle" onclick="toggleFullscreen()" title="태블릿 상하단 바 숨기고 100% 전체화면 전환">
          <span id="fs-icon">⛶</span><span id="fs-text">전체화면</span>
        </button>

        <!-- 1) 로그인 상태 -->
        <div class="admin-elements" style="display:flex; align-items:center; gap:6px;">
          <div class="admin-badge">
            <div class="admin-avatar" id="header-avatar">G</div>
            <span><strong id="header-user-name">이승호</strong></span>
            <span class="google-pill">관리자</span>
          </div>

          <button class="btn-header-setting" onclick="openGlobalSettingModal()" title="통합 설정">
            <span>⚙️</span><span>설정</span>
          </button>

          <button class="btn-logout" onclick="handleGoogleLogout()" title="로그아웃">
            <span>🚪</span><span>로그아웃</span>
          </button>
        </div>

        <!-- 2) 로그아웃 상태 -->
        <div class="guest-elements" style="display:none; align-items:center; gap:6px;">
          <span style="font-size:11px; color:var(--text-muted); font-weight:700;">👀 모니터링 전용</span>
          <button class="btn-google-login" onclick="openGoogleLoginModal()">
            <span>🔑</span><span>로그인</span>
          </button>
        </div>
      </div>
    </div>

    <!-- 🌾 분할 관제 레이아웃 (좌측: 대형 비닐하우스+섬네일 | 우측: 3D 컨트롤 데크) -->
    <div class="dashboard-split-layout">
      
      <!-- ========================================================
           🍓 [좌측 영역] 대형 메인 뷰 + 섬네일 하우스 일체형 관제 카드
           ======================================================== -->
      <div class="visual-twin-pane">

        <!-- 🏠 [일체형 메인 관제 카드] 상단 대형 뷰 + 하단 섬네일 덱 통합 -->
        <div class="twin-stage-card">
          
          <div class="stage-header-bar">
            <div class="house-title-tag">
              <span>🏢</span>
              <span id="twin-house-title">1동 하우스</span>
              <span class="crop-pill" id="twin-crop-badge">딸기 (설향)</span>
              <span class="multi-status-pill" id="twin-multi-badge">✨ 다중 선택 모드</span>
            </div>
            <span style="font-size:10px; color:#38BDF8; font-weight:700;">📡 실시간 투야 센서 연동</span>
          </div>

          <!-- SVG 대형 비닐하우스 캔버스 (여백 없이 콤팩트 배치) -->
          <div class="greenhouse-svg-wrapper" id="greenhouse-stage">
            
            <!-- 🌡️ 실내 온·습도 HUD 위젯 -->
            <div class="sensor-hud-center">
              <div class="hud-stat-box">
                <span class="hud-label">🌡️ 실내온도</span>
                <span class="hud-value-temp" id="val-twin-temp">24.5°C</span>
              </div>
              <div style="width:1px; height:20px; background:rgba(255,255,255,0.2);"></div>
              <div class="hud-stat-box">
                <span class="hud-label">💧 실내습도</span>
                <span class="hud-value-hum" id="val-twin-hum">62%</span>
              </div>
              <div style="width:1px; height:20px; background:rgba(255,255,255,0.2);"></div>
              <div class="hud-stat-box">
                <span class="hud-label">🍓 생육 상태</span>
                <span style="font-size:11px; font-weight:800; color:#FDE047;" id="val-twin-comfort">😊 최적</span>
              </div>
            </div>

            <!-- ☀️ 실시간 실외 날씨 효과 배지 -->
            <div class="outdoor-weather-effect-box">
              <span id="outdoor-icon">☀️</span>
              <span id="outdoor-desc">실외: 맑음 26.2°C</span>
            </div>

            <!-- 정밀 SVG 비닐하우스 그래픽 (상하좌우 빈틈없이 100% 꽉 채우는 웅장한 뷰) -->
            <svg viewBox="0 0 800 520" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
              <defs>
                <linearGradient id="skyGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop id="skyStop1" offset="0%" stop-color="#142646" stop-opacity="0.85"/>
                  <stop id="skyStop2" offset="100%" stop-color="#080F1E" stop-opacity="0.98"/>
                </linearGradient>
                <linearGradient id="groundGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#334155"/>
                  <stop offset="100%" stop-color="#0F172A"/>
                </linearGradient>
              </defs>

              <rect x="0" y="0" width="800" height="520" rx="10" fill="url(#skyGrad)"/>
              
              <!-- 밤하늘 별 & 달 -->
              <g id="svg-sky-night" opacity="0.85">
                <circle cx="700" cy="70" r="18" fill="#FEF08A" filter="drop-shadow(0 0 10px #FDE047)"/>
                <circle cx="692" cy="64" r="16" fill="#142646"/>
                <circle cx="140" cy="80" r="2" fill="#FFFFFF"/>
                <circle cx="260" cy="60" r="2.5" fill="#FFFFFF"/>
                <circle cx="480" cy="75" r="2" fill="#FFFFFF"/>
                <circle cx="600" cy="110" r="1.5" fill="#FFFFFF"/>
              </g>

              <!-- 낮 태양 -->
              <g id="svg-sky-day" opacity="0" style="display:none;">
                <circle cx="680" cy="80" r="26" fill="#F59E0B" filter="drop-shadow(0 0 18px #FBBF24)"/>
              </g>

              <!-- 흙 / 지면 바닥 -->
              <path d="M 0 460 L 800 460 L 800 520 L 0 520 Z" fill="url(#groundGrad)"/>
              
              <!-- 🏗️ 웅장한 대형 비닐하우스 아치 트러스 프레임 -->
              <path d="M 60 460 L 60 220 Q 60 30, 400 20 Q 740 30, 740 220 L 740 460" 
                    fill="none" stroke="#64748B" stroke-width="8" stroke-linecap="round"/>
              <path d="M 85 460 L 85 220 Q 85 55, 400 45 Q 715 55, 715 220 L 715 460" 
                    fill="none" stroke="#475569" stroke-width="4"/>

              <!-- 보강 가로/세로 트러스 빔 -->
              <line x1="160" y1="160" x2="640" y2="160" stroke="#475569" stroke-width="3" stroke-dasharray="6,5"/>
              <line x1="400" y1="20" x2="400" y2="160" stroke="#475569" stroke-width="3"/>

              <!-- 💨 천정 중앙 대형 송풍기 (유동팬) -->
              <g id="svg-center-fan-unit" transform="translate(400, 125)">
                <line x1="0" y1="-65" x2="0" y2="-40" stroke="#CBD5E1" stroke-width="6"/>
                <circle cx="0" cy="0" r="44" fill="#0F172A" stroke="#38BDF8" stroke-width="3.5"/>
                
                <!-- 4엽 회전 팬 날개 -->
                <g id="svg-fan-blades">
                  <path d="M 0 0 C -8 -15 -15 -24 0 -34 C 15 -24 8 -15 0 0 Z" fill="#00E5FF"/>
                  <path d="M 0 0 C 15 -8 24 -15 34 0 C 24 15 15 8 0 0 Z" fill="#00E5FF"/>
                  <path d="M 0 0 C 8 15 15 24 0 34 C -15 24 -8 15 0 0 Z" fill="#00E5FF"/>
                  <path d="M 0 0 C -15 8 -24 15 -34 0 C -24 -15 -15 -8 0 0 Z" fill="#00E5FF"/>
                  <circle cx="0" cy="0" r="9" fill="#1E293B" stroke="#38BDF8" stroke-width="2.5"/>
                </g>
                <text x="0" y="60" text-anchor="middle" fill="#FFFFFF" font-size="12" font-weight="900">💨 환풍 유동팬</text>
              </g>

              <!-- 📡 온·습도 센서 노드 -->
              <g transform="translate(580, 220)">
                <line x1="0" y1="-60" x2="0" y2="0" stroke="#94A3B8" stroke-width="2.5"/>
                <rect x="-14" y="0" width="28" height="38" rx="5" fill="#F8FAFC" stroke="#06B6D4" stroke-width="2.5"/>
                <circle cx="0" cy="10" r="3" fill="#10B981"/>
                <text x="0" y="52" text-anchor="middle" fill="#BAE6FD" font-size="11" font-weight="900">📡 센서</text>
              </g>

              <!-- 🍓 딸기 고설 재배 베드 & 관수 파이프라인 -->
              <line x1="180" y1="460" x2="180" y2="350" stroke="#64748B" stroke-width="5"/>
              <line x1="320" y1="460" x2="320" y2="350" stroke="#64748B" stroke-width="5"/>
              <line x1="480" y1="460" x2="480" y2="350" stroke="#64748B" stroke-width="5"/>
              <line x1="620" y1="460" x2="620" y2="350" stroke="#64748B" stroke-width="5"/>
              
              <rect x="130" y="335" width="540" height="24" rx="6" fill="#1E293B" stroke="#059669" stroke-width="2.5"/>

              <!-- 💧 물방울 파티클 (양수기 가동 시 노출) -->
              <g id="svg-water-drops" style="display:none;">
                <circle class="water-drop-anim" cx="190" cy="360" r="4" fill="#38BDF8"/>
                <circle class="water-drop-anim" cx="270" cy="360" r="4" fill="#38BDF8"/>
                <circle class="water-drop-anim" cx="350" cy="360" r="4" fill="#38BDF8"/>
                <circle class="water-drop-anim" cx="450" cy="360" r="4" fill="#38BDF8"/>
                <circle class="water-drop-anim" cx="530" cy="360" r="4" fill="#38BDF8"/>
                <circle class="water-drop-anim" cx="610" cy="360" r="4" fill="#38BDF8"/>
              </g>

              <!-- 풍성한 딸기 & 모종 일러스트 -->
              <g transform="translate(150, 290)">
                <text x="20" y="38" font-size="28">🍓</text>
                <text x="80" y="38" font-size="28">🌱</text>
                <text x="140" y="38" font-size="28">🍓</text>
                <text x="200" y="38" font-size="28">🌱</text>
                <text x="260" y="38" font-size="28">🍓</text>
                <text x="320" y="38" font-size="28">🌱</text>
                <text x="380" y="38" font-size="28">🍓</text>
                <text x="440" y="38" font-size="28">🌱</text>
                <text x="490" y="38" font-size="28">🍓</text>
              </g>
            </svg>
          </div>

          <!-- 🏡 [메인 카드 하단 일체형] 정사각형 슬라이딩 섬네일 하우스 덱 -->
          <div class="thumbnail-houses-deck-box">
            <div class="thumb-section-header">
              <div class="thumb-section-title">
                <span>🌾</span><span>섬네일 하우스 관제 덱 (터치 시 메인 뷰 전환 / 가로 슬라이딩)</span>
              </div>
              <button class="btn-util admin-elements" id="btn-toggle-multimode" onclick="toggleMultiSelectMode()" style="padding:2px 6px; font-size:10px; min-height:auto;">
                <span id="multi-icon">☑️</span><span>다중 선택</span>
              </button>
            </div>

            <!-- 가로 슬라이딩 섬네일 하우스 카드 리스트 -->
            <div class="thumbnail-houses-slider" id="thumbnail-houses-container">
              <!-- JS 동적 렌더링 -->
            </div>
          </div>

        </div>

      </div>

      <!-- ========================================================
           🎛️ [우측 영역] 3열(3개씩) 콤팩트 원터치 제어 그리드 & 최하단 유틸 바
           (관리자 로그인 시에만 노출)
           ======================================================== -->
      <div class="control-deck-pane">
        
        <!-- 3열 x 4행 (총 12개 슬롯) 원터치 제어 그리드 -->
        <div class="action-tile-grid" id="action-tiles-container">
          
          <!-- 1행: 열기 / 닫기 / 정지 -->
          <div class="ctrl-tile tile-open" id="tile-m-open" onclick="handleUnifiedControl('OPEN')">
            <span class="tile-icon">🔼</span>
            <span class="tile-name">열 기</span>
            <span class="tile-state" id="state-lbl-open">개폐기 전개</span>
          </div>

          <div class="ctrl-tile tile-close" id="tile-m-close" onclick="handleUnifiedControl('CLOSE')">
            <span class="tile-icon">🔽</span>
            <span class="tile-name">닫 기</span>
            <span class="tile-state" id="state-lbl-close">개폐기 밀폐</span>
          </div>

          <div class="ctrl-tile tile-stop" id="tile-m-stop" onclick="handleUnifiedControl('STOP')">
            <span class="tile-icon">⏸️</span>
            <span class="tile-name">정 지</span>
            <span class="tile-state">모터 정지</span>
          </div>

          <!-- 2행: 양수기 / 송풍기 / 양액기 -->
          <div class="ctrl-tile tile-water" id="tile-pump-water" onclick="handleUnifiedControl('WATER')">
            <span class="tile-icon">💧</span>
            <span class="tile-name">양수기</span>
            <span class="tile-state" id="state-lbl-water">정지 (OFF)</span>
          </div>

          <div class="ctrl-tile tile-fan" id="tile-aux-fan" onclick="handleUnifiedControl('FAN')">
            <span class="tile-icon">💨</span>
            <span class="tile-name">송풍기</span>
            <span class="tile-state" id="state-lbl-fan">정지 (OFF)</span>
          </div>

          <div class="ctrl-tile tile-nutrient" id="tile-pump-nutrient" onclick="handleUnifiedControl('NUTRIENT')">
            <span class="tile-icon">🧪</span>
            <span class="tile-name">양액기</span>
            <span class="tile-state" id="state-lbl-nutrient">대기 (OFF)</span>
          </div>

          <!-- 3행: 연무기 / 미지정 슬롯 8 / 미지정 슬롯 9 -->
          <div class="ctrl-tile tile-mist" id="tile-aux-mist" onclick="handleUnifiedControl('MIST')">
            <span class="tile-icon">🌫️</span>
            <span class="tile-name">연무기</span>
            <span class="tile-state" id="state-lbl-mist">대기 (OFF)</span>
          </div>

          <div class="ctrl-tile tile-empty" id="tile-custom-slot8" onclick="openSlotConfigModal(8)">
            <span class="tile-icon">➕</span>
            <span class="tile-name" id="name-slot-8">슬롯 8</span>
            <span class="tile-state">미지정</span>
          </div>

          <div class="ctrl-tile tile-empty" id="tile-custom-slot9" onclick="openSlotConfigModal(9)">
            <span class="tile-icon">➕</span>
            <span class="tile-name" id="name-slot-9">슬롯 9</span>
            <span class="tile-state">미지정</span>
          </div>

          <!-- 4행: 미지정 슬롯 10 / 미지정 슬롯 11 / 미지정 슬롯 12 -->
          <div class="ctrl-tile tile-empty" id="tile-custom-slot10" onclick="openSlotConfigModal(10)">
            <span class="tile-icon">➕</span>
            <span class="tile-name" id="name-slot-10">슬롯 10</span>
            <span class="tile-state">미지정</span>
          </div>

          <div class="ctrl-tile tile-empty" id="tile-custom-slot11" onclick="openSlotConfigModal(11)">
            <span class="tile-icon">➕</span>
            <span class="tile-name" id="name-slot-11">슬롯 11</span>
            <span class="tile-state">미지정</span>
          </div>

          <div class="ctrl-tile tile-empty" id="tile-custom-slot12" onclick="openSlotConfigModal(12)">
            <span class="tile-icon">➕</span>
            <span class="tile-name" id="name-slot-12">슬롯 12</span>
            <span class="tile-state">미지정</span>
          </div>

        </div>

        <!-- 📱 최하단 유틸 바 -->
        <div class="deck-bottom-util-bar">
          <button class="btn-util active" id="btn-tablet-fit" onclick="toggleTabletFitMode()">
            <span>📱</span><span>태블릿 핏</span>
          </button>
          <button class="btn-util" onclick="openPwaGuideModal()">
            <span>📲</span><span>앱 설치</span>
          </button>
        </div>

      </div>
    </div>
  </main>

  <!-- ========================================================
       📂 모달 다이얼로그
       ======================================================== -->
  
  <!-- 🔑 0. Google 로그인 모달 -->
  <div class="modal-backdrop" id="google-login-modal">
    <div class="modal-box" style="text-align:center; max-width:400px;">
      <div class="modal-header">
        <div class="modal-title">🔑 Google 계정 관리자 로그인</div>
        <button class="btn-close-modal" onclick="closeModal('google-login-modal')">✕</button>
      </div>

      <div style="padding:16px 0; display:flex; flex-direction:column; align-items:center; gap:12px;">
        <div style="font-size:42px;">🍓</div>
        <div style="font-size:16px; font-weight:800; color:#FFFFFF;">누리오 스마트팜 농가 관리자</div>
        <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
          인가된 관리자 구글 계정으로 로그인하시면<br>온실 원격 제어 및 모든 설정 기능이 활성화됩니다.
        </p>

        <button class="btn-google-login" onclick="performGoogleLogin('이승호', 'leesh0409@gmail.com')" style="width:100%; justify-content:center; padding:12px; font-size:14px; margin-top:8px;">
          <span style="font-size:18px;">G</span>
          <span>Google 계정으로 계속하기 (이승호)</span>
        </button>
      </div>
    </div>
  </div>

  <!-- ⚙️ 1. 통합 설정 모달 -->
  <div class="modal-backdrop" id="global-setting-modal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">⚙️ 누리오 스마트팜 통합 설정</div>
        <button class="btn-close-modal" onclick="closeModal('global-setting-modal')">✕</button>
      </div>

      <div style="background:rgba(255,255,255,0.05); padding:10px; border-radius:8px; display:flex; align-items:center; gap:10px;">
        <div class="admin-avatar" style="width:28px; height:28px; font-size:12px;">G</div>
        <div>
          <div style="font-weight:800; font-size:13px;">관리자 : <span id="modal-user-name">이승호</span></div>
          <div style="font-size:10px; color:var(--text-muted);">Google 인증 계정 연동됨</div>
        </div>
      </div>

      <div style="display:flex; flex-direction:column; gap:6px;">
        <button class="btn-util" onclick="closeModal('global-setting-modal'); openHouseModal(0);" style="justify-content:flex-start;">
          <span>🏗️</span><span>비닐하우스(동) 추가 및 수정</span>
        </button>
        <button class="btn-util" onclick="closeModal('global-setting-modal'); openRegionModal();" style="justify-content:flex-start;">
          <span>📍</span><span>농가 지역 날씨 설정 (충남 논산 등)</span>
        </button>
        <button class="btn-util" onclick="closeModal('global-setting-modal'); openSlotConfigModal(8);" style="justify-content:flex-start;">
          <span>🎛️</span><span>3열 제어 그리드 미지정 슬롯 커스텀 매핑</span>
        </button>
        <button class="btn-util" onclick="closeModal('global-setting-modal'); openInterlockModal();" style="justify-content:flex-start;">
          <span>🔒</span><span>4채널 하드웨어 인터락 안전 보호 설정</span>
        </button>
      </div>

      <button class="btn-submit" onclick="closeModal('global-setting-modal')">설정 완료</button>
    </div>
  </div>

  <!-- 🏗️ 2. 하우스 추가/편집 모달 -->
  <div class="modal-backdrop" id="house-modal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title" id="house-modal-title">🏗️ 비닐하우스 관리</div>
        <button class="btn-close-modal" onclick="closeModal('house-modal')">✕</button>
      </div>
      <input type="hidden" id="h-form-id" value="0">
      <div class="form-group">
        <label class="form-label">하우스 명칭 (예: 하우스 1동, 2동)</label>
        <input type="text" id="h-form-name" class="form-input" placeholder="하우스 명칭 입력">
      </div>
      <div class="form-group">
        <label class="form-label">재배 작물</label>
        <input type="text" id="h-form-crop" class="form-input" placeholder="예: 딸기 (설향)">
      </div>
      <button class="btn-submit" onclick="saveHouseSubmit()">하우스 저장</button>
    </div>
  </div>

  <!-- 📍 3. 농가 지역 날씨 설정 모달 -->
  <div class="modal-backdrop" id="region-modal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">📍 농가 지역 날씨 설정</div>
        <button class="btn-close-modal" onclick="closeModal('region-modal')">✕</button>
      </div>
      <div class="form-group">
        <label class="form-label">기상청 관측소 지역 선택</label>
        <select id="region-selector" class="form-select" onchange="changeFarmRegion(this.value)">
          <option value="nonsan" selected>충남 논산 (딸기 특화 농가)</option>
          <option value="damyang">전남 담양 (시설 원예 특화)</option>
          <option value="miryang">경남 밀양 (스마트팜 혁신밸리)</option>
          <option value="chungju">충북 충주 (과수/원예단지)</option>
          <option value="buyeo">충남 부여 (수박/방울토마토)</option>
        </select>
      </div>
      <button class="btn-submit" onclick="closeModal('region-modal')">확인</button>
    </div>
  </div>

  <!-- 🎛️ 4. 빈 슬롯 커스텀 장비 지정 모달 -->
  <div class="modal-backdrop" id="slot-modal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title" id="slot-modal-title">🎛️ 커스텀 제어 슬롯 지정</div>
        <button class="btn-close-modal" onclick="closeModal('slot-modal')">✕</button>
      </div>
      <input type="hidden" id="slot-id-hidden" value="8">
      <div class="form-group">
        <label class="form-label">장비 명칭</label>
        <input type="text" id="slot-form-name" class="form-input" placeholder="예: 열풍기, 보광등, CO2 발생기">
      </div>
      <div class="form-group">
        <label class="form-label">아이콘</label>
        <select id="slot-form-icon" class="form-select">
          <option value="🔥">🔥 열풍기 (난방기)</option>
          <option value="💡">💡 LED 보광등</option>
          <option value="🌫️">🌫️ 안개 분무기</option>
          <option value="🪴">🪴 자동 화분 급수</option>
          <option value="⚡">⚡ 스마트 전원 스위치</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">연동할 투야 하드웨어 포트</label>
        <select id="slot-form-binding" class="form-select">
          <option value="eb654aa2437462ea40dfjw:1">4채널 스위치 - 1번 포트 (측창열기)</option>
          <option value="eb654aa2437462ea40dfjw:2">4채널 스위치 - 2번 포트 (측창닫기)</option>
          <option value="eb654aa2437462ea40dfjw:3">4채널 스위치 - 3번 포트 (양수기)</option>
          <option value="eb654aa2437462ea40dfjw:4">4채널 스위치 - 4번 포트 (양액기)</option>
          <option value="ebb219afdebea03ba3shlz:1">스마트 플러그 #1 (환풍기/책상등)</option>
          <option value="42362638a4e57cb3cd0b:1">스마트 플러그 #2 (연무기/3D프린터)</option>
        </select>
      </div>
      <button class="btn-submit" onclick="saveSlotSubmit()">슬롯에 장비 등록</button>
    </div>
  </div>

  <!-- 🔒 5. 인터락 안내 모달 -->
  <div class="modal-backdrop" id="interlock-modal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">🔒 Tuya 4채널 개폐기 내장 인터락 상태</div>
        <button class="btn-close-modal" onclick="closeModal('interlock-modal')">✕</button>
      </div>
      <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
        Tuya 4채널 하드웨어 자체에 <strong>[1-2번 인터락 (1동 개폐)]</strong> 및 <strong>[3-4번 인터락 (2동 개폐)]</strong> 기능이 영구 내장되어 있어 모터의 정역회전 쇼트를 완벽하게 방지합니다. 별도 설정 없이 안전하게 자동 보호됩니다.
      </p>
      <div style="background:rgba(16,185,129,0.15); border:1px solid #10B981; padding:10px; border-radius:8px; font-size:12px; font-weight:700; color:#6EE7B7;">
        ⚡ 1동 (1-2번 채널) / 2동 (3-4번 채널) 하드웨어 자동 보호 작동중
      </div>
      <button class="btn-submit" onclick="closeModal('interlock-modal')">확인</button>
    </div>
  </div>

  <!-- 📲 6. PWA 앱 설치 가이드 모달 -->
  <div class="modal-backdrop" id="pwa-modal">
    <div class="modal-box">
      <div class="modal-header">
        <div class="modal-title">📲 태블릿PC 홈 화면 바로가기 추가</div>
        <button class="btn-close-modal" onclick="closeModal('pwa-modal')">✕</button>
      </div>
      <p style="font-size:12px; color:var(--text-secondary); line-height:1.6;">
        태블릿PC 브라우저(Chrome/Samsung Internet) 상단 메뉴에서 <strong>[홈 화면에 추가]</strong> 또는 <strong>[앱 설치]</strong>를 누르시면 전체화면 전용 앱으로 편리하게 사용하실 수 있습니다.
      </p>
      <button class="btn-submit" onclick="closeModal('pwa-modal')">확인</button>
    </div>
  </div>

  <!-- ========================================================
       🚀 JavaScript 통합 관제 & Tuya 실시간 양방향 제어 엔진
       ======================================================== -->
  <script>
    // Tuya 실제 물리 기기 ID (농가 현장 설치 장비 3종 매핑)
    const DEVICE_ID_PUMP = 'ebb219afdebea03ba3shlz'; // 양수기 (Smart Plug)
    const DEVICE_ID_FAN  = '42362638a4e57cb3cd0b'; // 송풍기 (smart plug)
    const DEVICE_ID_4CH  = 'eb654aa2437462ea40dfjw'; // 1동 개폐기 (4-433 4채널 멀티 스위치)

    let currentHouseId = 1;
    let isMultiSelectMode = false;
    let selectedHouseIds = [1];

    // 🌾 서버 DB에서 직접 로드된 실제 비닐하우스 목록 (깜빡임 완전 방지)
    let farmHouses = <?php echo $initHousesJson; ?>;

    let houseSensorData = {
      1: { temp: 24.5, hum: 62.0 },
      2: { temp: 23.8, hum: 65.0 }
    };

    // 실시간 상태 관리
    let states4ch = { 1: false, 2: false, 3: false, 4: false };
    let statePump = false; // 양수기 (Smart Plug)
    let stateFan  = false; // 송풍기 (Smart Plug)

    // --- 🖥️ WUXGA (1920x1200) / WQXGA (2560x1600) 자동 감지 및 16:10 고정 모드 ---
    function detectAndApplyResolutionMode() {
      const sw = window.screen.width * (window.devicePixelRatio || 1);
      const ww = window.innerWidth;
      const isWQXGA = (sw >= 2200 || ww >= 2200);

      const body = document.body;
      const textEl = document.getElementById('res-mode-text');

      if (isWQXGA) {
        body.classList.add('res-wqxga');
        body.classList.remove('res-wuxga');
        if (textEl) textEl.innerText = 'WQXGA (2560×1600)';
      } else {
        body.classList.add('res-wuxga');
        body.classList.remove('res-wqxga');
        if (textEl) textEl.innerText = 'WUXGA (1920×1200)';
      }
    }
    window.addEventListener('resize', detectAndApplyResolutionMode);

    // --- ⛶ 전체화면 전환 (태블릿 상하단 시스템 바 100% 숨김 및 복원) ---
    // --- ⛶ 전체화면 전환 및 PWA 앱 실행 시 자동 전체화면 기본 모드 ---
    function isPwaAppMode() {
      return window.matchMedia('(display-mode: standalone)').matches ||
             window.matchMedia('(display-mode: fullscreen)').matches ||
             window.navigator.standalone === true ||
             window.location.search.includes('mode=pwa');
    }

    function toggleFullscreen(isManual = true) {
      if (!document.fullscreenElement && !document.webkitFullscreenElement) {
        const docEl = document.documentElement;
        const requestFS = docEl.requestFullscreen || docEl.webkitRequestFullscreen || docEl.mozRequestFullScreen || docEl.msRequestFullscreen;
        if (requestFS) {
          requestFS.call(docEl).then(() => {
            localStorage.setItem('nurio_auto_fs', '1');
          }).catch(err => {
            console.log('Fullscreen request ignored or blocked:', err);
          });
        }
      } else if (isManual) {
        const exitFS = document.exitFullscreen || document.webkitExitFullscreen || document.mozCancelFullScreen || document.msExitFullscreen;
        if (exitFS) {
          exitFS.call(document);
          localStorage.setItem('nurio_auto_fs', '0');
        }
      }
    }

    // PWA 앱으로 실행 시 첫 터치 즉시 자동으로 전체화면 기본 모드 진입
    function initAutoFullscreenOnPwa() {
      const isPwa = isPwaAppMode();
      const savedAutoFs = localStorage.getItem('nurio_auto_fs');
      
      if (isPwa || savedAutoFs === '1') {
        const autoFsHandler = () => {
          if (!document.fullscreenElement && !document.webkitFullscreenElement) {
            toggleFullscreen(false);
          }
          window.removeEventListener('click', autoFsHandler);
          window.removeEventListener('touchstart', autoFsHandler);
        };
        window.addEventListener('click', autoFsHandler, { once: true });
        window.addEventListener('touchstart', autoFsHandler, { once: true });
      }
    }

    document.addEventListener('fullscreenchange', updateFullscreenUI);
    document.addEventListener('webkitfullscreenchange', updateFullscreenUI);
    function updateFullscreenUI() {
      const isFS = !!(document.fullscreenElement || document.webkitFullscreenElement);
      const fsIcon = document.getElementById('fs-icon');
      const fsText = document.getElementById('fs-text');
      if (fsIcon && fsText) {
        fsIcon.innerText = isFS ? '🗗' : '⛶';
        fsText.innerText = isFS ? '창모드' : '전체화면';
      }
    }

    // --- 🔑 구글 로그인 상태 관리 ---
    function initAuthStatus() {
      const savedAuth = localStorage.getItem('nurio_google_auth');
      const isLoggedIn = (savedAuth !== 'false');
      setLoginUI(isLoggedIn);
    }

    function setLoginUI(isLoggedIn) {
      const body = document.getElementById('main-body');
      const adminElements = document.querySelectorAll('.admin-elements');
      const guestElements = document.querySelectorAll('.guest-elements');

      if (isLoggedIn) {
        body.classList.remove('logged-out');
        body.classList.add('logged-in');
        adminElements.forEach(el => el.style.display = 'flex');
        guestElements.forEach(el => el.style.display = 'none');
      } else {
        body.classList.remove('logged-in');
        body.classList.add('logged-out');
        adminElements.forEach(el => el.style.display = 'none');
        guestElements.forEach(el => el.style.display = 'flex');
      }
    }

    function openGoogleLoginModal() {
      document.getElementById('google-login-modal').classList.add('active');
    }

    function performGoogleLogin(name, email) {
      localStorage.setItem('nurio_google_auth', 'true');
      localStorage.setItem('nurio_user_name', name);
      document.getElementById('header-user-name').innerText = name;
      document.getElementById('modal-user-name').innerText = name;
      setLoginUI(true);
      closeModal('google-login-modal');
    }

    function handleGoogleLogout() {
      if (confirm('로그아웃 하시겠습니까?\n로그아웃 시 원격 제어 버튼이 숨겨지고 실시간 모니터링 전용 뷰로 전환됩니다.')) {
        localStorage.setItem('nurio_google_auth', 'false');
        setLoginUI(false);
      }
    }

    // --- 1. 다중 선택 모드 토글 ---
    function toggleMultiSelectMode() {
      isMultiSelectMode = !isMultiSelectMode;
      const btn = document.getElementById('btn-toggle-multimode');
      const container = document.getElementById('thumbnail-houses-container');
      const multiBadge = document.getElementById('twin-multi-badge');

      if (isMultiSelectMode) {
        btn.classList.add('active');
        btn.innerHTML = `<span>✨</span><span>선택 해제 (${selectedHouseIds.length}동)</span>`;
        container.classList.add('multi-mode');
        multiBadge.style.display = 'inline-block';
        multiBadge.innerText = `✨ ${selectedHouseIds.length}개동 동시 제어`;
      } else {
        btn.classList.remove('active');
        btn.innerHTML = `<span>☑️</span><span>다중 선택</span>`;
        container.classList.remove('multi-mode');
        multiBadge.style.display = 'none';
        selectedHouseIds = [currentHouseId];
      }
      renderThumbnailHouses();
    }

    // --- 2. 하단 슬라이딩 "섬네일 하우스" 덱 렌더링 (시원하게 확대 & 공백 제거) ---
    function renderThumbnailHouses() {
      const container = document.getElementById('thumbnail-houses-container');
      if (!container) return;

      let html = '';
      Object.keys(farmHouses).forEach(hId => {
        const id = parseInt(hId);
        const h = farmHouses[id];
        const isCurrent = (currentHouseId === id);
        const isMultiSelected = selectedHouseIds.includes(id);
        const sData = houseSensorData[id] || { temp: 24.0, hum: 60 };

        let cardClass = 'thumb-house-card';
        if (isMultiSelectMode && isMultiSelected) cardClass += ' multi-selected';
        else if (!isMultiSelectMode && isCurrent) cardClass += ' active';

        // 💨 송풍기 가동 상태
        const isFanOn = stateFan;
        // 💧 양수기 가동 상태
        const isPumpOn = statePump;

        // 🏠 1동 (채널 1, 2) / 2동 (채널 3, 4) 개폐기 상태
        const isOpen = (id === 1) ? states4ch[1] : (id === 2 ? states4ch[3] : false);
        const isClose = (id === 1) ? states4ch[2] : (id === 2 ? states4ch[4] : false);
        const statusText = isOpen ? '🔼 개폐기 열림' : (isClose ? '🔽 개폐기 닫힘' : '⏸️ 개폐기 정지');
        const statusColor = isOpen ? '#10B981' : (isClose ? '#F43F5E' : 'var(--text-muted)');

        html += `
          <div class="${cardClass}" onclick="handleThumbnailHouseClick(${id}, event)" title="${h.name} 메인 뷰로 전환">
            <div class="thumb-card-header">
              <span>🏢 ${h.name}</span>
              <input type="checkbox" class="thumb-checkbox" ${isMultiSelected ? 'checked' : ''} onclick="event.stopPropagation(); toggleHouseCheck(${id});">
            </div>

            <!-- 중앙: 정밀 미니 SVG 뷰 (큼직하고 선명한 뷰) -->
            <div class="thumb-stage-canvas">
              <svg viewBox="0 0 160 85" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
                <defs>
                  <linearGradient id="thumbSky${id}" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop offset="0%" stop-color="#182A4A"/>
                    <stop offset="100%" stop-color="#0B132B"/>
                  </linearGradient>
                </defs>

                <rect x="2" y="2" width="156" height="81" rx="8" fill="url(#thumbSky${id})"/>
                
                <!-- 아치 프레임 (열림/닫힘 색상 실시간 반영) -->
                <path d="M 16 76 L 16 38 Q 16 10, 80 8 Q 144 10, 144 38 L 144 76" 
                      fill="none" stroke="${isOpen ? '#10B981' : (isClose ? '#F43F5E' : '#64748B')}" stroke-width="2.2" stroke-linecap="round"/>

                <!-- 💨 좌측: 미니 송풍기 (환풍기 ⨁) -->
                <g transform="translate(38, 28)">
                  <circle cx="0" cy="0" r="16" fill="#0F172A" stroke="#38BDF8" stroke-width="1.5"/>
                  <g class="${isFanOn ? 'fan-rotating' : ''}">
                    <path d="M 0 0 C -4 -6 -6 -10 0 -13 C 6 -10 4 -6 0 0 Z" fill="#00E5FF"/>
                    <path d="M 0 0 C 6 -4 10 -6 13 0 C 10 6 6 4 0 0 Z" fill="#00E5FF"/>
                    <path d="M 0 0 C 4 6 6 10 0 13 C -6 10 -4 6 0 0 Z" fill="#00E5FF"/>
                    <path d="M 0 0 C -6 4 -10 6 -13 0 C -10 -6 -6 -4 0 0 Z" fill="#00E5FF"/>
                    <circle cx="0" cy="0" r="3.5" fill="#1E293B" stroke="#38BDF8" stroke-width="1"/>
                  </g>
                </g>

                <!-- 🌡️💧 우측: 실시간 온·습도 수치 -->
                <text x="75" y="25" fill="#34D399" font-size="11.5" font-weight="900">🌡️ ${sData.temp.toFixed(1)}°C</text>
                <text x="75" y="42" fill="#38BDF8" font-size="11.5" font-weight="900">💧 ${Math.round(sData.hum)}%</text>

                <!-- 🍓 하단: 작물 재배 베드 & 관수 라인 -->
                <line x1="20" y1="62" x2="140" y2="62" stroke="#059669" stroke-width="2.5" stroke-linecap="round"/>
                <line x1="40" y1="62" x2="40" y2="76" stroke="#475569" stroke-width="1.5"/>
                <line x1="120" y1="62" x2="120" y2="76" stroke="#475569" stroke-width="1.5"/>

                <text x="28" y="58" font-size="11">🍓</text>
                <text x="54" y="58" font-size="11">🌱</text>
                <text x="80" y="58" font-size="11">🍓</text>
                <text x="106" y="58" font-size="11">🌱</text>
                <text x="126" y="58" font-size="11">🍓</text>

                <!-- 💧 물방울 애니메이션 (양수기 켜질 때 표시) -->
                ${isPumpOn ? `
                  <g class="water-drop-anim">
                    <circle cx="34" cy="68" r="2" fill="#38BDF8"/>
                    <circle cx="60" cy="68" r="2" fill="#38BDF8"/>
                    <circle cx="86" cy="68" r="2" fill="#38BDF8"/>
                    <circle cx="112" cy="68" r="2" fill="#38BDF8"/>
                    <circle cx="132" cy="68" r="2" fill="#38BDF8"/>
                  </g>
                ` : ''}
              </svg>
            </div>

            <!-- 하단 바 -->
            <div style="display:flex; justify-content:space-between; align-items:center; font-size:11px; color:var(--text-muted); font-weight:800; padding:0 2px;">
              <span style="color:#CBD5E1;">${h.crop || '딸기 (설향)'}</span>
              <span style="color:${statusColor}; font-weight:900;">
                ${statusText}
              </span>
            </div>
          </div>
        `;
      });

      // ➕ 동 추가 카드
      html += `
        <div class="thumb-house-card admin-elements" style="border:2px dashed rgba(255,255,255,0.25); justify-content:center; align-items:center; gap:6px; min-width:110px; width:110px;" onclick="openHouseModal(0)">
          <span style="font-size:24px; color:#38BDF8;">➕</span>
          <span style="font-size:11px; font-weight:800; color:var(--text-muted);">동 추가</span>
        </div>
      `;

      container.innerHTML = html;
    }

    function handleThumbnailHouseClick(houseId, event) {
      if (isMultiSelectMode) {
        toggleHouseCheck(houseId);
      } else {
        currentHouseId = houseId;
        selectedHouseIds = [houseId];
        renderThumbnailHouses();
        updateCurrentHouseVisual();
        updateControlDeckUI();
      }
    }

    function toggleHouseCheck(houseId) {
      const idx = selectedHouseIds.indexOf(houseId);
      if (idx > -1) {
        if (selectedHouseIds.length > 1) selectedHouseIds.splice(idx, 1);
      } else {
        selectedHouseIds.push(houseId);
      }
      currentHouseId = selectedHouseIds[0];
      const multiBadge = document.getElementById('twin-multi-badge');
      if (multiBadge) multiBadge.innerText = `✨ ${selectedHouseIds.length}개동 동시 제어`;
      const btn = document.getElementById('btn-toggle-multimode');
      if (btn) btn.innerHTML = `<span>✨</span><span>선택 해제 (${selectedHouseIds.length}동)</span>`;
      renderThumbnailHouses();
      updateCurrentHouseVisual();
      updateControlDeckUI();
    }

    function updateCurrentHouseVisual() {
      const h = farmHouses[currentHouseId];
      if (h) {
        document.getElementById('twin-house-title').innerText = h.name;
        document.getElementById('twin-crop-badge').innerText = h.crop || '작물';
        const sData = houseSensorData[currentHouseId] || { temp: 24.5, hum: 62 };
        document.getElementById('val-twin-temp').innerText = `${sData.temp.toFixed(1)}°C`;
        document.getElementById('val-twin-hum').innerText = `${Math.round(sData.hum)}%`;
      }
    }

    // --- 3. 통합 제어 (Tuya 실제 명령 toggle_plug REST API 전송) ---
    async function handleUnifiedControl(actionType) {
      const targetHouses = isMultiSelectMode ? selectedHouseIds : [currentHouseId];
      const houseNames = targetHouses.map(id => farmHouses[id]?.name || `${id}동`).join(', ');

      console.log(`[통합 제어] ${houseNames} 대상 ${actionType} 실행 (1동:ch1/2, 2동:ch3/4)`);

      // 1) UI 낙관적 즉각 반영
      targetHouses.forEach(hId => {
        if (hId === 1) {
          if (actionType === 'OPEN') { states4ch[1] = true; states4ch[2] = false; }
          else if (actionType === 'CLOSE') { states4ch[1] = false; states4ch[2] = true; }
          else if (actionType === 'STOP') { states4ch[1] = false; states4ch[2] = false; }
        } else if (hId === 2) {
          if (actionType === 'OPEN') { states4ch[3] = true; states4ch[4] = false; }
          else if (actionType === 'CLOSE') { states4ch[3] = false; states4ch[4] = true; }
          else if (actionType === 'STOP') { states4ch[3] = false; states4ch[4] = false; }
        }
      });

      if (actionType === 'WATER') {
        statePump = !statePump; // 양수기 토글 (스마트 플러그: ebb219afdebea03ba3shlz)
      } else if (actionType === 'FAN') {
        stateFan = !stateFan;   // 송풍기 토글 (스마트 플러그: 42362638a4e57cb3cd0b)
      }

      updateControlDeckUI();
      renderThumbnailHouses();

      // 2) Tuya Cloud 실제 REST API 호출 (action=toggle_plug)
      try {
        if (actionType === 'OPEN' || actionType === 'CLOSE' || actionType === 'STOP') {
          for (const hId of targetHouses) {
            if (hId === 1) {
              if (actionType === 'OPEN') {
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 1, state: true })
                });
              } else if (actionType === 'CLOSE') {
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 2, state: true })
                });
              } else if (actionType === 'STOP') {
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 1, state: false })
                });
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 2, state: false })
                });
              }
            } else if (hId === 2) {
              if (actionType === 'OPEN') {
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 3, state: true })
                });
              } else if (actionType === 'CLOSE') {
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 4, state: true })
                });
              } else if (actionType === 'STOP') {
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 3, state: false })
                });
                await fetch('api.php?action=toggle_plug', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 4, state: false })
                });
              }
            }
          }
        } else if (actionType === 'WATER') {
          // 양수기: 스마트 플러그 (ebb219afdebea03ba3shlz)
          await fetch('api.php?action=toggle_plug', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: DEVICE_ID_PUMP, state: statePump })
          });
        } else if (actionType === 'FAN') {
          // 송풍기: 스마트 플러그 (42362638a4e57cb3cd0b)
          await fetch('api.php?action=toggle_plug', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: DEVICE_ID_FAN, state: stateFan })
          });
        }
      } catch(e) {
        console.error('Tuya 제어 전송 오류', e);
      }
    }

    function updateControlDeckUI() {
      // 1 & 2. 현재 선택된 동(1동: ch1/2, 2동: ch3/4)의 개폐기 상태 판별
      let isCurrentOpen = false;
      let isCurrentClose = false;

      if (isMultiSelectMode) {
        isCurrentOpen = (selectedHouseIds.includes(1) && states4ch[1]) || (selectedHouseIds.includes(2) && states4ch[3]);
        isCurrentClose = (selectedHouseIds.includes(1) && states4ch[2]) || (selectedHouseIds.includes(2) && states4ch[4]);
      } else {
        if (currentHouseId === 1) {
          isCurrentOpen = states4ch[1];
          isCurrentClose = states4ch[2];
        } else if (currentHouseId === 2) {
          isCurrentOpen = states4ch[3];
          isCurrentClose = states4ch[4];
        }
      }

      // 1. 열기
      const tileOpen = document.getElementById('tile-m-open');
      const lblOpen = document.getElementById('state-lbl-open');
      if (isCurrentOpen) {
        tileOpen.classList.add('active');
        lblOpen.innerText = '열림 (ON)';
      } else {
        tileOpen.classList.remove('active');
        lblOpen.innerText = '개폐기 전개';
      }

      // 2. 닫기
      const tileClose = document.getElementById('tile-m-close');
      const lblClose = document.getElementById('state-lbl-close');
      if (isCurrentClose) {
        tileClose.classList.add('active');
        lblClose.innerText = '닫힘 (ON)';
      } else {
        tileClose.classList.remove('active');
        lblClose.innerText = '개폐기 밀폐';
      }

      // 3. 양수기 (스마트 플러그 & SVG 물방울 애니메이션)
      const tileWater = document.getElementById('tile-pump-water');
      const lblWater = document.getElementById('state-lbl-water');
      const waterDrops = document.getElementById('svg-water-drops');
      if (statePump) {
        tileWater.classList.add('active');
        lblWater.innerText = '급수중 (ON)';
        if (waterDrops) waterDrops.style.display = 'inline';
      } else {
        tileWater.classList.remove('active');
        lblWater.innerText = '정지 (OFF)';
        if (waterDrops) waterDrops.style.display = 'none';
      }

      // 4. 송풍기 (스마트 플러그 & SVG 송풍기 회전 애니메이션)
      const tileFan = document.getElementById('tile-aux-fan');
      const lblFan = document.getElementById('state-lbl-fan');
      const mainFanBlades = document.getElementById('svg-fan-blades');
      if (stateFan) {
        tileFan.classList.add('active');
        lblFan.innerText = '회전중 (ON)';
        if (mainFanBlades) mainFanBlades.classList.add('fan-rotating');
      } else {
        tileFan.classList.remove('active');
        lblFan.innerText = '정지 (OFF)';
        if (mainFanBlades) mainFanBlades.classList.remove('fan-rotating');
      }
    }

    // --- 4. DB 및 Tuya 실시간 상태 동기화 (3초 폴링) ---
    async function syncStatusFromDb() {
      try {
        const res = await fetch('api.php?action=get_status');
        const data = await res.json();
        if (data && data.success) {
          if (data.houses && Object.keys(data.houses).length > 0) {
            farmHouses = data.houses;
          }

          if (data.devices) {
            // 양수기 스마트 플러그 (ebb219afdebea03ba3shlz)
            if (data.devices[DEVICE_ID_PUMP]) {
              statePump = !!data.devices[DEVICE_ID_PUMP].state;
            }
            // 송풍기 스마트 플러그 (42362638a4e57cb3cd0b)
            if (data.devices[DEVICE_ID_FAN]) {
              stateFan = !!data.devices[DEVICE_ID_FAN].state;
            }
            // 1동 개폐기 4채널 멀티 스위치 (eb654aa2437462ea40dfjw)
            if (data.devices[DEVICE_ID_4CH] && data.devices[DEVICE_ID_4CH].channels) {
              const chs = data.devices[DEVICE_ID_4CH].channels;
              states4ch[1] = !!(chs[1]?.state ?? chs[1]);
              states4ch[2] = !!(chs[2]?.state ?? chs[2]);
              states4ch[3] = !!(chs[3]?.state ?? chs[3]);
              states4ch[4] = !!(chs[4]?.state ?? chs[4]);
            }
          }

          // 투야 온습도 센서 실시간 수치 반영
          if (data.telemetry && data.telemetry.temperature !== undefined) {
            houseSensorData[1].temp = parseFloat(data.telemetry.temperature);
            if (data.telemetry.humidity !== undefined) {
              houseSensorData[1].hum = parseFloat(data.telemetry.humidity);
            }
          }

          updateControlDeckUI();
          renderThumbnailHouses();
          updateCurrentHouseVisual();
        }
      } catch(e) {
        console.error('상태 동기화 오류', e);
      }
    }

    // --- 5. 태블릿 핏 모드 토글 ---
    function toggleTabletFitMode() {
      const isFit = document.body.classList.toggle('tablet-fit-mode');
      const btn = document.getElementById('btn-tablet-fit');
      if (isFit) {
        btn.classList.add('active');
      } else {
        btn.classList.remove('active');
      }
      localStorage.setItem('nurio_tablet_fit', isFit ? '1' : '0');
    }

    // --- 6. 모달 컨트롤러 ---
    function openGlobalSettingModal() { document.getElementById('global-setting-modal').classList.add('active'); }
    function openHouseModal(id = 0) { document.getElementById('house-modal').classList.add('active'); }
    function openRegionModal() { document.getElementById('region-modal').classList.add('active'); }
    function openInterlockModal() { document.getElementById('interlock-modal').classList.add('active'); }
    function openPwaGuideModal() { document.getElementById('pwa-modal').classList.add('active'); }
    function openSlotConfigModal(slotId) {
      document.getElementById('slot-id-hidden').value = slotId;
      document.getElementById('slot-modal-title').innerText = `🎛️ 커스텀 슬롯 ${slotId} 지정`;
      document.getElementById('slot-modal').classList.add('active');
    }
    function closeModal(id) { document.getElementById(id).classList.remove('active'); }

    function saveSlotSubmit() {
      const slotId = document.getElementById('slot-id-hidden').value;
      const name = document.getElementById('slot-form-name').value.trim() || `슬롯 ${slotId}`;
      const icon = document.getElementById('slot-form-icon').value;
      
      const tile = document.getElementById(`tile-custom-slot${slotId}`);
      if (tile) {
        tile.classList.remove('tile-empty');
        tile.innerHTML = `
          <span class="tile-icon">${icon}</span>
          <span class="tile-name">${name}</span>
          <span class="tile-state">대기 (OFF)</span>
        `;
      }
      closeModal('slot-modal');
    }

    async function saveHouseSubmit() {
      const name = document.getElementById('h-form-name').value.trim();
      const crop = document.getElementById('h-form-crop').value.trim() || '작물';
      if (!name) return alert('명칭을 입력하세요');

      const nextId = Object.keys(farmHouses).length + 1;
      farmHouses[nextId] = { id: nextId, name: name, crop: crop };
      houseSensorData[nextId] = { temp: 24.0, hum: 60.0 };
      renderThumbnailHouses();
      closeModal('house-modal');
    }

    function changeFarmRegion(val) {
      const names = { nonsan: '충남 논산', damyang: '전남 담양', miryang: '경남 밀양', chungju: '충북 충주', buyeo: '충남 부여' };
      const name = names[val] || '충남 논산';
      document.getElementById('region-current-name').innerText = name;
      document.getElementById('weather-text-live').innerText = `${name} · 맑은 밤 24.6°C / 95%`;
      closeModal('region-modal');
    }

    // 초기화
    document.addEventListener('DOMContentLoaded', () => {
      detectAndApplyResolutionMode();
      initAutoFullscreenOnPwa();
      initAuthStatus();
      renderThumbnailHouses();
      updateCurrentHouseVisual();
      updateControlDeckUI();
      syncStatusFromDb();
      setInterval(syncStatusFromDb, 2500); // 2.5초 주기 실시간 Tuya 동기화
    });
  </script>
</body>
</html>
