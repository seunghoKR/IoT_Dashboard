# 📝 IoT_Dashboard 프로젝트 핸드오버 & 연속 작업 문서 (Handover Guide)

> **다른 컴퓨터나 미래의 AI 작업자가 이 프로젝트를 이어받아 계속 개발하기 위한 완벽 가이드 문서입니다.**

---

## 1. 프로젝트 개요 & 비전
- **프로젝트 명**: 설향 딸기 스마트팜 & 커피마실 카페 통합 관제 대시보드 (IoT B2B SaaS)
- **핵심 목표**: Tuya Cloud 유료 연간 구독료($25,000/년)를 **$0원으로 탈피**하고, Local LAN direct 2ms 제어 및 외부 공인 IP 포트포워딩을 통한 타지역 원격 관제 실현.

---

## 2. 실물 디바이스 상세 사양 및 IP / MAC 정보

### A. 로컬 스마트 플러그 2종 (대표님 댁/농장)
1. **Smart Plug #1 [책상등]**:
   - Device ID: `ebb219afdebea03ba3shlz`
   - Local IP: `192.168.100.51`
   - MAC: `50:8b:b9:00:5c:f5`
   - 전력 수치: ON 시 52.3W
2. **Smart Plug #2 [3D 프린터]**:
   - Device ID: `42362638a4e57cb3cd0b`
   - Local IP: `192.168.100.63`
   - MAC: `a4:e5:7c:b3:cd:0b`
   - 전력 수치: ON 시 44.8W

### B. 커피마실 카페 이지롤 (EASY-ROLL) Wi-Fi 스마트 롤블라인드 3대 (타지역 원격)
- **카페 외부 공인 IP**: `180.227.195.211` (게이트웨이: `180.227.195.129`)
- **1번 블라인드**: `EZS15N1100036` ➔ 로컬 IP `192.168.100.57` (MAC: `AC:67:B2:D2:C6:64`) ➔ 외부 포트 `8891`: 내부 포트 `48899` (TCP/UDP)
- **2번 블라인드**: `EZS15N1100039` ➔ 로컬 IP `192.168.100.77` (MAC: `F0:08:D1:60:11:30`) ➔ 외부 포트 `8892`: 내부 포트 `48899` (TCP/UDP)
- **3번 블라인드**: `EZS15N1100022` ➔ 로컬 IP `192.168.100.82` (MAC: `F0:08:D1:60:15:28`) ➔ 외부 포트 `8893`: 내부 포트 `48899` (TCP/UDP)

---

## 3. 핵심 주요 소스 코드 파일 설명

- `standalone_preview.html`: 메인 관제 웹 대시보드 UI (실물 플러그 2종, 비닐하우스 5동, 4구 스위치, LoraTap 커튼, 12채널 릴레이 보드, 카페 이지롤 3대 패널 통합)
- `tuya-bridge-server.js`: Node.js Fastify/HTTP 백엔드 서버 ($0원 Local Tuya 3초 하트비트 폴링 및 API 제공)
- `test-real-dual-plugs.js`: 스마트 플러그 2종 실물 제어 테스트 검증 스크립트
- `test-remote-easyroll-udp.js`: 카페 외부 공인 IP 48899 TCP/UDP 포트포워딩 소켓 검증 스크립트
- `.env`: Tuya Cloud API 키 및 프로젝트 설정 파일

---

## 4. 다른 컴퓨터에서 작업을 이어받는 순서

1. **깃허브 클론**:
   ```bash
   git clone https://github.com/seunghoKR/IoT_Dashboard.git
   cd IoT_Dashboard
   ```
2. **백엔드 실행**:
   ```bash
   node tuya-bridge-server.js
   ```
3. **대시보드 열기**:
   - `standalone_preview.html` 파일을 브라우저로 실행.

---

*문서 작성일: 2026년 8월 14일 / 작성자: AI 디자인실장 영자*
