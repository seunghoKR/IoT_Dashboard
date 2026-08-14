<?php
/**
 * IoT Dashboard REST API (PHP 8.4 + MariaDB)
 */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/config.php';
$pdo = getDbConnection();
$prefix = DB_PREFIX;

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_status';

try {
    if ($action === 'get_status') {
        // 1. 스마트플러그 디바이스 가져오기
        $stmtDev = $pdo->query("SELECT * FROM `{$prefix}devices`");
        $devices = [];
        while ($row = $stmtDev->fetch()) {
            $devices[$row['id']] = [
                'name' => $row['device_name'],
                'type' => $row['device_type'],
                'ip' => $row['local_ip'],
                'mac' => $row['mac_address'],
                'state' => (bool)$row['is_active'],
                'power' => (float)$row['power_watt']
            ];
        }

        // 2. 이지롤 블라인드 3대 가져오기
        $stmtBlind = $pdo->query("SELECT * FROM `{$prefix}blinds` ORDER BY `blind_id` ASC");
        $blinds = [];
        while ($row = $stmtBlind->fetch()) {
            $blinds[$row['blind_id']] = [
                'name' => $row['blind_name'],
                'serial' => $row['serial_no'],
                'localIp' => $row['local_ip'],
                'publicPort' => (int)$row['public_port'],
                'internalPort' => (int)$row['internal_port'],
                'position' => (int)$row['position_pct']
            ];
        }

        echo json_encode([
            'success' => true,
            'devices' => $devices,
            'blinds' => $blinds,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
        exit;
    }

    if ($action === 'toggle_plug') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? $_POST['id'] ?? '';
        $state = isset($input['state']) ? (bool)$input['state'] : null;

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Device ID required']);
            exit;
        }

        // 현재 상태 반전
        if ($state === null) {
            $stmt = $pdo->prepare("SELECT `is_active` FROM `{$prefix}devices` WHERE `id` = ?");
            $stmt->execute([$id]);
            $curr = $stmt->fetchColumn();
            $state = !((bool)$curr);
        }

        $powerWatts = $state ? ($id === 'ebb219afdebea03ba3shlz' ? 52.30 : 44.80) : 0.00;

        $stmtUp = $pdo->prepare("UPDATE `{$prefix}devices` SET `is_active` = ?, `power_watt` = ? WHERE `id` = ?");
        $stmtUp->execute([$state ? 1 : 0, $powerWatts, $id]);

        // 로그 기록
        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $stmtLog->execute(["스마트플러그 [{$id}] 전원 상태 변경 -> " . ($state ? 'ON' : 'OFF')]);

        echo json_encode([
            'success' => true,
            'deviceId' => $id,
            'targetState' => $state,
            'powerWatt' => $powerWatts
        ]);
        exit;
    }

    if ($action === 'move_blind') {
        $input = json_decode(file_get_contents('php://input'), true);
        $blindId = (int)($input['blind_id'] ?? $_POST['blind_id'] ?? 0);
        $position = (int)($input['position'] ?? $_POST['position'] ?? 0);

        if ($blindId > 0) {
            $stmtUp = $pdo->prepare("UPDATE `{$prefix}blinds` SET `position_pct` = ? WHERE `blind_id` = ?");
            $stmtUp->execute([$position, $blindId]);
        } else {
            // 전체 3대 일괄 변경
            $stmtUp = $pdo->prepare("UPDATE `{$prefix}blinds` SET `position_pct` = ?");
            $stmtUp->execute([$position]);
        }

        echo json_encode([
            'success' => true,
            'blindId' => $blindId,
            'position' => $position
        ]);
        exit;
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
