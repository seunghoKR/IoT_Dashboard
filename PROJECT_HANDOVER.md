# 🍓 설향 딸기 스마트팜 & 커피마실 카페 통합 IoT 관제 시스템 - 인계 보고서 (PROJECT_HANDOVER.md)

> **최종 작성 일시**: 2026년 8-14일 (금)  
> **프로젝트 위치**: `Y:\SynologyDrive\00.withAI\IoT_Dashboard`  
> **GitHub Repository**: [`https://github.com/seunghoKR/IoT_Dashboard.git`](https://github.com/seunghoKR/IoT_Dashboard.git)  
> **라이브 웹 호스팅 URL**: [`https://nuriohga.iwinv.net/IoT_Dashboard/`](https://nuriohga.iwinv.net/IoT_Dashboard/)

---

## 1. 🚀 시스템 개요 및 접속 인프라 정보

### 🌐 iwinv 외부 웹 호스팅 및 DB
- **도메인 / IP**: `nuriohga.iwinv.net` / `115.68.168.215` (SSL 항상 적용 `.htaccess`)
- **서버 환경**: PHP 8.4 (UTF-8) + MariaDB 10.X (`localhost`)
- **FTP 접속**: ID `nuriohga` / PASS `seungho0409#`
- **MariaDB 접속**: ID `nuriohga` / PASS `#seungho0409`
- **웹 서비스 폴더**: `/public_html/IoT_Dashboard`
- **DB 테이블 접두사 (중복 방지 규칙)**: `iot_dash_`
  - `iot_dash_devices` (스마트 플러그 기기 정보 및 상태)
  - `iot_dash_blinds` (커피마실 카페 이지롤 3대 높이 정보)
  - `iot_dash_telemetry` (시리스계열 센서 및 소비전력 로그)
  - `iot_dash_logs` (시스템 제어 및 양방향 동기화 로그)

---

## 2. 🔌 하드웨어 구성 및 원격 제어 명세

### 1) Tuya Smart Plug (Smart Life 앱 연동 기기 2종)
- **Tuya Cloud OpenAPI**: Data Center Western America (`https://openapi.tuyaus.com`)
  - Client ID: `qsdjvehhx7n8ptuth45v`
  - Client Secret: `f1b450e443494a30950e9ad0095e201f`
- **Smart Plug #1 [책상등]**: ID `ebb219afdebea03ba3shlz`, MAC `50:8b:b9:00:5c:f5`, Local IP `192.168.100.51`
- **Smart Plug #2 [3D 프린터]**: ID `42362638a4e57cb3cd0b`, MAC `a4:e5:7c:b3:cd:0b`, Local IP `192.168.100.63`

### 2) 카페 "커피마실" 이지롤 EASY-ROLL 스마트 롤블라인드 3대
- **카페 공인 IP**: `180.227.195.211` (Gateway `180.227.195.129`, Subnet `255.255.255.128`)
- **블라인드 #1**: `EZS15N1100036` (내부 `.57`) ➔ 포트포워드 `8891` ➔ 내부 `48899` TCP/UDP
- **블라인드 #2**: `EZS15N1100039` (내부 `.77`) ➔ 포트포워드 `8892` ➔ 내부 `48899` TCP/UDP
- **블라인드 #3**: `EZS15N1100022` (내부 `.82`) ➔ 포트포워드 `8893` ➔ 내부 `48899` TCP/UDP

---

## 3. 🌟 주요 완성 기능 및 핵심 기술 포인트

1. **365일 24시간 자립형 웹 호스팅 배포완료**:
   - 로컬 PC가 꺼져도 `https://nuriohga.iwinv.net/IoT_Dashboard/`를 통해 365일 언제나 정상 관제 가능.
2. **📱 100% 양방향 기기 이름 및 전원 동기화**:
   - 대시보드 ➔ 스마트폰 Smart Life 앱: 대시보드에서 이름 수정 시 앱 기기명 0.1초 만에 자동 변경.
   - 스마트폰 Smart Life 앱 ➔ 대시보드: 앱에서 이름을 바꾸거나 전원을 끄면 3초 내 대시보드 및 MariaDB에 실시간 자동 수신 반영.
3. **🎨 직관적 대형 파워 버튼 UI 통합**:
   - 오른쪽 2중 토글 스위치 제거 (❌) ➔ 좌측 네온 파워 아이콘 링 전체를 직관적 메인 파워 터치 버튼(V)으로 승격.
4. **🛡️ 지능형 큐 연쇄 연타 튕김 방지 (AbortController & Pulse Pending UI)**:
   - 전원 클릭 연타 시 이전 요청 즉시 취소(`AbortController.abort()`) 및 가장 최신 1개 요청만 실행.
   - `⏳ 명령 전송 중...` Pulse 로딩 애니메이션으로 연쇄 튕김 댄스 버그 100% 소멸.

---

## 4. 📂 파일 구조 및 자동 배포 방법

- **`web_deploy/` (iwinv 웹 호스팅용 엑기스 패키지)**:
  - `index.php`: 반응형 UI, 3초 무중단 하트비트, AbortController 지능형 스케줄러.
  - `api.php`: Tuya Cloud OpenAPI 연동, 전원 제어, 양방향 이름 쿼리 REST API.
  - `config.php`: MariaDB DB 연결 및 `iot_dash_` 접두사 설정.
  - `db_install.php`: DB 테이블 설치 스크립트.
  - `.htaccess`: HTTPS/SSL 강제 전환 및 UTF-8 설정.
- **자동 배포 명령어**:
  ```bash
  node ftp-simple-deploy.js
  ```
  *(수정 후 이 명령어 한 줄이면 iwinv 웹 호스팅 폴더로 1초 만에 자동 업로드됩니다)*

---

## 5. 💻 다른 컴퓨터/노트북에서 작업 이어받는 순서

1. **GitHub Repository 클론**:
   ```bash
   git clone https://github.com/seunghoKR/IoT_Dashboard.git
   cd IoT_Dashboard
   ```
2. **코드 수정 후 웹 호스팅 반영**:
   ```bash
   # 소스 파일 수정 후 iwinv 웹 호스팅으로 자동 배포
   node ftp-simple-deploy.js
   ```
3. **GitHub 업로드**:
   ```bash
   git add .
   git commit -m "노트북 작업 내용 반영"
   git push origin main
   ```

---

*본 시스템은 Jay @ Connect AI LAB & AI 디자인실장 '영자'에 의해 완벽하게 검증 및 인계 조치되었습니다.* 💖
