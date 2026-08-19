<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title>🏢 비전 스마트 센터 (Vision Smart Building BMS)</title>
  
  <link rel="manifest" href="manifest.json">
  <link rel="icon" type="image/svg+xml" href="icon.svg">
  <link rel="apple-touch-icon" href="icon.svg">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="theme-color" content="#090D16">

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">

  <style>
    :root {
      --bg-main: #090D16;
      --bg-surface: rgba(21, 30, 48, 0.9);
      --bg-glass-card: rgba(26, 40, 68, 0.75);
      --border-subtle: rgba(255, 255, 255, 0.12);
      --border-bright: rgba(255, 255, 255, 0.24);
      --border-accent: rgba(59, 130, 246, 0.4);
      --primary: #3B82F6;
      --primary-glow: rgba(59, 130, 246, 0.4);
      --amber-gold: #F59E0B;
      --amber-glow: rgba(245, 158, 11, 0.4);
      --emerald: #10B981;
      --emerald-glow: rgba(16, 185, 129, 0.35);
      --rose: #EF4444;
      --text-white: #FFFFFF;
      --text-muted: #94A3B8;
      --text-dim: #64748B;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; user-select: none; }

    html, body {
      height: 100vh;
      height: 100dvh;
      max-height: 100dvh;
      overflow: hidden !important;
      font-family: "Pretendard", -apple-system, BlinkMacSystemFont, sans-serif;
      background-color: var(--bg-main);
      background-image: 
        radial-gradient(circle at 20% 15%, rgba(30, 58, 138, 0.28) 0%, transparent 45%),
        radial-gradient(circle at 80% 85%, rgba(16, 185, 129, 0.15) 0%, transparent 50%),
        linear-gradient(180deg, #090D16 0%, #0D1322 100%);
      color: var(--text-white);
      display: flex;
      flex-direction: column;
      padding: 6px 12px;
      gap: 6px;
    }

    /* 🌟 상단 탑바 */
    .top-nav {
      height: 42px;
      min-height: 42px;
      background: var(--bg-surface);
      backdrop-filter: blur(16px);
      -webkit-backdrop-filter: blur(16px);
      border: 1px solid var(--border-subtle);
      border-radius: 10px;
      padding: 0 14px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 12px;
      flex-shrink: 0;
    }

    .brand-group { display: flex; align-items: center; gap: 8px; }
    .brand-icon {
      width: 28px; height: 28px; border-radius: 6px;
      background: linear-gradient(135deg, #2563EB, #1D4ED8);
      display: flex; align-items: center; justify-content: center;
      font-size: 15px;
    }
    .brand-name { font-size: 14.5px; font-weight: 800; color: #FFF; letter-spacing: -0.3px; }

    .mode-switch-link {
      text-decoration: none;
      background: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #6EE7B7;
      padding: 4px 9px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      display: flex;
      align-items: center;
      gap: 4px;
    }
    .mode-switch-link:hover { background: #10B981; color: #FFF; }

    .top-chips { display: flex; align-items: center; gap: 10px; }
    .top-chip {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--border-subtle);
      border-radius: 6px;
      padding: 3px 9px;
      font-size: 11px;
      display: flex;
      align-items: center;
      gap: 5px;
    }
    .top-chip-val { font-weight: 800; color: #FFF; }
    .top-chip-val.amber { color: #FBBF24; }

    .top-actions { display: flex; align-items: center; gap: 8px; }
    .btn-master-off {
      background: linear-gradient(135deg, #DC2626, #991B1B);
      border: 1px solid rgba(239, 68, 68, 0.6);
      color: #FFF;
      padding: 5px 14px;
      border-radius: 6px;
      font-size: 11.5px;
      font-weight: 800;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(220, 38, 38, 0.4);
    }
    .btn-master-off:active { transform: scale(0.96); }

    .btn-vacation {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid var(--border-subtle);
      color: #FFF;
      padding: 5px 10px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
    }

    .btn-fs {
      background: transparent;
      border: 1px solid var(--border-subtle);
      color: var(--text-muted);
      padding: 5px 9px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 700;
      cursor: pointer;
    }

    /* 🌾 메인 2컬럼 레이아웃 */
    main {
      flex: 1;
      width: 100%;
      height: 100%;
      min-height: 0;
      display: grid;
      grid-template-columns: 310px 1fr;
      gap: 8px;
      overflow: hidden;
    }

    /* 🏛️ 좌측: 3층 단면도 디지털 트윈 일러스트 */
    .left-twin-panel {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 8px 10px;
      display: flex;
      flex-direction: column;
      gap: 6px;
      height: 100%;
      min-height: 0;
    }
    .twin-header { display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; }
    .twin-title { font-size: 12px; font-weight: 800; color: #FFF; display: flex; align-items: center; gap: 5px; }

    .building-render {
      flex: 1;
      min-height: 0;
      background: radial-gradient(circle at 50% 20%, #152747 0%, #0A111F 85%);
      border-radius: 8px;
      border: 1px solid var(--border-subtle);
      padding: 8px 10px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      overflow: hidden;
    }

    .roof-deco {
      display: flex; justify-content: space-between; align-items: flex-end; padding: 0 6px; height: 16px; flex-shrink: 0;
    }
    .roof-tag { font-size: 8px; font-weight: 800; color: #93C5FD; background: rgba(0,0,0,0.5); padding: 1px 5px; border-radius: 3px; }

    .slab-box {
      flex: 1;
      min-height: 0;
      background: rgba(18, 28, 48, 0.85);
      border: 1.5px solid var(--border-bright);
      border-radius: 6px;
      padding: 4px 6px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      transition: all 0.3s ease;
    }
    .slab-title-row { display: flex; justify-content: space-between; align-items: center; font-size: 9px; font-weight: 800; color: #CBD5E1; }

    .room-stage-grid { flex: 1; min-height: 0; display: grid; gap: 4px; margin-top: 2px; }
    .room-stage-grid.cols-2 { grid-template-columns: 1fr 1fr; }
    .room-stage-grid.cols-3 { grid-template-columns: 1.5fr 1.5fr; }

    .room-stage-box {
      background: rgba(10, 16, 28, 0.7);
      border: 1px solid var(--border-subtle);
      border-radius: 4px;
      padding: 3px 5px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      position: relative;
      transition: all 0.3s ease;
    }
    .room-stage-box.lit {
      background: linear-gradient(180deg, rgba(245, 158, 11, 0.25) 0%, rgba(217, 119, 6, 0.1) 100%);
      border-color: rgba(245, 158, 11, 0.7);
      box-shadow: inset 0 0 10px rgba(245, 158, 11, 0.3);
    }
    .r-tag { font-size: 8.5px; font-weight: 800; display: flex; justify-content: space-between; }
    .bead-strip { position: absolute; top: 1px; width: 85%; display: flex; justify-content: space-around; }
    .bead-item { width: 6px; height: 6px; border-radius: 50%; background: #334155; }
    .bead-item.on { background: #FACC15; box-shadow: 0 0 6px #F59E0B; }

    .mini-service-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2px; }
    .mini-status-chip {
      background: rgba(10, 16, 28, 0.7); border: 1px solid var(--border-subtle);
      border-radius: 3px; padding: 1px 3px; font-size: 7.5px; font-weight: 700;
      display: flex; justify-content: space-between; align-items: center;
    }
    .mini-status-chip.on { background: rgba(245, 158, 11, 0.3); border-color: var(--amber-gold); color: #FEF08A; }

    /* ========================================================
       🎛️ 우측: 대표님 스케치 기반 직관적 대형 스위치 패널
       ======================================================== */
    .right-bento-panel {
      display: grid;
      grid-template-rows: 0.9fr 1.55fr 1.55fr;
      gap: 6px;
      height: 100%;
      min-height: 0;
      overflow: hidden;
    }

    .floor-row-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-subtle);
      border-radius: 12px;
      padding: 6px 12px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 0;
      overflow: hidden;
    }

    .card-top-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-bottom: 3px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
      flex-shrink: 0;
    }
    .f-title-group { display: flex; align-items: center; gap: 6px; }
    .f-badge-pill { font-size: 10px; font-weight: 800; padding: 2px 7px; border-radius: 4px; }
    .f-badge-pill.f3 { background: rgba(139, 92, 246, 0.25); color: #C4B5FD; border: 1px solid rgba(139, 92, 246, 0.4); }
    .f-badge-pill.f2 { background: rgba(59, 130, 246, 0.25); color: #93C5FD; border: 1px solid rgba(59, 130, 246, 0.4); }
    .f-badge-pill.f1 { background: rgba(16, 185, 129, 0.25); color: #6EE7B7; border: 1px solid rgba(16, 185, 129, 0.4); }
    .f-title-text { font-size: 12px; font-weight: 800; color: #FFF; }

    .btn-floor-off {
      background: rgba(239, 68, 68, 0.14);
      border: 1px solid rgba(239, 68, 68, 0.35);
      color: #FCA5A5;
      font-size: 9.5px;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 4px;
      cursor: pointer;
    }
    .btn-floor-off:hover { background: #DC2626; color: #FFF; }

    .boxes-horizontal-flow {
      flex: 1;
      min-height: 0;
      display: flex;
      gap: 10px;
      align-items: stretch;
      margin-top: 4px;
    }

    /* 개별 구역/장치 박스 */
    .module-box {
      background: var(--bg-glass-card);
      border: 1.5px solid var(--border-subtle);
      border-radius: 10px;
      padding: 6px 10px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      min-height: 0;
      flex-shrink: 0;
    }

    .mod-head {
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 11px;
      font-weight: 800;
      color: #E2E8F0;
      flex-shrink: 0;
      margin-bottom: 2px;
    }
    .btn-toggle-all {
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid var(--border-subtle);
      color: var(--text-muted);
      border-radius: 4px;
      padding: 1px 7px;
      font-size: 9px;
      font-weight: 600;
      cursor: pointer;
    }
    .btn-toggle-all:hover { color: #FFF; border-color: var(--border-bright); }

    /* 🔲 92px × 84px 초대형 스퀘어 터치 스위치 */
    .huge-switch-btn {
      width: 92px;
      height: 84px;
      min-width: 92px;
      min-height: 84px;
      background: linear-gradient(180deg, rgba(30, 48, 80, 0.75) 0%, rgba(18, 30, 52, 0.95) 100%);
      border: 2px solid rgba(255, 255, 255, 0.16);
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 5px;
      cursor: pointer;
      transition: all 0.15s cubic-bezier(0.4, 0, 0.2, 1);
      color: var(--text-dim);
      padding: 0;
      box-shadow: inset 0 1px 2px rgba(255,255,255,0.1), 0 3px 8px rgba(0,0,0,0.35);
    }
    .huge-switch-btn:hover {
      border-color: rgba(245, 158, 11, 0.7);
      transform: translateY(-2px);
      box-shadow: 0 6px 14px rgba(0,0,0,0.45);
    }
    .huge-switch-btn:active { transform: scale(0.95); }

    .huge-switch-btn.active {
      background: linear-gradient(180deg, #FEF08A 0%, #F59E0B 100%);
      border-color: #FBBF24;
      box-shadow: 0 0 18px rgba(245, 158, 11, 0.5), inset 0 1px 2px rgba(255,255,255,0.8);
      color: #78350F;
    }
    .huge-switch-btn .sw-icon { font-size: 22px; line-height: 1; }
    .huge-switch-btn .sw-txt { font-size: 13px; font-weight: 900; line-height: 1; }

    /* 2구 가로 배열 (목양실, 미팅룸) */
    .sw-row-2 {
      flex: 1;
      display: flex;
      gap: 8px;
      align-items: center;
      justify-content: center;
    }

    /* 6구 조명 3열 2행 (3 x 2 그리드) */
    .sw-grid-3x2 {
      flex: 1;
      display: grid;
      grid-template-columns: repeat(3, 92px);
      grid-template-rows: repeat(2, 84px);
      gap: 8px;
      align-items: center;
      justify-content: center;
    }

    /* ❄️ 2층 시스템 냉난방기 */
    .hvac-sketch-layout {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 16px;
      justify-content: center;
    }
    .hvac-temp-col {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 8px;
      padding: 0 6px;
    }
    .hvac-giant-temp {
      font-size: 42px;
      font-weight: 900;
      color: #60A5FA;
      letter-spacing: -1.5px;
      line-height: 1;
      text-shadow: 0 0 16px var(--primary-glow);
    }
    .hvac-temp-stepper { display: flex; gap: 6px; }
    .btn-step-mini {
      width: 36px; height: 32px; border: 1px solid var(--border-subtle); border-radius: 6px;
      background: var(--bg-surface); color: #FFF; font-size: 18px; font-weight: 800; cursor: pointer;
    }
    .btn-step-mini:hover { background: rgba(255,255,255,0.15); color: var(--primary); }

    /* 냉난방기 2x2 버튼 매트릭스 */
    .hvac-2x2-matrix {
      display: grid;
      grid-template-columns: repeat(2, 92px);
      grid-template-rows: repeat(2, 84px);
      gap: 8px;
    }

    .hvac-action-btn {
      width: 92px;
      height: 84px;
      border: 2px solid var(--border-subtle);
      border-radius: 12px;
      background: var(--bg-surface);
      color: var(--text-muted);
      font-size: 13px;
      font-weight: 800;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 4px;
      cursor: pointer;
      transition: all 0.15s;
    }
    .hvac-action-btn:hover { border-color: var(--border-bright); transform: translateY(-1px); }
    .hvac-action-btn.pwr-on.active {
      background: linear-gradient(180deg, #34D399 0%, #059669 100%);
      border-color: #34D399; color: #FFF; box-shadow: 0 0 16px var(--emerald-glow);
    }
    .hvac-action-btn.pwr-off.active {
      background: linear-gradient(180deg, #64748B 0%, #334155 100%);
      border-color: #94A3B8; color: #FFF;
    }
    .hvac-action-btn.mode-cool.active {
      background: linear-gradient(180deg, #60A5FA 0%, #2563EB 100%);
      border-color: #60A5FA; color: #FFF; box-shadow: 0 0 16px var(--primary-glow);
    }
    .hvac-action-btn.mode-heat.active {
      background: linear-gradient(180deg, #FB923C 0%, #EA580C 100%);
      border-color: #FB923C; color: #FFF; box-shadow: 0 0 16px rgba(234, 88, 12, 0.4);
    }

    /* 1층 화장실 (남/여) 2구 대형 스위치 */
    .restroom-sw-col {
      flex: 1;
      display: flex;
      flex-direction: column;
      gap: 8px;
      align-items: center;
      justify-content: center;
    }

    /* 🚪 메인 현관 도어락 대형 터치 버튼 */
    .door-large-btn {
      width: 110px;
      height: 176px;
      border: 2px solid rgba(16, 185, 129, 0.4);
      background: linear-gradient(135deg, rgba(16, 185, 129, 0.18), rgba(5, 150, 105, 0.3));
      color: #6EE7B7;
      border-radius: 12px;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 10px;
      cursor: pointer;
      font-size: 13.5px;
      font-weight: 800;
      transition: all 0.2s;
      box-shadow: 0 4px 14px rgba(0,0,0,0.3);
    }
    .door-large-btn:hover { background: #10B981; color: #FFF; box-shadow: 0 0 20px var(--emerald-glow); }
    .door-large-btn.unlocked {
      border-color: rgba(239, 68, 68, 0.5);
      background: linear-gradient(135deg, rgba(239, 68, 68, 0.22), rgba(185, 28, 28, 0.35));
      color: #FCA5A5;
    }
    .door-large-btn.unlocked:hover { background: #DC2626; color: #FFF; }
  </style>
</head>
<body>

  <!-- ========================================================
       🌟 1. 상단 네비게이션 탑바
       ======================================================== -->
  <header class="top-nav">
    <div class="brand-group">
      <div class="brand-icon">🏢</div>
      <div class="brand-name">비전 스마트 센터</div>
      <a href="index.php" class="mode-switch-link" title="🍓 누리오 스마트팜 관제 화면으로 이동">
        <span>🌱</span><span>스마트팜 모드</span>
      </a>
    </div>

    <!-- 중앙 실시간 지표 -->
    <div class="top-chips">
      <div class="top-chip">
        <span>⚡ 총 부하:</span><strong class="top-chip-val" id="top-val-power">1,420 W</strong>
      </div>
      <div class="top-chip">
        <span>💡 점등:</span><strong class="top-chip-val amber" id="top-val-lights">11 / 18구</strong>
      </div>
      <div class="top-chip">
        <span>🛡️ 보안:</span><strong class="top-chip-val" style="color:var(--emerald);">정상 경비</strong>
      </div>
    </div>

    <!-- 우측 마스터 액션 -->
    <div class="top-actions">
      <button class="btn-master-off" onclick="masterAllOff()">
        <span>🚨</span><span>건물 전체 전등 끄기</span>
      </button>
      <button class="btn-vacation" onclick="safeVacationMode()">
        <span>🌙</span><span>퇴근 모드</span>
      </button>
      <button class="btn-fs" onclick="toggleFullscreen()">
        <span>⛶</span><span>전체화면</span>
      </button>
    </div>
  </header>

  <!-- ========================================================
       🌾 2. 메인 관제 2컬럼 레이아웃
       ======================================================== -->
  <main>
    
    <!-- 🏛️ 좌측: 3층 단면 디지털 트윈 일러스트 -->
    <section class="left-twin-panel">
      <div class="twin-header">
        <div class="twin-title">
          <span style="color:var(--emerald);">●</span>
          <span>3층 단면 디지털 트윈</span>
        </div>
      </div>

      <div class="building-render">
        <div class="roof-deco">
          <div style="display:flex; gap:2px;">
            <div style="width:16px; height:7px; background:#2563EB; border-radius:1px; transform:skewX(-15deg);"></div>
            <div style="width:16px; height:7px; background:#2563EB; border-radius:1px; transform:skewX(-15deg);"></div>
          </div>
          <div class="roof-tag">VISION CENTER BMS</div>
        </div>

        <!-- 3층 단면 슬래브 -->
        <div class="slab-box" id="slab-3f">
          <div class="slab-title-row">
            <span>🟣 3F 목양실 & 미팅룸</span>
            <span id="slab-3f-summary">1/4구</span>
          </div>
          <div class="room-stage-grid cols-2">
            <div class="room-stage-box lit" id="rz-pastor">
              <div class="r-tag"><span>목양실</span><span id="rz-badge-pastor">1/2구</span></div>
              <div class="bead-strip">
                <div class="bead-item on" id="bead-p1"></div>
                <div class="bead-item" id="bead-p2"></div>
              </div>
            </div>
            <div class="room-stage-box" id="rz-meeting">
              <div class="r-tag"><span>미팅룸</span><span id="rz-badge-meeting">0/2구</span></div>
              <div class="bead-strip">
                <div class="bead-item on" id="bead-m1"></div>
                <div class="bead-item" id="bead-m2"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- 2층 단면 슬래브 -->
        <div class="slab-box" id="slab-2f">
          <div class="slab-title-row">
            <span>🔵 2F 대예배실 (Sanctuary)</span>
            <span id="slab-2f-hvac-txt" style="color:#93C5FD;">❄️ 22°C</span>
          </div>
          <div class="room-stage-grid">
            <div class="room-stage-box lit" id="rz-sanctuary">
              <div class="r-tag"><span>대예배실 홀</span><span id="rz-badge-sanctuary">6/6구</span></div>
              <div class="bead-strip">
                <div class="bead-item on" id="bead-s1"></div>
                <div class="bead-item on" id="bead-s2"></div>
                <div class="bead-item on" id="bead-s3"></div>
                <div class="bead-item on" id="bead-s4"></div>
                <div class="bead-item on" id="bead-s5"></div>
                <div class="bead-item on" id="bead-s6"></div>
              </div>
            </div>
          </div>
        </div>

        <!-- 1층 단면 슬래브 -->
        <div class="slab-box" id="slab-1f">
          <div class="slab-title-row">
            <span>🟢 1F 메인 로비 & 편의시설</span>
            <span id="slab-1f-summary">3/6구</span>
          </div>
          <div class="room-stage-grid cols-3">
            <div class="room-stage-box lit" id="rz-lobby">
              <div class="r-tag"><span>로비</span><span id="rz-badge-lobby">3/6구</span></div>
              <div class="bead-strip">
                <div class="bead-item on" id="bead-l1"></div>
                <div class="bead-item on" id="bead-l2"></div>
                <div class="bead-item on" id="bead-l3"></div>
                <div class="bead-item" id="bead-l4"></div>
                <div class="bead-item" id="bead-l5"></div>
                <div class="bead-item" id="bead-l6"></div>
              </div>
            </div>
            <div style="display:flex; flex-direction:column; justify-content:space-between; gap:2px;">
              <div class="mini-service-grid">
                <div class="mini-status-chip on" id="chip-rr-m">🚹 <span id="txt-rr-m">ON</span></div>
                <div class="mini-status-chip" id="chip-rr-w">🚺 <span id="txt-rr-w">OFF</span></div>
              </div>
              <div class="mini-status-chip" id="chip-door" style="color:var(--emerald);">
                <span>🚪</span><strong id="txt-door">🔒 잠김</strong>
              </div>
            </div>
          </div>
        </div>

        <!-- 지상 바닥 -->
        <div style="display:flex; justify-content:space-between; align-items:center; padding:0 6px; height:8px;">
          <div style="width:10px; height:10px; border-radius:50%; background:#15803D;"></div>
          <div style="flex:1; height:3px; background:#22C55E; margin:0 4px; border-radius:2px;"></div>
          <div style="width:10px; height:10px; border-radius:50%; background:#15803D;"></div>
        </div>
      </div>
    </section>

    <!-- ========================================================
       🎛️ 우측: 대표님 스케치 기반 92px × 84px 직관적 제어 패널
       ======================================================== -->
    <section class="right-bento-panel">
      
      <!-- ================= 3F ================= -->
      <div class="floor-row-card" id="card-3f">
        <div class="card-top-bar">
          <div class="f-title-group">
            <span class="f-badge-pill f3">3F</span>
            <span class="f-title-text">3층 목양실 & 미팅룸</span>
          </div>
          <button class="btn-floor-off" onclick="floorOff(3)">3F 소등</button>
        </div>

        <div class="boxes-horizontal-flow">
          <!-- 목양실 (2구) -->
          <div class="module-box">
            <div class="mod-head">
              <span>📖 목양실 전등 (2구)</span>
              <button class="btn-toggle-all" onclick="toggleRoomAll('pastor')">전체</button>
            </div>
            <div class="sw-row-2">
              <button class="huge-switch-btn active" id="sw-p1" onclick="toggleLight('pastor', 0)">
                <span class="sw-icon">💡</span>
                <span class="sw-txt">1구</span>
              </button>
              <button class="huge-switch-btn" id="sw-p2" onclick="toggleLight('pastor', 1)">
                <span class="sw-icon">💡</span>
                <span class="sw-txt">2구</span>
              </button>
            </div>
          </div>

          <!-- 미팅룸 (2구) -->
          <div class="module-box">
            <div class="mod-head">
              <span>👥 미팅룸 전등 (2구)</span>
              <button class="btn-toggle-all" onclick="toggleRoomAll('meeting')">전체</button>
            </div>
            <div class="sw-row-2">
              <button class="huge-switch-btn" id="sw-m1" onclick="toggleLight('meeting', 0)">
                <span class="sw-icon">💡</span>
                <span class="sw-txt">1구</span>
              </button>
              <button class="huge-switch-btn" id="sw-m2" onclick="toggleLight('meeting', 1)">
                <span class="sw-icon">💡</span>
                <span class="sw-txt">2구</span>
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= 2F ================= -->
      <div class="floor-row-card" id="card-2f">
        <div class="card-top-bar">
          <div class="f-title-group">
            <span class="f-badge-pill f2">2F</span>
            <span class="f-title-text">2층 대예배실 (Sanctuary)</span>
          </div>
          <button class="btn-floor-off" onclick="floorOff(2)">2F 소등</button>
        </div>

        <div class="boxes-horizontal-flow">
          <!-- 예배실 메인 조명 (6구 - 3x2 그리드) -->
          <div class="module-box">
            <div class="mod-head">
              <span>💡 예배실 메인 샹들리에 (6구)</span>
              <button class="btn-toggle-all" onclick="toggleRoomAll('sanctuary')">전체</button>
            </div>
            <div class="sw-grid-3x2">
              <button class="huge-switch-btn active" id="sw-s1" onclick="toggleLight('sanctuary', 0)"><span class="sw-icon">💡</span><span class="sw-txt">1구</span></button>
              <button class="huge-switch-btn active" id="sw-s2" onclick="toggleLight('sanctuary', 1)"><span class="sw-icon">💡</span><span class="sw-txt">2구</span></button>
              <button class="huge-switch-btn active" id="sw-s3" onclick="toggleLight('sanctuary', 2)"><span class="sw-icon">💡</span><span class="sw-txt">3구</span></button>
              <button class="huge-switch-btn active" id="sw-s4" onclick="toggleLight('sanctuary', 3)"><span class="sw-icon">💡</span><span class="sw-txt">4구</span></button>
              <button class="huge-switch-btn active" id="sw-s5" onclick="toggleLight('sanctuary', 4)"><span class="sw-icon">💡</span><span class="sw-txt">5구</span></button>
              <button class="huge-switch-btn active" id="sw-s6" onclick="toggleLight('sanctuary', 5)"><span class="sw-icon">💡</span><span class="sw-txt">6구</span></button>
            </div>
          </div>

          <!-- 시스템 냉난방기 -->
          <div class="module-box">
            <div class="mod-head">
              <span>❄️ 시스템 냉난방기</span>
            </div>
            <div class="hvac-sketch-layout">
              <!-- 온도 및 증감 -->
              <div class="hvac-temp-col">
                <div class="hvac-giant-temp" id="disp-hvac-temp">22°C</div>
                <div class="hvac-temp-stepper">
                  <button class="btn-step-mini" onclick="adjustTemp(-1)">-</button>
                  <button class="btn-step-mini" onclick="adjustTemp(1)">+</button>
                </div>
              </div>

              <!-- 2x2 버튼 매트릭스 -->
              <div class="hvac-2x2-matrix">
                <button class="hvac-action-btn pwr-on active" id="btn-hvac-on" onclick="setHvacPower(true)">
                  <span style="font-size:18px;">⚡</span><span>ON</span>
                </button>
                <button class="hvac-action-btn pwr-off" id="btn-hvac-off" onclick="setHvacPower(false)">
                  <span style="font-size:18px;">⭕</span><span>OFF</span>
                </button>
                <button class="hvac-action-btn mode-cool active" id="btn-hvac-cool" onclick="setHvacMode('cool')">
                  <span style="font-size:18px;">❄️</span><span>냉방</span>
                </button>
                <button class="hvac-action-btn mode-heat" id="btn-hvac-heat" onclick="setHvacMode('heat')">
                  <span style="font-size:18px;">🔥</span><span>난방</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ================= 1F ================= -->
      <div class="floor-row-card" id="card-1f">
        <div class="card-top-bar">
          <div class="f-title-group">
            <span class="f-badge-pill f1">1F</span>
            <span class="f-title-text">1층 메인 로비 & 화장실 & 현관</span>
          </div>
          <button class="btn-floor-off" onclick="floorOff(1)">1F 소등</button>
        </div>

        <div class="boxes-horizontal-flow">
          <!-- 1. 메인 로비 조명 (6구 - 3x2 그리드) -->
          <div class="module-box">
            <div class="mod-head">
              <span>💡 메인 로비 조명 (6구)</span>
              <button class="btn-toggle-all" onclick="toggleRoomAll('lobby')">전체</button>
            </div>
            <div class="sw-grid-3x2">
              <button class="huge-switch-btn active" id="sw-l1" onclick="toggleLight('lobby', 0)"><span class="sw-icon">💡</span><span class="sw-txt">1구</span></button>
              <button class="huge-switch-btn active" id="sw-l2" onclick="toggleLight('lobby', 1)"><span class="sw-icon">💡</span><span class="sw-txt">2구</span></button>
              <button class="huge-switch-btn active" id="sw-l3" onclick="toggleLight('lobby', 2)"><span class="sw-icon">💡</span><span class="sw-txt">3구</span></button>
              <button class="huge-switch-btn" id="sw-l4" onclick="toggleLight('lobby', 3)"><span class="sw-icon">💡</span><span class="sw-txt">4구</span></button>
              <button class="huge-switch-btn" id="sw-l5" onclick="toggleLight('lobby', 4)"><span class="sw-icon">💡</span><span class="sw-txt">5구</span></button>
              <button class="huge-switch-btn" id="sw-l6" onclick="toggleLight('lobby', 5)"><span class="sw-icon">💡</span><span class="sw-txt">6구</span></button>
            </div>
          </div>

          <!-- 2. 남성 화장실 (M - 세로 2단 스위치) -->
          <div class="module-box">
            <div class="mod-head">
              <span>🚹 남성 화장실 (M)</span>
            </div>
            <div class="restroom-sw-col">
              <button class="huge-switch-btn active" id="sw-mr-light" onclick="toggleRestroomDev('m', 'light')">
                <span class="sw-icon">💡</span>
                <span class="sw-txt">전등</span>
              </button>
              <button class="huge-switch-btn active" id="sw-mr-fan" onclick="toggleRestroomDev('m', 'fan')">
                <span class="sw-icon">🌀</span>
                <span class="sw-txt">환풍기</span>
              </button>
            </div>
          </div>

          <!-- 3. 여성 화장실 (F - 세로 2단 스위치) -->
          <div class="module-box">
            <div class="mod-head">
              <span>🚺 여성 화장실 (F)</span>
            </div>
            <div class="restroom-sw-col">
              <button class="huge-switch-btn" id="sw-wr-light" onclick="toggleRestroomDev('w', 'light')">
                <span class="sw-icon">💡</span>
                <span class="sw-txt">전등</span>
              </button>
              <button class="huge-switch-btn" id="sw-wr-fan" onclick="toggleRestroomDev('w', 'fan')">
                <span class="sw-icon">🌀</span>
                <span class="sw-txt">환풍기</span>
              </button>
            </div>
          </div>

          <!-- 4. 현관 도어락 (Door) -->
          <div class="module-box">
            <div class="mod-head">
              <span>🚪 메인 현관</span>
            </div>
            <div style="flex:1; display:flex; align-items:center; justify-content:center;">
              <button class="door-large-btn" id="btn-door-act" onclick="toggleDoor()">
                <span style="font-size:32px;" id="door-icon">🔒</span>
                <span id="door-txt">현관문 열기</span>
              </button>
            </div>
          </div>

        </div>
      </div>

    </section>
  </main>

  <!-- ========================================================
       ⚡ 3. 고속 인터랙션 엔진
       ======================================================== -->
  <script>
    const state = {
      pastor: [true, false],
      meeting: [false, false],
      sanctuary: [true, true, true, true, true, true],
      hvac: { power: true, mode: 'cool', temp: 22 },
      lobby: [true, true, true, false, false, false],
      menRestroom: { light: true, fan: true },
      womenRestroom: { light: false, fan: false },
      doorLocked: true
    };

    function toggleLight(room, idx) {
      state[room][idx] = !state[room][idx];
      syncUI();
    }

    function toggleRoomAll(room) {
      const allOn = state[room].every(Boolean);
      state[room] = state[room].map(() => !allOn);
      syncUI();
    }

    function setHvacPower(pwr) {
      state.hvac.power = pwr;
      syncUI();
    }

    function setHvacMode(m) {
      state.hvac.mode = m;
      syncUI();
    }

    function adjustTemp(d) {
      if (!state.hvac.power) return;
      state.hvac.temp += d;
      syncUI();
    }

    function toggleRestroomDev(gender, dev) {
      if (gender === 'm') state.menRestroom[dev] = !state.menRestroom[dev];
      if (gender === 'w') state.womenRestroom[dev] = !state.womenRestroom[dev];
      syncUI();
    }

    function toggleDoor() {
      state.doorLocked = !state.doorLocked;
      syncUI();
    }

    function floorOff(f) {
      if (f === 3) {
        state.pastor = [false, false];
        state.meeting = [false, false];
      } else if (f === 2) {
        state.sanctuary = [false, false, false, false, false, false];
      } else if (f === 1) {
        state.lobby = [false, false, false, false, false, false];
        state.menRestroom.light = false;
        state.womenRestroom.light = false;
      }
      syncUI();
    }

    function masterAllOff() {
      floorOff(3);
      floorOff(2);
      floorOff(1);
    }

    function safeVacationMode() {
      masterAllOff();
      state.hvac.power = false;
      state.menRestroom.fan = false;
      state.womenRestroom.fan = false;
      state.doorLocked = true;
      syncUI();
    }

    function toggleFullscreen() {
      if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen().catch(err => alert('전체화면 미지원: ' + err.message));
      } else {
        document.exitFullscreen();
      }
    }

    function syncUI() {
      // 3층
      const pOn = state.pastor.filter(Boolean).length;
      document.getElementById('rz-pastor').className = 'room-stage-box' + (pOn > 0 ? ' lit' : '');
      document.getElementById('rz-badge-pastor').innerText = `${pOn}/2구`;
      document.getElementById('bead-p1').className = 'bead-item' + (state.pastor[0] ? ' on' : '');
      document.getElementById('bead-p2').className = 'bead-item' + (state.pastor[1] ? ' on' : '');
      document.getElementById('sw-p1').className = 'huge-switch-btn' + (state.pastor[0] ? ' active' : '');
      document.getElementById('sw-p2').className = 'huge-switch-btn' + (state.pastor[1] ? ' active' : '');

      const mOn = state.meeting.filter(Boolean).length;
      document.getElementById('rz-meeting').className = 'room-stage-box' + (mOn > 0 ? ' lit' : '');
      document.getElementById('rz-badge-meeting').innerText = `${mOn}/2구`;
      document.getElementById('bead-m1').className = 'bead-item' + (state.meeting[0] ? ' on' : '');
      document.getElementById('bead-m2').className = 'bead-item' + (state.meeting[1] ? ' on' : '');
      document.getElementById('sw-m1').className = 'huge-switch-btn' + (state.meeting[0] ? ' active' : '');
      document.getElementById('sw-m2').className = 'huge-switch-btn' + (state.meeting[1] ? ' active' : '');
      document.getElementById('slab-3f-summary').innerText = `${pOn + mOn}/4구`;

      // 2층 대예배실 6구 (92px x 84px)
      const sOn = state.sanctuary.filter(Boolean).length;
      document.getElementById('rz-sanctuary').className = 'room-stage-box' + (sOn > 0 ? ' lit' : '');
      document.getElementById('rz-badge-sanctuary').innerText = `${sOn}/6구`;
      for(let i=0; i<6; i++) {
        document.getElementById(`bead-s${i+1}`).className = 'bead-item' + (state.sanctuary[i] ? ' on' : '');
        document.getElementById(`sw-s${i+1}`).className = 'huge-switch-btn' + (state.sanctuary[i] ? ' active' : '');
      }

      // 2층 HVAC
      document.getElementById('disp-hvac-temp').innerText = `${state.hvac.temp}°C`;
      document.getElementById('slab-2f-hvac-txt').innerText = state.hvac.power ? `❄️ ${state.hvac.temp}°C` : 'OFF';
      document.getElementById('btn-hvac-on').className = 'hvac-action-btn pwr-on' + (state.hvac.power ? ' active' : '');
      document.getElementById('btn-hvac-off').className = 'hvac-action-btn pwr-off' + (!state.hvac.power ? ' active' : '');
      document.getElementById('btn-hvac-cool').className = 'hvac-action-btn mode-cool' + (state.hvac.mode === 'cool' ? ' active' : '');
      document.getElementById('btn-hvac-heat').className = 'hvac-action-btn mode-heat' + (state.hvac.mode === 'heat' ? ' active' : '');

      // 1층 로비 6구 (92px x 84px)
      const lOn = state.lobby.filter(Boolean).length;
      document.getElementById('rz-lobby').className = 'room-stage-box' + (lOn > 0 ? ' lit' : '');
      document.getElementById('rz-badge-lobby').innerText = `${lOn}/6구`;
      for(let i=0; i<6; i++) {
        document.getElementById(`bead-l${i+1}`).className = 'bead-item' + (state.lobby[i] ? ' on' : '');
        document.getElementById(`sw-l${i+1}`).className = 'huge-switch-btn' + (state.lobby[i] ? ' active' : '');
      }
      document.getElementById('slab-1f-summary').innerText = `${lOn}/6구`;

      // 1층 남/여 화장실 (92px x 84px 2단 정렬)
      const mrActive = state.menRestroom.light || state.menRestroom.fan;
      document.getElementById('chip-rr-m').className = 'mini-status-chip' + (mrActive ? ' on' : '');
      document.getElementById('txt-rr-m').innerText = mrActive ? 'ON' : 'OFF';
      document.getElementById('sw-mr-light').className = 'huge-switch-btn' + (state.menRestroom.light ? ' active' : '');
      document.getElementById('sw-mr-fan').className = 'huge-switch-btn' + (state.menRestroom.fan ? ' active' : '');

      const wrActive = state.womenRestroom.light || state.womenRestroom.fan;
      document.getElementById('chip-rr-w').className = 'mini-status-chip' + (wrActive ? ' on' : '');
      document.getElementById('txt-rr-w').innerText = wrActive ? 'ON' : 'OFF';
      document.getElementById('sw-wr-light').className = 'huge-switch-btn' + (state.womenRestroom.light ? ' active' : '');
      document.getElementById('sw-wr-fan').className = 'huge-switch-btn' + (state.womenRestroom.fan ? ' active' : '');

      // 1층 도어락
      const doorChip = document.getElementById('chip-door');
      const doorBtn = document.getElementById('btn-door-act');
      if (state.doorLocked) {
        doorChip.className = 'mini-status-chip';
        document.getElementById('txt-door').innerText = '🔒 잠김';
        doorBtn.className = 'door-large-btn';
        document.getElementById('door-icon').innerText = '🔒';
        document.getElementById('door-txt').innerText = '현관문 열기';
      } else {
        doorChip.className = 'mini-status-chip on';
        document.getElementById('txt-door').innerText = '🔓 열림';
        doorBtn.className = 'door-large-btn unlocked';
        document.getElementById('door-icon').innerText = '🔓';
        document.getElementById('door-txt').innerText = '현관문 잠그기';
      }

      // 전체 통계
      const totalBulbs = pOn + mOn + sOn + lOn + (state.menRestroom.light ? 1 : 0) + (state.womenRestroom.light ? 1 : 0);
      document.getElementById('top-val-lights').innerText = `${totalBulbs} / 18구`;
      const baseWatts = totalBulbs * 35 + (state.hvac.power ? 850 : 50) + (state.menRestroom.fan ? 30 : 0) + (state.womenRestroom.fan ? 30 : 0) + 100;
      document.getElementById('top-val-power').innerText = `${baseWatts.toLocaleString()} W`;
    }

    syncUI();
  </script>
</body>
</html>
