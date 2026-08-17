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

function sendTuyaCommandRaw($deviceId, $code, $value) {
    $token = getTuyaAccessToken();
    if (!$token) return false;

    $t = (string)round(microtime(true) * 1000);
    $url = "/v1.0/devices/{$deviceId}/commands";
    $bodyObj = ['commands' => [['code' => $code, 'value' => $value]]];
    $bodyStr = json_encode($bodyObj);
    $sign = getTuyaSign(TUYA_CLIENT_ID, TUYA_SECRET, $t, $token, 'POST', $url, $bodyStr);

    $ch = curl_init(TUYA_ENDPOINT . $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyStr);
    curl_setopt($ch, CURLOPT_TIMEOUT, 4);
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

function decodeTuyaInterlock($base64Str) {
    if (empty($base64Str)) return [];
    $raw = base64_decode($base64Str);
    $len = strlen($raw);
    $groups = [];
    for ($i = 0; $i < $len; $i += 2) {
        if ($i + 1 < $len) {
            $high = ord($raw[$i]);
            $low = ord($raw[$i + 1]);
            $val = ($high << 8) | $low;
            if ($val > 0) {
                $group = [];
                for ($bit = 0; $bit < 16; $bit++) {
                    if (($val & (1 << $bit)) !== 0) {
                        $group[] = $bit + 1;
                    }
                }
                if (!empty($group)) {
                    $groups[] = $group;
                }
            }
        }
    }
    return $groups;
}

function encodeTuyaInterlock($groups) {
    // 8 bytes (4 groups of 16-bit uint)
    $buf = array_fill(0, 8, 0);
    $idx = 0;
    foreach ($groups as $grp) {
        if ($idx >= 4) break;
        $mask = 0;
        foreach ($grp as $chNo) {
            $bit = (int)$chNo - 1;
            if ($bit >= 0 && $bit < 16) {
                $mask |= (1 << $bit);
            }
        }
        $buf[$idx * 2] = ($mask >> 8) & 0xFF;
        $buf[$idx * 2 + 1] = $mask & 0xFF;
        $idx++;
    }
    $binaryStr = '';
    foreach ($buf as $b) {
        $binaryStr .= chr($b);
    }
    return base64_encode($binaryStr);
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
            $interlockGroups = [];
            $temperature = null;
            $humidity = null;
            $battery = null;
            if (isset($resObj['status']) && is_array($resObj['status'])) {
                foreach ($resObj['status'] as $item) {
                    if ($item['code'] === 'switch_1' || $item['code'] === 'switch') {
                        $state = (bool)$item['value'];
                        $channels[1] = (bool)$item['value'];
                    } elseif (preg_match('/^switch_(\d+)$/', $item['code'], $m)) {
                        $cNum = (int)$m[1];
                        $channels[$cNum] = (bool)$item['value'];
                        if ((bool)$item['value']) $state = true;
                    } elseif ($item['code'] === 'switch_interlock') {
                        $interlockGroups = decodeTuyaInterlock($item['value']);
                    } elseif ($item['code'] === 'va_temperature' || $item['code'] === 'temp_current') {
                        $val = (float)$item['value'];
                        $temperature = ($val > 100) ? ($val / 10.0) : $val;
                    } elseif ($item['code'] === 'va_humidity' || $item['code'] === 'humidity_value') {
                        $humidity = (float)$item['value'];
                    } elseif ($item['code'] === 'battery_percentage' || $item['code'] === 'battery_state') {
                        $battery = (int)$item['value'];
                    }
                }
            }
            $results[$id] = [
                'name' => $name,
                'state' => $state,
                'channels' => $channels,
                'interlockGroups' => $interlockGroups,
                'temperature' => $temperature,
                'humidity' => $humidity,
                'battery' => $battery
            ];
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

        $farmAliases = [
            'ebb219afdebea03ba3shlz' => '💧 양수기',
            '42362638a4e57cb3cd0b' => '💨 송풍기',
            'eb654aa2437462ea40dfjw' => '🎛️ 1동 개폐기 (4-433)'
        ];

        while ($row = $stmtDev->fetch()) {
            $id = $row['id'];
            $tuyaDeviceIds[] = $id;
            $dbName = $row['device_name'];
            if (empty($dbName) || $dbName === '책상등' || $dbName === '3D프린터' || $dbName === '4채널 멀티 스위치' || $dbName === '💧 주 양수기' || $dbName === '💨 천정 송풍기' || $dbName === '🎛️ 4채널 모터 스위치') {
                $dbName = $farmAliases[$id] ?? $dbName;
                $pdo->prepare("UPDATE `{$prefix}devices` SET `device_name` = ? WHERE `id` = ?")->execute([$dbName, $id]);
            }
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
                        if (isset($info['interlockGroups'])) {
                            $devices[$id]['interlockGroups'] = $info['interlockGroups'];
                        }
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

        // 하우스 및 하우스별 스마트 농가 장비 로드
        $houses = [];
        try {
            $stmtH = $pdo->query("SELECT * FROM `{$prefix}houses` ORDER BY `sort_order` ASC, `id` ASC");
            while ($hRow = $stmtH->fetch()) {
                $hId = (int)$hRow['id'];
                $houses[$hId] = [
                    'id' => $hId,
                    'name' => $hRow['house_name'],
                    'crop' => $hRow['crop_type'],
                    'memo' => $hRow['memo'],
                    'sortOrder' => (int)$hRow['sort_order'],
                    'devices' => []
                ];
            }

            $stmtHD = $pdo->query("SELECT * FROM `{$prefix}house_devices` ORDER BY `sort_order` ASC, `id` ASC");
            while ($dRow = $stmtHD->fetch()) {
                $hId = (int)$dRow['house_id'];
                if (isset($houses[$hId])) {
                    $devId = (int)$dRow['id'];
                    $boundDevId = $dRow['bound_device_id'];
                    $boundChNo = (int)$dRow['bound_channel_no'];

                    $isActive = (bool)$dRow['is_active'];
                    if ($boundDevId && isset($devices[$boundDevId])) {
                        if (isset($devices[$boundDevId]['channels'][$boundChNo])) {
                            $isActive = (bool)$devices[$boundDevId]['channels'][$boundChNo]['state'];
                        } else {
                            $isActive = (bool)$devices[$boundDevId]['state'];
                        }
                    }

                    $houses[$hId]['devices'][$devId] = [
                        'id' => $devId,
                        'houseId' => $hId,
                        'category' => $dRow['device_category'],
                        'name' => $dRow['device_name'],
                        'boundDeviceId' => $boundDevId,
                        'boundChannelNo' => $boundChNo,
                        'state' => $isActive,
                        'position' => (int)$dRow['position_pct'],
                        'specs' => $dRow['specs']
                    ];
                }
            }
        } catch (Exception $e) {
            // 테이블이 아직 없는 경우 무시
        }

        // 실시간 커튼 및 모터 개폐율 상태 로드
        $curtains = [
            1 => ['motorNo' => 1, 'name' => '1호 모터 (측창 비닐막)', 'position' => 0.0, 'direction' => 0],
            2 => ['motorNo' => 2, 'name' => '2호 모터 (상부 차광막)', 'position' => 0.0, 'direction' => 0]
        ];
        try {
            $pdo->exec("CREATE TABLE IF NOT EXISTS `{$prefix}curtains` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `house_id` INT NOT NULL DEFAULT 1,
                `motor_no` INT NOT NULL DEFAULT 1,
                `curtain_name` VARCHAR(100) DEFAULT '측창 비닐막',
                `position_pct` FLOAT NOT NULL DEFAULT 0.0,
                `direction` INT NOT NULL DEFAULT 0,
                `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                UNIQUE KEY `uk_house_motor` (`house_id`, `motor_no`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

            $stmtC = $pdo->query("SELECT * FROM `{$prefix}curtains` WHERE `house_id` = 1 ORDER BY `motor_no` ASC");
            while ($cRow = $stmtC->fetch()) {
                $mNo = (int)$cRow['motor_no'];
                $curtains[$mNo] = [
                    'motorNo' => $mNo,
                    'name' => $cRow['curtain_name'],
                    'position' => (float)$cRow['position_pct'],
                    'direction' => (int)$cRow['direction']
                ];
            }
        } catch (Exception $e) {}

        echo json_encode([
            'success' => true,
            'farmName' => '누리오 스마트팜 (Nurio Smart Farm)',
            'devices' => $devices,
            'houses' => $houses,
            'curtains' => $curtains,
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
                $stmtLog->execute(["[누리오 스마트팜] 4채널 스위치 [{$id}] 전체 채널 전원 변경 -> " . ($state ? 'ON' : 'OFF')]);

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
                $stmtLog->execute(["[누리오 스마트팜] 4채널 스위치 [{$id}] {$cNo}번 채널 전원 변경 -> " . ($state ? 'ON' : 'OFF')]);

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
            $stmtLog->execute(["[누리오 스마트팜] 스마트플러그 [{$id}] 전원 변경 -> " . ($state ? 'ON' : 'OFF')]);

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

    if ($action === 'set_interlock') {
        $input = json_decode(file_get_contents('php://input'), true);
        $id = $input['id'] ?? 'eb654aa2437462ea40dfjw';
        $groups = $input['groups'] ?? []; // e.g. [[1,2], [3,4]] or [[1,2,3,4]] or []

        $base64Val = encodeTuyaInterlock($groups);
        $tuyaSuccess = sendTuyaCommandRaw($id, 'switch_interlock', $base64Val);

        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $grpSummary = empty($groups) ? '전체 해제(독립모드)' : json_encode($groups);
        $stmtLog->execute(["[인터락 설정] 4채널 스위치 [{$id}] 인터락 그룹 변경 -> {$grpSummary} (Tuya 전송: " . ($tuyaSuccess ? '성공' : '실패') . ")"]);

        echo json_encode([
            'success' => true,
            'deviceId' => $id,
            'groups' => $groups,
            'base64' => $base64Val,
            'tuyaDispatched' => $tuyaSuccess
        ]);
        exit;
    }

    // --- 🍓 누리오 스마트팜 비닐하우스 & 장비 CRUD API ---

    if ($action === 'save_house') {
        $input = json_decode(file_get_contents('php://input'), true);
        $hId = (int)($input['id'] ?? 0);
        $name = trim($input['name'] ?? '신규 하우스');
        $crop = trim($input['crop'] ?? '딸기 (설향)');
        $memo = trim($input['memo'] ?? '');
        $sortOrder = (int)($input['sort_order'] ?? 1);

        if ($hId > 0) {
            $stmt = $pdo->prepare("UPDATE `{$prefix}houses` SET `house_name` = ?, `crop_type` = ?, `memo` = ?, `sort_order` = ? WHERE `id` = ?");
            $stmt->execute([$name, $crop, $memo, $sortOrder, $hId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO `{$prefix}houses` (`house_name`, `crop_type`, `memo`, `sort_order`) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $crop, $memo, $sortOrder]);
            $hId = (int)$pdo->lastInsertId();
        }

        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $stmtLog->execute(["[하우스 설정] '{$name}' 하우스 정보가 저장되었습니다."]);

        echo json_encode(['success' => true, 'houseId' => $hId]);
        exit;
    }

    if ($action === 'delete_house') {
        $input = json_decode(file_get_contents('php://input'), true);
        $hId = (int)($input['id'] ?? $_GET['id'] ?? 0);

        if ($hId > 0) {
            $stmtDev = $pdo->prepare("DELETE FROM `{$prefix}house_devices` WHERE `house_id` = ?");
            $stmtDev->execute([$hId]);

            $stmt = $pdo->prepare("DELETE FROM `{$prefix}houses` WHERE `id` = ?");
            $stmt->execute([$hId]);

            $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('WARN', ?)");
            $stmtLog->execute(["[하우스 삭제] {$hId}번 하우스 및 하위 장비가 삭제되었습니다."]);

            echo json_encode(['success' => true, 'deletedHouseId' => $hId]);
            exit;
        }
        echo json_encode(['success' => false, 'error' => 'House ID required']);
        exit;
    }

    if ($action === 'save_house_device') {
        $input = json_decode(file_get_contents('php://input'), true);
        $devId = (int)($input['id'] ?? 0);
        $houseId = (int)($input['house_id'] ?? 1);
        $category = trim($input['category'] ?? 'WATER_PUMP');
        $name = trim($input['name'] ?? '신규 농가 장치');
        $boundDeviceId = !empty($input['bound_device_id']) ? trim($input['bound_device_id']) : null;
        $boundChannelNo = (int)($input['bound_channel_no'] ?? 1);
        $specs = trim($input['specs'] ?? '');
        $position = (int)($input['position_pct'] ?? 0);
        $sortOrder = (int)($input['sort_order'] ?? 1);

        if ($devId > 0) {
            $stmt = $pdo->prepare("UPDATE `{$prefix}house_devices` SET `house_id` = ?, `device_category` = ?, `device_name` = ?, `bound_device_id` = ?, `bound_channel_no` = ?, `specs` = ?, `position_pct` = ?, `sort_order` = ? WHERE `id` = ?");
            $stmt->execute([$houseId, $category, $name, $boundDeviceId, $boundChannelNo, $specs, $position, $sortOrder, $devId]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO `{$prefix}house_devices` (`house_id`, `device_category`, `device_name`, `bound_device_id`, `bound_channel_no`, `specs`, `position_pct`, `sort_order`) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$houseId, $category, $name, $boundDeviceId, $boundChannelNo, $specs, $position, $sortOrder]);
            $devId = (int)$pdo->lastInsertId();
        }

        $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
        $stmtLog->execute(["[장치 설정] '{$name}' 농가 장치가 저장되었습니다."]);

        echo json_encode(['success' => true, 'deviceId' => $devId]);
        exit;
    }

    if ($action === 'delete_house_device') {
        $input = json_decode(file_get_contents('php://input'), true);
        $devId = (int)($input['id'] ?? $_GET['id'] ?? 0);

        if ($devId > 0) {
            $stmt = $pdo->prepare("DELETE FROM `{$prefix}house_devices` WHERE `id` = ?");
            $stmt->execute([$devId]);

            $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('WARN', ?)");
            $stmtLog->execute(["[장치 삭제] {$devId}번 장치가 삭제되었습니다."]);

            echo json_encode(['success' => true, 'deletedDeviceId' => $devId]);
            exit;
        }
        echo json_encode(['success' => false, 'error' => 'Device ID required']);
        exit;
    }

    if ($action === 'control_house_device') {
        $input = json_decode(file_get_contents('php://input'), true);
        $devId = (int)($input['id'] ?? 0);
        $type = $input['type'] ?? 'TOGGLE'; // TOGGLE or POSITION
        $targetState = isset($input['state']) ? (bool)$input['state'] : null;
        $targetPosition = isset($input['position']) ? (int)$input['position'] : null;

        $stmt = $pdo->prepare("SELECT * FROM `{$prefix}house_devices` WHERE `id` = ?");
        $stmt->execute([$devId]);
        $deviceRow = $stmt->fetch();

        if (!$deviceRow) {
            echo json_encode(['success' => false, 'error' => 'Device not found']);
            exit;
        }

        $boundDevId = $deviceRow['bound_device_id'];
        $boundChNo = (int)$deviceRow['bound_channel_no'];
        $tuyaDispatched = false;

        if ($type === 'TOGGLE') {
            if ($targetState === null) {
                $targetState = !((bool)$deviceRow['is_active']);
            }
            $stmtUp = $pdo->prepare("UPDATE `{$prefix}house_devices` SET `is_active` = ? WHERE `id` = ?");
            $stmtUp->execute([$targetState ? 1 : 0, $devId]);

            // 투야 물리 기기와 바인딩되어 있다면 릴레이 명령 전송
            if ($boundDevId) {
                $tuyaDispatched = sendTuyaCommand($boundDevId, $targetState, $boundChNo);
                usleep(300000);
                $liveTuya = fetchTuyaMultipleDevicesParallel([$boundDevId]);
                if (!empty($liveTuya)) {
                    syncTuyaDevicesToDb($liveTuya, $pdo, $prefix);
                }
            }

            $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
            $stmtLog->execute(["[장치 제어] '{$deviceRow['device_name']}' 상태 -> " . ($targetState ? 'ON' : 'OFF')]);

            echo json_encode([
                'success' => true,
                'deviceId' => $devId,
                'targetState' => $targetState,
                'boundDeviceId' => $boundDevId,
                'tuyaDispatched' => $tuyaDispatched
            ]);
            exit;
        } elseif ($type === 'POSITION') {
            $stmtUp = $pdo->prepare("UPDATE `{$prefix}house_devices` SET `position_pct` = ? WHERE `id` = ?");
            $stmtUp->execute([$targetPosition, $devId]);

            $stmtLog = $pdo->prepare("INSERT INTO `{$prefix}logs` (`log_level`, `message`) VALUES ('INFO', ?)");
            $stmtLog->execute(["[개폐 제어] '{$deviceRow['device_name']}' 개폐율 -> {$targetPosition}%"]);

            echo json_encode([
                'success' => true,
                'deviceId' => $devId,
                'position' => $targetPosition
            ]);
            exit;
        }
    }

    if ($action === 'set_curtain') {
        $input = json_decode(file_get_contents('php://input'), true);
        $houseId = (int)($input['house_id'] ?? 1);
        $motorNo = (int)($input['motor_no'] ?? 1);
        $position = isset($input['position']) ? (float)$input['position'] : 0.0;
        $direction = isset($input['direction']) ? (int)$input['direction'] : 0;
        $name = ($motorNo === 1) ? '1호 모터 (측창 비닐막)' : '2호 모터 (상부 차광막)';

        try {
            $stmtCurtain = $pdo->prepare("INSERT INTO `{$prefix}curtains` (`house_id`, `motor_no`, `curtain_name`, `position_pct`, `direction`) 
                VALUES (?, ?, ?, ?, ?) 
                ON DUPLICATE KEY UPDATE `position_pct` = VALUES(`position_pct`), `direction` = VALUES(`direction`)");
            $stmtCurtain->execute([$houseId, $motorNo, $name, $position, $direction]);

            echo json_encode(['success' => true, 'motorNo' => $motorNo, 'position' => $position, 'direction' => $direction]);
            exit;
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }

    echo json_encode(['success' => false, 'error' => 'Invalid action']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
?>
