<?php
/**
 * IoT Dashboard - MariaDB Table Installation Script
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

    // 2. 커피마실 카페 이지롤 3대 상태 테이블 (iot_dash_blinds)
    $sqlBlinds = "CREATE TABLE IF NOT EXISTS `{$prefix}blinds` (
        `blind_id` INT NOT NULL PRIMARY KEY,
        `blind_name` VARCHAR(50) NOT NULL,
        `serial_no` VARCHAR(64) NOT NULL,
        `local_ip` VARCHAR(45) NOT NULL,
        `public_port` INT NOT NULL,
        `internal_port` INT DEFAULT 48899,
        `position_pct` INT DEFAULT 100,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 3. 센서 텔레메트리 로그 테이블 (iot_dash_telemetry)
    $sqlTelemetry = "CREATE TABLE IF NOT EXISTS `{$prefix}telemetry` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `house_no` INT NOT NULL,
        `temp_c` DECIMAL(4, 1) NOT NULL,
        `humidity_pct` INT NOT NULL,
        `co2_ppm` INT NOT NULL,
        `soil_moisture_pct` INT NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_house_time (`house_no`, `created_at`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    // 4. 시스템 동작 알림 이력 테이블 (iot_dash_logs)
    $sqlLogs = "CREATE TABLE IF NOT EXISTS `{$prefix}logs` (
        `id` BIGINT AUTO_INCREMENT PRIMARY KEY,
        `log_level` VARCHAR(20) DEFAULT 'INFO',
        `message` TEXT NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sqlDevices);
    $pdo->exec($sqlBlinds);
    $pdo->exec($sqlTelemetry);
    $pdo->exec($sqlLogs);

    // 초기 시드 데이터 삽입 (스마트플러그 2종 & 이지롤 블라인드 3대)
    $pdo->exec("INSERT INTO `{$prefix}devices` (`id`, `device_name`, `device_type`, `local_ip`, `mac_address`, `is_active`, `power_watt`) VALUES
        ('ebb219afdebea03ba3shlz', '💡 Smart Plug #1 [책상등]', 'SMART_PLUG', '192.168.100.51', '50:8b:b9:00:5c:f5', 0, 0.00),
        ('42362638a4e57cb3cd0b', '🖨️ Smart Plug #2 [3D 프린터]', 'SMART_PLUG', '192.168.100.63', 'a4:e5:7c:b3:cd:0b', 0, 0.00)
        ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;");

    $pdo->exec("INSERT INTO `{$prefix}blinds` (`blind_id`, `blind_name`, `serial_no`, `local_ip`, `public_port`, `internal_port`, `position_pct`) VALUES
        (1, '1번 블라인드', 'EZS15N1100036', '192.168.100.57', 8891, 48899, 100),
        (2, '2번 블라인드', 'EZS15N1100039', '192.168.100.77', 8892, 48899, 100),
        (3, '3번 블라인드', 'EZS15N1100022', '192.168.100.82', 8893, 48899, 100)
        ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;");

    echo "<h1>✅ MariaDB 테이블 자동 설치 완료!</h1>";
    echo "<p>생성된 테이블 목록 (접두사: <strong>{$prefix}</strong>):</p>";
    echo "<ul>
            <li><code>{$prefix}devices</code> (스마트 플러그 2종)</li>
            <li><code>{$prefix}blinds</code> (커피마실 이지롤 3대)</li>
            <li><code>{$prefix}telemetry</code> (온습도/CO2 시계열)</li>
            <li><code>{$prefix}logs</code> (조작 이력)</li>
          </ul>";
    echo "<p><a href='index.php'>👉 대시보드로 이동하기</a></p>";

} catch (Exception $e) {
    echo "<h1>❌ DB 테이블 설치 오류</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
