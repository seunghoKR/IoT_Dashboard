<?php
/**
 * IoT Dashboard REST API (PHP 8.4 + MariaDB + Tuya Cloud OpenAPI 직통 제어)
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

// Tuya Cloud Credentials
define('TUYA_CLIENT_ID', 'qsdjvehhx7n8ptuth45v');
define('TUYA_SECRET', 'f1b450e443494a30950e9ad0095e201f');
define('TUYA_ENDPOINT', 'https://openapi.tuyaus.com');

function getTuyaSign($accessId, $secret, $t, $accessToken = '', $httpMethod = 'GET', $url = '', $bodyStr = '') {
    $contentHash = hash('sha256', $bodyStr);
    $stringToSign = implode("\n", [$httpMethod, $contentHash, "", $url]);
    $signStr = $accessId . $accessToken . $t . $stringToSign;
    return strtoupper(hash_hmac('sha256', $signStr, $secret));
}

function getTuyaAccessToken() {
    static $token = null;
    if ($token !== null) return $token;

    $t = (string)round(microtime(true) * 1000);
    $url = '/v1.0/token?grant_type=1';
    $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, '', 'GET', $url);

    $ch = curl_init(TUYA_ENDPOINT . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'client_id: ' . TUYA_CLIENT_ID,
        'sign: ' . $sign,
        't: ' . $t,
        'sign_method: HMAC-SHA256'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    if (isset($data['success']) && $data['success'] && isset($data['result']['access_token'])) {
        $token = $data['result']['access_token'];
        return $token;
    }
    return null;
}

function sendTuyaCommand($deviceId, $state) {
    $token = getTuyaAccessToken();
    if (!$token) return false;

    $t = (string)round(microtime(true) * 1000);
    $url = "/v1.0/devices/{$deviceId}/commands";
    $bodyObj = ['commands' => [['code' => 'switch_1', 'value' => (bool)$state]]];
    $bodyStr = json_encode($bodyObj);
    $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, $token, 'POST', $url, $bodyStr);

    $ch = curl_init(TUYA_ENDPOINT . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyStr);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'client_id: ' . TUYA_CLIENT_ID,
        'access_token: ' . $token,
        'sign: ' . $sign,
        't: ' . $t,
        'sign_method: HMAC-SHA256',
        'Content-Type: application/json'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    return isset($data['success']) && $data['success'];
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_status';

try {
    if ($action === 'get_status') {
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
        $id = $input['id'] ?? $_GET['id'] ?? $_POST['id'] ?? '';
        $state = isset($input['state']) ? (bool)$input['state'] : (isset($_GET['state']) ? (bool)$_GET['state'] : null);

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Device ID required']);
            exit;
        }

        if ($state === null) {
            $stmt = $pdo->prepare("SELECT `is_active` FROM `{$prefix}devices` WHERE `id` = ?");
            $stmt->execute([$id]);
            $curr = $stmt->fetchColumn();
            $state = !((bool)$curr);
        }

        // 1. 외부 호스팅 -> 실제 투야 클라우드를 통해 집/농장의 디바이스로 0.3초 만에 명령 전송!
        $tuyaSuccess = sendTuyaCommand($id, $state);

        // 2. MariaDB 상태 저장
        $powerWatts = $state ? ($id === 'ebb219afdebea03ba3shlz' ? 52.30 : 44.80) : 0.00;
        $stmtUp = $pdo->prepare("UPDATE `{$prefix}devices` SET `is_active` = ?, `power_watt` = ? WHERE `id` = ?");
        $stmtUp->execute([$state ? 1 : 0, $powerWatts, $id]);

        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $stmtLog->execute(["[iwinv 웹호스팅] 스마트플러그 [{$id}] 전원 변경 -> " . ($state ? 'ON' : 'OFF') . " (Tuya전송: " . ($tuyaSuccess ? '성공' : '실패') . ")"]);

        echo json_encode([
            'success' => true,
            'deviceId' => $id,
            'targetState' => $state,
            'powerWatt' => $powerWatts,
            'tuyaDispatched' => $tuyaSuccess
        ]);
        exit;
    }

    if ($action === 'move_blind') {
        $input = json_decode(file_get_contents('php://input'), true);
        $blindId = (int)($input['blind_id'] ?? $_GET['blind_id'] ?? $_POST['blind_id'] ?? 0);
        $position = (int)($input['position'] ?? $_GET['position'] ?? $_POST['position'] ?? 0);

        if ($blindId > 0) {
            $stmtUp = $pdo->prepare("UPDATE `{$prefix}blinds` SET `position_pct` = ? WHERE `blind_id` = ?");
            $stmtUp->execute([$position, $blindId]);
        } else {
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
