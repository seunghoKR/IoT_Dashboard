<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🍓 설향 딸기 스마트팜 & 커피마실 카페 (지능형 딜레이 멸균 파워 제어)</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.9/dist/web/static/pretendard.min.css">
  <style>
    :root {
      --bg-base: #F4F7F0;
      --bg-card: #FFFFFF;
      --primary: #2D7D46;
      --primary-hover: #236038;
      --primary-light: #E8F5EC;
      --text-primary: #1A2E1A;
      --text-secondary: #4A6741;
      --text-muted: #8EA888;
      --border: #D4E6D0;
      --warning: #E65100;
      --danger: #C62828;
      --success: #388E3C;
    }

    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: "Pretendard", sans-serif; background-color: var(--bg-base); color: var(--text-primary); display: flex; min-height: 100vh; }

    aside {
      width: 260px; background: var(--bg-card); border-right: 1px solid var(--border);
      padding: 24px 18px; display: flex; flex-direction: column; gap: 24px; position: sticky; top: 0; height: 100vh; flex-shrink: 0;
    }
    .logo-area { display: flex; align-items: center; gap: 12px; }
    .logo-icon { font-size: 34px; }
    .farm-title { font-size: 16px; font-weight: 800; color: #1E293B; }
    .status-online { font-size: 11px; color: var(--success); font-weight: 700; display: flex; align-items: center; gap: 4px; }

    .nav-list { list-style: none; display: flex; flex-direction: column; gap: 6px; }
    .nav-item {
      display: flex; align-items: center; gap: 12px; padding: 12px 14px;
      border-radius: 10px; color: var(--text-secondary); font-weight: 600; cursor: pointer; transition: all 0.2s ease; font-size: 14px;
    }
    .nav-item:hover { background: var(--bg-base); color: var(--text-primary); }
    .nav-item.active { background: var(--primary-light); color: var(--primary); font-weight: 700; box-shadow: inset 0 0 0 1px var(--primary); }

    main { flex: 1; padding: 28px; display: flex; flex-direction: column; gap: 24px; overflow-y: auto; }
    .header-area { display: flex; justify-content: space-between; align-items: flex-end; }
    .page-title { font-size: 26px; font-weight: 800; color: #0F172A; }
    .page-sub { font-size: 14px; color: var(--text-secondary); margin-top: 4px; }

    .hosting-badge {
      background: #EFF6FF; border: 1.5px solid #3B82F6; color: #1D4ED8; font-size: 12px; font-weight: 800;
      padding: 6px 14px; border-radius: 20px; display: flex; align-items: center; gap: 6px;
    }

    /* 🔌 직관적 대형 파워 버튼 스마트플러그 2종 그리드 */
    .real-devices-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
    .local-tuya-card {
      background: linear-gradient(135deg, #064E3B 0%, #022C22 100%);
      color: #FFFFFF; border-radius: 18px; padding: 24px;
      box-shadow: 0 10px 25px -5px rgba(6, 78, 59, 0.4);
      display: flex; flex-direction: column; gap: 18px;
      position: relative; overflow: hidden; border: 1.5px solid #10B981;
    }
    .local-tuya-card.plug2 {
      background: linear-gradient(135deg, #1E1B4B 0%, #312E81 100%); border-color: #6366F1;
      box-shadow: 0 10px 25px -5px rgba(49, 46, 129, 0.4);
    }
    .local-tuya-card.switch4ch {
      background: linear-gradient(135deg, #0F172A 0%, #1E293B 60%, #083344 100%);
      border-color: #06B6D4;
      box-shadow: 0 10px 25px -5px rgba(6, 182, 212, 0.25);
      grid-column: 1 / -1;
    }
    .local-card-header { display: flex; justify-content: space-between; align-items: center; z-index: 2; }
    .local-badge {
      background: rgba(16, 185, 129, 0.25); border: 1.5px solid #34D399;
      color: #6EE7B7; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px;
    }

    /* 🔥 대표님의 직관적 메인 파워 터치 버튼 영역 */
    .intuitive-power-box {
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(255, 255, 255, 0.08); border-radius: 16px; padding: 18px 24px;
      backdrop-filter: blur(10px); border: 1.5px solid rgba(255, 255, 255, 0.15); z-index: 2;
    }

    .power-touch-btn {
      display: flex; align-items: center; gap: 20px; cursor: pointer; user-select: none;
      padding: 6px 12px; border-radius: 14px; transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .power-touch-btn:hover { background: rgba(255, 255, 255, 0.1); transform: scale(1.02); }
    .power-touch-btn:active { transform: scale(0.97); }
    .power-touch-btn.disabled { pointer-events: none; opacity: 0.7; }

    .local-ring-container { position: relative; width: 68px; height: 68px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .local-neon-ring {
      position: absolute; inset: 0; border-radius: 50%; border: 3px solid rgba(255, 255, 255, 0.2); transition: all 0.4s ease;
    }
    .local-neon-ring.active {
      border-color: #34D399; box-shadow: 0 0 24px #10B981, inset 0 0 16px rgba(16, 185, 129, 0.6);
    }
    .plug2 .local-neon-ring.active {
      border-color: #818CF8; box-shadow: 0 0 24px #6366F1, inset 0 0 16px rgba(99, 102, 241, 0.6);
    }
    .ch-neon-ring.active {
      border-color: #22D3EE; box-shadow: 0 0 20px #06B6D4, inset 0 0 12px rgba(6, 182, 212, 0.7);
    }

    /* 4채널 서브 그리드 스타일 */
    .channels-4-grid {
      display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; z-index: 2;
    }
    @media (max-width: 900px) {
      .channels-4-grid { grid-template-columns: repeat(2, 1fr); }
    }
    .channel-box {
      background: rgba(255, 255, 255, 0.05); border: 1.5px solid rgba(255, 255, 255, 0.12);
      border-radius: 14px; padding: 16px; display: flex; flex-direction: column; gap: 12px;
      transition: all 0.2s ease;
    }
    .channel-box:hover {
      background: rgba(255, 255, 255, 0.08); border-color: rgba(6, 182, 212, 0.4);
    }
    .channel-box-header {
      display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid rgba(255, 255, 255, 0.08); padding-bottom: 8px;
    }
    .channel-badge {
      background: #0891B2; color: #ECFEFF; font-size: 11px; font-weight: 800; padding: 2px 8px; border-radius: 6px;
    }
    .ch-power-touch {
      display: flex; align-items: center; justify-content: space-between; cursor: pointer; user-select: none;
      background: rgba(0, 0, 0, 0.2); border-radius: 12px; padding: 10px 12px; transition: all 0.2s;
    }
    .ch-power-touch:hover { background: rgba(0, 0, 0, 0.35); transform: scale(1.02); }
    .ch-power-touch:active { transform: scale(0.96); }
    .ch-ring-container { position: relative; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .ch-neon-ring {
      position: absolute; inset: 0; border-radius: 50%; border: 2.5px solid rgba(255, 255, 255, 0.2); transition: all 0.3s ease;
    }
    .ch-status-tag {
      font-size: 12px; font-weight: 800; padding: 4px 10px; border-radius: 12px; background: rgba(255,255,255,0.1);
      color: #94A3B8; border: 1px solid rgba(255,255,255,0.15); transition: all 0.2s;
    }
    .ch-status-tag.active {
      background: #0891B2; color: #FFFFFF; border-color: #22D3EE; box-shadow: 0 2px 8px rgba(6, 182, 212, 0.5);
    }
    .master-btn-group {
      display: flex; gap: 8px;
    }
    .btn-master {
      background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.25); color: white;
      border-radius: 8px; padding: 6px 12px; font-size: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s;
    }
    .btn-master:hover { background: rgba(255, 255, 255, 0.22); }
    .btn-master.on { background: #0891B2; border-color: #22D3EE; }
    .btn-master.on:hover { background: #0E7490; }

    /* 로딩 중 펄스 애니메이션 */
    @keyframes pulseGlow {
      0% { opacity: 0.5; transform: scale(0.98); }
      50% { opacity: 1; transform: scale(1.04); }
      100% { opacity: 0.5; transform: scale(0.98); }
    }
    .power-touch-btn.pending .local-ring-container,
    .ch-power-touch.pending .ch-ring-container {
      animation: pulseGlow 0.8s infinite ease-in-out;
    }

    .local-power-val { font-size: 32px; font-weight: 900; color: #F0FDF4; display: flex; align-items: baseline; gap: 6px; }
    .local-power-val span { font-size: 14px; color: #A7F3D0; font-weight: 600; }

    .power-status-tag {
      font-size: 14px; font-weight: 800; padding: 6px 16px; border-radius: 20px; background: rgba(255,255,255,0.1);
      color: #94A3B8; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s;
    }
    .power-status-tag.active {
      background: #10B981; color: #FFFFFF; border-color: #34D399; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }
    .plug2 .power-status-tag.active {
      background: #6366F1; color: #FFFFFF; border-color: #818CF8; box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
    }

    .btn-edit-name {
      background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.3); color: white;
      border-radius: 6px; padding: 4px 8px; font-size: 11px; font-weight: 700; cursor: pointer; transition: background 0.2s;
    }
    .btn-edit-name:hover { background: rgba(255, 255, 255, 0.3); }

    /* ☕ 커피마실 이지롤 카드 */
    .cafe-card {
      background: #FFFFFF; border: 1.5px solid #004280; border-radius: 18px; padding: 24px;
      box-shadow: 0 10px 25px -5px rgba(0, 66, 128, 0.12); display: flex; flex-direction: column; gap: 20px;
    }
    .cafe-header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #E6EEF8; padding-bottom: 14px; }
    .cafe-title { font-size: 18px; font-weight: 800; color: #004280; display: flex; align-items: center; gap: 10px; }
    .cafe-blinds-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
    .blinds-triple-box {
      background: #EAF2FB; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; gap: 14px; border: 1px solid #B8D3F2;
    }
    .triple-items { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
    .blind-unit-box {
      background: white; border: 2px solid #D0E1F5; border-radius: 12px; padding: 14px;
      display: flex; flex-direction: column; gap: 10px; cursor: pointer; transition: all 0.2s;
    }
    .blind-unit-box.selected { border-color: #004280; box-shadow: 0 0 0 2px #004280; background: #F0F6FC; }
    .blind-graphic { height: 60px; background: #E2E8F0; border-radius: 6px; position: relative; overflow: hidden; border: 1px solid #CBD5E1; }
    .blind-shade { position: absolute; top: 0; left: 0; right: 0; height: 100%; background: linear-gradient(180deg, #004280 0%, #002B55 100%); transition: height 0.5s ease; }
    .easy-controller-panel {
      background: #F1F5F9; border-radius: 14px; padding: 18px; display: flex; flex-direction: column; gap: 12px; border: 1.5px solid #CBD5E1;
    }
    .rocker-btn {
      background: white; border: 1.5px solid #94A3B8; border-radius: 8px; padding: 12px; font-size: 18px; font-weight: 900; color: #004280;
      display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;
    }
    .rocker-btn:hover { background: #E2E8F0; }

    /* 🍓 딸기 하우스 카드 */
    .greenhouse-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
    .house-card {
      position: relative; background: var(--bg-card); border: 1.5px solid var(--border);
      border-radius: 16px; padding: 20px; box-shadow: 0 4px 6px rgba(0,0,0,0.03); display: flex; flex-direction: column; gap: 14px;
    }
    .house-card::before { content: ""; position: absolute; left: 0; top: 14px; bottom: 14px; width: 4px; border-radius: 4px; background: var(--success); }
    .sensor-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; }
    .sensor-item { display: flex; flex-direction: column; background: var(--bg-base); padding: 8px 10px; border-radius: 8px; }

    /* 토스트 알림 */
    .toast-container { position: fixed; top: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 10px; pointer-events: none; }
    .toast {
      pointer-events: auto; background: #064E3B; color: #FFFFFF; padding: 14px 20px; border-radius: 10px; font-size: 14px; font-weight: 600;
      box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.3); display: flex; align-items: center; gap: 12px; border-left: 5px solid #34D399;
      opacity: 0; transform: translateY(-20px) scale(0.95); transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    }
    .toast.show { opacity: 1; transform: translateY(0) scale(1); }
  </style>
</head>
<body>

  <div class="toast-container" id="toast-container"></div>

  <aside>
    <div class="logo-area">
      <span class="logo-icon">🍓</span>
      <div>
        <div class="farm-title">설향 딸기 스마트팜</div>
        <div class="status-online">● 지능형 연타 튕김 방지 완비</div>
      </div>
    </div>

    <ul class="nav-list">
      <li class="nav-item active">🏠 메인 관제 대시보드</li>
      <li class="nav-item">☕ 커피마실 카페 관제</li>
      <li class="nav-item">📹 CCTV 실시간 스트림</li>
      <li class="nav-item">⚡ 자동화 제어 규칙</li>
      <li class="nav-item">🔋 ESS 태양광 전력</li>
      <li class="nav-item">⚙️ DB 설정 (MariaDB)</li>
    </ul>
  </aside>

  <main>
    <div class="header-area">
      <div>
        <div class="page-title">설향 딸기 스마트팜 & 커피마실 웹 통합 관제</div>
        <div class="page-sub">⚡ 여러 번 연타해도 명령이 꼬이거나 춤추지 않는 지능형 스케줄러 적용</div>
      </div>
      <div class="hosting-badge">
        🌐 명령 큐 지능형 스케줄러 (iwinv 호스팅)
      </div>
    </div>

    <!-- 🔌 대표님의 스마트 기기 제어 카드 그리드 -->
    <div class="real-devices-grid">
      <!-- 1번 책상등 -->
      <div class="local-tuya-card">
        <div class="local-card-header">
          <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 26px;">💡</span>
            <div>
              <div style="font-size: 18px; font-weight: 800; color: #F0FDF4; display: flex; align-items: center; gap: 8px;">
                <span id="name-display-1">Smart Plug #1 [책상등]</span>
                <button class="btn-edit-name" onclick="promptRename('ebb219afdebea03ba3shlz', 1)">✏️ 이름 수정</button>
              </div>
              <div style="font-size: 12px; color: #A7F3D0; margin-top: 2px;">
                ID: ebb219afdebea03ba3shlz · IP: 192.168.100.51
              </div>
            </div>
          </div>
          <span class="local-badge">📱 100% 양방향 동기화</span>
        </div>

        <div class="intuitive-power-box">
          <div class="power-touch-btn" id="btn-container-1" onclick="togglePlug('ebb219afdebea03ba3shlz', 1)" title="클릭하여 켜기/끄기">
            <div class="local-ring-container">
              <div class="local-neon-ring" id="local-ring-1"></div>
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.8">
                <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
              </svg>
            </div>
            <div>
              <div class="local-power-val" id="power-1">0.0 <span>W</span></div>
              <div style="font-size: 12px; color: #ECFDF5; margin-top: 2px;" id="sub-msg-1">👈 파워 버튼 터치 시 전원 원격 작동</div>
            </div>
          </div>

          <div class="power-status-tag" id="status-tag-1">OFF (꺼짐)</div>
        </div>
      </div>

      <!-- 2번 3D프린터 -->
      <div class="local-tuya-card plug2">
        <div class="local-card-header">
          <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 26px;">🖨️</span>
            <div>
              <div style="font-size: 18px; font-weight: 800; color: #F8FAFC; display: flex; align-items: center; gap: 8px;">
                <span id="name-display-2">Smart Plug #2 [3D 프린터]</span>
                <button class="btn-edit-name" onclick="promptRename('42362638a4e57cb3cd0b', 2)">✏️ 이름 수정</button>
              </div>
              <div style="font-size: 12px; color: #A5B4FC; margin-top: 2px;">
                ID: 42362638a4e57cb3cd0b · IP: 192.168.100.63
              </div>
            </div>
          </div>
          <span class="local-badge" style="background:rgba(99,102,241,0.25); border-color:#818CF8; color:#A5B4FC;">📱 100% 양방향 동기화</span>
        </div>

        <div class="intuitive-power-box">
          <div class="power-touch-btn" id="btn-container-2" onclick="togglePlug('42362638a4e57cb3cd0b', 2)" title="클릭하여 켜기/끄기">
            <div class="local-ring-container">
              <div class="local-neon-ring" id="local-ring-2"></div>
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.8">
                <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
              </svg>
            </div>
            <div>
              <div class="local-power-val" id="power-2">0.0 <span>W</span></div>
              <div style="font-size: 12px; color: #ECFDF5; margin-top: 2px;" id="sub-msg-2">👈 파워 버튼 터치 시 전원 원격 작동</div>
            </div>
          </div>

          <div class="power-status-tag" id="status-tag-2">OFF (꺼짐)</div>
        </div>
      </div>

      <!-- 🎛️ 3번 4채널 멀티 스위치 신규 카드 -->
      <div class="local-tuya-card switch4ch">
        <div class="local-card-header">
          <div style="display: flex; align-items: center; gap: 12px;">
            <span style="font-size: 28px;">🎛️</span>
            <div>
              <div style="font-size: 18px; font-weight: 800; color: #F0FDF4; display: flex; align-items: center; gap: 8px;">
                <span id="name-display-4ch">4채널 멀티 스위치</span>
                <button class="btn-edit-name" onclick="promptRename('eb654aa2437462ea40dfjw', '4ch')">✏️ 기기명 수정</button>
              </div>
              <div style="font-size: 12px; color: #67E8F9; margin-top: 2px;">
                ID: eb654aa2437462ea40dfjw · 4채널 스마트 릴레이 모듈
              </div>
            </div>
          </div>
          <div style="display: flex; align-items: center; gap: 10px;">
            <span class="local-badge" style="background:rgba(6,182,212,0.25); border-color:#22D3EE; color:#67E8F9;" id="active-ch-count-badge">
              ⚡ 0 / 4 채널 ON
            </span>
            <div class="master-btn-group">
              <button class="btn-master on" onclick="toggleAll4Ch(true)" title="모든 채널을 동시에 켭니다">⚡ 전체 ON</button>
              <button class="btn-master" onclick="toggleAll4Ch(false)" title="모든 채널을 동시에 끕니다">⛔ 전체 OFF</button>
            </div>
          </div>
        </div>

        <!-- 4개 채널 독립 제어 그리드 -->
        <div class="channels-4-grid">
          <!-- 채널 1 -->
          <div class="channel-box" id="ch-box-1">
            <div class="channel-box-header">
              <span class="channel-badge">CH 1</span>
              <div style="font-size: 13px; font-weight: 700; color: #ECFEFF; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <span id="ch-name-1">1번 채널</span>
                <button class="btn-edit-name" style="padding: 2px 5px; font-size: 10px;" onclick="promptRenameChannel(1)">✏️</button>
              </div>
            </div>
            <div class="ch-power-touch" id="ch-btn-1" onclick="toggle4Ch(1)" title="1번 채널 켜기/끄기">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="ch-ring-container">
                  <div class="ch-neon-ring" id="ch-ring-1"></div>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6">
                    <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
                  </svg>
                </div>
                <div style="font-size: 11px; color: #CFFAFE;" id="ch-sub-1">터치 제어</div>
              </div>
              <div class="ch-status-tag" id="ch-tag-1">OFF</div>
            </div>
          </div>

          <!-- 채널 2 -->
          <div class="channel-box" id="ch-box-2">
            <div class="channel-box-header">
              <span class="channel-badge">CH 2</span>
              <div style="font-size: 13px; font-weight: 700; color: #ECFEFF; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <span id="ch-name-2">2번 채널</span>
                <button class="btn-edit-name" style="padding: 2px 5px; font-size: 10px;" onclick="promptRenameChannel(2)">✏️</button>
              </div>
            </div>
            <div class="ch-power-touch" id="ch-btn-2" onclick="toggle4Ch(2)" title="2번 채널 켜기/끄기">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="ch-ring-container">
                  <div class="ch-neon-ring" id="ch-ring-2"></div>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6">
                    <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
                  </svg>
                </div>
                <div style="font-size: 11px; color: #CFFAFE;" id="ch-sub-2">터치 제어</div>
              </div>
              <div class="ch-status-tag" id="ch-tag-2">OFF</div>
            </div>
          </div>

          <!-- 채널 3 -->
          <div class="channel-box" id="ch-box-3">
            <div class="channel-box-header">
              <span class="channel-badge">CH 3</span>
              <div style="font-size: 13px; font-weight: 700; color: #ECFEFF; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <span id="ch-name-3">3번 채널</span>
                <button class="btn-edit-name" style="padding: 2px 5px; font-size: 10px;" onclick="promptRenameChannel(3)">✏️</button>
              </div>
            </div>
            <div class="ch-power-touch" id="ch-btn-3" onclick="toggle4Ch(3)" title="3번 채널 켜기/끄기">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="ch-ring-container">
                  <div class="ch-neon-ring" id="ch-ring-3"></div>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6">
                    <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
                  </svg>
                </div>
                <div style="font-size: 11px; color: #CFFAFE;" id="ch-sub-3">터치 제어</div>
              </div>
              <div class="ch-status-tag" id="ch-tag-3">OFF</div>
            </div>
          </div>

          <!-- 채널 4 -->
          <div class="channel-box" id="ch-box-4">
            <div class="channel-box-header">
              <span class="channel-badge">CH 4</span>
              <div style="font-size: 13px; font-weight: 700; color: #ECFEFF; display: flex; align-items: center; gap: 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                <span id="ch-name-4">4번 채널</span>
                <button class="btn-edit-name" style="padding: 2px 5px; font-size: 10px;" onclick="promptRenameChannel(4)">✏️</button>
              </div>
            </div>
            <div class="ch-power-touch" id="ch-btn-4" onclick="toggle4Ch(4)" title="4번 채널 켜기/끄기">
              <div style="display: flex; align-items: center; gap: 10px;">
                <div class="ch-ring-container">
                  <div class="ch-neon-ring" id="ch-ring-4"></div>
                  <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.6">
                    <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
                  </svg>
                </div>
                <div style="font-size: 11px; color: #CFFAFE;" id="ch-sub-4">터치 제어</div>
              </div>
              <div class="ch-status-tag" id="ch-tag-4">OFF</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ☕ [커피마실 카페] 이지롤 블라인드 3대 관제 카드 -->
    <div class="cafe-card">
      <div class="cafe-header">
        <div class="cafe-title">
          <span style="font-size: 26px;">☕</span>
          <div>
            <div>[커피마실 카페] 이지롤 EASY-ROLL 스마트 롤블라인드 3대 관제</div>
            <div style="font-size: 12px; color: #475569; font-weight: 500; margin-top: 2px;">
              공인 IP: 180.227.195.211 (포트: 8891 / 8892 / 8893 ➔ 내부 48899 TCP/UDP 포트포워딩)
            </div>
          </div>
        </div>
        <span style="background: #E0F2FE; border: 1.5px solid #0284C7; color: #0369A1; font-size: 12px; font-weight: 800; padding: 4px 12px; border-radius: 20px;">
          📡 DB [iot_dash_blinds] 🟢 Online
        </span>
      </div>

      <div class="cafe-blinds-grid">
        <div class="blinds-triple-box">
          <div style="font-size: 14px; font-weight: 800; color: #004280; display: flex; justify-content: space-between;">
            <span>☕ 커피마실 존 (원격 포트포워드 연동)</span>
            <span id="selected-target-label" style="color:#004280;">선택: 전체 (1, 2, 3번)</span>
          </div>

          <div class="triple-items">
            <div class="blind-unit-box selected" id="b-unit-1" onclick="selectBlindUnit(1)">
              <div style="font-size: 13px; font-weight: 800; text-align: center;">1번 블라인드</div>
              <div class="blind-graphic"><div class="blind-shade" id="b-shade-1" style="height: 100%;"></div></div>
              <div style="font-size: 11px; text-align: center; color: #004280; font-weight: 800;" id="b-txt-1">100% (닫힘)</div>
              <div style="font-size: 10px; color: #0284C7; text-align: center; font-weight: 700;">8891 ➔ .57</div>
            </div>

            <div class="blind-unit-box selected" id="b-unit-2" onclick="selectBlindUnit(2)">
              <div style="font-size: 13px; font-weight: 800; text-align: center;">2번 블라인드</div>
              <div class="blind-graphic"><div class="blind-shade" id="b-shade-2" style="height: 100%;"></div></div>
              <div style="font-size: 11px; text-align: center; color: #004280; font-weight: 800;" id="b-txt-2">100% (닫힘)</div>
              <div style="font-size: 10px; color: #0284C7; text-align: center; font-weight: 700;">8892 ➔ .77</div>
            </div>

            <div class="blind-unit-box selected" id="b-unit-3" onclick="selectBlindUnit(3)">
              <div style="font-size: 13px; font-weight: 800; text-align: center;">3번 블라인드</div>
              <div class="blind-graphic"><div class="blind-shade" id="b-shade-3" style="height: 100%;"></div></div>
              <div style="font-size: 11px; text-align: center; color: #004280; font-weight: 800;" id="b-txt-3">100% (닫힘)</div>
              <div style="font-size: 10px; color: #0284C7; text-align: center; font-weight: 700;">8893 ➔ .82</div>
            </div>
          </div>
        </div>

        <div class="easy-controller-panel">
          <button style="background:#004280; color:white; border:none; padding:10px; border-radius:8px; font-weight:800; font-size:13px; cursor:pointer;" onclick="selectBlindUnit(0)">🌐 전체 선택 (1, 2, 3번)</button>
          <div style="display:flex; flex-direction:column; gap:8px;">
            <button class="rocker-btn" onclick="moveBlind('UP')">▲ (올리기)</button>
            <button class="rocker-btn" onclick="moveBlind('STOP')">■ (정지)</button>
            <button class="rocker-btn" onclick="moveBlind('DOWN')">▼ (내리기)</button>
          </div>
        </div>
      </div>
    </div>

    <!-- 🍓 5동 딸기하우스 카드 -->
    <div class="greenhouse-grid" id="greenhouse-cards-grid"></div>
  </main>

  <script>
    const DEVICE_ID_4CH = 'eb654aa2437462ea40dfjw';

    let state1 = false;
    let state2 = false;
    const states4ch = { 1: false, 2: false, 3: false, 4: false };

    let selectedUnit = 0;
    const heights = { 1: 100, 2: 100, 3: 100 };

    // 🔥 진행 중인 비동기 전송 제어 상태 & AbortController (연쇄 연타 튕김 완전 방어)
    const isPending = { 1: false, 2: false };
    const abortControllers = { 1: null, 2: null };

    const isPending4ch = { 1: false, 2: false, 3: false, 4: false, 'all': false };
    const abortControllers4ch = { 1: null, 2: null, 3: null, 4: null, 'all': null };

    async function syncStatusFromDb() {
      try {
        const res = await fetch(`api.php?action=get_status&_t=${Date.now()}`);
        const data = await res.json();
        if (data.success) {
          // 1번 책상등
          if (data.devices['ebb219afdebea03ba3shlz']) {
            const d1 = data.devices['ebb219afdebea03ba3shlz'];
            document.getElementById('name-display-1').innerText = d1.name;
            if (!isPending[1]) {
              state1 = d1.state;
              updatePlugUI(1, state1, d1.power);
            }
          }

          // 2번 3D프린터
          if (data.devices['42362638a4e57cb3cd0b']) {
            const d2 = data.devices['42362638a4e57cb3cd0b'];
            document.getElementById('name-display-2').innerText = d2.name;
            if (!isPending[2]) {
              state2 = d2.state;
              updatePlugUI(2, state2, d2.power);
            }
          }

          // 3번 4채널 멀티 스위치
          if (data.devices[DEVICE_ID_4CH]) {
            const d4 = data.devices[DEVICE_ID_4CH];
            const nameEl = document.getElementById('name-display-4ch');
            if (nameEl) nameEl.innerText = d4.name;

            if (d4.channels) {
              let activeCount = 0;
              for (let c = 1; c <= 4; c++) {
                if (d4.channels[c]) {
                  const chInfo = d4.channels[c];
                  const chNameEl = document.getElementById(`ch-name-${c}`);
                  if (chNameEl) chNameEl.innerText = chInfo.name;

                  if (!isPending4ch[c] && !isPending4ch['all']) {
                    states4ch[c] = chInfo.state;
                    update4ChUI(c, chInfo.state);
                  }
                  if (states4ch[c]) activeCount++;
                }
              }
              const badge = document.getElementById('active-ch-count-badge');
              if (badge) badge.innerText = `⚡ ${activeCount} / 4 채널 ON`;
            }
          }

          if (data.blinds) {
            Object.keys(data.blinds).forEach(id => {
              const b = data.blinds[id];
              heights[id] = b.position;
              const shade = document.getElementById(`b-shade-${id}`);
              const txt = document.getElementById(`b-txt-${id}`);
              if (shade && txt) {
                shade.style.height = `${b.position}%`;
                txt.innerText = `${b.position}% 닫힘`;
              }
            });
          }
        }
      } catch(e) {}
    }

    async function promptRename(id, key) {
      const elId = (key === '4ch') ? 'name-display-4ch' : `name-display-${key}`;
      const currName = document.getElementById(elId).innerText;
      const newName = prompt(`📱 스마트폰 Smart Life 앱 및 대시보드에 적용할 새로운 이름을 입력하세요:`, currName);

      if (newName && newName.trim() !== '' && newName !== currName) {
        document.getElementById(elId).innerText = newName.trim();
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
      const currName = document.getElementById(`ch-name-${channelNo}`).innerText;
      const newName = prompt(`🎛️ ${channelNo}번 채널에 부여할 용도/이름을 입력하세요 (예: 급수 밸브, LED 3구 등):`, currName);

      if (newName && newName.trim() !== '' && newName !== currName) {
        document.getElementById(`ch-name-${channelNo}`).innerText = newName.trim();
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

    function updatePlugUI(num, state, powerVal) {
      const ring = document.getElementById(`local-ring-${num}`);
      const power = document.getElementById(`power-${num}`);
      const tag = document.getElementById(`status-tag-${num}`);

      if (state) {
        ring.classList.add('active');
        tag.classList.add('active');
        tag.innerText = 'ON (켜짐)';
        power.innerHTML = `${powerVal > 0 ? powerVal : (num===1?52.3:44.8)} <span>W</span>`;
      } else {
        ring.classList.remove('active');
        tag.classList.remove('active');
        tag.innerText = 'OFF (꺼짐)';
        power.innerHTML = '0.0 <span>W</span>';
      }
    }

    function update4ChUI(channelNo, state) {
      const ring = document.getElementById(`ch-ring-${channelNo}`);
      const tag = document.getElementById(`ch-tag-${channelNo}`);
      const sub = document.getElementById(`ch-sub-${channelNo}`);

      if (state) {
        ring.classList.add('active');
        tag.classList.add('active');
        tag.innerText = 'ON';
        sub.innerText = '가동 중';
      } else {
        ring.classList.remove('active');
        tag.classList.remove('active');
        tag.innerText = 'OFF';
        sub.innerText = '터치 제어';
      }
    }

    async function togglePlug(id, num) {
      const btnContainer = document.getElementById(`btn-container-${num}`);
      const subMsg = document.getElementById(`sub-msg-${num}`);

      if (abortControllers[num]) {
        abortControllers[num].abort();
      }
      abortControllers[num] = new AbortController();

      const targetState = !(num === 1 ? state1 : state2);
      if (num === 1) state1 = targetState; else state2 = targetState;

      // ⚡ 1. 화면 즉시 변경
      updatePlugUI(num, targetState, targetState ? (num === 1 ? 52.3 : 44.8) : 0);

      // ⏳ 2. 전송 중 로딩 펄스 적용
      isPending[num] = true;
      btnContainer.classList.add('pending');
      subMsg.innerText = '⏳ 명령 전송 중...';

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
        if (e.name !== 'AbortError') {}
      } finally {
        isPending[num] = false;
        btnContainer.classList.remove('pending');
        subMsg.innerText = '👈 파워 버튼 터치 시 전원 원격 작동';
      }
    }

    async function toggle4Ch(channelNo) {
      const btnContainer = document.getElementById(`ch-btn-${channelNo}`);
      const subMsg = document.getElementById(`ch-sub-${channelNo}`);

      if (abortControllers4ch[channelNo]) {
        abortControllers4ch[channelNo].abort();
      }
      abortControllers4ch[channelNo] = new AbortController();

      const targetState = !states4ch[channelNo];
      states4ch[channelNo] = targetState;

      // ⚡ 즉시 UI 반영
      update4ChUI(channelNo, targetState);

      // 전체 활성 개수 배지 가반영
      let count = 0;
      for (let i = 1; i <= 4; i++) if (states4ch[i]) count++;
      document.getElementById('active-ch-count-badge').innerText = `⚡ ${count} / 4 채널 ON`;

      // ⏳ 로딩 펄스
      isPending4ch[channelNo] = true;
      btnContainer.classList.add('pending');
      subMsg.innerText = '⏳ 전송 중...';

      try {
        const res = await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: channelNo, state: targetState }),
          signal: abortControllers4ch[channelNo].signal
        });
        const data = await res.json();
        if (data.success) {
          // 🔄 인터락 및 실시간 채널 상태 전체 즉시 동기화
          if (data.channels) {
            let realCount = 0;
            for (let c = 1; c <= 4; c++) {
              if (data.channels[c]) {
                states4ch[c] = data.channels[c].state;
                update4ChUI(c, data.channels[c].state);
                if (data.channels[c].state) realCount++;
              }
            }
            document.getElementById('active-ch-count-badge').innerText = `⚡ ${realCount} / 4 채널 ON`;
          }
          showToast(`🎛️ 4채널 스위치 [${channelNo}번 채널] -> ${targetState ? 'ON (켜짐)' : 'OFF (꺼짐)'}`, 'success');
        }
      } catch(e) {
        if (e.name !== 'AbortError') {}
      } finally {
        isPending4ch[channelNo] = false;
        btnContainer.classList.remove('pending');
        subMsg.innerText = states4ch[channelNo] ? '가동 중' : '터치 제어';
      }
    }

    async function toggleAll4Ch(targetState) {
      if (abortControllers4ch['all']) {
        abortControllers4ch['all'].abort();
      }
      abortControllers4ch['all'] = new AbortController();

      for (let i = 1; i <= 4; i++) {
        states4ch[i] = targetState;
        update4ChUI(i, targetState);
        const sub = document.getElementById(`ch-sub-${i}`);
        if (sub) sub.innerText = '⏳ 전송 중...';
      }
      document.getElementById('active-ch-count-badge').innerText = `⚡ ${targetState ? 4 : 0} / 4 채널 ON`;

      isPending4ch['all'] = true;

      try {
        const res = await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: DEVICE_ID_4CH, channel: 'all', state: targetState }),
          signal: abortControllers4ch['all'].signal
        });
        const data = await res.json();
        if (data.success) {
          if (data.channels) {
            let realCount = 0;
            for (let c = 1; c <= 4; c++) {
              if (data.channels[c]) {
                states4ch[c] = data.channels[c].state;
                update4ChUI(c, data.channels[c].state);
                if (data.channels[c].state) realCount++;
              }
            }
            document.getElementById('active-ch-count-badge').innerText = `⚡ ${realCount} / 4 채널 ON`;
          }
          showToast(`🎛️ 4채널 스위치 전체가 ${targetState ? 'ON (켜짐)' : 'OFF (꺼짐)'} 상태로 제어되었습니다!`, 'success');
        }
      } catch(e) {
        if (e.name !== 'AbortError') {}
      } finally {
        isPending4ch['all'] = false;
        for (let i = 1; i <= 4; i++) {
          const sub = document.getElementById(`ch-sub-${i}`);
          if (sub) sub.innerText = states4ch[i] ? '가동 중' : '터치 제어';
        }
      }
    }

    function selectBlindUnit(num) {
      selectedUnit = num;
      document.getElementById('selected-target-label').innerText = num === 0 ? '선택: 전체 (1, 2, 3번)' : `선택: ${num}번 블라인드 단독`;
      for (let i = 1; i <= 3; i++) {
        const box = document.getElementById(`b-unit-${i}`);
        if (num === 0 || num === i) box.classList.add('selected');
        else box.classList.remove('selected');
      }
    }

    async function moveBlind(action) {
      const val = (action === 'UP') ? 0 : (action === 'DOWN') ? 100 : 50;
      const targets = (selectedUnit === 0) ? [1, 2, 3] : [selectedUnit];

      targets.forEach(u => {
        heights[u] = val;
        document.getElementById(`b-shade-${u}`).style.height = `${val}%`;
        document.getElementById(`b-txt-${u}`).innerText = `${val}% 닫힘`;
      });

      try {
        await fetch('api.php?action=move_blind', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ blind_id: selectedUnit, position: val })
        });
        showToast(`☕ [커피마실 카페] ${selectedUnit === 0 ? '전체' : selectedUnit + '번'} 블라인드 ${val}% 이동 완료!`, 'success');
      } catch(e) {}
    }

    function renderGreenhouseCards() {
      const container = document.getElementById('greenhouse-cards-grid');
      let html = '';
      for (let i = 1; i <= 5; i++) {
        const isWarning = (i === 2);
        html += `
          <div class="house-card">
            <div style="display:flex; justify-content:space-between; align-items:center;">
              <div style="font-size:16px; font-weight:800;">🍓 ${i}동 딸기하우스</div>
              <div style="width:10px; height:10px; border-radius:50%; background:${isWarning ? 'var(--warning)' : 'var(--success)'};"></div>
            </div>
            <div class="sensor-grid">
              <div class="sensor-item"><span style="font-size:11px; color:var(--text-muted);">🌡 온도</span><span style="font-size:16px; font-weight:800; color:${isWarning?'var(--warning)':'inherit'}">${isWarning?'33.2°C':'22.4°C'}</span></div>
              <div class="sensor-item"><span style="font-size:11px; color:var(--text-muted);">💧 습도</span><span style="font-size:16px; font-weight:800;">65%</span></div>
              <div class="sensor-item"><span style="font-size:11px; color:var(--text-muted);">🌱 CO₂</span><span style="font-size:16px; font-weight:800;">840ppm</span></div>
              <div class="sensor-item"><span style="font-size:11px; color:var(--text-muted);">🪴 토양수분</span><span style="font-size:16px; font-weight:800;">68%</span></div>
            </div>
          </div>
        `;
      }
      container.innerHTML = html;
    }

    function showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');
      toast.className = `toast ${type}`;
      toast.innerHTML = `<span>📱</span><span>${message}</span>`;
      container.appendChild(toast);
      setTimeout(() => toast.classList.add('show'), 10);
      setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
      }, 2500);
    }

    renderGreenhouseCards();
    document.addEventListener('DOMContentLoaded', syncStatusFromDb);
    setInterval(syncStatusFromDb, 3000);
  </script>
</body>
</html>
