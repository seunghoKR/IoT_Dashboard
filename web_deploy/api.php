<?php
/**
 * IoT Dashboard REST API (PHP 8.4 + MariaDB + 명령 큐 연쇄 튕김 방지 지능형 제어)
 */
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json; charset=utf-8');

if (function_exists('opcache_reset')) {
    @opcache_reset();
}

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
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
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

function fetchTuyaRealDeviceInfo($deviceId) {
    $token = getTuyaAccessToken();
    if (!$token) return null;

    $t = (string)round(microtime(true) * 1000);
    $url = "/v1.0/devices/{$deviceId}";
    $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, $token, 'GET', $url);

    $ch = curl_init(TUYA_ENDPOINT . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'client_id: ' . TUYA_CLIENT_ID,
        'access_token: ' . $token,
        'sign: ' . $sign,
        't: ' . $t,
        'sign_method: HMAC-SHA256'
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($res, true);
    if (isset($data['success']) && $data['success'] && is_array($data['result'])) {
        $resObj = $data['result'];
        $name = $resObj['name'] ?? null;
        $channels = [];
        $state = false;
        if (isset($resObj['status']) && is_array($resObj['status'])) {
            foreach ($resObj['status'] as $item) {
                if ($item['code'] === 'switch_1' || $item['code'] === 'switch') {
                    $state = (bool)$item['value'];
                    $channels[1] = (bool)$item['value'];
                } elseif (preg_match('/^switch_(\d+)$/', $item['code'], $m)) {
                    $cNum = (int)$m[1];
                    $channels[$cNum] = (bool)$item['value'];
                    if ((bool)$item['value']) $state = true;
                }
            }
        }
        return ['name' => $name, 'state' => $state, 'channels' => $channels];
    }
    return null;
}

function sendTuyaCommand($deviceId, $state, $channel = 1) {
    $token = getTuyaAccessToken();
    if (!$token) return false;

    $t = (string)round(microtime(true) * 1000);
    $url = "/v1.0/devices/{$deviceId}/commands";

    if ($channel === 'all' || $channel === 0) {
        $commands = [
            ['code' => 'switch_1', 'value' => (bool)$state],
            ['code' => 'switch_2', 'value' => (bool)$state],
            ['code' => 'switch_3', 'value' => (bool)$state],
            ['code' => 'switch_4', 'value' => (bool)$state]
        ];
    } else {
        $code = ($channel > 1) ? "switch_{$channel}" : 'switch_1';
        $commands = [['code' => $code, 'value' => (bool)$state]];
    }

    $bodyObj = ['commands' => $commands];
    $bodyStr = json_encode($bodyObj);
    $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, $token, 'POST', $url, $bodyStr);

    $ch = curl_init(TUYA_ENDPOINT . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyStr);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
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

function renameTuyaDevice($deviceId, $newName) {
    $token = getTuyaAccessToken();
    if (!$token) return false;

    $t = (string)round(microtime(true) * 1000);
    $url = "/v1.0/devices/{$deviceId}";
    $bodyObj = ['name' => $newName];
    $bodyStr = json_encode($bodyObj);
    $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, $token, 'PUT', $url, $bodyStr);

    $ch = curl_init(TUYA_ENDPOINT . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyStr);
    curl_setopt($ch, CURLOPT_TIMEOUT, 3);
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

function fetchTuyaMultipleDevicesParallel($deviceIds) {
    $token = getTuyaAccessToken();
    if (!$token || empty($deviceIds)) return [];

    $mh = curl_multi_init();
    $curlHandles = [];

    foreach ($deviceIds as $id) {
        $t = (string)round(microtime(true) * 1000);
        $url = "/v1.0/devices/{$id}";
        $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, $token, 'GET', $url);

        $ch = curl_init(TUYA_ENDPOINT . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'client_id: ' . TUYA_CLIENT_ID,
            'access_token: ' . $token,
            'sign: ' . $sign,
            't: ' . $t,
            'sign_method: HMAC-SHA256'
        ]);
        curl_multi_add_handle($mh, $ch);
        $curlHandles[$id] = $ch;
    }

    $running = null;
    do {
        $status = curl_multi_exec($mh, $running);
        if ($running > 0) {
            curl_multi_select($mh, 0.2);
        }
    } while ($running > 0 && $status === CURLM_OK);

    $results = [];
    foreach ($curlHandles as $id => $ch) {
        $content = curl_multi_getcontent($ch);
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);

        $data = json_decode($content, true);
        if (isset($data['success']) && $data['success'] && is_array($data['result'])) {
            $resObj = $data['result'];
            $name = $resObj['name'] ?? null;
            $channels = [];
            $state = false;
            if (isset($resObj['status']) && is_array($resObj['status'])) {
                foreach ($resObj['status'] as $item) {
                    if ($item['code'] === 'switch_1' || $item['code'] === 'switch') {
                        $state = (bool)$item['value'];
                        $channels[1] = (bool)$item['value'];
                    } elseif (preg_match('/^switch_(\d+)$/', $item['code'], $m)) {
                        $cNum = (int)$m[1];
                        $channels[$cNum] = (bool)$item['value'];
                        if ((bool)$item['value']) $state = true;
                    }
                }
            }
            $results[$id] = ['name' => $name, 'state' => $state, 'channels' => $channels];
        }
    }
    curl_multi_close($mh);
    return $results;
}

function syncTuyaDevicesToDb($tuyaLiveMap, $pdo, $prefix) {
    foreach ($tuyaLiveMap as $deviceId => $info) {
        $name = $info['name'];
        $state = $info['state'];
        $channels = $info['channels'];

        if (!empty($channels)) {
            $activeCount = 0;
            foreach ($channels as $cNo => $cState) {
                $stmtUp = $pdo->prepare("UPDATE `{$prefix}channels` SET `is_active` = ? WHERE `device_id` = ? AND `channel_no` = ?");
                $stmtUp->execute([$cState ? 1 : 0, $deviceId, $cNo]);
                if ($cState) $activeCount++;
            }
            $powerWatts = $activeCount * 15.60;
            $devActive = ($activeCount > 0);
            $stmtUpDev = $pdo->prepare("UPDATE `{$prefix}devices` SET `is_active` = ?, `power_watt` = ? WHERE `id` = ?");
            $stmtUpDev->execute([$devActive ? 1 : 0, $powerWatts, $deviceId]);
        } else {
            $powerWatts = $state ? ($deviceId === 'ebb219afdebea03ba3shlz' ? 52.30 : 44.80) : 0.00;
            $stmtUpDev = $pdo->prepare("UPDATE `{$prefix}devices` SET `is_active` = ?, `power_watt` = ? WHERE `id` = ?");
            $stmtUpDev->execute([$state ? 1 : 0, $powerWatts, $deviceId]);
        }
    }
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'get_status';

try {
    if ($action === 'get_status') {
        $stmtDev = $pdo->query("SELECT * FROM `{$prefix}devices`");
        $devices = [];
        $tuyaDeviceIds = [];

        while ($row = $stmtDev->fetch()) {
            $id = $row['id'];
            $tuyaDeviceIds[] = $id;
            $dbName = $row['device_name'];
            $dbState = (bool)$row['is_active'];

            $devData = [
                'name' => $dbName,
                'type' => $row['device_type'],
                'ip' => $row['local_ip'],
                'mac' => $row['mac_address'],
                'state' => $dbState,
                'power' => $dbState ? ($id === 'ebb219afdebea03ba3shlz' ? 52.30 : ($id === '42362638a4e57cb3cd0b' ? 44.80 : 15.60)) : 0.00,
                'channels' => []
            ];

            $devices[$id] = $devData;
        }

        // 실시간 Tuya 클라우드 상태 병렬 동기화
        if (!empty($tuyaDeviceIds)) {
            $liveTuya = fetchTuyaMultipleDevicesParallel($tuyaDeviceIds);
            if (!empty($liveTuya)) {
                syncTuyaDevicesToDb($liveTuya, $pdo, $prefix);
                foreach ($liveTuya as $id => $info) {
                    if (isset($devices[$id])) {
                        $devices[$id]['state'] = $info['state'];
                    }
                }
            }
        }

        // 채널 정보 로드
        try {
            $stmtCh = $pdo->query("SELECT * FROM `{$prefix}channels` ORDER BY `device_id`, `channel_no` ASC");
            while ($cRow = $stmtCh->fetch()) {
                $devId = $cRow['device_id'];
                $cNo = (int)$cRow['channel_no'];
                if (isset($devices[$devId])) {
                    $devices[$devId]['channels'][$cNo] = [
                        'no' => $cNo,
                        'code' => $cRow['channel_code'],
                        'name' => $cRow['channel_name'],
                        'state' => (bool)$cRow['is_active']
                    ];
                }
            }
        } catch (Exception $e) {
            // 채널 테이블 무시
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
        $channel = $input['channel'] ?? $_GET['channel'] ?? $_POST['channel'] ?? null;
        $state = isset($input['state']) ? (bool)$input['state'] : (isset($_GET['state']) ? (bool)$_GET['state'] : null);

        if (!$id) {
            echo json_encode(['success' => false, 'error' => 'Device ID required']);
            exit;
        }

        if ($channel !== null && $channel !== '' && $channel !== 'single') {
            // 멀티채널 제어 (단일 채널 또는 전체)
            if ($channel === 'all' || $channel == 0) {
                if ($state === null) {
                    $stmt = $pdo->prepare("SELECT COUNT(*) FROM `{$prefix}channels` WHERE `device_id` = ? AND `is_active` = 1");
                    $stmt->execute([$id]);
                    $activeCount = (int)$stmt->fetchColumn();
                    $state = ($activeCount === 0);
                }

                $tuyaSuccess = sendTuyaCommand($id, $state, 'all');

                // 하드웨어 및 클라우드 상태 안정화 대기 후 실시간 상태 수신
                usleep(300000);
                $liveTuya = fetchTuyaMultipleDevicesParallel([$id]);
                if (!empty($liveTuya)) {
                    syncTuyaDevicesToDb($liveTuya, $pdo, $prefix);
                }

                $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
                $stmtLog->execute(["[iwinv 호스팅] 4채널 스위치 [{$id}] 전체 채널 전원 변경 -> " . ($state ? 'ON' : 'OFF')]);

                // 최신 채널 상태 취득
                $stmtCh = $pdo->prepare("SELECT * FROM `{$prefix}channels` WHERE `device_id` = ? ORDER BY `channel_no` ASC");
                $stmtCh->execute([$id]);
                $updatedChannels = [];
                while ($c = $stmtCh->fetch()) {
                    $updatedChannels[(int)$c['channel_no']] = [
                        'no' => (int)$c['channel_no'],
                        'code' => $c['channel_code'],
                        'name' => $c['channel_name'],
                        'state' => (bool)$c['is_active']
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'deviceId' => $id,
                    'channel' => 'all',
                    'targetState' => $state,
                    'channels' => $updatedChannels,
                    'tuyaDispatched' => $tuyaSuccess
                ]);
                exit;
            } else {
                $cNo = (int)$channel;
                if ($state === null) {
                    $stmt = $pdo->prepare("SELECT `is_active` FROM `{$prefix}channels` WHERE `device_id` = ? AND `channel_no` = ?");
                    $stmt->execute([$id, $cNo]);
                    $curr = $stmt->fetchColumn();
                    $state = !((bool)$curr);
                }

                $tuyaSuccess = sendTuyaCommand($id, $state, $cNo);

                // 인터락 및 기기 상태 동기화 (Tuya 클라우드에서 모든 채널 실시간 상태 즉시 조회)
                usleep(300000);
                $liveTuya = fetchTuyaMultipleDevicesParallel([$id]);
                if (!empty($liveTuya)) {
                    syncTuyaDevicesToDb($liveTuya, $pdo, $prefix);
                }

                $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
                $stmtLog->execute(["[iwinv 호스팅] 4채널 스위치 [{$id}] {$cNo}번 채널 전원 변경 -> " . ($state ? 'ON' : 'OFF')]);

                // 최신 채널 상태 취득 (인터락으로 꺼진 다른 채널 포함)
                $stmtCh = $pdo->prepare("SELECT * FROM `{$prefix}channels` WHERE `device_id` = ? ORDER BY `channel_no` ASC");
                $stmtCh->execute([$id]);
                $updatedChannels = [];
                while ($c = $stmtCh->fetch()) {
                    $updatedChannels[(int)$c['channel_no']] = [
                        'no' => (int)$c['channel_no'],
                        'code' => $c['channel_code'],
                        'name' => $c['channel_name'],
                        'state' => (bool)$c['is_active']
                    ];
                }

                echo json_encode([
                    'success' => true,
                    'deviceId' => $id,
                    'channel' => $cNo,
                    'targetState' => $state,
                    'channels' => $updatedChannels,
                    'tuyaDispatched' => $tuyaSuccess
                ]);
                exit;
            }
        } else {
            // 일반 1구 플러그 제어
            if ($state === null) {
                $stmt = $pdo->prepare("SELECT `is_active` FROM `{$prefix}devices` WHERE `id` = ?");
                $stmt->execute([$id]);
                $curr = $stmt->fetchColumn();
                $state = !((bool)$curr);
            }

            $powerWatts = $state ? ($id === 'ebb219afdebea03ba3shlz' ? 52.30 : 44.80) : 0.00;
            $stmtUp = $pdo->prepare("UPDATE `{$prefix}devices` SET `is_active` = ?, `power_watt` = ? WHERE `id` = ?");
            $stmtUp->execute([$state ? 1 : 0, $powerWatts, $id]);

            $tuyaSuccess = sendTuyaCommand($id, $state, 1);

            $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
            $stmtLog->execute(["[iwinv 웹호스팅] 스마트플러그 [{$id}] 전원 변경 -> " . ($state ? 'ON' : 'OFF')]);

            echo json_encode([
                'success' => true,
                'deviceId' => $id,
                'targetState' => $state,
                'powerWatt' => $powerWatts,
                'tuyaDispatched' => $tuyaSuccess
            ]);
            exit;
        }
    }

    if ($action === 'rename_device') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? $_GET['id'] ?? $_POST['id'] ?? '';
        $newName = trim($input['name'] ?? $_GET['name'] ?? $_POST['name'] ?? '');

        if (!$id || !$newName) {
            echo json_encode(['success' => false, 'error' => 'Device ID & New Name required']);
            exit;
        }

        $appRenamed = renameTuyaDevice($id, $newName);

        $stmtUp = $pdo->prepare("UPDATE `{$prefix}devices` SET `device_name` = ? WHERE `id` = ?");
        $stmtUp->execute([$newName, $id]);

        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $stmtLog->execute(["[양방향 동기화] 기기 [{$id}] 이름을 '{$newName}'(으)로 변경 (앱 동기화: " . ($appRenamed ? '성공' : '실패') . ")"]);

        echo json_encode([
            'success' => true,
            'deviceId' => $id,
            'newName' => $newName,
            'appSynced' => $appRenamed
        ]);
        exit;
    }

    if ($action === 'rename_channel') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? $_GET['id'] ?? $_POST['id'] ?? '';
        $cNo = (int)($input['channel'] ?? $_GET['channel'] ?? $_POST['channel'] ?? 0);
        $newName = trim($input['name'] ?? $_GET['name'] ?? $_POST['name'] ?? '');

        if (!$id || !$cNo || !$newName) {
            echo json_encode(['success' => false, 'error' => 'Device ID, Channel & New Name required']);
            exit;
        }

        $stmtUp = $pdo->prepare("UPDATE `{$prefix}channels` SET `channel_name` = ? WHERE `device_id` = ? AND `channel_no` = ?");
        $stmtUp->execute([$newName, $id, $cNo]);

        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $stmtLog->execute(["[채널 이름 변경] 기기 [{$id}] 채널 {$cNo}번 이름을 '{$newName}'(으)로 변경"]);

        echo json_encode([
            'success' => true,
            'deviceId' => $id,
            'channel' => $cNo,
            'newName' => $newName
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
