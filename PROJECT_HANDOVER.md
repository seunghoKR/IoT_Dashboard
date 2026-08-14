# 🍓 누리오 스마트팜 (Nurio Smart Farm) 통합 IoT 관제 시스템 - 인계 보고서 (PROJECT_HANDOVER.md)

> **최종 갱신 일시**: 2026년 8월 14일 (금)  
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
  - `iot_dash_houses` (비닐하우스/온실 시설동 관리 테이블)
  - `iot_dash_house_devices` (하우스별 차단막/비닐막/양수기/양액기/환풍기 등 농가 스마트 장비)
  - `iot_dash_devices` (스마트 플러그 및 멀티 스위치 기기 정보 및 실시간 상태)
  - `iot_dash_channels` (4채널 스위치 세부 채널 관리)
  - `iot_dash_telemetry` (온습도/CO2/토양수분 시계열 로그)
  - `iot_dash_logs` (시스템 제어 및 양방향 동기화 로그)

---

## 2. 🔌 하드웨어 구성 및 원격 제어 명세

### 1) Tuya Smart Devices (Smart Life 앱 연동 기기 3종)
- **Tuya Cloud OpenAPI**: Data Center Western America (`https://openapi.tuyaus.com`)
  - Client ID: `qsdjvehhx7n8ptuth45v`
  - Client Secret: `f1b450e443494a30950e9ad0095e201f`
- **Smart Plug #1 [책상등]**: ID `ebb219afdebea03ba3shlz`, MAC `50:8b:b9:00:5c:f5`, Local IP `192.168.100.51`
- **Smart Plug #2 [3D 프린터]**: ID `42362638a4e57cb3cd0b`, MAC `a4:e5:7c:b3:cd:0b`, Local IP `192.168.100.63`
- **4CH Smart Switch #3 [4채널 멀티 스위치]**: ID `eb654aa2437462ea40dfjw`, Category `tdq` (4채널 릴레이 `switch_1` ~ `switch_4`)
  - 하드웨어 인터락 지원: 1-2번 상호 잠금(양수기/양액기 안전 제어), 3-4번 상호 잠금(차광막/비닐막 정역회전 안전 제어)

---

## 3. 🌟 주요 완성 기능 및 핵심 기술 포인트

1. **📱 안드로이드 태블릿PC 터치 최적화 UI**:
   - 좌측 메뉴를 고정으로 열어두지 않고 접이식 서랍(Drawer) 오버레이로 배치하여 **화면 100%를 시원하고 직관적인 장치 관제 패널로 극대화**.
   - 크고 명확한 네온 파워 링, 터치 패드, 단계별 개폐기 버튼 제공.
2. **🏗️ 동적 비닐하우스 & 농가 장비 커스텀 허브**:
   - 농가에서 필요한 비닐하우스(동)를 자유롭게 **추가 / 수정 / 삭제**.
   - 하우스마다 **양수기(관수펌프), 양액기(양액공급기), 차단막(차광스크린), 비닐막(개폐기), 환풍기(유동팬), 열풍기, LED 보광등**을 자유롭게 등록 및 제어.
   - 실제 투야 물리 기기(4채널 스위치 및 플러그)의 특정 포트와 소프트웨어 장비를 1:1 바인딩하여 원터치 물리 제어 가능.
3. **⚡ 0.3초 초고속 인터락 양방향 동기화**:
   - 1번이 켜져 있을 때 2번을 켜면, 투야 하드웨어에서 1번을 끄는 즉시 대시보드에서도 0.3초 내에 실시간 반영.
4. **🛡️ 지능형 큐 연쇄 연타 튕김 방지 (AbortController & Pulse Pending UI)**.

---

## 4. 📂 파일 구조 및 자동 배포 방법

- **`web_deploy/` (iwinv 웹 호스팅용 엑기스 패키지)**:
  - `index.php`: 태블릿 반응형 UI, 동적 하우스/장비 허브, 3초 무중단 하트비트.
  - `api.php`: Tuya Cloud OpenAPI 연동, 하우스 및 장치 CRUD REST API.
  - `config.php`: MariaDB DB 연결 및 `iot_dash_` 접두사 설정.
  - `db_install.php`: DB 테이블 자동 설치 및 마이그레이션 스크립트.
  - `.htaccess`: HTTPS/SSL 강제 전환 및 UTF-8 설정.
- **자동 배포 명령어**:
  ```bash
  node ftp-simple-deploy.js
  ```

---

*본 시스템은 Jay @ Connect AI LAB & AI 디자인실장 '영자'에 의해 완벽하게 검증 및 인계 조치되었습니다.* 💖
