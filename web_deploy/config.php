<?php
/**
 * IoT Dashboard - MariaDB Configuration
 * Web Hosting: iwinv (PHP 8.4 + MariaDB 10.X)
 * DB Prefix: iot_dash_ (테이블 중복 방지)
 */

define('DB_HOST', 'localhost');
define('DB_USER', 'nuriohga');
define('DB_PASS', '#seungho0409');
define('DB_NAME', 'nuriohga');
define('DB_PREFIX', 'iot_dash_');

function getDbConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database Connection Failed: ' . $e->getMessage()]);
            exit;
        }
    }
    return $pdo;
}
?>
