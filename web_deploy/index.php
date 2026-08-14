<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>🍓 누리오 스마트팜 (Nurio Smart Farm) - 통합 IoT 관제 시스템</title>
  
  <!-- 📱 PWA & 태블릿 앱 바로가기 메타태그 및 매니페스트 -->
  <link rel="manifest" href="manifest.json">
  <link rel="icon" type="image/svg+xml" href="icon.svg">
  <link rel="apple-touch-icon" href="icon.svg">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#1E293B">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
  <style>
    :root {
      --bg-base: #0F172A;
      --bg-card: #1E293B;
      --bg-card-sub: #162032;
      --primary: #10B981;
      --primary-light: rgba(16, 185, 129, 0.15);
      --primary-border: #059669;
      --cyan: #06B6D4;
      --indigo: #6366F1;
      --amber: #F59E0B;
      --rose: #F43F5E;
      --text-primary: #F8FAFC;
      --text-secondary: #94A3B8;
      --text-muted: #64748B;
      --border: rgba(255, 255, 255, 0.1);
      --border-focus: rgba(16, 185, 129, 0.5);
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
    }

    /* 📱 상단 글로벌 내비게이션 바 (태블릿/모바일 최적화) */
    header {
      background: #1E293B;
      border-bottom: 1px solid var(--border);
      padding: 14px 20px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
      backdrop-filter: blur(12px);
    }
    .header-left { display: flex; align-items: center; gap: 14px; }
    .btn-hamburger {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid var(--border);
      color: white;
      border-radius: 8px;
      padding: 8px 12px;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 18px;
      transition: background 0.2s;
    }
    .btn-hamburger:hover { background: rgba(255, 255, 255, 0.16); }
    .brand-title-box { display: flex; align-items: center; gap: 10px; }
    .brand-logo { font-size: 26px; }
    .brand-name { font-size: 18px; font-weight: 800; color: #F8FAFC; letter-spacing: -0.5px; }
    .brand-sub { font-size: 11px; color: var(--primary); font-weight: 700; }

    .header-right { display: flex; align-items: center; gap: 10px; }
    .status-pill {
      background: rgba(16, 185, 129, 0.15);
      border: 1px solid #10B981;
      color: #6EE7B7;
      font-size: 12px;
      font-weight: 700;
      padding: 6px 14px;
      border-radius: 20px;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .btn-action-header {
      background: #10B981;
      border: none;
      color: white;
      border-radius: 8px;
      padding: 8px 14px;
      font-size: 13px;
      font-weight: 800;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: background 0.2s, transform 0.1s;
    }
    .btn-action-header:hover { background: #059669; }
    .btn-action-header:active { transform: scale(0.97); }

    /* 📂 서랍형 오버레이 사이드바 (태블릿에서는 닫혀있다가 열림) */
    .sidebar-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.6);
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
      background: #162032;
      border-right: 1px solid var(--border);
      padding: 24px 20px;
      display: flex;
      flex-direction: column;
      gap: 24px;
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
      border-radius: 10px; color: var(--text-secondary); font-weight: 600; cursor: pointer; transition: all 0.2s; font-size: 14px;
    }
    .nav-item:hover { background: rgba(255, 255, 255, 0.06); color: white; }
    .nav-item.active { background: var(--primary-light); color: var(--primary); font-weight: 800; border: 1px solid var(--primary-border); }

    /* 📊 메인 풀스크린 대시보드 뷰 (태블릿PC 100% 장치 집중) */
    main {
      flex: 1;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 22px;
      max-width: 1600px;
      margin: 0 auto;
      width: 100%;
    }

    .section-title-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 2px;
    }
    .section-title { font-size: 18px; font-weight: 800; color: #F8FAFC; display: flex; align-items: center; gap: 8px; }
    .section-sub { font-size: 12px; color: var(--text-secondary); }

    /* 🎛️ 실물 투야 하드웨어 그리드 */
    .physical-devices-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 16px;
    }
    @media (max-width: 1024px) {
      .physical-devices-grid { grid-template-columns: 1fr; }
    }

    .hardware-card {
      background: var(--bg-card);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    }
    .hardware-card.switch4ch {
      grid-column: 1 / -1;
      border-color: rgba(6, 182, 212, 0.4);
      background: linear-gradient(135deg, #162032 0%, #0F172A 100%);
    }

    .card-header-flex { display: flex; justify-content: space-between; align-items: center; }
    .device-title-box { display: flex; align-items: center; gap: 12px; }
    .device-icon { font-size: 28px; }
    .device-name-text { font-size: 17px; font-weight: 800; color: #F8FAFC; }
    .device-id-text { font-size: 11px; color: var(--text-muted); }

    .btn-edit-sm {
      background: rgba(255, 255, 255, 0.1); border: 1px solid var(--border); color: white;
      border-radius: 6px; padding: 3px 8px; font-size: 11px; font-weight: 700; cursor: pointer;
    }
    .btn-edit-sm:hover { background: rgba(255, 255, 255, 0.2); }

    /* 4채널 멀티 릴레이 컨트롤 박스 */
    .multi-ch-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 12px;
    }
    @media (max-width: 800px) {
      .multi-ch-grid { grid-template-columns: repeat(2, 1fr); }
    }

    /* 🔒 인터락 그룹핑 시각화 */
    .interlock-badge-btn {
      background: rgba(8, 145, 178, 0.18);
      border: 1px solid #22D3EE;
      color: #22D3EE;
      font-size: 11px;
      font-weight: 800;
      padding: 4px 10px;
      border-radius: 8px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 6px;
      transition: all 0.2s;
    }
    .interlock-badge-btn:hover {
      background: rgba(8, 145, 178, 0.4);
      box-shadow: 0 0 12px rgba(6, 182, 212, 0.5);
    }
    .interlock-groups-container {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 14px;
      width: 100%;
    }
    @media (max-width: 768px) {
      .interlock-groups-container { grid-template-columns: 1fr; }
    }
    .interlock-group-card {
      background: rgba(0, 0, 0, 0.3);
      border: 1.5px dashed rgba(6, 182, 212, 0.4);
      border-radius: 14px;
      padding: 12px;
      display: flex;
      flex-direction: column;
      gap: 10px;
    }
    .interlock-group-card.group-b {
      border-color: rgba(129, 140, 248, 0.4);
    }
    .interlock-group-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 0 2px;
    }
    .interlock-group-tag {
      font-size: 11px;
      font-weight: 800;
      color: #22D3EE;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .group-b .interlock-group-tag {
      color: #A5B4FC;
    }
    .interlock-group-desc {
      font-size: 10px;
      color: var(--text-muted);
    }
    .interlock-inner-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 10px;
    }

    .ch-unit-box {
      background: rgba(0, 0, 0, 0.3);
      border: 1.5px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 14px;
      display: flex;
      flex-direction: column;
      gap: 10px;
      transition: all 0.2s;
    }
    .ch-unit-box:hover { border-color: rgba(6, 182, 212, 0.5); }
    .ch-unit-header { display: flex; justify-content: space-between; align-items: center; }
    .ch-badge { background: #0891B2; color: #ECFEFF; font-size: 10px; font-weight: 800; padding: 2px 6px; border-radius: 4px; }
    .ch-name { font-size: 12px; font-weight: 700; color: #F1F5F9; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .ch-touch-pad {
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(255, 255, 255, 0.04); border-radius: 10px; padding: 10px 12px; cursor: pointer; user-select: none;
      transition: all 0.2s;
    }
    .ch-touch-pad:hover { background: rgba(255, 255, 255, 0.1); transform: scale(1.02); }
    .ch-touch-pad:active { transform: scale(0.96); }

    .neon-ring-sm {
      position: relative; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
    }
    .neon-ring-sm .ring {
      position: absolute; inset: 0; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.2); transition: all 0.3s;
    }
    .neon-ring-sm.active .ring {
      border-color: #22D3EE; box-shadow: 0 0 16px #06B6D4, inset 0 0 10px rgba(6, 182, 212, 0.6);
    }
    .ch-status-pill {
      font-size: 11px; font-weight: 800; padding: 4px 10px; border-radius: 12px; background: rgba(255,255,255,0.08);
      color: #94A3B8; border: 1px solid rgba(255,255,255,0.1);
    }
    .ch-status-pill.active {
      background: #0891B2; color: #FFFFFF; border-color: #22D3EE; box-shadow: 0 2px 8px rgba(6, 182, 212, 0.5);
    }

    /* 플러그 카드 파워 터치 박스 */
    .plug-touch-box {
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(0, 0, 0, 0.25); border-radius: 12px; padding: 14px 18px; cursor: pointer; user-select: none;
      border: 1px solid rgba(255, 255, 255, 0.08); transition: all 0.2s;
    }
    .plug-touch-box:hover { background: rgba(0, 0, 0, 0.4); transform: scale(1.01); }
    .plug-touch-box:active { transform: scale(0.98); }
    .neon-ring-lg {
      position: relative; width: 52px; height: 52px; display: flex; align-items: center; justify-content: center;
    }
    .neon-ring-lg .ring {
      position: absolute; inset: 0; border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.2); transition: all 0.3s;
    }
    .neon-ring-lg.active .ring {
      border-color: #34D399; box-shadow: 0 0 20px #10B981, inset 0 0 12px rgba(16, 185, 129, 0.6);
    }
    .plug2 .neon-ring-lg.active .ring {
      border-color: #818CF8; box-shadow: 0 0 20px #6366F1, inset 0 0 12px rgba(99, 102, 241, 0.6);
    }

    /* 로딩 애니메이션 */
    @keyframes pulseGlow { 0% { opacity: 0.5; transform: scale(0.97); } 50% { opacity: 1; transform: scale(1.03); } 100% { opacity: 0.5; transform: scale(0.97); } }
    .pending { animation: pulseGlow 0.8s infinite ease-in-out; }

    /* 🍓 동적 비닐하우스 스마트 장치 카드 그리드 */
    .houses-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(360px, 1fr));
      gap: 18px;
    }

    .house-container-card {
      background: var(--bg-card);
      border: 1.5px solid var(--border);
      border-radius: 16px;
      padding: 20px;
      display: flex;
      flex-direction: column;
      gap: 16px;
      box-shadow: 0 6px 20px rgba(0,0,0,0.25);
    }
    .house-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      border-bottom: 1px solid var(--border);
      padding-bottom: 12px;
    }
    .house-title { font-size: 17px; font-weight: 800; color: #F8FAFC; display: flex; align-items: center; gap: 8px; }
    .house-crop-tag {
      background: rgba(16, 185, 129, 0.2); border: 1px solid #10B981; color: #6EE7B7;
      font-size: 11px; font-weight: 800; padding: 3px 8px; border-radius: 6px;
    }

    /* 하우스 내부 장치 아이템 리스트 */
    .house-device-list { display: flex; flex-direction: column; gap: 10px; }
    .house-device-item {
      background: rgba(0, 0, 0, 0.25);
      border: 1px solid rgba(255, 255, 255, 0.08);
      border-radius: 12px;
      padding: 12px 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
    }
    .hdev-info { display: flex; align-items: center; gap: 10px; flex: 1; }
    .hdev-icon { font-size: 22px; }
    .hdev-name { font-size: 13px; font-weight: 700; color: #F1F5F9; }
    .hdev-sub { font-size: 11px; color: var(--text-muted); }

    /* 개폐기(차광막/비닐막) 컨트롤러 */
    .curtain-controls { display: flex; align-items: center; gap: 6px; }
    .btn-curtain-step {
      background: rgba(255, 255, 255, 0.1); border: 1px solid var(--border); color: white;
      border-radius: 6px; padding: 6px 10px; font-size: 11px; font-weight: 800; cursor: pointer;
    }
    .btn-curtain-step:hover { background: rgba(255, 255, 255, 0.2); }

    /* 팝업 모달 스타일 */
    .modal-overlay {
      position: fixed; inset: 0; background: rgba(0, 0, 0, 0.7); backdrop-filter: blur(6px);
      z-index: 500; display: none; align-items: center; justify-content: center; padding: 20px;
    }
    .modal-overlay.active { display: flex; }
    .modal-content {
      background: #1E293B; border: 1.5px solid var(--border); border-radius: 16px;
      width: 100%; max-width: 520px; padding: 24px; display: flex; flex-direction: column; gap: 18px;
      box-shadow: 0 20px 40px rgba(0,0,0,0.6);
    }
    .modal-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid var(--border); padding-bottom: 12px; }
    .modal-title { font-size: 17px; font-weight: 800; color: #F8FAFC; }
    .form-group { display: flex; flex-direction: column; gap: 6px; }
    .form-label { font-size: 12px; font-weight: 700; color: var(--text-secondary); }
    .form-input, .form-select {
      background: #0F172A; border: 1px solid var(--border); color: white; border-radius: 8px;
      padding: 10px 12px; font-size: 14px; outline: none; transition: border-color 0.2s;
    }
    .form-input:focus, .form-select:focus { border-color: var(--primary); }
    .modal-btn-row { display: flex; justify-content: flex-end; gap: 10px; margin-top: 8px; }
    .btn-modal-cancel { background: rgba(255, 255, 255, 0.1); border: none; color: white; padding: 10px 16px; border-radius: 8px; font-weight: 700; cursor: pointer; }
    .btn-modal-save { background: #10B981; border: none; color: white; padding: 10px 20px; border-radius: 8px; font-weight: 800; cursor: pointer; }

    /* 토스트 알림 */
    .toast-container { position: fixed; top: 20px; right: 20px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
    .toast {
      pointer-events: auto; background: #064E3B; color: #FFFFFF; padding: 12px 18px; border-radius: 10px; font-size: 13px; font-weight: 700;
      box-shadow: 0 10px 25px rgba(0,0,0,0.4); display: flex; align-items: center; gap: 10px; border-left: 4px solid #34D399;
      opacity: 0; transform: translateY(-15px); transition: all 0.3s ease;
    }
    .toast.show { opacity: 1; transform: translateY(0); }
  </style>
</head>
<body>

  <div class="toast-container" id="toast-container"></div>

  <!-- 📱 상단 글로벌 헤더 -->
  <header>
    <div class="header-left">
      <button class="btn-hamburger" onclick="toggleSidebar()" title="메뉴 열기/닫기">☰</button>
      <div class="brand-title-box">
        <span class="brand-logo">🍓</span>
        <div>
          <div class="brand-name">누리오 스마트팜</div>
          <div class="brand-sub">● 실시간 통합 IoT 관제</div>
        </div>
      </div>
    </div>

    <div class="header-right">
      <div class="status-pill">
        <span>⚡</span><span id="active-summary">0개 장치 가동 중</span>
      </div>
      <button class="btn-action-header" style="background:#0891B2;" onclick="openPwaGuideModal()">
        <span>📲</span><span>앱 설치 / 바로가기</span>
      </button>
      <button class="btn-action-header" onclick="openHouseModal()">
        <span>➕</span><span>하우스 추가</span>
      </button>
    </div>
  </header>

  <!-- 📂 서랍형 오버레이 사이드바 -->
  <div class="sidebar-overlay" id="sidebar-overlay" onclick="toggleSidebar(false)"></div>
  <aside id="sidebar-drawer">
    <div class="sidebar-header">
      <div style="display:flex; align-items:center; gap:8px;">
        <span style="font-size:22px;">🍓</span>
        <span style="font-weight:800; font-size:16px;">누리오 스마트팜</span>
      </div>
      <button class="btn-edit-sm" onclick="toggleSidebar(false)">✕ 닫기</button>
    </div>

    <ul class="nav-list">
      <li class="nav-item active" onclick="toggleSidebar(false)">🏠 스마트팜 메인 관제</li>
      <li class="nav-item" onclick="openHouseModal(); toggleSidebar(false);">🏗️ 비닐하우스 관리/추가</li>
      <li class="nav-item" onclick="openDeviceModal(); toggleSidebar(false);">⚙️ 스마트 농가 장비 설정</li>
      <li class="nav-item" style="color:#22D3EE; font-weight:800;" onclick="openPwaGuideModal(); toggleSidebar(false);">📲 태블릿/폰 홈화면 앱 설치</li>
      <li class="nav-item" onclick="showToast('📹 CCTV 실시간 연동 준비 완료', 'success'); toggleSidebar(false);">📹 하우스 CCTV 스트림</li>
      <li class="nav-item" onclick="showToast('⚡ 자동 관수/환풍 규칙 설정 준비 완료', 'success'); toggleSidebar(false);">⚡ 지능형 환경 자동화</li>
    </ul>

    <div style="margin-top:auto; padding:12px; background:rgba(0,0,0,0.3); border-radius:10px; font-size:11px; color:var(--text-muted);">
      <div>🌐 iwinv 365일 24시간 호스팅</div>
      <div style="margin-top:4px;">📱 태블릿PC 터치 최적화 UI</div>
    </div>
  </aside>

  <!-- 📊 메인 풀스크린 대시보드 (태블릿 100% 장치 중심) -->
  <main>

    <!-- 🎛️ 1. 실물 투야 하드웨어 관제 섹션 -->
    <div class="section-title-bar">
      <div class="section-title">
        <span>🔌</span><span>실물 투야 하드웨어 직접 제어 (스마트 스위치 & 플러그)</span>
      </div>
      <div class="section-sub">⚡ 인터락(Interlock 1-2, 3-4) 및 스마트폰 앱 양방향 100% 실시간 동기화</div>
    </div>

    <div class="physical-devices-grid">
      <!-- 4채널 멀티 스위치 -->
      <div class="hardware-card switch4ch">
        <div class="card-header-flex">
          <div class="device-title-box">
            <span class="device-icon">🎛️</span>
            <div>
              <div style="display: flex; align-items: center; gap: 8px;">
                <span class="device-name-text" id="name-display-4ch">4채널 멀티 스위치</span>
                <button class="btn-edit-sm" onclick="promptRename('eb654aa2437462ea40dfjw', '4ch')">✏️ 수정</button>
              </div>
              <div class="device-id-text">ID: eb654aa2437462ea40dfjw · 4채널 스마트 릴레이</div>
            </div>
          </div>
          <div style="display:flex; align-items:center; gap:8px;">
            <button class="interlock-badge-btn" id="interlock-summary-btn" onclick="openInterlockModal()" title="인터락 설정 열기">
              <span>🔒</span><span id="interlock-badge-text">인터락: [1↔2] [3↔4]</span><span>⚙️</span>
            </button>
            <button class="btn-edit-sm" style="background:#0891B2; border-color:#22D3EE;" onclick="toggleAll4Ch(true)">⚡ 전체 ON</button>
            <button class="btn-edit-sm" onclick="toggleAll4Ch(false)">⛔ 전체 OFF</button>
          </div>
        </div>

        <!-- 🔒 인터락 그룹 시각화 그리드 -->
        <div class="interlock-groups-container" id="interlock-groups-wrapper">
          <!-- 인터락 그룹 1 (CH1 & CH2) -->
          <div class="interlock-group-card" id="interlock-card-g1">
            <div class="interlock-group-header">
              <span class="interlock-group-tag" id="itag-1">🔒 인터락 그룹 [1번 ↔ 2번 묶음]</span>
              <span class="interlock-group-desc" id="idesc-1">⚡ 상호 배타 잠금 (1번 켜면 2번 자동 OFF)</span>
            </div>
            <div class="interlock-inner-grid">
              <!-- CH 1 -->
              <div class="ch-unit-box" id="ch-box-1">
                <div class="ch-unit-header">
                  <span class="ch-badge">CH 1</span>
                  <div class="ch-name" id="ch-name-1">1번 채널</div>
                  <button class="btn-edit-sm" style="padding:2px 4px; font-size:9px;" onclick="promptRenameChannel(1)">✏️</button>
                </div>
                <div class="ch-touch-pad" id="ch-pad-1" onclick="toggle4Ch(1)">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <div class="neon-ring-sm" id="ch-ring-1">
                      <div class="ring"></div>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
                    </div>
                    <span style="font-size:11px; color:#CFFAFE;" id="ch-sub-1">터치 제어</span>
                  </div>
                  <span class="ch-status-pill" id="ch-tag-1">OFF</span>
                </div>
              </div>

              <!-- CH 2 -->
              <div class="ch-unit-box" id="ch-box-2">
                <div class="ch-unit-header">
                  <span class="ch-badge">CH 2</span>
                  <div class="ch-name" id="ch-name-2">2번 채널</div>
                  <button class="btn-edit-sm" style="padding:2px 4px; font-size:9px;" onclick="promptRenameChannel(2)">✏️</button>
                </div>
                <div class="ch-touch-pad" id="ch-pad-2" onclick="toggle4Ch(2)">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <div class="neon-ring-sm" id="ch-ring-2">
                      <div class="ring"></div>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
                    </div>
                    <span style="font-size:11px; color:#CFFAFE;" id="ch-sub-2">터치 제어</span>
                  </div>
                  <span class="ch-status-pill" id="ch-tag-2">OFF</span>
                </div>
              </div>
            </div>
          </div>

          <!-- 인터락 그룹 2 (CH3 & CH4) -->
          <div class="interlock-group-card group-b" id="interlock-card-g2">
            <div class="interlock-group-header">
              <span class="interlock-group-tag" id="itag-2">🔒 인터락 그룹 [3번 ↔ 4번 묶음]</span>
              <span class="interlock-group-desc" id="idesc-2">⚡ 상호 배타 잠금 (3번 켜면 4번 자동 OFF)</span>
            </div>
            <div class="interlock-inner-grid">
              <!-- CH 3 -->
              <div class="ch-unit-box" id="ch-box-3">
                <div class="ch-unit-header">
                  <span class="ch-badge">CH 3</span>
                  <div class="ch-name" id="ch-name-3">3번 채널</div>
                  <button class="btn-edit-sm" style="padding:2px 4px; font-size:9px;" onclick="promptRenameChannel(3)">✏️</button>
                </div>
                <div class="ch-touch-pad" id="ch-pad-3" onclick="toggle4Ch(3)">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <div class="neon-ring-sm" id="ch-ring-3">
                      <div class="ring"></div>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
                    </div>
                    <span style="font-size:11px; color:#CFFAFE;" id="ch-sub-3">터치 제어</span>
                  </div>
                  <span class="ch-status-pill" id="ch-tag-3">OFF</span>
                </div>
              </div>

              <!-- CH 4 -->
              <div class="ch-unit-box" id="ch-box-4">
                <div class="ch-unit-header">
                  <span class="ch-badge">CH 4</span>
                  <div class="ch-name" id="ch-name-4">4번 채널</div>
                  <button class="btn-edit-sm" style="padding:2px 4px; font-size:9px;" onclick="promptRenameChannel(4)">✏️</button>
                </div>
                <div class="ch-touch-pad" id="ch-pad-4" onclick="toggle4Ch(4)">
                  <div style="display:flex; align-items:center; gap:8px;">
                    <div class="neon-ring-sm" id="ch-ring-4">
                      <div class="ring"></div>
                      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.5"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
                    </div>
                    <span style="font-size:11px; color:#CFFAFE;" id="ch-sub-4">터치 제어</span>
                  </div>
                  <span class="ch-status-pill" id="ch-tag-4">OFF</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- 플러그 1 [책상등] -->
      <div class="hardware-card">
        <div class="card-header-flex">
          <div class="device-title-box">
            <span class="device-icon">💡</span>
            <div>
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="device-name-text" id="name-display-1">책상등</span>
                <button class="btn-edit-sm" onclick="promptRename('ebb219afdebea03ba3shlz', 1)">✏️</button>
              </div>
              <div class="device-id-text">ID: ebb219afdebea03ba3shlz</div>
            </div>
          </div>
          <span style="font-size:12px; color:#34D399; font-weight:800;" id="power-1">0.0 W</span>
        </div>

        <div class="plug-touch-box" id="btn-container-1" onclick="togglePlug('ebb219afdebea03ba3shlz', 1)">
          <div style="display:flex; align-items:center; gap:14px;">
            <div class="neon-ring-lg" id="local-ring-1">
              <div class="ring"></div>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
            </div>
            <div>
              <div style="font-size:14px; font-weight:800;" id="label-1">전원 터치 작동</div>
              <div style="font-size:11px; color:var(--text-muted);" id="sub-msg-1">클릭 시 즉시 전환</div>
            </div>
          </div>
          <span class="ch-status-pill" id="status-tag-1">OFF</span>
        </div>
      </div>

      <!-- 플러그 2 [3D 프린터] -->
      <div class="hardware-card plug2">
        <div class="card-header-flex">
          <div class="device-title-box">
            <span class="device-icon">🖨️</span>
            <div>
              <div style="display:flex; align-items:center; gap:8px;">
                <span class="device-name-text" id="name-display-2">3D프린터</span>
                <button class="btn-edit-sm" onclick="promptRename('42362638a4e57cb3cd0b', 2)">✏️</button>
              </div>
              <div class="device-id-text">ID: 42362638a4e57cb3cd0b</div>
            </div>
          </div>
          <span style="font-size:12px; color:#818CF8; font-weight:800;" id="power-2">0.0 W</span>
        </div>

        <div class="plug-touch-box" id="btn-container-2" onclick="togglePlug('42362638a4e57cb3cd0b', 2)">
          <div style="display:flex; align-items:center; gap:14px;">
            <div class="neon-ring-lg" id="local-ring-2">
              <div class="ring"></div>
              <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6"><path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/></svg>
            </div>
            <div>
              <div style="font-size:14px; font-weight:800;" id="label-2">전원 터치 작동</div>
              <div style="font-size:11px; color:var(--text-muted);" id="sub-msg-2">클릭 시 즉시 전환</div>
            </div>
          </div>
          <span class="ch-status-pill" id="status-tag-2">OFF</span>
        </div>
      </div>
    </div>

    <!-- 🍓 2. 동적 비닐하우스 & 농가 장비 관리 섹션 -->
    <div class="section-title-bar" style="margin-top:10px;">
      <div class="section-title">
        <span>🍓</span><span>누리오 비닐하우스별 스마트 장비 허브 (차단막 · 비닐막 · 양수기 · 양액기)</span>
      </div>
      <div style="display:flex; gap:8px;">
        <button class="btn-edit-sm" style="background:#10B981; padding:6px 12px;" onclick="openHouseModal()">➕ 하우스 추가</button>
        <button class="btn-edit-sm" style="background:#0891B2; padding:6px 12px;" onclick="openDeviceModal()">➕ 장비 추가</button>
      </div>
    </div>

    <div class="houses-grid" id="houses-render-grid">
      <!-- 동적 하우스 렌더링 영역 -->
    </div>

  </main>

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
        <input type="text" class="form-input" id="h-form-crop" placeholder="예: 딸기 (설향), 토마토, 엽채류 등">
      </div>
      <div class="form-group">
        <label class="form-label">메모 / 구역 설명</label>
        <input type="text" class="form-input" id="h-form-memo" placeholder="예: A라인 스마트 양액 및 차광막 집중 관제">
      </div>
      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('house-modal')">취소</button>
        <button class="btn-modal-save" onclick="saveHouseSubmit()">저장하기</button>
      </div>
    </div>
  </div>

  <!-- ⚙️ 2. 하우스별 스마트 농가 장치 추가/편집 모달 -->
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
        <select class="form-select" id="d-form-category" onchange="handleCategoryChange()">
          <option value="WATER_PUMP">💧 양수기 / 관수 펌프</option>
          <option value="NUTRIENT_FEEDER">🧪 양액기 / 양액 공급기</option>
          <option value="CURTAIN">☀️ 차광막 / 차단막 (스크린)</option>
          <option value="VINYL">🏠 비닐막 / 측창·천창 개폐기</option>
          <option value="VENT_FAN">💨 환풍기 / 유동팬</option>
          <option value="HEATER">🔥 열풍기 / 난방기</option>
          <option value="GROW_LIGHT">💡 LED 보광등</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">장비 명칭</label>
        <input type="text" class="form-input" id="d-form-name" placeholder="예: 1동 고압 양수기 2호">
      </div>
      <div class="form-group">
        <label class="form-label">실물 투야 릴레이 연동 (선택)</label>
        <select class="form-select" id="d-form-binding">
          <option value="">연동 안 함 (독립 소프트웨어 관제)</option>
          <option value="eb654aa2437462ea40dfjw:1">🎛️ 4채널 스위치 - 1번 채널</option>
          <option value="eb654aa2437462ea40dfjw:2">🎛️ 4채널 스위치 - 2번 채널</option>
          <option value="eb654aa2437462ea40dfjw:3">🎛️ 4채널 스위치 - 3번 채널</option>
          <option value="eb654aa2437462ea40dfjw:4">🎛️ 4채널 스위치 - 4번 채널</option>
          <option value="ebb219afdebea03ba3shlz:1">💡 스마트 플러그 #1 [책상등]</option>
          <option value="42362638a4e57cb3cd0b:1">🖨️ 스마트 플러그 #2 [3D프린터]</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">규격 / 메모</label>
        <input type="text" class="form-input" id="d-form-specs" placeholder="예: 2.0HP 다단 펌프, 24V 롤업 모터 등">
      </div>
      <div class="modal-btn-row">
        <button class="btn-modal-cancel" onclick="closeModal('device-modal')">취소</button>
        <button class="btn-modal-save" onclick="saveDeviceSubmit()">저장하기</button>
      </div>
    </div>
  </div>

  <script>
    const DEVICE_ID_4CH = 'eb654aa2437462ea40dfjw';

    let state1 = false;
    let state2 = false;
    const states4ch = { 1: false, 2: false, 3: false, 4: false };

    let farmHouses = {};
    let farmDevices = {};

    const isPending = { 1: false, 2: false };
    const abortControllers = { 1: null, 2: null };

    const isPending4ch = { 1: false, 2: false, 3: false, 4: false, 'all': false };
    const abortControllers4ch = { 1: null, 2: null, 3: null, 4: null, 'all': null };

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

    async function syncStatusFromDb() {
      try {
        const res = await fetch(`api.php?action=get_status&_t=${Date.now()}`);
        const data = await res.json();
        if (data.success) {
          farmHouses = data.houses || {};
          farmDevices = data.devices || {};

          let totalActiveCount = 0;

          // 1. 책상등 플러그
          if (data.devices['ebb219afdebea03ba3shlz']) {
            const d1 = data.devices['ebb219afdebea03ba3shlz'];
            const elName = document.getElementById('name-display-1');
            if (elName) elName.innerText = d1.name;
            if (!isPending[1]) {
              state1 = d1.state;
              updatePlugUI(1, state1, d1.power);
            }
            if (d1.state) totalActiveCount++;
          }

          // 2. 3D프린터 플러그
          if (data.devices['42362638a4e57cb3cd0b']) {
            const d2 = data.devices['42362638a4e57cb3cd0b'];
            const elName = document.getElementById('name-display-2');
            if (elName) elName.innerText = d2.name;
            if (!isPending[2]) {
              state2 = d2.state;
              updatePlugUI(2, state2, d2.power);
            }
            if (d2.state) totalActiveCount++;
          }

          // 3. 4채널 멀티 스위치
          if (data.devices[DEVICE_ID_4CH]) {
            const d4 = data.devices[DEVICE_ID_4CH];
            const nameEl = document.getElementById('name-display-4ch');
            if (nameEl) nameEl.innerText = d4.name;

            if (d4.channels) {
              for (let c = 1; c <= 4; c++) {
                if (d4.channels[c]) {
                  const chInfo = d4.channels[c];
                  const chNameEl = document.getElementById(`ch-name-${c}`);
                  if (chNameEl) chNameEl.innerText = chInfo.name;

                  if (!isPending4ch[c] && !isPending4ch['all']) {
                    states4ch[c] = chInfo.state;
                    update4ChUI(c, chInfo.state);
                  }
                  if (states4ch[c]) totalActiveCount++;
                }
              }
            }

            if (d4.interlockGroups) {
              renderInterlockStatus(d4.interlockGroups);
            }
          }

          // 상단 배지 업데이트
          document.getElementById('active-summary').innerText = `${totalActiveCount}개 장치 가동 중`;

          // 4. 동적 하우스 & 스마트 농가 장비 렌더링
          renderHousesGrid();
        }
      } catch(e) {}
    }

    function renderHousesGrid() {
      const container = document.getElementById('houses-render-grid');
      if (!container) return;

      const houseKeys = Object.keys(farmHouses);
      if (houseKeys.length === 0) {
        container.innerHTML = `
          <div style="grid-column: 1/-1; text-align:center; padding:40px; background:var(--bg-card); border-radius:16px; border:1px solid var(--border);">
            <div style="font-size:32px;">🌱</div>
            <div style="font-size:16px; font-weight:800; margin-top:8px;">등록된 비닐하우스가 없습니다</div>
            <div style="font-size:12px; color:var(--text-muted); margin-top:4px;">상단의 [+ 하우스 추가] 버튼을 눌러 첫 번째 온실/하우스를 등록해 보세요!</div>
          </div>
        `;
        return;
      }

      let html = '';
      houseKeys.forEach(hId => {
        const h = farmHouses[hId];
        const devices = h.devices || {};
        const devKeys = Object.keys(devices);

        html += `
          <div class="house-container-card">
            <div class="house-header">
              <div class="house-title">
                <span>${h.name}</span>
                <span class="house-crop-tag">${h.crop || '작물 미지정'}</span>
              </div>
              <div style="display:flex; gap:6px;">
                <button class="btn-edit-sm" onclick="editHouse(${h.id})">✏️ 하우스 수정</button>
                <button class="btn-edit-sm" style="color:#F43F5E;" onclick="deleteHouse(${h.id})">🗑️ 삭제</button>
              </div>
            </div>

            <div style="font-size:12px; color:var(--text-secondary); margin-top:-6px;">
              ${h.memo ? h.memo : '스마트 농가 환경 관제 구역'}
            </div>

            <div class="house-device-list">
        `;

        if (devKeys.length === 0) {
          html += `
            <div style="text-align:center; padding:18px; font-size:12px; color:var(--text-muted); border:1px dashed var(--border); border-radius:8px;">
              등록된 장비가 없습니다. <a href="javascript:openDeviceModal(${h.id})" style="color:var(--primary); font-weight:800;">[+ 장치 등록]</a>
            </div>
          `;
        } else {
          devKeys.forEach(dId => {
            const dev = devices[dId];
            const isCurtainOrVinyl = (dev.category === 'CURTAIN' || dev.category === 'VINYL');
            const icon = getCategoryIcon(dev.category);

            html += `
              <div class="house-device-item">
                <div class="hdev-info">
                  <span class="hdev-icon">${icon}</span>
                  <div>
                    <div class="hdev-name">${dev.name}</div>
                    <div class="hdev-sub">${dev.specs ? dev.specs : (dev.boundDeviceId ? '🔌 실물 릴레이 연동됨' : '소프트웨어 관제')}</div>
                  </div>
                </div>

                ${isCurtainOrVinyl ? `
                  <div class="curtain-controls">
                    <span style="font-size:11px; font-weight:800; color:#22D3EE; margin-right:4px;">${dev.position}%</span>
                    <button class="btn-curtain-step" onclick="controlHouseDevice(${dev.id}, 'POSITION', ${dev.position <= 0 ? 0 : dev.position - 50})">▲ 열기</button>
                    <button class="btn-curtain-step" onclick="controlHouseDevice(${dev.id}, 'POSITION', ${dev.position >= 100 ? 100 : dev.position + 50})">▼ 닫기</button>
                    <button class="btn-edit-sm" onclick="editHouseDevice(${dev.id})">✏️</button>
                  </div>
                ` : `
                  <div style="display:flex; align-items:center; gap:8px;">
                    <button class="ch-status-pill ${dev.state ? 'active' : ''}" style="cursor:pointer; padding:6px 14px;" onclick="controlHouseDevice(${dev.id}, 'TOGGLE', ${!dev.state})">
                      ${dev.state ? 'ON (가동중)' : 'OFF (정지)'}
                    </button>
                    <button class="btn-edit-sm" onclick="editHouseDevice(${dev.id})">✏️</button>
                    <button class="btn-edit-sm" style="color:#F43F5E;" onclick="deleteHouseDevice(${dev.id})">🗑️</button>
                  </div>
                `}
              </div>
            `;
          });
        }

        html += `
            </div>
            <button class="btn-edit-sm" style="background:rgba(255,255,255,0.06); padding:8px; border-radius:8px; font-weight:700; color:#CBD5E1; margin-top:2px;" onclick="openDeviceModal(${h.id})">
              ➕ 이 하우스에 장비(양수기/양액기/차광막 등) 추가하기
            </button>
          </div>
        `;
      });

      container.innerHTML = html;
    }

    function getCategoryIcon(cat) {
      switch(cat) {
        case 'WATER_PUMP': return '💧';
        case 'NUTRIENT_FEEDER': return '🧪';
        case 'CURTAIN': return '☀️';
        case 'VINYL': return '🏠';
        case 'VENT_FAN': return '💨';
        case 'HEATER': return '🔥';
        case 'GROW_LIGHT': return '💡';
        default: return '⚙️';
      }
    }

    function updatePlugUI(num, state, powerVal) {
      const ring = document.getElementById(`local-ring-${num}`);
      const power = document.getElementById(`power-${num}`);
      const tag = document.getElementById(`status-tag-${num}`);
      if (!ring || !tag) return;

      if (state) {
        ring.classList.add('active');
        tag.classList.add('active');
        tag.innerText = 'ON (켜짐)';
        if (power) power.innerHTML = `${powerVal > 0 ? powerVal : (num===1?52.3:44.8)} W`;
      } else {
        ring.classList.remove('active');
        tag.classList.remove('active');
        tag.innerText = 'OFF (꺼짐)';
        if (power) power.innerHTML = '0.0 W';
      }
    }

    function update4ChUI(channelNo, state) {
      const ring = document.getElementById(`ch-ring-${channelNo}`);
      const tag = document.getElementById(`ch-tag-${channelNo}`);
      const sub = document.getElementById(`ch-sub-${channelNo}`);
      if (!ring || !tag) return;

      if (state) {
        ring.classList.add('active');
        tag.classList.add('active');
        tag.innerText = 'ON';
        if (sub) sub.innerText = '가동 중';
      } else {
        ring.classList.remove('active');
        tag.classList.remove('active');
        tag.innerText = 'OFF';
        if (sub) sub.innerText = '터치 제어';
      }
    }

    async function togglePlug(id, num) {
      const btnContainer = document.getElementById(`btn-container-${num}`);
      const subMsg = document.getElementById(`sub-msg-${num}`);

      if (abortControllers[num]) abortControllers[num].abort();
      abortControllers[num] = new AbortController();

      const targetState = !(num === 1 ? state1 : state2);
      if (num === 1) state1 = targetState; else state2 = targetState;

      updatePlugUI(num, targetState, targetState ? (num === 1 ? 52.3 : 44.8) : 0);
      isPending[num] = true;
      if (btnContainer) btnContainer.classList.add('pending');
      if (subMsg) subMsg.innerText = '⏳ 전송 중...';

      try {
        const res = await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, state: targetState }),
          signal: abortControllers[num].signal
        });
        const data = await res.json();
        if (data.success) {
          showToast(`🔌 전원이 ${targetState ? 'ON (켜짐)' : 'OFF (꺼짐)'} 상태로 제어되었습니다`, 'success');
        }
      } catch(e) {
      } finally {
        isPending[num] = false;
        if (btnContainer) btnContainer.classList.remove('pending');
        if (subMsg) subMsg.innerText = '클릭 시 즉시 전환';
      }
    }

    async function toggle4Ch(channelNo) {
      const btnContainer = document.getElementById(`ch-pad-${channelNo}`);
      const subMsg = document.getElementById(`ch-sub-${channelNo}`);

      if (abortControllers4ch[channelNo]) abortControllers4ch[channelNo].abort();
      abortControllers4ch[channelNo] = new AbortController();

      const targetState = !states4ch[channelNo];
      states4ch[channelNo] = targetState;

      update4ChUI(channelNo, targetState);

      isPending4ch[channelNo] = true;
      if (btnContainer) btnContainer.classList.add('pending');
      if (subMsg) subMsg.innerText = '⏳ 전송 중...';

      try {
        const res = await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: channelNo, state: targetState }),
          signal: abortControllers4ch[channelNo].signal
        });
        const data = await res.json();
        if (data.success && data.channels) {
          for (let c = 1; c <= 4; c++) {
            if (data.channels[c]) {
              states4ch[c] = data.channels[c].state;
              update4ChUI(c, data.channels[c].state);
            }
          }
          showToast(`🎛️ 4채널 스위치 [${channelNo}번 채널] -> ${targetState ? 'ON (켜짐)' : 'OFF (꺼짐)'}`, 'success');
        }
      } catch(e) {
      } finally {
        isPending4ch[channelNo] = false;
        if (btnContainer) btnContainer.classList.remove('pending');
        if (subMsg) subMsg.innerText = states4ch[channelNo] ? '가동 중' : '터치 제어';
      }
    }

    async function toggleAll4Ch(targetState) {
      if (abortControllers4ch['all']) abortControllers4ch['all'].abort();
      abortControllers4ch['all'] = new AbortController();

      for (let i = 1; i <= 4; i++) {
        states4ch[i] = targetState;
        update4ChUI(i, targetState);
      }
      isPending4ch['all'] = true;

      try {
        const res = await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 'all', state: targetState }),
          signal: abortControllers4ch['all'].signal
        });
        const data = await res.json();
        if (data.success && data.channels) {
          for (let c = 1; c <= 4; c++) {
            if (data.channels[c]) {
              states4ch[c] = data.channels[c].state;
              update4ChUI(c, data.channels[c].state);
            }
          }
          showToast(`🎛️ 4채널 전체가 ${targetState ? 'ON' : 'OFF'} 상태로 제어되었습니다!`, 'success');
        }
      } catch(e) {
      } finally {
        isPending4ch['all'] = false;
      }
    }

    async function controlHouseDevice(devId, type, val) {
      try {
        const body = (type === 'TOGGLE') ? { id: devId, type: 'TOGGLE', state: val } : { id: devId, type: 'POSITION', position: val };
        const res = await fetch('api.php?action=control_house_device', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(body)
        });
        const data = await res.json();
        if (data.success) {
          showToast(`⚡ 장치 제어 완료!`, 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
    }

    // --- 🏗️ 모달 및 폼 핸들러 ---
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

    function editHouse(id) { openHouseModal(id); }

    async function deleteHouse(id) {
      if (!confirm('이 비닐하우스와 소속된 모든 장비 설정을 삭제하시겠습니까?')) return;
      try {
        const res = await fetch('api.php?action=delete_house', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
        const data = await res.json();
        if (data.success) {
          showToast('🗑️ 하우스가 삭제되었습니다.', 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
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

    function openDeviceModal(defaultHouseId = 0) {
      populateHouseSelect(defaultHouseId);
      document.getElementById('d-form-id').value = '0';
      document.getElementById('device-modal-title').innerText = '⚙️ 스마트 농가 장비 등록';
      document.getElementById('d-form-category').value = 'WATER_PUMP';
      document.getElementById('d-form-name').value = '💧 신규 양수기/관수펌프';
      document.getElementById('d-form-binding').value = '';
      document.getElementById('d-form-specs').value = '';
      document.getElementById('device-modal').classList.add('active');
    }

    function editHouseDevice(devId) {
      let targetDev = null;
      let parentHouseId = 0;
      Object.keys(farmHouses).forEach(hId => {
        if (farmHouses[hId].devices && farmHouses[hId].devices[devId]) {
          targetDev = farmHouses[hId].devices[devId];
          parentHouseId = hId;
        }
      });

      if (!targetDev) return;
      populateHouseSelect(parentHouseId);
      document.getElementById('d-form-id').value = devId;
      document.getElementById('device-modal-title').innerText = '⚙️ 스마트 농가 장비 수정';
      document.getElementById('d-form-category').value = targetDev.category;
      document.getElementById('d-form-name').value = targetDev.name;
      document.getElementById('d-form-specs').value = targetDev.specs || '';

      const bindingVal = targetDev.boundDeviceId ? `${targetDev.boundDeviceId}:${targetDev.boundChannelNo || 1}` : '';
      document.getElementById('d-form-binding').value = bindingVal;

      document.getElementById('device-modal').classList.add('active');
    }

    function populateHouseSelect(selectedId = 0) {
      const select = document.getElementById('d-form-house-id');
      select.innerHTML = '';
      Object.keys(farmHouses).forEach(hId => {
        const h = farmHouses[hId];
        const opt = document.createElement('option');
        opt.value = h.id;
        opt.innerText = h.name;
        if (parseInt(selectedId) === parseInt(h.id)) opt.selected = true;
        select.appendChild(opt);
      });
    }

    function handleCategoryChange() {
      const cat = document.getElementById('d-form-category').value;
      const nameInput = document.getElementById('d-form-name');
      if (document.getElementById('d-form-id').value === '0') {
        switch(cat) {
          case 'WATER_PUMP': nameInput.value = '💧 양수기 (관수펌프)'; break;
          case 'NUTRIENT_FEEDER': nameInput.value = '🧪 양액기 (양액공급기)'; break;
          case 'CURTAIN': nameInput.value = '☀️ 차광막 (차단막 스크린)'; break;
          case 'VINYL': nameInput.value = '🏠 측창 비닐막 개폐기'; break;
          case 'VENT_FAN': nameInput.value = '💨 환풍 유동팬'; break;
          case 'HEATER': nameInput.value = '🔥 온풍 난방기'; break;
          case 'GROW_LIGHT': nameInput.value = '💡 LED 보광등'; break;
        }
      }
    }

    async function saveDeviceSubmit() {
      const id = parseInt(document.getElementById('d-form-id').value) || 0;
      const houseId = parseInt(document.getElementById('d-form-house-id').value) || 1;
      const category = document.getElementById('d-form-category').value;
      const name = document.getElementById('d-form-name').value.trim();
      const bindingStr = document.getElementById('d-form-binding').value;
      const specs = document.getElementById('d-form-specs').value.trim();

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
            id, house_id: houseId, category, name, bound_device_id: boundDeviceId, bound_channel_no: boundChannelNo, specs
          })
        });
        const data = await res.json();
        if (data.success) {
          closeModal('device-modal');
          showToast(`✅ '${name}' 장비가 저장되었습니다!`, 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
    }

    async function deleteHouseDevice(devId) {
      if (!confirm('이 장비를 삭제하시겠습니까?')) return;
      try {
        const res = await fetch('api.php?action=delete_house_device', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: devId })
        });
        const data = await res.json();
        if (data.success) {
          showToast('🗑️ 장비가 삭제되었습니다.', 'success');
          syncStatusFromDb();
        }
      } catch(e) {}
    }

    function closeModal(modalId) {
      document.getElementById(modalId).classList.remove('active');
    }

    async function promptRename(id, key) {
      const elId = (key === '4ch') ? 'name-display-4ch' : `name-display-${key}`;
      const currName = document.getElementById(elId) ? document.getElementById(elId).innerText : '';
      const newName = prompt(`📱 스마트폰 Smart Life 앱 및 대시보드에 적용할 새로운 이름을 입력하세요:`, currName);

      if (newName && newName.trim() !== '' && newName !== currName) {
        if (document.getElementById(elId)) document.getElementById(elId).innerText = newName.trim();
        try {
          const res = await fetch('api.php?action=rename_device', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, name: newName.trim() })
          });
          const data = await res.json();
          if (data.success) {
            showToast(`📱 기기 이름이 [${newName.trim()}] (으)로 양방향 동기화되었습니다!`, 'success');
          }
        } catch(e) {}
      }
    }

    async function promptRenameChannel(channelNo) {
      const currName = document.getElementById(`ch-name-${channelNo}`) ? document.getElementById(`ch-name-${channelNo}`).innerText : '';
      const newName = prompt(`🎛️ ${channelNo}번 채널에 부여할 용도/이름을 입력하세요 (예: 1동 주양수기, 양액기 등):`, currName);

      if (newName && newName.trim() !== '' && newName !== currName) {
        if (document.getElementById(`ch-name-${channelNo}`)) document.getElementById(`ch-name-${channelNo}`).innerText = newName.trim();
        try {
          const res = await fetch('api.php?action=rename_channel', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: DEVICE_ID_4CH, channel: channelNo, name: newName.trim() })
          });
          const data = await res.json();
          if (data.success) {
            showToast(`🎛️ ${channelNo}번 채널 이름이 [${newName.trim()}] (으)로 변경되었습니다!`, 'success');
          }
        } catch(e) {}
      }
    }

  <!-- 🔒 4. 4채널 멀티 스위치 인터락 설정 모달 -->
  <div class="modal-overlay" id="interlock-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">🔒 4채널 하드웨어 인터락(Interlock) 설정</div>
        <button class="btn-edit-sm" onclick="closeModal('interlock-modal')">✕</button>
      </div>

      <div style="background:rgba(6, 182, 212, 0.12); border:1px solid rgba(6, 182, 212, 0.3); border-radius:12px; padding:12px; font-size:12px; color:#CFFAFE; line-height:1.5;">
        📱 <strong>스마트폰 Smart Life 앱 및 투야 하드웨어와 실시간 100% 양방향 동기화됩니다.</strong><br>
        묶인 채널 중 하나를 켜면 반대편 채널이 물리 릴레이 수준에서 자동으로 즉시 차단(OFF)되어 <strong>모터 정역회전 쇼트 방지 및 안전 개폐</strong>를 완벽 보장합니다.
      </div>

      <div class="form-group">
        <label class="form-label">인터락 묶음 모드 선택</label>
        <div style="display:flex; flex-direction:column; gap:10px;">
          <label style="display:flex; align-items:flex-start; gap:10px; background:rgba(0,0,0,0.3); padding:12px; border-radius:10px; cursor:pointer; border:1px solid rgba(255,255,255,0.08);">
            <input type="radio" name="interlock_preset" value="2x2" checked style="margin-top:3px;">
            <div>
              <div style="font-size:13px; font-weight:800; color:#34D399;">🌟 [농가 기본 권장] 1-2번 묶음 & 3-4번 묶음</div>
              <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">[CH1 ↔ CH2 상호잠금], [CH3 ↔ CH4 상호잠금] (개폐기 열림/닫힘 및 양수/양액 모터 최적화)</div>
            </div>
          </label>

          <label style="display:flex; align-items:flex-start; gap:10px; background:rgba(0,0,0,0.3); padding:12px; border-radius:10px; cursor:pointer; border:1px solid rgba(255,255,255,0.08);">
            <input type="radio" name="interlock_preset" value="1x2" style="margin-top:3px;">
            <div>
              <div style="font-size:13px; font-weight:800; color:#F8FAFC;">🔒 1-2번 묶음만 사용 (3, 4번은 독립 스위치)</div>
              <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">[CH1 ↔ CH2만 상호잠금], 3번과 4번 채널은 일반 독립 조명/팬으로 사용</div>
            </div>
          </label>

          <label style="display:flex; align-items:flex-start; gap:10px; background:rgba(0,0,0,0.3); padding:12px; border-radius:10px; cursor:pointer; border:1px solid rgba(255,255,255,0.08);">
            <input type="radio" name="interlock_preset" value="4all" style="margin-top:3px;">
            <div>
              <div style="font-size:13px; font-weight:800; color:#F8FAFC;">⚡ 1-2-3-4 전체 상호 인터락 (단 1개 채널만 가동)</div>
              <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">4개 채널 중 언제나 오직 1개 채널만 ON 가능 (선택적 급수 라인 등)</div>
            </div>
          </label>

          <label style="display:flex; align-items:flex-start; gap:10px; background:rgba(0,0,0,0.3); padding:12px; border-radius:10px; cursor:pointer; border:1px solid rgba(255,255,255,0.08);">
            <input type="radio" name="interlock_preset" value="none" style="margin-top:3px;">
            <div>
              <div style="font-size:13px; font-weight:800; color:#F43F5E;">⛔ 인터락 완전 해제 (4채널 개별 독립 스위치)</div>
              <div style="font-size:11px; color:var(--text-muted); margin-top:2px;">상호 잠금 없이 4개 채널을 모두 자유롭게 켜고 끕니다.</div>
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

  <!-- 📲 3. 태블릿 & 스마트폰 홈 화면 앱 설치 가이드 모달 -->
  <div class="modal-overlay" id="pwa-modal">
    <div class="modal-content">
      <div class="modal-header">
        <div class="modal-title">📲 태블릿PC에 전용 앱으로 설치하기</div>
        <button class="btn-edit-sm" onclick="closeModal('pwa-modal')">✕</button>
      </div>

      <div style="text-align:center; padding:10px 0;">
        <img src="icon.svg" style="width:72px; height:72px; border-radius:18px; box-shadow:0 6px 15px rgba(0,0,0,0.4);" alt="App Icon">
        <div style="font-size:16px; font-weight:800; margin-top:8px;">누리오 스마트팜</div>
        <div style="font-size:12px; color:var(--primary); font-weight:700;">주소창 없는 100% 전체화면 독립 앱</div>
      </div>

      <div style="background:rgba(0,0,0,0.3); border-radius:12px; padding:14px; display:flex; flex-direction:column; gap:12px; font-size:13px; line-height:1.5;">
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <span style="background:#10B981; color:white; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px; flex-shrink:0;">1</span>
          <div>
            <strong>크롬 / 삼성 인터넷 브라우저</strong> 우측 상단의 <strong>더보기 (⋮ 또는 ☰)</strong> 메뉴를 누르세요.
          </div>
        </div>
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <span style="background:#0891B2; color:white; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px; flex-shrink:0;">2</span>
          <div>
            메뉴에서 <strong>[홈 화면에 추가]</strong> 또는 <strong>[앱 설치]</strong>를 선택하세요.
          </div>
        </div>
        <div style="display:flex; gap:10px; align-items:flex-start;">
          <span style="background:#6366F1; color:white; border-radius:50%; width:22px; height:22px; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:11px; flex-shrink:0;">3</span>
          <div>
            태블릿PC 바탕화면에 🍓 <strong>누리오 스마트팜</strong> 아이콘이 생성되며, 터치 시 주소창 없는 <strong>풀스크린 전용 앱</strong>으로 실행됩니다!
          </div>
        </div>
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

  <script>
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

    function openInterlockModal() {
      document.getElementById('interlock-modal').classList.add('active');
    }

    async function saveInterlockSubmit() {
      const selected = document.querySelector('input[name="interlock_preset"]:checked').value;
      let groups = [];
      if (selected === '2x2') {
        groups = [[1, 2], [3, 4]];
      } else if (selected === '1x2') {
        groups = [[1, 2]];
      } else if (selected === '4all') {
        groups = [[1, 2, 3, 4]];
      } else {
        groups = [];
      }

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

    function renderInterlockStatus(groups) {
      const badgeText = document.getElementById('interlock-badge-text');
      const itag1 = document.getElementById('itag-1');
      const itag2 = document.getElementById('itag-2');
      const idesc1 = document.getElementById('idesc-1');
      const idesc2 = document.getElementById('idesc-2');
      const cardG1 = document.getElementById('interlock-card-g1');
      const cardG2 = document.getElementById('interlock-card-g2');

      if (!groups || groups.length === 0) {
        if (badgeText) badgeText.innerText = '인터락: 해제됨 (독립모드)';
        if (itag1) itag1.innerText = '🔓 CH 1 & CH 2 (독립 작동)';
        if (itag2) itag2.innerText = '🔓 CH 3 & CH 4 (독립 작동)';
        if (idesc1) idesc1.innerText = '개별 독립 제어';
        if (idesc2) idesc2.innerText = '개별 독립 제어';
        if (cardG1) cardG1.style.borderStyle = 'solid';
        if (cardG2) cardG2.style.borderStyle = 'solid';
      } else {
        const is2x2 = (groups.length === 2 && groups[0].length === 2 && groups[1].length === 2);
        if (is2x2) {
          if (badgeText) badgeText.innerText = '인터락: [1↔2] [3↔4]';
          if (itag1) itag1.innerText = '🔒 인터락 그룹 [1번 ↔ 2번 묶음]';
          if (itag2) itag2.innerText = '🔒 인터락 그룹 [3번 ↔ 4번 묶음]';
          if (idesc1) idesc1.innerText = '⚡ 상호 배타 잠금 (1번 켜면 2번 자동 OFF)';
          if (idesc2) idesc2.innerText = '⚡ 상호 배타 잠금 (3번 켜면 4번 자동 OFF)';
        } else if (groups.length === 1 && groups[0].length === 4) {
          if (badgeText) badgeText.innerText = '인터락: [1↔2↔3↔4 단일ON]';
          if (itag1) itag1.innerText = '⚡ 전체 상호 잠금 그룹 A';
          if (itag2) itag2.innerText = '⚡ 전체 상호 잠금 그룹 B';
          if (idesc1) idesc1.innerText = '4개 채널 중 단 1개만 가동';
          if (idesc2) idesc2.innerText = '4개 채널 중 단 1개만 가동';
        } else {
          if (badgeText) badgeText.innerText = `인터락: [${groups.map(g => g.join('↔')).join('], [')}]`;
        }
      }
    }

    function showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `<span>🍓</span><span>${message}</span>`;
      container.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 2500);
    }

    document.addEventListener('DOMContentLoaded', syncStatusFromDb);
    setInterval(syncStatusFromDb, 3000);
  </script>
</body>
</html>
