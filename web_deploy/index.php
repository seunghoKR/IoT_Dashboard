<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>🍓 누리오 스마트팜 (Nurio Smart Farm) - 비주얼 디지털 트윈 관제</title>
  
  <!-- 📱 PWA & 태블릿 앱 바로가기 메타태그 및 매니페스트 -->
  <link rel="manifest" href="manifest.json">
  <link rel="icon" type="image/svg+xml" href="icon.svg">
  <link rel="apple-touch-icon" href="icon.svg">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#070B19">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
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

    /* 📱 100% 무스크롤 태블릿 핏 모드 (Tablet Zero-Scroll Mode) */
    body.tablet-fit-mode {
      height: 100vh;
      max-height: 100vh;
      overflow: hidden !important;
    }
    body.tablet-fit-mode header {
      padding: 8px 16px;
      min-height: 48px;
    }
    body.tablet-fit-mode .brand-logo { font-size: 22px; }
    body.tablet-fit-mode .brand-name { font-size: 16px; }
    body.tablet-fit-mode .brand-sub { display: none; }
    body.tablet-fit-mode main {
      padding: 8px 16px;
      gap: 8px;
      height: calc(100vh - 48px);
      max-height: calc(100vh - 48px);
      overflow: hidden;
    }
    body.tablet-fit-mode .dashboard-split-layout {
      flex: 1;
      height: calc(100% - 46px);
      gap: 12px;
      grid-template-columns: 1.65fr 1fr;
      overflow: hidden;
    }
    body.tablet-fit-mode .visual-twin-pane {
      height: 100%;
      gap: 8px;
      overflow: hidden;
    }
    body.tablet-fit-mode .twin-stage-card {
      height: 100%;
      padding: 10px 14px;
      display: flex;
      flex-direction: column;
    }
    body.tablet-fit-mode .twin-env-bar {
      margin-bottom: 6px;
      padding: 6px 12px;
      font-size: 12px;
    }
    body.tablet-fit-mode .greenhouse-svg-wrapper {
      flex: 1;
      min-height: 0;
      height: 100%;
      padding: 4px;
    }
    body.tablet-fit-mode .greenhouse-svg-wrapper svg {
      height: 100%;
      max-height: 100%;
    }
    body.tablet-fit-mode .curtain-indicator-box {
      padding: 4px 8px;
      font-size: 11px;
    }
    body.tablet-fit-mode .control-deck-pane {
      height: 100%;
      gap: 6px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow-y: auto;
    }
    body.tablet-fit-mode .deck-section-card {
      padding: 8px 12px;
      gap: 6px;
      border-radius: 12px;
    }
    body.tablet-fit-mode .deck-card-header { padding-bottom: 4px; }
    body.tablet-fit-mode .deck-card-title { font-size: 13px; }
    body.tablet-fit-mode .motor-control-group {
      padding: 6px 8px;
      gap: 4px;
      border-radius: 8px;
    }
    body.tablet-fit-mode .motor-name { font-size: 12px; }
    body.tablet-fit-mode .btn-actuator {
      padding: 5px 4px;
      font-size: 12px;
      gap: 2px;
    }
    body.tablet-fit-mode .btn-pump-unit {
      padding: 6px 6px;
      gap: 3px;
      border-radius: 8px;
    }
    body.tablet-fit-mode .pump-icon { font-size: 18px; }
    body.tablet-fit-mode .pump-name { font-size: 12px; }
    body.tablet-fit-mode .pump-state-badge { font-size: 10px; padding: 2px 6px; }
    body.tablet-fit-mode .physical-dock {
      padding: 5px 12px;
      border-radius: 8px;
      margin-top: 0;
    }
    body.tablet-fit-mode .plug-mini-btn {
      padding: 4px 8px;
      font-size: 11px;
    }

    /* 📱 상단 글로벌 헤더 */
    header {
      background: rgba(15, 25, 45, 0.9);
      border-bottom: 1px solid var(--border);
      padding: 12px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(16px);
    }
    .header-left { display: flex; align-items: center; gap: 14px; }
    .btn-hamburger {
      background: rgba(255, 255, 255, 0.1);
      border: 1px solid var(--border-bright);
      color: #FFFFFF;
      border-radius: 8px;
      padding: 8px 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      font-size: 18px;
      transition: background 0.2s;
    }
    .btn-hamburger:hover { background: rgba(255, 255, 255, 0.2); }
    .brand-title-box { display: flex; align-items: center; gap: 10px; }
    .brand-logo { font-size: 28px; }
    .brand-name { font-size: 19px; font-weight: 800; color: #FFFFFF; letter-spacing: -0.5px; }
    .brand-sub { font-size: 11px; color: #34D399; font-weight: 700; }

    .header-right { display: flex; align-items: center; gap: 10px; }
    .status-pill {
      background: rgba(16, 185, 129, 0.25);
      border: 1px solid #10B981;
      color: #6EE7B7;
      font-size: 12px;
      font-weight: 800;
      padding: 6px 14px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .btn-tablet-mode {
      background: #1E2D4A;
      border: 1px solid var(--border-bright);
      color: #FFFFFF;
      border-radius: 8px;
      padding: 7px 12px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .btn-tablet-mode.active {
      background: #0891B2;
      border-color: #22D3EE;
      color: #FFFFFF;
      box-shadow: 0 0 10px rgba(34, 211, 238, 0.4);
    }
    .btn-action-header {
      background: #10B981;
      border: none;
      color: #FFFFFF;
      border-radius: 8px;
      padding: 8px 14px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background 0.2s, transform 0.1s;
    }
    .btn-action-header:hover { background: #059669; }
    .btn-action-header:active { transform: scale(0.97); }

    /* 📂 서랍형 사이드바 */
    .sidebar-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.7);
      backdrop-filter: blur(4px);
      z-index: 200;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.3s ease;
    }
    .sidebar-overlay.active { opacity: 1; pointer-events: auto; }

    aside {
      position: fixed;
      top: 0;
      left: 0;
      bottom: 0;
      width: 280px;
      background: #111C30;
      border-right: 1px solid var(--border-bright);
      padding: 24px 20px;
      display: flex;
      flex-direction: column;
      gap: 20px;
      z-index: 210;
      transform: translateX(-100%);
      transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
      box-shadow: 10px 0 25px rgba(0,0,0,0.5);
    }
    aside.open { transform: translateX(0); }

    .sidebar-header { display: flex; justify-content: space-between; align-items: center; padding-bottom: 12px; border-bottom: 1px solid var(--border); }
    .nav-list { list-style: none; display: flex; flex-direction: column; gap: 8px; }
    .nav-item {
      display: flex; align-items: center; gap: 12px; padding: 12px 16px;
      border-radius: 10px; color: var(--text-secondary); font-weight: 700; cursor: pointer; transition: all 0.2s; font-size: 14px;
    }
    .nav-item.active, .nav-item:hover { background: rgba(16, 185, 129, 0.2); color: #FFFFFF; border: 1px solid #10B981; }

    /* 🌾 2:1 대시보드 스플릿 컨테이너 */
    main {
      flex: 1;
      padding: 16px 20px;
      max-width: 1720px;
      margin: 0 auto;
      width: 100%;
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .dashboard-split-layout {
      display: grid;
      grid-template-columns: 1.85fr 1fr;
      gap: 20px;
      align-items: start;
    }

    @media (max-width: 1024px) {
      .dashboard-split-layout {
        grid-template-columns: 1fr;
      }
    }

    /* 🍓 좌측 2/3: 비주얼 디지털 트윈 영역 */
    .visual-twin-pane {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    /* 하우스 선택 탭바 */
    .house-tabs-bar {
      display: flex;
      align-items: center;
      gap: 10px;
      overflow-x: auto;
      padding-bottom: 4px;
    }
    .house-tab-btn {
      background: #18263E;
      border: 1px solid var(--border-bright);
      color: #FFFFFF;
      padding: 9px 18px;
      border-radius: 10px;
      font-size: 14px;
      font-weight: 800;
      cursor: pointer;
      white-space: nowrap;
      transition: all 0.2s;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .house-tab-btn.active {
      background: rgba(16, 185, 129, 0.25);
      border-color: #10B981;
      color: #6EE7B7;
      box-shadow: 0 0 14px rgba(16, 185, 129, 0.3);
    }
    .house-tab-btn:hover:not(.active) {
      background: rgba(255, 255, 255, 0.12);
    }

    /* 비주얼 트윈 하우스 메인 카드 */
    .twin-stage-card {
      background: #111C30;
      border: 1px solid var(--border-bright);
      border-radius: 20px;
      padding: 20px;
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
    }

    /* 🌦️ 상단 실시간 날씨 및 온실 환경 상태 바 */
    .twin-env-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      flex-wrap: wrap;
      gap: 10px;
      background: rgba(0, 0, 0, 0.45);
      border: 1px solid rgba(255, 255, 255, 0.15);
      border-radius: 14px;
      padding: 12px 18px;
      margin-bottom: 16px;
    }
    .env-item {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 14px;
      font-weight: 700;
      color: #FFFFFF;
    }
    .env-val { color: #38BDF8; font-weight: 800; }
    .region-select-badge {
      background: rgba(56, 189, 248, 0.18);
      border: 1px solid #38BDF8;
      color: #BAE6FD;
      padding: 4px 10px;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 4px;
      transition: background 0.2s;
    }
    .region-select-badge:hover { background: rgba(56, 189, 248, 0.35); }

    /* 🏠 비닐하우스 단면 SVG 인터랙티브 스테이지 */
    .greenhouse-svg-wrapper {
      width: 100%;
      min-height: 400px;
      position: relative;
      display: flex;
      align-items: center;
      justify-content: center;
      background: radial-gradient(circle at 50% 25%, rgba(56, 189, 248, 0.12) 0%, rgba(10, 18, 33, 0.9) 75%);
      border-radius: 16px;
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 10px;
    }

    /* 🌡️ 온실 내부 투야 스마트 온·습도 센서 실시간 HUD 위젯 (좌측 상단 고정 배치로 천정 송풍기 시야 100% 확보) */
    .sensor-hud-center {
      position: absolute;
      top: 14px;
      left: 16px;
      background: rgba(10, 18, 33, 0.92);
      backdrop-filter: blur(10px);
      border: 2px solid rgba(56, 189, 248, 0.6);
      border-radius: 14px;
      padding: 8px 16px;
      display: flex;
      align-items: center;
      gap: 14px;
      z-index: 15;
      box-shadow: 0 6px 25px rgba(0, 0, 0, 0.6), 0 0 15px rgba(56, 189, 248, 0.2);
    }
    .hud-stat-box {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 2px;
    }
    .hud-label {
      font-size: 11px;
      font-weight: 700;
      color: #94A3B8;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .hud-value-temp {
      font-size: 22px;
      font-weight: 900;
      color: #34D399; /* 적온 초록 */
      letter-spacing: -0.5px;
    }
    .hud-value-hum {
      font-size: 22px;
      font-weight: 900;
      color: #38BDF8; /* 습도 하늘색 */
      letter-spacing: -0.5px;
    }
    .hud-comfort-pill {
      font-size: 11px;
      font-weight: 800;
      padding: 3px 8px;
      border-radius: 6px;
      background: rgba(16, 185, 129, 0.2);
      color: #6EE7B7;
      border: 1px solid rgba(16, 185, 129, 0.4);
      white-space: nowrap;
    }

    /* 개폐막 포지션 인디케이터 배지 (고대비 선명화) */
    .curtain-indicator-box {
      position: absolute;
      background: rgba(10, 18, 33, 0.85);
      backdrop-filter: blur(8px);
      border: 1px solid rgba(255, 255, 255, 0.25);
      color: #FFFFFF;
      border-radius: 10px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
      z-index: 10;
      box-shadow: 0 4px 15px rgba(0, 0, 0, 0.4);
    }
    .curtain-pos-left { top: 38%; left: 5%; }
    .curtain-pos-right { top: 38%; right: 5%; }
    .curtain-pos-top { top: 6%; right: 6%; }

    /* 실시간 물방울 / 양액 파티클 애니메이션 */
    @keyframes dripFlow {
      0% { transform: translateY(0); opacity: 0.9; }
      50% { opacity: 1; }
      100% { transform: translateY(14px); opacity: 0; }
    }
    .water-drop {
      animation: dripFlow 1.1s infinite linear;
    }
    @keyframes fanRotate {
      from { transform: rotate(0deg); }
      to { transform: rotate(360deg); }
    }
    .fan-spinning {
      transform-box: fill-box;
      transform-origin: center;
      animation: fanRotate 0.5s infinite linear;
    }
    @keyframes windFlow {
      0% { opacity: 0.1; transform: translateY(0); }
      50% { opacity: 0.85; }
      100% { opacity: 0.1; transform: translateY(14px); }
    }
    .wind-wave {
      animation: windFlow 0.9s infinite ease-in-out;
    }

    /* 🎛️ 우측 1/3: 고감도 원터치 조작 패널 영역 */
    .control-deck-pane {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }

    .deck-section-card {
      background: #142036;
      border: 1px solid var(--border-bright);
      border-radius: 18px;
      padding: 18px 20px;
      display: flex;
      flex-direction: column;
      gap: 14px;
      box-shadow: 0 6px 20px rgba(0, 0, 0, 0.35);
    }
    .deck-card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 10px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    .deck-card-title {
      font-size: 16px;
      font-weight: 800;
      color: #FFFFFF;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    /* 🌡️ 동별 온·습도 실시간 정밀 모니터링 카드 */
    .sensor-detail-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 10px;
      background: #1B2B47;
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 14px;
      padding: 12px;
    }
    .sensor-box {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .sensor-bar-track {
      height: 8px;
      background: rgba(255, 255, 255, 0.15);
      border-radius: 4px;
      overflow: hidden;
      position: relative;
    }
    .sensor-bar-fill-temp {
      height: 100%;
      background: linear-gradient(90deg, #38BDF8 0%, #10B981 50%, #F59E0B 80%, #EF4444 100%);
      border-radius: 4px;
      width: 60%;
      transition: width 0.5s ease;
    }
    .sensor-bar-fill-hum {
      height: 100%;
      background: linear-gradient(90deg, #F59E0B 0%, #10B981 60%, #38BDF8 100%);
      border-radius: 4px;
      width: 65%;
      transition: width 0.5s ease;
    }

    /* 2채널 모터 인터락 제어 유닛 */
    .motor-control-group {
      background: #1B2B47;
      border: 1px solid rgba(255, 255, 255, 0.18);
      border-radius: 14px;
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 12px;
    }
    .motor-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .motor-name { font-size: 15px; font-weight: 800; color: #FFFFFF; }
    
    .motor-status-tag {
      font-size: 12px;
      font-weight: 800;
      padding: 4px 10px;
      border-radius: 8px;
      background: #334155;
      color: #F1F5F9;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .motor-status-tag.active {
      background: #059669;
      color: #FFFFFF;
      border-color: #10B981;
      box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);
    }

    /* 모터 열기/닫기 2버튼 그리드 */
    .motor-btn-grid {
      display: grid;
      grid-template-columns: 1fr 1fr 0.8fr;
      gap: 8px;
    }
    .btn-actuator {
      background: #243553;
      border: 1px solid rgba(255, 255, 255, 0.2);
      color: #FFFFFF;
      border-radius: 10px;
      padding: 12px 8px;
      font-size: 15px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 4px;
      transition: all 0.2s;
    }
    .btn-actuator.open:hover, .btn-actuator.open.active {
      background: #0891B2;
      border-color: #22D3EE;
      box-shadow: 0 0 12px rgba(34, 211, 238, 0.5);
    }
    .btn-actuator.close:hover, .btn-actuator.close.active {
      background: #4F46E5;
      border-color: #818CF8;
      box-shadow: 0 0 12px rgba(129, 140, 248, 0.5);
    }
    .btn-actuator.stop {
      background: #334155;
    }
    .btn-actuator.stop:hover {
      background: #E11D48;
      border-color: #FB7185;
    }

    /* 개폐율 슬라이더 */
    .slider-row {
      display: flex;
      align-items: center;
      gap: 12px;
    }
    .position-slider {
      flex: 1;
      accent-color: #10B981;
      height: 8px;
      border-radius: 4px;
      background: rgba(255, 255, 255, 0.2);
      cursor: pointer;
    }
    .pos-val-text {
      font-size: 14px;
      font-weight: 800;
      color: #38BDF8;
      min-width: 46px;
      text-align: right;
    }

    /* 양수기/양액기 펌프 원터치 버튼 */
    .pump-btn-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 12px;
    }
    .btn-pump-unit {
      background: #1B2B47;
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 14px;
      padding: 16px 12px;
      cursor: pointer;
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 8px;
      transition: all 0.2s;
    }
    .btn-pump-unit:hover {
      background: #23375B;
      border-color: rgba(255, 255, 255, 0.35);
    }
    .btn-pump-unit.active {
      background: rgba(16, 185, 129, 0.25);
      border-color: #10B981;
      box-shadow: 0 0 16px rgba(16, 185, 129, 0.4);
    }
    .pump-icon { font-size: 28px; }
    .pump-name {
      font-size: 15px;
      font-weight: 800;
      color: #FFFFFF;
      letter-spacing: -0.3px;
    }
    .pump-state-badge {
      font-size: 12px;
      font-weight: 800;
      padding: 4px 12px;
      border-radius: 12px;
      background: #334155;
      color: #F1F5F9;
      border: 1px solid rgba(255, 255, 255, 0.2);
    }
    .btn-pump-unit.active .pump-state-badge {
      background: #10B981;
      color: #FFFFFF;
      border-color: #34D399;
    }

    /* 하단 실물 스마트 플러그 및 하드웨어 연동 바 */
    .physical-dock {
      background: #142036;
      border: 1px solid var(--border-bright);
      border-radius: 16px;
      padding: 14px 18px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }
    .dock-plugs-group {
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .plug-mini-btn {
      background: #1E2D4A;
      border: 1px solid var(--border-bright);
      color: #FFFFFF;
      border-radius: 8px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .plug-mini-btn.active {
      background: rgba(16, 185, 129, 0.25);
      border-color: #10B981;
      color: #6EE7B7;
    }

    /* 팝업 모달 공통 스타일 */
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.75);
      backdrop-filter: blur(6px);
      z-index: 300;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 16px;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.2s ease;
    }
    .modal-overlay.active { opacity: 1; pointer-events: auto; }
    .modal-content {
      background: #152238;
      border: 1px solid rgba(255, 255, 255, 0.25);
      border-radius: 18px;
      width: 100%;
      max-width: 480px;
      padding: 24px;
      display: flex;
      flex-direction: column;
      gap: 18px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.6);
      transform: scale(0.95);
      transition: transform 0.2s ease;
    }
    .modal-overlay.active .modal-content { transform: scale(1); }
    .modal-header { display: flex; justify-content: space-between; align-items: center; }
    .modal-title { font-size: 17px; font-weight: 800; color: #FFFFFF; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label { font-size: 13px; font-weight: 700; color: #E2E8F0; }
    .form-input, .form-select {
      background: #090E1A;
      border: 1px solid var(--border-bright);
      border-radius: 8px;
      padding: 10px 14px;
      color: #FFFFFF;
      font-size: 14px;
      font-weight: 600;
      outline: none;
    }
    .form-input:focus, .form-select:focus { border-color: #10B981; }
    .modal-btn-row { display: flex; justify-content: flex-end; gap: 8px; margin-top: 6px; }
    .btn-modal-cancel {
      background: transparent;
      border: 1px solid var(--border-bright);
      color: #E2E8F0;
      border-radius: 8px;
      padding: 8px 16px;
      font-size: 13px;
      font-weight: 700;
      cursor: pointer;
    }
    .btn-modal-save {
      background: #10B981;
      border: none;
      color: #FFFFFF;
      border-radius: 8px;
      padding: 8px 18px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
    }

    /* 토스트 알림 */
    #toast-container { position: fixed; bottom: 24px; right: 24px; z-index: 999; display: flex; flex-direction: column; gap: 8px; }
    .toast {
      background: rgba(19, 30, 50, 0.95);
      border: 1px solid rgba(16, 185, 129, 0.6);
      color: #FFFFFF;
      padding: 12px 18px;
      border-radius: 10px;
      box-shadow: 0 8px 25px rgba(0,0,0,0.5);
      backdrop-filter: blur(8px);
      font-size: 14px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 10px;
      transform: translateY(20px);
      opacity: 0;
      transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toast.show { transform: translateY(0); opacity: 1; }

    .btn-edit-sm {
      background: rgba(255, 255, 255, 0.12);
      border: 1px solid var(--border-bright);
      color: #FFFFFF;
      border-radius: 6px;
      padding: 5px 10px;
      font-size: 12px;
      font-weight: 800;
      cursor: pointer;
      transition: background 0.2s;
    }
    .btn-edit-sm:hover { background: rgba(255, 255, 255, 0.25); color: #FFFFFF; }
  </style>
</head>
<body class="weather-night">

  <!-- 📱 상단 글로벌 내비게이션 바 -->
  <header>
    <div class="header-left">
      <button class="btn-hamburger" onclick="toggleSidebar()" title="메뉴 열기">☰</button>
      <div class="brand-title-box">
        <span class="brand-logo">🍓</span>
        <div>
          <div class="brand-name">누리오 스마트팜 (Nurio Smart Farm)</div>
          <div class="brand-sub">디지털 트윈 기반 실시간 온실 관제 시스템</div>
        </div>
      </div>
    </div>

    <div class="header-right">
      <div class="status-pill">
        <span style="width:8px; height:8px; background:#10B981; border-radius:50%; box-shadow:0 0 6px #10B981;"></span>
        <span id="active-summary">장치 동기화 중...</span>
      </div>
      <!-- 📱 태블릿 한화면 무스크롤 모드 토글 버튼 -->
      <button class="btn-tablet-mode" id="btn-tablet-toggle" onclick="toggleTabletFitMode()" title="태블릿 화면 꽉 채움 무스크롤 모드">
        <span>📱</span><span id="txt-tablet-toggle">태블릿 핏 모드: OFF</span>
      </button>
      <button class="btn-action-header" style="background:#0891B2;" onclick="openInterlockModal()">
        <span>🔒</span><span>인터락 설정</span>
      </button>
      <button class="btn-action-header" onclick="openPwaGuideModal()">
        <span>📲</span><span>앱 설치</span>
      </button>
    </div>
  </header>

  <!-- 📂 서랍형 사이드바 -->
  <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar(false)"></div>
  <aside id="sidebar-drawer">
    <div class="sidebar-header">
      <div style="font-weight: 800; font-size: 16px; color:#FFFFFF;">🍓 누리오 관리 메뉴</div>
      <button class="btn-edit-sm" onclick="toggleSidebar(false)">✕</button>
    </div>
    <ul class="nav-list">
      <li class="nav-item active" onclick="toggleSidebar(false)">📊 실시간 디지털 트윈 뷰</li>
      <li class="nav-item" onclick="toggleTabletFitMode(); toggleSidebar(false);">📱 태블릿 핏 모드 (무스크롤) 전환</li>
      <li class="nav-item" onclick="openRegionModal(); toggleSidebar(false);">📍 농가 지역 날씨 설정</li>
      <li class="nav-item" onclick="openHouseModal(); toggleSidebar(false);">🏗️ 비닐하우스 추가/관리</li>
      <li class="nav-item" onclick="openDeviceModal(); toggleSidebar(false);">⚙️ 스마트 장비 등록</li>
      <li class="nav-item" onclick="openInterlockModal(); toggleSidebar(false);">🔒 4채널 하드웨어 인터락</li>
      <li class="nav-item" onclick="openPwaGuideModal(); toggleSidebar(false);">📲 태블릿PC 앱 바로가기</li>
    </ul>
    <div style="margin-top:auto; font-size:11px; color:var(--text-muted); line-height:1.5;">
      💡 <strong>AI 디자인실장 영자</strong>가 디자인한<br>누리오 스마트팜 디지털 트윈 v2.4
    </div>
  </aside>

  <!-- 🌾 메인 2:1 대시보드 스플릿 레이아웃 -->
  <main>
    <!-- 하우스 선택 탭바 -->
    <div class="house-tabs-bar" id="house-tabs-container">
      <!-- JavaScript로 동적 탭 렌더링 -->
    </div>

    <div class="dashboard-split-layout">
      
      <!-- 🍓 [좌측 2/3] 실시간 비주얼 농가 뷰 (Digital Twin Visualizer) -->
      <div class="visual-twin-pane">
        <div class="twin-stage-card">
          
          <!-- 🌦️ 상단 실시간 지역 날씨 & 온실 환경 관제 바 -->
          <div class="twin-env-bar">
            <div class="env-item">
              <span>🏠</span>
              <span id="twin-house-title" style="font-weight:800; color:#FFFFFF;">1동 하우스</span>
              <span class="btn-edit-sm" id="twin-crop-badge" style="background:rgba(16,185,129,0.3); color:#6EE7B7; border-color:#10B981;">딸기 (설향)</span>
            </div>

            <!-- 실시간 지역 날씨 배지 -->
            <div class="env-item">
              <span id="weather-icon-live">🌙</span>
              <span id="weather-text-live">충남 논산 · 맑은 밤 24.6°C / 95%</span>
              <button class="region-select-badge" onclick="openRegionModal()" title="농가 지역 변경">
                <span>📍</span><span id="region-current-name">충남 논산</span>
              </button>
            </div>

            <div class="env-item">
              <span>📡 투야 온습도 센서: <span class="env-val" id="env-sensor-status">정상 수신 중</span></span>
            </div>
          </div>

          <!-- 🏠 인터랙티브 비닐하우스 단면 SVG 캔버스 -->
          <div class="greenhouse-svg-wrapper" id="greenhouse-stage">
            
            <!-- 🌡️ 온실 중앙 실시간 스마트 온·습도 HUD 위젯 -->
            <div class="sensor-hud-center" id="sensor-hud-widget">
              <div class="hud-stat-box">
                <span class="hud-label">🌡️ 하우스 실내온도</span>
                <span class="hud-value-temp" id="val-twin-temp">24.5°C</span>
              </div>
              <div style="width:1px; height:32px; background:rgba(255,255,255,0.2);"></div>
              <div class="hud-stat-box">
                <span class="hud-label">💧 실내습도</span>
                <span class="hud-value-hum" id="val-twin-hum">62%</span>
              </div>
              <div style="width:1px; height:32px; background:rgba(255,255,255,0.2);"></div>
              <div class="hud-stat-box">
                <span class="hud-label">🍓 생육 쾌적도</span>
                <span class="hud-comfort-pill" id="val-twin-comfort">😊 쾌적 (최적 생육)</span>
              </div>
            </div>

            <!-- 개폐막 포지션 인디케이터 배지 -->
            <div class="curtain-indicator-box curtain-pos-left" id="badge-left-curtain">
              <span id="gear-left" style="display:inline-block;">⚙️</span>
              <span id="lbl-left-curtain">측창 비닐: 0% (완전밀폐)</span>
            </div>

            <div class="curtain-indicator-box curtain-pos-top" id="badge-top-curtain">
              <span>☀️</span>
              <span id="lbl-top-curtain">차광막: 0% (해제)</span>
            </div>

            <div class="curtain-indicator-box curtain-pos-right" id="badge-pump-status">
              <span>💧</span>
              <span id="lbl-pump-status">양수기: 대기 중</span>
            </div>

            <!-- 정밀 SVG 비닐하우스 단면 그래픽 -->
            <svg viewBox="0 0 800 460" width="100%" height="100%" preserveAspectRatio="xMidYMid meet">
              <defs>
                <linearGradient id="skyGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop id="skyStop1" offset="0%" stop-color="#142646" stop-opacity="0.8"/>
                  <stop id="skyStop2" offset="100%" stop-color="#080F1E" stop-opacity="0.95"/>
                </linearGradient>
                <linearGradient id="vinylGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                  <stop offset="0%" stop-color="#38BDF8" stop-opacity="0.5"/>
                  <stop offset="50%" stop-color="#FFFFFF" stop-opacity="0.25"/>
                  <stop offset="100%" stop-color="#38BDF8" stop-opacity="0.5"/>
                </linearGradient>
                <pattern id="shadePattern" width="8" height="8" patternUnits="userSpaceOnUse">
                  <path d="M0 0h8v8H0z" fill="#0B132B" fill-opacity="0.85"/>
                  <path d="M0 0l8 8M8 0l-8 8" stroke="#475569" stroke-width="1.2"/>
                </pattern>
                <linearGradient id="groundGrad" x1="0%" y1="0%" x2="0%" y2="100%">
                  <stop offset="0%" stop-color="#334155"/>
                  <stop offset="100%" stop-color="#0F172A"/>
                </linearGradient>
              </defs>

              <rect x="20" y="20" width="760" height="420" rx="16" fill="url(#skyGrad)"/>
              
              <!-- 밤하늘 별 & 달 -->
              <g id="svg-sky-night" opacity="0.8">
                <circle cx="700" cy="70" r="18" fill="#FEF08A" filter="drop-shadow(0 0 8px #FDE047)"/>
                <circle cx="692" cy="65" r="16" fill="#142646"/>
                <circle cx="150" cy="80" r="1.5" fill="#FFFFFF"/>
                <circle cx="280" cy="60" r="2" fill="#FFFFFF"/>
                <circle cx="450" cy="85" r="1.5" fill="#FFFFFF"/>
                <circle cx="600" cy="95" r="2" fill="#FFFFFF"/>
              </g>

              <!-- 낮 태양 -->
              <g id="svg-sky-day" opacity="0" style="display:none;">
                <circle cx="680" cy="75" r="24" fill="#F59E0B" filter="drop-shadow(0 0 14px #FBBF24)"/>
              </g>

              <path d="M 40 380 L 760 380 L 760 430 L 40 430 Z" fill="url(#groundGrad)"/>
              
              <!-- 🏗️ 비닐하우스 외부 아치 철골 트러스 프레임 -->
              <path d="M 100 380 L 100 240 Q 100 80, 400 70 Q 700 80, 700 240 L 700 380" 
                    fill="none" stroke="#64748B" stroke-width="8" stroke-linecap="round"/>
              <path d="M 120 380 L 120 240 Q 120 100, 400 90 Q 680 100, 680 240 L 680 380" 
                    fill="none" stroke="#475569" stroke-width="4"/>

              <!-- 트러스 보강 지지대 -->
              <line x1="200" y1="160" x2="600" y2="160" stroke="#475569" stroke-width="3" stroke-dasharray="6,4"/>
              <line x1="400" y1="70" x2="400" y2="160" stroke="#475569" stroke-width="3"/>
              <line x1="280" y1="160" x2="400" y2="90" stroke="#334155" stroke-width="2"/>
              <line x1="520" y1="160" x2="400" y2="90" stroke="#334155" stroke-width="2"/>

              <!-- 💨 3. 하우스 천정 중앙 대형 고시인성 순환 송풍기 (유동팬) -->
              <g id="svg-center-fan-unit" transform="translate(400, 125)">
                <!-- 천정 고정 마운트 브라켓 -->
                <line x1="0" y1="-55" x2="0" y2="-38" stroke="#CBD5E1" stroke-width="6"/>
                <line x1="-25" y1="-38" x2="25" y2="-38" stroke="#94A3B8" stroke-width="4"/>
                
                <!-- 원형 송풍기 보호망 및 외부 하우징 (반지름 42px 대형화) -->
                <circle cx="0" cy="0" r="42" fill="#0F172A" stroke="#38BDF8" stroke-width="3.5" filter="drop-shadow(0 0 10px rgba(56,189,248,0.4))"/>
                <circle cx="0" cy="0" r="37" fill="none" stroke="#64748B" stroke-width="1.5" stroke-dasharray="4,3"/>
                <!-- 방사형 보호 그릴 살대 -->
                <line x1="-36" y1="0" x2="36" y2="0" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>
                <line x1="0" y1="-36" x2="0" y2="36" stroke="rgba(255,255,255,0.2)" stroke-width="1.5"/>

                <!-- 4엽 대형 고성능 회전 팬 날개 (작동 시 초고속 회전) -->
                <g id="svg-fan-blades">
                  <!-- 날개 1 (상) -->
                  <path d="M 0 0 C -9 -16 -18 -26 0 -34 C 18 -26 9 -16 0 0 Z" fill="#00E5FF" stroke="#FFFFFF" stroke-width="1"/>
                  <!-- 날개 2 (우) -->
                  <path d="M 0 0 C 16 -9 26 -18 34 0 C 26 18 16 9 0 0 Z" fill="#00E5FF" stroke="#FFFFFF" stroke-width="1"/>
                  <!-- 날개 3 (하) -->
                  <path d="M 0 0 C 9 16 18 26 0 34 C -18 26 -9 16 0 0 Z" fill="#00E5FF" stroke="#FFFFFF" stroke-width="1"/>
                  <!-- 날개 4 (좌) -->
                  <path d="M 0 0 C -16 9 -26 18 -34 0 C -26 -18 -16 -9 0 0 Z" fill="#00E5FF" stroke="#FFFFFF" stroke-width="1"/>
                  <!-- 중앙 모터 로터 허브 & 작동 LED -->
                  <circle cx="0" cy="0" r="11" fill="#1E293B" stroke="#38BDF8" stroke-width="2.5"/>
                  <circle cx="0" cy="0" r="5" fill="#64748B" id="svg-fan-led"/>
                </g>

                <!-- 송풍 가동 시 하방 순환 강력 바람결 파동 효과 -->
                <g id="svg-fan-wind" style="display:none;" opacity="0.85">
                  <path class="wind-wave" d="M -30 46 Q 0 68, 30 46" fill="none" stroke="#38BDF8" stroke-width="3" stroke-linecap="round"/>
                  <path class="wind-wave" d="M -48 64 Q 0 94, 48 64" fill="none" stroke="#38BDF8" stroke-width="3.5" stroke-linecap="round" style="animation-delay:0.25s;"/>
                  <path class="wind-wave" d="M -65 82 Q 0 120, 65 82" fill="none" stroke="#38BDF8" stroke-width="3" stroke-linecap="round" style="animation-delay:0.5s;"/>
                </g>

                <!-- 송풍기 명칭 배지 -->
                <rect x="-65" y="46" width="130" height="22" rx="6" fill="rgba(15,23,42,0.92)" stroke="rgba(255,255,255,0.3)" stroke-width="1"/>
                <text x="0" y="61" text-anchor="middle" fill="#FFFFFF" font-size="11" font-weight="900">💨 천정 순환 송풍기</text>
              </g>

              <!-- 📡 트러스 하단에 매달린 스마트 온·습도 센서 노드 일러스트 (송풍기와 겹치지 않게 하방 배치) -->
              <g transform="translate(560, 200)">
                <line x1="0" y1="-40" x2="0" y2="0" stroke="#94A3B8" stroke-width="2"/>
                <rect x="-14" y="0" width="28" height="42" rx="6" fill="#F8FAFC" stroke="#06B6D4" stroke-width="2.5" filter="drop-shadow(0 2px 6px rgba(0,0,0,0.5))"/>
                <!-- 센서 환기 슬릿 및 LED -->
                <circle cx="0" cy="12" r="3" fill="#10B981"/>
                <line x1="-8" y1="24" x2="8" y2="24" stroke="#64748B" stroke-width="2"/>
                <line x1="-8" y1="30" x2="8" y2="30" stroke="#64748B" stroke-width="2"/>
                <!-- RF 전파 링 -->
                <circle cx="0" cy="12" r="16" fill="none" stroke="#38BDF8" stroke-width="1.5" opacity="0.5" stroke-dasharray="4,3"/>
                <text x="0" y="54" text-anchor="middle" fill="#BAE6FD" font-size="10" font-weight="800">📡 온·습도 센서</text>
              </g>

              <!-- ☀️ 1. 상부 차광막/보온스크린 레이어 -->
              <path id="svg-shade-screen" d="M 130 160 L 670 160" 
                    stroke="url(#shadePattern)" stroke-width="28" stroke-linecap="round" opacity="0.1"/>

              <!-- 🏠 2. 측창/천창 롤업 비닐막 -->
              <path id="svg-left-vinyl" d="M 100 380 L 100 240 Q 100 130, 260 110" 
                    fill="none" stroke="url(#vinylGrad)" stroke-width="12" stroke-linecap="round"/>
              <circle id="svg-left-roller" cx="100" cy="380" r="10" fill="#38BDF8" stroke="#FFFFFF" stroke-width="2.5"/>

              <path id="svg-right-vinyl" d="M 700 380 L 700 240 Q 700 130, 540 110" 
                    fill="none" stroke="url(#vinylGrad)" stroke-width="12" stroke-linecap="round"/>
              <circle id="svg-right-roller" cx="700" cy="380" r="10" fill="#38BDF8" stroke="#FFFFFF" stroke-width="2.5"/>

              <!-- 🍓 4. 고설 딸기 재배 베드 & 관수 라인 -->
              <line x1="220" y1="380" x2="220" y2="300" stroke="#64748B" stroke-width="5"/>
              <line x1="340" y1="380" x2="340" y2="300" stroke="#64748B" stroke-width="5"/>
              <line x1="460" y1="380" x2="460" y2="300" stroke="#64748B" stroke-width="5"/>
              <line x1="580" y1="380" x2="580" y2="300" stroke="#64748B" stroke-width="5"/>

              <rect x="180" y="285" width="440" height="20" rx="6" fill="#1E293B" stroke="#059669" stroke-width="2"/>
              <line x1="180" y1="295" x2="620" y2="295" stroke="#06B6D4" stroke-width="4" id="svg-drip-pipe"/>

              <!-- 💧 실시간 물방울 파티클 -->
              <g id="svg-water-drops" style="display:none;">
                <circle class="water-drop" cx="240" cy="305" r="3.5" fill="#38BDF8"/>
                <circle class="water-drop" cx="300" cy="305" r="3.5" fill="#38BDF8"/>
                <circle class="water-drop" cx="360" cy="305" r="3.5" fill="#38BDF8"/>
                <circle class="water-drop" cx="420" cy="305" r="3.5" fill="#38BDF8"/>
                <circle class="water-drop" cx="480" cy="305" r="3.5" fill="#38BDF8"/>
                <circle class="water-drop" cx="540" cy="305" r="3.5" fill="#38BDF8"/>
                <circle class="water-drop" cx="600" cy="305" r="3.5" fill="#38BDF8"/>
              </g>

              <!-- 딸기 잎 & 열매 일러스트 -->
              <g transform="translate(200, 245)">
                <text x="30" y="35" font-size="28">🍓</text>
                <text x="80" y="35" font-size="28">🌱</text>
                <text x="130" y="35" font-size="28">🍓</text>
                <text x="180" y="35" font-size="28">🌱</text>
                <text x="230" y="35" font-size="28">🍓</text>
                <text x="280" y="35" font-size="28">🌱</text>
                <text x="330" y="35" font-size="28">🍓</text>
              </g>

              <!-- 💡 5. LED 보광등 조명 빔 -->
              <g id="svg-grow-lights" style="display:none;" opacity="0.4">
                <polygon points="260,160 200,285 320,285" fill="#EC4899"/>
                <polygon points="400,160 340,285 460,285" fill="#A855F7"/>
                <polygon points="540,160 480,285 600,285" fill="#EC4899"/>
              </g>
            </svg>
          </div>
        </div>
      </div>

      <!-- 🎛️ [우측 1/3] 원터치 직관 조작 패널 -->
      <div class="control-deck-pane">
        
        <!-- 0. 🌡️ 투야 온·습도 센서 실시간 환경 모니터링 카드 -->
        <div class="deck-section-card">
          <div class="deck-card-header">
            <div class="deck-card-title">
              <span>🌡️</span><span id="deck-sensor-title">1동 온·습도 스마트 센서</span>
            </div>
            <span class="btn-edit-sm" style="background:rgba(56,189,248,0.2); border-color:#38BDF8; color:#BAE6FD;" id="deck-sensor-battery">🔋 98%</span>
          </div>

          <div class="sensor-detail-grid">
            <div class="sensor-box">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:12px; font-weight:700; color:#94A3B8;">온도 (적온 18~25°C)</span>
                <span style="font-size:16px; font-weight:900; color:#34D399;" id="deck-val-temp">24.5°C</span>
              </div>
              <div class="sensor-bar-track">
                <div class="sensor-bar-fill-temp" id="bar-temp" style="width: 61%;"></div>
              </div>
            </div>

            <div class="sensor-box">
              <div style="display:flex; justify-content:space-between; align-items:center;">
                <span style="font-size:12px; font-weight:700; color:#94A3B8;">습도 (적정 60~70%)</span>
                <span style="font-size:16px; font-weight:900; color:#38BDF8;" id="deck-val-hum">62%</span>
              </div>
              <div class="sensor-bar-track">
                <div class="sensor-bar-fill-hum" id="bar-hum" style="width: 62%;"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- 1. 개폐기 모터 제어반 (모터 2조 인터락 연동) -->
        <div class="deck-section-card">
          <div class="deck-card-header">
            <div class="deck-card-title">
              <span>🎛️</span><span>온실 개폐기 모터 제어반</span>
            </div>
            <span class="btn-edit-sm" onclick="openInterlockModal()">🔒 인터락</span>
          </div>

          <!-- 모터 1조 (측창 비닐막) -->
          <div class="motor-control-group">
            <div class="motor-header">
              <span class="motor-name">🏠 1호 모터 : 측창 비닐막</span>
              <span class="motor-status-tag" id="tag-motor-1">0% (밀폐)</span>
            </div>
            <div class="motor-btn-grid">
              <button class="btn-actuator open" id="btn-m1-open" onclick="triggerMotorStep(1, 'OPEN')">
                <span>▲</span><span>열기</span>
              </button>
              <button class="btn-actuator close" id="btn-m1-close" onclick="triggerMotorStep(1, 'CLOSE')">
                <span>▼</span><span>닫기</span>
              </button>
              <button class="btn-actuator stop" onclick="triggerMotorStop(1)">
                <span>⏸️</span><span>정지</span>
              </button>
            </div>
            <div class="slider-row">
              <input type="range" min="0" max="100" value="0" class="position-slider" id="slider-motor-1" oninput="handleSliderChange(1, this.value)">
              <span class="pos-val-text" id="val-motor-1">0%</span>
            </div>
          </div>

          <!-- 모터 2조 (상부 차광막/보온스크린) -->
          <div class="motor-control-group">
            <div class="motor-header">
              <span class="motor-name">☀️ 2호 모터 : 상부 차광 스크린</span>
              <span class="motor-status-tag" id="tag-motor-2">0% (해제)</span>
            </div>
            <div class="motor-btn-grid">
              <button class="btn-actuator open" id="btn-m2-open" onclick="triggerMotorStep(2, 'OPEN')">
                <span>▲</span><span>전개</span>
              </button>
              <button class="btn-actuator close" id="btn-m2-close" onclick="triggerMotorStep(2, 'CLOSE')">
                <span>▼</span><span>수축</span>
              </button>
              <button class="btn-actuator stop" onclick="triggerMotorStop(2)">
                <span>⏸️</span><span>정지</span>
              </button>
            </div>
            <div class="slider-row">
              <input type="range" min="0" max="100" value="0" class="position-slider" id="slider-motor-2" oninput="handleSliderChange(2, this.value)">
              <span class="pos-val-text" id="val-motor-2">0%</span>
            </div>
          </div>
        </div>

        <!-- 2. 스마트 관수 & 양액 펌프 제어반 -->
        <div class="deck-section-card">
          <div class="deck-card-header">
            <div class="deck-card-title">
              <span>💧</span><span>관수 및 양액 공급 시스템</span>
            </div>
            <span style="font-size:12px; font-weight:700; color:#38BDF8;">원터치 펌프</span>
          </div>

          <div class="pump-btn-grid">
            <button class="btn-pump-unit" id="pump-unit-water" onclick="togglePumpDevice('WATER')">
              <span class="pump-icon">💧</span>
              <span class="pump-name">주 양수기 (2.0HP)</span>
              <span class="pump-state-badge" id="badge-pump-water">정지 (OFF)</span>
            </button>

            <button class="btn-pump-unit" id="pump-unit-nutrient" onclick="togglePumpDevice('NUTRIENT')">
              <span class="pump-icon">🧪</span>
              <span class="pump-name">스마트 양액기</span>
              <span class="pump-state-badge" id="badge-pump-nutrient">대기 (OFF)</span>
            </button>
          </div>
        </div>

        <!-- 3. 환경 보조 제어 (환풍기 & 보광등) -->
        <div class="deck-section-card">
          <div class="deck-card-header">
            <div class="deck-card-title">
              <span>💨</span><span>온실 환경 보조 설비</span>
            </div>
          </div>

          <div class="pump-btn-grid">
            <button class="btn-pump-unit" id="pump-unit-fan" onclick="toggleAuxDevice('FAN')">
              <span class="pump-icon">💨</span>
              <span class="pump-name">환풍 유동팬</span>
              <span class="pump-state-badge" id="badge-aux-fan">정지 (OFF)</span>
            </button>

            <button class="btn-pump-unit" id="pump-unit-light" onclick="toggleAuxDevice('LIGHT')">
              <span class="pump-icon">💡</span>
              <span class="pump-name">LED 보광등</span>
              <span class="pump-state-badge" id="badge-aux-light">소등 (OFF)</span>
            </button>
          </div>
        </div>

      </div>
    </div>

    <!-- 🔌 하단 실물 하드웨어 빠른 관리 바 -->
    <div class="physical-dock">
      <div style="display:flex; align-items:center; gap:12px;">
        <span style="font-size:18px;">🔌</span>
        <div>
          <div style="font-size:14px; font-weight:800; color:#FFFFFF;">실물 투야 하드웨어 연동 상태</div>
          <div style="font-size:12px; font-weight:600; color:#94A3B8;" id="hardware-quick-status">4채널 스위치 & 투야 온습도 센서 정상 수신 중</div>
        </div>
      </div>

      <div class="dock-plugs-group">
        <button class="plug-mini-btn" id="plug-btn-1" onclick="togglePlug('ebb219afdebea03ba3shlz', 1)">
          <span>💡</span><span id="plug-name-1">책상등</span>: <span id="plug-state-1">OFF</span>
        </button>
        <button class="plug-mini-btn" id="plug-btn-2" onclick="togglePlug('42362638a4e57cb3cd0b', 2)">
          <span>🖨️</span><span id="plug-name-2">3D프린터</span>: <span id="plug-state-2">OFF</span>
        </button>
        <button class="btn-edit-sm" onclick="openDeviceModal()">➕ 장비 등록</button>
      </div>
    </div>

  </main>

  <!-- 📍 0. 농가 지역 날씨 설정 모달 -->
  <div class="modal-overlay" id="region-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">📍 농가 지역 날씨 실시간 연동 설정</div>
        <button class="btn-edit-sm" onclick="closeModal('region-modal')">✕</button>
      </div>

      <div style="background:rgba(56, 189, 248, 0.15); border:1px solid rgba(56, 189, 248, 0.4); border-radius:12px; padding:12px; font-size:13px; color:#BAE6FD; line-height:1.5;">
        🌦️ <strong>선택하신 지역의 기상청/글로벌 실시간 기상 데이터(기온, 습도, 풍속, 낮/밤, 강수)</strong>가 대시보드 배경과 환경 바에 즉시 반영됩니다!
      </div>

      <div class="form-group">
        <label class="form-label">딸기 주산지 및 농가 지역 선택</label>
        <select class="form-select" id="sel-region-preset" onchange="applyRegionPreset(this.value)">
          <option value="nonsan">🍓 충남 논산시 (36.19°N, 127.09°E) - 대표 주산지</option>
          <option value="miryang">🍓 경남 밀양시 (35.50°N, 128.75°E)</option>
          <option value="damyang">🍓 전남 담양군 (35.32°N, 126.98°E)</option>
          <option value="jinju">🍓 경남 진주시 (35.18°N, 128.10°E)</option>
          <option value="goryeong">🍓 경북 고령군 (35.72°N, 128.26°E)</option>
          <option value="buyeo">🍓 충남 부여군 (36.27°N, 126.92°E)</option>
          <option value="wanju">🍓 전북 완주군 (35.90°N, 127.16°E)</option>
          <option value="yangpyeong">🍓 경기 양평군 (37.49°N, 127.49°E)</option>
          <option value="seoul">🏙️ 서울 / 수도권 (37.56°N, 126.97°E)</option>
        </select>
      </div>

      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('region-modal')">닫기</button>
        <button class="btn-modal-save" onclick="saveRegionSubmit()">적용 및 실시간 반영</button>
      </div>
    </div>
  </div>

  <!-- 🏗️ 1. 하우스 추가/편집 모달 -->
  <div class="modal-overlay" id="house-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title" id="house-modal-title">🏗️ 비닐하우스 추가 / 편집</div>
        <button class="btn-edit-sm" onclick="closeModal('house-modal')">✕</button>
      </div>
      <input type="hidden" id="h-form-id">
      <div class="form-group">
        <label class="form-label">하우스 명칭</label>
        <input type="text" class="form-input" id="h-form-name" placeholder="예: 🍓 1동 설향 딸기 재배하우스">
      </div>
      <div class="form-group">
        <label class="form-label">재배 작물</label>
        <input type="text" class="form-input" id="h-form-crop" placeholder="예: 딸기 (설향), 토마토 등">
      </div>
      <div class="form-group">
        <label class="form-label">메모 / 구역 설명</label>
        <input type="text" class="form-input" id="h-form-memo" placeholder="예: 1동 스마트 양액 및 차광막 집중 관제">
      </div>
      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('house-modal')">취소</button>
        <button class="btn-modal-save" onclick="saveHouseSubmit()">저장하기</button>
      </div>
    </div>
  </div>

  <!-- ⚙️ 2. 스마트 농가 장비 등록 모달 -->
  <div class="modal-overlay" id="device-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title" id="device-modal-title">⚙️ 스마트 농가 장비 등록 / 편집</div>
        <button class="btn-edit-sm" onclick="closeModal('device-modal')">✕</button>
      </div>
      <input type="hidden" id="d-form-id">
      <div class="form-group">
        <label class="form-label">소속 비닐하우스</label>
        <select class="form-select" id="d-form-house-id"></select>
      </div>
      <div class="form-group">
        <label class="form-label">장치 종류 (카테고리)</label>
        <select class="form-select" id="d-form-category">
          <option value="TEMP_HUMID_SENSOR">🌡️ 투야 스마트 온·습도 센서</option>
          <option value="CURTAIN">☀️ 차광막 / 보온스크린</option>
          <option value="VINYL">🏠 측창·천창 비닐막 개폐기</option>
          <option value="WATER_PUMP">💧 양수기 / 관수 펌프</option>
          <option value="NUTRIENT_FEEDER">🧪 양액기 / 양액 공급기</option>
          <option value="VENT_FAN">💨 환풍기 / 유동팬</option>
          <option value="GROW_LIGHT">💡 LED 보광등</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">장비 명칭</label>
        <input type="text" class="form-input" id="d-form-name" placeholder="예: 1동 투야 온·습도 센서 1호">
      </div>
      <div class="form-group">
        <label class="form-label">실물 투야 릴레이/센서 연동 (선택)</label>
        <select class="form-select" id="d-form-binding">
          <option value="">연동 안 함 (독립 관제)</option>
          <option value="eb654aa2437462ea40dfjw:1">🎛️ 4채널 스위치 - 1번 채널 (1호 열기)</option>
          <option value="eb654aa2437462ea40dfjw:2">🎛️ 4채널 스위치 - 2번 채널 (1호 닫기)</option>
          <option value="eb654aa2437462ea40dfjw:3">🎛️ 4채널 스위치 - 3번 채널 (2호 열기)</option>
          <option value="eb654aa2437462ea40dfjw:4">🎛️ 4채널 스위치 - 4번 채널 (2호 닫기)</option>
        </select>
      </div>
      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('device-modal')">취소</button>
        <button class="btn-modal-save" onclick="saveDeviceSubmit()">저장하기</button>
      </div>
    </div>
  </div>

  <!-- 🔒 3. 4채널 멀티 스위치 인터락 설정 모달 -->
  <div class="modal-overlay" id="interlock-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">🔒 4채널 하드웨어 인터락(Interlock) 설정</div>
        <button class="btn-edit-sm" onclick="closeModal('interlock-modal')">✕</button>
      </div>

      <div style="background:rgba(6, 182, 212, 0.15); border:1px solid rgba(6, 182, 212, 0.4); border-radius:12px; padding:12px; font-size:13px; color:#CFFAFE; line-height:1.5;">
        📱 <strong>스마트폰 Smart Life 앱 및 투야 하드웨어와 실시간 100% 양방향 동기화됩니다.</strong><br>
        묶인 채널 중 하나를 켜면 반대편 채널이 물리 릴레이 수준에서 자동으로 즉시 차단(OFF)되어 <strong>모터 정역회전 쇼트 방지 및 안전 개폐</strong>를 완벽 보장합니다.
      </div>

      <div class="form-group" style="margin-top:10px;">
        <label class="form-label">인터락 묶음 모드 선택</label>
        <div style="display:flex; flex-direction:column; gap:10px;">
          <label style="display:flex; align-items:flex-start; gap:10px; background:rgba(0,0,0,0.3); padding:12px; border-radius:10px; cursor:pointer; border:1px solid rgba(255,255,255,0.15);">
            <input type="radio" name="interlock_preset" value="2x2" checked style="margin-top:3px;">
            <div>
              <div style="font-size:14px; font-weight:800; color:#34D399;">🌟 [농가 기본 권장] 1-2번 묶음 & 3-4번 묶음</div>
              <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">[CH1 ↔ CH2 상호잠금], [CH3 ↔ CH4 상호잠금] (개폐기 1호 / 2호 정역회전 방지)</div>
            </div>
          </label>

          <label style="display:flex; align-items:flex-start; gap:10px; background:rgba(0,0,0,0.3); padding:12px; border-radius:10px; cursor:pointer; border:1px solid rgba(255,255,255,0.15);">
            <input type="radio" name="interlock_preset" value="none" style="margin-top:3px;">
            <div>
              <div style="font-size:14px; font-weight:800; color:#F43F5E;">⛔ 인터락 완전 해제 (4채널 개별 독립 스위치)</div>
              <div style="font-size:12px; color:var(--text-secondary); margin-top:2px;">상호 잠금 없이 4개 채널을 모두 자유롭게 켜고 끕니다.</div>
            </div>
          </label>
        </div>
      </div>

      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('interlock-modal')">취소</button>
        <button class="btn-modal-save" style="background:#0891B2;" onclick="saveInterlockSubmit()">저장 & Tuya 동기화</button>
      </div>
    </div>
  </div>

  <!-- 📲 4. 태블릿 PWA 앱 설치 가이드 모달 -->
  <div class="modal-overlay" id="pwa-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">📲 태블릿PC에 전용 앱으로 설치하기</div>
        <button class="btn-edit-sm" onclick="closeModal('pwa-modal')">✕</button>
      </div>

      <div style="text-align:center; padding:10px 0;">
        <img src="icon.svg" style="width:72px; height:72px; border-radius:18px; box-shadow:0 6px 15px rgba(0,0,0,0.4);" alt="App Icon">
        <div style="font-size:16px; font-weight:800; margin-top:8px; color:#FFFFFF;">누리오 스마트팜</div>
        <div style="font-size:12px; color:var(--primary); font-weight:700;">주소창 없는 100% 전체화면 독립 앱</div>
      </div>

      <div style="background:rgba(0,0,0,0.3); border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:10px; font-size:13px; line-height:1.5;">
        <div>1. 브라우저 우측 상단 <strong>더보기 (⋮ 또는 ☰)</strong>를 누르세요.</div>
        <div>2. <strong>[홈 화면에 추가]</strong> 또는 <strong>[앱 설치]</strong>를 선택하세요.</div>
        <div>3. 바탕화면에 🍓 <strong>누리오 스마트팜</strong> 아이콘이 생성됩니다!</div>
      </div>

      <div id="native-install-box" style="display:none; text-align:center;">
        <button class="btn-modal-save" style="width:100%; padding:12px; font-size:14px;" onclick="triggerNativeInstall()">
          ⚡ 원클릭 즉시 앱 설치하기
        </button>
      </div>

      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('pwa-modal')">확인 완료</button>
      </div>
    </div>
  </div>

  <!-- 토스트 컨테이너 -->
  <div id="toast-container"></div>

  <script>
    const DEVICE_ID_4CH = 'eb654aa2437462ea40dfjw';

    let state1 = false;
    let state2 = false;
    const states4ch = { 1: false, 2: false, 3: false, 4: false };

    let farmHouses = {};
    let currentHouseId = 1;

    // 동별 온·습도 실측 데이터 (기본값 / 센서 연동값)
    let houseSensorData = {
      1: { temp: 24.5, hum: 62.0, battery: 98, status: '정상' },
      2: { temp: 23.8, hum: 65.0, battery: 95, status: '정상' },
      3: { temp: 25.1, hum: 59.0, battery: 100, status: '정상' }
    };

    // 📱 태블릿 무스크롤 모드 설정
    let isTabletFitMode = localStorage.getItem('nurio_tablet_fit_mode') === 'true';

    function initTabletFitMode() {
      applyTabletFitMode(isTabletFitMode);
    }

    function toggleTabletFitMode() {
      isTabletFitMode = !isTabletFitMode;
      localStorage.setItem('nurio_tablet_fit_mode', isTabletFitMode);
      applyTabletFitMode(isTabletFitMode);
      showToast(isTabletFitMode ? '📱 [태블릿 한화면 무스크롤 모드]가 켜졌습니다!' : '💻 [일반 스크롤 모드]로 전환되었습니다.', 'success');
    }

    function applyTabletFitMode(enable) {
      const btn = document.getElementById('btn-tablet-toggle');
      const txt = document.getElementById('txt-tablet-toggle');
      if (enable) {
        document.body.classList.add('tablet-fit-mode');
        if (btn) btn.classList.add('active');
        if (txt) txt.innerText = '태블릿 핏: ON';
      } else {
        document.body.classList.remove('tablet-fit-mode');
        if (btn) btn.classList.remove('active');
        if (txt) txt.innerText = '태블릿 핏: OFF';
      }
    }

    // 지역 좌표 프리셋
    const REGION_MAP = {
      'nonsan': { name: '충남 논산', lat: 36.19, lon: 127.09 },
      'miryang': { name: '경남 밀양', lat: 35.50, lon: 128.75 },
      'damyang': { name: '전남 담양', lat: 35.32, lon: 126.98 },
      'jinju': { name: '경남 진주', lat: 35.18, lon: 128.10 },
      'goryeong': { name: '경북 고령', lat: 35.72, lon: 128.26 },
      'buyeo': { name: '충남 부여', lat: 36.27, lon: 126.92 },
      'wanju': { name: '전북 완주', lat: 35.90, lon: 127.16 },
      'yangpyeong': { name: '경기 양평', lat: 37.49, lon: 127.49 },
      'seoul': { name: '서울/수도권', lat: 37.56, lon: 126.97 }
    };
    let currentRegionKey = localStorage.getItem('nurio_farm_region') || 'nonsan';

    // 모터 가상 포지션 (0 ~ 100%)
    let motorPositions = { 1: 0, 2: 0 };
    let motorDirections = { 1: 0, 2: 0 };

    let isWaterPumpActive = false;
    let isNutrientActive = false;
    let isVentFanActive = false;
    let isGrowLightActive = false;

    let deferredPrompt = null;
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredPrompt = e;
      const box = document.getElementById('native-install-box');
      if (box) box.style.display = 'block';
    });

    function openPwaGuideModal() {
      document.getElementById('pwa-modal').classList.add('active');
    }

    async function triggerNativeInstall() {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        const { outcome } = await deferredPrompt.userChoice;
        if (outcome === 'accepted') {
          showToast('🎉 누리오 스마트팜 앱이 태블릿에 설치되었습니다!', 'success');
          closeModal('pwa-modal');
        }
        deferredPrompt = null;
      }
    }

    function toggleSidebar(force) {
      const drawer = document.getElementById('sidebar-drawer');
      const overlay = document.getElementById('sidebar-overlay');
      const isOpen = drawer.classList.contains('open');
      const target = (typeof force === 'boolean') ? force : !isOpen;

      if (target) {
        drawer.classList.add('open');
        overlay.classList.add('active');
      } else {
        drawer.classList.remove('open');
        overlay.classList.remove('active');
      }
    }

    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('active');
    }

    // --- 🌦️ 실시간 지역 날씨 API 연동 및 배경 전환 ---
    async function fetchLiveFarmWeather() {
      const reg = REGION_MAP[currentRegionKey] || REGION_MAP['nonsan'];
      const regNameEl = document.getElementById('region-current-name');
      if (regNameEl) regNameEl.innerText = reg.name;

      try {
        const url = `https://api.open-meteo.com/v1/forecast?latitude=${reg.lat}&longitude=${reg.lon}&current=temperature_2m,relative_humidity_2m,apparent_temperature,is_day,precipitation,weather_code,wind_speed_10m&timezone=Asia%2FSeoul`;
        const res = await fetch(url);
        const data = await res.json();
        if (data && data.current) {
          const c = data.current;
          const isDay = c.is_day === 1;
          const temp = Math.round(c.temperature_2m * 10) / 10;
          const hum = c.relative_humidity_2m;
          const wind = Math.round(c.wind_speed_10m * 10) / 10;
          const code = c.weather_code;

          let weatherDesc = isDay ? '맑음 ☀️' : '맑은 밤 🌙';
          let weatherIcon = isDay ? '☀️' : '🌙';
          let bgClass = isDay ? 'weather-day-clear' : 'weather-night';

          if (code >= 1 && code <= 3) {
            weatherDesc = isDay ? '구름 조금 ⛅' : '구름 밤 ☁️';
            weatherIcon = isDay ? '⛅' : '☁️';
            bgClass = isDay ? 'weather-day-cloudy' : 'weather-night';
          } else if (code >= 51 && code <= 67 || code >= 80) {
            weatherDesc = '비 내림 🌧️';
            weatherIcon = '🌧️';
            bgClass = 'weather-rain';
          } else if (code >= 71 && code <= 77) {
            weatherDesc = '눈 내림 ❄️';
            weatherIcon = '❄️';
          }

          document.body.className = bgClass;
          if (isTabletFitMode) document.body.classList.add('tablet-fit-mode');

          const weatherIconEl = document.getElementById('weather-icon-live');
          const weatherTextEl = document.getElementById('weather-text-live');
          if (weatherIconEl) weatherIconEl.innerText = weatherIcon;
          if (weatherTextEl) {
            weatherTextEl.innerHTML = `${reg.name} · ${weatherDesc} <span class="env-val">${temp}°C</span> / <span class="env-val">${hum}%</span> (풍속 ${wind}km/h)`;
          }

          // SVG 캔버스 하늘 낮/밤 전환
          const skyNight = document.getElementById('svg-sky-night');
          const skyDay = document.getElementById('svg-sky-day');
          const skyStop1 = document.getElementById('skyStop1');
          const skyStop2 = document.getElementById('skyStop2');

          if (isDay) {
            if (skyNight) skyNight.style.display = 'none';
            if (skyDay) { skyDay.style.display = 'block'; skyDay.setAttribute('opacity', '1'); }
            if (skyStop1) skyStop1.setAttribute('stop-color', '#1E4E7A');
            if (skyStop2) skyStop2.setAttribute('stop-color', '#0C203E');
          } else {
            if (skyNight) { skyNight.style.display = 'block'; skyNight.setAttribute('opacity', '0.8'); }
            if (skyDay) skyDay.style.display = 'none';
            if (skyStop1) skyStop1.setAttribute('stop-color', '#131E3A');
            if (skyStop2) skyStop2.setAttribute('stop-color', '#070B19');
          }
        }
      } catch(e) {}
    }

    function openRegionModal() {
      const sel = document.getElementById('sel-region-preset');
      if (sel) sel.value = currentRegionKey;
      document.getElementById('region-modal').classList.add('active');
    }

    function applyRegionPreset(val) {
      currentRegionKey = val;
    }

    function saveRegionSubmit() {
      const sel = document.getElementById('sel-region-preset');
      currentRegionKey = sel.value;
      localStorage.setItem('nurio_farm_region', currentRegionKey);
      closeModal('region-modal');
      fetchLiveFarmWeather();
      showToast(`📍 [${REGION_MAP[currentRegionKey].name}] 기상 데이터로 실시간 변경되었습니다!`, 'success');
    }

    // --- 🌡️ 동별 온·습도 HUD 및 모니터링 렌더링 ---
    function renderHouseSensorTelemetry() {
      const curData = houseSensorData[currentHouseId] || { temp: 24.5, hum: 62.0, battery: 98, status: '정상' };
      const tempVal = curData.temp;
      const humVal = curData.hum;

      // 1. 좌측 캔버스 HUD 위젯
      const valTwinTemp = document.getElementById('val-twin-temp');
      const valTwinHum = document.getElementById('val-twin-hum');
      const valTwinComfort = document.getElementById('val-twin-comfort');

      if (valTwinTemp) {
        valTwinTemp.innerText = `${tempVal.toFixed(1)}°C`;
        // 색상 판정
        if (tempVal >= 18 && tempVal <= 25) valTwinTemp.style.color = '#34D399'; // 적온
        else if (tempVal > 25 && tempVal <= 28) valTwinTemp.style.color = '#FBBF24'; // 약간 높음
        else if (tempVal > 28) valTwinTemp.style.color = '#F87171'; // 고온 경고
        else valTwinTemp.style.color = '#60A5FA'; // 저온
      }

      if (valTwinHum) {
        valTwinHum.innerText = `${Math.round(humVal)}%`;
      }

      if (valTwinComfort) {
        if (tempVal >= 18 && tempVal <= 25 && humVal >= 60 && humVal <= 75) {
          valTwinComfort.innerText = '😊 쾌적 (딸기 최적 생육)';
          valTwinComfort.style.background = 'rgba(16, 185, 129, 0.2)';
          valTwinComfort.style.color = '#6EE7B7';
        } else if (tempVal > 28) {
          valTwinComfort.innerText = '⚠️ 고온 주의 (차광/환기 권장)';
          valTwinComfort.style.background = 'rgba(239, 68, 68, 0.2)';
          valTwinComfort.style.color = '#FCA5A5';
        } else {
          valTwinComfort.innerText = '🌱 안정 생육 유지 중';
          valTwinComfort.style.background = 'rgba(56, 189, 248, 0.2)';
          valTwinComfort.style.color = '#7DD3FC';
        }
      }

      // 2. 우측 제어반 센서 카드
      const deckValTemp = document.getElementById('deck-val-temp');
      const deckValHum = document.getElementById('deck-val-hum');
      const barTemp = document.getElementById('bar-temp');
      const barHum = document.getElementById('bar-hum');
      const deckBattery = document.getElementById('deck-sensor-battery');
      const deckTitle = document.getElementById('deck-sensor-title');

      if (deckTitle && farmHouses[currentHouseId]) {
        deckTitle.innerText = `${farmHouses[currentHouseId].name} 온·습도 센서`;
      }
      if (deckValTemp) deckValTemp.innerText = `${tempVal.toFixed(1)}°C`;
      if (deckValHum) deckValHum.innerText = `${Math.round(humVal)}%`;
      if (deckBattery) deckBattery.innerText = `🔋 ${curData.battery}%`;

      if (barTemp) {
        const pct = Math.min(100, Math.max(0, (tempVal / 40.0) * 100));
        barTemp.style.width = `${pct}%`;
      }
      if (barHum) {
        const pct = Math.min(100, Math.max(0, humVal));
        barHum.style.width = `${pct}%`;
      }
    }

    // --- 🍓 실시간 비주얼 렌더링 & 시뮬레이션 엔진 ---
    function updateDigitalTwinVisuals() {
      // 1. 좌측/우측 측창 비닐막 롤업 (Motor 1)
      const pos1 = motorPositions[1];
      const leftRoller = document.getElementById('svg-left-roller');
      const rightRoller = document.getElementById('svg-right-roller');
      const leftVinyl = document.getElementById('svg-left-vinyl');
      const rightVinyl = document.getElementById('svg-right-vinyl');
      const lblLeft = document.getElementById('lbl-left-curtain');
      const gearLeft = document.getElementById('gear-left');

      const rollerY = 380 - (pos1 / 100) * 200;
      if (leftRoller) leftRoller.setAttribute('cy', rollerY);
      if (rightRoller) rightRoller.setAttribute('cy', rollerY);

      if (leftVinyl) {
        leftVinyl.setAttribute('d', `M 100 ${rollerY} L 100 240 Q 100 130, 260 110`);
      }
      if (rightVinyl) {
        rightVinyl.setAttribute('d', `M 700 ${rollerY} L 700 240 Q 700 130, 540 110`);
      }

      if (lblLeft) {
        lblLeft.innerText = pos1 === 0 ? '측창 비닐: 0% (완전밀폐)' : (pos1 === 100 ? '측창 비닐: 100% (완전개방)' : `측창 비닐: ${Math.round(pos1)}% 개방`);
      }
      if (gearLeft) {
        gearLeft.style.transform = motorDirections[1] !== 0 ? `rotate(${Date.now() / 5 % 360}deg)` : 'none';
      }

      // 2. 상부 차광막 스크린 (Motor 2)
      const pos2 = motorPositions[2];
      const shadeScreen = document.getElementById('svg-shade-screen');
      const lblTop = document.getElementById('lbl-top-curtain');
      if (shadeScreen) {
        shadeScreen.setAttribute('opacity', 0.1 + (pos2 / 100) * 0.85);
        shadeScreen.setAttribute('stroke-width', 8 + (pos2 / 100) * 32);
      }
      if (lblTop) {
        lblTop.innerText = pos2 === 0 ? '차광막: 0% (해제)' : `차광막: ${Math.round(pos2)}% 차광`;
      }

      // 3. 관수 라인 물방울 & 양액기 파티클
      const drops = document.getElementById('svg-water-drops');
      const dripPipe = document.getElementById('svg-drip-pipe');
      const pumpBadge = document.getElementById('badge-pump-status');
      const lblPump = document.getElementById('lbl-pump-status');

      if (isWaterPumpActive || isNutrientActive) {
        if (drops) drops.style.display = 'block';
        if (dripPipe) dripPipe.setAttribute('stroke', isNutrientActive ? '#A855F7' : '#06B6D4');
        if (pumpBadge) pumpBadge.style.borderColor = '#10B981';
        if (lblPump) lblPump.innerText = isNutrientActive ? '🧪 양액 공급 중' : '💧 관수 분사 중';
      } else {
        if (drops) drops.style.display = 'none';
        if (dripPipe) dripPipe.setAttribute('stroke', '#475569');
        if (pumpBadge) pumpBadge.style.borderColor = 'rgba(255,255,255,0.25)';
        if (lblPump) lblPump.innerText = '양수기: 대기 중';
      }

      // 4. 환풍 유동팬 회전 애니메이션 & 순환 바람결 효과
      const fanGroup = document.getElementById('svg-fan-blades');
      const fanWind = document.getElementById('svg-fan-wind');
      const fanLed = document.getElementById('svg-fan-led');
      if (fanGroup) {
        if (isVentFanActive) {
          fanGroup.classList.add('fan-spinning');
          if (fanWind) fanWind.style.display = 'block';
          if (fanLed) fanLed.setAttribute('fill', '#10B981');
        } else {
          fanGroup.classList.remove('fan-spinning');
          if (fanWind) fanWind.style.display = 'none';
          if (fanLed) fanLed.setAttribute('fill', '#64748B');
        }
      }

      // 5. LED 보광등 빔
      const growLights = document.getElementById('svg-grow-lights');
      if (growLights) {
        growLights.style.display = isGrowLightActive ? 'block' : 'none';
      }

      renderHouseSensorTelemetry();
    }

    // 런타임 타이머 적분 엔진
    setInterval(() => {
      for (let m = 1; m <= 2; m++) {
        if (motorDirections[m] === 1) {
          motorPositions[m] = Math.min(100, motorPositions[m] + 3.5);
          updateSliderUI(m, motorPositions[m]);
          if (motorPositions[m] >= 100) triggerMotorStop(m);
        } else if (motorDirections[m] === -1) {
          motorPositions[m] = Math.max(0, motorPositions[m] - 3.5);
          updateSliderUI(m, motorPositions[m]);
          if (motorPositions[m] <= 0) triggerMotorStop(m);
        }
      }
      updateDigitalTwinVisuals();
    }, 500);

    function updateSliderUI(m, val) {
      const slider = document.getElementById(`slider-motor-${m}`);
      const valText = document.getElementById(`val-motor-${m}`);
      const tag = document.getElementById(`tag-motor-${m}`);
      if (slider) slider.value = val;
      if (valText) valText.innerText = `${Math.round(val)}%`;
      if (tag) {
        tag.innerText = `${Math.round(val)}% (${motorDirections[m] === 1 ? '열림 동작중 ▲' : (motorDirections[m] === -1 ? '닫힘 동작중 ▼' : '정지')})`;
        if (motorDirections[m] !== 0) tag.classList.add('active'); else tag.classList.remove('active');
      }
    }

    function handleSliderChange(m, val) {
      motorPositions[m] = parseInt(val);
      updateSliderUI(m, val);
      updateDigitalTwinVisuals();
    }

    // --- 🎛️ 모터 및 액추에이터 제어 ---
    async function triggerMotorStep(motorNo, action) {
      const chOpen = (motorNo === 1) ? 1 : 3;
      const chClose = (motorNo === 1) ? 2 : 4;
      const targetChannel = (action === 'OPEN') ? chOpen : chClose;

      motorDirections[motorNo] = (action === 'OPEN') ? 1 : -1;
      updateSliderUI(motorNo, motorPositions[motorNo]);

      const btnOpen = document.getElementById(`btn-m${motorNo}-open`);
      const btnClose = document.getElementById(`btn-m${motorNo}-close`);
      if (action === 'OPEN') {
        if (btnOpen) btnOpen.classList.add('active');
        if (btnClose) btnClose.classList.remove('active');
      } else {
        if (btnClose) btnClose.classList.add('active');
        if (btnOpen) btnOpen.classList.remove('active');
      }

      showToast(`🎛️ ${motorNo}호 모터 [${action === 'OPEN' ? '열림' : '닫힘'}] 가동 시작!`, 'success');

      try {
        await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: targetChannel, state: true })
        });
      } catch(e) {}
    }

    async function triggerMotorStop(motorNo) {
      motorDirections[motorNo] = 0;
      updateSliderUI(motorNo, motorPositions[motorNo]);

      const btnOpen = document.getElementById(`btn-m${motorNo}-open`);
      const btnClose = document.getElementById(`btn-m${motorNo}-close`);
      if (btnOpen) btnOpen.classList.remove('active');
      if (btnClose) btnClose.classList.remove('active');

      showToast(`⏸️ ${motorNo}호 모터가 정지되었습니다.`, 'success');

      const chOpen = (motorNo === 1) ? 1 : 3;
      const chClose = (motorNo === 1) ? 2 : 4;

      try {
        await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: chOpen, state: false })
        });
        await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: chClose, state: false })
        });
      } catch(e) {}
    }

    // --- 💧 펌프 및 보조 장치 토글 (실물 투야 플러그와 100% 통합 연동) ---
    const lastActionTimestamp = { 1: 0, 2: 0 };
    const plugAbortControllers = { 1: null, 2: null };

    async function togglePlug(id, num) {
      lastActionTimestamp[num] = Date.now();
      const currState = (num === 1) ? state1 : state2;
      const targetState = !currState;

      if (num === 1) {
        state1 = targetState;
        isWaterPumpActive = targetState;
      } else {
        state2 = targetState;
        isNutrientActive = targetState;
      }

      // 즉각적인 UI 반영 (Optimistic UI)
      updatePlugUI(1, state1);
      updatePlugUI(2, state2);
      updateDigitalTwinVisuals();

      if (plugAbortControllers[num]) {
        plugAbortControllers[num].abort();
      }
      plugAbortControllers[num] = new AbortController();

      try {
        await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, state: targetState }),
          signal: plugAbortControllers[num].signal
        });
      } catch(e) {
        if (e.name !== 'AbortError') {
          // 네트워크 에러 시 롤백 방지는 sync에 위임
        }
      }
    }

    function togglePumpDevice(type) {
      if (type === 'WATER') {
        togglePlug('ebb219afdebea03ba3shlz', 1);
      } else {
        togglePlug('42362638a4e57cb3cd0b', 2);
      }
    }

    function updatePlugUI(num, isActive) {
      const btnBottom = document.getElementById(`plug-btn-${num}`);
      const tagBottom = document.getElementById(`plug-state-${num}`);

      if (num === 1) {
        const btnTop = document.getElementById('pump-unit-water');
        const badgeTop = document.getElementById('badge-pump-water');
        if (isActive) {
          if (btnTop) btnTop.classList.add('active');
          if (badgeTop) badgeTop.innerText = '가동 중 (ON)';
          if (btnBottom) btnBottom.classList.add('active');
          if (tagBottom) tagBottom.innerText = 'ON';
        } else {
          if (btnTop) btnTop.classList.remove('active');
          if (badgeTop) badgeTop.innerText = '정지 (OFF)';
          if (btnBottom) btnBottom.classList.remove('active');
          if (tagBottom) tagBottom.innerText = 'OFF';
        }
      } else {
        const btnTop = document.getElementById('pump-unit-nutrient');
        const badgeTop = document.getElementById('badge-pump-nutrient');
        if (isActive) {
          if (btnTop) btnTop.classList.add('active');
          if (badgeTop) badgeTop.innerText = '공급 중 (ON)';
          if (btnBottom) btnBottom.classList.add('active');
          if (tagBottom) tagBottom.innerText = 'ON';
        } else {
          if (btnTop) btnTop.classList.remove('active');
          if (badgeTop) badgeTop.innerText = '대기 (OFF)';
          if (btnBottom) btnBottom.classList.remove('active');
          if (tagBottom) tagBottom.innerText = 'OFF';
        }
      }
    }

    function toggleAuxDevice(type) {
      if (type === 'FAN') {
        isVentFanActive = !isVentFanActive;
        const btn = document.getElementById('pump-unit-fan');
        const badge = document.getElementById('badge-aux-fan');
        if (isVentFanActive) {
          if (btn) btn.classList.add('active');
          if (badge) badge.innerText = '회전 중 (ON)';
        } else {
          if (btn) btn.classList.remove('active');
          if (badge) badge.innerText = '정지 (OFF)';
        }
      } else {
        isGrowLightActive = !isGrowLightActive;
        const btn = document.getElementById('pump-unit-light');
        const badge = document.getElementById('badge-aux-light');
        if (isGrowLightActive) {
          if (btn) btn.classList.add('active');
          if (badge) badge.innerText = '점등 중 (ON)';
        } else {
          if (btn) btn.classList.remove('active');
          if (badge) badge.innerText = '소등 (OFF)';
        }
      }
      updateDigitalTwinVisuals();
    }

    // --- 📡 백엔드 상태 및 투야 온습도 센서 동기화 ---
    async function syncStatusFromDb() {
      try {
        const res = await fetch(`api.php?action=get_status&_t=${Date.now()}`);
        const data = await res.json();
        if (data.success) {
          farmHouses = data.houses || {};
          renderHouseTabs();

          const now = Date.now();

          // 1. 스마트 플러그 1 (양수기) - 사용자 조작 4초 이내에는 폴링 덮어쓰기 방지
          if (data.devices['ebb219afdebea03ba3shlz']) {
            const d1 = data.devices['ebb219afdebea03ba3shlz'];
            const nameEl = document.getElementById('plug-name-1');
            if (nameEl) nameEl.innerText = d1.name;
            if (now - lastActionTimestamp[1] > 4000) {
              state1 = d1.state;
              isWaterPumpActive = state1;
              updatePlugUI(1, state1);
            }
          }

          // 2. 스마트 플러그 2 (양액기) - 사용자 조작 4초 이내에는 폴링 덮어쓰기 방지
          if (data.devices['42362638a4e57cb3cd0b']) {
            const d2 = data.devices['42362638a4e57cb3cd0b'];
            const nameEl = document.getElementById('plug-name-2');
            if (nameEl) nameEl.innerText = d2.name;
            if (now - lastActionTimestamp[2] > 4000) {
              state2 = d2.state;
              isNutrientActive = state2;
              updatePlugUI(2, state2);
            }
          }

          // 3. 4채널 스위치
          if (data.devices[DEVICE_ID_4CH]) {
            const d4 = data.devices[DEVICE_ID_4CH];
            if (d4.channels) {
              for (let c = 1; c <= 4; c++) {
                if (d4.channels[c]) states4ch[c] = d4.channels[c].state;
              }
              if (states4ch[1]) motorDirections[1] = 1;
              else if (states4ch[2]) motorDirections[1] = -1;
              else if (motorDirections[1] !== 0 && !states4ch[1] && !states4ch[2]) motorDirections[1] = 0;

              if (states4ch[3]) motorDirections[2] = 1;
              else if (states4ch[4]) motorDirections[2] = -1;
              else if (motorDirections[2] !== 0 && !states4ch[3] && !states4ch[4]) motorDirections[2] = 0;
            }
          }

          // 4. 투야 온·습도 센서 실시간 바인딩
          Object.keys(data.devices || {}).forEach(dId => {
            const dev = data.devices[dId];
            if (dev.temperature !== undefined && dev.temperature !== null) {
              houseSensorData[1].temp = dev.temperature;
              if (dev.humidity !== undefined && dev.humidity !== null) houseSensorData[1].hum = dev.humidity;
              if (dev.battery !== undefined && dev.battery !== null) houseSensorData[1].battery = dev.battery;
            }
          });

          const activeCount = (state1?1:0) + (state2?1:0) + Object.values(states4ch).filter(Boolean).length;
          document.getElementById('active-summary').innerText = `${activeCount}개 장비 정상 가동 중`;
          updateDigitalTwinVisuals();
        }
      } catch(e) {}
    }

    function renderHouseTabs() {
      const container = document.getElementById('house-tabs-container');
      if (!container) return;

      const keys = Object.keys(farmHouses);
      if (keys.length === 0) {
        container.innerHTML = `
          <button class="house-tab-btn active">🍓 1동 설향 딸기 (24.5°C / 62%)</button>
          <button class="house-tab-btn" onclick="openHouseModal()">➕ 하우스 추가</button>
        `;
        return;
      }

      let html = '';
      keys.forEach(hId => {
        const h = farmHouses[hId];
        const isActive = (parseInt(currentHouseId) === parseInt(h.id));
        const sData = houseSensorData[h.id] || { temp: 24.5, hum: 62 };
        html += `
          <button class="house-tab-btn ${isActive ? 'active' : ''}" onclick="selectHouseTab(${h.id})">
            <span>🍓</span><span>${h.name}</span>
            <span style="font-size:11px; color:#38BDF8; font-weight:800;">(${sData.temp.toFixed(1)}°C / ${Math.round(sData.hum)}%)</span>
          </button>
        `;
      });
      html += `<button class="house-tab-btn" onclick="openHouseModal()">➕ 하우스 추가</button>`;
      container.innerHTML = html;
    }

    function selectHouseTab(houseId) {
      currentHouseId = houseId;
      renderHouseTabs();
      if (farmHouses[houseId]) {
        const h = farmHouses[houseId];
        document.getElementById('twin-house-title').innerText = h.name;
        document.getElementById('twin-crop-badge').innerText = h.crop || '작물 미지정';
        renderHouseSensorTelemetry();
        showToast(`📍 '${h.name}' 온·습도 및 디지털 트윈 뷰로 전환되었습니다.`, 'success');
      }
    }

    // --- 팝업 모달 핸들러 ---
    function openHouseModal(id = 0) {
      document.getElementById('h-form-id').value = id;
      if (id > 0 && farmHouses[id]) {
        const h = farmHouses[id];
        document.getElementById('house-modal-title').innerText = '🏗️ 비닐하우스 편집';
        document.getElementById('h-form-name').value = h.name;
        document.getElementById('h-form-crop').value = h.crop || '';
        document.getElementById('h-form-memo').value = h.memo || '';
      } else {
        document.getElementById('house-modal-title').innerText = '🏗️ 신규 비닐하우스 추가';
        document.getElementById('h-form-name').value = `🍓 ${Object.keys(farmHouses).length + 1}동 하우스`;
        document.getElementById('h-form-crop').value = '딸기 (설향)';
        document.getElementById('h-form-memo').value = '';
      }
      document.getElementById('house-modal').classList.add('active');
    }

    async function saveHouseSubmit() {
      const id = parseInt(document.getElementById('h-form-id').value) || 0;
      const name = document.getElementById('h-form-name').value.trim();
      const crop = document.getElementById('h-form-crop').value.trim();
      const memo = document.getElementById('h-form-memo').value.trim();

      if (!name) { alert('하우스 명칭을 입력해 주세요.'); return; }

      try {
        const res = await fetch('api.php?action=save_house', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id, name, crop, memo })
        });
        const data = await res.json();
        if (data.success) {
          closeModal('house-modal');
          showToast(`✅ '${name}' 하우스가 저장되었습니다!`, 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
    }

    function openDeviceModal() {
      const select = document.getElementById('d-form-house-id');
      select.innerHTML = '';
      Object.keys(farmHouses).forEach(hId => {
        const h = farmHouses[hId];
        const opt = document.createElement('option');
        opt.value = h.id;
        opt.innerText = h.name;
        select.appendChild(opt);
      });
      document.getElementById('device-modal').classList.add('active');
    }

    async function saveDeviceSubmit() {
      const houseId = parseInt(document.getElementById('d-form-house-id').value) || 1;
      const category = document.getElementById('d-form-category').value;
      const name = document.getElementById('d-form-name').value.trim();
      const bindingStr = document.getElementById('d-form-binding').value;

      if (!name) { alert('장비 명칭을 입력해 주세요.'); return; }

      let boundDeviceId = null;
      let boundChannelNo = 1;
      if (bindingStr) {
        const parts = bindingStr.split(':');
        boundDeviceId = parts[0];
        boundChannelNo = parseInt(parts[1]) || 1;
      }

      try {
        const res = await fetch('api.php?action=save_house_device', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            house_id: houseId, category, name, bound_device_id: boundDeviceId, bound_channel_no: boundChannelNo
          })
        });
        const data = await res.json();
        if (data.success) {
          closeModal('device-modal');
          showToast(`✅ '${name}' 장비가 등록되었습니다!`, 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
    }

    function openInterlockModal() {
      document.getElementById('interlock-modal').classList.add('active');
    }

    async function saveInterlockSubmit() {
      const selected = document.querySelector('input[name="interlock_preset"]:checked').value;
      const groups = (selected === '2x2') ? [[1, 2], [3, 4]] : [];

      try {
        const res = await fetch('api.php?action=set_interlock', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, groups: groups })
        });
        const data = await res.json();
        if (data.success) {
          closeModal('interlock-modal');
          showToast(`🔒 인터락 설정이 투야 클라우드 및 앱에 실시간 반영되었습니다!`, 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
    }

    function showToast(message, type = 'success') {
      // 📱 태블릿 버튼 가림 방지를 위해 토스트 팝업 완전 비활성화 (버튼 자체 즉시 반응)
      console.log(`[Nurio Smart Farm] ${message}`);
    }

    document.addEventListener('DOMContentLoaded', () => {
      initTabletFitMode();
      fetchLiveFarmWeather();
      syncStatusFromDb();
      updateDigitalTwinVisuals();
      setInterval(fetchLiveFarmWeather, 60000);
    });
    setInterval(syncStatusFromDb, 3000);
  </script>
</body>
</html>
