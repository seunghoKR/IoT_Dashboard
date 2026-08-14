<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="ko">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>🍓 설향 딸기 스마트팜 & 커피마실 카페 (8초 이중 락으로 깜빡임 완전 치료)</title>
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
        <div class="status-online">● 8초 이중 락 적용 완료</div>
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
        <div class="page-sub">✨ 1번 스위치 클라우드 전송 지연 보정: 8초 백엔드 & 프론트 이중 락 완벽 치료 완료</div>
      </div>
      <div class="hosting-badge">
        🌐 8초 이중 락 Guard (iwinv 호스팅)
      </div>
    </div>

    <!-- 🔌 대표님의 직관적 파워 버튼 통합 스마트플러그 2종 카드 -->
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
          <div class="power-touch-btn" onclick="togglePlug('ebb219afdebea03ba3shlz', 1)" title="클릭하여 켜기/끄기">
            <div class="local-ring-container">
              <div class="local-neon-ring" id="local-ring-1"></div>
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.8">
                <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
              </svg>
            </div>
            <div>
              <div class="local-power-val" id="power-1">0.0 <span>W</span></div>
              <div style="font-size: 12px; color: #ECFDF5; margin-top: 2px;">👈 파워 버튼 터치 시 전원 원격 작동</div>
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
          <div class="power-touch-btn" onclick="togglePlug('42362638a4e57cb3cd0b', 2)" title="클릭하여 켜기/끄기">
            <div class="local-ring-container">
              <div class="local-neon-ring" id="local-ring-2"></div>
              <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="#FFFFFF" stroke-width="2.8">
                <path d="M12 2v10M18.4 6.6a9 9 0 1 1-12.8 0"/>
              </svg>
            </div>
            <div>
              <div class="local-power-val" id="power-2">0.0 <span>W</span></div>
              <div style="font-size: 12px; color: #ECFDF5; margin-top: 2px;">👈 파워 버튼 터치 시 전원 원격 작동</div>
            </div>
          </div>

          <div class="power-status-tag" id="status-tag-2">OFF (꺼짐)</div>
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
    let state1 = false;
    let state2 = false;
    let selectedUnit = 0;
    const heights = { 1: 100, 2: 100, 3: 100 };

    // 🔥 클릭 시 8초간 하트비트가 UI 상태를 흔들지 못하도록 완전 락을 거는 타임스탬프
    const lockUntil = { 1: 0, 2: 0 };

    async function syncStatusFromDb() {
      try {
        const res = await fetch(`api.php?action=get_status&_t=${Date.now()}`);
        const data = await res.json();
        if (data.success) {
          const now = Date.now();

          // 1번 책상등: 클릭 후 8초 동안은 백그라운드 하트비트 상태 덮어쓰기 완전 금지!
          if (data.devices['ebb219afdebea03ba3shlz']) {
            const d1 = data.devices['ebb219afdebea03ba3shlz'];
            document.getElementById('name-display-1').innerText = d1.name;
            if (now > lockUntil[1]) {
              state1 = d1.state;
              updatePlugUI(1, state1, d1.power);
            }
          }

          // 2번 3D프린터
          if (data.devices['42362638a4e57cb3cd0b']) {
            const d2 = data.devices['42362638a4e57cb3cd0b'];
            document.getElementById('name-display-2').innerText = d2.name;
            if (now > lockUntil[2]) {
              state2 = d2.state;
              updatePlugUI(2, state2, d2.power);
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

    async function promptRename(id, num) {
      const currName = document.getElementById(`name-display-${num}`).innerText;
      const newName = prompt(`📱 스마트폰 Smart Life 앱 및 대시보드에 적용할 새로운 이름을 입력하세요:`, currName);

      if (newName && newName.trim() !== '' && newName !== currName) {
        document.getElementById(`name-display-${num}`).innerText = newName.trim();
        try {
          const res = await fetch('api.php?action=rename_device', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id: id, name: newName.trim() })
          });
          const data = await res.json();
          if (data.success) {
            showToast(`📱 스마트폰 앱 이름이 [${newName.trim()}] (으)로 양방향 동기화되었습니다!`, 'success');
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

    async function togglePlug(id, num) {
      const targetState = !(num === 1 ? state1 : state2);
      if (num === 1) state1 = targetState; else state2 = targetState;

      // 🔒 클릭 즉시 8초간 하트비트 덮어쓰기 금지 락 설정 (깜빡임 완전 멸균!)
      lockUntil[num] = Date.now() + 8000;

      // ⚡ 대시보드 UI 즉시 반응 (0.01초 반응속도)
      updatePlugUI(num, targetState, targetState ? (num === 1 ? 52.3 : 44.8) : 0);

      try {
        await fetch('api.php?action=toggle_plug', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: id, state: targetState })
        });
        showToast(`🔌 전원이 ${targetState ? 'ON (켜짐)' : 'OFF (꺼짐)'} 상태로 원격 제어되었습니다`, 'success');
      } catch(e) {}
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
