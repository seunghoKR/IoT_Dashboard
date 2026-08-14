<?php
/**
 * 누리오 스마트팜 (Nurio Smart Farm) - MariaDB Table Installation Script
 * iwinv Hosting DB Prefix: iot_dash_
 */
header('Content-Type: text/html; charset=utf-8');
require_once __DIR__ . '/config.php';

try {
    $pdo = getDbConnection();
    $prefix = DB_PREFIX;

    // 1. 디바이스 마스터 및 실시간 상태 테이블 (iot_dash_devices)
    $sqlDevices = "CREATE TABLE IF NOT EXISTS `{$prefix}devices` (
        `id` VARCHAR(64) NOT NULL PRIMARY KEY,
        `device_name` VARCHAR(100) NOT NULL,
        `device_type` VARCHAR(50) NOT NULL,
        `local_ip` VARCHAR(45) NULL,
        `mac_address` VARCHAR(30) NULL,
        `is_active` TINYINT(1) DEFAULT 0,
        `power_watt` DECIMAL(8, 2) DEFAULT 0.00,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 2. 멀티채널 기기 채널 관리 테이블 (iot_dash_channels)
    $sqlChannels = "CREATE TABLE IF NOT EXISTS `{$prefix}channels` (
        `device_id` VARCHAR(64) NOT NULL,
        `channel_no` INT NOT NULL,
        `channel_code` VARCHAR(30) NOT NULL,
        `channel_name` VARCHAR(100) NOT NULL,
        `is_active` TINYINT(1) DEFAULT 0,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`device_id`, `channel_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 3. 비닐하우스 관리 테이블 (iot_dash_houses)
    $sqlHouses = "CREATE TABLE IF NOT EXISTS `{$prefix}houses` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `house_name` VARCHAR(100) NOT NULL,
        `crop_type` VARCHAR(50) DEFAULT '딸기 (설향)',
        `memo` VARCHAR(255) NULL,
        `sort_order` INT DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 4. 하우스별 스마트 농가 장치 관리 테이블 (iot_dash_house_devices)
    $sqlHouseDevices = "CREATE TABLE IF NOT EXISTS `{$prefix}house_devices` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `house_id` INT NOT NULL,
        `device_category` VARCHAR(50) NOT NULL, -- CURTAIN, VINYL, WATER_PUMP, NUTRIENT_FEEDER, VENT_FAN, HEATER, GROW_LIGHT
        `device_name` VARCHAR(100) NOT NULL,
        `bound_device_id` VARCHAR(64) NULL, -- 투야 실물 기기 ID (예: eb654aa2437462ea40dfjw)
        `bound_channel_no` INT DEFAULT 1, -- 투야 채널 번호 1~4
        `is_active` TINYINT(1) DEFAULT 0,
        `position_pct` INT DEFAULT 0, -- 차광막/비닐막 개폐율 (0% 열림 ~ 100% 닫힘)
        `specs` VARCHAR(255) NULL, -- 규격/용량 메모
        `sort_order` INT DEFAULT 1,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_house (`house_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 5. 센서 텔레메트리 로그 테이블 (iot_dash_telemetry)
    $sqlTelemetry = "CREATE TABLE IF NOT EXISTS `{$prefix}telemetry` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `house_id` INT NOT NULL,
        `temp_c` DECIMAL(4, 1) NOT NULL,
        `humidity_pct` INT NOT NULL,
        `co2_ppm` INT NOT NULL,
        `soil_moisture_pct` INT NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_house_time (`house_id`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 6. 시스템 동작 알림 이력 테이블 (iot_dash_logs)
    $sqlLogs = "CREATE TABLE IF NOT EXISTS `{$prefix}logs` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `log_level` VARCHAR(20) DEFAULT 'INFO',
    // 7. 실시간 커튼/비닐막/차광막 개폐율 및 모터 상태 동기화 테이블 (iot_dash_curtains)
    $sqlCurtains = "CREATE TABLE IF NOT EXISTS `{$prefix}curtains` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `house_id` INT NOT NULL DEFAULT 1,
        `motor_no` INT NOT NULL DEFAULT 1,
        `curtain_name` VARCHAR(100) DEFAULT '측창 비닐막',
        `position_pct` FLOAT NOT NULL DEFAULT 0.0,
        `direction` INT NOT NULL DEFAULT 0, -- -1: 닫힘중, 0: 정지, 1: 열림중
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY `uk_house_motor` (`house_id`, `motor_no`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlDevices);
    $pdo->exec($sqlChannels);
    $pdo->exec($sqlHouses);
    $pdo->exec($sqlHouseDevices);
    $pdo->exec($sqlTelemetry);
    $pdo->exec($sqlLogs);
    $pdo->exec($sqlCurtains);

    $pdo->exec("INSERT INTO `{$prefix}curtains` (`house_id`, `motor_no`, `curtain_name`, `position_pct`, `direction`) VALUES
        (1, 1, '1호 모터 (측창 비닐막)', 0.0, 0),
        (1, 2, '2호 모터 (상부 차광막)', 0.0, 0)
        ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;");

    // 실제 설치된 투야 하드웨어 시드 (스마트플러그 2종 + 4채널 멀티 스위치)
    $pdo->exec("INSERT INTO `{$prefix}devices` (`id`, `device_name`, `device_type`, `local_ip`, `mac_address`, `is_active`, `power_watt`) VALUES
        ('ebb219afdebea03ba3shlz', '책상등', 'SMART_PLUG', '192.168.100.51', '50:8b:b9:00:5c:f5', 0, 0.00),
        ('42362638a4e57cb3cd0b', '3D프린터', 'SMART_PLUG', '192.168.100.63', 'a4:e5:7c:b3:cd:0b', 0, 0.00),
        ('eb654aa2437462ea40dfjw', '4채널 멀티 스위치', '4CH_SWITCH', '49.171.41.10', '4c:d7:b7:b0:ea:16', 0, 0.00)
        ON DUPLICATE KEY UPDATE `device_name` = VALUES(`device_name`), `updated_at` = CURRENT_TIMESTAMP;");

    $pdo->exec("INSERT INTO `{$prefix}channels` (`device_id`, `channel_no`, `channel_code`, `channel_name`, `is_active`) VALUES
        ('eb654aa2437462ea40dfjw', 1, 'switch_1', '1번 채널 (1동 양수기/관수펌프)', 0),
        ('eb654aa2437462ea40dfjw', 2, 'switch_2', '2번 채널 (1동 양액기/양액공급)', 0),
        ('eb654aa2437462ea40dfjw', 3, 'switch_3', '3번 채널 (1동 차광막/환풍팬)', 0),
        ('eb654aa2437462ea40dfjw', 4, 'switch_4', '4번 채널 (1동 비닐개폐/보광등)', 0)
        ON DUPLICATE KEY UPDATE `channel_name` = VALUES(`channel_name`), `updated_at` = CURRENT_TIMESTAMP;");

    // 기본 하우스(1동) 및 필수 장치 초기 설정 (대표님이 자유롭게 편집/추가/삭제 가능)
    $pdo->exec("DELETE FROM `{$prefix}house_devices`");
    $pdo->exec("DELETE FROM `{$prefix}houses`");

    $pdo->exec("INSERT INTO `{$prefix}houses` (`id`, `house_name`, `crop_type`, `memo`, `sort_order`) VALUES
        (1, '🍓 1동 설향 딸기 재배하우스', '딸기 (설향)', '수경재배 베드 A라인 및 스마트 관수/양액 구역', 1),
        (2, '🌱 2동 육묘 및 보조 온실', '딸기 모종', '육묘 온습도 관리 및 환풍 제어 구역', 2);");

    $pdo->exec("INSERT INTO `{$prefix}house_devices` (`house_id`, `device_category`, `device_name`, `bound_device_id`, `bound_channel_no`, `is_active`, `position_pct`, `specs`) VALUES
        (1, 'WATER_PUMP', '💧 1동 주양수기 (관수펌프)', 'eb654aa2437462ea40dfjw', 1, 0, 0, '2.0 HP 고압 다단 펌프'),
        (1, 'NUTRIENT_FEEDER', '🧪 1동 정밀 양액기 (양액공급기)', 'eb654aa2437462ea40dfjw', 2, 0, 0, 'EC/pH 자동 비례 제어기'),
        (1, 'CURTAIN', '☀️ 1동 내부 차광막 (알루미늄 스크린)', 'eb654aa2437462ea40dfjw', 3, 0, 100, '모터 감속기 100W'),
        (1, 'VINYL', '🏠 1동 측창 비닐막 개폐기', 'eb654aa2437462ea40dfjw', 4, 0, 0, '24V DC 롤업 모터'),
        (2, 'VENT_FAN', '💨 2동 대형 환풍 유동팬', NULL, 1, 0, 0, '500mm 고효율 환기팬'),
        (2, 'WATER_PUMP', '💧 2동 보조 관수 밸브', NULL, 1, 0, 0, '솔레노이드 전동 밸브');");

    echo "<h1>✅ 누리오 스마트팜 MariaDB 테이블 설치 & 업데이트 완료!</h1>";
    echo "<p>생성된 테이블 목록 (접두사: <strong>{$prefix}</strong>):</p>";
    echo "<ul>
            <li><code>{$prefix}houses</code> (비닐하우스/온실 시설동 관리)</li>
            <li><code>{$prefix}house_devices</code> (하우스별 차단막/비닐막/양수기/양액기/환풍기 등)</li>
            <li><code>{$prefix}devices</code> (실물 투야 스마트 플러그 & 4채널 스위치)</li>
            <li><code>{$prefix}channels</code> (4채널 스위치 세부 채널 관리)</li>
            <li><code>{$prefix}telemetry</code> (센서 데이터 로그)</li>
            <li><code>{$prefix}logs</code> (조작 이력)</li>
          </ul>";
    echo "<p><a href='index.php'>👉 누리오 스마트팜 대시보드로 이동하기</a></p>";

} catch (Exception $e) {
    echo "<h1>❌ DB 테이블 설치 오류</h1>";
}
?>
